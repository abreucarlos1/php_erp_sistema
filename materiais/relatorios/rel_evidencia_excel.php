<?php
/*
		Relatório de Evidencia
		
		Criado por Carlos Eduardo  
		
		local/Nome do arquivo:		
		../materiais/relatorios/rel_evidencia_excel.php
		
		Versão 0 --> VERSÃO INICIAL - 02/03/2016
		Versão 1 --> Inclusão dos campos reg_del nas consultas - 01/12/2017 - Carlos Abreu	
*/

error_reporting(E_ERROR);

ini_set('max_execution_time', 0); // No time limit
ini_set('post_max_size', '20M');
ini_set('upload_max_filesize', '20M');

require_once(implode(DIRECTORY_SEPARATOR,array('..','..','config.inc.php')));

require_once(INCLUDE_DIR."PHPExcel/Classes/PHPExcel.php");

$db = new banco_dados();

$objPHPExcel = PHPExcel_IOFactory::load("../modelos_excel/lista_evidencia.xlsx");

$locale = 'pt_br';

$validlocale = PHPExcel_Settings::setlocale($locale);

if (!$validlocale) 
{
	echo 'Unable to set locale to '.$locale." - reverting to en_us<br />\n";
}

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, 1, iconv('ISO-8859-1', 'UTF-8',date('d/m/Y')));

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
		  id_ged_arquivo, id_os, id_disciplina, id_cabecalho, id_lista_materiais_versoes, lista_materiais.atual, cod_lista_materiais,
          qtd, codProduto, componentecodigo, desc_long_por, unidade, descricao, desc_os, setor, desc_os, atividade, desc_arquivo
		FROM
		   ".DATABASE.".lista_materiais
		   JOIN(
			   SELECT
					id_lista_materiais_versoes id_versao, id_lista_materiais cod_lista_materiais, qtd, unidade, margem, revisao_documento, data_versao
				FROM
					".DATABASE.".lista_materiais_versoes
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
		ORDER BY id_disciplina";

$linha=6;
$dadosCabecalho = array();
$db->select($sql, 'MYSQL',
	function ($reg, $i) use (&$objPHPExcel, &$linha, &$dadosCabecalho)
	{
		if (!isset($dadosCabecalho['projeto']))
		{
			$dadosCabecalho['projeto'] = $reg['desc_os'];
			$dadosCabecalho['disciplina'] = $reg['setor'];
		}

		if (!isset($dadosCabecalho['arquivo'][$reg['id_ged_arquivo']]))
		{
			$dadosCabecalho['arquivo'][$reg['id_ged_arquivo']] = $reg['desc_arquivo'].' - '.$reg['atividade'];
			
			if (count($dadosCabecalho['arquivo']) > 1)
			{
				//Se não for o primeiro arquivo da lista, pular 3 linhas
				$linha++;
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $linha, iconv('ISO-8859-1', 'UTF-8',$reg['desc_arquivo'].' - '.$reg['atividade']));
								
				$linha++;
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $linha, iconv('ISO-8859-1', 'UTF-8','Cod. Barras'));
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $linha, iconv('ISO-8859-1', 'UTF-8','Descrição'));
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $linha, iconv('ISO-8859-1', 'UTF-8','Qtd.'));
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $linha, iconv('ISO-8859-1', 'UTF-8','unidade'));
				
				$objPHPExcel->getActiveSheet()->getStyle('A'.$linha.':'.'D'.$linha)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('A'.($linha-1).':'.'D'.$linha)->getFont()->setBold(true);
				
				$linha++;
			}
		}
		
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $linha, iconv('ISO-8859-1', 'UTF-8',$reg['componentecodigo']));
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $linha, iconv('ISO-8859-1', 'UTF-8',$reg['descricao']));
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $linha, iconv('ISO-8859-1', 'UTF-8',$reg['qtd']));
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $linha, iconv('ISO-8859-1', 'UTF-8',$reg['unidade']));
		
		$objPHPExcel->getActiveSheet()->getStyle('A'.$linha.':'.'D'.$linha)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
		$linha++;
	}
);

//Zerando o array dos arquivos
$dadosCabecalho['arquivo'] = array_values($dadosCabecalho['arquivo']);

$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, 1, iconv('ISO-8859-1', 'UTF-8',$dadosCabecalho['projeto']));
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, 2, iconv('ISO-8859-1', 'UTF-8',$dadosCabecalho['disciplina']));
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, 4, iconv('ISO-8859-1', 'UTF-8',$dadosCabecalho['arquivo'][0]));
//$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $linha+1, iconv('ISO-8859-1', 'UTF-8','Total'));
//$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $linha+1, iconv('ISO-8859-1', 'UTF-8','=sum(C6:C'.($linha-1).')'));

// Redirect output to a clients web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment;filename=lista_materiais_".date('Y_m_d_H_i_s').".xlsx");
header('Cache-Control: max-age=0');

$objWriter->save('php://output');