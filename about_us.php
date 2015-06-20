<?php
session_start();
include('config/config.php');
$tplName = 'about_us.html';
$subDir	 = WEB_ROOTDIR.'/';

include('common/common_header.php');

// Get website_aboutus data
$websiteAboutusList = array();
$sql = "SELECT 		wsa_title,
					wsa_desc,
					wsa_picture 
		FROM 		website_aboutus";
$result = mysql_query($sql, $dbConn);
$rows = mysql_num_rows($result);
if($rows > 0) {
	for($i=0; $i<$rows; $i++) {
		array_push($websiteAboutusList, mysql_fetch_assoc($result));
	}
}

$smarty->assign('websiteAboutusList', $websiteAboutusList);
$smarty->assign('tplName', $tplName);
include('common/common_footer.php');
?>

