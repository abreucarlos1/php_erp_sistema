<?php
/*
		Relatorio Avaliação
		
		Criado por Carlos
		
		local/Nome do arquivo:
		../rh/relatorios/rel_avaliacao_excel.php
		
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

$clausulasetores = '';
if (isset($_POST['selsetores']) && count($_POST['selsetores']) > 0)
{
	$clausulasetores = 'AND id_setor_aso IN('.implode(',',$_POST['selsetores']).')';
}

$clausulaAvaliacao = '';
if (isset($_POST['selAvaliacoes']) && !empty($_POST['selAvaliacoes']))
{
	$clausulaAvaliacao = 'AND avf_ava_id IN('.$_POST['selAvaliacoes'].')';//Caso algum dia seja necessário selecionar várias avaliações, incrementar aqui.
}

$sql = "SELECT
  avf_sup_id, avf_sub_id, avf_ava_id, avf_nota, avf_bqp_id, funcs.*, perguntas.*, setor_aso.*, funcoes.*, grupos.*
FROM
  ".DATABASE.".avaliacoes_funcionarios
  JOIN(
    SELECT id_funcionario, funcionario, id_setor_aso, id_funcao FROM ".DATABASE.".funcionarios WHERE funcionarios.reg_del = 0
  ) funcs
  ON id_funcionario = avf_sub_id
  JOIN(
    SELECT bqp_texto, bqp_bqg_id, bqp_id FROM ".DATABASE.".banco_questoes_perguntas WHERE reg_del = 0
  ) perguntas
  ON bqp_id = avf_bqp_id
  JOIN(
    SELECT bqg_titulo, bqg_id FROM ".DATABASE.".banco_questoes_grupos WHERE reg_del = 0
  ) grupos
  ON bqg_id = bqp_bqg_id
  JOIN(
    SELECT setor_aso, id_setor_aso FROM ".DATABASE.".setor_aso WHERE setor_aso.reg_del = 0 {$clausulasetores}
  ) setor_aso ON setor_aso.id_setor_aso = funcs.id_setor_aso
  JOIN(
    SELECT descricao, id_funcao FROM ".DATABASE.".rh_funcoes WHERE rh_funcoes.reg_del = 0
  ) funcoes
  ON funcoes.id_funcao = funcs.id_funcao
WHERE
  reg_del = 0
  {$clausulaAvaliacao}";

$array_setores 	= array();
$array_funcs	= array();
$array_respostas= array();
$array_perguntas= array();
$array_grupos= array();

//Montando os arrays necessários para a planilha
$db->select($sql, 'MYSQL',
	function ($reg, $i) use(&$array_setores, &$array_funcs, &$array_respostas, &$array_perguntas, &$array_grupos)
	{
		$array_funcs[$reg['id_setor_aso']][$reg['id_funcionario']] = array('nome' =>$reg['funcionario'], 'cargo' => $reg['descricao']);
		$array_setores[$reg['id_setor_aso']] = $reg['setor_aso'];
		$array_perguntas[$reg['bqp_bqg_id']][$reg['avf_bqp_id']] = $reg['bqp_texto'];
		$array_respostas[$reg['id_setor_aso']][$reg['bqp_bqg_id']][$reg['avf_bqp_id']][$reg['id_funcionario']] = $reg['avf_nota'];
		$array_grupos[$reg['bqp_bqg_id']] = $reg['bqg_titulo'];
	}
);

if ($db->numero_registros == 0)
{
	exit('<script>alert("Nenhum registro encontrado!");window.close();</script>');
}

$objPHPExcel = PHPExcel_IOFactory::load("../modelos_excel/avaliacao.xlsx");

$locale = 'pt_br';

$validlocale = PHPExcel_Settings::setlocale($locale);

if (!$validlocale) 
{
	echo 'Unable to set locale to '.$locale." - reverting to en_us<br />\n";
}

//referencia a folha modelo
$A = $objPHPExcel->getActiveSheet();

$sheetIndex = 0;

//renomeia as folhas conforme os escopos
foreach ($array_setores as $chave=>$valor)
{
	if($sheetIndex==0)
	{
		$objPHPExcel->getActiveSheet()->setTitle(substr(tiraacentos($valor),0,25));
	}
	else
	{
		//copia a folha
		$B = clone $A;
		
		$B->setTitle(substr(tiraacentos($valor),0,25));
		
		$objPHPExcel->addSheet($B,$sheetIndex);
	}
	
	$sheetIndex++;
}

$sheetIndex = 0;
$arrCelMediasItens = array();
$arrCelMediassetores = array();

//preenche a planilha
foreach ($array_setores as $chave=>$valor)
{
	//seta a planilha corrente
	$objPHPExcel->setActiveSheetIndex($sheetIndex);
	$planilhaNome = $objPHPExcel->getActiveSheet()->getTitle();
		
	$linha = 2;
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0,$linha, $valor);
	
	//Adicionando os nomes e os setores do colaborador
	$coluna = 1;
	foreach($array_funcs[$chave] as $codFunc=>$arrFunc)
	{
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna,$linha, $arrFunc['nome']);
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna,$linha+1, $arrFunc['cargo']);
		$coluna++;
	}
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna,$linha+1, 'MÉDIA GERAL');
	$coluna++;
	
	$arrCols = array('A', 'B', 'C', 'D', 'E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T');
	$arrCols = array_reverse($arrCols);
	
	$iteracoes = (count($arrCols)-$coluna);
	
	for ($i = 0; $i < $iteracoes; $i++)
	{
		$objPHPExcel->getActiveSheet()->removeColumn("{$arrCols[$i]}",1);
	}
	$arrCols = array_reverse($arrCols);
	
	$objPHPExcel->getActiveSheet()->getStyle('A1:A100')->getAlignment()->setWrapText(true);
	
	$coluna = 0;
	$linha = 4;
	foreach($array_grupos as $grupo=>$titulo)
	{
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna,$linha, $titulo);
		$linha ++;
		foreach($array_perguntas[$grupo] as $per => $pergTexto)
		{
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna,$linha, $pergTexto);
			$objPHPExcel->getActiveSheet()->getRowDimension($linha)->setRowHeight(40);

			$coluna = 1;
			if (isset($array_respostas[$chave][$grupo][$per]))
			{
				foreach($array_respostas[$chave][$grupo][$per] as $func=>$nota)
				{
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna,$linha, $nota);
					$coluna++;
				}
			}
			else
			{
				$coluna++;
			}
			
			//Armazenando das células que contém o cálculo das médias dos itens 
			$arrCelMediasItens[$planilhaNome][$titulo][$coluna][] = $linha;
			
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna,$linha,"=AVERAGE(".$arrCols[1].$linha.":".$arrCols[$coluna-1].$linha.")");
			$coluna = 0;
			
			$linha ++;
		}
		
		$linha++;
	}
	
	$colunaAux = 1;
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0,$linha, "MÉDIA INDIVIDUAL");
		
	foreach($array_funcs[$chave] as $codFunc=>$arrFunc)
	{
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($colunaAux,$linha, "=AVERAGE(".$arrCols[$colunaAux].'4'.":".$arrCols[$colunaAux].($linha-1).")");
		$colunaAux++;
	}
	
	//Armazenando das células que contém o cálculo das médias dos setores 
	$arrCelMediassetores[$planilhaNome] = array('linha' => $linha, 'coluna' => $colunaAux, 'celula' => $planilhaNome.'!'.$arrCols[$colunaAux].$linha);
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($colunaAux,$linha, "=AVERAGE(".$arrCols[$colunaAux].'4'.":".$arrCols[$colunaAux].($linha-1).")");
	
	$sheetIndex++;
}
//Por enquanto os gráficos não estão funcionando
/*
//Gráfico
$dataseriesLabels1 = array(new PHPExcel_Chart_DataSeriesValues('String', 'CIVIL!$A$2', NULL, 1));

$xAxisTickValues1 = array(new PHPExcel_Chart_DataSeriesValues('String', 'CIVIL!$B$3:$D$3', NULL, 4));

$dataSeriesValues1 = array(new PHPExcel_Chart_DataSeriesValues('Number', 'CIVIL!$B$4:$D$6', NULL, 4));

$series1 = new PHPExcel_Chart_DataSeries(
	PHPExcel_Chart_DataSeries::TYPE_PIECHART,	// plotType
	PHPExcel_Chart_DataSeries::GROUPING_CLUSTERED,	// plotGrouping
	range(0, count($dataSeriesValues1)-1),	// plotOrder
	$dataseriesLabels1,	// plotLabel
	$xAxisTickValues1,	// plotCategory
	$dataSeriesValues1	// plotValues
);

$layout1 = new PHPExcel_Chart_Layout();
$layout1->setShowVal(true);
$layout1->setShowPercent(true);
$layout1->setShowCatName(true);

$plotarea1 = new PHPExcel_Chart_PlotArea($layout1, array($series1));
$legend1 = new PHPExcel_Chart_Legend(PHPExcel_Chart_Legend::POSITION_RIGHT, NULL, true);
$title1 = new PHPExcel_Chart_Title('teste');

// Create the chart
$chart1 = new PHPExcel_Chart(
	'grafico',	// name
	$title1,	// title
	$legend1,	// legend
	$plotarea1,	// plotArea
	true,	// plotVisibleOnly
	0,	// displayBlanksAs
	NULL,	// xAxisLabel
	NULL	// yAxisLabel - Pie charts don't have a Y-Axis
);

// Set the position where the chart should appear in the worksheet
$chart1->setTopLeftPosition('A33');
$chart1->setBottomRightPosition('D47');

// Add the chart to the worksheet
$objPHPExcel->getActiveSheet()->addChart($chart1);
*/

