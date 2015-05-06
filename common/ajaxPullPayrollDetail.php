<?php
include('../config/config.php');
include('../common/common_header.php');

$response = array(
	'emp_salary' => 0,
	'sumCommission' => 0,
	'sumOvertime' => 0
);

if(hasValue($_POST['emp_id']) && hasValue($_POST['payroll_monthly'])) {
	$emp_id = $_POST['emp_id'];
	$payroll_monthly = $_POST['payroll_monthly'];
	$monthly = str_replace('/','-', substr($payroll_monthly, 0, 7));

	// Find employee salary
	$sql = "SELECT 	emp_salary 
			FROM 	employees 
			WHERE 	emp_id = '$emp_id'";
	$result = mysql_query($sql, $dbConn);
	$rows 	= mysql_num_rows($result);
	if($rows > 0) {
		for($i=0; $i<$rows; $i++) {
			$record = mysql_fetch_assoc($result);
			$response['emp_salary'] = $record['emp_salary'];
		}
	}

	// FInd commission package
	$sql = "SELECT 		sum(pkgdtl.pkgdtl_com) sumCom 
			FROM  		package_details pkgdtl,
						service_service_list_times st, 
						service_packages sp, 
						services s 
			WHERE 		pkgdtl.sersvt_id = st.sersvt_id AND 
						st.serpkg_id = sp.serpkg_id AND 
						sp.ser_id = s.ser_id AND 
						ser_date like '$monthly%' AND 
						pkgdtl.emp_id = '$emp_id'";
	$result = mysql_query($sql, $dbConn);
	$rows 	= mysql_num_rows($result);
	if($rows > 0) {
		for($i=0; $i<$rows; $i++) {
			$record = mysql_fetch_assoc($result);
			$response['sumCommission'] += $record['sumCom'];
		}
	}

	// FInd commission service list
	$sql = "SELECT 		sum(sd.svldtl_com) sumCom 
			FROM 		service_list_details sd, 
						service_service_lists ss, 
						services s 
			WHERE 		sd.sersvl_id = ss.sersvl_id AND 
						ss.ser_id = s.ser_id AND 
						ser_date like '$monthly%' AND 
						sd.emp_id = '$emp_id'";
	$result = mysql_query($sql, $dbConn);
	$rows 	= mysql_num_rows($result);
	if($rows > 0) {
		for($i=0; $i<$rows; $i++) {
			$record = mysql_fetch_assoc($result);
			$response['sumCommission'] += $record['sumCom'];
		}
	}

	// Get work time
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
						e.emp_id = '$emp_id'";
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
							timeatt_in,
							SUBSTRING(timeatt_out,1,5) timeatt_out 
				FROM 		time_attendances 
				WHERE 		emp_id = '$emp_id' AND 
							dateatt_in like '$monthly%'";
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
					$response['sumOvertime'] += $differ * $otr_bath_per_hour;
				}
			}
		} // end for
	}

} 
echo json_encode($response);

function toMin($time) {
	$tmp = split(":", $time);
	$min = (int)($tmp[0]*60) + $tmp[1];
	return $min;
}
?>