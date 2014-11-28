<?php
session_start();
$action			= isset($_REQUEST['action']) ? $_REQUEST['action'] : 'ADD';
$tableName		= $_REQUEST['tableName'];
$code			= $_REQUEST['code'];

// Check table
switch ($tableName) {
	case 'orders':
		header("location:form_orders.php?action=$action&code=$code");
		break;

	case 'receives':
		header("location:form_receives.php?action=$action&code=$code");
		break;

	case 'packages':
		header("location:form_packages.php?action=$action&code=$code");
		break;
}

include('../config/config.php');
$tplName = "form_$tableName.html";
$subDir	 = WEB_ROOTDIR.'/backoffice/';
include('../common/common_header.php');
$tableInfo = getTableInfo($tableName);

if(!$_REQUEST['ajaxCall']) {
	//1. Display form
	if($action == 'EDIT' || $action == 'VIEW_DETAIL') {
		$tableRecord = new TableSpa($tableName, $code);
		$values      = array();
		foreach($tableInfo['fieldNameList'] as $field => $value) {
			$colFieldType = $tableRecord->getFieldType($field);
			if($colFieldType == 'time'){
				$tmpTime = $tableRecord->getFieldValue($field);//get time from database
				$newTmpTime = substr($tmpTime, 0, 5);
				$values[$field] = $newTmpTime;
			}else{
				$values[$field] = $tableRecord->getFieldValue($field);
			}

			if($action == 'VIEW_DETAIL') {
				if(hasValue($values[$field])) {
					
					if($colFieldType == 'date' || $colFieldType == 'datetime') {
						$values[$field] = dateThaiFormat($values[$field]);
					}
				} else {
					$values[$field] = '-';
				}
			}
		}
		$smarty->assign('values', $values);
	}

	// Get reference data for selectReferenceJS
	if(is_array($tableInfo['referenceData']) && count($tableInfo['referenceData']) > 0) {
		$sqlRefData 	= '';
		$referenceData 	= array();

		foreach ($tableInfo['referenceData'] as $key => $table) {
			switch ($table) {
				case 'sex':
					$sqlRefData = "	SELECT 		sex_id refValue,
												sex_name refText 
									FROM 		sex 
									ORDER BY 	sex_name ASC";
					$refField 	= 'sex_id';
					break;

				case 'titles':
					$sqlRefData = "	SELECT 		t.title_id refValue,
												t.title_name refText,
												s.sex_name 
									FROM 		titles t left join sex s 
									ON          t.sex_id = s.sex_id 
									ORDER BY 	t.title_name ASC";
					$refField 	= 'title_id';
					break;

				case 'rooms':
					$sqlRefData = "	SELECT 		room_id refValue,
												room_name refText 
									FROM 		rooms 
									ORDER BY 	room_name ASC";
					$refField 	= 'room_id';
					break;

				case 'positions':
					$sqlRefData = "	SELECT 		pos_id refValue,
												pos_name refText 
									FROM 		positions 
									ORDER BY 	pos_name ASC";
					$refField 	= 'pos_id';
					break;

				case 'customer_types':
					$sqlRefData = "	SELECT 		custype_id refValue,
												custype_name refText 
									FROM 		customer_types 
									ORDER BY 	custype_name ASC";
					$refField 	= 'custype_id';
					break;

				case 'service_lists':
					$sqlRefData = "	SELECT 		svl_id refValue,
												svl_name refText 
									FROM 		service_lists 
									ORDER BY 	svl_name ASC";
					$refField 	= 'svl_id';
					break;

				case 'service_list_types':
					$sqlRefData = "	SELECT 		svltyp_id refValue,
												svltyp_name refText 
									FROM 		service_list_types 
									ORDER BY 	svltyp_name ASC";
					$refField 	= 'svltyp_id';
					break;

				case 'promotions':
					$sqlRefData = "	SELECT 		prm_id refValue,
												prm_name refText 
									FROM 		promotions 
									ORDER BY 	prm_name ASC";
					$refField 	= 'prm_id';
					break;

				case 'product_types':
					$sqlRefData = "	SELECT 		prdtyp_id refValue,
												prdtyp_name refText 
									FROM 		product_types 
									ORDER BY 	prdtyp_name ASC";
					$refField 	= 'prdtyp_id';
					break;

				case 'brands':
					$sqlRefData = "	SELECT 		brand_id refValue,
												brand_name refText 
									FROM 		brands 
									ORDER BY 	brand_name ASC";
					$refField 	= 'brand_id';
					break;

				case 'units':
					$sqlRefData = "	SELECT 		unit_id refValue,
												unit_name refText 
									FROM 		units 
									ORDER BY 	unit_name ASC";
					$refField 	= 'unit_id';
					break;

				case 'employees':
					$sqlRefData = "	SELECT 		emp_id refValue,
												CONCAT(emp_name, ' ', emp_surname) refText 
									FROM 		employees 
									ORDER BY 	refText ASC";
					$refField 	= 'emp_id';
					break;

				case 'customers':
					$sqlRefData = "	SELECT 		cus_id refValue,
												CONCAT(cus_name, ' ', cus_surname) refText 
									FROM 		customers 
									ORDER BY 	refText ASC";
					$refField 	= 'cus_id';
					break;

				case 'booking_status':
					$sqlRefData = "	SELECT 		bkgstat_id refValue,
												bkgstat_name refText 
									FROM 		booking_status 
									ORDER BY 	refText ASC";
					$refField 	= 'bkgstat_id';
					break;

				case 'bank_accounts':
					$sqlRefData = "	SELECT 		bnkacc_id refValue,
												bnkacc_name refText 
									FROM 		bank_accounts 
									ORDER BY 	refText ASC";
					$refField 	= 'bnkacc_id';
					break;

				case 'booking':
					$sqlRefData = "	SELECT 		bkg_id refValue,
												bkg_id refText 
									FROM 		booking 
									ORDER BY 	refText DESC";
					$refField 	= 'bkg_id';
					break;

				case 'beds':
					$sqlRefData = "	SELECT 		bed_id refValue,
												bed_name refText 
									FROM 		beds 
									ORDER BY 	refText ASC";
					$refField 	= 'bed_id';
					break;

				case 'pay_types':
					$sqlRefData = "	SELECT 		paytyp_id refValue,
												paytyp_name refText 
									FROM 		pay_types 
									ORDER BY 	refText ASC";
					$refField 	= 'paytyp_id';
					break;

				case 'element_types':
					$sqlRefData = "	SELECT 		eletyp_id refValue,
												eletyp_name refText 
									FROM 		element_types 
									ORDER BY 	refText ASC";
					$refField 	= 'eletyp_id';
					break;

				case 'services':
					$sqlRefData = "	SELECT 		ser_id refValue,
												ser_id refText 
									FROM 		services 
									ORDER BY 	refText DESC";
					$refField 	= 'ser_id';
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

	$smarty->assign('action', $action);
	$smarty->assign('tableName', $tableName);
	$smarty->assign('tableNameTH', $tableInfo['tableNameTH']);
	$smarty->assign('code', $code);
	$smarty->assign('randNum', substr(str_shuffle('0123456789'), 0, 5));
	if(isset($_REQUEST['hideEditButton'])) {
		$smarty->assign('hideEditButton', true);
	}
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

		// Insert แบบระบุ Field
		$tableRecord = new TableSpa($tableName, $values['fieldName'], $values['fieldValue']);
		if($tableRecord->insertSuccess()) {
			$response['status'] = 'ADD_PASS';
			echo json_encode($response);
		} else {
			$response['status'] = 'ADD_FAIL';
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

		// Commit
		if($tableRecord->commit()) {
			$response['status'] = 'EDIT_PASS';
			echo json_encode($response);
		} else {
			$response['status'] = 'EDIT_FAIL';
			echo json_encode($response);
		}
	}
}
?>