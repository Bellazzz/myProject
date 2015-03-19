<?php
session_start();
//require('check_session.php');
include('config/config.php');
$tplName = 'shopping_cart.html';
$subDir	 = WEB_ROOTDIR.'/';

include('common/common_header.php');

// Get booking service list session
if(isset($_SESSION['bookingSvl']) && is_array($_SESSION['bookingSvl'])) {
	$svlList = $_SESSION['bookingSvl'];
	$svlIds 	= array();
	foreach ($svlList as $svl_id => $svl) {
		array_push($svlIds, $svl['svl_id']);
		$svlList[$svl_id]['bkg_date_th'] = dateThaiFormat($svlList[$svl_id]['bkg_date']);
	}
	$svlIds = wrapSingleQuote($svlIds);

	if(count($svlIds) > 0) {
		$sql = "SELECT 	svl_id,
						svl_name,
						svl_picture,
						svl_price 
				FROM 	service_lists 
				WHERE 	svl_id IN (".implode(',', $svlIds).")";
		$result = mysql_query($sql, $dbConn);
		$rows 	= mysql_num_rows($result);
		if($rows > 0) {
			for($i=0; $i<$rows; $i++) {
				$record = mysql_fetch_assoc($result);
				$svlList[$record['svl_id']]['svl_name'] 		= $record['svl_name'];
				$svlList[$record['svl_id']]['svl_picture'] 		= $record['svl_picture'];
				$svlList[$record['svl_id']]['svl_price'] 	= $record['svl_price'];
				$svlList[$record['svl_id']]['svl_realPrice'] = $record['svl_price'];
				$svlList[$record['svl_id']]['discout_price'] = 0.00;
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
							svlprmdtl.svl_id IN (".implode(',', $svlIds).") AND 
							svlprm.custype_id = 'CT2'";
		$result = mysql_query($sql, $dbConn);
		$rows 	= mysql_num_rows($result);
		if($rows > 0) {
			for($i=0; $i<$rows; $i++) {
				$record = mysql_fetch_assoc($result);
				$svl_id = $record['svl_id'];
				$discoutText = $record['svlprmdtl_discout'];
				$discoutPrice = $record['svlprmdtl_discout'];

				if($record['svlprmdtl_discout_type'] == '%') {
					$discoutPrice = $svlList[$record['svl_id']]['svl_price'] * $record['svlprmdtl_discout'] / 100;
					$discoutText .= '%';
				} else {
					$discoutText .= ' บาท';
				}
				$svlList[$record['svl_id']]['discout_price'] = $discoutPrice;
				$svlList[$record['svl_id']]['svl_realPrice']  = $svlList[$record['svl_id']]['svl_price'] - $discoutPrice;
			}
		}
	}

	$smarty->assign('svlList', $svlList);
}


// Get booking packages session
if(isset($_SESSION['bookingPkg']) && is_array($_SESSION['bookingPkg'])) {
	$pkgList = $_SESSION['bookingPkg'];
	$pkgIds 	= array();
	foreach ($pkgList as $pkg_id => $pkg) {
		array_push($pkgIds, $pkg['pkg_id']);
		$pkgList[$pkg_id]['bkg_date_th'] = dateThaiFormat($pkgList[$pkg_id]['bkg_date']);
	}
	$pkgIds = wrapSingleQuote($pkgIds);

	if(count($pkgIds) > 0) {
		$sql = "SELECT 	pkg_id,
						pkg_name,
						pkg_picture,
						pkg_price 
				FROM 	packages 
				WHERE 	pkg_id IN (".implode(',', $pkgIds).")";
		$result = mysql_query($sql, $dbConn);
		$rows 	= mysql_num_rows($result);
		if($rows > 0) {
			for($i=0; $i<$rows; $i++) {
				$record = mysql_fetch_assoc($result);
				$pkgList[$record['pkg_id']]['pkg_name'] 	= $record['pkg_name'];
				$pkgList[$record['pkg_id']]['pkg_picture'] 	= $record['pkg_picture'];
				$pkgList[$record['pkg_id']]['pkg_price'] 	= $record['pkg_price'];
				$pkgList[$record['pkg_id']]['pkg_realPrice'] = $record['pkg_price'];
				$pkgList[$record['pkg_id']]['discout_price'] = 0.00;
			}
		}

		// Get service_list promotion detail data
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
							pkgprm.custype_id = 'CT2'";
		$result = mysql_query($sql, $dbConn);
		$rows 	= mysql_num_rows($result);
		if($rows > 0) {
			for($i=0; $i<$rows; $i++) {
				$record = mysql_fetch_assoc($result);
				$pkg_id = $record['pkg_id'];
				$discoutText = $record['pkgprmdtl_discout'];
				$discoutPrice = $record['pkgprmdtl_discout'];

				if($record['pkgprmdtl_discout_type'] == '%') {
					$discoutPrice = $pkgList[$record['pkg_id']]['svl_price'] * $record['pkgprmdtl_discout'] / 100;
					$discoutText .= '%';
				} else {
					$discoutText .= ' บาท';
				}
				$pkgList[$record['pkg_id']]['discout_price'] = $discoutPrice;
				$pkgList[$record['pkg_id']]['pkg_realPrice']  = $pkgList[$record['pkg_id']]['pkg_price'] - $discoutPrice;
			}
		}
	}

	$smarty->assign('pkgList', $pkgList);
}

$smarty->assign('tplName', $tplName);
include('common/common_footer.php');
?>

