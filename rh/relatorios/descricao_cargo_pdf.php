<?php
/*
		Ficha do candidato	
		
		Criado por Carlos Eduardo
		
		local/Nome do arquivo:
		../rh/relatorios/descricao_cargo_pdf.php
		
		Versão 0 --> VERSÃO INICIAL - 29/03/2016
		Versão 1 --> Inclusão dos campos reg_del nas consultas - 27/11/2017 - Carlos Abreu	
*/
require_once(implode(DIRECTORY_SEPARATOR,array('..','..','config.inc.php')));

require_once(INCLUDE_DIR."include_pdf.inc.php");

require_once(INCLUDE_DIR."WriteTag.php");

class PDF extends PDF_WriteTag
{
	public $cargo = '';	
	
	function Header()
	{
		$this->Image(DIR_IMAGENS.'logo_pb.png',15,11,30);
		
		//Criando as bordas do cabecalho
		$this->setY(10);
		$this->Cell(40, 10, '',1,0,'',0);
		$this->Cell(150, 10, '',1,0,'C',0);
		//$this->Cell(40, 20, '',1,0,'',0);
		
		//Voltando ao início da página para fazer o texto do cabecalho
		$this->setY(10);
		
		$this->SetFont('Arial','B',9);
		$this->setX(50);
		$this->Cell(150, 5, $this->titulo,0,1,'C',0);
		
		$this->setX(50);
		$this->SetFont('Arial','',8);
		$this->Cell(150, 5, $this->cargo,'B',1,'C',0);
	}

	function Footer(){}
}

$db = new banco_dados;

$codCargo = $_GET['cod_cargo'];
$avaliacao = isset($_GET['avaliacao']) && !empty($_GET['avaliacao']) ? $_GET['avaliacao'] : 0;

$avaliacoes = array();

if ($avaliacao)
{
	$sql = "SELECT
	funcionario, data_inicio, DATE_ADD(data_inicio, INTERVAL 45 DAY) periodo_1, DATE_ADD(data_inicio, INTERVAL 90 DAY) periodo_2,
	comentarios, aprovado, periodo
FROM 
	".DATABASE.".funcionarios
	JOIN ".DATABASE.".periodo_experiencia ON id_avaliado = id_funcionario AND periodo_experiencia.reg_del = 0 
WHERE
	situacao = 'ATIVO'
	AND funcionarios.reg_del = 0  
	AND id_funcionario = ".$_GET['idFuncionario'];
	
	$db->select($sql, 'MYSQL', function($reg, $i) use(&$avaliacoes, &$pdf){
		$avaliacoes[$reg['periodo']] = $reg; 
	});
}

$sql = 
"SELECT 
	*
FROM 
	".DATABASE.".rh_funcoes f
	JOIN ".DATABASE.".rh_escolaridade e ON e.id_rh_escolaridade = f.id_rh_escolaridade AND e.reg_del = 0
WHERE
	f.reg_del = 0  
	AND id_funcao = ".$codCargo;

$dadosCargo = $db->select($sql, 'MYSQL', function($reg, $i){
	return $reg;
});

$pdf=new PDF();
$pdf->SetAutoPageBreak(true,30);
$pdf->SetMargins(10,10);
$pdf->SetLineWidth(0.1);

$pdf->SetStyle("b","arial","B",8,"0,0,0");
$pdf->SetStyle("p","arial","",8,"0,0,0",15);

$sql = "SELECT setor, abreviacao FROM ".DATABASE.".rh_cargos_x_setor ";
$sql .= "JOIN ".DATABASE.".setores ON id_setor = id_rh_setor AND setores.reg_del = 0 ";
$sql .= "WHERE rh_cargos_x_setor.reg_del = 0 ";
$sql .= "AND id_rh_cargo = ".$codCargo;

$setores = '';
$virgula = '';
$x = $pdf->getX();
$db->select($sql, 'MYSQL', function($reg, $i) use(&$setores, &$virgula){
	$setores .= $virgula.$reg['abreviacao'];
	$virgula = ', ';
});

//Seta o cabeçalho
if($avaliacao)
{
	$pdf->titulo="AVALIAÇÃO DE PERÍODO DE EXPERIÊNCIA";
}
else
{
	$pdf->titulo="DESCRIÇÃO DO CARGO";
}

$pdf->cargo = strtoupper($dadosCargo[0]['descricao']);
 
$pdf->emissao=date("d/m/Y");

$pdf->AliasNbPages();
$pdf->AddPage();

$pdf->ln(3);

if ($avaliacao)
{
	$nomeFunc = isset($avaliacoes[1]) ? $avaliacoes[1]['funcionario'] : $avaliacoes[2]['funcionario'];
	$dataInicio = isset($avaliacoes[1]) ? $avaliacoes[1]['data_inicio'] : $avaliacoes[2]['data_inicio'];
	
	$pdf->WriteTag(0,5,'<p align="left"><b>Nome: </b>'.$nomeFunc.'</p>', 0,'L',0,1);
	$pdf->ln();
	
	$pdf->WriteTag(0,5,'<p align="left"><b>Data de Contratação: </b>'.mysql_php($dataInicio).'</p>', 0,'L',0,1);
	$pdf->ln(3);
}

