<?php
$action			= isset($_REQUEST['action']) ? $_REQUEST['action'] : 'ADD';
$tableName		= 'packages';
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
		$sql = "SELECT 		ps.pkgsvl_id, s.svl_id 
				FROM 		packages p, package_service_lists ps, service_lists s 
				WHERE 		p.pkg_id = ps.pkg_id AND ps.svl_id = s.svl_id 
							AND p.pkg_id = '$code' 
				ORDER BY 	s.svl_id";
		$result = mysql_query($sql, $dbConn);
		$rows 	= mysql_num_rows($result);
		for($i=0; $i<$rows; $i++) {
			array_push($valuesDetail, mysql_fetch_assoc($result));
		}
		$smarty->assign('valuesDetail', $valuesDetail);

	} else if($action == 'VIEW_DETAIL') {
		// Get table packages data
		$tableRecord = new TableSpa($tableName, $code);
		$values      = array();
		foreach($tableInfo['fieldNameList'] as $field => $value) {
			$values[$field] = $tableRecord->getFieldValue($field);
		}
		// Date thai format
		$values['pkg_start_th'] 	= dateThaiFormat($values['pkg_start']);
		$values['pkg_stop_th'] 		= dateThaiFormat($values['pkg_stop']);
		$smarty->assign('values', $values);

		// Get service lists of packages
		$pkgsvlDetailList = array();
		$sql 	= "	SELECT ps.pkgsvl_id,
					s.svl_id,
					s.svl_name,
					s.svl_hr,
					s.svl_min 
					FROM package_service_lists ps, service_lists s 
					WHERE ps.svl_id = s.svl_id 
					AND ps.pkg_id = '$code' 
					ORDER BY s.svl_id";
		$result = mysql_query($sql, $dbConn);
		$rows 	= mysql_num_rows($result);
		for($i=0; $i<$rows; $i++) {
			array_push($pkgsvlDetailList, mysql_fetch_assoc($result));
			$pkgsvlDetailList[$i]['no'] = $i+1;
		}
		$smarty->assign('pkgsvlDetailList', $pkgsvlDetailList);
	}

	// Get reference data for selectReferenceJS
	if(is_array($tableInfo['referenceData']) && count($tableInfo['referenceData']) > 0) {
		$sqlRefData 	= '';
		$referenceData 	= array();

		foreach ($tableInfo['referenceData'] as $key => $table) {
			switch ($table) {
				case 'service_lists':
					$sqlRefData = "	SELECT 		svl_id refValue,
												svl_name refText 
									FROM 		service_lists 
									ORDER BY 	svl_name ASC";
					$refField 	= 'svl_id';
					break;
			}

			if(hasValue($sqlRefData)) {
				$resultRefData 	= mysql_query($sqlRefData);
				$rowsRefData 	= mysql_num_rows($resultRefData);

				if($rowsRefData > 0) {
					$referenceData[$table] = array();
					// push to referenc data
					for($i=0; $i<$rowsRefData; $i++) {
						$refDataRow = mysql_fetch_assoc($resultRefData);
						array_push($referenceData[$table], array(
							'refText'	=> $refDataRow['refText'],
							'refValue'	=> $refDataRow['refValue'],
							'refField'	=> $refField
						));
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
	if(isset($_REQUEST['hideEditButton']) && $hideEditButton == 'true') {
		$smarty->assign('hideEditButton', true);
	}
	if(isset($_REQUEST['hideBackButton']) && $hideBackButton == 'true') {
		$smarty->assign('hideBackButton', true);
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

		// Rename Image
		if(strpos($formData['pkg_picture'], 'temp_') !== FALSE) {
			$type		= str_replace(".", "", strrchr($formData['pkg_picture'],"."));
			$tmpRecord	= new TableSpa('packages', null);
			$pkg_picture	= $tmpRecord->genKeyCharRunning().".$type";
			$pkg_picture_path = '../img/packages/'.$pkg_picture;

			// Delete Old Image
			if(file_exists($pkg_picture_path)) {
				if(!unlink($pkg_picture_path)) {
					$response['status'] = 'DELETE_OLD_IMG_FAIL';
					echo json_encode($response);
					exit();
				}
			}

			if(rename('../img/temp/'.$formData['pkg_picture'], $pkg_picture_path)) {
				$formData['pkg_picture'] = $pkg_picture;
			} else {
				$response['status'] = 'RENAME_FAIL';
				echo json_encode($response);
				exit();
			}
		}

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
		
		// Insert packages
		$tableRecord = new TableSpa($tableName, $values['fieldName'], $values['fieldValue']);
		if(!$tableRecord->insertSuccess()) {
			$response['status'] = 'ADD_PACKAGES_FAIL';
			echo json_encode($response);
		}

		// Insert packages service lists
		$insertPkgsvlResult = true;
		$insertPkgsvlError  = '';
		$pkg_id = $tableRecord->getKey();
		foreach ($formData['svl_id'] as $key => $svl_id) {
			$pkgsvlValues 	= array($svl_id, $pkg_id);
			$pkgsvlRecord 	= new TableSpa('package_service_lists', $pkgsvlValues);
			if(!$pkgsvlRecord->insertSuccess()) {
				$insertPkgsvlResult = false;
				$insertPkgsvlError .= 'ADD_PACKAGES_SERVICE_LISTS['.($key+1).']_FAIL\n';
			}
		}

		if($insertPkgsvlResult) {
			// Add packages and packages_service_lists success
			$response['status'] = 'ADD_PASS';
			echo json_encode($response);
		} else {
			$response['status'] = $insertPkgsvlError;
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
			$imgNewPath = '../img/packages/'.$pkg_picture;

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