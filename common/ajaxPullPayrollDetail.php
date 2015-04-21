<?php
include('../config/config.php');
include('../common/common_header.php');

$response = array(
	'emp_salary' => 0,
	'sumCommission' => 0,
	'sumOvertime' => 0
);

if(hasValue($_POST['emp_id']) && hasValue($_POST['payroll_monthly'])) {
	$emp_id = $_POST['emp_id'];
	$payroll_monthly = $_POST['payroll_monthly'];

	// Find employee salary
	$sql = "SELECT 	emp_salary 
			FROM 	employees 
			WHERE 	emp_id = '$emp_id'";
	$result = mysql_query($sql, $dbConn);
	$rows 	= mysql_num_rows($result);
	if($rows > 0) {
		for($i=0; $i<$rows; $i++) {
			$record = mysql_fetch_assoc($result);
			$response['emp_salary'] = $record['emp_salary'];
		}
	}
} 
echo json_encode($response);
?>