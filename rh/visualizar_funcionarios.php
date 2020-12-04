<?php
/*
		Formul�rio de Visualizar Funcionarios	
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		../rh/visualizar_funcionarios.php
		
		Versão 0 --> VERSÃO INICIAL - 20/03/2007
		Versão 1 --> Inclusão dos campos reg_del nas consultas - 29/11/2017 - Carlos Abreu
*/	

session_start();

if(!isset($_SESSION["id_usuario"]) || !isset($_SESSION["nome_usuario"]))
{
	// Usu�rio n�o logado! Redireciona para a p�gina de login
	header("Location: ../index.php?pagina=" . $_SERVER['PHP_SELF']);
	exit;
}

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));

$sql = "SELECT * FROM empresas ";
$sql .= "LEFT JOIN unidade ON (empresas.id_unidade = unidades.id_unidade AND unidades.reg_del = 0) ";
$sql .= "LEFT JOIN segmentos ON (segmentos.id_segmento = empresas.id_segmento AND segmentos.reg_del = 0) ";
$sql .= "WHERE empresas.id_empresa_erp = '".$_GET["id_empresa"]."' ";
$sql .= "AND empresas.reg_del = 0 ";

$registro = mysql_query($sql,$conexao) or die("Não foi possível fazer a seleção." . $sql);

$regs = mysql_fetch_array($registro);

$sql = "SELECT * FROM contatos ";
$sql .= "WHERE contatos.id_empresa_erp = '".$_GET["id_empresa"]."' ";
$sql .= "AND contatos.reg_del = 0 ";
$sql .= "AND contatos.situacao = 1 ";
$sql .= "ORDER BY nome_contato ";

$registro1 = mysql_query($sql,$conexao) or die("Não foi possível fazer a seleção." . $sql);


?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>::..  (ERP1-2 0 0 7)  - Visualizar empresas ..::</title>
<link href="../classes/css_geral.css" rel="stylesheet" type="text/css" />
<style type="text/css">
<!--
.style1 {color: #FFFFFF}
-->
</style>
</head>

<body>
<table width="100%" border="0">
  <tr>
    <td colspan="2" align="center" valign="top">
	<img src="<?= $regs["logotipo"] ?>"/>	</td>
  </tr>
</table>
<br />
<table width="100%" border="0">
  <tr>
    <td width="13%" class="fonte_12_az">empresa:</td>
    <td width="1%">&nbsp;</td>
    <td width="86%" class="caixa_txt"><?= $regs["empresa"]. ' - '.$regs["unidade"] ?></td>
  </tr>
  <tr>
    <td class="fonte_12_az">Abreviação</td>
    <td>&nbsp;</td>
    <td class="caixa_txt"><?= $regs["abreviacao"]?></td>
  </tr>
  <tr>
    <td class="fonte_12_az">Endereço</td>
    <td>&nbsp;</td>
    <td class="caixa_txt"><?= $regs["endereco"]. ' - '. $regs["bairro"] ?></td>
  </tr>
  <tr>
    <td class="fonte_12_az">cidade</td>
    <td>&nbsp;</td>
    <td class="caixa_txt"><?= $regs["cidade"] . ' - '.  $regs["estado"] ?></td>
  </tr>
  <tr>
    <td class="fonte_12_az">telefone</td>
    <td>&nbsp;</td>
    <td class="caixa_txt"><?= $regs["telefone"] ?></td>
  </tr>
  <tr>
    <td class="fonte_12_az">fax</td>
    <td>&nbsp;</td>
    <td class="caixa_txt"><?= $regs["fax"] ?></td>
  </tr>
  <tr>
    <td class="fonte_12_az">Home Page </td>
    <td>&nbsp;</td>
    <td class="caixa_txt"><a href="<?= $regs["homepage"] ?>" target="_blank"><?= $regs["homepage"] ?></a></td>
  </tr>
</table>
<br />
<form id="form1" name="form1" method="post" action="etqtA46181.php" target="_blank">
<table width="100%" border="1">
  <tr>
    <td colspan="5"><div align="center" class="fundo_azul_claro style1">CONTATOS</div></td>
  </tr>
  <tr class="fonte_descricao_campos">
		<td width="30%">Nome</td>
		<td width="21%">E-mail</td>
		<td width="14%">telefone - Ramal</td>
		<td width="17%">celular</td>
        <td width="18%">Imprimir etiqueta </td>
  </tr>
<?
	while($regs1 = mysql_fetch_array($registro1))
	{
	?>
	  <tr class="fonte_11" bordercolor="#0099FF">
		<td width="30%">&nbsp;<?= $regs1["nome_contato"] ?></td>
		<td width="21%">&nbsp;<a href="mailto:<?= $regs1["email"] ?>"><?= $regs1["email"] ?></a></td>
		<td width="14%">&nbsp;<?= $regs1["telefone"]. ' - '.$regs1["Ramal1"] ?></td>
		<td width="17%">&nbsp;<?= $regs1["celular"] ?></td>
		
	    <td width="18%"><div align="center">
	      
	        <input name="chk_<?= $regs1["id_contato"] ?>" type="checkbox" value="1" checked="checked" />
          
	      </div></td>
	  </tr>
  	<?
  	}
	if(mysql_num_rows($registro1)>0)
	{
	?>
	  <tr class="fonte_11">
		<td  colspan="4"><input type="hidden" name="id_empresa" value="<?= $regs["id_empresa_erp"] ?>" /></td>
	    <td width="18%" align="center"><input class="botao_chumbo" name="btn" type="submit" value="&nbsp;Imprimir Etiqueta"/></td>
	  </tr>
	<?	
	}
	
?>
</table>
</form>
</body>
</html>
