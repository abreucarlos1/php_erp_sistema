<?php
/*
	Relatório de A1 equivalente
	Criado por Carlos Abreu  
	
	Versão 0 --> VERSÃO INICIAL : 25/06/2015
	Versão 1 --> Inclusão dos campos reg_del nas consultas - 17/11/2017 - Carlos Abreu
*/

error_reporting(0);
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

$filtro0 = "";

$array_os_ano = NULL;


//filtra a os (-1 TODAS AS OS)
if($_POST["escolhaos"]!=-1)
{
	//$filtro0 .= "AND AF8_PROJET = '".sprintf("%010d",$_POST["escolhaos"])."' ";

	$filtro0 .= "AND os = '".$_POST["escolhaos"]."' ";
}

if($_POST["escolhacoord"]!=-1)
{
	//$filtro0 .= "AND (AF8_COORD1 = '".$_POST["escolhacoord"]."' ";
	//$filtro0 .= "OR AF8_COORD2 = '".$_POST["escolhacoord"]."') ";

	$filtro0 .= "AND (id_cod_coord = '".$_POST["escolhacoord"]."' ";
	$filtro0 .= "OR id_coord_aux = '".$_POST["escolhacoord"]."') ";

}

/*
//Seleciona as PROJETOS
$sql = "SELECT * FROM AF8010 WITH(NOLOCK), SA1010 WITH(NOLOCK) ";
$sql .= "WHERE AF8010.D_E_L_E_T_ = '' ";
$sql .= "AND SA1010.D_E_L_E_T_ = '' ";
$sql .= "AND AF8010.AF8_CLIENT = SA1010.A1_COD ";
$sql .= "AND AF8010.AF8_LOJA = SA1010.A1_LOJA ";
$sql .= "AND AF8_PROJET > 0000003100 ";
$sql .= "AND AF8_FASE NOT IN ('01','06','08','09', '10', '13', '17', '18') ";
$sql .= $filtro0;
$sql .= "ORDER BY AF8_DATA, AF8_PROJET ";

$db->select($sql,'MSSQL', true);

if($db->erro!='')
{
	die($db->erro);
}

$array_proj = $db->array_select;
*/

$sql = "SELECT * FROM ".DATABASE.".ordem_servico ";
$sql .= "WHERE ordem_servico.reg_del = 0 ";
$sql .= $filtro0;


$db->select($sql,'MYSQL', true);

if($db->erro!='')
{
	die($db->erro);
}

$array_proj = $db->array_select;

foreach($array_proj as $regs1)
{
	//$array_os_ano[$regs1["AF8_PROJET"]] = substr($regs1["AF8_DATA"],0,4);
	
	//SELECIONA A ULTIMA EMISSÃO DOS DOCUMENTOS	
	$sql = "SELECT * FROM ".DATABASE.".ordem_servico, ".DATABASE.".setores, ".DATABASE.".numeros_interno, ".DATABASE.".ged_arquivos, ".DATABASE.".ged_versoes, ".DATABASE.".ged_pacotes, ".DATABASE.".formatos ";
	//$sql .= "WHERE os.os = '" . (int)$regs1["AF8_PROJET"] . "' ";
	$sql .= "WHERE ordem_servico.id_os = '".$regs1["id_os"]."' ";
	$sql .= "AND numeros_interno.reg_del = 0 ";
	$sql .= "AND ged_arquivos.reg_del = 0 ";
	$sql .= "AND ged_versoes.reg_del = 0 ";
	$sql .= "AND ged_pacotes.reg_del = 0 ";
	$sql .= "AND ordem_servico.reg_del = 0 ";
	$sql .= "AND setores.reg_del = 0 ";
	$sql .= "AND formatos.reg_del = 0 ";
	$sql .= "AND numeros_interno.id_formato = formatos.id_formato ";
	$sql .= "AND numeros_interno.id_disciplina = setores.id_setor ";	
	$sql .= "AND setores.abreviacao IN ('AUT','CIV','EBP','ELE','EST','INS','MEC','PDM','SEG','TUB','VAC') ";	
	$sql .= "AND ordem_servico.id_os = numeros_interno.id_os ";
	$sql .= "AND numeros_interno.id_numero_interno = ged_arquivos.id_numero_interno ";
	$sql .= "AND ged_arquivos.id_ged_arquivo = ged_versoes.id_ged_arquivo ";
	$sql .= "AND ged_arquivos.documento_interno = 1 "; //somente documentos internos
	$sql .= "AND ged_versoes.id_ged_pacote = ged_pacotes.id_ged_pacote ";
	$sql .= "GROUP BY ged_arquivos.id_ged_arquivo, setores.setor ";
	$sql .= "ORDER BY setores.setor ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		die($db->erro);
	}
	
	$docs_emitidos = array();
	
	foreach($db->array_select as $cont0)
	{
		//monta array com a qtd de documentos emitidos (a1 equivalente)
		//$docs_emitidos[trim($regs1["AF8_PROJET"])][$cont0["id_setor"]] += ($cont0["numero_folhas"]*$cont0["fator_equivalente"]);
		$docs_emitidos[trim($cont0["os"])][$cont0["id_setor"]] += ($cont0["numero_folhas"]*$cont0["fator_equivalente"]);	
		
		//monta o array de disciplinas
		$discipl[$cont0["setor"]] = $cont0["id_setor"];

	}
	
	//SOMA AS HORAS PELAS OS/DISCIPLINAS
	$sql = "SELECT *, SUM(TIME_TO_SEC(hora_normal)) AS HN, SUM(TIME_TO_SEC(hora_adicional)) AS HA, SUM(TIME_TO_SEC(hora_adicional_noturna)) AS HAN FROM ".DATABASE.".ordem_servico, ".DATABASE.".apontamento_horas, ".DATABASE.".setores ";
	$sql .= "WHERE apontamento_horas.id_os = ordem_servico.id_os ";
	$sql .= "AND ordem_servico.reg_del = 0 ";
	$sql .= "AND apontamento_horas.reg_del = 0 ";
	$sql .= "AND setores.reg_del = 0 ";
	//$sql .= "AND ordem_servico.os = '".intval($regs1["AF8_PROJET"])."' ";
	$sql .= "AND ordem_servico.os = '".$regs1["os"]."' ";
	$sql .= "AND apontamento_horas.id_setor = setores.id_setor ";	
	$sql .= "AND setores.abreviacao IN ('AUT','CIV','EBP','ELE','EST','INS','MEC','PDM','SEG','TUB','VAC') ";	
	$sql .= "GROUP BY setores.setor ";
	$sql .= "ORDER BY setores.setor ";
	
	$db->select($sql, 'MYSQL',true);

	if ($db->erro != '')
	{
		exit("Não foi possível a seleção dos dados.".$sql);
	}

	foreach($db->array_select as $cont)
	{
		//monta array com a soma das horas
		//$sub_total_horas[$regs1["AF8_PROJET"]][$cont["id_setor"]] += ($cont["HN"]+$cont["HA"]+$cont["HAN"]);
		$sub_total_horas[$regs1["os"]][$cont["id_setor"]] += ($cont["HN"]+$cont["HA"]+$cont["HAN"]);
		
		//monta array com as disciplinas
		$discipl[$cont["setor"]] = $cont["id_setor"];
	}
	
}

