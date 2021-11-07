<?php
/*
		Relatório de rotinas de manutencao
		
		Criado por Carlos Abreu 
		
		local/Nome do arquivo:
		../ti/relatorios/rel_rotinas_manutencao_excel.php
		
		Versão 0 --> VERSÃO INICIAL : 23/02/2012
		Versão 1 --> Inclusão dos campos reg_del nas consultas - 23/11/2017 - Carlos Abreu
		
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

require_once(implode(DIRECTORY_SEPARATOR,array('..','..','config.inc.php')));

require_once(INCLUDE_DIR."PHPExcel/Classes/PHPExcel.php"); 

$db = new banco_dados();

$diario = 0;

$semanal = 0;

$quinzenal = 0;

$mensal = 0;

$ano = $_POST['ano'];

$sql = "SELECT * FROM ".DATABASE.".funcionarios, ti.ti_rotinas_manutencoes ";	
$sql .= "WHERE funcionarios.id_funcionario = ti.ti_rotinas_manutencoes.id_ti_analista ";
$sql .= "AND funcionarios.reg_del = 0 ";
$sql .= "AND ti_rotinas_manutencoes.reg_del = 0 ";
$sql .= "GROUP BY funcionarios.funcionario ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}

$array_manut = $db->array_select;

foreach($array_manut as $cont)
{
	$array_analistas[$cont["id_funcionario"]] = $cont["funcionario"];
	
	$sql = "SELECT * FROM ti.ti_rotinas_manutencoes, ti.ti_rotinas, ti.ti_rotinas_frequencias, ti.ti_frequencias ";
	$sql .= "WHERE ti_rotinas_manutencoes.reg_del = 0 ";
	$sql .= "AND ti_rotinas.reg_del = 0 ";
	$sql .= "AND ti_rotinas_frequencias.reg_del = 0 ";
	$sql .= "AND ti_frequencias.reg_del = 0 ";
	$sql .= "AND ti_rotinas_manutencoes.id_ti_rotina = ti_rotinas.id_ti_rotina ";
	$sql .= "AND ti_rotinas_manutencoes.id_ti_analista = '".$cont["id_funcionario"]."' ";
	$sql .= "AND ti_rotinas.id_ti_rotina = ti_rotinas_frequencias.id_ti_rotina ";
	$sql .= "AND year(ti_data_manutencao) = ".$ano." ";
	$sql .= "AND ti_rotinas_frequencias.id_ti_frequencia = ti_frequencias.id_ti_frequencia ";
	$sql .= "ORDER BY ti_frequencia_dias, ti_data_manutencao, ti_rotina ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}
	
	foreach($db->array_select as $regs)
	{
		
		$mes_corrente = substr($regs["ti_data_manutencao"],5,2);
		
		switch($regs["ti_frequencia"])
		{
			case 'DIÁRIO':
			
				$array_diario[$regs["id_ti_rotina_manutencao"]][$mes_corrente][$regs["id_ti_analista"]][$regs["ti_data_manutencao"]][$regs["ti_rotina"]] = $regs["ti_manutencao_observacao"];
				
				$diario++;
				
			break;
			
			case 'SEMANAL':
			
				$array_semanal[$regs["id_ti_rotina_manutencao"]][$mes_corrente][$regs["id_ti_analista"]][$regs["ti_data_manutencao"]][$regs["ti_rotina"]] = $regs["ti_manutencao_observacao"];
			
				$semanal++;
			
			break;
			
			case 'QUINZENAL':
			
				$array_quinzenal[$regs["id_ti_rotina_manutencao"]][$mes_corrente][$regs["id_ti_analista"]][$regs["ti_data_manutencao"]][$regs["ti_rotina"]] = $regs["ti_manutencao_observacao"];
			
				$quinzenal++;
			
			break;			
				
			case 'MENSAL':
			
				$array_mensal[$regs["id_ti_rotina_manutencao"]][$mes_corrente][$regs["id_ti_analista"]][$regs["ti_data_manutencao"]][$regs["ti_rotina"]] = $regs["ti_manutencao_observacao"];
				
				$mensal++;
				
			break;
		}			
	}
			
}

//monta o array dos meses e datas

for($d = 1;$d <= 12;$d++)
{
	$dias = date("t", mktime(0, 0, 0, $d, 1, $_POST["ano"]));

	for($j = 1; $j <= $dias ; $j++)
	{
		$array_datas_mes[$d][$_POST["ano"]."-".$d."-".$j] = 1;
	}		
}

$objPHPExcel = PHPExcel_IOFactory::load("../modelos_excel/relatorio_manutencoes_modelo.xls");

$locale = 'pt_br';

$validlocale = PHPExcel_Settings::setlocale($locale);

if (!$validlocale) 
{
	echo 'Unable to set locale to '.$locale." - reverting to en_us<br />\n";
}

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

$num_linhas = count($array_analistas)+$diario+$semanal+$quinzenal+$mensal;

foreach ($array_datas_mes as $mes=>$data)
{
	$objPHPExcel->setActiveSheetIndex($mes-1);
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, 3, iconv('ISO-8859-1', 'UTF-8',date('d/m/Y')));
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, 3, iconv('ISO-8859-1', 'UTF-8',$_POST["ano"]));

	$linha=9;	
	
	//DIÁRIO
	foreach($array_diario as $id_manu=>$a_mes)
	{
		foreach($a_mes[sprintf("%02d",$mes)] as $codfun=>$a_datas)
		{
			if($cod_fun!=$codfun)
			{
				//nome do funcionario
				
               $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $linha, iconv('ISO-8859-1', 'UTF-8',$array_analistas[$cod_fun]));
				$linha++;
				
				$objPHPExcel->getActiveSheet()->insertNewRowBefore($linha,1);
				
				$objPHPExcel->getActiveSheet()->mergeCells("A".$linha.":D".$linha);
			}
			
			$cod_fun = $codfun;
			
			foreach($a_datas as $datas=>$a_rotinas)
			{
				
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, $linha, iconv('ISO-8859-1', 'UTF-8',mysql_php($datas)));
							
				foreach($a_rotinas as $rotinas=>$obs)
				{							
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, $linha, iconv('ISO-8859-1', 'UTF-8',$rotinas));
					
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $linha, iconv('ISO-8859-1', 'UTF-8',$obs));
				
					$objPHPExcel->getActiveSheet()->mergeCells("G".$linha.":I".$linha);
				}
				
				$linha++;
				
				$objPHPExcel->getActiveSheet()->insertNewRowBefore($linha,1);
				
				$objPHPExcel->getActiveSheet()->mergeCells("A".$linha.":D".$linha);
		
			}		
		}		
	}
		
	$linha+=3;	

	//SEMANAL
	foreach($array_semanal as $id_manu=>$a_mes)
	{
		foreach($a_mes[sprintf("%02d",$mes)] as $codfun=>$a_datas)
		{
			if($cod_fun!=$codfun)
			{
				//nome do funcionario
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $linha, iconv('ISO-8859-1', 'UTF-8',$array_analistas[$codfun]));
				
				$linha++;
				
				$objPHPExcel->getActiveSheet()->insertNewRowBefore($linha,1);
				
				$objPHPExcel->getActiveSheet()->mergeCells("A".$linha.":D".$linha);
			}
			
			$cod_fun = $codfun;
			
			foreach($a_datas as $datas=>$a_rotinas)
			{
				
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, $linha, iconv('ISO-8859-1', 'UTF-8',mysql_php($datas)));
							
				foreach($a_rotinas as $rotinas=>$obs)
				{							
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, $linha, iconv('ISO-8859-1', 'UTF-8',$rotinas));
					
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $linha, iconv('ISO-8859-1', 'UTF-8',$obs));
				
					$objPHPExcel->getActiveSheet()->mergeCells("G".$linha.":I".$linha);
				}
				
				$linha++;
				
				$objPHPExcel->getActiveSheet()->insertNewRowBefore($linha,1);
				
				$objPHPExcel->getActiveSheet()->mergeCells("A".$linha.":D".$linha);
		
			}		
		}		
	}
	
	$linha+=3;
		
	//QUINZENAL
	foreach($array_quinzenal as $id_manu=>$a_mes)
	{
		foreach($a_mes[sprintf("%02d",$mes)] as $codfun=>$a_datas)
		{
			if($cod_fun!=$codfun)
			{
				//nome do funcionario
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $linha, iconv('ISO-8859-1', 'UTF-8',$array_analistas[$codfun]));
				
				$linha++;
				
				$objPHPExcel->getActiveSheet()->insertNewRowBefore($linha,1);
				
				$objPHPExcel->getActiveSheet()->mergeCells("A".$linha.":D".$linha);
			}
			
			$cod_fun = $codfun;
			
			foreach($a_datas as $datas=>$a_rotinas)
			{
				
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, $linha, iconv('ISO-8859-1', 'UTF-8',mysql_php($datas)));
							
				foreach($a_rotinas as $rotinas=>$obs)
				{							
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, $linha, iconv('ISO-8859-1', 'UTF-8',$rotinas));
					
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $linha, iconv('ISO-8859-1', 'UTF-8',$obs));
				
					$objPHPExcel->getActiveSheet()->mergeCells("G".$linha.":I".$linha);
				}
				
				$linha++;
				
				$objPHPExcel->getActiveSheet()->insertNewRowBefore($linha,1);
				
				$objPHPExcel->getActiveSheet()->mergeCells("A".$linha.":D".$linha);
		
			}		
		}		
	}
	
	$linha+=3;
	
	//MENSAL
	foreach($array_mensal as $id_manu=>$a_mes)
	{
		foreach($a_mes[sprintf("%02d",$mes)] as $codfun=>$a_datas)
		{
			if($cod_fun!=$codfun)
			{
				//nome do funcionario
			    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $linha, iconv('ISO-8859-1', 'UTF-8',$array_analistas[$cod_fun]));
				
				$linha++;
				
				$objPHPExcel->getActiveSheet()->insertNewRowBefore($linha,1);
				
				$objPHPExcel->getActiveSheet()->mergeCells("A".$linha.":D".$linha);
			}
			
			$cod_fun = $codfun;
			
			foreach($a_datas as $datas=>$a_rotinas)
			{
				
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, $linha, iconv('ISO-8859-1', 'UTF-8',mysql_php($datas)));
							
				foreach($a_rotinas as $rotinas=>$obs)
				{							
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, $linha, iconv('ISO-8859-1', 'UTF-8',$rotinas));
					
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $linha, iconv('ISO-8859-1', 'UTF-8',$obs));
				
					$objPHPExcel->getActiveSheet()->mergeCells("G".$linha.":I".$linha);
				}
				
				$linha++;
				
				$objPHPExcel->getActiveSheet()->insertNewRowBefore($linha,1);
				
				$objPHPExcel->getActiveSheet()->mergeCells("A".$linha.":D".$linha);		
			}		
		}		
	}		
}

$objPHPExcel->setActiveSheetIndex((date('m')-1));


// Redirect output to a clients web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="rotinas_manutencoes_"'.date('His').'".xlsx"');
header('Cache-Control: max-age=0');
$objWriter->save('php://output');

exit;
?>
