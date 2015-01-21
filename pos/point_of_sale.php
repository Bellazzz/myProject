<?php
session_start();
//require('../backoffice/check_session.php');

include('../config/config.php');
$tplName = 'point_of_sale.html';
$subDir	 = WEB_ROOTDIR.'/pos/';

include('../common/common_header.php');

// check shop
if(!hasValue($_POST['shop_id'])) {
	redirect('select_shops.php');
}
$shop_id 	= $_POST['shop_id'];
$prdIdList 	= array();

// Get products data
$productList = array();
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
		FROM 		products p, product_types pt, brands b, units u, shop_display_products s 
		WHERE 		p.brand_id = b.brand_id AND 
					p.prdtyp_id = pt.prdtyp_id AND 
					p.unit_id = u.unit_id AND 
					p.prd_id = s.prd_id AND 
					s.shop_id = '$shop_id' 
		ORDER BY 	p.prd_name ASC";
$result = mysql_query($sql, $dbConn);
$rows 	= mysql_num_rows($result);
if($rows > 0) {
	for($i=0; $i<$rows; $i++) {
		$record = mysql_fetch_assoc($result);
		$productList[$record['prd_id']] = $record;
		array_push($prdIdList, "'".$record['prd_id']."'");
	}
}

// Get product sale frequency
$retroactDate = date('Y-m-d', strtotime('-1 months'));
$sql = "SELECT 		sd.prd_id,
					SUM(sd.saledtl_amount) saleFrequency 
		FROM 		sale_details sd, 
					sales s 
		WHERE 		s.sale_id = sd.sale_id AND 
					sd.prd_id IN (". implode(',', $prdIdList).") AND 
					s.sale_date >= '$retroactDate' 
		GROUP BY 	sd.prd_id";
$result = mysql_query($sql, $dbConn);
$rows 	= mysql_num_rows($result);				
if($rows > 0) {
	for($i=0; $i<$rows; $i++) {
		$record = mysql_fetch_assoc($result);
		$productList[$record['prd_id']]['frequency'] = $record['saleFrequency'];
	}
}

// Product list order by frequency
function cmp_by_optionNumber($a, $b) {
  return $b["frequency"] - $a["frequency"];
}
$productListFrequency = $productList;
usort($productListFrequency, "cmp_by_optionNumber");

// Get product promotion group data
$productPrmGrpList = array();
$sql = "SELECT DISTINCT 	prdprmgrp_id,
							prdprmgrp_name 
		FROM 				product_promotion_groups";
$result = mysql_query($sql, $dbConn);
$rows 	= mysql_num_rows($result);
if($rows > 0) {
	for($i=0; $i<$rows; $i++) {
		$record = mysql_fetch_assoc($result);
		$productPrmGrpList[$record['prdprmgrp_id']] = array(
			'id'		=> $record['prdprmgrp_id'],
			'name'		=> $record['prdprmgrp_name']
		);
	}
}

// Get products types data
$productTypeList = array();
$sql = "SELECT DISTINCT 	pt.prdtyp_id,
							pt.prdtyp_name 
		FROM 				product_types pt, products p 
		WHERE 				pt.prdtyp_id = p.prdtyp_id AND 
							p.prd_id IN (". implode(',', $prdIdList).") 
		ORDER BY 			pt.prdtyp_name ASC";
$result = mysql_query($sql, $dbConn);
$rows 	= mysql_num_rows($result);
if($rows > 0) {
	for($i=0; $i<$rows; $i++) {
		array_push($productTypeList, mysql_fetch_assoc($result));
	}
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
					) AND 
					prmprd.prd_id IN (". implode(',', $prdIdList).") ";
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
}

// Get promotion discout sale data
$promotionSale 			= array();
$promotionSaleGroup 	= array();
$sql = "SELECT 		prmds.prmds_id,
					prmds.prmds_name,
					prmds.prmds_purchase,
					prmds.prmds_discout,
					prmds.prmds_discout_type,
					prmds.prdprmgrp_id,
					prmdsdtl.prdtyp_id 
		FROM 		promotion_discout_sales prmds,
					promotion_discout_sale_details prmdsdtl 
		WHERE 		prmds.prmds_id = prmdsdtl.prmds_id AND 
					prmds.prmds_startdate <= '$nowDate' AND 
					(
						prmds.prmds_enddate IS NULL OR 
						prmds.prmds_enddate >= '$nowDate'
					)";
$result = mysql_query($sql, $dbConn);
$rows 	= mysql_num_rows($result);
if($rows > 0) {
	for($i=0; $i<$rows; $i++) {
		$record			= mysql_fetch_assoc($result);
		$prdtyp_id 		= $record['prdtyp_id'];

		$promotionSale[$record['prmds_id']] = array(
			'prmds_name' 			=> $record['prmds_name'],
			'prmds_purchase' 		=> $record['prmds_purchase'],
			'prmds_discout' 		=> $record['prmds_discout'],
			'prmds_discout_type' 	=> $record['prmds_discout_type']
		);
		$promotionSaleGroup[$record['prdprmgrp_id']][$prdtyp_id] = $record['prmds_id'];
	}
}

$smarty->assign('productList', $productList);
$smarty->assign('productListFrequency', $productListFrequency);
$smarty->assign('productPrmGrpList', $productPrmGrpList);
$smarty->assign('productTypeList', $productTypeList);
$smarty->assign('promotion', $promotion);
$smarty->assign('promotionSale', $promotionSale);
$smarty->assign('promotionSaleGroup', $promotionSaleGroup);

include('../common/common_footer.php');
?>