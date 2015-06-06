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
        $(this).parent().siblings('.tree-view').find('.tree-view-child').slideUp(200);
        $(this).parent().siblings('.tree-view').find('.tree-view-root').removeClass('hideChild');

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
    // find all tree view child
    $('.allTreeViewChild').each(function(){
        var root     = $(this).parent().parent().parent().parent();
        var allChild = root.find('.tree-view-child li').length;
        $(this).text(allChild);

        if(allChild > 0) {
            root.css('display', 'block');
        } else {
            root.css('display', 'none');
        }
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
            currentPage = 1;
	        searchRecord();
	    }, ms);
	});
	$('#clear-search-record-input').click(function () {
	    $('#clear-search-record-input').css('display', 'none');
	    $('#search-record-input').val('');
	    searchRecord();
	});

    $('.overlay-inner').click(function() {
        document.getElementById("iframe-form-table").contentWindow.cancelBtnClick();
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
var currentPage = 1;
var recordDisplay = 9; // แสดงแถว

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
        'searchInput'   : '',
        'deleteTxtPatternMain'   : '',
        'deleteTxtPatternMin'   : ''
    };
    currentPage = 1;
    $('#search-record-input').val('');
}

function changeTable(tableName) {
    clearTable();
    this.table.name = tableName;
    refreshFilterQuery();
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
    var searchCol           = $('#search-record-filter').val();
    var searchInput         = $('#search-record-input').val();
    var queryFilter         = $('#query-record-filter').val();
    var filterRetroact      = $('#query-record-filter-retroact').val(); 
    var filterExpired       = $('#query-record-filter-expired').val(); 
 
    $.ajax({
        url: 'table_data.php',
        type: 'POST',
        data: 'tableName=' + this.table.name + '&sortCol=' + this.table.sortCol + 
              '&sortBy=' + this.table.sortBy + '&searchCol=' + searchCol + 
              '&searchInput=' + searchInput + '&filter=' + queryFilter + 
              '&filterRetroact=' + filterRetroact +
              '&filterExpired=' + filterExpired +
              '&page=' + currentPage + '&recordDisplay=' + recordDisplay,
        success:
        function (response) {
            var htmlResponse;
            if (response == 'EMPTY') {
                htmlResponse = 'EMPTY';
            } else {
                htmlResponse = response;
            }

            // Check for re pullTable with previous page
            if(htmlResponse.indexOf('table-data-empty') != -1 && currentPage > 1) {
                currentPage--;
                pullTable(reFilter);
                return;
            }

            // Refresh Content
            $('.page-panel-content').html('');
            $('.page-panel-content').html(htmlResponse);
            refreshToolbarMenu();
            refreshPaging();
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
    // Generate delete text
    var delText     = '';
    var delVal      = '';
    if(typeof(table.deleteTxtPatternMain) != 'undefined' && table.deleteTxtPatternMain != '') {
        delText = table.deleteTxtPatternMain;
        for(i in table.deleteTxtField) {
            delVal = $('tr[id="' + code + '"]').find('td[field="' + table.deleteTxtField[i] + '"]').text();
            delText = delText.replace('%f' + (parseInt(i)+1), delVal);
        }
    } else {
        for(i in table.deleteTxtField) {
            delVal += $('tr[id="' + code + '"]').find('td[field="' + table.deleteTxtField[i] + '"]').text() + ' ';
        }
        delText = 'คุณต้องการลบ' + table.nameTH + ' ' + delVal + 'ใช่หรือไม่?';
    }

    if(table.name == 'withdraws') {
        showConfirmDeleteWithdraws(code, delText);
    } else {
        confirmDelteCurrentRecord(code, delText);
    }
}

function confirmDelteCurrentRecord(code, delText) {
    showActionDialog({
        title: 'ลบข้อมูล',
        message: delText,
        actionList: [
            {
                id: 'delete',
                name: 'ลบ',
                func:
                function() {
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
                                hideActionDialog();
                                showActionDialog({
                                    title: 'เกิดข้อผิดพลาด',
                                    message: 'ไม่สามารถลบข้อมูลได้ เนื่องจากมีตารางอื่นอ้างอิงข้อมูลนี้อยู่',
                                    actionList: [
                                        {
                                            id: 'ok',
                                            name: 'ตกลง',
                                            func:
                                            function() {
                                                hideActionDialog();
                                            }
                                        }
                                    ]
                                });
                            } else {
                                alert(response);
                            }
                        }
                    });
                }
            },
            {
                id: 'cancel',
                name: 'ยกเลิก',
                func:
                function() {
                    hideActionDialog();
                }
            }
        ],
        boxWidth: 400
    });
}

