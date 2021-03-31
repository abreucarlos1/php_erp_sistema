<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width: 100%; height: auto;">
	<form name="frm" id="frm" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST" style="margin: 0px; padding: 0px;">
		<table width="100%" border="0">
			<tr>
				<td width="116" rowspan="2" valign="top" class="espacamento">
					<table width="100%">
						<tr>
							<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar"	onclick="window.close();" /></td>
						</tr>
					</table></td>
			</tr>
			<tr>
				<td colspan="2" valign="middle">
                <label class="labels"><b>funcionario:</b> <smarty>$funcionario</smarty></label><br />
                 <input name="id_fechamento" type="hidden" id="id_fechamento" value="<smarty>$id_fechamento</smarty>">
				</td>
			</tr>
		</table>
		<div id="documentos"> </div>
	</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>
