<?php /* Smarty version Smarty-3.1.18, created on 2015-01-28 22:51:09
         compiled from "C:\AppServ\www\myProject\backoffice\template\form_product_promotions.html" */ ?>
<?php /*%%SmartyHeaderCode:2270754c9056dac99c9-41408845%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '154bb13ad6180d21361021df2d1ff3a2796b2d5b' => 
    array (
      0 => 'C:\\AppServ\\www\\myProject\\backoffice\\template\\form_product_promotions.html',
      1 => 1418383651,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '2270754c9056dac99c9-41408845',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'action' => 0,
    'tableName' => 0,
    'tableNameTH' => 0,
    'code' => 0,
    'values' => 0,
    'referenceData' => 0,
    'randNum' => 0,
    'nowDate' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.18',
  'unifunc' => 'content_54c9056df02c99_16599733',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_54c9056df02c99_16599733')) {function content_54c9056df02c99_16599733($_smarty_tpl) {?><!DOCTYPE html>
<html lang="th">
<head>
	<title>Spa - Backoffice</title>
	<meta charset="UTF-8"/>
    
	<link rel="stylesheet" type="text/css" href="../inc/font-awesome/css/font-awesome.min.css">
	<link rel="stylesheet" type="text/css" href="../css/lazybingo.css">
	<link rel="stylesheet" type="text/css" href="../inc/datetimepicker/jquery.datetimepicker.css">
    <script type="text/javascript" src="../js/jquery.min.js"></script>
	<script type="text/javascript" src="../inc/datetimepicker/jquery.datetimepicker.js"></script>
	<script type="text/javascript" src="../inc/datetimepicker/mbk.datetimepickerThai.js"></script>
    <script type="text/javascript" src="../js/mbk_common_function.js"></script>
    <script type="text/javascript" src="../js/mbk_main.js"></script>
    <script type="text/javascript" src="../js/mbk_form_table.js"></script>
    <script type="text/javascript">
        // Global variables
        var action      = '<?php echo $_smarty_tpl->tpl_vars['action']->value;?>
';
        var tableName   = '<?php echo $_smarty_tpl->tpl_vars['tableName']->value;?>
';
		var tableNameTH = '<?php echo $_smarty_tpl->tpl_vars['tableNameTH']->value;?>
';
        var code        = '<?php echo $_smarty_tpl->tpl_vars['code']->value;?>
';
        var ajaxUrl     = 'form_product_promotions.php';

		$(document).ready(function() {
            // Default value of radio input
            
            <?php if ($_smarty_tpl->tpl_vars['values']->value['prdprm_type']=='ลดราคา') {?>
            $('#prdprm_type_sale').prop('checked', true);
            <?php } elseif ($_smarty_tpl->tpl_vars['values']->value['prdprm_type']=='ฟรี') {?>
            $('#prdprm_type_free').prop('checked', true);
            <?php }?>
            

            selectReferenceJS({
                elem            : $('#prdprmgrp_id'),
                data            : <?php echo json_encode($_smarty_tpl->tpl_vars['referenceData']->value["product_promotion_groups"]);?>
,
                searchTool      : true,
                defaultValue    : '<?php echo $_smarty_tpl->tpl_vars['values']->value['prdprmgrp_id'];?>
'
            });
			 
			uploadImageInput({
				area: $('#prdprm_picture'),
				input: $('input[name="prdprm_picture"]'),
				selector: $('#prdprm_picture_file'),
				defaultValue: '<?php if ($_smarty_tpl->tpl_vars['values']->value['prdprm_picture']) {?>../img/product_promotions/<?php echo $_smarty_tpl->tpl_vars['values']->value['prdprm_picture'];?>
?rand=<?php echo $_smarty_tpl->tpl_vars['randNum']->value;?>
<?php }?>'
			});

			$('#prdprm_startdate').datetimepicker({
                lang                : 'th',
                format              : 'Y/m/d',
                timepicker          :false,
                closeOnDateSelect   :true,
                scrollInput         :false,
                yearOffset          :543,
                onSelectDate: 
                function(){
                  $('#prdprm_startdate').blur();
                },
                onShow:function( ct ){
                    if(action == 'ADD') {
                    	this.setOptions({
	                        minDate: realDateToTmpDate('<?php echo $_smarty_tpl->tpl_vars['nowDate']->value;?>
'),
	                        maxDate:$('#prdprm_enddate').val()?unconvertThaiDate($('#prdprm_enddate').val()):false
	                    });
                    } else if(action == 'EDIT') {
                    	this.setOptions({
	                        maxDate:$('#prdprm_enddate').val()?unconvertThaiDate($('#prdprm_enddate').val()):false
	                    });
                    }
                },
                timepicker:false
            });

			$('#prdprm_enddate').datetimepicker({
                lang                : 'th',
                format              : 'Y/m/d',
                timepicker          :false,
                closeOnDateSelect   :true,
                scrollInput         :false,
                yearOffset          :543,
                onSelectDate: 
                function(){
                  $('#prdprm_enddate').blur();
                },
                onShow:function( ct ){
                    if(action == 'ADD') {
                    	this.setOptions({
	                        minDate:$('#prdprm_startdate').val()?unconvertThaiDate($('#prdprm_startdate').val()):realDateToTmpDate('<?php echo $_smarty_tpl->tpl_vars['nowDate']->value;?>
')
	                    });
                    } else if(action="EDIT") {
                    	this.setOptions({
	                        minDate:$('#prdprm_startdate').val()?unconvertThaiDate($('#prdprm_startdate').val()):false
	                    });
                    }
                },
                timepicker:false
            });

            // Check date
            $('#prdprm_startdate').change(function(){
                checkDate($(this), $('#prdprm_enddate'));
            });
            $('#prdprm_enddate').change(function() {
                checkDate($(this), $('#prdprm_startdate'));
            });
		});

		function checkDate(self, sibling) {
            // Skip check
            if(self.val() == '' || isDateThaiFormat(self.val())) {
                return;
            }

            var selfDate        = new Date(self.val());
            var siblingDate     = new Date(unconvertThaiDate(sibling.val()));
            var nowDate         = new Date(realDateToTmpDate('<?php echo $_smarty_tpl->tpl_vars['nowDate']->value;?>
'));
            var selfId          = self.attr('id');

            // Check sibling
            if(sibling.val() != '') {
                if(selfId == 'prdprm_startdate') {
                    if(selfDate.getTime() > siblingDate.getTime()) {
                        showAlertInvalidDate(self, 'overSibling');
                        return;
                    }
                } else if(selfId == 'prdprm_enddate') {
                    if(selfDate.getTime() < siblingDate.getTime()) {
                        showAlertInvalidDate(self, 'overSibling');
                        return;
                    }
                }
            }

            if(action == 'ADD') {
            	// Check now date
                if(selfId == 'prdprm_startdate') {
                    if(selfDate.getTime() < nowDate.getTime()) {
                        showAlertInvalidDate(self, 'lessThanNow')
                    }
                } else if(selfId == 'prdprm_enddate') {
                    if(selfDate.getTime() < nowDate.getTime()) {
                        showAlertInvalidDate(self, 'lessThanNow')
                    }
                }
            }
        }
        function showAlertInvalidDate(self, errType) {
            var titleTxt    = '';
            var messageTxt  = '';
            var descTxt     = '';
            var selfId      = self.attr('id');

            if(selfId == 'prdprm_startdate') {
                titleTxt    = 'วันที่เริ่มใช้ไม่ถูกต้อง';
                descTxt     = 'ป้อนวันที่เริ่มใช้ใหม่';
                if(errType == 'overSibling') {
                    messageTxt  = 'วันที่เริ่มใช้ไม่สามารถอยู่หลังวันที่สิ้นสุดได้ค่ะ';
                } else if(errType == 'lessThanNow') {
                    messageTxt  = 'ไม่สามารถป้อนวันที่เริ่มใช้ย้อนหลังได้ค่ะ';
                }
            } else if(selfId == 'prdprm_enddate') {
                titleTxt    = 'วันที่สิ้นสุดไม่ถูกต้อง';
                descTxt     = 'ป้อนวันที่สิ้นสุดใหม่';
                if(errType == 'overSibling') {
                    messageTxt  = 'วันที่สิ้นสุดไม่สามารถอยู่ก่อนหน้าวันที่เริ่มใช้ได้ค่ะ';
                } else if(errType == 'lessThanNow') {
                    messageTxt  = 'ไม่สามารถป้อนวันที่สิ้นสุดย้อนหลังได้ค่ะ';
                }
            }

            if(parent.$('.action-dialog-container').length <= 0) {
                parent.showActionDialog({
                    title: titleTxt,
                    message: messageTxt,
                    actionList: [
                        {
                            id: 'ok',
                            name: 'ตกลง',
                            desc: descTxt,
                            func:
                            function() {
                                parent.hideActionDialog();
                                self.val('');
                                self.focus();
                            }
                        }
                    ],
                    boxWidth: 400
                });
            }
        }

        function beforeSaveRecord() {
            // Not check time cover
            if(action == 'ADD' || $('#prdprm_startdate').val() == '') {
                return false;
            }

            var returnVal = true; // false = no err, true = have err

            $.ajax({
                url: '../common/ajaxCheckProductPromotionTimeCover.php',
                type: 'POST',
                data: {
                    prdprm_id       : code,
                    startdate       : tmpDateToRealDate($('#prdprm_startdate').val()),
                    enddate         : tmpDateToRealDate($('#prdprm_enddate').val())
                },
                success:
                function(responseJSON) {
                    var response = $.parseJSON(responseJSON);
                    if(response.status == 'NOT_COVER') {
                        var notCoverListHTML = '<ul>';
                        for(i in response.notCoverList) {
                            notCoverListHTML += '<li>' + response.notCoverList[i] + '</li>';
                        }
                        notCoverListHTML += '</ul>';

                        var msg = 'คุณกำหนดระยะเวลาโปรโมชั่น '
                                + $('#prdprm_name').val()
                                + ' ไม่ครอบคลุมผลิตภัณฑ์ที่จัดโปรโมชั่นในช่วงเวลาดังต่อไปนี้' + notCoverListHTML 
                                + '<br>โปรดแก้ไขระยะเวลาโปรโมชั่นให้ครอบคลุมช่วงเวลาดังกล่าว';
                        parent.showActionDialog({
                            title: 'ระยะเวลาโปรโมชั่นไม่ครอบคลุม',
                            message: msg,
                            actionList: [
                                {
                                    id: 'ok',
                                    name: 'ตกลง',
                                    func:
                                    function() {
                                        parent.hideActionDialog();
                                    }
                                }
                            ],
                            boxWidth: 500
                        });
                    } else if(response.status == 'COVER') {
                        if(action == 'EDIT') {
                            if(!hasInputError()) {
                                confirmSaveRecord();
                            }
                        }
                    } else {
                        alert(response.status);
                    }
                }
            });

            return returnVal;
        }
    </script>
    
</head>
<body>
 	 	 	 	 
<?php echo $_smarty_tpl->getSubTemplate ("form_table_header.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array(), 0);?>

<div class="ftb-body"> 
<div class="ftb-body">
	<?php if ($_smarty_tpl->tpl_vars['action']->value=='VIEW_DETAIL') {?>
	<!-- VIEW_DETAIL -->
	<div class="table-view-detail-image full">
        <img src="<?php if ($_smarty_tpl->tpl_vars['values']->value['prdprm_picture']!='-') {?>../img/product_promotions/<?php echo $_smarty_tpl->tpl_vars['values']->value['prdprm_picture'];?>
?rand=<?php echo $_smarty_tpl->tpl_vars['randNum']->value;?>
<?php } else { ?>../img/backoffice/no-pic.png<?php }?>">
	</div>
	<table class="table-view-detail">
		<tbody> 			
			<tr>
				<td>รหัสโปรโมชั่นผลิตภัณฑ์ :</td>
				<td><?php echo $_smarty_tpl->tpl_vars['code']->value;?>
</td>
			</tr>
			<tr>
				<td>ชื่อโปรโมชั่นผลิตภัณฑ์ :</td>
				<td><?php echo $_smarty_tpl->tpl_vars['values']->value['prdprm_name'];?>
</td>
			</tr>
            <tr>
                <td>ประเภทโปรโมชั่น :</td>
                <td><?php echo $_smarty_tpl->tpl_vars['values']->value['prdprm_type'];?>
</td>
            </tr>
            <tr>
                <td>กลุ่มโปรโมชั่นผลิตภัณฑ์ :</td>
                <td><div id="prdprmgrp_id" class="selectReferenceJS text"></div></td>
            </tr>
            <tr>
                <td>คำอธิบาย :</td>
                <td><?php if ($_smarty_tpl->tpl_vars['values']->value['prdprm_desc']) {?><?php echo $_smarty_tpl->tpl_vars['values']->value['prdprm_desc'];?>
<?php } else { ?>-<?php }?></td>
            </tr>
			<tr>
				<td>วันที่เริ่มใช้ :</td>
				<td><?php echo $_smarty_tpl->tpl_vars['values']->value['prdprm_startdate'];?>
</td>
			</tr>
			<tr>
				<td>วันที่สิ้นสุด :</td>
				<td><?php if ($_smarty_tpl->tpl_vars['values']->value['prdprm_enddate']!='-') {?><?php echo $_smarty_tpl->tpl_vars['values']->value['prdprm_enddate'];?>
<?php } else { ?>ไม่มีกำหนด<?php }?></td>
			</tr>
		</tbody>
	</table>
	<?php } else { ?>	 	
	<!-- ADD, EDIT -->	 	 	 	 	 	 	 	 	
    <form id="form-table" name="form-table" onsubmit="return false;">
	<input type="hidden" name="requiredFields" value="prdprm_name,prdprmgrp_id,prdprm_startdate,prdprm_type">
	<table class="mbk-form-input-normal" cellpadding="0" cellspacing="0">
		<tbody>
			<tr>
				<td colspan=2>
					<label class="input-required">ชื่อโปรโมชั่นผลิตภัณฑ์</label>
					<input id="prdprm_name" name="prdprm_name" type="text" class="form-input full" value="<?php echo $_smarty_tpl->tpl_vars['values']->value['prdprm_name'];?>
" require>
				</td>
			</tr>
			<tr class="errMsgRow">
                <td colspan=2>
                    <span id="err-prdprm_name-require" class="errInputMsg half err-prdprm_name">โปรดป้อนชื่อโปรโมชั่นผลิตภัณฑ์</span>
                </td>
            </tr>
            <tr>
                <td colspan=2>
                    <label class="input-required">ชื่อกลุ่มโปรโมชั่นผลิตภัณฑ์</label>
                    <div id="prdprmgrp_id" class="selectReferenceJS form-input full" require></div>
                </td>
            </tr>
            <tr>
                <td colspan=2>
                    <span id="err-prdprmgrp_id-require" class="errInputMsg err-prdprmgrp_id">โปรดเลือกกลุ่มโปรโมชั่นผลิตภัณฑ์</span>
                </td>
            </tr>
            <tr>
                <td colspan=2>
                    <label class="input-required">ประเภทโปรโมชั่น</label>
                    <label style="display:inline-block;"><input id="prdprm_type_sale" type="radio" name="prdprm_type" value="ลดราคา" checked> ลดราคา</label><br>
                    <label style="display:inline-block;"><input id="prdprm_type_free" type="radio" name="prdprm_type" value="ฟรี"> ฟรี</label><br><br>
                </td>
            </tr>
			<tr>
				<td>
					<label class="input-required">วันที่เริ่มใช้</label>
                	<input id="prdprm_startdate" name="prdprm_startdate" type="text" class="mbk-dtp-th form-input half" value="<?php echo $_smarty_tpl->tpl_vars['values']->value['prdprm_startdate'];?>
" require>
                </td>
                <td>
					<label>วันที่สิ้นสุด</label>
                	<input id="prdprm_enddate" name="prdprm_enddate" type="text" class="mbk-dtp-th form-input half" value="<?php echo $_smarty_tpl->tpl_vars['values']->value['prdprm_enddate'];?>
">
                </td>
			</tr>
			<tr class="errMsgRow">
                <td>
                    <span id="err-prdprm_startdate-require" class="errInputMsg half err-prdprm_startdate">โปรดป้อนวันที่เริ่มใช้</span>
                </td>
                <td></td>
            </tr>
            <tr>
                <td colspan=2>
                    <label>คำอธิบาย</label>
                    <textarea id="prdprm_desc" name="prdprm_desc" class="form-input full" rows="6"><?php echo $_smarty_tpl->tpl_vars['values']->value['prdprm_desc'];?>
</textarea>
                </td>
            </tr>
			<tr>
				<td colspan=2>
                    <label>รูปภาพโปรโมชั่น</label>
					<div id="prdprm_picture" class="uploadImageArea full"></div>
					<input type="hidden" name="prdprm_picture" value="<?php echo $_smarty_tpl->tpl_vars['values']->value['prdprm_picture'];?>
">
				</td>
			</tr>
		</tbody>
    </table>
    </form>
	<form method="post" enctype="multipart/form-data">
		<input id="prdprm_picture_file" type="file" name="imageFile" class="uploadImageSelector" multiple="multiple">
	</form>
	<?php }?>
</div>
</body>
</html>
<!--
    [Note]
    1. ให้ใส่ field ที่ต้องการเช็คใน input[name="requiredFields"] โดยกำหนดชื่อฟิลด์ลงใน value หากมีมากกว่า 1 field ให้คั่นด้วยเครื่องหมาย คอมม่า (,) และห้ามมีช่องว่าง เช่น value="name,surname,address" เป็นต้น
    2. input จะต้องกำหนด id, name ให้ตรงกับชื่อฟิลด์ของตารางนั้นๆ และกำหนด value ให้มีรูปแบบ value="$values.ชื่อฟิลด์"
--><?php }} ?>
