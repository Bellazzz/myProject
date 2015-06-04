<?php
session_start();
include('../config/config.php');
include('../common/common_header.php');

$formData		= array();
parse_str($_REQUEST['formData'], $formData);

$cusRecord = new TableSpa('customers', $_SESSION['cus_id']);
$cusRecord->setFieldValue('title_id', $formData['title_id']);
$cusRecord->setFieldValue('sex_id', $formData['sex_id_hidden']);
$cusRecord->setFieldValue('cus_name', $formData['cus_name']);
$cusRecord->setFieldValue('cus_surname', $formData['cus_surname']);
$cusRecord->setFieldValue('cus_addr', $formData['cus_addr']);
$cusRecord->setFieldValue('cus_tel', $formData['cus_tel']);
$cusRecord->setFieldValue('cus_birthdate', $formData['cus_birthdate'] == '' ? null : getRealDate($formData['cus_birthdate']));
$cusRecord->setFieldValue('cus_facebook', $formData['cus_facebook'] == '' ? null : $formData['cus_facebook']);
$cusRecord->setFieldValue('cus_line_id', $formData['cus_line_id'] == '' ? null : $formData['cus_line_id']);

if($cusRecord->commit()) {
	echo 'PASS';
} else {
	echo "NOT_PASS";
	echo mysql_error($dbConn);
	print_r($formData);
}
?>