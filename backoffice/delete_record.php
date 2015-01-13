<?php
if(!isset($keySelected) || $keySelected == '') {
	echo 'NO_RECORD_SELECTED';
	exit();
}

include('../common/class_database.php');
include('../common/common_function.php');

$tableInfo	= getTableInfo($tableName);
$keyName	= $tableInfo['keyFieldName'];
$keyType	= $tableInfo['keyFieldType'];

// Delete detail
if($tableName == 'packages') {
	foreach($keySelected as $index => $pkg_id) {
		$sql = "DELETE FROM package_service_lists WHERE pkg_id = '$pkg_id'";
		$result = mysql_query($sql, $dbConn);
		if(!$result) {
			$err = mysql_error($dbConn);
			echo "DELETE_PACKAGE_SERVICE_LISTS_FAIL : $err";
			exit();
		}
	}
} else if($tableName == 'shops') {
	foreach($keySelected as $index => $shop_id) {
		$sql = "DELETE FROM shop_display_products WHERE shop_id = '$shop_id'";
		$result = mysql_query($sql, $dbConn);
		if(!$result) {
			$err = mysql_error($dbConn);
			echo "DELETE_SHOP_DISPLAY_PRODUCTS_FAIL : $err";
			exit();
		}
	}
} else if($tableName == 'promotion_discout_sales') {
	foreach($keySelected as $index => $prmds_id) {
		$sql = "DELETE FROM promotion_discout_sale_details WHERE prmds_id = '$prmds_id'";
		$result = mysql_query($sql, $dbConn);
		if(!$result) {
			$err = mysql_error($dbConn);
			echo "DELETE_PROMOTION_DISCOUT_SALE_DETAILS_FAIL : $err";
			exit();
		}
	}
} else if($tableName == 'orders') {
	foreach($keySelected as $index => $ord_id) {
		$sql = "SELECT orddtl_id FROM order_details WHERE ord_id = '$ord_id'";
		$result = mysql_query($sql, $dbConn);
		$rows 	= mysql_num_rows($result);
		for($i=0; $i<$rows; $i++) {
			$resultRow = mysql_fetch_assoc($result);
			$orddtl_id = $resultRow['orddtl_id'];
			$orderDetailRecord = new TableSpa('order_details', $orddtl_id);
			if(!$orderDetailRecord->delete()) {
				$err = mysql_error($dbConn);
				echo "DELETE_ORDER_DETAIL_FAIL : $err";
				exit();
			}
		}
	}
} else if($tableName == 'receives') {
	foreach($keySelected as $index => $rec_id) {
		$sql = "SELECT recdtl_id, prd_id, recdtl_amount FROM receive_details WHERE rec_id = '$rec_id'";
		$result = mysql_query($sql, $dbConn);
		$rows 	= mysql_num_rows($result);
		for($i=0; $i<$rows; $i++) {
			// Delete receive_details
			$resultRow = mysql_fetch_assoc($result);
			$recdtl_id = $resultRow['recdtl_id'];
			$receiveDetailRecord = new TableSpa('receive_details', $recdtl_id);
			if($receiveDetailRecord->delete()) {
				// Remove product amount
				$rmPrd_id = $resultRow['prd_id'];
				$rmAmount = (int)$resultRow['recdtl_amount'];
				$prdRecord = new TableSpa('products', $rmPrd_id);
				$prdAmount = (int)$prdRecord->getFieldValue('prd_amount');
				$newAmount = (int)$prdAmount - $rmAmount;
				$prdRecord->setFieldValue('prd_amount', "$newAmount");
				if(!$prdRecord->commit()) {
					$err = mysql_error($dbConn);
					echo "REMOVE_PRODUCT_AMOUNT_FAIL : $err";
					exit();
				}
			} else {
				$err = mysql_error($dbConn);
				echo "DELETE_RECEIVE_DETAIL_FAIL : $err";
				exit();
			}
		}

		// Delete receive
		$receivesRecord = new TableSpa('receives', $rec_id);
		$ord_id 		= $receivesRecord->getFieldValue('ord_id');
		if(!$receivesRecord->delete()) {
			$err = mysql_error($dbConn);
			echo "DELETE_RECEIVES_FAIL : $err";
		}
	}

	

	// Update status of orders
	$sql 					= "SELECT rec_id FROM receives WHERE ord_id = '$ord_id'";
	$updateStatOrdResult 	= mysql_query($sql, $dbConn);
	$updateStatOrdRows 	 	= mysql_num_rows($updateStatOrdResult);
	if($updateStatOrdRows > 0) {
		$ordstat_id = 'OS02';
	} else {
		$ordstat_id = 'OS01';
	}
	$ordersRecord 			= new TableSpa('orders', $ord_id);
	$ordersRecord->setFieldValue('ordstat_id', $ordstat_id);
	if(!$ordersRecord->commit()) {
		$err = mysql_error($dbConn);
		echo "UPDATE_STATUS_ORDERS_FAIL : $err";
		exit();
	}

	echo "PASS";
	exit();
} else if($tableName == 'withdraws') {
	foreach($keySelected as $index => $wdw_id) {
		$allPrdIdList 			= array();
		$allAmountList 			= array();
		$productData 			= array();
		$increaseShelfAmount 	= 0;
		$wdwRecord 				= new TableSpa('withdraws', $wdw_id);
		$wdwtyp_id 				= $wdwRecord->getFieldValue('wdwtyp_id');
		$wdwtypRecord 			= new TableSpa('withdraw_types', $wdwtyp_id);
		$increaseShelfAmount 	= $wdwtypRecord->getFieldValue('wdwtyp_increase_shelf_amount');


		// Delete withdraw details
		$sql = "SELECT wdwdtl_id, prd_id, wdwdtl_amount FROM withdraw_details WHERE wdw_id = '$wdw_id'";
		$result = mysql_query($sql, $dbConn);
		$rows 	= mysql_num_rows($result);
		for($i=0; $i<$rows; $i++) {
			$resultRow 		= mysql_fetch_assoc($result);
			$wdwdtl_id 		= $resultRow['wdwdtl_id'];
			$wdwdtlRecord 	= new TableSpa('withdraw_details', $wdwdtl_id);
			array_push($allPrdIdList, 	$resultRow['prd_id']);
			array_push($allAmountList, 	$resultRow['wdwdtl_amount']);
			if(!$wdwdtlRecord->delete()) {
				$err = mysql_error($dbConn);
				echo "DELETE_ORDER_DETAIL_FAIL : $err";
				exit();
			}
		}

		// Get product data
		getProductData($allPrdIdList);

		// Remove product amount
		foreach ($allPrdIdList as $key => $prd_id) {
			removeProductAmount($prd_id, $allAmountList[$key]);
		}
	}
} else if($tableName == 'sales') {
	foreach($keySelected as $index => $sale_id) {
		// Find sale detail id
		$saledtlIdList 	= array();
		$prdList 		= array();
		$amountList 	= array();
		$sql 	= "SELECT saledtl_id, prd_id, saledtl_amount FROM sale_details WHERE sale_id = '$sale_id'";
		$result = mysql_query($sql, $dbConn);
		$rows 	= mysql_num_rows($result);
		for($i=0; $i<$rows; $i++) {
			$record = mysql_fetch_assoc($result);
			array_push($saledtlIdList, $record['saledtl_id']);
			array_push($prdList, $record['prd_id']);
			array_push($amountList, $record['saledtl_amount']);
		}
		$saledtlIdList = wrapSingleQuote($saledtlIdList);

		// Delete sale_promotion_details
		$sql 	= "DELETE FROM sale_promotion_details WHERE saledtl_id IN (".implode(',', $saledtlIdList).")";
		$result = mysql_query($sql, $dbConn);
		if(!$result) {
			$err = mysql_error($dbConn);
			echo "DELETE_SALE_PROMOTION_DETAIL_FAIL : $err";
			exit();
		}

		// Delete sale_details
		$sql 	= "DELETE FROM sale_details WHERE saledtl_id IN (".implode(',', $saledtlIdList).")";
		$result = mysql_query($sql, $dbConn);
		if(!$result) {
			$err = mysql_error($dbConn);
			echo "DELETE_SALE_DETAIL_FAIL : $err";
			exit();
		}

		// Increase product shelf amount
		foreach ($prdList as $key => $prd_id) {
			$prdRecord 		= new TableSpa('products', $prd_id);
			$oldShelfAmount = $prdRecord->getFieldValue('prd_shelf_amount');
			if($oldShelfAmount != '') {
				// Do Increase
				$newShelfAmount = $oldShelfAmount + $amountList[$key];
				$prdRecord->setFieldValue('prd_shelf_amount', $newShelfAmount);
				if(!$prdRecord->commit()) {
					$err = mysql_error($dbConn);
					echo "INCREASE_PRODUCT_SHELF_AMOUNT_FAIL : $err";
					exit();
				}
			}
		}
		// End Increase product shelf amount
	}
}


