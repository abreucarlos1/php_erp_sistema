<?php
/*
	Relatório de Margem de Lucro
	Criado por Carlos Abreu  
	
	Versão 0 --> VERSÃO INICIAL : 25/06/2015
	Versão 1 --> Inclusão dos campos reg_del nas consultas - 17/11/2017 - Carlos Abreu
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

$array_coord = NULL;

$array_despesas = NULL;

$db = new banco_dados();

//TABELA PA7 - COORDENADORES
$sql = "SELECT * FROM PA7010 WITH(NOLOCK) ";
$sql .= "WHERE PA7010.D_E_L_E_T_ = '' ";

$db->select($sql,'MSSQL', true);

if($db->erro!='')
{
	die($db->erro);
}

foreach($db->array_select as $regs)
{
	$array_coord[$regs["PA7_ID"]] = $regs["PA7_NOME"];
}

//Seleciona o PROJETO
$sql = "SELECT * FROM AF8010 WITH(NOLOCK), SA1010 WITH(NOLOCK) ";
$sql .= "WHERE AF8010.D_E_L_E_T_ = '' ";
$sql .= "AND SA1010.D_E_L_E_T_ = '' ";
$sql .= "AND AF8010.AF8_CLIENT = SA1010.A1_COD ";
$sql .= "AND AF8010.AF8_LOJA = SA1010.A1_LOJA ";
$sql .= "AND AF8_PROJET = '".$_POST["escolhaos"]."' ";
$sql .= "ORDER BY AF8_DATA, AF8_PROJET ";

$db->select($sql,'MSSQL', true);

if($db->erro!='')
{
	die($db->erro);
}

$regs1 = $db->array_select[0];

//SELECIONA A MAIOR DATA COM AVANCO DE 100%
$sql = "SELECT TOP 1 AFF010.AFF_DATA FROM AF9010 WITH(NOLOCK), AFF010 WITH(NOLOCK) ";
$sql .= "WHERE AFF010.D_E_L_E_T_ = '' ";
$sql .= "AND AF9010.D_E_L_E_T_ = '' ";
$sql .= "AND AF9010.AF9_PROJET = '".$regs1["AF8_PROJET"]."' ";
$sql .= "AND AF9010.AF9_REVISA = '".$regs1["AF8_REVISA"]."' "; 
$sql .= "AND AFF010.AFF_PROJET = AF9010.AF9_PROJET ";
$sql .= "AND AFF010.AFF_REVISA = AF9010.AF9_REVISA ";
$sql .= "AND AFF010.AFF_TAREFA = AF9010.AF9_TAREFA ";
$sql .= "AND (AFF_QUANT/AF9_QUANT) >= '1' ";
$sql .= "ORDER BY AFF010.AFF_DATA DESC ";

$db->select($sql,'MSSQL', true);

if($db->erro!='')
{
	die($db->erro);
}

$regs2 = $db->array_select[0];

//seleciona os valores de venda tarefas (MO) , nivel maior que 002	
$sql = "SELECT SUM(AF2_TOTAL) AS MO FROM AF2010 WITH(NOLOCK) ";
$sql .= "WHERE AF2010.D_E_L_E_T_ = '' ";
$sql .= "AND AF2010.AF2_ORCAME = '".$regs1["AF8_PROJET"]."' ";
$sql .= "AND AF2010.AF2_NIVEL > 0002 ";
$sql .= "AND AF2_GRPCOM <> 'DES' ";

$db->select($sql,'MSSQL', true);

if($db->erro!='')
{
	die($db->erro);
}

$regs3 = $db->array_select[0];

//seleciona os valores de venda tarefas (DES) , nivel maior que 002	
$sql = "SELECT SUM(AF2_TOTAL) AS DES FROM AF2010 WITH(NOLOCK) ";
$sql .= "WHERE AF2010.D_E_L_E_T_ = '' ";
$sql .= "AND AF2010.AF2_ORCAME = '".$regs1["AF8_PROJET"]."' ";
$sql .= "AND AF2010.AF2_NIVEL > 0002 ";
$sql .= "AND AF2_GRPCOM = 'DES' ";

$db->select($sql,'MSSQL', true);

if($db->erro!='')
{
	die($db->erro);
}

$regs4 = $db->array_select[0];

//CONTABILIZA OS DEBITOS (CT2) MO
$sql = "SELECT SUM(CT2_VALOR) AS DEBITO FROM CT2010 WITH(NOLOCK) ";
$sql .= "WHERE CT2010.D_E_L_E_T_ = '' ";
$sql .= "AND SUBSTRING(CT2_CLVLDB,9,10)='".$regs1["AF8_PROJET"]."' ";
$sql .= "AND SUBSTRING(CT2_DEBITO,1,6) >= 411001 ";
$sql .= "AND SUBSTRING(CT2_DEBITO,1,6) <= 411006 "; 

$db->select($sql,'MSSQL', true);

if($db->erro!='')
{
	die($db->erro);
}

$regs5 = $db->array_select[0];

$valor_cont_mo = $regs5["DEBITO"]-$regs6["CREDITO"];

//CONTABILIZA OS DEBITOS (CT2) DES
$sql = "SELECT SUM(CT2_VALOR) AS DEBITO FROM CT2010 WITH(NOLOCK) ";
$sql .= "WHERE CT2010.D_E_L_E_T_ = '' ";
$sql .= "AND SUBSTRING(CT2_CLVLDB,9,10)='".$regs1["AF8_PROJET"]."' ";
$sql .= "AND SUBSTRING(CT2_DEBITO,1,6) = 411007 ";

$db->select($sql,'MSSQL', true);

if($db->erro!='')
{
	die($db->erro);
}

$regs7 = $db->array_select[0];

$valor_cont_des = $regs7["DEBITO"]-$regs8["CREDITO"];

$objPHPExcel = PHPExcel_IOFactory::load("../modelos_excel/margem_lucro_modelo.xls");

$locale = 'pt_br';

$validlocale = PHPExcel_Settings::setlocale($locale);

if (!$validlocale) 
{
	echo 'Unable to set locale to '.$locale." - reverting to en_us<br />\n";
}

// Redirect output to a clients web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="margem_lucro_"'.date('His').'".xlsx"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

//1ª folha
$objPHPExcel->setActiveSheetIndex(0);

$coluna = 2;

$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna, 5, iconv('ISO-8859-1', 'UTF-8',trim($regs1["AF8_PROJET"])." - ".trim($regs1["AF8_DESCRI"])));

$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna, 6, iconv('ISO-8859-1', 'UTF-8',trim($regs1["A1_NOME"])." - ".trim($regs1["A1_NREDUZ"])));

$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna, 7, iconv('ISO-8859-1', 'UTF-8',trim($array_coord[$regs1["AF8_COORD1"]])));

//Nome coordenador cliente
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna, 8, iconv('ISO-8859-1', 'UTF-8',trim($regs1["AF8_RESPTE"])));


//data
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna, 9, iconv('ISO-8859-1', 'UTF-8',mysql_php(protheus_mysql($regs2["AFF_DATA"]))));

//valor Venda MO
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+5, 12, $regs3["MO"]);
$objPHPExcel->getActiveSheet()->getStyle("H12")->getNumberFormat()->setFormatCode('R$ #,#00.00');

//valor Venda DESPESAS
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+5, 13, $regs4["DES"]);
$objPHPExcel->getActiveSheet()->getStyle("H13")->getNumberFormat()->setFormatCode('R$ #,#00.00');

//valor CONTABIL MO
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+5, 17, $valor_cont_mo);
$objPHPExcel->getActiveSheet()->getStyle("H17")->getNumberFormat()->setFormatCode('R$ #,#00.00');

//valor CONTABIL DES
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+5, 18, $valor_cont_des);
$objPHPExcel->getActiveSheet()->getStyle("H17")->getNumberFormat()->setFormatCode('R$ #,#00.00');

$objWriter->save('php://output');

exit;
?>