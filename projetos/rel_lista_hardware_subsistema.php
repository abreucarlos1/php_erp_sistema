<?php
/*
		data de cria��o: 09/05/2006
		
		Versão 0 --> VERSÃO INICIAL
		Versão 1 --> Retomada do uso - Simioli / alterado por Carlos Abreu - 10/03/2016
	
*/	

define('FPDF_FONTPATH','../includes/font/');
require("../includes/fpdf.php");
require("../includes/tools.inc.php");
include("../includes/conectdb.inc.php");

class PDF extends FPDF
{
//Page header
function Header()
{

	$this->Image($this->Logotipocliente(),13,23,60,12);

	//$this->Line(20,27.5,70,27.5);
	
	//$this->Image("../logotipos/logo_horizontal.jpg",23,30,45,7.5);
    //Arial bold 12
    //Titulo(Largura,Altura,Texto,Borda,Quebra de Linha,Alinhamento,Preenchimento
	//$this->Ln(1);
	
	$this->SetFont('Arial','',6);
	//Informa��es do Centro de Custo
	$this->Cell(66,8,'',0,0,'L',0); // C�LULA LOGOTIPO 146
	$this->SetFont('Arial','B',12);
	$this->Cell(140,8,$this->Cliente(),1,1,'C',0); // C�LULA CLIENTE
	
	$this->Image("../logotipos/logo_horizontal.jpg",219,17,59,10);
	
	$this->SetFont('Arial','B',10);
	$this->Cell(66,5.5,'',0,0,'L',0); // C�LULA LOGOTIPO 
	$this->HCell(140,5.5,$this->Subsistema() . " / " .$this->Area() ,1,1,'C',0); // C�LULA AREA / SUBSISTEMA

	$this->Cell(66,5.5,'',0,0,'L',0); // C�LULA LOGOTIPO
	$this->SetFont('Arial','B',10);
	$this->Cell(140,5.5,"LISTA DE HARDWARE",1,0,'C',0); // C�LULA COMPONENTE
	
	
	$X = $this->GetX();
	$this->Cell(64,5.5,'',1,0,'C',0);
	$this->SetX($X);
	$this->SetFont('Arial','',5);
	$this->Cell(5,5.5,'N�: ',0,0,'L',0);
	$this->SetFont('Arial','B',8);
	$this->Cell(55,5.5,$this->Numdvm(),0,1,'C',0);

	$this->Cell(66,5.5,'',0,0,'L',0); // C�LULA LOGOTIPO

	$this->SetFont('Arial','B',10);
	$this->HCell(140,5.5,$this->Titulo(),1,0,'C',0);
	
	$X = $this->GetX();
	$this->Cell(30,5.5,'',1,0,'C',0);
	$this->SetFont('Arial','',5);
	$this->SetX($X);
	$this->Cell(10,5.5,'DATA: ',0,0,'L',0);
	$this->SetFont('Arial','B',6);
	$this->Cell(20,5.5,$this->Emissao(),0,0,'L',0);
	
	$X = $this->GetX();
	$this->Cell(14,5.5,'',1,0,'C',0);
	$this->SetFont('Arial','',5);
	$this->SetX($X);
	$this->Cell(6,5.5,'REV: ',0,0,'L',0);
	$this->SetFont('Arial','B',6);
	$this->Cell(8,5.5,$this->Revisao(),0,0,'R',0);
	
	
	$X = $this->GetX();
	$this->Cell(20,5.5,'',1,0,'C',0);
	$this->SetFont('Arial','',4);
	$this->SetX($X);
	$this->Cell(8,5.5,'FL: ',0,0,'L',0);
	$this->SetFont('Arial','B',6);
	$this->Cell(10,5.5,$this->PageNo().' / {nb}',0,1,'R',0);
	
	$this->SetFont('Arial','B',8);
	$this->HCell(66,5.5,$this->unidade(),1,0,'C',0); // C�LULA LOGOTIPO
	$this->HCell(140,5.5,$this->Titulo2(),1,0,'C',0);

	$X = $this->GetX();
	$this->Cell(64,5.5,'',1,0,'C',0);
	$this->SetFont('Arial','',5);
	$this->SetX($X);
	$this->Cell(17,5.5,'N� CLIENTE: ',0,0,'L',0);
	$this->SetFont('Arial','B',8);
	$this->Cell(30,5.5,$this->Numcliente(),0,1,'C',0);	
	
	$this->SetFont('Arial','',9);
    //Seta a espessura da linha
	$this->SetLineWidth(0.5);
	//Seta a cor da linha
	$this->SetDrawColor(0,0,0);

	/*
	COMENTADO POR OT�VIO - LINHAS ANTERIORES � ALTERA��O DA MARGEM - 20/07/2006
	$this->Line(20,15,280,15); // LINHA SUPERIOR
	$this->Line(20,45,280,45); // LINHA INFERIOR
	$this->Line(20,15,20,45); // LINHA ESQUERDA
		
	//$this->Line(20,15,20,280); // LINHA ESQUERDA
	//$this->Line(20,280,195,280); // LINHA INFERIOR pagina
	$this->Line(280,15,280,45); // LINHA DIREITA
	//$this->Line(195,15,195,280); // LINHA DIREITA 
	$this->Line(80,15,80,45); // LINHA LOGOTIPO aqui
	$this->Line(220,15,220,45); // LINHA DOC / FOLHA
	*/

	//LINHAS NOVAS - 20/07/2006
	$this->Line(10,15,280,15); // LINHA SUPERIOR
	$this->Line(10,45,280,45); // LINHA INFERIOR
	$this->Line(10,15,10,45); // LINHA ESQUERDA
		
	//$this->Line(20,15,20,280); // LINHA ESQUERDA
	//$this->Line(20,280,195,280); // LINHA INFERIOR pagina
	$this->Line(280,15,280,45); // LINHA DIREITA
	//$this->Line(195,15,195,280); // LINHA DIREITA 
	$this->Line(76,15,76,45); // LINHA LOGOTIPO aqui
	$this->Line(216,15,216,45); // LINHA DOC / FOLHA
	//AT� AQUI
	
	$this->SetLineWidth(0,5);
	
	$this->Ln(2);
	
	$this->SetXY(10,48);
}

//Page footer
function Footer()
{ 
}
}

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

