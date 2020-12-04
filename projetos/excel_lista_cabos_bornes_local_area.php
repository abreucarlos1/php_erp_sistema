<?php
/*
		Criado por Carlos Abreu / Otávio Pamplona
		
		Versão 0 --> VERSÃO INICIAL
		Versão 1 --> Retomada do uso - Simioli / alterado por Carlos Abreu - 10/03/2016

*/	
session_start();
if(!isset($_SESSION["id_usuario"]) || !isset($_SESSION["nome_usuario"]))
{
    // Usu�rio n�o logado! Redireciona para a p�gina de login
    header("Location: ../index.php");
    exit;
}

header("Content-Type: application/vnd.ms-excel");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");


// Inclui o arquivo de utilidades
require ("../includes/tools.inc.php");
//require ("../includes/layout.php");

include ("../includes/conectdb.inc.php");

$db = new banco_dados;

if($_POST["disciplina"]!='')
{

	$sql = "SELECT * FROM ".DATABASE.".setores ";
	$sql .= "WHERE id_setor = '".$_POST["disciplina"]."' ";
	
	$registro = $db->select($sql,'MYSQL');
	
	$cont = mysqli_fetch_array($registro);
	
	$disciplina = $cont["setor"];
	
	$abrdisc = $cont["abreviacao"];
	
	$filtro = "AND componentes.id_disciplina = '".$_POST["disciplina"]."' ";
	
}
else
{
	$disciplina = 'GERAL';
	$abrdisc = 'GER';
	$filtro = "";
}


$sql1 = "SELECT * FROM ".DATABASE.".setores, Projetos.area, Projetos.locais  ";
$sql1 .= "LEFT JOIN Projetos.equipamentos ON (Projetos.locais.id_equipamento = Projetos.equipamentos.id_equipamentos) ";
$sql1 .= "WHERE Projetos.locais.id_disciplina = ".DATABASE.".setores.id_setor ";
$sql1 .= "AND ".DATABASE.".setores.setor = 'EL�TRICA' ";
//$sql1 .= "AND Projetos.locais.id_disciplina = '" . $_POST["disciplina"] . "' ";
//$sql1 .= "AND Projetos.locais.id_disciplina = '" . $_POST["disciplina"] . "' ";
$sql1 .= "AND locais.id_area = area.id_area ";
$sql1 .= "AND area.id_area = '".$_POST["id_area"]."' ";
$sql1 .= "ORDER BY cd_local, nr_sequencia ";

$regsub = $db->select($sql1,'MYSQL');

?>

<html>
<head>
<title>LISTA DE COMPONENTES</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<link href="/voith/stylescss/estilos.css" rel="stylesheet" type="text/css"></head>
<body>

<table width="1059" border="1">
  <tr>
  	<td width="175" height="23" bordercolor="#D4D0C8" bgcolor="#999999"><div align="center"><strong>IDENTIFICA��O</strong></div></td>
    <td width="122" bordercolor="#D4D0C8" bgcolor="#999999"><div align="center"><strong>FORMA��O</strong></div></td>
	<td width="110" bordercolor="#D4D0C8" bgcolor="#999999"><div align="center"><strong>DE</strong></div></td>
	<td width="110" bordercolor="#D4D0C8" bgcolor="#999999"><div align="center"><strong>IDENT.</strong></div></td>
	<td width="123" bordercolor="#D4D0C8" bgcolor="#999999"><div align="center"><strong>PARA</strong></div></td>
	<td width="129" bordercolor="#D4D0C8" bgcolor="#999999"><div align="center"><strong>COMPR</strong></div></td>
	<td width="89" bordercolor="#D4D0C8" bgcolor="#999999"><div align="center"><strong>TRECHO</strong></div></td>
    <td width="110" bordercolor="#D4D0C8" bgcolor="#999999"><div align="center"><strong>OBSERVA��O</strong></div></td>
  </tr>
  <?

