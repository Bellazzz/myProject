<?php
include('../config/config.php');
include('../common/common_header.php');

$prd_id = '';
$qty 	= 1;

if(isset($_POST['prd_id'])) {
	$prd_id = $_POST['prd_id'];
}
if(isset($_POST['qty'])) {
	$qty = $_POST['qty'];
}

$sql = "SELECT 	p.prd_name,
				p.prd_price,
				p.prd_pic,
				pt.prdtyp_name,
				b.brand_name 
		FROM 	products p, brands b, product_types pt 
		WHERE 	p.brand_id = b.brand_id AND 
				p.prdtyp_id = pt.prdtyp_id AND
				p.prd_id = '$prd_id' 
		LIMIT 	1";
$result = mysql_query($sql, $dbConn);
$rows 	= mysql_num_rows($result);
if($rows > 0) {
	$prdRow 	= mysql_fetch_assoc($result);
	$prd_price 	= number_format($prdRow['prd_price'], 2);
?>
<table>
	<tbody>
		<tr>
			<td>
				<img src="../img/products/<?=$prdRow['prd_pic']?>" class="prd_image">
			</td>
			<td style="width: 100%;padding-left: 20px;">
				<h1><?=$prdRow['prd_name']?></h1>
				<span>ราคา <span id="eqp-unitPrice"><?=$prd_price?></span> บาท</span>
			</td>
		</tr>
		<tr>
			<td colspan="2" style="text-align:center;padding-top:20px;">จำนวน</td>
		</tr>
		<tr>
			<td colspan="2" style="position:relative;">
				<button id="eqp-qty-minus-btn" type="button" class="qty-circle-btn minus">
					<i class="fa fa-minus"></i>
				</button>
				<input id="eqp-qty" type="text" value="<?=$qty?>" onkeypress='return event.charCode >= 48 && event.charCode <= 57'>
				<button id="eqp-qty-plus-btn" type="button" class="qty-circle-btn plus">
					<i class="fa fa-plus"></i>
				</button>
			</td>
		</tr>
	</tbody>
</table>
<center>
	<button id="removeSlvDtlBtn" type="button" class="pos-btn white">เอาสินค้านี้ออก</button>
</center>
<?php
}
?>