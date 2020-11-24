<?php
/*
	Relat�rio de IDC/IDP
	Criado por Carlos Abreu  
	
	Vers�o 0 --> VERS�O INICIAL : 25/06/2015
	Vers�o 1 --> Inclus�o dos campos reg_del nas consultas - 17/11/2017 - Carlos Abreu
*/

ini_set('max_execution_time', 0); // No time limit
ini_set('post_max_size', '20M');
ini_set('memory_limit', '1024M');
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

//TABELA PA7 - COORDENADORES
$sql = "SELECT * FROM PA7010 WITH(NOLOCK) ";
$sql .= "WHERE PA7010.D_E_L_E_T_ = '' ";

$db->select($sql,'MSSQL',true);

if($db->erro!='')
{
	die($db->erro);
}

foreach($db->array_select as $regs)
{
	$array_coord[$regs["PA7_ID"]] = $regs["PA7_NOME"];
}

$filtro0 = "";

//filtra a os (-1 TODAS AS OS)
if($_POST["escolhafase"]!=-1)
{
	$filtro0 .= "AND AF1_FASE = '".$_POST["escolhafase"]."' ";
}

//filtra a os (-1 TODAS AS OS)
if($_POST["escolhaos"]!=-1)
{
	$filtro0 .= "AND AF1_ORCAME = '".sprintf("%010d",$_POST["escolhaos"])."' ";
}

if($_POST["escolhacoord"]!=-1)
{
	$filtro0 .= "AND (AF1_COORD1 = '".$_POST["escolhacoord"]."' ";
	$filtro0 .= "OR AF1_COORD2 = '".$_POST["escolhacoord"]."') ";
}

//Seleciona as PROJETOS
$sql = "SELECT * FROM AF8010 WITH(NOLOCK), AF1010 WITH(NOLOCK) ";
$sql .= "WHERE AF8010.D_E_L_E_T_ = '' ";
$sql .= "AND AF1010.D_E_L_E_T_ = '' ";
$sql .= "AND AF8_ORCAME = AF1_ORCAME ";
$sql .= "AND AF1_FASE IN ('04','09') ";
$sql .= "AND AF8_FASE IN ('03','07') ";
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
	$os = intval($regs1["AF1_ORCAME"]); //retira os zeros a esquerda
	
	$array_orcame[$regs1["AF1_ORCAME"]] = str_replace(array("'", '"', '\"', "\'"),"",$regs1["AF1_DESCRI"]);
	$array_coord[$regs1["AF1_ORCAME"]]= str_replace(array("'", '"', '\"', "\'"),"",$array_coord[$regs1["AF1_COORD1"]]);
	
	//TABELA AF2 - TAREFAS ORCAMENTO - CUSTO PREVISTO	
	$sql = "SELECT SUM(AF2_CUSTO) AS CUSTO_PREV FROM AF2010 WITH(NOLOCK) ";
	$sql .= "WHERE AF2010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF2_ORCAME = '".$regs1["AF1_ORCAME"]."' ";
	$sql .= "AND AF2_CODIGO <> '' ";
	$sql .= "AND AF2_GRPCOM NOT IN ('DES','SUP') ";
	//$sql .= "AND AF2_COMPOS NOT IN ('SUP12','SUP13','SUP14','SUP15','SUP16','SUP17') ";

	$db->select($sql,'MSSQL',true);

	if($db->erro!='')
	{
		die($db->erro);
	}
	
	$regs2 = $db->array_select[0];

	//CUSTO PREVISTO ORCAMENTO
	$array_cust_prev[$regs1["AF1_ORCAME"]] = $regs2["CUSTO_PREV"];
	
	//CUSTO REAL			
	//HORAS REALIZADAS			
	$sql = "SELECT * FROM AE8010 WITH(NOLOCK), AJK010 WITH(NOLOCK), AF8010 WITH(NOLOCK), AF9010 WITH(NOLOCK) ";
	$sql .= "WHERE AJK010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF8010.D_E_L_E_T_ = '' ";
	$sql .= "AND AE8010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF9010.D_E_L_E_T_ = '' ";
	$sql .= "AND AJK_CTRRVS = '1' ";
	$sql .= "AND AF8_PROJET = AF9_PROJET ";
	$sql .= "AND AF8_REVISA = AF9_REVISA ";
	$sql .= "AND AJK_PROJET = AF8_PROJET ";
	$sql .= "AND AJK_REVISA = AF8_REVISA ";
	$sql .= "AND AJK_TAREFA = AF9_TAREFA ";
	$sql .= "AND AJK_RECURS = AE8_RECURS ";
	$sql .= "AND AF8_ORCAME = '".$regs1["AF1_ORCAME"]."' ";

	$db->select($sql,'MSSQL',true);
	
	if($db->erro!='')
	{
		die($db->erro);
	}
	
	$array_horas = $db->array_select;

	foreach($array_horas as $regs6)
	{		
		$recurs = explode("_",$regs6["AJK_RECURS"]);	
		
		//Obtem o valor do salario na data
		$sql = "SELECT * FROM ".DATABASE.".salarios ";
		$sql .= "WHERE salarios.id_funcionario = '" . intval($recurs[1]) . "' ";
		$sql .= "AND DATE_FORMAT(data , '%Y%m%d' ) <= '".$regs6["AJK_DATA"]."' ";
		$sql .= "AND salarios.reg_del = 0 ";
		$sql .= "ORDER BY id_salario DESC, data DESC LIMIT 1 ";

		$db->select($sql,'MYSQL',true);

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
				
		$regs4 = $db->array_select[0];
  
		switch ($regs4[" tipo_contrato"])
		{
			case 'SC':
			case 'SC+CLT':

				$array_cust_real[$regs1["AF1_ORCAME"]] += round($regs4["salario_hora"]*$regs6["AJK_HQUANT"]*(0.975),2);
				
			break;
			
			case 'CLT':
			case 'EST':

				$array_cust_real[$regs1["AF1_ORCAME"]] += round((($regs4["salario_clt"]/176)*1.84*$regs6["AJK_HQUANT"]),2);
				
			break;
			
			case 'SC+MENS':
			case 'SC+CLT+MENS':

				$array_cust_real[$regs1["AF1_ORCAME"]] += round((($regs4["salario_mensalista"]/176)*$regs6["AJK_HQUANT"]*(0.975)),2);
				
			break;
	   }		
	}			
}

