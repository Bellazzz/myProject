<?php
date_default_timezone_set('Asia/Bangkok');
header("Content-type: text/html; charset=utf-8;");
header("Content-Disposition: attachment; filename='db-projectSpa-".date('dm').((int)date('Y')+543).'-'.date('His').'.sql');
require('../config/config.php');
require('../common/common_constant.php');

// Export all data
if(isset($_POST['createDB']) && $_POST['createDB'] == '1') {
	// add create database
	$fileSQL = backup_tables(DB_HOST,DB_USERNAME,DB_PASSWORD,DB_NAME, '*', true);
} else {
	$fileSQL = backup_tables(DB_HOST,DB_USERNAME,DB_PASSWORD,DB_NAME, '*');
}


echo $fileSQL;

/* backup the db OR just a table */
function backup_tables($host,$user,$pass,$name,$tables = '*', $createDB = false) {
	$link = mysql_connect($host,$user,$pass);
	mysql_select_db($name,$link);
	mysql_query("SET NAMES UTF8", $link);
	
	//get all of the tables
	if($tables == '*') {
		$tables = array();
		$result = mysql_query('SHOW TABLES');
		while($row = mysql_fetch_row($result)) {
			$tables[] = $row[0];
		}
	}
	else {
		$tables = is_array($tables) ? $tables : explode(',',$tables);
	}

	$return.= "SET AUTOCOMMIT = 0;\nSET FOREIGN_KEY_CHECKS=0;\n\n";

	//create database
	if($createDB) {
		$return .= "DROP DATABASE IF EXISTS  `".DB_NAME."`;\n";
		$return .= "CREATE DATABASE  `".DB_NAME."` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;\n";
		$return .= "USE `".DB_NAME."`;\n\n";
	}

	//cycle through
	foreach($tables as $table) {
		$result = mysql_query('SELECT * FROM '.$table);
		$num_fields = mysql_num_fields($result);
		
		
		$row2 = mysql_fetch_row(mysql_query('SHOW CREATE TABLE '.$table));
		$row2 = str_replace('CREATE TABLE', 'CREATE TABLE IF NOT EXISTS', $row2);
		$return.= "\n\n".$row2[1].";\n\n";
		
		for ($i = 0; $i < $num_fields; $i++)  {
			while($row = mysql_fetch_row($result)) {
				$return.= 'INSERT INTO '.$table.' VALUES(';
				for($j=0; $j<$num_fields; $j++) {
					if(is_null($row[$j])) {
						$return .= 'NULL';
					} else {
						$row[$j] = addslashes($row[$j]);
						$row[$j] = ereg_replace("\n","\\n",$row[$j]);
						if (isset($row[$j])) { $return.= '"'.$row[$j].'"' ; } else { $return.= '""'; }
					}
					if ($j<($num_fields-1)) { $return.= ','; }
				}
				$return.= ");\n";
			}
		}
		$return.="\n\n\n";
	}
	$return.= "\n\nSET FOREIGN_KEY_CHECKS = 1;\nCOMMIT;\nSET AUTOCOMMIT = 1;";
	
	//save file
	// $handle = fopen('db-backup-'.time().'-'.(md5(implode(',',$tables))).'.sql','w+');
	// fwrite($handle,$return);
	// fclose($handle);
	return $return;
}
?>
