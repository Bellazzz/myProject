<?php
/*
 * Process Zone
 */
session_start();
include('../config/config.php');
include('../common/common_header.php');

// Pre Valiables
$tableName	= '';
if(hasValue($_REQUEST['tableName'])) {
	$tableName = $_REQUEST['tableName'];
}
$tableInfo	= getTableInfo($tableName);
$sortCol	= $tableInfo['keyFieldName'];
$sortBy		= 'asc';
$sortBySpecial = 'desc';
$filter 		= '';
$filterRetroact = '';
$filterExpired 	= '';
$where			= '';
$whereAllRecord = '';
$like			= '';
$order			= '';
$limit 			= '';
$page 			= 1;
$recordDisplay 	= 20;
$retroactDate 	= '';
$filterExpiredCond = '';
$fieldStartDate = '';
$fieldEndDate = '';

if(hasValue($_REQUEST['sortBy'])) {
	$sortBy	= $_REQUEST['sortBy'];
	$sortBySpecial = $_REQUEST['sortBy'];
}
if(hasValue($_REQUEST['sortCol'])) {
	$sortCol = $_REQUEST['sortCol'];
}
if(hasValue($_REQUEST['filter'])) {
	$filter = $_REQUEST['filter'];
}
if(hasValue($_REQUEST['filterRetroact'])) {
	$filterRetroact = $_REQUEST['filterRetroact'];
}
if(hasValue($_REQUEST['filterExpired'])) {
	$filterExpired = $_REQUEST['filterExpired'];
	if($tableName == 'packages') {
		$fieldStartDate = 'pkg_start';
		$fieldEndDate = 'pkg_stop';
	} else if($tableName == 'service_lists') {
		$fieldStartDate = 'svl_start';
		$fieldEndDate = 'svl_stop';
	} else if($tableName == 'service_list_promotions') {
		$fieldStartDate = 'p.svlprm_startdate';
		$fieldEndDate = 'p.svlprm_enddate';
	} else if($tableName == 'service_list_promotion_details') {
		$fieldStartDate = 'd.svlprmdtl_startdate';
		$fieldEndDate = 'd.svlprmdtl_enddate';
	} else if($tableName == 'package_promotions') {
		$fieldStartDate = 'p.pkgprm_startdate';
		$fieldEndDate = 'p.pkgprm_enddate';
	} else if($tableName == 'package_promotion_details') {
		$fieldStartDate = 'd.pkgprmdtl_startdate';
		$fieldEndDate = 'd.pkgprmdtl_enddate';
	} else if($tableName == 'product_promotions') {
		$fieldStartDate = 'p.prdprm_startdate';
		$fieldEndDate = 'p.prdprm_enddate';
	} else if($tableName == 'promotion_products') {
		$fieldStartDate = 'prmprd.prmprd_startdate';
		$fieldEndDate = 'prmprd.prmprd_enddate';
	} else if($tableName == 'promotion_discout_sales') {
		$fieldStartDate = 'p.prmds_startdate';
		$fieldEndDate = 'p.prmds_enddate';
	}
}
if(hasValue($_REQUEST['page'])) {
	$page = (Int)$_REQUEST['page'];
}
if(hasValue($_REQUEST['recordDisplay'])) {
	$recordDisplay = (Int)$_REQUEST['recordDisplay'];
}
if($filterRetroact == '1') {
	$retroactDate 	= date('Y-m-d', strtotime('-1 months'));
} else if($filterRetroact == '3') {
	$retroactDate 	= date('Y-m-d', strtotime('-3 months'));
} else if($filterRetroact == '6') {
	$retroactDate 	= date('Y-m-d', strtotime('-6 months'));
} else if($filterRetroact == '9') {
	$retroactDate 	= date('Y-m-d', strtotime('-9 months'));
} else if($filterRetroact == '12') {
	$retroactDate 	= date('Y-m-d', strtotime('-12 months'));
}
if($filterExpired == 'ONLINE') {
	$filterExpiredCond 	= " (
							$fieldStartDate <= '$nowDate' AND 
							($fieldEndDate IS NULL OR $fieldEndDate >= '$nowDate')
							) ";
} else if($filterExpired == 'FORWARD') {
	$filterExpiredCond 	= " ($fieldStartDate > '$nowDate') ";
} else if($filterExpired == 'EXPIRED') {
	$filterExpiredCond 	= " ($fieldEndDate IS NOT NULL AND $fieldEndDate < '$nowDate') ";
}
// Generate order and limit
$startPage 		= ($page - 1) * $recordDisplay;
$order	 		= "ORDER BY $sortCol $sortBy LIMIT $startPage, $recordDisplay";
$orderSpecial 	= "ORDER BY $sortCol $sortBySpecial LIMIT $startPage, $recordDisplay";

$searchCol = $tableInfo['keyFieldName'];
if(hasValue($_REQUEST['searchCol']) && hasValue($_REQUEST['searchInput'])) {
	$searchCol		= $_REQUEST['searchCol'];
	$searchInput	= $_REQUEST['searchInput'];
	$like			= "$searchCol like '%$searchInput%'";
}

// check for table that display special
switch ($tableName) {
	case 'orders':
		header("location:table_data_orders.php?sortCol=$sortCol&sortBy=$sortBySpecial&order=$orderSpecial&searchCol=$searchCol&searchInput=$searchInput&filter=$filter&filterRetroact=$filterRetroact");
		break;
	case 'receives':
		header("location:table_data_receives.php?sortCol=$sortCol&sortBy=$sortBySpecial&order=$orderSpecial&searchCol=$searchCol&searchInput=$searchInput&filter=$filter&filterRetroact=$filterRetroact");
		break;
	case 'booking':
		header("location:table_data_booking.php?sortCol=$sortCol&sortBy=$sortBySpecial&order=$orderSpecial&searchCol=$searchCol&searchInput=$searchInput&filter=$filter&filterRetroact=$filterRetroact");
		break;
}

