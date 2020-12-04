<div class='divCadastroCandidatoLinha first'>
	<div class='divCadastroCandidato'>
		<label class='labels'>Empresa</label>
		<input type='text' size="25" class='caixa' id='emprego_anterior[empresa]' name='emprego_anterior[empresa]' value="<smarty>$post['emprego_anterior']['empresa']</smarty>" />
	</div>
	
	<div class='divCadastroCandidato'>
		<label class='labels'>Tel.</label>
		<input type='text' size="15" class='caixa _foneFixo' id='emprego_anterior[fone]' name='emprego_anterior[fone]' value="<smarty>$post['emprego_anterior']['fone']</smarty>" />
	</div>
	
	<div class='divCadastroCandidato'>
		<label class='labels'>Endereço</label>
		<input type='text' size="50" class='caixa' id='emprego_anterior[endereco]' name='emprego_anterior[endereco]' value="<smarty>$post['emprego_anterior']['endereco']</smarty>" />
	</div>
	
	<div class='divCadastroCandidato'>
		<label class='labels'>Cidade</label>
		<input type='text' size="50" class='caixa' id='emprego_anterior[cidade]' name='emprego_anterior[cidade]' value="<smarty>$post['emprego_anterior']['cidade']</smarty>" />
	</div>
	
	<div class='divCadastroCandidato'>
		<label class="labels">UF</label>
		<select name="emprego_anterior[uf]" class="caixa" id="emprego_anterior[uf]" onkeypress="return keySort(this);">
				<option value="">SELECIONE</option>
				<option value="AC" <smarty>if $post['emprego_anterior']['uf'] == 'AC'</smarty>selected="selected"<smarty>/if</smarty>>AC</option>
				<option value="AL" <smarty>if $post['emprego_anterior']['uf'] == 'AL'</smarty>selected="selected"<smarty>/if</smarty>>AL</option>
				<option value="AM" <smarty>if $post['emprego_anterior']['uf'] == 'AM'</smarty>selected="selected"<smarty>/if</smarty>>AM</option>
				<option value="AP" <smarty>if $post['emprego_anterior']['uf'] == 'AP'</smarty>selected="selected"<smarty>/if</smarty>>AP</option>
				<option value="BA" <smarty>if $post['emprego_anterior']['uf'] == 'BA'</smarty>selected="selected"<smarty>/if</smarty>>BA</option>
				<option value="CE" <smarty>if $post['emprego_anterior']['uf'] == 'CE'</smarty>selected="selected"<smarty>/if</smarty>>CE</option>
				<option value="DF" <smarty>if $post['emprego_anterior']['uf'] == 'DF'</smarty>selected="selected"<smarty>/if</smarty>>DF</option>
				<option value="ES" <smarty>if $post['emprego_anterior']['uf'] == 'ES'</smarty>selected="selected"<smarty>/if</smarty>>ES</option>
				<option value="GO" <smarty>if $post['emprego_anterior']['uf'] == 'GO'</smarty>selected="selected"<smarty>/if</smarty>>GO</option>
				<option value="MA" <smarty>if $post['emprego_anterior']['uf'] == 'MA'</smarty>selected="selected"<smarty>/if</smarty>>MA</option>
				<option value="MG" <smarty>if $post['emprego_anterior']['uf'] == 'MG'</smarty>selected="selected"<smarty>/if</smarty>>MG</option>
				<option value="MS" <smarty>if $post['emprego_anterior']['uf'] == 'MS'</smarty>selected="selected"<smarty>/if</smarty>>MS</option>
				<option value="MT" <smarty>if $post['emprego_anterior']['uf'] == 'MT'</smarty>selected="selected"<smarty>/if</smarty>>MT</option>
				<option value="PA" <smarty>if $post['emprego_anterior']['uf'] == 'PA'</smarty>selected="selected"<smarty>/if</smarty>>PA</option>
				<option value="PB" <smarty>if $post['emprego_anterior']['uf'] == 'PB'</smarty>selected="selected"<smarty>/if</smarty>>PB</option>
				<option value="PE" <smarty>if $post['emprego_anterior']['uf'] == 'PE'</smarty>selected="selected"<smarty>/if</smarty>>PE</option>
				<option value="PI" <smarty>if $post['emprego_anterior']['uf'] == 'PI'</smarty>selected="selected"<smarty>/if</smarty>>PI</option>
				<option value="PR" <smarty>if $post['emprego_anterior']['uf'] == 'PR'</smarty>selected="selected"<smarty>/if</smarty>>PR</option>
				<option value="RJ" <smarty>if $post['emprego_anterior']['uf'] == 'RJ'</smarty>selected="selected"<smarty>/if</smarty>>RJ</option>
				<option value="RN" <smarty>if $post['emprego_anterior']['uf'] == 'RN'</smarty>selected="selected"<smarty>/if</smarty>>RN</option>
				<option value="RO" <smarty>if $post['emprego_anterior']['uf'] == 'RO'</smarty>selected="selected"<smarty>/if</smarty>>RO</option>
				<option value="RR" <smarty>if $post['emprego_anterior']['uf'] == 'RR'</smarty>selected="selected"<smarty>/if</smarty>>RR</option>
				<option value="RS" <smarty>if $post['emprego_anterior']['uf'] == 'RS'</smarty>selected="selected"<smarty>/if</smarty>>RS</option>
				<option value="SC" <smarty>if $post['emprego_anterior']['uf'] == 'SC'</smarty>selected="selected"<smarty>/if</smarty>>SC</option>
				<option value="SE" <smarty>if $post['emprego_anterior']['uf'] == 'SE'</smarty>selected="selected"<smarty>/if</smarty>>SE</option>
				<option value="SP" <smarty>if $post['emprego_anterior']['uf'] == 'SP'</smarty>selected="selected"<smarty>/if</smarty>>SP</option>
				<option value="TO" <smarty>if $post['emprego_anterior']['uf'] == 'TO'</smarty>selected="selected"<smarty>/if</smarty>>TO</option>
			</select>
	</div>
		
	<div class='divCadastroCandidato'>
		<label class='labels'>Cargo Exercido</label>
		<input type='text' size="30" class='caixa' id='emprego_anterior[cargo]' name='emprego_anterior[cargo]' value="<smarty>$post['emprego_anterior']['cargo']</smarty>" />
	</div>
	
	<div class='divCadastroCandidato'>
		<label class='labels'>Admissão</label>
		<input type='text' size="15" class='caixa _data' id='emprego_anterior[admissao]' name='emprego_anterior[admissao]' value="<smarty>$post['emprego_anterior']['admissao']|date_format:'%d/%m/%Y'</smarty>" />
	</div>
	
	<div class='divCadastroCandidato'>
		<label class='labels'>Demissão</label>
		<input type='text' size="15" class='caixa _data' id='emprego_anterior[demissao]' name='emprego_anterior[demissao]' value="<smarty>$post['emprego_anterior']['demissao']|date_format:'%d/%m/%Y'</smarty>" />
	</div>
	
	<div class='divCadastroCandidato'>
		<label class='labels'>Salário Inicial</label>
		<input type='text' size="15" class='caixa _currency' id='emprego_anterior[sal_ini]' name='emprego_anterior[sal_ini]' value="<smarty>$post['emprego_anterior']['sal_ini']</smarty>" />
	</div>
	
	<div class='divCadastroCandidato'>
		<label class='labels'>Salário Final</label>
		<input type='text' size="15" class='caixa _currency' id='emprego_anterior[sal_fim]' name='emprego_anterior[sal_fim]' value="<smarty>$post['emprego_anterior']['sal_fim']</smarty>" />
	</div>
</div>

<div class='divCadastroCandidatoLinha'>
	<div class='divCadastroCandidato'>
		<label class='labels'>Descrição sumária das tarefas</label><br />
		<textarea class='caixa maiusculas' id='emprego_anterior[descricao]' name='emprego_anterior[descricao]' cols="100" rows="5"><smarty>$post['emprego_anterior']['descricao']</smarty></textarea>
	</div>
</div>

<div class='divCadastroCandidatoLinha'>
	<div class='divCadastroCandidato'>
		<label class='labels'>Motivo da saída</label><br />
		<textarea class='caixa maiusculas' id='emprego_anterior[mot_saida]' name='emprego_anterior[mot_saida]' cols="100" rows="5"><smarty>$post['emprego_anterior']['mot_saida']</smarty></textarea>
	</div>
</div>