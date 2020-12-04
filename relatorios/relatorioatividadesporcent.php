<?php
/*
		Relatório Lista de atividades Orcamento
		
		Criado por Carlos Abreu  
		
		local/Nome do arquivo:
		../relatorios/relatorioatividadesporcent.php
	
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
	$this->Cell(228,4,'',0,0,'L',0);
	$this->Cell(15,4,'DOC:',0,0,'L',0);
	$this->Cell(12,4,$this->setor() . '-' . $this->codigodoc() . '-' .$this->codigo(),0,1,'R',0);
	$this->SetLineWidth(0.3);
	$this->Line(254,19.5,280,19.5);
	$this->Cell(240,4,'EMISSÃO:',0,0,'R',0); //aqui
	$this->Cell(15,4,$this->Emissao(),0,1,'R',0); //aqui
	$this->Line(254,23.5,280,23.5);
	$this->Cell(228,4,'',0,0,'L',0);
	$this->Cell(15,4,'FOLHA:',0,0,'L',0);
	$this->Cell(13,4,$this->PageNo().' de {nb}',0,0,'R',0);
	$this->Line(254,27.5,280,27.5);
	$this->Ln(8);
	$this->SetFont('Arial','B',12);
	$this->Cell(255,4,$this->Titulo(),0,1,'R',0);
	$this->SetFont('Arial','B',8);
	$this->SetFont('Arial','',9);
	$this->SetLineWidth(1);
	$this->SetDrawColor(0,0,0);
	$this->Line(25,40,280,40);
	$this->SetLineWidth(0.5);
	$this->SetXY(25,43);
	
}

//Page footer
function Footer()
{

}
}

$db = new banco_dados;

$pdf=new PDF('L','mm',A4);
$pdf->SetMargins(25,15);
$pdf->SetLineWidth(0.3);

$pdf->titulo="LISTA DE ATIVIDADES PARA ORÇAMENTO";
$pdf->setor="ADM";
$pdf->codigodoc="01"; //"00";
$pdf->codigo=11; //Numero OS
$pdf->setorextenso=$setor; //"INFORMATICA"
$pdf->emissao=date("d/m/Y");
$pdf->versao_documento="0";

$pdf->AliasNbPages();

$disciplinaant = "";
$disciplina = "";

$sql = "SELECT * FROM ".DATABASE.".setores ";
$sql .= "WHERE setores.reg_del = 0 ";
$sql .= "ORDER BY setor ";

$db->select($sql,'MYSQL',true);

$array_set = $db->array_select;	

foreach ($array_set as $regcc)
{	
	$sql = "SELECT * FROM ".DATABASE.".atividades_orcamento, ".DATABASE.".atividades ";
	$sql .= "LEFT JOIN ".DATABASE.".formatos ON (atividades.id_formato = formatos.id_formato AND formatos.reg_del = 0) ";
	$sql .= "WHERE LEFT(atividades.codigo,3)='".$regcc["abreviacao"]."' ";
	$sql .= "AND atividades_orcamento.reg_del = 0 ";
	$sql .= "AND atividades.reg_del = 0 ";
	$sql .= "AND atividades.obsoleto = 0 ";
	$sql .= "AND atividades.id_atividade = atividades_orcamento.id_atividade ";
	$sql .= "ORDER BY descricao ";

	$db->select($sql,'MYSQL',true);		

	foreach ($db->array_select as $regs)
	{
		if($regcc["setor"]!= $setor_old)
		{		
			$pdf->AddPage();
			$pdf->SetFont('Arial','B',8);
			$pdf->Cell(255,5,$regcc["setor"],0,1,'R',0);
			$pdf->SetDrawColor(128,128,128);
			$pdf->SetLineWidth(0.5);
			$pdf->Line(25,50,280,50);
			$pdf->Ln(4);
			$pdf->Cell(20,5,"CÓDIGO",0,0,'C',0);
			$pdf->Cell(3);
			$pdf->Cell(100,5,"DESCRIÇÃO",0,0,'L',0);
			$pdf->Cell(50,5,"%",0,0,'C',0);
			$pdf->Cell(25,5,"FORMATO",0,0,'C',0);
			$pdf->Cell(25,5,"H. ESTIMADAS",0,1,'C',0);
			$pdf->Ln(4);
			$pdf->SetFont('Arial','',8);
			
			$setor_old = $regcc["setor"];
		}
		
		$txt = '';

		$sql = "SELECT * FROM ".DATABASE.".atividades_orcamento, ".DATABASE.".rh_funcoes, ".DATABASE.".rh_cargos ";
		$sql .= "WHERE id_atividade = '".$regs["id_atividade"]."' ";
		$sql .= "AND atividades_orcamento.reg_del = 0 ";
		$sql .= "AND rh_funcoes.reg_del = 0 ";
		$sql .= "AND rh_cargos.reg_del = 0 ";
		$sql .= "AND atividades_orcamento.id_funcao = rh_funcoes.id_funcao ";
		$sql .= "AND rh_funcoes.id_cargo_grupo = rh_cargos.id_cargo_grupo ";
		$sql .= "ORDER BY rh_cargos.ordem ";
		
		$db->select($sql,'MYSQL',true);		
		
		foreach ($db->array_select as $regs1)
		{
			$txt .= $regs1["abreviacao"].' '.$regs1["porcentagem"].'%'.' | ';
		}
		
		if($regs["id_atividade"]!= $atividade)
		{
			$pdf->Cell(20,5,$regs["codigo"],0,0,'C',0);
			$pdf->Cell(3);
			$pdf->HCell(100,5,$regs["descricao"],0,0,'L',0);
			$pdf->HCell(50,5,$txt,0,0,'C',0);
			$pdf->Cell(25,5,$regs["formato"],0,0,'C',0);
			$pdf->Cell(25,5,$regs["horasestimadas"],0,1,'C',0);
			
			$atividade = $regs["id_atividade"];
		}
		
	}
}

$pdf->Output('RELATORIO_ATIVIDADES_PORC_'.date('dmYhis').'.pdf', 'D');

?> 