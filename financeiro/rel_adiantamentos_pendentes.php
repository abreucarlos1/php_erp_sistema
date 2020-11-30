<?php
	
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));

require_once(INCLUDE_DIR."include_pdf.inc.php");

class PDF extends FPDF
{
//Page header
function Header()
{
	//Logo
    $this->Image('../images/logo_pb.jpg',26,16,40);
    //Titulo(Largura,Altura,Texto,Borda,Quebra de Linha,Alinhamento,Preenchimento
	$this->Ln(1);
	$this->SetFont('Arial','',6);
	$this->Cell(146,4,'',0,0,'L',0);
	$this->Cell(12,4,'DOC:',0,0,'L',0);
	$this->Cell(12,4,$this->setor() . '-' . $this->codigodoc() . '-' .$this->codigo(),0,1,'R',0);
	$this->SetLineWidth(0.3);
	$this->Line(172,19.5,195,19.5);
	$this->Cell(158,4,'EMISSÃO:',0,0,'R',0); //aqui
	$this->Cell(12,4,$this->Emissao(),0,1,'R',0); //aqui
	$this->Line(172,23.5,195,23.5);
	$this->Cell(146,4,'',0,0,'L',0);
	$this->Cell(12,4,'FOLHA:',0,0,'L',0);
	$this->Cell(12,4,$this->PageNo().' de {nb}',0,0,'R',0);
	$this->Line(172,27.5,195,27.5);
	$this->Ln(8);
	$this->SetFont('Arial','B',12);
	$this->Cell(170,4,$this->Titulo(),0,1,'R',0);
	$this->SetFont('Arial','B',8);
	$this->SetFont('Arial','',9);
    //Seta a espessura da linha
	$this->SetLineWidth(1);
	//Seta a cor da linha
	$this->SetDrawColor(0,0,0);
	$this->Line(25,40,195,40);
	$this->SetXY(25,45);
	
}

//Page footer
function Footer()
{

}
}

$db = new banco_dados;

//Instanciation of inherited class
$pdf=new PDF('p','mm',A4);
$pdf->SetMargins(25,15);
$pdf->SetLineWidth(0.3);

$pdf->titulo="RELATÓRIO DE ADIANTAMENTOS PENDENTES";

$pdf->setor="FIN";
$pdf->codigodoc="01"; //"00";
$pdf->codigo=11; //Numero OS
$pdf->setorextenso=$setor; //"INFORMATICA"
$pdf->emissao=date('d/m/Y');
$pdf->versao_documento="0";

$pdf->AliasNbPages();
$pdf->AddPage();


$pdf->SetFont('Arial','B',8);
$pdf->Cell(10,5,"Nº",0,0,'L',0);
$pdf->Cell(20,5,"DATA",0,0,'L',0);
$pdf->Cell(58,5,"SOLICITANTE",0,0,'L',0);
$pdf->Cell(58,5,"RESPONSÁVEL",0,0,'L',0);
$pdf->Cell(25,5,"VALOR",0,1,'R',0);
$pdf->Ln(3);

$sql = "SELECT * FROM ".DATABASE.".requisicao_despesas, ".DATABASE.".adiantamento_funcionario, ".DATABASE.".funcionarios ";
$sql .= "WHERE requisicao_despesas.id_requisicao_despesa = adiantamento_funcionario.id_requisicao_despesa ";
$sql .= "AND requisicao_despesas.id_funcionario = funcionarios.id_funcionario ";
$sql .= "AND adiantamento_funcionario.status_adiantamento = 0 ";
$sql .= "ORDER BY requisicao_despesas.id_requisicao_despesa ";

$reg = $db->select($sql,'MYSQL');	

$total = 0;

while ($regs = mysqli_fetch_assoc($reg))
{

	$sql1 = "SELECT * FROM ".DATABASE.".funcionarios ";
	$sql1 .= "WHERE funcionarios.id_funcionario = '".$regs["responsavel_despesas"]."' ";
	
	$reg1 = $db->select($sql1,'MYSQL');	
	
	$regs1 = mysqli_fetch_assoc($reg1);

	$pdf->SetFont('Arial','',7);
	$pdf->Cell(10,5,$regs["id_requisicao_despesa"],0,0,'L',0);
	$pdf->Cell(20,5,mysql_php($regs["data_requisicao"]),0,0,'L',0);
	$pdf->Cell(58,5,$regs["funcionario"],0,0,'L',0);
	$pdf->Cell(58,5,$regs1["funcionario"],0,0,'L',0);
	$pdf->Cell(25,5,number_format($regs["valor_adiantamento"],2,',','.'),0,1,'R',0);
	
	$total += $regs["valor_adiantamento"];

}

$pdf->Ln(3);
$pdf->SetFont('Arial','B',7);
$pdf->Cell(146,5,"TOTAL: ",0,0,'R',0);
$pdf->SetFont('Arial','',7);	
$pdf->Cell(25,5,number_format($total,2,',','.'),0,1,'R',0);	

$pdf->Output();
?> 