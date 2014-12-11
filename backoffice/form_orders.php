<?php
session_start();
$action			= isset($_REQUEST['action']) ? $_REQUEST['action'] : 'ADD';
$tableName		= 'orders';
$code			= $_REQUEST['code'];
$hideEditButton = $_REQUEST['hideEditButton'];
$hideBackButton = $_REQUEST['hideBackButton'];

include('../config/config.php');
$tplName = "form_$tableName.html";
$subDir	 = WEB_ROOTDIR.'/backoffice/';
include('../common/common_header.php');
$tableInfo = getTableInfo($tableName);

if(!$_REQUEST['ajaxCall']) {
	//1. Display form
	if($action == 'EDIT') {
		// Get table orders data
		$tableRecord = new TableSpa($tableName, $code);
		$values      = array();
		foreach($tableInfo['fieldNameList'] as $field => $value) {
			$values[$field] = $tableRecord->getFieldValue($field);
		}
		$smarty->assign('values', $values);

		// Get table order_details data
		$valuesDetail = array();
		$sql = "SELECT 	o.orddtl_id, 
						p.prd_id, 
						o.orddtl_amount,
						u.unit_name 
				FROM 	order_details o, products p, units u 
				WHERE 	o.prd_id = p.prd_id AND p.unit_id = u.unit_id 
						AND ord_id = '$code' ORDER BY orddtl_id";
		$result = mysql_query($sql, $dbConn);
		$rows 	= mysql_num_rows($result);
		for($i=0; $i<$rows; $i++) {
			array_push($valuesDetail, mysql_fetch_assoc($result));
		}
		$smarty->assign('valuesDetail', $valuesDetail);

	} else if($action == 'VIEW_DETAIL') {
		// Get table orders data
		$tableRecord = new TableSpa($tableName, $code);
		$values      = array();
		foreach($tableInfo['fieldNameList'] as $field => $value) {
			$values[$field] = $tableRecord->getFieldValue($field);
		}
		// Date thai format
		$values['ord_date_th']  	= dateThaiFormat($values['ord_date']);
		$values['ord_snd_date_th'] 	= dateThaiFormat($values['ord_snd_date']);
		$smarty->assign('values', $values);
		
		// Get detail of orders
		$orderDetailList = array();
		$sql 	= "	SELECT o.orddtl_amount,
					p.prd_id,
					p.prd_name,
					p.prd_price,
					u.unit_name 
					FROM order_details o, products p, units u 
					WHERE o.prd_id = p.prd_id AND p.unit_id = u.unit_id 
					AND o.ord_id = '$code'";
		$result = mysql_query($sql, $dbConn);
		$rows 	= mysql_num_rows($result);
		for($i=0; $i<$rows; $i++) {
			array_push($orderDetailList, mysql_fetch_assoc($result));
			$orderDetailList[$i]['no'] = $i+1;
		}
		$smarty->assign('orderDetailList', $orderDetailList);
	}

	// Get reference data for selectReferenceJS
	if(is_array($tableInfo['referenceData']) && count($tableInfo['referenceData']) > 0) {
		$sqlRefData 	= '';
		$referenceData 	= array();

		foreach ($tableInfo['referenceData'] as $key => $table) {
			switch ($table) {
				case 'employees':
					$sqlRefData = "	SELECT 		emp_id refValue,
												CONCAT(emp_name, ' ', emp_surname) refText 
									FROM 		employees 
									ORDER BY 	refText ASC";
					$refField 	= 'emp_id';
					break;

				case 'companies':
					$sqlRefData = "	SELECT 		comp_id refValue,
												comp_name refText 
									FROM 		companies 
									ORDER BY 	refText ASC";
					$refField 	= 'comp_id';
					break;

				case 'order_types':
					$sqlRefData = "	SELECT 		ordtyp_id refValue,
												ordtyp_name refText 
									FROM 		order_types 
									ORDER BY 	refText ASC";
					$refField 	= 'ordtyp_id';
					break;

				case 'order_status':
					$sqlRefData = "	SELECT 		ordstat_id refValue,
												ordstat_name refText 
									FROM 		order_status 
									ORDER BY 	refText ASC";
					$refField 	= 'ordstat_id';
					break;

				case 'products':
					$sqlRefData = "	SELECT 		prd_id refValue,
												prd_name refText,
												unit_name  
									FROM 		products p, units u 
									WHERE 		p.unit_id = u.unit_id  
									ORDER BY 	refText ASC";
					$refField 	= 'prd_id';
					break;
			}

			if(hasValue($sqlRefData)) {
				$resultRefData 	= mysql_query($sqlRefData);
				$rowsRefData 	= mysql_num_rows($resultRefData);

				if($rowsRefData > 0) {
					$referenceData[$table] = array();
					// push to referenc data
					for($i=0; $i<$rowsRefData; $i++) {
						$tmpRow 	= mysql_fetch_assoc($resultRefData);
						$refDataRow = array();

						foreach ($tmpRow as $key => $value) {
							$refDataRow[$key] = $value;
						}
						$refDataRow['refField'] = $refField;

						array_push($referenceData[$table], $refDataRow);
					}
					
				}
			}
		}
		$smarty->assign('referenceData', $referenceData);
	}

	// Hide edit button
	if($values['ordstat_id'] != 'OS01') {
		$hideEditButton = 'true';
	}

	// Check for hide edit, back button
	if($hideEditButton == 'true') {
		$smarty->assign('hideEditButton', true);
	}
	if($hideBackButton == 'true') {
		$smarty->assign('hideBackButton', true);
	}

	$smarty->assign('action', $action);
	$smarty->assign('tableName', $tableName);
	$smarty->assign('tableNameTH', $tableInfo['tableNameTH']);
	$smarty->assign('code', $code);
	include('../common/common_footer.php');
} else {
	//2. Process record
	$formData		= array();
	$values			= array();
	$fieldListEn	= array();
	parse_str($_REQUEST['formData'], $formData);
	
	// Check input required
	if(hasValue($formData['requiredFields'])) {
		$requiredFields = explode(',', $formData['requiredFields']);
		foreach($requiredFields as $key => $fieldName) {
			if(!hasValue($formData[$fieldName])) {
				$response['status'] = 'REQURIED_VALUE';
				$response['text']	= $fieldName;
				echo json_encode($response);
				exit();
			}
		}
	}

	// Check unique filed
	if(hasValue($formData['uniqueFields'])) {
		$uniqueFields = explode(',', $formData['uniqueFields']);
		foreach($uniqueFields as $key => $fieldName) {
			// Skip if value is empty
			if($formData[$fieldName] == '') {
				continue;
			}

			$value = $formData[$fieldName];
			$value = str_replace("\\\'", "'", $value);
			$value = str_replace('\\\"', '"', $value);
			$value = str_replace('\\\\"', '\\', $value);
			$value = "'$value'";

			$sql	= "SELECT $fieldName FROM $tableName WHERE $fieldName = $value AND ".$tableInfo['keyFieldName']." != '$code' LIMIT 1";
			$result	= mysql_query($sql, $dbConn);
			if(mysql_num_rows($result) > 0) {
				$response['status'] = 'UNIQUE_VALUE';
				$response['text']	= $fieldName;
				echo json_encode($response);
				exit();
			}
		}
	}
	

	// Prepare variable
	foreach($tableInfo['fieldNameList'] as $field => $value) {
		array_push($fieldListEn, $field);
	}

	if($action == 'ADD') {
		//2.1 Insert new record
		$values['fieldName']  = array();
		$values['fieldValue'] = array();

		// Push values to array
		foreach($formData as $fieldName => $value) {
			if(in_array($fieldName, $fieldListEn)) {
				// Skip if value is empty and default this field is null
				if($value == '' && is_array($tableInfo['defaultNull']) && in_array($fieldName, $tableInfo['defaultNull'])) {
					continue;
				}
				
				$value = str_replace("\\\'", "'", $value);
				$value = str_replace('\\\"', '"', $value);
				$value = str_replace('\\\\"', '\\', $value);
				array_push($values['fieldName'], $fieldName);
				array_push($values['fieldValue'], $value);
			}
		}
		// order status default is wait for transport
		array_push($values['fieldName'], 'ordstat_id');
		array_push($values['fieldValue'], 'OS01');

		// Insert Orders
		$tableRecord = new TableSpa($tableName, $values['fieldName'], $values['fieldValue']);
		if($tableRecord->insertSuccess()) {
			$insertOrdersResult = true;
		} else {
			$insertOrdersResult = false;
		}

		if($insertOrdersResult) {
			// Insert Orders detail
			$insertOrdersDetailResult = true;
			$insertOrdersDetailError  = '';
			$ord_id = $tableRecord->getKey();
			foreach ($formData['prd_id'] as $key => $prd_id) {
				$orddtl_amount 		= $formData['prd_qty'][$key];
				$orddtlValues 		= array($ord_id, $prd_id, $orddtl_amount);
				$orderDetailRecord 	= new TableSpa('order_details', $orddtlValues);
				if(!$orderDetailRecord->insertSuccess()) {
					$insertOrdersDetailResult = false;
					$insertOrdersDetailError .= 'ADD_ORDERS_DETAIL['.($key+1).']_FAIL\n';
				}
			}

			if($insertOrdersDetailResult) {
				// Add order and order_details success
				$response['status'] = 'ADD_PASS';
				echo json_encode($response);
			} else {
				$response['status'] = $insertOrdersDetailError;
				echo json_encode($response);
			}
		} else {
			$response['status'] = 'ADD_ORDERS_FAIL';
			echo json_encode($response);
		}
		

	} else if($action == 'EDIT') {
		//2.2 Update record
		$tableRecord = new TableSpa($tableName, $code);

		// Set all field value
		foreach($formData as $fieldName => $value) {
			if(in_array($fieldName, $fieldListEn)) {
				// value is empty will set default is null
				if($value == '' && is_array($tableInfo['defaultNull']) && in_array($fieldName, $tableInfo['defaultNull'])) {
					$value = 'NULL';
				}
				
				$tableRecord->setFieldValue($fieldName, $value);
			}
		}

		// Update orders
		if($tableRecord->commit()) {
			$updateOrdersResult = true;
		} else {
			$updateOrdersResult = true;
		}

		if($updateOrdersResult) {
			// Delete order_details if delete old order_details
			$oldOrderDetailList = array();
			$newOrderDetailList = array();
			// Find old order_details
			$sql = "SELECT orddtl_id FROM order_details WHERE ord_id = '$code'";
			$result = mysql_query($sql, $dbConn);
			$rows 	= mysql_num_rows($result);
			for($i=0; $i<$rows; $i++) {
				$oldOrddtlRecord = mysql_fetch_assoc($result);
				array_push($oldOrderDetailList, $oldOrddtlRecord['orddtl_id']);
			}
			// Find new order_detail
			foreach ($formData['orddtl_id'] as $key => $newOrddtl_id) {
				array_push($newOrderDetailList, $newOrddtl_id);
			}
			// Check for delete 
			foreach ($oldOrderDetailList as $key => $oldOrddtl_id) {
				if(!in_array($oldOrddtl_id, $newOrderDetailList)) {
					// Delete order_details
					$orderDetailRecord 	= new TableSpa('order_details', $oldOrddtl_id);
					if(!$orderDetailRecord->delete()) {
						$updateOrdersDetailResult = false;
						$updateOrdersDetailError .= "DELETE_ORDERS_DETAIL[$oldOrddtl_id]_FAIL\n";
					}
				}
			}

			
			// Update or Add order_details
			$updateOrdersDetailResult = true;
			$updateOrdersDetailError  = '';

			foreach ($formData['prd_id'] as $key => $prd_id) {
				$orddtl_amount 	= $formData['prd_qty'][$key];

				if(isset($formData['orddtl_id'][$key])) {
					// Update order_details
					$orddtl_id = $formData['orddtl_id'][$key];
					$orderDetailRecord 	= new TableSpa('order_details', $orddtl_id);
					$orderDetailRecord->setFieldValue('prd_id', $prd_id);
					$orderDetailRecord->setFieldValue('orddtl_amount', $orddtl_amount);
					if(!$orderDetailRecord->commit()) {
						$updateOrdersDetailResult = false;
						$updateOrdersDetailError .= 'EDIT_ORDERS_DETAIL['.($key+1).']_FAIL\n';
					}
				} else {
					// Add new order_details
					$orddtlValues 		= array($code, $prd_id, $orddtl_amount);
					$orderDetailRecord 	= new TableSpa('order_details', $orddtlValues);
					if(!$orderDetailRecord->insertSuccess()) {
						$updateOrdersDetailResult = false;
						$updateOrdersDetailError .= 'ADD_ORDERS_DETAIL['.($key+1).']_FAIL\n';
					}
				}
			}

			if($updateOrdersDetailResult) {
				// Edit order and order_details success
				$response['status'] = 'EDIT_PASS';
				echo json_encode($response);
			} else {
				// Edit order_details fail
				$response['status'] = $updateOrdersDetailError;
				echo json_encode($response);
			}

		} else {
			// Edit orders fail
			$response['status'] = 'EDIT_ORDERS_FAIL';
			echo json_encode($response);
		}


	}
}
?>