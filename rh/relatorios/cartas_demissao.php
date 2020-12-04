<?php
/*
		Relatório Cartas Homologação, dispensa e Preposicao	
		
		Criado por Carlos Abreu / Otávio Pamplona  
		
		local/Nome do arquivo:
		../rh/relatorios/cartas_demissao.php
		
		Versão 0 --> VERSÃO INICIAL : 27/07/2007
		Versão 1 --> Atualização de rotinas : db_conect		
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

	$this->SetDrawColor(0,0,0);

	$this->SetXY(25,45);
}

//Page footer
function Footer()
{
	$this->SetXY(25,280);
	$this->SetFont('Arial','B',10);
	$this->Cell(160,5,"Rua xxxxxxxx, xx - Centro - xxxxxx",0,1,'C',0);
	$this->Cell(160,5,"cep: xxxxxxx - SP - Fone/fax: (11) xxxxx",0,1,'C',0);
	$this->Cell(160,5,"http://xxxxxxxxxxxxxx - E-mail: empresa@dominio.com.br".$this->GetY(),0,1,'C',0);	
}
}

$db = new banco_dados;

$pdf=new PDF('p','mm',A4);
$pdf->SetAutoPageBreak(false,15);
$pdf->SetMargins(25,15);
$pdf->SetLineWidth(0.3);

$diasestampa = mktime(0,0,0,date('m'),date('d'),date('Y'));
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

$pdf->AliasNbPages();

$sql = "SELECT * FROM ".DATABASE.".funcionarios ";
$sql .= "WHERE funcionarios.id_funcionario = '" . $_GET["id_funcionario"] . "' ";
$sql .= "AND funcionarios.reg_del = 0 ";

$db->select($sql,'MYSQL',true);

$reg_editar = $db->array_select[0];

if(numero_meses($reg_editar["clt_admissao"],$reg_editar["data_desligamento"])>=12)
{
	// Solicitação de Homologação, ocorre sempre que o tempo de admissao >= 12 meses
	$pdf->AddPage();
	$pdf->SetFont('Arial','BU',14);
	$pdf->Cell(170,7,"SOLICITAÇÃO DE AGENDAMENTO DE HOMOLOGAÇÃO",0,1,'C',0);
	
	$pdf->Ln(10);
	
	$pdf->SetFont('Arial','',10);
	$pdf->Cell(20,5,"Empresa: ",0,0,'L',0);
	$pdf->SetFont('Arial','B',12);
	$pdf->Cell(90,5,NOME_EMPRESA,0,1,'L',0);
	$pdf->Ln(5);
	$pdf->SetFont('Arial','',10);
	$pdf->Cell(20,5,"Telefone: ",0,0,'L',0);
	$pdf->SetFont('Arial','B',12);
	$pdf->Cell(50,5,"(11) xxxxxxxx",0,0,'L',0);
	$pdf->SetFont('Arial','',10);
	$pdf->Cell(15,5,"CNPJ: ",0,0,'L',0);
	$pdf->SetFont('Arial','B',12);
	$pdf->Cell(50,5,"xxxxxxxxx",0,1,'L',0);
	$pdf->Ln(5);
	$pdf->SetFont('Arial','',10);
	$pdf->Cell(20,5,"Endereço: ",0,0,'L',0);
	$pdf->SetFont('Arial','B',12);
	$pdf->Cell(155,5,"Rua xxxxxxx, xxx - Centro - xxxxxxxx - SP",0,1,'L',0);
	$pdf->Ln(5);
	$pdf->SetFont('Arial','',10);
	$pdf->Cell(50,5,"Local da prestação de serviço: ",0,0,'L',0);
	$pdf->SetFont('Arial','B',12);
	$pdf->Cell(155,5,"Rua xxxxxx, xx - xxxxxx",0,1,'L',0);
	$pdf->Cell(50,5,"",0,0,'L',0);
	$pdf->Cell(155,5,"xxxxxxxxx - SP",0,1,'L',0);
	$pdf->Ln(5);
	$pdf->SetFont('Arial','',10);
	$pdf->Cell(20,5,"Sindicato: ",0,0,'L',0);
	$pdf->SetFont('Arial','B',12);
	$pdf->Cell(155,5,"Sindicato",0,1,'L',0);
	$pdf->Ln(5);
	$pdf->SetFont('Arial','',10);
	$pdf->Cell(200,5,"Solicita que seja designada data para homologação do seguinte trabalhador: ",0,1,'L',0);
	$pdf->Ln(10);
	$pdf->SetFont('Arial','',10);
	$pdf->Cell(15,5,"Nome: ",0,0,'L',0);
	$pdf->SetFont('Arial','B',10);
	$pdf->Cell(155,5,$reg_editar["funcionario"],0,1,'L',0);
	$pdf->SetFont('Arial','',10);
	$pdf->Cell(30,5,"Data admissão: ",0,0,'L',0);
	$pdf->SetFont('Arial','B',10);
	$pdf->Cell(50,5,mysql_php($reg_editar["clt_admissao"]),0,0,'L',0);
	$pdf->SetFont('Arial','',10);
	$pdf->Cell(30,5,"Data demissão: ",0,0,'L',0);
	$pdf->SetFont('Arial','B',10);
	$pdf->Cell(30,5,mysql_php($reg_editar["data_desligamento"]),0,1,'L',0);
	$pdf->SetFont('Arial','',10);
	$pdf->Cell(10,5,"PIS: ",0,0,'L',0);
	$pdf->SetFont('Arial','B',10);
	$pdf->Cell(30,5,$reg_editar["pis_num"],0,1,'L',0);
	$pdf->Ln(20);

	$pdf->SetFont('Arial','',10);
	$pdf->Cell(150,5,CIDADE.", ".$diasarray["mday"]." de ".$mes[$diasarray["mon"]]." de ".$diasarray["year"],0,1,'L',0);
	
	$pdf->Ln(20);
	$pdf->SetDrawColor(0,0,0);
	$pdf->Line(25,$pdf->GetY(),70,$pdf->GetY());
	$pdf->Cell(90,5,"NOME",0,1,'L',0);
	$pdf->Cell(90,5,"CARGO",0,1,'L',0);
	$pdf->Ln(5);
	
	$pdf->Cell(90,5,"Data de agendamento:",0,1,'L',0);
	$pdf->Ln(10);
	$pdf->SetFont('Arial','B',12);
	$pdf->Cell(160,5,"NÃO SERÁ TOLERADO ATRASO SUPERIOR A 10 MINUTOS",0,1,'C',0);
	$pdf->Line(0,$pdf->GetY(),210,$pdf->GetY());
	$pdf->SetFont('Arial','',10);
	$pdf->Cell(160,5,"Reservado ao Ministério do Trabalho e Emprego no ato da Homologação",0,1,'C',0);
	$pdf->Ln(2);
	$pdf->Cell(40,5,"( ) Homologação",0,0,'L',0);
	$pdf->Cell(40,5,"( ) Sim",0,0,'L',0);
	$pdf->Cell(40,5,"( ) Não,Motivo:",0,1,'L',0);
	$pdf->Cell(80,5,"",0,0,'L',0);
	$pdf->Cell(40,5,"Não Comparecimento:",0,0,'L',0);
	$pdf->Cell(40,5,"( ) Empregado",0,1,'L',0);
	$pdf->Cell(120,5,"",0,0,'L',0);
	$pdf->Cell(40,5,"( ) Empregador",0,1,'L',0);
	$pdf->Cell(120,5,"",0,0,'L',0);
	$pdf->Cell(40,5,"Outros",0,0,'L',0);
	$pdf->Line(160,$pdf->GetY()+4,200,$pdf->GetY()+4);
	
	$pdf->Ln(10);
	$pdf->Line(25,$pdf->GetY(),70,$pdf->GetY());
	$pdf->Cell(40,5,"Carimbo e Assinatura",0,1,'L',0);
	$pdf->Ln(10);
	
	// Fim Homologação
	
	// Carta de preposição, ocorre sempre que o tempo de admissao >= 12 meses
	$pdf->AddPage();
	$pdf->SetFont('Arial','BU',14);
	$pdf->Cell(170,7,"CARTA DE PREPOSIÇÃO",0,1,'C',0);
	
	$pdf->Ln(10);
	
	$pdf->SetFont('Arial','',10);
	$pdf->Cell(170,5,"Através da presente, a empresa ".NOME_EMPRESA.", com sede no município de ".CIDADE." - SP,",0,1,'L',0);
	$pdf->Cell(170,5,"estabelecida á Rua xxxxx, xxx, Centro, xxxxx, inscrita no CNPJ sob o nº xxxxxxxxxx, autoriza o Sra.",0,1,'L',0);
	$pdf->SetFont('Arial','B',10);
	$pdf->Cell(70,5,"NOME,",0,0,'L',0);
	$pdf->SetFont('Arial','',10);
	$pdf->Cell(90,5,"portador da cédula de identidade nº xxxxxx / SP, ",0,1,'L',0);
	$pdf->Cell(170,5,"a representa-la junto a Delegacia Regional do Trabalho, para fins de homologação ",0,1,'L',0);
	$pdf->Cell(170,5,"do ex-funcionario: ",0,1,'L',0);
	$pdf->Ln(10);
	$pdf->SetFont('Arial','B',10);
	$pdf->Cell(100,5,$reg_editar["funcionario"],0,0,'L',0);
	$pdf->SetFont('Arial','',10);
	$pdf->Cell(15,5,"CTPS: ",0,0,'L',0);
	$pdf->SetFont('Arial','B',10);
	$pdf->Cell(10,5,$reg_editar["ctps_num"],0,1,'L',0);
	$pdf->SetFont('Arial','',10);
	$pdf->Ln(20);
	$pdf->Cell(170,5,"Este funcionário foi contratado para prestar serviços em xxxxxxx - SP, á Rua xxxxxxx, xx ",0,1,'L',0);
	$pdf->Cell(170,5,"Centro.",0,1,'L',0);
	$pdf->Ln(10);
	$pdf->Cell(170,5,"A empresa não possui filiação Sindical na região, filiação Sindical em xxxxxxx - SP.",0,0,'L',0);
	$pdf->Ln(20);
	$pdf->SetFont('Arial','',10);
	$pdf->Cell(150,5,"cidade, ".$diasarray["mday"]." de ".$mes[$diasarray["mon"]]." de ".$diasarray["year"],0,1,'L',0);
	
	$pdf->Ln(20);
	$pdf->SetDrawColor(0,0,0);
	$pdf->Line(25,$pdf->GetY(),70,$pdf->GetY());
	$pdf->Cell(90,5,"NOME",0,1,'L',0);
	$pdf->Cell(90,5,"CARGO",0,1,'L',0);
	$pdf->Ln(10);

}

$demissao = explode("/",$reg_editar["tipo_demissao"]);

if($demissao[0]!="PEDIDO DEMISSÃO")
{
	$pdf->AddPage();
	
	$pdf->SetFont('Arial','B',10);
	$pdf->Cell(150,5,NOME_EMPRESA,0,1,'L',0);
	$pdf->Cell(150,5,"CNPJ: XXXXXXXXXX",0,1,'L',0);
	$pdf->Ln(10);
	
	$pdf->SetFont('Arial','',10);
	$pdf->Cell(150,5,CIDADE.", ".$diasarray["mday"]." de ".$mes[$diasarray["mon"]]." de ".$diasarray["year"],0,1,'L',0);
	$pdf->Ln(10);
	$pdf->Cell(35,5,"Prezado funcionário ",0,0,'L',0);
	$pdf->Cell(140,5,$reg_editar["funcionario"],0,1,'L',0);
	$pdf->Cell(140,5,"RG: ".$reg_editar["identidade_num"],0,1,'L',0);
	$pdf->Ln(10);
	
	$pdf->SetFont('Arial','BU',14);
	$pdf->Cell(170,7,"DISPENSA ".trim($demissao[0]),0,1,'C',0);
	
	$pdf->Ln(10);
	
	$pdf->SetFont('Arial','',10);
	$pdf->Cell(160,5,"Através da presente, comunicamos que esta empresa decidiu rescindir o Contrato de Trabalho com V.Sa ",0,1,'L',0);
	$pdf->Cell(20,5,"á partir de: ",0,0,'L',0);
	$pdf->SetFont('Arial','B',10);
	$pdf->Cell(30,5,mysql_php($reg_editar["data_desligamento"]),0,1,'L',0);
	
	$pdf->Ln(15);
	
	$pdf->Cell(30,5,$reg_editar["tipo_demissao"],0,1,'L',0);
	
	$pdf->Ln(10);
	
	if(trim($demissao[1])=='AVISO INDENIZADO')
	{
		$pdf->SetFont('Arial','B',10);
		$pdf->Cell(65,5,"Sem o cumprimento do aviso prévio,",0,0,'L',0);
		$pdf->SetFont('Arial','',10);
		$pdf->Cell(50,5,"pois o mesmo será indenizado.",0,1,'L',0);			
	}
	else
	{
		if(trim($demissao[1])=='AVISO TRABALHADO')
		{
			$pdf->SetFont('Arial','B',10);
			$pdf->Cell(65,5,"Com o cumprimento do aviso prévio.",0,0,'L',0);
				
		}	
	}	
	
	$pdf->Ln(15);
	
	$pdf->SetFont('Arial','',10);
	$pdf->Cell(160,5,"Atenciosamente,",0,1,'L',0);
	$pdf->Ln(15);
	$pdf->SetDrawColor(0,0,0);
	$pdf->Line(25,$pdf->GetY(),70,$pdf->GetY());
	$pdf->Cell(90,5,"NOME",0,1,'L',0);
	$pdf->Cell(90,5,"CARGO",0,1,'L',0);
	$pdf->Ln(15);
	$pdf->Cell(160,5,"Afirmo estar ciente do processo acima citado,",0,1,'L',0);
	$pdf->Ln(15);
	$pdf->Line(25,$pdf->GetY(),100,$pdf->GetY());
	$pdf->Cell(100,5,$reg_editar["funcionario"],0,1,'L',0);
	$pdf->Ln(10);
}

$pdf->Output('CARTA_DEMISSAO_'.date('dmYhis').'.pdf', 'D');
?> 