<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div style="width:100%;height:660px;">
<form name="frm_sub_grupo" id="frm_sub_grupo" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST">
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td width="116" rowspan="3" valign="top" class="espacamento">
				<table width="100%" cellpadding="0" cellspacing="0">
					<tr>
						<td valign="middle"><input name="btninserir" id="btninserir" type="button" class="class_botao" value="<smarty>$botao[1]</smarty>" onclick="xajax_insere(xajax.getFormValues('frm_sub_grupo'));" /></td>
					</tr>
					<tr>
						<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="<smarty>$botao[2]</smarty>" onclick="history.back();" /></td>
					</tr>
					<tr>
						<td valign="middle"><input name="id_sub_grupo" type="hidden" id="id_sub_grupo" value=""></td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td colspan="2" valign="top" class="borda_alto borda_esquerda">
				<table border="0">
					<tr>
						<td class="td_sp"><label class="labels">C&oacute;digo</label><br />
							<input name="codigo" type="text" class="caixa" id="codigo" size="10" maxlength="2" onkeypress="num_only();" /></td>
						<td class="td_sp"><label class="labels">Sub-grupo</label><br />
							<input name="sub_grupo" type="text" class="caixa" id="sub_grupo" size="50"></td>
					</tr>
				</table>
				<table width="100%" cellspacing="0" cellpadding="0">
					<tr>
						<td class="td_sp"><label class="labels">Busca</label> <input name="busca" type="text" class="caixa" id="busca" onKeyUp="iniciaBusca.verifica(this);" size="50" /></td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
	<div id="sub_grupos" style="width: 100%;"></div>
</form>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>