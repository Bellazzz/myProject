var imgTmp = Array();

$(document).ready(function () {
    hideOverlayInner();
    hideOverlayFull();

    $(document).click(function () {
        hideAllPopup();
    });

	// Clear temp image when close page
	$(window).bind("beforeunload",function(event) {
		clearTempImage();
	});
});

function showOverlayInner() {
    $('.overlay-inner').css('display', 'block');
}

function hideOverlayInner() {
    $('.overlay-inner').css('display', 'none');
}

function showOverlayFull() {
    $('.overlay-full').css('display', 'block');
}

function hideOverlayFull() {
    $('.overlay-full').css('display', 'none');
}

function showActionDialog(dialog) {
    // Prepare variable
    var title   = '';
    var message = '';
    if(typeof(dialog.title) != 'undefined') {
        title = dialog.title;
    }
    if(typeof(dialog.message) != 'undefined') {
        message = dialog.message;
    }

    var actionDialogHTML    = '<div class="action-dialog-container">'
                            + '     <div class="action-dialog">'
                            + '         <h2>' + title + '</h2>'
                            + '         <span class="message">' + message + '</span>'
                            + '     </div>'
                            + '</div>';
    $('body').prepend(actionDialogHTML);
    $('.action-dialog-container').css('visibility', 'hidden');

    // Set width
    if(typeof(dialog.boxWidth) != 'undefined') {
        $('.action-dialog').css('width', dialog.boxWidth + 'px');
    }

    for (i in dialog.actionList) {
        // Create action button

        var actionItem = dialog.actionList[i];
        var desc = '';
        if(typeof(actionItem.desc) != 'undefined') {
            desc = actionItem.desc;
        }

        var actionBtn = '<div id="action-btn-' + actionItem.id + '" class="action-btn">'
                      + '<h1>' + actionItem.name + '</h1>'
                      + desc
                      + '</div>';
        $('.action-dialog').append(actionBtn);

        // Add event when click button
        $('#action-btn-' + actionItem.id).click(actionItem.func);
    }

    // Set position
    $('.action-dialog').css('margin-top', -Math.abs($('.action-dialog').outerHeight() / 2));
    $('.action-dialog').css('margin-left', -Math.abs($('.action-dialog').outerWidth() / 2));
    
    // Display
    $('.action-dialog-container').css('visibility', 'visible');
}

function hideActionDialog() {
    $('.action-dialog-container').remove();
}

function hideAllPopup() {
    $('.select-reference-container').css('display', 'none'); // select reference
    $('.selectReferenceJS-container').css('display', 'none'); // select reference js
}

function showIframeDialog(dialog) {
	// Create dialog
	var dialogHtml	= '<div class="iframe-dialog" style="width:' + dialog.boxWidth + 'px; height:' + dialog.boxHeight + 'px;">'
					+ '		<h1>' + dialog.title + '</h1>'
					+ '		<div class="iframe-dialog-body">'
					+ '			<iframe id="iframe-iframe-dialog" src="' + dialog.src + '">'
					+ '		</div>'
					+ '</div>';
	$('body').append(dialogHtml);
	$('.iframe-dialog').css('display', 'none');

	// Set position
    $('.iframe-dialog').css('margin-top', -Math.abs($('.iframe-dialog').height() / 2));
    $('.iframe-dialog').css('margin-left', -Math.abs($('.iframe-dialog').width() / 2));
	
	// Show dialog
	showOverlayFull();
    $('.iframe-dialog').css('display', 'block');
	
	// Iframe load
	var iframe = document.getElementById('iframe-iframe-dialog');
    iframe.style.visibility = "hidden";

    function endload() {
        $('.manage-box-loading').css('display', 'none');
        iframe.style.visibility = "visible";
        $('#manage-box').css('display', 'block');
    }

    if (iframe.attachEvent) {
        iframe.attachEvent('onload', endload);
    }
    else {
        iframe.onload = endload;
    }

    iframe.src = dialog.src;
}

