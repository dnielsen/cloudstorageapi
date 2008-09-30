<?php
/* Copyright (C) 2008 Komea LLC */
/* 
 *  This program is free software: you can redistribute it and/or modify 
 *  it under the terms of the GNU General Public License as published by 
 *  the Free Software Foundation, either version 3 of the License, or 
 *  (at your option) any later version. 
 * 
 *  This program is distributed in the hope that it will be useful, 
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of 
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the 
 *  GNU General Public License for more details. 
 * 
 *  You should have received a copy of the GNU General Public License 
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>. 
 */
/* AmazonUnifiedApi.php */
require_once 'Cloud/Storage/UnifiedInterface.php';

class Cloud_Storage_Amazon implements Cloud_Storage_Unified_Interface
{
    const AMAZON_S3_DOMAIN = 's3.amazonaws.com';
    const X_AMZ_ACL = 'x-amz-acl:public-read';
    const CREDENTIAL_AWS_KEY = 'aws_key';
    const CREDENTIAL_AWS_SECRET = 'aws_secret';
    const CREDENTIAL_AWS_SECRET_LENGTH = 40;
    const HTTP_PORT = 80;
    const HTTP_TIME_OUT = 30; /* seconds */
    const RESPONSE_MAX_LINE_LENGTH = 256;

    public $debug;
    private $aws_key;
    private $aws_secret;
    private $defaultBucketName;
    private $response;

    public function __construct($credential, $defaultBucketName=null)
    {
      $this->debug = FALSE;

      if (strlen($credential[self::CREDENTIAL_AWS_SECRET]) != self::CREDENTIAL_AWS_SECRET_LENGTH) {
        die(self::CREDENTIAL_AWS_SECRET." should be exactly "
          .self::CREDENTIAL_AWS_SECRET_LENGTH." bytes long.");
      } // if
      $this->aws_key = $credential[self::CREDENTIAL_AWS_KEY];
      $this->aws_secret = $credential[self::CREDENTIAL_AWS_SECRET];
      $this->defaultBucketName = $defaultBucketName;
    } // __construct

    private function _restWriteFunction(&$curl, &$data) {
        $this->response->body .= $data;
        return strlen($data);
    } // _restWriteFunction

    private function _restHeaderFunction(&$curl, &$data) {
        if (($strlen = strlen($data)) <= 2) return $strlen;
        if (substr($data, 0, 4) == 'HTTP')
            $this->response->code = (int)substr($data, 9, 3);
        else {
            list($header, $value) = explode(': ', trim($data), 2);
            if ($header == 'Last-Modified')
                $this->response->headers['time'] = strtotime($value);
            elseif ($header == 'Content-Length')
                $this->response->headers['size'] = (int)$value;
            elseif ($header == 'Content-Type')
                $this->response->headers['type'] = $value;
            elseif ($header == 'ETag')
                $this->response->headers['hash'] = substr($value, 1, -1);
            elseif (preg_match('/^x-amz-meta-.*$/', $header))
                $this->response->headers[$header] = is_numeric($value) ? (int)$value : $value;
        }
        return $strlen;
    } // _restHeaderFunction

