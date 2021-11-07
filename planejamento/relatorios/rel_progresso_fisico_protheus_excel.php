<?php
/*
		Relatório Progresso Fisico
		
		Criado por Carlos Abreu
		
		local/Nome do arquivo:		
		../planejamento/relatorios/rel_progresso_fisico_protheus_excel.php
		
		Versão 0 --> VERSÃO INICIAL - 02/03/2006
		Versão 1 --> Atualização classe banco de dados - 22/01/2015 - Carlos Abreu
		Versão 2 --> Inclusão dos campos reg_del nas consultas - 20/11/2017 - Carlos Abreu	
*/

ini_set('max_execution_time', 0); // No time limit
ini_set('post_max_size', '20M');
ini_set('upload_max_filesize', '20M');
ini_set('memory_limit', '256M');

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

$os = $_POST["escolhaos"];

$filtro0 = "";

if($os!=-1)
{
	$filtro0 .= "AND AF1_ORCAME = '".sprintf("%010d",$os)."' ";
	$txt = $os;	
}

//EXCESSOES PARA DESMEMBRAMENTO (atividades que não podem ser desmembradas)
$excessao_aut = array('AUT04','AUT48','AUT47','AUT20','AUT22','AUT52','AUT54','AUT55','AUT11','AUT41','AUT10','AUT42','AUT57','AUT56','AUT01','AUT71','AUT44','AUT40','AUT49','AUT05');

$excessao_civ = array('CIV04','CIV48','CIV47','CIV94','CIV49','CIV46','CIV41','CIV10','CIV16','CIV17','CIV44','CIV59','CIV58','CIV78','CIV72','CIV71','CIV45','CIV64','CIV77','CIV05');

$excessao_ele = array('ELE05','ELE48','ELE47','ELE94','ELE41','ELE22','ELE87','ELE55','ELE63','ELE01','ELE67','ELE44','ELE60','ELE54','ELE29','ELE28','ELE49','ELE51','ELE12','ELE86','ELE26','ELE68','ELE64','ELE11','ELE09');

$excessao_est = array('EST40','EST01','EST60','EST31','EST34');

$excessao_ger = array('GER17','GER09','GER01','GER02','GER11','GER03','GER05','GER04','GER22');

$excessao_ins = array('INS02','INS48','INS47','INS93','INS27','INS55','INS01','INS73','INS24','INS60','INS44','INS25','INS51','INS59','INS28','INS49','INS57','INS10','INS14','INS05');

$excessao_mec = array('MEC04','MEC48','MEC24','MEC47','MEC94','MEC46','MEC64','MEC10','MEC62','MEC63','MEC55','MEC44','MEC59','MEC58','MEC67','MEC99','MEC28','MEC49','MEC79','MEC12','MEC80','MEC05');

$excessao_ebp = array('EBP11','EBP48','EBP47','EBP45','EBP93','EBP22','EBP41','EBP10','EBP16','EBP14','EBP01','EBP67','EBP96','EBP49','EBP12','EBP40','EBP08','EBP17','EBP13');

$excessao_tub = array('TUB04','TUB48','TUB60','TUB47','TUB93','TUB46','TUB10','TUB73','TUB55','TUB44','TUB70','TUB71','TUB69','TUB39','TUB49','TUB51','TUB80','TUB16','TUB05');

$excessao_pdm = array('PDM74','PDM75','PDM76','PDM77','PDM79');


$excessao = array_merge($excessao_aut, $excessao_civ, $excessao_ele, $excessao_est, $excessao_ger, $excessao_ins, $excessao_mec, $excessao_ebp, $excessao_tub, $excessao_pdm);

//Seleciona as OSs
$sql = "SELECT * FROM AF1010 WITH(NOLOCK), SA1010 WITH(NOLOCK) ";
$sql .= "WHERE AF1010.D_E_L_E_T_ = '' ";
$sql .= "AND SA1010.D_E_L_E_T_ = '' ";
$sql .= "AND AF1010.AF1_CLIENT = SA1010.A1_COD ";
$sql .= "AND AF1010.AF1_LOJA = SA1010.A1_LOJA ";
$sql .= $filtro0;

$db->select($sql,'MSSQL',true);

