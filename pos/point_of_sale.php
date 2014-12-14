<?php
session_start();
//require('check_session.php');

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
					prmprd.prmprd_id,
					prmprd.prd_id,
					prmprd.prmprd_discout,
					prmprd.prmprd_discout_type 
		FROM 		promotion_products prmprd,
					product_promotions prdprm 
		WHERE 		prmprd.prdprm_id = prdprm.prdprm_id AND 
					prmprd.prmprd_startdate <= '$nowDate' AND 
					prmprd.prmprd_enddate >= '$nowDate' AND 
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
			$promotion[$prdprmgrp_id][$prd_id]['free'] = $record['prmprd.prmprd_id'];
		} else {
			// Sale promotion
			$promotion[$prdprmgrp_id][$prd_id]['sale'] = array(
				'prmprd_id' 			=> $record['prmprd_id'],
				'prmprd_discout' 		=> $record['prmprd_discout'],
				'prmprd_discout_type' 	=> $record['prmprd_discout_type']
			);
		}
	}
}

$smarty->assign('productList', $productList);
$smarty->assign('productTypeList', $productTypeList);
$smarty->assign('promotion', $promotion);

include('../common/common_footer.php');
?>