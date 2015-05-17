<?php
session_start();
include('config/config.php');
$tplName = 'shop_promotion_packages.html';
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
	$sortCol = 'pkg_name';
	$sortBy  = 'ASC';
} else if($sort == 'NAME_DESC') {
	$sortCol = 'pkg_name';
	$sortBy  = 'DESC';
} else if($sort == 'PRICE_ASC') {
	$sortCol = 'pkg_price';
	$sortBy  = 'ASC';
} else if($sort == 'PRICE_DESC') {
	$sortCol = 'pkg_price';
	$sortBy  = 'DESC';
}

// Find all record
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
	$prevPageLink = "shop_promotions.php?sort=$sort&page=$prevPage&itemDisplay=$itemDisplay";
}
if($page >= $allPage) {
	$nextPageLink = '#fakelink';
} else {
	$nextPage = $page+1;
	$nextPageLink = "shop_promotions.php?sort=$sort&page=$nextPage&itemDisplay=$itemDisplay";
}

// Get package Data
$pkgData = array();
$pkgIds  = array();
$sql = "SELECT 		pkg_id,
					pkg_price 
		FROM 		packages 
		WHERE 		pkg_stop IS NULL OR 
					pkg_stop >= '$nowDate'";
$result = mysql_query($sql, $dbConn);
$rows 	= mysql_num_rows($result);
if($rows > 0) {
	for($i=0; $i<$rows; $i++) {
		$record = mysql_fetch_assoc($result);
		$pkgData[$record['pkg_id']] = $record;
		array_push($pkgIds, $record['pkg_id']);
	}
}

// Get package sum time
$pkgIds = wrapSingleQuote($pkgIds);
$sql = "SELECT 		ps.pkg_id,
					SUM(s.svl_hr) AS pkg_hr,
					SUM(s.svl_min) AS pkg_min 
		FROM 		package_service_lists ps,
					service_lists s 
		WHERE 		ps.svl_id = s.svl_id AND 
					ps.pkg_id IN (".implode(',', $pkgIds).") 
		GROUP BY 	ps.pkg_id";
$result = mysql_query($sql, $dbConn);
$rows 	= mysql_num_rows($result);
if($rows > 0) {
	for($i=0; $i<$rows; $i++) {
		$record = mysql_fetch_assoc($result);
		$pkg_id = $record['pkg_id'];
		$sumHr  = $record['pkg_hr'];
		$sumMin = $record['pkg_min'];

		for($j=$sumMin; $j>=60; $j-=60) {
			$sumHr++;
			$sumMin-=60;
		}
		$pkgData[$pkg_id]['pkg_hr'] = $sumHr;
		$pkgData[$pkg_id]['pkg_min'] = $sumMin;
	}
}

// Find end page
if($rows > 1) {
	$endPage = $startPage + $rows;
	$smarty->assign('endPage', $endPage);
}

// Get package promotion detail data
$pkgList = array();
$sql = "SELECT 		pkgprmdtl.pkg_id,
					p.pkg_name,
					pkgprm.pkgprm_id,
					pkgprm.pkgprm_name,
					pkgprm.pkgprm_pic,
					pkgprm.pkgprm_desc,
					pkgprmdtl.pkgprmdtl_startdate,
					pkgprmdtl.pkgprmdtl_enddate,
					pkgprmdtl.pkgprmdtl_discout,
					pkgprmdtl.pkgprmdtl_discout_type,
					ct.custype_name  
		FROM 		package_promotion_details pkgprmdtl,
					package_promotions pkgprm, packages p,
					customer_types ct 
		WHERE 		pkgprmdtl.pkgprm_id = pkgprm.pkgprm_id AND 
					pkgprm.custype_id = ct.custype_id AND 
					pkgprmdtl.pkgprmdtl_startdate <= '$nowDate' AND 
					(
						pkgprmdtl.pkgprmdtl_enddate IS NULL OR
						pkgprmdtl.pkgprmdtl_enddate >= '$nowDate'
					) 
		AND 		pkgprmdtl.pkg_id IN (".implode(',', $pkgIds).") 
		AND 		pkgprmdtl.pkg_id = p.pkg_id 
		ORDER BY  	pkgprm.pkgprm_id DESC, pkgprmdtl_id";

$result = mysql_query($sql, $dbConn);
$rows 	= mysql_num_rows($result);
if($rows > 0) {
	for($i=0; $i<$rows; $i++) {
		$record = mysql_fetch_assoc($result);
		$pkg_id = $record['pkg_id'];
		$pkgprm_id = $record['pkgprm_id'];

		if(!isset($pkgList[$pkgprm_id])) {
			$pkgList[$pkgprm_id]['pkgprm_name'] = $record['pkgprm_name'];
			$pkgList[$pkgprm_id]['pkgprm_pic'] = $record['pkgprm_pic'];
			$pkgList[$pkgprm_id]['pkgprm_desc'] = $record['pkgprm_desc'];
			$pkgList[$pkgprm_id]['custype_name'] = $record['custype_name'];
			$pkgList[$pkgprm_id]['pkgprmdtl'] = array();
		}

		$discoutText = $record['pkgprmdtl_discout'];
		$discoutPrice = $record['pkgprmdtl_discout'];

		if($record['pkgprmdtl_discout_type'] == '%') {
			$discoutPrice = $pkgData[$pkg_id]['pkg_price'] * $record['pkgprmdtl_discout'] / 100;
			$discoutText .= '%';
		} else {
			$discoutText .= ' บาท';
		}

		$pkgList[$pkgprm_id]['pkgprmdtl'][$pkg_id]['discoutText'] = $discoutText;
		$pkgList[$pkgprm_id]['pkgprmdtl'][$pkg_id]['pkg_prmPrice'] = $pkgData[$pkg_id]['pkg_price'] - $discoutPrice;
		if($record['pkgprmdtl_enddate'] != '') {
			$record['pkgprmdtl_enddate'] = dateThaiFormatShort($record['pkgprmdtl_enddate']);
		}
		$pkgList[$pkgprm_id]['pkgprmdtl'][$pkg_id]['pkgprmdtl_startdate'] = dateThaiFormatShort($record['pkgprmdtl_startdate']);
		$pkgList[$pkgprm_id]['pkgprmdtl'][$pkg_id]['pkgprmdtl_enddate'] = $record['pkgprmdtl_enddate'];
		$pkgList[$pkgprm_id]['pkgprmdtl'][$pkg_id]['pkg_name'] = $record['pkg_name'];
	}
}

$smarty->assign('pkgList', $pkgList);
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