/*
 * Select Reference
 */
function showSelectReference(selectRef) {
    $(selectRef).children('.select-reference-container').css('display', 'block');
}

function selectReference(select) {
    // Create select reference object
    var tableName       = select.tableName;
    var keyFieldName    = select.keyFieldName;
    var textFieldName   = select.textFieldName;
    var orderFieldName  = select.orderFieldName;
    var orderType       = '';
    var searchTool      = true;
    var defaultValue    = '';
	var pattern			= '';
    var ajaxUrl         = '../common/select_reference.php';
    var condition       = '';
    var showClearBtn    = false;
    var clearBtnText    = 'ไม่เลือก';
    var pleaseSelectText= 'กรุณาเลือก';
    var limit           = 15; //Option item limit
    var selectRefCon;
    var optionCon;
    var searchCon;
    var searchInput;
    var li;
    var inputHidden;
    var textShow;
    var timer;

    if(typeof(select.showClearBtn) != 'undefined') {
        showClearBtn        = select.showClearBtn;
    }
    if(typeof(select.clearBtnText) != 'undefined') {
        pleaseSelectText        = select.clearBtnText;
    }
    if(typeof(select.orderType) != 'undefined') {
        orderType        = select.orderType;
    }
    
    function init() {
        var initTag = '<span class="mbk-icon-16 mbk-icon-16-dropdown" style="right: 5px;"></span>'
                    + '<span class="select-reference-text">' + pleaseSelectText + '</span>'
                    + '<input class="select-reference-input" type="hidden" name="' + select.elem.attr('id') + '">'
                    + '<div class="select-reference-container">';
        if (searchTool) {
            initTag += '     <div class="search-container">'
                     + '         <span class="mbk-icon-16 mbk-icon-16-search"></span>'
                     + '         <input class="search-input" type="text">'
                     + '     </div>';
        }
        if(showClearBtn) {
            if(typeof(select.clearBtnText) != 'undefined') {
                clearBtnText = select.clearBtnText;
            }
            initTag += '     <div class="clear-value-btn">'
                     + '         <span>' + clearBtnText + '</span>'
                     + '     </div>';
        }
        initTag += '     <ul id="' + select.elem.attr('id') + '-option-container" class="option-container"></ul>'
                 + '</div>';

        select.elem.attr('more', 'true');
        select.elem.attr('begin', '0');
        select.elem.html(initTag);

        // Add event clear value
        var clearValBtn = select.elem.find('.clear-value-btn');
        clearValBtn.click(function(e){
            e.stopPropagation();
            if(typeof(select.allowChangeOption) == 'function') {
                if(!select.allowChangeOption('')) {
                    hideAllPopup();
                    return;
                }
            }
            $(this).parent().parent().parent().removeClass('required');
            $('.err-' + select.elem.attr('id')).css('display', 'none');
            selectRefCon.siblings('.select-reference-text').text(clearBtnText);
            selectRefCon.siblings('.select-reference-input').val('');
            hideAllPopup();
            if(typeof(select.onOptionSelect) == 'function') {
                select.onOptionSelect();
            }
        });

         // Skip this if has class text
        if(!select.elem.hasClass('text')) {
            // Add Event Listener
            $(select.elem).click(function (e) {
                e.stopPropagation();
                if(typeof(select.beforeShow) == 'function') {
                    if(!select.beforeShow()) {
                        hideAllPopup();
                        return;
                    }
                }

                if (selectRefCon.css('display') == 'none') {
                    hideAllPopup();
                    showSelectReference($(this));
                } else {
                    hideAllPopup();
                }
            });
        }

        // Use ajax url if has parameter
        if(typeof(select.ajaxUrl) != 'undefined') {
            ajaxUrl = select.ajaxUrl;
        }

        // Add condition for query
        if(typeof(select.condition) != 'undefined') {
            condition = select.condition;
        }

        // Prepare variable
        inputHidden = select.elem.find('.select-reference-input');
        textShow = select.elem.find('.select-reference-text');
    }

    function pullRefData() {
        if (select.elem.attr('more') != 'true') {
            return;
        }

        selectRefCon = $(select.elem).children('.select-reference-container');
        optionCon = $(selectRefCon).children('.option-container');
        if (searchTool) {
            searchCon = $(selectRefCon).children('.search-container');
            searchInput = $(searchCon).children('.search-input');
        }

        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            async:false, 
            cache: false,

            data: {
                'id'            : select.elem.attr('id'),
                'tableName'     : tableName,
                'keyFieldName'  : keyFieldName,
                'textFieldName' : textFieldName,
                'orderFieldName': orderFieldName,
                'orderType'     : orderType,
				'pattern'		: pattern,
                'searchText'    : $(searchInput).val(),
                'begin'         : select.elem.attr('begin'),
                'limit'         : limit,
                'condition'     : condition
            },
            success:
            function (response) {
                if (response == 'EMPTY') {
                    optionCon.html('EMPTY');
                } else {
                    optionCon.append(response);
                    li = $(optionCon).children('li');
                    addEvent();
                }

                // Check more query
                if (parseInt(select.elem.attr('responserows')) < limit) {
                    select.elem.attr('more', 'false');
                }

                $(optionCon).children('li.more-loader').remove();

                // More loader
                if (select.elem.attr('more') == 'true') {
                    optionCon.append('<li class="more-loader"></li>');
                }
            }
        });
    }
    
    function addEvent() {
        $(li).off();
        $(li).on('click', function (e) {
            e.stopPropagation();
            if(typeof(select.allowChangeOption) == 'function') {
                if(!select.allowChangeOption($(this).children('.value').text())) {
                    hideAllPopup();
                    return;
                }
            }
            $(this).parent().parent().parent().removeClass('required');
            $('.err-' + select.elem.attr('id')).css('display', 'none');
            selectRefCon.siblings('.select-reference-text').text($(this).children('.text').text());
            selectRefCon.siblings('.select-reference-input').val($(this).children('.value').text());
            hideAllPopup();
            if(typeof(select.onOptionSelect) == 'function') {
                select.onOptionSelect();
            }
        });

        $(searchCon).off();
        $(searchCon).on('click', function (e) {
            e.stopPropagation();
        });

        if (searchTool) {
            $(searchInput).off();
            $(searchInput).on('click', function (e) {
                e.stopPropagation();
            });
            $(searchInput).on('keyup', function () {
                clearTimeout(timer);
                var ms = 300; // milliseconds
                timer = setTimeout(function () {
                    // Clear value
                    select.elem.attr('more', 'true');
                    select.elem.attr('begin', '0');
                    optionCon.html('');
                    pullRefData();
                }, ms);
            });
        }

        $(optionCon).off();
        $(optionCon).on('scroll', function () {
            if (this.scrollTop + $(this).innerHeight() >= this.scrollHeight - $(this).find('.more-loader').height()) {
                pullRefData();
            }
        });                                                                                                             
    }

    // Prepare variable
    if (typeof (select.searchTool) != 'undefined' && select.searchTool != null) {
        searchTool = select.searchTool;
    }
    if (typeof (select.defaultValue) != 'undefined' && select.defaultValue != null) {
        defaultValue = select.defaultValue;
    }
	if (typeof (select.pattern) != 'undefined' && select.pattern != null) {
        pattern = select.pattern;
    }
    
    // Create select reference input
	$(select.elem).html('กำลังโหลด...');
    $.ajax({
        url: '../common/get_default_value_select_reference.php',
        type: 'POST',
        data: {
            'key'           : defaultValue,
            'tableName'     : tableName,
            'keyFieldName'  : keyFieldName,
            'textFieldName' : textFieldName,
			'pattern'		: pattern
        },
        success:
        function (response) {
            init();
            // Set default value
            if (response != '') {
                textShow.text(response);
                inputHidden.val(defaultValue);
            } else {
                // Null value will display (-) for view detail
                if(select.elem.hasClass('text')) {
                    textShow.text('-');
                    inputHidden.val('');
                }
            }

            // Skip this if has class text
            if(!select.elem.hasClass('text')) {
                pullRefData();
            }

            // Call back function success
            if(typeof(select.success) == 'function') {
                select.success();
            }
        }
    });
}

