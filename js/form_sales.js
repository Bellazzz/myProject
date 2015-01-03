$(document).ready(function() {
    // Create product input
    if(action == 'ADD') {
        addProduct({
            defaultValue : false
        });
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
    }
});

function addProduct(data) {
    var randNum;
    var selectRefDefault = '';
    var unitName = '';
    do {
        randNum     = parseInt(Math.random()*1000);
    } while($('#prd_id_' + randNum).length > 0);
    var inputKeyId  = 'prd_id_' + randNum;
    var inputQtyId  = 'prd_qty_' + randNum;
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
                    + '             โปรดกรอกจำนวนที่สั่งซื้อ'
                    + '         </span>'
                    + '         <span id="err-' + inputQtyId + '-numberMoreThanZero" class="errInputMsg half err-' + inputQtyId + '" style="width:100px;">'
                    + '             โปรดกรอกจำนวนเป็นตัวเลขจำนวนเต็มตั้งแต่ 1 ขึ้นไป'
                    + '         </span>'
                    + '     </td>'
                    + '</tr>'
                    + '<tr id="productPrmRow_"' + randNum + ' class="product-prm-row">'
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
            pullUnitPrice(inputKeyId);
            addPrmSale($('#' + inputKeyId).find('input[name="prd_id[]"]').val());
            calSummary();
        },
        success         : 
        function() {
            $('input[name="' + inputKeyId + '"]').attr('name', 'prd_id[]');
            setAllSalePrd();
        },
        group           : 'products'
    });
    // Check Input required and pattern
    $('#' + inputQtyId).focusout(validateInput);
    // Calculate sum price
    $('#' + inputQtyId).change(function() {
        var sumPriceInput = $(this).parent().parent().find('input[name="saledtl_price[]"]');
        calSumPriceInput(sumPriceInput);
        addPrmSale($('#' + inputKeyId).find('input[name="prd_id[]"]').val());
        calSummary();
    });
}

function removeProduct(randNum) {
    if($('.removeProductBtn').length > 1) {
        var selectRef   = $('#prd_id_' + randNum);
        var tr          = selectRef.parent().parent();
        var txt         = selectRef.find('.selectReferenceJS-text').text();
        var val         = selectRef.find('.selectReferenceJS-input').val();
        var msg         = '';
        if(val != '') {
            msg = 'คุณต้องการลบผลิตภัณฑ์ ' + txt + ' ออกจากการสั่งซื้อครั้งนี้ใช่หรือไม่?';
        } else {
            msg = 'คุณต้องการลบผลิตภัณฑ์ที่เลือกออกจากการสั่งซื้อครั้งนี้ใช่หรือไม่?';
        }
        parent.showActionDialog({
            title: 'ลบผลิตภัณฑ์',
            message: msg,
            actionList: [
                {
                    id: 'ok',
                    name: 'ตกลง',
                    desc: 'ลบผลิตภัณฑ์นี้ออกจากการสั่งซื้อ',
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
            message: 'รายการผลิตภัณฑ์ที่สั่งซื้อต้องมีอย่างน้อย 1 รายการค่ะ',
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

    var prdprmgrp_id    = $('input[name="prdprmgrp_id"]').val();
    var tdPrm           = $('input[value="' + prd_id + '"]').parent().parent().parent().next().next().find('td');
    var amount          = parseInt($('input[value="' + prd_id + '"]').parent().parent().parent().find('input[name="prd_qty[]"]').val());
    
    if(amount == '' || !validateMoney(amount)) {
        tdPrm.find('.prmSale').remove();
        return;
    }

    for(prdID in promotions[prdprmgrp_id]) {
        for(prmType in promotions[prdprmgrp_id][prdID]) {
            if(prdID == prd_id && prmType == 'sale') {
                var prm         = promotions[prdprmgrp_id][prdID][prmType];

                // add first amount
                tdPrm.find('.prmSale').remove();
                var unitPrice   = parseFloat(products[prd_id].prd_price);
                var discout     = parseFloat(prm.prmprd_discout);
                if(prm.prmprd_discout_type == '%') {
                    discout = parseFloat(unitPrice * discout / 100);
                }
                var sumDiscout  = parseFloat(amount * discout);
                var prmHTML     = '<div class="prmSale prm-list-container">'
                                + '     <div class="prm-list">'
                                + '         <div class="prm-thumb" style="background-image:url(\'../img/product_promotions/' + prm.prdprm_picture + '\');"></div>'
                                + '         <table>'
                                + '             <tr>'
                                + '                 <td class="prm-name-col">'
                                + '                     <span class="prm-name">' + prm.prdprm_name + '</span><br>'
                                + '                     <span class="discout-rate">ลดราคา ' + prm.prmprd_discout + ' ' + prm.prmprd_discout_type + '</span>'
                                + '                 </td>'
                                + '                 <td class="amount-col">'
                                + '                     <span class="prm-amount">' + amount + '</span>' 
                                + '                 </td>'
                                + '                 <td class="discout-col">'
                                + '                     <span class="prm-discout">' + sumDiscout + '</span>' 
                                + '                 </td>'
                                + '             </tr>'
                                + '         </table>'
                                + '         <input type="hidden" class="prm_id" name="prmSale_' + prd_id + '_prmprd_id" value="' + prm.prmprd_id + '">'
                                + '         <input type="hidden" class="prm_name" name="prmSale_' + prd_id + '_prdprm_name" value="' + prm.prdprm_name + '">'
                                + '         <input type="hidden" class="prm_amount" name="prmSale_' + prd_id + '_saleprmdtl_amount" value="' + amount + '">'
                                + '         <input type="hidden" class="prm_sumDiscout" name="prmSale_' + prd_id + '_saleprmdtl_discout" value="' + sumDiscout + '">'
                                + '         <input type="hidden" class="prm_discout" name="prmSale_' + prd_id + '_discout" value="' + prm.prmprd_discout + '">'
                                + '         <input type="hidden" class="prm_discoutType" name="prmSale_' + prd_id + '_discout_type" value="' + prm.prmprd_discout_type + '">'
                                + '     </div>'
                                + '</div>';
                                
                tdPrm.append(prmHTML);
                var sumDiscoutTxt = $('input[value="' + prd_id + '"]').parent().parent().parent().find('.prd_sumPrm_price');
                sumDiscoutTxt.text(sumDiscout.formatMoney(2, '.', ','));
                return;
            }
        }
    }
    tdPrm.find('.prmSale').remove();
}

function calSummary() {
    var sale_prm_discout    = 0;
    var sale_discout        = 0;
    var totalPrice          = 0;

    if($('#sale_discout').val() != '' && validateMoney($('#sale_discout').val())) {
        sale_discout = parseFloat($('#sale_discout').val());
    }

    // Cal sum sale promotion discout
    $('.prm_sumDiscout').each(function() {
        sale_prm_discout += parseFloat($(this).val());
    });
    $('#sale_prm_discout').val(sale_prm_discout.formatMoney(2, '.', ''));

    // Cal total price
    $('input[name="saledtl_price[]"]').each(function() {
        totalPrice += parseFloat($(this).val());
    });
    totalPrice -= sale_discout + sale_prm_discout;
    $('#sale_total_price').val(totalPrice.formatMoney(2, '.', ''));

}