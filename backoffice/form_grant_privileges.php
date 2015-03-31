<?php
session_start();
$action			= isset($_REQUEST['action']) ? $_REQUEST['action'] : 'ADD';
$tableName		= 'grant_privileges';
$code			= $_REQUEST['code'];
$hideEditButton = $_REQUEST['hideEditButton'];
$hideBackButton = $_REQUEST['hideBackButton'];

include('../config/config.php');
$tplName = "form_$tableName.html";
$subDir	 = WEB_ROOTDIR.'/backoffice/';
include('../common/common_header.php');
$tableInfo = getTableInfo($tableName);

if(!$_REQUEST['ajaxCall']) {
	$checkedList = array();

	//1. Display form
	if($action == 'EDIT') {
		$smarty->assign('code', $code);

		// Get grant privileges (checked)
		$sql = "SELECT 		privlg_id 
				FROM 		grant_privileges gp 
				WHERE 		emp_id = '$code'";
		$result = mysql_query($sql, $dbConn);
		$rows 	= mysql_num_rows($result);
		for($i=0; $i<$rows; $i++) {
			$record = mysql_fetch_assoc($result);
			$checkedList[$record['privlg_id']]['checked'] = true;
		}

	} else if($action == 'VIEW_DETAIL') {
		// Get table grant_privileges data
		$tableRecord = new TableSpa($tableName, $code);
		$values      = array();
		foreach($tableInfo['fieldNameList'] as $field => $value) {
			$values[$field] = $tableRecord->getFieldValue($field);
		}
		$smarty->assign('values', $values);

		// Get service lists of grant_privileges
		
		$grnprivlgList = array();
		$sql = "SELECT 		p.privlg_name_th  
				FROM 		grant_privileges gp, privileges p 
				WHERE 		gp.privlg_id = p.privlg_id 
							AND gp.emp_id = '$code' 
				ORDER BY 	p.privlg_name_th";
		$result = mysql_query($sql, $dbConn);
		$rows 	= mysql_num_rows($result);
		for($i=0; $i<$rows; $i++) {
			array_push($grnprivlgList, mysql_fetch_assoc($result));
			$grnprivlgList[$i]['no'] = $i+1;
		}
		$smarty->assign('grnprivlgList', $grnprivlgList);

		$empRecord = new TableSpa('employees', $code);
		$emp_fullName = $empRecord->getFieldValue('emp_name')." ".$empRecord->getFieldValue('emp_surname');
		$smarty->assign('emp_fullName', $emp_fullName);
	}

	// Get reference data for selectReferenceJS
	if(is_array($tableInfo['referenceData']) && count($tableInfo['referenceData']) > 0) {
		$sqlRefData 	= '';
		$referenceData 	= array();

		foreach ($tableInfo['referenceData'] as $key => $table) {
			switch ($table) {
				case 'employees':
					if($action == 'ADD') {
						$sqlRefData = "	SELECT 	* 
										FROM 	(
												SELECT 		e.emp_id refValue,
															CONCAT(e.emp_name, ' ', e.emp_surname) refText,
															COUNT(g.grnprivlg_id) AS amount 
												FROM 		employees e 
															LEFT JOIN 
															grant_privileges g 
															ON e.emp_id = g.emp_id 
												GROUP BY 	e.emp_id 
												ORDER BY 	refText ASC
												) a
										WHERE 	amount <= 0";
					} else {
						$sqlRefData = " SELECT 	e.emp_id refValue,
												CONCAT(e.emp_name, ' ', e.emp_surname) refText 
										FROM 	employees e 
										WHERE 	e.emp_id = '$code' 
										ORDER BY 	refText ASC";
					}
					
					$refField 	= 'emp_id';
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

	if($action != 'VIEW_DETAIL') {
		// Get all privileges data
		$privlgList = array();
		$sql = "SELECT  	privlg_id,
							privlg_name,
							privlg_name_th 
				FROM 		privileges 
				ORDER BY 	privlg_name_th";
		$result = mysql_query($sql, $dbConn);
		$rows 	= mysql_num_rows($result);
		if($rows > 0) {
			for($i=0; $i<$rows; $i++) {
				$record = mysql_fetch_assoc($result);
				$record['no'] = $i+1;
				$privlgList[$record['privlg_id']]['no'] 			= number_format($record['no']);
				$privlgList[$record['privlg_id']]['privlg_id'] 		= $record['privlg_id'];
				$privlgList[$record['privlg_id']]['privlg_name']	= $record['privlg_name'];
				$privlgList[$record['privlg_id']]['privlg_name_th'] = $record['privlg_name_th'];
				if(isset($checkedList[$record['privlg_id']])) {
					$privlgList[$record['privlg_id']]['checked'] = true;
				}
			}
			$smarty->assign('privlgList', $privlgList);
		}
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
	$smarty->assign('randNum', substr(str_shuffle('0123456789'), 0, 5));
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

		// Insert grant_privileges
		$insertResult = true;
		$insertError  = '';
		foreach ($formData['privlg_id'] as $key => $privlg_id) {
			$grnprivlgValues 	= array($privlg_id, $formData['emp_id']);
			$grnprivlgRecord 	= new TableSpa('grant_privileges', $grnprivlgValues);
			if(!$grnprivlgRecord->insertSuccess()) {
				$insertResult = false;
				$insertError .= 'ADD_GRANT_PRIVILEGES['.($key+1).']_FAIL\n';
				$insertError .= mysql_error($dbConn)."\n\n".$privlg_id.",".$formData['emp_id'];
			}
		}

		if($insertResult) {
			// Add grant_privileges success
			$response['status'] = 'ADD_PASS';
			echo json_encode($response);
		} else {
			$response['status'] = $insertError;
			echo json_encode($response);
		}
	} else if($action == 'EDIT') {
		//2.2 Update record
		$updateResult = true;
		$updateError = '';

		// Delete grant_privileges if delete old grant_privileges
		$oldGrnprivlgList = array();
		$newGrnprivlgList = array();
		// Find old grant_privileges
		$sql = "SELECT privlg_id FROM grant_privileges WHERE emp_id = '$code'";
		$result = mysql_query($sql, $dbConn);
		$rows 	= mysql_num_rows($result);
		for($i=0; $i<$rows; $i++) {
			$record = mysql_fetch_assoc($result);
			array_push($oldGrnprivlgList, $record['privlg_id']);
		}
		// Find new grant_privileges
		foreach ($formData['privlg_id'] as $key => $newGrnprivlg_id) {
			array_push($newGrnprivlgList, $newGrnprivlg_id);
		}
		
		// Get for delete 
		$grnprivlgIds_del = array();
		foreach ($oldGrnprivlgList as $key => $oldGrnprivlg_id) {
			$response['status'] .= "enter ($oldGrnprivlg_id)";
			if(!in_array($oldGrnprivlg_id, $newGrnprivlgList)) {
				array_push($grnprivlgIds_del, $oldGrnprivlg_id);
			}
		}

		// Delete grant_privileges
		if(count($grnprivlgIds_del) > 0) {
			$grnprivlgIds_del = wrapSingleQuote($grnprivlgIds_del);
			$sql = "DELETE FROM 	grant_privileges 
					WHERE 			emp_id = '$code' AND 
									privlg_id IN (".implode(',', $grnprivlgIds_del).")";
			$result = mysql_query($sql, $dbConn);
			if(!$result) {
				$updateResult = false;
				$updateError .= "DELETE_GRANT_PRIVILEGES_FAIL\n";
			}
		}

		// Add grant_privileges
		foreach ($newGrnprivlgList as $key => $newGrnprivlg_id) {
			if(!in_array($newGrnprivlg_id, $oldGrnprivlgList)) {
				// Add new grant_privileges
				$grnprivlgValues 	= array($newGrnprivlg_id, $code);
				$grnprivlgRecord 	= new TableSpa('grant_privileges', $grnprivlgValues);
				if(!$grnprivlgRecord->insertSuccess()) {
					$updateResult = false;
					$updateError .= 'ADD_GRANT_PRIVILEGES['.($key+1).']_FAIL\n';
				}
			}
		}

		if($updateResult) {
			// Edit package and package_service_lists success
			$response['status'] = 'EDIT_PASS';
			echo json_encode($response);
		} else {
			// Edit package_service_lists fail
			$response['status'] = $updateError;
			echo json_encode($response);
		}
	}
}
?>