<?php
session_start();
include('config/config.php');
$tplName = 'shop_promotion_service_lists.html';
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

//Get Service List Data
$svlData = array();
$svlIds  = array();
$sql = "SELECT 		svl_id,
					svl_price 
		FROM 		service_lists 
		WHERE 		svl_stop IS NULL OR 
					svl_stop >= '$nowDate'";
$result = mysql_query($sql, $dbConn);
$rows 	= mysql_num_rows($result);
if($rows > 0) {
	for($i=0; $i<$rows; $i++) {
		$record = mysql_fetch_assoc($result);
		$svlData[$record['svl_id']] = $record;
		array_push($svlIds, $record['svl_id']);
	}
}

// Find end page
if($rows > 1) {
	$endPage = $startPage + $rows;
	$smarty->assign('endPage', $endPage);
}

// Get service_list promotion detail data
$svlList = array();
$svlIds = wrapSingleQuote($svlIds);
$sql = "SELECT 		svlprmdtl.svl_id, 
					ss.svl_name,
					ss.svl_price,
					svlprm.svlprm_name,
					svlprm.svlprm_id,
					svlprm.svlprm_pic,
					svlprm.svlprm_desc, 
					svlprmdtl.svlprmdtl_startdate, 
					svlprmdtl.svlprmdtl_enddate,
					svlprmdtl.svlprmdtl_discout,
					svlprmdtl.svlprmdtl_discout_type 
		FROM 		service_list_promotion_details svlprmdtl,
					service_list_promotions svlprm, service_lists ss 
		WHERE 		svlprmdtl.svlprm_id = svlprm.svlprm_id AND 
					svlprmdtl.svlprmdtl_startdate <= '$nowDate' AND 
					(
						svlprmdtl.svlprmdtl_enddate IS NULL OR
						svlprmdtl.svlprmdtl_enddate >= '$nowDate'
					) AND 
					svlprmdtl.svl_id IN (".implode(',', $svlIds).")
		AND 		svlprmdtl.svl_id  = ss.svl_id 
		ORDER BY  	svlprm .svlprm_id DESC, svlprmdtl.svlprmdtl_id ";
$result = mysql_query($sql, $dbConn);
$rows 	= mysql_num_rows($result);
if($rows > 0) {
	for($i=0; $i<$rows; $i++) {
		$record = mysql_fetch_assoc($result);
		$svl_id = $record['svl_id'];
		$svlprm_id = $record['svlprm_id'];

		if(!isset($svlList[$svlprm_id])) {
			$svlList[$svlprm_id]['svlprm_name'] = $record['svlprm_name'];
			$svlList[$svlprm_id]['svlprm_pic'] = $record['svlprm_pic'];
			$svlList[$svlprm_id]['svlprm_desc'] = $record['svlprm_desc'];
			$svlList[$svlprm_id]['svlprmdtl'] = array();
		}

		$discoutText = $record['svlprmdtl_discout'];
		$discoutPrice = $record['svlprmdtl_discout'];

		if($record['svlprmdtl_discout_type'] == '%') {
			$discoutPrice = $svlData[$svl_id]['svl_price'] * $record['svlprmdtl_discout'] / 100;
			$discoutText .= '%';
		} else {
			$discoutText .= ' บาท';
		}

		$svlList[$svlprm_id]['svlprmdtl'][$svl_id]['discoutText'] = $discoutText;
		$svlList[$svlprm_id]['svlprmdtl'][$svl_id]['svl_prmPrice'] = $svlData[$svl_id]['svl_price'] - $discoutPrice;
		if($record['svlprmdtl_enddate'] != '') {
			$record['svlprmdtl_enddate'] = dateThaiFormatShort($record['svlprmdtl_enddate']);
		}
		$svlList[$svlprm_id]['svlprmdtl'][$svl_id]['svlprmdtl_startdate'] = dateThaiFormatShort($record['svlprmdtl_startdate']);
		$svlList[$svlprm_id]['svlprmdtl'][$svl_id]['svlprmdtl_enddate'] = $record['svlprmdtl_enddate'];
		$svlList[$svlprm_id]['svlprmdtl'][$svl_id]['svl_name'] = $record['svl_name'];
	}
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


