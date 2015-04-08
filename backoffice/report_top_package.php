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
		$smarty->assign('startDate_th', dateThaiFormat($startDate));
	}
	if(isset($_POST['endDate'])) {
		$endDate = $_POST['endDate'];
		$smarty->assign('endDate', $endDate);
		$smarty->assign('endDate_th', dateThaiFormat($endDate));
	}

	// Query
	$report = array();
	$pkgs = array();
	$sql = "SELECT 		p.pkg_name,
						p.pkg_id,
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

			$report[$record['pkg_id']] = array(
				'no' 		=> number_format($i+1),
				'pkg_name' 	=> $record['pkg_name'],
				'amount'	=> number_format($record['amount']),
				'together'  => array()
			);
			array_push($pkgs, array(
				'id' => $record['pkg_id'],
				'amount' => $record['amount']
			));

			$totalAmount += $record['amount'];
		}
	}

	// Find percent
	if(is_array($pkgs) && count($pkgs) > 0) {
		foreach ($pkgs as $key => $pkg) {
			$pkg_id = $pkg['id'];
			$amount = $pkg['amount'];

			$sql = "SELECT 		b.pkg_id, 
								b.pkg_name, 
								CONCAT( FORMAT( SUM( b.amount ) /$amount *100, 2 ) ,  '%' ) percent
					FROM 		(
								SELECT DISTINCT s.ser_id
								FROM 			services s, 
												service_packages sp
								WHERE 			s.ser_date >=  '$startDate' AND 
												s.ser_date <=  '$endDate' AND 
												s.ser_id = sp.ser_id AND 
												sp.pkg_id =  '$pkg_id'
								) a
								JOIN 
								(
								SELECT 		s.ser_id, 
											p.pkg_id, 
											p.pkg_name, 
											SUM( sp.serpkg_amount ) amount
								FROM 		services s, 
											service_packages sp, 
											packages p 
								WHERE 		s.ser_date >=  '$startDate' AND 
											s.ser_date <=  '$endDate' AND 
											s.ser_id = sp.ser_id AND 
											sp.pkg_id = p.pkg_id AND 
											p.pkg_id !=  '$pkg_id' 
								GROUP BY 	s.ser_id,p.pkg_id
								) b ON a.ser_id = b.ser_id 
					GROUP BY 	b.pkg_id 
					ORDER BY 	SUM( b.amount ) /$amount *100 DESC, b.pkg_name 
					LIMIT 		3";
			$result = mysql_query($sql, $dbConn);
			$rows   = mysql_num_rows($result);
			if($rows > 0) {
				for($i=0; $i<$rows; $i++) {
					$record = mysql_fetch_assoc($result);

					array_push($report[$pkg_id]['together'], array(
						'pkg_name' => $record['pkg_name'],
						'percent' => $record['percent']
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