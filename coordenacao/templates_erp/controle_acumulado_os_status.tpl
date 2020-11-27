<smarty>include file="../../templates_erp/header.tpl"</smarty>
<form name="frm_rel" id="frm_rel" action="../planejamento/rel_acumulado_os_status.php" method="POST" style="margin:0px; padding:0px;" target="_blank">
	<table width="100%" border="0" cellspacing="0" cellpadding="0">        
        <tr>
          <td width="116" rowspan="2" valign="top">
		  <table width="100%" cellpadding="0" cellspacing="0">
				<tr>
					<td valign="middle"><input name="btninserir" id="btninserir" type="submit" class="class_botao" value="Gerar relatório"/></td>
				</tr>
				<tr>
					<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onClick="history.back();" /></td>
				</tr>
			</table></td>
          <td width="132" rowspan="2" >&nbsp;</td>
          <td colspan="2">&nbsp;</td>
          <td width="6" rowspan="2" class="<smarty>$classe</smarty>">&nbsp;</td>
        </tr>        
        <tr>
          <td colspan="2" valign="top">
		  <table width="100%" cellspacing="0" cellpadding="0" style="margin-bottom:50px;">
				<tr>
					<td colspan="3" align="left"><label class="labels">PERÍODO</label></td>
					</tr>
				<tr>
					<td width="25%" class="td_sp">
					<table width="63%" border="0">
						<tr>
							<td width="18%" align="right">
									<input name="intervalo" id="intervalo[]" type="radio" value="0" checked="checked" onClick="xajax.$('periodo').style.display='none';" /></td>
							<td width="82%" align="left"><label class="labels">TOTAL</label></td>
						</tr>
						<tr>
							<td align="right">
									<input name="intervalo" id="intervalo[]" type="radio" value="1" onClick="xajax.$('periodo').style.display='inline';xajax.$('dataini').focus();" /></td>
							<td align="left"><label class="labels">PERÍODO</label></td>
						</tr>
					</table></td>
					<td width="28%"><div id="periodo" style="display:none">
						<table width="100%" border="0" cellpadding="0" cellspacing="0">
							<tr>
								<td width="27%" align="right"><label class="labels">Data&nbsp;inicial</label></td>
								<td width="73%" align="left"><input name="dataini" type="text" class="caixa" id="dataini" size="12"  onkeypress="transformaData(this, event);" onKeyUp="return autoTab(this,'datafim', 10);" /></td>
							</tr>
							<tr>
								<td align="right"><label class="labels">Data&nbsp;final</label></td>
								<td align="left"><input name="datafim" type="text" class="caixa" id="datafim" size="12"  onkeypress="transformaData(this, event);" onKeyUp="return autoTab(this,'escolhacoord', 10);"  /></td>
							</tr>
						</table>
					</div></td>
					<td width="47%">&nbsp;</td>
				</tr>
				<tr>
					<td colspan="3" align="left"><label class="labels">COORDENADOR</label></td>
					</tr>
				<tr>
					<td colspan="3" class="td_sp">
						<select name="escolhacoord" class="caixa" id="escolhacoord" onChange="xajax_preencheos(this.options[this.options.selectedIndex].value);" onkeypress="return keySort(this);">
						<smarty>html_options values=$option_coordenador_values output=$option_coordenador_output</smarty>
						</select>						</td>
					</tr>
				<tr>
					<td colspan="3" align="left"><label class="labels">OS</label></td>
				</tr>
				<tr>
					<td colspan="3">
						<select name="escolhaos" class="caixa" id="escolhaos" onkeypress="return keySort(this);">
						<smarty>html_options values=$option_os_values output=$option_os_output</smarty>
						</select>					</td>
				</tr>
				<tr>
				  <td colspan="3">&nbsp;</td>
			  </tr>
				<tr>
				  <td colspan="3" valign="top"><input type="radio" name="chk_p_atividade" id="chk_p_atividade" checked="checked" onclick="xajax.$('frm_rel').action = '../planejamento/rel_acumulado_os_status.php';"><label class="labels">Agrupar por Cargo / Calcular horas por Cargo</label></td>
			  </tr>
				<tr>
				  <td colspan="3" valign="top"><input type="radio" name="chk_p_atividade" id="chk_p_atividade" onclick="xajax.$('frm_rel').action = '../planejamento/rel_acumulado_os_status_atividade.php';"><label class="labels">Agrupar por Cargo / Calcular horas por Atividade</label></td>
			  </tr>
				<tr>
				  <td colspan="3" valign="top"><input type="radio" name="chk_p_atividade" id="chk_p_atividade" onclick="xajax.$('frm_rel').action = '../planejamento/rel_acumulado_os_atividade.php';"><label class="labels">Agrupar por Atividade / Calcular horas por Atividade</label></td>
			  </tr>

		  </table></td>
        </tr>
      </table>
</form>
<smarty>include file="../../templates_erp/footer.tpl"</smarty>