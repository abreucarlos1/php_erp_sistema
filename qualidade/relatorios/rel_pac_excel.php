<?php
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

//error_reporting(E_ALL);

//date_default_timezone_set('Europe/London');

/** PHPExcel_IOFactory */
//require_once("../includes/PHPExcel/Classes/PHPExcel/IOFactory.php"); 

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
 
require_once(INCLUDE_DIR."PHPExcel/Classes/PHPExcel.php");

$objPHPExcel = PHPExcel_IOFactory::load("../modelos_excel/relatorio_pac_modelo.xls");

$locale = 'pt_br';

$validlocale = PHPExcel_Settings::setlocale($locale);

if (!$validlocale) 
{
	echo 'Unable to set locale to '.$locale." - reverting to en_us<br />\n";
}

// Redirect output to a client's web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="pac_"'.date('His').'".xlsx"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

$db = new banco_dados();

switch($_POST["filtro"])
{
	//geral
	case 0:
		$filtro = "";
	break;
	
	//andamento
	case 1:
		$filtro1 = " >= '".date('Y-m-d')."' ";
	break;
	
	//atrasados
	case 2:
		$filtro1 = " < '".date('Y-m-d')."' ";
	break;
	
	//encerrados
	case 3:
		$filtro = "AND planos_acoes.status = 1 ";
	break;		
}

$array_sumario = NULL;
$array_sumario_acoes = NULL;


$sql = "SELECT * FROM ".DATABASE.".nao_conformidades ";
$sql .= "WHERE nao_conformidades.nao_conformidade_delete = 0 ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}

foreach($db->array_select as $cont0)
{
	$nao_conf[$cont0["id_nao_conformidade"]] = $cont0["cod_nao_conformidade"];
}
 
//Percorre a tabela
$sql = "SELECT * FROM ".DATABASE.".planos_acoes_referencias, ".DATABASE.".planos_acoes, ".DATABASE.".funcionarios, ".DATABASE.".setores "; 
$sql .= "WHERE planos_acoes.plano_acao_delete = 0 ";
$sql .= "AND planos_acoes.id_plano_acao_referencia = planos_acoes_referencias.id_plano_acao_referencia ";
$sql .= "AND planos_acoes.id_funcionario_criador = funcionarios.id_funcionario ";
$sql .= "AND planos_acoes.id_setor = setores.id_setor ";
$sql .= "AND planos_acoes.id_plano_acao IN ";
$sql .= "(SELECT id_plano_acao FROM ".DATABASE.".planos_acoes_complementos ";
$sql .= "WHERE planos_acoes_complementos.plano_acao_complemento_delete = 0 GROUP BY id_plano_acao HAVING MAX(prazo) ".$filtro1." ) ";
$sql .= $filtro;
$sql .= "GROUP BY planos_acoes.id_plano_acao ";
$sql .= "ORDER BY planos_acoes.id_plano_acao, data_criacao ASC ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}

$array_planos = $db->array_select;

$num_planilhas = $db->numero_registros;

$objPHPExcel->setActiveSheetIndex(1);

$A = $objPHPExcel->getActiveSheet();

//$A->setTitle('PA');
//cria as folhas na planilha
for($i=2;$i<=$num_planilhas;$i++)
{
	$B = clone $A;
	
	$B->setTitle('Plan');
	
	$sheetIndex = $i;
	
	$objPHPExcel->addSheet($B,$sheetIndex);	
}


$i = 1;

$regs_acoes = 0;

