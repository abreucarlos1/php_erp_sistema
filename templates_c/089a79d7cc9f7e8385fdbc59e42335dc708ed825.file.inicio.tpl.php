<?php /* Smarty version Smarty-3.1.11, created on 2021-01-07 17:19:38
         compiled from "templates_erp\inicio.tpl" */ ?>
<?php /*%%SmartyHeaderCode:2191493575ff7349a97b684-20304420%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '089a79d7cc9f7e8385fdbc59e42335dc708ed825' => 
    array (
      0 => 'templates_erp\\inicio.tpl',
      1 => 1607358191,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '2191493575ff7349a97b684-20304420',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'preenchido' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.11',
  'unifunc' => 'content_5ff7349a9c2088_71379937',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_5ff7349a9c2088_71379937')) {function content_5ff7349a9c2088_71379937($_smarty_tpl) {?><?php echo $_smarty_tpl->getSubTemplate ("html_conf.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>

<?php echo $_smarty_tpl->getSubTemplate ("header_inicio.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>

<form name="frm_tela" id="frm_tela" style="margin-top:5px; padding:0px;" action="<?php echo $_SERVER['PHP_SELF'];?>
" method="POST">
    <input type="hidden" name="preenchido" id="preenchido" value="<?php echo $_smarty_tpl->tpl_vars['preenchido']->value;?>
" />
    <div id="frame" style="width:100%; margin:0px; padding:0px;"> </div>    
</form>
<?php echo $_smarty_tpl->getSubTemplate ("footer_root.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>

<?php }} ?>