<?php /* Smarty version Smarty-3.1.18, created on 2015-01-28 22:15:01
         compiled from "C:\AppServ\www\myProject\backoffice\template\form_package_promotions.html" */ ?>
<?php /*%%SmartyHeaderCode:2744554c8fcf56ae181-49407413%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '6b8508ee80c9fec17174cf70b03cec4082b2c650' => 
    array (
      0 => 'C:\\AppServ\\www\\myProject\\backoffice\\template\\form_package_promotions.html',
      1 => 1422455216,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '2744554c8fcf56ae181-49407413',
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
    'randNum' => 0,
    'referenceData' => 0,
    'nowDate' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.18',
  'unifunc' => 'content_54c8fcf5963210_33007697',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_54c8fcf5963210_33007697')) {function content_54c8fcf5963210_33007697($_smarty_tpl) {?><!DOCTYPE html>
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
        var ajaxUrl     = 'form_package_promotions.php';

		$(document).ready(function() {
			uploadImageInput({
				area: $('#pkgprm_pic'),
				input: $('input[name="pkgprm_pic"]'),
				selector: $('#prdprm_picture_file'),
				defaultValue: '<?php if ($_smarty_tpl->tpl_vars['values']->value['pkgprm_pic']) {?>../img/package_promotions/<?php echo $_smarty_tpl->tpl_vars['values']->value['pkgprm_pic'];?>
?rand=<?php echo $_smarty_tpl->tpl_vars['randNum']->value;?>
<?php }?>'
			});

            selectReferenceJS({
                elem            : $('#custype_id'),
                data            : <?php echo json_encode($_smarty_tpl->tpl_vars['referenceData']->value["customer_types"]);?>
,
                searchTool      : true,
                defaultValue    : '<?php echo $_smarty_tpl->tpl_vars['values']->value['custype_id'];?>
'
            });

			$('#pkgprm_startdate').datetimepicker({
                lang                : 'th',
                format              : 'Y/m/d',
                timepicker          :false,
                closeOnDateSelect   :true,
                scrollInput         :false,
                yearOffset          :543,
                onSelectDate: 
                function(){
                  $('#pkgprm_startdate').blur();
                },
                onShow:function( ct ){
                    if(action == 'ADD') {
                    	this.setOptions({
	                        minDate: realDateToTmpDate('<?php echo $_smarty_tpl->tpl_vars['nowDate']->value;?>
'),
	                        maxDate:$('#pkgprm_enddate').val()?unconvertThaiDate($('#pkgprm_enddate').val()):false
	                    });
                    } else if(action == 'EDIT') {
                    	this.setOptions({
	                        maxDate:$('#pkgprm_enddate').val()?unconvertThaiDate($('#pkgprm_enddate').val()):false
	                    });
                    }
                },
                timepicker:false
            });

			$('#pkgprm_enddate').datetimepicker({
                lang                : 'th',
                format              : 'Y/m/d',
                timepicker          :false,
                closeOnDateSelect   :true,
                scrollInput         :false,
                yearOffset          :543,
                onSelectDate: 
                function(){
                  $('#pkgprm_enddate').blur();
                },
                onShow:function( ct ){
                    if(action == 'ADD') {
                    	this.setOptions({
	                        minDate:$('#pkgprm_startdate').val()?unconvertThaiDate($('#pkgprm_startdate').val()):realDateToTmpDate('<?php echo $_smarty_tpl->tpl_vars['nowDate']->value;?>
')
	                    });
                    } else if(action="EDIT") {
                    	this.setOptions({
	                        minDate:$('#pkgprm_startdate').val()?unconvertThaiDate($('#pkgprm_startdate').val()):false
	                    });
                    }
                },
                timepicker:false
            });

            // Check date
            $('#pkgprm_startdate').change(function(){
                checkDate($(this), $('#pkgprm_enddate'));
            });
            $('#pkgprm_enddate').change(function() {
                checkDate($(this), $('#pkgprm_startdate'));
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
                if(selfId == 'pkgprm_startdate') {
                    if(selfDate.getTime() > siblingDate.getTime()) {
                        showAlertInvalidDate(self, 'overSibling');
                        return;
                    }
                } else if(selfId == 'pkgprm_enddate') {
                    if(selfDate.getTime() < siblingDate.getTime()) {
                        showAlertInvalidDate(self, 'overSibling');
                        return;
                    }
                }
            }

            if(action == 'ADD') {
            	// Check now date
                if(selfId == 'pkgprm_startdate') {
                    if(selfDate.getTime() < nowDate.getTime()) {
                        showAlertInvalidDate(self, 'lessThanNow')
                    }
                } else if(selfId == 'pkgprm_enddate') {
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

            if(selfId == 'pkgprm_startdate') {
                titleTxt    = 'วันที่เริ่มใช้ไม่ถูกต้อง';
                descTxt     = 'ป้อนวันที่เริ่มใช้ใหม่';
                if(errType == 'overSibling') {
                    messageTxt  = 'วันที่เริ่มใช้ไม่สามารถอยู่หลังวันที่สิ้นสุดได้ค่ะ';
                } else if(errType == 'lessThanNow') {
                    messageTxt  = 'ไม่สามารถป้อนวันที่เริ่มใช้ย้อนหลังได้ค่ะ';
                }
            } else if(selfId == 'pkgprm_enddate') {
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

        // function beforeSaveRecord() {
        //     // Not check time cover
        //     if(action == 'ADD' || $('#pkgprm_startdate').val() == '') {
        //         return false;
        //     }

        //     var returnVal = true; // false = no err, true = have err

        //     $.ajax({
        //         url: '../common/ajaxCheckPackagePromotionTimeCover.php',
        //         type: 'POST',
        //         data: {
        //             prdprm_id       : code,
        //             startdate       : tmpDateToRealDate($('#pkgprm_startdate').val()),
        //             enddate         : tmpDateToRealDate($('#pkgprm_enddate').val())
        //         },
        //         success:
        //         function(responseJSON) {
        //             var response = $.parseJSON(responseJSON);
        //             if(response.status == 'NOT_COVER') {
        //                 var notCoverListHTML = '<ul>';
        //                 for(i in response.notCoverList) {
        //                     notCoverListHTML += '<li>' + response.notCoverList[i] + '</li>';
        //                 }
        //                 notCoverListHTML += '</ul>';

        //                 var msg = 'คุณกำหนดระยะเวลาโปรโมชั่น '
        //                         + $('#pkgprm_name').val()
        //                         + ' ไม่ครอบคลุมแพ็คเกจที่จัดโปรโมชั่นในช่วงเวลาดังต่อไปนี้' + notCoverListHTML 
        //                         + '<br>โปรดแก้ไขระยะเวลาโปรโมชั่นให้ครอบคลุมช่วงเวลาดังกล่าว';
        //                 parent.showActionDialog({
        //                     title: 'ระยะเวลาโปรโมชั่นไม่ครอบคลุม',
        //                     message: msg,
        //                     actionList: [
        //                         {
        //                             id: 'ok',
        //                             name: 'ตกลง',
        //                             func:
        //                             function() {
        //                                 parent.hideActionDialog();
        //                             }
        //                         }
        //                     ],
        //                     boxWidth: 500
        //                 });
        //             } else if(response.status == 'COVER') {
        //                 if(action == 'EDIT') {
        //                     if(!hasInputError()) {
        //                         confirmSaveRecord();
        //                     }
        //                 }
        //             } else {
        //                 alert(response.status);
        //             }
        //         }
        //     });

        //     return returnVal;
        // }
    </script>
    
</head>
<body>
 	 	 	 	 
<?php echo $_smarty_tpl->getSubTemplate ("form_table_header.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array(), 0);?>

<div class="ftb-body"> 
<div class="ftb-body">
	<?php if ($_smarty_tpl->tpl_vars['action']->value=='VIEW_DETAIL') {?>
	<!-- VIEW_DETAIL -->
	<div class="table-view-detail-image full">
        <img src="<?php if ($_smarty_tpl->tpl_vars['values']->value['pkgprm_pic']!='-') {?>../img/package_promotions/<?php echo $_smarty_tpl->tpl_vars['values']->value['pkgprm_pic'];?>
?rand=<?php echo $_smarty_tpl->tpl_vars['randNum']->value;?>
<?php } else { ?>../img/backoffice/no-pic.png<?php }?>">
	</div>
	<table class="table-view-detail">
		<tbody> 			
			<tr>
				<td>รหัสโปรโมชั่นแพ็คเกจ :</td>
				<td><?php echo $_smarty_tpl->tpl_vars['code']->value;?>
</td>
			</tr>
			<tr>
				<td>ชื่อโปรโมชั่นแพ็คเกจ :</td>
				<td><?php echo $_smarty_tpl->tpl_vars['values']->value['pkgprm_name'];?>
</td>
			</tr>
            <tr>
                <td>ประเภทผู้ใช้บริการ :</td>
                <td><div id="custype_id" class="selectReferenceJS text"></div></td>
            </tr>
            <tr>
                <td>วันที่เริ่มใช้ :</td>
                <td><?php echo $_smarty_tpl->tpl_vars['values']->value['pkgprm_startdate'];?>
</td>
            </tr>
            <tr>
                <td>วันที่สิ้นสุด :</td>
                <td><?php if ($_smarty_tpl->tpl_vars['values']->value['pkgprm_enddate']!='-') {?><?php echo $_smarty_tpl->tpl_vars['values']->value['pkgprm_enddate'];?>
<?php } else { ?>ไม่มีกำหนด<?php }?></td>
            </tr>
            <tr>
                <td>คำอธิบาย :</td>
                <td><?php if ($_smarty_tpl->tpl_vars['values']->value['pkgprm_desc']) {?><?php echo $_smarty_tpl->tpl_vars['values']->value['pkgprm_desc'];?>
<?php } else { ?>-<?php }?></td>
            </tr>
			
		</tbody>
	</table>
	<?php } else { ?>	 	
	<!-- ADD, EDIT -->	 	 	 	 	 	 	 	 	
    <form id="form-table" name="form-table" onsubmit="return false;">
	<input type="hidden" name="requiredFields" value="pkgprm_name,pkgprm_startdate,custype_id">
	<table class="mbk-form-input-normal" cellpadding="0" cellspacing="0">
		<tbody>
			<tr>
				<td colspan=2>
					<label class="input-required">ชื่อโปรโมชั่นแพ็คเกจ</label>
					<input id="pkgprm_name" name="pkgprm_name" type="text" class="form-input full" value="<?php echo $_smarty_tpl->tpl_vars['values']->value['pkgprm_name'];?>
" require>
				</td>
			</tr>
			<tr class="errMsgRow">
                <td colspan=2>
                    <span id="err-pkgprm_name-require" class="errInputMsg half err-pkgprm_name">โปรดป้อนชื่อโปรโมชั่นแพ็คเกจ</span>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <label class="input-required">ประเภทผู้ใช้บริการ</label>
                    <div id="custype_id" class="selectReferenceJS form-input full" require></div>
                </td>
            </tr>
            <tr class="errMsgRow">
                <td colspan="2">
                    <span id="err-custype_id-require" class="errInputMsg err-custype_id">โปรดเลือกประเภทผู้ใช้บริการ</span>
                </td>
            </tr>
			<tr>
				<td>
					<label class="input-required">วันที่เริ่มใช้</label>
                	<input id="pkgprm_startdate" name="pkgprm_startdate" type="text" class="mbk-dtp-th form-input half" value="<?php echo $_smarty_tpl->tpl_vars['values']->value['pkgprm_startdate'];?>
" require>
                </td>
                <td>
					<label>วันที่สิ้นสุด</label>
                	<input id="pkgprm_enddate" name="pkgprm_enddate" type="text" class="mbk-dtp-th form-input half" value="<?php echo $_smarty_tpl->tpl_vars['values']->value['pkgprm_enddate'];?>
">
                </td>
			</tr>
			<tr class="errMsgRow">
                <td>
                    <span id="err-pkgprm_startdate-require" class="errInputMsg half err-pkgprm_startdate">โปรดป้อนวันที่เริ่มใช้</span>
                </td>
                <td></td>
            </tr>
            <tr>
                <td colspan=2>
                    <label>คำอธิบาย</label>
                    <textarea id="pkgprm_desc" name="pkgprm_desc" class="form-input full" rows="6"><?php echo $_smarty_tpl->tpl_vars['values']->value['pkgprm_desc'];?>
</textarea>
                </td>
            </tr>
			<tr>
				<td colspan=2>
                    <label>รูปภาพโปรโมชั่น</label>
					<div id="pkgprm_pic" class="uploadImageArea full"></div>
					<input type="hidden" name="pkgprm_pic" value="<?php echo $_smarty_tpl->tpl_vars['values']->value['pkgprm_pic'];?>
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
