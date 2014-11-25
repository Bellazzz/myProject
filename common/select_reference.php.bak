<?php
include('common_constant.php');
include('common_function.php');

$id				= $_REQUEST['id'];
$tableName		= $_REQUEST['tableName'];
$keyFieldName	= $_REQUEST['keyFieldName'];
$textFieldName	= $_REQUEST['textFieldName'];
$searchText		= $_REQUEST['searchText'];
$begin			= $_REQUEST['begin'];
$limit			= $_REQUEST['limit'];
$pattern		= $_REQUEST['pattern'];
$where			= '';

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

// Generate where
if(hasValue($searchText)) {
	if(count($textFieldList) > 1) {
		$like = array();
		foreach($textFieldList as $key => $textField) {
			array_push($like, $textField." like '%$searchText%'");
		}
		$where = 'WHERE '.implode(' OR ', $like);
	} else if(count($textFieldList) == 1) {
		$where = "WHERE $textFieldName like '%$searchText%'";
	}
}

$sql = "SELECT $keyFieldName, $pattern pattern FROM $tableName $where LIMIT $begin,$limit";
$result = mysql_query($sql, $dbConn);
$rows	= mysql_num_rows($result);

if($rows > 0) {
	while($record = mysql_fetch_assoc($result)) {
		?>
		<li>
			<span class="text"><?=$record['pattern']?></span>
			<span class="value"><?=$record[$keyFieldName]?></span>
        </li>
		<?
	}
} else {
	echo '<div class="no-result">ไม่พบข้อมูลที่ค้นหา</div>';
}

$nextBegin = $begin + $rows;
?>
<script id="temp-script" type="text/javascript">
$('#<?=$id?>').attr('responserows', <?=$rows?>);
$('#<?=$id?>').attr('begin', <?=$nextBegin?>);
$('#temp-script').remove();
</script>
