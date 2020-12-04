<?php
/*
		Relatório funcionarios
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:		
		../supervisao/relatorios/rel_funcionarios.php
		
		Versão 0 --> VERSÃO INICIAL - 02/03/2006
		Versão 1 --> Atualização classe banco de dados - 22/01/2015 - Carlos Abreu
		Versão 2 --> Inclusão dos campos reg_del nas consultas - 20/11/2017 - Carlos Abreu	
*/

require_once(implode(DIRECTORY_SEPARATOR,array('..','..','config.inc.php')));

require_once(INCLUDE_DIR."include_pdf.inc.php");

class PDF extends FPDF
{

//Page header
function Header()
{	
	$this->Image(DIR_IMAGENS.'logo_pb.png',10,16,40);
	
	$this->Ln(1);
	$this->SetFont('Arial','',6);
	$this->SetFont('Arial','B',12);
	$this->Cell(271,8,$this->Titulo(),0,1,'C',0);
	
	$this->SetFont('Arial','B',8);
	$this->Cell(25,5,"DISCIPLINA",1,0,'C',0);
	$this->Cell(25,5,"C�DIGO",1,0,'C',0);
	$this->Cell(70,5,"FUNCIONÁRIO",1,0,'C',0);
	$this->Cell(50,5,"FUN��O",1,1,'C',0);
	
	$this->SetXY(10,30);
}

//Page footer
function Footer()
{

}
}


//Instanciation of inherited class
$pdf=new PDF('P','mm',A4);
$pdf->SetAutoPageBreak(true,25);
$pdf->SetMargins(10,15);
$pdf->SetLineWidth(0.2);
$pdf->SetDrawColor(0,0,0);

$pdf->titulo="FUNCIONÁRIOS POR FUN��O";

$pdf->AliasNbPages();

$pdf->AddPage();

$db = new banco_dados;

$sql_filtro = "";

if($_POST["disciplina"]==-1)
{
	$sql_filtro = "";
}
else
{
	$sql_filtro = "AND setores.id_setor = '".$_POST["disciplina"]."' ";
}

$pdf->SetFont('Arial','',6);

$sql = "SELECT * FROM ".DATABASE.".funcionarios, ".DATABASE.".setores, ".DATABASE.".rh_funcoes ";
$sql .= "LEFT JOIN ".DATABASE.".rh_cargos ON (rh_funcoes.id_cargo_grupo = rh_cargos.id_cargo_grupo AND rh_cargos.reg_del = 0) ";
$sql .= "WHERE funcionarios.id_setor = setores.id_setor ";
$sql .= "AND funcionarios.reg_del = 0 ";
$sql .= "AND setores.reg_del = 0 ";
$sql .= "AND rh_funcoes.reg_del = 0 ";
$sql .= $sql_filtro;
$sql .= "AND funcionarios.situacao = 'ATIVO' ";
$sql .= "AND funcionarios.nivel_atuacao <> 'P' ";
$sql .= "AND funcionarios.id_funcao = rh_funcoes.id_funcao ";
$sql .= "GROUP BY setor, funcionario ";
$sql .= "ORDER BY setor, funcionario ";

$db->select($sql,'MYSQL',true);	

foreach($db->array_select as $regs)
{	
	
	if($setor != $regs["id_setor"])
	{		
		$pdf->SetFont('Arial','B',6);
		$pdf->Cell(25,5,$regs["setor"],0,1,'L',0);
		$pdf->SetFont('Arial','',6);
	}
	
	$setor = $regs["id_setor"];
	
	$pdf->Cell(25,5,"",0,0,'L',0);
	$pdf->Cell(25,5,"FUN_".sprintf("%011d",$regs["id_funcionario"]),0,0,'L',0);
	$pdf->Cell(70,5,$regs["funcionario"],0,0,'L',0);
	$pdf->Cell(50,5,$regs["grupo"],0,1,'L',0);
	
}

$pdf->Output('RELATORIO_FUNCIONARIOS_'.date('dmYhis').'.pdf', 'D');
?> 