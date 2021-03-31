<?php
/*
		Relatorio Horas x Acessos - DIMEP
		
		Criado por Carlos Abreu
		
		local/Nome do arquivo:
		../rh/relatorios/rel_horas_acessos_excel.php
		
		Versão 0 --> VERSÃO INICIAL - 04/05/2006
		Versão 1 --> Inclusão dos campos reg_del nas consultas - 27/11/2017 - Carlos Abreu
		Versão 2 --> Inclusão do campo de trabalho em casa, interno e externo (cliente) - 27/02/2018 - Carlos Eduardo
*/

ini_set('max_execution_time', 0); // No time limit
ini_set('post_max_size', '20M');
ini_set('upload_max_filesize', '20M');
ini_set('memory_limit', '2048M');
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

switch($_POST["intervalo"])
{
	case "mes":
	
		if ($_POST["mes"]==1)
		{
			$mes=12;
			$ano=date('Y')-1;
			$data_ini = "26/" . $mes . "/" . $ano;
			$datafim = "25/01/" . date('Y');
		}
		else
		{ 
			$mesant = $_POST["mes"] - 1;
			//alteração aqui!!! 03/01/2008
			$ano=date('Y'); //retirado "-1" 07/02/2008 Otávio
			$data_ini = "26/" . sprintf("%02d",$mesant) . "/" . $ano;
			$datafim = "25/" . $_POST["mes"] . "/" . $ano;
		}
	break;
	
	case "periodo":
		
		$data_ini = $_POST["dataini"];
		$datafim = $_POST["datafim"];
		
	break;
	
	case "semana":
	
		ajustadata($_POST["semana"],$data_ini,$datafim);
	
	break;
}


$filtro0 = "";

$filtro1 = "";

//filtra a os (-1 TODOS OS FUNCIONARIOS)
if($_POST["funcionario"]!=-1)
{
	$sql = "SELECT * FROM ".DATABASE.".funcionarios ";
	$sql .= "WHERE id_funcionario='" . $_POST["funcionario"] . "' ";
	$sql .= "AND funcionarios.reg_del = 0 ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		die($db->erro);
	}
	
	$regs = $db->array_select[0];
	
	$filtro0 .= "AND PESSOAS.PES_NOME LIKE '%".$regs["funcionario"]."%' ";
	
	$filtro1 .= "AND funcionarios.id_funcionario = '".$regs["id_funcionario"]."' ";
	
}

//Seleciona
$sql = "SELECT CONVERT(nvarchar(10), MOV_DATAHORA, 103) AS DATA, CONVERT(CHAR(5),MOV_DATAHORA,8) AS HORA,* FROM PESSOAS, LOG_CREDENCIAL ";
$sql .= "WHERE PESSOAS.PES_NUMERO = LOG_CREDENCIAL.PES_NUMERO ";
$sql .= "AND MOV_ENTRADASAIDA IN ('1','2') "; //1 - ENTRADA / 2 - SAIDA / 3 - ACESSO PERMITIDO
$sql .= "AND EQPI_NUMERO <= '2' ";
$sql .= "AND MOV_DATAHORA BETWEEN '".str_replace("-","",php_mysql($data_ini))."' AND  '".str_replace("-","",php_mysql($datafim))."' ";
$sql .= $filtro0;
$sql .= "ORDER BY PESSOAS.PES_NOME, LOG_CREDENCIAL.MOV_DATAHORA, MOV_ENTRADASAIDA ";

$reg = $db->select($sql,'MSSQL', true);

if($db->erro!='')
{
	die($db->erro);
}

//Contabiliza Horas da Catraca
foreach($db->array_select as $regs0)
{	
	//entrada
	
	if($regs0["MOV_ENTRADASAIDA"]==1)
	{	
		$array_entsai[$regs0["PES_NOME"]][$regs0["DATA"]][1] = time_to_sec($regs0["HORA"]);
	}
	else
	{
		//saida
		if($regs0["MOV_ENTRADASAIDA"]==2)
		{
			$array_entsai[$regs0["PES_NOME"]][$regs0["DATA"]][2] = time_to_sec($regs0["HORA"]);
		
			$array_entsai[$regs0["PES_NOME"]][$regs0["DATA"]][3] += $array_entsai[$regs0["PES_NOME"]][$regs0["DATA"]][2]-$array_entsai[$regs0["PES_NOME"]][$regs0["DATA"]][1];
					
		}
	}
		
}