$regs0 = $db->array_select[0];

//Percorre as tarefas da fase de orçamento
$sql = "SELECT * FROM AF2010 WITH(NOLOCK) ";
$sql .= "WHERE AF2010.D_E_L_E_T_ = '' ";
$sql .= "AND AF2_ORCAME = '" . $regs0["AF1_ORCAME"] . "' ";
$sql .= "AND AF2_COMPOS NOT LIKE 'SUP%' ";
$sql .= "AND AF2_COMPOS NOT LIKE 'DES%' ";	
$sql .= "ORDER BY AF2_TAREFA ";

$db->select($sql,'MSSQL',true);

$array_tarefas = $db->array_select;

foreach($array_tarefas as $regs2)
{	
	$disciplina = substr($regs2["AF2_COMPOS"],0,3);
	
	switch ($disciplina)
	{
		case 'GER': //GERAL
			$discip = 1;
			$array_disc[1] = 'GERAL';
		break;
		
		case 'EBP': //PROCESSO
			$discip = 2;
			$array_disc[2] = 'PROCESSO';
		break;
		
		case 'CIV': //CIVIL
			$discip = 3;
			$array_disc[3] = 'CIVIL';
		break;
		
		case 'MEC': //MECÂNICA
			$discip = 4;
			$array_disc[4] = 'MECÂNICA';
		break;
		
		case 'TUB': //TUBULAÇÃO
			$discip = 5;
			$array_disc[5] = 'TUBULAÇÃO';
		break;
		
		case 'ELE': //ELETRICA
			$discip = 6;
			$array_disc[6] = 'ELÉTRICA';
		break;
		
		case 'INS': //INSTRUMENTAÇÃO
			$discip = 7;
			$array_disc[7] = 'INSTRUMENTAÇÃO';
		break;
		
		case 'AUT': //AUTOMAÇÃO
			$discip = 8;
			$array_disc[8] = 'AUTOMAÇÃO';
		break;
		
		case 'EST': //ESTRUTURA METÁLICA
			$discip = 9;
			$array_disc[9] = 'ESTRUTURA METÁLICA';
		break;
		
		case 'PDM': //PDMS
			$discip = 10;
			$array_disc[10] = 'PDMS';
		break;
		
		case 'SEG': //SEGURANÇA
			$discip = 11;
			$array_disc[11] = 'SEGURANÇA';
		break;

		case 'VAC': //VENTILAÇÃO E AR CONDICIONADO
			$discip = 12;
			$array_disc[12] = 'VENTILAÇÃO E AR CONDICIONADO';
		break;
		
		case 'COR': //COORDENACAO
			$discip = 1; //GERAL
			$array_disc[1] = 'GERAL';
		break;
		
		case 'PLN': //PLANEJAMENTO
			$discip = 1; //GERAL
			$array_disc[1] = 'GERAL';
		break;
		
		case 'ART': //ARQUIVOTEC
			$discip = 1; //GERAL
			$array_disc[1] = 'GERAL';
		break;
				
		case 'SUP': //SUPRIMENTOS
			$discip = 1;
			$array_disc[1] = 'GERAL';
		break;
	
	}
	
	$composicao = trim($regs2["AF2_COMPOS"]);
	
	$sql = "SELECT SUM(AF3_QUANT) AS QTD_HORAS FROM AF3010 WITH(NOLOCK) ";
	$sql .= "WHERE AF3010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF3_ORCAME = '".$regs2["AF2_ORCAME"]."' ";
	$sql .= "AND AF3_TAREFA = '".$regs2["AF2_TAREFA"]."' ";
	
	$db->select($sql,'MSSQL',true);
	
	$reg3 = $db->array_select[0];
	
	//verifica as composições, para agregar na composição pai.
	switch($composicao)
	{
		case 'PLN03': //PLANEJAMENTO
			
			$array_quant_docs[$discip]['GER20'] += $regs2["AF2_QUANT"];
			
			$array_formato[$discip]['GER20'] = $regs2["AF2_UM"];	

			$array_quant_horas[$discip]['GER20'] += $reg3["QTD_HORAS"];
			
			$array_titulo[$discip]['GER20'] = 'MOBILIZAÇÃO';
			
			$array_tarefas[$discip]['GER20'] = $regs2["AF2_TAREFA"];
			
			$array_grau[$discip]['GER20'] = $regs2["AF2_GRAU"];
			
			$array_compos[$discip]['GER20'] = 'GER20';
			
		break;
		
		default:
		
			$array_titulo[$discip][$composicao] = $regs2["AF2_DESCRI"];
			
			$array_tarefas[$discip][$composicao] = $regs2["AF2_TAREFA"];
			
			$array_quant_docs[$discip][$composicao] += $regs2["AF2_QUANT"];
			
			$array_formato[$discip][$composicao] = $regs2["AF2_UM"];
			
			$array_grau[$discip][$composicao] = $regs2["AF2_GRAU"];	

			$array_quant_horas[$discip][$composicao] += $reg3["QTD_HORAS"];
			
			$array_compos[$discip][$composicao] = $composicao;		
	}		
}

