<?php
session_start();
include('../config/config.php');
include('../common/common_header.php');

$formData		= array();
parse_str($_REQUEST['formData'], $formData);

$username = '';
$password = '';
if(hasValue($formData['username'])) {
	$username = $formData['username'];
}
if(hasValue($formData['password'])) {
	$password = md5($formData['password']);
}

$sql = "SELECT 	e.emp_id,
				e.emp_email,
				e.emp_name,
				e.emp_surname,
				p.pos_name 
		FROM 	employees e, positions p 
		WHERE 	e.pos_id = p.pos_id AND 
				e.emp_email = '$username' 
				AND e.emp_pass = '$password'
		LIMIT 	1";
$result = mysql_query($sql, $dbConn);
$row	= mysql_num_rows($result);
if($row > 0) {
	$empRow = mysql_fetch_assoc($result);
	$_SESSION['loggedin'] 		= true;
	$_SESSION['emp_id'] 		= $empRow['emp_id'];
	$_SESSION['emp_email'] 		= $empRow['emp_email'];
	$_SESSION['emp_name'] 		= $empRow['emp_name'];
	$_SESSION['emp_surname'] 	= $empRow['emp_surname'];
	$_SESSION['emp_pos_name'] 	= $empRow['pos_name'];
	echo "PASS";
} else {
	echo "NOT_PASS";
}

?>