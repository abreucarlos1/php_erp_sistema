<?php
/*
	Relatório de Vendas Negociando
	Criado por Carlos Abreu  
	
	Versão 0 --> VERSÃO INICIAL : 19/01/2015
	Versão 1 --> Inclusão dos campos reg_del nas consultas - 17/11/2017 - Carlos Abreu
*/

ini_set('max_execution_time', 0); // No time limit
ini_set('post_max_size', '20M');
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

//SELECIONA O COORDENADOR PRINCIPAL
$sql = "SELECT * FROM PA7010 WITH(NOLOCK) ";
$sql .= "WHERE PA7010.D_E_L_E_T_ = '' ";

$db->select($sql,'MSSQL', true);

$array_coordenadores = array();

foreach($db->array_select as $regs1)
{
	$array_coordenadores[$regs1["PA7_ID"]] = str_replace(array("'", '"', '\"', "\'"),"",$regs1["PA7_NOME"]);	
}

$os = $_POST["escolhaos"];

$filtro0 = "";

if($os!=-1)
{
	$filtro0 .= "AND AF1_ORCAME = '".$os."' ";		
}

if($_POST["escolhacoord"]!=-1)
{
	$filtro0 .= "AND AF1_COORD1 = '".sprintf("%04d",$_POST["escolhacoord"])."' ";
}

$sql = "SELECT * FROM ".DATABASE.".setores ";
$sql .= "WHERE setores.reg_del = 0 ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die('Erro ao selecionar os dados. '.$sql);
}
	
foreach ($db->array_select as $regs)
{
	if($_POST["chk_".$regs["id_setor"]]==1)
	{
		$setor[] = "'".$regs["abreviacao"]."'";
		$setor_d[] = "'".tiraacentos($regs["setor"])."'";
		$setor_desc[$regs["abreviacao"]] = tiraacentos($regs["setor"]);
	}	
}

$filtro_setor = implode(",",$setor);

$filtro_setor_d = implode(",",$setor_d);

//inicio das colunas de disciplinas
$index_coluna_valor = 30;

$index_coluna_esforco = 30;

//Seleciona as OSs
$sql = "SELECT * FROM AF1010 WITH(NOLOCK), SA1010 WITH(NOLOCK), AE9010 WITH(NOLOCK) ";
$sql .= "WHERE AF1010.D_E_L_E_T_ = '' ";
$sql .= "AND SA1010.D_E_L_E_T_ = '' ";
$sql .= "AND AE9010.D_E_L_E_T_ = '' ";
$sql .= "AND AF1_CLIENT = A1_COD ";
$sql .= "AND AF1_LOJA = A1_LOJA ";
$sql .= "AND AF1_FASE = AE9_COD ";
$sql .= "AND AF1_FASE IN ('06') ";
$sql .= "AND AF1_ORCAME > 0000003000 ";
$sql .= $filtro0;
$sql .= "ORDER BY AF1_ORCAME ";	

$db->select($sql,'MSSQL', true);

if($db->erro!='')
{
	die('Erro ao selecionar os dados. '.$sql);
}

if ($db->numero_registros_ms == 0)
{
	echo '<script>alert("N�o foram encontradas informa��es para gerar o Relatório");window.close();</script>';
	exit;
}

$array_orc = $db->array_select;

