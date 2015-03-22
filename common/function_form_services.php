<?php
function updateBookingStatus($bkg_id) {
	global $dbConn;
	$returnVal = true;
	$status_id = '';
	$totalBkgPersons = 0;
	$totalRemainPersons = 0;

	$bkgPkgIds_wait = array();
	$bkgPkgIds_remain = array();
	$bkgPkgIds_complete = array();

	$bkgSvlIds_wait = array();
	$bkgSvlIds_remain = array();
	$bkgSvlIds_complete = array();

	###### Query and Prepare variable
	// Packages
	$sql = "SELECT 		a.bkgpkg_id,
						bkgpkg_persons,
						IFNULL( bkgpkg_persons - sumAmount, bkgpkg_persons ) AS remainAmount 
			FROM (
					SELECT 	pkg_id, bkgpkg_id, bkgpkg_persons
					FROM 	booking_packages
					WHERE 	bkg_id =  '$bkg_id'
				) a
				LEFT JOIN 
				(
					SELECT 	pkg_id, SUM( serpkg_amount ) AS sumAmount
					FROM 	service_packages sp, services s
					WHERE 	sp.ser_id = s.ser_id AND s.bkg_id = '$bkg_id' 
					GROUP BY pkg_id
				) b 
				ON a.pkg_id = b.pkg_id";
	$result = mysql_query($sql, $dbConn);
	$rows 	= mysql_num_rows($result);
	if($rows > 0) {
		for($i=0; $i<$rows; $i++) {
			$record = mysql_fetch_assoc($result);
			$bkgpkg_id = $record['bkgpkg_id'];

			if($record['remainAmount'] == 0) {
				// complete
				array_push($bkgPkgIds_complete, $bkgpkg_id);
			} else if($record['bkgpkg_persons'] == $record['remainAmount']) {
				// wait
				array_push($bkgPkgIds_wait, $bkgpkg_id);
			} else {
				// remain
				array_push($bkgPkgIds_remain, $bkgpkg_id);
			}

			$totalBkgPersons += $record['bkgpkg_persons'];
			$totalRemainPersons += $record['remainAmount'];
		}
	}

	// Service lists
	$sql = "SELECT 		a.bkgsvl_id,
						bkgsvl_persons,
						IFNULL( bkgsvl_persons - sumAmount, bkgsvl_persons ) AS remainAmount 
			FROM (
					SELECT 	svl_id, bkgsvl_id, bkgsvl_persons
					FROM 	booking_service_lists
					WHERE 	bkg_id =  '$bkg_id'
				) a
				LEFT JOIN 
				(
					SELECT 	svl_id, SUM( sersvl_amount ) AS sumAmount
					FROM 	service_service_lists ss, services s
					WHERE 	ss.ser_id = s.ser_id AND s.bkg_id = '$bkg_id' 
					GROUP BY svl_id
				) b 
				ON a.svl_id = b.svl_id";
	$result = mysql_query($sql, $dbConn);
	$rows 	= mysql_num_rows($result);
	if($rows > 0) {
		for($i=0; $i<$rows; $i++) {
			$record = mysql_fetch_assoc($result);
			$bkgsvl_id = $record['bkgsvl_id'];
			if($record['remainAmount'] == 0) {
				// complete
				array_push($bkgSvlIds_complete, $bkgsvl_id);
			} else if($record['bkgsvl_persons'] == $record['remainAmount']) {
				// wait
				array_push($bkgSvlIds_wait, $bkgsvl_id);
			} else {
				// remain
				array_push($bkgSvlIds_remain, $bkgsvl_id);
			}

			$totalBkgPersons += $record['bkgsvl_persons'];
			$totalRemainPersons += $record['remainAmount'];
		}
	}


	###### Update booking packages status
	if(count($bkgPkgIds_wait) > 0) {
		$bkgPkgIds_wait = wrapSingleQuote($bkgPkgIds_wait);
		$sql = "UPDATE 		booking_packages 
				SET 		bkgpkg_status = 0 
				WHERE 		bkgpkg_id IN(".implode(',', $bkgPkgIds_wait).")";
		$result = mysql_query($sql, $dbConn);
		if(!$result) {
			// update fail
			$returnVal = false;
		}
	}
	if(count($bkgPkgIds_remain) > 0) {
		$bkgPkgIds_remain = wrapSingleQuote($bkgPkgIds_remain);
		$sql = "UPDATE 		booking_packages 
				SET 		bkgpkg_status = 1 
				WHERE 		bkgpkg_id IN(".implode(',', $bkgPkgIds_remain).")";
		$result = mysql_query($sql, $dbConn);
		if(!$result) {
			// update fail
			$returnVal = false;
		}
	}
	if(count($bkgPkgIds_complete) > 0) {
		$bkgPkgIds_complete = wrapSingleQuote($bkgPkgIds_complete);
		$sql = "UPDATE 		booking_packages 
				SET 		bkgpkg_status = 2 
				WHERE 		bkgpkg_id IN(".implode(',', $bkgPkgIds_complete).")";
		$result = mysql_query($sql, $dbConn);
		if(!$result) {
			// update fail
			$returnVal = false;
		}
	}

	###### Update booking service_lists status
	if(count($bkgSvlIds_wait) > 0) {
		$bkgSvlIds_wait = wrapSingleQuote($bkgSvlIds_wait);
		$sql = "UPDATE 		booking_service_lists 
				SET 		bkgsvl_status = 0 
				WHERE 		bkgsvl_id IN(".implode(',', $bkgSvlIds_wait).")";
		$result = mysql_query($sql, $dbConn);
		if(!$result) {
			// update fail
			$returnVal = false;
		}
	}
	if(count($bkgSvlIds_remain) > 0) {
		$bkgSvlIds_remain = wrapSingleQuote($bkgSvlIds_remain);
		$sql = "UPDATE 		booking_service_lists 
				SET 		bkgsvl_status = 1 
				WHERE 		bkgsvl_id IN(".implode(',', $bkgSvlIds_remain).")";
		$result = mysql_query($sql, $dbConn);
		if(!$result) {
			// update fail
			$returnVal = false;
		}
	}
	if(count($bkgSvlIds_complete) > 0) {
		$bkgSvlIds_complete = wrapSingleQuote($bkgSvlIds_complete);
		$sql = "UPDATE 		booking_service_lists 
				SET 		bkgsvl_status = 2 
				WHERE 		bkgsvl_id IN(".implode(',', $bkgSvlIds_complete).")";
		$result = mysql_query($sql, $dbConn);
		if(!$result) {
			// update fail
			$returnVal = false;
		}
	}

	###### Update booking status
	if($totalRemainPersons == 0) {
		// COMPLETED
		$status_id = 'S05';
	} else if($totalBkgPersons == $totalRemainPersons) {
		// PENDING_SERVICE
		$status_id = 'S03';
	} else {
		// REMAIN_SERVICE
		$status_id = 'S04';
	}

	$sql = "UPDATE 		booking 
			SET 		status_id = '$status_id' 
			WHERE 		bkg_id = '$bkg_id'";
	$result = mysql_query($sql, $dbConn);
	if(!$result) {
		// update fail
		$returnVal = false;
	}

	return $returnVal;
}
?>