if(is_file("modelos_excel/prog_fisico_".$os.".xls"))
{
	$objPHPExcel = PHPExcel_IOFactory::load("../modelos_excel/prog_fisico_".$txt.".xls");
}
else
{
	$objPHPExcel = PHPExcel_IOFactory::load("../modelos_excel/prog_fisico_modelo.xls");
}

$locale = 'pt_br';

$validlocale = PHPExcel_Settings::setlocale($locale);

if (!$validlocale) 
{
	echo 'Unable to set locale to '.$locale." - reverting to en_us<br />\n";
}

$objPHPExcel->getProperties()
            ->setCreator(NOME_EMPRESA)
            ->setLastModifiedBy($_SESSION["nome_usuario"])
            ->setTitle("PROGRESSO FISICO")
            ->setSubject("PROGRESSO FISICO")
            ->setDescription("PROGRESSO FISICO")
            ->setKeywords("PROGRESSO FISICO")
            ->setCategory("PLANEJAMENTO");


// Redirect output to a clients web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="proj_fisico_"'.$txt.'_'.date('dmYHis').'_".xlsx"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

$objPHPExcel->setActiveSheetIndex(0);

$row_cell = 25;

//percorre as disciplinas
foreach($array_titulo as $indice=>$valor)
{
	//percorre os documentos
	foreach ($valor as $index=>$descr)
	{		
		$descricao_doc = trim($descr);
		
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $row_cell,trim($regs0["A1_NOME"]).'-'.trim($regs0["A1_MUN"]));
		
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $row_cell, trim($regs0["AF1_ORCAME"]));
		
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $row_cell, str_replace("/"," ",trim($regs0["AF1_DESCRI"])));

		//Verifica se o formato é horas ou disciplina esta nas excessões
		if($array_formato[$indice][$index]=='HR' || $array_formato[$indice][$index]=='A4' || in_array($array_compos[$indice][$index],$excessao))
		{			

			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $row_cell, $array_disc[$indice]);

			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, $row_cell, $descricao_doc);
			
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, $row_cell, $array_formato[$indice][$index]);
			
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, $row_cell, $array_quant_docs[$indice][$index]);
			
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(9, $row_cell, $array_grau[$indice][$index]);
			
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(10, $row_cell, $array_quant_horas[$indice][$index]);
			
			//incrementa a linha
			$row_cell++;		
		}

		else
		{
			//desmembra os documentos
			for($i=1;$i<=($array_quant_docs[$indice][$index]);$i++)
			{
				
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $row_cell, trim($regs0["A1_NOME"]).'-'.trim($regs0["A1_MUN"]));
		
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $row_cell, trim($regs0["AF1_ORCAME"]));
		
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $row_cell, str_replace("/"," ",trim($regs0["AF1_DESCRI"])));
				
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $row_cell, $array_disc[$indice]);

				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, $row_cell, $descricao_doc."_".$i);
				
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, $row_cell, $array_formato[$indice][$index]);
				
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, $row_cell, 1);
				
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(9, $row_cell, $array_grau[$indice][$index]);				

				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(10, $row_cell, ($array_quant_horas[$indice][$index]/$array_quant_docs[$indice][$index]));
						
				//incrementa a linha
				$row_cell++;	
			}
			
		}
	
	}
}

$objWriter->save('php://output');

exit;

?>