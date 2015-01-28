<?php /* Smarty version Smarty-3.1.18, created on 2015-01-28 22:55:17
         compiled from "C:\AppServ\www\myProject\backoffice\template\form_packages.html" */ ?>
<?php /*%%SmartyHeaderCode:1076154c906654b1dd5-84703418%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'f4adde3305190bea6239b8a2d95ba709ceeb9abf' => 
    array (
      0 => 'C:\\AppServ\\www\\myProject\\backoffice\\template\\form_packages.html',
      1 => 1419270579,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '1076154c906654b1dd5-84703418',
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
    'randNum' => 0,
    'nowDate' => 0,
    'pkgsvlDetailList' => 0,
    'referenceData' => 0,
    'pkgsvl' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.18',
  'unifunc' => 'content_54c9066579d703_63512940',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_54c9066579d703_63512940')) {function content_54c9066579d703_63512940($_smarty_tpl) {?><!DOCTYPE html>
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
        var ajaxUrl     = 'form_packages.php';
        var valuesDetail    = '';
        var vdtlAllSvl 	= 0;

         // Set valuesDetail
        
        <?php if ($_smarty_tpl->tpl_vars['valuesDetail']->value) {?>
        
            valuesDetail= <?php echo json_encode($_smarty_tpl->tpl_vars['valuesDetail']->value);?>
;
        
        <?php }?>
        

		$(document).ready(function() {
			$('#addSvlBtn').click(addServiceList);

			uploadImageInput({
				area: $('#pkg_picture'),
				input: $('input[name="pkg_picture"]'),
				selector: $('#pkg_picture_file'),
				defaultValue: '<?php if ($_smarty_tpl->tpl_vars['values']->value['pkg_picture']) {?>../img/packages/<?php echo $_smarty_tpl->tpl_vars['values']->value['pkg_picture'];?>
?rand=<?php echo $_smarty_tpl->tpl_vars['randNum']->value;?>
<?php }?>'
			});

			$('#pkg_start').datetimepicker({
                lang                : 'th',
                format              : 'Y/m/d',
                timepicker          :false,
                closeOnDateSelect   :true,
                scrollInput         :false,
                yearOffset          :543,
                onSelectDate: 
                function(){
                  $('#pkg_start').blur();
                },
                onShow:function( ct ){
                    if(action == 'ADD') {
                    	this.setOptions({
	                        minDate: realDateToTmpDate('<?php echo $_smarty_tpl->tpl_vars['nowDate']->value;?>
'),
	                        maxDate:$('#pkg_stop').val()?unconvertThaiDate($('#pkg_stop').val()):false
	                    });
                    } else if(action == 'EDIT') {
                    	this.setOptions({
	                        maxDate:$('#pkg_stop').val()?unconvertThaiDate($('#pkg_stop').val()):false
	                    });
                    }
                },
                timepicker:false
            });

            $('#pkg_stop').datetimepicker({
                lang                : 'th',
                format              : 'Y/m/d',
                timepicker          :false,
                closeOnDateSelect   :true,
                scrollInput         :false,
                yearOffset          :543,
                onSelectDate: 
                function(){
                  $('#pkg_stop').blur();
                },
                onShow:function( ct ){
                    if(action == 'ADD') {
                    	this.setOptions({
	                        minDate:$('#pkg_start').val()?unconvertThaiDate($('#pkg_start').val()):realDateToTmpDate('<?php echo $_smarty_tpl->tpl_vars['nowDate']->value;?>
')
	                    });
                    } else if(action="EDIT") {
                    	this.setOptions({
	                        minDate:$('#pkg_start').val()?unconvertThaiDate($('#pkg_start').val()):false
	                    });
                    }
                },
                timepicker:false
            });

			// Check date
            $('#pkg_start').change(function(){
                checkDate($(this), $('#pkg_stop'));
            });
            $('#pkg_stop').change(function() {
                checkDate($(this), $('#pkg_start'));
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
                    if(selfId == 'pkg_start') {
                        if(selfDate.getTime() > siblingDate.getTime()) {
                            showAlertInvalidDate(self, 'overSibling');
                            return;
                        }
                    } else if(selfId == 'pkg_stop') {
                        if(selfDate.getTime() < siblingDate.getTime()) {
                            showAlertInvalidDate(self, 'overSibling');
                            return;
                        }
                    }
                }

                if(action == 'ADD') {
                	// Check now date
	                if(selfId == 'pkg_start') {
	                    if(selfDate.getTime() < nowDate.getTime()) {
	                        showAlertInvalidDate(self, 'lessThanNow')
	                    }
	                } else if(selfId == 'pkg_stop') {
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

                if(selfId == 'pkg_start') {
                    titleTxt    = 'วันที่เริ่มใช้ไม่ถูกต้อง';
                    descTxt     = 'ป้อนวันที่เริ่มใช้ใหม่';
                    if(errType == 'overSibling') {
                        messageTxt  = 'วันที่เริ่มใช้ไม่สามารถอยู่หลังวันที่สิ้นสุดได้ค่ะ';
                    } else if(errType == 'lessThanNow') {
                        messageTxt  = 'ไม่สามารถป้อนวันที่เริ่มใช้ย้อนหลังได้ค่ะ';
                    }
                } else if(selfId == 'pkg_stop') {
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

			// Create service lists input
            if(action == 'ADD') {
                addServiceList({
                    defaultValue : false
                });
            } else if(action == 'EDIT') {
                for(i in valuesDetail) {
                     addServiceList({
                        defaultValue : true,
                        pkgsvl_id   : valuesDetail[i].pkgsvl_id,
                        svl_id      : valuesDetail[i].svl_id
                    });
                }
            } else if(action == 'VIEW_DETAIL') {
            	$('#allSvl').text('<?php echo count($_smarty_tpl->tpl_vars['pkgsvlDetailList']->value);?>
');
            }


		});

		function addServiceList(data) {
            var randNum;
            var selectRefDefault = '';
            if(data.defaultValue) {
                selectRefDefault = data.svl_id;
            }
            do {
                randNum     = parseInt(Math.random()*1000);
            } while($('#svl_id_' + randNum).length > 0);
            var inputKeyId  = 'svl_id_' + randNum;

            // Create HTML and append
            var svlRowHTML  = '<tr class="svl-row">'
                            + '     <td>'
                            + '         <div id="' + inputKeyId + '" class="selectReferenceJS form-input full" require></div>';

            // add pkgsvl id for update
            if(action == 'EDIT' && typeof(data.pkgsvl_id) != 'undefined') {
                svlRowHTML += '         <input name="pkgsvl_id[]" type="hidden" value="' + data.pkgsvl_id + '">';
            }

                svlRowHTML += '         <a title="ลบรายการนี้"><button class="removeSvlBtn button button-icon button-icon-delete" onclick="removeServiceLists(\'' + randNum + '\')"></button></a>'
                            + '     </td>'
                            + '</tr>'
                            + '<tr id="' + inputKeyId + '_errRow">'
                            + '		<td>'
                            + '			<span id="err-' + inputKeyId + '-require" class="errInputMsg err-' + inputKeyId + '">'
                            + '				โปรดเลือกรายการบริการ'
                            + '			</span>'
                            + '		</td>'
                            + '</tr>';
            $('#service-lists-table tbody').append(svlRowHTML);

            selectReferenceJS({
                elem            : $('#' + inputKeyId),
                data            : <?php echo json_encode($_smarty_tpl->tpl_vars['referenceData']->value["service_lists"]);?>
,
                searchTool      : true,
                defaultValue    : selectRefDefault,
                allowChangeOption : allowSelectSvlId,
                success         : 
                function() {
                    $('input[name="' + inputKeyId + '"]').attr('name', 'svl_id[]');
                    refreshSvlTable();
                },
                group           : 'service_lists'
            });
        }

        function removeServiceLists(randNum) {
            if($('.removeSvlBtn').length > 1) {
                var selectRef   = $('#svl_id_' + randNum);
                var tr          = selectRef.parent().parent();
                var txt         = selectRef.find('.selectReferenceJS-text').text();
                var val         = selectRef.find('.selectReferenceJS-input').val();
                var msg         = '';
                if(val != '') {
                    msg = 'คุณต้องการลบรายการบริการ ' + txt + ' ออกจากแพ็คเกจนี้ใช่หรือไม่?';
                } else {
                    msg = 'คุณต้องการลบรายการบริการที่เลือกออกจากแพ็คเกจนี้ใช่หรือไม่?';
                }
                parent.showActionDialog({
                    title: 'ลบรายการบริการ',
                    message: msg,
                    actionList: [
                        {
                            id: 'ok',
                            name: 'ตกลง',
                            desc: 'ลบรายการบริการนี้ออกจากแพ็คเกจ',
                            func:
                            function() {
                                parent.hideActionDialog();
                                tr.remove();
                                $('#svl_id_' + randNum + '_errRow').remove();
                                refreshSvlTable();
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
                    title: 'ไม่สามารถลบรายการบริการได้',
                    message: 'รายการบริการที่จัดแพ็คเกจต้องมีอย่างน้อย 1 รายการค่ะ',
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

        function allowSelectSvlId(selected) {
            var notDuplicate = true;
            $('input[name="svl_id[]"]').each(function() {
                if($(this).val() == selected) {
                     parent.showActionDialog({
                        title: 'คุณเลือกรายการบริการนี้แล้ว',
                        message: 'รายการบริการไม่สามารถซ้ำกันได้ค่ะ',
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
                    notDuplicate = false;
                }
            });
            return notDuplicate;
        }

        function refreshSvlTable() {
        	var allSvl = $('input[name="svl_id[]"]').length;
        	$('#allSvl').text(allSvl);
        }
    </script>
    
</head>
<body>
 	 	 	 	 
<?php echo $_smarty_tpl->getSubTemplate ("form_table_header.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array(), 0);?>

<div class="ftb-body">
	<?php if ($_smarty_tpl->tpl_vars['action']->value=='VIEW_DETAIL') {?>
	<!-- VIEW_DETAIL -->
	<div class="table-view-detail-image full">
        <img src="<?php if ($_smarty_tpl->tpl_vars['values']->value['pkg_picture']) {?>../img/packages/<?php echo $_smarty_tpl->tpl_vars['values']->value['pkg_picture'];?>
?rand=<?php echo $_smarty_tpl->tpl_vars['randNum']->value;?>
<?php } else { ?>../img/backoffice/no-pic.png<?php }?>">
	</div>
	<table class="table-view-detail">
		<tbody> 					
			<tr>
				<td>รหัสแพ็คเกจ :</td>
				<td><?php echo $_smarty_tpl->tpl_vars['code']->value;?>
</td>
			</tr>
			<tr>
                <td>ชื่อแพ็คเกจ :</td>
                <td><?php echo $_smarty_tpl->tpl_vars['values']->value['pkg_name'];?>
</td>
            </tr>
			<tr>
				<td>วันที่เริ่มใช้ :</td>
				<td><?php echo $_smarty_tpl->tpl_vars['values']->value['pkg_start_th'];?>
</td>
			</tr>
			<tr>
				<td>วันที่สิ้นสุด :</td>
				<td><?php if ($_smarty_tpl->tpl_vars['values']->value['pkg_stop']) {?><?php echo $_smarty_tpl->tpl_vars['values']->value['pkg_stop_th'];?>
<?php } else { ?>-<?php }?></td>
			</tr>
			<tr>
				<td>คำอธิบาย :</td>
				<td><?php if ($_smarty_tpl->tpl_vars['values']->value['pkg_desc']) {?><?php echo $_smarty_tpl->tpl_vars['values']->value['pkg_desc'];?>
<?php } else { ?>-<?php }?></td>
			</tr>
			<tr>
				<td>ราคา :</td>
				<td><?php echo number_format($_smarty_tpl->tpl_vars['values']->value['pkg_price'],2,".",",");?>
 บาท</td>
			</tr>
			
		</tbody>
	</table>
	<!--Service list Data-->
	<label class="article-title">รายการบริการที่จัดแพ็คเกจ</label>
	ทั้งหมด <span id="allSvl"></span> รายการ
	<table class="view-detail-reference" style="margin-top:5px;">
        <thead>
            <tr>
                <th style="text-align:center;">ลำดับ</th>
                <th>รหัสรายการบริการ</th>
                <th class="txtLong-col">ชื่อรายการบริการ</th>
                <th>เวลาที่ใช้</th>
            </tr>
        </thead>
        <tbody>
            <?php  $_smarty_tpl->tpl_vars['pkgsvl'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['pkgsvl']->_loop = false;
 $_smarty_tpl->tpl_vars['i'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['pkgsvlDetailList']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['pkgsvl']->key => $_smarty_tpl->tpl_vars['pkgsvl']->value) {
$_smarty_tpl->tpl_vars['pkgsvl']->_loop = true;
 $_smarty_tpl->tpl_vars['i']->value = $_smarty_tpl->tpl_vars['pkgsvl']->key;
?>
            <tr>
                <td align="center"><?php echo $_smarty_tpl->tpl_vars['pkgsvl']->value['no'];?>
</td>
                <td align="left">
                	<a href="form_table.php?action=VIEW_DETAIL&tableName=service_lists&code=<?php echo $_smarty_tpl->tpl_vars['pkgsvl']->value['svl_id'];?>
&hideEditButton=true" class="normal-link" title="คลิกเพื่อดูรายละเอียด">
                		<?php echo $_smarty_tpl->tpl_vars['pkgsvl']->value['svl_id'];?>

                	</a>
                </td>
                <td align="left" class="txtLong-col"><?php echo $_smarty_tpl->tpl_vars['pkgsvl']->value['svl_name'];?>
</td>
                <td align="left"><?php echo $_smarty_tpl->tpl_vars['pkgsvl']->value['svl_hr'];?>
 ชั่วโมง <?php echo $_smarty_tpl->tpl_vars['pkgsvl']->value['svl_min'];?>
 นาที</td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
	<?php } else { ?>	 	
	<!-- ADD, EDIT -->	 	 	 	 	 	 	 	 	
    <form id="form-table" name="form-table" onsubmit="return false;">
	<input type="hidden" name="requiredFields" value="pkg_name,pkg_start,pkg_price">
	<table class="mbk-form-input-normal" cellpadding="0" cellspacing="0">
		<tbody>
			<tr>
				<td colspan=2>
					<label class="input-required">ชื่อแพ็คเกจ</label>
					<input id="pkg_name" name="pkg_name" type="text" class="form-input full" value="<?php echo $_smarty_tpl->tpl_vars['values']->value['pkg_name'];?>
" require>
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<span id="err-pkg_name-require" class="errInputMsg err-pkg_name">โปรดป้อนชื่อแพ็คเกจ</span>
				</td>
			</tr>
			<tr>
				<td>
					<label class="input-required">วันที่เริ่มใช้</label>
                	<input id="pkg_start" name="pkg_start" type="text" class="mbk-dtp-th form-input half" value="<?php if ($_smarty_tpl->tpl_vars['values']->value['pkg_start']) {?><?php echo $_smarty_tpl->tpl_vars['values']->value['pkg_start'];?>
<?php } else { ?><?php echo $_smarty_tpl->tpl_vars['nowDate']->value;?>
<?php }?>" require>
                </td>
                <td>
					<label>วันที่สิ้นสุด</label>
                	<input id="pkg_stop" name="pkg_stop" type="text" class="mbk-dtp-th form-input half" value="<?php echo $_smarty_tpl->tpl_vars['values']->value['pkg_stop'];?>
">
                </td>
			</tr>
			<tr>
				<td>
					<span id="err-pkg_start-require" class="errInputMsg err-pkg_start">โปรดป้อนวันที่เริ่มใช้</span>
				</td>
				<td></td>
			</tr>
			<tr>
				<td colspan=2>
					<label class="input-required">ราคา (บาท)</label>
					<input id="pkg_price" name="pkg_price" type="text" class="form-input full" value="<?php echo $_smarty_tpl->tpl_vars['values']->value['pkg_price'];?>
" valuepattern="money" require style="text-align:right;">
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<span id="err-pkg_price-require" class="errInputMsg err-pkg_price">โปรดป้อนราคา</span>
					<span id="err-pkg_price-money" class="errInputMsg err-pkg_price">รูปแบบจำนวนเงินไม่ถูกต้อง จำนวนเงินเป็นได้เฉพาะตัวเลข ไม่มีคอมม่าคั่น จุดทศนิยม 2 ตำแหน่ง เช่น 130, 1600.25</span>
				</td>
			</tr>
			<tr>
				<td colspan=2>
					<label>คำอธิบาย</label>
					<textarea id="pkg_desc" name="pkg_desc" class="form-input full" rows="6"><?php echo $_smarty_tpl->tpl_vars['values']->value['pkg_desc'];?>
</textarea>
				</td>
			</tr>
			<tr>
				<td colspan="2">
                    <label>รูปภาพแพ็คเกจ</label>
					<div id="pkg_picture" class="uploadImageArea full"></div>
					<input type="hidden" name="pkg_picture" value="<?php echo $_smarty_tpl->tpl_vars['values']->value['pkg_picture'];?>
">
				</td>
			</tr>
		</tbody>
    </table>
    
    <label class="article-title">รายการบริการที่จัดแพ็คเกจ</label>
	<table id="service-lists-table" class="mbk-form-input-normal" cellpadding="0" cellspacing="0">
        <tbody>
            <tr>
                <td><label class="input-required">รายการบริการทั้งหมด <span id="allSvl"></span> รายการ</label></td>
            </tr>
        </tbody>
    </table>
    <button id="addSvlBtn" class="button button-icon button-icon-add">เพิ่มรายการบริการ</button>
    </form>

	<form method="post" enctype="multipart/form-data">
		<input id="pkg_picture_file" type="file" name="imageFile" class="uploadImageSelector" multiple="multiple">
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
