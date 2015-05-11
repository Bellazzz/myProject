<?
$fso = new COM('Scripting.FileSystemObject');
foreach ($fso->Drives as $drive) {
        // var_dump($drive->DriveLetter);
		$dir = $drive->DriveLetter.':';
        if(is_dir($dir) && !is_empty_dir($dir)) {
        	echo $dir;
        }

}
// var_dump(is_dir('G:'));
// var_dump(is_dir('a_file.txt'));
// var_dump(is_dir('bogus_dir/abc'));
function is_empty_dir($dir)
{
    if (($files = @scandir($dir)) && count($files) > 1) {
        return false;
    } else {
    	return true;
    }
    
}

// function echo_win_drives() {

//   for($c='A'; $c<='Z'; $c++) 
//     if(is_dir($c . ':')) {
//     	try{
//     		$dh = opendir($c . ':');
//     		echo $c . ': '; 
//     	} catch(Exception $e) {

//     	}
//     }
// }



?>