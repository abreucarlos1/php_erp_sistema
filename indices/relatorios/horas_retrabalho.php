<?php
/*
		Relatório de Horas de retrabalho
		
		Criado por Carlos Abreu
		
		local/Nome do arquivo:		
		../indices/relatorios/horas_retrabalho.php
		
		Versão 0 --> VERSÃO INICIAL - 02/03/2006
		Versão 1 --> atualização classe banco de dados - 22/01/2015 - Carlos Abreu
		Versão 2 --> Inclusão dos campos reg_del nas consultas - 20/11/2017 - Carlos Abreu	
*/

require_once(implode(DIRECTORY_SEPARATOR,array('..','..','config.inc.php')));

require_once(INCLUDE_DIR."include_pdf.inc.php");

class PDF extends FPDF
{
 var $status;
 var $setor;
 var $codigodoc;
 var $codigo;
 var $emissao;
 var $titulo;
 var $versao_documento;

//Page header
function Header()
{    
	$this->Image(DIR_IMAGENS.'logo_pb.png',26,16,40);
	$this->Ln(1);
	$this->SetFont('Arial','',6);
	$this->Cell(146,4,'',0,0,'L',0);
	$this->Cell(12,4,'DOC:',0,0,'L',0);
	$this->Cell(12,4,$this->setor . '-' . $this->codigodoc . '-' .$this->codigo,0,1,'R',0);
	$this->SetLineWidth(0.3);
	$this->Line(172,19.5,195,19.5);
	$this->Cell(158,4,'EMISSÃO:',0,0,'R',0); //aqui
	$this->Cell(12,4,$this->emissao,0,1,'R',0); //aqui
	$this->Line(172,23.5,195,23.5);
	$this->Cell(146,4,'',0,0,'L',0);
	$this->Cell(12,4,'FOLHA:',0,0,'L',0);
	$this->Cell(12,4,$this->PageNo().' de {nb}',0,0,'R',0);
	$this->Line(172,27.5,195,27.5);
	$this->Ln(8);
	$this->SetFont('Arial','B',12);
	$this->Cell(170,4,$this->titulo,0,1,'R',0);
	$this->SetFont('Arial','B',8);	
	$this->Cell(85,4,"STATUS: ".$this->status,0,0,'L',0);	
	$this->Cell(85,4,$this->versao_documento,0,1,'R',0);
	$this->SetFont('Arial','B',8);
	$this->SetLineWidth(1);
	$this->SetDrawColor(0,0,0);
	$this->Line(25,40,195,40);
	$this->SetXY(25,45);
	$this->Ln(5);
}

//Page footer
function Footer()
{
	
}
}


//Instanciation of inherited class
$pdf=new PDF('p','mm',A4);
$pdf->SetAutoPageBreak(true,15);
$pdf->SetMargins(25,15);
$pdf->SetLineWidth(0.5);

$db = new banco_dados;

//Seta o cabeçalho
$pdf->departamento=NOME_EMPRESA;
$pdf->titulo="HORAS DE RETRABALHO";
$pdf->setor="COR";
$pdf->codigodoc="001"; //"00"; //"02";
$pdf->codigo="01"; //Numero OS
$pdf->setorextenso=$setor; //"INFORMATICA"
$pdf->emissao=date("d/m/Y");

switch($_POST["intervalo"])
{
	case "mes":
	
		if ($_POST["mes"]==1)
		{
			$mes=12;
			$ano=date('Y')-1;
			$data_ini = "26/" . $mes . "/" . $ano;
			$datafim = "25/01/" . date('Y');
		}
		else
		{ 
			$mesant = $_POST["mes"] - 1;
			//alteração aqui!!! 03/01/2008
			$ano=date('Y'); //retirado "-1" 07/02/2008 
			$dataini = "26/" . sprintf("%02d",$mesant) . "/" . $ano;
			$datafim = "25/" . $_POST["mes"] . "/" . $ano;
		}
		
		$pdf->versao_documento=$data_ini . " á " . $datafim;
		
		$sql_periodo = "AND data BETWEEN '". php_mysql($dataini) ."' AND '". php_mysql($datafim) ."' ";
		
	break;
	
	case "periodo":
		
		$data_ini = $_POST["dataini"];
		$datafim = $_POST["datafim"];
		
		$pdf->versao_documento=$data_ini . " á " . $datafim;
		
		$sql_periodo = "AND data BETWEEN '". php_mysql($dataini) ."' AND '". php_mysql($datafim) ."' ";
		
	break;
	
	case "semana":
	
		ajustadata($_POST["semana"],$dataini,$datafim);
	
		$pdf->versao_documento=$dataini . " á " . $datafim;
		
		$sql_periodo = "AND data BETWEEN '". php_mysql($dataini) ."' AND '". php_mysql($datafim) ."' ";
		
	break;
	
	case "total":		
		$pdf->versao_documento="PERÍODO TOTAL";
		$sql_periodo = "";	
	break;
}

if($_POST["status"]!=-1)
{
	
	$sql = "SELECT * FROM ".DATABASE.".ordem_servico_status ";
	$sql .= "WHERE ordem_servico_status.id_os_status = '".$_POST["status"]."' ";
	$sql .= "AND ordem_servico_status.reg_del = 0 ";
	
	$db->select($sql,'MYSQL',true);
	
	$regs = $db->array_select[0];
	
	$pdf->status = $regs["os_status"];
}
else
{
	$pdf->status = "TODOS OS STATUS";
}

