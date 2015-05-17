var oldPersons;

$(document).ready(function() {
	$('#grid-view-btn').click(function() {
		$('.change-view a').removeClass('active');
		$(this).addClass('active');
		$('.productList .item').removeClass('list-view');
	});
	$('#list-view-btn').click(function() {
		$('.change-view a').removeClass('active');
		$(this).addClass('active');
		$('.productList .item').addClass('list-view');
	});

	$('.addCartSuccess').slideUp(0);

	$('input[name="persons"]').focus(function() {
		oldPersons = $(this).val();
	});
	$('input[name="persons"]').change(function() {
		if(!validateNumberMoreThanZero($(this).val())) {
			$(this).val(oldPersons);
		}
	});
});

function getShopPath(category) {
	var path = 'shop_service_lists.php';
	var page = 1;
	if(category == 'packages') {
		path = 'shop_packages.php'
	} else if(category == 'products') {
		path = 'shop_products.php'
	} else if(category == 'foods') {
		path = 'shop_foods.php'
	}
	var itemDisplay = $('select[name="itemDisplay"]').val();
	var sort = $('select[name="sort"]').val();
	path += '?sort=' + sort + '&itemDisplay=' + itemDisplay + '&page=' + page;
	return path;
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
