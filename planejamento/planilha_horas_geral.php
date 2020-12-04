<?php
/*
		Relatório Planilha Geral Horas
		
		Criado por Carlos Abreu  
		
		local/Nome do arquivo:		
		../planejamento/planilha_horas_geral.php
		
		Versão 0 --> VERSÃO INICIAL - 02/03/2014
		Versão 1 --> Atualização classe banco de dados - 22/01/2015 - Carlos Abreu
		Versão 2 --> Atualização layout - Carlos Abreu - 31/03/2017
		Versão 3 --> Inclusão dos campos reg_del nas consultas - 20/11/2017 - Carlos Abreu
*/

ini_set('max_execution_time', 0);
ini_set('post_max_size', '20M');
ini_set('upload_max_filesize', '20M');
ini_set('memory_limit', '256M');

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
require_once(implode(DIRECTORY_SEPARATOR,array('..','includes', 'include_form.inc.php')));

if(!verifica_sub_modulo(602))
{
    nao_permitido();
}

require_once(INCLUDE_DIR."PHPExcel/Classes/PHPExcel.php");

$objPHPExcel = PHPExcel_IOFactory::load("./modelos_excel/planilha_horas_geral.xlsx");
$locale = 'pt_br';

$validlocale = PHPExcel_Settings::setlocale($locale);
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

//Aba resumo
$objPHPExcel->setActiveSheetIndex(0);

//data de emissao
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, 3, date('d/m/Y'));

//ABA OS EM EXECUÇÃO
$objPHPExcel->setActiveSheetIndex(1);

//data de emissao
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, 3, date('d/m/Y'));

//Gerar a Planilha
$arrResultado = popularPlanilha('2,3,7', $objPHPExcel, $db);

//ABA OS PARALIZADAS
$objPHPExcel->setActiveSheetIndex(2);

//data de emissao
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, 3, date('d/m/Y'));

//Gerar a Planilha
$arrResultado = popularPlanilha('5,11,12', $objPHPExcel, $db);

//ABA HISTOGRAMA
$objPHPExcel->setActiveSheetIndex(3);

//data de emissao
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, 3, date('d/m/Y'));

$arrOrdemLista = array(
	'AUTOMAÇÃO' => array('linha' => '23'),
	'INSTRUMENTAÇÃO' => array('linha' => '24'),
	'ELÉTRICA' => array('linha' => '25'),
	'CIVIL' => array('linha' => '26'),
	'ESTRUTURAS METÁLICAS' => array('linha' => '27'),		
	'MECÂNICA' => array('linha' => '28'),
	'SISTEMA COMBATE INCÊNCIO' => array('linha' => '29'),		
	'TUBULAÇÃO' => array('linha' => '30'),
	'VENTILAÇÃO E AR CONDICIONADO' => array('linha' => '31'),		
	'PDMS' => array('linha' => '32'),
	'PROCESSO' => array('linha' => '33'),
	'COORDENAÇÃO' => array('linha' => '34'),
	'PLANEJAMENTO' => array('linha' => '35')
);

//Número de funcionários por setor
$sql = "SELECT
	setor, count(id_funcionario) quant
FROM
	".DATABASE.".funcionarios
    JOIN ".DATABASE.".setores ON setores.id_setor = funcionarios.id_setor
WHERE situacao = 'ATIVO'
AND funcionarios.reg_del = 0
AND setores.reg_del = 0 
AND id_local = 3
GROUP BY setor
ORDER BY setor ";

$db->select($sql, 'MYSQL', function($reg, $i) use(&$arrOrdemLista){
	if (isset($arrOrdemLista[$reg['setor']]))
		$arrOrdemLista[$reg['setor']]['qtd'] = $reg['quant'];
});

foreach($arrOrdemLista as $k => $lista)
{
	if (isset($lista['qtd']))
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, $lista['linha'], intval($lista['qtd']));
}

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="planilha_horas_geral_'.date('dmYHis').'_".xlsx"');
header('Cache-Control: max-age=0');

$objWriter->save('php://output');

