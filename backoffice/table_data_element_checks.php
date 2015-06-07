<?php
/*
 * Process Zone
 */
include('../common/common_constant.php');
include('../common/common_function.php');

// Pre Valiable
$tableName	= 'element_checks';
$sortCol	= $_REQUEST['sortCol'];
$sortBy		= $_REQUEST['sortBy'];
$order		= $_REQUEST['order'];
$pathPic = '../img/element_checks/';
$where		= '';
$tableInfo	= getTableInfo($tableName);

if(hasValue($_REQUEST['searchCol']) && hasValue($_REQUEST['searchInput'])) {
	$searchCol		= $_REQUEST['searchCol'];
	$searchInput	= $_REQUEST['searchInput'];
	$like			= "$searchCol like '%$searchInput%'";
	$where		   .= ' WHERE '.$like;
}
								
// Query table data	
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
		et.eletyp_name eletyp_id,
		ec.elechk_pic 
		FROM element_checks ec, employees e, customers c, element_types et
		$where 
		$orderSpecial ";
$sortBy = $sortBySpecial;
$result		= mysql_query($sql, $dbConn);
$rows 		= mysql_num_rows($result);
$tableData	= array();

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
			<tr>
				<td class="icon-col">
					<input type="checkbox" value="<?=$code?>" name="table-record[]" class="mbk-checkbox" onclick="checkRecord(this)">
				</td>
				<td class="action-col">
					<a title="แก้ไข">
						<i class="fa fa-pencil" onclick="openFormTable('EDIT', '<?=$code?>')"></i>
					</a>
					<a title="ลบ">
						<i class="fa fa-times" onclick="delteCurrentRecord('<?=$code?>')"></i>
					</a>
				</td>
			
			<?
			foreach($row as $field => $value) {
				if($field == 'elechk_pic') {
				?>
					<td><img src="<?echo $pathPic.$value?>" style="width: 100px;" title="<?=$value?>"></td>
				<?
				} else {
				?>
					<td><?=$value?></td>
				<?
				}
			}
			?>
			</tr>
			<?
		}
		?>
	</tbody>
</table>
<script id="tmpScriptTableData1" type="text/javascript" src="../js/table_data.js"></script>
<script id="tmpScriptTableData2" type="text/javascript">
	// Set table data
	var table = {
		'name'			: '<?=$tableName?>',
		'nameTH'		: '<?=$tableInfo["tableNameTH"]?>',
		'sortCol'		: '<?=$sortCol?>',
		'sortBy'		: '<?=$sortBy?>',
		'fieldNameList'	: <? echo json_encode($tableInfo['fieldNameList']); ?>
	};
	setTable(table);

	// Config column
	configColumn(<? echo count($tableInfo['fieldNameList']); ?>);

	$('#tmpScriptTableData1').remove();
	$('#tmpScriptTableData2').remove();
</script>
<?
}
else{
// No record will display notification
?>
<div id="table-data-empty">
	<img src="../img/backoffice/test.png"><br>
	ไม่พบข้อมูล
</div>
<?}?>
