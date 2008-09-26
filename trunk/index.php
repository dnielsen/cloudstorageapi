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
/* index.php */
require_once 'config.php';

?>
<html>
<head>
<title>uapi</title>
</head>
<body>
<h1>Unified API Tester</h1>
<form  enctype="multipart/form-data" id="uapiForm" method="POST" action="execute.php">
<table>
<tr>
<td align="right">Local File:</td>
<td align="left"><input type="FILE" size="50" name="<?=Config::PARAM_CONTENT_FILE?>"/>
<br>
E.g.: /tmp/test2.txt
</td>
</tr>

<tr>
<td align="right">Target Path:</td>
<td align="left"><input type="input" size="50" name="<?=Config::PARAM_FILE_PATH?>"/>
<br>
E.g.: /folder2/test2.txt
</td>
</tr>

<tr>
<td align="right">Bucket:</td>
<td align="left"><input type="input" size="50" name="<?=Config::PARAM_BUCKET?>" value="<?=Config::AMAZON_DEFAULT_BUCKET?>"/></td>
</tr>

<tr>
<td align="center" ><input type="submit" value="<?=Config::MODE_NIRVANIX?>" name="<?=Config::PARAM_TARGET_SYSTEM?>"></td>
<td align="center" ><input type="submit" value="<?=Config::MODE_AMAZON?>" name="<?=Config::PARAM_TARGET_SYSTEM?>"></td>
</tr>
</table>
</form>
</body>
</html>

