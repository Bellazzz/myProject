<?php
session_start();
$action			= isset($_REQUEST['action']) ? $_REQUEST['action'] : 'ADD';
$tableName		= 'services';
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
		// Get table services data
		$tableRecord = new TableSpa($tableName, $code);
		$values      = array();
		foreach($tableInfo['fieldNameList'] as $field => $value) {
			$values[$field] = $tableRecord->getFieldValue($field);
		}
		$smarty->assign('values', $values);

		// Get table service_packages data
		$valuesPkg = array();
		$sql = "SELECT 		s.serpkg_id, 
							p.pkg_id, 
							s.serpkg_amount,
							p.pkg_price 
				FROM 		service_packages s, packages p  
				WHERE 		s.pkg_id = p.pkg_id AND 
							ser_id = '$code' 
				ORDER BY 	serpkg_id";
		$result = mysql_query($sql, $dbConn);
		$rows 	= mysql_num_rows($result);
		for($i=0; $i<$rows; $i++) {
			array_push($valuesPkg, mysql_fetch_assoc($result));
		}
		$smarty->assign('valuesPkg', $valuesPkg);

		// Get table service_service_lists data
		$valuesSvl = array();
		$sql = "SELECT 		ss.sersvl_id, 
							s.svl_id, 
							ss.sersvl_amount,
							s.svl_price 
				FROM 		service_service_lists ss, service_lists s  
				WHERE 		ss.svl_id = s.svl_id AND 
							ser_id = '$code' 
				ORDER BY 	sersvl_id";
		$result = mysql_query($sql, $dbConn);
		$rows 	= mysql_num_rows($result);
		for($i=0; $i<$rows; $i++) {
			array_push($valuesSvl, mysql_fetch_assoc($result));
		}
		$smarty->assign('valuesSvl', $valuesSvl);

	} else if($action == 'VIEW_DETAIL') {
		// Get table services data
		$tableRecord = new TableSpa($tableName, $code);
		$values      = array();
		foreach($tableInfo['fieldNameList'] as $field => $value) {
			$values[$field] = $tableRecord->getFieldValue($field);
		}
		// Date thai format
		$values['ord_date_th']  	= dateThaiFormat($values['ord_date']);
		$values['ord_snd_date_th'] 	= dateThaiFormat($values['ord_snd_date']);
		$smarty->assign('values', $values);
		
		// Get detail of services
		$orderDetailList = array();
		$sql 	= "	SELECT o.serpkg_amount,
					p.pkg_id,
					p.prd_name,
					p.prd_price,
					u.unit_name 
					FROM service_packages o, products p, units u 
					WHERE o.pkg_id = p.pkg_id AND p.unit_id = u.unit_id 
					AND o.ser_id = '$code'";
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

				case 'beds':
					$sqlRefData = "	SELECT 		bed_id refValue,
												bed_name refText 
									FROM 		beds 
									ORDER BY 	refText ASC";
					$refField 	= 'bed_id';
					break;

				case 'booking':
					$sqlRefData = "	SELECT 		bkg_id refValue,
												bkg_id refText 
									FROM 		booking 
									ORDER BY 	refText DESC";
					$refField 	= 'bkg_id';
					break;

				case 'pay_types':
					$sqlRefData = "	SELECT 		paytyp_id refValue,
												paytyp_name refText 
									FROM 		pay_types 
									ORDER BY 	refText ASC";
					$refField 	= 'paytyp_id';
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
						)";echo $sql;
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

		// Insert services
		$tableRecord = new TableSpa($tableName, $values['fieldName'], $values['fieldValue']);
		if(!$tableRecord->insertSuccess()) {
			$insertResult = false;
			$errTxt .= 'INSERT_SERVICES_FAIL\n';
			$errTxt .= mysql_error($dbConn).'\n\n';
		}
		$ser_id = $tableRecord->getKey();
		
		// Insert service packages
		if(isset($formData['pkg_id']) && is_array($formData['pkg_id'])) {
			foreach ($formData['pkg_id'] as $key => $pkg_id) {
				$serpkg_amount 	= $formData['pkg_qty'][$key];
				$serpkg_total_price 		= $formData['serpkg_total_price'][$key];
				$serpkgValues 		= array($ser_id, $pkg_id, $serpkg_amount, $serpkg_total_price);
				$serpkgRecord 	= new TableSpa('service_packages', $serpkgValues);
				if(!$serpkgRecord->insertSuccess()) {
					$insertResult = false;
					$errTxt .= 'INSERT_SERVICE_PACKAGES['.($key+1).']_FAIL\n';
					$errTxt .= mysql_error($dbConn).'\n\n';
				}


				// Insert service packages promotion (Sale)
				if(hasValue($formData['prmSale_'.$pkg_id.'_pkgprmdtl_id'])) {
					$pkgprmdtl_id 				= $formData['prmSale_'.$pkg_id.'_pkgprmdtl_id'];
					$serpkgprm_amount 			= $formData['prmSale_'.$pkg_id.'_serpkgprm_amount'];
					$serpkgprm_discout_total 	= $formData['prmSale_'.$pkg_id.'_serpkgprm_discout_total'];
					$serpkgprmValues 			= array($ser_id, $pkgprmdtl_id, $serpkgprm_amount, $serpkgprm_discout_total);
					$serpkgprmRecord 			= new TableSpa('service_package_promotions', $serpkgprmValues);
					if(!$serpkgprmRecord->insertSuccess()) {
						$insertResult = false;
						$errTxt .= 'INSERT_SERVICE_PACKAGE_PROMOTIONS['.($key+1).']_FAIL\n';
						$errTxt .= mysql_error($dbConn).'\n\n';
					}
				}
			}
		}
		// End service packages

		// Insert service service_list
		if(isset($formData['svl_id']) && is_array($formData['svl_id'])) {
			foreach ($formData['svl_id'] as $key => $svl_id) {
				$sersvl_amount 	= $formData['svl_qty'][$key];
				$sersvl_total_price 		= $formData['sersvl_total_price'][$key];
				$sersvlValues 		= array($ser_id, $svl_id, $sersvl_amount, $sersvl_total_price);
				$sersvlRecord 	= new TableSpa('service_service_lists', $sersvlValues);
				if(!$sersvlRecord->insertSuccess()) {
					$insertResult = false;
					$errTxt .= 'INSERT_SERVICE_SERVICE_LISTS['.($key+1).']_FAIL\n';
					$errTxt .= mysql_error($dbConn).'\n\n';
				}


				// Insert service service_list promotion (Sale)
				if(hasValue($formData['prmSale_'.$svl_id.'_svlprmdtl_id'])) {
					$svlprmdtl_id 				= $formData['prmSale_'.$svl_id.'_svlprmdtl_id'];
					$sersvlprm_amount 			= $formData['prmSale_'.$svl_id.'_sersvlprm_amount'];
					$sersvlprm_discout_total 	= $formData['prmSale_'.$svl_id.'_sersvlprm_discout_total'];
					$sersvlprmValues 			= array($ser_id, $svlprmdtl_id, $sersvlprm_amount, $sersvlprm_discout_total);
					$sersvlprmRecord 			= new TableSpa('service_service_list_promotions', $sersvlprmValues);
					if(!$sersvlprmRecord->insertSuccess()) {
						$insertResult = false;
						$errTxt .= 'INSERT_SERVICE_SERVICE_LIST_PROMOTIONS['.($key+1).']_FAIL\n';
						$errTxt .= mysql_error($dbConn).'\n\n';
					}
				}
			}
		}
		// End service packages



		if($insertResult) {
			$response['status'] = 'ADD_PASS';
			echo json_encode($response);
		} else {
			$response['status'] = $errTxt;
			echo json_encode($response);
		}
		

	} else if($action == 'EDIT') {
		//2.2 Update record
		$tableRecord = new TableSpa($tableName, $code);
		$updateResult  	= true;
		$errTxt 		= '';

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

		// Update Services
		if(!$tableRecord->commit()) {
			$updateResult = false;
			$errTxt .= 'EDIT_SERVICES_FAIL\n';
			$errTxt .= mysql_error($dbConn).'\n\n';
		}

		### Update service_packages & service_package_promotions
		// Delete service_packages if delete old service_packages
		$oldServicePkgList = array();
		$newServicePkgList = array();
		$oldPkgPrmDtlList = array();
		$newPkgPrmDtlList = array();
		$oldSerPkgPrmList = array();
		// Find old service_packages
		$sql = "SELECT serpkg_id FROM service_packages WHERE ser_id = '$code'";
		$result = mysql_query($sql, $dbConn);
		$rows 	= mysql_num_rows($result);
		for($i=0; $i<$rows; $i++) {
			$oldServicePkgRecord = mysql_fetch_assoc($result);
			array_push($oldServicePkgList, $oldServicePkgRecord['serpkg_id']);
		}
		// Find old service_package_promotions
		$sql = "SELECT serpkgprm_id, pkgprmdtl_id FROM service_package_promotions WHERE ser_id = '$code'";
		$result = mysql_query($sql, $dbConn);
		$rows 	= mysql_num_rows($result);
		for($i=0; $i<$rows; $i++) {
			$oldServicePkgPrmRecord = mysql_fetch_assoc($result);
			array_push($oldPkgPrmDtlList, $oldServicePkgPrmRecord['pkgprmdtl_id']);
			$oldSerPkgPrmList[$oldServicePkgPrmRecord['pkgprmdtl_id']] = $oldServicePkgPrmRecord['serpkgprm_id'];
		}
		// Find new service_packages
		foreach ($formData['serpkg_id'] as $key => $newserpkg_id) {
			array_push($newServicePkgList, $newserpkg_id);
		}
		// Find new service_package_promotions
		foreach ($formData['pkg_id'] as $key => $pkg_id) {
			if(hasValue($formData['prmSale_'.$pkg_id.'_pkgprmdtl_id'])) {
				array_push($newPkgPrmDtlList, $formData['prmSale_'.$pkg_id.'_pkgprmdtl_id']);
			}
		}
		

		// Check for delete service_packages
		foreach ($oldServicePkgList as $key => $oldserpkg_id) {
			if(!in_array($oldserpkg_id, $newServicePkgList)) {
				// Delete service_packages
				$servicePkgRecord 	= new TableSpa('service_packages', $oldserpkg_id);
				if(!$servicePkgRecord->delete()) {
					$updateResult = false;
					$errTxt .= "DELETE_SERVICE_PACKAGES[$oldserpkg_id]_FAIL\n";
					$errTxt .= mysql_error($dbConn).'\n\n';
				}
			}
		}

		// Check for delete service_package_promotions
		foreach ($oldPkgPrmDtlList as $key => $oldpkgprmdtl_id) {
			if(!in_array($oldpkgprmdtl_id, $newPkgPrmDtlList)) {
				// Delete service_package_promotions
				$serpkgprm_id = $oldSerPkgPrmList[$oldpkgprmdtl_id];
				$servicePkgPrmRecord 	= new TableSpa('service_package_promotions', $serpkgprm_id);
				if(!$servicePkgPrmRecord->delete()) {
					$updateResult = false;
					$errTxt .= "DELETE_SERVICE_PACKAGE_PROMOTIONS[$serpkgprm_id]_FAIL\n";
					$errTxt .= mysql_error($dbConn).'\n\n';
				}
			}
		}

		// Update or Add service_packages
		foreach ($formData['pkg_id'] as $key => $pkg_id) {
			$serpkg_amount  = $formData['pkg_qty'][$key];
			$serpkg_total_price = $formData['serpkg_total_price'][$key];

			if(isset($formData['serpkg_id'][$key])) {
				// Update service_packages
				$serpkg_id = $formData['serpkg_id'][$key];
				$servicePkgRecord 	= new TableSpa('service_packages', $serpkg_id);
				$old_pkg_id 		= $servicePkgRecord->getFieldValue('pkg_id');
				$old_serpkg_amount = $servicePkgRecord->getFieldValue('serpkg_amount');
				$servicePkgRecord->setFieldValue('pkg_id', $pkg_id);
				$servicePkgRecord->setFieldValue('serpkg_amount', $serpkg_amount);
				$servicePkgRecord->setFieldValue('serpkg_total_price', $serpkg_total_price);
				if(!$servicePkgRecord->commit()) {
					$updateResult = false;
					$errTxt .= 'EDIT_SERVICE_PACKAGES['.($key+1).']_FAIL\n';
					$errTxt .= mysql_error($dbConn).'\n\n';
				}
			} else {
				// Add new service_packages
				$saledtlValues 		= array($code, $pkg_id, $serpkg_amount, $serpkg_total_price);
				$servicePkgRecord 	= new TableSpa('service_packages', $saledtlValues);
				if(!$servicePkgRecord->insertSuccess()) {
					$updateResult = false;
					$errTxt .= 'ADD_SERVICE_PACKAGES['.($key+1).']_FAIL\n';
					$errTxt .= mysql_error($dbConn).'\n\n';
				}
			}

			if(hasValue($formData['prmSale_'.$pkg_id.'_pkgprmdtl_id'])) {
				$pkgprmdtl_id 				= $formData['prmSale_'.$pkg_id.'_pkgprmdtl_id'];
				$serpkgprm_amount 			= $formData['prmSale_'.$pkg_id.'_serpkgprm_amount'];
				$serpkgprm_discout_total 	= $formData['prmSale_'.$pkg_id.'_serpkgprm_discout_total'];

				if(!in_array($pkgprmdtl_id, $oldPkgPrmDtlList)) {
					// Add service_package_promotions
					$serpkgprmValues = array($code, $pkgprmdtl_id, $serpkgprm_amount, $serpkgprm_discout_total);
					$serpkgprmRecord = new TableSpa('service_package_promotions', $serpkgprmValues);
					if(!$serpkgprmRecord->insertSuccess()) {
						$updateResult = false;
						$errTxt .= 'INSERT_SERVICE_PACKAGE_PROMOTIONS['.($key+1).']_FAIL\n';
						$errTxt .= mysql_error($dbConn).'\n\n';
					}
				} else {
					// Update service_package_promotions
					$serpkgprm_id = $oldSerPkgPrmList[$pkgprmdtl_id];
					$servicePkgPrmRecord = new TableSpa('service_package_promotions', $serpkgprm_id);
					$servicePkgPrmRecord->setFieldValue('serpkgprm_amount', $serpkgprm_amount);
					$servicePkgPrmRecord->setFieldValue('serpkgprm_discout_total', $serpkgprm_discout_total);
					if(!$servicePkgPrmRecord->commit()) {
						$updateResult = false;
						$errTxt .= 'EDIT_SERVICE_PACKAGE_PROMOTIONS['.($key+1).']_FAIL\n';
						$errTxt .= mysql_error($dbConn).'\n\n';
					}
				}
			}
		}
		### End update service_packages & service_package_promotions


		### Update service_service_lists & service_service_list_promotions
		// Delete service_service_lists if delete old service_service_lists
		$oldServiceSvlList = array();
		$newServiceSvlList = array();
		$oldSvlPrmDtlList = array();
		$newSvlPrmDtlList = array();
		$oldSerSvlPrmList = array();
		// Find old service_service_lists
		$sql = "SELECT sersvl_id FROM service_service_lists WHERE ser_id = '$code'";
		$result = mysql_query($sql, $dbConn);
		$rows 	= mysql_num_rows($result);
		for($i=0; $i<$rows; $i++) {
			$oldServiceSvlRecord = mysql_fetch_assoc($result);
			array_push($oldServiceSvlList, $oldServiceSvlRecord['sersvl_id']);
		}
		// Find old service_service_list_promotions
		$sql = "SELECT sersvlprm_id, svlprmdtl_id FROM service_service_list_promotions WHERE ser_id = '$code'";
		$result = mysql_query($sql, $dbConn);
		$rows 	= mysql_num_rows($result);
		for($i=0; $i<$rows; $i++) {
			$oldServiceSvlPrmRecord = mysql_fetch_assoc($result);
			array_push($oldSvlPrmDtlList, $oldServiceSvlPrmRecord['svlprmdtl_id']);
			$oldSerSvlPrmList[$oldServiceSvlPrmRecord['svlprmdtl_id']] = $oldServiceSvlPrmRecord['sersvlprm_id'];
		}
		// Find new service_service_lists
		if(isset($formData['sersvl_id']) && is_array($formData['sersvl_id'])) {
			foreach ($formData['sersvl_id'] as $key => $newsersvl_id) {
				array_push($newServiceSvlList, $newsersvl_id);
			}
			// Find new service_service_list_promotions
			foreach ($formData['svl_id'] as $key => $svl_id) {
				if(hasValue($formData['prmSale_'.$svl_id.'_svlprmdtl_id'])) {
					array_push($newSvlPrmDtlList, $formData['prmSale_'.$svl_id.'_svlprmdtl_id']);
				}
			}
		}
		
		

		// Check for delete service_service_lists
		foreach ($oldServiceSvlList as $key => $oldsersvl_id) {
			if(!in_array($oldsersvl_id, $newServiceSvlList)) {
				// Delete service_service_lists
				$serviceSvlRecord 	= new TableSpa('service_service_lists', $oldsersvl_id);
				if(!$serviceSvlRecord->delete()) {
					$updateResult = false;
					$errTxt .= "DELETE_SERVICE_SERVICE_LISTS[$oldsersvl_id]_FAIL\n";
					$errTxt .= mysql_error($dbConn).'\n\n';
				}
			}
		}

		// Check for delete service_service_list_promotions
		foreach ($oldSvlPrmDtlList as $key => $oldsvlprmdtl_id) {
			if(!in_array($oldsvlprmdtl_id, $newSvlPrmDtlList)) {
				// Delete service_service_list_promotions
				$sersvlprm_id = $oldSerSvlPrmList[$oldsvlprmdtl_id];
				$serviceSvlPrmRecord 	= new TableSpa('service_service_list_promotions', $sersvlprm_id);
				if(!$serviceSvlPrmRecord->delete()) {
					$updateResult = false;
					$errTxt .= "DELETE_SERVICE_SERVICE_LIST_PROMOTIONS[$sersvlprm_id]_FAIL\n";
					$errTxt .= mysql_error($dbConn).'\n\n';
				}
			}
		}

		// Update or Add service_service_lists
		foreach ($formData['svl_id'] as $key => $svl_id) {
			$sersvl_amount  = $formData['svl_qty'][$key];
			$sersvl_total_price = $formData['sersvl_total_price'][$key];

			if(isset($formData['sersvl_id'][$key])) {
				// Update service_service_lists
				$sersvl_id = $formData['sersvl_id'][$key];
				$serviceSvlRecord 	= new TableSpa('service_service_lists', $sersvl_id);
				$serviceSvlRecord->setFieldValue('svl_id', $svl_id);
				$serviceSvlRecord->setFieldValue('sersvl_amount', $sersvl_amount);
				$serviceSvlRecord->setFieldValue('sersvl_total_price', $sersvl_total_price);
				if(!$serviceSvlRecord->commit()) {
					$updateResult = false;
					$errTxt .= 'EDIT_SERVICE_SERVICE_LISTS['.($key+1).']_FAIL\n';
					$errTxt .= mysql_error($dbConn).'\n\n';
				}
			} else {
				// Add new service_service_lists
				$sersvlValues 		= array($code, $svl_id, $sersvl_amount, $sersvl_total_price);
				$serviceSvlRecord 	= new TableSpa('service_service_lists', $sersvlValues);
				if(!$serviceSvlRecord->insertSuccess()) {
					$updateResult = false;
					$errTxt .= 'ADD_SERVICE_SERVICE_LISTS['.($key+1).']_FAIL\n';
					$errTxt .= mysql_error($dbConn).'\n\n';
				}
			}

			if(hasValue($formData['prmSale_'.$svl_id.'_svlprmdtl_id'])) {
				$svlprmdtl_id 				= $formData['prmSale_'.$svl_id.'_svlprmdtl_id'];
				$sersvlprm_amount 			= $formData['prmSale_'.$svl_id.'_sersvlprm_amount'];
				$sersvlprm_discout_total 	= $formData['prmSale_'.$svl_id.'_sersvlprm_discout_total'];

				if(!in_array($svlprmdtl_id, $oldSvlPrmDtlList)) {
					// Add service_service_list_promotions
					$sersvlprmValues = array($code, $svlprmdtl_id, $sersvlprm_amount, $sersvlprm_discout_total);
					$sersvlprmRecord = new TableSpa('service_service_list_promotions', $sersvlprmValues);
					if(!$sersvlprmRecord->insertSuccess()) {
						$updateResult = false;
						$errTxt .= 'INSERT_SERVICE_SERVICE_LIST_PROMOTIONS['.($key+1).']_FAIL\n';
						$errTxt .= mysql_error($dbConn).'\n\n';
					}
				} else {
					// Update service_service_list_promotions
					$sersvlprm_id = $oldSerSvlPrmList[$svlprmdtl_id];
					$serviceSvlPrmRecord = new TableSpa('service_service_list_promotions', $sersvlprm_id);
					$serviceSvlPrmRecord->setFieldValue('sersvlprm_amount', $sersvlprm_amount);
					$serviceSvlPrmRecord->setFieldValue('sersvlprm_discout_total', $sersvlprm_discout_total);
					if(!$serviceSvlPrmRecord->commit()) {
						$updateResult = false;
						$errTxt .= 'EDIT_SERVICE_SERVICE_LIST_PROMOTIONS['.($key+1).']_FAIL\n';
						$errTxt .= mysql_error($dbConn).'\n\n';
					}
				}
			}
		}
		### End update service_service_lists & service_service_list_promotions

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