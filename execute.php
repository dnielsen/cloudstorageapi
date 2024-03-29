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
/* execute.php */
require_once('Zend/Debug.php');
require_once 'config.php';
require_once 'Cloud/Storage/Amazon/AmazonUnifiedApi.php';
require_once 'Cloud/Storage/Nirvanix/NirvanixUnifiedApi.php';

$container = $_POST[Config::PARAM_CONTAINER];
$filePath = $_POST[Config::PARAM_FILE_PATH];
$targetName = $_POST[Config::PARAM_TARGET_SYSTEM];
$file = null;
if (count($_FILES) > 0) {
  foreach ($_FILES as $fileEntry) {
    $file = $fileEntry;
    break;
  } // foreach
} // if

$storage = null;
switch ($targetName) {
case Config::MODE_NIRVANIX:
  $nvxCredential = array(
    'username' => Config::NIRVANIX_USERNAME,
    'password' => Config::NIRVANIX_PASSWORD,
    'appKey'   => Config::NIRVANIX_APPKEY,
  ); // array
  $storage = new Cloud_Storage_Nirvanix($nvxCredential, null);
  break;
case Config::MODE_AMAZON:
  $amznCredential = array(
    'aws_key' => Config::AMAZON_AWS_KEY,
    'aws_secret' => Config::AMAZON_AWS_SECRET,
  ); // array
  $storage = new Cloud_Storage_Amazon($amznCredential, Config::AMAZON_DEFAULT_BUCKET);
  break;
} // switch

echo("<h1>".$targetName."</h1>");

echo("<hr>");
echo("List all containers<br>");
try {
  $folderList = $storage->listAllContainers();
  echo("<br>listAllContainers response:</br>");
  echo("<br>".Zend_Debug::dump($folderList, null, FALSE));
} catch (Exception $e) {
  echo("<br>".Zend_Debug::dump($e, "listAllContainers error" , FALSE));
}

echo("<hr>");
echo("Upload Contents from file <tt>".$file['name']."</tt> to <tt>".$filePath."</tt>");
try {
  $originalData = file_get_contents($file['tmp_name']);
  echo("<br>".Zend_Debug::dump($originalData, "uploadContents" , FALSE));
  $resp = $storage->uploadContents($filePath, $originalData,
    $file['type'], $container);
  echo("<br>uploadContents succeeded.</br>");
} catch (Exception $e) {
  echo("<br>".Zend_Debug::dump($e, "uploadContents error" , FALSE));
}

echo("<hr>");
echo("Download Contents from path <tt>".$filePath."</tt>");
try {
  $storedData = $storage->downloadContents($filePath, null, $container);
  echo("<br>".Zend_Debug::dump($storedData, "downloadContents" , FALSE));
  if ($storedData != $originalData) {
    echo("<br>downloadContents <b>Data mismatched!<b><br>");
    echo("<hr>");
    echo("Original:<br>");
    echo(Zend_Debug::dump($originalData,null,FALSE));
    echo("<hr>");
    echo("Results:<br>");
    echo(Zend_Debug::dump($storedData,null,FALSE));
    echo("<hr>");
  } else {
    echo("<br>downloadContents Data matched.<br>");
  } // if
} catch (Exception $e) {
  echo("<br>".Zend_Debug::dump($e, "downloadContents error" , FALSE));
}

echo("<hr>");
echo("get content metadata of path <tt>".$filePath."</tt>");
try {
  $metaData = $storage->getContentsMetaData($filePath, $container);
  echo("<br>".Zend_Debug::dump($metaData, "getContentsMetaData error" , FALSE));
} catch (Exception $e) {
  echo("<br>".Zend_Debug::dump($e, "getContentsMetaData error" , FALSE));
}

echo("<hr>");
echo("delete contents of path <tt>".$filePath."</tt>");
try {
  $resp2 = $storage->deleteContents($filePath, $container);
  echo("<br>deleteContents succeeded.<br>");
} catch (Exception $e) {
  echo("<br>".Zend_Debug::dump($e, "deleteContents error" , FALSE));
}
echo("<hr>");

?>