session_cache_limiter('private');
session_start();

$sql_rev0 = "SELECT * FROM ".DATABASE.".revisao_cliente ";
$sql_rev0 .= "WHERE id_os = '".$_SESSION["id_os"]."' ";
$sql_rev0 .= "AND tipodoc = '".$_POST["relatorio"]."' ";
//$sql_rev0 .= "AND numero_cliente = '".$_POST["numero_cliente"]."' ";
$sql_rev0 .= "AND numeros_interno = '".$_POST["numeros_interno"]."' ";
$sql_rev0 .= "ORDER BY versao_documento ASC LIMIT 1 ";

$reg_rev0 = $db->select($sql_rev0,'MYSQL');

$revis0 = mysqli_fetch_array($reg_rev0);

$sql = "SELECT * FROM ".DATABASE.".caminho_docs, ".DATABASE.".OS ";
$sql .= "WHERE caminho_docs.id_os = '".$_SESSION["id_os"]."' ";
$sql .= "AND caminho_docs.id_os = OS.id_os ";

$registro = $db->select($sql,'MYSQL');

$path1 = mysqli_fetch_array($registro);

$path = str_replace('\\','/',$path1["caminho_pasta"]);

$caminho = "/home/dt_arqtec/".$path."/".$path1["os"]."-DOCS_EMITIDOS/".$path1["os"]."-".$abrdisc."/";

$pasta = explode("/",$_SERVER['SCRIPT_FILENAME']);

//Instanciation of inherited class
$pdf=new PDF('L','mm',A4);
$pdf->SetAutoPageBreak(false,10);
$pdf->SetMargins(10,15);
$pdf->SetLineWidth(0.2);

