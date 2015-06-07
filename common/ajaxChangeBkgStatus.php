<?php
session_start();
include('../config/config.php');
include('../common/common_header.php');

if(hasValue($_POST['bkg_id']) && hasValue($_POST['status_id'])) {
	$bkg_id 	= $_POST['bkg_id'];
	$status_id  = $_POST['status_id'];

	$sql = "UPDATE 	booking 
			SET 	status_id = '$status_id' ";
	$sql .= " WHERE bkg_id = '$bkg_id'";
	$result = mysql_query($sql, $dbConn);
	if($result) {
		echo "PASS";
	} else {
		echo "FAIL";
	}
} else {
	echo "FAIL";
}
?>