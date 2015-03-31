<?php
/*
 * Process Zone
 */
include('../common/common_constant.php');
include('../common/common_function.php');

// Pre Valiable
$tableName		= 'promotion_products';
$sortCol		= $_REQUEST['sortCol'];
$sortBy			= 'desc';
$filter 		= $_REQUEST['filter'];
$filterRetroact = $_REQUEST['filterRetroact'];
$where 			= 'WHERE prmprd.prdprm_id = prdprm.prdprm_id AND prmprd.prd_id = p.prd_id';
$order 			= $_REQUEST['order'];
$tableInfo		= getTableInfo($tableName);
$nowDate 		= date('Y/m/d');

if(hasValue($_REQUEST['sortBy'])) {
	$sortBy	= $_REQUEST['sortBy'];
}

// Generate search
if(hasValue($_REQUEST['searchCol']) && hasValue($_REQUEST['searchInput'])) {
	$searchCol		= $_REQUEST['searchCol'];
	$searchInput	= $_REQUEST['searchInput'];
	$like			= "$searchCol like '%$searchInput%'";

	$like	= str_replace('prdprm_id', 'prdprm.prdprm_name', $like);
	$like	= str_replace('prd_id', 'p.prd_id', $like);
	$like	= str_replace('prmprd_discout_type', "COALESCE(CONCAT(prmprd.prmprd_discout,' ', prmprd.prmprd_discout_type), 'ฟรี')", $like);
	$like	= str_replace('%%%','%\%%', $like);
	$where .= " AND $like";
}

// Generate filter
if(hasValue($_REQUEST['filter'])) {
	$filter = $_REQUEST['filter'];
	if($filter == 'ONLINE') {
		$where .= " AND prmprd.prmprd_startdate <= '$nowDate' 
					AND (
						prmprd.prmprd_enddate IS NULL 
						OR prmprd.prmprd_enddate >= '$nowDate'
					) ";
		$whereAllRecord = " WHERE 	prmprd_startdate <= '$nowDate' 
									AND (
										prmprd_enddate IS NULL 
										OR prmprd_enddate >= '$nowDate'
									) ";
	} else if($filter == 'FORWARD') {
		$where .= " AND prmprd.prmprd_startdate > '$nowDate' ";
		$whereAllRecord = " WHERE prmprd_startdate > '$nowDate' ";
	} else if($filter == 'EXPIRED') {
		$where .= " AND (
						prmprd.prmprd_enddate IS NOT NULL 
						AND prmprd.prmprd_enddate < '$nowDate' 
					) ";
		$whereAllRecord = " WHERE (
								prmprd_enddate IS NOT NULL 
								AND prmprd_enddate < '$nowDate' 
							) ";
	}
}
 	 	 	 	 	 	 	 	 	
// Query table data
$sql = "SELECT 	prmprd.prmprd_id,
				p.prd_name prd_id,
				prdprm.prdprm_name prdprm_id,
				COALESCE(CONCAT(prmprd.prmprd_discout,' ', prmprd.prmprd_discout_type), 'ฟรี') prmprd_discout_type,
				prmprd.prmprd_startdate,
				prmprd.prmprd_enddate 
		FROM promotion_products prmprd, product_promotions prdprm, products p 
		$where 
		$order";
$result		= mysql_query($sql, $dbConn);
$rows 		= mysql_num_rows($result);
$tableData	= array();

// Find all record
if(hasValue($_REQUEST['searchCol']) && hasValue($_REQUEST['searchInput'])) {
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
$displayAddBtn 		= true;
$displayEditBtn 	= true;
$displayDeleteBtn 	= true;
switch ($tableName) {
	case 'promotion_products':
		if(!$emp_privileges['manage_promotion_products']) {
			$displayAddBtn = false;
			$displayEditBtn = false;
			$displayDeleteBtn = false;
		}
		break;
}

if(!$displayAddBtn && !$displayEditBtn && !$displayDeleteBtn) {
	echo alertNoPrivlgTableData();
} else if($rows > 0){
//Has record will display table data
?>
<table class="mbk mbk-table-sortable">
	<? include('table_data_thead.php') ?>
	<tbody id="table-data">
		<?
		foreach($tableData as $key => $row) {
			$code = $row[$tableInfo['keyFieldName']];
			?>
			<tr id="<?=$code?>">
					<td class="icon-col"></td>
					<td class="action-col">
						<?
						if($filter != 'EXPIRED') {
						?>
						<a title="แก้ไข">
							<i class="fa fa-pencil" onclick="openFormTable('EDIT', '<?=$code?>')"></i>
						</a>
						
						<a title="ลบ">
							<i class="fa fa-times" onclick="delteCurrentRecord('<?=$code?>')"></i>
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
				if($field == $tableInfo['keyFieldName']) {
					if(isset($tableInfo['hiddenFields'])) {
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
				else if(mysql_field_type($result, $offset) == 'real') {
					?>
					<td field="<?=$field?>" class="real-col"><? echo number_format($value,2);?></td>
					<?
				} 
				else if (mysql_field_type($result, $offset) == 'int'){
					?>
					<td  field="<?=$field?>" class="real-col"><?=$value?></td>
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
				else {
					?>
					<td field="<?=$field?>"><?=$value?></td>
					<?
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

	//$('#tmpScriptTableData1').remove();
	//$('#tmpScriptTableData2').remove();
</script>
