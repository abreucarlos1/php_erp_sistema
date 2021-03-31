<?php
/*
	 Relatório Acumulado Custos
	 
	 Criado por Carlos Abreu  
	 
	 Versão 0 --> VERSÃO INICIAL : 10/06/2017
	 Versão 1 --> Inclusão dos campos reg_del nas consultas - 17/11/2017 - Carlos Abreu
 */
 
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

ini_set('max_execution_time', 0); // No time limit
ini_set('post_max_size', '20M');
ini_set('upload_max_filesize', '20M');
ini_set('memory_limit', '1024M');

require_once(implode(DIRECTORY_SEPARATOR,array('..','..','config.inc.php')));

require_once(INCLUDE_DIR."PHPExcel/Classes/PHPExcel.php"); 

$db = new banco_dados();

$os = $_POST["escolhaos"];

$filtro0 = "";
$filtro1 = "";
$filtro2 = "";

/*
//filtra a os (-1 TODAS AS OS)
if($os!=-1)
{
	$filtro0 .= "AND AF8_PROJET = '".sprintf("%010d",$os)."' ";
	
}
else
{
	//Execução, Aguard. Def. Clien., As built, ADMs
	$filtro0 .= "AND AF8_PROJET > '0000003000' ";
	$filtro0 .= "AND AF8_FASE IN ('03','09','07') ";
}

if($_POST["intervalo"]=='1')
{
	$filtro0 .= "AND AF8_START >= '" . mysql_protheus(php_mysql($_POST["dataini"])) . "' ";
	$filtro1 .= "AND (AF9_START >= '" . mysql_protheus(php_mysql($_POST["dataini"])) . "' ";
	$filtro1 .= "OR AF9_DTATUI >= '" . mysql_protheus(php_mysql($_POST["dataini"])) . "') ";
	$filtro2 .= "AND AFU_DATA >= '" . mysql_protheus(php_mysql($_POST["dataini"])) . "' ";
}


//Seleciona as OSs
$sql = "SELECT * FROM AF8010 WITH(NOLOCK) ";
$sql .= "WHERE D_E_L_E_T_ = '' ";
$sql .= $filtro0;
$sql .= "ORDER BY AF8_PROJET ";

$db->select($sql, 'MSSQL', true);

foreach($db->array_select as $regs0)
{	
	//PEGA A ULTIMA REVISÃO DA FASE 01 (ORÇAMENTO)
	$sql = "SELECT MAX(AFE_REVISA) AS ULT_REVISA FROM AFE010 WITH(NOLOCK) ";
	$sql .= "WHERE AFE010.D_E_L_E_T_ = '' ";
	$sql .= "AND AFE010.AFE_PROJET = '".$regs0["AF8_PROJET"]."' ";
	$sql .= "AND AFE010.AFE_FASE = '01' ";
	
	$db->select($sql, 'MSSQL', true);
	
	$regs_ult_rev = $db->array_select[0];
	
	//OBTEM O CUSTO TOTAL DO PROJETO (PREVISTO)
	$sql = "SELECT SUM(AF9_CUSTO) AS CUSTO_TOTAL FROM AF9010 WITH(NOLOCK) ";
	$sql .= "WHERE AF9010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF9_PROJET = '".$regs0["AF8_PROJET"]."' ";
	$sql .= "AND AF9_REVISA = '".$regs_ult_rev["ULT_REVISA"]."' ";
	$sql .= $filtro1;
	
	$db->select($sql, 'MSSQL', true);
	
	$reg_cust_tot_prev = $db->array_select[0];
	
	//OBTEM O CUSTO TOTAL DO PROJETO (REAL)
	$sql = "SELECT SUM(AFU_CUSTO1) AS CUSTO_TOTAL FROM AFU010 WITH(NOLOCK) ";
	$sql .= "WHERE AFU010.D_E_L_E_T_ = '' ";
	$sql .= "AND AFU_PROJET = '".$regs0["AF8_PROJET"]."' ";
	$sql .= "AND AFU_REVISA = '".$regs0["AF8_REVISA"]."' ";
	$sql .= $filtro2;
	
	$db->select($sql, 'MSSQL', true);
	
	$reg_cust_tot_real = $db->array_select[0];
	
	//OBTEM AS DESPESAS (ORCADO)
	$sql = "SELECT SUM(AFB010.AFB_VALOR) AS despesas FROM AFB010 WITH(NOLOCK) ";
	$sql .= "WHERE AFB010.D_E_L_E_T_ = ''  ";
	$sql .= "AND AFB010.AFB_PROJET = '".$regs0["AF8_PROJET"]."' ";
	$sql .= "AND AFB010.AFB_REVISA = '".$regs0["AF8_REVISA"]."' ";
	
	$db->select($sql,'MSSQL', true);

	$reg_desp_orc = $db->array_select[0];
		
	$array_proj[$regs0["AF8_PROJET"]] = $regs0["AF8_PROJET"];
	$array_fase[$regs0["AF8_PROJET"]] = $regs0["AF8_FASE"];
	$array_ver_orc[$regs0["AF8_PROJET"]] = $regs_ult_rev["ULT_REVISA"];
	$array_ver_rea[$regs0["AF8_PROJET"]] = $regs0["AF8_REVISA"];
	$array_cust_prev[$regs0["AF8_PROJET"]] = $reg_cust_tot_prev["CUSTO_TOTAL"];
	$array_cust_real[$regs0["AF8_PROJET"]] = $reg_cust_tot_real["CUSTO_TOTAL"];
	$array_desp[$regs0["AF8_PROJET"]] = $reg_desp_orc["despesas"];		
	
	//Obtem a data de inicio e fim do projeto (previsto) 
	$sql = "SELECT MIN(AF9_START) AS START, MAX(AF9_FINISH) AS FINISH FROM AF9010 WITH(NOLOCK), AF8010 WITH(NOLOCK) ";
	$sql .= "WHERE AF9_PROJET = AF8_PROJET ";
	$sql .= "AND AF8010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF9010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF8_PROJET = '" . $regs0["AF8_PROJET"] . "' ";	
	$sql .= "AND AF9_REVISA = '".$regs_ult_rev["ULT_REVISA"]."' ";
	$sql .= $filtro1;	
	$sql .= "GROUP BY AF9_PROJET ";
	
	$db->select($sql, 'MSSQL', true);
	
	$dataprojet = $db->array_select[0];
	
	//Obtem a data do 1ª e ultimo apontamento confirmado
	$sql = "SELECT MIN(AFU_DATA) AS DATAINI, MAX(AFU_DATA) AS DATAFIM, SUM(AFU_HQUANT) AS HorasApont FROM AFU010 WITH(NOLOCK), AF8010 WITH(NOLOCK) ";
	$sql .= "WHERE AFU_PROJET = AF8_PROJET ";
	$sql .= "AND AFU_REVISA = AF8_REVISA ";
	$sql .= "AND AF8010.D_E_L_E_T_ = '' ";
	$sql .= "AND AFU010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF8_PROJET = '" . $regs0["AF8_PROJET"] . "' ";
	$sql .= "AND AF8_REVISA = '" . $regs0["AF8_REVISA"] . "' ";
	$sql .= $filtro2;
	$sql .= "GROUP BY AFU_PROJET ";
	
	$db->select($sql, 'MSSQL', true);
	
	$dataapont = $db->array_select[0];
	
	//pega a menor data entre o previsto e o realizado
	if($dataprojet["START"]<=$dataapont["DATAINI"])
	{
		$start = $dataprojet["START"];
	}
	else
	{
		$start = $dataapont["DATAINI"];
	}
	
	//pega a maior data entre o previsto e o realizado
	if($dataprojet["FINISH"]>=$dataapont["DATAFIM"])
	{
		$finish = $dataprojet["FINISH"];
	}
	else
	{
		$finish = $dataapont["DATAFIM"];
	}	
	
	$semanas = montasemana(mysql_php(protheus_mysql($start)),mysql_php(protheus_mysql($finish)),0);
	
	//percorre o array de datas, a fim de somar as horas do periodo contido no array
	for($i=0;$i<=count($semanas);$i++)
	{
		//data_per[0] --> data inicio
		//data_per[1] --> data fim
		$data_per = explode("#",$semanas[$i]);
		
		//SOMA OS CUSTOS NO PERIODO (previsto)
		$sql = "SELECT SUM(AF9_CUSTO) AS CustoPrev FROM AF8010 WITH(NOLOCK), AF9010 WITH(NOLOCK) ";
		$sql .= "WHERE AF8_PROJET = AF9_PROJET ";
		$sql .= "AND AF8010.D_E_L_E_T_ = '' ";
		$sql .= "AND AF9010.D_E_L_E_T_ = '' ";
		$sql .= "AND AF8_PROJET = '" . $regs0["AF8_PROJET"] . "' ";
		$sql .= "AND AF9_FINISH <= '" . mysql_protheus(php_mysql($data_per[1])) . "' ";
		$sql .= "AND AF9_REVISA = '".$regs_ult_rev["ULT_REVISA"]."' ";
		$sql .= "GROUP BY AF9_PROJET ";
		
		$db->select($sql, 'MSSQL', true);
		
		$reg_custo_prev = $db->array_select[0];
		
		//SOMA OS CUSTO NO PERIODO (realizado)
		$sql = "SELECT SUM(AFU_CUSTO1) AS CustoReal FROM AF8010 WITH(NOLOCK), AFU010 WITH(NOLOCK) ";
		$sql .= "WHERE AF8_PROJET = AFU_PROJET ";
		$sql .= "AND AF8_REVISA = AFU_REVISA ";
		$sql .= "AND AF8010.D_E_L_E_T_ = '' ";
		$sql .= "AND AFU010.D_E_L_E_T_ = '' ";
		$sql .= "AND AF8_PROJET = '" . $regs0["AF8_PROJET"] . "' ";
		$sql .= "AND AFU_DATA <= '" . mysql_protheus(php_mysql($data_per[1])) . "' ";
		$sql .= "GROUP BY AFU_PROJET ";
		
		$db->select($sql, 'MSSQL', true);
		
		$reg_custo_real = $db->array_select[0];
		
		$array_semanas_custo_prev[$regs0["AF8_PROJET"]][$data_per[1]] = $reg_custo_prev["CustoPrev"];
		
		$array_semanas_custo_real[$regs0["AF8_PROJET"]][$data_per[1]] = $reg_custo_real["CustoReal"];
	}
}
*/

