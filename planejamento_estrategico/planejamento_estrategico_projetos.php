<?php
/*

		Formulário de Planejamento Estrategico Projetos
		
		Criado por Carlos Abreu
		
		local/Nome do arquivo:
		../planejamento_estrategico/planejamento_estrategico_projetos.php
		
		Versão 0 --> VERSÃO INICIAL - 10/03/2006
		Versão 1 --> Atualização classe banco de dados - 22/01/2015		
*/	
	

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(219))
{
	nao_permitido();
}

?>

<html>
<head><title>PLANEJAMENTO ESTRATÉGICO</title>
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

	<script>
	
	
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
 id="Revisão do Planejamento Estratégico - 2009 Rev 03_divulgar_26375_Styles">

@media print {body {display:none;}}


<!--table
	{mso-displayed-decimal-separator:"\,";
	mso-displayed-thousand-separator:"\.";}
.font026375
	{color:black;
	font-size:10.0pt;
	font-weight:400;
	font-style:normal;
	text-decoration:none;
	font-family:Calibri, sans-serif;
	mso-font-charset:0;}
.font526375
	{color:black;
	font-size:10.0pt;
	font-weight:700;
	font-style:normal;
	text-decoration:none;
	font-family:Calibri, sans-serif;
	mso-font-charset:0;}
.xl1526375
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
.xl6326375
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
.xl6426375
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
	background:yellow;
	mso-pattern:black none;
	white-space:nowrap;}
.xl6526375
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
	text-align:left;
	vertical-align:middle;
	border:.5pt solid windowtext;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:normal;}
.xl6626375
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
	vertical-align:middle;
	border:.5pt solid windowtext;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:nowrap;}
.xl6726375
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
.xl6826375
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
.xl6926375
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
	vertical-align:middle;
	border:.5pt solid windowtext;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:normal;}
.xl7026375
	{padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:black;
	font-size:10.0pt;
	font-weight:700;
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
.xl7126375
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
	background:gray;
	mso-pattern:black none;
	white-space:nowrap;}
.xl7226375
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
	text-align:left;
	vertical-align:middle;
	border:.5pt solid windowtext;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:nowrap;}
.xl7326375
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
	border-top:none;
	border-right:.5pt solid windowtext;
	border-bottom:.5pt solid windowtext;
	border-left:.5pt solid windowtext;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:normal;}
.xl7426375
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
	mso-number-format:"mmm\/yy";
	text-align:general;
	vertical-align:bottom;
	border:.5pt solid windowtext;
	background:white;
	mso-pattern:black none;
	white-space:nowrap;}
.xl7526375
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
	mso-number-format:"mmm\/yy";
	text-align:general;
	vertical-align:bottom;
	border:.5pt solid windowtext;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:nowrap;}
.xl7626375
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
.xl7726375
	{padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:black;
	font-size:8.0pt;
	font-weight:400;
	font-style:normal;
	text-decoration:none;
	font-family:Arial, sans-serif;
	mso-font-charset:0;
	mso-number-format:General;
	text-align:general;
	vertical-align:bottom;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:normal;}
.xl7826375
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
	border-top:.5pt solid windowtext;
	border-right:.5pt solid windowtext;
	border-bottom:none;
	border-left:.5pt solid windowtext;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:normal;}
.xl7926375
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
	border-top:none;
	border-right:.5pt solid windowtext;
	border-bottom:none;
	border-left:.5pt solid windowtext;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:normal;}
.xl8026375
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
	border-top:.5pt solid windowtext;
	border-right:.5pt solid windowtext;
	border-bottom:none;
	border-left:.5pt solid windowtext;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:nowrap;}
.xl8126375
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
	border-top:none;
	border-right:.5pt solid windowtext;
	border-bottom:.5pt solid windowtext;
	border-left:.5pt solid windowtext;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:nowrap;}
.xl8226375
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
	text-align:left;
	vertical-align:middle;
	border-top:.5pt solid windowtext;
	border-right:.5pt solid windowtext;
	border-bottom:none;
	border-left:.5pt solid windowtext;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:normal;}
.xl8326375
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
	text-align:left;
	vertical-align:middle;
	border-top:none;
	border-right:.5pt solid windowtext;
	border-bottom:none;
	border-left:.5pt solid windowtext;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:normal;}
.xl8426375
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
	text-align:left;
	vertical-align:middle;
	border-top:none;
	border-right:.5pt solid windowtext;
	border-bottom:.5pt solid windowtext;
	border-left:.5pt solid windowtext;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:normal;}
.xl8526375
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
	border-top:none;
	border-right:.5pt solid windowtext;
	border-bottom:none;
	border-left:.5pt solid windowtext;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:nowrap;}
.xl8626375
	{padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:white;
	font-size:14.0pt;
	font-weight:700;
	font-style:normal;
	text-decoration:none;
	font-family:Calibri, sans-serif;
	mso-font-charset:0;
	mso-number-format:General;
	text-align:center;
	vertical-align:bottom;
	border-top:none;
	border-right:none;
	border-bottom:.5pt solid windowtext;
	border-left:none;
	background:black;
	mso-pattern:black none;
	white-space:nowrap;}
