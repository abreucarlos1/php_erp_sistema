<style>
table{
	font-family: "Arial";
	font-size: 10px;
	color: #000000;
	text-align: left;
	letter-spacing: 1pt;
}

.labels{
	text-align:center;
	font-family: "Arial;
}

table th{
	text-align: left;
}

.auto_lista td{
	
}

.semBorda{
	font-size: 11px;
	border: solid 1px #909090;
}
</style>

<img src='{$smarty.const.DIR_IMAGENS}logo_pb.png' width="120px" />

<h3 class="labels">{$dadosAvaliacao['ava_titulo']}</h3>
<input type="hidden" id="avaId" name="avaId" value="1" />

<table width="100%">
	{if $dadosAvaliacao['ava_alvo'] == 2}
	<tr>
		<th>Razão Social:</th>
		<td><label id="tdRazaoSocial">{$tdRazaoSocial}</label></td>
	</tr>
	<tr>
		<th>CNPJ:</th>
		<td><label id="tdCnpj" class="tabela_header ">{$tdCnpj}</label></td>
	</tr>
	{/if}
	<tr>
		<th>Nome:</th>
		<td><label id="tdNomeFuncionario">{$tdNomeFuncionario}</label></td>
	</tr>
	<tr>	
		<th>Data Avaliação:</th>
		<td><label>{$dataAvaliacao}</label></td>
	</tr>
	<tr>
		<th align="left">Descrição da função:</th>
		<td><label id="tdDescricaoServico">{$tdDescricaoServico}</label></td>
	</tr>
	{if $dadosAvaliacao['ava_alvo'] == 2}
	<tr>
		<th align="left">CNAE:</th>
		<td><label id="tdCnae">{$tdCnae}</label></td>
	</tr>
	{/if}
</table>

<table>
	<tr>
		<th><label >Total de pontos Auto Avaliação: </label>{$mediaAuto}</th>
	</tr>
	<tr>
		<th><label >Total de pontos Avaliação: </label>{$media}</th>
	</tr>
	<tr>
		<th><label >Total de pontos Consenso: </label>{$mediaConsenso}</th>
	</tr>
</table>
<br />
<div class="auto_lista">
	<div>
		<table width="100%" cellpadding="5px;" cellspacing="0" border="1">
		{counter start=0 skip=1 print=false}
		{foreach $grupos as $k => $grupo}
			<tr>
				<th colspan="5" style="border-bottom: solid 1px #909090;" align="center">
					{$grupo|lower|@ucfirst}
				</th>
			</tr>
			<tr>
				<th width="100px" colspan="2" style="text-align: center;">Item de avaliação</th>
				<th width="20px" style="text-align: center;">AA</th>
				<th width="20px" style="text-align: center;">ACI</th>
				<th width="20px" style="text-align: center;">Consenso</th>
				<!-- <th width="36%" style="text-align: center;">Meta</th> -->
			</tr>
			{foreach $questoes[$k] as $f => $fatores}
				{foreach $fatores as $q => $questao}
				<tr>
					<td colspan="2" style="border-bottom: solid 1px #909090;" valign="middle">
						{$questao[0]|lower|@ucfirst}
					</td>
					<td style="border-bottom: solid 1px #909090;" valign="middle">
						{$questao[3]|lower|@ucfirst}
					</td>
					<td align="center" valign="middle" style="border-bottom: solid 1px #909090;">
						{$questao[1]}
					</td>
					<td align="center" valign="middle" style="border-bottom: solid 1px #909090;">
						{$questao[4]}
					</td>
					<!-- <td style="border-bottom: solid 1px #909090;"><label>{$questao[2]|lower|@ucfirst}</label></td> -->
				</tr>
				{/foreach}
			{/foreach}	
		{/foreach}
		</table>
	</div>
</div>

<br />

<table border="1" cellspacing="0" cellpadding="2">
	<tr>
		<td style="background-color: red;">Ruim: 0 a 25</td>
		<td style="background-color: yellow;">Regular: 26 a 50</td>
		<td style="background-color: green;">Bom: 57 a 75</td>
		<td>Excelente: 76 a 100</td>
	</tr>
</table>

<br />
{if $metas[0]['met_descricao'] != ''}
	<table border="1" cellspacing="0" cellpadding="2" width="100%">
		<tr>
			<th colspan="3">Metas</th>
		</tr>
		<tr>
			<th>Descrição da meta</th>
			<th>Peso %</th>
			<th>Resultado %</th>
		</tr>
	{foreach $metas as $k => $m}
		<tr>
			<td style="border-bottom: solid 1px #909090;" valign="middle">
				{$m['met_descricao']|lower|@ucfirst}
			</td>
			<td style="border-bottom: solid 1px #909090;" valign="middle">
				{$m['met_peso']}%
			</td>
			<td align="center" valign="middle" style="border-bottom: solid 1px #909090;">
				{if $m['met_resultado'] > 0}{$m['met_resultado']}{/if}
			</td>
		</tr>
	{/foreach}
	</table>
	
	<br />
{/if}

{if $pdi[0]['apd_programa'] != ''}
	<table border="1" cellspacing="0" cellpadding="2" width="100%">
		<tr>
			<th colspan="3">PDI</th>
		</tr>
		<tr>
			<th>Programa</th>
			<th>Comentário Avaliador</th>
			<th>Comentário Avaliado</th>
		</tr>
		{foreach $pdi as $k => $p}
			<tr>
				<td>{$p['apd_programa']}</td>
				<td>{$p['apd_comentario_avaliador']}</td>
				<td>{$p['apd_comentario_avaliado']}</td>
			</tr>
		{/foreach}
	</table>
{/if}

<br /><br />
<table width="100%" cellspacing="10px;">
	<tr>
		<th width="50%"> </th>
		<th width="50%"> </th>
	</tr>
	<tr>
		<th style="border-top: solid 1px black;" align="center">{$tdNomeFuncionario}<br />{if $tdRazaoSocial != ''}{$tdRazaoSocial}<br />{/if}AVALIADO</th>
		<th style="border-top: solid 1px black;" align="center">{$tdNomeAvaliador}<br />AVALIADOR</th>
	</tr>
</table>