<?php
session_start();
require('check_session.php');
include('../config/config.php');
$tplName = 'report_top_massage.html';
$subDir	 = WEB_ROOTDIR.'/backoffice/';

include('../common/common_header.php');

$startDate 		= '';
$endDate 		= '';
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


	$totalAmount 	= 0;
	$totalPrice  	= 0;
	$totalDiscout 	= 0;
	$totalRealPrice = 0;

	// Query Report Services List
	$reportServiceList = array();
	$sql = "SELECT a.svltyp_name,a.svltyp_name, a.svl_name, a.svl_hr, a.svl_min, a.svl_price, 
	 		a.sumAmount, a.sumPrice, IFNULL(b.discout,0) AS discout, a.sumPrice - IFNULL(b.discout,0) AS sumTotal
		FROM 
			( SELECT sl.svl_id, st.svltyp_id, st.svltyp_name, sl.svl_name, sl.svl_hr, sl.svl_min, sl.svl_price, SUM( ss.sersvl_amount ) AS sumAmount, SUM( ss.sersvl_total_price ) AS sumPrice
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
		for($i=0; $i<$rows; $i++) {
			$record = mysql_fetch_assoc($result);

			array_push($reportServiceList, array(
				// 'no' 			=> number_format($i+1),
				'svltyp_id' 	=> $record['svltyp_id'],
				'svltyp_name'	=> $record['svltyp_name'],
				'svl_name'		=> $record['svl_name'],
				'svl_hr'		=> $record['svl_hr'],
				'svl_min'		=> $record['svl_min'],
				'svl_price'		=> number_format($record['svl_price'], 2),
				'sumAmount'		=> number_format($record['sumAmount']),
				'sumPrice'		=> number_format($record['sumPrice'], 2),
				'discout'		=> number_format($record['discout'], 2),
				'sumTotal'		=> number_format($record['sumTotal'], 2)
			));
			
			$totalAmount 	+= $record['sumAmount'];
			$totalPrice 	+= $record['sumPrice'];
			$totalDiscout 	+= $record['discout'];
			$totalRealPrice += $record['sumTotal'];
		}

		$curSvlTypName = '';
		foreach ($reportServiceList as $key => $value) {
			if($curSvlTypName != $value['svltyp_name']) {
				$reportServiceList[$key]['type'] = $value['svltyp_name'];
				$curSvlTypName = $value['svltyp_name'];
			}
		}
			
		$smarty->assign('reportServiceList', $reportServiceList);
	}

	// Query Report Package
	$pkgIds = array();
	$reportPackage = array();
	$sql = "SELECT a.pkg_id, a.pkg_name, a.pkg_price, a.sumAmount, a.sumPrice, IFNULL(b.sumDiscout,0) AS sumDiscout, 
			a.sumPrice - IFNULL(b.sumDiscout,0) AS sumTotal 
			FROM (

				SELECT p.pkg_id, p.pkg_name, p.pkg_price, SUM( sp.serpkg_amount ) AS sumAmount, SUM( sp.serpkg_total_price ) AS sumPrice
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
		for($i=0; $i<$rows; $i++) {
			$record = mysql_fetch_assoc($result);

			$reportPackage[$record['pkg_id']] = array(
				// 'no' 			=> number_format($i+1),
				'pkg_name' 		=> $record['pkg_name'],
				'pkg_price'		=> number_format($record['pkg_price'], 2),
				'sumAmount'		=> number_format($record['sumAmount']),
				'sumPrice'		=> number_format($record['sumPrice'], 2),
				'sumDiscout'	=> number_format($record['sumDiscout'], 2),
				'sumTotal'		=> number_format($record['sumTotal'], 2)
			);
			array_push($pkgIds, $record['pkg_id']);

			$totalAmount 	+= $record['sumAmount'];
			$totalPrice 	+= $record['sumPrice'];
			$totalDiscout 	+= $record['sumDiscout'];
			$totalRealPrice += $record['sumTotal'];
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
				$reportPackage[$pkg_id]['pkg_hr'] = $sumHr;
				$reportPackage[$pkg_id]['pkg_min'] = $sumMin;
			}
		}

		$smarty->assign('reportPackage', $reportPackage);
	}



	$smarty->assign('totalAmount', number_format($totalAmount));
	$smarty->assign('totalPrice', number_format($totalPrice, 2));
	$smarty->assign('totalDiscout', number_format($totalDiscout, 2));
	$smarty->assign('totalRealPrice', number_format($totalRealPrice, 2));
}

// Query Service List Type
$svltyps = array();
$sql = "SELECT svltyp_id, svltyp_name FROM service_list_types";
$result = mysql_query($sql, $dbConn);
$rows   = mysql_num_rows($result);

if($rows > 0) {
	for($i=0; $i<$rows; $i++) {
		$record = mysql_fetch_assoc($result);
		$svltyps[$record['svltyp_id']]['svltyp_id'] 	= $record['svltyp_id'];
		$svltyps[$record['svltyp_id']]['svltyp_name'] 	= $record['svltyp_name'];
	}
	// product Checked
	if(isset($_POST['prdtypSelected'])) {
		foreach ($_POST['prdtypSelected'] as $key => $svltyp_id) {
			$svltyps[$svltyp_id]['checked'] = true;
		}
	}
	$smarty->assign('svltyps', $svltyps);
}

include('../common/common_footer.php');
?>