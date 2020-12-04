<?php
/*
		Relatorio Distribuição EPI
		
		Criado por Carlos Eduardo
		
		local/Nome do arquivo:
		../rh/relatorios/rel_distribuicao_epi.php
		
		Versão 0 --> VERSÃO INICIAL - 04/05/2016
		Versão 1 --> Inclusão dos campos reg_del nas consultas - 27/11/2017 - Carlos Abreu	
*/
require_once(implode(DIRECTORY_SEPARATOR,array('..','..','config.inc.php')));

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

function Header()
{
	$this->Image(DIR_IMAGENS.'logo_pb.png',10,16,40);
	$this->Ln(1);
	$this->SetFont('Arial','',6);
	$this->SetFont('Arial','B',12);
	$this->Cell(240,8,$this->Titulo(),0,0,'C',0);
	$this->SetFont('Arial','B',6);
	$this->Cell(30,4,'REVISÃO:01',0,1,'L',0);
	$this->Cell(240,4,'',0,0,'R',0);
	$this->Cell(30,4,'DATA: 19/02/2010',0,1,'L',0);
	$this->SetXY(10,30);
}

function Footer()
{

}
}

$pdf=new PDF('l','mm',A4);
$pdf->SetAutoPageBreak(true,25);
$pdf->SetMargins(10,15);
$pdf->SetLineWidth(0.2);
$pdf->SetDrawColor(0,0,0);

$pdf->titulo="FICHA DE CONTROLE DE DISTRIBUIÇÃO DE EPI´S";

$pdf->AliasNbPages();
$pdf->AddPage();

$db = new banco_dados;

$sql = "SELECT * FROM ".DATABASE.".requisicao_epi, ".DATABASE.".rh_funcoes, ".DATABASE.".funcionarios ";
$sql .= "LEFT JOIN ".DATABASE.".empresa_funcionarios ON (empresa_funcionarios.id_empfunc = funcionarios.id_empfunc AND empresa_funcionarios.reg_del = 0) ";
$sql .= "WHERE requisicao_epi.id_funcionario = funcionarios.id_funcionario ";
$sql .= "AND requisicao_epi.reg_del = 0 ";
$sql .= "AND rh_funcoes.reg_del = 0 ";
$sql .= "AND funcionarios.reg_del = 0 ";
$sql .= "AND funcionarios.id_funcao = rh_funcoes.id_funcao ";
$sql .= "AND requisicao_epi.id_requisicao_epi = '" . $_GET["id_requisicao_epi"] . "' ";

$db->select($sql,'MYSQL',true);	

$regs = $db->array_select[0];

switch ($regs["id_motivo"])
{
	case 1:
		$motivo = "ADMISSÃO";
	break;
	
	case 3:
		$motivo = "VALIDADE/INSPEÇÃO";
	break;
	
	default: 
		$motivo = "";
}

$pdf->SetFont('Arial','B',8);
$pdf->Cell(20,5,"NOME: ",1,0,'L',0);
$pdf->SetFont('Arial','',8);
$pdf->Cell(100,5,$regs["funcionario"],1,0,'L',0);
$pdf->SetFont('Arial','B',8);
$pdf->Cell(45,5,"EMPRESA SUBCONTRATADA: ",1,0,'L',0);
$pdf->SetFont('Arial','',8);
$pdf->HCell(100,5,$regs["empresa_func"],1,1,'L',0);
$pdf->SetFont('Arial','B',8);
$pdf->Cell(35,5,"DATA CONTRATUAL: ",1,0,'L',0);
$pdf->SetFont('Arial','',8);
$pdf->Cell(20,5,mysql_php($regs["data_inicio"]),1,0,'L',0);
$pdf->SetFont('Arial','B',8);
$pdf->Cell(45,5,"IMPRESSÃO DA FICHA POR: ",1,0,'L',0);
$pdf->SetFont('Arial','',8);
$pdf->HCell(30,5,$motivo,1,0,'L',0);
$pdf->SetFont('Arial','B',8);
$pdf->Cell(15,5,"CARGO: ",1,0,'L',0);
$pdf->SetFont('Arial','',8);
$pdf->HCell(80,5,$regs["descricao"],1,0,'L',0);
$pdf->SetFont('Arial','B',8);
$pdf->Cell(20,5,"REGISTRO: ",1,0,'L',0);
$pdf->SetFont('Arial','',8);
$pdf->Cell(20,5,$regs["id_funcionario"],1,1,'L',0);
$pdf->Ln(5);
$pdf->SetFont('Arial','B',10);
$pdf->Cell(265,5,"DECLARAÇÃO",0,1,'C',0);
$pdf->SetFont('Arial','',8);
$pdf->Cell(10,5,"",0,0,'L',0);
$pdf->Cell(55,5,"Declaro para os devidos fins que recebi da ",0,0,'L',0);
$pdf->SetFont('Arial','B',8);
$pdf->Cell(46,5,NOME_EMPRESA,0,0,'L',0);
$pdf->SetFont('Arial','',8);
$pdf->Cell(154,5,"nas datas abaixo lançadas, assinadas, os materiais de Segurança discriminados, os quais são de uso obrigatório e ",0,1,'L',0);
$pdf->Cell(10,5,"",0,0,'L',0);
$pdf->Cell(255,5,"exclusivo na execução das minhas atividades dentro desta Obra, ciente ainda que, se não usá-los adequadamente poderei sofrer as sanções previstas no art. 158, I, da CLT.",0,1,'L',0);
$pdf->Cell(10,5,"",0,0,'L',0);
$pdf->Cell(255,5,"Declaro, ainda, estar ciente das obrigações descrita na Portaria 3214 do Ministério do Trabalho, que determina na NR-6: ",0,1,'L',0);
$pdf->SetFont('Arial','B',8);
$pdf->Ln(3);
$pdf->Cell(10,5,"",0,0,'L',0);
$pdf->Cell(255,5,"6.7 - Cabe ao Empregado. ",0,1,'L',0);
$pdf->Cell(15,5,"",0,0,'L',0);
$pdf->Cell(250,5,"6.7.1 - Cabe ao Empregado quanto ao EPI: ",0,1,'L',0);
$pdf->Cell(30,5,"",0,0,'L',0);
$pdf->Cell(220,5,"a)  Usar, utilizando-o apenas para a finalidade que se destina;",0,1,'L',0);
$pdf->Cell(30,5,"",0,0,'L',0);
$pdf->Cell(220,5,"b)  Responsabilizar-se pela guarda e conservação;",0,1,'L',0);
$pdf->Cell(30,5,"",0,0,'L',0);
$pdf->Cell(220,5,"c)  Comunicar ao Empregador qualquer alteração que o torne impróprio para o uso; e,",0,1,'L',0);
$pdf->Cell(30,5,"",0,0,'L',0);
$pdf->Cell(220,5,"d)  Cumprir as determinações do Empregador sobre o uso adequado.",0,1,'L',0);
$pdf->SetFont('Arial','',8);
$pdf->Ln(3);
$pdf->Cell(10,5,"",0,0,'L',0);
$pdf->Cell(255,5,"Estou ciente que poderei solicitar novos EPI´s, em decorrência de desgaste ou perda. Comprometo-me também a devolvê-los após o término do contrato de trabalho. ",0,1,'L',0);
$pdf->Ln(3);

