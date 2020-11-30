<?php

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));

require_once(INCLUDE_DIR."include_pdf.inc.php");
// RELAT�RIO DE CLIENTE / OS

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
	$this->Cell(12,4,$this->setor() . '-' . $this->codigodoc() . '-' .$this->codigo(),0,1,'R',0); //setor - C�digo Documento - Sequencia
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
	$this->Cell(170,4,$this->Revisao(),0,1,'R',0);
	$this->SetFont('Arial','',9);
	$this->SetLineWidth(1);
	$this->SetDrawColor(0,0,0);
	$this->Line(25,40,195,40);
	$this->SetXY(25,45);
}

//Page footer
function Footer()
{

}
}


//Instanciation of inherited class
$pdf=new PDF('p','mm',A4);
$pdf->SetAutoPageBreak(true,20);
$pdf->SetMargins(25,15);
$pdf->SetLineWidth(0.5);

$db = new banco_dados;

//Seta o cabeçalho
$pdf->departamento="ADMINISTRA��O";
$pdf->titulo="RELAT�RIO OS / CLIENTE / STATUS";
$pdf->setor="ADM";
$pdf->codigodoc="405"; //"00";
$pdf->codigo="0"; //Numero OS
$pdf->setorextenso=$setor; //"INFORMATICA"
$pdf->emissao=date("d/m/Y");

$pdf->AliasNbPages();

$pdf->SetXY(25,40);
$pdf->SetFont('Arial','B',8);

//MOSTRA CLIENTES
if ($escolhacliente==-1)
{
	$sql = "SELECT * FROM ".DATABASE.".empresas, ".DATABASE.".unidade, ".DATABASE.".ordem_servico_status, ".DATABASE.".OS ";
	$sql .= "LEFT JOIN ".DATABASE.".funcionarios ON (OS.id_cod_coord = funcionarios.id_funcionario) ";
	$sql .= "LEFT JOIN ".DATABASE.".contatos ON (OS.id_cod_resp = contatos.id_contato) ";
	$sql .= "WHERE empresas.id_unidade = unidades.id_unidade ";
	$sql .= "AND empresas.id_empresa_erp = OS.id_empresa_erp ";
	$sql .= "AND OS.id_os_status = ordem_servico_status.id_os_status ";
	$sql .= "ORDER BY empresas.empresa, ordem_servico_status.os_status, os.os ";

}
else
{
	$sql = "SELECT * FROM ".DATABASE.".empresas, ".DATABASE.".unidade, ".DATABASE.".ordem_servico_status, ".DATABASE.".OS  ";
	$sql .= "LEFT JOIN ".DATABASE.".funcionarios ON (OS.id_cod_coord = funcionarios.id_funcionario) ";
	$sql .= "LEFT JOIN ".DATABASE.".contatos ON (OS.id_cod_resp = contatos.id_contato) ";
	$sql .= "WHERE empresas.id_empresa_erp='".$_POST["escolhacliente"]."' ";
	$sql .= "AND empresas.id_unidade=unidades.id_unidade ";
	$sql .= "AND OS.id_empresa_erp=empresas.id_empresa_erp ";
	$sql .= "AND OS.id_os_status = ordem_servico_status.id_os_status ";
	$sql .= "ORDER BY empresas.empresa, os_status, OS  ";
	
}

$pdf->AddPage();

$db->select($sql,'MYSQL',true);

foreach ($db->array_select as $regcliente)
{
	$pdf->SetFont('Arial','B',6);

	if($regcliente["empresa"]!=$empresa)
	{
		$pdf->SetFont('Arial','B',8);
		
		$pdf->Cell(170,3,"CLIENTE - " . $regcliente["empresa"] . " - " . $regcliente["unidade"],0,1,'L',0);
	
		$pdf->SetFont('Arial','B',6);
		
		$pdf->Cell(10,3,"",0,0,'L',0);
		
		$pdf->Cell(25,3,"OS",0,0,'L',0);
		
		$pdf->Cell(70,3,"COORD. DVM",0,0,'L',0);
		
		$pdf->Cell(70,3,"COORD. CLIENTE",0,1,'L',0);
		
		$empresa = $regcliente["empresa"];
		
		$status = '';
	}
	
	if($regcliente["os_status"]!= $status)
	{
		$pdf->Cell(70,3,$regcliente["os_status"],0,1,'L',0);
		
		$status = $regcliente["os_status"];
	}
	
	$pdf->SetFont('Arial','',6);
	
	$pdf->Cell(10,3,'',0,0,'L',0);	

	$os = sprintf("%05d",$regcliente["os"]);
	
	$pdf->Cell(25,3,$os,0,0,'L',0);
	
	$pdf->Cell(70,3,$regcliente["funcionario"],0,0,'L',0);
	
	$pdf->Cell(70,3,$regcliente["nome_contato"],0,1,'L',0);	
}

$pdf->Output('CONTROLE_OS_CLIENTE_STATUS_'.date('dmYhis').'.pdf', 'D');

?>
