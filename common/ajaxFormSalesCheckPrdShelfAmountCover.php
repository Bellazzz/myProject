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

$sql = "SELECT 	p.prd_shelf_amount,
				p.prd_name,
				u.unit_name 
		FROM 	products p, units u 
		WHERE 	p.unit_id = u.unit_id AND 
				p.prd_id = '$prd_id'";
$result = mysql_query($sql, $dbConn);
$rows 	= mysql_num_rows($result);
if($rows > 0) {
	$record 		= mysql_fetch_assoc($result);
	$shelf_amount 	= $record['prd_shelf_amount'];
	if($shelf_amount != '' && $qty > $shelf_amount) {
		$overAmount 			= $qty - $shelf_amount;
		$response['status'] 	= 'NOT_ENOUGH';
		$response['prd_name'] 	= $record['prd_name'];
		$response['overAmount'] = number_format($overAmount).' '.$record['unit_name'];
		echo json_encode($response);
	} else {
		$response['status'] = 'ENOUGH';
		echo json_encode($response);
	}
} else {
	$response['status'] = 'FAIL';
	echo json_encode($response);
}
?>