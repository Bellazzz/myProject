<?php
	include('class_database.php');
	include('common_function.php');
	require(WEB_ROOTDIR.'/inc/Smarty-3.1.18/libs/Smarty.class.php');

	$smarty = new Smarty();
	$smarty->setTemplateDir($subDir.'template');//html
	$smarty->setCompileDir($subDir.'template_c');//file compile
	
	date_default_timezone_set('Asia/Bangkok');
	$nowDate 		= date('Y/m/d');
	$nowTime 		= date('H:i');
	$nowTimeFull 	= date('H:i:s');
	$smarty->assign('nowYear', date('Y'));
	$smarty->assign('nowMonth', date('m'));
	$smarty->assign('nowDate', date('Y/m/d'));
	$smarty->assign('nowDate_th', dateThaiFormat(date('Y/m/d')));
	$smarty->assign('nowTime', date('H:i'));
	$smarty->assign('nowTimeFull', date('H:i:s'));

	// Session login employee
	if(isset($_SESSION['loggedin'])) {
		$smarty->assign('session_loggedin', $_SESSION['loggedin']);
	}
	if(isset($_SESSION['emp_id'])) {
		$session_emp_id = $_SESSION['emp_id'];
		$smarty->assign('session_emp_id', $session_emp_id);
	}
	if(isset($_SESSION['emp_email'])) {
		$smarty->assign('session_emp_user', $_SESSION['emp_email']);
	}
	if(isset($_SESSION['emp_name'])) {
		$smarty->assign('session_emp_name', $_SESSION['emp_name']);
	}
	if(isset($_SESSION['emp_surname'])) {
		$smarty->assign('session_emp_surname', $_SESSION['emp_surname']);
	}

	// Session & Cookie login customer
	if(isset($_COOKIE['cusRememberMe'])) {
		$_SESSION['cusLogin'] = $_COOKIE['cusRememberMe'];
	}
	if(isset($_COOKIE['cus_id'])) {
		$_SESSION['cus_id'] = $_COOKIE['cus_id'];
	}
	if(isset($_COOKIE['cus_email'])) {
		$_SESSION['cus_email'] = $_COOKIE['cus_email'];
	}
	if(isset($_COOKIE['cus_fullname'])) {
		$_SESSION['cus_fullname'] = $_COOKIE['cus_fullname'];
	}

	if(isset($_SESSION['cusLogin'])) {
		$smarty->assign('session_cusLogin', $_SESSION['cusLogin']);
	}
	if(isset($_SESSION['cus_id'])) {
		$session_cus_id = $_SESSION['cus_id'];
		$smarty->assign('session_cus_id', $session_cus_id);
	}
	if(isset($_SESSION['cus_email'])) {
		$smarty->assign('session_cus_email', $_SESSION['cus_email']);
	}
	if(isset($_SESSION['cus_fullname'])) {
		$smarty->assign('session_cus_fullname', $_SESSION['cus_fullname']);
	}

	//Get data to show
	$spaRecord 	= new TableSpa('spa', 'SA01');
	$spaData 	= array(
		'spa_name' 	=> $spaRecord->getFieldValue('spa_name'),
		'spa_addr' 	=> $spaRecord->getFieldValue('spa_addr'),
		'spa_tel' 	=> $spaRecord->getFieldValue('spa_tel'),
		'spa_fax' 	=> $spaRecord->getFieldValue('spa_fax'),
		'spa_email' => $spaRecord->getFieldValue('spa_email')
	);
	// Check null
	$spaData['spa_fax'] 	= $spaData['spa_fax'] == '' ? '-' : $spaData['spa_fax'];
	$spaData['spa_email'] 	= $spaData['spa_email'] == '' ? '-' : $spaData['spa_email'];

	$smarty->assign('spaData', $spaData);

	/*
	 * Get Privileges
	 */
	$emp_privileges = array(
		'view_withdraws' 				=> false,
		'insert_withdraws' 				=> false,
		'update_withdraws' 				=> false,
		'delete_withdraws' 				=> false,

		'view_sales' 					=> false,
		'insert_sales' 					=> false,
		'update_sales' 					=> false,
		'delete_sales' 					=> false,
		'print_sales_receipt'			=> false,

		'manage_standard_tables'		=> false,
		'point_of_sale'					=> false,
		'manage_website' 				=> false,

		'manage_orders' 				=> false,
		'manage_services' 				=> false,
		'manage_booking' 				=> false,
		'manage_employees' 				=> false,
		'manage_customers' 				=> false,
		'manage_spa' 					=> false,
		'manage_shops' 					=> false,
		'manage_packages' 				=> false,
		'manage_service_lists' 			=> false,
		'manage_companies'				=> false,
		'manage_products' 				=> false,
		'manage_promotion_discout_sales'=> false,
		'manage_advertising' 			=> false,
		'manage_time_attendances' 		=> false,
		'manage_payrolls' 				=> false,
		'manage_element_checks' 		=> false,
		'manage_receives' 				=> false,
		'view_report' 					=> false
	);
	if(isset($session_emp_id)) {
		$sql = "SELECT  	p.privlg_name 
				FROM 		privileges p, 
							grant_privileges gp 
				WHERE 		p.privlg_id = gp.privlg_id AND 
							gp.emp_id = '$session_emp_id'";
		$result = mysql_query($sql, $dbConn);
		$rows 	= mysql_num_rows($result);
		if($rows > 0) {
			for($i=0; $i<$rows; $i++) {
				$record = mysql_fetch_assoc($result);
				$emp_privileges[$record['privlg_name']] = true;
			}
			$smarty->assign("emp_privileges", $emp_privileges);
		}
	}

	// Get session shopping cart
	$amountItemInCart = 0;
	$totalPriceInCart = 0;
	if(isset($_SESSION['bookingPkg'])) {
		$smarty->assign("session_bookingPkg", $_SESSION['bookingPkg']);
		$amountItemInCart += count($_SESSION['bookingPkg']);
		foreach ($_SESSION['bookingPkg'] as $key => $pkg) {
			$totalPriceInCart += $pkg['persons'] * $pkg['pkg_price'];
		}
		
	}
	if(isset($_SESSION['bookingSvl'])) {
		$smarty->assign("session_bookingSvl", $_SESSION['bookingSvl']);
		$amountItemInCart += count($_SESSION['bookingSvl']);
		foreach ($_SESSION['bookingSvl'] as $key => $svl) {
			$totalPriceInCart += $svl['persons'] * $svl['svl_price'];
		}
	}
	$smarty->assign('amountItemInCart', $amountItemInCart);
	$smarty->assign('totalPriceInCart', $totalPriceInCart);
?>