$pdf->SetFont('Arial','B',8);
$pdf->Cell(20,5,"DATA",1,0,'L',0);
$pdf->Cell(15,5,"QUANT.",1,0,'L',0);
$pdf->Cell(20,5,"C.A.",1,0,'L',0);
$pdf->Cell(105,5,"DESCRIÇÃO DO EPI",1,0,'L',0);
$pdf->Cell(35,5,"VISTO RECEBIMENTO",1,0,'L',0);
$pdf->Cell(2,5,"",0,0,'L',0);
$pdf->Cell(35,5,"DATA DEVOLUÇÃO",1,0,'L',0);
$pdf->Cell(35,5,"VISTO DEVOLUÇÃO",1,1,'L',0);

$sql = "SELECT * FROM ".DATABASE.".requisicao_epi_detalhes, suprimentos.materiais ";
$sql .= "WHERE requisicao_epi_detalhes.id_requisicao_epi = '". $_GET["id_requisicao_epi"] ."' ";
$sql .= "AND requisicao_epi_detalhes.reg_del = 0 ";
$sql .= "AND materiais.reg_del = 0 ";
$sql .= "AND requisicao_epi_detalhes.id_material = materiais.id_material ";
$sql .= "ORDER BY requisicao_epi_detalhes.data_requisicao ";

$db->select($sql,'MYSQL',true);

$count = $db->numero_registros;

foreach($db->array_select as $regs)
{
	$pdf->SetFont('Arial','',7);
	$pdf->Cell(20,5,mysql_php($regs["data_requisicao"]),1,0,'L',0);
	$pdf->Cell(15,5,$regs["quantidade"],1,0,'L',0);
	$pdf->Cell(20,5,$regs["ca"],1,0,'L',0);
	$pdf->Cell(105,5,$regs["material"],1,0,'L',0);
	$pdf->Cell(35,5,"",1,0,'L',0);
	$pdf->Cell(2,5,"",0,0,'L',0);
	$pdf->Cell(35,5,"",1,0,'L',0);
	$pdf->Cell(35,5,"",1,1,'L',0);

}

while (8-$count)
{
	$pdf->Cell(20,5,"",1,0,'L',0);
	$pdf->Cell(15,5,"",1,0,'L',0);
	$pdf->Cell(20,5,"",1,0,'L',0);
	$pdf->Cell(105,5,"",1,0,'L',0);
	$pdf->Cell(35,5,"",1,0,'L',0);
	$pdf->Cell(2,5,"",0,0,'L',0);
	$pdf->Cell(35,5,"",1,0,'L',0);
	$pdf->Cell(35,5,"",1,1,'L',0);
	$count++;
}

$pdf->Ln(3);
$pdf->SetFont('Arial','B',10);
$pdf->Cell(265,5,"ALMOXARIFADO",0,1,'L',0);
$pdf->SetFont('Arial','',10);
$pdf->Cell(65,5,"Data de baixa dos materiais devolvidos:",0,0,'L',0);
$pdf->Line(75,$pdf->GetY()+4,80,$pdf->GetY()+4);
$pdf->Line(83,$pdf->GetY(),81,$pdf->GetY()+4);
$pdf->Line(81,$pdf->GetY()+4,86,$pdf->GetY()+4);
$pdf->Line(89,$pdf->GetY(),87,$pdf->GetY()+4);
$pdf->Line(88,$pdf->GetY()+4,93,$pdf->GetY()+4);

$pdf->Cell(25,5,"",0,0,'L',0);
$pdf->Cell(65,5,"NOME LEGÍVEL: ",0,0,'L',0);
$pdf->Line(130,$pdf->GetY()+4,200,$pdf->GetY()+4);
$pdf->Ln(15);

$pdf->SetFont('Arial','B',6);
$pdf->Cell(30,4,'',0,1,'L',0);

$pdf->Output('RELATORIO_EPI_'.date('dmYhis').'.pdf', 'D');
?> 