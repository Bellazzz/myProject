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
