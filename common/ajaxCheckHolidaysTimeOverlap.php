<?php
include('../config/config.php');
include('../common/common_header.php');

$hld_id 		= '';
$startdate 		= '';
$enddate 		= '';
if(hasValue($_POST['hld_id'])) {
	$hld_id = $_POST['hld_id'];
}
if(hasValue($_POST['startdate'])) {
	$startdate = $_POST['startdate'];
}
if(hasValue($_POST['enddate'])) {
	$enddate = $_POST['enddate'];
}

$sql = "SELECT 		hld_name,
					hld_startdate,
					hld_enddate 
		FROM 		holidays 
		WHERE 		(
						(
							hld_startdate >= '$startdate' 
							AND hld_startdate <= '$enddate'
						)
						OR (
							hld_enddate >= '$startdate' 
							AND hld_enddate <= '$enddate'
						)
					) ";
if(hasValue($hld_id)) {
	$sql .= " AND hld_id != '$hld_id' ";
}
$sql .= " ORDER BY 	hld_startdate ASC ";

$result 	= mysql_query($sql, $dbConn);
$rows 		= mysql_num_rows($result);
$response 	= array();
if($rows > 0) {
	$response = array(
		'status' 		=> 'OVERLAP',
		'overlapList' 	=> array()
	);

	for($i=0; $i<$rows; $i++) {
		$tmpRecord = mysql_fetch_assoc($result);
		$tmpRecord['hld_startdate'] = dateThaiFormat($tmpRecord['hld_startdate']);
		if(hasValue($tmpRecord['hld_enddate'])) {
			$tmpRecord['hld_enddate'] = dateThaiFormat($tmpRecord['hld_enddate']);
		} else {
			$tmpRecord['hld_enddate'] = 'ไม่มีกำหนด';
		}

		if($tmpRecord['hld_startdate'] == $tmpRecord['hld_enddate']) {
			$prmTime = $tmpRecord['hld_name']." (".$tmpRecord['hld_startdate'].")";
		} else {
			$prmTime = $tmpRecord['hld_name']." (".$tmpRecord['hld_startdate'].' - '
					 . $tmpRecord['hld_enddate'].")";
		}

		array_push($response['overlapList'], $prmTime);
	}
	
	echo json_encode($response);
} else {
	$response['status'] = 'NOT_OVERLAP';
	echo json_encode($response);
}
?>