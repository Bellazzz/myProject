<?php
include('../config/config.php');
include('../common/common_header.php');

$response = array();

$sql = "SELECT 	bnkacc_id,
				bnkacc_no,
				bnkacc_name 
		FROM 	bank_accounts 
		WHERE  	bnkacc_status = 1 
		ORDER BY bnkacc_name";
$result = mysql_query($sql, $dbConn);
$row	= mysql_num_rows($result);
if($row > 0) {
	$bnkaccData = array();
	for($i=0; $i<$row; $i++) {
		array_push($bnkaccData, mysql_fetch_assoc($result));
	}
	$response['status'] 	= 'PASS';
	$response['data'] 		= $bnkaccData;
	echo json_encode($response);
} else {
	$response['status'] = 'EMPTY';
	echo json_encode($response);
}
?>