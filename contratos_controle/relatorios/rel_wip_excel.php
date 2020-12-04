<?php
/*
	Relatório WIP
	Criado por Carlos Abreu  
	
	Versão 0 --> VERSÃO INICIAL : 01/07/2017
	Versão 1 --> Inclusão dos campos reg_del nas consultas - 17/11/2017 - Carlos Abreu
	Versão 2 --> Altera��es em campos - 02/03/2018 - Carlos Abreu
	Versão 3 --> Inclusão de campos conforme chamado #2691 - 06/04/2018 - Carlos Abreu
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

if(!verifica_sub_modulo(608))
{
	nao_permitido();
}

require_once(INCLUDE_DIR."PHPExcel/Classes/PHPExcel.php");

$db = new banco_dados();

$array_wip = NULL;

/*
$primeiro_dia_mes_ant = pri_dia_mes(calcula_data(date('d/m/Y'),'sub','month',1),true);

$ultimo_dia_mes_ant = ult_dia_mes(calcula_data(date('d/m/Y'),'sub','month',1),true);

$pri_data = mysql_protheus(php_mysql($primeiro_dia_mes_ant));

$ult_data = mysql_protheus(php_mysql($ultimo_dia_mes_ant));
*/

$mes = $_POST["mes"];

$ano = $_POST["ano"];

$array_meses = array("JANEIRO","FEVEREIRO","MAR�O","ABRIL","MAIO","JUNHO","JULHO","AGOSTO","SETEMBRO","OUTUBRO","NOVEMBRO","DEZEMBRO");

if (intval($mes)==1)
{
	$mes = 12;
	$ano = $ano - 1;
	$data_ini = "26/" . $mes . "/" . $ano;
}
else
{ 
	$mesant = $mes - 1;
	$data_ini = "26/" . $mesant . "/" . $ano;
}

$temp = explode("/",$data_ini);

$d = $temp[0];
$m = $temp[1];
$a = $temp[2];

$diasestampa = mktime(0,0,0,$m+1,0,$ano);

$diasarray = getdate($diasestampa);

$diasdomes = $diasarray["mday"];

// loop de dias
for($i=1;$i<=$diasdomes;$i++)
{	
	if($d==$diasdomes+1)
	{
		$d = 1;
		
		$m++;
		
		if($m==13)
		{
			$m=1;
			$a++;
		}
	}

	$data[$i]=$a."-". sprintf('%02d',$m) ."-".sprintf('%02d',$d);
	
	$d++;
}
// loop de dias

$pri_data = mysql_protheus($data[1]);

$ult_data = mysql_protheus($data[count($data)]);

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

$sql = "SELECT codigo, conta_contabil FROM ".DATABASE.".atividades ";
$sql .= "WHERE atividades.obsoleto = 0 "; //n�o obsoletos
$sql .= "AND atividades.reg_del = 0 ";
$sql .= "AND atividades.cod IN (29,18) "; //despesas/suprimentos
$sql .= "AND conta_contabil <> '' ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}

foreach($db->array_select as $regs1)
{
	$array_cod_desp[$regs1["codigo"]] = $regs1["conta_contabil"];
}

//seleciona as OS com apontamentos no periodo
$sql = "SELECT AJK_PROJET FROM AJK010 WITH(NOLOCK) ";
$sql .= "WHERE AJK010.D_E_L_E_T_ = '' ";
$sql .= "AND AJK_CTRRVS = '1' ";
$sql .= "AND AJK_DATA BETWEEN '".$pri_data."' AND '".$ult_data."' ";
$sql .= "GROUP BY AJK_PROJET ";

$db->select($sql,'MSSQL',true);

if($db->erro!='')
{
	die($db->erro);
}

$array_projetos = $db->array_select;

foreach($array_projetos as $regs)
{
	$a_projetos[] = $regs["AJK_PROJET"];
	
	$a_projetos_med[] = intval($regs["AJK_PROJET"]);
}

if(count($a_projetos)<=0)
{
	die("N�O EXISTEM PROJETOS NESTE PER�ODO");
}

