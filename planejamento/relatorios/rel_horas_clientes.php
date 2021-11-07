<?php
/*
		Relatório Horas x Clientes
		
		Criado por Carlos Abreu
		
		local/Nome do arquivo:		
		../planejamento/relatorios/rel_horas_clientes.php
		
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
	$this->SetXY(25,40);
	
	$this->SetFont('Arial','B',8);
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

$pdf->departamento=NOME_EMPRESA;
$pdf->titulo="RELATÓRIO HORAS CLIENTES";
$pdf->setor="PLN";
$pdf->codigodoc="000"; //"00"; //"02";
$pdf->codigo="01"; //Numero OS
$pdf->setorextenso=$setor; //"INFORMATICA"
$pdf->emissao=date("d/m/Y");
	
$data_ini = "01/" . $_POST["mes"] . "/" . date('Y');

$datafim = date("d/m/Y",mktime(0, 0, 0, $_POST["mes"]+1, 0, date('Y')));

$pdf->versao_documento=$data_ini . " á " . $datafim;

$sql = "SELECT * FROM ".DATABASE.".local ";
$sql .= "WHERE local.reg_del = 0 ";
$sql .= "ORDER BY descricao ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}

foreach ($db->array_select as $regs)
{
	if($_POST["chk_".$regs["id_local"]]==1)
	{
		$setor[$regs["id_local"]] = $regs["descricao"];
	}

}

$pdf->AliasNbPages();
$pdf->AddPage();

$pdf->SetFont('Arial','B',8);
$pdf->Cell(55,5,"LOCAL DE TRABALHO",0,0,'L',0);
$pdf->Cell(95,5,"FUNCIONÁRIO",0,0,'L',0);
$pdf->Cell(20,5,"HH",0,1,'L',0);

$pdf->SetFont('Arial','',8);

$data_ini = php_mysql($data_ini);
$datafim = php_mysql($datafim);

foreach ($setor as $chave=>$valor)
{
	
	$sql = "SELECT *, SUM(TIME_TO_SEC(hora_normal+hora_adicional+hora_adicional_noturna)) AS HH ";
	$sql .= "FROM ".DATABASE.".apontamento_horas, ".DATABASE.".funcionarios ";
	$sql .= "WHERE apontamento_horas.id_local_trabalho_externo ='" . $chave . "' ";
	$sql .= "AND apontamento_horas.reg_del = 0 ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	$sql .= "AND funcionarios.id_funcionario = apontamento_horas.id_funcionario ";
	$sql .= "AND data BETWEEN '". $data_ini ."' AND '". $datafim ."' ";
	$sql .= "GROUP BY funcionarios.id_funcionario, apontamento_horas.id_local_trabalho_externo ";
	$sql .= "ORDER BY funcionarios.funcionario ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		die($db->erro);
	}
	
	$total_horas[$chave] = 0;
	$total_mo[$chave] = 0;
	
	if($db->numero_registros>0)
	{
		$pdf->HCell(55,5,$valor,0,1,'L',0);
		
		$total_horas[$chave] = 0;
		$total_mo[$chave] = 0;
		
		foreach($db->array_select as $regs1)
		{
			$total_horas[$chave]+= $regs1["HH"];
			
			if($regs1["HH"]>0)
			{
				$total_mo[$chave]+=1;
			}
			
			$pdf->Cell(55,5,"",0,0,'L',0);
			$pdf->HCell(95,5,$regs1["funcionario"],0,0,'L',0);
			$pdf->HCell(20,5, substr(sec_to_time($regs1["HH"]),0,count(sec_to_time($regs1["HH"]))-4),0,1,'L',0);
		}

		$pdf->Cell(30);
		$pdf->SetFont('Arial','',5);
		$pdf->Cell(45,5,"Nº FUNCIONÁRIOS: ",0,0,'R',0);
		$pdf->HCell(20,5, $total_mo[$chave],0,0,'L',0);		

		$pdf->SetFont('Arial','B',8);
		$pdf->Cell(45,5,"TOTAL:",0,0,'R',0);
		$pdf->SetFont('Arial','',8);		
		$pdf->HCell(20,5, substr(sec_to_time($total_horas[$chave]),0,count(sec_to_time($total_horas[$chave]))-4),0,1,'R',0);
	}
		
}

$pdf->Output('HORAS_CLIENTES_'.date('dmYhis').'.pdf', 'D');

?> 