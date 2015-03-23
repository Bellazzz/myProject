<?php
session_start();
require('check_session.php');
include('config/config.php');
$tplName = 'booking_history.html';
$subDir	 = WEB_ROOTDIR.'/';

include('common/common_header.php');

$cus_id = $_SESSION['cus_id'];
$bkgList = array();
$sql = "SELECT 		b.bkg_id,
					b.bkg_date,
					DATE_FORMAT(b.bkg_time,'%H:%i') bkg_time,
					b.bkg_total_price,
					b.status_id,
					bs.bkgstat_name 
		FROM 		booking b,
					booking_status bs 
		WHERE 		b.status_id = bs.bkgstat_id AND 
					b.cus_id = '$cus_id' 
		ORDER BY 	b.bkg_date DESC,
					b.bkg_time DESC";
$result = mysql_query($sql, $dbConn);
$rows 	= mysql_num_rows($result);
if($rows > 0) {
	for($i=0; $i<$rows; $i++) {
		$record = mysql_fetch_assoc($result);
		array_push($bkgList, $record);
		$bkgList[$i]['bkg_date_th'] = dateThaiFormat($record['bkg_date']);
	}
	$smarty->assign('bkgList', $bkgList);
}

$smarty->assign('tplName', $tplName);
include('common/common_footer.php');
?>

