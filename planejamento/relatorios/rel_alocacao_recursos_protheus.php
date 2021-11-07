<?php
/*
		Relatório de ALOCAÇÃO DE RECURSOS	
		
		Criado por Carlos Abreu 
		
		local/Nome do arquivo:
		../planejamento/relatorios/rel_alocacao_recursos_protheus.php
		
		Versão 0 --> VERSÃO INICIAL : 02/03/2006		
		Versão 1 --> atualização classe banco de dados - 22/01/2015 - Carlos Abreu
*/

ini_set('max_execution_time','-1'); // No time limit
ini_set('memory_limit', '-1');

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

$filtro0 = "";

//filtra a os (-1 TODAS AS OS)
/*
if($_POST["equipe"] !=-1)
{
	$filtro0 .= "AND AE8_EQUIP = '".$_POST["equipe"]."' ";
	
}

if($_POST["recurso"]!=-1)
{
	$filtro0 .= "AND AE8_RECURS = '" . $_POST["recurso"] . "' ";
}
*/
//COLUNA A EXCELL
$coluna = 0;

$linha = 4;

$tmp_rec = "";

$array_custo_orc = NULL;

$objPHPExcel = PHPExcel_IOFactory::load("../modelos_excel/alocacao_recursos_modelo.xls");

$locale = 'pt_br';

$validlocale = PHPExcel_Settings::setlocale($locale);

if (!$validlocale) 
{
	echo 'Unable to set locale to '.$locale." - reverting to en_us<br />\n";
}

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

// Add some data
$objPHPExcel->setActiveSheetIndex(0);

$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(10, 1, "DATA EMISSÃO: ".date('d/m/Y'));

$db = new banco_dados();

