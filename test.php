<?
include('config/config.php');
include('common/common_header.php');

$sql = "SELECT * FROM test";
$result = mysql_query($sql, $dbConn);
$rows = mysql_num_rows($result);

for($i=0; $i<$rows; $i++) {
	$record = mysql_fetch_array($result);
	foreach($record as $field => $value) {
	echo $value.' '.gettype($value)."<br>";
}
}

?>