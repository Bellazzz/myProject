<?php
session_start();
include('../config/config.php');
include('../common/common_header.php');

$sessionBkg = array();

if(isset($_SESSION['bookingSvl']) && is_array($_SESSION['bookingSvl'])) {
	foreach ($_SESSION['bookingSvl'] as $svl_id => $value) {
		array_push($sessionBkg, array(
			'name' => $value['svl_name'],
			'bkg_date' => $value['bkg_date'],
			'bkg_time' => $value['bkg_time'],
			'bkgemp_id' => $value['bkgemp_id'],
			'allMin' => $value['allMin']
		));
	}
}

if(isset($_SESSION['bookingPkg']) && is_array($_SESSION['bookingPkg'])) {
	foreach ($_SESSION['bookingPkg'] as $pkg_id => $value) {
		array_push($sessionBkg, array(
			'name' => $value['pkg_name'],
			'bkg_date' => $value['bkg_date'],
			'bkg_time' => $value['bkg_time'],
			'bkgemp_id' => $value['bkgemp_id'],
			'allMin' => $value['allMin'],
			'bkgemp_fullname' => $value['bkgemp_fullname']
		));
	}
}

echo json_encode($sessionBkg);
?>