<?php
session_start();
include('config/config.php');
include('common/common_header.php');

if(hasValue($_POST['pkg_id']) && hasValue($_POST['persons']) &&
hasValue($_POST['bkg_date']) && hasValue($_POST['bkg_time']) && 
hasValue($_POST['pkg_picture']) && hasValue($_POST['pkg_name']) && 
hasValue($_POST['pkg_price']) && hasValue($_POST['allMin'])) {
	$pkg_id 	= $_POST['pkg_id'];
	$persons 	= $_POST['persons'];
	$bkg_date 	= $_POST['bkg_date'];
	$bkg_time 	= $_POST['bkg_time'];
	$pkg_picture = $_POST['pkg_picture'];
	$pkg_name = $_POST['pkg_name'];
	$pkg_price = $_POST['pkg_price'];
	$bkgemp_id = $_POST['bkgemp_id'];
	$bkgemp_fullname = $_POST['bkgemp_fullname'];
	$allMin = $_POST['allMin'];

	$values = array(
		'pkg_id' 	=> $pkg_id,
		'persons' 	=> $persons,
		'bkg_date' 	=> $bkg_date,
		'bkg_time' 	=> $bkg_time,
		'pkg_picture' 	=> $pkg_picture,
		'pkg_name' 	=> $pkg_name,
		'pkg_price' => $pkg_price,
		'bkgemp_id' => $bkgemp_id,
		'bkgemp_fullname' => $bkgemp_fullname,
		'allMin' => $allMin
	);

	if($bkgemp_fullname == 'ไม่ระบุ') {
		unset($values['bkgemp_fullname']);
	}

	$_SESSION['bookingPkg'][$pkg_id] = $values;

	echo "PASS";
} else {
	echo "FAIL";
}
?>