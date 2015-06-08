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
if(isset($_SESSION['emp_name'])) {
	unset($_SESSION['emp_name']);
}
if(isset($_SESSION['emp_surname'])) {
	unset($_SESSION['emp_surname']);
}
if(isset($_SESSION['emp_pos_name'])) {
	unset($_SESSION['emp_pos_name']);
}
if(isset($_SESSION['hideRemindPrdMinAmount'])) {
	unset($_SESSION['hideRemindPrdMinAmount']);
}
header("location:login.php");
?>