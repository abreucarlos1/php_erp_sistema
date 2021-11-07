<?php
/*
		Relatório Horas Adicionais x supervisor
		
		Criado por Carlos Abreu
		
		local/Nome do arquivo:		
		../planejamento/relatorios/rel_horas_adicionais_supervisor.php
		
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

$pdf=new PDF('p','mm',A4);
$pdf->SetAutoPageBreak(true,15);
$pdf->SetMargins(25,15);
$pdf->SetLineWidth(0.2);

$db = new banco_dados;

$pdf->departamento=NOME_EMPRESA;
$pdf->titulo="RELATÓRIO SUPERVISÃO / HORAS ADICIONAIS";
$pdf->setor="SUP";
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
			$ano=date('Y'); //retirado "-1" 07/02/2008
			$data_ini = "26/" . sprintf("%02d",$mesant) . "/" . $ano;
			$datafim = "25/" . $_POST["mes"] . "/" . $ano;
		}
	break;
	
	case "periodo":
		
		$data_ini = $_POST["dataini"];
		$datafim = $_POST["datafim"];
		
	break;
	
	case "semana":
	
		ajustadata($_POST["semana"],$data_ini,$datafim);
	
	break;
}



$pdf->versao_documento=$data_ini . " á " . $datafim;

$pdf->AliasNbPages();
$pdf->AddPage();

$pdf->SetFont('Arial','B',8);
$pdf->Cell(20,5,"DATA",0,0,'L',0);
$pdf->Cell(65,5,"SUPERVISOR",0,0,'L',0);
$pdf->Cell(65,5,"RECURSO",0,0,'L',0);
$pdf->Cell(20,5,"TRABALHO",0,1,'R',0);
$pdf->SetFont('Arial','',8);

$data_ini = php_mysql($data_ini);
$datafim = php_mysql($datafim);

$sql = "SELECT * FROM ".DATABASE.".horas_adicionais ";
$sql .= "WHERE data_ini BETWEEN '". $data_ini ."' AND '". $datafim ."' ";
$sql .= "AND horas_adicionais.reg_del = 0 ";
$sql .= "GROUP BY data_ini ";
$sql .= "ORDER BY data_ini ";

$db->select($sql,'MYSQL',true);

$array_horas = $db->array_select;

foreach ($array_horas as $regs)
{
	$pdf->Cell(20,5,mysql_php($regs["data_ini"]),0,1,'L',0);
	
	$sql = "SELECT * FROM ".DATABASE.".funcionarios, ".DATABASE.".horas_adicionais ";
	$sql .= "WHERE horas_adicionais.data_ini = '".$regs["data_ini"]."' ";
	$sql .= "AND horas_adicionais.reg_del = 0 ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	$sql .= "AND horas_adicionais.id_solicitante = funcionarios.id_funcionario ";
	$sql .= "GROUP BY horas_adicionais.id_solicitante ";
	$sql .= "ORDER BY funcionario ";
	
	$db->select($sql,'MYSQL',true);
	
	$array_func = $db->array_select;

	foreach($array_func as $regs_sol)
	{
		$pdf->Cell(20,5,"",0,0,'L',0);
		
		$pdf->HCell(65,5,mysql_php($regs_sol["funcionario"]),0,1,'L',0);
		
		$sql = "SELECT * FROM ".DATABASE.".funcionarios, ".DATABASE.".horas_adicionais ";
		$sql .= "WHERE horas_adicionais.id_solicitante = '".$regs_sol["id_solicitante"]."' ";
		$sql .= "AND horas_adicionais.reg_del = 0 ";
		$sql .= "AND funcionarios.reg_del = 0 ";
		$sql .= "AND data_ini = '". $regs_sol["data_ini"] ."' ";
		$sql .= "AND horas_adicionais.id_funcionario = funcionarios.id_funcionario ";
		$sql .= "GROUP BY funcionario ";
		$sql .= "ORDER BY funcionario ";
		
		$db->select($sql,'MYSQL',true);

		foreach($db->array_select as $regs_fun)
		{		
			$pdf->Cell(85,5,"",0,0,'L',0);
			
			$pdf->HCell(65,5,$regs_fun["funcionario"],0,0,'L',0);
			
			switch ($regs_fun["trabalho"])
			{
				case "1": 
					$trabalho = "EMPRESA";
				break;
				
				case "2": 
					$trabalho = "EM CASA";
				break;
			}
			
			$pdf->Cell(20,5,$trabalho,0,1,'R',0);
		}
		
		$pdf->Line(25,$pdf->GetY(),195,$pdf->GetY());
		
	}	
}

$pdf->Output('HORAS_ADICIONAIS_SUPERVISOR_'.date('dmYhis').'.pdf', 'D');

?>