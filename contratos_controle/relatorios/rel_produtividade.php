<?php
/*
	Relatório de Produtividade
	Criado por Carlos Abreu  
	
	Versão 0 --> VERSÃO INICIAL : 19/01/2017
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

$array_coord = NULL;

$array_qtd_emitido = NULL;

$array_a1_equivalente = NULL;

$array_recursos = NULL;

$array_disciplina = NULL;

$array_atividades = NULL;

$array_cargos = NULL;

$array_linha_sub = NULL;

$disciplina = "";

$atividade = "";

$recursos = "";

$index_ativ = 0;

$index_recurs = 0;

if($_POST["escolhaos"]==-1)
{
	$projeto = 'TODOS';
	$cliente = 'TODOS';
	$coorddvm = 'TODOS';
	$coordcli = 'TODOS';
}
else
{
	//TABELA PA7 - COORDENADORES
	$sql = "SELECT * FROM PA7010 WITH(NOLOCK) ";
	$sql .= "WHERE PA7010.D_E_L_E_T_ = '' ";

	$db->select($sql,'MSSQL', true);

	if($db->erro!='')
	{
		die($db->erro);
	}
	
	foreach($db->array_select[0] as $regs)
	{
		$array_coord[$regs["PA7_ID"]] = $regs["PA7_NOME"];
	}
	
	//Seleciona o PROJETO
	$sql = "SELECT AF8_PROJET, AF8_DESCRI, A1_NOME, A1_NREDUZ, AF8_COORD1, AF8_RESPTE FROM AF8010 WITH(NOLOCK), SA1010 WITH(NOLOCK) ";
	$sql .= "WHERE AF8010.D_E_L_E_T_ = '' ";
	$sql .= "AND SA1010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF8010.AF8_CLIENT = SA1010.A1_COD ";
	$sql .= "AND AF8010.AF8_LOJA = SA1010.A1_LOJA ";
	$sql .= "AND AF8_PROJET = '".$_POST["escolhaos"]."' ";
	$sql .= "ORDER BY AF8_PROJET ";

	$db->select($sql,'MSSQL', true);

	if($db->erro!='')
	{
		die($db->erro);
	}	
	
	$regs1 = $db->array_select[0];
	
	$projeto = trim($regs1["AF8_PROJET"])." - ".trim($regs1["AF8_DESCRI"]);
	$cliente = trim($regs1["A1_NOME"])." - ".trim($regs1["A1_NREDUZ"]);
	$coorddvm = trim($array_coord[$regs1["AF8_COORD1"]]);
	$coordcli = trim($regs1["AF8_RESPTE"]);
}

//SELECIONA A ULTIMA EMISSÃO DOS DOCUMENTOS

$sql = "SELECT setores.abreviacao, atividades.codigo, SUM(numero_folhas) AS FOLHAS, SUM(numero_folhas*fator_equivalente) AS A1_EQUIV FROM ".DATABASE.".OS, ".DATABASE.".setores, ".DATABASE.".atividades, ".DATABASE.".numeros_interno, ".DATABASE.".solicitacao_documentos_detalhes, ".DATABASE.".ged_arquivos, ".DATABASE.".ged_versoes, ".DATABASE.".ged_pacotes, ".DATABASE.".formatos ";
$sql .= "WHERE numeros_interno.id_formato = formatos.id_formato ";
$sql .= "AND OS.reg_del = 0 ";
$sql .= "AND setores.reg_del = 0 ";
$sql .= "AND atividades.reg_del = 0 ";
$sql .= "AND formatos.reg_del = 0 ";
$sql .= "AND numeros_interno.reg_del = 0 ";
$sql .= "AND solicitacao_documentos_detalhes.reg_del = 0 ";
$sql .= "AND ged_arquivos.reg_del = 0 ";
$sql .= "AND ged_versoes.reg_del = 0 ";
$sql .= "AND ged_pacotes.reg_del = 0 ";
$sql .= "AND numeros_interno.id_disciplina = setores.id_setor ";

if($_POST["escolhaos"]!=-1)
{
	$sql .= "AND os.os = '" . (int)$_POST["escolhaos"] . "' ";
}

if($_POST["escolhadisciplina"]!=-1)
{
	$sql .= "AND setores.abreviacao = '".$_POST["escolhadisciplina"]."' ";
}

if($_POST["escolhaatividade"]!=-1)
{
	$sql .= "AND atividades.codigo = '".$_POST["escolhaatividade"]."' ";
}

$sql .= "AND OS.id_os = numeros_interno.id_os ";
$sql .= "AND numeros_interno.id_numero_interno = ged_arquivos.id_numero_interno ";
$sql .= "AND solicitacao_documentos_detalhes.id_numero_interno = numeros_interno.id_numero_interno ";
$sql .= "AND numeros_interno.id_atividade = atividades.id_atividade ";
$sql .= "AND ged_arquivos.id_ged_arquivo = ged_versoes.id_ged_arquivo ";
$sql .= "AND ged_arquivos.documento_interno = 1 "; //somente documentos internos
$sql .= "AND ged_versoes.id_ged_pacote = ged_pacotes.id_ged_pacote ";
$sql .= "GROUP BY setores.setor, atividades.codigo ";
$sql .= "ORDER BY setores.setor ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}

foreach($db->array_select as $cont0)
{
	$array_qtd_emitido[$cont0["abreviacao"]][$cont0["codigo"]] += $cont0["FOLHAS"];
	
	$array_a1_equivalente[$cont0["abreviacao"]][$cont0["codigo"]] += $cont0["A1_EQUIV"];
}

//RECURSOS E HORAS
$sql = "SELECT SUM(AJK_HQUANT) AS HORAS, AE8_DESCRI, AF9_GRPCOM, AF9_COMPOS, AE8_RECURS, AN1_DESCRI FROM AJK010 WITH(NOLOCK), AE8010 WITH(NOLOCK), AN1010 WITH(NOLOCK), AF9010 WITH(NOLOCK), AF8010 WITH(NOLOCK) "; 
$sql .= "WHERE AJK010.D_E_L_E_T_ = '' "; 
$sql .= "AND AE8010.D_E_L_E_T_ = '' ";
$sql .= "AND AF8010.D_E_L_E_T_ = '' ";
$sql .= "AND AF9010.D_E_L_E_T_ = '' ";
$sql .= "AND AF9_PROJET = AF8_PROJET ";
$sql .= "AND AF9_REVISA = AF8_REVISA ";
$sql .= "AND AJK_PROJET = AF9_PROJET ";
$sql .= "AND AJK_REVISA = AF9_REVISA ";
$sql .= "AND AJK_TAREFA = AF9_TAREFA ";

if($_POST["escolhaos"]!=-1)
{
	$sql .= "AND AF8_PROJET = '".$_POST["escolhaos"]."' ";
}

if($_POST["escolhadisciplina"]!=-1)
{
	$sql .= "AND AF9_GRPCOM = '".$_POST["escolhadisciplina"]."' ";
}

if($_POST["escolhaatividade"]!=-1)
{
	$sql .= "AND AF9_COMPOS = '".$_POST["escolhaatividade"]."' ";
}

$sql .= "AND AJK_CTRRVS = 1 ";
$sql .= "AND AJK_RECURS = AE8_RECURS ";
$sql .= "AND AE8_FUNCAO = AN1_CODIGO ";
$sql .= "GROUP BY AE8_DESCRI, AF9_GRPCOM, AF9_COMPOS, AE8_RECURS, AN1_DESCRI ";

$db->select($sql,'MSSQL', true);

if($db->erro!='')
{
	$resposta->addAlert($db->erro);
	
	return $resposta;
}

foreach($db->array_select as $regs3)
{
	$array_recursos[trim($regs3["AF9_GRPCOM"])][trim($regs3["AF9_COMPOS"])][trim($regs3["AE8_RECURS"])] += $regs3["HORAS"];

	$array_cargos[trim($regs3["AF9_GRPCOM"])][trim($regs3["AF9_COMPOS"])][trim($regs3["AE8_RECURS"])] = trim($regs3["AN1_DESCRI"]);
	
	$array_nomes[trim($regs3["AE8_RECURS"])] = trim($regs3["AE8_DESCRI"]);

	$index_recurs++;	
}


$objPHPExcel = PHPExcel_IOFactory::load("../modelos_excel/produtividade_modelo.xls");

$locale = 'pt_br';

$validlocale = PHPExcel_Settings::setlocale($locale);

if (!$validlocale) 
{
	echo 'Unable to set locale to '.$locale." - reverting to en_us<br />\n";
}

// Redirect output to a clients web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="produtividade_"'.date('His').'".xlsx"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

//1ª folha
$objPHPExcel->setActiveSheetIndex(0);

$coluna = 2;

$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna, 5, iconv('ISO-8859-1', 'UTF-8',$projeto));

$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna, 6, iconv('ISO-8859-1', 'UTF-8',$cliente));

$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna, 7, iconv('ISO-8859-1', 'UTF-8',$coorddvm));

//Nome coordenador cliente
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna, 8, iconv('ISO-8859-1', 'UTF-8',$coordcli));

//DISCIPLINAS E ATIVIDADES
$sql = "SELECT AE5_GRPCOM, AE5_DESCRI, AE1_DESCRI, AF9_COMPOS FROM AF9010 WITH(NOLOCK), AF8010 WITH(NOLOCK), AE1010 WITH(NOLOCK), AE5010 WITH(NOLOCK) ";
$sql .= "WHERE AF8010.D_E_L_E_T_ = '' ";
$sql .= "AND AF9010.D_E_L_E_T_ = '' ";
$sql .= "AND AE5010.D_E_L_E_T_ = '' ";
$sql .= "AND AE1010.D_E_L_E_T_ = '' ";
$sql .= "AND AE5_GRPCOM = AF9_GRPCOM ";
$sql .= "AND AE1_COMPOS = AF9_COMPOS ";

if($_POST["escolhaos"]!=-1)
{
	$sql .= "AND AF8_PROJET = '".$_POST["escolhaos"]."' ";
}

if($_POST["escolhadisciplina"]!=-1)
{
	$sql .= "AND AE5_GRPCOM = '".$_POST["escolhadisciplina"]."' ";
}

if($_POST["escolhaatividade"]!=-1)
{
	$sql .= "AND AF9_COMPOS = '".$_POST["escolhaatividade"]."' ";
}

$sql .= "AND AF9_PROJET = AF8_PROJET ";
$sql .= "AND AF9_REVISA = AF8_REVISA ";
$sql .= "AND AE5_GRPCOM NOT IN ('DES') ";
$sql .= "GROUP BY AE5_GRPCOM, AF9_COMPOS, AE5_DESCRI, AE1_DESCRI ";

$db->select($sql,'MSSQL', true);

if($db->erro!='')
{
	$resposta->addAlert($db->erro);
	
	return $resposta;
}

$linha = 11;

foreach($db->array_select as $regs2)
{
	$array_disciplina[trim($regs2["AE5_GRPCOM"])] = trim($regs2["AE5_DESCRI"]);
	
	$array_atividades[trim($regs2["AE5_GRPCOM"])][trim($regs2["AF9_COMPOS"])] = trim($regs2["AE1_DESCRI"]);
	
	$index_ativ++;	

	$atividade = trim($regs2["AF9_COMPOS"]);	
}

$linhas = count($array_disciplina)+(count($array_atividades,1))+$index_ativ+$index_recurs;

//INSERE LINHAS COM A QUANTIDADE DE REGISTROS //AUMENTA A PERFORMANCE
$objPHPExcel->getActiveSheet()->insertNewRowBefore($linha+1,$linhas);

//MONTA AS DISCIPLINAS
foreach ($array_disciplina as $grupo=>$descricao)
{
		$objPHPExcel->getActiveSheet()->getStyle('A'.$linha.":B".$linha)->getFont()->setBold(true)->setSize(10);

		$objPHPExcel->getActiveSheet()->mergeCells("A".($linha).":B".($linha));

		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $linha, iconv('ISO-8859-1', 'UTF-8',$descricao));
		
		$linha++;
		
		$linha_inicio_disc = $linha;
		
		$array_linha_sub_ativ = NULL;		
		
		foreach($array_atividades[$grupo] as $composicao=>$descricao_ativ)
		{
			$objPHPExcel->getActiveSheet()->mergeCells("C".($linha).":D".($linha));
			
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $linha, iconv('ISO-8859-1', 'UTF-8',$composicao." - ".$descricao_ativ));
			
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, $linha, $array_qtd_emitido[$grupo][$composicao]);
			
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, $linha, $array_a1_equivalente[$grupo][$composicao]);
			
			$linha_inicio_ativ = $linha;
			
			$linha++;			
			
			foreach($array_recursos[$grupo][$composicao] as $cod_recursos=>$horas)
			{
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $linha, iconv('ISO-8859-1', 'UTF-8',$array_nomes[$cod_recursos]));
		
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8, $linha, iconv('ISO-8859-1', 'UTF-8',$array_cargos[$grupo][$composicao][$cod_recursos]));
				
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(10, $linha, $horas);
				
				$objPHPExcel->getActiveSheet()->mergeCells("G".($linha).":H".($linha));
			
				$objPHPExcel->getActiveSheet()->mergeCells("I".($linha).":J".($linha));
				
				$linha++;					
			}
			
			//por ativide
			//printa o texto de sub-total		
			$objPHPExcel->getActiveSheet()->getStyle('D'.$linha.":L".$linha)->getFont()->setBold(true)->setSize(10);
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $linha, "SUB-TOTAL:");
			
			//SUMARIZA OS DOCUMENTOS
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, $linha, "=SUM(E".$linha_inicio_ativ.":E".($linha-1).")");
		
			//SUMARIZA OS A1 EQUIVALENTES
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, $linha, "=SUM(F".$linha_inicio_ativ.":F".($linha-1).")");
		
			//SUMARIZA AS HORAS
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(10, $linha, "=SUM(K".$linha_inicio_ativ.":K".($linha-1).")");
	
			//SUMARIZA OS PERCENTUAIS
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(11, $linha, "=SUM(L".$linha_inicio_ativ.":L".($linha-1).")");
			
			$array_linha_sub_ativ[] = $linha;
	
			//APLICA O PERCENTUAL PARA CADA DISCIPLINA		
			for($i=$linha_inicio_ativ+1;$i<=($linha-1);$i++)
			{
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(11, $i, "=K".$i."/K".($linha));
			}
			
			$linha++;						
		}
		
		$sum_docs = "";
		$sum_a1 = "";
		$sum_horas = "";
		
		foreach($array_linha_sub_ativ as $linha_sub_ativ)
		{
			$sum_docs .= "E".$linha_sub_ativ.",";
			$sum_a1 .= "F".$linha_sub_ativ.",";
			$sum_horas .= "K".$linha_sub_ativ.",";
		}
		
		$objPHPExcel->getActiveSheet()->getStyle('D'.$linha.":L".$linha)->getFont()->setBold(true)->setSize(12);
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $linha, "TOTAL DISCIPLINA:");	
		
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, $linha, "=SUM(".$sum_docs.")");
		
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, $linha, "=SUM(".$sum_a1.")");
		
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(10, $linha, "=SUM(".$sum_horas.")");
		
		$array_linha_sub[] = $linha;
		
		$linha++;
}

//TOTALIZA
$objPHPExcel->getActiveSheet()->getStyle("A".($linha+1).":"."K".($linha+1))->getFont()->setBold(TRUE)->setSize(12);
$objPHPExcel->getActiveSheet()->getStyle("A".($linha+1).":"."D".($linha+1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $linha+1, "TOTAL");

$objPHPExcel->getActiveSheet()->mergeCells("A".($linha+1).":D".($linha+1));

$sum_docs = "";
$sum_a1 = "";
$sum_horas = "";

foreach($array_linha_sub as $linha_sub)
{
	$sum_docs .= "E".$linha_sub.",";
	$sum_a1 .= "F".$linha_sub.",";
	$sum_horas .= "K".$linha_sub.",";
}

$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, $linha+1, "=SUM(".$sum_docs.")");

$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, $linha+1, "=SUM(".$sum_a1.")");

$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(10, $linha+1, "=SUM(".$sum_horas.")");

$objWriter->save('php://output');

$objPHPExcel->disconnectWorksheets();

unset($objPHPExcel);

exit;

?>