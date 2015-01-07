<?php
include('../config/config.php');
include('../common/common_header.php');

$prd_id = '';
$qty 	= 0;
if(hasValue($_POST['prd_id'])) {
	$prd_id = $_POST['prd_id'];
}
if(hasValue($_POST['qty'])) {
	$qty = $_POST['qty'];
}

$sql = "SELECT 	prd_shelf_amount 
		FROM 	products 
		WHERE 	prd_id = '$prd_id'";
$result = mysql_query($sql, $dbConn);
$rows 	= mysql_num_rows($result);
if($rows > 0) {
	$record 		= mysql_fetch_assoc($result);
	$shelf_amount 	= $record['prd_shelf_amount'];
	if($shelf_amount != '' || $qty > $shelf_amount) {
		echo 'NOT_ENOUGH';
	} else {
		echo 'ENOUGH';
	}
} else {
	echo 'FAIL';
}
?>