<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width:100%;height:700px;">
<form name="frm" id="frm" action="relatorios/rel_orcamentacao_excel.php" method="POST" style="margin:0px; padding:0px;">
	<table width="100%" border="0">        
        <tr>
          <td width="116" rowspan="2" valign="top" class="espacamento">
		  <table width="100%" border="0">
				<tr>
					<td valign="middle"><input name="btninserir" id="btninserir" type="submit" class="class_botao" value="Gerar relatório"/></td>
				</tr>
				<tr id="trConfirmar" style="display:none;">
					<td valign="middle"><input name="btnconfirmar" id="btnconfirmar" type="button" class="class_botao" value="Confirmar" onClick="xajax_confirmar_alteracoes(xajax.getFormValues('frm'));" /></td>
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
					<td width="100%" align="left"><label for="selAno" class="labels">ANO</label><br />
						<select name="selAno" class="caixa" id="selAno" onchange="xajax_atualizatabela(xajax.getFormValues('frm'));" onkeypress="return keySort(this);">
						<smarty>html_options values=$option_ano_values output=$option_ano_output</smarty>
						</select>
                    </td>
				</tr>
		  </table>
         </td>
        </tr>
      </table>
</form>
<div id="divLista"></div>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>