foreach($array_planos as $cont)
{
	switch($cont["tipo_plano_acao"])
	{
		case 1:
			$tipo_acao = "CORRETIVA";
		break;
		
		case 2:
			$tipo_acao = "PREVENTIVA";
		break;
	
		default: $tipo_acao = "";
	}
	
	$objPHPExcel->setActiveSheetIndex($i);
	
	$objPHPExcel->getActiveSheet()->setTitle($cont["cod_plano_acao"]);
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, 2, $cont["cod_plano_acao"]);
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, 3, date('d/m/Y'));
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, 4, $cont["funcionario"]);
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, 4, $cont["setor"]);
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8, 4, mysql_php($cont["data_criacao"]));
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, 5, $cont["status"]?'ENCERRADO':'PENDENTE');
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, 6, $tipo_acao);
	
	$complem = '';
	
	if($cont["id_plano_acao_referencia"]!=5)
	{
		if($cont["id_plano_acao_referencia"]==1)
		{
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, 7, $cont["plano_acao_referencia"].": ".$nao_conf[$cont["id_nao_conformidade"]]);	
			
			$complem = $nao_conf[$cont["id_nao_conformidade"]];
		
		}
		else
		{
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, 7, $cont["plano_acao_referencia"]);
		}
	
	}
	else
	{
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, 7, $cont["plano_acao_referencia"].": ".$cont["desc_outros"]);
		
		$complem = $cont["desc_outros"];
	}
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, 9, $cont["desc_nc"]);
	
	$objPHPExcel->getActiveSheet()->getStyle('A9')->getAlignment()->setWrapText(true);
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, 11, $cont["desc_acao"]);
	
	$objPHPExcel->getActiveSheet()->getStyle('A11')->getAlignment()->setWrapText(true);
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, 13, $cont["desc_causa_raiz"]);
	
	$objPHPExcel->getActiveSheet()->getStyle('A13')->getAlignment()->setWrapText(true);
	
	$sql = "SELECT * FROM ".DATABASE.".planos_acoes_complementos, ".DATABASE.".funcionarios  ";
	$sql .= "WHERE planos_acoes_complementos.id_plano_acao = '".$cont["id_plano_acao"]."' ";
	$sql .= "AND planos_acoes_complementos.plano_acao_complemento_delete = 0 ";
	$sql .= "AND planos_acoes_complementos.id_funcionario_responsavel = funcionarios.id_funcionario ";

	$db->select($sql,'MYSQL',true);
	
	//se der mensagem de erro, mostra
	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}
	
	$array_acoes = $db->array_select;
	
	//pega a maior quantidade de acoes
	if($regs_acoes<=$db->numero_registros)
	{
		$regs_acoes = $db->numero_registros;	
	}
	
	$linha = 16;
	
	$j = 1;
	
	foreach($array_acoes as $regs1)
	{
		switch ($regs1["status_plano_acao"])
		{
			case 0:
				$status_acao = "PENDENTE";
			break;
			
			case 1:
				$status_acao = "EM ANDAMENTO";
			break;
			
			case 2:
				$status_acao = "ENCERRADO";
			break;
		}		
					
		$objPHPExcel->getActiveSheet()->mergeCells("B".$linha.":D".$linha);
		
		$objPHPExcel->getActiveSheet()->mergeCells("E".$linha.":G".$linha);
	
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $linha, $regs1["item_acao"]);
		
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $linha, str_replace("|","\n",$regs1["plano_acao"]));
		
		$objPHPExcel->getActiveSheet()->getStyle('B'.$linha)->getAlignment()->setWrapText(true);
		
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, $linha, $regs1["funcionario"]);
		
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, $linha, mysql_php($regs1["prazo"]));
		
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8, $linha, $status_acao);
		
		//array do sumario
		$array_sumario_acoes[$cont["cod_plano_acao"]][0][$j] = $regs1["item_acao"];
		
		$array_sumario_acoes[$cont["cod_plano_acao"]][1][$j] = str_replace("|","\n",$regs1["plano_acao"]);
		
		$array_sumario_acoes[$cont["cod_plano_acao"]][2][$j] = $regs1["funcionario"]; 
		
		$array_sumario_acoes[$cont["cod_plano_acao"]][3][$j] = mysql_php($regs1["prazo"]);
		
		$array_sumario_acoes[$cont["cod_plano_acao"]][4][$j] = $status_acao;
		
		$j++;
		
		$linha++;
		
		$objPHPExcel->getActiveSheet()->insertNewRowBefore($linha,1);			
	}
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $linha+2, $cont["desc_obs"]);
	
	$objPHPExcel->getActiveSheet()->getStyle('A'.($linha+2))->getAlignment()->setWrapText(true);

	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $linha+4, $cont["desc_encerramento"]);
	
	$objPHPExcel->getActiveSheet()->getStyle('A'.($linha+4))->getAlignment()->setWrapText(true);
	
	//cria array para o sumário
	//código
	$array_sumario[0][$cont["cod_plano_acao"]][$i] = $cont["cod_plano_acao"];
	//originador
	$array_sumario[1][$cont["cod_plano_acao"]][$i] = $cont["funcionario"];
	//setor
	$array_sumario[2][$cont["cod_plano_acao"]][$i] = $cont["setor"];
	//tipo acao
	$array_sumario[3][$cont["cod_plano_acao"]][$i] = $tipo_acao;
	//status
	$array_sumario[4][$cont["cod_plano_acao"]][$i] = $cont["status"]?'ENCERRADO':'PENDENTE';
	//documento referencia
	$array_sumario[5][$cont["cod_plano_acao"]][$i] = $cont["plano_acao_referencia"];
	//complemento
	$array_sumario[6][$cont["cod_plano_acao"]][$i] = $complem;
	//data criacao
	$array_sumario[7][$cont["cod_plano_acao"]][$i] = mysql_php($cont["data_criacao"]);
	//descricao nc
	$array_sumario[8][$cont["cod_plano_acao"]][$i] = $cont["desc_nc"];
	//acao_imadiata
	$array_sumario[9][$cont["cod_plano_acao"]][$i] = $cont["desc_acao"];
	//causa raiz
	$array_sumario[10][$cont["cod_plano_acao"]][$i] = $cont["desc_causa_raiz"];
	//observacao
	$array_sumario[11][$cont["cod_plano_acao"]][$i] = $cont["desc_obs"];
	//encerramento
	$array_sumario[12][$cont["cod_plano_acao"]][$i] = $cont["desc_encerramento"];	
	
	$i++;		
}

