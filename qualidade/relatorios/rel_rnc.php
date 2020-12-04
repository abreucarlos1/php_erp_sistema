<?php
/*
	  Relatório Não Conformidade	
	  
	  Criado por Carlos Abreu / Otávio Pamplona
	  
	  local/Nome do arquivo:
	  ../relatorios/rel_rnc.php
	  
	  Versão 0 --> VERSÃO INICIAL - 01/07/2014
*/	

ini_set('max_execution_time', 0); // No time limit
ini_set('post_max_size', '20M');
ini_set('upload_max_filesize', '20M');

require_once(implode(DIRECTORY_SEPARATOR,array('..','..','config.inc.php')));

require_once(INCLUDE_DIR."include_pdf.inc.php");

class PDF extends FPDF
{
	var $documento;
	
	//Page header
	function Header()
	{
		$this->Image(DIR_IMAGENS.'logo_pb.png',16,16,40);
		$this->Ln(1);
		$this->SetFont('Arial','',6);
		$this->Cell(146,4,'',0,0,'L',0);
		$this->Cell(12,4,'DOC:',0,0,'L',0);
		$this->Cell(12,4,$this->documento,0,1,'R',0);
		$this->SetLineWidth(0.3);
		$this->Line(172,19.5,195,19.5);
		$this->Line(172,23.5,195,23.5);
		$this->Cell(146,4,'',0,0,'L',0);
		$this->Cell(12,4,'FOLHA:',0,0,'L',0);
		$this->Cell(12,4,$this->PageNo().' de {nb}',0,0,'R',0);
		$this->Ln(8);
		$this->SetFont('Arial','B',12);
		$this->Cell(170,4,$this->Titulo(),0,1,'R',0);
		$this->SetFont('Arial','B',8);
		$this->Cell(170,4,$this->Revisao(),0,1,'R',0);
		$this->SetFont('Arial','',9);
		$this->SetLineWidth(1);
		$this->SetDrawColor(0,0,0);
		$this->Line(15,40,195,40);
		$this->SetXY(15,42);
	}
	
	//Page footer
	function Footer(){}
}

$db = new banco_dados;
	
$pdf=new PDF('p','mm','A4');
$pdf->SetAutoPageBreak(true,30);
$pdf->SetMargins(15,15);
$pdf->SetLineWidth(0.1);

$pdf->departamento=NOME_EMPRESA;
$pdf->titulo="RELATÓRIO DE NÃO CONFORMIDADE INTERNA";
$pdf->documento="";
$pdf->emissao=date("d/m/Y");

$pdf->AliasNbPages();

$pdf->AddPage();

$pdf->SetFont('Arial','',8);

$sql = "SELECT * FROM ".DATABASE.".funcionarios, ".DATABASE.".setores, ".DATABASE.".nao_conformidades  ";
$sql .= "LEFT JOIN ".DATABASE.".ordem_servico ON (nao_conformidades.id_os = ordem_servico.id_os), ".DATABASE.".tipo_origem, ".DATABASE.".tipos_documentos_planos_acao, ".DATABASE.".empresas ";
$sql .= "WHERE nao_conformidades.nao_conformidade_delete = 0 ";
$sql .= "AND nao_conformidades.id_funcionario_criador = funcionarios.id_funcionario ";
$sql .= "AND nao_conformidades.id_setor = setores.id_setor ";
$sql .= "AND tipos_origem.id_tipo_origem = nao_conformidades.id_tipo_origem ";
$sql .= "AND tipos_documentos_planos_acao.id_tipo_documento = nao_conformidades.id_tipo_documento ";
$sql .= "AND empresas.id_empresa_erp = nao_conformidades.id_cliente ";
$sql .= "AND nao_conformidades.id_nao_conformidade = '".$_GET["id_rnc"]."' ";

$dados = $db->select($sql,'MYSQL',function($reg, $i){
	return $reg;
});

//Inicio Relatório
$pdf->SetFont('Arial','B',9);
$pdf->Cell(20,5,'Código:',0,0,'L',0);
$pdf->SetFont('Arial','',9);
$pdf->Cell(40,5,$dados[0]['cod_nao_conformidade'],0,0,'L',0);

$arrStatus = array('Pendente', 'Andamento', 'Encerrada');
$pdf->SetFont('Arial','B',9);
$pdf->Cell(13,5,'Status:',0,0,'L',0);
$pdf->SetFont('Arial','',9);
$pdf->Cell(27,5,$arrStatus[$dados[0]['status']],0,0,'L',0);

