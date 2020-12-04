<div class='divCadastroCandidatoLinha first'>
	<div class='divCadastroCandidato'>
		<label class='labels'>Carteira Profissional nº</label>
		<input type='text' size="20" class='caixa' id='documentos[cpts_num]' name='documentos[ctps_num]' value="<smarty>$post['documentos']['ctps_num']</smarty>" />
	</div>
	
	<div class='divCadastroCandidato'>
		<label class='labels'>Série</label>
		<input type='text' size="15" class='caixa' id='documentos[cpts_serie]' name='documentos[ctps_serie]' value="<smarty>$post['documentos']['ctps_serie']</smarty>" />
	</div>
	
	<div class='divCadastroCandidato'>
		<label class='labels'>Emissão</label>
		<input type='text' size="10" class='caixa _data' id='documentos[ctps_data_emissao]' name='documentos[ctps_data_emissao]' value="<smarty>$post['documentos']['ctps_data_emissao']|date_format:'%d/%m/%Y'</smarty>" />
	</div>
</div>

<div class='divCadastroCandidatoLinha'>	
	<div class='divCadastroCandidato'>
		<label class='labels'>RG Nº</label>
		<input type='text' size="15" class='caixa' id='documentos[identidade_num]' name='documentos[identidade_num]' value="<smarty>$post['documentos']['identidade_num']</smarty>" />
	</div>
	
	<div class='divCadastroCandidato'>
		<label class='labels'>Órgão Emissor</label>
		<input type='text' size="15" class='caixa' id='documentos[identidade_emissor]' name='documentos[identidade_emissor]' value="<smarty>$post['documentos']['identidade_emissor']</smarty>" />
	</div>
	
	<div class='divCadastroCandidato'>
		<label class='labels'>Data Emissão RG</label>
		<input type='text' size="10" class='caixa _data' id='documentos[data_emissao]' name='documentos[data_emissao]' value="<smarty>$post['documentos']['data_emissao']|date_format:'%d/%m/%Y'</smarty>" />
	</div>
	
	<div class='divCadastroCandidato'>
		<label class='labels'>CPF</label>
		<input type='text' size="15" class='caixa _cpf' id='documentos[cpf_num]' name='documentos[cpf_num]' value="<smarty>$post['documentos']['cpf_num']</smarty>" />
	</div>
</div>

<div class='divCadastroCandidatoLinha'>
	<div class='divCadastroCandidato'>
		<label class='labels'>Titulo Eleitor Nº</label>
		<input type='text' size="25" class='caixa' id='documentos[titulo_eleitor]' name='documentos[titulo_eleitor]' value="<smarty>$post['documentos']['titulo_eleitor']</smarty>" />
	</div>
	
	<div class='divCadastroCandidato'>
		<label class='labels'>Zona</label>
		<input type='text' size="15" class='caixa' id='documentos[titulo_zona]' name='documentos[titulo_zona]' value="<smarty>$post['documentos']['titulo_zona']</smarty>" />
	</div>
	
	<div class='divCadastroCandidato'>
		<label class='labels'>Seção</label>
		<input type='text' size="10" class='caixa' id='documentos[titulo_secao]' name='documentos[titulo_secao]' value="<smarty>$post['documentos']['titulo_secao']</smarty>" />
	</div>
	
	<div class='divCadastroCandidato'>
		<label class='labels'>PIS Nº</label>
		<input type='text' size="15" class='caixa' id='documentos[pis_numero]' name='documentos[pis_numero]' value="<smarty>$post['documentos']['pis_numero']</smarty>" />
	</div>
</div>

<div class='divCadastroCandidatoLinha'>
	<div class='divCadastroCandidato'>
		<label class='labels'>Certificado Reservista Nº</label>
		<input type='text' size="25" class='caixa' id='documentos[reservista_num]' name='documentos[reservista_num]' value="<smarty>$post['documentos']['reservista_num']</smarty>" />
	</div>
	
	<div class='divCadastroCandidato'>
		<label class='labels'>Categoria</label>
		<input type='text' size="15" class='caixa' id='documentos[reservista_serie]' name='documentos[reservista_serie]' value="<smarty>$post['documentos']['reservista_serie']</smarty>" />
	</div>
	
	<div class='divCadastroCandidato'>
		<label class='labels'>Cidade</label>
		<input type='text' size="30" class='caixa' id='documentos[cidade]' name='documentos[cidade]' value="<smarty>$post['documentos']['cidade']</smarty>" />
	</div>
</div>

<div class='divCadastroCandidatoLinha'>
	<div class='divCadastroCandidato'>
		<label class='labels'>CNPJ</label>
		<input type='text' size="20" class='caixa _cnpj' id='documentos[cnpj]' name='documentos[cnpj]' value="<smarty>$post['documentos']['cnpj']</smarty>" />
	</div>
	
	<div class='divCadastroCandidato'>
		<label class='labels'>Nome Empresa</label>
		<input type='text' size="40" class='caixa' id='documentos[empresa]' name='documentos[empresa]' value="<smarty>$post['documentos']['empresa']</smarty>" />
	</div>
	
	<div class='divCadastroCandidato'>
		<label class='labels'>Optante</label>
		<select name="documentos[tipo_tributacao]" class="caixa" id="documentos[tipo_tributacao]">
			<option value="">SELECIONE...</option>
			<option value="1" <smarty>if $post['documentos']['tipo_tributacao'] == 1</smarty>selected="selected"<smarty>/if</smarty>>SIMPLES NACIONAL</option>
			<option value="2" <smarty>if $post['documentos']['tipo_tributacao'] == 2</smarty>selected="selected"<smarty>/if</smarty>>LUCRO PRESUMIDO</option>
		</select>
	</div>
</div>