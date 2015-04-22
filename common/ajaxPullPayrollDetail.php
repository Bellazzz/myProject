<?php
include('../config/config.php');
include('../common/common_header.php');

$response = array(
	'emp_salary' => 0,
	'sumCommission' => 0,
	'sumOvertime' => 0
);

if(hasValue($_POST['emp_id']) && hasValue($_POST['payroll_monthly'])) {
	$emp_id = $_POST['emp_id'];
	$payroll_monthly = $_POST['payroll_monthly'];
	$monthly = str_replace('/','-', substr($payroll_monthly, 0, 7));

	// Find employee salary
	$sql = "SELECT 	emp_salary 
			FROM 	employees 
			WHERE 	emp_id = '$emp_id'";
	$result = mysql_query($sql, $dbConn);
	$rows 	= mysql_num_rows($result);
	if($rows > 0) {
		for($i=0; $i<$rows; $i++) {
			$record = mysql_fetch_assoc($result);
			$response['emp_salary'] = $record['emp_salary'];
		}
	}

	// FInd commission package
	$sql = "SELECT 		sum(pkgdtl.pkgdtl_com) sumCom 
			FROM  		package_details pkgdtl,
						service_service_list_times st, 
						service_packages sp, 
						services s 
			WHERE 		pkgdtl.sersvt_id = st.sersvt_id AND 
						st.serpkg_id = sp.serpkg_id AND 
						sp.ser_id = s.ser_id AND 
						ser_date like '$monthly%' AND 
						pkgdtl.emp_id = '$emp_id'";
	$result = mysql_query($sql, $dbConn);
	$rows 	= mysql_num_rows($result);
	if($rows > 0) {
		for($i=0; $i<$rows; $i++) {
			$record = mysql_fetch_assoc($result);
			$response['sumCommission'] += $record['sumCom'];
		}
	}

	// FInd commission service list
	$sql = "SELECT 		sum(sd.svldtl_com) sumCom 
			FROM 		service_list_details sd, 
						service_service_lists ss, 
						services s 
			WHERE 		sd.sersvl_id = ss.sersvl_id AND 
						ss.ser_id = s.ser_id AND 
						ser_date like '$monthly%' AND 
						sd.emp_id = '$emp_id'";
	$result = mysql_query($sql, $dbConn);
	$rows 	= mysql_num_rows($result);
	if($rows > 0) {
		for($i=0; $i<$rows; $i++) {
			$record = mysql_fetch_assoc($result);
			$response['sumCommission'] += $record['sumCom'];
		}
	}
} 
echo json_encode($response);
?>