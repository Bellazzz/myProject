SET AUTOCOMMIT = 0;
SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS advertising;

CREATE TABLE `advertising` (
  `avs_id` char(6) collate utf8_unicode_ci NOT NULL,
  `avs_name` varchar(150) collate utf8_unicode_ci NOT NULL,
  `avs_img` varchar(255) collate utf8_unicode_ci default NULL,
  `avs_txt` varchar(255) collate utf8_unicode_ci default NULL,
  `avs_status` tinyint(1) NOT NULL,
  PRIMARY KEY  (`avs_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO advertising VALUES("AV0001","ฑีฆายุโห โหตุ","AV0001.gif","","1");
INSERT INTO advertising VALUES("AV0002","ลดโลกร้อน","AV0002.gif","มาร้วมกัยประหยัด ลดภาวะโลกร้อนกันเถอะ โลกสวยด้วยมือเรา","1");
INSERT INTO advertising VALUES("AV0003","รักเมืองไทย","AV0003.gif","อยู่เมืองไทย กินของไทย ใช้ของไทย รักเมืองไทย","1");
INSERT INTO advertising VALUES("AV0004","รักในหลวง","AV0004.gif","","1");
INSERT INTO advertising VALUES("AV0005","วันเวาเลนไทน์","AV0005.gif","วาเลนไทน์นีอย่าลืม พาคนที่รักไปเข้าวัดฟังธรรมนะคะ","1");
INSERT INTO advertising VALUES("AV0006","ระเบียบการเข้างาน","","1.  เข้า – ออกงานให้ตรงเวลา ต้องแสกนด้วยตนเองทุกครั้ง\n2.  สวมใส่เสื้อผ้าที่สะอาด ซัก – รีด ให้เรียบร้อยทุกครั้ง สวมรองเท้าสีขาว และติดบัตรพนักงานทุกครั้ง\n3.  เล็บมือต้องตัดให้สั้น ไม่ทาเล็บ และรักษาความสะอาดทั้งกลิ่นตัว กลิ่นปาก ฟัน รวมถึงถุงเท้า รองเท้","1");



DROP TABLE IF EXISTS bank_accounts;

CREATE TABLE `bank_accounts` (
  `bnkacc_id` char(4) collate utf8_unicode_ci NOT NULL,
  `bnkacc_no` varchar(20) collate utf8_unicode_ci NOT NULL,
  `bnkacc_name` varchar(100) collate utf8_unicode_ci NOT NULL,
  `bnkacc_branch` varchar(50) collate utf8_unicode_ci NOT NULL,
  `bnkacc_type` varchar(50) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`bnkacc_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO bank_accounts VALUES("BA01","590-231568-8","สปา รพ.เจ้าพระยาอภับภูเบศร","ปราจีนบุรี","สะสมทรัพย์");



DROP TABLE IF EXISTS beds;

CREATE TABLE `beds` (
  `bed_id` char(4) collate utf8_unicode_ci NOT NULL,
  `room_id` char(4) collate utf8_unicode_ci NOT NULL,
  `bed_name` varchar(50) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`bed_id`),
  KEY `room_id` (`room_id`),
  CONSTRAINT `beds_ibfk_1` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`room_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO beds VALUES("BE01","RM01","เตียงนวดหน้า1");
INSERT INTO beds VALUES("BE02","RM01","เตียงนวดหน้า2");
INSERT INTO beds VALUES("BE03","RM01","เตียงนวดตัว");
INSERT INTO beds VALUES("BE04","RM01","เตียงขัดตัว");
INSERT INTO beds VALUES("BE05","RM01","เตียงนวดสวีท");
INSERT INTO beds VALUES("BE06","RM02","เตียงนวดสวีท");
INSERT INTO beds VALUES("BE07","RM03","เตียงสปา");



DROP TABLE IF EXISTS booking;

CREATE TABLE `booking` (
  `bkg_id` varchar(15) collate utf8_unicode_ci NOT NULL,
  `cus_id` varchar(8) collate utf8_unicode_ci NOT NULL,
  `emp_id` varchar(6) collate utf8_unicode_ci default NULL,
  `status_id` varchar(3) collate utf8_unicode_ci NOT NULL,
  `bnkacc_id` varchar(4) collate utf8_unicode_ci default NULL,
  `bkg_transfer_date` date default NULL,
  `bkg_transfer_time` time default NULL,
  `bkg_transfer_evidence` varchar(150) collate utf8_unicode_ci default NULL,
  `bkg_total_price` float NOT NULL,
  `bkg_date` date NOT NULL,
  `bkg_time` time NOT NULL,
  `bkg_transfer_money` float default NULL,
  PRIMARY KEY  (`bkg_id`),
  KEY `cus_id` (`cus_id`),
  KEY `emp_id` (`emp_id`),
  KEY `status_id` (`status_id`),
  KEY `bnkacc_id` (`bnkacc_id`),
  CONSTRAINT `booking_ibfk_1` FOREIGN KEY (`cus_id`) REFERENCES `customers` (`cus_id`),
  CONSTRAINT `booking_ibfk_2` FOREIGN KEY (`emp_id`) REFERENCES `employees` (`emp_id`),
  CONSTRAINT `booking_ibfk_3` FOREIGN KEY (`status_id`) REFERENCES `booking_status` (`bkgstat_id`),
  CONSTRAINT `booking_ibfk_4` FOREIGN KEY (`bnkacc_id`) REFERENCES `bank_accounts` (`bnkacc_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO booking VALUES("BK0000000001/58","CM000001","EM0002","S03","BA01","2015-04-08","23:00:00","","1350","2015-04-08","22:58:00","1350");
INSERT INTO booking VALUES("BK0000000002/58","CM000001","EM0002","S03","BA01","2015-04-08","23:00:00","","1215","2015-04-08","23:23:00","1215");
INSERT INTO booking VALUES("BK0000000003/58","CM000001","EM0002","S03","","","","","800","2015-05-04","22:21:00","");
INSERT INTO booking VALUES("BK0000000004/58","CM000012","EM0002","S03","","","","","1200","2015-05-05","10:26:00","");
INSERT INTO booking VALUES("BK0000000006/58","CM000001","EM0002","S03","","","","","2100","2015-05-05","14:43:00","");
INSERT INTO booking VALUES("BK0000000007/58","CM000004","EM0002","S05","","","","","200","2015-05-05","20:06:00","");
INSERT INTO booking VALUES("BK0000000008/58","CM000015","EM0002","S05","","","","","350","2015-05-06","20:23:00","");
INSERT INTO booking VALUES("BK0000000009/58","CM000015","EM0002","S05","","","","","1200","2015-05-06","20:25:00","");
INSERT INTO booking VALUES("BK0000000010/58","CM000012","EM0002","S05","","","","","5800","2015-05-07","00:03:00","");
INSERT INTO booking VALUES("BK0000000011/58","CM000001","EM0002","S01","","","","","350","2015-05-07","14:41:00","");
INSERT INTO booking VALUES("BK0000000012/58","CM000015","EM0002","S01","","","","","350","2015-05-07","15:14:00","");
INSERT INTO booking VALUES("BK0000000013/58","CM000015","EM0002","S01","","","","","3350","2015-05-08","21:39:00","");
INSERT INTO booking VALUES("BK0000000014/58","CM000015","EM0002","S01","","","","","350","2015-05-08","21:41:00","");
INSERT INTO booking VALUES("BK0000000015/58","CM000015","EM0002","S01","","","","","1200","2015-05-09","01:17:00","");
INSERT INTO booking VALUES("BK0000000016/58","CM000004","EM0002","S01","","","","","2200","2015-05-10","10:58:00","");
INSERT INTO booking VALUES("BK0000000017/58","CM000004","EM0002","S01","","","","","2200","2015-05-10","10:59:00","");
INSERT INTO booking VALUES("BK0000000018/58","CM000015","EM0002","S01","","","","","5150","2015-05-10","11:18:00","");
INSERT INTO booking VALUES("BK0000000019/58","CM000012","EM0002","S03","","","","","550","2015-05-10","11:23:00","");
INSERT INTO booking VALUES("BK0000000020/58","CM000004","EM0002","S03","","","","","200","2015-05-10","12:12:00","");
INSERT INTO booking VALUES("BK0000000021/58","CM000015","EM0002","S03","","","","","2200","2015-05-10","12:19:00","");
INSERT INTO booking VALUES("BK0000000022/58","CM000015","EM0002","S01","","","","","1200","2015-05-10","17:04:00","");
INSERT INTO booking VALUES("BK0000000023/58","CM000015","EM0002","S01","","","","","1200","2015-05-10","17:05:00","");
INSERT INTO booking VALUES("BK0000000024/58","CM000012","EM0002","S01","","","","","1200","2015-05-10","17:05:00","");
INSERT INTO booking VALUES("BK0000000025/58","CM000012","EM0002","S01","","","","","1200","2015-05-10","17:05:00","");
INSERT INTO booking VALUES("BK0000000026/58","CM000001","EM0002","S01","","","","","1200","2015-05-10","17:05:00","");
INSERT INTO booking VALUES("BK0000000027/58","CM000004","EM0002","S01","","","","","1200","2015-05-10","17:06:00","");
INSERT INTO booking VALUES("BK0000000028/58","CM000004","EM0002","S01","","","","","2600","2015-05-10","17:06:00","");
INSERT INTO booking VALUES("BK0000000029/58","CM000001","","S01","","","","","3600","2015-05-10","22:39:00","");
INSERT INTO booking VALUES("BK0000000030/58","CM000001","","S01","","","","","2550","2015-05-10","23:00:00","");
INSERT INTO booking VALUES("BK0000000031/58","CM000001","","S01","","","","","3500","2015-05-10","23:23:00","");
INSERT INTO booking VALUES("BK0000000032/58","CM000001","","S01","","","","","800","2015-05-10","23:51:00","");
INSERT INTO booking VALUES("BK0000000033/58","CM000001","","S01","","","","","2650","2015-05-10","23:53:00","");



DROP TABLE IF EXISTS booking_packages;

CREATE TABLE `booking_packages` (
  `bkgpkg_id` char(14) collate utf8_unicode_ci NOT NULL,
  `pkg_id` char(5) collate utf8_unicode_ci NOT NULL,
  `bkg_id` char(15) collate utf8_unicode_ci NOT NULL,
  `emp_id` char(6) collate utf8_unicode_ci default NULL,
  `bkgpkg_date` date NOT NULL,
  `bkgpkg_time` time NOT NULL,
  `bkgpkg_total_price` float NOT NULL,
  `bkgpkg_persons` int(11) NOT NULL,
  `bkgpkg_status` int(1) NOT NULL default '0',
  PRIMARY KEY  (`bkgpkg_id`),
  KEY `bkg_id` (`bkg_id`),
  KEY `pkg_id` (`pkg_id`),
  CONSTRAINT `booking_packages_ibfk_2` FOREIGN KEY (`pkg_id`) REFERENCES `packages` (`pkg_id`),
  CONSTRAINT `booking_packages_ibfk_3` FOREIGN KEY (`bkg_id`) REFERENCES `booking` (`bkg_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO booking_packages VALUES("BP000000000001","PK007","BK0000000002/58","","2015-04-08","11:00:00","1215","1","0");
INSERT INTO booking_packages VALUES("BP000000000002","PK003","BK0000000004/58","EM0008","2015-05-05","10:00:00","1200","1","0");
INSERT INTO booking_packages VALUES("BP000000000006","PK003","BK0000000009/58","EM0008","2015-05-06","20:00:00","1200","1","2");
INSERT INTO booking_packages VALUES("BP000000000007","PK003","BK0000000010/58","","2015-05-07","04:00:00","1200","1","2");
INSERT INTO booking_packages VALUES("BP000000000008","PK007","BK0000000010/58","","2015-05-07","02:00:00","2000","1","2");
INSERT INTO booking_packages VALUES("BP000000000009","PK005","BK0000000010/58","","2015-05-07","04:00:00","2600","1","2");
INSERT INTO booking_packages VALUES("BP000000000010","PK003","BK0000000015/58","EM0003","2015-05-09","01:00:00","1200","1","0");
INSERT INTO booking_packages VALUES("BP000000000011","PK002","BK0000000016/58","","2015-05-10","13:00:00","2200","1","0");
INSERT INTO booking_packages VALUES("BP000000000012","PK002","BK0000000017/58","EM0001","2015-05-10","10:00:00","2200","1","0");
INSERT INTO booking_packages VALUES("BP000000000013","PK002","BK0000000018/58","EM0011","2015-05-10","12:00:00","2200","1","0");
INSERT INTO booking_packages VALUES("BP000000000014","PK002","BK0000000021/58","EM0003","2015-05-10","13:00:00","2200","1","0");
INSERT INTO booking_packages VALUES("BP000000000015","PK005","BK0000000018/58","","2015-05-10","15:00:00","2600","1","0");
INSERT INTO booking_packages VALUES("BP000000000016","PK003","BK0000000022/58","EM0001","2015-05-10","17:00:00","1200","1","0");
INSERT INTO booking_packages VALUES("BP000000000017","PK003","BK0000000023/58","EM0003","2015-05-10","17:00:00","1200","1","0");
INSERT INTO booking_packages VALUES("BP000000000018","PK003","BK0000000024/58","EM0004","2015-05-10","17:00:00","1200","1","0");
INSERT INTO booking_packages VALUES("BP000000000019","PK003","BK0000000025/58","EM0011","2015-05-10","17:00:00","1200","1","0");
INSERT INTO booking_packages VALUES("BP000000000020","PK003","BK0000000026/58","EM0012","2015-05-10","17:00:00","1200","1","0");
INSERT INTO booking_packages VALUES("BP000000000021","PK003","BK0000000027/58","EM0013","2015-05-10","17:00:00","1200","1","0");
INSERT INTO booking_packages VALUES("BP000000000022","PK005","BK0000000028/58","EM0014","2015-05-10","17:00:00","2600","1","0");
INSERT INTO booking_packages VALUES("BP000000000023","PK002","BK0000000030/58","","2015-05-16","11:00:00","2200","1","0");
INSERT INTO booking_packages VALUES("BP000000000024","PK007","BK0000000031/58","EM0011","2015-05-10","10:00:00","2000","1","0");
INSERT INTO booking_packages VALUES("BP000000000025","PK006","BK0000000033/58","EM0011","2015-05-16","11:00:00","2000","1","0");



DROP TABLE IF EXISTS booking_service_lists;

CREATE TABLE `booking_service_lists` (
  `bkgsvl_id` char(8) collate utf8_unicode_ci NOT NULL,
  `svl_id` char(5) collate utf8_unicode_ci NOT NULL,
  `bkg_id` char(15) collate utf8_unicode_ci NOT NULL,
  `emp_id` char(6) collate utf8_unicode_ci default NULL,
  `bkgsvl_date` date NOT NULL,
  `bkgsvl_time` time NOT NULL,
  `bkgsvl_total_price` float NOT NULL,
  `bkgsvl_persons` int(11) NOT NULL,
  `bkgsvl_status` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`bkgsvl_id`),
  KEY `svl_id` (`svl_id`),
  KEY `bkg_id` (`bkg_id`),
  CONSTRAINT `booking_service_lists_ibfk_1` FOREIGN KEY (`svl_id`) REFERENCES `service_lists` (`svl_id`),
  CONSTRAINT `booking_service_lists_ibfk_2` FOREIGN KEY (`bkg_id`) REFERENCES `booking` (`bkg_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO booking_service_lists VALUES("BL000001","SL003","BK0000000001/58","","2015-04-08","10:00:00","1350","1","0");
INSERT INTO booking_service_lists VALUES("BL000002","SL006","BK0000000003/58","","2015-05-04","09:00:00","800","1","0");
INSERT INTO booking_service_lists VALUES("BL000003","SL005","BK0000000007/58","","2015-05-05","18:00:00","200","1","2");
INSERT INTO booking_service_lists VALUES("BL000004","SL011","BK0000000008/58","EM0003","2015-05-06","19:00:00","350","1","2");
INSERT INTO booking_service_lists VALUES("BL000005","SL011","BK0000000011/58","EM0001","2015-05-07","14:00:00","350","1","0");
INSERT INTO booking_service_lists VALUES("BL000006","SL011","BK0000000012/58","EM0001","2015-05-07","15:00:00","350","1","0");
INSERT INTO booking_service_lists VALUES("BL000007","SL011","BK0000000013/58","EM0001","2015-05-08","21:00:00","350","1","0");
INSERT INTO booking_service_lists VALUES("BL000008","SL006","BK0000000013/58","EM0003","2015-05-08","21:00:00","800","1","0");
INSERT INTO booking_service_lists VALUES("BL000009","SL005","BK0000000013/58","EM0004","2015-05-08","21:00:00","200","1","0");
INSERT INTO booking_service_lists VALUES("BL000010","SL010","BK0000000013/58","EM0011","2015-05-08","21:00:00","700","1","0");
INSERT INTO booking_service_lists VALUES("BL000011","SL007","BK0000000013/58","EM0012","2015-05-08","21:00:00","650","1","0");
INSERT INTO booking_service_lists VALUES("BL000012","SL023","BK0000000013/58","EM0013","2015-05-08","21:00:00","0","1","0");
INSERT INTO booking_service_lists VALUES("BL000013","SL008","BK0000000013/58","EM0014","2015-05-08","21:00:00","650","1","0");
INSERT INTO booking_service_lists VALUES("BL000014","SL011","BK0000000014/58","EM0013","2015-05-08","21:00:00","350","1","0");
INSERT INTO booking_service_lists VALUES("BL000015","SL011","BK0000000019/58","","2015-05-10","13:00:00","350","1","0");
INSERT INTO booking_service_lists VALUES("BL000016","SL005","BK0000000019/58","EM0001","2015-05-10","14:00:00","200","1","0");
INSERT INTO booking_service_lists VALUES("BL000017","SL005","BK0000000020/58","EM0004","2015-05-10","13:00:00","200","1","0");
INSERT INTO booking_service_lists VALUES("BL000018","SL011","BK0000000018/58","","2015-05-10","12:00:00","350","1","0");
INSERT INTO booking_service_lists VALUES("BL000019","SL006","BK0000000029/58","","2015-05-10","12:00:00","1600","2","0");
INSERT INTO booking_service_lists VALUES("BL000020","SL011","BK0000000029/58","","2015-05-17","12:00:00","700","2","0");
INSERT INTO booking_service_lists VALUES("BL000021","SL008","BK0000000029/58","","2015-05-10","14:00:00","1300","2","0");
INSERT INTO booking_service_lists VALUES("BL000022","SL011","BK0000000030/58","","2015-05-10","11:00:00","350","1","0");
INSERT INTO booking_service_lists VALUES("BL000023","SL001","BK0000000031/58","EM0003","2015-05-10","13:00:00","1500","1","0");
INSERT INTO booking_service_lists VALUES("BL000024","SL006","BK0000000032/58","EM0004","2015-05-10","10:00:00","800","1","0");
INSERT INTO booking_service_lists VALUES("BL000025","SL007","BK0000000033/58","","2015-05-10","11:00:00","650","1","0");



DROP TABLE IF EXISTS booking_status;

CREATE TABLE `booking_status` (
  `bkgstat_id` char(3) collate utf8_unicode_ci NOT NULL,
  `bkgstat_name` varchar(50) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`bkgstat_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO booking_status VALUES("S01","รอตรวจสอบ");
INSERT INTO booking_status VALUES("S02","รอชำระเงิน");
INSERT INTO booking_status VALUES("S03","รอใช้บริการ");
INSERT INTO booking_status VALUES("S04","ยังใช้บริการไม่ครบ");
INSERT INTO booking_status VALUES("S05","ใช้บริการครบแล้ว");
INSERT INTO booking_status VALUES("S06","ยกเลิกการจอง");
INSERT INTO booking_status VALUES("S07","ใช้บริการไม่ครบตามที่จอง");



DROP TABLE IF EXISTS brands;

CREATE TABLE `brands` (
  `brand_id` char(4) collate utf8_unicode_ci NOT NULL,
  `brand_name` varchar(100) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`brand_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO brands VALUES("BR01","อภัยภูเบศร");
INSERT INTO brands VALUES("BR02","Panpuri");
INSERT INTO brands VALUES("BR03","Erb");
INSERT INTO brands VALUES("BR04","Thann");
INSERT INTO brands VALUES("BR05","Harrn");
INSERT INTO brands VALUES("BR06","ไม่ระบุ");



DROP TABLE IF EXISTS commission_rates;

CREATE TABLE `commission_rates` (
  `cmr_id` char(4) collate utf8_unicode_ci NOT NULL,
  `cmr_day` varchar(20) collate utf8_unicode_ci NOT NULL,
  `cmr_starttime` time NOT NULL,
  `cmr_endtime` time NOT NULL,
  `cmr_rate` int(11) NOT NULL,
  PRIMARY KEY  (`cmr_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO commission_rates VALUES("CR01","อาทิตย์","08:30:00","16:00:00","20");
INSERT INTO commission_rates VALUES("CR02","อาทิตย์","16:01:00","20:00:00","25");
INSERT INTO commission_rates VALUES("CR03","จันทร์","08:00:00","20:00:00","30");
INSERT INTO commission_rates VALUES("CR04","อังคาร","08:00:00","20:00:00","30");
INSERT INTO commission_rates VALUES("CR05","พุธ","08:00:00","16:00:00","20");
INSERT INTO commission_rates VALUES("CR06","พุธ","16:01:00","20:00:00","25");
INSERT INTO commission_rates VALUES("CR07","พฤหัสบดี","08:00:00","16:00:00","20");
INSERT INTO commission_rates VALUES("CR08","พฤหัสบดี","16:01:00","20:00:00","25");
INSERT INTO commission_rates VALUES("CR09","ศุกร์","08:00:00","16:00:00","20");
INSERT INTO commission_rates VALUES("CR10","ศุกร์","16:01:00","20:00:00","25");
INSERT INTO commission_rates VALUES("CR11","เสาร์","08:00:00","16:00:00","20");
INSERT INTO commission_rates VALUES("CR12","เสาร์","16:01:00","20:00:00","25");
INSERT INTO commission_rates VALUES("CR13","วันหยุดสปา","08:00:00","20:00:00","30");



DROP TABLE IF EXISTS companies;

CREATE TABLE `companies` (
  `comp_id` char(4) collate utf8_unicode_ci NOT NULL,
  `comp_name` varchar(100) collate utf8_unicode_ci NOT NULL,
  `fax` varchar(15) collate utf8_unicode_ci default NULL,
  `comp_tel` varchar(30) collate utf8_unicode_ci NOT NULL,
  `comp_email` varchar(255) collate utf8_unicode_ci default NULL,
  `comp_addr` varchar(255) collate utf8_unicode_ci NOT NULL,
  `comp_contact` varchar(100) collate utf8_unicode_ci default NULL,
  PRIMARY KEY  (`comp_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO companies VALUES("CP01","บริษัท เคมีภัณฑ์ จำกัด","021115510","021115510-05","","112 ต.สามโคก อ.เมือง จ.ปทุมธานี","ประภัสสร เมืองคง");
INSERT INTO companies VALUES("CP02","บริษัท รักสมุนไพร จำกัด","037405638","037405638","","37/1 ม.8 ต.ไม้เค็ด อ.เมือง จ.ปราจีนบุรี ","วารุณี พิสาคร");
INSERT INTO companies VALUES("CP03","บริษัท ฟอเรส สปา จำกัด (มหาชน)","037-896553","037-896552 ต่อ 727","","สมุทรปราการ","คุณอุษมา ราตรี");
INSERT INTO companies VALUES("CP04","บริษัท อิมเพรส สปา จำกัด (มหาชน)","","111","","ดดด","");



DROP TABLE IF EXISTS customer_types;

CREATE TABLE `customer_types` (
  `custype_id` char(3) collate utf8_unicode_ci NOT NULL,
  `custype_name` varchar(30) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`custype_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO customer_types VALUES("CT1","ลูกค้าทั่วไป");
INSERT INTO customer_types VALUES("CT2","สมาชิก");
INSERT INTO customer_types VALUES("CT3","พนักงานสปา");
INSERT INTO customer_types VALUES("CT4","พนักงาน รพ.");



DROP TABLE IF EXISTS customers;

CREATE TABLE `customers` (
  `cus_id` char(8) collate utf8_unicode_ci NOT NULL,
  `sex_id` char(2) collate utf8_unicode_ci NOT NULL,
  `custype_id` char(3) collate utf8_unicode_ci NOT NULL,
  `title_id` char(3) collate utf8_unicode_ci NOT NULL,
  `cus_name` varchar(30) collate utf8_unicode_ci NOT NULL,
  `cus_surname` varchar(50) collate utf8_unicode_ci NOT NULL,
  `cus_addr` varchar(255) collate utf8_unicode_ci NOT NULL,
  `cus_tel` char(10) collate utf8_unicode_ci NOT NULL,
  `cus_pass` varchar(32) collate utf8_unicode_ci default NULL,
  `cus_birthdate` date default NULL,
  `cus_registered_date` date NOT NULL,
  `cus_facebook` varchar(30) collate utf8_unicode_ci default NULL,
  `cus_line_id` varchar(20) collate utf8_unicode_ci default NULL,
  `cus_email` varchar(100) collate utf8_unicode_ci default NULL,
  PRIMARY KEY  (`cus_id`),
  KEY `custype_id` (`custype_id`),
  KEY `title_id` (`title_id`),
  KEY `sex_id` (`sex_id`),
  CONSTRAINT `customers_ibfk_10` FOREIGN KEY (`custype_id`) REFERENCES `customer_types` (`custype_id`),
  CONSTRAINT `customers_ibfk_11` FOREIGN KEY (`title_id`) REFERENCES `titles` (`title_id`),
  CONSTRAINT `customers_ibfk_9` FOREIGN KEY (`sex_id`) REFERENCES `sex` (`sex_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO customers VALUES("CM000001","X1","CT2","T01","จีระพงษ์","ฮะไล้","Bangkok","1111111111","81dc9bdb52d04dc20036dbd8313ed055","2014-08-03","2014-08-17","Stan Weston2","westonnn","user@gmail.com");
INSERT INTO customers VALUES("CM000002","X2","CT1","T02","พรรษา","พวงทอง","United Kingdom","0900399948","dsdd333","2014-08-01","2014-08-17","Nanny Star","nannnnn","naneyiv@gmail.com");
INSERT INTO customers VALUES("CM000003","X1","CT2","T07","มนัส","รุ่งเรือง","ปราจีนบุรี","0863394500","1234","2014-09-16","2014-09-18","sfsdfsdf","sdfsdfsd","");
INSERT INTO customers VALUES("CM000004","X2","CT2","T03","จริณพร","ทรัพย์มี","นครนายก","0952950028","1234","","2014-09-09","","","");
INSERT INTO customers VALUES("CM000005","X2","CT2","T02","อนิลทิตา","วัยเวศ","กรุงเทพมหานคร","0895562231","d41d8cd98f00b204e9800998ecf8427e","","2014-09-01","","","");
INSERT INTO customers VALUES("CM000006","X1","CT4","T07","ทินกร","กังสดาร","ปราจีนบุรี","0896652233","e10adc3949ba59abbe56e057f20f883e","1971-10-23","2014-10-25","https://www.facebook.com/gasy","gasy","anitarinorn@hotmail.com");
INSERT INTO customers VALUES("CM000007","X1","CT2","T10","ประวิทย์","เกษรเงิน","กทม.","0893350210","d41d8cd98f00b204e9800998ecf8427e","","2014-11-02","","","");
INSERT INTO customers VALUES("CM000008","X2","CT1","T11","บุษบง","บัวบาน","นนทบุรี","0937740129","d41d8cd98f00b204e9800998ecf8427e","","2014-05-08","","","");
INSERT INTO customers VALUES("CM000009","X1","CT3","T05","อำนวย","พืชผล","ปราจีนบุรี","0924991003","d41d8cd98f00b204e9800998ecf8427e","","2014-11-01","","","");
INSERT INTO customers VALUES("CM000010","X2","CT4","T03","ชนกนันท์","ฟ้าประดับ","ปราจีนบุรี","0802229911","d41d8cd98f00b204e9800998ecf8427e","","2014-11-15","","","");
INSERT INTO customers VALUES("CM000011","X2","CT2","T11","พิมผกา","จิตอาสา","สมุทรสาคร","0911117733","d41d8cd98f00b204e9800998ecf8427e","","2014-11-07","","","");
INSERT INTO customers VALUES("CM000012","X1","CT2","T01","ขจรเดช","เมืองคง","นครราชสีมา","0913885032","d41d8cd98f00b204e9800998ecf8427e","","2014-03-07","","","");
INSERT INTO customers VALUES("CM000013","X2","CT3","T03","หทัยมาศ","ไกรสร","ปราจีนบุรี","0922940011","d41d8cd98f00b204e9800998ecf8427e","","2014-11-16","","","");
INSERT INTO customers VALUES("CM000014","X2","CT4","T02","อันทรา","เกดกล้า","dfs","0895563326","","","2014-11-23","https://www.facebook.com/test","45698","");
INSERT INTO customers VALUES("CM000015","X1","CT2","T10","กาญจนา","มารศรี","jjjjj","0845465465","","","2014-12-19","","","");
INSERT INTO customers VALUES("CM000016","X1","CT1","T01","sadasd","adad","adasd","aasdasd","81dc9bdb52d04dc20036dbd8313ed055","","2015-05-11","","","aaaaa@gmail.com");



DROP TABLE IF EXISTS element_checks;

CREATE TABLE `element_checks` (
  `elechk_id` char(9) collate utf8_unicode_ci NOT NULL,
  `eletyp_id` char(4) collate utf8_unicode_ci NOT NULL,
  `emp_id` char(6) collate utf8_unicode_ci NOT NULL,
  `cus_id` char(8) collate utf8_unicode_ci NOT NULL,
  `elechk_date` date NOT NULL,
  `elechk_time` time NOT NULL,
  PRIMARY KEY  (`elechk_id`),
  KEY `eletyp_id` (`eletyp_id`),
  KEY `emp_id` (`emp_id`),
  KEY `cus_id` (`cus_id`),
  CONSTRAINT `element_checks_ibfk_1` FOREIGN KEY (`eletyp_id`) REFERENCES `element_types` (`eletyp_id`),
  CONSTRAINT `element_checks_ibfk_2` FOREIGN KEY (`emp_id`) REFERENCES `employees` (`emp_id`),
  CONSTRAINT `element_checks_ibfk_3` FOREIGN KEY (`cus_id`) REFERENCES `customers` (`cus_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO element_checks VALUES("EC0000001","ET01","EM0001","CM000001","2014-08-08","00:00:00");
INSERT INTO element_checks VALUES("EC0000002","ET02","EM0002","CM000002","2014-08-08","00:00:00");
INSERT INTO element_checks VALUES("EC0000003","ET02","EM0003","CM000003","2014-09-04","14:45:12");
INSERT INTO element_checks VALUES("EC0000004","ET04","EM0003","CM000001","2014-09-10","10:00:00");
INSERT INTO element_checks VALUES("EC0000005","ET02","EM0007","CM000003","2014-10-26","11:31:00");



DROP TABLE IF EXISTS element_types;

CREATE TABLE `element_types` (
  `eletyp_id` char(4) collate utf8_unicode_ci NOT NULL,
  `eletyp_name` varchar(50) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`eletyp_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO element_types VALUES("ET01","ดิน");
INSERT INTO element_types VALUES("ET02","น้ำ");
INSERT INTO element_types VALUES("ET03","ลม");
INSERT INTO element_types VALUES("ET04","ไฟ");
INSERT INTO element_types VALUES("ET05","เหล็ก");



DROP TABLE IF EXISTS employees;

CREATE TABLE `employees` (
  `emp_id` char(6) collate utf8_unicode_ci NOT NULL,
  `sex_id` char(2) collate utf8_unicode_ci default NULL,
  `pos_id` char(3) collate utf8_unicode_ci NOT NULL,
  `title_id` char(3) collate utf8_unicode_ci NOT NULL,
  `emp_name` varchar(30) collate utf8_unicode_ci NOT NULL,
  `emp_surname` varchar(30) collate utf8_unicode_ci NOT NULL,
  `emp_addr` varchar(255) collate utf8_unicode_ci NOT NULL,
  `emp_tel` char(10) collate utf8_unicode_ci NOT NULL,
  `emp_pic` varchar(150) collate utf8_unicode_ci default NULL,
  `emp_birthdate` date default NULL,
  `emp_indate` date NOT NULL,
  `emp_salary` float NOT NULL,
  `emp_email` varchar(255) collate utf8_unicode_ci default NULL,
  `emp_pass` varchar(32) collate utf8_unicode_ci default NULL,
  PRIMARY KEY  (`emp_id`),
  KEY `pos_id` (`pos_id`),
  KEY `title_id` (`title_id`),
  KEY `sex_id` (`sex_id`),
  CONSTRAINT `employees_ibfk_17` FOREIGN KEY (`sex_id`) REFERENCES `sex` (`sex_id`),
  CONSTRAINT `employees_ibfk_18` FOREIGN KEY (`pos_id`) REFERENCES `positions` (`pos_id`),
  CONSTRAINT `employees_ibfk_19` FOREIGN KEY (`title_id`) REFERENCES `titles` (`title_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO employees VALUES("EM0001","X2","P03","T03","สำรวย","น้อยมาลา","Prachinburi","0877534470","EM0001.jpeg","2014-08-20","0000-00-00","9000","employee1@gmail.com","81dc9bdb52d04dc20036dbd8313ed055");
INSERT INTO employees VALUES("EM0002","X2","P01","T02","กนกพร","น้อยมาลา","oooooo","0895632256","EM0002.jpeg","1471-08-15","1471-11-23","25000","kanokporn@gmail.com","81dc9bdb52d04dc20036dbd8313ed055");
INSERT INTO employees VALUES("EM0003","X2","P03","T08","ญานิกา","ประกาสิต","สระบุรี","0854458897","EM0003.jpeg","2014-09-13","2015-05-01","8500","employee2@gmail.com","81dc9bdb52d04dc20036dbd8313ed055");
INSERT INTO employees VALUES("EM0004","X2","P03","T02","พิจิตรา","พันธุ์สะอาด","ปราจีนบุรี","0928289956","EM0004.jpeg","2014-09-02","2014-09-02","7500","","81dc9bdb52d04dc20036dbd8313ed055");
INSERT INTO employees VALUES("EM0005","X2","P04","T02","กมลลักษณ์","ไชยมงคล","เชียงใหม่","0894484811","EM0005.jpeg","","2014-09-19","10000","","");
INSERT INTO employees VALUES("EM0006","X2","P01","T03","เพ็ญพัชร","อิ่มเกษม","กรุงเทพฯ","0875584132","EM0006.jpeg","","2014-09-18","12000","","d41d8cd98f00b204e9800998ecf8427e");
INSERT INTO employees VALUES("EM0007","X2","P04","T03","เย็นฤดี","ศรีตรัง","ร้อยเอ็ด","0873345567","EM0007.jpeg","1996-04-03","2009-08-31","16000","cashier@gmail.com","81dc9bdb52d04dc20036dbd8313ed055");
INSERT INTO employees VALUES("EM0008","X1","P02","T10","Edword","Ellicc","Americnano","0859965247","","1989-10-09","2010-10-17","9900","","d41d8cd98f00b204e9800998ecf8427e");
INSERT INTO employees VALUES("EM0009","X1","P02","T07","เจษริน","อินสุข","ปราจีนบุรี","0877569958","EM0009.jpeg","1986-08-18","2011-10-01","11000","","d41d8cd98f00b204e9800998ecf8427e");
INSERT INTO employees VALUES("EM0010","X2","P02","T11","สสิพร","มีสุข","ขอนแก่น","0995823356","","","2013-12-01","13500","","d41d8cd98f00b204e9800998ecf8427e");
INSERT INTO employees VALUES("EM0011","X2","P03","T03","กนกกร","พวงดอกไม้","ปราจีนบุรี","0907731123","EM0011.jpeg","2014-11-19","2014-11-19","15000","","5ce8b97501b7fa488e1fa3504172eb5b");
INSERT INTO employees VALUES("EM0012","X2","P03","T03","รัศมี","มาลาเพศ","dfdsf","0875692231","EM0012.jpeg","","2014-11-06","8500","mamew@hotmail.com","25d55ad283aa400af464c76d713c07ad");
INSERT INTO employees VALUES("EM0013","X2","P03","T03","หฟกฟหก","ฟหกฟหก","ฟหกฟหก","0545454654","","2014-11-27","2014-11-27","90000","","d41d8cd98f00b204e9800998ecf8427e");
INSERT INTO employees VALUES("EM0014","X1","P03","T01","มั่งมี","ศรีสุข","asdads","0845645456","","","2014-11-28","12500","","d41d8cd98f00b204e9800998ecf8427e");
INSERT INTO employees VALUES("EM0015","X1","P04","T01","สินทรัพย์","มั่นคง","asdasd","0548465468","","","2014-12-19","10000","","");



DROP TABLE IF EXISTS grant_privileges;

CREATE TABLE `grant_privileges` (
  `grnprivlg_id` varchar(7) collate utf8_unicode_ci NOT NULL,
  `privlg_id` char(5) collate utf8_unicode_ci NOT NULL,
  `emp_id` char(6) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`grnprivlg_id`),
  KEY `privlg_id` (`privlg_id`),
  KEY `emp_id` (`emp_id`),
  CONSTRAINT `grant_privileges_ibfk_1` FOREIGN KEY (`privlg_id`) REFERENCES `privileges` (`privlg_id`),
  CONSTRAINT `grant_privileges_ibfk_2` FOREIGN KEY (`emp_id`) REFERENCES `employees` (`emp_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO grant_privileges VALUES("GP00001","PV029","EM0002");
INSERT INTO grant_privileges VALUES("GP00002","PV014","EM0002");
INSERT INTO grant_privileges VALUES("GP00003","PV026","EM0002");
INSERT INTO grant_privileges VALUES("GP00004","PV027","EM0002");
INSERT INTO grant_privileges VALUES("GP00005","PV028","EM0002");
INSERT INTO grant_privileges VALUES("GP00006","PV010","EM0002");
INSERT INTO grant_privileges VALUES("GP00007","PV025","EM0002");
INSERT INTO grant_privileges VALUES("GP00008","PV013","EM0002");
INSERT INTO grant_privileges VALUES("GP00009","PV021","EM0002");
INSERT INTO grant_privileges VALUES("GP00010","PV024","EM0002");
INSERT INTO grant_privileges VALUES("GP00011","PV022","EM0002");
INSERT INTO grant_privileges VALUES("GP00012","PV016","EM0002");
INSERT INTO grant_privileges VALUES("GP00013","PV015","EM0002");
INSERT INTO grant_privileges VALUES("GP00015","PV018","EM0002");
INSERT INTO grant_privileges VALUES("GP00016","PV020","EM0002");
INSERT INTO grant_privileges VALUES("GP00017","PV017","EM0002");
INSERT INTO grant_privileges VALUES("GP00018","PV019","EM0002");
INSERT INTO grant_privileges VALUES("GP00019","PV023","EM0002");
INSERT INTO grant_privileges VALUES("GP00020","PV005","EM0002");
INSERT INTO grant_privileges VALUES("GP00021","PV001","EM0002");
INSERT INTO grant_privileges VALUES("GP00022","PV009","EM0002");
INSERT INTO grant_privileges VALUES("GP00023","PV012","EM0002");
INSERT INTO grant_privileges VALUES("GP00024","PV008","EM0002");
INSERT INTO grant_privileges VALUES("GP00025","PV004","EM0002");
INSERT INTO grant_privileges VALUES("GP00026","PV006","EM0002");
INSERT INTO grant_privileges VALUES("GP00027","PV002","EM0002");
INSERT INTO grant_privileges VALUES("GP00028","PV007","EM0002");
INSERT INTO grant_privileges VALUES("GP00029","PV003","EM0002");
INSERT INTO grant_privileges VALUES("GP00032","PV008","EM0007");
INSERT INTO grant_privileges VALUES("GP00033","PV006","EM0007");
INSERT INTO grant_privileges VALUES("GP00034","PV007","EM0007");
INSERT INTO grant_privileges VALUES("GP00040","PV030","EM0002");
INSERT INTO grant_privileges VALUES("GP00041","PV011","EM0002");
INSERT INTO grant_privileges VALUES("GP00042","PV012","EM0007");
INSERT INTO grant_privileges VALUES("GP00043","PV005","EM0007");
INSERT INTO grant_privileges VALUES("GP00044","PV022","EM0007");



DROP TABLE IF EXISTS holidays;

CREATE TABLE `holidays` (
  `hld_id` char(6) collate utf8_unicode_ci NOT NULL,
  `hld_name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `hld_startdate` date NOT NULL,
  `hld_enddate` date NOT NULL,
  PRIMARY KEY  (`hld_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO holidays VALUES("HL0001","วันขึ้นปีใหม่","2015-01-01","2015-01-01");
INSERT INTO holidays VALUES("HL0002","วันมาฆบูชา","2015-03-04","2015-03-04");
INSERT INTO holidays VALUES("HL0004","ครม. ประกาศให้เป็นวันหยุดพิเศษ","2015-05-04","2015-05-04");
INSERT INTO holidays VALUES("HL0005","วันสงกรานต์ 2558","2015-04-13","2015-04-15");
INSERT INTO holidays VALUES("HL0006","ดกก","2015-04-22","2015-04-22");



DROP TABLE IF EXISTS order_details;

CREATE TABLE `order_details` (
  `orddtl_id` char(9) collate utf8_unicode_ci NOT NULL,
  `ord_id` char(7) collate utf8_unicode_ci NOT NULL,
  `prd_id` char(6) collate utf8_unicode_ci NOT NULL,
  `orddtl_amount` int(11) NOT NULL,
  PRIMARY KEY  (`orddtl_id`),
  KEY `ord_id` (`ord_id`),
  KEY `prd_id` (`prd_id`),
  CONSTRAINT `order_details_ibfk_1` FOREIGN KEY (`ord_id`) REFERENCES `orders` (`ord_id`),
  CONSTRAINT `order_details_ibfk_2` FOREIGN KEY (`prd_id`) REFERENCES `products` (`prd_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO order_details VALUES("OD0000001","OR00001","PD0041","10");
INSERT INTO order_details VALUES("OD0000002","OR00002","PD0029","10");
INSERT INTO order_details VALUES("OD0000003","OR00003","PD0042","10");



DROP TABLE IF EXISTS order_status;

CREATE TABLE `order_status` (
  `ordstat_id` char(4) collate utf8_unicode_ci NOT NULL,
  `ordstat_name` varchar(50) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ordstat_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO order_status VALUES("OS01","ใบสั่งซื้อ");
INSERT INTO order_status VALUES("OS02","ค้างรับ");
INSERT INTO order_status VALUES("OS03","รับครบทั้งหมดแล้ว");
INSERT INTO order_status VALUES("OS04","รับไม่ครบตามที่สั่งซื้อ");



DROP TABLE IF EXISTS order_types;

CREATE TABLE `order_types` (
  `ordtyp_id` char(3) collate utf8_unicode_ci NOT NULL,
  `ordtyp_name` varchar(50) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ordtyp_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO order_types VALUES("OT1","สั่งซื้อเพื่อขาย");
INSERT INTO order_types VALUES("OT2","สั่งซื้อเพื่อใช้ในสปา");
INSERT INTO order_types VALUES("OT3","สั่งซื้อเพื่อบริจาค");



DROP TABLE IF EXISTS orders;

CREATE TABLE `orders` (
  `ord_id` char(7) collate utf8_unicode_ci NOT NULL,
  `ordtyp_id` char(3) collate utf8_unicode_ci NOT NULL,
  `emp_id` char(6) collate utf8_unicode_ci NOT NULL,
  `comp_id` char(4) collate utf8_unicode_ci NOT NULL,
  `ord_date` date NOT NULL,
  `ord_snd_date` date default NULL,
  `ordstat_id` char(4) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ord_id`),
  KEY `ordtyp_id` (`ordtyp_id`),
  KEY `emp_id` (`emp_id`),
  KEY `comp_id` (`comp_id`),
  KEY `ordstat_id` (`ordstat_id`),
  CONSTRAINT `orders_ibfk_4` FOREIGN KEY (`ordtyp_id`) REFERENCES `order_types` (`ordtyp_id`),
  CONSTRAINT `orders_ibfk_5` FOREIGN KEY (`emp_id`) REFERENCES `employees` (`emp_id`),
  CONSTRAINT `orders_ibfk_6` FOREIGN KEY (`comp_id`) REFERENCES `companies` (`comp_id`),
  CONSTRAINT `orders_ibfk_7` FOREIGN KEY (`ordstat_id`) REFERENCES `order_status` (`ordstat_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO orders VALUES("OR00001","OT1","EM0002","CP02","2015-03-28","","OS03");
INSERT INTO orders VALUES("OR00002","OT2","EM0002","CP04","2015-03-28","","OS03");
INSERT INTO orders VALUES("OR00003","OT2","EM0002","CP02","2015-03-28","","OS03");



DROP TABLE IF EXISTS overtime_rates;

CREATE TABLE `overtime_rates` (
  `otr_id` char(4) collate utf8_unicode_ci NOT NULL,
  `otr_name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `otr_time_in` time NOT NULL,
  `otr_time_out` time NOT NULL,
  `otr_otstarttime_daywork` time NOT NULL,
  `otr_otendtime_daywork` time NOT NULL,
  `otr_otstarttime_dayoff` time NOT NULL,
  `otr_otendtime_dayoff` time NOT NULL,
  `otr_bath_per_hour` float NOT NULL,
  PRIMARY KEY  (`otr_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO overtime_rates VALUES("TR01","อัตราค่าล่วงเวลาพนักงานทั่วไป","08:00:00","16:00:00","16:00:00","20:00:00","08:00:00","20:00:00","37.5");
INSERT INTO overtime_rates VALUES("TR02","อัตราค่าล่วงเวลาแพทย์","08:00:00","14:00:00","16:01:00","20:00:00","08:00:00","20:00:00","75");



DROP TABLE IF EXISTS package_details;

CREATE TABLE `package_details` (
  `pkgdtl_id` char(15) collate utf8_unicode_ci NOT NULL,
  `sersvt_id` char(15) collate utf8_unicode_ci NOT NULL,
  `emp_id` char(6) collate utf8_unicode_ci NOT NULL,
  `pkgdtl_com` float NOT NULL,
  PRIMARY KEY  (`pkgdtl_id`),
  KEY `emp_id` (`emp_id`),
  KEY `sersvt_id` (`sersvt_id`),
  CONSTRAINT `package_details_ibfk_2` FOREIGN KEY (`sersvt_id`) REFERENCES `service_service_list_times` (`sersvt_id`),
  CONSTRAINT `package_details_ibfk_3` FOREIGN KEY (`emp_id`) REFERENCES `employees` (`emp_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO package_details VALUES("KD0000000000001","ST0000000000001","EM0011","17.5");
INSERT INTO package_details VALUES("KD0000000000002","ST0000000000002","EM0011","50");
INSERT INTO package_details VALUES("KD0000000000003","ST0000000000003","EM0011","140");
INSERT INTO package_details VALUES("KD0000000000004","ST0000000000001","EM0002","32.5");
INSERT INTO package_details VALUES("KD0000000000005","ST0000000000004","EM0011","300");
INSERT INTO package_details VALUES("KD0000000000006","ST0000000000005","EM0011","100");
INSERT INTO package_details VALUES("KD0000000000015","ST0000000000014","EM0011","50");
INSERT INTO package_details VALUES("KD0000000000016","ST0000000000015","EM0011","50");
INSERT INTO package_details VALUES("KD0000000000017","ST0000000000016","EM0011","140");
INSERT INTO package_details VALUES("KD0000000000018","ST0000000000017","EM0011","50");
INSERT INTO package_details VALUES("KD0000000000019","ST0000000000018","EM0011","50");
INSERT INTO package_details VALUES("KD0000000000020","ST0000000000019","EM0011","140");
INSERT INTO package_details VALUES("KD0000000000021","ST0000000000020","EM0003","300");
INSERT INTO package_details VALUES("KD0000000000022","ST0000000000021","EM0005","100");
INSERT INTO package_details VALUES("KD0000000000023","ST0000000000022","EM0004","200");
INSERT INTO package_details VALUES("KD0000000000024","ST0000000000023","EM0004","40");
INSERT INTO package_details VALUES("KD0000000000025","ST0000000000024","EM0004","100");
INSERT INTO package_details VALUES("KD0000000000026","ST0000000000025","EM0004","100");
INSERT INTO package_details VALUES("KD0000000000027","ST0000000000026","EM0004","80");
INSERT INTO package_details VALUES("KD0000000000028","ST0000000000027","EM0011","50");
INSERT INTO package_details VALUES("KD0000000000029","ST0000000000028","EM0011","50");
INSERT INTO package_details VALUES("KD0000000000030","ST0000000000029","EM0011","140");



DROP TABLE IF EXISTS package_promotion_details;

CREATE TABLE `package_promotion_details` (
  `pkgprmdtl_id` char(10) collate utf8_unicode_ci NOT NULL,
  `pkgprm_id` char(6) collate utf8_unicode_ci NOT NULL,
  `pkg_id` char(5) collate utf8_unicode_ci NOT NULL,
  `pkgprmdtl_startdate` date NOT NULL,
  `pkgprmdtl_enddate` date default NULL,
  `pkgprmdtl_discout` float NOT NULL,
  `pkgprmdtl_discout_type` varchar(30) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`pkgprmdtl_id`),
  KEY `pkgprm_id` (`pkgprm_id`),
  KEY `pkg_id` (`pkg_id`),
  CONSTRAINT `package_promotion_details_ibfk_2` FOREIGN KEY (`pkgprm_id`) REFERENCES `package_promotions` (`pkgprm_id`),
  CONSTRAINT `package_promotion_details_ibfk_3` FOREIGN KEY (`pkg_id`) REFERENCES `packages` (`pkg_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO package_promotion_details VALUES("PE00000001","PG0001","PK001","2015-03-19","2015-04-22","100","บาท");
INSERT INTO package_promotion_details VALUES("PE00000002","PG0001","PK007","2015-03-22","2015-04-28","50","%");
INSERT INTO package_promotion_details VALUES("PE00000003","PG0001","PK002","2015-03-21","2015-03-31","10","%");
INSERT INTO package_promotion_details VALUES("PE00000004","PG0001","PK003","2015-04-23","2015-04-30","50","%");
INSERT INTO package_promotion_details VALUES("PE00000005","PG0001","PK005","2015-04-23","2015-04-30","50","%");
INSERT INTO package_promotion_details VALUES("PE00000006","PG0001","PK001","2015-04-23","2015-04-30","50","%");
INSERT INTO package_promotion_details VALUES("PE00000007","PG0001","PK006","2015-04-23","2015-04-30","50","%");
INSERT INTO package_promotion_details VALUES("PE00000008","PG0001","PK002","2015-04-23","2015-04-30","50","%");
INSERT INTO package_promotion_details VALUES("PE00000009","PG0001","PK008","2015-04-23","2015-04-30","50","%");
INSERT INTO package_promotion_details VALUES("PE00000010","PG0002","PK003","2015-05-02","","50","%");



DROP TABLE IF EXISTS package_promotions;

CREATE TABLE `package_promotions` (
  `pkgprm_id` char(6) collate utf8_unicode_ci NOT NULL,
  `pkgprm_name` varchar(150) collate utf8_unicode_ci NOT NULL,
  `pkgprm_startdate` date NOT NULL,
  `pkgprm_enddate` date default NULL,
  `pkgprm_pic` varchar(150) collate utf8_unicode_ci default NULL,
  `pkgprm_desc` varchar(255) collate utf8_unicode_ci default NULL,
  `custype_id` char(3) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`pkgprm_id`),
  KEY `custype_id` (`custype_id`),
  CONSTRAINT `package_promotions_ibfk_1` FOREIGN KEY (`custype_id`) REFERENCES `customer_types` (`custype_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO package_promotions VALUES("PG0001","ลดราคาต้อนรับเทศกาลสงกานต์","2015-03-19","2015-04-30","","","CT2");
INSERT INTO package_promotions VALUES("PG0002","test","2015-05-02","","","","CT2");



DROP TABLE IF EXISTS package_service_lists;

CREATE TABLE `package_service_lists` (
  `pkgsvl_id` char(8) collate utf8_unicode_ci NOT NULL,
  `svl_id` char(5) collate utf8_unicode_ci NOT NULL,
  `pkg_id` char(5) collate utf8_unicode_ci NOT NULL,
  `pkgsvl_hr` int(2) default NULL,
  `pkgsvl_min` int(2) default NULL,
  `pkgsvl_price` int(11) NOT NULL,
  PRIMARY KEY  (`pkgsvl_id`),
  KEY `svl_id` (`svl_id`),
  KEY `pkg_id` (`pkg_id`),
  CONSTRAINT `package_service_lists_ibfk_1` FOREIGN KEY (`svl_id`) REFERENCES `service_lists` (`svl_id`),
  CONSTRAINT `package_service_lists_ibfk_2` FOREIGN KEY (`pkg_id`) REFERENCES `packages` (`pkg_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO package_service_lists VALUES("PS000001","SL020","PK001","","","0");
INSERT INTO package_service_lists VALUES("PS000004","SL017","PK001","","30","500");
INSERT INTO package_service_lists VALUES("PS000007","SL018","PK002","","30","500");
INSERT INTO package_service_lists VALUES("PS000010","SL027","PK003","1","","500");
INSERT INTO package_service_lists VALUES("PS000011","SL020","PK003","","","0");
INSERT INTO package_service_lists VALUES("PS000013","SL018","PK003","","30","500");
INSERT INTO package_service_lists VALUES("PS000019","SL001","PK005","1","","1000");
INSERT INTO package_service_lists VALUES("PS000021","SL018","PK001","","30","500");
INSERT INTO package_service_lists VALUES("PS000022","SL004","PK001","1","30","1400");
INSERT INTO package_service_lists VALUES("PS000024","SL005","PK002","1","","700");
INSERT INTO package_service_lists VALUES("PS000025","SL012","PK002","1","","1000");
INSERT INTO package_service_lists VALUES("PS000026","SL004","PK003","1","30","1400");
INSERT INTO package_service_lists VALUES("PS000029","SL024","PK005","","30","200");
INSERT INTO package_service_lists VALUES("PS000030","SL021","PK005","","","0");
INSERT INTO package_service_lists VALUES("PS000033","SL002","PK006","1","30","1500");
INSERT INTO package_service_lists VALUES("PS000034","SL017","PK006","","30","500");
INSERT INTO package_service_lists VALUES("PS000035","SL020","PK006","","","0");
INSERT INTO package_service_lists VALUES("PS000036","SL003","PK007","1","30","1500");
INSERT INTO package_service_lists VALUES("PS000037","SL017","PK007","","30","500");
INSERT INTO package_service_lists VALUES("PS000038","SL020","PK007","","","0");
INSERT INTO package_service_lists VALUES("PS000039","SL003","PK008","","","1500");
INSERT INTO package_service_lists VALUES("PS000040","SL025","PK008","","","900");
INSERT INTO package_service_lists VALUES("PS000041","SL020","PK008","","","0");
INSERT INTO package_service_lists VALUES("PS000042","SL023","PK005","0","30","500");
INSERT INTO package_service_lists VALUES("PS000043","SL022","PK005","0","0","0");
INSERT INTO package_service_lists VALUES("PS000044","SL018","PK005","0","30","500");
INSERT INTO package_service_lists VALUES("PS000045","SL007","PK005","0","30","400");
INSERT INTO package_service_lists VALUES("PS000046","SL020","PK002","0","0","0");



DROP TABLE IF EXISTS packages;

CREATE TABLE `packages` (
  `pkg_id` char(5) collate utf8_unicode_ci NOT NULL,
  `pkg_name` varchar(50) collate utf8_unicode_ci NOT NULL,
  `pkg_start` date NOT NULL,
  `pkg_stop` date default NULL,
  `pkg_desc` varchar(300) collate utf8_unicode_ci default NULL,
  `pkg_price` float NOT NULL,
  `pkg_picture` varchar(150) collate utf8_unicode_ci default NULL,
  PRIMARY KEY  (`pkg_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO packages VALUES("PK001","ปรับธาตุ","2014-09-05","2015-04-30","เป็นการปรับสมดุลร่างกาย ให้สุขภาพดี ทั้งภายในและภายนอก","2400","PK001.jpg");
INSERT INTO packages VALUES("PK002","หน้าสวยผิวใส","2014-09-05","","หน้าใส หอมอบสมุนไพร ผ่อนคลาย รู้สึกเหมือนผิวเกิดใหม่","2200","PK002.jpg");
INSERT INTO packages VALUES("PK003","Detox ล้างพิษ","2014-09-05","2015-06-14","เป็นแพ็คเกจล้างสารพิษให้ผิวแลดูสุขภาพดีจากเดิม ดูกระจ่างใสเนี่ยนนุ่ม ชุ่มชื่น","2400","PK003.jpeg");
INSERT INTO packages VALUES("PK005","ดูแลมารดาหลังคลอด","2014-09-05","","แพ็คเกจดูแลมารดาหลังคลอดปรับสมดุลร่างกาย ","2600","PK005.jpeg");
INSERT INTO packages VALUES("PK006","ปรับสมดุลสตรี","2015-03-19","","","2000","PK006.jpeg");
INSERT INTO packages VALUES("PK007","ปรับสมดุลลำไส้","2015-03-19","","","2000","PK007.jpeg");
INSERT INTO packages VALUES("PK008","เผายา","2015-03-19","","","2400","PK008.jpeg");



DROP TABLE IF EXISTS pay_types;

CREATE TABLE `pay_types` (
  `paytyp_id` char(4) collate utf8_unicode_ci NOT NULL,
  `paytyp_name` varchar(50) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`paytyp_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO pay_types VALUES("PA01","เงินสด");
INSERT INTO pay_types VALUES("PA02","บัตรเครดิต");



DROP TABLE IF EXISTS payrolls;

CREATE TABLE `payrolls` (
  `payroll_id` char(7) collate utf8_unicode_ci NOT NULL,
  `emp_id` char(6) collate utf8_unicode_ci NOT NULL,
  `emp_give_id` char(6) collate utf8_unicode_ci NOT NULL,
  `payroll_salary` float NOT NULL,
  `payroll_commission` float NOT NULL,
  `payroll_overtime` float NOT NULL,
  `payroll_monthly` date NOT NULL,
  `payroll_date` date NOT NULL,
  PRIMARY KEY  (`payroll_id`),
  KEY `emp_id` (`emp_id`),
  KEY `emp_give_id` (`emp_give_id`),
  CONSTRAINT `payrolls_ibfk_1` FOREIGN KEY (`emp_id`) REFERENCES `employees` (`emp_id`),
  CONSTRAINT `payrolls_ibfk_2` FOREIGN KEY (`emp_give_id`) REFERENCES `employees` (`emp_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;




DROP TABLE IF EXISTS positions;

CREATE TABLE `positions` (
  `pos_id` char(3) collate utf8_unicode_ci NOT NULL,
  `pos_name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `otr_id` char(4) collate utf8_unicode_ci default NULL,
  PRIMARY KEY  (`pos_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO positions VALUES("P01","ผู้ดูแลระบบ","");
INSERT INTO positions VALUES("P02","แพทย์","TR02");
INSERT INTO positions VALUES("P03","พนักงานนวด","TR01");
INSERT INTO positions VALUES("P04","พนักงานขาย","");



DROP TABLE IF EXISTS print_purchase_orders;

CREATE TABLE `print_purchase_orders` (
  `prtord_id` char(9) collate utf8_unicode_ci NOT NULL,
  `ord_id` char(7) collate utf8_unicode_ci NOT NULL,
  `emp_id` char(6) collate utf8_unicode_ci NOT NULL,
  `prtord_date` date NOT NULL,
  `prtord_time` time NOT NULL,
  PRIMARY KEY  (`prtord_id`),
  KEY `ord_id` (`ord_id`),
  KEY `emp_id` (`emp_id`),
  CONSTRAINT `print_purchase_orders_ibfk_1` FOREIGN KEY (`ord_id`) REFERENCES `orders` (`ord_id`),
  CONSTRAINT `print_purchase_orders_ibfk_2` FOREIGN KEY (`emp_id`) REFERENCES `employees` (`emp_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;




DROP TABLE IF EXISTS privileges;

CREATE TABLE `privileges` (
  `privlg_id` char(5) collate utf8_unicode_ci NOT NULL,
  `privlg_name` varchar(150) collate utf8_unicode_ci NOT NULL,
  `privlg_name_th` varchar(150) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`privlg_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO privileges VALUES("PV001","view_withdraws","ดูข้อมูลการเบิก");
INSERT INTO privileges VALUES("PV002","insert_withdraws","เพิ่มการเบิก");
INSERT INTO privileges VALUES("PV003","update_withdraws","แก้ไขการเบิก");
INSERT INTO privileges VALUES("PV004","delete_withdraws","ลบการเบิก");
INSERT INTO privileges VALUES("PV005","view_sales","ดูข้อมูลการขาย");
INSERT INTO privileges VALUES("PV006","insert_sales","เพิ่มการขาย");
INSERT INTO privileges VALUES("PV007","update_sales","แก้ไขการขาย");
INSERT INTO privileges VALUES("PV008","delete_sales","ลบการขาย");
INSERT INTO privileges VALUES("PV009","print_sales_receipt","พิมพ์ใบเสร็จการขาย");
INSERT INTO privileges VALUES("PV010","manage_orders","จัดการข้อมูลการสั่งซื้อ");
INSERT INTO privileges VALUES("PV011","manage_standard_tables","จัดการข้อมูลพื้นฐาน");
INSERT INTO privileges VALUES("PV012","point_of_sale","ระบบขายหน้าร้าน");
INSERT INTO privileges VALUES("PV013","manage_services","จัดการข้อมูลการใช้บริการ");
INSERT INTO privileges VALUES("PV014","manage_booking","จัดการข้อมูลการจอง");
INSERT INTO privileges VALUES("PV015","manage_employees","จัดการข้อมูลพนักงาน");
INSERT INTO privileges VALUES("PV016","manage_customers","จัดการข้อมูลผู้ใช้บริการ");
INSERT INTO privileges VALUES("PV017","manage_spa","จัดการข้อมูลสปา");
INSERT INTO privileges VALUES("PV018","manage_shops","จัดการข้อมูลร้านค้า");
INSERT INTO privileges VALUES("PV019","manage_packages","จัดการข้อมูลแพ็คเกจ และโปรโมชั่น");
INSERT INTO privileges VALUES("PV020","manage_service_lists","จัดการข้อมูลรายการบริการ และโปรโมชั่น");
INSERT INTO privileges VALUES("PV021","manage_companies","จัดการข้อมูลบริษัทจำหน่ายผลิตภัณฑ์");
INSERT INTO privileges VALUES("PV022","manage_products","จัดการข้อมูลผลิตภัณฑ์ และโปรโมชั่น");
INSERT INTO privileges VALUES("PV023","manage_promotion_discout_sales","จัดการข้อมูลโปรโมชั่นการขาย");
INSERT INTO privileges VALUES("PV024","manage_advertising","จัดการข้อมูลประชาสัมพันธ์หน้าการเข้า-ออกงาน");
INSERT INTO privileges VALUES("PV025","manage_time_attendances","จัดการข้อมูลการเข้า-ออกงาน");
INSERT INTO privileges VALUES("PV026","manage_payrolls","จัดการข้อมูลการจ่ายเงินเดือน");
INSERT INTO privileges VALUES("PV027","manage_element_checks","จัดการข้อมูลการตรวจธาตุ");
INSERT INTO privileges VALUES("PV028","manage_receives","จัดการข้อมูลการรับ");
INSERT INTO privileges VALUES("PV029","view_report","การออกรายงาน");
INSERT INTO privileges VALUES("PV030","manage_website","จัดการเว็บไซต์");



DROP TABLE IF EXISTS product_promotion_groups;

CREATE TABLE `product_promotion_groups` (
  `prdprmgrp_id` char(4) collate utf8_unicode_ci NOT NULL,
  `prdprmgrp_name` varchar(100) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`prdprmgrp_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO product_promotion_groups VALUES("DG01","โปรโมชั่นสำหรับลูกค้า");
INSERT INTO product_promotion_groups VALUES("DG02","โปรโมชั่นสำหรับพนักงาน");
INSERT INTO product_promotion_groups VALUES("DG03","โปรโมชั่นสำหรับสมาชิก");



DROP TABLE IF EXISTS product_promotions;

CREATE TABLE `product_promotions` (
  `prdprm_id` char(6) collate utf8_unicode_ci NOT NULL,
  `prdprm_name` varchar(150) collate utf8_unicode_ci NOT NULL,
  `prdprm_startdate` date NOT NULL,
  `prdprm_enddate` date default NULL,
  `prdprm_picture` varchar(150) collate utf8_unicode_ci default NULL,
  `prdprm_desc` varchar(300) collate utf8_unicode_ci default NULL,
  `prdprm_type` varchar(20) collate utf8_unicode_ci NOT NULL,
  `prdprmgrp_id` char(4) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`prdprm_id`),
  KEY `prdprmgrp_id` (`prdprmgrp_id`),
  CONSTRAINT `product_promotions_ibfk_1` FOREIGN KEY (`prdprmgrp_id`) REFERENCES `product_promotion_groups` (`prdprmgrp_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO product_promotions VALUES("PP0001","test","2015-03-21","","","","ลดราคา","DG03");
INSERT INTO product_promotions VALUES("PP0002","ซื้อกาแฟครบ 10 แก้ว ฟรี 1 แก้ว","2015-03-30","2015-04-01","","","ฟรี","DG01");



DROP TABLE IF EXISTS product_types;

CREATE TABLE `product_types` (
  `prdtyp_id` char(4) collate utf8_unicode_ci NOT NULL,
  `prdtyp_name` char(100) collate utf8_unicode_ci NOT NULL,
  `prdtyp_group` varchar(255) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`prdtyp_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO product_types VALUES("PT01","ไม่ระบุประเภท","ไม่ระบุกลุ่ม");
INSERT INTO product_types VALUES("PT02","แชมพู","ผลิตภัณฑ์");
INSERT INTO product_types VALUES("PT03","ครีมนวด","ผลิตภัณฑ์");
INSERT INTO product_types VALUES("PT04","โลชั่น","ผลิตภัณฑ์");
INSERT INTO product_types VALUES("PT05","สบู่เหลว","ผลิตภัณฑ์");
INSERT INTO product_types VALUES("PT06","กาแฟร้อน","เครื่องดื่ม");
INSERT INTO product_types VALUES("PT07","กาแฟเย็น","เครื่องดื่ม");
INSERT INTO product_types VALUES("PT08","ชาสมุนไพรแบบถ้วย","เครื่องดื่ม");
INSERT INTO product_types VALUES("PT09","เครื่องดื่มสมุนไพร","เครื่องดื่ม");
INSERT INTO product_types VALUES("PT10","อาหารและขนมหวาน","อาหาร");
INSERT INTO product_types VALUES("PT11","ชาสมุนไพรแบบกา","เครื่องดื่ม");



DROP TABLE IF EXISTS products;

CREATE TABLE `products` (
  `prd_id` char(6) collate utf8_unicode_ci NOT NULL,
  `prdtyp_id` char(4) collate utf8_unicode_ci NOT NULL,
  `unit_id` char(3) collate utf8_unicode_ci NOT NULL,
  `brand_id` char(4) collate utf8_unicode_ci NOT NULL,
  `prd_name` varchar(100) collate utf8_unicode_ci NOT NULL,
  `prd_price` float NOT NULL default '0',
  `prd_amount` int(11) default NULL,
  `prd_shelf_amount` int(11) default NULL,
  `prd_pic` varchar(255) collate utf8_unicode_ci default NULL,
  `prd_desc` varchar(300) collate utf8_unicode_ci default NULL,
  `prd_barcode` varchar(255) collate utf8_unicode_ci default NULL,
  `prd_amount_max` int(11) default NULL,
  `prd_amount_min` int(11) default NULL,
  PRIMARY KEY  (`prd_id`),
  KEY `prdtyp_id` (`prdtyp_id`),
  KEY `unit_id` (`unit_id`),
  KEY `brand_id` (`brand_id`),
  CONSTRAINT `products_ibfk_15` FOREIGN KEY (`prdtyp_id`) REFERENCES `product_types` (`prdtyp_id`),
  CONSTRAINT `products_ibfk_16` FOREIGN KEY (`unit_id`) REFERENCES `units` (`unit_id`),
  CONSTRAINT `products_ibfk_17` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`brand_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO products VALUES("PD0001","PT01","U05","BR03","Cellulite Control Cream (Bulk)","390","","","PD0001.jpeg","","5406021620116","","");
INSERT INTO products VALUES("PD0002","PT01","U05","BR02","Bamboo Mask Powder (Bulk)","250","750","45","PD0002.jpeg","","","","");
INSERT INTO products VALUES("PD0003","PT01","U04","BR05","Whitening Body Mask Cream","500","","","PD0003.jpeg","","","","");
INSERT INTO products VALUES("PD0004","PT01","U03","BR03","Whitening Jelly Mask","460","","","PD0004.jpeg","","","","");
INSERT INTO products VALUES("PD0005","PT01","U03","BR01","Herbal Massage Cream (Bulk)","140","210","50","PD0005.jpeg","","","100","10");
INSERT INTO products VALUES("PD0006","PT01","U03","BR01","Basic Massage Oil (Bulk)","300","","","PD0006.jpeg","","","","");
INSERT INTO products VALUES("PD0007","PT01","U04","BR03","Cellulite Control Gel (Bulk)","390","625","50","PD0007.jpeg","","","50","10");
INSERT INTO products VALUES("PD0008","PT01","U03","BR05","Body Scrub Gel (Bulk)","180","210","50","PD0008.jpeg","","","200","5");
INSERT INTO products VALUES("PD0009","PT01","U04","BR04","Rose Aroma Bath Gel (Bulk)","400","0","50","PD0009.jpeg","","","100","5");
INSERT INTO products VALUES("PD0010","PT01","U03","BR04","Rose Aroma Lotion (Bulk)","100","20","50","PD0010.jpeg","","","500","10");
INSERT INTO products VALUES("PD0011","PT01","U03","BR02","Rose Aroma Shampoo (Bulk)","200","100","50","PD0011.jpeg","","","200","10");
INSERT INTO products VALUES("PD0012","PT01","U03","BR05","Rose Aroma Conditioner (Bulk)","239","120","50","PD0012.jpeg","","","200","10");
INSERT INTO products VALUES("PD0013","PT01","U05","BR02","Cellulite Control Gel","200","100","49","PD0013.jpeg","","","500","5");
INSERT INTO products VALUES("PD0014","PT01","U02","BR02","Body Scrub Gel Rose","40","450","49","PD0014.jpeg","","","100","5");
INSERT INTO products VALUES("PD0015","PT02","U04","BR02","Aroma Shampoo Ylang Ylang","410","200","49","PD0015.jpeg","","","100","5");
INSERT INTO products VALUES("PD0016","PT02","U03","BR05","Aroma Shampoo Rose","800","0","49","PD0016.jpeg","","","100","5");
INSERT INTO products VALUES("PD0017","PT02","U03","BR02","Aroma Shampoo Moke","160","10","47","PD0017.jpeg","","","100","5");
INSERT INTO products VALUES("PD0018","PT03","U03","BR02","Aroma Conditioner Ylang Ylang","150","0","49","PD0018.jpeg","","","100","5");
INSERT INTO products VALUES("PD0019","PT03","U03","BR05","Aroma Conditioner Rose","140","69","42","PD0019.jpeg","","","100","5");
INSERT INTO products VALUES("PD0020","PT03","U03","BR05","Aroma Conditioner Moke","150","15","43","PD0020.jpeg","","885155341304","100","5");
INSERT INTO products VALUES("PD0021","PT05","U03","BR05","Aroma Bath Gel Ylang Ylang","230","110","38","PD0021.jpeg","","885155341303","200","5");
INSERT INTO products VALUES("PD0022","PT05","U04","BR05","Aroma Bath Gel Rose","240","1000","47","PD0022.jpeg","","885155341302","100","5");
INSERT INTO products VALUES("PD0023","PT05","U05","BR05","Aroma Bath Gel Moke","100","903","43","PD0023.jpeg","","885155341301","1000","100");
INSERT INTO products VALUES("PD0024","PT04","U04","BR05","Aroma Lotion Ylang Ylang","220","0","44","PD0024.jpeg","","","1000","10");
INSERT INTO products VALUES("PD0025","PT04","U03","BR02","Aroma Lotion Rose","200","120","49","PD0025.jpeg","","885155341305","100","5");
INSERT INTO products VALUES("PD0026","PT04","U03","BR02","Aroma Lotion Moke","200","100","49","PD0026.jpeg","","","200","5");
INSERT INTO products VALUES("PD0027","PT06","U07","BR06","Espresso เอสเพรสโซ่ หมามุ่ย(ร้อน)","35","","","PD0027.jpeg","กาแฟเอสเพรสโซ่เข้มข้น ผสมเมล็ดหมามุ่ย","","","");
INSERT INTO products VALUES("PD0028","PT07","U07","BR06","Espresso เอสเพรสโซ่ หมามุ่ย(เย็น)","50","","","PD0028.jpeg","กาแฟเอสเพรสโซ่เข้มข้น ผสมเมล็ดหมามุ่ย","","","");
INSERT INTO products VALUES("PD0029","PT06","U07","BR06","Americano อเมริกาโน่ หมามุ่ย(ร้อน)","35","10","","PD0029.jpeg","กาแฟเอสเพรสโซ่ผสมหมามุ่ยเข้มข้น ผสมน้ำร้อน","","","");
INSERT INTO products VALUES("PD0030","PT07","U07","BR06","Americano อเมริกาโน่ หมามุ่ย(เย็น)","50","","","PD0030.jpeg","","","","");
INSERT INTO products VALUES("PD0031","PT06","U07","BR06","Cappuccino คาปูชิโน หมามุ่ย(ร้อน)","40","","","PD0031.jpeg","กาแฟเอสเพรสโช่หมามุ่ยเข้มข้น","","","");
INSERT INTO products VALUES("PD0032","PT07","U07","BR06","Cappuccino คาปูชิโน หมามุ่ย(เย็น)","60","","","PD0032.jpeg","","","","");
INSERT INTO products VALUES("PD0033","PT06","U07","BR06","Latte ลาเต้ หมามุ่ย(ร้อน)","40","","","PD0033.jpeg","กาแฟเอสเพรสโซหมามุ่ยเข้มข้น ผสมนมสดร้อนและฟองนม","","","");
INSERT INTO products VALUES("PD0034","PT07","U07","BR06","Latte ลาเต้ หมามุ่ย(เย็น)","60","","","PD0034.jpeg","","","","");
INSERT INTO products VALUES("PD0035","PT06","U07","BR06","Mocha มอคค่า หมามุ่ย(ร้อน)","40","","","PD0035.jpeg","กาแฟเอสเพรสโชหมามุ่ยเข้มข้น ผสมผงโกโก้และนมสดร้อน","","","");
INSERT INTO products VALUES("PD0036","PT07","U07","BR06","Mocha มอคค่า หมามุ่ย(เย็น)","60","","","PD0036.jpeg","","","","");
INSERT INTO products VALUES("PD0037","PT06","U07","BR06","Cocoa โกโก้ หมามุ่ย(ร้อน)","40","","","PD0037.jpeg","โกโก้ร้อนเข้มข้น","","","");
INSERT INTO products VALUES("PD0038","PT07","U07","BR06","Cocoa โกโก้ หมามุ่ย(เย็น)","60","","","PD0038.jpeg","","","","");
INSERT INTO products VALUES("PD0039","PT06","U07","BR06","Espresso เอสเพรสโซ รากสามสิบ(ร้อน)","35","","","PD0039.jpeg","","","","");
INSERT INTO products VALUES("PD0040","PT07","U07","BR06","Espresso เอสเพรสโซ รากสามสิบ(เย็น)","60","","","PD0040.jpeg","กาแฟเข้มข้น ผสมรากสามสิบ","","","");
INSERT INTO products VALUES("PD0041","PT06","U07","BR06","Americano อเมริกาโน รากสามสิบ(ร้อน)","35","10","","PD0041.jpeg","","","","");
INSERT INTO products VALUES("PD0042","PT07","U07","BR06","Americano อเมริกาโน รากสามสิบ(เย็น)","50","10","","PD0042.jpeg","","","","");
INSERT INTO products VALUES("PD0043","PT06","U07","BR06","Cappuccino คาปูชิโน รากสามสิบ(ร้อน)","40","","","PD0043.jpeg","กาแฟเอสเพรสโซรากสามสิบเข้มข้น ผสมนมร้อนและฟองนมหนานุ่ม","","","");
INSERT INTO products VALUES("PD0044","PT07","U07","BR06","Cappuccino คาปูชิโน รากสามสิบ(เย็น)","60","","","PD0044.jpeg","","","","");
INSERT INTO products VALUES("PD0045","PT06","U07","BR06","Latte ลาเต้ รากสามสิบ(ร้อน)","40","","","PD0045.jpeg","","","","");
INSERT INTO products VALUES("PD0046","PT07","U07","BR06","Latte ลาเต้ รากสามสิบ(เย็น)","60","","","PD0046.jpeg","","","","");
INSERT INTO products VALUES("PD0047","PT06","U07","BR06","Mocha มอคค่า รากสามสิบ(ร้อน)","40","","","PD0047.jpeg","","","","");
INSERT INTO products VALUES("PD0048","PT07","U07","BR06","Mocha มอคค่า รากสามสิบ(เย็น)","60","","","PD0048.jpeg","","","","");
INSERT INTO products VALUES("PD0049","PT06","U07","BR06","Cocoa โกโก้ รากสามสิบ(ร้อน)","40","","","PD0049.jpeg","","","","");
INSERT INTO products VALUES("PD0050","PT07","U07","BR06","Cocoa โกโก้ รากสามสิบ(เย็น)","60","","","PD0050.jpeg","","","","");
INSERT INTO products VALUES("PD0051","PT06","U07","BR06","มัทฉะลาเต้(ร้อน)","40","","","PD0051.jpeg","","","","");
INSERT INTO products VALUES("PD0052","PT07","U07","BR06","มัทฉะลาเต้(เย็น)","60","","","PD0052.jpeg","","","","");
INSERT INTO products VALUES("PD0053","PT08","U08","BR06","ชาเบญจเกสร(ถ้วย)","15","","","PD0053.jpeg","","","","");
INSERT INTO products VALUES("PD0054","PT11","U09","BR06","ชาเบญจเกสร(กา)","30","","","PD0054.jpeg","","","","");
INSERT INTO products VALUES("PD0055","PT08","U08","BR06","ชากลิ่นกายหอม(ถ้วย)","25","","","PD0055.jpeg","","","","");
INSERT INTO products VALUES("PD0056","PT11","U09","BR06","ชากลิ่นกายหอม(กา)","50","","","PD0056.jpeg","","","","");
INSERT INTO products VALUES("PD0057","PT08","U08","BR06","ชาดอกไม้(ถ้วย)","25","","","PD0057.jpeg","","","","");
INSERT INTO products VALUES("PD0058","PT11","U09","BR06","ชาดอกไม้(กา)","50","","","PD0058.jpeg","","","","");
INSERT INTO products VALUES("PD0059","PT08","U08","BR06","ชาว่านสาวหลง(ถ้วย)","15","","","PD0059.jpeg","","","","");
INSERT INTO products VALUES("PD0060","PT11","U09","BR06","ชาว่านสาวหลง(กา)","30","","","PD0060.jpeg","","","","");
INSERT INTO products VALUES("PD0061","PT08","U08","BR06","ชาอัญชันใบเตย(ถ้วย)","15","","","PD0061.jpeg","","","","");
INSERT INTO products VALUES("PD0062","PT11","U09","BR06","ชาอัญชันใบเตย(กา)","30","","","PD0062.jpeg","","","","");
INSERT INTO products VALUES("PD0063","PT08","U08","BR06","ชารางจืดใบเตย(ถ้วย)","15","","","PD0063.jpeg","","","","");
INSERT INTO products VALUES("PD0064","PT11","U09","BR06","ชารางจืดใบเตย(กา)","30","","","PD0064.jpeg","","","","");
INSERT INTO products VALUES("PD0065","PT08","U08","BR06","ชาดอกคำฝอย(ถ้วย)","15","","","PD0065.jpeg","","","","");
INSERT INTO products VALUES("PD0066","PT11","U09","BR06","ชาดอกคำฝอย(กา)","30","","","PD0066.jpeg","","","","");
INSERT INTO products VALUES("PD0067","PT08","U08","BR06","ชาดาวเรือง(ถ้วย)","25","","","PD0067.jpeg","","","","");
INSERT INTO products VALUES("PD0068","PT11","U09","BR06","ชาดาวเรือง(กา)","50","","","PD0068.jpeg","","","","");
INSERT INTO products VALUES("PD0069","PT08","U08","BR06","ชาบานไม่รู้โรย(ถ้วย)","15","","","PD0069.jpeg","","","","");
INSERT INTO products VALUES("PD0070","PT11","U09","BR06","ชาบานไม่รู้โรย(กา)","30","","","PD0070.jpeg","","","","");
INSERT INTO products VALUES("PD0071","PT08","U08","BR06","ชาใบฝรั่ง(ถ้วย)","10","","","PD0071.jpeg","","","","");
INSERT INTO products VALUES("PD0072","PT11","U09","BR06","ชาใบฝรั่ง(กา)","20","","","PD0072.jpeg","","","","");
INSERT INTO products VALUES("PD0073","PT08","U08","BR06","ชาโม่ยตัน(ถ้วย)","15","","","PD0073.jpeg","","","","");
INSERT INTO products VALUES("PD0074","PT11","U09","BR06","ชาโม่ยตัน(กา)","30","","","PD0074.jpeg","","","","");
INSERT INTO products VALUES("PD0075","PT08","U08","BR06","ชาหญ้าฮี่ยุ่ม(ถ้วย)","20","","","PD0075.jpeg","","","","");
INSERT INTO products VALUES("PD0076","PT11","U09","BR06","ชาหญ้าฮี่ยุ่ม(กา)","40","","","PD0076.jpeg","","","","");
INSERT INTO products VALUES("PD0077","PT10","U07","BR06","วุ้นเบญจรงค์","30","","","PD0077.jpeg","หลากสี รางวัลแด่หัวใจ สมอง ตับ ไต อุดมไปด้วยคุณค่าจากดอกอัญชัญ ขมิ้น ใบบัวบก กระเจี๊ยบ และดอกคำฝอย","","","");
INSERT INTO products VALUES("PD0078","PT10","U10","BR06","ขนมปังขมิ้น","15","","","PD0078.jpeg","","","","");
INSERT INTO products VALUES("PD0079","PT10","U10","BR06","ขนมปังงา","15","","","PD0079.jpeg","","","","");
INSERT INTO products VALUES("PD0080","PT10","U10","BR06","ขนมปังอัญชัน","15","","","PD0080.jpeg","ขนมปังเพื่อสุขภาพอุดมไปด้วยคุณประโยชน์ของอัญชัน สีสันน่ารับประทาน","","","");
INSERT INTO products VALUES("PD0081","PT10","U11","BR06","ช่างป่องสมุนไพร","75","","","PD0081.jpeg","อุดมไปด้วยคุณประโยชน์จากวิตามิน A D E และ K ทำจากส่วนผสมที่มีพืชผัก สมุนไร นานาชนิด","","","");
INSERT INTO products VALUES("PD0082","PT10","U11","BR06","ปอเปี๊ยสดหูเสื้อ","70","","","PD0082.jpeg","เพิ่มพลังความสดชื่น ลมหายใจผ่อนคลาย โปร่งโล่ง","","","");
INSERT INTO products VALUES("PD0083","PT10","U11","BR06","บ่อบุ้ง","100","","","PD0083.jpeg","","","","");
INSERT INTO products VALUES("PD0084","PT10","U11","BR06","สลัดผัก","100","","","PD0084.jpeg","ผักจากโครงการหลวง มากด้วยคุณค่าทางโภชนาการ","","","");
INSERT INTO products VALUES("PD0085","PT10","U08","BR06","น้ำสลัดขมิ้นชัน","20","","","PD0085.jpeg","ขมิ้นชันแก้ท้องอืด ป้องกันมะเร็ง ลดอาการอักเสบ","","","");
INSERT INTO products VALUES("PD0086","PT10","U08","BR06","น้ำสลัดโหรพา","20","","","PD0086.jpeg","","","","");
INSERT INTO products VALUES("PD0087","PT10","U08","BR06","น้ำสลัดอัญชัน","20","","","PD0087.jpeg","บำรุงสายตา ช่วยไหลเวียน","","","");
INSERT INTO products VALUES("PD0088","PT10","U08","BR06","น้ำสลัดกระเจี๊ยบ","30","","","PD0088.jpeg","ป้องกันความชรา ช่วยให้สดชื่น","","","");
INSERT INTO products VALUES("PD0089","PT10","U08","BR06","น้ำสลัดซอสญี่ปุ่น","50","","","PD0089.jpeg","หอมละมุนซอสหมัก เพื่อคนรักสุขภาพ","","","");
INSERT INTO products VALUES("PD0090","PT10","U11","BR06","โดนัทสมุนไพร","40","","","PD0090.jpeg","โดนัทรสชาติอร่อย อุดมไปด้วยสมุนไพรบำรุงสุขภาพ มีคุณประโยชน์จากใบบัวบก ขมิ้นชัน ","","","");
INSERT INTO products VALUES("PD0091","PT10","U11","BR06","วาฟเฟิลข้าวกล้อง","60","","","PD0091.jpeg","เสิร์ฟพร้อมกับแยมกระเจี๊ยบ บำรุงสุขภาพ บำรุงสมอง","","","");
INSERT INTO products VALUES("PD0092","PT10","U11","BR06","ขนมคนทีสอ","45","","","PD0092.png","บำรุงเลือกสตรี ทำจากน้ำคั้นใบคนทีสอด้วยข้าวมาคั่ว","","","");
INSERT INTO products VALUES("PD0093","PT10","U11","BR06","ม้าฮ่อ","40","","","PD0093.png","","","","");
INSERT INTO products VALUES("PD0094","PT10","U11","BR06","ขนมปังหน้าหมูโรยงา","60","","","PD0094.jpeg","","","","");
INSERT INTO products VALUES("PD0095","PT09","U07","BR06","สมูทตี้ไล่หวัดไล่ลม","60","","","PD0095.jpeg","","","","");
INSERT INTO products VALUES("PD0096","PT09","U07","BR06","สมูทตี้ดับกระหาย","60","","","PD0096.jpeg","","","","");
INSERT INTO products VALUES("PD0097","PT09","U07","BR06","สมูทตี้ชุ่มคอชื่นใจ","60","","","PD0097.jpeg","","","","");
INSERT INTO products VALUES("PD0098","PT09","U07","BR06","สมูทตี้ฟักแฟง","50","","","PD0098.jpeg","","","","");
INSERT INTO products VALUES("PD0099","PT09","U07","BR06","สมูทตี้หูเสื้อ","60","","","","","","","");
INSERT INTO products VALUES("PD0100","PT09","U07","BR06","น้ำตระไคร้น้ำผึ้งมะนาว","50","","","PD0100.jpeg","","","","");
INSERT INTO products VALUES("PD0101","PT09","U07","BR06","น้ำอัญชันมะนาว","50","","","PD0101.jpeg","","","","");
INSERT INTO products VALUES("PD0102","PT09","U07","BR06","น้ำขิงแมงลัก","50","","","PD0102.jpeg","","","","");
INSERT INTO products VALUES("PD0103","PT09","U07","BR06","น้ำตรีผลา","50","","","PD0103.jpeg","","","","");



DROP TABLE IF EXISTS promotion_discout_sale_details;

CREATE TABLE `promotion_discout_sale_details` (
  `prmdsdtl_id` char(8) collate utf8_unicode_ci NOT NULL,
  `prmds_id` char(6) collate utf8_unicode_ci NOT NULL,
  `prdtyp_id` char(4) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`prmdsdtl_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO promotion_discout_sale_details VALUES("ED000001","PC0001","PT06");
INSERT INTO promotion_discout_sale_details VALUES("ED000002","PC0001","PT07");



DROP TABLE IF EXISTS promotion_discout_sales;

CREATE TABLE `promotion_discout_sales` (
  `prmds_id` char(6) collate utf8_unicode_ci NOT NULL,
  `prdprmgrp_id` char(4) collate utf8_unicode_ci NOT NULL,
  `prmds_name` varchar(150) collate utf8_unicode_ci NOT NULL,
  `prmds_startdate` date NOT NULL,
  `prmds_enddate` date default NULL,
  `prmds_purchase` float NOT NULL,
  `prmds_discout` float NOT NULL,
  `prmds_discout_type` varchar(10) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`prmds_id`),
  KEY `prdprmgrp_id` (`prdprmgrp_id`),
  CONSTRAINT `promotion_discout_sales_ibfk_1` FOREIGN KEY (`prdprmgrp_id`) REFERENCES `product_promotion_groups` (`prdprmgrp_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO promotion_discout_sales VALUES("PC0001","DG03","ซื้อครบ 1000 บาท ลด 10%","2015-03-30","","1000","10","%");



DROP TABLE IF EXISTS promotion_products;

CREATE TABLE `promotion_products` (
  `prmprd_id` char(10) collate utf8_unicode_ci NOT NULL,
  `prd_id` char(6) collate utf8_unicode_ci NOT NULL,
  `prdprm_id` char(6) collate utf8_unicode_ci NOT NULL,
  `prmprd_startdate` date NOT NULL,
  `prmprd_enddate` date default NULL,
  `prmprd_discout` float default NULL,
  `prmprd_discout_type` varchar(20) collate utf8_unicode_ci default NULL,
  PRIMARY KEY  (`prmprd_id`),
  KEY `prd_id` (`prd_id`),
  KEY `prdprm_id` (`prdprm_id`),
  CONSTRAINT `promotion_products_ibfk_1` FOREIGN KEY (`prd_id`) REFERENCES `products` (`prd_id`),
  CONSTRAINT `promotion_products_ibfk_2` FOREIGN KEY (`prdprm_id`) REFERENCES `product_promotions` (`prdprm_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO promotion_products VALUES("RP00000001","PD0078","PP0001","2015-03-21","","20","%");
INSERT INTO promotion_products VALUES("RP00000002","PD0041","PP0002","2015-03-30","2015-04-01","","");



DROP TABLE IF EXISTS receive_details;

CREATE TABLE `receive_details` (
  `recdtl_id` char(9) collate utf8_unicode_ci NOT NULL,
  `rec_id` char(7) collate utf8_unicode_ci NOT NULL,
  `prd_id` char(6) collate utf8_unicode_ci NOT NULL,
  `recdtl_amount` int(11) NOT NULL,
  `recdtl_price` float NOT NULL,
  PRIMARY KEY  (`recdtl_id`),
  KEY `rec_id` (`rec_id`),
  KEY `prd_id` (`prd_id`),
  CONSTRAINT `receive_details_ibfk_1` FOREIGN KEY (`rec_id`) REFERENCES `receives` (`rec_id`),
  CONSTRAINT `receive_details_ibfk_2` FOREIGN KEY (`prd_id`) REFERENCES `products` (`prd_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO receive_details VALUES("RD0000001","RE00001","PD0029","5","35");
INSERT INTO receive_details VALUES("RD0000002","RE00002","PD0042","10","50");
INSERT INTO receive_details VALUES("RD0000003","RE00003","PD0029","5","35");
INSERT INTO receive_details VALUES("RD0000004","RE00004","PD0041","10","35");



DROP TABLE IF EXISTS receives;

CREATE TABLE `receives` (
  `rec_id` char(7) collate utf8_unicode_ci NOT NULL,
  `ord_id` char(7) collate utf8_unicode_ci NOT NULL,
  `emp_id` char(6) collate utf8_unicode_ci NOT NULL,
  `rec_date` date NOT NULL,
  `rec_total_price` float NOT NULL,
  PRIMARY KEY  (`rec_id`),
  KEY `ord_id` (`ord_id`),
  KEY `emp_id` (`emp_id`),
  CONSTRAINT `receives_ibfk_1` FOREIGN KEY (`ord_id`) REFERENCES `orders` (`ord_id`),
  CONSTRAINT `receives_ibfk_2` FOREIGN KEY (`emp_id`) REFERENCES `employees` (`emp_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO receives VALUES("RE00001","OR00002","EM0002","2015-03-28","175");
INSERT INTO receives VALUES("RE00002","OR00003","EM0002","2015-03-28","500");
INSERT INTO receives VALUES("RE00003","OR00002","EM0007","2015-04-01","175");
INSERT INTO receives VALUES("RE00004","OR00001","EM0002","2015-04-01","350");



DROP TABLE IF EXISTS rooms;

CREATE TABLE `rooms` (
  `room_id` char(4) collate utf8_unicode_ci NOT NULL,
  `room_name` varchar(50) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`room_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO rooms VALUES("RM01","ห้องนวดรีแล็ก");
INSERT INTO rooms VALUES("RM02","ห้องนวดแอคทีฟ");
INSERT INTO rooms VALUES("RM03","ห้องนวดส่วนตัว");



DROP TABLE IF EXISTS sale_details;

CREATE TABLE `sale_details` (
  `saledtl_id` char(12) collate utf8_unicode_ci NOT NULL,
  `sale_id` char(8) collate utf8_unicode_ci NOT NULL,
  `prd_id` char(6) collate utf8_unicode_ci NOT NULL,
  `saledtl_amount` int(11) NOT NULL,
  `saledtl_price` float NOT NULL,
  PRIMARY KEY  (`saledtl_id`),
  KEY `sale_id` (`sale_id`),
  KEY `prd_id` (`prd_id`),
  CONSTRAINT `sale_details_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`sale_id`),
  CONSTRAINT `sale_details_ibfk_2` FOREIGN KEY (`prd_id`) REFERENCES `products` (`prd_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO sale_details VALUES("SD0000000001","S0000001","PD0064","1","30");
INSERT INTO sale_details VALUES("SD0000000002","S0000001","PD0069","1","15");
INSERT INTO sale_details VALUES("SD0000000003","S0000001","PD0063","1","15");
INSERT INTO sale_details VALUES("SD0000000004","S0000001","PD0060","1","30");
INSERT INTO sale_details VALUES("SD0000000005","S0000001","PD0059","1","15");
INSERT INTO sale_details VALUES("SD0000000006","S0000001","PD0070","1","30");
INSERT INTO sale_details VALUES("SD0000000007","S0000001","PD0067","1","25");
INSERT INTO sale_details VALUES("SD0000000008","S0000001","PD0065","1","15");
INSERT INTO sale_details VALUES("SD0000000009","S0000001","PD0066","1","30");
INSERT INTO sale_details VALUES("SD0000000010","S0000001","PD0058","1","50");
INSERT INTO sale_details VALUES("SD0000000011","S0000002","PD0020","1","150");
INSERT INTO sale_details VALUES("SD0000000012","S0000002","PD0019","1","140");
INSERT INTO sale_details VALUES("SD0000000013","S0000002","PD0018","1","150");
INSERT INTO sale_details VALUES("SD0000000014","S0000002","PD0023","1","100");
INSERT INTO sale_details VALUES("SD0000000015","S0000002","PD0022","1","240");
INSERT INTO sale_details VALUES("SD0000000016","S0000002","PD0021","1","230");
INSERT INTO sale_details VALUES("SD0000000017","S0000002","PD0017","1","160");
INSERT INTO sale_details VALUES("SD0000000018","S0000002","PD0016","1","800");
INSERT INTO sale_details VALUES("SD0000000019","S0000002","PD0015","1","410");
INSERT INTO sale_details VALUES("SD0000000020","S0000002","PD0026","1","200");
INSERT INTO sale_details VALUES("SD0000000021","S0000002","PD0025","1","200");
INSERT INTO sale_details VALUES("SD0000000022","S0000002","PD0024","1","220");
INSERT INTO sale_details VALUES("SD0000000023","S0000002","PD0014","1","40");
INSERT INTO sale_details VALUES("SD0000000024","S0000002","PD0013","1","200");
INSERT INTO sale_details VALUES("SD0000000025","S0000003","PD0041","1","35");
INSERT INTO sale_details VALUES("SD0000000026","S0000003","PD0029","1","35");
INSERT INTO sale_details VALUES("SD0000000027","S0000003","PD0043","1","40");
INSERT INTO sale_details VALUES("SD0000000028","S0000003","PD0050","1","60");
INSERT INTO sale_details VALUES("SD0000000029","S0000003","PD0038","1","60");
INSERT INTO sale_details VALUES("SD0000000030","S0000003","PD0040","1","60");
INSERT INTO sale_details VALUES("SD0000000031","S0000003","PD0066","1","30");
INSERT INTO sale_details VALUES("SD0000000032","S0000003","PD0064","1","30");
INSERT INTO sale_details VALUES("SD0000000033","S0000003","PD0065","1","15");
INSERT INTO sale_details VALUES("SD0000000034","S0000003","PD0063","1","15");
INSERT INTO sale_details VALUES("SD0000000035","S0000003","PD0059","1","15");
INSERT INTO sale_details VALUES("SD0000000036","S0000003","PD0075","1","20");
INSERT INTO sale_details VALUES("SD0000000037","S0000003","PD0067","1","25");
INSERT INTO sale_details VALUES("SD0000000038","S0000003","PD0057","1","25");
INSERT INTO sale_details VALUES("SD0000000039","S0000004","PD0029","1","35");
INSERT INTO sale_details VALUES("SD0000000040","S0000004","PD0037","200","8000");
INSERT INTO sale_details VALUES("SD0000000041","S0000005","PD0037","1","40");
INSERT INTO sale_details VALUES("SD0000000042","S0000005","PD0067","1","25");
INSERT INTO sale_details VALUES("SD0000000043","S0000005","PD0064","1","30");
INSERT INTO sale_details VALUES("SD0000000044","S0000005","PD0066","1","30");
INSERT INTO sale_details VALUES("SD0000000045","S0000005","PD0065","1","15");
INSERT INTO sale_details VALUES("SD0000000046","S0000006","PD0078","3","45");
INSERT INTO sale_details VALUES("SD0000000047","S0000007","PD0078","1","15");
INSERT INTO sale_details VALUES("SD0000000048","S0000008","PD0063","1","15");
INSERT INTO sale_details VALUES("SD0000000049","S0000008","PD0055","1","25");
INSERT INTO sale_details VALUES("SD0000000050","S0000008","PD0069","1","15");
INSERT INTO sale_details VALUES("SD0000000051","S0000009","PD0022","1","240");
INSERT INTO sale_details VALUES("SD0000000052","S0000009","PD0020","1","150");
INSERT INTO sale_details VALUES("SD0000000053","S0000009","PD0024","1","220");



DROP TABLE IF EXISTS sale_promotion_details;

CREATE TABLE `sale_promotion_details` (
  `saleprmdtl_id` char(13) collate utf8_unicode_ci NOT NULL,
  `saledtl_id` char(12) collate utf8_unicode_ci NOT NULL,
  `prmprd_id` char(10) collate utf8_unicode_ci default NULL,
  `saleprmdtl_amount` int(11) NOT NULL,
  `saleprmdtl_discout` float NOT NULL,
  PRIMARY KEY  (`saleprmdtl_id`),
  KEY `saledtl_id` (`saledtl_id`),
  KEY `prmprd_id` (`prmprd_id`),
  CONSTRAINT `sale_promotion_details_ibfk_1` FOREIGN KEY (`saledtl_id`) REFERENCES `sale_details` (`saledtl_id`),
  CONSTRAINT `sale_promotion_details_ibfk_2` FOREIGN KEY (`prmprd_id`) REFERENCES `promotion_products` (`prmprd_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO sale_promotion_details VALUES("SP00000000001","SD0000000046","RP00000001","3","9");
INSERT INTO sale_promotion_details VALUES("SP00000000002","SD0000000047","RP00000001","1","3");



DROP TABLE IF EXISTS sale_promotion_sale_details;

CREATE TABLE `sale_promotion_sale_details` (
  `saleprmdsdtl_id` char(10) collate utf8_unicode_ci NOT NULL,
  `sale_id` char(8) collate utf8_unicode_ci NOT NULL,
  `prmds_id` char(6) collate utf8_unicode_ci default NULL,
  `saleprmdsdtl_discout` float NOT NULL,
  PRIMARY KEY  (`saleprmdsdtl_id`),
  KEY `sale_id` (`sale_id`),
  KEY `prmds_id` (`prmds_id`),
  CONSTRAINT `sale_promotion_sale_details_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`sale_id`),
  CONSTRAINT `sale_promotion_sale_details_ibfk_2` FOREIGN KEY (`prmds_id`) REFERENCES `promotion_discout_sales` (`prmds_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO sale_promotion_sale_details VALUES("SM00000001","S0000004","PC0001","803.5");



DROP TABLE IF EXISTS sales;

CREATE TABLE `sales` (
  `sale_id` char(8) collate utf8_unicode_ci NOT NULL,
  `emp_id` char(6) collate utf8_unicode_ci NOT NULL,
  `sale_date` date NOT NULL,
  `sale_time` time NOT NULL,
  `sale_discout` float NOT NULL,
  `sale_prm_discout` float NOT NULL,
  `sale_total_price` float NOT NULL,
  `sale_pay_price` float NOT NULL,
  PRIMARY KEY  (`sale_id`),
  KEY `emp_id` (`emp_id`),
  CONSTRAINT `sales_ibfk_1` FOREIGN KEY (`emp_id`) REFERENCES `employees` (`emp_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO sales VALUES("S0000001","EM0002","2015-03-24","22:46:48","0","0","255","255");
INSERT INTO sales VALUES("S0000002","EM0002","2015-03-30","14:09:36","0","0","3240","4000");
INSERT INTO sales VALUES("S0000003","EM0002","2015-03-30","14:11:14","0","0","465","500");
INSERT INTO sales VALUES("S0000004","EM0002","2015-03-30","14:14:54","803.5","0","7232","8000");
INSERT INTO sales VALUES("S0000005","EM0002","2015-04-08","23:46:44","0","0","140","140");
INSERT INTO sales VALUES("S0000006","EM0002","2015-04-08","23:54:48","0","9","36","36");
INSERT INTO sales VALUES("S0000007","EM0002","2015-04-09","00:00:41","0","3","12","12");
INSERT INTO sales VALUES("S0000008","EM0002","2015-04-09","00:02:10","0","0","55","55");
INSERT INTO sales VALUES("S0000009","EM0002","2015-04-09","00:02:27","0","0","610","610");



DROP TABLE IF EXISTS service_list_details;

CREATE TABLE `service_list_details` (
  `svldtl_id` char(15) collate utf8_unicode_ci NOT NULL,
  `svl_id` char(5) collate utf8_unicode_ci NOT NULL,
  `emp_id` char(6) collate utf8_unicode_ci NOT NULL,
  `sersvl_id` char(14) collate utf8_unicode_ci NOT NULL,
  `svldtl_com` float NOT NULL,
  PRIMARY KEY  (`svldtl_id`),
  KEY `svl_id` (`svl_id`),
  KEY `emp_id` (`emp_id`),
  KEY `sersvl_id` (`sersvl_id`),
  CONSTRAINT `service_list_details_ibfk_1` FOREIGN KEY (`svl_id`) REFERENCES `service_lists` (`svl_id`),
  CONSTRAINT `service_list_details_ibfk_2` FOREIGN KEY (`emp_id`) REFERENCES `employees` (`emp_id`),
  CONSTRAINT `service_list_details_ibfk_3` FOREIGN KEY (`sersvl_id`) REFERENCES `service_service_lists` (`sersvl_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO service_list_details VALUES("ST0000000000001","SL011","EM0011","SS000000000001","70");
INSERT INTO service_list_details VALUES("ST0000000000003","SL011","EM0005","SS000000000003","70");
INSERT INTO service_list_details VALUES("ST0000000000004","SL010","EM0003","SS000000000005","175");



DROP TABLE IF EXISTS service_list_promotion_details;

CREATE TABLE `service_list_promotion_details` (
  `svlprmdtl_id` char(10) collate utf8_unicode_ci NOT NULL,
  `svlprm_id` char(6) collate utf8_unicode_ci NOT NULL,
  `svl_id` char(5) collate utf8_unicode_ci NOT NULL,
  `svlprmdtl_startdate` date NOT NULL,
  `svlprmdtl_enddate` date default NULL,
  `svlprmdtl_discout_type` varchar(30) collate utf8_unicode_ci NOT NULL,
  `svlprmdtl_discout` float NOT NULL,
  PRIMARY KEY  (`svlprmdtl_id`),
  KEY `svlprm_id` (`svlprm_id`),
  KEY `svl_id` (`svl_id`),
  CONSTRAINT `service_list_promotion_details_ibfk_1` FOREIGN KEY (`svlprm_id`) REFERENCES `service_list_promotions` (`svlprm_id`),
  CONSTRAINT `service_list_promotion_details_ibfk_2` FOREIGN KEY (`svl_id`) REFERENCES `service_lists` (`svl_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO service_list_promotion_details VALUES("PE00000001","PG0002","SL004","2015-03-16","2015-04-23","%","10");
INSERT INTO service_list_promotion_details VALUES("PE00000002","PG0002","SL003","2015-03-19","2015-04-21","%","10");
INSERT INTO service_list_promotion_details VALUES("PE00000003","PG0002","SL009","2015-03-19","2015-03-31","%","15");
INSERT INTO service_list_promotion_details VALUES("PE00000004","PG0002","SL016","2015-03-19","2015-04-08","บาท","100");
INSERT INTO service_list_promotion_details VALUES("PE00000005","PG0002","SL019","2015-03-19","2015-03-31","บาท","200");
INSERT INTO service_list_promotion_details VALUES("PE00000006","PG0004","SL011","2015-05-03","","%","50");



DROP TABLE IF EXISTS service_list_promotions;

CREATE TABLE `service_list_promotions` (
  `svlprm_id` char(6) collate utf8_unicode_ci NOT NULL,
  `svlprm_name` varchar(150) collate utf8_unicode_ci NOT NULL,
  `svlprm_startdate` date NOT NULL,
  `svlprm_enddate` date default NULL,
  `svlprm_pic` varchar(150) collate utf8_unicode_ci default NULL,
  `svlprm_desc` varchar(255) collate utf8_unicode_ci default NULL,
  `custype_id` char(3) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`svlprm_id`),
  KEY `custype_id` (`custype_id`),
  CONSTRAINT `service_list_promotions_ibfk_1` FOREIGN KEY (`custype_id`) REFERENCES `customer_types` (`custype_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO service_list_promotions VALUES("PG0001","ลดราคาต้อนรับวันแห่งความรัก","2015-02-01","2015-02-21","PG0001.jpeg","","CT2");
INSERT INTO service_list_promotions VALUES("PG0002","ลดราคาต้อนรับเทศกาลสงกานต์","2015-03-16","2015-04-23","","","CT2");
INSERT INTO service_list_promotions VALUES("PG0003","sdfsdf","2015-04-10","2015-04-10","","","CT4");
INSERT INTO service_list_promotions VALUES("PG0004","หกดกหด","2015-05-03","","","","CT2");



DROP TABLE IF EXISTS service_list_types;

CREATE TABLE `service_list_types` (
  `svltyp_id` char(4) collate utf8_unicode_ci NOT NULL,
  `svltyp_name` varchar(50) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`svltyp_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO service_list_types VALUES("ST01","นวด");
INSERT INTO service_list_types VALUES("ST02","ขัดผิวกาย");
INSERT INTO service_list_types VALUES("ST03","พอกผิว");
INSERT INTO service_list_types VALUES("ST04","สปาหน้า");
INSERT INTO service_list_types VALUES("ST05","สปาเท้า");
INSERT INTO service_list_types VALUES("ST06","ประคบ");
INSERT INTO service_list_types VALUES("ST07","อบ");
INSERT INTO service_list_types VALUES("ST08","แช่");
INSERT INTO service_list_types VALUES("ST09","สปาอบ");
INSERT INTO service_list_types VALUES("ST10","สปาแช่");
INSERT INTO service_list_types VALUES("ST12","อาหารและเครื่องดื่ม");



DROP TABLE IF EXISTS service_lists;

CREATE TABLE `service_lists` (
  `svl_id` char(5) collate utf8_unicode_ci NOT NULL,
  `svltyp_id` char(4) collate utf8_unicode_ci NOT NULL,
  `svl_min` int(2) default NULL,
  `svl_hr` int(2) default NULL,
  `svl_name` varchar(50) collate utf8_unicode_ci NOT NULL,
  `svl_price` float NOT NULL,
  `svl_desc` varchar(200) collate utf8_unicode_ci default NULL,
  `svl_picture` varchar(100) collate utf8_unicode_ci default NULL,
  `svl_start` date NOT NULL,
  `svl_stop` date default NULL,
  `svl_type` int(1) NOT NULL default '1',
  PRIMARY KEY  (`svl_id`),
  KEY `svltyp_id` (`svltyp_id`),
  CONSTRAINT `service_lists_ibfk_1` FOREIGN KEY (`svltyp_id`) REFERENCES `service_list_types` (`svltyp_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO service_lists VALUES("SL001","ST01","30","1","นวดไทย","1500","นวดสบาย ผ่อนคลาย","SL001.jpeg","2015-04-01","","2");
INSERT INTO service_lists VALUES("SL002","ST01","30","1","นวดปรับสมดุลสตรี","1500","นวดสบาย ผ่อนคลาย","SL002.jpeg","0000-00-00","","1");
INSERT INTO service_lists VALUES("SL003","ST01","30","1","นวดปรับสมดุลลำไส้","1500","เป็นการนวดเพื่อให้เลือดลมไหลเวียนดี","SL003.jpeg","0000-00-00","","1");
INSERT INTO service_lists VALUES("SL004","ST01","30","1","นวดน้ำมันอโรม่าตามเจ้าเรือน","1400","เป็นการนวดน้ำมันอโรม่าให้ความหอม ผ่อนครายความตึงเครียด สบายตัว","SL004.jpeg","0000-00-00","","1");
INSERT INTO service_lists VALUES("SL005","ST02","","1","ขัดผิวบัว ไผ่ ข้าว","200","-","SL005.jpeg","0000-00-00","","1");
INSERT INTO service_lists VALUES("SL006","ST02","","1","Body Scrub Gel Rose","800","เป็นการขัดผิวโดยใช้สครับเจลที่นุ่มละมุน หอมกลิ่นกุหลาบผรั่งเศส","SL006.jpeg","0000-00-00","","1");
INSERT INTO service_lists VALUES("SL007","ST02","","1","ขัดผิวสมุนไพรตามเจ้าเรือน","650","เป็นการชัดผิวด้วยสมุนไร สูตรลับเฉพาะอภัยภูเบศร ให้ผิวแลดูสุขภาพดี ","SL007.jpeg","0000-00-00","","1");
INSERT INTO service_lists VALUES("SL008","ST02","","1","ขัดผิวเกลือสปาตามเจ้าเรือน","650","เป็นการขัดผิวโดยใช้เม็ดเหลือสปา เนื้อละเอียด ให้ผิวเนียนนุ่ม น่าสัมผัส","SL008.jpeg","0000-00-00","","1");
INSERT INTO service_lists VALUES("SL009","ST03","","1","พอกผิวสมุนไพรตามเจ้าเรือน","650","เป็นการพอกผิวโดยใช้สูตรเฉพาะของอภัยภูเบศร ให้ผิวแลดูสุขภาพดี","SL009.jpeg","0000-00-00","","1");
INSERT INTO service_lists VALUES("SL010","ST03","","1","Whitening Body Mask Cream","700","เป็นการพอกผิวด้วยครีมผิวขาวที่ช่วยให้ผิวแลดูขาว สว่าง กระจ่างใสขึ้น ","SL010.jpeg","0000-00-00","","1");
INSERT INTO service_lists VALUES("SL011","ST03","","1","Bamboo Mask Power","700","เป็นการพอกผิวดูดสารพิษ ให้ผิวสุขภาพดี ล้างคราบไหล ให้ผิว กระจ่างใส สุขภาพดี","SL011.jpeg","0000-00-00","","1");
INSERT INTO service_lists VALUES("SL012","ST04","","1","สปาหน้าบัว ไผ่ ข้าว","1000","เป็นนวดหน้า โดยใช้ผลิตภัณฑ์ที่สกัดเข้มข้นจาก บัว ไผ่ และข้าว รวมกันให้ผิวหน้า กระชับ เต่งตึง ดูอ่อนเยาว์","SL012.jpeg","0000-00-00","","1");
INSERT INTO service_lists VALUES("SL013","ST04","","1","สปาหน้าสมุนไพรตามเจ้าเรือน","900","เป็นการนวดหระชับผิวหน้า ให้ความรู้สึกเนียนนุ่ม น่าสัมผัส พร้อมกินหอมสมุนไพรแบบไทยๆ","SL013.jpeg","0000-00-00","","1");
INSERT INTO service_lists VALUES("SL014","ST04","","1","มาร์คสิว","900","เป็นการพอกหน้าที่มีคุณสมบัติยับยั้งแบคทีเรียที่ก่อให้เกิดสิวเสี้ยน สิวหัวแดง ผดผื่นบนใบหน้า ช่วยให้รูขุมขนกระชับ ผิวหน้ากระจ่างใส","SL014.jpeg","0000-00-00","","1");
INSERT INTO service_lists VALUES("SL015","ST04","","1","มาร์คหน้าขาว","1000","เป็นการพอกผิวหน้าที่ช่วยให้ผิวขาวกระจ่างใสอย่างเป็นธรรมชาติ ลดริ้วรอย จุดด่างดำ","SL015.jpeg","0000-00-00","","1");
INSERT INTO service_lists VALUES("SL016","ST05","30","1","สปาเท้า","1200","เป็นการนวดสปาเท้าให้ความรู้สึกสบาย ผ่อนคลายจากความตึงเครียด เท้าเนียนนุ่ม","SL016.jpeg","0000-00-00","","1");
INSERT INTO service_lists VALUES("SL017","ST06","30","","ประคบสมุนไพรผ้าคราม","600","เป็นการประคบด้วยลูกประคบสมุนไพร คลายความเมื่อยล้า ","SL017.jpeg","0000-00-00","","1");
INSERT INTO service_lists VALUES("SL018","ST07","30","","อบสมุนไพรตามธาตุเจ้าเรือน","800","เป็นการอบผิวด้วยสมุนไพรสูตรเฉพาะอภัยภูเบศร หอมผ่อนคลายผิว ล้างสารพิษ ให้ผิวเนียนนุ่ม ดูอิ่มน้ำ สุขภาพดี สว่างกระจ่างใสขึ้น","SL018.jpeg","0000-00-00","","1");
INSERT INTO service_lists VALUES("SL019","ST08","30","","แช่ยาสมุนไรตามเจ้าเรือน","800","เป็นการแช่ผิวในน้ำยาสมุนไร ที่มีคุณสมบัตรตรงเข้าบำรุงผิวทันที ช่วยให้ผิวเนียนนุ่ม หอมสมุนไพรไทย","SL019.jpeg","0000-00-00","","1");
INSERT INTO service_lists VALUES("SL020","ST12","","","อาหารว่างและชาสมุนไพร","0","","SL020.jpeg","2015-04-01","","2");
INSERT INTO service_lists VALUES("SL021","ST06","","","ประคบเต้านม","0","","SL021.jpeg","0000-00-00","","1");
INSERT INTO service_lists VALUES("SL022","ST01","","","เข้าตะเกียบ","0","","SL022.jpeg","0000-00-00","","1");
INSERT INTO service_lists VALUES("SL023","ST06","","","ทับหม้อเกลือ","0","","SL023.jpeg","0000-00-00","","1");
INSERT INTO service_lists VALUES("SL024","ST01","","","นวดกระตุ้นน้ำนม","0","","SL024.jpeg","0000-00-00","","1");
INSERT INTO service_lists VALUES("SL025","ST07","","","เผายา","0","นวดหน้าเพื่อความกระชับ","SL025.jpeg","0000-00-00","","1");
INSERT INTO service_lists VALUES("SL026","ST07","30","","อบสมุนไพรหญ้ารีแพร์","800","","SL026.jpeg","0000-00-00","","1");
INSERT INTO service_lists VALUES("SL027","ST03","","","พอกผิวดูดสารพิษ","0","","","0000-00-00","","1");



DROP TABLE IF EXISTS service_package_promotions;

CREATE TABLE `service_package_promotions` (
  `serpkgprm_id` char(14) collate utf8_unicode_ci NOT NULL,
  `ser_id` char(15) collate utf8_unicode_ci NOT NULL,
  `pkgprmdtl_id` char(10) collate utf8_unicode_ci NOT NULL,
  `serpkgprm_amount` int(11) NOT NULL,
  `serpkgprm_discout_total` float NOT NULL,
  PRIMARY KEY  (`serpkgprm_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO service_package_promotions VALUES("SP000000000001","SE0000000001/58","PE00000010","1","1200");
INSERT INTO service_package_promotions VALUES("SP000000000003","SE0000000007/58","PE00000010","1","1200");
INSERT INTO service_package_promotions VALUES("SP000000000004","SE0000000008/58","PE00000010","1","1200");
INSERT INTO service_package_promotions VALUES("SP000000000005","SE0000000010/58","PE00000010","1","1200");



DROP TABLE IF EXISTS service_packages;

CREATE TABLE `service_packages` (
  `serpkg_id` char(14) collate utf8_unicode_ci NOT NULL,
  `ser_id` char(15) collate utf8_unicode_ci NOT NULL,
  `pkg_id` char(5) collate utf8_unicode_ci NOT NULL,
  `serpkg_amount` int(11) NOT NULL,
  `serpkg_total_price` float NOT NULL,
  PRIMARY KEY  (`serpkg_id`),
  KEY `ser_id` (`ser_id`),
  KEY `pkg_id` (`pkg_id`),
  CONSTRAINT `service_packages_ibfk_1` FOREIGN KEY (`ser_id`) REFERENCES `services` (`ser_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO service_packages VALUES("SK000000000001","SE0000000001/58","PK003","1","2400");
INSERT INTO service_packages VALUES("SK000000000002","SE0000000003/58","PK006","1","2000");
INSERT INTO service_packages VALUES("SK000000000005","SE0000000007/58","PK003","1","2400");
INSERT INTO service_packages VALUES("SK000000000006","SE0000000008/58","PK003","1","2400");
INSERT INTO service_packages VALUES("SK000000000007","SE0000000008/58","PK007","1","2000");
INSERT INTO service_packages VALUES("SK000000000008","SE0000000008/58","PK005","1","2600");
INSERT INTO service_packages VALUES("SK000000000009","SE0000000010/58","PK003","1","2400");



DROP TABLE IF EXISTS service_service_list_promotions;

CREATE TABLE `service_service_list_promotions` (
  `sersvlprm_id` char(14) collate utf8_unicode_ci NOT NULL,
  `ser_id` char(15) collate utf8_unicode_ci NOT NULL,
  `svlprmdtl_id` varchar(10) collate utf8_unicode_ci NOT NULL,
  `sersvlprm_amount` int(11) NOT NULL,
  `sersvlprm_discout_total` float NOT NULL,
  PRIMARY KEY  (`sersvlprm_id`),
  KEY `ser_id` (`ser_id`),
  KEY `ser_id_2` (`svlprmdtl_id`),
  CONSTRAINT `service_service_list_promotions_ibfk_1` FOREIGN KEY (`ser_id`) REFERENCES `services` (`ser_id`),
  CONSTRAINT `service_service_list_promotions_ibfk_2` FOREIGN KEY (`svlprmdtl_id`) REFERENCES `service_list_promotion_details` (`svlprmdtl_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO service_service_list_promotions VALUES("SO000000000001","SE0000000002/58","PE00000006","1","350");
INSERT INTO service_service_list_promotions VALUES("SO000000000002","SE0000000006/58","PE00000006","1","350");



DROP TABLE IF EXISTS service_service_list_times;

CREATE TABLE `service_service_list_times` (
  `sersvt_id` char(15) collate utf8_unicode_ci NOT NULL,
  `serpkg_id` char(14) collate utf8_unicode_ci NOT NULL,
  `pkgsvl_id` char(8) collate utf8_unicode_ci NOT NULL,
  `sersvt_time` time NOT NULL,
  PRIMARY KEY  (`sersvt_id`),
  KEY `pkgsvl_id` (`pkgsvl_id`),
  KEY `serpkg_id` (`serpkg_id`),
  CONSTRAINT `service_service_list_times_ibfk_2` FOREIGN KEY (`serpkg_id`) REFERENCES `service_packages` (`serpkg_id`),
  CONSTRAINT `service_service_list_times_ibfk_3` FOREIGN KEY (`pkgsvl_id`) REFERENCES `package_service_lists` (`pkgsvl_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO service_service_list_times VALUES("ST0000000000001","SK000000000001","PS000010","08:30:00");
INSERT INTO service_service_list_times VALUES("ST0000000000002","SK000000000001","PS000013","09:01:00");
INSERT INTO service_service_list_times VALUES("ST0000000000003","SK000000000001","PS000026","10:02:00");
INSERT INTO service_service_list_times VALUES("ST0000000000004","SK000000000002","PS000033","08:00:00");
INSERT INTO service_service_list_times VALUES("ST0000000000005","SK000000000002","PS000034","09:31:00");
INSERT INTO service_service_list_times VALUES("ST0000000000014","SK000000000005","PS000010","09:40:00");
INSERT INTO service_service_list_times VALUES("ST0000000000015","SK000000000005","PS000013","10:50:00");
INSERT INTO service_service_list_times VALUES("ST0000000000016","SK000000000005","PS000026","12:00:00");
INSERT INTO service_service_list_times VALUES("ST0000000000017","SK000000000006","PS000010","08:00:00");
INSERT INTO service_service_list_times VALUES("ST0000000000018","SK000000000006","PS000013","09:01:00");
INSERT INTO service_service_list_times VALUES("ST0000000000019","SK000000000006","PS000026","10:00:00");
INSERT INTO service_service_list_times VALUES("ST0000000000020","SK000000000007","PS000036","11:40:00");
INSERT INTO service_service_list_times VALUES("ST0000000000021","SK000000000007","PS000037","12:00:00");
INSERT INTO service_service_list_times VALUES("ST0000000000022","SK000000000008","PS000019","13:00:00");
INSERT INTO service_service_list_times VALUES("ST0000000000023","SK000000000008","PS000029","14:05:00");
INSERT INTO service_service_list_times VALUES("ST0000000000024","SK000000000008","PS000042","14:50:00");
INSERT INTO service_service_list_times VALUES("ST0000000000025","SK000000000008","PS000044","15:30:00");
INSERT INTO service_service_list_times VALUES("ST0000000000026","SK000000000008","PS000045","16:05:00");
INSERT INTO service_service_list_times VALUES("ST0000000000027","SK000000000009","PS000010","08:00:00");
INSERT INTO service_service_list_times VALUES("ST0000000000028","SK000000000009","PS000013","09:02:00");
INSERT INTO service_service_list_times VALUES("ST0000000000029","SK000000000009","PS000026","10:03:00");



DROP TABLE IF EXISTS service_service_lists;

CREATE TABLE `service_service_lists` (
  `sersvl_id` char(14) collate utf8_unicode_ci NOT NULL,
  `ser_id` char(15) collate utf8_unicode_ci NOT NULL,
  `svl_id` char(5) collate utf8_unicode_ci NOT NULL,
  `sersvl_amount` int(11) NOT NULL,
  `sersvl_total_price` float NOT NULL,
  `sersvl_time` time NOT NULL,
  PRIMARY KEY  (`sersvl_id`),
  KEY `ser_id` (`ser_id`),
  KEY `svl_id` (`svl_id`),
  CONSTRAINT `service_service_lists_ibfk_1` FOREIGN KEY (`ser_id`) REFERENCES `services` (`ser_id`),
  CONSTRAINT `service_service_lists_ibfk_2` FOREIGN KEY (`svl_id`) REFERENCES `service_lists` (`svl_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO service_service_lists VALUES("SS000000000001","SE0000000002/58","SL011","1","700","08:00:00");
INSERT INTO service_service_lists VALUES("SS000000000003","SE0000000006/58","SL011","1","700","10:00:00");
INSERT INTO service_service_lists VALUES("SS000000000004","SE0000000009/58","SL005","1","200","06:40:00");
INSERT INTO service_service_lists VALUES("SS000000000005","SE0000000011/58","SL010","1","700","16:20:00");



DROP TABLE IF EXISTS services;

CREATE TABLE `services` (
  `ser_id` char(15) collate utf8_unicode_ci NOT NULL,
  `cus_id` char(8) collate utf8_unicode_ci NOT NULL,
  `emp_id` char(6) collate utf8_unicode_ci NOT NULL,
  `paytyp_id` char(4) collate utf8_unicode_ci NOT NULL,
  `bed_id` char(4) collate utf8_unicode_ci NOT NULL,
  `bkg_id` char(15) collate utf8_unicode_ci default NULL,
  `ser_date` date NOT NULL,
  `ser_time` time NOT NULL,
  `ser_prm_discout` float NOT NULL,
  `ser_total_price` float NOT NULL,
  `ser_pay_price` float NOT NULL,
  PRIMARY KEY  (`ser_id`),
  KEY `cus_id` (`cus_id`),
  KEY `emp_id` (`emp_id`),
  KEY `paytyp_id` (`paytyp_id`),
  KEY `bed_id` (`bed_id`),
  KEY `bkg_id` (`bkg_id`),
  CONSTRAINT `services_ibfk_10` FOREIGN KEY (`bed_id`) REFERENCES `beds` (`bed_id`),
  CONSTRAINT `services_ibfk_11` FOREIGN KEY (`bkg_id`) REFERENCES `booking` (`bkg_id`),
  CONSTRAINT `services_ibfk_7` FOREIGN KEY (`cus_id`) REFERENCES `customers` (`cus_id`),
  CONSTRAINT `services_ibfk_8` FOREIGN KEY (`emp_id`) REFERENCES `employees` (`emp_id`),
  CONSTRAINT `services_ibfk_9` FOREIGN KEY (`paytyp_id`) REFERENCES `pay_types` (`paytyp_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO services VALUES("SE0000000001/58","CM000012","EM0002","PA01","BE05","","2015-05-02","22:14:00","1200","1200","1200");
INSERT INTO services VALUES("SE0000000002/58","CM000004","EM0002","PA01","BE04","","2015-05-03","12:56:00","350","350","350");
INSERT INTO services VALUES("SE0000000003/58","CM000001","EM0002","PA01","BE06","","2015-05-03","13:26:00","0","2000","2000");
INSERT INTO services VALUES("SE0000000006/58","CM000012","EM0002","PA01","BE05","BK0000000008/58","2015-05-06","23:56:00","350","350","350");
INSERT INTO services VALUES("SE0000000007/58","CM000004","EM0002","PA01","BE03","BK0000000009/58","2015-05-06","23:56:00","1200","1200","1200");
INSERT INTO services VALUES("SE0000000008/58","CM000001","EM0002","PA01","BE03","BK0000000010/58","2015-05-07","00:33:00","1200","5800","5800");
INSERT INTO services VALUES("SE0000000009/58","CM000010","EM0002","PA01","BE05","BK0000000007/58","2015-05-05","00:41:00","0","200","200");
INSERT INTO services VALUES("SE0000000010/58","CM000001","EM0002","PA01","BE04","","2015-05-07","00:47:00","1200","1200","1200");
INSERT INTO services VALUES("SE0000000011/58","CM000004","EM0002","PA01","BE03","","2015-05-07","00:53:00","0","700","700");



DROP TABLE IF EXISTS sex;

CREATE TABLE `sex` (
  `sex_id` char(2) character set utf8 collate utf8_unicode_ci NOT NULL,
  `sex_name` varchar(20) character set utf8 collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`sex_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci;

INSERT INTO sex VALUES("X1","ชาย");
INSERT INTO sex VALUES("X2","หญิง");



DROP TABLE IF EXISTS shop_display_products;

CREATE TABLE `shop_display_products` (
  `shpdpprd_id` char(8) collate utf8_unicode_ci NOT NULL,
  `shop_id` char(4) collate utf8_unicode_ci NOT NULL,
  `prd_id` char(6) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`shpdpprd_id`),
  KEY `shop_id` (`shop_id`),
  KEY `prd_id` (`prd_id`),
  CONSTRAINT `shop_display_products_ibfk_1` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`shop_id`),
  CONSTRAINT `shop_display_products_ibfk_2` FOREIGN KEY (`prd_id`) REFERENCES `products` (`prd_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO shop_display_products VALUES("DP000008","SH03","PD0026");
INSERT INTO shop_display_products VALUES("DP000009","SH03","PD0025");
INSERT INTO shop_display_products VALUES("DP000010","SH03","PD0024");
INSERT INTO shop_display_products VALUES("DP000016","SH03","PD0022");
INSERT INTO shop_display_products VALUES("DP000021","SH03","PD0023");
INSERT INTO shop_display_products VALUES("DP000022","SH03","PD0021");
INSERT INTO shop_display_products VALUES("DP000031","SH01","PD0041");
INSERT INTO shop_display_products VALUES("DP000034","SH03","PD0017");
INSERT INTO shop_display_products VALUES("DP000035","SH03","PD0016");
INSERT INTO shop_display_products VALUES("DP000036","SH03","PD0015");
INSERT INTO shop_display_products VALUES("DP000037","SH03","PD0020");
INSERT INTO shop_display_products VALUES("DP000038","SH03","PD0019");
INSERT INTO shop_display_products VALUES("DP000039","SH03","PD0018");
INSERT INTO shop_display_products VALUES("DP000040","SH03","PD0014");
INSERT INTO shop_display_products VALUES("DP000041","SH03","PD0013");
INSERT INTO shop_display_products VALUES("DP000042","SH01","PD0042");
INSERT INTO shop_display_products VALUES("DP000043","SH01","PD0029");
INSERT INTO shop_display_products VALUES("DP000044","SH01","PD0030");
INSERT INTO shop_display_products VALUES("DP000045","SH01","PD0039");
INSERT INTO shop_display_products VALUES("DP000046","SH01","PD0040");
INSERT INTO shop_display_products VALUES("DP000047","SH01","PD0027");
INSERT INTO shop_display_products VALUES("DP000048","SH01","PD0028");
INSERT INTO shop_display_products VALUES("DP000049","SH01","PD0045");
INSERT INTO shop_display_products VALUES("DP000050","SH01","PD0046");
INSERT INTO shop_display_products VALUES("DP000051","SH01","PD0033");
INSERT INTO shop_display_products VALUES("DP000052","SH01","PD0034");
INSERT INTO shop_display_products VALUES("DP000053","SH01","PD0043");
INSERT INTO shop_display_products VALUES("DP000054","SH01","PD0044");
INSERT INTO shop_display_products VALUES("DP000055","SH01","PD0031");
INSERT INTO shop_display_products VALUES("DP000056","SH01","PD0032");
INSERT INTO shop_display_products VALUES("DP000057","SH01","PD0049");
INSERT INTO shop_display_products VALUES("DP000058","SH01","PD0050");
INSERT INTO shop_display_products VALUES("DP000059","SH01","PD0037");
INSERT INTO shop_display_products VALUES("DP000060","SH01","PD0038");
INSERT INTO shop_display_products VALUES("DP000061","SH01","PD0047");
INSERT INTO shop_display_products VALUES("DP000062","SH01","PD0048");
INSERT INTO shop_display_products VALUES("DP000063","SH01","PD0035");
INSERT INTO shop_display_products VALUES("DP000064","SH01","PD0036");
INSERT INTO shop_display_products VALUES("DP000065","SH01","PD0051");
INSERT INTO shop_display_products VALUES("DP000066","SH01","PD0052");
INSERT INTO shop_display_products VALUES("DP000067","SH01","PD0056");
INSERT INTO shop_display_products VALUES("DP000068","SH01","PD0055");
INSERT INTO shop_display_products VALUES("DP000069","SH01","PD0066");
INSERT INTO shop_display_products VALUES("DP000070","SH01","PD0065");
INSERT INTO shop_display_products VALUES("DP000071","SH01","PD0058");
INSERT INTO shop_display_products VALUES("DP000072","SH01","PD0057");
INSERT INTO shop_display_products VALUES("DP000073","SH01","PD0068");
INSERT INTO shop_display_products VALUES("DP000074","SH01","PD0067");
INSERT INTO shop_display_products VALUES("DP000075","SH01","PD0070");
INSERT INTO shop_display_products VALUES("DP000076","SH01","PD0069");
INSERT INTO shop_display_products VALUES("DP000077","SH01","PD0064");
INSERT INTO shop_display_products VALUES("DP000078","SH01","PD0063");
INSERT INTO shop_display_products VALUES("DP000079","SH01","PD0060");
INSERT INTO shop_display_products VALUES("DP000080","SH01","PD0059");
INSERT INTO shop_display_products VALUES("DP000081","SH01","PD0076");
INSERT INTO shop_display_products VALUES("DP000082","SH01","PD0075");
INSERT INTO shop_display_products VALUES("DP000083","SH01","PD0062");
INSERT INTO shop_display_products VALUES("DP000084","SH01","PD0061");
INSERT INTO shop_display_products VALUES("DP000085","SH01","PD0054");
INSERT INTO shop_display_products VALUES("DP000086","SH01","PD0053");
INSERT INTO shop_display_products VALUES("DP000087","SH01","PD0074");
INSERT INTO shop_display_products VALUES("DP000088","SH01","PD0073");
INSERT INTO shop_display_products VALUES("DP000089","SH01","PD0072");
INSERT INTO shop_display_products VALUES("DP000090","SH01","PD0071");
INSERT INTO shop_display_products VALUES("DP000091","SH01","PD0077");
INSERT INTO shop_display_products VALUES("DP000092","SH01","PD0078");
INSERT INTO shop_display_products VALUES("DP000093","SH01","PD0080");
INSERT INTO shop_display_products VALUES("SP000004","SH02","PD0023");



DROP TABLE IF EXISTS shops;

CREATE TABLE `shops` (
  `shop_id` char(4) collate utf8_unicode_ci NOT NULL,
  `shop_name` varchar(100) collate utf8_unicode_ci NOT NULL,
  `shop_picture` varchar(150) collate utf8_unicode_ci default NULL,
  `shop_desc` varchar(150) collate utf8_unicode_ci default NULL,
  PRIMARY KEY  (`shop_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO shops VALUES("SH01","Tea & Coffee","SH01.png","ขายเครื่องดื่ม และของว่างเพื่อสุขภาพ");
INSERT INTO shops VALUES("SH02","Cusine","SH02.png","ขายอาหาร");
INSERT INTO shops VALUES("SH03","Product of Spa","SH03.png","");



DROP TABLE IF EXISTS spa;

CREATE TABLE `spa` (
  `spa_id` char(4) collate utf8_unicode_ci NOT NULL,
  `spa_name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `spa_addr` varchar(255) collate utf8_unicode_ci NOT NULL,
  `spa_tel` varchar(20) collate utf8_unicode_ci NOT NULL,
  `spa_fax` varchar(15) collate utf8_unicode_ci default NULL,
  `spa_email` varchar(255) collate utf8_unicode_ci default NULL,
  `spa_logo` varchar(150) collate utf8_unicode_ci default NULL,
  PRIMARY KEY  (`spa_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO spa VALUES("SA01","สปา โรงพยาบาลเจ้าพระยาอภัยภูเบศร","เลขที่ 32/7 หมู่ 12 ถนนปราจีนอนุสรณ์\nตำบลท่างาม อำเภอเมืองปราจีนบุรี จังหวัดปราจีนบุรี 25000","0875582269","0375586213","abhaiherb@gmail.com","SA01.jpeg");



DROP TABLE IF EXISTS time_attendances;

CREATE TABLE `time_attendances` (
  `timeatt_id` char(8) collate utf8_unicode_ci NOT NULL,
  `emp_id` char(6) collate utf8_unicode_ci NOT NULL,
  `dateatt_in` date NOT NULL,
  `timeatt_in` time NOT NULL,
  `dateatt_out` date default NULL,
  `timeatt_out` time default NULL,
  PRIMARY KEY  (`timeatt_id`),
  KEY `emp_id` (`emp_id`),
  CONSTRAINT `time_attendances_ibfk_1` FOREIGN KEY (`emp_id`) REFERENCES `employees` (`emp_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO time_attendances VALUES("W0000001","EM0001","2013-08-01","00:00:00","2013-08-01","00:20:14");
INSERT INTO time_attendances VALUES("W0000002","EM0002","2013-08-02","00:00:00","2013-08-02","00:20:14");



DROP TABLE IF EXISTS titles;

CREATE TABLE `titles` (
  `title_id` char(3) collate utf8_unicode_ci NOT NULL,
  `title_name` varchar(100) collate utf8_unicode_ci NOT NULL,
  `sex_id` char(2) collate utf8_unicode_ci default NULL,
  PRIMARY KEY  (`title_id`),
  UNIQUE KEY `title_name` (`title_name`),
  KEY `sex_id` (`sex_id`),
  CONSTRAINT `titles_ibfk_1` FOREIGN KEY (`sex_id`) REFERENCES `sex` (`sex_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO titles VALUES("T01","นาย","X1");
INSERT INTO titles VALUES("T02","นางสาว","X2");
INSERT INTO titles VALUES("T03","นาง","X2");
INSERT INTO titles VALUES("T04","ว่าที่ร้อยตรี","");
INSERT INTO titles VALUES("T05","ร้อยโท","");
INSERT INTO titles VALUES("T07","พันโท","");
INSERT INTO titles VALUES("T08","ร้อยตรี","");
INSERT INTO titles VALUES("T09","ว่าที่ร้อยโท","");
INSERT INTO titles VALUES("T10","นายแพทย์","X1");
INSERT INTO titles VALUES("T11","แพทย์หญิง","X2");
INSERT INTO titles VALUES("T12","หม่อมหลวง","");



DROP TABLE IF EXISTS units;

CREATE TABLE `units` (
  `unit_id` char(3) collate utf8_unicode_ci NOT NULL,
  `unit_name` varchar(30) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`unit_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO units VALUES("U01","โหล");
INSERT INTO units VALUES("U02","ลิตร");
INSERT INTO units VALUES("U03","ขวด");
INSERT INTO units VALUES("U04","กล่อง");
INSERT INTO units VALUES("U05","กระปุก");
INSERT INTO units VALUES("U06","หลอด");
INSERT INTO units VALUES("U07","แก้ว");
INSERT INTO units VALUES("U08","ถ้วย");
INSERT INTO units VALUES("U09","กา");
INSERT INTO units VALUES("U10","แผ่น");
INSERT INTO units VALUES("U11","จาน");



DROP TABLE IF EXISTS withdraw_details;

CREATE TABLE `withdraw_details` (
  `wdwdtl_id` char(10) collate utf8_unicode_ci NOT NULL,
  `wdw_id` char(8) collate utf8_unicode_ci NOT NULL,
  `prd_id` char(6) collate utf8_unicode_ci NOT NULL,
  `wdwdtl_amount` int(11) NOT NULL,
  PRIMARY KEY  (`wdwdtl_id`),
  KEY `wdw_id` (`wdw_id`),
  KEY `prd_id` (`prd_id`),
  CONSTRAINT `withdraw_details_ibfk_1` FOREIGN KEY (`wdw_id`) REFERENCES `withdraws` (`wdw_id`),
  CONSTRAINT `withdraw_details_ibfk_2` FOREIGN KEY (`prd_id`) REFERENCES `products` (`prd_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO withdraw_details VALUES("WD00000001","W0000001","PD0041","10");
INSERT INTO withdraw_details VALUES("WD00000002","W0000001","PD0030","10");
INSERT INTO withdraw_details VALUES("WD00000003","W0000002","PD0043","10");
INSERT INTO withdraw_details VALUES("WD00000004","W0000003","PD0002","5");
INSERT INTO withdraw_details VALUES("WD00000005","W0000003","PD0001","3");
INSERT INTO withdraw_details VALUES("WD00000006","W0000003","PD0031","10");



DROP TABLE IF EXISTS withdraw_types;

CREATE TABLE `withdraw_types` (
  `wdwtyp_id` char(3) collate utf8_unicode_ci NOT NULL,
  `wdwtyp_name` varchar(50) collate utf8_unicode_ci NOT NULL,
  `wdwtyp_increase_shelf_amount` tinyint(1) NOT NULL,
  PRIMARY KEY  (`wdwtyp_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO withdraw_types VALUES("WT1","เบิกเพื่อการให้บริการ","0");
INSERT INTO withdraw_types VALUES("WT2","เบิกเพื่อขาย","1");
INSERT INTO withdraw_types VALUES("WT3","เบิกเพื่อฝากขาย","1");
INSERT INTO withdraw_types VALUES("WT4","เบิกเพื่อใช้ตกแต่งสปา","0");
INSERT INTO withdraw_types VALUES("WT5","เบิกเพื่อใช้ทดลอง","0");



DROP TABLE IF EXISTS withdraws;

CREATE TABLE `withdraws` (
  `wdw_id` char(8) collate utf8_unicode_ci NOT NULL,
  `emp_id` char(6) collate utf8_unicode_ci NOT NULL,
  `emp_give_id` char(6) collate utf8_unicode_ci NOT NULL,
  `ser_id` char(15) collate utf8_unicode_ci default NULL,
  `wdw_date` date NOT NULL,
  `wdwtyp_id` char(3) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`wdw_id`),
  KEY `emp_id` (`emp_id`),
  KEY `emp_give_id` (`emp_give_id`),
  KEY `ser_id` (`ser_id`),
  KEY `wdwtyp_id` (`wdwtyp_id`),
  CONSTRAINT `withdraws_ibfk_19` FOREIGN KEY (`emp_id`) REFERENCES `employees` (`emp_id`),
  CONSTRAINT `withdraws_ibfk_20` FOREIGN KEY (`emp_give_id`) REFERENCES `employees` (`emp_id`),
  CONSTRAINT `withdraws_ibfk_21` FOREIGN KEY (`ser_id`) REFERENCES `services` (`ser_id`),
  CONSTRAINT `withdraws_ibfk_22` FOREIGN KEY (`wdwtyp_id`) REFERENCES `withdraw_types` (`wdwtyp_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO withdraws VALUES("W0000001","EM0011","EM0002","","2015-04-02","WT1");
INSERT INTO withdraws VALUES("W0000002","EM0003","EM0002","","2015-04-02","WT2");
INSERT INTO withdraws VALUES("W0000003","EM0002","EM0002","","2015-04-02","WT4");





SET FOREIGN_KEY_CHECKS = 1;
COMMIT;
SET AUTOCOMMIT = 1;