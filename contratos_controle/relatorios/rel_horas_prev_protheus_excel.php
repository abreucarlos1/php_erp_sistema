<?php
/*
	Relatório de Horas Previstas Protheus
	Criado por Carlos Abreu  
	
	Versão 0 --> VERSÃO INICIAL : 25/06/2015
	Versão 1 --> Inclusão dos campos reg_del nas consultas - 17/11/2017 - Carlos Abreu
*/

ini_set('max_execution_time', 0); // No time limit
ini_set('post_max_size', '20M');
ini_set('memory_limit', '512M');
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

$nome_projeto = "";

$nome_cliente = "";

$coordenador = "";

$array_horas_prev = NULL;

$array_horas_real = NULL;

$array_ana_horas_prev = NULL;

$array_ana_horas_real = NULL;

$total_horas_prev = 0;

$total_horas_real = 0;

//filtra a os (-1 TODAS AS OS)
if($_POST["escolhafase"]!=-1)
{
	$filtro0 .= "AND AF8_FASE = '".$_POST["escolhafase"]."' ";
}

//filtra a os (-1 TODAS AS OS)
if($_POST["escolhaos"]!=-1)
{
	$filtro0 .= "AND AF1_ORCAME = '".sprintf("%010d",$_POST["escolhaos"])."' ";
}
else
{
	$nome_projeto = "TODOS";
	
	$nome_cliente = "TODOS";	
}

if($_POST["escolhacoord"]!=-1)
{
	$filtro0 .= "AND (AF1_COORD1 = '".$_POST["escolhacoord"]."' ";
	$filtro0 .= "OR AF1_COORD2 = '".$_POST["escolhacoord"]."') ";
}
else
{
	$coordenador = "TODOS";	
}

//TABELA AE5 - GRUPOS DE COMPOSI��O
$sql = "SELECT * FROM AE5010 WITH(NOLOCK) ";
$sql .= "WHERE AE5010.D_E_L_E_T_ = '' ";

$db->select($sql,'MSSQL', true);

if($db->erro!='')
{
	die($db->erro);
}

foreach($db->array_select as $regs0)
{
	$array_grpcom[trim($regs0["AE5_GRPCOM"])] = $regs0["AE5_DESCRI"];
}

//TABELA PA7 - COORDENADORES
$sql = "SELECT * FROM PA7010 WITH(NOLOCK) ";
$sql .= "WHERE PA7010.D_E_L_E_T_ = '' ";

$db->select($sql,'MSSQL',true);

if($db->erro!='')
{
	die($db->erro);
}

foreach($db->array_select as $regs)
{
	$array_coord[$regs["PA7_ID"]] = $regs["PA7_NOME"];
}

//Seleciona as PROJETOS
$sql = "SELECT * FROM AF8010 WITH(NOLOCK), AF1010 WITH(NOLOCK), SA1010 WITH(NOLOCK) ";
$sql .= "WHERE AF8010.D_E_L_E_T_ = '' ";
$sql .= "AND AF1010.D_E_L_E_T_ = '' ";
$sql .= "AND SA1010.D_E_L_E_T_ = '' ";
$sql .= "AND AF1010.AF1_CLIENT = SA1010.A1_COD ";
$sql .= "AND AF1010.AF1_LOJA = SA1010.A1_LOJA ";
$sql .= "AND AF8_ORCAME = AF1_ORCAME ";
$sql .= "AND AF1_FASE IN ('04') "; //ORCAMENTO APROVADO
$sql .= $filtro0;
$sql .= "ORDER BY AF1_ORCAME ";

$db->select($sql,'MSSQL', true);

if($db->erro!='')
{
	die($db->erro);
}

$array_proj = $db->array_select;

