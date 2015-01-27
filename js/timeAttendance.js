var emp_id 			= '';
var emp_name 		= '';
var emp_surname 	= '';
var emp_pic			= '';
var pos_name		= '';
var dateatt_in 		= '';
var timeatt_in 		= '';
var dateatt_out 	= '';
var timeatt_out 	= '';
var curAds 			= 0;
var autoDisplayTimer;

$(document).ready(function() {
	// focus barcode input
	$('#barcode-input').focus();

	// barcode input 
	$('#barcode-input').keyup(function(e){
		e.stopPropagation();
		var code = (e.keyCode ? e.keyCode : e.which);
		if(code == 13 || code == 9) { // Enter or Tab
			if($(this).val() != '') {
				var barcode = $(this).val();
				var found 	= false;

				pullEmpData(barcode);

				// if(!found) {
				//  	alert('ไม่พบพนักงานรหัสนี้!!');
				//  	$(this).val('');
				//  }
			}
        }
	});

	$('#scan-btn').click(function(){
		pullEmpData($('#barcode-input').val());
	});

	// Display number of advertising
	var numAdsHtml = '';
	for(i in advertisingList) {
		numAdsHtml += '<a id="numAds_' + i + '" class="numAds" data-numAds="' + i + '">' + (parseInt(i)+1) + '</a>';
	}
	$('.numAds-container').html(numAdsHtml);

	// When numAds click
	$('.numAds').click(function() {
		stopDisplayAds();
		curAds = parseInt($(this).attr('data-numAds'));
		switchAds();
		refreshIntervalId  = setInterval(function() {
			switchAds();
		}, 10000);
	});

	// Auto display advertising
	switchAds();
	startDisplayAds();
});

function startDisplayAds() {
	refreshIntervalId  = setInterval(function() {
		switchAds();
	}, 10000);
}
function stopDisplayAds() {
	clearInterval(refreshIntervalId);
}

function switchAds() {
	$('.container').fadeOut(1000);
	$('.textRunner-text').fadeOut(1000);
	setTimeout(function() {
		nextAds();
		$('.container').fadeIn(1000);
		$('.textRunner-text').fadeIn(1000);
	}, 1000);
}

function nextAds() {
	if(curAds >= advertisingList.length) {
		curAds = 0;
	}
	$('.advertising-image').css('display', 'none');
	$('.advertising-txt').css('display', 'none');
	$('.numAds').removeClass('selected');

	// Set content
	$('.advertising-image').css('background-image', 'url(\'../img/advertising/' + advertisingList[curAds].avs_img + '\')');
	if(advertisingList[curAds].avs_txt == null) {
		$('.advertising-txt').text('');
		$('.textRunner-text').text('');
	} else {
		$('.advertising-txt').text(advertisingList[curAds].avs_txt);
		$('.textRunner-text').text(advertisingList[curAds].avs_txt);
	}


	if(advertisingList[curAds].avs_img != null) {
		$('.advertising-image').css('display', 'block');
		if(advertisingList[curAds].avs_txt == null) {
			$('.textRunner').fadeOut(1000);
		} else {
			$('.textRunner').fadeIn(1000);
		}
	} else if(advertisingList[curAds].avs_txt != null) {
		$('.advertising-txt').css('display', 'block');
	}

	$('#numAds_' + curAds).addClass('selected');
	curAds++;
}

function clearBarcodeInput() {
	$('#barcode-input').val('');
}
function focusBarcodeInput() {
	$('#barcode-input').focus();
}
function pullEmpData(empId){
	if(empId == '') {
		focusBarcodeInput();
		return;
	}

	$.ajax({
		url:  '../common/ajaxTimeAttendancePullEmpData.php',
		type: 'POST',
		data: {
			emp_id: empId
		},
		success: 
		function(responseJSON){
			var response = $.parseJSON(responseJSON);
			if (response.status == 'PASS'){
				// Set data of current employee
				emp_id 		= empId;
				emp_name 	= response.emp_name;
				emp_surname = response.emp_surname;
				emp_pic 	= response.emp_pic;
				pos_name 	= response.pos_name;
				dateatt_in 	= response.dateatt_in;
				timeatt_in 	= response.timeatt_in;
				dateatt_out = response.dateatt_out;
				timeatt_out = response.timeatt_out;
				showClockInOutBox();
				clearBarcodeInput();
			}else if (response.status == 'FAIL'){
				clearBarcodeInput();
				focusBarcodeInput();
				alert('ไม่พบพนักงานรหัสนี้');
			}else{
				alert(response.status);
			}
		}
	});
}