$sql1 = "SELECT OS, logotipo, OS.descricao AS osdesc, empresas.empresa, unidades.descricao AS unidade FROM ".DATABASE.".OS, ".DATABASE.".empresas, ".DATABASE.".unidade ";
$sql1 .= "WHERE id_os = '" . $_SESSION["id_os"] . "' ";
$sql1 .= "AND OS.id_empresa_erp = empresas.id_empresa_erp ";
$sql1 .= "AND empresas.id_unidade = unidades.id_unidade ";

$registro1 = $db->select($sql1,'MYSQL');

$reg1 = mysqli_fetch_array($registro1);

$sql = "SELECT * FROM Projetos.area, Projetos.subsistema ";
$sql .= "WHERE subsistema.id_subsistema = '" .$_POST["id_subsistema"]. "' ";
$sql .= "AND area.id_area = subsistema.id_area ";

$registro = $db->select($sql,'MYSQL');

$reg = mysqli_fetch_array($registro);

//Seta o cabeçalho
//$pdf->departamento="ENGENHARIA";

$pdf->cliente=$reg1["empresa"]; // Cliente
$pdf->subsistema = $reg["ds_divisao"]; // DIVIS�O
$pdf->area = $reg["ds_area"]; // �REA
$pdf->logotipocliente = $reg1["logotipo"]; // logotipo Cliente

$pdf->numeros_interno = $_POST["numeros_interno"];

$pdf->numero_cliente = $_POST["numero_cliente"];

$pdf->unidade= $reg1["unidade"];

$pdf->versao_documento = $_POST["versao_documento"];

$pdf->titulo = $reg["subsistema"];

$pdf->titulo2 = $reg1["osdesc"];

$pdf->emissao=date('d/m/Y');
//$pdf->versao_documento=$data_ini . " � " . $datafim;

$pdf->AliasNbPages();
$pdf->AddPage('L');

$flag = 0;

$pdf->SetLineWidth(0.5);

$pdf->Line(10,15,10,195); // LINHA ESQUERDA
$pdf->Line(10,195,280,195); // LINHA INFERIOR pagina
$pdf->Line(280,15,280,195); // LINHA DIREITA
$pdf->SetLineWidth(0.2);

// P�gina de rosto abaixo
$pdf->SetXY(10,70);

$pdf->SetFont('Arial','BU',20);
$pdf->Cell(280,10,"LISTA DE HARDWARE",0,1,'C',0);
$pdf->SetFont('Arial','BU',16);
$pdf->Cell(280,10,$disciplina,0,1,'C',0);
$pdf->Ln(5);
$pdf->SetFont('Arial','B',16);
$pdf->Cell(280,10, $reg["ds_divisao"] ,0,1,'C',0);
$pdf->Ln(5);
$pdf->Cell(280,10, $reg["ds_area"] ,0,1,'C',0);
$pdf->Ln(5);
$pdf->Cell(280,10, $reg["subsistema"] ,0,1,'C',0);

//REVIS�ES
$pdf->SetFont('Arial','B',8);

$y = 155;

$pdf->SetXY(25,$y);
$pdf->SetFont('Arial','B',8);
$pdf->Cell(50,4,'CONTROLE DE REVIS�ES',0,1,'L',0);
$pdf->SetFont('Arial','',6);

$pdf->Ln(1);

$sql_rev = "SELECT * FROM ".DATABASE.".revisao_cliente ";
$sql_rev .= "WHERE id_os = '".$_SESSION["id_os"]."' ";
$sql_rev .= "AND tipodoc = '".$_POST["relatorio"]."' ";
$sql_rev .= "AND versao_documento NOT LIKE '".$revis0["versao_documento"]."' ";
//$sql_rev .= "AND numero_cliente = '".$_POST["numero_cliente"]."' ";
$sql_rev .= "ORDER BY versao_documento DESC LIMIT 5 ";

$reg_rev = $db->select($sql_rev,'MYSQL');

