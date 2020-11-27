<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width:100%; height:700px;">
<form name="frm_rel" id="frm_rel" action="relatorios/rel_saldo_disciplinas_protheus.php" method="POST" style="margin:0px; padding:0px;">
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
					<td width="100%" align="left"><label for="escolhacoord" class="labels">COORDENADOR</label><br />
						<select name="escolhacoord" class="caixa" id="escolhacoord" onChange="xajax_preencheos(this.options[this.options.selectedIndex].value);" onkeypress="return keySort(this);">
						<smarty>html_options values=$option_coordenador_values output=$option_coordenador_output</smarty>
						</select>
                    </td>
					</tr>
				<tr>
					<td align="left"><label for="escolhaos" class="labels">OS</label><br />
						<select name="escolhaos" class="caixa" id="escolhaos" onkeypress="return keySort(this);">
						<smarty>html_options values=$option_os_values output=$option_os_output</smarty>
						</select>
                    </td>
				</tr>
				<tr>
				  <td>
                  <smarty>$check_equipe</smarty>
                  </td>
		    </tr>
		  </table></td>
        </tr>
      </table>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>