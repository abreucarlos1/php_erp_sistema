<?php
/*
	
		Criado por Carlos Abreu / Otávio Pamplona

	
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

require ("../includes/tools.inc.php");
include ("../includes/conectdb.inc.php");

$db = new banco_dados;

$filtro = '';
$disciplina = '';

if($_POST["slots"]!='')
{
	if($_POST["racks"]!='')
	{
		if($_POST["devices"]!='')
		{
			$filtro = "AND devices.cd_dispositivo = '".$_POST["devices"]."' ";
			$disciplina = 'DEVICE: '.$_POST["cd_dispositivo"];		
		}
		$filtro .= "AND racks.nr_rack = '".$_POST["racks"]."' ";
		$disciplina .= 'RACK: '.$_POST["racks"];
	}
	$filtro .= "AND slots.nr_slot = '".$_POST["slots"]."' ";
	$disciplina .= 'SLOT: '.$_POST["slots"];
}
else
{
	if($_POST["racks"]!='')
	{
		$filtro = "AND racks.nr_rack = '".$_POST["racks"]."' ";
		$disciplina = 'RACK: '.$_POST["racks"];
	}
	else
	{
		if($_POST["devices"]!='')
		{
			$filtro = "AND devices.cd_dispositivo = '".$_POST["devices"]."' ";
			$disciplina = 'DEVICE: '.$_POST["cd_dispositivo"];	
		}
		$disciplina = 'GERAL';
		$filtro = "";
	}
	
}

/*

include("../includes/conectdbaqt.inc");

$sql_rev0 = "SELECT * FROM revisao_cliente ";
$sql_rev0 .= "WHERE id_os = '".$_SESSION["id_os"]."' ";
$sql_rev0 .= "AND tipodoc = '".$_POST["relatorio"]."' ";
//$sql_rev0 .= "AND numero_cliente = '".$_POST["numero_cliente"]."' ";
$sql_rev0 .= "AND numeros_interno = '".$_POST["numeros_interno"]."' ";
$sql_rev0 .= "ORDER BY versao_documento ASC LIMIT 1 ";

$reg_rev0 = mysql_query($sql_rev0,$conexao) or die("Não foi possível fazer a seleção.2" . $sql_rev0);

$revis0 = mysql_fetch_array($reg_rev0);

$sql_rev = "SELECT * FROM revisao_cliente ";
$sql_rev .= "WHERE id_os = '".$_SESSION["id_os"]."' ";
$sql_rev .= "AND tipodoc = '".$_POST["relatorio"]."' ";
$sql_rev .= "AND versao_documento NOT LIKE '".$revis0["versao_documento"]."' ";
//$sql_rev .= "AND numero_cliente = '".$_POST["numero_cliente"]."' ";
$sql_rev .= "AND numeros_interno = '".$_POST["numeros_interno"]."' ";
$sql_rev .= "ORDER BY versao_documento DESC LIMIT 5 ";

$reg_rev = mysql_query($sql_rev,$conexao) or die("Não foi possível fazer a seleção.2" . $sql);


$sql = "SELECT * FROM ".DATABASE.".caminho_docs, ".DATABASE.".OS ";
$sql .= "WHERE caminho_docs.id_os = '".$_SESSION["id_os"]."' ";
$sql .= "AND caminho_docs.id_os = OS.id_os ";

$registro = mysql_query($sql,$conexao) or die("Não foi possível fazer a seleção.2" . $sql);

$path1 = mysql_fetch_array($registro);

$path = str_replace('\\','/',$path1["caminho_pasta"]);

$caminho = "/home/dt_arqtec/".$path."/".$path1["os"]."-DOCS_EMITIDOS/".$path1["os"]."-".$abrdisc."/";

$pasta = explode("/",$_SERVER['SCRIPT_FILENAME']);


*/


$sql = "SELECT * FROM Projetos.area, Projetos.subsistema, Projetos.locais, Projetos.racks, Projetos.devices ";
$sql .= "WHERE area.id_area = '" .$_POST["id_area"]. "' ";
$sql .= "AND subsistema.id_area = area.id_area ";
$sql .= "AND area.id_area = locais.id_area ";
$sql .= "AND locais.id_local = racks.id_local ";
$sql .= "AND racks.id_devices = devices.id_devices ";

$registro = $db->select($sql,'MYSQL');

$reg = mysqli_fetch_array($registro);

