<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width: 100%; height: 700px;">
	<form name="frm" id="frm" onsubmit="xajax_enviarAvaliacao(xajax.getFormValues('frm', true));return false;">
		<iframe id="upload_target" name="upload_target" src="#" style="width: 0; height: 0; border: 0px solid #fff; display: none;"></iframe>
		<table width="100%" border="0">
			<tr>
				<td width="116" valign="top" class="espacamento">
					<table width="100%" border="0">
						<tr>
							<td valign="middle"><input name="btn_inserir" id="btn_inserir" type="submit" class="class_botao" value="Enviar Avaliação" />
							</td>
						</tr>
						<tr>
							<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onClick="history.back();" />
							</td>
						</tr>
					</table>
				</td>
		</table>
		<input type="hidden" id="avaId" name="avaId" value="1" />
		<div align="left" id="a_tabbar" mode="top" class="dhtmlxTabBar" imgpath="../includes/dhtmlx_403/dhtmlxTabbar/codebase/imgs/" margin="3" style="height: 590px; width: 100%; margin-top: 20px; margin-right: 3px; overflow: auto;" tabstyle="modern" skinColors="#F1F4F5,#F1F4F5">
			<div id="apresentacao" name="Apresentacao">
				<div id="div_apresentacao" name="div_apresentacao" style="padding: 10px;">
					<h3 style="text-align:center;font-family: arial, verdana;">Não há avaliações liberadas no momento</h3>
				</div>
			</div>
			<div id="avaliacao" width="100px" name="Avaliacao" style="margin-left: 3px; height: 100%; overflow: auto; ">
				<div id="div_avaliacao_perguntas" style="margin-top: 10px;">
					<h3 style="text-align:center;font-family: arial, verdana;">Não há avaliações liberadas no momento</h3>
				</div>
			</div>
			<div id="avaliados" name="Finalizadas">
				<div id="div_avaliados" name="div_avaliados"></div>
			</div>
			<div id="criterios" name="Tabela de Criterios">
				<div id="div_criterios" name="div_criterios"></div>
			</div>
		</div>
	</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>
