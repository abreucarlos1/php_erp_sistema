<?php
/*
		Formulário de Planejamento Estratégico considerações	
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		../planejamento_estrategico/.php
		
		Versão 0 --> VERSÃO INICIAL - 10/03/2006
		Versao 1 --> Atualização classe banco de dados - 22/01/2015 - Carlos Abreu		
		
*/	
	
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(220))
{
	nao_permitido();
}

?>

<html>
<head><title>PLANEJAMENTO ESTRATÉGICO - V1</title>
<link href="../classes/estilos.css" rel="stylesheet" type="text/css">

<?php

if($_GET["liberado"]!="ok")
{

		$id_funcionario = $_SESSION["id_funcionario"];

?>

	<style type="text/css">
<!--
.style1 {
	font-size: 12;
	font-weight: bold;
}
-->
    </style>
<form action="<?= $_SERVER["PHP_SELF"] ?>" method="GET" name="verificacao">
	<input type="hidden" name="liberado" value="ok">
	<input type="hidden" name="id_funcionario" value="<?= $id_funcionario ?>">

	<script language="javascript">
	
	
	version = parseFloat(navigator.appVersion.split("MSIE")[1]);
	
	if(window.opener) // && version < 7
	{
		document.forms.verificacao.submit();
	}
	else
	{
		location.href='../erro_geral.php';
	}
	
	</script>
	
	<NOSCRIPT>
	 <P>Ação não permitida - Habilite o Javascript em seu navegador.</P>
	</NOSCRIPT>

	</form>

<?php

exit();

}

?>


<script language="JavaScript">
<!--

function click(e) 
{
	if (document.all) 
	{

		if (event.button == 2) 
		{
		alert('Ação não permitida');
		window.close();
		return false;
		}
	}

	if (document.layers) 	
	{
	
		if (e.which == 3) 
		{
		alert('Ação não permitida');
		window.close();
		return false;
		}
	}
}

	if (document.layers) 
	{
		document.captureEvents(Event.MOUSEDOWN);
		document.captureEvents(Event.MOUSEUP);
	}

document.onmousedown=click;
document.onmouseup=click;
// --> 

</script>


<SCRIPT language=Javascript>
var keyesMessage="Ação não permitida";
function nokeys(){
if (document.all)
{
	alert(keyesMessage);
	window.close();
	return false;
}
}
if (document.all)
{
	document.onkeydown=nokeys;
	document.onkeyup=nokeys;
}
</SCRIPT>


<style
 id="Revisão do Planejamento Estratégico - 2009 Rev 03_divulgar_13623_Styles">

@media print {body {display:none;}}
<!--table
	{mso-displayed-decimal-separator:"\,";
	mso-displayed-thousand-separator:"\.";}
.font013623
	{color:black;
	font-size:10.0pt;
	font-weight:400;
	font-style:normal;
	text-decoration:none;
	font-family:Calibri, sans-serif;
	mso-font-charset:0;}
.font513623
	{color:black;
	font-size:10.0pt;
	font-weight:400;
	font-style:normal;
	text-decoration:none;
	font-family:Arial, sans-serif;
	mso-font-charset:0;}
.font613623
	{color:black;
	font-size:10.0pt;
	font-weight:400;
	font-style:normal;
	text-decoration:none;
	font-family:Calibri, sans-serif;
	mso-font-charset:0;}
.xl1513623
	{padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:black;
	font-size:10.0pt;
	font-weight:400;
	font-style:normal;
	text-decoration:none;
	font-family:Calibri, sans-serif;
	mso-font-charset:0;
	mso-number-format:General;
	text-align:general;
	vertical-align:bottom;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:nowrap;}
.xl6313623
	{padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:black;
	font-size:10.0pt;
	font-weight:400;
	font-style:normal;
	text-decoration:none;
	font-family:Calibri, sans-serif;
	mso-font-charset:0;
	mso-number-format:General;
	text-align:justify;
	vertical-align:middle;
	border:.5pt solid windowtext;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:nowrap;}
.xl6413623
	{padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:black;
	font-size:10.0pt;
	font-weight:400;
	font-style:normal;
	text-decoration:none;
	font-family:Calibri, sans-serif;
	mso-font-charset:0;
	mso-number-format:General;
	text-align:general;
	vertical-align:bottom;
	border:.5pt solid windowtext;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:nowrap;}
