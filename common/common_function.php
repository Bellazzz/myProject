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

function moneyThaiText($number){ 
$txtnum1 = array('ศูนย์','หนึ่ง','สอง','สาม','สี่','ห้า','หก','เจ็ด','แปด','เก้า','สิบ'); 
$txtnum2 = array('','สิบ','ร้อย','พัน','หมื่น','แสน','ล้าน','สิบ','ร้อย','พัน','หมื่น','แสน','ล้าน'); 
$number = str_replace(",","",$number); 
$number = str_replace(" ","",$number); 
$number = str_replace("บาท","",$number); 
$number = explode(".",$number); 
if(sizeof($number)>2){ 
return 'ทศนิยมหลายตัว'; 
exit; 
} 
$strlen = strlen($number[0]); 
$convert = ''; 
for($i=0;$i<$strlen;$i++){ 
	$n = substr($number[0], $i,1); 
	if($n!=0){ 
		if($i==($strlen-1) AND $n==1){ $convert .= 'เอ็ด'; } 
		elseif($i==($strlen-2) AND $n==2){  $convert .= 'ยี่'; } 
		elseif($i==($strlen-2) AND $n==1){ $convert .= ''; } 
		else{ $convert .= $txtnum1[$n]; } 
		$convert .= $txtnum2[$strlen-$i-1]; 
	} 
} 

$convert .= 'บาท'; 
if($number[1]=='0' OR $number[1]=='00' OR 
$number[1]==''){ 
$convert .= 'ถ้วน'; 
}else{ 
$strlen = strlen($number[1]); 
for($i=0;$i<$strlen;$i++){ 
$n = substr($number[1], $i,1); 
	if($n!=0){ 
	if($i==($strlen-1) AND $n==1){$convert 
	.= 'เอ็ด';} 
	elseif($i==($strlen-2) AND 
	$n==2){$convert .= 'ยี่';} 
	elseif($i==($strlen-2) AND 
	$n==1){$convert .= '';} 
	else{ $convert .= $txtnum1[$n];} 
	$convert .= $txtnum2[$strlen-$i-1]; 
	} 
} 
$convert .= 'สตางค์'; 
} 
return $convert; 
} 
?>