$pdf->SetFont('Arial','B',9);
$pdf->Cell(20,5,'Data:',0,0,'L',0);
$pdf->SetFont('Arial','',9);
$pdf->Cell(0,5,mysql_php($dados[0]['data_criacao']),0,1,'L',0);

$pdf->SetFont('Arial','B',9);
$pdf->Cell(20,5,'Originador:',0,0,'L',0);
$pdf->SetFont('Arial','',9);
$pdf->Cell(80,5,$dados[0]['funcionario'],0,0,'L',0);

$pdf->SetFont('Arial','B',9);
$pdf->Cell(20,5,'Setor:',0,0,'L',0);
$pdf->SetFont('Arial','',9);
$pdf->Cell(0,5,$dados[0]['setor'],0,1,'L',0);

$pdf->Ln();

$pdf->SetFont('Arial','B',9);
$pdf->Cell(20,5,'Origem:',0,0,'L',0);
$pdf->SetFont('Arial','',9);
$pdf->Cell(40,5,$dados[0]['tipo_origem'],0,0,'L',0);

$pdf->SetFont('Arial','B',9);
$pdf->Cell(32,5,'Tipo de documento:',0,0,'L',0);
$pdf->SetFont('Arial','',9);
$pdf->Cell(0,5,$dados[0]['tipo_documento'],0,1,'L',0);


$pdf->SetFont('Arial','B',9);
if (!empty($dados[0]['desc_outros_acidente']) || !empty($dados[0]['desc_outros_incidente']) || !empty($dados[0]['desc_outros_cliente']) || !empty($dados[0]['desc_outros_fornec']) || !empty($dados[0]['desc_outros']))
{
	$pdf->Cell(23,5,'Observações:',0,0,'L',0);
	$pdf->SetFont('Arial','',9);
	$texto = trim($dados[0]['desc_outros_acidente']).
			 trim($dados[0]['desc_outros_incidente']).
			 trim($dados[0]['desc_outros_cliente']).
			 trim($dados[0]['desc_outros_fornec']).
			 trim($dados[0]['desc_outros']);
	
	$pdf->MultiCell(0,5,$texto,0,'L',0);
}

$pdf->Cell(0,2,'','B',0,'L',0);
$pdf->Ln();

$pdf->SetFont('Arial','B',9);
$pdf->Cell(0,7,'Descrição da ocorrência:',0,1,'L',0);
$pdf->SetFont('Arial','',9);
$pdf->MultiCell(0,5,$dados[0]['desc_nao_conformidade'],0,'L',0);

$pdf->Cell(0,2,'','B',0,'L',0);
$pdf->Ln();

$pdf->SetFont('Arial','B',9);
$pdf->Cell(13,5,'Cliente:',0,0,'L',0);
$pdf->SetFont('Arial','',9);
$pdf->Cell(0,5,$dados[0]['empresa'],0,1,'L',0);

$pdf->SetFont('Arial','B',9);
$pdf->Cell(13,5,'Projeto:',0,0,'L',0);
$pdf->SetFont('Arial','',9);
$pdf->Cell(0,5,sprintf('%05d', $dados[0]['OS']).' - '.$dados[0]['descricao'],0,1,'L',0);

$pdf->SetFont('Arial','B',9);
$pdf->Cell(18,5,'Disciplina:',0,0,'L',0);
$pdf->SetFont('Arial','',9);
$pdf->Cell(0,5,$dados[0]['setor'],0,1,'L',0);

$pdf->Cell(0,2,'','B',0,'L',0);
$pdf->Ln();

$pdf->SetFont('Arial','B',9);
$pdf->Cell(0,7,'Ação imediata:',0,1,'L',0);
$pdf->SetFont('Arial','',9);
$pdf->MultiCell(0,5,$dados[0]['desc_acao_imediata'],0,'L',0);

$pdf->Cell(0,2,'','B',0,'L',0);
$pdf->Ln();

$pdf->SetFont('Arial','B',9);
$pdf->Cell(0,7,'Perdas:',0,1,'L',0);
$pdf->SetFont('Arial','',9);
$pdf->MultiCell(0,5,$dados[0]['desc_perdas'],0,'L',0);

$pdf->Cell(0,2,'','B',0,'L',0);
$pdf->Ln();

$pdf->SetFont('Arial','B',9);
$pdf->Cell(20,5,'Procedente:',0,0,'L',0);
$pdf->SetFont('Arial','',9);
$pdf->Cell(0,5,$dados[0]['procedente'] == 1 ? 'Sim' : 'Não',0,1,'L',0);