//cria os labels no sumario
$objPHPExcel->setActiveSheetIndex(0);

$coluna = 13;

for($i=1;$i<=$regs_acoes;$i++)
{
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna, 7, 'ITEM AÇÕES');
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+1, 7, 'AÇÕES');

	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+2, 7, 'RESPONSÁVEL');

	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+3, 7, 'PRAZO');

	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+4, 7, 'STATUS');
	
	$coluna+=5;
}

$linha_sumario = 8;  

$j = 1;

foreach($array_sumario[0] as $cod_plano_acao=>$indice)
{
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $linha_sumario, $cod_plano_acao);

	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $linha_sumario, $array_sumario[1][$cod_plano_acao][$j]);

	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $linha_sumario, $array_sumario[2][$cod_plano_acao][$j]);

	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $linha_sumario, $array_sumario[3][$cod_plano_acao][$j]);
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, $linha_sumario, $array_sumario[4][$cod_plano_acao][$j]);

	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, $linha_sumario, $array_sumario[5][$cod_plano_acao][$j]);

	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $linha_sumario, $array_sumario[6][$cod_plano_acao][$j]);

	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, $linha_sumario, $array_sumario[7][$cod_plano_acao][$j]);
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8, $linha_sumario, $array_sumario[8][$cod_plano_acao][$j]);

	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(9, $linha_sumario, $array_sumario[9][$cod_plano_acao][$j]);
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(10, $linha_sumario, $array_sumario[10][$cod_plano_acao][$j]);

	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(11, $linha_sumario, $array_sumario[11][$cod_plano_acao][$j]);

	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(12, $linha_sumario, $array_sumario[12][$cod_plano_acao][$j]);

	$k = 1;
	
	$coluna = 12;
	
	foreach($array_sumario_acoes[$cod_plano_acao][0] as $item=>$index)
	{
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($k+$coluna, $linha_sumario, $item);
	
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($k+$coluna+1, $linha_sumario, $array_sumario_acoes[$cod_plano_acao][1][$k]);
		
		$objPHPExcel->getActiveSheet()->getStyle((num2alfa($k+$coluna+1)).($linha_sumario))->getAlignment()->setWrapText(true);
	
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($k+$coluna+2, $linha_sumario, $array_sumario_acoes[$cod_plano_acao][2][$k]);
		
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($k+$coluna+3, $linha_sumario, $array_sumario_acoes[$cod_plano_acao][3][$k]);
		
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($k+$coluna+4, $linha_sumario, $array_sumario_acoes[$cod_plano_acao][4][$k]);
		
		$coluna+=4;
		
		$k++;
	}
	
	$j++;
	
	$linha_sumario++;
}

$objWriter->save('php://output');

exit;
?>
