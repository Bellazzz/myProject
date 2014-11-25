<?
session_start();
include('../config/config.php');
include('../common/common_header.php');

$ord_id = '';

if(isset($_REQUEST['ord_id'])) {
	$ord_id = $_REQUEST['ord_id'];
}

date_default_timezone_set('Asia/Bangkok');
$printTime 		= date("H:i:s");
$printDate 		= date("Y-m-d");
$values 		= array($ord_id, $session_emp_id, $printDate, $printTime);

// Insert
$prtordRecord = new TableSpa('print_purchase_orders', $values);
if($prtordRecord->insertSuccess()) {
	echo "PASS";
} else {
	echo "INSERT_PRINT_PURCHASE_ORDERS_FAIL";
}
?>