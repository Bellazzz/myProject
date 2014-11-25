<?php
/*
 * Process Zone
 */
include('../common/common_constant.php');
include('../common/common_function.php');

// Pre Valiable
$tableName		= 'receives';
$sortCol		= $_REQUEST['sortCol'];
$sortBy			= 'desc';
$filter 		= $_REQUEST['filter'];
$filterRetroact = $_REQUEST['filterRetroact'];
$where 			= 'WHERE r.ord_id = o.ord_id AND r.emp_id = e.emp_id ';
$tableInfo		= getTableInfo($tableName);

if(hasValue($_REQUEST['sortBy'])) {
	$sortBy	= $_REQUEST['sortBy'];
}

// Generate search
if(hasValue($_REQUEST['searchCol']) && hasValue($_REQUEST['searchInput'])) {
	$searchCol		= $_REQUEST['searchCol'];
	$searchCol		= str_replace('ord_id', 'o.ord_id', $searchCol);
	$searchInput	= $_REQUEST['searchInput'];
	$like			= "$searchCol like '%$searchInput%'";

	if($searchCol == 'emp_id') {
		$like = "(e.emp_name like '%$searchInput%' OR e.emp_surname like '%$searchInput%') ";
	}
	$where .= " AND $like";
}

// Generate filter
if(hasValue($_REQUEST['filter'])) {
	$filter = $_REQUEST['filter'];
	if($filter == 'REMAIN') {
		$where .= " AND o.ordstat_id = 'OS02' ";
		$whereAllRecord = " AND ordstat_id = 'OS02' ";
	} else if($filter == 'COMPLETED') {
		$where 			.= " AND o.ordstat_id = 'OS03' ";
		$hideIconCol 	= true; // hide column icon in thead
		$hideActionCol 	= true; // hide column action in thead
		$whereAllRecord = " AND o.ordstat_id = 'OS03' ";
	}
}

// Generate filter retroact
if(hasValue($_REQUEST['filterRetroact'])) {
	if($filterRetroact == 'true') {
		$retroactDate = date('Y-m-d', strtotime('-1 years'));
		$where .= " AND r.rec_date >= '$retroactDate' ";
		$whereAllRecord .= " AND r.rec_date >= '$retroactDate' ";
	}
}
 	 	 	 	 	 	 	 	 	
// Query table data
$sql = "SELECT 	r.rec_id,
				r.ord_id,
				CONCAT(e.emp_name, '  ', e.emp_surname) emp_id,
				r.rec_date,
				r.rec_total_price 
		FROM 	receives r, orders o, employees e 
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
	$sqlAllRecord 		= "SELECT COUNT(*) allRecords 
							FROM receives r, orders o, employees e 
							WHERE r.ord_id = o.ord_id AND r.emp_id = e.emp_id 
							$whereAllRecord";
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

if($rows > 0){
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
				<?
				if($filter == 'REMAIN') {
				?>
					<td field="<?=$field?>" class="icon-col">
						<input type="checkbox" value="<?=$code?>" name="table-record[]" class="mbk-checkbox" onclick="checkRecord(this)">
					</td>
					<td field="<?=$field?>" class="action-col">
						<a title="แก้ไข">
							<i class="fa fa-pencil" onclick="openFormTable('EDIT', '<?=$code?>')"></i>
						</a>
						<a title="ลบ">
							<i class="fa fa-times" onclick="delteCurrentRecord('<?=$code?>')"></i>
						</a>
					</td>
			<?
				}
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
						<td><a href="javascript:openFormTable('VIEW_DETAIL', '<?=$value?>');" class="normal-link" title="คลิกเพื่อดูรายละเอียด"><?=$value?></a></td>
						<?
					} else {
						?>
						<td><?=$value?></td>
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
