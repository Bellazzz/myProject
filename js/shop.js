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

	$('select[name="itemDisplay"]').change(function() {
		window.location = getShopPath('service_lists');
	});
	$('select[name="sort"]').change(function() {
		window.location = getShopPath('service_lists');
	});
});

function getShopPath(category) {
	var path = 'shop_service_lists.php';
	var page = 1;
	if(category == 'packages') {
		path = 'shop_packages.php'
	}
	var itemDisplay = $('select[name="itemDisplay"]').val();
	var sort = $('select[name="sort"]').val();
	path += '?sort=' + sort + '&itemDisplay=' + itemDisplay + '&page=' + page;
	return path;
}