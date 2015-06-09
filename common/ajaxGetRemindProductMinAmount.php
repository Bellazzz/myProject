<?php
include('../config/config.php');
include('../common/common_header.php');

$sql = "SELECT 		p.prd_id,
					p.prd_name,
					u.unit_name,
					p.prd_amount,
					p.prd_amount_min - p.prd_amount AS getMoreAmount 
		FROM 		products p, 
					units u 
		WHERE 		p.unit_id = u.unit_id AND 
					p.prd_amount IS NOT NULL AND 
					p.prd_amount <= p.prd_amount_min 
		ORDER BY 	p.prd_name";
$result 	= mysql_query($sql, $dbConn);
$rows 		= mysql_num_rows($result);
$response 	= array();
if($rows > 0) {
	$outOfStockListHtml = '<ol>';

	for($i=0; $i<$rows; $i++) {
		$tmpRecord = mysql_fetch_assoc($result);
		$outOfStockListHtml  .= '<li>'.$tmpRecord['prd_name'].' คงเหลือ '
						.number_format($tmpRecord['prd_amount']).' '
						.$tmpRecord['unit_name'].'</li>';
		$outOfStockListHtml .= '<input type="hidden" name="outOfStockPrdIds[]" value="'.$tmpRecord['prd_id'].'">';
		$outOfStockListHtml .= '<input type="hidden" name="getMoreAmounts[]" value="'.$tmpRecord['getMoreAmount'].'">';
	}
	
	$outOfStockListHtml .= '</ol>';

	$response['status'] 				= 'OUT_OF_STOCK';
	$response['outOfStockListHtml'] 	= $outOfStockListHtml;
	echo json_encode($response);
} else {
	$response['status'] = 'STOCK_OK';
	echo json_encode($response);
}
?>