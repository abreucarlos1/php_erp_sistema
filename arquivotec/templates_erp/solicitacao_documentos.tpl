<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width:100%; height:700px;">
	<form name="frm" id="frm" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST" style="margin:0px; padding:0px;">
		<table width="100%" border="0">
			<tr>
			  <td width="116" valign="top" class="espacamento" >
			  <table width="100%">
              		<tr>
                    	<td valign="middle"><input name="btninserir_itens" type="button" class="class_botao" id="btninserir_itens" onclick="xajax_inserir(xajax.getFormValues('frm',true));" disabled="disabled" value="Incluir" />
                        </td>
                    </tr>
					<tr>
						<td valign="middle"><input name="btninserir" type="button" class="class_botao" id="btninserir" onclick="xajax_enviar(xajax.getFormValues('frm',true));" disabled="disabled" value="Solicitar Docs" />
						</td>
					</tr>
					<tr>
						<td valign="middle"><input name="btnvoltar" type="button" class="class_botao" id="btnvoltar" onclick="history.back();" value="Voltar" />
						</td>
					</tr>
				</table></td>
			  <td width="100%" valign="top" class="espacamento">
			    <table border="0" width="100%">
                <tr>
                  <td colspan="2"><label class="labels">Funcionário  <span style="font-size:12px; font-weight:bold;">
					<smarty>$nome_funcionario</smarty></span></label>                  
                  </td>
                  </tr>
                  <tr>
                  	<td width="21%"><label for="id_pedido" class="labels">N&deg; pedido</label><br />
                    <input name="id_pedido" type="text" id="id_pedido" value="" class="caixa" disabled="disabled" />
                    </td>
                   </tr>
                </table>                   
				</td>
			</tr>
			<tr>
			  <td colspan="2" valign="top">              
       			<div id="my_tabbar" style="height:600px;">                                     
                	<div id="a10">     
                		<div id="div_dados_os">                
                            <table width="100%" border="0">
                            <tr>
                              <td width="8%"><label for="data" class="labels">Data</label><br />
                              	<input name="data" type="text" class="caixa" id="data" size="12" maxlength="10"  onkeypress="transformaData(this, event);" onkeyup="return autoTab(this,'os', 10);" value="<smarty>$smarty.now|date_format:'%d/%m/%Y'</smarty>" /></td>
                                <td width="92%"><label for="os" class="labels">OS</label><br /> 
                                   <select name="os" class="caixa" id="os" onkeypress="return keySort(this);" onchange="xajax_voltar();xajax_atualizatabela(xajax.getFormValues('frm',true));if(this.value!=''){myTabbar.tabs('a20_').enable();}else{myTabbar.tabs('a20_').disable(true)};">
                                    <smarty>html_options values=$option_os_values output=$option_os_output</smarty>
                                  </select>
                                 </td>
                            </tr>			    
                            </table>
                          <table border="0">
                              <tr>
                                    <td width="173"><label for="busca" class="labels">Busca</label><br />
                                        <input name="busca" type="text" class="caixa" id="busca" placeholder="Busca" size="50" /> <input class="class_botao" type="button" name="button" id="button" value="Buscar" onclick="xajax_atualizatabela(xajax.getFormValues('frm',true));" />
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div id="solicitacao"> </div>
            		</div>
                    <div id="a20">     
                      <div id="div_itens">
                        <table width="100%" border="0">
                            <tr>
                                <td width="7%"><label for="id_disciplina" class="labels">Disciplina*</label><br />                  
                                <select name="id_disciplina" class="caixa" id="id_disciplina" onchange="xajax_preenchetarefas(xajax.getFormValues('frm',true));" onkeypress="return keySort(this);">
                                </select> 
                              </td>
                                
                                <td width="93%"><label for="CodAtividade" class="labels">Documento*</label><br />
                                  <!-- <select name="CodAtividade" class="caixa" id="CodAtividade" onchange="if(document.getElementById('tag').value==''){document.getElementById('tag').value=this.options[this.selectedIndex].text};xajax_formato(xajax.getFormValues('frm',true));" onkeypress="return keySort(this);"> -->
                                 <select name="CodAtividade" class="caixa" id="CodAtividade" onchange="if(document.getElementById('tag').value==''){document.getElementById('tag').value=this.options[this.selectedIndex].text};" onkeypress="return keySort(this);">
                                    <option value="">SELECIONE</option>
                                  </select>
                              </td>
                          </tr>
                        </table>
                        <table width="100%" border="0">
                            <tr>
                                <td width="15%"><label class="labels">Documento</label><br />
                                <label class="labels">Novo</label><input name="tipodoc" type="radio" value="0" checked="checked" onclick="habilita(false)" /> <label class="labels">Existente</label><input name="tipodoc" type="radio" value="1" onclick="habilita(true)" /></td>
                                <td width="11%"><label for="finalidade" class="labels">Finalidade</label><br />
                                <select name="finalidade" class="caixa" id="finalidade" disabled="disabled" onkeypress="return keySort(this);">
                                        <option value="">SELECIONE</option>
                                        <option value="CONSULTA">CONSULTA</option>
                                        <option value="REVISÃO">REVISÃO</option>
                                                            </select></td>
                                <td width="74%"><label for="servico" class="labels">Serviço</label><br /> 
                                   <select name="servico" class="caixa" id="servico">
                                    <smarty>html_options values=$option_servico_values output=$option_servico_output</smarty>
                                  </select>
                                 </td>
                            </tr>
                        </table>
                      	<table width="100%" border="0">
                      <tr>
                        <td width="20%"><label for="tag" class="labels">Título 1</label><br />
                          <input name="tag" type="text" class="caixa" id="tag" size="30" maxlength="50" value="" readonly="readonly" />
                        </td>                    
                        <td width="20%"><label for="tag2" class="labels">Título 2</label><br />
                          <input name="tag2" type="text" class="caixa" id="tag2" size="30" maxlength="50" placeholder="Tag 2" value="" />
                        </td>
                        <td width="20%"><label for="tag3" class="labels">Título 3</label><br />
                          <input name="tag3" type="text" class="caixa" id="tag3" size="30" maxlength="50" placeholder="Tag 3" value="" />
                        </td>
                        <td width="40%"><label for="tag4" class="labels">Título 4</label><br />
                          <input name="tag4" type="text" class="caixa" id="tag4" size="30" maxlength="50" placeholder="Tag 4" value="" />
                        </td>
                      </tr>
                      </table>
                        <table width="100%" border="0">
                            <tr>
                                <td width="7%"><label for="area" class="labels">Área</label><br />
                                    <input name="area" type="text" class="caixa" id="area" placeholder="Area" size="10" maxlength="100" /></td>
                                <td width="13%"><label for="setor" class="labels">Setor</label><br />
                                    <input name="setor" type="text" class="caixa" id="setor" size="20" placeholder="Setor" maxlength="100" /></td>
                                <td width="13%"><label for="numcliente" class="labels">N&uacute;mero Cliente</label><br />
                                    <input name="numcliente" type="text" class="caixa" id="numcliente" placeholder="Numero Cliente" size="20" maxlength="50" /></td>
                                <td width="7%"><label for="formato" class="labels">Formato*</label><br />
                                    <select name="formato" class="caixa"  id="formato" onkeypress="return keySort(this);">
                                <smarty>html_options values=$option_formatos_values output=$option_formatos_output</smarty>
                                </select></td>
                                <td width="5%"><label for="folhas" class="labels">Folhas</label><br />
                                    <input name="folhas" type="text" class="caixa" id="folhas" placeholder="Folhas" size="5" maxlength="5" value="0" /></td>
                                <td width="6%"><label for="revisao" class="labels">Revisão*</label><br />
                                    <input name="revisao" type="text" class="caixa" id="revisao" size="3" placeholder="Folhas" maxlength="3" value="0" /></td>
                                <td width="49%"><label for="txt_obs" class="labels">Observações:</label><br />
                                  <input name="txt_obs" type="text" class="caixa" id="txt_obs" size="30" placeholder="Observação" maxlength="60">
                               </td>
                            </tr>
                        </table>
                      </div>
                      <br />
                      <div id="itens" style="width:100%;"> </div>
                  </div>
              </div>
              </td>
		  </tr>
		  </table>
	</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>