function showClockInOutBox(){
	var randNum 	= parseInt(Math.random()*1000); 
	var msg 		= '';
	var buttonTxt  	= '';

	if(dateatt_in == '') {
		// Clock in
		msg 		= 'กรุณาป้อนรหัสผ่านเพื่อบันทึกเวลาเข้างาน';
		buttonTxt 	= 'เข้างาน';
	} else {
		// CLock out
		msg 		= 'กรุณาป้อนรหัสผ่านเพื่อบันทึกเวลาออกงาน';
		buttonTxt 	= 'ออกงาน';
	}

	var clockInOutHTML  = '<div class="clockInOutBox-container">'
						+ ' 	<div class="clockInOutBox">'
						+ '			<div class="clockInOutBox-body">'
						+ '				<div class="empInfo">'
						+ '					<div class="empInfo-inner">'
						+ '						<div class="empInfo-wrapper">'
						+ '							<div class="empPicThumb" style="background-image:url(\'../img/employees/' + emp_pic + '?rand=' + randNum + '\');"></div>'
						+ '							<small>สวัสดี</small>'
						+ '							<span class="empFullName">คุณ' + emp_name + '  ' + emp_surname + '</span>'
						+ '							<span class="posName">' + pos_name + '</span>'
						+ '						</div>'
						+ '					</div>'
						+ '				</div>'
						+ '				' + msg
						+ '				<span class="password-err-require password-err">กรุณาป้อนรหัสผ่าน</span>'
						+ '				<span class="password-err-invalid password-err">รหัสผ่านที่คุณป้อนไม่ถูกต้อง โปรดลองอีกครั้ง</span>'
						+ '				<input id="passInput" type="password" class="passInput"><br><br>'
						+ '				<button id="saveTimeAttBtn" class="button btnLarge btnColor-green">' + buttonTxt + '</button><br><br>'
						+ '				<button id="closeClockInOutBtn" class="button btnColor-gray">ยกเลิก</button><br><br>'
						+ '			</div>'
						+ ' 		<div class="clockInOutBox-footer">'
						+ '			</div>'
						+ '		</div>'
						+ '</div>';

	$('body').prepend(clockInOutHTML);
	$('.clockInOutBox-container').css('visibility', 'hidden');

	// Add event
	$('#closeClockInOutBtn').click(closeClockInOutBox);
	$('#saveTimeAttBtn').click(validatePassword);
	$('#passInput').keyup(function(e){
		e.stopPropagation();
		var code = (e.keyCode ? e.keyCode : e.which);
		if(code == 13) { // enter
			validatePassword();
		}
	});

	// set position
	$('.clockInOutBox').css('margin-top', -Math.abs($('.clockInOutBox').outerHeight() / 2));
    $('.clockInOutBox').css('margin-left', -Math.abs($('.clockInOutBox').outerWidth() / 2));


    $('.clockInOutBox-container').css('visibility', 'visible');
    $('#passInput').focus();
}

function closeClockInOutBox() {
	$('.clockInOutBox-container').remove();
	clearBarcodeInput();
	focusBarcodeInput();
}

function validatePassword() {
	var password = $('#passInput').val();
	if($('#passInput').val() == '') {
		$('.password-err').css('display','none');
		$('.password-err-require').css('display','block');
		$('#passInput').focus();
		return;
	}

	$.ajax({
		url: '../common/ajaxTimeAttendanceCheckPassword',
		type: 'POST',
		data: {
			emp_id 		: emp_id,
			password 	: password
		},
		success:
		function(response) {
			if(response == 'PASS') {
				saveTimeAtt();
			} else if(response == 'FAIL') {
				$('.password-err').css('display','none');
				$('.password-err-invalid').css('display','block');
				$('#passInput').focus();
			} else {
				alert(response);
			}
		}
	});
}

function saveTimeAtt() {
	$.ajax({
		url: '../common/ajaxTimeAttendanceInsertTimeAtt.php',
		type: 'POST',
		data: {
			emp_id 		: emp_id
		},
		success:
		function(response) {
			if(response == 'PASS') {
				closeClockInOutBox();
				focusBarcodeInput();
				alert('บันทึกข้อมูลเรียบร้อย :)');
			} else if(response == 'FAIL') {
				alert('fail');
			} else {
				alert(response);
			}
		}
	});
}