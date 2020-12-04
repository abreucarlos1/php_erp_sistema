<form id="frmCadastrarTarefa" name="frmCadastrarTarefa">
	<table cellspacing="10">
	<tr>
		<th align="right"><label class="labels">ID:</label></th>
		<td>
			<input type="text" value="<smarty>$registro['id_gmud']</smarty>" readonly="readonly" id="id_gmud" name="id_gmud" class="caixa" />
			<input type="hidden" id="id_gmudt" name="id_gmudt" />
		</td>
	</tr>
	
	<tr>
		<th align="right"><label class="labels">Titulo:</label></th>
		<td><label class="labels"><smarty>$registro['titulo_gmud']</smarty></label>
	</tr>
	
	<tr>
		<th align="right"><label class="labels">Descrição:</label></th>
		<td><label class="labels"><smarty>$registro['descricao_gmud']</smarty></label>
	</tr>
	</table>
	
	<hr />

	<table width="100%">
		<caption>Tarefas a serem realizadas</caption>
		<!-- <tr>
			<td align="right" class="labels">Título da tarefa</td>
			<td><input type="text" id="tituloTarefa" name="tituloTarefa" size="50" /></td>
		</tr>-->
		<tr>
			<td width="170px" style="text-align:right;" valign="top" class="labels">Descrição da tarefa</td>
			<td><textarea rows="2" cols="82" id="descTarefa" name="descTarefa" class="caixa"></textarea></td>
		</tr>
		<tr>
			<td class="labels" style="text-align:right;">Status</td>
			<td>
				<select id="selStatusGmudt" name="selStatusGmudt" class="caixa">
					<option value="1">Não iniciada</option>
					<option value="2">Iniciada</option>
					<option value="3">Concluída</option>
					<option value="4">Cancelada</option>
				</select>
			</td>
		</tr>
		<tr>
			<td style="text-align:right;" class="labels">Desenvolvedor da Tarefa</td>
			<td>
				<select id="selIdFuncGmudt" name="selIdFuncGmudt" class="caixa">
					<option value="">Selecione</option>
					<option value="6">Carlos Abreu</option>
					<option value="978">Carlos Eduardo</option>
					<option value="3">Hugo Leonardo</option>
				</select>
			</td>
		</tr>
		<tr>
			<td style="text-align:right;" class="labels">Qtd. Horas Necessárias</td>
			<td><input type="text" id="qtd_horas" maxlength="5" name="qtd_horas" class="caixa" onkeyup="return txtBoxFormat(document.frmCadastrarTarefa, 'qtd_horas', '99:99', event);" /></td>
		</tr>
		<tr>
			<td colspan="2" style="text-align:right;">
				<input type="button" value="Gravar Tarefa" onclick="cadastrarTarefa();" class="class_botao" />
				<input type="button" value="TAP" onclick="imprimirTap();" class="class_botao" />
			</td>
		</tr>
	</table>
</form>

<hr />
<table width="100%">
	<caption>Riscos para o projeto</caption>
	<tr><td class="labels">Risco</td><td><textarea id="riscos" class="caixa" name="riscos" cols="86" rows="1"></textarea></td><td class="labels">Grau</td><td>
		<select id="selGraviRisco" name="selGraviRisco" class="caixa">
			<option value="">Selecione</option>
			<option value="0">Alto</option>
			<option value="1">Médio</option>
			<option value="2">Baixo</option>
		</select>
	</td><td><input type="button" id="btnGravarRisco" name="btnGravarRisco" onclick='gravarRiscoProjeto();' value="Gravar Risco" class="class_botao" /></tr>
</table>
<hr />
<div id="divListaTarefasGmud"></div>