<?php
include('../config/config.php');
include('../common/common_header.php');

$prdtyp_ids = array();
$response = array();
if(hasValue($_POST['prdtyp_ids'])) {
	$prdtyp_ids = $_POST['prdtyp_ids'];
	$prdtyp_ids = wrapSingleQuote($prdtyp_ids);
	$sql = "SELECT 		MAX(prd_price) max,
						MIN(prd_price) min,
						AVG(prd_price) avg 
			FROM 		products 
			WHERE 		prdtyp_id IN (".implode(',', $prdtyp_ids).")";
	$result = mysql_query($sql, $dbConn);
	$row	= mysql_num_rows($result);
	if($row > 0) {
		$record = mysql_fetch_assoc($result);
		$response['maxPrice'] = $record['max'];
		$response['minPrice'] = $record['min'];
		$response['avgPrice'] = $record['avg'];
		$response['status'] = 'PASS';
		echo json_encode($response);
	} else {
		$response['status'] = 'ไม่พบข้อมูลสินค้าประเภทนี้';
		echo json_encode($response);
	}
} else {
	$response['status'] = 'ไม่มีการส่งค่ามา';
	echo json_encode($response);
}

?>