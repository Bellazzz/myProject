<?php
session_start();
require('check_session.php');
include('../config/config.php');
$tplName = 'report_overtime.html';
$subDir	 = WEB_ROOTDIR.'/backoffice/';

include('../common/common_header.php');

$startDate 		= '';
$endDate 		= '';
$emp_ids = array();

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
	if(isset($_POST['emp_ids'])) {
		$emp_ids = $_POST['emp_ids'];
	}

	// Get work time
	$tmpEmp_ids = wrapSingleQuote($emp_ids);
	$sumOvertime = 0;
	$sumHours = 0;
	$report = array();
	$otr_otstarttime_daywork = array();
	$otr_otendtime_daywork = array();
	$otr_otstarttime_dayoff = array();
	$otr_otendtime_dayoff = array();
	$otRateData = array();
	$otr_bath_per_hour = null;
	$sql = "SELECT 		e.emp_id,
						SUBSTRING(ot.otr_otstarttime_daywork,1,5) otr_otstarttime_daywork,
						SUBSTRING(ot.otr_otendtime_daywork,1,5) otr_otendtime_daywork,
						SUBSTRING(ot.otr_otstarttime_dayoff,1,5) otr_otstarttime_dayoff,
						SUBSTRING(ot.otr_otendtime_dayoff,1,5) otr_otendtime_dayoff,
						ot.otr_bath_per_hour 
			FROM  		employees e,
						positions p,
						overtime_rates ot 
			WHERE 		e.pos_id = p.pos_id AND 
						p.otr_id = ot.otr_id AND  
						e.emp_id IN (".implode(',', $tmpEmp_ids).")";
	$result = mysql_query($sql, $dbConn);
	$rows 	= mysql_num_rows($result);
	if($rows > 0) {
		for($i=0; $i<$rows; $i++) {
			$record = mysql_fetch_assoc($result);
			$otRateData[$record['emp_id']] = array(
				'daywork_starttime' => $record['otr_otstarttime_daywork'],
				'daywork_endtime' 	=> $record['otr_otendtime_daywork'],
				'dayoff_starttime' 	=> $record['otr_otstarttime_dayoff'],
				'dayoff_endtime' 	=> $record['otr_otendtime_dayoff'],
				'bath_per_hour' 	=> $record['otr_bath_per_hour']
			);
		}
	}

	// Find overtime
	if(count($otRateData) > 0) {
		$sql = "SELECT 		e.emp_id,
							CONCAT(e.emp_name, ' ', e.emp_surname) empFullName,
							t.dateatt_in,
							SUBSTRING(t.timeatt_in,1,5) timeatt_in,
							SUBSTRING(t.timeatt_out,1,5) timeatt_out 
				FROM 		time_attendances t 
							JOIN employees e 
							ON t.emp_id = e.emp_id 
				WHERE 		t.emp_id IN (".implode(',', $tmpEmp_ids).") AND 
							t.dateatt_in >= '$startDate' AND 
							t.dateatt_in <= '$endDate' 
				ORDER BY 	empFullName, t.dateatt_in";
		$result = mysql_query($sql, $dbConn);
		$rows 	= mysql_num_rows($result);
		if($rows > 0) {
			for($i=0; $i<$rows; $i++) {
				$record = mysql_fetch_assoc($result);
				$emp_id = $record['emp_id'];
				$otr_otstarttime_daywork = $otRateData[$emp_id]['daywork_starttime'];
				$otr_otendtime_daywork = $otRateData[$emp_id]['daywork_endtime'];
				$otr_otstarttime_dayoff = $otRateData[$emp_id]['dayoff_starttime'];
				$otr_otendtime_dayoff = $otRateData[$emp_id]['dayoff_endtime'];
				$otr_bath_per_hour = $otRateData[$emp_id]['bath_per_hour'];

				$minAttTimeIn = toMin($record['timeatt_in']);
				$minAttTimeOut = toMin($record['timeatt_out']); // 867
				$curDay = getSpaCurrentDay($record['dateatt_in']);

				if($curDay == 'วันหยุดสปา') {
					$minOtrTimeStart = toMin($otr_otstarttime_dayoff); // 480
					$minOtrTimeEnd = toMin($otr_otendtime_dayoff); // 1200
				} else {
					$minOtrTimeStart = toMin($otr_otstarttime_daywork);
					$minOtrTimeEnd = toMin($otr_otendtime_daywork);
				}

				if($minAttTimeOut >= $minOtrTimeStart) {
					if($minAttTimeIn < $minOtrTimeStart)
						$minAttTimeIn = $minOtrTimeStart;
					if($minAttTimeOut > $minOtrTimeEnd)
						$minAttTimeOut = $minOtrTimeEnd;

					$differ = ($minAttTimeOut - $minAttTimeIn) / 60;
					$decimalDiffer = $differ - floor($differ);
					$differ = $decimalDiffer >= 0.5 ? floor($differ)+1 : floor($differ);
					$sumOvertime += $differ * $otr_bath_per_hour;
					$sumHours += $differ;
				}

				// Push to report array
				if($differ > 0) {
					array_push($report, array(
						'emp_id'		=> $record['emp_id'],
						'empFullName'	=> $record['empFullName'],
						'dateatt_in' 	=> dateThaiFormat($record['dateatt_in']),
						'timeatt_in' 	=> $record['timeatt_in'].' น.',
						'timeatt_out' 	=> $record['timeatt_out'].' น.',
						'hours' 		=> $differ,
						'hours_ot'		=> $differ.' ชั่วโมง',
						'ot_rate'		=> $otr_bath_per_hour,
						'ot_bath'		=> $differ * $otr_bath_per_hour,
						'ot_bath_txt'	=> number_format($differ * $otr_bath_per_hour,2)
					));
				}
			} // end for

			$curEmp = '';
			$subTotalHours = 0;
			$subTotalOvertime = 0;
			foreach ($report as $key => $value) {
				// employee header
				if($curEmp != $value['emp_id']) {
					$report[$key]['empHeader'] = true;
				}

				// Cal sub total
				if($key == 0 || $curEmp == $value['emp_id']) {
					$subTotalHours += $value['hours'];
					$subTotalOvertime += $value['ot_bath'];
				} else {
					if(isset($report[$key-1])) {
						$report[$key-1]['subTotalHours'] = $subTotalHours;
						$report[$key-1]['subTotalOvertime'] = number_format($subTotalOvertime,2);
						$subTotalHours = $value['hours'];
						$subTotalOvertime = $value['ot_bath'];
					}
				}

				$curEmp = $value['emp_id']; 
			}

			// Cal sub total last employee
			$report[count($report)-1]['subTotalHours'] = $subTotalHours;
			$report[count($report)-1]['subTotalOvertime'] = number_format($subTotalOvertime,2);

			$smarty->assign('report', $report);
			$smarty->assign('sumOvertime', number_format($sumOvertime,2));
			$smarty->assign('sumHours', number_format($sumHours, 0).' ชั่วโมง');
		} // end if
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

$smarty->assign('emp_ids', $emp_ids);
$smarty->assign('tplName', $tplName);
include('../common/common_footer.php');
?>