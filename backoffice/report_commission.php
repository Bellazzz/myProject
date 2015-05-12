<?php
session_start();
require('check_session.php');
include('../config/config.php');
$tplName = 'report_commission.html';
$subDir	 = WEB_ROOTDIR.'/backoffice/';

include('../common/common_header.php');

$startDate 		= '';
$endDate 		= '';
$curEmp_id = '';

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
	if(isset($_POST['curEmp_id'])) {
		$curEmp_id = $_POST['curEmp_id'];
		$smarty->assign('curEmp_id', $curEmp_id);
	}

	// Find employee full name
	$empRecord = new TableSpa('employees', $curEmp_id);
	$smarty->assign('empFullName', $empRecord->getFieldValue('emp_name')." ".$empRecord->getFieldValue('emp_surname'));


	// Query Report
	$report = array();
	$sql = "SELECT 		s.ser_date date, 
						s.cus_id, 
						c.custype_id,
						CONCAT(c.cus_name, ' ', c.cus_surname) customer, 
						svl.svl_id,
						svl.svl_name, 
						IFNULL(svl.svl_hr,0) hr, 
						IFNULL(svl.svl_min,0) min, 
						null pkg_id,
						ss.sersvl_time time, 
						sd.svldtl_com com 
			FROM 		service_list_details sd, 
						service_service_lists ss, 
						services s, 
						service_lists svl,
						customers c  
			WHERE 		sd.sersvl_id = ss.sersvl_id AND 
						ss.ser_id = s.ser_id AND 
						sd.svl_id = svl.svl_id AND 
						s.cus_id = c.cus_id AND 
						sd.emp_id = '$curEmp_id' AND 
						s.ser_date >= '$startDate' AND 
						s.ser_date <= '$endDate'
			UNION ALL 
			SELECT 		s.ser_date date, 
						s.cus_id, 
						c.custype_id,
						CONCAT(c.cus_name, ' ', c.cus_surname) customer, 
						svl.svl_id,
						svl.svl_name, 
						IFNULL(pkgsvl.pkgsvl_hr,0) hr, 
						IFNULL(pkgsvl.pkgsvl_min,0) min,
						pkgsvl.pkg_id,
						sersvt.sersvt_time time, 
						pkgdtl.pkgdtl_com com
			FROM 		package_details pkgdtl, 
						service_service_list_times sersvt, 
						package_service_lists pkgsvl, 
						service_lists svl, 
						service_packages serpkg, 
						services s, 
						customers c  
			WHERE 		pkgdtl.sersvt_id = sersvt.sersvt_id AND 
						sersvt.pkgsvl_id = pkgsvl.pkgsvl_id AND 
						pkgsvl.svl_id = svl.svl_id AND 
						sersvt.serpkg_id = serpkg.serpkg_id AND 
						serpkg.ser_id = s.ser_id AND 
						s.cus_id = c.cus_id AND 
						pkgdtl.emp_id = '$curEmp_id' AND 
						s.ser_date >= '$startDate' AND 
						s.ser_date <= '$endDate' 
			ORDER BY 	date, TIME";
	$result = mysql_query($sql, $dbConn);
	$rows   = mysql_num_rows($result);
	if($rows > 0) {
		$countDate = array();
		$noDate = 0;
		$totalRealPrice = 0;
		$totalCom = 0;
		for($i=0; $i<$rows; $i++) {
			$record = mysql_fetch_assoc($result);
			$allMin 			= $record['hr'] * 60 + $record['min'];
			$startDate 			= strtotime($nowDate." ".$record['time']);
			$endDate 			= $startDate+(60*$allMin);
			$record['timeEnd'] 	= date('H:i', $endDate);
			$record['time'] 	= substr($record['time'], 0, 5);
			$record['dateTH'] 	= dateThaiFormatShort($record['date']);
			$currentDay 		= getSpaCurrentDay($record['date']);
			$record['comRate'] 	= getComRate($currentDay, $record['time'], $record['timeEnd']);

			// Get realPrice 
			if($record['pkg_id'] != null) {
				// package service list
				$pkgsvlRealPrices 		= getPkgSvlPrices($record['date'], $record['custype_id']);
				$record['realPrice'] 	= $pkgsvlRealPrices[$record['pkg_id']][$record['svl_id']]['realPrice'];
			} else {
				// service list
				$svlRealPrices 			= getSvlPrices($record['date'], $record['custype_id']);
				$record['realPrice'] 	= $svlRealPrices[$record['svl_id']]['realPrice'];
			}

			$record['fullComRate'] = $record['realPrice'] * $record['comRate'] / 100;
			$record['devidePer'] = $record['com'] / $record['fullComRate'] * 100;

			// Cal total
			$totalRealPrice += $record['realPrice'];
			$totalCom += $record['com'];

			// set format
			$record['com'] 			= number_format($record['com'],2);
			$record['realPrice'] 	= number_format($record['realPrice'],2);
			
			array_push($report, $record);


			if(!isset($countDate[$record['date']])) {
				$countDate[$record['date']] = 0;
				$noDate++;
			}
			$countDate[$record['date']]++;
		}

		$curDate = '';
		$curCus = '';
		foreach ($report as $key => $value) {
			if($curDate != $value['date']) {
				$report[$key]['dateRowspan'] = $countDate[$value['date']];
				$curDate = $value['date'];
			}
			if($curCus != $value['customer']) {
				$curCus = $value['customer'];
				$i = $key;
				$cusRowSpan = 0;
				while ($curCus == $report[$i]['customer']) {
					$cusRowSpan++;
					$i++;
				}
				if($cusRowSpan > 0)
					$report[$key]['cusRowspan'] = $cusRowSpan;
			}
		}
		$smarty->assign('report', $report);
		$smarty->assign('totalRealPrice', number_format($totalRealPrice,2));
		$smarty->assign('totalCom', number_format($totalCom,2));
	}
}