//TABELA AF2 - TAREFAS ORCAMENTO - HORAS PREVISTAS
$sql = "SELECT AF8_ORCAME, SUM(AF3_QUANT) AS QUANT FROM AE8010 WITH(NOLOCK), AF8010 WITH(NOLOCK), AF2010 WITH(NOLOCK), AF3010 WITH(NOLOCK) ";
$sql .= "WHERE AF2010.D_E_L_E_T_ = '' ";
$sql .= "AND AE8010.D_E_L_E_T_ = '' ";
$sql .= "AND AF8010.D_E_L_E_T_ = '' ";
$sql .= "AND AF3010.D_E_L_E_T_ = '' ";
//$sql .= "AND AF8_FASE IN ('02','03','04','07','09','05','11','12') ";
$sql .= "AND AF8_PROJET IN (".implode(",",$a_projetos).") ";
$sql .= "AND AF3_RECURS = AE8_RECURS ";
$sql .= "AND AF3_TAREFA = AF2_TAREFA ";
$sql .= "AND AF3_ORCAME = AF2_ORCAME ";
$sql .= "AND AF2_ORCAME = AF8_ORCAME ";
$sql .= "AND AF2_CODIGO <> '' ";
$sql .= "AND AF2_GRPCOM <> 'DES' ";
//$sql .= "AND AF2_COMPOS NOT IN ('SUP12','SUP13','SUP14','SUP15','SUP16','SUP17') ";
$sql .= "GROUP BY AF8_ORCAME ";
$sql .= "ORDER BY AF8_ORCAME ";

$db->select($sql,'MSSQL', true);

if($db->erro!='')
{
	die($db->erro);
}

$array_horas = $db->array_select;

foreach($array_horas as $regs2)
{
	$array_wip['horasprev'][$regs2["AF8_ORCAME"]] = $regs2["QUANT"];	
}

//TABELA AF2 - TAREFAS ORCAMENTO - CUSTO PREVISTO	
$sql = "SELECT * FROM AF8010 WITH(NOLOCK), AF2010 WITH(NOLOCK) ";
$sql .= "WHERE AF2010.D_E_L_E_T_ = '' ";
$sql .= "AND AF8010.D_E_L_E_T_ = '' ";
//$sql .= "AND AF8_FASE IN ('02','03','04','07','09','05','11','12') ";
//$sql .= "AND AF8_PROJET >= 0000003000 ";
$sql .= "AND AF8_PROJET IN (".implode(",",$a_projetos).") ";
$sql .= "AND AF2_ORCAME = AF8_ORCAME ";
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
	$array_wip['valorvenda'][$regs2["AF2_ORCAME"]] += $regs2["AF2_TOTAL"];	

	if(trim($regs2["AF2_GRPCOM"])=='DES')
	{
		//TABELA AF4 - DESPESAS - CUSTO PREVISTO
		$sql = "SELECT * FROM AF4010 WITH(NOLOCK) ";
		$sql .= "WHERE AF4010.D_E_L_E_T_ = '' ";
		$sql .= "AND AF4_ORCAME = '".$regs2["AF2_ORCAME"]."' ";
		$sql .= "AND AF4_TAREFA = '".$regs2["AF2_TAREFA"]."' ";

		$db->select($sql,'MSSQL',true);

		if($db->erro!='')
		{
			die($db->erro);
		}
		
		$array_desp = $db->array_select;
	
		foreach($array_desp as $regs5)
		{
			//CUSTO PREVISTO
			$array_wip['custodesp'][$regs5["AF4_ORCAME"]] += $regs5["AF4_VALOR"];
			
			//$array_conta[$array_cod_desp[trim($regs2["AF2_COMPOS"])]] = trim($regs5["AF4_DESCRI"]);
			$array_conta[$regs5["AF4_ORCAME"]][$array_cod_desp[trim($regs2["AF2_COMPOS"])]] = trim($regs5["AF4_DESCRI"]);
		}
	}
	else
	{
		$array_wip['customo'][$regs2["AF2_ORCAME"]] += $regs2["AF2_CUSTO"];
		
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
			//CUSTO PREVISTO
			$array_wip['custoprevsubcontrato'][$regs6["AF3_ORCAME"]] += $regs6["AF3_CUSTD"];

			
			//armazena as tarefas para buscar o custo real
			//$array_tarefas[trim($regs2["AF3_COMPOS"])][trim($regs6["B1_DESC"])] = trim($regs6["AF3_TAREFA"]);
			//PEGA O VALOR PELA NF DE ENTRADA
			$sql = "SELECT SUM(D1_TOTAL) AS TOTAL, D1_EMISSAO FROM SD1010 WITH(NOLOCK) ";
			$sql .= "WHERE SD1010.D_E_L_E_T_ = '' ";
			$sql .= "AND D1_COD = '".$regs6["B1_COD"]."' ";
			$sql .= "AND SUBSTRING (D1_CLVL,9,10) = '".$regs6["AF3_ORCAME"]."' ";
			$sql .= "GROUP BY D1_EMISSAO ";									
			
			$db->select($sql,'MSSQL',true);

			if($db->erro!='')
			{
				die($db->erro);
			}
			
			foreach($db->array_select as $regs7)
			{
				$array_wip['custorealsubcontrato'][$regs6["AF3_ORCAME"]] += $regs7["TOTAL"];
			
				//POR PERIODO 01 AO ULTIMO DIA DO MES ANTERIOR
				if($pri_data<=$regs7["D1_EMISSAO"] && $ult_data>=$regs7["D1_EMISSAO"])
				{
					$array_wip['custorealsubcontrato_per'][$regs6["AF3_ORCAME"]] += $regs7["TOTAL"];
				}
			}			
		}									
	}	
}

