<smarty>include file="../../templates_erp/header.tpl"</smarty>
<div id="frame" style="width:100%; height:660px;" onclick="buscaMenu();">
	<form name="frm_ged_lista_documentos" id="frm_ged_lista_documentos" action="../arquivotec/ged_lista_documentos.php" target="_blank" method="POST" style="margin:0px; padding:0px;">
		<table width="100%" border="0" cellspacing="0" cellpadding="0">        
	        <tr>
	          <td width="116" rowspan="3" valign="top" class="espacamento">
			  <table width="100%" cellpadding="0" cellspacing="0">
					<tr>
					  <td valign="middle"><input name="btngerar" id="btngerar" type="button" class="class_botao" value="Gerar Relat&oacute;rio" onclick="if(document.getElementById('id_os').options[document.getElementById('id_os').selectedIndex].value!==''){document.getElementById('frm_ged_lista_documentos').submit();}else{alert('É necessário selecionar uma OS / Projeto!');}" /></td>
				  </tr>
					<tr>
						<td valign="middle" ><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onclick="history.back();" /></td>
					</tr>
				</table></td>
	          <td width="132" rowspan="2" > </td>
	          <td colspan="2"> </td>
	          <td width="6" rowspan="3" class="<smarty>$classe</smarty>"> </td>
	        </tr>        
	        <tr>
	          <td colspan="2" valign="top" class="td_sp">
				<table width="100%" border="0">
	            <tr>
	              <td colspan="3" align="left" class="td_sp"><label class="labels">OS / Projeto</label><br />
				    <select name="id_os" id="id_os" class="caixa">
				      <option value="">SELECIONE</option>
				      <smarty>html_options values=$option_os_values output=$option_os_output</smarty>
				      </select>
				  </td>
	            </tr>
	            
	            <tr>
	              <td width="53%" class="td_sp">
				    <input name="chk_emitidos" type="checkbox" id="chk_emitidos"  value="1" onclick="if(!this.checked){xajax.$('chk_resumido').checked=false;xajax.$('div_finalidade').style.display='none';xajax.$('id_finalidade').selectedIndex=0;xajax.$('chk_periodo').checked=false;}">
				  <span class="labels">Somente documentos emitidos</span></td>
	              <td width="16%" align="left" class="td_sp"><label class="labels">Formato<br /></label>
	              <select name="id_formato" id="id_formato" class="caixa">
	              <option value="" selected="selected">TODOS</option>
	              <smarty>html_options values=$option_fmt_values output=$option_fmt_output</smarty>
	              </select>
	             </td>
	              <td width="31%" align="left" class="td_sp"> </td>
	            </tr>
	            <tr>
	              <td class="td_sp">
	              	<input name="chk_resumido" type="checkbox" id="chk_resumido" value="1" onclick="if(this.checked){xajax.$('chk_emitidos').checked=true;xajax.$('div_finalidade').style.display='inline';}else{xajax.$('div_finalidade').style.display='none';xajax.$('id_finalidade').selectedIndex=0;}" title="Mostrar apenas a emissão mais recente de cada documento">
	              <span class="labels">Mostrar resumido</span></td>
	              <td class="td_sp"><label class="labels">Disciplina</label>
	              <select name="disciplina" id="disciplina" class="caixa">
	              <option value="" selected="selected">TODAS</option>
	              <smarty>html_options values=$option_disc_values output=$option_disc_output</smarty>
	              </select>              </td>
	              <td class="fonte_12_az"> </td>
	            </tr>
	            <tr>
	              <td class="td_sp">
	              <div id="div_finalidade" style="display:none;">
	              <label class="labels">Finalidade</label>
	              <br />
	                <select name="id_finalidade" id="id_finalidade" class="caixa">
	                  <option value="" selected="selected">TODOS</option>
	                  <smarty>html_options values=$option_finalidade_values output=$option_finalidade_output</smarty>
	              </select>
	              </div></td>
	              <td class="td_sp"><input name="chk_periodo" type="checkbox" id="chk_periodo" value="1" onclick="if(this.checked){xajax.$('chk_emitidos').checked=true;xajax.$('div_periodo').style.display='inline';xajax.$('dataini').focus();}else{xajax.$('dataini').value='';xajax.$('datafim').value='';xajax.$('div_periodo').style.display='none';}"><span class="labels">Período (Data de Emissão)</span></td>
	              <td class="td_sp"><input name="chk_periodo_dev" type="checkbox" id="chk_periodo_dev" value="1" onclick="if(this.checked){xajax.$('chk_emitidos').checked=true;xajax.$('div_periodo_dev').style.display='inline';xajax.$('dataini_dev').focus();}else{xajax.$('dataini_dev').value='';xajax.$('datafim_dev').value='';xajax.$('div_periodo_dev').style.display='none';}" />
	              <span class="labels">Período (Data de Devolução)</span></td>
	            </tr>
	            <tr class="borda_esquerda">
	              <td class="td_sp"><input name="chk_estatistica" type="checkbox" id="chk_estatistica" value="1" title="Suprime as estatisticas dos documentos" />
	              <span class="labels">Mostrar estatísticas</span></td>
	              <td class="td_sp"><div id="div_periodo" style="display:none;">
	              <input type="text" name="dataini" id="dataini" class="caixa" size="12" onKeyPress="transformaData(this, event);" onKeyUp="autoTab(this,'datafim', 10);" /> <span class="labels">até</span> 
	              <input type="text" name="datafim" id="datafim" class="caixa" size="12" maxlength="10" onKeyPress="transformaData(this, event);" />
	              </div></td>
	              <td class="td_sp"><div id="div_periodo_dev" style="display:none;">
	              <input type="text" name="dataini_dev" id="dataini_dev" class="caixa" size="12" onKeyPress="transformaData(this, event);" onKeyUp="autoTab(this,'datafim_dev', 10);" /> <span class="labels">até</span> 
	              <input type="text" name="datafim_dev" id="datafim_dev" class="caixa" size="12" maxlength="10" onKeyPress="transformaData(this, event);" />
	              </div></td>
	            </tr>
	          </table>          
	          </td>
	        </tr>
	        
	        <tr>
	          <td class="fundo_azul"> </td>
	          <td colspan="2" class="<smarty>$classe</smarty>"> </td>
	        </tr>
	      </table>
	</form>
</div>
<smarty>include file="../../templates_erp/footer.tpl"</smarty>