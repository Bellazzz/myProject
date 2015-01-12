$(document).ready(function() {
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
});

function pullEmpData(emp_id){
	$.ajax({
		url:  '../common/ajaxTimeAttendancePullEmpData.php',
		type: 'POST',
		data: {
			emp_id: emp_id
		},
		success: 
		function(response){
			alert(response);
		}
	});
}