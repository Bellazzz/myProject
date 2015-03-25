<?php
include('../config/config.php');
include('../common/common_header.php');

$response = array();
if(hasValue($_POST['ser_date'])) {
	$ser_date = $_POST['ser_date'];

	$sql = "	SELECT 		b.bkg_id,
							countPkg,
							countSvl 
				FROM 		booking b 
				LEFT JOIN 	(
								SELECT 	bkg_id, COUNT( bkgpkg_id ) AS countPkg 
								FROM 	booking_packages 
								WHERE 	bkgpkg_date =  '$ser_date' 
								GROUP BY bkg_id 
							) bp 
							ON b.bkg_id = bp.bkg_id 
				LEFT JOIN 	(
								SELECT bkg_id, COUNT( bkgsvl_id ) AS countSvl 
								FROM booking_service_lists 
								WHERE bkgsvl_date =  '$ser_date' 
								GROUP BY bkg_id 
							) bs 
							ON b.bkg_id = bs.bkg_id 
				WHERE 		b.status_id IN ('S03',  'S04') AND 
							(
								countPkg IS NOT NULL OR 
								countSvl IS NOT NULL
							)
				ORDER BY 	b.bkg_id DESC";
	$result = mysql_query($sql, $dbConn);
	$rows 	= mysql_num_rows($result);
	if($rows > 0) {
		for($i=0; $i<$rows; $i++) {
			$record = mysql_fetch_assoc($result);
			array_push($response, $record['bkg_id']);
		}
	}
} 
echo json_encode($response);

?>