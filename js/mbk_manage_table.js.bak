$(document).ready(function () {
    init();
    
	/*
	 * Sidebar
	 */
    $('.sidebar-body, .sidebar-footer').click(function (e) {
        toggleShowSidebar();
    });
	$('.tree-view-child li ').mouseenter(function() {
		if($('.sidebar').attr('aria-expanded') == 'false') {
			var top	= $(this).offset().top;
			var left = $('.sidebar').width();
			var text = $(this).find('.item-label').text();
			showTableTooltip( $(this).offset().top, left, text);
		}
	});
	$('.tree-view-child li ').mouseleave(function() {
		hideTableTooltip();
	});
 

	/*
	 * Tree view tool
	 */
    $('.tree-view').click(function (e) {
        e.stopPropagation();
    });
	$('.tree-view-root').click(function(){
	    $(this).siblings('.tree-view-child').slideToggle(200);
	    if ($(this).hasClass('hideChild')) {
	        $(this).removeClass('hideChild');
	    } else {
	        $(this).addClass('hideChild');
	    }
	});
	$('.tree-view ul li').click(function () {
        // Effect
		$('.sidebar').attr('table-selected', 'true');
	    $('.tree-view ul li').removeClass('selected');
		$(this).addClass('selected');

	    // Show table data
		var tableName = $(this).attr('id').replace('tn-', '');
		changeTable(tableName);
	});

    /*
     * Table toolbar
     */
	$('#add-record-btn').click(function () {
	    openFormTable('ADD', null);
	});
	$('#edit-record-btn').click(function () {
	    var code = '';
        var checkboxs = document.getElementsByName('table-record[]');
        for (i = 0; i < checkboxs.length; i++) {
            if (checkboxs[i].checked) {
                code = checkboxs[i].value;
                break;
            }
        }
	    openFormTable('EDIT', code);
	});
	$('#delete-record-btn').click(function () {
	    deleteRecordSelected();
	});
	$('#cancel-select-btn').click(function () {
	    cancelSelectRecord();
	});
	$('#search-record-input').keyup(function () {
	    if ($(this).val() != '') {
	        $('#clear-search-record-input').css('display', 'block');
	    } else {
	        $('#clear-search-record-input').css('display', 'none');
	    }

	    clearTimeout(timer);
	    var ms = 300; // Delay search (miliseconds)
	    timer = setTimeout(function () {
	        searchRecord();
	    }, ms);
	});
	$('#clear-search-record-input').click(function () {
	    $('#clear-search-record-input').css('display', 'none');
	    $('#search-record-input').val('');
	    searchRecord();
	});
});

function init() {
    
    $('#manage-box').css('display', 'none');
    //changeTable('titles');
    $('.tree-view-child').slideUp(0);
}

/*
 * Data of page
 */
var table = {
    'name'          : '',
    'sortCol'       : '',
    'sortBy'        : '',
    'searchCol'     : '',
    'searchInput'   : ''
};
var timer;

function setTable(table) {
    this.table = table;
	$('.cur-table-name h1').text(this.table.nameTH);
}

function clearTable() {
    table = {
        'name'          : '',
        'sortCol'       : '',
        'sortBy'        : '',
        'searchCol'     : '',
        'searchInput'   : ''
    };
    $('#search-record-input').val('');
}

function changeTable(tableName) {
    clearTable();
    this.table.name = tableName;
    pullTable(true);
}

function refreshTable() {
    $('#search-record-input').val('');
    pullTable(true);
}

function pullTable(reFilter) {
    // Effect for processing
    $('.page-panel-content').css('opacity', '0.5');

    // Prepare variables
    var searchCol   = $('#search-record-filter').val();
    var searchInput = $('#search-record-input').val();
 
    $.ajax({
        url: 'table_data.php',
        type: 'POST',
        data: 'tableName=' + this.table.name + '&sortCol=' + this.table.sortCol + '&sortBy=' + this.table.sortBy + '&searchCol=' + searchCol + '&searchInput=' + searchInput,
        success:
        function (response) {
            var htmlResponse;
            if (response == 'EMPTY') {
                htmlResponse = 'EMPTY';
            } else {
                htmlResponse = response;
            }

            // Refresh Content
            $('.page-panel-content').html('');
            $('.page-panel-content').html(htmlResponse);
            refreshToolbarMenu();
            if (reFilter) {
                refreshSearchRecord();
            }

            // Add Trigger
            $('.tablesorter-header').click(function () {
                var sortColNew = $(this).attr('id').replace('tf-', '');
                sortTable(sortColNew);
            });

            // Effect for end process
            $('.page-panel-content').css('opacity', '1');
        }
    });
}

