<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width: 100%; height: 700px">
<form name="frm" id="frm" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST">
	<table width="100%" border="0">        
                
        <tr>
        	<td width="116" valign="top" class="espacamento">
        		<table width="100%" border="0">
        			<tr>
        				<td valign="middle">
        					<input name="btninserir" type="button" class="class_botao" id="btninserir"  onclick="xajax_atualizar(xajax.getFormValues('frm'))" disabled="disabled" value="<smarty>$botao[1]</smarty>" /></td>
					</tr>
        			<tr>
        				<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="<smarty>$botao[2]</smarty>" onclick="history.back();" />
                        <input name="id_requisicao_despesa" type="hidden" id="id_requisicao_despesa" value="">
                      </td>
					</tr>
   			  </table>
      		</td>
        	<td colspan="2" rowspan="2" valign="top">            
                <div id="my_tabbar" style="height:400px;">    
    				<div id="a0">
                       <div id="filtros">
                       		<table width="100%">
		                       <tr>
		        				<td valign="middle"><label for="status" class="labels">STATUS</label><br />
		                           <select name="status" class="caixa" id="status" onchange="xajax_atualizatabela(xajax.getFormValues('frm'));">
		                              <option value="0">REQUISITADOS</option>
		                              <option value="1">ADIANTAMENTO</option>
		                              <option value="2">DESPESAS</option>
		                              <option value="3">ACERTADOS</option>
		                              <option value="4">REJEITADOS</option>
		                            </select>
		                        </td>
							</tr>
							<tr>
								<td>
									<label for="os" class="labels">PROJETOS</label><br />
									<select class="caixa" name="os" id="os" onchange="xajax_atualizatabela(xajax.getFormValues('frm'));">
										<smarty>html_options values=$option_os_values output=$option_os_output</smarty>
									</select>
								</td>
							</tr>
							<tr>
								<td>
									<label for="os" class="labels">FUNCIONÁRIOS</label><br />
									<select class="caixa" name="funcionario" id="funcionario" onchange="xajax_atualizatabela(xajax.getFormValues('frm'));">
										<smarty>html_options values=$option_func_values output=$option_func_output</smarty>
									</select>
								</td>
							</tr>
							<tr>
								<td>
									<label for="data_adiantamento" class="labels">DATA ADIANTAMENTO</label><br />
									<input type="text" class="caixa" name="data_adiantamento" id="data_adiantamento" onKeyPress="transformaData(this, event);" onBlur="verificaDataErro(this.value, 'data');xajax_atualizatabela(xajax.getFormValues('frm'));" size="10" />
								</td>
							</tr>
							</table>
                       </div>
                    </div>
                    
                    <div id="a1">
                       <div id="necessidades">&nbsp;</div>
                       <div id="itens_nec">&nbsp;</div>
                    </div>
                    
                    <div id="a2">
                       <div id="funcionarios">&nbsp;</div>
                    </div>
                    
                    <div id="a3">
                       <div id="dv_acerto_despesas">&nbsp;</div>
                       <div id="div_acerto">&nbsp;</div>
                       <div id="div_button">&nbsp;</div>
                    </div>                    
                </div>
          </td>
        </tr>
        <tr>
        	<td valign="bottom">
   			  <input name="btnexcluir_selecionados" id="btnexcluir_selecionados" class="class_botao" value="Excluir Selecionados" disabled="disabled" style="width:auto;" onclick="excluir_itens_selecionados();" type="button" />
   			</td>
        </tr>
      </table>      
	  <div id="adiantamento_despesas" style="width:100%;"></div>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>