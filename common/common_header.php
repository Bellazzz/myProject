<?php
	include('class_database.php');
	include('common_function.php');
	require(WEB_ROOTDIR.'/inc/Smarty-3.1.18/libs/Smarty.class.php');

	$smarty = new Smarty();
	$smarty->setTemplateDir($subDir.'template');//html
	$smarty->setCompileDir($subDir.'template_c');//file compile
	
	date_default_timezone_set('Asia/Bangkok');
	$nowDate = date('Y/m/d');
	$nowTime = date('H:i');
	$smarty->assign('nowDate', date('Y/m/d'));
	$smarty->assign('nowTime', date('H:i'));

	// Session
	if(isset($_SESSION['loggedin'])) {
		$smarty->assign('session_loggedin', $_SESSION['loggedin']);
	}
	if(isset($_SESSION['emp_id'])) {
		$session_emp_id = $_SESSION['emp_id'];
		$smarty->assign('session_emp_id', $_SESSION['emp_id']);
	}
	if(isset($_SESSION['emp_email'])) {
		$smarty->assign('session_emp_user', $_SESSION['emp_email']);
	}
	if(isset($_SESSION['emp_name'])) {
		$smarty->assign('session_emp_name', $_SESSION['emp_name']);
	}
	if(isset($_SESSION['emp_surname'])) {
		$smarty->assign('session_emp_surname', $_SESSION['emp_surname']);
	}
?>