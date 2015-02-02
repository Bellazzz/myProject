<?php
include('../config/config.php');
include('../common/common_header.php');

$svlprm_id 		= '';
$startdate 		= '';
$enddate 		= '';
if(hasValue($_POST['svlprm_id'])) {
	$svlprm_id = $_POST['svlprm_id'];
}
if(hasValue($_POST['startdate'])) {
	$startdate = $_POST['startdate'];
}
if(hasValue($_POST['enddate'])) {
	$enddate = $_POST['enddate'];
}

$sql = "SELECT 		svlprmdtl.svlprmdtl_startdate,
					svlprmdtl.svlprmdtl_enddate 
		FROM 		service_list_promotions svlprm,
					service_list_promotion_details svlprmdtl  
		WHERE 		svlprm.svlprm_id = svlprmdtl.svlprm_id 
					AND svlprm.svlprm_id = '$svlprm_id' ";

if($enddate == '') {
	$sql .= " AND svlprmdtl_startdate < '$startdate' ";
} else {
	$sql .= " AND (
				svlprmdtl_startdate < '$startdate' 
				OR svlprmdtl_startdate > '$enddate' 
				OR svlprmdtl_enddate IS NULL 
				OR svlprmdtl_enddate < '$startdate' 
				OR svlprmdtl_enddate > '$enddate' 
			  )";
}
$sql .= " ORDER BY svlprmdtl.svlprmdtl_startdate ASC";

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

		array_push($response['notCoverList'], $prmTime);
	}
	
	echo json_encode($response);
} else {
	$response['status'] = 'COVER';
	echo json_encode($response);
}
?>