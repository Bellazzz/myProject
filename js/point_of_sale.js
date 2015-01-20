var listWidth = 0;
var curPrdtyp_id = '';

$(document).ready(function() {
	$(document).keyup(function(e) {
		var code = (e.keyCode ? e.keyCode : e.which);
		var stat = getWindowStatus();

		if(stat == 'pay') {
			if(code == 13) {
				if($('#payMoney-input').is(':focus')) {
					$('#payMoney-input').blur();
				} else {
					saveSale();
				}
			} else if(code == 27) {
				closePayBox();
			}
		} else if(stat == 'editQty') {
			if (code == 27) { // ESC
				if($('#edit-quantity-product').length > 0) {
		  			closeEditQtyBox();
		  		}
			} else if(code == 38) { // Arrow up
		  		if($('#sale-product-list tr.selected').prev().length > 0) {
		  			$('#sale-product-list tr.selected').prev().click();
		  		}
		  	} else if(code == 40) { // Arrow down
		  		if($('#sale-product-list tr.selected').next().length > 0) {
		  			$('#sale-product-list tr.selected').next().click();
		  		}
		  	} else if(code == 46) { // Delete
		  		$('#removeSlvDtlBtn').click();
		  	} else if(code == 107) { // +
		  		$('#eqp-qty-plus-btn').click();
		  	} else if(code == 109) { // -
		  		$('#eqp-qty-minus-btn').click();
		  	}
		} else if(stat == 'addProduct') {
			if (code == 13) { // ENTER
				if($('#sale-product-list tr').length > 0) {
					if(hasProductInOrder()) {
						openPayBox();
					}
				}
			} else if (code == 115) { // F4
				$('#sale-product-list tr:first-child').click();
			}
		}
	});

	$('#setPrdprmgrpBtn').click(function() {
		openPrdprmgrpBox();
	});

	// barcode input 
	$('#barcode-input').keyup(function(e){
		e.stopPropagation();
		var code = (e.keyCode ? e.keyCode : e.which);
		if(code == 13 || code == 9) { // Enter or Tab
			if($(this).val() != '') {
				var barcode = $(this).val();
				var found 	= false;

				// Get product data
				for(i in productList) {
				 	if(productList[i].prd_barcode == barcode) {
				 		addSaleDetail({
							prd_id 		: productList[i].prd_id,
							prd_name 	: productList[i].prd_name,
							unit_price 	: productList[i].prd_price,
							qty: 1
						});
						found = true;
						$(this).val('');
						break;
				 	}
				 }

				if(!found) {
				 	alert('ไม่พบสินค้ารหัสนี้!!');
				 	$(this).val('');
				 }
			}
        }
	});

	// Add product type list 
	var prdtypListHtml  = '<li>'
						+ '	<div id="frequency" class="product-category-item-nopic prdtypItem">'
						+ ' 	<div class="prdtypName-container">'
						+ ' 		<div class="prdtypName-inner">'
						+ ' 			<p>เรียงตามความนิยม</p>'
						+ ' 		</div>'
						+ '		</div>'
						+ '	</div>'
						+ '</li>';
	for(i in productTypeList) {
		prdtypListHtml += '<li>'
						+ '	<div id="' + productTypeList[i].prdtyp_id + '" class="product-category-item-nopic prdtypItem">'
						+ ' 	<div class="prdtypName-container">'
						+ ' 		<div class="prdtypName-inner">'
						+ ' 			<p>' + productTypeList[i].prdtyp_name + '</p>'
						+ ' 		</div>'
						+ '		</div>'
						+ '	</div>'
						+ '</li>';
	}
	$('.product-category-list').html(prdtypListHtml);

	// Cal product type list width
	$('.product-category-list li').each(function() {
		listWidth += $(this).width();
	});

	// Hide prev & next prdtyp button
	if(listWidth <= $('.product-category-container').width()) {
		$('#prevPagePrdTypBtn').css('visibility', 'hidden');
		$('#nextPagePrdTypBtn').css('visibility', 'hidden');
	}

	// Add event prdtyp item
	$('.prdtypItem').click(function() {
		$('#searchProduct').val('');
		setCurPrdTypId($(this).attr('id'));
		$('#barcode-input').focus();
	});

	// Sale transaction control
	$('#openPayBoxBtn').click(function(){
		var stat = getWindowStatus();
		if(stat == 'addProduct' || stat == 'editQty') {
			if(hasProductInOrder()) {
				if(stat == 'editQty') {
					closeEditQtyBox();
					setTimeout(function() {
						openPayBox();
					}, 200);
				} else {
					openPayBox();
				}
			}
		}
	});
	$('#openSaleDiscountBtn').click(openSaleDiscount);
	$('#clearSaleBtn').click(function() {
		showPopupBox({
			title: 'ยกเลิกการขาย',
			content: 'คุณต้องการยกเลิกการขายใช่หรือไม่?',
			buttons: [
				{
					id: 'ok',
					name: 'ตกลง',
					func:
					function(){
						clearSale();
						closeShowPopupBox();
					}
				},
				{
					id: 'cancel',
					name: 'ยกเลิก',
					func:
					function(){
						closeShowPopupBox();
					}
				}
			]
		});
	});

	// Set default product type
	setCurPrdTypId('frequency');

	// Search product
    $('#searchProduct').keyup(pullProductList);
});

function getWindowStatus() {
	if($('#payBox').length > 0) {
		return "pay";
	} else if($('#edit-quantity-product').length > 0) {
		return "editQty";
	} else if($('.popupBox').length > 0) {
		return "popupBox";
	} else {
		return "addProduct";
	}
}

function setPrdPrmGrp(prdprmgrp_id) {
	$('#prdprmgrp_id').val(prdprmgrp_id);
	if(prdprmgrp_id == '') {
		$('#prdprmgrp_txt').text('ไม่คิดโปรโมชั่น');
	} else {
		$('#prdprmgrp_txt').text(promotionGroups[prdprmgrp_id].name);
	}
}

function setCurPrdTypId(prdtyp_id) {
	$('.prdtypItem').removeClass('selected');
	$('#' + prdtyp_id).addClass('selected');
	curPrdtyp_id = prdtyp_id;
	pullProductList();
}

function hasEqpPrmSale(prd_id) {
	var prdprmgrp_id = $('#prdprmgrp_id').val();
	for(prdID in promotion[prdprmgrp_id]) {
		for(prmType in promotion[prdprmgrp_id][prdID]) {
			if(prdID == prd_id && prmType == 'sale') {
				return true;
			}
		}
	}
	return false;
}

function hasEqpPrmFree(prd_id) {
	var prdprmgrp_id = $('#prdprmgrp_id').val();
	for(prdID in promotion[prdprmgrp_id]) {
		for(prmType in promotion[prdprmgrp_id][prdID]) {
			if(prdID == prd_id && prmType == 'free') {
				return true;
			}
		}
	}
	return false;
}

function hasPrmSaleHTML(prd_id) {
	return $('input[name="prmSale_' + prd_id + '_prmprd_id"]').length > 0;
}
function hasPrmFreeHTML(prd_id) {
	return $('input[name="prmFree_' + prd_id + '_prmprd_id"]').length > 0;
}

