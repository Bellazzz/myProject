<?php
include('../config/config.php');
include('../common/common_header.php');

$bkg_id = '';
$ser_date = '';

if(hasValue($_POST['bkg_id'])) {
	$bkg_id = $_POST['bkg_id'];
}
if(hasValue($_POST['ser_date'])) {
	$ser_date = $_POST['ser_date'];
}

$bkgDetails = array(
	'pkg' => array(),
	'svl' => array()
);

// Get booking packages
$sql = "SELECT 		pkg_id,
					emp_id,
					SUBSTRING(bkgpkg_time,1,5) bkgpkg_time 
		FROM 		booking_packages 
		WHERE 		bkg_id = '$bkg_id' AND 
					bkgpkg_status != 2 AND 
					bkgpkg_date = '$ser_date'";
$result = mysql_query($sql, $dbConn);
$rows 	= mysql_num_rows($result);
if($rows > 0) {
	for($i=0; $i<$rows; $i++) {
		array_push($bkgDetails['pkg'], mysql_fetch_assoc($result));
	}
}

// Get booking service_lists
$sql = "SELECT 		svl_id,
					emp_id,
					SUBSTRING(bkgsvl_time,1,5) bkgsvl_time 
		FROM 		booking_service_lists 
		WHERE 		bkg_id = '$bkg_id' AND 
					bkgsvl_status != 2 AND 
					bkgsvl_date = '$ser_date'";
$result = mysql_query($sql, $dbConn);
$rows 	= mysql_num_rows($result);
if($rows > 0) {
	for($i=0; $i<$rows; $i++) {
		array_push($bkgDetails['svl'], mysql_fetch_assoc($result));
	}
}

echo json_encode($bkgDetails);
?>