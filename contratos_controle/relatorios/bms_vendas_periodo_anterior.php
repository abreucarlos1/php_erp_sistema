<?php
/*
	Relatório de vendas no período selecionado
	Criado por Carlos Eduardo  
	
	Obs.: Este arquivo é chamado de contratos_controle/bms.php
	
	Versão 0 --> VERSÃO INICIAL : 25/06/2015
	Versão 1 --> Inclusão dos campos reg_del nas consultas - 17/11/2017 - Carlos Abreu
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
    bms_pedido.id_os, ordem_servico.os, valor_pedido, data_pedido data, numero_item, descricao, quantidade, valor, id_bms_pedido, condicao_pgto, id_empresa, empresa, formato
  FROM
  	(SELECT DISTINCT id_bms_pedido, data_pedido, valor_pedido, os, condicao_pgto FROM ".DATABASE.".bms_pedido WHERE bms_pedido.reg_del = 0 ".$clausulaOs.") bms_pedido
    LEFT JOIN (SELECT DISTINCT id_bms_pedido codPedido, id_bms_item, id_bms_controle, numero_item, descricao, quantidade, valor, id_unidade FROM ".DATABASE.".bms_item WHERE bms_item.reg_del = 0) bms_item
      ON codPedido = id_bms_pedido
    JOIN (SELECT id_os, id_empresa, os FROM ".DATABASE.".ordem_servico WHERE ordem_servico.reg_del = 0) os ON os.id_os = bms_pedido.id_os 
	LEFT JOIN (SELECT id_formato, codigo_formato, formato FROM ".DATABASE.".formatos WHERE formatos.reg_del = 0) formatos ON bms_item.id_unidade = id_formato  
  	JOIN ( SELECT empresa, id_empresa codEmp, id_unidade FROM ".DATABASE.".empresas WHERE empresas.reg_del = 0 JOIN ".DATABASE.".unidades ON id_unidade = id_unidade AND unidades.reg_del = 0) empresas ON CodEmp = OS.id_empresa 
    LEFT JOIN (SELECT DISTINCT id_bms_medicao, id_bms_item codItem, valor_medido, valor_saldo, valor_planejado, data FROM ".DATABASE.".bms_medicao WHERE bms_medicao.reg_del = 0) bms_medicao ON bms_item.id_bms_item = codItem
WHERE
	data_pedido >= '{$dataRelatorio}' ".$complDataFim."
GROUP BY
  bms_pedido.id_os, numero_item, id_bms_pedido
ORDER BY
	OS.id_empresa, bms_pedido.id_os desc";

$objPHPExcel = PHPExcel_IOFactory::load('../modelos_excel/bms_vendas_periodo.xlsx');

$locale = 'pt_br';

$validlocale = PHPExcel_Settings::setlocale($locale);

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

$arrCols = array('A', 'B', 'C', 'D', 'E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T');

$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4,1,$_GET['data']);
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7,1,date('d/m/Y'));

$linha = 1;
$pedidosProcessados = array();
$clientes 			= array();
$cond = null;

$db->select($sql, 'MYSQL',
	function($reg, $i) use(&$objPHPExcel, &$linha, &$pedidosProcessados, &$cond, &$clientes)
	{
		$linha = $i + 4;
		
		/*
		$sql = "SELECT DISTINCT E4_DESCRI, E4_COND, E4_CODIGO FROM SE4010 WHERE D_E_L_E_T_ = '' AND E4_CODIGO = '{$reg['condicao_pgto']}'";
		$db2 = new banco_dados();
		$cond = $db2->select($sql, 'MSSQL',
					function($regs, $i){
						return trim($regs['E4_DESCRI']);
					}
				);
		$cond = isset($cond[0]) ? $cond[0] : '';
		*/
		
		$clientes[$reg['id_empresa']]["A{$linha}"] = "A{$linha}";
		$qtdClientes = count($clientes[$reg['id_empresa']]);
		
		if ($qtdClientes == 1)
		{
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0,$linha,$reg['empresa']);
		}
				
		//Saberemos quantas vezes cada pedido foi processado
		$pedidosProcessados[$reg['id_bms_pedido']]["H{$linha}"] = "H{$linha}";
		$qtd = count($pedidosProcessados[$reg['id_bms_pedido']]);

		if ($qtd == 1)
		{
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1,$linha,$reg['OS']);
		}
		
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2,$linha,trim($reg['numero_item']));
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3,$linha,trim($reg['descricao']));
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4,$linha,trim($reg['quantidade']));
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5,$linha,trim($reg['formato']));
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6,$linha,trim($reg['valor']));
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8,$linha,$cond);
				
		if ($qtd == 1)
		{
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7,$linha,trim($reg['valor_pedido']));
		}
	}
);

foreach($pedidosProcessados as $idBmsPedido => $pedido)
{
	if (count($pedido) > 1)
	{
		$primeiro 	= array_shift($pedido);
		$ultimo 	= array_pop($pedido);
		
		$objPHPExcel->getActiveSheet()->mergeCells(str_replace('H', 'B', "{$primeiro}:{$ultimo}"));
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
		
		$objPHPExcel->getActiveSheet()->mergeCells("{$primeiro}:{$ultimo}");
		$style = array(
	        'alignment' => array(
	            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
	        )
	    );

    	$objPHPExcel->getActiveSheet()->getStyle($primeiro)->applyFromArray($style);
	}
}

$ultimaLinha = $linha;
$linha+=2;

$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7,$linha,"=SUM(H4:H{$ultimaLinha})");

$tmpName = md5(date('YmdHis')).'.xlsx';

header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header("Content-Disposition: attachment;filename={$tmpName}");
header("Cache-Control: max-age=0");

$objWriter->save('php://output');

exit();
?>