<?php
include('../config/config.php');
include('../common/common_header.php');

$prd_id = '';
$response = array();
if(hasValue($_REQUEST['prd_id'])) {
	$prd_id = $_REQUEST['prd_id'];
}

$sql = "SELECT 	u.unit_name 
		FROM 	products p, units u 
		WHERE 	p.unit_id = u.unit_id 
				AND p.prd_id = '$prd_id'";
$result = mysql_query($sql, $dbConn);
$row	= mysql_num_rows($result);
if($row > 0) {
	$unitRow = mysql_fetch_assoc($result);
	$response['status'] 	= 'PASS';
	$response['unit_name'] 	= $unitRow['unit_name'];
	echo json_encode($response);
} else {
	$response['status'] = 'ไม่พบหน่วยนับของ prd_id นี้';
	echo json_encode($response);
}
?>