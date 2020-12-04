<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width:100%;height: 700px">
<form name="frm_rel" id="frm_rel" action="relatorios/rel_progresso_fisico_protheus_excel.php" method="POST" style="margin:0px; padding:0px;">
	<table width="100%" border="0">        
        <tr>
          <td width="116" valign="top" class="espacamento">
		  <table width="100%" border="0">
				<tr>
					<td valign="middle" ><input name="btninserir" id="btninserir" type="submit" class="class_botao" value="Gerar relatório"/></td>
				</tr>
				<tr>
					<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onClick="history.back();" /></td>
				</tr>
			</table></td>
          <td colspan="2" valign="top" class="espacamento">
		  <table width="100%" border="0">
				<tr>
					<td align="left"><label for="escolhacoord" class="labels">COORDENADOR</label><br />
						<select name="escolhacoord" class="caixa" id="escolhacoord" onchange="xajax_preencheos(this.value);" onkeypress="return keySort(this);">
						<smarty>html_options values=$option_coordenador_values output=$option_coordenador_output</smarty>
						</select>
                    </td>
					</tr>
				<tr>
					<td align="left"><label class="labels">OS</label><br />
						<select name="escolhaos" class="caixa" id="escolhaos" onkeypress="return keySort(this);">
						</select>
                    </td>
				</tr>
		  </table>
		  </td>
        </tr>
      </table>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>