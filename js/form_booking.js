var oldCusId   = '';
var oldCusName = '';
var oldCusTypeId = '';

$(document).ready(function(){
	$('#addPackageBtn').click(addPackage);
	$('#addServiceListBtn').click(addServiceList);

	//Cal change money
    $('#bkg_pay_price').change(calSummary);

	setAllBkgDetailAmount();

    // Change Booking Status
	$('#changeBpkgStatus-pending_check-btn').click(function(){
        var title = 'เปลี่ยนสถานะเป็นรอชำระเงิน';
        var msg   = 'คุณได้ตรวจสอบการจองครั้งนี้ และพบว่าลูกค้าสามารถมาใช้บริการในวันเวลาดังกล่าวได้ และต้องการเปลี่ยนสถานะการจองเป็น รอชำระเงิน เพื่อแจ้งให้ลูกค้าทำการชำระเงินใช่หรือไม่?';
        changeBkgStatus(title, msg, 'S02');
    });
    $('#changeBpkgStatus-pending_service-btn').click(function(){
        var title = 'เปลี่ยนสถานะเป็นรอใช้บริการ';
        var msg   = 'หลักฐานการชำระเงินของลูกค้าถูกต้อง และต้องการเปลี่ยนสถานะการจองเป็น รอใช้บริการ เพื่อแจ้งให้ลูกค้าเข้ามาใช้บริการที่สปาใช่หรือไม่?';
        changeBkgStatus(title, msg, 'S03');
    });
    $('#changeBpkgStatus-remain_service-btn').click(function(){
        var title = 'ใช้บริการไม่ครบตามที่จอง';
        var msg   = 'ลูกค้ามาใช้บริการไม่ครบตามที่ได้จองไว้ และคุณต้องการเปลี่ยนสถานะการจองเป็น ใช้บริการไม่ครบตามที่จอง ใช่หรือไม่?';
        changeBkgStatus(title, msg, 'S07');
    });
    $('#changeBpkgStatus-cancel-btn').click(function(){
        var title = 'เปลี่ยนสถานะเป็นยกเลิกการจอง';
        var msg   = 'คุณต้องการเปลี่ยนสถานะการจองเป็น ยกเลิกการจอง ใช่หรือไม่?';
        changeBkgStatus(title, msg, 'S06');
    });
});

