<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width: 100%; height: 700px">
<form name="frm_rel" id="frm_rel" action="relatorios/rel_controle_os_status.php" method="POST" style="margin:0px; padding:0px;">
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
					<td><label for="statsu" class="labels">STATUS</label><br />
						<select name="status" class="caixa" id="status" onkeypress="return keySort(this);">
						<option value="-1">TODAS</option>
                        <smarty>html_options values=$option_status_values output=$option_status_output</smarty>
						</select>
                    </td>
				</tr>
				<tr>
					<td><p>
						<label class="labels"><input type="radio" name="formato" value="0" id="formato_0" checked="checked" />PDF</label>
						<br />
						<label class="labels"><input type="radio" name="formato" value="1" id="formato_1" />Excel</label>
						<br />
					</p></td>
				</tr>
		  </table></td>
        </tr>
      </table>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>