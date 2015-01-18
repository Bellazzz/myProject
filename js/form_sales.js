var oldPrdPrmGrpId   = '';
var oldPrdPrmGrpName = '';

$(document).ready(function() {
    // Create product input
    if(action == 'ADD') {
        addProduct({
            defaultValue : false
        });

        $('input[name="sale_totalPrice_no_saleDiscout"]').val(0);
    } else if(action == 'EDIT') {
        for(i in valuesDetail) {
             addProduct({
                defaultValue : true,
                saledtl_id   : valuesDetail[i].saledtl_id,
                prd_id      : valuesDetail[i].prd_id,
                prd_qty     : valuesDetail[i].saledtl_amount,
                unit_name   : valuesDetail[i].unit_name
            });
        }
        if(typeof(valuesPrmDetail) != 'undefined') {
            for(i in valuesPrmDetail) {
                if(valuesPrmDetail[i].prmprd_discout_type == null) { // null is promotion free
                    var tr  = $('input[value="' + valuesPrmDetail[i].saledtl_id + '"]').parent().parent();
                    var freeAmountInput = tr.find('input[name="freeAmount[]"]');
                    freeAmountInput.val(valuesPrmDetail[i].saleprmdtl_amount);
                    freeAmountInput.change();
                }
            }
        }
        for(i in valuesPrmSaleDetail) {
            if(valuesPrmSaleDetail[i].prmds_id == null) {
                // sale discout from manual
                var saleprmdsdtlIdHTML = '<input class="prmds_' + valuesPrmSaleDetail[i].prmds_id + '" type="hidden" name="saleprmdsdtl_id[]" value="' + valuesPrmSaleDetail[i].saleprmdsdtl_id + '">';
                $('#sale_discout_val').val(valuesPrmSaleDetail[i].saleprmdsdtl_discout);
                $('#sale_discout').val(valuesPrmSaleDetail[i].saleprmdsdtl_discout);
                $('.prmdsManual-td').append(saleprmdsdtlIdHTML);
            } else {
                // sale discout from promotion
                addPrmSaleDetail({
                    saleprmdsdtl_id         : valuesPrmSaleDetail[i].saleprmdsdtl_id,
                    prmds_id                : valuesPrmSaleDetail[i].prmds_id,
                    saleprmdsdtl_discout    : valuesPrmSaleDetail[i].saleprmdsdtl_discout 
                });
            }
        }
        calSummary();
    }

    $('#sale_discout_val').change(checkSaleDiscout);
    $('input[name="sale_discout_type"]').click(checkSaleDiscout);
});

function saveOldPrdPrmGrp() {
    oldPrdPrmGrpId      = $('input[name="prdprmgrp_id"]').val();
    oldPrdPrmGrpName    = $('#prdprmgrp_id').find('.selectReferenceJS-text').text();
    return true;
}

function changePrdprmGrpId() {
    var prdprmgrpId = $('input[name="prdprmgrp_id"]').val();
    var msg         = 'การเปลี่ยนกลุ่มโปรโมชั่นจำเป็นต้องเคลียร์ข้อมูลรายละเอียดการขายใหม่ '
                    + 'คุณแน่ใจหรือไม่ที่จะเปลี่ยนกลุ่มโปรโมชั่น?';
    parent.showActionDialog({
        title: 'เปลี่ยนกลุ่มโปรโมชั่น',
        message: msg,
        actionList: [
            {
                id: 'change',
                name: 'เปลี่ยน',
                desc: 'ข้อมูลรายละเอียดการขายจะถูกเคลียร์',
                func:
                function() {
                    $('#order-detail-table tr:not(.headTable-row)').remove();
                    addProduct({
                        defaultValue : false
                    });
                    calSummary();
                    parent.hideActionDialog();
                }
            },
            {
                id: 'cancel',
                name: 'ยกเลิก',
                desc: 'ยกเลิกการเปลี่ยนกลุ่มโปรโมชั่น',
                func:
                function() {
                    parent.hideActionDialog();
                    $('#prdprmgrp_id').find('.selectReferenceJS-input').val(oldPrdPrmGrpId);
                    $('#prdprmgrp_id').find('.selectReferenceJS-text').text(oldPrdPrmGrpName);
                }
            }
        ],
        boxWidth: 500
    });
}