// Query table data (table need join)
switch ($tableName) {
	case 'beds':
		$where = 'WHERE b.room_id = r.room_id ';
		if(hasValue($like)) {
			$like	= str_replace('room_id', 'r.room_name', $like);
			$where .= " AND $like";
		}
		$sql = "SELECT b.bed_id,
				b.bed_name,
				r.room_name room_id 
				FROM beds b, rooms r 
				$where 
				$order";
		break;

	case 'positions':
		if(hasValue($like)) {
			$like	= str_replace('otr_id', 'o.otr_name', $like);
			$where .= " WHERE $like";
		}
		$sql = "SELECT p.pos_id,
				p.pos_name,
				o.otr_name otr_id 
				FROM positions p LEFT JOIN overtime_rates o 
				ON p.otr_id = o.otr_id 
				$where 
				$order";
		break;

	case 'bank_accounts':
		if(hasValue($like)) {
			$where .= " WHERE $like";
		}
		$sql = "SELECT bnkacc_id,
				bnkacc_no,
				bnkacc_name,
				bnkacc_bank,
				bnkacc_branch,
				bnkacc_type,
				IF(bnkacc_status,'ใช้งานอยู่','ไม่ได้ใช้งาน') bnkacc_status 
				FROM bank_accounts 
				$where 
				$order";
		break;

	case 'spa':
		if(hasValue($like)) {
			$where .= " WHERE $like";
		}
		$sql = "SELECT spa_id,
				spa_name,
				spa_tel,
				spa_fax,
				spa_email,
				IF(spa_status,'ใช้งานอยู่','ไม่ได้ใช้งาน') spa_status 
				FROM spa 
				$where 
				$order";
		break;

	case 'titles':
		$where = 'LEFT JOIN sex s ON t.sex_id = s.sex_id ';
		if(hasValue($like)) {
			$like	= str_replace('sex_id', 's.sex_name', $like);
			$where .= " WHERE $like";
		}
		$sql = "SELECT t.title_id,
				t.title_name,
				IFNULL(s.sex_name,'ไม่ระบุ') sex_id 
				FROM titles t  
				$where 
				$order";
		break;

		case'booking':
			$where		= 'WHERE b.cus_id = c.cus_id and b.emp_id = e.emp_id and b.status_id = s.bkgstat_id ';
			if(hasValue($like)) {
				if($searchCol == 'emp_id') {
					$like = "(e.emp_name like '%$searchInput%' OR e.emp_surname like '%$searchInput%') ";
				} else if($searchCol == 'cus_id') {
					$like = "(c.cus_name like '%$searchInput%' OR c.cus_surname like '%$searchInput%') ";
				} else {
					$like		= str_replace('cus_id','c.cus_name', $like);
					$like		= str_replace('emp_id','e.emp_name', $like);
					$like		= str_replace('status_id','s.bkgstat_name', $like);
					$like		= str_replace('bnkacc_id','a.bnkacc_no', $like);
				}
				$where .= " AND $like";						
			} else {
				$where .= " AND b.bkg_date >= '$retroactDate' ";
			}
			$sql = "SELECT b.bkg_id,
					CONCAT(c.cus_name, ' ', c.cus_surname) cus_id,
					CONCAT(e.emp_name, ' ', e.emp_surname) emp_id,
					a.bnkacc_no bnkacc_id,
					b.bkg_transfer_date,
					b.bkg_transfer_time,
					b.bkg_transfer_evidence,
					b.bkg_total_price,
					b.bkg_date,
					b.bkg_time,
					s.bkgstat_name status_id,
					b.bkg_transfer_money 
					FROM booking b, booking_status s, employees e, bank_accounts a, customers c 
					$where 
					$orderSpecial";
			$sortBy = $sortBySpecial;
			break;

		case 'service_lists':
			$where		= 'WHERE s.svltyp_id = t.svltyp_id';
			if(hasValue($like)) {
				$like		= str_replace('svltyp_id', 't.svltyp_name', $like);
				$like		= str_replace('svl_min', "IFNULL(s.svl_hr,0) * 60 + IFNULL(s.svl_min,0)", $like);
				$where .= " AND $like";
				//$where	   .= ' AND '.$like;
			} else {
				$where .= " AND $filterExpiredCond ";
			}
			$sql = "SELECT s.svl_picture,
					s.svl_id,
					s.svl_name,
					t.svltyp_name svltyp_id,
					IFNULL(s.svl_hr,0) * 60 + IFNULL(s.svl_min,0) svl_min,
					s.svl_price 
					FROM service_lists s, service_list_types t 
					$where 
					$order";
			break;

		case 'employees':
			$where = 'WHERE e.sex_id = s.sex_id AND e.title_id = t.title_id AND p.pos_id = e.pos_id ';
			if(hasValue($like)) {
				$like		= "$searchCol like '%$searchInput%'";
				$like		= str_replace('sex_id', 's.sex_name', $like);
				$like		= str_replace('title_id', 't.title_name', $like);
				$like		= str_replace('pos_id', 'p.pos_name', $like);
				$where	   .= ' AND '.$like;
			}
			$sql = "SELECT e.emp_pic,
					e.emp_id,
					t.title_name title_id,
					e.emp_name,
					e.emp_surname,
					s.sex_name sex_id,
					p.pos_name pos_id 
					FROM employees e, sex s, titles t, positions p 
					$where
					$order";
			break;

	case 'customers':
		$where = 'WHERE c.sex_id = s.sex_id AND c.custype_id = ct.custype_id and c.title_id = t.title_id ';
		if(hasValue($like)) {
			$like	= str_replace('sex_id', 's.sex_name', $like);
			$like	= str_replace('custype_id', 'ct.custype_name', $like);
			$like	= str_replace('title_id', 't.title_name', $like);
			$where .= " AND $like";
		}
		$sql = "SELECT c.cus_id,
				t.title_name title_id,
				c.cus_name,
				c.cus_surname,
				s.sex_name sex_id,
				ct.custype_name custype_id 
				FROM customers c, sex s, customer_types ct, titles t 
				$where 
				$order";
		break;
	
	case 'package_service_lists':
		$where = 'WHERE ps.svl_id = s.svl_id AND ps.pkg_id = p.pkg_id ';
		if(hasValue($like)) {
			$like	= str_replace('room_id', 'r.room_name', $like);
			$where .= " AND $like";
		}
		$sql = "SELECT ps.pkgsvl_id,
				s.svl_name svl_id,
				p.pkg_name pkg_id 
				FROM package_service_lists ps, service_lists s, packages p 
				$where 
				$order";
		break;

	case 'promotion_service_lists':
		$where = 'WHERE ps.svl_id = s.svl_id AND ps.prm_id = p.prm_id ';
		if(hasValue($like)) {
			$like	= str_replace('svl_id', 's.svl_name', $like);
			$like	= str_replace('prm_id', 'p.prm_name', $like);
			$where .= " AND $like";
		}
		$sql = "SELECT ps.prmsvl_id,
				s.svl_name svl_id,
				p.prm_name prm_id,
				ps.prmsvl_start,
				ps.prmsvl_end,
				ps.prmsvl_desc,
				ps.prmsvl_price 
				FROM promotion_service_lists ps, service_lists s, promotions p 
				$where 
				$order";
		break;

	case 'promotion_products':
		$where = 'WHERE prmprd.prdprm_id = prdprm.prdprm_id AND prmprd.prd_id = p.prd_id ';
		if(hasValue($like)) {
			$like	= str_replace('prdprm_id', 'prdprm.prdprm_name', $like);
			$like	= str_replace('prd_id', 'p.prd_id', $like);
			$like	= str_replace('prmprd_discout_type', "COALESCE(CONCAT(prmprd.prmprd_discout,' ', prmprd.prmprd_discout_type), 'ฟรี')", $like);
			$like	= str_replace('%%%','%\%%', $like);
			$where .= " AND $like";
		} else {
			$where .= " AND $filterExpiredCond ";
		}
		$sql = "SELECT 	prmprd.prmprd_id,
				p.prd_name prd_id,
				COALESCE(CONCAT(prmprd.prmprd_discout,' ', prmprd.prmprd_discout_type), 'ฟรี') prmprd_discout_type,
				prdprm.prdprm_name prdprm_id,
				prmprd.prmprd_startdate,
				prmprd.prmprd_enddate 
				FROM promotion_products prmprd, product_promotions prdprm, products p 
				$where 
				$orderSpecial";
		break;
	 	
	case 'products':
		$where = 'WHERE p.prdtyp_id = pt.prdtyp_id AND p.unit_id = u.unit_id AND p.brand_id = b.brand_id ';
		if(hasValue($like)) {
			$like	= str_replace('prdtyp_id', 'pt.prdtyp_name', $like);
			$like	= str_replace('unit_id', 'u.unit_name', $like);
			$like	= str_replace('brand_id', 'b.brand_name', $like);
			$like	= str_replace('prd_barcode', 'p.prd_barcode', $like);

			$where .= " AND $like";
		}
		$sql = "SELECT p.prd_id,
				p.prd_name,
				pt.prdtyp_name prdtyp_id,
				b.brand_name brand_id,
				p.prd_price,
				p.prd_amount,
				u.unit_name unit_id 
				FROM 	products p,
						product_types pt,
						units u,
						brands b  
				$where 
				$order";
		break;

	case 'time_attendances':
		$where = 'WHERE t.emp_id = e.emp_id ';
		if(hasValue($like)) {
			if($searchCol == 'emp_id') {
				$like = "(e.emp_name like '%$searchInput%' OR e.emp_surname like '%$searchInput%') ";
			}
			$where .= " AND $like";
		} else {
			$where .= " AND t.dateatt_in >= '$retroactDate' ";
			$whereAllRecord .= " WHERE dateatt_in >= '$retroactDate' ";
		}
		$sql = "SELECT t.timeatt_id,
				CONCAT(e.emp_name, '  ', e.emp_surname) emp_id,
				t.dateatt_in,
				SUBSTRING(t.timeatt_in,1,5) as timeatt_in,
				-- t.dateatt_out,
				SUBSTRING(t.timeatt_out,1,5) as timeatt_out  
				FROM time_attendances t, employees e 
				$where 
				$orderSpecial";
		$sortBy = $sortBySpecial;
		break;
 	 	 	 	
	case 'payrolls':
		$where = 'WHERE p.emp_id = e.emp_id ';
		if(hasValue($like)) {
			if($searchCol == 'emp_id') {
				$like = "(e.emp_name like '%$searchInput%' OR e.emp_surname like '%$searchInput%') ";
			} else if($searchCol == 'payroll_salary') {
				$like = "(p.payroll_commission + p.payroll_overtime + p.payroll_salary like '%$searchInput%') ";
			}
			$where .= " AND $like";
		} else {
			$where .= " AND p.payroll_date >= '$retroactDate' ";
			$whereAllRecord .= " WHERE payroll_date >= '$retroactDate' ";
		}
		$sql = "SELECT p.payroll_id,
				p.payroll_date,
				p.payroll_monthly,
				CONCAT(e.emp_name, '  ', e.emp_surname) emp_id,
				p.payroll_commission + p.payroll_overtime + p.payroll_salary AS payroll_salary 
				FROM payrolls p, employees e 
				$where 
				$orderSpecial";
		$sortBy = $sortBySpecial;
		break;
 	  	 	 	 	 	 	
	case 'services':
		$where = 'WHERE s.cus_id = c.cus_id '
			   . 'AND s.emp_id = e.emp_id AND s.paytyp_id = p.paytyp_id AND s.bed_id = b.bed_id ';
		if(hasValue($like)) {
			if($searchCol == 'cus_id') {
				$like = "(c.cus_name like '%$searchInput%' OR c.cus_surname like '%$searchInput%') ";
			} else if($searchCol == 'emp_id') {
				$like = "(e.emp_name like '%$searchInput%' OR e.emp_surname like '%$searchInput%') ";
			} else {
				$like	= str_replace('paytyp_id', 'p.paytyp_name', $like);
				$like	= str_replace('bed_id', 'b.bed_name', $like);
			}
			$where .= " AND $like";
		} else {
			$where .= " AND s.ser_date >= '$retroactDate' ";
			$whereAllRecord .= " WHERE ser_date >= '$retroactDate' ";
		}
		$sql = "SELECT s.ser_id,
				s.bkg_id,
				s.ser_time,
				CONCAT(c.cus_name, '  ', c.cus_surname) cus_id,
				CONCAT(e.emp_name, '  ', e.emp_surname) emp_id,
				s.ser_date,
				p.paytyp_name paytyp_id,
				b.bed_name bed_id,
				s.ser_total_price 
				FROM services s, customers c, employees e, pay_types p, beds b 
				$where 
				$orderSpecial ";
		$sortBy = $sortBySpecial;
		break;
 	 	 	 	
	case 'package_details':
		$where = 'WHERE p.emp_id = e.emp_id ';
		if(hasValue($like)) {
			if($searchCol == 'emp_id') {
				$like = "(e.emp_name like '%$searchInput%' OR e.emp_surname like '%$searchInput%') ";
			}
			$where .= " AND $like";
		}
		$sql = "SELECT p.pkgdtl_id,
				p.serpkg_id,
				p.pkgsvl_id,
				CONCAT(e.emp_name, '  ', e.emp_surname) emp_id,
				p.pkgdtl_comm 
				FROM package_details p, employees e 
				$where 
				$order";
		break;

	case 'service_list_details':
		$where = 'WHERE s.emp_id = e.emp_id ';
		if(hasValue($like)) {
			if($searchCol == 'emp_id') {
				$like = "(e.emp_name like '%$searchInput%' OR e.emp_surname like '%$searchInput%') ";
			}
			$where .= " AND $like";
		}
		$sql = "SELECT s.svldtl_id,
				s.sersvl_id,
				CONCAT(e.emp_name, '  ', e.emp_surname) emp_id,
				s.svldtl_com 
				FROM service_list_details s, employees e 
				$where 
				$order";
		break;
 	 	 	 	
	case 'promotion_details':
		$where = 'WHERE p.emp_id = e.emp_id ';
		if(hasValue($like)) {
			if($searchCol == 'emp_id') {
				$like = "(e.emp_name like '%$searchInput%' OR e.emp_surname like '%$searchInput%') ";
			}
			$where .= " AND $like";
		}
		$sql = "SELECT p.prmdtl_id,
				CONCAT(e.emp_name, '  ', e.emp_surname) emp_id,
				p.serprm_id,
				p.svl_id,
				p.prmdtl_comm 
				FROM promotion_details p, employees e 
				$where 
				$order";
		break;

	case 'element_checks':
		$where = 'WHERE ec.cus_id = c.cus_id AND ec.emp_id = e.emp_id AND ec.eletyp_id = et.eletyp_id';
		if(hasValue($like)) {
			if($searchCol == 'cus_id') {
				$like = "(c.cus_name like '%$searchInput%' OR c.cus_surname like '%$searchInput%') ";
			} else if($searchCol == 'emp_id') {
				$like = "(e.emp_name like '%$searchInput%' OR e.emp_surname like '%$searchInput%') ";
			} else {
				$like	= str_replace('eletyp_id', 'et.eletyp_name', $like);
			}
			$where .= " AND $like";
		} else {
			$where .= " AND ec.elechk_date >= '$retroactDate' ";
			$whereAllRecord .= " WHERE elechk_date >= '$retroactDate' ";
		}
		$sql = "SELECT ec.elechk_id,
				CONCAT(e.emp_name, '  ', e.emp_surname) emp_id,
				CONCAT(c.cus_name, '  ', c.cus_surname) cus_id,
				ec.elechk_date,
				SUBSTRING(ec.elechk_time,1,5) as elechk_time,
				et.eletyp_name eletyp_id 
				FROM element_checks ec, employees e, customers c, element_types et
				$where 
				$orderSpecial ";
		$sortBy = $sortBySpecial;
		break;
 	 	 	 	 	
	case 'orders':
		$where = 'WHERE o.emp_id = e.emp_id AND o.comp_id = c.comp_id '
			   . 'AND o.ordtyp_id = ot.ordtyp_id ';
		if(hasValue($like)) {
			if($searchCol == 'emp_id') {
				$like = "(e.emp_name like '%$searchInput%' OR e.emp_surname like '%$searchInput%') ";
			} else {
				$like	= str_replace('comp_id', 'c.comp_name', $like);
			}
			$where .= " AND $like";
		}
		$sql = "SELECT o.ord_id,
				CONCAT(e.emp_name, '  ', e.emp_surname) emp_id,
				c.comp_name comp_id,
				ot.ordtyp_name ordtyp_id,
				o.ord_date,
				o.ord_snd_date 
				FROM orders o, order_types ot, employees e, companies c 
				$where 
				$order";
		break;
 	 	 	
	case 'order_details':
		$where = 'WHERE o.prd_id = p.prd_id ';
		if(hasValue($like)) {
			$like	= str_replace('prd_id', 'p.prd_name', $like);
			$where .= " AND $like";
		}
		$sql = "SELECT o.orddtl_id,
				o.ord_id,
				p.prd_name prd_id,
				o.orddtl_amount 
				FROM order_details o, products p 
				$where 
				$order";
		break;
 	 	 	 	
	case 'receives':
		$where = 'WHERE r.emp_id = e.emp_id ';
		if(hasValue($like)) {
			if($searchCol == 'emp_id') {
				$like = "(e.emp_name like '%$searchInput%' OR e.emp_surname like '%$searchInput%') ";
			}
			$where .= " AND $like";
		}
		$sql = "SELECT r.rec_id,
				r.ord_id,
				CONCAT(e.emp_name, '  ', e.emp_surname) emp_id,
				r.rec_date,
				r.rec_total_price 
				FROM receives r, employees e 
				$where 
				$order";
		break;
 	 	 	 	
	case 'receive_details':
		$where = 'WHERE r.prd_id = p.prd_id ';
		if(hasValue($like)) {
			$like	= str_replace('prd_id', 'p.prd_name', $like);
			$where .= " AND $like";
		}
		$sql = "SELECT r.recdtl_id,
				r.rec_id,
				p.prd_name prd_id,
				r.recdtl_amount,
				r.recdtl_price 
				FROM receive_details r, products p 
				$where 
				$order";
		break;

	case 'withdraws':
		$where = 'WHERE w.emp_give_id = eg.emp_id AND w.emp_id = e.emp_id AND w.wdwtyp_id = wt.wdwtyp_id ';
		if(hasValue($like)) {
			if($searchCol == 'emp_id') {
				$like = "(e.emp_name like '%$searchInput%' OR e.emp_surname like '%$searchInput%') ";
			} else if($searchCol == 'emp_give_id') {
				$like = "(eg.emp_name like '%$searchInput%' OR eg.emp_surname like '%$searchInput%') ";
			}
			$like	= str_replace('wdwtyp_id', 'wt.wdwtyp_name', $like);
			$where .= " AND $like";
		} else {
			$where .= " AND w.wdw_date >= '$retroactDate' ";
			$whereAllRecord .= " WHERE wdw_date >= '$retroactDate' ";
		}
		$sql = "SELECT w.wdw_id,
				CONCAT(eg.emp_name, '  ', eg.emp_surname) emp_give_id,
				CONCAT(e.emp_name, '  ', e.emp_surname) emp_id,
				w.wdw_date,
				wt.wdwtyp_name wdwtyp_id 
				FROM withdraws w, employees eg, employees e, withdraw_types wt 
				$where 
				$orderSpecial ";
		$sortBy = $sortBySpecial;
		break;
 	 	 	
	case 'withdraw_details':
		$where = 'WHERE w.prd_id = p.prd_id ';
		if(hasValue($like)) {
			$like	= str_replace('prd_id', 'p.prd_name', $like);
			$where .= " AND $like";
		}
		$sql = "SELECT w.wdwdtl_id,
				w.wdw_id,
				p.prd_name prd_id,
				w.wdwdtl_amount 
				FROM withdraw_details w, products p 
				$where 
				$order";
		break;
 	 	 	
	case 'sales':
		$where = 'WHERE s.emp_id = e.emp_id ';
		if(hasValue($like)) {
			if($searchCol == 'emp_id') {
				$like = "(e.emp_name like '%$searchInput%' OR e.emp_surname like '%$searchInput%') ";
			}
			$where .= " AND $like";
		} else {
			$where .= " AND s.sale_date >= '$retroactDate' ";
			$whereAllRecord .= " WHERE sale_date >= '$retroactDate' ";
		}
		$sql = "SELECT s.sale_id,
				CONCAT(e.emp_name, '  ', e.emp_surname) emp_id,
				s.sale_date,
				s.sale_time,
				s.sale_total_price 
				FROM sales s, employees e 
				$where 
				$orderSpecial";
		$sortBy = $sortBySpecial;
		break;
 	 	 	 	
	case 'sale_details':
		$where = 'WHERE s.prd_id = p.prd_id ';
		if(hasValue($like)) {
			$like	= str_replace('prd_id', 'p.prd_name', $like);
			$where .= " AND $like";
		}
		$sql = "SELECT s.saledtl_id,
				s.sale_id,
				p.prd_name prd_id,
				s.saledtl_amount,
				s.saledtl_price 
				FROM sale_details s, products p 
				$where 
				$order";
		break;

	case 'product_promotions':
		$where = 'WHERE p.prdprmgrp_id = g.prdprmgrp_id ';
		if(hasValue($like)) {
			$like	= str_replace('prdprmgrp_id', 'g.prdprmgrp_name', $like);
			$where .= " AND $like";
		} else {
			$where .= " AND $filterExpiredCond ";
		}
		$sql = "SELECT p.prdprm_id,
				p.prdprm_name,
				p.prdprm_type,
				g.prdprmgrp_name prdprmgrp_id,
				p.prdprm_startdate,
				p.prdprm_enddate 
				FROM product_promotions p, product_promotion_groups g 
				$where 
				$orderSpecial";
		$sortBy = $sortBySpecial;
		break;

	case 'promotion_products':
		$where = 'WHERE prmprd.prdprm_id = prdprm.prdprm_id AND prmprd.prd_id = p.prd_id ';
		if(hasValue($like)) {
			$like	= str_replace('prdprm_id', 'prdprm.prdprm_name', $like);
			$like	= str_replace('prd_id', 'p.prd_id', $like);
			$like	= str_replace('prmprd_discout_type', "COALESCE(CONCAT(prmprd.prmprd_discout,' ', prmprd.prmprd_discout_type), 'ฟรี')", $like);
			$like	= str_replace('%%%','%\%%', $like);
			$where .= " AND $like";
		}
		$sql = "SELECT prmprd.prmprd_id,
				p.prd_name prd_id,
				prdprm.prdprm_name prdprm_id,
				COALESCE(CONCAT(prmprd.prmprd_discout,' ', prmprd.prmprd_discout_type), 'ฟรี') prmprd_discout_type,
				prmprd.prmprd_startdate,
				prmprd.prmprd_enddate 
				FROM promotion_products prmprd, product_promotions prdprm, products p 
				$where 
				$orderSpecial";
		$sortBy = $sortBySpecial;
		break;

	case 'withdraw_types':
		if(hasValue($like)) {
			$where = " WHERE $like";
		}
		$sql = "SELECT wdwtyp_id,
				wdwtyp_name,
				IF(wdwtyp_increase_shelf_amount,'เบิกผลิตภัณฑ์ไปวางขายหน้าร้าน','เบิกผลิตภัณฑ์ไปใช้ทันที') wdwtyp_increase_shelf_amount 
				FROM withdraw_types 
				$where 
				$order";
		break;

	case 'promotion_discout_sales':
		$where = 'WHERE p.prdprmgrp_id = g.prdprmgrp_id ';
		if(hasValue($like)) {
			$like	= str_replace('prdprmgrp_id', 'g.prdprmgrp_name', $like);
			$like	= str_replace('%%%','%\%%', $like);
			$where .= " AND $like";
		} else {
			$where .= " AND $filterExpiredCond ";
		}
		$sql = "SELECT p.prmds_id,
				p.prmds_name,
				g.prdprmgrp_name prdprmgrp_id,
				p.prmds_purchase,
				COALESCE(CONCAT(p.prmds_discout,' ', p.prmds_discout_type), 'ฟรี') prmds_discout_type,
				p.prmds_startdate,
				p.prmds_enddate 
				FROM promotion_discout_sales p, product_promotion_groups g  
				$where 
				$orderSpecial";
		$sortBy = $sortBySpecial;
		break;

	case 'advertising':
		if(hasValue($like)) {
			$where = " WHERE $like";
		}
		$sql = "SELECT avs_id,
				avs_name,
				IF(avs_status,'แสดงประชาสัมพันธ์','ไม่แสดงประชาสัมพันธ์') avs_status 
				FROM advertising 
				$where 
				$orderSpecial";
			$sortBy = $sortBySpecial;
		break;

	case 'package_promotions':
		$where = 'WHERE p.custype_id = c.custype_id ';
		if(hasValue($like)) {
			$like	= str_replace('custype_id', 'c.custype_name', $like);
			$where .= " AND $like";
		} else {
			$where .= " AND $filterExpiredCond ";
		}
		$sql = "SELECT p.pkgprm_id,
				p.pkgprm_name,
				c.custype_name custype_id,
				p.pkgprm_startdate,
				p.pkgprm_enddate 
				FROM package_promotions p, customer_types c 
				$where 
				$orderSpecial";
		$sortBy = $sortBySpecial;
		break;

	case 'service_list_promotions':
		$where = 'WHERE p.custype_id = c.custype_id ';
		if(hasValue($like)) {
			$like	= str_replace('custype_id', 'c.custype_name', $like);
			$where .= " AND $like";
		} else {
			$where .= " AND $filterExpiredCond ";
		}
		$sql = "SELECT p.svlprm_id,
				p.svlprm_name,
				c.custype_name custype_id,
				p.svlprm_startdate,
				p.svlprm_enddate 
				FROM service_list_promotions p, customer_types c 
				$where 
				$orderSpecial";
		$sortBy = $sortBySpecial;
		break;
	
	case 'package_promotion_details':
		$where = 'WHERE p.pkg_id = d.pkg_id and t.pkgprm_id = d.pkgprm_id ';
		if(hasValue($like)) {
			$like	= str_replace('pkgprm_id', 't.pkgprm_name', $like);
			$like	= str_replace('pkg_id', 'p.pkg_name', $like);
			$like	= str_replace('pkgprmdtl_discout_type', "COALESCE(CONCAT(d.pkgprmdtl_discout,' ', d.pkgprmdtl_discout_type))", $like);
			$like	= str_replace('%%%','%\%%', $like);
			$where .= " AND $like";
		} else {
			$where .= " AND $filterExpiredCond ";
		}
		$sql = "SELECT d.pkgprmdtl_id,
				p.pkg_name pkg_id,
				COALESCE(CONCAT(d.pkgprmdtl_discout,' ', d.pkgprmdtl_discout_type)) pkgprmdtl_discout_type,
				t.pkgprm_name pkgprm_id,
				d.pkgprmdtl_startdate,
				d.pkgprmdtl_enddate 
				FROM package_promotion_details d, package_promotions t, packages p 
				$where 
				$orderSpecial";
		$sortBy = $sortBySpecial;
		break;

	case 'service_list_promotion_details':
		$where = 'WHERE p.svl_id = d.svl_id and t.svlprm_id = d.svlprm_id ';
		if(hasValue($like)) {
			$like	= str_replace('svlprm_id', 't.svlprm_name', $like);
			$like	= str_replace('svl_id', 'p.svl_name', $like);
			$like	= str_replace('svlprmdtl_discout_type', "COALESCE(CONCAT(d.svlprmdtl_discout,' ', d.svlprmdtl_discout_type))", $like);
			$like	= str_replace('%%%','%\%%', $like);
			$where .= " AND $like";
		} else {
			$where .= " AND $filterExpiredCond ";
		}
		$sql = "SELECT d.svlprmdtl_id,
				p.svl_name svl_id,
				COALESCE(CONCAT(d.svlprmdtl_discout,' ', d.svlprmdtl_discout_type)) svlprmdtl_discout_type,
				t.svlprm_name svlprm_id,
				d.svlprmdtl_startdate,
				d.svlprmdtl_enddate 
				FROM service_list_promotion_details d, service_list_promotions t, service_lists p 
				$where 
				$orderSpecial";
		$sortBy = $sortBySpecial;
		break;

	case 'privileges':
		$where = 'WHERE 1 ';
		if(hasValue($like)) {
			$where .= " AND $like";
		}
		$sql = "SELECT p.privlg_id, 
				p.privlg_name_th 
				FROM privileges p 
				$where 
				$order";
		break;
 	 	 	 	
	case 'grant_privileges':
		$where = 'WHERE e.emp_id = g.emp_id ';
		if(hasValue($like)) {
			if($searchCol == 'emp_id') {
				$like = "(e.emp_name like '%$searchInput%' OR e.emp_surname like '%$searchInput%') ";
			}
			$like	= str_replace('grnprivlg_id', 'e.emp_id', $like);
			$where .= " AND $like";
		}
		$sql = "SELECT 	e.emp_id AS grnprivlg_id,
						CONCAT(e.emp_name, '  ', e.emp_surname) AS emp_id, 
						COUNT(g.grnprivlg_id) AS privlg_id  
				FROM grant_privileges g, employees e 
				$where 
				GROUP BY e.emp_id 
				$order";
		break;

	default:
		if(hasValue($like)) {
			$where = "WHERE $like";
		} else if(hasValue($filterExpiredCond)) {
			$where = "WHERE $filterExpiredCond";
		}
		$sql = "SELECT * FROM $tableName $where $order";
		break;
}

