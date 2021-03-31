<div class='divCadastroCandidato'>
	<table width="100%">
	<caption class='labels'>Duplicar última Linha</label><img src="<smarty>$smarty.const.DIR_IMAGENS</smarty>add.png" style="cursor:pointer" id='btn_add_campo_curso'></caption>
	<tr>
		<td class="td_sp">
			<input type="hidden" id="itens" name="itens" value="0">
			<div id="form_acad" style="width:inherit;">
				<table id="tbl_curso">
					<tr>
						<td><label class="labels">Especialização/Idioma</label></td>
						<td><label class="labels">Instituição</label></td>
						<td><label class="labels">Mês Início</label></td>
						<td><label class="labels">Mês Conclusão</label></td>
						<td><label class="labels">Nível/Domínio</label></td>
					</tr>
					<smarty>foreach from=$post['cursos'] item=cursos</smarty>
					<tr id="tr_curso_<smarty>$cursos['id']</smarty>" class='trCursos'>
						<td>
							<input name="cursos[id][]" type="hidden" class="caixa" id="cursos[id][]" value="<smarty>$cursos['id']</smarty>" />
							<input name="cursos[curso][]" size="25" type="text" class="caixa" id="cursos[curso][]" value="<smarty>$cursos['curso']</smarty>" />
						</td>
						<td><input name="cursos[instituicao_ensino][]" size="25" type="text" class="caixa" id="cursos[instituicao_ensino][]" value="<smarty>$cursos['instituicao_ensino']</smarty>" /></td>
						<td><input name="cursos[mes_inicio][]" type="text" class="caixa _ano" id="cursos[mes_inicio][]" size="15" maxlength="4" value="<smarty>$cursos['mes_inicio']</smarty>" /></td>
						<td><input name="cursos[mes_conclusao][]" type="text" class="caixa _ano" id="cursos[mes_conclusao][]" size="15" maxlength="4" value="<smarty>$cursos['mes_conclusao']</smarty>" /></td>
						<td><input name="cursos[nivel][]" type="text" class="caixa" id="cursos[nivel][]" size="15" onkeypress="num_only();" value="<smarty>$cursos['nivel']</smarty>" /></td>
						<td><img src='<smarty>$smarty.const.DIR_IMAGENS</smarty>apagar.png' style='cursor:pointer;' class="btnExcluirCurso" id="<smarty>$cursos['id']</smarty>" /></td>
					</tr>
					<smarty>/foreach</smarty>
				</table>
			</div>
		</td>
	</tr>
	</table>
</div>