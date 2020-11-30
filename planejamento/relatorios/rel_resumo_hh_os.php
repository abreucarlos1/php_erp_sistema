<?php
/*
		Relat�rio Resumo Hh x OS
		
		Criado por Carlos Abreu / Ot�vio Pamplon ia
		
		local/Nome do arquivo:		
		../planejamento/relatorios/rel_resumo_hh_os.php
		
		Vers�o 0 --> VERS�O INICIAL - 02/03/2006
		Vers�o 1 --> atualiza��o classe banco de dados - 22/01/2015 - Carlos Abreu
		Vers�o 2 --> Inclus�o dos campos reg_del nas consultas - 20/11/2017 - Carlos Abreu	
*/
require_once(implode(DIRECTORY_SEPARATOR,array('..','..','config.inc.php')));

require_once(INCLUDE_DIR."include_pdf.inc.php");

class PDF extends HTML2FPDF
{

var $setor;
var $codigodoc;
var $codigo;
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
	$this->Cell(12,4,date('d/m/Y'),0,1,'R',0); //aqui
	$this->Line(172,23.5,195,23.5);
	$this->Cell(146,4,'',0,0,'L',0);
	$this->Cell(12,4,'FOLHA:',0,0,'L',0);
	$this->Cell(12,4,$this->PageNo().' de {nb}',0,0,'R',0);
	$this->Line(172,27.5,195,27.5);
	$this->Ln(8);
	$this->SetFont('Arial','B',12);
	$this->Cell(170,4,$this->titulo,0,1,'R',0);
	$this->SetFont('Arial','B',8);
	$this->Cell(170,4,$this->versao_documento,0,1,'R',0);
	$this->SetFont('Arial','',9);
	$this->SetLineWidth(1);
	$this->SetDrawColor(0,0,0);
	$this->Line(25,40,195,40);
	$this->SetXY(25,40);
}

//Page footer
function Footer()
{

}
}

$pdf=new PDF('p','mm',A4);
$pdf->SetAutoPageBreak(true,20);
$pdf->SetMargins(25,15);
$pdf->SetLineWidth(0.5);

$db = new banco_dados;

$escolhaos = $_POST['escolhaos'];

$pdf->titulo = "RESUMO - MEDIÇÃO DE Hh POR OS";
$pdf->setor = "ADM";
$pdf->codigodoc = "401"; //"00"; //"02";
$pdf->codigo = "0"; //Numero OS

$pdf->versao_documento = $data_ini . " � " . $datafim;

$pdf->AliasNbPages();
$pdf->SetXY(25,40);
$pdf->SetFont('Arial','B',8);
$pdf->Ln(5);
$pdf->SetFont('Arial','',8);
$pdf->Ln(5);

$data_ini = php_mysql($_POST["data_ini"]);
$datafim = php_mysql($_POST["datafim"]);

//MOSTRA A OS E A DESCRICAO

