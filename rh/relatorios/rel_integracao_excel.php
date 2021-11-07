<?php
/*
		Relatorio Integração
		
		Criado por Carlos
		
		local/Nome do arquivo:
		../rh/relatorios/rel_integracao_excel.php
		
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

$objPHPExcel = PHPExcel_IOFactory::load("../modelos_excel/integracao.xls");

$locale = 'pt_br';

$validlocale = PHPExcel_Settings::setlocale($locale);

if (!$validlocale) 
{
	echo 'Unable to set locale to '.$locale." - reverting to en_us<br />\n";
}

$sql = "SELECT funcionario, descricao, data_integracao, data_vencimento FROM ".DATABASE.".funcionarios, ".DATABASE.".local, ".DATABASE.".rh_integracao
WHERE rh_integracao.id_funcionario = funcionarios.id_funcionario
AND funcionarios.reg_del = 0
AND local.reg_del = 0
AND rh_integracao.reg_del = 0
AND funcionarios.situacao NOT IN ('DESLIGADO','CANCELADO')
AND rh_integracao.id_local_trabalho = local.id_local
AND data_vencimento >= NOW()
ORDER BY rh_integracao.data_vencimento, funcionarios.funcionario ";	

$db->select($sql,'MYSQL',true);

$objPHPExcel->setActiveSheetIndex(0);

$linha = 3;
$db->select($sql, 'MYSQL',
	function ($reg, $i) use(&$objPHPExcel, &$linha)
	{
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0,$linha,$reg['funcionario']);
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1,$linha,$reg['descricao']);
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2,$linha,mysql_php($reg['data_integracao']));
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3,$linha,mysql_php($reg['data_vencimento']));
		$linha++;
	}
);

if ($db->numero_registros == 0)
{
	exit('<script>alert("Nenhum registro encontrado!");window.close();</script>');
}

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header("Content-Disposition: attachment;filename=relatorio_integracoes_".date('Ymd').".xlsx");
header('Cache-Control: max-age=0');
$objWriter->save('php://output');

exit;