function allowChangePrdPrmGrpId() {
    if(action == 'EDIT') {
        parent.showActionDialog({
            title: 'ไม่สามารถเปลี่ยนกลุ่มโปรโมชั่นได้',
            message: 'การขายที่บันทึกแล้วไม่สามารถแก้ไขกลุ่มโปรโมชั่นได้',
            actionList: [
                {
                    id: 'ok',
                    name: 'ตกลง',
                    func:
                    function() {
                        parent.hideActionDialog();
                        return false;
                    }
                }
            ],
            boxWidth: 500
        });
    } else {
        return true;
    }
}

function checkSaleDiscout() {
    var discoutType = $('input[name="sale_discout_type"]:checked').val();
    if(discoutType == 'บาท') {
            if(parseFloat($('#sale_discout_val').val()) > parseFloat($('input[name="sale_totalPrice_no_saleDiscout"]').val())) {
               if($('.action-dialog').length > 0) {
                    return;
               }
                parent.showActionDialog({
                    title: 'ส่วนลดการขายไม่ถูกต้อง',
                    message: 'ส่วนลดการขายมากกว่าราคาสุทธิ กรุณากรอกส่วนลดการขายใหม่',
                    actionList: [
                        {
                            id: 'ok',
                            name: 'ตกลง',
                            desc: 'แก้ไขส่วนลดการขายใหม่',
                            func:
                            function() {
                                $('#sale_discout_val').val('');
                                $('#sale_discout_val').focus();
                                calSummary();
                                parent.hideActionDialog();
                            }
                        }
                    ],
                    boxWidth: 400
                });
            } else {
                calSummary();
            }
        } else if(discoutType == '%') {
            if(parseFloat($('#sale_discout_val').val()) > 100) {
                if(parent.$('.action-dialog').length > 0) {
                    return;
               }
                parent.showActionDialog({
                    title: 'ส่วนลดการขายไม่ถูกต้อง',
                    message: 'ไม่สามารถกำหนดส่วนลดมากกว่า 100% ได้ กรุณากรอกส่วนลดการขายใหม่',
                    actionList: [
                        {
                            id: 'ok',
                            name: 'ตกลง',
                            desc: 'แก้ไขส่วนลดการขายใหม่',
                            func:
                            function() {
                                $('#sale_discout_val').val('');
                                $('#sale_discout_val').focus();
                                calSummary();
                                parent.hideActionDialog();
                            }
                        }
                    ],
                    boxWidth: 400
                });
            } else {
                calSummary();
            }
        } else {
            calSummary();
        }
}

