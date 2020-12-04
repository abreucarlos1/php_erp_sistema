<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width: 100%; height: 700px;">
<form name="frm_aniversariantes" id="frm_aniversariantes" action="" method="POST" style="margin:0px; padding:0px;" target="_blank">
	<table width="100%" border="0">        
        <tr>
          <td width="116" rowspan="2" valign="top" class="espacamento">
		  <table width="100%" cellpadding="0" cellspacing="0">
				<tr>
					<td valign="middle"><input name="btninserir" id="btninserir" type="button" onclick="document.getElementById('frm_aniversariantes').action='relatorios/rel_aniversariantes.php';document.getElementById('frm_aniversariantes').submit();" class="class_botao" value="Gerar PDF" /></td>
				</tr>
				<tr>
					<td valign="middle"><input name="btninserir" id="btninserir" type="button" onclick="document.getElementById('frm_aniversariantes').action='relatorios/rel_aniversariantes_excel.php';document.getElementById('frm_aniversariantes').submit();" class="class_botao" value="Gerar Excel" /></td>
				</tr>
				<tr>
					<td valign="middle" ><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onclick="history.back();" /></td>
				</tr>
		  </table>
		</td>
        </tr>        
        <tr>
          <td colspan="2" valign="top" class="espacamento">
		  <table width="100%" border="0">
				<tr>
					<td width="66%"><label class="labels">M&ecirc;s</label><br />
					<select name="mes" class="caixa" id="mes" onkeypress="return keySort(this);">
					<smarty>html_options values=$option_mes_values selected=$option_mes_id output=$option_mes_output</smarty>
					</select>						
					</td>
				</tr>
			</table></td>
        </tr>
      </table>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>