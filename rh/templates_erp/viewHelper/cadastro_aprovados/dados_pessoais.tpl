<div class='divCadastroCandidatoLinha first'>
	<div class='divCadastroCandidato'>
		<label class='labels'>Nome</label>
		<input type='text' size="45" class='caixa' id='funcionario' name='funcionario' value="<smarty>$dados_principais['nome']</smarty>" />
	</div>
	
	<div class='divCadastroCandidato'>
		<label class='labels'>Nacionalidade</label>
		<select name="dados_pessoais[id_nacionalidade]" class="caixa" id="dados_pessoais[id_nacionalidade]" onkeypress="return keySort(this);">
			<option value="">SELECIONE</option>
			<smarty>html_options values=$option_nacionalidade_values output=$option_nacionalidade_output selected=$dados_pessoais['cdp_nacionalidade']</smarty>
		</select>
	</div>
	
	<div class='divCadastroCandidato'>
		<label class="labels">Naturalidade</label>
		<input name="dados_pessoais[naturalidade]" type="text" class="caixa" id="dados_pessoais[naturalidade]" size="35" value="<smarty>$dados_pessoais['cdp_naturalidade']</smarty>" />
	</div>
	
	<div class='divCadastroCandidato'>
		<label class="labels">UF Nasc.</label>
		<select name="dados_pessoais[estado_nasc]" class="caixa" id="dados_pessoais[estado_nasc]" onkeypress="return keySort(this);">
				<option value="">SELECIONE</option>
				<smarty>html_options values=$option_uf_values selected=$dados_pessoais['cdp_uf_nasc'] output=$option_uf_values</smarty>
			</select>
		</div>
		
		<div class='divCadastroCandidato'>
			<label class='labels'>Sexo</label>
			<select class='caixa' id='dados_pessoais[sexo]' name='dados_pessoais[sexo]'>
				<option value='M' <smarty>if $dados_pessoais['cdp_sexo'] == 'M'</smarty>selected="selected"<smarty>/if</smarty>>MASCULINO</option>
				<option value='F' <smarty>if $dados_pessoais['cdp_sexo'] == 'F'</smarty>selected="selected"<smarty>/if</smarty>>FEMININO</option>
			</select>
		</div>
									
		<div class='divCadastroCandidato'>
			<label class='labels'>Idade</label>
			<input type='text' size="2" class='caixa' id='dados_pessoais[idade]' name='dados_pessoais[idade]' value="<smarty>$dados_pessoais['cdp_idade']</smarty>" />
		</div>
		
		<div class='divCadastroCandidato'>
			<label class='labels'>Data Nasc.</label>
			<input type='text' size="10" class='caixa _data' id='dados_pessoais[data_nascimento]' name='dados_pessoais[data_nascimento]' value="<smarty>$dados_pessoais['cdp_data_nasc']|date_format:'%d/%m/%Y'</smarty>" />
		</div>
	</div>
	
	<div class='divCadastroCandidatoLinha'>
		<div class='divCadastroCandidato'>
			<label class='labels'>Endereço</label>
			<input type='text' size="40" class='caixa' id='dados_pessoais[endereco]' name='dados_pessoais[endereco]' value="<smarty>$dados_pessoais['cdp_endereco']</smarty>" />
		</div>
		
		<div class='divCadastroCandidato'>
			<label class='labels'>Bairro</label>
			<input type='text' size="30" class='caixa' id='dados_pessoais[bairro]' name='dados_pessoais[bairro]' value="<smarty>$dados_pessoais['cdp_bairro']</smarty>" />
		</div>
		
		<div class='divCadastroCandidato'>
			<label class='labels'>Cidade</label>
			<input type='text' size="30" class='caixa' id='dados_pessoais[cidade_mora]' name='dados_pessoais[cidade_mora]' value="<smarty>$dados_pessoais['cdp_cidade']</smarty>" />
		</div>
		
		<div class='divCadastroCandidato'>
			<label class="labels">UF</label>
			<select name="dados_pessoais[uf]" class="caixa" id="dados_pessoais[uf]" onkeypress="return keySort(this);">
				<option value="">SELECIONE</option>
				<option value="AC" <smarty>if $dados_pessoais['cdp_uf'] == 'AC'</smarty>selected="selected"<smarty>/if</smarty>>AC</option>
				<option value="AL" <smarty>if $dados_pessoais['cdp_uf'] == 'AL'</smarty>selected="selected"<smarty>/if</smarty>>AL</option>
				<option value="AM" <smarty>if $dados_pessoais['cdp_uf'] == 'AM'</smarty>selected="selected"<smarty>/if</smarty>>AM</option>
				<option value="AP" <smarty>if $dados_pessoais['cdp_uf'] == 'AP'</smarty>selected="selected"<smarty>/if</smarty>>AP</option>
				<option value="BA" <smarty>if $dados_pessoais['cdp_uf'] == 'BA'</smarty>selected="selected"<smarty>/if</smarty>>BA</option>
				<option value="CE" <smarty>if $dados_pessoais['cdp_uf'] == 'CE'</smarty>selected="selected"<smarty>/if</smarty>>CE</option>
				<option value="DF" <smarty>if $dados_pessoais['cdp_uf'] == 'DF'</smarty>selected="selected"<smarty>/if</smarty>>DF</option>
				<option value="ES" <smarty>if $dados_pessoais['cdp_uf'] == 'ES'</smarty>selected="selected"<smarty>/if</smarty>>ES</option>
				<option value="GO" <smarty>if $dados_pessoais['cdp_uf'] == 'GO'</smarty>selected="selected"<smarty>/if</smarty>>GO</option>
				<option value="MA" <smarty>if $dados_pessoais['cdp_uf'] == 'MA'</smarty>selected="selected"<smarty>/if</smarty>>MA</option>
				<option value="MG" <smarty>if $dados_pessoais['cdp_uf'] == 'MG'</smarty>selected="selected"<smarty>/if</smarty>>MG</option>
				<option value="MS" <smarty>if $dados_pessoais['cdp_uf'] == 'MS'</smarty>selected="selected"<smarty>/if</smarty>>MS</option>
				<option value="MT" <smarty>if $dados_pessoais['cdp_uf'] == 'MT'</smarty>selected="selected"<smarty>/if</smarty>>MT</option>
				<option value="PA" <smarty>if $dados_pessoais['cdp_uf'] == 'PA'</smarty>selected="selected"<smarty>/if</smarty>>PA</option>
				<option value="PB" <smarty>if $dados_pessoais['cdp_uf'] == 'PB'</smarty>selected="selected"<smarty>/if</smarty>>PB</option>
				<option value="PE" <smarty>if $dados_pessoais['cdp_uf'] == 'PE'</smarty>selected="selected"<smarty>/if</smarty>>PE</option>
				<option value="PI" <smarty>if $dados_pessoais['cdp_uf'] == 'PI'</smarty>selected="selected"<smarty>/if</smarty>>PI</option>
				<option value="PR" <smarty>if $dados_pessoais['cdp_uf'] == 'PR'</smarty>selected="selected"<smarty>/if</smarty>>PR</option>
				<option value="RJ" <smarty>if $dados_pessoais['cdp_uf'] == 'RJ'</smarty>selected="selected"<smarty>/if</smarty>>RJ</option>
				<option value="RN" <smarty>if $dados_pessoais['cdp_uf'] == 'RN'</smarty>selected="selected"<smarty>/if</smarty>>RN</option>
				<option value="RO" <smarty>if $dados_pessoais['cdp_uf'] == 'RO'</smarty>selected="selected"<smarty>/if</smarty>>RO</option>
				<option value="RR" <smarty>if $dados_pessoais['cdp_uf'] == 'RR'</smarty>selected="selected"<smarty>/if</smarty>>RR</option>
				<option value="RS" <smarty>if $dados_pessoais['cdp_uf'] == 'RS'</smarty>selected="selected"<smarty>/if</smarty>>RS</option>
				<option value="SC" <smarty>if $dados_pessoais['cdp_uf'] == 'SC'</smarty>selected="selected"<smarty>/if</smarty>>SC</option>
				<option value="SE" <smarty>if $dados_pessoais['cdp_uf'] == 'SE'</smarty>selected="selected"<smarty>/if</smarty>>SE</option>
				<option value="SP" <smarty>if $dados_pessoais['cdp_uf'] == 'SP'</smarty>selected="selected"<smarty>/if</smarty>>SP</option>
				<option value="TO" <smarty>if $dados_pessoais['cdp_uf'] == 'TO'</smarty>selected="selected"<smarty>/if</smarty>>TO</option>
			</select>
		</div>
		
		<div class='divCadastroCandidato'>
			<label class='labels'>CEP</label>
			<input type='text' size="9" class='caixa _cep' id='dados_pessoais[cep]' name='dados_pessoais[cep]' value="<smarty>$dados_pessoais['cdp_cep']</smarty>" />
		</div>
		
		<div class='divCadastroCandidato'>
			<label class='labels'>Telefone</label>
			<input type="text" size="15" class="caixa _foneFixo" name="dados_pessoais[telefone]" id="dados_pessoais[telefone]" value="<smarty>$dados_pessoais['cdp_fone']</smarty>" />
		</div>
		
		<div class='divCadastroCandidato'>
			<label class='labels'>Celular</label>
			<input type="text" size="15" class="caixa _cel" name="dados_pessoais[celular]" id="dados_pessoais[celular]" value="<smarty>$dados_pessoais['cdp_cel']</smarty>" />
		</div>
		
		<div class='divCadastroCandidato'>
			<label class='labels'>Fone Recados</label>
			<input type="text" size="15" class="caixa _foneFixo" name="dados_pessoais[foneRecados]" id="dados_pessoais[foneRecados]" value="<smarty>$dados_pessoais['cdp_fone_recados']</smarty>" />
		</div>
		
		<div class='divCadastroCandidato'>
			<label class='labels'>E-Mail</label>
			<input type="text" readonly="readonly" size="30" class="caixa _email" name="dados_pessoais[email]" id="dados_pessoais[email]" value="<smarty>$dados_principais['email']</smarty>" />
		</div>
	</div>
	
	<div class='divCadastroCandidatoLinha'>
		<div class='divCadastroCandidato'>
			<label class="labels">Estado Civil</label>
			<select name="dados_pessoais[estado_civil]" class="caixa" id="dados_pessoais[estado_civil]" onkeypress="return keySort(this);">
				<smarty>html_options values=$option_est_civ_values output=$option_est_civ_output selected=$estado_civil_selecionado</smarty>
			</select>
		</div>
		
		<div class='divCadastroCandidato'>
			<label class='labels'>Data Casam.</label>
			<input type='text' size="10" class='caixa _data' id='dados_pessoais[data_casamento]' name='dados_pessoais[data_casamento]' value="<smarty>$dados_pessoais['cdp_data_casamento']|date_format:'%d/%m/%Y'</smarty>" />
		</div>
	
		<div class='divCadastroCandidato'>
			<label class='labels'>N. Filhos</label>
			<input type='text' size="3" class='caixa' id='dados_pessoais[num_filhos]' name='dados_pessoais[num_filhos]' value="<smarty>$dados_pessoais['cdp_n_filhos']</smarty>" />
		</div>
		
		<div class='divCadastroCandidato'>
			<label class='labels'>Nome Cônjuge</label>
			<input type='text' size="45" class='caixa' id='dados_pessoais[conjuge]' name='dados_pessoais[conjuge]' value="<smarty>$dados_pessoais['cdp_nome_conjuge']</smarty>" />
		</div>
		
		<div class='divCadastroCandidato'>
			<label class='labels'>Nome pai</label>
			<input type='text' size="45" class='caixa' id='dados_pessoais[pai]' name='dados_pessoais[pai]' value="<smarty>$dados_pessoais['cdp_nome_pai']</smarty>" />
		</div>
		
		<div class='divCadastroCandidato'>
			<label class='labels'>Nome Mãe</label>
			<input type='text' size="45" class='caixa' id='dados_pessoais[mae]' name='dados_pessoais[mae]' value="<smarty>$dados_pessoais['cdp_nome_mae']</smarty>" />
		</div>
	</div>
	
	<div class='divCadastroCandidatoLinha'>							
		<div class='divCadastroCandidato'>
			<label class='labels'>Peso</label>
			<input type='text' size="3" class='caixa' id='dados_pessoais[peso]' name='dados_pessoais[peso]' value="<smarty>$dados_pessoais['cdp_peso']</smarty>" />
		</div>
		
		<div class='divCadastroCandidato'>
			<label class='labels'>Tipo Sanguíneo</label>
			<select onkeypress="return keySort(this);" id="dados_pessoais[tipo_sanguineo]" class="caixa" name="dados_pessoais[tipo_sanguineo]">
				<option value="">SELECIONE</option>
				<option value="O+" <smarty>if $dados_pessoais['cdp_tp_sangue'] == 'O+'</smarty>selected="selected"<smarty>/if</smarty>>O+</option>
				<option value="A+" <smarty>if $dados_pessoais['cdp_tp_sangue'] == 'A+'</smarty>selected="selected"<smarty>/if</smarty>>A+</option>
				<option value="B+" <smarty>if $dados_pessoais['cdp_tp_sangue'] == 'B+'</smarty>selected="selected"<smarty>/if</smarty>>B+</option>
				<option value="AB+" <smarty>if $dados_pessoais['cdp_tp_sangue'] == 'AB+'</smarty>selected="selected"<smarty>/if</smarty>>AB+</option>
				<option value="O-" <smarty>if $dados_pessoais['cdp_tp_sangue'] == 'O-'</smarty>selected="selected"<smarty>/if</smarty>>O-</option>
				<option value="A-" <smarty>if $dados_pessoais['cdp_tp_sangue'] == 'A-'</smarty>selected="selected"<smarty>/if</smarty>>A-</option>
				<option value="B-" <smarty>if $dados_pessoais['cdp_tp_sangue'] == 'B-'</smarty>selected="selected"<smarty>/if</smarty>>B-</option>
				<option value="AB-" <smarty>if $dados_pessoais['cdp_tp_sangue'] == 'AB-'</smarty>selected="selected"<smarty>/if</smarty>>AB-</option>
			</select>
		</div>
		
		<div class='divCadastroCandidato'>
			<label class='labels'>Altura</label>
			<input type='text' size="4" class='caixa' id='dados_pessoais[altura]' name='dados_pessoais[altura]' value="<smarty>$dados_pessoais['cdp_altura']</smarty>" />
		</div>
		
		<div class='divCadastroCandidato'>
			<label class='labels'>Etnia</label>
			<input type='text' size="20" class='caixa' id='dados_pessoais[etnia]' name='dados_pessoais[etnia]' value="<smarty>$dados_pessoais['cdp_etnia']</smarty>" />
		</div>
	</div>
	
	<div class='divCadastroCandidatoLinha'>
		<div class='divCadastroCandidato'>
			<label class='labels'>Banco</label>
			<select name="dados_pessoais[banco]" class="caixa" id="dados_pessoais[banco']">
				<smarty>html_options values=$option_bancos_values output=$option_bancos_output selected=$banco_selecionado</smarty>
			</select>
		</div>
		
		<div class='divCadastroCandidato' style='width:20%'>
		<label class="labels">Conta</label>
		<input type="text" size="20" id="" class="caixa" name="dados_pessoais[cc]" id="dados_pessoais[cc]" value="<smarty>$dados_pessoais['cdp_cc']</smarty>">
	</div>
	
	<div class='divCadastroCandidato' style='width:15%'>
		<label class="labels">Agência</label>
		<input name="dados_pessoais[banco_agencia]" type="text" class="caixa" id="dados_pessoais[banco_agencia]" size="10" value="<smarty>$dados_pessoais['cdp_agencia']</smarty>" />
	</div>
	
	<div class='divCadastroCandidato' style='width:20%'>
			<label class="labels">PJ</label>
			<input name="dados_pessoais[tpContrato]" value='PJ' type="checkbox" class="caixa" id="dados_pessoais[tpContrato]" <smarty>if $dados_pessoais['cdp_tp_contrato'] == 'PJ'</smarty>checked='checked'<smarty>/if</smarty> />
			
			<label class="labels">CLT</label>
			<input name="dados_pessoais[tpContrato]" value='CLT' type="checkbox" class="caixa" id="dados_pessoais[tpContrato]" <smarty>if $dados_pessoais['cdp_tp_contrato'] == 'CLT'</smarty>checked='checked'<smarty>/if</smarty> />
		</div>
	</div>