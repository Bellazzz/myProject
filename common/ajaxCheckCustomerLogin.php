<?php
session_start();
include('../config/config.php');
include('../common/common_header.php');

$formData		= array();
parse_str($_REQUEST['formData'], $formData);

$email = '';
$password = '';

if(hasValue($formData['email'])) {
	$email = $formData['email'];
}
if(hasValue($formData['password'])) {
	$password = md5($formData['password']);
}

$sql = "SELECT 	cus_id,
				cus_email,
				cus_name,
				cus_surname 
		FROM 	customers 
		WHERE 	cus_email = '$email' 
				AND cus_pass = '$password'
		LIMIT 	1";
$result = mysql_query($sql, $dbConn);
$row	= mysql_num_rows($result);
if($row > 0) {
	$empRow = mysql_fetch_assoc($result);
	$_SESSION['cusLogin'] 		= true;
	$_SESSION['cus_id'] 		= $empRow['cus_id'];
	$_SESSION['cus_email'] 		= $empRow['cus_email'];
	$_SESSION['cus_fullname'] 	= $empRow['cus_name']." ".$empRow['cus_surname'];

	// Remember me
	if(isset($formData['rememberMe'])) {
		if(!isset($_COOKIE['cusRememberMe'])) {
			setcookie("cusRememberMe", true, time() + (86400 * 30), "/"); // 86400 = 1 day
			setcookie("cus_id", $_SESSION['cus_id'], time() + (86400 * 30), "/"); 
			setcookie("cus_email", $_SESSION['cus_email'], time() + (86400 * 30), "/");
			setcookie("cus_fullname", $_SESSION['cus_fullname'], time() + (86400 * 30), "/");
		}
	}
	
	echo "PASS";
} else {
	echo "NOT_PASS";
}

?>