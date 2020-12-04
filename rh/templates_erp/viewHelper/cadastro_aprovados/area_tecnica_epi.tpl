<div class='divCadastroCandidatoLinha first'>
	<div class='divCadastroCandidato'>
		<label class='labels'>Nº Calçado</label>
		<input type='text' class='caixa' id='area_tecnica_epi[num_calcado]' name='area_tecnica_epi[num_calcado]' value="<smarty>$post['area_tecnica_epi']['num_calcado']</smarty>" />
	</div>
	
	<div class='divCadastroCandidato'>
		<label class='labels'>Tamanho Calça</label>
		<input type='text' class='caixa' id='area_tecnica_epi[tam_calca]' name='area_tecnica_epi[tam_calca]' value="<smarty>$post['area_tecnica_epi']['tam_calca']</smarty>" />
	</div>
	
	<div class='divCadastroCandidato'>
		<label class='labels'>Tamanho Jaleco</label>
		<input type='text' class='caixa' id='area_tecnica_epi[tam_jaleco]' name='area_tecnica_epi[tam_jaleco]' value="<smarty>$post['area_tecnica_epi']['tam_jaleco']</smarty>" />
	</div>
	
	<div class='divCadastroCandidato'>
		<label class='labels'>Tamanho Camisa Social</label>
		<input type='text' class='caixa' id='area_tecnica_epi[tam_camisa]' name='area_tecnica_epi[tam_camisa]' value="<smarty>$post['area_tecnica_epi']['tam_camisa']</smarty>" />
	</div>
	
	<div style='float: left; width:100%;'>
		<div class='divCadastroCandidato'>
			<label class='labels'>Óculos Comum</label>
			<input type='radio' class='caixa' id='area_tecnica_epi[tp_oculos]' checked="checked" name='area_tecnica_epi[tp_oculos]' value='1' <smarty>if $post['area_tecnica_epi']['tp_oculos'] == 1</smarty>checked="checked"<smarty>/if</smarty> />
		</div>
		
		<div class='divCadastroCandidato'>
			<label class='labels'>Sobrepor</label>
			<input type='radio' class='caixa' id='area_tecnica_epi[tp_oculos]' name='area_tecnica_epi[tp_oculos]' value='0' <smarty>if $post['area_tecnica_epi']['tp_oculos'] == 0</smarty>checked="checked"<smarty>/if</smarty> />
		</div>
	</div>
</div>