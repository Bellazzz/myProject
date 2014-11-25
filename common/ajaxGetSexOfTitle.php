<?php
include('../config/config.php');
include('../common/common_header.php');

$title_id = '';
$response = array();
if(hasValue($_REQUEST['title_id'])) {
	$title_id = $_REQUEST['title_id'];
}

$sql = "SELECT 	IFNULL(s.sex_id, '') sex_id,
				IFNULL(s.sex_name, '') sex_name 
		FROM 	titles t left join sex s 
		ON 		t.sex_id = s.sex_id 
		WHERE 	t.title_id = '$title_id'";
$result = mysql_query($sql, $dbConn);
$row	= mysql_num_rows($result);
if($row > 0) {
	$titleRow = mysql_fetch_assoc($result);
	$response['status'] 	= 'PASS';
	$response['sex_id'] 	= $titleRow['sex_id'];
	$response['sex_name'] 	= $titleRow['sex_name'];
	echo json_encode($response);
} else {
	$response['status'] = 'ไม่พบ title_id นี้';
	echo json_encode($response);
}
?>