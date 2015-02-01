<?php
include('../config/config.php');
include('../common/common_header.php');

$svlprmdtl_id 		= '';
$svl_id 		= '';
$startdate 		= '';
$enddate 		= '';
$custype_id 	= '';
if(hasValue($_POST['svlprmdtl_id'])) {
	$svlprmdtl_id = $_POST['svlprmdtl_id'];
}
if(hasValue($_POST['svl_id'])) {
	$svl_id = $_POST['svl_id'];
}
if(hasValue($_POST['startdate'])) {
	$startdate = $_POST['startdate'];
}
if(hasValue($_POST['enddate'])) {
	$enddate = $_POST['enddate'];
}
if(hasValue($_POST['custype_id'])) {
	$custype_id = $_POST['custype_id'];
}

$sql = "SELECT 		svlprmdtl.svlprmdtl_startdate,
					svlprmdtl.svlprmdtl_enddate 
		FROM 		service_list_promotion_details svlprmdtl, 
					service_list_promotions svlprm 
		WHERE 		svlprmdtl.svlprm_id = svlprm.svlprm_id 
					AND svlprmdtl.svl_id = '$svl_id' 
					AND svlprm.custype_id = '$custype_id' ";

if($enddate == '') {
	$sql .= " AND (
				svlprmdtl_startdate <= '$startdate' 
			  	AND (
			  		svlprmdtl_enddate IS NULL 
			  		OR 
			  		svlprmdtl_enddate >= '$startdate'
			  	)
			  )";
} else {
	$sql .= " AND (
				(
					svlprmdtl_startdate >= '$startdate' 
					AND svlprmdtl_startdate <= '$enddate'
				)
				OR (
					svlprmdtl_enddate IS NULL 
					OR (
						svlprmdtl_enddate >= '$startdate' 
						AND svlprmdtl_enddate <= '$enddate'
					)
				) 
			  )";
}
if($svlprmdtl_id != '') {
	$sql .= " AND svlprmdtl.svlprmdtl_id != '$svlprmdtl_id' ";
}
$sql .= " ORDER BY svlprmdtl.svlprmdtl_startdate ASC";

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
		$tmpRecord['svlprmdtl_startdate'] = dateThaiFormat($tmpRecord['svlprmdtl_startdate']);
		if(hasValue($tmpRecord['svlprmdtl_enddate'])) {
			$tmpRecord['svlprmdtl_enddate'] = dateThaiFormat($tmpRecord['svlprmdtl_enddate']);
		} else {
			$tmpRecord['svlprmdtl_enddate'] = 'ไม่มีกำหนด';
		}

		if($tmpRecord['svlprmdtl_startdate'] == $tmpRecord['svlprmdtl_enddate']) {
			$prmTime = $tmpRecord['svlprmdtl_startdate'];
		} else {
			$prmTime = $tmpRecord['svlprmdtl_startdate'].' - '
					 . $tmpRecord['svlprmdtl_enddate'];
		}

		array_push($response['overlapList'], $prmTime);
	}
	
	echo json_encode($response);
} else {
	$response['status'] = 'NOT_OVERLAP';
	echo json_encode($response);
}
?>