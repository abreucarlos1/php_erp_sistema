<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width:100%;height:700px;">
<form name="frm_rel" id="frm_rel" action="relatorios/rel_hh_medicao.php" method="POST">
	<table width="100%" border="0">        
        <tr>
          <td width="116" rowspan="2" valign="top" class="espacamento">
		  <table width="100%" border="0">
				<tr>
					<td valign="middle"><input name="btninserir" id="btninserir" type="submit" class="class_botao" value="Gerar relatório" disabled="disabled" /></td>
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
					<td colspan="3" align="left"><label class="labels">Período</label></td>
	      		</tr>
        </table>
        <table width="100%" border="0">
				<tr>
					<td width="9%"><label for="mes" class="labels">Mês</label><br />
						<select name="mes" class="caixa" id="mes" onkeypress="return keySort(this);">
						<smarty>html_options values=$option_per_values selected=$option_per_id output=$option_per_output</smarty>
						</select>
                    </td>
                    <td width="9%"><label for="ano" class="labels">Ano</label><br />
						<select name="ano" class="caixa" id="ano" onkeypress="return keySort(this);">
						<smarty>html_options values=$option_ano_values selected=$option_ano_id output=$option_ano_output</smarty>
						</select>
                        					
                     </td>                     
				  <td width="82%"><label class="labels">Local de trabalho</label><br />
                    <select name="local_trabalho" class="caixa" id="local_trabalho">
                      <option value="">TODOS</option>				   
                      <smarty>html_options values=$option_local_values output=$option_local_output</smarty>
                    </select>
                  </td>
                     
				</tr>
          </table>
          <table width="100%" border="0">
				<tr>

		  </tr>
          </table>
          <table width="100%" border="0">
          <tr valign="top">
				<td width="38%"><label class="labels">Equipe</label>
                  <smarty>$check_equipe</smarty>
                 
				</td>
				  <td width="62%">
                  <smarty>$check_contrato</smarty></td>
          </tr>
		  </table></td>
        </tr>
      </table>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>