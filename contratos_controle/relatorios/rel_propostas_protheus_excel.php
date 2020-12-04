<?php
/*
	Relatório de Propostas
	Criado por Carlos Abreu  
	
	Versão 0 --> VERSÃO INICIAL : 19/01/2017
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

$os = $_POST["escolhaos"];

$filtro0 = "";

//filtra a os (-1 TODAS AS OS)
if($os!=-1)
{
	$filtro0 .= "AND AF1_ORCAME = '".$os."' ";		
}

if($_POST["escolhacoord"]!=-1)
{
	$filtro0 .= "AND AF1_COORD1 = '".sprintf("%04d",$_POST["escolhacoord"])."' ";
}

if (!empty($_POST['dataIni']) && !empty($_POST['dataFim']))
{
	$filtro0 .= "AND AF1_DATA BETWEEN '".str_replace('-', '', php_mysql($_POST['dataIni']))."' AND '".str_replace('-', '', php_mysql($_POST['dataFim']))."'";
}

//SELECIONA O COORDENADOR PRINCIPAL
$sql = "SELECT * FROM PA7010 WITH(NOLOCK) ";
$sql .= "WHERE PA7010.D_E_L_E_T_ = '' ";

$db->select($sql,'MSSQL',true);

foreach ($db->array_select as $regs1)
{
	$array_coordenadores[$regs1["PA7_ID"]] = str_replace(array("'", '"', '\"', "\'"),"",$regs1["PA7_NOME"]);	
}

$sql = "SELECT * FROM ".DATABASE.".setores ";
$sql .= "WHERE setores.reg_del = 0 ";

$db->select($sql,'MYSQL',true);

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
$index_coluna_valor = 29;

$index_coluna_esforco = 29;

//Nova consulta
$sql = "SELECT * FROM 
(
	SELECT
		AF1_ORCAME, AF1_CLIENT, AF1_DESCRI, AF1_LOJA, AF1_FASE, AF1_CONVIT,
		AF1_REFCLI, AF1_DESCRE, AF1_VENDED, AF1_TPCLIE,
		AF1_SEGMEN, AF1_RESPTE, AF1_RESPSU, AF1_DTSOLI, AF1_DTENTR,
		AF1_DTENRE, AF1_DTAPRO, AF1_DATA, AF1_EXECU1, AF1_EXECU2,
		AF1_EXECU3, AF1_EXECU4, AF1_COORD1, AF1_COORD2, AF1_OBSERV
	FROM 
		AF1010 WITH(NOLOCK)
	WHERE
		D_E_L_E_T_ = ''
	" . $filtro0 . "
) AF1010
JOIN (
	SELECT
		A1_NOME, A1_COD, A1_MUN, A1_EST, A1_CGC, A1_INSCR, A1_INSCRM, A1_NREDUZ, A1_LOJA
	FROM
		SA1010 WITH(NOLOCK)
	WHERE
		D_E_L_E_T_ = ''
) SA1010
ON A1_COD = AF1_CLIENT AND A1_LOJA = AF1_LOJA
JOIN(
	SELECT
		AE9_COD, AE9_DESCRI
	FROM 
		AE9010 WITH(NOLOCK)
	WHERE
		D_E_L_E_T_ = ''
) AE9010
ON AE9_COD = AF1_FASE

ORDER BY AF1_ORCAME";

$db->select($sql,'MSSQL',true);

$array_projetos = $db->array_select;

foreach($array_projetos as $regs0)
{	
	$array_proj[$regs0["AF1_ORCAME"]] = str_replace(array("'", '"', '\"', "\'"),"",trim($regs0["AF1_DESCRI"]));
	
	$array_proj_status[$regs0["AF1_ORCAME"]] = str_replace(array("'", '"', '\"', "\'"),"",trim($regs0["AE9_DESCRI"]));
	
	$array_convite[$regs0["AF1_ORCAME"]] = str_replace(array("'", '"', '\"', "\'"),"",trim($regs0["AF1_CONVIT"]));
	
	$array_ref_cliente[$regs0["AF1_ORCAME"]] = str_replace(array("'", '"', '\"', "\'"),"",trim($regs0["AF1_REFCLI"]));
	
	$array_desc_cliente[$regs0["AF1_ORCAME"]] = str_replace(array("'", '"', '\"', "\'"),"",trim($regs0["AF1_DESCRE"]));
	
	$array_nome_cliente[$regs0["AF1_ORCAME"]] = str_replace(array("'", '"', '\"', "\'"),"",trim($regs0["A1_NOME"]));
	
	$array_unidade_cliente[$regs0["AF1_ORCAME"]] = str_replace(array("'", '"', '\"', "\'"),"",trim($regs0["A1_MUN"])." - ".trim($regs0["A1_EST"]));
	
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
	
	switch ($regs0["AF1_TPCLIE"])
	{
		case 1:
			$array_tpcliente[$regs0["AF1_ORCAME"]] = 'Novo';
		break;
		
		case 2:
			$array_tpcliente[$regs0["AF1_ORCAME"]] = 'Existente';
		break;
		
		case 3:
			$array_tpcliente[$regs0["AF1_ORCAME"]] = 'Contrato guarda-chuva';
		break;
		
		default:
			$array_tpcliente[$regs0["AF1_ORCAME"]] = '';
	}
	
	$array_segmen[$regs0["AF1_ORCAME"]] = $regs0["AF1_SEGMEN"];	
	
	$array_cnpj_cliente[$regs0["AF1_ORCAME"]] = $regs0["A1_CGC"];
	
	$array_ie_cliente[$regs0["AF1_ORCAME"]] = $regs0["A1_INSCR"];
	
	$array_im_cliente[$regs0["AF1_ORCAME"]] = $regs0["A1_INSCRM"];
	
	$array_nome_fantasia[$regs0["AF1_ORCAME"]] = str_replace(array("'", '"', '\"', "\'"),"",trim($regs0["A1_NREDUZ"]));
	
	$array_resp_tec[$regs0["AF1_ORCAME"]] = str_replace(array("'", '"', '\"', "\'"),"",trim($regs0["AF1_RESPTE"]));
	
	$array_resp_sup[$regs0["AF1_ORCAME"]] = str_replace(array("'", '"', '\"', "\'"),"",trim($regs0["AF1_RESPSU"]));
	
	$array_dt_soli[$regs0["AF1_ORCAME"]] = $regs0["AF1_DTSOLI"];
	
	$array_dt_entrega[$regs0["AF1_ORCAME"]] = $regs0["AF1_DTENTR"];
	
	$array_dt_entrega_real[$regs0["AF1_ORCAME"]] = $regs0["AF1_DTENRE"];
	
	//#742
	$array_dt_aprov[$regs0["AF1_ORCAME"]] = $regs0["AF1_DTAPRO"];
	
	$array_dt_projeto[$regs0["AF1_ORCAME"]] = $regs0["AF1_DATA"];
	
	$array_exec1[$regs0["AF1_ORCAME"]] = str_replace(array("'", '"', '\"', "\'"),"",trim($regs0["AF1_EXECU1"]));
	
	$array_exec2[$regs0["AF1_ORCAME"]] = str_replace(array("'", '"', '\"', "\'"),"",trim($regs0["AF1_EXECU2"]));
	
	$array_exec3[$regs0["AF1_ORCAME"]] = str_replace(array("'", '"', '\"', "\'"),"",trim($regs0["AF1_EXECU3"]));
	
	$array_exec4[$regs0["AF1_ORCAME"]] = str_replace(array("'", '"', '\"', "\'"),"",trim($regs0["AF1_EXECU4"]));
	
	//SELECIONA OS TELEFONES DO RESP TEC 
	$sql = "SELECT * FROM SU5010 WITH(NOLOCK) ";
	$sql .= "WHERE SU5010.D_E_L_E_T_ = '' ";
	$sql .= "AND SU5010.U5_CONTAT = '".str_replace(array("'", '"', '\"', "\'"),"",trim($regs0["AF1_RESPTE"]))."' ";
	
	$db->select($sql,'MSSQL',true);

	$regs3 = $db->array_select[0];	
	
	if($db->numero_registros_ms>0)
	{
	
		$array_resp_tec_tel[$regs0["AF1_ORCAME"]] = trim($regs3["U5_DDDFCO1"])." ".trim($regs3["U5_FCOM1"])." R: ".trim($regs3["U5_RAMAL1"])." / ".trim($regs3["U5_DDDFCO2"])." ".trim($regs3["U5_FCOM2"])." R: ".trim($regs3["U5_RAMAL2"]);
		
		$array_resp_tec_cel[$regs0["AF1_ORCAME"]] = trim($regs3["U5_DDDCELU"])." ".trim($regs3["U5_CELULAR"]);
		
		$array_resp_tec_email[$regs0["AF1_ORCAME"]] = trim($regs3["U5_EMAIL"]);
	}
	else
	{
		$array_resp_tec_tel[$regs0["AF1_ORCAME"]] = "";
		
		$array_resp_tec_cel[$regs0["AF1_ORCAME"]] = "";
		
		$array_resp_tec_email[$regs0["AF1_ORCAME"]] = "";
	}
	
	//SELECIONA OS TELEFONES DO RESP SUP 
	$sql = "SELECT * FROM SU5010 WITH(NOLOCK) ";
	$sql .= "WHERE SU5010.D_E_L_E_T_ = '' ";
	$sql .= "AND SU5010.U5_CONTAT = '".str_replace(array("'", '"', '\"', "\'"),"",trim($regs0["AF1_RESPSU"]))."' ";
	
	$db->select($sql,'MSSQL',true);

	$regs4 = $db->array_select[0];		
	
	if($db->numero_registros_ms>0)
	{	
		$array_resp_sup_tel[$regs0["AF1_ORCAME"]] = trim($regs4["U5_DDDFCO1"])." ".trim($regs4["U5_FCOM1"])." R: ".trim($regs4["U5_RAMAL1"])." / ".trim($regs4["U5_DDDFCO2"])." ".trim($regs4["U5_FCOM2"])." R: ".trim($regs4["U5_RAMAL2"]);
		
		$array_resp_sup_cel[$regs0["AF1_ORCAME"]] = trim($regs4["U5_DDDCELU"])." ".trim($regs4["U5_CELULAR"]);
		
		$array_resp_sup_email[$regs0["AF1_ORCAME"]] = trim($regs4["U5_EMAIL"]);
	}
	else
	{
		$array_resp_sup_tel[$regs0["AF1_ORCAME"]] = "";
		
		$array_resp_sup_cel[$regs0["AF1_ORCAME"]] = "";
		
		$array_resp_sup_email[$regs0["AF1_ORCAME"]] = "";	
	}
	
	$array_coord_princ[$regs0["AF1_ORCAME"]] = $array_coordenadores[$regs0["AF1_COORD1"]];
	
	$array_coord_aux[$regs0["AF1_ORCAME"]] = $array_coordenadores[$regs0["AF1_COORD2"]];
	
	$array_projet_obs[$regs0["AF1_ORCAME"]] = str_replace(array("'", '"', '\"', "\'"),"",trim($regs0["AF1_OBSERV"]));	

	$sql = "SELECT AF2_GRPCOM, SUM(AF2_QUANT) AS TOTALH, SUM(AF2_TOTAL) AS TOTALC FROM AF2010 WITH(NOLOCK) ";
	$sql .= "WHERE AF2010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF2010.AF2_ORCAME = '".$regs0["AF1_ORCAME"]."' ";
	
	if(count($setor_d)>0)
	{
		$sql .= "AND AF2010.AF2_GRPCOM IN (".$filtro_setor.") ";
	}
	
	$sql .= "GROUP BY AF2_GRPCOM ";
	$sql .= "ORDER BY AF2_GRPCOM ";

	$db->select($sql,'MSSQL',true);	

	foreach ($db->array_select as $regs7)
	{
		$array_horas[$regs0["AF1_ORCAME"]][$regs7["AF2_GRPCOM"]] = $regs7["TOTALH"];
		
		$array_horas_tot[$regs0["AF1_ORCAME"]] += $regs7["TOTALH"];
		
		$array_custo[$regs0["AF1_ORCAME"]][$regs7["AF2_GRPCOM"]] = $regs7["TOTALC"];
		
		$array_custo_tot[$regs0["AF1_ORCAME"]] += $regs7["TOTALC"];
		
		//usado para montar o cabe�alho das disciplinas x valor		
		if(!array_key_exists($regs7["AF2_GRPCOM"],$array_disciplinas_valor))
		{
			$array_disciplinas_valor[$regs7["AF2_GRPCOM"]] = $index_coluna_valor;
		
			$index_coluna_valor++;
		}		
	}	
	
	//#742
	$sql = "SELECT AFE_FASE FROM AFE010 WITH(NOLOCK) ";
	$sql .= "WHERE AFE010.D_E_L_E_T_ = '' ";
	$sql .= "AND AFE010.AFE_PROJET = '".$regs0["AF1_ORCAME"]."' ";
	$sql .= "AND AFE010.AFE_REVISA = '0003' ";
	
	$db->select($sql,'MSSQL',true);
	
	foreach($db->array_select as $regs8)
	{
		$array_fase[$regs0["AF1_ORCAME"]] = $regs8["AFE_FASE"];
	}
}	

$disc = array_keys($array_disciplinas_valor);

$var = array_values($array_disciplinas_valor);

sort($disc);

sort($var);

foreach($disc as $key=>$val)
{
	$array_disciplinas_val[$val] = $var[$key];
}

$objPHPExcel = PHPExcel_IOFactory::load("../modelos_excel/propostas_modelo.xls");

$locale = 'pt_br';

$validlocale = PHPExcel_Settings::setlocale($locale);

if (!$validlocale) 
{
	echo 'Unable to set locale to '.$locale." - reverting to en_us<br />\n";
}

// Redirect output to a client�s web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="planilha_vendas_'.date('Ymd').'.xlsx"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

$objPHPExcel->setActiveSheetIndex(0);

//COLUNA A EXCELL
$coluna = 0;

$linha = 4;

//acrescenta as colunas conforme disciplinas (Horas)
$objPHPExcel->getActiveSheet()->insertNewColumnBefore('AF',count($array_disciplinas_valor));

$num_colunas = alfa2num('AF');

//salta as colunas totalizadoras
$num_colunas += count($array_disciplinas_valor)+3;

//acrescenta as colunas conforme disciplinas (Esfor�o)
$objPHPExcel->getActiveSheet()->insertNewColumnBefore(num2alfa($num_colunas),count($array_disciplinas_valor));

//acrescenta linhas conforme quantidade de projetos
$objPHPExcel->getActiveSheet()->insertNewRowBefore(5,count($array_proj));
foreach($array_proj as $projeto=>$descricao)
{
	//A
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna, $linha, iconv('ISO-8859-1', 'UTF-8',$array_nome_cliente[$projeto]));
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+1, $linha, iconv('ISO-8859-1', 'UTF-8',$array_unidade_cliente[$projeto]));
	//B
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+2, $linha, iconv('ISO-8859-1', 'UTF-8',$array_tpcliente[$projeto]));
	//C
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+3, $linha, iconv('ISO-8859-1', 'UTF-8',$array_segmen[$projeto]));
	//D
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+4, $linha, iconv('ISO-8859-1', 'UTF-8',$array_nome_fantasia[$projeto]));
	//E
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+5, $linha, $projeto);
	//F
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+6, $linha, iconv('ISO-8859-1', 'UTF-8',$descricao));
	//G
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+7, $linha, iconv('ISO-8859-1', 'UTF-8',$array_vendedor[$projeto]));
	//H
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+8, $linha, iconv('ISO-8859-1', 'UTF-8',$array_coord_princ[$projeto]));
	//I
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+9, $linha, iconv('ISO-8859-1', 'UTF-8',$array_coord_aux[$projeto]));
	//J
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+10, $linha, iconv('ISO-8859-1', 'UTF-8',$array_convite[$projeto]));
	//K
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+11, $linha, iconv('ISO-8859-1', 'UTF-8',$array_ref_cliente[$projeto]));
	//L
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+12, $linha, iconv('ISO-8859-1', 'UTF-8',$array_desc_cliente[$projeto]));
	//M
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+13, $linha, iconv('ISO-8859-1', 'UTF-8',$array_resp_sup[$projeto]));
	//N
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+14, $linha, $array_resp_sup_tel[$projeto]);
	//O
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+15, $linha, $array_resp_sup_cel[$projeto]);
	//P
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+16, $linha, iconv('ISO-8859-1', 'UTF-8',$array_resp_sup_email[$projeto]));
	//Q
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+17, $linha, iconv('ISO-8859-1', 'UTF-8',$array_resp_tec[$projeto]));
	//R
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+18, $linha, $array_resp_tec_tel[$projeto]);
	//S	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+19, $linha, $array_resp_tec_cel[$projeto]);
	//T
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+20, $linha, iconv('ISO-8859-1', 'UTF-8',$array_resp_tec_email[$projeto]));
	//U
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+21, $linha, $array_proj_status[$projeto]);
	//V
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+22, $linha, mysql_php(protheus_mysql($array_dt_soli[$projeto])));
	//W
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+23, $linha, mysql_php(protheus_mysql($array_dt_entrega[$projeto])));
	//X
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+24, $linha, mysql_php(protheus_mysql($array_dt_entrega_real[$projeto])));
	//Y
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+25, $linha, mysql_php(protheus_mysql($array_dt_aprov[$projeto])));
	//Z
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+26, $linha, mysql_php(protheus_mysql($array_dt_projeto[$projeto])));
	//AA
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+27, $linha, iconv('ISO-8859-1', 'UTF-8',$array_exec1[$projeto]));
	//AB
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+28, $linha, iconv('ISO-8859-1', 'UTF-8',$array_exec2[$projeto]));
	//AC
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+29, $linha, iconv('ISO-8859-1', 'UTF-8',$array_exec3[$projeto]));
	//AD
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+30, $linha, iconv('ISO-8859-1', 'UTF-8',$array_exec4[$projeto]));
	
	$i = 0;
	
	foreach ($array_disciplinas_val as $disciplinas=>$index)
	{
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+2+$index, 3, iconv('ISO-8859-1', 'UTF-8',$disciplinas));
		
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+2+$index, $linha, $array_custo[$projeto][$disciplinas]);
		
		//esfor�o
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+$num_colunas+$i, 3, iconv('ISO-8859-1', 'UTF-8',$disciplinas));
		
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+$num_colunas+$i, $linha, $array_horas[$projeto][$disciplinas]);
		
		$i++;
	
		$ult_elem_h = $index;
		
		$ult_elem_e = $num_colunas+$i;	
	}	
	
	//horas
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+$ult_elem_h+4, $linha, $array_custo_tot[$projeto]);
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+$ult_elem_e, $linha, $array_horas_tot[$projeto]);

	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+$ult_elem_e+1, $linha, $array_cnpj_cliente[$projeto]);
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+$ult_elem_e+2, $linha, $array_ie_cliente[$projeto]);
		
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+$ult_elem_e+3, $linha, $array_im_cliente[$projeto]);
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+$ult_elem_e+4, $linha, iconv('ISO-8859-1', 'UTF-8',$array_projet_obs[$projeto]));
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+$ult_elem_e+5, $linha, $array_fase[$projeto]);
	
	$linha++;	
}

$objWriter->save('php://output');

exit;
?>