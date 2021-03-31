<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width: 100%; height: 700px">
<form name="frm_rel" id="frm_rel" action="relatorios/rel_controle_os_func.php" method="POST" style="margin:0px; padding:0px;">
	<table width="100%" border="0">        
        <tr>
          <td width="116" rowspan="2" valign="top" class="espacamento">
		  <table width="100%" border="0">
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
					<td colspan="3"><label class="labels">PERÍODO</label></td>
					</tr>
				<tr>
					<td colspan="3">
						<table width="100%" border="0">
							<tr>
								<td width="14%"><label for="dataini" class="labels">Data inicial</label><br />
                         		<input name="dataini" type="text" class="caixa" id="dataini" size="10" placeholder="Data ini." onkeypress="transformaData(this, event);" onKeyUp="return autoTab(this,'datafim', 10);" />
                                </td>
							</tr>
							<tr>
								<td><label for="datafim" class="labels">Data final</label><br />
                                <input name="datafim" type="text" class="caixa" id="datafim" size="10" placeholder="Data fin." onkeypress="transformaData(this, event);" onKeyUp="return autoTab(this,'status', 10);"  />
                                </td>
							</tr>
						</table>
                        </td>
					</tr>
				<tr>
					<td colspan="3"><label for="status" class="labels">STATUS</label><br />
						<select name="status" class="caixa" id="status" onchange="xajax_preencheos(this.options[this.options.selectedIndex].value);" onkeypress="return keySort(this);">
						<smarty>html_options values=$option_status_values output=$option_status_output</smarty>
						</select>	
                    </td>
				</tr>
				<tr>
					<td colspan="3"><label for="escolhaos" class="labels">OS</label><br />
						<select name="escolhaos" class="caixa" id="escolhaos" onkeypress="return keySort(this);" onchange="xajax_preenchefunc(this.options[this.selectedIndex].value);">
						</select>
                    </td>
				</tr>
				<tr>
					<td colspan="3"><label for="codfuncionario" class="labels">Funcionário</label><br />
					<select name="codfuncionario" class="caixa" id="codfuncionario" onkeypress="return keySort(this);">
					</select>
                    </td>
				</tr>
				<tr>
					<td width="20%">
						<input name="chk_traslado" type="checkbox" id="chk_traslado" value="1" checked="checked" />
						<label class="labels">Mostrar Traslado</label></td>
					<td width="80%">
						<input name="chk_excel" type="checkbox" id="chk_excel" value="1" />
					<label class="labels">Gerar em Excel</label></td>
				</tr>
				<tr>
				  <td><input name="chk_atividades" type="checkbox" id="chk_atividades" value="1" onclick="seleciona_atividades(this);" /><label class="labels">Por Atividade</label></td>
				  <td><div id="div_atividades" style="visibility:hidden;"><label for="codatividade" class="labels">Atividade</label><br />
                  <select name="codatividade" class="caixa" id="codatividade" onkeypress="return keySort(this);">
                  <smarty>html_options values=$option_atividades_values output=$option_atividades_output</smarty>
                  </select></div></td>
			  </tr>
		  </table></td>
        </tr>
      </table>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>