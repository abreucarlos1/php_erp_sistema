<?php
/*
		Relatório TERMO DE RESPONSABILIDADE
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		../financeiro/rel_termo_responsabilidade.php
		
		Versão 0 --> VERSÃO INICIAL - 14/01/2005
		Versão 1 --> ATUALIZAÇÃO LAY OUT - 28/03/2006
		Versão 2 --> atualização classe banco de dados - 21/01/2015 - Carlos Abreu
		
*/


require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));

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
$pdf->setor="FIN";
$pdf->codigodoc="01"; //"00";
$pdf->codigo="01"; //Numero OS
$pdf->emissao=date("d/m/Y");

$pdf->AliasNbPages();
$pdf->AddPage();

$db = new banco_dados;

$sql = "SELECT * FROM ".DATABASE.".funcionarios, ".DATABASE.".ordem_servico, ".DATABASE.".empresas, ".DATABASE.".adiantamento_funcionario, ".DATABASE.".requisicao_despesas ";
$sql .= "WHERE adiantamento_funcionario.id_adiantamento_funcionario = '" . $_GET["id_adiantamento"] . "' ";
$sql .= "AND requisicao_despesas.responsavel_despesas = funcionarios.id_funcionario ";
$sql .= "AND requisicao_despesas.id_requisicao_despesa = adiantamento_funcionario.id_requisicao_despesa ";
$sql .= "AND requisicao_despesas.id_os = ordem_servico.id_os ";
$sql .= "AND requisicao_despesas.id_empresa = empresas.id_empresa ";

$registro = $db->select($sql,'MYSQL');

$regs = mysqli_fetch_assoc($registro);

$sql_sol = "SELECT * FROM ".DATABASE.".funcionarios, ".DATABASE.".requisicao_despesas, ".DATABASE.".adiantamento_funcionario ";
$sql_sol .= "WHERE adiantamento_funcionario.id_adiantamento_funcionario = '" . $_GET["id_adiantamento"] . "' ";
$sql_sol .= "AND requisicao_despesas.id_funcionario = funcionarios.id_funcionario ";
$sql_sol .= "AND requisicao_despesas.id_requisicao_despesa = adiantamento_funcionario.id_requisicao_despesa ";

$registro_sol = $db->select($sql_sol,'MYSQL');

$regs_sol = mysqli_fetch_assoc($registro_sol);

$pdf->SetFont('Arial','BU',16);
$pdf->Cell(170,7,"TERMO DE RESPONSABILIDADE DE ADIANTAMENTO",0,1,'C',0);
$pdf->Cell(170,7,"DE DESPESAS DE VIAGEM",0,1,'C',0);

$pdf->Ln(5);

$pdf->SetLineWidth(0.3);
$pdf->SetDrawColor(0,0,0);
$pdf->Line(0,$pdf->GetY(),250,$pdf->GetY());
$pdf->SetFont('Arial','B',10);
$pdf->Cell(15,5,"Requisição nº: ".$regs["id_requisicao_despesa"],0,1,'L',0);
$pdf->Cell(15,5,"Solicitante: ".$regs_sol["funcionario"],0,1,'L',0);
$pdf->Line(0,$pdf->GetY(),250,$pdf->GetY());

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
$pdf->Cell(170,5,"* OS: ".sprintf("%05d",$regs["os"]). " - ".$regs["abreviacao"],0,1,'L',0);
$pdf->Cell(170,5,"* Destino / Obs.: ". $regs["destino"],0,1,'L',0);
$pdf->Cell(170,5,"* Período: ".mysql_php($regs["periodo_inicial"]) ." á ". mysql_php($regs["periodo_final"]). ";",0,1,'L',0);
$pdf->SetLineWidth(0.3);
$pdf->SetDrawColor(0,0,0);
$pdf->Line(0,$pdf->GetY(),250,$pdf->GetY());
if($regs["refeicao"])
{
	$pdf->Cell(170,5,"* Refeição;",0,1,'L',0);
}

if($regs["veiculo"])
{
	$pdf->Cell(170,5,"* Veículo: ".$regs["veiculo_modelo"] . " - Placa: ".$regs["veiculo_placa"] .";" ,0,1,'L',0);
	$pdf->Cell(170,5,"* Período(previsão) - Saída: ".$regs["previsao_saida"] . " - Retorno: ".$regs["previsao_retorno"] .";" ,0,1,'L',0);
}

if($regs["alugar_veiculo"])
{
	$pdf->Cell(170,5,"* Veículo alugado no destino no período de: ".mysql_php($regs["veiculo_periodo_inicial"]) . " á ".mysql_php($regs["veiculo_periodo_final"]).";",0,1,'L',0);
}

if($regs["hotel"])
{
	$pdf->Cell(20,5,"Hotel: ",0,0,'L',0);
	$pdf->Cell(150,5,$regs["hotel_nome"],0,1,'L',0);
	$pdf->Cell(20,5,"",0,0,'L',0);
	$pdf->Cell(150,5,"End.:".$regs["hotel_endereco"]." - Tel.: ".$regs["hotel_telefone"],0,1,'L',0);
	$pdf->Cell(20,5,"",0,0,'L',0);
	$pdf->Cell(150,5,"Tel.: ".$regs["hotel_telefone"],0,1,'L',0);
	$pdf->Cell(20,5,"",0,0,'L',0);
	$pdf->Cell(20,5,"Faturamento: ".$regs["hotel_faturamento"],0,1,'L',0);
}

$pdf->SetFont('Arial','B',10);
$pdf->Cell(170,5,"Valor do adiantamento R$ ".number_format($regs["valor_adiantamento"],2,",",".")." (".maiusculas(valorPorExtenso($regs["valor_adiantamento"])).")",0,1,'L',0);
$pdf->Line(0,$pdf->GetY(),250,$pdf->GetY());

$pdf->SetFont('Arial','',10);
$pdf->Cell(170,5,"Sendo assim declaro ter pleno conhecimento dos seguintes itens necessários á execução de minhas",0,1,'L',0);
$pdf->Cell(170,5,"atividades com relação as despesas:",0,1,'L',0);
$pdf->Ln(3);
$pdf->SetFont('Arial','B',10);
$pdf->Cell(15,5,"1.",0,0,'L',0);
$pdf->SetFont('Arial','',10);
$pdf->Cell(155,5,"É proibida a utilização do dinheiro para quaisquer outros fins, a não ser os relacionados",0,1,'L',0);
$pdf->Cell(15,5,"",0,0,'L',0);
$pdf->Cell(155,5,"as  despesas de viagem a trabalho;",0,1,'L',0);
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
$pdf->Cell(155,5,"As despesas de refeição(R$ 00,00 almoço e R$ 00,00 jantar) serão válidas para ",0,1,'L',0);
$pdf->Cell(15,5,"",0,0,'L',0);
$pdf->Cell(155,5,"serviços executados em um raio superior a 100 km do local de trabalho;",0,1,'L',0);

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

$pdf->Cell(150,5,CIDADE.", ".$diasarray["mday"]." de ".$mes[$diasarray["mon"]]." de ".$diasarray["year"],0,1,'R',0);

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

$pdf->Output('TERMO_RESPONSABILIDADE_'.date('dmYhis').'.pdf', 'D');
?> 