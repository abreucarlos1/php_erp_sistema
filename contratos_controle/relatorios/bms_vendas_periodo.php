<?php
/*
	Relatório de vendas no período selecionado
	Criado por Carlos 
	
	Obs.: Este arquivo é chamado de contratos_controle/bms.php
	
	Versão 0 --> VERSÃO INICIAL : 25/06/2015
	Versão 1 --> Inclusão dos campos reg_del nas consultas - 17/11/2017 - Carlos Abreu
	Versão 2 --> Inclusão do campo reembolso de despesa - 08/03/2018 - Carlos
*/

require_once(implode(DIRECTORY_SEPARATOR,array('..','..','config.inc.php')));

require_once(INCLUDE_DIR."PHPExcel/Classes/PHPExcel.php");

if (empty($_GET['data']))
{
	exit('<script>alert("Por favor, digite uma data para gerar o Relatório");window.close();</script>');
}

if (!empty($_GET['data_fim']))
{
	$complDataFim = "AND data_pedido <= '".php_mysql($_GET['data_fim'])."'";
}

$clausulaOs = !empty($_GET['id_solicitacao_documento']) ? "AND id_bms_pedido = {$_GET['id_solicitacao_documento']}" : '';

$db = new banco_dados();

$dataRelatorio = php_mysql($_GET['data']);

$sql = 
"SELECT
    bms_pedido.id_os, ordem_servico.os, valor_pedido, data_pedido data, id_bms_pedido, condicao_pgto, id_empresa, empresa, id_cliente_protheus, bms_pedido.id_loja_protheus, bairro,
    bms_pedido.tp_orc_protheus
  FROM
  	(SELECT DISTINCT id_bms_pedido, data_pedido, valor_pedido, os, condicao_pgto, id_cliente_protheus, id_loja_protheus, tp_orc_protheus FROM ".DATABASE.".bms_pedido WHERE bms_pedido.reg_del = 0 AND reembolso_despesa = 0) bms_pedido
    LEFT JOIN (SELECT id_os, id_empresa, os FROM ".DATABASE.".ordem_servico WHERE ordem_servico.reg_del = 0) os ON os.os = bms_pedido.id_os 
    LEFT JOIN (SELECT empresa, id_empresa codEmp, id_cod_protheus, id_loja_protheus, bairro FROM ".DATABASE.".empresas WHERE empresas.reg_del = 0) empresas 
    ON id_cod_protheus = id_cliente_protheus AND empresas.id_loja_protheus = bms_pedido.id_loja_protheus 
WHERE
	data_pedido >= '{$dataRelatorio}' ".$complDataFim."
GROUP BY
  bms_pedido.id_os, id_bms_pedido
ORDER BY
	bms_pedido.id_cliente_protheus, bms_pedido.id_loja_protheus, bms_pedido.id_os";

$objPHPExcel = PHPExcel_IOFactory::load('../modelos_excel/bms_vendas_periodo.xls');

$locale = 'pt_br';

$validlocale = PHPExcel_Settings::setlocale($locale);

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

$arrCols = array('A', 'B', 'C', 'D', 'E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T');

$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2,1,$_GET['data']);
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4,1,$_GET['data_fim']);

$linha = 1;
$pedidosProcessados = array();
$clientes 			= array();
$cond = null;

$db->select($sql, 'MYSQL',
	function($reg, $i) use(&$objPHPExcel, &$linha, &$pedidosProcessados, &$cond, &$clientes)
	{
		$linha = $i + 4;
		
		$clientes[$reg['id_cliente_protheus'].$reg['id_loja_protheus']]["A{$linha}"] = "A{$linha}";
		$qtdClientes = count($clientes[$reg['id_cliente_protheus'].$reg['id_loja_protheus']]);
		
		if ($qtdClientes == 1)
		{
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0,$linha,$reg['empresa'].'-'.$reg['bairro']);
		}
				
		//Saberemos quantas vezes cada pedido foi processado
		$pedidosProcessados[$reg['id_bms_pedido']]["H{$linha}"] = "H{$linha}";
		$qtd = count($pedidosProcessados[$reg['id_bms_pedido']]);

		if ($qtd == 1)
		{
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1,$linha,$reg['os']);
		}
		
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6,$linha,trim($reg['valor']));

		if ($qtd == 1)
		{
			if ($reg['tp_orc_protheus'] == 1)
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2,$linha,trim($reg['valor_pedido']));
			else
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3,$linha,trim($reg['valor_pedido']));
		}
	}
);

foreach($pedidosProcessados as $idBmsPedido => $pedido)
{
	if (count($pedido) > 1)
	{
		$primeiro 	= array_shift($pedido);
		$ultimo 	= array_pop($pedido);
		
		$objPHPExcel->getActiveSheet()->mergeCells(str_replace('H', 'C', "{$primeiro}:{$ultimo}"));
		$objPHPExcel->getActiveSheet()->mergeCells(str_replace('H', 'I', "{$primeiro}:{$ultimo}"));
		
		$objPHPExcel->getActiveSheet()->mergeCells("{$primeiro}:{$ultimo}");
	}
}

foreach($clientes as $idCliente => $nomes)
{
	if (count($nomes) > 1)
	{
		$primeiro 	= array_shift($nomes);
		$ultimo 	= array_pop($nomes);
		
		//Mesclando o cliente Coluna A
		$objPHPExcel->getActiveSheet()->mergeCells("{$primeiro}:{$ultimo}");
		
		
		$style = array(
	        'alignment' => array(
	            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
	        )
	    );
	    
		//Mesclando o total do cliente Coluna E
		$objPHPExcel->getActiveSheet()->mergeCells(str_replace('A', 'E', "{$primeiro}:{$ultimo}"));
		//Colocando o Total na coluna E
		$objPHPExcel->getActiveSheet()->setCellValue(str_replace('A', 'E', $primeiro),"=SUM(".str_replace('A', 'C', $primeiro).':'.str_replace('A', 'D', $ultimo).")");

    	$objPHPExcel->getActiveSheet()->getStyle($primeiro)->applyFromArray($style);
	}
	else
	{
		$primeiro 	= array_shift($nomes);
		$objPHPExcel->getActiveSheet()->setCellValue(str_replace('A', 'E', $primeiro),"=".str_replace('A', 'C', $primeiro).'+'.str_replace('A', 'D', $primeiro));
	}
}

$ultimaLinha = $linha;
$linha+=2;

$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2,$linha,"=SUM(C4:C{$ultimaLinha})");
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3,$linha,"=SUM(D4:D{$ultimaLinha})");
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4,$linha,"=SUM(E4:E{$ultimaLinha})");

$tmpName = md5(date('YmdHis')).'.xls';

header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header("Content-Disposition: attachment;filename={$tmpName}");
header("Cache-Control: max-age=0");

$objWriter->save('php://output');

exit();
?>