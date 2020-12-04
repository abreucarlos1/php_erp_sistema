<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width: 100%; height: 700px">
<form name="frm_rel" id="frm_rel" action="relatorios/rel_alocacao_recursos_protheus.php" method="POST" style="margin:0px; padding:0px;">
	<table width="100%" border="0">        
        <tr>
          <td width="116" rowspan="2" valign="top" class="espacamento">
		  <table width="100%" border="0">
				<tr>
					<td valign="middle"><input name="btninserir" id="btninserir" type="submit" class="class_botao" value="Gerar relatório"/></td>
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
					<td width="10%"><label for="equipe" class="labels">EQUIPE</label><br />
						<select name="equipe" class="caixa" id="equipe" onchange="xajax_preencherec(this.options[this.options.selectedIndex].value);" onkeypress="return keySort(this);">
						<smarty>html_options values=$option_equipe_values output=$option_equipe_output</smarty>
						</select>
                    </td>
				</tr>
				<tr>
					<td><label for="recurso" class="labels">RECURSO</label><br />
						<select name="recurso" class="caixa" id="recurso" onkeypress="return keySort(this);">
						<smarty>html_options values=$option_recurso_values output=$option_recurso_output</smarty>
						</select>
                    </td>
				</tr>
				<tr>
				  <td><label for="avanco" class="labels">Ignora tarefas concluídas?</label><br />
                  <input type="checkbox" name="avanco" id="avanco" title="Ignora tarefas concluídas?" value="1" />
                  </td>
		    </tr>
		  </table></td>
        </tr>
      </table>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>