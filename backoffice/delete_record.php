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

?>