<?php /* Smarty version Smarty-3.1.11, created on 2021-03-29 16:00:07
         compiled from "templates_erp\header_index.tpl" */ ?>
<?php /*%%SmartyHeaderCode:98986656061dd67e7d215-28615143%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '31e1d687636f7648977bb2a5f757e2cd7837f9ac' => 
    array (
      0 => 'templates_erp\\header_index.tpl',
      1 => 1607358225,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '98986656061dd67e7d215-28615143',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'campo' => 0,
    'versao' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.11',
  'unifunc' => 'content_6061dd67e7fa04_24404456',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_6061dd67e7fa04_24404456')) {function content_6061dd67e7fa04_24404456($_smarty_tpl) {?><div id="div_tudo" style="position:absolute; left:50%; top:50%; margin-left:-180px; margin-top:-190px;">
	<div class="div_login">
        <div class="header" align="center">
        	<img align="middle" src="<?php echo @DIR_IMAGENS;?>
logo_erp.png" width="302" height="70">            
        </div>        
        <div class="nome_formulario"><?php echo $_smarty_tpl->tpl_vars['campo']->value[1];?>
 - <?php echo $_smarty_tpl->tpl_vars['versao']->value;?>
</div>
<?php }} ?>