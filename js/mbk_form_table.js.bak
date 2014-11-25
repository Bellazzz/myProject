$(document).ready(function () {
    //Init
	var ftbTitle;
	if(action == 'ADD') {
		ftbTitle = 'เพิ่มข้อมูล' + tableNameTH;
	} else {
		ftbTitle = 'แก้ไขข้อมูล' + tableNameTH + ' (' + code + ')';
	}
	$('#ftb-title').html(ftbTitle);

    // Button Click
    $('#save-btn').click(function () {
        saveRecord();
    });
    $('#cancel-btn').click(function () {
        parent.confirmCloseFormTable(action);
    });

    // Remove required
    $('.form-input').focusout(function () {
        if ($(this).val() != '') {
            $(this).removeClass('required');
        }
    });

	
});

function saveRecord() {
	if(checkRequiredInput()) {
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
					alert(response.text + ' ไม่สามารถเป็นค่าซ้ำได้');
					// Add required
					$('#' + response.text).addClass('required');
					$('#' + response.text).focus();
				} else {
					alert(response.status + "\n" + response.text);
				}
			}
		});
	}
}

function checkRequiredInput() {
	var pass = true;

	if($('input[name="requiredFields"]').length) {
		var tmp				= $('input[name="requiredFields"]').val();
		var requiredFields	= tmp.split(',');
		var hasFocus		= true;
		
		
		for(i in requiredFields) {
			var name	=  requiredFields[i];
			var input	= $('input[name="' + name + '"]');

			if(input.val() == '') {
				$('#' + name).addClass('required');
				if(hasFocus) {
					$('#' + name).focus();
					hasFocus = false;
				}
				pass = false;
			} else {
				// Check input pattern
				if(input.attr('pattern') == 'email') {
					if(!validateEmail(input.val())) {
						alert('รูปแบบ E-mail ไม่ถูกต้อง');
						$('#' + name).addClass('required');
						if(hasFocus) {
							$('#' + name).focus();
							hasFocus = false;
						}
						pass = false;
					}
				}
			}
		}
	}

	return pass;
}

function validateEmail(email) { 
    var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(email);
} 