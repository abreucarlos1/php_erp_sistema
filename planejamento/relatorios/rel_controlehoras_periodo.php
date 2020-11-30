<?php
/*
		Relat�rio de Apontamentos x periodo
		
		Criado por Carlos Abreu / Ot�vio Pamplon ia
		
		local/Nome do arquivo:		
		../planejamento/relatorios/rel_controlehoras_periodo.php
		
		Vers�o 0 --> VERS�O INICIAL - 02/03/2006
		Vers�o 1 --> atualiza��o classe banco de dados - 22/01/2015 - Carlos Abreu
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
	
	$this->SetFont('Arial','B',8);
	$this->Cell(170,5,"FUNCION�RIO: " . $this->funcionario,0,1,'L',0);
	$this->SetLineWidth(0.5);
	$this->SetDrawColor(128,128,128);
	$this->Line(25,50,195,50);
	$this->Ln(5);	

}

//Page footer
function Footer()
{
	
}
}

$pdf=new PDF('p','mm',A4);
$pdf->SetAutoPageBreak(true,15);
$pdf->SetMargins(25,15);
$pdf->SetLineWidth(0.5);

$db = new banco_dados;

//Seta o cabeçalho
$pdf->departamento="ADMINISTRA��O";
$pdf->titulo="RELAT�RIO POR PER�ODO";
$pdf->setor="ADM";
$pdf->codigodoc="303"; //"00"; //"02";
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
			//altera��o aqui!!! 03/01/2008
			$ano=date('Y'); //retirado "-1" 07/02/2008 Ot�vio
			$data_ini = "26/" . sprintf("%02d",$mesant) . "/" . $ano;
			$datafim = "25/" . $_POST["mes"] . "/" . $ano;
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

$pdf->versao_documento=$data_ini . " � " . $datafim;

$sql = "SELECT * FROM ".DATABASE.".funcionarios ";
$sql .= "WHERE id_funcionario = '" . $_POST["funcionario"] . "' ";
$sql .= "AND funcionarios.reg_del = 0 ";

$db->select($sql,'MYSQL',true);

$regs = $db->array_select[0];

$pdf->funcionario = $regs["funcionario"];

$array_os = NULL;

$filtro = "";

$array_os = $_POST["os"];

if(!in_array('-1',$array_os) && !empty($array_os))
{
	$filtro = "AND apontamento_horas.id_os IN (".implode(",",$array_os).") ";
}

$pdf->AliasNbPages();
$pdf->AddPage();

$pdf->SetFont('Arial','B',8);
$pdf->Cell(20,5,"DATA",0,0,'L',0);
$pdf->Cell(20,5,"OS",0,0,'L',0);
$pdf->Cell(80,5,"DESCRI��O",0,0,'L',0);
$pdf->Cell(20,5,"H. NORMAIS",0,0,'R',0);
$pdf->Cell(20,5,"H. EXTRAS",0,1,'R',0);
$pdf->SetFont('Arial','',8);

$data_ini = php_mysql($data_ini);
$datafim = php_mysql($datafim);

$sql = "SELECT *, TIME_TO_SEC(hora_normal) AS HN, TIME_TO_SEC(hora_adicional) AS HA, TIME_TO_SEC(hora_adicional_noturna) AS HAN ";
$sql .= "FROM ".DATABASE.".apontamento_horas, ".DATABASE.".OS, ".DATABASE.".atividades  ";
$sql .= "WHERE apontamento_horas.id_funcionario = '" . $_POST["funcionario"] . "' ";
$sql .= "AND apontamento_horas.reg_del = 0 ";
$sql .= "AND OS.reg_del = 0 ";
$sql .= "AND atividades.reg_del = 0 ";
$sql .= "AND atividades.id_atividade = apontamento_horas.id_atividade ";
$sql .= "AND apontamento_horas.id_os = OS.id_os ";
$sql .= $filtro;
$sql .= " AND data BETWEEN '". $data_ini ."' AND '". $datafim ."' ";
$sql .= " ORDER BY apontamento_horas.data, os.os ";

$db->select($sql,'MYSQL',true);

foreach ($db->array_select as $regs)
{
	  $horanormal = explode(":",sec_to_time($regs["HN"]));
	 
	  $horaadicional = explode(":",sec_to_time($regs["HA"]+$regs["HAN"]));
			  
	  $complemento = preg_replace("\n", "", $regs["complemento"]);
	  
	  $tam = $pdf->GetStringWidth($regs["descricao"] . " " . $complemento);
	  
	  $celula = ceil($tam/75); //65 caracteres em uma linha / 120 tamanho do campo
	  // Era 83 - Alterado para 75 por Ot�vio em 03/07/2007. MOTIVO: corre��o de letras embaralhadas.
	  
	  if (!$celula)
	  {
		  $celula = 1;
	  }
	  $celula = $celula * 5;
	  
	  $diaSemana = date('w', strtotime($regs['data']));
	  
	  $indicadorFds = intval($diaSemana) == 0 ? '*' : ''; 
	  
	  $pdf->Cell(20,$celula,mysql_php($regs["data"]),0,0,'L',0);
	  $pdf->Cell(20,$celula,sprintf("%05d",$regs["os"]),0,0,'L',0);
	  $y = $pdf->GetY();
	  $x = $pdf->GetX();
	  $pdf->MultiCell(80,5,$regs["descricao"] . " " . $complemento,0,'L',0);
	  $pdf->SetXY($x+80,$y);
	  $pdf->Cell(20,$celula,$horanormal[0] . ":" . $horanormal[1],0,0,'R',0);
	  $pdf->Cell(20,$celula,$horaadicional[0] . ":" . $horaadicional[1].$indicadorFds,0,1,'R',0);		
	  
	  if($pdf->GetY() > 250)
	  {
		  $pdf->AddPage();

	  }
}

$sql = "SELECT SUM(TIME_TO_SEC(hora_normal)) AS TN, ";
$sql .= "SUM(TIME_TO_SEC(hora_adicional)) AS TA, SUM(TIME_TO_SEC(hora_adicional_noturna)) AS TAN FROM ".DATABASE.".apontamento_horas ";
$sql .= "WHERE apontamento_horas.id_funcionario = '" . $_POST["funcionario"]. "' ";
$sql .= "AND apontamento_horas.reg_del = 0 ";
$sql .= $filtro;
$sql .= "AND data BETWEEN '". $data_ini ."' AND '". $datafim ."' ";

$db->select($sql,'MYSQL',true);

$regs1 = $db->array_select[0];

$subtotaln = explode(":",sec_to_time($regs1["TN"]));
$subtotala = explode(":",sec_to_time($regs1["TA"]+$regs1["TAN"]));
$total = explode(":",sec_to_time($regs1["TN"]+$regs1["TA"]+$regs1["TAN"]));

$pdf->Cell(110);
$pdf->SetFont('Arial','B',8);
$pdf->Cell(20,5,"SUB-TOTAL:",0,0,'R',0);
$pdf->SetFont('Arial','',8);

$pdf->Cell(20,5,$subtotaln[0] . ":" . $subtotaln[1],0,0,'R',0);
$pdf->Cell(20,5,$subtotala[0] . ":" . $subtotala[1],0,1,'R',0);

$pdf->Cell(110);
$pdf->SetFont('Arial','B',8);
$pdf->Cell(20,5,"TOTAL:",0,0,'R',0);
$pdf->SetFont('Arial','',8);

$pdf->Cell(20,5,$total[0]. ":" . $total[1],0,0,'R',0);

$pdf->Line(25,260,65,260);

$pdf->Line(145,260,195,260);	
$pdf->SetXY(25,261);
$pdf->Cell(40,5,"FUNCION�RIO",0,0,'C',0);
$pdf->Cell(210,5,"ADMINISTRA��O",0,0,'C',0);

$pdf->Output('CONTROLE_HORAS_PERIODO_'.date('dmYhis').'.pdf', 'D');

?> 