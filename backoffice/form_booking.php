<?php
session_start();
$action			= isset($_REQUEST['action']) ? $_REQUEST['action'] : 'ADD';
$tableName		= 'booking';
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
		// Get table booking data
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
		}
		$smarty->assign('values', $values);

		// Display payment form
		if($values['bnkacc_id'] != '' || $values['bkg_transfer_date'] != '' ||
		$values['bkg_transfer_time'] != '' || $values['bkg_transfer_evidence'] != '') {
			$smarty->assign('displayPaymentForm', true);
		}

		// Get table booking_packages data
		$valuesPkg = array();
		$sql = "SELECT 		bp.bkgpkg_id, 
							bp.bkgpkg_date,
							DATE_FORMAT(bp.bkgpkg_time,'%H:%i') bkgpkg_time,
							bp.bkgpkg_persons,
							p.pkg_id,
							p.pkg_price 
				FROM 		booking_packages bp, 
							packages p  
				WHERE 		bp.pkg_id = p.pkg_id AND 
							bkg_id = '$code'";
		$result = mysql_query($sql, $dbConn);
		$rows 	= mysql_num_rows($result);
		for($i=0; $i<$rows; $i++) {
			$record = mysql_fetch_assoc($result);
			if(!isset($valuesPkg[$record['pkg_id']])) {
				$valuesPkg[$record['pkg_id']] = array(
					'bkgpkg_id'   		=> $record['bkgpkg_id'],
					'pkg_id' 			=> $record['pkg_id'],
					'bkgpkg_date' 		=> $record['bkgpkg_date'],
					'bkgpkg_time' 		=> $record['bkgpkg_time'],
					'pkg_price' 		=> $record['pkg_price'],
					'bkgpkg_persons' 	=> $record['bkgpkg_persons']
				);
			}
		}
		$smarty->assign('valuesPkg', $valuesPkg);

		// Get table booking_service_lists data
		$valuesSvl = array();
		$sql = "SELECT 		bs.bkgsvl_id, 
							s.svl_id, 
							bs.bkgsvl_date,
							DATE_FORMAT(bs.bkgsvl_time,'%H:%i') bkgsvl_time,
							bs.bkgsvl_persons,
							s.svl_price 
				FROM 		booking_service_lists bs, service_lists s  
				WHERE 		bs.svl_id = s.svl_id AND 
							bkg_id = '$code' 
				ORDER BY 	bkgsvl_id";
		$result = mysql_query($sql, $dbConn);
		$rows 	= mysql_num_rows($result);
		for($i=0; $i<$rows; $i++) {
			array_push($valuesSvl, mysql_fetch_assoc($result));
		}
		$smarty->assign('valuesSvl', $valuesSvl);

	} else if($action == 'VIEW_DETAIL') {
		// Get table booking data
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

			if(hasValue($values[$field])) {
				if($colFieldType == 'date' || $colFieldType == 'datetime') {
					$values[$field] = dateThaiFormat($values[$field]);
				}
			} else {
				$values[$field] = '-';
			}
		}
		$smarty->assign('values', $values);
		
		// Get booking packages
		$viewBkgpkgData = array();
		$sql 	= "	SELECT 	bp.bkgpkg_date,
							DATE_FORMAT(bp.bkgpkg_time,'%H:%i') bkgpkg_time,
							bp.bkgpkg_persons,
							bp.bkgpkg_status,
							p.pkg_id,
							p.pkg_name 
					FROM 	booking_packages bp, packages p 
					WHERE 	bp.pkg_id = p.pkg_id AND 
					 		bp.bkg_id = '$code'";
		$result = mysql_query($sql, $dbConn);
		$rows 	= mysql_num_rows($result);
		for($i=0; $i<$rows; $i++) {
			$record = mysql_fetch_assoc($result);
			$record['bkgpkg_date'] = dateThaiFormat($record['bkgpkg_date']);
			array_push($viewBkgpkgData, $record);
			$viewBkgpkgData[$i]['no'] = $i+1;
		}
		$smarty->assign('viewBkgpkgData', $viewBkgpkgData);

		// Get booking service_lists
		$viewBkgsvlData = array();
		$sql 	= "	SELECT 	bs.bkgsvl_date,
							DATE_FORMAT(bs.bkgsvl_time,'%H:%i') bkgsvl_time,
							bs.bkgsvl_persons,
							bs.bkgsvl_status,
							s.svl_id,
							s.svl_name 
					FROM 	booking_service_lists bs, service_lists s 
					WHERE 	bs.svl_id = s.svl_id AND 
					 		bs.bkg_id = '$code'";
		$result = mysql_query($sql, $dbConn);
		$rows 	= mysql_num_rows($result);
		for($i=0; $i<$rows; $i++) {
			$record = mysql_fetch_assoc($result);
			$record['bkgsvl_date'] = dateThaiFormat($record['bkgsvl_date']);
			array_push($viewBkgsvlData, $record);
			$viewBkgsvlData[$i]['no'] = $i+1;
		}
		$smarty->assign('viewBkgsvlData', $viewBkgsvlData);
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

				case 'customers':
					$sqlRefData = "	SELECT 		cus_id refValue,
												CONCAT(cus_name, ' ', cus_surname) refText,
												custype_id 
									FROM 		customers 
									ORDER BY 	refText ASC";
					$refField 	= 'cus_id';
					break;

				case 'customer_types':
					$sqlRefData = "	SELECT 		custype_id refValue,
												custype_name refText 
									FROM 		customer_types 
									ORDER BY 	refText ASC";
					$refField 	= 'custype_id';
					break;

				case 'service_lists':
					$sqlRefData = "	SELECT 		svl_id refValue,
												svl_name refText,
												svl_price 
									FROM 		service_lists 
									ORDER BY 	svl_name ASC";
					$refField 	= 'svl_id';
					break;

				case 'packages':
					$sqlRefData = "	SELECT 		pkg_id refValue,
												pkg_name refText,
												pkg_start,
												pkg_stop,
												pkg_price
									FROM 		packages p 
									ORDER BY 	refText ASC";
					$refField 	= 'pkg_id';
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

	// Get package promotion detail data
	$pkgPromotions = array();
	$sql = "SELECT 		pkgprm.custype_id,
						pkgprm.pkgprm_name,
						pkgprm.pkgprm_pic,
						pkgprmdtl.pkgprmdtl_id,
						pkgprmdtl.pkg_id,
						pkgprmdtl.pkgprmdtl_discout,
						pkgprmdtl.pkgprmdtl_discout_type 
			FROM 		package_promotion_details pkgprmdtl,
						package_promotions pkgprm 
			WHERE 		pkgprmdtl.pkgprm_id = pkgprm.pkgprm_id AND 
						pkgprmdtl.pkgprmdtl_startdate <= '$nowDate' AND 
						(
							pkgprmdtl.pkgprmdtl_enddate IS NULL OR
							pkgprmdtl.pkgprmdtl_enddate >= '$nowDate'
						)";
	$result = mysql_query($sql, $dbConn);
	$rows 	= mysql_num_rows($result);
	if($rows > 0) {
		for($i=0; $i<$rows; $i++) {
			$record			= mysql_fetch_assoc($result);
			$custype_id 	= $record['custype_id'];
			$pkg_id 		= $record['pkg_id'];

			// Sale promotion
			$pkgPromotions[$custype_id][$pkg_id] = array(
				'pkgprmdtl_id' 			 => $record['pkgprmdtl_id'],
				'pkgprm_name' 			 => $record['pkgprm_name'],
				'pkgprm_pic' 			 => $record['pkgprm_pic'],
				'pkgprmdtl_discout' 	 => $record['pkgprmdtl_discout'],
				'pkgprmdtl_discout_type' => $record['pkgprmdtl_discout_type']
			);
		}
		$smarty->assign('pkgPromotions', $pkgPromotions);
	}

	// Get service_list promotion detail data
	$svlPromotions = array();
	$sql = "SELECT 		svlprm.custype_id,
						svlprm.svlprm_name,
						svlprm.svlprm_pic,
						svlprmdtl.svlprmdtl_id,
						svlprmdtl.svl_id,
						svlprmdtl.svlprmdtl_discout,
						svlprmdtl.svlprmdtl_discout_type 
			FROM 		service_list_promotion_details svlprmdtl,
						service_list_promotions svlprm 
			WHERE 		svlprmdtl.svlprm_id = svlprm.svlprm_id AND 
						svlprmdtl.svlprmdtl_startdate <= '$nowDate' AND 
						(
							svlprmdtl.svlprmdtl_enddate IS NULL OR
							svlprmdtl.svlprmdtl_enddate >= '$nowDate'
						)";
	$result = mysql_query($sql, $dbConn);
	$rows 	= mysql_num_rows($result);
	if($rows > 0) {
		for($i=0; $i<$rows; $i++) {
			$record			= mysql_fetch_assoc($result);
			$custype_id 	= $record['custype_id'];
			$svl_id 		= $record['svl_id'];

			// Sale promotion
			$svlPromotions[$custype_id][$svl_id] = array(
				'svlprmdtl_id' 			 => $record['svlprmdtl_id'],
				'svlprm_name' 			 => $record['svlprm_name'],
				'svlprm_pic' 			 => $record['svlprm_pic'],
				'svlprmdtl_discout' 	 => $record['svlprmdtl_discout'],
				'svlprmdtl_discout_type' => $record['svlprmdtl_discout_type']
			);
		}
		$smarty->assign('svlPromotions', $svlPromotions);
	}

	// Get package_service_lists data
	$pkgsvlData = array();
	$sql = "SELECT 		pkg.pkg_id,
						s.svl_id,
						s.svl_name 
			FROM 		packages pkg,
						package_service_lists pkgsvl,
						service_lists s  
			WHERE 		pkg.pkg_id = pkgsvl.pkg_id AND 
						pkgsvl.svl_id = s.svl_id";
	$result = mysql_query($sql, $dbConn);
	$rows 	= mysql_num_rows($result);
	if($rows > 0) {
		for($i=0; $i<$rows; $i++) {
			$record	= mysql_fetch_assoc($result);

			if(!isset($pkgsvlData[$record['pkg_id']])) {
				$pkgsvlData[$record['pkg_id']] = array();
			}

			$pkgsvlValues = array(
				'svl_id' 	=> $record['svl_id'],
				'svl_name' 	=> $record['svl_name']
			);
			array_push($pkgsvlData[$record['pkg_id']], $pkgsvlValues);
		}
		$smarty->assign('pkgsvlData', $pkgsvlData);
	}

	// Hide edit button from status
	if($values['status_id'] != 'S01') {
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
		$insertResult 		  = true;
		$errTxt 			  = '';

		// Rename Image
		if(strpos($formData['bkg_transfer_evidence'], 'temp_') !== FALSE) {
			$type		= str_replace(".", "", strrchr($formData['bkg_transfer_evidence'],"."));
			$tmpRecord	= new TableSpa('booking', null);
			$bkg_transfer_evidence	= $tmpRecord->genKeyCharRunning().".$type";
			$bkg_transfer_evidence 	= str_replace('/', '_', $bkg_transfer_evidence);
			$bkg_transfer_evidence_path = '../img/booking/'.$bkg_transfer_evidence;

			// Delete Old Image
			if(file_exists($bkg_transfer_evidence_path)) {
				if(!unlink($bkg_transfer_evidence_path)) {
					$response['status'] = 'DELETE_OLD_IMG_FAIL';
					echo json_encode($response);
					exit();
				}
			}

			if(rename('../img/temp/'.$formData['bkg_transfer_evidence'], $bkg_transfer_evidence_path)) {
				$formData['bkg_transfer_evidence'] = $bkg_transfer_evidence;
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
		// Init booking status
		array_push($values['fieldName'], 'status_id');
		array_push($values['fieldValue'], 'S01');

		// Insert booking
		$tableRecord = new TableSpa($tableName, $values['fieldName'], $values['fieldValue']);
		if(!$tableRecord->insertSuccess()) {
			$insertResult = false;
			$errTxt .= 'INSERT_BOOKING_FAIL\n';
			$errTxt .= mysql_error($dbConn).'\n\n';
		}
		$bkg_id = $tableRecord->getKey();
		
		// Insert booking packages
		if(isset($formData['pkg_id']) && is_array($formData['pkg_id'])) {
			foreach ($formData['pkg_id'] as $key => $pkg_id) {
				$bkgpkg_date 		= $formData['bkgpkg_date'][$key];
				$bkgpkg_time 		= $formData['bkgpkg_time'][$key];
				$bkgpkg_persons 	= $formData['pkg_qty'][$key];
				$bkgpkg_total_price = $formData['bkgpkg_total_price'][$key];
				$bkgpkgValues 		= array($pkg_id, $bkg_id, $bkgpkg_date, $bkgpkg_time, $bkgpkg_total_price, $bkgpkg_persons);
				$bkgpkgRecord 		= new TableSpa('booking_packages', $bkgpkgValues);
				if(!$bkgpkgRecord->insertSuccess()) {
					$insertResult = false;
					$errTxt .= 'INSERT_BOOKING_PACKAGES['.($key+1).']_FAIL\n';
					$errTxt .= mysql_error($dbConn).'\n\n';
				}
			}
		}
		// End booking packages

		// Insert booking service_list
		if(isset($formData['svl_id']) && is_array($formData['svl_id'])) {
			foreach ($formData['svl_id'] as $key => $svl_id) {
				$bkgsvl_date 		= $formData['bkgsvl_date'][$key];
				$bkgsvl_time 		= $formData['bkgsvl_time'][$key];
				$bkgsvl_persons 	= $formData['svl_qty'][$key];
				$bkgsvl_total_price = $formData['bkgsvl_total_price'][$key];
				$bkgsvlValues 		= array($svl_id, $bkg_id, $bkgsvl_date, $bkgsvl_time, $bkgsvl_total_price, $bkgsvl_persons);
				$bkgsvlRecord 		= new TableSpa('booking_service_lists', $bkgsvlValues);
				if(!$bkgsvlRecord->insertSuccess()) {
					$insertResult = false;
					$errTxt .= 'INSERT_BOOKING_SERVICE_LISTS['.($key+1).']_FAIL\n';
					$errTxt .= mysql_error($dbConn).'\n\n';
				}
			}
		}
		// End booking packages

		if($insertResult) {
			$response['status'] = 'ADD_PASS';
			echo json_encode($response);
		} else {
			$response['status'] = $errTxt;
			echo json_encode($response);
		}
		

	} else if($action == 'EDIT') {
		//2.2 Update record
		$tableRecord 	= new TableSpa($tableName, $code);
		$updateResult  	= true;
		$errTxt 		= '';

		// Rename Image
		if(strpos($formData['bkg_transfer_evidence'], 'temp_') !== FALSE) {
			$type		= str_replace(".", "", strrchr($formData['bkg_transfer_evidence'],"."));
			$bkg_transfer_evidence = $code.".$type";
			$bkg_transfer_evidence = str_replace('/', '_', $bkg_transfer_evidence);
			$imgTmpPath = '../img/temp/'.$formData['bkg_transfer_evidence'];
			$imgNewPath = '../img/booking/'.$bkg_transfer_evidence;

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
					$formData['bkg_transfer_evidence'] = $bkg_transfer_evidence;
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

		// Get package_service_list
		$pkgSvlIdList = array();
		$sql = "SELECT 		ps.pkgsvl_id,
							p.pkg_id, 
							s.svl_id 
				FROM 		packages p, 
							package_service_lists ps,
							service_lists s 
				WHERE 		p.pkg_id = ps.pkg_id AND 
							s.svl_id = ps.svl_id";
		$result 	= mysql_query($sql, $dbConn);
		$rows 		= mysql_num_rows($result);
		if($rows > 0) {
			for($i=0; $i<$rows; $i++) {
				$record = mysql_fetch_assoc($result);
				$pkgSvlIdList[$record['pkg_id']][$record['svl_id']] = $record['pkgsvl_id'];
			}
		}

		// Update booking
		if(!$tableRecord->commit()) {
			$updateResult = false;
			$errTxt .= 'EDIT_BOOKING_FAIL\n';
			$errTxt .= mysql_error($dbConn).'\n\n';
		}

		### Update booking_packages
		// Delete booking_packages if delete old booking_packages
		$oldBookingPkgList = array();
		$newBookingPkgList = array();
		// Find old booking_service_lists
		$sql = "SELECT bkgpkg_id FROM booking_packages WHERE bkg_id = '$code'";
		$result = mysql_query($sql, $dbConn);
		$rows 	= mysql_num_rows($result);
		for($i=0; $i<$rows; $i++) {
			$oldBookingPkgRecord = mysql_fetch_assoc($result);
			array_push($oldBookingPkgList, $oldBookingPkgRecord['bkgpkg_id']);
		}
		// Find new booking_service_lists
		if(isset($formData['bkgpkg_id']) && is_array($formData['bkgpkg_id'])) {
			foreach ($formData['bkgpkg_id'] as $key => $newbkgpkg_id) {
				array_push($newBookingPkgList, $newbkgpkg_id);
			}
		}
		

		// Check for delete booking_packages
		foreach ($oldBookingPkgList as $key => $oldbkgpkg_id) {
			if(!in_array($oldbkgpkg_id, $newBookingPkgList)) {
				// Delete booking_packages
				$bookingPkgRecord 	= new TableSpa('booking_packages', $oldbkgpkg_id);
				if(!$bookingPkgRecord->delete()) {
					$updateResult = false;
					$errTxt .= "DELETE_BOOKING_PACKAGES[$oldbkgpkg_id]_FAIL\n";
					$errTxt .= mysql_error($dbConn).'\n\n';
				}
			}
		}

		// Update or Add booking_packages
		if(isset($formData['pkg_id']) && is_array($formData['pkg_id'])) {
			foreach ($formData['pkg_id'] as $key => $pkg_id) {
				$bkgpkg_date  = $formData['bkgpkg_date'][$key];
				$bkgpkg_time  = $formData['bkgpkg_time'][$key];
				$bkgpkg_persons  = $formData['pkg_qty'][$key];
				$bkgpkg_total_price = $formData['bkgpkg_total_price'][$key];

				if(isset($formData['bkgpkg_id'][$key])) {
					// Update booking_packages
					$bkgpkg_id = $formData['bkgpkg_id'][$key];
					$bookingPkgRecord 	= new TableSpa('booking_packages', $bkgpkg_id);
					$bookingPkgRecord->setFieldValue('pkg_id', $pkg_id);
					$bookingPkgRecord->setFieldValue('bkgpkg_date', $bkgpkg_date);
					$bookingPkgRecord->setFieldValue('bkgpkg_time', $bkgpkg_time);
					$bookingPkgRecord->setFieldValue('bkgpkg_persons', $bkgpkg_persons);
					$bookingPkgRecord->setFieldValue('bkgpkg_total_price', $bkgpkg_total_price);
					if(!$bookingPkgRecord->commit()) {
						$updateResult = false;
						$errTxt .= 'EDIT_BOOKING_PACKAGES['.($key+1).']_FAIL\n';
						$errTxt .= mysql_error($dbConn).'\n\n';
					}
				} else {
					// Add new booking_packages
					$bkgpkgValues 		= array($pkg_id, $code, $bkgpkg_date, $bkgpkg_time, $bkgpkg_total_price, $bkgpkg_persons);
					$bookingPkgRecord 	= new TableSpa('booking_packages', $bkgpkgValues);
					if(!$bookingPkgRecord->insertSuccess()) {
						$updateResult = false;
						$errTxt .= 'ADD_BOOKING_PACKAGES['.($key+1).']_FAIL\n';
						$errTxt .= mysql_error($dbConn).'\n\n';
					}
				}
			}
		}
		### End update booking_packages


		### Update booking_service_lists
		// Delete booking_service_lists if delete old booking_service_lists
		$oldBookingSvlList = array();
		$newBookingSvlList = array();
		// Find old booking_service_lists
		$sql = "SELECT bkgsvl_id FROM booking_service_lists WHERE bkg_id = '$code'";
		$result = mysql_query($sql, $dbConn);
		$rows 	= mysql_num_rows($result);
		for($i=0; $i<$rows; $i++) {
			$oldBookingSvlRecord = mysql_fetch_assoc($result);
			array_push($oldBookingSvlList, $oldBookingSvlRecord['bkgsvl_id']);
		}
		// Find new booking_service_lists
		if(isset($formData['bkgsvl_id']) && is_array($formData['bkgsvl_id'])) {
			foreach ($formData['bkgsvl_id'] as $key => $newbkgsvl_id) {
				array_push($newBookingSvlList, $newbkgsvl_id);
			}
		}
		
		

		// Check for delete booking_service_lists
		foreach ($oldBookingSvlList as $key => $oldbkgsvl_id) {
			if(!in_array($oldbkgsvl_id, $newBookingSvlList)) {
				// Delete booking_service_lists
				$bookingSvlRecord 	= new TableSpa('booking_service_lists', $oldbkgsvl_id);
				if(!$bookingSvlRecord->delete()) {
					$updateResult = false;
					$errTxt .= "DELETE_BOOKING_SERVICE_LISTS[$oldbkgsvl_id]_FAIL\n";
					$errTxt .= mysql_error($dbConn).'\n\n';
				}
			}
		}

		// Update or Add booking_service_lists
		if(isset($formData['svl_id']) && is_array($formData['svl_id'])) {
			foreach ($formData['svl_id'] as $key => $svl_id) {
				$bkgsvl_date  = $formData['bkgsvl_date'][$key];
				$bkgsvl_time  = $formData['bkgsvl_time'][$key];
				$bkgsvl_persons  = $formData['svl_qty'][$key];
				$bkgsvl_total_price = $formData['bkgsvl_total_price'][$key];

				if(isset($formData['bkgsvl_id'][$key])) {
					// Update booking_service_lists
					$bkgsvl_id = $formData['bkgsvl_id'][$key];
					$bookingSvlRecord 	= new TableSpa('booking_service_lists', $bkgsvl_id);
					$bookingSvlRecord->setFieldValue('svl_id', $svl_id);
					$bookingSvlRecord->setFieldValue('bkgsvl_date', $bkgsvl_date);
					$bookingSvlRecord->setFieldValue('bkgsvl_time', $bkgsvl_time);
					$bookingSvlRecord->setFieldValue('bkgsvl_persons', $bkgsvl_persons);
					$bookingSvlRecord->setFieldValue('bkgsvl_total_price', $bkgsvl_total_price);
					if(!$bookingSvlRecord->commit()) {
						$updateResult = false;
						$errTxt .= 'EDIT_BOOKING_SERVICE_LISTS['.($key+1).']_FAIL\n';
						$errTxt .= mysql_error($dbConn).'\n\n';
					}
				} else {
					// Add new booking_service_lists
					$bkgsvlValues 		= array($svl_id, $code, $bkgsvl_date, $bkgsvl_time, $bkgsvl_total_price, $bkgsvl_persons);
					$bookingSvlRecord 	= new TableSpa('booking_service_lists', $bkgsvlValues);
					if(!$bookingSvlRecord->insertSuccess()) {
						$updateResult = false;
						$errTxt .= 'ADD_BOOKING_SERVICE_LISTS['.($key+1).']_FAIL\n';
						$errTxt .= mysql_error($dbConn).'\n\n';
					}
				}
			}
		}
		### End update booking_service_lists

		if($updateResult) {
			$response['status'] = 'EDIT_PASS';
			echo json_encode($response);
		} else {
			$response['status'] = $errTxt;
			echo json_encode($response);
		}


	}
}
?>