function hasProductInOrder() {
	if($('#sale-product-list tr').length > 0) {
 		return true;;
 	} else {
 		showPopupBox({
			title: 'โปรดเลือกรายการสินค้า',
			content: 'โปรดเลือกรายการสินค้าอย่างน้อย 1 รายการ',
			buttons: [
				{
					id: 'ok',
					name: 'ตกลง',
					func:
					function(){
						closeShowPopupBox();
					}
				}
			]
		});
		return false;
 	}
}

function getPrdQty(prd_id) {
	if($('#' + prd_id).find('input[name="qty[]"]').length > 0) {
		return parseInt($('#' + prd_id).find('input[name="qty[]"]').val());
	} else {
		return 0;
	}
}

function getPrmFreeAmount(prd_id) {
	if($('input[name="prmFree_' + prd_id + '_prmprd_id"]').length > 0) {
		return parseInt($('input[name="prmFree_' + prd_id + '_saleprmdtl_amount"]').val());
	} else {
		return 0;
	}
}

function getSaleDiscout() {
	return parseFloat($('#sale-discout-val').val());
}
function setSaleDiscount(discout) {
	var discoutTxt = '';
	if($('#sale-discout-type').val() == 'บาท'){
		discoutTxt = parseFloat(discout).formatMoney(2, '.', ',') + ' บาท';
	} else if($('#sale-discout-type').val() == '%'){
		discoutTxt = parseFloat(discout) + ' %';
	}
	if(discout == 0) {
		$('#sale-discout-txt').text('ไม่มี');
	} else {
		$('#sale-discout-txt').text(discoutTxt);
	}
	$('#sale-discout-val').val(discout);
}

function getTotalPrice() {
	return parseFloat($('#total-price').text().replace(',',''));
}

function pullProductList() {
	var pinHTML = '';
	var tmpProductList = Array();
	if(curPrdtyp_id == 'frequency') {
		tmpProductList = productListFrequency;
	} else {
		tmpProductList = productList;
	}
	for(i in tmpProductList) {
		var displayFlag = false;

		if($('#searchProduct').val() == '') {
			// Display only current product type
			if(tmpProductList[i].prdtyp_id == curPrdtyp_id || curPrdtyp_id == 'frequency') {
				displayFlag = true;
			}
		} else {
			// Search product
			var prdName 	= tmpProductList[i].prd_name.toLowerCase();
			var searchText  = $('#searchProduct').val().toLowerCase();
			if(prdName.indexOf(searchText) > -1) {
				displayFlag = true;
			}
		}

		if(displayFlag) {
			pinHTML += '<div class="pin-container">'
					 + '		<div class="pin" prd-id="' + tmpProductList[i].prd_id + '" prd-price="' + tmpProductList[i].prd_price + '">'
					 + '			<div class="prd-image-container">'
					 + ' 				<div class="prd-image" style="background-image:url(\'../img/products/' + tmpProductList[i].prd_pic + '\');"></div>'
					 + ' 			</div>'
					 + ' 			<div class="prd-name-container">'
					 + '				<p>' + tmpProductList[i].prd_name + '</p>'
					 + ' 			</div>'
					 + ' 		</div>'
					 + '</div>';
		}
	}

	if(pinHTML != '') {
		$('#columns').html(pinHTML);

		// Add event
		$('.pin').click(function() {
			var prdId 		= $(this).attr('prd-id');
			var prdPrice 	= $(this).attr('prd-price');
			var prdName 	= $(this).find('p').text();
			var qty 		= getPrdQty(prdId) + 1;
			prdShelfAmountCover(prdId, qty, function() {
				addSaleDetail({
					prd_id: prdId,
					prd_name: prdName,
					unit_price: prdPrice,
					qty: 1
				});
				$('#barcode-input').focus();
			}, null);
		});
	}
}

function addSaleDetail(data) {
	var html 		= '';
	var prdRow 		= $('#' + data.prd_id);
	var prdName 	= '';
	var prdQty		= 1;
	var unitPrice 	= 0;
	var sumPrice 	= 0;

	if(typeof(data.prd_name) != 'undefined') {
		prdName = data.prd_name;
	}
	if(typeof(data.qty) != 'undefined') {
		prdQty = parseInt(data.qty);
	}
	if(typeof(data.unit_price) != 'undefined') {
		unitPrice = parseFloat(data.unit_price);
	}

	if(prdRow.length > 0) {
		// Add to old product row
		plusOrMinusQty(data.prd_id, data.qty, 'plus');
	} else {
		// First product row
		html 	= '<tr id="' + data.prd_id + '">'
				+ '		<td class="prdName-col">' + prdName + '</td>'
				+ '		<td class="qty-col">'
				+ ' 		<button type="button" class="minusAmount qty-circle-btn" style="position: absolute;left: 0;top: 1px;" '
				+ '			onclick="plusOrMinusQty(\'' + data.prd_id + '\',1,\'minus\')">'
				+ '				<i class="fa fa-minus"></i>'
				+ '			</button>'
				+ '			<span class="prd_qty">' + prdQty + '</span>'
				+ ' 		<button type="button" class="plusAmount qty-circle-btn" style="position: absolute;right: 0;top: 1px;">'
				+ '				<i class="fa fa-plus"></i>'
				+ '			</button>'
				+ '		</td>'
				+ '		<td class="unitPrice-col">' + unitPrice.formatMoney(2, '.', ',') + '</td>'
				+ '		<td class="sumDiscout-col">0.00</td>'
				+ '		<td class="sumPrice-col">' + unitPrice.formatMoney(2, '.', ',') + '</td>'
				+ '		<input type="hidden" name="prd_id[]" value="' + data.prd_id + '">'
				+ '		<input type="hidden" name="qty[]" value="' + prdQty + '">'
				+ '		<input type="hidden" name="prdtyp_id[]" value="' + productList[data.prd_id].prdtyp_id + '">'
				+ '		<input type="hidden" name="sumPrice[]" value="' + unitPrice + '">'
				+ '		<input type="hidden" name="sumDiscout[]" value="0">'
				+ '		<input type="hidden" name="sumPriceReal[]" value="' + unitPrice + '">'
				+ '</tr>';
		$('#sale-product-list tbody').append(html);

		// cal total price
		addPrmSale(data.prd_id);
		calSummary();
		updateEqpPrm(prd_id);

		// add event
		$('#' + data.prd_id).click(function(){
			$(this).siblings('tr').removeClass('selected');
			if($(this).hasClass('selected')) {
				// Close edit qty box
				closeEditQtyBox();
			} else {
				// Open edit qty box
				var qty = parseInt($(this).find('.prd_qty').text().replace(',',''));
				$(this).addClass('selected');
				openEditQtyBox(data.prd_id, qty);
			}
		});
		$('#' + data.prd_id).find('.qty-circle-btn').click(function(e) {
			e.stopPropagation();
		});
		$('#' + data.prd_id).find('.plusAmount').click(function() {
			prdShelfAmountCover(data.prd_id, getPrdQty(data.prd_id) + 1, function() {
				plusOrMinusQty(data.prd_id, 1, 'plus');
			}, null);
		});
	}

	// animate scroll bar
	var container 	= $('#sale-product-list-body')
    var scrollTo 	= $('#' + data.prd_id);
    container.animate({
	    scrollTop: scrollTo.offset().top - container.offset().top + container.scrollTop()
	}, 'fast');
	// hilight that row
	$('#' + data.prd_id).addClass('hilight');
	setTimeout(function(){
		$('#' + data.prd_id).removeClass('hilight');
	}, 1000);
	
}