foreach($array_proj as $regs1)
{	
	$os = intval($regs1["AF8_PROJET"]); //retira os zeros a esquerda
	
	$sql = "SELECT nome_contato FROM ".DATABASE.".OS, ".DATABASE.".contatos ";
	$sql .= "WHERE os.os = '". $os."' ";
	$sql .= "AND OS.reg_del = 0 ";
	$sql .= "AND contatos.reg_del = 0 ";
	$sql .= "AND OS.id_cod_resp = contatos.id_contato ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		die($db->erro);
	}
	
	$regs_os = $db->array_select[0];	
		
	if($_POST["escolhaos"]!=-1 || $_POST["escolhacoord"]!=-1)
	{
		$coordenador = $array_coord[$regs1["AF1_COORD1"]] . " - " .$array_coord[$regs1["AF1_COORD2"]];
	}
	
	if($nome_projeto=="")
	{
		$nome_projeto = $regs1["AF8_PROJET"]." - ".$regs1["AF8_DESCRI"];
	}
	
	if($nome_cliente=="")
	{
		$nome_cliente = $regs1["A1_NOME"];
	}
	
	//analitico
	$array_ana_proj[$regs1["AF8_PROJET"]] = str_replace(array("'", '"', '\"', "\'"),"",$regs1["AF8_DESCRI"]);
	$array_ana_coord[$regs1["AF8_PROJET"]]= str_replace(array("'", '"', '\"', "\'"),"",$array_coord[$regs1["AF1_COORD1"]] . " - " .$array_coord[$regs1["AF1_COORD2"]]);
	$array_ana_client[$regs1["AF8_PROJET"]] = str_replace(array("'", '"', '\"', "\'"),"",$regs1["A1_NOME"]);
	$array_ana_coord_client[$regs1["AF8_PROJET"]] = $regs_os["nome_contato"];
	
	
	//TABELA AF2 - TAREFAS ORCAMENTO - HORAS PREVISTAS
	$sql = "SELECT * FROM AE8010 WITH(NOLOCK), AF2010 WITH(NOLOCK), AF3010 WITH(NOLOCK) ";
	$sql .= "WHERE AF2010.D_E_L_E_T_ = '' ";
	$sql .= "AND AE8010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF3010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF3_RECURS = AE8_RECURS ";
	$sql .= "AND AF3_TAREFA = AF2_TAREFA ";
	$sql .= "AND AF3_ORCAME = AF2_ORCAME ";
	$sql .= "AND AF2_ORCAME = '".$regs1["AF1_ORCAME"]."' ";
	$sql .= "AND AF2_CODIGO <> '' ";
	$sql .= "ORDER BY AF2_GRPCOM ";	

	$db->select($sql,'MSSQL', true);

	if($db->erro!='')
	{
		die($db->erro);
	}
	
	$array_tarefas = $db->array_select;

	foreach($array_tarefas as $regs2)
	{
		if(trim($regs2["AF2_GRPCOM"])!='DES' && !in_array(trim($regs2["AF2_COMPOS"]),array('SUP12','SUP13','SUP14','SUP15','SUP16','SUP17')))
		{	
			$recurs = explode("_",$regs2["AF3_RECURS"]);
			
			//TABELA Categorias
			$sql = "SELECT * FROM ".DATABASE.".rh_categorias, ".DATABASE.".rh_cargos ";
			$sql .= "WHERE rh_cargos.id_categoria = rh_categorias.id_categoria ";
			$sql .= "AND rh_cargos.reg_del = 0 ";
			$sql .= "AND rh_categorias.reg_del = 0 ";

			//se recurso orc, obtem as horas do protheus
			if($recurs[0]=='ORC')
			{
				$sql .= "AND rh_cargos.id_cargo_grupo = '".$regs2["AE8_ID_CAR"]."' ";
			}
			else
			{
				$sql .= "AND rh_cargos.id_cargo_grupo = '".intval($regs2["AE8_FUNCAO"])."' ";
			}

			$db->select($sql,'MYSQL',true);

			if($db->erro!='')
			{
				die($db->erro);
			}
			
			$regs0 = $db->array_select[0];

			//gerencial
			if($regs0["categoria"]!="")
			{
				$array_horas_prev[$array_grpcom[trim($regs2["AF2_GRPCOM"])]][$regs0["categoria"]] += $regs2["AF3_QUANT"];
			
				$array_ana_horas_prev[$regs2["AF2_ORCAME"]][$array_grpcom[trim($regs2["AF2_GRPCOM"])]][$regs0["categoria"]] += $regs2["AF3_QUANT"];
			}
			else
			{
				$array_horas_prev[$array_grpcom[trim($regs2["AF2_GRPCOM"])]][$regs2["AF3_RECURS"]] += $regs2["AF3_QUANT"];
			
				$array_ana_horas_prev[$regs2["AF2_ORCAME"]][$array_grpcom[trim($regs2["AF2_GRPCOM"])]][$regs2["AF3_RECURS"]] += $regs2["AF3_QUANT"];
			}
			
			ksort($array_horas_prev[$array_grpcom[trim($regs2["AF2_GRPCOM"])]]);
							
		}
	}	
	
	//TABELA AF9 - TAREFAS PROJETOS - SALDO 0
	$sql = "SELECT * FROM AE8010 WITH(NOLOCK), AF8010 WITH(NOLOCK), AF9010 WITH(NOLOCK), AFA010 WITH(NOLOCK) ";
	$sql .= "WHERE AF9010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF8010.D_E_L_E_T_ = '' ";
	$sql .= "AND AE8010.D_E_L_E_T_ = '' ";
	$sql .= "AND AFA010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF8_PROJET = AF9_PROJET ";
	$sql .= "AND AF8_REVISA = AF9_REVISA ";
	$sql .= "AND AF9_PROJET = AFA_PROJET ";
	$sql .= "AND AF9_REVISA = AFA_REVISA ";
	$sql .= "AND AFA_RECURS = AE8_RECURS ";
	$sql .= "AND AFA_TAREFA = AF9_TAREFA ";
	$sql .= "AND AF8_ORCAME = '".$regs1["AF1_ORCAME"]."' ";
	$sql .= "AND AF9_CODIGO = '' ";
	$sql .= "ORDER BY AF9_GRPCOM ";

	$db->select($sql,'MSSQL', true);

	if($db->erro!='')
	{
		die($db->erro);
	}
	
	$array_tarefas = $db->array_select;

	foreach($array_tarefas as $regs2)
	{
		if(trim($regs2["AF9_GRPCOM"])!='DES' && !in_array(trim($regs2["AF9_COMPOS"]),array('SUP12','SUP13','SUP14','SUP15','SUP16','SUP17')))
		{	
			$recurs = explode("_",$regs2["AFA_RECURS"]);
			
			//TABELA Categorias
			$sql = "SELECT * FROM ".DATABASE.".rh_categorias, ".DATABASE.".rh_cargos ";
			$sql .= "WHERE rh_cargos.id_categoria = rh_categorias.id_categoria ";
			$sql .= "AND rh_cargos.reg_del = 0 ";
			$sql .= "AND rh_categorias.reg_del = 0 ";
			
			//se recurso orc, obtem o custo direto do protheus
			if($recurs[0]=='ORC')
			{			
				$sql .= "AND rh_cargos.id_cargo_grupo = '".$regs2["AE8_ID_CAR"]."' ";
			}
			else
			{
				$sql .= "AND rh_cargos.id_cargo_grupo = '".intval($regs2["AE8_FUNCAO"])."' ";

			}

			$db->select($sql,'MYSQL',true);

			if($db->erro!='')
			{
				die($db->erro);
			}
			
			$regs0 = $db->array_select[0];
							
			//gerencial
			if($regs0["categoria"]!="")
			{
				$array_horas_prev[$array_grpcom[trim($regs2["AF9_GRPCOM"])]][$regs0["categoria"]] += 0;
			
				$array_ana_horas_prev[$regs2["AF9_PROJET"]][$array_grpcom[trim($regs2["AF9_GRPCOM"])]][$regs0["categoria"]] += 0;
			}
			else
			{
				$array_horas_prev[$array_grpcom[trim($regs2["AF9_GRPCOM"])]][$regs2["AFA_RECURS"]] += 0;
			
				$array_ana_horas_prev[$regs2["AF9_PROJET"]][$array_grpcom[trim($regs2["AF9_GRPCOM"])]][$regs2["AFA_RECURS"]] += 0;
			}
			
			ksort($array_horas_prev[$array_grpcom[trim($regs2["AF9_GRPCOM"])]]);			
		}

	}
	
	//HORAS REALIZADAS			
	$sql = "SELECT * FROM AE8010 WITH(NOLOCK), AJK010 WITH(NOLOCK), AF8010 WITH(NOLOCK), AF9010 WITH(NOLOCK) ";
	$sql .= "WHERE AJK010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF8010.D_E_L_E_T_ = '' ";
	$sql .= "AND AE8010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF9010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF8_PROJET = AF9_PROJET ";
	$sql .= "AND AF8_REVISA = AF9_REVISA ";
	$sql .= "AND AF9_TAREFA = AJK_TAREFA ";
	$sql .= "AND AJK_PROJET = AF8_PROJET ";
	$sql .= "AND AJK_REVISA = AF8_REVISA ";
	$sql .= "AND AJK_RECURS = AE8_RECURS ";
	$sql .= "AND AF8_ORCAME = '".$regs1["AF1_ORCAME"]."' ";

	$db->select($sql,'MSSQL', true);
	
	if($db->erro!='')
	{
		die($db->erro);
	}
	
	$array_horas = $db->array_select;

	foreach($array_horas as $regs6)
	{		
		//TABELA Categorias
		$sql = "SELECT * FROM ".DATABASE.".rh_categorias, ".DATABASE.".rh_cargos ";
		$sql .= "WHERE rh_cargos.id_categoria = rh_categorias.id_categoria ";
		$sql .= "AND rh_cargos.reg_del = 0 ";
		$sql .= "AND rh_categorias.reg_del = 0 ";
		$sql .= "AND rh_cargos.id_cargo_grupo = '".intval($regs6["AE8_FUNCAO"])."' ";

		$db->select($sql,'MYSQL',true);

		if($db->erro!='')
		{
			die($db->erro);
		}
		
		$regs0 = $db->array_select[0];
		
		$array_horas_prev[$array_grpcom[trim($regs6["AF9_GRPCOM"])]][$regs0["categoria"]] += 0;
	
		$array_ana_horas_prev[$regs6["AF9_PROJET"]][$array_grpcom[trim($regs6["AF9_GRPCOM"])]][$regs0["categoria"]] += 0;
														
		$array_horas_real[$array_grpcom[trim($regs6["AF9_GRPCOM"])]][$regs0["categoria"]] += $regs6["AJK_HQUANT"];		
		
		$array_ana_horas_real[$regs6["AF8_ORCAME"]][$array_grpcom[trim($regs6["AF9_GRPCOM"])]][$regs0["categoria"]] += $regs6["AJK_HQUANT"];
	}
}