// Add single quote
foreach($keySelected as $index => $value) {
	$keySelected[$index] = "'$value'";
}

// Delete record
$sql = "DELETE FROM $tableName WHERE $keyName in (" . implode($keySelected, ',') . ')';

try {
	if(mysql_query($sql, $dbConn)) {
		echo "PASS";
	} else {
		throw new Exception(mysql_error(), mysql_errno());
	}
} catch (Exception $e) {
    if ($e->getCode() == 1451) {
        echo "DELETE_REFERENCE";
    }
}


/* 
 * Function for delete withdraws table
 */
function getProductData($allPrdIdList) {
	global $productData, $dbConn;

	$allPrdIdList = wrapSingleQuote($allPrdIdList);
	$sql = "SELECT 	prd_id,
					prd_amount,
					prd_shelf_amount 
			FROM 	products p 
			WHERE 	prd_id IN (".implode(',', $allPrdIdList).")";
	$result = mysql_query($sql, $dbConn);
	$rows	= mysql_num_rows($result);
	if($rows > 0) {
		for($i=0; $i<$rows; $i++) {
			$record = mysql_fetch_assoc($result);
			$productData[$record['prd_id']] = array(
				'amount' 			=> $record['prd_amount'],
				'shelf_amount' 		=> $record['prd_shelf_amount']
			);
		}
	}
}

