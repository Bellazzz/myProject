<?php
include('../config/config.php');
include('../common/common_header.php');

$emp_id = '';
$password = '';
if (hasValue($_POST['emp_id'])) {
	$emp_id = $_POST['emp_id'];
}
if (hasValue($_POST['password'])) {
	$password = md5($_POST['password']);
}

$sql = "SELECT 		emp_id 
		FROM 		employees 
		WHERE 		emp_id = '$emp_id' AND 
					emp_pass = '$password'";
$result = mysql_query($sql, $dbConn);
$rows   = mysql_num_rows($result);
if($rows > 0){
	echo "PASS";
} else {
	echo "FAIL";
}
?>