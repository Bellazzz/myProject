<?php
$action			= isset($_REQUEST['action']) ? $_REQUEST['action'] : 'ADD';
$tableName		= 'promotion_discout_sales';
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

		// Get table promotion_discout_sales data
		$valuesDetail = array();
		$sql = "SELECT 		pd.prmdsdtl_id, pd.prdtyp_id 
				FROM 		promotion_discout_sales p, promotion_discout_sale_details pd 
				WHERE 		p.prmds_id = pd.prmds_id AND 
							p.prmds_id = '$code' 
				ORDER BY 	pd.prdtyp_id";
		$result = mysql_query($sql, $dbConn);
		$rows 	= mysql_num_rows($result);
		for($i=0; $i<$rows; $i++) {
			array_push($valuesDetail, mysql_fetch_assoc($result));
		}
		$smarty->assign('valuesDetail', $valuesDetail);

	} else if($action == 'VIEW_DETAIL') {
		// Get table promotion_discout_sales data
		$tableRecord = new TableSpa($tableName, $code);
		$values      = array();
		foreach($tableInfo['fieldNameList'] as $field => $value) {
			$values[$field] = $tableRecord->getFieldValue($field);
		}
		// Date thai format
		$values['prmds_startdate_th'] 	= dateThaiFormat($values['prmds_startdate']);
		$values['prmds_enddate_th'] 	= dateThaiFormat($values['prmds_enddate']);
		$smarty->assign('values', $values);

		// Get product types of promotion_discout_sales
		$prmdsdtlDetailList = array();
		$sql 	= "	SELECT pd.prmdsdtl_id,
					p.prdtyp_id,
					p.prdtyp_name 
					FROM promotion_discout_sale_details pd, product_types p 
					WHERE pd.prdtyp_id = p.prdtyp_id 
					AND pd.prmds_id = '$code' 
					ORDER BY p.prdtyp_id";
		$result = mysql_query($sql, $dbConn);
		$rows 	= mysql_num_rows($result);
		for($i=0; $i<$rows; $i++) {
			array_push($prmdsdtlDetailList, mysql_fetch_assoc($result));
			$prmdsdtlDetailList[$i]['no'] = $i+1;
		}
		$smarty->assign('prmdsdtlDetailList', $prmdsdtlDetailList);
	}

	// Get reference data for selectReferenceJS
	if(is_array($tableInfo['referenceData']) && count($tableInfo['referenceData']) > 0) {
		$sqlRefData 	= '';
		$referenceData 	= array();

		foreach ($tableInfo['referenceData'] as $key => $table) {
			switch ($table) {
				case 'product_promotion_groups':
					$sqlRefData = "	SELECT 		prdprmgrp_id refValue,
												prdprmgrp_name refText 
									FROM 		product_promotion_groups 
									ORDER BY 	refText ASC";
					$refField 	= 'prdprmgrp_id';
					break;

				case 'product_types':
					$sqlRefData = "	SELECT 		prdtyp_id refValue,
												prdtyp_name refText 
									FROM 		product_types 
									ORDER BY 	prdtyp_name ASC";
					$refField 	= 'prdtyp_id';
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
		
		// Insert promotion_discout_sales
		$tableRecord = new TableSpa($tableName, $values['fieldName'], $values['fieldValue']);
		if(!$tableRecord->insertSuccess()) {
			$response['status'] = 'ADD_PROMOTION_DISCOUT_SALES_FAIL';
			echo json_encode($response);
		}

		// Insert promotion_discout_sale_details
		$insertPrmdsdtlResult = true;
		$insertPrmdsdtlError  = '';
		$prmds_id = $tableRecord->getKey();
		foreach ($formData['prdtyp_id'] as $key => $prdtyp_id) {
			$prmdsdtlValues 	= array($prmds_id, $prdtyp_id);
			$prmdsdtlRecord 	= new TableSpa('promotion_discout_sale_details', $prmdsdtlValues);
			if(!$prmdsdtlRecord->insertSuccess()) {
				$insertPrmdsdtlResult = false;
				$insertPrmdsdtlError .= 'ADD_PROMOTION_DISCOUT_SALE_DETAILS['.($key+1).']_FAIL\n';
			}
		}

		if($insertPrmdsdtlResult) {
			// Add promotion_discout_sales and promotion_discout_sale_details success
			$response['status'] = 'ADD_PASS';
			echo json_encode($response);
		} else {
			$response['status'] = $insertPrmdsdtlError;
			echo json_encode($response);
		}
	} else if($action == 'EDIT') {
		//2.2 Update record
		$tableRecord = new TableSpa($tableName, $code);

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

		// Delete promotion_discout_sale_details if delete old promotion_discout_sale_details
		$oldPrmdsdtlList = array();
		$newPrmdsdtlList = array();
		// Find old promotion_discout_sale_details
		$sql = "SELECT prmdsdtl_id FROM promotion_discout_sale_details WHERE prmds_id = '$code'";
		$result = mysql_query($sql, $dbConn);
		$rows 	= mysql_num_rows($result);
		for($i=0; $i<$rows; $i++) {
			$oldPrmdsdtlRecord = mysql_fetch_assoc($result);
			array_push($oldPrmdsdtlList, $oldPrmdsdtlRecord['prmdsdtl_id']);
		}
		// Find new promotion_discout_sale_details
		foreach ($formData['prmdsdtl_id'] as $key => $newPrmdsdtl_id) {
			array_push($newPrmdsdtlList, $newPrmdsdtl_id);
		}
		// Check for delete 
		foreach ($oldPrmdsdtlList as $key => $oldPrmdsdtl_id) {
			if(!in_array($oldPrmdsdtl_id, $newPrmdsdtlList)) {
				// Delete promotion_discout_sale_details
				$prmdsdtlRecord 	= new TableSpa('promotion_discout_sale_details', $oldPrmdsdtl_id);
				if(!$prmdsdtlRecord->delete()) {
					$updatePrmdsdtlResult = false;
					$updatePrmdsdtlError .= "DELETE_PROMOTION_DISCOUT_SALE_DETAILS[$oldPrmdsdtl_id]_FAIL\n";
				}
			}
		}

		// Update or Add promotion_discout_sale_details
		$updatePrmdsdtlResult = true;
		$updatePrmdsdtlError  = '';

		foreach ($formData['prdtyp_id'] as $key => $prdtyp_id) {
			if(isset($formData['prmdsdtl_id'][$key])) {
				// Update promotion_discout_sale_details
				$prmdsdtl_id 		= $formData['prmdsdtl_id'][$key];
				$prmdsdtlRecord 	= new TableSpa('promotion_discout_sale_details', $prmdsdtl_id);
				$prmdsdtlRecord->setFieldValue('prdtyp_id', $prdtyp_id);
				if(!$prmdsdtlRecord->commit()) {
					$updatePrmdsdtlResult = false;
					$updatePrmdsdtlError .= 'EDIT_PROMOTION_DISCOUT_SALE_DETAILS['.($key+1).']_FAIL\n';
				}
			} else {
				// Add new promotion_discout_sale_details
				$prmdsdtlValues 	= array($code, $prdtyp_id);
				$prmdsdtlRecord 	= new TableSpa('promotion_discout_sale_details', $prmdsdtlValues);
				if(!$prmdsdtlRecord->insertSuccess()) {
					$updatePrmdsdtlResult = false;
					$updatePrmdsdtlError .= 'ADD_PROMOTION_DISCOUT_SALE_DETAILS['.($key+1).']_FAIL\n';
				}
			}
		}

		if($updatePrmdsdtlResult) {
			// Edit promotion_discout_sales and promotion_discout_sale_details success
			$response['status'] = 'EDIT_PASS';
			echo json_encode($response);
		} else {
			// Edit promotion_discout_sale_details fail
			$response['status'] = $updatePrmdsdtlError;
			echo json_encode($response);
		}
	}
}
?>