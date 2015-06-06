<?php
include('../config/config.php');
$tplName = 'printBookingPaper.html';
$subDir	 = WEB_ROOTDIR.'/backoffice/';

include('../common/common_header.php');

$bkg_id 		= $_GET['bkg_id'];
$bnkacc_id 		= $_GET['bnkacc_id'];
$printImmediately = true;

if(hasValue($bkg_id)) {
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

	// Get booking data
	$serData = array();
	$sql = "SELECT 	b.bkg_id,
					b.bkg_date,
					DATE_FORMAT(b.bkg_time,'%H:%i') bkg_time,
					b.bkg_total_price,
					b.bkg_transfer_date,
					DATE_FORMAT(b.bkg_transfer_time,'%H:%i') bkg_transfer_time,
					b.bkg_transfer_evidence,
					b.bkg_transfer_money,
					b.status_id,
					bs.bkgstat_name,
					ba.bnkacc_name,
					CONCAT(t.title_name,c.cus_name,' ',c.cus_surname) cus_fullName 
			FROM 	booking b 
					LEFT JOIN 	bank_accounts ba ON b.bnkacc_id = ba.bnkacc_id 
					JOIN 		booking_status bs ON b.status_id = bs.bkgstat_id 
					LEFT JOIN  	employees e 	ON b.emp_id = e.emp_id 
					JOIN 		customers c 	ON b.cus_id = c.cus_id 
					JOIN 		titles t 		ON c.title_id = t.title_id 
			WHERE 	b.bkg_id = '$bkg_id' LIMIT 1";
	$result = mysql_query($sql, $dbConn);
	$rows 	= mysql_num_rows($result);
	if($rows > 0) {
		$bkgData = mysql_fetch_assoc($result);
		if($bkgData['bkg_date'] != '') {
			$bkgData['bkg_date_th'] = dateThaiFormat($bkgData['bkg_date']);
		}
		if($bkgData['bkg_transfer_date'] != '') {
			$bkgData['bkg_transfer_date_th'] = dateThaiFormat($bkgData['bkg_transfer_date']);
		}
		
		$smarty->assign('bkgData', $bkgData);
	}

	// Get booking service list data
	$svlList = array();
	$sql = "SELECT 	s.svl_id,
					s.svl_name,
					bs.bkgsvl_persons,
					bs.bkgsvl_date,
					DATE_FORMAT(bs.bkgsvl_time,'%H:%i') bkgsvl_time,
					bs.bkgsvl_status 
			FROM 	booking_service_lists bs,
					service_lists s 
			WHERE 	bs.svl_id = s.svl_id AND 
					bs.bkg_id = '$bkg_id'";
	$result = mysql_query($sql, $dbConn);
	$rows 	= mysql_num_rows($result);
	if($rows > 0) {
		for($i=0; $i<$rows; $i++) {
			$record = mysql_fetch_assoc($result);
			$record['bkgsvl_date_th'] = dateThaiFormat($record['bkgsvl_date']);
			$svlList[$record['svl_id']] = $record;
		}
		$smarty->assign('svlList', $svlList);
	}

	// Get booking package data
	$pkgList = array();
	$sql = "SELECT 	p.pkg_id,
					p.pkg_name,
					bp.bkgpkg_persons,
					bp.bkgpkg_date,
					DATE_FORMAT(bp.bkgpkg_time,'%H:%i') bkgpkg_time,
					bp.bkgpkg_status 
			FROM 	booking_packages bp,
					packages p 
			WHERE 	bp.pkg_id = p.pkg_id AND 
					bp.bkg_id = '$bkg_id'";
	$result = mysql_query($sql, $dbConn);
	$rows 	= mysql_num_rows($result);
	if($rows > 0) {
		for($i=0; $i<$rows; $i++) {
			$record = mysql_fetch_assoc($result);
			$record['bkgpkg_date_th'] = dateThaiFormat($record['bkgpkg_date']);
			$pkgList[$record['pkg_id']] = $record;
		}
		$smarty->assign('pkgList', $pkgList);
	}

	// Get bank account data
	$bnkaccRecord = new TableSpa('bank_accounts', $bnkacc_id);
	$bnkaccData = array(
		'bnkacc_no' 	=> $bnkaccRecord->getFieldValue('bnkacc_no'),
		'bnkacc_name'	=> $bnkaccRecord->getFieldValue('bnkacc_name'),
		'bnkacc_branch'	=> $bnkaccRecord->getFieldValue('bnkacc_branch')
	);
	$smarty->assign('bnkaccData', $bnkaccData);
	$smarty->assign('bnkacc_id', $bnkacc_id);


}
$smarty->assign('randNum', substr(str_shuffle('0123456789'), 0, 5));
if(hasValue($_REQUEST['printImmediately'])) {
	$printImmediately = (bool)$_REQUEST['printImmediately'];
}
$smarty->assign('printImmediately', $printImmediately);
include('../common/common_footer.php');
?>