<?php
include('../config/config.php');
include('../common/common_header.php');

$prmprd_id 		= '';
$prd_id 		= '';
$startdate 		= '';
$enddate 		= '';
$prdprm_type 	= '';
$prdprmgrp_id 	= '';
if(hasValue($_POST['prmprd_id'])) {
	$prmprd_id = $_POST['prmprd_id'];
}
if(hasValue($_POST['prd_id'])) {
	$prd_id = $_POST['prd_id'];
}
if(hasValue($_POST['startdate'])) {
	$startdate = $_POST['startdate'];
}
if(hasValue($_POST['enddate'])) {
	$enddate = $_POST['enddate'];
}
if(hasValue($_POST['prdprm_type'])) {
	$prdprm_type = $_POST['prdprm_type'];
}
if(hasValue($_POST['prdprmgrp_id'])) {
	$prdprmgrp_id = $_POST['prdprmgrp_id'];
}

$sql = "SELECT 		prmprd.prmprd_startdate,
					prmprd.prmprd_enddate 
		FROM 		promotion_products prmprd, 
					product_promotions prdprm 
		WHERE 		prmprd.prdprm_id = prdprm.prdprm_id 
					AND prmprd.prd_id = '$prd_id' 
					AND prdprm.prdprm_type = '$prdprm_type' 
					AND prdprm.prdprmgrp_id = '$prdprmgrp_id' ";

if($enddate == '') {
	$sql .= " AND (
				prmprd_startdate <= '$startdate' 
			  	AND (
			  		prmprd_enddate IS NULL 
			  		OR 
			  		prmprd_enddate >= '$startdate'
			  	)
			  )";
} else {
	$sql .= " AND (
				(
					prmprd_startdate >= '$startdate' 
					AND prmprd_startdate <= '$enddate'
				)
				OR (
					prmprd_enddate IS NULL 
					OR (
						prmprd_enddate >= '$startdate' 
						AND prmprd_enddate <= '$enddate'
					)
				) 
			  )";
}
if($prmprd_id != '') {
	$sql .= " AND prmprd.prmprd_id != '$prmprd_id' ";
}
$sql .= " ORDER BY prmprd.prmprd_startdate ASC";

$result 	= mysql_query($sql, $dbConn);
$rows 		= mysql_num_rows($result);
$response 	= array();
if($rows > 0) {
	$response = array(
		'status' 		=> 'OVERLAP',
		'prdprm_type' 	=> $prdprm_type,
		'overlapList' 	=> array()
	);

	for($i=0; $i<$rows; $i++) {
		$tmpRecord = mysql_fetch_assoc($result);
		$tmpRecord['prmprd_startdate'] = dateThaiFormat($tmpRecord['prmprd_startdate']);
		if(hasValue($tmpRecord['prmprd_enddate'])) {
			$tmpRecord['prmprd_enddate'] = dateThaiFormat($tmpRecord['prmprd_enddate']);
		} else {
			$tmpRecord['prmprd_enddate'] = 'ไม่มีกำหนด';
		}

		if($tmpRecord['prmprd_startdate'] == $tmpRecord['prmprd_enddate']) {
			$prmTime = $tmpRecord['prmprd_startdate'];
		} else {
			$prmTime = $tmpRecord['prmprd_startdate'].' - '
					 . $tmpRecord['prmprd_enddate'];
		}

		array_push($response['overlapList'], $prmTime);
	}
	
	echo json_encode($response);
} else {
	$response['status'] = 'NOT_OVERLAP';
	echo json_encode($response);
}
?>