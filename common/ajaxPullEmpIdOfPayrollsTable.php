<?php
include('../config/config.php');
include('../common/common_header.php');

$response = array();
if(hasValue($_POST['payroll_monthly'])) {
	$payroll_monthly = $_POST['payroll_monthly'];

	$sql = "SELECT 	emp_id,
					CONCAT(emp_name, ' ', emp_surname) emp_fullname 
			FROM 	employees 
			WHERE 	emp_id NOT IN 
					(
						SELECT 	emp_id 
						FROM 	payrolls 
						WHERE 	payroll_monthly = '$payroll_monthly'
					) 
			ORDER BY emp_fullname ASC";
	$result = mysql_query($sql, $dbConn);
	$rows 	= mysql_num_rows($result);
	if($rows > 0) {
		for($i=0; $i<$rows; $i++) {
			$record = mysql_fetch_assoc($result);
			array_push($response, array(
				'emp_id' => $record['emp_id'],
				'emp_fullname' => $record['emp_fullname']
			));
		}
	}
} 
echo json_encode($response);
?>