$numregs = 4 - $db->numero_registros;

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

while($revis = mysqli_fetch_array($reg_rev))
{
	$sql_exe = "SELECT abreviacao FROM ".DATABASE.".funcionarios ";
	$sql_exe .= "WHERE id_funcionario = '".$revis["id_executante"]."' ";
	
	$regexe = $db->select($sql_exe,'MYSQL');
	
	$executante = mysqli_fetch_array($regexe);
	
	$sql_ver = "SELECT abreviacao FROM ".DATABASE.".funcionarios ";
	$sql_ver .= "WHERE id_funcionario = '".$revis["id_verificador"]."' ";
	
	$regver = $db->select($sql_ver,'MYSQL');
	
	$verificador = mysqli_fetch_array($regver);
	
	$sql_apr = "SELECT abreviacao FROM ".DATABASE.".funcionarios ";
	$sql_apr .= "WHERE id_funcionario = '".$revis["id_aprovador"]."' ";
	
	$regapr = $db->select($sql_apr,'MYSQL');
	
	$aprovador = mysqli_fetch_array($regapr);
	
	$y += 4;
	
	$pdf->SetXY(25,$y);
	$pdf->Cell(10,4,$revis["versao_documento"],1,0,'C',0);
	$pdf->Cell(70,4,$revis["alteracao"],1,0,'C',0);
	$pdf->Cell(20,4,mysql_php($revis["data_emissao"]),1,0,'C',0);
	$pdf->Cell(20,4,$executante["abreviacao"],1,0,'C',0);
	$pdf->Cell(20,4,$verificador["abreviacao"],1,0,'C',0);
	$pdf->Cell(20,4,$aprovador["abreviacao"],1,1,'C',0);
	
}


$sql_exe0 = "SELECT abreviacao FROM ".DATABASE.".funcionarios ";
$sql_exe0 .= "WHERE id_funcionario = '".$revis0["id_executante"]."' ";

$regexe0 = $db->select($sql_exe0,'MYSQL');

$contexe = mysqli_fetch_array($regexe0);

$executante0 = $contexe["abreviacao"];

$sql_ver0 = "SELECT abreviacao FROM ".DATABASE.".funcionarios ";
$sql_ver0 .= "WHERE id_funcionario = '".$revis0["id_verificador"]."' ";

$regver0 = $db->select($sql_ver0,'MYSQL');

$contver = mysqli_fetch_array($regver0);

$verificador0 = $contver["abreviacao"];

$sql_apr0 = "SELECT abreviacao FROM ".DATABASE.".funcionarios ";
$sql_apr0 .= "WHERE id_funcionario = '".$revis0["id_aprovador"]."' ";

$regapr0 = $db->select($sql_apr0,'MYSQL');

$contapr = mysqli_fetch_array($regapr0);

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


$pdf->AddPage();

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


$sql1 = "SELECT * FROM Projetos.area, Projetos.subsistema, Projetos.locais, Projetos.racks, Projetos.devices, Projetos.slots, Projetos.cartoes, ".DATABASE.".setores ";
$sql1 .= "WHERE locais.id_area = area.id_area ";
$sql1 .= "AND locais.id_local = racks.id_local ";
$sql1 .= "AND racks.id_devices = devices.id_devices ";
$sql1 .= "AND racks.id_racks = slots.id_racks ";
$sql1 .= "AND slots.id_cartoes = cartoes.id_cartoes ";
$sql1 .= "AND locais.id_disciplina = setores.id_setor ";
$sql1 .= "AND area.id_area = subsistema.id_area ";
$sql1 .= "AND subsistema.id_subsistema = '".$_POST["id_subsistema"]."' ";
$sql1 .= $filtro;
$sql1 .= "ORDER BY nr_rack, nr_slot ";	

$regsub = $db->select($sql1,'MYSQL');

$flag = 0;

