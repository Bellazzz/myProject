<?php
session_start();
require('check_session.php');
include('../config/config.php');
$tplName = 'report_top_product.html';
$subDir	 = WEB_ROOTDIR.'/backoffice/';

include('../common/common_header.php');

$startDate = '';
$endDate = '';
$prdtypSelected = '';

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
	if(isset($_POST['prdtypSelected'])) {
		$prdtypSelected = $_POST['prdtypSelected'];
		$prdtypSelected = wrapSingleQuote($prdtypSelected);
	}

	// Query Report
	$report = array();
	$sql = "SELECT * , sumPrice - sumDiscout AS sumRealPrice
			FROM (

				SELECT pt.prdtyp_name,pt.prdtyp_id, p.prd_name, p.prd_price, SUM( a.saledtl_amount ) AS sumAmount, SUM( a.saledtl_price ) AS sumPrice, SUM( a.discout ) AS sumDiscout, a.sale_date 
				FROM (

					SELECT sd.saledtl_id, sd.prd_id, sd.saledtl_amount, sd.saledtl_price, IFNULL( sp.saleprmdtl_discout, 0 ) discout, s.sale_date 
					FROM sale_details sd 
					JOIN sales s ON sd.sale_id = s.sale_id 
					LEFT JOIN sale_promotion_details sp ON sd.saledtl_id = sp.saledtl_id
					)a, products p, product_types pt, sales s
			WHERE a.prd_id = p.prd_id AND 
			p.prdtyp_id = pt.prdtyp_id AND 
			pt.prdtyp_id in (".implode(',', $prdtypSelected).") AND 
			a.sale_date >= '$startDate' AND 
			a.sale_date <= '$endDate' 
			GROUP BY p.prd_id 
			ORDER BY pt.prdtyp_name, p.prd_name )b";
	$result = mysql_query($sql, $dbConn);
	$rows   = mysql_num_rows($result);
	if($rows > 0) {
		$totalAmount = 0;
		$totalPrice  = 0;
		$totalDiscout = 0;
		$totalRealPrice = 0;
		$countPrdtyp = array();

		for($i=0; $i<$rows; $i++) {
			$record = mysql_fetch_assoc($result);

			array_push($report, array(
				'no' 		=> number_format($i+1),
				'prdtyp_name' 	=> $record['prdtyp_name'],
				'prdtyp_id'	=> $record['prdtyp_id'],
				'prd_name'	=> $record['prd_name'],
				'prd_price'	=> number_format($record['prd_price'], 2),
				'sumAmount'	=> number_format($record['sumAmount']),
				'sumPrice'	=> number_format($record['sumPrice'], 2),
				'sumDiscout'	=> number_format($record['sumDiscout'], 2),
				'sumRealPrice'	=> number_format($record['sumRealPrice'], 2)
			));

			if(!isset($countPrdtyp[$record['prdtyp_id']])) {
				$countPrdtyp[$record['prdtyp_id']] = 0;
			}
			$countPrdtyp[$record['prdtyp_id']]++;
			
			$totalAmount += $record['sumAmount'];
			$totalPrice += $record['sumPrice'];
			$totalDiscout += $record['sumDiscout'];
			$totalRealPrice += $record['sumRealPrice'];
		}

		$curPrdTypName = '';
		foreach ($report as $key => $value) {
			if($curPrdTypName != $value['prdtyp_name']) {
				$report[$key]['rowspan'] = $countPrdtyp[$value['prdtyp_id']];
				$curPrdTypName = $value['prdtyp_name'];
			}
		}
			
		$smarty->assign('report', $report);
		$smarty->assign('totalAmount', number_format($totalAmount));
		$smarty->assign('totalPrice', number_format($totalPrice, 2));
		$smarty->assign('totalDiscout', number_format($totalDiscout, 2));
		$smarty->assign('totalRealPrice', number_format($totalRealPrice, 2));
	}
}

// Query Product Type
$prdtyps = array();
$sql = "SELECT prdtyp_id, prdtyp_name FROM product_types";
$result = mysql_query($sql, $dbConn);
$rows   = mysql_num_rows($result);

if($rows > 0) {
	for($i=0; $i<$rows; $i++) {
		$record = mysql_fetch_assoc($result);
		$prdtyps[$record['prdtyp_id']]['prdtyp_id'] 	= $record['prdtyp_id'];
		$prdtyps[$record['prdtyp_id']]['prdtyp_name'] 	= $record['prdtyp_name'];
	}
	// product Checked
	if(isset($_POST['prdtypSelected'])) {
		foreach ($_POST['prdtypSelected'] as $key => $prdtyp_id) {
			$prdtyps[$prdtyp_id]['checked'] = true;
		}
	}
	$smarty->assign('prdtyps', $prdtyps);
}

include('../common/common_footer.php');
?>