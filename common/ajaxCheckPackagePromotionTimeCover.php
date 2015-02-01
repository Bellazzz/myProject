<?php
include('../config/config.php');
include('../common/common_header.php');

$prdprm_id 		= '';
$startdate 		= '';
$enddate 		= '';
if(hasValue($_POST['prdprm_id'])) {
	$prdprm_id = $_POST['prdprm_id'];
}
if(hasValue($_POST['startdate'])) {
	$startdate = $_POST['startdate'];
}
if(hasValue($_POST['enddate'])) {
	$enddate = $_POST['enddate'];
}

$sql = "SELECT 		prmprd.prmprd_startdate,
					prmprd.prmprd_enddate 
		FROM 		product_promotions prdprm,
					promotion_products prmprd  
		WHERE 		prdprm.prdprm_id = prmprd.prdprm_id 
					AND prdprm.prdprm_id = '$prdprm_id' ";

if($enddate == '') {
	$sql .= " AND prmprd_startdate < '$startdate' ";
} else {
	$sql .= " AND (
				prmprd_startdate < '$startdate' 
				OR prmprd_startdate > '$enddate' 
				OR prmprd_enddate IS NULL 
				OR prmprd_enddate < '$startdate' 
				OR prmprd_enddate > '$enddate' 
			  )";
}
$sql .= " ORDER BY prmprd.prmprd_startdate ASC";

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

		array_push($response['notCoverList'], $prmTime);
	}
	
	echo json_encode($response);
} else {
	$response['status'] = 'COVER';
	echo json_encode($response);
}
?>