<?php
include('../config/config.php');
$tplName = 'printServiceReceipt.html';
$subDir	 = WEB_ROOTDIR.'/backoffice/';

include('../common/common_header.php');

$ser_id 		= $_REQUEST['ser_id'];
$cash			= $_REQUEST['cash'];
$change			= 0;
$subtotal 		= 0;
$totalAmount 	= 0;
$printImmediately = true;

if(hasValue($ser_id)) {
	// Get spa data
	$spaRecord 	= new TableSpa('spa', 'SA01');
	$spaData 	= array(
		'spa_name' 	=> $spaRecord->getFieldValue('spa_name'),
		'spa_addr' 	=> $spaRecord->getFieldValue('spa_addr'),
		'spa_tel' 	=> $spaRecord->getFieldValue('spa_tel'),
		'spa_fax' 	=> $spaRecord->getFieldValue('spa_fax'),
		'spa_email' => $spaRecord->getFieldValue('spa_email'),
		'spa_logo'  => $spaRecord->getFieldValue('spa_logo'),
	);
	// Check null
	$spaData['spa_fax'] 	= $spaData['spa_fax'] 	== '' ? '-' : $spaData['spa_fax'];
	$spaData['spa_email'] 	= $spaData['spa_email'] == '' ? '-' : $spaData['spa_email'];
	$spaData['spa_logo'] 	= $spaData['spa_logo']  == '' ? ''  : $spaData['spa_logo'];

	$smarty->assign('spaData', $spaData);

	// Get services data
	$serData = array();
	$sql = "SELECT 	s.ser_id,
					s.ser_date,
					s.ser_time,
					e.emp_id,
					CONCAT(t.title_name,c.cus_name,' ',c.cus_surname) cus_fullName,
					s.ser_prm_discout,
					s.ser_total_price,
					s.ser_pay_price 
			FROM 	services s, employees e, customers c, titles t 
			WHERE 	s.emp_id = e.emp_id AND 
					s.cus_id = c.cus_id AND 
					c.title_id = t.title_id AND 
					s.ser_id = '$ser_id' LIMIT 1";
	$result = mysql_query($sql, $dbConn);
	$rows 	= mysql_num_rows($result);
	$serviceRecord = mysql_fetch_assoc($result);
	$serData = array(
			'ser_id' 			=> $ser_id,
			'ser_date' 			=> $serviceRecord['ser_date'],
			'ser_time' 			=> $serviceRecord['ser_time'],
			'emp_id' 			=> $serviceRecord['emp_id'],
			'cus_fullName' 		=> $serviceRecord['cus_fullName'],
			'ser_prm_discout' 	=> $serviceRecord['ser_prm_discout'],
			'ser_total_price' 	=> number_format($serviceRecord['ser_total_price'], 2),
			'ser_pay_price' 	=> number_format($serviceRecord['ser_pay_price'], 2)
	);
	$cash = $serviceRecord['ser_pay_price'];
	$change = number_format($cash - $serviceRecord['ser_total_price'], 2);
	$serData['ser_total_price_text'] = moneyThaiText($serviceRecord['ser_total_price']);
	$smarty->assign('serData', $serData);

	// Get service_packages data
	$serpkgData = array();
	$sql = "SELECT 	sp.pkg_id,
	 				p.pkg_name,
	 				p.pkg_price,
	 				FORMAT(sp.serpkg_amount, 0) serpkg_amount,
	 				sp.serpkg_total_price 
			FROM 	service_packages sp, packages p 
			WHERE 	sp.pkg_id = p.pkg_id 
			AND 	sp.ser_id = '$ser_id'";
	$result = mysql_query($sql, $dbConn);
	$rows 	= mysql_num_rows($result);
	for($i=0; $i<$rows; $i++) {
		$record = mysql_fetch_assoc($result);
		$serpkgData[$record['pkg_id']] = array(
			'pkg_id' 				=> $record['pkg_id'],
			'pkg_name' 				=> $record['pkg_name'],
			'pkg_price' 			=> number_format($record['pkg_price'], 2),
			'serpkg_amount' 		=> $record['serpkg_amount'],
			'serpkg_discout' 		=> number_format(0.00, 2),
			'serpkg_total_price' 	=> $record['serpkg_total_price']
		);
		$subtotal		+= $record['serpkg_total_price'];
		$totalAmount 	+= $record['serpkg_amount'];
	}
	
	// Get service_service_lists data
	$sersvlData = array();
	$sql = "SELECT 	ss.svl_id,
	 				s.svl_name,
	 				s.svl_price,
	 				FORMAT(ss.sersvl_amount, 0) sersvl_amount,
	 				ss.sersvl_total_price 
			FROM 	service_service_lists ss, service_lists s 
			WHERE 	ss.svl_id = s.svl_id 
			AND 	ss.ser_id = '$ser_id'";
	$result = mysql_query($sql, $dbConn);
	$rows 	= mysql_num_rows($result);
	for($i=0; $i<$rows; $i++) {
		$record = mysql_fetch_assoc($result);
		$sersvlData[$record['svl_id']] = array(
			'svl_id' 				=> $record['svl_id'],
			'svl_name' 				=> $record['svl_name'],
			'svl_price' 			=> number_format($record['svl_price'], 2),
			'sersvl_amount' 		=> $record['sersvl_amount'],
			'sersvl_discout' 		=> number_format(0.00, 2),
			'sersvl_total_price' 	=> $record['sersvl_total_price']
		);
		$subtotal		+= $record['sersvl_total_price'];
		$totalAmount 	+= $record['sersvl_amount'];
	}

	// Get service package promotions data
	$sql = "SELECT 	p.pkg_id,
	 				pp.serpkgprm_discout_total 
			FROM 	service_package_promotions pp,
					package_promotion_details pd, 
					packages p 
			WHERE 	pp.pkgprmdtl_id = pd.pkgprmdtl_id AND 
					pd.pkg_id = p.pkg_id AND 
					pp.ser_id = '$ser_id'";
	$result = mysql_query($sql, $dbConn);
	$rows 	= mysql_num_rows($result);
	for($i=0; $i<$rows; $i++) {
		$record = mysql_fetch_assoc($result);
		$serpkgData[$record['pkg_id']]['serpkg_discout'] += $record['serpkgprm_discout_total']; 
		$serpkgData[$record['pkg_id']]['serpkg_total_price'] -= $record['serpkgprm_discout_total']; 
	}

	// Get service service_list promotions data
	$sql = "SELECT 	s.svl_id,
	 				sp.sersvlprm_discout_total 
			FROM 	service_service_list_promotions sp,
					service_list_promotion_details sd, 
					service_lists s 
			WHERE 	sp.svlprmdtl_id = sd.svlprmdtl_id AND 
					sd.svl_id = s.svl_id AND 
					sp.ser_id = '$ser_id'";
	$result = mysql_query($sql, $dbConn);
	$rows 	= mysql_num_rows($result);
	for($i=0; $i<$rows; $i++) {
		$record = mysql_fetch_assoc($result);
		$sersvlData[$record['svl_id']]['sersvl_discout'] += $record['sersvlprm_discout_total']; 
		$sersvlData[$record['svl_id']]['sersvl_total_price'] -= $record['sersvlprm_discout_total']; 
	}
	

	$smarty->assign('serpkgData', $serpkgData);
	$smarty->assign('sersvlData', $sersvlData);
	$smarty->assign('subtotal', number_format($subtotal, 2));
	$smarty->assign('totalAmount', $totalAmount);
	$smarty->assign('cash', number_format($cash, 2));
	$smarty->assign('change', $change);

}
$smarty->assign('randNum', substr(str_shuffle('0123456789'), 0, 5));
if(hasValue($_REQUEST['printImmediately'])) {
	$printImmediately = (bool)$_REQUEST['printImmediately'];
}
$smarty->assign('printImmediately', $printImmediately);
include('../common/common_footer.php');
?>