ksort($array_horas_prev);

$objPHPExcel = PHPExcel_IOFactory::load("../modelos_excel/horas_prev_modelo.xls");

$locale = 'pt_br';

$validlocale = PHPExcel_Settings::setlocale($locale);

if (!$validlocale) 
{
	echo 'Unable to set locale to '.$locale." - reverting to en_us<br />\n";
}

// Redirect output to a client�s web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="horas_prev_"'.date('His').'".xlsx"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

//1� folha
$objPHPExcel->setActiveSheetIndex(0);

//data emiss�o
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, 3, iconv('ISO-8859-1', 'UTF-8',"data de emiss�o: ".date('d/m/Y')));

//Nome Projeto
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, 5, iconv('ISO-8859-1', 'UTF-8',$nome_projeto));

//Nome cliente
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, 6, iconv('ISO-8859-1', 'UTF-8',$nome_cliente));

//Nome coordenador Devemada
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, 7, iconv('ISO-8859-1', 'UTF-8',$coordenador));

//Nome coordenador cliente
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, 8, iconv('ISO-8859-1', 'UTF-8',$regs_os["nome_contato"]));

$i = 1;

$linha = 12;

$coluna = 5;

foreach($array_horas_prev as $disciplinas=>$categorias)
{	
	$objPHPExcel->getActiveSheet()->insertNewRowBefore($linha,1);
	
	//item
	$objPHPExcel->getActiveSheet()->getStyle('A'.$linha.":K".$linha)->getFont()->setBold(false)->setSize(10);
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $linha, iconv('ISO-8859-1', 'UTF-8',$i));

	//disciplina
	$objPHPExcel->getActiveSheet()->getStyle('B'.$linha.":B".$linha)->getFont()->setBold(true)->setSize(10);
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $linha, iconv('ISO-8859-1', 'UTF-8',$disciplinas));

	$objPHPExcel->getActiveSheet()->mergeCells(num2alfa(1).$linha.":".num2alfa(4).$linha);

	foreach($categorias as $categ=>$horas_prev)
	{
		$linha+=1;
		
		$objPHPExcel->getActiveSheet()->insertNewRowBefore($linha,1);
		
		//categoria
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, $linha, iconv('ISO-8859-1', 'UTF-8',$categ));
	
		$objPHPExcel->getActiveSheet()->mergeCells(num2alfa(5).$linha.":".num2alfa(7).$linha);
		
		//horas previstas
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8, $linha, $horas_prev?$horas_prev:0);
	
		//horas real
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(9, $linha, $array_horas_real[$disciplinas][$categ]);

		if($horas_prev<$array_horas_real[$disciplinas][$categ])
		{
			$objPHPExcel->getActiveSheet()->getStyle(num2alfa(10).$linha)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
		}
		else
		{
			$objPHPExcel->getActiveSheet()->getStyle(num2alfa(10).$linha)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_BLACK);	
		}
	
		//saldo
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(10, $linha, "=".num2alfa(8).$linha."-".num2alfa(9).$linha);


	}
	
	$linha+=1;
	
	$i++;	
	
}

