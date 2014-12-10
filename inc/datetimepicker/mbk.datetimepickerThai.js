/**
 * MBK datetimepicker thai (JQuery datetimepicker plugin)
 * @version 1.0
 * @Coding  Supakrit Saisin
 Example use :
 $('#inputId').datetimepicker({
		lang				: 'th',
		format 				: 'Y/m/d',
		timepicker 			:false,
		closeOnDateSelect 	:true,
		scrollInput 		:false,
		yearOffset 			:543,
		onSelectDate: 
		function(){
		  $('#inputId').blur();
		}
	});
 */

var monthsTH = {
  	January		: 'มกราคม',
  	February	: 'กุมภาพันธ์',
  	March		: 'มีนาคม',
  	April		: 'เมษายน',
  	May			: 'พฤษภาคม',
  	June		: 'มิถุนายน',
  	July		: 'กรกฎาคม',
  	August		: 'สิงหาคม',
  	September	: 'กันยายน',
  	October		: 'ตุลาคม',
  	November	: 'พฤศจิกายน',
  	December	: 'ธันวาคม',
};
var monthsTHMin = {
  	January		: 'ม.ค.',
  	February	: 'ก.พ.',
  	March		: 'มี.ค.',
  	April		: 'เม.ย.',
  	May			: 'พ.ค.',
  	June		: 'มิ.ย.',
  	July		: 'ก.ค.',
  	August		: 'ส.ค.',
  	September	: 'ก.ย.',
  	October		: 'ต.ค.',
  	November	: 'พ.ย.',
  	December	: 'ธ.ค.',
};
var monthsTH2 = {
	'0'			: 'มกราคม',
  	'1'			: 'กุมภาพันธ์',
  	'2'			: 'มีนาคม',
  	'3'			: 'เมษายน',
  	'4'			: 'พฤษภาคม',
  	'5'			: 'มิถุนายน',
  	'6'			: 'กรกฎาคม',
  	'7'			: 'สิงหาคม',
  	'8'			: 'กันยายน',
  	'9'			: 'ตุลาคม',
  	'10'		: 'พฤศจิกายน',
  	'11'		: 'ธันวาคม'
};
var checkTimer;

$(document).ready(function() {
	// Add event MBK dtp thai
	$('.mbk-dtp-th').each(function() {
		var dtp = $(this);
		// Convert date default
		if(dtp.val() != '' && !isDateThaiFormat(dtp)) {
			realDateToThaiDate(dtp);
		}
		// Convert when select date
		dtp.focusout(function() {
			//Unconvert before convert
			if(isDateThaiFormat(dtp)) {
				unconvertThaiDate(dtp);
			}
			setTimeout(function() {
				convertThaiDate(dtp);
			}, 1);
		});
		// for hilight selected date
		dtp.click(function() {
			if(dtp.val() != '') {
				unconvertThaiDate(dtp);
			}
		});
		dtp.change(function() {
			clearTimeout(checkTimer);
			checkTimer = setTimeout(function() {
				if(dtp.val() != '' && !isDateThaiFormat(dtp)) {
					convertThaiDate(dtp);
				}
			}, 5);
		});
	});
});

function isDateThaiFormat(input) {
	var value = typeof(input) == 'string' ? input : input.val();
	for(i in monthsTH) {
		if(value.indexOf(monthsTH[i]) > -1 || value.indexOf(monthsTHMin[i]) > -1) {
			return true;
		}
	}
	return false;
}

function realDateToThaiDate(input) {
	// Skip if empty value
	if(typeof(input) == 'string') {
		if(input == '') {
			return;
		}
	} else {
		if(input.val() == '') {
			return;
		}
	}

	// Convert
	var realDate 		= typeof(input) == 'string' ? input : input.val();
	var realDateObj 	= new Date(realDate);
	var date 			= realDateObj.getDate();
	var month 			= monthsTH2[realDateObj.getMonth().toString()];
	var year 			= realDateObj.getFullYear()+543;
	var thaiDate 		= date + ' ' + month + ' ' + year; // Concat
	if(typeof(input) == 'string') {
		return(thaiDate);
	} else {
		input.val(thaiDate);
	}
}

