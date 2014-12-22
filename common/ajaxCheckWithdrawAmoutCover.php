<?php
include('../config/config.php');
include('../common/common_header.php');

$wdwId 					= '';
$wdwtyp_id 				= '';
$prdIdList 				= array();
$wdwdtlAmountList 		= array();
$OldPrdIdList 			= array();
$OldWdwdtlAmountList 	= array();
$allPrdIdList 			= array();
$productData 			= array();
$increaseShelfAmount 	= 0;
$response 				= array(
	'status' 		=> 'PASS',
	'overAmountList' 		=> array(),
	'overShelfAmountList' 	=> array()
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
if(hasValue($_POST['wdwtyp_id'])) {
	$wdwtyp_id = $_POST['wdwtyp_id'];
} else {
	// Get withdraws type id
	$wdwtypRecord = new TableSpa('withdraws', $wdwId);
	if($wdwtypRecord != null) {
		$wdwtyp_id = $wdwtypRecord->getFieldValue('wdwtyp_id');
	}
}

// Get increase shelf amount
$sql = "SELECT 		wdwtyp_increase_shelf_amount 
		FROM 		withdraw_types 
		WHERE 		wdwtyp_id = '$wdwtyp_id' LIMIT 1";
$result = mysql_query($sql, $dbConn);
$rows 	= mysql_num_rows($result);
if($rows > 0) {
	$record = mysql_fetch_assoc($result);
	$increaseShelfAmount = $record['wdwtyp_increase_shelf_amount'];
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
				p.prd_shelf_amount,
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
			'name' 			=> $record['prd_name'],
			'amount' 		=> $record['prd_amount'],
			'shelf_amount' 	=> $record['prd_shelf_amount'],
			'unitName' 		=> $record['unit_name']
		);
	}
}

// Check from add or update
foreach ($prdIdList as $key => $prdId) {
	if($productData[$prdId]['amount'] != '') {
		if(in_array($prdId, $OldPrdIdList)) {
			$oldAmount = $OldWdwdtlAmountList[$prdId];
		} else {
			$oldAmount = 0;
		}

		$resultAmount = abs($oldAmount - $wdwdtlAmountList[$key]);

		if($wdwdtlAmountList[$key] >= $oldAmount) {
			// Check product amount
			if($resultAmount > $productData[$prdId]['amount']) {
				$response['status'] = 'OVER';
				array_push($response['overAmountList'], array(
					'prdName' 		=> $productData[$prdId]['name'],
					'stockAmount' 	=> number_format($productData[$prdId]['amount']),
					'overAmount' 	=> number_format($resultAmount - $productData[$prdId]['amount']),
					'unitName' 		=> $productData[$prdId]['unitName']
				));
			}
		} else {
			// Check product shelf amount
			if($increaseShelfAmount && $resultAmount > $productData[$prdId]['shelf_amount']) {
				$response['status'] = 'OVER';
				array_push($response['overShelfAmountList'], array(
					'prdName' 		=> $productData[$prdId]['name'],
					'stockAmount' 	=> number_format($productData[$prdId]['shelf_amount']),
					'overAmount' 	=> number_format($resultAmount - $productData[$prdId]['shelf_amount']),
					'unitName' 		=> $productData[$prdId]['unitName']
				));
			}
		}
	}
}

// Check from delete
foreach ($OldPrdIdList as $key => $prdId) {
	if($productData[$prdId]['amount'] != '' && $increaseShelfAmount) {
		if(!in_array($prdId, $prdIdList)) {
			// Check product shelf amount
			if($OldWdwdtlAmountList[$prdId] > $productData[$prdId]['shelf_amount']) {
				$response['status'] = 'OVER';
				array_push($response['overShelfAmountList'], array(
					'prdName' 		=> $productData[$prdId]['name'],
					'stockAmount' 	=> number_format($productData[$prdId]['shelf_amount']),
					'overAmount' 	=> number_format($OldWdwdtlAmountList[$prdId] - $productData[$prdId]['shelf_amount']),
					'unitName' 		=> $productData[$prdId]['unitName']
				));
			}
		}
	}
}

echo json_encode($response);
?>