<?php
$monthsTH = array(
  	'January'		=> 'มกราคม',
  	'February'	=> 'กุมภาพันธ์',
  	'March'		=> 'มีนาคม',
  	'April'		=> 'เมษายน',
  	'May'			=> 'พฤษภาคม',
  	'June'		=> 'มิถุนายน',
  	'July'		=> 'กรกฎาคม',
  	'August'		=> 'สิงหาคม',
  	'September'	=> 'กันยายน',
  	'October'		=> 'ตุลาคม',
  	'November'	=> 'พฤศจิกายน',
  	'December'	=> 'ธันวาคม',
);
$monthsTHMin = array(
  	'January'		=> 'ม.ค.',
  	'February'	=> 'ก.พ.',
  	'March'		=> 'มี.ค.',
  	'April'		=> 'เม.ย.',
  	'May'			=> 'พ.ค.',
  	'June'		=> 'มิ.ย.',
  	'July'		=> 'ก.ค.',
  	'August'		=> 'ส.ค.',
  	'September'	=> 'ก.ย.',
  	'October'		=> 'ต.ค.',
  	'November'	=> 'พ.ย.',
  	'December'	=> 'ธ.ค.',
);
$monthsTH2 = array(
	'0'			=> 'มกราคม',
  	'1'			=> 'กุมภาพันธ์',
  	'2'			=> 'มีนาคม',
  	'3'			=> 'เมษายน',
  	'4'			=> 'พฤษภาคม',
  	'5'			=> 'มิถุนายน',
  	'6'			=> 'กรกฎาคม',
  	'7'			=> 'สิงหาคม',
  	'8'			=> 'กันยายน',
  	'9'			=> 'ตุลาคม',
  	'10'		=> 'พฤศจิกายน',
  	'11'		=> 'ธันวาคม'
);

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

function getRealDate($input) {
	/*** Set date th to date eng ***/
	global $monthsTH;
	$dateTH = $input;
	$i = 1;
	foreach ($monthsTH as $key => $curMonthsTH) {
	  	if(strpos($dateTH, $curMonthsTH) !== false) {
	  		$dateEn = str_replace($curMonthsTH, $i, $dateTH);
	  		//echo "replace $curMonthsTH in $dateTH";
	  		$tmpDateEn = split(' ', $dateEn);
	  		$tmpDateEn[2] -= 543;
	  		$realDate = $tmpDateEn[2]."/".$tmpDateEn[1]."/".$tmpDateEn[0];
	  		return $realDate;
	  	}
	  	$i++;
	}

	return "";
}

function alertNoPrivlgTableData() {
	echo '	<div class="noPrivilege-tableData">
			<i class="fa fa-lock"></i><br> คุณไม่ได้รับสิทธิ์ในการเข้าถึงข้อมูลในส่วนนี้
			</div>';
}

function intervalTime($a, $b) {
	$tmpA = split(':', $a);
	$tmpB = split(':', $b);

	if(!isset($tmpA[2])) $tmpA[2] = 0;
	if(!isset($tmpB[2])) $tmpB[2] = 0;

	$minA = ((int)$tmpA[0] * 60) + (int)$tmpA[1] + (float)$tmpA[2]/100;
	$minB = ((int)$tmpB[0] * 60) + (int)$tmpB[1] + (float)$tmpB[2]/100;
	return $minA - $minB;
}
?>