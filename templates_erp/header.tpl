<!-- -->
<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge"/>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="cache-control" content="max-age=0">
<meta http-equiv="cache-control" content="no-cache, must-revalidate">
<meta http-equiv="Expires" content="0">
	<smarty>$xajax_javascript</smarty>

<title>::.. Empresa X - ERP  - <smarty>$campo[1]</smarty> - <smarty>$versao</smarty>  ..::</title>

<link href="<smarty>$classe</smarty>" rel="stylesheet" type="text/css">

<link rel="stylesheet" type="text/css" href="../includes/dhtmlx_403/codebase/dhtmlx.css">

<style>

.standartTreeRow{

font-size:9px;
font-family:Arial, Helvetica, sans-serif;

}

.selectedTreeRow{
font-size:9px;
font-family:Arial, Helvetica, sans-serif;
}

div.gridbox table.hdr td {
font-family:arial;
font-size:12px;
font-weight:bold;
color:#03C;
}

div.gridbox table.obj td {
font-family:Arial;
font-size:11px;
}

</style> 

<script src="../includes/utils.js"></script>

</head>

<body onload="<smarty>$body_onload</smarty>">

<div align="center" style="width:100%;">
<smarty>if !$ocultarCabecalhoRodape</smarty>
	<div style="width:1020px;">
<smarty>else</smarty>
	<div style="width:100%;">
<smarty>/if</smarty>
	
		<table style='padding-bottom:8px;' width="100%" align="center" cellpadding="0" cellspacing="0" id="tabelaCabecalhoDvmsys">

            <tr <smarty>$ocultarCabecalhoRodape</smarty>>
				<td align="center"><div style="width:302px; height:70px; margin-top:3px; margin-bottom:3px;"><img src="<smarty>$smarty.const.DIR_IMAGENS</smarty>logo_erp.png" width="302" height="70"></div></td>
			</tr>

			<tr>
				<td class="nome_formulario"><smarty>$campo[1]</smarty> - <smarty>$versao</smarty></td>
			</tr>
			<tr <smarty>$ocultarCabecalhoRodape</smarty>>
				<td style="height:10px; padding-right:0px; text-align: right;">
				<img class="mini_seta" src="<smarty>$smarty.const.DIR_IMAGENS</smarty>mini_seta.png"><label class="link_1"><smarty>$smarty.session.login</smarty></label><img class="mini_seta" src="<smarty>$smarty.const.DIR_IMAGENS</smarty>mini_seta.png"><a href="../inicio.php" class="link_1">Inicio</a><img class="mini_seta" src="<smarty>$smarty.const.DIR_IMAGENS</smarty>mini_seta.png"><a href="../logout.php" class="link_1">Sair</a></td>
			</tr>
			
		</table>

		<img id="div_loader_dvm" src="<smarty>$smarty.const.DIR_IMAGENS</smarty>ajax-loader.gif" style='display:none;' />