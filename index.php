<?php
session_start();
//require('check_session.php');
include('config/config.php');
$tplName = 'index.html';
$subDir	 = WEB_ROOTDIR.'/';

include('common/common_header.php');

$smarty->assign('tplName', $tplName);
include('common/common_footer.php');
?>