function removeSaleDetail(prd_id) {
	//remove product
	$('#' + prd_id).remove();
	if($('#sale-product-list tr').length > 0) {
		closeEditQtyBox();
		// cal total price
		calSummary();
	} else {
		clearSale();
	}
}

function addPrmSale(prd_id) {
	// Skip
	if(promotion == '') {
		return;
	}

	var prdprmgrp_id = $('#prdprmgrp_id').val();
	for(prdID in promotion[prdprmgrp_id]) {
		for(prmType in promotion[prdprmgrp_id][prdID]) {
			if(prdID == prd_id && prmType == 'sale') {
				var prm 		= promotion[prdprmgrp_id][prdID][prmType];

				if($('input[name="prmSale_' + prd_id + '_prmprd_id"]').length > 0) {
					// plus amount
					plusPrmSaleAmount(prd_id);
				} else {
					// add first amount
					var unitPrice 	= productList[prd_id].prd_price;
					var prmHTML 	= '<input type="hidden" class="prmSale_' + prd_id + '" name="prmSale_' + prd_id + '_prmprd_id" value="' + prm.prmprd_id + '">'
									+ '<input type="hidden" class="prmSale_' + prd_id + '" name="prmSale_' + prd_id + '_prdprm_name" value="' + prm.prdprm_name + '">'
									+ '<input type="hidden" class="prmSale_' + prd_id + '" name="prmSale_' + prd_id + '_saleprmdtl_amount" value="1">'
									+ '<input type="hidden" class="prmSale_' + prd_id + '" name="prmSale_' + prd_id + '_saleprmdtl_discout" value="">'
									+ '<input type="hidden" class="prmSale_' + prd_id + '" name="prmSale_' + prd_id + '_discout" value="' + prm.prmprd_discout + '">'
									+ '<input type="hidden" class="prmSale_' + prd_id + '" name="prmSale_' + prd_id + '_discout_type" value="' + prm.prmprd_discout_type + '">';
					$('#' + prd_id).append(prmHTML);
				}
				return;
			}
		}
	}
}

function addPrmFree(prd_id) {
	// Skip
	if(promotion == '') {
		return;
	}

	var prdprmgrp_id = $('#prdprmgrp_id').val();
	for(prdID in promotion[prdprmgrp_id]) {
		for(prmType in promotion[prdprmgrp_id][prdID]) {
			if(prdID == prd_id && prmType == 'free') {
				var prm 		= promotion[prdprmgrp_id][prdID][prmType];

				if($('input[name="prmFree_' + prd_id + '_prmprd_id"]').length > 0) {
					// plus amount
					plusPrmFreeAmount(prd_id);
				} else {
					// add first amount
					var unitPrice 	= productList[prd_id].prd_price;
					var prmHTML 	= '<input type="hidden" class="prmFree_' + prd_id + '" name="prmFree_' + prd_id + '_prmprd_id" value="' + prm.prmprd_id + '">'
									+ '<input type="hidden" class="prmFree_' + prd_id + '" name="prmFree_' + prd_id + '_prdprm_name" value="' + prm.prdprm_name + '">'
									+ '<input type="hidden" class="prmFree_' + prd_id + '" name="prmFree_' + prd_id + '_saleprmdtl_amount" value="1">'
									+ '<input type="hidden" class="prmFree_' + prd_id + '" name="prmFree_' + prd_id + '_saleprmdtl_discout" value="' + unitPrice + '">'
					$('#' + prd_id).append(prmHTML);
					minusPrmSaleAmount(prd_id);
				}
				return;
			}
		}
	}
}

function plusPrmSaleAmount(prd_id) {
	var amount = parseInt($('input[name="prmSale_' + prd_id + '_saleprmdtl_amount"]').val())+1;
	$('input[name="prmSale_' + prd_id + '_saleprmdtl_amount"]').val(amount);
}

function minusPrmSaleAmount(prd_id) {
	var amount = parseInt($('input[name="prmSale_' + prd_id + '_saleprmdtl_amount"]').val())-1;
	if(amount > 0) {
		$('input[name="prmSale_' + prd_id + '_saleprmdtl_amount"]').val(amount);
	} else {
		$('.prmSale_' + prd_id).remove();
		$('.eqp-prm-sale-list').remove();
	}
}

function plusPrmFreeAmount(prd_id) {
	var amount = parseInt($('input[name="prmFree_' + prd_id + '_saleprmdtl_amount"]').val())+1;
	$('input[name="prmFree_' + prd_id + '_saleprmdtl_amount"]').val(amount);
	minusPrmSaleAmount(prd_id);
}

function minusPrmFreeAmount(prd_id) {
	var amount = parseInt($('input[name="prmFree_' + prd_id + '_saleprmdtl_amount"]').val())-1;
	if(amount > 0) {
		$('input[name="prmFree_' + prd_id + '_saleprmdtl_amount"]').val(amount);
	} else {
		$('.prmFree_' + prd_id).remove();
		$('.eqp-prm-free-list').remove();
	}
	if(hasEqpPrmSale(prd_id)) {
		if(getPrdQty(prd_id) > amount) {
			addPrmSale(prd_id);
		}
	}
}

function calSummaryPrm(prd_id) {
	var sumDiscoutSale 	= 0;
	var sumDiscoutFree 	= 0;
	var totalDiscout 	= 0;
	var amount 			= 0;
	var unitPrice 		= productList[prd_id].prd_price;

	if($('input[name="prmSale_' + prd_id + '_prmprd_id"]').length > 0) {
		var discout 	= parseFloat($('input[name="prmSale_' + prd_id + '_discout"]').val());
		var discoutType = $('input[name="prmSale_' + prd_id + '_discout_type"]').val();
		amount 			= parseInt($('input[name="prmSale_' + prd_id + '_saleprmdtl_amount"]').val());

		if(discoutType == '%') {
			discout = parseFloat(unitPrice * discout / 100);
		}

		sumDiscoutSale = parseFloat(discout * amount);
		$('input[name="prmSale_' + prd_id + '_saleprmdtl_discout"]').val(sumDiscoutSale);
	}

	if($('input[name="prmFree_' + prd_id + '_prmprd_id"]').length > 0) {
		amount 			= parseInt($('input[name="prmFree_' + prd_id + '_saleprmdtl_amount"]').val());
		sumDiscoutFree 	= parseFloat(unitPrice * amount);
		$('input[name="prmFree_' + prd_id + '_saleprmdtl_discout"]').val(sumDiscoutFree);
	}

	totalDiscout = sumDiscoutSale + sumDiscoutFree;
	$('#' + prd_id).find('input[name="sumDiscout[]"]').val(totalDiscout);
	$('#' + prd_id).find('.sumDiscout-col').text(totalDiscout.formatMoney(2, '.', ','));
}