/*
 * Planilha de totais por setor 
 */
$objPHPExcel->createSheet($sheetIndex);
$objPHPExcel->setActiveSheetIndex($sheetIndex);
$objPHPExcel->getActiveSheet()->setTitle('MEDIA SETORES');

$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0,2,'SETOR');
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1,2,'MEDIA');

$linha = 3;
$coluna = 0;
foreach($arrCelMediassetores as $setor => $celulas)
{
	$col = $celulas['coluna'];
	$lin = $celulas['linha'];
	$cel = $celulas['celula'];
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna,$linha,$setor);
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+1,$linha,"=".$cel);
	$linha++;
}
$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);

$sheetIndex++;
/*
 * Planilha de totais por item 
 */
$objPHPExcel->createSheet($sheetIndex);
$objPHPExcel->setActiveSheetIndex($sheetIndex);
$objPHPExcel->getActiveSheet()->setTitle('MEDIA ITENS');

$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0,2,'ITEM');
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1,2,'MEDIA');

$linha = 3;
$coluna = 0;
$formulaMedia = array();
foreach($arrCelMediasItens as $setor => $item)
{
	foreach($item as $colunaItem => $linItem)
	{
		$col 	= array_keys($linItem)[0];
		$lin1 	= $linItem[$col][0];
		
		$lin 	= $linItem[$col][count($linItem[$col])-1];
		
		$textoCelula = 'AVERAGE('.$setor.'!$'.$arrCols[$col].'$'.$lin1.':$'.$arrCols[$col].'$'.$lin.')';
		$formulaMedia[$colunaItem][] = $textoCelula;
	}
}

//Loop para cada item fazendo sua média [PRAZO, QUALIDADE, CUSTO, ETC]
foreach($formulaMedia as $item => $medias)
{
	$qtdFormulas = count($medias);
	$formula = implode(',', $medias);	
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna,$linha,$item);
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+1,$linha, "=AVERAGE($formula)");
	$linha++;
}

$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
//$objWriter->setIncludeCharts(true);

header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header("Content-Disposition: attachment;filename=relatorio_avaliacoes_".date("dmYHis").".xlsx");
header('Cache-Control: max-age=0');
$objWriter->save('php://output');

exit;

?>