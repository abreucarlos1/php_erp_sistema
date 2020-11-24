<?php
/*
	Relatório de Custo por centros de custo
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

$array_centro_custo = NULL;

$array_recursos = NULL;

$array_custo_pj = NULL;

$array_custo_clt = NULL;

$array_custo_mens = NULL;

$array_funcoes = NULL;

$sql = "SELECT * FROM CTT010 WITH(NOLOCK) ";
$sql .= "WHERE CTT010.D_E_L_E_T_ = '' ";
$sql .= "AND CTT_BLOQ = '2' "; //SOMENTE OS CC NÃO BLOQUEADOS
$sql .= "ORDER BY CTT010.CTT_CUSTO ";

$db->select($sql,'MSSQL', true);

if($db->erro!='')
{
	die($db->erro);
}	

foreach($db->array_select as $regs)
{
	$array_centro_custo[trim($regs["CTT_CUSTO"])] = $regs["CTT_DESC01"];
}

//filtra a os (-1 TODAS AS OS)
if($_POST["escolhacc"]!=-1)
{
	$filtro0 .= "AND funcionarios.id_centro_custo = '".$_POST["escolhacc"]."' ";
}

//Seleciona os funcionarios
$sql = "SELECT * FROM ".DATABASE.".setores, ".DATABASE.".funcionarios, ".DATABASE.".rh_funcoes ";
$sql .= "WHERE funcionarios.situacao NOT IN ('DESLIGADO','CANCELADO') ";
$sql .= "AND setores.reg_del = 0 ";
$sql .= "AND funcionarios.reg_del = 0 ";
$sql .= "AND rh_funcoes.reg_del = 0 ";
$sql .= "AND funcionarios.id_funcao = rh_funcoes.id_funcao ";
$sql .= "AND funcionarios.id_setor = setores.id_setor ";
$sql .= $filtro0;
$sql .= "ORDER BY funcionarios.id_centro_custo, funcionarios.funcionario ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}

$array_func = $db->array_select;

foreach($array_func as $regs1)
{
	//CUSTO UNITARIO
	//Obtem o valor do salario na data
	$sql = "SELECT * FROM ".DATABASE.".salarios ";
	$sql .= "WHERE salarios.id_funcionario = '" . $regs1["id_funcionario"] . "' ";
	$sql .= "AND salarios.reg_del = 0 ";
	$sql .= "ORDER BY data DESC, id_salario DESC LIMIT 1 ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
			
	$regs2 = $db->array_select[0];

	switch ($regs2[" tipo_contrato"])
	{
		case 'SC':
		case 'SC+CLT':	

			$array_custo_pj[$regs1["id_funcionario"]] = $regs2["salario_hora"];
			$tipo_trabalho = "H";
			
		break;
		
		case 'CLT':
		case 'EST':

			$array_custo_clt[$regs1["id_funcionario"]] = $regs2["salario_clt"];
			$tipo_trabalho = "M";
			
		break;
		
		case 'SC+MENS':
		case 'SC+CLT+MENS':
		
			$array_custo_mens[$regs1["id_funcionario"]] = $regs2["salario_mensalista"];
			$tipo_trabalho = "M";			
			
		break;						
	}	
	
	//tipo de contratacao
	if($regs1["id_centro_custo"]>11200)
	{
		$tipo = "P".$tipo_trabalho;	
	}
	else
	{
		$tipo = "A".$tipo_trabalho;	
	}
	
	$array_recursos[$regs1["id_centro_custo"]][$regs1["id_funcionario"]] = array($array_centro_custo[$regs1["id_centro_custo"]],$regs1["funcionario"],$regs1["descricao"],$regs1["setor"],$tipo,$array_custo_pj[$regs1["id_funcionario"]],$array_custo_mens[$regs1["id_funcionario"]],$array_custo_clt[$regs1["id_funcionario"]]);
}

$objPHPExcel = PHPExcel_IOFactory::load("../modelos_excel/custo_uni_cc_modelo.xls");

$locale = 'pt_br';

$validlocale = PHPExcel_Settings::setlocale($locale);

if (!$validlocale) 
{
	echo 'Unable to set locale to '.$locale." - reverting to en_us<br />\n";
}

// Redirect output to a clients web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="custos_uni_cc_"'.date('His').'".xlsx"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

//1� folha
$objPHPExcel->setActiveSheetIndex(0);

$linha = 9;

foreach($array_recursos as $centro_custo=>$recursos)
{
	foreach($recursos as $array_funcionario)
	{
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $linha, $centro_custo);
		
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $linha, $array_funcionario[0]);
		
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $linha, $array_funcionario[1]);
		
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, $linha, $array_funcionario[2]);
		
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8, $linha, $array_funcionario[3].$centro_custo);
		
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(9, $linha, $array_funcionario[4]);
		
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(10, $linha, $array_funcionario[5]);
		
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(11, $linha, "=+M".$linha."/176");
		
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(12, $linha, $array_funcionario[6]);
		
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(13, $linha, "=+O".$linha."/176");
		
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(14, $linha, $array_funcionario[7]);

		$linha++;
	}

}

$objPHPExcel->setActiveSheetIndex(0);

$objWriter->save('php://output');

exit;

?>