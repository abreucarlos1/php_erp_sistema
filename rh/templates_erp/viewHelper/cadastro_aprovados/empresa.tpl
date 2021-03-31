<div class='divCadastroCandidatoLinha first'>
	<div class='divCadastroCandidato'>
		<label class='labels'>E-Mail Empresa</label>
		<input type='text' class='caixa _email' size='50' id='empresa[email]' name='empresa[email]' value="<smarty>$post['empresa']['email']</smarty>" />
	</div>
	
	<div class='divCadastroCandidato'>
		<label class='labels'>login Empresa</label>
		<input type='text' class='caixa' id='empresa[login]' name='empresa[login]' value="<smarty>$post['empresa']['login']</smarty>" />
	</div>
	
	<div class='divCadastroCandidato'>
		<label class='labels'>Sigla</label>
		<input style='width:40px;' type='text' class='caixa' id='empresa[sigla_func]' name='empresa[sigla_func]' value="<smarty>$post['empresa']['sigla_func']</smarty>" />
	</div>
	
	<input type='hidden' id='data_inicio' name='data_inicio' value="<smarty>$dados_principais['data_inicio']</smarty>" />
	<input type='hidden' id='centro_custo' name='centro_custo' value="<smarty>$dados_principais['centro_custo']</smarty>" />
</div>