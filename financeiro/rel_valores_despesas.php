<?php

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
	$this->Image('../images/logo_pb.jpg',26,16,40);
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
	$this->Cell(271,4,$this->Titulo(),0,1,'R',0);
	$this->SetFont('Arial','B',8);
	$this->Cell(271,4,$this->Revisao(),0,1,'R',0);
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

}
}

$db = new banco_dados;

//Instanciation of inherited class
$pdf=new PDF('l','mm',A4);
$pdf->SetAutoPageBreak(true,25);
$pdf->SetMargins(10,15);
$pdf->SetLineWidth(0.3);

$pdf->titulo="RELATÓRIO DE VALORES DE DESPESAS";

$pdf->setor="FIN";
$pdf->codigodoc="01"; //"00";
$pdf->codigo=12; //Numero OS
$pdf->setorextenso=$setor; //"INFORMATICA"
$pdf->emissao=date('d/m/Y');

if($_POST["intervalo"]=='1')
{
	$filtro1 = " AND adiantamento_funcionario.data_adiantamento BETWEEN '" . php_mysql($_POST["data_ini"]) . "' AND '" . php_mysql($_POST["datafim"]) . "' ";
	$pdf->versao_documento="DE: ".$_POST["data_ini"] . " A " . $_POST["datafim"];
}
else
{
	$pdf->versao_documento="TOTAL";
}

if($_POST["escolhaos"]=='-1')
{
	$filtro .= '';
}
else
{
	$filtro .= " AND ordem_servico.id_os = '". $_POST["escolhaos"] . "' ";
}

$pdf->AliasNbPages();
$pdf->AddPage();

$pdf->SetFont('Arial','B',8);
$pdf->Cell(20,5,"DATA",0,0,'L',0);
$pdf->Cell(10,5,"",0,0,'L',0); //SITE
$pdf->Cell(10,5,"C.C.",0,0,'L',0);
$pdf->Cell(15,5,"OS",0,0,'L',0);
$pdf->Cell(25,5,"REFEIÇÃO",0,0,'L',0);
$pdf->Cell(25,5,"PASSAGEM",0,0,'L',0);
$pdf->Cell(20,5,"KM",0,0,'L',0);
$pdf->Cell(25,5,"VALOR/KM",0,0,'L',0);
$pdf->Cell(25,5,"COMBUSTÍVEL",0,0,'L',0);
$pdf->Cell(25,5,"PEDÁGIO",0,0,'L',0);
$pdf->Cell(25,5,"DIVERSOS",0,0,'L',0);
$pdf->Cell(25,5,"SUB-TOTAL",0,1,'R',0);
$pdf->Ln(3);


$sql = "SELECT *, SUM(despesas_funcionario.refeicao) AS gasto_refeicao, SUM(despesas_funcionario.passagem) AS passagem, ";
$sql .= "SUM(despesas_funcionario.km_rodados) AS km_rodados, SUM(despesas_funcionario.km_rodados*despesas_funcionario.tarifa_km) AS valor_km, ";
$sql .= "SUM(despesas_funcionario.combustivel) AS combustivel, SUM(despesas_funcionario.pedagio) AS pedagio, ";
$sql .= "SUM(despesas_funcionario.despesas_diversas) AS despesas_diversas, ";
$sql .= "SUM(despesas_funcionario.refeicao+despesas_funcionario.passagem+(despesas_funcionario.km_rodados*despesas_funcionario.tarifa_km)+despesas_funcionario.combustivel+despesas_funcionario.pedagio+despesas_funcionario.despesas_diversas) AS sub_total ";
$sql .= "FROM ".DATABASE.".requisicao_despesas, ".DATABASE.".adiantamento_funcionario, ".DATABASE.".despesas_funcionario, ".DATABASE.".ordem_servico, ".DATABASE.".empresas ";
$sql .= "WHERE requisicao_despesas.id_requisicao_despesa = adiantamento_funcionario.id_requisicao_despesa ";
$sql .= "AND adiantamento_funcionario.id_adiantamento_funcionario = despesas_funcionario.id_adiantamento_funcionario ";
$sql .= "AND adiantamento_funcionario.status_adiantamento = 1 ";
$sql .= "AND requisicao_despesas.id_os = ordem_servico.id_os ";
$sql .= "AND requisicao_despesas.id_empresa = empresas.id_empresa_erp ";
$sql .= $filtro1;
$sql .= $filtro;
$sql .= "GROUP BY ordem_servico.id_os, empresas.id_empresa_erp, adiantamento_funcionario.data_adiantamento "; //sites.id_site,
$sql .= "ORDER BY adiantamento_funcionario.data_adiantamento, ordem_servico.os ";