//Seleciona os recursos
/*
$sql = "SELECT * FROM AE8010 WITH(NOLOCK), AF8010 WITH(NOLOCK), AF9010 WITH(NOLOCK), AFA010 WITH(NOLOCK) ";
$sql .= "WHERE AE8010.D_E_L_E_T_ = '' ";
$sql .= "AND AFA010.D_E_L_E_T_ = '' ";
$sql .= "AND AF8010.D_E_L_E_T_ = '' ";
$sql .= "AND AF9010.D_E_L_E_T_ = '' ";
$sql .= "AND AE8_RECURS = AFA_RECURS ";
$sql .= "AND AF8_FASE IN ('03','07') ";
$sql .= "AND AF8_PROJET = AFA_PROJET ";
$sql .= "AND AF8_REVISA = AFA_REVISA ";
$sql .= "AND AF8_PROJET = AF9_PROJET ";
$sql .= "AND AF8_REVISA = AF9_REVISA ";
$sql .= "AND AF9_TAREFA = AFA_TAREFA ";
$sql .= $filtro0;
$sql .= "ORDER BY AE8_DESCRI, AF8_PROJET, AF9_START, AF9_FINISH ";

$db->select($sql,'MSSQL', true);

$array_rec = $db->array_select;

foreach($array_rec as $regs0)
{	
	//OBTEM O CUSTO ORÇADO DA TAREFA
	$sql = "SELECT AF2_CUSTO, AF2_HDURAC FROM AF2010 WITH(NOLOCK) ";
	$sql .= "WHERE AF2010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF2010.AF2_ORCAME = '".$regs0["AF9_PROJET"]."' ";
	$sql .= "AND AF2010.AF2_CODIGO = '".$regs0["AF9_CODIGO"]."' ";

	$db->select($sql,'MSSQL', true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}
	
	$regs_orc = $db->array_select[0];
	
	if($_POST["avanco"])
	{	
		//OBTEM O AVANÇO FÍSICO DA TAREFA
		$sql = "SELECT AFF010.AFF_QUANT FROM AFF010 WITH(NOLOCK) ";
		$sql .= "WHERE AFF010.D_E_L_E_T_ = '' ";
		$sql .= "AND AFF010.AFF_PROJET = '".$regs0["AF9_PROJET"]."' ";
		$sql .= "AND AFF010.AFF_REVISA = '".$regs0["AF9_REVISA"]."' ";
		$sql .= "AND AFF010.AFF_TAREFA = '".$regs0["AF9_TAREFA"]."' ";
		$sql .= "ORDER BY AFF_DATA DESC ";
		
		$db->select($sql,'MSSQL', true);

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
			
			return $resposta;
		}
		
		$regs_tarefa = $db->array_select[0];		
	
		//VERIFICA SE O AVANÇO É < 100%
		if($regs_tarefa["AFF_QUANT"]/$regs0["AF9_QUANT"]<1)
		{			
			//coluna A
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna, $linha, iconv('ISO-8859-1', 'UTF-8',sprintf("%010d",$regs0["AF8_PROJET"])));
			$objPHPExcel->getActiveSheet()->getStyle("A".$linha)->getNumberFormat()->setFormatCode('0000000000');
			//COLUNA B
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+1, $linha, iconv('ISO-8859-1', 'UTF-8',trim($regs0["AE8_DESCRI"])));
			//COLUNA C
			//$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+2, $linha, iconv('ISO-8859-1', 'UTF-8',trim($regs0["AF9_TAREFA"])));//item
			
			$objPHPExcel->getActiveSheet()->getCell('C'.$linha)->setValueExplicit(iconv('ISO-8859-1', 'UTF-8',trim($regs0["AF9_TAREFA"])), PHPExcel_Cell_DataType::TYPE_STRING);
			$objPHPExcel->getActiveSheet()->getStyle('C'.$linha)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);			
			
			//COLUNA D
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+3, $linha, iconv('ISO-8859-1', 'UTF-8',trim($regs0["AF9_DESCRI"])));
			//COLUNA E
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+4, $linha, mysql_php(protheus_mysql($regs0["AF9_START"])));
			//COLUNA F
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+5, $linha, mysql_php(protheus_mysql($regs0["AF9_FINISH"])));
			//COLUNA G
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+6, $linha, $regs0["AFA_QUANT"]);
			//COLUNA H
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+7, $linha, "=NETWORKDAYS(E".$linha.",F".$linha.")");
			//COLUNA I
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+8, $linha, "=G".$linha."/H".$linha."");
			//COLUNA J
			
			if(!$array_custo_orc[$regs0["AF9_PROJET"]][$regs0["AF9_CODIGO"]])
			{			
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+9, $linha, $regs_orc["AF2_CUSTO"]);
				$objPHPExcel->getActiveSheet()->getStyle("J".$linha)->getNumberFormat()->setFormatCode('R$ #,#00.00');
			}
			else
			{
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+9, $linha, "");	
			}
			
			//COLUNA K
			//$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+10, 4, "=IF(AND(H1<=D4,H1>=C4),G4,\"\")");
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+10, $linha, $regs_orc["AF2_HDURAC"]);
				
			$linha+=1;	
		}
	}
	else
	{
		
		//coluna A
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna, $linha, iconv('ISO-8859-1', 'UTF-8',sprintf("%010d",$regs0["AF8_PROJET"])));
		$objPHPExcel->getActiveSheet()->getStyle("A".$linha)->getNumberFormat()->setFormatCode('0000000000');

		//COLUNA B
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+1, $linha, iconv('ISO-8859-1', 'UTF-8',trim($regs0["AE8_DESCRI"])));
		//COLUNA C
		//$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+2, $linha, iconv('ISO-8859-1', 'UTF-8',trim($regs0["AF9_TAREFA"])));//item
		$objPHPExcel->getActiveSheet()->getCell('C'.$linha)->setValueExplicit(iconv('ISO-8859-1', 'UTF-8',trim($regs0["AF9_TAREFA"])), PHPExcel_Cell_DataType::TYPE_STRING);
		$objPHPExcel->getActiveSheet()->getStyle('C'.$linha)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);			

		
		//COLUNA D
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+3, $linha, iconv('ISO-8859-1', 'UTF-8',trim($regs0["AF9_DESCRI"])));
		//COLUNA E
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+4, $linha, mysql_php(protheus_mysql($regs0["AF9_START"])));
		//COLUNA F
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+5, $linha, mysql_php(protheus_mysql($regs0["AF9_FINISH"])));
		//COLUNA G
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+6, $linha, $regs0["AFA_QUANT"]);
		//COLUNA H
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+7, $linha, "=NETWORKDAYS(E".$linha.",F".$linha.")");
		//COLUNA I
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+8, $linha, "=G".$linha."/H".$linha."");
		//COLUNA J
		
		if(!$array_custo_orc[$regs0["AF9_PROJET"]][$regs0["AF9_CODIGO"]])
		{			
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+9, $linha, $regs_orc["AF2_CUSTO"]);
			$objPHPExcel->getActiveSheet()->getStyle("J".$linha)->getNumberFormat()->setFormatCode('R$ #,#00.00');
		}
		else
		{
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+9, $linha, "");	
		}
		
		//COLUNA K
		//$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+10, 4, "=IF(AND(H1<=D4,H1>=C4),G4,\"\")");
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+10, $linha, $regs_orc["AF2_HDURAC"]);
			
		$linha+=1;				
	}
	
	$array_custo_orc[$regs0["AF9_PROJET"]][$regs0["AF9_CODIGO"]] = $regs_orc["AF2_CUSTO"];

}
*/

// Redirect output to a client's web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="alocacao_recursos_"'.date('dmYHis').'".xlsx"');
header('Cache-Control: max-age=0');

$objWriter->save('php://output');

?>