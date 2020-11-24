<?php
/*
	Relatório de Medicao
	Criado por Carlos Abreu  
	
	Versão 0 --> VERSÃO INICIAL : 25/06/2015
	Versão 1 --> Inclusão dos campos reg_del nas consultas - 17/11/2017 - Carlos Abreu
	Versão 2 --> Alterações do chamado #2613 - 15/02/2018 - Carlos Abreu
	Versão 3 --> Alterações do chamado #2613 - 05/04/2018 - Carlos Abreu
*/

ini_set('max_execution_time', 0); // No time limit
ini_set('post_max_size', '20M');
ini_set('upload_max_filesize', '20M');
ini_set('memory_limit', '2014M');
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

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(556))
{
	nao_permitido();
}

require_once(INCLUDE_DIR."PHPExcel/Classes/PHPExcel.php");

$db = new banco_dados();

$chars = array("'","\"",")","(","\\","/",".",":","&","%","´","`","'","?");

if($_POST["intervalo"]=='periodo')
{
	$data_ini = substr(php_mysql($_POST["data_ini"]),0,4).'-'.substr(php_mysql($_POST["data_ini"]),5,2); //ano/mes
	$data_fim = substr(php_mysql($_POST["datafim"]),0,4).'-'.substr(php_mysql($_POST["datafim"]),5,2); //ano/mes;
}

//SELECIONA O COORDENADOR
/*
$sql = "SELECT * FROM PA7010 WITH(NOLOCK) ";
$sql .= "WHERE PA7010.D_E_L_E_T_ = '' ";

$db->select($sql,'MSSQL',true);

foreach ($db->array_select as $regs1)
{
	$array_coordenadores[$regs1["PA7_ID"]] = str_replace($chars,"",$regs1["PA7_NOME"]);	
}
*/

//filtra as OS com pedidos e itens, excluindo as excess�es
$sql = "SELECT bms_pedido.os, ordem_servico.id_os, ordem_servico.id_cod_coord FROM ".DATABASE.".bms_medicao, ".DATABASE.".bms_pedido, ".DATABASE.".bms_item, ".DATABASE.".ordem_servico "; 
$sql .= "WHERE bms_pedido.reg_del = 0 ";
$sql .= "AND bms_medicao.reg_del = 0 "; 
$sql .= "AND bms_item.reg_del = 0 ";
$sql .= "AND ordem_servico.reg_del = 0 ";
$sql .= "AND bms_item.id_bms_item = bms_medicao.id_bms_item ";
$sql .= "AND bms_pedido.os = ordem_servico.os ";
$sql .= "AND ordem_servico.id_os_status IN (1,2,3,5,7,14,15,16,17,18,19) ";
$sql .= "AND bms_medicao.id_bms_controle IN (1,2,3,5) "; //planejada, medido, faturado, bms gerado
$sql .= "AND bms_pedido.id_bms_pedido = bms_item.id_bms_pedido ";
$sql .= "AND (bms_pedido.data_pedido >= '2017-07-01' ";
$sql .= "OR bms_pedido.os IN (SELECT bms_excecoes.os FROM ".DATABASE.".bms_excecoes WHERE bms_excecoes.reg_del = 0)) ";
$sql .= "GROUP BY bms_pedido.os ";

$db->select($sql,'MYSQL',true);

foreach ($db->array_select as $regs2)
{
	$array_os_pedidos[sprintf("%010d",$regs2["os"])] = sprintf("%010d",$regs2["os"]);
	
	$array_os_bms[$regs2["os"]] = $regs2["os"];
	
	$array_os_coord[$regs2["id_cod_coord"]] = $array_coordenadores[sprintf("%04d",$regs2["id_cod_coord"])];
}

//die(print_r($array_os_coord,true));

$array_os = implode(",",$array_os_pedidos);