function realDateToTmpDate(input) {
	// Skip if empty value
	if(typeof(input) == 'string') {
		if(input == '') {
			return;
		}
	} else {
		if(input.val() == '') {
			return;
		}
	}

	// Convert
	var realDate 		= typeof(input) == 'string' ? input : input.val();
	var realDateObj 	= new Date(realDate);
	var date 			= realDateObj.getDate();
	var month 			= realDateObj.getMonth()+1;
	var year 			= realDateObj.getFullYear()+543;
	// Add zero
	if(date.toString().length < 2) {
		date = '0' + date;
	}
	if(month.toString().length < 2) {
		month = '0' + month;
	}
	var tmpDate = year + '/' + month + '/' + date; // Concat
	if(typeof(input) == 'string') {
		return(tmpDate);
	} else {
		input.val(tmpDate);
	}
}

function convertThaiDate(input) {
	// Skip if empty value
	if(typeof(input) == 'string') {
		if(input == '') {
			return;
		}
	} else {
		if(input.val() == '') {
			return;
		}
	}

	// Convert
	var engDate 	= typeof(input) == 'string' ? input : input.val();
	var engDateObj 	= new Date(engDate);
	var date 		= engDateObj.getDate();
	var month 		= monthsTH2[engDateObj.getMonth().toString()];
	var year 		= engDateObj.getFullYear();
	var dateTH 		= date + ' ' + month + ' ' + year; // Concat
	if(typeof(input) == 'string') {
		return(dateTH);
	} else {
		input.val(dateTH);
	}
}

function unconvertThaiDate(input) {
	// Skip if empty value
	if(typeof(input) == 'string') {
		if(input == '') {
			return;
		}
	} else {
		if(input.val() == '') {
			return;
		}
	}

	// Unconvert
	var thaiDate = typeof(input) == 'string' ? input : input.val();
	for(i in monthsTH) {
	  	if(thaiDate.indexOf(monthsTH[i]) != -1 || thaiDate.indexOf(monthsTHMin[i]) != -1) {
	  		var tmpDate;
	  		if(thaiDate.indexOf(monthsTH[i]) != -1) {
	  			tmpDate 		= thaiDate.replace(monthsTH[i], i);
	  		} else {
	  			tmpDate 		= thaiDate.replace(monthsTHMin[i], i);
	  		}
	  		var tmpDateObj 		= new Date(tmpDate);
	  		var date 			= tmpDateObj.getDate();
	  		var month 			= tmpDateObj.getMonth()+1;
	  		var year 			= tmpDateObj.getFullYear();
	  		// Add zero
	  		if(date.toString().length < 2) {
	  			date = '0' + date;
	  		}
	  		if(month.toString().length < 2) {
	  			month = '0' + month;
	  		}
	  		var engDate = year + '/' + month + '/' + date; // Concat
	  		if(typeof(input) == 'string') {
	  			return(engDate);
	  		} else {
	  			input.val(engDate);
	  			return;	  			
	  		}
	  	}
  	}
}

function getRealDate(input) {
	/*** Set date th to date eng ***/
	var dateTH = typeof(input) == 'string' ? input : input.val();
	for(i in monthsTH) {
	  	if(dateTH.indexOf(monthsTH[i]) != -1) {
	  		var dateEN 		= dateTH.replace(monthsTH[i], i);
	  		var datetimeEN 	= new Date(dateEN);
	  		var date 		=	datetimeEN.getDate();
	  		var month 		= datetimeEN.getMonth()+1;
	  		var year = datetimeEN.getFullYear()-543;
	  		// Add zero
	  		if(date.toString().length < 2) {
	  			date = '0' + date;
	  		}
	  		if(month.toString().length < 2) {
	  			month = '0' + month;
	  		}
	  		// Concat
	  		var realDate = year + '/' + month + '/' + date;

	  		if(typeof(input) == 'string') {
	  			return(realDate);
	  		} else {
	  			input.val(realDate);
	  			break;
	  		}
	  	}
	}
}

function tmpDateToRealDate(tmpDate) {
	if(tmpDate == '') {
		return '';
	}
	if(isDateThaiFormat(tmpDate)) {
		tmpDate = unconvertThaiDate(tmpDate);
	}

	var tmpDateTime = new Date(tmpDate);
	var date 		= tmpDateTime.getDate();
	var month 		= tmpDateTime.getMonth()+1;
	var year 		= tmpDateTime.getFullYear()-543;
	// Add zero
	if(date.toString().length < 2) {
		date = '0' + date;
	}
	if(month.toString().length < 2) {
		month = '0' + month;
	}
	// Concat
	var realDate = year + '/' + month + '/' + date;
	return(realDate);
}