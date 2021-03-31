<?php
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

$db = new banco_dados();

$id_os = $_POST["id_os"];

$sql = "SELECT *,empresas.id_empresa FROM ".DATABASE.".empresas, ".DATABASE.".funcionarios, ".DATABASE.".ordem_servico ";
$sql .= "LEFT JOIN ".DATABASE.".contatos ON (ordem_servico.id_cod_resp = contatos.id_contato) ";
$sql .= "WHERE ordem_servico.id_os = '" . $id_os . "' ";
$sql .= "AND ordem_servico.id_empresa = empresas.id_empresa ";
$sql .= "AND ordem_servico.id_cod_coord = funcionarios.id_funcionario ";

$db->select($sql,'MYSQL',true);

$reg_os = $db->array_select[0];


if(is_file("../modelos_excel/modelo_LP_".$reg_os["id_empresa"].".xls"))
{
	$objPHPExcel = PHPExcel_IOFactory::load("../modelos_excel/modelo_LP_".$reg_os["id_empresa"].".xls");
}
else
{
	$objPHPExcel = PHPExcel_IOFactory::load("../modelos_excel/modelo_LP_modelo.xls");
}

$cacheMethod = PHPExcel_CachedObjectStorageFactory:: cache_to_phpTemp;
$cacheSettings = array( ' memoryCacheSize '  => '8MB'
                      );
PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);

$locale = 'pt_br';
$validlocale = PHPExcel_Settings::setlocale($locale);

if (!$validlocale) 
{
	echo 'Unable to set locale to '.$locale." - reverting to en_us<br />\n";
}

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

// Add some data
$objPHPExcel->setActiveSheetIndex(0);

$objPHPExcel->getActiveSheet()->getStyle('D3')->getNumberFormat()->setFormatCode('00000');			
$objPHPExcel->getActiveSheet()->setCellValue('D3', sprintf("%05d",$reg_os["os"]));

$objPHPExcel->getActiveSheet()->setCellValue('M3',  $reg_os["descricao"]);
$objPHPExcel->getActiveSheet()->setCellValue('AP1',  'Atualização: '.date("d/m/Y"));

if($_POST["lista_pendencia"]=='2')
{
	$objPHPExcel->getActiveSheet()->getStyle('AR5')->getFont()->setSize("8");
	$objPHPExcel->getActiveSheet()->getStyle('AR5')->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet()->getStyle('AR5:AS6')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$objPHPExcel->getActiveSheet()->getStyle('AR5:AS6')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	$objPHPExcel->getActiveSheet()->mergeCells('AR5:AS6');
	$objPHPExcel->getActiveSheet()->setCellValue('AR5', 'Pendência');
}

$sql = "SELECT * FROM ".DATABASE.".os_x_analise_critica_inicial ";
$sql .= "WHERE id_os = '" . $reg_os["id_os"] . "' ";

$db->select($sql,'MYSQL',true);

$regs = $db->array_select[0];

$sql = "SELECT * FROM ".DATABASE.".os_x_analise_critica_periodica ";
$sql .= "LEFT JOIN ".DATABASE.".setores ON (os_x_analise_critica_periodica.id_disciplina = setores.id_setor) ";
$sql .= "WHERE id_os = '" . $reg_os["id_os"] . "' ";

if($_POST["lista_pendencia"]=='1') //lista cliente
{
	$sql .= "AND pendencia_interna = '0' "; //somente pendencias externas
}

$sql .= "ORDER BY id_os_x_analise_critica_periodica, data_ap ";

$db->select($sql,'MYSQL',true);

$item = 1;

