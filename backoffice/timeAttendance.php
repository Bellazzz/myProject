<?php
session_start();
//require('check_session.php');

include('../config/config.php');
$tplName = 'timeAttendance.html';
$subDir	 = WEB_ROOTDIR.'/backoffice/';

include('../common/common_header.php');

// Get Advertising data
$advertisingList = array();
$sql = "SELECT  	avs_name,
					avs_img,
					avs_txt 
		FROM 		advertising 
		WHERE 		avs_status = 1 
		ORDER BY 	avs_id DESC";
$result = mysql_query($sql, $dbConn);
$rows 	= mysql_num_rows($result);
if($rows > 0) {
	for($i=0; $i<$rows; $i++) {
		$record = mysql_fetch_assoc($result);
		array_push($advertisingList, array(
			'avs_name' 		=> $record['avs_name'],
			'avs_img' 		=> $record['avs_img'],
			'avs_txt' 		=> $record['avs_txt']
		));
	}
}
$smarty->assign('advertisingList', $advertisingList);

include('../common/common_footer.php');
?>