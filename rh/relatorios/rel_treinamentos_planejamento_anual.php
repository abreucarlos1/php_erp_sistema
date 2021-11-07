<?php
/*
		Relatorio treinamentos planejamento
		
		Criado por Carlos
		
		local/Nome do arquivo:
		../rh/relatorios/rel_treinamentos_planejamento_anual.php
		
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

$objPHPExcel = PHPExcel_IOFactory::load("../modelos_excel/planejamento_anual_treinamentos.xls");

$locale = 'pt_br';

$validlocale = PHPExcel_Settings::setlocale($locale);

if (!$validlocale) 
{
	echo 'Unable to set locale to '.$locale." - reverting to en_us<br />\n";
}

$clausulasetores = '';
$clausulaData = '';
$clausulaStatus = '';
$clausulaClassificacao = '';

if (!empty($_POST['data_inicio']) && !empty($_POST['data_fim']))
{
	$clausulaData = "AND rtc_data_treinamento BETWEEN '".php_mysql($_POST['data_inicio'])."' AND '".php_mysql($_POST['data_fim'])."'";
}

if (!empty($_POST['status']))
{
	$clausulaStatus = "AND rtc_situacao = {$_POST['status']}";
}

if (!empty($_POST['classificacao']))
{
	$clausulaStatus = "AND rtc_id_tipo = {$_POST['classificacao']}";
}

$sql = 
"SELECT
  rtc_id,
  COUNT(rti_id_funcionario) totalFuncs,
  rtc_id_tipo, rtc_duracao, rtc_valor,
  treinamento, DATE_FORMAT(rtc_data_treinamento, '%m/%Y') mesTreinamento
FROM
".DATABASE.".rh_treinamentos_cabecalho
JOIN(
  SELECT
    *
  FROM
    ".DATABASE.".rh_treinamentos_itens
    JOIN(
      SELECT id_funcionario, funcionario FROM ".DATABASE.".funcionarios WHERE funcionarios.situacao NOT IN ('DESLIGADO','CANCELADO') AND funcionarios.reg_del = 0 
    ) funcionarios
    ON id_funcionario = rti_id_funcionario
  WHERE
    rh_treinamentos_itens.reg_del = 0
) itens
ON rti_rtc_id = rtc_id
JOIN(
  SELECT * FROM ".DATABASE.".rh_treinamentos WHERE rh_treinamentos.reg_del = 0 
) treinamento
ON id_rh_treinamento = rtc_id_treinamento
WHERE itens.reg_del = 0
{$clausulaData}
{$clausulaStatus}
{$clausulaClassificacao}
GROUP BY
  rtc_id, rtc_id_treinamento, rtc_id_tipo, rtc_duracao, rtc_valor
ORDER BY
	rtc_data_treinamento;";

$arrTipos = array('1' => 'FORMAÇÃO', '2' => 'RECICLAGEM');

$objPHPExcel->setActiveSheetIndex(0);

$mesAnterior = '';
$meses = array();
$linha = 2;
$db->select($sql, 'MYSQL',
	function ($reg, $i) use(&$objPHPExcel, &$linha, &$arrTipos, &$meses, &$mesAnterior)
	{
		$mes = explode('/', $reg['mesTreinamento']);
		
		if (!key_exists($reg['mesTreinamento'], $meses))
		{
			//Se já tiver algum mês, calcular o total do mês anterior
			if ($mesAnterior != '')
			{
				$linhaInicial = $linha - count($meses[$mesAnterior]);
				$linhaFinal = $linha - 1;
	
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2,$linha,'Total Geral');
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5,$linha,'=SUM(F'.$linhaInicial.':F'.$linhaFinal.')');
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8,$linha,'=SUM(I'.$linhaInicial.':I'.$linhaFinal.')');

				$objPHPExcel->getActiveSheet()->getStyle('B'.$linha.':'.'I'.$linha)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('B'.$linha.':'.'I'.$linha)->getFont()->setBold(true);
				$objPHPExcel->getActiveSheet()->getStyle('B'.$linha.':'.'I'.$linha)->applyFromArray(
					array(
				        'fill' => array(
				            'type' => PHPExcel_Style_Fill::FILL_SOLID,
				            'color' => array('rgb' => '99AAFF')
				        )
				    )
				);
				$linha++;
			}
			
			$meses[trim($reg['mesTreinamento'])][] = $reg['mesTreinamento'];
			
			$linha++;
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1,$linha,strtoupper(tiraacentos(meses(intval($mes[0]), 1))).'/'.$mes[1]);
			$objPHPExcel->getActiveSheet()->mergeCells('B'.$linha.':'.'I'.$linha);
			$objPHPExcel->getActiveSheet()->getStyle('B'.$linha)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$objPHPExcel->getActiveSheet()->getStyle('B'.$linha.':'.'I'.$linha)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B'.$linha.':'.'I'.$linha)->getFont()->setBold(true);
			
			$linha++;
			
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1,$linha,'N.');
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2,$linha,'Treinamento');
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3,$linha,'Tipo');
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4,$linha,'Valor(R$)');
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5,$linha,'Valor/Hora(R$)');
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6,$linha,'N. Participantes');
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7,$linha,'Horas/Pessoa');
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8,$linha,'Total Horas');
			
			$objPHPExcel->getActiveSheet()->getStyle('B'.$linha.':'.'I'.$linha)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B'.$linha.':'.'I'.$linha)->getFont()->setBold(true);
			
			$linha ++;
			$i = 1;
		}
		
		//Apenas para contabilizar quantos registros houveram no mês
		$meses[trim($reg['mesTreinamento'])][] = $reg['mesTreinamento'];
		
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1,$linha,$i);
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2,$linha,trim($reg['treinamento']));
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3,$linha,$arrTipos[$reg['rtc_id_tipo']]);
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4,$linha,'=F'.$linha.'*I'.$linha);
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5,$linha,trim($reg['rtc_valor']));
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6,$linha,trim($reg['totalFuncs']));
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7,$linha,trim($reg['rtc_duracao']));
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8,$linha,'=H'.$linha.'*G'.$linha);

		$objPHPExcel->getActiveSheet()->getStyle('B'.$linha.':'.'I'.$linha)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
		
		$linha++;
		
		$mesAnterior = $reg['mesTreinamento'];
	}
);
		
//Se já tiver algum mês, calcular o total do mês anterior
if ($mesAnterior != '')
{
	$linhaInicial = $linha - count($meses[$mesAnterior]);
	$linhaFinal = $linha - 1;

	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2,$linha,'Total Geral');
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5,$linha,'=SUM(F'.$linhaInicial.':F'.$linhaFinal.')');
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8,$linha,'=SUM(I'.$linhaInicial.':I'.$linhaFinal.')');
	
	$objPHPExcel->getActiveSheet()->getStyle('B'.$linha.':'.'I'.$linha)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
	$objPHPExcel->getActiveSheet()->getStyle('B'.$linha.':'.'I'.$linha)->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet()->getStyle('B'.$linha.':'.'I'.$linha)->applyFromArray(
					array(
				        'fill' => array(
				            'type' => PHPExcel_Style_Fill::FILL_SOLID,
				            'color' => array('rgb' => '99AAFF')
				        )
				    )
				);
	$linha++;
}

if ($db->numero_registros == 0)
{
	exit('<script>alert("Nenhum registro encontrado!");window.close();</script>');
}

//Planilha 2
$objPHPExcel->setActiveSheetIndex(1);

$sql = 
"SELECT
  rtc_id,
  COUNT(rti_id_funcionario) totalFuncs,
  GROUP_CONCAT(funcionario) AS Funcs,
  rtc_id_tipo, rtc_duracao, rtc_valor,
  treinamento, DATE_FORMAT(rtc_data_treinamento, '%m/%Y') mesTreinamento,
  rtc_situacao, rtc_data_situacao, situacao, rtc_observacoes
FROM
".DATABASE.".rh_treinamentos_cabecalho
JOIN(
  SELECT
    *
  FROM
    ".DATABASE.".rh_treinamentos_itens
    JOIN(
      SELECT id_funcionario, funcionario FROM ".DATABASE.".funcionarios WHERE funcionarios.situacao NOT IN ('DESLIGADO','CANCELADO') AND funcionarios.reg_del = 0
    ) funcionarios
    ON id_funcionario = rti_id_funcionario
  WHERE
    rh_treinamentos_itens.reg_del = 0
) itens
ON rti_rtc_id = rtc_id
JOIN(
  SELECT * FROM ".DATABASE.".rh_treinamentos
) treinamento
ON id_rh_treinamento = rtc_id_treinamento
LEFT JOIN(
  SELECT rtv_item situacao, rtv_valor idsituacao FROM ".DATABASE.".rh_treinamentos_valores WHERE rtv_titulo = 'situacao' AND reg_del = 0
) situacao
ON idsituacao = rtc_situacao
WHERE rh_treinamentos_cabecalho.reg_del = 0
{$clausulaData}
{$clausulaStatus}
{$clausulaClassificacao}
GROUP BY
  rtc_id, rtc_id_treinamento, rtc_id_tipo, rtc_duracao, rtc_valor,
  rtc_situacao, situacao, rtc_observacoes,rtc_data_treinamento
ORDER BY
	mesTreinamento;";

$mesAnterior = '';
$meses = array();
$linha = 10;
$ultimaLinha = 10;
$db->select($sql, 'MYSQL',
	function ($reg, $i) use(&$objPHPExcel, &$linha, &$arrTipos, &$meses, &$mesAnterior, &$ultimaLinha)
	{
		$mes = explode('/', $reg['mesTreinamento']);
		
		$funcs = explode(',', $reg['Funcs']);
		foreach($funcs as $k => $func)
		{
			$nome = explode(' ', $func);
			$funcs[$k] = ucwords(strtolower(tiraacentos($nome[0])));
		}
		$funcs = implode('/ ', $funcs);
		
		//Se for realizado, fica a data
		$dataRealizacao = $reg['rtc_situacao'] == 6 ? mysql_php($reg['rtc_data_treinamento']) : '';
		$observacoes = in_array($reg['rtc_situacao'], array(2,3,4)) ? mysql_php($reg['rtc_data_situacao']).' - '.$reg['rtc_observacoes'] : '';
		
		$reg['situacao'] = empty($reg['situacao']) ? 'Em andamento' : $reg['situacao'];
		
		$col = 0;
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col++,$linha,$i);
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col++,$linha,trim($reg['treinamento']));
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col++,$linha,"=C{$linha}*D{$linha}*E{$linha}");
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col++,$linha,$funcs);
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col++,$linha,$reg['rtc_duracao']);
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col++,$linha,"={$reg['totalFuncs']}*H{$linha}*{$reg['rtc_valor']}");
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col++,$linha,$dataRealizacao);
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col++,$linha,$reg['situacao']);
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col++,$linha,$observacoes);
		
		$altura = strlen($funcs) < 27 ? 16 : strlen($funcs) < 54 ? 33 : 55;
		
		$objPHPExcel->getActiveSheet()->getRowDimension($linha)->setRowHeight($altura);
		$ultimaLinha = $linha;
		$linha++;
	}
);

$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7,($ultimaLinha+1),"=SUM(H10:H{$ultimaLinha})");
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8,($ultimaLinha+1),"=SUM(I10:I{$ultimaLinha})");

$objPHPExcel->getActiveSheet()->getStyle('A10:L'.($ultimaLinha))->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_DOTTED);

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header("Content-Disposition: attachment;filename=relatorio_planejamento_treinamentos_".$_POST['data_inicio'].'_'.$_POST['data_fim'].".xlsx");
header('Cache-Control: max-age=0');
$objWriter->save('php://output');

exit;

?>