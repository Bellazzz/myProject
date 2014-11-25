<?php
include('../config/config.php');
$tplName = 'printEmployeeCard.html';
$subDir	 = WEB_ROOTDIR.'/backoffice/';

include('../inc/Barcode39.php'); 
include('../common/common_header.php');

$empId = $_REQUEST['empId'];
if(hasValue($empId)) {
	$monthTH	= array(
		'01'	=> 'มกราคม',
		'02'	=> 'กุมภาพันธ์',
		'03'	=> 'มีนาคม',
		'04'	=> 'เมษายน',
		'05'	=> 'พฤษภาคม',
		'06'	=> 'มิถุนายน',
		'07'	=> 'กรกฎาคม',
		'08'	=> 'สิงหาคม',
		'09'	=> 'กันยายน',
		'10'	=> 'ตุลาคม',
		'11'	=> 'พฤศจิกายน',
		'12'	=> 'ธันวาคม'
	);
	$empRecord	= new TableSpa('employees', $empId);
	$pos_id		= $empRecord->getFieldValue('pos_id');
	$posRecord	= new TableSpa('positions', $pos_id);
	$empInfo	= array(
		'emp_id'		=> $empId,
		'emp_name'		=> $empRecord->getFieldValue('emp_name'),
		'emp_surname'	=> $empRecord->getFieldValue('emp_surname'),
		'pos_name'		=> $posRecord->getFieldValue('pos_name'),
		'emp_pic'		=> $empRecord->getFieldValue('emp_pic')
	);
	
	$smarty->assign('datePrint', date('d')."  ".$monthTH[date('m')]."  ".(date('Y')+543));
	$smarty->assign('dateExpire', date('d')."  ".$monthTH[date('m')]."  ".(date('Y')+544));
	$smarty->assign('empInfo', $empInfo);
	$smarty->assign('randNum', substr(str_shuffle('0123456789'), 0, 5));
}

include('../common/common_footer.php');
?>