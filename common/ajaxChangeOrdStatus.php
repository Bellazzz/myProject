<?php
session_start();
include('../config/config.php');
include('../common/common_header.php');

if(hasValue($_POST['ord_id']) && hasValue($_POST['ordstat_id'])) {
	$ord_id 	= $_POST['ord_id'];
	$ordstat_id  = $_POST['ordstat_id'];

	$sql = "UPDATE 	orders 
			SET 	ordstat_id = '$ordstat_id' 
			WHERE ord_id = '$ord_id'";
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