function calSummary() {
	var totalPrice 		= 0;
	var sumPrice   		= 0;
	var sumDiscout 		= 0;
	var sumPriceReal 	= 0;
	var unitPrice 		= 0;
	var qty 			= 0;
	var discout 		= 0;
	var totalQty 		= 0;
	var totalProduct 	= $('#sale-product-list tbody tr').length;
	var saleDiscout 	= getSaleDiscout();
	var saleDiscoutPrm 	= 0;
	var totalPrmDiscout = 0;

	$('#sale-product-list tbody tr').each(function() {
		prd_id 		 	= $(this).attr('id');
		unitPrice 	 	= parseFloat($(this).find('.unitPrice-col').text().replace(',',''));
		qty 	 	 	= parseInt($(this).find('.prd_qty').text().replace(',',''));
		sumPrice 	 	= parseFloat(unitPrice * qty);
		calSummaryPrm(prd_id);
		sumDiscout 	 	= parseFloat($(this).find('input[name="sumDiscout[]"]').val());
		totalPrmDiscout += sumDiscout;
		sumPriceReal 	= sumPrice - sumDiscout;

		totalPrice 		+= sumPriceReal;
		totalQty   		+= qty;

		$(this).find('input[name="sumPriceReal[]"]').val(sumPriceReal);
		$(this).find('.sumPrice-col').text(sumPriceReal.formatMoney(2, '.', ','));
	});

	// Sale discount manual
	if($('#sale-discout-type').val() == '%') {
		saleDiscout = parseFloat(totalPrice * saleDiscout / 100);
	}
	$('#saleprmdsdtl_discout_manual').val(saleDiscout);

	// Sale discout promotion
	if(typeof(promotionSaleGroup) != 'undefined') {
		var prdprmgrp_id = $('#prdprmgrp_id').val();
		if(typeof(promotionSaleGroup[prdprmgrp_id]) != 'undefined') {
			var prmdsSumPurchase = Array();
			$('input[name="prdtyp_id[]"]').each(function() {
				var prdtyp_id = $(this).val();
				if(typeof(promotionSaleGroup[prdprmgrp_id][prdtyp_id]) != 'undefined') {
					var prmds_id = promotionSaleGroup[prdprmgrp_id][prdtyp_id];
					if(typeof(prmdsSumPurchase[prmds_id]) == 'undefined') {
						prmdsSumPurchase[prmds_id] = 0;
					}
					prmdsSumPurchase[prmds_id] += parseFloat($(this).siblings('input[name="sumPriceReal[]"]').val());
				}
			});
			for(i in prmdsSumPurchase) {
				if(prmdsSumPurchase[i] >= promotionSale[i].prmds_purchase) {
					var prmds_discout =  promotionSale[i].prmds_discout;
					if(promotionSale[i].prmds_discout_type == '%') {
						prmds_discout = parseFloat(totalPrice * prmds_discout / 100);
					}
					if($('.prmds_' + i).length == 0) {
						// Add
						var prmdsHTML 	= '<input class="prmds_' + i + '" type="hidden" name="prmds_id[]" value="' + i + '">'
										+ '<input class="prmds_' + i + '" type="hidden" name="saleprmdsdtl_discout[]" value="' + prmds_discout + '">';
						$('.prmds-col').append(prmdsHTML);
					} else {
						// Update
						$('.prmds_' + i + '[name="saleprmdsdtl_discout[]"]').val(prmds_discout);
					}
				} else {
					// Remove
					$('.prmds_' + i).remove();
				}
			}
		}
	}
	$('.prmds-col input[name="saleprmdsdtl_discout[]"]').each(function() {
		saleDiscoutPrm += parseFloat($(this).val());
	});
	$('#sale-discout-promotion-txt').text(saleDiscoutPrm.formatMoney(2, '.', ',') + ' บาท');


	$('#total-promotion-discout-txt').text(totalPrmDiscout.formatMoney(2, '.', ',') + ' บาท');
	totalPrice -= saleDiscout + saleDiscoutPrm;

	$('#total-price').text(totalPrice.formatMoney(2, '.', ','));
	$('#total-product').text(totalProduct.formatMoney(0, '.', ','));
	$('#total-qty').text(totalQty.formatMoney(0, '.', ','));
	$('input[name="total-price"]').val(totalPrice);
}

function plusOrMinusQty(prd_id, qty, action) {
	var prdRow 		= $('#' + prd_id);
	var prdQty		= 1;
	var unitPrice 	= 0;
	var sumPrice 	= 0;

	prdQty 		= parseFloat(prdRow.find('.prd_qty').text().replace(',',''));
	unitPrice 	= parseFloat(prdRow.find('.unitPrice-col').text().replace(',',''));
	if(action == 'plus') {
		// plus
		prdQty 		= prdQty + qty;
	} else {
		// minus
		if(prdQty <= 1) {
			removeSaleDetail(prd_id);
			return;
		}
		prdQty 		= prdQty - qty;
	}
	sumPrice 	= parseFloat(prdQty * unitPrice);

	// Update product row
	prdRow.find('.prd_qty').text(prdQty.formatMoney(0, '.', ','));
	prdRow.find('.sumPrice-col').text(sumPrice.formatMoney(2, '.', ','));
	prdRow.find('input[name="qty[]"]').val(prdQty);
	prdRow.find('input[name="sumPrice[]"]').val(sumPrice);

	// Update edit qty prduct box
	$('#eqp-qty').val(prdQty);
	
	// Update promotion
	if(action == 'plus') {
		if(prdQty - getPrmFreeAmount(prd_id) > 0) {
			for(i=1; i<=qty; i++) {
				addPrmSale(prd_id);
			}
		}
	} else {
		for(i=1; i<=qty; i++) {
			if(hasPrmSaleHTML(prd_id)) {
				minusPrmSaleAmount(prd_id);
			} else if(hasPrmFreeHTML(prd_id) && getPrmFreeAmount(prd_id) > prdQty){
				minusPrmFreeAmount(prd_id);
			}
		}
		
	}
	
	// cal total price
	calSummary();
	updateEqpPrm(prd_id);
}