$result		= mysql_query($sql, $dbConn);
$rows 		= mysql_num_rows($result);
$tableData	= array();

// Find all record
if($tableName == 'grant_privileges') {
	$sqlAllRecord = "SELECT COUNT(*) allRecords FROM ($sql) a";
} else if((hasValue($_REQUEST['searchCol']) && hasValue($_REQUEST['searchInput'])) || 
	hasValue($filterExpiredCond)) {
	$txtSelect = substr($sql, strpos($sql, 'SELECT'), strpos($sql, 'FROM'));
	$txtLimit  = substr($sql, strpos($sql, 'LIMIT'));
	$newSelect = "SELECT COUNT(*) allRecords ";
	$sqlAllRecord = str_replace($txtSelect, $newSelect, $sql);
	$sqlAllRecord = str_replace($txtLimit, '', $sqlAllRecord);
} else {
	$sqlAllRecord 		= "SELECT COUNT(*) allRecords FROM $tableName $whereAllRecord";
}
$resultAllRecord 	= mysql_query($sqlAllRecord, $dbConn);
$allRecordsRows 	= mysql_fetch_assoc($resultAllRecord);
$allRecords 		= $allRecordsRows['allRecords'];

// Get table data
for($i = 0; $i < $rows; $i++) {
	array_push($tableData, mysql_fetch_assoc($result));
}

