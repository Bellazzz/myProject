<?php
include('../config/config.php');
include('../common/common_header.php');

$code 				= '';
$formData 			= array();
$formPrmds 			= array();
$saleDiscout 		= 0;
$errTxt  			= '';

// Prepare variable
parse_str($_REQUEST['formData'], $formData);
parse_str($_REQUEST['formPrmds'], $formPrmds);
if(hasValue($_POST['code'])) {
	$code = $_POST['code'];
}
if(hasValue($_POST['saleDiscout'])) {
	$saleDiscout = $_POST['saleDiscout'];
}
foreach ($formPrmds['saleprmdsdtl_discout'] as $key => $discout) {
	$saleDiscout += (float)$discout;
}

// Check for action
if($code == '') {
	## Insert
	// Insert Sale

	// Prepare variable
	$insertResult 		= true;
	$sale_prm_discout 	= 0.0;
	foreach ($formData['sumDiscout'] as $key => $discout) {
		$sale_prm_discout += (float)$discout;
	}

	$saleValues = array(
		$formData['emp_id'],
		date('Y-m-d'),
		date('H:i:s'),
		$saleDiscout,
		$sale_prm_discout,
		$formData['total-price'],
		$formData['pay-price']
	);

	$salesRecord = new TableSpa('sales', $saleValues);
	if(!$salesRecord->insertSuccess()) {
		$insertResult = false;
		$errTxt .= 'INSERT_SALES_FAIL\n';
		$errTxt .= mysql_error($dbConn).'\n\n';
	}

	// Insert sale detail
	$sale_id 		= $salesRecord->getKey();
	foreach ($formData['prd_id'] as $key => $prd_id) {
		$saledtl_amount 	= $formData['qty'][$key];
		$saledtl_price 		= $formData['sumPrice'][$key];
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

	// Insert promotion discout sale
	foreach ($formPrmds['prmds_id'] as $key => $prmds_id) {
		$saleprmdsdtl_discout = $formPrmds['saleprmdsdtl_discout'][$key];
		if($saleprmdsdtl_discout > 0) {
			$prmds_id 			= $prmds_id == '' ? NULL : $prmds_id;
			$salePrmSaleValues 	= array($sale_id, $prmds_id, $saleprmdsdtl_discout);
			$salePrmSaleRecord 	= new TableSpa('sale_promotion_sale_details', $salePrmSaleValues);
			if(!$salePrmSaleRecord->insertSuccess()) {
				$insertResult = false;
				$errTxt .= 'INSERT_SALE_PROMOTION_SALE_DETAILS['.($key+1).']_FAIL\n';
				$errTxt .= mysql_error($dbConn).'\n\n';
			}
		}
	}

	if($insertResult) {
		$response['status'] = 'PASS';
		$response['sale_id'] = $sale_id;
		echo json_encode($response);
	} else {
		$response['status'] = 'FAIL';
		$response['errorTxt']	= $errTxt;
		echo json_encode($response);
	}


} else {
	## Update
}

?>