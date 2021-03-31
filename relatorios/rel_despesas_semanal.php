<?php
/*
		Relatório Controle Horas Semanal	
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		../relatorios/rel_despesas_semanal.php
		
		Versão 0 --> VERSÃO INICIAL - 14/07/2007
		
*/	
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));

require_once(INCLUDE_DIR."include_pdf.inc.php");

class PDF extends FPDF
{

function chkbox($checado = false)
{
	$x1 = $this->GetX();
	$y1 = $this->GetY();
	
	$x2 = $x1+3;
	$y2 = $y1+3;
	
	$this->SetLineWidth(0.1);
	//Seta a cor da linha
	$this->SetDrawColor(0,0,0);
	
	$this->Line($x1,$y1,$x2,$y1); //sup
	$this->Line($x1,$y2,$x2,$y2); //inf
	$this->Line($x1,$y1,$x1,$y2); //esq
	$this->Line($x2,$y1,$x2,$y2); //dir
	
	if($checado)
	{
		$this->SetLineWidth(0.5);
		
		$this->Line($x1+0.5,$y1+1.3,$x1+1.3,$y1+2.3);
		$this->Line($x1+1.3,$y1+2.3,$x2-0.3,$y1+0.3);
	}
	
	$this->SetY($y1-0.8);
	$this->SetX($x2-1);
	$this->Cell(2,3,' ',0,0,'C',0);
	$this->SetLineWidth(0.2);
}

//Page header
function Header()
{
	$this->Image(DIR_IMAGENS.'logo_pb.png',26,16,40);
	$this->Ln(1);
	$this->SetFont('Arial','',6);
	$this->Cell(228,4,'',0,0,'L',0);
	$this->Cell(15,4,'DOC:',0,0,'L',0);
	$this->Cell(12,4,"",0,1,'R',0);
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
	$this->Cell(271,4,$this->Titulo(),0,1,'R',0);
	$this->SetFont('Arial','B',8);
	$this->Cell(135,4,$this->codigo(),0,0,'L',0);
	$this->Cell(136,4,$this->Revisao(),0,1,'R',0);
	$this->SetFont('Arial','',9);
	$this->SetLineWidth(1);
	$this->SetDrawColor(0,0,0);
	$this->Line(11,40,280,40);
	$this->SetLineWidth(0.5);
	$this->SetXY(10,43);
}

//Page footer
function Footer()
{
	$this->Line(25,190,75,190);
	$this->Line(90,190,130,190);
	$this->SetXY(25,191);
	$this->Cell(50,5,"APROVAÇÃO DO COORDENADOR",0,0,'C',0);
	$this->Cell(15);
	$this->Cell(40,5,"RESPONSÁVEL",0,0,'C',0);

}
}

$db = new banco_dados;
	
$sql = "SELECT * FROM ".DATABASE.".funcionarios, ".DATABASE.".setores, ".DATABASE.".rh_funcoes, ".DATABASE.".adiantamento_funcionario, ".DATABASE.".requisicao_despesas ";
$sql .= "WHERE adiantamento_funcionario.id_adiantamento_funcionario = '" . $_GET["id_adiantamento_funcionario"] . "' ";
$sql .= "AND adiantamento_funcionario.id_requisicao_despesa = requisicao_despesas.id_requisicao_despesa ";
$sql .= "AND requisicao_despesas.responsavel_despesas = funcionarios.id_funcionario ";
$sql .= "AND funcionarios.id_setor = setores.id_setor ";
$sql .= "AND funcionarios.id_funcao = rh_funcoes.id_funcao ";

$db->select($sql,'MYSQL',true);

$funcionarios = $db->array_select[0];

$pdf=new PDF('l','mm',A4);
$pdf->SetAutoPageBreak(true,25);
$pdf->SetMargins(10,15);
$pdf->SetLineWidth(0.5);

$pdf->departamento=NOME_EMPRESA;
$pdf->titulo="RELATÓRIO SEMANAL DE DESPESAS";
$pdf->emissao=date("d/m/Y");

$pdf->versao_documento="Requisição nº: ".$funcionarios["id_requisicao_despesa"];

$pdf->codigo="Período: ".mysql_php($funcionarios["periodo_inicial"])." á ".mysql_php($funcionarios["periodo_final"]);

$pdf->AliasNbPages();
$pdf->AddPage();

$pdf->SetFont('Arial','B',8);

