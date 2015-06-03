<?php
session_start();
require('check_session.php');
include('../config/config.php');
$tplName = 'backup.html';
$subDir	 = WEB_ROOTDIR.'/backoffice/';

include('../common/common_header.php');

$optionData = array();
$optionData['drives'] = array();

// Scan drive in computer
$fso = new COM('Scripting.FileSystemObject'); 
$D = $fso->Drives; 
$type = array("Unknown","Removable","Fixed","Network","CD-ROM","RAM Disk"); 
foreach($D as $d ){ 
   $dO = $fso->GetDrive($d); 
   $s = ""; 
   $n = '';
   if($dO->DriveType == 3){ 
       $n = $dO->Sharename; 
   }else if($dO->IsReady){ 
       $n = $dO->VolumeName; 
       $s = file_size($dO->FreeSpace) . " free of " . file_size($dO->TotalSize); 
   }else{ 
       $n = "[Drive not ready]"; 
   } 

   if($n != "[Drive not ready]") {
   	if($type[$dO->DriveType] == 'Fixed') {
   		$driveName = $dO->DriveLetter.": - Local Disk"." (". $s . ")"; 
   	} else if($n != ''){
   		$driveName = $dO->DriveLetter.": - ". $n ." (". $s . ")"; 
   	} else {

   	}
   	
   	array_push($optionData['drives'], array(
		'refText' => $driveName, 
		'refValue' => $dO->DriveLetter.":/")
	);
   }
} 

function file_size($size) { 
	$filesizename = array(" Bytes", " KB", " MB", " GB", " TB", " PB", " EB", " ZB", " YB"); 
	return $size ? round($size/pow(1024, ($i = floor(log($size, 1024)))), 2) . $filesizename[$i] : '0 Bytes'; 
} 

$smarty->assign('optionData', $optionData);

if(isset($_GET['exportResult'])) {
	$smarty->assign('exportResult', true);
}

// Import database
if(isset($_POST['importSubmit'])) {
	$importLog = '';
	$processList = array();
	$allowedExts = array("sql");
	$type = end(explode(".", $_FILES["importFile"]["name"]));
	if(isset($_FILES["importFile"]) && $_FILES['importFile']['size'] != 0){ 
		if(in_array($type, $allowedExts)) {
			// Temporary variable, used to store current query
			$templine = '';
			$errMsg = '';
			// Read in entire file
			$lines = file($_FILES["importFile"]["tmp_name"]);
			// Loop through each line
			foreach ($lines as $line) {
				// Skip it if it's a comment
				if (substr($line, 0, 2) == '--' || $line == '')
				    continue;

				// Add this line to the current segment
				$templine .= $line;
				// If it has a semicolon at the end, it's the end of the query
				if (substr(trim($line), -1, 1) == ';') {
				    // Perform the query
				    if(!mysql_query($templine)) {
				    	$errMsg .= 'Error performing query \'<strong>' . $templine . '\': ' . mysql_error() . '<br /><br />';
				    } else {
				    	######### Log import #########
				    	// Drop database
				    	preg_match("/DROP DATABASE IF EXISTS  [\W]{1}\w*[\W]{1}/", $templine, $matchDropDB);
				    	if(count($matchDropDB) > 0) {
				    		$dbName = str_replace('DROP DATABASE IF EXISTS  ', '', str_replace('`', '', $matchDropDB[0]));
				    		$importLog .= "-> ลบฐานข้อมูล $dbName<br>";
				    	}
				    	// Create database
				    	preg_match("/CREATE DATABASE  [\W]{1}\w*[\W]{1}/", $templine, $matchCreateDB);
				    	if(count($matchCreateDB) > 0) {
				    		$dbName = str_replace('CREATE DATABASE  ', '', str_replace('`', '', $matchCreateDB[0]));
				    		$importLog .= "-> สร้างฐานข้อมูล $dbName<br>";
				    	}
				    	// Create
				    	preg_match("/CREATE TABLE IF NOT EXISTS [\W]{1}\w*[\W]{1}/", $templine, $matchCreateTbl);
				    	if(count($matchCreateTbl) > 0) {
				    		$tableName = str_replace('CREATE TABLE IF NOT EXISTS ', '', str_replace('`', '', $matchCreateTbl[0]));
				    		if(!isset($processList[$tableName]['create'])) {
				    			$processList[$tableName]['create'] = true;
				    		}
				    	}
				    	// Insert
				    	preg_match("/INSERT INTO \w*\s/", $templine, $matchInsert);
				    	if(count($matchInsert) > 0) {
				    		$tableName = str_replace('INSERT INTO ', '', str_replace(' SET', '', $matchInsert[0]));
				    		if(!isset($processList[$tableName]['insert'])) {
				    			$processList[$tableName]['insert'] = 0;
				    		}
				    		$processList[$tableName]['insert']++;
				    	}
				    	// Update
				    	preg_match("/UPDATE\s\w*\sSET/", $templine, $matchUpdate);
				    	if(count($matchUpdate) > 0) {
				    		$tableName = str_replace('UPDATE ', '', str_replace(' SET', '', $matchUpdate[0]));
				    		if(!isset($processList[$tableName]['update'])) {
				    			$processList[$tableName]['update'] = 0;
				    		}
				    		$processList[$tableName]['update']++;
				    	}
				    } 
				    // Reset temp variable to empty
				    $templine = '';
				}
			}
		    if($errMsg == '') {
		    	// Generate import log
		    	foreach ($processList as $tbl => $value) {
		    		if(isset($value['create'])) {
		    			$importLog .= "-> สร้างตาราง $tbl<br>";
		    		}
		    		if(isset($value['insert'])) {
		    			$importLog .= "-> เพิ่มข้อมูลลงตาราง $tbl จำนวน ".number_format($value['insert'])." แถว<br>";
		    		}
		    		if(isset($value['update'])) {
		    			$importLog .= "-> กู้คืนข้อมูลตาราง $tbl จำนวน ".number_format($value['update'])." แถว<br>";
		    		}
		    	}
		    	
		    	$smarty->assign('importResult', 'PASS');
		    	$smarty->assign('importLog', $importLog);
		    } else {
		    	$smarty->assign('importResult', 'ERROR');
		    	$smarty->assign('errMsg', $errMsg);
		    }
		} else {
			$smarty->assign('importResult', 'WRONGTYPE');
		}
	}else{
		$smarty->assign('importResult', 'NOFILE');
	}
}

$smarty->assign('tplName', $tplName);
include('../common/common_footer.php');
?>