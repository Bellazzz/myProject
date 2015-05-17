<?php
session_start();
if(isset($_SESSION['cusLogin'])) {
	unset($_SESSION['cusLogin']);
}
if(isset($_SESSION['cus_id'])) {
	unset($_SESSION['cus_id']);
}
if(isset($_SESSION['cus_email'])) {
	unset($_SESSION['cus_email']);
}
if(isset($_SESSION['cus_fullname'])) {
	unset($_SESSION['cus_fullname']);
}
if (isset($_COOKIE['cusRememberMe'])) {
    unset($_COOKIE['cusRememberMe']);
    unset($_COOKIE['cus_id']);
    unset($_COOKIE['cus_email']);
    unset($_COOKIE['cus_fullname']);
    setcookie('cusRememberMe', null, -1, '/');
    setcookie('cus_id', null, -1, '/');
    setcookie('cus_email', null, -1, '/');
    setcookie('cus_fullname', null, -1, '/');
}

if(isset($_SESSION['bookingSvl'])) {
    unset($_SESSION['bookingSvl']);
}
if(isset($_SESSION['bookingPkg'])) {
    unset($_SESSION['bookingPkg']);
}
header("location:index.php");
?>