<?php
/*
	Relatório de Custo Previsto Protheus
	Criado por Carlos Abreu  
	
	Versão 0 --> VERSÃO INICIAL : 25/06/2015
	Versão 1 --> Inclusão dos campos reg_del nas consultas - 17/11/2017 - Carlos Abreu
*/

ini_set('max_execution_time', 0); // No time limit
ini_set('post_max_size', '20M');
ini_set('upload_max_filesize', '20M');
ini_set('memory_limit', '1024M');
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

$total_proj = 0;

$total_custo = 0;

$total_real = 0;

$array_ana_cust_prev = NULL;	
$array_ana_cust_real = NULL;

$array_cat_cust_prev = NULL;	
$array_cat_cust_real = NULL;

$array_cust_prev = NULL;
$array_cust_real = NULL;

$array_ana_cust_descr = NULL;
$array_ana_cust_desp = NULL;

$array_cust_real_despesas = NULL;

$array_cust_real_subcontratos = NULL;

$array_ana_cust_sub_descr = NULL;
$array_ana_cust_sub_desp = NULL;

$array_tarefas = NULL;

$array_conta = NULL;

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

if($_POST["escolhafase"]!=-1)
{
	$filtro0 .= "AND AF8_FASE = '".$_POST["escolhafase"]."' ";
}

//TABELA AE5 - GRUPOS DE COMPOSIÇÃO
$sql = "SELECT AE5_GRPCOM, AE5_DESCRI FROM AE5010 WITH(NOLOCK) ";
$sql .= "WHERE AE5010.D_E_L_E_T_ = '' ";

$db->select($sql,'MSSQL', true);

if($db->erro!='')
{
	die($db->erro);
}

foreach($db->array_select as $regs0)
{
	$array_grpcom[trim($regs0["AE5_GRPCOM"])] = trim($regs0["AE5_DESCRI"]);
}

$sql = "SELECT codigo, conta_contabil FROM ".DATABASE.".atividades ";
$sql .= "WHERE atividades.obsoleto = 0 "; //não obsoletos
$sql .= "AND atividades.reg_del = 0 ";
$sql .= "AND atividades.cod IN (29,18) "; //despesas/suprimentos

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}

foreach($db->array_select as $regs1)
{
	$array_cod_desp[$regs1["codigo"]] = $regs1["conta_contabil"];
}

//TABELA PA7 - COORDENADORES
$sql = "SELECT PA7_ID, PA7_NOME FROM PA7010 WITH(NOLOCK) ";
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

