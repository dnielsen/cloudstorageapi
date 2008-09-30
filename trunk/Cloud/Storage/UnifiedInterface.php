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
/* UnifiedInterface.php */
interface Cloud_Storage_Unified_Interface
{

    public function __construct($credential, $defaultBucketName=null);

/**
    listAllContainers retrieves a list of all top-level containers and returns 
      an in-memory Array of names.
 */
    public function listAllContainers();

/**
    uploadContents uploads contents stored in memory in $contentsInMemory
      to a file on the storage network under the path $filePath.
      $mimeType is the mime type of the contents.
      $containerName is an optional parameter for Amazon S3.
      It returns null on success.
 */
    public function uploadContents($filePath, $contentsInMemory, $mimeType=null, $containerName=null);

/**
    downloadContents downloads a file stored on the storage network under 
      the path $filePath, stores the contents into a memory buffer and returns.
      $expiration is an optional parameter for Nirvanix.
      $containerName is an optional parameter for Amazon S3.
 */
    public function downloadContents($filePath, $expiration=null, $containerName=null);

/**
    getContentsMetaData retrieves the meta-data of a file stored on the 
      storage network under the path $filePath and returns the meta-data as 
      an in-memory structure.  The meta-data contents are specific to each 
      storage network.
 */
    public function getContentsMetaData($filePath, $containerName=null);

/**
    deleteContents deletes a file stored on the storage network under 
      the path $filePath.  It returns null on success.
 */
    public function deleteContents($filePath, $containerName=null);

} // Cloud_Storage_Unified_Interface
