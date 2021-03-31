<?php /* Smarty version Smarty-3.1.11, created on 2021-01-06 12:36:55
         compiled from "templates_erp\unidades.tpl" */ ?>
<?php /*%%SmartyHeaderCode:14059248605ff5a0d7529654-81029057%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'ada073ed41d435a71e1e92be59dcd3cd61512766' => 
    array (
      0 => 'templates_erp\\unidades.tpl',
      1 => 1607358357,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '14059248605ff5a0d7529654-81029057',
  'function' => 
  array (
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.11',
  'unifunc' => 'content_5ff5a0d7568976_05655426',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_5ff5a0d7568976_05655426')) {function content_5ff5a0d7568976_05655426($_smarty_tpl) {?><?php echo $_smarty_tpl->getSubTemplate (((string)@TEMPLATES_DIR)."html_conf.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>

<?php echo $_smarty_tpl->getSubTemplate (((string)@TEMPLATES_DIR)."cabecalho.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>

<div id="frame" style="width: 100%; height: 700px">
<form name="frm_unidades" id="frm_unidades" action="<?php echo $_SERVER['PHP_SELF'];?>
" method="POST" style="margin:0px; padding:0px;">
	<table width="100%" border="0">        
        <tr>
          <td width="122" rowspan="2" valign="top" class="espacamento">
		  <table width="100%" cellpadding="0" cellspacing="0">
				<tr>
					<td valign="middle">
						<input name="btninserir" type="button" class="class_botao" id="btninserir" onclick="xajax_insere(xajax.getFormValues('frm_unidades'));" value="Inserir" />					</td>
				</tr>
				<tr>
					<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onclick="history.back();" /></td>
				</tr>
                <input type="hidden" name="id_unidade" id="id_unidade" value="">
			</table></td>
        </tr>        
        <tr>
          <td colspan="2" valign="top" class="espacamento">
          <table border="0" width="100%">
				<tr>
					<td width="12%"><label for="abreviacao" class="labels">Abreviação</label><br />
							<input name="abreviacao" type="text" class="caixa" placeholder="Abreviacao" id="abreviacao" size="15" /></td>
					<td width="88%"><label for="unidade" class="labels">Unidade</label><br />
							<input name="unidade" type="text" class="caixa" placeholder="Unidade" id="unidade" size="50" /></td>
				</tr>
			</table>
          	<table border="0" width="100%">
				<tr>
					<td width="11%"><label for="busca" class="labels">Busca</label><br />
                    <input name="busca" type="text" class="caixa" placeholder="Busca" id="busca" onkeyup="iniciaBusca.verifica(this);" size="30" />
                    </td>
				</tr>
			</table></td>
        </tr>
      </table>
    <div id="unidades" style="width:100%;"> </div>
</form>
</div>
<?php echo $_smarty_tpl->getSubTemplate (((string)@TEMPLATES_DIR)."footer_root.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>
<?php }} ?>