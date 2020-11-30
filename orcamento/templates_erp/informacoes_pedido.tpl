<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>

<div id="frame" style="width:100%; height:700px;">
<form name="frm" id="frm" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST">
<table width="100%" border="0">               
	<tr>
		<td width="116" valign="top" class="espacamento">
			<table width="100%">
        		<tr>
        			<td valign="middle">
						<input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onClick="history.back();" />
					</td>
				</tr>
			</table>
		</td>
		<td>
		<table>
			<tr>
				<td>
					<label for="busca" class="labels">Busca</label><br />                          
					<input name="busca" id="busca" size="55" type="text" placeholder="Busca" class="caixa" value="" onkeyup="iniciaBusca2.verifica(this);" />
				</td>
			</tr>
		</table>
	</tr>
</table>
<div id="div_grid" style="width:100%;height:630px;">&nbsp;</div>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>