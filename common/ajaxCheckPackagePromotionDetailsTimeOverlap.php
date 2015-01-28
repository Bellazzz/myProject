<?php
include('../config/config.php');
include('../common/common_header.php');

$pkgprm_id 		= '';
$pkg_id 		= '';
$startdate 		= '';
$enddate 		= '';
$custype_id 	= '';
if(hasValue($_POST['pkgprm_id'])) {
	$pkgprm_id = $_POST['pkgprm_id'];
}
if(hasValue($_POST['pkg_id'])) {
	$pkg_id = $_POST['pkg_id'];
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

$sql = "SELECT 		pkgprmdtl.pkgprmdtl_startdate,
					pkgprmdtl.pkgprmdtl_enddate 
		FROM 		package_promotion_details pkgprmdtl, 
					package_promotions pkgprm 
		WHERE 		pkgprmdtl.pkgprm_id = pkgprm.pkgprm_id 
					AND pkgprmdtl.pkg_id = '$pkg_id' 
					AND pkgprm.custype_id = '$custype_id' ";

if($enddate == '') {
	$sql .= " AND (
				pkgprmdtl_startdate <= '$startdate' 
			  	AND (
			  		pkgprmdtl_enddate IS NULL 
			  		OR 
			  		pkgprmdtl_enddate >= '$startdate'
			  	)
			  )";
} else {
	$sql .= " AND (
				(
					pkgprmdtl_startdate >= '$startdate' 
					AND pkgprmdtl_startdate <= '$enddate'
				)
				OR (
					pkgprmdtl_enddate IS NULL 
					OR (
						pkgprmdtl_enddate >= '$startdate' 
						AND pkgprmdtl_enddate <= '$enddate'
					)
				) 
			  )";
}
if($pkgprm_id != '') {
	$sql .= " AND pkgprmdtl.pkgprm_id != '$pkgprm_id' ";
}
$sql .= " ORDER BY pkgprmdtl.pkgprmdtl_startdate ASC";

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
		$tmpRecord['pkgprmdtl_startdate'] = dateThaiFormat($tmpRecord['pkgprmdtl_startdate']);
		if(hasValue($tmpRecord['pkgprmdtl_enddate'])) {
			$tmpRecord['pkgprmdtl_enddate'] = dateThaiFormat($tmpRecord['pkgprmdtl_enddate']);
		} else {
			$tmpRecord['pkgprmdtl_enddate'] = 'ไม่มีกำหนด';
		}

		if($tmpRecord['pkgprmdtl_startdate'] == $tmpRecord['pkgprmdtl_enddate']) {
			$prmTime = $tmpRecord['pkgprmdtl_startdate'];
		} else {
			$prmTime = $tmpRecord['pkgprmdtl_startdate'].' - '
					 . $tmpRecord['pkgprmdtl_enddate'];
		}

		array_push($response['overlapList'], $prmTime);
	}
	
	echo json_encode($response);
} else {
	$response['status'] = 'NOT_OVERLAP';
	echo json_encode($response);
}
?>