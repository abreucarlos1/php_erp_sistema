<?php
/*
		Relatório Horas previstas x projetadas
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:		
		../planejamento/relatorios/rel_horas_prevista_projetada.php
		
		Versão 0 --> VERSÃO INICIAL - 02/03/2006
		Versão 1 --> Atualização classe banco de dados - 22/01/2015 - Carlos Abreu
		Versão 2 --> Inclusão dos campos reg_del nas consultas - 20/11/2017 - Carlos Abreu	
*/

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

ini_set('max_execution_time','-1'); // No time limit
ini_set('memory_limit', '-1');

require_once(implode(DIRECTORY_SEPARATOR,array('..','..','config.inc.php')));

require_once(INCLUDE_DIR."PHPExcel/Classes/PHPExcel.php"); 

$linha = 3;

$objPHPExcel = PHPExcel_IOFactory::load("../modelos_excel/horas_previstas_projetadas.xls");

$locale = 'pt_br';

$validlocale = PHPExcel_Settings::setlocale($locale);

if (!$validlocale) 
{
	echo 'Unable to set locale to '.$locale." - reverting to en_us<br />\n";
}

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

// Add some data
$objPHPExcel->setActiveSheetIndex(0);

$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(12, 1, "DATA EMISSÃO: ".date('d/m/Y'));

$db = new banco_dados();

$sql = "SELECT * FROM ".DATABASE.".propostas ";
$sql .= "WHERE propostas.reg_del = 0 ";
$sql .= "AND propostas.id_proposta = '".$_POST["id_proposta"]."' ";

$db->select($sql,'MYSQL',true);

$cont = $db->array_select[0];

//seleciona os recursos atividades
$sql = "SELECT * FROM ".DATABASE.".rh_cargos ";
$sql .= "WHERE rh_cargos.reg_del = 0 ";
$sql .= "ORDER BY grupo";

$db->select($sql,'MYSQL',true);

foreach($db->array_select as $regs)
{
	$array_recs_orc[$regs["id_cargo_grupo"]] = $regs["grupo"];	
}

//seleciona os recursos funcionarios
$sql = "SELECT * FROM ".DATABASE.".funcionarios ";
$sql .= "WHERE situacao = 'ATIVO' ";
$sql .= "AND funcionarios.reg_del = 0 ";
$sql .= "ORDER BY funcionario ";

$db->select($sql,'MYSQL',true);

foreach($db->array_select as $regs)
{
	$array_recs_func[$regs["id_funcionario"]] = $regs["funcionario"];	
}

$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna, $linha, $cont["numero_proposta"]." - ".$cont["descricao_proposta"]);

$linha++;

