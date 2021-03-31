<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width: 100%;height:700px;">
<form name="frm_rel" id="frm_rel" action="relatorios/a1_equivalente.php" method="POST" style="margin:0px; padding:0px;" target="_blank">
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
					<td colspan="3" align="left"><label class="labels">PERÍODO</label></td>
					</tr>
				<tr>
					<td width="12%">
					<table width="63%" border="0">
						<tr>
							<td width="18%" align="right">
									<input name="intervalo" type="radio" value="0" checked="checked" onclick="xajax.$('periodo').style.display='none';" /></td>
							<td width="82%" align="left"><label class="labels">TOTAL</label></td>
						</tr>
						<tr>
							<td align="right">
									<input name="intervalo" type="radio" value="1" onclick="xajax.$('periodo').style.display='inline';xajax.$('dataini').focus();" /></td>
							<td align="left"><label class="labels">PERÍODO</label></td>
						</tr>
				  </table></td>
					<td width="88%"><div id="periodo" style="display:none">
						<table width="100%" border="0">
							<tr>
								<td width="27%"><label for="dataini" class="labels">Data inicial</label><br />
                                <input name="dataini" type="text" class="caixa" id="dataini" size="10" placeholder="Data Ini."  onkeypress="transformaData(this, event);" onkeyup="return autoTab(this,'datafim', 10);" />								
                                </td>
							</tr>
							<tr>
								<td><label for="datafim" class="labels">Data final</label><br />
                                <input name="datafim" type="text" class="caixa" placeholder="Data Fim" id="datafim" size="10"  onkeypress="transformaData(this, event);" onKeyUp="return autoTab(this,'escolhaos', 10);"  />
                                </td>
							</tr>
						</table>
				  </div></td>
				</tr>
				<tr>
				  <td colspan="3"><label for="status" class="labels">Status</label><br />
                     <select name="status" class="caixa" id="status" onkeypress="return keySort(this);" onchange="xajax_seleciona_os(this.options[this.selectedIndex].value);">
                      <smarty>html_options values=$option_status_values output=$option_status_output</smarty>
                    </select>
				  </td>
		    </tr>
				<tr>
					<td colspan="3"><label for="os" class="labels">OS</label><br />
						<select name="os" class="caixa" id="os" onkeypress="return keySort(this);">
						<option value="-1">TODAS AS OS</option>
						</select> 
                    </td>
			    </tr>
				<tr>
				  <td><label class="labels">Disciplina</label>
					<div id="disc"><smarty>$check_box</smarty></div>
                  </td>
			  </tr>
		  </table></td>
        </tr>
      </table>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>