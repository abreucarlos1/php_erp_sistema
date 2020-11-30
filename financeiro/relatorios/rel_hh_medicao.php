<?php
/*
	  Relatório HH x Medição	
	  
	  Criado por Carlos Abreu / Otávio Pamplona
	  
	  local/Nome do arquivo:
	  ../financeiro/relatorios/rel_hh_medicao.php
	  
	  Versão 0 --> VERSÃO INICIAL - 14/07/2007
	  Versão 1 --> Atualização lay-out - 23/06/2014 - Carlos Abreu
	  Versão 2 --> atualização classe banco de dados - 22/01/2015 - Carlos Abreu
	  Versão 3 --> Inclusão dos campos reg_del nas consultas - 20/11/2017 - Carlos Abreu		
*/

require_once(implode(DIRECTORY_SEPARATOR,array('..','..','config.inc.php')));

require_once(INCLUDE_DIR."include_pdf.inc.php");

$periodo_fim = $_POST["ano"].'-'.$_POST["mes"];

$periodo_ini = calcula_data("01/".$_POST["mes"]."/".$_POST["ano"],"sub","month",1);

$array_periodo = explode("/",$periodo_ini);

$data_ini = $array_periodo[1] . "/" . $array_periodo[2];

$datafim = $_POST["mes"] . "/" . $_POST["ano"];

$periodo = $array_periodo[2] . "-" . $array_periodo[1] . "," . $_POST["ano"] . "-" . $_POST["mes"];

$sql = "SELECT * FROM ".DATABASE.".setores ";
$sql .= "WHERE setores.reg_del = 0 ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}

foreach ($db->array_select as $regs)
{
	if($_POST["chk_".$regs["id_setor"]]==1)
	{
		$array_setor[] = $regs["id_setor"];
	}
}

if(count($array_setor)>0)
{
	$filtro_setor = "AND funcionarios.id_setor IN (".implode(",",$array_setor).") ";
}

$sql = "SELECT * FROM ".DATABASE.".salarios ";
$sql .= "WHERE salarios.reg_del = 0 ";
$sql .= "GROUP BY  tipo_contrato ";
$sql .= "ORDER BY  tipo_contrato ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}

foreach ($db->array_select as $regs)
{
	if($_POST["chk1_".$regs[" tipo_contrato"]]==1)
	{
		$array_contrato[] = "'".$regs[" tipo_contrato"]."'";
	}
}

if(count($array_contrato)>0)
{
	$filtro_contrato = "AND salarios. tipo_contrato IN (".implode(",",$array_contrato).") ";
}

$filtro = "AND fechamento_folha.periodo = '" . $periodo . "' ";

class PDF extends FPDF
{
//Page header
function Header()
{    
	//Logo
	$this->Image(DIR_IMAGENS.'logo_pb.png',11,16,40);

	$this->Ln(1);
	$this->SetFont('Arial','',6);
	$this->Cell(146,4,'',0,0,'L',0);
	$this->Cell(12,4,'DOC:',0,0,'L',0);
	$this->Cell(12,4,$this->documento,0,1,'R',0);
	$this->SetLineWidth(0.3);
	$this->Line(172,19.5,195,19.5);
	$this->Cell(158,4,'EMISSÃO:',0,0,'R',0); //aqui
	$this->Cell(15,4,$this->Emissao(),0,1,'R',0); //aqui
	$this->Line(172,23.5,195,23.5);
	$this->Cell(146,4,'',0,0,'L',0);
	$this->Cell(12,4,'FOLHA:',0,0,'L',0);
	$this->Cell(15,4,$this->PageNo().' de {nb}',0,1,'R',0);
	$this->Line(172,27.5,195,27.5);
	$this->Ln(8);
	$this->Cell(170,4,"",0,1,'R',0);
	$this->SetFont('Arial','B',9);
	$this->SetXY(10,35);	
	$this->Cell(100,5,"FUNCIONÁRIOS",0,0,'L',0);	
	$this->Cell(15,5,"H. N.",0,0,'L',0);
	$this->Cell(15,5,"H. A.",0,0,'L',0);	
	$this->Cell(25,5,"VALOR BRUTO",0,0,'L',0);	
	$this->Ln(5);	
}

//Page footer
function Footer()
{

}
}

