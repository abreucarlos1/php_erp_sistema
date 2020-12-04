<table width="100%" class="table" cellspacing="10px">
	{if $dadosAvaliacao['ava_alvo'] == 2}
		<tr>
			<td class="labels">RAZÃO SOCIAL: <label id="tdRazaoSocial" class="tabela_header ">{$tdRazaoSocial}</label></td>
			<td class="labels">CNPJ: <label id="tdCnpj" class="tabela_header ">{$tdCnpj}</label></td>
		</tr>
		<!-- <tr>
			<td class="labels" colspan="2">PERIODO PREST. SERV.: <label class="tabela_header " id="tdPeriodo"></label></td>
		</tr>-->
	{/if}
	<tr>
		<td class="labels">NOME: <label class="tabela_header " id="tdNomeFuncionario">{$tdNomeFuncionario}</label></td>
		
		<td class="labels">DATA AVALIAÇÃO: <label class="tabela_header ">{date('d/m/Y')}</label></td>
	</tr>
	<tr>
		<td colspan="2"  class="labels">DESCRIÇÃO DA FUNÇÃO: <label class="tabela_header " id="tdDescricaoServico">{$tdDescricaoServico}</label></td>
	</tr>
	{if $dadosAvaliacao['ava_alvo'] == 2}
		<tr>
			<td colspan="2" class="labels">CNAE: <label class="tabela_header " id="tdCnae">{$tdCnae}</label></td>
		</tr>
	{/if}
</table>

<input type="hidden" name="avaId" id="avaId" value="{$dadosAvaliacao['ava_id']}" />
<input type="hidden" name="consenso" id="consenso" value="{$consenso}" />
<input type="hidden" name="codFuncionarioConsenso" id="codFuncionarioConsenso" value="{$codFuncionario}" />

<div class="auto_lista" style="margin-top: 10px;height: 480px;overflow: auto;">
	<div class="labels legend">
		<table width="100%" cellspacing="0">
		<tr>
			<th width="70%" colspan="2">ITEM DE AVALIAÇÃO - {$dadosAvaliacao['ava_titulo']}</th>
			{if $autoavaliacao}
				<th width="2%">Resposta</th>
			{/if}
		</tr>
		{counter start=0 skip=1 print=false}
		{foreach $grupos as $k => $grupo}
			<tr>
				<th colspan="6">
					{$grupo}
				</th>
			</tr>
			{foreach $questoes[$k] as $f => $fatores}
				{foreach $fatores as $q => $questao}
					<tr>
						{if isset($questao[5][1])}
							<td rowspan="{$totalFatores[$k][$questao[5][0]]}">{$questao[5][1]}</td>
						{/if}
						<td valign="middle">
							{$questao[0]}
						</td>
						<td align="center" valign="middle">
							<select class="caixa" id="selAutoAvaliacao_{$q}" style="width:200px;" name="selAutoAvaliacao[{$q}]">
								<option value="">Selecione</option>
								{foreach $questao['respostas'] as $idResposta => $resp}
									<option value="{$idResposta}">{$resp['texto']}</option>
								{/foreach}
							</select>
						</td>
					</tr>
				{/foreach}
			{/foreach}	
		{/foreach}
		</table>
	</div>
</div>