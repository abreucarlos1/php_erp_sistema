<?php
/*

		Formulário de Planejamento estrategico resumo	
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		../planejamento_estrategico/planejamento_estrategico_resumo.php
		
		Versão 0 --> VERSÃO INICIAL - 10/03/2006
		Versao 1 --> Atualização classe banco de dados - 22/01/2015 - Carlos Abreu
		
*/	

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(218))
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
 id="Revisão do Planejamento Estratégico - 2009 Rev 03_divulgar_15980_Styles">

@media print {body {display:none;}}


<!--table
	{mso-displayed-decimal-separator:"\,";
	mso-displayed-thousand-separator:"\.";}
.font015980
	{color:black;
	font-size:10.0pt;
	font-weight:400;
	font-style:normal;
	text-decoration:none;
	font-family:Calibri, sans-serif;
	mso-font-charset:0;}
.font515980
	{color:black;
	font-size:10.0pt;
	font-weight:400;
	font-style:normal;
	text-decoration:none;
	font-family:Arial, sans-serif;
	mso-font-charset:0;}
.xl1515980
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
.xl6315980
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
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:nowrap;}
.xl6415980
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
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:normal;}
.xl6515980
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
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:normal;}
.xl6615980
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
	white-space:normal;}
.xl6715980
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
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:nowrap;}
.xl6815980
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
.xl6915980
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
.xl7015980
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
.xl7115980
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
.xl7215980
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
.xl7315980
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
.xl7415980
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
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:nowrap;}
.xl7515980
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
.xl7615980
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
.xl7715980
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
	vertical-align:bottom;
	border:.5pt solid windowtext;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:normal;}
.xl7815980
	{padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:black;
	font-size:14.0pt;
	font-weight:700;
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
.xl7915980
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
	text-align:general;
	vertical-align:bottom;
	border:.5pt solid windowtext;
	background:black;
	mso-pattern:black none;
	white-space:nowrap;}
.xl8015980
	{padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:white;
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
	background:black;
	mso-pattern:black none;
	white-space:nowrap;}
.xl8115980
	{padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:white;
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
	background:black;
	mso-pattern:black none;
	white-space:nowrap;}
.xl8215980
	{padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:white;
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
	background:black;
	mso-pattern:black none;
	white-space:nowrap;}
.xl8315980
	{padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:white;
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
	background:black;
	mso-pattern:black none;
	white-space:normal;}
.xl8415980
	{padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:white;
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
	background:black;
	mso-pattern:black none;
	white-space:normal;}
.xl8515980
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
.xl8615980
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
	background:yellow;
	mso-pattern:black none;
	white-space:normal;}
.xl8715980
	{padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:white;
	font-size:10.0pt;
	font-weight:700;
	font-style:normal;
	text-decoration:none;
	font-family:Calibri, sans-serif;
	mso-font-charset:0;
	mso-number-format:General;
	text-align:general;
	vertical-align:bottom;
	border:.5pt solid windowtext;
	background:gray;
	mso-pattern:black none;
	white-space:nowrap;}
.xl8815980
	{padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:white;
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
	background:gray;
	mso-pattern:black none;
	white-space:normal;}
.xl8915980
	{padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:white;
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
	background:gray;
	mso-pattern:black none;
	white-space:normal;}
.xl9015980
	{padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:white;
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
	background:gray;
	mso-pattern:black none;
	white-space:nowrap;}
.xl9115980
	{padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:white;
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
	background:gray;
	mso-pattern:black none;
	white-space:nowrap;}
.xl9215980
	{padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:white;
	font-size:10.0pt;
	font-weight:700;
	font-style:normal;
	text-decoration:none;
	font-family:Calibri, sans-serif;
	mso-font-charset:0;
	mso-number-format:General;
	text-align:general;
	vertical-align:middle;
	border:.5pt solid windowtext;
	background:gray;
	mso-pattern:black none;
	white-space:nowrap;}
.xl9315980
	{padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:white;
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
	background:gray;
	mso-pattern:black none;
	white-space:nowrap;}
.xl9415980
	{padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:white;
	font-size:10.0pt;
	font-weight:700;
	font-style:normal;
	text-decoration:none;
	font-family:Calibri, sans-serif;
	mso-font-charset:0;
	mso-number-format:General;
	text-align:general;
	vertical-align:bottom;
	border:.5pt solid windowtext;
	background:gray;
	mso-pattern:black none;
	white-space:normal;}
.xl9515980
	{padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:white;
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
	background:gray;
	mso-pattern:black none;
	white-space:nowrap;}
.xl9615980
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
	text-align:center;
	vertical-align:middle;
	border:.5pt solid windowtext;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:normal;}
.xl9715980
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
	mso-number-format:"_\(* \#\,\#\#0\.00_\)\;_\(* \\\(\#\,\#\#0\.00\\\)\;_\(* \0022-\0022??_\)\;_\(\@_\)";
	text-align:general;
	vertical-align:bottom;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:nowrap;}
.xl9815980
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
	mso-number-format:"_\(* \#\,\#\#0\.00_\)\;_\(* \\\(\#\,\#\#0\.00\\\)\;_\(* \0022-\0022??_\)\;_\(\@_\)";
	text-align:general;
	vertical-align:bottom;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:nowrap;}
.xl9915980
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
	text-align:right;
	vertical-align:middle;
	border:.5pt solid windowtext;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:nowrap;}
.xl10015980
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
	mso-number-format:"Short Date";
	text-align:general;
	vertical-align:middle;
	border:.5pt solid windowtext;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:nowrap;}
.xl10115980
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
.xl10215980
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
	text-align:right;
	vertical-align:middle;
	border:.5pt solid windowtext;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:normal;}
.xl10315980
	{padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:white;
	font-size:10.0pt;
	font-weight:400;
	font-style:normal;
	text-decoration:none;
	font-family:Calibri, sans-serif;
	mso-font-charset:0;
	mso-number-format:General;
	text-align:right;
	vertical-align:middle;
	border:.5pt solid windowtext;
	background:gray;
	mso-pattern:black none;
	white-space:normal;}
.xl10415980
	{padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:white;
	font-size:10.0pt;
	font-weight:400;
	font-style:normal;
	text-decoration:none;
	font-family:Calibri, sans-serif;
	mso-font-charset:0;
	mso-number-format:General;
	text-align:right;
	vertical-align:bottom;
	border:.5pt solid windowtext;
	background:gray;
	mso-pattern:black none;
	white-space:nowrap;}
.xl10515980
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
	text-align:right;
	vertical-align:middle;
	border:.5pt solid windowtext;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:normal;}
.xl10615980
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
	mso-number-format:0%;
	text-align:right;
	vertical-align:bottom;
	border:.5pt solid windowtext;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:nowrap;}
.xl10715980
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
	text-align:right;
	vertical-align:bottom;
	border:.5pt solid windowtext;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:nowrap;}
.xl10815980
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
	text-align:right;
	vertical-align:bottom;
	border:.5pt solid windowtext;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:normal;}
.xl10915980
	{padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:white;
	font-size:10.0pt;
	font-weight:400;
	font-style:normal;
	text-decoration:none;
	font-family:Calibri, sans-serif;
	mso-font-charset:0;
	mso-number-format:General;
	text-align:right;
	vertical-align:middle;
	border:.5pt solid windowtext;
	background:black;
	mso-pattern:black none;
	white-space:normal;}
.xl11015980
	{padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:white;
	font-size:10.0pt;
	font-weight:400;
	font-style:normal;
	text-decoration:none;
	font-family:Calibri, sans-serif;
	mso-font-charset:0;
	mso-number-format:General;
	text-align:right;
	vertical-align:bottom;
	border:.5pt solid windowtext;
	background:black;
	mso-pattern:black none;
	white-space:nowrap;}
