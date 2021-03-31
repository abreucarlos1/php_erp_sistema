<?php
/*
		Lista de Evidências	
		
		Criado por Carlos Eduardo
		
		data de criação: 14/12/2016
		
		Versão 0 --> VERSÃO INICIAL
		Versão 1 --> Inclusão dos campos reg_del nas consultas - 01/12/2017 - Carlos Abreu		
*/
require_once(implode(DIRECTORY_SEPARATOR,array('..','..','config.inc.php')));

require_once(INCLUDE_DIR."include_pdf.inc.php");

class PDF extends FPDF
{
	public $cargo = '';
	public $salario = '';
	function Header()
	{
		$this->Image(DIR_IMAGENS.'logo_pb.png',10,10,40);
		
		$this->SetFont('Arial','',6);
		$this->Cell(0,4,'DATA: '.$this->emissao(),0,1,'R',0);
		
		$this->SetLineWidth(0.3);
		$this->Ln(5);
		$this->SetFont('Arial','B',12);
		$this->Cell(0,4,$this->titulo,0,0,'R',0);
		$this->ln();
		
		$this->SetFont('Arial','B',8);
		$this->MultiCell(0,5,$this->titulo2,0,'R',0);
		$this->SetXY(25,28);
	}

	function Footer(){
		$this->SetY(-10);
	    $this->SetFont('Arial','I',8);
	    $this->Cell(0,10,'Pág. '.$this->PageNo().'/{nb}',0,0,'R');
	}
}

$db = new banco_dados();

//Instanciation of inherited class
$pdf=new PDF('L','mm',A4);
$pdf->SetAutoPageBreak(false,30);
$pdf->SetMargins(10,10,10,10);
$pdf->SetLineWidth(0.5);

//Seta o cabeçalho
$pdf->titulo="RELATÓRIO DE EVIDÊNCIA";
$pdf->emissao=date("d/m/Y");

//Consulta
$clausulaIdGedArquivo = isset($_GET['id_ged_arquivo']) ? "AND id_lista_materiais_cabecalho IN(SELECT DISTINCT id_lista_materiais FROM materiais_old.lista_materiais WHERE lista_materiais.reg_del = 0 AND lista_materiais.id_ged_arquivo = {$_GET['id_ged_arquivo']})" : '';

$clausulaIdOs = '';
if (isset($_GET['id_os']) && !empty($_GET['id_os']))
{
	$idOs = explode('/', $_GET['id_os']);
	$clausulaIdOs = "AND id_os = ".$idOs[0];
}

$clausulaIdDisciplina = '';
if (isset($_GET['id_disciplina']) && !empty($_GET['id_disciplina']))
{
	$clausulaIdDisciplina = "AND id_disciplina = {$_GET['id_disciplina']}";
}

