<?php
include('common_constant.php');
include('common_function.php');

$id				= $_REQUEST['id'];
$tableName		= $_REQUEST['tableName'];
$keyFieldName	= $_REQUEST['keyFieldName'];
$textFieldName	= $_REQUEST['textFieldName'];
$orderFieldName	= $_REQUEST['orderFieldName'];
$orderType		= $_REQUEST['orderType'];
$searchText		= $_REQUEST['searchText'];
$begin			= $_REQUEST['begin'];
$limit			= $_REQUEST['limit'];
$pattern		= $_REQUEST['pattern'];
$condition 		= $_REQUEST['condition'];
$where			= '';
$order			= '';

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
		// if has more than one field
		$like = array();
		foreach($textFieldList as $key => $textField) {
			array_push($like, $textField." like '%$searchText%'");
		}
		$where = 'WHERE ('.implode(' OR ', $like).')';
	} else if(count($textFieldList) == 1) {
		// has one field
		$where = "WHERE $textFieldName like '%$searchText%'";
		
	}
}

// Add condition for query
if(hasValue($condition)) {
	$condition = str_replace("\'", "'", $condition);
	$condition = str_replace('\\"', '"', $condition);
	$condition = str_replace('\"', '\\', $condition);
	if(hasValue($where)) {
		$where .= " AND $condition";
	} else {
		$where = "WHERE $condition ";
	}
}

// Generate order
if(count($textFieldList) > 1) {
	// if has more than one field
	if(hasValue($orderFieldName)) {
		// has define will order by that fields
		$orderFieldList = explode(',', $orderFieldName);
		if(hasValue($orderType)) {
			$orderTypeList	= explode(',', $orderType);
			foreach($orderFieldList as $key => $field) {
				if(isset($orderTypeList[$key])) {
					$orderFieldList[$key] = $field.' '.$orderTypeList[$key];
				}
			}
		}
		$order = 'ORDER BY '.implode(',', $orderFieldList);
	} else {
		// undefine will default last field
		$order = 'ORDER BY '.end($textFieldList);
	}
} else if(count($textFieldList) == 1) {
	// has one field use that field
	$order 			= 'ORDER BY '.$textFieldList[0];
	if(hasValue($orderType)) {
		$orderTypeList	= explode(',', $orderType);
		$order .= ' '.$orderTypeList[0];
	}
}

$sql = "SELECT $keyFieldName, $pattern pattern FROM $tableName $where $order LIMIT $begin,$limit";
$result = mysql_query($sql, $dbConn);
$rows	= mysql_num_rows($result);

if($rows > 0) {
	while($record = mysql_fetch_assoc($result)) {
		?>
		<li id="<?=$id?>_<?=$record[$keyFieldName]?>">
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
