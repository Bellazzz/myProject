function checkRequired(requiredFields) {
    for (i in requiredFields) {
        if ($('#' + requiredFields[i]).val() == '' || $('#' + requiredFields[i]).val() == null) {
            return requiredFields[i];
        }
    }
    return true;
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

function getDateString(date, time) {
    if(date == '') {
        return false;
    }

    var dateStr = date.replace(/\//g,'-');
    if(typeof(time) != 'undefinded' && time != '' && time !== null) {
        dateStr += ' ' + time;
    } else {
        dateStr += ' 00:00:00';
    }
    return dateStr;
}

function printElemBOF(elem) {
    var params = [
        'height='+screen.height,
        'width='+screen.width,
        'fullscreen=yes' // only works in IE, but here for completeness
    ].join(',');

    var mywindow = window.open('', 'my div', params);
    mywindow.document.write('<html><head><title>my div</title>');
    mywindow.document.write('<link rel="stylesheet" type="text/css" href="../inc/font-awesome/css/font-awesome.min.css"><link rel="stylesheet" type="text/css" href="../css/lazybingo.css">');
    /*optional stylesheet*/ //mywindow.document.write('<link rel="stylesheet" href="main.css" type="text/css" />');
    mywindow.document.write('</head><body >');
    mywindow.document.write(elem.html());
    mywindow.document.write('</body></html>');

    mywindow.document.close(); // necessary for IE >= 10
    mywindow.focus(); // necessary for IE >= 10

    mywindow.print();
    mywindow.close();

    return true;
}

function tableToExcel(table, name) {
    var uri = 'data:application/vnd.ms-excel;base64,'
    , template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>{worksheet}</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--><meta http-equiv="content-type" content="text/plain; charset=UTF-8"/></head><body><table>{table}</table></body></html>'
    , base64 = function(s) { return window.btoa(unescape(encodeURIComponent(s))) }
    , format = function(s, c) { return s.replace(/{(\w+)}/g, function(m, p) { return c[p]; }) }

    if (!table.nodeType) table = document.getElementById(table)
    var ctx = {worksheet: name || 'Worksheet', table: table.innerHTML};
    uri += base64(format(template, ctx));
    var link = document.createElement("a");    
    link.href = uri;
    link.style = "visibility:hidden";
    link.download = name + ".xls";
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    //window.location.href = uri + base64(format(template, ctx))
}
