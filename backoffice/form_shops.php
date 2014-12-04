<?php
$action			= isset($_REQUEST['action']) ? $_REQUEST['action'] : 'ADD';
$tableName		= 'shops';
$code			= $_REQUEST['code'];

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
		$sql = "SELECT 		sd.shpdpprd_id, p.prd_id 
				FROM 		shops s, shop_display_products sd, products p 
				WHERE 		s.shop_id = sd.shop_id AND sd.prd_id = p.prd_id 
							AND s.shop_id = '$code' 
				ORDER BY 	p.prd_id";
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
		$smarty->assign('values', $values);

		// Get service lists of packages
		$shopDisplayPrdList = array();
		$sql 	= "	SELECT sd.shpdpprd_id,
					p.prd_id,
					p.prd_name 
					FROM shop_display_products sd, products p 
					WHERE sd.prd_id = p.prd_id 
					AND sd.shop_id = '$code' 
					ORDER BY p.prd_name";
		$result = mysql_query($sql, $dbConn);
		$rows 	= mysql_num_rows($result);
		for($i=0; $i<$rows; $i++) {
			array_push($shopDisplayPrdList, mysql_fetch_assoc($result));
			$shopDisplayPrdList[$i]['no'] = $i+1;
		}
		$smarty->assign('shopDisplayPrdList', $shopDisplayPrdList);
	}

	// Get reference data for selectReferenceJS
	if(is_array($tableInfo['referenceData']) && count($tableInfo['referenceData']) > 0) {
		$sqlRefData 	= '';
		$referenceData 	= array();

		foreach ($tableInfo['referenceData'] as $key => $table) {
			switch ($table) {
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
		if(strpos($formData['shop_picture'], 'temp_') !== FALSE) {
			$type		= str_replace(".", "", strrchr($formData['shop_picture'],"."));
			$tmpRecord	= new TableSpa('shops', null);
			$shop_picture	= $tmpRecord->genKeyCharRunning().".$type";
			$shop_picture_path = '../img/shops/'.$shop_picture;

			// Delete Old Image
			if(file_exists($shop_picture_path)) {
				if(!unlink($shop_picture_path)) {
					$response['status'] = 'DELETE_OLD_IMG_FAIL';
					echo json_encode($response);
					exit();
				}
			}

			if(rename('../img/temp/'.$formData['shop_picture'], $shop_picture_path)) {
				$formData['shop_picture'] = $shop_picture;
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
		
		// Insert shops
		$tableRecord = new TableSpa($tableName, $values['fieldName'], $values['fieldValue']);
		if(!$tableRecord->insertSuccess()) {
			$response['status'] = 'ADD_SHOPS_FAIL';
			echo json_encode($response);
		}

		// Insert shop_display_products
		$insertShpdpprdResult = true;
		$insertShpdpprdError  = '';
		$shop_id = $tableRecord->getKey();
		foreach ($formData['prd_id'] as $key => $prd_id) {
			$shpdpprdValues 	= array($shop_id, $prd_id);
			$shpdpprdRecord 	= new TableSpa('shop_display_products', $shpdpprdValues);
			if(!$shpdpprdRecord->insertSuccess()) {
				$insertShpdpprdResult = false;
				$insertShpdpprdError .= 'ADD_SHOPS_DISPLAY_PRODUCTS['.($key+1).']_FAIL\n';
			}
		}

		if($insertShpdpprdResult) {
			// Add shops and shop_display_products success
			$response['status'] = 'ADD_PASS';
			echo json_encode($response);
		} else {
			$response['status'] = $insertShpdpprdError;
			echo json_encode($response);
		}
	} else if($action == 'EDIT') {
		//2.2 Update record
		$tableRecord = new TableSpa($tableName, $code);

		// Rename Image
		if(strpos($formData['shop_picture'], 'temp_') !== FALSE) {
			$type		= str_replace(".", "", strrchr($formData['shop_picture'],"."));
			$shop_picture = $code.".$type";
			$imgTmpPath = '../img/temp/'.$formData['shop_picture'];
			$imgNewPath = '../img/shops/'.$shop_picture;

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
					$formData['shop_picture'] = $shop_picture;
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

		// Delete shop_display_products if delete old shop_display_products
		$oldShpdpprdList = array();
		$newShpdpprdList = array();
		// Find old shop_display_products
		$sql = "SELECT shpdpprd_id FROM shop_display_products WHERE shop_id = '$code'";
		$result = mysql_query($sql, $dbConn);
		$rows 	= mysql_num_rows($result);
		for($i=0; $i<$rows; $i++) {
			$oldShpdpprdRecord = mysql_fetch_assoc($result);
			array_push($oldShpdpprdList, $oldShpdpprdRecord['shpdpprd_id']);
		}
		// Find new shop_display_products
		foreach ($formData['shpdpprd_id'] as $key => $newshpdpprd_id) {
			array_push($newShpdpprdList, $newshpdpprd_id);
		}
		// Check for delete 
		foreach ($oldShpdpprdList as $key => $oldshpdpprd_id) {
			if(!in_array($oldshpdpprd_id, $newShpdpprdList)) {
				// Delete shop_display_products
				$shpdbprdRecord 	= new TableSpa('shop_display_products', $oldshpdpprd_id);
				if(!$shpdbprdRecord->delete()) {
					$updateShpdpprdResult = false;
					$updateShpdbprdError .= "DELETE_SHOP_DISPLAY_PRODUCTS[$oldshpdpprd_id]_FAIL\n";
				}
			}
		}

		// Update or Add shop_display_products
		$updateShpdpprdResult = true;
		$updateShpdbprdError  = '';

		foreach ($formData['prd_id'] as $key => $prd_id) {
			if(isset($formData['shpdpprd_id'][$key])) {
				// Update shop_display_products
				$shpdpprd_id 		= $formData['shpdpprd_id'][$key];
				$shpdbprdRecord 	= new TableSpa('shop_display_products', $shpdpprd_id);
				$shpdbprdRecord->setFieldValue('prd_id', $prd_id);
				if(!$shpdbprdRecord->commit()) {
					$updateShpdpprdResult = false;
					$updateShpdbprdError .= 'EDIT_SHOP_DISPLAY_PRODUCTS['.($key+1).']_FAIL\n';
				}
			} else {
				// Add new shop_display_products
				$shpdbprdValues 	= array($code, $prd_id);
				$shpdbprdRecord 	= new TableSpa('shop_display_products', $shpdbprdValues);
				if(!$shpdbprdRecord->insertSuccess()) {
					$updateShpdpprdResult = false;
					$updateShpdbprdError .= 'ADD_SHOP_DISPLAY_PRODUCTS['.($key+1).']_FAIL\n';
				}
			}
		}

		if($updateShpdpprdResult) {
			// Edit shops and shop_display_products success
			$response['status'] = 'EDIT_PASS';
			echo json_encode($response);
		} else {
			// Edit shop_display_products fail
			$response['status'] = $updateShpdbprdError;
			echo json_encode($response);
		}
	}
}
?>