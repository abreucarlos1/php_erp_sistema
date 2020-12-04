<?php
/*
	Relatorio Movimento funcionarios
	
	Criado por Carlos Abreu
	
	local/Nome do arquivo:
	../rh/relatorios/rel_movimento_funcionarios_excel.php
	
	Versão 0 --> VERSÃO INICIAL - 04/05/2006
	Versão 1 --> Inclusão dos campos reg_del nas consultas - 27/11/2017 - Carlos Abreu
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

$filtro = "";
$filtro0 = '';
$filtro1 = '';
$filtro2 = '';

$sep	= "";

if($_POST["intervalo"]=='1')
{
	$filtro0	= "AND funcionarios.data_inicio BETWEEN '".php_mysql($_POST["dataini"])."' AND '".php_mysql($_POST["datafim"])."' ";
	$filtro 	.= "funcionarios.data_inicio BETWEEN '".php_mysql($_POST["dataini"])."' AND '".php_mysql($_POST["datafim"])."' ";
	
	$sep 		 = "AND ";
	
	$filtro1 	= "AND salarios.data BETWEEN '".php_mysql($_POST["dataini"])."' AND '".php_mysql($_POST["datafim"])."' ";
	$filtro2 	= "AND funcionarios.data_desligamento BETWEEN '".php_mysql($_POST["dataini"])."' AND '".php_mysql($_POST["datafim"])."' ";
	$filtro3	= "WHERE dataLimite BETWEEN '".php_mysql($_POST["dataini"])."' AND '".php_mysql($_POST["datafim"])."' ";
}

$filtroCondContr = "";
if (isset($_POST['situacao']) && !empty($_POST['situacao']))
{
	$filtro		.= $sep."situacao = '".trim($_POST['situacao'])."' ";
	$filtroCondContr = "AND situacao = '".trim($_POST['situacao'])."' ";
}

$filtro = !empty($filtro) ? 'AND '.$filtro : '';
$ordenacao = $_POST["ordenacao"];

//SELECIONA AS CONTRATAÇÕES
$sql = "SELECT * FROM ".DATABASE.".salarios, ".DATABASE.".rh_funcoes, ".DATABASE.".funcionarios ";
$sql .= "LEFT JOIN ".DATABASE.".empresa_funcionarios ON (empresa_funcionarios.id_empfunc = funcionarios.id_empfunc AND empresa_funcionarios.reg_del = 0) ";
$sql .= "WHERE salarios.id_funcionario = funcionarios.id_funcionario ";
$sql .= "AND rh_funcoes.reg_del = 0 ";
$sql .= "AND salarios.reg_del = 0 ";
$sql .= "AND funcionarios.reg_del = 0 ";
$sql .= $filtro;
$sql .= "AND funcionarios.id_funcao = rh_funcoes.id_funcao ";
$sql .= "GROUP BY funcionarios.id_funcionario ";
$sql .= "ORDER BY ".$ordenacao;

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}

$res = $db->array_select;

//pega todos os centros de custo (PROTHEUS)
$sql = "SELECT DISTINCT CTT_CUSTO, CTT_DESC01 FROM CTT010 ";
$sql .= "WHERE D_E_L_E_T_ = '' ORDER BY CTT_CUSTO";

$cc = array();

$res2 = $db->select($sql,'MSSQL', true);

foreach($db->array_select as $reg)
{
	$cc[trim($reg['CTT_CUSTO'])] = trim($reg['CTT_CUSTO']).' - '.trim($reg['CTT_DESC01']);
}

$arrayOrdenacao = array();

foreach($res as $regs)
{
	$regs2 = $cc[trim($regs["id_centro_custo"])];
	
	$array_cont_func[$regs["id_funcionario"]] = $regs["funcionario"];
	$array_cont_cargo[$regs["id_funcionario"]] = $regs["descricao"];
	$array_cont_centrocusto[$regs["id_funcionario"]] = $regs2;
	$array_cont_contrato[$regs["id_funcionario"]] = $regs[" tipo_contrato"];
	$array_cont_salclt[$regs["id_funcionario"]] = $regs["salario_clt"];
	$array_cont_salmen[$regs["id_funcionario"]] = $regs["salario_mensalista"];
	$array_cont_salhora[$regs["id_funcionario"]] = $regs["salario_hora"];
	$array_cont_empfunc[$regs["id_funcionario"]] = $regs["empresa_func"];
	$array_cont_dataini[$regs["id_funcionario"]] = $regs["data_inicio"];	
	$arrayOrdenacao[] = $regs['id_funcionario'];
}

//SELECIONA AS ALTERAÇÕES TARIFA
$sql = "SELECT * FROM ".DATABASE.".funcionarios	";
$sql .= "LEFT JOIN ".DATABASE.".rh_funcoes ON funcionarios.id_funcao = rh_funcoes.id_funcao AND rh_funcoes.reg_del = 0 ";
$sql .= "LEFT JOIN(
		  SELECT * FROM ".DATABASE.".empresa_funcionarios WHERE empresa_funcionarios.reg_del = 0
		) empresa_funcionarios
		ON empresa_funcionarios.id_empfunc = funcionarios.id_empfunc ";
$sql .= "WHERE funcionarios.reg_del = 0 ";
$sql .= $filtro0;
$sql .= "GROUP BY funcionarios.id_funcionario ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}

$array_func = $db->array_select;

foreach($array_func as $regs)
{
	$regs2 = $cc[trim($regs["id_centro_custo"])];
	
	$array_alt_func[$regs["id_funcionario"]] = $regs["funcionario"];
	$array_alt_cargo[$regs["id_funcionario"]] = $regs["descricao"];
	$array_alt_centrocusto[$regs["id_funcionario"]] = $regs2;
	
	//PEGA Os salarios
	$sql = "SELECT * FROM ".DATABASE.".salarios ";
	$sql .= "WHERE salarios.id_funcionario = '" . $regs["id_funcionario"] . "' ";
	$sql .= "AND salarios.reg_del = 0 ";
	$sql .= $filtro1;
	$sql .= "ORDER BY id_salario ASC, data ASC ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		die($db->erro);
	}
	
	$reg1 = $db->array_select;
	
	$i = 0;
	
	foreach($reg1 as $regs1)
	{
		$array_alt_contrato[$regs["id_funcionario"]][$i] = $regs1[" tipo_contrato"];
		$array_alt_salclt[$regs["id_funcionario"]][$i] = $regs1["salario_clt"];
		$array_alt_salmen[$regs["id_funcionario"]][$i] = $regs1["salario_mensalista"];
		$array_alt_salhora[$regs["id_funcionario"]][$i] = $regs1["salario_hora"];
		$array_alt_dataalt[$regs["id_funcionario"]][$i] = $regs1["data"];
	
		$i++;
	}
	
	$array_alt_empfunc[$regs["id_funcionario"]] = $regs["empresa_func"];	
}

//SELECIONA OS DESLIGAMENTOS
$sql = "SELECT * FROM ".DATABASE.".funcionarios, ".DATABASE.".rh_funcoes ";
$sql .= "WHERE funcionarios.id_funcao = rh_funcoes.id_funcao ";
$sql .= "AND funcionarios.reg_del = 0 ";
$sql .= "AND rh_funcoes.reg_del = 0 ";
$sql .= "AND funcionarios.data_desligamento <> '0000-00-00' ";
$sql .= $filtro2;
$sql .= "GROUP BY funcionarios.id_funcionario ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}

$array_func = $db->array_select;

foreach($array_func as $regs)
{
	//PEGA O ÚLTIMO SALÁRIO E CONTRATO
	$sql = "SELECT * FROM ".DATABASE.".salarios ";
	$sql .= "WHERE salarios.id_funcionario = '" . $regs["id_funcionario"] . "' ";
	$sql .= "AND salarios.reg_del = 0 ";
	$sql .= "ORDER BY id_salario DESC, data DESC LIMIT 1 ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		die($db->erro);
	}
	
	$regs1 = $db->array_select[0];
	
	$regs2 = $cc[trim($regs["id_centro_custo"])];
	
	//PEGA A EMPRESA DO FUNCIONÁRIO
	$sql = "SELECT * FROM ".DATABASE.".empresa_funcionarios ";
	$sql .= "WHERE empresa_funcionarios.id_empfunc = '" . $regs["id_empfunc"] . "' ";
	$sql .= "AND empresa_funcionarios.reg_del = 0 ";
	$sql .= "ORDER BY id_empfunc DESC LIMIT 1 ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		die($db->erro);
	}
	
	$regs3 = $db->array_select[0];
		
	$array_des_func[$regs["id_funcionario"]] = $regs["funcionario"];
	$array_des_cargo[$regs["id_funcionario"]] = $regs["descricao"];
	$array_des_centrocusto[$regs["id_funcionario"]] = $regs2;
	$array_des_contrato[$regs["id_funcionario"]] = $regs1[" tipo_contrato"];
	$array_des_salclt[$regs["id_funcionario"]] = $regs1["salario_clt"];
	$array_des_salmen[$regs["id_funcionario"]] = $regs1["salario_mensalista"];
	$array_des_salhora[$regs["id_funcionario"]] = $regs1["salario_hora"];
	$array_des_empfunc[$regs["id_funcionario"]] = $regs3["empresa_func"];
	$array_des_datades[$regs["id_funcionario"]] = $regs["data_desligamento"];
}

$objPHPExcel = PHPExcel_IOFactory::load("../modelos_excel/movimento_funcionarios_modelo.xls");

$locale = 'pt_br';

$validlocale = PHPExcel_Settings::setlocale($locale);

if (!$validlocale) 
{
	echo 'Unable to set locale to '.$locale." - reverting to en_us<br />\n";
}

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

$objPHPExcel->setActiveSheetIndex(0);

$coluna = 0;
$linha = 3;

foreach($array_cont_func as $funcionario=>$nome)
{
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $linha, $nome);
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $linha, $array_cont_cargo[$funcionario]);	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $linha, $array_cont_centrocusto[$funcionario]);
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $linha, $array_cont_contrato[$funcionario]);	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, $linha, $array_cont_empfunc[$funcionario]);	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, $linha, mysql_php($array_cont_dataini[$funcionario]));
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $linha, $array_cont_salclt[$funcionario]);
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, $linha, $array_cont_salmen[$funcionario]);
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8, $linha, $array_cont_salhora[$funcionario]);

	$linha++;
}

$objPHPExcel->setActiveSheetIndex(1);

//COLUNA A EXCELL
$coluna = 0;

$linha = 3;

foreach($array_alt_func as $funcionario=>$nome)
{
	if (isset($array_alt_func[$funcionario]))
	{
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $linha, $nome);
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $linha, $array_alt_cargo[$funcionario]);	
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $linha, $array_alt_centrocusto[$funcionario]);
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $linha, $array_alt_empfunc[$funcionario]);
		
		$linha++;
		
		foreach($array_alt_contrato[$funcionario] as $index=>$valor)
		{	
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, $linha, $array_alt_contrato[$funcionario][$index]);
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, $linha, mysql_php($array_alt_dataalt[$funcionario][$index]));
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $linha, $array_alt_salclt[$funcionario][$index]);
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, $linha, $array_alt_salmen[$funcionario][$index]);
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8, $linha, $array_alt_salhora[$funcionario][$index]);
			
			$linha++;
		}
		
		$linha++;
	}
}

$objPHPExcel->setActiveSheetIndex(2);

//COLUNA A EXCELL
$coluna = 0;

$linha = 3;

foreach($array_des_func as $funcionario=>$nome)
{	
	if (!isset($array_des_datades[$funcionario]))
		continue;
		
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $linha, $nome);
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $linha, $array_des_cargo[$funcionario]);	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $linha, $array_des_centrocusto[$funcionario]);
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $linha, $array_des_contrato[$funcionario]);	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, $linha, $array_des_empfunc[$funcionario]);	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, $linha, mysql_php($array_des_datades[$funcionario]));
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $linha, $array_des_salclt[$funcionario]);
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, $linha, $array_des_salmen[$funcionario]);
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8, $linha, $array_des_salhora[$funcionario]);

	$linha++;
}

/*Alterações pedidas  em 03/2015*/
$sql = 
"SELECT *
FROM (
SELECT funcionario, id_funcionario, id_local localAtual, id_centro_custo FROM ".DATABASE.".funcionarios WHERE funcionarios.reg_del = 0 ".$filtroCondContr."
) funcionarios
JOIN (
	SELECT
	  id_funcionario codFun, refeicaoId, transporteId, hotelId, data_inicio data_inicio_contrato, data_fim data_fim_contrato, id_local_trabalho, numero_contrato_cliente, numero_os,
	  descRefeicao, descTransporte, descHotel, cliente_exigencias.data_del dataLimite, Descricaolocal, centroCusto, DescricaoOs
	FROM ".DATABASE.".cliente_exigencias
	LEFT JOIN(
			SELECT id_adicional refeicaoId, rh_adicional descRefeicao FROM ".DATABASE.".rh_adicional WHERE rh_adicional.reg_del = 0
		  ) refeicao
		  ON refeicaoId = id_adicional_refeicao

	LEFT JOIN(
			SELECT id_adicional transporteId, rh_adicional descTransporte FROM ".DATABASE.".rh_adicional WHERE rh_adicional.reg_del = 0
		  ) transporte
		  ON transporteId = id_adicional_transporte
	LEFT JOIN(
			SELECT id_adicional hotelId, rh_adicional descHotel FROM ".DATABASE.".rh_adicional WHERE rh_adicional.reg_del = 0
		  ) hotel
		  ON hotelId = id_adicional_hotel
	LEFT JOIN (
	  SELECT id_local, descricao Descricaolocal FROM ".DATABASE.".local WHERE local.reg_del = 0 
	) locais
	ON locais.id_local = id_local_trabalho
	LEFT JOIN (
		SELECT id_os, descricao descricaoOs, os FROM ".DATABASE.".ordem_servico WHERE ordem_servico.reg_del = 0 
	) OS
	ON OS.id_os = numero_os
) condicoesContratuais ON codFun = funcionarios.id_funcionario
JOIN (
SELECT id_local, descricao DescricaolocalAtual FROM ".DATABASE.".local WHERE local.reg_del = 0 
) localAtual
ON localAtual.id_local = localAtual
".$filtro3."
ORDER BY
	funcionario, data_inicio_contrato";

