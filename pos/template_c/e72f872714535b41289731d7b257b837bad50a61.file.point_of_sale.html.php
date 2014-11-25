<?php /* Smarty version Smarty-3.1.18, created on 2014-11-24 00:07:51
         compiled from "C:\AppServ\www\projectSpa\pos\template\point_of_sale.html" */ ?>
<?php /*%%SmartyHeaderCode:230454686341b9e7a9-38643476%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'e72f872714535b41289731d7b257b837bad50a61' => 
    array (
      0 => 'C:\\AppServ\\www\\projectSpa\\pos\\template\\point_of_sale.html',
      1 => 1416762468,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '230454686341b9e7a9-38643476',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.18',
  'unifunc' => 'content_54686341c0e105_68914625',
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_54686341c0e105_68914625')) {function content_54686341c0e105_68914625($_smarty_tpl) {?><!DOCTYPE html>
<html>
<head>
	<title>Spa - Point of sale</title>
	<meta charset="UTF-8"/>
	<link rel="stylesheet" type="text/css" href="../inc/font-awesome/css/font-awesome.min.css">
	<link rel="stylesheet" type="text/css" href="../css/point_of_sale.css">
	<script type="text/javascript" src="../js/jquery.min.js"></script>
	<script type="text/javascript" src="../js/mbk_common_function.js"></script>
	<script type="text/javascript" src="../js/point_of_sale.js"></script>
</head>
<body>
<div id="header"></div>
<div id="control-sale">
	<div id="sale-transaction">
		<div id="sale-transaction-inner">
			<div id="sale-tranaction-inner-header">
				<div id="total-price-container">
					<span style="float:left">฿</span>
					<span id="total-price">0.00</span>
				</div>
			</div>
			<div id="sale-product-list-container">
				<div id="sale-product-list-header">
					<table>
						<thead>
							<th class="prdName-col">ชื่อสินค้า</th>
							<th class="qty-col">จำนวน</th>
							<th class="unitPrice-col">หน่วยละ</th>
							<th class="sumPrice-col">ราคารวม</th>
						</thead>
					</table>
				</div>
				<div id="sale-product-list-body">
					<form id="formSale" method="post">
						<table id="sale-product-list">
							<tbody>
							</tbody>
						</table>
						<input type="hidden" name="total-price">
					</form>
				</div>
			</div>
			<div id="barcode-input-container">
				<div class="icon-barcode">
					<i class="fa fa-barcode"></i>
				</div>
				<input type="text" id="barcode-input" placeholder="Scan barcode">
			</div>
			<table id="summary-sale-tranaction">
				<tbody>
					<tr>
						<td>รายการสินค้าทั้งหมด:</td>
						<td id="total-product">0</td>
					</tr>
					<tr>
						<td>จำนวนสินค้าทั้งหมด</td>
						<td id="total-qty">0</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="sale-transaction-control"></div>
	</div>
	
</div>
<div id="quick-product-container">
	<div id="quick-product">
		<div id="quick-product-header">
			
		</div>
		<div id="quick-product-body">
			<div id="columns">
			    <!-- and so on ...  -->
			</div>
		</div>
	</div>
</div>
</body>
</html><?php }} ?>
