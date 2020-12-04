<?php
/*

		data de cria��o: 09/05/2006
		
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


$sql = "SELECT * FROM Projetos.area, Projetos.subsistema ";
$sql .= "WHERE subsistema.id_area = area.id_area ";
$sql .= "AND area.id_area = '" .$_POST["id_area"] . "' ";
$sql .= "ORDER BY nr_subsistema ";

$regsub = $db->select($sql,'MYSQL');



?> 
<html>
<head>
<title>LISTA DE COMPONENTES</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<link href="/voith/stylescss/estilos.css" rel="stylesheet" type="text/css"></head>
<body>

<table width="1020" border="1">
  <tr>
  	<td width="74" height="23" bordercolor="#D4D0C8" bgcolor="#999999"><div align="center"><strong>TAG</strong></div></td>
    <td width="155" bordercolor="#D4D0C8" bgcolor="#999999"><div align="center"><strong>COMPONENTE</strong></div></td>
	<td width="132" bordercolor="#D4D0C8" bgcolor="#999999"><div align="center"><strong>FUN&Ccedil;&Atilde;O</strong></div></td>
	<td width="142" bordercolor="#D4D0C8" bgcolor="#999999"><div align="center"><strong>LOCAL</strong></div></td>
	<td width="139" bordercolor="#D4D0C8" bgcolor="#999999"><div align="center"><strong>DISPOSITIVO</strong></div></td>
	<td width="112" bordercolor="#D4D0C8" bgcolor="#999999"><div align="center"><strong>RACK</strong></div></td>
	<td width="108" bordercolor="#D4D0C8" bgcolor="#999999"><div align="center"><strong>ENDERE&Ccedil;O</strong></div></td>
    <td width="45" bordercolor="#D4D0C8" bgcolor="#999999"><div align="center"><strong>TIPO</strong></div></td>
    <td width="55" bordercolor="#D4D0C8" bgcolor="#999999"><div align="center"><strong>REV</strong></div></td>
  </tr>
  <?
 
while ($subsistema = mysqli_fetch_array($regsub))
{
	
		//$pdf->Cell(185,4,$subsistema["subsistema"],0,1,'L',0);			
		?>
		<tr>
			<td colspan="9" style="font-size:12px"><font color="#FF0000"><strong><?= $subsistema["subsistema"] ?></strong></font></td>
		</tr>
		<tr>
			<td colspan="9"><strong>&nbsp;</strong></td>
		</tr>
		<?
		
		$sql1 = "SELECT * FROM Projetos.malhas, Projetos.processo ";
		$sql1 .= "WHERE id_subsistema = '".$subsistema["id_subsistema"]."' ";
		$sql1 .= "AND malhas.id_processo = processo.id_processo ";
		$sql1 .= "ORDER BY processo, nr_malha, nr_malha_seq ";	
		
		$regmalha = $db->select($sql1,'MYSQL');
		
		while ($malhas = mysqli_fetch_array($regmalha))
		{

			/*
			acrescenta zeros a esquerda
			*/
			if($malhas["processo"]!='D')
			{
				$nrmalha = sprintf("%03d",$malhas["nr_malha"]);
			}
			else
			{
				$nrmalha = $malhas["nr_malha"];
			}	
			
			if($malhas["nr_malha_seq"]!='')
			{
				$nrseq = '.'.$malhas["nr_malha_seq"];
			}
			else
			{
				$nrseq = ' ';
			}
			
	
			//$pdf->SetFont('Arial','B',8);
			//$pdf->Cell(90,4,$malhas["ds_servico"],0,1,'L',0);
			//$pdf->SetFont('Arial','',8);
			?>
			<tr>
				
				<td colspan="9" style="font-size:10px"><strong><?= $malhas["processo"]." - ".$nrmalha ?>&nbsp;&nbsp;&nbsp;<?= " - " ?>&nbsp;&nbsp;&nbsp;<?= $malhas["ds_servico"] ?></strong></td>
				<td>&nbsp;</td>
			</tr>
			<?
			
		
			
			$sql = "SELECT * FROM ".DATABASE.".setores, Projetos.funcao , Projetos.dispositivos, Projetos.componentes ";
			$sql .= "LEFT JOIN Projetos.enderecos ON (componentes.id_componente = enderecos.id_componente) ";
			$sql .= "LEFT JOIN Projetos.slots ON (enderecos.id_slots = slots.id_slots) ";
			$sql .= "LEFT JOIN Projetos.cartoes ON (cartoes.id_cartoes = slots.id_cartoes) ";
			$sql .= "LEFT JOIN Projetos.racks ON (slots.id_racks = racks.id_racks) ";
			$sql .= "LEFT JOIN Projetos.devices ON (racks.id_devices = devices.id_devices), ";
			$sql .= "Projetos.locais LEFT JOIN Projetos.equipamentos ON (locais.id_equipamento = equipamentos.id_equipamentos) ";
			$sql .= "WHERE componentes.id_malha = '" . $malhas["id_malha"] . "' ";
			//$sql .= "AND processo.id_processo = '" . $malhas["id_processo"] . "'  ";
			$sql .= "AND componentes.id_funcao = funcao.id_funcao ";
			$sql .= "AND componentes.id_dispositivo = dispositivos.id_dispositivo ";
			$sql .= "AND componentes.id_local = locais.id_local ";
			$sql .= $filtro;
			$sql .= "AND locais.id_disciplina = setores.id_setor ";
			$sql .= "ORDER BY sequencia ";
			
			$regcomp = $db->select($sql,'MYSQL');
			
			while ($componentes = mysqli_fetch_array($regcomp))
			{
				
				if($componentes["omit_proc"])
				{
					$processo = '';
				}
				else
				{
					$processo = $malhas["processo"]. "";
				}
				
				if($componentes["funcao"]!="")
				{
					$modificador =" - ". $componentes["funcao"];
				}
				else
				{
					if($componentes["comp_modif"])
					{
						$modificador = ".".$componentes["comp_modif"];
					}
					else
					{
						$modificador = "";
					}
				}
				
				if($componentes["setor"]=='EL�TRICA')
				{
					$sql = "SELECT * FROM Projetos.locais ";
					$sql .= "LEFT JOIN Projetos.equipamentos ON (Projetos.locais.id_equipamento = Projetos.equipamentos.id_equipamentos) ";
					$sql .= "WHERE Projetos.locais.id_local = '".$componentes["id_local"]."' ";
					$sql .= "ORDER BY cd_local, nr_sequencia, ds_equipamento ";
					
					$regis = $db->select($sql,'MYSQL');
					
					$cont = mysqli_fetch_array($regis);
					
					$tag = $malhas["nr_area"]. " - ". $cont["cd_local"]. " ". $cont["nr_sequencia"];
		
				}
				else
				{
					if($componentes["setor"]=='MEC�NICA')
					{
						$sql = "SELECT * FROM Projetos.locais ";
						$sql .= "LEFT JOIN Projetos.equipamentos ON (Projetos.locais.id_equipamento = Projetos.equipamentos.id_equipamentos) ";
						$sql .= "WHERE Projetos.locais.id_local = '".$componentes["id_local"]."' ";
						$sql .= "ORDER BY cd_local, nr_sequencia, ds_equipamento ";							
						
						$regis = $db->select($sql,'MYSQL');
						
						$cont = mysqli_fetch_array($regis);
						
						$tag = $malhas["nr_area"]. " - ". $cont["cd_local"]. " ". $cont["nr_sequencia"];
						
					}
					else
					{
						$sql = "SELECT * FROM Projetos.locais ";
						$sql .= "LEFT JOIN Projetos.fluidos ON (Projetos.locais.id_fluido = Projetos.fluidos.id_fluido) ";
						$sql .= "LEFT JOIN Projetos.materiais ON (Projetos.locais.id_material = Projetos.materiais.id_material) ";
						$sql .= "WHERE Projetos.locais.id_local = '".$componentes["id_local"]."' ";
						$sql .= "ORDER BY cd_fluido, nr_sequencia, cd_material, nr_diametro ";							
		
						$regis = $db->select($sql,'MYSQL');
						
						$cont = mysqli_fetch_array($regis);
		
						$tag = $cont["cd_fluido"]. " - ". $cont["nr_sequencia"]. " - ". $cont["cd_material"]. " - ". $cont["nr_diametro"];
				
					}
				}
				?>
				<tr style="font-size:9px">
					<td>&nbsp;<?= $subsistema["nr_area"] . " - " . $processo . $componentes["dispositivo"]. " - " . $nrmalha.$nrseq . $modificador ?></td>
					<td>&nbsp;<?= $componentes["ds_dispositivo"] ?></td>
					<td>&nbsp;<?= $componentes["ds_funcao"] ?></td>
					<td>&nbsp;<?= $tag ?></td>
					<td>&nbsp;<?= $componentes["cd_dispositivo"] ?></td>
					<td>&nbsp;<?= $componentes["nr_rack"] ?></td>
					<td>&nbsp;<?= maiusculas($componentes["cd_endereco"]) ?></td>
					<td>&nbsp;<?= $componentes["cd_atributo"] ?></td>
					<td>&nbsp;<?= $componentes["comp_revisao"] ?></td>
					
					
				</tr>
				<?
				
				/*
				//$pdf->Cell(180,20,$sql,0,0,'L',0);
				$pdf->HCell(33,4,$subsistema["nr_area"] . " - " . $processo . $componentes["dispositivo"]. " - " . $nrmalha . $nrseq . $modificador ,0,0,'L',0);
				$pdf->HCell(50,4,$componentes["ds_dispositivo"],0,0,'L',0);
				$pdf->HCell(40,4,$componentes["ds_funcao"],0,0,'L',0);
				$pdf->HCell(40,4,$tag,0,0,'L',0);
				$pdf->HCell(30,4,$componentes["cd_dispositivo"],0,0,'L',0);
				$pdf->HCell(35,4,$componentes["nr_rack"],0,0,'L',0);
				$pdf->HCell(25,4,maiusculas($componentes["cd_endereco"]),0,0,'L',0);
				$pdf->HCell(10,4,$componentes["cd_atributo"],0,0,'C',0);
				$pdf->HCell(10,4,$componentes["comp_revisao"],0,1,'C',0);
				*/
			}
			//$pdf->Ln(2);
			?>
			<tr><TD colspan="9">&nbsp;</TD></tr>
			<?
		}
			
			
		
}

?>
</table>
</body>
</html>

