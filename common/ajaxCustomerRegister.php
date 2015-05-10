<?php
session_start();
include('../config/config.php');
include('../common/common_header.php');

$formData		= array();
parse_str($_REQUEST['formData'], $formData);

$cusRecord = new TableSpa('customers', array(
	'X1',
	'CT1',
	$formData['title_id'],
	$formData['cus_name'],
	$formData['cus_surname'],
	$formData['cus_addr'],
	$formData['cus_tel'],
	$formData['cus_email'],
	md5($formData['cus_pass']),
	$formData['cus_birthdate'] == '' ? null : getRealDate($formData['cus_birthdate']),
	$nowDate,
	$formData['cus_facebook'] == '' ? null : $formData['cus_facebook'],
	$formData['cus_line'] == '' ? null : $formData['cus_line'],
));

if($cusRecord->insertSuccess()) {
	echo 'PASS';
} else {
	echo "NOT_PASS";
	echo mysql_error($dbConn);
}
?>