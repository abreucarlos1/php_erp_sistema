<?php
/*
    Relatório Notebooks por OS	
    
    Criado por Carlos 
    
    local/Nome do arquivo:
    ../financeiro/relatorios/rel_notebooks_os.php
    
    Versão 0 --> VERSÃO INICIAL - 20/03/2018		
*/

ini_set('max_execution_time', 0); // No time limit
ini_set('post_max_size', '20M');
ini_set('upload_max_filesize', '20M');

require_once(implode(DIRECTORY_SEPARATOR,array('..','..','config.inc.php')));

require_once(INCLUDE_DIR."PHPExcel/Classes/PHPExcel.php"); 

$db = new banco_dados();

$clausulaDataIni = !empty($_POST['dataini']) ? "AND c.data >= '".php_mysql($_POST['dataini'])."'" : '';
$clausulaDataFim = !empty($_POST['datafim']) ? "AND c.data <= '".php_mysql($_POST['datafim'])."'" : '';
$clausulaDevolucao = !empty($_POST['datafim']) ? "OR data_devolucao >= '".$_POST['datafim']."'" : '';
$clausulaOs = !empty($_POST['id_os']) ? "AND e.id_os = ".$_POST['id_os'] : '';

$nomeComplemento = '';
if (!empty($clausulaDataIni))
    $nomeComplemento .= 'PERÍODO: '.$_POST['dataini']; 
if (!empty($clausulaDataFim))
    $nomeComplemento .= !empty($nomeComplemento) ? ' - '.$_POST['datafim'] : 'PERÍODO: '.$_POST['datafim'];
    
$sql = "SELECT funcionario, equipamento, os, MAX(nApontamentos) nApontamentos, num_dvm, data_saida saida
        FROM (
        	SELECT 
        		DISTINCT d.funcionario, b.num_dvm, equipamento, MIN(data_saida) data_saida, MAX(c.data) inicio, 
                CONCAT(LPAD(e.os,5,'0'), ' - ', e.descricao) os,
        		COUNT(DISTINCT c.data) nApontamentos, a.data_devolucao
        	FROM 
        		ti.inventario a
        		JOIN ti.equipamentos b ON b.reg_del = 0 AND b.id_equipamento = a.id_equipamento  AND area = 'TI'
        		LEFT JOIN ".DATABASE.".apontamento_horas c ON c.reg_del = 0 AND c.id_funcionario = a.id_funcionario AND c.data >= data_saida
        		LEFT JOIN ".DATABASE.".funcionarios d ON d.reg_del = 0 AND d.id_funcionario = a.id_funcionario
        		LEFT JOIN ".DATABASE.".ordem_servico e ON e.reg_del = 0 AND e.id_os = c.id_os
        	WHERE
        		a.reg_del = 0 ".$clausulaDataIni." ".$clausulaDataFim." ".$clausulaOs."        
        	GROUP BY
        		funcionario, equipamento, e.os, e.descricao, data_devolucao
        	ORDER BY
        		funcionario, equipamento, COUNT(DISTINCT c.data) DESC
        ) consulta
        WHERE (data_devolucao IS NULL ".$clausulaDevolucao.")
        GROUP BY
        	equipamento, funcionario, saida";

$objPHPExcel = PHPExcel_IOFactory::load("../modelos_excel/relatorio_notebooks_os.xls");

$objPHPExcel->setActiveSheetIndex(0);
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, 1, 'RELATÓRIO DE NOTEBOOKS POR OS '.$nomeComplemento);

$db->select($sql, 'MYSQL', function($reg, $i) use(&$objPHPExcel)
{
    $linha = $i+3;
    $coluna = 0;
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna++, $linha, maiusculas($reg['num_dvm']));
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna++, $linha, maiusculas($reg['equipamento']));
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna++, $linha, mysql_php(substr($reg['saida'],0,10)));
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna++, $linha, maiusculas($reg['funcionario']));
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna++, $linha, $reg['os']);
});

$locale = 'pt_br';

$validlocale = PHPExcel_Settings::setlocale($locale);

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="previsao_custo_"'.date('His').'".xlsx"');
header('Cache-Control: max-age=0');

$objWriter->save('php://output');

exit;
?>
