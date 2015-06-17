<?php
include('../config/config.php');
include('../common/common_header.php');

$emp_id = '';
if (hasValue($_POST['emp_id'])) {
	$emp_id = $_POST['emp_id'];
}

$sql = "SELECT 	e.emp_name, 
				e.emp_surname,
				e.emp_pic,
				p.pos_name 
		FROM 	employees e, 
				positions p 
		WHERE 	p.pos_id = e.pos_id AND 
				e.emp_id = '$emp_id' ";
$result = mysql_query($sql, $dbConn);
$rows   = mysql_num_rows($result);
if($rows > 0){
	$record = mysql_fetch_assoc($result);
	$response['status']   		= 'PASS';
	$response['emp_name'] 		= $record['emp_name'];
	$response['emp_surname'] 	= $record['emp_surname'];
	$response['emp_pic'] 		= $record['emp_pic'];
	$response['pos_name'] 		= $record['pos_name'];
	$response['dateatt_in'] 	= '';
	$response['timeatt_in'] 	= '';
	$response['dateatt_out'] 	= '';
	$response['timeatt_out'] 	= '';

	// get time attendance history
	$now = date('Y-m-d');
	$sql = "SELECT 	dateatt_in,
					timeatt_in,
					dateatt_out,
					timeatt_out 
			FROM 	time_attendances 
			WHERE 	emp_id = '$emp_id' AND 
					(
						dateatt_in = '$now' OR 
						dateatt_out = '$now' 
					)";
	$result = mysql_query($sql, $dbConn);
	$rows   = mysql_num_rows($result);
	echo mysql_error($dbConn);
	if($rows > 0) {
		$record = mysql_fetch_assoc($result);
		$response['dateatt_in'] 	= $record['dateatt_in'];
		$response['timeatt_in'] 	= $record['timeatt_in'];
		$response['dateatt_out'] 	= $record['dateatt_out'];
		$response['timeatt_out'] 	= $record['timeatt_out'];
	}

	// Get yesterday time attendance
	$yesterday = date('Y-m-d', strtotime('-1 days'));
	$sql = "SELECT 	dateatt_in,
					timeatt_in,
					dateatt_out,
					timeatt_out 
			FROM 	time_attendances 
			WHERE 	emp_id = '$emp_id' AND 
					dateatt_in = '$yesterday' AND 
					(
						dateatt_out IS NULL OR 
						timeatt_out IS NULL
					)";
	$result = mysql_query($sql, $dbConn);
	$rows   = mysql_num_rows($result);
	echo mysql_error($dbConn);
	if($rows > 0) {
		$record = mysql_fetch_assoc($result);
		$response['dateMiss'] 	= dateThaiFormat($record['dateatt_in']);
	}

	echo json_encode($response);
} else{
	$response['status']   		= 'FAIL';
	echo json_encode($response);//ทำอาร์เรย์ให้กลายเป็นเจสัน
}
?>