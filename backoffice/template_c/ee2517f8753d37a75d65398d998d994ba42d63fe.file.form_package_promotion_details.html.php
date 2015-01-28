<?php /* Smarty version Smarty-3.1.18, created on 2015-01-28 22:59:52
         compiled from "C:\AppServ\www\myProject\backoffice\template\form_package_promotion_details.html" */ ?>
<?php /*%%SmartyHeaderCode:911754c8f6b29c9ce9-30170666%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'ee2517f8753d37a75d65398d998d994ba42d63fe' => 
    array (
      0 => 'C:\\AppServ\\www\\myProject\\backoffice\\template\\form_package_promotion_details.html',
      1 => 1422460781,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '911754c8f6b29c9ce9-30170666',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.18',
  'unifunc' => 'content_54c8f6b2e54dd0_73890811',
  'variables' => 
  array (
    'action' => 0,
    'tableName' => 0,
    'tableNameTH' => 0,
    'code' => 0,
    'referenceData' => 0,
    'values' => 0,
    'nowDate' => 0,
  ),
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_54c8f6b2e54dd0_73890811')) {function content_54c8f6b2e54dd0_73890811($_smarty_tpl) {?><!DOCTYPE html>
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
    <style type="text/css">
        #pkgprmTime {
            display: none;
            margin-top: -12px;
            margin-bottom: 15px;
            color: #aaa;
            font-size: 14px;
        }
        #previewPrmPrice {
            list-style: none;
            width: 520px;
            padding: 0;
            margin: 0;
            text-align: center;
        }
        #previewPrmPrice.viewDetail {
            width: 100%;
        }
        #previewPrmPrice li {
            width: 170px;
            height: 100px;
            display: block;
            float: left;
            border: 1px solid #ddd;
            margin-right: 5px;
        }
        #previewPrmPrice.viewDetail li {
            width: 32.5%;
            margin-right: 1.25%;
        }
        #previewPrmPrice li:last-child {
            margin-right: 0;
        }
        #previewPrmPrice li h2 {
            font-family: ThaiSansNeue-Bold;
        }
        #previewPrmPrice li p {
            font-size: 20px;
            margin-bottom: 20px;
        }
    </style>
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
        var ajaxUrl     = 'form_package_promotion_details.php';
        var refPkgData      = <?php echo json_encode($_smarty_tpl->tpl_vars['referenceData']->value["packages"]);?>
;
        var refPkgprmData   = <?php echo json_encode($_smarty_tpl->tpl_vars['referenceData']->value["todayOnward_package_promotions"]);?>
;
        var curPkgPrice     = null;
        var curPrdPrmGrpId     = '';

        // Check if not have product promotion now
        
        <?php if ($_smarty_tpl->tpl_vars['referenceData']->value["todayOnward_package_promotions"]==null) {?>
        parent.showActionDialog({
            title: 'ไม่พบโปรโมชั่นแพ็คเกจ',
            message: 'ไม่พบโปรโมชั่นแพ็คเกจที่สามารถจัดได้ในขณะนี้ กรุณาเพิ่มโปรโมชั่นแพ็คเกจก่อน',
            actionList: [
                {
                    id: 'ok',
                    name: 'ตกลง',
                    desc: 'ปิดฟอร์มเพิ่มข้อมูล',
                    func:
                    function() {
                        parent.closeManageBox();
                        parent.hideActionDialog();
                        self.val('');
                        self.focus();
                    }
                }
            ],
            boxWidth: 400
        });
        <?php }?>
        

        $(document).ready(function () {
            selectReferenceJS({
                elem            : $('#pkg_id'),
                data            : refPkgData,
                defaultValue    : '<?php echo $_smarty_tpl->tpl_vars['values']->value['pkg_id'];?>
',
                success         : 
                function () {
                    setCurPkgPrice();
                    calPreviewPkgPrice();
                },
                onOptionSelect  : 
                function() {
                    setCurPkgPrice();
                    validateDiscout();
                    calPreviewPkgPrice();
                }
            });

            selectReferenceJS({
                elem            : $('#pkgprm_id'),
                data            : refPkgprmData,
                defaultValue    : '<?php echo $_smarty_tpl->tpl_vars['values']->value['pkgprm_id'];?>
',
                onOptionSelect  :
                function() {
                    $('#pkgprmdtl_startdate').val('');
                    $('#pkgprmdtl_enddate').val('');
                    pkgprmIdChange();
                    validateDiscout();
                },
                success         : pkgprmIdChange
            });

            $('#pkgprmdtl_startdate').datetimepicker({
                lang                : 'th',
                format              : 'Y/m/d',
                timepicker          :false,
                closeOnDateSelect   :true,
                scrollInput         :false,
                yearOffset          :543,
                onSelectDate: 
                function(){
                  $('#pkgprmdtl_startdate').blur();
                },
                onShow:function( ct ){
                    if(action == 'ADD') {
                        this.setOptions({
                            minDate: realDateToTmpDate('<?php echo $_smarty_tpl->tpl_vars['nowDate']->value;?>
'),
                            maxDate:$('#pkgprmdtl_enddate').val()?unconvertThaiDate($('#pkgprmdtl_enddate').val()):false
                        });
                    } else if(action == 'EDIT') {
                        this.setOptions({
                            maxDate:$('#pkgprmdtl_enddate').val()?unconvertThaiDate($('#pkgprmdtl_enddate').val()):false
                        });
                    }
                },
                timepicker:false
            });

            $('#pkgprmdtl_enddate').datetimepicker({
                lang                : 'th',
                format              : 'Y/m/d',
                timepicker          :false,
                closeOnDateSelect   :true,
                scrollInput         :false,
                yearOffset          :543,
                onSelectDate: 
                function(){
                  $('#pkgprmdtl_enddate').blur();
                },
                onShow:function( ct ){
                    if(action == 'ADD') {
                        this.setOptions({
                            minDate:$('#pkgprmdtl_startdate').val()?unconvertThaiDate($('#pkgprmdtl_startdate').val()):realDateToTmpDate('<?php echo $_smarty_tpl->tpl_vars['nowDate']->value;?>
')
                        });
                    } else if(action="EDIT") {
                        this.setOptions({
                            minDate:$('#pkgprmdtl_startdate').val()?unconvertThaiDate($('#pkgprmdtl_startdate').val()):false
                        });
                    }
                },
                timepicker:false
            });

            // Check date
            $('#pkgprmdtl_startdate').change(function(){
                checkDate($(this), $('#pkgprmdtl_enddate'));
            });
            $('#pkgprmdtl_enddate').change(function() {
                checkDate($(this), $('#pkgprmdtl_startdate'));
            });

            // Change discout type
            $('input[name="pkgprmdtl_discout_type"]').change(function() {
                if($(this).val() == 'บาท') {
                    $('#pkgprmdtl_discout_type_text').text('บาท');
                } else if($(this).val() == '%') {
                    $('#pkgprmdtl_discout_type_text').text('%');
                }
                validateDiscout();
            });

            // Check discout
            $('#pkgprmdtl_discout').change(validateDiscout);

            // Set default radio input
            if(action == 'EDIT') {
                $('input[name="pkgprmdtl_discout_type"][value="<?php echo $_smarty_tpl->tpl_vars['values']->value['pkgprmdtl_discout_type'];?>
"]').click();
            }
        });

        function setCurPkgPrice() {
            var pkg_id = $('#pkg_id').find('.selectReferenceJS-input').val();
            if(pkg_id != '') {
                for(i in refPkgData) {
                    if(refPkgData[i].refValue == pkg_id) {
                        curPkgPrice = parseFloat(refPkgData[i].pkg_price);
                        break;
                    }
                } 
            }
        }

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
                if(selfId == 'pkgprmdtl_startdate') {
                    if(selfDate.getTime() > siblingDate.getTime()) {
                        showAlertInvalidDate(self, 'overSibling');
                        return;
                    }
                } else if(selfId == 'pkgprmdtl_enddate') {
                    if(selfDate.getTime() < siblingDate.getTime()) {
                        showAlertInvalidDate(self, 'overSibling');
                        return;
                    }
                }
            }

            if(action == 'ADD') {
                // Check now date
                if(selfId == 'pkgprmdtl_startdate') {
                    if(selfDate.getTime() < nowDate.getTime()) {
                        showAlertInvalidDate(self, 'lessThanNow');
                    }
                } else if(selfId == 'pkgprmdtl_enddate') {
                    if(selfDate.getTime() < nowDate.getTime()) {
                        showAlertInvalidDate(self, 'lessThanNow');
                    }
                }
            }

            // Check promotion date
            var pkgprm_id = $('#pkgprm_id').find('.selectReferenceJS-input').val();
            if(pkgprm_id != '') {
                for(i in refPkgprmData) {
                    if(refPkgprmData[i].refValue == pkgprm_id) {
                        var startDate = new Date(realDateToTmpDate(refPkgprmData[i].pkgprmdtl_startdate));
                        
                        if(refPkgprmData[i].pkgprmdtl_enddate == '') {
                            // no enddate
                            if(selfDate.getTime() < startDate.getTime()) {
                                showAlertInvalidDate(self, 'notInPrmTime');
                            }
                        } else {
                            // have enddate
                            var endDate = new Date(realDateToTmpDate(refPkgprmData[i].pkgprmdtl_enddate));

                            if(selfDate.getTime() < startDate.getTime() || 
                               selfDate.getTime() > endDate.getTime()) {
                                showAlertInvalidDate(self, 'notInPrmTime');
                            }
                        }
                        break;
                    }
                    
                }
            }
            
        }
        function showAlertInvalidDate(self, errType) {
            var titleTxt    = '';
            var messageTxt  = '';
            var descTxt     = '';
            var selfId      = self.attr('id');

            if(selfId == 'pkgprmdtl_startdate') {
                titleTxt    = 'วันที่เริ่มใช้ไม่ถูกต้อง';
                descTxt     = 'ป้อนวันที่เริ่มใช้ใหม่';
                if(errType == 'overSibling') {
                    messageTxt  = 'วันที่เริ่มใช้ไม่สามารถอยู่หลังวันที่สิ้นสุดได้ค่ะ';
                } else if(errType == 'lessThanNow') {
                    messageTxt  = 'ไม่สามารถป้อนวันที่เริ่มใช้ย้อนหลังได้ค่ะ';
                } else if(errType == 'notInPrmTime') {
                    var prdprmName = $('#pkgprm_id').find('.selectReferenceJS-text').text();
                    messageTxt = 'วันที่เริ่มใช้ไม่ได้อยู่ในระยะเวลาของโปรโมชั่น '
                               + prdprmName + ' ค่ะ';
                }
            } else if(selfId == 'pkgprmdtl_enddate') {
                titleTxt    = 'วันที่สิ้นสุดไม่ถูกต้อง';
                descTxt     = 'ป้อนวันที่สิ้นสุดใหม่';
                if(errType == 'overSibling') {
                    messageTxt  = 'วันที่สิ้นสุดไม่สามารถอยู่ก่อนหน้าวันที่เริ่มใช้ได้ค่ะ';
                } else if(errType == 'lessThanNow') {
                    messageTxt  = 'ไม่สามารถป้อนวันที่สิ้นสุดย้อนหลังได้ค่ะ';
                } else if(errType == 'notInPrmTime') {
                    var prdprmName = $('#pkgprm_id').find('.selectReferenceJS-text').text();
                    messageTxt = 'วันที่สิ้นสุดไม่ได้อยู่ในระยะเวลาของโปรโมชั่น '
                               + prdprmName + ' ค่ะ';
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

        function validateDiscout() {
            if($('#pkgprmdtl_discout').val() == '') {
                return;
            }

            var discout = parseFloat($('#pkgprmdtl_discout').val());

            if($('input[name="pkgprmdtl_discout_type"]:checked').val() == 'บาท') {
                if(curPkgPrice != null && discout >= curPkgPrice) {
                    var msg = $('#pkg_id').find('.selectReferenceJS-text').text()
                            + ' ราคา ' + curPkgPrice.formatMoney(2, '.', ',') + ' บาท '
                            + 'คุณไม่สามารถกำหนดส่วนลดให้มากกว่าหรือเท่ากับราคาแพ็คเกจได้';

                    parent.showActionDialog({
                        title: 'ส่วนลดไม่ถูกต้อง',
                        message: msg,
                        actionList: [
                            {
                                id: 'ok',
                                name: 'ตกลง',
                                desc: 'ป้อนส่วนลดใหม่',
                                func:
                                function() {
                                    parent.hideActionDialog();
                                    $('#pkgprmdtl_discout').val('');
                                    $('#pkgprmdtl_discout').focus();
                                    calPreviewPkgPrice();
                                }
                            }
                        ],
                        boxWidth: 400
                    });
                    return;
                }
            } else if($('input[name="pkgprmdtl_discout_type"]:checked').val() == '%') {
                if(curPkgPrice != null && discout >= 100) {
                    parent.showActionDialog({
                        title: 'ส่วนลดไม่ถูกต้อง',
                        message: 'เปอร์เซ็นส่วนลดต้องน้อยกว่า 100%',
                        actionList: [
                            {
                                id: 'ok',
                                name: 'ตกลง',
                                desc: 'ป้อนส่วนลดใหม่',
                                func:
                                function() {
                                    parent.hideActionDialog();
                                    $('#pkgprmdtl_discout').val('');
                                    $('#pkgprmdtl_discout').focus();
                                    calPreviewPkgPrice();
                                }
                            }
                        ],
                        boxWidth: 400
                    });
                    return;
                }
            }
            calPreviewPkgPrice();
        }

        function calPreviewPkgPrice() {
            var discout = parseFloat($('#pkgprmdtl_discout').val());

            // Package Price
            if(curPkgPrice != null) {
                $('#preview-pkg_price').text(curPkgPrice.formatMoney(2, '.', ','));
            }

            // Discout
            if($('#pkgprmdtl_discout').val() == '') {
                discout = 0.00;
                $('#preview-discout').text('0.00');
            } else {
                if($('input[name="pkgprmdtl_discout_type"]:checked').val() == '%') {
                    discout = Math.round(parseFloat(curPkgPrice * discout / 100));
                }
                $('#preview-discout').text(discout.formatMoney(2, '.', ','));
            }

            // Promotion Price
            if(curPkgPrice != null) {
                $('#preview-pkgPrm_price').text(parseFloat(curPkgPrice - discout).formatMoney(2, '.', ','));
            }
        }

        function beforeSaveRecord() {
            // Not check time overlap
            if($('#pkgprmdtl_startdate').val() == '') {
                return false;
            }

            var returnVal = true; // false = no err, true = have err

            $.ajax({
                url: '../common/ajaxCheckPackagePromotionDetailsTimeOverlap.php',
                type: 'POST',
                data: {
                    pkgprm_id       : code,
                    pkg_id          : $('input[name="pkg_id"]').val(),
                    startdate       : tmpDateToRealDate($('#pkgprmdtl_startdate').val()),
                    enddate         : tmpDateToRealDate($('#pkgprmdtl_enddate').val()),
                    prdprmgrp_id    : curPrdPrmGrpId
                },
                success:
                function(responseJSON) {
                    var response = $.parseJSON(responseJSON);
                    if(response.status == 'OVERLAP') {
                        var overlapListHTML = '<ul>';
                        for(i in response.overlapList) {
                            overlapListHTML += '<li>' + response.overlapList[i] + '</li>';
                        }
                        overlapListHTML += '</ul>';

                        var msg = 'คุณกำหนดระยะเวลาโปรโมชั่น' + response.prdprm_type + 'ของผลิตภัณฑ์ ' 
                                + $('#pkg_id').find('.selectReferenceJS-text').text()
                                + ' ซ้อนทับกับช่วงเวลาดังต่อไปนี้' + overlapListHTML 
                                + '<br>โปรดแก้ไขระยะเวลาโปรโมชั่นไม่ให้อยู่ในช่วงเวลาดังกล่าว';
                        parent.showActionDialog({
                            title: 'ระยะเวลาโปรโมชั่น' + response.prdprm_type + 'ซ้อนทับกัน',
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
                    } else if(response.status == 'NOT_OVERLAP') {
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

        function pkgprmIdChange() {
            for(i in refPkgprmData) {
                if(refPkgprmData[i].refValue == $('#pkgprm_id').find('.selectReferenceJS-input').val()) {
                    curPrdPrmGrpId = refPkgprmData[i].prdprmgrp_id;
                    // Gen promotion time text
                    var timeTxt = '(ระยะเวลาโปรโมชั่น: ';
                    if(refPkgprmData[i].pkgprmdtl_enddate == '') {
                        timeTxt += realDateToThaiDate(refPkgprmData[i].pkgprmdtl_startdate) + ' - ไม่มีกำหนด)';
                    } else if(refPkgprmData[i].pkgprmdtl_startdate == refPkgprmData[i].pkgprmdtl_enddate) {
                        timeTxt += 'เฉพาะวันที่ ' + realDateToThaiDate(refPkgprmData[i].pkgprmdtl_startdate) + ')';
                    } else {
                        timeTxt += realDateToThaiDate(refPkgprmData[i].pkgprmdtl_startdate) + ' - '
                                 + realDateToThaiDate(refPkgprmData[i].pkgprmdtl_enddate) + ')';
                    }
                    $('#pkgprmTime').text(timeTxt);
                    $('#pkgprmTime').css('display','block');

                    // Show for sale type
                    if(curPrdPrmType == 'ลดราคา') {
                        $('.showForSale').css('visibility', 'visible');
                        $('.discout_type_hidden').prop('disabled', true);
                        $('.discout_hidden').prop('disabled', true);
                        $('.discout_type_radio').prop('disabled', false);
                        $('#pkgprmdtl_discout').prop('disabled', false);
                    } else if(curPrdPrmType == 'ฟรี'){
                        $('.showForSale').css('visibility', 'hidden');
                        $('.discout_type_radio').prop('disabled', true);
                        $('#pkgprmdtl_discout').prop('disabled', true);
                        $('.discout_type_hidden').prop('disabled', false);
                        $('.discout_hidden').prop('disabled', false);
                        $('#pkgprmdtl_discout').removeClass('required');
                        $('.err-pkgprmdtl_discout').css('display', 'none');
                    }

                    // Add or remove require of enddate
                    if(refPkgprmData[i].pkgprmdtl_enddate == '') {
                        $('#pkgprmdtl_enddate').removeClass('required');
                        $('.err-pkgprmdtl_enddate').css('display', 'none');
                        $('#pkgprmdtl_enddate').removeAttr('require');
                        $('#prmprd_enddate_label').removeClass('input-required');
                    } else {
                        $('#pkgprmdtl_enddate').attr('require', '');
                        $('#prmprd_enddate_label').addClass('input-required');
                        $('#pkgprmdtl_enddate').focusout(validateInput);
                    }
                    break;
                }
            }
        }
    </script>
    
</head>
<body>

<?php echo $_smarty_tpl->getSubTemplate ("form_table_header.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array(), 0);?>

<div class="ftb-body">
    <?php if ($_smarty_tpl->tpl_vars['action']->value=='VIEW_DETAIL') {?>
    <!-- VIEW_DETAIL -->
    <table class="table-view-detail">
        <tbody>             
            <tr>
                <td>รหัสแพ็คเกจที่จัดโปรโมชั่น :</td>
                <td><?php echo $_smarty_tpl->tpl_vars['code']->value;?>
</td>
            </tr>
            <tr>
                <td>แพ็คเกจ :</td>
                <td>
                    <a href="form_table.php?action=VIEW_DETAIL&tableName=packages&code=<?php echo $_smarty_tpl->tpl_vars['values']->value['pkg_id'];?>
&hideEditButton=true" class="normal-link" title="คลิกเพื่อดูรายละเอียด">
                        <div id="pkg_id" class="selectReferenceJS text" style="cursor:pointer;"></div>
                    </a>
                </td>
            </tr>
            <tr>
                <td>โปรโมชั่นแพ็คเกจที่จัด :</td>
                <td>
                    <a href="form_table.php?action=VIEW_DETAIL&tableName=package_promotions&code=<?php echo $_smarty_tpl->tpl_vars['values']->value['pkgprm_id'];?>
&hideEditButton=true" class="normal-link" title="คลิกเพื่อดูรายละเอียด">
                        <div id="pkgprm_id" class="selectReferenceJS text" style="cursor:pointer;"></div>
                    </a>
                </td>
            </tr>
            <tr>
                <td>ส่วนลด :</td>
                <td>
                    <?php if ($_smarty_tpl->tpl_vars['values']->value['pkgprmdtl_discout']!='-') {?>
                        <?php echo $_smarty_tpl->tpl_vars['values']->value['pkgprmdtl_discout'];?>
 <?php echo $_smarty_tpl->tpl_vars['values']->value['pkgprmdtl_discout_type'];?>

                    <?php } else { ?>
                        ฟรี
                    <?php }?>
                </td>
            </tr>
            <tr>
                <td>วันที่เริ่มใช้ :</td>
                <td><?php echo $_smarty_tpl->tpl_vars['values']->value['pkgprmdtl_startdate'];?>
</td>
            </tr>
            <tr>
                <td>วันที่สิ้นสุด :</td>
                <td><?php if ($_smarty_tpl->tpl_vars['values']->value['pkgprmdtl_enddate']!='-') {?><?php echo $_smarty_tpl->tpl_vars['values']->value['pkgprmdtl_enddate'];?>
<?php } else { ?>ไม่มีกำหนด<?php }?></td>
            </tr>
        </tbody>
    </table>
    <!--For calculate PreviewPrmPrice-->
    <input name="pkgprmdtl_discout_type" type="radio" value="<?php echo $_smarty_tpl->tpl_vars['values']->value['pkgprmdtl_discout_type'];?>
" style="display:none;" checked>
    <input id="pkgprmdtl_discout" type="hidden" value="<?php echo $_smarty_tpl->tpl_vars['values']->value['pkgprmdtl_discout'];?>
"> 
    <?php } else { ?>      
    <!-- ADD, EDIT -->   
    <form id="form-table" name="form-table" onsubmit="return false;">
    <input type="hidden" name="requiredFields" value="pkgprm_id,pkg_id,pkgprmdtl_startdate,pkgprmdtl_discout,pkgprmdtl_discout_type">
    <table class="mbk-form-input-normal" cellpadding="0" cellspacing="0">
        <tbody>
            <tr>
                <td colspan="2">
                    <label class="input-required">แพ็คเกจ</label>
                    <div id="pkg_id" class="selectReferenceJS form-input full" require></div>
                </td>
            </tr>
            <tr class="errMsgRow">
                <td colspan="2">
                    <span id="err-pkg_id-require" class="errInputMsg half err-pkg_id">โปรดเลือกแพ็คเกจ</span>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <label class="input-required">โปรโมชั่นแพ็คเกจที่จัด</label>
                    <div id="pkgprm_id" class="selectReferenceJS form-input full" require></div>
                    <span id="pkgprmTime"></span>
                </td>
            </tr>
            <tr class="errMsgRow">
                <td colspan="2">
                    <span id="err-pkgprm_id-require" class="errInputMsg half err-pkgprm_id">โปรดเลือกโปรโมชั่น</span>
                </td>
            </tr>
            <tr>
                <td>
                    <label class="input-required">วันที่เริ่มใช้</label>
                    <input id="pkgprmdtl_startdate" name="pkgprmdtl_startdate" type="text" class="mbk-dtp-th form-input half" value="<?php echo $_smarty_tpl->tpl_vars['values']->value['pkgprmdtl_startdate'];?>
" require>
                </td>
                <td>
                    <label id="prmprd_enddate_label">วันที่สิ้นสุด</label>
                    <input id="pkgprmdtl_enddate" name="pkgprmdtl_enddate" type="text" class="mbk-dtp-th form-input half" value="<?php echo $_smarty_tpl->tpl_vars['values']->value['pkgprmdtl_enddate'];?>
">
                </td>
            </tr>
            <tr class="errMsgRow">
                <td>
                    <span id="err-pkgprmdtl_startdate-require" class="errInputMsg half err-pkgprmdtl_startdate">โปรดป้อนวันที่เริ่มใช้</span>
                </td>
                <td>
                    <span id="err-pkgprmdtl_enddate-require" class="errInputMsg half err-pkgprmdtl_enddate">โปรดป้อนวันที่สิ้นสุด</span>
                </td>
            </tr>
            <tr class="showForSale">
                <td colspan="2">
                    <label class="input-required">รูปแบบส่วนลด</label>
                    <label style="display:inline-block;"><input type="radio" name="pkgprmdtl_discout_type" value="บาท" class="discout_type_radio" checked> ราคาสุทธิ</label><br>
                    <label style="display:inline-block;"><input type="radio" name="pkgprmdtl_discout_type" value="%" class="discout_type_radio"> เปอร์เซ็น</label><br><br>
                </td>
            </tr>
            <tr class="showForSale">
                <td colspan="2">
                    <label class="input-required">ส่วนลด (<span id="pkgprmdtl_discout_type_text">บาท</span>)</label>
                    <input id="pkgprmdtl_discout" name="pkgprmdtl_discout" type="text" class="form-input full" value="<?php echo $_smarty_tpl->tpl_vars['values']->value['pkgprmdtl_discout'];?>
" valuepattern="money" require>
                </td>
            </tr>
            <tr class="errMsgRow showForSale">
                <td colspan="2">
                    <span id="err-pkgprmdtl_discout-require" class="errInputMsg half err-pkgprmdtl_discout">โปรดป้อนส่วนลด</span>
                    <span id="err-pkgprmdtl_discout-money" class="errInputMsg err-pkgprmdtl_discout">รูปแบบส่วนลดไม่ถูกต้อง ส่วนลดเป็นได้เฉพาะตัวเลขเท่านั้น</span>
                </td>
            </tr>
        </tbody>
    </table>
    </form>
    <?php }?>
    <ul id="previewPrmPrice" class="showForSale<?php if ($_smarty_tpl->tpl_vars['action']->value=='VIEW_DETAIL') {?> viewDetail<?php }?>">
        <li>
            <h2>ราคาปกติ</h2>
            <p>฿<span id="preview-pkg_price">0.00</span></p>
        </li>
        <li>
            <h2>ราคาส่วนลด</h2>
            <p style="color: rgb(208, 38, 38);">
                ฿<span id="preview-discout">0.00</span>
            </p>
        </li>
        <li>
            <h2>ราคาโปรโมชั่น</h2>
            <p style="color: rgb(41, 127, 210);">
                ฿<span id="preview-pkgPrm_price">0.00</span>
            </p>
        </li>
    </ul>
</div>
</body>
</html>
<!--
    [Note]
    1. ให้ใส่ field ที่ต้องการเช็คใน input[name="requiredFields"] โดยกำหนดชื่อฟิลด์ลงใน value หากมีมากกว่า 1 field ให้คั่นด้วยเครื่องหมาย คอมม่า (,) และห้ามมีช่องว่าง เช่น value="name,surname,address" เป็นต้น
    2. input จะต้องกำหนด id, name ให้ตรงกับชื่อฟิลด์ของตารางนั้นๆ และกำหนด value ให้มีรูปแบบ value="$values.ชื่อฟิลด์"
    3.  input[name="uniqueFields"] ใส่ชื่อฟิลด์ที่ต้องการเช็คว่าห้ามซ้ำ
--><?php }} ?>
