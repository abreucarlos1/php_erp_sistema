<?
/*
		Relatório TERMO DE RESPONSABILIDADE
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		../financeiro/relatorios/rel_termo_reponsabilidade.php
		
		Versão 0 --> VERSÃO INICIAL - 14/01/2005
		Versão 1 --> ATUALIZAÇÃO LAY OUT - 28/03/2006	
		Versão 2 --> Atualização lay-out - 18/06/2014 - Carlos Abreu
		Versão 3 --> Inclusão dos campos reg_del nas consultas - 20/11/2017 - Carlos Abreu		
*/

require_once(implode(DIRECTORY_SEPARATOR,array('..','..','config.inc.php')));

require_once(INCLUDE_DIR."include_pdf.inc.php");

class PDF extends FPDF
{
	
var $documento;

//Page header
function Header()
{
	$this->Image(DIR_IMAGENS.'logo_pb.png',26,16,40);
	$this->Ln(1);
	$this->SetFont('Arial','',6);
	$this->Cell(146,4,'',0,0,'L',0);
	$this->Cell(12,4,'DOC:',0,0,'L',0);
	$this->Cell(12,4,$this->documento,0,1,'R',0);
	$this->SetLineWidth(0.3);
	$this->Line(172,19.5,195,19.5);
	$this->Cell(158,4,'EMISSÃO:',0,0,'R',0); //aqui
	$this->Cell(12,4,$this->Emissao(),0,1,'R',0); //aqui
	$this->Line(172,23.5,195,23.5);
	$this->Cell(146,4,'',0,0,'L',0);
	$this->Cell(12,4,'FOLHA:',0,0,'L',0);
	$this->Cell(12,4,$this->PageNo().' de {nb}',0,1,'R',0);
	$this->Line(172,27.5,195,27.5);
	$this->Ln(8);
	$this->Cell(170,4,"",0,1,'R',0);
	$this->SetFont('Arial','B',8);
	$this->Cell(170,4,"",0,1,'R',0);
	$this->SetFont('Arial','',9);
	$this->SetDrawColor(0,0,0);
	$this->SetXY(25,35);
}

//Page footer
function Footer()
{
	
}
}

//Instanciation of inherited class
$pdf=new PDF('p','mm',A4);
$pdf->SetAutoPageBreak(true,5);
$pdf->SetMargins(25,15);
$pdf->SetLineWidth(0.3);

//Seta o cabeçalho
$pdf->departamento=NOME_EMPRESA;
$pdf->documento="FIN-01-R3";
$pdf->emissao=date("d/m/Y");

$pdf->AliasNbPages();
$pdf->AddPage();

$db = new banco_dados;

//seleciona a requisição
$sql = "SELECT * FROM ".DATABASE.".funcionarios, ".DATABASE.".ordem_servico, ".DATABASE.".requisicao_despesas ";
$sql .= "WHERE requisicao_despesas.id_requisicao_despesa = '" . $_GET["id_requisicao"] . "' ";
$sql .= "AND funcionarios.reg_del = 0 ";
$sql .= "AND ordem_servico.reg_del = 0 ";
$sql .= "AND requisicao_despesas.reg_del = 0 ";
$sql .= "AND requisicao_despesas.responsavel_despesas = funcionarios.id_funcionario ";
$sql .= "AND requisicao_despesas.id_os = ordem_servico.id_os ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}

$regs = $db->array_select[0];

//pega o solicitante da requisição
$sql = "SELECT * FROM ".DATABASE.".funcionarios ";
$sql .= "WHERE funcionarios.id_funcionario = '" . $regs["id_funcionario"] . "' ";
$sql .= "AND funcionarios.reg_del = 0 ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}

$regs_sol = $db->array_select[0];

$pdf->SetFont('Arial','BU',16);
$pdf->Cell(170,7,"TERMO DE RESPONSABILIDADE DE ADIANTAMENTO",0,1,'C',0);
$pdf->Cell(170,7,"DE DESPESAS DE VIAGEM",0,1,'C',0);

$pdf->Ln(5);

$pdf->SetLineWidth(0.3);
$pdf->SetDrawColor(0,0,0);
$pdf->Line(10,$pdf->GetY(),200,$pdf->GetY());

