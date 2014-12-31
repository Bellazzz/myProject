



function validateMoney(money) {
	var re = /^[0-9]*(\.[0-9]{1,2})?$/;
	return re.test(money);
}

function posRadio(data) {
	var valChecked   = '';
	var radioHTML = '';
	if(typeof(data.valChecked) != 'undefined') {
		valChecked = data.valChecked
	}

	for(i in data.radios) {
		var radioClass = '';
		if(data.radios[i].val == valChecked) {
			radioClass = 'class="checked"';
		}

		radioHTML += '<li data-value="' + data.radios[i].val + '" ' + radioClass + '>'
					+ '		<i class="fa fa-check"></i>'
					+ data.radios[i].name
					+ '</li>'; 
	}
	data.elem.html(radioHTML);
	addEventPosRadio(data.onChecked);

	if(typeof(data.success) == 'function') {
		data.success();
	}
}

function addEventPosRadio(func) {
	$('.pos-radio li').click(function() {
		$('.pos-radio li').removeClass('checked');
		$(this).addClass('checked');

		if(typeof(func) == 'function') {
			func();
		}
	});
}