$db->select($sql,'MSSQL',true);

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
		$nome_projeto = $regs1["AF8_PROJET"]." - ".trim($regs1["AF8_DESCRI"]);
	}
	
	if($nome_cliente=="")
	{
		$nome_cliente = trim($regs1["A1_NOME"]);
	}
	
	//analitico
	$array_ana_proj[$regs1["AF8_PROJET"]] = str_replace(array("'", '"', '\"', "\'"),"",trim($regs1["AF8_DESCRI"]));
	$array_ana_coord[$regs1["AF8_PROJET"]]= str_replace(array("'", '"', '\"', "\'"),"",$array_coord[$regs1["AF1_COORD1"]] . " - " .$array_coord[$regs1["AF1_COORD2"]]);
	$array_ana_client[$regs1["AF8_PROJET"]] = str_replace(array("'", '"', '\"', "\'"),"",trim($regs1["A1_NOME"]));
	$array_ana_coord_client[$regs1["AF8_PROJET"]] = $regs_os["nome_contato"];
	
	
	//TABELA AF2 - TAREFAS ORCAMENTO - CUSTO PREVISTO	
	$sql = "SELECT * FROM AF2010 WITH(NOLOCK) ";
	$sql .= "WHERE AF2010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF2_ORCAME = '".$regs1["AF1_ORCAME"]."' ";
	$sql .= "AND AF2_CODIGO <> '' ";
	$sql .= "ORDER BY AF2_GRPCOM ";

	$db->select($sql,'MSSQL',true);

	if($db->erro!='')
	{
		die($db->erro);
	}
	
	$array_tar = $db->array_select;

	foreach($array_tar as $regs2)
	{
		if(trim($regs2["AF2_GRPCOM"])!='DES' && !in_array(trim($regs2["AF2_COMPOS"]),array('SUP12','SUP13','SUP14','SUP15','SUP16','SUP17')))
		{
			//analitico
			$array_ana_cust_prev[$regs2["AF2_ORCAME"]][trim($regs2["AF2_GRPCOM"])] += $regs2["AF2_CUSTO"];
			
			//gerencial
			$array_cust_prev[trim($regs2["AF2_GRPCOM"])] += $regs2["AF2_CUSTO"];
			
		}
		else
		{
			if(trim($regs2["AF2_GRPCOM"])=='DES')
			{
				//TABELA AF4 - DESPESAS - CUSTO
				$sql = "SELECT * FROM AF4010 WITH(NOLOCK) ";
				$sql .= "WHERE AF4010.D_E_L_E_T_ = '' ";
				$sql .= "AND AF4_ORCAME = '".$regs2["AF2_ORCAME"]."' ";
				$sql .= "AND AF4_TAREFA = '".$regs2["AF2_TAREFA"]."' ";

				$db->select($sql,'MSSQL',true);

				if($db->erro!='')
				{
					die($db->erro);
				}
			
				foreach($db->array_select as $regs5)
				{
					//analitico - ALTERADO - CUSTO PREVISTO
					$array_ana_cust_descr[$regs2["AF2_ORCAME"]][trim($regs5["AF4_DESCRI"])] = trim($regs5["AF4_DESCRI"]);
					$array_ana_cust_desp[$regs2["AF2_ORCAME"]][trim($regs5["AF4_DESCRI"])] += $regs5["AF4_VALOR"];
					
					//gerencial - ALTERADO - CUSTO PREVISTO
					$array_cust_descr[trim($regs5["AF4_DESCRI"])] = trim($regs5["AF4_DESCRI"]);
					$array_cust_desp[trim($regs5["AF4_DESCRI"])] += $regs5["AF4_VALOR"];
					
					//armazena as tarefas para buscar o custo real
					$array_tarefas[trim($regs2["AF2_COMPOS"])][trim($regs5["AF4_DESCRI"])] = trim($regs2["AF2_TAREFA"]);
					
					$array_conta[$array_cod_desp[trim($regs2["AF2_COMPOS"])]] = trim($regs5["AF4_DESCRI"]);
				}
			}
			else
			{
				//TABELA AF3 - SUB-CONTRATADOS - CUSTO -- METODO NOVO - TABELA PRODUTOS
				$sql = "SELECT * FROM AF3010 WITH(NOLOCK), SB1010 WITH(NOLOCK) ";
				$sql .= "WHERE AF3010.D_E_L_E_T_ = '' ";
				$sql .= "AND SB1010.D_E_L_E_T_ = '' ";
				$sql .= "AND AF3_ORCAME = '".$regs2["AF2_ORCAME"]."' ";
				$sql .= "AND AF3_TAREFA = '".$regs2["AF2_TAREFA"]."' ";
				$sql .= "AND AF3_PRODUT = B1_COD ";

				$db->select($sql,'MSSQL',true);

				if($db->erro!='')
				{
					die($db->erro);
				}
				
				$array_sub = $db->array_select;
							
				foreach($array_sub as $regs6)
				{
					//analitico - CUSTO PREVISTO
					$array_ana_cust_sub_descr[$regs2["AF2_ORCAME"]][trim($regs6["B1_DESC"])] = trim($regs6["B1_DESC"]);
					$array_ana_cust_sub_desp[$regs2["AF2_ORCAME"]][trim($regs6["B1_DESC"])] += $regs6["AF3_CUSTD"];

					//gerencial - CUSTO PREVISTO
					$array_cust_sub_descr[trim($regs6["B1_DESC"])] = trim($regs6["B1_DESC"]);
					$array_cust_sub_desp[trim($regs6["B1_DESC"])] += $regs6["AF3_CUSTD"];
					$array_cust_sub_tarefa[trim($regs6["B1_DESC"])] = trim($regs2["AF2_TAREFA"]);
					
					//armazena as tarefas para buscar o custo real
					//$array_tarefas[trim($regs2["AF3_COMPOS"])][trim($regs6["B1_DESC"])] = trim($regs6["AF3_TAREFA"]);
					//PEGA O VALOR PELA NF DE ENTRADA
					$sql = "SELECT SUM(D1_TOTAL) AS TOTAL FROM SD1010 WITH(NOLOCK) ";
					$sql .= "WHERE SD1010.D_E_L_E_T_ = '' ";
					$sql .= "AND D1_COD = '".$regs6["B1_COD"]."' ";
					$sql .= "AND SUBSTRING (D1_CLVL,9,10) = '".$regs2["AF2_ORCAME"]."' ";					
					
					$db->select($sql,'MSSQL',true);

					if($db->erro!='')
					{
						die($db->erro);
					}
					
					$regs7 = $db->array_select[0];
					
					//$array_cust_real_subcontratos[trim($regs2["AF2_TAREFA"])] += ($regs7["TOTAL"]*(0.975));
					$array_cust_real_subcontratos[trim($regs2["AF2_TAREFA"])] += $regs7["TOTAL"];												
					
				}									
			}
		}		
	}	
	
	//TAREFAS ADICIONADAS APÓS FASE DE PROJETOS = CUSTO PREV = 0
	//$sql = "SELECT * FROM AE8010, AF8010, AF9010, AFA010 ";
	$sql = "SELECT * FROM AF8010 WITH(NOLOCK), AF9010 WITH(NOLOCK) ";
	$sql .= "WHERE AF9010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF8010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF8_PROJET = AF9_PROJET ";
	$sql .= "AND AF8_REVISA = AF9_REVISA ";
	$sql .= "AND AF8_ORCAME = '".$regs1["AF1_ORCAME"]."' ";
	$sql .= "AND AF9_CODIGO <> '' ";
	$sql .= "ORDER BY AF9_GRPCOM ";

	$db->select($sql,'MSSQL',true);

	if($db->erro!='')
	{
		die($db->erro);
	}
	
	$array_tar = $db->array_select;

	foreach($array_tar as $regs_2)
	{
		if(trim($regs_2["AF9_GRPCOM"])!='DES' && !in_array(trim($regs_2["AF2_COMPOS"]),array('SUP12','SUP13','SUP14','SUP15','SUP16','SUP17')))
		{
			//analitico
			$array_ana_cust_prev[$regs_2["AF9_PROJET"]][trim($regs_2["AF9_GRPCOM"])] += 0;
			
			//gerencial
			$array_cust_prev[trim($regs_2["AF9_GRPCOM"])] += 0;
			
		}
		else
		{
			if(trim($regs_2["AF9_GRPCOM"])=='DES')
			{
				//TABELA AF4 - DESPESAS - CUSTO
				$sql = "SELECT * FROM AFB010 WITH(NOLOCK) ";
				$sql .= "WHERE AFB010.D_E_L_E_T_ = '' ";
				$sql .= "AND AFB_PROJET = '".$regs_2["AF9_PROJETO"]."' ";
				$sql .= "AND AFB_TAREFA = '".$regs_2["AF9_TAREFA"]."' ";

				$db->select($sql,'MSSQL',true);

				if($db->erro!='')
				{
					die($db->erro);
				}
		
				foreach($db->array_select as $regs_5)
				{
					//analitico - ALTERADO
					$array_ana_cust_descr[$regs_2["AF9_PROJET"]][trim($regs_5["AFB_DESCRI"])] = trim($regs_5["AFB_DESCRI"]);
					$array_ana_cust_desp[$regs_2["AF9_PROJET"]][trim($regs_5["AFB_DESCRI"])] += $regs_5["AFB_VALOR"];
					
					//gerencial - ALTERADO
					$array_cust_descr[trim($regs_5["AFB_DESCRI"])] = trim($regs_5["AFB_DESCRI"]);
					$array_cust_desp[trim($regs_5["AFB_DESCRI"])] += $regs_5["AFB_VALOR"];
					
					//armazena as tarefas para buscar o custo real
					$array_tarefas[trim($regs_2["AF9_COMPOS"])][trim($regs_5["AFB_DESCRI"])] = trim($regs_2["AF9_TAREFA"]);
				}
			}
		}		
	}
	
	//CUSTO REAL			
	//HORAS REALIZADAS			
	$sql = "SELECT * FROM AE8010 WITH(NOLOCK), AJK010 WITH(NOLOCK), AF8010 WITH(NOLOCK), AF9010 WITH(NOLOCK) ";
	$sql .= "WHERE AJK010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF8010.D_E_L_E_T_ = '' ";
	$sql .= "AND AE8010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF9010.D_E_L_E_T_ = '' ";
	$sql .= "AND AJK_CTRRVS = '1' ";
	$sql .= "AND AF8_PROJET = AF9_PROJET ";
	$sql .= "AND AF8_REVISA = AF9_REVISA ";
	$sql .= "AND AJK_PROJET = AF8_PROJET ";
	$sql .= "AND AJK_REVISA = AF8_REVISA ";
	$sql .= "AND AJK_TAREFA = AF9_TAREFA ";
	$sql .= "AND AJK_RECURS = AE8_RECURS ";
	$sql .= "AND AF8_ORCAME = '".$regs1["AF1_ORCAME"]."' ";

	$db->select($sql,'MSSQL',true);
	
	if($db->erro!='')
	{
		die($db->erro);
	}
	
	$array_horas = $db->array_select;

	foreach($array_horas as $regs6)
	{		
		$recurs = explode("_",$regs6["AJK_RECURS"]);	
		
		//Obtem o valor do salario na data
		$sql = "SELECT * FROM ".DATABASE.".salarios ";
		$sql .= "WHERE salarios.id_funcionario = '" . intval($recurs[1]) . "' ";
		$sql .= "AND DATE_FORMAT(data , '%Y%m%d' ) <= '".$regs6["AJK_DATA"]."' ";
		$sql .= "AND salarios.reg_del = 0 ";
		$sql .= "ORDER BY id_salario DESC, data DESC LIMIT 1 ";

		$db->select($sql,'MYSQL',true);

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
				
		$regs4 = $db->array_select[0];
		
		//analitico
		$array_ana_cust_prev[$regs6["AF8_PROJET"]][trim($regs6["AF9_GRPCOM"])] += 0;
		
		//gerencial
		$array_cust_prev[trim($regs6["AF9_GRPCOM"])] += 0;
  
		switch ($regs4[" tipo_contrato"])
		{
			case 'SC':
			case 'SC+CLT':
			
				//analitico
				//$array_ana_cust_real[$regs6["AF8_PROJET"]][trim($regs6["AF9_GRPCOM"])] += round($regs4["salario_hora"]*$regs6["AJK_HQUANT"]*(0.975),2);
  				$array_ana_cust_real[$regs6["AF8_PROJET"]][trim($regs6["AF9_GRPCOM"])] += round($regs4["salario_hora"]*$regs6["AJK_HQUANT"],2);
  
				//gerencial
				//$array_cust_real[trim($regs6["AF9_GRPCOM"])] += round($regs4["salario_hora"]*$regs6["AJK_HQUANT"]*(0.975),2);
				$array_cust_real[trim($regs6["AF9_GRPCOM"])] += round($regs4["salario_hora"]*$regs6["AJK_HQUANT"],2);
				
			break;
			
			case 'CLT':
			case 'EST':
			
				//analitico
				$array_ana_cust_real[$regs6["AF8_PROJET"]][trim($regs6["AF9_GRPCOM"])] += round((($regs4["salario_clt"]/176)*1.84*$regs6["AJK_HQUANT"]),2);
  
				//gerencial
				$array_cust_real[trim($regs6["AF9_GRPCOM"])] += round((($regs4["salario_clt"]/176)*1.84*$regs6["AJK_HQUANT"]),2);
				
			break;
			
			case 'SC+MENS':
			case 'SC+CLT+MENS':
			
				//analitico
				//$array_ana_cust_real[$regs6["AF8_PROJET"]][trim($regs6["AF9_GRPCOM"])] += round((($regs4["salario_mensalista"]/176)*$regs6["AJK_HQUANT"])*(0.975),2);
  				$array_ana_cust_real[$regs6["AF8_PROJET"]][trim($regs6["AF9_GRPCOM"])] += round((($regs4["salario_mensalista"]/176)*$regs6["AJK_HQUANT"]),2);
  	
				//gerencial
				//$array_cust_real[trim($regs6["AF9_GRPCOM"])] += round((($regs4["salario_mensalista"]/176)*$regs6["AJK_HQUANT"]*(0.975)),2);
				$array_cust_real[trim($regs6["AF9_GRPCOM"])] += round((($regs4["salario_mensalista"]/176)*$regs6["AJK_HQUANT"]),2);
				
			break;
	   }		
	}	
	
	//INCLUIDO EM 24/10/2013
	//CATEGORIAS PREVISTO
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

	$db->select($sql,'MSSQL',true);
	
	if($db->erro!='')
	{
		die($db->erro);
	}
	
	$array_tar = $db->array_select;

	foreach($array_tar as $regs3)
	{
		$recurs = explode("_",$regs3["AF3_RECURS"]);
					
		//TABELA Categorias
		$sql = "SELECT * FROM ".DATABASE.".rh_categorias, ".DATABASE.".rh_cargos ";
		$sql .= "WHERE rh_cargos.id_categoria = rh_categorias.id_categoria ";
		$sql .= "AND rh_cargos.reg_del = 0 ";
		$sql .= "AND rh_categorias.reg_del = 0 ";
		
		//se recurso orc, obtem as horas do protheus
		if($recurs[0]=='ORC')
		{
			$sql .= "AND rh_cargos.id_cargo_grupo = '".$regs3["AE8_ID_CAR"]."' ";
		}
		else
		{
			$sql .= "AND rh_cargos.id_cargo_grupo = '".(int)$regs3["AE8_FUNCAO"]."' ";
		}

		$db->select($sql,'MYSQL',true);

		if($db->erro!='')
		{
			die($db->erro);
		}
		
		$regs4 = $db->array_select[0];
		
		if($regs4["categoria"]!="")
		{
			$array_cat_cust_prev[$array_grpcom[trim($regs3["AF2_GRPCOM"])]][$regs4["categoria"]] += ($regs3["AF3_CUSTD"]*$regs3["AF3_QUANT"]);
		}
		else
		{
			$array_cat_cust_prev[$array_grpcom[trim($regs3["AF2_GRPCOM"])]][$regs3["AF3_RECURS"]] += ($regs3["AF3_CUSTD"]*$regs3["AF3_QUANT"]);
		}
		
		ksort($array_cat_cust_prev[$array_grpcom[trim($regs3["AF2_GRPCOM"])]]);
	}
	
	//CUSTO REAL			
	//HORAS REALIZADAS
	$sql = "SELECT * FROM AE8010 WITH(NOLOCK), AJK010 WITH(NOLOCK), AF8010 WITH(NOLOCK), AF9010 WITH(NOLOCK) ";			
	$sql .= "WHERE AJK010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF8010.D_E_L_E_T_ = '' ";
	$sql .= "AND AE8010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF9010.D_E_L_E_T_ = '' ";
	$sql .= "AND AJK_CTRRVS = '1' ";
	$sql .= "AND AF8_PROJET = AF9_PROJET ";
	$sql .= "AND AF8_REVISA = AF9_REVISA ";
	$sql .= "AND AJK_PROJET = AF8_PROJET ";
	$sql .= "AND AJK_REVISA = AF8_REVISA ";
	$sql .= "AND AJK_TAREFA = AF9_TAREFA ";
	$sql .= "AND AJK_RECURS = AE8_RECURS ";
	$sql .= "AND AF8_ORCAME = '".$regs1["AF1_ORCAME"]."' ";

	$db->select($sql,'MSSQL',true);
	
	if($db->erro!='')
	{
		die($db->erro);
	}
	
	$array_horas = $db->array_select;

	foreach($array_horas as $regs6)
	{
		$recurs = explode("_",$regs6["AJK_RECURS"]);
		
		//TABELA Categorias
		$sql = "SELECT * FROM ".DATABASE.".rh_categorias, ".DATABASE.".rh_cargos ";
		$sql .= "WHERE rh_cargos.id_categoria = rh_categorias.id_categoria ";
		$sql .= "AND rh_cargos.reg_del = 0 ";
		$sql .= "AND rh_categorias.reg_del = 0 ";

		//se recurso orc, obtem as horas do protheus
		if($recurs[0]=='ORC')
		{
			$sql .= "AND rh_cargos.id_cargo_grupo = '".$regs6["AE8_ID_CAR"]."' ";
		}
		else
		{
			$sql .= "AND rh_cargos.id_cargo_grupo = '".intval($regs6["AE8_FUNCAO"])."' ";
		}

		$db->select($sql,'MYSQL',true);

		if($db->erro!='')
		{
			die($db->erro);
		}
		
		$regs3 = $db->array_select[0];	
		
		//Obtem o valor do salario na data
		$sql = "SELECT * FROM ".DATABASE.".salarios ";
		$sql .= "WHERE salarios.id_funcionario = '" . intval($recurs[1]) . "' ";
		$sql .= "AND DATE_FORMAT(data , '%Y%m%d' ) <= '".$regs6["AJK_DATA"]."' ";
		$sql .= "AND salarios.reg_del = 0 ";
		$sql .= "ORDER BY id_salario DESC, data DESC LIMIT 1 ";

		$db->select($sql,'MYSQL',true);

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
				
		$regs4 = $db->array_select[0];		

		$array_cat_cust_prev[$array_grpcom[trim($regs6["AF9_GRPCOM"])]][$regs3["categoria"]] += 0;
  
		switch ($regs4[" tipo_contrato"])
		{
			case 'SC':
			case 'SC+CLT':
			
				//$array_cat_cust_real[$array_grpcom[trim($regs6["AF9_GRPCOM"])]][$regs3["categoria"]] += round($regs4["salario_hora"]*$regs6["AJK_HQUANT"]*(0.975),2);
				$array_cat_cust_real[$array_grpcom[trim($regs6["AF9_GRPCOM"])]][$regs3["categoria"]] += round($regs4["salario_hora"]*$regs6["AJK_HQUANT"],2);
			break;
			
			case 'CLT':
			case 'EST':
			
				$array_cat_cust_real[$array_grpcom[trim($regs6["AF9_GRPCOM"])]][$regs3["categoria"]] += round((($regs4["salario_clt"]/176)*1.84*$regs6["AJK_HQUANT"]),2);
				
			break;
			
			case 'SC+MENS':
			case 'SC+CLT+MENS':
		
				//$array_cat_cust_real[$array_grpcom[trim($regs6["AF9_GRPCOM"])]][$regs3["categoria"]] += round((($regs4["salario_mensalista"]/176)*$regs6["AJK_HQUANT"]*(0.975)),2);
				$array_cat_cust_real[$array_grpcom[trim($regs6["AF9_GRPCOM"])]][$regs3["categoria"]] += round((($regs4["salario_mensalista"]/176)*$regs6["AJK_HQUANT"]),2);
			break;
	   }		
	}

	foreach($array_conta as $conta=>$custo_descr)
	{
		//percorre a tabela de CUSTOS CONTABIL (CT1 e CT2)
		$sql = "SELECT SUM(CT2_VALOR) AS VALOR FROM CT1010 WITH(NOLOCK), CT2010 WITH(NOLOCK) ";
		$sql .= "WHERE CT1010.D_E_L_E_T_ = '' ";
		$sql .= "AND CT2010.D_E_L_E_T_ = '' ";
		$sql .= "AND CT1_CONTA = '".$conta."' ";
		$sql .= "AND CT2_DEBITO = CT1_CONTA ";
		$sql .= "AND SUBSTRING (CT2_CLVLDB,9,10) =  '".$regs1["AF1_ORCAME"]."' ";				
		
		$db->select($sql,'MSSQL',true);
		
		if($db->erro!='')
		{
			die($db->erro);
		}
		
		$regs7 = $db->array_select[0];

		$array_cust_real_despesas[$custo_descr] += $regs7["VALOR"];		
	}
}

