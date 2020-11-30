<?php
/*
		Relat�rio de funcionario x OS x apontamentos	
		
		Criado por Carlos Abreu / Ot�vio Pamplon ia
		
		local/Nome do arquivo:
		../planejamento/relatorios/rel_controle_funcxosxconthoras.php
		
		Vers�o 0 --> VERS�O INICIAL : 02/03/2006		
		Versao 1 --> atualiza��o classe banco de dados - 22/01/2015 - Carlos Abreu
		Vers�o 2 --> Inclus�o dos campos reg_del nas consultas - 20/11/2017 - Carlos Abreu
*/

require_once(implode(DIRECTORY_SEPARATOR,array('..','..','config.inc.php')));

require_once(INCLUDE_DIR."include_pdf.inc.php");

// RELAT�RIO DE HH / OS
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
	$this->SetXY(25,40);
}

//Page footer
function Footer()
{

}
}

//Instanciation of inherited class
$pdf=new PDF('p','mm',A4);
$pdf->SetAutoPageBreak(true,25);
$pdf->SetMargins(25,15);
$pdf->SetLineWidth(0.5);

$db = new banco_dados;

//Seta o cabeçalho
$pdf->departamento="PLANEJAMENTO";
$pdf->titulo="EQUIPES DE TRABALHO";
$pdf->setor="PLN";
$pdf->codigodoc="106"; //"00"; //"02";
$pdf->codigo="0"; //Numero OS
$pdf->setorextenso=$setor; //"INFORMATICA"
$pdf->emissao=date('d/m/Y');
$pdf->versao_documento=$data_ini . " � " . $datafim;

$pdf->AliasNbPages();
$pdf->AddPage();

$pdf->SetXY(25,40);
$pdf->SetFont('Arial','B',9);
$pdf->Ln(5);

$data_ini = php_mysql($_POST["data_ini"]);
$datafim = php_mysql($_POST["datafim"]);

$sql = "SELECT funcionario FROM ".DATABASE.".funcionarios ";
$sql .= "WHERE funcionarios.id_funcionario = '".$_POST["escolhaos"]."' ";
$sql .= "AND funcionarios.reg_del = 0 ";

$db->select($sql,'MYSQL',true);

$regs = $db->array_select[0];

$pdf->Cell(100,5,'COORDENADOR: '.$regs["funcionario"],0,1);

$pdf->Cell(25,5,'OS',0,0);
$pdf->Cell(70,5,'FUNCION�RIO',0,0);
$pdf->Cell(50,5,'DISCIPLINA',0,0);
$pdf->Cell(25,5,'CATEGORIA',0,1);

$sql = "SELECT * FROM ".DATABASE.".apontamento_horas, ".DATABASE.".rh_funcoes, ".DATABASE.".OS, ".DATABASE.".setores, ".DATABASE.".ordem_servico_status, ".DATABASE.".funcionarios ";
$sql .= "WHERE OS.id_cod_coord = '" . $_POST["escolhaos"] . "' ";
$sql .= "AND apontamento_horas.reg_del = 0 ";
$sql .= "AND rh_funcoes.reg_del = 0 ";
$sql .= "AND OS.reg_del = 0 ";
$sql .= "AND setores.reg_del = 0 ";
$sql .= "AND ordem_servico_status.reg_del = 0 ";
$sql .= "AND funcionarios.reg_del = 0 ";
$sql .= "AND OS.id_os = apontamento_horas.id_os ";
$sql .= "AND apontamento_horas.id_funcionario = funcionarios.id_funcionario ";
$sql .= "AND funcionarios.id_funcao = rh_funcoes.id_funcao ";
$sql .= "AND OS.id_os_status = ordem_servico_status.id_os_status ";
$sql .= "AND ordem_servico_status.id_os_status NOT IN (2,3,8,9,12) ";
$sql .= "AND funcionarios.situacao NOT IN ('DESLIGADO','CANCELADODVM','CANCELADO') ";
$sql .= "AND funcionarios.id_setor = setores.id_setor ";
$sql .= "AND apontamento_horas.data BETWEEN '" . $data_ini ."' AND '" . $datafim ."' ";
$sql .= "GROUP BY apontamento_horas.id_funcionario ";
$sql .= "ORDER BY os.os, rh_funcoes.ordem, funcionario ";

$db->select($sql,'MYSQL',true);

$array_horas = $db->array_select;

foreach ($array_horas as $regconth)
{
	if($regconth["os"]!= $osant)
	{
		
		if($pdf->GetY()>250)
		{
			$pdf->AddPage();
			$pdf->SetFont('Arial','B',9);
			
			$sql = "SELECT funcionario FROM ".DATABASE.".funcionarios ";
			$sql .= "WHERE funcionarios.id_funcionario = '".$regconth["id_cod_coord"]."' ";
			$sql .= "AND funcionarios.reg_del = 0 ";
			
			$db->select($sql,'MYSQL',true);
			
			$regs = $db->array_select[0];
			
			$pdf->Cell(100,5,'COORDENADOR: '.$regs["funcionario"],0,1);
			$pdf->Cell(25,5,'OS',0,0);
			$pdf->Cell(70,5,'FUNCION�RIO',0,0);
			$pdf->Cell(50,5,'DISCIPLINA',0,0);
			$pdf->Cell(25,5,'CATEGORIA',0,1);
			
		}

		$os = sprintf("%05d",$regconth["os"]);		
	
		$pdf->SetFont('Arial','B',8);
		$pdf->Ln(5);
		$pdf->Cell(170,5,"OS: ".$os . " - " . $regconth["descricao"] . " - " . $regconth["os_status"],0,1,'L',0);
		$pdf->Ln(2);
	}
	$pdf->SetFont('Arial','',7);
	$pdf->Cell(25,5,'',0,0);
	$pdf->Cell(70,5,$regconth["funcionario"],0,0);
	$pdf->Cell(50,5,$regconth["setor"],0,0);
	$pdf->Cell(25,5,$regconth["categoria"],0,1);
	
	$osant = $regconth["os"];

}

$pdf->Output('CONTROLE_FUNCIONARIOS_OS_'.date('dmYhis').'.pdf', 'D');
?>