function deleteWithdrawsRecord(code) {
    $.ajax({
        url: '../common/ajaxCheckWithdrawAmoutCover.php',
        type: 'POST',
        data: {
            wdwId : code
        },
        success:
        function(responseJSON) {
            var response = $.parseJSON(responseJSON);
            if(response.status == 'PASS') {
                // send to delete
                delteCurrentRecord(code);
            } else if(response.status == 'OVER') {
                parent.showActionDialog({
                    title: 'ไม่สามารถลบการเบิกได้',
                    message: 'ผลิตภัณฑ์จากการเบิกครั้งนี้ได้ถูกขายไปแล้ว ไม่สามารถคืนผลิตภัณฑ์ได้',
                    actionList: [
                        {
                            id: 'ok',
                            name: 'ตกลง',
                            func:
                            function() {
                                parent.hideActionDialog();
                            }
                        }
                    ],
                    boxWidth: 400
                });
            } else {
                alert(response.status);
            }
        }
    });
}

function showConfirmDeleteWithdraws(code, delText) {
    $.ajax({
        url: '../common/ajaxGetWithdrawDetailForDeleteDialog.php',
        type: 'POST',
        data: {
            wdwId : code
        },
        success:
        function(responseJSON) {
            var response = $.parseJSON(responseJSON);
            if(response.status == 'PASS') {
                // send to delete
                confirmDelteCurrentRecord(code, delText + response.wdwdtlHtml);
            } else if(response.status == 'FAIL') {
                parent.showActionDialog({
                    title: 'เกิดข้อผิดพลาด',
                    message: 'ไม่พบการเบิกรหัส ' + code,
                    actionList: [
                        {
                            id: 'ok',
                            name: 'ตกลง',
                            func:
                            function() {
                                parent.hideActionDialog();
                            }
                        }
                    ],
                    boxWidth: 400
                });
            } else {
                alert(response.status);
            }
        }
    });
}

function deleteRecordSelected() {
    var delText     = 'ข้อมูล' + table.nameTH + 'ที่คุณเลือกคือ<ol style="margin-bottom:10px;">';
    var delVal      = '';
    var keySelected = Array();
    var checkboxs = document.getElementsByName('table-record[]');

    for (i = 0; i < checkboxs.length; i++) {
        if (checkboxs[i].checked) {
            var code = checkboxs[i].value;
            keySelected.push(code);
            // Generate delete text
            if(typeof(table.deleteTxtPatternMin) != 'undefined' && table.deleteTxtPatternMin != '') {
                delVal = table.deleteTxtPatternMin;
                for(j in table.deleteTxtField) {
                    delTmpVal = $('tr[id="' + code + '"]').find('td[field="' + table.deleteTxtField[j] + '"]').text();
                    delVal = delVal.replace('%f' + (parseInt(j)+1), delTmpVal);
                }
                delText += '<li>' + delVal + '</li>';
            } else {
                delVal = '';
                for(j in table.deleteTxtField) {
                    delVal += $('tr[id="' + code + '"]').find('td[field="' + table.deleteTxtField[j] + '"]').text() + ' ';
                }
                delText += '<li>' + delVal + '</li>';
            }
        }
    }
    delText += '</ol>คุณต้องการลบข้อมูลทั้งหมดที่เลือกใช่หรือไม่?';

    showActionDialog({
        title: 'ลบข้อมูล',
        message: delText,
        actionList: [
            {
                id: 'delete',
                name: 'ลบ',
                desc: 'ลบข้อมูลที่เลือก',
                func:
                function() {
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
                                hideActionDialog();
                                showActionDialog({
                                    title: 'เกิดข้อผิดพลาด',
                                    message: 'ไม่สามารถลบข้อมูลที่เลือกได้ เนื่องจากมีตารางอื่นอ้างอิงข้อมูลที่เลือกอยู่',
                                    actionList: [
                                        {
                                            id: 'ok',
                                            name: 'ตกลง',
                                            func:
                                            function() {
                                                hideActionDialog();
                                            }
                                        }
                                    ]
                                });
                            } else {
                                alert(response);
                            }
                        }
                    });
                    hideActionDialog();
                }
            },
            {
                id: 'cancel',
                name: 'ยกเลิก',
                desc: 'ยกเลิกการลบ',
                func:
                function() {
                    hideActionDialog();
                }
            }
        ],
        boxWidth: 400
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

