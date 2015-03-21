<?php
session_start();
require('check_session.php');
include('../config/config.php');
$tplName = 'report_top_service_lists.html';
$subDir	 = WEB_ROOTDIR.'/backoffice/';

include('../common/common_header.php');

$startDate = '';
$endDate = '';

if(isset($_POST['submit'])) {
	// Get input data
	if(isset($_POST['startDate'])) {
		$startDate = $_POST['startDate'];
		$smarty->assign('startDate', $startDate);
		echo $startDate;
		$smarty->assign('startDate_th', dateThaiFormat($startDate));
	}
	if(isset($_POST['endDate'])) {
		$endDate = $_POST['endDate'];
		$smarty->assign('endDate', $endDate);
		$smarty->assign('endDate_th', dateThaiFormat($endDate));
	}

	// Query
	$report = array();
	$sql = "SELECT 		sl.svl_name,
						SUM(ss.sersvl_amount) AS amount 
			FROM 		services s,
						service_lists sl,
						service_service_lists ss 
			WHERE 		ss.ser_id = s.ser_id AND 	
						ss.svl_id = sl.svl_id AND 
						s.ser_date >= '$startDate' AND s.ser_date <= '$endDate' 
			GROUP BY 	sl.svl_id 
			ORDER BY 	amount DESC";
	$result = mysql_query($sql, $dbConn);
	$rows   = mysql_num_rows($result);
	if($rows > 0) {
		$totalAmount = 0;

		for($i=0; $i<$rows; $i++) {
			$record = mysql_fetch_assoc($result);

			array_push($report, array(
				'no' 		=> number_format($i+1),
				'svl_name' 	=> $record['svl_name'],
				'amount'	=> number_format($record['amount'])
			));

			$totalAmount += $record['amount'];
		}
		$smarty->assign('report', $report);
		$smarty->assign('totalAmount', number_format($totalAmount));
	}
}



include('../common/common_footer.php');
?>