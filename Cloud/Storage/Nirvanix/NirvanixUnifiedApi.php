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
/* NirvanixUnifiedApi.php */
require_once 'Cloud/Storage/UnifiedInterface.php';
require_once 'Cloud/Storage/Exception.php';
require_once 'Zend/Service/Nirvanix.php';

class Cloud_Storage_Nirvanix implements Cloud_Storage_Unified_Interface
{
    const DEFAULT_DOWNLOAD_EXPIRATION = 7776000; /* seconds: 90 days */

    public $debug;
    private $nirvanix;
    private $imfs;
    private $metadata;

    public function __construct($credential, $defaultBucketName=null)
    {
      $this->debug = TRUE;
      $this->nirvanix = new Zend_Service_Nirvanix(array(
          'username' => $credential['username'],
          'password' => $credential['password'],
          'appKey'   => $credential['appKey'])
        );
      $this->imfs = $this->nirvanix->getService('IMFS');
      $this->metadata = $this->nirvanix->getService('METADATA');
    } // __construct

    private function _reportError($functionName, $exception) {
      if ($this->debug === TRUE) {
        echo($functionName." error:<br>");
        print_r($exception);
        echo("<hr>");
      } // if
    } // _reportError

    public function listAllContainers() {
      $params = array(
        'folderPath' => '/',
        'pageNumber' => 1,
        'pageSize' => 100,
        'sortCode' => 'Name',
        'sortDescending' => 'false'
      ); // array

      try {
        $resp = $this->imfs->listFolder($params);
        $respCode = $resp->ResponseCode;
        if ($resp->ResponseCode != 0) {
          throw new Cloud_Storage_Exception('listAllContainers error',
            $resp->ResponseCode);
        } // if
      } catch (Zend_Exception $e) {
        $this->_reportError('listAllContainers', $e);
        throw $e;
      }

       /* return null if no folder */
       $listFolder = $resp->ListFolder;
       if ($listFolder->TotalFolderCount == 0) return(null);

       /* only return the folder names in an array */
       $returnArray = array();
       $folderArray = $listFolder->Folder;
       foreach ($folderArray as $folder) {
         $returnArray[] = (string) $folder->Name;
       } // foreach
       return($returnArray);
    } // listAllContainers

    public function uploadContents($filePath, $contentsInMemory, $mimeType=null,
      $containerName=null)
    {
      try {
        $resp= $this->imfs->putContents($filePath, $contentsInMemory, $mimeType);
/*
$resp has this data structure:

Zend_Service_Nirvanix_Response Object (
 [_sxml:protected] => SimpleXMLElement Object (
  [ResponseCode] => 0
  [FilesUploaded] => 1
  [BytesUploaded] => 24 
 )
)
 */
        if ($resp->ResponseCode != 0) {
          throw new Cloud_Storage_Exception('uploadContents error',
            $resp->ResponseCode);
        } // if
        return(0);
      } catch (Zend_Exception $e) {
        $this->_reportError('uploadContents', $e);
        throw $e;
      }
    } // uploadContents

    public function downloadContents($filePath, $expiration=null,
      $containerName=null)
    {
      if (is_null($expiration)) {
          $expiration = self::DEFAULT_DOWNLOAD_EXPIRATION;
      } // if

      try {
        $resp = $this->imfs->getContents($filePath, $expiration);
        if ($resp->ResponseCode != 0) {
          throw new Cloud_Storage_Exception('downloadContents error',
            $resp->ResponseCode);
        } // if
        return($resp);
      } catch (Zend_Exception $e) {
        $this->_reportError('downloadContents', $e);
        throw $e;
      }
    } // downloadContents

    public function getContentsMetaData($filePath, $containerName=null)
    {
      $params = array('path' => $filePath);
      try {
        $resp = $this->metadata->GetMetadata($params);
/*
$resp has this data structure:

Zend_Service_Nirvanix_Response Object (
 [_sxml:protected] => SimpleXMLElement Object (
  [ResponseCode] => 0
  [Metadata] => SimpleXMLElement Object (
   [Type] => MD5
   [Value] => faGef+NOKhdUkbN1K3MvWw== 
  )
 )
)
 */
        if ($resp->ResponseCode != 0) {
          throw new Cloud_Storage_Exception('getContentsMetaData error',
            $resp->ResponseCode);
        } // if
        return($resp->Metadata);
      } catch (Zend_Exception $e) {
        $this->_reportError('getContentsMetaData', $e);
        return(null);
      }
    } // getContentsMetaData

    public function deleteContents($filePath, $containerName=null)
    {
      try {
        If (is_array($filePath)) {
          $resp = $this->imfs->deleteFiles($filePath);
        } else {
          $resp = $this->imfs->deleteFiles(Array('filePath' => $filePath));
        } // else
/*
$resp has this data structure:

Zend_Service_Nirvanix_Response Object (
 [_sxml:protected] => SimpleXMLElement Object (
  [ResponseCode] => 0 
 )
)
 */
        if ($resp->ResponseCode != 0) {
          throw new Cloud_Storage_Exception('deleteContents error',
            $resp->ResponseCode);
        } // if
        return(null);
      } catch (Zend_Exception $e) {
        $this->_reportError('deleteContents', $e);
        return(null);
      }
    } // deleteContents

    public function getService($serviceName) {
      return($this->nirvanix->getService($serviceName));
    } // getService

} // Cloud_Storage_Nirvanix
