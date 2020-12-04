<?php
/*
		Relatorio Exames Vencidos
		
		Criado por Carlos Abreu
		
		local/Nome do arquivo:
		../rh/relatorios/rel_exames_vencidos.php
		
		Versão 0 --> VERSÃO INICIAL - 04/05/2006
		Versão 1 --> Inclusão dos campos reg_del nas consultas - 27/11/2017 - Carlos Abreu	
*/
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));

require_once(INCLUDE_DIR."include_pdf.inc.php");

class PDF extends FPDF
{

var $titulo;
var $situacao;

function Header()
{
	$this->Image(DIR_IMAGENS.'logo_pb.png',26,16,40);
	
	$this->Ln(2);
	$this->SetFont('Arial','B',12);
	$this->Cell(32,5,'',0,0,'C',0);
	$this->Cell(120,5,$this->titulo,0,0,'C',0);
	$this->SetFont('Arial','',8);
	$this->Cell(25,5,date('d/m/Y'),0,1,'R',0);
	$this->SetFont('Arial','B',8);
	$this->Cell(32,5,'',0,0,'C',0);
	$this->Cell(120,5,$this->situacao,0,0,'C',0);
	$this->SetFont('Arial','',8);
	$this->Cell(25,5,'',0,1,'R',0);
	$this->SetFont('Arial','',8);
	$this->Ln(5);

}

function Footer()
{
	$this->SetFont('Arial','',8);
	$this->SetY(290);
	$this->Cell(32,5,'FRQ 708',0,0,'L',0);
	$this->Cell(120,5,'',0,0,'C',0);
	$this->SetFont('Arial','',8);
	$this->Cell(25,5,$this->PageNo().' de {nb}',0,0,'R',0);
	
}

}

$pdf=new PDF('p','mm',A4);
$pdf->SetAutoPageBreak(true,10);
$pdf->SetMargins(25,15);
$pdf->SetLineWidth(0.5);
$pdf->SetTitle("EXAMES VENCIDOS");

$pdf->titulo="EXAMES VENCIDOS";

$db = new banco_dados;

switch ($_POST["mes"])
{
	case '01':
		$pdf->situacao="JANEIRO - ".$_POST["ano"];
	break;
	case '02':
		$pdf->situacao="FEVEREIRO - ".$_POST["ano"];
	break;
	case '03':
		$pdf->situacao="MARÇO - ".$_POST["ano"];
	break;
	case '04':
		$pdf->situacao="ABRIL - ".$_POST["ano"];
	break;
	case '05':
		$pdf->situacao="MAIO - ".$_POST["ano"];
	break;
	case '06':
		$pdf->situacao="JUNHO - ".$_POST["ano"];
	break;
	case '07':
		$pdf->situacao="JULHO - ".$_POST["ano"];
	break;
	case '08':
		$pdf->situacao="AGOSTO - ".$_POST["ano"];
	break;
	case '09':
		$pdf->situacao="SETEMBRO - ".$_POST["ano"];
	break;
	case '10':
		$pdf->situacao="OUTUBRO - ".$_POST["ano"];
	break;
	case '11':
		$pdf->situacao="NOVEMBRO - ".$_POST["ano"];
	break;
	case '12':
		$pdf->situacao="DEZEMBRO - ".$_POST["ano"];
	break;

}

$data_ini = $_POST["ano"] ."-". $_POST["mes"] . "-" . "01";
$datafim = $_POST["ano"] ."-".$_POST["mes"] . "-" . "31";

$pdf->AliasNbPages();
$pdf->AddPage();

$pdf->SetFont('Arial','B',8);
$pdf->Cell(80,5,"FUNCIONÁRIO",0,0,'L',0);
$pdf->Cell(50,5,"TIPO DE EXAME",0,0,'L',0);
$pdf->Cell(35,5,"DATA DE VENCIMENTO",0,1,'L',0);
$pdf->SetFont('Arial','',8);
$pdf->Ln(5);

$filtro = "";

if($_POST["chk_demiss"]!=="1")
{
	$filtro = "AND rh_aso.tipo_exame NOT IN ('5') ";
}


$sql = "SELECT * FROM ".DATABASE.".funcionarios, ".DATABASE.".rh_aso ";
$sql .= "WHERE rh_aso.data_vencimento BETWEEN '".$data_ini."' AND '".$datafim."' ";
$sql .= "AND funcionarios.situacao = 'ATIVO' ";
$sql .= "AND funcionarios.reg_del = 0 ";
$sql .= "AND rh_aso.reg_del = 0 ";
$sql .= "AND rh_aso.id_funcionario = funcionarios.id_funcionario ";
$sql .= "AND rh_aso.realizado = 0 ";
$sql .= $filtro;
$sql .= "ORDER BY rh_aso.data_vencimento, funcionarios.funcionario ";

$db->select($sql,'MYSQL',true);

foreach ($db->array_select as $regs)
{
	$tipo_exame = "";
	
	switch($regs["tipo_exame"])
	{
		case '1':
			$tipo_exame = 'ADMISSIONAL';
		break;
		
		case '2':
			$tipo_exame = 'PERIÓDICO';
		break;
		
		case '3':
			$tipo_exame = 'PERIÓDICO/AUDIOMÉTRICO';
		break;
		
		case '4':
			$tipo_exame = 'MUDANÇA DE FUNÇÃO';
		break;
		
		case '5':
			$tipo_exame = 'DEMISSIONAL';
		break;
		
		case '6':
			$tipo_exame = 'RETORNO AO TRABALHO';
		break;
	
	}

	$pdf->Cell(80,5,$regs["funcionario"],0,0,'L',0);
	$pdf->Cell(50,5,$tipo_exame,0,0,'L',0);
	$pdf->Cell(35,5,mysql_php($regs["data_vencimento"]),0,1,'L',0);

}

$pdf->Output('RELATORIO_EXAMES_VENC_'.date('dmYhis').'.pdf', 'D');

?> 