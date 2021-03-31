<?php
/*
		Relatório planilha FPV
		
		Criado por Carlos Abreu  
		
		local/Nome do arquivo:
		../orcamento/relatorios/rel_planilha_fpv_excel.php
	
		Versão 0 --> VERSÃO INICIAL : 10/03/2015 - Carlos Abreu
		Versão 1 --> Inclusão dos campos reg_del nas consultas - 20/11/2017 - Carlos Abreu		
*/	

ini_set('max_execution_time', 0); // No time limit
ini_set('post_max_size', '20M');
ini_set('upload_max_filesize', '20M');
ini_set('memory_limit', '512M');
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

$sql = "SELECT * FROM ".DATABASE.".propostas ";
$sql .= "WHERE propostas.reg_del = 0 ";
$sql .= "AND propostas.id_proposta = '".$_POST["id_proposta"]."' ";

$db->select($sql,'MYSQL',true);

$regs0 = $db->array_select[0];


//indices [codsetor]= [id_cargo_grupo => linha] 
//mapeia as celulas do excel (linhas)
$array_setores[15] = array(37=>12,61=>15); 							   //Apoio/COORDENAÇÃO/Supervisão
$array_setores[23] = array(16=>17,82=>18,74=>19,83=>20,84=>21,61=>15); //PDMS
$array_setores[12] = array(48=>22,85=>23,86=>24,15=>25,79=>26,61=>15); //PROCESSO
$array_setores[8] = array(51=>27,87=>28,100=>29,88=>30,80=>31,61=>15); //TUBULAÇÃO
$array_setores[9] = array(54=>32,89=>33,90=>34,91=>35,78=>36,61=>15);  //MECANICA
$array_setores[13] = array(52=>37,92=>38,12=>39,93=>40,76=>41,61=>15); //ELÉTRICA
$array_setores[10] = array(45=>42,94=>43,7=>44,95=>45,77=>46,61=>15);  //INSTRUMENTAÇÃO
$array_setores[7] = array(43=>47,96=>48,13=>49,97=>50,155=>51,61=>15); //AUTOMACAO
$array_setores[20] = array(40=>52,98=>53,14=>54,99=>55,75=>56,61=>15); //ESTRUTURA METALICA
$array_setores[14] = array(40=>57,98=>58,14=>59,99=>60,75=>61,61=>15); //CIVIL
$array_setores[27] = array(54=>62,89=>63,90=>64,91=>65,78=>66,61=>15); //SEGURANÇA
$array_setores[26] = array(54=>67,89=>68,90=>69,91=>70,78=>71,61=>15); //VAC
$array_setores[5] = array(47=>13,69=>14,61=>16);					   //PLANEJAMENTO

//para despesas (MOBILIZAÇÃO)
//indices [codsetor][codatividade] = linha 
$array_setores[29] = array(1246=>11,1245=>12,1254=>13,1267=>14,1266=>20,1265=>21,1273=>22,1274=>23,
						   	1271=>34,1258=>41,1261=>42,1247=>49,1256=>56,1270=>57,1249=>58,1255=>59,
							1248=>60,1260=>64,1262=>65,1257=>66,1269=>67,1259=>68,1264=>69,1272=>70,
							1263=>71,1251=>77,1252=>78,1253=>79,1250=>80,1244=>107,1268=>108); //DESPESAS


