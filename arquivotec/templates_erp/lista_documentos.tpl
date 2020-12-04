<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width:100%; height:700px;">
	<form name="frm_ged_lista_documentos" id="frm_ged_lista_documentos" action="relatorios/rel_lista_documentos.php" method="POST" style="margin:0px; padding:0px;">
		<table width="100%" border="0">        
	        <tr>
	          <td width="116" rowspan="3" valign="top" class="espacamento">
			  <table width="100%" cellpadding="0" cellspacing="0">
					<tr>
					  <td valign="middle"><input name="btngerar" id="btngerar" type="button" class="class_botao" value="Gerar&nbsp;Relat&oacute;rio" onClick="if(document.getElementById('id_os').options[document.getElementById('id_os').selectedIndex].value!==''){document.forms[0].submit();}else{alert('� necess�rio selecionar uma OS / Projeto!');}" /></td>
				  </tr>
					<tr>
						<td valign="middle" ><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onClick="history.back();" /></td>
					</tr>
				</table></td>
	        </tr>        
	        <tr>
	          <td colspan="2" valign="top" class="espacamento">
				<table width="100%" border="0">
	            <tr>
	              <td colspan="3" align="left"><label for="id_os" class="labels">OS / Projeto</label><br />
				    <select name="id_os" id="id_os" class="caixa" onkeypress="return keySort(this);">
				      <option value="">SELECIONE</option>
				      <smarty>html_options values=$option_os_values output=$option_os_output</smarty>
				      </select>
				  </td>
	            </tr>	            
	            <tr>
	              <td width="47%">
				    <input name="chk_emitidos" type="checkbox" id="chk_emitidos"  value="1" onclick="if(!this.checked){xajax.$('chk_resumido').checked=false;xajax.$('div_finalidade').style.display='none';xajax.$('id_finalidade').selectedIndex=0;xajax.$('chk_periodo').checked=false;}">
				  <label class="labels">Somente documentos emitidos</label></td>
	              <td colspan="2" align="left"><label class="labels">Formato</label><br />
                      <select name="id_formato" id="id_formato" class="caixa" onkeypress="return keySort(this);">
                      <option value="" selected="selected">TODOS</option>
                      <smarty>html_options values=$option_fmt_values output=$option_fmt_output</smarty>
                      </select>
	             </td>
	            </tr>
	            <tr>
	              <td>
	              	<input name="chk_resumido" type="checkbox" id="chk_resumido" value="1" onclick="if(this.checked){xajax.$('chk_emitidos').checked=true;xajax.$('div_finalidade').style.display='inline';}else{xajax.$('div_finalidade').style.display='none';xajax.$('id_finalidade').selectedIndex=0;}" title="Mostrar apenas a emiss�o mais recente de cada documento">
	              <label class="labels">Mostrar resumido</label></td>
	              <td colspan="2"><label for="disciplina" class="labels">Disciplina</label><br />
                      <select name="disciplina" id="disciplina" class="caixa" onkeypress="return keySort(this);">
                      <option value="" selected="selected">TODAS</option>
                      <smarty>html_options values=$option_disc_values output=$option_disc_output</smarty>
                      </select>              
                  </td>
	            </tr>
	            <tr>
	              <td>
	              <div id="div_finalidade" style="display:none;">
	              <label for="id_finalidade" class="labels">Finalidade</label><br />
	                <select name="id_finalidade" id="id_finalidade" class="caixa">
	                  <option value="" selected="selected">TODOS</option>
	                  <smarty>html_options values=$option_finalidade_values output=$option_finalidade_output</smarty>
	              </select>
	              </div></td>
	              <td width="24%"><input name="chk_periodo" type="checkbox" id="chk_periodo" value="1" onClick="if(this.checked){xajax.$('chk_emitidos').checked=true;xajax.$('div_periodo').style.display='inline';xajax.$('dataini').focus();}else{xajax.$('dataini').value='';xajax.$('datafim').value='';xajax.$('div_periodo').style.display='none';}"><span class="labels">Per&iacute;odo&nbsp;(Data&nbsp;de&nbsp;Emissão)</span></td>
	              <td width="29%"><input name="chk_periodo_dev" type="checkbox" id="chk_periodo_dev" value="1" onclick="if(this.checked){xajax.$('chk_emitidos').checked=true;xajax.$('div_periodo_dev').style.display='inline';xajax.$('dataini_dev').focus();}else{xajax.$('dataini_dev').value='';xajax.$('datafim_dev').value='';xajax.$('div_periodo_dev').style.display='none';}" />
	              <label class="labels">Per&iacute;odo&nbsp;(Data&nbsp;de&nbsp;Devolução)</label></td>
	            </tr>
	            <tr>
	              <td><input name="chk_estatistica" type="checkbox" id="chk_estatistica" value="1" title="Suprime as estatisticas dos documentos" />
	              <label class="labels">Mostrar estat&iacute;sticas</label></td>
	              <td><div id="div_periodo" style="display:none;">
	              <input type="text" name="dataini" id="dataini" class="caixa" size="12" onKeyPress="transformaData(this, event);" onKeyUp="return autoTab(this,'datafim', 10);" /> <label class="labels">at�</label> 
	              <input type="text" name="datafim" id="datafim" class="caixa" size="12" maxlength="10" onKeyPress="transformaData(this, event);" />
	              </div></td>
	              <td><div id="div_periodo_dev" style="display:none;">
	              <input type="text" name="dataini_dev" id="dataini_dev" class="caixa" size="12" onKeyPress="transformaData(this, event);" onKeyUp="return autoTab(this,'datafim_dev', 10);" /> <label class="labels">at�</label> 
	              <input type="text" name="datafim_dev" id="datafim_dev" class="caixa" size="12" maxlength="10" onKeyPress="transformaData(this, event);" />
	              </div></td>
	            </tr>
	          </table>          
	          </td>
	        </tr>
	      </table>
	</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>