$reg = $db->select($sql,'MYSQL');	

$total = 0;

$sub_total_refeicao = 0;

$sub_total_passagem = 0;

$sub_total_km = 0;

$sub_total_valor_km = 0;

$sub_total_combustivel = 0;

$sub_total_pedagio = 0;

$sub_total_despesas_diversas = 0;

while ($regs = mysqli_fetch_assoc($reg))
{

	$pdf->SetFont('Arial','',7);
	$pdf->Cell(20,5,mysql_php($regs["data_adiantamento"]),0,0,'L',0);
	$pdf->Cell(10,5,"",0,0,'L',0); //$regs["id_site"]
	$pdf->Cell(10,5,$centro,0,0,'L',0);
	$pdf->Cell(15,5,$os,0,0,'L',0);
	$pdf->Cell(25,5,number_format($regs["gasto_refeicao"],2,',','.'),0,0,'L',0);
	$pdf->Cell(25,5,number_format($regs["passagem"],2,',','.'),0,0,'L',0);
	$pdf->Cell(20,5,$regs["km_rodados"],0,0,'L',0);
	$pdf->Cell(25,5,number_format($regs["valor_km"],2,',','.'),0,0,'L',0);
	$pdf->Cell(25,5,number_format($regs["combustivel"],2,',','.'),0,0,'L',0);
	$pdf->Cell(25,5,number_format($regs["pedagio"],2,',','.'),0,0,'L',0);
	$pdf->Cell(25,5,number_format($regs["despesas_diversas"],2,',','.'),0,0,'L',0);
	$pdf->Cell(25,5,number_format($regs["sub_total"],2,',','.'),0,1,'R',0);	
	
	$sub_total_refeicao += $regs["gasto_refeicao"];
	
	$sub_total_passagem += $regs["passagem"];
	
	$sub_total_km += $regs["km_rodados"];
	
	$sub_total_valor_km += $regs["valor_km"];
	
	$sub_total_combustivel += $regs["combustivel"];
	
	$sub_total_pedagio += $regs["pedagio"];
	
	$sub_total_despesas_diversas += $regs["despesas_diversas"];
	
	$total += $regs["sub_total"];

}

$pdf->Ln(3);
$pdf->SetFont('Arial','B',7);
$pdf->Cell(55,5,"TOTAIS: ",0,0,'R',0);
$pdf->SetFont('Arial','',7);	
$pdf->Cell(25,5,number_format($sub_total_refeicao,2,',','.'),0,0,'L',0);
$pdf->Cell(25,5,number_format($sub_total_passagem,2,',','.'),0,0,'L',0);
$pdf->Cell(20,5,$sub_total_km,0,0,'L',0);
$pdf->Cell(25,5,number_format($sub_total_valor_km,2,',','.'),0,0,'L',0);
$pdf->Cell(25,5,number_format($sub_total_combustivel,2,',','.'),0,0,'L',0);
$pdf->Cell(25,5,number_format($sub_total_pedagio,2,',','.'),0,0,'L',0);
$pdf->Cell(25,5,number_format($sub_total_despesas_diversas,2,',','.'),0,0,'L',0);
$pdf->Cell(25,5,number_format($total,2,',','.'),0,1,'R',0);	

$pdf->Output();
?> 