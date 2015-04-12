var oldCusId   = '';
var oldCusName = '';
var oldCusTypeId = '';
var oldBkgId   = '';
var oldBkgName = '';
var oldSerDate = '';

$(document).ready(function(){
	$('#addPackageBtn').click(addPackage);
	$('#addServiceListBtn').click(addServiceList);

	//Cal change money
    $('#ser_pay_price').change(function() {
        calSummary(false);
    });
    $('#ser_pay_price').click(function() {
        $(this).select();
    });
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
                unitPrice   : valuesSvl[i].svl_price,
                sersvl_time : valuesSvl[i].sersvl_time
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

        for(i in valuesPkgDtl) {
            addPkgCommission({
                defaultValue    : true,
                pkgdtl_id       : valuesPkgDtl[i].pkgdtl_id,
                pkg_id          : valuesPkgDtl[i].pkg_id,
                svl_id          : valuesPkgDtl[i].svl_id,
                emp_id          : valuesPkgDtl[i].emp_id,
                com_rate        : valuesPkgDtl[i].com_rate
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

function saveOldBkgId() {
    oldBkgId      = $('input[name="bkg_id"]').val();
    oldBkgName    = $('#bkg_id').find('.selectReferenceJS-text').text();
    return true;
}

function setBookingId(bkg_id) {
    for(i in refBkgData) {
        if(refBkgData[i].refValue == bkg_id) {
            var bookingId = refBkgData[i].bkg_id;
            $('#bkg_id_' + bookingId).click();
        }
    }
}

function getBookingId(bkg_id) {
    for(i in refBkgData) {
        if(refBkgData[i].refValue == bkg_id) {
            return refBkgData[i].bkg_id;
        }
    }
    return '';
}

function saveSerDate() {
    oldSerDate = $('input[name="ser_date"]').val();
    return true;
}

function setSerDate(ser_date) {
    $('#ser_date').val(ser_date);
}

function getSerDate(bkg_id) {
    return $('#ser_date').val();;
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
                        $('#bkg_id').find('.clear-value-btn').click();
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

function allowChangeBookingId() {
    var alert = false;
    var title = '';
    var msg = '';

    if(action == 'EDIT') {
        title = 'ไม่สามารถแก้ไขรหัสการจองได้';
        msg = 'การใช้บริการที่บันทึกแล้วจะไม่สามารถแก้ไขรหัสการจองได้';
        alert = true;
    } else if($('input[name="cus_id"]').val() == '') {
        title = 'กรุณาเลือกผู้ใช้บริการ';
        msg = 'กรุณาเลือกผู้ใช้บริการก่อนจึงจะสามารถเลือกรหัสการจองได้';
        alert = true;
    } else if($('#ser_date').val() == '') {
        title = 'กรุณาเลือกวันที่ใช้บริการ';
        msg = 'กรุณาเลือกวันที่ใช้บริการก่อนจึงจะสามารถเลือกรหัสการจองได้';
        alert = true;
    } else {
        return true;
    }

    if(alert) {
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
    }
}

function changeBkgId() {
    newBkgId = $('input[name="bkg_id"]').val();
    if(($('input[name="pkg_id[]"]').length > 0 || $('input[name="svl_id[]"]').length > 0) 
        && oldBkgId != newBkgId) {
        var msg         = 'การเปลี่ยนรหัสการจองจำเป็นต้องเคลียร์ข้อมูลรายละเอียดการใช้บริการใหม่ '
                        + 'คุณแน่ใจหรือไม่ที่จะเปลี่ยนรหัสการจอง?';
        parent.showActionDialog({
            title: 'เปลี่ยนรหัสการจอง',
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
                        $('#addPackageBtn').css('display', 'none');
                        $('#addServiceListBtn').css('display', 'none');
                        setBookingId(newBkgId);
                        pullBkgPkgAndBkgSvl(function() {
                            setAllSerDetailAmount();
                            calSummary();
                        });
                        parent.hideActionDialog();
                    }
                },
                {
                    id: 'cancel',
                    name: 'ยกเลิก',
                    desc: 'ยกเลิกการเปลี่ยนรหัสการจอง',
                    func:
                    function() {
                        parent.hideActionDialog();
                        $('#bkg_id').find('.selectReferenceJS-input').val(oldBkgId);
                        $('#bkg_id').find('.selectReferenceJS-text').text(oldBkgId);
                    }
                }
            ],
            boxWidth: 500
        });
    } else {
        setBookingId(newBkgId);
        if(newBkgId != '') {
            pullBkgPkgAndBkgSvl();
        }
    }
}

function changeSerDate() {
    var newSerDate = $('input[name="ser_date"]').val();
    if($('input[name="bkg_id"]').val() != '') {
        var msg         = 'การเปลี่ยนวันที่ใช้บริการจำเป็นต้องเคลียร์ข้อมูลรหัสการจองใหม่ '
                        + 'คุณแน่ใจหรือไม่ที่จะเปลี่ยนวันที่ใช้บริการ?';
        parent.showActionDialog({
            title: 'เปลี่ยนวันที่ใช้บริการ',
            message: msg,
            actionList: [
                {
                    id: 'change',
                    name: 'เปลี่ยน',
                    desc: 'ข้อมูลรหัสการจองจะถูกเคลียร์',
                    func:
                    function() {
                        $('#bkg_id').find('.clear-value-btn').click();
                        $('#addPackageBtn').css('display', 'inline');
                        $('#addServiceListBtn').css('display', 'inline');
                        setSerDate(newSerDate);
                        pullBkgId();
                        $('#ser_date').blur();
                        parent.hideActionDialog();
                        
                    }
                },
                {
                    id: 'cancel',
                    name: 'ยกเลิก',
                    desc: 'ยกเลิกการเปลี่ยนวันที่ใช้บริการ',
                    func:
                    function() {
                        parent.hideActionDialog();
                        setSerDate(oldSerDate);
                        $('#ser_date').blur();
                    }
                }
            ],
            boxWidth: 500
        });
    } else {
        setSerDate(newSerDate);
        pullBkgId();
    }
}

function pullBkgId(success) {
    var serDate = tmpDateToRealDate($('#ser_date').val());
    $('.pullBkgId-loader').css('display', 'inline');

    $.ajax({
        url: '../common/ajaxPullBkgIdOfSerTable.php',
        type: 'POST',
        data: {
            ser_date: serDate
        },
        success:
        function(responseJSON) {
            var response = $.parseJSON(responseJSON);
            var bkgIdListHTML = '';
            for(i in response) {
                bkgIdListHTML  += '<li style="display: list-item;" id="bkg_id_' + response[i] + '">'
                                + '     <span class="text">' + response[i] + '</span>'
                                + '     <span class="value">' + response[i] + '</span>'
                                + '</li>';
            }
            $('#bkg_id').find('.option-container').html(bkgIdListHTML);

            addEventSelectReferenceJSLi({
                elem            : $('#bkg_id'),
                allowChangeOption   : saveOldBkgId,
                onOptionSelect      : changeBkgId
            });

            if(typeof(success) == 'function') {
                success();
            }

            $('.pullBkgId-loader').css('display', 'none');
        }
    });
}

function pullBkgPkgAndBkgSvl(success) {
    var serDate = nowDate;
    if($('#ser_date').val() != '') {
        serDate = getRealDate($('#ser_date').val());
    }

    $.ajax({
        url: '../common/ajaxPullBkgPkgAndBkgSvl.php',
        type: 'POST',
        data: {
            bkg_id: $('input[name="bkg_id"]').val(),
            ser_date: serDate
        },
        success:
        function(responseJSON) {
            var response = $.parseJSON(responseJSON);
            for(i in response.pkg) {
                addPackage({
                    defaultValue    : true,
                    pullPkg         : true,
                    pkg_id          : response.pkg[i],
                    pkg_qty         : 1
                });
            }
            for(i in response.svl) {
                addServiceList({
                    defaultValue : true,
                    pullSvl         : true,
                    svl_id      : response.svl[i],
                    svl_qty     : 1,
                });
            }

            $('#addPackageBtn').css('display', 'none');
            $('#addServiceListBtn').css('display', 'none');

            if(typeof(success) == 'function') {
                success();
            }
        }
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
                    + '     <td style="padding-left:40px;" class="qty-column">';

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
        beforeShow      : allowChangePackage,
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
        function(defaultKey) {
            $('input[name="' + inputKeyId + '"]').attr('name', 'pkg_id[]');
            setAllSerDetailAmount();
            pullPkgUnitPrice(inputKeyId);
            addPkgPrmSale($('#' + inputKeyId).find('input[name="pkg_id[]"]').val());
            calSummary();
            if(typeof(defaultKey) != 'undefined' && typeof(data.serpkg_id) != 'undefined') {
                addServiceListOfPackage({
                    pkg_id          : defaultKey,
                    parentRandNum   : randNum,
                    addPkgCom       : false
                });
            }
            if(typeof(data.pullPkg) != 'undefined') {
                addServiceListOfPackage({
                    pkg_id          : defaultKey,
                    parentRandNum   : randNum,
                });
            }
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

    function allowChangePackage() {
        if($('input[name="bkg_id"]').val() != '') {
            parent.showActionDialog({
                title: 'ไม่สามารถเปลี่ยนแพ็คเกจได้',
                message: 'ไม่สามารถเลือกแพ็คเกจอื่นนอกเหนือจากแพ็คเกจที่ลูกค้าจองไว้',
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
                    $('#packagePkgSvlRow_' + randNum).remove();
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
    var addPkgCom = true;
    var svlPkgTd  = $('#packagePkgSvlRow_' + data.parentRandNum + ' > td');
    var initHTML  = '<span class="com-list-title" data-status="1"><i class="fa fa-chevron-down"></i> ซ่อนพนักงานที่ให้บริการ</span>'
                    + '<div class="pkgsvl-list-container"></div>';
    if(typeof(data.addPkgCom) != 'undefined') {
        addPkgCom = data.addPkgCom;
    }
    svlPkgTd.html(initHTML);
    svlPkgTd.find('.com-list-title').click(function() {
        var stat = $(this).attr('data-status');
        if(stat == "1") {
            $(this).parent().find('.pkgsvl-list-container').css('display', 'none');
            $(this).attr('data-status', '0');
            $(this).html('<i class="fa fa-chevron-right"></i> แสดงพนักงานที่ให้บริการ');
        } else {
            $(this).parent().find('.pkgsvl-list-container').css('display', 'block');
            $(this).attr('data-status', '1');
            $(this).html('<i class="fa fa-chevron-down"></i> ซ่อนพนักงานที่ให้บริการ');
        }
    });

    for(i in pkgsvlData[data.pkg_id]) {
        var no        = parseInt(i) + 1;
        var svl_id    = pkgsvlData[data.pkg_id][i].svl_id;
        var svl_name  = pkgsvlData[data.pkg_id][i].svl_name;
        var addPkgComBtnRandNum;
        do {
            addPkgComBtnRandNum     = parseInt(Math.random()*1000);
        } while($('#addPkgComBtn_' + addPkgComBtnRandNum).length > 0);
        var pkgsvlHTML= '<div class="pkgsvl-list">'
                      + '   ' + no + '. ' + svl_name
                      + '   <input type="hidden" class="svl_id" name="pkgCom_'+ data.pkg_id + '_svl_id[]" value="' + svl_id + '">'
                      + '   <div id="pkgsvl-list-com-container_' + data.pkg_id + '_' + svl_id + '" class="pkgsvlCom-list-container">'
                      + '   <div class="pkgsvlCom-list-container-body"></div>'
                      + '</div>'
                      + '<span class="errInputMsg com-err-empty com-err">กรุณาป้อนค่าคอมมิชชั่น</span>'
                      + '<span class="errInputMsg com-err-notNum com-err">กรุณาป้อนค่าคอมมิชชั่นเป็นตัวเลข</span>'
                      + '<span class="errInputMsg com-err-zero com-err">ค่าคอมมิชชั่นไม่สามารถเป็น 0 ได้</span>'
                      + '<span class="errInputMsg com-err-over com-err">ค่าคอมมิชชั่นเกิน 100%</span>'
                      + '<span class="errInputMsg com-err-less com-err">ค่าคอมมิชชั่นไม่ครบ 100%</span>'
                      + '<button id="addPkgComBtn_' + addPkgComBtnRandNum + '" data-svlId="' + svl_id + '" class="addPkgComBtn button button-icon button-icon-add">เพิ่มพนักงาน</button>'
                      + '</div>';
        svlPkgTd.find('.pkgsvl-list-container').append(pkgsvlHTML);

        if(addPkgCom) {
            addPkgCommission({
                pkg_id          : data.pkg_id,
                svl_id          : svl_id,
            });
        }
        
        $('#addPkgComBtn_' + addPkgComBtnRandNum).click(function() {
            addPkgCommission({
                pkg_id          : data.pkg_id,
                svl_id          : $(this).attr('data-svlId'),
            });
        });
    }
}

function addPkgCommission(data) {
    var randNum;
    var selectRefDefault = '';
    var comRate          = 100;
    do {
        randNum     = parseInt(Math.random()*1000);
    } while($('#emp_id_pkgCom_' + randNum).length > 0);
    var inputKeyId      = 'emp_id_pkgCom_' + randNum;
    var inputComRateId  = 'com_rate_pkgCom_' + randNum;

    var pkgsvlComHtml = '<div class="pkgsvlCom com-list">'
                      + '   <table cellpadding="0" cellspacing="0">'
                      + '       <tbody>'
                      + '           <tr>'
                      + '               <td class="emp-col">'
                      + '                   <div id="' + inputKeyId + '" class="selectReferenceJS form-input half" require style="width:350px;"></div>'
                      + '               </td>'
                      + '               <td class="com-rate-col"><span class="inlineBlock">ค่าคอมมิชชั่น </span>';

    // add input commisstion rate
    if(data.defaultValue) {
        pkgsvlComHtml += '                  <input id="' + inputComRateId + '" type="text" class="form-input half com_rate_input" value="' + data.com_rate + '" maxlength="6" size="6" valuepattern="number" require style="text-align:right; width:80px;">';
        selectRefDefault = data.emp_id;
        comRate          = data.com_rate;
    } else {
        pkgsvlComHtml += '                  <input id="' + inputComRateId + '" type="text" class="form-input half com_rate_input" value="100" maxlength="6" size="6" valuepattern="numberMoreThanZero" require style="text-align:right; width:80px;">';
    }

    pkgsvlComHtml    += '                   <span class="inlineBlock">%</span>&nbsp;&nbsp;&nbsp;'
                      + '                   <button id="removeSvlComBtn_' + inputKeyId + '" class="removeSvlComBtn button button-icon button-icon-delete">ลบ</button>'
                      + '               </td>';

    // add service package id for update
    if(action == 'EDIT' && typeof(data.pkgdtl_id) != 'undefined') {
        pkgsvlComHtml  += ' <input name="pkgdtl_id[]" type="hidden" value="' + data.pkgdtl_id + '">'
                        + ' <input type="hidden" name="pkgCom_' + data.pkg_id + '_' + data.svl_id + '_pkgdtl_id[]" value="' + data.pkgdtl_id + '">';
    }

    pkgsvlComHtml    += '               <input type="hidden" class="emp_id" name="pkgCom_'+ data.pkg_id + '_' + data.svl_id + '_emp_id[]" value="">'
                      + '               <input id="com_rate_' + inputKeyId + '" type="hidden" class="com_rate" name="pkgCom_' + data.pkg_id + '_' + data.svl_id + '_com_rate[]" value="' + comRate + '">'
                      + '           </tr>'
                      + '       </tbody>'
                      + '   </table>'
                      + '</div>';

    $('#pkgsvl-list-com-container_' + data.pkg_id + '_' + data.svl_id).find('.pkgsvlCom-list-container-body').append(pkgsvlComHtml);
    var comListConn = $('#removeSvlComBtn_' + inputKeyId).parent().parent().parent().parent().parent().parent();

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
        group           : 'employeePkg_' + data.pkg_id + '_' + data.svl_id
    });

    $('#removeSvlComBtn_' + inputKeyId).click(function() {
        var pkgsvlList = $('#removeSvlComBtn_' + inputKeyId).parent().parent().parent().parent().parent();
        var pkgsvlCont = pkgsvlList.parent();
        if(pkgsvlCont.find('.com-list').length > 1) {
            parent.showActionDialog({
                title: 'ลบพนักงานที่ให้บริการ',
                message: 'คุณต้องการลบพนักงานที่ให้บริการใช่หรือไม่?',
                actionList: [
                    {
                        id: 'ok',
                        name: 'ตกลง',
                        func:
                        function() {
                            parent.hideActionDialog();
                            pkgsvlList.remove();
                            checkAllowChangeComRate(pkgsvlCont.parent());
                            validateComRate({
                                tdCom: pkgsvlCont.parent(),
                                inputComRate: $('#' + inputComRateId)
                            });
                        }
                    },
                    {
                        id: 'cancel',
                        name: 'ยกเลิก',
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
                title: 'ไม่สามารถลบพนักงานที่ให้บริการได้',
                message: 'พนักงานที่ให้บริการต้องมีอย่างน้อย 1 คนค่ะ',
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
    });
    $('#' + inputComRateId).change(function() {
        $('#com_rate_' + inputKeyId).val($(this).val());
        var pkgcomConn = $(this).parent().parent().parent().parent().parent().parent().parent().parent();
        validateComRate({
            tdCom: pkgcomConn,
            inputComRate: $('#' + inputComRateId)
        });
    });

    if(!data.defaultValue) {
        checkAllowChangeComRate(comListConn);
        validateComRate({
            tdCom: comListConn,
            inputComRate: $('#' + inputComRateId)
        });
    }
}

function addServiceList(data) {
    var randNum;
    var selectRefDefault = '';
    var unitPrice = '0.00';
    var svl_min = 0;
    var time = '';
    do {
        randNum     = parseInt(Math.random()*1000);
    } while($('#svl_id_' + randNum).length > 0);
    var inputKeyId  = 'svl_id_' + randNum;
    var inputQtyId  = 'svl_qty_' + randNum;
    var inputTimeId = 'svl_time_' + randNum;
    if(typeof(data.unitPrice) != 'undefined' && data.unitPrice != '') {
        unitPrice = data.unitPrice;
    }
    if(typeof(data.sersvl_time) != 'undefined' && data.sersvl_time != '') {
        time = data.sersvl_time;
    }
    if(typeof(data.sersvl_min) != 'undefined' && data.sersvl_min != '') {
        svl_min = data.sersvl_min;
    }

    // Create HTML and append
    var svlRowHTML  = '<tr class="service-list-row">'
                    + '     <td width="350px">'
                    + '         <div id="' + inputKeyId + '" class="selectReferenceJS form-input half" require style="width:350px;"></div>'
                    + '     </td>'
                    + '     <td><input id="' + inputTimeId + '" type="text" name="sersvl_time[]" class="form-input" value="' + time + '" style="width:150px;" require></td>'
                    + '     <td align="right" style="padding-right:20px;">'
                    + '         <span class="svl_min_txt">' + svl_min + ' นาที</span>'
                    + '         <input type="hidden" name="sersvl_min[]" value="' + svl_min + '">'
                    + '         <input type="hidden" name="sersvl_time_end[]">'
                    + '     </td>'
                    + '		<td align="right" style="padding-right:20px;"><span class="svl_unit_price">' + unitPrice + '</span>'
                    + '     <td style="padding-left:40px;" class="qty-column">';

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
                    + '     <td>'
                    + '         <span id="err-' + inputTimeId + '-require" class="errInputMsg half err-' + inputTimeId + '">'
                    + '             โปรดป้อนเวลาที่ใช้บริการ'
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
        beforeShow      : allowChangeServiceList,
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
                svlComTr.find('.svlCom_hidden_svldtl_id').remove();
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

            if(typeof(data.pullSvl) != 'undefined') {
                var svlComTr = $('#serviceListComRow_' + randNum);
                if(svlComTr.find('.com-list-container').length == 0) {
                    addServiceListCommission({
                        svl_id: $('#' + inputKeyId).find('input[name="svl_id[]"]').val(),
                        parentRandNum: randNum
                    });
                } else {
                    var empId = $('#' + inputKeyId).find('.selectReferenceJS-input').val();
                    svlComTr.find('.svlCom_hidden_svldtl_id').remove();
                    svlComTr.find('.emp_id').attr('name', 'svlCom_' + empId + '_emp_id[]');
                    svlComTr.find('.com_rate').attr('name', 'svlCom_' + empId + '_com_rate[]');
                } 
            }
        },
        group           : 'service_lists'
    });
    // Create time picker
    $('#' + inputTimeId).datetimepicker({
        datepicker:false,
        format:'H:i',
        step: 5
    });
    // Check Input required and pattern
    $('#' + inputQtyId).focusout(validateInput);
    $('#' + inputTimeId).focusout(validateInput);
    $('#' + inputTimeId).focusout(function() {
        setSvlTimeEnd(inputKeyId); // Set time end
    });
    // Calculate sum price
    $('#' + inputQtyId).change(function() {
        var sumPriceInput = $(this).parent().parent().find('input[name="sersvl_total_price[]"]');
        calSumPriceInput(sumPriceInput, 'service_lists');
        addSvlPrmSale($('#' + inputKeyId).find('input[name="svl_id[]"]').val());
        calSummary();
    });

    function allowChangeServiceList() {
        if($('input[name="bkg_id"]').val() != '') {
            parent.showActionDialog({
                title: 'ไม่สามารถเปลี่ยนรายการบริการได้',
                message: 'ไม่สามารถเลือกรายการบริการอื่นนอกเหนือจากรายการบริการที่ลูกค้าจองไว้',
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
                            + '         <td class="com-rate-col"><span class="inlineBlock">ค่าคอมมิชชั่น <span>';

    // add input commisstion rate
    if(data.defaultValue) {
        commissionHTML += '         <input id="' + inputComRateId + '" type="text" class="form-input half com_rate_input" value="' + data.com_rate + '" maxlength="6" size="6" valuepattern="number" require style="text-align:right; width:80px;">';
        selectRefDefault = data.emp_id;
        comRate          = data.com_rate;
    } else {
        commissionHTML += '         <input id="' + inputComRateId + '" type="text" class="form-input half com_rate_input" value="100" maxlength="6" size="6" valuepattern="numberMoreThanZero" require style="text-align:right; width:80px;">';
    }

    commissionHTML         += '         %&nbsp;&nbsp;&nbsp;'
                            + '         <button id="removeSvlComBtn_' + inputKeyId + '" class="removeSvlComBtn button button-icon button-icon-delete">ลบ</button>'
                            + '         </td>'
                            + '     </tr>'
                            + '     </tbody>'
                            + ' </table>';

    // add service package id for update
    if(action == 'EDIT' && typeof(data.svldtl_id) != 'undefined') {
        commissionHTML     += ' <input name="svldtl_id[]" type="hidden" class="svlCom_hidden_svldtl_id" value="' + data.svldtl_id + '">'
                            + ' <input type="hidden" class="svlCom_hidden_svldtl_id" name="svlCom_' + data.svl_id + '_svldtl_id[]" value="' + data.svldtl_id + '">';
    }

    commissionHTML          += ' <input type="hidden" class="emp_id" name="svlCom_' + data.svl_id + '_emp_id[]" value="">'
                            + ' <input id="com_rate_' + inputKeyId + '" type="hidden" class="com_rate" name="svlCom_' + data.svl_id + '_com_rate[]" value="' + comRate + '">'
                            + '</div>';
    if(tdCom.find('.com-list-container').length <= 0) {
        var comCont     = '<span class="com-list-title" data-status="1">'
                        + '     <i class="fa fa-chevron-down"></i> ซ่อนพนักงานที่ให้บริการ'
                        + '</span>'
                        + '<div class="com-list-container">'
                        + '     <div class="com-list-container-body"></div>'
                        + '<span class="errInputMsg com-err-empty com-err">กรุณาป้อนค่าคอมมิชชั่น</span>'
                        + '<span class="errInputMsg com-err-notNum com-err">กรุณาป้อนค่าคอมมิชชั่นเป็นตัวเลข</span>'
                        + '<span class="errInputMsg com-err-zero com-err">ค่าคอมมิชชั่นไม่สามารถเป็น 0 ได้</span>'
                        + '<span class="errInputMsg com-err-over com-err">ค่าคอมมิชชั่นเกิน 100%</span>'
                        + '<span class="errInputMsg com-err-less com-err">ค่าคอมมิชชั่นไม่ครบ 100%</span>'
                        + '<button class="addSvlComBtn button button-icon button-icon-add">เพิ่มพนักงาน</button>'
                        + '</div>';
        tdCom.append(comCont);
        tdCom.find('.com-list-title').click(function() {
            var stat = $(this).attr('data-status');
            if(stat == "1") {
                $(this).parent().find('.com-list-container').css('display', 'none');
                $(this).attr('data-status', '0');
                $(this).html('<i class="fa fa-chevron-right"></i> แสดงพนักงานที่ให้บริการ');
            } else {
                $(this).parent().find('.com-list-container').css('display', 'block');
                $(this).attr('data-status', '1');
                $(this).html('<i class="fa fa-chevron-down"></i> ซ่อนพนักงานที่ให้บริการ');
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
        var svlcomList = $('#removeSvlComBtn_' + inputKeyId).parent().parent().parent().parent().parent();
        var svlcomConn = svlcomList.parent();
        if(svlcomConn.find('.com-list').length > 1) {
            parent.showActionDialog({
                title: 'ลบพนักงานที่ให้บริการ',
                message: 'คุณต้องการลบพนักงานที่ให้บริการใช่หรือไม่?',
                actionList: [
                    {
                        id: 'ok',
                        name: 'ตกลง',
                        func:
                        function() {
                            parent.hideActionDialog();
                            svlcomList.remove();
                            checkAllowChangeComRate(svlcomConn.parent());
                            validateComRate({
                                tdCom: svlcomConn.parent(),
                                inputComRate: $('#' + inputComRateId)
                            });
                        }
                    },
                    {
                        id: 'cancel',
                        name: 'ยกเลิก',
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
                title: 'ไม่สามารถลบพนักงานที่ให้บริการได้',
                message: 'พนักงานที่ให้บริการต้องมีอย่างน้อย 1 คนค่ะ',
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
    });
    $('#' + inputComRateId).change(function() {
        $('#com_rate_' + inputKeyId).val($(this).val());
        var svlcomConn = $(this).parent().parent().parent().parent().parent().parent().parent().parent();
        validateComRate({
            tdCom: svlcomConn,
            inputComRate: $('#' + inputComRateId)
        });
    });

    if(!data.defaultValue) {
        checkAllowChangeComRate(tdCom);
        validateComRate({
            tdCom: tdCom,
            inputComRate: $('#' + inputComRateId)
        });
    }
    
}

function validateComRate(data) {
    var comRateInputs = data.tdCom.find('.com_rate_input');
    var total = 0;
    var empty = 0;
    var zero  = 0;
    var notNum = 0;
    var pass  = true;
    var notNumInputs = Array();
    var emptyInputs = Array();
    var zeroInputs  = Array();

    // fill auto
    if(comRateInputs.length == 2) {
        comRateInputs.each(function() {
            if($(this).attr('id') != data.inputComRate.attr('id')) {
                var enterVal = parseFloat(data.inputComRate.val());
                if(enterVal <= 100) {
                    var mirrorConRateInput = $(this).parent().parent().parent().parent().parent().find('.com_rate');
                    var percent = 100 - enterVal;
                    $(this).val(percent);
                    mirrorConRateInput.val(percent)
                }
            }
        });
    }

    comRateInputs.each(function() {
        if($(this).val() == '') {
            empty++;
            emptyInputs.push($(this));
        } else if(!validateNumber($(this).val())) {
            notNum++;
            notNumInputs.push($(this));
        } else {
            if($(this).val() == '0') {
                zero++;
                zeroInputs.push($(this));
            }
            total += parseFloat($(this).val());
        }
        // Clear error
        $(this).removeClass('required');
    });

    // Clear error
    data.tdCom.find('.com-err').css('display', 'none');

    // Add error
    if(empty > 0) {
        data.tdCom.find('.com-err-empty').css('display', 'block');
        pass = false; 
        for(i in emptyInputs) {
            emptyInputs[i].addClass('required');
        }
    } else if(notNum > 0) {
        data.tdCom.find('.com-err-notNum').css('display', 'block');
        pass = false; 
        for(i in notNumInputs) {
            notNumInputs[i].addClass('required');
        }
    } else if(zero > 0) {
        data.tdCom.find('.com-err-zero').css('display', 'block');
        pass = false;
        for(i in zeroInputs) {
            zeroInputs[i].addClass('required');
        }
    } else if(total > 100) {
        data.tdCom.find('.com-err-over').css('display', 'block');
        pass = false;
        comRateInputs.each(function() {
            $(this).addClass('required');
        });
    } else if(total < 100) {
        data.tdCom.find('.com-err-less').css('display', 'block');
        pass = false;
        comRateInputs.each(function() {
            $(this).addClass('required');
        });
    }

    return pass;
}

function checkAllowChangeComRate(tdCom) {
    var comRateInputs   = tdCom.find('.com_rate_input');
    var disable         = false;
    if(comRateInputs.length == 1) {
        // Disable
        disable = true;
        comRateInputs.each(function() {
            var mirrorConRateInput = $(this).parent().parent().parent().parent().parent().find('.com_rate');
            $(this).val(100);
            mirrorConRateInput.val(100);
        });
    } else if(comRateInputs.length == 2) {
        comRateInputs.each(function() {
            var mirrorConRateInput = $(this).parent().parent().parent().parent().parent().find('.com_rate');
            $(this).val(50);
            mirrorConRateInput.val(50);
        });
    }

    comRateInputs.each(function() {
        $(this).prop('disabled', disable);
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
        var svlMinTxt = $('#' + inputKeyId).parent().parent().find('.svl_min_txt');
        var svlMinInput = $('#' + inputKeyId).parent().parent().find('input[name="sersvl_min[]"]');
        var sersvlTimeInput = $('#' + inputKeyId).parent().parent().find('input[name="sersvl_time[]"]');
        var unitPrice    = '';
        var svl_min = '';

        for(i in refSvlData) {
            if(refSvlData[i].refValue == svlID) {
                unitPrice = parseFloat(refSvlData[i].svl_price);
                svl_min = parseInt(refSvlData[i].svl_min);
                break;
            }
        }

        
        setSvlTimeEnd(inputKeyId, svl_min);
        svlUnitPrice.text(unitPrice.formatMoney(2, '.', ','));
        svlMinTxt.text(svl_min.formatMoney(0, '', ',') + ' นาที');
        svlMinInput.val(svl_min);
        calSumPriceInput($('#' + inputKeyId).parent().parent().find('input[name="sersvl_total_price[]"]'), 'service_lists');
    }
}

function setSvlTimeEnd(inputKeyId, svlMin) {
    var sersvlTimeInput = $('#' + inputKeyId).parent().parent().find('input[name="sersvl_time[]"]');
    var svl_min = svlMin;
    if(typeof(svl_min) == 'undefined') {
        var svlID = $('#' + inputKeyId).find('.selectReferenceJS-input').val();
        if(typeof(svlID) != 'undefined' && svlID != '') {
            for(i in refSvlData) {
                if(refSvlData[i].refValue == svlID) {
                    svl_min = parseInt(refSvlData[i].svl_min);
                    break;
                }
            }
        }
    }

    // cal time end
    if(sersvlTimeInput.val() != '') {
        var sersvlTimeEndInput = $('#' + inputKeyId).parent().parent().find('input[name="sersvl_time_end[]"]');
        var endTime = addMinutes(sersvlTimeInput.val(), svl_min);
        sersvlTimeEndInput.val(endTime);
        checkTimeOverlap(inputKeyId, 'service_lists');
    }
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

function checkTimeOverlap(inputKeyId, type) {
    var timeList = Array();
    var timeOverlapList = Array();
    var inputTime = '';
    var inputTimeEnd = '';
    var selectRefVal = $('#' + inputKeyId).parent().parent().find('.selectReferenceJS-input').val();

    if(selectRefVal == '') {
        return;
    }
    
    if(type == 'service_lists') {
        // get time list of service list
        inputTime = $('#' + inputKeyId).parent().parent().find('input[name="sersvl_time[]"]');
        inputTimeEnd = $('#' + inputKeyId).parent().parent().find('input[name="sersvl_time_end[]"]');

        allInputTime = $('#' + inputKeyId).parent().parent().parent().find('input[name="sersvl_time[]"]');
        allInputTime.each(function() {
            if($(this).attr('id') != inputTime.attr('id')) {
                timeList.push({
                    name: $(this).parent().parent().find('.selectReferenceJS-text').text(),
                    timeStart: $(this).val(),
                    timeEnd: $(this).parent().parent().find('input[name="sersvl_time_end[]"]').val()
                });
            }
        });
    } else {

    }

    // Check overlap
    var now = new Date();
    var timeStart = new Date(now.getMonth()+ '/' +now.getDate()+ '/' +now.getFullYear() + ' ' + inputTime.val() + ':00');
    var timeEnd = new Date(now.getMonth()+ '/' +now.getDate()+ '/' +now.getFullYear() + ' ' + inputTimeEnd.val() + ':00');
    var tmpTimeStart, tmpTimeEnd;
    for(i in timeList) {
        tmpTimeStart = new Date(now.getMonth()+ '/' +now.getDate()+ '/' +now.getFullYear() + ' ' + timeList[i].timeStart + ':00');
        tmpTimeEnd = new Date(now.getMonth()+ '/' +now.getDate()+ '/' +now.getFullYear() + ' ' + timeList[i].timeEnd + ':00');

        if((timeStart >= tmpTimeStart && timeStart <= tmpTimeEnd) || 
            (timeEnd >= tmpTimeStart  && timeEnd <= tmpTimeEnd)) {
            var tmpTimeStartTxt = tmpTimeStart.getHours() + ':' + (tmpTimeStart.getMinutes()<10?'0':'') + tmpTimeStart.getMinutes();
            var tmpTimeEndTxt = tmpTimeEnd.getHours() + ':' + (tmpTimeEnd.getMinutes()<10?'0':'') + tmpTimeEnd.getMinutes();
            var txt = timeList[i].name + ' (' + tmpTimeStartTxt + ' น. - ' + tmpTimeEndTxt + ' น.)';
            timeOverlapList.push(txt)
        }
    }

    // Show alert dialogBox
    if(timeOverlapList.length > 0) {
        var svlName = $('#' + inputKeyId).parent().parent().find('.selectReferenceJS-text').text();
        var timeStartTxt = timeStart.getHours() + ':' + (timeStart.getMinutes()<10?'0':'') + timeStart.getMinutes();
        var timeEndTxt = timeEnd.getHours() + ':' + (timeEnd.getMinutes()<10?'0':'') + timeEnd.getMinutes();
        var msg = 'คุณกำหนดให้';
        msg += (type == 'service_lists')?'รายการบริการ ':'แพ็คเกจ ';
        msg += svlName + ' มีการใช้บริการเวลา ' + timeStartTxt + ' น. - ' + timeEndTxt + ' น. ซึ่งซ้อนทับกับรายการดังต่อไปนี้ <ul>';
        for(i in timeOverlapList) {
            msg += '<li>' + timeOverlapList[i] + '</li>';
        }
        msg += '</ul><br>กรุณาป้อนเวลาที่ใช้บริการไม่ให้อยู่ในช่วงเวลาดังกล่าว';
        parent.showActionDialog({
            title: 'ระยะเวลาที่ใช้บริการซ้อนทับกัน',
            message: msg,
            actionList: [
                {
                    id: 'ok',
                    name: 'ตกลง',
                    desc: 'ป้อนเวลาที่ใช้บริการใหม่',
                    func:
                    function() {
                        parent.hideActionDialog();
                        inputTime.val('');
                        inputTimeEnd.val('');
                        inputTime.focus();
                    }
                }
            ],
            boxWidth: 650
        });
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

    if(pkgPromotions != null) {
        for(pkgID in pkgPromotions[custype_id]) {
            if(pkgID == pkg_id) {
                var prm         = pkgPromotions[custype_id][pkgID];

                // add first amount
                removePrm(tdPrm.find('.prmSale'));
                var pkgRow      = getPkgRow(pkg_id);
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
                var svlRow      = getSvlRow(svl_id);
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

function calSummary(autoPay) {
    var totalPrice          = 0;
    var totalDiscoutPrm   = 0;
    var autoPayPrice = true;
    if(typeof(autoPay) != 'undefined') {
        autoPayPrice = autoPay;
    }
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
    if(autoPayPrice) {
        $('#ser_pay_price').val(totalPrice);
    }
    
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