<?php
session_start();
$action			= isset($_REQUEST['action']) ? $_REQUEST['action'] : 'ADD';
$tableName		= 'package_promotion_details';
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
						// get real enddate to find expired
						if($field == 'pkgprmdtl_enddate') {
							$end_time = strtotime($values[$field]);
						}
						
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
				case 'todayOnward_package_promotions':
					$sqlRefData = "	SELECT 		pkgprm_id refValue,
												pkgprm_name refText,
												pkgprm_startdate,
												IFNULL(pkgprm_enddate,'') pkgprm_enddate,
												custype_id 
									FROM 		package_promotions 
									WHERE 		pkgprm_enddate IS NULL OR 
												pkgprm_enddate >= CURDATE() 
									ORDER BY 	refValue DESC";
					$refField 	= 'pkgprm_id';
					break;

				case 'packages':
					$sqlRefData = "	SELECT 		pkg_id refValue,
												pkg_name refText,
												pkg_start,
												pkg_stop,
												pkg_desc,
												pkg_price,
												pkg_picture 
									FROM 		packages 
									WHERE 		(
													pkg_stop IS NULL OR 
													pkg_stop >= '$nowDate'
												)
									ORDER BY 	refText ASC";
					$refField 	= 'pkg_id';
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
	if($values['pkgprmdtl_enddate'] != '-') {
		$cur_time = strtotime($nowDate);
		if($end_time < $cur_time) { // Expired
			$hideEditButton = 'true';
		}
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
	$smarty->assign('randNum', substr(str_shuffle('0123456789'), 0, 5));
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