<?php
include('../config/config.php');
$tplName = 'printReceiptBOF.html';
$subDir	 = WEB_ROOTDIR.'/backoffice/';

include('../common/common_header.php');

$sale_id 	    = $_REQUEST['sale_id'];
$hideBackButton = $_REQUEST['hideBackButton'];
if(hasValue($sale_id)) {
	if(isset($_REQUEST['hideBackButton']) && $hideBackButton == 'true') {
		$smarty->assign('hideBackButton', true);
	}
	$smarty->assign('sale_id', $sale_id);
}

include('../common/common_footer.php');
?>