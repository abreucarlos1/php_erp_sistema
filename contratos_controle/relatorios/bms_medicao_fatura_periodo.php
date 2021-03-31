<?php
/*
	Relatório de vendas no período selecionado
	Criado por Carlos Eduardo  
	
	Obs.: Este arquivo é chamado de contratos_controle/bms.php
	
	Versão 0 --> VERSÃO INICIAL : 27/07/2015
	Versão 1 --> Inclusão dos campos reg_del nas consultas - 17/11/2017 - Carlos Abreu
	Versão 2 --> Inclusão do campo reembolso de despesa - 08/03/2018 - Carlos Eduardo
*/
require_once(implode(DIRECTORY_SEPARATOR,array('..','..','config.inc.php')));
	
require_once(INCLUDE_DIR."PHPExcel/Classes/PHPExcel.php");

$db = new banco_dados();

$dataRelatorio = php_mysql($_GET['data']);

$dataRelatorio2 = php_mysql($_GET['data_fim']);

$id_solicitacao_documento = php_mysql($_GET['id_solicitacao_documento']);

$clausulaOs = !empty($id_solicitacao_documento) ? "AND c.id_bms_pedido = {$id_solicitacao_documento}" : '';

if (isset($_GET['tipoRelatorio']) && !empty($_GET['tipoRelatorio']))
{
    switch($_GET['tipoRelatorio'])
    {
        case 0:
            $clausulaFatura = '';
        break;
        case 1:
            $clausulaFatura = "AND a.numero_nf IS NULL OR TRIM(a.numero_nf) = ''";            
        break;
        case 2:
            //As vezes as notas são canceladas
            $clausulaFatura = "AND a.numero_nf IS NOT NULL AND lower(a.numero_nf) NOT IN('cancelado','cancelada','excluido','excluida','excluída','excluído')";
        break;
        case 3:
            //Somente adm
            $clausulaFatura = "AND tp_orc_protheus = 1";
        break;
        case 4:
            //Somente projeto
            $clausulaFatura = "AND tp_orc_protheus = 2";
        break;
    }
}

$order = 'f.empresa, g.unidade, c.os, b.numero_item';

if (isset($_GET['par_extra']) && !empty($_GET['par_extra']))
{
	$order = "e.funcionario, f.empresa, g.unidade, c.os, b.numero_item";
}

$clausulaData = !empty($dataRelatorio) ? "AND a.data >= '{$dataRelatorio}'" : '';
$clausulaData .= !empty($dataRelatorio2) ? "AND a.data <= '{$dataRelatorio2}'" : '';

$sql = "
SELECT
	c.os, sum(a.valor_planejado) valor_planejado, sum(a.valor_planejado)-sum(a.valor_medido) valor_saldo,
    max(a.id_bms_controle) id_bms_controle, sum(a.valor_medido) valor_medido, c.id_bms_pedido,
    sum(a.quantidade_medida) quantidade_medida, max(a.numero_nf) numero_nf, b.numero_item,
    d.id_cod_coord, e.funcionario, d.id_empresa, g.unidade, f.empresa, h.formato, c.condicao_pgto, b.descricao,
    tp_orc_protheus
FROM 
	".DATABASE.".bms_medicao a
    JOIN ".DATABASE.".bms_item b ON b.reg_del = 0 AND b.id_bms_item = a.id_bms_item
    JOIN ".DATABASE.".bms_pedido c ON c.reg_del = 0 AND c.id_bms_pedido = b.id_bms_pedido AND reembolso_despesa = 0
    JOIN ".DATABASE.".ordem_servico d ON d.os = c.os
    LEFT JOIN ".DATABASE.".funcionarios e ON e.situacao NOT IN('DESLIGADO') AND e.id_funcionario = d.id_cod_coord AND e.reg_del = 0 
    JOIN ".DATABASE.".empresas f ON f.id_empresa = d.id_empresa AND f.reg_del = 0 
    JOIN ".DATABASE.".unidades g ON g.id_unidade = f.id_unidade AND g.reg_del = 0 
    LEFT JOIN ".DATABASE.".formatos h ON h.id_formato = b.id_unidade AND h.reg_del = 0
