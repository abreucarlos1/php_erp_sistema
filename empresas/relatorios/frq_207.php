<?php
require_once(implode(DIRECTORY_SEPARATOR,array('..','..','config.inc.php')));

require_once(INCLUDE_DIR."include_pdf.inc.php");

class PDF extends FPDF
{
var $titulo;
var $relevancia;

function Header()
{
	//Logo
	$this->Image(DIR_IMAGENS.'logo_pb.png',26,16,40);
	
	$this->Ln(2);
	$this->SetFont('Arial','B',12);
	$this->Cell(32,5,'',0,0,'C',0);
	$this->Cell(500,5,$this->titulo . " - ".$this->relevancia,0,0,'C',0);
	$this->SetFont('Arial','',8);
	$this->Cell(25,5,date('d/m/Y'),0,1,'R',0);
	
	$this->SetFont('Arial','B',8);
	$this->Cell(32,5,'',0,0,'C',0);
	$this->Cell(500,5,$this->periodo,0,0,'C',0);
	$this->SetFont('Arial','',8);
	$this->Cell(25,5,'',0,1,'R',0);
	$this->SetFont('Arial','',8);
	
	$this->Ln(5);
	
	$this->SetFont('Arial','B',8);
	//1ª LINHA
	$this->Cell(50,5,"",1,0,'C',0);
	$this->Cell(135,5,"EMPRESA",1,0,'C',0);
	$this->Cell(290,5,"CONTATO",1,0,'C',0);
	$this->Cell(85,5,"SECRETARIA",1,1,'C',0);
	//2ª LINHA
	$this->Cell(50,5,"SETOR",1,0,'L',0);
	$this->Cell(75,5,"NOME",1,0,'L',0);
	$this->Cell(60,5,"SITE",1,0,'L',0);
	
	$this->Cell(75,5,"NOME",1,0,'L',0);
	$this->Cell(55,5,"CARGO",1,0,'L',0);
	$this->Cell(25,5,"TELEFONE",1,0,'L',0);
	$this->Cell(25,5,"FAX",1,0,'L',0);
	$this->Cell(25,5,"CELULAR",1,0,'L',0);
	$this->Cell(65,5,"E-MAIL",1,0,'L',0);
	$this->Cell(20,5,"DATA NASC.",1,0,'L',0);
	
	$this->Cell(60,5,"NOME",1,0,'L',0);
	$this->Cell(25,5,"TELEFONE",1,1,'L',0);
	$this->SetFont('Arial','',8);
	
}

function Footer()
{
	$this->SetFont('Arial','',8);
	$this->SetY(410);
	$this->Cell(32,5,'FRQ 207',0,0,'L',0);
	$this->Cell(500,5,'',0,0,'C',0);
	$this->SetFont('Arial','',8);
	$this->Cell(25,5,$this->PageNo().' de {nb}',0,0,'R',0);	
	
}
}

$format=array(420,594); //A2

//Instanciation of inherited class
$pdf=new PDF('L','mm',$format);
$pdf->SetAutoPageBreak(true,25);
$pdf->SetMargins(25,15);
$pdf->SetLineWidth(0.15);
$pdf->SetTitle("RELATÓRIO DE CLIENTES POR RELEVÂNCIA - FRQ 207");

$filtro_rel = FALSE;

switch($_POST["escolha_relevancia"])
{
	case "1": $pdf->relevancia="BAIXA";
			  $filtro_rel = 1;	
	break;
	
	case "2": $pdf->relevancia="MÉDIA";
			  $filtro_rel = 2;	
	break;

	case "3": $pdf->relevancia="ALTA";
			  $filtro_rel = 3;	
	break;
	
	default: $pdf->relevancia="TODOS";
}

switch($_POST["escolha_decisao"])
{
	case "0": 
			  $filtro_des = 0;	
	break;

	case "1": 
			  $filtro_des = 1;	
	break;
	
	default: $filtro_des = "";
}

$pdf->titulo="RELATÓRIO DE CLIENTES POR RELEVÂNCIA";

$pdf->AliasNbPages();
$pdf->AddPage();

$db = new banco_dados;

$sql = "SELECT * FROM ".DATABASE.".contatos, ".DATABASE.".empresas ";
$sql .= "LEFT JOIN ".DATABASE.".segmentos ON (empresas.id_segmento = segmentos.id_segmento) ";
$sql .= "LEFT JOIN ".DATABASE.".unidades ON (empresas.id_unidade = unidades.id_unidade) ";
$sql .= "WHERE empresas.status = 'CLIENTE' ";
$sql .= "AND contatos.id_empresa = empresas.id_empresa ";

if($filtro_rel)
{
	$sql .= "AND empresas.relevancia = '".$filtro_rel."' ";
}

if($filtro_des!=="")
{
	$sql .= "AND contatos.decisao = '".$filtro_des."' ";
}

$sql .= "ORDER BY segmentos.segmentos, empresas.relevancia, empresas.empresa, unidades.descricao ";


$db->select($sql,'MYSQL',true);

if ($db->erro != '')
{
	exit("Não foi possível realizar a seleção.");
}

foreach ($db->array_select as $regs)
{
	//linha separação
	if($setor!=$regs["id_segmento"] || $empresa!=$regs["id_empresa"])
	{
		//$pdf->Ln(3);
	}
	
	//imprime 1 vez a atuação
	if($setor!=$regs["id_segmento"])
	{
		$pdf->Ln(5);
		$pdf->HCell(50,5,$regs["segmentos"],1,0,'L',0);
	}
	else
	{
		$pdf->HCell(50,5,"",1,0,'L',0);
	}
	
	$setor = $regs["id_segmento"];
	
	// imprime 1 vez a empresa
	if($empresa!=$regs["id_empresa"])
	{
		$pdf->HCell(75,5,$regs["empresa"] ." - ".$regs["unidade"],1,0,'L',0);
		
		$pdf->HCell(60,5,$regs["homepage"],1,0,'L',0,$regs["homepage"]);		
	}
	else
	{
		$pdf->HCell(135,5,"",1,0,'L',0);
	}
	
	$empresa = $regs["id_empresa"];
	
	$pdf->HCell(75,5,$regs["nome_contato"],1,0,'L',0);
	
	$pdf->HCell(55,5,$regs["cargo"],1,0,'L',0);
	
	$pdf->HCell(25,5,$regs["telefone"],1,0,'L',0);
	
	$pdf->HCell(25,5,$regs["fax_contato"],1,0,'L',0);
	
	$pdf->HCell(25,5,$regs["celular"],1,0,'L',0);
	
	$pdf->HCell(65,5,$regs["email"],1,0,'L',0,$regs["email"]);
	
	$pdf->HCell(20,5,mysql_php($regs["data_nascimento"]),1,0,'L',0);
	
	$pdf->HCell(60,5,$regs["nome_secretaria"],1,0,'L',0);
	
	$pdf->HCell(25,5,$regs["telefone_secretaria"],1,1,'L',0);
	
	
}

$pdf->Output('RELATORIO_RELEVANCIA_'.date('dmYhis').'.pdf', 'D');

?> 