.xl11115980
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
	mso-number-format:0%;
	text-align:right;
	vertical-align:middle;
	border:.5pt solid windowtext;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:normal;}
.xl11215980
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
	text-align:right;
	vertical-align:middle;
	border:.5pt solid windowtext;
	background:yellow;
	mso-pattern:black none;
	white-space:normal;}
.xl11315980
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
	mso-number-format:"_\(* \#\,\#\#0\.00_\)\;_\(* \\\(\#\,\#\#0\.00\\\)\;_\(* \0022-\0022??_\)\;_\(\@_\)";
	text-align:right;
	vertical-align:middle;
	border:.5pt solid windowtext;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:normal;}
.xl11415980
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
	text-align:right;
	vertical-align:bottom;
	border:.5pt solid windowtext;
	background:yellow;
	mso-pattern:black none;
	white-space:nowrap;}
.xl11515980
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
	text-align:right;
	vertical-align:bottom;
	border:.5pt solid windowtext;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:nowrap;}
.xl11615980
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
	text-align:right;
	vertical-align:middle;
	border:.5pt solid windowtext;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:nowrap;}
.xl11715980
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
	mso-number-format:0%;
	text-align:right;
	vertical-align:middle;
	border:.5pt solid windowtext;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:nowrap;}
.xl11815980
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
	mso-number-format:0%;
	text-align:right;
	vertical-align:middle;
	border:.5pt solid windowtext;
	background:yellow;
	mso-pattern:black none;
	white-space:nowrap;}
.xl11915980
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
.xl12015980
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
.xl12115980
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
.xl12215980
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
.xl12315980
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
.xl12415980
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
.xl12515980
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
	vertical-align:bottom;
	border-top:.5pt solid windowtext;
	border-right:.5pt solid windowtext;
	border-bottom:none;
	border-left:.5pt solid windowtext;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:normal;}
.xl12615980
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
	vertical-align:bottom;
	border-top:none;
	border-right:.5pt solid windowtext;
	border-bottom:.5pt solid windowtext;
	border-left:.5pt solid windowtext;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:normal;}
.xl12715980
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
	text-align:right;
	vertical-align:middle;
	border-top:.5pt solid windowtext;
	border-right:.5pt solid windowtext;
	border-bottom:none;
	border-left:.5pt solid windowtext;
	background:yellow;
	mso-pattern:black none;
	white-space:nowrap;}
.xl12815980
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
	text-align:right;
	vertical-align:middle;
	border-top:none;
	border-right:.5pt solid windowtext;
	border-bottom:.5pt solid windowtext;
	border-left:.5pt solid windowtext;
	background:yellow;
	mso-pattern:black none;
	white-space:nowrap;}
.xl12915980
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
	text-align:right;
	vertical-align:middle;
	border-top:.5pt solid windowtext;
	border-right:.5pt solid windowtext;
	border-bottom:none;
	border-left:.5pt solid windowtext;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:nowrap;}
.xl13015980
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
	text-align:right;
	vertical-align:middle;
	border-top:none;
	border-right:.5pt solid windowtext;
	border-bottom:.5pt solid windowtext;
	border-left:.5pt solid windowtext;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:nowrap;}
.xl13115980
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
.xl13215980
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
.xl13315980
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
	text-align:right;
	vertical-align:middle;
	border-top:.5pt solid windowtext;
	border-right:.5pt solid windowtext;
	border-bottom:none;
	border-left:.5pt solid windowtext;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:normal;}
.xl13415980
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
	text-align:right;
	vertical-align:middle;
	border-top:none;
	border-right:.5pt solid windowtext;
	border-bottom:.5pt solid windowtext;
	border-left:.5pt solid windowtext;
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
id="Revisão do Planejamento Estratégico - 2009 Rev 03_divulgar_15980"
align=center x:publishsource="Excel">

