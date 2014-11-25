<?php
// put full path to Smarty.class.php

require('Smarty-3.1.18/libs/Smarty.class.php');

$smarty = new Smarty();

$smarty->setTemplateDir('template');

//$smarty->setCompileDir('template_c');

$smarty->assign('website', 'Thaicoding.net');

$smarty->display('index.tpl');

?>