/*
$array_os_medicao = implode(",",$array_os_bms);

//Obtem o total por OS
$sql = "SELECT * FROM ".DATABASE.".ordem_servico, ".DATABASE.".bms_item, ".DATABASE.".bms_medicao, ".DATABASE.".bms_pedido ";
$sql .= "WHERE bms_item.reg_del = 0 ";
$sql .= "AND ordem_servico.reg_del = 0 ";
$sql .= "AND bms_medicao.reg_del = 0 ";
$sql .= "AND bms_pedido.reg_del = 0 ";
$sql .= "AND bms_pedido.os = ordem_servico.os ";
$sql .= "AND bms_item.id_bms_item = bms_medicao.id_bms_item ";
$sql .= "AND bms_pedido.id_bms_pedido = bms_item.id_bms_pedido ";
$sql .= "AND bms_pedido.os IN (".$array_os_medicao.") ";
$sql .= "AND bms_medicao.id_bms_controle IN (1,2,3,5) "; //planejada, medido, faturado, bms gerado

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}
else
{
	foreach($db->array_select as $regs)
	{
		$array_medicao['valor_medido_coordenador'][sprintf("%04d",$regs['id_cod_coord'])][date("Y-m",strtotime($regs["data"]))] += $regs["valor_medido"];
	
		$array_medicao['valor_planejado_coordenador'][sprintf("%04d",$regs['id_cod_coord'])][date("Y-m",strtotime($regs["data"]))] += $regs["valor_planejado"];
	}
}
*/

//obtem a mÃo de obra a partir dos apontamentos da disciplina no periodo
$sql = "SELECT * FROM ".DATABASE.".apontamento_horas, ".DATABASE.".funcionarios ";
$sql .= "WHERE apontamento_horas.reg_del = 0 ";
$sql .= "AND funcionarios.reg_del = 0 ";
$sql .= "AND apontamento_horas.id_funcionario = funcionarios.id_funcionario ";

if($_POST["intervalo"]=='periodo')
{
	$sql .= "AND DATE_FORMAT(data, '%Y-%m') BETWEEN '".$data_ini."' AND '".$data_fim."' ";
}

//$sql .= "AND apontamento_horas.id_os = '".$array_id_os[$os]."' ";
$sql .= "GROUP BY funcionarios.id_funcionario, funcionarios.id_setor, DATE_FORMAT(apontamento_horas.data, '%Y-%m') ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}
else
{
	foreach($db->array_select as $regs)
	{		
		if($regs["id_local"]!=3) //exclui Mogi das Cruzes
		{
			//contabiliza os funcionarios alocados nos clientes
			$array_medicao['num_func_adm'][date("Y-m",strtotime($regs["data"]))] += 1;
		}
		else
		{
			$array_medicao['num_func'][$regs["id_setor"]][date("Y-m",strtotime($regs["data"]))] += 1;
			
			//contabiliza os funcionarios das disciplinas - pct
			if(in_array($regs["id_setor"],array(5,6,7,8,9,10,11,12,13,14,15,18,20,23,24)))
			{		
				$array_medicao['num_func_pct'][date("Y-m",strtotime($regs["data"]))] += 1;
			}
			else
			{
				$array_medicao['num_func_out'][date("Y-m",strtotime($regs["data"]))] += 1;
			}
		}
	}
}

//Seleciona os Projetos
$sql = "SELECT * FROM AF8010 WITH (NOLOCK), SA1010 WITH (NOLOCK), AEA010 WITH (NOLOCK), AF1010 WITH (NOLOCK) ";
$sql .= "LEFT JOIN AF5010 WITH (NOLOCK) ON (AF5_ORCAME = AF1_ORCAME AND AF5010.D_E_L_E_T_ = '' AND AF5_NIVEL = '001') ";
$sql .= "WHERE AF1010.D_E_L_E_T_ = '' ";
$sql .= "AND SA1010.D_E_L_E_T_ = '' ";
$sql .= "AND AEA010.D_E_L_E_T_ = '' ";
$sql .= "AND AF8010.D_E_L_E_T_ = '' ";
$sql .= "AND AF8_ORCAME = AF1_ORCAME ";
$sql .= "AND AF1_CLIENT = A1_COD ";
$sql .= "AND AF1_LOJA = A1_LOJA ";
$sql .= "AND AF8_FASE = AEA_COD ";
$sql .= "AND AF1_ORCAME > '0000003000' ";
$sql .= "AND AF1_ORCAME IN (".$array_os.") ";

if($_POST["intervalo"]=='periodo')
{
	//$sql .= "AND DATE_FORMAT(AF1_DTAPRO, '%Y-%m') BETWEEN '".$data_ini."' AND '".$data_fim."' ";
	
	$sql .= "AND MONTH(AF1_DTAPRO) BETWEEN ".substr(php_mysql($_POST["data_ini"]),5,2)." AND ".substr(php_mysql($_POST["datafim"]),5,2)."
				AND 
			 YEAR(AF1_DTAPRO) BETWEEN ".substr(php_mysql($_POST["data_ini"]),0,4)." AND ".substr(php_mysql($_POST["datafim"]),0,4)." ";		
}

if($_POST["escolhacoord"]!='-1')
{
	$sql .= "AND AF8_COORD1 = '".sprintf("%04d",$_POST["escolhacoord"])."' ";
}

