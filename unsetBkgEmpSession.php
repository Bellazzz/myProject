<?php
session_start();
include('config/config.php');
include('common/common_header.php');

if(hasValue($_POST['id'])) {
	$id = $_POST['id'];

	if(isset($_SESSION['bookingSvl'][$id])) {
		unset($_SESSION['bookingSvl'][$id]['bkgemp_id']);
		unset($_SESSION['bookingSvl'][$id]['bkgemp_fullname']);
	} else if(isset($_SESSION['bookingPkg'][$id])) {
		unset($_SESSION['bookingPkg'][$id]['bkgemp_id']);
		unset($_SESSION['bookingPkg'][$id]['bkgemp_fullname']);
	}
}
?>