$db->select($sql, 'MYSQL', true);

$arrColunas = array();
$i = 0;
foreach($db->array_select as $regs)
{
	$ccAlteracao = $cc[trim($regs["centroCusto"])];
	
	$arrColunas[$regs['id_funcionario']][$i]['A'] = $regs['funcionario'];
	$arrColunas[$regs['id_funcionario']][$i]['B'] = $cc[trim($regs["id_centro_custo"])];
	$arrColunas[$regs['id_funcionario']][$i]['C'] = $regs['Descricaolocal'];
	$arrColunas[$regs['id_funcionario']][$i]['D'] = $regs['numero_os'] > 0 ? sprintf('%06s', $regs['numero_os']).' - '.$regs['DescricaoOs'] : '';
	$arrColunas[$regs['id_funcionario']][$i]['E'] = mysql_php(substr($regs['dataLimite'], 0, 10));
	$arrColunas[$regs['id_funcionario']][$i]['F'] = $regs['descHotel'];
	$arrColunas[$regs['id_funcionario']][$i]['G'] = $regs['descRefeicao'];
	$arrColunas[$regs['id_funcionario']][$i]['H'] = $regs['descTransporte'];
	$arrColunas[$regs['id_funcionario']][$i]['I'] = $regs['numero_contrato'];
	$arrColunas[$regs['id_funcionario']][$i]['J'] = $regs['data_inicio_contrato'] != '0000-00-00' ? mysql_php($regs['data_inicio_contrato']) : '';
	$arrColunas[$regs['id_funcionario']][$i]['K'] = $regs['data_fim_contrato'] != '0000-00-00' ? mysql_php($regs['data_fim_contrato']) : '';
	
	$i++;
}

