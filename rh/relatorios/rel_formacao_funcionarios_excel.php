<?php
/*
		Relatorio formação funcionarios
		
		Criado por Carlos Abreu
		
		local/Nome do arquivo:
		../rh/relatorios/rel_formacao_funcionarios_excel.php
		
		Versão 0 --> VERSÃO INICIAL - 04/05/2006
		Versão 1 --> Inclusão dos campos reg_del nas consultas - 27/11/2017 - Carlos Abreu	
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

require_once(implode(DIRECTORY_SEPARATOR,array('..','..','config.inc.php')));

require_once(INCLUDE_DIR."PHPExcel/Classes/PHPExcel.php");

$db = new banco_dados();

$sql = "SELECT * FROM ".DATABASE.".funcionarios, ".DATABASE.".rh_funcoes, ".DATABASE.".setores ";
$sql .= "WHERE funcionarios.id_funcao = rh_funcoes.id_funcao ";
$sql .= "AND funcionarios.reg_del = 0 ";
$sql .= "AND rh_funcoes.reg_del = 0 ";
$sql .= "AND setores.reg_del = 0 ";
$sql .= "AND funcionarios.id_setor = setores.id_setor ";
$sql .= "AND funcionarios.situacao = 'ATIVO' ";
$sql .= "GROUP BY funcionarios.id_funcionario ";
$sql .= "ORDER BY funcionarios.funcionario ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}

$array_func = $db->array_select;

foreach($array_func as $regs0)
{		
	$array_cont_func[$regs0["id_funcionario"]] = $regs0["funcionario"];
	$array_cont_cargo[$regs0["id_funcionario"]] = $regs0["descricao"];
	$array_cont_setor[$regs0["id_funcionario"]] = $regs0["setor"];
	
	$sql = "SELECT * FROM ".DATABASE.".rh_formacao ";
	$sql .= "WHERE rh_formacao.id_funcionario = '" . $regs0["id_funcionario"] . "' ";
	$sql .= "AND rh_formacao.reg_del = 0 ";
	$sql .= "ORDER BY ano_conclusao DESC ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		die($db->erro);
	}
	
	$i = 0;
	
	foreach ($db->array_select as $regs1)
	{
		$array_cont_formacao[$regs0["id_funcionario"]][$i] = $regs1["descricao"];
		$array_cont_conclusao[$regs0["id_funcionario"]][$i] = $regs1["ano_conclusao"];
		
		$i++;
	}
	
}


$objPHPExcel = PHPExcel_IOFactory::load("../modelos_excel/formacao_funcionario_modelo.xls");

$locale = 'pt_br';

$validlocale = PHPExcel_Settings::setlocale($locale);

if (!$validlocale) 
{
	echo 'Unable to set locale to '.$locale." - reverting to en_us<br />\n";
}

// Redirect output to a clients web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="movimento_'.date('d-m-Y').'.xlsx"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

$objPHPExcel->setActiveSheetIndex(0);

//COLUNA A EXCELL
$coluna = 0;

$linha = 3;

foreach($array_cont_func as $id_funcionario=>$funcionario)
{
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna, $linha, $funcionario);
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+2, $linha, $array_cont_cargo[$id_funcionario]);	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+4, $linha, $array_cont_setor[$id_funcionario]);
	
	foreach($array_cont_formacao[$id_funcionario] as $chave=>$formacao)
	{
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+6, $linha, $formacao);		
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+8, $linha, $array_cont_conclusao[$id_funcionario][$chave]);
		
		$linha++;
	}

	$linha++;
}

$objWriter->save('php://output');

exit;

?>