function openEditQtyBox(prd_id, qty) {
	if($('#edit-quantity-product').length <= 0) {
		var editQtyBoxHtml 	= '<div id="edit-quantity-product">'
							+ '		<div id="edit-quantity-product-inner">'
							+ '			<div id="edit-quantity-product-header">'
							+ '				<button id="closeEqpBoxBtn" type="button" class="pos-btn arrowBtn white"><i class="fa fa-times"></i></button>'
							+ '			</div>'
							+ ' 		<div id="edit-quantity-product-body"></div>'
							+ '		</div>'
							+ '</div>';
		$('body').prepend(editQtyBoxHtml);
		// add event
		$('#closeEqpBoxBtn').click(closeEditQtyBox);
	}

	// Get product data
	var prdData 	= productList[prd_id];
	var eqpBoxHTML  = '<table class="produt-data">'
					+ '		<tbody>'
					+ ' 		<tr>'
					+ '				<td>'
					+ '					<img src="../img/products/' + prdData.prd_pic + '" class="prd_image">'
					+ ' 			</td>'
					+ ' 			<td style="width: 100%;padding-left: 20px;">'
					+ ' 				<h1>' + prdData.prd_name + '</h1>'
					+ ' 				<table>'
					+ '						<tr>'
					+ ' 						<td>ประเภท:</td>'
					+ ' 						<td>' + prdData.prdtyp_name + '</td>'
					+ '						</tr>'
					+ '						<tr>'
					+ ' 						<td>ยี่ห้อ:</td>'
					+ ' 						<td>' + prdData.brand_name + '</td>'
					+ '						</tr>'
					+ '						<tr>'
					+ ' 						<td colspan="2">' + prdData.prd_desc + '</td>'
					+ '						</tr>'
					+ '					</table>'
					+ ' 				<span>ราคา <span id="eqp-unitPrice">' + prdData.prd_price + '</span> บาท</span>'
					+ ' 			</td>'
					+ '			</tr>'
					+ ' 		<tr>'
					+ ' 			<td colspan="2" style="text-align:center;padding-top:20px;">จำนวน</td>'
					+ ' 		</tr>'
					+ '			<tr>'
					+ '				<td colspan="2">'
					+ ' 				<div id="eqp-qty-container">'
					+ '						<button id="eqp-qty-minus-btn" type="button" class="qty-circle-btn minus">'
					+ '							<i class="fa fa-minus"></i>'
					+ ' 					</button>'
					+ ' 					<input id="eqp-qty" type="text" value="' + qty + '" onkeypress="return event.charCode >= 48 && event.charCode <= 57">'
					+ ' 					<button id="eqp-qty-plus-btn" type="button" class="qty-circle-btn plus">'
					+ ' 						<i class="fa fa-plus"></i>'
					+ ' 					</button>'
					+ '					</div>'
					+ ' 			</td>'
					+ '			</tr>'
					+ ' 	</tbody>'
					+ '</table>';

	// Gen button
	eqpBoxHTML += '<div class="eqp-control">';
	if(hasEqpPrmFree(prd_id)) {
		eqpBoxHTML +=  '<button id="addPrmFreeBtn" type="button" class="pos-btn white">แถมฟรี</button>';
	}
	eqpBoxHTML += '		<button id="removeSlvDtlBtn" type="button" class="pos-btn white">เอาสินค้านี้ออก</button>'
				+ '</div>';

	// Get promotion data
	if($('input[name="prmSale_' + prd_id + '_prmprd_id"]').length >0 
	|| $('input[name="prmSale_' + prd_id + '_prmprd_id"]').length >0) {
		eqpBoxHTML += '<div class="eqp-prm">'
					+ '	<h1>รายการส่วนลด</h1>'
					+ ' 	<div class="eqp-prm-list-container">'
					+ '	</div>'
					+ '</div>';
	}

	$('#edit-quantity-product-body').html(eqpBoxHTML);
	$('#edit-quantity-product-body').css('visibility', 'hidden');

	// add promotion 
	updateEqpPrm(prd_id);

	// add event
	$('#eqp-qty-minus-btn').click(function(){
		plusOrMinusQty(prd_id, 1, 'minus');
	});
	$('#eqp-qty-plus-btn').click(function(){
		prdShelfAmountCover(prd_id, getPrdQty(prd_id) + 1, function() {
			plusOrMinusQty(prd_id, 1, 'plus');
		}, null);
	});
	$('#removeSlvDtlBtn').click(function(){
		removeSaleDetail(prd_id);
	});
	$('#eqp-qty').change(function(){
		var oldQty 	= getPrdQty(prd_id);
		var newQty 	= parseInt($(this).val());

		if($(this).val() != '' && newQty >= 1) {
			prdShelfAmountCover(prd_id, newQty, function() {
				if(newQty > oldQty) {
					var plusAmount = newQty - oldQty;
					plusOrMinusQty(prd_id, plusAmount, 'plus');
				} else if(newQty < oldQty) {
					var minusAmount = oldQty - newQty;
					plusOrMinusQty(prd_id, minusAmount, 'minus');
				}
				calSummary();
				updateEqpPrm(prd_id);
				return;
			},
			function() {
				$('#eqp-qty').val(oldQty);
			});
		}
	});
	$('#addPrmFreeBtn').click(function(){
			openAddPrmFreeBox(prd_id);
	});

	// show when load image success
	var prdImg = $('#edit-quantity-product .prd_image');
	$(prdImg).load(function(){
		if(prdImg.height() > 250) {
			prdImg.css('height', '250px');
		}
		$('#edit-quantity-product-body').css('visibility', 'visible');
		$('#edit-quantity-product-inner').css('margin-left', 0);
	});
}
function closeEditQtyBox() {
	$('#edit-quantity-product-inner').css('margin-left', '100%');
	setTimeout(function() {
		$('#sale-product-list tr').removeClass('selected');
		$('#edit-quantity-product').remove();
		$('#barcode-input').focus();
	}, 200);
}

function updateEqpPrm(prd_id) {
	// Skip if not open eqp box
	if($('#edit-quantity-product').length <= 0) {
		return;
	}

	var hasEqpPrmBox 	= $('.eqp-prm-list-container').length > 0;

	// Create eqp promotion box
	if(!hasEqpPrmBox && (hasPrmSaleHTML(prd_id) || hasPrmFreeHTML(prd_id))) {
		var eqpPrmBoxHTML  	= '<div class="eqp-prm">'
							+ '	<h1>รายการส่วนลด</h1>'
							+ ' <div class="eqp-prm-list-container">'
							+ '	</div>'
							+ '</div>';
		$('#edit-quantity-product-body').append(eqpPrmBoxHTML);
	}

	if(hasPrmSaleHTML(prd_id)) {
		var sale_prmgrp_id 				= $('#prdprmgrp_id').val();
		var sale_prmName 				= $('input[name="prmSale_' + prd_id + '_prdprm_name"]').val();
		var sale_amount 				= parseInt($('input[name="prmSale_' + prd_id + '_saleprmdtl_amount"]').val()).formatMoney(0, '.', ',');
		var sale_saleprmdtl_discout 	= parseFloat($('input[name="prmSale_' + prd_id + '_saleprmdtl_discout"]').val()).formatMoney(2, '.', ',');
		var sale_discout 				= $('input[name="prmSale_' + prd_id + '_discout"]').val();
		var sale_discout_type 			= $('input[name="prmSale_' + prd_id + '_discout_type"]').val();
		var sale_prmPicPath 			= '../img/product_promotions/'+ promotion[sale_prmgrp_id][prd_id]['sale']['prdprm_picture'];

		if($('.eqp-prm-sale-list').length > 0) {
			// update
			$('.eqp-prmSale-prdprm_name').text(sale_prmName);
			$('.eqp-prmSale-saleprmdtl_amount').text(sale_amount);
			$('.eqp-prmSale-saleprmdtl_discount').text(sale_saleprmdtl_discout);
		} else {
			// add
			var discountRate = '';
			if(sale_discout_type == '%') {
				discountRate = sale_discout + '%';
			} else if(sale_discout_type == 'บาท') {
				discountRate = sale_discout + ' บาท';
			}
			var prmSale = '<div class="eqp-prm-sale-list eqp-prm-list">'
						+ '		<div class="prm-thumb" style="background-image:url(\'' + sale_prmPicPath + '\');">'
						+ '		</div>'
						+ '		<table>'
						+ '			<tr>'
						+ '				<td class="prm-name-col">'
						+ '					<span class="eqp-prmSale-prdprm_name prm-name">' + sale_prmName + '</span><br>'
						+ '					<span class="discount-rate">ลดราคา ' + discountRate + '</span>'
						+ '				</td>'
						+ '				<td class="amount-col">'
						+ '					<span class="eqp-prmSale-saleprmdtl_amount">' + sale_amount + '</span>'
						+ '				</td>'
						+ '				<td class="discount-col">'
						+ '					<span class="eqp-prmSale-saleprmdtl_discount">' + sale_saleprmdtl_discout + '</span>'
						+ '				</td>'
						+ '			</tr>'
						+ ' 	</table>'
						+ '</div>';
			$('.eqp-prm-list-container').append(prmSale);
		}
	}

	if(hasPrmFreeHTML(prd_id)) {
		var free_prmgrp_id 	= $('#prdprmgrp_id').val();
		var free_prmName 	= $('input[name="prmFree_' + prd_id + '_prdprm_name"]').val();
		var free_amount 	= parseInt($('input[name="prmFree_' + prd_id + '_saleprmdtl_amount"]').val()).formatMoney(0, '.', ',');
		var free_discout 	= parseFloat($('input[name="prmFree_' + prd_id + '_saleprmdtl_discout"]').val()).formatMoney(2, '.', ',');
		var free_prmPicPath = '../img/product_promotions/'+ promotion[free_prmgrp_id][prd_id]['free']['prdprm_picture'];

		if($('.eqp-prm-free-list').length > 0) {
			// update
			$('.eqp-prmFree-prdprm_name').text(free_prmName);
			$('.eqp-prmFree-saleprmdtl_amount').text(free_amount);
			$('.eqp-prmFree-saleprmdtl_discount').text(free_discout);
		} else {
			// add
			var prmFree = '<div class="eqp-prm-free-list eqp-prm-list">'
						+ '		<div class="prm-thumb" style="background-image:url(\'' + free_prmPicPath + '\');">'
						+ '		</div>'
						+ '		<table>'
						+ '			<tr>'
						+ '				<td class="prm-name-col">'
						+ '					<span class="eqp-prmFree-prdprm_name prm-name">' + free_prmName + '</span><br>'
						+ '					<span class="discount-rate">ฟรี</span>'
						+ '				</td>'
						+ '				<td class="amount-col">'
						+ '					<span class="eqp-prmFree-saleprmdtl_amount">' + free_amount + '</span>'
						+ '				</td>'
						+ '				<td class="discount-col">'
						+ '					<span class="eqp-prmFree-saleprmdtl_discount">' + free_discout + '</span>'
						+ '				</td>'
						+ '			</tr>'
						+ ' 	</table>'
						+ '</div>';
			$('.eqp-prm-list-container').append(prmFree);
		}
	}
}
function removeEqpPrm() {
	$('.eqp-prm').remove();
}

