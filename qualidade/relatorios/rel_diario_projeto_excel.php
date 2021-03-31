<?php
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

//error_reporting(E_ALL);

//date_default_timezone_set('Europe/London');

/** PHPExcel_IOFactory */
//require_once("../includes/PHPExcel/Classes/PHPExcel/IOFactory.php"); 

require_once(implode(DIRECTORY_SEPARATOR,array('..','..','config.inc.php')));
 
require_once(INCLUDE_DIR."PHPExcel/Classes/PHPExcel.php");

function getRowcount($text, $width=6) 
{
    $rc = 0;
    
	$line = explode("\n", $text);
	
	$rc = count($line)+1;
	
    return $rc;
}

$db = new banco_dados();

$sql = "SELECT * FROM ".DATABASE.".ordem_servico, ".DATABASE.".empresas ";
$sql .= "WHERE ordem_servico.id_empresa = empresas.id_empresa ";
$sql .= "AND ordem_servico.id_os = '".$_POST["id_os"]."' ";

$db->select($sql,'MYSQL', true);

if($db->erro!='')
{
	die('Erro0.');
}

$regs_os = $db->array_select[0];

$sql = "SELECT * FROM ".DATABASE.".diario_projeto, ".DATABASE.".diario_projeto_itens, ".DATABASE.".funcionarios, ".DATABASE.".setores ";
$sql .= "WHERE diario_projeto.reg_del = 0 ";
$sql .= "AND diario_projeto_itens.reg_del = 0 ";
$sql .= "AND diario_projeto.id_diario_projeto = diario_projeto_itens.id_diario_projeto ";
$sql .= "AND diario_projeto_itens.id_funcionario = funcionarios.id_funcionario ";
$sql .= "AND diario_projeto_itens.id_setor = setores.id_setor ";
$sql .= "AND diario_projeto.id_os = '".$_POST["id_os"]."' ";

$db->select($sql,'MYSQL', true);

if($db->erro!='')
{
	die('Erro1.');
}

$reg_itens = $db->array_select;

$objPHPExcel = PHPExcel_IOFactory::load("../modelos_excel/relatorio_diario_projeto_modelo.xls");

$locale = 'pt_br';

$validlocale = PHPExcel_Settings::setlocale($locale);

if (!$validlocale) 
{
	echo 'Unable to set locale to '.$locale." - reverting to en_us<br />\n";
}

// Redirect output to a client's web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="relatorio_diario_projeto_"'.date('His').'".xlsx"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

$objPHPExcel->setActiveSheetIndex(0);

$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, 3, sprintf("%05d",$regs_os["os"])." - ".$regs_os["descricao"]);

$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, 4, date('d/m/Y'));

$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, 5, $regs_os["empresa"]);

$linha = 8;

foreach($reg_itens as $cont0)
{
	$descricao = wordwrap($cont0["descricao_item"],90,"\n");
	
	$numrows = getRowcount($descricao);
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $linha, sprintf("%03d",$cont0["numero_item"]));

	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $linha, $descricao);
	
	$objPHPExcel->getActiveSheet()->getRowDimension($linha)->setRowHeight($numrows * 13 + 2.5);
	
	$objPHPExcel->getActiveSheet()->getStyle('B'.$linha)->getAlignment()->setWrapText(true);

	$objPHPExcel->getActiveSheet()->mergeCells("B".$linha.":I".$linha);
	
	$linha++;	
}

$objPHPExcel->setActiveSheetIndex(0);

$objWriter->save('php://output');

exit;
?>