$pdf->Cell(0,2,'','B',0,'L',0);
$pdf->Ln();

$pdf->SetFont('Arial','B',9);
$pdf->Cell(0,7,'Análise de causa:',0,1,'L',0);
$pdf->SetFont('Arial','',9);
$pdf->MultiCell(0,5,$dados[0]['desc_analise_causa'],0,'L',0);

$pdf->Cell(0,2,'','B',0,'L',0);
$pdf->Ln(8);

//Plano de ação
$sql = "SELECT * FROM ".DATABASE.".planos_acoes_complementos, ".DATABASE.".funcionarios
WHERE planos_acoes_complementos.id_nao_conformidade = '".$_GET["id_rnc"]."'
AND planos_acoes_complementos.plano_acao_complemento_delete = 0
AND planos_acoes_complementos.id_funcionario_responsavel = funcionarios.id_funcionario
ORDER BY prazo, item_acao";

$dadosPA = $db->select($sql,'MYSQL', function($reg, $i){
	return $reg;
});

if ($pdf->getY() >= 255)
    $pdf->AddPage();
    
$pdf->SetFont('Arial','B',9);
$pdf->Cell(95,7,'Plano de Ação',1,0,'C',0);
$pdf->Cell(65,7,'Responsável',1,0,'C',0);
$pdf->Cell(20,7,'Prazo',1,1,'C',0);

$pdf->SetFont('Arial','',9);
foreach($dadosPA as $pa)
{
	$yInicial = $pdf->getY();
	$pdf->MultiCell(95,5,$pa['plano_acao'],1,'L',0);
	$yFinal = $pdf->getY();
	$alturaCelula = $yFinal-$yInicial;
	
	$pdf->setXY(110, $yFinal - $alturaCelula);
	$pdf->Cell(65,$alturaCelula,$pa['funcionario'],1,0,'L',0);
	$pdf->Cell(20,$alturaCelula,mysql_php($pa['prazo']),1,1,'L',0);
}

$pdf->setY($yFinal+$alturaCelula/4);
$pdf->Cell(0,2,'','B',0,'L',0);
$pdf->Ln();

if (!empty($dados[0]['desc_evidencia']))
{
	$pdf->SetFont('Arial','B',9);
	$pdf->Cell(0,7,'Evidências das ações:',0,1,'L',0);
	$pdf->SetFont('Arial','',9);
	$pdf->MultiCell(0,5,$dados[0]['desc_evidencia'],0,'L',0);
	
	$pdf->Cell(0,2,'','B',0,'L',0);
	$pdf->Ln();
}

$sql = "SELECT * FROM ".DATABASE.".nao_conformidades_anexos, ".DATABASE.".nao_conformidades
WHERE nao_conformidades.id_nao_conformidade = '".$_GET["id_rnc"]."'
AND nao_conformidades_anexos.reg_del = 0
AND nao_conformidades.nao_conformidade_delete = 0
AND nao_conformidades.id_nao_conformidade = nao_conformidades_anexos.id_nao_conformidade ";

$dadosPA2 = $db->select($sql,'MYSQL', function($reg, $i){
	return $reg;
});

if (count($dadosPA2) > 0)
{
	$pdf->SetFont('Arial','B',9);
	$pdf->Cell(0,7,'ANEXOS - Nome do Arquivo',1,1,'L',0);
	
	$pdf->SetFont('Arial','',9);
	foreach($dadosPA2 as $pa)
	{
		$pdf->MultiCell(0,6,maiusculas($pa['nome_arquivo']),1,'L',0);
	}
	
	$pdf->Cell(0,2,'','B',0,'L',0);
	$pdf->Ln(7);
}

if (!empty($dados[0]['desc_evidencia']))
{
	$pdf->SetFont('Arial','B',9);
	$pdf->Cell(0,7,'Observações:',0,1,'L',0);
	$pdf->SetFont('Arial','',9);
	$pdf->MultiCell(0,5,$dados[0]['desc_obs'],0,'L',0);
	
	$pdf->Cell(0,2,'','B',0,'L',0);
	$pdf->Ln();
}

if (!empty($dados[0]['desc_encerramento']))
{
	$pdf->SetFont('Arial','B',9);
	$pdf->Cell(0,7,'Verificação da eficiência:',0,1,'L',0);
	$pdf->SetFont('Arial','',9);
	$pdf->MultiCell(0,5,maiusculas($dados[0]['desc_encerramento']),0,'L',0);
	
	$pdf->Cell(0,2,'','B',0,'L',0);
	$pdf->Ln();
}

$pdf->Output();

?>