function removeProductAmount($prd_id, $oldAmount) {
	global 	$productData, $increaseShelfAmount, $dbConn;
	
	if($productData[$prd_id]['amount'] != '' && $productData[$prd_id]['shelf_amount'] != '') {
		$newPrdAmount = $productData[$prd_id]['amount'] + $oldAmount;

		// Remove product amount
		$sql = "UPDATE 	products 
				SET 	prd_amount = '$newPrdAmount' 
				WHERE 	prd_id = '$prd_id'";
		$result = mysql_query($sql, $dbConn);
		if(!$result) {
			$err = mysql_error($dbConn);
			echo "REMOVE_PRODUCT_AMOUNT_FAIL : $err";
			exit();
		}

		if($increaseShelfAmount) {
			$newPrdShelfAmount = $productData[$prd_id]['shelf_amount'] - $oldAmount;

			// Update product shelf amount
			$sql = "UPDATE 	products 
					SET 	prd_shelf_amount = '$newPrdShelfAmount' 
					WHERE 	prd_id = '$prd_id'";
			$result = mysql_query($sql, $dbConn);
			if(!$result) {
				$err = mysql_error($dbConn);
				echo "REMOVE_PRODUCT_SHELF_AMOUNT_FAIL : $err";
				exit();
			}
		}
	}
}
?>