//Buscando produtos da lista de materiais encontrada
$sql = "SELECT
		  id_ged_arquivo, id_os, id_disciplina, id_cabecalho, id_lista_materiais_versoes, lista_materiais.atual, cod_lista_materiais,
          qtd, codProduto, componentecodigo, desc_long_por, unidade, descricao, desc_os, setor, desc_os, atividade, desc_arquivo, descFamilia
		FROM
		   materiais_old.lista_materiais
		   JOIN(
			   SELECT
					id_lista_materiais_versoes id_versao, id_lista_materiais cod_lista_materiais, qtd, unidade, margem, revisao_documento, data_versao
				FROM
					materiais_old.lista_materiais_versoes
				WHERE
					lista_materiais_versoes.reg_del = 0 
					
		   ) versoes
		   ON cod_lista_materiais = id_lista_materiais
           AND id_versao = id_lista_materiais_versoes
           AND atual = 1
           AND id_ged_arquivo > 0
		   JOIN(
			   SELECT
					id_lista_materiais_cabecalho id_cabecalho
			   FROM
					materiais_old.lista_materiais_cabecalho
			   WHERE
					lista_materiais_cabecalho.reg_del = 0
		   )cabecalho
		   ON id_cabecalho = id_lista_materiais_cabecalho 
		   JOIN(
			   SELECT
				  atual, id_produto codProduto, cod_barras componentecodigo, desc_res_ing, desc_res_esp, desc_long_por, desc_long_ing, desc_long_esp, unidade1, unidade2, peso1, peso2
			   FROM materiais_old.produto
		   ) produto
		   ON componentecodigo = cod_barras
		  JOIN(
			SELECT id_grupo, id_sub_grupo, codigo_inteligente, descricao, cod_barras codBarrasComponente, id_familia FROM materiais_old.componentes WHERE componentes.reg_del = 0
		  ) componentes
		  ON codBarrasComponente = componentecodigo
		  LEFT JOIN (
				SELECT id_familia idFamilia, descricao descFamilia FROM materiais_old.familia WHERE familia.reg_del = 0
			) familia
			ON idFamilia = id_familia
          JOIN(
			SELECT a.id_ged_arquivo idGedArquivo, b.os desc_os, b.atividade, a.descricao desc_arquivo FROM ".DATABASE.".ged_arquivos a
			JOIN ".DATABASE.".ged_versoes b ON b.id_ged_versao = a.id_ged_versao AND b.reg_del = 0 AND a.reg_del = 0
          ) arquivo
          ON idGedArquivo = id_ged_arquivo
          JOIN(
          	SELECT setor, id_setor FROM ".DATABASE.".setores WHERE setores.reg_del = 0
          ) disciplina
          ON id_setor = id_disciplina
		WHERE
		  lista_materiais.reg_del = 0
		  AND produto.atual = 1
		  ".$clausulaIdOs." ".$clausulaIdDisciplina."
		ORDER BY id_disciplina";

//CABEÇALHO DO ITEM (NÃO DA PÁGINA)
$sql2 = $sql.' LIMIT 0,1';
$db->select($sql2, 'MYSQL', function ($reg, $i) use (&$pdf){
	$pdf->titulo2 = $reg['desc_os'].' - '.$reg['setor'];
});

$pdf->AliasNbPages();
$pdf->AddPage();

$pdf->SetLineWidth(0.1);

$cabecalhos = array();
$item = 1;
$totalItem = 0;
$db->select($sql, 'MYSQL', function ($reg, $i) use (&$pdf, &$cabecalhos, &$item, &$totalItem){
	if (!isset($cabecalhos[$reg['id_ged_arquivo']]))
	{
		if ($totalItem > 0)
		{
			//Linha totalizadora
			$pdf->SetFont('Arial','B',8);
			$pdf->Cell(230,5, '', '', '', 0);
			$pdf->Cell(25, 5, $totalItem, '', 0,'C');
			$pdf->AddPage();
		}
		

		$pdf->SetFont('Arial','B',8);
		$pdf->MultiCell(0,5,$reg['desc_arquivo'].' - '.$reg['atividade'],'T','L',0);
		$cabecalhos[$reg['id_ged_arquivo']] = 1;
		$pdf->ln(2);
		
		$pdf->Cell(230,5,'DESCRIÇÃO', '',0);
		$pdf->Cell(25,5,'QUANTIDADE', '',0,'C');
		$pdf->Cell(25,5,'UNIDADE', '',0,'C');
		$pdf->ln();
		
		$totalItem = 0;
	}
	
	if ($pdf->getY() >= 270)
	{
		$pdf->AddPage();
	}
			
	$pdf->ln();
	
	$pdf->SetFont('Arial','',8);
	$x = $pdf->getX();
	$y = $pdf->getY();
	
	$pdf->MultiCell(230,5,trim($reg["descFamilia"]).', '.trim($reg["descricao"]), '','L',0);
	
	$x2 = $pdf->getX();
	$y2 = $pdf->getY();
	
	$pdf->setXY($x+230, $y);
	$pdf->Cell(25,5,$reg['qtd'], '',0,'C');
	$pdf->Cell(25,5,$reg['unidade'], '',0,'C');
	$pdf->setXY($x2, $y2);
	$pdf->ln();
	
	$totalItem += $reg['qtd'];
});

$pdf->Output('LISTA_MATERIAIS_'.date('dmYhis').'.pdf', 'D');