<?php
/*
		Formulário de Fechamento MODELO	
		
		Criado por Carlos Abreu  
		
		local/Nome do arquivo:
		../financeiro/fechamento_modelo.php
		
		Versão 0 --> VERSÃO INICIAL : 01/07/2013
		
*/

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge"/>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="cache-control" content="max-age=0">
<meta http-equiv="cache-control" content="no-cache, must-revalidate">
<meta http-equiv="Expires" content="0">

<style>

@media print {body {display:none;}}

div {
	font-family:arial;
	font-size:10px;
	
}

.nom
{
	width:100%; 
	float: left; 
	border-width:1px; 
	border-color:#CCC; 
	border-style:solid; 
	text-align:center; 
	font-weight:bolder; 
	font-size:14px; 
	margin-bottom:10px;
	margin-top:10px;
	margin-right:5px;

}

.tit
{
	/* Classe para os titulos */
	background-color:#034467; 
	color:#FfFfFf; 
	border-width:1px; 
	border-color:#ABF; 
	border-style:solid; 
	margin-top:10px; 
	margin-right:5px;
	text-align:center;
	font-weight:bolder;	
}

.label1
{
  /* Classe para os titulos dos campos */	
  width: 150px;  
  float: left; 
  border-width:1px; 
  border-color:#ABF; 
  border-style:solid; 
  margin-top:1px;
  padding-right:3px; 
  text-align:right; 
  font-weight:bold;	
}

.label2
{
	/* Classe para as descrições dos campos */		
	clear:right;
	width: 100%; 
	border-width:1px; 
	border-color:#ABF; 
	border-style:solid; 
	margin-top: 1px; 
	margin-right: 5px;

}

.label3
{
	/* Classe para descrição das inf adic.  */
	width: 150px;
	float: left;
	border-width:1px;
	border-color:#ABF;
	border-style:solid;
	margin-top: 1px;
	padding-right:3px;
	text-align:right;	
}

.label4
{
	/* Classe para descrição das inf adic.  */
	clear:right;
	border-width:1px;
	border-color:#ABF;
	border-style:solid;
	margin-top: 1px;
	margin-right: 5px;
}

.label5
{
	width: 50%;
	float: left;
	border-width:1px;
	border-color:#ABF; 
	border-style:solid; 
	margin-top: 1px; 
	padding-right:3px; 
	text-align:right; 
	font-weight:bold;	
}

.label6
{
	/* Classe para descrição das inf adic.  */
	width: 150px;
	float: left;
	border-width:1px;
	border-color:#ABF;
	border-style:solid;
	margin-top: 1px;
	padding-right:3px;
	text-align:right;	
}


</style>


</head>

<script language="javascript">

function mostra_dadosfat(div)
{

	if(!document.getElementById('div_dadosfat'))
	{	
		var obj_dadosfat = document.createElement('div');
		
		//Define as propriedades do <DIV> do objeto obj_dadosfat
		obj_dadosfat.id = 'div_dadosfat';
		obj_dadosfat.innerHTML = '<BR><p>EMPRESA<BR><BR><BR> <BR>';
		obj_dadosfat.style.background = "#EEEEEE";
		obj_dadosfat.style.position = "absolute";
		obj_dadosfat.style.left = '-50px';
		obj_dadosfat.style.top = '16px';
	
		obj_dadosfat.style.borderRightWidth = '1px';
		obj_dadosfat.style.borderRightStyle = 'solid';
		obj_dadosfat.style.borderRightColor = '#999999';
	
		obj_dadosfat.style.borderBottomWidth = '1px';
		obj_dadosfat.style.borderBottomStyle = 'solid';
		obj_dadosfat.style.borderBottomColor = '#999999';
	
		obj_dadosfat.style.borderLeftWidth = '1px';
		obj_dadosfat.style.borderLeftStyle = 'solid';
		obj_dadosfat.style.borderLeftColor = '#FFFFFF';
		
		obj_dadosfat.style.borderTopWidth = '1px';
		obj_dadosfat.style.borderTopStyle = 'solid';
		obj_dadosfat.style.borderTopColor = '#FFFFFF';	
	
		obj_dadosfat.style.width = '230px';
	
		div.appendChild(obj_dadosfat);		
		
		div.style.borderStyle='inset';
	
	
	}
	else
	{
		div.removeChild(document.getElementById('div_dadosfat'));
		div.style.borderStyle='outset';

	}

}

</script>


<?
	
$header = "<div style='z-index:2; width: 100%; background: #FFF;margin: 0 auto; overflow: no;'>";
$footer = "</div>";


//DADOS DO FUNCIONÁRIO
$conteudo_f = "<div class='nom'>";
$conteudo_f .= "Fulano de Tal";
$conteudo_f .= "</div>";

//DADOS DA MEDIÇÃO
//periodo
$conteudo_d .= "<div class='label1'>Período:</div>";
$conteudo_d .= "<div class='label2'>";
$conteudo_d .= "26/01/2020 á 25/02/2020";
$conteudo_d .= "</div>";

//Função
$conteudo_d .= "<div class='label1'>Função:</div>";
$conteudo_d .= "<div class='label2'>";
$conteudo_d .= "XXXXXXXXXXXX";
$conteudo_d .= "</div>";

