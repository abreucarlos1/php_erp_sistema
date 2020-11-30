<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<script src="../includes/jquery/jquery.min.js"></script>
<script src="../includes/jquery/jquery-ui-1.11.1/jquery-ui.min.js"></script>
<style>
	div.gridbox table.obj tr td {
	
	cursor: pointer;
}
</style>
<div id="frame" style="width:100%; height:700px;">
<form name="frm" id="frm" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST">
<table width="100%" border="0">               
        <tr>
        	<td width="116" valign="top" class="espacamento">
        		<table width="100%" border="0">
        			<tr>
        				<td valign="middle">
                        <input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onClick="history.back();" />
                    	</td>
                   	</tr>
       			</table>
			  <table width="100%" border="0">
              <tr>
                <td width="23%"><label for="status" class="labels">Status</label><br />
                  <select name="status" class="caixa" id="status" onkeypress="return keySort(this);" onchange="xajax_atualizatabela(xajax.getFormValues('frm'));">
                    <smarty>html_options values=$option_status_values output=$option_status_output selected=1</smarty>
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
                <div class="labels" style="font-weight:bold" id="nr_proposta">&nbsp;</div></td>
                <td width="81%"><label class="labels">Descrição</label><br />
                <div class="labels" style="font-weight:bold" id="descri_proposta">&nbsp;</div></td>
                <input type="hidden" id="id_proposta" name="id_proposta" value="" />
                
                <input type="hidden" id="row_id" name="row_id" value="" />
                
                <input type="hidden" id="chk_del" name="chk_del[]" value="">
              </tr>
            </table>
                  <table width="100%">
                  	<tr>
                    	<td width="5%"><label for="regiao" class="labels">Região</label><br />
                              <select name="regiao" class="caixa" id="regiao" onkeypress="return keySort(this);" onchange="xajax_preenche_categorias(xajax.getFormValues('frm'));">
                                <smarty>html_options values=$option_regiao_values output=$option_regiao_output selected=$selecionado</smarty>
                            </select>
                        </td>
                    	<td width="9%"><label for="reajuste" class="labels">%&nbsp;Reajuste</label><br />
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
              <td width="10%"><label for="lucro_liq" class="labels">%&nbsp;Lucro&nbsp;liq.</label><br />
                            <input type="text" name="lucro_liq" id="lucro_liq" class="caixa" size="5" value="12,5" onkeypress="num_only();" onblur="xajax_escolha_margens(xajax.getFormValues('frm'));" />
                        </td>
                        
                        <td width="10%"><label class="labels">Margem&nbsp;aplicada</label><br />
                            <div id="div_margem">&nbsp;</div>
                        </td>                        
                    	<td width="10%">
                            <input type="button" name="btn_aplicar" id="btn_aplicar" class="class_botao" style="width:70px;" value="Recalcular" disabled="disabled" onclick="xajax_preenche_categorias(xajax.getFormValues('frm'));xajax_preenche_guarda_chuva(xajax.getFormValues('frm'));" />
                        </td>
                    	<td width="60%">
                            <input type="button" name="btn_concluir" id="btn_concluir" class="class_botao" value="Concluir&nbsp;Valorização" disabled="disabled" onclick="xajax_concluir_proposta(xajax.getFormValues('frm'));" />
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
                <div id="div_dados_cliente">&nbsp;</div>
              </div>
              
              <div id="a40">
                <div id="div_control_resumo" style="visibility:hidden"> 
                  <div id="div_resumo" style="width:99%">&nbsp;</div>
                  <div id="barra_btn_quant">&nbsp;</div>
                </div>
            </div>
            
              <div id="a50">
                <div id="div_control_categorias" style="visibility:hidden;">
                  <div id="div_categorias" style="width:99%">&nbsp;</div>
             </div>
            </div>            
              <div id="a70">
                <div id="div_control_guarda_chuva_dvm" style="visibility:hidden;">
                  <div id="div_guarda_chuva_dvm" style="width:99%">&nbsp;</div>
             </div>
             
               <div id="a80">
                <div id="div_control_adm_dvm" style="visibility:hidden;">
                  <div id="div_adm_dvm" style="width:99%">&nbsp;</div>
             </div>
            </div>              
          </div>  
		</td>
	</tr>
</table>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>