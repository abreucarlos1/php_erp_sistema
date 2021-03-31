<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width:100%; height:700px;">
<form name="frm" id="frm" action="" method="POST" style="margin:0px; padding:0px;">
	<table width="100%" border="0">        
        <tr>
          <td width="116" rowspan="2" valign="top" class="espacamento">
          <table width="100%" cellpadding="0" cellspacing="0">
				<tr>
					<td valign="middle"><input name="btninserir" id="btninserir" type="button" class="class_botao" value="Gerar gráfico" onclick="xajax_grafico(xajax.getFormValues('frm'));"/></td>
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
					<td align="left"><label for="escolhaos" class="labels">OS</label><br />
 						<select name="escolhaos" class="caixa" id="escolhaos" onkeypress="return keySort(this);" onchange="xajax_grafico(xajax.getFormValues('frm_rel'));">
						<smarty>html_options values=$option_os_values output=$option_os_output</smarty>
						</select>
                    </td>
				</tr>
			</table>
            </td>
        </tr>
      </table>
	  <div align="center" id="grafico"> </div>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>