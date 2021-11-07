<?php
/*
	Exportação lista desenhos
	Criado por Carlos 
	
	Versão 0 --> VERSÃO INICIAL - 13/05/2016
	Versão 1 --> Inclusão dos campos reg_del nas consultas - 01/12/2017 - Carlos Abreu		
*/
ini_set('max_execution_time', 0); // No time limit
ini_set('post_max_size', '20M');
ini_set('upload_max_filesize', '20M');

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));

require_once(INCLUDE_DIR."PHPExcel/Classes/PHPExcel.php");

$db = new banco_dados();

$idLista = isset($_GET['idLista']) ? $_GET['idLista'] : 0;
$idListaMateriais = '';
$idListaCab = '';
$versao_documento = isset($_GET['versao_documento']) ? $_GET['versao_documento'] : 0;
$fechados = isset($_GET['fechados']) ? $_GET['fechados'] : 1;

//Quando forem selecionadas colunas adicionais, pegar o arquivo das colunas adicionais 
$complNomePlanilha = isset($_GET['colunasAdicionais']) && $_GET['colunasAdicionais'] == 1 ? 'colunasAdicionais_' : '';

$excel_file = "./modelos_excel/planilha_exportacao_documento.xls";

$objPHPExcel = PHPExcel_IOFactory::load($excel_file);

$locale = 'pt_br';

$validlocale = PHPExcel_Settings::setlocale($locale);

if (!$validlocale) 
{
	echo 'Unable to set locale to '.$locale." - reverting to en_us<br />\n";
}

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

//Obtendo as configurações da planilha selecionada
$sql = 
"SELECT
	mle_campo, mle_celula, mle_formula
FROM
	".DATABASE.".modelo_lista_excel
WHERE
	modelo_lista_excel.reg_del = 0 
	AND modelo_lista_excel.mle_mlc_id = {$idModeloLista}";

$naoAplicaveis = array(
	'logo_cliente',
	'id_lista_materiais',
	'revisao_documento',
	'versao_documento',
	'Descricao_os',
	'empresa',
	'revisao_material'
);

$parametros = array();
$celAnt		= '';
$db->select($sql, 'MYSQL', function($reg, $i) use(&$parametros, &$naoAplicaveis){
	if (!in_array($reg['mle_campo'], $naoAplicaveis))
	{
		$parametros['lista'][minusculas(trim($reg['mle_celula']))] = array(
			'formula' => intval($reg['mle_formula']),
			'campo' => minusculas($reg['mle_campo'])
		);
	}
	else
	{
		$parametros['cabecalho'][$reg['mle_campo']] = array(
			'celula' => $reg['mle_celula']
		);
	}
});

$clausulaFechados = $fechados == 1 ? 'AND fechado = 1' : '';

$sql = "SELECT
	MAX(id_lista_materiais) id_lista_materiais, MAX(id_produto) id_produto, MAX(id_os) id_os,
	MAX(id_lista_materiais_cabecalho) id_lista_materiais_cabecalho, round(SUM(qtd), 3) qtd, MAX(unidade) unidade,
    SUM(margem) margem, SUM(revisao_documento) revisao_documento, case when descFamilia is not null then CONCAT(MAX(descFamilia), ', ', MAX(descricao)) else MAX(descricao) end  desc_long_por, 
	MAX(componentecodigo) componentecodigo, MAX(descFamilia) descFamilia,
	MAX(id_lista_materiais_versoes) maiorVersao, marcar_excluido, status, /*SUM(qtd_comprada) qtd_comprada, */
	MAX(data_versao) data_versao, MAX(versao_documento) ultimaRevisao, id_ged_arquivo, descArquivoGed, numero_cliente
