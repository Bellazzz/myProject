<?php
session_start();
include('config/config.php');
$tplName = 'about_us.html';
$subDir	 = WEB_ROOTDIR.'/';

include('common/common_header.php');

$smarty->assign('tplName', $tplName);
include('common/common_footer.php');
?>