//custo real despesas
foreach($array_conta as $orcame=>$array_compos)
{
	foreach($array_compos as $conta=>$descri)
	{
		//percorre a tabela de CUSTOS CONTABIL (CT1 e CT2)
		$sql = "SELECT CT2_DATA, SUM(CT2_VALOR) AS VALOR, SUBSTRING (CT2_CLVLDB,9,10) AS ORCAME FROM CT1010 WITH(NOLOCK), CT2010 WITH(NOLOCK) ";
		$sql .= "WHERE CT1010.D_E_L_E_T_ = '' ";
		$sql .= "AND CT2010.D_E_L_E_T_ = '' ";
		$sql .= "AND CT1_CONTA = '".$conta."' ";
		$sql .= "AND SUBSTRING (CT2_CLVLDB,9,10) = '".$orcame."' ";
		$sql .= "AND CT2_DEBITO = CT1_CONTA ";
		$sql .= "GROUP BY CT2_DATA, CT2_CLVLDB ";
		
		$db->select($sql,'MSSQL',true);
		
		if($db->erro!='')
		{
			die($db->erro);
		}
		
		$array_ct2 = $db->array_select;
		
		foreach($array_ct2 as $regs7)
		{
			$array_wip['custorealdespesas'][$regs7["ORCAME"]] += $regs7["VALOR"];
			
			//POR PERIODO 01 AO ULTIMO DIA DO MES ANTERIOR
			if($pri_data<=$regs7["CT2_DATA"] && $ult_data>=$regs7["CT2_DATA"])
			{
				$array_wip['custorealdespesas_per'][$regs7["ORCAME"]] += $regs7["VALOR"];
			}
		}
	}
}