//Seta o cabeçalho
/*
$pdf->cliente=$reg1["empresa"]; // Cliente
$pdf->subsistema = $reg["ds_divisao"]; // DIVIS�O
$pdf->area = $reg["ds_area"]; // �REA
$pdf->logotipocliente = $reg1["logotipo"]; // logotipo Cliente

$pdf->numeros_interno = $_POST["numeros_interno"];

$pdf->numero_cliente = $_POST["numero_cliente"];

$pdf->unidade= $reg1["unidade"];

$pdf->versao_documento = $_POST["versao_documento"];

$pdf->titulo = '';
$pdf->titulo2 = $reg1["osdesc"];

$pdf->emissao=date('d/m/Y');
//$pdf->versao_documento=$data_ini . " � " . $datafim;
*/

$flag = 0;

/*
// P�gina de rosto abaixo
$pdf->SetXY(10,70);

$pdf->SetFont('Arial','BU',20);
$pdf->Cell(280,10,"LISTA DE ENTRADAS E SA�DAS",0,1,'C',0);
$pdf->SetFont('Arial','BU',16);
$pdf->Cell(280,10,$disciplina,0,1,'C',0);
$pdf->Ln(5);
$pdf->SetFont('Arial','B',16);
$pdf->Cell(280,10, $reg["ds_divisao"] ,0,1,'C',0);
$pdf->Ln(5);
$pdf->Cell(280,10, $reg["ds_area"] ,0,1,'C',0);
$pdf->Ln(5);

//REVIS�ES
$pdf->SetFont('Arial','B',8);

$y = 155;

$pdf->SetXY(25,$y);
$pdf->SetFont('Arial','B',8);
$pdf->Cell(50,4,'CONTROLE DE REVIS�ES',0,1,'L',0);
$pdf->SetFont('Arial','',6);

$pdf->Ln(1);

$numregs = 4 - mysql_num_rows($reg_rev);

//c�lulas em branco
for($a=0;$a<=$numregs;$a++)
{
	$y += 4;
	$pdf->SetXY(25,$y);
	$pdf->Cell(10,4,'',1,0,'C',0);
	$pdf->Cell(70,4,'',1,0,'C',0);
	$pdf->Cell(20,4,'',1,0,'C',0);
	$pdf->Cell(20,4,'',1,0,'C',0);
	$pdf->Cell(20,4,'',1,0,'C',0);
	$pdf->Cell(20,4,'',1,0,'C',0);
}

include ("../includes/conectdb.inc");
while($revis = mysql_fetch_array($reg_rev))
{
	$sql_exe = "SELECT abreviacao FROM Funcionarios ";
	$sql_exe .= "WHERE id_funcionario = '".$revis["id_executante"]."' ";
	$regexe = mysql_query($sql_exe,$conexao) or die("N�o foi poss�vel a sele��o dos dados" . $sql_exe);
	$executante = mysql_fetch_array($regexe);
	
	$sql_ver = "SELECT abreviacao FROM Funcionarios ";
	$sql_ver .= "WHERE id_funcionario = '".$revis["id_verificador"]."' ";
	$regver = mysql_query($sql_ver,$conexao) or die("N�o foi poss�vel a sele��o dos dados" . $sql_ver);
	$verificador = mysql_fetch_array($regver);
	
	$sql_apr = "SELECT abreviacao FROM Funcionarios ";
	$sql_apr .= "WHERE id_funcionario = '".$revis["id_aprovador"]."' ";
	$regapr = mysql_query($sql_apr,$conexao) or die("N�o foi poss�vel a sele��o dos dados" . $sql_apr);
	$aprovador = mysql_fetch_array($regapr);
	
	$y += 4;
	
	$pdf->SetXY(25,$y);
	$pdf->Cell(10,4,$revis["versao_documento"],1,0,'C',0);
	$pdf->Cell(70,4,$revis["alteracao"],1,0,'C',0);
	$pdf->Cell(20,4,mysql_php($revis["data_emissao"]),1,0,'C',0);
	$pdf->Cell(20,4,$executante["abreviacao"],1,0,'C',0);
	$pdf->Cell(20,4,$verificador["abreviacao"],1,0,'C',0);
	$pdf->Cell(20,4,$aprovador["abreviacao"],1,1,'C',0);
	
}

			
include ("../includes/conectdb.inc");
$sql_exe0 = "SELECT abreviacao FROM Funcionarios ";
$sql_exe0 .= "WHERE id_funcionario = '".$revis0["id_executante"]."' ";
$regexe0 = mysql_query($sql_exe0,$conexao) or die("N�o foi poss�vel a sele��o dos dados" . $sql_exe0);
$contexe = mysql_fetch_array($regexe0);
$executante0 = $contexe["abreviacao"];

$sql_ver0 = "SELECT abreviacao FROM Funcionarios ";
$sql_ver0 .= "WHERE id_funcionario = '".$revis0["id_verificador"]."' ";
$regver0 = mysql_query($sql_ver0,$conexao) or die("N�o foi poss�vel a sele��o dos dados" . $sql_ver);
$contver = mysql_fetch_array($regver0);
$verificador0 = $contver["abreviacao"];

$sql_apr0 = "SELECT abreviacao FROM Funcionarios ";
$sql_apr0 .= "WHERE id_funcionario = '".$revis0["id_aprovador"]."' ";
$regapr0 = mysql_query($sql_apr0,$conexao) or die("N�o foi poss�vel a sele��o dos dados" . $sql_apr);
$contapr = mysql_fetch_array($regapr0);
$aprovador0 = $contapr["abreviacao"];

$y += 4;

$pdf->SetXY(25,$y);

$pdf->Cell(10,4,$revis0["versao_documento"],1,0,'C',0);
$pdf->Cell(70,4,$revis0["alteracao"],1,0,'C',0);
$pdf->Cell(20,4,mysql_php($revis0["data_emissao"]),1,0,'C',0);
$pdf->Cell(20,4,$executante0,1,0,'C',0);
$pdf->Cell(20,4,$verificador0,1,0,'C',0);
$pdf->Cell(20,4,$aprovador0,1,0,'C',0);

$pdf->SetXY(25,$y+4);

$pdf->SetFont('Arial','B',8);
$pdf->Cell(10,4,'REV.',1,0,'C',0);
$pdf->Cell(70,4,'ALTERA��O',1,0,'C',0);
$pdf->Cell(20,4,'DATA',1,0,'C',0);
$pdf->Cell(20,4,'EXEC.',1,0,'C',0);
$pdf->Cell(20,4,'VERIF.',1,0,'C',0);
$pdf->Cell(20,4,'APROV.',1,0,'C',0);		

//REVIS�ES

$pdf->SetXY(10,48);


$pdf->AddPage();

/*
$pdf->SetXY(10,48);

// T�TULOS
$pdf->SetFont('Arial','B',8);
$pdf->Cell(33,4,"LOCAL",0,0,'L',0);
$pdf->Cell(50,4,"DEVICE",0,0,'L',0);
$pdf->Cell(40,4,"N� RACK",0,0,'L',0);
$pdf->Cell(40,4,"SLOT",0,0,'L',0);
$pdf->Cell(30,4,"CAPACIDADE.",0,0,'L',0);
$pdf->Cell(35,4,"CART�O",0,0,'L',0);
$pdf->Cell(30,4,"TIPO",0,1,'L',0);
$pdf->SetFont('Arial','',8);

$pdf->Ln(2);
*/
?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
</head>

