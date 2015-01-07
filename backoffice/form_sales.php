<?php
session_start();
$action			= isset($_REQUEST['action']) ? $_REQUEST['action'] : 'ADD';
$tableName		= 'sales';
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
		// Get table Sales data
		$tableRecord = new TableSpa($tableName, $code);
		$values      = array();
		foreach($tableInfo['fieldNameList'] as $field => $value) {
			$values[$field] = $tableRecord->getFieldValue($field);
		}
		$smarty->assign('values', $values);

		// Get table sale_details data
		$valuesDetail = array();
		$saledtlIdList = array();
		$sql = "SELECT 	saledtl_id, 
						prd_id, 
						saledtl_amount 
				FROM 	sale_details  
				WHERE   sale_id = '$code' ORDER BY saledtl_id";
		$result = mysql_query($sql, $dbConn);
		$rows 	= mysql_num_rows($result);
		for($i=0; $i<$rows; $i++) {
			$record = mysql_fetch_assoc($result);
			$valuesDetail[$record['prd_id']] = $record;
			array_push($saledtlIdList, $record['saledtl_id']);
		}
		$smarty->assign('valuesDetail', $valuesDetail);

		// Get table sale_promotion_details data
		$valuesPrmDetail = array();
		$saledtlIdList 	 = wrapSingleQuote($saledtlIdList);
		$prdprmgrp_id 	 = '';
		$sql = "SELECT 	s.saleprmdtl_id, 
						s.saledtl_id, 
						s.prmprd_id,
						s.saleprmdtl_amount,
						s.saleprmdtl_discout,
						prmprd.prmprd_discout_type,
						prdprmgrp.prdprmgrp_id 
				FROM 	sale_promotion_details s,
						promotion_products prmprd,
						product_promotions prdprm,
						product_promotion_groups prdprmgrp 
				WHERE  	s.prmprd_id = prmprd.prmprd_id AND 
						prmprd.prdprm_id = prdprm.prdprm_id AND 
						prdprm.prdprmgrp_id = prdprmgrp.prdprmgrp_id AND 
						s.saledtl_id IN (".implode(',', $saledtlIdList).")";
		$result = mysql_query($sql, $dbConn);
		$rows 	= mysql_num_rows($result);
		for($i=0; $i<$rows; $i++) {
			$record = mysql_fetch_assoc($result);
			array_push($valuesPrmDetail, $record);
			$prdprmgrp_id = $record['prdprmgrp_id'];
		}
		$smarty->assign('valuesPrmDetail', $valuesPrmDetail);
		$smarty->assign('prdprmgrp_id', $prdprmgrp_id);

	} else if($action == 'VIEW_DETAIL') {
		// Get table Sales data
		$tableRecord = new TableSpa($tableName, $code);
		$values      = array();
		foreach($tableInfo['fieldNameList'] as $field => $value) {
			$values[$field] = $tableRecord->getFieldValue($field);
		}
		// Date thai format
		$values['sale_date_th']  	= dateThaiFormat($values['sale_date']);
		$smarty->assign('values', $values);
		
		// Get detail of Sales
		$sum_saledtl_amount = 0;
		$sum_freeAmount 	= 0;
		$sum_discout		= 0;
		$sum_saledtl_price 	= 0;
		$saleDetailList 	= array();
		$saledtlIdList 		= array();
		$sql 	= "	SELECT 		s.saledtl_id,
								p.prd_id,
								p.prd_name,
								p.prd_price,
								s.saledtl_amount,
								s.saledtl_price 
					FROM 		sale_details s, 
								products p 
					WHERE 		s.prd_id = p.prd_id AND 
								s.sale_id = '$code' 
					ORDER BY 	s.saledtl_id";
		$result = mysql_query($sql, $dbConn);
		$rows 	= mysql_num_rows($result);
		for($i=0; $i<$rows; $i++) {
			$record = mysql_fetch_assoc($result);
			$saleDetailList[$record['saledtl_id']] = array(
				'no' 				=> $i+1,
				'saledtl_id' 		=> $record['saledtl_id'],
				'prd_id' 			=> $record['prd_id'],
				'prd_name' 			=> $record['prd_name'],
				'prd_price' 		=> $record['prd_price'],
				'saledtl_amount' 	=> $record['saledtl_amount'],
				'saledtl_price' 	=> $record['saledtl_price'],
				'freeAmount'		=> 0,
				'discout' 			=> 0
			);
			array_push($saledtlIdList, $record['saledtl_id']);
		}

		// Get detail promotion of Sales
		$saledtlIdList 	 = wrapSingleQuote($saledtlIdList);
		$sql 	= "	SELECT  		s.saledtl_id,
									s.saleprmdtl_amount,
									s.saleprmdtl_discout,
									prmprd.prmprd_discout_type 
					FROM 			sale_promotion_details s,
									promotion_products prmprd 
					WHERE 			s.prmprd_id = prmprd.prmprd_id AND 
									s.saledtl_id IN (".implode(',', $saledtlIdList).")";
									echo $sql;
		$result = mysql_query($sql, $dbConn);
		$rows 	= mysql_num_rows($result);
		if($rows > 0) {
			for($i=0; $i<$rows; $i++) {
				$record = mysql_fetch_assoc($result);
				$saleDetailList[$record['saledtl_id']]['discout'] += $record['saleprmdtl_discout'];
				$saleDetailList[$record['saledtl_id']]['saledtl_price'] -= $record['saleprmdtl_discout'];
				if($record['prmprd_discout_type'] == '') {
					$saleDetailList[$record['saledtl_id']]['freeAmount'] += $record['saleprmdtl_amount'];
				}
			}
		}
		// cal sum of tfoot
		foreach ($saleDetailList as $key => $list) {
			$sum_saledtl_amount += $list['saledtl_amount'];
			$sum_freeAmount 	+= $list['freeAmount'];
			$sum_discout		+= $list['discout'];
			$sum_saledtl_price 	+= $list['saledtl_price'];
		}
		$smarty->assign('saleDetailList', $saleDetailList);
		$smarty->assign('sum_saledtl_amount', $sum_saledtl_amount);
		$smarty->assign('sum_freeAmount', $sum_freeAmount);
		$smarty->assign('sum_discout', $sum_discout);
		$smarty->assign('sum_saledtl_price', $sum_saledtl_price);
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

				case 'products':
					$sqlRefData = "	SELECT 		p.prd_id refValue,
												p.prd_name refText,
												p.prd_price,
												u.unit_name  
									FROM 		products p, units u 
									WHERE 		p.unit_id = u.unit_id  
									ORDER BY 	refText ASC";
					$refField 	= 'prd_id';
					break;

				case 'product_promotion_groups':
					$sqlRefData = "	SELECT 		prdprmgrp_id refValue,
												prdprmgrp_name refText 
									FROM 		product_promotion_groups 
									ORDER BY 	refText ASC";
					$refField 	= 'prdprmgrp_id';
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

	// Get products data
	$products = array();
	$sql = "SELECT 		p.prd_id,
						pt.prdtyp_id,
						pt.prdtyp_name,
						u.unit_name,
						b.brand_name,
						p.prd_name,
						p.prd_price,
						p.prd_amount,
						IFNULL(p.prd_pic, '') prd_pic,
						IFNULL(p.prd_desc, '') prd_desc,
						IFNULL(p.prd_barcode, '') prd_barcode 
			FROM 		products p, product_types pt, brands b, units u  
			WHERE 		p.brand_id = b.brand_id AND 
						p.prdtyp_id = pt.prdtyp_id AND 
						p.unit_id = u.unit_id 
			ORDER BY 	p.prd_name ASC";
	$result = mysql_query($sql, $dbConn);
	$rows 	= mysql_num_rows($result);
	if($rows > 0) {
		for($i=0; $i<$rows; $i++) {
			$record = mysql_fetch_assoc($result);
			$products[$record['prd_id']] = $record;
		}
		$smarty->assign('products', $products);
	}

	// Get promotion products data
	$promotion = array();
	$sql = "SELECT 		prdprm.prdprmgrp_id,
						prdprm.prdprm_name,
						prdprm.prdprm_picture,
						prmprd.prmprd_id,
						prmprd.prd_id,
						prmprd.prmprd_discout,
						prmprd.prmprd_discout_type 
			FROM 		promotion_products prmprd,
						product_promotions prdprm 
			WHERE 		prmprd.prdprm_id = prdprm.prdprm_id AND 
						prmprd.prmprd_startdate <= '$nowDate' AND 
						(
							prmprd.prmprd_enddate IS NULL OR
							prmprd.prmprd_enddate >= '$nowDate'
						)";
	$result = mysql_query($sql, $dbConn);
	$rows 	= mysql_num_rows($result);
	if($rows > 0) {
		for($i=0; $i<$rows; $i++) {
			$record			= mysql_fetch_assoc($result);
			$prdprmgrp_id 	= $record['prdprmgrp_id'];
			$prd_id 		= $record['prd_id'];

			if($record['prmprd_discout'] == '' && $record['prmprd_discout_type'] == '') {
				// Free promotion
				$promotion[$prdprmgrp_id][$prd_id]['free'] = array(
					'prmprd_id' 			=> $record['prmprd_id'],
					'prdprm_name' 			=> $record['prdprm_name'],
					'prdprm_picture' 		=> $record['prdprm_picture']
				);
			} else {
				// Sale promotion
				$promotion[$prdprmgrp_id][$prd_id]['sale'] = array(
					'prmprd_id' 			=> $record['prmprd_id'],
					'prdprm_name' 			=> $record['prdprm_name'],
					'prdprm_picture' 		=> $record['prdprm_picture'],
					'prmprd_discout' 		=> $record['prmprd_discout'],
					'prmprd_discout_type' 	=> $record['prmprd_discout_type']
				);
			}
		}
		$smarty->assign('promotion', $promotion);
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

		// Insert Sales
		$tableRecord = new TableSpa($tableName, $values['fieldName'], $values['fieldValue']);
		if(!$tableRecord->insertSuccess()) {
			$insertResult = false;
			$errTxt .= 'INSERT_SALES_FAIL\n';
			$errTxt .= mysql_error($dbConn).'\n\n';
		}

		
		// Insert Sales detail
		$sale_id = $tableRecord->getKey();
		foreach ($formData['prd_id'] as $key => $prd_id) {
			$saledtl_amount 	= $formData['prd_qty'][$key];
			$saledtl_price 		= $formData['saledtl_price'][$key];
			$saledtlValues 		= array($sale_id, $prd_id, $saledtl_amount, $saledtl_price);
			$saleDetailsRecord 	= new TableSpa('sale_details', $saledtlValues);
			if(!$saleDetailsRecord->insertSuccess()) {
				$insertResult = false;
				$errTxt .= 'INSERT_SALE_DETAILS['.($key+1).']_FAIL\n';
				$errTxt .= mysql_error($dbConn).'\n\n';
			}

			$saledtl_id = $saleDetailsRecord->getKey();

			// Insert sale promotion detail (Sale)
			if(hasValue($formData['prmSale_'.$prd_id.'_prmprd_id'])) {
				$prmprd_id 				= $formData['prmSale_'.$prd_id.'_prmprd_id'];
				$saleprmdtl_amount 		= $formData['prmSale_'.$prd_id.'_saleprmdtl_amount'];
				$saleprmdtl_discout 	= $formData['prmSale_'.$prd_id.'_saleprmdtl_discout'];
				$saleprmdtlValues 		= array($saledtl_id, $prmprd_id, $saleprmdtl_amount, $saleprmdtl_discout);
				$salePrmDetailsRecord 	= new TableSpa('sale_promotion_details', $saleprmdtlValues);
				if(!$saleDetailsRecord->insertSuccess()) {
					$insertResult = false;
					$errTxt .= 'INSERT_SALE_PROMOTION_DETAILS['.($key+1).']_FAIL\n';
					$errTxt .= mysql_error($dbConn).'\n\n';
				}
			}
			// Insert sale promotion detail (Free)
			if(hasValue($formData['prmFree_'.$prd_id.'_prmprd_id'])) {
				$prmprd_id 				= $formData['prmFree_'.$prd_id.'_prmprd_id'];
				$saleprmdtl_amount 		= $formData['prmFree_'.$prd_id.'_saleprmdtl_amount'];
				$saleprmdtl_discout 	= $formData['prmFree_'.$prd_id.'_saleprmdtl_discout'];
				$saleprmdtlValues 		= array($saledtl_id, $prmprd_id, $saleprmdtl_amount, $saleprmdtl_discout);
				$salePrmDetailsRecord 	= new TableSpa('sale_promotion_details', $saleprmdtlValues);
				if(!$saleDetailsRecord->insertSuccess()) {
					$insertResult = false;
					$errTxt .= 'INSERT_SALE_PROMOTION_DETAILS['.($key+1).']_FAIL\n';
					$errTxt .= mysql_error($dbConn).'\n\n';
				}
			}

			// Decrease product shelf amount
			$prdRecord 		= new TableSpa('products', $prd_id);
			$oldShelfAmount = $prdRecord->getFieldValue('prd_shelf_amount');
			if($oldShelfAmount != '') {
				$newShelfAmount = $oldShelfAmount - $saledtl_amount;
				$prdRecord->setFieldValue('prd_shelf_amount', $newShelfAmount);
				if(!$prdRecord->commit()) {
					$insertResult = false;
					$errTxt .= 'DECREASE_PRODUCT_SHELF_AMOUNT['.($key+1).']_FAIL\n';
					$errTxt .= mysql_error($dbConn).'\n\n';
				}
			}
		}
		// End for insert sale details


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

		// Update Sales
		if(!$tableRecord->commit()) {
			$updateResult = false;
			$errTxt .= 'EDIT_ORDERS_FAIL\n';
			$errTxt .= mysql_error($dbConn).'\n\n';
		}

		
		// Delete sale_details if delete old sale_details
		$oldSaleDetailList = array();
		$newSaleDetailList = array();
		// Find old sale_details
		$sql = "SELECT saledtl_id FROM sale_details WHERE sale_id = '$code'";
		$result = mysql_query($sql, $dbConn);
		$rows 	= mysql_num_rows($result);
		for($i=0; $i<$rows; $i++) {
			$oldSaledtlRecord = mysql_fetch_assoc($result);
			array_push($oldSaleDetailList, $oldSaledtlRecord['saledtl_id']);
		}
		// Find new order_detail
		foreach ($formData['saledtl_id'] as $key => $newSaledtl_id) {
			array_push($newSaleDetailList, $newSaledtl_id);
		}
		// Check for delete 
		foreach ($oldSaleDetailList as $key => $oldSaledtl_id) {
			if(!in_array($oldSaledtl_id, $newSaleDetailList)) {
				// Delete sale_details
				$saleDetailRecord 	= new TableSpa('sale_details', $oldSaledtl_id);
				$prd_id 			= $saleDetailRecord->getFieldValue('prd_id');
				$saledtl_amount 	= $saleDetailRecord->getFieldValue('saledtl_amount');
				if(!$saleDetailRecord->delete()) {
					$updateResult = false;
					$errTxt .= "DELETE_ORDERS_DETAIL[$oldSaledtl_id]_FAIL\n";
					$errTxt .= mysql_error($dbConn).'\n\n';
				}

				// Increase product shelf amount
				$prdRecord 		= new TableSpa('products', $prd_id);
				$oldShelfAmount = $prdRecord->getFieldValue('prd_shelf_amount');
				if($oldShelfAmount != '') {
					$newShelfAmount = $oldShelfAmount + $saledtl_amount;
					$prdRecord->setFieldValue('prd_shelf_amount', $newShelfAmount);
					if(!$prdRecord->commit()) {
						$insertResult = false;
						$errTxt .= 'INCREASE_PRODUCT_SHELF_AMOUNT['.($key+1).']_FAIL\n';
						$errTxt .= mysql_error($dbConn).'\n\n';
					}
				}
			}
		}

		
		// Update or Add sale_details
		foreach ($formData['prd_id'] as $key => $prd_id) {
			$saledtl_amount = $formData['prd_qty'][$key];
			$saledtl_price 	= $formData['saledtl_price'][$key];

			if(isset($formData['saledtl_id'][$key])) {
				// Update sale_details
				$saledtl_id = $formData['saledtl_id'][$key];
				$saleDetailRecord 	= new TableSpa('sale_details', $saledtl_id);
				$old_saledtl_amount = $saleDetailRecord->getFieldValue('saledtl_amount');
				$saleDetailRecord->setFieldValue('prd_id', $prd_id);
				$saleDetailRecord->setFieldValue('saledtl_amount', $saledtl_amount);
				$saleDetailRecord->setFieldValue('saledtl_price', $saledtl_price);
				if(!$saleDetailRecord->commit()) {
					$updateResult = false;
					$errTxt .= 'EDIT_ORDERS_DETAIL['.($key+1).']_FAIL\n';
					$errTxt .= mysql_error($dbConn).'\n\n';
				}

				// Update product shelf amount
				$prdRecord 		= new TableSpa('products', $prd_id);
				$oldShelfAmount = $prdRecord->getFieldValue('prd_shelf_amount');
				if($oldShelfAmount != '') {
					if($saledtl_amount > $old_saledtl_amount) {
						// Decrease product shelf amount
						$newShelfAmount = $saledtl_amount - $old_saledtl_amount;
						$prdRecord->setFieldValue('prd_shelf_amount', $newShelfAmount);
						if(!$prdRecord->commit()) {
							$insertResult = false;
							$errTxt .= 'DECREASE_PRODUCT_SHELF_AMOUNT['.($key+1).']_FAIL\n';
							$errTxt .= mysql_error($dbConn).'\n\n';
						}
					} else if($saledtl_amount < $old_saledtl_amount) {
						// Increase product shelf amount
						$newShelfAmount = $old_saledtl_amount - $saledtl_amount;
						$prdRecord->setFieldValue('prd_shelf_amount', $newShelfAmount);
						if(!$prdRecord->commit()) {
							$insertResult = false;
							$errTxt .= 'DECREASE_PRODUCT_SHELF_AMOUNT['.($key+1).']_FAIL\n';
							$errTxt .= mysql_error($dbConn).'\n\n';
						}
					}
				}
			} else {
				// Add new sale_details
				$saledtlValues 		= array($code, $prd_id, $saledtl_amount, $saledtl_price);
				$saleDetailRecord 	= new TableSpa('sale_details', $saledtlValues);
				if(!$saleDetailRecord->insertSuccess()) {
					$updateResult = false;
					$errTxt .= 'ADD_ORDERS_DETAIL['.($key+1).']_FAIL\n';
					$errTxt .= mysql_error($dbConn).'\n\n';
				}

				// Decrease product shelf amount
				$prdRecord 		= new TableSpa('products', $prd_id);
				$oldShelfAmount = $prdRecord->getFieldValue('prd_shelf_amount');
				if($oldShelfAmount != '') {
					$newShelfAmount = $oldShelfAmount - $saledtl_amount;
					$prdRecord->setFieldValue('prd_shelf_amount', $newShelfAmount);
					if(!$prdRecord->commit()) {
						$insertResult = false;
						$errTxt .= 'DECREASE_PRODUCT_SHELF_AMOUNT['.($key+1).']_FAIL\n';
						$errTxt .= mysql_error($dbConn).'\n\n';
					}
				}
			}
		}

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