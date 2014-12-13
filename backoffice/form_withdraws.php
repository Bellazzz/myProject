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
		// withdraw status default is wait for transport
		/*array_push($values['fieldName'], 'ordstat_id');
		array_push($values['fieldValue'], 'OS01');*/

		// Insert withdraws
		$tableRecord = new TableSpa($tableName, $values['fieldName'], $values['fieldValue']);
		if($tableRecord->insertSuccess()) {
			$insertWithdrawsResult = true;
		} else {
			$insertWithdrawsResult = false;
		}

		if($insertWithdrawsResult) {
			// Insert withdraws detail
			$insertWithdrawsDetailResult = true;
			$insertWithdrawsDetailError  = '';
			$wdw_id = $tableRecord->getKey();
			foreach ($formData['prd_id'] as $key => $prd_id) {
				$wdwdtl_amount 		= $formData['prd_qty'][$key];
				$wdwdtlValues 		= array($wdw_id, $prd_id, $wdwdtl_amount);
				$withdrawsDetailRecord 	= new TableSpa('withdraw_details', $wdwdtlValues);
				if(!$withdrawsDetailRecord->insertSuccess()) {
					$insertWithdrawsDetailResult = false;
					$insertWithdrawsDetailError .= 'ADD_WITHDRAW_DETAIL['.($key+1).']_FAIL\n';
				}
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
			$sql = "SELECT wdwdtl_id FROM withdraw_details WHERE wdw_id = '$code'";
			$result = mysql_query($sql, $dbConn);
			$rows 	= mysql_num_rows($result);
			for($i=0; $i<$rows; $i++) {
				$oldWdwdtlRecord = mysql_fetch_assoc($result);
				array_push($oldWithdrawsDetailList, $oldWdwdtlRecord['wdwdtl_id']);
			}
			// Find new withdraw_details
			foreach ($formData['wdwdtl_id'] as $key => $newWdwdtl_id) {
				array_push($newWithdrawsDetailList, $newWdwdtl_id);
			}
			// Check for delete 
			foreach ($oldWithdrawsDetailList as $key => $oldWdwdtl_id) {
				if(!in_array($oldWdwdtl_id, $newWithdrawsDetailList)) {
					// Delete withdraw_details
					$orderDetailRecord 	= new TableSpa('withdraw_details', $oldWdwdtl_id);
					if(!$withdrawsDetailRecord->delete()) {
						$updateWithdrawsResult = false;
						$updateWithdrawsDetailError .= "DELETE_WITHDRAWS_DETAIL[$oldWdwdtl_id]_FAIL\n";
					}
				}
			}

			// Update or Add withdraw_details
			$updateWithdrawsResult = true;
			$updateWithdrawsDetailError  = '';

			foreach ($formData['prd_id'] as $key => $prd_id) {
				$orddtl_amount 	= $formData['prd_qty'][$key];

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
					$orddtlValues 		= array($code, $prd_id, $wdwdtl_amount);
					$orderDetailRecord 	= new TableSpa('withdraw_details', $wdwdtlValues);
					if(!$orderDetailRecord->insertSuccess()) {
						$updateWithdrawsDetailResult = false;
						$updateWithdrawsDetailError .= 'ADD_WITHDRAWS_DETAIL['.($key+1).']_FAIL\n';
					}
				}
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
?>