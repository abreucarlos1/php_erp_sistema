<?php
/*
	  Relatório Fechamento Folha	
	  
	  Criado por Carlos Abreu
	  
	  local/Nome do arquivo:
	  ../financeiro/relatorios/rel_fechamentofolha.php
	  
	  Versão 0 --> VERSÃO INICIAL - 14/07/2007
	  Versão 1 --> Atualização lay-out - 23/06/2014 - Carlos Abreu
	  Versão 2 --> atualização classe banco de dados - 22/01/2015 - Carlos Abreu
	  Versão 3 --> Inclusão dos campos reg_del nas consultas - 20/11/2017 - Carlos Abreu		
*/

require_once(implode(DIRECTORY_SEPARATOR,array('..','..','config.inc.php')));

require_once(INCLUDE_DIR."include_pdf.inc.php");

if($_POST["periodo"])
{
	$datas = explode(",",$_POST["periodo"]);
	
	$data_ini = substr($datas[0],-2,2) . "/" . substr($datas[0],0,4);
	$datafim = substr($datas[1],-2,2) . "/" . substr($datas[1],0,4);
	
	$data_parcela_ini = $datas[0] . "-26";
	$data_parcela_fim = $datas[1] . "-25";
	
	$filtro = "AND fechamento_folha.periodo='" . $_POST["periodo"] . "' ";
}
else
{	
	$mespassado_stamp = mktime(0,0,0,date("m"),0,date("Y"));
	$data_ini = date("m", $mespassado_stamp) . "/" . date("Y");
	$datafim = date("m/Y");
	
	$filtro = "AND SUBSTRING(fechamento_folha.periodo,9,7) = '" . date("Y-m") . "' ";
}

$db = new banco_dados;

$sql = "SELECT id_funcionario, valor_parcela FROM ".DATABASE.".adiantamento_emprestimo, ".DATABASE.".parcelas_emprestimo ";
$sql .= "WHERE adiantamento_emprestimo.id_adiantamento_emprestimo = parcelas_emprestimo.id_adiantamento_emprestimo ";
$sql .= "AND adiantamento_emprestimo.reg_del = 0 ";
$sql .= "AND parcelas_emprestimo.reg_del = 0 ";
$sql .= "AND parcelas_emprestimo.data_parcela BETWEEN '" . $data_parcela_ini . "' AND '" . $data_parcela_fim . "' ";

$db->select($sql,'MYSQL',true);

foreach($db->array_select as $reg_parcelas)
{
	$array_parcelas[$reg_parcelas["id_funcionario"]] += $reg_parcelas["valor_parcela"];
}

class PDF extends FPDF
{
//Page header
function Header()
{    
	$this->Image(DIR_IMAGENS.'logo_pb.png',16,16,40);
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
	$this->Line(15,40,280,40);
	$this->SetLineWidth(0.5);
	$this->SetXY(15,28); 	
	$this->SetFont('Arial','',8);
	$this->SetLineWidth(0.5);	
	$this->SetDrawColor(128,128,128);
	$this->Ln(15);	
	$this->SetFont('Arial','B',8);
	
	$this->Cell(70,5,"FUNCIONÁRIO",0,0,'L',0);
	
	$this->Cell(15,5,"H. N.",0,0,'L',0);

	$this->Cell(15,5,"H. A.",0,0,'L',0);
	
	$this->Cell(15,5,"H. T.",0,0,'L',0);

	$this->Cell(25,5,"MEDIÇÃO",0,0,'L',0);
	
	$this->Cell(25,5,"VALOR BRUTO",0,0,'L',0);
	
	$this->Cell(15,5,"NF",0,0,'L',0);

	$this->Cell(20,5,"VALOR IR (1,5%)",0,0,'L',0);
	
	$this->Cell(30,5,"VALOR PCC (4,65%)",0,0,'R',0);
	
	$this->Cell(25,5,"VALOR LÍQUIDO",0,0,'R',0);
	
	$this->Ln(5);	
}

//Page footer
function Footer()
{
	$this->Line(15,190,280,190);

}
}

//Instanciation of inherited class
$pdf=new PDF('l','mm',A4);
$pdf->SetAutoPageBreak(true,20);
$pdf->SetMargins(15,15);
$pdf->SetLineWidth(0.5);

//Seta o cabeçalho
$pdf->departamento=NOME_EMPRESA;
$pdf->titulo="FECHAMENTO DA FOLHA DE PAGAMENTO";
$pdf->codigodoc="04"; //"00";
$pdf->codigo=01; //Numero OS

$pdf->emissao=date("d/m/Y");

$pdf->versao_documento="Período: " . $data_ini . " á " . $datafim;

$pdf->AliasNbPages();

$pdf->AddPage();

$pdf->SetFont('Arial','',8);

$sql = "SELECT * FROM ".DATABASE.".funcionarios, ".DATABASE.".fechamento_folha ";
$sql .= "WHERE funcionarios.id_funcionario = fechamento_folha.id_funcionario " . $filtro;
$sql .= "AND fechamento_folha.reg_del = 0 ";
$sql .= "AND funcionarios.reg_del = 0 ";
$sql .= "ORDER BY funcionarios.funcionario, fechamento_folha.data_ini, fechamento_folha.data_fim ";

$db->select($sql,'MYSQL',true);