ksort($discipl);

$objPHPExcel = PHPExcel_IOFactory::load("../modelos_excel/produtividade_A1_equiv.xls");

$locale = 'pt_br';

$validlocale = PHPExcel_Settings::setlocale($locale);

if (!$validlocale) 
{
	echo 'Unable to set locale to '.$locale." - reverting to en_us<br />\n";
}

// Redirect output to a clients web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="prod_a1_"'.date('His').'".xlsx"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

//1ª folha
$objPHPExcel->setActiveSheetIndex(0);

$linha = 2;

$coluna = 2;

$col = NULL;

//imprime os titulos
foreach($discipl as $disciplina=>$codigo)
{
	//disciplina
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna, 1, $disciplina);

	$objPHPExcel->getActiveSheet()->mergeCells(num2alfa($coluna)."1:".num2alfa($coluna+2)."1");

	//texto
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna, 2, 'Horas');

	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+1, 2, 'A1 Eq. Emitidos');
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+2, 2, 'HH/A1 Equivalente');

	$col[$codigo] = $coluna;

	$coluna += 3;
}

reset($discipl);

//TOTALIZAÇÃO GERAL
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna, 1, 'TOTAL');

$objPHPExcel->getActiveSheet()->mergeCells(num2alfa($coluna)."1:".num2alfa($coluna+2)."1");

//texto
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna, 2, 'Horas');

$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+1, 2, 'A1 Eq. Emitidos');

$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+2, 2, 'A1 Equivalente');

foreach($array_os_ano as $projeto=>$ano)
{
	$soma_horas = '';
	
	$soma_docs = '';

	//OS
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $linha+1, $projeto);

	//ano
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $linha+1, $ano);
	
	foreach($sub_total_horas[$projeto] as $disciplina=>$horas)
	{
		//horas		
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col[$disciplina], $linha+1, $horas/3600);//$sub_total_horas[$projeto][$codigo]

		$objPHPExcel->getActiveSheet()->getStyle(num2alfa($col[$disciplina]).$linha+1)->getNumberFormat()->setFormatCode('0,00');

		//docs emitidos	
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col[$disciplina]+1, $linha+1, $docs_emitidos[$projeto][$disciplina]);
		
		//A1 equivalente	
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col[$disciplina]+2, $linha+1, "=".num2alfa($col[$disciplina]).($linha+1)."/".num2alfa($col[$disciplina]+1).($linha+1));

	}

	for($i=2;$i<$coluna;$i+=3)
	{
		$soma_horas .= num2alfa($i).($linha+1).",";
		$soma_docs .= num2alfa($i+1).($linha+1).",";
	}	
	
	//totaliza horas		
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna, $linha+1, "=SUM(".$soma_horas.")");

	$objPHPExcel->getActiveSheet()->getStyle(num2alfa($coluna).$linha+1)->getNumberFormat()->setFormatCode('0,00');
	
	//totaliza docs		
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+1, $linha+1, "=SUM(".$soma_docs.")");

	$objPHPExcel->getActiveSheet()->getStyle(num2alfa($coluna+1).$linha+1)->getNumberFormat()->setFormatCode('0,00');
	
	//A1 equivalente		
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+2, $linha+1, "=".num2alfa($coluna).($linha+1)."/".num2alfa($coluna+1).($linha+1));

	$objPHPExcel->getActiveSheet()->getStyle(num2alfa($coluna+2).$linha+1)->getNumberFormat()->setFormatCode('0,00');
	
	$linha+=1;
}

//totaliza global
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $linha+1, 'TOTAL');

//Monta os sub-totais
for($j=2;$j<$coluna+3;$j+=3)
{
	//horas
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($j, $linha+1, "=SUM(".num2alfa($j)."3:".num2alfa($j).($linha).")");		

	//docs
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($j+1, $linha+1, "=SUM(".num2alfa($j+1)."3:".num2alfa($j+1).($linha).")");

	//A1 equivalente
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($j+2,$linha+1,"=".num2alfa($j).($linha+1)."/".num2alfa($j+1).($linha+1));

}

$objPHPExcel->setActiveSheetIndex(0);

$objWriter->save('php://output');

exit;

?>