FROM
  ".DATABASE.".lista_materiais
  JOIN(
   SELECT
		id_lista_materiais_cabecalho id_cabecalho, status, versao_documento revLC
   FROM
		".DATABASE.".lista_materiais_cabecalho
   WHERE
		lista_materiais_cabecalho.reg_del = 0
)cabecalho
ON id_cabecalho = id_lista_materiais_cabecalho
AND revLC = versao_documento
JOIN(
   SELECT
		id_lista_materiais cod_lista_materiais, qtd, unidade, margem, revisao_documento, data_versao,
		id_lista_materiais_versoes as idVersao
	FROM
		".DATABASE.".lista_materiais_versoes
		JOIN(
			SELECT id_lista_materiais id_lm FROM ".DATABASE.".lista_materiais WHERE (lista_materiais.reg_del = 0 OR lista_materiais.marcar_excluido = 1) AND lista_materiais.id_lista_materiais_cabecalho IN(".$idLista.")
		) lm
		ON id_lm = id_lista_materiais
	WHERE
		lista_materiais_versoes.reg_del = 0
		AND lista_materiais_versoes.id_lista_materiais_cabecalho IN(".$idLista.")
) versoes
ON idVersao = id_lista_materiais_versoes
JOIN(
	SELECT
		atual, id_produto codProduto, cod_barras componentecodigo, desc_res_ing, desc_res_esp, desc_long_por, desc_long_ing, desc_long_esp, unidade1, unidade2, peso1, peso2
	FROM ".DATABASE.".produto
	WHERE atual = 1 AND reg_del = 0
) produto
ON componentecodigo = cod_barras
JOIN(
	SELECT id_grupo, id_sub_grupo, codigo_inteligente, descricao, cod_barras codBarrasComponente, descFamilia
	FROM 
		".DATABASE.".componentes
		LEFT JOIN (
			SELECT id_familia idFamilia, descricao descFamilia FROM ".DATABASE.".familia WHERE familia.reg_del = 0
		) familia
		ON idFamilia = id_familia
	WHERE 
		componentes.reg_del = 0
) componentes
ON codBarrasComponente = componentecodigo
LEFT JOIN(
	SELECT id_ged_arquivo idArquivoGed, descricao descArquivoGed, numero_cliente 
	FROM ".DATABASE.".ged_arquivos 
		 JOIN ".DATABASE.".numeros_interno ON numeros_interno.id_numero_interno = ged_arquivos.id_numero_interno AND numeros_interno.reg_del = 0
	WHERE
		ged_arquivos.reg_del = 0
) arquivo_ged
ON idArquivoGed = id_ged_arquivo
WHERE
	lista_materiais.reg_del = 0
	AND produto.atual = 1
	AND lista_materiais.id_lista_materiais_cabecalho IN(".$idLista.")
	AND lista_materiais.atual = 1
GROUP BY componentecodigo ORDER BY componentecodigo";

$linha=2;
$primeiraLinha = 0;
$ultimaLinha = 0;
$cabecalhoPronto = false;

$db->select($sql, 'MYSQL',
	function ($reg, $i) use (&$objPHPExcel, &$linha)
	{
		$objPHPExcel->getActiveSheet()->setCellValue('A'.$linha, iconv('ISO-8859-1', 'UTF-8',$reg['componentecodigo']));
		$objPHPExcel->getActiveSheet()->setCellValue('B'.$linha, iconv('ISO-8859-1', 'UTF-8',$reg['desc_long_por']));
		$objPHPExcel->getActiveSheet()->setCellValue('C'.$linha, iconv('ISO-8859-1', 'UTF-8',$reg['qtd']));
		$objPHPExcel->getActiveSheet()->setCellValue('D'.$linha, iconv('ISO-8859-1', 'UTF-8',$reg['unidade']));
		
		$objPHPExcel->getActiveSheet()->getRowDimension($linha)->setRowHeight(35);
		$objPHPExcel->getActiveSheet()->getStyle('B'.$linha)->getAlignment()->setWrapText(true);
		$linha++;
	}
);

$ultimaLinha = $linha;

$objPHPExcel->getActiveSheet()->getStyle('A'.$primeiraLinha.':'.'D'.$ultimaLinha)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

// Redirect output to a clients web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment;filename='lista_emitida_".date('Y_m_d_H_i_s').".xlsx");
header('Cache-Control: max-age=0');

$objWriter->save('php://output');