//CUSTO REAL			
//HORAS REALIZADAS			
$sql = "SELECT AF8_ORCAME, AJK_RECURS, AJK_DATA, AJK_HQUANT FROM AE8010 WITH(NOLOCK), AJK010 WITH(NOLOCK), AF8010 WITH(NOLOCK), AF9010 WITH(NOLOCK) ";
$sql .= "WHERE AJK010.D_E_L_E_T_ = '' ";
$sql .= "AND AF8010.D_E_L_E_T_ = '' ";
$sql .= "AND AE8010.D_E_L_E_T_ = '' ";
$sql .= "AND AF9010.D_E_L_E_T_ = '' ";
$sql .= "AND AJK_CTRRVS = '1' ";
//$sql .= "AND AF8_FASE IN ('02','03','04','07','09','05','11','12') ";
//$sql .= "AND AF8_PROJET >= 0000003000 ";
$sql .= "AND AF8_PROJET IN (".implode(",",$a_projetos).") ";
$sql .= "AND AF8_PROJET = AF9_PROJET ";
$sql .= "AND AF8_REVISA = AF9_REVISA ";
$sql .= "AND AJK_PROJET = AF8_PROJET ";
$sql .= "AND AJK_REVISA = AF8_REVISA ";
$sql .= "AND AJK_TAREFA = AF9_TAREFA ";
$sql .= "AND AJK_RECURS = AE8_RECURS ";

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
	
	$total = 0;

	switch ($regs4[" tipo_contrato"])
	{
		case 'SC':
		case 'SC+CLT':

			$total = round($regs4["salario_hora"]*$regs6["AJK_HQUANT"],2);
			
		break;
		
		case 'CLT':
		case 'EST':

			$total = round((($regs4["salario_clt"]/176)*1.84*$regs6["AJK_HQUANT"]),2);
			
		break;
		
		case 'SC+MENS':
		case 'SC+CLT+MENS':
		
			$total = round((($regs4["salario_mensalista"]/176)*$regs6["AJK_HQUANT"]),2);
			
		break;
   }
   
   $array_wip['horasrealmo'][$regs6["AF8_ORCAME"]] += $regs6["AJK_HQUANT"];
   
   $array_wip['custorealmo'][$regs6["AF8_ORCAME"]] += $total;
   
   //POR PERIODO 26 DO MES ANTERIOR A 25 
   if($pri_data<=$regs6["AJK_DATA"] && $ult_data>=$regs6["AJK_DATA"])
   {
	   $array_wip['horasrealmo_per'][$regs6["AF8_ORCAME"]] += $regs6["AJK_HQUANT"];
	   
	   $array_wip['custorealmo_per'][$regs6["AF8_ORCAME"]] += $total;		
   }
   
	//Obtem o valor medido do BMS
	/*
	$sql = "SELECT SUM(valor_medido) AS valor_medido FROM ".DATABASE.".bms_pedido, ".DATABASE.".bms_medicao, ".DATABASE.".bms_item ";
	$sql .= "WHERE bms_pedido.reg_del = 0 ";
	$sql .= "AND bms_medicao.reg_del = 0 ";
	$sql .= "AND bms_item.reg_del = 0 ";
	$sql .= "AND bms_item.id_bms_item = bms_medicao.id_bms_item ";
	$sql .= "AND bms_pedido.id_bms_pedido = bms_medicao.id_bms_pedido ";
	$sql .= "AND bms_pedido.id_os = '".intval($regs6["AF8_ORCAME"])."' ";
	$sql .= "AND bms_medicao.id_bms_controle IN (2,3,5) "; //MEDIDO, FATURADO, BMS GERADO
	*/	
}

$sql = "SELECT bms_medicao.valor_medido, bms_medicao.data, bms_pedido.id_os FROM ".DATABASE.".bms_pedido, ".DATABASE.".bms_medicao, ".DATABASE.".bms_item ";
$sql .= "WHERE bms_pedido.reg_del = 0 ";
$sql .= "AND bms_medicao.reg_del = 0 ";
$sql .= "AND bms_item.reg_del = 0 ";
$sql .= "AND bms_item.id_bms_item = bms_medicao.id_bms_item ";
$sql .= "AND bms_pedido.id_bms_pedido = bms_medicao.id_bms_pedido ";
$sql .= "AND bms_pedido.id_os IN (".implode(",",$a_projetos_med).") ";
$sql .= "AND bms_medicao.id_bms_controle IN (2,3,5) "; //MEDIDO, FATURADO, BMS GERADO
$sql .= "ORDER BY bms_pedido.id_os ";

$db->select($sql,'MYSQL',true);