function sortTable(sortColNew) {
    if (sortColNew == table.sortCol) {
        table.sortBy = table.sortBy == 'asc' ? 'desc' : 'asc';
    } else {
        table.sortBy = 'asc';
    }
    table.sortCol = sortColNew;
    pullTable(false);
}

function searchRecord() {
    pullTable(false);
}

function delteCurrentRecord(code) {
    showActionDialog({
        title: 'ลบข้อมูล',
        actionList: [
            {
                id: 'delete',
                name: 'ลบ',
                desc: 'ลบข้อมูลที่เลือก'
            },
            {
                id: 'cancel',
                name: 'ยกเลิก',
                desc: 'ยกเลิกการลบ'
            }
        ]
    });
    $('#action-btn-delete').click(function () {
        var codeArr = [code];
        $.ajax({
            url: 'delete_record.php',
            type: 'POST',
            data: {
                'keySelected': codeArr,
                'tableName': table.name
            },
            success:
            function (response) {
                var htmlResponse;
                if (response == 'PASS') {
                    // Delete Success
                    hideActionDialog();
                    pullTable(false);
                } else if(response == 'DELETE_REFERENCE') {
					alert('ไม่สามารถลบข้อมูลได้ เนื่องจากมีตารางอื่นอ้างอิงข้อมูลนี้อยู่');
					hideActionDialog();
				} else {
                    alert(response);
                }
            }
        });
    });

    $('#action-btn-cancel').click(function () {
        hideActionDialog();
    });
}
function deleteRecordSelected() {
    var keySelected = Array();
    var checkboxs = document.getElementsByName('table-record[]');
    for (i = 0; i < checkboxs.length; i++) {
        if (checkboxs[i].checked) {
            keySelected.push(checkboxs[i].value);
        }
    }

    $.ajax({
        url: 'delete_record.php',
        type: 'POST',
        data: {
            'keySelected'   : keySelected,
            'tableName'     : table.name
        },
        success:
		function (response) {
		    if (response == 'PASS') {
		        // Delete Success
		        pullTable(false);
		    }else if(response == 'DELETE_REFERENCE') {
				alert('ไม่สามารถลบข้อมูลได้ เนื่องจากมีตารางอื่นอ้างอิงข้อมูลนี้อยู่');
				hideActionDialog();
			} else {
		        alert(response);
		    }
		}
    });
}



function checkRecord(item) {
    var tr = item.parentNode.parentNode;
    if(item.checked) {
        $(tr).addClass('selected');
    } else {
        $(tr).removeClass('selected');
    }
    refreshToolbarMenu();
}


/*
 * Side bar
 */
function toggleShowSidebar() {
    var newFlag = $('.sidebar').attr('aria-expanded') == 'true' ? 'false' : 'true';
    $('.sidebar').attr('aria-expanded', newFlag);
    if (newFlag == 'false') {
        //$('.tree-view').css('display', 'none');
    } else {
        //$('.tree-view').css('display', 'block');
    }
}

/*
 * Table Toolbar menu
 */
function refreshToolbarMenu() {
    var checkboxs = document.getElementsByName('table-record[]');
    var numCheckboxs = 0;
    var oneKeySelect;
    for (i = 0; i < checkboxs.length; i++) {
        if (checkboxs[i].checked) {
            numCheckboxs++;
            oneKeySelect = checkboxs[i].value;
        }
    }

    if (numCheckboxs == 1) {
        $('#toolbar-curselect').text(oneKeySelect);
        $('#toolbar-curselect').css('display', 'inline-block');
        $('#edit-record-btn').css('display', 'inline-block');
        $('#delete-record-btn').css('display', 'inline-block');
        $('#cancel-select-btn').css('display', 'inline-block');
        $('table.mbk').addClass('hasSelectRecord');
    } else if (numCheckboxs > 1) {
        $('#toolbar-curselect').text(numCheckboxs + ' แถวที่เลือก');
        $('#toolbar-curselect').css('display', 'inline-block');
        $('#edit-record-btn').css('display', 'none');
        $('#delete-record-btn').css('display', 'inline-block');
        $('#cancel-select-btn').css('display', 'inline-block');
        $('table.mbk').addClass('hasSelectRecord');
    } else {
       $('#edit-record-btn').css('display', 'none');
        $('#delete-record-btn').css('display', 'none');
        $('#cancel-select-btn').css('display', 'none');
        $('#toolbar-curselect').css('display', 'none');
        $('table.mbk').removeClass('hasSelectRecord');
    }
}

