var oldPerson;

$(document).ready(function() {
	calSummary();

	$('input[name="persons[]"]').focus(function() {
		oldPerson = $(this).val();
	});
	$('input[name="persons[]"]').change(function() {
		if(!validateNumberMoreThanZero($(this).val())) {
			$(this).val(oldPerson);
		} else {
			updateCartPersons({
				id: $(this).attr('data-id'),
				persons: $(this).val(),
				success:
				function() {
					calSummary();
				}
			});
		}
	});

	$('.bootstrap-touchspin-up').click(function() {
		var personInput = $(this).parent().parent().find('input[name="persons[]"]');
		plusOrMinusPerson(personInput, 'plus');
	});
	$('.bootstrap-touchspin-down').click(function() {
		var personInput = $(this).parent().parent().find('input[name="persons[]"]');
		plusOrMinusPerson(personInput, 'minus');
	});

	$('.deleteBtn').click(function() {
		var name = $(this).attr('data-name');
		var path = $(this).attr('href');
		parent.showActionDialog({
            title: 'ลบรายการที่จอง',
            message: 'คุณต้องการลบ "' + name + '" ออกจากการจองใช่หรือไม่?',
            actionList: [
                {
                    id: 'ok',
                    name: 'ตกลง',
                    func:
                    function() {
                        parent.hideActionDialog();
                        window.location = path;
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
        return false;
	});
});

function plusOrMinusPerson(personInput, action) {
	var person = personInput.val();
	if(action == 'plus') {
		var bkgemp_fullnames = personInput.parent().parent().parent().parent().find('.bkgemp_fullname');
		if(bkgemp_fullnames.length > 0) {
			if(confirm('การจองที่มีจำนวนผู้ใช้บริการมากกว่า 1 คน ไม่สามารถจองพนักงานได้ คุณต้องการเพิ่มจำนวนผู้ใช้บริการหรือไม่?') == true) {
				$.ajax({
					url: 'unsetBkgEmpSession.php',
					type: 'POST',
					data: {
						id: personInput.attr('data-id')
					},
					success:
					function() {
						bkgemp_fullnames.slideUp(200);
						setTimeout(function(){
							bkgemp_fullnames.remove();
						}, 200);
					}
				});
				person++;
			}
		} else {
			person++;
		}
	} else if(action == 'minus'){
		person--;
	}
	if(validateNumberMoreThanZero(person)) {
		updateCartPersons({
			id: personInput.attr('data-id'),
			persons: person,
			success:
			function() {
				personInput.val(person);
				calSummary();
			}
		});
	}
}

function updateCartPersons(data) {
	$.ajax({
		url: 'updateCartPersons.php',
		type: 'POST',
		data: {
			id: data.id,
			persons: data.persons
		},
		success:
		function() {
			if(typeof(data.success) == 'function') {
				data.success();
			}
		}
	})
}

function calSummary() {
	var totalPrice = 0;
	var totalRealPrice = 0;
	var totalDiscout = 0;
	// Service list
	$('input[name="persons[]"]').each(function() {
		if(validateNumberMoreThanZero($(this).val())) {
			var sumPrice = parseFloat($(this).val() * $(this).parent().find('.inputPrice').val());
			var sumRealPrice = parseFloat($(this).val() * $(this).parent().find('.inputRealPrice').val());
			var discoutPrice = parseFloat($(this).val() * $(this).parent().find('.inputDiscoutPrice').val());

			$(this).parent().parent().parent().find('.sumDiscoutPrice').text(discoutPrice.formatMoney(2, '.', ','));
			$(this).parent().parent().parent().find('.sumPrice').text(sumRealPrice.formatMoney(2, '.', ','));

			totalPrice += sumPrice;
			totalRealPrice += sumRealPrice;
			totalDiscout += discoutPrice;
		}
	});

	$('#total-price').text(totalPrice.formatMoney(2, '.', ','));
	$('#total-realPrice').text(totalRealPrice.formatMoney(2, '.', ','));
	$('#total-discout').text(totalDiscout.formatMoney(2, '.', ','));
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
