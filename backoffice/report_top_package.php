<?php
session_start();
require('check_session.php');
include('../config/config.php');
$tplName = 'report_top_package.html';
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
	$sql = "SELECT 		p.pkg_name,
						SUM(sp.serpkg_amount) AS amount 
			FROM 		services s,
						service_packages sp,
						packages p  
			WHERE 		sp.ser_id = s.ser_id AND 	
						sp.pkg_id = p.pkg_id AND 
						s.ser_date >= '$startDate' AND 
						s.ser_date <= '$endDate' 
			GROUP BY 	p.pkg_id 
			ORDER BY 	amount DESC";
	$result = mysql_query($sql, $dbConn);
	$rows   = mysql_num_rows($result);
	if($rows > 0) {
		$totalAmount = 0;

		for($i=0; $i<$rows; $i++) {
			$record = mysql_fetch_assoc($result);

			array_push($report, array(
				'no' 		=> number_format($i+1),
				'pkg_name' 	=> $record['pkg_name'],
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