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
		$smarty->assign('startDate_th', dateThaiFormat($startDate));
	}
	if(isset($_POST['endDate'])) {
		$endDate = $_POST['endDate'];
		$smarty->assign('endDate', $endDate);
		$smarty->assign('endDate_th', dateThaiFormat($endDate));
	}

	// Query
	$report = array();
	$svls = array();
	$sql = "SELECT 		sl.svl_name,
						sl.svl_id,
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

			$report[$record['svl_id']] = array(
				'no' 		=> number_format($i+1),
				'svl_id' 	=> $record['svl_id'],
				'svl_name' 	=> $record['svl_name'],
				'amount'	=> number_format($record['amount']),
				'together' 	=> array()
			);
			array_push($svls, array(
				'id' => $record['svl_id'],
				'amount' => $record['amount']
			));

			$totalAmount += $record['amount'];
		}
		
	}

	// Find percent
	if(is_array($svls) && count($svls) > 0) {
		foreach ($svls as $key => $svl) {
			$svl_id = $svl['id'];
			$amount = $svl['amount'];

			$sql = "SELECT 		b.svl_id, 
								b.svl_name, 
								CONCAT( FORMAT( SUM( b.amount ) /$amount *100, 2 ) ,  '%' ) percent
					FROM 		(
								SELECT DISTINCT s.ser_id
								FROM 			services s, 
												service_service_lists sl
								WHERE 			s.ser_date >=  '2015-04-09' AND 
												s.ser_date <=  '2015-04-09' AND 
												s.ser_id = sl.ser_id AND 
												sl.svl_id =  '$svl_id'
								) a
								JOIN 
								(
								SELECT 		s.ser_id, 
											sl.svl_id, 
											sl.svl_name, 
											SUM( ss.sersvl_amount ) amount
								FROM 		services s, 
											service_service_lists ss, 
											service_lists sl
								WHERE 		s.ser_date >=  '$startDate' AND 
											s.ser_date <=  '$endDate' AND 
											s.ser_id = ss.ser_id AND 
											ss.svl_id = sl.svl_id AND 
											sl.svl_id !=  '$svl_id' 
								GROUP BY 	s.ser_id,sl.svl_id
								) b ON a.ser_id = b.ser_id 
					GROUP BY 	b.svl_id 
					ORDER BY 	SUM( b.amount ) /$amount *100 DESC, b.svl_name 
					LIMIT 		3";
			$result = mysql_query($sql, $dbConn);
			$rows   = mysql_num_rows($result);
			if($rows > 0) {
				for($i=0; $i<$rows; $i++) {
					$record = mysql_fetch_assoc($result);

					array_push($report[$svl_id]['together'], array(
						'svl_name' => $record['svl_name'],
						'percent' => $record['percent'],
					));
				}
			}
		}
		$smarty->assign('report', $report);
		$smarty->assign('totalAmount', number_format($totalAmount));
	}
}



include('../common/common_footer.php');
?>