$sql .= "ORDER BY AF1_ORCAME ";

$db->select($sql,'MSSQL', true);

if($db->erro!='')
{
	die($db->erro);
}

$array_proj = $db->array_select;

foreach($array_proj as $regs0)
{	
	$os = intval($regs0["AF1_ORCAME"]);
	
	switch(intval($regs0["AF1_TPORC"]))
	{
		case '1':
			$tipo_orcamento = 'SERVI�O POR ADM';
		break;
		
		case '2':
			$tipo_orcamento = 'PRE�O GLOBAL';
		break;
		
		case '3':
			$tipo_orcamento = 'OS INTERNA';
		break;
		
		default:
			$tipo_orcamento = '';
	}
	
	//Obtem o acumulado por OS
	$sql = "SELECT * FROM ".DATABASE.".bms_item, ".DATABASE.".bms_medicao, ".DATABASE.".bms_pedido ";
	$sql .= "WHERE bms_item.reg_del = 0 ";
	$sql .= "AND bms_medicao.reg_del = 0 ";
	$sql .= "AND bms_pedido.reg_del = 0 ";
	$sql .= "AND bms_item.id_bms_item = bms_medicao.id_bms_item ";
	$sql .= "AND bms_pedido.id_bms_pedido = bms_item.id_bms_pedido ";
	$sql .= "AND bms_pedido.os = '".$os."' ";
	$sql .= "AND bms_medicao.id_bms_controle IN (1,2,3,5) "; //planejada, medido, faturado, bms gerado
	
	$db->select($sql,'MYSQL',true);
	
	if($db->erro!='')
	{
		die($db->erro);
	}
	else
	{
		foreach($db->array_select as $regs)
		{
			$array_medicao['valor_acumulado'][$regs0["AF1_ORCAME"]] += $regs["valor_medido"];
			
			if(in_array($regs0["AF8_FASE"],array('09','04')) && intval($regs0["AF1_TPORC"])==1)
			{
				
			}
			else
			{
				//if(true || intval($regs0["AF1_TPORC"])==2 && intval($regs0["AF8_FASE"])!=9) //pacote
				//{
					$array_medicao['valor_carteira_acumulado'] += $regs["valor_medido"];
				//}
			}
			
			/*
			if(intval($regs0["AF1_TPORC"])==2 && intval($regs0["AF8_FASE"])!=9) //pacote
			{
				//$array_medicao['valor_carteira_pct'][$regs0["AF1_ORCAME"]] += $regs["valor_medido"];
				
				//$array_medicao['valor_carteira_acumulado'][date("Y-m",strtotime($data_apro))] += $regs["valor_medido"];
				
				//$array_medicao['valor_carteira_acumulado'][date("Y-m",strtotime($regs["data"]))] += $regs["valor_medido"];
				
				//$array_os_pct[$regs["os"]] = $regs["os"];
				
				$array_medicao['valor_carteira_acumulado'] += $regs["valor_medido"];				
				
			}
			*/
		}
	}
	
	//Obtem os periodos e os valores (medido/planejado/%)
	$sql = "SELECT * FROM ".DATABASE.".ordem_servico, ".DATABASE.".bms_item, ".DATABASE.".bms_medicao, ".DATABASE.".bms_pedido ";
	$sql .= "WHERE bms_item.reg_del = 0 ";
	$sql .= "AND bms_medicao.reg_del = 0 ";
	$sql .= "AND bms_pedido.reg_del = 0 ";
	$sql .= "AND ordem_servico.reg_del = 0 ";
	$sql .= "AND bms_pedido.os = ordem_servico.os ";
	$sql .= "AND bms_item.id_bms_item = bms_medicao.id_bms_item ";
	$sql .= "AND bms_pedido.id_bms_pedido = bms_item.id_bms_pedido ";
	$sql .= "AND bms_pedido.os = '".$os."' ";
	
	if($_POST["intervalo"]=='periodo')
	{
		$sql .= "AND DATE_FORMAT(data, '%Y-%m') BETWEEN '".$data_ini."' AND '".$data_fim."' ";
	}
	
	$sql .= "AND bms_medicao.id_bms_controle IN (1,2,3,5) "; //planejada, medido, faturado, bms gerado
	
	$db->select($sql,'MYSQL',true);
	
	if($db->erro!='')
	{
		die($db->erro);
	}
	else
	{
		foreach($db->array_select as $regs)
		{
			//date('Y-m-d',mktime(0,0,0,$dados_form["mes"],1,$dados_form["ano"]))
			$array_medicao['periodo'][date("Y-m",strtotime($regs["data"]))] = date("Y-m",strtotime($regs["data"]));
			
			$array_medicao['valor_planejado'][$regs0["AF1_ORCAME"]][date("Y-m",strtotime($regs["data"]))] += $regs["valor_planejado"];
			
			$array_medicao['valor_medido'][$regs0["AF1_ORCAME"]][date("Y-m",strtotime($regs["data"]))] += $regs["valor_medido"];
			
			//$array_medicao['valor_medido_coordenador'][$regs0['AF8_COORD1']][date("Y-m",strtotime($regs["data"]))] += $regs["valor_medido"];
			
			//sumariza conforme tipo orcamento e fase
			if(in_array($regs0["AF8_FASE"],array('09','04')) && intval($regs0["AF1_TPORC"])==1) //ADM
			{
				$array_medicao['valor_medido_adm'][date("Y-m",strtotime($regs["data"]))] += $regs["valor_medido"];
			}
			else
			{
				//if(true || intval($regs0["AF1_TPORC"])==2 && intval($regs0["AF8_FASE"])!=9) //GLOBAL
				//{
					$array_medicao['valor_medido_pct'][date("Y-m",strtotime($regs["data"]))] += $regs["valor_medido"];
					
					$array_medicao['valor_medido_coordenador'][sprintf("%04d",$regs['id_cod_coord'])][date("Y-m",strtotime($regs["data"]))] += $regs["valor_medido"];
	
					$array_medicao['valor_planejado_coordenador'][sprintf("%04d",$regs['id_cod_coord'])][date("Y-m",strtotime($regs["data"]))] += $regs["valor_planejado"];					
					
				//}				
			}			
		}			
	}
	
	$array_medicao['projeto'][$regs0["AF1_ORCAME"]] = str_replace($chars,"",trim($regs0["AF1_DESCRI"]));
	
	$data_apro = trim($regs0["AF1_DTAPRO"]);
	
	$array_medicao['data_aprovacao'][$regs0["AF1_ORCAME"]] = substr($data_apro,4,2).'/'.substr($data_apro,0,4); //mes/ano
	
	$array_medicao['tipo_orcamento'][$regs0["AF1_ORCAME"]] = $tipo_orcamento; //tipo do orcamento
	
	$array_medicao['cliente'][$regs0["AF1_ORCAME"]] = str_replace($chars,"",trim($regs0["A1_NOME"]));
	
	$array_medicao['coordenador'][$regs0["AF1_ORCAME"]] = str_replace($chars,"",$array_coordenadores[$regs0["AF8_COORD1"]]);
	
	$array_medicao['status'][$regs0["AF1_ORCAME"]] = str_replace($chars,"",trim($regs0["AEA_DESCRI"]));
	
	$array_medicao['valor_total_contrato'][$regs0["AF1_ORCAME"]] = $regs0["AF5_TOTAL"];
	
	/*
	if(intval($regs0["AF1_TPORC"])==2 && intval($regs0["AF8_FASE"])!=9) //pacote
	{
		$array_medicao['valor_carteira_pct'][date("Y-m",strtotime($data_apro))] += $regs0["AF5_TOTAL"];		
	}
	*/
	if(in_array($regs0["AF8_FASE"],array('09','04')) && intval($regs0["AF1_TPORC"])==1) //ADM
	{
		//$array_medicao['valor_carteira_adm'][date("Y-m",strtotime($regs["data"]))] += $regs["valor_medido"];
	}
	else
	{
		//if(true || intval($regs0["AF1_TPORC"])==2 && intval($regs0["AF8_FASE"])!=9) //GLOBAL
		//{
			//$array_medicao['valor_medido_pct'][date("Y-m",strtotime($regs["data"]))] += $regs["valor_medido"];
			$array_medicao['valor_carteira_pct'][date("Y-m",strtotime($data_apro))] += $regs0["AF5_TOTAL"];			
		//}				
	}	
}
/*
$array_os_medicao = implode(",",$array_os_pct);

//Obtem o total por OS
$sql = "SELECT * FROM ".DATABASE.".ordem_servico, ".DATABASE.".bms_item, ".DATABASE.".bms_medicao, ".DATABASE.".bms_pedido ";
$sql .= "WHERE bms_item.reg_del = 0 ";
$sql .= "AND ordem_servico.reg_del = 0 ";
$sql .= "AND bms_medicao.reg_del = 0 ";
$sql .= "AND bms_pedido.reg_del = 0 ";
$sql .= "AND bms_pedido.os = ordem_servico.os ";
$sql .= "AND bms_item.id_bms_item = bms_medicao.id_bms_item ";
$sql .= "AND bms_pedido.id_bms_pedido = bms_item.id_bms_pedido ";
$sql .= "AND bms_pedido.os IN (".$array_os_medicao.") ";
$sql .= "AND bms_medicao.id_bms_controle IN (1,2,3,5) "; //planejada, medido, faturado, bms gerado

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}
else
{
	foreach($db->array_select as $regs)
	{
		$array_medicao['valor_medido_coordenador'][sprintf("%04d",$regs['id_cod_coord'])][date("Y-m",strtotime($regs["data"]))] += $regs["valor_medido"];
	
		$array_medicao['valor_planejado_coordenador'][sprintf("%04d",$regs['id_cod_coord'])][date("Y-m",strtotime($regs["data"]))] += $regs["valor_planejado"];
	}
}
*/