$objPHPExcel = PHPExcel_IOFactory::load("../modelos_excel/idc_idp_modelo.xls");

$locale = 'pt_br';

$validlocale = PHPExcel_Settings::setlocale($locale);

if (!$validlocale) 
{
	echo 'Unable to set locale to '.$locale." - reverting to en_us<br />\n";
}

// Redirect output to a client�s web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="idc_idp_"'.date('dmYHis').'".xlsx"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

//1� folha
$objPHPExcel->setActiveSheetIndex(0);

$linha = 2;

ksort($array_orcame);

foreach($array_orcame as $projeto=>$descricao)
{
	//Nome Projeto
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $linha, iconv('ISO-8859-1', 'UTF-8',sprintf("%010d",$projeto)));
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $linha, iconv('ISO-8859-1', 'UTF-8',$array_cust_prev[$projeto]));
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $linha, iconv('ISO-8859-1', 'UTF-8',$array_cust_real[$projeto]));
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $linha, iconv('ISO-8859-1', 'UTF-8','=B'.$linha.'-C'.$linha));
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $linha, iconv('ISO-8859-1', 'UTF-8','=B'.$linha.'*E'.$linha));
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, $linha, iconv('ISO-8859-1', 'UTF-8','=B'.$linha.'*F'.$linha));
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8, $linha, iconv('ISO-8859-1', 'UTF-8','=H'.$linha.'/C'.$linha));
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(9, $linha, iconv('ISO-8859-1', 'UTF-8','=H'.$linha.'/G'.$linha));
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(10, $linha, iconv('ISO-8859-1', 'UTF-8','=((B'.$linha.'-H'.$linha.')/(I'.$linha.'*J'.$linha.')+C'.$linha.')'));
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(11, $linha, iconv('ISO-8859-1', 'UTF-8','=B'.$linha.'-K'.$linha));
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(12, $linha, iconv('ISO-8859-1', 'UTF-8',$array_coord[$projeto]));
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(13, $linha, iconv('ISO-8859-1', 'UTF-8',$descricao));
	
	$linha++;	
}

$objPHPExcel->setActiveSheetIndex(0);

$objWriter->save('php://output');

exit;
?>