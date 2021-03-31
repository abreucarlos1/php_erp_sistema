<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width: 100%; height: 700px">
<form name="frm_rel" id="frm_rel" action="relatorios/rel_funcionarios_os_excel.php" method="POST" style="margin:0px; padding:0px;">
	<table width="100%" border="0">        
        <tr>
          <td width="122" rowspan="2" valign="top" class="espacamento">
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
					<td width="10%"><label class="labels">PERÍODO</label></td>
					</tr>
				<tr>
					<td align="left">
						<input name="dataini" type="text" class="caixa" id="dataini" size="10"  onkeypress="transformaData(this, event);" onkeyup="return autoTab(this,'datafim', 10);" /> 
						<label class="labels">á</label> 
						<input name="datafim" type="text" class="caixa" id="datafim" size="10"  onkeypress="transformaData(this, event);" />
					</td>
					</tr>
				<tr>
					<td><label class="labels">TIPO CONTRATAÇÃO</label></td>
					</tr>
				<tr>
					<td>
					<table width="100%" border="0">
						<tr>
							<td width="3%"><input name="tipocontrato" type="radio" value="CLT" checked="checked" /></td>
							<td width="97%"><label class="labels">CLT</label></td>
							</tr>
						<tr>
							<td height="40"><input name="tipocontrato" type="radio" value="EST" /></td>
							<td><label class="labels">ESTÁGIARIOS</label></td>
							</tr>
						<tr>
							<td height="40"><input name="tipocontrato" type="radio" value="SOCIO" /></td>
							<td><label class="labels">SÓCIOS</label></td>
							</tr>
					</table></td>
				</tr>
			</table></td>
        </tr>
      </table>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>