while ($locais = mysqli_fetch_array($regsub))
{
		//$pdf->Cell(185,4,$subsistema["subsistema"],0,1,'L',0);			
		
	$sql = "SELECT *, cabos.id_cabo ";
	$sql .= "FROM Projetos.area, Projetos.subsistema, Projetos.cabos_tipos, Projetos.cabos ";
	$sql .= "LEFT JOIN Projetos.cabos_bornes ON (cabos_bornes.id_cabo = cabos.id_cabo) ";
	$sql .= "LEFT JOIN Projetos.cabos_veias ON (cabos_bornes.id_cabo_veia = cabos_veias.id_cabo_veia) ";
	$sql .= "WHERE cabos.id_subsistema = subsistema.id_subsistema ";
	$sql .= "AND subsistema.id_area = area.id_area ";
	$sql .= "AND cabos.id_cabo_tipo = cabos_tipos.id_cabo_tipo ";
	$sql .= "AND cabos.id_disciplina = '".$_POST["disciplina"]."' ";
	$sql .= "AND cabos.id_origem_local = '".$locais["id_local"]."' ";
	$sql .= "ORDER BY cabos.id_cabo, id_origem_local, nr_subsistema, ds_formacao, seq_veia ";

	$registro = $db->select($sql,'MYSQL');
		
	if(false)
	{
	
		?>
		<tr>
			<td colspan="8" style="font-size:12px"><font color="#FF0000"><strong><?= $locais["id_disciplina"] ?></strong></font></td>
		</tr>
		<tr>
			<td colspan="8"><strong>&nbsp;</strong></td>
		</tr>
		<?
	
	}
		$num = $db->numero_registros;

		while ($cabos = mysqli_fetch_array($registro))
		{
		
		$sql2 = "SELECT * FROM Projetos.processo, Projetos.dispositivos, Projetos.funcao, Projetos.componentes, Projetos.malhas, Projetos.subsistema, Projetos.area ";
		$sql2 .= "WHERE componentes.id_malha = malhas.id_malha ";
		$sql2 .= "AND componentes.id_funcao = funcao.id_funcao ";
		$sql2 .= "AND componentes.id_dispositivo = dispositivos.id_dispositivo ";
		$sql2 .= "AND malhas.id_processo = processo.id_processo ";
		$sql2 .= "AND malhas.id_subsistema = subsistema.id_subsistema ";
		$sql2 .= "AND subsistema.id_area = area.id_area ";
		$sql2 .= "AND componentes.id_componente = '".$cabos["id_destino_comp"]."' ";
		
		$regis2 = $db->select($sql2,'MYSQL');
		
		$destcomp = mysqli_fetch_array($regis2);
		
		if($destcomp["processo"]!='D')
		{

			$nrmalha = sprintf("%03d",$destcomp["nr_malha"]);
			
			if($nrmalha==0)
			{
				$nrmalha ="";
			}
		}
		else
		{
			$nrmalha = $destcomp["nr_malha"];
		}
		
		if($destcomp["omit_proc"])
		{
			$processo = '';
		}
		else
		{
			$processo = $destcomp["processo"];
		}
		
		if($destcomp["nr_malha_seq"]!='')
		{
			$nrseq = '.'.$destcomp["nr_malha_seq"];
		}
		else
		{
			$nrseq = ' ';
		}
		
		if($destcomp["funcao"]!="")
		{
			$modificador =" ". $destcomp["funcao"];
		}
		else
		{
			if($destcomp["comp_modif"])
			{
				$modificador = ".".$destcomp["comp_modif"];
			}
			else
			{
				$modificador = " ";
			}
		}
	
		$sql3 = "SELECT * FROM ".DATABASE.".setores, Projetos.area, Projetos.locais  ";
		$sql3 .= "LEFT JOIN Projetos.equipamentos ON (Projetos.locais.id_equipamento = Projetos.equipamentos.id_equipamentos) ";
		$sql3 .= "WHERE Projetos.locais.id_disciplina = ".DATABASE.".setores.id_setor ";
		$sql3 .= "AND ".DATABASE.".setores.setor = 'EL�TRICA' ";
		$sql3 .= "AND locais.id_area = area.id_area ";
		$sql3 .= "AND locais.id_local = '".$cabos["id_destino_local"]."' ";
		//$sql3 .= "ORDER BY cd_local, nr_sequencia, ds_equipamento ";
		
		$regis3 = $db->select($sql3,'MYSQL');
		
		$destlocal = mysqli_fetch_array($regis3);
		
		$origem = $locais["nr_area"]. " " .$locais["cd_local"] . " " . $locais["nr_sequencia"];
		
		$destino = $destcomp["nr_area"]." ".$processo . $destcomp["dispositivo"] . "  " .$nrmalha.$nrseq.$modificador." ".$destlocal["nr_area"] . " " .$destlocal["cd_local"] . " " . $destlocal["nr_sequencia"];





		  ?>
			<tr style="font-size:9px">
				<td>&nbsp;<?= $cabos["identificacao_cabo"] ?></td>
				<td>&nbsp;<?= $cabos["ds_formacao"] ?></td>
				<td>&nbsp;<?= $origem ?></td>
				<td>&nbsp;</td>
				<td>&nbsp;<?= $destino ?></td>
				<td>&nbsp;<?= $cabos["nr_comprimento"] ?></td>
				<td>&nbsp;0</td>
				<td>&nbsp;<?= $cabos["ds_trecho"] ?></td>
				<td>&nbsp;<?= $cabos["ds_observacao"] ?></td>
			</tr>
		  <?		
		}
		// Libera a mem�ria

	if($num>0)
	{

		?>
		<tr><TD colspan="9">&nbsp;</TD></tr>
		<?
	}		
	
	
	
}

?>
</table>
</body>
</html>
