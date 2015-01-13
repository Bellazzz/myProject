<?php
include('../config/config.php');
include('../common/common_header.php');

$emp_id = '';
if (hasValue($_POST['emp_id'])) {
	$emp_id = $_POST['emp_id'];
}

$sql = "SELECT 	e.emp_name, 
				e.emp_surname,
				e.emp_pic,
				p.pos_name 
		FROM 	employees e, 
				positions p 
		WHERE 	p.pos_id = e.pos_id AND 
				e.emp_id = '$emp_id' ";
echo $sql;

?>