if ($data_ini=='' || $datafim=='')
{
	if ($escolhaos==-1)
	{
		$sql = "SELECT *, SUM(TIME_TO_SEC(hora_normal)) AS THN, SUM(TIME_TO_SEC(hora_adicional)) AS THA ";
		$sql .= "FROM ".DATABASE.".apontamento_horas, ".DATABASE.".OS, ".DATABASE.".ordem_servico_status ";
		$sql .= "WHERE apontamento_horas.id_os = OS.id_os ";
		$sql .= "AND apontamento_horas.reg_del = 0 ";
		$sql .= "AND OS.reg_del = 0 ";
		$sql .= "AND ordem_servico_status.reg_del = 0 ";
		$sql .= "AND OS.id_os_status = ordem_servico_status.id_os_status ";
		$sql .= "AND ordem_servico_status.id_os_status IN (1,14,16) ";
		$sql .= "GROUP BY os.os";
	}
	else
	{
		$sql = "SELECT *, SUM(TIME_TO_SEC(hora_normal)) AS THN, SUM(TIME_TO_SEC(hora_adicional)) AS THA ";
		$sql .= "FROM ".DATABASE.".apontamento_horas, ".DATABASE.".OS ";
		$sql .= "WHERE apontamento_horas.id_os = '" . $_POST["escolhaos"] . "' ";
		$sql .= "AND apontamento_horas.reg_del = 0 ";
		$sql .= "AND OS.reg_del = 0 ";
		$sql .= "AND OS.id_os = apontamento_horas.id_os ";
		$sql .= "GROUP BY os.os";
	}
}
else
{
	if ($escolhaos==-1)
	{
		$sql = "SELECT *, SUM(TIME_TO_SEC(hora_normal)) AS THN, SUM(TIME_TO_SEC(hora_adicional)) AS THA ";
		$sql .= "FROM ".DATABASE.".apontamento_horas, ".DATABASE.".OS, ".DATABASE.".ordem_servico_status ";
		$sql .= "WHERE apontamento_horas.id_os = OS.id_os ";
		$sql .= "AND apontamento_horas.reg_del = 0 ";
		$sql .= "AND OS.reg_del = 0 ";
		$sql .= "AND ordem_servico_status.reg_del = 0 ";
		$sql .= "AND apontamento_horas.data BETWEEN '" . $data_ini . "' AND '" . $datafim . "' ";
		$sql .= "AND OS.id_os_status = ordem_servico_status.id_os_status ";
		$sql .= "AND ordem_servico_status.id_os_status IN (1,14,16) ";
		$sql .= "GROUP BY os.os";
	}
	else
	{
		$sql = "SELECT *, SUM(TIME_TO_SEC(hora_normal)) AS THN, SUM(TIME_TO_SEC(hora_adicional)) AS THA ";
		$sql .= "FROM ".DATABASE.".apontamento_horas, ".DATABASE.".OS ";
		$sql .= "WHERE apontamento_horas.id_os = '" . $_POST["escolhaos"] . "' ";
		$sql .= "AND apontamento_horas.reg_del = 0 ";
		$sql .= "AND OS.reg_del = 0 ";
		$sql .= "AND OS.id_os = apontamento_horas.id_os ";
		$sql .= "AND apontamento_horas.data BETWEEN '" . $data_ini . "' AND '" . $datafim . "' ";
		$sql .= "GROUP BY os.os";
	}
}
$celula = 1;
$THN = 0;
$THA = 0;
$filtro = "";

if($escolhaos==-1)
{
	$pdf->AddPage();
}

$db->select($sql,'MYSQL',true);

$array_horas = $db->array_select;