$objPHPExcel = PHPExcel_IOFactory::load("../modelos_excel/medicao_modelo.xls");

$locale = 'pt_br';

$validlocale = PHPExcel_Settings::setlocale($locale);

if (!$validlocale) 
{
	echo 'Unable to set locale to '.$locale." - reverting to en_us<br />\n";
}

// Redirect output to a client�s web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="planilha_medicao_'.date('Ymd-His').'.xlsx"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

//PLANILHA STATUS
$objPHPExcel->setActiveSheetIndex(0);

//COLUNA A EXCELL
$coluna = 0;

$linha = 5;

$coluna_periodos = 12;

//quantidades de periodos
if(count($array_medicao['periodo'])>1)
{
	$num_colunas = count($array_medicao['periodo']);
}
else
{
	$num_colunas = 1;
}

//acrescenta as colunas conforme os periodos e acrescenta os titulos dinamicamente
$objPHPExcel->getActiveSheet()->insertNewColumnBefore('M',($num_colunas*4));

$i = $coluna_periodos;

$h = $coluna_periodos;

ksort($array_medicao['periodo']);

foreach($array_medicao['periodo'] as $chave=>$valor)
{		
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($i, 2, iconv('ISO-8859-1', 'UTF-8',date("M/Y",strtotime($chave))));
	
	$objPHPExcel->getActiveSheet()->mergeCells(num2alfa($i)."2:".num2alfa($i+3)."2");
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($h, 3, iconv('ISO-8859-1', 'UTF-8','Planejado'));
	
	$objPHPExcel->getActiveSheet()->mergeCells(num2alfa($h)."3:".num2alfa($h+1)."3");
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($h+2, 3, iconv('ISO-8859-1', 'UTF-8','Medido'));
	
	$objPHPExcel->getActiveSheet()->mergeCells(num2alfa($h+2)."3:".num2alfa($h+3)."3");
	
	for($j=$i;$j<=$i+3;$j++)
	{		
		if($j%2==0)
		{	
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($j, 4, iconv('ISO-8859-1', 'UTF-8','Em %'));
		}
		else
		{
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($j, 4, iconv('ISO-8859-1', 'UTF-8','Em R$'));
		}
	}
			
	$i+=4;
	
	$h=$i;
}

