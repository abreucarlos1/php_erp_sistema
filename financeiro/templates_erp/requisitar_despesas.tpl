<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width:100%; height:700px;">
<form name="frm" id="frm" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST">
	<table width="100%" border="0">               
  		<tr>
        	<td width="116" valign="top" class="espacamento">
        		<table width="100%" border="0">
        			<tr>
        				<td valign="middle">
        					<input name="btninserir" type="button" class="class_botao" id="btninserir" onclick="javascript:if(inserir()){xajax_insere(xajax.getFormValues('frm'));};" disabled="disabled" value="<smarty>$botao[1]</smarty>" /></td>
					</tr>
        			<tr>
        				<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="<smarty>$botao[2]</smarty>" onclick="history.back();" />
                        <input name="id_requisicao_despesa" type="hidden" id="id_requisicao_despesa" value="">
                      </td>
					</tr>                  
                  <tr>
                  <td><label class="labels">Filtrar&nbsp;status</label><br />
                     <select name="status" class="caixa" id="status" onchange="xajax_atualizatabela(xajax.getFormValues('frm'));" onkeypress="return keySort(this);">
                        <option value="0" selected="selected">REQUISITADOS</option>
                        <option value="1">ADIANTAMENTO</option>
                        <option value="2">DESPESAS</option>
                        <option value="3">ACERTADOS</option>
                        <option value="4">REJEITADOS</option>
                      </select>
                  </td>
                  </tr>
   			  </table>
      	</td>
        	<td colspan="2" valign="top" class="espacamento">
              <div id="my_tabbar" style="height:450px;"> 
              	<div id="a1" style="overflow:scroll;">
             		 <div id="dv_dados">
						<table width="100%" border="0">
                            <tr>
                            	<td><div id="num_sol" class="labels">&nbsp;</div></td>
                              <td><label class="labels"><smarty>$campo[4]</smarty>&nbsp;<strong><div id="nome_func"><smarty>$nome_funcionario</smarty></div></strong></label></td>
                              </tr>
                        </table>
                        <table width="100%" border="0">
                            <tr>
                              <td width="10%"><label for="data" class="labels"><smarty>$campo[5]</smarty></label><br />
                                	<input name="data" type="text" class="caixa" id="data" size="12" value="<smarty>$smarty.now|date_format:'%d/%m/%Y'</smarty>" readonly="1" /></td>
                                <td width="90%"><label for="os" class="labels"><smarty>$campo[6]</smarty></label><br /> 
                                 	<select name="os" class="caixa" id="os" onchange="xajax_os_destino(this.value);xajax_despesas(xajax.getFormValues('frm'));" onkeypress="return keySort(this);">
                                    <smarty>html_options values=$option_os_values output=$option_os_output</smarty>
                                  </select>
                                </td>
                            </tr>
                            </table>
                            <div id="div_os_destino" style="display:none; z-index:15000;">
                        	<table width="100%" border="0">
                                <tr>
                                    <td><label for="os_destino" class="labels">OS&nbsp;destino</label><br /> 
										<div id="combo_os_destino">&nbsp;</div>
                                    </td>
                                </tr>
                            </table>
                            </div>
                            <table width="100%" border="0">
                              <tr>
                                <td width="41%"><label for="atividade" class="labels"><smarty>$campo[7]</smarty></label><br /> 
                                    <input name="atividade" type="text" class="caixa" id="atividade" placeholder="Atividade"  value="" size="50" maxlength="100"></td>
                                <td width="59%"><label class="labels"><smarty>$campo[8]</smarty></label>
                                    <table width="19%" border="0">
                                        <tr>
                                        <td width="16%"><label class="labels">Data&nbsp;inicial</label><br />
                                        	<input name="periodo_ini" type="text" class="caixa" id="periodo_ini" size="10" onkeypress="transformaData(this, event);" onkeyup="return autoTab(this, 10);" value="<smarty>$smarty.now|date_format:'%d/%m/%Y'</smarty>" onblur="return checaTamanhoData(this,10); " /></td>
                                        <td width="3%"><label class="labels">Data&nbsp;final</label><br />
                                        	<input name="periodo_fim" type="text" class="caixa" id="periodo_fim" size="10" onkeypress="transformaData(this, event);" onkeyup="return autoTab(this, 10);" value="<smarty>$smarty.now|date_format:'%d/%m/%Y'</smarty>" onblur="return checaTamanhoData(this,10); " />
                                        
                                        </td>
                                    </tr>
                                    </table>
                                </td>
                              </tr>
                          </table>                          
                            <table width="100%" border="0">
                          <tr>
                            <td width="29%"><label class="labels"><smarty>$campo[9]</smarty></label><br /> 
                                <select name="responsavel" class="caixa" id="responsavel" onkeypress="return keySort(this);">
                                    <smarty>html_options values=$option_resp_values output=$option_resp_output</smarty>
                                        </select>					
                             </td>
                            </tr>
                        </table>                        
                         <table width="100%" border="0">
                          <tr valign="top">
                            <td width="51%" valign="top">
                              <div id="div_colaborador" style="display:block; width:100%; height:130px; overflow:scroll;">
                                 <div id="divcontr_1" style="float:left; width:100%;">
                                  <label class="labels"><smarty>$campo[10]</smarty></label><br />
                                    <select name="items_1" class="caixa" id="items_1" onkeypress="return keySort(this);">
                                      <smarty>html_options values=$option_funcionario_values output=$option_funcionario_output</smarty>
                                  </select><br />
                                  </div>                                  
                             </div>
                             </td>
                             <td width="49%" valign="top">
							
                                 <img src="../imagens/add.png" style="cursor:pointer" onclick="add_controles('div_colaborador','divcontr_1','items_1','qtd_itens')" />
                                 <img src="../imagens/delete.png" style="cursor:pointer" onclick="remove_controles('divcontr_1','qtd_itens')" /> 
                                <input name="qtd_itens" type="hidden" id="qtd_itens"  value="1" />
                            
                            </td>
                            </tr>
                        </table>                     
                     </div>
                </div>
              	<div id="a2">
             		 <div id="dv_necessidade">
						<table width="100%" border="0">
                                  <tr align="center">
                                   <td width="24%"><label class="labels"><smarty>$campo[12]</smarty></label>
                                        <table width="161">
                                            <tr>
                                                <td width="56"><label class="labels">
                                                <input type="radio" id="radio1" name="cobrar_cliente" value="1">
                                                SIM</label></td>
                                            <td width="70"><label class="labels">
                                                <input id="radio2" name="cobrar_cliente" type="radio" value="0">
                                                N&Atilde;O</label></td>
                                        </tr>
                                        </table></td>
                                  </tr>
                              </table>
						<table width="100%" border="0">				
						   <TR>
								<TD style="text-align:center; border:solid; border-color:#000; border-width:1px;"><label class="labels">Valor&nbsp;orçado:&nbsp;</label><div id="dv_orcado">&nbsp;</div>
                                <input name="vlr_orc" type="hidden" id="vlr_orc"  value="0">
                                <input name="vlr_sol" type="hidden" id="vlr_sol"  value="0">
                                <input name="qtd_itensnec" type="hidden" id="qtd_itensnec" value="0">
                                </TD>
								<TD style="text-align:center; border:solid; border-color:#000; border-width:1px;"><label class="labels">Valor&nbsp;consumido:&nbsp;</label><div id="dv_consumido">&nbsp;</div></TD>
						   </TR>
                        </table>
                        <div id="div_necessidades">&nbsp;</div>
                     </div>
                </div>   
              </div>
        </td>
        </tr>
      </table>
	  <div id="requisicao" style="width:100%;">&nbsp;</div>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>