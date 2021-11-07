<?php
/*
		Relatório de Cotação de materiais
		
		Criado por Carlos 
		
		local/Nome do arquivo:		
		../materiais/relatorios/lista_materiais_cotacao.php
		
		Versão 0 --> VERSÃO INICIAL - 02/03/2016
		Versão 1 --> Inclusão dos campos reg_del nas consultas - 01/12/2017 - Carlos Abreu	
*/

ini_set('max_execution_time', 0); // No time limit
ini_set('memory_limit', '100M');
ini_set('post_max_size', '20M');
ini_set('upload_max_filesize', '20M');

require_once(implode(DIRECTORY_SEPARATOR,array('..','..','config.inc.php')));

require_once(INCLUDE_DIR."PHPExcel/Classes/PHPExcel.php");

$db = new banco_dados();

$objPHPExcel = PHPExcel_IOFactory::load("../modelos_excel/planilha_cotacao_materiais.xlsx");

$locale = 'pt_br';

$validlocale = PHPExcel_Settings::setlocale($locale);

if (!$validlocale) 
{
	echo 'Unable to set locale to '.$locale." - reverting to en_us<br />\n";
}

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

$clausulaIdArquivos = '';
if (isset($_GET['selecionados']) && !empty($_GET['selecionados']))
{
	$clausulaIdArquivos = "AND lm.id_lista_materiais_cabecalho IN(".$_GET['selecionados'].")";
	$clausulaIdCabecalhoLm = "";
	$atualSelecionados = "";
}
else
{
	$clausulaIdArquivos = "AND (id_ged_arquivo = 0 OR id_ged_arquivo is null)";
	$clausulaIdCabecalhoLm = "AND id_lista_materiais_cabecalho = ".$_GET['id_cabecalho'];
	$atualSelecionados = "AND atual = 1";
}

$objPHPExcel->setActiveSheetIndex(1);

if (isset($_GET['id_cabecalho']) && !empty($_GET['id_cabecalho']))
{
	$clausulaIdCabecalho = 'AND id_cabecalho = '.$_GET['id_cabecalho'];
}

$clausulaIdGedArquivo = isset($_GET['id_ged_arquivo']) ? "AND id_lista_materiais_cabecalho IN(SELECT DISTINCT id_lista_materiais FROM ".DATABASE.".lista_materiais WHERE reg_del = 0 AND id_ged_arquivo = {$_GET['id_ged_arquivo']})" : '';

$clausulaIdOs = '';
if (isset($_GET['id_os']) && !empty($_GET['id_os']))
{
	$idOs = explode('/', $_GET['id_os']);
	$clausulaIdOs = "lm.id_os = ".$idOs[0];
}

$clausulaIdDisciplina = '';
if (isset($_GET['id_disciplina']) && !empty($_GET['id_disciplina']))
{
	$clausulaIdDisciplina = "AND lm.id_disciplina = {$_GET['id_disciplina']}";
}

//Buscando produtos da lista de materiais encontrada
$sql = "SELECT 
	MAX(id_ged_arquivo) id_ged_arquivo, MAX(id_os) id_os, MAX(id_disciplina) id_disciplina, MAX(id_lista_materiais_cabecalho) id_lista_materiais_cabecalho,
    MAX(id_versao) id_versao, MAX(atual) atual, MAX(id_lista_materiais) id_lista_materiais, ROUND(SUM(qtd), 3) qtd, MAX(codProduto) codProduto,
    componentecodigo, desc_long_por, unidade, descricao, descFamilia, descLongaFamilia, desc_os, OS, empresa, logotipo, idFamilia, setor,
    revCabecalho, versao_documento
FROM (
SELECT
  id_ged_arquivo, lm.id_os, id_disciplina, lm.id_lista_materiais_cabecalho, lm.id_lista_materiais_versoes id_versao, lm.atual, lm.id_lista_materiais,
  qtd, lm.id_produto codProduto, lm.cod_barras componentecodigo, p.desc_long_por, p.unidade1 unidade, c.descricao, f.descricao descFamilia, f.descricao_longa descLongaFamilia,
  OS.descricao desc_os, os.os, empresa, logotipo, c.id_familia idFamilia, setor, lc.versao_documento revCabecalho, lm.versao_documento
	FROM
	   ".DATABASE.".lista_materiais lm
	   JOIN ".DATABASE.".lista_materiais_versoes lv ON lv.id_lista_materiais = lm.id_lista_materiais AND lv.id_lista_materiais_versoes = lm.id_lista_materiais_versoes ".$atualSelecionados." AND lm.reg_del = 0 ".$clausulaIdArquivos."
	   JOIN ".DATABASE.".lista_materiais_cabecalho lc ON lc.id_lista_materiais_cabecalho = lm.id_lista_materiais_cabecalho AND lc.reg_del = 0 AND lc.versao_documento = lm.versao_documento
	   JOIN ".DATABASE.".produto p ON p.cod_barras = lm.cod_barras AND p.atual = 1 AND p.reg_del = 0
	   JOIN ".DATABASE.".componentes c ON c.cod_barras = lm.cod_barras AND c.reg_del = 0
	   JOIN ".DATABASE.".familia f ON f.id_familia = c.id_familia AND f.reg_del = 0
	   JOIN ".DATABASE.".OS ON OS.id_os = lm.id_os AND OS.reg_del = 0  
	   JOIN ".DATABASE.".empresas e ON e.id_empresa = OS.id_empresa AND e.reg_del = 0
	   JOIN ".DATABASE.".setores s ON s.id_setor = lm.id_disciplina AND s.reg_del = 0
	
	WHERE
		".$clausulaIdOs." ".$clausulaIdDisciplina."

		GROUP BY componentecodigo, id_lista_materiais
) consulta
GROUP BY componentecodigo
ORDER BY idFamilia, componentecodigo";

