<?php
/*
	Relat�rio de vendas no per�odo selecionado
	Criado por Carlos Eduardo  
	
	Obs.: Este arquivo � chamado de contratos_controle/bms.php
	
	Vers�o 0 --> VERS�O INICIAL : 25/06/2015
	Vers�o 1 --> Inclus�o dos campos reg_del nas consultas - 17/11/2017 - Carlos Abreu
*/
require_once(implode(DIRECTORY_SEPARATOR,array('..','..','config.inc.php')));

require_once(INCLUDE_DIR."PHPExcel/Classes/PHPExcel.php");

$db = new banco_dados();

$ano = $_POST['selAno'];

$sql = 
"SELECT
    SUM(valor_pedido) valorTotal, SUBSTRING(data_pedido, 1, 7) data, month(data_pedido) mes, bms_pedido.tp_orc_protheus tpOrc,
    COUNT(DISTINCT os) nPropostas
  FROM
  	(SELECT DISTINCT id_bms_pedido, data_pedido, valor_pedido, os, condicao_pgto, id_cliente_protheus, id_loja_protheus, tp_orc_protheus FROM ".DATABASE.".bms_pedido WHERE bms_pedido.reg_del = 0 AND reembolso_despesa = 0) bms_pedido
WHERE
	valor_pedido > 0
    AND year(data_pedido) = '".$ano."'
GROUP BY
  tp_orc_protheus, data, mes
ORDER BY
	data";

$objPHPExcel = PHPExcel_IOFactory::load('../modelos_excel/orcamentacao.xls');

$locale = 'pt_br';

$validlocale = PHPExcel_Settings::setlocale($locale);

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0,1,iconv('ISO-8859-1', 'UTF-8','METAS VENDAS - ANO '.$ano));

$linha = 1;
$pedidosProcessados = array();
$clientes 			= array();
$cond = null;

$colunas = array(1 => 'B',2 => 'C',3 => 'D',4 => 'F',5 => 'G',6 => 'H',7 => 'K',8 => 'L',9 => 'M',10 => 'O',11 => 'P',12 => 'Q');
$db2 = new banco_dados();
$db->select($sql, 'MYSQL', function($reg, $i) use(&$objPHPExcel, &$coluna, &$colunas, &$db2){
    $linha = $reg['tpOrc'] == 1 ? 16 : 14;
    $coluna = $reg['mes'];
    
    $arrMes = explode('-', $reg['data']);
    
    $dataTermino = $reg['data'].'-'.cal_days_in_month(CAL_GREGORIAN, $arrMes[1], $arrMes[0]);
    
    $objPHPExcel->getActiveSheet()->setCellValue($colunas[$coluna].$linha,iconv('ISO-8859-1', 'UTF-8',$reg['valorTotal']));
    $objPHPExcel->getActiveSheet()->setCellValue($colunas[$coluna].'20',iconv('ISO-8859-1', 'UTF-8',$reg['nPropostas']));
    $objPHPExcel->getActiveSheet()->setCellValue($colunas[$coluna].'22',iconv('ISO-8859-1', 'UTF-8','='.$colunas[$coluna].'18-'.$colunas[$coluna].'10'));
    
    //Buscando o saldo ate o mes selecionado
    $sql = "SELECT SUM(saldo) saldo, tp_orc_protheus
FROM (
	SELECT
		a.valor_pedido - IFNULL(sum(d.valor_medido), 0) as saldo, data, tp_orc_protheus
	FROM
		".DATABASE.".bms_pedido a
		JOIN ".DATABASE.".OS b on b.OS = a.os AND b.reg_del = 0 AND b.id_os_status IN(1,2,7,14,15,16)
		JOIN ".DATABASE.".bms_item c on c.id_bms_pedido = a.id_bms_pedido AND c.reg_del = 0
		LEFT JOIN ".DATABASE.".bms_medicao d on d.id_bms_item = c.id_bms_item AND d.reg_del = 0 AND data <= '".$dataTermino."'
		JOIN ".DATABASE.".empresas e ON e.id_empresa_erp = b.id_empresa_erp AND e.reg_del = 0
	WHERE
		a.reg_del = 0  AND reembolso_despesa = 0 AND (a.data_pedido > '2017-01-01' OR a.os IN(SELECT os FROM ".DATABASE.".bms_excecoes WHERE bms_excecoes.reg_del = 0))
	GROUP BY
		a.os
	HAVING saldo > 0.00
) saldo
GROUP BY tp_orc_protheus";
    
    $db2->select($sql, 'MYSQL', true);
    
    $linhaAux = array(1 => 30, 2 => 28);
    foreach($db2->array_select as $res)
    {
        $objPHPExcel->getActiveSheet()->setCellValue($colunas[$coluna].$linhaAux[$res['tp_orc_protheus']],iconv('ISO-8859-1', 'UTF-8',$res['saldo']));
    }
});

$arrCabecalho = array();
//Fazendo o cabecalho
$sql = "SELECT * FROM ".DATABASE.".bms_previsao_vendas WHERE reg_del = 0 AND ano = ".$ano." AND confirmado = 1";
$db->select($sql, 'MYSQL', function($reg) use(&$arrCabecalho, &$objPHPExcel){
    $linha = $reg['tp_orcamento'] == 1 ? 8 : 6;
    $coluna = 1;
    
    //Janeiro, fevereiro e marco
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna,$linha,iconv('ISO-8859-1', 'UTF-8',$reg['val_janeiro']));
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+1,$linha,iconv('ISO-8859-1', 'UTF-8',$reg['val_fevereiro']));
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+2,$linha,iconv('ISO-8859-1', 'UTF-8',$reg['val_marco']));
    
    //Abril, maio e junho
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+4,$linha,iconv('ISO-8859-1', 'UTF-8',$reg['val_abril']));
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+5,$linha,iconv('ISO-8859-1', 'UTF-8',$reg['val_maio']));
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+6,$linha,iconv('ISO-8859-1', 'UTF-8',$reg['val_junho']));
    
    //Julho, agosto e setembro
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+9,$linha,iconv('ISO-8859-1', 'UTF-8',$reg['val_julho']));
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+10,$linha,iconv('ISO-8859-1', 'UTF-8',$reg['val_agosto']));
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+11,$linha,iconv('ISO-8859-1', 'UTF-8',$reg['val_setembro']));
    
    //Outubro, novembro e dezembro
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+13,$linha,iconv('ISO-8859-1', 'UTF-8',$reg['val_outubro']));
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+14,$linha,iconv('ISO-8859-1', 'UTF-8',$reg['val_novembro']));
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+15,$linha,iconv('ISO-8859-1', 'UTF-8',$reg['val_dezembro']));
});

if ($db->numero_registros == 0)
{
    exit('<script>alert("N�o foram encontrados valores para gerar a planilha, por favor, verifique se j� � necess�rio confirmar as altera��es");history.back(-1);</script>');
}

$tmpName = md5(date('YmdHis')).'.xlsx';

header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header("Content-Disposition: attachment;filename={$tmpName}");
header("Cache-Control: max-age=0");

$objWriter->save('php://output');

exit();
?>