foreach ($array_horas as $regconth)
{
	if($escolhaos!=-1)
	{
		$pdf->AddPage();
	}
	
	$sql = "SELECT SUM(TIME_TO_SEC(hora_normal)+TIME_TO_SEC(hora_adicional)) AS HORAS FROM ".DATABASE.".apontamento_horas ";
	$sql .= "WHERE apontamento_horas.id_os = '".$regconth["id_os"]."' ";
	$sql .= "AND apontamento_horas.reg_del = 0 ";

	$db->select($sql,'MYSQL',true);		

	$regho = $db->array_select[0];
	
	if(($regh["HH"]*3600)<($regho["HORAS"]))
	{
		$negativo = true;
	}
	else
	{
		$negativo = false;
	}		
	
	$horasrestantes = explode(":",sec_to_time(($regh["HH"]*3600) - ($regho["HORAS"])));

	$contratada = explode(":",sec_to_time(($regh["HH"]*3600)));		
	
	$pdf->SetLineWidth(0.2);	

	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(170,5,"OS - " . sprintf("%05d",$regconth["os"]) . " - " . $regconth["descricao"],0,1,'L',0);
	
	$pdf->Cell(120,5,"DATA DE INICIO: " . $_POST["data_ini"] . " - DATA FINAL: " . $_POST["datafim"] . " - HORAS CONTRATADAS: " . $contratada[0].":".$contratada[1],0,0,'L',0);
	if (!$negativo)
	{
		$pdf->Cell(50,5,"SALDO DE HORAS: " . $horasrestantes[0].":".$horasrestantes[1],0,1,'R',0);
	}
	else
	{
		$pdf->SetTextColor(255,0,0);
		$pdf->Cell(50,5,"SALDO DE HORAS: -" . $horasrestantes[0].":".$horasrestantes[1],0,1,'R',0);
		$pdf->SetTextColor(0,0,0);
	}
	
	$pdf->SetDrawColor(0,0,0);
	$pdf->Line(25,$pdf->GetY(),195,$pdf->GetY());
	
	$pdf->Ln(5);
	
	$pdf->Cell(50,5,"DISCIPLINA",0,0,'L',0);
	$pdf->Cell(75,5,"",0,0,'L',0);
	$pdf->Cell(20,5,"H. NORMAIS",0,0,'R',0);
	$pdf->Cell(10,5);
	$pdf->Cell(13,5,"H. EXTRAS",0,1,'R',0);
	$pdf->SetFont('Arial','',8);	
	
	// MOSTRA AS DISCIPLINAS
	$sql = "SELECT *, SUM(TIME_TO_SEC(hora_normal)) AS DHN, SUM(TIME_TO_SEC(hora_adicional)) AS DHA FROM ".DATABASE.".apontamento_horas, ".DATABASE.".setores ";
	$sql .= "WHERE apontamento_horas.id_setor = setores.id_setor ";
	$sql .= "AND apontamento_horas.reg_del = 0 ";
	$sql .= "AND setores.reg_del = 0 ";
	$sql .= "AND apontamento_horas.id_os = '" . $regconth["id_os"] . "' ";
	$sql .= "AND apontamento_horas.data BETWEEN '" . $data_ini . "' AND '" . $datafim . "' ";
	$sql .= "GROUP BY setores.setor";
	
	$db->select($sql,'MYSQL',true);
	
	foreach ($db->array_select as $regdisciplina)
	{
		$pdf->Cell(30,5,"",0,0,'L',0);
		$pdf->Cell(75,5,$regdisciplina["setor"],0,0,'L',0);
		
		$dhn = explode(":",sec_to_time($regdisciplina["DHN"]));

		$dha = explode(":",sec_to_time($regdisciplina["DHA"]));	
		
		$pdf->Cell(20,5,'',0,0,'R',0);
		$pdf->SetFont('Arial','',8);
		$pdf->Cell(20,5,$dhn[0] . ":" . $dhn[1],0,0,'R',0);
		$pdf->Cell(10,5);
		$pdf->Cell(13,5,$dha[0] . ":" . $dha[1],0,1,'R',0);
		$pdf->Ln(2);
		
	}
		
	$thn = explode(":",sec_to_time($regconth["THN"]));

	$tha = explode(":",sec_to_time($regconth["THA"]));
	
	$THN += $regconth["THN"];
	$THA += $regconth["THA"]; 
	
	if($escolhaos!=-1)
	{
		$pdf->SetFont('Arial','B',8);
		$pdf->Cell(125,5,'TOTAL:',0,0,'R',0);
		$pdf->SetFont('Arial','',8);
		$pdf->Cell(20,5,$thn[0] . ":" . $thn[1],0,0,'R',0);
		$pdf->Cell(10,5);
		$pdf->Cell(13,5,$tha[0] . ":" . $tha[1],0,1,'R',0);		
		$pdf->Ln(2);
	}
	else
	{
		$pdf->SetFont('Arial','B',8);
		$pdf->Cell(125,5,'SUB-TOTAL:',0,0,'R',0);
		$pdf->SetFont('Arial','',8);
		$pdf->Cell(20,5,$thn[0] . ":" . $thn[1],0,0,'R',0);
		$pdf->Cell(10,5);
		$pdf->Cell(13,5,$tha[0] . ":" . $tha[1],0,1,'R',0);
		
		
		$pdf->SetLineWidth(0.5);
		$pdf->SetDrawColor(128,128,128);
		$pdf->Line(25,$pdf->GetY(),195,$pdf->GetY());
			
		$pdf->Ln(2);		
	}
}
if($escolhaos==-1)
{
	$tthn = explode(":",sec_to_time($THN));

	$ttha = explode(":",sec_to_time($THA));
	$pdf->Ln(2);
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(125,5,'TOTAL:',0,0,'R',0);
	$pdf->SetFont('Arial','',8);
	$pdf->Cell(20,5,$tthn[0] . ":" . $tthn[1],0,0,'R',0);
	$pdf->Cell(10,5);
	$pdf->Cell(13,5,$ttha[0] . ":" . $ttha[1],0,1,'R',0);
}

$pdf->Output('RESUMO_HH_OS_'.date('dmYhis').'.pdf', 'D');
?>