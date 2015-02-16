<?php
include('../config/config.php');
$tplName = 'printPurchaseOrder.html';
$subDir	 = WEB_ROOTDIR.'/backoffice/';

include('../common/common_header.php');
###### End initial ######


$ordId 			= $_REQUEST['ordId'];
$hideBackButton = $_REQUEST['hideBackButton'];

if(hasValue($ordId)) {
	// Get spa data
	$spaRecord 	= new TableSpa('spa', 'SA01');
	$spaData 	= array(
		'spa_name' 	=> $spaRecord->getFieldValue('spa_name'),
		'spa_addr' 	=> $spaRecord->getFieldValue('spa_addr'),
		'spa_tel' 	=> $spaRecord->getFieldValue('spa_tel'),
		'spa_fax' 	=> $spaRecord->getFieldValue('spa_fax'),
		'spa_email' => $spaRecord->getFieldValue('spa_email')
	);
	// Check null
	$spaData['spa_fax'] 	= $spaData['spa_fax'] == '' ? '-' : $spaData['spa_fax'];
	$spaData['spa_email'] 	= $spaData['spa_email'] == '' ? '-' : $spaData['spa_email'];

	$smarty->assign('spaData', $spaData);

	// Get orders data
	$ordData = array();
	$sql = "SELECT 	o.ord_date,
	 				o.ord_snd_date,
	 				o.ordstat_id,
	 				ot.ordtyp_name,
	 				CONCAT(t.title_name, e.emp_name, ' ', e.emp_surname) emp_fullname,
	 				c.comp_name,
	 				c.comp_addr,
	 				c.comp_contact,
	 				c.comp_tel,
	 				c.fax 
			FROM 	orders o, order_types ot, employees e, titles t, companies c 
			WHERE 	o.ordtyp_id = ot.ordtyp_id AND o.emp_id = e.emp_id 
					AND e.title_id = t.title_id AND o.comp_id = c.comp_id 
			 		AND o.ord_id = '$ordId' LIMIT 1";
	$result = mysql_query($sql, $dbConn);
	$rows 	= mysql_num_rows($result);
	for($i=0; $i<$rows; $i++) {
		$ordRecord = mysql_fetch_assoc($result);
		// Check ord_snd_date if is null will not convert
		if($ordRecord['ord_snd_date'] == '') {
			$ord_snd_date = '-';
		} else {
			$ord_snd_date = dateThaiFormat($ordRecord['ord_snd_date']);
		}

		$ordData = array(
			'ord_id' 		=> $ordId,
			'ordstat_id' 	=> $ordRecord['ordstat_id'],
			'ordtyp_name' 	=> $ordRecord['ordtyp_name'],
			'emp_fullname' 	=> $ordRecord['emp_fullname'],
			'comp_name' 	=> $ordRecord['comp_name'],
			'comp_addr' 	=> $ordRecord['comp_addr'],
			'comp_contact' 	=> $ordRecord['comp_contact'],
			'comp_tel' 		=> $ordRecord['comp_tel'],
			'comp_email' 	=> $ordRecord['comp_email'],
			'fax' 			=> $ordRecord['fax'],
			'ord_date' 		=> dateThaiFormat($ordRecord['ord_date']),
			'ord_snd_date' 	=> $ord_snd_date
		);
	}
	// Check null
	$ordData['fax'] 			= $ordData['fax'] == '' ? '-' : $ordData['fax'];
	$ordData['comp_contact'] 	= $ordData['comp_contact'] == '' ? '-' : $ordData['comp_contact'];
	$ordData['comp_email'] 		= $ordData['comp_email'] == '' ? '-' : $ordData['comp_email'];

	$smarty->assign('ordData', $ordData);
			
	// Get order details data
	$orddtlData = array();
	$totalPrice = 0;
	$sql = "SELECT 	od.orddtl_id,
	 				p.prd_name,
	 				p.prd_price,
	 				FORMAT(od.orddtl_amount, 0) orddtl_amount,
	 				u.unit_name,
	 				FORMAT(p.prd_price * od.orddtl_amount, 2) sum_price_text,
	 				p.prd_price * od.orddtl_amount sum_price 
			FROM 	order_details od, products p, units u 
			WHERE 	od.prd_id = p.prd_id AND p.unit_id = u.unit_id 
			 		AND od.ord_id = '$ordId'";
	$result = mysql_query($sql, $dbConn);
	$rows 	= mysql_num_rows($result);
	for($i=0; $i<$rows; $i++) {
		$orddtlRecord = mysql_fetch_assoc($result);
		array_push($orddtlData, array(
			'no' 				=> $i+1,
			'orddtl_id' 		=> $orddtl_id,
			'prd_name' 			=> $orddtlRecord['prd_name'],
			'prd_price' 		=> number_format($orddtlRecord['prd_price'], 2),
			'orddtl_amount' 	=> $orddtlRecord['orddtl_amount'],
			'unit_name' 		=> $orddtlRecord['unit_name'],
			'sum_price' 		=> $orddtlRecord['sum_price_text']
		));
		$totalPrice += $orddtlRecord['sum_price'];
	}
	$smarty->assign('orddtlData', $orddtlData);
	$smarty->assign('totalPrice', number_format($totalPrice, 2));
	$smarty->assign('totalPriceText', moneyThaiText(number_format($totalPrice, 2)));

	// add white space record in table productList
	$prdListMaxRows = 16;
	$prdWhiteSpaceRows = $prdListMaxRows - $rows;
	$smarty->assign('prdWhiteSpaceRows', $prdWhiteSpaceRows);
	$smarty->assign('nowDateThai', dateThaiFormatShort(date('Y-m-d')));

	// Get print purchase orders history
	$sql 		= "SELECT COUNT(*) printNum FROM print_purchase_orders WHERE ord_id = '$ordId'";
	$result 	= mysql_query($sql, $dbConn);
	$prtordRow 	= mysql_fetch_assoc($result);
	$printNum 	= $prtordRow['printNum'];
	$smarty->assign('printNum', $printNum);
	if(isset($_REQUEST['hideBackButton']) && $hideBackButton == 'true') {
		$smarty->assign('hideBackButton', true);
	}
}

include('../common/common_footer.php');
?>