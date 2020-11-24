<?php
/*
	Relatório de ART
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

$db = new banco_dados();

$filtro0 = "";

$nome_projeto = "";

$projeto = "";

$nome_cliente = "";

$coordenador = "";

//filtra a os (-1 TODAS AS OS)
if($_POST["escolhaos"]!="")
{
	$filtro0 .= "AND AF1_ORCAME = '".sprintf("%010d",$_POST["escolhaos"])."' ";
}

if($_POST["escolhacoord"]!="")
{
	$filtro0 .= "AND (AF1_COORD1 = '".$_POST["escolhacoord"]."' ";
	$filtro0 .= "OR AF1_COORD2 = '".$_POST["escolhacoord"]."') ";
}

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

//Seleciona os PROJETOS
$sql = "SELECT * FROM AF1010 WITH(NOLOCK), SA1010 WITH(NOLOCK) ";
$sql .= "WHERE AF1010.D_E_L_E_T_ = '' ";
$sql .= "AND SA1010.D_E_L_E_T_ = '' ";
$sql .= "AND AF1010.AF1_CLIENT = SA1010.A1_COD ";
$sql .= "AND AF1010.AF1_LOJA = SA1010.A1_LOJA ";
$sql .= "AND AF1_FASE IN ('02','04') "; //ORCAMENTO APROVADO
$sql .= $filtro0;
$sql .= "ORDER BY AF1_ORCAME ";

$db->select($sql,'MSSQL',true);

if($db->erro!='')
{
	die($db->erro);
}

$regs1 = $db->array_select[0];

$os = intval($regs1["AF1_PROJET"]); //retira os zeros a esquerda

$sql = "SELECT nome_contato FROM ".DATABASE.".OS, ".DATABASE.".contatos ";
$sql .= "WHERE os.os = '". $os."' ";
$sql .= "AND OS.reg_del = 0 ";
$sql .= "AND contatos.reg_del = 0 ";
$sql .= "AND OS.id_cod_resp = contatos.id_contato ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}

$regs_os = $db->array_select[0];	
	
if($_POST["escolhaos"]!="" || $_POST["escolhacoord"]!="")
{
	$coordenador = $array_coord[$regs1["AF1_COORD1"]] . " - " .$array_coord[$regs1["AF1_COORD2"]];
}

$nome_projeto = trim($regs1["AF1_ORCAME"])." - ".trim($regs1["AF1_DESCRI"]);

$nome_cliente = trim($regs1["A1_NOME"]);

$projeto = trim($regs1["AF1_ORCAME"]);

//TABELA AF2 - TAREFAS ORCAMENTO - CUSTO PREVISTO	
$sql = "SELECT * FROM AF2010 WITH(NOLOCK) ";
$sql .= "WHERE AF2010.D_E_L_E_T_ = '' ";
$sql .= "AND AF2_ORCAME = '".$regs1["AF1_ORCAME"]."' ";
$sql .= "AND AF2_CODIGO <> '' ";
$sql .= "ORDER BY AF2_GRPCOM ";

$db->select($sql,'MSSQL',true);

if($db->erro!='')
{
	die($db->erro);
}

$array_cust = NULL;

foreach($db->array_select as $regs2)
{
	
	//verifica se a composição de subcontratados e sumariza
	//MEC/TUB/VAC
	if(in_array(trim($regs2["AF2_COMPOS"]),array('SUP16','SUP17')))
	{
		$array_cust[1] += $regs2["AF2_TOTAL"];
	}
	else
	{
		//eletrica/instrumentacao
		if(in_array(trim($regs2["AF2_COMPOS"]),array('SUP13','SUP14')))
		{
			$array_cust[3] += $regs2["AF2_TOTAL"];
		}
		else
		{
			//CIVIL/ESTRUTURA
			if(in_array(trim($regs2["AF2_COMPOS"]),array('SUP15')))
			{
				$array_cust[2] += $regs2["AF2_TOTAL"];
				
			}
			else
			{
				//OUTRAS SITUAÇÕES /GERAL
				switch (trim($regs2["AF2_GRPCOM"]))
				{
					case 'MEC':
					case 'TUB':
					case 'EBP':
					case 'VAC':
					case 'SEG':
					case 'PDM':
						$array_cust[1] += $regs2["AF2_TOTAL"];
					break;
					
					case 'CIV':
					case 'EST':
						$array_cust[2] += $regs2["AF2_TOTAL"];
					break;
					
					case 'ELE':
					case 'INS':
					case 'AUT':
						$array_cust[3] += $regs2["AF2_TOTAL"];
					break;
					
					case 'GER':
					case 'ART':
					case 'DES':
					case 'COR':
					case 'PLN':
					case 'SUP':
						$array_cust[4] += $regs2["AF2_TOTAL"];
					break;			
						
				}	
			}				
		}		
	}	
}

$objPHPExcel = PHPExcel_IOFactory::load("../modelos_excel/form_modelo_art.xls");

$locale = 'pt_br';

$validlocale = PHPExcel_Settings::setlocale($locale);

if (!$validlocale) 
{
	echo 'Unable to set locale to '.$locale." - reverting to en_us<br />\n";
}

// Redirect output to a clients web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="relatorio_art_"'.date('dmYHis').'".xlsx"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');


//1� folha
$objPHPExcel->setActiveSheetIndex(0);

//data emiss�o
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, 3, iconv('ISO-8859-1', 'UTF-8',"data de emissão: ".date('d/m/Y')));

//Nome Projeto
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, 5, iconv('ISO-8859-1', 'UTF-8',$nome_projeto));

//Nome cliente
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, 6, iconv('ISO-8859-1', 'UTF-8',$nome_cliente));

//Nome coordenador Devemada
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, 7, iconv('ISO-8859-1', 'UTF-8',$coordenador));

//Raz�o social
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, 15, iconv('ISO-8859-1', 'UTF-8',$nome_cliente));

$cnpj = substr($regs1["A1_CGC"], 0, 2) . '.' . substr($regs1["A1_CGC"], 2, 3) . 
	'.' . substr($regs1["A1_CGC"], 5, 3) . '/' . 
	substr($regs1["A1_CGC"], 8, 4) . '-' . substr($regs1["A1_CGC"], 12, 2);

//CNPJ
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, 16, iconv('ISO-8859-1', 'UTF-8',$cnpj));

if($regs1["A1_CEPART"]=='')
{
	$cep = substr($regs1["A1_CEP"], 0, 5) . '-' . substr($regs1["A1_CEP"], 5, 3);
}
else
{
	$cep = substr($regs1["A1_CEPART"], 0, 5) . '-' . substr($regs1["A1_CEPART"], 5, 3);
}

//cep
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, 17, iconv('ISO-8859-1', 'UTF-8',$cep));

//ENDERE�O 
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, 18, iconv('ISO-8859-1', 'UTF-8',trim($regs1["A1_END"]).', '.trim($regs1["A1_BAIRRO"]).', '.trim($regs1["A1_MUN"])));

//Nome coordenador cliente
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, 19, iconv('ISO-8859-1', 'UTF-8',trim($regs1["AF1_RESPTE"])));

//PEDIDO / CONTRATO
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, 20, iconv('ISO-8859-1', 'UTF-8',trim($regs1["AF1_CONTRA"])." - ".trim($regs1["AF1_PEDIDO"])));

//DATA INICIO
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, 22, iconv('ISO-8859-1', 'UTF-8',mysql_php(protheus_mysql($regs1["AF1_DTAPRO"]))));

//DATA FIM
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, 23, iconv('ISO-8859-1', 'UTF-8',mysql_php(protheus_mysql($regs1["AF8_DTATUF"]))));

//DESCRICAO OS
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, 30, iconv('ISO-8859-1', 'UTF-8',trim($regs1["AF1_DESCRI"])));

//Raz�o social
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, 33, iconv('ISO-8859-1', 'UTF-8',$nome_cliente));

$linha = 9;

//MEC/TUB/EBP
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $linha, $array_cust[1]);

//CIV/EST
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $linha+1, $array_cust[2]);

//ELE/AUT/INS
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $linha+2, $array_cust[3]);

//GERAL
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $linha+3, $array_cust[4]);


$objWriter->save('php://output');

exit;

?>