function refreshFilterQuery() {
    var filterRecordQueryHTML = '';
    var allowFilterRetroact   = ['orders','receives','time_attendances','element_checks','payrolls','booking','services','withdraws','sales'];
    var allowFilterExpired = ['packages','service_lists','service_list_promotions','service_list_promotion_details',
                                'package_promotions','package_promotion_details','product_promotions',
                                'promotion_products','promotion_discout_sales'];

    if(this.table.name == 'orders') {
        filterRecordQueryHTML   = 'ดูการสั่งซื้อที่มีสถานะ '
                                + '<select id="query-record-filter" class="mbk-select">'
                                + '     <option value="WAIT">ใบสั่งซื้อ</option>'
                                + '     <option value="REMAIN">ค้างรับ</option>'
                                + '     <option value="COMPLETED">รับครบทั้งหมดแล้ว</option>'
                                + '     <option value="FIXED_COMPLETED">รับไม่ครบตามที่สั่งซื้อ</option>'
                                + '</select>';
    } else if(this.table.name == 'receives') {
        filterRecordQueryHTML   = 'ดูการรับที่มีสถานะ '
                                + '<select id="query-record-filter" class="mbk-select">'
                                + '     <option value="REMAIN">ค้างรับ</option>'
                                + '     <option value="COMPLETED">รับครบทั้งหมดแล้ว</option>'
                                + '     <option value="FIXED_COMPLETED">รับไม่ครบตามที่สั่งซื้อ</option>'
                                + '</select>';
    } else if(this.table.name == 'booking') {
        filterRecordQueryHTML   = 'ดูการจองที่มีสถานะ '
                                + '<select id="query-record-filter" class="mbk-select">'
                                + '     <option value="PENDING_CHECK">รอตรวจสอบ</option>'
                                + '     <option value="PENDING_PAYMENT">รอชำระเงิน</option>'
                                + '     <option value="PENDING_SERVICE">รอใช้บริการ</option>'
                                + '     <option value="REMAIN_SERVICE">ยังใช้บริการไม่ครบ</option>'
                                + '     <option value="COMPLETED">ใช้บริการครบแล้ว</option>'
                                + '     <option value="FIXED_COMPLETED">ใช้บริการไม่ครบตามที่จอง</option>'
                                + '     <option value="CANCEL">ยกเลิกการจอง</option>'
                                + '</select>';
    }

    if(allowFilterRetroact.indexOf(this.table.name) != -1) {
        var filterRetroactName = 'ขอบเขตการแสดง';
        var filterRetroactFrom = '';
        filterRecordQueryHTML  += '&emsp;&emsp;' + filterRetroactName + ' '
                                + '<select id="query-record-filter-retroact" class="mbk-select">'
                                + '     <option value="1">1 เดือนที่ผ่านมา</option>'
                                + '     <option value="3">3 เดือนที่ผ่านมา</option>'
                                + '     <option value="6">6 เดือนที่ผ่านมา</option>'
                                + '     <option value="9">9 เดือนที่ผ่านมา</option>'
                                + '     <option value="12">12 เดือนที่ผ่านมา</option>'
                                + '</select>'
                                + ' ' + filterRetroactFrom;
    }

    if(allowFilterExpired.indexOf(this.table.name) != -1) {
        var filterExpiredName = 'ดูจากสถานะ';
        var filterExpiredFrom = '';
        filterRecordQueryHTML  += '&emsp;&emsp;' + filterExpiredName + ' '
                                + '<select id="query-record-filter-expired" class="mbk-select">'
                                + '     <option value="ONLINE">กำลังใช้งานอยู่</option>'
                                + '     <option value="FORWARD">จัดล่วงหน้า</option>'
                                + '     <option value="EXPIRED">หมดอายุ</option>'
                                + '</select>'
                                + ' ' + filterExpiredFrom;
    }


    $('.table-toolbar-filter').html(filterRecordQueryHTML);

    // Add event
    if($('#query-record-filter').length > 0) {
        $('#query-record-filter').change(function() {
            pullTable(false);
        });
    }
    if($('#query-record-filter-retroact').length > 0) {
        $('#query-record-filter-retroact').change(function() {
            pullTable(false);
        });
    }
    if($('#query-record-filter-expired').length > 0) {
        $('#query-record-filter-expired').change(function() {
            pullTable(false);
        });
    }
}

function refreshSearchRecord() {
    var options = '';

    if(this.table.searchFields.length > 0) {
        for(i in this.table.searchFields) {
            var fieldEn = this.table.searchFields[i];
            var fieldTh = this.table.fieldNameList[fieldEn];
            options += '<option value="' + fieldEn + '">' + fieldTh + '</option>';
        }
    } else {
        for(fieldEn in this.table.fieldNameList) {
            var fieldTh = this.table.fieldNameList[fieldEn];
            options += '<option value="' + fieldEn + '">' + fieldTh + '</option>';
        }
    }

    $('#search-record-input').val('');
    $('#search-record-filter').html(options);

    // Hide search filter if has one field
    if(this.table.searchFields.length == 1) {
        $('#search-record-filter').css('visibility', 'hidden');
    } else {
        $('#search-record-filter').css('visibility', 'visible');
    }
}

