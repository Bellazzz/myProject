$(document).ready(function() {
	pullProductList();

	$(document).keyup(function(e) {
		var code = (e.keyCode ? e.keyCode : e.which);
		var stat = getWindowStatus();

		if(stat == 'pay') {
			if(code == 27) {
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
		  	}
		} else {
			if (code == 13) { // ENTER
				if($('#sale-product-list tr').length > 0) {
					openPayBox();
				}
			}
		}
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
	})
});

function getWindowStatus() {
	if($('#payBox').length > 0) {
		return "pay";
	} else if($('#edit-quantity-product').length > 0) {
		return "editQty";
	} else {
		return "addProduct";
	}
}

function pullProductList() {
	var pinHTML = '';
	for(i in productList) {
		pinHTML += '<div class="pin-container">'
				 + '		<div class="pin" prd-id="' + productList[i].prd_id + '" prd-price="' + productList[i].prd_price + '">'
				 + '			<div class="prd-image-container">'
				 + ' 				<div class="prd-image" style="background-image:url(\'../img/products/' + productList[i].prd_pic + '\');"></div>'
				 + ' 			</div>'
				 + ' 			<div class="prd-name-container">'
				 + '				<p>' + productList[i].prd_name + '</p>'
				 + ' 			</div>'
				 + ' 		</div>'
				 + '</div>';
	}

	if(pinHTML != '') {
		$('#columns').html(pinHTML);

		// Add event
		$('.pin').click(function() {
			var prdId 		= $(this).attr('prd-id');
			var prdPrice 	= $(this).attr('prd-price');
			var prdName 	= $(this).find('p').text();
			addSaleDetail({
				prd_id: prdId,
				prd_name: prdName,
				unit_price: prdPrice,
				qty: 1
			});
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
				+ ' 		<button type="button" class="qty-circle-btn" style="position: absolute;left: 0;top: 1px;" '
				+ '			onclick="plusOrMinusQty(\'' + data.prd_id + '\',1,\'minus\')">'
				+ '				<i class="fa fa-minus"></i>'
				+ '			</button>'
				+ '			<span class="prd_qty">' + prdQty + '</span>'
				+ ' 		<button type="button" class="qty-circle-btn" style="position: absolute;right: 0;top: 1px;" '
				+ '			onclick="plusOrMinusQty(\'' + data.prd_id + '\',1,\'plus\')">'
				+ '				<i class="fa fa-plus"></i>'
				+ '			</button>'
				+ '		</td>'
				+ '		<td class="unitPrice-col">฿' + unitPrice.formatMoney(2, '.', ',') + '</td>'
				+ '		<td class="sumPrice-col">฿' + unitPrice.formatMoney(2, '.', ',') + '</td>'
				+ '		<input type="hidden" name="prd_id[]" value="' + data.prd_id + '">'
				+ '		<input type="hidden" name="qty[]" value="' + prdQty + '">'
				+ '		<input type="hidden" name="sumPrice[]" value="' + unitPrice + '">'
				+ '</tr>';
		$('#sale-product-list tbody').append(html);
		// cal total price
		calSummary();
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
	}, 1000)
	
}

function removeSaleDetail(prd_id) {
	//remove product
	$('#' + prd_id).remove();
	closeEditQtyBox();
	// cal total price
	calSummary();
}

function calSummary() {
	var totalPrice 		= 0;
	var sumPrice   		= 0;
	var unitPrice 		= 0;
	var qty 			= 0;
	var totalQty 		= 0;
	var totalProduct 	= $('#sale-product-list tbody tr').length;

	$('#sale-product-list tbody tr').each(function() {
		unitPrice = parseFloat($(this).find('.unitPrice-col').text().replace('฿','').replace(',',''));
		qty 	 = parseInt($(this).find('.prd_qty').text().replace(',',''));
		sumPrice = parseFloat(unitPrice * qty);
		totalPrice += sumPrice;
		totalQty   += qty;

		$(this).find('.sumPrice-col').text('฿' + sumPrice.formatMoney(2, '.', ','));
	});

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
	unitPrice 	= parseFloat(prdRow.find('.unitPrice-col').text().replace('฿', '').replace(',',''));
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
	prdRow.find('.sumPrice-col').text('฿' + sumPrice.formatMoney(2, '.', ','));
	prdRow.find('input[name="qty[]"]').val(prdQty);
	prdRow.find('input[name="sumPrice[]"]').val(sumPrice);

	// Update edit qty prduct box
	$('#eqp-qty').val(prdQty);
	
	// cal total price
	calSummary();
}

function openEditQtyBox(prd_id, qty) {
	if($('#edit-quantity-product').length <= 0) {
		var editQtyBoxHtml 	= '<div id="edit-quantity-product">'
							+ '		<div id="edit-quantity-product-inner">'
							+ '			<div id="edit-quantity-product-header">'
							+ '				<button id="closeEqpBoxBtn" type="button" class="pos-btn arrowBtn white">ปิด</button>'
							+ '			</div>'
							+ ' 		<div id="edit-quantity-product-body"></div>'
							+ '		</div>'
							+ '</div>';
		$('body').prepend(editQtyBoxHtml);
		// add event
		$('#closeEqpBoxBtn').click(closeEditQtyBox);
	}

	// Get product data
	var prdData = Array();
	for(i in productList) {
		if(productList[i].prd_id == prd_id) {
			prdData = productList[i];
			break;
		}
	}

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
					+ '</table>'
					+ '<div id="eqp-control">'
					+ '		<button id="removeSlvDtlBtn" type="button" class="pos-btn white">เอาสินค้านี้ออก</button>'
					+ '</div>';

	$('#edit-quantity-product-body').html(eqpBoxHTML);
	$('#edit-quantity-product-body').css('visibility', 'hidden');

	// add event
	$('#eqp-qty-minus-btn').click(function(){
		plusOrMinusQty(prd_id, 1, 'minus');
	});
	$('#eqp-qty-plus-btn').click(function(){
		plusOrMinusQty(prd_id, 1, 'plus');
	});
	$('#removeSlvDtlBtn').click(function(){
		removeSaleDetail(prd_id);
	});
	$('#eqp-qty').change(function(){
		var qty;
		if($(this).val() == '' || parseInt($(this).val()) < 1) {
			qty = parseInt(1);
			
		} else {
			qty = parseInt($(this).val());
		}
		$(this).val(qty);
		$('#' + prd_id).find('.prd_qty').text(qty.formatMoney(0, '.', ','));
		calSummary();
	});

	// show when load image success
	var prdImg = $('#edit-quantity-product .prd_image');
	$(prdImg).load(function(){
		if(prdImg.height() > 250) {
			prdImg.css('height', '250px');
		}
		$('#edit-quantity-product-body').css('visibility', 'visible');
	});
}
function closeEditQtyBox() {
	$('#sale-product-list tr').removeClass('selected');
	$('#edit-quantity-product').remove();
}

function openPayBox() {
	var  payBoxHTML = '<div id="payBox">'
					+ ' 	<div id="payBox-inner">'
					+ '			<div id="payBox-inner-header">'
					+ '				<h1>ชำระเงิน</h1>'
					+ '			</div>'
					+ ' 		<div id="payBox-inner-body">'
					+ '				<div id="payBox-leftCont"></div>'
					+ '				<div id="payBox-rightCont"></div>'
					+ '			</div>'
					+ ' 	</div>'
					+ '</div>';
	$('body').prepend(payBoxHTML);
	$('#payBox').css('visibility', 'hidden');

	// Set position
    $('#payBox-inner').css('margin-top', -Math.abs($('#payBox-inner').outerHeight(true) / 2));
    $('#payBox-inner').css('margin-left', -Math.abs($('#payBox-inner').outerWidth(true) / 2));
    $('#payBox').css('visibility', 'visible');
}
function closePayBox() {
	$('#payBox').remove();
}