$pdf->SetFont('Arial','B',8);
$pdf->Cell(90,5,'DIRETORIA:','LRT',0,'L',0);
$pdf->Cell(80,5,'SETORES:','LRT',0,'L',0);
$pdf->Cell(20,5,'CBO:','LRT',1,'L',0);

$pdf->SetFont('Arial','',8);
$pdf->Cell(90,5,strtoupper($dadosCargo[0]['diretoria']),'LRB',0,'L',0);
$pdf->Cell(80,5,$setores,'RB',0,'L',0);
$pdf->Cell(20,5,$dadosCargo[0]['cbo_2002'],'RB',1,'L',0);

$pdf->ln(3);

if ($avaliacao)
{
	$pdf->SetFont('Arial','',8);
	$texto = "ESTE ACOMPANHAMENTO TEM COMO OBJETIVO LEVANTAR DADOS SOBRE O PERÍODO DE EXPERIÊNCIA / HABILIDADES E FACILITAR O DIRECIONAMENTO PARA A ".
			 "ATUAÇÃO DE RECURSOS HUMANOS. O SUPERIOR IMEDIATO DO COLABORADOR DEVE DEVOLVER AO RH O FORMULÁRIO PREENCHIDO 3 DIAS ÚTEIS ANTES DO VENCIMENTO DO SEGUNDO PERÍODO DE ".
			 "EXPERIÊNCIA. O NÃO PREENCHIMENTO OU A NÃO DEVOLUÇÃO ATÉ O PRAZO DETERMINADO PODE TRANSFORMAR, AUTOMATICAMENTE O CONTRATO DE EXPERIÊNCIA EM CONTRATO POR PRAZO ".
			 "INDETERMINDADO.";
	
	$pdf->MultiCell(190,5,$texto, 'LRBT','L',0);
	
	$pdf->ln(3);
}

$pdf->SetFont('Arial','B',8);
$pdf->Cell(190,5,'MISSÃO DO CARGO:','LRT',1,'L',0);
$pdf->SetFont('Arial','',8);
$pdf->MultiCell(190,5,$dadosCargo[0]['missao'],'LRB','L',0);

$pdf->ln(3);

$pdf->SetFont('Arial','B',8);
$pdf->Cell(190,5,'PRINCIPAIS ATRIBUIÇÕES / RESPONSABILIDADES:','LRT',1,'L',0);
$pdf->SetFont('Arial','',8);
$pdf->MultiCell(190,5,$dadosCargo[0]['principais_atividades'],'LRB','L',0);

$pdf->ln(3);
$pdf->SetFont('Arial','B',8);
$pdf->Cell(190,5,'REQUISITOS:',1,1,'C',0);
$pdf->Cell(95,5,'Escolaridade:','LR',0,'C',0);
$pdf->Cell(95,5,'Recomendável Experiência:','LR',1,'C',0);
$pdf->SetFont('Arial','',8);

$complFormacao = !empty($dadosCargo[0]['formacao']) ? ' ('.$dadosCargo[0]['formacao'].')' : '';
$pdf->Cell(95,5,$dadosCargo[0]['escolaridade'],'LR',0,'L',0);
$pdf->Cell(95,5,$dadosCargo[0]['experiencia'],'LR',1,'L',0);
$pdf->Cell(95,5,$complFormacao,'LRB',0,'L',0);
$pdf->Cell(95,5,'','LRB',1,'L',0);

$sql = 
"SELECT
	*
FROM
	".DATABASE.".rh_conhecimentos,
	".DATABASE.".rh_cargos_x_conhecimento 
WHERE 
	rh_cargos_x_conhecimento.id_rh_conhecimento = rh_conhecimentos.id_rh_conhecimento
AND rh_cargos_x_conhecimento.id_rh_cargo = '".$codCargo."'
AND rh_cargos_x_conhecimento.reg_del = 0 
ORDER BY
	rh_cargos_x_conhecimento_status";

$ant = 0;
$pdf->SetFont('Arial','B',8);

$x = $pdf->getX();

$pdf->Cell(95,5,'Conhecimentos desejáveis:','LR',0,'L',0);
$pdf->Cell(95,5,'Conhecimentos obrigatórios:','LRT',1,'L',0);
$y = $pdf->getY();
$yOld = $y; 
$resto = '';

