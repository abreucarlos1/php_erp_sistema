<?php
/*
	Relatório de saldo de clientes
	Criado por Carlos Eduardo  
	
	Obs.: Este arquivo é chamado de contratos_controle/bms.php
	
	Versão 0 --> VERSÃO INICIAL : 23/06/2015
	Versão 1 --> Correções gerais: 17/08/2017 - Carlos Eduardo
	Versão 2 --> Inclusão dos campos reg_del nas consultas - 17/11/2017 - Carlos Abreu
	Versão 3 --> Inclusão do campo reembolso de despesa - 08/03/2018 - Carlos Eduardo
*/
require_once(implode(DIRECTORY_SEPARATOR,array('..','..','config.inc.php')));

require_once(INCLUDE_DIR."PHPExcel/Classes/PHPExcel.php");

$db = new banco_dados();

$dataRelatorio = php_mysql($_GET['data']);

$complData = '';
$complDataMedicao = '';


if (!empty($_GET['data_fim']))
{
    $complDataMedicao .= "AND data <= '".php_mysql($_GET['data_fim'])."'";
}

$clausulaOs = !empty($_GET['id_solicitacao_documento']) ? "AND id_bms_pedido = {$_GET['id_solicitacao_documento']}" : '';

$sql = 
"SELECT 
	a.os, IFNULL(sum(d.valor_medido),0) total_medido, a.valor_pedido valor_planejado, a.valor_pedido - IFNULL(sum(d.valor_medido), 0) as saldo, data, a.os numOs,
    a.data_pedido, e.empresa, tp_orc_protheus, b.descricao
FROM 
	".DATABASE.".bms_pedido a
    JOIN ".DATABASE.".ordem_servico b on b.os = a.os AND b.reg_del = 0 AND b.id_os_status IN(1,2,7,14,15,16)
    JOIN ".DATABASE.".bms_item c on c.id_bms_pedido = a.id_bms_pedido AND c.reg_del = 0
    LEFT JOIN ".DATABASE.".bms_medicao d on d.id_bms_item = c.id_bms_item AND d.reg_del = 0 ".$complDataMedicao."
    JOIN ".DATABASE.".empresas e ON e.id_empresa_erp = b.id_empresa_erp AND e.reg_del = 0 
WHERE 
	a.reg_del = 0  AND reembolso_despesa = 0 AND (a.data_pedido > '2017-01-01' OR a.os IN(SELECT os FROM ".DATABASE.".bms_excecoes WHERE bms_excecoes.reg_del = 0))
GROUP BY
	a.os
HAVING 
    a.valor_pedido - IFNULL(sum(d.valor_medido),0) > 0.00
ORDER BY
	a.os";

$objPHPExcel = PHPExcel_IOFactory::load('../modelos_excel/resumo_saldo_cliente.xlsx');

$locale = 'pt_br';

$validlocale = PHPExcel_Settings::setlocale($locale);

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

$linha = 10;
$arrCols = array('A', 'B', 'C', 'D', 'E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T');

$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2,2,$_GET['data']);
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3,2,$_GET['data_fim']);

$linha = 3;
$db->select($sql, 'MYSQL',
	function($reg, $i) use(&$objPHPExcel, &$linha)
	{
		/*
		$sql = 
		"SELECT AF8_PROJET, AF8_REVISA, AF8_DESCRI, AF8_FASE FROM AF8010
		WHERE 
			AF8010.D_E_L_E_T_ = ''
			--AND (AF8010.AF8_FASE = '03' OR AF8010.AF8_FASE = '09' OR AF8010.AF8_FASE = '07')
			AND AF8010.AF8_PROJET = '".sprintf('%010d', $reg['numOs'])."'
		GROUP BY
			AF8010.AF8_PROJET, AF8010.AF8_REVISA, AF8010.AF8_DESCRI, AF8_FASE";
		
		$db2 = new banco_dados();
		$db2->select($sql, 'MSSQL', true);
		$regOs = $db2->array_select[0];
		*/
		
		$linha = $i + 4;
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0,$linha,$reg['numOs']);
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1,$linha,trim($reg['descricao']));
	
		if ($reg['tp_orc_protheus'] == 1)
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2,$linha,$reg['saldo']);
		else
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3,$linha,$reg['saldo']);
	}
);

$ultimaLinha = $linha;
$linha+=2;

$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2,$linha,"=SUM(C1:C{$ultimaLinha})");
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3,$linha,"=SUM(D1:D{$ultimaLinha})");
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2,$linha+1,"=C{$linha}/C".($linha+2));
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3,$linha+1,"=D{$linha}/C".($linha+2));
$objPHPExcel->getActiveSheet()->getStyle('C'.($linha+1).':D'.($linha+1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE_00);

$styleArray = array(
    'borders' => array(
        'allborders' => array(
            'style' => PHPExcel_Style_Border::BORDER_THIN
        )
    )
);

$objPHPExcel->getActiveSheet()->getStyle('A4:D'.$ultimaLinha)->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('C'.($ultimaLinha+2).':D'.($ultimaLinha+4))->applyFromArray($styleArray);

$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2,$linha+2,"=SUM(C{$linha}:D{$linha})");
$linha+=2;
$objPHPExcel->getActiveSheet()->mergeCells("C{$linha}:D{$linha}");
$tmpName = md5(date('YmdHis')).'.xlsx';

header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header("Content-Disposition: attachment;filename={$tmpName}");
header("Cache-Control: max-age=0");

$objWriter->save('php://output');

exit();
?>