function changeBkgStatus(title, msg, status_id) {
    parent.showActionDialog({
        title: title,
        message: msg,
        actionList: [
            {
                id: 'change',
                name: 'ตกลง',
                desc: 'สถานะการจองครั้งนี้จะถูกเปลี่ยน',
                func:
                function() {
                    parent.hideActionDialog();
                    $.ajax({
                        url: '../common/ajaxChangeBkgStatus.php',
                        type: 'POST',
                        data: {
                            bkg_id: $('input[name="bkg_id"]').val(),
                            status_id: status_id
                        },
                        success:
                        function(response) {
                            if(response == 'PASS') {
                                 parent.confirmCloseFormTable(action);
                                 parent.pullTable(false);
                            } else if(response == 'FAIL') {
                                parent.showActionDialog({
                                    title: 'เกิดข้อผิดพลาด',
                                    message: 'ไม่สามารถเปลี่ยนสถานะการจองได้เนื่องจากเกิดข้อผิดพลาดบางประการ',
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
                            } else {
                                alert(response);
                            }
                        }
                    });
                }
            },
            {
                id: 'cancel',
                name: 'ยกเลิก',
                desc: 'ยกเลิกการเปลี่ยนสถานะการจอง',
                func:
                function() {
                    parent.hideActionDialog();
                }
            }
        ],
        boxWidth: 500
    });
}

function addItemForEdit() {
	if(action == 'EDIT') {
		for(i in valuesPkg) {
             addPackage({
                defaultValue : true,
                bkgpkg_id   : valuesPkg[i].bkgpkg_id,
                pkg_id      : valuesPkg[i].pkg_id,
                emp_id      : valuesPkg[i].emp_id,
                bkgpkg_date : valuesPkg[i].bkgpkg_date,
                bkgpkg_time : valuesPkg[i].bkgpkg_time,
                pkg_qty     : valuesPkg[i].bkgpkg_persons,
                unitPrice   : valuesPkg[i].pkg_price
            });
        }
        for(i in valuesSvl) {
             addServiceList({
                defaultValue : true,
                bkgsvl_id   : valuesSvl[i].bkgsvl_id,
                svl_id      : valuesSvl[i].svl_id,
                emp_id      : valuesSvl[i].emp_id,
                bkgsvl_date : valuesSvl[i].bkgsvl_date,
                bkgsvl_time : valuesSvl[i].bkgsvl_time,
                svl_qty     : valuesSvl[i].bkgsvl_persons,
                unitPrice   : valuesSvl[i].svl_price
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
    	var msg         = 'การเปลี่ยนผู้จองที่มีประเภทต่างกันจำเป็นต้องเคลียร์ข้อมูลรายละเอียดการจองใหม่ '
	                    + 'คุณแน่ใจหรือไม่ที่จะเปลี่ยนผู้จอง?';
	    parent.showActionDialog({
	        title: 'เปลี่ยนผู้จอง',
	        message: msg,
	        actionList: [
	            {
	                id: 'change',
	                name: 'เปลี่ยน',
	                desc: 'ข้อมูลรายละเอียดการจองจะถูกเคลียร์',
	                func:
	                function() {
	                    $('#booking-package-table tr:not(.headTable-row)').remove();
	                    $('#booking-service-list-table tr:not(.headTable-row)').remove();
	                    setAllBkgDetailAmount();
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
    if(!hasCustomer('pkg')) {
        return;
    }

    var randNum;
    var selectRefDefault = '';
    var unitPrice = '0.00';
    var bkgpkg_date = nowDate;
    var bkgpkg_time = '';
    var bkgpkg_emp_id = '';
    do {
        randNum     = parseInt(Math.random()*1000);
    } while($('#pkg_id_' + randNum).length > 0);
    var inputKeyId  = 'pkg_id_' + randNum;
    var inputQtyId  = 'pkg_qty_' + randNum;
    var inputDateId = 'pkg_date_' + randNum;
    var inputTimeId = 'pkg_time_' + randNum;
    var inputEmpId  = 'pkg_emp_id_' + randNum;
    if(typeof(data.unitPrice) != 'undefined' && data.unitPrice != '') {
        unitPrice = data.unitPrice;
    }
    if(typeof(data.emp_id) != 'undefined' && data.emp_id != '') {
        bkgpkg_emp_id = data.emp_id;
    }
    if(typeof(data.bkgpkg_date) != 'undefined' && data.bkgpkg_date != '') {
        bkgpkg_date = data.bkgpkg_date;
    }
    if(typeof(data.bkgpkg_time) != 'undefined' && data.bkgpkg_time != '') {
        bkgpkg_time = data.bkgpkg_time;
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

    // add booking package id for update
    if(action == 'EDIT' && typeof(data.bkgpkg_id) != 'undefined') {
        pkgRowHTML += '         <input name="bkgpkg_id[]" type="hidden" value="' + data.bkgpkg_id + '">';
    }

        pkgRowHTML += ' 	</td>'
        			+ '     <td>'
                    + '         <span class="pkg_sumPrm_price">0.00</span>'
                    + '     </td>'
                    + '     <td>'
                    + '         <span class="bkgpkg_price_txt">0.00</span>'
                    + '         <input type="hidden" name="bkgpkg_total_price_tmp[]" value="0">'
                    + '         <input type="hidden" name="bkgpkg_total_price[]" value="0">'
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
                    + '             โปรดกรอกจำนวนผู้ใช้บริการ'
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
                    + '     <td colspan="6">'
                    + '         <span class="com-list-title" data-status="1">'
                    + '             <i class="fa fa-chevron-down"></i> ซ่อนวันเวลาที่มาใช้บริการ'
                    + '         </span>'
                    + '         <div class="pkgdtl-container">'
                    + '             <table>'
                    + '             <tbody>'
                    + '                 <tr>'
                    + '                     <td>'
                    + '                         <label>วันที่</label> <input id="' + inputDateId + '" name="bkgpkg_date[]" type="text" class="mbk-dtp-th form-input half" require value="' + bkgpkg_date + '">'
                    + '                     </td>'
                    + '                     <td>'
                    + '                         <label>เวลา</label> <input id="' + inputTimeId + '" name="bkgpkg_time[]" type="text" class="form-input half" require style="width:80px;" value="' + bkgpkg_time + '">'
                    + '                     </td>'
                    + '                     <td>'
                    + '                         <label>พนักงานที่จอง</label> <div id="' + inputEmpId + '" class="selectReferenceJS form-input half">'
                    + '                     </td>'
                    + '                 </tr>'
                    + '                 <tr>'
                    + '                     <td>'
                    + '                         <span id="err-' + inputDateId + '-require" class="errInputMsg err-'+ inputDateId + '">โปรดป้อนวันที่มาใช้บริการ</span>'
                    + '                     </td>'
                    + '                     <td>'
                    + '                         <span id="err-' + inputTimeId + '-require" class="errInputMsg err-'+ inputTimeId + '">โปรดป้อนเวลาที่มาใช้บริการ</span>'
                    + '                     </td>'
                    + '                 </tr>'
                    + '             </tbody>'
                    + '             </table>'
                    + '         </div>'
                    + '     </td>'
                    + '</tr>';
    $('#booking-package-table > tbody').append(pkgRowHTML);

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
        },
        success         : 
        function(defaultKey) {
            $('input[name="' + inputKeyId + '"]').attr('name', 'pkg_id[]');
            setAllBkgDetailAmount();
            pullPkgUnitPrice(inputKeyId);
            addPkgPrmSale($('#' + inputKeyId).find('input[name="pkg_id[]"]').val());
            calSummary();
        },
        group           : 'packages'
    });
    selectReferenceJS({
        elem            : $('#' + inputEmpId),
        data            : refEmpData,
        defaultValue    : bkgpkg_emp_id,
        showClearBtn    : true,
        clearBtnText    : 'ไม่ระบุ',
        success:
        function() {
            $('input[name="' + inputEmpId + '"]').attr('name', 'bkgpkg_emp_id[]');
        }
    });
    $('#' + inputDateId).datetimepicker({
        lang                : 'th',
        format              : 'Y/m/d',
        timepicker          :false,
        closeOnDateSelect   :true,
        scrollInput         :false,
        yearOffset          :543,
        onSelectDate: 
        function(){
          $('#' + inputDateId).blur();
        },
        timepicker:false
    });
    $('#' + inputTimeId).datetimepicker({
        datepicker:false,
        format:'H:i'
    });
    $('#' + inputDateId).focusout(validateInput);
    $('#' + inputTimeId).focusout(validateInput);
    $('#' + inputDateId).parent().parent().parent().parent().parent().parent().find('.com-list-title').click(function() {
        var stat = $(this).attr('data-status');
        if(stat == "1") {
            $(this).parent().find('.pkgdtl-container').css('display', 'none');
            $(this).attr('data-status', '0');
            $(this).html('<i class="fa fa-chevron-right"></i> แสดงวันเวลาที่มาใช้บริการ');
        } else {
            $(this).parent().find('.pkgdtl-container').css('display', 'block');
            $(this).attr('data-status', '1');
            $(this).html('<i class="fa fa-chevron-down"></i> ซ่อนวันเวลาที่มาใช้บริการ');
        }
    });
    addEventDtpTh($('#' + inputDateId));
    // Check Input required and pattern
    $('#' + inputQtyId).focusout(validateInput);
    // Calculate sum price
    $('#' + inputQtyId).change(function() {
        var sumPriceInput = $(this).parent().parent().find('input[name="bkgpkg_total_price_tmp[]"]');
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
        msg = 'คุณต้องการลบแพ็คเกจ ' + txt + ' ออกจากการจองครั้งนี้ใช่หรือไม่?';
    } else {
        msg = 'คุณต้องการลบแพ็คเกจที่เลือกออกจากการจองครั้งนี้ใช่หรือไม่?';
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
                    $('#packagePkgSvlRow_' + randNum).remove();
                    setAllBkgDetailAmount();
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

function addServiceList(data) {
    if(!hasCustomer('svl')) {
        return;
    }

    var randNum;
    var selectRefDefault = '';
    var unitPrice = '0.00';
    var bkgsvl_emp_id = '';
    var bkgsvl_date = nowDate;
    var bkgsvl_time = '';
    var svl_min = 0;
    do {
        randNum     = parseInt(Math.random()*1000);
    } while($('#svl_id_' + randNum).length > 0);
    var inputKeyId  = 'svl_id_' + randNum;
    var inputQtyId  = 'svl_qty_' + randNum;
    var inputDateId = 'svl_date_' + randNum;
    var inputTimeId = 'svl_time_' + randNum;
    var inputEmpId  = 'bkgsvl_emp_id_' + randNum;
    if(typeof(data.emp_id) != 'undefined' && data.emp_id != '') {
        bkgsvl_emp_id = data.emp_id;
    }
    if(typeof(data.unitPrice) != 'undefined' && data.unitPrice != '') {
        unitPrice = data.unitPrice;
    }
    if(typeof(data.bkgsvl_date) != 'undefined' && data.bkgsvl_date != '') {
        bkgsvl_date = data.bkgsvl_date;
    }
    if(typeof(data.bkgsvl_time) != 'undefined' && data.bkgsvl_time != '') {
        bkgsvl_time = data.bkgsvl_time;
    }

    // Create HTML and append
    var svlRowHTML  = '<tr class="service-list-row">'
                    + '     <td width="350px">'
                    + '         <div id="' + inputKeyId + '" class="selectReferenceJS form-input half" require style="width:350px;"></div>'
                    + '     </td>'
                    + '     <td align="right" style="padding-right:20px;width:110px;"><span id="svl_min_' + randNum + '" class="svl_min_txt">' + svl_min + '</span> นาที'
                    + '		<td align="right" style="padding-right:20px;"><span class="svl_unit_price">' + unitPrice + '</span>'
                    + '     <td style="padding-left:40px;">';

    // add input package amount
    if(data.defaultValue) {
        svlRowHTML += '         <input id="' + inputQtyId + '" name="svl_qty[]" type="text" class="form-input half" value="' + data.svl_qty + '" maxlength="6" size="6" valuepattern="number" require style="text-align:right; width:80px;">';
        selectRefDefault = data.svl_id;
    } else {
        svlRowHTML += '         <input id="' + inputQtyId + '" name="svl_qty[]" type="text" class="form-input half" value="1" maxlength="6" size="6" valuepattern="numberMoreThanZero" require style="text-align:right; width:80px;">';
    }

    // add booking package id for update
    if(action == 'EDIT' && typeof(data.bkgsvl_id) != 'undefined') {
        svlRowHTML += '         <input name="bkgsvl_id[]" type="hidden" value="' + data.bkgsvl_id + '">';
    }
        svlRowHTML += ' 	</td>'
        			+ '     <td>'
                    + '         <span class="svl_sumPrm_price">0.00</span>'
                    + '     </td>'
                    + '     <td>'
                    + '         <span class="bkgsvl_price_txt">0.00</span>'
                    + '         <input type="hidden" name="bkgsvl_total_price_tmp[]" value="0">'
                    + '         <input type="hidden" name="bkgsvl_total_price[]" value="0">'
                    + '     </td>'
                    + '		<td>'
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
                    + '             โปรดกรอกจำนวนผู้ใช้บริการ'
                    + '         </span>'
                    + '         <span id="err-' + inputQtyId + '-numberMoreThanZero" class="errInputMsg half err-' + inputQtyId + '">'
                    + '             โปรดกรอกจำนวนเป็นตัวเลขจำนวนเต็มตั้งแต่ 1 ขึ้นไป'
                    + '         </span>'
                    + '     </td>'
                    + '</tr>'
                    + '<tr id="serviceListPrmRow_' + randNum + '" class="service-list-prm-row">'
                    + '     <td colspan="6"></td>'
                    + '</tr>'
                    + '<tr id="serviceListDtlRow_' + randNum + '" class="service-list-dtl-row">'
                    + '     <td colspan="6">'
                    + '         <span class="com-list-title" data-status="1">'
                    + '             <i class="fa fa-chevron-down"></i> ซ่อนวันเวลาที่มาใช้บริการ'
                    + '         </span>'
                    + '         <div class="svldtl-container">'
                    + '             <table>'
                    + '             <tbody>'
                    + '                 <tr>'
                    + '                     <td>'
                    + '                         <label>วันที่</label> <input id="' + inputDateId + '" name="bkgsvl_date[]" type="text" class="mbk-dtp-th form-input half" require value="' + bkgsvl_date + '">'
                    + '                     </td>'
                    + '                     <td>'
                    + '                         <label>เวลา</label> <input id="' + inputTimeId + '" name="bkgsvl_time[]" type="text" class="form-input half" require style="width:80px;" value="' + bkgsvl_time + '">'
                    + '                     </td>'
                    + '                     <td class="bkgemp_col" style="display:none;position:relative;">'
                    + '                         <img class="pullEmpId-loader" src="../img/loading.gif">'
                    + '                         <label>พนักงานที่จอง</label> <div id="' + inputEmpId + '" class="selectReferenceJS form-input half" data-randNum="' + randNum + '">'
                    + '                     </td>'
                    + '                 </tr>'
                    + '                 <tr>'
                    + '                     <td>'
                    + '                         <span id="err-' + inputDateId + '-require" class="errInputMsg err-'+ inputDateId + '">โปรดป้อนวันที่มาใช้บริการ</span>'
                    + '                     </td>'
                    + '                     <td>'
                    + '                         <span id="err-' + inputTimeId + '-require" class="errInputMsg err-'+ inputTimeId + '">โปรดป้อนเวลาที่มาใช้บริการ</span>'
                    + '                     </td>'
                    + '                 </tr>'
                    + '             </tbody>'
                    + '             </table>'
                    + '         </div>'
                    + '     </td>';
    $('#booking-service-list-table > tbody').append(svlRowHTML);

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

            pullBkgEmp({
                empInput: $('#' + inputEmpId),
                type: 'svl'
            });
        },
        success         : 
        function() {
            $('input[name="' + inputKeyId + '"]').attr('name', 'svl_id[]');
            setAllBkgDetailAmount();
            pullSvlUnitPrice(inputKeyId);
            addSvlPrmSale($('#' + inputKeyId).find('input[name="svl_id[]"]').val());
            calSummary();
        },
        group           : 'service_lists'
    });
    selectReferenceJS({
        elem            : $('#' + inputEmpId),
        data            : refEmpData,
        defaultValue    : bkgsvl_emp_id,
        showClearBtn    : true,
        clearBtnText    : 'ไม่ระบุ',
        success:
        function() {
            $('input[name="' + inputEmpId + '"]').attr('name', 'bkgsvl_emp_id[]');
        }
    });
    $('#' + inputDateId).datetimepicker({
        lang                : 'th',
        format              : 'Y/m/d',
        timepicker          :false,
        closeOnDateSelect   :true,
        scrollInput         :false,
        yearOffset          :543,
        onSelectDate: 
        function(){
          $('#' + inputDateId).blur();
        },
        timepicker:false
    });
    $('#' + inputTimeId).datetimepicker({
        datepicker:false,
        format:'H:i'
    });
    $('#' + inputDateId).focusout(validateInput);
    $('#' + inputTimeId).focusout(validateInput);
    $('#' + inputDateId).parent().parent().parent().parent().parent().parent().find('.com-list-title').click(function() {
        var stat = $(this).attr('data-status');
        if(stat == "1") {
            $(this).parent().find('.svldtl-container').css('display', 'none');
            $(this).attr('data-status', '0');
            $(this).html('<i class="fa fa-chevron-right"></i> แสดงวันเวลาที่มาใช้บริการ');
        } else {
            $(this).parent().find('.svldtl-container').css('display', 'block');
            $(this).attr('data-status', '1');
            $(this).html('<i class="fa fa-chevron-down"></i> ซ่อนวันเวลาที่มาใช้บริการ');
        }
    });
    addEventDtpTh($('#' + inputDateId));
    // Check Input required and pattern
    $('#' + inputQtyId).focusout(validateInput);
    // Calculate sum price
    $('#' + inputQtyId).change(function() {
        var sumPriceInput = $(this).parent().parent().find('input[name="bkgsvl_total_price_tmp[]"]');
        calSumPriceInput(sumPriceInput, 'service_lists');
        addSvlPrmSale($('#' + inputKeyId).find('input[name="svl_id[]"]').val());
        calSummary();

        pullBkgEmp({
            empInput: $('#' + inputEmpId),
            type: 'svl'
        });
    });
    // Pull employee id
    $('#' + inputDateId).focusout(function() {
        pullBkgEmp({
            empInput: $('#' + inputEmpId),
            type: 'svl'
        });
    });
    $('#' + inputTimeId).focusout(function() {
        pullBkgEmp({
            empInput: $('#' + inputEmpId),
            type: 'svl'
        });
    });
}

function removeServiceList(randNum) {
    var selectRef   = $('#svl_id_' + randNum);
    var tr          = selectRef.parent().parent();
    var txt         = selectRef.find('.selectReferenceJS-text').text();
    var val         = selectRef.find('.selectReferenceJS-input').val();
    var msg         = '';
    if(val != '') {
        msg = 'คุณต้องการลบรายการบริการ ' + txt + ' ออกจากการจองครั้งนี้ใช่หรือไม่?';
    } else {
        msg = 'คุณต้องการลบรายการบริการที่เลือกออกจากการจองครั้งนี้ใช่หรือไม่?';
    }
    parent.showActionDialog({
        title: 'ลบรายการบริการ',
        message: msg,
        actionList: [
            {
                id: 'ok',
                name: 'ตกลง',
                desc: 'ลบรายการบริการนี้ออกจากการจอง',
                func:
                function() {
                    parent.hideActionDialog();
                    tr.remove();
                    $('#errMsgRow_' + randNum).remove();
                    $('#serviceListPrmRow_' + randNum).remove();
                    $('#serviceListDtlRow_' + randNum).remove();
                    setAllBkgDetailAmount();
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
        calSumPriceInput($('#' + inputKeyId).parent().parent().find('input[name="bkgpkg_total_price_tmp[]"]'), 'packages');
    }
}

function pullSvlUnitPrice(inputKeyId) {
    var svlID       = $('#' + inputKeyId).find('.selectReferenceJS-input').val();
    
    if(typeof(svlID) != 'undefined' && svlID != '') {
        var svlUnitPrice = $('#' + inputKeyId).parent().parent().find('.svl_unit_price');
        var svlMinTxt = $('#' + inputKeyId).parent().parent().find('.svl_min_txt');
        var unitPrice    = '';
        var svl_min = '';

        for(i in refSvlData) {
            if(refSvlData[i].refValue == svlID) {
                unitPrice = parseFloat(refSvlData[i].svl_price);
                svl_min = parseInt(refSvlData[i].svl_min);
                break;
            }
        }

        svlUnitPrice.text(unitPrice.formatMoney(2, '.', ','));
        svlMinTxt.text(svl_min.formatMoney(0, '', ','));
        calSumPriceInput($('#' + inputKeyId).parent().parent().find('input[name="bkgsvl_total_price_tmp[]"]'), 'service_lists');
    }
}

function calSumPriceInput(sumPriceInput, type) {
	var  unitSelector, qtySelector, SumSelector;
	if(type == 'packages') {
		unitSelector 	= '.pkg_unit_price';
		qtySelector  	= 'input[name="pkg_qty[]"]';
		SumSelector  	= '.bkgpkg_price_txt';
	} else if(type == 'service_lists') {
		unitSelector 	= '.svl_unit_price';
		qtySelector  	= 'input[name="svl_qty[]"]';
		SumSelector  	= '.bkgsvl_price_txt';
	}
    var unitPrice   = parseFloat(sumPriceInput.parent().parent().find(unitSelector).text().replace(',', ''));
    var qtyInput    = sumPriceInput.parent().parent().find(qtySelector);
    var sumPriceTxt = sumPriceInput.parent().parent().find(SumSelector);
    var qty         = validateMoney(qtyInput.val()) &&  qtyInput.val() != '' ? parseInt(qtyInput.val()) : 0;
    var sumPrice    = parseFloat(unitPrice * qty);
    sumPriceInput.val(sumPrice.formatMoney(2, '.', ''));
    sumPriceTxt.text(sumPrice.formatMoney(2, '.', ','));
}

function setAllBkgDetailAmount() {
	var pkgNum = $('input[name="pkg_id[]"]').length;
	var svlNum = $('input[name="svl_id[]"]').length;
    $('#allPkg').text(pkgNum);
    $('#allSvl').text(svlNum);

    if(pkgNum > 0) {
    	$('#booking-package-table').css('display', 'table');
    } else {
    	$('#booking-package-table').css('display', 'none');
    }
    if(svlNum > 0) {
    	$('#booking-service-list-table').css('display', 'table');
    } else {
    	$('#booking-service-list-table').css('display', 'none');
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
    if(pkgPromotions != null) {
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
                                + ' <input type="hidden" class="prm_amount" name="prmSale_' + pkg_id + '_bkgpkgprm_amount" value="' + amount + '">'
                                + ' <input type="hidden" class="prm_sumDiscout" name="prmSale_' + pkg_id + '_bkgpkgprm_discout_total" value="' + sumDiscout + '">'
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
    if(svlPromotions != null) {
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
                                + ' <input type="hidden" class="prm_amount" name="prmSale_' + svl_id + '_bkgsvlprm_amount" value="' + amount + '">'
                                + ' <input type="hidden" class="prm_sumDiscout" name="prmSale_' + svl_id + '_bkgsvlprm_discout_total" value="' + sumDiscout + '">'
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
    var totalPrice        = 0;
    var totalDiscoutPrm   = 0;

    // Cal sum promotion discout
    $('input[name="pkg_id[]"]').each(function() {
        var tdPrm               = $(this).parent().parent().parent().next().next().find('td');
        var pkg_sumPrm_price    = $(this).parent().parent().parent().find('.pkg_sumPrm_price');
        var bkgpkg_price_txt    = $(this).parent().parent().parent().find('.bkgpkg_price_txt');
        var bkgpkg_price        = parseFloat($(this).parent().parent().parent().find('input[name="bkgpkg_total_price_tmp[]"]').val());
        var sumPrmDiscout       = 0;
        var bkgpkgTotalPrice    = $(this).parent().parent().parent().find('input[name="bkgpkg_total_price[]"]');

        tdPrm.find('.prm_sumDiscout').each(function() {
            sumPrmDiscout += parseFloat($(this).val());
        });

        totalDiscoutPrm += sumPrmDiscout;

        bkgpkg_price_txt.text((bkgpkg_price - sumPrmDiscout).formatMoney(2, '.', ','));
        bkgpkgTotalPrice.val(bkgpkg_price - sumPrmDiscout);
        pkg_sumPrm_price.text(sumPrmDiscout.formatMoney(2, '.', ','));
    });
    $('input[name="svl_id[]"]').each(function() {
        var tdPrm               = $(this).parent().parent().parent().next().next().find('td');
        var svl_sumPrm_price    = $(this).parent().parent().parent().find('.svl_sumPrm_price');
        var bkgsvl_price_txt    = $(this).parent().parent().parent().find('.bkgsvl_price_txt');
        var bkgsvl_price        = parseFloat($(this).parent().parent().parent().find('input[name="bkgsvl_total_price_tmp[]"]').val());
        var sumPrmDiscout       = 0;
        var bkgsvlTotalPrice    = $(this).parent().parent().parent().find('input[name="bkgsvl_total_price[]"]');

        tdPrm.find('.prm_sumDiscout').each(function() {
            sumPrmDiscout += parseFloat($(this).val());
        });

        totalDiscoutPrm += sumPrmDiscout;

        bkgsvl_price_txt.text((bkgsvl_price - sumPrmDiscout).formatMoney(2, '.', ','));
        bkgsvlTotalPrice.val(bkgsvl_price - sumPrmDiscout);
        svl_sumPrm_price.text(sumPrmDiscout.formatMoney(2, '.', ','));
    });

    // Cal total price
    $('input[name="bkgpkg_total_price[]"]').each(function() {
        totalPrice += parseFloat($(this).val());
    });
    $('input[name="bkgsvl_total_price[]"]').each(function() {
        totalPrice += parseFloat($(this).val());
    });
    
    totalPrice = Math.ceil(totalPrice);

    $('#pkg_prm_discout').val(totalDiscoutPrm.formatMoney(2, '.', ''));
    $('#bkg_total_price').val(totalPrice.formatMoney(2, '.', ''));
    
    $('#bkg_pay_price').focusout();
    calChangeMoney(totalPrice);
}

function calChangeMoney(totalPrice) {
    if($('#bkg_pay_price').val() != '' && validateMoney($('#bkg_pay_price').val())) {
        totalPrice          = parseFloat(totalPrice);
        var bkg_pay_price  = parseFloat($('#bkg_pay_price').val());
        if(bkg_pay_price >= totalPrice){
            var change_money   = bkg_pay_price - totalPrice;
            $('#changeMoney').val(change_money.formatMoney(2, '.', ''));
        } else {
            $('#changeMoney').val("0.00");
        }
    }
}

function beforeSaveRecord() {
    // Check input required
    var returnVal 				= false;

    // Not input booking
    if(!hasInputError() && $('input[name="pkg_id[]"]').length == 0 && $('input[name="svl_id[]"]').length == 0) {
    	parent.showActionDialog({
            title	: 'คุณยังไม่ได้ป้อนข้อมูล',
            message : 'โปรดป้อนข้อมูลรายละเอียดการจองอย่างน้อย 1 รายการค่ะ',
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

function hasCustomer(type) {
    if($('input[name="cus_id"]').val() == '') {
        var title = 'กรุณาเลือกผู้ใช้บริการ';
        var msg = 'กรุณาเลือกผู้ใช้บริการก่อนจึงจะสามารถเลือก';
        if(type == 'svl') {
            msg += 'รายการบริการได้';
        } else {
            msg += 'แพ็คเกจได้';
        }
        
        parent.showActionDialog({
            title: title,
            message: msg,
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
        return false;
    } else {
        return true;
    }
}

function pullBkgEmp(data) {
    var randNum = data.empInput.attr('data-randNum');
    var date = '';
    var time = '';

    if(data.type == 'svl') {
        var svl_id = $('#svl_id_' + randNum).find('.selectReferenceJS-input').val();
        var svl_qty = $('#svl_qty_' + randNum).val();
        var svl_min = $('#svl_min_' + randNum).text();
        date = data.empInput.parent().parent().find('input[name="bkgsvl_date[]"]').val();
        time = data.empInput.parent().parent().find('input[name="bkgsvl_time[]"]').val();

        // Check for skip
        if(svl_id != '' && svl_qty != '' && parseInt(svl_qty) == 1 && date != '' && time != '') {
            var timeEnd = addMinutes(time, svl_min);
            if(isDateThaiFormat(date)) {
                date = getRealDate(date);
            } else {
                date = tmpDateToRealDate(date);
            }
            ajaxPullBkgEmp({
                date: date,
                time: time,
                timeEnd: timeEnd,
                empInput: data.empInput,
                success:
                function() {
                    data.empInput.parent().parent().find('.bkgemp_col').css('display','table-cell');
                }
            });
        } else {
            // alert('no pull');
            data.empInput.parent().parent().find('.bkgemp_col').css('display','none');
            data.empInput.find('.selectReferenceJS-text').text('ไม่ระบุ');
            data.empInput.find('.selectReferenceJS-input').val('');
        }
    }
}

function ajaxPullBkgEmp(data) {
    // Show loader
    $(data.empInput).parent().find('.pullEmpId-loader').css('display','inline-block');
    

    $.ajax({
        url: '../common/ajaxPullEmpIdOfBooking.php',
        type: 'POST',
        data: {
            date: data.date,
            time: data.time,
            timeEnd: data.timeEnd
        },
        success:
        function(responseJSON) {
            console.log(responseJSON);
            var response = $.parseJSON(responseJSON);
            if(response.status == 'PASS') {
                // Add options
                var empIdListHTML = '';
                for(i in response.returnEmpId) {
                    empIdListHTML  += '<li style="display: list-item;" id="emp_id_' + response.returnEmpId[i].emp_id + '">'
                                    + '     <span class="text">' + response.returnEmpId[i].fullName + '</span>'
                                    + '     <span class="value">' + response.returnEmpId[i].emp_id + '</span>'
                                    + '</li>';
                }
                data.empInput.find('.option-container').html(empIdListHTML);

                addEventSelectReferenceJSLi({
                    elem : data.empInput
                });
            } else if(response.status == 'EMPTY') {
                $(data.empInput).css('display','none');
            } else {
                alert(response.status);
            }
            

            if(typeof(data.success) == 'function') {
                // Hide loader
                $(data.empInput).parent().find('.pullEmpId-loader').css('display','none');
                $(data.empInput).css('display','inline-block');
                data.success(); // calback
            }
        }
    })
}

function addMinutes(time, addMin) {
    var now = new Date();
    var date = new Date(now.getMonth()+ '/' +now.getDate()+ '/' +now.getFullYear() + ' ' + time + ':00');
    if(addMin > 0) {
        var after = new Date(date.getTime() + addMin*60000);
    } else {
        var after = date;
    }
    return after.getHours() + ':' + (after.getMinutes()<10?'0':'') + after.getMinutes();
}