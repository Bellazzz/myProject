<?php
include('common_constant.php');
include('common_function.php');

$key			= $_REQUEST['key'];
$tableName		= $_REQUEST['tableName'];
$keyFieldName	= $_REQUEST['keyFieldName'];
$textFieldName	= $_REQUEST['textFieldName'];
$pattern		= $_REQUEST['pattern'];

// Generate pattern
$textFieldList = explode(',', $textFieldName);
if(!hasValue($pattern)) {
	if(count($textFieldList) > 1) {
		$pattern = 'CONCAT('.implode(',"  ",', $textFieldList).')';
	} else if(count($textFieldList) == 1) {
		$pattern = $textFieldList[0];
	}
} else {
	$pattern = str_replace("\'", "'", $pattern);
	$pattern = str_replace('\\"', '"', $pattern);
	$pattern = str_replace('\"', '\\', $pattern);
}

if(hasValue($_REQUEST['key'])) {
	$sql = "SELECT $pattern pattern FROM $tableName WHERE $keyFieldName = '$key'  LIMIT 1";
	$result = mysql_query($sql, $dbConn);
	$rows	= mysql_num_rows($result);

	if($rows > 0) {
		$record = mysql_fetch_assoc($result);
		echo $record['pattern'];
	}
}
?>