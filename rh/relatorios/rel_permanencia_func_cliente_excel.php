<?php
/*
		Relatorio permanencia cliente
		
		Criado por Carlos Eduardo
		
		local/Nome do arquivo:
		../rh/relatorios/rel_permanencia_func_cliente_excel.php
		
		Versão 0 --> VERSÃO INICIAL - 04/05/2016
		Versão 1 --> Inclusão dos campos reg_del nas consultas - 27/11/2017 - Carlos Abreu	
*/
ini_set('max_execution_time', 0); // No time limit
ini_set('post_max_size', '20M');
ini_set('upload_max_filesize', '20M');

require_once(implode(DIRECTORY_SEPARATOR,array('..','..','config.inc.php')));

require_once(INCLUDE_DIR."PHPExcel/Classes/PHPExcel.php");

require_once(INCLUDE_DIR."PHPExcel/Classes/PHPExcel/Worksheet/Drawing.php");

$db = new banco_dados();

$objPHPExcel = PHPExcel_IOFactory::load("../modelos_excel/permanencia_func_cliente.xls");

$locale = 'pt_br';

$validlocale = PHPExcel_Settings::setlocale($locale);

if (!$validlocale) 
{
	echo 'Unable to set locale to '.$locale." - reverting to en_us<br />\n";
}

$sql = "SELECT
	flt_id, flt_id_funcionario, flt_id_local, flt_inicio, flt_fim, flt_atual, funcionario, local.descricao, 
    tipo_empresa, flt_numero_contrato, flt_id_os, flt_qtd_horas, os, os.descricao descOs
FROM 
	".DATABASE.".funcionario_x_local_trabalho
    JOIN ".DATABASE.".funcionarios ON id_funcionario = flt_id_funcionario AND funcionarios.reg_del = 0
    JOIN ".DATABASE.".local ON id_local = flt_id_local AND local.reg_del = 0 
    LEFT JOIN ".DATABASE.".ordem_servico ON ordem_servico.id_os = flt_id_os AND ordem_servico.reg_del = 0 
WHERE
	funcionario_x_local_trabalho.reg_del = 0 ".$sql_filtro." ".$complAtual.' ORDER BY flt_id_funcionario, flt_inicio DESC';

$db->select($sql,'MYSQL',true);

$objPHPExcel->setActiveSheetIndex(0);

$linha = 3;
$db->select($sql, 'MYSQL',
	function ($reg, $i) use(&$objPHPExcel, &$linha)
	{
	    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0,$linha,$reg['flt_numero_contrato']);
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1,$linha,$reg['descricao']);
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2,$linha,$reg['funcionario']);
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3,$linha,$reg['OS']);
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4,$linha,$reg['descOs']);
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5,$linha,mysql_php($reg['flt_inicio']));
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6,$linha,mysql_php($reg['flt_fim']));
		$linha++;
	}
);

if ($db->numero_registros == 0)
{
	exit('<script>alert("Nenhum registro encontrado!");window.close();</script>');
}

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header("Content-Disposition: attachment;filename=relatorio_periodo_colab_cliente_".date('Ymd').".xlsx");
header('Cache-Control: max-age=0');
$objWriter->save('php://output');

exit;

?>