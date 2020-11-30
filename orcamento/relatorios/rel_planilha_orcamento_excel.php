<?php
/*
		Relatório planilha orcamento
		
		Criado por Carlos Abreu  
		
		local/Nome do arquivo:
		../orcamento/relatorios/rel_planilha_orcamento_excel.php
	
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

session_start();

$db = new banco_dados();

$sql = "SELECT * FROM ".DATABASE.".propostas ";
$sql .= "WHERE propostas.reg_del = 0 ";
$sql .= "AND propostas.id_proposta = '".$_POST["id_proposta"]."' ";

$db->select($sql,'MYSQL',true);

$regs0 = $db->array_select[0];

$chars = array("'","\"",")","(","\\","/","´","`"," ","?");

//verifica se existe registro no escopo detalhado

$sql = "SELECT * FROM ".DATABASE.".setores, ".DATABASE.".atividades, ".DATABASE.".formatos, ".DATABASE.".escopo_geral, ".DATABASE.".escopo_detalhado ";
$sql .= "LEFT JOIN ".DATABASE.".subcontratados ON (escopo_detalhado.id_subcontratado = subcontratados.id_subcontratado AND subcontratados.reg_del = 0) ";
$sql .= "WHERE escopo_geral.reg_del = 0 ";
$sql .= "AND escopo_detalhado.reg_del = 0 ";
$sql .= "AND setores.reg_del = 0 ";
$sql .= "AND atividades.reg_del = 0 ";
$sql .= "AND formatos.reg_del = 0 ";
$sql .= "AND escopo_geral.id_proposta = '".$_POST["id_proposta"]."' ";
$sql .= "AND escopo_geral.id_escopo_geral = escopo_detalhado.id_escopo_geral ";
$sql .= "AND escopo_detalhado.id_tarefa = atividades.id_atividade ";
$sql .= "AND formatos.id_formato = atividades.id_formato ";
$sql .= "AND atividades.cod = setores.id_setor ";
$sql .= "AND atividades.obsoleto = 0 ";
$sql .= "AND setores.abreviacao NOT IN ('ADM','CMS','CON','COM','FIN','GOB','MON','MAT','OUT','GER','TIN') ";	
$sql .= "ORDER BY escopo_geral.escopo_geral, setores.setor, atividades.descricao ";

$db->select($sql,'MYSQL',true);

$array_orc = $db->array_select;

foreach($array_orc as $regs1)
{
	$sql = "SELECT * FROM ".DATABASE.".atividades_orcamento, ".DATABASE.".rh_cargos ";
	$sql .= "WHERE rh_cargos.id_cargo_grupo = atividades_orcamento.id_cargo ";
	$sql .= "AND atividades_orcamento.reg_del = 0 ";
	$sql .= "AND rh_cargos.reg_del = 0 ";
	$sql .= "AND atividades_orcamento.id_atividade = '" . $regs1["id_atividade"] . "' ";

	$db->select($sql,'MYSQL',true);
	
	$array_porcent = NULL;
	
	$calc_eng = 0;
	
	$calc_cad = 0;
	
	$calc_proj = 0;
	
	$quant_fmt = 0;
	
	foreach($db->array_select as $reg_por)
	{
		switch ($reg_por["id_categoria"])
		{					
			case 1: //ENG
			case 2:						
			case 3:				
				$array_porcent['ENG'] += $reg_por["porcentagem"];				
			break;
			
			case 4: //projetista
			case 6: //apoio
				$array_porcent['PROJ'] += $reg_por["porcentagem"];			
			break;
			
			case 5: //cadista			
				$array_porcent['CAD'] += $reg_por["porcentagem"];			
			break;					
		}
	
	}	

	$calc_eng = $regs1["horasestimadas"]*$regs1["qtd_necessario"]*$regs1["grau_dificuldade"]*($array_porcent['ENG']/100);
	
	$calc_proj = $regs1["horasestimadas"]*$regs1["qtd_necessario"]*$regs1["grau_dificuldade"]*($array_porcent['PROJ']/100);
	
	$calc_cad = $regs1["horasestimadas"]*$regs1["qtd_necessario"]*$regs1["grau_dificuldade"]*($array_porcent['CAD']/100);
	
	//SE FORMATOS
	if(in_array($regs1["id_formato"],array('1','2','3','4','5')))
	{
		$quant_fmt = $regs1["qtd_necessario"]*$regs1["fator_equivalente"];
	}
	
	if($regs1["id_setor"]!=29)//se diferente de despesas
	{
		$array_setores[$regs1["id_setor"]] = $regs1["setor"];	
	}
	else
	{
		$array_setores[$regs1["id_setor"]] = 'MOBILIZAÇÃO';
	}
	
	//MONTA O ESCOPOS GERAIS	
	$array_escopo[$regs1["id_escopo_geral"]] = tiraacentos(str_replace($chars,"",trim(substr(addslashes($regs1["escopo_geral"]),0,30))));
	
	//MONTA OS ESCOPOS DETALHADOS
	$array_escopo_detalhe[$regs1["id_escopo_geral"]][$regs1["id_setor"]][$regs1["id_escopo_detalhado"]] = array($regs1["codigo"],$regs1["descricao"]." ".$regs1["descricao_escopo"],$regs1["codigo_formato"],number_format($regs1["grau_dificuldade"],2,".",","),number_format($regs1["qtd_necessario"],2,".",","),number_format($calc_eng,2,".",","),number_format($calc_proj,2,".",","),number_format($calc_cad,2,".",","),number_format($calc_eng+$calc_proj+$calc_cad,2,".",","),number_format($quant_fmt,2,".",","),$regs1["subcontratado"]." - ".$regs1["descritivo"]);		

	//numero linhas das atividades
	$array_count_linhas[$regs1["id_escopo_geral"]][$regs1["id_escopo_detalhado"]]=+1;
	
	$array_count_setores[$regs1["id_escopo_geral"]][$regs1["id_setor"]] =+1;	
	
	//seleciona a mobilizacao (DESPESAS)
	$sql = "SELECT * FROM ".DATABASE.".mobilizacao, ".DATABASE.".atividades, ".DATABASE.".formatos ";
	$sql .= "WHERE mobilizacao.reg_del = 0 ";
	$sql .= "AND atividades.reg_del = 0 ";
	$sql .= "AND formatos.reg_del = 0 ";
	$sql .= "AND mobilizacao.id_escopo_geral = '".$regs1["id_escopo_geral"]."' ";
	$sql .= "AND mobilizacao.id_tarefa = atividades.id_atividade ";
	$sql .= "AND atividades.id_formato = formatos.id_formato ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}
	
	$array_mobilizacao = $db->array_select;

	//se hover mobilizacao mostra os registros
	if($db->numero_registros>0)
	{
		//MONTA AS MOBILIZAÇÕES
		foreach($array_mobilizacao as $regs3)
		{
			$array_mobilizacao[$regs3["id_escopo_geral"]][$regs3["id_mobilizacao"]] = array($regs3["codigo"],$regs3["descricao"]." ".$regs3["descricao_mobilizacao"],number_format($regs3["qtd_necessario"],2,".",","),number_format($regs3["valor_mobilizacao"],2,".",","));
		}		
	}	
}

$objPHPExcel = PHPExcel_IOFactory::load("../modelos_excel/modelo_orcamento.xls");

$locale = 'pt_br';

$validlocale = PHPExcel_Settings::setlocale($locale);

if (!$validlocale) 
{
	echo 'Unable to set locale to '.$locale." - reverting to en_us<br />\n";
}

// Redirect output to a clients web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="planilha_orcamento_"'.date("dmYHis").'".xlsx"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

//referencia a folha modelo
$A = $objPHPExcel->getActiveSheet();

$sheetIndex = 0;

//renomeia as folhas conforme os escopos
foreach ($array_escopo as $chave=>$valor)
{
	if($sheetIndex==0)
	{
		$objPHPExcel->getActiveSheet()->setTitle(($sheetIndex+1)."_".substr(tiraacentos($valor),0,25));
	}
	else
	{
		//copia a folha
		$B = clone $A;
		
		$B->setTitle(($sheetIndex+1)."_".substr(tiraacentos($valor),0,25));
		
		$objPHPExcel->addSheet($B,$sheetIndex);
	}
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2,1,'PLANILHA PADRÃO - '.$regs0["numero_proposta"].' - '.tiraacentos($regs0["descricao_proposta"]));
	
	$sheetIndex++;
}

$sheetIndex = 0;

//preenche a planilha
foreach ($array_escopo as $chave=>$valor)
{	
	//seta a planilha corrente
	$objPHPExcel->setActiveSheetIndex($sheetIndex);
		
	//seta a linha inicial
	$linha = 5;
	
	//CALCULA O NUMERO DE LINHAS A AVANÇAR	
	$num_linhas = count($array_count_linhas[$chave])+(count($array_count_setores[$chave])*2)+count($array_mobilizacao[$chave]);
		
	$objPHPExcel->getActiveSheet()->insertNewRowBefore($linha+1,$num_linhas);
	
	$array_linha = NULL;
	
	foreach ($array_escopo_detalhe[$chave] as $id_setor=>$array_escop_det)
	{
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0,$linha,$array_setores[$id_setor]);
		
		$linha++;
		
		$linha_sum_sub = $linha;
		
		foreach ($array_escop_det as $id_det=>$descri)
		{		
			
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1,$linha,$descri[0]);
			
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2,$linha,$descri[1]);
			
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3,$linha,$descri[2]);
			
			$descri[3] = floatval(str_replace(',','',$descri[3]));
			
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4,$linha,$descri[3]);
			
			$descri[4] = floatval(str_replace(',','',$descri[4]));
			
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5,$linha,$descri[4]);						
			
			$descri[5] = floatval(str_replace(',','',$descri[5]));
			
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6,$linha,$descri[5]);
			
			$descri[6] = floatval(str_replace(',','',$descri[6]));
			
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7,$linha,$descri[6]);
			
			$descri[7] = floatval(str_replace(',','',$descri[7]));
			
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8,$linha,$descri[7]);
			
			$descri[8] = floatval(str_replace(',','',$descri[8]));
			
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(9,$linha,$descri[8]);
			
			$descri[9] = floatval(str_replace(',','',$descri[9]));
			
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(10,$linha,$descri[9]);
			
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(11,$linha,$descri[10]);
			
			$linha++;
		}		
		
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2,$linha,"SUBTOTAL");
		
		$objPHPExcel->getActiveSheet()->getStyle("C".$linha)->getFont()->setBold(true);
		
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6,$linha,"=SUM(G".$linha_sum_sub.":G".($linha-1).")");
		
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7,$linha,"=SUM(H".$linha_sum_sub.":H".($linha-1).")");
		
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8,$linha,"=SUM(I".$linha_sum_sub.":I".($linha-1).")");
		
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(9,$linha,"=SUM(J".$linha_sum_sub.":J".($linha-1).")");
		
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(10,$linha,"=SUM(K".$linha_sum_sub.":K".($linha-1).")");
		
		$objPHPExcel->getActiveSheet()->getStyle('A'.$linha.":L".$linha)->applyFromArray(
			array(
				'fill' => array(
					'type' => PHPExcel_Style_Fill::FILL_SOLID,
					'color' => array('rgb' => 'C1FFC1')
				)
			)
		);
		
		//armazena as linhas dos subtotais
		$array_linha['G'][] .= "G".$linha;
		$array_linha['H'][] .= "H".$linha;
		$array_linha['I'][] .= "I".$linha;
		$array_linha['J'][] .= "J".$linha;
		$array_linha['K'][] .= "K".$linha;
		
		$linha++;		
	}	
	
	//se existir mobilizacao
	if(count($array_mobilizacao[$chave])>0)
	{
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0,$linha,'MOBILIZAÇÃO');
		
		$linha++;
			
		//MOBILIZACAO
		foreach($array_mobilizacao[$chave] as $id_mobilizacao=>$array_descri_mobilizacao)
		{
			
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1,$linha,$array_descri_mobilizacao[0]);
			
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2,$linha,$array_descri_mobilizacao[1]);
			
			$descri[2] = floatval(str_replace(',','',$array_descri_mobilizacao[2]));
			
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5,$linha,$descri[2]);
			
			$linha++;
				
		}
	}
	else
	{
		$linha++;	
	}	
	
	//TOTALIZA
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6,$linha,"=SUM(".implode(",",$array_linha['G']).")");
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7,$linha,"=SUM(".implode(",",$array_linha['H']).")");
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8,$linha,"=SUM(".implode(",",$array_linha['I']).")");
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(9,$linha,"=SUM(".implode(",",$array_linha['J']).")");
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(10,$linha,"=SUM(".implode(",",$array_linha['K']).")");
	
	$sheetIndex++;
}

$objWriter->save('php://output');

exit;

?>