$pdf->SetFont('Arial','B',10);
$pdf->Cell(15,5,"Requisição nº: ".sprintf("%05d",$regs["id_requisicao_despesa"]),0,1,'L',0);
$pdf->Cell(15,5,"Solicitante: ".$regs_sol["funcionario"],0,1,'L',0);
$pdf->Line(10,$pdf->GetY(),200,$pdf->GetY());

$pdf->Ln(5);
$pdf->SetFont('Arial','',10);
$pdf->Cell(5,5,"Eu, ",0,0,'L',0);
$pdf->SetFont('Arial','B',10);
$pdf->Cell(90,5," * ".$regs["funcionario"]." * ",0,0,'C',0);
$pdf->SetFont('Arial','',10);
$pdf->Cell(85,5," responsável pelo adiantamento de despesas",0,1,'L',0);
$pdf->Cell(115,5,"de viagem da ".NOME_EMPRESA.", declaro ter recebido a importância e/ou veículo e/ou passagem ",0,1,'L',0);
$pdf->SetFont('Arial','',10);
$pdf->Cell(92,5,"aérea que se destina aos gastos relacionados abaixo, durante a viagem á trabalho:",0,1,'L',0);
$pdf->HCell(170,5,"* OS: ".sprintf("%05d",$regs["os"]). " - ".$regs["descricao"],0,1,'L',0);
$pdf->Cell(170,5,"* Atividade / Obs.: ". $regs["atividade"],0,1,'L',0);
$pdf->Cell(170,5,"* Período: ".mysql_php($regs["periodo_inicial"]) ." á ". mysql_php($regs["periodo_final"]). ";",0,1,'L',0);
$pdf->SetLineWidth(0.3);
$pdf->SetDrawColor(0,0,0);
$pdf->Line(10,$pdf->GetY(),200,$pdf->GetY());

//obtem as despesas cadastradas no or�amento
/*	
$sql = "SELECT AF2010.AF2_COMPOS, AF2010.AF2_DESCRI, AF2010.AF2_QUANT FROM AF1010 WITH(NOLOCK), AF2010 WITH(NOLOCK) ";
$sql .= "WHERE AF1010.D_E_L_E_T_ = '' ";
$sql .= "AND AF2010.D_E_L_E_T_ = '' ";
$sql .= "AND AF1010.AF1_ORCAME = '".sprintf("%010d",$regs["os"])."' "; 
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
*/

//filtra as necessidades requisitadas
$sql = "SELECT * FROM ".DATABASE.".requisicao_despesas_necessidades ";
$sql .= "WHERE requisicao_despesas_necessidades.id_requisicao_despesa = '" . $regs["id_requisicao_despesa"] . "' ";
$sql .= "AND requisicao_despesas_necessidades.reg_del = 0 ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}
		
foreach($db->array_select as $cont2)
{
	if($cont2["cod_necessidade"]=='DES99')
	{
		$item = $cont2["item"];
	}

	if($cont2["cod_necessidade"]=='DES99' || $item!='')
	{
		$pdf->Cell(170,5,"* ".$item.";",0,1,'L',0);
	}
	else
	{
		switch ($cont2["cod_necessidade"])
		{
			case 'DES98': //CARRO FROTA
				$pdf->Cell(170,5,"* ".$array_items_desp[$cont2["cod_necessidade"]]." - ". $regs["veiculo_modelo"] . " / Placa: ".$regs["veiculo_placa"].";",0,1,'L',0);
			break;
			
			case 'DES03': //ALUGUEL CARRO
			case 'DES04':
				$pdf->Cell(170,5,"* VEÍCULO ALUGADO;",0,1,'L',0);
			break;
			
			default:
				$pdf->Cell(170,5,"* ".$array_items_desp[$cont2["cod_necessidade"]].";",0,1,'L',0);
		}		
	}
}

$pdf->SetFont('Arial','B',10);
$pdf->Cell(170,5,"valor do adiantamento R$ ".number_format($regs["valor_adiantamento"],2,",",".")." (".maiusculas(valorPorExtenso($regs["valor_adiantamento"])).")",0,1,'L',0);
$pdf->Line(10,$pdf->GetY(),200,$pdf->GetY());

$pdf->SetFont('Arial','',10);
$pdf->Cell(170,5,"Sendo assim declaro ter pleno conhecimento dos seguintes itens necessários á execução de minhas",0,1,'L',0);
$pdf->Cell(170,5,"atividades com relação as despesas:",0,1,'L',0);
$pdf->Ln(3);

