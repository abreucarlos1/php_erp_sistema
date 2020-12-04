<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width: 100%; height: 700px;">
<form name="frm_exames" id="frm_exames" action="relatorios/rel_exames_vencidos.php" method="POST" style="margin:0px; padding:0px;">
	<table width="100%" border="0">        
        <tr>
          <td width="116" rowspan="2" valign="top" class="espacamento">
		  <table width="100%" border="0">
				<tr>
					<td valign="middle"><input name="btninserir" id="btninserir" type="submit" class="class_botao" value="Gerar Relatório" /></td>
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
					<td width="7%"><label for="mes" class="labels">Mês</label><br />
					<select name="mes" class="caixa" id="mes" onkeypress="return keySort(this);">
					<smarty>html_options values=$option_mes_values selected=$option_mes_id output=$option_mes_output</smarty>
					</select>
                    </td>
				    <td width="7%"><label for="ano" class="labels">Ano</label><br />
                    <select name="ano" class="caixa" id="ano" onkeypress="return keySort(this);">
                      <smarty>html_options values=$option_ano_values output=$option_ano_output</smarty>
                    </select></td>
				</tr>
		  </table></td>
        </tr>
      </table>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>