    private function _sendRest($domainName, $fullFilePath, $httpVerb, $header, $contentsInMemory)
    {
        /* $this->response is used by the callback functions to pass back information */
	$this->response = new STDClass;
	$this->response->error = false;

        /* prepare to curl */
        $url = 'http://'.$domainName.$fullFilePath;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_USERAGENT, 'Cloud/Storage');
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_URL, $url);

        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, false);
        curl_setopt($curl, CURLOPT_WRITEFUNCTION, array(&$this, '_restWriteFunction'));
        curl_setopt($curl, CURLOPT_HEADERFUNCTION, array(&$this, '_restHeaderFunction'));
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

        switch ($httpVerb) {
            case 'GET': break;
            case 'PUT':
                if (!is_null($contentsInMemory)) {
                    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $contentsInMemory);
                    curl_setopt($curl, CURLOPT_BUFFERSIZE, strlen($contentsInMemory));
                } else
                    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
            break;
            case 'HEAD':
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'HEAD');
                curl_setopt($curl, CURLOPT_NOBODY, true);
            break;
            case 'DELETE':
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
            break;
            default: break;
        } // switch

        if (curl_exec($curl)) {
            $this->response->code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        } else {
            $this->response->error = array(
                'code' => curl_errno($curl),
                'message' => curl_error($curl),
            );
                } // else

        @curl_close($curl);

        // Parse body into XML
        if ($this->response->error === false && isset($this->response->headers['type']) &&
        $this->response->headers['type'] == 'application/xml' && isset($this->response->body)) {
            $this->response->body = simplexml_load_string($this->response->body);

            // Grab S3 errors
            if (!in_array($this->response->code, array(200, 204)) &&
            isset($this->response->body->Code, $this->response->body->Message)) {
                $this->response->error = array(
                    'code' => (string)$this->response->body->Code,
                    'message' => (string)$this->response->body->Message
                );
                if (isset($this->response->body->Resource))
                    $this->response->error['resource'] = (string)$this->response->body->Resource;
                unset($this->response->body);
            }
        }

        return $this->response;

    } // _sendRest
    
    private function _binSha1($d)
    {
        return sha1($d, true); 
    } // _binSha1

    private function _hmac($stringToSign) 
    {
        /* http://en.wikipedia.org/wiki/HMAC. */
        $aws_secret = $this->aws_secret;
    
        if (strlen($aws_secret) == 40)
            $aws_secret = $aws_secret.str_repeat(chr(0), 24);
    
        /* warning: key($aws_secret) is padded to 64 bytes with 0x0 after first function call */
        $ipad = str_repeat(chr(0x36), 64);
        $opad = str_repeat(chr(0x5c), 64);
    
        $hmac = $this->_binSha1(($aws_secret^$opad)
          .$this->_binSha1(($aws_secret^$ipad).$stringToSign));
        return base64_encode($hmac);
    } // _hmac

    private function _execute($httpVerb, $amzHeaders, $filePath, $contentsInMemory, $mimeType,
      $containerName)
    {
        if (is_null($httpVerb)) return(false);

        $domainName = self::AMAZON_S3_DOMAIN;

        $contentsLength = 0;
        if (!is_null($contentsInMemory)) {
          $contentsLength = strlen($contentsInMemory);
        } // if

        /* GMT based long timestamp */
        $dt = gmdate('r');

	$contentMd5 = null;
        $fullFilePath = '/'.$containerName;
        if (!empty($filePath)) {
          if (substr($filePath, 0, 1) != '/') {
            $fullFilePath .= '/';
          } // if
          $fullFilePath .= $filePath;
        } // if

        /* preparing String to Sign */
        $stringToSign = $httpVerb."\n"
	  .$contentMd5."\n"
          .$mimeType."\n"
          .$dt."\n"
          .$fullFilePath;

        /* preparing HTTP header */
        $header = Array();
        if (!is_null($amzHeaders)) {
	  foreach ($amzHeaders as $amzhdr => $value) {
            if (strlen($value) > 0) $headers[] = $amzhdr.': '.$value;
          } // foreach
        } // if

        $header[] = "Host: ".$domainName;
        if (!is_null($mimeType) && ($mimeType != '')) {
            $header[] = "Content-Type: ".$mimeType;
            $header[] = "Content-Length: ".$contentsLength;
        } // if
        $header[] = "Date: ".$dt;
        $header[] = "Authorization: AWS ".$this->aws_key.":".$this->_hmac($stringToSign)
          ."\n".$fullFilePath;

        $resp = $this->_sendRest($domainName, $fullFilePath, $httpVerb, $header, $contentsInMemory);
        return($resp);
    } // _execute

    public function listAllContainers() {
        $resp = $this->_execute('GET', null, null, null, null, null);
/*
$resp returns something like this:

$resp =(
 [error] => 
 [code] => 200 
 [headers] => Array (
  [type] => application/xml 
 )
 [body] => SimpleXMLElement Object (
  [Owner] => SimpleXMLElement Object (
   [ID] => e33b4ef33bzzzzzzzzzzzzz885512de77cdc469012d5fd60aa315c1385555555 
   [DisplayName] => userName
  )
  [Buckets] => SimpleXMLElement Object (
   [Bucket] => Array (
    [0] => SimpleXMLElement Object (
     [Name] => bucket1
     [CreationDate] => 2008-03-22T10:55:19.000Z 
    )
    [1] => SimpleXMLElement Object (
     [Name] => bucket2
     [CreationDate] => 2008-03-22T10:50:29.000Z 
    )
   )
  )
 )
)
 */

        $returnArray = Array();
        $body = $resp->body;
        $bucketArray = $body->Buckets->Bucket;
        foreach ($bucketArray as $bucket) {
          $returnArray[] = (string) $bucket->Name;
        } // foreach
        return($returnArray);
    } // listAllContainers

    public function uploadContents($filePath, $contentsInMemory, $mimeType=null,
      $containerName=null)
    {
        $amzHeaders = Array();
        $amzHeaders[] = self::X_AMZ_ACL;
        $resp = $this->_execute('PUT', $amzHeaders, $filePath, $contentsInMemory, $mimeType, $containerName);
/*
$resp returns something like this:

Object (
 [error] =>
 [code] => 200
 [headers] => Array (
  [hash] => 7da19e7fe34e2a17zzzzzzzz2b732f5b
  [size] => 0 
 )
)
 */
        return(null);
    } // uploadContents

    public function downloadContents($filePath, $expiration=null,
      $containerName=null)
    {
        $resp = $this->_execute('GET', null, $filePath, null, null, $containerName);
/*
$resp returns something like this:

$resp = (
 [error] => 
 [code] => 200 
 [headers] => Array (
  [time] => 1220307156
  [hash] => 7da19e7zzzzzzzzzzz91b3752b732f5b
  [type] => application/octet-stream
  [size] => 240
 )
 [body] => data line 1 data line 2 
)



 */
      return($resp->body);
    } // downloadContents

    public function getContentsMetaData($filePath, $containerName=null)
    {
        $resp = $this->_execute('HEAD', null, $filePath, null, null, $containerName);
/*
$resp returns something like this:

$resp = (
 [error] =>
 [code] => 200
 [headers] => Array (
  [time] => 1220307156
  [hash] => 7da19e7zzzzzzzzzzz91b3752b732f5b
  [type] => application/octet-stream
  [size] => 240
 )
)
 */
        return($resp->headers);
    } // getContentsMetaData

    public function deleteContents($filePath, $containerName=null)
    {
        $resp = $this->_execute('DELETE', null, $filePath, null, null, $containerName);
/*
$resp returns something like this:

$resp = (
 [error] =>
 [code] => 200
)
*/
        return(null);
    } // deleteContents

} // Cloud_Storage_Unified_Interface
