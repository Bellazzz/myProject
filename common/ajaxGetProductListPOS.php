<?php
include('../config/config.php');
include('../common/common_header.php');

$where 		= '';
$like 		= '';
$filterTyp	= '';
$order 		= " ORDER BY prd_name ASC";

if(hasValue($_POST['searchText'])) {
	$where = " WHERE prd_name LIKE '%".$_POST['searchText']."%' ";
}
if(hasValue($_POST['prdtyp_id'])) {
	if(hasValue($where)) {
		$where .= " AND prdtyp_id = '".$_POST['prdtyp_id']."'";
	} else {
		$where .= " WHERE prdtyp_id = '".$_POST['prdtyp_id']."'";
	}
}

$sql = "SELECT 	prd_id,
				prd_name,
				prd_price,
				prd_pic  
		FROM 	products 
		$where 
		$order";
$result = mysql_query($sql, $dbConn);
$rows	= mysql_num_rows($result);
if($rows > 0) {
	for($i=0; $i<$rows; $i++) {
		$prdRow = mysql_fetch_assoc($result);
		?>
		<div class="pin-container">
	    	<div class="pin" prd-id="<?=$prdRow['prd_id']?>" prd-price="<?=$prdRow['prd_price']?>">
	    		<div class="prd-image-container">
	    			<div class="prd-image" style="background-image:url('../img/products/<?=$prdRow['prd_pic']?>');"></div>
	    		</div>
	    		<div class="prd-name-container">
	    			<p><?=$prdRow['prd_name']?></p>
	    		</div>
	    	</div>
	    </div>
		<?
	}
} else {

}
?>