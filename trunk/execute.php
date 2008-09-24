<?php
/* execute.php */
require_once('Zend/Debug.php');
require_once 'config.php';
require_once 'Cloud/Storage/Amazon/AmazonUnifiedApi.php';
require_once 'Cloud/Storage/Nirvanix/NirvanixUnifiedApi.php';

$bucket = $_POST[Config::PARAM_BUCKET];
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
echo("List all buckets<br>");
try {
  $folderList = $storage->listAllBuckets();
  echo("<br>listAllBuckets response:</br>");
  echo("<br>".Zend_Debug::dump($folderList, null, FALSE));
} catch (Exception $e) {
  echo("<br>".Zend_Debug::dump($e, "listAllBuckets error" , FALSE));
}

echo("<hr>");
echo("Upload Contents from file <tt>".$file['name']."</tt> to <tt>".$filePath."</tt>");
try {
  $originalData = file_get_contents($file['tmp_name']);
  $resp = $storage->uploadContents($filePath, $originalData,
    $file['type'], $bucket);
  echo("<br>uploadContents succeeded.</br>");
} catch (Exception $e) {
  echo("<br>".Zend_Debug::dump($e, "uploadContents error" , FALSE));
}

echo("<hr>");
echo("Download Contents from path <tt>".$filePath."</tt>");
try {
  $storedData = $storage->downloadContents($filePath, null, $bucket);
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
  $metaData = $storage->getContentsMetaData($filePath, $bucket);
  echo("<br>".Zend_Debug::dump($metaData, "getContentsMetaData error" , FALSE));
} catch (Exception $e) {
  echo("<br>".Zend_Debug::dump($e, "getContentsMetaData error" , FALSE));
}

echo("<hr>");
echo("delete contents of path <tt>".$filePath."</tt>");
try {
  $resp2 = $storage->deleteContents($filePath, $bucket);
  echo("<br>deleteContents succeeded.<br>");
} catch (Exception $e) {
  echo("<br>".Zend_Debug::dump($e, "deleteContents error" , FALSE));
}
echo("<hr>");

?>
