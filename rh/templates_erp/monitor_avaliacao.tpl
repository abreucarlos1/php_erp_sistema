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
					</tr>
						<tr>
							<td valign="middle"><input name="btnextras" id="btnextras" type="button" class="class_botao" value="Mais Opções" onclick="xajax_modalLiberarAvaliacaoAvulso();" />
							</td>
						</tr>
						<tr>
							<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onclick="history.back();" />
							</td>
						</tr>
					</table>
				</td>
				<td>
					<table>
						<tr>
							<td><label for="selAvaId" class="labels">Avaliação*</label><br />
								<select id="selAvaId" name="selAvaId" class="caixa" onchange="if (this.value!=''){xajax_atualizatabela(this.value);xajax_atualizatabelaCandidatos(this.value);}">
									<smarty>html_options values=$option_ava_values output=$option_ava_output</smarty>
								</select>
                            </td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
		
		<div align="left" id="a_tabbar" mode="top" class="dhtmlxTabBar" style="height: 590px; width: 100%; margin-top: 20px; margin-right: 3px; overflow: auto;">
			<div id="avaliacao" width="100px" name="Colaboradores">
				<div id="div_monitor_avaliacao" style="width: 100%;">
					<input type="hidden" id="avaId" name="avaId" value="1" />					
					<div id="div_monitor"><h3 style="text-align:center;font-family: arial, verdana;">Nenhuma avaliação selecionada</h3></div>
				</div>
			</div>
			<div id="avaliacao_candidatos" width="100px" name="Candidatos">
				<div id="div_monitor_avaliacao_candidatos" style="width: 100%;">
					<div id="div_monitor_candidatos"><h3 style="text-align:center;font-family: arial, verdana;">Nenhuma avaliação selecionada</h3></div>
				</div>
			</div>
		</div>
	</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>