function changePage(page) {
    this.currentPage = page;
    pullTable(false);
}

function refreshPaging() {
    var displayLimit    = 5;
    var lastPage        = Math.ceil(table.allRecords/recordDisplay);
    var startPage;
    var endPage;
    var pagingHTML      = '';

    // Calculate
    if(lastPage > displayLimit) {
        startPage = currentPage - Math.floor(displayLimit/2);
    } else {
        startPage = 1;
    }
    
    if(startPage < 1) {
        startPage = 1;
    }
    endPage = startPage + displayLimit - 1;
    if(endPage > lastPage) {
        endPage = lastPage;
    }
    if(lastPage > displayLimit) {
        if(startPage > endPage - displayLimit + 1) {
            startPage = endPage - displayLimit + 1;
        }
        if(startPage > 1) {
            pagingHTML += '<a href="javascript:changePage(1)">หน้าแรก</a>';
        }
        if(currentPage > 1) {
            var previousPage = currentPage - 1;
            pagingHTML += '<a href="javascript:changePage(' + previousPage + ')"><</a>';
        }
        if(startPage > 1) {
            pagingHTML += '...';
        }
    }

    for(i=startPage; i<=endPage; i++) {
        if(i == currentPage) {
            pagingHTML += '<a href="javascript:changePage(' + i + ')" class="selected">' + i + '</a>';
        } else {
            pagingHTML += '<a href="javascript:changePage(' + i + ')">' + i + '</a>';
        }
    }

    if(lastPage > displayLimit) {
        if(endPage < lastPage) {
            pagingHTML += '...';
        }
        if(currentPage < lastPage) {
            var nextPage = currentPage + 1;
            pagingHTML += '<a href="javascript:changePage(' + nextPage + ')">></a>';
        }
        if(endPage < lastPage) {
            pagingHTML += '<a href="javascript:changePage(' + lastPage + ')">หน้าสุดท้าย</a>';
        }
    }

    document.getElementById("pagingContainer").innerHTML = pagingHTML;

}

/*
 * Dialog Box
 */
function openManageBox(data) {
	showOverlayInner();
    // Init
    $('#manage-box').removeClass('large');
    $('#manage-box').removeClass('full');
    if(typeof(data.widthSize) != 'undefinded') {
        $('#manage-box').addClass(data.widthSize);
    }
    $('#manage-box').css('display', 'block');
    $('.manage-box-loading').css('display', 'block');
    
    var tableForm = document.getElementById('iframe-form-table');
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

    tableForm.src = data.formSrc;
}

function closeManageBox() {
	hideOverlayInner();
    $('#manage-box').css('display', 'none');
}

function openFormTable(action, code) {
    var src = 'form_table.php' + '?action=' + action + '&tableName=' + this.table.name
            + '&hideBackButton=true';
    if (typeof(code) != 'undefinded' && code != null) {
        src += '&code=' + code;
    }

    if(table.name == 'receives' || table.name == 'sales' || table.name == 'services' || table.name == 'booking' || table.name == 'packages') {
        openManageBox({
            formSrc     : src,
            widthSize   : 'full'
        });
    } else {
        openManageBox({
            formSrc: src
        });
    }
}

function confirmCloseFormTable(action) {
    if (action == 'ADD') {
        parent.showActionDialog({
            title: 'ปิดฟอร์มเพิ่มข้อมูล',
            actionList: [
                {
                    id: 'close',
                    name: 'ปิด',
                    desc: 'ข้อมูลที่กรอกจะถูกลบ',
                    func:
                    function() {
                        hideOverlayInner();
                        $('#manage-box').css('display', 'none');
                        hideActionDialog();
                    }
                },
                {
                    id: 'cancel',
                    name: 'ยกเลิก',
                    desc: 'กลับสู่ฟอร์มเพิ่มข้อมุล',
                    func:
                    function() {
                        hideActionDialog();
                    }
                }
            ]
        });
    } else if (action == 'EDIT') {
        parent.showActionDialog({
            title: 'ปิดฟอร์มแก้ไขข้อมูล',
            actionList: [
                {
                    id: 'close',
                    name: 'ปิด',
                    desc: 'ข้อมูลที่เปลี่ยนแปลงจะไม่ถูกบันทึก',
                    func:
                    function() {
                        hideOverlayInner();
                        $('#manage-box').css('display', 'none');
                        hideActionDialog();
                    }
                },
                {
                    id: 'cancel',
                    name: 'ยกเลิก',
                    desc: 'กลับสู่ฟอร์มแก้ไขข้อมุล',
                    func:
                    function() {
                        hideActionDialog();
                    }
                }
            ]
        });
    } else {
        hideOverlayInner();
        $('#manage-box').css('display', 'none');
    }
}

