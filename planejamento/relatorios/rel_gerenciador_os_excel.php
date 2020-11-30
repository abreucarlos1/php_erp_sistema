<?php
/*
		Relat�rio de Gerenciador OS
		
		Criado por Carlos Abreu   
		
		local/Nome do arquivo:		
		../planejamento/relatorios/rel_gerenciador_os_excel.php
		
		Vers�o 0 --> VERS�O INICIAL - 05/01/2018 - Carlos Abreu
		Vers�o 1 --> Inclus�o de data GRD, valor Medido, valor venda - 11/01/2018 - Carlos Abreu
		Vers�o 2 --> Altera��o de fases chamado #2533 - 24/01/2018 - Carlos Abreu
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

//VERIFICA SE O USUARIO POSSUI ACESSO AO M�DULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(614))
{
	nao_permitido();
}

require_once(INCLUDE_DIR."PHPExcel/Classes/PHPExcel.php"); 

$db = new banco_dados();

$array_proj = NULL;

$array_pln = NULL;

$array_emissao = NULL;

$array_horas_prev = NULL;

$array_percentual = NULL;

//seleciona os percentuais medidos
$sql = "SELECT (SUM(bms_medicao.valor_medido)/bms_pedido.valor_pedido)*100 AS PERCENTUAL, bms_pedido.id_os AS OS FROM ".DATABASE.".bms_pedido, ".DATABASE.".bms_medicao, ".DATABASE.".bms_item "; 
$sql .= "WHERE bms_pedido.reg_del = 0 "; 
$sql .= "AND bms_medicao.reg_del = 0 "; 
$sql .= "AND bms_item.reg_del = 0 "; 
$sql .= "AND bms_pedido.id_bms_pedido = bms_medicao.id_bms_pedido "; 
$sql .= "AND bms_medicao.id_bms_item = bms_item.id_bms_item ";
//$sql .= "AND (bms_pedido.data_pedido >= 2017-07-01 OR bms_pedido.id_os IN (SELECT os FROM ".DATABASE.".bms_excecoes WHERE reg_del = 0)) ";
$sql .= "GROUP BY bms_pedido.id_os ";
$sql .= "ORDER BY bms_pedido.id_os ";

$db->select($sql, 'MYSQL', true);

$array_perc = $db->array_select;

foreach($array_perc as $regs)
{
	$array_percentual[sprintf("%010d",$regs["os"])] = $regs["PERCENTUAL"]; 
}

//TABELA AF2 - TAREFAS ORCAMENTO - HORAS PREVISTAS
$sql = "SELECT SUM(AF3_QUANT) AS HORAS_PREV, AF2_ORCAME FROM AF2010 WITH(NOLOCK), AF3010 WITH(NOLOCK) "; 
$sql .= "WHERE AF2010.D_E_L_E_T_ = '' "; 
$sql .= "AND AF3010.D_E_L_E_T_ = '' "; 
$sql .= "AND AF3_TAREFA = AF2_TAREFA "; 
$sql .= "AND AF3_ORCAME = AF2_ORCAME "; 
$sql .= "AND AF2_CODIGO <> '' ";
$sql .= "AND AF2_GRPCOM <> 'DES' ";
$sql .= "AND AF2_COMPOS NOT IN ('SUP12','SUP13','SUP14','SUP15','SUP16','SUP17') ";    
$sql .= "GROUP BY AF2_ORCAME ";
$sql .= "ORDER BY AF2_ORCAME ";	

$db->select($sql,'MSSQL', true);

if($db->erro!='')
{
	die($db->erro);
}

$array_prev = $db->array_select;

foreach($array_prev as $regs)
{
	$array_horas_prev[trim($regs["AF2_ORCAME"])] = $regs["HORAS_PREV"]; 
}

//seleciona as GRDs (ultima emiss�o)
$sql = "SELECT MAX(grd.data_emissao) AS emissao, os.os FROM ".DATABASE.".grd, ".DATABASE.".OS ";
$sql .= "WHERE grd.reg_del = 0 ";
$sql .= "AND OS.reg_del = 0 ";
$sql .= "AND grd.id_os = OS.id_os ";
$sql .= "GROUP BY OS.id_os ";
$sql .= "ORDER BY os.os ";

$db->select($sql, 'MYSQL', true);

$array_grd = $db->array_select;

foreach($array_grd as $regs)
{
	$array_emissao[sprintf("%010d",$regs["os"])] = $regs["emissao"]; 
}

//seleciona os planejadores
$sql = "SELECT AF8_PROJET, AE8_RECURS, AE8_DESCRI FROM AF8010, AF9010, AFA010, AE8010 ";
$sql .= "WHERE AF8010.D_E_L_E_T_ = '' ";
$sql .= "AND AF9010.D_E_L_E_T_ = '' ";
$sql .= "AND AFA010.D_E_L_E_T_ = '' ";
$sql .= "AND AE8010.D_E_L_E_T_ = '' ";
$sql .= "AND AF9_PROJET = AF8_PROJET ";
$sql .= "AND AF9_REVISA = AF8_REVISA ";
$sql .= "AND AE8_EQUIP = '0000000005' "; //PLANEJAMENTO
$sql .= "AND AF8_PROJET > '0000004000' ";
$sql .= "AND AE8_RECURS NOT LIKE 'ORC_%' ";

if(intval($_POST["fase"])>0)
{
	//$sql .= "AND AF8_FASE = '".$_POST["fase"]."' ";
	
	switch (intval($_POST["fase"]))
	{
		case 3:
		case 7:
			$array_fase = array('03','07');
		break;
		
		case 5:
		case 11:
			$array_fase = array('05','11');
		break;
		
		default:
			$array_fase = array($_POST["fase"]);
	}
	
	$sql .= "AND AF8_FASE IN ('".implode("','",$array_fase)."') ";	
	
}

$sql .= "AND AF8_FASE NOT IN ('01','06','08','10','17','18','13','4','09') ";
$sql .= "AND AF9_PROJET = AFA_PROJET ";
$sql .= "AND AF9_REVISA = AFA_REVISA ";
$sql .= "AND AF9_TAREFA = AFA_TAREFA ";
$sql .= "AND AFA_RECURS = AE8_RECURS ";

$db->select($sql, 'MSSQL', true);

$array_planejadores = $db->array_select;

foreach($array_planejadores as $regs)
{
	$array_pln[trim($regs["AF8_PROJET"])][trim($regs["AE8_RECURS"])] = trim($regs["AE8_DESCRI"]); 
}

//Seleciona as OSs
$sql = "SELECT * FROM AF8010, AF1010, SA1010, AEA010, PA7010 ";
$sql .= "WHERE AF8010.D_E_L_E_T_ = '' ";
$sql .= "AND AF1010.D_E_L_E_T_ = '' ";
$sql .= "AND SA1010.D_E_L_E_T_ = '' ";
$sql .= "AND PA7010.D_E_L_E_T_ = '' ";
$sql .= "AND AEA010.D_E_L_E_T_ = '' ";

if(intval($_POST["fase"])>0)
{
	//$sql .= "AND AF8_FASE = '".$_POST["fase"]."' ";
	$sql .= "AND AF8_FASE IN ('".implode("','",$array_fase)."') ";
}

$sql .= "AND AF8_FASE NOT IN ('01','06','08','10','17','18','13','4','09') ";
$sql .= "AND AF8_PROJET > '0000004000' ";
$sql .= "AND AF8_FASE = AEA_COD ";
$sql .= "AND AF8_ORCAME = AF1_ORCAME ";
$sql .= "AND AF1_CLIENT = A1_COD ";
$sql .= "AND AF1_LOJA = A1_LOJA ";
$sql .= "AND AF1_COORD1 = PA7_ID ";
$sql .= "ORDER BY AF8_PROJET ";

$db->select($sql, 'MSSQL', true);

$array_projetos = $db->array_select;

foreach($array_projetos as $regs)
{	
	$array_proj['projeto'][trim($regs["AF1_ORCAME"])] = trim($regs["AF1_ORCAME"]);
	$array_proj['descricao'][trim($regs["AF1_ORCAME"])] = trim($regs["AF1_DESCRI"]);
	$array_proj['cliente'][trim($regs["AF1_ORCAME"])] = trim($regs["A1_NOME"]);
	$array_proj['fase'][trim($regs["AF1_ORCAME"])] = trim($regs["AEA_DESCRI"]);
	$array_proj['coordenador'][trim($regs["AF1_ORCAME"])] = trim($regs["PA7_NOME"]);
	$array_proj['data_aprov'][trim($regs["AF1_ORCAME"])] = protheus_mysql(trim($regs["AF1_DTAPRO"]));
	$array_proj['data_emissao'][trim($regs["AF1_ORCAME"])] = $array_emissao[trim($regs["AF1_ORCAME"])];
	$array_proj['horas_prev'][trim($regs["AF1_ORCAME"])] = $array_horas_prev[trim($regs["AF1_ORCAME"])];
	$array_proj['percentual'][trim($regs["AF1_ORCAME"])] = $array_percentual[trim($regs["AF1_ORCAME"])];
}

//die($sql . '-' . print_r($array_proj,true));

$objPHPExcel = PHPExcel_IOFactory::load("../modelos_excel/gerenciador_os_modelo.xls");

$locale = 'pt_br';

$validlocale = PHPExcel_Settings::setlocale($locale);

if (!$validlocale) 
{
	echo 'Unable to set locale to '.$locale." - reverting to en_us<br />\n";
}

// Redirect output to a client�s web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="gerenciador_os_'.date('dmYhis').'.xlsx"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

$objPHPExcel->setActiveSheetIndex(0);

$linha = 3; //linha

foreach($array_proj['projeto'] as $projeto)
{
	//$objPHPExcel->getActiveSheet()->setCellValue('B2', iconv('ISO-8859-1', 'UTF-8', $descricao));
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $linha,iconv('ISO-8859-1', 'UTF-8', $projeto));
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $linha,iconv('ISO-8859-1', 'UTF-8', $array_proj['descricao'][$projeto]));
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $linha,iconv('ISO-8859-1', 'UTF-8', $array_proj['cliente'][$projeto]));
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $linha,iconv('ISO-8859-1', 'UTF-8', $array_proj['fase'][$projeto]));
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, $linha,iconv('ISO-8859-1', 'UTF-8', $array_proj['coordenador'][$projeto]));
	
	$array_nome_plan = NULL;
	
	foreach($array_pln[$projeto] as $descricao)
	{
		$array_nome_plan[] = $descricao;
	}
	
	$nomes = implode(',',$array_nome_plan);
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, $linha,iconv('ISO-8859-1', 'UTF-8', $nomes));
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $linha,iconv('ISO-8859-1', 'UTF-8', mysql_php($array_proj['data_aprov'][$projeto])));
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, $linha,iconv('ISO-8859-1', 'UTF-8', number_format($array_proj['horas_prev'][$projeto],2,'.','')));
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8, $linha,iconv('ISO-8859-1', 'UTF-8', mysql_php($array_proj['data_emissao'][$projeto])));
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(9, $linha,iconv('ISO-8859-1', 'UTF-8', number_format($array_proj['percentual'][$projeto],2,'.','')));
	
	$linha++;
}

$objWriter->save('php://output');

exit;

?>