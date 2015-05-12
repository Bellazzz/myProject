<?php
session_start();
require('check_session.php');
include('../config/config.php');
$tplName = 'report_income.html';
$subDir	 = WEB_ROOTDIR.'/backoffice/';

include('../common/common_header.php');

$startDate 		= '';
$endDate 		= '';

if(isset($_POST['submit'])) {
	// Get input data
	if(isset($_POST['startDate'])) {
		$startDate = $_POST['startDate'];
		$smarty->assign('startDate', $startDate);
		$smarty->assign('startDate_th', dateThaiFormat($startDate));
	}
	if(isset($_POST['endDate'])) {
		$endDate = $_POST['endDate'];
		$smarty->assign('endDate', $endDate);
		$smarty->assign('endDate_th', dateThaiFormat($endDate));
	}

	// Query Report
	// Find services
	$report = array();
	$serTotalPrice = 0;
	$saleTotalPrice = 0;
	$totalPrice = 0;
	$sql = "SELECT 		IFNULL(SUM(bp.bkgpkg_total_price), 0) pkgTotalPrice,
						IFNULL(SUM(bs.bkgsvl_total_price), 0) svlTotalPrice 
			FROM 		booking b 
						LEFT JOIN 
						booking_packages bp 
						ON b.bkg_id = bp.bkg_id 
						LEFT JOIN 
						booking_service_lists bs 
						ON b.bkg_id = bs.bkg_id 
			WHERE 		b.status_id NOT IN ('S01','S02','S06') AND 
						b.bkg_transfer_date IS NOT NULL AND 
						b.bkg_transfer_date >= '$startDate' AND 
						b.bkg_transfer_date <= '$endDate'";

	$result = mysql_query($sql, $dbConn);
	$rows   = mysql_num_rows($result);
	if($rows > 0) {
		$record = mysql_fetch_assoc($result);
		$report['pkgTotalPrice'] = number_format($record['pkgTotalPrice'], 2);
		$report['svlTotalPrice'] = number_format($record['svlTotalPrice'], 2);
		$serTotalPrice += $record['pkgTotalPrice'] + $record['svlTotalPrice'];
	}

	// Find sales
	$sql = "SELECT 		pt.prdtyp_group,
						IFNULL( SUM( sd.saledtl_price - IFNULL( sp.saleprmdtl_discout, 0 ) ) , 0 ) totalPrice 
			FROM 		sales s 
						JOIN 
						sale_details sd ON s.sale_id = sd.sale_id 
						JOIN 
						products p ON sd.prd_id = p.prd_id 
						JOIN 
						product_types pt ON p.prdtyp_id = pt.prdtyp_id 
						LEFT JOIN 
						sale_promotion_details sp ON sd.saledtl_id = sp.saledtl_id 
			WHERE 		s.sale_date >= '$startDate' AND 
						s.sale_date <= '$endDate' 
			GROUP BY 	pt.prdtyp_group 
			ORDER BY 	pt.prdtyp_group";

	$result = mysql_query($sql, $dbConn);
	$rows   = mysql_num_rows($result);
	if($rows > 0) {
		$report['prdtyps'] = array();

		for($i=0; $i<$rows; $i++) {
			$record = mysql_fetch_assoc($result);
			array_push($report['prdtyps'], array(
				'name' => $record['prdtyp_group'],
				'totalPrice' => number_format($record['totalPrice'], 2)
			));
			$saleTotalPrice += $record['totalPrice'];
		}
	}


	$totalPrice += $serTotalPrice + $saleTotalPrice;

	$smarty->assign('report', $report);
	$smarty->assign('serTotalPrice', number_format($serTotalPrice, 2));
	$smarty->assign('saleTotalPrice', number_format($saleTotalPrice, 2));
	$smarty->assign('totalPrice', number_format($totalPrice, 2));
}

$smarty->assign('tplName', $tplName);
include('../common/common_footer.php');
?>