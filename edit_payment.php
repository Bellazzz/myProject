<?php
session_start();
include('config/config.php');
$tplName = 'edit_payment.html';
$subDir	 = WEB_ROOTDIR.'/';

include('common/common_header.php');

$cus_id = $_SESSION['cus_id'];
$allowEdit = false;
$bkg_id = '';

if(hasValue($_GET['bkg_id'])) {
	$bkg_id = $_GET['bkg_id'];
}else if (hasValue($_POST['bkg_id'])) {
	$bkg_id = $_POST['bkg_id'];
}


if(!isset($_POST['submit'])) {
	// Query booking data
	$sql = "SELECT 		b.bkg_id,
						b.bkg_date,
						b.bkg_total_price,
						b.bkg_transfer_date,
						DATE_FORMAT(b.bkg_transfer_time,'%H:%i') bkg_transfer_time,
						b.bkg_transfer_evidence,
						b.bkg_transfer_money,
						b.status_id,
						bs.bkgstat_name,
						ba.bnkacc_id,
						ba.bnkacc_name 
			FROM 		booking b 
						JOIN 		booking_status bs 	ON b.status_id = bs.bkgstat_id 
						LEFT JOIN 	bank_accounts ba 	ON b.bnkacc_id = ba.bnkacc_id 
			WHERE 		b.cus_id = '$cus_id' AND 
						b.bkg_id = '$bkg_id' 
			ORDER BY 	b.bkg_date DESC,
						b.bkg_time DESC";
	$result = mysql_query($sql, $dbConn);
	$rows 	= mysql_num_rows($result);
	if($rows > 0) {
		$bkgData = mysql_fetch_assoc($result);
		$bkgData['bkg_date_th'] = dateThaiFormat($bkgData['bkg_date']);
		if($bkgData['bkg_transfer_date'] != '') {
			$bkgData['bkg_transfer_date_th'] = dateThaiFormat($bkgData['bkg_transfer_date']);
		}
		if($bkgData['status_id'] == 'S02') {
			$allowEdit = true;
		}
		$smarty->assign('bkgData', $bkgData);
	}

	// Query bank account data
	$bnkaccList = array();
	$sql = "SELECT 		bnkacc_id,
						bnkacc_name 
			FROM 		bank_accounts 
			ORDER BY 	bnkacc_name";
	$result = mysql_query($sql, $dbConn);
	$rows 	= mysql_num_rows($result);
	if($rows > 0) {
		for($i=0; $i<$rows; $i++) {
			array_push($bnkaccList, mysql_fetch_assoc($result));
		}
		$smarty->assign('bnkaccList', $bnkaccList);
	}

	// Init hr and min
	$times = array();
	$step = 5;
	for($hr=0; $hr<=24; $hr++) {
		$tmpHr = '';
		if($hr < 10) {
			$tmpHr .= '0';
		}
		$tmpHr .= $hr;
		for($min=0; $min<=59; $min+=$step) {
			$time = $tmpHr.":";
			if($min < 10) {
				$time .= '0';
			}
			$time .= $min;
			array_push($times, $time);
		}
	}

	$smarty->assign('times', $times);
} else {
	### Edit payment data
	$editError = false;
	$errTxt = '';
	$bnkacc_id 			= $_POST['bnkacc_id'];
	$bkg_transfer_money = $_POST['bkg_transfer_money'];
	$bkg_transfer_date 	= getRealDate($_POST['bkg_transfer_date']);
	$bkg_transfer_time 	= $_POST['bkg_transfer_time'];
	$bkg_transfer_evidence = '';
	$bkgRecord = new TableSpa('booking', $bkg_id);

	// Rename Image
	if(strpos($_POST['bkg_transfer_evidence'], 'temp_') !== FALSE) {
		$type		= str_replace(".", "", strrchr($_POST['bkg_transfer_evidence'],"."));
		$bkg_transfer_evidence = $bkg_id.".$type";
		$bkg_transfer_evidence = str_replace('/', '_', $bkg_transfer_evidence);
		$imgTmpPath = 'img/temp/'.$_POST['bkg_transfer_evidence'];
		$imgNewPath = 'img/booking/'.$bkg_transfer_evidence;

		// Delete Old Image
		if(file_exists($imgNewPath)) {
			if(!unlink($imgNewPath)) {
				$editError = true;
				$errTxt .= 'DELETE_OLD_IMG_FAIL<br>';
			}
		}
		// Rename temp to new image
		if(file_exists($imgTmpPath)) {
			if(rename($imgTmpPath, $imgNewPath)) {
				$bkg_transfer_evidence = $bkg_transfer_evidence;
			} else {
				$editError = true;
				$errTxt .= 'RENAME_FAIL<br>';
			}
		}
		$bkgRecord->setFieldValue('bkg_transfer_evidence', $bkg_transfer_evidence);
	}

	// Update booking data
	$bkgRecord->setFieldValue('bnkacc_id', $bnkacc_id);
	$bkgRecord->setFieldValue('bkg_transfer_money', $bkg_transfer_money);
	$bkgRecord->setFieldValue('bkg_transfer_date', $bkg_transfer_date);
	$bkgRecord->setFieldValue('bkg_transfer_time', $bkg_transfer_time);
	
	if(!$bkgRecord->commit()) {
		$editError = true;
		$errTxt .= 'UPDATE_BOOKING_FAIL<br>'.$bkg_transfer_evidence;
		$errTxt .= mysql_error($dbConn)."<br><br>";
	}

	if($editError) {
		$smarty->assign('editError', true);
		$smarty->assign('errTxt', $errTxt);
	} else {
		redirect("booking_detail.php?bkg_id=$bkg_id");
	}
}




$smarty->assign('tplName', $tplName);
include('common/common_footer.php');
?>

