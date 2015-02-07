var oldCusId   = '';
var oldCusName = '';
var oldCusTypeId = '';

$(document).ready(function(){
	$('#addPackageBtn').click(addPackage);
	$('#addServiceListBtn').click(addServiceList);

	setAllSerDetailAmount();
});

function saveOldCusId() {
    oldCusId      = $('input[name="cus_id"]').val();
    oldCusName    = $('#cus_id').find('.selectReferenceJS-text').text();
	oldCusTypeId  = getCusTypeId(oldCusId);
    return true;
}

function setCusTypeId(cus_id) {
	for(i in refCusData) {
		if(refCusData[i].refValue == cus_id) {
			var cusTypeId = refCusData[i].custype_id;
			$('#custype_id_' + cusTypeId).click();
		}
	}
}

function getCusTypeId(cus_id) {
	for(i in refCusData) {
		if(refCusData[i].refValue == cus_id) {
			return refCusData[i].custype_id;
		}
	}
	return '';
}

function getPkgRow(pkg_id) {
	for(i in refPkgData) {
		if(refPkgData[i].refValue == pkg_id) {
			return refPkgData[i];
		}
	}
	return null;
}

function getSvlRow(svl_id) {
	for(i in refSvlData) {
		if(refSvlData[i].refValue == svl_id) {
			return refSvlData[i];
		}
	}
	return null;
}

function changeCusId() {
	newCusId = $('input[name="cus_id"]').val();
    if(($('input[name="pkg_id[]"]').length > 0 || $('input[name="svl_id[]"]').length > 0) 
    	&& getCusTypeId(oldCusId) != getCusTypeId(newCusId)) {
    	var msg         = 'การเปลี่ยนผู้ใช้บริการที่มีประเภทต่างกันจำเป็นต้องเคลียร์ข้อมูลรายละเอียดการใช้บริการใหม่ '
	                    + 'คุณแน่ใจหรือไม่ที่จะเปลี่ยนผู้ใช้บริการ?';
	    parent.showActionDialog({
	        title: 'เปลี่ยนผู้ใช้บริการ',
	        message: msg,
	        actionList: [
	            {
	                id: 'change',
	                name: 'เปลี่ยน',
	                desc: 'ข้อมูลรายละเอียดการใช้บริการจะถูกเคลียร์',
	                func:
	                function() {
	                    $('#service-package-table tr:not(.headTable-row)').remove();
	                    $('#service-service-list-table tr:not(.headTable-row)').remove();
	                    setAllSerDetailAmount();
	                    setCusTypeId(newCusId);
	                    //calSummary();
	                    parent.hideActionDialog();
	                }
	            },
	            {
	                id: 'cancel',
	                name: 'ยกเลิก',
	                desc: 'ยกเลิกการเปลี่ยนผู้ใช้บริการ',
	                func:
	                function() {
	                    parent.hideActionDialog();
	                    $('#cus_id').find('.selectReferenceJS-input').val(oldCusId);
	                    $('#cus_id').find('.selectReferenceJS-text').text(oldCusName);
	                }
	            }
	        ],
	        boxWidth: 500
	    });
    } else {
    	setCusTypeId(newCusId);
    }
}

