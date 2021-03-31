<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width:100%; height:700px;">
<form name="frm" id="frm" action="relatorios/rel_wip_excel.php" method="POST">
	<table width="100%" border="0" cellspacing="0" cellpadding="0">               
        <tr>
        	<td width="116" valign="top" class="espacamento">
        		<table width="100%" border="0">
        			<tr>
        				<td valign="middle">
        					<input name="btninserir" type="submit" class="class_botao" id="btninserir" value="Gerar relatório" /></td>
					</tr>
        			<tr>
        				<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onclick="history.back();" /></td>
					</tr>
       			</table>
			</td>
        	<td colspan="2" valign="top" class="espacamento">
            <table width="100%" border="0">
				<tr>
					<td>
						<select name="mes" class="caixa" id="mes" onkeypress="return keySort(this);">
						<smarty>html_options values=$option_per_values selected=$option_per_id output=$option_per_output</smarty>
						</select>
                          
						<select name="ano" class="caixa" id="ano" onkeypress="return keySort(this);">
						<smarty>html_options values=$option_ano_values selected=$option_ano_id output=$option_ano_output</smarty>
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