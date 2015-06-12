<?php
/*
 * Database
 */
define('DB_HOST', 		'localhost');
define('DB_USERNAME', 	'root');
define('DB_PASSWORD', 	'root');
define('DB_NAME', 		'projectSpa');

$monthThai 		= array('มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน','กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม');
$monthThaiMin 	= array('ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.','ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.');

/*
 * Global Connection
 */
$dbConn;
dbConnect();
function dbConnect() {
	global $dbConn;
	$dbConn = mysql_connect(DB_HOST, DB_USERNAME, DB_PASSWORD);
	if($dbConn) {
		if(mysql_select_db(DB_NAME, $dbConn)) {
			// Initial
			mysql_query("SET NAMES UTF8", $dbConn);
		} else {
			throw new Exception('Cannot select database');
		}
	} else {
		throw new Exception('Cannot connect host');
	}
}
function dbClose() {
	global $dbConn;
	return mysql_close($dbConn);
}

/*
 * การใช้ฟังก์ชั่น getTableInfo
 * 1. keyFieldName = ชื่อ primary key
 * 2. keyFieldType = ชนิดของ primary key
 *		(1) = Auto number
 *		(2) = Char running เป็น pk ที่ขึ้นต้นด้วยตัวอักษรและตามด้วยตัวเลข โดยมีการ run ตัวเลขจากน้อยไปมาก เช่น T01, EMP00001 เป็นต้น
 *		(3) = Char running with year เหมือนกับชนิดที่ 2 แต่ลงท้ายด้วย ปี พ.ศ. เช่น OR00001/57, RE00001/57 เป็นต้น
 * 3. keyChar = ตัวอักษรที่ขึ้นต้นของ primary key เช่น ตาราง employees ขึ้นต้นด้วย EM ก็ให้ใส่ 'EM' เป็นต้น
 * 4. keyLength = ขนาดของ primary key ทั้งหมด (รวมตัวอักษร และ ปี พ.ศ. ด้วย) เช่น P01 มีขนาด 3, OR00001/57 มีขนาด 10 เป็นต้น
 * 5. fieldNameList = ชื่อฟิลด์ของตาราง ฝั่งซ้ายเป็นชื่อภาษาอังกฤษ ฝั่งขวาเป็นชื่อภาษาไทย
 */
 function getTableInfo($tableName) {
	 switch($tableName) {
		 case 'titles':
			 return array(
				'tableNameTH'	=> 'คำนำหน้าชื่อ',
				'keyFieldName'  => 'title_id',
				'keyFieldType'	=> 2,
				'keyChar'		=> 'T',
				'keyLength'		=> 3,
				'fieldNameList'	=> array(
					'title_id'		=> 'รหัสคำนำหน้าชื่อ',
					'title_name'	=> 'คำนำหน้าชื่อ',
					'sex_id' 		=> 'เพศ'
				),
				'defaultNull' 	=> array('sex_id'),
				'searchFields'	=> array('title_name'),
				'deleteTxtField'	=> array('title_name'),
				'referenceData'	=> array('sex')
			);
			break;

		case 'sex':
			return array(
				'tableNameTH'	=> 'เพศ',
				'keyFieldName'  => 'sex_id',
				'keyFieldType'	=> 2,
				'keyChar'		=> 'X',
				'keyLength'		=> 2,
				'fieldNameList'	=> array(
					'sex_id'	=> 'รหัสเพศ',
					'sex_name'	=> 'ชื่อเพศ'
				),
				'searchFields'	=> array('sex_name'),
				'deleteTxtField'	=> array('sex_name')
			);
			break;

		case 'withdraw_types':
			return array(
				'tableNameTH'	=> 'ประเภทการเบิก',
				'keyFieldName'  => 'wdwtyp_id',
				'keyFieldType'	=> 2,
				'keyChar'		=> 'WT',
				'keyLength'		=> 3,
				'fieldNameList'	=> array(
					'wdwtyp_id'						=> 'รหัสประเภทการเบิก',
					'wdwtyp_name'					=> 'ชื่อประเภทการเบิก',
					'wdwtyp_increase_shelf_amount' 	=> 'ลักษณะการเบิก'
				),
				'searchFields'	=> array('wdwtyp_name'),
				'deleteTxtField'	=> array('wdwtyp_name'),
				'deleteTxtPatternMain' 	=> 'คุณต้องการลบประเภทการเบิก %f1 ใช่หรือไม่?'
			);
			break;

		case 'order_status':
			return array(
				'tableNameTH'	=> 'สถานะการสั่งซื้อ',
				'keyFieldName'  => 'ordstat_id',
				'keyFieldType'	=> 2,
				'keyChar'		=> 'OS',
				'keyLength'		=> 4,
				'fieldNameList'	=> array(
					'ordstat_id'		=> 'รหัสสถานะการสั่งซื้อ',
					'ordstat_name'		=> 'ชื่อสถานะการสั่งซื้อ'
				),
				'searchFields'	=> array('ordstat_name'),
				'deleteTxtField'	=> array('ordstat_name')
			);
			break;
				
		case 'positions':
			 return array(
				'tableNameTH'	=> 'ตำแหน่ง',
				'keyFieldName'  => 'pos_id',
				'keyFieldType'	=> 2, 
				'keyChar'		=> 'P',
				'keyLength'		=> 3,
				'fieldNameList'	=> array(
						'pos_id'		=> 'รหัสตำแหน่ง',
						'pos_name'		=> 'ชื่อตำแหน่ง',
						'otr_id' 		=> 'อัตราการจ่ายค่าล่วงเวลา'
				),
				'searchFields'	=> array('pos_name','otr_id'),
				'defaultNull' 	=> array('otr_id'),
				'deleteTxtField'=> array('pos_name'),
				'referenceData' => array('overtime_rates')
			);
			break;

		case 'employees':
			 return array(
				'tableNameTH'	=> 'พนักงาน',
				'keyFieldName'  => 'emp_id',
				'keyFieldType'	=> 2,
				'keyChar'		=> 'EM',
				'keyLength'		=> 6,
				'fieldNameList'	=> array(
						'emp_id'		=> 'รหัสพนักงาน',
						'sex_id' 		=> 'เพศ',
						'title_id'	    => 'คำนำหน้า',
						'pos_id'		=> 'ตำแหน่ง',
						'emp_name'		=> 'ชื่อ',
						'emp_surname'	=> 'นามสกุล',
						'emp_birthdate' => 'วันเกิด',
						'emp_addr'		=> 'ที่อยู่',
						'emp_tel'		=> 'เบอร์โทร',
						'emp_indate'	=> 'วันที่เข้าทำงาน',
						'emp_salary'	=> 'ฐานเงินเดือน',
						'emp_email'		=> 'E-mail/Username',
						'emp_pass'		=> 'Password',
						'emp_pic'		=> 'รูปภาพ'
				),
				'hiddenFields'	=> array('emp_indate','emp_birthdate','emp_addr','emp_tel','emp_email','emp_pass','emp_pic','emp_salary'),
				'defaultNull' 	=> array('emp_pic','emp_birthdate','emp_email','emp_pass'),
				'searchFields'	=> array('emp_name','emp_surname','title_id','sex_id','pos_id'),
				'deleteTxtField'	 	=> array('title_id','emp_name','emp_surname'),
				'deleteTxtPatternMain' 	=> 'คุณต้องการลบข้อมูลพนักงานของ %f1%f2 %f3 ใช่หรือไม่?',
				'deleteTxtPatternMin' 	=> '%f1%f2 %f3',
				'referenceData'			=> array('titles','sex','positions')
			);
			break;
		
		case 'units':
			 return array(
				'tableNameTH'	=> 'หน่วยนับ',
				'keyFieldName'  => 'unit_id',
				'keyFieldType'	=> 2, 
				'keyChar'		=> 'U',
				'keyLength'		=> 3,
				'fieldNameList'	=> array(
						'unit_id'		=> 'รหัสหน่วยนับ',
						'unit_name'		=> 'หน่วยนับ'
				),  
				'searchFields'	=> array('unit_name'),
				'deleteTxtField'=> array('unit_name')
			);
			break;

		case 'beds':
			 return array(
				'tableNameTH'	=> 'เตียงนวด',
				'keyFieldName'  => 'bed_id',
				'keyFieldType'	=> 2, 
				'keyChar'		=> 'BE',
				'keyLength'		=> 4,
				'fieldNameList'	=> array(
						'bed_id'		=> 'รหัสเตียงนวด',
						'room_id'		=> 'ห้องนวด',
						'bed_name'		=> 'เตียงนวด'
				),
				'searchFields'	=> array('bed_name','room_id'),
				'deleteTxtField'=> array('bed_name'),
				'referenceData'	=> array('rooms')
			);
			break;

		case 'bank_accounts':
			 return array(
				'tableNameTH'	=> 'บัญชีธนาคาร',
				'keyFieldName'  => 'bnkacc_id',
				'keyFieldType'	=> 2, 
				'keyChar'		=> 'BA',
				'keyLength'		=> 4,
				'fieldNameList'	=> array(
						'bnkacc_id'		=> 'รหัสบัญชีธนาคาร',
						'bnkacc_no'		=> 'เลขบัญชี',
						'bnkacc_name'	=> 'ชื่อบัญชี',
						'bnkacc_bank'	=> 'ชื่อธนาคาร',
						'bnkacc_branch' => 'สาขา',
						'bnkacc_type'	=> 'ประเภทบัญชี',
						'bnkacc_status' => 'สถานะ'
				),
				'searchFields'	=> array('bnkacc_no','bnkacc_name','bnkacc_bank','bnkacc_type'),
				'deleteTxtField'=> array('bnkacc_name')
			);
			break;

		case 'rooms':
			 return array(
				'tableNameTH'	=> 'ห้องนวด',
				'keyFieldName'  => 'room_id',
				'keyFieldType'	=> 2, 
				'keyChar'		=> 'RM',
				'keyLength'		=> 4,
				'fieldNameList'	=> array(
						'room_id'		=> 'รหัสห้องนวด',
						'room_name'		=> 'ห้องนวด'	
				),
				'searchFields'	=> array('room_name'),
				'deleteTxtField'=> array('room_name')
			);
			break;

		case 'booking_status':
			 return array(
				'tableNameTH'	=> 'สถานะการจอง',
				'keyFieldName'  => 'bkgstat_id',
				'keyFieldType'	=> 2, 
				'keyChar'		=> 'S',
				'keyLength'		=> 3,
				'fieldNameList'	=> array(
						'bkgstat_id'		=> 'รหัสสถานะการจอง',
						'bkgstat_name'		=> 'สถานะการจอง'
				),
				'searchFields'	=> array('bkgstat_name'),
				'deleteTxtField'=> array('bkgstat_name')
			);
			break;

		case 'brands':
			 return array(
				'tableNameTH'	=> 'ยี่ห้อผลิตภัณฑ์',
				'keyFieldName'  => 'brand_id',
				'keyFieldType'	=> 2, 
				'keyChar'		=> 'BR',
				'keyLength'		=> 4,
				'fieldNameList'	=> array(
						'brand_id'		=> 'รหัสยี่ห้อ',
						'brand_name'	=> 'ยี่ห้อ'
				),
				'searchFields'	=> array('brand_name'),
				'deleteTxtField'=> array('brand_name')
			);
			break;

		case 'pay_types':
			 return array(
				'tableNameTH'	=> 'ประเภทการชำระเงิน',
				'keyFieldName'  => 'paytyp_id',
				'keyFieldType'	=> 2, 
				'keyChar'		=> 'PA',
				'keyLength'		=> 4,
				'fieldNameList'	=> array(
						'paytyp_id'		=> 'รหัสประเภทการชำระเงิน',
						'paytyp_name'	=> 'ประเภทการชำระเงิน'
				),
				'searchFields'	=> array('paytyp_name'),
				'deleteTxtField'=> array('paytyp_name')
			);
			break;

		case 'element_types':
			 return array(
				'tableNameTH'	=> 'ประเภทธาตุ',
				'keyFieldName'  => 'eletyp_id',
				'keyFieldType'	=> 2, 
				'keyChar'		=> 'ET',
				'keyLength'		=> 4,
				'fieldNameList'	=> array(
						'eletyp_id'		=> 'รหัสประเภทธาตุ',
						'eletyp_name'	=> 'ประเภทธาตุ'
				),
				'searchFields'	=> array('eletyp_name'),
				'deleteTxtField'=> array('eletyp_name')
			);
			break;

		case 'product_types':
			 return array(
				'tableNameTH'	=> 'ประเภทผลิตภัณฑ์',
				'keyFieldName'  => 'prdtyp_id',
				'keyFieldType'	=> 2, 
				'keyChar'		=> 'PT',
				'keyLength'		=> 4,
				'fieldNameList'	=> array(
						'prdtyp_id'		=> 'รหัสประเภทผลิตภัณฑ์',
						'prdtyp_name'	=> 'ประเภทผลิตภัณฑ์',
						'prdtyp_group' 	=> 'กลุ่มประเภทผลิตภัณฑ์'
				),
				'searchFields'	=> array('prdtyp_name','prdtyp_group'),
				'deleteTxtField'=> array('prdtyp_name'),
				'optionData'    => array('prdtyp_group')
			);
			break;

		case 'order_types':
			 return array(
				'tableNameTH'	=> 'ประเภทการสั่งซื้อ',
				'keyFieldName'  => 'ordtyp_id',
				'keyFieldType'	=> 2, 
				'keyChar'		=> 'OT',
				'keyLength'		=> 3,
				'fieldNameList'	=> array(
						'ordtyp_id'		=> 'รหัสประเภทการสั่งซื้อ',
						'ordtyp_name'	=> 'ประเภทการสั่งซื้อ'
				),
				'searchFields'	=> array('ordtyp_name'),
				'deleteTxtField'=> array('ordtyp_name')
			);
			break;

		case 'service_list_types':
			 return array(
				'tableNameTH'	=> 'ประเภทรายการบริการ',
				'keyFieldName'  => 'svltyp_id',
				'keyFieldType'	=> 2, 
				'keyChar'		=> 'ST',
				'keyLength'		=> 4,
				'fieldNameList'	=> array(
						'svltyp_id'		=> 'รหัสประเภทรายการบริการ',
						'svltyp_name'	=> 'ประเภทรายการบริการ'
				),
				'searchFields'	=> array('svltyp_name'),
				'deleteTxtField'=> array('svltyp_name')
			);
			break;

		case 'customer_types':
			 return array(
				'tableNameTH'	=> 'ประเภทผู้ใช้บริการ',
				'keyFieldName'  => 'custype_id',
				'keyFieldType'	=> 2, 
				'keyChar'		=> 'CT',
				'keyLength'		=> 3,
				'fieldNameList'	=> array(
						'custype_id'		=> 'รหัสประเภทผู้ใช้บริการ',
						'custype_name'		=> 'ประเภทผู้ใช้บริการ'
				),
				'searchFields'	=> array('custype_name'),
				'deleteTxtField'=> array('custype_name')
			);
			break;

		case 'spa':
			 return array(
				'tableNameTH'	=> 'สปา',
				'keyFieldName'  => 'spa_id',
				'keyFieldType'	=> 2, 
				'keyChar'		=> 'SA',
				'keyLength'		=> 4,
				'fieldNameList'	=> array(
						'spa_id'		=> 'รหัสสปา',
						'spa_name'		=> 'ชื่อสปา',
						'spa_addr'		=> 'ที่อยู่',
						'spa_tel'		=> 'เบอร์โทรศัพท์',
						'spa_fax'		=> 'แฟ็กซ์',
						'spa_logo'		=> 'รูปภาพ',
						'spa_email'		=> 'E-mail'
				),
				'hiddenFields'	=> array('spa_addr','spa_logo'),
				'searchFields'	=> array('spa_name'),
				'deleteTxtField'=> array('spa_name')

			);
			break;

		case 'customers':
			 return array(
				'tableNameTH'	=> 'ผู้ใช้บริการ',
				'keyFieldName'  => 'cus_id',
				'keyFieldType'	=> 2, 
				'keyChar'		=> 'CM',
				'keyLength'		=> 8,
				'fieldNameList'	=> array(
						'cus_id'	    		=> 'รหัสผู้ใช้บริการ',
						'sex_id' 				=> 'เพศ',
						'custype_id'			=> 'ประเภทผู้ใช้บริการ',
						'title_id'      		=> 'คำนำหน้าชื่อ',
						'cus_name'      		=> 'ชื่อ',
						'cus_surname'   		=> 'นามสกุล',
						'cus_addr'      		=> 'ที่อยู่',
						'cus_tel'       		=> 'เบอร์โทรศัพท์',
						'cus_email'      		=> 'Email/ชื่อผู้ใช้',
						'cus_pass'      		=> 'รหัสผ่าน',
						'cus_birthdate' 		=> 'วันที่เกิด',
						'cus_registered_date'   => 'วันที่สมัคร',
						'cus_facebook'  		=> 'facebook',
						'cus_line_id'   		=> 'Line'  
				),
				'hiddenFields'	=> array('cus_tel','cus_addr','cus_pass','cus_birthdate','cus_registered_date',
					'cus_facebook','cus_line_id','cus_email'),
				'defaultNull' 	=> array('cus_pass','cus_birthdate','cus_facebook','cus_line_id','cus_email'),
				'searchFields'	=> array('cus_name','cus_surname','title_id','sex_id','custype_id'),
				'deleteTxtField' 		=> array('title_id','cus_name','cus_surname'),
				'deleteTxtPatternMain' 	=> 'คุณต้องการลบข้อมูลผู้ใช้บริการของ %f1%f2 %f3 ใช่หรือไม่',
				'deleteTxtPatternMin' 	=> '%f1%f2 %f3',
				'referenceData'			=> array('titles','sex','customer_types')
			);
			break;

		case 'service_lists':
			 return array(
				'tableNameTH'	=> 'รายการบริการ',
				'keyFieldName'  => 'svl_id',
				'keyFieldType'	=> 2, 
				'keyChar'		=> 'SL',
				'keyLength'		=> 5,
				'fieldNameList'	=> array(
						'svl_id'			=> 'รหัสรายการบริการ',
						'svltyp_id'			=> 'ประเภทรายการบริการ',
						'svl_min'			=> 'เวลาที่ใช้(นาที)',
						'svl_hr'			=> 'เวลาที่ใช้(ชั่วโมง)',
						'svl_name'			=> 'รายการบริการ',
						'svl_price'			=> 'ราคา(บาท)',
						'svl_desc'			=> 'คำอธิบาย',
						'svl_picture'		=> 'รูปภาพ',
						'svl_start'			=> 'วันที่เริ่มใช้',
						'svl_stop' 			=> 'วันที่สิ้นสุด',
						'svl_type' 			=> 'รูปแบบ'
				),
				'hiddenFields'	=> array('svl_desc','svl_picture','svl_hr','svl_start','svl_stop','svl_type'),
				'defaultNull' 	=> array('svl_min','svl_hr','svl_desc','svl_picture','svl_stop'),
				'searchFields'	=> array('svl_name','svl_min','svl_price'),
				'deleteTxtField' 	=> array('svl_name'),
				'referenceData'		=> array('service_list_types')
			
			);
			break;

		case 'packages':
			 return array(
				'tableNameTH'	=> 'แพ็คเกจ',
				'keyFieldName'  => 'pkg_id',
				'keyFieldType'	=> 2, 
				'keyChar'		=> 'PK',
				'keyLength'		=> 5,
				'fieldNameList'	=> array(
						'pkg_id'	    => 'รหัสแพ็คเกจ',
						'pkg_name'	    => 'ชื่อแพ็คเกจ',
						'pkg_price'     => 'ราคา(บาท)',
						'pkg_start'     => 'วันที่เริ่มใช้',
						'pkg_stop'      => 'วันที่สิ้นสุด',
						'pkg_desc'      => 'คำอธิบาย',
						'pkg_picture'   => 'รูปภาพ'			
				),
				'hiddenFields'	=> array('pkg_desc','pkg_picture'),
				'defaultNull' 	=> array('pkg_stop','pkg_desc','pkg_picture'),
				'searchFields'	=> array('pkg_name','pkg_price','pkg_start','pkg_stop'),
				'deleteTxtField' 	=> array('pkg_name'),
				'referenceData'		=> array('service_lists')
			);
			break;

		case 'package_service_lists':
			 return array(
				'tableNameTH'	=> 'รายการบริการที่จัดแพ็คเกจ',
				'keyFieldName'  => 'pkgsvl_id',
				'keyFieldType'	=> 2, 
				'keyChar'		=> 'PS',
				'keyLength'		=> 8,
				'fieldNameList'	=> array(
						'pkgsvl_id'	    => 'รหัสรายการบริการที่จัดแพ็คเกจ',
						'svl_id'	    => 'รายการบริการ',
						'pkg_id'        => 'แพ็คเกจ',
						'pkgsvl_hr' 		=> 'เวลาที่ใช้(ชั่วโมง)',
						'pkgsvl_min' 		=> 'เวลาที่ใช้(นาทำ)',
						'pkgsvl_price' 	=> 'ราคา'
				)
			);
			break;

		case 'promotions':
			 return array(
				'tableNameTH'	=> 'โปรโมชั่น',
				'keyFieldName'  => 'prm_id',
				'keyFieldType'	=> 2, 
				'keyChar'		=> 'PM',
				'keyLength'		=> 6,
				'fieldNameList'	=> array(
						'prm_id'			=> 'รหัสโปรโมชั่น',
						'prm_name'			=> 'โปรโมชั่น',
						'prm_use_amount'	=> 'จำนวนครั้งที่ใช้บริการ',
						'prm_free_amount'	=> 'จำนวนครั้งที่ฟรี',
						'prm_startdate'	    => 'วันที่เริ่มใช้',
						'prm_enddate'		=> 'วันที่สิ้นสุด',
						'prm_picture'		=> 'รูปภาพ'
				),
				'hiddenFields'	=> array('prm_picture'),
				'defaultNull' 	=> array('prm_use_amount','prm_free_amount','prm_enddate','prm_picture'),
				'searchFields'	=> array('prm_name'),
				'deleteTxtField' 	=> array('prm_name')
			);
			break;

		case 'companies':
			 return array(
				'tableNameTH'	=> 'บริษัทจำหน่าย',
				'keyFieldName'  => 'comp_id',
				'keyFieldType'	=> 2, 
				'keyChar'		=> 'CP',
				'keyLength'		=> 4,
				'fieldNameList'	=> array(
						'comp_id'	      => 'รหัสบริษัทจำหน่าย',
						'comp_name'	      => 'ชื่อบริษัท',
						'fax'             => 'โทรสาร',
						'comp_tel'	      => 'เบอร์โทรศัพท์',
						'comp_email'	  => 'E-mail',
						'comp_addr'	      => 'ที่อยู่',
						'comp_contact'    => 'ผู้ติดต่อ'
				),
				'hiddenFields'	=> array('fax','comp_tel','comp_addr','comp_contact','comp_email'),
				'defaultNull' 	=> array('fax','comp_contact','comp_email'),
				'searchFields'	=> array('comp_name'),
				'deleteTxtField' 		=> array('comp_name')
			);
			break;

		case 'products':
			 return array(
				'tableNameTH'	=> 'ผลิตภัณฑ์',
				'keyFieldName'  => 'prd_id',
				'keyFieldType'	=> 2, 
				'keyChar'		=> 'PD',
				'keyLength'		=> 6,
				'fieldNameList'	=> array(
						'prd_id'	      => 'รหัสผลิตภัณฑ์',
						'prdtyp_id'	      => 'ประเภทผลิตภัณฑ์',
						'unit_id'         => 'หน่ายนับ',
						'brand_id'	      => 'ยี่ห้อ',
						'prd_name'	      => 'ผลิตภัณฑ์',
						'prd_price'       => 'ราคา(บาท)',
						'prd_amount'      => 'จำนวนคงเหลือ',
						'prd_shelf_amount'=> 'จำนวนที่วางขาย',
						'prd_pic'      	  => 'รูปภาพ',
						'prd_desc'		  => 'คำอธิบาย',
						'prd_barcode'	  => 'รหัสบาร์โค้ด',
						'prd_amount_max'  => 'จำนวนคงเหลือสูงสุด',
						'prd_amount_min'  => 'จำนวนคงเหลือน้อยสุด'
				),
				'hiddenFields'	=> array('prd_pic','prd_amount','unit_id','prd_desc','prd_barcode','prd_amount_max','prd_amount_min'),
				'defaultNull' 	=> array('prd_amount','prd_desc','prd_barcode','prd_amount_max','prd_amount_min'),
				'searchFields'	=> array('prd_name','prdtyp_id','brand_id','prd_barcode'),
				'deleteTxtField' 		=> array('prd_name'),
				'referenceData'			=> array('product_types', 'brands', 'units')
			);
			break;

		case 'time_attendances':
			 return array(
				'tableNameTH'	=> 'การเข้า-ออกงาน',
				'keyFieldName'  => 'timeatt_id',
				'keyFieldType'	=> 2, 
				'keyChar'		=> 'W',
				'keyLength'		=> 8,
				'fieldNameList'	=> array(
						'timeatt_id'	  => 'รหัสการเข้าออกงาน',
						'emp_id'	      => 'ชื่อ-นามสกุลพนักงาน',
						'dateatt_in'      => 'วันที่เข้า-ออกงาน',
						'timeatt_in'      => 'เวลาที่เข้า',
						'dateatt_out'	  => 'วันที่ออก',
						'timeatt_out'	  => 'เวลาที่ออก'
				),
				'defaultNull' 	=> array('timeatt_out','dateatt_out'),
				'searchFields'	=> array('emp_id','dateatt_in','dateatt_out'),
				'deleteTxtField'	=> array('dateatt_in','emp_id'),
				'deleteTxtPatternMain' 	=> 'คุณต้องการลบการเข้า-ออกงานวันที่ %f1 ของ %f2 ใช่หรือไม่?',
				'deleteTxtPatternMin' 	=> 'วันที่ %f1 ของ %f2',
				'referenceData'			=> array('employees')
			);
			break;

		case 'payrolls':
			 return array(
				'tableNameTH'	=> 'การจ่ายเงินเดือน',
				'keyFieldName'  => 'payroll_id',
				'keyFieldType'	=> 2, 
				'keyChar'		=> 'PR',
				'keyLength'		=> 7,
				'fieldNameList'	=> array(
						'payroll_id'			=> 'รหัสการจ่ายเงินเดือน',
						'emp_id'				=> 'พนักงานที่ได้รับเงินเดือน',
						'emp_give_id'			=> 'พนักงานที่จ่ายเงินเดือน',
						'payroll_salary'		=> 'จำนวนเงินทั้งหมด(บาท)',
						'payroll_commission'	=> 'ค่าคอมมิชชั่น(บาท)',
						'payroll_overtime'		=> 'ค่าล่วงเวลา(บาท)',
						'payroll_monthly'		=> 'ประจำเดือน-ปี',
						'payroll_date'			=> 'วันที่จ่ายเงินเดือน'
				),
				'hiddenFields'	=> array('payroll_commission','payroll_overtime','emp_give_id'),
				'searchFields'	=> array('emp_id','payroll_monthly','payroll_salary'),
				'deleteTxtField'	=> array('emp_id','payroll_monthly'),
				'deleteTxtPatternMain' 	=> 'คุณต้องการลบการจ่ายเงินเดือนของ %f1 ประจำเดือน %f2 ใช่หรือไม่?',
				'deleteTxtPatternMin' 	=> '%f1 ประจำเดือน %f2',
				'referenceData'			=> array('employees'),
				'optionData' => array('payroll_year', 'payroll_month')
			);
			break;

		case 'booking':
			 return array(
				'tableNameTH'	=> 'การจอง',
				'keyFieldName'  => 'bkg_id',
				'keyFieldType'	=> 3, 
				'keyChar'		=> 'BK',
				'keyLength'		=> 15,
				'fieldNameList'	=> array(
						'bkg_id'	  			  => 'รหัสการจอง',
						'cus_id'	        	  => 'ผู้ใช้บริการ',
						'emp_id' 		    	  => 'พนักงานที่รับการจอง',
						'status_id'         	  => 'สถานะการจอง',
						'bnkacc_id'	        	  => 'เลขบัญชีธนาคาร',
						'bkg_transfer_date'  	  => 'วันที่โอน',
						'bkg_transfer_time'  	  => 'เวลาที่โอน',
						'bkg_transfer_evidence'   => 'หลักฐานการโอน',
						'bkg_total_price'         => 'ราคารวมการจองทั้งหมด(บาท)',
						'bkg_date'    			  => 'วันที่จอง',
						'bkg_time'    			  => 'เวลาที่จอง',
						'bkg_transfer_money'      => 'จำนวนเงินที่โอน(บาท)'
				),
				'hiddenFields'	=> array('bnkacc_id','bkg_transfer_date','bkg_transfer_time','bkg_transfer_evidence','bkg_total_price','bkg_transfer_money','bkg_time'),
				'defaultNull' 	=> array('emp_id','bkg_transfer_date','bkg_transfer_time','bkg_transfer_evidence','bkg_transfer_money','bnkacc_id'),
				'searchFields'	=> array('bkg_id','cus_id','emp_id','status_id','bkg_total_price','bkg_date','bkg_time'),
				'deleteTxtField'	=> array('cus_id','bkg_date'),
				'deleteTxtPatternMain' 	=> 'คุณต้องการลบการจองของ %f1 ในวันที่ %f2 ใช่หรือไม่?',
				'deleteTxtPatternMin' 	=> '%f1 จองวันที่ %f2',
				'referenceData'			=> array('employees','employees_service','customers','customer_types','booking_status','bank_accounts','packages','service_lists')
			);
			break;

		case 'booking_service_lists':
			 return array(
				'tableNameTH'	=> 'รายละเอียดรายการบริการที่จอง',
				'keyFieldName'  => 'bkgsvl_id',
				'keyFieldType'	=> 2, 
				'keyChar'		=> 'BL',
				'keyLength'		=> 8,
				'fieldNameList'	=> array(
						'bkgsvl_id'	  		 => 'รหัสรายละเอียดรายการบริการที่จอง',
						'svl_id'	         => 'รหัสรายการบริการ',
						'bkg_id' 		     => 'รหัสการจอง',
						'emp_id'			 => 'รหัสพนักงาน',
						'bkgsvl_date'        => 'วันที่ใช้บริการ',
						'bkgsvl_time'        => 'เวลาที่ใช้บริการ',
						'bkgsvl_total_price' => 'ราคารวมการจองรายการบริการ(บาท)',
						'bkgsvl_persons'     => 'จำนวนผู้ใช้บริการ'
				),
				'searchFields'	=> array('bkgsvl_id','svl_id','bkg_id'),
				'defaultNull' 	=> array('emp_id')
			);
			break;

		case 'booking_packages':
			 return array(
				'tableNameTH'	=> 'รายละเอียดแพ็คเกจที่จอง',
				'keyFieldName'  => 'bkgpkg_id',
				'keyFieldType'	=> 2, 
				'keyChar'		=> 'BP',
				'keyLength'		=> 14,
				'fieldNameList'	=> array(
						'bkgpkg_id'	  		  => 'รหัสรายละเอียดแพ็คเกจที่จอง',
						'pkg_id'	          => 'รหัสแพ็คเกจ',
						'bkg_id' 		      => 'รหัสการจอง',
						'emp_id'			  => 'พนักงานที่จอง',
						'bkgpkg_date'         => 'วันที่ใช้บริการ',
						'bkgpkg_time'         => 'เวลาที่ใช้บริการ',
						'bkgpkg_total_price'  => 'ราคารวมการจองแพ็คเกจ(บาท)',
						'bkgpkg_persons'      => 'จำนวนผู้ใช้บริการ'
				),
				'searchFields'	=> array('bkgpkg_id','pkg_id','bkg_id'),
				'defaultNull' 	=> array('emp_id')
			);
			break;

		case 'booking_promotions':
			 return array(
				'tableNameTH'	=> 'รายละเอียดโปรโมชั่นที่จอง',
				'keyFieldName'  => 'bkgprm_id',
				'keyFieldType'	=> 2, 
				'keyChar'		=> 'BP',
				'keyLength'		=> 14,
				'fieldNameList'	=> array(
						'bkgprm_id'	  		  => 'รหัสรายละเอียดโปรโมชั่นที่จอง',
						'prmsvl_id'	          => 'รหัสรายการบริการที่จัดโปรโมชั่น',
						'bkg_id' 		      => 'รหัสการจอง',
						'bkgprm_date'         => 'วัน-เวลาที่ใช้บริการ',
						'bkgprm_total_price'  => 'ราคารวมการจองโปรโมชั่น(บาท)',
						'bkgprm_persons'      => 'จำนวนผู้ใช้บริการ'
						
				),
				'searchFields'	=> array('bkgprm_id','prmsvl_id','bkg_id')
			);
			break;

		case 'services':
			 return array(
				'tableNameTH'	=> 'การใช้บริการ',
				'keyFieldName'  => 'ser_id',
				'keyFieldType'	=> 3, 
				'keyChar'		=> 'SE',
				'keyLength'		=> 15,
				'fieldNameList'	=> array(
						'ser_id'	  		  => 'รหัสการใช้บริการ',
						'cus_id'	          => 'ผู้ใช้บริการ',
						'emp_id' 		      => 'พนักงานที่รับเงิน',
						'paytyp_id'			  => 'ประเภทการชำระเงิน',
						'bed_id'      		  => 'รหัสเตียงนวด',
						'bkg_id'	  		  => 'รหัสการจอง',
						'ser_date'	          => 'วันที่ใช้บริการ',
						'ser_time'	          => 'เวลาที่ใช้บริการ',
						'ser_prm_discout'	  => 'ส่วนลดโปรโมชั่น',
						'ser_total_price' 	  => 'ราคาสุทธิ(บาท)',
						'ser_pay_price'		  => 'จำนวนเงินที่รับมา'
				),
				'hiddenFields'	=> array('bed_id','bkg_id','ser_time'),
				'defaultNull' 	=> array('bkg_id'),
				'searchFields'	=> array('ser_id','cus_id','ser_date','ser_time'),
				'deleteTxtField'		=> array('cus_id','ser_date'),
				'deleteTxtPatternMain' 	=> 'คุณต้องการลบการใช้บริการของ %f1 ในวันที่ %f2 ใช่หรือไม่?',
				'deleteTxtPatternMin' 	=> '%f1 ใช้บริการวันที่ %f2',
				'referenceData'			=> array('employees','employeesMasseuse','customers','customer_types','booking','beds','pay_types','packages', 'service_lists')

			);
			break;

		case 'service_service_lists':
			 return array(
				'tableNameTH'	=> 'รายละเอียดการใช้บริการรายการบริการ',
				'keyFieldName'  => 'sersvl_id',
				'keyFieldType'	=> 2, 
				'keyChar'		=> 'SS',
				'keyLength'		=> 14,
				'fieldNameList'	=> array(
						'sersvl_id'	  		  => 'รหัสรายละเอียดการใช้บริการรายการบริการ',
						'ser_id'	          => 'รหัสการใช้บริการ',
						'svl_id' 			  => 'รหัสรายการบริการ',
						'sersvl_amount' 	  => 'จำนวนครั้งที่ใช้บริการ',
						'sersvl_total_price'  => 'ราคารวม(บาท)',
						'sersvl_time'		  => 'เวลาที่ใช้บริการ'
				)

			);
			break;

		case 'service_list_details':
			 return array(
				'tableNameTH'	=> 'รายละเอียดรายการบริการ',
				'keyFieldName'  => 'svldtl_id',
				'keyFieldType'	=> 2, 
				'keyChar'		=> 'ST',
				'keyLength'		=> 15,
				'fieldNameList'	=> array(
						'svldtl_id'	  		  => 'รหัสรายละเอียดรายการบริการ',
						'svl_id'	          => 'รหัสรายการบริการ',
						'emp_id' 			  => 'ชื่อ-นามสกุลพนักงาน',
						'sersvl_id'	  		  => 'รหัสรายละเอียดการใช้บริการรายการบริการ',
						'svldtl_com'	      => 'ค่าคอมมิชชั่น(บาท)'
				),
				'searchFields'	=> array('svldtl_id','svl_id','emp_id')
			);
			break;

		case 'service_packages':
			 return array(
				'tableNameTH'	=> 'รายละเอียดการใช้บริการแพ็คเกจ',
				'keyFieldName'  => 'serpkg_id',
				'keyFieldType'	=> 2, 
				'keyChar'		=> 'SK',
				'keyLength'		=> 14,
				'fieldNameList'	=> array(
						'serpkg_id'	  		  	=> 'รหัสรายละเอียดการใช้บริการแพ็คเกจ',
						'ser_id'	          	=> 'รหัสรายการใช้บริการ',
						'pkg_id' 				=> 'รหัสแพ็คเกจ',
						'serpkg_amount' 		=> 'จำนวนครั้งที่ใช้บริการ',
						'serpkg_total_price'  	=> 'ราคารวม(บาท)'
				),
				'searchFields'	=> array('serpkg_id','ser_id')
			);
			break;

		case 'service_service_list_times':
			 return array(
				'tableNameTH'	=> 'รายละเอียดเวลาที่ใช้บริการรายการบริการ',
				'keyFieldName'  => 'sersvt_id',
				'keyFieldType'	=> 2, 
				'keyChar'		=> 'ST',
				'keyLength'		=> 15,
				'fieldNameList'	=> array(
						'sersvt_id'	  		  	=> 'รหัสรายละเอียดเวลาที่ใช้บริการรายการบริการ',
						'serpkg_id'	          	=> 'รหัสรายละเอียดการใช้บริการแพ็คเกจ',
						'pkgsvl_id' 			=> 'รหัสรายการบริการที่จัดแพ็คเกจ',
						'sersvt_time' 			=> 'เวลาที่ใช้บริการ'
				)
			);
			break;

		case 'package_details':
			 return array(
				'tableNameTH'	=> 'รายละเอียดแพ็คเกจ',
				'keyFieldName'  => 'pkgdtl_id',
				'keyFieldType'	=> 2, 
				'keyChar'		=> 'KD',
				'keyLength'		=> 15,
				'fieldNameList'	=> array(
						'pkgdtl_id'	  		  => 'รหัสรายละเอียดแพ็คเกจ',
						'sersvt_id'	  		  => 'รหัสรายละเอียดเวลาที่ใช้บริการรายการบริการ',
						'emp_id' 			  => 'ชื่อ-สกุลพนักงาน',
						'pkgdtl_com'		  => 'ค่าคอมมิชชั่น(บาท)'
				)
			);
			break;

		case 'element_checks':
			 return array(
				'tableNameTH'	=> 'การตรวจธาตุ',
				'keyFieldName'  => 'elechk_id',
				'keyFieldType'	=> 2, 
				'keyChar'		=> 'EC',
				'keyLength'		=> 9,
				'fieldNameList'	=> array(
						'elechk_id'	  		  => 'รหัสการตรวจประเภทธาตุ',
						'eletyp_id' 		  => 'ชื่อประเภทธาตุ',
						'emp_id' 			  => 'แพทย์ผู้ตรวจ',
						'cus_id'	  		  => 'ผู้ใช้บริการ',
						'elechk_date'	  	  => 'วันที่ตรวจ',
						'elechk_time'	  	  => 'เวลาที่ตรวจ'
				),
				'searchFields'	=> array('cus_id','emp_id','elechk_date'),
				'deleteTxtField'		=> array('cus_id','elechk_date'),
				'deleteTxtPatternMain' 	=> 'คุณต้องการลบการตรวจธาตุของ %f1 ในวันที่ %f2 ใช่หรือไม่?',
				'deleteTxtPatternMin' 	=> '%f1 ตรวจธาตุวันที่ %f2',
				'referenceData'			=> array('employees_doctor','customers','element_types')
			);
			break;

		case 'order_status':
			 return array(
				'tableNameTH'	=> 'สถานะการสั่งซื้อ',
				'keyFieldName'  => 'ordstat_id',
				'keyFieldType'	=> 2, 
				'keyChar'		=> 'OS',
				'keyLength'		=> 4,
				'fieldNameList'	=> array(
						'ordstat_id'	  	  => 'รหัสสถานะการสั่งซื้อ',
						'ordstat_name' 		  => 'ชื่อสถานะการสั่งซื้อ'
				),
				'searchFields'	=> array('ordstat_name'),
				'deleteTxtField'		=> array('ordstat_name')
			);
			break;

		case 'orders':
			 return array(
				'tableNameTH'	=> 'การสั่งซื้อผลิตภัณฑ์',
				'keyFieldName'  => 'ord_id',
				'keyFieldType'	=> 2, 
				'keyChar'		=> 'OR',
				'keyLength'		=> 7,
				'fieldNameList'	=> array(
						'ord_id'	  		  => 'รหัสการสั่งซื้อ',
						'ordtyp_id' 		  => 'ประเภทการสั่งซื้อ',
						'emp_id' 			  => 'ชื่อ-สกุลพนักงาน',
						'comp_id'	  		  => 'บริษัทจำหน่าย',
						'ord_date'	  	      => 'วันที่สั่งซื้อ',
						'ord_snd_date'        => 'วันที่จัดส่ง',
						'ordstat_id'	  	  => 'สถานะการสั่งซื้อ'
				),
				'hiddenFields' 	=> array(),
				'defaultNull'	=> array('ord_snd_date'),
				'searchFields'	=> array('ord_id','ordtyp_id','comp_id','ord_date','ordstat_id'),
				'deleteTxtField'		=> array('comp_id','ord_date'),
				'deleteTxtPatternMain' 	=> 'คุณต้องการลบการสั่งซื้อวันที่ %f2 จาก%f1 ใช่หรือไม่?',
				'deleteTxtPatternMin' 	=> 'การสั่งซื้อวันที่ %f2 จาก%f1',
				'referenceData'			=> array('employees','companies','order_types','order_status','products')
			);
			break;

		case 'order_details':
			 return array(
				'tableNameTH'	=> 'รายละเอียดการสั่งซื้อ',
				'keyFieldName'  => 'orddtl_id',
				'keyFieldType'	=> 2, 
				'keyChar'		=> 'OD',
				'keyLength'		=> 9,
				'fieldNameList'	=> array(
						'orddtl_id'	  		  => 'รหัสรายละเอียดการสั่งซื้อ',
						'ord_id' 		      => 'รหัสการสั่งซื้อ',
						'prd_id' 			  => 'ผลิตภัณฑ์',
						'orddtl_amount'	  	  => 'จำนวนที่สั่งซื้อ'
				),
				'searchFields'	=> array('ord_id','prd_id','orddtl_amount')
			);
			break;

		case 'receives':
			 return array(
				'tableNameTH'	=> 'การรับผลิตภัณฑ์',
				'keyFieldName'  => 'rec_id',
				'keyFieldType'	=> 2, 
				'keyChar'		=> 'RE',
				'keyLength'		=> 7,
				'fieldNameList'	=> array(
						'rec_id'	  		  => 'รหัสการรับ',
						'ord_id' 		      => 'รหัสการสั่งซื้อ',
						'emp_id' 			  => 'ชื่อ-นามสกุลพนักงาน',
						'rec_date'	  	      => 'วันที่รับ',
						'rec_total_price'	  => 'ราคา(บาท)'
				),
				'hiddenFields' 	=> array(),
				'searchFields'	=> array('rec_id','ord_id','rec_date','rec_total_price'),
				'deleteTxtField'	=> array('ord_id','rec_date'),
				'deleteTxtPatternMain' 	=> 'คุณต้องการลบการรับในวันที่ %f2 ของรหัสการสั่งซื้อ %f1 ใช่หรือไม่?',
				'deleteTxtPatternMin' 	=> 'การรับวันที่ %f2 ของรหัสการสั่งซื้อ %f1 ',
				'referenceData'			=> array('orders','employees')
			);
			break;

		case 'receive_details':
			 return array(
				'tableNameTH'	=> 'รายละเอียดการรับ',
				'keyFieldName'  => 'recdtl_id',
				'keyFieldType'	=> 2, 
				'keyChar'		=> 'RD',
				'keyLength'		=> 9,
				'fieldNameList'	=> array(
						'recdtl_id'	  		  => 'รหัสรายละเอียดการรับ',
						'rec_id'	  		  => 'รหัสการรับ',
						'prd_id' 		      => 'ผลิตภัณฑ์',
						'recdtl_amount' 	  => 'จำนวนที่รับ',
						'recdtl_price'	  	  => 'ราคาต่อหน่วย(บาท)'
				),
				'searchFields'	=> array('rec_id','prd_id')
			);
			break;

		case 'withdraws':
			 return array(
				'tableNameTH'	=> 'การเบิกผลิตภัณฑ์',
				'keyFieldName'  => 'wdw_id',
				'keyFieldType'	=> 2, 
				'keyChar'		=> 'W',
				'keyLength'		=> 8,
				'fieldNameList'	=> array(
						'wdw_id'	  		  => 'รหัสการเบิก',
						'emp_give_id' 		  => 'พนักงานที่ให้เบิก',
						'emp_id'	  		  => 'พนักงานที่เบิก',
						'ser_id' 	 		  => 'รหัสการใช้บริการ',
						'wdw_date'	  	      => 'วันที่เบิก',
						'wdwtyp_id' 	      => 'ประเภทการเบิก'
				),
				'searchFields'	=> array('emp_give_id','emp_id','wdw_date','wdwtyp_id'),
				'defaultNull' 	=> array('ser_id'),
				'hiddenFields'	=> array('ser_id'),
				'deleteTxtField'	=> array('wdw_date','emp_id'),
				'deleteTxtPatternMain' 	=> 'คุณต้องการลบการเบิกในวันที่ %f1 ที่เบิกโดย %f2 ใช่หรือไม่?',
				'deleteTxtPatternMin' 	=> 'การเบิกวันที่ %f1 ของรหัสการใช้บริการ %f2',
				'referenceData'			=> array('employees','services','products','withdraw_types')
			);
			break;

		case 'withdraw_details':
			 return array(
				'tableNameTH'	=> 'รายละเอียดการเบิก',
				'keyFieldName'  => 'wdwdtl_id',
				'keyFieldType'	=> 2, 
				'keyChar'		=> 'WD',
				'keyLength'		=> 10,
				'fieldNameList'	=> array(
						'wdwdtl_id'	  		  => 'รหัสรายละเอียดการเบิก',
						'wdw_id'	  		  => 'รหัสการเบิก',
						'prd_id'	  		  => 'ผลิตภัณฑ์',
						'wdwdtl_amount' 	  => 'จำนวนที่เบิก'
				),
				'searchFields'	=> array('wdw_id','prd_id','wdwdtl_id','wdwtyp_id'),
				'referenceData'	=> array('withdraws','products')
			);
			break;

		case 'sales':
			 return array(
				'tableNameTH'	=> 'การขาย',
				'keyFieldName'  => 'sale_id',
				'keyFieldType'	=> 2, 
				'keyChar'		=> 'S',
				'keyLength'		=> 8,
				'fieldNameList'	=> array(
						'sale_id'	  		  => 'รหัสการขาย',
						'emp_id'	  		  => 'ชื่อ-นามสกุลพนักงานที่ขาย',
						'sale_date'	  		  => 'วันที่ขาย',
						'sale_time'	  		  => 'เวลาที่ขาย',
						'sale_discout'		  => 'ส่วนลดการขาย',
						'sale_prm_discout' 	  => 'ส่วนลดโปรโมชั่น',
						'sale_total_price' 	  => 'ราคาทั้งหมด(บาท)',
						'sale_pay_price'	  => 'รับเงินมา'
				),
				'searchFields'		=> array('sale_id','emp_id','sale_date'),
				'hiddenFields'		=> array('sale_discout','sale_prm_discout','sale_pay_price'),
				'deleteTxtField'	=> array('sale_id','sale_date','sale_time'),
				'deleteTxtPatternMain' 	=> 'คุณต้องการลบการขายรหัส %f1 ที่ขายในวันที่ %f2 เวลา %f3 น. ใช่หรือไม่?',
				'deleteTxtPatternMin' 	=> 'การขายวันที่ %f1 เวลา %f3 ของรหัการขาย %f2',
				'referenceData'			=> array('employees','employeesSale','products','product_promotion_groups')
			);
			break;

		case 'sale_details':
			 return array(
				'tableNameTH'	=> 'รายละเอียดการขาย',
				'keyFieldName'  => 'saledtl_id',
				'keyFieldType'	=> 2, 
				'keyChar'		=> 'SD',
				'keyLength'		=> 12,
				'fieldNameList'	=> array(
						'saledtl_id'	  	  => 'รหัสรายละเอียดการขาย',
						'sale_id'	  		  => 'รหัสการขาย',
						'prd_id'	  		  => 'ผลิตภัณฑ์',
						'saledtl_amount'	  => 'จำนวน',
						'saledtl_price' 	  => 'ราคารวม(บาท)'
				),
				'searchFields'	=> array('sale_id','prd_id')
			);
			break;

		case 'sale_promotion_details':
			 return array(
				'tableNameTH'	=> 'รายละเอียดการเกิดโปรโมชั่น',
				'keyFieldName'  => 'saleprmdtl_id',
				'keyFieldType'	=> 2, 
				'keyChar'		=> 'SP',
				'keyLength'		=> 13,
				'fieldNameList'	=> array(
						'saleprmdtl_id'	  	  => 'รหัสรายละเอียดการเกิดโปรโมชั่น',
						'saledtl_id'	  	  => 'รหัสรายละเอียดการขาย',
						'prmprd_id'	  		  => 'ผลิตภัณฑ์ที่จัดโปรโมชั่น',
						'saleprmdtl_amount'	  => 'จำนวน',
						'saleprmdtl_discout'  => 'ส่วนลดรวม(บาท)'
				),
				'defaultNull' 	=> array('prmprd_id'),
				'searchFields'	=> array('saleprmdtl_id','saledtl_id','prmprd_id')
			);
			break;

		case 'print_purchase_orders':
			 return array(
				'tableNameTH'	=> 'การพิมพ์ใบสั่งซื้อ',
				'keyFieldName'  => 'prtord_id',
				'keyFieldType'	=> 2, 
				'keyChar'		=> 'PO',
				'keyLength'		=> 9,
				'fieldNameList'	=> array(
						'prtord_id'	  	  => 'รหัสการพิมพ์ใบสั่งซื้อ',
						'ord_id'	  	  => 'รหัสการสั่งซื้อ',
						'emp_id'	  	  => 'รหัสพนักงาน',
						'prtord_date'	  => 'วันที่พิมพ์',
						'prtord_time' 	  => 'เวลาที่พิมพ์'
				),
				'searchFields'	=> array('ord_id','emp_id','prtord_date')
			);
			break;

		case 'shops':
			 return array(
				'tableNameTH'	=> 'ร้านค้า',
				'keyFieldName'  => 'shop_id',
				'keyFieldType'	=> 2, 
				'keyChar'		=> 'SH',
				'keyLength'		=> 4,
				'fieldNameList'	=> array(
						'shop_id'	  	  => 'รหัสร้านค้า',
						'shop_name'	  	  => 'ชื่อร้านค้า',
						'shop_picture' 	  => 'รูปภาพร้านค้า',
						'shop_desc' 	  => 'คำอธิบาย'
				),
				'hiddenFields' 	=> array('shop_picture','shop_desc'),
				'defaultNull' 	=> array('shop_picture','shop_desc'),
				'searchFields'	=> array('shop_name'),
				'deleteTxtField'		=> array('shop_name'),
				'deleteTxtPatternMain' 	=> 'คุณต้องการลบร้านค้า %f1 ใช่หรือไม่?',
				'referenceData'			=> array('products')
			);
			break;

		case 'shop_display_products':
			 return array(
				'tableNameTH'	=> 'ผลิตภัณฑ์ที่ขายในร้านค้า',
				'keyFieldName'  => 'shpdpprd_id',
				'keyFieldType'	=> 2, 
				'keyChar'		=> 'DP',
				'keyLength'		=> 8,
				'fieldNameList'	=> array(
						'shpdpprd_id'	=> 'รหัสผลิตภัณฑ์ที่ขายในร้านค้า',
						'shop_id'	  	=> 'ร้านค้า',
						'prd_id' 		=> 'ผลิตภัณฑ์'
				),
				'searchFields'	=> array('shop_id','prd_id')
			);
			break;

		case 'product_promotion_groups':
			 return array(
				'tableNameTH'	=> 'กลุ่มโปรโมชั่นผลิตภัณฑ์',
				'keyFieldName'  => 'prdprmgrp_id',
				'keyFieldType'	=> 2, 
				'keyChar'		=> 'DG',
				'keyLength'		=> 4,
				'fieldNameList'	=> array(
						'prdprmgrp_id'	  	  => 'รหัสกลุ่มโปรโมชั่นผลิตภัณฑ์',
						'prdprmgrp_name'	  => 'ชื่อกลุ่มโปรโมชั่นผลิตภัณฑ์'
				),
				'searchFields'	=> array('prdprmgrp_name'),
				'deleteTxtField'	=> array('prdprmgrp_name'),
				'deleteTxtPatternMain' 	=> 'คุณต้องการลบกลุ่มโปรโมชั่นผลิตภัณฑ์ %f1 ใช่หรือไม่?'
			);
			break;

		case 'product_promotions':
			 return array(
				'tableNameTH'	=> 'โปรโมชั่นผลิตภัณฑ์',
				'keyFieldName'  => 'prdprm_id',
				'keyFieldType'	=> 2, 
				'keyChar'		=> 'PP',
				'keyLength'		=> 6,
				'fieldNameList'	=> array(
						'prdprm_id'	  	  		=> 'รหัสโปรโมชั่นผลิตภัณฑ์',
						'prdprm_name'	  		=> 'ชื่อโปรโมชั่นผลิตภัณฑ์',
						'prdprm_startdate'	  	=> 'วันที่เริ่มใช้',
						'prdprm_enddate'	  	=> 'วันที่สิ้นสุด',
						'prdprm_picture'	  	=> 'รูปภาพ',
						'prdprm_desc'	  		=> 'คำอธิบาย',
						'prdprm_type'	  		=> 'ประเภท',
						'prdprmgrp_id'	  		=> 'กลุ่มโปรโมชั่น'
				),
				'hiddenFields' 	=> array('prdprm_picture','prdprm_desc'),
				'defaultNull' 	=> array('prdprm_enddate','prdprm_picture','prdprm_desc'),
				'searchFields'	=> array('prdprm_name','prdprm_type','prdprmgrp_id','prdprm_startdate','prdprm_enddate'),
				'deleteTxtField'	=> array('prdprm_name'),
				'deleteTxtPatternMain' 	=> 'คุณต้องการลบโปรโมชั่นผลิตภัณฑ์ %f1 ใช่หรือไม่?',
				'referenceData'			=> array('product_promotion_groups')
			);
			break;

		case 'promotion_products':
			 return array(
				'tableNameTH'	=> 'ผลิตภัณฑ์ที่จัดโปรโมชั่น',
				'keyFieldName'  => 'prmprd_id',
				'keyFieldType'	=> 2, 
				'keyChar'		=> 'RP',
				'keyLength'		=> 10,
				'fieldNameList'	=> array(
						'prmprd_id'	  	  		=> 'รหัสผลิตภัณฑ์ที่จัดโปรโมชั่น',
						'prd_id'	  			=> 'ผลิตภัณฑ์',
						'prdprm_id'	  			=> 'โปรโมชั่นที่จัด',
						'prmprd_startdate'	  	=> 'วันที่เริ่มใช้',
						'prmprd_enddate'	  	=> 'วันที่สิ้นสุด',
						'prmprd_discout'	  	=> 'ส่วนลด',
						'prmprd_discout_type'	=> 'ส่วนลด' 
				),
				'hiddenFields' 			=> array('prmprd_discout'),
				'defaultNull' 			=> array('prmprd_enddate','prmprd_discout','prmprd_discout_type'),
				'searchFields'			=> array('prd_id','prmprd_discout_type','prdprm_id','prmprd_startdate','prmprd_enddate'),
				'deleteTxtField'		=> array('prd_id','prdprm_id'),
				'deleteTxtPatternMain' 	=> 'คุณต้องการลบผลิตภัณฑ์ %f1 ออกจากโปรโมชั่น %f2 ใช่หรือไม่?',
				'referenceData'			=> array('products','todayOnward_product_promotions')
			);
			break;

		case 'advertising':
			 return array(
				'tableNameTH'	=> 'ประชาสัมพันธ์หน้าการเข้า-ออกงาน',
				'keyFieldName'  => 'avs_id',
				'keyFieldType'	=> 2, 
				'keyChar'		=> 'AV',
				'keyLength'		=> 6,
				'fieldNameList'	=> array(
						'avs_id'	  => 'รหัสการประชาสัมพันธ์',
						'avs_name'	  => 'ชื่อ',
						'avs_img'	  => 'รูปภาพ',
						'avs_txt'	  => 'ข้อความ',
						'avs_status'  => 'สถานะ'
				),
				'searchFields'			=> array('avs_name','avs_status'),
				'deleteTxtField'		=> array('avs_name'),
				'deleteTxtPatternMain' 	=> 'คุณต้องการลบการประชาสัมพันธ์ลำดับที่ %f1 ใช่หรือไม่?',
				'hiddenFields' 			=> array('avs_img','avs_txt')
			);
			break;

		case 'promotion_discout_sales':
			 return array(
				'tableNameTH'	=> 'โปรโมชั่นการขาย',
				'keyFieldName'  => 'prmds_id',
				'keyFieldType'	=> 2, 
				'keyChar'		=> 'PC',
				'keyLength'		=> 6,
				'fieldNameList'	=> array(
						'prmds_id'	  	  		=> 'รหัสโปรโมชั่นการขาย',
						'prmds_name'	  		=> 'ชื่อโปรโมชั่นการขาย',
						'prdprmgrp_id'	  		=> 'กลุ่มโปรโมชั่น',
						'prmds_startdate'	  	=> 'วันที่เริ่มใช้',
						'prmds_enddate'	  		=> 'วันที่สิ้นสุด',
						'prmds_purchase'	  	=> 'ซื้อครบ(บาท)',
						'prmds_discout'	  		=> 'ส่วนลด',
						'prmds_discout_type'	=> 'ส่วนลด'
				),
				'hiddenFields' 			=> array('prmds_discout'),
				'defaultNull' 			=> array('prmds_enddate'),
				'searchFields'			=> array('prmds_name','prdprmgrp_id','prmds_purchase','prmds_discout_type','prmds_startdate','prmds_enddate'),
				'deleteTxtField'		=> array('prmds_name'),
				'deleteTxtPatternMain' 	=> 'คุณต้องการลบโปรโมชั่นส่วนลดการขาย %f1 ใช่หรือไม่?',
				'referenceData'			=> array('product_promotion_groups','product_types')
			);
			break;

		case 'promotion_discout_sale_details':
			 return array(
				'tableNameTH'	=> 'รายละเอียดโปรโมชั่นการขาย',
				'keyFieldName'  => 'prmdsdtl_id',
				'keyFieldType'	=> 2, 
				'keyChar'		=> 'ED',
				'keyLength'		=> 8,
				'fieldNameList'	=> array(
						'prmdsdtl_id'	  	=> 'รหัสรายละเอียดโปรโมชั่นการขาย',
						'prmds_id'	  		=> 'รหัสโปรโมชั่นส่วนลดการขาย',
						'prdtyp_id'	  		=> 'ประเภทผลิตภัณฑ์'
				),
				'searchFields'			=> array('prmdsdtl_id','prmds_id','prdtyp_id'),
				'deleteTxtField'		=> array('prmdsdtl_id'),
				'deleteTxtPatternMain' 	=> 'คุณต้องการลบรายละเอียดโปรโมชั่นการขาย %f1 ใช่หรือไม่?'
			);
			break;

		case 'sale_promotion_sale_details':
			 return array(
				'tableNameTH'	=> 'รายละเอียดการเกิดโปรโมชั่นการขาย',
				'keyFieldName'  => 'saleprmdsdtl_id',
				'keyFieldType'	=> 2, 
				'keyChar'		=> 'SM',
				'keyLength'		=> 10,
				'fieldNameList'	=> array(
						'saleprmdsdtl_id'	  		=> 'รหัสรายละเอียดการเกิดโปรโมชั่นการขาย',
						'sale_id'	  				=> 'รหัสการขาย',
						'prmds_id'	  				=> 'รหัสโปรโมชั่นการขาย',
						'saleprmdsdtl_discout'	  	=> 'ส่วนลด'
				)
			);
			break;

		case 'privileges':
			 return array(
				'tableNameTH'	=> 'สิทธิ์การใช้งาน',
				'keyFieldName'  => 'privlg_id',
				'keyFieldType'	=> 2, 
				'keyChar'		=> 'PV',
				'keyLength'		=> 5,
				'fieldNameList'	=> array(
						'privlg_id'	  		=> 'รหัสสิทธิ์การใช้งาน',
						'privlg_name'		=> 'ชื่อสิทธิ์การใช้งาน',
						'privlg_name_th'	=> 'ชื่อสิทธิ์การใช้งาน'
				),
				// 'hiddenFields'			=> array('privlg_name'),
				'searchFields'			=> array('privlg_name_th'),
				'deleteTxtField'		=> array('privlg_name_th'),
				'deleteTxtPatternMain' 	=> 'คุณต้องการลบสิทธิ์การใช้งาน %f1 ใช่หรือไม่?'
			);
			break;

		case 'grant_privileges':
			 return array(
				'tableNameTH'	=> 'การมอบสิทธิ์การใช้งาน',
				'keyFieldName'  => 'grnprivlg_id',
				'keyFieldType'	=> 2, 
				'keyChar'		=> 'GP',
				'keyLength'		=> 7,
				'fieldNameList'	=> array(
						'grnprivlg_id'	=> 'รหัสพนักงานที่ได้รับสิทธิ์',
						'privlg_id'		=> 'จำนวนสิทธิ์ที่ได้รับ',
						'emp_id' 		=> 'ชื่อ-นามสกุลพนักงานที่ได้รับสิทธิ์' 
				),
				'fixedHiddenFields'		=> true,
				'searchFields'			=> array('grnprivlg_id', 'emp_id'),
				'deleteTxtField'		=> array('privlg_id', 'emp_id'),
				'deleteTxtPatternMain' 	=> 'คุณต้องการลบสิทธิ์การใช้งานทั้งหมดของ %f2 ใช่หรือไม่?',
				'referenceData'			=> array('employees')
			);
			break;

		case 'package_promotions':
			 return array(
				'tableNameTH'	=> 'โปรโมชั่นแพ็คเกจ',
				'keyFieldName'  => 'pkgprm_id',
				'keyFieldType'	=> 2, 
				'keyChar'		=> 'PG',
				'keyLength'		=> 6,
				'fieldNameList'	=> array(
						'pkgprm_id'	  	  		=> 'รหัสโปรโมชั่นแพ็คเกจ',
						'pkgprm_name'	  		=> 'ชื่อโปรโมชั่นแพ็คเกจ',
						'custype_id'	  		=> 'ประเภทผู้ใช้บริการ',
						'pkgprm_startdate'	  	=> 'วันที่เริ่มใช้',
						'pkgprm_enddate'	  	=> 'วันที่สิ้นสุด',
						'pkgprm_desc'	  		=> 'คำอธิบาย',
						'pkgprm_pic'	  		=> 'รูปภาพ'
				),
				'hiddenFields' 			=> array('pkgprm_desc','pkgprm_pic'),
				'defaultNull' 			=> array('pkgprm_enddate','pkgprm_desc','pkgprm_pic'),
				'searchFields'			=> array('pkgprm_name','custype_id','pkgprm_startdate','pkgprm_enddate'),
				'deleteTxtField'		=> array('pkgprm_name'),
				'deleteTxtPatternMain' 	=> 'คุณต้องการลบโปรโมชั่นส่วนลดการขาย %f1 ใช่หรือไม่?',
				'referenceData'			=> array('customer_types')
			);
			break;

		case 'package_promotion_details':
			 return array(
				'tableNameTH'	=> 'แพ็คเกจที่จัดโปรโมชั่น',
				'keyFieldName'  => 'pkgprmdtl_id',
				'keyFieldType'	=> 2, 
				'keyChar'		=> 'PE',
				'keyLength'		=> 10,
				'fieldNameList'	=> array(
						'pkgprmdtl_id'	  	  		=> 'รหัสแพ็คเกจที่จัดโปรโมชั่น',
						'pkgprm_id'	  				=> 'โปรโมชั่นที่จัด',
						'pkg_id'	  				=> 'แพ็คเกจ',
						'pkgprmdtl_startdate'	  	=> 'วันที่เริ่มใช้',
						'pkgprmdtl_enddate'	  		=> 'วันที่สิ้นสุด',
						'pkgprmdtl_discout_type'	=> 'ส่วนลด',
						'pkgprmdtl_discout'	  		=> 'ส่วนลด'
				),
				'hiddenFields' 			=> array('pkgprmdtl_discout'),
				'defaultNull' 			=> array('pkgprmdtl_enddate'),
				'searchFields'			=> array('pkg_id','pkgprmdtl_discout_type','pkgprm_id','pkgprmdtl_startdate','pkgprmdtl_enddate'),
				'deleteTxtField'		=> array('pkg_id','pkgprm_id'),
				'deleteTxtPatternMain' 	=> 'คุณต้องการลบแพ็คเกจ %f1 ออกจากโปรโมชั่น %f2 ใช่หรือไม่?',
				'referenceData'			=> array('todayOnward_package_promotions','packages')
			);
			break;

		case 'service_list_promotions':
			 return array(
				'tableNameTH'	=> 'โปรโมชั่นรายการบริการ',
				'keyFieldName'  => 'svlprm_id',
				'keyFieldType'	=> 2, 
				'keyChar'		=> 'PG',
				'keyLength'		=> 6,
				'fieldNameList'	=> array(
						'svlprm_id'	  	  		=> 'รหัสโปรโมชั่นรายการบริการ',
						'svlprm_name'	  		=> 'ชื่อโปรโมชั่นรายการบริการ',
						'custype_id'	  		=> 'ประเภทผู้ใช้บริการ',
						'svlprm_startdate'	  	=> 'วันที่เริ่มใช้',
						'svlprm_enddate'	  	=> 'วันที่สิ้นสุด',
						'svlprm_desc'	  		=> 'คำอธิบาย',
						'svlprm_pic'	  		=> 'รูปภาพ'
				),
				'hiddenFields' 			=> array('svlprm_desc','svlprm_pic'),
				'defaultNull' 			=> array('svlprm_enddate','svlprm_desc','svlprm_pic'),
				'searchFields'			=> array('svlprm_name','custype_id','svlprm_startdate','svlprm_enddate'),
				'deleteTxtField'		=> array('svlprm_name'),
				'deleteTxtPatternMain' 	=> 'คุณต้องการลบโปรโมชั่นแพ็คเกจ %f1 ใช่หรือไม่?',
				'referenceData'			=> array('customer_types')
			);
			break;

		case 'service_list_promotion_details':
			 return array(
				'tableNameTH'	=> 'รายการบริการที่จัดโปรโมชั่น',
				'keyFieldName'  => 'svlprmdtl_id',
				'keyFieldType'	=> 2, 
				'keyChar'		=> 'PE',
				'keyLength'		=> 10,
				'fieldNameList'	=> array(
						'svlprmdtl_id'	  	  		=> 'รหัสรายการบริการที่จัดโปรโมชั่น',
						'svlprm_id'	  				=> 'โปรโมชั่นที่จัด',
						'svl_id'	  				=> 'รายการบริการ',
						'svlprmdtl_startdate'	  	=> 'วันที่เริ่มใช้',
						'svlprmdtl_enddate'	  		=> 'วันที่สิ้นสุด',
						'svlprmdtl_discout_type'	=> 'ส่วนลด',
						'svlprmdtl_discout'	  		=> 'ส่วนลด'
				),
				'hiddenFields' 			=> array('svlprmdtl_discout'),
				'defaultNull' 			=> array('svlprmdtl_enddate'),
				'searchFields'			=> array('svl_id','svlprmdtl_discout_type','svlprm_id','svlprmdtl_startdate','svlprmdtl_enddate'),
				'deleteTxtField'		=> array('svl_id','svlprm_id'),
				'deleteTxtPatternMain' 	=> 'คุณต้องการลบการบริการ %f1 ออกจากโปรโมชั่น %f2 ใช่หรือไม่?',
				'referenceData'			=> array('todayOnward_service_list_promotions','service_lists')
			);
			break;

		case 'service_package_promotions':
			 return array(
				'tableNameTH'	=> 'รายละเอียดการเกิดโปรโมชั่นแพ็คเกจ',
				'keyFieldName'  => 'serpkgprm_id',
				'keyFieldType'	=> 2, 
				'keyChar'		=> 'SP',
				'keyLength'		=> 14,
				'fieldNameList'	=> array(
						'serpkgprm_id'	  	  		=> 'รหัสรายละเอียดการเกิดโปรโมชั่นแพ็คเกจ',
						'ser_id'	  				=> 'รหัสการใช้บริการ',
						'pkgprmdtl_id'	  			=> 'รหัสแพ็คเกจที่จัดโปรโมชั่น',
						'serpkgprm_amount'	  		=> 'จำนวนการเกิด',
						'serpkgprm_discout_total'	=> 'ส่วนลด'
				)
			);
			break;

		case 'service_service_list_promotions':
			 return array(
				'tableNameTH'	=> 'รายละเอียดการเกิดโปรโมชั่นรายการบริการ',
				'keyFieldName'  => 'sersvlprm_id',
				'keyFieldType'	=> 2, 
				'keyChar'		=> 'SO',
				'keyLength'		=> 14,
				'fieldNameList'	=> array(
						'sersvlprm_id'	  	  		=> 'รหัสรายละเอียดการเกิดโปรโมชั่นรายการบริการ',
						'ser_id'	  				=> 'รหัสการใช้บริการ',
						'svlprmdtl_id'	  			=> 'รหัสรายการบริการที่จัดโปรโมชั่น',
						'sersvlprm_amount'	  		=> 'จำนวนการเกิด',
						'sersvlprm_discout_total'	=> 'ส่วนลด'
				)
			);
			break;

		case 'holidays':
			 return array(
				'tableNameTH'	=> 'วันหยุดให้บริการสปา',
				'keyFieldName'  => 'hld_id',
				'keyFieldType'	=> 2, 
				'keyChar'		=> 'HL',
				'keyLength'		=> 6,
				'fieldNameList'	=> array(
						'hld_id'	  	  		=> 'รหัสวันหยุดให้บริการสปา',
						'hld_name'	  			=> 'ชื่อวันหยุด',
						'hld_startdate'	  		=> 'วันหยุดเริ่มต้น',
						'hld_enddate'	  		=> 'วันหยุดสิ้นสุด'
				),
				'searchFields'			=> array('hld_name','hld_startdate'),
				'deleteTxtField'		=> array('hld_name','hld_startdate','hld_enddate'),
				'deleteTxtPatternMain' 	=> 'คุณต้องการลบวันหยุด %f1 (%f2 - %f3) ใช่หรือไม่?'
			);
			break;

		case 'commission_rates':
			 return array(
				'tableNameTH'	=> 'อัตราการจ่ายค่าคอมมิชชั่น',
				'keyFieldName'  => 'cmr_id',
				'keyFieldType'	=> 2, 
				'keyChar'		=> 'CR',
				'keyLength'		=> 4,
				'fieldNameList'	=> array(
						'cmr_id'	  	  => 'รหัสอัตราการจ่ายค่าคอมมิชชั่น',
						'cmr_day'	  	=> 'วัน',
						'cmr_starttime'	=> 'เวลาเริ่มต้น',
						'cmr_endtime' 	=> 'เวลาสิ้นสุด',
						'cmr_rate'	   	=> 'อัตราการจ่าย(%)'
				),
				'searchFields'			=> array('cmr_day','cmr_starttime','cmr_endtime','cmr_rate'),
				'deleteTxtField'		=> array('cmr_day','cmr_starttime','cmr_endtime'),
				'deleteTxtPatternMain' 	=> 'คุณต้องการลบอัตราการจ่ายค่าคอมมิชชั่นของวัน %f1 เวลา %f2-%f3 ใช่หรือไม่?',
				'optionData' 			=> array('cmr_day')
			);
			break;

		case 'overtime_rates':
			 return array(
				'tableNameTH'	=> 'อัตราการจ่ายค่าล่วงเวลา',
				'keyFieldName'  => 'otr_id',
				'keyFieldType'	=> 2, 
				'keyChar'		=> 'TR',
				'keyLength'		=> 4,
				'fieldNameList'	=> array(
						'otr_id'	  	  			=> 'รหัสอัตราการจ่ายค่าล่วงเวลา',
						'otr_name' 					=> 'ชื่ออัตราการจ่ายค่าล่วงเวลา',
						'otr_time_in' 				=> 'เวลาเข้างาน',
						'otr_time_out'	   			=> 'เวลาออกงาน',
						'otr_otstarttime_daywork' 	=> 'เวลาเริ่มทำงานล่วงเวลาวันทำงาน',
						'otr_otendtime_daywork' 	=> 'เวลาสิ้นสุดทำงานล่วงเวลาวันทำงาน',
						'otr_otstarttime_dayoff' 	=> 'เวลาเริ่มทำงานล่วงเวลาวันหยุด',
						'otr_otendtime_dayoff' 		=> 'เวลาสิ้นสุดทำงานล่วงเวลาวันหยุด',
						'otr_bath_per_hour' 		=> 'อัตราค่าล่วงเวลา(บาท/ชั่วโมง)'
				),
				'hiddenFields' 			=> array('otr_time_in','otr_time_out','otr_otstarttime_daywork','otr_otendtime_daywork','otr_otstarttime_dayoff','otr_otendtime_dayoff'),
				'searchFields'			=> array('otr_name','otr_bath_per_hour'),
				'deleteTxtField'		=> array('otr_name'),
				'deleteTxtPatternMain' 	=> 'คุณต้องการลบอัตราการจ่ายค่าล่วงเวลา %f1 ใช่หรือไม่?'
			);
			break;
	 }
 }
 /*วิธีการเรียกใช้ array $table['employees']['fieldNameList']['pos_name'];*/
?>