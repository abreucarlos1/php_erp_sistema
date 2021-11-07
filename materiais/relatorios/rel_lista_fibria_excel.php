<?php
/*
		Relatório lista fibria excel
		
		Criado por Carlos 
		
		local/Nome do arquivo:		
		../materiais/relatorios/rel_lista_fibria_excel.php
		
		Versão 0 --> VERSÃO INICIAL - 02/03/2016
		Versão 1 --> Inclusão dos campos reg_del nas consultas - 01/12/2017 - Carlos Abreu	
*/

error_reporting(E_ERROR);

ini_set('max_execution_time', 0); // No time limit
ini_set('memory_limit', '510M');
ini_set('post_max_size', '20M');
ini_set('upload_max_filesize', '20M');

require_once(implode(DIRECTORY_SEPARATOR,array('..','..','config.inc.php')));

require_once(INCLUDE_DIR."PHPExcel/Classes/PHPExcel.php");

$db = new banco_dados();

$objPHPExcel = PHPExcel_IOFactory::load("../modelos_excel/lista_materiais_fibria.xls");

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

//Obtendo o cabecalho antigo
$sql = "SELECT
	DISTINCT revLm, id_produto, qtd, margem, unidade, versao_documento
FROM
	".DATABASE.".lista_materiais_cabecalho lc
    JOIN(
		SELECT 
			id_lista_materiais_cabecalho idCabecalhoLm, id_lista_materiais_versoes, versao_documento revLm, id_produto, qtd, margem, unidade
		FROM 
			".DATABASE.".lista_materiais
            JOIN(
				SELECT id_lista_materiais_versoes idLv, unidade, qtd, margem FROM ".DATABASE.".lista_materiais_versoes lv WHERE lv.reg_del = 0 AND lv.fechado = 1
            )lv
            ON idLv = id_lista_materiais_versoes
		WHERE 
			lista_materiais.reg_del = 0
    ) lm
	ON idCabecalhoLm = id_lista_materiais_cabecalho
WHERE
	lc.reg_del = 0	
	AND revLm < versao_documento
	".$clausulaIdCabecalhoLm."

ORDER BY 
	revLm, id_produto";

$arrayRevisoesAnteriores = array();
$db->select($sql, 'MYSQL', function($reg, $i) use(&$arrayRevisoesAnteriores){
    $arrayRevisoesAnteriores[$reg['id_produto']][$reg['revLm']] = $reg['qtd']+$reg['margem'];
});


$objPHPExcel->setActiveSheetIndex(0);

$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, 1, iconv('ISO-8859-1', 'UTF-8',date('d/m/Y')));

//Criando a planilha extra
$A = $objPHPExcel->getActiveSheet();
$B = clone $A;

if (isset($_GET['id_cabecalho']) && !empty($_GET['id_cabecalho']))
{
	$clausulaIdCabecalho = 'AND id_cabecalho = '.$_GET['id_cabecalho'];
}

$clausulaIdGedArquivo = isset($_GET['id_ged_arquivo']) ? "AND id_lista_materiais_cabecalho IN(SELECT DISTINCT id_lista_materiais FROM ".DATABASE.".lista_materiais WHERE lista_materiais.reg_del = 0 AND lista_materiais.id_ged_arquivo = {$_GET['id_ged_arquivo']})" : '';

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

$plans = array();
$colRevs = array(0=>3,1=>4,2=>5,3=>6,4=>7,5=>3,6=>4,7=>5,8=>6,9=>7);

//Buscando produtos da lista de materiais encontrada
$sql = "SELECT 
	MAX(id_ged_arquivo) id_ged_arquivo, MAX(id_os) id_os, MAX(id_disciplina) id_disciplina, MAX(id_lista_materiais_cabecalho) id_lista_materiais_cabecalho,
    MAX(id_versao) id_versao, MAX(atual) atual, MAX(id_lista_materiais) id_lista_materiais, ROUND(SUM(qtd), 3) qtd, MAX(codProduto) codProduto,
    componentecodigo, desc_long_por, unidade, descricao, descFamilia, descLongaFamilia, desc_os, OS, empresa, logotipo, idFamilia, setor, specs,
    revCabecalho, versao_documento
FROM (
SELECT
  id_ged_arquivo, lm.id_os, id_disciplina, lm.id_lista_materiais_cabecalho, lm.id_lista_materiais_versoes id_versao, lm.atual, lm.id_lista_materiais,
  qtd, lm.id_produto codProduto, lm.cod_barras componentecodigo, p.desc_long_por, p.unidade1 unidade, c.descricao, f.descricao descFamilia, f.descricao_longa descLongaFamilia,
  OS.descricao desc_os, os.os, empresa, logotipo, c.id_familia idFamilia, setor, GROUP_CONCAT(DISTINCT ec_descricao) specs, lc.versao_documento revCabecalho, lm.versao_documento
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
	   JOIN ".DATABASE.".espec_cabecalho ec ON ec.ec_cliente = OS.id_empresa AND ec.ec_os = OS.id_os AND ec.reg_del = 0
	   JOIN ".DATABASE.".espec_lista el ON el.el_ec_id = ec.ec_id AND el.el_cod_barras = lm.cod_barras AND el.reg_del = 0
	
	WHERE
		".$clausulaIdOs." ".$clausulaIdDisciplina."

		GROUP BY componentecodigo, id_lista_materiais
) consulta
GROUP BY componentecodigo
ORDER BY descFamilia, componentecodigo";

