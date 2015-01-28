<?php /* Smarty version Smarty-3.1.18, created on 2015-01-28 22:16:33
         compiled from "C:\AppServ\www\myProject\backoffice\template\timeAttendance.html" */ ?>
<?php /*%%SmartyHeaderCode:306254c8fd51a34991-08809440%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'c327270a5b9682c2de2bfb6891a25c991e4da7a6' => 
    array (
      0 => 'C:\\AppServ\\www\\myProject\\backoffice\\template\\timeAttendance.html',
      1 => 1422452641,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '306254c8fd51a34991-08809440',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'nowDate' => 0,
    'advertisingList' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.18',
  'unifunc' => 'content_54c8fd51aff848_96199173',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_54c8fd51aff848_96199173')) {function content_54c8fd51aff848_96199173($_smarty_tpl) {?><!DOCTYPE html>
<html>
<head>
	<title>Spa - Time Attendance</title>
	<meta charset="UTF-8"/>
	
	<link rel="stylesheet" type="text/css" href="../inc/font-awesome/css/font-awesome.min.css">
	<link rel="stylesheet" type="text/css" href="../css/timeAttendance.css">
	<script type="text/javascript" src="../js/jquery.min.js"></script>
	<script type="text/javascript" src="../js/mbk_common_function.js"></script>
	<script type="text/javascript" src="../inc/datetimepicker/mbk.datetimepickerThai.js"></script>
	<script type="text/javascript" src="../js/timeAttendance.js"></script>
	<script type="text/javascript">
		var nowDate = '<?php echo $_smarty_tpl->tpl_vars['nowDate']->value;?>
';
		var advertisingList = <?php echo json_encode($_smarty_tpl->tpl_vars['advertisingList']->value);?>
;

		$(document).ready(function () {
			$('#nowDate').text(realDateToThaiDate(nowDate));
			StartClock24();
		});

		function showFilled(Value) {
			return (Value > 9) ? "" + Value : "0" + Value;
		}
		function StartClock24() {
			TheTime = new Date;
			var curTime = showFilled(TheTime.getHours()) + ":" +
			showFilled(TheTime.getMinutes());
			// var curTime = showFilled(TheTime.getHours()) + ":" +
			// showFilled(TheTime.getMinutes()) + ":" + showFilled(TheTime.getSeconds());
			$('#clock').text(curTime);
			setTimeout("StartClock24()",1000);
		}
	</script>
	
</head>
<body>
<div class="title-container">
	<h1><span id="clock"></span></h1> 
	<h6><span id="nowDate"></span></h6>
</div>
<div class="container">
	<div class="advertising-container">
		<div class="advertising-image"></div>
		<div class="advertising-txt-container">
			<div class="advertising-txt-inner">
				<div class="advertising-txt"></div>
			</div>
		</div>
	</div>
</div>
<div class="numAds-container">
</div>
<div class="textRunner">
	<marquee class="textRunner-text" scrollamount="4"></marquee>
</div>
<div class="barcode-container">
	<table>
		<tr>
			<td>
				<span class="welcome-msg">ยินดีต้อนรับ กรุณาสแกนบัตรหรือกรอกรหัสบัตรค่ะ</span>
			</td>
			<td style="text-align : left;">
				<input id="barcode-input" class="ta-input" type="text" name="barcode">
				<button class="ta-button ta-button-large button-color-green" id="scan-btn">
					<i class="fa fa-arrow-right"></i>
				</button>
			</td>
		</tr>
	</table>
</div>
</body>
</html><?php }} ?>
