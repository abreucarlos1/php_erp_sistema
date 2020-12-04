<?php
/*
		Relatorio Acessos da catraca - DIMEP
		
		Criado por Carlos Abreu 
		
		local/Nome do arquivo:
		../rh/relatorios/rel_acessos_catraca_excel.php
		
		Versão 0 --> VERSÃO INICIAL - 04/05/2016
		Versão 1 --> 28/03/2006
		Versão 2 --> Inclusão dos campos reg_del nas consultas - 27/11/2017 - Carlos Abreu	
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

$db->db_ms = 'DMPACESSO_V100';

$filtro0 = "";

//filtra a os (-1 TODOS OS FUNCIONARIOS)
if($_POST["funcionario"]!=-1)
{
	$filtro0 .= "AND PESSOAS.PES_NUMERO = '".$_POST["funcionario"]."' ";
	
}


if($_POST["intervalo"]=='1')
{
	$filtro0 .= "AND MOV_DATAHORA BETWEEN '".str_replace("-","",php_mysql($_POST["dataini"]))."' AND  '".str_replace("-","",php_mysql($_POST["datafim"]))."' ";
}


//Seleciona
$sql = "SELECT CONVERT(nvarchar(10), MOV_DATAHORA, 103) AS DATA, CONVERT(CHAR(5),MOV_DATAHORA,8) AS HORA,* FROM PESSOAS, LOG_CREDENCIAL ";
$sql .= "WHERE PESSOAS.PES_NUMERO = LOG_CREDENCIAL.PES_NUMERO ";
$sql .= "AND MOV_ENTRADASAIDA IN ('1','2') "; //1 - ENTRADA / 2 - SAIDA / 3 - ACESSO PERMITIDO
$sql .= "AND EQPI_NUMERO <= '2' ";
$sql .= $filtro0;
$sql .= "ORDER BY PESSOAS.PES_NOME, LOG_CREDENCIAL.MOV_DATAHORA, MOV_ENTRADASAIDA ";

$db->select($sql,'MSSQL', true);

if($db->erro!='')
{
	die($db->erro);
}

$i = 0;

foreach($db->array_select as $regs0)
{	
	//entrada
	
	if($regs0["MOV_ENTRADASAIDA"]==1)
	{	
		$array_entsai[$regs0["PES_NUMERO"]][$regs0["DATA"]][$i][1] = time_to_sec($regs0["HORA"]);
	}
	else
	{
		//saida
		if($regs0["MOV_ENTRADASAIDA"]==2)
		{
			$array_entsai[$regs0["PES_NUMERO"]][$regs0["DATA"]][$i][2] = time_to_sec($regs0["HORA"]);
		
			$array_entsai[$regs0["PES_NUMERO"]][$regs0["DATA"]][$i][3] += $array_entsai[$regs0["PES_NUMERO"]][$regs0["DATA"]][$i][2]-$array_entsai[$regs0["PES_NUMERO"]][$regs0["DATA"]][$i][1];
			
			$i++;			
		}
	}
		
}

$objPHPExcel = PHPExcel_IOFactory::load("../modelos_excel/acessos_catraca_modelo.xls");

$locale = 'pt_br';

$validlocale = PHPExcel_Settings::setlocale($locale);

if (!$validlocale) 
{
	echo 'Unable to set locale to '.$locale." - reverting to en_us<br />\n";
}

// Redirect output to a clients web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="acessos_'.date('d-m-Y').'.xlsx"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

$objPHPExcel->setActiveSheetIndex(0);

//COLUNA A EXCELL
$coluna = 0;

$linha = 3;

foreach($array_entsai as $pessoas=>$datas)
{

	$sql = "SELECT PES_NOME FROM PESSOAS ";	
	$sql .= "WHERE PESSOAS.PES_NUMERO = '".$pessoas."' ";

	$db->select($sql,'MSSQL', true);

	if($db->erro!='')
	{
		die($db->erro);
	}
	
	$regs1 = $db->array_select[0];
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna, $linha, $regs1["PES_NOME"]);
	
	foreach($datas as $data=>$index)
	{
		//echo $data . "<br>";
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+2, $linha, $data);
		
		foreach($index as $a=>$hora)
		{
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+4, $linha, sec_to_time($hora[1]));
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+6, $linha, sec_to_time($hora[2]));
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+8, $linha, sec_to_time($hora[3]));
			
			$linha++;
		}
		$linha++;
	}
	
	$linha+=2;
}

$linha+=2;

$objWriter->save('php://output');

exit;
?>
