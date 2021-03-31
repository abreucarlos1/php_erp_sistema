<!-- -->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="cache-control" content="max-age=0" />
<meta http-equiv="cache-control" content="no-cache" />
<meta http-equiv="expires" content="0" />
	<smarty>$xajax_javascript</smarty>

<title>::.. Empresa X (SISTEMA)  - <smarty>$nome_formulario</smarty>  ..::</title>
<link href="../includes/dhtmlx/dhtmlxGrid/codebase/dhtmlxgrid.css" rel="stylesheet" type="text/css" />
<link href="../includes/dhtmlx/dhtmlxTabbar/codebase/dhtmlxtabbar.css" rel="stylesheet" type="text/css" />
<link href="../classes/css_geral.css" rel="stylesheet" type="text/css" />

</head>

<body onload="<smarty>$body_onload</smarty>">

<div id="div_body" align="center" style="width:100%;">
	<div style="width:1100px">

		<table width="100%" border="0" cellspacing="0" cellpadding="0">
		  <tr>
			<td width="109" class="fundo_cinza"> </td>
			<td width="66" class="fundo_azul"> </td>
			<td width="832" class="<smarty>$classe</smarty>"> </td>
			<td width="2" rowspan="4" class="<smarty>$classe</smarty>"> </td>
		  </tr>
		  <tr>
			<td colspan="2" rowspan="2"><img src="../images/logo_h.jpg" width="200" height="56" class="imagem_sp" /></td>
			<td align="right" valign="middle"><img src="../images/setas.gif" width="22" height="13" class="imagem_sp" />
				<label class="fonte_14"><smarty>$nome_formulario</smarty></label> </td>
			</tr>
		  <tr>
			<td align="right" valign="middle"><img src="../images/setas_menor.gif" width="18" height="10" class="imagem_sp" />
				<label  class="fonte_12_az">
				<smarty>$smarty.session.login</smarty>
				</label> 
				<img src="../images/setas_menor.gif" width="18" height="10" class="imagem_sp" /><a href="../inicio.php" class="fonte_12_az"> Inicio </a> <img src="../images/setas_menor.gif" width="18" height="10" class="imagem_sp" /><a href="../logout.php" class="fonte_12_az"> Sair</a> </td>
			</tr>
		  <tr>
			<td class="fundo_cinza"> </td>
			<td class="fundo_azul"> </td>
			<td class="<smarty>$classe</smarty>"> </td>
			</tr>
		</table>
		
