<?php
/*
	Relatório de OS principal e adicionais
	Criado por Carlos Abreu  
	
	Versão 0 --> VERSÃO INICIAL : 19/01/2017
	Versão 1 --> Inclusão dos campos reg_del nas consultas - 17/11/2017 - Carlos Abreu
*/

ini_set('max_execution_time', 0); // No time limit
ini_set('post_max_size', '20M');
ini_set('memory_limit', '512M');
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

$filtro0 = "";

//filtra a os (-1 TODAS AS OS)
if($_POST["escolhaos"]!=-1)
{
	$filtro0 = "AND AF1_ORCAME = '".sprintf("%010d",$_POST["escolhaos"])."' ";
}

//Seleciona as PROJETOS PRINCIPAIS
$sql = "SELECT * FROM AF1010 WITH(NOLOCK) ";
$sql .= "WHERE AF1010.D_E_L_E_T_ = '' ";
$sql .= "AND AF1_ORCAME IN (SELECT AF1_RAIZ FROM AF1010 WITH(NOLOCK) WHERE AF1010.D_E_L_E_T_ = '') ";
$sql .= $filtro0;
$sql .= "ORDER BY AF1_ORCAME ";

$db->select($sql,'MSSQL', true);

if($db->erro!='')
{
	die($db->erro);
}

$array_proj = $db->array_select;

foreach($array_proj as $regs1)
{	
	//Seleciona as PROJETOS ADICIONAIS
	$sql = "SELECT * FROM AF1010 WITH(NOLOCK) ";
	$sql .= "WHERE AF1010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF1_RAIZ = '".$regs1["AF1_ORCAME"]."' ";
	$sql .= "ORDER BY AF1_ORCAME ";
	
	$db->select($sql,'MSSQL', true);
	
	if($db->erro!='')
	{
		die($db->erro);
	}
	
	$array_adic = $db->array_select;
	
	foreach($array_adic as $regs2)
	{
		//[PRINCIPAL][ADICIONAIS]
		$array_projetos[$regs1["AF1_ORCAME"]][$regs2["AF1_ORCAME"]] = 1;	
	}	
}

$objPHPExcel = PHPExcel_IOFactory::load("../modelos_excel/os_principal_x_adicionais.xls");

$locale = 'pt_br';

$validlocale = PHPExcel_Settings::setlocale($locale);

if (!$validlocale) 
{
	echo 'Unable to set locale to '.$locale." - reverting to en_us<br />\n";
}

// Redirect output to a clients web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="os_principal_x_adicionais_"'.date('His').'".xlsx"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

//1ª folha
$objPHPExcel->setActiveSheetIndex(0);

//data emissão
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, 3, iconv('ISO-8859-1', 'UTF-8',"Data de emissão: ".date('d/m/Y')));

$linha = 6;

foreach ($array_projetos as $principais=>$array_adicionais)
{
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $linha, iconv('ISO-8859-1', 'UTF-8',$principais));
	
	foreach ($array_adicionais as $adicionais=>$valor)
	{
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $linha, iconv('ISO-8859-1', 'UTF-8',$adicionais));
		
		$linha++;
	}
	
	$linha++;
}

$objPHPExcel->setActiveSheetIndex(0);

$objWriter->save('php://output');

exit;
?>