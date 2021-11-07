<?php
/*
		Relatório lista materiais 
		
		Criado por Carlos 
		
		local/Nome do arquivo:		
		../materiais/relatorios/rel_lista_materiais_excel.php
		
		Versão 0 --> VERSÃO INICIAL - 02/03/2016
		Versão 1 --> Inclusão dos campos reg_del nas consultas - 01/12/2017 - Carlos Abreu	
*/

ini_set('max_execution_time', 0); // No time limit
ini_set('post_max_size', '20M');
ini_set('upload_max_filesize', '20M');

require_once(implode(DIRECTORY_SEPARATOR,array('..','..','config.inc.php')));

require_once(INCLUDE_DIR."PHPExcel/Classes/PHPExcel.php");

$db = new banco_dados();

$objPHPExcel = PHPExcel_IOFactory::load("../modelos_excel/lista_materiais.xlsx");

$locale = 'pt_br';

$validlocale = PHPExcel_Settings::setlocale($locale);

if (!$validlocale) 
{
	echo 'Unable to set locale to '.$locale." - reverting to en_us<br />\n";
}

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, 1, iconv('ISO-8859-1', 'UTF-8',date('d/m/Y')));

$clausulaIdGedArquivo = isset($_GET['id_ged_arquivo']) ? "AND id_lista_materiais_cabecalho IN(SELECT DISTINCT id_lista_materiais FROM ".DATABASE.".lista_materiais WHERE lista_materiais.reg_del = 0 AND lista_materiais.id_ged_arquivo = {$_GET['id_ged_arquivo']})" : '';

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
		  *
		FROM
		   ".DATABASE.".lista_materiais
		   JOIN(
			   SELECT
					id_lista_materiais cod_lista_materiais, qtd, unidade, margem, revisao_documento, data_versao
				FROM
					".DATABASE.".lista_materiais_versoes
				WHERE
					lista_materiais_versoes.reg_del = 0 
					{$clausulaIdGedArquivo}
		   ) versoes
		   ON cod_lista_materiais = id_lista_materiais
		   JOIN(
			   SELECT
					id_lista_materiais_cabecalho id_cabecalho
			   FROM
					".DATABASE.".lista_materiais_cabecalho
			   WHERE
					lista_materiais_cabecalho.reg_del = 0
		   )cabecalho
		   ON id_cabecalho = id_lista_materiais_cabecalho 
		   JOIN(
			   SELECT
				  atual, id_produto codProduto, cod_barras componentecodigo, desc_res_ing, desc_res_esp, desc_long_por, desc_long_ing, desc_long_esp, unidade1, unidade2, peso1, peso2
			   FROM ".DATABASE.".produto WHERE produto.reg_del = 0
		   ) produto
		   ON codProduto = id_produto
		  JOIN(
			SELECT id_grupo, id_sub_grupo, codigo_inteligente, descricao, cod_barras codBarrasComponente FROM ".DATABASE.".componentes WHERE componentes.reg_del = 0
		  ) componentes
		  ON codBarrasComponente = componentecodigo
		WHERE
		  lista_materiais.reg_del = 0
		  AND produto.atual = 1
		  {$clausulaIdOs}
		  $clausulaIdDisciplina";

$linha=3;
$db->select($sql, 'MYSQL',
	function ($reg, $i) use (&$objPHPExcel, &$linha)
	{
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $linha, iconv('ISO-8859-1', 'UTF-8',$reg['componentecodigo']));
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $linha, iconv('ISO-8859-1', 'UTF-8',$reg['descricao']));
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $linha, iconv('ISO-8859-1', 'UTF-8',$reg['qtd']));
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $linha, iconv('ISO-8859-1', 'UTF-8',strtoupper($reg['unidade'])));
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, $linha, iconv('ISO-8859-1', 'UTF-8',$reg['margem']));
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, $linha, iconv('ISO-8859-1', 'UTF-8',"=IF(E{$linha}>0,C{$linha}*(E{$linha}/100+1),C{$linha})"));
		
		$linha++;
	}
);

// Redirect output to a clients web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment;filename=lista_materiais_".date('Y_m_d_H_i_s').".xlsx");
header('Cache-Control: max-age=0');

$objWriter->save('php://output');