ksort($array_cust_prev);
ksort($array_cust_real);
ksort($array_cust_descr);
ksort($array_cust_sub_descr);
ksort($array_cat_cust_prev);

$objPHPExcel = PHPExcel_IOFactory::load("../modelos_excel/custo_prev_modelo.xls");

$locale = 'pt_br';

$validlocale = PHPExcel_Settings::setlocale($locale);

if (!$validlocale) 
{
	echo 'Unable to set locale to '.$locale." - reverting to en_us<br />\n";
}

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="custos_prev_"'.date('dmYHis').'".xlsx"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');


//1� folha
$objPHPExcel->setActiveSheetIndex(0);

$st = $objPHPExcel->getActiveSheet();

//acrescenta linhas conforme quantidade de projetos
//$objPHPExcel->getActiveSheet()->insertNewRowBefore(5,count($array_proj));

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

$linha=12;

//CUSTO PREVISTO DISCIPLINAS
foreach($array_cust_prev as $disciplinas=>$custo)
{
	$total_custo += $custo;
	
	$total_real += $array_cust_real[$disciplinas];
		
	$objPHPExcel->getActiveSheet()->insertNewRowBefore($linha,1);
	
	$objPHPExcel->getActiveSheet()->mergeCells("B".$linha.":E".$linha);	
			
	//item
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $linha, iconv('ISO-8859-1', 'UTF-8',$i));

	//disciplina
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $linha, iconv('ISO-8859-1', 'UTF-8',$array_grpcom[trim($disciplinas)]));

	//custo previsto
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $linha, $custo);

	$objPHPExcel->getActiveSheet()->getStyle("G".$linha)->getNumberFormat()->setFormatCode('R$ #,#00.00');
	
	//custo real
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, $linha, $array_cust_real[$disciplinas]);
	
	$objPHPExcel->getActiveSheet()->getStyle("H".$linha)->getNumberFormat()->setFormatCode('R$ #,#00.00');
	
	if($custo<$array_cust_real[$disciplinas])
	{
		$objPHPExcel->getActiveSheet()->getStyle('H'.$linha)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
	}
	else
	{
		if($custo==$array_cust_real[$disciplinas])
		{
			$objPHPExcel->getActiveSheet()->getStyle('H'.$linha)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_DARKYELLOW);
		}
		else
		{
			$objPHPExcel->getActiveSheet()->getStyle('H'.$linha)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_DARKGREEN);
		}
	}
	
	//%UTILIZA��O
	$objPHPExcel->getActiveSheet()->setCellValue('I'.$linha, '=H'.$linha.'/G'.$linha);
	
	$objPHPExcel->getActiveSheet()->getStyle("I".$linha)->getNumberFormat()->setFormatCode('0.00%');
	
	$linha+=1;
	
	$i++;
}

