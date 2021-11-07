<?php
/*
	  Relatório Fechamento Folha - CLT	
	  
	  Criado por Carlos Abreu
	  
	  local/Nome do arquivo:
	  ../financeiro/relatorios/rel_fechamentofolha_clt.php
	  
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
	$this->Cell(75,5,"FUNCIONÁRIO",0,0,'L',0);	
	$this->Cell(25);	
	$this->Cell(20,5,"CONTRATO",0,0,'L',0);	
	$this->Cell(80);
	$this->Cell(15,5,"H. N.",0,0,'L',0);
	$this->Cell(30);	
	$this->Cell(15,5,"H. A.",0,0,'L',0);
	$this->Ln(5);	
}

//Page footer
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

//Seta o cabeçalho
$pdf->departamento=NOME_EMPRESA;
$pdf->titulo="RELATÓRIO DE HORAS - FUNCIONÁRIOS CLT/EST";
//$pdf->setor=$abreviacao;
$pdf->codigodoc="04";
$pdf->codigo=01;
	
$mespassado_stamp = mktime(0,0,0,date("m"),0,date("Y"));

$data_ini = $_POST["dataini"];
$datafim = $_POST["data_fim"];

$array_dataini = explode("/",$data_ini);
$array_datafim = explode("/", $datafim);

$pdf->emissao=date("d/m/Y");
$pdf->versao_documento="Período: " . $data_ini . " á " . $datafim;

$pdf->AliasNbPages();
$pdf->AddPage();


$pdf->SetFont('Arial','',8);

//Se caso o arquivo PDF já existir, manda mensagem de confirmação de substituição ao usuário.
$dh = opendir(DOCUMENTOS_FINANCEIRO.COMPROVANTES_FECHAMENTO); 

// loop que busca todos os arquivos até que não encontre mais nada 
while (false !== ($filename = readdir($dh))) 
{ 
	// verificando se o arquivo é .pdf 
	if ($filename == "REL_CLT_" . $array_dataini[2] . $array_dataini[1] . "-" . $array_datafim[2] . $array_datafim[1] . " " . date("dmY") . '.pdf') 
	{ 

		?>
		<script>
			if(!confirm('O Relatório selecionado já existe no arquivo, e pode ser visualizado através do botão "Arquivo". Deseja continuar e substituir o anterior?'))
			{
				window.close();			
			}
		</script>
		<?php
	}
}

$sql = "SELECT funcionarios.funcionario, funcionarios.id_funcionario, SUM( TIME_TO_SEC( hora_normal ) ) AS Soma_Segundos_Normais, SUM( TIME_TO_SEC( hora_adicional ) + TIME_TO_SEC( hora_adicional_noturna ) ) AS Soma_Segundos_Adicionais "; 
$sql .= "FROM ".DATABASE.".apontamento_horas, ".DATABASE.".funcionarios ";
$sql .= "WHERE apontamento_horas.id_funcionario = funcionarios.id_funcionario ";
$sql .= "AND apontamento_horas.reg_del = 0 ";
$sql .= "AND funcionarios.reg_del = 0 ";
$sql .= "AND apontamento_horas.data BETWEEN '" . php_mysql($_POST["dataini"]) . "' AND '" . php_mysql($_POST["data_fim"]) . "' ";
$sql .= "AND funcionarios.situacao IN ('ATIVO','FECHAMENTO FOLHA') ";
$sql .= "GROUP BY funcionarios.id_funcionario ";
$sql .= "ORDER BY funcionarios.funcionario ";

$db->select($sql,'MYSQL',true);

$array_select1 = $db->array_select;	

foreach($array_select1 as $regs)
{	
	//INSERIDO POR CARLOS ABREU - 10/01/2008 
	//OBJETIVO: Transferir formularios de Financeiro -> RH
	$sql = "SELECT * FROM ".DATABASE.".salarios ";
	$sql .= "WHERE salarios.id_funcionario = '" . $regs["id_funcionario"] . "' ";
	$sql .= "AND DATE_FORMAT(data , '%Y%m%d' ) < '".str_replace("-","",php_mysql($_GET["data_fim"]))."' ";
	$sql .= "AND salarios.reg_del = 0 ";
	$sql .= "ORDER BY id_salario DESC LIMIT 1 ";
	
	$db->select($sql,'MYSQL',true);

	$tipocont = $db->array_select[0];
	
	if($tipocont[" tipo_contrato"]=='CLT' || $tipocont[" tipo_contrato"]=='EST')
	{
		//Formata as horas normais
		$ahora_normal = explode(":", sec_to_time($regs["Soma_Segundos_Normais"]));
		$shora_normal = $ahora_normal[0] . ":" . $ahora_normal[1];
		
		//Formata as horas adicionais
		$ahora_adicional = explode(":", sec_to_time($regs["Soma_Segundos_Adicionais"]));
		$shora_adicional = $ahora_adicional[0] . ":" . $ahora_adicional[1];		
	
		$funcionario = $regs["funcionario"];
		
		$contrato = $tipocont[" tipo_contrato"];
		
		$periodo = $_POST["dataini"] .' a '. $_POST["data_fim"];
		
		$htotal_normal = $shora_normal;
		
		$htotal_adicional = $shora_adicional;
		
		$pdf->HCell(75,5,$funcionario,0,0,'L',0);

		$pdf->Cell(25);

		$pdf->HCell(20,5,$contrato,0,0,'L',0);

		$pdf->Cell(80);

		$pdf->Cell(15,5,$htotal_normal,0,0,'L',0);

		$pdf->Cell(30);

		$pdf->Cell(15,5,$htotal_adicional,0,1,'L',0);

		$pdf->SetFont('Arial','',8);
	}	
}
		
$pdf->Ln(2);
$pdf->Line(125,$pdf->GetY(),280,$pdf->GetY());
$pdf->Cell(100,5,"",0,'L',0);
$pdf->SetFont('Arial','B',8);		

$pdf->Ln(50);

$pdf->Output('FECHAMENTO_CLT_'.date('dmYHmi').'.pdf','D');

//Grava o arquivo PDF em uma pasta, no formato "TIPO AnoMesInicial-AnoMesFinal DataGeracao.pdf".
$pdf->Output(DOCUMENTOS_FINANCEIRO.COMPROVANTES_FECHAMENTO . "REL_CLT_" . $array_dataini[2] . $array_dataini[1] . "-" . $array_datafim[2] . $array_datafim[1] . " " . date("dmY") . '.pdf','F');

?> 