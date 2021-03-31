<?php
/*
	  Relatório Controle Horas Semanal	
	  
	  Criado por Carlos Abreu / Otávio Pamplona
	  
	  local/Nome do arquivo:
	  ../financeiro/relatorios/rel_despesas_semanal.php
	  
	  Versão 0 --> VERSÃO INICIAL - 14/07/2007
	  Versão 1 --> Atualização lay-out - 23/06/2014 - Carlos Abreu
	  Versão 2 --> atualização classe banco de dados - 22/01/2015 - Carlos Abreu
	  Versão 3 --> Inclusão dos campos reg_del nas consultas - 20/11/2017 - Carlos Abreu		
*/

require_once(implode(DIRECTORY_SEPARATOR,array('..','..','config.inc.php')));

require_once(INCLUDE_DIR."include_pdf.inc.php");

class PDF extends FPDF
{

var $documento;

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
	$this->Cell(12,4,$this->documento,0,1,'R',0);
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
	$this->SetLineWidth(0.5);
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
	$this->Cell(40,5,"FUNCIONÁRIO",0,0,'C',0);
}
}

$db = new banco_dados;
	
$sql = "SELECT *, rh_funcoes.descricao AS funcao FROM ".DATABASE.".funcionarios, ".DATABASE.".setores, ".DATABASE.".rh_funcoes, ".DATABASE.".ordem_servico, ".DATABASE.".requisicao_despesas ";
$sql .= "WHERE requisicao_despesas.id_requisicao_despesa = '" . $_GET["id_requisicao_despesa"] . "' ";
$sql .= "AND funcionarios.reg_del = 0 ";
$sql .= "AND setores.reg_del = 0 ";
$sql .= "AND rh_funcoes.reg_del = 0 ";
$sql .= "AND ordem_servico.reg_del = 0 ";
$sql .= "AND requisicao_despesas.reg_del = 0 ";
$sql .= "AND requisicao_despesas.responsavel_despesas = funcionarios.id_funcionario ";
$sql .= "AND requisicao_despesas.id_os = ordem_servico.id_os ";
$sql .= "AND funcionarios.id_setor = setores.id_setor ";
$sql .= "AND funcionarios.id_funcao = rh_funcoes.id_funcao ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}

$funcionarios = $db->array_select[0];

//obtem as despesas cadastradas no orçamento
/*	
$sql = "SELECT AF2010.AF2_COMPOS, AF2010.AF2_DESCRI, AF2010.AF2_QUANT FROM AF1010, AF2010 ";
$sql .= "WHERE AF1010.D_E_L_E_T_ = '' ";
$sql .= "AND AF2010.D_E_L_E_T_ = '' ";
$sql .= "AND AF1010.AF1_ORCAME = '".sprintf("%010d",$funcionarios["os"])."' "; 
$sql .= "AND AF2010.AF2_ORCAME = AF1010.AF1_ORCAME ";
$sql .= "AND AF2010.AF2_COMPOS <> '' ";	
$sql .= "AND LEFT(AF2010.AF2_COMPOS,3) = 'DES' ";
$sql .= "GROUP BY AF2010.AF2_COMPOS, AF2010.AF2_DESCRI, AF2010.AF2_QUANT ";
$sql .= "ORDER BY AF2010.AF2_DESCRI ";

$db->select($sql,'MSSQL', true);

if($db->erro!='')
{
	die($db->erro);
}

foreach($db->array_select as $regs1)
{
	$array_items_desp[trim($regs1["AF2_COMPOS"])] = trim($regs1["AF2_DESCRI"]);
}

//obtem o cliente
$sql = "SELECT A1_NOME, A1_MUN FROM AF1010, SA1010 ";
$sql .= "WHERE AF1010.D_E_L_E_T_ = '' ";
$sql .= "AND SA1010.D_E_L_E_T_ = '' ";
$sql .= "AND AF1010.AF1_CLIENT = SA1010.A1_COD ";
$sql .= "AND AF1010.AF1_LOJA = SA1010.A1_LOJA ";
$sql .= "AND AF1010.AF1_ORCAME = '".sprintf("%010d",$funcionarios["os"])."' ";

$db->select($sql,'MSSQL', true);

if($db->erro!='')
{
	$resposta->addAlert($db->erro);
}
else
{
	$regs_client = $db->array_select[0];
}
*/