/*
 * select reference JS
 */
 function showSelectReferenceJS(selectRef) {
    $(selectRef).children('.selectReferenceJS-container').css('display', 'block');
}

function selectReferenceJS(select) {
    // Create select reference object
    var data            = select.data;
    var searchTool      = true;
    var defaultValue    = '';
    var showClearBtn    = false;
    var clearBtnText    = 'ไม่เลือก';
    var pleaseSelectText= 'กรุณาเลือก';
    var limit           = 15; //Option item limit
    var selectRefCon;
    var optionCon;
    var searchCon;
    var searchInput;
    var li;
    var inputHidden;
    var textShow;

    if(typeof(select.showClearBtn) != 'undefined') {
        showClearBtn = select.showClearBtn;
    }
    if(typeof(select.clearBtnText) != 'undefined') {
        pleaseSelectText = select.clearBtnText;
    }
    if(typeof(select.orderType) != 'undefined') {
        orderType = select.orderType;
    }
    
    function init() {
        var initTag = '<span class="mbk-icon-16 mbk-icon-16-dropdown" style="right: 5px;"></span>'
                    + '<span class="selectReferenceJS-text">' + pleaseSelectText + '</span>'
                    + '<input class="selectReferenceJS-input" type="hidden" name="' + select.elem.attr('id') + '">'
                    + '<div class="selectReferenceJS-container">';
        if (searchTool) {
            initTag += '     <div class="search-container">'
                     + '         <span class="mbk-icon-16 mbk-icon-16-search"></span>'
                     + '         <input class="search-input" type="text">'
                     + '     </div>';
        }
        if(showClearBtn) {
            if(typeof(select.clearBtnText) != 'undefined') {
                clearBtnText = select.clearBtnText;
            }
            initTag += '     <div class="clear-value-btn">'
                     + '         <span>' + clearBtnText + '</span>'
                     + '     </div>';
        }
        initTag += '     <ul id="' + select.elem.attr('id') + '-option-container" class="option-container"></ul>'
                 + '</div>';

        select.elem.html(initTag);

        // Add event clear value
        var clearValBtn = select.elem.find('.clear-value-btn');
        clearValBtn.click(function(e){
            e.stopPropagation();
            if(typeof(select.allowChangeOption) == 'function') {
                if(!select.allowChangeOption('')) {
                    hideAllPopup();
                    return;
                }
            }
            $(this).parent().parent().parent().removeClass('required');
            $('.err-' + select.elem.attr('id')).css('display', 'none');
            selectRefCon.siblings('.selectReferenceJS-text').text(clearBtnText);
            selectRefCon.siblings('.selectReferenceJS-input').val('');
            hideAllPopup();
            if(typeof(select.onOptionSelect) == 'function') {
                select.onOptionSelect();
            }
        });

        // Skip this if has class text
        if(!select.elem.hasClass('text')) {
            // Add Event Display option when click
            $(select.elem).click(function (e) {
                e.stopPropagation();
                if(typeof(select.beforeShow) == 'function') {
                    if(!select.beforeShow()) {
                        hideAllPopup();
                        return;
                    }
                }

                if (selectRefCon.css('display') == 'none') {
                    hideAllPopup();
                    showSelectReferenceJS($(this));
                } else {
                    hideAllPopup();
                }
            });
        }

        // Prepare variable
        inputHidden     = select.elem.find('.selectReferenceJS-input');
        textShow        = select.elem.find('.selectReferenceJS-text');
    }

    function pullRefData() {
        var options;
        var resultHtml = '';
        var searchText = '';

        selectRefCon = $(select.elem).children('.selectReferenceJS-container');
        optionCon = $(selectRefCon).children('.option-container');
        if (searchTool) {
            searchCon   = $(selectRefCon).children('.search-container');
            searchInput = $(searchCon).children('.search-input');
            searchText  = $(searchInput).val();
        }

        if(searchTool && searchInput.val() != '') {
            // Search
            options = $.grep(data, function(el) {
                return el.refText.indexOf(searchText) > -1;
            });
        } else {
            // Get all data
            options = data;
        }

        if(options.length > 0) {
            for(i in options) {
                resultHtml  += '<li id="' + options[i].refField + '_'+ options[i].refValue + '">'
                            + '     <span class="text">' + options[i].refText + '</span>'
                            + '     <span class="value">' + options[i].refValue + '</span>'
                            + '</li>';
            }
        } else {
            resultHtml = '<div class="no-result">ไม่พบข้อมูลที่ค้นหา</div>';
        }

        optionCon.html(resultHtml);
        li = $(optionCon).children('li');
        addEvent();
    }
    
    function addEvent() {
        $(li).off();
        $(li).on('click', function (e) {
            e.stopPropagation();
            if(typeof(select.allowChangeOption) == 'function') {
                if(!select.allowChangeOption($(this).children('.value').text())) {
                    hideAllPopup();
                    return;
                }
            }
            $(this).parent().parent().parent().removeClass('required');
            $('.err-' + select.elem.attr('id')).css('display', 'none');
            selectRefCon.siblings('.selectReferenceJS-text').text($(this).children('.text').text());
            selectRefCon.siblings('.selectReferenceJS-input').val($(this).children('.value').text());
            hideAllPopup();
            if(typeof(select.onOptionSelect) == 'function') {
                select.onOptionSelect();
            }
        });

        $(searchCon).off();
        $(searchCon).on('click', function (e) {
            e.stopPropagation();
        });

        if (searchTool) {
            $(searchInput).off();
            $(searchInput).on('click', function (e) {
                e.stopPropagation();
            });
            $(searchInput).on('keyup', function () {
                optionCon.html('');
                pullRefData();
            });
        }                                                                                                            
    }

    // Prepare variable
    if (typeof (select.searchTool) != 'undefined' && select.searchTool != null) {
        searchTool = select.searchTool;
    }
    if (typeof (select.defaultValue) != 'undefined' && select.defaultValue != null) {
        defaultValue = select.defaultValue;
    }
    if (typeof (select.pattern) != 'undefined' && select.pattern != null) {
        pattern = select.pattern;
    }
    
    // Create select reference input
    $(select.elem).html('กำลังโหลด...');
    init();
    pullRefData();

    // Set default value
    if(typeof(select.defaultValue) != 'undefined' && select.defaultValue != '') {
        for(i in data) {
            if(select.defaultValue == data[i].refValue) {
                inputHidden.val(data[i].refValue);
                textShow.text(data[i].refText);
                break;
            }
        }
    }
}