foreach($array_orc as $regs0)
{	
	$array_proj[$regs0["AF1_ORCAME"]] = str_replace(array("'", '"', '\"', "\'"),"",$regs0["AF1_DESCRI"]);
	
	$array_proj_status[$regs0["AF1_ORCAME"]] = str_replace(array("'", '"', '\"', "\'"),"",$regs0["AE9_DESCRI"]);
	
	$array_desc_cliente[$regs0["AF1_ORCAME"]] = str_replace(array("'", '"', '\"', "\'"),"",$regs0["AF1_DESCRE"]);
	
	$array_nome_cliente[$regs0["AF1_ORCAME"]] = str_replace(array("'", '"', '\"', "\'"),"",$regs0["A1_NOME"]);
	
	$array_unidade_cliente[$regs0["AF1_ORCAME"]] = str_replace(array("'", '"', '\"', "\'"),"",trim($regs0["A1_MUN"])." - ".trim($regs0["A1_EST"]));
	
	$array_nome_fantasia[$regs0["AF1_ORCAME"]] = str_replace(array("'", '"', '\"', "\'"),"",$regs0["A1_NREDUZ"]);
	
	$array_nome_respte[$regs0["AF1_ORCAME"]] = str_replace(array("'", '"', '\"', "\'"),"",$regs0["AF1_RESPTE"]);
	
	$array_nome_respsu[$regs0["AF1_ORCAME"]] = str_replace(array("'", '"', '\"', "\'"),"",$regs0["AF1_RESPSU"]);
	
	$array_dt_soli[$regs0["AF1_ORCAME"]] = $regs0["AF1_DTSOLI"];
	
	$array_dt_entrega[$regs0["AF1_ORCAME"]] = $regs0["AF1_DTENTR"];
	
	$array_dt_entrega_real[$regs0["AF1_ORCAME"]] = $regs0["AF1_DTENRE"];
	
	switch ($regs0["AF1_ACAO"])
	{
		case 0:
			$array_acao[$regs0["AF1_ORCAME"]] = 'Novo Or�amento';
		break;
		
		case 1:
			$array_acao[$regs0["AF1_ORCAME"]] = 'Atualizar status';
		break;
		
		case 2:
			$array_acao[$regs0["AF1_ORCAME"]] = 'Fazer Follow-up';
		break;
		
		case 3:
			$array_acao[$regs0["AF1_ORCAME"]] = 'Manter Follow-up';
		break;
		
		case 4:
			$array_acao[$regs0["AF1_ORCAME"]] = 'Atualizar Or�amento';
		break;
		
		case 5:
			$array_acao[$regs0["AF1_ORCAME"]] = 'Retirar da lista';
		break;
		
		default:
			$array_acao[$regs0["AF1_ORCAME"]] = 'Sem status';							

	}
	
	switch ($regs0["AF1_VENDED"])
	{
		case 1:
			$array_vendedor[$regs0["AF1_ORCAME"]] = 'Leonardo Oca';
		break;
		
		case 2:
			$array_vendedor[$regs0["AF1_ORCAME"]] = 'Fl�vio Freitas';
		break;
		
		case 3:
			$array_vendedor[$regs0["AF1_ORCAME"]] = 'Contrato guarda-chuva';
		break;
		
		default:
			$array_vendedor[$regs0["AF1_ORCAME"]] = '';							

	}		

	$array_coord_princ[$regs0["AF1_ORCAME"]] = $array_coordenadores[$regs0["AF1_COORD1"]];

	//SELECIONA O COORDENADOR CLIENTE
	//incluido em 19/05/2014 #461
	//vivian
	$sql = "SELECT * FROM SU5010 WITH(NOLOCK) ";
	$sql .= "WHERE SU5010.D_E_L_E_T_ = '' ";
	$sql .= "AND SU5010.U5_CONTAT = '".$regs0["AF1_RESPTE"]."' ";

	$db->select($sql,'MSSQL', true);
	
	if($db->erro!='')
	{
		die('Erro ao selecionar os dados. '.$sql);
	}

	$regs2 = $db->array_select[0];
	
	$array_respte_tel1[$regs0["AF1_ORCAME"]] = $regs2["U5_DDDFCO1"]." - ".$regs2["U5_FCOM1"];
	$array_respte_email[$regs0["AF1_ORCAME"]] = $regs2["U5_EMAIL"];

	//TABELA AFC - ESFOR�O E LINHA DE BASE
	$sql = "SELECT * FROM AF5010 WITH(NOLOCK) ";
	$sql .= "WHERE AF5010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF5010.AF5_ORCAME = '".$regs0["AF1_ORCAME"]."' ";

	$db->select($sql,'MSSQL', true);
	
	if($db->erro!='')
	{
		die('Erro ao selecionar os dados. '.$sql);
	}

	$regs6 = $db->array_select[0];
	
	$array_projet_custo[$regs0["AF1_ORCAME"]] = $regs6["AF5_TOTAL"];
	
	if($regs0["AF1_CODMEM"]!='')
	{
		//TABELA SYP - CAMPO OBSERVA��O
		$sql = "SELECT * FROM SYP010 WITH(NOLOCK) ";
		$sql .= "WHERE SYP010.D_E_L_E_T_ = '' ";
		$sql .= "AND SYP010.YP_CAMPO = 'AF1_CODMEM' ";
		$sql .= "AND SYP010.YP_CHAVE = '".$regs0["AF1_CODMEM"]."' ";
		$sql .= "ORDER BY SYP010.YP_SEQ ";

		$db->select($sql,'MSSQL', true);
		
		if($db->erro!='')
		{
			die('Erro ao selecionar os dados. '.$sql);
		}
		
		foreach($db->array_select as $regs7)
		{
			$array_obs[$regs0["AF1_ORCAME"]] .= str_replace(array("'", '"', '\"', "\'"),"",$regs7["YP_TEXTO"]);
		}		
	}		
}	