/*
//Seleciona os recursos
$sql = "SELECT * FROM AE8010, AF8010, AF9010, AFA010 ";
$sql .= "WHERE AE8010.D_E_L_E_T_ = '' ";
$sql .= "AND AFA010.D_E_L_E_T_ = '' ";
$sql .= "AND AF8010.D_E_L_E_T_ = '' ";
$sql .= "AND AF9010.D_E_L_E_T_ = '' ";
$sql .= "AND AE8_RECURS = AFA_RECURS ";
$sql .= "AND AF8_FASE IN ('03','07') ";
$sql .= "AND AF8_PROJET = AFA_PROJET ";
$sql .= "AND AF8_REVISA = AFA_REVISA ";
$sql .= "AND AF8_PROJET = AF9_PROJET ";
$sql .= "AND AF8_REVISA = AF9_REVISA ";
$sql .= "AND AF9_TAREFA = AFA_TAREFA ";
$sql .= $filtro0;
$sql .= "ORDER BY AE8_DESCRI, AF8_PROJET, AF9_START, AF9_FINISH ";

$con0 = $db->select($sql,'MSSQL', true) or die('Erro'.$sql);


foreach($db->array_select as $regs0)
{	
	//OBTEM O CUSTO OR�ADO DA TAREFA
	$sql = "SELECT AF2_CUSTO, AF2_HDURAC FROM AF2010 ";
	$sql .= "WHERE AF2010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF2010.AF2_ORCAME = '".$regs0["AF9_PROJET"]."' ";
	$sql .= "AND AF2010.AF2_CODIGO = '".$regs0["AF9_CODIGO"]."' ";
	
	//FAZ O SELECT
	$cont3 = $db->select($sql,'MSSQL', true);
	
	//se der mensagem de erro, mostra
	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}
	
	$regs_orc = $db->array_select[0];
	
	if($_POST["avanco"])
	{	
		//OBTEM O AVAN�O F�SICO DA TAREFA
		$sql = "SELECT AFF010.AFF_QUANT FROM AFF010 ";
		$sql .= "WHERE AFF010.D_E_L_E_T_ = '' ";
		$sql .= "AND AFF010.AFF_PROJET = '".$regs0["AF9_PROJET"]."' ";
		$sql .= "AND AFF010.AFF_REVISA = '".$regs0["AF9_REVISA"]."' ";
		$sql .= "AND AFF010.AFF_TAREFA = '".$regs0["AF9_TAREFA"]."' ";
		$sql .= "ORDER BY AFF_DATA DESC ";
		
		//FAZ O SELECT
		$cont2 = $db->select($sql,'MSSQL', true);
		
		//se der mensagem de erro, mostra
		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
			
			return $resposta;
		}
		
		$regs_tarefa = $db->array_select[0];		
	
		//VERIFICA SE O AVAN�O � < 100%
		if($regs_tarefa["AFF_QUANT"]/$regs0["AF9_QUANT"]<1)
		{			
			//coluna A
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna, $linha, iconv('ISO-8859-1', 'UTF-8',sprintf("%010d",$regs0["AF8_PROJET"])));
			$objPHPExcel->getActiveSheet()->getStyle("A".$linha)->getNumberFormat()->setFormatCode('0000000000');
			//COLUNA B
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+1, $linha, iconv('ISO-8859-1', 'UTF-8',trim($regs0["AE8_DESCRI"])));
			//COLUNA C
			//$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+2, $linha, iconv('ISO-8859-1', 'UTF-8',trim($regs0["AF9_TAREFA"])));//item
			
			$objPHPExcel->getActiveSheet()->getCell('C'.$linha)->setValueExplicit(iconv('ISO-8859-1', 'UTF-8',trim($regs0["AF9_TAREFA"])), PHPExcel_Cell_DataType::TYPE_STRING);
			$objPHPExcel->getActiveSheet()->getStyle('C'.$linha)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);			
			
			//COLUNA D
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+3, $linha, iconv('ISO-8859-1', 'UTF-8',trim($regs0["AF9_DESCRI"])));
			//COLUNA E
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+4, $linha, mysql_php(protheus_mysql($regs0["AF9_START"])));
			//COLUNA F
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+5, $linha, mysql_php(protheus_mysql($regs0["AF9_FINISH"])));
			//COLUNA G
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+6, $linha, $regs0["AFA_QUANT"]);
			//COLUNA H
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+7, $linha, "=NETWORKDAYS(E".$linha.",F".$linha.")");
			//COLUNA I
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+8, $linha, "=G".$linha."/H".$linha."");
			//COLUNA J
			
			if(!$array_custo_orc[$regs0["AF9_PROJET"]][$regs0["AF9_CODIGO"]])
			{			
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+9, $linha, $regs_orc["AF2_CUSTO"]);
				$objPHPExcel->getActiveSheet()->getStyle("J".$linha)->getNumberFormat()->setFormatCode('R$ #,#00.00');
			}
			else
			{
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+9, $linha, "");	
			}
			
			//COLUNA K
			//$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+10, 4, "=IF(AND(H1<=D4,H1>=C4),G4,\"\")");
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+10, $linha, $regs_orc["AF2_HDURAC"]);
				
			$linha+=1;	
		}
	}
	else
	{
		
		//coluna A
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna, $linha, iconv('ISO-8859-1', 'UTF-8',sprintf("%010d",$regs0["AF8_PROJET"])));
		$objPHPExcel->getActiveSheet()->getStyle("A".$linha)->getNumberFormat()->setFormatCode('0000000000');

		//COLUNA B
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+1, $linha, iconv('ISO-8859-1', 'UTF-8',trim($regs0["AE8_DESCRI"])));
		//COLUNA C
		//$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+2, $linha, iconv('ISO-8859-1', 'UTF-8',trim($regs0["AF9_TAREFA"])));//item
		$objPHPExcel->getActiveSheet()->getCell('C'.$linha)->setValueExplicit(iconv('ISO-8859-1', 'UTF-8',trim($regs0["AF9_TAREFA"])), PHPExcel_Cell_DataType::TYPE_STRING);
		$objPHPExcel->getActiveSheet()->getStyle('C'.$linha)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);			

		
		//COLUNA D
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+3, $linha, iconv('ISO-8859-1', 'UTF-8',trim($regs0["AF9_DESCRI"])));
		//COLUNA E
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+4, $linha, mysql_php(protheus_mysql($regs0["AF9_START"])));
		//COLUNA F
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+5, $linha, mysql_php(protheus_mysql($regs0["AF9_FINISH"])));
		//COLUNA G
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+6, $linha, $regs0["AFA_QUANT"]);
		//COLUNA H
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+7, $linha, "=NETWORKDAYS(E".$linha.",F".$linha.")");
		//COLUNA I
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+8, $linha, "=G".$linha."/H".$linha."");
		//COLUNA J
		
		if(!$array_custo_orc[$regs0["AF9_PROJET"]][$regs0["AF9_CODIGO"]])
		{			
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+9, $linha, $regs_orc["AF2_CUSTO"]);
			$objPHPExcel->getActiveSheet()->getStyle("J".$linha)->getNumberFormat()->setFormatCode('R$ #,#00.00');
		}
		else
		{
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+9, $linha, "");	
		}
		
		//COLUNA K
		//$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+10, 4, "=IF(AND(H1<=D4,H1>=C4),G4,\"\")");
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+10, $linha, $regs_orc["AF2_HDURAC"]);
			
		$linha+=1;				
	}
	
	$array_custo_orc[$regs0["AF9_PROJET"]][$regs0["AF9_CODIGO"]] = $regs_orc["AF2_CUSTO"];

}
*/

