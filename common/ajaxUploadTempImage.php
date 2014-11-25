<?
$uploadPath	= '../img/temp/';
$imgType	= $_REQUEST['imgType'];

// Generage temp name
$rand		= substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ23456789'),0,9);
$imgName	= "temp_".$rand.".".$imgType;
$imgPath    = $uploadPath.$imgName;

// Upload image to tmp dir
if(file_put_contents($imgPath, file_get_contents('php://input'))) {
	$pass = true;
} else {
	$pass = false;
}

// Return response
if($pass) {
	$response = array(
		'status'	=> "PASS",
		'imgName'	=> $imgName,
		'imgType'	=> $imgType,
		'imgPath'	=> $imgPath
	);
} else {
	$response = array(
		'status'	=> "FAIL"
	);
}
echo json_encode($response);

?>