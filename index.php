<?php
session_start();
include('config/config.php');
$tplName = 'index.html';
$subDir	 = WEB_ROOTDIR.'/';

include('common/common_header.php');

// Find top 5 service list 
$oneMonthAgo = date('Y-m-d', strtotime('-1 months'));
$svlIds = array();
$topSvlList = array();
$sql = "SELECT 		svl.svl_name, 
					svl.svl_id, 
					svl.svl_desc, 
					svl.svl_price, 
					svl.svl_picture, 
					a.amount 
		FROM 		(
						SELECT 		ss.svl_id, 
									SUM( ss.sersvl_amount ) AS amount 
						FROM 		services s 
									JOIN service_service_lists ss ON ss.ser_id = s.ser_id 
						WHERE 		s.ser_date IS NULL 
									OR s.ser_date >=  '$oneMonthAgo'
						GROUP BY 	ss.svl_id 
					) a 
					RIGHT JOIN service_lists svl ON a.svl_id = svl.svl_id 
		WHERE  		svl.svl_start <=  '$nowDate' 
					AND (
						svl.svl_stop IS NULL 
						OR svl.svl_stop >=  '$nowDate'
					)
		ORDER BY 	a.amount DESC , svl.svl_name 
		LIMIT 5";
$result = mysql_query($sql, $dbConn);
$rows   = mysql_num_rows($result);
if($rows > 0) {
	for($i=0; $i<$rows; $i++) {
		$record = mysql_fetch_assoc($result);

		$topSvlList[$record['svl_id']] = array(
			'no' 		=> number_format($i+1),
			'svl_id' 	=> $record['svl_id'],
			'svl_name' 	=> $record['svl_name'],
			'svl_desc' 	=> $record['svl_desc'],
			'svl_price' => $record['svl_price'],
			'svl_picture' => $record['svl_picture'],
			'amount'	=> number_format($record['amount'])
		);
		array_push($svlIds, $record['svl_id']);
	}
}

// Get service_list promotion detail data
$svlIds = wrapSingleQuote($svlIds);
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
					svlprmdtl.svl_id IN (".implode(',', $svlIds).") AND 
					svlprm.custype_id = '".$_SESSION['custype_id']."'";
$result = mysql_query($sql, $dbConn);
$rows 	= mysql_num_rows($result);
if($rows > 0) {
	for($i=0; $i<$rows; $i++) {
		$record = mysql_fetch_assoc($result);
		$svl_id = $record['svl_id'];
		$discoutText = $record['svlprmdtl_discout'];
		$discoutPrice = $record['svlprmdtl_discout'];

		if($record['svlprmdtl_discout_type'] == '%') {
			$discoutPrice = $topSvlList[$svl_id]['svl_price'] * $record['svlprmdtl_discout'] / 100;
			$discoutText .= '%';
		} else {
			$discoutText .= ' บาท';
		}
		$topSvlList[$svl_id]['discoutText'] = $discoutText;
		$topSvlList[$svl_id]['svl_prmPrice'] = $topSvlList[$svl_id]['svl_price'] - $discoutPrice;
	}
}

// Find top 5 packages
$pkgIds = array();
$topPkgList = array();
$sql = "SELECT 		pkg.pkg_name, 
					pkg.pkg_id, 
					pkg.pkg_desc, 
					pkg.pkg_price, 
					pkg.pkg_picture, 
					a.amount 
		FROM 		(
						SELECT 		sp.pkg_id, 
									SUM( sp.serpkg_amount ) AS amount 
						FROM 		services s 
									JOIN service_packages sp ON sp.ser_id = s.ser_id 
						WHERE 		s.ser_date IS NULL 
									OR s.ser_date >=  '$oneMonthAgo'
						GROUP BY 	sp.pkg_id 
					) a 
					RIGHT JOIN packages pkg ON a.pkg_id = pkg.pkg_id 
		WHERE  		pkg.pkg_start <=  '$nowDate' 
					AND (
						pkg.pkg_stop IS NULL 
						OR pkg.pkg_stop >=  '$nowDate'
					)
		ORDER BY 	a.amount DESC , pkg.pkg_name 
		LIMIT 5"; 
$result = mysql_query($sql, $dbConn);
$rows   = mysql_num_rows($result);
if($rows > 0) {
	for($i=0; $i<$rows; $i++) {
		$record = mysql_fetch_assoc($result);

		$topPkgList[$record['pkg_id']] = array(
			'no' 		=> number_format($i+1),
			'pkg_id' 	=> $record['pkg_id'],
			'pkg_name' 	=> $record['pkg_name'],
			'pkg_desc' 	=> $record['pkg_desc'],
			'pkg_price' => $record['pkg_price'],
			'pkg_picture' => $record['pkg_picture'],
			'amount'	=> number_format($record['amount'])
		);
		array_push($pkgIds, $record['pkg_id']);
	}
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
					pkgprmdtl.pkg_id IN (".implode(',', $pkgIds).") AND 
					pkgprm.custype_id = '".$_SESSION['custype_id']."'";
$result = mysql_query($sql, $dbConn);
$rows 	= mysql_num_rows($result);
if($rows > 0) {
	for($i=0; $i<$rows; $i++) {
		$record = mysql_fetch_assoc($result);
		$pkg_id = $record['pkg_id'];
		$discoutText = $record['pkgprmdtl_discout'];
		$discoutPrice = $record['pkgprmdtl_discout'];

		if($record['pkgprmdtl_discout_type'] == '%') {
			$discoutPrice = $topPkgList[$pkg_id]['pkg_price'] * $record['pkgprmdtl_discout'] / 100;
			$discoutText .= '%';
		} else {
			$discoutText .= ' บาท';
		}
		$topPkgList[$pkg_id]['pkg_prmPrice'] = $topPkgList[$pkg_id]['pkg_price'] - $discoutPrice;
	}
}


$smarty->assign('topSvlList', $topSvlList);
$smarty->assign('topPkgList', $topPkgList);
$smarty->assign('tplName', $tplName);
include('common/common_footer.php');
?>

