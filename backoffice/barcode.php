<?php
include('../inc/Barcode39.php'); 
$text	 = $_REQUEST['text'];
$barcode = new Barcode39($text); 
$barcode->draw();
?>