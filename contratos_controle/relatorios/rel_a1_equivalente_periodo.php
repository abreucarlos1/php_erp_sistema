<?php
/*
	Relatório de A1 equivalente periodo
	Criado por Carlos Abreu  
	
	Versão 0 --> VERSÃO INICIAL : 25/06/2015
	Versão 1 --> Inclusão dos campos reg_del nas consultas - 17/11/2017 - Carlos Abreu
*/

ini_set('max_execution_time', 0); // No time limit
ini_set('post_max_size', '20M');
ini_set('upload_max_filesize', '20M');

require_once(implode(DIRECTORY_SEPARATOR,array('..','..','config.inc.php')));

require_once(INCLUDE_DIR."PHPExcel/Classes/PHPExcel.php");

require_once(INCLUDE_DIR."PHPExcel/Classes/PHPExcel/Worksheet/Drawing.php");

$db = new banco_dados();

$clausulaTipoEmissao = !empty($_POST['tiposEmissao']) ? "AND id_fin_emissao IN(".implode(',', $_POST['tiposEmissao']).") " : '';

$sql = 
"SELECT setor, SUM(numero_folhas) totalFolhas, formato, fator_equivalente
	FROM ".DATABASE.".setores, ".DATABASE.".numeros_interno, ".DATABASE.".ged_arquivos, ".DATABASE.".ged_versoes, ".DATABASE.".ged_pacotes, ".DATABASE.".formatos
	WHERE numeros_interno.reg_del = 0
	AND setores.reg_del = 0
	AND formatos.reg_del = 0
	AND ged_arquivos.reg_del = 0
	AND ged_versoes.reg_del = 0
	AND ged_pacotes.reg_del = 0
	AND numeros_interno.id_disciplina = setores.id_setor
	AND ged_arquivos.documento_interno = 1
	AND ged_arquivos.id_numero_interno = numeros_interno.id_numero_interno
	AND numeros_interno.mostra_relatorios = '1'
	AND numeros_interno.id_numero_interno = ged_arquivos.id_numero_interno
	AND ged_arquivos.id_ged_versao = ged_versoes.id_ged_versao
	AND ged_versoes.id_ged_pacote = ged_pacotes.id_ged_pacote
	AND numeros_interno.id_formato = formatos.id_formato
	{$clausulaTipoEmissao}
	AND data_emissao_arquivo BETWEEN '".php_mysql($_POST['data_inicio'])."' AND '".php_mysql($_POST['data_fim'])."'
	GROUP BY  setor, formato;";

$totalFolhas = 0;

$objPHPExcel = PHPExcel_IOFactory::load("../modelos_excel/produtividade_A1_equiv_periodo.xls");

$locale = 'pt_br';

$validlocale = PHPExcel_Settings::setlocale($locale);

if (!$validlocale) 
{
	echo 'Unable to set locale to '.$locale." - reverting to en_us<br />\n";
}

//referencia a folha modelo
$linha = 2;

$db->select($sql, 'MYSQL',
	function($reg, $i) use(&$objPHPExcel, &$totalFolhas, &$linha){
		$lin = $i+2;
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $lin,$reg['setor']);
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $lin,$reg['totalFolhas']);
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $lin,$reg['formato']);
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $lin,$reg['fator_equivalente']);
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, $lin,"=B{$lin}*D{$lin}");
		
		$totalFolhas += $reg['totalFolhas'];
		$linha++;
	}
);
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $linha+1,'Total Folhas');
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $linha+1,"=SUM(B2:B".($linha-1).")");

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header("Content-Disposition: attachment;filename=prod_a1_".date("dmYHis").".xlsx");
header('Cache-Control: max-age=0');
$objWriter->save('php://output');

exit;

?>