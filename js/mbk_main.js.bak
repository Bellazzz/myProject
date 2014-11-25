$(document).ready(function () {
    hideOverlayInner();
    hideOverlayFull();

    $(document).click(function () {
        hideAllPopup();
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
    // Create action dialog ถ้าหาวิธี remove tag เจอค่อยใช้แบบนี้ ตอนนี้ใช้แบบล่างไปก่อน
    /*var actionDialog    = '<div class="action-dialog">'
                        + '<h2>' + obj.title + '</h2>'
                        + '</div>';
    $('body').append(actionDialog);*/

    $('.action-dialog').html('<h2>' + dialog.title + '</h2>');

    for (i in dialog.actionList) {
        // Create action button
        var actionItem = dialog.actionList[i];
        var actionBtn = '<div id="action-btn-' + actionItem.id + '" class="action-btn">'
                      + '<h1>' + actionItem.name + '</h1>'
                      + actionItem.desc
                      + '</div>';
        $('.action-dialog').append(actionBtn);
    }

    // Set position
    $('.action-dialog').css('margin-top', -Math.abs($('.action-dialog').height() / 2));
    $('.action-dialog').css('margin-left', -Math.abs($('.action-dialog').width() / 2));

    showOverlayFull();
    $('.action-dialog').css('display', 'block');
}

function hideActionDialog() {
    $('.action-dialog').css('display', 'none');
    hideOverlayFull();
}

function hideAllPopup() {
    $('.select-reference-container').css('display', 'none'); // select reference
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
    var searchTool      = true;
    var defaultValue    = '';
	var pattern			= '';
    var limit           = 15; //Option item limit
    var selectRefCon;
    var optionCon;
    var searchCon;
    var searchInput;
    var li;
    var inputHidden;
    var textShow;
    var timer;
    
    function init() {
        var initTag = '<span class="mbk-icon-16 mbk-icon-16-dropdown" style="right: 5px;"></span>'
                    + '<span class="select-reference-text">กรุณาเลือก</span>'
                    //+ '<span class="select-reference-value"></span>'
                    + '<input class="select-reference-input" type="hidden" name="' + select.elem.attr('id') + '">'
                    + '<div class="select-reference-container">';
        if (searchTool) {
            initTag += '     <div class="search-container">'
                     + '         <span class="mbk-icon-16 mbk-icon-16-search"></span>'
                     + '         <input class="search-input" type="text">'
                     + '     </div>'
        }
        initTag += '     <ul id="' + select.elem.attr('id') + '-option-container" class="option-container"></ul>'
                 + '</div>';

        select.elem.attr('more', 'true');
        select.elem.attr('begin', '0');
        select.elem.html(initTag);

        // Add Event Listener
        $(select.elem).click(function (e) {
            e.stopPropagation();

            if (selectRefCon.css('display') == 'none') {
                hideAllPopup();
                showSelectReference($(this));
            } else {
                hideAllPopup();
            }
        });

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
            url: '../common/select_reference.php',
            type: 'POST',
            async:false, 
            cache: false,

            data: {
                'id'            : select.elem.attr('id'),
                'tableName'     : tableName,
                'keyFieldName'  : keyFieldName,
                'textFieldName' : textFieldName,
				'pattern'		: pattern,
                'searchText'    : $(searchInput).val(),
                'begin'         : select.elem.attr('begin'),
                'limit'         : limit
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
            $(this).parent().parent().parent().removeClass('required');
            selectRefCon.siblings('.select-reference-text').text($(this).children('.text').text());
            selectRefCon.siblings('.select-reference-input').val($(this).children('.value').text());
            hideAllPopup();
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
            }
            pullRefData();
        }
    });
}