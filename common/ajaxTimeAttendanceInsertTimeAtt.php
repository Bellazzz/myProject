<?php
include('../config/config.php');
include('../common/common_header.php');

$emp_id 	= '';
$dateNow 	= date('Y-m-d');
$timeNow 	= date('H:i:s');
if (hasValue($_POST['emp_id'])) {
	$emp_id = $_POST['emp_id'];
}

$sql = "SELECT 		timeatt_id 
		FROM 		time_attendances 
		WHERE 		emp_id = '$emp_id' AND 
					dateatt_in = '$dateNow'";
$result = mysql_query($sql, $dbConn);
$rows   = mysql_num_rows($result);
if($rows > 0){
	// Update clock out
	$record 		= mysql_fetch_assoc($result);
	$timeAttRecord 	= new TableSpa('time_attendances', $record['timeatt_id']);
	$timeAttRecord->setFieldValue('dateatt_out', $dateNow);
	$timeAttRecord->setFieldValue('timeatt_out', $timeNow);
	if($timeAttRecord->commit()) {
		echo "PASS";
	} else {
		echo "FAIL";
	}
} else {
	// Insert new record
	$timeAttFields = array('emp_id','dateatt_in','timeatt_in');
	$timeAttValues = array($emp_id, $dateNow, $timeNow);
	$timeAttRecord = new TableSpa('time_attendances', $timeAttFields, $timeAttValues);
	if($timeAttRecord->insertSuccess()) {
		echo "PASS";
	} else {
		echo "FAIL";
	}
}
?>