<?php
include('../config/config.php');
$tplName = 'printServiceReceiptContainer.html';
$subDir	 = WEB_ROOTDIR.'/backoffice/';

include('../common/common_header.php');

$ser_id 	    = $_REQUEST['ser_id'];
$hideBackButton = $_REQUEST['hideBackButton'];
if(hasValue($ser_id)) {
	if(isset($_REQUEST['hideBackButton']) && $hideBackButton == 'true') {
		$smarty->assign('hideBackButton', true);
	}
	$smarty->assign('ser_id', $ser_id);
}

include('../common/common_footer.php');
?>