$sql = "SELECT 
            funcionario, ch.data, trabalho, horas_adicionais.hora_ini, horas_adicionais.hora_fim, 
            SUM(TIME_TO_SEC(ch.hora_normal)+TIME_TO_SEC(ch.hora_adicional)+TIME_TO_SEC(ch.hora_adicional_noturna)) AS HORAS,
            MAX(ch2.externo) externo, MIN(ch2.hora_inicial) hora_inicial, MAX(ch2.hora_final) hora_final
        FROM 
            ".DATABASE.".apontamento_horas ch
            LEFT JOIN ".DATABASE.".apontamento_horas ch2 ON ch2.id_apontamento_horas = ch.id_apontamento_horas AND ch.externo = 1
            LEFT JOIN ".DATABASE.".horas_adicionais 
                ON horas_adicionais.reg_del = 0 AND horas_adicionais.id_os = ch.id_os 
                AND horas_adicionais.id_funcionario = ch.id_funcionario 
                AND ch.data BETWEEN horas_adicionais.data_ini AND horas_adicionais.data_fim AND horas_adicionais.trabalho = 2
        ,".DATABASE.".funcionarios 
        WHERE ch.id_funcionario = funcionarios.id_funcionario 
            AND ch.reg_del = 0 AND funcionarios.reg_del = 0 ".$filtro1." 
            AND ch.data BETWEEN '". php_mysql($data_ini) ."' AND '". php_mysql($datafim) ."'
        GROUP BY 
               funcionarios.funcionario, ch.data, horas_adicionais.hora_ini, horas_adicionais.hora_fim
        ORDER BY 
               funcionarios.funcionario,ch.data";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}

$array_divergencias = array();
foreach($db->array_select as $regs1)
{
	$array_apont[$regs1["funcionario"]][mysql_php($regs1["data"])] = $regs1["HORAS"];
	
	if (intval($regs1['trabalho']) == 2)
	{
	    $array_divergencias[$regs1["funcionario"]][mysql_php($regs1["data"])] = "CASA ".substr($regs1['hora_ini'],0,5)." - ".substr($regs1['hora_fim'],0,5);
	}
	
	if (intval($regs1['externo']) == 1)
	{
	    $array_divergencias[$regs1["funcionario"]][mysql_php($regs1["data"])] = "EXTERNO ".substr($regs1['hora_inicial'],0,5)." - ".substr($regs1['hora_final'],0,5);
	}
}

$objPHPExcel = PHPExcel_IOFactory::load("../modelos_excel/horas_acesso_modelo.xls");

$locale = 'pt_br';

$validlocale = PHPExcel_Settings::setlocale($locale);

if (!$validlocale) 
{
	echo 'Unable to set locale to '.$locale." - reverting to en_us<br />\n";
}

// Redirect output to a clients web browser (Excel2007)
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

$objPHPExcel->setActiveSheetIndex(0);

//COLUNA A EXCELL
$coluna = 0;

$linha = 3;

foreach($array_apont as $pessoas=>$datas)
{
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna, $linha, $pessoas);
	
	$objPHPExcel->getActiveSheet()->getStyle("D".$linha)->getFont()->setBold(false)->setSize(10);
	
	$subtotal = 0;
	
	foreach($datas as $data=>$horas)
	{
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+1, $linha, $data);
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+2, $linha, substr(sec_to_time($horas),0,5));
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+3, $linha, substr(sec_to_time($array_entsai[$pessoas][$data][3]),0,5));
		 
		$dif_segundos = ($array_entsai[$pessoas][$data][3]-$horas);
		 
		if($dif_segundos<0)
		{
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+4, $linha," - ". substr(sec_to_time($dif_segundos),0,5));
		}
		else
		{
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+4, $linha,"   ". substr(sec_to_time($dif_segundos),0,5));
		}
		
		if (isset($array_divergencias[$pessoas][$data]))
		{
		    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+5, $linha,$array_divergencias[$pessoas][$data]);
		}
				
		$subtotal += ($dif_segundos);
		
		$linha++;
	}
	
	
	$objPHPExcel->getActiveSheet()->getStyle("D".$linha)->getFont()->setBold(true)->setSize(10);
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+3, $linha, 'SUBTOTAL');
	
	if($subtotal<0)
	{
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+4, $linha," - ". substr(sec_to_time($subtotal),0,5));
	}
	else
	{
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+4, $linha,"  ". substr(sec_to_time($subtotal),0,5));
	}
	
	$linha+=2;
	
}

$linha+=2;

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="horas_acessos_'.date('d-m-Y').'.xlsx"');
header('Cache-Control: max-age=0');

$objWriter->save('php://output');

exit;
?>
