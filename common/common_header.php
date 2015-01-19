<?php
	include('class_database.php');
	include('common_function.php');
	require(WEB_ROOTDIR.'/inc/Smarty-3.1.18/libs/Smarty.class.php');

	$smarty = new Smarty();
	$smarty->setTemplateDir($subDir.'template');//html
	$smarty->setCompileDir($subDir.'template_c');//file compile
	
	date_default_timezone_set('Asia/Bangkok');
	$nowDate 		= date('Y/m/d');
	$nowTime 		= date('H:i');
	$nowTimeFull 	= date('H:i:s');
	$smarty->assign('nowDate', date('Y/m/d'));
	$smarty->assign('nowTime', date('H:i'));
	$smarty->assign('nowTimeFull', date('H:i:s'));

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


	/*
	 * Get Privileges
	 */
	$emp_privileges = array();
	if(isset($session_emp_id)) {
		$sql = "SELECT  	p.privlg_name 
				FROM 		privileges p, 
							grant_privileges gp 
				WHERE 		p.privlg_id = gp.privlg_id AND 
							gp.emp_id = '$session_emp_id'";
		$result = mysql_query($sql, $dbConn);
		$rows 	= mysql_num_rows($result);
		if($rows > 0) {
			for($i=0; $i<$rows; $i++) {
				$record = mysql_fetch_assoc($result);
				array_push($emp_privileges, $record['privlg_name']);
			}
		}
	}
?>