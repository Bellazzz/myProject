<?php
session_start();
//require('check_session.php');
include('config/config.php');
include('common/common_header.php');

if(hasValue($_POST['svl_id']) && hasValue($_POST['persons']) &&
hasValue($_POST['bkg_date']) && hasValue($_POST['bkg_time']) && 
hasValue($_POST['svl_picture']) && hasValue($_POST['svl_name']) && 
hasValue($_POST['svl_price'])) {
	$svl_id 	= $_POST['svl_id'];
	$persons 	= $_POST['persons'];
	$bkg_date 	= $_POST['bkg_date'];
	$bkg_time 	= $_POST['bkg_time'];
	$svl_picture = $_POST['svl_picture'];
	$svl_name = $_POST['svl_name'];
	$svl_price = $_POST['svl_price'];

	$values = array(
		'svl_id' 	=> $svl_id,
		'persons' 	=> $persons,
		'bkg_date' 	=> $bkg_date,
		'bkg_time' 	=> $bkg_time,
		'svl_picture' 	=> $svl_picture,
		'svl_name' 	=> $svl_name,
		'svl_price' => $svl_price
	);

	$_SESSION['bookingSvl'][$svl_id] = $values;

	echo "PASS";
} else {
	echo "FAIL";
}
?>