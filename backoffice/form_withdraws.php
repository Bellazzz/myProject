<?php
session_start();
$action			= isset($_REQUEST['action']) ? $_REQUEST['action'] : 'ADD';
$tableName		= 'withdraws';
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
		// Get table withdraws data
		$tableRecord = new TableSpa($tableName, $code);
		$values      = array();
		foreach($tableInfo['fieldNameList'] as $field => $value) {
			$values[$field] = $tableRecord->getFieldValue($field);
		}
		$smarty->assign('values', $values);

		// Get table withdraw_details data
		$valuesDetail = array();
		$sql = "SELECT 	w.wdwdtl_id, 
						p.prd_id, 
						w.wdwdtl_amount,
						u.unit_name 
				FROM 	withdraw_details w, products p, units u 
				WHERE 	w.prd_id = p.prd_id AND p.unit_id = u.unit_id 
						AND wdw_id = '$code' ORDER BY wdwdtl_id";
		$result = mysql_query($sql, $dbConn);
		$rows 	= mysql_num_rows($result);
		for($i=0; $i<$rows; $i++) {
			array_push($valuesDetail, mysql_fetch_assoc($result));
		}
		$smarty->assign('valuesDetail', $valuesDetail);

	} else if($action == 'VIEW_DETAIL') {
		// Get table withdraws data
		$tableRecord = new TableSpa($tableName, $code);
		$values      = array();
		foreach($tableInfo['fieldNameList'] as $field => $value) {
			$values[$field] = $tableRecord->getFieldValue($field);
		}
		// Date thai format
		$values['wdw_date_th']  	= dateThaiFormat($values['wdw_date']);
		$smarty->assign('values', $values);
		
		// Get detail of withdraws
		$withdrawDetailList = array();
		$sql 	= "	SELECT w.wdwdtl_amount,
					p.prd_id,
					p.prd_name,
					p.prd_price,
					u.unit_name 
					FROM withdraw_details w, products p, units u 
					WHERE w.prd_id = p.prd_id AND p.unit_id = u.unit_id 
					AND w.wdw_id = '$code'";
		$result = mysql_query($sql, $dbConn);
		$rows 	= mysql_num_rows($result);
		for($i=0; $i<$rows; $i++) {
			array_push($withdrawDetailList, mysql_fetch_assoc($result));
			$withdrawDetailList[$i]['no'] = $i+1;
		}
		$smarty->assign('withdrawDetailList', $withdrawDetailList);
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

				case 'services':
					$sqlRefData = "	SELECT 		ser_id refValue,
												ser_id refText 
									FROM 		services 
									ORDER BY 	refText DESC";
					$refField 	= 'ser_id';
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

				case 'withdraw_types':
					$sqlRefData = "	SELECT 		wdwtyp_id refValue,
												wdwtyp_name refText   
									FROM 		withdraw_types 
									ORDER BY 	refText ASC";
					$refField 	= 'wdwtyp_id';
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
	$OldPrdIdList 			= array();
	$OldWdwdtlAmountList 	= array();
	$allPrdIdList 			= array();
	$productData 			= array();
	$increaseShelfAmount 	= 0;
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

	// Find increase shelf amount
	$sql = "SELECT 		wdwtyp_increase_shelf_amount 
			FROM 		withdraw_types 
			WHERE 		wdwtyp_id = '".$formData['wdwtyp_id']."' LIMIT 1";
	$result = mysql_query($sql, $dbConn);
	$rows 	= mysql_num_rows($result);
	if($rows > 0) {
		$record = mysql_fetch_assoc($result);
		$increaseShelfAmount = $record['wdwtyp_increase_shelf_amount'];
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

		// Insert withdraws
		$tableRecord = new TableSpa($tableName, $values['fieldName'], $values['fieldValue']);
		if($tableRecord->insertSuccess()) {
			$insertWithdrawsResult = true;
		} else {
			$insertWithdrawsResult = false;
		}

		if($insertWithdrawsResult) {
			/* Insert withdraws detail */
			// Get product data
			getProductData(); 

			$insertWithdrawsDetailResult = true;
			$insertWithdrawsDetailError  = '';
			$wdw_id = $tableRecord->getKey();
			foreach ($formData['prd_id'] as $key => $prd_id) {
				$wdwdtl_amount 		= $formData['wdwdtl_amount'][$key];
				$wdwdtlValues 		= array($wdw_id, $prd_id, $wdwdtl_amount);
				$withdrawDetailRecord 	= new TableSpa('withdraw_details', $wdwdtlValues);
				if(!$withdrawDetailRecord->insertSuccess()) {
					$insertWithdrawsDetailResult = false;
					$insertWithdrawsDetailError .= 'ADD_WITHDRAW_DETAIL['.($key+1).']_FAIL\n';
				}

				// Update product amount
				updateProductAmount($prd_id, $wdwdtl_amount, $insertWithdrawsDetailResult, $insertWithdrawsDetailError);
			}

			if($insertWithdrawsDetailResult) {
				// Add withdraw and withdraw_details success
				$response['status'] = 'ADD_PASS';
				echo json_encode($response);
			} else {
				$response['status'] = $insertWithdrawsDetailError;
				echo json_encode($response);
			}
		} else {
			$response['status'] = 'ADD_WITHDRAW_FAIL';
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

		// Update withdraws
		if($tableRecord->commit()) {
			$updateWithdrawsResult = true;
		} else {
			$updateWithdrawsResult = true;
		}

		if($updateWithdrawsResult) {
			// Delete withdraw_details if delete old withdraw_details
			$oldWithdrawsDetailList = array();
			$newWithdrawsDetailList = array();
			// Find old withdraw_details
			$sql = "SELECT wdwdtl_id, prd_id, wdwdtl_amount FROM withdraw_details WHERE wdw_id = '$code'";
			$result = mysql_query($sql, $dbConn);
			$rows 	= mysql_num_rows($result);
			for($i=0; $i<$rows; $i++) {
				$oldWdwdtlRecord = mysql_fetch_assoc($result);
				array_push($oldWithdrawsDetailList, $oldWdwdtlRecord['wdwdtl_id']);
				array_push($OldPrdIdList, $oldWdwdtlRecord['prd_id']);
				$OldWdwdtlAmountList[$oldWdwdtlRecord['prd_id']] = $oldWdwdtlRecord['wdwdtl_amount'];
			}

			// Get product data
			getProductData(); 

			// Find new withdraw_details
			foreach ($formData['wdwdtl_id'] as $key => $newWdwdtl_id) {
				array_push($newWithdrawsDetailList, $newWdwdtl_id);
			}
			// Check for delete 
			foreach ($oldWithdrawsDetailList as $key => $oldWdwdtl_id) {
				if(!in_array($oldWdwdtl_id, $newWithdrawsDetailList)) {
					// Delete withdraw_details
					$withdrawDetailRecord 	= new TableSpa('withdraw_details', $oldWdwdtl_id);
					$prd_id 				= $withdrawDetailRecord->getFieldValue('prd_id');
					if(!$withdrawDetailRecord->delete()) {
						$updateWithdrawsDetailResult = false;
						$updateWithdrawsDetailError .= "DELETE_WITHDRAWS_DETAIL[$oldWdwdtl_id]_FAIL\n";
					}

					// Remove product amount
					removeProductAmount($prd_id, $updateWithdrawsDetailResult, $updateWithdrawsDetailError);
				}
			}

			// Update or Add withdraw_details
			$updateWithdrawsDetailResult = true;
			$updateWithdrawsDetailError  = '';

			foreach ($formData['prd_id'] as $key => $prd_id) {
				$wdwdtl_amount 	= $formData['wdwdtl_amount'][$key];

				if(isset($formData['wdwdtl_id'][$key])) {
					// Update withdraw_details
					$wdwdtl_id = $formData['wdwdtl_id'][$key];
					$withdrawDetailRecord 	= new TableSpa('withdraw_details', $wdwdtl_id);
					$withdrawDetailRecord->setFieldValue('prd_id', $prd_id);
					$withdrawDetailRecord->setFieldValue('wdwdtl_amount', $wdwdtl_amount);
					if(!$withdrawDetailRecord->commit()) {
						$updateWithdrawsDetailResult = false;
						$updateWithdrawsDetailError .= 'EDIT_WITHDRAWS_DETAIL['.($key+1).']_FAIL\n';
					}
				} else {
					// Add new withdraw_details
					$wdwdtlValues 		= array($code, $prd_id, $wdwdtl_amount);
					$withdrawDetailRecord 	= new TableSpa('withdraw_details', $wdwdtlValues);
					if(!$withdrawDetailRecord->insertSuccess()) {
						$updateWithdrawsDetailResult = false;
						$updateWithdrawsDetailError .= 'ADD_WITHDRAWS_DETAIL['.($key+1).']_FAIL\n';
					}
				}

				// Update product amount
				updateProductAmount($prd_id, $wdwdtl_amount, $updateWithdrawsDetailResult, $updateWithdrawsDetailError);
			}

			if($updateWithdrawsDetailResult) {
				// Edit withdraws and withdraw_details success
				$response['status'] = 'EDIT_PASS';
				echo json_encode($response);
			} else {
				// Edit withdraw_details fail
				$response['status'] = $updateWithdrawsDetailError;
				echo json_encode($response);
			}

		} else {
			// Edit withdraws fail
			$response['status'] = 'EDIT_WITHDRAWS_FAIL';
			echo json_encode($response);
		}


	}

	
}

function getProductData() {
	global $allPrdIdList, $OldPrdIdList, $formData, $productData, $dbConn;

	$allPrdIdList = array_unique(array_merge($OldPrdIdList, $formData['prd_id']));
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

function updateProductAmount($prd_id, $wdwdtl_amount, &$pass, &$errTxt) {
	global 	$productData, $OldPrdIdList, $OldWdwdtlAmountList, 
			$increaseShelfAmount, $dbConn;

	if($productData[$prd_id]['amount'] != '' && $productData[$prd_id]['shelf_amount'] != '') {
		if(in_array($prd_id, $OldPrdIdList)) {
			$oldAmount = $OldWdwdtlAmountList[$prd_id];
		} else {
			$oldAmount = 0;
		}

		$resultAmount = abs($oldAmount - $wdwdtl_amount);
		if($wdwdtl_amount >= $oldAmount) {
			$newPrdAmount 		= $productData[$prd_id]['amount'] - $resultAmount;
			$newPrdShelfAmount 	= $productData[$prd_id]['shelf_amount'] + $resultAmount;
		} else {
			$newPrdAmount 		= $productData[$prd_id]['amount'] + $resultAmount;
			$newPrdShelfAmount 	= $productData[$prd_id]['shelf_amount'] - $resultAmount;
		}

		// Update product amount
		$sql = "UPDATE 	products 
				SET 	prd_amount = '$newPrdAmount' 
				WHERE 	prd_id = '$prd_id'";
		$result = mysql_query($sql, $dbConn);
		if(!$result) {
			$errTxt .= "DECREASE_PRODUCT_AMOUNT[$prd_id]_FAIL\n";
			$pass = false;
			return;
		}

		if($increaseShelfAmount) {
			// Update product shelf amount
			$sql = "UPDATE 	products 
					SET 	prd_shelf_amount = '$newPrdShelfAmount' 
					WHERE 	prd_id = '$prd_id'";
			$result = mysql_query($sql, $dbConn);
			if(!$result) {
				$errTxt .= "INCREASE_PRODUCT_SHELF_AMOUNT[$prd_id]_FAIL\n";
				$pass = false;
				return;
			}
		}
	}
}

function removeProductAmount($prd_id, &$pass, &$errTxt) {
	global 	$productData, $OldPrdIdList, $OldWdwdtlAmountList, 
			$increaseShelfAmount, $dbConn;

	if($productData[$prd_id]['amount'] != '' && $productData[$prd_id]['shelf_amount'] != '') {
		$newPrdAmount = $productData[$prd_id]['amount'] + $OldWdwdtlAmountList[$prd_id];

		// Remove product amount
		$sql = "UPDATE 	products 
				SET 	prd_amount = '$newPrdAmount' 
				WHERE 	prd_id = '$prd_id'";
		$result = mysql_query($sql, $dbConn);
		if(!$result) {
			$errTxt .= "REMOVE_PRODUCT_AMOUNT[$prd_id]_FAIL\n";
			$pass = false;
			return;
		}

		if($increaseShelfAmount) {
			$newPrdShelfAmount = $productData[$prd_id]['shelf_amount'] - $OldWdwdtlAmountList[$prd_id];

			// Update product shelf amount
			$sql = "UPDATE 	products 
					SET 	prd_shelf_amount = '$newPrdShelfAmount' 
					WHERE 	prd_id = '$prd_id'";
			$result = mysql_query($sql, $dbConn);
			if(!$result) {
				$errTxt .= "REMOVE_PRODUCT_SHELF_AMOUNT[$prd_id]_FAIL\n";
				$pass = false;
				return;
			}
		}
	}
}
?>