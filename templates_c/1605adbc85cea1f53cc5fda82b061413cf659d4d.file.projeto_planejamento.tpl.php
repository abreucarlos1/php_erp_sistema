<?php /* Smarty version Smarty-3.1.11, created on 2021-01-06 17:49:48
         compiled from "templates_erp\projeto_planejamento.tpl" */ ?>
<?php /*%%SmartyHeaderCode:6724626595ff5ea2c970d09-25953805%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '1605adbc85cea1f53cc5fda82b061413cf659d4d' => 
    array (
      0 => 'templates_erp\\projeto_planejamento.tpl',
      1 => 1607357985,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '6724626595ff5ea2c970d09-25953805',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'option_status_values' => 0,
    'option_status_output' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.11',
  'unifunc' => 'content_5ff5ea2ca8c193_02404792',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_5ff5ea2ca8c193_02404792')) {function content_5ff5ea2ca8c193_02404792($_smarty_tpl) {?><?php if (!is_callable('smarty_function_html_options')) include 'C:\\Developer\\XAMPP\\htdocs\\erp_sistema\\includes\\smarty\\libs\\plugins\\function.html_options.php';
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
                        <input name="btnimprimir" id="btnimprimir" type="button" class="class_botao" value="Relatório" disabled="disabled" onclick="if(document.getElementById('id_proposta').value!=0){imprimir()};" />
                    	</td>
                   	</tr>
        			<tr>
        				<td valign="middle">
                        <input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onclick="history.back();" />
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
			<table width="100%" border="0">
              <tr>
                <td width="23%"><label for="status" class="labels">Status</label><br />
                  <select name="status" class="caixa" id="status" onkeypress="return keySort(this);" onchange="xajax_atualizatabela(xajax.getFormValues('frm'));">
                    <?php echo smarty_function_html_options(array('values'=>$_smarty_tpl->tpl_vars['option_status_values']->value,'output'=>$_smarty_tpl->tpl_vars['option_status_output']->value,'selected'=>1),$_smarty_tpl);?>

                </select>
                </td>
              </tr>
            </table>                        
            </td>
        </tr>
        <tr>
          <td colspan="3" valign="top">
            <div id="my_tabbar" style="height:600px;">            
              <div id="a10">     
                <div id="div_dados_cliente"> </div>
              </div>
            
              <div id="a20">
                <div id="div_control_escopo_geral" style="visibility:hidden; display:none;">
                  <table width="100%">
                    <tr>
                      <td width="10%">
                          <label for="escopogeral" class="labels">Escopo Geral</label><br />           
                            <input name="escopogeral" type="text" class="caixa" id="escopogeral" placeholder="Escopo Geral" size="70">
                            <input name="h_escopogeral" id="h_escopogeral" type="hidden">
                      </td>
                      <td width="11%" valign="bottom"><input name="btn_escopo" type="button" id="btn_escopo" value="Inserir" class="class_botao" onclick="xajax_inc_escopogeral(xajax.getFormValues('frm'));"></td>            
                    </tr>
                  </table>                  
                  <div id="div_escopo_geral"> </div>
                </div>          
            </div>
              
              <div id="a30">
                <div id="div_control_escopo_detalhado" style="visibility:hidden">       
                  <table width="100%">
                    <tr>
                      <td width="10%"><label class="labels">Escopo Geral</label><br />
                        <div id="escop"> </div>
                      </td>
                      <td width="90%"><label class="labels">Disciplina</label><br />
                        <div id="div_disciplina"> </div>
                      </td>								
                    </tr>
                  </table>
                  <div id="div_escopo_detalhado" style="width:99%; "> </div>
                  <input name="btn_escopodet" type="button" id="btn_escopodet" value="Concluir" disabled="disabled" onclick="xajax_inc_escopodetalhado(xajax.getFormValues('frm',true));">
               </div>
            </div>
              
              <div id="a40">
                <div id="div_control_resumo" style="visibility:hidden"> 
                  <div id="div_resumo" style="width:99%"> </div>
                  <div id="barra_btn_quant"> </div>
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