$sql = "SELECT setores.id_setor, atividades.id_atividade, escopo_detalhado.grau_dificuldade, escopo_detalhado.qtd_necessario, atividades.horasestimadas, atividades_orcamento.porcentagem, rh_cargos.id_cargo_grupo ";
$sql .= "FROM ".DATABASE.".setores, ".DATABASE.".propostas, ".DATABASE.".escopo_geral, ".DATABASE.".escopo_detalhado, ".DATABASE.".atividades "; 
$sql .= "LEFT JOIN  ".DATABASE.".atividades_orcamento ON (atividades.id_atividade = atividades_orcamento.id_atividade AND atividades_orcamento.reg_del = 0) ";
$sql .= "LEFT JOIN  ".DATABASE.".rh_cargos ON (atividades_orcamento.id_cargo = rh_cargos.id_cargo_grupo AND rh_cargos.reg_del = 0) ";
$sql .= "WHERE propostas.reg_del = 0 ";
$sql .= "AND escopo_geral.reg_del = 0 "; 
$sql .= "AND escopo_detalhado.reg_del = 0 ";
$sql .= "AND setores.reg_del = 0 ";
$sql .= "AND atividades.reg_del = 0 ";
$sql .= "AND propostas.id_proposta = '".$_POST["id_proposta"]."' "; 
$sql .= "AND escopo_geral.id_proposta = propostas.id_proposta "; 
$sql .= "AND escopo_geral.id_escopo_geral = escopo_detalhado.id_escopo_geral ";
$sql .= "AND escopo_detalhado.id_tarefa = atividades.id_atividade ";
$sql .= "AND atividades.cod = setores.id_setor ";

$db->select($sql,'MYSQL',true);

foreach($db->array_select as $regs)
{	
	$grau = $regs["grau_dificuldade"];
	
	$quant = $regs["qtd_necessario"];
	
	$calc_des = 0;
	
	$calc_tot = 0;
	
	//se mobilização
	if($regs["id_setor"]==29)
	{
		$calc_des = $regs["horasestimadas"]*$quant*$grau;
	}
	
	//se geral
	if($regs["id_setor"]==25)
	{
		$calc_tot = $regs["horasestimadas"]*$quant*$grau;
	}
	else
	{		
		$calc_tot = $regs["horasestimadas"]*$quant*$grau*($regs["porcentagem"]/100);		
	}
	
	//setor - cargo grupo
	$array_total[$regs["id_setor"]][$regs["id_cargo_grupo"]] += $calc_tot;
	
	//setor - codatividade
	$array_des[$regs["id_setor"]][$regs["id_atividade"]] += $calc_des;		
}

$sql = "SELECT * FROM ".DATABASE.".subcontratados ";
$sql .= "WHERE subcontratados.reg_del = 0 ";
$sql .= "AND subcontratados.id_proposta = '".$_POST["id_proposta"]."' ";
$sql .= "ORDER BY subcontratado ";

$db->select($sql,'MYSQL',true);

foreach($db->array_select as $regs1)
{	
	//subcontratados
	$array_subcontrato[$regs1["id_subcontratado"]] = array($regs1["subcontratado"],$regs1["descritivo"],$regs1["valor_subcontrato"]);		
}

$objPHPExcel = PHPExcel_IOFactory::load("../modelos_excel/modelo_fpv.xls");

$locale = 'pt_br';

$validlocale = PHPExcel_Settings::setlocale($locale);

if (!$validlocale) 
{
	echo 'Unable to set locale to '.$locale." - reverting to en_us<br />\n";
}

// Redirect output to a clients web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="planilha_fpv_"'.date('dmYHis').'".xlsx"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

//MOBILIZAÇÃO
$objPHPExcel->setActiveSheetIndex(4);

foreach($array_des as $codsetor=>$array_ativ)
{
	foreach($array_ativ as $cod_ativ=>$valor)
	{
		if($array_setores[$codsetor][$cod_ativ]!='')
		{
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2,$array_setores[$codsetor][$cod_ativ],$valor);
		}
	}	
}

//HH
$objPHPExcel->setActiveSheetIndex(5);

//R$ PROPOSTA
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0,10,$regs0["numero_proposta"]." - ".$regs0["descricao_proposta"]);

foreach($array_total as $codsetor=>$array_cargo)
{
	foreach($array_cargo as $cod_cargo=>$valor)
	{
		if($array_setores[$codsetor][$cod_cargo]!='')
		{
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(12,$array_setores[$codsetor][$cod_cargo],$valor);
		}
	}	
}

//SUBCONTRATADOS
$objPHPExcel->setActiveSheetIndex(6);

$linha = 10;

foreach($array_subcontrato as $id_subcontrato => $array_subcont)
{
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0,$linha,$array_subcont[0]);
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1,$linha,$array_subcont[1]);
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2,$linha,$array_subcont[2]);
	
	$linha++;		
}

$objWriter->save('php://output');

exit;

?>