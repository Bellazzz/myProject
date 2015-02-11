var oldCusId   = '';
var oldCusName = '';
var oldCusTypeId = '';

$(document).ready(function(){
	$('#addPackageBtn').click(addPackage);
	$('#addServiceListBtn').click(addServiceList);

	//Cal change money
    $('#ser_pay_price').change(calSummary);

	setAllSerDetailAmount();

	
});

function addItemForEdit() {
	if(action == 'EDIT') {
		for(i in valuesPkg) {
             addPackage({
                defaultValue : true,
                serpkg_id   : valuesPkg[i].serpkg_id,
                pkg_id      : valuesPkg[i].pkg_id,
                pkg_qty     : valuesPkg[i].serpkg_amount,
                unitPrice   : valuesPkg[i].pkg_price
            });
        }
        for(i in valuesSvl) {
             addServiceList({
                defaultValue : true,
                sersvl_id   : valuesSvl[i].sersvl_id,
                svl_id      : valuesSvl[i].svl_id,
                svl_qty     : valuesSvl[i].sersvl_amount,
                unitPrice   : valuesSvl[i].svl_price
            });
        }
        for(i in valuesSvlDtl) {
            var svlComTr = $('input[value="' + valuesSvlDtl[i].svl_id + '"]').parent().parent().parent().next().next().next();
            var randNum  = svlComTr.attr('id').replace('serviceListComRow_', '');
            addServiceListCommission({
                defaultValue    : true,
                svldtl_id       : valuesSvlDtl[i].svldtl_id,
                svl_id          : valuesSvlDtl[i].svl_id,
                emp_id          : valuesSvlDtl[i].emp_id,
                com_rate        : valuesSvlDtl[i].com_rate,
                parentRandNum   : randNum
            });
           
        }
	}
}

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
	                    calSummary();
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
                    + '</tr>'
                    + '<tr id="packagePkgSvlRow_' + randNum + '" class="package-svl-row">'
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
            var curPkgId = $('#' + inputKeyId).find('input[name="pkg_id[]"]').val();
            pullPkgUnitPrice(inputKeyId);
            addPkgPrmSale(curPkgId);
            calSummary();
            addServiceListOfPackage({
                pkg_id          : curPkgId,
                parentRandNum   : randNum
            });
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
}