$sql = "SELECT * FROM ".DATABASE.".setores, ".DATABASE.".atividades, ".DATABASE.".formatos, ".DATABASE.".escopo_geral, ".DATABASE.".escopo_detalhado ";
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
$sql .= "AND setores.abreviacao NOT IN ('ADM','DES','CMS','CON','COM','FIN','GOB','MON','SUP','MAT','OUT','GER','TIN') ";	
$sql .= "ORDER BY escopo_geral.escopo_geral, setores.setor, atividades.descricao ";

$db->select($sql,'MYSQL',true);

$escopo_geral = "";

$disciplina = "";

$total_prev = 0;

$total_proj = 0;

$array_resumo = $db->array_select;

foreach($array_resumo as $regs)
{
	if($escopo_geral!=$regs["id_escopo_geral"])
	{
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $linha, $regs["escopo_geral"]);			
		
		$linha++;
	}	
	
	if($disciplina!=$regs["id_setor"] || $escopo_geral!=$regs["id_escopo_geral"])
	{
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $linha, $regs["setor"]);			
	
		$linha++;
	}
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, $linha, $regs["codigo"]);
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, $linha, $regs["descricao"]." ".$regs["descricao_escopo"]);
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8, $linha, $regs["formato"]);

	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(9, $linha, number_format($regs["quantidade"],2,",","."));

	//seleciona a tabela de recursos cadastrados
	$sql = "SELECT * FROM ".DATABASE.".recursos ";
	$sql .= "WHERE recursos.reg_del = 0 ";
	$sql .= "AND recursos.id_escopo_geral = '".$regs["id_escopo_geral"]."' ";
	$sql .= "AND recursos.id_escopo_detalhado = '".$regs["id_escopo_detalhado"]."' ";
	$sql .= "AND recursos.id_tarefa = '".$regs["id_atividade"]."' ";
	$sql .= "AND recursos.item_escopo = '".$regs["item"]."' ";
	
	$db->select($sql,'MYSQL',true);
	
	$array_rec = $db->array_select;
		
	foreach($array_rec as $reg_rec)
	{		
		
		if(!empty($reg_rec["id_recurso"]))
		{
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(10, $linha, $array_recs_func[$reg_rec["id_recurso"]]);
		}
		else
		{
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(10, $linha, $array_recs_orc[$reg_rec["id_recurso_orcamento"]]);
		}
		
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(11, $linha, number_format($reg_rec["horas_orcamento"],2,",","."));
		
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(12, $linha, number_format($reg_rec["horas"],2,",","."));
		
		$total_prev += $reg_rec["horas_orcamento"];
		
		$total_proj += $reg_rec["horas"];
		
		$linha++;
	}
	
	$disciplina = $regs["id_setor"];
	
	$escopo_geral = $regs["id_escopo_geral"];
	
	$linha++;		
}

$objPHPExcel->getActiveSheet()->getStyle('A'.$linha.":M".$linha)->getFont()->setBold(true)->setSize(10);

$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(10, $linha, 'TOTAL');

$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(11, $linha, number_format($total_prev,2,",","."));

$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(12, $linha, number_format($total_proj,2,",","."));

// Redirect output to a client's web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="horas_previstas_projetadas_"'.date('dmYHis').'".xlsx"');
header('Cache-Control: max-age=0');

$objWriter->save('php://output');

?>