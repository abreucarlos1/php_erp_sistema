<?php
/*
	  Relatório Fechamento Folha - IR	
	  
	  Criado por Carlos Abreu / Otávio Pamplona
	  
	  local/Nome do arquivo:
	  ../financeiro/relatorios/rel_fechamentofolha_ir.php
	  
	  Versão 0 --> VERSÃO INICIAL - 14/07/2007
	  Versão 1 --> Atualização lay-out - 23/06/2014 - Carlos Abreu
	  Versão 2 --> atualização classe banco de dados - 22/01/2015 - Carlos Abreu
	  Versão 3 --> Inclusão dos campos reg_del nas consultas - 20/11/2017 - Carlos Abreu		
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

	$this->Cell(20,5,"NR. NF",0,0,'L',0);

	$this->Cell(90,5,"EMPRESA",0,0,'L',0);
	
	$this->Cell(80,5,"FUNCIONÁRIO",0,0,'L',0);
	
	$this->Cell(30,5,"VALOR NF",0,0,'L',0);

	$this->Cell(30,5,"VALOR IR (1,5%)",0,0,'L',0);
	
	$this->Ln(5);
	
}

function Footer()
{
	$this->Line(25,190,280,190);
}
}

$db = new banco_dados;

$pdf=new PDF('l','mm',A4);
$pdf->SetAutoPageBreak(true,20);
$pdf->SetMargins(25,15);
$pdf->SetLineWidth(0.5);

$pdf->departamento=NOME_EMPRESA;

$pdf->titulo="RELATÓRIO DE NOTAS FISCAIS - RETENÇÃO IR (1,5%)";

$pdf->codigodoc="04"; //"00";

$pdf->codigo=01; //Numero OS

if($_POST["periodo"])
{
	$datas = explode(",",($_POST["periodo"]));
	
	$data_ini = substr($datas[0],-2,2) . "/" . substr($datas[0],0,4);
	
	$datafim = substr($datas[1],-2,2) . "/" . substr($datas[1],0,4);
	
	$filtro = "AND fechamento_folha.periodo = '" . $_POST["periodo"] . "' ";

}
else
{	
	$mespassado_stamp = mktime(0,0,0,date("m"),0,date("Y"));
	
	$data_ini = date("m", $mespassado_stamp) . "/" . date("Y");
	
	$datafim = date("m/Y");
	
	$filtro = "AND fechamento_folha.periodo = '" . date("Y-m") . "' ";
}

$pdf->emissao=date("d/m/Y");

$pdf->versao_documento="Período: " . $data_ini . " á " . $datafim;

$pdf->AliasNbPages();

$pdf->AddPage();

$pdf->SetFont('Arial','',8);

$sql = "SELECT * FROM ".DATABASE.".nf_funcionarios, ".DATABASE.".fechamento_folha ";
$sql .= "WHERE nf_funcionarios.id_fechamento = fechamento_folha.id_fechamento ";
$sql .= "AND nf_funcionarios.reg_del = 0 ";
$sql .= "AND fechamento_folha.reg_del = 0 ";
$sql .= "AND fechamento_folha.periodo = '" . $_POST["periodo"] . "' ";

$db->select($sql,'MYSQL',true);

foreach($db->array_select as $cont_periodo)
{
	if($_POST["chk_" . $cont_periodo["id_nf_funcionario"]]=="1")
	{
		$array_exc[] = $cont_periodo["id_nf_funcionario"];
	}
}

if(count($array_exc)>0)
{
	$filtro_notas .= "AND nf_funcionarios.id_nf_funcionario NOT IN (" . implode(",",$array_exc) . ") ";
}

$vlr_total_ir = "0.00";
$vlr_total_outros = "0.00";
$valor_ir = "0.00";
$valor_outros = "0.00";

$sql = "SELECT * FROM ".DATABASE.".nf_funcionarios, ".DATABASE.".empresa_funcionarios, ".DATABASE.".funcionarios, ".DATABASE.".fechamento_folha ";
$sql .= "WHERE nf_funcionarios.id_fechamento = fechamento_folha.id_fechamento ";
$sql .= "AND nf_funcionarios.reg_del = 0 ";
$sql .= "AND empresa_funcionarios.reg_del = 0 ";
$sql .= "AND funcionarios.reg_del = 0 ";
$sql .= "AND fechamento_folha.reg_del = 0 ";
$sql .= "AND empresa_funcionarios.id_empfunc = funcionarios.id_empfunc ";
$sql .= "AND funcionarios.id_funcionario = fechamento_folha.id_funcionario ";
$sql .= $filtro;
$sql .= $filtro_notas;
$sql .= "ORDER BY funcionarios.funcionario, fechamento_folha.data_ini, fechamento_folha.data_fim ";

$db->select($sql,'MYSQL',true);

foreach($db->array_select as $regs)
{	
	$funcionario = maiusculas($regs["funcionario"]);
	
	$periodo = mysql_php($regs["data_ini"]) . " - " . mysql_php($regs["data_fim"]);
	
	$htotal_normal = $shora_normal;
	
	$htotal_adicional = $shora_adicional;
	
	$valor_pis_cofins_csl = $regs["valor_pcc"];
	
	$valor_medicao = $regs["valor_medicao"];
	
	$valor_bruto = $regs["valor_total"];
	
	$valor_liquido = $regs["valor_pagamento"];
	
	$nome_empresa = maiusculas($regs["empresa_func"]);
	
	$nr_nota_fiscal = $regs["nf_numero"];
	
	$vlr_nota_fiscal = $regs["nf_valor"];
	
	$dt_nota_fiscal = mysql_php($regs["nf_emissao"]);

	$valor_ir = ($regs["nf_valor"]*1.5)/100;
	
	$valor_outros = ($regs["nf_valor"]*4.65)/100;
	
	$pdf->HCell(20,5,$nr_nota_fiscal,0,0,'L',0);
	
	$pdf->HCell(90,5,$nome_empresa,0,0,'L',0);
	
	$pdf->HCell(80,5,$funcionario,0,0,'L',0);
	
	$pdf->Cell(30,5,"R$ " . formatavalor($vlr_nota_fiscal),0,0,'L',0);
	
	$vlr_total_nf += $vlr_nota_fiscal;

	//Verifica se o tipo de Relatório selecionado é de IR e se existiu imposto para esse fechamento.
	if($regs["valor_imposto"]!=="0.00")
	{
		
		$pdf->Cell(30,5,"R$ " . formatavalor(number_format($valor_ir,2,".","")),0,0,'L',0);
		//Soma o valor total do IR
		$vlr_total_ir += $valor_ir;

	}
	else
	{
		$pdf->Cell(30,5,"R$ 0,00",0,0,'L',0);			
	}
	
	$pdf->SetFont('Arial','',8);
	
	$pdf->Ln(5);	
}

$pdf->Ln(3);

$pdf->SetFont('Arial','B',8);

$pdf->Cell(190,5,"TOTAIS: ",0,'R',0);

$pdf->SetFont('Arial','',8);

$pdf->HCell(30,5,"R$ " . formatavalor(number_format($vlr_total_nf,2,".","")),0,0,'L',0);

$pdf->SetFont('Arial','',8);

$pdf->Line(203,$pdf->GetY(),279,$pdf->GetY());

if($vlr_total_ir!=0)
{
	$pdf->HCell(21,5,"R$ " . formatavalor(number_format($vlr_total_ir,2,".","")),0,0,'L',0);
}
else
{
	$pdf->HCell(21,5,"R$ 0,00",0,0,'L',0);
}

$pdf->Ln(50);

$pdf->Output('FECHAMENTO_IR_'.date('dmYHmi').'.pdf','D');

//Grava o arquivo PDF em uma pasta, no formato "TIPO AnoMesInicial-AnoMesFinal DataGeracao.pdf".
$pdf->Output(DOCUMENTOS_FINANCEIRO.COMPROVANTES_FECHAMENTO. "IR_" . str_replace("-","",$datas[0]) . "-" . str_replace("-","",$datas[1]) . " " . date("dmY") . '.pdf','F');

?> 