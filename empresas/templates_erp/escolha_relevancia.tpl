<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width: 100%; height: 700px">
<form name="frm_rel" id="frm_rel" action="relatorios/frq_207.php" method="POST" style="margin:0px; padding:0px;">
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
					<td width="10%" colspan="3" align="left"><label for="escolha_relevancia" class="labels">RELEVÂNCIA DO CLIENTE</label><br />
						<select name="escolha_relevancia" class="caixa" id="escolha_relevancia" onkeypress="return keySort(this);">
						<smarty>html_options values=$option_relevancia_values output=$option_relevancia_output</smarty>
						</select>
                    </td>
					</tr>
				<tr>
					<td colspan="3" align="left"><label for="escolha_decisao" class="labels">PODER DECISÃO DOS CONTATOS</label><br />
						<select name="escolha_decisao" class="caixa" id="escolha_decisao" onkeypress="return keySort(this);">
						<smarty>html_options values=$option_decisao_values output=$option_decisao_output</smarty>
						</select>
                    </td>
				</tr>
		  </table></td>
        </tr>
      </table>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>