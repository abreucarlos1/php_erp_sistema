<?php
/*
		Relat�rio Resumo Hh
		
		Criado por Carlos Abreu / Ot�vio Pamplon ia
		
		local/Nome do arquivo:		
		../planejamento/relatorios/rel_resumo_hh_funcionarios.php
		
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

}

//Page footer
function Footer()
{

	
}
}

$data_ini = $_POST['data_ini'];
$datafim = $_POST['datafim'];

$pdf=new PDF('p','mm',A4);
$pdf->SetAutoPageBreak(true,30);
$pdf->SetMargins(25,15);
$pdf->SetLineWidth(0.5);

$db = new banco_dados;

$pdf->departamento="ADMINISTRA��O";
$pdf->titulo="RELAT�RIO HH / FUNCION�RIO ";
$pdf->setor="ADM";
$pdf->codigodoc="109"; //"00"; //"02";
$pdf->codigo="01"; //Numero OS
$pdf->setorextenso=$setor; //"INFORMATICA"
$pdf->emissao=date("d/m/Y");
$pdf->versao_documento=$data_ini . " � " . $datafim;

$sql = "SELECT * FROM ".DATABASE.".funcionarios ";
$sql .= "WHERE id_funcionario = '" . $_POST["escolhafuncionario"] . "' ";
$sql .= "AND funcionarios.reg_del = 0 ";

$db->select($sql,'MYSQL',true);

$regs = $db->array_select[0];

$pdf->AliasNbPages();
$pdf->AddPage();

$pdf->SetFont('Arial','B',8);
$pdf->Cell(170,5,"FUNCION�RIO: " . $regs["funcionario"],0,1,'L',0);
$pdf->SetLineWidth(0.5);
$pdf->SetDrawColor(128,128,128);
$pdf->Line(25,50,195,50);
$pdf->Ln(5);

$pdf->SetFont('Arial','B',8);
$pdf->Cell(20,5,"OS",0,0,'L',0);
$pdf->Cell(20,5,"HORAS",0,1,'R',0);
$pdf->SetFont('Arial','',8);

$data_ini = php_mysql($data_ini);
$datafim = php_mysql($datafim);

$sql = "SELECT *, SUM(TIME_TO_SEC(hora_normal+hora_adicional+hora_adicional_noturna)) AS HT ";
$sql .= "FROM ".DATABASE.".apontamento_horas, ".DATABASE.".OS ";
$sql .= "WHERE apontamento_horas.id_funcionario = '" . $_POST["escolhafuncionario"] . "' ";
$sql .= "AND apontamento_horas.reg_del = 0 ";
$sql .= "AND OS.reg_del = 0 ";
$sql .= "AND apontamento_horas.id_os = OS.id_os ";
$sql .= "AND data BETWEEN '".$data_ini."' AND '".$datafim."' ";
$sql .= "GROUP BY OS.id_os ";
$sql .= "ORDER BY os.os ";

$db->select($sql,'MYSQL',true);

foreach ($db->array_select as $regs)
{
		$horanormal = explode(":",sec_to_time($regs["HT"]));
		$pdf->Cell(20,5,sprintf("%05d",$regs["os"]),0,0,'L',0);
		$pdf->Cell(20,5,$horanormal[0] . ":" . $horanormal[1],0,1,'R',0);
}

$sql = "SELECT SUM(TIME_TO_SEC(hora_normal+hora_adicional+hora_adicional_noturna)) AS TOTAL ";
$sql .= "FROM ".DATABASE.".apontamento_horas ";
$sql .= "WHERE apontamento_horas.id_funcionario = '" . $_POST["escolhafuncionario"]. "' ";
$sql .= "AND apontamento_horas.reg_del = 0 ";
$sql .= "AND data BETWEEN '".$data_ini."' AND '".$datafim."' ";

$db->select($sql,'MYSQL',true);

$regs = $db->array_select[0];

$total = explode(":",sec_to_time($regs["TOTAL"]));

$pdf->Ln(10);

$pdf->SetFont('Arial','B',8);
$pdf->Cell(30,5,"TOTAL:",0,0,'R',0);
$pdf->SetFont('Arial','',8);

$pdf->Cell(20,5,$total[0]. ":" . $total[1],0,1,'R',0);

$pdf->Output('RESUMO_HH_FUNCIONARIOS_'.date('dmYhis').'.pdf', 'D');

?> 