function popularPlanilha($fases, $objPHPExcel, $db)
{
	$setores = array();
	
	$sql = "SELECT abreviacao, setor FROM ".DATABASE.".setores ";
	$sql .= "WHERE ativo = 1 ";
	$sql .= "AND setores.reg_del = 0 ";
	
	$db->select($sql, 'MYSQL', function($reg, $i) use(&$setores){
		$setores[$reg['abreviacao']] = tiraacentos($reg['setor']);
	});
	
	$sql = "
	SELECT
		AF8_PROJET, SUM(AFA_QUANT) PLANEJADO, AF9_GRPCOM
	FROM 
		AF8010 WITH(NOLOCK), 
		AF9010 WITH(NOLOCK), 
		AFA010 WITH(NOLOCK)
	WHERE 
		AF9010.D_E_L_E_T_ = ''
		AND AF8010.D_E_L_E_T_ = ''
		AND AFA010.D_E_L_E_T_ = ''
		AND AF8_PROJET = AF9_PROJET
		AND AF8_REVISA = AF9_REVISA
		AND AF9_PROJET = AFA_PROJET
		AND AF9_REVISA = AFA_REVISA
		AND AFA_TAREFA = AF9_TAREFA
		AND AF8_FASE IN(".$fases.")
		AND AF9_GRPCOM <> ''
	GROUP BY AF8_PROJET, AF9_GRPCOM
	ORDER BY AF8_PROJET";

	$listaOs = array();
	$virgula = '';
	$arrResultado = array();
	
	$db->select($sql, 'MSSQL', function($reg, $i) use(&$arrResultado, &$listaOs, &$virgula, &$setores){
		$arrResultado[$reg['AF8_PROJET']][$setores[trim($reg['AF9_GRPCOM'])]]['PLANEJADO'] = $reg['PLANEJADO'];
		$arrResultado[$reg['AF8_PROJET']][$setores[trim($reg['AF9_GRPCOM'])]]['ORCADO'] = 0.00;
		$arrResultado[$reg['AF8_PROJET']][$setores[trim($reg['AF9_GRPCOM'])]]['HORAS_APONTADAS'] = 0.00;
			
		$listaOs[$reg['AF8_PROJET']] = "'".$reg['AF8_PROJET']."'";
	});
	
	$sql = "SELECT 
			AF2_ORCAME, SUM(AF3_QUANT) ORCADO, AF2_GRPCOM
		FROM 
			AF2010 WITH(NOLOCK), 
			AF3010 WITH(NOLOCK)
		WHERE 
			AF2010.D_E_L_E_T_ = ''
			AND AF3010.D_E_L_E_T_ = ''
			AND AF3_TAREFA = AF2_TAREFA
			AND AF3_ORCAME = AF2_ORCAME
			AND AF2_CODIGO <> ''
			AND AF3_RECURS <> ''
			AND AF2_ORCAME IN (".implode(",", $listaOs).")
			
		GROUP BY AF2_ORCAME, AF2_GRPCOM";
	
	$db->select($sql, 'MSSQL', function($reg, $i) use(&$arrResultado, &$setores){
		$arrResultado[$reg['AF2_ORCAME']][$setores[trim($reg['AF2_GRPCOM'])]]['ORCADO'] = $reg['ORCADO'];
	});
	
	$sql = " SELECT 
		SUM(AJK_HQUANT) HORAS_APONTADAS, AF9_GRPCOM, AJK_PROJET
		FROM 
			AJK010 WITH(NOLOCK)
			JOIN AF9010 WITH(NOLOCK) ON AF9010.D_E_L_E_T_ = '' AND AF9_PROJET = AJK_PROJET AND AF9_REVISA = AJK_REVISA AND AF9_TAREFA = AJK_TAREFA
			JOIN AF8010 WITH(NOLOCK) ON AF8010.D_E_L_E_T_ = '' AND AF8_PROJET = AJK_PROJET AND AF8_REVISA = AJK_REVISA
			JOIN AE8010 WITH(NOLOCK) ON AE8010.D_E_L_E_T_ = '' AND AE8_RECURS = AJK_RECURS
		WHERE 
			AJK010.D_E_L_E_T_ = ''
			AND AJK_PROJET IN (".implode(",", $listaOs).")
			AND AF9_GRPCOM <> ''
		GROUP BY
			AF9_GRPCOM, AJK_PROJET";
	
	$db->select($sql, 'MSSQL', function($reg, $i) use(&$arrResultado, &$setores){
		$arrResultado[$reg['AJK_PROJET']][$setores[trim($reg['AF9_GRPCOM'])]]['HORAS_APONTADAS'] = $reg['HORAS_APONTADAS'];
	});
	
	$linha = 7;
	foreach($arrResultado as $os => $registro)
	{
		foreach($registro as $disciplina => $reg)
		{
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $linha, $os);
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $linha,  $disciplina);
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $linha, $reg['ORCADO']);
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, $linha, number_format($reg['PLANEJADO'], 2, '.', ''));
		
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, $linha, $reg['HORAS_APONTADAS']);
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(9, $linha, '=F'.$linha.'-H'.$linha);
			
			if ($linha >= 34)
			{
				$objPHPExcel->getActiveSheet()->mergeCellsByColumnAndRow(1,$linha,2,$linha);
				$objPHPExcel->getActiveSheet()->mergeCellsByColumnAndRow(3,$linha,4,$linha);
				$objPHPExcel->getActiveSheet()->mergeCellsByColumnAndRow(5,$linha,6,$linha);
				$objPHPExcel->getActiveSheet()->mergeCellsByColumnAndRow(7,$linha,8,$linha);
			}
			$linha++;
		}
		
	};
	
	$objPHPExcel->getActiveSheet()->getStyle('A35:J'.$linha)->applyFromArray(array('borders' => 
																			array(
																				'allborders' => array(
																					'style' => PHPExcel_Style_Border::BORDER_THIN
																				)
																			)
																	   	)
																	  );
}

?>