function openPayBox() {
	var  payBoxHTML = '<div id="payBox">'
					+ ' 	<div id="payBox-inner">'
					+ '			<div id="payBox-inner-header">'
					+ '				<h1>ชำระเงิน</h1>'
					+ '			</div>'
					+ ' 		<div id="payBox-inner-body">'
					+ '				<div id="payBox-leftCont">'
					+ '					<table class="payBox-table-pay" cellspacing="0">'
					+ '						<tr class="totalPrice-row">'
					+ '							<td>ราคาสุทธิ</td>'
					+ '							<td>' +  getTotalPrice().formatMoney(2, '.', ',') + '</td>'
					+ '						</tr>'
					+ '						<tr>'
					+ '							<td>รับมา</td>'
					+ '							<td>'
					+ ' 							<input id="payMoney-input" type="text" class="pos-input" onkeypress="return (event.charCode >= 48 && event.charCode <= 57) || event.charCode == 46">'
					+ '							</td>'
					+ '						</tr>'
					+ '						<tr class="changement-row">'
					+ '							<td>ทอน</td>'
					+ '							<td>'
					+ ' 							<span id="changeMoney">0.00</span>'
					+ '							</td>'
					+ '						</tr>'
					+ '					</table>'
					+ '					<button id="closePayBoxBtn" type="button" class="pos-btn white">ยกเลิก</button>'
					+ '					<button id="saveSaleBtn" type="button" class="pos-btn green">บันทึก</button>'
					+ '				</div>'
					+ '				<div id="payBox-rightCont">'
					// + '					<ul class="thaiMoneyLevel">'
					// + '						<li></li>'
					// + '						<li></li>'
					// + '						<li></li>'
					// + '						<li></li>'
					// + '						<li></li>'
					// + '						<li></li>'
					// + '						<li></li>'
					// + '						<li></li>'
					// + '					</ul>'
					+ '				</div>'
					+ '			</div>'
					+ ' 	</div>'
					+ '</div>';
	$('body').prepend(payBoxHTML);
	$('#payBox').css('visibility', 'hidden');

	// Add event
	$('#payMoney-input').change(function() {
		var totalPrice 	= getTotalPrice();
		var payMoney 	= parseFloat($(this).val());

		if(validateMoney($(this).val()) && payMoney >= totalPrice) {
			var change = payMoney - totalPrice;
			$('#changeMoney').text(change.formatMoney(2, '.', ','));
		} else if($(this).val() != '') {
			showPopupBox({
				title: 'จำนวนเงินไม่ถูกต้อง',
				content: 'โปรดกรอกจำนวนเงินที่รับให้ถูกต้อง',
				buttons: [
					{
						id: 'ok',
						name: 'ตกลง',
						func:
						function(){
							$('#payMoney-input').val('');
							$('#payMoney-input').focus();
							closeShowPopupBox();
						}
					}
				]
			});
		}
	});
	$('#saveSaleBtn').click(function() {
		saveSale();
	});
	$('#closePayBoxBtn').click(function() {
		closePayBox();
	});

	// Set position
    $('#payBox-inner').css('margin-top', -Math.abs($('#payBox-inner').outerHeight(true) / 2));
    $('#payBox-inner').css('margin-left', -Math.abs($('#payBox-inner').outerWidth(true) / 2));
    $('#payBox').css('visibility', 'visible');
    $('#payMoney-input').focus();
}
function closePayBox() {
	$('#payBox').remove();
}

function prevPagePrdTyp() {
	var page = parseInt($('.product-category-list').attr('data-page'))-1;
	setPagePrdTyp(page);
	animatePagePrdTyp();
	$('#barcode-input').focus();
}

function nextPagePrdTyp() {
	var page = parseInt($('.product-category-list').attr('data-page'))+1;
	setPagePrdTyp(page);
	animatePagePrdTyp();
	$('#barcode-input').focus();
}

function setPagePrdTyp(page) {
	$('.product-category-list').attr('data-page', page);
}

function animatePagePrdTyp() {
	var pageWidth = $('.product-category-container').width();
	var maxPage   = Math.ceil(listWidth / pageWidth);
	var page 	  = parseInt($('.product-category-list').attr('data-page'));

	if(page > maxPage) {
		setPagePrdTyp(1);
		animatePagePrdTyp();
		return;
	} if(page < 1) {
		setPagePrdTyp(maxPage);
		animatePagePrdTyp();
		return;
	}

	var left = -Math.abs(pageWidth * (page-1));
	$('.product-category-list').css('left', left + 'px');
}


/*
 * Popup box
 */
