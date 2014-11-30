<?php
session_start();
//require('check_session.php');
include('../config/config.php');
$tplName = 'point_of_sale.html';
$subDir	 = WEB_ROOTDIR.'/pos/';

include('../common/common_header.php');

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
					p.prd_pic,
					IFNULL(p.prd_desc, '') prd_desc 
		FROM 		products p, product_types pt, brands b, units u 
		WHERE 		p.brand_id = b.brand_id AND 
					p.prdtyp_id = pt.prdtyp_id AND 
					p.unit_id = u.unit_id 
		ORDER BY 	p.prd_name ASC";
$result = mysql_query($sql, $dbConn);
$rows 	= mysql_num_rows($result);
if($rows > 0) {
	for($i=0; $i<$rows; $i++) {
		array_push($productList, mysql_fetch_assoc($result));
	}
}

// Get products types data
$productTypeList = array();
$sql = "SELECT 		prdtyp_id,
					prdtyp_name 
		FROM 		product_types 
		ORDER BY 	prdtyp_name ASC";
$result = mysql_query($sql, $dbConn);
$rows 	= mysql_num_rows($result);
if($rows > 0) {
	for($i=0; $i<$rows; $i++) {
		array_push($productTypeList, mysql_fetch_assoc($result));
	}
}

$smarty->assign('productList', $productList);
$smarty->assign('productTypeList', $productTypeList);

include('../common/common_footer.php');
?>