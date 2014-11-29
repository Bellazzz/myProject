<?php
session_start();
//require('check_session.php');
include('../config/config.php');
$tplName = 'point_of_sale.html';
$subDir	 = WEB_ROOTDIR.'/pos/';

include('../common/common_header.php');

// Get products data
//$sql = "SELECT "


include('../common/common_footer.php');
?>