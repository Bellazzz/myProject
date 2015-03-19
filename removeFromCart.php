<?php
session_start();
//require('check_session.php');
include('config/config.php');
include('common/common_header.php');

if(hasValue($_GET['id'])) {
	$id = $_GET['id'];
	if(isset($_SESSION['bookingSvl'][$id])) {
		unset($_SESSION['bookingSvl'][$id]);
	} else if(isset($_SESSION['bookingPkg'][$id])) {
		unset($_SESSION['bookingPkg'][$id]);
	}
}
redirect('shopping_cart.php');
?>