?>

<?
/*
 * Display Zone
 */

// Hide if no privileges
$viewPrivileges 	= true;
$displayAddBtn 		= true;
$displayEditBtn 	= true;
$displayDeleteBtn 	= true;
switch ($tableName) {
	case 'withdraws':
		if(!$emp_privileges['view_withdraws'])
			$viewPrivileges = false;
		if(!$emp_privileges['insert_withdraws'])
			$displayAddBtn = false;
		if(!$emp_privileges['update_withdraws'])
			$displayEditBtn = false;
		if(!$emp_privileges['delete_withdraws'])
			$displayDeleteBtn = false;
		break;

	case 'sales':
		if(!$emp_privileges['view_sales'])
			$viewPrivileges = false;
		if(!$emp_privileges['insert_sales'])
			$displayAddBtn = false;
		if(!$emp_privileges['update_sales'])
			$displayEditBtn = false;
		if(!$emp_privileges['delete_sales'])
			$displayDeleteBtn = false;
		break;

	case 'services':
		if(!$emp_privileges['manage_services']) {
			$viewPrivileges = false;
			$displayAddBtn = false;
			$displayEditBtn = false;
			$displayDeleteBtn = false;
		}
		break;

	case 'spa':
		if(!$emp_privileges['manage_spa']) {
			$viewPrivileges = false;
			$displayAddBtn = false;
			$displayEditBtn = false;
			$displayDeleteBtn = false;
		}
		break;

	case 'shops':
		if(!$emp_privileges['manage_shops']) {
			$viewPrivileges = false;
			$displayAddBtn = false;
			$displayEditBtn = false;
			$displayDeleteBtn = false;
		}
		break;

	case 'customers':
		if(!$emp_privileges['manage_customers']) {
			$viewPrivileges = false;
			$displayAddBtn = false;
			$displayEditBtn = false;
			$displayDeleteBtn = false;
		}
		break;

	case 'employees':
		if(!$emp_privileges['manage_employees']) {
			$viewPrivileges = false;
			$displayAddBtn = false;
			$displayEditBtn = false;
			$displayDeleteBtn = false;
		}
		break;

	case 'service_lists':
		if(!$emp_privileges['manage_service_lists']) {
			$viewPrivileges = false;
			$displayAddBtn = false;
			$displayEditBtn = false;
			$displayDeleteBtn = false;
		}
		break;

	case 'service_list_promotions':
		if(!$emp_privileges['manage_service_lists']) {
			$viewPrivileges = false;
			$displayAddBtn = false;
			$displayEditBtn = false;
			$displayDeleteBtn = false;
		}
		break;

	case 'service_list_promotion_details':
		if(!$emp_privileges['manage_service_lists']) {
			$viewPrivileges = false;
			$displayAddBtn = false;
			$displayEditBtn = false;
			$displayDeleteBtn = false;
		}
		break;

	case 'packages':
		if(!$emp_privileges['manage_packages']) {
			$viewPrivileges = false;
			$displayAddBtn = false;
			$displayEditBtn = false;
			$displayDeleteBtn = false;
		}
		break;

	case 'package_promotions':
		if(!$emp_privileges['manage_packages']) {
			$viewPrivileges = false;
			$displayAddBtn = false;
			$displayEditBtn = false;
			$displayDeleteBtn = false;
		}
		break;

	case 'package_promotion_details':
		if(!$emp_privileges['manage_packages']) {
			$viewPrivileges = false;
			$displayAddBtn = false;
			$displayEditBtn = false;
			$displayDeleteBtn = false;
		}
		break;

	case 'companies':
		if(!$emp_privileges['manage_companies']) {
			$viewPrivileges = false;
			$displayAddBtn = false;
			$displayEditBtn = false;
			$displayDeleteBtn = false;
		}
		break;

	case 'products':
		if(!$emp_privileges['manage_products']) {
			$viewPrivileges = false;
			$displayAddBtn = false;
			$displayEditBtn = false;
			$displayDeleteBtn = false;
		}
		break;

	case 'product_promotion_groups':
		if(!$emp_privileges['manage_products']) {
			$viewPrivileges = false;
			$displayAddBtn = false;
			$displayEditBtn = false;
			$displayDeleteBtn = false;
		}
		break;

	case 'product_promotions':
		if(!$emp_privileges['manage_products']) {
			$viewPrivileges = false;
			$displayAddBtn = false;
			$displayEditBtn = false;
			$displayDeleteBtn = false;
		}
		break;

	case 'promotion_discout_sales':
		if(!$emp_privileges['manage_promotion_discout_sales']) {
			$viewPrivileges = false;
			$displayAddBtn = false;
			$displayEditBtn = false;
			$displayDeleteBtn = false;
		}
		break;


	case 'advertising':
		if(!$emp_privileges['manage_advertising']) {
			$viewPrivileges = false;
			$displayAddBtn = false;
			$displayEditBtn = false;
			$displayDeleteBtn = false;
		}
		break;

	case 'time_attendances':
		if(!$emp_privileges['manage_time_attendances']) {
			$viewPrivileges = false;
			$displayAddBtn = false;
			$displayEditBtn = false;
			$displayDeleteBtn = false;
		}
		break;

	case 'payrolls':
		if(!$emp_privileges['manage_payrolls']) {
			$viewPrivileges = false;
			$displayAddBtn = false;
			$displayEditBtn = false;
			$displayDeleteBtn = false;
		}
		break;

	case 'booking':
		if(!$emp_privileges['manage_booking']) {
			$viewPrivileges = false;
			$displayAddBtn = false;
			$displayEditBtn = false;
			$displayDeleteBtn = false;
		}
		break;

	case 'element_checks':
		if(!$emp_privileges['manage_element_checks']) {
			$viewPrivileges = false;
			$displayAddBtn = false;
			$displayEditBtn = false;
			$displayDeleteBtn = false;
		}
		break;

	case 'orders':
		if(!$emp_privileges['manage_orders']) {
			$viewPrivileges = false;
			$displayAddBtn = false;
			$displayEditBtn = false;
			$displayDeleteBtn = false;
		}
		break;
	default:
		$standardtables = array(
			'sex',
			'titles',
			'positions',
			'units',
			'bank_accounts',
			'rooms',
			'beds',
			'order_status',
			'booking_status',
			'brands',
			'pay_types',
			'element_types',
			'product_types',
			'order_types',
			'withdraw_types',
			'service_list_types',
			'customer_types',
			'holidays'

		);
		if(in_array($tableName, $standardtables)) {
			if(!$emp_privileges['manage_standard_tables']) {
				$viewPrivileges = false;
				$displayAddBtn = false;
				$displayEditBtn = false;
				$displayDeleteBtn = false;
			}
		}
	break;
}

