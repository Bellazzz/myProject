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
						'pos_name'		=> 'ชื่อตำแหน่ง'
				),
				'searchFields'	=> array('pos_name'),
				'deleteTxtField'=> array('pos_name')
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
						'emp_email'		=> 'E-mail/Username',
						'emp_pass'		=> 'Password',
						'emp_pic'		=> 'รูปภาพ'
				),
				'hiddenFields'	=> array('emp_indate','emp_birthdate','emp_addr','emp_tel','emp_email','emp_pass','emp_pic'),
				'defaultNull' 	=> array('emp_pic','emp_birthdate','emp_email','emp_pass'),
				'searchFields'	=> array('sex_id','title_id','emp_name','emp_surname','pos_id'),
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
						'bnkacc_branch' => 'สาขา',
						'bnkacc_type'	=> 'ประเภทบัญชี'
				),
				'searchFields'	=> array('bnkacc_no','bnkacc_name'),
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
						'prdtyp_name'	=> 'ประเภทผลิตภัณฑ์'
				),
				'searchFields'	=> array('prdtyp_name'),
				'deleteTxtField'=> array('prdtyp_name')
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
				'searchFields'	=> array('spa_name','spa_addr'),
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
				'searchFields'	=> array('cus_name','cus_surname','sex_id','custype_id'),
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
						'svltyp_id'			=> 'ประเภทรายการใช้บริการ',
						'svl_min'			=> 'เวลาที่ใช้(นาที)',
						'svl_hr'			=> 'เวลาที่ใช้(ชั่วโมง)',
						'svl_name'			=> 'รายการบริการ',
						'svl_price'			=> 'ราคา(บาท)',
						'svl_commission'	=> 'ค่าคอมมิชชั่น(บาท)',
						'svl_desc'			=> 'คำอธิบาย',
						'svl_picture'		=> 'รูปภาพ'	
				),
				'hiddenFields'	=> array('svl_desc','svl_picture','svl_commission','svl_price'),
				'defaultNull' 	=> array('svl_min','svl_hr','svl_desc','svl_picture'),
				'searchFields'	=> array('svl_name','svl_price'),
				'deleteTxtField' 	=> array('svl_name')
			
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
						'pkg_name'	    => 'แพ็คเกจ',
						'pkg_start'     => 'วันที่เริ่มใช้',
						'pkg_stop'      => 'วันเวลาที่สิ้นสุด',
						'pkg_desc'      => 'คำอธิบาย',
						'pkg_price'     => 'ราคา(บาท)',
						'pkg_picture'   => 'รูปภาพ'			
				),
				'hiddenFields'	=> array('pkg_desc','pkg_picture'),
				'defaultNull' 	=> array('pkg_stop','pkg_desc','pkg_picture'),
				'searchFields'	=> array('pkg_name','pkg_price'),
				'deleteTxtField' 	=> array('pkg_name')
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
						'pkg_id'        => 'แพ็คเกจ'
				),
				'searchFields'	=> array('svl_id','pkg_id')

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

		case 'promotion_service_lists':
			 return array(
				'tableNameTH'	=> 'รายการบริการที่จัดโปรโมชั่น',
				'keyFieldName'  => 'prmsvl_id',
				'keyFieldType'	=> 2, 
				'keyChar'		=> 'PL',
				'keyLength'		=> 8,
				'fieldNameList'	=> array(
						'prmsvl_id'	      => 'รหัสรายการบริการที่จัดโปรโมชั่น',
						'svl_id'	      => 'รายการบริการ',
						'prm_id'          => 'โปรโมชั่น',
						'prmsvl_start'	  => 'วันที่เริ่มใช้',
						'prmsvl_end'	  => 'วันที่สิ้นสุด',
						'prmsvl_desc'     => 'คำอธิบาย',
						'prmsvl_price'    => 'ราคา(บาท)'
				),
				'hiddenFields'	=> array('prmsvl_desc'),
				'defaultNull' 	=> array('prmsvl_start','prmsvl_end'),
				'deleteTxtField' 		=> array('prm_id','svl_id'),
				'deleteTxtPatternMain' 	=>('คุณต้องการลบรายการบริการ%f2 ออกจาก%f1 ใช่หรือไม่'),
				'deleteTxtPatternMin' 	=>('รายการบริการ%f2 ของโ%f1')

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
						'prd_amount'      => 'จำนวน',
						'prd_pic'      	  => 'รูปภาพ'
				),
				'hiddenFields'	=> array('prd_pic','prd_amount','unit_id'),
				'defaultNull' 	=> array('prd_amount'),
				'searchFields'	=> array('prd_name','prd_price'),
				'deleteTxtField' 		=> array('prd_name')
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
						'dateatt_in'      => 'วันที่เข้า',
						'timeatt_in'      => 'เวลาที่เข้า',
						'dateatt_out'	  => 'วันที่ออก',
						'timeatt_out'	  => 'เวลาที่ออก'
				),
				'defaultNull' 	=> array('timeatt_out','dateatt_out'),
				'searchFields'	=> array('emp_id','dateatt_in','dateatt_out'),
				'deleteTxtField'	=> array('dateatt_in','emp_id'),
				'deleteTxtPatternMain' 	=> 'คุณต้องการลบการเข้า-ออกงานวันที่ %f1 ของ %f2 ใช่หรือไม่?',
				'deleteTxtPatternMin' 	=> 'วันที่ %f1 ของ %f2'
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
						'emp_id'				=> 'ชื่อ-นามสกุลพนักงาน',
						'payroll_salary'		=> 'จำนวนเงินเดือน(บาท)',
						'payroll_commission'	=> 'ค่าคอมมิชชั่น(บาท)',
						'payroll_monthly'		=> 'ประจำเดือน-ปี',
						'payroll_date'			=> 'วันที่จ่ายเงินเดือน'
				),
				'searchFields'	=> array('emp_id','payroll_salary','payroll_monthly'),
				'deleteTxtField'	=> array('emp_id','payroll_monthly'),
				'deleteTxtPatternMain' 	=> 'คุณต้องการลบการจ่ายเงินเดือนของ %f1 ประจำเดือน %f2 ใช่หรือไม่?',
				'deleteTxtPatternMin' 	=> '%f1 ประจำเดือน %f2'
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
						'cus_id'	        	  => 'ชื่อ-นามสกุลผู้ใช้บริการ',
						'emp_id' 		    	  => 'ชื่อ-นามสกุลพนักงาน',
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
				'defaultNull' 	=> array('bkg_transfer_date','bkg_transfer_time','bkg_transfer_evidence','bkg_transfer_money','bnkacc_id'),
				'searchFields'	=> array('bkg_id','cus_id','emp_id','status_id','bkg_total_price','bkg_date','bkg_time'),
				'deleteTxtField'	=> array('cus_id','bkg_date'),
				'deleteTxtPatternMain' 	=> 'คุณต้องการลบการจองของ %f1 ในวันที่ %f2 ใช่หรือไม่?',
				'deleteTxtPatternMin' 	=> '%f1 จองวันที่ %f2'
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
						'bkgsvl_date'        => 'วัน-เวลาที่ใช้บริการ',
						'bkgsvl_total_price' => 'ราคารวมการจองรายการบริการ(บาท)',
						'bkgsvl_persons'     => 'จำนวนผู้ใช้บริการ'
				),
				'searchFields'	=> array('bkgsvl_id','svl_id','bkg_id')
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
						'pkgsvl_id'	          => 'รหัสรายการบริการที่จัดแพ็คเกจ',
						'bkg_id' 		      => 'รหัสการจอง',
						'bkgpkg_date'         => 'วัน-เวลาที่ใช้บริการ',
						'bkgpkg_total_price'  => 'ราคารวมการจองแพ็คเกจ(บาท)',
						'bkgpkg_persons'      => 'จำนวนผู้ใช้บริการ'
				),
				'searchFields'	=> array('bkgpkg_id','pkgsvl_id','bkg_id')
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
						'cus_id'	          => 'ชื่อ-นามสกุลผู้ใช้บริการ',
						'emp_id' 		      => 'ชื่อ-นามสกุลพนักงานที่รับเงิน',
						'paytyp_id'			  => 'รหัสประเภทการชำระเงิน',
						'bed_id'      		  => 'รหัสเตียงนวด',
						'bkg_id'	  		  => 'รหัสการจอง',
						'ser_date'	          => 'วันที่ใช้บริการ',
						'ser_time'	          => 'เวลาที่ใช้บริการ',
						'ser_total_price' 	  => 'ราคารวมทั้งหมด(บาท)'
				),
				'hiddenFields'	=> array('emp_id','bed_id','bkg_id','ser_time','ser_total_price'),
				'defaultNull' 	=> array('bkg_id'),
				'searchFields'	=> array('ser_id','cus_id','ser_date','ser_time'),
				'deleteTxtField'		=> array('cus_id','ser_date'),
				'deleteTxtPatternMain' 	=> 'คุณต้องการลบการใช้บริการของ %f1 ในวันที่ %f2 ใช่หรือไม่?',
				'deleteTxtPatternMin' 	=> '%f1 ใช้บริการวันที่ %f2'

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
						'sersvl_total_price'  => 'ราคารวม(บาท)'
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
						'serpkg_id'	  		  => 'รหัสรายละเอียดการใช้บริการแพ็คเกจ',
						'ser_id'	          => 'รหัสรายการใช้บริการ',
						'serpkg_total_price'  => 'ราคารวม(บาท)'
				),
				'searchFields'	=> array('serpkg_id','ser_id')
			);
			break;

		case 'package_details':
			 return array(
				'tableNameTH'	=> 'รายละเอียดแพ็คเกจ',
				'keyFieldName'  => 'pkgdtl_id',
				'keyFieldType'	=> 2, 
				'keyChar'		=> 'PA',
				'keyLength'		=> 15,
				'fieldNameList'	=> array(
						'pkgdtl_id'	  		  => 'รหัสรายละเอียดแพ็คเกจ',
						'serpkg_id'	  		  => 'รหัสรายละเอียดการใช้บริการแพ็คเกจ',
						'pkgsvl_id'	          => 'รหัสรายการบริการที่จัดแพ็คเกจ',
						'emp_id' 			  => 'ชื่อ-สกุลพนักงาน',
						'pkgdtl_comm'		  => 'ค่าคอมมิชชั่น(บาท)'
				),
				'searchFields'	=> array('pkgdtl_id','pkgsvl_id')
			);
			break;

		case 'service_promotions':
			 return array(
				'tableNameTH'	=> 'รายละเอียดการใช้บริการโปรโมชั่น',
				'keyFieldName'  => 'serprm_id',
				'keyFieldType'	=> 2, 
				'keyChar'		=> 'SP',
				'keyLength'		=> 14,
				'fieldNameList'	=> array(
						'serprm_id'	  		  => 'รหัสรายละเอียดการใช้บริการโปรโมชั่น',
						'ser_id'	  		  => 'รหัสการใช้บริการ',
						'serprm_total_price'  => 'ราคารวม(บาท)'
				),
				'searchFields'	=> array('serprm_id','ser_id')
			);
			break;

		case 'promotion_details':
			 return array(
				'tableNameTH'	=> 'รายละเอียดโปรโมชั่น',
				'keyFieldName'  => 'prmdtl_id',
				'keyFieldType'	=> 2, 
				'keyChar'		=> 'PD',
				'keyLength'		=> 15,
				'fieldNameList'	=> array(
						'prmdtl_id'	  		  => 'รหัสรายละเอียดโปรโมชั่น',
						'emp_id' 			  => 'ชื่อ-นามสกุลพนักงาน',
						'svl_id'	  		  => 'รหัสรายการบริการที่จัดโปรโมชั่น',
						'serprm_id'	  		  => 'รหัสรายละเอียดการใช้บริการโปรโมชั่น',
						'prmdtl_comm' 		  => 'ค่าคอมมิชชั่น(บาท)'
				),
				'searchFields'	=> array('prmdtl_id','svl_id')
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
				'deleteTxtPatternMin' 	=> '%f1 ตรวจธาตุวันที่ %f2'
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
				'tableNameTH'	=> 'การสั่งซื้อ',
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
				'deleteTxtPatternMin' 	=> 'การสั่งซื้อวันที่ %f2 จาก%f1'
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
				'tableNameTH'	=> 'การรับ',
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
				'deleteTxtPatternMin' 	=> 'การรับวันที่ %f2 ของรหัสการสั่งซื้อ %f1 '
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
				'tableNameTH'	=> 'การเบิก',
				'keyFieldName'  => 'wdw_id',
				'keyFieldType'	=> 2, 
				'keyChar'		=> 'W',
				'keyLength'		=> 8,
				'fieldNameList'	=> array(
						'wdw_id'	  		  => 'รหัสการเบิก',
						'emp_id'	  		  => 'ชื่อ-นามสกุลพนักงานที่ให้เบิก',
						'emp_give_id' 		  => 'ชื่อ-นามสกุลพนักงานที่เบิก',
						'ser_id' 	 		  => 'รหัสการใช้บริการ',
						'wdw_date'	  	      => 'วันที่เบิก'
				),
				'searchFields'	=> array('emp_id','emp_give_id','wdw_date'),
				'deleteTxtField'	=> array('wdw_date','ser_id'),
				'deleteTxtPatternMain' 	=> 'คุณต้องการลบการเบิกในวันที่ %f1 ของรหัสการใช้บริการ %f2 ใช่หรือไม่?',
				'deleteTxtPatternMin' 	=> 'การเบิกวันที่ %f1 ของรหัสการใช้บริการ %f2'
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
				'searchFields'	=> array('wdw_id','prd_id','wdwdtl_id')
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
						'sale_total_price' 	  => 'ราคารวม(บาท)'
				),
				'searchFields'	=> array('emp_id','sale_date'),
				'deleteTxtField'	=> array('sale_date','sale_id','sale_time'),
				'deleteTxtPatternMain' 	=> 'คุณต้องการลบการขายในวันที่ %f1 เวลา %f3 ของรหัสการขาย %f2 ใช่หรือไม่?',
				'deleteTxtPatternMin' 	=> 'การขายวันที่ %f1 เวลา %f3 ของรหัการขาย %f2'
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
						'saledtl_price' 	  => 'ราคาต่อหน่วย(บาท)'
				),
				'searchFields'	=> array('sale_id','prd_id')
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

		
	 }
 }
 /*วิธีการเรียกใช้ array $table['employees']['fieldNameList']['pos_name'];*/
?>