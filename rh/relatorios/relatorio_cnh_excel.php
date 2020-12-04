<?php
/*
		Relatorio CNH
		
		Criado por Carlos Eduardo
		
		local/Nome do arquivo:
		../rh/relatorios/relatorio_cnh_excel.php
		
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

$objPHPExcel = PHPExcel_IOFactory::load("../modelos_excel/relatorio_cnh.xls");

$locale = 'pt_br';

$validlocale = PHPExcel_Settings::setlocale($locale);

if (!$validlocale) 
{
	echo 'Unable to set locale to '.$locale." - reverting to en_us<br />\n";
}

$sql = 
"SELECT
	funcionario, numero_habilitacao, categoria, rh_habilitacao.data_emissao, data_vencimento
FROM
	".DATABASE.".funcionarios, ".DATABASE.".rh_habilitacao
WHERE
	rh_habilitacao.id_funcionario = funcionarios.id_funcionario
	AND funcionarios.situacao NOT IN ('DESLIGADO','CANCELADO')
	AND funcionarios.reg_del = 0 
ORDER BY
	funcionarios.funcionario, rh_habilitacao.data_vencimento ";	

$db->select($sql,'MYSQL',true);

$linha = 3;
$db->select($sql, 'MYSQL',
	function ($reg, $i) use(&$objPHPExcel, &$linha)
	{
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0,$linha,maiusculas($reg['funcionario']));
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1,$linha,$reg['numero_habilitacao']);
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2,$linha,maiusculas($reg['categoria']));
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3,$linha,mysql_php($reg['data_emissao']));
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4,$linha,mysql_php($reg['data_vencimento']));
		
		$linha ++;
	}
);

if ($db->numero_registros == 0)
{
	exit('<script>alert("Nenhum registro encontrado!");window.close();</script>');
}

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header("Content-Disposition: attachment;filename=relatorio_cnh_".date('d_m_Y').".xlsx");
header('Cache-Control: max-age=0');
$objWriter->save('php://output');

exit;

?>