<?php
include('../config/config.php');
include('../common/common_header.php');

$prmds_id 		= '';
$prdprmgrp_id 	= '';
$startdate 		= '';
$enddate 		= '';
$prdtypIdList 	= array();
if(hasValue($_POST['prmds_id'])) {
	$prmds_id = $_POST['prmds_id'];
}
if(hasValue($_POST['prdprmgrp_id'])) {
	$prdprmgrp_id = $_POST['prdprmgrp_id'];
}
if(hasValue($_POST['startdate'])) {
	$startdate = $_POST['startdate'];
}
if(hasValue($_POST['enddate'])) {
	$enddate = $_POST['enddate'];
}
if(hasValue($_POST['prdtypIdList'])) {
	$prdtypIdList = wrapSingleQuote($_POST['prdtypIdList']);

}

$sql = "SELECT 		p.prmds_startdate,
					p.prmds_enddate 
		FROM 		promotion_discout_sales p,
					promotion_discout_sale_details pd 
		WHERE 		p.prmds_id = pd.prmds_id AND 
					p.prdprmgrp_id = '$prdprmgrp_id' AND 
					pd.prdtyp_id IN (".implode(',', $prdtypIdList).") ";
if($prmds_id != '') {
	$sql .= " AND p.prmds_id != '$prmds_id' ";
}
if($enddate == '') {
	$sql .= " AND (
					'$startdate' <= p.prmds_startdate OR 
			      	p.prmds_enddate IS NULL 
			  ) ";
} else {
	$sql .= " AND (
				'$startdate' >= p.prmds_startdate AND 
				'$startdate' <= p.prmds_enddate AND 
				(
					p.prmds_enddate IS NULL OR 
					(
						'$enddate' >= p.prmds_startdate AND 
						'$enddate' <= p.prmds_enddate
					)
				)
			  )";
}
$sql .= " ORDER BY p.prmds_startdate ASC";
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
		$tmpRecord['prmds_startdate'] = dateThaiFormat($tmpRecord['prmds_startdate']);
		if(hasValue($tmpRecord['prmds_enddate'])) {
			$tmpRecord['prmds_enddate'] = dateThaiFormat($tmpRecord['prmds_enddate']);
		} else {
			$tmpRecord['prmds_enddate'] = 'ไม่มีกำหนด';
		}

		if($tmpRecord['prmds_startdate'] == $tmpRecord['prmds_enddate']) {
			$prmTime = $tmpRecord['prmds_startdate'];
		} else {
			$prmTime = $tmpRecord['prmds_startdate'].' - '
					 . $tmpRecord['prmds_enddate'];
		}

		array_push($response['notCoverList'], $prmTime);
	}
	
	echo json_encode($response);
} else {
	$response['status'] = 'COVER';
	echo json_encode($response);
}
?>