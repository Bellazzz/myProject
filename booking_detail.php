<?php
session_start();
require('check_session.php');
include('config/config.php');
$tplName = 'booking_detail.html';
$subDir	 = WEB_ROOTDIR.'/';

include('common/common_header.php');

$cus_id = $_SESSION['cus_id'];
$bkg_id = '';
$displayPayment = true;
$displayBtnEditPayment = false;
if(hasValue($_GET['bkg_id'])) {
	$bkg_id = $_GET['bkg_id'];
}

// Query booking data
$sql = "SELECT 		b.bkg_id,
					b.bkg_date,
					DATE_FORMAT(b.bkg_time,'%H:%i') bkg_time,
					b.bkg_total_price,
					b.bkg_transfer_date,
					DATE_FORMAT(b.bkg_transfer_time,'%H:%i') bkg_transfer_time,
					b.bkg_transfer_evidence,
					b.bkg_transfer_money,
					b.status_id,
					bs.bkgstat_name,
					ba.bnkacc_name 
		FROM 		booking b 
					JOIN 		booking_status bs 	ON b.status_id = bs.bkgstat_id 
					LEFT JOIN 	bank_accounts ba 	ON b.bnkacc_id = ba.bnkacc_id 
		WHERE 		b.cus_id = '$cus_id' AND 
					b.bkg_id = '$bkg_id' 
		ORDER BY 	b.bkg_date DESC,
					b.bkg_time DESC";
$result = mysql_query($sql, $dbConn);
$rows 	= mysql_num_rows($result);
if($rows > 0) {
	$bkgData = mysql_fetch_assoc($result);
	$bkgData['bkg_date_th'] = dateThaiFormat($bkgData['bkg_date']);
	if($bkgData['bkg_transfer_date'] != '') {
		$bkgData['bkg_transfer_date_th'] = dateThaiFormat($bkgData['bkg_transfer_date']);
	}
	if($bkgData['status_id'] == 'S01') {
		$displayPayment = false;
	} else if($bkgData['status_id'] == 'S02') {
		$displayBtnEditPayment = true;
	}
	$smarty->assign('bkgData', $bkgData);
}

// Get booking service list data
$svlList = array();
$sql = "SELECT 	s.svl_id,
				s.svl_name,
				s.svl_picture,
				bs.bkgsvl_persons,
				bs.bkgsvl_date,
				DATE_FORMAT(bs.bkgsvl_time,'%H:%i') bkgsvl_time,
				bs.bkgsvl_status 
		FROM 	booking_service_lists bs,
				service_lists s 
		WHERE 	bs.svl_id = s.svl_id AND 
				bs.bkg_id = '$bkg_id'";
$result = mysql_query($sql, $dbConn);
$rows 	= mysql_num_rows($result);
if($rows > 0) {
	for($i=0; $i<$rows; $i++) {
		$record = mysql_fetch_assoc($result);
		$record['bkgsvl_date_th'] = dateThaiFormat($record['bkgsvl_date']);
		$svlList[$record['svl_id']] = $record;
	}
	$smarty->assign('svlList', $svlList);
}

// Get booking package data
$pkgList = array();
$sql = "SELECT 	p.pkg_id,
				p.pkg_name,
				p.pkg_picture,
				bp.bkgpkg_persons,
				bp.bkgpkg_date,
				DATE_FORMAT(bp.bkgpkg_time,'%H:%i') bkgpkg_time,
				bp.bkgpkg_status 
		FROM 	booking_packages bp,
				packages p 
		WHERE 	bp.pkg_id = p.pkg_id AND 
				bp.bkg_id = '$bkg_id'";
$result = mysql_query($sql, $dbConn);
$rows 	= mysql_num_rows($result);
if($rows > 0) {
	for($i=0; $i<$rows; $i++) {
		$record = mysql_fetch_assoc($result);
		$record['bkgpkg_date_th'] = dateThaiFormat($record['bkgpkg_date']);
		$pkgList[$record['pkg_id']] = $record;
	}
	$smarty->assign('pkgList', $pkgList);
}



$smarty->assign('displayPayment', $displayPayment);
$smarty->assign('displayBtnEditPayment', $displayBtnEditPayment);

$smarty->assign('tplName', $tplName);
include('common/common_footer.php');
?>

