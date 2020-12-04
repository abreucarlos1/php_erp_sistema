<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width: 100%; height: 700px;">
<form name="frm" id="frm" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST" style="margin:0px; padding:0px;">
	<table width="100%" border="0" cellspacing="0" cellpadding="0">                
        <tr>
          <td width="116" valign="top" class="espacamento">
		  <table width="100%" border="0">
				<tr>
					<td valign="middle">
						<input name="btninserir" type="button" class="class_botao" id="btninserir" value="Inserir" onclick="xajax_insere(xajax.getFormValues('frm'));" />					</td>
				</tr>
				<tr>
					<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onclick="history.back();" /></td>
				</tr>
                <tr>
					<td width="16%"><label for="status" class="labels">Visualizar&nbsp;Status</label><br />
                    <select name="status" class="caixa" id="status" onchange="xajax_atualizatabela(xajax.getFormValues('frm'));" onkeypress="return keySort(this);">
						<option value="0" selected="selected">N�O AVALIADO</option>
                        <option value="2">APROVADO</option>
                        <option value="1">N�O APROVADO</option>
                    </select>
					</td>
                </tr>
			</table></td>
          <td colspan="2" valign="top" class="espacamento">
          
          	<table width="100%" border="0" style="<smarty>$display</smarty>">
				<tr>
					<td width="8%"><label for="funcionario" class="labels">funcionario</label><br /> 
						<select name="funcionario" class="caixa" id="funcionario" onkeypress="return keySort(this);" onkeyup="if (event.keyCod == 9){xajax_tarefas_colaborador(xajax.getFormValues('frm'));}" onchange="xajax_tarefas_colaborador(xajax.getFormValues('frm'));xajax_atualizatabela(xajax.getFormValues('frm'));" >
						<smarty>html_options values=$option_funcionario_values output=$option_funcionario_output</smarty>
						</select></td>
			    </tr>
			</table>
            
          	<table width="100%" border="0" style="display:inline;" id='tableOs' class="tableOs">
              <tr>
                <td width="8%"><label for="os" class="labels">Projeto</label><br />
                  <select name="os" class="caixa" id="os" onkeypress="return keySort(this);">
                  <smarty>html_options values=$option_os_values output=$option_os_output</smarty>
                  </select></td>
              </tr>
            </table>
          	<table width="100%" border="0">
              <tr>
                <td width="9%"><label for="data_ini" class="labels">Data&nbsp;inicial</label><br />
                  <input name="data_ini" type="text" class="caixa" id="data_ini" size="10" placeholder="Data ini." maxlength="10" onkeypress="transformaData(this, event);" onblur="verificaDataErro(this.value, this.id);" /></td>
                <td width="8%"><label for="data_fim" class="labels">Data&nbsp;final</label><br />
                  <input name="data_fim" type="text" class="caixa" id="data_fim" size="10" maxlength="10" placeholder="Data fin." onkeypress="transformaData(this, event);" onblur="verificaDataErro(this.value, this.id);" /></td>
                <td width="13%"><label for="hora_ini" class="labels">Hora&nbsp;Inicial</label><br />
                    <select name="hora_ini" class="caixa" id="hora_ini" onkeypress="return keySort(this);" onChange="xajax_hora_ini_fim(xajax.getFormValues('frm'));">
                      <option value="">SELECIONE</option>
                      <option value="0:00">0:00</option>
                      <option value="0:30">0:30</option>
                      <option value="1:00">1:00</option>
                      <option value="1:30">1:30</option>
                      <option value="2:00">2:00</option>
                      <option value="2:30">2:30</option>
                      <option value="3:00">3:00</option>
                      <option value="3:30">3:30</option>
                      <option value="4:00">4:00</option>
                      <option value="4:30">4:30</option>
                      <option value="5:00">5:00</option>
                      <option value="5:30">5:30</option>
                      <option value="6:00">6:00</option>
                      <option value="6:30">6:30</option>
                      <option value="7:00">7:00</option>
                      <option value="7:30">7:30</option>
                      <option value="8:00">8:00</option>
                      <option value="8:30">8:30</option>
                      <option value="9:00">9:00</option>
                      <option value="9:30">9:30</option>
                      <option value="10:00">10:00</option>
                      <option value="10:30">10:30</option>
                      <option value="11:00">11:00</option>
                      <option value="11:30">11:30</option>
                      <option value="12:00">12:00</option>
                      <option value="12:30">12:30</option>
                      <option value="13:00">13:00</option>
                      <option value="13:30">13:30</option>
                      <option value="14:00">14:00</option>
                      <option value="14:30">14:30</option>
                      <option value="15:00">15:00</option>
                      <option value="15:30">15:30</option>
                      <option value="16:00">16:00</option>
                      <option value="16:30">16:30</option>
                      <option value="17:00">17:00</option>
                      <option value="17:30">17:30</option>
                      <option value="18:00">18:00</option>
                      <option value="18:30">18:30</option>
                      <option value="19:00">19:00</option>
                      <option value="19:30">19:30</option>
                      <option value="20:00">20:00</option>
                      <option value="20:30">20:30</option>
                      <option value="21:00">21:00</option>
                      <option value="21:30">21:30</option>
                      <option value="22:00">22:00</option>
                      <option value="22:30">22:30</option>
                      <option value="23:00">23:00</option>
                      
                    </select>                </td>
                <td width="13%"><label for="hora_fim" class="labels">Hora&nbsp;final</label><br />
                  <select name="hora_fim" class="caixa" id="hora_fim" onkeypress="return keySort(this);" onChange="xajax_hora_ini_fim(xajax.getFormValues('frm'));">
                    <option value="">SELECIONE</option>
                    <option value="0:30">0:30</option>
                    <option value="1:00">1:00</option>
                    <option value="1:30">1:30</option>
                    <option value="2:00">2:00</option>
                    <option value="2:30">2:30</option>
                    <option value="3:00">3:00</option>
                    <option value="3:30">3:30</option>
                    <option value="4:00">4:00</option>
                    <option value="4:30">4:30</option>
                    <option value="5:00">5:00</option>
                    <option value="5:30">5:30</option>
                    <option value="6:00">6:00</option>
                    <option value="6:30">6:30</option>
                    <option value="7:00">7:00</option>
                    <option value="7:30">7:30</option>
                    <option value="8:00">8:00</option>
                    <option value="8:30">8:30</option>
                    <option value="9:00">9:00</option>
                    <option value="9:30">9:30</option>
                    <option value="10:00">10:00</option>
                    <option value="10:30">10:30</option>
                    <option value="11:00">11:00</option>
                    <option value="11:30">11:30</option>
                    <option value="12:00">12:00</option>
                    <option value="12:30">12:30</option>
                    <option value="13:00">13:00</option>
                    <option value="13:30">13:30</option>
                    <option value="14:00">14:00</option>
                    <option value="14:30">14:30</option>
                    <option value="15:00">15:00</option>
                    <option value="15:30">15:30</option>
                    <option value="16:00">16:00</option>
                    <option value="16:30">16:30</option>
                    <option value="17:00">17:00</option>
                    <option value="17:30">17:30</option>
                    <option value="18:00">18:00</option>
                    <option value="18:30">18:30</option>
                    <option value="19:00">19:00</option>
                    <option value="19:30">19:30</option>
                    <option value="20:00">20:00</option>
                    <option value="20:30">20:30</option>
                    <option value="21:00">21:00</option>
                    <option value="21:30">21:30</option>
                    <option value="22:00">22:00</option>
                    <option value="22:30">22:30</option>
                    <option value="23:00">23:00</option>
                    <option value="23:30">23:30</option>
                  </select></td>
                <td width="57%"><label for="trabalho" class="labels">Trabalho</label><br />
                  <select name="trabalho" class="caixa" id="trabalho" onkeypress="return keySort(this);">
						<option value="1" selected="selected">DEVEMADA</option>
                        <option value="3">CLIENTE</option>
                        <option value="2">EM CASA</option>
                  </select></td>
              </tr>
            </table>
          	<table width="100%" border="0">
              <tr>
                <td width="16%"><label for="motivo_solicita" class="labels">Motivo da Solicitação</label><br />
                    <input name="motivo_solicita" type="text" class="caixa" id="motivo_solicita" placeholder="Motivo" size="100" maxlength="200" />
                </td>
              </tr>
            </table>
          	<table border="0" width="100%">
				<tr>
                	<!--
					<td width="7%" class="td_sp"><label class="labels">Per&iacute;odo</label>
					  <select name="periodo" class="caixa" id="periodo" onchange="xajax_atualizatabela(xajax.getFormValues('frm'));" onkeypress="return keySort(this);">
                        <smarty>html_options values=$option_per_values selected=$option_per_id output=$option_per_output</smarty>
                      </select>
					</td> -->
                    <!--
					<td width="18%"><label for="busca" class="labels">Busca</label><br />
						<input name="busca" type="text" class="caixa" id="busca" onkeyup="if(this.value.length >= 3){xajax_atualizatabela(xajax.getFormValues('frm'));}" placeholder="Busca" size="25" />
					</td>
                    -->
                    <!--
					<td width="16%"><label for="status" class="labels">Visualizar&nbsp;Status</label><br />
                    <select name="status" class="caixa" id="status" onchange="xajax_atualizatabela(xajax.getFormValues('frm'));" onkeypress="return keySort(this);">
						<option value="0" selected="selected">N�O AVALIADO</option>
                        <option value="2">APROVADO</option>
                        <option value="1">N�O APROVADO</option>
                    </select>
					</td>
                    -->
					<!-- <td width="11%" class="labels"><div id="inicial">&nbsp;</div></td>
				    <td width="55%" class="labels"><div id="final">&nbsp;</div></td> -->
				</tr>
		  </table></td>
        </tr>
      </table>
    <div id="habilitados" style="width:100%;">&nbsp;</div>
    <!-- <div id="gridPaginacao" style="width:100%; text-align:left;">&nbsp;</div> -->
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>