function cancelSelectRecord() {
    var checkboxs = document.getElementsByName('table-record[]');
    for (i = 0; i < checkboxs.length; i++) {
        checkboxs[i].checked = false;
        var tr = checkboxs[i].parentNode.parentNode;
        $(tr).removeClass('selected');
        if (checkboxs[i].checked) {
            $(tr).addClass('selected');
        } else {
            $(tr).removeClass('selected');
        }
    }
    refreshToolbarMenu();
}

function refreshSearchRecord() {
    var options = '';
    for(fieldEn in this.table.fieldNameList) {
        var fieldTh = this.table.fieldNameList[fieldEn];
        options += '<option value="' + fieldEn + '">' + fieldTh + '</option>';
        
    }
    $('#search-record-input').val('');
    $('#search-record-filter').html(options);
}

/*
 * Dialog Box
 */
function openFormTable(action, code) {
    showOverlayInner();
    $('#manage-box').css('display', 'block');
    $('.manage-box-loading').css('display', 'block');
    
    var tableForm = document.getElementById('iframe-form-table');
    var src = 'form_table.php' + '?action=' + action + '&tableName=' + this.table.name;
    if (typeof(code) != 'undefinded' && code != null) {
        src += '&code=' + code;
    }

    tableForm.style.visibility = "hidden";

    function endload() {
        $('.manage-box-loading').css('display', 'none');
        tableForm.style.visibility = "visible";
        $('#manage-box').css('display', 'block');
    }

    if (tableForm.attachEvent) {
        tableForm.attachEvent('onload', endload);
    }
    else {
        tableForm.onload = endload;
    }

    tableForm.src = src;
}

function confirmCloseFormTable(action) {
    if (action == 'ADD') {
        parent.showActionDialog({
            title: 'ปิดฟอร์มเพิ่มข้อมูล',
            actionList: [
                {
                    id: 'close',
                    name: 'ปิด',
                    desc: 'ข้อมูลที่กรอกจะถูกลบ'
                },
                {
                    id: 'cancel',
                    name: 'ยกเลิก',
                    desc: 'กลับสู่ฟอร์มเพิ่มข้อมุล'
                }
            ]
        });
        $('#action-btn-close').click(function () {
            hideOverlayInner();
            $('#manage-box').css('display', 'none');
            hideActionDialog();
        });
        $('#action-btn-cancel').click(function () {
            hideActionDialog();
        });

    } else if (action == 'EDIT') {
        parent.showActionDialog({
            title: 'ปิดฟอร์มแก้ไขข้อมูล',
            actionList: [
                {
                    id: 'close',
                    name: 'ปิด',
                    desc: 'ข้อมูลที่เปลี่ยนแปลงจะไม่ถูกบันทึก'
                },
                {
                    id: 'cancel',
                    name: 'ยกเลิก',
                    desc: 'กลับสู่ฟอร์มแก้ไขข้อมุล'
                }
            ]
        });
        $('#action-btn-close').click(function () {
            hideOverlayInner();
            $('#manage-box').css('display', 'none');
            hideActionDialog();
        });
        $('#action-btn-cancel').click(function () {
            hideActionDialog();
        });
    }
}

function closeFormTable() {
    hideOverlayInner();
    $('#manage-box').css('display', 'none');
}

function showTableTooltip(top, left, text) {
	var htmlTooltip = '<div class="table-tooltip">' + text + '</div>';
	$('body').append(htmlTooltip);
	$('.table-tooltip').css('top', top + 'px');
	$('.table-tooltip').css('left', left + 'px');
}
function hideTableTooltip() {
	$('.table-tooltip').remove();
}