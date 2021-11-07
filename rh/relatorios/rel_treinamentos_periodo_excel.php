<?php
/*
		Relatorio treinamentos periodo
		
		Criado por Carlos
		
		local/Nome do arquivo:
		../rh/relatorios/rel_treinamentos_periodo_excel.php
		
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

$objPHPExcel = PHPExcel_IOFactory::load("../modelos_excel/treinamento_periodo.xls");

$locale = 'pt_br';

$validlocale = PHPExcel_Settings::setlocale($locale);

if (!$validlocale) 
{
	echo 'Unable to set locale to '.$locale." - reverting to en_us<br />\n";
}

$clausulasetores = '';
$sql = 
"SELECT * FROM
	(
		SELECT funcionario, id_funcionario, situacao FROM ".DATABASE.".funcionarios
		WHERE funcionarios.situacao NOT IN ('DESLIGADO','CANCELADO') AND funcionarios.reg_del = 0
	) funcionarios,
	".DATABASE.".rh_treinamentos
	LEFT JOIN ".DATABASE.".rh_treinamentos_efetuados ON rh_treinamentos_efetuados.id_treinamento = rh_treinamentos.id_rh_treinamento
WHERE
	rh_treinamentos_efetuados.id_funcionario = funcionarios.id_funcionario
	 AND rh_treinamentos_efetuados.reg_del = 0
	 AND rh_treinamentos.reg_del = 0  
  AND data_vencimento BETWEEN '".php_mysql($_POST['data_inicio'])."' AND '".php_mysql($_POST['data_fim'])."'
ORDER BY
	rh_treinamentos_efetuados.data_vencimento, funcionarios.funcionario";

$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0,1,'RELATÓRIO TREINAMENTOS POR PERÍODO - '.$_POST['data_inicio'].' - '.$_POST['data_fim']);

$linha = 3;
$db->select($sql, 'MYSQL',
	function ($reg, $i) use(&$objPHPExcel, &$linha)
	{
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0,$linha,$reg['funcionario']);
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1,$linha,trim($reg['treinamento']));
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2,$linha,mysql_php($reg['data_vencimento']));
		
		$linha ++;
	}
);

if ($db->numero_registros == 0)
{
	exit('<script>alert("Nenhum registro encontrado!");window.close();</script>');
}

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header("Content-Disposition: attachment;filename=relatorio_treinamentos_periodo_".$_POST['data_inicio'].'_'.$_POST['data_fim'].".xlsx");
header('Cache-Control: max-age=0');
$objWriter->save('php://output');

exit;
?>