$objPHPExcel = PHPExcel_IOFactory::load("../modelos_excel/negociando_modelo.xls");

$locale = 'pt_br';

$validlocale = PHPExcel_Settings::setlocale($locale);

if (!$validlocale) 
{
	echo 'Unable to set locale to '.$locale." - reverting to en_us<br />\n";
}

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

$objPHPExcel->setActiveSheetIndex(0);

//COLUNA A EXCELL
$coluna = 0;
$linha = 4;

//acrescenta linhas conforme quantidade de projetos
$objPHPExcel->getActiveSheet()->insertNewRowBefore(5,count($array_proj));

if (count($array_proj) == 0)
{
	echo '<script>alert("N�o foram encontradas informa��es para gerar o Relatório");history.back(1);</script>';
}

foreach($array_proj as $projeto=>$descricao)
{
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna, $linha, iconv('ISO-8859-1', 'UTF-8',$array_coord_princ[$projeto]));
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+1, $linha, iconv('ISO-8859-1', 'UTF-8',$array_vendedor[$projeto]));
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+2, $linha, iconv('ISO-8859-1', 'UTF-8',$array_nome_cliente[$projeto]));

	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+3, $linha, iconv('ISO-8859-1', 'UTF-8',$array_unidade_cliente[$projeto]));

	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+4, $linha, iconv('ISO-8859-1', 'UTF-8',$array_nome_fantasia[$projeto]));
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+5, $linha, iconv('ISO-8859-1', 'UTF-8',$array_nome_respte[$projeto]));
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+6, $linha, iconv('ISO-8859-1', 'UTF-8',$array_respte_tel1[$projeto]));
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+7, $linha, iconv('ISO-8859-1', 'UTF-8',$array_respte_email[$projeto]));
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+8, $linha, iconv('ISO-8859-1', 'UTF-8',$array_nome_respsu[$projeto]));
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+9, $linha, $projeto);
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+10, $linha, iconv('ISO-8859-1', 'UTF-8',$descricao));
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+11, $linha, $array_proj_status[$projeto]);

	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+12, $linha, mysql_php(protheus_mysql($array_dt_soli[$projeto])));
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+13, $linha, mysql_php(protheus_mysql($array_dt_entrega[$projeto])));

	if($array_dt_entrega[$projeto]<date('Ymd'))
	{
		$objPHPExcel->getActiveSheet()->getStyle("N".($linha).":N".($linha))->getFill()->applyFromArray(array('type'=> PHPExcel_Style_Fill::FILL_SOLID,'startcolor' => array('rgb' => 'FF0000'),));
	}
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+14, $linha, mysql_php(protheus_mysql($array_dt_entrega_real[$projeto])));

	//horas
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+15, $linha, $array_projet_custo[$projeto]);

	//esfor�o	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+16, $linha, $array_hesf[$projeto]);

	//acao	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+17, $linha, iconv('ISO-8859-1', 'UTF-8',$array_acao[$projeto]));

	//obs	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+18, $linha, iconv('ISO-8859-1', 'UTF-8',$array_obs[$projeto]));

	$linha+=1;	
}	

// Redirect output to a client's web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="negociando_"'.date('dmYHis').'".xlsx"');
header('Cache-Control: max-age=0');

$objWriter->save('php://output');

exit;
?>