//SE TEVE CUSTO, SUMARIZA
if($linha>12)
{	
	//sub total disciplinas
	$objPHPExcel->getActiveSheet()->setCellValue('G'.$linha, '=SUM(G12:G'.($linha-1).')');
	
	$objPHPExcel->getActiveSheet()->setCellValue('H'.$linha, '=SUM(H12:H'.($linha-1).')');
	
	if($total_custo<$total_real)
	{
		$objPHPExcel->getActiveSheet()->getStyle('H'.$linha)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
	}
	else
	{
		$objPHPExcel->getActiveSheet()->getStyle('H'.$linha)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_BLACK);
	}
	
	$objPHPExcel->getActiveSheet()->setCellValue('I'.$linha, '=H'.$linha.'/G'.$linha);
}

$linha+=4;

$linha_tmp = $linha;

$i = 1;

//DESPESAS
foreach($array_cust_descr as $item=>$descricao)
{	
	$objPHPExcel->getActiveSheet()->insertNewRowBefore($linha,1);
	
	$objPHPExcel->getActiveSheet()->mergeCells("B".$linha.":F".$linha);	
			
	//item
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $linha, iconv('ISO-8859-1', 'UTF-8',$i));

	//descricao
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $linha, iconv('ISO-8859-1', 'UTF-8',$descricao));


	//custo previsto
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $linha, $array_cust_desp[$item]);

	$objPHPExcel->getActiveSheet()->getStyle("G".$linha)->getNumberFormat()->setFormatCode('R$ #,#00.00');
	
	//custo real
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, $linha, $array_cust_real_despesas[$item]);

	$objPHPExcel->getActiveSheet()->getStyle("H".$linha)->getNumberFormat()->setFormatCode('R$ #,#00.00');
	
	if($array_cust_desp[$item]<$array_cust_real_despesas[$item])
	{
		$objPHPExcel->getActiveSheet()->getStyle('H'.$linha)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
	}
	else
	{
		if($array_cust_desp[$item]==$array_cust_real_despesas[$item])
		{
			$objPHPExcel->getActiveSheet()->getStyle('H'.$linha)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_DARKYELLOW);
		}
		else
		{
			$objPHPExcel->getActiveSheet()->getStyle('H'.$linha)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_DARKGREEN);
		}
	}
	
	//%UTILIZA��O
	$objPHPExcel->getActiveSheet()->setCellValue('I'.$linha, '=H'.$linha.'/G'.$linha);
	
	$objPHPExcel->getActiveSheet()->getStyle("I".$linha)->getNumberFormat()->setFormatCode('0.00%');
	
	$linha+=1;
	
	$i++;
}

