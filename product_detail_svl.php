<?php
session_start();
//require('check_session.php');
include('config/config.php');
$tplName = 'product_detail_svl.html';
$subDir	 = WEB_ROOTDIR.'/';

include('common/common_header.php');

$id = '';

if(hasValue($_GET['id'])) {
	$id = $_GET['id'];

	if(isset($_GET['edit']) && isset($_SESSION['bookingSvl'][$id])) {
		$smarty->assign('persons', $_SESSION['bookingSvl'][$id]['persons']);
		$smarty->assign('bkg_date', $_SESSION['bookingSvl'][$id]['bkg_date']);
		$smarty->assign('bkg_time', $_SESSION['bookingSvl'][$id]['bkg_time']);
		$smarty->assign('edit', true);
	}
}

$sql = "SELECT 		s.svl_id,
					t.svltyp_name,
					s.svl_hr,
					s.svl_min,
					s.svl_name,
					s.svl_price,
					s.svl_desc,
					s.svl_picture 
		FROM 		service_lists s,
					service_list_types t 
		WHERE 		s.svltyp_id = t.svltyp_id AND 
					s.svl_id = '$id'";
$result = mysql_query($sql, $dbConn);
$rows 	= mysql_num_rows($result);
if($rows > 0) {
	$prdData = mysql_fetch_assoc($result);
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
					svlprmdtl.svl_id ='$id' AND 
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
			$discoutPrice = $prdData['svl_price'] * $record['svlprmdtl_discout'] / 100;
			$discoutText .= '%';
		} else {
			$discoutText .= ' บาท';
		}
		$prdData['svl_prmPrice'] = $prdData['svl_price'] - $discoutPrice;
	}
}

$times = array();
for($i=9; $i<=20; $i++) {
	$time = '';
	if($i < 10) {
		$time .= '0';
	}
	$time .= $i.':00';
	array_push($times, $time);
}

$smarty->assign('prdData', $prdData);
$smarty->assign('times', $times);

$smarty->assign('tplName', $tplName);
include('common/common_footer.php');
?>