.xl8726375
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
                    id="Revisão do Planejamento Estratégico - 2009 Rev 03_divulgar_26375"
                    align=center x:publishsource="Excel">
                    
                    <table border=0 cellpadding=0 cellspacing=0 width=939 style='border-collapse:
                     collapse;table-layout:fixed;width:704pt'>
                     <col width=173 style='mso-width-source:userset;mso-width-alt:6326;width:130pt'>
                     <col width=55 style='mso-width-source:userset;mso-width-alt:2011;width:41pt'>
                     <col width=175 style='mso-width-source:userset;mso-width-alt:6400;width:131pt'>
                     <col width=472 style='mso-width-source:userset;mso-width-alt:17261;width:354pt'>
                     <col width=64 style='width:48pt'>
                     <tr height=25 style='height:18.75pt'>
                      <td colspan=5 height=25 class=xl8626375 width=939 style='height:18.75pt;
                      width:704pt'><a name="RANGE!A1:E114">RELAÇÃO DE PROJETOS</a></td>
                     </tr>
                     <tr height=21 style='height:15.75pt'>
                      <td height=21 class=xl7126375 style='height:15.75pt;border-top:none'>Perspectiva</td>
                      <td class=xl7126375 style='border-top:none;border-left:none'>Resp.</td>
                      <td class=xl7126375 style='border-top:none;border-left:none'>Ação</td>
                      <td class=xl7126375 style='border-top:none;border-left:none'>Atividades</td>
                      <td class=xl7126375 style='border-top:none;border-left:none'>Prazos</td>
                     </tr>
                     <tr height=34 style='mso-height-source:userset;height:25.5pt'>
                      <td rowspan=3 height=68 class=xl6526375 width=173 style='height:51.0pt;
                      border-top:none;width:130pt'>Perspectiva Financeira</td>
                      <td rowspan=3 class=xl8026375 style='border-bottom:.5pt solid black;
                      border-top:none'>XXXXXX</td>
                      <td rowspan=3 class=xl6526375 width=175 style='border-top:none;width:131pt'>Projeto
                      de apuração do resultado operacional</td>
                      <td class=xl6326375 style='border-top:none;border-left:none'>Implantar ERP</td>
                      <td class=xl7526375 align=right style='border-top:none;border-left:none'>dez/09</td>
                     </tr>
                     <tr height=17 style='mso-height-source:userset;height:12.75pt'>
                      <td height=17 class=xl6326375 style='height:12.75pt;border-top:none;
                      border-left:none'>Implantar ações de gestão voltado para resultado</td>
                      <td class=xl7526375 align=right style='border-top:none;border-left:none'>mar/10</td>
                     </tr>
                     <tr height=17 style='height:12.75pt'>
                      <td height=17 class=xl6326375 style='height:12.75pt;border-top:none;
                      border-left:none'>Rever ações, se necessário, para a obtenção dos lucros
                      definidos para cada ano</td>
                      <td class=xl7526375 align=right style='border-top:none;border-left:none'>jun/10</td>
                     </tr>
                     <tr height=51 style='height:38.25pt'>
                      <td rowspan=5 height=187 class=xl7226375 style='height:140.25pt;border-top:
                      none'>Perspectiva do Cliente</td>
                      <td rowspan=5 class=xl7826375 width=55 style='border-bottom:.5pt solid black;
                      border-top:none;width:41pt'>XXXXXXX</td>
                      <td rowspan=5 class=xl6526375 width=175 style='border-top:none;width:131pt'>Projeto
                      de Construção e apresentação de acervo de testemunhos de clientes satisfeitos
                      e demais atributos</td>
                      <td class=xl6326375 style='border-top:none;border-left:none'>Definir
                      critérios para a construção de acervo técnico (exemplo: que tipo de obra
                      obrigatoriamente será acervado no CREA? Que tipo de obra terá somente uma
                      declaração do cliente?)</td>
                      <td class=xl7526375 align=right style='border-top:none;border-left:none'>dez/09</td>
                     </tr>
                     <tr height=68 style='height:51.0pt'>
                      <td height=68 class=xl6326375 style='height:51.0pt;border-top:none;
                      border-left:none'>Definir projeto de comunicação visual da empresa para o
                      fortalecimento da marca (O que deverá ser feito? realização de catálogos,
                      books de fotos na internet, confecção de filmes, entrevistas com clientes
                      satisfeitos, etc.?) Esse projeto preferencialmente deve ser produzido por
                      empresa especialista na área.</td>
                      <td class=xl7526375 align=right style='border-top:none;border-left:none'>dez/09</td>
                     </tr>
                     <tr height=17 style='height:12.75pt'>
                      <td height=17 class=xl6326375 style='height:12.75pt;border-top:none;
                      border-left:none'>Investimento que será necessário</td>
                      <td class=xl7526375 align=right style='border-top:none;border-left:none'>dez/09</td>
                     </tr>
                     <tr height=17 style='height:12.75pt'>
                      <td height=17 class=xl6326375 style='height:12.75pt;border-top:none;
                      border-left:none'>Identificar fontes de financiamento</td>
                      <td class=xl7526375 align=right style='border-top:none;border-left:none'>dez/09</td>
                     </tr>
                     <tr height=34 style='height:25.5pt'>
                      <td height=34 class=xl6326375 style='height:25.5pt;border-top:none;
                      border-left:none'>Definir quais clientes/pessoas serão entrevistados /
                      consultados / outros e montar plano de ação</td>
                      <td class=xl7526375 align=right style='border-top:none;border-left:none'>dez/09</td>
                     </tr>
                     <tr height=34 style='height:25.5pt'>
                      <td rowspan=7 height=170 class=xl7226375 style='height:127.5pt;border-top:
                      none'>Perspectiva do Cliente</td>
                      <td rowspan=7 class=xl6726375 style='border-top:none'>XXXXXXXX</td>
                      <td rowspan=7 class=xl6526375 width=175 style='border-top:none;width:131pt'>Projeto
                      de fortalecimento da marca</td>
                      <td class=xl6326375 style='border-top:none;border-left:none'>Pesquisar quais
                      as feiras serão realizadas no Brasil e qual se adequa as necessidades da
                      Empresa</td>
                      <td class=xl7526375 align=right style='border-top:none;border-left:none'>set/09</td>
                     </tr>
                     <tr height=34 style='height:25.5pt'>
                      <td height=34 class=xl6326375 style='height:25.5pt;border-top:none;
                      border-left:none'>Definir qual será a outra feira que a Empresa irá
                      participar.</td>
                      <td class=xl7526375 align=right style='border-top:none;border-left:none'>set/09</td>
                     </tr>
                     <tr height=17 style='height:12.75pt'>
                      <td height=17 class=xl6326375 style='height:12.75pt;border-top:none;
                      border-left:none'>Definir os materiais promocionais (folders, catálogos,
                      brindes)</td>
                      <td class=xl7526375 align=right style='border-top:none;border-left:none'>set/09</td>
                     </tr>
                     <tr height=34 style='height:25.5pt'>
                      <td height=34 class=xl6326375 style='height:25.5pt;border-top:none;
                      border-left:none'>Fortalecer o relacionamento com o cliente através da
                      fomentação e participação de congressos e workshops</td>
                      <td class=xl7526375 align=right style='border-top:none;border-left:none'>set/09</td>
                     </tr>
                     <tr height=17 style='height:12.75pt'>
                      <td height=17 class=xl6326375 style='height:12.75pt;border-top:none;
                      border-left:none'>Definir clientes/pessoas chave para o convite a
                      almoços/jantares</td>
                      <td class=xl7526375 align=right style='border-top:none;border-left:none'>set/09</td>
                     </tr>
                     <tr height=17 style='height:12.75pt'>
                      <td height=17 class=xl6326375 style='height:12.75pt;border-top:none;
                      border-left:none'>Definição de feiras dentro do foco de negócios da Empresa.</td>
                      <td class=xl7526375 align=right style='border-top:none;border-left:none'>set/09</td>
                     </tr>
                     <tr height=17 style='height:12.75pt'>
                      <td height=17 class=xl6326375 style='height:12.75pt;border-top:none;
                      border-left:none'>Participar de feiras como visitantes</td>
                      <td class=xl7526375 align=right style='border-top:none;border-left:none'>set/09</td>
                     </tr>
                     <tr height=17 style='height:12.75pt'>
                      <td rowspan=7 height=136 class=xl7226375 style='height:102.0pt;border-top:
                      none'>Perspectiva Interna</td>
                      <td rowspan=7 class=xl6726375 style='border-top:none'>XXXXXXXXX</td>
                      <td rowspan=7 class=xl6526375 width=175 style='border-top:none;width:131pt'>Projeto
                      de ações de responsabilidade social</td>
                      <td class=xl6326375 style='border-top:none;border-left:none'>Levantar custos
                      envolvidos para a execução do projeto</td>
                      <td class=xl6426375 style='border-top:none;border-left:none'> </td>
                     </tr>
                     <tr height=17 style='height:12.75pt'>
                      <td height=17 class=xl6326375 style='height:12.75pt;border-top:none;
                      border-left:none'>Pesquisar e estudar sobre o conteúdo de ações de
                      responsabilidade social</td>
                      <td class=xl6426375 style='border-top:none;border-left:none'> </td>
                     </tr>
                     <tr height=17 style='height:12.75pt'>
                      <td height=17 class=xl6326375 style='height:12.75pt;border-top:none;
                      border-left:none'>Estruturar o projeto</td>
                      <td class=xl6426375 style='border-top:none;border-left:none'> </td>
                     </tr>
                     <tr height=34 style='height:25.5pt'>
                      <td height=34 class=xl6326375 style='height:25.5pt;border-top:none;
                      border-left:none'>Analisar e classificar as possíveis parcerias, convênios ou
                      filiações a outras instituições</td>
                      <td class=xl6426375 style='border-top:none;border-left:none'> </td>
                     </tr>
                     <tr height=17 style='height:12.75pt'>
                      <td height=17 class=xl6326375 style='height:12.75pt;border-top:none;
                      border-left:none'>Formalizar o patrocínios</td>
                      <td class=xl6426375 style='border-top:none;border-left:none'> </td>
                     </tr>
                     <tr height=17 style='height:12.75pt'>
                      <td height=17 class=xl6326375 style='height:12.75pt;border-top:none;
                      border-left:none'>Elaborar o Boletim Informativo Social</td>
                      <td class=xl6426375 style='border-top:none;border-left:none'> </td>
                     </tr>
                     <tr height=17 style='height:12.75pt'>
                      <td height=17 class=xl6326375 style='height:12.75pt;border-top:none;
                      border-left:none'>Realizar o Balanço Social da Empresa</td>
                      <td class=xl6426375 style='border-top:none;border-left:none'> </td>
                     </tr>
                     <tr height=17 style='page-break-before:always;height:12.75pt'>
                      <td rowspan=12 height=221 class=xl7226375 style='height:165.75pt;border-top:
                      none'>Perspectiva Interna</td>
                      <td rowspan=12 class=xl8726375 width=55 style='border-top:none;width:41pt'>XXXXXXXX</td>
                      <td rowspan=12 class=xl6526375 width=175 style='border-top:none;width:131pt'>Projeto
                      de Implantação da Política de Cargos e Salários</td>
                      <td class=xl6326375 style='border-top:none;border-left:none'>Definir
                      principais objetivos do plano de cargos e salários (PCS)</td>
                      <td class=xl6426375 style='border-top:none;border-left:none'> </td>
                     </tr>
                     <tr height=17 style='height:12.75pt'>
                      <td height=17 class=xl6326375 style='height:12.75pt;border-top:none;
                      border-left:none'>Estruturar tabelas de cargos e salários Y por pontos</td>
                      <td class=xl6426375 style='border-top:none;border-left:none'> </td>
                     </tr>
                     <tr height=17 style='height:12.75pt'>
                      <td height=17 class=xl6326375 style='height:12.75pt;border-top:none;
                      border-left:none'>Montar matriz das competências requeridas</td>
                      <td class=xl6426375 style='border-top:none;border-left:none'> </td>
                     </tr>
                     <tr height=17 style='height:12.75pt'>
                      <td height=17 class=xl6326375 style='height:12.75pt;border-top:none;
                      border-left:none'>Estabelecer conceito de remuneração total</td>
                      <td class=xl6426375 style='border-top:none;border-left:none'> </td>
                     </tr>
                     <tr height=17 style='height:12.75pt'>
                      <td height=17 class=xl6326375 style='height:12.75pt;border-top:none;
                      border-left:none'>Estabelecer o plano de benefícios</td>
                      <td class=xl6426375 style='border-top:none;border-left:none'> </td>
                     </tr>
                     <tr height=17 style='height:12.75pt'>
                      <td height=17 class=xl6326375 style='height:12.75pt;border-top:none;
                      border-left:none'>Estabelecer o plano salarial</td>
                      <td class=xl6426375 style='border-top:none;border-left:none'> </td>
                     </tr>
                     <tr height=17 style='height:12.75pt'>
                      <td height=17 class=xl6326375 style='height:12.75pt;border-top:none;
                      border-left:none'>Efetuar pesquisa salarial e de benefícios</td>
                      <td class=xl6426375 style='border-top:none;border-left:none'> </td>
                     </tr>
                     <tr height=34 style='height:25.5pt'>
                      <td height=34 class=xl6326375 style='height:25.5pt;border-top:none;
                      border-left:none'>Estabelecer políticas de salários, participações,
                      benefícios, premiações e demais formas de remunerações.</td>
                      <td class=xl6426375 style='border-top:none;border-left:none'> </td>
                     </tr>
                     <tr height=17 style='height:12.75pt'>
                      <td height=17 class=xl6326375 style='height:12.75pt;border-top:none;
                      border-left:none'>Estruturar tabela de avaliação de competências por pontos</td>
                      <td class=xl6426375 style='border-top:none;border-left:none'> </td>
                     </tr>
                     <tr height=17 style='height:12.75pt'>
                      <td height=17 class=xl6326375 style='height:12.75pt;border-top:none;
                      border-left:none'>Estruturar organograma com vagas por tipo de cargo/função</td>
                      <td class=xl6426375 style='border-top:none;border-left:none'> </td>
                     </tr>
                     <tr height=17 style='height:12.75pt'>
                      <td height=17 class=xl6326375 style='height:12.75pt;border-top:none;
                      border-left:none'>Estabelecer critérios de avaliações</td>
                      <td class=xl6426375 style='border-top:none;border-left:none'> </td>
                     </tr>
                     <tr height=17 style='height:12.75pt'>
                      <td height=17 class=xl6326375 style='height:12.75pt;border-top:none;
                      border-left:none'>Estabelecer plano de ações com base em avaliações</td>
                      <td class=xl6426375 style='border-top:none;border-left:none'> </td>
                     </tr>
                     <tr height=68 style='height:51.0pt'>
                      <td rowspan=7 height=170 class=xl7226375 style='height:127.5pt;border-top:
                      none'>Perspectiva Interna</td>
                      <td rowspan=7 class=xl7826375 width=55 style='border-bottom:.5pt solid black;
                      border-top:none;width:41pt'>XXXXXXX</td>
                      <td rowspan=7 class=xl6526375 width=175 style='border-top:none;width:131pt'>Projeto
                      para adequação da infra-estrutura física/TI para atendimento ao aumento da
                      demanda</td>
                      <td class=xl6326375 style='border-top:none;border-left:none'>Realizar projeto
                      com a infra-estrutura necessária para a Empresa, levando-se em
                      consideração todas as necessidades da operação (arquivo morto, treinamento,
                      TI, ISO 27001, etc.), bem como que a expansão do volume de mão-de-obra se
                      dará a partir do site de São Paulo.</td>
                      <td class=xl6426375 style='border-top:none;border-left:none'> </td>
                     </tr>
                     <tr height=17 style='height:12.75pt'>
                      <td height=17 class=xl6326375 style='height:12.75pt;border-top:none;
                      border-left:none'>Levantar o total de investimentos</td>
                      <td class=xl6426375 style='border-top:none;border-left:none'> </td>
                     </tr>
                     <tr height=17 style='height:12.75pt'>
                      <td height=17 class=xl6326375 style='height:12.75pt;border-top:none;
                      border-left:none'>Identificar fontes de financiamento</td>
                      <td class=xl6426375 style='border-top:none;border-left:none'> </td>
                     </tr>
                     <tr height=17 style='height:12.75pt'>
                      <td height=17 class=xl6326375 style='height:12.75pt;border-top:none;
                      border-left:none'>Identificar imóvel (eis)</td>
                      <td class=xl6426375 style='border-top:none;border-left:none'> </td>
                     </tr>
                     <tr height=17 style='height:12.75pt'>
                      <td height=17 class=xl6326375 style='height:12.75pt;border-top:none;
                      border-left:none'>Realizar reformas necessárias</td>
                      <td class=xl6426375 style='border-top:none;border-left:none'> </td>
                     </tr>
                     <tr height=17 style='height:12.75pt'>
                      <td height=17 class=xl6326375 style='height:12.75pt;border-top:none;
                      border-left:none'>Adquirir equipamentos / moveis, etc.</td>
                      <td class=xl6426375 style='border-top:none;border-left:none'> </td>
                     </tr>
                     <tr height=17 style='height:12.75pt'>
                      <td height=17 class=xl6326375 style='height:12.75pt;border-top:none;
                      border-left:none'>Efetuar mudanças</td>
                      <td class=xl6426375 style='border-top:none;border-left:none'> </td>
                     </tr>
                     <tr height=51 style='height:38.25pt'>
                      <td height=51 class=xl7226375 style='height:38.25pt;border-top:none'>Perspectiva
                      Interna</td>
                      <td class=xl7326375 width=55 style='border-left:none;width:41pt'>XXXXXXXX</td>
                      <td class=xl6526375 width=175 style='border-top:none;border-left:none;
                      width:131pt'>Projeto de aumento de produtividade associado ao adequado uso de
                      TI</td>
                      <td class=xl6326375 style='border-top:none;border-left:none'>Desenvolver
                      projeto para o estabelecimento de novas metodologias no uso dos sistemas, nos
                      processos internos, etc. de forma a gerar um aumento de produtividade.</td>
                      <td class=xl7526375 align=right style='border-top:none;border-left:none'>dez/09</td>
                     </tr>
                     <tr height=34 style='height:25.5pt'>
                      <td rowspan=3 height=85 class=xl7226375 style='height:63.75pt;border-top:
                      none'>Perspectiva Interna</td>
                      <td rowspan=3 class=xl6726375 style='border-top:none'>XXXXXXXXX</td>
                      <td rowspan=3 class=xl6526375 width=175 style='border-top:none;width:131pt'>Projeto
                      de abertura de sites</td>
                      <td class=xl6326375 style='border-top:none;border-left:none'>Estimar a
                      necessidade de mão-de-obra para os próximos 5 anos, com base no plano
                      estratégico</td>
                      <td class=xl6426375 style='border-top:none;border-left:none'> </td>
                     </tr>
                     <tr height=34 style='height:25.5pt'>
                      <td height=34 class=xl6326375 style='height:25.5pt;border-top:none;
                      border-left:none'>Definir número de pessoas para o site Principal,
                      alocação em clientes e outros sites</td>
                      <td class=xl6426375 style='border-top:none;border-left:none'> </td>
                     </tr>
                     <tr height=17 style='height:12.75pt'>
                      <td height=17 class=xl6326375 style='height:12.75pt;border-top:none;
                      border-left:none'>Montar uma comissão que cuidará de identificar o imóvel
                      necessário.</td>
                      <td class=xl6426375 style='border-top:none;border-left:none'> </td>
                     </tr>
                     <tr height=17 style='height:12.75pt'>
                      <td rowspan=4 height=102 class=xl7226375 style='height:76.5pt;border-top:
                      none'>Perspectiva Interna</td>
                      <td rowspan=4 class=xl8026375 style='border-bottom:.5pt solid black;
                      border-top:none'>XXXXXXX</td>
                      <td rowspan=4 class=xl6526375 width=175 style='border-top:none;width:131pt'>Prospecção
                      e fechamento de trabalhos com clientes novos</td>
                      <td class=xl6326375 style='border-top:none;border-left:none'>Definição dos
                      clientes estratégicos para o negócio</td>
                      <td class=xl7626375 style='border-top:none;border-left:none'>Concluido</td>
                     </tr>
                     <tr height=17 style='height:12.75pt'>
                      <td height=17 class=xl6326375 style='height:12.75pt;border-top:none;
                      border-left:none'>Programação de visitas de apresentação</td>
                      <td class=xl7626375 style='border-top:none;border-left:none'>Concluido</td>
                     </tr>
                     <tr height=34 style='height:25.5pt'>
                      <td height=34 class=xl6326375 style='height:25.5pt;border-top:none;
                      border-left:none'>Identificação de empresas de engenharia de pequeno porte
                      para estabelecimento de parcerias</td>
                      <td class=xl7526375 align=right style='border-top:none;border-left:none'>dez/09</td>
                     </tr>
                     <tr height=34 style='height:25.5pt'>
                      <td height=34 class=xl6326375 style='height:25.5pt;border-top:none;
                      border-left:none'>Contratação de equipe de vendas complementar (vendedores,
                      representantes, ou outros)</td>
                      <td class=xl7526375 align=right style='border-top:none;border-left:none'>dez/09</td>
                     </tr>
                     <tr height=136 style='page-break-before:always;height:102.0pt'>
                      <td rowspan=9 height=323 class=xl7226375 style='height:242.25pt;border-top:
                      none'>Perspectiva Interna</td>
                      <td rowspan=9 class=xl6726375 style='border-top:none'>XXXXXXX</td>
                      <td rowspan=9 class=xl6526375 width=175 style='border-top:none;width:131pt'>Projeto
                      para adequação da infra-estrutura física/TI para atendimento ao aumento da
                      demanda</td>
                      <td class=xl6326375 style='border-top:none;border-left:none'>CONSIDERAÇÕES:
                      Para analisar na necessidade partiremos do principio de que a Empresa
                      precisará mudar de endereço para adequar-se ás novas necessidades
                      organizacionais e para aumento de expansão de efetivo utilizará o site São
                      Paulo. Realizar projeto com a infra-estrutura necessária para a Empresa
                      , levando-se em consideração todas as necessidades da operação (arquivo
                      morto, treinamento, TI, ISO 27001, etc., bem como que a expansão do volume de
                      mão-de-obra se dará a partir do site de São Paulo). Vide Projeto abaixo:</td>
                      <td class=xl6426375 style='border-top:none;border-left:none'> </td>
                     </tr>
                     <tr height=68 style='height:51.0pt'>
                      <td height=68 class=xl6326375 style='height:51.0pt;border-top:none;
                      border-left:none'>Realizar projeto com a infra-estrutura necessária para a
                      Empresa, levando-se em consideração todas as necessidades da operação
                      (arquivo morto, treinamento, TI, ISO 27001, etc.), bem como que a expansão
                      do volume de mão-de-obra se dará a partir do site de São Paulo.</td>
                      <td class=xl7526375 align=right style='border-top:none;border-left:none'>out/09</td>
                     </tr>
                     <tr height=17 style='height:12.75pt'>
                      <td height=17 class=xl6326375 style='height:12.75pt;border-top:none;
                      border-left:none'>Levantar o total de investimentos</td>
                      <td class=xl7526375 align=right style='border-top:none;border-left:none'>nov/09</td>
                     </tr>
                     <tr height=17 style='height:12.75pt'>
                      <td height=17 class=xl6326375 style='height:12.75pt;border-top:none;
                      border-left:none'>Identificar fontes de financiamento</td>
                      <td class=xl7526375 align=right style='border-top:none;border-left:none'>nov/09</td>
                     </tr>
                     <tr height=17 style='height:12.75pt'>
                      <td height=17 class=xl6326375 style='height:12.75pt;border-top:none;
                      border-left:none'>Identificar imóvel (eis)</td>
                      <td class=xl7526375 align=right style='border-top:none;border-left:none'>nov/09</td>
                     </tr>
                     <tr height=17 style='height:12.75pt'>
                      <td height=17 class=xl6326375 style='height:12.75pt;border-top:none;
                      border-left:none'>Decidir sobre a mudança</td>
                      <td class=xl7526375 align=right style='border-top:none;border-left:none'>dez/09</td>
                     </tr>
                     <tr height=17 style='height:12.75pt'>
                      <td height=17 class=xl6326375 style='height:12.75pt;border-top:none;
                      border-left:none'>Realizar reformas necessárias</td>
                      <td class=xl6426375 style='border-top:none;border-left:none'> </td>
                     </tr>
                     <tr height=17 style='height:12.75pt'>
                      <td height=17 class=xl6326375 style='height:12.75pt;border-top:none;
                      border-left:none'>Adquirir equipamentos / moveis, etc.</td>
                      <td class=xl6426375 style='border-top:none;border-left:none'> </td>
                     </tr>
                     <tr height=17 style='height:12.75pt'>
                      <td height=17 class=xl6326375 style='height:12.75pt;border-top:none;
                      border-left:none'>Efetuar mudanças</td>
                      <td class=xl6426375 style='border-top:none;border-left:none'> </td>
                     </tr>
                     <tr height=187 style='height:140.25pt'>
                      <td rowspan=13 height=476 class=xl6526375 width=173 style='height:357.0pt;
                      border-top:none;width:130pt'>Perspectiva Interna</td>
                      <td rowspan=13 class=xl6726375 style='border-top:none'>XXXXXXX</td>
                      <td rowspan=13 class=xl6526375 width=175 style='border-top:none;width:131pt'>Projeto
                      para a criação do departamento de orçamentos e dos padrões orçamentários</td>
                      <td class=xl6326375 style='border-top:none;border-left:none'><font
                      class="font526375">CONSIDERAÇÕES INICIAIS</font><font class="font026375">: O
                      Orçamento deve gerar a quantidade de documentos, bem como a quantidade de
                      horas necessárias para a a realização dos trabalhos, que serão usados
                      internamente para o planejamento e controle, bem como a proposta (Técnico /
                      Comercial) que será fornecida ao cliente (objeto do orçamento que esta sendo
                      realizado). (2) O preço de venda, poderá ser formulado de três maneiras: a)
                      Com base no custo, b) Com base no mercado e c) com base na expectativa do
                      cliente. Caberá á área de venda formular o preço de forma a permitir o
                      atendimento das metas de venda buscando a maximização do resultado para a
                      empresa (Atendimento do objetivo Preço Justo). 3) O orçamento deve ser
                      realizado de forma a poder ser acompanhado financeiramente e fisicamente.</font></td>
                      <td class=xl6426375 style='border-top:none;border-left:none'> </td>
                     </tr>
                     <tr height=17 style='height:12.75pt'>
                      <td height=17 class=xl7026375 style='height:12.75pt;border-top:none;
                      border-left:none'>Criação do departamento de Orçamento</td>
                      <td class=xl7526375 align=right style='border-top:none;border-left:none'>set/09</td>
                     </tr>
                     <tr height=17 style='height:12.75pt'>
                      <td height=17 class=xl6326375 style='height:12.75pt;border-top:none;
                      border-left:none'>Definir a missão e o conceito de departamento de orçamento
                      dentro da Empresa</td>
                      <td class=xl7626375 style='border-top:none;border-left:none'> </td>
                     </tr>
                     <tr height=34 style='height:25.5pt'>
                      <td height=34 class=xl6326375 style='height:25.5pt;border-top:none;
                      border-left:none'>Formalização do organograma do departamento, bem como
                      responsabilidades, competências, etc.</td>
                      <td class=xl7626375 style='border-top:none;border-left:none'> </td>
                     </tr>
                     <tr height=17 style='height:12.75pt'>
                      <td height=17 class=xl6326375 style='height:12.75pt;border-top:none;
                      border-left:none'>Identificação e contratação do Coordenador do departamento
                      de Orçamento</td>
                      <td class=xl7626375 style='border-top:none;border-left:none'> </td>
                     </tr>
                     <tr height=17 style='height:12.75pt'>
                      <td height=17 class=xl7026375 style='height:12.75pt;border-top:none;
                      border-left:none'>Criação dos Padrões Orçamentários</td>
                      <td class=xl7526375 align=right style='border-top:none;border-left:none'>set/09</td>
                     </tr>
                     <tr height=34 style='height:25.5pt'>
                      <td height=34 class=xl6326375 style='height:25.5pt;border-top:none;
                      border-left:none'>Definição das estratégias de abordagem dos clientes e/ou
                      projetos para a realização dos orçamentos (porte pequeno, altamente complexo,
                      etc.)</td>
                      <td class=xl7626375 style='border-top:none;border-left:none'> </td>
                     </tr>
                     <tr height=17 style='height:12.75pt'>
                      <td height=17 class=xl6326375 style='height:12.75pt;border-top:none;
                      border-left:none'>Definir e documentar o fluxograma do processo orçamentário.</td>
                      <td class=xl7626375 style='border-top:none;border-left:none'> </td>
                     </tr>
                     <tr height=51 style='height:38.25pt'>
                      <td height=51 class=xl6326375 style='height:38.25pt;border-top:none;
                      border-left:none'>Utilização dos sistemas padronizados da empresa e/ou do
                      novo ERP, considerando estrutura detalhada de custos e complementar no que
                      for necessário.</td>
                      <td class=xl7626375 style='border-top:none;border-left:none'> </td>
                     </tr>
                     <tr height=17 style='height:12.75pt'>
                      <td height=17 class=xl7026375 style='height:12.75pt;border-top:none;
                      border-left:none'>Criação dos Novos Padrões de Proposta considerando:</td>
                      <td class=xl7526375 align=right style='border-top:none;border-left:none'>set/09</td>
                     </tr>
                     <tr height=17 style='height:12.75pt'>
                      <td height=17 class=xl6326375 style='height:12.75pt;border-top:none;
                      border-left:none'>Delimitação de Escopo, Cronograma, Serviços adicionais,
                      exclusões, etc.</td>
                      <td class=xl7626375 style='border-top:none;border-left:none'> </td>
                     </tr>
                     <tr height=34 style='height:25.5pt'>
                      <td height=34 class=xl6326375 style='height:25.5pt;border-top:none;
                      border-left:none'>Definir o conceito de &quot;Abrangência do Escopo&quot; no
                      procedimento padrão para elaboração de orçamento</td>
                      <td class=xl7626375 style='border-top:none;border-left:none'> </td>
                     </tr>
                     <tr height=17 style='height:12.75pt'>
                      <td height=17 class=xl6326375 style='height:12.75pt;border-top:none;
                      border-left:none'>Elaborar uma nova apresentação da proposta, mais
                      profissional.</td>
                      <td class=xl7626375 style='border-top:none;border-left:none'> </td>
                     </tr>
                     <tr height=17 style='page-break-before:always;height:12.75pt'>
                      <td rowspan=12 height=255 class=xl7226375 style='height:191.25pt;border-top:
                      none'>Perspectiva Interna</td>
                      <td rowspan=12 class=xl6726375 style='border-top:none'>XXXXXXX</td>
                      <td rowspan=12 class=xl6526375 width=175 style='border-top:none;width:131pt'>Projeto
                      de Criação do departamento de Planejamento e Controle</td>
                      <td class=xl7026375 style='border-top:none;border-left:none'>Criação do
                      departamento de Planejamento e Controle</td>
                      <td class=xl7526375 align=right style='border-top:none;border-left:none'>set/09</td>
                     </tr>
                     <tr height=17 style='height:12.75pt'>
                      <td height=17 class=xl6326375 style='height:12.75pt;border-top:none;
                      border-left:none'>Definição do papel do departamento</td>
                      <td class=xl7626375 style='border-top:none;border-left:none'> </td>
                     </tr>
                     <tr height=34 style='height:25.5pt'>
                      <td height=34 class=xl6326375 style='height:25.5pt;border-top:none;
                      border-left:none'>Formalização do organograma do departamento, bem como
                      responsabilidades, competências, etc.</td>
                      <td class=xl7626375 style='border-top:none;border-left:none'> </td>
                     </tr>
                     <tr height=17 style='height:12.75pt'>
                    
                      <td height=17 class=xl6326375 style='height:12.75pt;border-top:none;
                      border-left:none'>Identificação e contratação do Coordenador do departamento
                      de Orçamento</td>
                      <td class=xl7626375 style='border-top:none;border-left:none'> </td>
                     </tr>
                     <tr height=17 style='height:12.75pt'>
                      <td height=17 class=xl7026375 style='height:12.75pt;border-top:none;
                      border-left:none'>Criação dos Padrões de Planejamento e Controle</td>
                      <td class=xl7526375 align=right style='border-top:none;border-left:none'>set/09</td>
                     </tr>
                     <tr height=17 style='height:12.75pt'>
                      <td height=17 class=xl6326375 style='height:12.75pt;border-top:none;
                      border-left:none'>Definir e documentar o fluxograma do processo orçamentário.</td>
                      <td class=xl7626375 style='border-top:none;border-left:none'> </td>
                     </tr>
                     <tr height=17 style='height:12.75pt'>
                      <td height=17 class=xl6326375 style='height:12.75pt;border-top:none;
                      border-left:none'>Utilização dos sistemas padronizados da empresa e/ou do
                      novo ERP.</td>
                      <td class=xl7626375 style='border-top:none;border-left:none'> </td>
                     </tr>
                     <tr height=34 style='height:25.5pt'>
                      <td height=34 class=xl6326375 style='height:25.5pt;border-top:none;
                      border-left:none'>Definir os critérios de acompanhamento entre eles:
                      frequência de comunicação dos serviços realizados, medições, programação dos
                      serviços, etc.</td>
                      <td class=xl7626375 style='border-top:none;border-left:none'> </td>
                     </tr>
                     <tr height=17 style='height:12.75pt'>
                      <td height=17 class=xl7026375 style='height:12.75pt;border-top:none;
                      border-left:none'>Criação das Condições para Medição do Retrabalho</td>
                      <td class=xl7526375 align=right style='border-top:none;border-left:none'>set/09</td>
                     </tr>
                     <tr height=17 style='height:12.75pt'>
                      <td height=17 class=xl6326375 style='height:12.75pt;border-top:none;
                      border-left:none'>Criar mecanismo de apuração de horas de re-trabalho e
                      implantá-lo.</td>
                      <td class=xl7626375 style='border-top:none;border-left:none'> </td>
                     </tr>
                     <tr height=17 style='height:12.75pt'>
                      <td height=17 class=xl7026375 style='height:12.75pt;border-top:none;
                      border-left:none'>Criação das Condições para Medição da Produtividade Geral</td>
                      <td class=xl7526375 align=right style='border-top:none;border-left:none'>set/09</td>
                     </tr>
                     <tr height=34 style='height:25.5pt'>
                      <td height=34 class=xl6326375 style='height:25.5pt;border-top:none;
                      border-left:none'>Criar mecanismo de apuração de horas de totais gastas, bem
                      como de número de documentos produzidos</td>
                      <td class=xl7626375 style='border-top:none;border-left:none'> </td>
                     </tr>
                     <tr height=51 style='height:38.25pt'>
                      <td height=51 class=xl6626375 style='height:38.25pt;border-top:none'>Perspectiva
                      Interna</td>
                      <td class=xl6626375 style='border-top:none;border-left:none'>XXXXXXXX</td>
                      <td class=xl6826375 width=175 style='border-top:none;border-left:none;
                      width:131pt'>Projeto de Implantação do sistema de gestão de normas técnicas</td>
                      <td class=xl6326375 style='border-top:none;border-left:none'>Finalizar o
                      processo de implantação do sistema de gestão de normas técnicas</td>
                      <td class=xl6426375 style='border-top:none;border-left:none'> </td>
                     </tr>
                     <tr height=34 style='height:25.5pt'>
                      <td height=34 class=xl6626375 style='height:25.5pt;border-top:none'>Perspecitva
                      Interna</td>
                      <td class=xl6626375 style='border-top:none;border-left:none'>XXXXXXXX</td>
                      <td class=xl7726375 width=175 style='width:131pt'>Projetos de formação de
                      cultura de liderança e inovação.<span style='mso-spacerun:yes'></span></td>
                      <td class=xl6326375 style='border-top:none'>Desenvolver projeto para a
                      definição de como a questão da liderança será desenvolvida na
                      Empresa.</td>
                      <td class=xl6426375 style='border-top:none;border-left:none'> </td>
                     </tr>
                     <tr height=85 style='height:63.75pt'>
                      <td rowspan=3 height=136 class=xl6526375 width=173 style='height:102.0pt;
                      border-top:none;width:130pt'>Perspectiva de Aprendizado e Crescimento</td>
                      <td rowspan=3 class=xl8026375 style='border-bottom:.5pt solid black;
                      border-top:none'>XXXXXXXX</td>
                      <td rowspan=3 class=xl7226375>Projeto Treinamento</td>
                      <td class=xl6326375 style='border-top:none;border-left:none'>Com base na
                      estrutura de cargos e salários e a matriz de competências elaborar grade de
                      cursos completa para a Empresa, indo dos cursos básicos ao MBA e idiomas,
                      verificando onde e como serão realizados cada um dos cursos, carga horária,
                      conteúdo, realizador, etc., bem como os critérios de avaliação das
                      participações, inclusive para estagiários e trainees.</td>
                      <td class=xl7426375 align=right style='border-top:none;border-left:none'>set/09</td>
                     </tr>
                     <tr height=17 style='height:12.75pt'>
                      <td height=17 class=xl6326375 style='height:12.75pt;border-top:none;
                      border-left:none'>Estabelecer as políticas de subsídios pela Empresa aos
                      colaboradores.</td>
                      <td class=xl7426375 align=right style='border-top:none;border-left:none'>set/09</td>
                     </tr>
                     <tr height=34 style='height:25.5pt'>
                      <td height=34 class=xl6326375 style='height:25.5pt;border-top:none;
                      border-left:none'>Analisar a viabilidade da criação de uma área (local -
                      espaço físico, bem como infra-estrutura requerida) permanente de treinamento
                      na Empresa.</td>
                      <td class=xl7426375 align=right style='border-top:none;border-left:none'>set/09</td>
                     </tr>
                     <tr height=34 style='page-break-before:always;height:25.5pt'>
                      <td rowspan=3 height=102 class=xl6526375 width=173 style='height:76.5pt;
                      border-top:none;width:130pt'>Perspectiva de Aprendizado e Crescimento</td>
                      <td rowspan=3 class=xl8026375 style='border-bottom:.5pt solid black;
                      border-top:none'>XXXXXXXX</td>
                      <td rowspan=3 class=xl8226375 width=175 style='border-bottom:.5pt solid black;
                      border-top:none;width:131pt'>Projeto de Captação e Retenção de Mão de Obra</td>
                      <td class=xl6326375 style='border-top:none;border-left:none'>Estabelecimento
                      dos processos de seleção por tipo de cargo e competência requerida.</td>
                      <td class=xl7426375 align=right style='border-top:none;border-left:none'>dez/09</td>
                     </tr>
                     <tr height=17 style='height:12.75pt'>
                      <td height=17 class=xl6326375 style='height:12.75pt;border-top:none;
                      border-left:none'>Formatação dos programas de trainee e de estagiários.</td>
                      <td class=xl7426375 align=right style='border-top:none;border-left:none'>dez/09</td>
                     </tr>
                     <tr height=51 style='height:38.25pt'>
                      <td height=51 class=xl6326375 style='height:38.25pt;border-top:none;
                      border-left:none'>Estabelecimento de um plano de ações para ampliação das
                      fontes de identificação e captação de mão de obra (cadastros órgãos de
                      classe, governo, consultorias de RH, escolas e faculdades, etc.)</td>
                      <td class=xl7426375 align=right style='border-top:none;border-left:none'>dez/09</td>
                     </tr>
                     <tr height=57 style='mso-height-source:userset;height:42.75pt'>
                      <td rowspan=2 height=110 class=xl6526375 width=173 style='height:82.5pt;
                      border-top:none;width:130pt'>Perspectiva de Aprendizado e Crescimento</td>
                      <td rowspan=2 class=xl6726375 style='border-top:none'>XXXXXXXX</td>
                      <td rowspan=2 class=xl6526375 width=175 style='border-top:none;width:131pt'>Projeto
                      para estabelecer e implantar critérios de medição do resultado por projeto e
                      do desempenho individual de cada funcionário</td>
                      <td class=xl6326375 style='border-top:none;border-left:none'>Montar comissão
                      para determinação de critérios de participação em resultados para
                      funcionários.</td>
                      <td class=xl7526375 align=right style='border-top:none;border-left:none'>dez/09</td>
                     </tr>
                     <tr height=53 style='mso-height-source:userset;height:39.75pt'>
                      <td height=53 class=xl6326375 style='height:39.75pt;border-top:none;
                      border-left:none'>Montar comissão para determinação de critérios de
                      produtividade para prestadores de serviços</td>
                      <td class=xl7526375 align=right style='border-top:none;border-left:none'>dez/09</td>
                     </tr>
                     <tr height=34 style='height:25.5pt'>
                      <td height=34 class=xl6926375 width=173 style='height:25.5pt;border-top:none;
                      width:130pt'>Perspectiva de Aprendizado e Crescimento</td>
                      <td class=xl6726375 style='border-top:none;border-left:none'>XXXXXXXX</td>
                      <td class=xl6626375 style='border-top:none;border-left:none'>Projeto de
                      Implantação do ERP</td>
                      <td class=xl6326375 style='border-top:none;border-left:none'>Finalizar o
                      processo de implantação do ERP</td>
                      <td class=xl7526375 align=right style='border-top:none;border-left:none'>dez/09</td>
                     </tr>
                     <tr height=34 style='height:25.5pt'>
                      <td height=34 class=xl6926375 width=173 style='height:25.5pt;border-top:none;
                      width:130pt'>Perspectiva de Aprendizado e Crescimento</td>
                      <td class=xl6726375 style='border-top:none;border-left:none'>XXXXXXXX</td>
                      <td class=xl6626375 style='border-top:none;border-left:none'>Projeto de
                      Implantação do GED</td>
                      <td class=xl6326375 style='border-top:none;border-left:none'>Finalizar o
                      processo de implantação do GED</td>
                      <td class=xl7626375 style='border-top:none;border-left:none'>Concluido</td>
                     </tr>
                     <tr height=68 style='mso-height-source:userset;height:51.0pt'>
                      <td rowspan=15 height=340 class=xl6526375 width=173 style='height:255.0pt;
                      border-top:none;width:130pt'>Perspectiva de Aprendizado e Crescimento</td>
                      <td rowspan=15 class=xl6726375 style='border-top:none'>XXXXXXXX</td>
                      <td rowspan=15 class=xl6526375 width=175 style='border-top:none;width:131pt'>Projeto
                      de Adequação das bases de dados e políticas de tratamento da informação (
                      Ligado a ISO 27001)</td>
                      <td class=xl6326375 style='border-top:none;border-left:none'>Normalizar os
                      bancos de dados</td>
                      <td class=xl6426375 style='border-top:none;border-left:none'> </td>
                     </tr>
                     <tr height=17 style='mso-height-source:userset;height:12.75pt'>
                      <td height=17 class=xl6326375 style='height:12.75pt;border-top:none;
                      border-left:none'>Criar rotinas/metodologias de back-up e segurança dos dados</td>
                      <td class=xl6426375 style='border-top:none;border-left:none'> </td>
                     </tr>
                     <tr height=51 style='height:38.25pt'>
                      <td height=51 class=xl6326375 style='height:38.25pt;border-top:none;
                      border-left:none'>Elaborar Relatórios de análises para o back-up, recursos
                      consumidos pelo banco de dados (desempenho), tipos de erros para adequar os
                      tipos de dados e outros Relatórios se aplicáveis</td>
                      <td class=xl6426375 style='border-top:none;border-left:none'> </td>
                     </tr>
                     <tr height=17 style='height:12.75pt'>
                      <td height=17 class=xl6326375 style='height:12.75pt;border-top:none;
                      border-left:none'>Testar integridade do banco de dados com os softwares que
                      dependem desta base</td>
                      <td class=xl6426375 style='border-top:none;border-left:none'> </td>
                     </tr>
                     <tr height=17 style='height:12.75pt'>
                      <td height=17 class=xl6326375 style='height:12.75pt;border-top:none;
                      border-left:none'>Verificar se os resultados são compatíveis com o teste</td>
                      <td class=xl6426375 style='border-top:none;border-left:none'> </td>
                     </tr>
                     <tr height=17 style='height:12.75pt'>
                      <td height=17 class=xl6326375 style='height:12.75pt;border-top:none;
                      border-left:none'>Formar comitê</td>
                      <td class=xl6426375 style='border-top:none;border-left:none'> </td>
                     </tr>
                     <tr height=17 style='height:12.75pt'>
                      <td height=17 class=xl6326375 style='height:12.75pt;border-top:none;
                      border-left:none'>Identificar ativos</td>
                      <td class=xl6426375 style='border-top:none;border-left:none'> </td>
                     </tr>
                     <tr height=17 style='height:12.75pt'>
                      <td height=17 class=xl6326375 style='height:12.75pt;border-top:none;
                      border-left:none'>Analisar de riscos dos ativos identificados</td>
                      <td class=xl6426375 style='border-top:none;border-left:none'> </td>
                     </tr>
                     <tr height=17 style='height:12.75pt'>
                      <td height=17 class=xl6326375 style='height:12.75pt;border-top:none;
                      border-left:none'>Criar de plano de contingências</td>
                      <td class=xl6426375 style='border-top:none;border-left:none'> </td>
                     </tr>
                     <tr height=17 style='height:12.75pt'>
                      <td height=17 class=xl6326375 style='height:12.75pt;border-top:none;
                      border-left:none'>Adequar a infraestrutura do prédio</td>
                      <td class=xl6426375 style='border-top:none;border-left:none'> </td>
                     </tr>
                     <tr height=17 style='height:12.75pt'>
                      <td height=17 class=xl6326375 style='height:12.75pt;border-top:none;
                      border-left:none'>Formar auditores internos</td>
                      <td class=xl6426375 style='border-top:none;border-left:none'> </td>
                     </tr>
                     <tr height=17 style='height:12.75pt'>
                      <td height=17 class=xl6326375 style='height:12.75pt;border-top:none;
                      border-left:none'>Elaborar plano de ações corretivas e preventivas</td>
                      <td class=xl6426375 style='border-top:none;border-left:none'> </td>
                     </tr>
                     <tr height=17 style='height:12.75pt'>
                      <td height=17 class=xl6326375 style='height:12.75pt;border-top:none;
                      border-left:none'>Selecionar organismo certificador</td>
                      <td class=xl6426375 style='border-top:none;border-left:none'> </td>
                     </tr>
                     <tr height=17 style='height:12.75pt'>
                      <td height=17 class=xl6326375 style='height:12.75pt;border-top:none;
                      border-left:none'>Realizar auditoria de certificação</td>
                      <td class=xl6426375 style='border-top:none;border-left:none'> </td>
                     </tr>
                     <tr height=17 style='height:12.75pt'>
                      <td height=17 class=xl6326375 style='height:12.75pt;border-top:none;
                      border-left:none'>Certificar o sistema da ISO 27001</td>
                      <td class=xl7526375 align=right style='border-top:none;border-left:none'>dez/10</td>
                     </tr>
                     <tr height=34 style='height:25.5pt'>
                      <td height=34 class=xl6926375 width=173 style='height:25.5pt;border-top:none;
                      width:130pt'>Perspectiva de Aprendizado e Crescimento</td>
                      <td class=xl6726375 style='border-top:none;border-left:none'>XXXXXXXX</td>
                      <td class=xl6526375 width=175 style='border-top:none;border-left:none;
                      width:131pt'>Projeto para estabelecer cultura de liderança</td>
                      <td class=xl6326375 style='border-top:none;border-left:none'>Desenvolver
                      projeto que estabeleça os elementos necessários para que se desenvolvam
                      líderes, desde as ações de treinamento, avaliações, etc.</td>
                      <td class=xl7526375 align=right style='border-top:none;border-left:none'>dez/09</td>
                     </tr>
                     <tr height=68 style='height:51.0pt'>
                      <td height=68 class=xl6926375 width=173 style='height:51.0pt;border-top:none;
                      width:130pt'>Perspectiva de Aprendizado e Crescimento</td>
                      <td class=xl6726375 style='border-top:none;border-left:none'>XXXXXXXX</td>
                      <td class=xl6526375 width=175 style='border-top:none;border-left:none;
                      width:131pt'>Projeto para estabelecer cultura de Inovação</td>
                      <td class=xl6326375 style='border-top:none;border-left:none'>Desenvolver
                      projeto que estabeleça os elementos necessários para que se desenvolva uma
                      cultura de inovações, desde a criação do ambiente na empresa, busca de novas
                      tecnologias, formação de parcerias, participação em universidades,
                      associações, etc.</td>
                      <td class=xl7526375 align=right style='border-top:none;border-left:none'>dez/09</td>
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


