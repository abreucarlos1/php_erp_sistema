<?php
/*
	  Relatório Previsão Custo	
	  
	  Criado por Carlos Abreu  
	  
	  local/Nome do arquivo:
	  ../financeiro/relatorios/rel_previsao_custo_excel.php
	  
	  Versão 0 --> VERSÃO INICIAL - 14/07/2007
	  Versão 1 --> Atualização lay-out - 23/06/2014 - Carlos Abreu
	  Versão 2 --> atualização classe banco de dados - 22/01/2015 - Carlos Abreu
	  Versão 3 --> Inclusão dos campos reg_del nas consultas - 20/11/2017 - Carlos Abreu		
*/

ini_set('max_execution_time', 0); // No time limit
ini_set('post_max_size', '20M');
ini_set('upload_max_filesize', '20M');
/**
 * PHPExcel
 *
 * Copyright (C) 2006 - 2010 PHPExcel
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category   PHPExcel
 * @package    PHPExcel
 * @copyright  Copyright (c) 2006 - 2010 PHPExcel (http://www.codeplex.com/PHPExcel)
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt	LGPL
 * @version    1.7.4, 2010-08-26
 */

require_once(implode(DIRECTORY_SEPARATOR,array('..','..','config.inc.php')));

require_once(INCLUDE_DIR."PHPExcel/Classes/PHPExcel.php"); 

$db = new banco_dados();

//monta o array dos meses e datas
for($m = 1;$m <= 12;$m++)
{
	$dias = date("t", mktime(0, 0, 0, $m, 1, $_POST["ano"]));

	for($d = 1; $d <= $dias ; $d++)
	{
		$array_datas_mes[$m][$_POST["ano"]."-".$m."-".$d] = 1;
	}
		
}

//Percorre a tabela de colaboradores
$sql = "SELECT * FROM ".DATABASE.".funcionarios, ".DATABASE.".rh_funcoes ";
$sql .= "WHERE rh_funcoes.id_funcao = funcionarios.id_funcao ";
$sql .= "AND funcionarios.reg_del = 0 ";
$sql .= "AND rh_funcoes.reg_del = 0 ";
$sql .= "AND funcionarios.situacao NOT IN ('DESLIGADO','CANCELADO') ";
$sql .= "ORDER BY funcionarios.funcionario ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}

$array_cont = $db->array_select;

foreach($array_cont as $cont)
{
	$sql = "SELECT * FROM ".DATABASE.".salarios ";
	$sql .= "WHERE id_funcionario = '".$cont["id_funcionario"]."' ";
	$sql .= "AND salarios.reg_del = 0 ";
	$sql .= "ORDER BY data DESC, id_salario DESC LIMIT 1 ";
			
	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}
	
	$cont1 = $db->array_select[0];
	
	$array_funcionarios[$cont["id_funcionario"]] = $cont["funcionario"];
	
	$array_funcoes[$cont["id_funcionario"]] = $cont["descricao"];
	
	$array_tp_contrato[$cont["id_funcionario"]] = $cont1[" tipo_contrato"];
	
	$array_salario["CLT"][$cont["id_funcionario"]] = $cont1["salario_clt"];

	$array_salario["MENS"][$cont["id_funcionario"]] = $cont1["salario_mensalista"];
	
	$array_salario["SC"][$cont["id_funcionario"]] = $cont1["salario_hora"]; //8H DIARIAS				
}

$objPHPExcel = PHPExcel_IOFactory::load("../modelos_excel/relatorio_previsao_custo_modelo.xls");

$locale = 'pt_br';

$validlocale = PHPExcel_Settings::setlocale($locale);

if (!$validlocale) 
{
	echo 'Unable to set locale to '.$locale." - reverting to en_us<br />\n";
}

// Redirect output to a clients web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="previsao_custo_"'.date('His').'".xlsx"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

foreach ($array_datas_mes as $mes=>$data)
{
	$objPHPExcel->setActiveSheetIndex($mes-1);
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, 3, date('d/m/Y'));
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, 3, $_POST["ano"]);
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(9, 3, dias_uteis($mes,$_POST["ano"]));

	$linha=5;	

	foreach($array_funcionarios as $id_funcionario=>$funcionario)
	{
		  //nome do funcionario
		  $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $linha, $array_funcionarios[$id_funcionario]);
		  $objPHPExcel->getActiveSheet()->mergeCells("A".$linha.":D".$linha);
		  //fun��o
		  $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, $linha, $array_funcoes[$id_funcionario]);
		  $objPHPExcel->getActiveSheet()->mergeCells("E".$linha.":F".$linha);
		  //tipo contrato
		  $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $linha, $array_tp_contrato[$id_funcionario]);
		  //salario clt
		  $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, $linha, $array_salario['CLT'][$id_funcionario]);
		  $objPHPExcel->getActiveSheet()->getStyle("H".$linha)->getNumberFormat()->setFormatCode('R$ #,#00.00');
		  //SALARIO MENS
		  $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8, $linha, $array_salario['MENS'][$id_funcionario]);
		  $objPHPExcel->getActiveSheet()->getStyle("I".$linha)->getNumberFormat()->setFormatCode('R$ #,#00.00');
		  //SALARIO PJ/DIA
		  $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(9, $linha, $array_salario['SC'][$id_funcionario]*8);
		  $objPHPExcel->getActiveSheet()->getStyle("J".$linha)->getNumberFormat()->setFormatCode('R$ #,#00.00');
		  //SALARIO PJ/HORA
		  $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(10, $linha, $array_salario['SC'][$id_funcionario]);
		  $objPHPExcel->getActiveSheet()->getStyle("K".$linha)->getNumberFormat()->setFormatCode('R$ #,#00.00');		  //total
		  
		  $total = 'H'.$linha.'+I'.$linha;
		  
		  $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(11, $linha, '=(J'.$linha.'*J3)+'.$total);
		  $objPHPExcel->getActiveSheet()->getStyle("L".$linha)->getNumberFormat()->setFormatCode('R$ #,#00.00');
		  
		  $linha++;
		  
		  $objPHPExcel->getActiveSheet()->insertNewRowBefore($linha,1);		
	}
	
	//TOTALIZA
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $linha+1, "TOTAL:");
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, $linha+1, '=SUM(H5:H'.($linha-1).')');
	$objPHPExcel->getActiveSheet()->getStyle("H".$linha+1)->getNumberFormat()->setFormatCode('R$ #,#00.00');		  //total

	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8, $linha+1, '=SUM(I5:I'.($linha-1).')');
	$objPHPExcel->getActiveSheet()->getStyle("I".$linha+1)->getNumberFormat()->setFormatCode('R$ #,#00.00');

	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(9, $linha+1, '=SUM(J5:J'.($linha-1).')');
	$objPHPExcel->getActiveSheet()->getStyle("J".$linha+1)->getNumberFormat()->setFormatCode('R$ #,#00.00');
		
}

$objPHPExcel->setActiveSheetIndex((date('m')-1));

$objWriter->save('php://output');

exit;
?>