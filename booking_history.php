<?php
session_start();
require('check_session.php');
include('config/config.php');
$tplName = 'booking_history.html';
$subDir	 = WEB_ROOTDIR.'/';

include('common/common_header.php');

$sort 		 = 'DATE_DESC';
$itemDisplay = 10;
$page 		 = 1;

if(hasValue($_GET['sort'])) {
	$sort = $_GET['sort'];
}
if(hasValue($_GET['itemDisplay'])) {
	$itemDisplay = (Int)$_GET['itemDisplay'];
}
if(hasValue($_GET['page'])) {
	$page = (Int)$_GET['page'];
}

if($sort == 'ID_ASC') {
	$sortBy = 'b.bkg_id ASC';
} else if($sort == 'ID_DESC') {
	$sortBy = 'b.bkg_id DESC';
} else if($sort == 'PRICE_ASC') {
	$sortBy = 'b.bkg_total_price ASC';
} else if($sort == 'PRICE_DESC') {
	$sortBy  = 'b.bkg_total_price DESC';
} else if($sort == 'DATE_ASC') {
	$sortBy  = 'b.bkg_date ASC, b.bkg_time ASC';
} else if($sort == 'DATE_DESC') {
	$sortBy  = 'b.bkg_date DESC, b.bkg_time DESC';
} else if($sort == 'STATUS_ASC') {
	$sortBy  = 'b.status_id ASC';
} else if($sort == 'STATUS_DESC') {
	$sortBy  = 'b.status_id DESC';
}

// Find all record
$sql = "SELECT 	COUNT(*) AS allRecord FROM booking 
		WHERE 	cus_id = '$cus_id'";
$result = mysql_query($sql, $dbConn);
$record = mysql_fetch_assoc($result);
$allRecord = $record['allRecord'];

// Generate order and limit
$startPage 		= ($page - 1) * $itemDisplay;
$order	 		= "ORDER BY $sortBy";
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
	$prevPageLink = "booking_history.php?sort=$sort&page=$prevPage&itemDisplay=$itemDisplay";
}
if($page >= $allPage) {
	$nextPageLink = '#fakelink';
} else {
	$nextPage = $page+1;
	$nextPageLink = "booking_history.php?sort=$sort&page=$nextPage&itemDisplay=$itemDisplay";
}

// Find history list
$cus_id = $_SESSION['cus_id'];
$bkgList = array();
$sql = "SELECT 		b.bkg_id,
					b.bkg_date,
					DATE_FORMAT(b.bkg_time,'%H:%i') bkg_time,
					b.bkg_total_price,
					b.status_id,
					bs.bkgstat_name 
		FROM 		booking b,
					booking_status bs 
		WHERE 		b.status_id = bs.bkgstat_id AND 
					b.cus_id = '$cus_id' 
		$order";
$result = mysql_query($sql, $dbConn);
$rows 	= mysql_num_rows($result);
if($rows > 0) {
	for($i=0; $i<$rows; $i++) {
		$record = mysql_fetch_assoc($result);
		array_push($bkgList, $record);
		$bkgList[$i]['bkg_date_th'] = dateThaiFormat($record['bkg_date']);
	}
	$smarty->assign('bkgList', $bkgList);
}

// Find end page
if($rows > 1) {
	$endPage = $startPage + $rows;
	$smarty->assign('endPage', $endPage);
}

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

