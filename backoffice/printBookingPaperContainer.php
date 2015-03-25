<?php
include('../config/config.php');
$tplName = 'printBookingPaperContainer.html';
$subDir	 = WEB_ROOTDIR.'/backoffice/';

include('../common/common_header.php');

$bkg_id 	    = $_REQUEST['bkg_id'];
$hideBackButton = $_REQUEST['hideBackButton'];
if(hasValue($bkg_id)) {
	if(isset($_REQUEST['hideBackButton']) && $hideBackButton == 'true') {
		$smarty->assign('hideBackButton', true);
	}
	$smarty->assign('bkg_id', $bkg_id);
}

include('../common/common_footer.php');
?>