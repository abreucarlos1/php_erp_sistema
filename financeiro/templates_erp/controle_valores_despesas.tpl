<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width: 100%; height: 700px">
	<form name="frm_controle_valores_despesas" id="frm_controle_valores_despesas" action="relatorios/rel_valores_despesas.php" method="POST" style="margin: 0px; padding: 0px;">
		<table width="100%" border="0">
			<tr>
				<td width="122" rowspan="2" valign="top" class="espacamento">
					<table width="100%" border="0">
						<tr>
							<td valign="middle"><input name="Button" type="button" class="class_botao" value="Gerar Relatório" onclick="document.forms[0].submit()" /></td>
						</tr>
						<tr>
							<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onclick="history.back();" /></td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td colspan="2" valign="top" class="espacamento">
					<table width="100%" border="0">
						<tr>
							<td width="2%">
								<input name="intervalo" type="radio" value="0" checked="checked" onclick="xajax.$('periodo').style.display='none';">
							</td>
							<td><label class="labels">TOTAL</label></td>
						</tr>
						<tr>
							<td><input name="intervalo" type="radio" value="1" onclick="xajax.$('periodo').style.display='inline';xajax.$('dataini').focus();"></td>
							<td><label class="labels">PERÍODO</label></td>
						</tr>
						<tr>
							<td align="left" colspan="2">
								<div id="periodo" style="display: none">
									<table width="100%" border="0">
										<tr>
											<td width="4%"><label for="dataini" class="labels">DATA&nbsp;INICIAL</label><br />
                                            <input name="dataini" type="text" class="caixa" id="dataini" size="12" onKeyPress="transformaData(this, event);" onKeyUp="return autoTab(this,'datafim', 10);" />
                                            </td>
										</tr>
										<tr>
											<td><label for="datafim" class="labels">DATA&nbsp;FINAL</label><br />
                                            <input name="datafim" type="text" class="caixa" id="datafim" size="12" onKeyPress="transformaData(this, event);" onKeyUp="return autoTab(this,'escolhaos', 10);" />
                                            </td>
										</tr>
									</table>
								</div>
							</td>
						</tr>
						<tr>
							<td colspan="2"><label class="labels">OS</label><br />
                            <select name="escolhaos" class="caixa" id="escolhaos" onkeypress="return keySort(this);">
									<smarty>html_options values=$option_os_values output=$option_os_output</smarty>
							</select>
                            </td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
	</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>