WHERE a.reg_del = 0
	AND a.valor_medido > 0 AND a.data > '2017-01-01' ".$clausulaData." ".$clausulaOs." ".$clausulaFatura."
GROUP BY
	c.os, b.numero_item, a.data
ORDER BY {$order}";

$objPHPExcel = PHPExcel_IOFactory::load('../modelos_excel/bms_medicao_fatura_periodo.xls');

$locale = 'pt_br';

$validlocale = PHPExcel_Settings::setlocale($locale);

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

$arrCols = array('A', 'B', 'C', 'D', 'E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T');

$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6,2,$_GET['data']);
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8,2,$_GET['data_fim']);

$linha = 1;
$pedidosProcessados = array();
$coordenadoresProcessados = array();
$clientes 			= array();
$cond = null;
$db2 = new banco_dados();
$db->select($sql, 'MYSQL',
	function($reg, $i) use(&$objPHPExcel, &$linha, &$pedidosProcessados, &$cond, &$db2, &$clientes, &$coordenadoresProcessados)
	{
		$linCli= $i + 2;
		$linha = $i + 4;

		//Se for o relatório por clientes então guarda informações para mesclar as células
		if (empty($_GET['par_extra']))
			$clientes[trim($reg['empresa'])]["A{$linha}"] = "A{$linha}";
		
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0,$linha,$reg['empresa'].' - '.$reg['unidade']);
		
		//Saberemos quantas vezes cada pedido foi processado
		$pedidosProcessados[$reg['id_bms_pedido']]["F{$linha}"] = "F{$linha}";
		$qtd = count($pedidosProcessados[$reg['id_bms_pedido']]);

		$reg['funcionario'] = trim($reg['funcionario']) == '' ? 'SEM COORDENADOR CADASTRADO' : $reg['funcionario'];
		if ($qtd == 1)
		{
		}
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1,$linha,trim($reg['funcionario']));
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2,$linha,$reg['os']);
		
		$valorFaturado  = trim($reg['numero_nf']) != '' ? trim($reg['valor_medido']) : '';
		$valorMedido  	= in_array($reg['id_bms_controle'], array(2,5,3)) ? trim($reg['valor_medido']) : '';
		$valorPlanejado = trim($reg['valor_planejado']);
		$valorSaldo 	= trim($reg['valor_saldo']);

		$i = 3;
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($i++,$linha,trim($reg['numero_item']));
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($i++,$linha,trim($reg['descricao']));
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($i++,$linha,trim($reg['quantidade_medida']));
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($i++,$linha,trim($reg['formato']));
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($i++,$linha,$valorMedido);
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($i++,$linha,$reg['numero_nf']);
	}
);

foreach($pedidosProcessados as $idBmsPedido => $pedido)
{
	if (count($pedido) > 1)
	{
		$primeiro 	= array_shift($pedido);
		$ultimo 	= array_pop($pedido);
		
		$objPHPExcel->getActiveSheet()->mergeCells(str_replace('F', 'C', "{$primeiro}:{$ultimo}"));
		$objPHPExcel->getActiveSheet()->mergeCells(str_replace('F', 'B', "{$primeiro}:{$ultimo}"));
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


$styleArray = array(
  'borders' => array(
    'allborders' => array(
      'style' => PHPExcel_Style_Border::BORDER_THIN
    )
  )
);

$objPHPExcel->getActiveSheet()->getStyle('A4:I'.$linha++)->applyFromArray($styleArray);

//Colocando o Total Geral
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6,$linha+1,'TOTAL');
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7,$linha+1,'=SUM(H4:H'.($linha-1).')');

$ultimaLinha = $linha;
$linha+=2;

$tmpName = md5(date('YmdHis')).'.xls';

header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header("Content-Disposition: attachment;filename={$tmpName}");
header("Cache-Control: max-age=0");

$objWriter->save('php://output');

exit();
?>