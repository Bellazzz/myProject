<?php
include('../config/config.php');
include('../common/common_header.php');

$wdwId 					= '';
$prdIdList 				= array();
$wdwdtlAmountList 		= array();
$OldPrdIdList 			= array();
$OldWdwdtlAmountList 	= array();
$allPrdIdList 			= array();
$productData 			= array();
$response 				= array(
	'status' 		=> 'PASS',
	'prdName' 		=> array(),
	'stockAmount' 	=> array(),
	'overAmount' 	=> array(),
	'unitName' 		=> array()
);

if(hasValue($_POST['wdwId'])) {
	$wdwId = $_POST['wdwId'];
}
if(hasValue($_POST['prdIdList'])) {
	$prdIdList = $_POST['prdIdList'];
}
if(hasValue($_POST['wdwdtlAmountList'])) {
	$wdwdtlAmountList = $_POST['wdwdtlAmountList'];
}

// Get old withdraw detail 
$sql = "SELECT 	prd_id,
				wdwdtl_amount 
		FROM 	withdraw_details 
		WHERE 	wdw_id = '$wdwId'";
$result = mysql_query($sql, $dbConn);
$rows	= mysql_num_rows($result);
if($rows > 0) {
	for($i=0; $i<$rows; $i++) {
		$record = mysql_fetch_assoc($result);
		array_push($OldPrdIdList, $record['prd_id']);
		$OldWdwdtlAmountList[$record['prd_id']] = $record['wdwdtl_amount'];
	}
}

// Get product data
$allPrdIdList = array_unique(array_merge($OldPrdIdList, $prdIdList));
$allPrdIdList = wrapSingleQuote($allPrdIdList);
$sql = "SELECT 	p.prd_id,
				p.prd_name,
				p.prd_amount,
				u.unit_name 
		FROM 	products p, units u 
		WHERE 	p.unit_id = u.unit_id AND 
				p.prd_id IN (".implode(',', $allPrdIdList).")";
$result = mysql_query($sql, $dbConn);
$rows	= mysql_num_rows($result);
if($rows > 0) {
	for($i=0; $i<$rows; $i++) {
		$record = mysql_fetch_assoc($result);
		$productData[$record['prd_id']] = array(
			'name' 		=> $record['prd_name'],
			'amount' 	=> $record['prd_amount'],
			'unitName' 	=> $record['unit_name']
		);
	}
}

// Check from decrease
foreach ($prdIdList as $key => $prdId) {
	if($productData[$prdId]['amount'] != '') {
		if(in_array($prdId, $OldPrdIdList)) {
			$oldAmount = $OldWdwdtlAmountList[$prdId];
		} else {
			$oldAmount = 0;
		}

		$decreaseAmount = abs($oldAmount - $wdwdtlAmountList[$key]);
		if($decreaseAmount > $productData[$prdId]['amount']) {
			$response['status'] = 'OVER';
			array_push($response['prdName'], 		$productData[$prdId]['name']);
			array_push($response['stockAmount'], 	number_format($productData[$prdId]['amount']));
			array_push($response['overAmount'], 	number_format($decreaseAmount - $productData[$prdId]['amount']));
			array_push($response['unitName'], 		$productData[$prdId]['unitName']);
		}
	}
}

echo json_encode($response);
?>