// Table that can't add edit and delete
if($tableName == 'privileges') {
	$displayAddBtn = false;
	$displayEditBtn = false;
	$displayDeleteBtn = false;
}

if(!$viewPrivileges && !$displayAddBtn && !$displayEditBtn && !$displayDeleteBtn) {
	echo alertNoPrivlgTableData();
} else if($rows > 0){
//Has record will display table data
?>
<table class="mbk mbk-table-sortable">
	<? include('table_data_thead.php') ?>
	<tbody id="table-data">
		<?
		foreach($tableData as $key => $row) {
			$code = $row[$tableInfo['keyFieldName']];/*คืนค่ามาเป็นชื่อฟิล์ล PK*/
			?>
			<tr id="<?=$code?>">
				<td class="icon-col">
					<input type="checkbox" value="<?=$code?>" name="table-record[]" class="mbk-checkbox" onclick="checkRecord(this)">
				</td>
				<td class="action-col">
					<?
					if($tableName == 'employees'){
						?>
						<a title="พิมพ์บัตรพนักงาน">
							<i class="fa fa-credit-card" onclick="openPrintEmpCard('<?=$code?>')"></i>
						</a>
						<?
					} else if($tableName == 'sales' && $emp_privileges["print_sales_receipt"]) {
						?>
						<a title="ดูใบเสร็จ">
							<i class="fa fa-file-text-o" onclick="openPrintReceipt('<?=$code?>')"></i>
						</a>
						<?
					} else if($tableName == 'services' && $emp_privileges["manage_services"]) {
						?>
						<a title="ดูใบเสร็จ">
							<i class="fa fa-file-text-o" onclick="openPrintServiceReceipt('<?=$code?>')"></i>
						</a>
						<?
					}
					if($displayEditBtn && $filterExpired != 'EXPIRED') {
					?>
						<a title="แก้ไข">
							<i class="fa fa-pencil" onclick="openFormTable('EDIT', '<?=$code?>')"></i>
						</a>
					<?
					}
					if($displayDeleteBtn) {
					?>
						<a title="ลบ">
							<?
								if($tableName == 'withdraws') {
									?>
									<i class="fa fa-times" onclick="deleteWithdrawsRecord('<?=$code?>')"></i>
									<?
								} else {
									?>
									<i class="fa fa-times" onclick="delteCurrentRecord('<?=$code?>')"></i>
									<?
								}
							?>
						</a>
					<?
					}
					?>
				</td>
			<?
			$offset = 0;
			foreach($row as $field => $value) {
				//Skip hidden field
				if(isset($tableInfo['hiddenFields']) && in_array($field, $tableInfo['hiddenFields'])){
					$offset++;
					continue;
				}
				//Display field
				if ($value == ''){
					if(mysql_field_type($result, $offset) == 'real' || mysql_field_type($result, $offset) == 'int') {
						?>
							<td field="<?=$field?>" class="real-col">-</td>
						<?
					} else {
						?>
							<td field="<?=$field?>">-</td>
						<?
					}
				}else {
					if($field == $tableInfo['keyFieldName']) {
						if(isset($tableInfo['hiddenFields']) || isset($tableInfo['fixedHiddenFields'])) {
							// ถ้าตารางนี้มี hiddenFields แสดงว่าต้องมีหน้าแสดงรายละเอียด
							?>
							<td field="<?=$field?>"><a href="javascript:openFormTable('VIEW_DETAIL', '<?=$value?>');" class="normal-link" title="คลิกเพื่อดูรายละเอียด"><?=$value?></a></td>
							<?
						} else {
							?>
							<td field="<?=$field?>"><?=$value?></td>
							<?
						}
					}
					else if($field == 'payroll_monthly') {
						?>
						<td field="<?=$field?>"><? echo monthlyThaiFormat($value); ?></td>
						<?
					} 
					else if(mysql_field_type($result, $offset) == 'real') {
						?>
						<td field="<?=$field?>" class="real-col"><? echo number_format($value,2);?></td>
						<?
					} 
					else if (mysql_field_type($result, $offset) == 'int'){
						?>
						<td field="<?=$field?>" class="real-col"><?=$value?></td>
						<?
					}
					else if (mysql_field_type($result, $offset) == 'date' || mysql_field_type($result, $offset) == 'datetime'){
						if($value == '') {
							$dateValue 	= '-';
						} else {
							$time 		= strtotime($value);
							$yearMinTH 	= substr(date('Y', $time) + 543, 2);
							$month 		= $monthThaiMin[(int)date('m', $time)-1];
							$dateValue 	= date('d', $time).' '.$month.' '.$yearMinTH;
						}
						?>
						<td field="<?=$field?>"><?=$dateValue?></td>
						<?
					}
					else if (mysql_field_type($result, $offset) == 'time'){
						if($value == '') {
							$timeValue 	= '-';
						} else {
							$timeValue 	= substr($value, 0, 5);
						}
						?>
						<td field="<?=$field?>"><?=$timeValue?></td>
						<?
					}
					else {
						?>
						<td field="<?=$field?>"><?=$value?></td>
						<?
					}
				}
				
				$offset++;
			}
			?>
			</tr>
			<?
		}
		?>
	</tbody>
</table>
<?
} else{
?>
	<!-- No record will display notification-->
	<div id="table-data-empty">
		<img src="../img/backoffice/test.png"><br>
		ไม่พบข้อมูล
	</div>
<?
}
?>

