<table width="100%" class="table" cellspacing="10px">
	{if $dadosAvaliacao['ava_alvo'] == 2}
		<tr>
			<td class="labels">RAZÃO SOCIAL: <label id="tdRazaoSocial" class="tabela_header ">{$tdRazaoSocial}</label></td>
			<td class="labels">CNPJ: <label id="tdCnpj" class="tabela_header ">{$tdCnpj}</label></td>
		</tr>
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
				<th width="2%">AA</th>
			{else}
				{if $consenso}
					<th width="2%">AA</th>
					<th width="2%">ACI</th>
					<th width="2%">Consenso</th>
				{else}
					<th width="2%">ACI</th>
				{/if}
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
						{if $autoavaliacao}
							<td align="center" valign="middle">
								<select class="caixa" id="selAutoAvaliacao_{$q}" name="selAutoAvaliacao[{$q}]">
									<option value="0" 	{if $questao[3] == 0}	selected="selected"{/if}>N/A</option>
									<option value="1" {if $questao[3] == 1}	selected="selected"{/if}>1</option>
									<option value="2" 	{if $questao[3] == 2}	selected="selected"{/if}>2</option>
									<option value="3" {if $questao[3] == 3}	selected="selected"{/if}>3</option>
									<option value="4" 	{if $questao[3] == 4}	selected="selected"{/if}>4</option>
									<option value="5" 	{if $questao[3] == 5}	selected="selected"{/if}>5</option>
								</select>
							</td>
						{else}
							{if $consenso}
								<td align="center" valign="middle">
									<select class="caixa" {if $consenso}disabled="disabled"{/if} id="selAutoAvaliacao_{$q}" name="selAutoAvaliacao[{$q}]">
										<option value="0" 	{if $questao[3] == 0}	selected="selected"{/if}>N/A</option>
										<option value="1" {if $questao[3] == 1}	selected="selected"{/if}>1</option>
										<option value="2" 	{if $questao[3] == 2}	selected="selected"{/if}>2</option>
										<option value="3" {if $questao[3] == 3}	selected="selected"{/if}>3</option>
										<option value="4" 	{if $questao[3] == 4}	selected="selected"{/if}>4</option>
										<option value="5" 	{if $questao[3] == 5}	selected="selected"{/if}>5</option>
									</select>
								</td>
								<td>
									<select class="caixa" {if $consenso}disabled="disabled"{/if} onchange="liberaPlanoAcao(this.value,'{$q}');" id="selAvaliacao_{$q}" name="selAvaliacao[{$q}]">
										<option value="0" 	{if $questao[1] == 0}	selected="selected"{/if}>N/A</option>
										<option value="1" {if $questao[1] == 1}	selected="selected"{/if}>1</option>
										<option value="2" 	{if $questao[1] == 2}	selected="selected"{/if}>2</option>
										<option value="3" {if $questao[1] == 3}	selected="selected"{/if}>3</option>
										<option value="4" 	{if $questao[1] == 4}	selected="selected"{/if}>4</option>
										<option value="5" 	{if $questao[1] == 5}	selected="selected"{/if}>5</option>
									</select>
								</td>
								<td>
									<select class="caixa" id="selConsensoAvaliacao_{$q}" name="selConsensoAvaliacao[{$q}]">
										<option value="0">N/A</option>
										<option value="1">1</option>
										<option value="2">2</option>
										<option value="3">3</option>
										<option value="4">4</option>
										<option value="5">5</option>
									</select>
								</td>
							{else}
								<td>
									<select class="caixa" {if $consenso}disabled="disabled"{/if} onchange="liberaPlanoAcao(this.value,'{$q}');" id="selAvaliacao_{$q}" name="selAvaliacao[{$q}]">
										<option value="0" 	{if $questao[1] == 0}	selected="selected"{/if}>N/A</option>
										<option value="1" {if $questao[1] == 1}	selected="selected"{/if}>1</option>
										<option value="2" 	{if $questao[1] == 2}	selected="selected"{/if}>2</option>
										<option value="3" {if $questao[1] == 3}	selected="selected"{/if}>3</option>
										<option value="4" 	{if $questao[1] == 4}	selected="selected"{/if}>4</option>
										<option value="5" 	{if $questao[1] == 5}	selected="selected"{/if}>5</option>
									</select>
								</td>
							{/if}
						{/if}
					</tr>
				{/foreach}
			{/foreach}	
		{/foreach}
		</table>
	</div>
</div>