<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width: 100%; height: 700px;">
<form name="frm" id="frm" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST" style="margin:0px; padding:0px;">
	<table width="100%" border="0">        
        <tr>
          <td width="116" valign="top" class="espacamento">
		  <table width="100%" border="0">
				
				<tr>
					<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onclick="history.back();" /></td>
				</tr>
			</table></td>
          <td colspan="2" valign="top" class="espacamento">
          	<table border="0" width="100%">
				<tr>
					<td width="37%"><label for="busca" class="labels">Busca</label><br />
							<input name="busca" type="text" class="caixa" id="busca" placeholder="Busca" onkeyup="iniciaBusca.verifica(this);" size="50" /></td>
				</tr>
			</table></td>
        </tr>
      </table>
	  <div id="funcionarios" style="width:100%;">&nbsp;</div>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>