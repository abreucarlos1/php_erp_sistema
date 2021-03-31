<?php /* Smarty version Smarty-3.1.11, created on 2021-01-07 17:20:11
         compiled from "C:\Developer\XAMPP\htdocs\erp_sistema\templates_erp\menu.tpl" */ ?>
<?php /*%%SmartyHeaderCode:3795444315ff734bb191e26-57840433%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '38937849d64f19d2bbaa9c6389764df2fb55f3b0' => 
    array (
      0 => 'C:\\Developer\\XAMPP\\htdocs\\erp_sistema\\templates_erp\\menu.tpl',
      1 => 1607358182,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '3795444315ff734bb191e26-57840433',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'erros' => 0,
    'err' => 0,
    'botao' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.11',
  'unifunc' => 'content_5ff734bb1ef6c2_88168243',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_5ff734bb1ef6c2_88168243')) {function content_5ff734bb1ef6c2_88168243($_smarty_tpl) {?><?php echo $_smarty_tpl->getSubTemplate (((string)@TEMPLATES_DIR)."html_conf.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>

<?php echo $_smarty_tpl->getSubTemplate (((string)@TEMPLATES_DIR)."cabecalho.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>

<?php if (isset($_smarty_tpl->tpl_vars['erros']->value)){?>
<?php  $_smarty_tpl->tpl_vars['err'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['err']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['erros']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['err']->key => $_smarty_tpl->tpl_vars['err']->value){
$_smarty_tpl->tpl_vars['err']->_loop = true;
?>
    <h2 style="color:red;"><?php echo $_smarty_tpl->tpl_vars['err']->value['mensagem'];?>
</h2>
    <?php } ?>
<?php }?>
<div id="frame" style="width:100%; height:690px;">
<form name="frm" id="frm"  method="POST" style="margin-top:5px; padding:0px;" action="">

    <table width="100%" border="0">               
        <tr>
            <td width="116" valign="top" class="espacamento">
                <table width="100%">
                    <tr>
                        <td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="<?php echo $_smarty_tpl->tpl_vars['botao']->value[2];?>
" onclick="history.back();" /></td>
                    </tr>
                </table>
            </td>
            <td colspan="2" valign="top" class="espacamento"><div id="tela" style="width:100%;"> </div></td>
        </tr>
      </table>

</form>
</div>
<?php echo $_smarty_tpl->getSubTemplate (((string)@TEMPLATES_DIR)."footer_root.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>
<?php }} ?>