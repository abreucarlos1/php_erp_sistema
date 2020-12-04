<?php
/*
		Relatorio Aniversariantes
		
		Criado por Carlos Abreu 
		
		local/Nome do arquivo:
		../rh/relatorios/rel_aniversariantes.php
		
		Versão 0 --> VERSÃO INICIAL - 04/05/2016
		Versão 1 --> Inclusão dos campos reg_del nas consultas - 27/11/2017 - Carlos Abreu	
*/
require_once(implode(DIRECTORY_SEPARATOR,array('..','..','config.inc.php')));

require_once(INCLUDE_DIR."include_pdf.inc.php");

require_once(INCLUDE_DIR."antiInjection.php");

class PDF extends FPDF
{

function chkbox($checado = false)
{
	$x1 = $this->GetX();
	$y1 = $this->GetY();
	
	$x2 = $x1+3;
	$y2 = $y1+3;
	
	$this->SetLineWidth(0.1);

	$this->SetDrawColor(0,0,0);
	
	$this->Line($x1,$y1,$x2,$y1); //sup
	$this->Line($x1,$y2,$x2,$y2); //inf
	$this->Line($x1,$y1,$x1,$y2); //esq
	$this->Line($x2,$y1,$x2,$y2); //dir
	
	if($checado)
	{
		$this->SetLineWidth(0.5);
		
		$this->Line($x1+0.5,$y1+1.3,$x1+1.3,$y1+2.3);
		$this->Line($x1+1.3,$y1+2.3,$x2-0.3,$y1+0.3);
	}
	
	$this->SetY($y1-0.8);
	$this->SetX($x2-1);
	$this->Cell(2,3,' ',0,0,'C',0);
	$this->SetLineWidth(0.2);
}

//Page header
function Header()
{
	$this->Image(DIR_IMAGENS.'logo_pb.png',10,16,40);
	$this->Ln(1);
	$this->SetFont('Arial','',6);
	$this->SetFont('Arial','B',12);
	$this->Cell(271,8,$this->Titulo(),0,1,'C',0);
	$this->SetXY(10,40);
}

//Page footer
function Footer()
{

}
}


$pdf=new PDF('L','mm',A4);
$pdf->SetAutoPageBreak(true,25);
$pdf->SetMargins(10,15);
$pdf->SetLineWidth(0.2);
$pdf->SetDrawColor(0,0,0);

$pdf->titulo="ANIVERSARIANTES DO MÊS";

$data_ini = $_POST["mes"] . "-" . "01";
$datafim = $_POST["mes"] . "-" . "31";

$pdf->AliasNbPages();
$pdf->AddPage();

$db = new banco_dados;

$pdf->SetFont('Arial','B',10);
$pdf->Cell(80,5,'Colaborador',0,0,'L',0);
$pdf->Cell(30,5,'Data',0,0,'L',0);
$pdf->Cell(80,5,'Local',0,1,'C',0);
$pdf->Ln();

$pdf->SetFont('Arial','',10);

$sql = "SELECT * FROM ".DATABASE.".funcionarios, ".DATABASE.".local ";
$sql .= "WHERE RIGHT(data_nascimento,5) BETWEEN '".$data_ini."' AND '".$datafim."' ";
$sql .= "AND funcionarios.reg_del = 0 ";
$sql .= "AND local.reg_del = 0 ";
$sql .= "AND funcionarios.situacao = 'ATIVO' ";
$sql .= "AND local.id_local = funcionarios.id_local ";
$sql .= "ORDER BY RIGHT(funcionarios.data_nascimento,5), funcionarios.funcionario ";

$db->select($sql,'MYSQL',true);

foreach ($db->array_select as $regs)
{
	$data = explode("-",$regs["data_nascimento"]);
	
	$pdf->Cell(80,5,$regs["funcionario"],1,0,'L',0);
	$pdf->Cell(30,5,$data[2].".".$data[1],1,0,'L',0);
	$pdf->Cell(80,5,$regs["descricao"],1,1,'C',0);
}

$pdf->Output('ANIVERSARIANTES_'.date('dmYhis').'.pdf', 'D');
?> 