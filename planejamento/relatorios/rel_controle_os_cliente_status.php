<?php
/*
		Relatório de OS x Cliente x status	
		
		Criado por Carlos Abreu
		
		local/Nome do arquivo:
		../planejamento/relatorios/rel_controle_os_cliente_status.php
		
		Versão 0 --> VERSÃO INICIAL : 02/03/2006		
		Versão 1 --> atualização classe banco de dados - 22/01/2015 - Carlos Abreu
		Versão 2 --> Inclusão dos campos reg_del nas consultas - 20/11/2017 - Carlos Abreu
*/

require_once(implode(DIRECTORY_SEPARATOR,array('..','..','config.inc.php')));

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
$pdf->departamento=NOME_EMPRESA;
$pdf->titulo="RELATÓRIO OS / CLIENTE / STATUS";
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
	$sql = "SELECT * FROM ".DATABASE.".empresas, ".DATABASE.".unidades, ".DATABASE.".ordem_servico_status, ".DATABASE.".ordem_servico ";
	$sql .= "LEFT JOIN ".DATABASE.".funcionarios ON (ordem_servico.id_cod_coord = funcionarios.id_funcionario AND funcionarios.reg_del = 0) ";
	$sql .= "LEFT JOIN ".DATABASE.".contatos ON (ordem_servico.id_cod_resp = contatos.id_contato AND contatos.reg_del = 0) ";
	$sql .= "WHERE empresas.id_unidade = unidades.id_unidade ";
	$sql .= "AND empresas.reg_del = 0 ";
	$sql .= "AND unidades.reg_del = 0 ";
	$sql .= "AND ordem_servico_status.reg_del = 0 ";
	$sql .= "AND ordem_servico.reg_del = 0 ";
	$sql .= "AND empresas.id_empresa = ordem_servico.id_empresa ";
	$sql .= "AND ordem_servico.id_os_status = ordem_servico_status.id_os_status ";
	$sql .= "ORDER BY empresas.empresa, ordem_servico_status.os_status, ordem_servico.os ";

}
else
{
	$sql = "SELECT * FROM ".DATABASE.".empresas, ".DATABASE.".unidades, ".DATABASE.".ordem_servico_status, ".DATABASE.".ordem_servico  ";
	$sql .= "LEFT JOIN ".DATABASE.".funcionarios ON (ordem_servico.id_cod_coord = funcionarios.id_funcionario AND funcionarios.reg_del = 0) ";
	$sql .= "LEFT JOIN ".DATABASE.".contatos ON (ordem_servico.id_cod_resp = contatos.id_contato AND contatos.reg_del = 0) ";
	$sql .= "WHERE empresas.id_empresa = '".$_POST["escolhacliente"]."' ";
	$sql .= "AND empresas.reg_del = 0 ";
	$sql .= "AND unidades.reg_del = 0 ";
	$sql .= "AND ordem_servico_status.reg_del = 0 ";
	$sql .= "AND ordem_servico.reg_del = 0 ";
	$sql .= "AND empresas.id_unidade = unidades.id_unidade ";
	$sql .= "AND ordem_servico.id_empresa = empresas.id_empresa ";
	$sql .= "AND ordem_servico.id_os_status = ordem_servico_status.id_os_status ";
	$sql .= "ORDER BY empresas.empresa, os_status, ordem_servico.os  ";
	
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
		
		$pdf->Cell(70,3,"COORDENADOR",0,0,'L',0);
		
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
