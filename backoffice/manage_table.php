<?php
session_start();
require('check_session.php');
include('../config/config.php');
$tplName = 'manage_table.html';
$subDir	 = WEB_ROOTDIR.'/backoffice/';

include('../common/common_header.php');

// redirect to time attandance
if($emp_privileges_count == 1 && $emp_privileges['timer'] == true) {
	header("location:timeAttendance.php");
}

$smarty->assign('tplName', $tplName);
include('../common/common_footer.php');
?>