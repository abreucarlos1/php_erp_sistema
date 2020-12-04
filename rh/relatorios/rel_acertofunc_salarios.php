<?php
/*
		Relatorio Acerto salarios
		
		Criado por Carlos Abreu 
		
		local/Nome do arquivo:
		../rh/relatorios/rel_acertofunc_salarios.php
		
		Versão 0 --> VERSÃO INICIAL - 04/05/2016
		Versão 1 --> 28/03/2006
		Versão 2 --> Inclusão dos campos reg_del nas consultas - 27/11/2017 - Carlos Abreu	
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
	$this->SetXY(25,28); //43
	
	$this->SetFont('Arial','',8);
	$this->SetLineWidth(0.5);
	$this->SetDrawColor(128,128,128);
	$this->Ln(15);
	
	$this->SetFont('Arial','B',8);
	$this->Cell(65,5,"FUNCIONÁRIO",0,0,'L',0);
	$this->Cell(10);
	$this->Cell(55,5,"CARGO",0,0,'L',0);
	$this->Cell(10);
	$this->Cell(20,5,"CONTRATO",0,0,'L',0);
	$this->Cell(5);
	$this->Cell(25,5,"SAL. CLT (R$)",0,0,'L',0);
	$this->Cell(5);
	$this->Cell(25,5,"SAL. MENS. (R$)",0,0,'L',0);
	$this->Cell(5);
	$this->Cell(25,5,"VALOR/HORA (R$)",0,0,'L',0);	
	$this->Ln(5);
	
}

//Page footer
function Footer()
{
	$this->Line(25,190,280,190); //65
}
}

$db = new banco_dados;

$pdf=new PDF('l','mm',A4);
$pdf->SetAutoPageBreak(true,20);
$pdf->SetMargins(25,15);
$pdf->SetLineWidth(0.5);

$pdf->departamento=NOME_EMPRESA;
$pdf->titulo="RELATÓRIO DE TARIFAS";
$pdf->codigodoc="04";
$pdf->codigo=01; 

$pdf->emissao=date("d/m/Y");
$pdf->versao_documento="";
$pdf->AliasNbPages();
$pdf->AddPage();


$pdf->SetFont('Arial','',8);

$sql = "SELECT * FROM ".DATABASE.".funcionarios, ".DATABASE.".rh_funcoes ";
$sql .= "WHERE rh_funcoes.id_funcao = funcionarios.id_funcao ";
$sql .= "AND funcionarios.reg_del = 0 ";
$sql .= "AND rh_funcoes.reg_del = 0 ";
$sql .= "AND funcionarios.situacao NOT IN ('DESLIGADO','CANCELADO') ";
$sql .= "ORDER BY funcionarios.funcionario ";

$db->select($sql,'MYSQL',true);

$array_func = $db->array_select;	

//Se o mês for Janeiro, seta o ano passado.
if(date("m")==1)
{
	$ano = date("Y")-1;
}
else
{
	$ano = date("Y");
}

if(date('d')>=26)
{
	//Forma a data inicial do período
	$data_ini = $ano . "-" . sprintf("%02d",(date("m")-1)) . "-" . "26";
	//Forma a data final do período
	$datafim = date("Y") . "-" . sprintf("%02d",(date("m"))) . "-" . "25";
	
}
else
{
	//Forma a data inicial do período
	if(date("m")=='01')
	{
		$mes = 12;
	}
	else
	{
		$mes = sprintf("%02d",(date("m")));
	}
	$data_ini = $ano . "-" . $mes . "-" . "26";
	//Forma a data final do período
	$datafim = date("Y") . "-" . sprintf("%02d",(date("m")+1)) . "-" . "25";
}


foreach ($array_func as $cont_salarios)
{
	//Salario atual
	$sql = "SELECT * FROM ".DATABASE.".salarios ";
	$sql .= "WHERE salarios.id_funcionario = '" . $cont_salarios["id_funcionario"] . "' ";
	$sql .= "AND salarios.reg_del = 0 ";
	$sql .= "AND DATE_FORMAT(data , '%Y%m%d' ) <= '".str_replace("-","",$datafim)."' ";
	$sql .= "ORDER BY id_salario DESC, data DESC LIMIT 1 "; //alterada ordenação: Otávio 27/02/2009
	
	$db->select($sql,'MYSQL',true);
	
	$cont2 = $db->array_select[0];
	
	$pdf->HCell(65,5,$cont_salarios["funcionario"],0,0,'L',0);

	$pdf->Cell(10);
	
	$pdf->HCell(55,5,$cont_salarios["descricao"],0,0,'L',0);

	$pdf->Cell(10);
	
	$pdf->HCell(20,5,$cont2[" tipo_contrato"],0,0,'L',0);

	$pdf->Cell(5);

	$pdf->Cell(20,5,number_format($cont2["salario_clt"],2,",","."),0,0,'R',0);

	$pdf->Cell(5);

	$pdf->Cell(25,5,number_format($cont2["salario_mensalista"],2,",","."),0,0,'R',0);

	$pdf->Cell(5);

	$pdf->Cell(25,5,number_format($cont2["salario_hora"],2,",","."),0,1,'R',0);

	$pdf->SetFont('Arial','',8);
}

$pdf->Ln(2);

$pdf->Line(125,$pdf->GetY(),280,$pdf->GetY());

$pdf->Cell(100,5,"",0,'L',0);

$pdf->SetFont('Arial','B',8);

$pdf->Ln(50);

$pdf->Output('RELATORIO_ACERTO_TARIFAS_'.date('dmYhis').'.pdf', 'D');

?> 