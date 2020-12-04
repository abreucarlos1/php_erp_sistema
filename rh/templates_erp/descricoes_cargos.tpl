<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width: 100%; height: 700px;">
	<form name="frm" id="frm" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST" style="margin: 0px; padding: 0px;">
		<table width="100%" border="0">
			<tr>
				<td width="116" valign="top" class="espacamento">
					<table width="100%" border="0">
						<tr>
							<td valign="middle"><input name="btninserir" type="button" class="class_botao" id="btninserir" onclick="xajax_insere(xajax.getFormValues('frm_conhecimento'));" value="Inserir" />
							</td>
						</tr>
						<tr>
							<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onclick="history.back();" />
							</td>
						</tr>
					</table>
				</td>
				<td valign="top" class="espacamento">
					<table width="100%" border="0">
						<tr>
							<td>
								<label class='labels'>Descrições de cargos por área</label>
							</td>
						</tr>
						<tr>
							<td>
								<div id="div_tree" align="left" style="width:100%; height:600px;"></div>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
	</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>