.xl6513623
	{padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:black;
	font-size:10.0pt;
	font-weight:400;
	font-style:normal;
	text-decoration:none;
	font-family:Calibri, sans-serif;
	mso-font-charset:0;
	mso-number-format:General;
	text-align:center;
	vertical-align:middle;
	border:.5pt solid windowtext;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:nowrap;}
.xl6613623
	{padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:black;
	font-size:10.0pt;
	font-weight:400;
	font-style:normal;
	text-decoration:none;
	font-family:Calibri, sans-serif;
	mso-font-charset:0;
	mso-number-format:General;
	text-align:general;
	vertical-align:bottom;
	border:.5pt solid windowtext;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:normal;}
.xl6713623
	{padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:white;
	font-size:12.0pt;
	font-weight:700;
	font-style:normal;
	text-decoration:none;
	font-family:Calibri, sans-serif;
	mso-font-charset:0;
	mso-number-format:General;
	text-align:center;
	vertical-align:middle;
	border:.5pt solid windowtext;
	background:black;
	mso-pattern:black none;
	white-space:nowrap;}
.xl6813623
	{padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:black;
	font-size:10.0pt;
	font-weight:400;
	font-style:normal;
	text-decoration:none;
	font-family:Calibri, sans-serif;
	mso-font-charset:0;
	mso-number-format:General;
	text-align:justify;
	vertical-align:bottom;
	border:.5pt solid windowtext;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:normal;}
.xl6913623
	{padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:black;
	font-size:10.0pt;
	font-weight:400;
	font-style:normal;
	text-decoration:none;
	font-family:Calibri, sans-serif;
	mso-font-charset:0;
	mso-number-format:General;
	text-align:justify;
	vertical-align:middle;
	border:.5pt solid windowtext;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:normal;}
-->

</style>


</head>
<body onBlur='window.clipboardData.setData("Text", " ");'>

<script language="javascript" src="../includes/dvmfechamento.php"></script>

<div onselectstart="return false" unselectable="on" style="-moz-user-select:none;"> 