//SE TEVE DESPESA, SUMARIZA
if($linha>$linha_tmp)
{
	//sub total despesas
	$objPHPExcel->getActiveSheet()->setCellValue('G'.$linha, '=SUM(G'.$linha_tmp.':G'.($linha-1).')');
	
	$objPHPExcel->getActiveSheet()->setCellValue('H'.$linha, '=SUM(H'.$linha_tmp.':H'.($linha-1).')');
	
	$objPHPExcel->getActiveSheet()->setCellValue('I'.$linha, '=H'.$linha.'/G'.$linha);
}

$linha+=4;

$linha_tmp = $linha;

$i = 1;

//SUBCONTRATADOS
foreach($array_cust_sub_descr as $item=>$descricao)
{	
	$objPHPExcel->getActiveSheet()->insertNewRowBefore($linha,1);
	
	$objPHPExcel->getActiveSheet()->mergeCells("B".$linha.":F".$linha);	
			
	//item
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $linha, iconv('ISO-8859-1', 'UTF-8',$i));

	//descricao
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $linha, iconv('ISO-8859-1', 'UTF-8',$descricao));

	//custo previsto
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $linha, $array_cust_sub_desp[$item]);

	$objPHPExcel->getActiveSheet()->getStyle("G".$linha)->getNumberFormat()->setFormatCode('R$ #,#00.00');
	
	//custo real
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, $linha,$array_cust_real_subcontratos[$array_cust_sub_tarefa[$item]]);

	$objPHPExcel->getActiveSheet()->getStyle("H".$linha)->getNumberFormat()->setFormatCode('R$ #,#00.00');

	if($array_cust_sub_desp[$item]<$array_cust_real_subcontratos[$array_cust_sub_tarefa[$item]])
	{
		$objPHPExcel->getActiveSheet()->getStyle('H'.$linha)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
	}
	else
	{
		if($array_cust_sub_desp[$item]==$array_cust_real_subcontratos[$array_cust_sub_tarefa[$item]])
		{
			$objPHPExcel->getActiveSheet()->getStyle('H'.$linha)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_DARKYELLOW);
		}
		else
		{
			$objPHPExcel->getActiveSheet()->getStyle('H'.$linha)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_DARKGREEN);
		}
	}	
	
	//%UTILIZA��O
	$objPHPExcel->getActiveSheet()->setCellValue('I'.$linha, '=H'.$linha.'/G'.$linha);

	$objPHPExcel->getActiveSheet()->getStyle("I".$linha)->getNumberFormat()->setFormatCode('0.00%');

	$linha+=1;
	
	$i++;
}

//SE TEVE SUB-CONTRATADO, SUMARIZA
if($linha>$linha_tmp)
{
	//sub total despesas
	$objPHPExcel->getActiveSheet()->setCellValue('G'.$linha, '=SUM(G'.$linha_tmp.':G'.($linha-1).')');
	
	$objPHPExcel->getActiveSheet()->setCellValue('H'.$linha, '=SUM(H'.$linha_tmp.':H'.($linha-1).')');
	
	$objPHPExcel->getActiveSheet()->setCellValue('I'.$linha, '=H'.$linha.'/G'.$linha);
}


//2� folha - analitico
$objPHPExcel->setActiveSheetIndex(1);