$array_fechamento = $db->array_select;	

foreach($array_fechamento as $regs)
{
	$sql = "SELECT * FROM ".DATABASE.".nf_funcionarios ";
	$sql .= "WHERE nf_funcionarios.id_fechamento = '".$regs["id_fechamento"]."' ";
	$sql .= "AND nf_funcionarios.reg_del = 0 ";
	$sql .= "AND nf_funcionarios.nf_ajuda_custo = 0 ";
	
	$db->select($sql,'MYSQL',true);
	
	$regis = $db->array_select[0];

	//Formata as horas normais
	$ahora_normal = explode(":", $regs["total_horas_normais"]);
	$shora_normal = $ahora_normal[0] . ":" . $ahora_normal[1];
	
	//Formata as horas adicionais
	$ahora_adicional = explode(":", $regs["total_horas_adicionais"]);
	$shora_adicional = $ahora_adicional[0] . ":" . $ahora_adicional[1];
	
	$ht = time_to_sec($regs["total_horas_normais"])+time_to_sec($regs["total_horas_adicionais"]);	

	$funcionario = $regs["funcionario"];
	$periodo = mysql_php($regs["data_ini"]) . " - " . mysql_php($regs["data_fim"]);
	
	$htotal_normal = $shora_normal;
	
	$htotal_adicional = $shora_adicional;
	
	$totaisn += time_to_sec($regs["total_horas_normais"]);
	
	$totaisa += time_to_sec($regs["total_horas_adicionais"]);
	
	$total_ht += $ht;	
	
	$valor_ir = $regs["valor_imposto"];
	$valor_pis_cofins_csl = $regs["valor_pcc"];
	$valor_medicao = $regs["valor_medicao"];
	$valor_bruto = $regs["valor_total"];
	$valor_liquido = $regs["valor_pagamento"];
	$num_nf = $regis["nf_numero"];
	
	$pdf->HCell(70,5,$funcionario,0,0,'L',0);

	$pdf->Cell(15,5,$htotal_normal,0,0,'L',0);
	
	$pdf->Cell(15,5,$htotal_adicional,0,0,'L',0);
	
	$pdf->HCell(15,5,substr(sec_to_time($ht),0,count(sec_to_time($ht))-4),0,0,'L',0);
	
	$pdf->Cell(25,5,"R$ " . formatavalor($valor_medicao),0,0,'L',0);
	
	$pdf->Cell(25,5,"R$ " . formatavalor($valor_bruto),0,0,'L',0);
	
	$pdf->Cell(15,5,$num_nf,0,0,'L',0);
	
	$pdf->Cell(20,5,"R$ " . formatavalor($valor_ir),0,0,'R',0);

	$pdf->SetFont('Arial','',8);
	
	$pdf->Cell(30,5,"R$ " . formatavalor($valor_pis_cofins_csl),0,0,'R',0);
	
	$pdf->Cell(25,5,"R$ " . formatavalor($valor_liquido),0,1,'R',0);
	
	$soma_valor_medicao += + $valor_medicao;
	$soma_valor_bruto += + $valor_bruto;
	$soma_valor_liquido += + $valor_liquido;
	$soma_valor_ir += + $valor_ir;
	$soma_valor_pis_cofins_csl += + $valor_pis_cofins_csl;

}

$pdf->Ln(2);
$pdf->Line(10,$pdf->GetY(),280,$pdf->GetY());
$pdf->SetFont('Arial','B',8);		
$pdf->HCell(70,5,"TOTAIS: ",0,0,'L',0);
$pdf->SetFont('Arial','',8);

$pdf->HCell(15,5,substr(sec_to_time($totaisn),0,count(sec_to_time($totaisn))-4),0,0,'L',0);
$pdf->HCell(15,5,substr(sec_to_time($totaisa),0,count(sec_to_time($totaisa))-4),0,0,'L',0);
$pdf->HCell(15,5,substr(sec_to_time($total_ht),0,count(sec_to_time($total_ht))-4),0,0,'L',0);
$pdf->HCell(25,5,"R$ " . formatavalor(number_format($soma_valor_medicao,2,".","")),0,0,'L',0);
$pdf->HCell(45,5,"R$ " . formatavalor(number_format($soma_valor_bruto,2,".","")),0,0,'L',0);
$pdf->HCell(20,5,"R$ " . formatavalor(number_format($soma_valor_ir,2,".","")),0,0,'L',0);	
$pdf->HCell(25,5,"R$ " . formatavalor(number_format($soma_valor_pis_cofins_csl,2,".","")),0,0,'R',0);	
$pdf->HCell(25,5,"R$ " . formatavalor(number_format($soma_valor_liquido,2,".","")),0,0,'R',0);

$pdf->Ln(50);

$pdf->Output('FECHAMENTO_PJ_'.date('dmYHmi').'.pdf','D');

//Grava o arquivo PDF em uma pasta, no formato "TIPO AnoMesInicial-AnoMesFinal DataGeracao.pdf".
$pdf->Output(DOCUMENTOS_FINANCEIRO.COMPROVANTES_FECHAMENTO . "FECHAMENTO_" . str_replace("-","",$datas[0]) . "-" . str_replace("-","",$datas[1]) . " " . date("dmY") . '.pdf','F');

?> 