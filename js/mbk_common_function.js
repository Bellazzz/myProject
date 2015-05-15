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
