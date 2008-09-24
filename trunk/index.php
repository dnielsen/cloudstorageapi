<?php
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