$linha = 1;

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
	$objPHPExcel->getActiveSheet()->getStyle("A".($linha+5))->getFont()->setBold(true)->setSize(14);
	$objPHPExcel->getActiveSheet()->mergeCells("A".($linha+5).":D".($linha+5));
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $linha+5, iconv('ISO-8859-1', 'UTF-8','Recursos Or�ados:'));
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $linha+6, "Item");
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $linha+6, iconv('ISO-8859-1', 'UTF-8','Nome do Recurso/ Categoria'));
	$objPHPExcel->getActiveSheet()->mergeCells("D".($linha+6).":E".($linha+6));
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $linha+6, "Custo Previsto");
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, $linha+6, "Custo Real");
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8, $linha+6, iconv('ISO-8859-1', 'UTF-8','% Utiliz.'));

	$i = 1;
	
	$linha+=7;
	
	$linha_tmp = $linha;
	
	foreach($array_ana_cust_prev[$projeto] as $disciplinas=>$custo)
	{
			
		$objPHPExcel->getActiveSheet()->insertNewRowBefore($linha,1);
		
		$objPHPExcel->getActiveSheet()->mergeCells("B".$linha.":E".$linha);	
				
		//item
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $linha, $i);
	
		//disciplina
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $linha, iconv('ISO-8859-1', 'UTF-8',$array_grpcom[trim($disciplinas)]));
	
		//custo previsto
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $linha, $custo);
	
		$objPHPExcel->getActiveSheet()->getStyle("G".$linha)->getNumberFormat()->setFormatCode('R$ #,#00.00');
		
		//custo real
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, $linha, $array_ana_cust_real[$projeto][$disciplinas]);
		
		$objPHPExcel->getActiveSheet()->getStyle("H".$linha)->getNumberFormat()->setFormatCode('R$ #,#00.00');
		
		if($custo<$array_ana_cust_real[$projeto][$disciplinas])
		{
			$objPHPExcel->getActiveSheet()->getStyle('H'.$linha)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
		}
		else
		{
			if($custo==$array_ana_cust_real[$projeto][$disciplinas])
			{
				$objPHPExcel->getActiveSheet()->getStyle('H'.$linha)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_DARKYELLOW);
			}
			else
			{
				$objPHPExcel->getActiveSheet()->getStyle('H'.$linha)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_DARKGREEN);
			}
		}
		
		//%UTILIZA��O
		$objPHPExcel->getActiveSheet()->setCellValue('I'.$linha, '=H'.$linha.'/G'.$linha);
		
		$objPHPExcel->getActiveSheet()->getStyle("I".$linha)->getNumberFormat()->setFormatCode('0.00%');
		
		$linha+=1;
		
		$i++;
	}
	
	$linha += 1;
		
	//SE TEVE CUSTO, SUMARIZA	
	if($linha>$linha_tmp)
	{
		$objPHPExcel->getActiveSheet()->mergeCells("A".($linha).":E".($linha));
		$objPHPExcel->getActiveSheet()->getStyle("A".$linha)->getFont()->setBold(true)->setSize(12);
		$objPHPExcel->getActiveSheet()->getStyle("A".($linha).":E".($linha))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objPHPExcel->getActiveSheet()->setCellValue('A'.$linha, iconv('ISO-8859-1', 'UTF-8','Sub-Total'));

		$objPHPExcel->getActiveSheet()->getStyle("G".$linha)->getNumberFormat()->setFormatCode('R$ #,#00.00');
		$objPHPExcel->getActiveSheet()->getStyle("G".$linha)->getFont()->setBold(true)->setSize(11);
		$objPHPExcel->getActiveSheet()->getStyle("H".$linha)->getNumberFormat()->setFormatCode('R$ #,#00.00');
		$objPHPExcel->getActiveSheet()->getStyle("H".$linha)->getFont()->setBold(true)->setSize(11);
		$objPHPExcel->getActiveSheet()->getStyle("I".$linha)->getNumberFormat()->setFormatCode('0.00%');
		$objPHPExcel->getActiveSheet()->getStyle("I".$linha)->getFont()->setBold(true)->setSize(11);
	
		//sub total disciplinas
		$objPHPExcel->getActiveSheet()->setCellValue('G'.$linha, '=SUM(G'.$linha_tmp.':G'.($linha-1).')');
		
		$objPHPExcel->getActiveSheet()->setCellValue('H'.$linha, '=SUM(H'.$linha_tmp.':H'.($linha-1).')');
		
		if($total_custo<$total_real)
		{
			$objPHPExcel->getActiveSheet()->getStyle('H'.$linha)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
		}
		else
		{
			$objPHPExcel->getActiveSheet()->getStyle('H'.$linha)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_BLACK);
		}
		
		$objPHPExcel->getActiveSheet()->setCellValue('I'.$linha, '=H'.$linha.'/G'.$linha);
	
		$linha_custo = $linha;		
	}	
	
	$linha += 2;
	
	//OUTRAS DESPESAS
	$objPHPExcel->getActiveSheet()->getStyle("A".($linha))->getFont()->setBold(true)->setSize(14);
	$objPHPExcel->getActiveSheet()->mergeCells("A".($linha).":D".($linha));
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $linha, iconv('ISO-8859-1', 'UTF-8','Outras Despesas'));
	
	$objPHPExcel->getActiveSheet()->getStyle("A".($linha+1))->getFont()->setBold(false)->setSize(11);
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $linha+1, "Item");
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $linha+1, iconv('ISO-8859-1', 'UTF-8','Nome do Recurso/Categoria'));
	$objPHPExcel->getActiveSheet()->mergeCells("D".($linha+1).":E".($linha+1));
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $linha+1, "Custo Previsto");
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, $linha+1, "Custo Real");
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8, $linha+1, iconv('ISO-8859-1', 'UTF-8','% Utiliz.'));

	$linha += 2;

	$linha_tmp = $linha;
		
	$i = 1;
	
	foreach($array_ana_cust_descr[$projeto] as $item=>$descricao)
	{	
		$objPHPExcel->getActiveSheet()->insertNewRowBefore($linha,1);
		
		$objPHPExcel->getActiveSheet()->mergeCells("B".$linha.":F".$linha);	
				
		//item
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $linha, $i);
	
		//descricao
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $linha, iconv('ISO-8859-1', 'UTF-8',$descricao));
	
		//custo previsto
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $linha, $array_ana_cust_desp[$projeto][$item]);
	
		$objPHPExcel->getActiveSheet()->getStyle("G".$linha)->getNumberFormat()->setFormatCode('R$ #,#00.00');
	
		//custo real(REBATE DO PREVISTO)
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, $linha, $array_cust_real_despesas[$item]); // $array_cust_real_despesas[$item]
	
		//$objPHPExcel->getActiveSheet()->getStyle("H".$linha)->getNumberFormat()->setFormatCode('R$ #,#00.00');
		
		//%UTILIZA��O
		$objPHPExcel->getActiveSheet()->setCellValue('I'.$linha, '=H'.$linha.'/G'.$linha);
		
		$objPHPExcel->getActiveSheet()->getStyle("I".$linha)->getNumberFormat()->setFormatCode('0.00%');
		
		$linha+=1;
		
		$i++;
	}
	
	$linha += 1;	
	
	//SE TEVE DESPESA, SUMARIZA
	if($linha>$linha_tmp)
	{
		$objPHPExcel->getActiveSheet()->mergeCells("A".($linha).":F".($linha));
		$objPHPExcel->getActiveSheet()->getStyle("A".$linha)->getFont()->setBold(true)->setSize(12);
		$objPHPExcel->getActiveSheet()->getStyle("A".($linha).":F".($linha))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objPHPExcel->getActiveSheet()->setCellValue('A'.$linha, iconv('ISO-8859-1', 'UTF-8','Sub-Total'));

		$objPHPExcel->getActiveSheet()->getStyle("G".$linha)->getNumberFormat()->setFormatCode('R$ #,#00.00');
		$objPHPExcel->getActiveSheet()->getStyle("G".$linha)->getFont()->setBold(true)->setSize(11);
		$objPHPExcel->getActiveSheet()->getStyle("H".$linha)->getNumberFormat()->setFormatCode('R$ #,#00.00');
		$objPHPExcel->getActiveSheet()->getStyle("H".$linha)->getFont()->setBold(true)->setSize(11);
		$objPHPExcel->getActiveSheet()->getStyle("I".$linha)->getNumberFormat()->setFormatCode('0.00%');
		$objPHPExcel->getActiveSheet()->getStyle("I".$linha)->getFont()->setBold(true)->setSize(11);
		
		//sub total despesas
		$objPHPExcel->getActiveSheet()->setCellValue('G'.$linha, '=SUM(G'.$linha_tmp.':G'.($linha-1).')');
		
		$objPHPExcel->getActiveSheet()->setCellValue('H'.$linha, '=SUM(H'.$linha_tmp.':H'.($linha-1).')');
		
		$objPHPExcel->getActiveSheet()->setCellValue('I'.$linha, '=H'.$linha.'/G'.$linha);
	
		$linha_despesa = $linha;
	}
	
	$linha+=2;
	
	//SUBCONTRATADOS
	$objPHPExcel->getActiveSheet()->getStyle("A".($linha))->getFont()->setBold(true)->setSize(14);
	$objPHPExcel->getActiveSheet()->mergeCells("A".($linha).":D".($linha));
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $linha, iconv('ISO-8859-1', 'UTF-8','Sub-contrata��o'));
	
	$objPHPExcel->getActiveSheet()->getStyle("A".($linha+1))->getFont()->setBold(false)->setSize(11);
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $linha+1, "Item");
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $linha+1, iconv('ISO-8859-1', 'UTF-8','Nome do Recurso/Categoria'));
	$objPHPExcel->getActiveSheet()->mergeCells("D".($linha+1).":F".($linha+1));
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $linha+1, "Custo Previsto");
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, $linha+1, "Custo Real");
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8, $linha+1, iconv('ISO-8859-1', 'UTF-8','% Utiliz.'));
	
	$linha+=2;
	
	$linha_tmp = $linha;
	
	$i = 1;
	
	foreach($array_ana_cust_sub_descr[$projeto] as $item=>$descricao)
	{	
		$objPHPExcel->getActiveSheet()->insertNewRowBefore($linha,1);
		
		$objPHPExcel->getActiveSheet()->mergeCells("B".$linha.":F".$linha);	
				
		//item
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $linha, $i);
	
		//descricao
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $linha, iconv('ISO-8859-1', 'UTF-8',$descricao));
	
		//custo previsto
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $linha, $array_ana_cust_sub_desp[$projeto][$item]);
	
		$objPHPExcel->getActiveSheet()->getStyle("G".$linha)->getNumberFormat()->setFormatCode('R$ #,#00.00');
	
		//custo real(REBATE DO PREVISTO)
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, $linha, $array_cust_real_subcontratos[$array_cust_sub_tarefa[$item]]);
	
		$objPHPExcel->getActiveSheet()->getStyle("H".$linha)->getNumberFormat()->setFormatCode('R$ #,#00.00');
		
		//%UTILIZA��O
		$objPHPExcel->getActiveSheet()->setCellValue('I'.$linha, '=H'.$linha.'/G'.$linha);
	
		$objPHPExcel->getActiveSheet()->getStyle("I".$linha)->getNumberFormat()->setFormatCode('0.00%');
	
		$linha+=1;
		
		$i++;
	}
	
	$linha += 1;
	
	//SE TEVE SUB-CONTRATADO, SUMARIZA
	if($linha>$linha_tmp)
	{
		$objPHPExcel->getActiveSheet()->mergeCells("A".($linha).":F".($linha));
		$objPHPExcel->getActiveSheet()->getStyle("A".$linha)->getFont()->setBold(true)->setSize(12);
		$objPHPExcel->getActiveSheet()->getStyle("A".($linha).":F".($linha))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objPHPExcel->getActiveSheet()->setCellValue('A'.$linha, iconv('ISO-8859-1', 'UTF-8','Sub-Total'));

		$objPHPExcel->getActiveSheet()->getStyle("G".$linha)->getNumberFormat()->setFormatCode('R$ #,#00.00');
		$objPHPExcel->getActiveSheet()->getStyle("G".$linha)->getFont()->setBold(true)->setSize(11);
		$objPHPExcel->getActiveSheet()->getStyle("H".$linha)->getNumberFormat()->setFormatCode('R$ #,#00.00');
		$objPHPExcel->getActiveSheet()->getStyle("H".$linha)->getFont()->setBold(true)->setSize(11);
		$objPHPExcel->getActiveSheet()->getStyle("I".$linha)->getNumberFormat()->setFormatCode('0.00%');
		$objPHPExcel->getActiveSheet()->getStyle("I".$linha)->getFont()->setBold(true)->setSize(11);
		
		//sub total despesas
		$objPHPExcel->getActiveSheet()->setCellValue('G'.$linha, '=SUM(G'.$linha_tmp.':G'.($linha-1).')');
		
		$objPHPExcel->getActiveSheet()->setCellValue('H'.$linha, '=SUM(H'.$linha_tmp.':H'.($linha-1).')');
		
		$objPHPExcel->getActiveSheet()->setCellValue('I'.$linha, '=H'.$linha.'/G'.$linha);
	
		$linha_sub_cont = $linha;
	}
	
	$linha+=2;
	
	//sumariza os totais
	//cabe�alho
	$objPHPExcel->getActiveSheet()->getStyle("A".($linha))->getFont()->setBold(true)->setSize(14);
	$objPHPExcel->getActiveSheet()->mergeCells("A".($linha).":B".($linha));
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $linha, iconv('ISO-8859-1', 'UTF-8','Resumo dos recursos'));
	
	$objPHPExcel->getActiveSheet()->getStyle("A".($linha+1))->getFont()->setBold(false)->setSize(11);
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $linha+1, "Item");
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $linha+1, iconv('ISO-8859-1', 'UTF-8','Nome do Recurso/Categoria'));
	$objPHPExcel->getActiveSheet()->mergeCells("B".($linha+1).":F".($linha+1));
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $linha+1, "Custo Previsto");
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, $linha+1, "Custo Real");
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8, $linha+1, iconv('ISO-8859-1', 'UTF-8','% Utiliz.'));

	$linha += 2;
	
	$linha_tmp = $linha;
	
	//MO
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $linha, "1");
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $linha, iconv('ISO-8859-1', 'UTF-8','Recursos Or�ados (M�o-de-Obra)'));
	$objPHPExcel->getActiveSheet()->mergeCells("B".($linha).":F".($linha));
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $linha, "=G".$linha_custo);
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, $linha, "=H".$linha_custo);
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8, $linha, '=H'.$linha.'/G'.$linha);
	$objPHPExcel->getActiveSheet()->getStyle("G".$linha)->getNumberFormat()->setFormatCode('R$ #,#00.00');
	$objPHPExcel->getActiveSheet()->getStyle("H".$linha)->getNumberFormat()->setFormatCode('R$ #,#00.00');
	$objPHPExcel->getActiveSheet()->getStyle("I".$linha)->getNumberFormat()->setFormatCode('0.00%');

	$linha += 1;
	
	//Outras despesas
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $linha, "2");
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $linha, iconv('ISO-8859-1', 'UTF-8','Outras Despesas'));
	$objPHPExcel->getActiveSheet()->mergeCells("B".($linha).":F".($linha));
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $linha, "=G".$linha_despesa);
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, $linha, "=H".$linha_despesa);
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8, $linha, '=H'.$linha.'/G'.$linha);
	$objPHPExcel->getActiveSheet()->getStyle("G".$linha)->getNumberFormat()->setFormatCode('R$ #,#00.00');
	$objPHPExcel->getActiveSheet()->getStyle("H".$linha)->getNumberFormat()->setFormatCode('R$ #,#00.00');
	$objPHPExcel->getActiveSheet()->getStyle("I".$linha)->getNumberFormat()->setFormatCode('0.00%');

	$linha += 1;
	
	//SUBCONTRATOS
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $linha, "3");
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $linha, iconv('ISO-8859-1', 'UTF-8','Sub-contrata��o'));
	$objPHPExcel->getActiveSheet()->mergeCells("B".($linha).":F".($linha));
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $linha, "=G".$linha_sub_cont);
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, $linha, "=H".$linha_sub_cont);
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8, $linha, '=H'.$linha.'/G'.$linha);
	$objPHPExcel->getActiveSheet()->getStyle("G".$linha)->getNumberFormat()->setFormatCode('R$ #,#00.00');
	$objPHPExcel->getActiveSheet()->getStyle("H".$linha)->getNumberFormat()->setFormatCode('R$ #,#00.00');
	$objPHPExcel->getActiveSheet()->getStyle("I".$linha)->getNumberFormat()->setFormatCode('0.00%');
	
	$linha += 1;
		
	//Total
	$objPHPExcel->getActiveSheet()->getStyle("A".($linha))->getFont()->setBold(true)->setSize(12);
	$objPHPExcel->getActiveSheet()->getStyle("A".($linha).":F".($linha))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	$objPHPExcel->getActiveSheet()->mergeCells("A".($linha).":F".($linha));
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $linha, iconv('ISO-8859-1', 'UTF-8','TOTAL PROJETO'));	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $linha, "=SUM(G".$linha_tmp.":G".($linha-1).")");
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, $linha, "=SUM(H".$linha_tmp.":H".($linha-1).")");
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8, $linha, '=H'.$linha.'/G'.$linha);
	$objPHPExcel->getActiveSheet()->getStyle("G".$linha)->getNumberFormat()->setFormatCode('R$ #,#00.00');
	$objPHPExcel->getActiveSheet()->getStyle("H".$linha)->getNumberFormat()->setFormatCode('R$ #,#00.00');
	$objPHPExcel->getActiveSheet()->getStyle("I".$linha)->getNumberFormat()->setFormatCode('0.00%');
	$objPHPExcel->getActiveSheet()->getStyle("G".($linha))->getFont()->setBold(true)->setSize(11);
	$objPHPExcel->getActiveSheet()->getStyle("H".($linha))->getFont()->setBold(true)->setSize(11);
	$objPHPExcel->getActiveSheet()->getStyle("I".($linha))->getFont()->setBold(true)->setSize(11);
	
	$objPHPExcel->getActiveSheet()->mergeCells("A".($linha+1).":J".($linha+1));
	$objPHPExcel->getActiveSheet()->getStyle("A".($linha+1).":J".($linha+1))->getFill()->applyFromArray(
	array(
        'type'       => PHPExcel_Style_Fill::FILL_SOLID,
        'startcolor' => array('rgb' => '0099FF'),));
	
	$linha += 3;

}

