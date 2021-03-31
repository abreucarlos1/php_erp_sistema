<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width:100%; height:700px;">
<form name="frm" id="frm" method="POST" action="<smarty>$smarty.server.PHP_SELF</smarty>">
	<table width="100%" border="0">               
        <tr>
        	<td width="116" valign="top" class="espacamento">
        		<table border="0" width="100%">
        			<tr>
        				<td valign="middle">
        					<input name="btninserir" type="button" class="class_botao" id="btninserir" disabled="disabled" onclick="inserir_banco();" value="<smarty>$botao[1]</smarty>"  /></td>
					</tr>
        			<tr>
        				<td valign="middle">
        					<input name="btnimprimir" type="button" class="class_botao" id="btnimprimir" onclick="popupUp();" value="<smarty>$botao[5]</smarty>" /></td>
					</tr>
        			<tr>
        				<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="<smarty>$botao[2]</smarty>" onclick="history.back();" /></td>
					</tr>
        			<tr>
        			  <td valign="middle"><label for="periodo" class="labels">Período</label><br />
                      <select name="periodo" class="caixa" id="periodo" onkeypress="return keySort(this);" onchange="xajax_atualizatabela(xajax.getFormValues('frm'));">
						<smarty>html_options values=$option_periodo_values selected=$mesano output=$option_periodo_output</smarty>
		            </select>
                      </td>
      			  </tr>
       			</table>
		  </td>
        	<td colspan="2" valign="top" class="espacamento">
            <table border="0" width="100%">
                <tr>
                  <td><label class="labels"><smarty>$campo[2]</smarty>  <span style="font-size:12px; font-weight:bold;">
					<smarty>$nome_funcionario</smarty>
	              </span></label></td>
              </tr>
              </table>
		  	<table border="0" width="100%">
				<tr>
					<td width="10%">
                    		<label for="data" class="labels">Data (*)</label><br />
							<input name="data" type="text" class="caixa" id="data" size="10" value="<smarty>$smarty.now|date_format:'%d/%m/%Y'</smarty>" onkeypress="transformaData(this, event);"  onblur="if(verificaDataErro(this.value)){xajax_periodos(xajax.getFormValues('frm'));xajax_saldo_catraca(xajax.getFormValues('frm'));}else{this.value='';}" /> 
                            <input type="hidden" name="externo" id="externo" value="<smarty>$externo</smarty>" />
                            <input type="hidden" name="codfuncionario" id="codfuncionario" value="<smarty>$cod_funcionario</smarty>" />
							<input type="hidden" name="id_horas" id="id_horas" value="" />
                        	<input type="hidden" name="preenchido" id="preenchido" value="1" />
                        </td>
					<td width="90%"><label for="os" class="labels">OS (*)</label><br />
					<select name="os" class="caixa" id="os" onChange="xajax_tarefas(xajax.getFormValues('frm'));liberaCampoProjeto(this.value);" onkeypress="return keySort(this);">
						<smarty>html_options values=$option_values output=$option_output</smarty>
		            </select>
                      </td>
					</tr>
			</table>
		  <table border="0" width="100%">
		    <tr>
		      <td width="23%"><label for="disciplina" class="labels">Disciplina (*)</label><br />
                      <select name="disciplina" class="caixa" id="disciplina" onfocus="xajax_calcula_horas(xajax.getFormValues('frm'));" onchange="xajax_periodos(xajax.getFormValues('frm'));xajax_saldo_catraca(xajax.getFormValues('frm'));saldo_horas.focus();" onblur="xajax_calcula_horas(xajax.getFormValues('frm'));" onkeypress="return keySort(this);" >
                          <option value="">ESCOLHA A TAREFA</option>
              
                      </select>
              </td>
               <td>
					<label class="labels">Interna</label><input type="radio" name="rdoInternoExterno" id="rdoInterno" value="0" />
					<label class="labels">Externa</label><input type="radio" name="rdoInternoExterno" id="rdoExterno" value="1" />
               </td>
               <!--
		      <td>             
                  <div id="div_justificativa" style="visibility:hidden">
                  <label for="justificativa" class="labels">Justificativa*</label>	
                    <input name="justificativa" type="text" class="caixa" id="justificativa" value="" size="80" maxlength="150" onblur="xajax_periodos(xajax.getFormValues('frm'));">
                  </div>             
              </td>
              -->
	        </tr>
		    </table>
		  <table border="0" width="100%">
		    <tr>
		      <td width="10%"><label for="saldo_horas" class="labels"><smarty>$campo[6]</smarty></label><br />
              	<input name="saldo_horas" type="text" id="saldo_horas" value="0" size="10" readonly="readonly" class="caixa" /></td>
 		      <td width="10%"><label for="saldo_disciplina" class="labels">Saldo tarefa</label><br />
                <input name="saldo_disciplina" type="text" id="saldo_disciplina" value="0" size="10" readonly="readonly" class="caixa" /></td>
              <td width="10%"><label for="horas_apontadas" class="labels"><smarty>$campo[7]</smarty></label><br />
                <input name="horas_apontadas" type="text"  class="caixa" id="horas_apontadas" value="0" size="10" readonly="readonly" /></td>
		      <td width="70%"><label for="horas_aprovadas" class="labels"><smarty>$campo[8]</smarty></label><br />
              	<input name="horas_aprovadas" type="text"  class="caixa" id="horas_aprovadas" value="0" size="10" readonly="readonly" /></td>
	        </tr>
		    </table>
            <table width="100%" border="0">
              <tr>
                <td width="10%"><label class="labels"><smarty>$campo[9]</smarty>*</label><br />
                    <div id="inicial"> </div></td>
                <td width="11%"><label class="labels"><smarty>$campo[10]</smarty>*</label><br />
                    <div id="final"> </div></td>
                <td width="11%"><label for="qtd_horas" class="labels"><smarty>$campo[11]</smarty></label><br />
               		<input name="qtd_horas" type="text" class="caixa" id="qtd_horas" value="0" size="5" maxlength="20" disabled="disabled">
                </td>               
                <td width="68%"><label for="horas_disp" id="lb_hrs_disp" class="labels" style="visibility:inline"><smarty>$campo[12]</smarty></label><br />
                	<input name="horas_disp" type="text"  class="caixa" id="horas_disp" value="0" size="10" readonly="readonly" style="visibility:inline" />
                </td>                
                <!--
                <td width="8%" class="td_sp"><label class="labels">
		        <smarty>$campo[13]</smarty></label>
              <input type="checkbox" name="retrabalho" id="retrabalho" title="horas de retrabalho" value="1" /></td>
              -->
              </tr>
            </table> 
            <div id="local_trabalho" style="<smarty>$style</smarty>">
			    <table width="100%" border="0">
                  <tr>
                    <td width="1%"><label for="local" class="labels"><smarty>$campo[14]</smarty></label><br />					  
					  <select name="local" class="caixa" id="local" onkeypress="return keySort(this);">
						<smarty>html_options values=$option_local_values output=$option_local_output</smarty>
		            </select> 
                    </td>
                  </tr>
                </table>
            </div>
            <table border="0" width="100%">
              <tr>
                <td width="5%"><label class="labels">Complemento/Tarefa</label><br />
                	<!-- <input name="complemento" type="hidden" class="caixa" id="textarea" value="" maxlength="150"-->
                	<div id="txtAutocomplete"> </div>
              </tr>
              <tr>
                <td id="tdOrcamento" style="display:none;"><label for="orcamento" class="labels">Projeto</label><br />
					<select name="orcamento" class="caixa" id="orcamento" onkeypress="return keySort(this);">
						<smarty>html_options values=$option_orcamento_values output=$option_orcamento_output</smarty>
	            	</select>
				</td>
              </tr>
          </table>
           </td>
        </tr>
      </table>
	  <!-- <div id="controlehoras" style="scrollbar-face-color : #AAAAAA; scrollbar-highlight-color : #AAAAAA; scrollbar-3dlight-color : #ffffff; scrollbar-shadow-color : #FFFFFF; scrollbar-darkshadow-color : #FFFFFF; scrollbar-track-color : #FFFFFF; scrollbar-arrow-color : #FFFFFF;"> </div> -->
      <div id="controlehoras" style="width:100%"> </div>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>