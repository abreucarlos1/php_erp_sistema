<?php
/*
	Relat�rio de Produto x Fornecedor
	Criado por Carlos Abreu  
	
	Vers�o 0 --> VERS�O INICIAL : 19/01/2017
	Vers�o 1 --> Inclus�o dos campos reg_del nas consultas - 17/11/2017 - Carlos Abreu
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

$nome_cliente = "";

$coordenador = "";

//filtra a os
if($_POST["escolhaos"]!=0)
{
	$filtro0 .= "AND AF1_ORCAME = '".sprintf("%010d",$_POST["escolhaos"])."' ";
}

if($_POST["escolhacoord"]!=-1)
{
	$filtro0 .= "AND (AF1_COORD1 = '".$_POST["escolhacoord"]."' ";
	$filtro0 .= "OR AF1_COORD2 = '".$_POST["escolhacoord"]."') ";
}

if($_POST["escolhafase"]!=-1)
{
	$filtro0 .= "AND AF8_FASE = '".$_POST["escolhafase"]."' ";
}

//TABELA PA7 - COORDENADORES
$sql = "SELECT PA7_ID, PA7_NOME FROM PA7010 WITH(NOLOCK) ";
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

//Seleciona as PROJETOS
$sql = "SELECT * FROM AF8010 WITH(NOLOCK), AF1010 WITH(NOLOCK), SA1010 WITH(NOLOCK) ";
$sql .= "WHERE AF8010.D_E_L_E_T_ = '' ";
$sql .= "AND AF1010.D_E_L_E_T_ = '' ";
$sql .= "AND SA1010.D_E_L_E_T_ = '' ";
$sql .= "AND AF1010.AF1_CLIENT = SA1010.A1_COD ";
$sql .= "AND AF1010.AF1_LOJA = SA1010.A1_LOJA ";
$sql .= "AND AF8_ORCAME = AF1_ORCAME ";
$sql .= "AND AF1_FASE IN ('04') "; //ORCAMENTO APROVADO
$sql .= $filtro0;
$sql .= "ORDER BY AF1_ORCAME ";

$db->select($sql,'MSSQL',true);

if($db->erro!='')
{
	die($db->erro);
}

$regs1 = $db->array_select[0];

$os = intval($regs1["AF8_PROJET"]); //retira os zeros a esquerda

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
	
if($_POST["escolhaos"]!=-1 || $_POST["escolhacoord"]!=-1)
{
	$coordenador = $array_coord[$regs1["AF1_COORD1"]] . " - " .$array_coord[$regs1["AF1_COORD2"]];
}

if($nome_projeto=="")
{
	$nome_projeto = $regs1["AF8_PROJET"]." - ".trim($regs1["AF8_DESCRI"]);
}

if($nome_cliente=="")
{
	$nome_cliente = trim($regs1["A1_NOME"]);
}

//Chamado 1896 - Ewerton Paiva
if ($_POST["escolhaos"] == '0000000900')
{
	$regs1['AF1_ORCAME'] = '0000000900';
}

//armazena as tarefas para buscar o custo real
//PEGA O VALOR PELA NF DE ENTRADA
$sql = "SELECT * FROM SC7010 WITH(NOLOCK) ";
$sql .= "WHERE SC7010.D_E_L_E_T_ = '' ";
$sql .= "AND SUBSTRING (C7_CLVL,9,10) = '".$regs1["AF1_ORCAME"]."' ";

if ($_POST["escolhaos"] != '0000000900')
{
	$sql .= "AND C7_CONTA IN (411005001,411006001) ";			
}

$db->select($sql,'MSSQL',true);

foreach($db->array_select as $regs2)
{
	$array_prod[] = array(sprintf("%06d",$regs2["C7_NUM"]),$regs2["C7_PRODUTO"],$regs2["C7_TOTAL"],sprintf("%06d",$regs2["C7_FORNECE"]),trim($regs2["C7_DESCRI"].' / '.$regs2["C7_OBS"]));
}

$objPHPExcel = PHPExcel_IOFactory::load("../modelos_excel/prod_fornec_modelo.xls");

$locale = 'pt_br';

$validlocale = PHPExcel_Settings::setlocale($locale);

if (!$validlocale) 
{
	echo 'Unable to set locale to '.$locale." - reverting to en_us<br />\n";
}

// Redirect output to a client�s web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="prod_fornec_"'.date('dmYHis').'".xlsx"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

//1� folha
$objPHPExcel->setActiveSheetIndex(0);

//data emiss�o
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, 3, iconv('ISO-8859-1', 'UTF-8',"data de emiss�o: ".date('d/m/Y')));

//Nome Projeto
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, 5, iconv('ISO-8859-1', 'UTF-8',$nome_projeto));

//Nome cliente
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, 6, iconv('ISO-8859-1', 'UTF-8',$nome_cliente));

//Nome coordenador Devemada
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, 7, iconv('ISO-8859-1', 'UTF-8',$coordenador));

//Nome coordenador cliente
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, 8, iconv('ISO-8859-1', 'UTF-8',$regs_os["nome_contato"]));

$linha = 12;

//CUSTO PREVISTO DISCIPLINAS
foreach($array_prod as $array_produtos)
{
		
	$objPHPExcel->getActiveSheet()->insertNewRowBefore($linha,1);
	
	$objPHPExcel->getActiveSheet()->mergeCells("C".$linha.":G".$linha);	
			
	//produto
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $linha, iconv('ISO-8859-1', 'UTF-8',$array_produtos[1]));

	//fornecedor
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $linha, iconv('ISO-8859-1', 'UTF-8',$array_produtos[3]));

	//descricao
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $linha, $array_produtos[4]);
	
	//numero
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, $linha, $array_produtos[0]);
	
	//total
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8, $linha, $array_produtos[2]);

	$linha+=1;
}

$objWriter->save('php://output');

exit;
?>