function showPopupBox(data) {
	var no 		= $('.popupBox').length + 1;
	var id 		= 'popupBox-' + no;
	var html 	= '<div id="' + id + '" class="popupBox">'
				+ '	<div class="popupBox-container">'
				+ '		<div class="popupBox-header">'
				+ '			<div class="popupBox-header-inner">'
				+ '				<h1>' + data.title + '</h1>'
				+ '			</div>'
			 	+ '		</div>'
			 	+ '		<div class="popupBox-body">' + data.content + '</div>'
			 	+ '		<div class="popupBox-footer clearfix">'
			 	+ ' 		<div class="popupBox-footer-left"></div>'
			 	+ ' 		<div class="popupBox-footer-right"></div>'
			 	+ '		</div>'
				+ '	</div>'
			 	+ '</div>';
	$('body').prepend(html);
	$('#' + id).css('visibility', 'hidden');

	// Set width
    if(typeof(data.boxWidth) != 'undefined') {
        $('#' + id + ' .popupBox-container').css('width', data.boxWidth + 'px');
    }

    // Set z-index
    if(no > 1) {
    	var prev_ZIndex = parseInt($('#popupBox-' + (no-1)).css('z-index'));
    	$('#' + id).css('z-index', prev_ZIndex + 1);
    }

    for (i in data.buttons) {
        // Create action button
        var buttonItem = data.buttons[i];
        var popupBoxBtn = '<button id="' + id + '-action-btn-' + buttonItem.id + '" type="button" class="pos-btn white">'
                      	+ buttonItem.name
                      	+ '</button>';
        // Append button
        if(buttonItem.position == 'left') {
        	$('#' + id + ' .popupBox-footer-left').append(popupBoxBtn);
        } else if(buttonItem.position == 'right') {
        	$('#' + id + ' .popupBox-footer-right').append(popupBoxBtn);
        } else {
        	$('#' + id + ' .popupBox-footer').append(popupBoxBtn);
        }

        // Add event when click button
        $('#' + id + '-action-btn-' + buttonItem.id).click(buttonItem.func);
    }

    // Set position
   setPosPopupBox(id);
    
    // Display
    $('#' + id).css('visibility', 'visible');

    // Call back funtion when success
    if(typeof(data.success) == 'function') {
    	data.success();
    }
}

function closeShowPopupBox() {
	var last = $('.popupBox').length;
	$('#popupBox-' + last).remove();
	if(last <= 1) {
		$('#barcode-input').focus();
	}
}

function setPosPopupBox(id) {
    $('#' + id + ' .popupBox-container').css('margin-top', -Math.abs($('#' + id + ' .popupBox-container').outerHeight() / 2));
    $('#' + id + ' .popupBox-container').css('margin-left', -Math.abs($('#' + id + ' .popupBox-container').outerWidth() / 2));
}

function openAddPrmFreeBox(prd_id) {
	var amount  = parseInt(getPrmFreeAmount(prd_id));
	var maxQty 	= parseInt($('#' + prd_id).find('input[name="qty[]"]').val());
	var content = '<button id="prmFreeMinusBtn" type="button" class="qty-circle-btn"><i class="fa fa-minus"></i></button>'
				+ '<input id="prmFreeAmount" type="text" class="pos-input" value="' + amount + '" onkeypress="return event.charCode >= 48 && event.charCode <= 57">'
				+ '<button id="prmFreePlusBtn" type="button" class="qty-circle-btn"><i class="fa fa-plus"></i></button>';
	showPopupBox({
		title: 'กรอกจำนวนที่แถมฟรี',
		content: content,
		buttons: [
			{
				id: 'ok',
				name: 'ตกลง',
				func:
				function(){
					editPrmFreeAmount();
					closeShowPopupBox();
				}
			},
			{
				id: 'clear',
				name: 'ลบทั้งหมด',
				func:
				function(){
					$('#prmFreeAmount').val(0);
					editPrmFreeAmount();
					closeShowPopupBox();
				}
			},
			{
				id: 'cancel',
				name: 'ยกเลิก',
				func:
				function(){
					closeShowPopupBox();
				}
			}
		],
		success:
		function() {
			$('#prmFreeMinusBtn').click(function() {
				var newAmount = parseInt($('#prmFreeAmount').val()) - 1;
				if(newAmount >= 0) {
					$('#prmFreeAmount').val(newAmount);
				}
			});
			$('#prmFreePlusBtn').click(function() {
				var newAmount = parseInt($('#prmFreeAmount').val()) + 1;
				if(newAmount <= maxQty) {
					$('#prmFreeAmount').val(newAmount);
				}
			});
			$('#prmFreeAmount').change(function() {
				if(parseInt($(this).val()) > getPrdQty(prd_id)) {
					showPopupBox({
						title: 'จำนวนไม่ถูกต้อง',
						content: 'จำนวนแถมฟรีเกินจำนวนที่ขาย กรุณกรอกใหม่',
						buttons: [
							{
								id: 'ok',
								name: 'ตกลง',
								func:
								function(){
									$('#prmFreeAmount').val(getPrmFreeAmount(prd_id));
									$('#prmFreeAmount').focus();
									closeShowPopupBox();
								}
							}
						]
					});
				}
			});
		}
	});

	function editPrmFreeAmount() {
		var editedAmount = parseInt($('#prmFreeAmount').val());
		if(editedAmount > amount) {
			var addAmount = editedAmount - amount;
			for(i=1; i<=addAmount; i++) {
				addPrmFree(prd_id);
			}
			calSummary();
			updateEqpPrm(prd_id);
		} else if(editedAmount < amount) {
			var removeAmount = amount - editedAmount;
			for(i=1; i<=removeAmount; i++) {
				minusPrmFreeAmount(prd_id);
			}
			if(editedAmount == 0 && !hasPrmSaleHTML(prd_id)) {
				removeEqpPrm();
			}
			calSummary();
			updateEqpPrm(prd_id);
		}
	}
}

/*
 * Sale transaction control
 */
 function openSaleDiscount() {
 	// Detect product list
 	if(!hasProductInOrder()) {
 		return;
 	}

 	var saleDiscout  = getSaleDiscout();
	var totalPrice 	 = getTotalPrice();
	var content = '<input id="saleDiscoutInput" type="text" class="pos-input" value="' + saleDiscout + '" onkeypress="return (event.charCode >= 48 && event.charCode <= 57) || event.charCode == 46">'
				+ '<br><br><label><input id="saleDiscoutTypeInputBath" type="radio" name="saleDiscoutTypeInput" value="บาท" checked> บาท</label> &nbsp;'
				+ '<label><input id="saleDiscoutTypeInputPercent" type="radio" name="saleDiscoutTypeInput" value="%"> เปอร์เซ็น</label><br><br>';
 	showPopupBox({
		title: 'ส่วนลดการขาย',
		content: content,
		buttons: [
			{
				id: 'ok',
				name: 'ตกลง',
				func:
				function(){
					setSaleDiscoutInput();
					closeShowPopupBox();
				}
			},
			{
				id: 'clear',
				name: 'ไม่มีส่วนลด',
				func:
				function(){
					$('#saleDiscoutInput').val(0);
					setSaleDiscoutInput();
					closeShowPopupBox();
				}
			},
			{
				id: 'cancel',
				name: 'ยกเลิก',
				func:
				function(){
					closeShowPopupBox();
				}
			}
		],
		success:
		function() {
			// Set default sale discout typea
			if($('#sale-discout-type').val() == 'บาท') {
				$('#saleDiscoutTypeInputBath').prop('checked', true);
			} else if($('#sale-discout-type').val() == '%') {
				$('#saleDiscoutTypeInputPercent').prop('checked', true);
			}
			$('#saleDiscoutInput').change(validateSaleDiscout);
			$('input[name="saleDiscoutTypeInput"]').change(validateSaleDiscout);
		}
	});

	function setSaleDiscoutInput() {
		$('#sale-discout-type').val($('input[name="saleDiscoutTypeInput"]:checked').val());
		setSaleDiscount(parseFloat($('#saleDiscoutInput').val()));
		calSummary();
	}

	function validateSaleDiscout() {
		var editedDiscount = parseFloat($('#saleDiscoutInput').val());

		if($('input[name="saleDiscoutTypeInput"]:checked').val() == 'บาท') {
			var realTotalPrice = totalPrice + saleDiscout;
			if(editedDiscount > realTotalPrice) {
				showPopupBox({
					title: 'ส่วนลดไม่ถูกต้อง',
					content: 'จำนวนส่วนลดมากกว่าราคาสุทธิ กรุณกรอกใหม่',
					buttons: [
						{
							id: 'ok',
							name: 'ตกลง',
							func:
							function(){
								$('#saleDiscoutInput').val(saleDiscout);
								$('#saleDiscoutInput').focus();
								closeShowPopupBox();
							}
						}
					]
				});
			}
		} else if ($('input[name="saleDiscoutTypeInput"]:checked').val() == '%') {
			if(editedDiscount > 100) {
				showPopupBox({
					title: 'ส่วนลดไม่ถูกต้อง',
					content: 'ไม่สามารถกำหนดส่วนลดมากกว่า 100% ได้ กรุณกรอกใหม่',
					buttons: [
						{
							id: 'ok',
							name: 'ตกลง',
							func:
							function(){
								if(saleDiscout > 100) {
									$('#saleDiscoutInput').val(0);
								} else {
									$('#saleDiscoutInput').val(saleDiscout);
								}
								$('#saleDiscoutInput').focus();
								closeShowPopupBox();
							}
						}
					]
				});
			}
		}
	}
 }