function addServiceListOfPackage(data) {
    //$('#packagePkgSvlRow_' + data.randNum).
    var svlPkgTd    = $('#packagePkgSvlRow_' + data.parentRandNum + ' > td');
    var initHTML  = '<span class="com-list-title" data-status="1"><i class="fa fa-chevron-up"></i> ซ่อนพนักงานที่ให้บริการ</span>'
                    + '<div class="pkgsvl-list-container"></div>';
    svlPkgTd.html(initHTML);
    svlPkgTd.find('.com-list-title').click(function() {
        var stat = $(this).attr('data-status');
        if(stat == "1") {
            $(this).parent().find('.pkgsvl-list-container').css('display', 'none');
            $(this).attr('data-status', '0');
            $(this).html('<i class="fa fa-chevron-down"></i> แสดงพนักงานที่ให้บริการ');
        } else {
            $(this).parent().find('.pkgsvl-list-container').css('display', 'block');
            $(this).attr('data-status', '1');
            $(this).html('<i class="fa fa-chevron-up"></i> ซ่อนพนักงานที่ให้บริการ');
        }
    });

    for(i in pkgsvlData[data.pkg_id]) {
        var no = parseInt(i) + 1;
        var randNum;
        var selectRefDefault = '';
        do {
            randNum     = parseInt(Math.random()*1000);
        } while($('#emp_id_pkgCom_' + randNum).length > 0);
        var inputKeyId      = 'emp_id_pkgCom_' + randNum;
        var inputComRateId  = 'com_rate_pkgCom_' + randNum;
        var svl_id    = pkgsvlData[data.pkg_id][i].svl_id;
        var svl_name  = pkgsvlData[data.pkg_id][i].svl_name;
        var pkgsvlHTML= '<div class="pkgsvl-list">'
                      + '   ' + no + '. ' + svl_name
                      + '   <div class="pkgsvlCom-list-container">'
                      + '       <div class="pkgsvlCom com-list">'
                      + '           <table cellpadding="0" cellspacing="0">'
                      + '               <tbody>'
                      + '                   <tr>'
                      + '                       <td class="emp-col">'
                      + '                           <div id="' + inputKeyId + '" class="selectReferenceJS form-input half" require style="width:350px;"></div>'
                      + '                       </td>'
                      + '                       <td class="com-rate-col">ค่าคอมมิชชั่น ';

        // add input commisstion rate
        if(data.defaultValue) {
            pkgsvlHTML += '                         <input id="' + inputComRateId + '" type="text" class="form-input half" value="' + data.com_rate + '" maxlength="6" size="6" valuepattern="number" require style="text-align:right; width:80px;">';
            selectRefDefault = data.emp_id;
            comRate          = data.com_rate;
        } else {
            pkgsvlHTML += '                         <input id="' + inputComRateId + '" type="text" class="form-input half" value="100" maxlength="6" size="6" valuepattern="numberMoreThanZero" require style="text-align:right; width:80px;">';
        }

        pkgsvlHTML    += '                          %&nbsp;&nbsp;&nbsp;'
                      + '                           <button id="removeSvlComBtn_' + inputKeyId + '" class="removeSvlComBtn button button-icon button-icon-delete">ลบ</button>'
                      + '                       </td>'
                      + '                   </tr>'
                      + '               </tbody>'
                      + '           </table>'
                      + '       </div>'
                      + '   </div>'
                      + '</div>';
        svlPkgTd.find('.pkgsvl-list-container').append(pkgsvlHTML);

        // Create select reference
        selectReferenceJS({
            elem            : $('#' + inputKeyId),
            data            : refEmpData,
            defaultValue    : selectRefDefault,
            onOptionSelect  :
            function() {
                // var empId = $('#' + inputKeyId).find('.selectReferenceJS-input').val();
                // $('#' + inputKeyId).parent().parent().parent().parent().parent().find('.emp_id').val(empId);
            },
            success         : 
            function() {
                // var empId = $('#' + inputKeyId).find('.selectReferenceJS-input').val();
                // $('#' + inputKeyId).parent().parent().parent().parent().parent().find('.emp_id').val(empId);
            },
            group           : 'employeePkg_' + data.parentRandNum
        });


    //                   var commissionHTML      = '<div class="svlCom com-list">'
    //                         + ' <table cellpadding="0" cellspacing="0">'
    //                         + '     <tbody>'
    //                         + '     <tr>'
    //                         + '         <td class="emp-col">'
    //                         + '             <div id="' + inputKeyId + '" class="selectReferenceJS form-input half" require style="width:350px;"></div>'
    //                         + '         </td>'
    //                         + '         <td class="com-rate-col">ค่าคอมมิชชั่น ';

    
    }
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
        selectRefDefault = data.svl_id;
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
                    + '</tr>'
                    + '<tr id="serviceListComRow_' + randNum + '" class="service-list-com-row">'
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

            var svlComTr = $('#serviceListComRow_' + randNum);
            if(svlComTr.find('.com-list-container').length == 0) {
                addServiceListCommission({
                    svl_id: $('#' + inputKeyId).find('input[name="svl_id[]"]').val(),
                    parentRandNum: randNum
                });
            } else {
                var empId = $('#' + inputKeyId).find('.selectReferenceJS-input').val();
                svlComTr.find('.emp_id').attr('name', 'svlCom_' + empId + '_emp_id[]');
                svlComTr.find('.com_rate').attr('name', 'svlCom_' + empId + '_com_rate[]');
            }
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

function addServiceListCommission(data) {
	var randNum;
    var selectRefDefault = '';
    do {
        randNum     = parseInt(Math.random()*1000);
    } while($('#emp_id_svlCom_' + randNum).length > 0);
    var inputKeyId  	= 'emp_id_svlCom_' + randNum;
    var inputComRateId  = 'com_rate_svlCom_' + randNum;
	var selectRef    	= $('#svl_id_' + data.parentRandNum);
	var tr           	= selectRef.parent().parent();
	var tdCom 		 	= tr.next().next().next().find('td');
    var comRate         = 100;

	var commissionHTML      = '<div class="svlCom com-list">'
                            + ' <table cellpadding="0" cellspacing="0">'
                            + '     <tbody>'
                            + '     <tr>'
                            + '         <td class="emp-col">'
                            + ' 			<div id="' + inputKeyId + '" class="selectReferenceJS form-input half" require style="width:350px;"></div>'
                            + '         </td>'
                            + '         <td class="com-rate-col">ค่าคอมมิชชั่น ';

    // add input commisstion rate
    if(data.defaultValue) {
        commissionHTML += '         <input id="' + inputComRateId + '" type="text" class="form-input half" value="' + data.com_rate + '" maxlength="6" size="6" valuepattern="number" require style="text-align:right; width:80px;">';
        selectRefDefault = data.emp_id;
        comRate          = data.com_rate;
    } else {
        commissionHTML += '         <input id="' + inputComRateId + '" type="text" class="form-input half" value="100" maxlength="6" size="6" valuepattern="numberMoreThanZero" require style="text-align:right; width:80px;">';
    }

    commissionHTML         += '         %&nbsp;&nbsp;&nbsp;'
                            + '         <button id="removeSvlComBtn_' + inputKeyId + '" class="removeSvlComBtn button button-icon button-icon-delete">ลบ</button>'
                            + '         </td>'
                            + '     </tr>'
                            + '     </tbody>'
                            + ' </table>';

    // add service package id for update
    if(action == 'EDIT' && typeof(data.svldtl_id) != 'undefined') {
        commissionHTML     += ' <input name="svldtl_id[]" type="hidden" value="' + data.svldtl_id + '">'
                            + ' <input type="hidden" name="svlCom_' + data.svl_id + '_svldtl_id[]" value="' + data.svldtl_id + '">';
    }

    commissionHTML          += ' <input type="hidden" class="emp_id" name="svlCom_' + data.svl_id + '_emp_id[]" value="">'
                            + ' <input id="com_rate_' + inputKeyId + '" type="hidden" class="com_rate" name="svlCom_' + data.svl_id + '_com_rate[]" value="' + comRate + '">'
                            + '</div>';
    if(tdCom.find('.com-list-container').length <= 0) {
        var comCont     = '<span class="com-list-title" data-status="1">'
                        + '     <i class="fa fa-chevron-up"></i> ซ่อนพนักงานที่ให้บริการ'
                        + '</span>'
                        + '<div class="com-list-container">'
                        + '     <div class="com-list-container-body"></div>'
                        + '<button class="addSvlComBtn button button-icon button-icon-add">เพิ่มพนักงาน</button>'
                        + '</div>';
        tdCom.append(comCont);
        tdCom.find('.com-list-title').click(function() {
            var stat = $(this).attr('data-status');
            if(stat == "1") {
                $(this).parent().find('.com-list-container').css('display', 'none');
                $(this).attr('data-status', '0');
                $(this).html('<i class="fa fa-chevron-down"></i> แสดงพนักงานที่ให้บริการ');
            } else {
                $(this).parent().find('.com-list-container').css('display', 'block');
                $(this).attr('data-status', '1');
                $(this).html('<i class="fa fa-chevron-up"></i> ซ่อนพนักงานที่ให้บริการ');
            }
        });
        tdCom.find('.addSvlComBtn').click(function() {
            addServiceListCommission({
                svl_id: $('#svl_id_' + data.parentRandNum).find('input[name="svl_id[]"]').val(),
                parentRandNum: data.parentRandNum
            });
        });  
    }
    tdCom.find('.com-list-container-body').append(commissionHTML);


    // Create select reference
    selectReferenceJS({
        elem            : $('#' + inputKeyId),
        data            : refEmpData,
        defaultValue    : selectRefDefault,
        onOptionSelect  :
        function() {
        	var empId = $('#' + inputKeyId).find('.selectReferenceJS-input').val();
            $('#' + inputKeyId).parent().parent().parent().parent().parent().find('.emp_id').val(empId);
        },
        success         : 
        function() {
            var empId = $('#' + inputKeyId).find('.selectReferenceJS-input').val();
            $('#' + inputKeyId).parent().parent().parent().parent().parent().find('.emp_id').val(empId);
        },
        group           : 'employee_' + data.parentRandNum
    });
    // Delete svlCom
    $('#removeSvlComBtn_' + inputKeyId).click(function() {
        $(this).parent().parent().parent().parent().parent().remove();
    });
    $('#' + inputComRateId).change(function() {
        $('#com_rate_' + inputKeyId).val($(this).val());
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
                    $('#serviceListComRow_' + randNum).remove();
                    setAllSerDetailAmount();
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
                            + ' <input type="hidden" class="prm_amount" name="prmSale_' + pkg_id + '_serpkgprm_amount" value="' + amount + '">'
                            + ' <input type="hidden" class="prm_sumDiscout" name="prmSale_' + pkg_id + '_serpkgprm_discout_total" value="' + sumDiscout + '">'
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
                            + ' <input type="hidden" class="prm_amount" name="prmSale_' + svl_id + '_sersvlprm_amount" value="' + amount + '">'
                            + ' <input type="hidden" class="prm_sumDiscout" name="prmSale_' + svl_id + '_sersvlprm_discout_total" value="' + sumDiscout + '">'
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
    var totalDiscoutPrm   = 0;

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

        totalDiscoutPrm += sumPrmDiscout;

        serpkg_price_txt.text((serpkg_price - sumPrmDiscout).formatMoney(2, '.', ','));
        pkg_sumPrm_price.text(sumPrmDiscout.formatMoney(2, '.', ','));
    });
    $('input[name="svl_id[]"]').each(function() {
        var tdPrm               = $(this).parent().parent().parent().next().next().find('td');
        var svl_sumPrm_price    = $(this).parent().parent().parent().find('.svl_sumPrm_price');
        var sersvl_price_txt    = $(this).parent().parent().parent().find('.sersvl_price_txt');
        var sersvl_price        = parseFloat($(this).parent().parent().parent().find('input[name="sersvl_total_price[]"]').val());
        var sumPrmDiscout       = 0;

        tdPrm.find('.prm_sumDiscout').each(function() {
            sumPrmDiscout += parseFloat($(this).val());
        });

        totalDiscoutPrm += sumPrmDiscout;

        sersvl_price_txt.text((sersvl_price - sumPrmDiscout).formatMoney(2, '.', ','));
        svl_sumPrm_price.text(sumPrmDiscout.formatMoney(2, '.', ','));
    });

    // Cal total price
    $('input[name="serpkg_total_price[]"]').each(function() {
        totalPrice += parseFloat($(this).val());
    });
    $('input[name="sersvl_total_price[]"]').each(function() {
        totalPrice += parseFloat($(this).val());
    });
    
    totalPrice -= totalDiscoutPrm;
    totalPrice = Math.ceil(totalPrice);

    $('#ser_prm_discout').val(totalDiscoutPrm.formatMoney(2, '.', ''));
    $('#ser_total_price').val(totalPrice.formatMoney(2, '.', ''));
    
    $('#ser_pay_price').focusout();
    calChangeMoney(totalPrice);
}

function calChangeMoney(totalPrice) {
    if($('#ser_pay_price').val() != '' && validateMoney($('#ser_pay_price').val())) {
        totalPrice          = parseFloat(totalPrice);
        var ser_pay_price  = parseFloat($('#ser_pay_price').val());
        if(ser_pay_price >= totalPrice){
            var change_money   = ser_pay_price - totalPrice;
            $('#changeMoney').val(change_money.formatMoney(2, '.', ''));
        } else {
            $('#changeMoney').val("0.00");
        }
    }
}

function beforeSaveRecord() {
    // Check input required
    var returnVal 				= false;

    // Not input service
    if(!hasInputError() && $('input[name="pkg_id[]"]').length == 0 && $('input[name="svl_id[]"]').length == 0) {
    	parent.showActionDialog({
            title	: 'คุณยังไม่ได้ป้อนข้อมูล',
            message : 'โปรดป้อนข้อมูลรายละเอียดการใช้บริการอย่างน้อย 1 รายการค่ะ',
            actionList: [
                {
                    id: 'ok',
                    name: 'ตกลง',
                    func:
                    function() {
                        parent.hideActionDialog();
                    }
                }
            ]
        });
    	returnVal = true;
    }
    return returnVal;
}