//Fazendo um segundo loop apenas por causa da ordenação.
$objPHPExcel->setActiveSheetIndex(3);

$linha = 3;

foreach($arrayOrdenacao as $k=>$funcionario)
{	
	if (!isset($arrColunas[$funcionario]))
		continue;

	foreach($arrColunas[$funcionario] as $k => $value)
	{
		$colA = $arrColunas[$funcionario][$k-1]['A'] == $value['A'] ? '' : $value['A'];
		$colB = $arrColunas[$funcionario][$k-1]['B'] == $value['B'] ? '' : $value['B'];
		$colC = $arrColunas[$funcionario][$k-1]['C'] == $value['C'] ? '' : $value['C'];
		
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $linha, $colA);
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $linha, $colB);	
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $linha, $colC);	
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $linha, $value['D']);	
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, $linha, $value['E']);
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, $linha, $value['F']);
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $linha, $value['G']);
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, $linha, $value['H']);
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8, $linha, $value['I']);
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(9, $linha, $value['J']);
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(10, $linha,$value['K']);

		$linha++;
	}
}

/*/Alterações pedidas em 03/2015*/


// Redirect output to a clients web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="movimento_'.date('d-m-Y').'.xlsx"');
header('Cache-Control: max-age=0');
$objWriter->save('php://output');

exit();
?>