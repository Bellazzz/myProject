<?php
session_start();
include('config/config.php');
$tplName = 'updateAccount.html';
$subDir	 = WEB_ROOTDIR.'/';

include('common/common_header.php');

$sexList = array();
$sql = "SELECT 		sex_id,
					sex_name  
		FROM 		sex 
		ORDER BY 	sex_id ASC";
$result = mysql_query($sql, $dbConn);
$rows 	= mysql_num_rows($result);
if($rows > 0) {
	for($i=0; $i<$rows; $i++) {
		$record = mysql_fetch_assoc($result);
		$sexList[$record['sex_id']] = $record;
	}
}

$titleList = array();
$sql = "SELECT 		t.title_id,
					t.title_name,
					IFNULL(s.sex_id,'') sex_id,
					IFNULL(s.sex_name,'') sex_name 
		FROM 		titles t 
		LEFT JOIN 	sex s 
		ON          t.sex_id = s.sex_id 
		ORDER BY 	t.title_id ASC";
$result = mysql_query($sql, $dbConn);
$rows 	= mysql_num_rows($result);
if($rows > 0) {
	for($i=0; $i<$rows; $i++) {
		$record = mysql_fetch_assoc($result);
		$titleList[$record['title_id']] = $record;
	}
}

// Get customer data
$tableInfo = getTableInfo("customers");
$cusRecord = new TableSpa("customers", $_SESSION["cus_id"]);
$cusData = array();
foreach ($tableInfo['fieldNameList'] as $field => $value) {
	$cusData[$field] = $cusRecord->getFieldValue($field);
}
$smarty->assign('cusData', $cusData);
$smarty->assign('sexList', $sexList);
$smarty->assign('titleList', $titleList);

$smarty->assign('tplName', $tplName);
include('common/common_footer.php');
?>

