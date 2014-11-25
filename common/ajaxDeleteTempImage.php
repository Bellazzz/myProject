<?
if(file_exists($_REQUEST['imgPath'])) {
	if(unlink($_REQUEST['imgPath'])) {
		echo 'PASS';
	} else {
		echo 'DELETE_TEMP_IMAGE_FAIL';
	}
} else {
	echo "FILE_NOT_EXISTS";
}

?>