//tipo Contrato
$conteudo_d .= "<div class='label1'>Contrato:</div>";
$conteudo_d .= "<div class='label2'>";
$conteudo_d .= "XX";
$conteudo_d .= "</div>";

//valor Hora (Contrato SC)

$conteudo_d .= "<div class='label1'>Valor Hora:</div>";
$conteudo_d .= "<div class='label2'>R$ ";
$conteudo_d .= "0,00";
$conteudo_d .= "</div>";

//Horas Normais
$conteudo_d .= "<div class='label1'>Horas Normais:</div>";
$conteudo_d .= "<div class='label2'>";

$conteudo_d .= "0:00";
$conteudo_d .= "</div>";	


//Horas Adicionais
$conteudo_d .= "<div class='label1'>Horas Adicionais:</div>";
$conteudo_d .= "<div class='label2'>";	
$conteudo_d .= "0:00";
$conteudo_d .= "</div>";
	
//Medição
$conteudo_d .= "<div class='label1'>Medição:</div>";
$conteudo_d .= "<div class='label2'>R$ ";
$conteudo_d .= "0,00";
$conteudo_d .= "</div>";	

//DADOS DA NOTA FISCAL
$conteudo_n .= "<div class='tit' style='margin-top: 1px;'>NOTA FISCAL</div>";

//valor da nota
$conteudo_n .= "<div class='label5'>valor Nota Fiscal:</div>";
$conteudo_n .= "<div class='label2'>R$ ";
$conteudo_n .= "0,00";
$conteudo_n .= "</div>";

//data emissão  
$conteudo_n .= "<div class='label5'>Data de Emissão:</div>";
$conteudo_n .= "<div class='label2'>(Data de emissão: de hoje até 28/02/2020)</div>";

//Botão Dados  
$conteudo_n .= "<div id='div'  style='position:relative; background-color:#CCC; border-style:outset; border-width:1px; text-align:center; cursor:hand;  margin-top: 1px; margin-bottom: 10px;' onclick='mostra_dadosfat(this);'>DADOS P/ FATURAMENTO</div>";


//DADOS DE IMPOSTOS
$conteudo_n .= "<div class='tit'>IMPOSTOS</div>";

//IR
$conteudo_n .= "<div class='label5'>IR(1,5%):</div>";
$conteudo_n .= "<div class='label2'>R$ ";
$conteudo_n .= "0,00";
$conteudo_n .= "</div>";

$conteudo_n .= "<div> </div>";
$conteudo_n .= "<div> </div>";


//PIS
$conteudo_n .= "<div class='label5'>PIS(0,65%):</div>";
$conteudo_n .= "<div class='label2'>R$ ";
$conteudo_n .= "0,00";	
$conteudo_n .= "</div>";

//COFINS
$conteudo_n .= "<div class='label5'>COFINS(3%):</div>";
$conteudo_n .= "<div class='label2'>R$ ";
$conteudo_n .= "0,00";
$conteudo_n .= "</div>";

//CSLL
$conteudo_n .= "<div class='label5'>CSLL(1%):</div>";
$conteudo_n .= "<div class='label2'>R$ ";
$conteudo_n .= "0,00";
$conteudo_n .= "</div>";

//TOTAL IMPOSTOS
$conteudo_n .= "<div class='label5'>TOTAL (PIS/COFINS/CSLL)(4,65%):</div>";
$conteudo_n .= "<div class='label2'>R$ ";
$conteudo_n .= "0,00";
$conteudo_n .= "</div>";
	

//INF ADICIONAIS
//Mostra os detalhes de outros descontos.
$conteudo_n .= "<div class='tit'>OUTROS DESCONTOS</div>";
$conteudo_n .= "<div class='label5'>Adiantamento</div>"; 
$conteudo_n .= "<div class='label2'>0,00</div>";		


//Mostra os detalhes de acrescimos
$conteudo_n .= "<div class='tit'>OUTROS ACRÉSCIMOS</div>";	
$conteudo_n .= "<div class='label5'>Mês anterior</div>";
$conteudo_n .= "<div class='label2'>0,00</div>";

$conteudo_n .= "<div class='tit'>INFORMAÇÕES ADICIONAIS</div>";	
$conteudo_n .= "<div class='label2'>Fechamento parcial</div>";

?>

<body>

<link href="<?php CSS_FILE ?>" rel="stylesheet" type="text/css" />
<div>
<form name="frm" id="frm" style="margin:0px; padding:0px;">    
    <table width="100%" border="0" cellspacing="0" cellpadding="0">        
        <tr>
          <td colspan="2" ><?php echo $header.$conteudo_f.$footer ?></td>
        </tr>
        <tr>
          <td width="50%" valign="top"><?php echo $header.$conteudo_d.$footer ?></td>
          <td width="50%"  valign="top"><?php echo $header.$conteudo_n.$footer ?></td>
        </tr>
        <tr>
          <td colspan="2" valign="top" align="right"> </td>
        </tr>
        <tr>
          <td colspan="2" valign="top"><input class="class_botao" type="button" name="button" id="button" value="Fechar" onclick="window.close();"></td>
        </tr>
      </table>
          
</form>
</div>
</body>
</html>

