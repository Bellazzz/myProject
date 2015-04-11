$(document).ready(function () {
    //Init
	var ftbTitle;
	if(action == 'ADD') {
		ftbTitle = 'เพิ่มข้อมูล' + tableNameTH;
	} else if(action == 'EDIT') {
		ftbTitle = 'แก้ไขข้อมูล' + tableNameTH + ' (' + code + ')';
	} else if(action == 'VIEW_DETAIL') {
		ftbTitle = 'ข้อมูล' + tableNameTH + ' (' + code + ')';
	}
	$('#ftb-title').html(ftbTitle);

    // Button Click
    $('#save-btn').click(function () {
    	if(checkRequiredInput()) {
	    	if(action == 'EDIT'){
		        confirmSaveRecord();
	    	} else if (action == 'ADD'){
	    		saveRecord();
	    	}
	    } else {
	    	if(parent.$('.action-dialog').length == 0 && parent.table.name != 'orders' && 
	    		parent.table.name != 'service_list_promotions' && 
	    		parent.table.name != 'package_promotion_details' && 
	    		parent.table.name != 'promotion_discout_sales' && 
	    		parent.table.name != 'product_promotions' && 
	    		parent.table.name != 'promotion_products' && 
	    		parent.table.name != 'withdraws' &&
	    		parent.table.name != 'package_promotions' && 
	    		parent.table.name != 'service_list_promotion_details' && 
	    		parent.table.name != 'holidays') {
	    		parent.showActionDialog({
			        title: 'ข้อมูลไม่ถูกต้อง',
			        message: 'คุณป้อนข้อมูลไม่ครบ หรือรูปแบบข้อมูลไม่ถูกต้อง '
			        		+'โปรดตรวจสอบข้อความแจ้งเตือนและแก้ไขให้ถูกต้องด้วยค่ะ',
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
			        boxWidth: 450
			    });
	    	}
	    }
    });
    $('#cancel-btn').click(function () {
        parent.confirmCloseFormTable(action);
		clearTempImage();
    });
    $('#edit-btn').click(function () {
        parent.openFormTable('EDIT', code);
    });
    $('#back-btn').click(function() {
    	history.back();
    });

    // Check Input required and pattern
    $('#form-table input:enabled').filter('[require],[valuepattern]').focusout(validateInput);
    $('#form-table textarea:enabled').filter('[require],[valuepattern]').focusout(validateInput);

    // Fixed image in view detail
    $('.table-view-detail-image img').load(function() {
    	var width 	= $(this).width();
    	var height 	= $(this).height();
    	if(height > 350) {
    		$(this).height(350);
    	} else if(width > 470) {
    		$(this).width(470);
    	}
    });
});

function cancelBtnClick() {
	$('#cancel-btn').click();
}

function confirmSaveRecord() {
	parent.showActionDialog({
        title: 'บันทึกการแก้ไข',
        message: 'คุณต้องการแก้ไขข้อมูลใช่หรือไม่?',
        actionList: [
            {
                id: 'ok',
                name: 'ตกลง',
                desc: 'บันทึกการเปลี่ยนแปลงข้อมูล',
                func:
                function() { 
                	saveRecord();
                    parent.hideActionDialog();
                }
            },
            {
                id: 'cancel',
                name: 'ยกเลิก',
                desc: 'ยกเลิกการเปลี่ยนแปลงข้อมูล',
                func:
                function() {
                    parent.hideActionDialog();
                }
            }
        ],
        boxWidth: 400
    });
}

function saveRecord() {
	// Convert thai date to real date
	$('.mbk-dtp-th').each(function() {
		if($(this).val() != '') {
			if(isDateThaiFormat($(this))) {
				getRealDate($(this));
			} else {
				convertThaiDate($(this));
				getRealDate($(this));
			}
		}
	});

	$.ajax({
		url: ajaxUrl,
		type: 'POST',
		data: {
			'ajaxCall'			: true,
			'action'			: action,
			'tableName'			: tableName,
			'code'				: code,
			'formData'			: $('#form-table').serialize()
		},
		success:
		function (responseJSON) {
			// alert(responseJSON);
			var response = $.parseJSON(responseJSON);
			if (response.status == 'ADD_PASS') {
				// Add record success
				parent.closeFormTable();
				parent.refreshTable();
			} else if (response.status == 'EDIT_PASS') {
				// Edit record success
				parent.closeFormTable();
				parent.refreshTable();
			} else if (response.status == 'REQURIED_VALUE') {
				// Add required
				$('#' + response.text).addClass('required');
				$('#' + response.text).focus();
			} else if(response.status== 'UNIQUE_VALUE') {
				// Add required
				$('#' + response.text).addClass('required');
				$('.err-' + response.text).css('display', 'none');
				$('#err-' + response.text + '-unique').css('display', 'block');
				$('#' + response.text).focus();
			} else {
				alert(response.status + "\n" + response.text);
			}
		}
	});
}