foreach($array_medicao['projeto'] as $projeto=>$descricao)
{
	if (trim($descricao) == '')
	{
		continue;
	}
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna, $linha, $projeto);
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+1, $linha, iconv('ISO-8859-1', 'UTF-8',$descricao));
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+2, $linha, iconv('ISO-8859-1', 'UTF-8',$array_medicao['cliente'][$projeto]));
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+3, $linha, iconv('ISO-8859-1', 'UTF-8',$array_medicao['coordenador'][$projeto]));
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+4, $linha, iconv('ISO-8859-1', 'UTF-8',$array_medicao['data_aprovacao'][$projeto]));
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+5, $linha, iconv('ISO-8859-1', 'UTF-8',$array_medicao['tipo_orcamento'][$projeto]));
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+6, $linha, iconv('ISO-8859-1', 'UTF-8',$array_medicao['status'][$projeto]));
		
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+7, $linha, $array_medicao['valor_total_contrato'][$projeto]);
	
	$j = $coluna_periodos+1;
	
	$k = $coluna_periodos;
	
	$celulas = '';
	
	foreach($array_medicao['periodo'] as $periodo)
	{
		
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($j, $linha, $array_medicao['valor_planejado'][$projeto][$periodo]);
		
		$objPHPExcel->getActiveSheet()->getStyle(num2alfa($j).$linha)->getNumberFormat()->setFormatCode("R$ #,##0.00");
	
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($j+2, $linha, $array_medicao['valor_medido'][$projeto][$periodo]);
		
		$objPHPExcel->getActiveSheet()->getStyle(num2alfa($j+2).$linha)->getNumberFormat()->setFormatCode("R$ #,##0.00");
		
		//$celulas .= num2alfa($j+2).$linha.',';		
				
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($k, $linha, '='.num2alfa($k+1).$linha.'/H'.$linha);
		
		$objPHPExcel->getActiveSheet()->getStyle(num2alfa($k).$linha)->getNumberFormat()->applyFromArray(array('code' => PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE_00));
		
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($k+2, $linha, '='.num2alfa($k+3).$linha.'/H'.$linha);		
	
		$objPHPExcel->getActiveSheet()->getStyle(num2alfa($k+2).$linha)->getNumberFormat()->applyFromArray(array('code' => PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE_00));
	
		$j+=4;
		
		$k+=4;
	}
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+8, $linha, '=J'.$linha.'/H'.$linha);
	
	$objPHPExcel->getActiveSheet()->getStyle(num2alfa($coluna+8).$linha)->getNumberFormat()->applyFromArray(array('code' => PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE_00));
	
	//$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+9, $linha, '=SUM('.$celulas.')');
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+9, $linha, $array_medicao['valor_acumulado'][$projeto]);
	
	$objPHPExcel->getActiveSheet()->getStyle(num2alfa($coluna+9).$linha)->getNumberFormat()->setFormatCode("R$ #,##0.00");	
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+10, $linha, '=L'.$linha.'/H'.$linha);
	
	$objPHPExcel->getActiveSheet()->getStyle(num2alfa($coluna+10).$linha)->getNumberFormat()->applyFromArray(array('code' => PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE_00));
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+11, $linha, '=H'.$linha.'-J'.$linha);
	
	$objPHPExcel->getActiveSheet()->getStyle(num2alfa($coluna+11).$linha)->getNumberFormat()->setFormatCode("R$ #,##0.00");

	$linha++;
}

