<?php /* Smarty version Smarty-3.1.18, created on 2015-01-28 22:51:23
         compiled from "C:\AppServ\www\myProject\backoffice\template\form_promotion_discout_sales.html" */ ?>
<?php /*%%SmartyHeaderCode:2799954c9057bd006e6-32941349%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'dc536c03383bd1e2fbb934e5e163e8cf73bf20fc' => 
    array (
      0 => 'C:\\AppServ\\www\\myProject\\backoffice\\template\\form_promotion_discout_sales.html',
      1 => 1421286997,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '2799954c9057bd006e6-32941349',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'action' => 0,
    'tableName' => 0,
    'tableNameTH' => 0,
    'code' => 0,
    'valuesDetail' => 0,
    'values' => 0,
    'referenceData' => 0,
    'nowDate' => 0,
    'prmdsdtlDetailList' => 0,
    'prmdsdtl' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.18',
  'unifunc' => 'content_54c9057c24c653_90497296',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_54c9057c24c653_90497296')) {function content_54c9057c24c653_90497296($_smarty_tpl) {?><!DOCTYPE html>
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
        var ajaxUrl     = 'form_promotion_discout_sales.php';
        var valuesDetail    = '';

        // Set valuesDetail
        
        <?php if ($_smarty_tpl->tpl_vars['valuesDetail']->value) {?>
        
            valuesDetail= <?php echo json_encode($_smarty_tpl->tpl_vars['valuesDetail']->value);?>
;
        
        <?php }?>
        

		$(document).ready(function() {
            // Default value of radio input
            
            <?php if ($_smarty_tpl->tpl_vars['values']->value['prmds_discout_type']=='บาท') {?>
            $('#prmds_discout_type_bath').prop('checked', true);
            <?php } elseif ($_smarty_tpl->tpl_vars['values']->value['prmds_discout_type']=='%') {?>
            $('#prmds_discout_type_percent').prop('checked', true);
            <?php }?>
            

            $('#addPrdtypBtn').click(addProductType);

            selectReferenceJS({
                elem            : $('#prdprmgrp_id'),
                data            : <?php echo json_encode($_smarty_tpl->tpl_vars['referenceData']->value["product_promotion_groups"]);?>
,
                searchTool      : true,
                defaultValue    : '<?php echo $_smarty_tpl->tpl_vars['values']->value['prdprmgrp_id'];?>
'
            });

			$('#prmds_startdate').datetimepicker({
                lang                : 'th',
                format              : 'Y/m/d',
                timepicker          :false,
                closeOnDateSelect   :true,
                scrollInput         :false,
                yearOffset          :543,
                onSelectDate: 
                function(){
                  $('#prmds_startdate').blur();
                },
                onShow:function( ct ){
                    if(action == 'ADD') {
                    	this.setOptions({
	                        minDate: realDateToTmpDate('<?php echo $_smarty_tpl->tpl_vars['nowDate']->value;?>
'),
	                        maxDate:$('#prmds_enddate').val()?unconvertThaiDate($('#prmds_enddate').val()):false
	                    });
                    } else if(action == 'EDIT') {
                    	this.setOptions({
	                        maxDate:$('#prmds_enddate').val()?unconvertThaiDate($('#prmds_enddate').val()):false
	                    });
                    }
                },
                timepicker:false
            });

			$('#prmds_enddate').datetimepicker({
                lang                : 'th',
                format              : 'Y/m/d',
                timepicker          :false,
                closeOnDateSelect   :true,
                scrollInput         :false,
                yearOffset          :543,
                onSelectDate: 
                function(){
                  $('#prmds_enddate').blur();
                },
                onShow:function( ct ){
                    if(action == 'ADD') {
                    	this.setOptions({
	                        minDate:$('#prmds_startdate').val()?unconvertThaiDate($('#prmds_startdate').val()):realDateToTmpDate('<?php echo $_smarty_tpl->tpl_vars['nowDate']->value;?>
')
	                    });
                    } else if(action="EDIT") {
                    	this.setOptions({
	                        minDate:$('#prmds_startdate').val()?unconvertThaiDate($('#prmds_startdate').val()):false
	                    });
                    }
                },
                timepicker:false
            });

            // Check date
            $('#prmds_startdate').change(function(){
                checkDate($(this), $('#prmds_enddate'));
            });
            $('#prmds_enddate').change(function() {
                checkDate($(this), $('#prmds_startdate'));
            });

            // Create service lists input
            if(action == 'ADD') {
                addProductType({
                    defaultValue : false
                });
            } else if(action == 'EDIT') {
                for(i in valuesDetail) {
                     addProductType({
                        defaultValue    : true,
                        prmdsdtl_id     : valuesDetail[i].prmdsdtl_id,
                        prdtyp_id       : valuesDetail[i].prdtyp_id
                    });
                }
            } else if(action == 'VIEW_DETAIL') {
                $('#allPrdtyp').text('<?php echo count($_smarty_tpl->tpl_vars['prmdsdtlDetailList']->value);?>
');
            }
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
                if(selfId == 'prmds_startdate') {
                    if(selfDate.getTime() > siblingDate.getTime()) {
                        showAlertInvalidDate(self, 'overSibling');
                        return;
                    }
                } else if(selfId == 'prmds_enddate') {
                    if(selfDate.getTime() < siblingDate.getTime()) {
                        showAlertInvalidDate(self, 'overSibling');
                        return;
                    }
                }
            }

            if(action == 'ADD') {
            	// Check now date
                if(selfId == 'prmds_startdate') {
                    if(selfDate.getTime() < nowDate.getTime()) {
                        showAlertInvalidDate(self, 'lessThanNow')
                    }
                } else if(selfId == 'prmds_enddate') {
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

            if(selfId == 'prmds_startdate') {
                titleTxt    = 'วันที่เริ่มใช้ไม่ถูกต้อง';
                descTxt     = 'ป้อนวันที่เริ่มใช้ใหม่';
                if(errType == 'overSibling') {
                    messageTxt  = 'วันที่เริ่มใช้ไม่สามารถอยู่หลังวันที่สิ้นสุดได้ค่ะ';
                } else if(errType == 'lessThanNow') {
                    messageTxt  = 'ไม่สามารถป้อนวันที่เริ่มใช้ย้อนหลังได้ค่ะ';
                }
            } else if(selfId == 'prmds_enddate') {
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

        function addProductType(data) {
            var randNum;
            var selectRefDefault = '';
            if(data.defaultValue) {
                selectRefDefault = data.prdtyp_id;
            }
            do {
                randNum     = parseInt(Math.random()*1000);
            } while($('#prdtyp_id_' + randNum).length > 0);
            var inputKeyId  = 'prdtyp_id_' + randNum;

            // Create HTML and append
            var prdtypRowHTML  = '<tr class="prdtyp-row">'
                            + '     <td>'
                            + '         <div id="' + inputKeyId + '" class="selectReferenceJS form-input full" require></div>';

            // add pkgsvl id for update
            if(action == 'EDIT' && typeof(data.prmdsdtl_id) != 'undefined') {
                prdtypRowHTML += '         <input name="prmdsdtl_id[]" type="hidden" value="' + data.prmdsdtl_id + '">';
            }

                prdtypRowHTML += '         <a title="ลบรายการนี้"><button class="removePrdtypBtn button button-icon button-icon-delete" onclick="removeProductType(\'' + randNum + '\')"></button></a>'
                            + '     </td>'
                            + '</tr>'
                            + '<tr id="' + inputKeyId + '_errRow">'
                            + '     <td>'
                            + '         <span id="err-' + inputKeyId + '-require" class="errInputMsg err-' + inputKeyId + '">'
                            + '             โปรดเลือกประเภทผลิตภัณฑ์'
                            + '         </span>'
                            + '     </td>'
                            + '</tr>';
            $('#prmdsdtl-table tbody').append(prdtypRowHTML);

            selectReferenceJS({
                elem            : $('#' + inputKeyId),
                data            : <?php echo json_encode($_smarty_tpl->tpl_vars['referenceData']->value["product_types"]);?>
,
                searchTool      : true,
                defaultValue    : selectRefDefault,
                success         : 
                function() {
                    $('input[name="' + inputKeyId + '"]').attr('name', 'prdtyp_id[]');
                    refreshPrdtypTable();
                },
                group           : 'product_types'
            });
        }

        function removeProductType(randNum) {
            if($('.removePrdtypBtn').length > 1) {
                var selectRef   = $('#prdtyp_id_' + randNum);
                var tr          = selectRef.parent().parent();
                var txt         = selectRef.find('.selectReferenceJS-text').text();
                var val         = selectRef.find('.selectReferenceJS-input').val();
                var msg         = '';
                if(val != '') {
                    msg = 'คุณต้องการลบประเภทผลิตภัณฑ์ ' + txt + ' ออกจากโปรโมชั่นการขายนี้ใช่หรือไม่?';
                } else {
                    msg = 'คุณต้องการลบประเภทผลิตภัณฑ์ที่เลือกออกจากโปรโมชั่นการขายนี้ใช่หรือไม่?';
                }
                parent.showActionDialog({
                    title: 'ลบประเภทผลิตภัณฑ์',
                    message: msg,
                    actionList: [
                        {
                            id: 'ok',
                            name: 'ตกลง',
                            desc: 'ลบประเภทผลิตภัณฑ์นี้ออกจากโปรโมชั่นการขาย',
                            func:
                            function() {
                                parent.hideActionDialog();
                                tr.remove();
                                $('#prdtyp_id' + randNum + '_errRow').remove();
                                refreshPrdtypTable();
                            }
                        },
                        {
                            id: 'cancel',
                            name: 'ยกเลิก',
                            desc: 'ยกเลิกการลบ',
                            func:
                            function() {
                                parent.hideActionDialog();
                            }
                        }
                    ],
                    boxWidth: 400
                });
            } else {
                parent.showActionDialog({
                    title: 'ไม่สามารถลบประเภทผลิตภัณฑ์ได้',
                    message: 'ประเภทผลิตภัณฑ์ที่จัดโปรโมชั้นต้องมีอย่างน้อย 1 รายการค่ะ',
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
                    boxWidth: 400
                });
            }
            
        }

        function refreshPrdtypTable() {
            var allPrdtyp = $('input[name="prdtyp_id[]"]').length;
            $('#allPrdtyp').text(allPrdtyp);
        }

        function beforeSaveRecord() {
            // Not check time cover
            if($('#prmds_startdate').val() == '') {
                return false;
            }

            var returnVal = true; // false = no err, true = have err

            // Get all product types
            var prdtypIdList = Array();
            $('input[name="prdtyp_id[]"]').each(function() {
                if($(this).val() != '') {
                    prdtypIdList.push($(this).val());
                }
            });

            $.ajax({
                url: '../common/ajaxCheckPromotionDiscoutSaleTimeCover.php',
                type: 'POST',
                data: {
                    prmds_id        : code,
                    prdprmgrp_id    : $('input[name="prdprmgrp_id"]').val(),
                    prdtypIdList    : prdtypIdList,
                    startdate       : tmpDateToRealDate($('#prmds_startdate').val()),
                    enddate         : tmpDateToRealDate($('#prmds_enddate').val())
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
                                + $('#prmds_name').val()
                                + ' ซ้อนทับกับช่วงเวลาดังต่อไปนี้' + notCoverListHTML 
                                + '<br>โปรดแก้ไขระยะเวลาโปรโมชั่นไม่ให้อยู่ในช่วงเวลาดังกล่าว';
                        parent.showActionDialog({
                            title: 'ระยะเวลาโปรโมชั่นการขายซ้อนทับกัน',
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
                        if(action == 'ADD') {
                            if(!hasInputError()) {
                                saveRecord();
                            }
                        } else if(action == 'EDIT') {
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
	<table class="table-view-detail">
		<tbody> 			
			<tr>
				<td>รหัสโปรโมชั่นการขาย :</td>
				<td><?php echo $_smarty_tpl->tpl_vars['code']->value;?>
</td>
			</tr>
			<tr>
				<td>ชื่อโปรโมชั่นการขาย :</td>
				<td><?php echo $_smarty_tpl->tpl_vars['values']->value['prmds_name'];?>
</td>
			</tr>
            <tr>
                <td>กลุ่มโปรโมชั่นผลิตภัณฑ์ :</td>
                <td><div id="prdprmgrp_id" class="selectReferenceJS text"></div></td>
            </tr>
            <tr>
                <td>ซื้อครบ :</td>
                <td><?php echo number_format($_smarty_tpl->tpl_vars['values']->value['prmds_purchase'],2,".",",");?>
 บาท</td>
            </tr>
            <tr>
                <td>รับส่วนลดทันที :</td>
                <td><?php echo number_format($_smarty_tpl->tpl_vars['values']->value['prmds_discout'],2,".",",");?>
 <?php echo $_smarty_tpl->tpl_vars['values']->value['prmds_discout_type'];?>
</td>
            </tr>
			<tr>
				<td>วันที่เริ่มใช้ :</td>
				<td><?php echo $_smarty_tpl->tpl_vars['values']->value['prmds_startdate_th'];?>
</td>
			</tr>
			<tr>
				<td>วันที่สิ้นสุด :</td>
				<td><?php if ($_smarty_tpl->tpl_vars['values']->value['prmds_enddate']!='') {?><?php echo $_smarty_tpl->tpl_vars['values']->value['prmds_enddate_th'];?>
<?php } else { ?>ไม่มีกำหนด<?php }?></td>
			</tr>
		</tbody>
	</table>
    <!--Product type Data-->
    <label class="article-title">ประเภทผลิตภัณฑ์ที่คิดโปรโมชั่น</label>
    ทั้งหมด <span id="allPrdtyp"></span> รายการ
    <table class="view-detail-reference" style="margin-top:5px;">
        <thead>
            <tr>
                <th style="text-align:center;">ลำดับ</th>
                <th class="txtLong-col">ประเภทผลิตภัณฑ์</th>
            </tr>
        </thead>
        <tbody>
            <?php  $_smarty_tpl->tpl_vars['prmdsdtl'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['prmdsdtl']->_loop = false;
 $_smarty_tpl->tpl_vars['i'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['prmdsdtlDetailList']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['prmdsdtl']->key => $_smarty_tpl->tpl_vars['prmdsdtl']->value) {
$_smarty_tpl->tpl_vars['prmdsdtl']->_loop = true;
 $_smarty_tpl->tpl_vars['i']->value = $_smarty_tpl->tpl_vars['prmdsdtl']->key;
?>
            <tr>
                <td align="center"><?php echo $_smarty_tpl->tpl_vars['prmdsdtl']->value['no'];?>
</td>
                <td align="left"><?php echo $_smarty_tpl->tpl_vars['prmdsdtl']->value['prdtyp_name'];?>
</td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
	<?php } else { ?>	 	
	<!-- ADD, EDIT -->	 	 	 	 	 	 	 	 	
    <form id="form-table" name="form-table" onsubmit="return false;">
	<input type="hidden" name="requiredFields" value="prmds_name,prdprmgrp_id,prmds_startdate,prmds_purchase,prmds_discout,prmds_discout_type">
	<table class="mbk-form-input-normal" cellpadding="0" cellspacing="0">
		<tbody>
			<tr>
				<td colspan=2>
					<label class="input-required">ชื่อโปรโมชั่นการขาย</label>
					<input id="prmds_name" name="prmds_name" type="text" class="form-input full" value="<?php echo $_smarty_tpl->tpl_vars['values']->value['prmds_name'];?>
" require>
				</td>
			</tr>
			<tr class="errMsgRow">
                <td colspan=2>
                    <span id="err-prmds_name-require" class="errInputMsg half err-prmds_name">โปรดป้อนชื่อชื่อโปรโมชั่นการขาย</span>
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
				<td>
					<label class="input-required">วันที่เริ่มใช้</label>
                	<input id="prmds_startdate" name="prmds_startdate" type="text" class="mbk-dtp-th form-input half" value="<?php echo $_smarty_tpl->tpl_vars['values']->value['prmds_startdate'];?>
" require>
                </td>
                <td>
					<label>วันที่สิ้นสุด</label>
                	<input id="prmds_enddate" name="prmds_enddate" type="text" class="mbk-dtp-th form-input half" value="<?php echo $_smarty_tpl->tpl_vars['values']->value['prmds_enddate'];?>
">
                </td>
			</tr>
			<tr class="errMsgRow">
                <td>
                    <span id="err-prmds_startdate-require" class="errInputMsg half err-prmds_startdate">โปรดป้อนวันที่เริ่มใช้</span>
                </td>
                <td></td>
            </tr>
            <tr>
                <td colspan=2>
                    <label class="input-required">ซื้อครบ(บาท)</label>
                    <input id="prmds_purchase" name="prmds_purchase" type="text" class="form-input full" value="<?php echo $_smarty_tpl->tpl_vars['values']->value['prmds_purchase'];?>
" valuepattern="money" require>
                </td>
            </tr>
            <tr>
                <td colspan=2>
                    <span id="err-prmds_purchase-require" class="errInputMsg err-prmds_purchase">โปรดป้อนจำนวนเงิน</span>
                    <span id="err-prmds_purchase-money" class="errInputMsg err-prmds_purchase">รูปแบบไม่ถูกต้อง กรุณาป้อนเฉพาะตัวเลขเท่านั้น</span>
                </td>
            </tr>
             <tr>
                 <td>
                    <label class="input-required">รับส่วนลดทันที</label>
                   <input id="prmds_discout" name="prmds_discout" type="text" class="form-input half" 
                   value="<?php if ($_smarty_tpl->tpl_vars['values']->value['prmds_discout']) {?><?php echo $_smarty_tpl->tpl_vars['values']->value['prmds_discout'];?>
<?php }?>" valuepattern="money" require>
                </td>
                <td>
                    <label style="margin-bottom: 10px;visibility: hidden;">รูปแบบส่วนลดการขาย</label>
                    <label style="display:inline-block"><input id="prmds_discout_type_bath" type="radio" name="prmds_discout_type" value="บาท" checked> บาท</label> &nbsp;
                    <label style="display:inline-block"><input id="prmds_discout_type_percent" type="radio" name="prmds_discout_type" value="%"> เปอร์เซ็น</label>
                </td>         
            </tr>
            <tr class="errMsgRow">
                <td colspan="2">
                    <span id="err-prmds_discout-require" class="errInputMsg err-prmds_discout">โปรดป้อนส่วนลดการขาย</span>
                    <span id="err-prmds_discout-money" class="errInputMsg err-prmds_discout">รูปแบบจำนวนเงินไม่ถูกต้อง จำนวนเงินเป็นได้เฉพาะตัวเลข ไม่มีคอมม่าคั่น จุดทศนิยม 2 ตำแหน่ง เช่น 130, 1600.25</span>
                </td>
            </tr>
		</tbody>
    </table>

    <label class="article-title">ประเภทผลิตภัณฑ์ที่คิดโปรโมชั่น</label>
    ประเภทผลิตภัณฑ์ทั้งหมด <span id="allPrdtyp"></span> รายการ<br><br>
    <table id="prmdsdtl-table" class="mbk-form-input-normal" cellpadding="0" cellspacing="0">
        <tbody>
            <tr class="headTable-row">
                <td><label class="input-required">ประเภทผลิตภัณฑ์</label></td>
            </tr>
        </tbody>
    </table>
    <button id="addPrdtypBtn" class="button button-icon button-icon-add">เพิ่มประเภทผลิตภัณฑ์</button>
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
