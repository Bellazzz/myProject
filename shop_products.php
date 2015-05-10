<?php
session_start();
include('config/config.php');
$tplName = 'shop_products.html';
$subDir	 = WEB_ROOTDIR.'/';

include('common/common_header.php');

$sort 		 = 'NAME_ASC';
$itemDisplay = 12;
$page 		 = 1;
$where 		 = '';

if(hasValue($_GET['sort'])) {
	$sort = $_GET['sort'];
}
if(hasValue($_GET['itemDisplay'])) {
	$itemDisplay = (Int)$_GET['itemDisplay'];
}
if(hasValue($_GET['page'])) {
	$page = (Int)$_GET['page'];
}

if($sort == 'POPULAR') {

} else if($sort == 'NAME_ASC') {
	$sortCol = 'prd_name';
	$sortBy  = 'ASC';
} else if($sort == 'NAME_DESC') {
	$sortCol = 'prd_name';
	$sortBy  = 'DESC';
} else if($sort == 'PRICE_ASC') {
	$sortCol = 'prd_price';
	$sortBy  = 'ASC';
} else if($sort == 'PRICE_DESC') {
	$sortCol = 'prd_price';
	$sortBy  = 'DESC';
}

// Find all record
$sql = "SELECT COUNT( * ) AS allRecord
		FROM products
		WHERE prdtyp_id
		IN('PT12',  'PT02',  'PT03',  'PT04',  'PT05')";

$result = mysql_query($sql, $dbConn);
$record = mysql_fetch_assoc($result);
$allRecord = $record['allRecord'];

// Generate order and limit
$startPage 		= ($page - 1) * $itemDisplay;
$order	 		= "ORDER BY $sortCol $sortBy";
if($itemDisplay <= $allRecord) {
	$order .= " LIMIT $startPage, $itemDisplay";
} else {
	$startPage = 0;
}

// Calculate page
$allPage = ceil($allRecord/$itemDisplay);
if($page <= 1) {
	$prevPageLink = '#fakelink';
} else {
	$prevPage = $page-1;
	$prevPageLink = "shop_products.php?sort=$sort&page=$prevPage&itemDisplay=$itemDisplay";
}
if($page >= $allPage) {
	$nextPageLink = '#fakelink';
} else {
	$nextPage = $page+1;
	$nextPageLink = "shop_products.php?sort=$sort&page=$nextPage&itemDisplay=$itemDisplay";
}

$prdList = array();
$prdIds  = array();
$sql = "SELECT 	p.prd_id, 
				t.prdtyp_name, 
				u.unit_name, 
				p.prd_name, 
				p.prd_price, 
				p.prd_pic,
				p.prd_desc 
		FROM 	products p, units u, product_types t 
		WHERE 	p.unit_id = u.unit_id 
		AND 	p.prdtyp_id = t.prdtyp_id 
		AND  	p.prdtyp_id
		IN 		('PT12',  'PT02',  'PT03',  'PT04',  'PT05') 
		$order";
$result = mysql_query($sql, $dbConn);
$rows 	= mysql_num_rows($result);
if($rows > 0) {
	for($i=0; $i<$rows; $i++) {
		$record = mysql_fetch_assoc($result);
		$prdList[$record['prd_id']] = $record;
		array_push($prdIds, $record['prd_id']);
	}
}

// Find end page
if($rows > 1) {
	$endPage = $startPage + $rows;
	$smarty->assign('endPage', $endPage);
}

// Get products promotion detail data
$prdIds = wrapSingleQuote($prdIds);
$sql = "SELECT  pm.prmprd_id, 
				pm.prd_id, 
				pm.prmprd_discout, 
				pm.prmprd_discout_type,
				pm.prmprd_startdate,
				pm.prmprd_enddate  
		FROM 	promotion_products pm, product_promotions pp 
		WHERE 	pm.prdprm_id = pp.prdprm_id 
		AND 	pm.prmprd_startdate <= '$nowDate' 
		AND (
	    		pm.prmprd_enddate  IS NULL OR
	    		pm.prmprd_enddate  >= '$nowDate' 
			) 
 		AND 	pm.prd_id IN (".implode(',', $prdIds).")";
$result = mysql_query($sql, $dbConn);
$rows 	= mysql_num_rows($result);
if($rows > 0) {
	for($i=0; $i<$rows; $i++) {
		$record = mysql_fetch_assoc($result);
		$prd_id = $record['prd_id'];
		$discoutText = $record['prmprd_discout'];
		$discoutPrice = $record['prmprd_discout'];

		if($record['prmprd_discout_type'] == '%') {
			$discoutPrice = $prdList[$prd_id]['prd_price'] * $record['prmprd_discout'] / 100;
			$discoutText .= '%';
		} else {
			$discoutText .= ' บาท';
		}

		$prdList[$prd_id]['prmprd_startdate'] = dateThaiFormat($record['prmprd_startdate']);
		if($record['prmprd_enddate'] != null) {
			$prdList[$prd_id]['prmprd_enddate'] = dateThaiFormat($record['prmprd_enddate']);
		}

		$prdList[$prd_id]['discoutText'] = $discoutText;
		$prdList[$prd_id]['prd_prmPrice'] = $prdList[$prd_id]['prd_price'] - $discoutPrice;
	}
}

$smarty->assign('prdList', $prdList);
$smarty->assign('sort', $sort);
$smarty->assign('allPage', $allPage);
$smarty->assign('page', $page);
$smarty->assign('prevPageLink', $prevPageLink);
$smarty->assign('nextPageLink', $nextPageLink);
$smarty->assign('itemDisplay', $itemDisplay);
$smarty->assign('allRecord', number_format($allRecord));
$smarty->assign('startPage', $startPage+1);

$smarty->assign('tplName', $tplName);
include('common/common_footer.php');
?>