$db->select($sql,'MYSQL',function($reg, $i) use(&$pdf, &$ant, &$y, &$x, &$resto){
	$pdf->SetFont('Arial','',8);
	if ($ant != $reg['rh_cargos_x_conhecimento_status'])
	{
		$pdf->setXY(105,$y);
		
		$x = $pdf->getX();
		$ant = $reg['rh_cargos_x_conhecimento_status'];
		$pdf->SetFont('Arial','',8);
		
		$y2 = $pdf->getY();
		$pdf->Cell(190,5,'','LR',0,'L',0);
		$pdf->setY($y2);
	}
	
	$y2 = $pdf->getY();
	$pdf->Cell(190,5,'','LR',0,'L',0);
	$pdf->setY($y2);
	
	$pdf->setX($x);
	$pdf->Cell(95,5,$reg['conhecimento'],'LR',1,'L',0);
	
	$resto = $i%2;
});

//Verifico se o número de itens é par ou impar para criar uma célula vazia e completar a tabela
if ($resto == 0)
{
	$pdf->Cell(95,5,'','LR',0,'L',0);
	$pdf->setX(105);
	$pdf->Cell(95,5,'','LR',1,'L',0);
}

$pdf->Cell(190,1,'','T',1,'C',0);

$pdf->ln(2);

$pdf->SetFont('Arial','B',8);
$pdf->Cell(190,5,'CONDIÇÕES DE TRABALHO',1,1,'C',0);

$pdf->Cell(95,5,'Riscos:','LRT',0,'C',0);
$pdf->Cell(95,5,'Ambiente de trabalho:','LRT',1,'C',0);

$pdf->SetFont('Arial','',8);
$pdf->Cell(95,5,'Conforme definido no IOS','LRB',0,'C',0);
$pdf->Cell(95,5,'Conforme definido no PPRA ','LRB',1,'C',0);

$pdf->ln(3);
$pdf->SetFont('Arial','B',8);
$pdf->Cell(190,5,'COMPETÊNCIAS TÉCNICAS OU DE PROCESSOS',1,1,'C',0);
$pdf->SetFont('Arial','',8);
$pdf->MultiCell(190,5,$dadosCargo[0]['competencias_tecnicas'],1,'L',0);

$pdf->ln(3);

$pdf->SetFont('Arial','B',8);
$pdf->Cell(190,5,'COMPETÊNCIAS TÉCNICAS INDIVIDUAIS',1,1,'C',0);
$pdf->SetFont('Arial','',8);
$pdf->MultiCell(190,5,$dadosCargo[0]['competencias_individuais'],1,'L',0);

if ($avaliacao)
{
	$pdf->ln(3);
	
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(190,5,'PERÍODO DE EXPERIÊNCIA E HABILIDADES',1,1,'C',0);
	
	$y = $pdf->getY();
	
	$dataPeriodo1 = isset($avaliacoes[1]) ?  mysql_php($avaliacoes[1]['periodo_1']) : mysql_php($avaliacoes[2]['periodo_1']);
	$dataPeriodo2 = isset($avaliacoes[2]) ?  mysql_php($avaliacoes[2]['periodo_2']) : mysql_php($avaliacoes[1]['periodo_2']); 
	
	$arrTemp = array(0 => 'Reprovado: ', 1 => 'Aprovado: ');
	
	$avaliacoes[2]['comentarios'] = empty($avaliacoes[2]['comentarios']) && $avaliacoes[2]['aprovado'] == 1 ? 'N�O PREENCHIDO' : $avaliacoes[2]['comentarios'];
	$avaliacoes[1]['comentarios'] = empty($avaliacoes[1]['comentarios']) && $avaliacoes[1]['aprovado'] == 1 ? 'N�O PREENCHIDO' : $avaliacoes[1]['comentarios'];
	
	$pdf->padding(5);
	$pdf->WriteTag(190, 3,'<p align="left"><b>TÉRMINO DO 1° PERÍODO:</b> '.$dataPeriodo1.'</p><p align="left"><b>'.$arrTemp[$avaliacoes[1]['aprovado']]."</b><br />".$avaliacoes[1]['comentarios'].'</p>', 1,'L',0,1);
	$pdf->Ln();
	$pdf->WriteTag(190, 3,'<p align="left"><b>TÉRMINO DO 2º PERÍODO:</b> '.$dataPeriodo2.'</p><p align="left"><b>'.$arrTemp[$avaliacoes[2]['aprovado']]."</b><br />".$avaliacoes[2]['comentarios'].'</p>', 1,'L',0,1);
	
	$pdf->ln(4);
	
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(70,5,'RESPONSÁVEL DA ÁREA','LRT',0,'C',0);
	$pdf->Cell(70,5,'ÁREA DE RECURSOS HUMANOS','LRT',0,'C',0);
	$pdf->Cell(50,5,'DATA','LRT',1,'C',0);
	
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(70,10,'','LRB',0,'C',0);
	$pdf->Cell(70,10,'','LRB',0,'C',0);
	$pdf->Cell(50,10,'','LRB',0,'C',0);
}

$pdf->Output();

?>