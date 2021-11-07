<?php
/*
        Boletim de medição em excel
        Criado por Carlos
        Versão 0 --> VERSÃO INICIAL : 10/06/2015
        Versão 1 --> Alterações nas colunas e calculos - 04/10/2017
        Versão 2 --> Inclusão dos campos reg_del nas consultas - 17/11/2017 - Carlos Abreu
 */
require_once(implode(DIRECTORY_SEPARATOR,array('..','..','config.inc.php')));

require_once(INCLUDE_DIR."PHPExcel/Classes/PHPExcel.php");

if (!isset($_GET['id_solicitacao_documento']) || !isset($_GET['data']))
{
    exit('<script>alert("Por favor, selecione um pedido e digite uma data para gerar o BMS");window.close();</script>');
}

$db = new banco_dados();

$id_bms_pedido = $_GET['id_solicitacao_documento'];

$data_medicao = $_GET['data'];

$data_fim_medicao = !empty($_GET['data_fim_bms']) ? $_GET['data_fim_bms'] : $_GET['data'];

$clausulaPedido = '';

if (!empty($id_bms_pedido))
{
    $clausulaPedido = "AND p.id_bms_pedido = '".$id_bms_pedido."'";
}

$sql =
"SELECT * FROM
  ".DATABASE.".bms_pedido p
	JOIN (SELECT descricao, id_os as osCod, os as osNum, id_empresa as clientecodigo FROM ".DATABASE.".ordem_servico WHERE OS.reg_del = 0) AS os ON osNum = os
	JOIN (SELECT empresa, id_empresa FROM ".DATABASE.".empresas WHERE reg_del = 0) empresas ON empresas.id_empresa = clientecodigo
	JOIN (SELECT id_bms_item, numero_item, descricao as descItem, quantidade, id_unidade, valor, id_bms_pedido, data_item FROM ".DATABASE.".bms_item WHERE bms_item.reg_del = 0) i ON i.id_bms_pedido = p.id_bms_pedido
	JOIN (SELECT id_bms_item idItemMedido, id_bms_medicao, data_status, valor_planejado, valor_medido, id_bms_controle, progresso_medido, quantidade_planejada, quantidade_medida, quantidade_diferenca, data FROM ".DATABASE.".bms_medicao WHERE bms_medicao.reg_del = 0 AND bms_medicao.data BETWEEN '".$data_medicao."' AND '".$data_fim_medicao."' AND id_bms_controle IN(2,5)) m ON idItemMedido = i.id_bms_item
	JOIN (SELECT id_formato, formato FROM ".DATABASE.".formatos WHERE formatos.reg_del = 0) formato ON id_formato = id_unidade
WHERE p.reg_del = 0
{$clausulaPedido}
ORDER BY id_bms_item";

$cabecalho 	= array();
$pedidos 	= array();

$db->select($sql, 'MYSQL',
    function($reg, $i) use(&$cabecalho, &$pedidos, &$data_medicao, &$numBms)
    {
        $data = str_replace('/', '-', mysql_php($reg['data']));
        if (!isset($cabecalho['descricao']))
        {
            $cabecalho[$reg['os'].'_'.$data]['descricao'] 						= sprintf('%06d', $reg['osNum']).' - '.$reg['descricao'];
            $cabecalho[$reg['os'].'_'.$data]['data_pedido']						= $reg['data_pedido'];
            $cabecalho[$reg['os'].'_'.$data]['data_termino']					= $reg['data_termino'];
            $cabecalho[$reg['os'].'_'.$data]['ref_cliente']						= $reg['ref_cliente'];
            $cabecalho[$reg['os'].'_'.$data]['data_medicao']					= $data;
            $cabecalho[$reg['os'].'_'.$data]['valor_pedido']					= $reg['valor_pedido'];
            $cabecalho[$reg['os'].'_'.$data]['empresa']							= $reg['empresa'];
        }
        
        $pedidos[$reg['os'].'_'.$data][$reg['numero_item']]['descItem'] 			= $reg['descItem'];
        $pedidos[$reg['os'].'_'.$data][$reg['numero_item']]['quantidade'] 			= $reg['quantidade'];
        $pedidos[$reg['os'].'_'.$data][$reg['numero_item']]['valor'] 				= $reg['valor'];
        $pedidos[$reg['os'].'_'.$data][$reg['numero_item']]['valor_medido'] 		= $reg['valor_medido'];
        $pedidos[$reg['os'].'_'.$data][$reg['numero_item']]['id_bms_medicao'] 		= $reg['id_bms_medicao'];
        $pedidos[$reg['os'].'_'.$data][$reg['numero_item']]['quantidade_planejada'] = $reg['quantidade_planejada'];
        $pedidos[$reg['os'].'_'.$data][$reg['numero_item']]['quantidade_medida'] 	= $reg['quantidade_medida'];
        $pedidos[$reg['os'].'_'.$data][$reg['numero_item']]['unidade'] 				= $reg['formato'];
        
        $db2 = new banco_dados();
        
        //Pegando os valores do mês passado para cada item
        $sql = "SELECT data, progresso_medido, id_bms_medicao, id_bms_controle, SUM(valor_medido) valor_medido, valor_saldo, valor_diferenca, quantidade_planejada, SUM(quantidade_medida) quantidade_medida, quantidade_diferenca FROM ".DATABASE.".bms_medicao WHERE id_bms_item = ".$reg['idItemMedido']." AND data < '".$data_medicao."' AND reg_del = 0 ORDER BY data DESC LIMIT 0, 1";
        $db2->select($sql, 'MYSQL',
            function($reg1, $k) use(&$pedidos, &$reg, &$db2, $data)
            {
                $pedidos[$reg['os'].'_'.$data][$reg['numero_item']]['valor_anterior'] 					= $reg1['valor_medido'];
                $pedidos[$reg['os'].'_'.$data][$reg['numero_item']]['quantidade_plajejada_anterior'] 	= $reg1['quantidade_planejada'];
                $pedidos[$reg['os'].'_'.$data][$reg['numero_item']]['quantidade_medida_anterior'] 		= $reg1['quantidade_medida'];
                $pedidos[$reg['os'].'_'.$data][$reg['numero_item']]['diferenca_quantidade_anterior'] 	= $reg1['diferenca_quantidade'];
                $pedidos[$reg['os'].'_'.$data][$reg['numero_item']]['medicao_anterior'] 				= $reg1['data'];
        });
    });

