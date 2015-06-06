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
	$report = array();
	$serTotalPrice = 0;
	$saleTotalPrice = 0;
	$totalPrice = 0;
	
	// Find service lists
	$sql = "SELECT a.sumPrice - IFNULL(b.discout,0) AS sumTotal
		FROM 
			( SELECT sl.svl_id, st.svltyp_id, SUM( ss.sersvl_total_price ) AS sumPrice
			FROM service_service_lists ss, service_lists sl, service_list_types st, 
			     services s 
			WHERE sl.svl_id = ss.svl_id AND 
			 	  sl.svltyp_id = st.svltyp_id AND 
 				  ss.ser_id = s.ser_id AND 
				  s.ser_date >= '$startDate' AND 
				  s.ser_date <= '$endDate' AND 
				  (
				  		sl.svl_hr  != 0 || 
				  		sl.svl_min != 0
				  ) 
			GROUP BY sl.svl_id ) a 
			LEFT JOIN 
			(SELECT sd.svl_id, SUM( sp.sersvlprm_discout_total ) AS discout
			FROM service_service_list_promotions sp, service_list_promotion_details sd
			WHERE sp.svlprmdtl_id = sd.svlprmdtl_id
			GROUP BY sd.svl_id ) b 
			ON a.svl_id = b.svl_id";

	$result = mysql_query($sql, $dbConn);
	$rows   = mysql_num_rows($result);
	if($rows > 0) {
		$report['svlTotalPrice'] = 0;
		for($i=0; $i<$rows; $i++) {
			$record = mysql_fetch_assoc($result);
			$report['svlTotalPrice'] += $record['sumTotal'];
			$serTotalPrice += $record['sumTotal'];
			// $report['pkgTotalPrice'] = number_format($record['pkgTotalPrice'], 2);
		}
		$report['svlTotalPrice'] = number_format($report['svlTotalPrice'], 2);
	}

	// Find packages
	$sql = "SELECT a.sumPrice - IFNULL(b.sumDiscout,0) AS sumTotal 
			FROM (
				SELECT p.pkg_id, SUM( sp.serpkg_total_price ) AS sumPrice 
				FROM packages p, service_packages sp, services s
				WHERE p.pkg_id = sp.pkg_id AND 
					  sp.ser_id = s.ser_id AND 
					  s.ser_date >= '$startDate' AND 
					  s.ser_date <= '$endDate' 
				GROUP BY p.pkg_id
				) a 
				LEFT JOIN
				(SELECT pd.pkg_id, SUM( sp.serpkgprm_discout_total ) AS sumDiscout
				FROM service_package_promotions sp, package_promotion_details pd
				WHERE pd.pkgprmdtl_id = sp.pkgprmdtl_id
				GROUP BY pd.pkg_id
				) b
				ON a.pkg_id = b.pkg_id";

	$result = mysql_query($sql, $dbConn);
	$rows   = mysql_num_rows($result);
	if($rows > 0) {
		$report['pkgTotalPrice'] = 0;
		for($i=0; $i<$rows; $i++) {
			$record = mysql_fetch_assoc($result);
			$report['pkgTotalPrice'] += $record['sumTotal'];
			$serTotalPrice += $record['sumTotal'];
		}
		$report['pkgTotalPrice'] = number_format($report['pkgTotalPrice'], 2);
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