//Instanciation of inherited class
$pdf=new PDF('p','mm',A4);
$pdf->SetAutoPageBreak(true,5);
$pdf->SetMargins(10,15);
$pdf->SetLineWidth(0.3);

//Seta o cabeçalho
$pdf->departamento=NOME_EMPRESA;
$pdf->titulo="RELATÓRIO Hh POR MEDIÇÃO ";
$pdf->codigodoc="01"; //"00";
$pdf->codigo=01; //Numero OS
$pdf->documento='FIN';

$pdf->emissao=date("d/m/Y");

$pdf->versao_documento="Período: " . $data_ini . " á " . $datafim;

$pdf->AliasNbPages();

$pdf->AddPage();

$pdf->SetFont('Arial','',8);

$sql = "SELECT * FROM ".DATABASE.".funcionarios, ".DATABASE.".fechamento_folha ";
$sql .= "WHERE funcionarios.id_funcionario = fechamento_folha.id_funcionario ";
$sql .= "AND fechamento_folha.reg_del = 0 ";
$sql .= "AND funcionarios.reg_del = 0 ";

if($_POST["local_trabalho"])
{
	$sql .= "AND funcionarios.id_local = '".$_POST["local_trabalho"]."' ";
}

$sql .= $filtro;
$sql .= $filtro_setor;

$sql .= "ORDER BY funcionarios.funcionario, fechamento_folha.data_ini, fechamento_folha.data_fim ";

$db->select($sql,'MYSQL',true);

$array_fechamento = $db->array_select;	

foreach($array_fechamento as $regs)
{
	$sql = "SELECT  tipo_contrato FROM ".DATABASE.".salarios ";
	$sql .= "WHERE salarios.id_funcionario = '".$regs["id_funcionario"]."' ";
	$sql .= "AND salarios.reg_del = 0 ";
	$sql .= $filtro_contrato;
	$sql .= "ORDER BY id_salario DESC, data DESC LIMIT 1 ";
	
	$db->select($sql,'MYSQL',true);
	
	if($db->numero_registros>0)
	{	
		//Formata as horas normais
		$ahora_normal = explode(":", $regs["total_horas_normais"]);
		$shora_normal = $ahora_normal[0] . ":" . $ahora_normal[1];
		
		//Formata as horas adicionais
		$ahora_adicional = explode(":", $regs["total_horas_adicionais"]);
		$shora_adicional = $ahora_adicional[0] . ":" . $ahora_adicional[1];	
		
		$htotal_normal = $shora_normal;
		
		$htotal_adicional = $shora_adicional;
		
		$pdf->HCell(100,5,$regs["funcionario"],0,0,'L',0);
	
		$pdf->Cell(15,5,$htotal_normal,0,0,'L',0);
		
		$pdf->Cell(15,5,$htotal_adicional,0,0,'L',0);
		
		$pdf->Cell(25,5,"R$ " . formatavalor($regs["valor_total"]),0,1,'L',0);
		
		$totaisn += time_to_sec($regs["total_horas_normais"]);
		
		$totaisa += time_to_sec($regs["total_horas_adicionais"]);
	
		$soma_valor_bruto += + $regs["valor_total"];
	}
}

$pdf->Ln(2);
$pdf->Line(10,$pdf->GetY(),195,$pdf->GetY());
$pdf->SetFont('Arial','B',8);		
$pdf->HCell(100,5,"TOTAIS: ",0,0,'L',0);
$pdf->SetFont('Arial','',8);

$pdf->HCell(15,5,substr(sec_to_time($totaisn),0,count(sec_to_time($totaisn))-4),0,0,'L',0);
$pdf->HCell(15,5,substr(sec_to_time($totaisa),0,count(sec_to_time($totaisa))-4),0,0,'L',0);
$pdf->HCell(45,5,"R$ " . formatavalor(number_format($soma_valor_bruto,2,".","")),0,0,'L',0);

$pdf->Output('MEDICAO_HH_'.date('dmYHmi').'.pdf','D');

?> 