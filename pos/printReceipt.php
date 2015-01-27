<?php
include('../config/config.php');
$tplName = 'printReceipt.html';
$subDir	 = WEB_ROOTDIR.'/pos/';

include('../common/common_header.php');

$sale_id 		= $_REQUEST['sale_id'];
$cash			= $_REQUEST['cash'];
$change			= 0;
$subtotal 		= 0;
$totalAmount 	= 0;

if(hasValue($sale_id)) {
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

	// Get sales data
	$saleData = array();
	$sql = "SELECT 	s.sale_id,
					s.sale_date,
					s.sale_time,
					e.emp_id,
					s.sale_discout,
					s.sale_prm_discout,
					s.sale_total_price 
			FROM 	sales s, employees e 
			WHERE 	s.emp_id = e.emp_id 
			 		AND s.sale_id = '$sale_id' LIMIT 1";
	$result = mysql_query($sql, $dbConn);
	$rows 	= mysql_num_rows($result);
	$saleRecord = mysql_fetch_assoc($result);
	$saleData = array(
			'sale_id' 			=> $sale_id,
			'sale_date' 		=> $saleRecord['sale_date'],
			'sale_time' 		=> $saleRecord['sale_time'],
			'emp_id' 			=> $saleRecord['emp_id'],
			'sale_discout' 		=> $saleRecord['sale_discout'],
			'sale_prm_discout' 	=> $saleRecord['sale_prm_discout'],
			'sale_total_price' 	=> number_format($saleRecord['sale_total_price'], 2)
			
	);
	$change = number_format($cash - $saleRecord['sale_total_price'], 2);
	$smarty->assign('saleData', $saleData);

	// Get sale details data
	$saledtlData = array();
	$sql = "SELECT 	sd.saledtl_id,
	 				p.prd_name,
	 				p.prd_price,
	 				FORMAT(sd.saledtl_amount, 0) saledtl_amount,
	 				sd.saledtl_price 
			FROM 	sale_details sd, products p 
			WHERE 	sd.prd_id = p.prd_id 
			AND 	sd.sale_id = '$sale_id'";
	//echo $sql;
	$result = mysql_query($sql, $dbConn);
	$rows 	= mysql_num_rows($result);
	for($i=0; $i<$rows; $i++) {
		$saledtlRecord = mysql_fetch_assoc($result);
		array_push($saledtlData, array(
			'saledtl_id' 		=> $saledtl_id,
			'prd_name' 			=> $saledtlRecord['prd_name'],
			'prd_price' 		=> number_format($saledtlRecord['prd_price'], 2),
			'saledtl_amount' 	=> $saledtlRecord['saledtl_amount'],
			'saledtl_price' 	=> number_format($saledtlRecord['saledtl_price'], 2)
			
		));
		$subtotal		+= $saledtlRecord['saledtl_price'];
		$totalAmount 	+= $saledtlRecord['saledtl_amount'];
	}
	$smarty->assign('saledtlData', $saledtlData);

	// Get sale promotion detail
	$saleprmdtlData = array();
	$sql = "SELECT 	p.prd_name,
	 				sp.saleprmdtl_amount,
	 				sp.saleprmdtl_discout 
			FROM 	sale_details sd,
					sale_promotion_details sp, 
					products p 
			WHERE 	sd.saledtl_id = sp.saledtl_id AND 
					sd.prd_id = p.prd_id AND 
					sd.sale_id = '$sale_id'";
	$result = mysql_query($sql, $dbConn);
	$rows 	= mysql_num_rows($result);
	for($i=0; $i<$rows; $i++) {
		$record 		= mysql_fetch_assoc($result);
		$prdDiscoutName = 'ส่วนลด';
		$each_discout 	= $record['saleprmdtl_discout'] / $record['saleprmdtl_amount'];
	  	$prdDiscoutName .= $record['prd_name'];
		array_push($saleprmdtlData, array(
			'prdDiscout_name' 		=> $prdDiscoutName,
			'saleprmdtl_amount' 	=> number_format($record['saleprmdtl_amount']),
			'each_discout' 			=> number_format($each_discout, 2),
			'saleprmdtl_discout' 	=> number_format($record['saleprmdtl_discout'], 2)
			
		));
	}
	$smarty->assign('saleprmdtlData', $saleprmdtlData);

	// Get sale promotion sale detail
	$saleprmSaledtlData = array();
	$sql = "SELECT 	p.prmds_name,
	 				sd.saleprmdsdtl_discout 
			FROM 	sale_promotion_sale_details sd 
					LEFT JOIN 
					promotion_discout_sales p 
					ON sd.prmds_id = p.prmds_id 
			WHERE 	sd.sale_id = '$sale_id'";
	$result = mysql_query($sql, $dbConn);
	$rows 	= mysql_num_rows($result);
	for($i=0; $i<$rows; $i++) {
		$record = mysql_fetch_assoc($result);
		array_push($saleprmSaledtlData, array(
			'prmds_name' 			=> $record['prmds_name'],
			'saleprmdsdtl_discout' 	=> number_format($record['saleprmdsdtl_discout'], 2) 
		));
	}
	$smarty->assign('saleprmSaledtlData', $saleprmSaledtlData);




	$smarty->assign('subtotal', number_format($subtotal, 2));
	$smarty->assign('totalAmount', $totalAmount);
	$smarty->assign('cash', number_format($cash, 2));
	$smarty->assign('change', $change);

}
$smarty->assign('randNum', substr(str_shuffle('0123456789'), 0, 5));
include('../common/common_footer.php');
?>