function closeFormTable() {
    closeManageBox();
}

function openPrintEmpCard(empId) {
	var src = 'printEmployeeCard.php?empId=' + empId + '&hideBackButton=true';
	openManageBox({
        formSrc: src
    });
}

function openPrintPurchaseOrder(ordId) {
    var src = 'printPurchaseOrder.php?ordId=' + ordId + '&hideBackButton=true';
    openManageBox({
        formSrc     : src
        //widthSize   : 'large'
    });
    //$('#manage-box').addClass('large');
}

function openPrintReceipt(saleId) {
    var src = 'printReceiptBOF.php?sale_id=' + saleId + '&hideBackButton=true';
    openManageBox({
        formSrc: src
    });
}

function openPrintServiceReceipt(serId) {
    var src = 'printServiceReceiptContainer.php?ser_id=' + serId + '&hideBackButton=true';
    openManageBox({
        formSrc     : src,
        widthSize   : 'full'
    });
}

function openPrintBookingPaper(bkgId, hideBackButton) {
    $.ajax({
        url: '../common/ajaxGetBankAccountData.php',
        type: 'POST',
        success:
        function(responseJSON) {
            var response = $.parseJSON(responseJSON);
            if(response.status == 'PASS') {
                // Generate input
                var innerHtml = 'เลือกบัญชีธนาคาร:<br><select id="selectPrintBnkacc" style="padding:6px 10px;">';
                for(i in response.data) {
                  innerHtml += '<option value="' + response.data[i].bnkacc_id + '">'
                             + response.data[i].bnkacc_name + ' (' + response.data[i].bnkacc_no + ')'
                             + '</option>';
                }
                innerHtml += '</select><br><br>';

                parent.showActionDialog({
                    title: 'ใบแสดงข้อมูลการชำระเงินการจอง',
                    message: innerHtml,
                    actionList: [
                        {
                            id: 'ok',
                            name: 'ดูข้อมูล',
                            func:
                            function() {
                                var bnkacc_id = $('#selectPrintBnkacc').val();
                                var src = 'printBookingPaperContainer.php?bkg_id='+bkgId+'&hideBackButton='+hideBackButton+'&bnkacc_id='+bnkacc_id;
                                hideActionDialog();
                                openManageBox({
                                    formSrc     : src,
                                    widthSize   : 'full'
                                });
                            }
                        },
                        {
                            id: 'cancel',
                            name: 'ยกเลิก',
                            func:
                            function() {
                                hideActionDialog();
                            }
                        }
                    ],
                    boxWidth: 500
                });
            } else if(response.status == 'EMPTY') {
                alert('ไม่พบข้อมูลบัญชีธนาคาร');
            } else {
                alert(response.status);
            }
        }
    });
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

function remindProductMinAmount() {
    $.ajax({
        url: '../common/ajaxGetRemindProductMinAmount.php',
        type: 'POST',
        success:
        function(responseJSON) {
            var response = $.parseJSON(responseJSON);
            if(response.status == 'OUT_OF_STOCK') {
                var msg = 'ผลิตภัณฑ์ดังต่อไปนี้มีจำนวนคงเหลือน้อยกว่าจำนวนที่กำหนดไว้'
                        + response.outOfStockListHtml;
                parent.showActionDialog({
                    title: 'ผลิตภัณฑ์คงเหลือน้อยกว่ากำหนด',
                    message: msg,
                    actionList: [
                        {
                            id: 'ok',
                            name: 'สั่งซื้อผลิตภัณฑ์',
                            desc: 'เปิดฟอร์มการสั่งซื้อ',
                            func:
                            function() {
                                hideActionDialog();
                                $('#tn-orders').parent().parent().parent().find('.tree-view-root').click();
                                $('#tn-orders').click();
                                $('#add-record-btn').click();
                            }
                        },
                        {
                            id: 'cancel',
                            name: 'ปิด',
                            desc: 'ปิดหน้าต่างนี้',
                            func:
                            function() {
                                hideActionDialog();
                            }
                        }
                    ],
                    boxWidth: 500
                });
            }
        }
    });
}