foreach($db->array_select as $regs5)
{	
	$array_wip['valormedido'][sprintf("%010d",$regs5["os"])] += $regs5["valor_medido"];
	
	$dataper = explode("-",$regs5["data"]);
	
	//soma os BMS do periodo
	if($dataper[1]==$mes)
	{
		$array_wip['valormedidoper'][sprintf("%010d",$regs5["os"])] += $regs5["valor_medido"];
		
		$array_wip['projetosper'][sprintf("%010d",$regs5["os"])] = sprintf("%010d",$regs5["os"]);
	}
}

//die(print_r($array_wip["projetosper"],true));

//Seleciona as PROJETOS
$sql = "SELECT AF8_PROJET, AF8_FASE, AF8_DESCRI, AF8_COORD1, AF1_TPORC, AEA_DESCRI, A1_NOME FROM AF8010 WITH(NOLOCK), AF1010 WITH(NOLOCK), AEA010 WITH(NOLOCK), SA1010 WITH(NOLOCK) ";
$sql .= "WHERE AF8010.D_E_L_E_T_ = '' ";
$sql .= "AND AF1010.D_E_L_E_T_ = '' ";
$sql .= "AND SA1010.D_E_L_E_T_ = '' ";
$sql .= "AND AEA010.D_E_L_E_T_ = '' ";
$sql .= "AND AF8010.AF8_ORCAME = AF1010.AF1_ORCAME ";
$sql .= "AND AF8010.AF8_FASE = AEA010.AEA_COD ";
$sql .= "AND AF8010.AF8_CLIENT = SA1010.A1_COD ";
$sql .= "AND AF8010.AF8_LOJA = SA1010.A1_LOJA ";
//$sql .= "AND AF8_FASE IN ('02','03','04','07','09','05','11','12') ";
//$sql .= "AND AF8_PROJET >= 0000003000 ";
$sql .= "AND AF8_PROJET IN (".implode(",",$a_projetos).") ";
$sql .= "ORDER BY AF8_PROJET ";

$db->select($sql,'MSSQL',true);

if($db->erro!='')
{
	die($db->erro);
}

$array_proj = $db->array_select;

foreach($array_proj as $regs1)
{	
	$folha = 0;
	
	
	//OS INTERNAS
	if(intval($regs1["AF8_PROJET"])<3000)
	{
		$folha = 2;
	}
	else
	{
		//OS GLOBAL
		if(in_array($regs1["AF8_FASE"],array('02','03','04','07','05','11','12')) && intval($regs1["AF1_TPORC"])==2)
		{
			$folha = 0;
		}
		else
		{
			//ADM
			if(in_array($regs1["AF8_FASE"],array('09','04')) && intval($regs1["AF1_TPORC"])==1)
			{
				$folha = 1;
			}
		}
	}
			
	$array_wip['numero'][$folha][$regs1["AF8_PROJET"]] = str_replace(array("'", '"', '\"', "\'"),"",trim($regs1["AF8_DESCRI"]));
	
	$array_wip['coord'][$regs1["AF8_PROJET"]]= str_replace(array("'", '"', '\"', "\'"),"",trim($array_coord[$regs1["AF8_COORD1"]]));
	
	$array_wip['cliente'][$regs1["AF8_PROJET"]] = str_replace(array("'", '"', '\"', "\'"),"",trim($regs1["A1_NOME"]));
	
	$array_wip['fase'][$regs1["AF8_PROJET"]] = str_replace(array("'", '"', '\"', "\'"),"",trim($regs1["AEA_DESCRI"]));
	
	$array_folha[$folha] = $folha;
	
	if(in_array($regs1["AF8_PROJET"],$array_wip['projetosper']))
	{
		$folha = 3;
		
		$array_wip['numero'][$folha][$regs1["AF8_PROJET"]] = str_replace(array("'", '"', '\"', "\'"),"",trim($regs1["AF8_DESCRI"]));
		
		$array_folha[$folha] = $folha;
	}	
}

//die('ok '.print_r($array_wip['custorealsubcontrato'],true));