/*
 * Upload image input
 */
 function uploadImageInput(data) {
	 var imgData = Array();

     // Initial
    var inner   = '<div class="desc">'
                + '     <i class="fa fa-image"></i><br>'
                + '     JPG, JPEG, PNG'
                + '</div>'
                + '<ul class="control">'
                + '     <li class="clearImgBtn">ลบ</li>'
                + '     <li class="editImgBtn">เปลี่ยนรูปภาพ</li>'
                + '     <li class="chooseImgBtn">เลือกรูปภาพ</li>'
                + '</ul>';
    $(data.area).html(inner);
	 
	 // Set default image
	 if(typeof(data.defaultValue) != 'undefined' && data.defaultValue != '') {
         showImage(data.defaultValue);
	 }
	
	// Add event
    $(data.area).find('.chooseImgBtn').click(function() {
        data.selector.click();
    });
    $(data.area).find('.editImgBtn').click(function() {
        data.selector.click();
    });
    $(data.area).find('.clearImgBtn').click(function() {
        $(data.area).removeClass('hasImage');
        $(data.area).css('background-image', 'none');
        data.input.val('');
    });

	data.selector.change(function(e){
		clearTempImage();

		// fetch FileList object
		var allowType	= ['jpg', 'jpeg', 'png'];
		var files		= e.target.files || e.dataTransfer.files;
		var file		= files[0]; // Only one file
		var ftype		= file.type.replace("image/","");
		var form		= $(this).parent();

		// Parse file
		if(file.type.indexOf("image") == 0 && allowType.indexOf(ftype)) {
			// Show image in area
			var reader = new FileReader();
			reader.onload = function(e) {
				showImage(e.target.result);
				
				// Ajax upload temp image
				var xhr;
				if (window.XMLHttpRequest) {// code for IE7+, Firefox, Chrome, Opera, Safari
					xhr = new XMLHttpRequest();
				}
				else {// code for IE6, IE5
					xhr = new ActiveXObject("Microsoft.XMLHTTP");
				}
				if(xhr.upload){
					// start upload
					imgType	= file.type.replace("image/","");
					xhr.open("POST", "../common/ajaxUploadTempImage.php?imgType=" + imgType);
					xhr.send(file);
					xhr.onreadystatechange = function() {
						if (xhr.readyState == 4) {
							var response = $.parseJSON(xhr.responseText);
							if(response.status == "PASS") {
								data.input.val(response.imgName);
								imgTmp.push(response.imgPath);
							} else {
								alert(response.status);
							}
						}
					}
				}
			}
			reader.readAsDataURL(file);
		} else {
            parent.showActionDialog({
                title: 'รูปภาพไม่ถูกต้อง',
                message: 'ไฟล์รูปภาพที่คุณเลือกไม่ถูกต้อง <br>กรุณาเลือกไฟล์ ' + allowType.join(' ,') + ' เท่านั้น',
                actionList: [
                    {
                        id: 'ok',
                        name: 'ตกลง',
                        desc: 'เลือกไฟล์รูปภาพใหม่',
                        func:
                        function() {
                            parent.hideActionDialog();
                        }
                    }
                ],
                boxWidth: 400
            });
        }
	});

	function showImage(bg) {
		var img = new Image();
		img.src = bg;
		img.onload = function() {
			//Image size less than wrapper
			if(img.width <= data.area.width() && img.height <= data.area.height()) {
				$(data.area).css("background-size", "auto");
			}
		}
		var url = "url(" + img.src + ")";
		$(data.area).css('background-image', url);
        $(data.area).addClass('hasImage');
	}
 }

 function clearTempImage() {
	 for(i in imgTmp) {
		 $.ajax({
			 url: '../common/ajaxDeleteTempImage.php',
			 type: 'POST',
			 data: {imgPath: imgTmp[i]},
			 success:
			 function(response) {
				if(response == 'PASS' || response == 'FILE_NOT_EXISTS') {
					delete imgTmp[i];
				} else {
					alert(response);
				}
			 }
		 });
	 }
 }

Number.prototype.formatMoney = function(c, d, t){
    var n = this, 
        c = isNaN(c = Math.abs(c)) ? 2 : c, 
        d = d == undefined ? "." : d, 
        t = t == undefined ? "," : t, 
        s = n < 0 ? "-" : "", 
        i = parseInt(n = Math.abs(+n || 0).toFixed(c)) + "", 
        j = (j = i.length) > 3 ? j % 3 : 0;
       return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
 };