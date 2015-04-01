<?php
session_start();
require('check_session.php');
include('../config/config.php');
$tplName = 'report_top_withdraw.html';
$subDir	 = WEB_ROOTDIR.'/backoffice/';

include('../common/common_header.php');

$chooseDate = '';

if(isset($_POST['submit'])) {
	// Get input data
	if(isset($_POST['chooseDate'])) {
		$chooseDate = $_POST['chooseDate'];
		$smarty->assign('chooseDate', $chooseDate);
		$smarty->assign('chooseDate_th', dateThaiFormat($chooseDate));
	}

	// Query Report
	$report = array();
	$sql = "SELECT a.unit_name, a.prd_name, a.wdwtyp_id, a.wdwtyp_name, a.wdwdtl_amount, a.empFullname, 
			CONCAT( e.emp_name,  ' ', e.emp_surname ) AS empGiveFullname
			FROM (

				SELECT u.unit_name, d.wdw_date, p.prd_id, p.prd_name, wt.wdwtyp_id,  wt.wdwtyp_name, wd.wdwdtl_amount, CONCAT( e.emp_name,  ' ', e.emp_surname ) AS empFullname, d.emp_give_id
				FROM withdraws d, withdraw_details wd, withdraw_types wt, employees e, products p, units u 
				WHERE d.wdw_id = wd.wdw_id
				AND d.wdwtyp_id = wt.wdwtyp_id
				AND wd.prd_id = p.prd_id
				AND d.emp_id = e.emp_id
				AND u.unit_id = p.unit_id 
				)a, employees e
			WHERE a.emp_give_id = e.emp_id AND 
			a.wdw_date = '$chooseDate'";

	$result = mysql_query($sql, $dbConn);
	$rows   = mysql_num_rows($result);
	if($rows > 0) {
	
		$countWdwtyp = array();

		for($i=0; $i<$rows; $i++) {
			$record = mysql_fetch_assoc($result);

			array_push($report, array(
				'no' 				=> number_format($i+1),
				'wdwtyp_name' 		=> $record['wdwtyp_name'],
				'wdwtyp_id'			=> $record['wdwtyp_id'],
				'prd_name'			=> $record['prd_name'],
				'wdwdtl_amount'		=> $record['wdwdtl_amount'],
				'empFullname'		=> $record['empFullname'],
				'empGiveFullname'	=> $record['empGiveFullname'],
				'unit_name'			=> $record['unit_name']
			));

			if(!isset($countWdwtyp[$record['wdwtyp_id']])) {
				$countWdwtyp[$record['wdwtyp_id']] = 0;
			}
			$countWdwtyp[$record['wdwtyp_id']]++;
		}

		$curWdwTypName = '';
		foreach ($report as $key => $value) {
			if($curWdwTypName != $value['wdwtyp_name']) {
				$report[$key]['type'] = $value['wdwtyp_name'];
				$curWdwTypName = $value['wdwtyp_name'];
			}
		}

			
		$smarty->assign('report', $report);
	}
}

// Query Product Type
$wdwtyps = array();
$sql = "SELECT wdwtyp_id, wdwtyp_name FROM withdraw_types";
$result = mysql_query($sql, $dbConn);
$rows   = mysql_num_rows($result);

if($rows > 0) {
	for($i=0; $i<$rows; $i++) {
		$record = mysql_fetch_assoc($result);
		$wdwtyps[$record['wdwtyp_id']]['wdwtyp_id'] 	= $record['wdwtyp_id'];
		$wdwtyps[$record['wdwtyp_id']]['wdwtyp_name'] 	= $record['wdwtyp_name'];
	}
	$smarty->assign('wdwtyps', $wdwtyps);
}

include('../common/common_footer.php');
?>