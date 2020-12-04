<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width: 100%; height: 700px">
<form name="frm_rel" id="frm_rel" action="relatorios/rel_horas_clientes.php" method="POST" style="margin:0px; padding:0px;">
	<table width="100%" border="0">        
        <tr>
          <td width="116" rowspan="2" valign="top" class="espacamento">
		  <table width="100%" border="0">
				<tr>
					<td valign="middle"><input name="btninserir" id="btninserir" type="submit" class="class_botao" value="Gerar relatório" disabled="disabled" /></td>
				</tr>
				<tr>
					<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onClick="history.back();" /></td>
				</tr>
			</table></td>
        </tr>        
        <tr>
          <td colspan="2" valign="top" class="espacamento">
          <table width="100%" border="0">
			  <tr>
					<td colspan="2" width="8%"><label for="mes" class="labels">Período</label><br />
 						<select name="mes" class="caixa" id="mes" onkeypress="return keySort(this);">
						<smarty>html_options values=$option_per_values selected=$option_per_id output=$option_per_output</smarty>
						</select>	
                    </td>
	      	</tr>
				<tr>
				  <td colspan="2"><label class="labels">Local de Trabalho</label><br />
                   <smarty>$check_local</smarty>
                  </td>
                </tr>
		  </table></td>
        </tr>
      </table>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>