function addProduct(data) {
    var randNum;
    var selectRefDefault = '';
    var unitName = '';
    do {
        randNum     = parseInt(Math.random()*1000);
    } while($('#prd_id_' + randNum).length > 0);
    var inputKeyId      = 'prd_id_' + randNum;
    var inputQtyId      = 'prd_qty_' + randNum;
    var inputFreeQtyId  = 'prd_free_qty_' + randNum;
    if(typeof(data.unit_name) != 'undefined' && data.unit_name != '') {
        unitName = data.unit_name;
    }

    // Create HTML and append
    var prdRowHTML  = '<tr class="product-row">'
                    + '     <td>'
                    + '         <div id="' + inputKeyId + '" class="selectReferenceJS form-input full" require></div>'
                    + '     </td>'
                    + '     <td>'
                    + '         <span class="prd_unit_price">0.00</span>'
                    + '     </td>'
                    + '     <td>';

    // add input product amount
    if(data.defaultValue) {
        prdRowHTML += '         <input id="' + inputQtyId + '" name="prd_qty[]" type="text" class="form-input" value="' + data.prd_qty + '" maxlength="6" size="6" valuepattern="number" require style="text-align:right;width:100px;">';
        selectRefDefault = data.prd_id;
    } else {
        prdRowHTML += '         <input id="' + inputQtyId + '" name="prd_qty[]" type="text" class="form-input" value="1" maxlength="6" size="6" valuepattern="numberMoreThanZero" require style="text-align:right;width:100px;">';
    }

    // add order detail id for update
    if(action == 'EDIT' && typeof(data.saledtl_id) != 'undefined') {
        prdRowHTML += '         <input name="saledtl_id[]" type="hidden" value="' + data.saledtl_id + '">';
    }
        prdRowHTML += '     </td>'
                    + '     <td>'
                    + '         <input id="' + inputFreeQtyId + '" type="text" name="freeAmount[]" value="0" class="form-input readonly" readonly  require valuepattern="number" style="width: 100px;text-align:right;">'
                    + '     </td>'
                    + '     <td>'
                    + '         <span class="prd_sumPrm_price">0.00</span>'
                    + '     </td>'
                    + '     <td>'
                    + '         <span class="saledtl_price_txt">0.00</span>'
                    + '         <input type="hidden" name="saledtl_price[]" value="0">'
                    + '     </td>'
                    + '     <td>'
                    + '         <button class="removeProductBtn button button-icon button-icon-delete" onclick="removeProduct(\'' + randNum + '\')">ลบ</button>'
                    + '     </td>'
                    + '</tr>'
                    + '<tr id="errMsgRow_' + randNum + '" class="errMsgRow">'
                    + '     <td>'
                    + '         <span id="err-' + inputKeyId + '-require" class="errInputMsg half err-' + inputKeyId + '">'
                    + '             โปรดเลือกผลิตภัณฑ์'
                    + '         </span>'
                    + '     </td>'
                    + '     <td></td>'
                    + '     <td>'
                    + '         <span id="err-' + inputQtyId + '-require" class="errInputMsg half err-' + inputQtyId + '" style="width:100px;">'
                    + '             โปรดกรอกจำนวนที่ขาย'
                    + '         </span>'
                    + '         <span id="err-' + inputQtyId + '-numberMoreThanZero" class="errInputMsg half err-' + inputQtyId + '" style="width:100px;">'
                    + '             โปรดกรอกจำนวนเป็นตัวเลขจำนวนเต็มตั้งแต่ 1 ขึ้นไป'
                    + '         </span>'
                    + '     </td>'
                    + '     <td>'
                    + '         <span id="err-' + inputFreeQtyId + '-require" class="errInputMsg half err-' + inputFreeQtyId + '" style="width:100px;">'
                    + '             โปรดกรอกจำนวนที่ฟรี'
                    + '         </span>'
                    + '         <span id="err-' + inputFreeQtyId + '-number" class="errInputMsg half err-' + inputFreeQtyId + '" style="width:100px;">'
                    + '             โปรดกรอกจำนวนเป็นตัวเลขจำนวนเต็ม'
                    + '         </span>'
                    + '     </td>'
                    + '</tr>'
                    + '<tr id="productPrmRow_' + randNum + '" class="product-prm-row">'
                    + '     <td colspan="6"></td>'
                    + '</tr>';
    $('#order-detail-table > tbody').append(prdRowHTML);

    // Create select reference
    selectReferenceJS({
        elem            : $('#' + inputKeyId),
        data            : refProducts,
        defaultValue    : selectRefDefault,
        onOptionSelect  :
        function() {
            checkShelfAmountCover($('#' + inputKeyId).find('input[name="prd_id[]"]').val());
            pullUnitPrice(inputKeyId);
            addPrmSale($('#' + inputKeyId).find('input[name="prd_id[]"]').val());
            addPrmFree($('#' + inputKeyId).find('input[name="prd_id[]"]').val());
            calSummary();
        },
        success         : 
        function() {
            $('input[name="' + inputKeyId + '"]').attr('name', 'prd_id[]');
            setAllSalePrd();
            pullUnitPrice(inputKeyId);
            addPrmSale($('#' + inputKeyId).find('input[name="prd_id[]"]').val());
            addPrmFree($('#' + inputKeyId).find('input[name="prd_id[]"]').val());
            calSummary();
        },
        group           : 'products'
    });
    // Check Input required and pattern
    $('#' + inputQtyId).focusout(validateInput);
    $('#' + inputFreeQtyId).focusout(validateInput);
    // Calculate sum price
    $('#' + inputQtyId).change(function() {
        if(validateFreeAmount()) {
            checkShelfAmountCover($('#' + inputKeyId).find('input[name="prd_id[]"]').val());
            var sumPriceInput = $(this).parent().parent().find('input[name="saledtl_price[]"]');
            calSumPriceInput(sumPriceInput);
            addPrmSale($('#' + inputKeyId).find('input[name="prd_id[]"]').val());
            addPrmFree($('#' + inputKeyId).find('input[name="prd_id[]"]').val());
            calSummary();
        }
    });
    $('#' + inputFreeQtyId).change(function() {
        if(validateFreeAmount()) {
            addPrmSale($('#' + inputKeyId).find('input[name="prd_id[]"]').val());
            addPrmFree($('#' + inputKeyId).find('input[name="prd_id[]"]').val());
            calSummary();
        }
    });

    function validateFreeAmount() {
        if($('#' + inputQtyId).val() != '') {
            var saleQty = parseInt($('#' + inputQtyId).val());
            var freeQty = parseInt($('#' + inputFreeQtyId).val());
            if(freeQty > saleQty) {
                var prdName = $('#' + inputKeyId).find('.selectReferenceJS-text').text();
                parent.showActionDialog({
                    title: 'จำนวนฟรีไม่ถูกต้อง',
                    message: prdName+ ' มีจำนวนฟรีมากกว่าจำนวนที่ขาย กรุณากรอกจำนวนฟรีใหม่',
                    actionList: [
                        {
                            id: 'ok',
                            name: 'ตกลง',
                            desc: 'แก้ไขจำนวนฟรีใหม่',
                            func:
                            function() {
                                $('#' + inputFreeQtyId).val('');
                                $('#' + inputFreeQtyId).focus();
                                parent.hideActionDialog();
                            }
                        }
                    ],
                    boxWidth: 400
                });
                return false;
            }
        }
        return true;
    }
}

