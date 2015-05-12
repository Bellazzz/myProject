<?php
session_start();
require('check_session.php');
include('../config/config.php');
$tplName = 'report_overtime.html';
$subDir	 = WEB_ROOTDIR.'/backoffice/';

include('../common/common_header.php');

$startDate 		= '';
$endDate 		= '';
$curEmp_id = '';

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
	if(isset($_POST['curEmp_id'])) {
		$curEmp_id = $_POST['curEmp_id'];
		$smarty->assign('curEmp_id', $curEmp_id);
	}

	// Find employee full name
	$empRecord = new TableSpa('employees', $curEmp_id);
	$smarty->assign('empFullName', $empRecord->getFieldValue('emp_name')." ".$empRecord->getFieldValue('emp_surname'));

	// Get work time
	$sumOvertime = 0;
	$sumHours = 0;
	$report = array();
	$otr_otstarttime_daywork = '';
	$otr_otendtime_daywork = '';
	$otr_otstarttime_dayoff = '';
	$otr_otendtime_dayoff = '';
	$otr_bath_per_hour = null;
	$sql = "SELECT 		SUBSTRING(ot.otr_otstarttime_daywork,1,5) otr_otstarttime_daywork,
						SUBSTRING(ot.otr_otendtime_daywork,1,5) otr_otendtime_daywork,
						SUBSTRING(ot.otr_otstarttime_dayoff,1,5) otr_otstarttime_dayoff,
						SUBSTRING(ot.otr_otendtime_dayoff,1,5) otr_otendtime_dayoff,
						ot.otr_bath_per_hour 
			FROM  		employees e,
						positions p,
						overtime_rates ot 
			WHERE 		e.pos_id = p.pos_id AND 
						p.otr_id = ot.otr_id AND  
						e.emp_id = '$curEmp_id'";
	$result = mysql_query($sql, $dbConn);
	$rows 	= mysql_num_rows($result);
	if($rows > 0) {
		$record = mysql_fetch_assoc($result);
		$otr_otstarttime_daywork 	= $record['otr_otstarttime_daywork'];
		$otr_otendtime_daywork 		= $record['otr_otendtime_daywork'];
		$otr_otstarttime_dayoff 	= $record['otr_otstarttime_dayoff'];
		$otr_otendtime_dayoff 		= $record['otr_otendtime_dayoff'];
		$otr_bath_per_hour 			= $record['otr_bath_per_hour'];

	}

	// Find overtime
	if($otr_bath_per_hour != null) {
		$sql = "SELECT 		dateatt_in,
							SUBSTRING(timeatt_in,1,5) timeatt_in, 
							SUBSTRING(timeatt_out,1,5) timeatt_out 
				FROM 		time_attendances 
				WHERE 		emp_id = '$curEmp_id' AND 
							dateatt_in >= '$startDate'
				AND 		dateatt_in <= '$endDate'";
		$result = mysql_query($sql, $dbConn);
		$rows 	= mysql_num_rows($result);
		if($rows > 0) {
			for($i=0; $i<$rows; $i++) {
				$record = mysql_fetch_assoc($result);
				$minAttTimeOut = toMin($record['timeatt_out']);
				$curDay = getSpaCurrentDay($record['dateatt_in']);

				if($curDay == 'วันหยุดสปา') {
					$minOtrTimeStart = toMin($otr_otstarttime_dayoff);
					$minOtrTimeEnd = toMin($otr_otendtime_dayoff);
				} else {
					$minOtrTimeStart = toMin($otr_otstarttime_daywork);
					$minOtrTimeEnd = toMin($otr_otendtime_daywork);
				}

				if($minAttTimeOut >= $minOtrTimeStart) {
					if($minAttTimeOut > $minOtrTimeEnd)
						$minAttTimeOut = $minOtrTimeEnd;

					$differ = (int)($minAttTimeOut - $minOtrTimeStart) / 60;
					$sumOvertime += $differ * $otr_bath_per_hour;
					$sumHours += $differ;
				}

				// Push to report array
				array_push($report, array(
					'dateatt_in' 	=> dateThaiFormat($record['dateatt_in']),
					'timeatt_in' 	=> $record['timeatt_in'].' น.',
					'timeatt_out' 	=> $record['timeatt_out'].' น.',
					'hours_ot'		=> $differ.' ชั่วโมง',
					'ot_rate'		=> $otr_bath_per_hour,
					'ot_bath'		=> number_format($differ * $otr_bath_per_hour,2) 
				));
			}
			$smarty->assign('report', $report);
			$smarty->assign('sumOvertime', number_format($sumOvertime,2));
			$smarty->assign('sumHours', number_format($sumHours, 0).' ชั่วโมง');
		} // end for
	}
}


// Find employee select reference
$refEmpData = array();
$sql = "SELECT 		emp_id refValue,
					CONCAT(emp_name, ' ', emp_surname) refText 
		FROM 		employees 
		ORDER BY 	refText ASC";
$result = mysql_query($sql, $dbConn);
$rows = mysql_num_rows($result);
if($rows > 0) {
	for($i=0; $i<$rows; $i++) {
		array_push($refEmpData, mysql_fetch_assoc($result));
		$refEmpData[$i]['refField'] = 'emp_id';
	}
}
$smarty->assign('refEmpData', $refEmpData);

function toMin($time) {
	$tmp = split(":", $time);
	$min = (int)($tmp[0]*60) + $tmp[1];
	return $min;
}

$smarty->assign('tplName', $tplName);
include('../common/common_footer.php');
?>