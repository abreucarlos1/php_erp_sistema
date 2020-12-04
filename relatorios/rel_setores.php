<?php
/*
		Relatório Lista de equipes
		
		Criado por Carlos Abreu  
		
		local/Nome do arquivo:
		../relatorios/rel_setores.php
	
		Versão 0 --> VERSÃO INICIAL : 10/03/2015 - Carlos Abreu
		Versão 1 --> Inclusão dos campos reg_del nas consultas - 20/11/2017 - Carlos Abreu		
*/	
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));

require_once(INCLUDE_DIR."include_pdf.inc.php");

class PDF extends FPDF
{
//Page header
function Header()
{    
	$this->Image(DIR_IMAGENS.'logo_pb.png',26,16,40);
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
	$this->SetFont('Arial','B',8);	
	$this->Cell(40,5,"CÓDIGO",0,0,'L',0);
	$this->Cell(120,5,"DESCRIÇÃO",0,1,'L',0);
	$this->SetLineWidth(0.5);
	$this->SetDrawColor(0,0,0);
	$this->Line(25,40,195,40);
	$this->SetXY(25,45);	
}

//Page footer
function Footer()
{

}
}

$pdf=new PDF('p','mm',A4);
$pdf->SetMargins(25,15);
$pdf->SetLineWidth(0.3);

$pdf->titulo="LISTA DE EQUIPES";
$pdf->setor="ADM";
$pdf->codigodoc="02"; //"00";
$pdf->codigo=11; //Numero OS

$pdf->emissao=date("d/m/Y");
$pdf->versao_documento="0";

$pdf->AliasNbPages();

$pdf->AddPage();

$db = new banco_dados;

$sql = "SELECT * FROM ".DATABASE.".setores, ".DATABASE.".funcionarios ";
$sql .= "WHERE funcionarios.id_setor = setores.id_setor ";
$sql .= "AND setores.reg_del = 0 ";
$sql .= "AND funcionarios.reg_del = 0 ";
$sql .= "AND funcionarios.situacao = 'ATIVO' ";
$sql .= "GROUP BY setores.id_setor ";
$sql .= "ORDER BY setores.id_setor ";

$db->select($sql,'MYSQL',true);	

foreach ($db->array_select as $regcc)
{	
	$pdf->SetFont('Arial','',8);
	
	$pdf->Cell(40,5,sprintf("%010d",$regcc["id_setor"]),0,0,'L',0);

	$pdf->Cell(120,5,$regcc["setor"],0,1,'L',0);
}

$pdf->Output('LISTA_EQUIPES_'.date('dmYhis').'.pdf', 'D');
?> 