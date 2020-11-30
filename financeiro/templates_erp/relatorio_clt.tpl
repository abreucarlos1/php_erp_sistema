<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width:100%; height:700px;">
<form name="frm" id="frm" action="relatorios/rel_fechamentofolha_clt.php" method="POST">
	<table width="100%" border="0">               
        <tr>
        	<td width="116" valign="top" class="espacamento">
        		<table width="100%" border="0">
        			<tr>
        				<td valign="middle">
        					<input name="btninserir" type="button" class="class_botao" id="btninserir" value="Gerar" onclick="gerar_arquivo();" /></td>
					</tr>
        			<tr>
        				<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onclick="history.back();" /></td>
					</tr>
       			</table>
			</td>
        	<td colspan="2" valign="top" class="espacamento">
		  <table border="0" width="100%">
				<tr>
					<td width="9%"><label for="dataini" class="labels">Data&nbsp;inicial</label><br />
                    	<input name="dataini" id="dataini" type="text" size="10" class="caixa" onkeypress="return txtBoxFormat(document.frm, 'dataini', '99/99/9999', event);" onkeyup="return autoTab(this, 10, event);">
                    </td>
					<td width="91%"><label for="datafin" class="labels">Data&nbsp;final</label><br />
                    	<input name="datafin" id="datafin" type="text" size="10" class="caixa" onKeyPress="return txtBoxFormat(document.frm, 'datafin', '99/99/9999', event);" onKeyUp="return autoTab(this, 10, event);">
                    </td>
					</tr>
			</table></td>
        </tr>
      </table>
	  <div id="arquivos" style="width:100%;">&nbsp;</div>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>