//Monta os sub-totais
for($j=8;$j<=9;$j++)
{
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($j, $linha, "=SUM(".num2alfa($j)."12:".num2alfa($j).($linha-1).")");		
}

//sumariza o total
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(10, $linha, "=(".num2alfa(8).$linha."-".num2alfa(9).($linha).")");	


//2� folha
$objPHPExcel->setActiveSheetIndex(1);

$linha = 1;

ksort($array_ana_proj);

foreach($array_ana_proj as $projeto=>$descricao)
{
	//Nome Projeto
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $linha, "Projeto: ");
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $linha, iconv('ISO-8859-1', 'UTF-8',$projeto." - ".$descricao));
	$objPHPExcel->getActiveSheet()->mergeCells("A".$linha.":B".$linha);
	$objPHPExcel->getActiveSheet()->mergeCells("C".$linha.":I".$linha);

	//Nome cliente
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $linha+1, "Cliente: ");
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $linha+1, iconv('ISO-8859-1', 'UTF-8',$array_ana_client[$projeto]));
	$objPHPExcel->getActiveSheet()->mergeCells("A".($linha+1).":B".($linha+1));
	$objPHPExcel->getActiveSheet()->mergeCells("C".($linha+1).":I".($linha+1));

	
	//Nome coordenador DVM
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $linha+2, "Coordenador DVM: ");
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $linha+2, iconv('ISO-8859-1', 'UTF-8',$array_ana_coord[$projeto]));
	$objPHPExcel->getActiveSheet()->mergeCells("A".($linha+2).":B".($linha+2));
	$objPHPExcel->getActiveSheet()->mergeCells("C".($linha+2).":I".($linha+2));

	//Nome coordenador Cliente
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $linha+3, "Coordenador Cliente: ");
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $linha+3, iconv('ISO-8859-1', 'UTF-8',$array_ana_coord_client[$projeto]));
	$objPHPExcel->getActiveSheet()->mergeCells("A".($linha+3).":B".($linha+3));
	$objPHPExcel->getActiveSheet()->mergeCells("C".($linha+3).":I".($linha+3));

	//cabe�alho
	$objPHPExcel->getActiveSheet()->mergeCells("A".($linha+4).":K".($linha+4));

	//item
	$objPHPExcel->getActiveSheet()->getStyle("A".($linha+5))->getFont()->setBold(true)->setSize(12);
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $linha+5, "Item");
	$objPHPExcel->getActiveSheet()->mergeCells("A".($linha+5).":A".($linha+6));
	$objPHPExcel->getActiveSheet()->getStyle("A".($linha+5))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	$objPHPExcel->getActiveSheet()->getStyle("A".($linha+5))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	
	//disciplina
	$objPHPExcel->getActiveSheet()->getStyle("B".($linha+5))->getFont()->setBold(true)->setSize(12);
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $linha+5, "Disciplina");
	$objPHPExcel->getActiveSheet()->mergeCells("B".($linha+5).":E".($linha+6));
	$objPHPExcel->getActiveSheet()->getStyle("B".($linha+5))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	$objPHPExcel->getActiveSheet()->getStyle("B".($linha+5))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	
	//categorias
	$objPHPExcel->getActiveSheet()->getStyle("F".($linha+5))->getFont()->setBold(true)->setSize(12);
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, $linha+5, "Categorias");
	$objPHPExcel->getActiveSheet()->mergeCells("F".($linha+5).":H".($linha+6));
	$objPHPExcel->getActiveSheet()->getStyle("F".($linha+5))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	$objPHPExcel->getActiveSheet()->getStyle("F".($linha+5))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

	//HORAS
	$objPHPExcel->getActiveSheet()->mergeCells("I".($linha+5).":K".($linha+5));
	
	$objPHPExcel->getActiveSheet()->getStyle("I".($linha+6).":"."K".($linha+6))->getFont()->setBold(true)->setSize(12);
	$objPHPExcel->getActiveSheet()->getStyle("I".($linha+6).":"."K".($linha+6))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8, $linha+6, "Horas Previstas");
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(9, $linha+6, "Horas Realizadas");
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(10, $linha+6, "Saldo");

	$i = 1;
	
	$linha+=7;
	
	$linha_tmp = $linha;
	
	$total_prev = 0;
	$total_real = 0;
	
	foreach($array_ana_horas_prev[$projeto] as $disciplinas=>$categorias)
	{
		$objPHPExcel->getActiveSheet()->insertNewRowBefore($linha,1);			
				
		//item
		$objPHPExcel->getActiveSheet()->getStyle("A".($linha))->getFont()->setBold(FALSE)->setSize(10);
		$objPHPExcel->getActiveSheet()->getStyle("A".($linha).":"."A".($linha))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $linha, $i);
		
	
		//disciplina
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $linha, iconv('ISO-8859-1', 'UTF-8',$disciplinas));
		$objPHPExcel->getActiveSheet()->mergeCells("B".$linha.":E".$linha);
		$objPHPExcel->getActiveSheet()->getStyle("B".($linha).":"."E".($linha))->getFont()->setBold(TRUE)->setSize(10);
		$objPHPExcel->getActiveSheet()->getStyle("B".($linha).":"."E".($linha))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);


		$linha_tmp1 = $linha+1;
		
		foreach($categorias as $categoria=>$horas_prev)
		{
			$linha++;
			
			//CATEGORIAS
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, $linha, $categoria);
			$objPHPExcel->getActiveSheet()->mergeCells("F".$linha.":H".$linha);
			$objPHPExcel->getActiveSheet()->getStyle("F".($linha).":"."H".($linha))->getFont()->setBold(FALSE)->setSize(10);
			$objPHPExcel->getActiveSheet()->getStyle("F".($linha).":"."H".($linha))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			
			//HORAS PREVISTAS
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8, $linha, $horas_prev);
			$objPHPExcel->getActiveSheet()->getStyle("I".($linha).":"."I".($linha))->getFont()->setBold(FALSE)->setSize(10);
			$objPHPExcel->getActiveSheet()->getStyle("I".($linha).":"."I".($linha))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$objPHPExcel->getActiveSheet()->getStyle("I".$linha)->getNumberFormat()->setFormatCode('#0.00');
			
			$total_prev += $horas_prev;
			
			$total_real += $array_ana_horas_real[$projeto][$disciplinas][$categoria];
			
			//HORAS REALIZADAS
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(9, $linha, $array_ana_horas_real[$projeto][$disciplinas][$categoria]);
			$objPHPExcel->getActiveSheet()->getStyle("J".($linha).":"."J".($linha))->getFont()->setBold(FALSE)->setSize(10);
			$objPHPExcel->getActiveSheet()->getStyle("J".($linha).":"."J".($linha))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$objPHPExcel->getActiveSheet()->getStyle("J".$linha)->getNumberFormat()->setFormatCode('#0.00');	
		
			//SALDO
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(10, $linha, "=I".$linha."-J".$linha);
			$objPHPExcel->getActiveSheet()->getStyle("K".($linha).":"."K".($linha))->getFont()->setBold(FALSE)->setSize(10);
			$objPHPExcel->getActiveSheet()->getStyle("K".($linha).":"."K".($linha))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$objPHPExcel->getActiveSheet()->getStyle("K".$linha)->getNumberFormat()->setFormatCode('#0.00');
		
			if($horas_prev<$array_ana_horas_real[$projeto][$disciplinas][$categoria])
			{
				$objPHPExcel->getActiveSheet()->getStyle('K'.$linha)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
			}
			else
			{
				$objPHPExcel->getActiveSheet()->getStyle('K'.$linha)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_BLACK);
			}			
		}
		
		$linha++;
		
		//SUBTOTAL
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $linha, "SUB-TOTAL");
		$objPHPExcel->getActiveSheet()->mergeCells("A".$linha.":H".$linha);
		$objPHPExcel->getActiveSheet()->getStyle("A".($linha).":"."H".($linha))->getFont()->setBold(TRUE)->setSize(12);
		$objPHPExcel->getActiveSheet()->getStyle("A".($linha).":"."H".($linha))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
		
		//PREVISTA
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8, $linha, "=SUM(I".$linha_tmp1.":I".($linha-1).")");
		$objPHPExcel->getActiveSheet()->getStyle("I".($linha).":"."I".($linha))->getFont()->setBold(FALSE)->setSize(10);
		$objPHPExcel->getActiveSheet()->getStyle("I".($linha).":"."I".($linha))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objPHPExcel->getActiveSheet()->getStyle("I".$linha)->getNumberFormat()->setFormatCode('#0.00');
		
		//REAL
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(9, $linha, "=SUM(J".$linha_tmp1.":J".($linha-1).")");
		$objPHPExcel->getActiveSheet()->getStyle("J".($linha).":"."J".($linha))->getFont()->setBold(FALSE)->setSize(10);
		$objPHPExcel->getActiveSheet()->getStyle("J".($linha).":"."J".($linha))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objPHPExcel->getActiveSheet()->getStyle("J".$linha)->getNumberFormat()->setFormatCode('#0.00');

		//SALDO
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(10, $linha, "=I".$linha."-J".$linha);
		$objPHPExcel->getActiveSheet()->getStyle("K".($linha).":"."K".($linha))->getFont()->setBold(FALSE)->setSize(10);
		$objPHPExcel->getActiveSheet()->getStyle("K".($linha).":"."K".($linha))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objPHPExcel->getActiveSheet()->getStyle("K".$linha)->getNumberFormat()->setFormatCode('#0.00');

		$linha+=1;
		
		$i++;	
	}
	
	//TOTAL
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $linha, "TOTAL PROJETO");
	$objPHPExcel->getActiveSheet()->mergeCells("A".$linha.":H".$linha);
	$objPHPExcel->getActiveSheet()->getStyle("A".($linha).":"."H".($linha))->getFont()->setBold(TRUE)->setSize(12);
	$objPHPExcel->getActiveSheet()->getStyle("A".($linha).":"."H".($linha))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
	
	//PREVISTA
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8, $linha, $total_prev);
	$objPHPExcel->getActiveSheet()->getStyle("I".($linha).":"."I".($linha))->getFont()->setBold(FALSE)->setSize(10);
	$objPHPExcel->getActiveSheet()->getStyle("I".($linha).":"."I".($linha))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	$objPHPExcel->getActiveSheet()->getStyle("I".$linha)->getNumberFormat()->setFormatCode('#0.00');
	
	//REAL
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(9, $linha, $total_real);
	$objPHPExcel->getActiveSheet()->getStyle("J".($linha).":"."J".($linha))->getFont()->setBold(FALSE)->setSize(10);
	$objPHPExcel->getActiveSheet()->getStyle("J".($linha).":"."J".($linha))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	$objPHPExcel->getActiveSheet()->getStyle("J".$linha)->getNumberFormat()->setFormatCode('#0.00');

	//SALDO
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(10, $linha, "=I".$linha."-J".$linha);
	$objPHPExcel->getActiveSheet()->getStyle("K".($linha).":"."K".($linha))->getFont()->setBold(FALSE)->setSize(10);
	$objPHPExcel->getActiveSheet()->getStyle("K".($linha).":"."K".($linha))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	$objPHPExcel->getActiveSheet()->getStyle("K".$linha)->getNumberFormat()->setFormatCode('#0.00');


	$objPHPExcel->getActiveSheet()->mergeCells("A".($linha+1).":K".($linha+1));
	$objPHPExcel->getActiveSheet()->getStyle("A".($linha+1).":K".($linha+1))->getFill()->applyFromArray(
	array(
		'type'       => PHPExcel_Style_Fill::FILL_SOLID,
		'startcolor' => array('rgb' => '0099FF'),));
		
	$linha+=3;

}

$linha++;

$objPHPExcel->setActiveSheetIndex(0);

$objWriter->save('php://output');

exit;
?>