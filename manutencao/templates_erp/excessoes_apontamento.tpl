<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width:100%; height:700px;">
<form name="frm" id="frm" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST">
	<input type="hidden" name="id_exc_cal" id="id_exc_cal" />
	  <table width="100%" border="0">                
        <tr>
        	<td width="116" valign="top" class="espacamento">
        		<table width="100%" border="0">
        			<tr>
        				<td valign="middle">
        					<input name="btninserir" type="button" class="class_botao" onclick="xajax_insere(xajax.getFormValues('frm'));" id="btninserir" value="Inserir" />
        				</td>
					</tr>
					<tr>
        				<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onclick="history.back();" /></td>
					</tr>
       			</table>
				</td>
        	<td colspan="2" valign="top" class="espacamento">
			  	<table>
					<tr>
						<td colspan="5">
							<label for="id_os" class="labels">Selecione uma OS <span style="color:red;">*</span></label><br />
							<select name="id_os" id="id_os" class="caixa" onchange="xajax_funcionarios_os(xajax.getFormValues('frm'));xajax_atualiza_tabela(xajax.getFormValues('frm'));" onkeypress="return keySort(this);">
								<smarty>html_options values=$option_os_values output=$option_os_output</smarty>
							</select>
						</td>
					</tr>
					<tr>
						<td colspan="5">
							<label for="id_funcionario" class="labels">Selecione um Colaborador <span style="color:red;">*</span></label><br />
							<select name="id_funcionario" id="id_funcionario" class="caixa" onkeypress="return keySort(this);"></select>
						</td>
					</tr>
					<tr>	
						<td>
							<label for="data_inicio" class="labels">Data Inicio <span style="color:red;">*</span></label><br />
							<input type="text" name="data_inicio" size="10" maxlength="10" onkeyup="return txtBoxFormat(document.frm, 'data_inicio', '99/99/9999', event);" id="data_inicio" class="caixa" />
						</td>
						<td>
							<label for="data_fim" class="labels">Data Fim <span style="color:red;">*</span></label><br />
							<input type="text" name="data_fim" size="10" maxlength="10" onKeyUp="return txtBoxFormat(document.frm, 'data_fim', '99/99/9999', event);" id="data_fim" class="caixa" />
						</td>
					
						<td>
							<label for="hora_entrada" class="labels">Entrada <span style="color:red;">*</span></label><br />
							<input type="text" maxlength="5" placeholder="Entrada" size="10" onKeyUp="return txtBoxFormat(document.frm, 'hora_entrada', '99:99', event);" name="hora_entrada" id="hora_entrada" class="caixa" />
						</td>
						<td>
							<label for="hora_saida" class="labels">Saída <span style="color:red;">*</span></label><br />
							<input type="text" name="hora_saida" placeholder="Saída" size="10" onKeyUp="return txtBoxFormat(document.frm, 'hora_saida', '99:99', event);" id="hora_saida" class="caixa" />
						</td>
						<td><label for="intervalo" class="labels">Intervalo (minutos) <span style="color:red;">*</span></label><br />
							<input type="text" name="intervalo" placeholder="Intervalo" size="10" value="30"  id="intervalo" class="caixa" /></td>
					</tr>
				</table>
  			</td>
        </tr>
      </table>	  
	  <div id="excessoes" style="width:100%;"> </div>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>