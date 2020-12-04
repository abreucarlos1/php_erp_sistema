<style>
	#div_apresentacao{
		font-family: arial, verdana;
	}
	#div_apresentacao p,table{
		font-size: 12px;
	}
	.destaque{
		font-style: italic;
		font-weight: bold;
		text-decoration: underline;
		color: red;
	}
</style>
<h4 align="center">{$dados['ava_titulo']}</h4>

<p>Nesta avaliação serão analisados os valores da EMPRESA <span class="destaque">Criatividade, Comprometimento, Competência e Trabalho em Equipe.</span></p>

<p>A atribuição das notas se dará da seguinte forma:</p>
<table cellspacing="0" cellpadding="5" width='100%'>
	<tr>
		<td style="background-color: red;">Abaixo das expectativas = 1</td>
		<td style="background-color: yellow;">Atende parcialmente as expectativas = 2</td>
		<td style="color: white; background-color: green;">Atende expectativas = 3</td>
		<td style="color: white; background-color: blue;">Excede parcialmente as expectativas = 4</td>
		<td style="border: solid 1px black; border-left: none;">Excede completamente as expectativas = 5</td>
	</tr>
</table>

<h5>Segue abaixo a programação para processo de Avaliação de Desempenho.</h5>

<table cellspacing="0" cellpadding="5" width='100%'>
	<tr style="background-color: green;">
		<th style="border: solid 1px black; color: white;">Ação</th>
		<th style="border: solid 1px black; color: white;">Data</th>
		<th style="border: solid 1px black; color: white;">Responsável</th>
	</tr>
	<tr>
		<td style="border: solid 1px black;">Treinamento á liderança</td>
		<td style="border: solid 1px black;">{$dados['ava_data_inicio_treinamento_sup']|date_format:"%d/%m/%Y"}</td>
		<td style="border: solid 1px black;"></td>
	</tr>
	<tr>
		<td style="border: solid 1px black;">Treinamento á colaboradores</td>
		<td style="border: solid 1px black;">{$dados['ava_data_inicio_treinamento_sub']|date_format:"%d/%m/%Y"}</td>
		<td style="border: solid 1px black;"></td>
	</tr>
	<tr>
		<td style="border: solid 1px black;">Auto Avaliação</td>
		<td style="border: solid 1px black;">{$dados['ava_data_inicio_sub']|date_format:"%d/%m/%Y"} a {$dados['data_fim_auto_avaliacao']|date_format:"%d/%m/%Y"}</td>
		<td style="border: solid 1px black;">Colaboradores</td>
	</tr>
	<tr>
		<td style="border: solid 1px black;">Avaliação do superior</td>
		<td style="border: solid 1px black;">{$dados['ava_data_inicio']|date_format:"%d/%m/%Y"} a {$dados['data_fim_supervisao']}</td>
		<td style="border: solid 1px black;">Coordenação</td>
	</tr>
	<tr>
		<td style="border: solid 1px black;">Avaliação do administrativo</td>
		<td style="border: solid 1px black;">{$dados['ava_data_inicio']|date_format:"%d/%m/%Y"} a {$dados['data_fim_adm']}</td>
		<td style="border: solid 1px black;">Administrativo</td>
	</tr>
	<tr>
		<td style="border: solid 1px black;">Avaliação do Recursos humanos</td>
		<td style="border: solid 1px black;">{$dados['ava_data_inicio']|date_format:"%d/%m/%Y"} a {$dados['data_fim_rh']}</td>
		<td style="border: solid 1px black;">Recursos Humanos</td>
	</tr>
	<tr>
		<td style="border: solid 1px black;">Consenso e Feedback</td>
		<td style="border: solid 1px black;">{$dados['ava_data_consenso']|date_format:"%d/%m/%Y"} a {$dados['data_fim_feedback']|date_format:"%d/%m/%Y"}</td>
		<td style="border: solid 1px black;">Coordenação</td>
	</tr>
</table>

<p>Informações sobre os critérios que devem ser adotados na avaliação estão na aba <span class="destaque">Tabela de Critérios</span></p>