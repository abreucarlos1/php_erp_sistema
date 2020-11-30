<?php
/*
		Relat�rio de HORAS POR PER�ODO	
		
		Criado por Carlos Abreu / Ot�vio Pamplon ia
		
		local/Nome do arquivo:
		../planejamento/relatorios/controlehorasperiodo_assinaturas.php
		
		Vers�o 0 --> VERS�O INICIAL : 02/03/2006		
		Versao 1 --> atualiza��o classe banco de dados - 22/01/2015 - Carlos Abreu
		Vers�o 2 --> Inclus�o dos campos reg_del nas consultas - 20/11/2017 - Carlos Abreu
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
	$this->Cell(255,4,$this->Revisao(),0,1,'R',0);
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
	$this->Line(25,190,65,190);
	$this->Line(90,190,130,190);
	$this->Line(165,190,205,190);	
	$this->Line(240,190,280,190);
	$this->SetXY(25,191);
	$this->Cell(40,5,"FUNCION�RIO",0,0,'C',0);
	$this->Cell(25);
	$this->Cell(40,5,"SUPERVISOR",0,0,'C',0);
	$this->Cell(35);
	$this->Cell(40,5,"CLIENTE",0,0,'C',0);
	$this->Cell(35);
	$this->Cell(40,5,"PLANEJAMENTO",0,0,'C',0);
}
}

//Implementado em 26/09/2007
switch($_POST["intervalo"])
{
	case "mes":
	
		if ($_POST["mes"]==1)
		{
			$mes=12;
			$ano=date("Y")-1;
			$data_ini = "26/" . $mes . "/" . $ano;
			$datafim = "25/01/" . date("Y");
		}
		else
		{ 
			$mesant = $_POST["mes"] - 1;
			$data_ini = "26/" . $mesant . "/" . date("Y");
			$datafim = "25/" . $_POST["mes"] . "/" . date("Y");
		}
	break;
	
	case "periodo":
		
		$data_ini = $_POST["data_ini"];
		$datafim = $_POST["datafim"];
		
	break;
	
	case "semana":
	
		ajustadata($_POST["semana"],$data_ini,$datafim);
	
	break;
}

$db = new banco_dados;

$pdf=new PDF('l','mm',A4);
$pdf->SetAutoPageBreak(true,30);
$pdf->SetMargins(25,15);
$pdf->SetLineWidth(0.5);

$pdf->departamento="ADMINISTRA��O";
$pdf->titulo="RELAT�RIO POR DATA DE HORAS";
$pdf->setor="ADM";
$pdf->codigodoc="02"; //"00";
$pdf->codigo="01"; //Numero OS
$pdf->setorextenso=$setor; //"INFORMATICA"
$pdf->emissao=date("d/m/Y");
$pdf->versao_documento=$data_ini . " � " . $datafim;

$array_os = $_POST["os"];

$filtro = "";

if(!in_array('-1',$array_os) && !empty($array_os))
{
	$filtro = "AND apontamento_horas.id_os IN (".implode(",",$array_os).") ";
}

$sql = "SELECT * FROM ".DATABASE.".funcionarios ";
$sql .= "WHERE id_funcionario = '" . $_POST["funcionario"] . "' ";
$sql .= "AND funcionarios.reg_del = 0 ";

$db->select($sql,'MYSQL',true);

$regs = $db->array_select[0];

$pdf->AliasNbPages();
$pdf->AddPage();

$pdf->SetFont('Arial','B',8);
$pdf->Cell(170,5,"FUNCION�RIO: " . $regs["funcionario"],0,1,'L',0);
$pdf->SetLineWidth(0.5);
$pdf->SetDrawColor(128,128,128);
$pdf->Line(25,50,280,50);
$pdf->Ln(5);

$pdf->SetFont('Arial','B',8);
$pdf->Cell(20,5,"DATA",0,0,'L',0);
$pdf->Cell(10,5,"OS",0,0,'L',0);
$pdf->Cell(20,5,"ATIVIDADE",0,0,'L',0);
$pdf->Cell(150,5,"DESCRI��O",0,0,'L',0);
$pdf->Cell(20,5,"H. NORMAIS",0,0,'R',0);
$pdf->Cell(20,5,"H. EXTRAS",0,1,'R',0);
$pdf->SetFont('Arial','',8);

$data_ini = php_mysql($data_ini);
$datafim = php_mysql($datafim);

$sql = "SELECT *, TIME_TO_SEC(hora_normal) AS HN, TIME_TO_SEC(hora_adicional) AS HA, TIME_TO_SEC(hora_adicional_noturna) AS HAN ";
$sql .= "FROM ".DATABASE.".apontamento_horas, ".DATABASE.".OS, ".DATABASE.".atividades ";
$sql .= "WHERE apontamento_horas.id_funcionario='" . $_POST["funcionario"] . "' ";
$sql .= "AND apontamento_horas.reg_del = 0 ";
$sql .= "AND OS.reg_del = 0 ";
$sql .= "AND atividades.reg_del = 0 ";
$sql .= "AND apontamento_horas.id_os = OS.id_os ";
$sql .= $filtro;
$sql .= "AND atividades.id_atividade = apontamento_horas.id_atividade ";
$sql .= "AND data BETWEEN '" . $data_ini . "' AND '" . $datafim . "' ORDER BY data, os.os ";

$db->select($sql,'MYSQL',true);

foreach ($db->array_select as $regs)
{
	$horanormal = explode(":",sec_to_time($regs["HN"]));
	$horaadicional = explode(":",sec_to_time($regs["HA"]+$regs["HAN"]));
			
	$complemento = preg_replace ("\n", "", $regs["complemento"]);
	
	$tam = $pdf->GetStringWidth($regs["descricao"] . " " . $complemento);
	$celula = ceil($tam/90); //65 caracteres em uma linha / 120 tamanho do campo
	if (!$celula)
	{
		$celula = 1;
	}
	$celula = $celula * 5;
	
	if($regs["os"]>100)
	{
		$centro = "99";
		$os = sprintf("%05d",$regs["os"]);
	}
	else
	{
		$centro = "0".$regs["os"];
		$os = " - ";
	}
	
	$pdf->Cell(20,$celula,mysql_php($regs["data"]),0,0,'L',0);
	$pdf->Cell(10,$celula,$os,0,0,'L',0);
	$pdf->Cell(20,$celula,$regs["codigo"],0,0,'L',0);
	$y = $pdf->GetY();
	$x = $pdf->GetX();
	$pdf->MultiCell(150,5,$regs["descricao"] . " " . $complemento,0,'L',0);
	$pdf->SetXY($x+150,$y);
	$pdf->Cell(20,$celula,$horanormal[0] . ":" . $horanormal[1],0,0,'R',0);
	$pdf->Cell(20,$celula,$horaadicional[0] . ":" . $horaadicional[1],0,1,'R',0);
}

$sql = "SELECT SUM(TIME_TO_SEC(hora_normal)) AS TN, ";
$sql .= "SUM(TIME_TO_SEC(hora_adicional)) AS TA, SUM(TIME_TO_SEC(hora_adicional_noturna)) AS TAN FROM ".DATABASE.".apontamento_horas ";
$sql .= "WHERE apontamento_horas.id_funcionario='" . $_POST["funcionario"]. "' ";
$sql .= "AND apontamento_horas.reg_del = 0 ";
$sql .= $filtro;
$sql .= "AND data BETWEEN '" . $data_ini . "' AND '" . $datafim . "'";

$db->select($sql,'MYSQL',true);

$regs = $db->array_select[0];

$subtotaln = explode(":",sec_to_time($regs["TN"]));
$subtotala = explode(":",sec_to_time($regs["TA"]+$regs["TAN"]));
$total = explode(":",sec_to_time($regs["TN"]+$regs["TA"]+$regs["TAN"]));

$pdf->Cell(190);
$pdf->SetFont('Arial','B',8);
$pdf->Cell(20,5,"SUB-TOTAL:",0,0,'R',0);
$pdf->SetFont('Arial','',8);

$pdf->Cell(20,5,$subtotaln[0] . ":" . $subtotaln[1],0,0,'R',0);
$pdf->Cell(20,5,$subtotala[0] . ":" . $subtotala[1],0,1,'R',0);

$pdf->Cell(190);
$pdf->SetFont('Arial','B',8);
$pdf->Cell(20,5,"TOTAL:",0,0,'R',0);
$pdf->SetFont('Arial','',8);

$pdf->Cell(20,5,$total[0]. ":" . $total[1],0,0,'R',0);

$pdf->Output('RELATORIO_PERIODO_ASSINAT_'.date('dmYhis').'.pdf', 'D');

?> 