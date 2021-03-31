<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width: 100%; height: 700px;">
	<form name="frm" id="frm" onsubmit="xajax_salvar_pergunta(xajax.getFormValues('frm', true));return false;">
		<iframe id="upload_target" name="upload_target" src="#" style="width: 0; height: 0; border: 0px solid #fff; display: none;"></iframe>
		<!-- width:0;height:0; -->
		<table width="100%" border="0">
			<tr>
				<td width="116" valign="top" class="espacamento">
					<table width="100%" border="0">
						<tr>
							<td valign="middle"><input name="btn_inserir" id="btn_inserir" type="submit" class="class_botao" value="Inserir" />
							</td>
						</tr>
						<tr>
							<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onclick="history.back();" />
							</td>
						</tr>
						<tr>
						<td><label for="busca" class="labels">Busca</label><br />
							<input name="busca" type="text" class="caixa" id="busca" placeholder="Busca" onkeyup="iniciaBusca.verifica(this);" size="15" />
						</td>
					</tr>
					</table>
				</td>
				<td valign="top" class="espacamento">
					<table>
						<tr>
							<td colspan="3" rowspan="2"><label for="bqp_texto" class="labels">Pergunta</label><br />
								<textarea rows="3" cols="63" id="bqp_texto" name="bqp_texto" class="caixa" placeholder="Pergunta"></textarea>
								<input type="hidden" id="bqp_id" name="bqp_id" />
                            </td>
							
							<td valign="top"><label for="bqp_bqg_id" class="labels">Grupo</label><br />
								<select id="bqp_bqg_id" name="bqp_bqg_id" class="caixa" onkeypress="return keySort(this);">
									<smarty>html_options values=$option_grupos_values output=$option_grupos_output</smarty>
								</select>
                            </td>
						</tr>
						<tr>
							<td colspan="3"><label for="bqp_bqf_id" class="labels">Fator</label><br />
								<select id="bqp_bqf_id" name="bqp_bqf_id" class="caixa" onkeypress="return keySort(this);">
									<smarty>html_options values=$option_fator_values output=$option_fator_output</smarty>
								</select>
                            </td>
						</tr>
						<tr>
							<td><label for="bqp_peso" class="labels">Peso</label><br />
                            <input type="text" id="bqp_peso" name="bqp_peso" class="caixa" placeholder="Peso" size="5" />
                            </td>
							<td><label class="labels">Visivel</label><br />
								<input type="radio" id="bqp_atual" name="bqp_atual" value="1" checked="checked" class="caixa"><label class="labels">Sim</label>
								<input type="radio" id="bqp_atual" name="bqp_atual" value="0" class="caixa"><label class="labels">Não</label>
							</td>
                        	<td><label for="bqp_setor_aso" class="labels">Setor Avaliador</label><br />
								<select id="bqp_setor_aso" name="bqp_setor_aso" class="caixa" onkeypress="return keySort(this);">
									<smarty>html_options values=$option_setor_values output=$option_setor_output</smarty>
								</select>
                            </td>
                            <td> </td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
		
		<div align="left" id="a_tabbar" mode="top" class="dhtmlxTabBar" imgpath="../includes/dhtmlx_403/dhtmlxTabbar/codebase/imgs/" margin="3" style="height: 490px; width: 100%; margin-top: 20px; margin-right: 3px;" tabstyle="modern" skinColors="#F1F4F5,#F1F4F5">
			<div id="perguntas" name="Perguntas">
				<div id="div_perguntas" style="margin-top: 10px;"> </div>		
			</div>
			<div id="criterios" width="100px" name="Criterios">
				<input type="hidden" name="bqc_id" id="bqc_id" />
				<table>
				<tr>
					<td valign="top"><label for="bqc_valor" class="labels">Critérios</label><br />
						<select id="bqc_valor" name="bqc_valor" class="caixa" onkeypress="return keySort(this);">
							<option value="">Selecione...</option>
							<option value="1">Abaixo das expectativas</option>
							<option value="2">Atende parcialmente as expectativas</option>
							<option value="3">Atende as expectativas</option>
							<option value="4">Excede parcialmente as expectativas</option>
							<option value="5">Excede completamente as expectativas</option>
							<option value="6">Resposta correta</option>
							<option value="0">N/A</option>
						</select>
                    </td>
                    <td>
                    	<label for="bqc_ordem" class="labels">Ordem</label><br />
                    	<input type="text" id="bqc_ordem" name="bqc_ordem" placeholder="Ordem da resposta" />
                    </td>
				</tr>
				<tr>
					<td valign="top">
						<label for="bqc_descricao" class="labels">Descritivo do critério</label><br />
                        <textarea rows="3" cols="50"  id="bqc_descricao" name="bqc_descricao" placeholder="Descritivo"></textarea>
					</td>
				</tr>
				<tr>
					<td colspan="2">
						<input type="button" name="btnGravarCriterio" class="class_botao" id="btnGravarCriterio" onclick="xajax_salvar_criterio_pergunta(xajax.getFormValues('frm'));" value="Gravar Critério" />
						<input type="button" name="btnCancelarCriterio" class="class_botao" id="btnCancelarCriterio" onclick="limpar_form_criterios();" value="Cancelar" />
					</td>
				</tr>
				</table>
				<div id="div_itens_criterios" style="margin-top: 10px;"> </div>
			</div>
		</div>
		
	</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>
