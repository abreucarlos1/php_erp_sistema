<!-- -->
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta http-equiv="X-UA-Compatible" content="IE=Edge">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="cache-control" content="max-age=0" />
<meta http-equiv="cache-control" content="no-cache, must-revalidate" />
<meta http-equiv="expires" content="0" />
	<smarty>$xajax_javascript</smarty>

<title>::.. Empresa X - ERP  - <smarty>$campo[1]</smarty> - <smarty>$versao</smarty>  ..::</title>
<link href="../includes/dhtmlx/dhtmlxGrid/codebase/dhtmlxgrid.css" rel="stylesheet" type="text/css" />
<link href="../includes/dhtmlx/dhtmlxTabbar/codebase/dhtmlxtabbar.css" rel="stylesheet" type="text/css" />

<link href="<smarty>$classe</smarty>" rel="stylesheet" type="text/css" />

<script src="../includes/utils.js"></script>
</head>

<body  onload="<smarty>$body_onload</smarty>">

<div align="center" style="width:100%;">

	<div style="width:1020px;">
	
		<table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">

            <tr>
				<td align="center"><div style="width:302px; height:70px; margin-top:3px; margin-bottom:3px;"><img src="<smarty>$smarty.const.DIR_IMAGENS</smarty>logo_erp.png" width="302" height="70" /></div></td>
			</tr>

			<tr>
				<td class="nome_formulario"><smarty>$campo[1]</smarty>&nbsp;-&nbsp;<smarty>$versao</smarty></td>
			</tr>
			<tr style="padding-bottom:20px;">
				<td style="height:20px; padding-right:0px; text-align: right;">
				<table width="140" border="0" align="left" cellpadding="0" cellspacing="0">
					<tr>
						<td>&nbsp;</td>
					</tr>
				</table>
						<img class="mini_seta" src="<smarty>$smarty.const.DIR_IMAGENS</smarty>mini_seta.png" /><label class="link_1"><smarty>$smarty.session.login</smarty></label><img class="mini_seta" src="<smarty>$smarty.const.DIR_IMAGENS</smarty>mini_seta.png" /><a href="../inicio.php" class="link_1">Inicio</a><img class="mini_seta" src="<smarty>$smarty.const.DIR_IMAGENS</smarty>mini_seta.png" /><a href="../logout.php" class="link_1">Sair</a></td>
			</tr>
		</table>
		<smarty>if isset($erros)</smarty>
		<smarty>foreach $erros as $err</smarty>
			<h2 style="color:red;"><smarty>$err['mensagem']</smarty></h2>
			<smarty>/foreach</smarty>
		<smarty>/if</smarty>