$linha++;

$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+6, $linha, 'SUBTOTAIS:');

$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+7, $linha, '=SUBTOTAL(9,H5'.':H'.($linha-2).')');

$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+11, $linha, '=SUBTOTAL(9,L5'.':L'.($linha-2).')');

$objPHPExcel->getActiveSheet()->getStyle(num2alfa($coluna+11).$linha)->getNumberFormat()->setFormatCode("R$ #,##0.00");

$m = $coluna_periodos;

for($l=0;$l<$num_colunas;$l++)
{	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($m+1, $linha, '=SUBTOTAL(9,'.num2alfa($m+1).'5'.':'.num2alfa($m+1).($linha-2).')');
	
	$objPHPExcel->getActiveSheet()->getStyle(num2alfa($m+1).$linha)->getNumberFormat()->setFormatCode("R$ #,##0.00");

	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($m+3, $linha, '=SUBTOTAL(9,'.num2alfa($m+3).'5'.':'.num2alfa($m+3).($linha-2).')');
	
	$objPHPExcel->getActiveSheet()->getStyle(num2alfa($m+3).$linha)->getNumberFormat()->setFormatCode("R$ #,##0.00");
	
	$m+=4;
}

//PLANILHA GERENCIAL
$objPHPExcel->setActiveSheetIndex(1);

$numero_coordenadores = count($array_os_coord);

$inicial_metas_coordenadores = 49 + $numero_coordenadores + 4; //soma o inicio das linhas coordenadores + numero de coordenadores + linhas sobressalentes

//insere as linhas (saldo) com a quantidades de coordenadores
$objPHPExcel->getActiveSheet()->insertNewRowBefore(49,$numero_coordenadores-2);

//insere as linhas (metas) com a quantidades de coordenadores
$objPHPExcel->getActiveSheet()->insertNewRowBefore($inicial_metas_coordenadores,($numero_coordenadores*3)-2);

asort($array_os_coord);

//loop coordenadores (saldo)
$linha_coord = 48;

foreach($array_os_coord as $nome_coord)
{
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $linha_coord, iconv('ISO-8859-1', 'UTF-8',$nome_coord));
	
	$linha_coord++;
}

//loop coordenadores (metas)
$linha_coord = ($inicial_metas_coordenadores-2);

