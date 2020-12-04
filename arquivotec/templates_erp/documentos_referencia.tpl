<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<style>
	div.gridbox table.obj tr td {
	
	cursor: pointer;
}
</style>
<div id="frame" style="width:100%; height:700px;">
<form name="frm" id="frm" action="upload.php" method="post" enctype="multipart/form-data" target="upload_target" onsubmit="if(valida_campos()){startUpload_referencias();}else{alert('Campos obrigat&oacute;rios')}" style="margin:0px; padding:0px;">
	<iframe id="upload_target" name="upload_target" src="#" style="height:0px;width:0px;border:0px solid #fff;display:none;"></iframe>
    <table width="100%" border="0">        
        <tr>
          <td width="162" valign="top" class="espacamento">
		    <table width="100%" border="0">
					<tr>
						<td valign="middle"><input name="btnlimpar" id="btnlimpar" type="button" class="class_botao" value="Limpar" onclick="document.getElementById('frm').reset();" /></td>
					</tr>
					<tr>
						<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onClick="history.back();" />
                        <input name="id_documento_referencia" id="id_documento_referencia" type="hidden" value="">
                        <input type="hidden" name="acao" id="acao" value="incluir" />
                        <input type="hidden" name="funcao" id="funcao" value="documento_referencia" />
                        </td>
					</tr>
			  </table>
		  </td>
          <td valign="top">&nbsp;</td>
        </tr>        
        <tr>
          <td colspan="2" valign="top">
			<div id="my_tabbar" style="height:600px; margin-top:15px;">           	
                
            	<div id="a20">
                    <table border="0" width="100%">
                      <tr>
                        <td width="17%"><label for="id_os" class="labels">OS*</label><br />
                          <select name="id_os" class="caixa" id="id_os" onkeypress="return keySort(this);" onchange="xajax_atualizatabela(xajax.getFormValues('frm'), true,1);seleciona_combo(this.value, 'inc_id_os');xajax_atualizatabela(xajax.getFormValues('frm'), true,0);" >
                            <option value="">SELECIONE</option>
                            <smarty>html_options values=$option_os_values output=$option_os_output</smarty>
                          </select>
                       	</td> 
                      </tr>
                    </table>            
                    <table border="0" width="100%">              
                      <tr>
                        <td width="18%"><label for="tipo_doc" class="labels">Tipo&nbsp;documento*:</label><br />
                          <select name="tipo_doc" class="caixa"  id="tipo_doc" onkeypress="return keySort(this);" onchange="xajax_preenchetiporef(this.value);xajax_atualizatabela(xajax.getFormValues('frm'));">
                            <option value="">SELECIONE</option>
                            <smarty>html_options values=$option_tipo_values output=$option_tipo_output</smarty>
                        </select></td>
                        <td width="18%"><label for="numdocumento" class="labels">N&ordm;&nbsp;Documento</label><br />
                            <input name="numdocumento" type="text" class="caixa" id="numdocumento" placeholder="N�mero documento" size="30" maxlength="50" />
                        </td>
                        <td width="17%"><label for="titulo" class="labels">T&iacute;tulo/Assunto</label><br />
                        	<input name="titulo" type="text" class="caixa" id="titulo" placeholder="T�tulo" size="30" /></td>
                        <td width="65%"><label for="palavras_chave" class="labels">Palavras-chave</label><br />
                        <input name="palavras_chave" type="text" class="caixa" id="palavras_chave" placeholder="Palavras-chave" size="30" /></td>
                      </tr>
                    </table>
                    <table border="0" width="100%">
                      <tr>
                        <td width="10%"><label for="perm_rev" class="labels">Nova&nbsp;Revisão</label><br />
                          <select name="perm_rev" class="caixa"  id="perm_rev" onkeypress="return keySort(this);" disabled="disabled" onchange="xajax_lib_rev(this.options[this.selectedIndex].value)">
                            <option value="0">N&Atilde;O</option>
                            <option value="1">SIM</option>
                          </select></td>
                        <td width="6%"><label for="revisao" class="labels">Revisão</label><br />
                          <input name="revisao" type="text" class="caixa" id="revisao" size="5" value="0" />
                        </td>
                        <td width="9%"><label for="data_registro" class="labels">Data&nbsp;Registro</label><br />                         
  							<input name="data_registro" type="text" class="caixa" id="data_registro" size="10" onkeypress="transformaData(this, event);" onkeyup="return autoTab(this, 10);" value="<smarty>$smarty.now|date_format:'%d/%m/%Y'</smarty>" />
                        </td>
                        <td width="75%"><label for="arquivo" class="labels">Arquivo*</label><br />
                          <input type="file" name="arquivo" id="arquivo" class="caixa" />
                          <input type="hidden" name="arquivo_ed" id="arquivo_ed" value="" />
                          </td>
                      </tr>
                    </table>
                    <table border="0" width="100%">
                            <tr>
                            <td width="10%"><label for="servico" class="labels">Servi�o</label><br /> 
                                <select name="servico" class="caixa" id="servico" onkeypress="return keySort(this);">
                                    <smarty>html_options values=$option_servico_values output=$option_servico_output</smarty>
                                </select>
                            </td>
                          </tr>
                        </table>
                    <div id="tecnica" style="display:none;">
                    <table border="0" width="100%">
                      <tr>
                        <td width="19%"><label for="id_disciplina" class="labels">Disciplina*:</label><br />
                          <select name="id_disciplina" class="caixa"  id="id_disciplina" onkeypress="return keySort(this);" onchange="xajax_atualizatabela(xajax.getFormValues('frm'));xajax_preenchetipodoc(this.value); ">
                            <option value="">SELECIONE</option>
                            <smarty>html_options values=$option_setor_values output=$option_setor_output</smarty>
                        </select></td>
                        <td width="22%"><label for="id_tipo_doc" class="labels">Documento&nbsp;refer&ecirc;ncia*</label><br />
                            <select name="id_tipo_doc" class="caixa"  id="id_tipo_doc" onkeypress="return keySort(this);" onchange="xajax_atualizatabela(xajax.getFormValues('frm'));">
                              <option value="">SELECIONE</option>
                            </select>
                        </td>
                        <td width="5%"><label for="num_grd" class="labels">GRD N&ordm;</label><br />
                            <input type="text" name="num_grd" id="num_grd" class="caixa" placeholder="N�mero GRD" size="15" />
                        </td>
                      </tr>
                    </table>
                    <table border="0" width="100%">
                          <tr>
                            <td width="16%"><label for="id_formato" class="labels">Formato</label><br />
                              <select name="id_formato" class="caixa"  id="id_formato" onkeypress="return keySort(this);">
                                <option value="">SELECIONE</option>
                                <smarty>html_options values=$option_formato_values output=$option_formato_output</smarty>
                              </select>                    
                            </td>
                            <td width="30%"><label for="origem" class="labels">Origem</label><br />
                              <input name="origem" type="text" class="caixa" id="origem" placeholder="Origem" size="40"/></td>
                            <td width="7%">
                            	<input type="checkbox" name="chk_edital" id="chk_edital" value="1" onclick="if(this.checked){document.getElementById('chk_cert').checked=false;}" />
                            	<label class="labels">Edital</label></td>
                            <td width="11%"><input name="chk_cert" type="checkbox" id="chk_cert" value="1" onclick="if(this.checked){document.getElementById('chk_edital').checked=false;}" />
                            	<label class="labels">Certificado</label></td>
                          </tr>
                    </table>
                    </div>
                      <table border="0">
                          <tr>
                              <td><label for="busca" class="labels">Busca</label><br />
                              <input name="busca" type="text" class="caixa" id="busca" placeholder="Busca" size="17"><img src="<smarty>$smarty.const.DIR_IMAGENS</smarty>find.png" style='cursor:pointer;' onclick="xajax_atualizatabela(xajax.getFormValues('frm'), true,1);" /></td>
                          </tr>
                      </table>
                    <p style="display:none;" id="inf_upload">&nbsp;</p>
                    <input name="btnalterar" id="btnalterar" type="submit" class="class_botao" value="Alterar" disabled="disabled" />
                    <div id="div_docs_referencia" style="margin-top:10px;overflow:scroll;"><span class="labels" style="font-weight:bold">Selecione uma OS</span></div> 
                </div>
                
                <div id="a30">
                	<div id="div_revisoes" style="overflow:scroll;border:solid #999999 1px;">&nbsp;</div>
                </div>
                
                <div id="a10">
                    <table border="0" width="100%">
                      <tr>
                        <td width="17%"><label for="inc_id_os" class="labels">OS*</label><br />
                          <select name="inc_id_os" class="caixa" id="inc_id_os" onkeypress="return keySort(this);" onchange="xajax_atualizatabela(xajax.getFormValues('frm'), true,0);seleciona_combo(this.value, 'id_os');xajax_atualizatabela(xajax.getFormValues('frm'), true,1)" >
                            <option value="">SELECIONE</option>
                            <smarty>html_options values=$option_os_values output=$option_os_output</smarty>
                          </select>
                        </td>
                      </tr>
                    </table>
                    <table border="0" width="100%">              
                      <tr>
                        <td width="12%"><label for="inc_tipo_doc" class="labels">Tipo&nbsp;documento*:</label><br />
                          <select name="inc_tipo_doc" class="caixa"  id="inc_tipo_doc" onkeypress="return keySort(this);" onchange="xajax_preenchetiporef(this.value,0);xajax_atualizatabela(xajax.getFormValues('frm'),0);">
                            <option value="">SELECIONE</option>
                            <smarty>html_options values=$option_tipo_values output=$option_tipo_output</smarty>
                        </select></td>
                        <td width="20%"><label for="inc_numdocumento" class="labels">N&ordm;&nbsp;Documento</label><br />
                            <input name="inc_numdocumento" type="text" class="caixa" id="inc_numdocumento" placeholder="N�mero documento" size="30" maxlength="50" />
                        </td>
                        <td width="20%"><label for="inc_titulo" class="labels">T&iacute;tulo/Assunto</label><br />
                        	<input name="inc_titulo" type="text" class="caixa" id="inc_titulo" placeholder="T�tulo" size="30" /></td>
                        <td width="20%"><label for="inc_palavras_chave" class="labels">Palavras-chave</label><br />
                       		<input name="inc_palavras_chave" type="text" class="caixa" id="inc_palavras_chave" placeholder="Palavras-chave" size="30" /></td>
                        <td width="28%"><label for="inc_revisao" class="labels">Revisão</label><br />
                          <input name="inc_revisao" type="text" class="caixa" id="inc_revisao" size="5" value="0" />
                        </td>
                      </tr>
                    </table>
                    <table border="0" width="100%">
                        <tr>
                        <td width="9%"><label for="inc_data_registro" class="labels">Data&nbsp;registro</label><br />
                          <input name="inc_data_registro" type="text" class="caixa" id="inc_data_registro" size="10" onkeypress="transformaData(this, event);" value="<smarty>$smarty.now|date_format:'%d/%m/%Y'</smarty>" />
                        </td>
                        <td width="91%"><label for="inc_arquivo" class="labels">Arquivo*</label><br />
                          <input type="file" name="inc_arquivo" id="inc_arquivo" class="caixa" />
                          </td>
                        </tr>
                    </table>
                    
                    <div id="inc_tecnica" style="display:none;">
                    <table border="0" width="100%">
                      <tr>
                        <td width="12%"><label for="inc_id_disciplina" class="labels">Disciplina*:</label><br />
                          <select name="inc_id_disciplina" class="caixa"  id="inc_id_disciplina" onkeypress="return keySort(this);" onchange="xajax_atualizatabela(xajax.getFormValues('frm'),0);xajax_preenchetipodoc(this.value,0); ">
                            <option value="">SELECIONE</option>
                            <smarty>html_options values=$option_setor_values output=$option_setor_output</smarty>
                        </select></td>
                        <td width="16%"><label for="inc_id_tipo_doc" class="labels">Documento&nbsp;refer&ecirc;ncia*</label><br />
                            <select name="inc_id_tipo_doc" class="caixa"  id="inc_id_tipo_doc" onkeypress="return keySort(this);" onchange="xajax_atualizatabela(xajax.getFormValues('frm'),0);">
                              <option value="">SELECIONE</option>
                            </select>
                        </td>
                        <td width="13%"><label for="inc_num_grd" class="labels">GRD N&ordm;</label><br />
                            <input type="text" name="inc_num_grd" id="inc_num_grd" size="20" placeholder="N�mero GRD" class="caixa" />
                        </td>
                        <td width="11%"><label for="inc_id_formato" class="labels">Formato</label><br />
                          <select name="inc_id_formato" class="caixa"  id="inc_id_formato" onkeypress="return keySort(this);">
                            <option value="">SELECIONE</option>
                            <smarty>html_options values=$option_formato_values output=$option_formato_output</smarty>
                          </select></td>
                             <td width="26%"><label for="inc_origem" class="labels">Origem</label><br />
                                <input name="inc_origem" type="text" class="caixa" id="inc_origem" size="40" placeholder="Origem" /></td>
                        <td width="9%"><input type="checkbox" name="inc_chk_edital" id="inc_chk_edital" value="1" onclick="if(this.checked){document.getElementById('inc_chk_cert').checked=false;}" />
                        	<label class="labels">Edital</label></td>
                        <td width="13%"><input name="inc_chk_cert" type="checkbox" id="inc_chk_cert" value="1" onclick="if(this.checked){document.getElementById('inc_chk_edital').checked=false;}" />
                        <label class="labels">Certificado</label></td>                        
                      </tr>
                    </table>                                        
                   </div>
                   
                   <input name="btninserir" id="btninserir" type="submit" class="class_botao" value="Inserir" />&nbsp;
                   <input name="btnconcluir" id="btnconcluir" type="button" class="class_botao" value="Concluir" disabled="disabled" onclick="xajax_concluir(xajax.getFormValues('frm'));" />
                   <p style="display:none;" id="inc_inf_upload">&nbsp;</p>
                   <div id="inc_div_docs_referencia" style="width:100%">&nbsp;</div>                      
                </div>
                
            </div>   
           </td>
        </tr>
      </table>           
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>