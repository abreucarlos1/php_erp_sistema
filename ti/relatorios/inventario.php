<?php
require_once(implode(DIRECTORY_SEPARATOR,array('..','..','config.inc.php')));

require_once(INCLUDE_DIR."PHPExcel/Classes/PHPExcel.php");

$db = new banco_dados();

if (!empty($_GET['atuais']))
{
	$clausulaAtuais = " AND (data_devolucao IS NULL OR data_devolucao = '0000-00-00 00:00:00') ";
}

$sql = 
	"SELECT
	  data_saida, data_devolucao, id_funcionario, tipo, os, equipamento, num_dvm, funcionario, area, GROUP_CONCAT(acessorio) acessorios,
      situacao_devolucao
	FROM
	  ti.inventario
	  JOIN (
	    SELECT id_equipamento equip, equipamento, num_dvm, area FROM ti.equipamentos WHERE reg_del = 0
	  ) equipamentos
	  ON equip = id_equipamento
	  JOIN (
	    SELECT funcionario, id_funcionario func FROM ".DATABASE.".funcionarios
	  ) funcionario
	  ON func = id_funcionario
	  LEFT JOIN(
		  SELECT id_inventario inv, acessorio FROM ti.inventario_acessorios
		  JOIN (
		    SELECT id_acessorio id, acessorio FROM ti.acessorios WHERE reg_del = 0
		    ) acessorios
		    ON acessorios.id = id_acessorio
		    WHERE reg_del = 0
		  ) ia
		  ON ia.inv = inventario.id_inventario
	WHERE
	  reg_del = 0
	  ".$clausulaAtuais."
	GROUP BY
	  data_saida, data_devolucao, id_funcionario, tipo, os, equipamento, num_dvm, funcionario
	ORDER BY
	  data_saida DESC, data_devolucao, id_funcionario";

$db->select($sql, 'MYSQL',true);

$objPHPExcel = PHPExcel_IOFactory::load("../modelos_excel/relatorio_inventario.xls");
$objWriter 	= PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

$locale = 'pt_br';

$validlocale = PHPExcel_Settings::setlocale($locale);

if (!$validlocale) 
{
	echo 'Unable to set locale to '.$locale." - reverting to en_us<br />\n";
}

$objPHPExcel->setActiveSheetIndex(0);

$linha = 6;
foreach($db->array_select as $reg)
{
	$dataDevolucao = intval($reg['data_devolucao']) > 0 ? mysql_php(substr($reg['data_devolucao'],0,10)) : '';
	$col = 0;
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col++, $linha, mysql_php(substr($reg['data_saida'],0,10)));
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col++, $linha, $dataDevolucao);
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col++, $linha, utf8_encode($reg['funcionario']));
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col++, $linha, $reg['os']);
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col++, $linha, $reg['tipo']);
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col++, $linha, $reg['equipamento']);
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col++, $linha, $reg['num_dvm']);
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col++, $linha, str_replace(',', ', ',$reg['acessorios']));
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col++, $linha, utf8_encode($reg['situacao_devolucao']));
	
	$linha++;
}

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="relatorio_controle_notebooks.xlsx"');
header('Cache-Control: max-age=0');

$objWriter->save('php://output');