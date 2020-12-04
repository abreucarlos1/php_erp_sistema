<div class='divCadastroCandidatoLinha first'>
	<label class='labels'>Tem disponibilidade para viagens?</label><br />
	<div class='divCadastroCandidato'>
		<label class='labels'>Sim</label>
		<input type='radio' class='caixa' id='informacoes_adicionais[disp_viagens]' name='informacoes_adicionais[disp_viagens]' value='1' <smarty>if $post["informacoes_adicionais"]["disp_viagens"] == 1</smarty>checked="checked"<smarty>/if</smarty> />
	</div>
	
	<div class='divCadastroCandidato'>
		<label class='labels'>Não</label>
		<input type='radio' class='caixa' id='informacoes_adicionais[disp_viagens]' name='informacoes_adicionais[disp_viagens]' value='0' <smarty>if $post["informacoes_adicionais"]["disp_viagens"] == 0</smarty>checked="checked"<smarty>/if</smarty> />
	</div>
</div>

<div class='divCadastroCandidatoLinha'>
	<label class='labels'>Tem disponibilidade para trabalhar em outras cidades?</label><br />
	<div class='divCadastroCandidato'>
		<label class='labels'>Sim</label>
		<input type='radio' class='caixa' id='informacoes_adicionais[trab_outras_cid]' name='informacoes_adicionais[trab_outras_cid]' value='1' <smarty>if $post["informacoes_adicionais"]["trab_outras_cid"] == 1</smarty>checked="checked"<smarty>/if</smarty> />
	</div>
	
	<div class='divCadastroCandidato'>
		<label class='labels'>Não</label>
		<input type='radio' class='caixa' id='informacoes_adicionais[trab_outras_cid]' name='informacoes_adicionais[trab_outras_cid]' value='0' <smarty>if $post["informacoes_adicionais"]["trab_outras_cid"] == 0</smarty>checked="checked"<smarty>/if</smarty> />
	</div>
</div>

<div class='divCadastroCandidatoLinha'>
	<label class='labels'>Tem disponibilidade para trabalhar em turnos?</label><br />
	<div class='divCadastroCandidato'>
		<label class='labels'>Sim</label>
		<input type='radio' class='caixa' id='informacoes_adicionais[trab_turno]' name='informacoes_adicionais[trab_turno]' value='1' <smarty>if $post["informacoes_adicionais"]["trab_turno"] == 1</smarty>checked="checked"<smarty>/if</smarty> />
	</div>
	
	<div class='divCadastroCandidato'>
		<label class='labels'>Não</label>
		<input type='radio' class='caixa' id='informacoes_adicionais[trab_turno]' name='informacoes_adicionais[trab_turno]' value='0' <smarty>if $post["informacoes_adicionais"]["trab_turno"] == 0</smarty>checked="checked"<smarty>/if</smarty> />
	</div>
</div>

<div class='divCadastroCandidatoLinha'>
	<label class='labels'>Utiliza vale transporte?</label><br />
	<div style='float: left; width:100%;'>
		<div class='divCadastroCandidato'>
			<label class='labels'>Sim</label>
			<input type='radio' class='caixa' id='informacoes_adicionais[vale_transp]' name='informacoes_adicionais[vale_transp]' value='1' <smarty>if $post["informacoes_adicionais"]["vale_transp"] == 1</smarty>checked="checked"<smarty>/if</smarty> />
		</div>
		
		<div class='divCadastroCandidato'>
			<label class='labels'>Não</label>
			<input type='radio' class='caixa' id='informacoes_adicionais[vale_transp]' name='informacoes_adicionais[vale_transp]' value='0' <smarty>if $post["informacoes_adicionais"]["vale_transp"] == 0</smarty>checked="checked"<smarty>/if</smarty> />
		</div>
	</div>
	
	<label class='labels'>Qtd. passagens por dia</label><br />
	<div style='float: left; width:100%;'>
		<div class='divCadastroCandidato'>
			<label class='labels'>Ida</label>
			<input type='text' size="9" class='caixa' id='informacoes_adicionais[qtd_vt_ida]' name='informacoes_adicionais[qtd_vt_ida]' value='<smarty>$post["informacoes_adicionais"]["qtd_vt_ida"]</smarty>' />
		</div>
		
		<div class='divCadastroCandidato'>
			<label class='labels'>Volta</label>
			<input type='text' size="9" class='caixa' id='informacoes_adicionais[qtd_vt_volta]' name='informacoes_adicionais[qtd_vt_volta]' value='<smarty>$post["informacoes_adicionais"]["qtd_vt_volta"]</smarty>' />
		</div>
	</div>
	
	<div style='float: left; width:100%;'>
		<div class='divCadastroCandidato'>
			<label class='labels'>Valor por passagem ida</label>
			<input type='text' size="15" class='caixa _currency' id='informacoes_adicionais[val_vt_ida]' name='informacoes_adicionais[val_vt_ida]' value='<smarty>$post["informacoes_adicionais"]["val_vt_ida"]</smarty>' />
		</div>
		
		<div class='divCadastroCandidato'>
			<label class='labels'>Volta</label>
			<input type='text' size="15" class='caixa _currency' id='informacoes_adicionais[val_vt_volta]' name='informacoes_adicionais[val_vt_volta]' value='<smarty>$post["informacoes_adicionais"]["val_vt_volta"]</smarty>' />
		</div>
	</div>
</div>