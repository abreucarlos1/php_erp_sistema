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
							<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onclick="history.back();" />
							</td>
						</tr>
					</table>
				</td>
				<td>
					<label for="selCandidato" class="labels">Candidato*</label><br />
					<select id="selCandidato" name="selCandidato" class="caixa" onchange="if(this.value!=''){xajax_montaAvaliacaoCandidato(this.value)}">
						<option value=''>Selecione</option>
						<smarty>html_options values=$option_cand_values output=$option_cand_output</smarty>
					</select>
				</td>
			</tr>
		</table>
		<div id="avaliacao" width="100px" name="Avaliacao" style="margin-left: 3px; height: 100%; overflow: auto; ">
			<div id="div_avaliacao_perguntas" style="margin-top: 10px;">
				<h3 style="text-align:center;font-family: arial, verdana;">Selecione um candidato para responder a avaliação</h3>
			</div>
		</div>
	</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>
