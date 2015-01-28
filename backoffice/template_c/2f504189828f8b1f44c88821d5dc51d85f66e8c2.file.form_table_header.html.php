<?php /* Smarty version Smarty-3.1.18, created on 2015-01-28 21:48:18
         compiled from "C:\AppServ\www\myProject\backoffice\template\form_table_header.html" */ ?>
<?php /*%%SmartyHeaderCode:1230654c8f6b2eba405-42718664%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '2f504189828f8b1f44c88821d5dc51d85f66e8c2' => 
    array (
      0 => 'C:\\AppServ\\www\\myProject\\backoffice\\template\\form_table_header.html',
      1 => 1418280575,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '1230654c8f6b2eba405-42718664',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'action' => 0,
    'hideBackButton' => 0,
    'tableName' => 0,
    'code' => 0,
    'hideEditButton' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.18',
  'unifunc' => 'content_54c8f6b30580f3_18891148',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_54c8f6b30580f3_18891148')) {function content_54c8f6b30580f3_18891148($_smarty_tpl) {?><div class="ftb-header">
	<div class="title-container">
		<h1 id="ftb-title"></h1>
	</div>
    <div class="toolbar-container clearfix">
	    <ul class="toolbar-menu">
		    <li>
		    	<?php if ($_smarty_tpl->tpl_vars['action']->value=='ADD') {?>
			    <a id="save-btn">
				    <i class="fa fa-check"></i> เพิ่ม
			    </a>
			    <?php } elseif ($_smarty_tpl->tpl_vars['action']->value=='EDIT') {?>
			    <a id="save-btn">
				    <i class="fa fa-floppy-o"></i> บันทึก
			    </a>
			    <?php } elseif ($_smarty_tpl->tpl_vars['action']->value=='VIEW_DETAIL') {?>
			    	<?php if (!$_smarty_tpl->tpl_vars['hideBackButton']->value) {?>
				    <a id="back-btn">
					    <i class="fa fa-arrow-circle-left"></i> ย้อนกลับ
				    </a>
				    <?php }?>

			    	<?php if ($_smarty_tpl->tpl_vars['tableName']->value=='employees') {?>
				    <a href="printEmployeeCard.php?empId=<?php echo $_smarty_tpl->tpl_vars['code']->value;?>
">
					    <i class="fa fa-credit-card"></i> บัตรพนักงาน
				    </a>
				    <?php } elseif ($_smarty_tpl->tpl_vars['tableName']->value=='orders') {?>
				    <a id="printPurchaseOrd-btn" href="printPurchaseOrder.php?ordId=<?php echo $_smarty_tpl->tpl_vars['code']->value;?>
">
					    <i class="fa fa-file-text-o"></i> ใบสั่งซื้อ
				    </a>
				    <?php }?>
				    
				    <?php if (!$_smarty_tpl->tpl_vars['hideEditButton']->value) {?>
				    <a id="edit-btn">
					    <i class="fa fa-pencil"></i> แก้ไขข้อมูล
				    </a>
				    <?php }?>
			    <?php }?>
		    </li>
		    <li>
			    <a id="cancel-btn">
			    	<?php if ($_smarty_tpl->tpl_vars['action']->value=='VIEW_DETAIL') {?>
			    	<i class="fa fa-times"></i> ปิด
			    	<?php } else { ?>
				    <i class="fa fa-times"></i> ยกเลิก
				    <?php }?>
			    </a>
		    </li>
	    </ul>
    </div>
</div><?php }} ?>
