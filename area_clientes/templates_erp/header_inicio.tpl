<!-- -->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

	<smarty>$xajax_javascript</smarty>

<title>::.. NOME EMPRESA - <smarty>$campo[1]</smarty> - <smarty>$versao</smarty>  ..::</title>
<link href="<smarty>$classe</smarty>" rel="stylesheet" type="text/css" />
</head>

<body onload="<smarty>$body_onload</smarty>" onresize="<smarty>$body_onload</smarty>">

<div align="center" style="width:100%;">

	<div style="width:1020px;">
	
		<table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
           
            <tr>
				<td align="center"><div style="width:302px; height:70px; margin-top:3px; margin-bottom:3px;"><smarty>$logo_cliente</smarty></div></td>
			</tr>

			<tr>
				<td class="nome_formulario"><smarty>$campo[1]</smarty> - <smarty>$versao</smarty></td>
			</tr>
			<tr>
				<td style="height:20px; padding-right:0px; text-align: right;">
				<table width="140" border="0" align="left" cellpadding="0" cellspacing="0">
					<tr>
						<td> </td>
					</tr>
				</table>
						<img class="mini_seta" src="../images/mini_seta.jpg" /><label class="link_1"><smarty>$smarty.session.login</smarty></label><img class="mini_seta" src="../images/mini_seta.jpg" /><a href="logout.php" class="link_1">Sair</a></td>
			</tr>

		</table>
		
