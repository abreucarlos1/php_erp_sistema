<?php /* Smarty version Smarty-3.1.11, created on 2021-01-06 15:25:09
         compiled from "templates_erp\alocacao_recursos.tpl" */ ?>
<?php /*%%SmartyHeaderCode:5832639765ff5c845c92469-56907606%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'd1363d54cad16d611fd7823568ec8ae12fb7dacc' => 
    array (
      0 => 'templates_erp\\alocacao_recursos.tpl',
      1 => 1609942135,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '5832639765ff5c845c92469-56907606',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'option_cliente_values' => 0,
    'option_cliente_output' => 0,
    'option_os_values' => 0,
    'option_os_output' => 0,
    'option_funcionarios_values' => 0,
    'option_funcionarios_output' => 0,
    'option_status_values' => 0,
    'option_status_output' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.11',
  'unifunc' => 'content_5ff5c84793bad1_36289090',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_5ff5c84793bad1_36289090')) {function content_5ff5c84793bad1_36289090($_smarty_tpl) {?><?php if (!is_callable('smarty_function_html_options')) include 'C:\\Developer\\XAMPP\\htdocs\\erp_sistema\\includes\\smarty\\libs\\plugins\\function.html_options.php';
?><?php echo $_smarty_tpl->getSubTemplate (((string)@TEMPLATES_DIR)."html_conf.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>

<?php echo $_smarty_tpl->getSubTemplate (((string)@TEMPLATES_DIR)."cabecalho.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>

<form name="frm" id="frm" action="<?php echo $_SERVER['PHP_SELF'];?>
" method="POST" style="margin:0px; padding:0px;">
	<table width="100%" border="0">        
        <tr>
          <td width="116" rowspan="3" valign="top" class="espacamento">
		  <table width="100%" border="0">
				<tr>
					<td valign="middle">
						<input name="btninserir" type="button" class="class_botao" id="btninserir" value="Inserir" onclick="xajax_insere(xajax.getFormValues('frm'));">
					</td>
				</tr>
				<tr>
					<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onclick="history.back();" /></td>
				</tr>
			</table></td>
        </tr>        
        <tr>
          <td colspan="2" valign="top" class="espacamento">
          <!--
		  <table width="100%" border="0">
				<tr>
					<td width="11%"><label for="os" class="labels">OS</label><br />
						<input name="os" type="text" class="caixa" id="os" size="14" disabled="disabled">
						<input name="id_os" type="hidden" id="id_os" value="" /></td>
					<td width="89%"><label for="oscliente" class="labels">OS - Cliente</label><br /> 
						<input name="oscliente" type="text" class="caixa" id="oscliente" size="19"></td>
				</tr>
            </table>
        -->
        <!--
		  <table width="100%" border="0">
				<tr>
					<td width="27%"><label for="descricao" class="labels">Descrição</label><br />
						<input name="descricao" type="text" class="caixa" id="descricao" size="100"></td>
				</tr>
            </table>
        -->
        <!--
          	<table width="100%" border="0">
				<tr>
					<td width="37%"><label for="titulo_1" class="labels">Título 1*</label><br />
						<input name="titulo_1" type="text" class="caixa" id="titulo_1" placeholder="Título 1" size="50" /></td>
					<td width="63%"><label for="titulo_2" class="labels">Título 2</label><br />
						<input name="titulo_2" type="text" class="caixa" id="titulo_2" placeholder="Título 2" size="50" /></td>
				</tr>
            </table>
        -->
        <!--
          	<table width="100%" border="0">
				<tr>
					<td width="6%"><label for="cliente" class="labels">Cliente</label><br />
						<select name="cliente" class="caixa" id="cliente" onchange="xajax_preencheCombo(this.options[this.selectedIndex].value, 'CONTATO','coordcli');" onkeypress="return keySort(this);">
						<?php echo smarty_function_html_options(array('values'=>$_smarty_tpl->tpl_vars['option_cliente_values']->value,'output'=>$_smarty_tpl->tpl_vars['option_cliente_output']->value),$_smarty_tpl);?>

						</select></td>
				</tr>
            </table>
        -->
          	<table width="100%" border="0">
				<tr>
					<td width="19%"><label for="os" class="labels">Ordem Serviço</label><br /> 
						<select name="os" class="caixa" id="os" onkeypress="return keySort(this);" onchange="xajax_atualizatabela(this.options[this.selectedIndex].value);">
						<?php echo smarty_function_html_options(array('values'=>$_smarty_tpl->tpl_vars['option_os_values']->value,'output'=>$_smarty_tpl->tpl_vars['option_os_output']->value),$_smarty_tpl);?>

                        </select>
                        <input name="id_os_x_funcionarios" type="hidden" id="id_os_x_funcionarios" value="" /></td>
                        </td>
					<td width="81%"><label for="funcionario" class="labels">Funcionário</label><br /> 
						<select name="funcionario" class="caixa" id="funcionario" onkeypress="return keySort(this);">
						<?php echo smarty_function_html_options(array('values'=>$_smarty_tpl->tpl_vars['option_funcionarios_values']->value,'output'=>$_smarty_tpl->tpl_vars['option_funcionarios_output']->value),$_smarty_tpl);?>

						</select></td>
				</tr>
            </table>
            <!--
          	<table width="100%" border="0">
				<tr>
					<td width="16%"><label for="coordcli" class="labels">Coordenador Cliente</label><br />
                    	<select name="coordcli" class="caixa" id="coordcli" onkeypress="return keySort(this);">
								<option value="0">SELECIONE</option>
						</select>
                     </td>
					<td width="8%"><label for="datainicio" class="labels">Data início</label><br />
						<input name="datainicio" type="text" class="caixa" id="datainicio" onKeyPress="return txtBoxFormat(document.frm_os, 'datainicio', '99/99/9999', event);" maxlength="10" size="12"></td>
					<td width="76%"><label for="datafim" class="labels">Data Final</label><br />
						<input name="datafim" type="text" class="caixa" id="datafim" onKeyPress="return txtBoxFormat(document.frm_os, 'datafim', '99/99/9999', event);" maxlength="10" size="12"></td>
				</tr>
            </table> 
            -->         	
          	<table border="0" width="100%">
				<tr>
					<td width="37%"><label for="busca" class="labels">Buscar</label><br />
                    <input name="busca" type="text" class="caixa" id="busca" placeholder="Busca" onkeyup="iniciaBusca.verifica(this);" size="50"></td>
                    <!--
					<td width="63%"><label for="exibir" class="labels">Exibir:</label><br />
						<select name="exibir" class="caixa" id="exibir" onkeypress="return keySort(this);" onchange="xajax_atualizatabela('',this.value);">
						<?php echo smarty_function_html_options(array('values'=>$_smarty_tpl->tpl_vars['option_status_values']->value,'output'=>$_smarty_tpl->tpl_vars['option_status_output']->value),$_smarty_tpl);?>

						</select>
                    </td>
                    -->
				</tr>
			</table></td>
        </tr>
      </table>
	  <div id="alocacao_recursos" style="width:100%;"> </div>
</form>
</div>
<?php echo $_smarty_tpl->getSubTemplate (((string)@TEMPLATES_DIR)."footer_root.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>
<?php }} ?>