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

/* config.php */
class Config
{
const PARAM_CONTAINER = 'container';
const PARAM_CONTENT_FILE = "content";
const PARAM_FILE_PATH = "filePath";
const PARAM_TARGET_SYSTEM = "target";

const MODE_NIRVANIX = "Nirvanix";
const MODE_AMAZON = "Amazon";

/* The following information must be filled in */
const NIRVANIX_USERNAME = 'fill-in-your-info';
const NIRVANIX_PASSWORD = 'fill-in-your-info';
const NIRVANIX_APPKEY = 'fill-in-your-info';

const AMAZON_AWS_KEY = 'fill-in-your-info';
const AMAZON_AWS_SECRET = 'fill-in-your-info';

const AMAZON_DEFAULT_BUCKET = 'fill-in-your-info';

} // Config

?>
