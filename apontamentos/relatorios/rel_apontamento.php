<?php
/*
		Relatório Controle Horas Mensal	
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		../relatorios/rel_apontamento.php
		
		data de criação: 14/01/2005
		
		Versão 0 --> VERSÃO INICIAL
		Versão 1 --> ATUALIZAÇÃO LAY OUT - 15/09/2016
		Versão 2 --> Inclusão dos campos reg_del nas consultas - 14/11/2017 - Carlos Abreu	
*/

require_once(implode(DIRECTORY_SEPARATOR,array('..','..','config.inc.php')));

require_once(INCLUDE_DIR."include_pdf.inc.php");

class PDF extends FPDF
{
//Page header
function Header()
{    
	$this->Image(DIR_IMAGENS.'logo_doc_tecnicos_pb.png',26,16,40);

	$this->Ln(1);
	$this->SetFont('Arial','',6);
	$this->Cell(146,4,'',0,0,'L',0);
	$this->Cell(12,4,'DOC:',0,0,'L',0);
	$this->Cell(12,4,$this->setor() . '-' . $this->codigodoc() . '-' .$this->codigo(),0,1,'R',0);
	$this->SetLineWidth(0.3);
	$this->Line(172,19.5,195,19.5);
	$this->Cell(158,4,'EMISS�O:',0,0,'R',0); //aqui
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

$mes = intval($_POST["periodo_imp"]);

if($mes==1)
{
	$mes = 12;
	$ano = date("Y")-1;
	$data_ini = "26/" . sprintf("%02d",$mes) . "/" . $ano;
	$datafim = "25/01/" . date("Y");
	
	$dataini_p = $ano . $mes . "26";
	$datafim_p = date('Y')."0125";
}
else
{ 
	$mesant = $mes - 1;
	$ano = date('Y');
	$data_ini = "26/" .  sprintf("%02d",$mesant) . "/" . date("Y");
	$datafim = "25/" .  sprintf("%02d",$mes) . "/" . date("Y");
	
	$dataini_p = $ano . sprintf("%02d",$mesant)."26";
	$datafim_p = $ano . $mes . "25";
}

//Instanciation of inherited class
$pdf=new PDF('p','mm',A4);
$pdf->SetAutoPageBreak(true,30);
$pdf->SetMargins(25,15);
$pdf->SetLineWidth(0.5);

$db = new banco_dados;

$sql = "SELECT * FROM ".DATABASE.".funcionarios ";
$sql .= "WHERE funcionarios.id_funcionario = '" . $_POST["id_funcionario"] . "' ";
$sql .= "AND funcionarios.reg_del = 0 ";

$db->select($sql,'MYSQL',true);

$regs = $db->array_select[0];

//Seta o cabe�alho
$pdf->departamento="ADMINISTRATIVO";
$pdf->titulo="RELATÓRIO MENSAL DE HORAS - SIMPLES CONFERÊNCIA";
$pdf->setor="ADM";
$pdf->codigodoc="999"; //"00"; //"02";
$pdf->codigo="01"; //Numero OS
$pdf->setorextenso=$setor; //"INFORMATICA"
$pdf->emissao=date("d/m/Y");

$pdf->versao_documento = $data_ini . " á " . $datafim;

$pdf->AliasNbPages();
$pdf->AddPage();

$pdf->SetFont('Arial','B',8);
$pdf->Cell(170,5,"FUNCIONÁRIO: " . $regs["funcionario"],0,1,'L',0);
$pdf->SetLineWidth(0.5);
$pdf->SetDrawColor(128,128,128);
$pdf->Line(25,50,195,50);
$pdf->Ln(5);

$pdf->SetFont('Arial','B',8);
$pdf->Cell(20,5,"DATA",0,0,'L',0);
$pdf->Cell(40,5,"HORAS APONTADAS",0,0,'C',0);
$pdf->Cell(40,5,"HORAS APROVADAS",0,1,'C',0);
$pdf->SetFont('Arial','',8);

/*
$sql = "SELECT AJK_DATA, SUM(AJK_HQUANT) AS TOTAL_APO FROM AJK010 ";
$sql .= "WHERE AJK010.D_E_L_E_T_ = '' ";
$sql .= "AND AJK010.AJK_CTRRVS = '1' ";
$sql .= "AND AJK010.AJK_RECURS = 'FUN_".sprintf("%011d",$_POST["id_funcionario"])."' ";
$sql .= "AND AJK010.AJK_DATA BETWEEN '".$dataini_p."' AND '".$datafim_p."' ";
$sql .= "GROUP BY AJK010.AJK_DATA";

$db->select($sql, 'MSSQL', true);

$TOTAL = NULL;

foreach($db->array_select as $regs)
{
	$sql = "SELECT SUM(AFU_HQUANT) AS TOTAL_APR FROM AFU010 ";
	$sql .= "WHERE AFU010.D_E_L_E_T_ = '' ";
	$sql .= "AND AFU010.AFU_CTRRVS = '1' ";
	$sql .= "AND AFU010.AFU_RECURS = 'FUN_".sprintf("%011d",$_POST["id_funcionario"])."' ";
	$sql .= "AND AFU010.AFU_DATA = '".$regs["AJK_DATA"]."' ";
	
	$db->select($sql, 'MSSQL', true);
	
	$regs1 = $db->array_select[0];
	
	$pdf->SetTextColor(0,0,0);

	if($regs1["TOTAL_APR"]>0)
	{
		$pdf->Cell(20,5,mysql_php(protheus_mysql($regs["AJK_DATA"])),0,0,'L',0);
		$pdf->Cell(40,5,$regs["TOTAL_APO"] ? $regs["TOTAL_APO"] : "0" ,0,0,'C',0);
		$pdf->Cell(40,5,$regs1["TOTAL_APR"] ? $regs1["TOTAL_APR"] : "0",0,1,'C',0);
	}
	else
	{
		$pdf->SetTextColor(255,0,0);
		$pdf->Cell(20,5,mysql_php(protheus_mysql($regs["AJK_DATA"])),0,0,'L',0);
		$pdf->Cell(40,5,$regs["TOTAL_APO"] ? $regs["TOTAL_APO"] : "0" ,0,0,'C',0);
		$pdf->Cell(40,5,$regs1["TOTAL_APR"] ? $regs1["TOTAL_APR"] : "0",0,1,'C',0);
	}
	
	$TOTAL[0]+= $regs["TOTAL_APO"];
	$TOTAL[1]+= $regs1["TOTAL_APR"];

}

*/

$pdf->SetTextColor(0,0,0);

$pdf->ln(5);
$pdf->SetFont('Arial','B',8);
$pdf->Cell(20,5,"TOTAL:",0,0,'R',0);
$pdf->SetFont('Arial','',8);

$pdf->Cell(40,5,$TOTAL[0] ? $TOTAL[0] : "0",0,0,'C',0);

$pdf->Cell(40,5,$TOTAL[1] ? $TOTAL[1] : "0",0,1,'C',0);

$pdf->Output('APONTAMENTOS_'.date('dmYhis').'.pdf', 'D');

?>