function removeProduct(randNum) {
    if($('.removeProductBtn').length > 1) {
        var selectRef   = $('#prd_id_' + randNum);
        var tr          = selectRef.parent().parent();
        var txt         = selectRef.find('.selectReferenceJS-text').text();
        var val         = selectRef.find('.selectReferenceJS-input').val();
        var msg         = '';
        if(val != '') {
            msg = 'คุณต้องการลบผลิตภัณฑ์ ' + txt + ' ออกจากการขายครั้งนี้ใช่หรือไม่?';
        } else {
            msg = 'คุณต้องการลบผลิตภัณฑ์ที่เลือกออกจากการขายครั้งนี้ใช่หรือไม่?';
        }
        parent.showActionDialog({
            title: 'ลบผลิตภัณฑ์',
            message: msg,
            actionList: [
                {
                    id: 'ok',
                    name: 'ตกลง',
                    desc: 'ลบผลิตภัณฑ์นี้ออกจากการขาย',
                    func:
                    function() {
                        parent.hideActionDialog();
                        tr.remove();
                        $('#productPrmRow_' + randNum).remove();
                        $('#errMsgRow_' + randNum).remove();
                        setAllSalePrd();
                        calSummary();
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
            title: 'ไม่สามารถลบผลิตภัณฑ์ได้',
            message: 'รายการผลิตภัณฑ์ที่ขายต้องมีอย่างน้อย 1 รายการค่ะ',
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

function addPrmSaleDetail(data) {
    var prmSaleHTML = '';
    if(typeof(data.saleprmdsdtl_id) != 'undefined') {
        prmSaleHTML += '<input class="prmds_' + data.prmds_id + '" type="hidden" name="saleprmdsdtl_id[]" value="' + data.saleprmdsdtl_id + '">';
    }
    prmSaleHTML += '<input class="prmds_' + data.prmds_id + '" type="hidden" name="prmds_id[]" value="' + data.prmds_id + '">'
                 + '<input class="prmds_' + data.prmds_id + '" type="hidden" name="saleprmdsdtl_discout[]" value="' + data.saleprmdsdtl_discout + '">';
    $('.prmds-td').append(prmSaleHTML);
}

function checkShelfAmountCover(prd_id) {
    var tr          = $('input[value="' + prd_id + '"]').parent().parent().parent();
    var curAmount   = tr.find('input[name="prd_qty[]"]').val();
    if(prd_id != '' && curAmount != '') {
        var oldAmount   = 0;
        curAmount       = parseInt(curAmount);

        if(typeof(valuesDetail) != 'undefined') {
            for(i in valuesDetail) {
                if(i == prd_id) {
                    oldAmount = parseInt(valuesDetail[i].saledtl_amount);
                    break;
                }
            }
        }
        if(curAmount > oldAmount) {
            var newAmount = curAmount - oldAmount;
            $.ajax({
                url: '../common/ajaxFormSalesCheckPrdShelfAmountCover.php',
                type: 'POST',
                data: {
                    prd_id: prd_id,
                    qty: newAmount
                },
                success:
                function(responseJSON) {
                    var response = $.parseJSON(responseJSON);
                    if(response.status == 'NOT_ENOUGH') {
                        parent.showActionDialog({
                            title: 'สินค้าไม่เพียงพอ',
                            message: response.prd_name + ' มีจำนวนสินค้าที่วางขายไม่เพียงพอ'
                                     + ' ขาดอีก ' + response.overAmount,
                            actionList: [
                                {
                                    id: 'ok',
                                    name: 'ตกลง',
                                    desc: 'แก้ไขจำนวนที่ขายใหม่',
                                    func:
                                    function() {
                                        parent.hideActionDialog();
                                        tr.find('input[name="prd_qty[]"]').val('');
                                        tr.find('input[name="prd_qty[]"]').change();
                                        tr.find('input[name="prd_qty[]"]').focus();
                                    }
                                }
                            ],
                            boxWidth: 400
                        });
                    } else if(response.status == 'FAIL') {
                        alert('ไม่พบข้อมูลสินค้ารหัส ' + prd_id);
                    } else if(response.status != 'ENOUGH') {
                        alert(response.status);
                    }
                }
            });
        }
    }
}

function setAllSalePrd() {
    $('#allSalePrd').text($('input[name="prd_id[]"]').length);
}

function pullUnitPrice(inputKeyId) {
    var prdID       = $('#' + inputKeyId).find('.selectReferenceJS-input').val();
    
    if(typeof(prdID) != 'undefined' && prdID != '') {
        var prdUnitPrice = $('#' + inputKeyId).parent().parent().find('.prd_unit_price');
        var unitPrice    = '';
        unitPrice = parseFloat(products[prdID].prd_price).formatMoney(2, '.', '');
        prdUnitPrice.text(unitPrice);
        calSumPriceInput($('#' + inputKeyId).parent().parent().find('input[name="saledtl_price[]"]'));
    }
}

function calSumPriceInput(sumPriceInput) {
    var unitPrice   = parseFloat(sumPriceInput.parent().parent().find('.prd_unit_price').text());
    var qtyInput    = sumPriceInput.parent().parent().find('input[name="prd_qty[]"]');
    var sumPriceTxt = sumPriceInput.parent().parent().find('.saledtl_price_txt');
    var qty         = validateMoney(qtyInput.val()) &&  qtyInput.val() != '' ? parseInt(qtyInput.val()) : 0;
    var sumPrice    = parseFloat(unitPrice * qty);
    sumPriceInput.val(sumPrice.formatMoney(2, '.', ''));
    sumPriceTxt.text(sumPrice.formatMoney(2, '.', ','));
}

function addPrmSale(prd_id) {
    // Skip
    if(prd_id == '' || promotions == '') {
        return;
    }

    var prdprmgrp_id    = typeof($('input[name="prdprmgrp_id"]').val()) != 'undefined' ? $('input[name="prdprmgrp_id"]').val() : curPrdPrmGrpId;
    var tdPrm           = $('input[value="' + prd_id + '"]').parent().parent().parent().next().next().find('td');
    var freeAmount      = $('input[value="' + prd_id + '"]').parent().parent().parent().find('input[name="freeAmount[]"]');
    var amount          = parseInt($('input[value="' + prd_id + '"]').parent().parent().parent().find('input[name="prd_qty[]"]').val());
    
    if(parseInt(freeAmount.val()) > 0 && validateNumber(freeAmount.val())) {
        amount -= parseInt(freeAmount.val());
    }

    if(amount == '' || !validateMoney(amount)) {
        removePrm(tdPrm.find('.prmSale'));
        return;
    }

    for(prdID in promotions[prdprmgrp_id]) {
        for(prmType in promotions[prdprmgrp_id][prdID]) {
            if(prdID == prd_id && prmType == 'sale') {
                var prm         = promotions[prdprmgrp_id][prdID][prmType];

                // add first amount
                removePrm(tdPrm.find('.prmSale'));
                var unitPrice   = parseFloat(products[prd_id].prd_price);
                var discout     = parseFloat(prm.prmprd_discout);
                if(prm.prmprd_discout_type == '%') {
                    discout = parseFloat(unitPrice * discout / 100);
                }
                var sumDiscout  = parseFloat(amount * discout);
                var prmHTML     = '<div class="prmSale prm-list">'
                                + ' <div class="prm-thumb" style="background-image:url(\'../img/product_promotions/' + prm.prdprm_picture + '\');"></div>'
                                + ' <table>'
                                + '     <tr>'
                                + '         <td class="prm-name-col">'
                                + '             <span class="prm-name">' + prm.prdprm_name + '</span><br>'
                                + '             <span class="discout-rate">ลดราคา ' + prm.prmprd_discout + ' ' + prm.prmprd_discout_type + '</span>'
                                + '         </td>'
                                + '         <td class="amount-col">'
                                + '             <span class="prm-amount">' + amount + '</span>' 
                                + '         </td>'
                                + '         <td class="discout-col">'
                                + '             <span class="prm-discout">' + sumDiscout.formatMoney(2, '.', ',') + '</span>' 
                                + '         </td>'
                                + '     </tr>'
                                + ' </table>'
                                + ' <input type="hidden" class="prm_id" name="prmSale_' + prd_id + '_prmprd_id" value="' + prm.prmprd_id + '">'
                                + ' <input type="hidden" class="prm_name" name="prmSale_' + prd_id + '_prdprm_name" value="' + prm.prdprm_name + '">'
                                + ' <input type="hidden" class="prm_amount" name="prmSale_' + prd_id + '_saleprmdtl_amount" value="' + amount + '">'
                                + ' <input type="hidden" class="prm_sumDiscout" name="prmSale_' + prd_id + '_saleprmdtl_discout" value="' + sumDiscout + '">'
                                + ' <input type="hidden" class="prm_discout" name="prmSale_' + prd_id + '_discout" value="' + prm.prmprd_discout + '">'
                                + ' <input type="hidden" class="prm_discoutType" name="prmSale_' + prd_id + '_discout_type" value="' + prm.prmprd_discout_type + '">'
                                + '</div>';
                if(tdPrm.find('.prm-list-container').length <= 0) {
                    var prmCont     = '<div class="prm-list-container"></div>';
                    tdPrm.append(prmCont);
                }                
                tdPrm.find('.prm-list-container').append(prmHTML);
                return;
            }
        }
    }
    removePrm(tdPrm.find('.prmSale'));
}

function addPrmFree(prd_id) {
    // Skip
    if(prd_id == '' || promotions == '') {
        return;
    }

    var prdprmgrp_id    = typeof($('input[name="prdprmgrp_id"]').val()) != 'undefined' ? $('input[name="prdprmgrp_id"]').val() : curPrdPrmGrpId;
    var tdPrm           = $('input[value="' + prd_id + '"]').parent().parent().parent().next().next().find('td');
    var saleAmount      = parseInt($('input[value="' + prd_id + '"]').parent().parent().parent().find('input[name="prd_qty[]"]').val());
    var amount          = parseInt($('input[value="' + prd_id + '"]').parent().parent().parent().find('input[name="freeAmount[]"]').val());
    var freeAmountInput = $('input[value="' + prd_id + '"]').parent().parent().parent().find('input[name="freeAmount[]"]');

    if(saleAmount == '' || !validateMoney(saleAmount)) {
        removePrm(tdPrm.find('.prmFree'));
        return;
    }

    for(prdID in promotions[prdprmgrp_id]) {
        for(prmType in promotions[prdprmgrp_id][prdID]) {
            if(prdID == prd_id && prmType == 'free') {
                freeAmountInput.removeClass('readonly');
                freeAmountInput.attr('readonly', false);
                if(amount <= 0 || !validateNumber(freeAmountInput.val()) || freeAmountInput.val() == '') {
                    removePrm(tdPrm.find('.prmFree'));
                    return;
                }

                var prm         = promotions[prdprmgrp_id][prdID][prmType];

                // add first amount
                removePrm(tdPrm.find('.prmFree'));
                var unitPrice   = parseFloat(products[prd_id].prd_price);
                var sumDiscout  = parseFloat(amount * unitPrice);
                
                var prmHTML     = '<div class="prmFree prm-list">'
                                + ' <div class="prm-thumb" style="background-image:url(\'../img/product_promotions/' + prm.prdprm_picture + '\');"></div>'
                                + ' <table>'
                                + '     <tr>'
                                + '         <td class="prm-name-col">'
                                + '             <span class="prm-name">' + prm.prdprm_name + '</span><br>'
                                + '             <span class="discout-rate">ฟรี</span>'
                                + '         </td>'
                                + '         <td class="amount-col">'
                                + '             <span class="prm-amount">' + amount + '</span>' 
                                + '         </td>'
                                +               '<td class="discout-col">'
                                + '             <span class="prm-discout">' + sumDiscout.formatMoney(2, '.', ',') + '</span>' 
                                + '         </td>'
                                + '     </tr>'
                                + ' </table>'
                                + ' <input type="hidden" class="prm_id" name="prmFree_' + prd_id + '_prmprd_id" value="' + prm.prmprd_id + '">'
                                + ' <input type="hidden" class="prm_name" name="prmFree_' + prd_id + '_prdprm_name" value="' + prm.prdprm_name + '">'
                                + ' <input type="hidden" class="prm_amount" name="prmFree_' + prd_id + '_saleprmdtl_amount" value="' + amount + '">'
                                + ' <input type="hidden" class="prm_sumDiscout" name="prmFree_' + prd_id + '_saleprmdtl_discout" value="' + sumDiscout + '">'
                                + '</div>';
                if(tdPrm.find('.prm-list-container').length <= 0) {
                    var prmCont     = '<div class="prm-list-container"></div>';
                    tdPrm.append(prmCont);
                }                
                tdPrm.find('.prm-list-container').append(prmHTML);
                return;
            }
        }
    }
    removePrm(tdPrm.find('.prmFree'));
}

function removePrm(prmList) {
    var container = prmList.parent();
    prmList.remove();
    if(container.html() == '') {
        container.remove();
    }
}

function calSummary() {
    var sale_prm_discout    = 0;
    var sale_discout        = 0;
    var totalPrice          = 0;
    var saleDiscoutPrm      = 0;

    // Cal sum promotion discout
    $('input[name="prd_id[]"]').each(function() {
        var tdPrm               = $(this).parent().parent().parent().next().next().find('td');
        var prd_sumPrm_price    = $(this).parent().parent().parent().find('.prd_sumPrm_price');
        var saledtl_price_txt   = $(this).parent().parent().parent().find('.saledtl_price_txt');
        var saledtl_price       = parseFloat($(this).parent().parent().parent().find('input[name="saledtl_price[]"]').val());
        var sumPrmDiscout       = 0;

        tdPrm.find('.prm_sumDiscout').each(function() {
            sumPrmDiscout += parseFloat($(this).val());
        });
        saledtl_price_txt.text((saledtl_price - sumPrmDiscout).formatMoney(2, '.', ','));
        prd_sumPrm_price.text(sumPrmDiscout.formatMoney(2, '.', ''));
        sale_prm_discout += parseFloat(sumPrmDiscout);
    });
    $('#sale_prm_discout').val(sale_prm_discout.formatMoney(2, '.', ''));

    // Cal total price
    $('input[name="saledtl_price[]"]').each(function() {
        totalPrice += parseFloat($(this).val());
    });

    // Sale discout promotion
    var prdprmgrp_id = $('input[name="prdprmgrp_id"]').val();
    if(typeof(promotionSaleGroup) != 'undefined' && prdprmgrp_id != '') {
        var prmdsSumPurchase = Array();
        $('input[name="prd_id[]"]').each(function() {
            var prd_id          = $(this).val();
            var prdtyp_id       = '';
            if(typeof(products[prd_id]) != 'undefined') {
                prdtyp_id       = products[prd_id].prdtyp_id;
            }

            for(i in promotionSaleGroup) {
                for(j in promotionSaleGroup[i]) {
                    if(i == prdprmgrp_id && j == prdtyp_id) {
                        var prmds_id = promotionSaleGroup[i][j];
                        if(typeof(prmdsSumPurchase[prmds_id]) == 'undefined') {
                            prmdsSumPurchase[prmds_id] = 0;
                        }
                        prmdsSumPurchase[prmds_id] += parseFloat($(this).parent().parent().parent().find('.saledtl_price_txt').text().replace(',',''));
                        break;
                    }
                }
            }
        });
        for(i in prmdsSumPurchase) {
            if(prmdsSumPurchase[i] >= promotionSale[i].prmds_purchase) {
                var prmds_discout =  promotionSale[i].prmds_discout;
                if(promotionSale[i].prmds_discout_type == '%') {
                    prmds_discout = parseFloat(totalPrice * prmds_discout / 100);
                }
                if($('.prmds_' + i).length == 0) {
                    // Add
                    var prmdsHTML   = '<input class="prmds_' + i + '" type="hidden" name="prmds_id[]" value="' + i + '">'
                                    + '<input class="prmds_' + i + '" type="hidden" name="saleprmdsdtl_discout[]" value="' + prmds_discout + '">';
                    $('.prmds-td').append(prmdsHTML);
                } else {
                    // Update
                    $('.prmds_' + i + '[name="saleprmdsdtl_discout[]"]').val(prmds_discout);
                }
            } else {
                // Remove
                $('.prmds_' + i).remove();
            }
        }
    }
    $('.prmds-td input[name="saleprmdsdtl_discout[]"]').each(function() {
        saleDiscoutPrm += parseFloat($(this).val());
    });
    $('#prmds_sum_discout').val(saleDiscoutPrm.formatMoney(2, '.', ''));

    $('input[name="sale_totalPrice_no_saleDiscout"]').val(totalPrice - sale_prm_discout);
    if($('#sale_discout_val').val() != '' && validateMoney($('#sale_discout_val').val()) && totalPrice > 0) {
        if($('input[name="sale_discout_type"]:checked').val() == 'บาท'){
            sale_discout = parseFloat($('#sale_discout_val').val());
        } else if($('input[name="sale_discout_type"]:checked').val() == '%'){
            sale_discout = (totalPrice - sale_prm_discout) * parseFloat($('#sale_discout_val').val()) / 100;
        }
        $('#sale_discout').val(sale_discout);

    }
    
    totalPrice -= sale_discout + sale_prm_discout + saleDiscoutPrm;
    $('#sale_total_price').val(totalPrice.formatMoney(2, '.', ''));

}