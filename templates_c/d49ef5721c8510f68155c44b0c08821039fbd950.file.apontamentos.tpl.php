<?php /* Smarty version Smarty-3.1.11, created on 2021-01-06 15:27:48
         compiled from "templates_erp\apontamentos.tpl" */ ?>
<?php /*%%SmartyHeaderCode:8637230055ff5c8e40020b1-62225619%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'd49ef5721c8510f68155c44b0c08821039fbd950' => 
    array (
      0 => 'templates_erp\\apontamentos.tpl',
      1 => 1609935131,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '8637230055ff5c8e40020b1-62225619',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'botao' => 0,
    'option_periodo_values' => 0,
    'mesano' => 0,
    'option_periodo_output' => 0,
    'campo' => 0,
    'nome_funcionario' => 0,
    'externo' => 0,
    'cod_funcionario' => 0,
    'option_values' => 0,
    'option_output' => 0,
    'style' => 0,
    'option_local_values' => 0,
    'option_local_output' => 0,
    'option_orcamento_values' => 0,
    'option_orcamento_output' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.11',
  'unifunc' => 'content_5ff5c8e41c3353_35309642',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_5ff5c8e41c3353_35309642')) {function content_5ff5c8e41c3353_35309642($_smarty_tpl) {?><?php if (!is_callable('smarty_function_html_options')) include 'C:\\Developer\\XAMPP\\htdocs\\erp_sistema\\includes\\smarty\\libs\\plugins\\function.html_options.php';
if (!is_callable('smarty_modifier_date_format')) include 'C:\\Developer\\XAMPP\\htdocs\\erp_sistema\\includes\\smarty\\libs\\plugins\\modifier.date_format.php';
?><?php echo $_smarty_tpl->getSubTemplate (((string)@TEMPLATES_DIR)."html_conf.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>

<?php echo $_smarty_tpl->getSubTemplate (((string)@TEMPLATES_DIR)."cabecalho.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>

<div id="frame" style="width:100%; height:700px;">
<form name="frm" id="frm" method="POST" action="<?php echo $_SERVER['PHP_SELF'];?>
">
	<table width="100%" border="0">               
        <tr>
        	<td width="116" valign="top" class="espacamento">
        		<table border="0" width="100%">
        			<tr>
        				<td valign="middle">
        					<input name="btninserir" type="button" class="class_botao" id="btninserir" disabled="disabled" onclick="inserir_banco();" value="<?php echo $_smarty_tpl->tpl_vars['botao']->value[1];?>
"  /></td>
					</tr>
        			<tr>
        				<td valign="middle">
        					<input name="btnimprimir" type="button" class="class_botao" id="btnimprimir" onclick="popupUp();" value="<?php echo $_smarty_tpl->tpl_vars['botao']->value[5];?>
" /></td>
					</tr>
        			<tr>
        				<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="<?php echo $_smarty_tpl->tpl_vars['botao']->value[2];?>
" onclick="history.back();" /></td>
					</tr>
        			<tr>
        			  <td valign="middle"><label for="periodo" class="labels">Período</label><br />
                      <select name="periodo" class="caixa" id="periodo" onkeypress="return keySort(this);" onchange="xajax_atualizatabela(xajax.getFormValues('frm'));">
						<?php echo smarty_function_html_options(array('values'=>$_smarty_tpl->tpl_vars['option_periodo_values']->value,'selected'=>$_smarty_tpl->tpl_vars['mesano']->value,'output'=>$_smarty_tpl->tpl_vars['option_periodo_output']->value),$_smarty_tpl);?>

		            </select>
                      </td>
      			  </tr>
       			</table>
		  </td>
        	<td colspan="2" valign="top" class="espacamento">
            <table border="0" width="100%">
                <tr>
                  <td><label class="labels"><?php echo $_smarty_tpl->tpl_vars['campo']->value[2];?>
  <span style="font-size:12px; font-weight:bold;">
					<?php echo $_smarty_tpl->tpl_vars['nome_funcionario']->value;?>

	              </span></label></td>
              </tr>
              </table>
		  	<table border="0" width="100%">
				<tr>
					<td width="10%">
                    		<label for="data" class="labels">Data (*)</label><br />
							<input name="data" type="text" class="caixa" id="data" size="10" value="<?php echo smarty_modifier_date_format(time(),'%d/%m/%Y');?>
" onkeypress="transformaData(this, event);"  onblur="if(verificaDataErro(this.value)){xajax_periodos(xajax.getFormValues('frm'));xajax_saldo_catraca(xajax.getFormValues('frm'));}else{this.value='';}" /> 
                            <input type="hidden" name="externo" id="externo" value="<?php echo $_smarty_tpl->tpl_vars['externo']->value;?>
" />
                            <input type="hidden" name="codfuncionario" id="codfuncionario" value="<?php echo $_smarty_tpl->tpl_vars['cod_funcionario']->value;?>
" />
							<input type="hidden" name="id_horas" id="id_horas" value="" />
                        	<input type="hidden" name="preenchido" id="preenchido" value="1" />
                        </td>
					<td width="90%"><label for="os" class="labels">OS (*)</label><br />
					<select name="os" class="caixa" id="os" onChange="xajax_tarefas(xajax.getFormValues('frm'));liberaCampoProjeto(this.value);" onkeypress="return keySort(this);">
						<?php echo smarty_function_html_options(array('values'=>$_smarty_tpl->tpl_vars['option_values']->value,'output'=>$_smarty_tpl->tpl_vars['option_output']->value),$_smarty_tpl);?>

		            </select>
                      </td>
					</tr>
			</table>
		  <table border="0" width="100%">
		    <tr>
		      <td width="23%"><label for="disciplina" class="labels">Disciplina (*)</label><br />
                      <select name="disciplina" class="caixa" id="disciplina" onfocus="xajax_calcula_horas(xajax.getFormValues('frm'));" onchange="xajax_periodos(xajax.getFormValues('frm'));xajax_saldo_catraca(xajax.getFormValues('frm'));saldo_horas.focus();" onblur="xajax_calcula_horas(xajax.getFormValues('frm'));" onkeypress="return keySort(this);" >
                          <option value="">ESCOLHA A TAREFA</option>
              
                      </select>
              </td>
               <td>
					<label class="labels">Interna</label><input type="radio" name="rdoInternoExterno" id="rdoInterno" value="0" />
					<label class="labels">Externa</label><input type="radio" name="rdoInternoExterno" id="rdoExterno" value="1" />
               </td>
               <!--
		      <td>             
                  <div id="div_justificativa" style="visibility:hidden">
                  <label for="justificativa" class="labels">Justificativa*</label>	
                    <input name="justificativa" type="text" class="caixa" id="justificativa" value="" size="80" maxlength="150" onblur="xajax_periodos(xajax.getFormValues('frm'));">
                  </div>             
              </td>
              -->
	        </tr>
		    </table>
		  <table border="0" width="100%">
		    <tr>
		      <td width="10%"><label for="saldo_horas" class="labels"><?php echo $_smarty_tpl->tpl_vars['campo']->value[6];?>
</label><br />
              	<input name="saldo_horas" type="text" id="saldo_horas" value="0" size="10" readonly="readonly" class="caixa" /></td>
 		      <td width="10%"><label for="saldo_disciplina" class="labels">Saldo tarefa</label><br />
                <input name="saldo_disciplina" type="text" id="saldo_disciplina" value="0" size="10" readonly="readonly" class="caixa" /></td>
              <td width="10%"><label for="horas_apontadas" class="labels"><?php echo $_smarty_tpl->tpl_vars['campo']->value[7];?>
</label><br />
                <input name="horas_apontadas" type="text"  class="caixa" id="horas_apontadas" value="0" size="10" readonly="readonly" /></td>
		      <td width="70%"><label for="horas_aprovadas" class="labels"><?php echo $_smarty_tpl->tpl_vars['campo']->value[8];?>
</label><br />
              	<input name="horas_aprovadas" type="text"  class="caixa" id="horas_aprovadas" value="0" size="10" readonly="readonly" /></td>
	        </tr>
		    </table>
            <table width="100%" border="0">
              <tr>
                <td width="10%"><label class="labels"><?php echo $_smarty_tpl->tpl_vars['campo']->value[9];?>
*</label><br />
                    <div id="inicial"> </div></td>
                <td width="11%"><label class="labels"><?php echo $_smarty_tpl->tpl_vars['campo']->value[10];?>
*</label><br />
                    <div id="final"> </div></td>
                <td width="11%"><label for="qtd_horas" class="labels"><?php echo $_smarty_tpl->tpl_vars['campo']->value[11];?>
</label><br />
               		<input name="qtd_horas" type="text" class="caixa" id="qtd_horas" value="0" size="5" maxlength="20" disabled="disabled">
                </td>               
                <td width="68%"><label for="horas_disp" id="lb_hrs_disp" class="labels" style="visibility:inline"><?php echo $_smarty_tpl->tpl_vars['campo']->value[12];?>
</label><br />
                	<input name="horas_disp" type="text"  class="caixa" id="horas_disp" value="0" size="10" readonly="readonly" style="visibility:inline" />
                </td>                
                <!--
                <td width="8%" class="td_sp"><label class="labels">
		        <?php echo $_smarty_tpl->tpl_vars['campo']->value[13];?>
</label>
              <input type="checkbox" name="retrabalho" id="retrabalho" title="horas de retrabalho" value="1" /></td>
              -->
              </tr>
            </table> 
            <div id="local_trabalho" style="<?php echo $_smarty_tpl->tpl_vars['style']->value;?>
">
			    <table width="100%" border="0">
                  <tr>
                    <td width="1%"><label for="local" class="labels"><?php echo $_smarty_tpl->tpl_vars['campo']->value[14];?>
</label><br />					  
					  <select name="local" class="caixa" id="local" onkeypress="return keySort(this);">
						<?php echo smarty_function_html_options(array('values'=>$_smarty_tpl->tpl_vars['option_local_values']->value,'output'=>$_smarty_tpl->tpl_vars['option_local_output']->value),$_smarty_tpl);?>

		            </select> 
                    </td>
                  </tr>
                </table>
            </div>
            <table border="0" width="100%">
              <tr>
                <td width="5%"><label class="labels">Complemento/Tarefa</label><br />
                	<!-- <input name="complemento" type="hidden" class="caixa" id="textarea" value="" maxlength="150"-->
                	<div id="txtAutocomplete"> </div>
              </tr>
              <tr>
                <td id="tdOrcamento" style="display:none;"><label for="orcamento" class="labels">Projeto</label><br />
					<select name="orcamento" class="caixa" id="orcamento" onkeypress="return keySort(this);">
						<?php echo smarty_function_html_options(array('values'=>$_smarty_tpl->tpl_vars['option_orcamento_values']->value,'output'=>$_smarty_tpl->tpl_vars['option_orcamento_output']->value),$_smarty_tpl);?>

	            	</select>
				</td>
              </tr>
          </table>
           </td>
        </tr>
      </table>
	  <!-- <div id="controlehoras" style="scrollbar-face-color : #AAAAAA; scrollbar-highlight-color : #AAAAAA; scrollbar-3dlight-color : #ffffff; scrollbar-shadow-color : #FFFFFF; scrollbar-darkshadow-color : #FFFFFF; scrollbar-track-color : #FFFFFF; scrollbar-arrow-color : #FFFFFF;"> </div> -->
      <div id="controlehoras" style="width:100%"> </div>
</form>
</div>
<?php echo $_smarty_tpl->getSubTemplate (((string)@TEMPLATES_DIR)."footer_root.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>
<?php }} ?>