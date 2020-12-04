<div class='divCadastroCandidato'>
	<table width="100%">
	<caption class='labels'>Duplicar última Linha</label><img src="<smarty>$smarty.const.DIR_IMAGENS</smarty>add.png" style="cursor:pointer" id='btn_add_campo'></caption>
	<tr>
		<td class="td_sp">
			<input type="hidden" id="itens" name="itens" value="0">
			<div id="form_acad" style="width:inherit;">
				<table id="tbl_formacao">
					<tr>
						<td><label class="labels">Curso/Modalidade</label></td>
						<td><label class="labels">Instituição&nbsp;Ensino</label></td>
						<td><label class="labels">Mês&nbsp;Início</label></td>
						<td><label class="labels">Mês&nbsp;Conclusão</label></td>
						<td><label class="labels">Completo</label></td>
						<td><label class="labels">Até Série</label></td>
					</tr>
					<smarty>foreach from=$post['formacao'] item=formacao</smarty>
					<tr id="tr_<smarty>$formacao['id']</smarty>" class='trFormacao'>
						<td>
							<input name="formacao[id][]" type="hidden" class="caixa" id="formacao[id][]" value="<smarty>$formacao['id']</smarty>" />
							<input name="formacao[curso][]" size="25" type="text" class="caixa" id="formacao[curso][]" value="<smarty>$formacao['curso']</smarty>" />
						</td>
						<td>
						<select style="text-transform: uppercase" name="formacao[instituicao_ensino][]" class="caixa instituicaoEnsino" id="formacao[instituicao_ensino][]" title="instituicao" onkeypress="return keySort(this);" >
							<smarty>html_options values=$option_instituicao_values output=$option_instituicao_output selected=$formacao['instituicao_ensino']</smarty>
						</select>
						</td>
						<td><input name="formacao[mes_inicio][]" type="text" class="caixa _ano" id="formacao[mes_inicio][]" size="15" maxlength="4" value="<smarty>$formacao['mes_inicio']</smarty>" /></td>
						<td><input name="formacao[mes_conclusao][]" type="text" class="caixa _ano" id="formacao[mes_conclusao][]" size="15" maxlength="4" value="<smarty>$formacao['mes_conclusao']</smarty>" /></td>
						<td>
							<select class='caixa' style='width:60px;' name='formacao[completo][]' id='formacao[completo][]'>
								<option value='1' <smarty>if $formacao['completo'] == 1</smarty>selected="selected"<smarty>/if</smarty>>Sim</option>
								<option value='0' <smarty>if $formacao['completo'] == 0</smarty>selected="selected"<smarty>/if</smarty>>Não</option>
							</select>
						</td>
						<td><input name="formacao[ate_serie][]" type="text" class="caixa" id="formacao[ate_serie][]" size="15" onkeypress="num_only();" value="<smarty>$formacao['ate_serie']</smarty>" /></td>
						<td><img src='<smarty>$smarty.const.DIR_IMAGENS</smarty>apagar.png' style='cursor:pointer;' class="btnExcluirFormacao" id="<smarty>$formacao['id']</smarty>" /></td>
					</tr>
					<smarty>/foreach</smarty>
				</table>
			</div>
		</td>
	</tr>
	</table>
</div>