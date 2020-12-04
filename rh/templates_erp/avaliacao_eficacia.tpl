<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width: 100%; height: 700px;">
<form name="frm_aniversariantes" id="frm_aniversariantes" action="relatorios/rel_aniversariantes.php" method="POST" style="margin:0px; padding:0px;" target="_blank">
	<table width="100%" border="0">        
        <tr>
          <td width="116" rowspan="2" valign="top" class="espacamento">
		  <table width="100%" cellpadding="0" cellspacing="0">
				<tr>
					<td valign="middle" ><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="<smarty>$botoes[2]</smarty>" onclick="history.back();" /></td>
				</tr>
		  </table>
		</td>
		<td class="espacamento">
			<div id="divLista"></div>
		</td>
        </tr>
      </table>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>