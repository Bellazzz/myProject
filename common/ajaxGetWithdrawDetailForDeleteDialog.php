<?php
session_start();
include('../config/config.php');
include('../common/common_header.php');

$wdwId = '';
if(hasValue($_POST['wdwId'])) {
	$wdwId = $_POST['wdwId'];
}

$sql = "SELECT 	p.prd_name,
				wd.wdwdtl_amount,
				u.unit_name 
		FROM 	withdraw_details wd,
				products p,
				units u 
		WHERE 	wd.prd_id = p.prd_id AND 
				p.unit_id = u.unit_id AND 
				wdw_id = '$wdwId'";
$result = mysql_query($sql, $dbConn);
$rows	= mysql_num_rows($result);
if($rows > 0) {
	$html = "<br><br>รายการผลิตภัณฑ์ที่เบิก<br><ol>";
	for($i=0; $i<$rows; $i++) {
		$record = mysql_fetch_assoc($result);
		$html .= "<li>".$record['prd_name']."&nbsp;".$record['wdwdtl_amount']."&nbsp;".$record['unit_name']."</li>";
	}
	$html .= "</ol>";

	$response['status'] 	= "PASS";
	$response['wdwdtlHtml'] = $html;
	echo json_encode($response);
} else {
	$response['status'] = "FAIL";
	echo json_encode($response);
}

?>