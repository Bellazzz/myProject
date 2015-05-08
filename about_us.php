<?php
session_start();
include('config/config.php');
$tplName = 'about_us.html';
$subDir	 = WEB_ROOTDIR.'/';

include('common/common_header.php');

//Get data to show
$spaRecord 	= new TableSpa('spa', 'SA01');
$spaData 	= array(
	'spa_name' 	=> $spaRecord->getFieldValue('spa_name'),
	'spa_addr' 	=> $spaRecord->getFieldValue('spa_addr'),
	'spa_tel' 	=> $spaRecord->getFieldValue('spa_tel'),
	'spa_fax' 	=> $spaRecord->getFieldValue('spa_fax'),
	'spa_email' => $spaRecord->getFieldValue('spa_email')
);
// Check null
$spaData['spa_fax'] 	= $spaData['spa_fax'] == '' ? '-' : $spaData['spa_fax'];
$spaData['spa_email'] 	= $spaData['spa_email'] == '' ? '-' : $spaData['spa_email'];

$smarty->assign('spaData', $spaData);

$smarty->assign('tplName', $tplName);
include('common/common_footer.php');
?>

