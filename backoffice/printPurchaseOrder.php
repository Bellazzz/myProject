<?php
include('../config/config.php');
$tplName = 'printPurchaseOrder.html';
$subDir	 = WEB_ROOTDIR.'/backoffice/';

include('../common/common_header.php');

function moneyThaiText($number){ 
$txtnum1 = array('ศูนย์','หนึ่ง','สอง','สาม','สี่','ห้า','หก','เจ็ด','แปด','เก้า','สิบ'); 
$txtnum2 = array('','สิบ','ร้อย','พัน','หมื่น','แสน','ล้าน','สิบ','ร้อย','พัน','หมื่น','แสน','ล้าน'); 
$number = str_replace(",","",$number); 
$number = str_replace(" ","",$number); 
$number = str_replace("บาท","",$number); 
$number = explode(".",$number); 
if(sizeof($number)>2){ 
return 'ทศนิยมหลายตัว'; 
exit; 
} 
$strlen = strlen($number[0]); 
$convert = ''; 
for($i=0;$i<$strlen;$i++){ 
	$n = substr($number[0], $i,1); 
	if($n!=0){ 
		if($i==($strlen-1) AND $n==1){ $convert .= 'เอ็ด'; } 
		elseif($i==($strlen-2) AND $n==2){  $convert .= 'ยี่'; } 
		elseif($i==($strlen-2) AND $n==1){ $convert .= ''; } 
		else{ $convert .= $txtnum1[$n]; } 
		$convert .= $txtnum2[$strlen-$i-1]; 
	} 
} 

$convert .= 'บาท'; 
if($number[1]=='0' OR $number[1]=='00' OR 
$number[1]==''){ 
$convert .= 'ถ้วน'; 
}else{ 
$strlen = strlen($number[1]); 
for($i=0;$i<$strlen;$i++){ 
$n = substr($number[1], $i,1); 
	if($n!=0){ 
	if($i==($strlen-1) AND $n==1){$convert 
	.= 'เอ็ด';} 
	elseif($i==($strlen-2) AND 
	$n==2){$convert .= 'ยี่';} 
	elseif($i==($strlen-2) AND 
	$n==1){$convert .= '';} 
	else{ $convert .= $txtnum1[$n];} 
	$convert .= $txtnum2[$strlen-$i-1]; 
	} 
} 
$convert .= 'สตางค์'; 
} 
return $convert; 
} 
###### End initial ######


$ordId = $_REQUEST['ordId'];

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
}

include('../common/common_footer.php');
?>