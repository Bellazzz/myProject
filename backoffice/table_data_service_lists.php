<?php
/*
 * Process Zone
 */
include('../common/common_constant.php');
include('../common/common_function.php');

// Pre Valiable
$tableName	= 'service_lists';
$sortCol	= $_REQUEST['sortCol'];
$sortBy		= $_REQUEST['sortBy'];
$order		= $_REQUEST['order'];
$pathPic = '../img/service_lists/';
$where		= 'WHERE s.svltyp_id = t.svltyp_id';
$tableInfo	= getTableInfo($tableName);

if(hasValue($_REQUEST['searchCol']) && hasValue($_REQUEST['searchInput'])) {
	$searchCol		= $_REQUEST['searchCol'];
	$searchInput	= $_REQUEST['searchInput'];
	$like			= "$searchCol like '%$searchInput%'";
	$like			= str_replace('svltyp_id', 't.svltyp_name', $like);
	$where		   .= ' AND '.$like;
}
								
// Query table data
$sql = "SELECT s.svl_picture,
		s.svl_id,
		s.svl_name,
		t.svltyp_name svltyp_id,
		s.svl_desc,
		s.svl_hr,
		s.svl_min,
		s.svl_price 
		FROM service_lists s, service_list_types t 
		$where 
		$order";
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
				if($field == 'svl_picture') {
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
