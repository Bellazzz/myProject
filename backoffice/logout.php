<?php
session_start();
include('../config/config.php');
include('../common/common_header.php');

if(isset($_SESSION['loggedin'])) {
	unset($_SESSION['loggedin']);
}
if(isset($_SESSION['emp_id'])) {
	unset($_SESSION['emp_id']);
}
if(isset($_SESSION['emp_email'])) {
	unset($_SESSION['emp_email']);
}
header("location:login.php");
?>