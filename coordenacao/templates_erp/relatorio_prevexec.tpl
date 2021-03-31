<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width:100%; height:700px;">
<form name="frm_rel" id="frm_rel" action="../relatorios/rel_proposta_emitidos.php" method="POST" style="margin:0px; padding:0px;">
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
					<td width="25%">
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
					<td width="28%"><div id="periodo" style="display:none">
						<table width="100%" border="0">
							<tr>
								<td width="27%"><label for="dataini" class="labels">Data inicial</label><br />
									<input name="dataini" type="text" class="caixa" id="dataini" size="12"  onkeypress="transformaData(this, event);" onKeyUp="return autoTab(this,'datafim', 10);" />
                                </td>
							</tr>
							<tr>
								<td><label for="datafim" class="labels">Data final</label><br />
                                <input name="datafim" type="text" class="caixa" id="datafim" size="12"  onkeypress="transformaData(this, event);" onKeyUp="return autoTab(this,'escolhacoord', 10);"  />
                                </td>
							</tr>
						</table>
					</div></td>
				</tr>
				<tr>
					<td colspan="3"><label for="CodFuncionario" class="labels">COORDENADOR</label><br />
						<select name="id_funcionario" class="caixa" id="id_funcionario" onChange="xajax_preencheos(this.options[this.options.selectedIndex].value);" onkeypress="return keySort(this);">
						<smarty>html_options values=$option_coordenador_values output=$option_coordenador_output</smarty>
						</select>	
                    </td>
				</tr>
				<tr>
					<td colspan="3"><label for="id_os" class="labels">OS</label><br />
 						<select name="id_os" class="caixa" id="id_os" onkeypress="return keySort(this);">
						<smarty>html_options values=$option_os_values output=$option_os_output</smarty>
						</select>
                    </td>
				</tr>
				<tr>
				  <td colspan="3"><label class="labels">DISCIPLINA</label><br />
					<select name="id_setor" class="caixa" id="id_setor" onkeypress="return keySort(this);">
                    <smarty>html_options values=$option_setores_values output=$option_setores_output</smarty>
                  </select>                  
                  </td>
			  </tr>
		  </table></td>
        </tr>
      </table>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>