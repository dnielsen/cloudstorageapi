<?php
interface Cloud_Storage_Unified_Interface
{

    public function __construct($credential, $defaultBucketName=null);

    public function listAllBuckets();

/*
    public function createBucket($locationConstraint, $bucketName);

    public function getBucketContents($bucketName);

    public function getBucketConstraint($bucketName);

    public function deleteBucketIfEmpty($bucketName);
*/

    public function uploadContents($filePath, $contentsInMemory, $mimeType=null, $bucketName=null);

    public function downloadContents($filePath, $expiration=null, $bucketName=null);

    public function getContentsMetaData($filePath, $bucketName=null);

    public function deleteContents($filePath, $bucketName=null);

} // Cloud_Storage_Unified_Interface
