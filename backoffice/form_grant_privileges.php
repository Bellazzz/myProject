<?php
$action			= isset($_REQUEST['action']) ? $_REQUEST['action'] : 'ADD';
$tableName		= 'grant_privileges';
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
		$tableRecord = new TableSpa($tableName, $code);
		$values      = array();
		foreach($tableInfo['fieldNameList'] as $field => $value) {
			$values[$field] = $tableRecord->getFieldValue($field);
		}
		$smarty->assign('values', $values);

		// Get table order_details data
		$valuesDetail = array();
		$sql = "SELECT 		p.privlg_name_th  
				FROM 		grant_privileges gp, privileges p 
				WHERE 		gp.privlg_id = p.privlg_id 
							AND gp.emp_id = '$code' 
				ORDER BY 	p.privlg_name_th";
		$result = mysql_query($sql, $dbConn);
		$rows 	= mysql_num_rows($result);
		for($i=0; $i<$rows; $i++) {
			array_push($valuesDetail, mysql_fetch_assoc($result));
		}
		$smarty->assign('valuesDetail', $valuesDetail);

	} else if($action == 'VIEW_DETAIL') {
		// Get table grant_privileges data
		$tableRecord = new TableSpa($tableName, $code);
		$values      = array();
		foreach($tableInfo['fieldNameList'] as $field => $value) {
			$values[$field] = $tableRecord->getFieldValue($field);
		}
		$smarty->assign('values', $values);

		// Get service lists of grant_privileges
		
		$grnprivlgList = array();
		$sql = "SELECT 		p.privlg_name_th  
				FROM 		grant_privileges gp, privileges p 
				WHERE 		gp.privlg_id = p.privlg_id 
							AND gp.emp_id = '$code' 
				ORDER BY 	p.privlg_name_th";
		$result = mysql_query($sql, $dbConn);
		$rows 	= mysql_num_rows($result);
		for($i=0; $i<$rows; $i++) {
			array_push($grnprivlgList, mysql_fetch_assoc($result));
			$grnprivlgList[$i]['no'] = $i+1;
		}
		$smarty->assign('grnprivlgList', $grnprivlgList);

		$empRecord = new TableSpa('employees', $code);
		$emp_fullName = $empRecord->getFieldValue('emp_name')." ".$empRecord->getFieldValue('emp_surname');
		$smarty->assign('emp_fullName', $emp_fullName);
	}

	// Get reference data for selectReferenceJS
	if(is_array($tableInfo['referenceData']) && count($tableInfo['referenceData']) > 0) {
		$sqlRefData 	= '';
		$referenceData 	= array();

		foreach ($tableInfo['referenceData'] as $key => $table) {
			switch ($table) {
				case 'employees':
					$sqlRefData = "	SELECT 	* 
									FROM 	(
											SELECT 		e.emp_id refValue,
														CONCAT(e.emp_name, ' ', e.emp_surname) refText,
														COUNT(g.grnprivlg_id) AS amount 
											FROM 		employees e 
														LEFT JOIN 
														grant_privileges g 
														ON e.emp_id = g.emp_id 
											GROUP BY 	e.emp_id 
											ORDER BY 	refText ASC
											) a
									WHERE 	amount <= 0";
					$refField 	= 'emp_id';
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

	if($action != 'VIEW_DETAIL') {
		$privlgList = array();
		// Get all privileges data
		$sql = "SELECT  	privlg_id,
							privlg_name,
							privlg_name_th 
				FROM 		privileges 
				ORDER BY 	privlg_name_th";
		$result = mysql_query($sql, $dbConn);
		$rows 	= mysql_num_rows($result);
		if($rows > 0) {
			for($i=0; $i<$rows; $i++) {
				$record = mysql_fetch_assoc($result);
				$record['no'] = $i+1;
				$privlgList[$record['privlg_id']]['no'] 			= number_format($record['no']);
				$privlgList[$record['privlg_id']]['privlg_id'] 		= $record['privlg_id'];
				$privlgList[$record['privlg_id']]['privlg_name']	= $record['privlg_name'];
				$privlgList[$record['privlg_id']]['privlg_name_th'] = $record['privlg_name_th'];
			}
			$smarty->assign('privlgList', $privlgList);
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

		// Insert grant_privileges
		$insertResult = true;
		$insertError  = '';
		foreach ($formData['privlg_id'] as $key => $privlg_id) {
			$grnprivlgValues 	= array($privlg_id, $formData['emp_id']);
			$grnprivlgRecord 	= new TableSpa('grant_privileges', $grnprivlgValues);
			if(!$grnprivlgRecord->insertSuccess()) {
				$insertResult = false;
				$insertError .= 'ADD_GRANT_PRIVILEGES['.($key+1).']_FAIL\n';
				$insertError .= mysql_error($dbConn)."\n\n".$privlg_id.",".$formData['emp_id'];
			}
		}

		if($insertResult) {
			// Add grant_privileges success
			$response['status'] = 'ADD_PASS';
			echo json_encode($response);
		} else {
			$response['status'] = $insertError;
			echo json_encode($response);
		}
	} else if($action == 'EDIT') {
		//2.2 Update record
		$tableRecord = new TableSpa($tableName, $code);

		// Rename Image
		if(strpos($formData['pkg_picture'], 'temp_') !== FALSE) {
			$type		= str_replace(".", "", strrchr($formData['pkg_picture'],"."));
			$pkg_picture = $code.".$type";
			$imgTmpPath = '../img/temp/'.$formData['pkg_picture'];
			$imgNewPath = '../img/grant_privileges/'.$pkg_picture;

			// Delete Old Image
			if(file_exists($imgNewPath)) {
				if(!unlink($imgNewPath)) {
					$response['status'] = 'DELETE_OLD_IMG_FAIL';
					echo json_encode($response);
					exit();
				}
			}
			// Rename temp to new image
			if(file_exists($imgTmpPath)) {
				if(rename($imgTmpPath, $imgNewPath)) {
					$formData['pkg_picture'] = $pkg_picture;
				} else {
					$response['status'] = 'RENAME_FAIL';
					echo json_encode($response);
					exit();
				}
			}
		}

		// Set all field value
		foreach($formData as $fieldName => $value) {
			if(in_array($fieldName, $fieldListEn)) {
				// value is empty will set default is null
				if($value == '' && in_array($fieldName, $tableInfo['defaultNull'])) {
					$value = 'NULL';
				}

				$tableRecord->setFieldValue($fieldName, $value);
			}
		}

		// Commit
		if(!$tableRecord->commit()) {
			$response['status'] = 'EDIT_FAIL';
			echo json_encode($response);
		}

		// Delete package_service_lists if delete old package_service_lists
		$oldPkgsvlList = array();
		$newPkgsvlList = array();
		// Find old package_service_lists
		$sql = "SELECT pkgsvl_id FROM package_service_lists WHERE pkg_id = '$code'";
		$result = mysql_query($sql, $dbConn);
		$rows 	= mysql_num_rows($result);
		for($i=0; $i<$rows; $i++) {
			$oldPkgsvlRecord = mysql_fetch_assoc($result);
			array_push($oldPkgsvlList, $oldPkgsvlRecord['pkgsvl_id']);
		}
		// Find new package_service_lists
		foreach ($formData['pkgsvl_id'] as $key => $newPkgsvl_id) {
			array_push($newPkgsvlList, $newPkgsvl_id);
		}
		// Check for delete 
		foreach ($oldPkgsvlList as $key => $oldPkgsvl_id) {
			if(!in_array($oldPkgsvl_id, $newPkgsvlList)) {
				// Delete package_service_lists
				$pkgsvlRecord 	= new TableSpa('package_service_lists', $oldPkgsvl_id);
				if(!$pkgsvlRecord->delete()) {
					$updatePkgsvlResult = false;
					$updatePkgsvlError .= "DELETE_PAKAGE_SERVICE_LISTS[$oldPkgsvl_id]_FAIL\n";
				}
			}
		}

		// Update or Add package_service_lists
		$updatePkgsvlResult = true;
		$updatePkgsvlError  = '';

		foreach ($formData['svl_id'] as $key => $svl_id) {
			if(isset($formData['pkgsvl_id'][$key])) {
				// Update package_service_lists
				$pkgsvl_id 		= $formData['pkgsvl_id'][$key];
				$pkgsvlRecord 	= new TableSpa('package_service_lists', $pkgsvl_id);
				$pkgsvlRecord->setFieldValue('svl_id', $svl_id);
				if(!$pkgsvlRecord->commit()) {
					$updatePkgsvlResult = false;
					$updatePkgsvlError .= 'EDIT_PAKAGE_SERVICE_LISTS['.($key+1).']_FAIL\n';
				}
			} else {
				// Add new package_service_lists
				$pkgsvlValues 	= array($svl_id, $code);
				$pkgsvlRecord 	= new TableSpa('package_service_lists', $pkgsvlValues);
				if(!$pkgsvlRecord->insertSuccess()) {
					$updatePkgsvlResult = false;
					$updatePkgsvlError .= 'ADD_PAKAGE_SERVICE_LISTS['.($key+1).']_FAIL\n';
				}
			}
		}

		if($updatePkgsvlResult) {
			// Edit package and package_service_lists success
			$response['status'] = 'EDIT_PASS';
			echo json_encode($response);
		} else {
			// Edit package_service_lists fail
			$response['status'] = $updatePkgsvlError;
			echo json_encode($response);
		}
	}
}
?>