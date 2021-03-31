<?php
/*
	Relatório de Vendas Orçamento
	Criado por Carlos Abreu  
	
	Versão 0 --> VERSÃO INICIAL : 19/01/2015
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

$db = new banco_dados;

//SELECIONA O COORDENADOR PRINCIPAL
$sql = "SELECT * FROM PA7010 WITH(NOLOCK) ";
$sql .= "WHERE PA7010.D_E_L_E_T_ = '' ";

$db->select($sql,'MSSQL', true);

foreach($db->array_select as $regs1)
{
	$array_coordenadores[$regs1["PA7_ID"]] = str_replace(array("'", '"', '\"', "\'"),"",$regs1["PA7_NOME"]);	
}

$os = $_POST["escolhaos"];

$filtro0 = "";

if($os!=-1)
{
	$filtro0 .= "AND AF1_ORCAME = '".$os."' ";
}

if($_POST["escolhacoord"]!=-1)
{
	$filtro0 .= "AND AF1_COORD1 = '".sprintf("%04d",$_POST["escolhacoord"])."' ";
}

$sql = "SELECT * FROM AF1010 WITH(NOLOCK), SA1010 WITH(NOLOCK) ";
$sql .= "WHERE AF1010.D_E_L_E_T_ = '' ";
$sql .= "AND SA1010.D_E_L_E_T_ = '' ";
$sql .= "AND AF1_CLIENT = A1_COD ";
$sql .= "AND AF1_LOJA = A1_LOJA ";
$sql .= "AND AF1_FASE IN ('01') ";
$sql .= "AND AF1_ORCAME > 0000003000 ";
$sql .= $filtro0;
$sql .= "ORDER BY AF1_ORCAME ";

$db->select($sql, 'MSSQL', true);

if ($db->erro != '')
{
	exit($db->erro);
}

$array_orc = $db->array_select;

foreach($array_orc as $regs0)
{	
	$array_proj[$regs0["AF1_ORCAME"]] = str_replace(array("'", '"', '\"', "\'"),"",$regs0["AF1_DESCRI"]);
	
	$array_proj_status[$regs0["AF1_ORCAME"]] = str_replace(array("'", '"', '\"', "\'"),"",$regs0["AE9_DESCRI"]);
	
	$array_desc_cliente[$regs0["AF1_ORCAME"]] = str_replace(array("'", '"', '\"', "\'"),"",$regs0["AF1_DESCRE"]);
	
	$array_nome_cliente[$regs0["AF1_ORCAME"]] = str_replace(array("'", '"', '\"', "\'"),"",$regs0["A1_NOME"]);
	
	$array_unidade_cliente[$regs0["AF1_ORCAME"]] = str_replace(array("'", '"', '\"', "\'"),"",trim($regs0["A1_MUN"])." - ".trim($regs0["A1_EST"]));
	
	$array_nome_fantasia[$regs0["AF1_ORCAME"]] = str_replace(array("'", '"', '\"', "\'"),"",$regs0["A1_NREDUZ"]);
	
	$array_dt_soli[$regs0["AF1_ORCAME"]] = $regs0["AF1_DTSOLI"];
	
	$array_dt_entrega[$regs0["AF1_ORCAME"]] = $regs0["AF1_DTENTR"];
	
	$array_dt_entrega_real[$regs0["AF1_ORCAME"]] = $regs0["AF1_DTENRE"];
	
	$array_dt_projeto[$regs0["AF1_ORCAME"]] = $regs0["AF1_DATA"];
	
	$array_exec1[$regs0["AF1_ORCAME"]] = str_replace(array("'", '"', '\"', "\'"),"",$regs0["AF1_EXECU1"]);
	
	$array_exec2[$regs0["AF1_ORCAME"]] = str_replace(array("'", '"', '\"', "\'"),"",$regs0["AF1_EXECU2"]);
	
	$array_exec3[$regs0["AF1_ORCAME"]] = str_replace(array("'", '"', '\"', "\'"),"",$regs0["AF1_EXECU3"]);
	
	$array_exec4[$regs0["AF1_ORCAME"]] = str_replace(array("'", '"', '\"', "\'"),"",$regs0["AF1_EXECU4"]);	
	
	$array_coord_princ[$regs0["AF1_ORCAME"]] = $array_coordenadores[$regs0["AF1_COORD1"]];
	
	switch ($regs0["AF1_VENDED"])
	{
		case 1:
			$array_vendedor[$regs0["AF1_ORCAME"]] = 'Vendedor 1';
		break;
		
		case 2:
			$array_vendedor[$regs0["AF1_ORCAME"]] = 'Vendedor 2';
		break;
		
		case 3:
			$array_vendedor[$regs0["AF1_ORCAME"]] = 'Contrato guarda-chuva';
		break;
		
		default:
			$array_vendedor[$regs0["AF1_ORCAME"]] = '';							
	}	
	
	//TABELA AFC - ESFORÇO E LINHA DE BASE
	$sql = "SELECT * FROM AFC010 WITH(NOLOCK) ";
	$sql .= "WHERE AFC010.D_E_L_E_T_ = '' ";
	$sql .= "AND AFC010.AFC_PROJET = '".$regs0["AF1_ORCAME"]."' ";
	$sql .= "AND AFC010.AFC_REVISA = '0002' ";
	
	$db->select($sql, 'MSSQL', true);
	
	if ($db->erro != '')
	{
		exit($db->erro);
	}

	$regs6 = $db->array_select[0];
	
	$array_projet_start[$regs0["AF1_ORCAME"]] = $regs6["AFC_START"];
	
	$array_projet_finish[$regs0["AF1_ORCAME"]] = $regs6["AFC_FINISH"];	
}

$objPHPExcel = PHPExcel_IOFactory::load("../modelos_excel/orcamento_modelo.xls");

$locale = 'pt_br';

$validlocale = PHPExcel_Settings::setlocale($locale);

if (!$validlocale) 
{
	echo 'Unable to set locale to '.$locale." - reverting to en_us<br />\n";
}

// Redirect output to a clients web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="orcamento_"'.date('dmYHis').'".xlsx"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

$objPHPExcel->setActiveSheetIndex(0);

//COLUNA A EXCELL
$coluna = 0;
$linha = 4;

//acrescenta linhas conforme quantidade de projetos
$objPHPExcel->getActiveSheet()->insertNewRowBefore(5,count($array_proj));

foreach($array_proj as $projeto=>$descricao)
{
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna, $linha, iconv('ISO-8859-1', 'UTF-8',$array_coord_princ[$projeto]));
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+1, $linha, iconv('ISO-8859-1', 'UTF-8',$array_vendedor[$projeto]));
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+2, $linha, iconv('ISO-8859-1', 'UTF-8',$array_nome_cliente[$projeto]));
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+3, $linha, iconv('ISO-8859-1', 'UTF-8',$array_unidade_cliente[$projeto]));
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+4, $linha, iconv('ISO-8859-1', 'UTF-8',$array_nome_fantasia[$projeto]));
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+5, $linha, $projeto);
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+6, $linha, iconv('ISO-8859-1', 'UTF-8',$descricao));
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+7, $linha, $array_proj_status[$projeto]);
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+8, $linha, mysql_php(protheus_mysql($array_dt_soli[$projeto])));
	
	if($array_dt_entrega[$projeto]<date('Ymd'))
	{
		$objPHPExcel->getActiveSheet()->getStyle("J".($linha).":J".($linha))->getFill()->applyFromArray(array('type'=> PHPExcel_Style_Fill::FILL_SOLID,'startcolor' => array('rgb' => 'FF0000'),));
	}
		
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+10, $linha, mysql_php(protheus_mysql($array_dt_entrega_real[$projeto])));
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+11, $linha, mysql_php(protheus_mysql($array_dt_projeto[$projeto])));
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+12, $linha, iconv('ISO-8859-1', 'UTF-8',$array_exec1[$projeto]));
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+13, $linha, iconv('ISO-8859-1', 'UTF-8',$array_exec2[$projeto]));
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+14, $linha, iconv('ISO-8859-1', 'UTF-8',$array_exec3[$projeto]));
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+15, $linha, iconv('ISO-8859-1', 'UTF-8',$array_exec4[$projeto]));

	$linha+=1;	
}

$objWriter->save('php://output');

exit;
?>