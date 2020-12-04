<style>
table{
	font-family: "arial";
	font-size: 11px;
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
	<tr>
		<th width="15%">Nome:</th>
		<td><label id="tdNomeFuncionario">{$tdNomeFuncionario}</label></td>
	</tr>
	<tr>	
		<th>Data Avaliação:</th>
		<td><label>{$dataAvaliacao}</label></td>
	</tr>
	<tr>
		<th>Resultado:</th>
		<td><label>{number_format($mediaTecnica,2,',','.')}</label></td>
	</tr>
</table>
<br />
<div>
	<div>
		<table width="100%" cellpadding="5px;" cellspacing="0" border="0">
		{counter start=0 skip=1 print=false}
		{foreach $grupos as $k => $grupo}
			<tr>
				<th colspan="3" align="center">
					{$grupo|lower|@ucfirst}
				</th>
			</tr>
			{foreach $questoes[$k] as $f => $fatores}
				{foreach $fatores as $q => $questao}
				<tr>
					<td width="5%" align="right">{counter}</td>
					<td colspan="2" style="border-bottom: solid 1px #909090;" valign="middle">
						<b>{$questao[0]|lower|@ucfirst}</b>
					</td>
				</tr>
				<tr>
					<td width="5%" align="right">
						{if $questao['respostas'][$questao[3]]['correta']==6}
							<img src="{$smarty.const.DIR_IMAGENS}aprovado.png" />
						{else}
							<img src="{$smarty.const.DIR_IMAGENS}apagar.png" />
						{/if}
					</td>
					<td colspan="2" style="border-bottom: solid 1px #909090;" valign="middle">
						{$questao['respostas'][$questao[3]]['texto']|lower|@ucfirst}
					</td>
				</tr>
				{/foreach}
			{/foreach}	
		{/foreach}
		</table>
	</div>
</div>

<br /><br />
<table width="100%" cellspacing="10px;">
	<tr>
		<th width="50%">&nbsp;</th>
		<th width="50%">&nbsp;</th>
	</tr>
	<tr>
		<th style="border-top: solid 1px black;" align="center">{$tdNomeFuncionario}<br />AVALIADO</th>
	</tr>
</table>