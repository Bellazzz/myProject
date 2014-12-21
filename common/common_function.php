<?php
function wrapSingleQuote($value) {
	if(is_array($value)) {
		foreach ($value as $key => $val) {
			$value[$key] = "'$val'";
		}
		return $value;
	} else {
		return "'$value'";
	}
}//เอาไว้ครอบ Single Qute เมื่อรับค้าตอน Insert

function hasValue($variable) {
	if(isset($variable) && $variable != null && $variable != '') {
		return true;
	} else {
		return false;
	}
}

function dateThaiFormat($date) {
	global $monthThai;
	$day 	= date('d', strtotime($date));
	$month 	= $monthThai[(int)date('m', strtotime($date))-1];
	$year 	= date('Y', strtotime($date))+543;
	return "$day $month $year";
}

function dateThaiFormatShort($date) {
	global $monthThaiMin;
	$day 	= date('d', strtotime($date));
	$month 	= $monthThaiMin[(int)date('m', strtotime($date))-1];
	$year 	= date('Y', strtotime($date))+543;
	return "$day $month $year";
}

function redirect($location) {
	echo "<script>location='$location'</script>";
}

?>