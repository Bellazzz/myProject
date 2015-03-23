<?php
session_start();
if(!isset($_SESSION['loggedin']) || !isset($_SESSION['emp_id'])) {
	header("location:login.php");
}
?>