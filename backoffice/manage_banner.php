<?php
session_start();
require('check_session.php');
include('../config/config.php');
$tplName = 'manage_banner.html';
$subDir	 = WEB_ROOTDIR.'/backoffice/';

include('../common/common_header.php');

// Get website banner data
$sql = "SELECT * FROM website_banners";
$result = mysql_query($sql, $dbConn);
$rows = mysql_num_rows($result);
if($rows > 0) {
	$websiteBannerList = array();
	for($i=0; $i<$rows; $i++) {
		array_push($websiteBannerList, mysql_fetch_assoc($result));
	}
	$smarty->assign('websiteBannerList', $websiteBannerList);
}

$smarty->assign('tplName', $tplName);
include('../common/common_footer.php');
?>