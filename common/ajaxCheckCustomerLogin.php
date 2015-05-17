<?php
session_start();
include('../config/config.php');
include('../common/common_header.php');

$formData		= array();
parse_str($_REQUEST['formData'], $formData);

$email = '';
$password = '';

if(hasValue($formData['email'])) {
	$email = $formData['email'];
}
if(hasValue($formData['password'])) {
	$password = md5($formData['password']);
}

$sql = "SELECT 	cus_id,
				cus_email,
				cus_name,
				cus_surname,
				custype_id 
		FROM 	customers 
		WHERE 	cus_email = '$email' 
				AND cus_pass = '$password'
		LIMIT 	1";
$result = mysql_query($sql, $dbConn);
$row	= mysql_num_rows($result);
if($row > 0) {
	$cusRow = mysql_fetch_assoc($result);
	$_SESSION['cusLogin'] 		= true;
	$_SESSION['cus_id'] 		= $cusRow['cus_id'];
	$_SESSION['cus_email'] 		= $cusRow['cus_email'];
	$_SESSION['cus_fullname'] 	= $cusRow['cus_name']." ".$cusRow['cus_surname'];
	$custype_id = $cusRow['custype_id'];

	// Remember me
	if(isset($formData['rememberMe'])) {
		if(!isset($_COOKIE['cusRememberMe'])) {
			setcookie("cusRememberMe", true, time() + (86400 * 30), "/"); // 86400 = 1 day
			setcookie("cus_id", $_SESSION['cus_id'], time() + (86400 * 30), "/"); 
			setcookie("cus_email", $_SESSION['cus_email'], time() + (86400 * 30), "/");
			setcookie("cus_fullname", $_SESSION['cus_fullname'], time() + (86400 * 30), "/");
		}
	}

	// Update session booking
	if((isset($_SESSION['bookingPkg']) || isset($_SESSION['bookingSvl'])) && hasValue($custype_id)) {
		if(isset($_SESSION['bookingPkg']) && is_array($_SESSION['bookingPkg'])) {
			$pkgIds  = array();

			// Get pkgIds
			foreach ($_SESSION['bookingPkg'] as $key => $val) {
				array_push($pkgIds, $val['pkg_id']);
			}

			// Get package price
			$pkgIds = wrapSingleQuote($pkgIds);
			$pkgList = array();
			$sql = "SELECT 		pkg_id,
								pkg_price 
					FROM 		packages 
					WHERE 		pkg_start <= '$nowDate' AND 
								(
									pkg_stop IS NULL OR 
									pkg_stop >= '$nowDate'
								) AND 
								pkg_id IN (".implode(',', $pkgIds).")";
			$result = mysql_query($sql, $dbConn);
			$rows 	= mysql_num_rows($result);
			if($rows > 0) {
				for($i=0; $i<$rows; $i++) {
					$record = mysql_fetch_assoc($result);
					$pkgList[$record['pkg_id']] = $record;
				}
			}

			// Get package real price
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
								pkgprm.custype_id = '$custype_id'";
			$result = mysql_query($sql, $dbConn);
			$rows 	= mysql_num_rows($result);
			if($rows > 0) {
				for($i=0; $i<$rows; $i++) {
					$record = mysql_fetch_assoc($result);
					$pkg_id = $record['pkg_id'];
					$discoutPrice = $record['pkgprmdtl_discout'];

					if($record['pkgprmdtl_discout_type'] == '%') {
						$discoutPrice = $pkgList[$pkg_id]['pkg_price'] * $record['pkgprmdtl_discout'] / 100;
					}
					$pkgList[$pkg_id]['pkg_price'] = $pkgList[$pkg_id]['pkg_price'] - $discoutPrice;
				}
			}

			// Update session booking package
			foreach ($_SESSION['bookingPkg'] as $pkg_id => $val) {
				$_SESSION['bookingPkg'][$pkg_id]['pkg_price'] = $pkgList[$pkg_id]['pkg_price'];
			}
		}

		if(isset($_SESSION['bookingSvl']) && is_array($_SESSION['bookingSvl'])) {
			$svlIds  = array();

			// Get svlIds
			foreach ($_SESSION['bookingSvl'] as $key => $val) {
				array_push($svlIds, $val['svl_id']);
			}

			// Get service_list price
			$svlIds = wrapSingleQuote($svlIds);
			$svlList = array();
			$sql = "SELECT 		svl_id,
								svl_price 
					FROM 		service_lists  
					WHERE 		svl_start <= '$nowDate' AND 
								(
									svl_stop IS NULL OR 
									svl_stop >= '$nowDate'
								) AND 
								svl_type = 1 AND 
								svl_id IN (".implode(',', $svlIds).")";
			$result = mysql_query($sql, $dbConn);
			$rows 	= mysql_num_rows($result);
			if($rows > 0) {
				for($i=0; $i<$rows; $i++) {
					$record = mysql_fetch_assoc($result);
					$svlList[$record['svl_id']] = $record;
				}
			}

			// Get service_list real price
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
								svlprm.custype_id = '$custype_id'";
			$result = mysql_query($sql, $dbConn);
			$rows 	= mysql_num_rows($result);
			if($rows > 0) {
				for($i=0; $i<$rows; $i++) {
					$record = mysql_fetch_assoc($result);
					$svl_id = $record['svl_id'];
					$discoutPrice = $record['svlprmdtl_discout'];

					if($record['svlprmdtl_discout_type'] == '%') {
						$discoutPrice = $svlList[$svl_id]['svl_price'] * $record['svlprmdtl_discout'] / 100;
					}
					$svlList[$svl_id]['svl_price'] = $svlList[$svl_id]['svl_price'] - $discoutPrice;
				}
			}

			// Update session booking package
			foreach ($_SESSION['bookingSvl'] as $svl_id => $val) {
				$_SESSION['bookingSvl'][$svl_id]['svl_price'] = $svlList[$svl_id]['svl_price'];
			}
		}
	}
	echo "PASS";
} else {
	echo "NOT_PASS";
}

?>