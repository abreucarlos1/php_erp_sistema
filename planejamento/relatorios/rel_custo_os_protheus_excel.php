<?php
/*
		Relat�rio de Custo x OS
		
		Criado por Carlos Abreu / Ot�vio Pamplon ia
		
		local/Nome do arquivo:		
		../planejamento/relatorios/rel_custo_os_protheus_excel.php
		
		Vers�o 0 --> VERS�O INICIAL - 02/03/2006
		Vers�o 1 --> atualiza��o classe banco de dados - 22/01/2015 - Carlos Abreu
		Vers�o 2 --> Inclus�o dos campos reg_del nas consultas - 20/11/2017 - Carlos Abreu	
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

require_once(INCLUDE_DIR."include_pdf.inc.php");

require_once(INCLUDE_DIR."PHPExcel/Classes/PHPExcel.php"); 

$db = new banco_dados();

$os = $_POST["escolhaos"];

$filtro0 = "";
$filtro1 = "";

$total = 0;

$array_custo = NULL;

//filtra a os (-1 TODAS AS OS)
if($os!=-1)
{
	$filtro0 .= "AND AF8_PROJET = '".sprintf("%010d",$os)."' ";
	$txt = $os;

}
else
{
	//Execu��o, Aguard. Def. Clien., As built, ADMs
	$filtro0 .= "AND AF8_PROJET > '0000003000' ";
	$filtro0 .= "AND AF8_FASE IN ('03','05','07','09','12') ";
	$txt = '_TODAS_OS_';
}

if($_POST["intervalo"]=='1')
{
	$filtro0 .= "AND AF8_START >= '" . mysql_protheus(php_mysql($_POST["data_ini"])) . "' ";
	$filtro1 = "AND (AF9_START >= '" . mysql_protheus(php_mysql($_POST["data_ini"])) . "' ";
	$filtro1 .= "OR AF9_DTATUI >= '" . mysql_protheus(php_mysql($_POST["data_ini"])) . "') ";
}

//Seleciona as OSs
$sql = "SELECT * FROM AF8010 ";
$sql .= "WHERE D_E_L_E_T_ = '' ";
$sql .= $filtro0;

$con0 = $db->select($sql, 'MSSQL', true);

foreach($db->array_select as $regs0)
{
	//PEGA A ULTIMA REVIS�O DA FASE 01 (OR�AMENTO)
	$sql = "SELECT MAX(AFE_REVISA) AS ULT_REVISA FROM AFE010 ";
	$sql .= "WHERE AFE010.D_E_L_E_T_ = '' ";
	$sql .= "AND AFE010.AFE_PROJET = '".$regs0["AF8_PROJET"]."' ";
	$sql .= "AND AFE010.AFE_FASE = '01' ";
	
	$con1 = $db->select($sql, 'MSSQL', true);
	$regs_ult_rev = $db->array_select[0];
	
	//Obtem o custo na ultima fase de or�amento	
	$sql = "SELECT SUM(AF9_TOTAL) AS TOTAL FROM AF9010 ";
	$sql .= "WHERE AF9010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF9010.AF9_PROJET = '".$regs0["AF8_PROJET"]."' ";
	$sql .= "AND AF9010.AF9_REVISA = '".$regs_ult_rev["ULT_REVISA"]."' ";
	$sql .= $filtro1;
	
	$con3 = $db->select($sql, 'MSSQL', true);
	$regs_custo = $db->array_select[0];
	
	//OBTEM AS DATAS
	$sql = "select MIN(AF8_START) AS START_PREV, MAX(AF8_FINISH) AS FINISH_PREV, MIN(AF8_DTATUI) AS START_REAL, MAX(AF8_DTATUF) FINISH_REAL ";
	$sql .= "from AF8010 ";
	$sql .= "WHERE D_E_L_E_T_ = '' ";
	$sql .= "AND (AF8_START <> '' OR AF8_DTATUI <> '' OR AF8_FINISH <> '' OR AF8_DTATUF <> '') ";
	$sql .= "AND AF8_PROJET = '".$regs0["AF8_PROJET"]."' ";
	$sql .= "AND AF8_REVISA = '" . $regs0["AF8_REVISA"] . "' ";
	$sql .= $filtro0;
	
	$con = $db->select($sql, 'MSSQL', true);
	$regs_datas = $db->array_select[0];
	
	//Pega a quantidade de dias no projeto
	//Verifica se existe avan�o (AF8_DTATUI) DATA INI
	if(trim($regs_datas["START_REAL"])!="")
	{
		//formato DD/MM/AAAA
		//Se a data final prevista for menor ou igual a data inicial real
		//, assume a data inicial prevista
		if($regs_datas["FINISH_PREV"]<=$regs_datas["START_REAL"])
		{			
			$data_ini_proj = mysql_php(protheus_mysql($regs_datas["START_PREV"]));
		}
		else
		{
			$data_ini_proj = mysql_php(protheus_mysql($regs_datas["START_REAL"]));
		}
	}
	else
	{
		$data_ini_proj = mysql_php(protheus_mysql($regs_datas["START_PREV"]));
	}
	
	//Verifica se existe avan�o (AF8_DTATUF) DATA FIM
	if(trim($regs_datas["FINISH_REAL"])!="")
	{
		//formato DD/MM/AAAA
		$data_fim_proj = mysql_php(protheus_mysql($regs_datas["FINISH_REAL"]));
	}
	else
	{
		$data_fim_proj = mysql_php(protheus_mysql($regs_datas["FINISH_PREV"]));
	}	
	
	//Percorre as datas		
	$data_format_ini_proj = explode("/",$data_ini_proj);
	
	$data_stamp_ini_proj = mktime(0,0,0,$data_format_ini_proj[1],$data_format_ini_proj[0],$data_format_ini_proj[2]);
	
	$data_format_fim_proj = explode("/",$data_fim_proj);
	
	$data_stamp_fim_proj = mktime(0,0,0,$data_format_fim_proj[1],$data_format_fim_proj[0],$data_format_fim_proj[2]);
	
	$qtd_dias_proj = 0;
	
	//computa a quantidade de dias entre as datas inicial e final (inclusive)
	for($i=$data_stamp_ini_proj;$i<=$data_stamp_fim_proj;$i+=86400) //60*60*24 (1 dia)
	{			
		$qtd_dias_proj++;
	}
	
	//Percorre as tarefas, pegando a data real e prevista para calcular o custo
	$sql = "SELECT * FROM AF9010 ";
	$sql .= "WHERE AF9010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF9_PROJET = '" . $regs0["AF8_PROJET"] . "' ";	
	$sql .= "AND AF9_REVISA = '".$regs0["AF8_REVISA"]."' ";
	$sql .= $filtro1;
	$sql .= "ORDER BY AF9_TAREFA ";
	
	$con2 = $db->select($sql, 'MSSQL', true);
	
	foreach($db->array_select as $regs2)
	{	
		//Verifica se existe avan�o (AF9_DTATUI) DATA INI
		if(trim($regs2["AF9_DTATUI"])!="")
		{
			//formato DD/MM/AAAA
			if($regs2["AF9_FINISH"]<=$regs2["AF9_DTATUI"])
			{			
				$data_ini = mysql_php(protheus_mysql($regs2["AF9_START"]));
			}
			else
			{
				$data_ini = mysql_php(protheus_mysql($regs2["AF9_DTATUI"]));
			}
		}
		else
		{
			$data_ini = mysql_php(protheus_mysql($regs2["AF9_START"]));
		}
		
		//Verifica se existe avan�o (AF9_DTATUF) DATA FIM
		if(trim($regs2["AF9_DTATUF"])!="")
		{
			//formato DD/MM/AAAA
			$data_fim = mysql_php(protheus_mysql($regs2["AF9_DTATUF"]));
		}
		else
		{
			$data_fim = mysql_php(protheus_mysql($regs2["AF9_FINISH"]));
		}	
		
		//Percorre as datas		
		$data_format_ini = explode("/",$data_ini);
	
		$data_stamp_ini = mktime(0,0,0,$data_format_ini[1],$data_format_ini[0],$data_format_ini[2]);
	
		$data_format_fim = explode("/",$data_fim);
	
		$data_stamp_fim = mktime(0,0,0,$data_format_fim[1],$data_format_fim[0],$data_format_fim[2]);
				
		$qtd_dias = 0;

		//computa a quantidade de dias entre as datas inicial e final (inclusive)
		for($i=$data_stamp_ini;$i<=$data_stamp_fim;$i+=86400) //60*60*24 (1 dia)
		{			
			$qtd_dias++;
		}
		
		//calcula o fator multiplicativo com rela��o entre dias do projeto/dias da tarefa
		$fator_custo = ($qtd_dias/$qtd_dias_proj) * $regs_custo["TOTAL"];
		
		$data = $data_ini;		
	
		$sub_total = $regs_custo["TOTAL"];
		
		for($d=1;$d<=$qtd_dias;$d++)
		{			
			//acumula o custo por dia			
			$index = explode("/",$data);
			
			$custo_diario[$index[2].$index[1].$index[0]] += ($fator_custo/$qtd_dias);		
		
			echo trim($regs2["AF9_TAREFA"]) ." == ".$data." %%% ".$qtd_dias." # ".$qtd_dias_proj." - ".$fator_custo." -- ".$regs_custo["TOTAL"]."<br>";
			
			//incrementa a data
			$data = calcula_data($data, "sum", "day", "1");			
			 
		}		
			
	}	
	
	$array_custo[$regs0["AF8_PROJET"]] = $sub_total;
	$proj = $regs0["AF8_PROJET"];
	$descricao = $regs0["AF8_DESCRI"];
	$ver_orc = $regs_ult_rev["ULT_REVISA"];
	$ver_rea = $regs0["AF8_REVISA"];
}

ksort($custo_diario);

if(is_file("modelos_excel/custos_".$os.".xls"))
{
	$objPHPExcel = PHPExcel_IOFactory::load("modelos_excel/custos_".$txt.".xls");
}
else
{
	$objPHPExcel = PHPExcel_IOFactory::load("modelos_excel/custos_modelo.xls");
}

$locale = 'pt_br';

$validlocale = PHPExcel_Settings::setlocale($locale);

if (!$validlocale) 
{
	echo 'Unable to set locale to '.$locale." - reverting to en_us<br />\n";
}

// Redirect output to a client�s web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="custos_'.$txt.'.xlsx"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

$objPHPExcel->setActiveSheetIndex(0);

//se for os especifica
if($os!=-1)
{	
	//OS
	$objPHPExcel->getActiveSheet()->getStyle('A2')->getNumberFormat()->setFormatCode('0000000000');			
	$objPHPExcel->getActiveSheet()->setCellValue('A2', sprintf("%010d",$proj));
	
	//DESCRI��O
	$objPHPExcel->getActiveSheet()->setCellValue('B2', iconv('ISO-8859-1', 'UTF-8', $descricao));
	
	//VERSAO ORCAMENTO
	$objPHPExcel->getActiveSheet()->getStyle('A5')->getNumberFormat()->setFormatCode('0000');			
	$objPHPExcel->getActiveSheet()->setCellValue('A5', sprintf("%04d",$ver_orc));
	
	//VERSAO ATUAL
	$objPHPExcel->getActiveSheet()->getStyle('C5')->getNumberFormat()->setFormatCode('0000');			
	$objPHPExcel->getActiveSheet()->setCellValue('C5', sprintf("%04d",$ver_rea));
	
	//DATA EMISS�O
	$objPHPExcel->getActiveSheet()->setCellValue('N5', iconv('ISO-8859-1', 'UTF-8',date("d/m/Y")));
}
else
{
	//OS
	//$objPHPExcel->getActiveSheet()->getStyle('A2')->getNumberFormat()->setFormatCode('0000000000');			
	$objPHPExcel->getActiveSheet()->setCellValue('A2', 'TODAS AS OS');
	
	//DATA EMISS�O
	$objPHPExcel->getActiveSheet()->setCellValue('N5', iconv('ISO-8859-1', 'UTF-8',date("d/m/Y")));
	
}

$col_cell1 = 3; //COLUNA 'C' DO EXCEL
$row_cell1 = 37; //linha

$total_mes = NULL;

$array_mes = NULL;

$array_ano = NULL;

$indice = 0;

foreach($custo_diario as $data=>$fator_custo)
{
	$dia_mes = substr($data,6,2);
	
	$mes = substr($data,4,2);
	
	$ano = substr($data,2,2);
	
	$array_mes[$indice] = $mes;
	
	$array_ano[$indice] = $ano;
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col_cell1, $row_cell1,mysql_php(protheus_mysql($data))." - ".$dia_mes);
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col_cell1, $row_cell1+1,number_format($fator_custo,2,",",""));
	
	$col_cell1++;
	
	if($dia_mes!=20)
	{
		//dia 21/m ate 19/m-1
		$total_mes[$indice] += $fator_custo;
	}
	else
	{
		//dia 20/m-1
		$total_mes[$indice] += $fator_custo;
		
		$indice++;
	}
}

$col_cell = 3; //COLUNA 'C' DO EXCEL
$row_cell = 35; //linha

for($i=0;$i<=count($total_mes)-1;$i++)
{			
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col_cell, $row_cell,'21/'.sprintf("%02d",($array_mes[$i]))."/".$array_ano[$i]."-20/".$array_mes[$i]."/".$array_ano[$i]);
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col_cell, $row_cell+1,number_format($total_mes[$i],2,",",""));
	
	$col_cell++;	
}

$objWriter->save('php://output');

exit;