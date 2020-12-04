<!-- -->
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="cache-control" content="max-age=0" />
<meta http-equiv="cache-control" content="no-cache" />
<meta http-equiv="expires" content="0" />
	<smarty>$xajax_javascript</smarty>

<title>::.. Empresa X (SISTEMA)  - <smarty>$nome_formulario</smarty>  ..::</title>
<link href="includes/dhtmlx/dhtmlxGrid/codebase/dhtmlxgrid.css" rel="stylesheet" type="text/css" />
<link href="includes/dhtmlx/dhtmlxTabbar/codebase/dhtmlxtabbar.css" rel="stylesheet" type="text/css" />
<link href="classes/css_geral.css" rel="stylesheet" type="text/css" />

</head>

<body onload="<smarty>$body_onload</smarty>">

<div id="div_body" align="center" style="width:100%;">
	<div style="width:1100px">

		<table width="100%" border="0" cellspacing="0" cellpadding="0">
		  <tr>
			<td width="109" class="fundo_cinza">&nbsp;</td>
			<td width="66" class="fundo_azul">&nbsp;</td>
			<td width="832" class="<smarty>$classe</smarty>">&nbsp;</td>
			<td width="2" rowspan="4" class="<smarty>$classe</smarty>">&nbsp;</td>
		  </tr>
		  <tr>
			<td colspan="2" rowspan="2"><img src="/ERP/images/logo_h.jpg" width="200" height="56" class="imagem_sp" /></td>
			<td align="right" valign="middle"><img src="/ERP/images/setas.gif" width="22" height="13" class="imagem_sp" />
				<label class="fonte_14"><smarty>$nome_formulario</smarty></label>&nbsp;</td>
			</tr>
		  <tr>
			<td align="right" valign="middle"><img src="/ERP/images/setas_menor.gif" width="18" height="10" class="imagem_sp" />
				<label  class="fonte_12_az">
				<smarty>$smarty.session.login</smarty>
				</label>&nbsp;
				<img src="/ERP/images/setas_menor.gif" width="18" height="10" class="imagem_sp" /><a href="../inicio.php" class="fonte_12_az">&nbsp;Inicio&nbsp;</a> <img src="/erp/images/setas_menor.gif" width="18" height="10" class="imagem_sp" /><a href="../logout.php" class="fonte_12_az">&nbsp;Sair</a>&nbsp;</td>
			</tr>
		  <tr>
			<td class="fundo_cinza">&nbsp;</td>
			<td class="fundo_azul">&nbsp;</td>
			<td class="<smarty>$classe</smarty>">&nbsp;</td>
			</tr>
		</table>
		
