<?php
include('../config/config.php');
include('../common/common_header.php');

$pkgprm_id 		= '';
$startdate 		= '';
$enddate 		= '';
if(hasValue($_POST['pkgprm_id'])) {
	$pkgprm_id = $_POST['pkgprm_id'];
}
if(hasValue($_POST['startdate'])) {
	$startdate = $_POST['startdate'];
}
if(hasValue($_POST['enddate'])) {
	$enddate = $_POST['enddate'];
}

$sql = "SELECT 		pkgprmdtl.pkgprmdtl_startdate,
					pkgprmdtl.pkgprmdtl_enddate 
		FROM 		package_promotions pkgprm,
					package_promotion_details pkgprmdtl  
		WHERE 		pkgprm.pkgprm_id = pkgprmdtl.pkgprm_id 
					AND pkgprm.pkgprm_id = '$pkgprm_id' ";

if($enddate == '') {
	$sql .= " AND pkgprmdtl_startdate < '$startdate' ";
} else {
	$sql .= " AND (
				pkgprmdtl_startdate < '$startdate' 
				OR pkgprmdtl_startdate > '$enddate' 
				OR pkgprmdtl_enddate IS NULL 
				OR pkgprmdtl_enddate < '$startdate' 
				OR pkgprmdtl_enddate > '$enddate' 
			  )";
}
$sql .= " ORDER BY pkgprmdtl.pkgprmdtl_startdate ASC";

$result 	= mysql_query($sql, $dbConn);
$rows 		= mysql_num_rows($result);
$response 	= array();
if($rows > 0) {
	$response = array(
		'status' 		=> 'NOT_COVER',
		'notCoverList' 	=> array()
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

		array_push($response['notCoverList'], $prmTime);
	}
	
	echo json_encode($response);
} else {
	$response['status'] = 'COVER';
	echo json_encode($response);
}
?>