//filtra as necessidades requisitadas
$sql = "SELECT * FROM ".DATABASE.".requisicao_despesas_funcionarios, ".DATABASE.".funcionarios ";
$sql .= "WHERE requisicao_despesas_funcionarios.id_requisicao_despesa = '" . $funcionarios["id_requisicao_despesa"] . "' ";
$sql .= "AND requisicao_despesas_funcionarios.reg_del = 0 ";
$sql .= "AND funcionarios.reg_del = 0 ";
$sql .= "AND requisicao_despesas_funcionarios.id_funcionario = funcionarios.id_funcionario ";
$sql .= "ORDER BY funcionario ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}
		
foreach($db->array_select as $cont3)
{
	$array_func[$cont3["id_funcionario"]] = $cont3["funcionario"];
}

//filtra as necessidades requisitadas
$sql = "SELECT * FROM ".DATABASE.".requisicao_despesas_necessidades ";
$sql .= "WHERE requisicao_despesas_necessidades.id_requisicao_despesa = '" . $funcionarios["id_requisicao_despesa"] . "' ";
$sql .= "AND requisicao_despesas_necessidades.reg_del = 0 ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}
		
foreach($db->array_select as $cont2)
{

	if($cont2["cod_necessidade"]=='DES99' || $cont2["item"]!='')
	{
		$item = $cont2["item"];
	}
	else
	{
		if($cont2["cod_necessidade"]=='DES98')
		{
			$item = $array_items_desp[trim($cont2["cod_necessidade"])]." - ".$cont2["hora_ini"] . " a " .$cont2["hora_fim"];
		}
		else
		{
			$item = $array_items_desp[trim($cont2["cod_necessidade"])];
		}
	}
	
	$array_items_nec[$cont2["cod_necessidade"]."#".$item] += $cont2["valor_despesa"];

}

//contabiliza os valores da requisição
$sql = "SELECT SUM(valor_despesa) as total_despesa FROM ".DATABASE.".requisicao_despesas_necessidades ";
$sql .= "WHERE requisicao_despesas_necessidades.reg_del = 0 ";
$sql .= "AND requisicao_despesas_necessidades.id_requisicao_despesa = '".$funcionarios["id_requisicao_despesa"]."' ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}

$regs_vlr = $db->array_select[0];	

//Instanciation of inherited class
$pdf=new PDF('l','mm',A4);
$pdf->SetAutoPageBreak(true,25);
$pdf->SetMargins(10,15);
$pdf->SetLineWidth(0.5);

//Seta o cabeçalho
$pdf->departamento=NOME_EMPRESA;
$pdf->titulo="RELATÓRIO SEMANAL DE DESPESAS";
$pdf->documento="FIN-02-R1";
$pdf->emissao=date("d/m/Y");

$pdf->versao_documento="Requisição nº: ".sprintf("%05d",$funcionarios["id_requisicao_despesa"]);

$pdf->codigo="Período: ".mysql_php($funcionarios["periodo_inicial"])." á ".mysql_php($funcionarios["periodo_final"]);

$pdf->AliasNbPages();

$pdf->AddPage();

$pdf->SetFont('Arial','B',8);

$pdf->HCell(145,5,"RESPONSÁVEL PRESTAÇÃO CONTAS: " . $funcionarios["funcionario"],0,0,'L',0);

$pdf->HCell(125,5,"CARGO: " . $funcionarios["funcao"],0,0,'R',0);

$pdf->SetFont('Arial','',8);

$pdf->SetLineWidth(0.2);
$pdf->SetDrawColor(128,128,128);
$pdf->Line(10,50,280,50);
$pdf->Ln(10);

$pdf->SetFont('Arial','B',8);

$pdf->Cell(18,5,"DATA",0,0,'L',0);

$pdf->Cell(20,5,"OS",0,0,'L',0);

$pdf->HCell(105,5,"CLIENTE",0,0,'L',0);

$pdf->HCell(105,5,"ATIVIDADE/OBS.",0,1,'L',0);

$pdf->SetFont('Arial','',8);

$pdf->Cell(18,5,mysql_php($funcionarios["data_prestacao_contas"]),0,0,'L',0);

$pdf->Cell(20,5,sprintf("%010d",$funcionarios["os"]),0,0,'L',0);

//$pdf->HCell(105,5,trim($regs_client["A1_NOME"]).' - '.trim($regs_client["A1_MUN"]),0,0,'L',0);

