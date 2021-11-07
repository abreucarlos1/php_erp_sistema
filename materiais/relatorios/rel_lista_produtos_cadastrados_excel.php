<?php
/*
		Relatório lista produtos cadastrados 
		
		Criado por Carlos  
		
		local/Nome do arquivo:		
		../materiais/relatorios/rel_lista_produtos_cadastrados_excel.php
		
		Versão 0 --> VERSÃO INICIAL - 02/03/2016
		Versão 1 --> Inclusão dos campos reg_del nas consultas - 01/12/2017 - Carlos Abreu	
*/

error_reporting(0);

require_once(implode(DIRECTORY_SEPARATOR,array('..','..','config.inc.php')));

require_once(INCLUDE_DIR."PHPExcel/Classes/PHPExcel.php");

require_once(INCLUDE_DIR."antiInjection.php");

$db = new banco_dados();

$objPHPExcel = PHPExcel_IOFactory::load("../modelos_excel/lista_produtos_cadastrados.xlsx");

$locale = 'pt_br';

$validlocale = PHPExcel_Settings::setlocale($locale);

if (!$validlocale) 
{
	echo 'Unable to set locale to '.$locale." - reverting to en_us<br />\n";
}

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, 1, iconv('ISO-8859-1', 'UTF-8',date('d/m/Y')));

$filtro = $_GET['filtro'];

$sql_filtro = "";
$sql_texto = "";

if(!empty($filtro))
{
	$sql_texto = str_replace('  ', ' ', AntiInjection::clean($filtro));
	$sql_texto = str_replace(' ', '%', '%'.$sql_texto.'%');
	
	$sql_filtro = " AND (codigo_inteligente LIKE '".$sql_texto."' ";
	$sql_filtro .= " OR cod_barras LIKE '".$sql_texto."' ";
	$sql_filtro .= " OR desc_long_por LIKE '".$sql_texto."' ";
	$sql_filtro .= " OR descricao LIKE '".$sql_texto."' )";
}
$sql = 
"SELECT
  id_produto, cod_barras componentecodigo, codigo_inteligente, descricao, desc_res_ing, desc_res_esp, desc_long_por, desc_long_ing, desc_long_esp, unidade1, unidade2, peso1, peso2
FROM
  ".DATABASE.".produto
  JOIN(
    SELECT id_grupo, id_sub_grupo, codigo_inteligente, descricao, cod_barras codBarrasComponente FROM ".DATABASE.".componentes WHERE componentes.reg_del = 0
  ) componentes
  ON codBarrasComponente = cod_barras
WHERE
  produto.reg_del = 0
  AND atual = 1
  $sql_filtro";

$linha=3;
$db->select($sql, 'MYSQL',
	function ($reg, $i) use (&$objPHPExcel, &$linha)
	{
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $linha, iconv('ISO-8859-1', 'UTF-8',$reg['componentecodigo']));
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $linha, iconv('ISO-8859-1', 'UTF-8',$reg['codigo_inteligente']));
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $linha, iconv('ISO-8859-1', 'UTF-8',$reg['desc_long_por']));
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $linha, iconv('ISO-8859-1', 'UTF-8',$reg['descricao']));
				
		$linha++;
	}
);

// Redirect output to a clients web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment;filename='lista_materiais_'".date('Y_m_d_H_i_s').".xlsx'");
header('Cache-Control: max-age=0');

$objWriter->save('php://output');