$linha=13;

$styleArray = array(
			'borders' => array(
				'allborders' => array(
		        	'style' => PHPExcel_Style_Border::BORDER_THIN
		    	)
			)
		);
		
		

$db->select($sql, 'MYSQL',
	function ($reg, $i) use (&$objPHPExcel, &$linha, &$dadosCabecalho, &$plans, &$arrayRevisoesAnteriores, &$colRevs, &$styleArray)
	{
		$linhaCopia = $linha + 1;
		$objPHPExcel->getActiveSheet()->insertNewRowBefore($linhaCopia, 1);
		
		$objPHPExcel->getActiveSheet()->mergeCellsByColumnAndRow(0,$linhaCopia,1,$linhaCopia);
		$objPHPExcel->getActiveSheet()->mergeCellsByColumnAndRow(2,$linhaCopia,17,$linhaCopia);
		$objPHPExcel->getActiveSheet()->mergeCellsByColumnAndRow(18,$linhaCopia,19,$linhaCopia);
		$objPHPExcel->getActiveSheet()->mergeCellsByColumnAndRow(20,$linhaCopia,23,$linhaCopia);
		$objPHPExcel->getActiveSheet()->mergeCellsByColumnAndRow(24,$linhaCopia,27,$linhaCopia);
		$objPHPExcel->getActiveSheet()->mergeCellsByColumnAndRow(28,$linhaCopia,31,$linhaCopia);
		$objPHPExcel->getActiveSheet()->mergeCellsByColumnAndRow(32,$linhaCopia,35,$linhaCopia);
		$objPHPExcel->getActiveSheet()->mergeCellsByColumnAndRow(36,$linhaCopia,39,$linhaCopia);
		$objPHPExcel->getActiveSheet()->mergeCellsByColumnAndRow(40,$linhaCopia,43,$linhaCopia);
		
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $linha, iconv('ISO-8859-1', 'UTF-8',($i+1)));
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $linha, iconv('ISO-8859-1', 'UTF-8',$reg['descLongaFamilia'].', '.$reg['descricao']));
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(18, $linha, iconv('ISO-8859-1', 'UTF-8',$reg['unidade']));
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(20, $linha, iconv('ISO-8859-1', 'UTF-8',round($reg['qtd'], 2)));
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(45, $linha, iconv('ISO-8859-1', 'UTF-8',$reg['componentecodigo']));
		
		$linha++;
		
		$objPHPExcel->getActiveSheet()->getStyle('C'.$linha.':'.'R'.$linha)->applyFromArray($styleArray);
	}
);

$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(24, ($linha+1), iconv('ISO-8859-1', 'UTF-8','=SUM(Y13:Y'.$linha.')'));
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(28, ($linha+1), iconv('ISO-8859-1', 'UTF-8','=SUM(AC13:AC'.$linha.')'));
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(32, ($linha+1), iconv('ISO-8859-1', 'UTF-8','=SUM(AG13:AG'.$linha.')'));
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(36, ($linha+1), iconv('ISO-8859-1', 'UTF-8','=SUM(AK13:AK'.$linha.')'));
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(40, ($linha+1), iconv('ISO-8859-1', 'UTF-8','=SUM(AO13:AO'.$linha.')'));

foreach($objPHPExcel->getActiveSheet()->getRowDimensions() as $rd){
	if ($rd->getRowIndex() >= 13 && $rd->getRowIndex() < $linha)
    	$rd->setRowHeight(80);
}

if ($db->numero_registros == 0)
{
	exit('<script>alert("ATENÇÃO: Selecione uma ou mais listas para gerar esta planilha.");window.close();</script>');
}

// Redirect output to a clients web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment;filename=lista_materiais_".date('Y_m_d_H_i_s').'.xlsx');
header('Cache-Control: max-age=0');

$objWriter->save('php://output');