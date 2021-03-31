<?php /* Smarty version Smarty-3.1.11, created on 2021-03-29 16:00:07
         compiled from "templates_erp\index.tpl" */ ?>
<?php /*%%SmartyHeaderCode:8931195736061dd67704328-75901668%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '675e45633b98dbe7600923823dced3ca063b3d94' => 
    array (
      0 => 'templates_erp\\index.tpl',
      1 => 1609870319,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '8931195736061dd67704328-75901668',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'pagina' => 0,
    'user' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.11',
  'unifunc' => 'content_6061dd679d0e93_42458977',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_6061dd679d0e93_42458977')) {function content_6061dd679d0e93_42458977($_smarty_tpl) {?><?php echo $_smarty_tpl->getSubTemplate ("html_conf.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>

<?php echo $_smarty_tpl->getSubTemplate ("header_index.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>

<form name="frm_login" id="frm_login" method="POST" action="<?php echo $_SERVER['PHP_SELF'];?>
">
    <input type="hidden" name="pagina" id="pagina" value="<?php echo $_smarty_tpl->tpl_vars['pagina']->value;?>
">
    <div class="fieldset">
        <label for="login" class="labels">Usuário</label><br />
        <input name="login" id="login" class="caixa" style="text-transform:none;" type="text" placeholder="login" value="<?php echo $_smarty_tpl->tpl_vars['user']->value;?>
" size="40"/><br />
        <label for="senha" class="labels">Senha</label><br />
        <input name="senha" id="senha" type="password" class="caixa" style="text-transform:none;" placeholder="Senha" onkeypress="if(event.keyCode==13){xajax_autenticacao(xajax.getFormValues('frm_login'));}" size="40" /><br />
        <div onclick="esqueceusenha()"><label class="esq_senha">Esqueci minha senha</label></div><br />
        <button type="button" autofocus class="class_botao" onclick="xajax_autenticacao(xajax.getFormValues('frm_login'));">Entrar</button><br />
        <div class="alerta_erro" id="mensagem"> </div>
    </div>        
</form>
<?php echo $_smarty_tpl->getSubTemplate ("footer_root.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>

<?php }} ?>