if(is_file("modelos_excel/custos_acumulado_".$os.".xls"))
{
	$objPHPExcel = PHPExcel_IOFactory::load("modelos_excel/custos_acumulado_".$txt.".xls");
}
else
{
	$objPHPExcel = PHPExcel_IOFactory::load("modelos_excel/custos_acumulado_modelo.xls");
}

$locale = 'pt_br';

$validlocale = PHPExcel_Settings::setlocale($locale);

if (!$validlocale) 
{
	echo 'Unable to set locale to '.$locale." - reverting to en_us<br />\n";
}

// Redirect output to a clients web browser (Excel2007)
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

$objPHPExcel->setActiveSheetIndex(0);

//COLUNA A EXCELL
$coluna = 0;
$linha = 3;

$tot_cust_prev = 0;

$tot_cust_real = 0;

$tot_desp = 0;

foreach($array_proj as $projeto)
{
	//OS
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna, $linha, sprintf("%010d",$projeto));
	//rev. orc.
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+1, $linha, sprintf("%04d",$array_ver_orc[$projeto]));
	//rev. real.
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+2, $linha, sprintf("%04d",$array_ver_rea[$projeto]));
	//fase.
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+4, $linha, sprintf("%02d",$array_fase[$projeto]));
	
	foreach($array_semanas_custo_prev[$projeto] as $semanas1=>$custos)
	{	
		$linha++;
		//data
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+7, $linha, $semanas1);
		//custo previsto
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+10, $linha, $array_semanas_custo_prev[$projeto][$semanas1]);
		//custo real
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+13, $linha, $array_semanas_custo_real[$projeto][$semanas1]);
	
	}
	
	$linha+=2;
	
	$objPHPExcel->getActiveSheet()->setCellValue('J'.$linha, "SUB-TOTAL");
	//SUB-TOTAL custo previsto
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+10, $linha, $array_cust_prev[$projeto]);
	//SUB-TOTAL custo real
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+13, $linha, $array_cust_real[$projeto]);
	//SUB-TOTAL DESPESAS
	//$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+16, $linha, $array_desp[$projeto]);
	
	$tot_cust_prev += $array_cust_prev[$projeto];
	
	$tot_cust_real += $array_cust_real[$projeto];
	
	$tot_desp += $array_desp[$projeto];
	
	$linha+=2;	
}

$linha+=2;

$objPHPExcel->getActiveSheet()->setCellValue('J'.$linha, "TOTAL");
//TOTAL custo previsto
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+10, $linha, $tot_cust_prev);
//TOTAL custo real
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+13, $linha, $tot_cust_real);
//TOTAL despesas
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+16, $linha, $tot_desp);

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="custos_'.$txt.'.xlsx"');
header('Cache-Control: max-age=0');

$objWriter->save('php://output');

?>