$pdf->HCell(105,5,$funcionarios["atividade"],0,1,'L',0);

//obtem a linha superior
$y = $pdf->GetY();

//printa as despesas
$pdf->SetFont('Arial','B',8);
$pdf->Cell(80,5,"DESPESAS DECLARADAS",1,1,'C',0);
$pdf->Cell(65,5,"Item",1,0,'L',0);
$pdf->Cell(15,5,"valor (R$)",1,1,'L',0);
$pdf->SetFont('Arial','',8);

foreach($array_items_nec as $codigo=>$valor)
{
	$item = explode("#",$codigo);

	$pdf->HCell(65,5,$item[1],1,0,'L',0);
	$pdf->HCell(15,5,number_format($valor,2,',','.'),1,1,'L',0);	
}

$pdf->SetFont('Arial','B',8);
$pdf->Cell(65,5,"Total:",1,0,'L',0);

$pdf->Cell(15,5,number_format($regs_vlr["total_despesa"],2,',','.'),1,1,'L',0);
$pdf->SetFont('Arial','',8);

//printa os funcionarios
$pdf->SetY($y);

$pdf->SetFont('Arial','B',8);
$pdf->Cell(85,5,"",0,0,'C',0);
$pdf->Cell(80,5,"FUNCIONÁRIOS",1,1,'C',0);
$pdf->SetFont('Arial','',8);

foreach($array_func as $codigo=>$func)
{
	$pdf->Cell(85,5,"",0,0,'C',0);
	$pdf->HCell(80,5,$func,1,1,'L',0);
}

//printa as prestações de contas
$pdf->SetY($y);

$pdf->SetFont('Arial','B',8);
$pdf->Cell(170,5,"",0,0,'C',0);
$pdf->Cell(100,5,"PREENCHIMENTO E CONFERÊNCIA RESERVADO AO DEPTº. ADM/FIN",1,1,'C',0);
$pdf->SetFont('Arial','',8);

$pdf->Cell(170,5,"",0,0,'C',0);
$pdf->Cell(50,5,"ADIANTAMENTO",1,0,'C',0);
$pdf->Cell(50,5,"R$ ".number_format($funcionarios["valor_adiantamento"],2,',','.'),1,1,'C',0);

$pdf->Cell(170,5,"",0,0,'C',0);
$pdf->Cell(50,5,"VALOR TOTAL",1,0,'C',0);
$pdf->Cell(50,5,"R$ ".number_format($regs_vlr["total_despesa"],2,',','.'),1,1,'C',0);

if($funcionarios["valor_adiantamento"]-$regs_vlr["total_despesa"]<0)
{
	$reembolso = ($funcionarios["valor_adiantamento"]-$regs_vlr["total_despesa"])*-1;
	$devolucao = "0.00";
}
else
{
	$reembolso = "0.00";
	$devolucao = $funcionarios["valor_adiantamento"]-$regs_vlr["total_despesa"];
}

$pdf->Cell(170,5,"",0,0,'C',0);
$pdf->Cell(50,5,"DEVOLUÇÃO",1,0,'C',0);
$pdf->Cell(50,5,"R$ ".number_format($devolucao,2,',','.'),1,1,'C',0);

$pdf->Cell(170,5,"",0,0,'C',0);
$pdf->Cell(50,5,"REEMBOLSO",1,0,'C',0);
$pdf->Cell(50,5,"R$ ".number_format($reembolso,2,',','.'),1,1,'C',0);


$pdf->Cell(170,5,"",0,0,'C',0);
$pdf->Cell(100,5,"FORMA DE PAGAMENTO",1,1,'C',0);

$y = $pdf->GetY();

$pdf->Cell(170,5,"",0,0,'C',0);
$pdf->Cell(100,5,"",1,1,'C',0);

$pdf->SetY($y+1);
$pdf->SetX(190);
$pdf->chkbox();
$pdf->Cell(20,5,"Dep. Bancário",0,0,'L',0);

$pdf->SetY($y+1);
$pdf->SetX(230);
$pdf->chkbox();
$pdf->Cell(12,5,"Cheque",0,0,'L',0);

$pdf->SetY($y+1);
$pdf->SetX(255);
$pdf->chkbox();
$pdf->Cell(16,5,"Dinheiro",0,1,'L',0);

$pdf->Output('DESPESAS_SEMANAL_'.date('YmdHis').'.pdf','I');

?> 