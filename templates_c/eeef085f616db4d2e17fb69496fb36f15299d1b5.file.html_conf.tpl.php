<?php /* Smarty version Smarty-3.1.11, created on 2021-03-29 16:00:07
         compiled from "templates_erp\html_conf.tpl" */ ?>
<?php /*%%SmartyHeaderCode:18925439136061dd67d70025-00013330%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'eeef085f616db4d2e17fb69496fb36f15299d1b5' => 
    array (
      0 => 'templates_erp\\html_conf.tpl',
      1 => 1607352441,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '18925439136061dd67d70025-00013330',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'xajax_javascript' => 0,
    'campo' => 0,
    'versao' => 0,
    'classe' => 0,
    'body_onload' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.11',
  'unifunc' => 'content_6061dd67d9f106_83914665',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_6061dd67d9f106_83914665')) {function content_6061dd67d9f106_83914665($_smarty_tpl) {?><!-- -->
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge"/>
<meta charset="utf-8>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="cache-control" content="max-age=0">
<meta http-equiv="cache-control" content="no-cache, must-revalidate">
<meta http-equiv="Expires" content="0">

	<?php echo $_smarty_tpl->tpl_vars['xajax_javascript']->value;?>


<title>::.. Empresa X - ERP  - <?php echo $_smarty_tpl->tpl_vars['campo']->value[1];?>
 - <?php echo $_smarty_tpl->tpl_vars['versao']->value;?>
  ..::</title>

<link rel="stylesheet" href="<?php echo @ROOT_WEB;?>
/includes/dhtmlx_403/codebase/dhtmlx.css">

<link rel="stylesheet" href="<?php echo $_smarty_tpl->tpl_vars['classe']->value;?>
">

<!-- <link rel="shortcut icon" href="favicon.ico" > -->

<script src="<?php echo @ROOT_WEB;?>
/includes/utils.js"></script>
</head>

<body onload="<?php echo $_smarty_tpl->tpl_vars['body_onload']->value;?>
"><?php }} ?>