foreach($db->array_select as $regs)
{
	$row = $item+6;	
	
	$objPHPExcel->getActiveSheet()->getStyle('A'.$row.":AG".$row)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	
	$objPHPExcel->getActiveSheet()->getStyle('AJ'.$row.":AQ".$row)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
	
	$objPHPExcel->getActiveSheet()->getStyle('A'.$row)->getFont()->setSize("8");	
	$objPHPExcel->getActiveSheet()->mergeCells('A'.$row.':B'.$row);
	$objPHPExcel->getActiveSheet()->setCellValue('A'.$row, $regs["item"] );
	
	$objPHPExcel->getActiveSheet()->getStyle('C'.$row)->getFont()->setSize("8");
	$objPHPExcel->getActiveSheet()->mergeCells('C'.$row.':P'.$row);
	$objPHPExcel->getActiveSheet()->setCellValue('C'.$row,  $regs["identificacao_problema_ap"]);
	$objPHPExcel->getActiveSheet()->getStyle('C'.$row.':P'.$row)->getAlignment()->setWrapText(true);
	
	//Verifica qual o maior texto
	$fator_p = ceil(strlen($regs["identificacao_problema_ap"])/50); //pendencia
	
	$fator_s = ceil(strlen($regs["solucao_possivel_ap"])/40); //observação
	
	if($fator_p>$fator_s)
	{
		$fator = $fator_p;
	}
	else
	{
		$fator = $fator_s;
	}
	
	if($fator<1)
	{
		$fator = 1;
	}
	
	$objPHPExcel->getActiveSheet()->getRowDimension($row)->setRowHeight($fator*12.75); //tamanho padrão
	
	$objPHPExcel->getActiveSheet()->getStyle('Q'.$row)->getFont()->setSize("8");
	$objPHPExcel->getActiveSheet()->mergeCells('Q'.$row.':T'.$row);
	$objPHPExcel->getActiveSheet()->setCellValue('Q'.$row, $regs["setor"]);
	
	$objPHPExcel->getActiveSheet()->getStyle('U'.$row)->getFont()->setSize("8");
	$objPHPExcel->getActiveSheet()->mergeCells('U'.$row.':X'.$row);
	$objPHPExcel->getActiveSheet()->setCellValue('U'.$row, mysql_php($regs["data_solicitacao"])." - ". $regs["solicitado_por"]);
	
	$objPHPExcel->getActiveSheet()->getStyle('Y'.$row)->getFont()->setSize("8");
	$objPHPExcel->getActiveSheet()->mergeCells('Y'.$row.':AB'.$row);
	$objPHPExcel->getActiveSheet()->setCellValue('Y'.$row, $regs["solucao_por"]);
	
	$objPHPExcel->getActiveSheet()->getStyle('AC'.$row)->getFont()->setSize("8");	
	$objPHPExcel->getActiveSheet()->mergeCells('AC'.$row.':AF'.$row);
	$objPHPExcel->getActiveSheet()->setCellValue('AC'.$row, mysql_php($regs["data_ap"]));
	
	switch ($regs["status_ap"])
	{
		case 1: $status_ap = "PENDENTE";
				$objPHPExcel->getActiveSheet()->getStyle('AG'.$row)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
		break;
		
		case 2: $status_ap = "RESOLVIDO";
				$objPHPExcel->getActiveSheet()->getStyle('AG'.$row)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_BLUE);
		break;
		
		case 3: $status_ap = "INFORMAÇÃO";
				$objPHPExcel->getActiveSheet()->getStyle('AG'.$row)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_GREEN);
		break;
		
		default : $status_ap = "";
	}
	
	$objPHPExcel->getActiveSheet()->getStyle('AG'.$row)->getFont()->setSize("7.5");
	$objPHPExcel->getActiveSheet()->mergeCells('AG'.$row.':AI'.$row);
	$objPHPExcel->getActiveSheet()->setCellValue('AG'.$row, $status_ap);
	
	$objPHPExcel->getActiveSheet()->getStyle('AJ'.$row)->getFont()->setSize("8");	
	$objPHPExcel->getActiveSheet()->mergeCells('AJ'.$row.':AQ'.$row);
	$objPHPExcel->getActiveSheet()->getStyle('AJ'.$row.":AQ".$row)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$objPHPExcel->getActiveSheet()->setCellValue('AJ'.$row,  $regs["solucao_possivel_ap"]);
	$objPHPExcel->getActiveSheet()->getStyle('AJ'.$row.':AQ'.$row)->getAlignment()->setWrapText(true);
	
	if($_POST["lista_pendencia"]=='2')
	{		
		switch ($regs["pendencia_interna"])
		{
			case 0:
				$pend_ext = "PENDÊNCIA CLIENTE";
			break;
			
			default: $pend_ext = "PENDÊNCIA";
		}
		
		$objPHPExcel->getActiveSheet()->getStyle('AR'.$row)->getFont()->setSize("8");
		
		$objPHPExcel->getActiveSheet()->mergeCells('AR'.$row.':AS'.$row);
		
		$objPHPExcel->getActiveSheet()->getStyle('AR'.$row.':AS'.$row)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
		
		$objPHPExcel->getActiveSheet()->getStyle('AR'.$row.':AS'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
		
		$objPHPExcel->getActiveSheet()->setCellValue('AR'.$row, $pend_ext);
		
	}
	
	$item++;

}

// Redirect output to a clients web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="lista_pendencia_OS-'.sprintf("%05d",$reg_os["os"])."_".date("Y-m-d").'.xlsx"');
header('Cache-Control: max-age=0');

$objWriter->save('php://output');

?>
