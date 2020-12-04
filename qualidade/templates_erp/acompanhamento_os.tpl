<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<style>
div#a20 {
    position: relative;
    width: 100%;
    height: 100%;
    overflow: auto;
}

div#a30 {
    position: relative;
    width: 100%;
    height: 100%;
    overflow: auto;
}

div#a40 {
    position: relative;
    width: 100%;
    height: 100%;
    overflow: auto;
}

div#a50 {
    position: relative;
    width: 100%;
    height: 100%;
    overflow: auto;
}

div#a60 {
    position: relative;
    width: 100%;
    height: 100%;
    overflow: auto;
}

</style>

<div id="frame" style="width: 100%; height: 700px;">
<form name="frm_acompanhamento" id="frm_acompanhamento" action="upload.php" target="upload_target" method="POST" enctype="multipart/form-data">
	<iframe id="upload_target" name="upload_target" src="#" style="width:100%;height:150;display:none;"></iframe>
    <table width="100%" border="0">        
        <tr>
          <td width="116" rowspan="2" valign="top" class="espacamento">
              <table width="100%" border="0">
                    
                    <tr>
                      <td valign="middle">
                        <input name="btnimprimir" id="btnimprimir" type="button" class="class_botao" value="Imprimir" onclick="if(xajax.$('id_os').value==''){alert('É necessário selecionar uma OS!');}else{xajax_imprimir('pdf')}" disabled /> </td>
                </tr>
                    <tr>
                        <td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onclick="history.back();" /></td>
                    </tr>
              </table>
          </td>
          <td colspan="2"><label class="labels"><strong>Projeto:&nbsp;</strong><div id="os">&nbsp;</div></label>
								<input name="id_os" type="hidden" id="id_os" value="" />
								<input name="id" type="hidden" id="id" value="" />
                                <input type="hidden" id="prefixo" name="prefixo" value="" />
								<input name="lista_pendencia" type="hidden" id="lista_pendencia" value="" />
          </td>
        </tr>        
        <tr>
          <td colspan="2" valign="top" class="espacamento">
		  	 <div id="my_tabbar" style="height:650px;"></div>          
				<div id="a10">
					<table width="100%">
						<tr>
							<td width="34%"><label class="labels"><strong>Cliente</strong></label><br />
								<div id="cliente" class="labels">&nbsp;</div></td>
							<td width="25%"><label class="labels"><strong>Coordenador&nbsp;Cliente</strong></label><br />
								<div id="coord_cliente" class="labels">&nbsp;</div>							</td>
							<td width="41%"><label class="labels"><strong>E-mail</strong></label><br />
								<div id="email_coord_cliente" class="labels" style="text-decoration:none;">&nbsp;</div></td>
						</tr>
					</table>
					<table width="100%">
						<tr>
							<td width="34%"><label class="labels"><strong>Coordenador</strong></label><br />
								<div id="coord_dvm" class="labels">&nbsp;</div></td>
					  <td width="25%"><label class="labels"><strong>Coordenador&nbsp;Aux.</strong></label><br />
								<div id="coord_aux" class="labels">&nbsp;</div></td>
  						<td width="41%"><label class="labels"><strong>Status</strong></label><br />
								<div id="status_os" class="labels">&nbsp;</div>							
							</td>
					  </tr>
					</table>
					<table width="100%">
						<tr>
							<td width="45%"><label for="palavra_chave" class="labels"><strong>Palavras&nbsp;chave</strong></label><br />
							<input name="palavra_chave" type="text" class="caixa" id="palavra_chave" placeholder="Palavra chave" size="70"/>&nbsp;<input name="btnatualizar" id="btnatualizar" class="class_botao" type="button" value="Atualizar" onclick="if(confirm('Confirma&nbsp;a&nbsp;atualização&nbsp;das&nbsp;palavras&nbsp;chave?')){xajax_atualizar(xajax.getFormValues('frm_acompanhamento'),'palavra');};" disabled="disabled"  /> 	
                            </td>
					  </tr>
					</table>
					<table width="100%">
						<tr valign="top">
							<td width="32%"><label class="labels"><strong>Atestado&nbsp;de&nbsp;Capacidade&nbsp;Técnica</strong></label><br />
                         	                      	
							<input type="file" id="arq_cat" name="arq_cat" class="caixa" disabled="disabled" />&nbsp;<input name="btncat" id="btncat" class="class_botao" type="button" value="Inserir" disabled="disabled" onclick="if(confirm('Deseja&nbsp;anexar&nbsp;a&nbsp;CAT?')){anexos('ACT');};" /> 	
                            
                            </td>
					  	<td  width="68%"><label class="labels"><strong>Arquivo&nbsp;ACT</strong></label><br />
                        <div id="div_cat"></div>
                        </td>
                        
					  </tr>
					</table>
			  		<table width="100%" style="border-color:#09C; border-style:solid; border-width:1px;">
					  <tr>
							<td colspan="3"><label class="labels">Busca</label></td>
					  </tr>
						<tr>
						  <td colspan="3"><label for="chave" class="labels">Palavras&nbsp;chave</label><br />
						    <input name="chave" type="text" class="caixa" id="chave" size="30" /></td>
						</tr>
						<tr>
                          <td width="15%"><label for="os_coord" class="labels">Projeto&nbsp;coordenador</label><br />
                            <select name="os_coord" class="caixa" id="os_coord" onkeypress="return keySort(this);">
                              <smarty>html_options values=$option_coorddvm_values output=$option_coorddvm_output</smarty>
                            </select></td>
                          <td width="6%"><label for="status" class="labels">Status</label><br />
                            <select name="status" class="caixa" id="status" onkeypress="return keySort(this);">
                              <smarty>html_options values=$option_status_values output=$option_status_output</smarty>
                            </select></td>

                          <td width="79%"><input name="btnbuscar" id="btnbuscar" class="class_botao" type="button" value="Buscar" onclick="xajax_atualizatabela(xajax.getFormValues('frm_acompanhamento'));" /></td>
						</tr>
				  </table>
                  <div id="div_os" style="width:100%;">&nbsp;</div>
				</div>
                
                <div id="a30">
                      <div id="cont_analise_periodica" style="width:98%;">
                      <table width="99%" style="border:solid #999999 1px;" border="1" cellspacing="0" cellpadding="1">                     
                          
                          <tr>
                              <td align="center" colspan="5"><label class="labels"><strong>Para&nbsp;análise&nbsp;crítica periódica,&nbsp;utilize&nbsp;o&nbsp;Procedimento&nbsp;de&nbsp;Execução&nbsp;de&nbsp;Projetos&nbsp;
                              <a style="text-decoration:none;" href="#" onclick="open_file('016_PROJETO/PROCEDIMENTO_DE_EXECUCAO_DE_PROJETO.pdf','SGI');">Procedimento</a></strong></label></td>
                          </tr>
                          <tr>
                            <td width="46%"><label class="labels">2-Existem&nbsp;problemas&nbsp;para&nbsp;a&nbsp;realização&nbsp;do&nbsp;projeto?</label></td>
                            <td width="6%" align="center"><label class="labels">Sim</label><br />
                            	<input type="radio" name="chk_problemas_projeto" value="1" id="chk_problemas_projeto_1" onclick="control_div('tr_id_11',true);document.getElementById('btn_anexar').disabled=false;document.getElementById('pend_int').disabled=false;document.getElementById('data_solicitacao').disabled=false;" disabled="disabled" /></td>
                            <td width="6%" align="center"><label class="labels">Não</label><br />
                            	<input name="chk_problemas_projeto" type="radio" id="chk_problemas_projeto_2" value="0" checked="checked" onclick="control_div('tr_id_11',false);document.getElementById('btn_anexar').disabled=true;document.getElementById('pend_int').disabled=true;document.getElementById('data_solicitacao').disabled=true;document.getElementById('id_os_x_analise_critica_periodica').value='';" disabled="disabled" /></td>
                            <td width="14%" align="center"><label class="labels">Pendência&nbsp;Interna:</label><br />
                            	<input type="checkbox" name="pend_int" id="pend_int" value="1" disabled="disabled" /></td>
                            <td width="28%"><label for="data_solicitacao" class="labels">Solicitado&nbsp;em:</label><br />
                            	<input name="data_solicitacao" type="text" class="caixa" id="data_solicitacao" size="10" maxlength="10" disabled="disabled" value="<smarty>$smarty.now|date_format:'%d/%m/%Y'</smarty>" onkeypress="transformaData(this, event);" /></td>                       	  
                          </tr>
                          
                          <tr id="tr_id_11" style="display:none;visibility:hidden">
                            <td colspan="5">
                                <table width="99%" style="border:solid #09C 1px;" border="1" >
                                    <tr>
                                        <td colspan="2" rowspan="2" valign="top"><label class="labels">Identificação&nbsp;Problema</label><br />
                                        	<textarea name="identificacao_problema_ap" class="caixa" id="identificacao_problema_ap" cols="100" rows="5" placeholder="Identificação problema" style="width:100%;"></textarea></td>
                                        <td colspan="2" valign="top"><label for="disciplina_analise_critica" class="labels">Disciplina</label><br />
                                          <select name="disciplina_analise_critica" class="caixa" style="width:100%"  id="disciplina_analise_critica" onkeypress="return keySort(this);">
                                            <smarty>html_options values=$option_disciplina_values output=$option_disciplina_output</smarty>
                                          </select>
                                         </td>
                                    </tr>
                                    <tr>
                                      <td colspan="2" valign="top"><label for="solicitado_por" class="labels">Solicitado&nbsp;por:</label><br />
                                      	<input name="solicitado_por" type="text" class="caixa" id="solicitado_por" size="25" placeholder="Solicitado por" style="width:100%;"></td>
                                    </tr>
                                    <tr>
                                        <td width="40%" rowspan="2" valign="top"><label for="solucao_possivel_ap" class="labels">Observação</label><br />
                                        	<textarea name="solucao_possivel_ap" cols="55" rows="5" class="caixa" id="solucao_possivel_ap" style="width:100%;" placeholder="Solução possível"></textarea></td>
                                        <td width="37%" rowspan="2" valign="top"><label for="acao_corretiva_ap" class="labels">Ação&nbsp;corretiva</label><br />
                                        	<textarea name="acao_corretiva_ap" cols="55" rows="5" class="caixa" id="acao_corretiva_ap" style="width:100%;" placeholder="Ação corretiva"></textarea></td>
                                        <td colspan="2" valign="top"><label for="solucao_por" class="labels">Solução&nbsp;por:</label><br />
                                        	<input name="solucao_por" type="text" class="caixa" id="solucao_por" size="25" style="width:100%;" placeholder="Solução por" ></td>
                                    </tr>
                                    <tr>
                                      <td width="8%" valign="top"><label for="data_ap" class="labels">Data</label><br />
                                      	<input name="data_ap" type="text" class="caixa" id="data_ap" size="10" maxlength="10" placeholder="Data" value="" onkeypress="transformaData(this, event);" /></td>
                                      <td width="15%" valign="top"><label for="status_ap" class="labels">Status</label><br />
                                            <select name="status_ap" class="caixa" style="width:100%" id="status_ap" onkeypress="return keySort(this);">
                                                <option value="">SELECIONE</option>
                                                <option value="1">PENDENTE</option>
                                                <option value="2">RESOLVIDO</option>
                                                <option value="3">INFORMAÇÃO</option>
                                            </select>
                                      </td>
                                    </tr>
                                    <tr>
                                      <td colspan="4" valign="top"><label class="labels">Anexo</label><br />
                                      	<input type="file" id="arq_analise_periodica" name="arq_analise_periodica" class="caixa" /></td>
                                    </tr>
                                </table>           
                            </td>
                            <input type="hidden" name="id_os_x_analise_critica_periodica" id="id_os_x_analise_critica_periodica" value="" />
                        </tr>
                              
                      
                      </table>
                      <table width="95%" border="0" cellspacing="0" cellpadding="5">
                          <tr>
                              <td>							
                                  <input class="class_botao" type="button" name="btn_anexar" id="btn_anexar" disabled="disabled" value="Atualiza" onclick="if(confirm('Confirma&nbsp;a&nbsp;atualização&nbsp;das&nbsp;informações?')){anexos('APJ');};" />
                              </td>
                          </tr>
                      </table>                      
                            
                      </div>
                      <div id="div_ap" style="width:100%;">&nbsp;</div>             
                  </div>               
                  
				<div id="a50">
                	<div id="div_analise_final" style="overflow:scroll;">
					<table width="100%" style="border:solid #999999 1px;" border="0" cellspacing="0" cellpadding="2">
                
   						<tr>
						  <td>
                          	<table width="100%" style="border:solid #999999 0px;" border="0">
                            	<tr>
                                	<td><label for="txt_asp_positivos" class="labels" style="font-size:14px;">1-Quais&nbsp;foram&nbsp;os&nbsp;aspectos&nbsp;positivos&nbsp;mais&nbsp;relevantes?</label><br />
                                    <textarea name="txt_asp_positivos" cols="150" rows="5" class="caixa" id="txt_asp_positivos" style="width:100%;" placeholder="Aspectos positivos"></textarea>
                                    </td>
                                </tr>
                            </table>
                          </td>
					  </tr>                       
   						<tr>
						  <td>
                          	<table width="100%" style="border:solid #999999 0px;" border="0">
                            	<tr>
                                	<td><label for="txt_asp_negativos" class="labels" style="font-size:14px;">2-Quais&nbsp;foram&nbsp;os&nbsp;aspectos&nbsp;negativos&nbsp;mais&nbsp;relevantes?</label><br />
                                    <textarea name="txt_asp_negativos" cols="150" rows="5" class="caixa" id="txt_asp_negativos" style="width:100%;" placeholder="Aspectos negativos"></textarea>
                                    </td>
                                </tr>
                            </table>
                          </td>
					  </tr>            

					</table>                    
					<table width="100%" border="0" cellspacing="0" cellpadding="5">
                       <tr>
                       		<td width="300"><label for="nome_analise" class="labels">Nome:</label><br />
                            	<input type="text" name="nome_analise" id="nome_analise" value="<smarty>$nome_validacao</smarty>" class="caixa" size="50">
                       		<td width="624"><label for="data_analise" class="labels">Data:</label><br />
                            	<input type="text" name="data_analise" id="data_analise" class="caixa" value="<smarty>$data_validacao</smarty>" size="12" maxlength="10" onKeyPress="transformaData(this, event);"></td>
                      </tr>
		 			 	<tr>
							<td colspan="2">							
								<input class="class_botao" type="button" name="inserir_analise_final" id="inserir_analise_final" disabled="disabled" value="Inserir" onclick="xajax_atualizar(xajax.getFormValues('frm_acompanhamento'),'analise_final');" />
							</td>
						</tr>
					</table>
                    </div>
				</div> 
			
		  </td>
        </tr>
      </table>	  
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>
