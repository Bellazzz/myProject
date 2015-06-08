<?php
session_start();
$action			= isset($_REQUEST['action']) ? $_REQUEST['action'] : 'ADD';
$tableName		= 'services';
$code			= $_REQUEST['code'];
$hideEditButton = $_REQUEST['hideEditButton'];
$hideBackButton = $_REQUEST['hideBackButton'];

include('../config/config.php');
$tplName = "form_$tableName.html";
$subDir	 = WEB_ROOTDIR.'/backoffice/';
include('../common/common_header.php');
require('../common/function_form_services.php');
$tableInfo = getTableInfo($tableName);

if(!$_REQUEST['ajaxCall']) {
	//1. Display form
	if($action == 'EDIT') {
		// Get table services data
		$tableRecord = new TableSpa($tableName, $code);
		$values      = array();
		$currentDay = getSpaCurrentDay($tableRecord->getFieldValue('ser_date')); // Find current day type
		foreach($tableInfo['fieldNameList'] as $field => $value) {
			$colFieldType = $tableRecord->getFieldType($field);
			if($colFieldType == 'time'){
				$tmpTime = $tableRecord->getFieldValue($field);//get time from database
				$newTmpTime = substr($tmpTime, 0, 5);
				$values[$field] = $newTmpTime;
			}else{
				$values[$field] = $tableRecord->getFieldValue($field);
			}
		}
		$smarty->assign('values', $values);

		// Get table service_packages data
		$serpkgIdList 	= array();
		$pkgIdList 		= array();
		$pkgAmountList  = array();
		$valuesPkg = array();
		$sql = "SELECT 		s.serpkg_id, 
							p.pkg_id, 
							s.serpkg_amount,
							p.pkg_price 
				FROM 		service_packages s, packages p  
				WHERE 		s.pkg_id = p.pkg_id AND 
							ser_id = '$code' 
				ORDER BY 	serpkg_id";
		$result = mysql_query($sql, $dbConn);
		$rows 	= mysql_num_rows($result);
		for($i=0; $i<$rows; $i++) {
			$record = mysql_fetch_assoc($result);
			array_push($valuesPkg, $record);
			array_push($serpkgIdList, $record['serpkg_id']);
			array_push($pkgIdList, $record['pkg_id']);
			$pkgAmountList[$record['pkg_id']] = $record['serpkg_amount'];
		}
		$smarty->assign('valuesPkg', $valuesPkg);

		// Get service_list total rice of packages
		$realPkgSvlTotalPriceList = array();
		$pkgsvlRef = array();
		if(count($pkgIdList) > 0) {
			$pkgIdList = wrapSingleQuote($pkgIdList);
			$sql = "SELECT 		p.pkg_id,
								ps.pkgsvl_id, 
								s.svl_id,
								s.svl_price 
					FROM 		packages p, package_service_lists ps, service_lists s  
					WHERE 		p.pkg_id = ps.pkg_id AND 
								ps.svl_id = s.svl_id AND 
								p.pkg_id IN (".implode(',', $pkgIdList).")";
			$result = mysql_query($sql, $dbConn);
			$rows 	= mysql_num_rows($result);
			for($i=0; $i<$rows; $i++) {
				$record = mysql_fetch_assoc($result);
				$amount = $pkgAmountList[$record['pkg_id']];
				$realPkgSvlTotalPriceList[$record['pkgsvl_id']] = $record['svl_price'] * $amount;
				$pkgsvlRef[$record['pkgsvl_id']] = array(
					'pkg_id' => $record['pkg_id'],
					'svl_id' => $record['svl_id']
				);
			}
		}

		// Get table service_service_lists data
		$sersvlIdList 	= array();
		$svlIdList 		= array();
		$svlForCmr 		= array();
		$valuesSvl 		= array();
		$realSvlTotalPriceList = array();
		$sql = "SELECT 		ss.sersvl_id, 
							ss.sersvl_total_price,
							SUBSTR(ss.sersvl_time,1,5) sersvl_time,
							s.svl_id, 
							ss.sersvl_amount,
							s.svl_price,
							ss.sersvl_time tmpSersvl_time,
							IFNULL(s.svl_hr,0) svl_hr,
							IFNULL(s.svl_min,0) svl_min 
				FROM 		service_service_lists ss, service_lists s  
				WHERE 		ss.svl_id = s.svl_id AND 
							ser_id = '$code' 
				ORDER BY 	sersvl_id";
		$result = mysql_query($sql, $dbConn);
		$rows 	= mysql_num_rows($result);
		for($i=0; $i<$rows; $i++) {
			$record = mysql_fetch_assoc($result);
			array_push($valuesSvl, $record);
			array_push($sersvlIdList, $record['sersvl_id']);
			array_push($svlIdList, $record['svl_id']);
			$realSvlTotalPriceList[$record['svl_id']] = $record['sersvl_total_price'];

			// Find service for commission rate
			$allMin 			= $record['svl_hr'] * 60 + $record['svl_min'];
			$startDate 			= strtotime($nowDate." ".$record['tmpSersvl_time']);
			$endDate 			= $startDate+(60*$allMin);
			$sersvl_time_end 	= date('H:i', $endDate);
			$svlForCmr[$record['svl_id']] = getComRate($currentDay, $record['tmpSersvl_time'], $sersvl_time_end);
		}
		$smarty->assign('valuesSvl', $valuesSvl);

		// sersvl_total_price deduct promotion price
		$svlIdList  	= wrapSingleQuote($svlIdList);
		$valuesSvlPrmDtl = array();
		if(count($svlIdList) > 0) {
			$sql = "SELECT 		sd.svl_id, 
								sp.sersvlprm_discout_total 
					FROM 		service_service_list_promotions sp, 
								service_list_promotion_details sd   
					WHERE 		sp.svlprmdtl_id = sd.svlprmdtl_id AND 
								sp.ser_id = '$code' AND 
								sd.svl_id IN (".implode(',', $svlIdList).")";
			$result = mysql_query($sql, $dbConn);
			$rows 	= mysql_num_rows($result);
			for($i=0; $i<$rows; $i++) {
				$record = mysql_fetch_assoc($result);
				if(isset($realSvlTotalPriceList[$record['svl_id']])) {
					$realSvlTotalPriceList[$record['svl_id']] -= $record['sersvlprm_discout_total'];
				}
			}
		}
		

		// Get table service_list_detail data
		$sersvlIdList = wrapSingleQuote($sersvlIdList);
		if(is_array($sersvlIdList) && count($sersvlIdList) > 0) {
			$valuesSvlDtl = array();
			$sql = "SELECT 		svldtl_id,
								svl_id, 
								emp_id,
								svldtl_com 
					FROM 		service_list_details  
					WHERE 		sersvl_id IN (".implode(',', $sersvlIdList).")";
			$result = mysql_query($sql, $dbConn);
			$rows 	= mysql_num_rows($result);
			for($i=0; $i<$rows; $i++) {
				$record 		= mysql_fetch_assoc($result);
				$com_per 		= $svlForCmr[$record['svl_id']];
				$initPrice 		= $realSvlTotalPriceList[$record['svl_id']] * $com_per / 100;
				$svldtl_com 	= $record['svldtl_com'];
				$record['com_rate'] = $svldtl_com / $initPrice * 100;
				array_push($valuesSvlDtl, $record);
			}
			$smarty->assign('valuesSvlDtl', $valuesSvlDtl);
		}

		// Find commission rate for package service list
		$pkgsvlForCmr 		= array();
		$sql = "SELECT 		st.pkgsvl_id, 
							SUBSTR(st.sersvt_time,1,5) sersvt_time,
							st.sersvt_time tmpSersvt_time,
							IFNULL(ps.pkgsvl_hr,0) pkgsvl_hr,
							IFNULL(ps.pkgsvl_min,0) pkgsvl_min 
				FROM 		service_service_list_times st, 
							package_service_lists ps,
							service_packages sp    
				WHERE 		st.pkgsvl_id = ps.pkgsvl_id AND 
							st.serpkg_id = sp.serpkg_id AND 
							sp.ser_id = '$code'";
		$result = mysql_query($sql, $dbConn);
		$rows 	= mysql_num_rows($result);
		for($i=0; $i<$rows; $i++) {
			$record = mysql_fetch_assoc($result);
			$allMin 			= $record['pkgsvl_hr'] * 60 + $record['pkgsvl_min'];
			$startDate 			= strtotime($nowDate." ".$record['tmpSersvt_time']);
			$endDate 			= $startDate+(60*$allMin);
			$sersvt_time_end 	= date('H:i', $endDate);
			$pkgsvlForCmr[$record['pkgsvl_id']] = getComRate($currentDay, $record['tmpSersvt_time'], $sersvt_time_end);
		}

		// Get table package_detail data
		$pkgsvlData = getPkgSvlDataList();
		$sersvtData = array();
		$serpkgIdList = wrapSingleQuote($serpkgIdList);
		if(is_array($serpkgIdList) && count($serpkgIdList) > 0) {
			$valuesPkgDtl = array();
			$sql = "SELECT 		pd.pkgdtl_id,
								st.pkgsvl_id, 
								pd.emp_id,
								pd.pkgdtl_com,
								st.sersvt_id,
								SUBSTR(st.sersvt_time,1,5) sersvt_time,
								sp.serpkg_amount amount 
					FROM 		package_details pd,
								service_service_list_times st,
								service_packages sp  
					WHERE 		pd.sersvt_id = st.sersvt_id AND 
								st.serpkg_id = sp.serpkg_id AND 
								sp.serpkg_id IN (".implode(',', $serpkgIdList).")";
			$result = mysql_query($sql, $dbConn);
			$rows 	= mysql_num_rows($result);
			for($i=0; $i<$rows; $i++) {
				$record 		= mysql_fetch_assoc($result);
				$pkgsvl_id 		= $record['pkgsvl_id'];
				$pkg_id 		= $pkgsvlRef[$pkgsvl_id]['pkg_id'];
				$svl_id 		= $pkgsvlRef[$pkgsvl_id]['svl_id'];
				$com_per 		= $pkgsvlForCmr[$pkgsvl_id];
				$initPrice 		= ($pkgsvlData[$pkg_id][$svl_id]['realPrice']*$record['amount']) * $com_per / 100;
				$pkgdtl_com 	= $record['pkgdtl_com'];
				$record['com_rate'] = $pkgdtl_com / $initPrice * 100;
				$record['pkg_id'] 	= $pkg_id;
				$record['svl_id'] 	= $svl_id;
				array_push($valuesPkgDtl, $record);

				// Get sersvl times
				if(!isset($sersvtData[$pkg_id][$svl_id])) {
					$sersvtData[$pkg_id][$svl_id] = array(
						'sersvt_id' => $record['sersvt_id'],
						'sersvt_time' => $record['sersvt_time']
					);
				}
			}
			$smarty->assign('valuesPkgDtl', $valuesPkgDtl);
			$smarty->assign('sersvtData', $sersvtData);
		}
		

	} else if($action == 'VIEW_DETAIL') {
		// Get table services data
		$tableRecord = new TableSpa($tableName, $code);
		$values      = array();
		foreach($tableInfo['fieldNameList'] as $field => $value) {
			$colFieldType = $tableRecord->getFieldType($field);
			if($colFieldType == 'time'){
				$tmpTime = $tableRecord->getFieldValue($field);//get time from database
				$newTmpTime = substr($tmpTime, 0, 5);
				$values[$field] = $newTmpTime;
			}else{
				$values[$field] = $tableRecord->getFieldValue($field);
			}

			if(hasValue($values[$field])) {
				if($colFieldType == 'date' || $colFieldType == 'datetime') {
					$values[$field] = dateThaiFormat($values[$field]);
				}
			} else {
				$values[$field] = '-';
			}
		}
		$smarty->assign('values', $values);

		// Get service_packages data
		$serpkgData = array();
		$serpkgIds = array();
		$sql = "SELECT 	sp.serpkg_id,
						sp.pkg_id,
		 				p.pkg_name,
		 				p.pkg_price,
		 				FORMAT(sp.serpkg_amount, 0) serpkg_amount,
		 				sp.serpkg_total_price 
				FROM 	service_packages sp, packages p 
				WHERE 	sp.pkg_id = p.pkg_id 
				AND 	sp.ser_id = '$code'";
		$result = mysql_query($sql, $dbConn);
		$rows 	= mysql_num_rows($result);
		for($i=0; $i<$rows; $i++) {
			$record = mysql_fetch_assoc($result);
			$serpkgData[$record['pkg_id']] = array(
				'no' 					=> $i + 1,
				'pkg_id' 				=> $record['pkg_id'],
				'pkg_name' 				=> $record['pkg_name'],
				'pkg_price' 			=> number_format($record['pkg_price'], 2),
				'serpkg_amount' 		=> $record['serpkg_amount'],
				'serpkg_discout' 		=> number_format(0.00, 2),
				'serpkg_total_price' 	=> $record['serpkg_total_price']
			);
			$subtotal		+= $record['serpkg_total_price'];
			$totalAmount 	+= $record['serpkg_amount'];
			array_push($serpkgIds, $record['serpkg_id']);
		}
		
		// Get service_service_lists data
		$sersvlData = array();
		$sersvlIds = array();
		$allSvl = 0;
		$sql = "SELECT 	ss.sersvl_id,
						ss.svl_id,
		 				s.svl_name,
		 				s.svl_price,
		 				FORMAT(ss.sersvl_amount, 0) sersvl_amount,
		 				ss.sersvl_total_price 
				FROM 	service_service_lists ss, service_lists s 
				WHERE 	ss.svl_id = s.svl_id 
				AND 	ss.ser_id = '$code'";
		$result = mysql_query($sql, $dbConn);
		$rows 	= mysql_num_rows($result);
		for($i=0; $i<$rows; $i++) {
			$record = mysql_fetch_assoc($result);
			$sersvlData[$record['svl_id']] = array(
				'no' 					=> $i + 1,
				'svl_id' 				=> $record['svl_id'],
				'svl_name' 				=> $record['svl_name'],
				'svl_price' 			=> number_format($record['svl_price'], 2),
				'sersvl_amount' 		=> $record['sersvl_amount'],
				'sersvl_discout' 		=> number_format(0.00, 2),
				'sersvl_total_price' 	=> $record['sersvl_total_price']
			);
			$subtotal		+= $record['sersvl_total_price'];
			$totalAmount 	+= $record['sersvl_amount'];
			array_push($sersvlIds, $record['sersvl_id']);
		}

		// Get service package promotions data
		$sql = "SELECT 	p.pkg_id,
		 				pp.serpkgprm_discout_total 
				FROM 	service_package_promotions pp,
						package_promotion_details pd, 
						packages p 
				WHERE 	pp.pkgprmdtl_id = pd.pkgprmdtl_id AND 
						pd.pkg_id = p.pkg_id AND 
						pp.ser_id = '$code'";
		$result = mysql_query($sql, $dbConn);
		$rows 	= mysql_num_rows($result);
		for($i=0; $i<$rows; $i++) {
			$record = mysql_fetch_assoc($result);
			$serpkgData[$record['pkg_id']]['serpkg_discout'] 	 += $record['serpkgprm_discout_total']; 
			$serpkgData[$record['pkg_id']]['serpkg_total_price'] -= $record['serpkgprm_discout_total']; 
		}

		// Get service service_list promotions data
		$sql = "SELECT 	s.svl_id,
		 				sp.sersvlprm_discout_total 
				FROM 	service_service_list_promotions sp,
						service_list_promotion_details sd, 
						service_lists s 
				WHERE 	sp.svlprmdtl_id = sd.svlprmdtl_id AND 
						sd.svl_id = s.svl_id AND 
						sp.ser_id = '$code'";
		$result = mysql_query($sql, $dbConn);
		$rows 	= mysql_num_rows($result);
		for($i=0; $i<$rows; $i++) {
			$record = mysql_fetch_assoc($result);
			$sersvlData[$record['svl_id']]['sersvl_discout'] 	 += $record['sersvlprm_discout_total']; 
			$sersvlData[$record['svl_id']]['sersvl_total_price'] -= $record['sersvlprm_discout_total']; 
		}

		// Get service list detail data
		if(count($sersvlIds) > 0) {
			$sersvlIds = wrapSingleQuote($sersvlIds);
			$sql = "SELECT 		sd.svl_id,
								CONCAT(e.emp_name, ' ', e.emp_surname) emp_fullname 
					FROM 		service_list_details sd,
								employees e 
					WHERE 		sd.emp_id = e.emp_id AND 
								sd.sersvl_id IN (".implode(',', $sersvlIds).")";
			$result = mysql_query($sql, $dbConn);
			$rows 	= mysql_num_rows($result);
			for($i=0; $i<$rows; $i++) {
				$record = mysql_fetch_assoc($result);
				if(!isset($sersvlData[$record['svl_id']]['employees'])) {
					$sersvlData[$record['svl_id']]['employees'] = array();
				}
				array_push($sersvlData[$record['svl_id']]['employees'], $record['emp_fullname']);
			}
		}
		
		// Get package detail data
		if(count($serpkgIds) > 0) {
			$serpkgIds = wrapSingleQuote($serpkgIds);
			$sql = "SELECT 		ps.pkg_id,
								CONCAT(e.emp_name, ' ', e.emp_surname) emp_fullname 
					FROM 		service_service_list_times st,
								package_details pd,
								package_service_lists ps,
								employees e 
					WHERE 		st.sersvt_id = pd.sersvt_id AND 
								st.pkgsvl_id = ps.pkgsvl_id AND 
								pd.emp_id = e.emp_id AND 
								st.serpkg_id IN (".implode(',', $serpkgIds).")";
			$result = mysql_query($sql, $dbConn);
			$rows 	= mysql_num_rows($result);
			for($i=0; $i<$rows; $i++) {
				$record = mysql_fetch_assoc($result);
				if(!isset($serpkgData[$record['pkg_id']]['employees'])) {
					$serpkgData[$record['pkg_id']]['employees'] = array();
				}
				array_push($serpkgData[$record['pkg_id']]['employees'], $record['emp_fullname']);
			}
		}

		$smarty->assign('viewSerpkgData', $serpkgData);
		$smarty->assign('viewSersvlData', $sersvlData);
	}

	// Get reference data for selectReferenceJS
	if(is_array($tableInfo['referenceData']) && count($tableInfo['referenceData']) > 0) {
		$sqlRefData 	= '';
		$referenceData 	= array();

		foreach ($tableInfo['referenceData'] as $key => $table) {
			switch ($table) {
				case 'employees':
					$sqlRefData = "	SELECT 		emp_id refValue,
												CONCAT(emp_name, ' ', emp_surname) refText 
									FROM 		employees 
									ORDER BY 	refText ASC";
					$refField 	= 'emp_id';
					break;

				case 'employeesMasseuse':
					$sqlRefData = "	SELECT 		emp_id refValue,
												CONCAT(emp_name, ' ', emp_surname) refText 
									FROM 		employees 
									WHERE 		pos_id = 'P03' 
									ORDER BY 	refText ASC";
					$refField 	= 'emp_id';
					break;

				case 'customers':
					$sqlRefData = "	SELECT 		cus_id refValue,
												CONCAT(cus_name, ' ', cus_surname) refText,
												custype_id 
									FROM 		customers 
									ORDER BY 	refText ASC";
					$refField 	= 'cus_id';
					break;

				case 'customer_types':
					$sqlRefData = "	SELECT 		custype_id refValue,
												custype_name refText 
									FROM 		customer_types 
									ORDER BY 	refText ASC";
					$refField 	= 'custype_id';
					break;

				case 'service_lists':
					$sqlRefData = "	SELECT 		svl_id refValue,
												svl_name refText,
												svl_price,
												IFNULL(svl_hr,0) * 60 + IFNULL(svl_min,0) svl_min 
									FROM 		service_lists 
									WHERE 		svl_start <= '$nowDate' AND 
												(
													svl_stop IS NULL OR 
													svl_stop >= '$nowDate'
												) AND 
												svl_type = 1 
									ORDER BY 	svl_name ASC";
					$refField 	= 'svl_id';
					break;

				case 'packages':
					$sqlRefData = "	SELECT 		pkg_id refValue,
												pkg_name refText,
												pkg_start,
												pkg_stop,
												pkg_price 
									FROM 		packages p 
									WHERE 		pkg_start <= '$nowDate' AND 
												(
													pkg_stop IS NULL OR 
													pkg_stop >= '$nowDate'
												) 
									ORDER BY 	refText ASC";
					$refField 	= 'pkg_id';
					break;

				case 'beds':
					$sqlRefData = "	SELECT 		bed_id refValue,
												bed_name refText 
									FROM 		beds 
									ORDER BY 	refText ASC";
					$refField 	= 'bed_id';
					break;

				case 'booking':
					$sqlRefData = "	SELECT 		b.bkg_id refValue,
												b.bkg_id refText,
												countPkg, countSvl 
									FROM 		booking b 
									LEFT JOIN 	(
													SELECT 	bkg_id, COUNT( bkgpkg_id ) AS countPkg 
													FROM 	booking_packages 
													WHERE 	bkgpkg_date =  '$nowDate' 
													GROUP BY bkg_id 
												) bp 
												ON b.bkg_id = bp.bkg_id 
									LEFT JOIN 	(
													SELECT bkg_id, COUNT( bkgsvl_id ) AS countSvl 
													FROM booking_service_lists 
													WHERE bkgsvl_date =  '$nowDate' 
													GROUP BY bkg_id 
												) bs 
												ON b.bkg_id = bs.bkg_id 
									WHERE 		b.status_id IN ('S03',  'S04') AND 
												(
													countPkg IS NOT NULL OR 
													countSvl IS NOT NULL
												)
									ORDER BY 	refText DESC";
					$refField 	= 'bkg_id';
					break;

				case 'pay_types':
					$sqlRefData = "	SELECT 		paytyp_id refValue,
												paytyp_name refText 
									FROM 		pay_types 
									ORDER BY 	refText ASC";
					$refField 	= 'paytyp_id';
					break;
			}

			if(hasValue($sqlRefData)) {
				$resultRefData 	= mysql_query($sqlRefData);
				$rowsRefData 	= mysql_num_rows($resultRefData);

				if($rowsRefData > 0) {
					$referenceData[$table] = array();
					// push to referenc data
					for($i=0; $i<$rowsRefData; $i++) {
						$tmpRow 	= mysql_fetch_assoc($resultRefData);
						$refDataRow = array();

						foreach ($tmpRow as $key => $value) {
							$refDataRow[$key] = $value;
						}
						$refDataRow['refField'] = $refField;

						array_push($referenceData[$table], $refDataRow);
					}
					
				}
			}
		}
		$smarty->assign('referenceData', $referenceData);
	}

	// Get package promotion detail data
	$pkgPromotions = array();
	$sql = "SELECT 		pkgprm.custype_id,
						pkgprm.pkgprm_name,
						pkgprm.pkgprm_pic,
						pkgprmdtl.pkgprmdtl_id,
						pkgprmdtl.pkg_id,
						pkgprmdtl.pkgprmdtl_discout,
						pkgprmdtl.pkgprmdtl_discout_type 
			FROM 		package_promotion_details pkgprmdtl,
						package_promotions pkgprm 
			WHERE 		pkgprmdtl.pkgprm_id = pkgprm.pkgprm_id AND 
						pkgprmdtl.pkgprmdtl_startdate <= '$nowDate' AND 
						(
							pkgprmdtl.pkgprmdtl_enddate IS NULL OR
							pkgprmdtl.pkgprmdtl_enddate >= '$nowDate'
						)";
	$result = mysql_query($sql, $dbConn);
	$rows 	= mysql_num_rows($result);
	if($rows > 0) {
		for($i=0; $i<$rows; $i++) {
			$record			= mysql_fetch_assoc($result);
			$custype_id 	= $record['custype_id'];
			$pkg_id 		= $record['pkg_id'];

			// Sale promotion
			$pkgPromotions[$custype_id][$pkg_id] = array(
				'pkgprmdtl_id' 			 => $record['pkgprmdtl_id'],
				'pkgprm_name' 			 => $record['pkgprm_name'],
				'pkgprm_pic' 			 => $record['pkgprm_pic'],
				'pkgprmdtl_discout' 	 => $record['pkgprmdtl_discout'],
				'pkgprmdtl_discout_type' => $record['pkgprmdtl_discout_type']
			);
		}
		$smarty->assign('pkgPromotions', $pkgPromotions);
	}

	// Get service_list promotion detail data
	$svlPromotions = array();
	$sql = "SELECT 		svlprm.custype_id,
						svlprm.svlprm_name,
						svlprm.svlprm_pic,
						svlprmdtl.svlprmdtl_id,
						svlprmdtl.svl_id,
						svlprmdtl.svlprmdtl_discout,
						svlprmdtl.svlprmdtl_discout_type 
			FROM 		service_list_promotion_details svlprmdtl,
						service_list_promotions svlprm 
			WHERE 		svlprmdtl.svlprm_id = svlprm.svlprm_id AND 
						svlprmdtl.svlprmdtl_startdate <= '$nowDate' AND 
						(
							svlprmdtl.svlprmdtl_enddate IS NULL OR
							svlprmdtl.svlprmdtl_enddate >= '$nowDate'
						)";
	$result = mysql_query($sql, $dbConn);
	$rows 	= mysql_num_rows($result);
	if($rows > 0) {
		for($i=0; $i<$rows; $i++) {
			$record			= mysql_fetch_assoc($result);
			$custype_id 	= $record['custype_id'];
			$svl_id 		= $record['svl_id'];

			// Sale promotion
			$svlPromotions[$custype_id][$svl_id] = array(
				'svlprmdtl_id' 			 => $record['svlprmdtl_id'],
				'svlprm_name' 			 => $record['svlprm_name'],
				'svlprm_pic' 			 => $record['svlprm_pic'],
				'svlprmdtl_discout' 	 => $record['svlprmdtl_discout'],
				'svlprmdtl_discout_type' => $record['svlprmdtl_discout_type']
			);
		}
		$smarty->assign('svlPromotions', $svlPromotions);
	}

	// Get package_service_lists data
	$pkgsvlData = array();
	$sql = "SELECT 		pkg.pkg_id,
						s.svl_id,
						s.svl_name,
						IFNULL(pkgsvl.pkgsvl_hr,0) * 60 + IFNULL(pkgsvl.pkgsvl_min,0) allMin, 
						pkgsvl.pkgsvl_price 
			FROM 		packages pkg,
						package_service_lists pkgsvl,
						service_lists s  
			WHERE 		pkg.pkg_id = pkgsvl.pkg_id AND 
						pkgsvl.svl_id = s.svl_id";
	$result = mysql_query($sql, $dbConn);
	$rows 	= mysql_num_rows($result);
	if($rows > 0) {
		for($i=0; $i<$rows; $i++) {
			$record	= mysql_fetch_assoc($result);

			if(!isset($pkgsvlData[$record['pkg_id']])) {
				$pkgsvlData[$record['pkg_id']] = array();
			}

			$pkgsvlValues = array(
				'svl_id' 	=> $record['svl_id'],
				'svl_name' 	=> $record['svl_name'],
				'allMin' 	=> $record['allMin'],
				'pkgsvl_price' => $record['pkgsvl_price']
			);
			array_push($pkgsvlData[$record['pkg_id']], $pkgsvlValues);
		}
		$smarty->assign('pkgsvlData', $pkgsvlData);
	}

	// Check for hide edit, back button
	if($hideEditButton == 'true') {
		$smarty->assign('hideEditButton', true);
	}
	if($hideBackButton == 'true') {
		$smarty->assign('hideBackButton', true);
	}

	$smarty->assign('action', $action);
	$smarty->assign('tableName', $tableName);
	$smarty->assign('tableNameTH', $tableInfo['tableNameTH']);
	$smarty->assign('code', $code);
	include('../common/common_footer.php');
} else {
	//2. Process record
	$formData		= array();
	$values			= array();
	$fieldListEn	= array();
	parse_str($_REQUEST['formData'], $formData);
	
	// Check input required
	if(hasValue($formData['requiredFields'])) {
		$requiredFields = explode(',', $formData['requiredFields']);
		foreach($requiredFields as $key => $fieldName) {
			if(!hasValue($formData[$fieldName])) {
				$response['status'] = 'REQURIED_VALUE';
				$response['text']	= $fieldName;
				echo json_encode($response);
				exit();
			}
		}
	}

	// Check unique filed
	if(hasValue($formData['uniqueFields'])) {
		$uniqueFields = explode(',', $formData['uniqueFields']);
		foreach($uniqueFields as $key => $fieldName) {
			// Skip if value is empty
			if($formData[$fieldName] == '') {
				continue;
			}

			$value = $formData[$fieldName];
			$value = str_replace("\\\'", "'", $value);
			$value = str_replace('\\\"', '"', $value);
			$value = str_replace('\\\\"', '\\', $value);
			$value = "'$value'";

			$sql	= "SELECT $fieldName FROM $tableName WHERE $fieldName = $value AND ".$tableInfo['keyFieldName']." != '$code' LIMIT 1";
			$result	= mysql_query($sql, $dbConn);
			if(mysql_num_rows($result) > 0) {
				$response['status'] = 'UNIQUE_VALUE';
				$response['text']	= $fieldName;
				echo json_encode($response);
				exit();
			}
		}
	}
	

	// Prepare variable
	foreach($tableInfo['fieldNameList'] as $field => $value) {
		array_push($fieldListEn, $field);
	}

	if($action == 'ADD') {
		//2.1 Insert new record
		$values['fieldName']  = array();
		$values['fieldValue'] = array();
		$insertResult 		  = true;
		$errTxt 			  = '';
		$currentDay = getSpaCurrentDay($nowDate); // Find current day type

		// Push values to array
		foreach($formData as $fieldName => $value) {
			if(in_array($fieldName, $fieldListEn)) {
				// Skip if value is empty and default this field is null
				if($value == '' && is_array($tableInfo['defaultNull']) && in_array($fieldName, $tableInfo['defaultNull'])) {
					continue;
				}
				
				$value = str_replace("\\\'", "'", $value);
				$value = str_replace('\\\"', '"', $value);
				$value = str_replace('\\\\"', '\\', $value);
				array_push($values['fieldName'], $fieldName);
				array_push($values['fieldValue'], $value);
			}
		}

		// Get package_service_list data
		$pkgsvlDataList = getPkgSvlDataList();

		// Insert services
		$tableRecord = new TableSpa($tableName, $values['fieldName'], $values['fieldValue']);
		if(!$tableRecord->insertSuccess()) {
			$insertResult = false;
			$errTxt .= 'INSERT_SERVICES_FAIL\n';
			$errTxt .= mysql_error($dbConn).'\n\n';
		}
		$ser_id = $tableRecord->getKey();

		// Find commission percent of packages
		$cmrPkg = array();
		if(isset($formData['pkg_id']) && is_array($formData['pkg_id'])) {
			foreach ($formData['pkg_id'] as $key => $pkg_id) {
				if(hasValue($formData['pkgCom_'.$pkg_id.'_svl_id']) && is_array($formData['pkgCom_'.$pkg_id.'_svl_id'])) {
					foreach ($formData['pkgCom_'.$pkg_id.'_svl_id'] as $key => $svl_id) {
						$sersvt_time  = $formData['pkgCom_'.$pkg_id.'_sersvt_time'][$key];
						$sersvt_time_end = $formData['sersvt_time_end'][$key];
						$cmrPkg[$svl_id] = getComRate($currentDay, $sersvt_time, $sersvt_time_end);
					}
				}
			}
		}
		
		// Insert service packages
		if(isset($formData['pkg_id']) && is_array($formData['pkg_id'])) {
			foreach ($formData['pkg_id'] as $key => $pkg_id) {
				$serpkg_amount 	= $formData['pkg_qty'][$key];
				$serpkg_total_price = $formData['serpkg_total_price'][$key];
				$serpkgValues 		= array($ser_id, $pkg_id, $serpkg_amount, $serpkg_total_price);
				$serpkgRecord 		= new TableSpa('service_packages', $serpkgValues);
				if(!$serpkgRecord->insertSuccess()) {
					$insertResult = false;
					$errTxt .= 'INSERT_SERVICE_PACKAGES['.($key+1).']_FAIL\n';
					$errTxt .= mysql_error($dbConn).'\n\n';
				}
				$serpkg_id = $serpkgRecord->getKey();

				// Insert service packages promotion (Sale)
				if(hasValue($formData['prmSale_'.$pkg_id.'_pkgprmdtl_id'])) {
					$pkgprmdtl_id 				= $formData['prmSale_'.$pkg_id.'_pkgprmdtl_id'];
					$serpkgprm_amount 			= $formData['prmSale_'.$pkg_id.'_serpkgprm_amount'];
					$serpkgprm_discout_total 	= $formData['prmSale_'.$pkg_id.'_serpkgprm_discout_total'];
					$serpkgprmValues 			= array($ser_id, $pkgprmdtl_id, $serpkgprm_amount, $serpkgprm_discout_total);
					$serpkgprmRecord 			= new TableSpa('service_package_promotions', $serpkgprmValues);
					if(!$serpkgprmRecord->insertSuccess()) {
						$insertResult = false;
						$errTxt .= 'INSERT_SERVICE_PACKAGE_PROMOTIONS['.($key+1).']_FAIL\n';
						$errTxt .= mysql_error($dbConn).'\n\n';
					}
				}

				// Insert package_detail (Commission)
				if(hasValue($formData['pkgCom_'.$pkg_id.'_svl_id']) && is_array($formData['pkgCom_'.$pkg_id.'_svl_id'])) {
					foreach ($formData['pkgCom_'.$pkg_id.'_svl_id'] as $key => $svl_id) {
						// Insert service_service_list_times
						$pkgsvl_id 	  = $pkgsvlDataList[$pkg_id][$svl_id]['pkgsvl_id'];
						$sersvt_time  = $formData['pkgCom_'.$pkg_id.'_sersvt_time'][$key];
						$sersvtValues = array($serpkg_id, $pkgsvl_id, $sersvt_time);
						$sersvtRecord 		= new TableSpa('service_service_list_times', $sersvtValues);
						if(!$sersvtRecord->insertSuccess()) {
							$insertResult = false;
							$errTxt .= 'INSERT_SERVICE_SERVICE_LIST_TIMES['.($key+1).']_FAIL\n';
							$errTxt .= mysql_error($dbConn).'\n\n';
						}
						$sersvt_id = $sersvtRecord->getKey();

						if(hasValue($formData['pkgCom_'.$pkg_id.'_'.$svl_id.'_emp_id']) && is_array($formData['pkgCom_'.$pkg_id.'_'.$svl_id.'_emp_id'])) {
							foreach ($formData['pkgCom_'.$pkg_id.'_'.$svl_id.'_emp_id'] as $empKey => $comEmp_id) {
								$com_per 			= $cmrPkg[$svl_id]; // Percent
								$initCom 			= ($pkgsvlDataList[$pkg_id][$svl_id]['realPrice'] * $serpkg_amount) * $com_per / 100;
								$com_rate 			= $formData['pkgCom_'.$pkg_id.'_'.$svl_id.'_com_rate'][$empKey];
								$pkgdtl_com 		= $initCom * $com_rate / 100;
								$pkgdtlValues 		= array($sersvt_id, $comEmp_id, $pkgdtl_com);
								$pkgdtlprmRecord 	= new TableSpa('package_details', $pkgdtlValues);
								if(!$pkgdtlprmRecord->insertSuccess()) {
									$insertResult = false;
									$errTxt .= 'INSERT_PACKAGE_DETAILS['.($empKey+1).']_FAIL\n';
									$errTxt .= mysql_error($dbConn).'\n\n';
								}
							}
						}
					}
				}
			}
		}
		// End service packages

		// Find commission percent of service lists
		$cmrSvl = array();
		if(isset($formData['svl_id']) && is_array($formData['svl_id'])) {
			foreach ($formData['svl_id'] as $key => $svl_id) {
				$sersvl_time 		= $formData['sersvl_time'][$key];
				$sersvl_time_end	= $formData['sersvl_time_end'][$key];
				$cmrSvl[$svl_id] = getComRate($currentDay, $sersvl_time, $sersvl_time_end);
			}
		}

		// Insert service service_list
		if(isset($formData['svl_id']) && is_array($formData['svl_id'])) {
			foreach ($formData['svl_id'] as $key => $svl_id) {
				$sersvl_amount 		= $formData['svl_qty'][$key];
				$sersvl_total_price = $formData['sersvl_total_price'][$key];
				$sersvl_time 		= $formData['sersvl_time'][$key];
				$sersvlValues 		= array($ser_id, $svl_id, $sersvl_amount, $sersvl_total_price, $sersvl_time);
				$sersvlRecord 		= new TableSpa('service_service_lists', $sersvlValues);
				if(!$sersvlRecord->insertSuccess()) {
					$insertResult = false;
					$errTxt .= 'INSERT_SERVICE_SERVICE_LISTS['.($key+1).']_FAIL\n';
					$errTxt .= mysql_error($dbConn).'\n\n';
				}
				$sersvl_id = $sersvlRecord->getKey();


				// Insert service service_list promotion (Sale)
				if(hasValue($formData['prmSale_'.$svl_id.'_svlprmdtl_id'])) {
					$svlprmdtl_id 				= $formData['prmSale_'.$svl_id.'_svlprmdtl_id'];
					$sersvlprm_amount 			= $formData['prmSale_'.$svl_id.'_sersvlprm_amount'];
					$sersvlprm_discout_total 	= $formData['prmSale_'.$svl_id.'_sersvlprm_discout_total'];
					$sersvlprmValues 			= array($ser_id, $svlprmdtl_id, $sersvlprm_amount, $sersvlprm_discout_total);
					$sersvlprmRecord 			= new TableSpa('service_service_list_promotions', $sersvlprmValues);
					if(!$sersvlprmRecord->insertSuccess()) {
						$insertResult = false;
						$errTxt .= 'INSERT_SERVICE_SERVICE_LIST_PROMOTIONS['.($key+1).']_FAIL\n';
						$errTxt .= mysql_error($dbConn).'\n\n';
					}
					$sersvl_total_price -= $sersvlprm_discout_total;
				}

				// Insert service_list_detail (Commission)
				$real_sersvl_total_price = $sersvl_total_price;
				if(hasValue($formData['svlCom_'.$svl_id.'_emp_id']) && is_array($formData['svlCom_'.$svl_id.'_emp_id'])) {
					foreach ($formData['svlCom_'.$svl_id.'_emp_id'] as $key => $comEmp_id) {
						$com_per = $cmrSvl[$svl_id]; // Percent
						if($com_per > 0) {
							$initCom 			= $real_sersvl_total_price * $com_per / 100;
							$com_rate 			= $formData['svlCom_'.$svl_id.'_com_rate'][$key];
							$svldtl_com 		= $initCom * $com_rate / 100;
							$svldtlValues 		= array($svl_id, $comEmp_id, $sersvl_id, $svldtl_com);
							$svldtlprmRecord 	= new TableSpa('service_list_details', $svldtlValues);
							if(!$svldtlprmRecord->insertSuccess()) {
								$insertResult = false;
								$errTxt .= 'INSERT_SERVICE_LIST_DETAILS['.($key+1).']_FAIL\n';
								$errTxt .= mysql_error($dbConn).'\n\n';
							}
						}
					}
				}
			}
		}
		// End service packages


		// Update booking status
		if($formData['bkg_id'] != '') {
			if(!updateBookingStatus($formData['bkg_id'])) {
				$insertResult = false;
				$errTxt .= 'UPDATE_BOOKING_STATUS['.$formData['bkg_id'].']_FAIL\n';
				$errTxt .= mysql_error($dbConn).'\n\n';
			}
		}
		
		if($insertResult) {
			$response['status'] = 'ADD_PASS';
			echo json_encode($response);
		} else {
			$response['status'] = $errTxt;
			echo json_encode($response);
		}
		

	} else if($action == 'EDIT') {
		//2.2 Update record
		$tableRecord = new TableSpa($tableName, $code);
		$updateResult  	= true;
		$errTxt 		= '';
		$currentDay = getSpaCurrentDay($formData['ser_date']); // Find current day type

		// Set all field value
		foreach($formData as $fieldName => $value) {
			if(in_array($fieldName, $fieldListEn)) {
				// value is empty will set default is null
				if($value == '' && is_array($tableInfo['defaultNull']) && in_array($fieldName, $tableInfo['defaultNull'])) {
					$value = 'NULL';
				}
				
				$tableRecord->setFieldValue($fieldName, $value);
			}
		}

		// Get pkgsvl_id and real_pkgsvl_total_price list
		$pkgsvlDataList = getPkgSvlDataList();

		// Update Services
		if(!$tableRecord->commit()) {
			$updateResult = false;
			$errTxt .= 'EDIT_SERVICES_FAIL\n';
			$errTxt .= mysql_error($dbConn).'\n\n';
		}

		### Update service_packages & service_package_promotions & package_details
		// Delete service_packages if delete old service_packages
		$oldServicePkgList = array();
		$newServicePkgList = array();
		$oldPkgPrmDtlList = array();
		$newPkgPrmDtlList = array();
		$oldSerPkgPrmList = array();
		$oldPkgDtlList 		= array();
		$newPkgDtlList 		= array();
		$oldSerSvtList = array();
		$newSerSvtList = array();
		// Find old service_packages
		$sql = "SELECT serpkg_id FROM service_packages WHERE ser_id = '$code'";
		$result = mysql_query($sql, $dbConn);
		$rows 	= mysql_num_rows($result);
		for($i=0; $i<$rows; $i++) {
			$oldServicePkgRecord = mysql_fetch_assoc($result);
			array_push($oldServicePkgList, $oldServicePkgRecord['serpkg_id']);
		}
		// Find old service_service_list_times
		$tmpSerPkgIds = wrapSingleQuote($oldServicePkgList);
		$sql = "SELECT sersvt_id FROM service_service_list_times WHERE serpkg_id IN (".implode(',', $tmpSerPkgIds).")";
		$result = mysql_query($sql, $dbConn);
		$rows 	= mysql_num_rows($result);
		for($i=0; $i<$rows; $i++) {
			$record = mysql_fetch_assoc($result);
			array_push($oldSerSvtList, $record['sersvt_id']);
		}
		// Find old service_package_promotions
		$sql = "SELECT serpkgprm_id, pkgprmdtl_id FROM service_package_promotions WHERE ser_id = '$code'";
		$result = mysql_query($sql, $dbConn);
		$rows 	= mysql_num_rows($result);
		for($i=0; $i<$rows; $i++) {
			$oldServicePkgPrmRecord = mysql_fetch_assoc($result);
			array_push($oldPkgPrmDtlList, $oldServicePkgPrmRecord['pkgprmdtl_id']);
			$oldSerPkgPrmList[$oldServicePkgPrmRecord['pkgprmdtl_id']] = $oldServicePkgPrmRecord['serpkgprm_id'];
		}
		// Find new service_packages
		if(isset($formData['serpkg_id']) && is_array($formData['serpkg_id'])) {
			foreach ($formData['serpkg_id'] as $key => $newserpkg_id) {
				array_push($newServicePkgList, $newserpkg_id);
			}
		}
		if(isset($formData['pkg_id']) && is_array($formData['pkg_id'])) {
			foreach ($formData['pkg_id'] as $key => $pkg_id) {
				// Find new service_service_list_times
				foreach ($formData['pkgCom_'.$pkg_id.'_svl_id'] as $key => $svl_id) {
					if(isset($formData['sersvt_id_'.$pkg_id][$key])) {
						array_push($newSerSvtList, $formData['sersvt_id_'.$pkg_id][$key]);
					}
				}

				// Find new service_package_promotions
				if(hasValue($formData['prmSale_'.$pkg_id.'_pkgprmdtl_id'])) {
					array_push($newPkgPrmDtlList, $formData['prmSale_'.$pkg_id.'_pkgprmdtl_id']);
				}
			}
		}
		// Find old package_details
		$sql = "SELECT 		pd.pkgdtl_id 
				FROM 		package_details pd,
							service_service_list_times st,
							service_packages sp 
				WHERE 		st.serpkg_id = sp.serpkg_id AND 
							st.sersvt_id = pd.sersvt_id AND 
							sp.ser_id = '$code'";
		$result = mysql_query($sql, $dbConn);
		$rows 	= mysql_num_rows($result);
		for($i=0; $i<$rows; $i++) {
			$record = mysql_fetch_assoc($result);
			array_push($oldPkgDtlList, $record['pkgdtl_id']);
		}
		// Find new package_details
		if(isset($formData['pkgdtl_id']) && is_array($formData['pkgdtl_id'])) {
			foreach ($formData['pkgdtl_id'] as $key => $newpkgdtl_id) {
				array_push($newPkgDtlList, $newpkgdtl_id);
			}
		}
		
		// Check for delete package_details
		foreach ($oldPkgDtlList as $key => $oldpkgdtl_id) {
			if(!in_array($oldpkgdtl_id, $newPkgDtlList)) {
				// Delete service_service_lists
				$pkgDtlRecord 	= new TableSpa('package_details', $oldpkgdtl_id);
				if(!$pkgDtlRecord->delete()) {
					$updateResult = false;
					$errTxt .= "DELETE_PACKAGE_DETAILS[$oldpkgdtl_id]_FAIL\n";
					$errTxt .= mysql_error($dbConn).'\n\n';
				}
			}
		}

		// Check for delete service_service_list_times
		foreach ($oldSerSvtList as $key => $oldSerSvt_id) {
			if(!in_array($oldSerSvt_id, $newSerSvtList)) {
				// Delete service_service_list_times
				$serSvtRecord 	= new TableSpa('service_service_list_times', $oldSerSvt_id);
				if(!$serSvtRecord->delete()) {
					$updateResult = false;
					$errTxt .= "DELETE_SERVICE_SERVICE_LIST_TIMES[$oldSerSvt_id]_FAIL\n";
					$errTxt .= mysql_error($dbConn).'\n\n';
				}
			}
		}

		// Check for delete service_packages
		foreach ($oldServicePkgList as $key => $oldserpkg_id) {
			if(!in_array($oldserpkg_id, $newServicePkgList)) {
				// Delete service_packages
				$servicePkgRecord 	= new TableSpa('service_packages', $oldserpkg_id);
				if(!$servicePkgRecord->delete()) {
					$updateResult = false;
					$errTxt .= "DELETE_SERVICE_PACKAGES[$oldserpkg_id]_FAIL\n";
					$errTxt .= mysql_error($dbConn).'\n\n';
				}
			}
		}

		// Check for delete service_package_promotions
		foreach ($oldPkgPrmDtlList as $key => $oldpkgprmdtl_id) {
			if(!in_array($oldpkgprmdtl_id, $newPkgPrmDtlList)) {
				// Delete service_package_promotions
				$serpkgprm_id = $oldSerPkgPrmList[$oldpkgprmdtl_id];
				$servicePkgPrmRecord 	= new TableSpa('service_package_promotions', $serpkgprm_id);
				if(!$servicePkgPrmRecord->delete()) {
					$updateResult = false;
					$errTxt .= "DELETE_SERVICE_PACKAGE_PROMOTIONS[$serpkgprm_id]_FAIL\n";
					$errTxt .= mysql_error($dbConn).'\n\n';
				}
			}
		}

		// Find commission percent of packages
		$cmrPkg = array();
		if(isset($formData['pkg_id']) && is_array($formData['pkg_id'])) {
			foreach ($formData['pkg_id'] as $key => $pkg_id) {
				if(hasValue($formData['pkgCom_'.$pkg_id.'_svl_id']) && is_array($formData['pkgCom_'.$pkg_id.'_svl_id'])) {
					foreach ($formData['pkgCom_'.$pkg_id.'_svl_id'] as $key => $svl_id) {
						$sersvt_time  = $formData['pkgCom_'.$pkg_id.'_sersvt_time'][$key];
						$sersvt_time_end = $formData['sersvt_time_end'][$key];
						$cmrPkg[$svl_id] = getComRate($currentDay, $sersvt_time, $sersvt_time_end);
					}
				}
			}
		}

		// Update or Add service_packages
		if(isset($formData['pkg_id']) && is_array($formData['pkg_id'])) {
			foreach ($formData['pkg_id'] as $key => $pkg_id) {
				$serpkg_amount  = $formData['pkg_qty'][$key];
				$serpkg_total_price = $formData['serpkg_total_price'][$key];

				if(isset($formData['serpkg_id'][$key])) {
					// Update service_packages
					$serpkg_id = $formData['serpkg_id'][$key];
					$servicePkgRecord 	= new TableSpa('service_packages', $serpkg_id);
					$servicePkgRecord->setFieldValue('pkg_id', $pkg_id);
					$servicePkgRecord->setFieldValue('serpkg_amount', $serpkg_amount);
					$servicePkgRecord->setFieldValue('serpkg_total_price', $serpkg_total_price);
					if(!$servicePkgRecord->commit()) {
						$updateResult = false;
						$errTxt .= 'EDIT_SERVICE_PACKAGES['.($key+1).']_FAIL\n';
						$errTxt .= mysql_error($dbConn).'\n\n';
					}
				} else {
					// Add new service_packages
					$saledtlValues 		= array($code, $pkg_id, $serpkg_amount, $serpkg_total_price);
					$servicePkgRecord 	= new TableSpa('service_packages', $saledtlValues);
					if(!$servicePkgRecord->insertSuccess()) {
						$updateResult = false;
						$errTxt .= 'ADD_SERVICE_PACKAGES['.($key+1).']_FAIL\n';
						$errTxt .= mysql_error($dbConn).'\n\n';
					}
					$serpkg_id = $servicePkgRecord->getKey();
				}

				if(hasValue($formData['prmSale_'.$pkg_id.'_pkgprmdtl_id'])) {
					$pkgprmdtl_id 				= $formData['prmSale_'.$pkg_id.'_pkgprmdtl_id'];
					$serpkgprm_amount 			= $formData['prmSale_'.$pkg_id.'_serpkgprm_amount'];
					$serpkgprm_discout_total 	= $formData['prmSale_'.$pkg_id.'_serpkgprm_discout_total'];

					if(!in_array($pkgprmdtl_id, $oldPkgPrmDtlList)) {
						// Add service_package_promotions
						$serpkgprmValues = array($code, $pkgprmdtl_id, $serpkgprm_amount, $serpkgprm_discout_total);
						$serpkgprmRecord = new TableSpa('service_package_promotions', $serpkgprmValues);
						if(!$serpkgprmRecord->insertSuccess()) {
							$updateResult = false;
							$errTxt .= 'INSERT_SERVICE_PACKAGE_PROMOTIONS['.($key+1).']_FAIL\n';
							$errTxt .= mysql_error($dbConn).'\n\n';
						}
					} else {
						// Update service_package_promotions
						$serpkgprm_id = $oldSerPkgPrmList[$pkgprmdtl_id];
						$servicePkgPrmRecord = new TableSpa('service_package_promotions', $serpkgprm_id);
						$servicePkgPrmRecord->setFieldValue('serpkgprm_amount', $serpkgprm_amount);
						$servicePkgPrmRecord->setFieldValue('serpkgprm_discout_total', $serpkgprm_discout_total);
						if(!$servicePkgPrmRecord->commit()) {
							$updateResult = false;
							$errTxt .= 'EDIT_SERVICE_PACKAGE_PROMOTIONS['.($key+1).']_FAIL\n';
							$errTxt .= mysql_error($dbConn).'\n\n';
						}
					}
				}

				// Update or Add package_details
				if(isset($formData['pkgCom_'.$pkg_id.'_svl_id']) && is_array($formData['pkgCom_'.$pkg_id.'_svl_id'])) {
					foreach ($formData['pkgCom_'.$pkg_id.'_svl_id'] as $key => $svl_id) {
						if(isset($formData['sersvt_id_'.$pkg_id][$key])) {
							// Update service_service_list_times
							$sersvt_id = $formData['sersvt_id_'.$pkg_id][$key];
							$sersvt_time  = $formData['pkgCom_'.$pkg_id.'_sersvt_time'][$key];
							$sersvtRecord = new TableSpa('service_service_list_times', $sersvt_id);
							$sersvtRecord->setFieldValue('sersvt_time', $sersvt_time);
							if(!$sersvtRecord->commit()) {
								$updateResult = false;
								$errTxt .= 'EDIT_SERVICE_SERVICE_LIST_TIMES['.($key+1).']_FAIL\n';
								$errTxt .= mysql_error($dbConn).'\n\n';
							}
						} else {
							// Insert service_service_list_times
							$pkgsvl_id 	  = $pkgsvlDataList[$pkg_id][$svl_id]['pkgsvl_id'];
							$sersvt_time  = $formData['pkgCom_'.$pkg_id.'_sersvt_time'][$key];
							$sersvtValues = array($serpkg_id, $pkgsvl_id, $sersvt_time);
							$sersvtRecord 		= new TableSpa('service_service_list_times', $sersvtValues);
							if(!$sersvtRecord->insertSuccess()) {
								$insertResult = false;
								$errTxt .= 'INSERT_SERVICE_SERVICE_LIST_TIMES['.($key+1).']_FAIL\n';
								$errTxt .= mysql_error($dbConn).'\n\n';
							}
							$sersvt_id = $sersvtRecord->getKey();
						}

						foreach ($formData['pkgCom_'.$pkg_id.'_'.$svl_id.'_emp_id'] as $comKey => $comEmp_id) {
							$com_per 			= $cmrPkg[$svl_id]; // Percent
							$realPkgSvlTotalPrice 	= $pkgsvlDataList[$pkg_id][$svl_id]['realPrice'] * $serpkg_amount;
							$initCom 			= $realPkgSvlTotalPrice * $com_per / 100;
							$com_rate 			= $formData['pkgCom_'.$pkg_id.'_'.$svl_id.'_com_rate'][$comKey];
							$pkgdtl_com 		= $initCom * $com_rate / 100;
							if(isset($formData['pkgCom_'.$pkg_id.'_'.$svl_id.'_pkgdtl_id'][$comKey])) {
								// Update package_details
								$pkgdtl_id 	= $formData['pkgCom_'.$pkg_id.'_'.$svl_id.'_pkgdtl_id'][$comKey];
								$pkgDtlRecord 	= new TableSpa('package_details', $pkgdtl_id);
								$pkgDtlRecord->setFieldValue('emp_id', $comEmp_id);
								$pkgDtlRecord->setFieldValue('pkgdtl_com', $pkgdtl_com);
								if(!$pkgDtlRecord->commit()) {
									$updateResult = false;
									$errTxt .= 'EDIT_PACKAGE_DETAILS['.($comKey+1).']_FAIL\n';
									$errTxt .= mysql_error($dbConn).'\n\n';
								}
							} else {
								// Add package_details
								$pkgsvl_id = $pkgsvlDataList[$pkg_id][$svl_id]['pkgsvl_id'];
								$pkgdtlValues 		= array($sersvt_id, $comEmp_id, $pkgdtl_com);
								$pkgDtlRecord 		= new TableSpa('package_details', $pkgdtlValues);
								if(!$pkgDtlRecord->insertSuccess()) {
									$updateResult = false;
									$errTxt .= 'ADD_PACKAGE_DETAILS['.($comKey+1).']_FAIL\n';
									$errTxt .= mysql_error($dbConn).'\n\n';
								}
							}
						}
					}
				}
			}
		}
		### End update service_packages & service_package_promotions & package_details


		### Update service_service_lists & service_service_list_promotions
		// Delete service_service_lists if delete old service_service_lists
		$oldServiceSvlList 	= array();
		$newServiceSvlList 	= array();
		$oldSvlPrmDtlList 	= array();
		$newSvlPrmDtlList 	= array();
		$oldSerSvlPrmList 	= array();
		$oldSvlDtlList 		= array();
		$newSvlDtlList 		= array();
		// Find old service_service_lists
		$sql = "SELECT sersvl_id FROM service_service_lists WHERE ser_id = '$code'";
		$result = mysql_query($sql, $dbConn);
		$rows 	= mysql_num_rows($result);
		for($i=0; $i<$rows; $i++) {
			$oldServiceSvlRecord = mysql_fetch_assoc($result);
			array_push($oldServiceSvlList, $oldServiceSvlRecord['sersvl_id']);
		}
		// Find old service_service_list_promotions
		$sql = "SELECT sersvlprm_id, svlprmdtl_id FROM service_service_list_promotions WHERE ser_id = '$code'";
		$result = mysql_query($sql, $dbConn);
		$rows 	= mysql_num_rows($result);
		for($i=0; $i<$rows; $i++) {
			$oldServiceSvlPrmRecord = mysql_fetch_assoc($result);
			array_push($oldSvlPrmDtlList, $oldServiceSvlPrmRecord['svlprmdtl_id']);
			$oldSerSvlPrmList[$oldServiceSvlPrmRecord['svlprmdtl_id']] = $oldServiceSvlPrmRecord['sersvlprm_id'];
		}
		// Find new service_service_lists
		if(isset($formData['sersvl_id']) && is_array($formData['sersvl_id'])) {
			foreach ($formData['sersvl_id'] as $key => $newsersvl_id) {
				array_push($newServiceSvlList, $newsersvl_id);
			}
			
			foreach ($formData['svl_id'] as $key => $svl_id) {
				// Find new service_service_list_promotions
				if(hasValue($formData['prmSale_'.$svl_id.'_svlprmdtl_id'])) {
					array_push($newSvlPrmDtlList, $formData['prmSale_'.$svl_id.'_svlprmdtl_id']);
				}
			}
		}
		// Find old service_list_detail
		$sql = "SELECT 		sd.svldtl_id 
				FROM 		service_list_details sd,
							service_service_lists ss 
				WHERE 		sd.sersvl_id = ss.sersvl_id AND 
							ss.ser_id = '$code'";
		$result = mysql_query($sql, $dbConn);
		$rows 	= mysql_num_rows($result);
		for($i=0; $i<$rows; $i++) {
			$record = mysql_fetch_assoc($result);
			array_push($oldSvlDtlList, $record['svldtl_id']);
		}
		// Find new service_list_detail
		if(isset($formData['svldtl_id']) && is_array($formData['svldtl_id'])) {
			foreach ($formData['svldtl_id'] as $key => $newsvldtl_id) {
				array_push($newSvlDtlList, $newsvldtl_id);
			}
		}
		
		// Check for delete service_list_detail
		foreach ($oldSvlDtlList as $key => $oldsvldtl_id) {
			if(!in_array($oldsvldtl_id, $newSvlDtlList)) {
				// Delete service_service_lists
				$svlDtlRecord 	= new TableSpa('service_list_details', $oldsvldtl_id);
				if(!$svlDtlRecord->delete()) {
					$updateResult = false;
					$errTxt .= "DELETE_SERVICE_LIST_DETAILS[$oldsvldtl_id]_FAIL\n";
					$errTxt .= mysql_error($dbConn).'\n\n';
				}
			}
		}
		
		// Check for delete service_service_lists
		foreach ($oldServiceSvlList as $key => $oldsersvl_id) {
			if(!in_array($oldsersvl_id, $newServiceSvlList)) {
				// Delete service_service_lists
				$serviceSvlRecord 	= new TableSpa('service_service_lists', $oldsersvl_id);
				if(!$serviceSvlRecord->delete()) {
					$updateResult = false;
					$errTxt .= "DELETE_SERVICE_SERVICE_LISTS[$oldsersvl_id]_FAIL\n";
					$errTxt .= mysql_error($dbConn).'\n\n';
				}
			}
		}

		// Check for delete service_service_list_promotions
		foreach ($oldSvlPrmDtlList as $key => $oldsvlprmdtl_id) {
			if(!in_array($oldsvlprmdtl_id, $newSvlPrmDtlList)) {
				// Delete service_service_list_promotions
				$sersvlprm_id = $oldSerSvlPrmList[$oldsvlprmdtl_id];
				$serviceSvlPrmRecord 	= new TableSpa('service_service_list_promotions', $sersvlprm_id);
				if(!$serviceSvlPrmRecord->delete()) {
					$updateResult = false;
					$errTxt .= "DELETE_SERVICE_SERVICE_LIST_PROMOTIONS[$sersvlprm_id]_FAIL\n";
					$errTxt .= mysql_error($dbConn).'\n\n';
				}
			}
		}

		// Find commission percent of service lists
		$cmrSvl = array();
		if(isset($formData['svl_id']) && is_array($formData['svl_id'])) {
			foreach ($formData['svl_id'] as $key => $svl_id) {
				$sersvl_time 		= $formData['sersvl_time'][$key];
				$sersvl_time_end	= $formData['sersvl_time_end'][$key];
				$cmrSvl[$svl_id] = getComRate($currentDay, $sersvl_time, $sersvl_time_end);
			}
		}

		// Update or Add service_service_lists & service_service_list_promotions & service_list_details
		if(isset($formData['svl_id']) && is_array($formData['svl_id'])) {
			foreach ($formData['svl_id'] as $key => $svl_id) {
				$sersvl_amount  = $formData['svl_qty'][$key];
				$sersvl_total_price = $formData['sersvl_total_price'][$key];
				$sersvl_time = $formData['sersvl_time'][$key];

				if(isset($formData['sersvl_id'][$key])) {
					// Update service_service_lists
					$sersvl_id = $formData['sersvl_id'][$key];
					$serviceSvlRecord 	= new TableSpa('service_service_lists', $sersvl_id);
					$serviceSvlRecord->setFieldValue('svl_id', $svl_id);
					$serviceSvlRecord->setFieldValue('sersvl_amount', $sersvl_amount);
					$serviceSvlRecord->setFieldValue('sersvl_total_price', $sersvl_total_price);
					$serviceSvlRecord->setFieldValue('sersvl_time', $sersvl_time);
					if(!$serviceSvlRecord->commit()) {
						$updateResult = false;
						$errTxt .= 'EDIT_SERVICE_SERVICE_LISTS['.($key+1).']_FAIL\n';
						$errTxt .= mysql_error($dbConn).'\n\n';
					}
				} else {
					// Add new service_service_lists
					$sersvlValues 		= array($code, $svl_id, $sersvl_amount, $sersvl_total_price, $sersvl_time);
					$serviceSvlRecord 	= new TableSpa('service_service_lists', $sersvlValues);
					if(!$serviceSvlRecord->insertSuccess()) {
						$updateResult = false;
						$errTxt .= 'ADD_SERVICE_SERVICE_LISTS['.($key+1).']_FAIL\n';
						$errTxt .= mysql_error($dbConn).'\n\n';
					}
					$sersvl_id = $serviceSvlRecord->getKey();
				}

				if(hasValue($formData['prmSale_'.$svl_id.'_svlprmdtl_id'])) {
					$svlprmdtl_id 				= $formData['prmSale_'.$svl_id.'_svlprmdtl_id'];
					$sersvlprm_amount 			= $formData['prmSale_'.$svl_id.'_sersvlprm_amount'];
					$sersvlprm_discout_total 	= $formData['prmSale_'.$svl_id.'_sersvlprm_discout_total'];

					if(!in_array($svlprmdtl_id, $oldSvlPrmDtlList)) {
						// Add service_service_list_promotions
						$sersvlprmValues = array($code, $svlprmdtl_id, $sersvlprm_amount, $sersvlprm_discout_total);
						$sersvlprmRecord = new TableSpa('service_service_list_promotions', $sersvlprmValues);
						if(!$sersvlprmRecord->insertSuccess()) {
							$updateResult = false;
							$errTxt .= 'INSERT_SERVICE_SERVICE_LIST_PROMOTIONS['.($key+1).']_FAIL\n';
							$errTxt .= mysql_error($dbConn).'\n\n';
						}
					} else {
						// Update service_service_list_promotions
						$sersvlprm_id = $oldSerSvlPrmList[$svlprmdtl_id];
						$serviceSvlPrmRecord = new TableSpa('service_service_list_promotions', $sersvlprm_id);
						$serviceSvlPrmRecord->setFieldValue('sersvlprm_amount', $sersvlprm_amount);
						$serviceSvlPrmRecord->setFieldValue('sersvlprm_discout_total', $sersvlprm_discout_total);
						if(!$serviceSvlPrmRecord->commit()) {
							$updateResult = false;
							$errTxt .= 'EDIT_SERVICE_SERVICE_LIST_PROMOTIONS['.($key+1).']_FAIL\n';
							$errTxt .= mysql_error($dbConn).'\n\n';
						}
					}
				}

				// Update or Add service_list_details
				if(isset($formData['svlCom_'.$svl_id.'_emp_id']) && is_array($formData['svlCom_'.$svl_id.'_emp_id'])) {
					foreach ($formData['svlCom_'.$svl_id.'_emp_id'] as $comKey => $comEmp_id) {
						$com_per 			= $cmrSvl[$svl_id]; // Percent
						if($com_per > 0) {
							$realSvlTotalPrice 	= getRealSerSvlTotalPrice($code, $svl_id);
							$initCom 			= $realSvlTotalPrice * $com_per / 100;
							$com_rate 			= $formData['svlCom_'.$svl_id.'_com_rate'][$comKey];
							$svldtl_com 		= $initCom * $com_rate / 100;
							if(isset($formData['svlCom_'.$svl_id.'_svldtl_id'][$comKey])) {
								// Update service_list_details
								$svldtl_id 	= $formData['svlCom_'.$svl_id.'_svldtl_id'][$comKey];
								$svlDtlRecord 	= new TableSpa('service_list_details', $svldtl_id);
								$svlDtlRecord->setFieldValue('emp_id', $comEmp_id);
								$svlDtlRecord->setFieldValue('svldtl_com', $svldtl_com);
								if(!$svlDtlRecord->commit()) {
									$updateResult = false;
									$errTxt .= 'EDIT_SERVICE_LIST_DETAILS['.($comKey+1).']_FAIL\n';
									$errTxt .= mysql_error($dbConn).'\n\n';
								}
							} else {
								// Add service_list_details
								$svldtlValues 		= array($svl_id, $comEmp_id, $sersvl_id, $svldtl_com);
								$svlDtlRecord 		= new TableSpa('service_list_details', $svldtlValues);
								if(!$svlDtlRecord->insertSuccess()) {
									$updateResult = false;
									$errTxt .= 'ADD_SERVICE_LIST_DETAILS['.($comKey+1).']_FAIL\n';
									$errTxt .= mysql_error($dbConn).'\n\n';
								}
							}
						}
					}
				}
			}
		}
		### End update service_service_lists & service_service_list_promotions & service_list_details

		
		// Update booking status
		if($formData['bkg_id'] != '') {
			if(!updateBookingStatus($formData['bkg_id'])) {
				$insertResult = false;
				$errTxt .= 'UPDATE_BOOKING_STATUS['.$formData['bkg_id'].']_FAIL\n';
				$errTxt .= mysql_error($dbConn).'\n\n';
			}
		}

		if($updateResult) {
			$response['status'] = 'EDIT_PASS';
			echo json_encode($response);
		} else {
			$response['status'] = $errTxt;
			echo json_encode($response);
		}


	}
}