$pdf->Cell(145,5,"FUNCIONÁRIO: " . $funcionarios["funcionario"],0,0,'L',0);

$pdf->Cell(125,5,"CARGO: " . $funcionarios["descricao"],0,0,'R',0);

$pdf->SetFont('Arial','',8);

$pdf->SetLineWidth(0.5);
$pdf->SetDrawColor(128,128,128);
$pdf->Line(10,50,280,50);
$pdf->Ln(10);

$pdf->SetFont('Arial','B',8);

$pdf->Cell(18,5,"DATA",0,0,'L',0);

$pdf->Cell(105,5,"DESCRIÇÃO",0,0,'L',0);

$pdf->HCell(10,5,"CL.",0,0,'L',0);

$pdf->Cell(15,5,"OS",0,0,'L',0);

$pdf->Cell(20,5,"REFEIÇÃO",0,0,'L',0);

$pdf->Cell(20,5,"PASSAGEM",0,0,'L',0);

$pdf->Cell(10,5,"KM",0,0,'L',0);

$pdf->Cell(20,5,"VLR./km",0,0,'L',0);

$pdf->Cell(23,5,"COMBUSTÍVEL",0,0,'L',0);

$pdf->Cell(15,5,"PEDÁGIO",0,0,'L',0);

$pdf->Cell(20,5,"OUTROS",0,1,'L',0);

$pdf->SetFont('Arial','',8);

$sql = "SELECT SUM(despesas_funcionario.refeicao) AS refeicao, SUM(despesas_funcionario.passagem) AS passagem, SUM(despesas_funcionario.km_rodados) AS km_rodados, ";
$sql .= "SUM(despesas_funcionario.km_rodados*despesas_funcionario.tarifa_km) AS valor, SUM(despesas_funcionario.combustivel) AS combustivel, SUM(despesas_funcionario.pedagio) AS pedagio, ";
$sql .= "SUM(despesas_funcionario.despesas_diversas) AS despesas_diversas ";
$sql .= "FROM ".DATABASE.".despesas_funcionario ";
$sql .= "WHERE despesas_funcionario.id_adiantamento_funcionario = '" . $_GET["id_adiantamento_funcionario"] . "' ";
$sql .= "GROUP BY despesas_funcionario.id_adiantamento_funcionario ";

$db->select($sql,'MYSQL',true);	

$regs_total = $db->array_select[0];

$sql = "SELECT * FROM ".DATABASE.".requisicao_despesas, ".DATABASE.".despesas_funcionario, ".DATABASE.".adiantamento_funcionario, ".DATABASE.".ordem_servico, ".DATABASE.".empresas ";
$sql .= "WHERE despesas_funcionario.id_adiantamento_funcionario = '" . $_GET["id_adiantamento_funcionario"] . "' ";
$sql .= "AND despesas_funcionario.id_adiantamento_funcionario = adiantamento_funcionario.id_adiantamento_funcionario ";
$sql .= "AND adiantamento_funcionario.id_requisicao_despesa = requisicao_despesas.id_requisicao_despesa ";
$sql .= "AND requisicao_despesas.id_os = ordem_servico.id_os ";
$sql .= "AND requisicao_despesas.id_empresa = empresas.id_empresa ";
$sql .= "ORDER BY despesas_funcionario.data_despesa ";

$db->select($sql,'MYSQL',true);	

$adiantamento = 0;

foreach ($db->array_select as $regs)
{
	if($regs["valor_adiantamento"]>0)
	{
		$adiantamento = $regs["valor_adiantamento"];
	}
	
	if (!$celula)
	{
		$celula = 1;
	}
	$celula = 1;
	
	$celula = $celula * 5;

	$pdf->Cell(18,$celula,mysql_php($regs["data_despesa"]),0,0,'L',0);

	$pdf->HCell(105,5,$regs["descricao_despesa"],0,0,'L',0);
	
	$pdf->Cell(10,$celula,$regs["id_empresa"],0,0,'L',0);
	
	$os = sprintf("%05d",$regs["os"]);

	$pdf->Cell(15,$celula,$os,0,0,'L',0);
	$pdf->Cell(20,$celula,number_format($regs["refeicao"],2,',','.'),0,0,'L',0);
	$pdf->Cell(20,$celula,number_format($regs["passagem"],2,',','.'),0,0,'L',0);
	$pdf->Cell(10,$celula,$regs["km_rodados"],0,0,'L',0);
	$pdf->Cell(20,$celula,number_format($regs["km_rodados"]*$regs["tarifa_km"],2,',','.'),0,0,'L',0);
	$pdf->Cell(23,$celula,number_format($regs["combustivel"],2,',','.'),0,0,'L',0);
	$pdf->Cell(15,$celula,number_format($regs["pedagio"],2,',','.'),0,0,'L',0);
	$pdf->Cell(20,$celula,number_format($regs["despesas_diversas"],2,',','.'),0,1,'L',0);
	
}

