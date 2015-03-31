<?php
/*
 * Process Zone
 */
session_start();
include('../config/config.php');
include('../common/common_header.php');

// Pre Valiable
$tableName		= 'orders';
$sortCol		= $_REQUEST['sortCol'];
$sortBy			= 'desc';
$filter 		= $_REQUEST['filter'];
$filterRetroact = $_REQUEST['filterRetroact'];
$where 			= 'WHERE o.emp_id = e.emp_id AND o.comp_id = c.comp_id '
				. 'AND o.ordtyp_id = ot.ordtyp_id ';
$order 			= $_REQUEST['order'];
$tableInfo		= getTableInfo($tableName);
$retroactDate 	= '';

if(hasValue($_REQUEST['sortBy'])) {
	$sortBy	= $_REQUEST['sortBy'];
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

// Generate search
if(hasValue($_REQUEST['searchCol']) && hasValue($_REQUEST['searchInput'])) {
	$searchCol		= $_REQUEST['searchCol'];
	$searchInput	= $_REQUEST['searchInput'];
	$like			= "$searchCol like '%$searchInput%'";

	if($searchCol == 'emp_id') {
		$like = "(e.emp_name like '%$searchInput%' OR e.emp_surname like '%$searchInput%') ";
	} else {
		$like = str_replace('comp_id', 'c.comp_name', $like);
		$like = str_replace('ordtyp_id', 'ot.ordtyp_name', $like);
	}
	$where .= " AND $like";
}

// Generate filter
if(hasValue($_REQUEST['filter'])) {
	$filter = $_REQUEST['filter'];
	if($filter == 'WAIT') {
		$where .= " AND ordstat_id = 'OS01' ";
		$whereAllRecord = " WHERE ordstat_id = 'OS01' ";
	} else if($filter == 'REMAIN') {
		$where .= " AND ordstat_id = 'OS02' ";
		$hideIconCol = true; // hide column action icon in thead
		$whereAllRecord = " WHERE ordstat_id = 'OS02' ";
	} else if($filter == 'COMPLETED') {
		$where .= " AND ordstat_id = 'OS03' ";
		$hideIconCol = true; // hide column action icon in thead
		$whereAllRecord = " WHERE ordstat_id = 'OS03' ";
	} else if($filter == 'FIXED_COMPLETED') {
		$where .= " AND ordstat_id = 'OS04' ";
		$hideIconCol = true; // hide column action icon in thead
		$whereAllRecord = " WHERE ordstat_id = 'OS04' ";
	}
}

// Generate filter retroact
if(!hasValue($like)) {
	$where .= " AND ord_date >= '$retroactDate' ";
	if(isset($whereAllRecord)) {
		$whereAllRecord .= " AND ord_date >= '$retroactDate' ";
	} else {
		$whereAllRecord = " WHERE ord_date >= '$retroactDate' ";
	}
}
 	 	 	 	 	 	 	 	 	
// Query table data
$sql = "SELECT o.ord_id,
				CONCAT(e.emp_name, '  ', e.emp_surname) emp_id,
				c.comp_name comp_id,
				ot.ordtyp_name ordtyp_id,
				o.ord_date,
				o.ord_snd_date 
		FROM orders o, order_types ot, employees e, companies c 
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
$viewPrivileges 	= true;
$displayAddBtn 		= true;
$displayEditBtn 	= true;
$displayDeleteBtn 	= true;
switch ($tableName) {
	case 'orders':
		if(!$emp_privileges['manage_orders']) {
			$viewPrivileges = false;
			$displayAddBtn = false;
			$displayEditBtn = false;
			$displayDeleteBtn = false;
		}
		break;
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
			$code = $row[$tableInfo['keyFieldName']];
			?>
			<tr id="<?=$code?>">
				<?
				if($filter == 'WAIT') {
				?>
					<td class="icon-col">
						<input type="checkbox" value="<?=$code?>" name="table-record[]" class="mbk-checkbox" onclick="checkRecord(this)">
					</td>
				<?
				}
				?>
					<td class="action-col">
					<?php
					?>
						<?php if ($emp_privileges['print_purchase_orders']) { ?>
							<a title="ดูใบสั่งซื้อ">
								<i class="fa fa-file-text-o" onclick="openPrintPurchaseOrder('<?=$code?>')"></i>
							</a>
						<?php } ?>
						<?php if($filter == 'WAIT') { ?>
							<?php if($displayEditBtn) { ?>
								<a title="แก้ไข">
									<i class="fa fa-pencil" onclick="openFormTable('EDIT', '<?=$code?>')"></i>
								</a>
							<?php } ?>
							<?php if($displayDeleteBtn) { ?>
								<a title="ลบ">
									<i class="fa fa-times" onclick="delteCurrentRecord('<?=$code?>')"></i>
								</a>
							<?php } ?>
						<?php } ?>
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