function getRealSerSvlTotalPrice($ser_id, $svl_id) {
	global $dbConn;
	$realPrice = 0;
	$sql = "SELECT 	sersvl_total_price - 
					(
						SELECT 	COALESCE(SUM(sp.sersvlprm_discout_total),0) 
						FROM 	service_service_list_promotions sp, 
								service_list_promotion_details sd 
						WHERE 	sp.svlprmdtl_id= sd.svlprmdtl_id AND 
								sd.svl_id = '$svl_id' AND 
								sp.ser_id = '$ser_id'
					) AS \"realPrice\" 
			FROM 	service_service_lists 
			WHERE 	svl_id = '$svl_id' AND 
					ser_id = '$ser_id'";
	$result = mysql_query($sql, $dbConn);
	$rows 	= mysql_num_rows($result);
	if($rows > 0) {
		$record = mysql_fetch_assoc($result);
		$realPrice = $record['realPrice'];
	}
	return $realPrice;
}

function getPkgSvlDataList() {
	global $dbConn, $nowDate;
	$pkgsvlData = array();
	$sql = "SELECT 		p.pkg_id,
						ps.svl_id,
						ps.pkgsvl_id,
						ps.pkgsvl_price 
			FROM 		packages p,
						package_service_lists ps 
			WHERE 		p.pkg_id = ps.pkg_id AND 
						p.pkg_start <= '$nowDate' AND 
						(
							p.pkg_stop IS NULL OR 
							p.pkg_stop >= '$nowDate'
						) 
			ORDER BY 	ps.pkgsvl_id";
	$result = mysql_query($sql, $dbConn);
	$rows 	= mysql_num_rows($result);
	for($i=0; $i<$rows; $i++) {
		$record = mysql_fetch_assoc($result);
		$pkgsvlData[$record['pkg_id']][$record['svl_id']] = array(
			'pkgsvl_id' => $record['pkgsvl_id'],
			'price' => $record['pkgsvl_price'],
			'realPrice' => $record['pkgsvl_price']
		);
	}

	// Find pkgsvl real price for discout percent
	$sql = "SELECT 		pkg_id,
						pkgprmdtl_discout,
						pkgprmdtl_discout_type 
			FROM 		package_promotion_details 
			WHERE 		pkgprmdtl_startdate <= '$nowDate' AND 
						(
							pkgprmdtl_enddate IS NULL OR 
							pkgprmdtl_enddate >= '$nowDate'
						)";
	$result = mysql_query($sql, $dbConn);
	$rows 	= mysql_num_rows($result);
	for($i=0; $i<$rows; $i++) {
		$record = mysql_fetch_assoc($result);
		if(isset($pkgsvlData[$record['pkg_id']])) {
			$pkgsvlData[$record['pkg_id']]['discout_type'] = $record['pkgprmdtl_discout_type'];
			$pkgsvlData[$record['pkg_id']]['discout'] = $record['pkgprmdtl_discout'];
			foreach ($pkgsvlData[$record['pkg_id']] as $key => $value) {
				if(is_array($value) && isset($value['realPrice']) && $value['realPrice'] > 0) {
					if($record['pkgprmdtl_discout_type'] == '%') {
						$realPrice = $value['price'] - ($value['realPrice'] * $record['pkgprmdtl_discout'] / 100);
						$pkgsvlData[$record['pkg_id']][$key]['realPrice'] = $realPrice;
					}
				}
			}
		}
	}

	return $pkgsvlData;
}

?>