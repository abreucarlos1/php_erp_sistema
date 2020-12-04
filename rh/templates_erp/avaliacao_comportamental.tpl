<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>

<script src="../includes/jquery/jquery.min.js"></script>
<script src="../includes/jquery/jquery-ui-1.11.1/jquery-ui.min.js"></script>

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
				<td>
					<table>
						<tr>
							<td><label class="labels">Colaborador*</label> </td>
							<td>
								<smarty>if $todos_avaliados</smarty>
									<label class="labels">Todos os colaboradores foram avaliados!</label>
								<smarty>else</smarty>
									<select id="selSubId" name="selSubId" class="caixa" onchange="xajax_montaAvaliacao(this.value);">
										<smarty>html_options values=$option_func_values output=$option_func_output</smarty>
									</select>
								<smarty>/if</smarty>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
		
		<div align="left" id="a_tabbar" mode="top" class="dhtmlxTabBar" imgpath="../includes/dhtmlx_403/dhtmlxTabbar/codebase/imgs/" margin="3" style="height: 590px; width: 100%; margin-top: 20px; margin-right: 3px; overflow: auto;" tabstyle="modern" skinColors="#F1F4F5,#F1F4F5">
			<div id="apresentacao" name="Apresentacao">
				<div id="div_apresentacao" name="div_apresentacao" style="padding: 10px;">
					<h3 style="text-align:center;font-family: arial, verdana;">Não há avaliações liberadas no momento</h3>
				</div>
			</div>
			<div id="avaliados" name="Avaliados">
				<div id="div_avaliados" name="div_avaliados"></div>
			</div>
			<div id="avaliacao" width="100px" name="Avaliacao" style="margin-left: 3px; height: 100%; overflow: auto; ">
				<div id="div_avaliacao">
					<input type="hidden" id="avaId" name="avaId" value="1" />
					<div id="div_avaliacao_perguntas"><h3 style="text-align:center;font-family: arial, verdana;">Nenhuma avaliação selecionada</h3></div>
				</div>
			</div>
			<div id="pdi" name="PDI - Programa de Desenvolvimento Individual">
				<div id="div_pdi" name="div_pdi"><h3 style="text-align:center;font-family: arial, verdana;">Nenhum Colaborador selecionado</h3></div>
			</div>
			<div id="metas" name="Metas">
				<div id="div_metas" name="div_metas"><h3 style="text-align:center;font-family: arial, verdana;">Nenhum Colaborador selecionado</h3></div>
			</div>
			<div id="criterios" name="Tabela de Criterios">
				<div id="div_criterios" name="div_criterios"><h3 style="text-align:center;font-family: arial, verdana;">Não há avaliações liberadas no momento</h3></div>
			</div>
		</div>
	</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>