$pdf->SetFont('Arial','B',10);
$pdf->Cell(15,5,"1.",0,0,'L',0);
$pdf->SetFont('Arial','',10);
$pdf->Cell(155,5,"É proibida a utilização do dinheiro para quaisquer outros fins, a não ser os relacionados",0,1,'L',0);
$pdf->Cell(15,5,"",0,0,'L',0);
$pdf->Cell(155,5,"as despesas de viagem a trabalho;",0,1,'L',0);
$pdf->Ln(3);

$pdf->SetFont('Arial','B',10);
$pdf->Cell(15,5,"2.",0,0,'L',0);
$pdf->SetFont('Arial','',10);
$pdf->Cell(155,5,"As despesas  só serão aceitas mediante a apresentação dos respectivos comprovantes;",0,1,'L',0);
$pdf->Ln(3);

$pdf->SetFont('Arial','B',10);
$pdf->Cell(15,5,"3.",0,0,'L',0);
$pdf->SetFont('Arial','',10);
$pdf->Cell(155,5,"A prestação de contas deve ser realizada imediatamente após o retorno",0,1,'L',0);
$pdf->Cell(15,5,"",0,0,'L',0);
$pdf->Cell(155,5,"junto ao DEPARTAMENTO FINANCEIRO;",0,1,'L',0);
$pdf->Ln(3);

$pdf->SetFont('Arial','B',10);
$pdf->Cell(15,5,"4.",0,0,'L',0);
$pdf->SetFont('Arial','',10);
$pdf->Cell(155,5,"É EXPRESSAMENTE PROÍBIDO A UTILIZAÇÃO DO ADIANTAMENTO PARA A COMPRA DE ",0,1,'L',0);
$pdf->Cell(15,5,"",0,0,'L',0);
$pdf->Cell(155,5,"CIGARROS E/OU BEBIDAS ALCOÓLICAS;",0,1,'L',0);

$pdf->Ln(5);

$data = explode("-",$regs["data_adiantamento"]);

$diasestampa = mktime(0,0,0,$data[1],$data[2],$data[0]);

$diasarray = getdate($diasestampa);

$mes[1] = "Janeiro";
$mes[2] = "Fevereiro";
$mes[3] = "Março";
$mes[4] = "Abril";
$mes[5] = "Maio";
$mes[6] = "Junho";
$mes[7] = "Julho";
$mes[8] = "Agosto";
$mes[9] = "Setembro";
$mes[10] = "Outubro";
$mes[11] = "Novembro";
$mes[12] = "Dezembro";

$pdf->Cell(150,5, CIDADE . ", ".$diasarray["mday"]." de ".$mes[$diasarray["mon"]]." de ".$diasarray["year"],0,1,'R',0);

$pdf->Ln(10);
$pdf->SetDrawColor(0,0,0);
$pdf->Line(110,$pdf->GetY(),180,$pdf->GetY());
$pdf->SetFont('Arial','',8);
$pdf->Cell(85,5,"",0,0,'L',0);
$pdf->SetFont('Arial','',10);
$pdf->Cell(90,5,$regs["funcionario"],0,1,'L',0);
$pdf->SetFont('Arial','',8);
$pdf->Cell(85,5,"",0,0,'L',0);
$pdf->Cell(90,5,"Responsável",0,1,'L',0);
$pdf->Ln(5);

$pdf->SetLineWidth(0.3);
$pdf->SetDrawColor(0,0,0);
$pdf->Line(0,$pdf->GetY(),250,$pdf->GetY());

$pdf->SetFont('Arial','B',10);
$pdf->Ln(5);
$pdf->Cell(170,5,"CONTROLE DE ADIANTAMENTO",0,1,'C',0);
$pdf->SetFont('Arial','',10);
$pdf->Ln(5);
$pdf->Cell(90,5,$regs["funcionario"],0,1,'L',0);
$pdf->Cell(90,5,mysql_php($regs["data_adiantamento"]),0,1,'L',0);
$pdf->Cell(170,5,"R$ ".number_format($regs["valor_adiantamento"],2,",",".")." (".maiusculas(valorPorExtenso($regs["valor_adiantamento"])).")",0,1,'L',0);

$pdf->Output('TERMO_RESPONSABILIDADE_'.date('YmdHis').'.pdf','I');
?> 