<?php
session_start();
include('config/config.php');
include('common/common_header.php');

if(hasValue($_POST['id']) && hasValue($_POST['persons'])) {
	$id = $_POST['id'];
	$persons = (int)$_POST['persons'];

	if(is_int($persons) && $persons > 0) {
		if(isset($_SESSION['bookingSvl'][$id])) {
			$_SESSION['bookingSvl'][$id]['persons'] = $persons;
		} else if(isset($_SESSION['bookingPkg'][$id])) {
			$_SESSION['bookingPkg'][$id]['persons'] = $persons;
		}
	}
}
?>

