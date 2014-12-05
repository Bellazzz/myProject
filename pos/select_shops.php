<?php
session_start();
//require('check_session.php');

include('../config/config.php');
$tplName = 'select_shops.html';
$subDir	 = WEB_ROOTDIR.'/pos/';

include('../common/common_header.php');

// Get shops data
$shopList = array();
$sql = "SELECT 		* 
		FROM 		shops 
		ORDER BY 	shop_id ASC";
$result = mysql_query($sql, $dbConn);
$rows 	= mysql_num_rows($result);
if($rows > 0) {
	for($i=0; $i<$rows; $i++) {
		array_push($shopList, mysql_fetch_assoc($result));
	}
}

$smarty->assign('shopList', $shopList);

include('../common/common_footer.php');
?>