<?php /* Smarty version Smarty-3.1.18, created on 2015-01-28 22:10:07
         compiled from "C:\AppServ\www\myProject\backoffice\template\form_advertising.html" */ ?>
<?php /*%%SmartyHeaderCode:1540154c8fbcfefd705-09647114%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '06ee255aa23ccef128b7ee02fe4ef96523cd7b24' => 
    array (
      0 => 'C:\\AppServ\\www\\myProject\\backoffice\\template\\form_advertising.html',
      1 => 1421815056,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '1540154c8fbcfefd705-09647114',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'action' => 0,
    'tableName' => 0,
    'tableNameTH' => 0,
    'code' => 0,
    'values' => 0,
    'randNum' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.18',
  'unifunc' => 'content_54c8fbd0159383_59969327',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_54c8fbd0159383_59969327')) {function content_54c8fbd0159383_59969327($_smarty_tpl) {?><!DOCTYPE html>
<html lang="th">
<head>
    <title>avertising - Backoffice</title>
    <meta charset="UTF-8"/>
    
    <link rel="stylesheet" type="text/css" href="../inc/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="../css/lazybingo.css">
    <link rel="stylesheet" type="text/css" href="../inc/jquery-ui/jquery-ui.css"> 
    <script type="text/javascript" src="../js/jquery.min.js"></script>
    <script type="text/javascript" src="../inc/jquery-ui/jquery-ui.js"></script> 
    <script type="text/javascript" src="../js/mbk_common_function.js"></script>
    <script type="text/javascript" src="../js/mbk_main.js"></script>
    <script type="text/javascript" src="../js/mbk_form_table.js"></script>
    <script type="text/javascript">
        // Global variables
        var action      = '<?php echo $_smarty_tpl->tpl_vars['action']->value;?>
';
        var tableName   = '<?php echo $_smarty_tpl->tpl_vars['tableName']->value;?>
';
        var tableNameTH = '<?php echo $_smarty_tpl->tpl_vars['tableNameTH']->value;?>
';
        var code        = '<?php echo $_smarty_tpl->tpl_vars['code']->value;?>
';
        var ajaxUrl     = 'form_advertising.php';

        $(document).ready(function() {
            // Default value of radio input
            if(action == 'EDIT') {
                $('input[name="avs_status"][value="<?php echo $_smarty_tpl->tpl_vars['values']->value['avs_status'];?>
"]').click();
            }

            uploadImageInput({
                area: $('#avs_img'),
                input: $('input[name="avs_img"]'),
                selector: $('#avs_img_file'),
                defaultValue: '<?php if ($_smarty_tpl->tpl_vars['values']->value['avs_img']) {?>../img/advertising/<?php echo $_smarty_tpl->tpl_vars['values']->value['avs_img'];?>
?rand=<?php echo $_smarty_tpl->tpl_vars['randNum']->value;?>
<?php }?>'
            });
        });
    </script>
    
</head>
<body>
                 
<?php echo $_smarty_tpl->getSubTemplate ("form_table_header.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array(), 0);?>

<div class="ftb-body">
    <?php if ($_smarty_tpl->tpl_vars['action']->value=='VIEW_DETAIL') {?>
    <!-- VIEW_DETAIL -->
    <div class="table-view-detail-image full">
    <img src="<?php if ($_smarty_tpl->tpl_vars['values']->value['avs_img']!='-') {?>../img/advertising/<?php echo $_smarty_tpl->tpl_vars['values']->value['avs_img'];?>
?rand=<?php echo $_smarty_tpl->tpl_vars['randNum']->value;?>
<?php } else { ?>../img/backoffice/no-pic.png<?php }?>">
    </div>
    <table class="table-view-detail">
        <tbody>
            <tr>
                <td>รหัสการประชาสัมพันธ์ :</td>
                <td><?php echo $_smarty_tpl->tpl_vars['code']->value;?>
</td>
            </tr>
            <tr>
                <td>ชื่อการประชาสัมพันธ์ :</td>
                <td><?php echo $_smarty_tpl->tpl_vars['values']->value['avs_name'];?>
</td>
            </tr>
            <tr>
                <td>สถานะการประชาสัมพันธ์ :</td>
                <td><?php if ($_smarty_tpl->tpl_vars['values']->value['avs_status']==1) {?>แสดงการประชาสัมพันธ์<?php } elseif ($_smarty_tpl->tpl_vars['values']->value['avs_status']==0) {?>ไม่แสดงการประชาสัมพันธ์<?php } else { ?>-<?php }?></td>
            </tr>
            <tr>
                <td>ข้อความ :</td>
                <td><?php echo $_smarty_tpl->tpl_vars['values']->value['avs_txt'];?>
</td>
            </tr>
        </tbody>
    </table>
    <?php } else { ?>      
    <!-- ADD, EDIT -->                  
    <form id="form-table" name="form-table" onsubmit="return false;">
    <input type="hidden" name="requiredFields" value="avs_name,avs_status">
    <table class="mbk-form-input-normal" cellpadding="0" cellspacing="0">
        <tbody>
            <tr>
                <td colspan="2">
                    <label class="input-required">ชื่อการประชาสัมพันธ์</label>
                    <input id="avs_name" name="avs_name" type="text" class="form-input full" value="<?php echo $_smarty_tpl->tpl_vars['values']->value['avs_name'];?>
" requrie>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <span id="err-avs_name-require" class="errInputMsg err-avs_name">โปรดป้อนชื่อการประชาสัมพันธ์</span>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <label class="input-required">สถานะการประชาสัมพันธ์</label>
                    <label style="display: inline-block;">
                        <input id="avs_status_show" type="radio" name="avs_status" value="1" checked> แสดง
                    </label> &nbsp;
                    <label style="display: inline-block;">
                        <input id="avs_status_unshow" type="radio" name="avs_status" value="0"> ไม่แสดง
                    </label>
                    <br><br>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <span id="err-avs_status-require" class="errInputMsg err-avs_status">โปรดเลือกสถานะการประชาสัมพันธ์</span>
                </td>
            </tr>
            <tr>
                <td>
                    <label>ข้อความประชาสัมพันธ์</label>
                    <textarea id="avs_txt" name="avs_txt" class="form-input full" rows="6"><?php echo $_smarty_tpl->tpl_vars['values']->value['avs_txt'];?>
</textarea>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <label>รูปภาพประชาสัมพันธ์</label>
                    <div id="avs_img" class="uploadImageArea full"></div>
                    <input type="hidden" name="avs_img" value="<?php echo $_smarty_tpl->tpl_vars['values']->value['avs_img'];?>
">
                </td>
            </tr>
        </tbody>
    </table>
    
    </form>
    <form method="post" enctype="multipart/form-data">
        <input id="avs_img_file" type="file" name="imageFile" class="uploadImageSelector" multiple="multiple">
    </form>
    <?php }?>
</div>
</body>
</html>
<!--
    [Note]
    1. ให้ใส่ field ที่ต้องการเช็คใน input[name="requiredFields"] โดยกำหนดชื่อฟิลด์ลงใน value หากมีมากกว่า 1 field ให้คั่นด้วยเครื่องหมาย คอมม่า (,) และห้ามมีช่องว่าง เช่น value="name,surname,address" เป็นต้น
    2. input จะต้องกำหนด id, name ให้ตรงกับชื่อฟิลด์ของตารางนั้นๆ และกำหนด value ให้มีรูปแบบ value="$values.ชื่อฟิลด์"
--><?php }} ?>
