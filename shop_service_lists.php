<?php
session_start();
//require('check_session.php');
include('config/config.php');
$tplName = 'shop_service_lists.html';
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
	$sortCol = 'svl_name';
	$sortBy  = 'ASC';
} else if($sort == 'NAME_DESC') {
	$sortCol = 'svl_name';
	$sortBy  = 'DESC';
} else if($sort == 'PRICE_ASC') {
	$sortCol = 'svl_price';
	$sortBy  = 'ASC';
} else if($sort == 'PRICE_DESC') {
	$sortCol = 'svl_price';
	$sortBy  = 'DESC';
}

// Find all record
$sql = "SELECT COUNT(*) AS allRecord FROM service_lists";
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
	$prevPageLink = "shop_service_lists.php?sort=$sort&page=$prevPage&itemDisplay=$itemDisplay";
}
if($page >= $allPage) {
	$nextPageLink = '#fakelink';
} else {
	$nextPage = $page+1;
	$nextPageLink = "shop_service_lists.php?sort=$sort&page=$nextPage&itemDisplay=$itemDisplay";
}

$svlList = array();
$sql = "SELECT 		svl_id,
					svl_name,
					svl_min,
					svl_hr,
					svl_price,
					svl_desc,
					svl_picture 
		FROM 		service_lists 
		$order";
$result = mysql_query($sql, $dbConn);
$rows 	= mysql_num_rows($result);
if($rows > 0) {
	for($i=0; $i<$rows; $i++) {
		$record = mysql_fetch_assoc($result);
		$svlList[$record['svl_id']] = $record;
	}
}

// Get service_list promotion detail data
$sql = "SELECT 		svlprmdtl.svl_id,
					svlprmdtl.svlprmdtl_discout,
					svlprmdtl.svlprmdtl_discout_type 
		FROM 		service_list_promotion_details svlprmdtl,
					service_list_promotions svlprm 
		WHERE 		svlprmdtl.svlprm_id = svlprm.svlprm_id AND 
					svlprmdtl.svlprmdtl_startdate <= '$nowDate' AND 
					(
						svlprmdtl.svlprmdtl_enddate IS NULL OR
						svlprmdtl.svlprmdtl_enddate >= '$nowDate'
					) AND 
					svlprm.custype_id = 'CT3'";
$result = mysql_query($sql, $dbConn);
$rows 	= mysql_num_rows($result);
if($rows > 0) {
	for($i=0; $i<$rows; $i++) {
		$record = mysql_fetch_assoc($result);
		$svl_id = $record['svl_id'];
		$discoutText = $record['svlprmdtl_discout'];
		$discoutPrice = $record['svlprmdtl_discout'];

		if($record['svlprmdtl_discout_type'] == '%') {
			$discoutPrice = $svlList[$svl_id]['svl_price'] * $record['svlprmdtl_discout'] / 100;
			$discoutText .= '%';
		} else {
			$discoutText .= ' บาท';
		}
		$svlList[$svl_id]['discoutText'] = $discoutText;
		$svlList[$svl_id]['svl_prmPrice'] = $svlList[$svl_id]['svl_price'] - $discoutPrice;
	}
}

// Find end page
if($rows > 1) {
	$endPage = $startPage + $rows;
	$smarty->assign('endPage', $endPage);
}

$smarty->assign('svlList', $svlList);
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