while ($subsistema = mysqli_fetch_array($regsub))
{
	if($pdf->GetY()>180)
	{
		$pdf->AddPage();
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
		$pdf->Cell(90,4,$subsistema["ds_servico"],0,1,'L',0);
		$pdf->SetFont('Arial','',8);
	}
	
	if($subsistema["setor"]=='EL�TRICA')
	{
		$sql = "SELECT * FROM Projetos.locais ";
		$sql .= "LEFT JOIN Projetos.equipamentos ON (Projetos.locais.id_equipamento = Projetos.equipamentos.id_equipamentos) ";
		$sql .= "WHERE Projetos.locais.id_local = '".$subsistema["id_local"]."' ";
		$sql .= "ORDER BY cd_local, nr_sequencia, ds_equipamento ";
		
		$regis = $db->select($sql,'MYSQL');
		
		$cont = mysqli_fetch_array($regis);
		
		$tag = $subsistema["nr_area"]. " - ". $cont["cd_local"]. " ". $cont["nr_sequencia"];

	}
	else
	{
		if($subsistema["setor"]=='MEC�NICA')
		{
			$sql = "SELECT * FROM Projetos.locais ";
			$sql .= "LEFT JOIN Projetos.equipamentos ON (Projetos.locais.id_equipamento = Projetos.equipamentos.id_equipamentos) ";
			$sql .= "WHERE Projetos.locais.id_local = '".$subsistema["id_local"]."' ";
			$sql .= "ORDER BY cd_local, nr_sequencia, ds_equipamento ";							
			
			$regis = $db->select($sql,'MYSQL');
			
			$cont = mysqli_fetch_array($regis);
			
			$tag = $subsistema["nr_area"]. " - ". $cont["cd_local"]. " ". $cont["nr_sequencia"];
			
		}
		else
		{
			$sql = "SELECT * FROM Projetos.locais ";
			$sql .= "LEFT JOIN Projetos.fluidos ON (Projetos.locais.id_fluido = Projetos.fluidos.id_fluido) ";
			$sql .= "LEFT JOIN Projetos.materiais ON (Projetos.locais.id_material = Projetos.materiais.id_material) ";
			$sql .= "WHERE Projetos.locais.id_local = '".$subsistema["id_local"]."' ";
			$sql .= "ORDER BY cd_fluido, nr_sequencia, cd_material, nr_diametro ";							

			$regis = $db->select($sql,'MYSQL');
			
			$cont = mysqli_fetch_array($regis);

			$tag = $cont["cd_fluido"]. " - ". $cont["nr_sequencia"]. " - ". $cont["cd_material"]. " - ". $cont["nr_diametro"];
	
		}
	}

	//$pdf->Cell(180,20,$sql,0,0,'L',0);
	
	$pdf->HCell(33,4,$tag ,0,0,'L',0);
	$pdf->HCell(50,4,$subsistema["cd_dispositivo"],0,0,'L',0);
	$pdf->HCell(40,4,$subsistema["nr_rack"],0,0,'L',0);
	$pdf->HCell(40,4,$subsistema["nr_slot"],0,0,'L',0);
	$pdf->HCell(30,4,$subsistema["nr_capacidade"],0,0,'L',0);
	$pdf->HCell(35,4,$subsistema["cd_cartao"],0,0,'L',0);
	$pdf->HCell(30,4,$subsistema["ds_cartao"],0,1,'L',0);

	$pdf->Ln(2);		
		
}

$pdf->Output();

if($_POST["emissao"]=='1')
{
	$pdf->Output('../projetos/pdftemp/' . $_POST["numeros_interno"] .'_'. $_POST["numero_cliente"] .'_'.$_POST["versao_documento"] . '.pdf',F);
	
	copy('/'.$pasta[1].'/'.$pasta[2].'/'.$pasta[3].'/'.$pasta[4].'/pdftemp/'. $_POST["numeros_interno"] .'_'.$_POST["numero_cliente"] .'_'.$_POST["versao_documento"] . '.pdf',$caminho.$_POST["numeros_interno"] .'_'.$_POST["numero_cliente"] .'_'.$_POST["versao_documento"].'.pdf');
}


?> 