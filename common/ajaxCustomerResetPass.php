<?php
session_start();
include('../config/config.php');
include('../common/common_header.php');

$formData		= array();
parse_str($_REQUEST['formData'], $formData);
$password = md5($formData['cus_pass']);

$sql = "SELECT 	cus_id,
				cus_email,
				cus_name,
				cus_surname,
				custype_id 
		FROM 	customers 
		WHERE 	cus_id = '".$_SESSION["cus_id"]."' 
				AND cus_pass = '$password'
		LIMIT 	1";
$result = mysql_query($sql, $dbConn);
$row	= mysql_num_rows($result);
if($row > 0) {
	$newPass = md5($formData["cus_newPass"]);
	$cusRecord = new TableSpa("customers", $_SESSION["cus_id"]);
	$cusRecord->setFieldValue('cus_pass', $newPass);
	if($cusRecord->commit()) {
		echo 'PASS';
	} else {
		echo "NOT_PASS";
		echo mysql_error($dbConn);
	}
} else {
	echo "INCORRECT_PASS";
}
?>