$pdf->AliasNbPages();
$pdf->AddPage();

$pdf->SetFont('Arial','B',8);
$pdf->Cell(20,5,"OS",0,0,'R',0);
$pdf->Cell(35,5,"HORAS TOTAIS",0,0,'R',0);
$pdf->Cell(35,5,"H. RETRAB.",0,0,'R',0);
$pdf->Cell(35,5,"ÍNDICE RETRAB.(%)",0,1,'R',0);
$pdf->SetFont('Arial','',8);

//preenche array com HN e HA das horas de retrabalho
$sql = "SELECT *, TIME_TO_SEC(hora_normal) AS HN, TIME_TO_SEC(hora_adicional) AS HA, TIME_TO_SEC(hora_adicional_noturna) AS HAN ";
$sql .= "FROM ".DATABASE.".apontamento_horas, ".DATABASE.".ordem_servico, ".DATABASE.".ordem_servico_status ";
$sql .= "WHERE apontamento_horas.id_os = ordem_servico.id_os ";
$sql .= "AND apontamento_horas.reg_del = 0 ";
$sql .= "AND ordem_servico.reg_del = 0 ";
$sql .= "AND ordem_servico_status.reg_del = 0 ";
$sql .= "AND ordem_servico.id_os_status = ordem_servico_status.id_os_status ";

//Se for selecionado um status
if($_POST["status"]!=-1)
{
	$sql .= " AND ordem_servico.id_os_status = '" . $_POST["status"] . "' ";
}

//Se for selecionada apenas uma OS
if($_POST["os"]!=-1)
{
	$sql .= " AND apontamento_horas.id_os = '" . $_POST["os"] . "' ";
}


$sql .= $sql_periodo;

$sql .= "ORDER BY ordem_servico.os, apontamento_horas.data ";

$array_retrab = NULL;

$array_horas = NULL;

$tot_retrab = 0;

$tot_horas = 0;

$db->select($sql,'MYSQL',true);

foreach($db->array_select as $regs)
{
	if($regs["retrabalho"]==1)
	{
		$array_retrab[$regs["id_os"]] += $regs["HN"]+$regs["HA"]+$regs["HAN"];
		
		$tot_retrab += $regs["HN"]+$regs["HA"]+$regs["HAN"];
		
	}
	else
	{
		$array_horas[$regs["id_os"]] += $regs["HN"]+$regs["HA"]+$regs["HAN"];
		
		$tot_horas += $regs["HN"]+$regs["HA"]+$regs["HAN"];
	}	
}

//preenche array com HN e HA
$sql = "SELECT * FROM ".DATABASE.".apontamento_horas, ".DATABASE.".ordem_servico, ".DATABASE.".ordem_servico_status ";
$sql .= "WHERE apontamento_horas.id_os = ordem_servico.id_os ";
$sql .= "AND apontamento_horas.reg_del = 0 ";
$sql .= "AND ordem_servico.reg_del = 0 ";
$sql .= "AND ordem_servico_status.reg_del = 0 ";
$sql .= "AND ordem_servico.id_os_status = ordem_servico_status.id_os_status ";

//Se for selecionado um status
if($_POST["status"]!=-1)
{
	$sql .= " AND ordem_servico.id_os_status = '" . $_POST["status"] . "' ";
}

//Se for selecionada apenas uma OS
if($_POST["os"]!=-1)
{
	$sql .= " AND apontamento_horas.id_os = '" . $_POST["os"] . "' ";
}

$sql .= $sql_periodo;
$sql .= "GROUP BY apontamento_horas.id_os ";
$sql .= "ORDER BY ordem_servico.os ";

$db->select($sql,'MYSQL',true);

foreach($db->array_select as $regs)
{
	$pdf->Cell(20,5,sprintf("%05d",$regs["os"]),0,0,'R',0);
	
	$pdf->Cell(35,5,substr(sec_to_time($array_horas[$regs["id_os"]]),0,-3),0,0,'R',0);
	
	if($array_retrab[$regs["id_os"]])
	{
		$retrabalho = substr(sec_to_time($array_retrab[$regs["id_os"]]),0,-3);
	}
	else
	{
		$retrabalho = "00:00";
	}
	
	$pdf->Cell(35,5,$retrabalho,0,0,'R',0);
	
	//Indice = Hretrab/Horas Lancadas
	if($array_horas[$regs["id_os"]]!=0)
	{
		$indice = ($array_retrab[$regs["id_os"]]/($array_horas[$regs["id_os"]]))*100;
	}
	else
	{
		$indice = 0.00;
	}	
	
	$pdf->Cell(35,5,str_replace(".",",",round($indice,2)),0,1,'R',0);
}

$pdf->Ln(5);

$pdf->SetFont('Arial','B',8);

$pdf->Cell(20,5,"TOTAL: ",0,0,'R',0);

$pdf->SetFont('Arial','',8);

$pdf->Cell(35,5,substr(sec_to_time($tot_horas),0,-3),0,0,'R',0);

$pdf->Cell(35,5,substr(sec_to_time($tot_retrab),0,-3),0,0,'R',0);

$indice = ($tot_retrab/$tot_horas)*100;	

$pdf->Cell(35,5,str_replace(".",",",round($indice,2)),0,1,'R',0);

$pdf->Output('HORAS_RETRABALHO_'.date('dmYhis').'.pdf', 'D');

?> 