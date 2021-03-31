<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width: 100%; height: 700px;">
<form name="frm_rel" id="frm_rel" action="relatorios/rel_horas_adicionais_supervisor.php" method="POST" style="margin:0px; padding:0px;">
	<table width="100%" border="0">        
        <tr>
          <td width="116" rowspan="2" valign="top" class="espacamento">
		  <table width="100%">
				<tr>
					<td valign="middle"><input name="btninserir" id="btninserir" type="submit" class="class_botao" value="Gerar relatório"/></td>
				</tr>
				<tr>
					<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onclick="history.back();" /></td>
				</tr>
			</table></td>
        </tr>        
        <tr>
          <td colspan="2" valign="top" class="espacamento">
		  <table width="100%" border="0">
				<tr>
					<td><label class="labels">PERÍODO</label></td>
					</tr>
				<tr>
					<td>
					<table width="100%" border="0">
						<tr>
							<td width="8%"><label class="labels">Mensal</label></td>
							<td width="4%"><input name="intervalo" type="radio" value="mes" checked="checked" onclick="xajax.$('div_mes').style.display='inline';xajax.$('periodo').style.display='none';xajax.$('div_semana').style.display='none';xajax.$('mes').focus();" /></td>
							<td width="88%">
							<div id="div_mes" style="display:inline">
								<select name="mes" class="caixa" id="mes" onkeypress="return keySort(this);">
									<smarty>html_options values=$option_per_values selected=$option_per_id output=$option_per_output</smarty>
								</select><label class="labels">26 - 25</label>
							</div></td>
						</tr>
						<tr>
							<td><label class="labels">Período</label></td>
							<td><input name="intervalo" type="radio" value="periodo" onclick="xajax.$('div_mes').style.display='none';xajax.$('periodo').style.display='inline';xajax.$('div_semana').style.display='none';xajax.$('dataini').focus();" /></td>
							<td>
							<div id="periodo" style="display:none">
							<input name="dataini" type="text" class="caixa" id="dataini" size="10" maxlength="10" placeholder="Data ini." onkeypress="transformaData(this, event);" onkeyup="return autoTab(this,'datafim', 10);" /> <label class="labels">á</label> 
							<input name="datafim" type="text" class="caixa" id="datafim" size="10" maxlength="10" placeholder="Data fin." onkeypress="transformaData(this, event);" />
							</div></td>
						</tr>
						<tr>
							<td><label class="labels">Semana</label></td>
							<td><input name="intervalo" type="radio" value="semana" onclick="xajax.$('div_mes').style.display='none';xajax.$('periodo').style.display='none';xajax.$('div_semana').style.display='inline';xajax.$('semana').focus();" /></td>
							<td>
							<div id="div_semana" style="display:none">
									<input name="semana" type="text" class="caixa" readonly="readonly" value="<smarty>$data</smarty>" size="20"/>
							<a href="NewCal('semana','ddMMYYYY',false,24)"><img src="../imagens/cal.png" width="16" height="16" border="0" alt="Escolha a data" /></a>							</div></td>
						</tr>
					</table></td>
					</tr>
		  </table></td>
        </tr>
      </table>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>