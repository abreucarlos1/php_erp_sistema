<?php /* Smarty version Smarty-3.1.11, created on 2021-01-06 15:29:32
         compiled from "templates_erp\proposta_valorizacao.tpl" */ ?>
<?php /*%%SmartyHeaderCode:4430800835ff5c94c33d841-69831602%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '8fdcd2b15a5e6d3f65fc800f5e53bf16754af706' => 
    array (
      0 => 'templates_erp\\proposta_valorizacao.tpl',
      1 => 1607358356,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '4430800835ff5c94c33d841-69831602',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'option_status_values' => 0,
    'option_status_output' => 0,
    'option_regiao_values' => 0,
    'option_regiao_output' => 0,
    'selecionado' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.11',
  'unifunc' => 'content_5ff5c94c447e04_33107851',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_5ff5c94c447e04_33107851')) {function content_5ff5c94c447e04_33107851($_smarty_tpl) {?><?php if (!is_callable('smarty_function_html_options')) include 'C:\\Developer\\XAMPP\\htdocs\\erp_sistema\\includes\\smarty\\libs\\plugins\\function.html_options.php';
?><?php echo $_smarty_tpl->getSubTemplate (((string)@TEMPLATES_DIR)."html_conf.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>

<?php echo $_smarty_tpl->getSubTemplate (((string)@TEMPLATES_DIR)."cabecalho.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>

<script src="../includes/jquery/jquery.min.js"></script>
<script src="../includes/jquery/jquery-ui-1.11.1/jquery-ui.min.js"></script>
<style>
	div.gridbox table.obj tr td {
	
	cursor: pointer;
}
</style>
<div id="frame" style="width:100%; height:700px;">
<form name="frm" id="frm" action="<?php echo $_SERVER['PHP_SELF'];?>
" method="POST">
<table width="100%" border="0">               
        <tr>
        	<td width="116" valign="top" class="espacamento">
        		<table width="100%" border="0">
        			<tr>
        				<td valign="middle">
                        <input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onclick="history.back();" />
                    	</td>
                   	</tr>
       			</table>
			  <table width="100%" border="0">
              <tr>
                <td width="23%"><label for="status" class="labels">Status</label><br />
                  <select name="status" class="caixa" id="status" onkeypress="return keySort(this);" onchange="xajax_atualizatabela(xajax.getFormValues('frm'));">
                    <?php echo smarty_function_html_options(array('values'=>$_smarty_tpl->tpl_vars['option_status_values']->value,'output'=>$_smarty_tpl->tpl_vars['option_status_output']->value,'selected'=>1),$_smarty_tpl);?>

                </select>
                </td>
              </tr>
            </table>
			<table border="0" width="100%">
				<tr>
					<td><label for="busca" class="labels">Buscar</label><br />
                    <input name="busca" type="text" class="caixa" id="busca" placeholder="Busca" /><br />
                    <input name="btnbuscar" id="btnbuscar" type="button" class="class_botao" value="Buscar" onclick="xajax_atualizatabela(xajax.getFormValues('frm'),true);" />                    
                    </td>
				</tr>
			</table>
		  </td>
        	<td colspan="2" valign="top" class="espacamento">           
            <table width="100%" border="0">
              <tr>
                <td width="19%"><label class="labels">Proposta</label><br />
                <div class="labels" style="font-weight:bold" id="nr_proposta"> </div></td>
                <td width="81%"><label class="labels">Descrição</label><br />
                <div class="labels" style="font-weight:bold" id="descri_proposta"> </div></td>
                <input type="hidden" id="id_proposta" name="id_proposta" value="" />
                
                <input type="hidden" id="row_id" name="row_id" value="" />
                
                <input type="hidden" id="chk_del" name="chk_del[]" value="">
              </tr>
            </table>
                  <table width="100%">
                  	<tr>
                    	<td width="5%"><label for="regiao" class="labels">Região</label><br />
                              <select name="regiao" class="caixa" id="regiao" onkeypress="return keySort(this);" onchange="xajax_preenche_categorias(xajax.getFormValues('frm'));">
                                <?php echo smarty_function_html_options(array('values'=>$_smarty_tpl->tpl_vars['option_regiao_values']->value,'output'=>$_smarty_tpl->tpl_vars['option_regiao_output']->value,'selected'=>$_smarty_tpl->tpl_vars['selecionado']->value),$_smarty_tpl);?>

                            </select>
                        </td>
                    	<td width="9%"><label for="reajuste" class="labels">% Reajuste</label><br />
                            <input type="text" name="reajuste" id="reajuste" class="caixa" size="5" value="0" onkeypress="num_only();" />
                        </td>
                    	<td width="12%"><label for="contrato" class="labels">Contrato</label><br />
                              <select name="contrato" class="caixa" id="contrato" onkeypress="return keySort(this);" onchange="muda_contrato(this.value);xajax_preenche_categorias(xajax.getFormValues('frm'));xajax_preenche_guarda_chuva(xajax.getFormValues('frm'));xajax_preenche_guarda_chuva_dvm(xajax.getFormValues('frm'));">
                                <option value="1">PJ</option>
                                <option value="2">CLT-MÊS</option>
                                <option value="3">CLT-HORA</option>
                            </select>
                        </td>
                        
                      <td width="15%"><div id="div_per" style="visibility:hidden;"><input type="checkbox" name="periculosidade" id="periculosidade" value="1" onclick="xajax_preenche_categorias(xajax.getFormValues('frm'));xajax_preenche_guarda_chuva(xajax.getFormValues('frm'));xajax_preenche_guarda_chuva_dvm(xajax.getFormValues('frm'));" /><label class="labels">Periculosidade</label></div></td>
                      <td width="59%"><div id="div_ins" style="visibility:hidden;"><input type="checkbox" name="insalubridade" id="insalubridade" value="1" onclick="xajax_preenche_categorias(xajax.getFormValues('frm'));xajax_preenche_guarda_chuva(xajax.getFormValues('frm'));xajax_preenche_guarda_chuva_dvm(xajax.getFormValues('frm'));" /><label class="labels">Insalubridade</label></div></td>
                    </tr>
                  </table>
                  <table>
               	  <tr>
              <td width="10%"><label for="lucro_liq" class="labels">% Lucro liq.</label><br />
                            <input type="text" name="lucro_liq" id="lucro_liq" class="caixa" size="5" value="12,5" onkeypress="num_only();" onblur="xajax_escolha_margens(xajax.getFormValues('frm'));" />
                        </td>
                        
                        <td width="10%"><label class="labels">Margem aplicada</label><br />
                            <div id="div_margem"> </div>
                        </td>                        
                    	<td width="10%">
                            <input type="button" name="btn_aplicar" id="btn_aplicar" class="class_botao" style="width:70px;" value="Recalcular" disabled="disabled" onclick="xajax_preenche_categorias(xajax.getFormValues('frm'));xajax_preenche_guarda_chuva(xajax.getFormValues('frm'));" />
                        </td>
                    	<td width="60%">
                            <input type="button" name="btn_concluir" id="btn_concluir" class="class_botao" value="Concluir Valorização" disabled="disabled" onclick="xajax_concluir_proposta(xajax.getFormValues('frm'));" />
                        </td>     
                    <tr>
                  </table>
			                           
            </td>
        </tr>
</table>
<table width="100%" border="0">
	<tr>
    	<td valign="top" class="espacamento">
            <div id="my_tabbar" style="height:490px;">            
              <div id="a10">     
                <div id="div_dados_cliente"> </div>
              </div>
              
              <div id="a40">
                <div id="div_control_resumo" style="visibility:hidden"> 
                  <div id="div_resumo" style="width:99%"> </div>
                  <div id="barra_btn_quant"> </div>
                </div>
            </div>
            
              <div id="a50">
                <div id="div_control_categorias" style="visibility:hidden;">
                  <div id="div_categorias" style="width:99%"> </div>
             </div>
            </div>            
              <div id="a70">
                <div id="div_control_guarda_chuva_dvm" style="visibility:hidden;">
                  <div id="div_guarda_chuva_dvm" style="width:99%"> </div>
             </div>
             
               <div id="a80">
                <div id="div_control_adm_dvm" style="visibility:hidden;">
                  <div id="div_adm_dvm" style="width:99%"> </div>
             </div>
            </div>              
          </div>  
		</td>
	</tr>
</table>
</form>
</div>
<?php echo $_smarty_tpl->getSubTemplate (((string)@TEMPLATES_DIR)."footer_root.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>
<?php }} ?>