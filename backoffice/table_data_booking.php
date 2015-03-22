<?php
/*
 * Process Zone
 */
session_start();
include('../config/config.php');
include('../common/common_header.php');

// Pre Valiable
$tableName	= 'booking';
$sortCol	= $_REQUEST['sortCol'];
$sortBy		= 'desc';
$filter 		= $_REQUEST['filter'];
$filterRetroact = $_REQUEST['filterRetroact'];
$pathPic = '../img/booking/';
$where		= 'WHERE b.cus_id = c.cus_id AND b.emp_id = e.emp_id AND '
			. 'b.status_id = s.bkgstat_id ';
$order		= $_REQUEST['order'];
$tableInfo	= getTableInfo($tableName);
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

if(hasValue($_REQUEST['searchCol']) && hasValue($_REQUEST['searchInput'])) {
	$searchCol		= $_REQUEST['searchCol'];
	$searchInput	= $_REQUEST['searchInput'];
	$like			= "$searchCol like '%$searchInput%'";

	if($searchCol == 'cus_id') {
		$like = "(c.cus_name like '%$searchInput%' OR c.cus_surname like '%$searchInput%') ";
	} else if($searchCol == 'emp_id') {
		$like = "(e.emp_name like '%$searchInput%' OR e.emp_surname like '%$searchInput%') ";
	} else {
		$like = str_replace('status_id', 'bs.bkgstat_name', $like);
	}

	$where		   .= ' AND '.$like;
}

// Generate filter
if(hasValue($_REQUEST['filter'])) {
	$filter = $_REQUEST['filter'];
	if($filter == 'PENDING_CHECK') {
		$where .= " AND status_id = 'S01' ";
		$whereAllRecord = " WHERE status_id = 'S01' ";
	} else if($filter == 'PENDING_PAYMENT') {
		$where .= " AND status_id = 'S02' ";
		$whereAllRecord = " WHERE status_id = 'S02' ";
	} else if($filter == 'PENDING_SERVICE') {
		$where .= " AND status_id = 'S03' ";
		$whereAllRecord = " WHERE status_id = 'S03' ";
	} else if($filter == 'REMAIN_SERVICE') {
		$where .= " AND status_id = 'S04' ";
		$hideIconCol = true; // hide column action icon in thead
		$whereAllRecord = " WHERE status_id = 'S04' ";
	} else if($filter == 'COMPLETED') {
		$where .= " AND status_id = 'S05' ";
		$hideIconCol = true; // hide column action icon in thead
		$whereAllRecord = " WHERE status_id = 'S05' ";
	} else if($filter == 'CANCEL') {
		$where .= " AND status_id = 'S06' ";
		$hideIconCol = true; // hide column action icon in thead
		$whereAllRecord = " WHERE status_id = 'S06' ";
	} else if($filter == 'FIXED_COMPLETED') {
		$where .= " AND status_id = 'S07' ";
		$hideIconCol = true; // hide column action icon in thead
		$whereAllRecord = " WHERE status_id = 'S07' ";
	}
}

// Generate filter retroact
if(!hasValue($like)) {
	$where .= " AND bkg_date >= '$retroactDate' ";
	if(isset($whereAllRecord)) {
		$whereAllRecord .= " AND bkg_date >= '$retroactDate' ";
	} else {
		$whereAllRecord = " WHERE bkg_date >= '$retroactDate' ";
	}
}
 	 	 	 	 	 	 	 	 	
// Query table data
$sql = "SELECT 	b.bkg_id,
				CONCAT(c.cus_name, ' ', c.cus_surname) cus_id,
				CONCAT(e.emp_name, ' ', e.emp_surname) emp_id,
				b.bkg_date,
				s.bkgstat_name status_id 
		FROM booking b, booking_status s, employees e, customers c 
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
	case 'booking':
		if(!$emp_privileges['insert_booking'])
			$displayAddBtn = false;
		if(!$emp_privileges['update_booking'])
			$displayEditBtn = false;
		if(!$emp_privileges['delete_booking'])
			$displayDeleteBtn = false;
		break;
}

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
			<tr>
				<td class="icon-col">
					<input type="checkbox" value="<?=$code?>" name="table-record[]" class="mbk-checkbox" onclick="checkRecord(this)">
				</td>
				<td class="action-col">
					<?php if($displayEditBtn && $filter == 'PENDING_CHECK') { ?>
					<a title="แก้ไข">
						<i class="fa fa-pencil" onclick="openFormTable('EDIT', '<?=$code?>')"></i>
					</a>
					<?php } ?>
					<?php if($displayDeleteBtn && $filter == 'PENDING_CHECK') { ?>
					<a title="ลบ">
						<i class="fa fa-times" onclick="delteCurrentRecord('<?=$code?>')"></i>
					</a>
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
					else if($field == 'status_id') {
						$txtClass = 'statusHL-gray';
						if($filter == 'PENDING_SERVICE') {
							$txtClass = 'statusHL-blue';
						} else if($filter == 'PENDING_PAYMENT') {
							$txtClass = 'statusHL-darkYellow';
						} else if($filter == 'REMAIN_SERVICE') {
							$txtClass = 'statusHL-lightGreen';
						} else if($filter == 'COMPLETED') {
							$txtClass = 'statusHL-green';
						} else if($filter == 'CANCEL') {
							$txtClass = 'statusHL-red';
						} else if($filter == 'FIXED_COMPLETED') {
							$txtClass = 'statusHL-yellow';
						}
						?>
						<td field="<?=$field?>">
							<span class="statusHL <?php echo $txtClass; ?>">
								<?=$value?>
							</span>
						</td>
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

