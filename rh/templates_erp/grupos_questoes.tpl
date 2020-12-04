<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width: 100%; height: 700px;">
	<form name="frm" id="frm" onsubmit="xajax_salvar_grupo(xajax.getFormValues('frm', true));return false;">
		<iframe id="upload_target" name="upload_target" src="#" style="width: 0; height: 0; border: 0px solid #fff; display: none;"></iframe>
		<table width="100%" border="0">
			<tr>
				<td width="116" valign="top" class="espacamento">
					<table width="100%" border="0">
						<tr>
							<td valign="middle"><input name="btn_inserir" id="btn_inserir" type="submit" class="class_botao" value="Inserir" />
							</td>
						</tr>
						<tr>
							<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onClick="history.back();" />
							</td>
						</tr>
					</table>
				</td>
				<td class="espacamento">
					<table>
						<tr>
							<td><label for="bqg_titulo" class="labels">Titulo&nbsp;Grupo</label><br />
								<input size="120" type="text" id="bqg_titulo" name="bqg_titulo" class="caixa" placeholder="Titulo" />
								<input type="hidden" id="bqg_id" name="bqg_id" />
                            </td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
		
		<div id="div_grupos" style="width:100%;">&nbsp;</div>		
	</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>
