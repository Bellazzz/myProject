<?php
session_start();
require('check_session.php');
include('../config/config.php');
$tplName = 'manage_table.html';
$subDir	 = WEB_ROOTDIR.'/backoffice/';

include('../common/common_header.php');

include('../common/common_footer.php');
?>