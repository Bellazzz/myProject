<?php
session_start();
//require('check_session.php');
include('config/config.php');
$tplName = 'shop_packages.html';
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
$sql = "SELECT COUNT(*) AS allRecord FROM packages";
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
	$prevPageLink = "shop_packages.php?sort=$sort&page=$prevPage&itemDisplay=$itemDisplay";
}
if($page >= $allPage) {
	$nextPageLink = '#fakelink';
} else {
	$nextPage = $page+1;
	$nextPageLink = "shop_packages.php?sort=$sort&page=$nextPage&itemDisplay=$itemDisplay";
}

$pkgList = array();
$pkgIds  = array();
$sql = "SELECT 		pkg_id,
					pkg_name,
					pkg_start,
					pkg_stop,
					pkg_price,
					pkg_desc,
					pkg_picture 
		FROM 		packages 
		$order";
$result = mysql_query($sql, $dbConn);
$rows 	= mysql_num_rows($result);
if($rows > 0) {
	for($i=0; $i<$rows; $i++) {
		$record = mysql_fetch_assoc($result);
		$pkgList[$record['pkg_id']] = $record;
		array_push($pkgIds, $record['pkg_id']);
	}
}

// Find end page
if($rows > 1) {
	$endPage = $startPage + $rows;
	$smarty->assign('endPage', $endPage);
}

// Get package promotion detail data
$pkgIds = wrapSingleQuote($pkgIds);
$sql = "SELECT 		pkgprmdtl.pkg_id,
					pkgprmdtl.pkgprmdtl_discout,
					pkgprmdtl.pkgprmdtl_discout_type 
		FROM 		package_promotion_details pkgprmdtl,
					package_promotions pkgprm 
		WHERE 		pkgprmdtl.pkgprm_id = pkgprm.pkgprm_id AND 
					pkgprmdtl.pkgprmdtl_startdate <= '$nowDate' AND 
					(
						pkgprmdtl.pkgprmdtl_enddate IS NULL OR
						pkgprmdtl.pkgprmdtl_enddate >= '$nowDate'
					) AND 
					pkgprmdtl.pkg_id IN (".implode(',', $pkgIds).")";
$result = mysql_query($sql, $dbConn);
$rows 	= mysql_num_rows($result);
if($rows > 0) {
	for($i=0; $i<$rows; $i++) {
		$record = mysql_fetch_assoc($result);
		$pkg_id = $record['pkg_id'];
		$discoutText = $record['pkgprmdtl_discout'];
		$discoutPrice = $record['pkgprmdtl_discout'];

		if($record['pkgprmdtl_discout_type'] == '%') {
			$discoutPrice = $pkgList[$pkg_id]['pkg_price'] * $record['pkgprmdtl_discout'] / 100;
			$discoutText .= '%';
		} else {
			$discoutText .= ' บาท';
		}
		$pkgList[$pkg_id]['discoutText'] = $discoutText;
		$pkgList[$pkg_id]['pkg_prmPrice'] = $pkgList[$pkg_id]['pkg_price'] - $discoutPrice;
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