$linha=15;
$dadosCabecalho = array();
$plans = array();
$colRevs = array(0=>3,1=>4,2=>5,3=>6,4=>7,5=>3,6=>4,7=>5,8=>6,9=>7);

$db->select($sql, 'MYSQL',
	function ($reg, $i) use (&$objPHPExcel, &$linha, &$dadosCabecalho, &$plans, &$arrayRevisoesAnteriores, &$colRevs, &$B)
	{
		if (!isset($plans[$reg['idFamilia']]) || count($plans)==0)
		{
			$plans[$reg['idFamilia']] = count($plans);
			$dadosCabecalho['descricao_familia'][$reg['idFamilia']] = $reg['descLongaFamilia'];
			
			$titulo = explode(' ', $reg['descFamilia']);
			$dadosCabecalho['titulos_planilhas'][$reg['idFamilia']] = str_replace(array(',','.'), '', tiraacentos($titulo[0])); 
			
			$dadosCabecalho['projeto'] = $reg['OS'].' - '.$reg['desc_os'];
			$dadosCabecalho['versao_documento'] = $reg['revCabecalho'];
			$dadosCabecalho['empresa'] = $reg['empresa'];
			$dadosCabecalho['logotipo'] = $reg['logotipo'];
			
			//Clona a planilha atual ainda vazia
			$newPlan = clone $B;
			
			$nomePlanilha = 'Plan';
			
			$newPlan->setTitle($nomePlanilha.' ('.(count($plans)+1).')');
			$objPHPExcel->addSheet($newPlan,count($plans));
			
			$objPHPExcel->setActiveSheetIndex($plans[$reg['idFamilia']]);
			$objPHPExcel->getActiveSheet()->setTitle($nomePlanilha.' ('.count($plans).')');
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, 8,  iconv('ISO-8859-1', 'UTF-8',$reg['descLongaFamilia']));
			$linha = 15;
			
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, 1, iconv('ISO-8859-1', 'UTF-8',$dadosCabecalho['projeto']));
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, 5, iconv('ISO-8859-1', 'UTF-8',$dadosCabecalho['versao_documento']));			
		}
		
		if (!isset($dadosCabecalho['idFamilia'][$reg['idFamilia']]))
		{
			$dadosCabecalho['disciplina'] = $reg['setor'];	
		}

		$dadosCabecalho['specs'][$reg['idFamilia']] = $reg['specs'];
		
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $linha, iconv('ISO-8859-1', 'UTF-8',($i+1)));
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $linha, iconv('ISO-8859-1', 'UTF-8',$reg['descricao']));
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $linha, iconv('ISO-8859-1', 'UTF-8',$reg['unidade']));
		
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($colRevs[$reg['revCabecalho']], $linha, iconv('ISO-8859-1', 'UTF-8',$reg['qtd']));
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($colRevs[$reg['revCabecalho']], 13, iconv('ISO-8859-1', 'UTF-8','REV. '.$reg['revCabecalho']));
				
		foreach($arrayRevisoesAnteriores[$reg['codProduto']] as $versao_documento => $qtdRevisao)
		{
			if ($colRevs[$reg['revCabecalho']] != $colRevs[$versao_documento])
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($colRevs[$versao_documento], $linha, iconv('ISO-8859-1', 'UTF-8',$qtdRevisao));
				if ($versao_documento > 4)
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($colRevs[$versao_documento], 13, iconv('ISO-8859-1', 'UTF-8','REV. '.$versao_documento));
				
			$nColuna ++;
		}

		$objPHPExcel->getActiveSheet()->getStyle('A'.$linha.':'.'H'.$linha)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
		
		$linha++;
	}
);

if ($db->numero_registros == 0)
{
	exit('<script>alert("ATENÇÃO: A lista não está pronta, por favor, realize a emissão da lista da OS.");window.close();</script>');
}

//Renomeando as planilhas para as specs
foreach($plans as $k => $v)
{
	$objPHPExcel->setActiveSheetIndex($plans[$k]);
	$objPHPExcel->getActiveSheet()->setTitle($dadosCabecalho['titulos_planilhas'][$k]);
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, 7, iconv('ISO-8859-1', 'UTF-8',$dadosCabecalho['specs'][$k]));
}

$numPlans = array_pop($plans);
$objPHPExcel->removeSheetByIndex($numPlans+1);
		
//Zerando o array dos arquivos
$dadosCabecalho['arquivo'] = array_values($dadosCabecalho['arquivo']);

$objPHPExcel->setActiveSheetIndexByName('CAPA');

$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, 9, iconv('ISO-8859-1', 'UTF-8',$dadosCabecalho['empresa']));
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, 13, iconv('ISO-8859-1', 'UTF-8',$dadosCabecalho['projeto']));
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(13, 5, iconv('ISO-8859-1', 'UTF-8',$dadosCabecalho['versao_documento']));

$objPHPExcel->setActiveSheetIndex(0);

// Redirect output to a clients web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment;filename=lista_materiais_".date('Y_m_d_H_i_s').".xls");
header('Cache-Control: max-age=0');

$objWriter->save('php://output');