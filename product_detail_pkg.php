<?php
session_start();
include('config/config.php');
$tplName = 'product_detail_pkg.html';
$subDir	 = WEB_ROOTDIR.'/';

include('common/common_header.php');

$id = '';

if(hasValue($_GET['id'])) {
	$id = $_GET['id'];

	if(isset($_GET['edit']) && isset($_SESSION['bookingPkg'][$id])) {
		$smarty->assign('persons', $_SESSION['bookingPkg'][$id]['persons']);
		$smarty->assign('bkg_date', $_SESSION['bookingPkg'][$id]['bkg_date']);
		$smarty->assign('bkg_time', $_SESSION['bookingPkg'][$id]['bkg_time']);
		$smarty->assign('bkgemp_id', $_SESSION['bookingPkg'][$id]['bkgemp_id']);
		$smarty->assign('bkgemp_fullname', $_SESSION['bookingPkg'][$id]['bkgemp_fullname']);
		$smarty->assign('edit', true);
	}
}

$sql = "SELECT 		pkg_id,
					pkg_name,
					pkg_price,
					pkg_desc,
					pkg_picture,
					pkg_start,
					pkg_stop 
		FROM 		packages  
		WHERE 		pkg_id = '$id' AND 
					pkg_start <= '$nowDate' AND 
					(
						pkg_stop IS NULL OR 
						pkg_stop >= '$nowDate'
					)";
$result = mysql_query($sql, $dbConn);
$rows 	= mysql_num_rows($result);
if($rows > 0) {
	$record = mysql_fetch_assoc($result);
	$prdData = $record;
	$prdData['svlList'] = array();

	$prdData['pkg_start'] = dateThaiFormat($prdData['pkg_start']);
	if($record['pkg_stop'] != null) {
		$prdData['pkg_stop'] = dateThaiFormat($prdData['pkg_stop']);
	}
}

// Get package time
$sql = "SELECT 		IFNULL(pkgsvl_hr,0) * 60 + IFNULL(pkgsvl_min,0) allMin 
		FROM 		package_service_lists  
		WHERE 		pkg_id = '$id'";
$result = mysql_query($sql, $dbConn);
$rows 	= mysql_num_rows($result);
if($rows > 0) {
	$record = mysql_fetch_assoc($result);
	$prdData['allMin'] = $record['allMin'];
}

// Get package promotion detail data
$sql = "SELECT 		pkgprmdtl.pkg_id,
					pkgprmdtl.pkgprmdtl_discout,
					pkgprmdtl.pkgprmdtl_discout_type 
		FROM 		package_promotion_details pkgprmdtl,
					package_promotions pkgprm 
		WHERE 		pkgprmdtl.pkgprm_id = pkgprm.pkgprm_id AND 
					pkgprmdtl.pkgprmdtl_startdate <= '$nowDate' AND 
					(
						pkgprmdtl.pkgprmdtl_enddate IS NULL OR
						pkgprmdtl.pkgprmdtl_enddate >= '$nowDate'
					) AND 
					pkgprmdtl.pkg_id ='$id' AND 
					pkgprm.custype_id = '".$_SESSION['custype_id']."'";
$result = mysql_query($sql, $dbConn);
$rows 	= mysql_num_rows($result);
if($rows > 0) {
	for($i=0; $i<$rows; $i++) {
		$record = mysql_fetch_assoc($result);
		$pkg_id = $record['pkg_id'];
		$discoutText = $record['pkgprmdtl_discout'];
		$discoutPrice = $record['pkgprmdtl_discout'];

		if($record['pkgprmdtl_discout_type'] == '%') {
			$discoutPrice = $prdData['pkg_price'] * $record['pkgprmdtl_discout'] / 100;
			$discoutText .= '%';
		} else {
			$discoutText .= ' บาท';
		}
		$prdData['pkg_prmPrice'] = $prdData['pkg_price'] - $discoutPrice;
	}
}


// Get service lists or package
$sql = "SELECT 		s.svl_name 
		FROM 		package_service_lists ps,
					service_lists s 
		WHERE 		ps.svl_id = s.svl_id AND 
					ps.pkg_id = '$id'";
$result = mysql_query($sql, $dbConn);
$rows 	= mysql_num_rows($result);
if($rows > 0) {
	$sumHr = 0;
	$sumMin = 0;

	for($i=0; $i<$rows; $i++) {
		$record = mysql_fetch_assoc($result);
		$svl_id = $record['svl_id'];
		$sumHr += $record['svl_hr'];
		$sumMin += $record['svl_min'];

		array_push($prdData['svlList'], $record['svl_name']);
	}

	for($i=$sumMin; $i>=60; $i-=60) {
		$sumHr++;
		$sumMin-=60;
	}
	$prdData['pkg_hr'] = $sumHr;
	$prdData['pkg_min'] = $sumMin;
}

$times = array();
for($i=9; $i<=20; $i++) {
	$time = '';
	if($i < 10) {
		$time .= '0';
	}
	$time .= $i.':00';
	array_push($times, $time);
}

$smarty->assign('prdData', $prdData);
$smarty->assign('times', $times);

$smarty->assign('tplName', $tplName);
include('common/common_footer.php');
?>

