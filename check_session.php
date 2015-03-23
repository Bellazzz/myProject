<?php
session_start();
if(!isset($_SESSION['cusLogin']) || !isset($_SESSION['cus_id'])) {
	header("location:account_login.php");
}
?>