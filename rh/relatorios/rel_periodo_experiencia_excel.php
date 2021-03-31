<?php
/*
		Relatorio periodo Experiencia
		
		Criado por Carlos Eduardo
		
		local/Nome do arquivo:
		../rh/relatorios/rel_periodo_experiencia_excel.php
		
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

$objPHPExcel = PHPExcel_IOFactory::load("../modelos_excel/periodo_experiencia.xls");

$locale = 'pt_br';

$validlocale = PHPExcel_Settings::setlocale($locale);

if (!$validlocale) 
{
	echo 'Unable to set locale to '.$locale." - reverting to en_us<br />\n";
}

$sql = 
"SELECT * FROM (
	SELECT 
		f.id_funcionario, fa.avaliador, f.id_funcao, f.reg_del, f.id_setor, f.funcionario avaliado, f.data_inicio, 
		CASE
			WHEN datediff(date_add(f.data_inicio, INTERVAL 45 DAY), now()) between -7 AND 7 
			THEN 
				date_add(f.data_inicio, INTERVAL 45 DAY)
			ELSE 
				date_add(f.data_inicio, INTERVAL 90 DAY) 
		END termino_experiencia,
	
		CASE WHEN datediff(date_add(f.data_inicio, INTERVAL 45 DAY), now()) between -7 AND 7 THEN '1' ELSE '2' END periodo,
	    pe.id, rf.descricao, pe.comentarios, pe.aprovado, f.tipo_empresa
	FROM 
		".DATABASE.".funcionarios f
		JOIN ".DATABASE.".rh_funcoes rf ON rf.id_funcao = f.id_funcao AND rf.reg_del = 0
	    LEFT JOIN ".DATABASE.".periodo_experiencia pe ON pe.reg_del = 0 AND pe.id_avaliado = f.id_funcionario AND pe.reg_del = 0
	    LEFT JOIN (SELECT id_funcionario codAvaliador, funcionario avaliador FROM ".DATABASE.".funcionarios WHERE situacao = 'ATIVO' AND reg_del = 0) fa ON fa.codAvaliador = id_avaliador
	
	WHERE
		f.reg_del = 0 
		AND (
			datediff(date_add(f.data_inicio, INTERVAL 45 DAY), now()) between -7 AND 7
	        OR
			datediff(date_add(f.data_inicio, INTERVAL 90 DAY), now()) between -7 AND 7
		)
	OR pe.id_avaliado IS NOT NULL
) lista

ORDER BY
	avaliado";

$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0,1,iconv('ISO-8859-1', 'UTF-8','RELATÓRIO DE PERÍODO DE EXPERIÊNCIA'));

$arrPeriodo = array(1 => '45 DIAS', 2 => '90 DIAS');
$linha = 3;
$db->select($sql, 'MYSQL',
    function ($reg, $i) use(&$objPHPExcel, &$linha, &$arrPeriodo)
	{
	    $situacao = !empty($reg['aprovado']) ? $reg['aprovado'] == 1 ? 'APROVADO' : 'REPROVADO' : 'NÃO AVALIADO';
	    
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0,$linha,$reg['avaliado']);
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1,$linha,$reg['avaliador']);
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2,$linha,$arrPeriodo[$reg['periodo']]);
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3,$linha,$situacao);
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4,$linha,$reg['comentarios']);
		
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