<?php
session_start();
//require('check_session.php');
include('config/config.php');
include('common/common_header.php');

if(hasValue($_POST['pkg_id']) && hasValue($_POST['persons']) &&
hasValue($_POST['bkg_date']) && hasValue($_POST['bkg_time']) && 
hasValue($_POST['pkg_picture']) && hasValue($_POST['pkg_name']) && 
hasValue($_POST['pkg_price'])) {
	$pkg_id 	= $_POST['pkg_id'];
	$persons 	= $_POST['persons'];
	$bkg_date 	= $_POST['bkg_date'];
	$bkg_time 	= $_POST['bkg_time'];
	$pkg_picture = $_POST['pkg_picture'];
	$pkg_name = $_POST['pkg_name'];
	$pkg_price = $_POST['pkg_price'];

	$values = array(
		'pkg_id' 	=> $pkg_id,
		'persons' 	=> $persons,
		'bkg_date' 	=> $bkg_date,
		'bkg_time' 	=> $bkg_time,
		'pkg_picture' 	=> $pkg_picture,
		'pkg_name' 	=> $pkg_name,
		'pkg_price' => $pkg_price
	);

	$_SESSION['bookingPkg'][$pkg_id] = $values;

	echo "PASS";
} else {
	echo "FAIL";
}
?>