function validateInput() {
	var id = $(this).attr('id');

	// Clear error
	$(this).removeClass('required');
	$('.err-' + id).css('display', 'none');
	
	var value = '';
	if($(this).hasClass('select-reference')) {
		value = $(this).find('.select-reference-input').val();
	} else if($(this).hasClass('selectReferenceJS')) {
		value = $(this).find('.selectReferenceJS-input').val();
	} else {
		value = $(this).val();
	}
	if (value == '') {
        var attrRequire = $(this).attr('require');
    	if (typeof attrRequire !== typeof undefined && attrRequire !== false) {
    		// Check require
    		$(this).addClass('required');
    		$('#err-' + id + '-require').css('display', 'block');
		}
    } else {
    	var attrPattern = $(this).attr('valuepattern');
    	if (typeof attrPattern !== typeof undefined && attrPattern !== false) {
    		// Validate value pattern
    		if(attrPattern == 'money') {
    			if(!validateMoney(value)) {
					$('#err-' + id + '-money').css('display', 'block');
					$(this).addClass('required');
				}
    		} else if(attrPattern == 'email') {
    			if(!validateEmail(value)) {
					$('#err-' + id + '-email').css('display', 'block');
					$(this).addClass('required');
				}
    		} else if(attrPattern == 'number') {
    			if(!validateNumber(value)) {
					$('#err-' + id + '-number').css('display', 'block');
					$(this).addClass('required');
				}
    		} else if(attrPattern == 'percent') {
    			if(!validatePercent(value)) {
					$('#err-' + id + '-percent').css('display', 'block');
					$(this).addClass('required');
				}
    		} else if(attrPattern == 'character') {
    			if(!validateCharacter(value)) {
					$('#err-' + id + '-character').css('display', 'block');
					$(this).addClass('required');
				}
    		} else if(attrPattern == 'tel') {
    			if(!validateTel(value)) {
					$('#err-' + id + '-tel').css('display', 'block');
					$(this).addClass('required');
				}
    		} else if(attrPattern == 'minute') {
    			if(!validateMinute(value)) {
					$('#err-' + id + '-minute').css('display', 'block');
					$(this).addClass('required');
				}
    		} else if(attrPattern == 'numberMoreThanZero') {
    			if(!validateNumberMoreThanZero(value)) {
					$('#err-' + id + '-numberMoreThanZero').css('display', 'block');
					$(this).addClass('required');
				}
    		} else if(attrPattern == 'username') {
    			if(!validateUsername(value)) {
					$('#err-' + id + '-username').css('display', 'block');
					$(this).addClass('required');
				}
    		} else if(attrPattern == 'moneyMorethanEqual') {
    			if(!validateMoney(value)) {
					$('#err-' + id + '-money').css('display', 'block');
					$(this).addClass('required');
				} else {
					// validate less than
					var thisVal 			= parseFloat(value);
	    			var morethanEqualId 	= $(this).attr('data-morethanEqual-id');
	    			var morethanEqualVal 	= parseFloat($('#' + morethanEqualId).val());
	    			if(thisVal < morethanEqualVal) {
	    				$('#err-' + id + '-moneyMorethanEqual').css('display', 'block');
						$(this).addClass('required');
	    			}
				}
    		}
		}
    }
}

function checkRequiredInput() {
	var pass = true;

	$('#form-table input:enabled').filter('[require],[valuepattern]').focusout();
	$('#form-table textarea:enabled').filter('[require],[valuepattern]').focusout();
	$('#form-table .select-reference').filter('[require]').each(validateInput);
	$('#form-table .selectReferenceJS').filter('[require]').each(validateInput);

	// Do someting before save
	if(typeof beforeSaveRecord == 'function') {
		if(beforeSaveRecord()) {
			pass = false;
		}
	}

	if(hasInputError()) {
		pass = false;
	}

	return pass;
}

function hasInputError() {
	var inputErr 		= $('#form-table input:enabled.required').length;
	var txtAreaErr 		= $('#form-table textarea:enabled.required').length;
	var selectRefErr 	= $('#form-table .select-reference.required').length;
	var selectRefJSErr 	= $('#form-table .selectReferenceJS.required').length;
	var allErr 			= inputErr + txtAreaErr + selectRefErr + selectRefJSErr;
	if(allErr > 0) {
		return true;
	} else {
		return false;
	}
}

function validateEmail(email) { 
    var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(email);
}

function validateMoney(money) {
	var re = /^[0-9]*(\.[0-9]{1,2})?$/;
	return re.test(money);
}

function validateNumber(number) {
	var re = /^[0-9]*$/;
	return re.test(number);
}

function validateCharacter(character) {
	var re = /^[a-zA-Zก-๙\s]+$/;
	return re.test(character);
}

function validateTel(tel) {
	var re = /^[0-9]{10}$/;
	return re.test(tel);
}

function validateMinute(min) {
	var pass = true;
	var re 	 = /^[0-9]{1,2}$/;
	if(re.test(min)) {
		if(parseInt(min) > 59) {
			pass = false;
		}
	} else {
		pass = false;
	}

	return pass;
}

function validateNumberMoreThanZero(number) {
	var pass = true;
	var re 	 = /^[0-9]+$/;
	if(re.test(number)) {
		if(parseInt(number) <= 0) {
			pass = false;
		}
	} else {
		pass = false;
	}

	return pass;
}

function validatePercent(percent) {
	var pass = true;
	var re = /^[0-9]*(\.[0-9]{1,2})?$/;
	if(re.test(percent)) {
		if(parseFloat(percent) <= 0 || parseFloat(percent) > 100) {
			pass = false;
		}
	} else {
		pass = false;
	}

	return pass;
}

function validateUsername(strUsername) {
	var pass = true;
	var re = /^[a-zA-Z]+[a-zA-Z0-9]*$/;
	if(re.test(strUsername)) {
		if(strUsername.length < 6) {
			pass = false;
		}
	} else {
		pass = false;
	}

	return pass;
}