/*
 * Save sale
 */
function saveSale() {
	var change = parseFloat($('#changeMoney').text().replace(',',''));
	if($('#payMoney-input').val() != '' && change >= 0 && $('.popupBox').length <= 0) {
		showPopupBox({
			title: 'บันทึกการขาย',
			content: 'คุณต้องการบันทึกการขายใช่หรือไม่?',
			buttons: [
				{
					id: 'cancel',
					name: 'ยกเลิก',
					func:
					function(){
						closeShowPopupBox();
					}
				},
				{
					id: 'ok',
					name: 'ตกลง',
					func:
					function(){
						$.ajax({
							url: '../common/ajaxPOSManageSale.php',
							type: 'POST',
							data: {
								formData		: $('#formSale').serialize(),
								formPrmds 		: $('#formPrmds').serialize()
							},
							success:
							function(responseJSON) {
								var response = $.parseJSON(responseJSON);
								if(response.status == 'PASS') {
									// Print Receipt
									var receiptHref = 'printReceipt.php?sale_id=' + response.sale_id
													+ '&cash=' + $('#payMoney-input').val();
									window.open(receiptHref, '_blank');

									closePayBox();
									showPopupBox({
										title: 'บันทึกเรียบร้อย',
										content: 'บันทึกการขายเก็บลงฐานข้อมูลเรียบร้อย',
										buttons: [
											{
												id: 'ok',
												name: 'การขายถัดไป',
												func:
												function(){
													clearSale();
													closeShowPopupBox();
												}
											}
										]
									});
								} else if(response.status == 'FAIL') {
									showPopupBox({
										title: 'เกิดข้อผิดพลาด',
										content: 'เกิดข้อผิดพลาดในการบันทึกข้อมูลลงฐานข้อมูล<br><br>'+response.errorTxt,
										buttons: [
											{
												id: 'ok',
												name: 'ตกลง',
												func:
												function(){
													closeShowPopupBox();
												}
											}
										],
										boxWidth: 800
									});
								} else {
									alert(response.status + response.error);
								}
							}
						});
						closeShowPopupBox();
					}
				}
			]
		});
	}
}

function clearSale() {
	$('#sale-product-list tr').remove();
	closeEditQtyBox();
	$('#sale-discout-txt').text('ไม่มี');
	$('#sale-discout-val').val(0);
	$('.prmds-col input[name="prmds_id[]"]').remove();
	$('.prmds-col input[name="saleprmdsdtl_discout[]"]').remove();
	calSummary();
}

function openPrdprmgrpBox() {
	var curPrdprmGrpId 	= $('#prdprmgrp_id').val();

	showPopupBox({
		title: 'ตั้งค่ากลุ่มโปรโมชั่น',
		content: '<ul id="prdprmgrpRadio" class="pos-radio"></ul>',
		buttons: [
			{
				id: 'ok',
				name: 'ตกลง',
				func:
				function(){
					var checked = $('#prdprmgrpRadio li.checked').attr('data-value');
					if(curPrdprmGrpId != checked) {
						showPopupBox({
							title: 'เปลียนกลุ่มโปรโมชั่น',
							content: 'ข้อมูลการขายจะถูกล้าง คุณต้องการเปลี่ยนกลุ่มโปรโมชั่นใช่หรือไม่?',
							buttons: [
								{
									id: 'ok',
									name: 'ตกลง',
									func:
									function(){
										setPrdPrmGrp(checked);
										clearSale();
										closeShowPopupBox(); 
										closeShowPopupBox(); // Close popupBox parent
									}
								},
								{
									id: 'cancel',
									name: 'ยกเลิก',
									func:
									function(){
										closeShowPopupBox();
									}
								}
							],
							boxWidth: 400
						});
					} else {
						closeShowPopupBox();
					}
				}
			},
			{
				id: 'cancel',
				name: 'ยกเลิก',
				func:
				function(){
					closeShowPopupBox();
				}
			}
		],
		boxWidth: 500,
		success:
		function() {
			var prdprmgrpRadios = Array({
				val: '',
				name: 'ไม่คิดโปรโมชั่น'
			});
			for(i in promotionGroups) {
				prdprmgrpRadios.push({
					val: promotionGroups[i].id,
					name: promotionGroups[i].name
				});
			}
			posRadio({
				elem 		: $('#prdprmgrpRadio'),
				radios 		: prdprmgrpRadios,
				valChecked 	: curPrdprmGrpId,
				success 	:
				function() {
					setPosPopupBox('popupBox-1');
				}
			});
		}
	});
}

function prdShelfAmountCover(prd_id, qty, onEnough, onNotEnough) {
	$.ajax({
		url: '../common/ajaxPOSCheckPrdShelfAmountCover.php',
		type: 'POST',
		data: {
			prd_id: prd_id,
			qty: qty
		},
		success:
		function(response) {
			if(response == 'ENOUGH') {
				if(typeof(onEnough) == 'function') {
					onEnough();
				}
			} else if(response == 'NOT_ENOUGH') {
				showPopupBox({
					title: 'สินค้าไม่พอ',
					content: 'จำนวนสินค้าไม่เพียงพอต่อการขาย',
					buttons: [
						{
							id: 'ok',
							name: 'ตกลง',
							func:
							function(){
								if(typeof(onNotEnough) == 'function') {
									onNotEnough();
								}
								closeShowPopupBox();
							}
						}
					],
					boxWidth: 300
				});
			} else if(response == 'FAIL') {
				alert('ไม่พบสินค้ารหัสนี้');
			} else {
				alert(response);
			}
		}
	});
}