$objPHPExcel = PHPExcel_IOFactory::load("../modelos_excel/relatorio_wip_modelo.xls");

$locale = 'pt_br';

$validlocale = PHPExcel_Settings::setlocale($locale);

if (!$validlocale) 
{
	echo 'Unable to set locale to '.$locale." - reverting to en_us<br />\n";
}

// Redirect output to a client�s web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="relatorio_wip_"'.date('dmYHis').'".xlsx"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

//SEPARA AS folhas NAS ABAS
foreach($array_folha as $folha)
{
	$objPHPExcel->setActiveSheetIndex($folha);
	
	$st = $objPHPExcel->getActiveSheet();
	
	//data emiss�o
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, 3, iconv('ISO-8859-1', 'UTF-8',"data de emiss�o: ".date('d/m/Y')));
	
	$linha = 7;
	
	foreach($array_wip['numero'][$folha] as $projeto=>$descricao)
	{
		$custo_real = 0;
		
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $linha, iconv('ISO-8859-1', 'UTF-8',$projeto));
		
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $linha, iconv('ISO-8859-1', 'UTF-8', $descricao));
		
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $linha, iconv('ISO-8859-1', 'UTF-8', $array_wip['coord'][$projeto]));
		
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $linha, iconv('ISO-8859-1', 'UTF-8', $array_wip['cliente'][$projeto]));
		
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, $linha, iconv('ISO-8859-1', 'UTF-8', $array_wip['fase'][$projeto]));
		
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, $linha, $array_wip['valorvenda'][$projeto]);
		
		$objPHPExcel->getActiveSheet()->getStyle("F".$linha)->getNumberFormat()->setFormatCode('R$ #,#00.00');
		
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $linha, $array_wip['horasprev'][$projeto]);
		
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, $linha, $array_wip['customo'][$projeto]);
		
		$objPHPExcel->getActiveSheet()->getStyle("H".$linha)->getNumberFormat()->setFormatCode('R$ #,#00.00');
		
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8, $linha, $array_wip['custodesp'][$projeto]);
		
		$objPHPExcel->getActiveSheet()->getStyle("I".$linha)->getNumberFormat()->setFormatCode('R$ #,#00.00');
		
		$objPHPExcel->getActiveSheet()->setCellValue('J'.$linha, '=SUM(H'.$linha.':I'.$linha.')');
		
		$objPHPExcel->getActiveSheet()->getStyle("J".$linha)->getNumberFormat()->setFormatCode('R$ #,#00.00');

		$objPHPExcel->getActiveSheet()->setCellValue('K'.$linha, '=(F'.$linha.'/J'.$linha.')');
		
		$objPHPExcel->getActiveSheet()->setCellValue('L'.$linha, '=(F'.$linha.'-J'.$linha.')');
		
		$objPHPExcel->getActiveSheet()->getStyle("L".$linha)->getNumberFormat()->setFormatCode('R$ #,#00.00');
		
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(12, $linha, $array_wip['horasrealmo'][$projeto]);
		
		$custo_real = $array_wip['custorealsubcontrato'][$projeto]+$array_wip['custorealmo'][$projeto];
		
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(13, $linha, $custo_real);
		
		$objPHPExcel->getActiveSheet()->getStyle("N".$linha)->getNumberFormat()->setFormatCode('R$ #,#00.00');
		
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(14, $linha, $array_wip['custorealdespesas'][$projeto]);
		
		$objPHPExcel->getActiveSheet()->getStyle("O".$linha)->getNumberFormat()->setFormatCode('R$ #,#00.00');
		
		$objPHPExcel->getActiveSheet()->setCellValue('P'.$linha, '=SUM(N'.$linha.':O'.$linha.')');
		
		$objPHPExcel->getActiveSheet()->getStyle("P".$linha)->getNumberFormat()->setFormatCode('R$ #,#00.00');		
		
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(16, $linha, $array_wip['horasrealmo_per'][$projeto]);
		
		$custo_real_per = $array_wip['custorealsubcontrato_per'][$projeto]+$array_wip['custorealmo_per'][$projeto];
		
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(17, $linha, $custo_real_per);
		
		$objPHPExcel->getActiveSheet()->getStyle("R".$linha)->getNumberFormat()->setFormatCode('R$ #,#00.00');
		
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(18, $linha, $array_wip['custorealdespesas_per'][$projeto]);
		
		$objPHPExcel->getActiveSheet()->getStyle("S".$linha)->getNumberFormat()->setFormatCode('R$ #,#00.00');
		
		$objPHPExcel->getActiveSheet()->setCellValue('T'.$linha, '=SUM(R'.$linha.':S'.$linha.')');
		
		$objPHPExcel->getActiveSheet()->getStyle("T".$linha)->getNumberFormat()->setFormatCode('R$ #,#00.00');
		
		$objPHPExcel->getActiveSheet()->setCellValue('U'.$linha, '=P'.$linha.'/J'.$linha);
		
		//$objPHPExcel->getActiveSheet()->getStyle("U".$linha)->getNumberFormat()->setFormatCode('R$ #,#00.00');	
		
		//$objPHPExcel->getActiveSheet()->setCellValue('V'.$linha, '=T'.$linha.'*H'.$linha);
		
		//$objPHPExcel->getActiveSheet()->getStyle("V".$linha)->getNumberFormat()->setFormatCode('R$ #,#00.00');		

		$objPHPExcel->getActiveSheet()->setCellValue('W'.$linha, '=T'.$linha.'*K'.$linha);
		
		$objPHPExcel->getActiveSheet()->getStyle("W".$linha)->getNumberFormat()->setFormatCode('R$ #,#00.00');
		
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(23, $linha, $array_wip['valormedidoper'][$projeto]);
		
		$objPHPExcel->getActiveSheet()->getStyle("X".$linha)->getNumberFormat()->setFormatCode('R$ #,#00.00');
		
		$objPHPExcel->getActiveSheet()->setCellValue('Y'.$linha, '=P'.$linha.'*K'.$linha);
		
		$objPHPExcel->getActiveSheet()->getStyle("Y".$linha)->getNumberFormat()->setFormatCode('R$ #,#00.00');
		
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(25, $linha, $array_wip['valormedido'][$projeto]);
		
		$objPHPExcel->getActiveSheet()->getStyle("Z".$linha)->getNumberFormat()->setFormatCode('R$ #,#00.00');
		
		$objPHPExcel->getActiveSheet()->setCellValue('AA'.$linha, '=Z'.$linha.'-Y'.$linha);
		
		$objPHPExcel->getActiveSheet()->setCellValue('AB'.$linha, '=AA'.$linha.'/Y'.$linha);
		
		$linha++;	
	}
	
	$linha++;
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $linha, iconv('ISO-8859-1', 'UTF-8',"SUBTOTAL:"));	
	
	$objPHPExcel->getActiveSheet()->setCellValue('F'.$linha, '=SUBTOTAL(9,F7:F'.($linha-2).')');
	
	$objPHPExcel->getActiveSheet()->getStyle("F".$linha)->getNumberFormat()->setFormatCode('R$ #,#00.00');
	
	$objPHPExcel->getActiveSheet()->setCellValue('G'.$linha, '=SUBTOTAL(9,G7:G'.($linha-2).')');	
	
	$objPHPExcel->getActiveSheet()->setCellValue('H'.$linha, '=SUBTOTAL(9,H7:H'.($linha-2).')');
	
	$objPHPExcel->getActiveSheet()->getStyle("H".$linha)->getNumberFormat()->setFormatCode('R$ #,#00.00');
	
	$objPHPExcel->getActiveSheet()->setCellValue('I'.$linha, '=SUBTOTAL(9,I7:I'.($linha-2).')');
	
	$objPHPExcel->getActiveSheet()->getStyle("I".$linha)->getNumberFormat()->setFormatCode('R$ #,#00.00');
	
	$objPHPExcel->getActiveSheet()->setCellValue('J'.$linha, '=SUBTOTAL(9,J7:J'.($linha-2).')');
	
	$objPHPExcel->getActiveSheet()->getStyle("J".$linha)->getNumberFormat()->setFormatCode('R$ #,#00.00');
	
	$objPHPExcel->getActiveSheet()->setCellValue('L'.$linha, '=SUBTOTAL(9,L7:L'.($linha-2).')');
	
	$objPHPExcel->getActiveSheet()->getStyle("L".$linha)->getNumberFormat()->setFormatCode('R$ #,#00.00');
	
	$objPHPExcel->getActiveSheet()->setCellValue('M'.$linha, '=SUBTOTAL(9,M7:M'.($linha-2).')');
	
	$objPHPExcel->getActiveSheet()->setCellValue('N'.$linha, '=SUBTOTAL(9,N7:N'.($linha-2).')');
	
	$objPHPExcel->getActiveSheet()->getStyle("N".$linha)->getNumberFormat()->setFormatCode('R$ #,#00.00');
	
	$objPHPExcel->getActiveSheet()->setCellValue('O'.$linha, '=SUBTOTAL(9,O7:O'.($linha-2).')');
	
	$objPHPExcel->getActiveSheet()->getStyle("O".$linha)->getNumberFormat()->setFormatCode('R$ #,#00.00');
	
	$objPHPExcel->getActiveSheet()->setCellValue('P'.$linha, '=SUBTOTAL(9,P7:P'.($linha-2).')');
	
	$objPHPExcel->getActiveSheet()->getStyle("P".$linha)->getNumberFormat()->setFormatCode('R$ #,#00.00');
	
	$objPHPExcel->getActiveSheet()->setCellValue('Q'.$linha, '=SUBTOTAL(9,Q7:Q'.($linha-2).')');
	
	$objPHPExcel->getActiveSheet()->setCellValue('R'.$linha, '=SUBTOTAL(9,R7:R'.($linha-2).')');
	
	$objPHPExcel->getActiveSheet()->getStyle("R".$linha)->getNumberFormat()->setFormatCode('R$ #,#00.00');
	
	$objPHPExcel->getActiveSheet()->setCellValue('S'.$linha, '=SUBTOTAL(9,S7:S'.($linha-2).')');
	
	$objPHPExcel->getActiveSheet()->getStyle("S".$linha)->getNumberFormat()->setFormatCode('R$ #,#00.00');
	
	$objPHPExcel->getActiveSheet()->setCellValue('T'.$linha, '=SUBTOTAL(9,T7:T'.($linha-2).')');
	
	$objPHPExcel->getActiveSheet()->getStyle("T".$linha)->getNumberFormat()->setFormatCode('R$ #,#00.00');
	
	$objPHPExcel->getActiveSheet()->setCellValue('W'.$linha, '=SUBTOTAL(9,W7:W'.($linha-2).')');
	
	$objPHPExcel->getActiveSheet()->getStyle("W".$linha)->getNumberFormat()->setFormatCode('R$ #,#00.00');
	
	$objPHPExcel->getActiveSheet()->setCellValue('X'.$linha, '=SUBTOTAL(9,X7:X'.($linha-2).')');
	
	$objPHPExcel->getActiveSheet()->getStyle("X".$linha)->getNumberFormat()->setFormatCode('R$ #,#00.00');
	
	$objPHPExcel->getActiveSheet()->setCellValue('Y'.$linha, '=SUBTOTAL(9,Y7:Y'.($linha-2).')');
	
	$objPHPExcel->getActiveSheet()->getStyle("Y".$linha)->getNumberFormat()->setFormatCode('R$ #,#00.00');
	
	$objPHPExcel->getActiveSheet()->setCellValue('Z'.$linha, '=SUBTOTAL(9,Z7:Z'.($linha-2).')');
	
	$objPHPExcel->getActiveSheet()->getStyle("Z".$linha)->getNumberFormat()->setFormatCode('R$ #,#00.00');

}

$objPHPExcel->setActiveSheetIndex(0);

$objWriter->save('php://output');

exit;

?>