<form name="relatoriohoras">
<table width="100%" height="10%" border="0" cellpadding="0" cellspacing="0">

      <tr>
        <td>
			  <?php

					$id_funcionario = $_SESSION["id_funcionario"];
					


				  ?>
		  <div id="tbbody" style="position:relative; width:100%; height:200px; z-index:2; overflow-y:no; overflow-x:no; border-color:#999999; border-style:solid; border-width:1px;">
				<?php
					//$permitidos = array('6','11');
				
					//Verifica se o usuário possue Fechamentos a serem visualizados
					//Caso negativo, mostra mensagem de alerta e volta para o Menu Principal
					//if(!in_array($id_funcionario,$permitidos))
					if(FALSE)
					{
						?>
						<script>
						alert('Você não possue acesso ao conteúdo.');
						window.close();
						</script>
					<?php
					}
					?>
                    <div
                    id="Revisão do Planejamento Estratégico - 2009 Rev 03_divulgar_13623"
                    align=center x:publishsource="Excel">
                    
                    <table border=0 cellpadding=0 cellspacing=0 width=751 style='border-collapse:
                     collapse;table-layout:fixed;width:564pt'>
                     <col width=93 style='mso-width-source:userset;mso-width-alt:3401;width:70pt'>
                     <col width=658 style='mso-width-source:userset;mso-width-alt:24064;width:494pt'>
                     <tr height=21 style='height:15.75pt'>
                      <td height=21 class=xl6713623 width=93 style='height:15.75pt;width:70pt'><a
                      name="RANGE!A1:B33">INDICADOR</a></td>
                      <td class=xl6713623 width=658 style='border-left:none;width:494pt'>CONSIDERAÇÕES</td>
                     </tr>
                     <tr height=51 style='height:38.25pt'>
                      <td height=51 class=xl6513623 style='height:38.25pt;border-top:none'>F1</td>
                      <td class=xl6313623 style='border-top:none;border-left:none'>(1) O indicador
                      desejado é o da apuração do resultado do ano, porém, serão utilizadas as
                      informações dos fechamentos trimestrais como referencias do valor obtido,
                      portanto, nestes valores deverão estar incluídas todas as provisões,
                      depreciações e demais provisões com base na competência trimestral.</td>
                     </tr>
                     <tr height=17 style='height:12.75pt'>
                      <td height=17 class=xl6513623 style='height:12.75pt;border-top:none'>F3a</td>
                      <td class=xl6413623 style='border-top:none;border-left:none'>(1) Criar
                      mecanismo de apuração de horas de retrabalho e implementá-lo, (2) Efetuar
                      medida mensal do índice.</td>
                     </tr>
                     <tr height=34 style='height:25.5pt'>
                      <td height=34 class=xl6513623 style='height:25.5pt;border-top:none'>F3b</td>
                      <td class=xl6613623 width=658 style='border-top:none;border-left:none;
                      width:494pt'>(1) Criar mecanismo de apuração de horas de totais gastas, bem
                      como de número de documentos produzidos.<span style='mso-spacerun:yes'>
                      </span>(2) Efetuar medida mensal do índice.</td>
                     </tr>
                     <tr height=17 style='height:12.75pt'>
                      <td height=17 class=xl6513623 style='height:12.75pt;border-top:none'>F5</td>
                      <td class=xl6613623 width=658 style='border-top:none;border-left:none;
                      width:494pt'>Faturamento médio mensal dos últimos 12 meses</td>
                     </tr>
                     <tr height=17 style='height:12.75pt'>
                      <td height=17 class=xl6513623 style='height:12.75pt;border-top:none'>F6</td>
                      <td class=xl6313623 style='border-top:none;border-left:none'>(1) Medida
                      trimestral com informações anuais em base móvel.</td>
                     </tr>
                     <tr height=17 style='height:12.75pt'>
                      <td height=17 class=xl6513623 style='height:12.75pt;border-top:none'>F7</td>
                      <td class=xl6313623 style='border-top:none;border-left:none'>(1) Medida
                      trimestral com informações anuais em base móvel.</td>
                     </tr>
                     <tr height=51 style='height:38.25pt'>
                      <td height=51 class=xl6513623 style='height:38.25pt;border-top:none'>C2</td>
                      <td class=xl6613623 width=658 style='border-top:none;border-left:none;
                      width:494pt'>(1) Para o indicador 15 teremos o indicador mensal composto por
                      = numero de obras 100% dentro do prazo / número total de obras. (2) Para o
                      indicador C2 teremos o indicador mensal composto por = numero de obras 100%
                      dentro do custo planejado / número total de obras</td>
                     </tr>
                     <tr height=34 style='height:25.5pt'>
                      <td height=34 class=xl6513623 style='height:25.5pt;border-top:none'>C3</td>
                      <td class=xl6813623 width=658 style='border-top:none;border-left:none;
                      width:494pt'>Deverá ser calculado com base no valor de soluções fechadas no
                      último ano dividido pelo total de orçamentos fechados. (2) Deverá ser feita
                      uma medida trimestral, considerando-se o valor móvel dos últimos 12 meses.</td>
                     </tr>
                     <tr height=68 style='height:51.0pt'>
                      <td height=68 class=xl6513623 style='height:51.0pt;border-top:none'>C4a</td>
                      <td class=xl6613623 width=658 style='border-top:none;border-left:none;
                      width:494pt'>(1) Deverá ser criada uma lista de clientes ABC levando-se em
                      consideração os trabalhos realizados nos últimos 36 meses (3 anos). Deverá
                      ser uma medida trimestral, com base na soma dos últimos 36 meses. (2) Com
                      base no posicionamento de cada cliente ele será considerado A, B, ou C se
                      estiver dentro dos seguintes grupo: A até 50%, B até 85% e C até 100%.<span
                      style='mso-spacerun:yes'></span></td>
                     </tr>
                     <tr height=68 style='height:51.0pt'>
                      <td height=68 class=xl6513623 style='height:51.0pt;border-top:none'>C4b</td>
                      <td class=xl6613623 width=658 style='border-top:none;border-left:none;
                      width:494pt'>(1) Deverá ser criada uma lista de clientes ABC levando-se em
                      consideração os trabalhos realizados nos últimos 36 meses (3 anos). Deverá
                      ser uma medida trimestral, com base na soma dos últimos 36 meses. (2) Com
                      base no posicionamento de cada cliente ele será considerado A, B, ou C se
                      estiver dentro dos seguintes grupo: A até 50%, B até 85% e C até 100%.<span
                      style='mso-spacerun:yes'></span></td>
                     </tr>
                     <tr height=34 style='height:25.5pt'>
                      <td height=34 class=xl6513623 style='height:25.5pt;border-top:none'>C6</td>
                      <td class=xl6313623 style='border-top:none;border-left:none'>(1) A
                      documentação do testemunho pode variar de uma entrevista, filmagem, fotos,
                      etc. que contribua para a viabilização de novas vendas e fortalecimento da
                      marca.</td>
                     </tr>
                     <tr height=17 style='height:12.75pt'>
                      <td height=17 class=xl6513623 style='height:12.75pt;border-top:none'>C7a</td>
                      <td class=xl6813623 width=658 style='border-top:none;border-left:none;
                      width:494pt'>(1) Deve ser considerado para investimento como expositor em
                      feira anualmente</td>
                     </tr>
                     <tr height=85 style='height:63.75pt'>
                      <td height=85 class=xl6513623 style='height:63.75pt;border-top:none'>C7b</td>
                      <td class=xl6813623 width=658 style='border-top:none;border-left:none;
                      width:494pt'>(1) O Objetivo destas visitas é o estreitamento de
                      relacionamento e identificação da oportunidade de geração de novos negócios.
                      (2) Por tanto, devem ser realizadas visitas dirigidas a potenciais negócios.
                      São consideradas visitas com potencial de negócio aquelas visitas dirigidas a
                      clientes previamente escolhidos efetivamente realizadas pela Diretoria
                      Técnica e/ou financeira. (2) Deverão previamente ser escolhidas as feiras que
                      serão visitadas dentro de um programa de objetivos claramente definido. (3) Medida
                      Trimestral</td>
                     </tr>
                     <tr height=17 style='height:12.75pt'>
                      <td height=17 class=xl6513623 style='height:12.75pt;border-top:none'>I1</td>
                      <td class=xl6413623 style='border-top:none;border-left:none'>(1) Indicador
                      mensal onde teremos => numero de obras 100% dentro do custo / número total
                      de obras.</td>
                     </tr>
                     <tr height=170 style='height:127.5pt'>
                      <td height=170 class=xl6513623 style='height:127.5pt;border-top:none'>I5</td>
                      <td class=xl6913623 width=658 style='border-top:none;border-left:none;
                      width:494pt'>(1) Quando da análise estratégica inicial havia a percepção de
                      que trabalhar próximo ao cliente era entendido por este como valor oferecido.
                      A percepção hoje é que as empresas são atendidas por fornecedores locais, que
                      usam a disponibilidade mão-de-obra local. Quando contratam mão-de-obra de
                      outras localidades esperam que os serviços sejam prestados fora dos locais de
                      solicitação do serviço, até para que não haja a competição pela mão-de-obra
                      local. Desta forma não há geração de valor para o cliente com criação de
                      sites locais. (2) A motivação pela descentralização se mostrou inadequada,
                      sendo que manterá uma gestão centralizada. O que poderá haver é a
                      criação de uma unidade na cidade de São Paulo (capital) caso haja necessidade
                      de expansão de mão-de-obra que não esteja alocada em clientes. Sendo assim em
                      2012 deverá haver um novo local para a contratação de mão-de-obra local (São
                      Paulo). (3) O atendimento local a clientes será feito a partir de viagens de
                      Mogi das Cruzes ou São Paulo.</td>
                     </tr>
                     <tr height=34 style='height:25.5pt'>
                      <td height=34 class=xl6513623 style='height:25.5pt;border-top:none'>I7</td>
                      <td class=xl6813623 width=658 style='border-top:none;border-left:none;
                      width:494pt'>(1) Cliente novo é um novo grupo de empresas, que pode fazer
                      parte de um mesmo CNPJ, por exemplo, entrei na xxxxxx, em São Paulo e
                      depois no RJ, considerar duas vezes.</td>
                     </tr>
                     <tr height=51 style='height:38.25pt'>
                      <td height=51 class=xl6513623 style='height:38.25pt;border-top:none'>I8a</td>
                      <td class=xl6813623 width=658 style='border-top:none;border-left:none;
                      width:494pt'>(1) A medida do valor total orçado no trimestre deve ser
                      calculada<span style='mso-spacerun:yes'> </span>como uma soma móvel dos
                      últimos 3 meses de forma a poder realizar medidas mensais deste indicador.
                      (2) O valor de 0,12 apresentado no divisor do Score representa o nível de
                      fechamento de negociações face ao orçado no momento atual (jun/2009).<span
                      style='mso-spacerun:yes'></span></td>
                     </tr>
                     <tr height=17 style='height:12.75pt'>
                      <td height=17 class=xl6513623 style='height:12.75pt;border-top:none'>I8b</td>
                      <td class=xl6813623 width=658 style='border-top:none;border-left:none;
                      width:494pt'>(1) Devemos calcular o total de orçamentos fechados e dividi-lo
                      pelo total de orçamentos realizados.</td>
                     </tr>
                     <tr height=51 style='height:38.25pt'>
                      <td height=51 class=xl6513623 style='height:38.25pt;border-top:none'>I8c</td>
                      <td class=xl6813623 width=658 style='border-top:none;border-left:none;
                      width:494pt'>(1) Esse índice procura dar a visão de quantos meses de
                      faturamento vendido a empresa tem em carteira. O valor do Planejamento da
                      Faturamento Anual deve ser feito com base no faturamento projetado para os
                      próximos 12 meses.</td>
                     </tr>
                     <tr height=85 style='page-break-before:always;height:63.75pt'>
                      <td height=85 class=xl6513623 style='height:63.75pt;border-top:none'>I10</td>
                      <td class=xl6813623 width=658 style='border-top:none;border-left:none;
                      width:494pt'>(1) Deverá ser obtido a partir da gestão das informações obtidas
                      no ERP, com base no encerramento do projeto para a apresentação de uma medida
                      trimestral. (2) Deverá ser construída uma planilha que elenque o histórico
                      das rentabilidades dos projetos por cliente e com base nisso, construir-se a
                      rentabilidade média ponderada de cada cliente. (3) Com os dados obtidos, a
                      área de vendas deverá ser instruída para focar seus esforços de vendas nos
                      clientes que apresentem a maior rentabilidade.</td>
                     </tr>
                     <tr height=51 style='height:38.25pt'>
                      <td height=51 class=xl6513623 style='height:38.25pt;border-top:none'>A1a</td>
                      <td class=xl6313623 style='border-top:none;border-left:none'>(1) Diretor é
                      diferente de sócio. O sócio pode não ser escolhido como diretor. (2) É
                      condição para que ele seja Diretor que tenha MBA. (3) Se dará um tempo de 3
                      anos para regularização dessa situação. (Meta a ser atingida em 2011). (4) O
                      investimento será por conta de cada um dos Diretores.</td>
                     </tr>
                     <tr height=85 style='height:63.75pt'>
                      <td height=85 class=xl6513623 style='height:63.75pt;border-top:none'>A1b</td>
                      <td class=xl6313623 style='border-top:none;border-left:none'>(1) Deverá ser
                      contemplada estruturação de carreira em Y, onde no tramo superior, de um dos
                      lados encontramos a figura técnica administrativa. Especificamente para esta
                      é que desejamos induzir a qualificação de gestão. No outro lado temos a
                      figura do consultor técnico altamente especializado. (2) Para o grupo que se
                      deseja incentivar a qualificação e subsidiará parcialmente os cursos
                      dentro de política a ser desenvolvida. (3) Se dará um tempo de 4 anos para o
                      atingimento deste meta.</td>
                     </tr>
                     <tr height=51 style='height:38.25pt'>
                      <td height=51 class=xl6513623 style='height:38.25pt;border-top:none'>A1c</td>
                      <td class=xl6313623 style='border-top:none;border-left:none'>(1) Nº de horas
                      de treinamento = total de horas gastas por cada um dos funcionários que se
                      submeteram ao processo de treinamento (2) Nº de horas totais trabalhadas no
                      trimestre com base no Relatório diário de horas. (3) O indicador
                      deverá gerar uma medida mensal móvel.</td>
                     </tr>
                     <tr height=51 style='height:38.25pt'>
                      <td height=51 class=xl6513623 style='height:38.25pt;border-top:none'>A1d</td>
                      <td class=xl6313623 style='border-top:none;border-left:none'>(1) Considera-se
                      fluente em inglês (para os objetivos da Empresa: Ler documentos, falar ao
                      telefone, escrever cartas e Relatórios, realizar viagens, receber clientes
                      internacionais e conduzir reuniões) quem atingir X pontos no teste Y. (2)
                      Prazo para o atendimento da meta: 2 anos (2011).</td>
                     </tr>
                     <tr height=51 style='height:38.25pt'>
                      <td height=51 class=xl6513623 style='height:38.25pt;border-top:none'>A1e</td>
                      <td class=xl6313623 style='border-top:none;border-left:none'>(1) Considera-se
                      com leitura em inglês (para os objetivos da Empresa: Ler documentos,<span
                      style='mso-spacerun:yes'></span>escrever cartas e Relatórios) quem for
                      capaz de atingir um nível <font class="font513623">&#8805;</font><font
                      class="font613623"> 90% de entendimento de um texto técnico naquele idioma,
                      de acordo com pontuação em prova efetuada. </font><font
                      class="font013623">(2) Prazo para o atendimento da meta: 2 anos (2011).</font></td>
                     </tr>
                     <tr height=51 style='height:38.25pt'>
                      <td height=51 class=xl6513623 style='height:38.25pt;border-top:none'>A1f</td>
                      <td class=xl6313623 style='border-top:none;border-left:none'>(1) Considera-se
                      fluente em inglês (para os objetivos da Empresa: Ler documentos, falar ao
                      telefone, escrever cartas e Relatórios, realizar viagens, receber clientes
                      internacionais e conduzir reuniões) quem atingir X pontos no teste Y. (2)
                      Prazo para o atendimento da meta: 2 anos (2011).</td>
                     </tr>
                     <tr height=85 style='height:63.75pt'>
                      <td height=85 class=xl6513623 style='height:63.75pt;border-top:none'>A2a</td>
                      <td class=xl6313623 style='border-top:none;border-left:none'>(1) a construção
                      do indicador deve considerar: a) Total de funcionário requeridos anualmente
                      com base na taxa de crescimento projetada + perda de funcionários pela
                      rotatividade media projetada = acréscimos (esse valor é calculado uma vez por
                      ano); b) O valor de acréscimo anual deverá ser dividido por 12 (acréscimo
                      mensal), c) o acréscimo mensal deverá ser divido pelo número de funcionários
                      médios ativos na empresa no mês em questão. (2) Este indicador deve
                      proporcionar uma medida mensal.</td>
                     </tr>
                     <tr height=17 style='height:12.75pt'>
                      <td height=17 class=xl6513623 style='height:12.75pt;border-top:none'>A2b</td>
                      <td class=xl6313623 style='border-top:none;border-left:none'>(1) indicador
                      semestral (2) Prazo para o atendimento da meta é de um ano (2010 - Agosto)</td>
                     </tr>
                     <tr height=17 style='height:12.75pt'>
                      <td height=17 class=xl6513623 style='height:12.75pt;border-top:none'>A2c</td>
                      <td class=xl6313623 style='border-top:none;border-left:none'>(1) indicador
                      semestral (2) Prazo para o atendimento da meta é de um ano (2010 - Agosto)</td>
                     </tr>
                     <tr height=17 style='height:12.75pt'>
                      <td height=17 class=xl6513623 style='height:12.75pt;border-top:none'>A2d</td>
                      <td class=xl6313623 style='border-top:none;border-left:none'>(1) Deve ser
                      utilizado o índice produzido pelo RH</td>
                     </tr>
                     <tr height=34 style='height:25.5pt'>
                      <td height=34 class=xl6513623 style='height:25.5pt;border-top:none'>A6a</td>
                      <td class=xl6813623 width=658 style='border-top:none;border-left:none;
                      width:494pt'>(1) Funcionário é o trabalhador que mantém vinculo com a empresa
                      de acordo com a CLT.<span style='mso-spacerun:yes'> </span>(2) Meta a ser
                      atingida no fechamento do 1º Semestre de 2010.</td>
                     </tr>
                     <tr height=34 style='height:25.5pt'>
                      <td height=34 class=xl6513623 style='height:25.5pt;border-top:none'>A6b</td>
                      <td class=xl6813623 width=658 style='border-top:none;border-left:none;
                      width:494pt'>(1) Prestador de Serviço são empresas (Pessoas Jurídicas) que
                      prestam serviços para a Empresa. Meta a ser atingida no fechamento do 1º
                      Semestre de 2010.</td>
                     </tr>
                    </table>
                    
                    </div>
             
			              
		  </div>
          </td>
      </tr>
      
</table>
	<table width="100%" border="0">
  <tr>
    <td align="right"><input name="Voltar" type="button" class="btn" id="Voltar" value="Voltar" onclick="window.close()"></td>
  </tr>
</table>

</form>

</div>


</body>
</html>


