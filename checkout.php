<?php
session_start();
require('check_session.php');
include('config/config.php');
$tplName = 'checkout.html';
$subDir	 = WEB_ROOTDIR.'/';

include('common/common_header.php');

if((isset($_SESSION['bookingPkg']) || isset($_SESSION['bookingSvl'])) && isset($_SESSION['cus_id'])) {
	$insertResult = ture;
	$errTxt = '';
	$bkg_total_price = 0;
	$bookingPkg = $_SESSION['bookingPkg'];
	$bookingSvl = $_SESSION['bookingSvl'];

	// Cal booking total price
	if(isset($_SESSION['bookingPkg']) && is_array($_SESSION['bookingPkg'])) {
		foreach ($bookingPkg as $key => $val) {
			$sumPrice = $val['persons'] * $val['pkg_price'];
			$bkg_total_price += $sumPrice;
			$bookingPkg[$key]['sumPrice'] = $sumPrice;
		}
	}
	if(isset($_SESSION['bookingSvl']) && is_array($_SESSION['bookingSvl'])) {
		foreach ($bookingSvl as $key => $val) {
			$sumPrice = $val['persons'] * $val['svl_price'];
			$bkg_total_price += $sumPrice;
			$bookingSvl[$key]['sumPrice'] = $sumPrice;
		}
	}
	

	$fieldNames = array(
		'cus_id',
		'status_id',
		'bkg_total_price',
		'bkg_date',
		'bkg_time'
	);
	$fieldValues = array(
		$_SESSION['cus_id'],
		'S01', // Pending Check
		$bkg_total_price,
		$nowDate,
		$nowTime
	);

	// Insert booking
	$tableRecord = new TableSpa('booking', $fieldNames, $fieldValues);
	if(!$tableRecord->insertSuccess()) {
		$insertResult = false;
		$errTxt .= 'INSERT_BOOKING_FAIL<br>';
		$errTxt .= mysql_error($dbConn).'<br><br>';
		echo $errTxt;
	}
	$bkg_id = $tableRecord->getKey();

	// Insert booking packages
	if(isset($bookingPkg) && is_array($bookingPkg)) {
		foreach ($bookingPkg as $pkg_id => $val) {
			$bkgpkg_date 		= $val['bkg_date'];
			$bkgpkg_time 		= $val['bkg_time'];
			$bkgpkg_persons 	= $val['persons'];
			$bkgpkg_total_price = $val['sumPrice'];
			$bkgpkgValues 		= array($pkg_id, $bkg_id, $bkgpkg_date, $bkgpkg_time, $bkgpkg_total_price, $bkgpkg_persons);
			$bkgpkgRecord 		= new TableSpa('booking_packages', $bkgpkgValues);
			if(!$bkgpkgRecord->insertSuccess()) {
				$insertResult = false;
				$errTxt .= 'INSERT_BOOKING_PACKAGES['.$pkg_id.']_FAIL<br>';
				$errTxt .= mysql_error($dbConn).'<br><br>';
			}
		}
	}
	// End booking packages

	// Insert booking service_list
	if(isset($bookingSvl) && is_array($bookingSvl)) {
		foreach ($bookingSvl as $svl_id => $val) {
			$bkgsvl_date 		= $val['bkg_date'];
			$bkgsvl_time 		= $val['bkg_time'];
			$bkgsvl_persons 	= $val['persons'];
			$bkgsvl_total_price = $val['sumPrice'];
			$bkgsvlValues 		= array($svl_id, $bkg_id, $bkgsvl_date, $bkgsvl_time, $bkgsvl_total_price, $bkgsvl_persons);
			$bkgsvlRecord 		= new TableSpa('booking_service_lists', $bkgsvlValues);
			if(!$bkgsvlRecord->insertSuccess()) {
				$insertResult = false;
				$errTxt .= 'INSERT_BOOKING_SERVICE_LISTS['.$svl_id.']_FAIL<br>';
				$errTxt .= mysql_error($dbConn).'<br><br>';
			}
		}
	}
	// End booking packages

	if($insertResult) {
		unset($_SESSION['bookingPkg']);
		unset($_SESSION['bookingSvl']);
	}

	$smarty->assign('insertResult', $insertResult);
	$smarty->assign('errTxt', $errTxt);
} else {
	header("location:shopping_cart.php");
}

$smarty->assign('tplName', $tplName);
include('common/common_footer.php');
?>