<script id="tmpScriptTableData1" type="text/javascript" src="../js/table_data.js"></script>
<script id="tmpScriptTableData2" type="text/javascript">
	// Set table data
	var table = {
		'name'			: '<?=$tableName?>',
		'nameTH'		: '<?=$tableInfo["tableNameTH"]?>',
		'sortCol'		: '<?=$sortCol?>',
		'sortBy'		: '<?=$sortBy?>',
		'fieldNameList'	: <? echo json_encode($tableInfo['fieldNameList']); ?>,
		'searchFields' 	: 
		<? 
			if(hasValue($tableInfo['searchFields'])) {
				echo json_encode($tableInfo['searchFields']); 
			} else {
				echo "[]"; // empty array
			}
		?>,
		'allRecords'	: '<?=$allRecords?>',
		'deleteTxtField': <? echo json_encode($tableInfo['deleteTxtField']); ?>,
		'deleteTxtPatternMain' 	: '<?=$tableInfo["deleteTxtPatternMain"]?>',
		'deleteTxtPatternMin' 	: '<?=$tableInfo["deleteTxtPatternMin"]?>'
	};
	setTable(table);

	// Config column
	configColumn(<? echo count($tableInfo['fieldNameList']); ?>);

	// Hide or show AddBtn
	<?php
	if($displayAddBtn) {
		?>
		$('#add-record-btn').css('visibility', 'visible');
		<?php
	} else {
		?>
		$('#add-record-btn').css('visibility', 'hidden');
		<?php
	}
	?>

	$('#tmpScriptTableData1').remove();
	$('#tmpScriptTableData2').remove();
</script>