<table border=0 cellpadding=0 cellspacing=0 width=1243 style='border-collapse:
 collapse;table-layout:fixed;width:932pt'>
 <col width=355 style='mso-width-source:userset;mso-width-alt:12982;width:266pt'>
 <col width=324 style='mso-width-source:userset;mso-width-alt:11849;width:243pt'>
 <col width=85 style='mso-width-source:userset;mso-width-alt:3108;width:64pt'>
 <col width=76 style='mso-width-source:userset;mso-width-alt:2779;width:57pt'>
 <col width=157 style='mso-width-source:userset;mso-width-alt:5741;width:118pt'>
 <col width=64 style='width:48pt'>
 <col width=95 style='mso-width-source:userset;mso-width-alt:3474;width:71pt'>
 <col width=87 style='mso-width-source:userset;mso-width-alt:3181;width:65pt'>
 <tr height=25 style='height:18.75pt'>
  <td height=25 class=xl7815980 width=355 style='height:18.75pt;width:266pt'><a
  name="RANGE!A1:E87">MAPA ESTRATÉGICO / OBJETIVOS</a></td>
  <td class=xl7815980 width=324 style='border-left:none;width:243pt'>INDICADORES</td>
  <td class=xl7815980 width=85 style='border-left:none;width:64pt'>METAS</td>
  <td class=xl7815980 width=76 style='border-left:none;width:57pt'>PRAZO</td>
  <td class=xl7815980 width=157 style='border-left:none;width:118pt'>AÇÃO
  ASSOCIADA</td>
  </tr>
 <tr height=25 style='height:18.75pt'>
  <td height=25 class=xl7915980 style='height:18.75pt;border-top:none'>Perspectiva
  Financeira</td>
  <td class=xl8015980 style='border-top:none;border-left:none'> </td>
  <td class=xl8115980 style='border-top:none;border-left:none'> </td>
  <td class=xl8015980 style='border-top:none;border-left:none'> </td>
  <td class=xl8215980 style='border-top:none;border-left:none'> </td>
  </tr>
 <tr height=17 style='height:12.75pt'>
  <td height=17 class=xl8715980 style='height:12.75pt;border-top:none'>Objetivo
  Final</td>
  <td class=xl9015980 style='border-top:none;border-left:none'> </td>
  <td class=xl9515980 style='border-top:none;border-left:none'> </td>
  <td class=xl9015980 style='border-top:none;border-left:none'> </td>
  <td class=xl9115980 style='border-top:none;border-left:none'> </td>
  </tr>
 <tr height=17 style='height:12.75pt'>
  <td rowspan=4 height=68 class=xl6815980 style='height:51.0pt;border-top:none'>F1:
  LAJIR =25% sobre vendas brutas em 2012</td>
  <td rowspan=4 class=xl6815980 style='border-top:none'>F1: LAJIR esperado</td>
  <td class=xl6915980 style='border-top:none;border-left:none'><font
  class="font515980">&#8805;</font><font class="font015980">15%</font></td>
  <td class=xl7215980 align=right style='border-top:none;border-left:none'>2009</td>
  <td rowspan=4 class=xl7015980 width=157 style='border-top:none;width:118pt'>Projeto
  de apuração do resultado operacional</td>
  </tr>
 <tr height=17 style='height:12.75pt'>
  <td height=68 class=xl6915980 style='height:12.75pt;border-top:none;
  border-left:none'>&#8805;18%</td>
  <td class=xl7215980 align=right style='border-top:none;border-left:none'>2010</td>
  </tr>
 <tr height=17 style='height:12.75pt'>
  <td height=68 class=xl6915980 style='height:12.75pt;border-top:none;
  border-left:none'>&#8805;22%<span style='mso-spacerun:yes'></span></td>
  <td class=xl7215980 align=right style='border-top:none;border-left:none'>2011</td>
  </tr>
 <tr height=17 style='height:12.75pt'>
  <td height=68 class=xl6915980 style='height:12.75pt;border-top:none;
  border-left:none'>&#8805;25%</td>
  <td class=xl7215980 align=right style='border-top:none;border-left:none'>2012</td>
  </tr>
 <tr height=17 style='height:12.75pt'>
  <td height=17 class=xl8715980 style='height:12.75pt;border-top:none'>Redução
  dos Custos</td>
  <td class=xl8815980 width=324 style='border-top:none;border-left:none;
  width:243pt'> </td>
  <td class=xl8915980 width=85 style='border-top:none;border-left:none;
  width:64pt'> </td>
  <td class=xl9015980 style='border-top:none;border-left:none'> </td>
  <td class=xl9115980 style='border-top:none;border-left:none'> </td>
  </tr>
 <tr height=17 style='height:12.75pt'>
  <td height=17 class=xl7215980 style='height:12.75pt;border-top:none'>F2:
  Redução de 20% dos custos totais</td>
  <td class=xl7215980 style='border-top:none;border-left:none'>Vide F3 e F4</td>
  <td class=xl7115980 width=85 style='border-top:none;border-left:none;
  width:64pt'> </td>
  <td class=xl7215980 style='border-top:none;border-left:none'> </td>
  <td class=xl6815980 style='border-top:none;border-left:none'> </td>
  </tr>
 <tr class=xl6315980 height=51 style='mso-height-source:userset;height:38.25pt'>
  <td rowspan=2 height=102 class=xl6815980 style='height:76.5pt;border-top:
  none'>F3: Redução de custo de mão-de-obra</td>
  <td class=xl7615980 width=324 style='border-top:none;border-left:none;
  width:243pt'>F3a:<span style='mso-spacerun:yes'> </span>% das horas de
  retrabalho / número de horas total</td>
  <td class=xl7115980 width=85 style='border-top:none;border-left:none;
  width:64pt'>&#8804; 2%<span style='mso-spacerun:yes'></span></td>
  <td class=xl10015980 align=right style='border-top:none;border-left:none'>30/10/2009</td>
  <td class=xl7015980 width=157 style='border-top:none;border-left:none;
  width:118pt'>Projeto de Criação do departamento de Planejamento e Controle</td>
  </tr>
 <tr class=xl6315980 height=51 style='mso-height-source:userset;height:38.25pt'>
  <td height=102 class=xl7615980 width=324 style='height:38.25pt;border-top:
  none;border-left:none;width:243pt'>F3b: Número de documentos produzidos (A1
  Equivalente)/ Número de Horas Totais gastas.</td>
  <td class=xl8615980 width=85 style='border-top:none;border-left:none;
  width:64pt'>Medir ultimos 3 meses</td>
  <td class=xl10015980 align=right style='border-top:none;border-left:none'>30/10/2009</td>
  <td class=xl7015980 width=157 style='border-top:none;border-left:none;
  width:118pt'>Projeto de Criação do departamento de Planejamento e Controle</td>
  </tr>
 <tr class=xl6315980 height=51 style='height:38.25pt'>
  <td height=51 class=xl7315980 style='height:38.25pt;border-top:none'>F4:
  Investimentos em TI e processos</td>
  <td class=xl7615980 width=324 style='border-top:none;border-left:none;
  width:243pt'>Vide F3b, I3</td>
  <td class=xl7115980 width=85 style='border-top:none;border-left:none;
  width:64pt'> </td>
  <td class=xl9615980 width=76 style='border-top:none;border-left:none;
  width:57pt'>dez/09</td>
  <td class=xl7015980 width=157 style='border-top:none;border-left:none;
  width:118pt'>Projeto de aumento de produtividade associado ao adequado uso de
  TI</td>
  </tr>
 <tr class=xl6315980 height=17 style='height:12.75pt'>
  <td height=17 class=xl9215980 style='height:12.75pt;border-top:none'>Aumento
  das Vendas</td>
  <td class=xl8815980 width=324 style='border-top:none;border-left:none;
  width:243pt'> </td>
  <td class=xl8915980 width=85 style='border-top:none;border-left:none;
  width:64pt'> </td>
  <td class=xl9315980 style='border-top:none;border-left:none'> </td>
  <td class=xl9115980 style='border-top:none;border-left:none'> </td>
  </tr>
 <tr class=xl6715980 height=51 style='mso-height-source:userset;height:38.25pt'>
  <td rowspan=3 height=102 class=xl6815980 style='height:76.5pt;border-top:
  none'>F6: Crescimento em novas áreas</td>
  <td rowspan=3 class=xl7015980 width=324 style='border-top:none;width:243pt'>F6:
  Faturamento por Segmento (em R$) / Faturamento Total da empresa (em R$)</td>
  <td class=xl10215980 width=85 style='border-top:none;border-left:none;
  width:64pt'>&#8804; 70%</td>
  <td class=xl11615980 style='border-top:none;border-left:none'>2009</td>
  <td rowspan=4 class=xl7015980 width=157 style='border-top:none;width:118pt'>Prospecção
  e fechamento de trabalhos com clientes novos</td>
  </tr>
 <tr class=xl6715980 height=17 style='height:12.75pt'>
  <td height=102 class=xl10215980 width=85 style='height:12.75pt;border-top:
  none;border-left:none;width:64pt'>&#8804; 55%</td>
  <td class=xl11615980 style='border-top:none;border-left:none'>2010</td>
  </tr>
 <tr class=xl6715980 height=34 style='height:25.5pt'>
  <td height=102 class=xl10215980 width=85 style='height:25.5pt;border-top:none;
  border-left:none;width:64pt'>&#8804; 40%</td>
  <td class=xl10215980 width=76 style='border-top:none;border-left:none;
  width:57pt'>2011 em diante</td>
  </tr>
 <tr class=xl6715980 height=34 style='height:25.5pt'>
  <td height=34 class=xl6815980 style='height:25.5pt;border-top:none'>F7:
  Ingresso de Novos Clientes</td>
  <td class=xl7015980 width=324 style='border-top:none;border-left:none;
  width:243pt'>F7: Faturamento anual realizado com Novos Clientes (em R$) /
  Faturamento anual Total Realizado<span style='mso-spacerun:yes'></span></td>
  <td class=xl10215980 width=85 style='border-top:none;border-left:none;
  width:64pt'><span style='mso-spacerun:yes'></span>&#8805; 20%</td>
  <td class=xl11615980 style='border-top:none;border-left:none'>2010</td>
  </tr>
 <tr height=25 style='height:18.75pt'>
  <td height=25 class=xl7915980 style='height:18.75pt;border-top:none'>Perspectiva
  do Cliente</td>
  <td class=xl8315980 width=324 style='border-top:none;border-left:none;
  width:243pt'> </td>
  <td class=xl8415980 width=85 style='border-top:none;border-left:none;
  width:64pt'> </td>
  <td class=xl8015980 style='border-top:none;border-left:none'> </td>
  <td class=xl8215980 style='border-top:none;border-left:none'> </td>
  </tr>
 <tr height=17 style='height:12.75pt'>
  <td height=17 class=xl9215980 style='height:12.75pt;border-top:none'>Atributos
  do serviço</td>
  <td class=xl8815980 width=324 style='border-top:none;border-left:none;
  width:243pt'> </td>
  <td class=xl8915980 width=85 style='border-top:none;border-left:none;
  width:64pt'> </td>
  <td class=xl9015980 style='border-top:none;border-left:none'> </td>
  <td class=xl9115980 style='border-top:none;border-left:none'> </td>
  </tr>
 <tr height=17 style='height:12.75pt'>
  <td height=17 class=xl7315980 style='height:12.75pt;border-top:none'>C1:
  Preço justo</td>
  <td class=xl7615980 width=324 style='border-top:none;border-left:none;
  width:243pt'>Vide I1</td>
  <td class=xl7115980 width=85 style='border-top:none;border-left:none;
  width:64pt'> </td>
  <td class=xl7215980 style='border-top:none;border-left:none'> </td>
  <td class=xl6815980 style='border-top:none;border-left:none'> </td>
  </tr>
 <tr height=17 style='height:12.75pt'>
  <td rowspan=3 height=51 class=xl6815980 style='height:38.25pt;border-top:
  none'>C2: Prazo garantido</td>
  <td rowspan=3 class=xl7015980 width=324 style='border-top:none;width:243pt'>C2:<span
  style='mso-spacerun:yes'> </span>% das obras dentro do prazo</td>
  <td class=xl7115980 width=85 style='border-top:none;border-left:none;
  width:64pt'><span style='mso-spacerun:yes'></span>&#8805; 95%</td>
  <td class=xl8515980 align=right style='border-top:none;border-left:none'>dez/09</td>
  <td rowspan=3 class=xl7015980 width=157 style='border-top:none;width:118pt'>Projeto
  de Criação do departamento de Planejamento e Controle</td>
  </tr>
 <tr height=17 style='height:12.75pt'>
  <td height=51 class=xl7115980 width=85 style='height:12.75pt;border-top:none;
  border-left:none;width:64pt'><span style='mso-spacerun:yes'></span>&#8805;
  98%</td>
  <td class=xl8515980 align=right style='border-top:none;border-left:none'>jun/10</td>
  </tr>
 <tr height=17 style='height:12.75pt'>
  <td height=51 class=xl7115980 width=85 style='height:12.75pt;border-top:none;
  border-left:none;width:64pt'><span style='mso-spacerun:yes'></span>&#8805;
  99%</td>
  <td class=xl8515980 align=right style='border-top:none;border-left:none'>dez/10</td>
  </tr>
 <tr height=51 style='height:38.25pt'>
  <td height=51 class=xl7315980 style='height:38.25pt;border-top:none'>C3:
  Venda de Soluções</td>
  <td class=xl7615980 width=324 style='border-top:none;border-left:none;
  width:243pt'>C3: Venda de Soluções (em R$) / Venda Total (R$)</td>
  <td class=xl10215980 width=85 style='border-top:none;border-left:none;
  width:64pt'><span style='mso-spacerun:yes'> </span>0,30 &#8805;<span
  style='mso-spacerun:yes'> </span>EPC &#8805; 0,15</td>
  <td class=xl9915980 style='border-top:none;border-left:none'>dez/10</td>
  <td class=xl7715980 width=157 style='border-top:none;border-left:none;
  width:118pt'>Prospecção e fechamento de trabalhos com clientes novos</td>
  </tr>
 <tr height=17 style='page-break-before:always;height:12.75pt'>
  <td height=17 class=xl9215980 style='height:12.75pt;border-top:none'>Relacionamento</td>
  <td class=xl8815980 width=324 style='border-top:none;border-left:none;
  width:243pt'> </td>
  <td class=xl10315980 width=85 style='border-top:none;border-left:none;
  width:64pt'> </td>
  <td class=xl10415980 style='border-top:none;border-left:none'> </td>
  <td class=xl9115980 style='border-top:none;border-left:none'> </td>
  </tr>
 <tr height=34 style='mso-height-source:userset;height:25.5pt'>
  <td rowspan=6 height=119 class=xl11915980 width=355 style='border-bottom:
  .5pt solid black;height:89.25pt;border-top:none;width:266pt'>C4:
  Fortalecimento dos relacionamentos existentes</td>
  <td rowspan=3 class=xl11915980 width=324 style='border-bottom:.5pt solid black;
  border-top:none;width:243pt'>C4a: % dos clientes A Visitadas no trimestre /
  Total de clientes A = Score desejado</td>
  <td class=xl10515980 width=85 style='border-top:none;border-left:none;
  width:64pt'>dez/09</td>
  <td class=xl10615980 style='border-top:none;border-left:none'>95%</td>
  <td class=xl6815980 style='border-top:none;border-left:none'> </td>
  </tr>
 <tr height=17 style='height:12.75pt'>
  <td height=119 class=xl10515980 width=85 style='height:12.75pt;border-top:
  none;border-left:none;width:64pt'>jun/10</td>
  <td class=xl10615980 style='border-top:none;border-left:none'>98%</td>
  <td class=xl6815980 style='border-top:none;border-left:none'> </td>
  </tr>
 <tr height=17 style='height:12.75pt'>
  <td height=119 class=xl10215980 width=85 style='height:12.75pt;border-top:
  none;border-left:none;width:64pt'>em diante</td>
  <td class=xl10615980 style='border-top:none;border-left:none'>99%</td>
  <td class=xl6815980 style='border-top:none;border-left:none'> </td>
  </tr>
 <tr height=17 style='height:12.75pt'>
  <td rowspan=3 height=119 class=xl11915980 width=324 style='border-bottom:.5pt solid black;
  height:38.25pt;border-top:none;width:243pt'>C4b: % dos clientes B Visitadas
  no trimestre / Total de clientes B = Score desejado</td>
  <td class=xl10515980 width=85 style='border-top:none;border-left:none;
  width:64pt'>dez/09</td>
  <td class=xl10615980 style='border-top:none;border-left:none'>90%</td>
  <td class=xl6815980 style='border-top:none;border-left:none'> </td>
  </tr>
 <tr height=17 style='height:12.75pt'>
  <td height=119 class=xl10515980 width=85 style='height:12.75pt;border-top:
  none;border-left:none;width:64pt'>jun/10</td>
  <td class=xl10615980 style='border-top:none;border-left:none'>93%</td>
  <td class=xl6815980 style='border-top:none;border-left:none'> </td>
  </tr>
 <tr height=17 style='height:12.75pt'>
  <td height=119 class=xl10215980 width=85 style='height:12.75pt;border-top:
  none;border-left:none;width:64pt'>em diante</td>
  <td class=xl10615980 style='border-top:none;border-left:none'>95%</td>
  <td class=xl6815980 style='border-top:none;border-left:none'> </td>
  </tr>
 <tr height=34 style='height:25.5pt'>
  <td height=34 class=xl7615980 width=355 style='height:25.5pt;border-top:none;
  width:266pt'>C5: Criação de novos canais de relacionamento</td>
  <td class=xl7615980 width=324 style='border-top:none;border-left:none;
  width:243pt'>Vide Projeto de Prospecção e fechamento de trabalhos com
  clientes novos</td>
  <td class=xl10215980 width=85 style='border-top:none;border-left:none;
  width:64pt'> </td>
  <td class=xl10715980 style='border-top:none;border-left:none'> </td>
  <td class=xl6815980 style='border-top:none;border-left:none'> </td>
  </tr>
 <tr height=17 style='height:12.75pt'>
  <td height=17 class=xl8715980 style='height:12.75pt;border-top:none'>Imagem</td>
  <td class=xl8815980 width=324 style='border-top:none;border-left:none;
  width:243pt'> </td>
  <td class=xl10315980 width=85 style='border-top:none;border-left:none;
  width:64pt'> </td>
  <td class=xl10415980 style='border-top:none;border-left:none'> </td>
  <td class=xl9115980 style='border-top:none;border-left:none'> </td>
  </tr>
 <tr height=85 style='height:63.75pt'>
  <td height=85 class=xl7315980 style='height:63.75pt;border-top:none'>C6:
  Percepção do valor oferecido</td>
  <td class=xl7615980 width=324 style='border-top:none;border-left:none;
  width:243pt'>C6: Numero de testemunhos documentados por trimestre</td>
  <td class=xl10215980 width=85 style='border-top:none;border-left:none;
  width:64pt'><span style='mso-spacerun:yes'></span>&#8805; 2</td>
  <td class=xl9915980 style='border-top:none;border-left:none'>jun/10</td>
  <td class=xl7015980 width=157 style='border-top:none;border-left:none;
  width:118pt'>Projeto de Construção e apresentação de acervo de testemunhos de
  clientes satisfeitos e demais atributos</td>
  </tr>
 <tr height=34 style='height:25.5pt'>
  <td rowspan=2 height=68 class=xl6815980 style='height:51.0pt;border-top:none'>C7:
  Fortalecimento da Marca</td>
  <td class=xl7615980 width=324 style='border-top:none;border-left:none;
  width:243pt'>C7a: Participação em feiras como expositor (anualmente)</td>
  <td class=xl10215980 width=85 style='border-top:none;border-left:none;
  width:64pt'>&#8805; 1</td>
  <td class=xl10715980 style='border-top:none;border-left:none'>2010</td>
  <td rowspan=2 class=xl7015980 width=157 style='border-top:none;width:118pt'>Projeto
  de fortalecimento da marca</td>
  </tr>
 <tr height=34 style='height:25.5pt'>
  <td height=68 class=xl7615980 width=324 style='height:25.5pt;border-top:none;
  border-left:none;width:243pt'>C7b: Participação em feiras como visitante
  (Trimestralmente)</td>
  <td class=xl10215980 width=85 style='border-top:none;border-left:none;
  width:64pt'><span style='mso-spacerun:yes'></span><font class="font515980">&#8805;</font><font
  class="font015980"> 3</font></td>
  <td class=xl10815980 width=76 style='border-top:none;border-left:none;
  width:57pt'>4º TRIM 2009</td>
  </tr>
 <tr height=25 style='height:18.75pt'>
  <td height=25 class=xl7915980 style='height:18.75pt;border-top:none'>Perspectiva
  Interna</td>
  <td class=xl8315980 width=324 style='border-top:none;border-left:none;
  width:243pt'> </td>
  <td class=xl10915980 width=85 style='border-top:none;border-left:none;
  width:64pt'> </td>
  <td class=xl11015980 style='border-top:none;border-left:none'> </td>
  <td class=xl8215980 style='border-top:none;border-left:none'> </td>
  </tr>
 <tr height=17 style='height:12.75pt'>
  <td height=17 class=xl9415980 width=355 style='height:12.75pt;border-top:
  none;width:266pt'>Processos de Gestão Operacional</td>
  <td class=xl8815980 width=324 style='border-top:none;border-left:none;
  width:243pt'> </td>
  <td class=xl10315980 width=85 style='border-top:none;border-left:none;
  width:64pt'> </td>
  <td class=xl10415980 style='border-top:none;border-left:none'> </td>
  <td class=xl9115980 style='border-top:none;border-left:none'> </td>
  </tr>
 <tr height=68 style='mso-height-source:userset;height:51.0pt'>
  <td rowspan=3 height=136 class=xl7015980 width=355 style='height:102.0pt;
  border-top:none;width:266pt'>I1: Gestão de processos com base no resultado</td>
  <td rowspan=3 class=xl6815980 style='border-top:none'>I1= % das obras dentro
  do custo planejado.<span style='mso-spacerun:yes'></span></td>
  <td class=xl10215980 width=85 style='border-top:none;border-left:none;
  width:64pt'>&#8805; 90%</td>
  <td class=xl9915980 style='border-top:none;border-left:none'>mar/10</td>
  <td rowspan=2 class=xl12215980 width=157 style='border-bottom:.5pt solid black;
  border-top:none;width:118pt'>Projeto para a criação do departamento de
  orçamentos e dos padrões orçamentários</td>
  </tr>
 <tr height=17 style='height:12.75pt'>
  <td height=136 class=xl10215980 width=85 style='height:12.75pt;border-top:
  none;border-left:none;width:64pt'>&#8805; 95%</td>
  <td class=xl9915980 style='border-top:none;border-left:none'>dez/10</td>
  </tr>
 <tr height=51 style='height:38.25pt'>
  <td height=136 class=xl11115980 width=85 style='height:38.25pt;border-top:
  none;border-left:none;width:64pt'>100%</td>
  <td class=xl9915980 style='border-top:none;border-left:none'>jun/11</td>
  <td class=xl7015980 width=157 style='border-top:none;border-left:none;
  width:118pt'>Projeto de Criação do departamento de Planejamento e Controle</td>
  </tr>
 <tr height=34 style='height:25.5pt'>
  <td height=34 class=xl7515980 width=355 style='height:25.5pt;border-top:none;
  width:266pt'>I2: Gestão de RH</td>
  <td class=xl7615980 width=324 style='border-top:none;border-left:none;
  width:243pt'> </td>
  <td class=xl10215980 width=85 style='border-top:none;border-left:none;
  width:64pt'> </td>
  <td class=xl10515980 width=76 style='border-top:none;border-left:none;
  width:57pt'>set/09</td>
  <td class=xl7615980 width=157 style='border-top:none;border-left:none;
  width:118pt'>Projeto de Implantação do Plano de Cargos e Salários</td>
  </tr>
 <tr height=68 style='height:51.0pt'>
  <td rowspan=2 height=119 class=xl11915980 width=355 style='border-bottom:
  .5pt solid black;height:89.25pt;border-top:none;width:266pt'>I3: Gestão de TI
  / Infra-estrutura</td>
  <td rowspan=2 class=xl13115980 style='border-bottom:.5pt solid black;
  border-top:none'> </td>
  <td rowspan=2 class=xl13315980 width=85 style='border-bottom:.5pt solid black;
  border-top:none;width:64pt'> </td>
  <td class=xl10515980 width=76 style='border-top:none;border-left:none;
  width:57pt'>dez/10</td>
  <td class=xl7615980 width=157 style='border-top:none;border-left:none;
  width:118pt'>Projeto para adequação da infra-estrutura física/TI para
  atendimento ao aumento da demanda</td>
  </tr>
 <tr height=51 style='height:38.25pt'>
  <td height=119 class=xl10515980 width=76 style='height:38.25pt;border-top:
  none;border-left:none;width:57pt'>dez/09</td>
  <td class=xl7015980 width=157 style='border-top:none;border-left:none;
  width:118pt'>Projeto de aumento de produtividade associado ao adequado uso de
  TI</td>
  </tr>
 <tr height=17 style='height:12.75pt'>
  <td height=17 class=xl7515980 width=355 style='height:12.75pt;border-top:
  none;width:266pt'>I4: Gestão da qualidade e normas técnicas</td>
  <td class=xl7615980 width=324 style='border-top:none;border-left:none;
  width:243pt'>Vide I11, I12 e I13</td>
  <td class=xl10215980 width=85 style='border-top:none;border-left:none;
  width:64pt'> </td>
  <td class=xl10715980 style='border-top:none;border-left:none'> </td>
  <td class=xl6815980 style='border-top:none;border-left:none'> </td>
  </tr>
 <tr height=17 style='height:12.75pt'>
  <td rowspan=2 height=34 class=xl7015980 width=355 style='height:25.5pt;
  border-top:none;width:266pt'>I5: Centralização</td>
  <td rowspan=2 class=xl7015980 width=324 style='border-top:none;width:243pt'>I5:
  Número de Sites Abertos</td>
  <td class=xl10215980 width=85 style='border-top:none;border-left:none;
  width:64pt'>1</td>
  <td class=xl10715980 style='border-top:none;border-left:none'>2011</td>
  <td rowspan=2 class=xl6815980 style='border-top:none'>Abertura de sites</td>
  </tr>
 <tr height=17 style='height:12.75pt'>
  <td height=34 class=xl10215980 width=85 style='height:12.75pt;border-top:
  none;border-left:none;width:64pt'>2</td>
  <td class=xl10715980 style='border-top:none;border-left:none'>2012</td>
  </tr>
 <tr height=17 style='page-break-before:always;height:12.75pt'>
  <td height=17 class=xl9415980 width=355 style='height:12.75pt;border-top:
  none;width:266pt'>Processos de Gestão de Clientes</td>
  <td class=xl8815980 width=324 style='border-top:none;border-left:none;
  width:243pt'> </td>
  <td class=xl10315980 width=85 style='border-top:none;border-left:none;
  width:64pt'> </td>
  <td class=xl10415980 style='border-top:none;border-left:none'> </td>
  <td class=xl9115980 style='border-top:none;border-left:none'> </td>
  </tr>
 <tr height=51 style='height:38.25pt'>
  <td height=51 class=xl7615980 width=355 style='height:38.25pt;border-top:
  none;width:266pt'>I7: Aumento do número de clientes</td>
  <td class=xl7615980 width=324 style='border-top:none;border-left:none;
  width:243pt'>I7: Número de Clientes Novos por Trimestre</td>
  <td class=xl10215980 width=85 style='border-top:none;border-left:none;
  width:64pt'>&#8805; 1</td>
  <td class=xl10815980 width=76 style='border-top:none;border-left:none;
  width:57pt'>a partir de 2009</td>
  <td class=xl7715980 width=157 style='border-top:none;border-left:none;
  width:118pt'>Prospecção e fechamento de trabalhos com clientes novos</td>
  </tr>
 <tr height=51 style='height:38.25pt'>
  <td rowspan=6 height=170 class=xl11915980 width=355 style='border-bottom:
  .5pt solid black;height:127.5pt;border-top:none;width:266pt'>I8: Aumento da
  receita de clientes de forma contínua</td>
  <td class=xl7615980 width=324 style='border-top:none;border-left:none;
  width:243pt'>I8a: valor Total Orçado no Trimestre (em R$</td>
  <td class=xl11215980 width=85 style='border-top:none;border-left:none;
  width:64pt'>Plano de Vendas Anual / (4 x 0,12)</td>
  <td class=xl9915980 style='border-top:none;border-left:none'>jun/09</td>
  <td rowspan=6 class=xl7015980 width=157 style='border-top:none;width:118pt'>Prospecção
  e fechamento de trabalhos com clientes novos</td>
  </tr>
 <tr height=17 style='height:12.75pt'>
  <td rowspan=4 height=170 class=xl7015980 width=324 style='height:51.0pt;
  border-top:none;width:243pt'>I8b: valor de Orçamentos Fechados (em R$) /
  valor de Orçamentos Realizados (em R$)</td>
  <td class=xl11315980 width=85 style='border-top:none;border-left:none;
  width:64pt'><span style='mso-spacerun:yes'> </span>0,15 </td>
  <td class=xl10715980 style='border-top:none;border-left:none'>2009</td>
  </tr>
 <tr height=17 style='height:12.75pt'>
  <td height=170 class=xl11315980 width=85 style='height:12.75pt;border-top:
  none;border-left:none;width:64pt'><span
  style='mso-spacerun:yes'> </span>0,20 </td>
  <td class=xl10715980 style='border-top:none;border-left:none'>1º Sem. 2010</td>
  </tr>
 <tr height=17 style='height:12.75pt'>
  <td height=170 class=xl11315980 width=85 style='height:12.75pt;border-top:
  none;border-left:none;width:64pt'><span
  style='mso-spacerun:yes'></span>0,25 </td>
  <td class=xl10715980 style='border-top:none;border-left:none'>2º Sem. 2010</td>
  </tr>
 <tr height=17 style='height:12.75pt'>
  <td height=170 class=xl11315980 width=85 style='height:12.75pt;border-top:
  none;border-left:none;width:64pt'><span
  style='mso-spacerun:yes'> </span>0,30 </td>
  <td class=xl10715980 style='border-top:none;border-left:none'>1º Sem. 2011</td>
  </tr>
 <tr height=51 style='height:38.25pt'>
  <td height=170 class=xl7015980 width=324 style='height:38.25pt;border-top:
  none;border-left:none;width:243pt'>I8c: (Total de solicitacao_documentos Fechados em
  Carteira (em R$) / Total do Planejamento de Faturamento Anual (em R$)) X 12</td>
  <td class=xl11315980 width=85 style='border-top:none;border-left:none;
  width:64pt'><span style='mso-spacerun:yes'> </span>&#8805; 6<span
  style='mso-spacerun:yes'></span></td>
  <td class=xl9915980 style='border-top:none;border-left:none'>dez/09</td>
  </tr>
 <tr height=17 style='height:12.75pt'>
  <td height=17 class=xl7515980 width=355 style='height:12.75pt;border-top:
  none;width:266pt'>I9: Comunicação de valor para o cliente</td>
  <td class=xl7615980 width=324 style='border-top:none;border-left:none;
  width:243pt'>Vide C6 e C7</td>
  <td class=xl10215980 width=85 style='border-top:none;border-left:none;
  width:64pt'> </td>
  <td class=xl10715980 style='border-top:none;border-left:none'> </td>
  <td class=xl6815980 style='border-top:none;border-left:none'> </td>
  </tr>
 <tr height=51 style='height:38.25pt'>
  <td height=51 class=xl7515980 width=355 style='height:38.25pt;border-top:
  none;width:266pt'>I10: Aumento da rentabilidade de clientes</td>
  <td class=xl7615980 width=324 style='border-top:none;border-left:none;
  width:243pt'>I10: Margem Bruta por Cliente (em R$) / Faturamento Bruto por
  Cliente (em R$)</td>
  <td class=xl10215980 width=85 style='border-top:none;border-left:none;
  width:64pt'>&#8805; 40%</td>
  <td class=xl9915980 style='border-top:none;border-left:none'>mar/10</td>
  <td class=xl7715980 width=157 style='border-top:none;border-left:none;
  width:118pt'>Prospecção e fechamento de trabalhos com clientes novos</td>
  </tr>
 <tr height=17 style='height:12.75pt'>
  <td height=17 class=xl9415980 width=355 style='height:12.75pt;border-top:
  none;width:266pt'>Processos regulatórios e sociais</td>
  <td class=xl8815980 width=324 style='border-top:none;border-left:none;
  width:243pt'> </td>
  <td class=xl10315980 width=85 style='border-top:none;border-left:none;
  width:64pt'> </td>
  <td class=xl10415980 style='border-top:none;border-left:none'> </td>
  <td class=xl9115980 style='border-top:none;border-left:none'> </td>
  </tr>
 <tr height=17 style='height:12.75pt'>
  <td height=17 class=xl7515980 width=355 style='height:12.75pt;border-top:
  none;width:266pt'>I11: OHSAS 18.001</td>
  <td class=xl7615980 width=324 style='border-top:none;border-left:none;
  width:243pt'> </td>
  <td class=xl10215980 width=85 style='border-top:none;border-left:none;
  width:64pt'> </td>
  <td class=xl11415980 style='border-top:none;border-left:none'> </td>
  <td rowspan=3 class=xl11915980 width=157 style='border-bottom:.5pt solid black;
  border-top:none;width:118pt'>Implantação do sistema de gestão de normas
  técnicas</td>
  </tr>
 <tr height=17 style='height:12.75pt'>
  <td height=17 class=xl7515980 width=355 style='height:12.75pt;border-top:
  none;width:266pt'>I12: ISO 14.001</td>
  <td class=xl7615980 width=324 style='border-top:none;border-left:none;
  width:243pt'> </td>
  <td class=xl10215980 width=85 style='border-top:none;border-left:none;
  width:64pt'> </td>
  <td class=xl11415980 style='border-top:none;border-left:none'> </td>
  </tr>
 <tr height=17 style='height:12.75pt'>
  <td height=17 class=xl7515980 width=355 style='height:12.75pt;border-top:
  none;width:266pt'>I13: ISO 27001</td>
  <td class=xl7615980 width=324 style='border-top:none;border-left:none;
  width:243pt'>Vide I4</td>
  <td class=xl10215980 width=85 style='border-top:none;border-left:none;
  width:64pt'> </td>
  <td class=xl11415980 style='border-top:none;border-left:none'> </td>
  </tr>
 <tr height=34 style='height:25.5pt'>
  <td height=34 class=xl7515980 width=355 style='height:25.5pt;border-top:none;
  width:266pt'>I14: Implementação e acompanhamento de plano de ações de
  responsabilidade social.</td>
  <td class=xl7615980 width=324 style='border-top:none;border-left:none;
  width:243pt'> </td>
  <td class=xl10215980 width=85 style='border-top:none;border-left:none;
  width:64pt'> </td>
  <td class=xl11415980 style='border-top:none;border-left:none'> </td>
  <td class=xl7015980 width=157 style='border-top:none;border-left:none;
  width:118pt'>Projeto de ações de responsabilidade social</td>
  </tr>
 <tr height=45 style='height:33.75pt'>
  <td height=45 class=xl7515980 width=355 style='height:33.75pt;border-top:
  none;width:266pt'>I15: Desenvolvimento dos projetos de formação de cultura de
  liderança e inovação.<span style='mso-spacerun:yes'></span></td>
  <td class=xl7615980 width=324 style='border-top:none;border-left:none;
  width:243pt'> </td>
  <td class=xl10215980 width=85 style='border-top:none;border-left:none;
  width:64pt'> </td>
  <td class=xl11415980 style='border-top:none;border-left:none'> </td>
  <td class=xl10115980 width=157 style='width:118pt'>Projetos de formação de
  cultura de liderança e inovação.<span style='mso-spacerun:yes'></span></td>
  </tr>
 <tr height=25 style='height:18.75pt'>
  <td height=25 class=xl7915980 style='height:18.75pt;border-top:none'>Perspectiva
  de Aprendizado e Crescimento</td>
  <td class=xl8315980 width=324 style='border-top:none;border-left:none;
  width:243pt'> </td>
  <td class=xl10915980 width=85 style='border-top:none;border-left:none;
  width:64pt'> </td>
  <td class=xl11015980 style='border-top:none;border-left:none'> </td>
  <td class=xl8215980 style='border-left:none'> </td>
  </tr>
 <tr height=17 style='height:12.75pt'>
  <td height=17 class=xl9415980 width=355 style='height:12.75pt;border-top:
  none;width:266pt'>Capital Humano</td>
  <td class=xl8815980 width=324 style='border-top:none;border-left:none;
  width:243pt'> </td>
  <td class=xl10315980 width=85 style='border-top:none;border-left:none;
  width:64pt'> </td>
  <td class=xl10415980 style='border-top:none;border-left:none'> </td>
  <td class=xl9115980 style='border-top:none;border-left:none'> </td>
  </tr>
 <tr height=17 style='height:12.75pt'>
  <td rowspan=6 height=170 class=xl7015980 width=355 style='height:127.5pt;
  border-top:none;width:266pt'>A1: Capacitação e treinamento</td>
  <td class=xl7615980 width=324 style='border-top:none;border-left:none;
  width:243pt'>A1a: Diretores com MBA / Total de Diretores</td>
  <td class=xl11115980 width=85 style='border-top:none;border-left:none;
  width:64pt'>100%</td>
  <td class=xl11515980 style='border-top:none;border-left:none'>dez/11</td>
  <td rowspan=6 class=xl6815980 style='border-top:none'>Projeto Treinamento</td>
  </tr>
 <tr height=34 style='height:25.5pt'>
  <td height=170 class=xl7515980 width=324 style='height:25.5pt;border-top:none;
  border-left:none;width:243pt'>A1b: Gerentes e Coordenadores com Pós-Graduação
  em Gestão / (Gerentes e Coordenadores Total)</td>
  <td class=xl11615980 style='border-top:none;border-left:none'>&#8805; %</td>
  <td class=xl11515980 style='border-top:none;border-left:none'>jun/13</td>
  </tr>
 <tr height=68 style='height:51.0pt'>
  <td height=170 class=xl7615980 width=324 style='height:51.0pt;border-top:none;
  border-left:none;width:243pt'>A1c: Nº de horas de treinamento por trimestre /
  Nº de horas totais trabalhadas por trimestre</td>
  <td class=xl11215980 width=85 style='border-top:none;border-left:none;
  width:64pt'>Depende da grade de cursos. A definir</td>
  <td class=xl9915980 style='border-top:none;border-left:none'>set/09</td>
  </tr>
 <tr height=17 style='height:12.75pt'>
  <td height=170 class=xl7615980 width=324 style='height:12.75pt;border-top:
  none;border-left:none;width:243pt'>A1d:<span style='mso-spacerun:yes'>
  </span>Coordenadores e gerentes com fluência em inglês</td>
  <td class=xl10215980 width=85 style='border-top:none;border-left:none;
  width:64pt'><span style='mso-spacerun:yes'></span>&#8805; %</td>
  <td class=xl11515980 style='border-top:none;border-left:none'>dez/11</td>
  </tr>
 <tr height=17 style='height:12.75pt'>
  <td height=170 class=xl7615980 width=324 style='height:12.75pt;border-top:
  none;border-left:none;width:243pt'>A1e:<span style='mso-spacerun:yes'>
  </span>Coordenadores e gerentes com leitura em inglês</td>
  <td class=xl11715980 style='border-top:none;border-left:none'>%</td>
  <td class=xl11515980 style='border-top:none;border-left:none'>dez/11</td>
  </tr>
 <tr height=17 style='height:12.75pt'>
  <td height=170 class=xl7615980 width=324 style='height:12.75pt;border-top:
  none;border-left:none;width:243pt'>A1f:<span style='mso-spacerun:yes'>
  </span>% de diretores com fluência em inglês</td>
  <td class=xl11715980 style='border-top:none;border-left:none'>&#8805; %</td>
  <td class=xl11515980 style='border-top:none;border-left:none'>dez/12</td>
  </tr>
 <tr height=34 style='height:25.5pt'>
  <td rowspan=4 height=102 class=xl7015980 width=355 style='height:76.5pt;
  border-top:none;width:266pt'>A2: Captação e retenção de mão-de-obra</td>
  <td class=xl7615980 width=324 style='border-top:none;border-left:none;
  width:243pt'>A2a: Nº de vagas em aberto / total de funcionários trabalhando</td>
  <td class=xl11815980 style='border-top:none;border-left:none'> </td>
  <td class=xl11515980 style='border-top:none;border-left:none'>dez/09</td>
  <td rowspan=4 class=xl7015980 width=157 style='border-top:none;width:118pt'>Projeto
  de Captação e Retenção de Mão de Obra</td>
  </tr>
 <tr height=17 style='height:12.75pt'>
  <td height=102 class=xl7615980 width=324 style='height:12.75pt;border-top:
  none;border-left:none;width:243pt'>A2b: Nº de trainee / total de funcionários
  trabalhando</td>
  <td class=xl11715980 style='border-top:none;border-left:none'><span
  style='mso-spacerun:yes'></span>&#8805; %</td>
  <td class=xl11515980 style='border-top:none;border-left:none'>ago/10</td>
  </tr>
 <tr height=34 style='height:25.5pt'>
  <td height=102 class=xl7615980 width=324 style='height:25.5pt;border-top:none;
  border-left:none;width:243pt'>A2c: Nº de estagiários / total de funcionários
  trabalhando</td>
  <td class=xl11715980 style='border-top:none;border-left:none'><span
  style='mso-spacerun:yes'></span>&#8805; %</td>
  <td class=xl9915980 style='border-top:none;border-left:none'>ago/10</td>
  </tr>
 <tr height=17 style='height:12.75pt'>
  <td height=102 class=xl7615980 width=324 style='height:12.75pt;border-top:
  none;border-left:none;width:243pt'>A2d: Índice de turn-over</td>
  <td class=xl11815980 style='border-top:none;border-left:none'> </td>
  <td class=xl11515980 style='border-top:none;border-left:none'>dez/09</td>
  </tr>
 <tr height=17 style='page-break-before:always;height:12.75pt'>
  <td height=17 class=xl9415980 width=355 style='height:12.75pt;border-top:
  none;width:266pt'>Capital da Informação</td>
  <td class=xl8815980 width=324 style='border-top:none;border-left:none;
  width:243pt'> </td>
  <td class=xl10315980 width=85 style='border-top:none;border-left:none;
  width:64pt'> </td>
  <td class=xl10415980 style='border-top:none;border-left:none'> </td>
  <td class=xl9115980 style='border-top:none;border-left:none'> </td>
  </tr>
 <tr height=34 style='height:25.5pt'>
  <td rowspan=2 height=68 class=xl12515980 width=355 style='border-bottom:.5pt solid black;
  height:51.0pt;border-top:none;width:266pt'>A3: Implantação de softwares</td>
  <td rowspan=2 class=xl11915980 width=324 style='border-bottom:.5pt solid black;
  border-top:none;width:243pt'> </td>
  <td rowspan=2 class=xl12715980 style='border-bottom:.5pt solid black;
  border-top:none'> </td>
  <td rowspan=2 class=xl12915980 style='border-bottom:.5pt solid black;
  border-top:none'>dez/09</td>
  <td class=xl7015980 width=157 style='border-top:none;border-left:none;
  width:118pt'>Projeto de implantação do ERP</td>
  </tr>
 <tr height=34 style='height:25.5pt'>
  <td height=68 class=xl7015980 width=157 style='height:25.5pt;border-top:none;
  border-left:none;width:118pt'>Projeto de implantação do GED</td>
  </tr>
 <tr height=17 style='height:12.75pt'>
  <td height=17 class=xl7515980 width=355 style='height:12.75pt;border-top:
  none;width:266pt'>A4: Aquisição de hardware e infra-estrutura de TI</td>
  <td class=xl7615980 width=324 style='border-top:none;border-left:none;
  width:243pt'>Vide I4</td>
  <td class=xl10215980 width=85 style='border-top:none;border-left:none;
  width:64pt'> </td>
  <td class=xl10715980 style='border-top:none;border-left:none'> </td>
  <td class=xl6815980 style='border-top:none;border-left:none'> </td>
  </tr>
 <tr height=85 style='height:63.75pt'>
  <td height=85 class=xl7615980 width=355 style='height:63.75pt;border-top:
  none;width:266pt'>A5: Estruturação de base de dados</td>
  <td class=xl7615980 width=324 style='border-top:none;border-left:none;
  width:243pt'> </td>
  <td class=xl11215980 width=85 style='border-top:none;border-left:none;
  width:64pt'> </td>
  <td class=xl11415980 style='border-top:none;border-left:none'> </td>
  <td class=xl7015980 width=157 style='border-top:none;border-left:none;
  width:118pt'>Projeto de Adequação das bases de dados e políticas de
  tratamento da informação ( Ligado a ISO 27001)</td>
  </tr>
 <tr height=17 style='height:12.75pt'>
  <td height=17 class=xl9415980 width=355 style='height:12.75pt;border-top:
  none;width:266pt'>Capital Organizacional</td>
  <td class=xl8815980 width=324 style='border-top:none;border-left:none;
  width:243pt'> </td>
  <td class=xl10315980 width=85 style='border-top:none;border-left:none;
  width:64pt'> </td>
  <td class=xl10415980 style='border-top:none;border-left:none'> </td>
  <td class=xl9115980 style='border-top:none;border-left:none'> </td>
  </tr>
 <tr height=56 style='mso-height-source:userset;height:42.0pt'>
  <td rowspan=4 height=203 class=xl12215980 width=355 style='border-bottom:
  .5pt solid black;height:152.25pt;border-top:none;width:266pt'>A6: Cultura de
  resultados, liderança e inovação</td>
  <td class=xl7615980 width=324 style='border-top:none;border-left:none;
  width:243pt'>A6a: Funcionários que recebem participação / Total de
  funcionários</td>
  <td class=xl11115980 width=85 style='border-top:none;border-left:none;
  width:64pt'>%</td>
  <td class=xl11615980 style='border-top:none;border-left:none'>1º Sem. 2010</td>
  <td rowspan=2 class=xl7015980 width=157 style='border-top:none;width:118pt'>Projeto
  para estabelecer e implantar critérios de medição do resultado por projeto e
  do desempenho individual de cada funcionário</td>
  </tr>
 <tr height=49 style='mso-height-source:userset;height:36.75pt'>
  <td height=203 class=xl7615980 width=324 style='height:36.75pt;border-top:
  none;border-left:none;width:243pt'>A6b: Prestadores de Serviço que recebem
  incentivos por produtividade / Total de Prestadores de Serviço</td>
  <td class=xl11715980 style='border-top:none;border-left:none'><span
  style='mso-spacerun:yes'></span>&#8805; %</td>
  <td class=xl11615980 style='border-top:none;border-left:none'>1º Sem. 2010</td>
  </tr>
 <tr height=49 style='mso-height-source:userset;height:36.75pt'>
  <td height=203 class=xl7615980 width=324 style='height:36.75pt;border-top:
  none;border-left:none;width:243pt'> </td>
  <td class=xl11715980 style='border-top:none;border-left:none'> </td>
  <td class=xl11615980 style='border-top:none;border-left:none'> </td>
  <td class=xl7015980 width=157 style='border-top:none;border-left:none;
  width:118pt'>Projeto para estabelecer cultura de liderança</td>
  </tr>
 <tr height=49 style='mso-height-source:userset;height:36.75pt'>
  <td height=203 class=xl7615980 width=324 style='height:36.75pt;border-top:
  none;border-left:none;width:243pt'> </td>
  <td class=xl11715980 style='border-top:none;border-left:none'> </td>
  <td class=xl11615980 style='border-top:none;border-left:none'> </td>
  <td class=xl7015980 width=157 style='border-top:none;border-left:none;
  width:118pt'>Projeto para estabelecer cultura de Inovação</td>
  </tr>
 <tr height=17 style='height:12.75pt'>
  <td height=17 class=xl7515980 width=355 style='height:12.75pt;border-top:
  none;width:266pt'>A7: Cultura de valorização dos colaboradores</td>
  <td class=xl7615980 width=324 style='border-top:none;border-left:none;
  width:243pt'>Vide I2</td>
  <td class=xl10215980 width=85 style='border-top:none;border-left:none;
  width:64pt'> </td>
  <td class=xl10715980 style='border-top:none;border-left:none'> </td>
  <td class=xl6815980 style='border-top:none;border-left:none'> </td>
  </tr>
 <tr height=17 style='height:12.75pt'>
  <td height=17 class=xl7515980 width=355 style='height:12.75pt;border-top:
  none;width:266pt'>A8: Cultura de responsabilidade social</td>
  <td class=xl7615980 width=324 style='border-top:none;border-left:none;
  width:243pt'>Vide I14</td>
  <td class=xl10215980 width=85 style='border-top:none;border-left:none;
  width:64pt'> </td>
  <td class=xl10715980 style='border-top:none;border-left:none'> </td>
  <td class=xl6815980 style='border-top:none;border-left:none'> </td>
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