$total = $regs_total["refeicao"]+$regs_total["passagem"]+$regs_total["valor"]+$regs_total["combustivel"]+$regs_total["pedagio"]+$regs_total["despesas_diversas"];

$pdf->Ln(5);
$pdf->SetLineWidth(0.2);
$pdf->SetDrawColor(0,0,0);

$pdf->Cell(128,5,"",0,0,'L',0);
$pdf->SetFont('Arial','B',8);
$pdf->Cell(20,5,"SUBTOTAL:",1,0,'R',0);
$pdf->SetFont('Arial','',8);
$pdf->Cell(20,5,number_format($regs_total["refeicao"],2,',','.'),1,0,'L',0);
$pdf->Cell(20,5,number_format($regs_total["passagem"],2,',','.'),1,0,'L',0);
$pdf->Cell(10,5,$regs_total["km_rodados"],1,0,'L',0);
$pdf->Cell(20,5,number_format($regs_total["valor"],2,',','.'),1,0,'L',0);
$pdf->Cell(23,5,number_format($regs_total["combustivel"],2,',','.'),1,0,'L',0);
$pdf->Cell(15,5,number_format($regs_total["pedagio"],2,',','.'),1,0,'L',0);
$pdf->Cell(15,5,number_format($regs_total["despesas_diversas"],2,',','.'),1,1,'L',0);

$pdf->Ln(10);

$pdf->Cell(110,5,"",0,0,'C',0);
$pdf->Cell(160,5,"PREENCHIMENTO E CONFERÊNCIA RESERVADO AO DEPTº. ADM/FIN",1,1,'C',0);

$pdf->Cell(110,5,"",0,0,'C',0);
$pdf->Cell(80,5,"ADIANTAMENTO",1,0,'C',0);
$pdf->Cell(80,5,number_format($adiantamento,2,',','.'),1,1,'C',0);

$pdf->Cell(110,5,"",0,0,'C',0);
$pdf->Cell(80,5,"VALOR TOTAL",1,0,'C',0);
$pdf->Cell(80,5,number_format($total,2,',','.'),1,1,'C',0);

if($adiantamento-$total<0)
{
	$reembolso = ($adiantamento-$total)*-1;
	$devolucao = "0.00";
}
else
{
	$reembolso = "0.00";
	$devolucao = $adiantamento-$total;
}


$pdf->Cell(110,5,"",0,0,'C',0);
$pdf->Cell(80,5,"DEVOLUÇÃO A EMPRESA",1,0,'C',0);
$pdf->Cell(80,5,"R$ ".number_format($devolucao,2,',','.'),1,1,'C',0);

$pdf->Cell(110,5,"",0,0,'C',0);
$pdf->Cell(80,5,"REEMBOLSO",1,0,'C',0);
$pdf->Cell(80,5,"R$ ".number_format($reembolso,2,',','.'),1,1,'C',0);


$pdf->Cell(110,5,"",0,0,'C',0);
$pdf->Cell(80,5,"FORMA DE PAGAMENTO",1,0,'C',0);

$y = $pdf->GetY();

$pdf->Cell(80,5,"",1,1,'C',0);

$pdf->SetY($y+1);
$pdf->SetX(205);
$pdf->chkbox();
$pdf->Cell(20,5,"Dep. Bancário",0,0,'L',0);

$pdf->SetY($y+1);
$pdf->SetX(235);
$pdf->chkbox();
$pdf->Cell(12,5,"Cheque",0,0,'L',0);

$pdf->SetY($y+1);
$pdf->SetX(260);
$pdf->chkbox();
$pdf->Cell(16,5,"Dinheiro",0,1,'L',0);

$pdf->Cell(110,10,"",0,0,'C',0);
$pdf->Cell(80,10,"EXECUTANTE",1,0,'C',0);
$pdf->Cell(80,10,"",1,1,'C',0);

$pdf->Output('DESPESAS_SEMANAL_'.date('dmYhis').'.pdf', 'D');

?> 