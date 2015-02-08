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
		$valuesDetail = array();
		$sql = "SELECT 	o.orddtl_id, 
						p.pkg_id, 
						o.serpkg_amount,
						u.unit_name 
				FROM 	service_packages o, products p, units u 
				WHERE 	o.pkg_id = p.pkg_id AND p.unit_id = u.unit_id 
						AND ser_id = '$code' ORDER BY orddtl_id";
		$result = mysql_query($sql, $dbConn);
		$rows 	= mysql_num_rows($result);
		for($i=0; $i<$rows; $i++) {
			array_push($valuesDetail, mysql_fetch_assoc($result));
		}
		$smarty->assign('valuesDetail', $valuesDetail);

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
		// End service packages

		// Insert service service_list
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

		// Update services
		if($tableRecord->commit()) {
			$updateservicesResult = true;
		} else {
			$updateservicesResult = true;
		}

		if($updateservicesResult) {
			// Delete service_packages if delete old service_packages
			$oldOrderDetailList = array();
			$newOrderDetailList = array();
			// Find old service_packages
			$sql = "SELECT orddtl_id FROM service_packages WHERE ser_id = '$code'";
			$result = mysql_query($sql, $dbConn);
			$rows 	= mysql_num_rows($result);
			for($i=0; $i<$rows; $i++) {
				$oldOrddtlRecord = mysql_fetch_assoc($result);
				array_push($oldOrderDetailList, $oldOrddtlRecord['orddtl_id']);
			}
			// Find new order_detail
			foreach ($formData['orddtl_id'] as $key => $newOrddtl_id) {
				array_push($newOrderDetailList, $newOrddtl_id);
			}
			// Check for delete 
			foreach ($oldOrderDetailList as $key => $oldOrddtl_id) {
				if(!in_array($oldOrddtl_id, $newOrderDetailList)) {
					// Delete service_packages
					$serpkgReccord 	= new TableSpa('service_packages', $oldOrddtl_id);
					if(!$serpkgReccord->delete()) {
						$updateservicesDetailResult = false;
						$updateservicesDetailError .= "DELETE_services_DETAIL[$oldOrddtl_id]_FAIL\n";
					}
				}
			}

			
			// Update or Add service_packages
			$updateservicesDetailResult = true;
			$updateservicesDetailError  = '';

			foreach ($formData['pkg_id'] as $key => $pkg_id) {
				$serpkg_amount 	= $formData['pkg_qty'][$key];

				if(isset($formData['orddtl_id'][$key])) {
					// Update service_packages
					$orddtl_id = $formData['orddtl_id'][$key];
					$serpkgReccord 	= new TableSpa('service_packages', $orddtl_id);
					$serpkgReccord->setFieldValue('pkg_id', $pkg_id);
					$serpkgReccord->setFieldValue('serpkg_amount', $serpkg_amount);
					if(!$serpkgReccord->commit()) {
						$updateservicesDetailResult = false;
						$updateservicesDetailError .= 'EDIT_services_DETAIL['.($key+1).']_FAIL\n';
					}
				} else {
					// Add new service_packages
					$serpkgValues 		= array($code, $pkg_id, $serpkg_amount);
					$serpkgReccord 	= new TableSpa('service_packages', $serpkgValues);
					if(!$serpkgReccord->insertSuccess()) {
						$updateservicesDetailResult = false;
						$updateservicesDetailError .= 'ADD_services_DETAIL['.($key+1).']_FAIL\n';
					}
				}
			}

			if($updateservicesDetailResult) {
				// Edit order and service_packages success
				$response['status'] = 'EDIT_PASS';
				echo json_encode($response);
			} else {
				// Edit service_packages fail
				$response['status'] = $updateservicesDetailError;
				echo json_encode($response);
			}

		} else {
			// Edit services fail
			$response['status'] = 'EDIT_services_FAIL';
			echo json_encode($response);
		}


	}
}
?>