foreach($array_os_coord as $nome_coord)
{
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $linha_coord, iconv('ISO-8859-1', 'UTF-8',$nome_coord));
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $linha_coord, iconv('ISO-8859-1', 'UTF-8','PREVISTO'));
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $linha_coord+1, iconv('ISO-8859-1', 'UTF-8','REALIZADO'));
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $linha_coord+2, iconv('ISO-8859-1', 'UTF-8','RESULTADO'));
	
	$linha_coord+=3;
}

$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, 22, $array_medicao['valor_carteira_acumulado']);

$objPHPExcel->getActiveSheet()->getStyle(num2alfa(2).'22')->getNumberFormat()->setFormatCode("R$ #,##0.00");

$coluna_periodos = 4;

$i = $coluna_periodos;

$h = $coluna_periodos;

foreach($array_medicao['periodo'] as $chave=>$valor)
{		
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($i, 1, iconv('ISO-8859-1', 'UTF-8',date("M/Y",strtotime($chave))));
	
	//$celulas .= num2alfa($j+2).$linha.',';
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($i, 5, '=SUM('.num2alfa($i).'3:'.num2alfa($i).'4)');
	
	$objPHPExcel->getActiveSheet()->getStyle(num2alfa($i).'5')->getNumberFormat()->setFormatCode("R$ #,##0.00");
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($i, 8, '=SUM('.num2alfa($i).'6:'.num2alfa($i).'7)');
	
	$objPHPExcel->getActiveSheet()->getStyle(num2alfa($i).'8')->getNumberFormat()->setFormatCode("R$ #,##0.00");
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($i, 9, '=SUM('.num2alfa($i).'5,'.num2alfa($i).'8)');
	
	$objPHPExcel->getActiveSheet()->getStyle(num2alfa($i).'9')->getNumberFormat()->setFormatCode("R$ #,##0.00");		
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($i, 15, $array_medicao['valor_medido_pct'][$valor]);
	
	$objPHPExcel->getActiveSheet()->getStyle(num2alfa($i).'15')->getNumberFormat()->setFormatCode("R$ #,##0.00");
		
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($i, 17, $array_medicao['valor_medido_adm'][$valor]);
	
	$objPHPExcel->getActiveSheet()->getStyle(num2alfa($i).'17')->getNumberFormat()->setFormatCode("R$ #,##0.00");
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($i, 19, '=SUM('.num2alfa($i).'15:'.num2alfa($i).'18)');
	
	$objPHPExcel->getActiveSheet()->getStyle(num2alfa($i).'19')->getNumberFormat()->setFormatCode("R$ #,##0.00");
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($i, 21, $array_medicao['valor_carteira_pct'][$valor]);
	
	$objPHPExcel->getActiveSheet()->getStyle(num2alfa($i).'21')->getNumberFormat()->setFormatCode("R$ #,##0.00");
	
	//$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($i, 22, ($array_medicao['valor_carteira_pct'][$valor] - $array_medicao['valor_carteira_acumulado'][$valor]));
	
	//$objPHPExcel->getActiveSheet()->getStyle(num2alfa($i).'22')->getNumberFormat()->setFormatCode("R$ #,##0.00");
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($i, 24, $array_medicao['num_func_pct'][$valor]);
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($i, 25, $array_medicao['num_func_adm'][$valor]);
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($i, 27, $array_medicao['num_func_out'][$valor]);
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($i, 28, '=SUM('.num2alfa($i).'24:'.num2alfa($i).'27)'); 	

	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($i, 32, $array_medicao['num_func'][12][$valor]); //ebp
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($i, 33, $array_medicao['num_func'][9][$valor]+$array_medicao['num_func'][8][$valor]+$array_medicao['num_func'][23][$valor]); //mec/tub/pdm
		
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($i, 34, $array_medicao['num_func'][14][$valor]+$array_medicao['num_func'][20][$valor]); //civ/est
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($i, 35, $array_medicao['num_func'][13][$valor]); //ele
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($i, 36, $array_medicao['num_func'][7][$valor]+$array_medicao['num_func'][10][$valor]); //ins/aut
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($i, 37, $array_medicao['num_func'][15][$valor]); //cor
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($i, 38, $array_medicao['num_func'][5][$valor]); //pln
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($i, 39, $array_medicao['num_func'][24][$valor]+$array_medicao['num_func'][18][$valor]); //mat/sup
	
	//$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($i, 40, $array_medicao['num_func'][24][$valor]); //sup
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($i, 41, $array_medicao['num_func'][23][$valor]); //pdm
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($i, 42, $array_medicao['num_func'][11][$valor]); //sgi
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($i, 43, $array_medicao['num_func'][6][$valor]); //art
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($i, 45, '=SUM('.num2alfa($i).'32:'.num2alfa($i).'43)');
	
	
	$linha_coord = 48;
	
	//monta as linhas dos saldos dos coordenadores
	foreach($array_os_coord as $cod_coord=>$nome_coord)
	{
		//$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($i, $linha_coord, $array_medicao['valor_medido_coordenador'][sprintf("%04d",$cod_coord)][$valor]);
	
		//$objPHPExcel->getActiveSheet()->getStyle(num2alfa($i).$linha_coord)->getNumberFormat()->setFormatCode("R$ #,##0.00");
	
		//$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $linha_coord, '=SUM(E'.$linha_coord.':'.num2alfa($i).$linha_coord.')');
		
		//=SOMASE('status R$'!D:L;Gerencial!B48;'status R$'!L:L)
		
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $linha_coord, "=SUMIF('status'!D:L,'Gerencial'!B".$linha_coord.",'status'!L:L)");
		
		$objPHPExcel->getActiveSheet()->getStyle(num2alfa($i).$linha_coord)->getNumberFormat()->setFormatCode("R$ #,##0.00");
		
		$linha_coord++;	
	}
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($i, $linha_coord+1, '=SUM('.num2alfa($i).'48:'.num2alfa($i).($linha_coord-1).')');	
	
	$objPHPExcel->getActiveSheet()->getStyle(num2alfa($i).($linha_coord+1))->getNumberFormat()->setFormatCode("R$ #,##0.00");
	
	$linha_coord += 3;	
	
	$celulas_planejado = '';
	
	$celulas_medido = '';
	
	//monta as linhas das metas dos coordenadores
	foreach($array_os_coord as $cod_coord=>$nome_coord)
	{
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($i, $linha_coord, $array_medicao['valor_planejado_coordenador'][sprintf("%04d",$cod_coord)][$valor]);
	
		$objPHPExcel->getActiveSheet()->getStyle(num2alfa($i).$linha_coord)->getNumberFormat()->setFormatCode("R$ #,##0.00");
		
		$celulas_planejado .= num2alfa($i).$linha_coord.',';			

		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($i, $linha_coord+1, $array_medicao['valor_medido_coordenador'][sprintf("%04d",$cod_coord)][$valor]);
	
		$objPHPExcel->getActiveSheet()->getStyle(num2alfa($i).($linha_coord+1))->getNumberFormat()->setFormatCode("R$ #,##0.00");
		
		$celulas_medido .= num2alfa($i).($linha_coord+1).',';
		
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($i, $linha_coord+2, '='.num2alfa($i).($linha_coord+1).'-'.num2alfa($i).($linha_coord));
		
		$objPHPExcel->getActiveSheet()->getStyle(num2alfa($i).($linha_coord+2))->getNumberFormat()->setFormatCode("R$ #,##0.00");
				
		$linha_coord+=3;	
	}
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($i, $linha_coord, '='.num2alfa($i).'3');

	$objPHPExcel->getActiveSheet()->getStyle(num2alfa($i).$linha_coord)->getNumberFormat()->setFormatCode("R$ #,##0.00");
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($i, ($linha_coord+1), '=SUM('.$celulas_planejado.')');

	$objPHPExcel->getActiveSheet()->getStyle(num2alfa($i).($linha_coord+1))->getNumberFormat()->setFormatCode("R$ #,##0.00");
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($i, ($linha_coord+2), '=SUM('.$celulas_medido.')');

	$objPHPExcel->getActiveSheet()->getStyle(num2alfa($i).($linha_coord+2))->getNumberFormat()->setFormatCode("R$ #,##0.00");
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($i, ($linha_coord+3), '='.num2alfa($i).($linha_coord+2).'-'.num2alfa($i).($linha_coord+1));

	$objPHPExcel->getActiveSheet()->getStyle(num2alfa($i).($linha_coord+3))->getNumberFormat()->setFormatCode("R$ #,##0.00");
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($i, ($linha_coord+4), '='.num2alfa($i).($linha_coord+2).'-'.num2alfa($i).($linha_coord));

	$objPHPExcel->getActiveSheet()->getStyle(num2alfa($i).($linha_coord+4))->getNumberFormat()->setFormatCode("R$ #,##0.00");		
	
	$objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($i+1)->setAutoSize(false);
	
	$objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($i+1)->setWidth('1.2');
	
	$i+=2;
	
	$h=$i;
}

$objPHPExcel->setActiveSheetIndex(0);

$objWriter->save('php://output');

exit;
?>