for ($col = 'A'; $col != 'J'; $col++) 
{
	$objPHPExcel->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);
}

//3� folha - CATEGORIAS
$objPHPExcel->setActiveSheetIndex(2);

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

$coluna = 6;

//CUSTO PREVISTO
foreach($array_cat_cust_prev as $disciplinas=>$categorias)
{	
	$objPHPExcel->getActiveSheet()->insertNewRowBefore($linha,1);
	
	//item
	$objPHPExcel->getActiveSheet()->getStyle('A'.$linha.":K".$linha)->getFont()->setBold(false)->setSize(10);
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $linha, iconv('ISO-8859-1', 'UTF-8',$i));

	//disciplina
	$objPHPExcel->getActiveSheet()->getStyle('B'.$linha.":B".$linha)->getFont()->setBold(true)->setSize(10);
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $linha, iconv('ISO-8859-1', 'UTF-8',$disciplinas));

	$objPHPExcel->getActiveSheet()->mergeCells(num2alfa(1).$linha.":".num2alfa(4).$linha);

	foreach($categorias as $categ=>$custo_prev)
	{
		$linha+=1;
		
		$objPHPExcel->getActiveSheet()->insertNewRowBefore($linha,1);
		
		$objPHPExcel->getActiveSheet()->mergeCells(num2alfa(1).$linha.":".num2alfa(4).$linha);
		
		//categoria
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, $linha, iconv('ISO-8859-1', 'UTF-8',$categ));
		
		//CUSTO previsto
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $linha, $custo_prev?$custo_prev:0);
	
		//custo real
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8, $linha, $array_cat_cust_real[$disciplinas][$categ]);
	
		if($custo_prev<$array_cat_cust_real[$disciplinas][$categ])
		{
			$objPHPExcel->getActiveSheet()->getStyle('I'.$linha)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
		}
		else
		{
			if($custo_prev==$array_cat_cust_real[$disciplinas][$categ])
			{
				$objPHPExcel->getActiveSheet()->getStyle('I'.$linha)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_DARKYELLOW);
			}
			else
			{
				$objPHPExcel->getActiveSheet()->getStyle('I'.$linha)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_DARKGREEN);
			}
		}	
	
		//%UTILIZA��O
		$objPHPExcel->getActiveSheet()->setCellValue('J'.$linha, '=I'.$linha.'/G'.$linha);
		
		$objPHPExcel->getActiveSheet()->getStyle("J".$linha)->getNumberFormat()->setFormatCode('0.00%');
		
	}
	
	$linha+=1;
	
	$i++;	
}

//Monta os sub-totais
for($j=6;$j<=8;$j++)
{
	if($j!=7)
	{
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($j, $linha, "=SUM(".num2alfa($j)."12:".num2alfa($j).($linha-1).")");		
	}
}

//%UTILIZA��O
$objPHPExcel->getActiveSheet()->setCellValue('J'.$linha, '=I'.$linha.'/G'.$linha);

$objPHPExcel->getActiveSheet()->getStyle("J".$linha)->getNumberFormat()->setFormatCode('0.00%');

$objPHPExcel->setActiveSheetIndex(0);

$objWriter->save('php://output');

exit;

?>
