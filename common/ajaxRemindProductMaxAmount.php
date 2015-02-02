<?php
include('../config/config.php');
include('../common/common_header.php');

$prdOrdList = '';
$prdIdList 	= array();
if(hasValue($_POST['prdOrdList'])) {
	$prdOrdList = $_POST['prdOrdList'];
}
foreach ($prdOrdList as $prd_id => $qty) {
	array_push($prdIdList, $prd_id);
}
$prdIdList = wrapSingleQuote($prdIdList);

// Get order product amount
$prdOldOrdAmounts = array();
$ordIdList 		  = array();
$sql = "SELECT  	o.ord_id,
					od.prd_id,
					od.orddtl_amount 
	    FROM 		orders o, 
	   				order_details od 
	   	WHERE 		o.ord_id = od.ord_id AND 
	   				o.ordstat_id != 'OS03' AND 
	   				od.prd_id IN (".implode(',', $prdIdList).")";
$result 	= mysql_query($sql, $dbConn);
$rows 		= mysql_num_rows($result);
if($rows > 0) {
	for($i=0; $i<$rows; $i++) {
		$tmpRecord = mysql_fetch_assoc($result);
		$prdOldOrdAmounts[$tmpRecord['prd_id']] = $tmpRecord['orddtl_amount'];
		array_push($ordIdList, $tmpRecord['ord_id']);
	}
	$ordIdList = wrapSingleQuote($ordIdList);
}


// Get resives product amount
$sql = "SELECT  rd.prd_id,
				rd.recdtl_amount 
		FROM 	receives r, 
				receive_details rd 
		WHERE 	r.rec_id = rd.rec_id AND 
				rd.prd_id IN (".implode(',', $prdIdList).")";
if(count($ordIdList) > 0) {
	$sql .= " AND r.ord_id IN (".implode(',', $ordIdList).") ";
}
$result = mysql_query($sql, $dbConn);
$rows 	= mysql_num_rows($result);
if($rows > 0) {
	for($i=0; $i<$rows; $i++) {
		$tmpRecord 	= mysql_fetch_assoc($result);
		if(isset($prdOldOrdAmounts[$tmpRecord['prd_id']])) {
			$prdOldOrdAmounts[$tmpRecord['prd_id']] -= $tmpRecord['recdtl_amount'];
		}
	}
}


// Check product over amount
$sql = "SELECT 		p.prd_id,
					p.prd_name,
					u.unit_name,
					p.prd_amount,
					p.prd_amount_max 
		FROM 		products p, 
					units u 
		WHERE 		p.prd_id IN (".implode(',', $prdIdList).") AND 
					p.unit_id = u.unit_id 
		ORDER BY 	p.prd_id";
$result 	= mysql_query($sql, $dbConn);
$rows 		= mysql_num_rows($result);
$response 	= array();
if($rows > 0) {
	$hasAmountOver 	= false;
	$overListHtml 	= '<ol>';

	for($i=0; $i<$rows; $i++) {
		$tmpRecord = mysql_fetch_assoc($result);
		if($tmpRecord['prd_amount'] != '') {
			$newAmount = $tmpRecord['prd_amount'] + $prdOrdList[$tmpRecord['prd_id']]['qty']; // current order
			if(isset($prdOldOrdAmounts[$tmpRecord['prd_id']])) {
				$newAmount += $prdOldOrdAmounts[$tmpRecord['prd_id']]; // old order
			}
			if($newAmount > $tmpRecord['prd_amount_max']) {
				$hasAmountOver 	= true;
				$amountOver 	= number_format($newAmount - $tmpRecord['prd_amount_max']);
				$overListHtml  .= '<li>'.$tmpRecord['prd_name'].' เกินจำนวน '.$amountOver.' '.$tmpRecord['unit_name'].'</li>';
			}
		}
	}

	$overListHtml 	.= '</ol>';

	if($hasAmountOver) {
		$response['status'] 		= 'OVER';
		$response['overListHtml'] 	= $overListHtml;
	} else {
		$response['status'] 		= 'NOT_OVER';
	}
	echo json_encode($response);
} else {
	$response['status'] = 'NOT_FOUND_PRODUCT_ID';
	echo json_encode($response);
}
?>