<?php
/*
		Lista de Evidência	
		
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
		$this->Cell(190,4,'DATA: '.$this->emissao(),0,1,'R',0);
		
		$this->SetLineWidth(0.3);
		$this->Ln(5);
		$this->SetFont('Arial','B',12);
		$this->Cell(190,4,$this->titulo,0,0,'R',0);
		$this->ln();
		
		$this->SetFont('Arial','B',8);
		$this->MultiCell(190,5,$this->titulo2,0,'R',0);
		$this->SetXY(25,28);
	}

	function Footer(){
//		$this->SetY(-10);
//	    $this->SetFont('Arial','I',8);
//	    $this->Cell(200,10,'Pág. '.$this->PageNo().'/{nb}',0,0,'R');
	}
}

$db = new banco_dados();

//Instanciation of inherited class
$pdf=new PDF('p','mm',A4);
$pdf->SetAutoPageBreak(false,30);
$pdf->SetMargins(10,10,10,10);
$pdf->SetLineWidth(0.5);

//Seta o cabeçalho
$pdf->titulo="RELATÓRIO DE ITENS POR DOCUMENTO";
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
			   FROM materiais_old.produto WHERE produto.reg_del = 0 
		   ) produto
		   ON componentecodigo = cod_barras
		  JOIN(
			SELECT id_grupo, id_sub_grupo, codigo_inteligente, descricao, cod_barras codBarrasComponente, id_familia FROM materiais_old.componentes WHERE componentes.reg_del = 0
		  ) componentes
		  ON codBarrasComponente = componentecodigo
		  LEFT JOIN (
			SELECT id_familia idFamilia, descricao descFamilia FROM materiais_old.familia WHERE familia.reg_del = 0
		  ) familia ON idFamilia = id_familia
          JOIN(
			SELECT a.id_ged_arquivo idGedArquivo, b.os desc_os, b.atividade, a.descricao desc_arquivo FROM ".DATABASE.".ged_arquivos a
			JOIN ".DATABASE.".ged_versoes b ON b.id_ged_versao = a.id_ged_versao AND a.reg_del = 0 AND b.reg_del = 0
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
		ORDER BY componentecodigo";

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
	if (!isset($cabecalhos[$reg['componentecodigo']]))
	{
		if ($totalItem > 0)
		{
			//Linha totalizadora
			$pdf->setX(20);
			$pdf->Cell(140,5, '', '', '', 0);
			$pdf->SetFont('Arial','B',8);
			$pdf->Cell(25, 5, $totalItem, '', 0);
			$pdf->ln();
		}
		
		if ($pdf->getY() >= 270)
		{
			$pdf->AddPage();
		}
				
		$pdf->ln();

		$pdf->SetFont('Arial','B',8);
		$pdf->Cell(10, 5, 'ITEM', 'B', 0);
		$pdf->Cell(180, 5, 'DESCRIÇÃO', 'B', 0);
		$pdf->ln();
		
		$pdf->Cell(15, 5, $item, '', 0);
		
		$pdf->setX(20);
		$pdf->MultiCell(175,5,$reg['descFamilia'].', '.$reg['descricao'],'','L',0);
		$cabecalhos[$reg['componentecodigo']] = 1;
		$pdf->ln(2);
		
		$pdf->setX(20);
		$pdf->Cell(140,5,'DOCUMENTO', '',0);
		$pdf->Cell(25,5,'QUANTIDADE', '',0);
		$pdf->Cell(25,5,'UNIDADE', '',0);
		$pdf->ln();
		$item++;
		$totalItem = 0;
	}
	
	$pdf->setX(20);
	$pdf->SetFont('Arial','',8);
	$pdf->Cell(140,5,$reg['desc_arquivo'].' - '.$reg['atividade'], '',0);
	$pdf->Cell(25,5,$reg['qtd'], '',0);
	$pdf->Cell(25,5,$reg['unidade'], '',0);
	$totalItem += $reg['qtd'];
	$pdf->ln();
});

$pdf->Output('LISTA_MATERIAIS_'.date('dmYhis').'.pdf', 'D');