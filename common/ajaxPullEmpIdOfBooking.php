<?php
include('../config/config.php');
include('../common/common_header.php');

$response = array();
if(hasValue($_POST['date']) && hasValue($_POST['time']) && hasValue($_POST['timeEnd'])) {
	$date = str_replace("/", "-", $_POST['date']);
	$time = $_POST['time'];
	$timeEnd = $_POST['timeEnd'];

	// Get package sum min
	$pkgMins = array();
	$sql = "SELECT 	p.pkg_id,
					IFNULL(ps.pkgsvl_hr,0) * 60 + IFNULL(ps.pkgsvl_min,0) allMin 
			FROM 	packages p,
					package_service_lists ps 
			WHERE 	p.pkg_id = ps.pkg_id AND 
					p.pkg_start <= '$nowDate' AND 
					(
						p.pkg_stop IS NULL OR 
						p.pkg_stop >= '$nowDate'
					)";
	$result = mysql_query($sql, $dbConn);
	$rows 	= mysql_num_rows($result);
	if($rows > 0) {
		for($i=0; $i<$rows; $i++) {
			$record = mysql_fetch_assoc($result);
			$pkgMins[$record['pkg_id']] = $record['allMin'];
		}
	}

	// Check from packages
	$overlapEmpIds = array();
	$sql = "SELECT 	bp.emp_id,
					bp.pkg_id,
					SUBSTRING(bp.bkgpkg_time,1,5) time 
			FROM 	booking_packages bp,
					booking b 
			WHERE 	bp.bkg_id = b.bkg_id AND 
					b.status_id IN ('S01','S02','S03','S04') AND 
					bp.bkgpkg_status = 0 AND 
					bp.bkgpkg_date = '$date' AND 
					bp.emp_id IS NOT NULL";
	$result = mysql_query($sql, $dbConn);
	$rows 	= mysql_num_rows($result);
	if($rows > 0) {
		for($i=0; $i<$rows; $i++) {
			$record = mysql_fetch_assoc($result);
			$tmpTime = strtotime($record['time']);
			$endTime = date("H:i", strtotime('+' . $pkgMins[$record['pkg_id']] . ' minutes', $tmpTime));
			$curStartTime = (float)str_replace(":", ".", $record['time']);
			$curEndTime = (float)str_replace(":", ".", $endTime);
			$chkStartTime = (float)str_replace(":", ".", $time);
			$chkEndTime = (float)str_replace(":", ".", $timeEnd);

			if(($chkStartTime >= $curStartTime && $chkStartTime < $curEndTime) || 
				($chkEndTime > $curStartTime && $chkEndTime <= $curEndTime)) {
				array_push($overlapEmpIds, $record['emp_id']);
			}
		}
	}

	// Check from service_lists
	$sql = "SELECT 	bs.emp_id,
					bs.svl_id,
					IFNULL(s.svl_hr,0) * 60 + IFNULL(s.svl_min,0) allMin,
					SUBSTRING(bs.bkgsvl_time,1,5) time 
			FROM 	booking_service_lists bs,
					booking b,
					service_lists s 
			WHERE 	bs.bkg_id = b.bkg_id AND 
					bs.svl_id = s.svl_id AND 
					b.status_id IN ('S01','S02','S03','S04') AND 
					bs.bkgsvl_status = 0 AND 
					bs.bkgsvl_date = '$date' AND 
					bs.emp_id IS NOT NULL";echo $sql;
	$result = mysql_query($sql, $dbConn);
	$rows 	= mysql_num_rows($result);
	if($rows > 0) {
		for($i=0; $i<$rows; $i++) {
			$record = mysql_fetch_assoc($result);
			$tmpTime = strtotime($record['time']);
			$endTime = date("H:i", strtotime('+' . $record['allMin'] . ' minutes', $tmpTime));
			$curStartTime = (float)str_replace(":", ".", $record['time']);
			$curEndTime = (float)str_replace(":", ".", $endTime);
			$chkStartTime = (float)str_replace(":", ".", $time);
			$chkEndTime = (float)str_replace(":", ".", $timeEnd);

			if(($chkStartTime >= $curStartTime && $chkStartTime < $curEndTime) || 
				($chkEndTime > $curStartTime && $chkEndTime <= $curEndTime)) {
				array_push($overlapEmpIds, $record['emp_id']);
			}
		}
	}
	print_r($overlapEmpIds);

	
} 
// echo json_encode($response);
echo "heyyyy";
?>