function allowChangeCusTypeId() {
    parent.showActionDialog({
        title: 'ไม่สามารถเปลี่ยนประเภทผู้ใช้บริการได้',
        message: 'ประเภทผู้ใช้บริการจะเปลี่ยนไปตามผู้ใช้บริการที่คุณเลือก',
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
}

function addPackage(data) {
    var randNum;
    var selectRefDefault = '';
    var unitPrice = '0.00';
    do {
        randNum     = parseInt(Math.random()*1000);
    } while($('#pkg_id_' + randNum).length > 0);
    var inputKeyId  = 'pkg_id_' + randNum;
    var inputQtyId  = 'pkg_qty_' + randNum;
    if(typeof(data.unitPrice) != 'undefined' && data.unitPrice != '') {
        unitPrice = data.unitPrice;
    }

    // Create HTML and append
    var pkgRowHTML  = '<tr class="package-row">'
                    + '     <td width="350px">'
                    + '         <div id="' + inputKeyId + '" class="selectReferenceJS form-input half" require style="width:350px;"></div>'
                    + '     </td>'
                    + '		<td align="right" style="padding-right:20px;"><span class="pkg_unit_price">' + unitPrice + '</span>'
                    + '     <td style="padding-left:40px;">';

    // add input package amount
    if(data.defaultValue) {
        pkgRowHTML += '         <input id="' + inputQtyId + '" name="pkg_qty[]" type="text" class="form-input half" value="' + data.pkg_qty + '" maxlength="6" size="6" valuepattern="number" require style="text-align:right; width:80px;">';
        selectRefDefault = data.pkg_id;
    } else {
        pkgRowHTML += '         <input id="' + inputQtyId + '" name="pkg_qty[]" type="text" class="form-input half" value="1" maxlength="6" size="6" valuepattern="numberMoreThanZero" require style="text-align:right; width:80px;">';
    }

    // add service package id for update
    if(action == 'EDIT' && typeof(data.serpkg_id) != 'undefined') {
        pkgRowHTML += '         <input name="serpkg_id[]" type="hidden" value="' + data.serpkg_id + '">';
    }
        pkgRowHTML += ' 	</td>'
        			+ '     <td>'
                    + '         <span class="pkg_sumPrm_price">0.00</span>'
                    + '     </td>'
                    + '     <td>'
                    + '         <span class="serpkg_price_txt">0.00</span>'
                    + '         <input type="hidden" name="serpkg_total_price[]" value="0">'
                    + '     </td>'
                    + '		<td style="width:100%">'
        			+ '			<button class="removePackageBtn button button-icon button-icon-delete" onclick="removePackage(\'' + randNum + '\')">ลบ</button>'
                    + '     </td>'
                    + '</tr>'
                    + '<tr id="errMsgRow_' + randNum + '" class="errMsgRow">'
                    + '     <td>'
                    + '         <span id="err-' + inputKeyId + '-require" class="errInputMsg half err-' + inputKeyId + '">'
                    + '             โปรดเลือกแพ็คเกจ'
                    + '         </span>'
                    + '     </td>'
                    + '		<td></td>'
                    + '     <td>'
                    + '         <span id="err-' + inputQtyId + '-require" class="errInputMsg half err-' + inputQtyId + '">'
                    + '             โปรดกรอกจำนวนที่ใช้บริการ'
                    + '         </span>'
                    + '         <span id="err-' + inputQtyId + '-numberMoreThanZero" class="errInputMsg half err-' + inputQtyId + '">'
                    + '             โปรดกรอกจำนวนเป็นตัวเลขจำนวนเต็มตั้งแต่ 1 ขึ้นไป'
                    + '         </span>'
                    + '     </td>'
                    + '</tr>'
                    + '<tr id="packagePrmRow_' + randNum + '" class="package-prm-row">'
                    + '     <td colspan="6"></td>'
                    + '</tr>';
    $('#service-package-table > tbody').append(pkgRowHTML);

    // Create select reference
    selectReferenceJS({
        elem            : $('#' + inputKeyId),
        data            : refPkgData,
        defaultValue    : selectRefDefault,
        onOptionSelect  :
        function() {
            pullPkgUnitPrice(inputKeyId);
            addPkgPrmSale($('#' + inputKeyId).find('input[name="pkg_id[]"]').val());
            calSummary();
        },
        success         : 
        function() {
            $('input[name="' + inputKeyId + '"]').attr('name', 'pkg_id[]');
            setAllSerDetailAmount();
            pullPkgUnitPrice(inputKeyId);
            addPkgPrmSale($('#' + inputKeyId).find('input[name="pkg_id[]"]').val());
            calSummary();
        },
        group           : 'packages'
    });
    // Check Input required and pattern
    $('#' + inputQtyId).focusout(validateInput);
    // Calculate sum price
    $('#' + inputQtyId).change(function() {
        var sumPriceInput = $(this).parent().parent().find('input[name="serpkg_total_price[]"]');
        calSumPriceInput(sumPriceInput, 'packages');
        addPkgPrmSale($('#' + inputKeyId).find('input[name="pkg_id[]"]').val());
        calSummary();
    });
}

function removePackage(randNum) {
    var selectRef   = $('#pkg_id_' + randNum);
    var tr          = selectRef.parent().parent();
    var txt         = selectRef.find('.selectReferenceJS-text').text();
    var val         = selectRef.find('.selectReferenceJS-input').val();
    var msg         = '';
    if(val != '') {
        msg = 'คุณต้องการลบแพ็คเกจ ' + txt + ' ออกจากการใช้บริการครั้งนี้ใช่หรือไม่?';
    } else {
        msg = 'คุณต้องการลบแพ็คเกจที่เลือกออกจากการใช้บริการครั้งนี้ใช่หรือไม่?';
    }
    parent.showActionDialog({
        title: 'ลบแพ็คเกจ',
        message: msg,
        actionList: [
            {
                id: 'ok',
                name: 'ตกลง',
                desc: 'ลบแพ็คเกจนี้ออกจากการใช้บริการ',
                func:
                function() {
                    parent.hideActionDialog();
                    tr.remove();
                    $('#errMsgRow_' + randNum).remove();
                    $('#packagePrmRow_' + randNum).remove();
                    setAllSerDetailAmount();
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
}

function addServiceList(data) {
    var randNum;
    var selectRefDefault = '';
    var unitPrice = '0.00';
    do {
        randNum     = parseInt(Math.random()*1000);
    } while($('#svl_id_' + randNum).length > 0);
    var inputKeyId  = 'svl_id_' + randNum;
    var inputQtyId  = 'svl_qty_' + randNum;
    if(typeof(data.unitPrice) != 'undefined' && data.unitPrice != '') {
        unitPrice = data.unitPrice;
    }

    // Create HTML and append
    var svlRowHTML  = '<tr class="service-list-row">'
                    + '     <td width="350px">'
                    + '         <div id="' + inputKeyId + '" class="selectReferenceJS form-input half" require style="width:350px;"></div>'
                    + '     </td>'
                    + '		<td align="right" style="padding-right:20px;"><span class="svl_unit_price">' + unitPrice + '</span>'
                    + '     <td style="padding-left:40px;">';

    // add input package amount
    if(data.defaultValue) {
        svlRowHTML += '         <input id="' + inputQtyId + '" name="svl_qty[]" type="text" class="form-input half" value="' + data.svl_qty + '" maxlength="6" size="6" valuepattern="number" require style="text-align:right; width:80px;">';
        selectRefDefault = data.pkg_id;
    } else {
        svlRowHTML += '         <input id="' + inputQtyId + '" name="svl_qty[]" type="text" class="form-input half" value="1" maxlength="6" size="6" valuepattern="numberMoreThanZero" require style="text-align:right; width:80px;">';
    }

    // add service package id for update
    if(action == 'EDIT' && typeof(data.sersvl_id) != 'undefined') {
        svlRowHTML += '         <input name="sersvl_id[]" type="hidden" value="' + data.sersvl_id + '">';
    }
        svlRowHTML += ' 	</td>'
        			+ '     <td>'
                    + '         <span class="svl_sumPrm_price">0.00</span>'
                    + '     </td>'
                    + '     <td>'
                    + '         <span class="sersvl_price_txt">0.00</span>'
                    + '         <input type="hidden" name="sersvl_total_price[]" value="0">'
                    + '     </td>'
                    + '		<td style="width:100%">'
                    + '			<button class="removeServiceListBtn button button-icon button-icon-delete" onclick="removeServiceList(\'' + randNum + '\')">ลบ</button>'
                    + '     </td>'
                    + '</tr>'
                    + '<tr id="errMsgRow_' + randNum + '" class="errMsgRow">'
                    + '     <td>'
                    + '         <span id="err-' + inputKeyId + '-require" class="errInputMsg half err-' + inputKeyId + '">'
                    + '             โปรดเลือกรายการบริการ'
                    + '         </span>'
                    + '     </td>'
                    + '		<td></td>'
                    + '     <td>'
                    + '         <span id="err-' + inputQtyId + '-require" class="errInputMsg half err-' + inputQtyId + '">'
                    + '             โปรดกรอกจำนวนที่ใช้บริการ'
                    + '         </span>'
                    + '         <span id="err-' + inputQtyId + '-numberMoreThanZero" class="errInputMsg half err-' + inputQtyId + '">'
                    + '             โปรดกรอกจำนวนเป็นตัวเลขจำนวนเต็มตั้งแต่ 1 ขึ้นไป'
                    + '         </span>'
                    + '     </td>'
                    + '</tr>'
                    + '<tr id="serviceListPrmRow_' + randNum + '" class="service-list-prm-row">'
                    + '     <td colspan="6"></td>'
                    + '</tr>';
    $('#service-service-list-table > tbody').append(svlRowHTML);

    // Create select reference
    selectReferenceJS({
        elem            : $('#' + inputKeyId),
        data            : refSvlData,
        defaultValue    : selectRefDefault,
        onOptionSelect  :
        function() {
            pullSvlUnitPrice(inputKeyId);
            addSvlPrmSale($('#' + inputKeyId).find('input[name="svl_id[]"]').val());
            calSummary();
        },
        success         : 
        function() {
            $('input[name="' + inputKeyId + '"]').attr('name', 'svl_id[]');
            setAllSerDetailAmount();
            pullSvlUnitPrice(inputKeyId);
            addSvlPrmSale($('#' + inputKeyId).find('input[name="svl_id[]"]').val());
            calSummary();
        },
        group           : 'service_lists'
    });
    // Check Input required and pattern
    $('#' + inputQtyId).focusout(validateInput);
    // Calculate sum price
    $('#' + inputQtyId).change(function() {
        var sumPriceInput = $(this).parent().parent().find('input[name="sersvl_total_price[]"]');
        calSumPriceInput(sumPriceInput, 'service_lists');
        addSvlPrmSale($('#' + inputKeyId).find('input[name="svl_id[]"]').val());
        calSummary();
    });
}

function removeServiceList(randNum) {
    var selectRef   = $('#svl_id_' + randNum);
    var tr          = selectRef.parent().parent();
    var txt         = selectRef.find('.selectReferenceJS-text').text();
    var val         = selectRef.find('.selectReferenceJS-input').val();
    var msg         = '';
    if(val != '') {
        msg = 'คุณต้องการลบรายการบริการ ' + txt + ' ออกจากการใช้บริการครั้งนี้ใช่หรือไม่?';
    } else {
        msg = 'คุณต้องการลบรายการบริการที่เลือกออกจากการใช้บริการครั้งนี้ใช่หรือไม่?';
    }
    parent.showActionDialog({
        title: 'ลบรายการบริการ',
        message: msg,
        actionList: [
            {
                id: 'ok',
                name: 'ตกลง',
                desc: 'ลบรายการบริการนี้ออกจากการใช้บริการ',
                func:
                function() {
                    parent.hideActionDialog();
                    tr.remove();
                    $('#errMsgRow_' + randNum).remove();
                    $('#serviceListPrmRow_' + randNum).remove();
                    setAllSerDetailAmount();
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
}

function pullPkgUnitPrice(inputKeyId) {
    var pkgID       = $('#' + inputKeyId).find('.selectReferenceJS-input').val();
    
    if(typeof(pkgID) != 'undefined' && pkgID != '') {
        var pkgUnitPrice = $('#' + inputKeyId).parent().parent().find('.pkg_unit_price');
        var unitPrice    = '';

        for(i in refPkgData) {
            if(refPkgData[i].refValue == pkgID) {
                unitPrice = parseFloat(refPkgData[i].pkg_price);
                break;
            }
        }

        pkgUnitPrice.text(unitPrice.formatMoney(2, '.', ','));
        calSumPriceInput($('#' + inputKeyId).parent().parent().find('input[name="serpkg_total_price[]"]'), 'packages');
    }
}

function pullSvlUnitPrice(inputKeyId) {
    var svlID       = $('#' + inputKeyId).find('.selectReferenceJS-input').val();
    
    if(typeof(svlID) != 'undefined' && svlID != '') {
        var svlUnitPrice = $('#' + inputKeyId).parent().parent().find('.svl_unit_price');
        var unitPrice    = '';

        for(i in refSvlData) {
            if(refSvlData[i].refValue == svlID) {
                unitPrice = parseFloat(refSvlData[i].svl_price);
                break;
            }
        }

        svlUnitPrice.text(unitPrice.formatMoney(2, '.', ','));
        calSumPriceInput($('#' + inputKeyId).parent().parent().find('input[name="sersvl_total_price[]"]'), 'service_lists');
    }
}

function calSumPriceInput(sumPriceInput, type) {
	var  unitSelector, qtySelector, SumSelector;
	if(type == 'packages') {
		unitSelector 	= '.pkg_unit_price';
		qtySelector  	= 'input[name="pkg_qty[]"]';
		SumSelector  	= '.serpkg_price_txt';
	} else if(type == 'service_lists') {
		unitSelector 	= '.svl_unit_price';
		qtySelector  	= 'input[name="svl_qty[]"]';
		SumSelector  	= '.sersvl_price_txt';
	}
    var unitPrice   = parseFloat(sumPriceInput.parent().parent().find(unitSelector).text().replace(',', ''));
    var qtyInput    = sumPriceInput.parent().parent().find(qtySelector);
    var sumPriceTxt = sumPriceInput.parent().parent().find(SumSelector);
    var qty         = validateMoney(qtyInput.val()) &&  qtyInput.val() != '' ? parseInt(qtyInput.val()) : 0;
    var sumPrice    = parseFloat(unitPrice * qty);
    sumPriceInput.val(sumPrice.formatMoney(2, '.', ''));
    sumPriceTxt.text(sumPrice.formatMoney(2, '.', ','));
}

function setAllSerDetailAmount() {
	var pkgNum = $('input[name="pkg_id[]"]').length;
	var svlNum = $('input[name="svl_id[]"]').length;
    $('#allPkg').text(pkgNum);
    $('#allSvl').text(svlNum);

    if(pkgNum > 0) {
    	$('#service-package-table').css('display', 'table');
    } else {
    	$('#service-package-table').css('display', 'none');
    }
    if(svlNum > 0) {
    	$('#service-service-list-table').css('display', 'table');
    } else {
    	$('#service-service-list-table').css('display', 'none');
    }
}

function addPkgPrmSale(pkg_id) {
    // Skip
    if(pkg_id == '' || pkgPromotions == '') {
        return;
    }

    var custype_id    = typeof($('input[name="custype_id"]').val()) != 'undefined' ? $('input[name="custype_id"]').val() : curCustypeId;
    var tdPrm           = $('input[value="' + pkg_id + '"]').parent().parent().parent().next().next().find('td');
    var amount          = parseInt($('input[value="' + pkg_id + '"]').parent().parent().parent().find('input[name="pkg_qty[]"]').val());

    if(amount == '' || !validateMoney(amount)) {
        removePrm(tdPrm.find('.prmSale'));
        return;
    }

    for(pkgID in pkgPromotions[custype_id]) {
        if(pkgID == pkg_id) {
            var prm         = pkgPromotions[custype_id][pkgID];

            // add first amount
            removePrm(tdPrm.find('.prmSale'));
            var pkgRow 		= getPkgRow(pkg_id);
            var unitPrice   = parseFloat(pkgRow.pkg_price);
            var discout     = parseFloat(prm.pkgprmdtl_discout);
            if(prm.pkgprmdtl_discout_type == '%') {
                discout = parseFloat(unitPrice * discout / 100);
            }
            var sumDiscout  = parseFloat(amount * discout);
            var prmHTML     = '<div class="prmSale prm-list">'
                            + ' <div class="prm-thumb" style="background-image:url(\'../img/package_promotions/' + prm.pkgprm_pic + '\');"></div>'
                            + ' <table>'
                            + '     <tr>'
                            + '         <td class="prm-name-col">'
                            + '             <span class="prm-name">' + prm.pkgprm_name + '</span><br>'
                            + '             <span class="discout-rate">ลดราคา ' + prm.pkgprmdtl_discout + ' ' + prm.pkgprmdtl_discout_type + '</span>'
                            + '         </td>'
                            + '         <td class="amount-col">'
                            + '             <span class="prm-amount">' + amount + '</span>' 
                            + '         </td>'
                            + '         <td class="discout-col">'
                            + '             <span class="prm-discout">' + sumDiscout.formatMoney(2, '.', ',') + '</span>' 
                            + '         </td>'
                            + '     </tr>'
                            + ' </table>'
                            + ' <input type="hidden" class="prm_id" name="prmSale_' + pkg_id + '_pkgprmdtl_id" value="' + prm.pkgprmdtl_id + '">'
                            + ' <input type="hidden" class="prm_name" name="prmSale_' + pkg_id + '_pkgprm_name" value="' + prm.pkgprm_name + '">'
                            + ' <input type="hidden" class="prm_amount" name="prmSale_' + pkg_id + '_pkgprmdtl_amount" value="' + amount + '">'
                            + ' <input type="hidden" class="prm_sumDiscout" name="prmSale_' + pkg_id + '_pkgprmdtl_total_price" value="' + sumDiscout + '">'
                            + ' <input type="hidden" class="prm_discout" name="prmSale_' + pkg_id + '_discout" value="' + prm.pkgprmdtl_discout + '">'
                            + ' <input type="hidden" class="prm_discoutType" name="prmSale_' + pkg_id + '_discout_type" value="' + prm.pkgprmdtl_discout_type + '">'
                            + '</div>';
            if(tdPrm.find('.prm-list-container').length <= 0) {
                var prmCont     = '<div class="prm-list-container"></div>';
                tdPrm.append(prmCont);
            }                
            tdPrm.find('.prm-list-container').append(prmHTML);
            return;
        }
    }
    removePrm(tdPrm.find('.prmSale'));
}

function addSvlPrmSale(svl_id) {
    // Skip
    if(svl_id == '' || svlPromotions == '') {
        return;
    }

    var custype_id    = typeof($('input[name="custype_id"]').val()) != 'undefined' ? $('input[name="custype_id"]').val() : curCustypeId;
    var tdPrm           = $('input[value="' + svl_id + '"]').parent().parent().parent().next().next().find('td');
    var amount          = parseInt($('input[value="' + svl_id + '"]').parent().parent().parent().find('input[name="svl_qty[]"]').val());

    if(amount == '' || !validateMoney(amount)) {
        removePrm(tdPrm.find('.prmSale'));
        return;
    }
    for(svlID in svlPromotions[custype_id]) {
        if(svlID == svl_id) {
            var prm         = svlPromotions[custype_id][svlID];
            // add first amount
            removePrm(tdPrm.find('.prmSale'));
            var svlRow 		= getSvlRow(svl_id);
            var unitPrice   = parseFloat(svlRow.svl_price);
            var discout     = parseFloat(prm.svlprmdtl_discout);
            if(prm.svlprmdtl_discout_type == '%') {
                discout = parseFloat(unitPrice * discout / 100);
            }
            var sumDiscout  = parseFloat(amount * discout);
            var prmHTML     = '<div class="prmSale prm-list">'
                            + ' <div class="prm-thumb" style="background-image:url(\'../img/service_list_promotions/' + prm.svlprm_pic + '\');"></div>'
                            + ' <table>'
                            + '     <tr>'
                            + '         <td class="prm-name-col">'
                            + '             <span class="prm-name">' + prm.svlprm_name + '</span><br>'
                            + '             <span class="discout-rate">ลดราคา ' + prm.svlprmdtl_discout + ' ' + prm.svlprmdtl_discout_type + '</span>'
                            + '         </td>'
                            + '         <td class="amount-col">'
                            + '             <span class="prm-amount">' + amount + '</span>' 
                            + '         </td>'
                            + '         <td class="discout-col">'
                            + '             <span class="prm-discout">' + sumDiscout.formatMoney(2, '.', ',') + '</span>' 
                            + '         </td>'
                            + '     </tr>'
                            + ' </table>'
                            + ' <input type="hidden" class="prm_id" name="prmSale_' + svl_id + '_svlprmdtl_id" value="' + prm.svlprmdtl_id + '">'
                            + ' <input type="hidden" class="prm_name" name="prmSale_' + svl_id + '_svlprm_name" value="' + prm.svlprm_name + '">'
                            + ' <input type="hidden" class="prm_amount" name="prmSale_' + svl_id + '_svlprmdtl_amount" value="' + amount + '">'
                            + ' <input type="hidden" class="prm_sumDiscout" name="prmSale_' + svl_id + '_svlprmdtl_total_price" value="' + sumDiscout + '">'
                            + ' <input type="hidden" class="prm_discout" name="prmSale_' + svl_id + '_discout" value="' + prm.svlprmdtl_discout + '">'
                            + ' <input type="hidden" class="prm_discoutType" name="prmSale_' + svl_id + '_discout_type" value="' + prm.svlprmdtl_discout_type + '">'
                            + '</div>';
            if(tdPrm.find('.prm-list-container').length <= 0) {
                var prmCont     = '<div class="prm-list-container"></div>';
                tdPrm.append(prmCont);
            }                
            tdPrm.find('.prm-list-container').append(prmHTML);
            return;
        }
    }
    removePrm(tdPrm.find('.prmSale'));
}

function removePrm(prmList) {
    var container = prmList.parent();
    prmList.remove();
    if(container.html() == '') {
        container.remove();
    }
}

function calSummary() {
    var totalPrice          = 0;
    var saleDiscoutPkgPrm   = 0;

    // Cal sum promotion discout
    $('input[name="pkg_id[]"]').each(function() {
        var tdPrm               = $(this).parent().parent().parent().next().next().find('td');
        var pkg_sumPrm_price    = $(this).parent().parent().parent().find('.pkg_sumPrm_price');
        var serpkg_price_txt    = $(this).parent().parent().parent().find('.serpkg_price_txt');
        var serpkg_price        = parseFloat($(this).parent().parent().parent().find('input[name="serpkg_total_price[]"]').val());
        var sumPrmDiscout       = 0;

        tdPrm.find('.prm_sumDiscout').each(function() {
            sumPrmDiscout += parseFloat($(this).val());
        });
        serpkg_price_txt.text((serpkg_price - sumPrmDiscout).formatMoney(2, '.', ','));
        pkg_sumPrm_price.text(sumPrmDiscout.formatMoney(2, '.', ','));
    });

    // Cal total price
    $('input[name="serpkg_price[]"]').each(function() {
        totalPrice += parseFloat($(this).val());
    });
    
    totalPrice -= saleDiscoutPkgPrm;
    totalPrice = Math.ceil(totalPrice);
    $('#sale_total_price').val(totalPrice.formatMoney(2, '.', ''));
    
    //calChangeMoney(totalPrice);
}