$objPHPExcel = PHPExcel_IOFactory::load("../modelos_excel/bms_modelo_v4.xls");

$locale = 'pt_br';

$validlocale = PHPExcel_Settings::setlocale($locale);

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

$sheetIndex = 0;

$A = $objPHPExcel->getActiveSheet();

foreach ($pedidos as $chave=>$valor)
{
    $chave = explode('_', $chave);
    
    if($sheetIndex==0)
    {
        $objPHPExcel->getActiveSheet()->setTitle("{$chave[0]} {$chave[1]}");
    }
    else
    {
        //copia a folha
        $B = clone $A;
        
        $B->setTitle("{$chave[0]} {$chave[1]}");
        
        $objPHPExcel->addSheet($B,$sheetIndex);
    }
    
    $sheetIndex++;
}

$sheetIndex = 0;

foreach($pedidos as $id_solicitacao_documento => $itensPedido)
{
    $objPHPExcel->setActiveSheetIndex($sheetIndex);
    
    $linha = 10;
    $arrCols = array('A', 'B', 'C', 'D', 'E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T');
    
    //Cabeçalho
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8,2,$cabecalho[$id_solicitacao_documento]['empresa']);
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7,4,strtoupper($cabecalho[$id_solicitacao_documento]['descricao']));
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(14,2,str_replace('-', '/', $cabecalho[$id_solicitacao_documento]['data_medicao']));
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(14,3,mysql_php($cabecalho[$id_solicitacao_documento]['data_pedido']));
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8,3,$cabecalho[$id_solicitacao_documento]['ref_cliente']);
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(14,4,$cabecalho[$id_solicitacao_documento]['valor_pedido']);
    //$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(13,3,iconv('ISO-8859-1', 'UTF-8',$numBms[0]));//Por enquanto não vamos utilizar o número de bms's gerados
    
    //solicitacao_documentos
    foreach($itensPedido as $codPedido => $pedido)
    {
        $qtdAvanco = 0;
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1,$linha,$codPedido);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2,$linha,$pedido['descItem']);
        //$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8,$linha,iconv('ISO-8859-1', 'UTF-8',$pedido['quantidade_medida']/$pedido['valor']*100));
        
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(12,$linha,$pedido['valor']);
        
        //Se houver medição anterior
        if (isset($pedido['valor_anterior']))
        {
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(13,$linha,$pedido['valor_anterior']);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8,$linha,$pedido['quantidade_medida_anterior']);
            $qtdAvanco += $pedido['quantidade_medida_anterior'];
        }
        
        if ($pedido['valor'] == 0.00)
        {
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7,$linha,'');
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8,$linha,'');
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(9,$linha,'');
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(16,$linha,'');
        }
        
        $qtdAvanco += $pedido['quantidade_medida'];
        
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7,$linha,$pedido['unidade']);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(9,$linha,$pedido['quantidade_medida']);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(10,$linha,$qtdAvanco);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(11,$linha,$pedido['quantidade']-$qtdAvanco);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(14,$linha,$pedido['valor_medido']);
        
        $linha++;
    }
    
    $sheetIndex++;
}

$tmpName = md5(date('YmdHis')).'.xls';

header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header("Content-Disposition: attachment;filename={$tmpName}");
header("Cache-Control: max-age=0");

$objWriter->save('php://output');

exit();
?>