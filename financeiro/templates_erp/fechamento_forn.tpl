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

<body onBlur='window.clipboardData.setData("Text", " ");'>

<link rel="stylesheet" href="<smarty>$classe</smarty>">

<div onselectstart="return false" unselectable="on" style="-moz-user-select:none;">
<smarty>if isset($mensagem_bloqueio)</smarty>
	<h2 style="color:red;"><smarty>$mensagem_bloqueio</smarty></h2>
<smarty>/if</smarty>

<form name="frm" id="frm" action="<smarty>$smarty.server.PHP_SELF</smarty>" style="margin:0px; padding:0px;">
    
    <table width="100%" border="0" cellspacing="0" cellpadding="0">        
        <tr>
          <td colspan="2" ><smarty>$func</smarty></td>
        </tr>
        <tr>
          <td width="50%" valign="top"><smarty>$dados</smarty></td>
          <td width="50%"  valign="top"><smarty>$nf</smarty></td>
        </tr>
        <tr>
          <td colspan="2" valign="top" align="right"><smarty>$documentos</smarty></td>
        </tr>
        <tr>
          <td colspan="2" valign="top"><input class="class_botao" type="button" name="button" id="button" value="Fechar" onclick="window.close();"></td>
        </tr>
      </table>
      
<input type="hidden" name="id_funcionario" value="<smarty>$id_funcionario</smarty>">      
</form>
</div>
</body>