<body>
<?

$sql = "SELECT * FROM Projetos.locais, Projetos.racks, Projetos.devices, Projetos.slots, Projetos.cartoes, ".DATABASE.".setores ";
$sql .= "WHERE locais.id_area = '" . $_POST["id_area"] . "' ";
$sql .= "AND racks.id_local = locais.id_local ";
$sql .= "AND racks.id_devices = devices.id_devices ";
$sql .= "AND locais.id_disciplina = setores.id_setor ";
$sql .= "AND slots.id_cartoes = cartoes.id_cartoes ";
$sql .= "AND slots.id_racks = racks.id_racks ";
$sql .= $filtro;
$sql .= "ORDER BY cd_dispositivo, nr_rack, nr_slot ";

$regmalha = $db->select($sql,'MYSQL');

if($db->numero_registros>0)
{
	while ($malhas = mysqli_fetch_array($regmalha))
	{

		if($malhas["setor"]=='EL�TRICA')
		{
			$sql = "SELECT * FROM Projetos.locais ";
			$sql .= "LEFT JOIN Projetos.equipamentos ON (Projetos.locais.id_equipamento = Projetos.equipamentos.id_equipamentos) ";
			$sql .= "WHERE Projetos.locais.id_local = '".$malhas["id_local"]."' ";
			$sql .= "ORDER BY cd_local, nr_sequencia, ds_equipamento ";
			
			$regis = $db->select($sql,'MYSQL');
			
			$cont = mysqli_fetch_array($regis);
			
			$tag = $reg["nr_area"]. " - ". $cont["cd_local"]. " ". $cont["nr_sequencia"];
	
		}
		else
		{
			if($malhas["setor"]=='MEC�NICA')
			{
				$sql = "SELECT * FROM Projetos.locais ";
				$sql .= "LEFT JOIN Projetos.equipamentos ON (Projetos.locais.id_equipamento = Projetos.equipamentos.id_equipamentos) ";
				$sql .= "WHERE Projetos.locais.id_local = '".$malhas["id_local"]."' ";
				$sql .= "ORDER BY cd_local, nr_sequencia, ds_equipamento ";							
				
				$regis = $db->select($sql,'MYSQL');
				
				$cont = mysqli_fetch_array($regis);
				
				$tag = $reg["nr_area"]. " - ". $cont["cd_local"]. " ". $cont["nr_sequencia"];
				
			}
			else
			{
				$sql = "SELECT * FROM Projetos.locais ";
				$sql .= "LEFT JOIN Projetos.fluidos ON (Projetos.locais.id_fluido = Projetos.fluidos.id_fluido) ";
				$sql .= "LEFT JOIN Projetos.materiais ON (Projetos.locais.id_material = Projetos.materiais.id_material) ";
				$sql .= "WHERE Projetos.locais.id_local = '".$malhas["id_local"]."' ";
				$sql .= "ORDER BY cd_fluido, nr_sequencia, cd_material, nr_diametro ";							
	
				$regis = $db->select($sql,'MYSQL');
				
				$cont = mysqli_fetch_array($regis);
	
				$tag = $cont["cd_fluido"]. " - ". $cont["nr_sequencia"]. " - ". $cont["cd_material"]. " - ". $cont["nr_diametro"];
		
			}
		}
		/*	
		$pdf->SetFont('Arial','B',8);
		$pdf->Cell(20,5,"�REA",0,0,'L',0);
		$pdf->SetFont('Arial','',8);
		$pdf->Cell(45,5,$reg["nr_area"],0,0,'L',0);
		
		$pdf->SetFont('Arial','B',8);
		$pdf->Cell(30,5,"DISPOSITIVO",0,0,'L',0);
		$pdf->SetFont('Arial','',8);
		$pdf->Cell(50,5,$malhas["cd_dispositivo"],0,1,'L',0);
					
		//$pdf->Cell(10,5,"",1,0,'L',0);
		$pdf->SetFont('Arial','B',8);
		$pdf->Cell(20,5,"RACK",0,0,'L',0);
		$pdf->SetFont('Arial','',8);
		$pdf->Cell(45,5,$malhas["nr_rack"],0,0,'L',0);
		
		$pdf->SetFont('Arial','B',8);
		$pdf->Cell(30,5,"SLOT",0,0,'L',0);
		$pdf->SetFont('Arial','',8);
		$pdf->Cell(50,5,$malhas["nr_slot"],0,1,'L',0);
	
		$pdf->SetFont('Arial','B',8);
		$pdf->Cell(20,5,"LOCAL",0,0,'L',0);
		$pdf->SetFont('Arial','',8);
		$pdf->Cell(45,5,$tag,0,0,'L',0);
		
		$pdf->SetFont('Arial','B',8);
		$pdf->Cell(30,5,"CART�O",0,0,'L',0);
		$pdf->SetFont('Arial','',8);
		$pdf->Cell(50,5,$malhas["cd_cartao"],0,1,'L',0);
		
		$pdf->Ln(5);
						
		$pdf->SetFont('Arial','',8);
		*/
		?>
		<br />
		<br />
		<table width="100%" border="1" bordercolor="#333333">
		  <tr>
			<td width="9%"><div align="center"><strong>AREA</strong></div></td>
			<td width="1%">&nbsp;</td>
			<td width="22%"><div align="left">
			  <?= $reg["nr_area"] ?>
		    </div></td>
			<td width="1%">&nbsp;</td>
			<td width="19%"><div align="center"><strong>DISPOSITIVOS</strong></div></td>
			<td width="1%">&nbsp;</td>
			<td width="38%"><div align="left">
			  <?= $malhas["cd_dispositivo"] ?>
		    </div></td>
			<td width="9%">&nbsp;</td>
		  </tr>
		  <tr>
			<td><div align="center"><strong>RACK</strong></div></td>
			<td>&nbsp;</td>
			<td><div align="left">
			  <?= $malhas["nr_rack"] ?>
		    </div></td>
			<td>&nbsp;</td>
			<td><div align="center"><strong>SLOTS</strong></div></td>
			<td>&nbsp;</td>
			<td><div align="left">
			  <?= $malhas["nr_slot"] ?>
		    </div></td>
			<td>&nbsp;</td>
		  </tr>
		  <tr>
			<td><div align="center"><strong>LOCAL</strong></div></td>
			<td>&nbsp;</td>
			<td><div align="left">
			  <?= $tag ?>
		    </div></td>
			<td>&nbsp;</td>
			<td><div align="center"><strong>CART&Atilde;O</strong></div></td>
			<td>&nbsp;</td>
			<td><div align="left">
			  <?= $malhas["cd_cartao"] ?>
		    </div></td>
			<td>&nbsp;</td>
		  </tr>
		</table>
		<br />
		<?
		
		$sql2 = "SELECT * FROM Projetos.enderecos ";
		$sql2 .= "WHERE enderecos.id_slots = '" .$malhas["id_slots"]. "' ";
		$sql2 .= "ORDER BY nr_canal ";
		
		$regend = $db->select($sql2,'MYSQL');
	
		$cabecalho = 1;
		
		while ($enderecos = mysqli_fetch_array($regend))
		{
					
			$sql3 = "SELECT * FROM ".DATABASE.".setores, Projetos.componentes, Projetos.dispositivos, Projetos.malhas, Projetos.processo, Projetos.funcao, Projetos.locais ";
			$sql3 .= "LEFT JOIN Projetos.equipamentos ON (locais.id_equipamento = equipamentos.id_equipamentos) ";
			$sql3 .= "WHERE componentes.id_componente = '" .$enderecos["id_componente"]. "' ";
			$sql3 .= "AND componentes.id_dispositivo = dispositivos.id_dispositivo ";
			$sql3 .= "AND componentes.id_malha = malhas.id_malha ";
			$sql3 .= "AND componentes.id_funcao = funcao.id_funcao ";
			$sql3 .= "AND componentes.id_local = locais.id_local ";			
			$sql3 .= "AND malhas.id_processo = processo.id_processo ";
			$sql3 .= "AND locais.id_disciplina = setores.id_setor "; 
			
			$regcom = $db->select($sql3,'MYSQL');
			
			$componente = mysqli_fetch_array($regcom);			
			
			if($componente["omit_proc"])
			{
				$processo = '';
			}
			else
			{
				$processo = $componente["processo"];
			}
			
			if($componente["funcao"]!="")
			{
				$modificador = " - ". $componente["funcao"];
			}
			else
			{
				if($componente["comp_modif"])
				{
					$modificador = ".".$componente["comp_modif"];
				}
				else
				{
					$modificador = " ";
				}
			}
			
			
			if($db->numero_registros>0)
			{
				$tag = $reg["nr_area"] . " - " .  $processo . $componente["dispositivo"]." - ". $componente["nr_malha"] . $modificador;
			}
			else
			{
				$tag = " ";
			}
			
			//$pdf->Line(70,$pdf->GetY(),180,$pdf->GetY()); // LINHA INFERIOR pagina
			if($cabecalho==1)
			{
				/*					
				$pdf->SetFont('Arial','B',8);
				//$pdf->Cell(45,5,"",0,0,'L',0);
				$pdf->Cell(15,5,"CANAL",1,0,'C',0);
				$pdf->Cell(30,5,"ENDERE�O",1,0,'C',0);
				$pdf->Cell(20,5,"ATRIBUTO",1,0,'C',0);
				$pdf->Cell(35,5,"TAG",1,0,'C',0);
				$pdf->Cell(125,5,"DESCRI��O",1,0,'C',0);
				$pdf->Cell(45,5,"LOCAL",1,1,'C',0);
				$pdf->SetFont('Arial','',8);
				//$pdf->Cell(45,5,"",0,0,'L',0);
				//$pdf->Line(20,$pdf->GetY(),180,$pdf->GetY()); // LINHA INFERIOR pagina
				*/
				?>
				<table width="100%" border="1" bordercolor="#666666">
				  <tr>
					<th width="6%" scope="col">CANAL</th>
					<th width="10%" scope="col">ENDERE&Ccedil;O</th>
					<th width="9%" scope="col">ATRIBUTO</th>
					<th width="11%" scope="col">TAG</th>
					<th scope="col" colspan="3">DESCRI&Ccedil;&Atilde;O</th>
					<th width="15%" scope="col">LOCAL</th>
				  </tr>
				</table> 
				<?
			}
			if($componente["setor"]=='EL�TRICA')
			{
				$sql = "SELECT * FROM Projetos.locais ";
				$sql .= "LEFT JOIN Projetos.equipamentos ON (Projetos.locais.id_equipamento = Projetos.equipamentos.id_equipamentos) ";
				$sql .= "WHERE Projetos.locais.id_local = '".$componente["id_local"]."' ";
				$sql .= "ORDER BY cd_local, nr_sequencia, ds_equipamento ";
				
				$regis = $db->select($sql,'MYSQL');
				
				$cont = mysqli_fetch_array($regis);
				
				$tag1 = $reg["nr_area"]. " - ". $cont["cd_local"]. " ". $cont["nr_sequencia"];
	
			}
			else
			{
				if($componente["setor"]=='MEC�NICA')
				{
					$sql = "SELECT * FROM Projetos.locais ";
					$sql .= "LEFT JOIN Projetos.equipamentos ON (Projetos.locais.id_equipamento = Projetos.equipamentos.id_equipamentos) ";
					$sql .= "WHERE Projetos.locais.id_local = '".$componente["id_local"]."' ";
					$sql .= "ORDER BY cd_local, nr_sequencia, ds_equipamento ";							
					
					$regis = $db->select($sql,'MYSQL');
					
					$cont = mysqli_fetch_array($regis);
					
					$tag1 = $reg["nr_area"]. " - ". $cont["cd_local"]. " ". $cont["nr_sequencia"];
					
				}
				else
				{
					$sql = "SELECT * FROM Projetos.locais ";
					$sql .= "LEFT JOIN Projetos.fluidos ON (Projetos.locais.id_fluido = Projetos.fluidos.id_fluido) ";
					$sql .= "LEFT JOIN Projetos.materiais ON (Projetos.locais.id_material = Projetos.materiais.id_material) ";
					$sql .= "WHERE Projetos.locais.id_local = '".$componente["id_local"]."' ";
					$sql .= "ORDER BY cd_fluido, nr_sequencia, cd_material, nr_diametro ";							
	
					$regis = $db->select($sql,'MYSQL');
					
					$cont = mysqli_fetch_array($regis);
	
					$tag1 = $cont["cd_fluido"]. " - ". $cont["nr_sequencia"]. " - ". $cont["cd_material"]. " - ". $cont["nr_diametro"];
			
				}
			}
			
			/*
			
			$pdf->Cell(15,5,$enderecos["nr_canal"],1,0,'C',0);
			$pdf->Cell(30,5,$enderecos["cd_endereco"],1,0,'C',0);
			$pdf->Cell(20,5,$enderecos["cd_atributo"],1,0,'C',0);
			$pdf->Cell(35,5,$tag,1,0,'C',0);
			$pdf->HCell(125,5,$componente["ds_dispositivo"]."-".$componente["ds_funcao"]."-".$componente["ds_servico"],1,0,'L',0);
			$pdf->HCell(45,5,$tag1,1,1,'C',0);
			*/
			?>
			<table width="100%" border="1" bordercolor="#666666">
			  <tr>
				<td width="6%"><div align="center">
				  <?= $enderecos["nr_canal"] ?>
			    </div></td>
				<td width="10%"><div align="center">
				  <?= $enderecos["cd_endereco"] ?>
			    </div></td>
				<td width="9%"><div align="center">
				  <?= $enderecos["cd_atributo"] ?>
			    </div></td>
				<td width="12%"><div align="center">
				  <?= $tag ?>
			    </div></td>
				<td colspan="3"><?= $componente["ds_dispositivo"]."-".$componente["ds_funcao"]."-".$componente["ds_servico"] ?></td>
				<td width="16%"><?= $tag1 ?></td>
			  </tr>
			</table>

			<?
			
			$cabecalho = 0;
			
		}

	}
}

?>
</body>
</html> 