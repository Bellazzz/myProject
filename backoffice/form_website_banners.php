<?php
session_start();
$action			= isset($_REQUEST['action']) ? $_REQUEST['action'] : 'ADD';
$tableName		= 'website_banners';
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
			$values[$field] = $tableRecord->getFieldValue($field);
		}
		$smarty->assign('values', $values);
	}

	// Get website_banner order
	$maxOrder = 1; 
	$sql = "SELECT MAX(wsb_order) maxOrder FROM website_banners WHERE wsb_active = 1";
	$result = mysql_query($sql, $dbConn);
	$rows = mysql_num_rows($result);
	if($rows > 0) {
		$record = mysql_fetch_assoc($result);
		$maxOrder = $record['maxOrder'];
	}

	// Check for hide edit, back button
	if($hideEditButton == 'true') {
		$smarty->assign('hideEditButton', true);
	}
	if($hideBackButton == 'true') {
		$smarty->assign('hideBackButton', true);
	}

	$smarty->assign('maxOrder', $maxOrder);
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
		if(strpos($formData['wsb_picture'], 'temp_') !== FALSE) {
			$type		= str_replace(".", "", strrchr($formData['wsb_picture'],"."));
			$tmpRecord	= new TableSpa('website_banners', null);
			$wsb_picture	= $tmpRecord->genKeyCharRunning().".$type";
			$wsb_picture_path = '../img/website_banners/'.$wsb_picture;

			// Delete Old Image
			if(file_exists($wsb_picture_path)) {
				if(!unlink($wsb_picture_path)) {
					$response['status'] = 'DELETE_OLD_IMG_FAIL';
					echo json_encode($response);
					exit();
				}
			}

			if(rename('../img/temp/'.$formData['wsb_picture'], $wsb_picture_path)) {
				$formData['wsb_picture'] = $wsb_picture;
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

		setOrderAuto($formData['wsb_order']);

		// Insert
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

		// Set order auto
		$oldOrder = $tableRecord->getFieldValue('wsb_order');
		$newOrder = $formData['wsb_order'];
		if(hasValue($oldOrder)) {
			setOrderAutoHasOld($oldOrder, $newOrder);
		} else {
			setOrderAuto($newOrder);
		}

		// Rename Image
		if(strpos($formData['wsb_picture'], 'temp_') !== FALSE) {
			$type		= str_replace(".", "", strrchr($formData['wsb_picture'],"."));
			$wsb_picture	= $code.".$type";
			$imgTmpPath = '../img/temp/'.$formData['wsb_picture'];
			$imgNewPath = '../img/website_banners/'.$wsb_picture;

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
					$formData['wsb_picture'] = $wsb_picture;
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

function setOrderAuto($order) {
	// Skip
	if($order == '') {
		return;
	}

	global $dbConn;
	$sql = "SELECT wsb_id, wsb_order FROM website_banners WHERE wsb_order >= '$order' AND wsb_active = 1";
	$result = mysql_query($sql, $dbConn);
	$rows = mysql_num_rows($result);
	for($i=0; $i<$rows; $i++) {
		$record = mysql_fetch_assoc($result);
		$wsb_id = $record['wsb_id'];
		$wsb_order = $record['wsb_order'] + 1;
		$wsbRecord = new TableSpa('website_banners', $wsb_id);
		$wsbRecord->setFieldValue('wsb_order', $wsb_order);
		if(!$wsbRecord->commit()) {
			$response['status'] = 'SET_ORDER_AUTO_FAIL';
		}
	}
}

function setOrderAutoHasOld($oldOrder, $newOrder) {
	global $dbConn;
	$doPlus;
	$sql = "";
	if($newOrder == '') {
		$sql = "SELECT 	wsb_id, wsb_order FROM website_banners 
				WHERE 	wsb_order > '$oldOrder' 
						AND wsb_active = 1";
		$doPlus = false; 
	} else if($newOrder > $oldOrder) {
		$sql = "SELECT 	wsb_id, wsb_order FROM website_banners 
				WHERE 	wsb_order > '$oldOrder' 
						AND wsb_order <= '$newOrder' 
						AND wsb_active = 1";
		$doPlus = false; 
	} else {
		$sql = "SELECT 	wsb_id, wsb_order FROM website_banners 
				WHERE 	wsb_order >= '$newOrder' 
						AND wsb_order < '$oldOrder' 
						AND wsb_active = 1";
		$doPlus = true;
	}
	$result = mysql_query($sql, $dbConn);
	$rows = mysql_num_rows($result);
	for($i=0; $i<$rows; $i++) {
		$record = mysql_fetch_assoc($result);
		$wsb_id = $record['wsb_id'];
		$wsb_order = $record['wsb_order'];
		if($doPlus) {
			$wsb_order++;
		} else {
			$wsb_order--;
		}
		$wsbRecord = new TableSpa('website_banners', $wsb_id);
		$wsbRecord->setFieldValue('wsb_order', $wsb_order);
		if(!$wsbRecord->commit()) {
			$response['status'] = 'SET_ORDER_AUTO_FAIL';
		}
	}
}
?>