// Find employee select reference
$refEmpData = array();
$sql = "SELECT 		emp_id refValue,
					CONCAT(emp_name, ' ', emp_surname) refText 
		FROM 		employees 
		ORDER BY 	refText ASC";
$result = mysql_query($sql, $dbConn);
$rows = mysql_num_rows($result);
if($rows > 0) {
	for($i=0; $i<$rows; $i++) {
		array_push($refEmpData, mysql_fetch_assoc($result));
		$refEmpData[$i]['refField'] = 'emp_id';
	}
}
$smarty->assign('refEmpData', $refEmpData);


function getPkgSvlPrices($date, $custype_id) {
	global $dbConn;
	$pkgsvlData = array();
	$sql = "SELECT 		p.pkg_id,
						ps.svl_id,
						ps.pkgsvl_price 
			FROM 		packages p,
						package_service_lists ps 
			WHERE 		p.pkg_id = ps.pkg_id  
			ORDER BY 	ps.pkgsvl_id";
	$result = mysql_query($sql, $dbConn);
	$rows 	= mysql_num_rows($result);
	for($i=0; $i<$rows; $i++) {
		$record = mysql_fetch_assoc($result);
		$pkgsvlData[$record['pkg_id']][$record['svl_id']] = array(
			'price' => $record['pkgsvl_price'],
			'realPrice' => $record['pkgsvl_price']
		);
	}

	// Find pkgsvl real price for discout percent
	$sql = "SELECT 		pkgprmdtl.pkg_id,
						pkgprmdtl.pkgprmdtl_discout,
						pkgprmdtl.pkgprmdtl_discout_type 
			FROM 		package_promotion_details pkgprmdtl,
						package_promotions pkgprm 
			WHERE 		pkgprmdtl.pkgprm_id = pkgprm.pkgprm_id AND 
						pkgprm.custype_id = '$custype_id' AND 
						pkgprmdtl.pkgprmdtl_startdate <= '$date' AND 
						(
							pkgprmdtl.pkgprmdtl_enddate IS NULL OR 
							pkgprmdtl.pkgprmdtl_enddate >= '$date'
						)";
	$result = mysql_query($sql, $dbConn);
	$rows 	= mysql_num_rows($result);
	for($i=0; $i<$rows; $i++) {
		$record = mysql_fetch_assoc($result);
		if(isset($pkgsvlData[$record['pkg_id']])) {
			$pkgsvlData[$record['pkg_id']]['discout_type'] = $record['pkgprmdtl_discout_type'];
			$pkgsvlData[$record['pkg_id']]['discout'] = $record['pkgprmdtl_discout'];
			foreach ($pkgsvlData[$record['pkg_id']] as $key => $value) {
				if(is_array($value) && isset($value['realPrice']) && $value['realPrice'] > 0) {
					if($record['pkgprmdtl_discout_type'] == '%') {
						$realPrice = $value['price'] - ($value['realPrice'] * $record['pkgprmdtl_discout'] / 100);
						$pkgsvlData[$record['pkg_id']][$key]['realPrice'] = $realPrice;
					}
				}
			}
		}
	}

	return $pkgsvlData;
}

function getSvlPrices($date, $custype_id) {
	global $dbConn;
	$svlData = array();
	$sql = "SELECT 		svl_id,
						svl_price 
			FROM 		service_lists  
			ORDER BY 	svl_id";
	$result = mysql_query($sql, $dbConn);
	$rows 	= mysql_num_rows($result);
	for($i=0; $i<$rows; $i++) {
		$record = mysql_fetch_assoc($result);
		$svlData[$record['svl_id']] = array(
			'price' => $record['svl_price'],
			'realPrice' => $record['svl_price']
		);
	}

	// Find pkgsvl real price for discout percent
	$sql = "SELECT 		svlprmdtl.svl_id,
						svlprmdtl.svlprmdtl_discout,
						svlprmdtl.svlprmdtl_discout_type 
			FROM 		service_list_promotion_details svlprmdtl,
						service_list_promotions svlprm 
			WHERE 		svlprmdtl.svlprm_id = svlprm.svlprm_id AND 
						svlprm.custype_id = '$custype_id' AND 
						svlprmdtl.svlprmdtl_startdate <= '$date' AND 
						(
							svlprmdtl.svlprmdtl_enddate IS NULL OR 
							svlprmdtl.svlprmdtl_enddate >= '$date'
						)";
	$result = mysql_query($sql, $dbConn);
	$rows 	= mysql_num_rows($result);
	for($i=0; $i<$rows; $i++) {
		$record = mysql_fetch_assoc($result);
		$svl_id = $record['svl_id'];
		if(isset($svlData[$svl_id])) {
			$svlData[$svl_id]['discout_type'] = $record['svlprmdtl_discout_type'];
			$svlData[$svl_id]['discout'] = $record['svlprmdtl_discout'];
			if(isset($svlData[$svl_id]['realPrice']) && $svlData[$svl_id]['realPrice'] > 0) {
				if($record['svlprmdtl_discout_type'] == '%') {
					$discout = $svlData[$svl_id]['realPrice'] * $record['svlprmdtl_discout'] / 100;
				} else {
					$discout = $record['svlprmdtl_discout'];
				}
				$realPrice = $svlData[$svl_id]['price'] - $discout;
				$svlData[$svl_id]['realPrice'] = $realPrice;
			}
		}
	}

	return $svlData;
}

$smarty->assign('tplName', $tplName);
include('../common/common_footer.php');
?>