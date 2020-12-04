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

require_once(implode(DIRECTORY_SEPARATOR,array('..','..','config.inc.php')));
 
require_once(INCLUDE_DIR."PHPExcel/Classes/PHPExcel.php");
$objPHPExcel = PHPExcel_IOFactory::load("../modelos_excel/relatorio_nc_modelo.xls");

$locale = 'pt_br';

$validlocale = PHPExcel_Settings::setlocale($locale);

if (!$validlocale) 
{
	echo 'Unable to set locale to '.$locale." - reverting to en_us<br />\n";
}

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

$db = new banco_dados();

switch($_POST["filtro"])
{
	//geral
	case 0:
		$filtro = "";
	break;
	
	//pendentes
	case 1:
		//$filtro1 = " >= '".date('Y-m-d')."' ";
		$filtro = "AND nao_conformidades.status = 0 ";
	break;
	
	//em análise
	case 2:
		$filtro1 = "AND nao_conformidades.data_criacao >= '".php_mysql(calcula_data(date('d/m/Y'),'sub','day',15))."' ";
		$filtro = "AND nao_conformidades.status = 1 ";
	break;
	
	//atrasados
	case 3:
		$filtro1 = "AND nao_conformidades.data_criacao < '".php_mysql(calcula_data(date('d/m/Y'),'sub','day',15))."' ";
		$filtro = "AND nao_conformidades.status = 1 ";
	break;
	
	//encerrados
	case 4:
		$filtro = "AND nao_conformidades.status = 2 ";
	break;		
}

$sql = "SELECT * FROM ".DATABASE.".setores, ".DATABASE.".nao_conformidades  ";
$sql .= "LEFT JOIN ".DATABASE.".ordem_servico ON (nao_conformidades.id_os = ordem_servico.id_os) ";
$sql .= "JOIN( SELECT id_tipo_origem idTipoOrigem, tipo_origem FROM ".DATABASE.".tipo_origem) tpOrigem ON idTipoOrigem = id_tipo_origem ";
$sql .= "JOIN( SELECT id_tipo_documento idTipoDocumento, tipo_documento, sufixo_codigo FROM ".DATABASE.".tipos_documentos_planos_acao) tpDocumentos ON idTipoDocumento = id_tipo_documento ";
$sql .= "WHERE nao_conformidades.nao_conformidade_delete = 0 ";
$sql .= "AND nao_conformidades.id_setor = setores.id_setor ";

$sql .= $filtro;
$sql .= $filtro1;

//FAZ O SELECT
$res0 = $db->select($sql,'MYSQL');

//se der mensagem de erro, mostra
if($db->erro!='')
{
	die($db->erro);
}

$objPHPExcel->setActiveSheetIndex(0);

$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, 3, 'Data de Emissão: '.date('d/m/Y'));

$linha = 6;

$statusPlanoAcao = array(0 => 'PENDENTE',1 => 'EM ANDAMENTO', 2 => 'ENCERRADO');

$styleArray = array(
  'borders' => array(
    'allborders' => array(
      'style' => PHPExcel_Style_Border::BORDER_THIN
    )
  )
);

while($cont0 = mysqli_fetch_assoc($res0))
{
	$textoCompletoTipoOrigem = in_array($cont0["id_tipo_origem"], array(1,3,7)) ? $cont0["tipo_origem"].': '.$cont0['desc_outros'].$cont0['desc_outros_cliente'].$cont0['desc_outros_fornec'] : $cont0["tipo_origem"];
	
	$objPHPExcel->getActiveSheet()->insertNewRowBefore($linha+1,1);
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $linha, $cont0["cod_nao_conformidade"]);
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $linha, $textoCompletoTipoOrigem);
	
	$objPHPExcel->getActiveSheet()->getRowDimension($linha)->setRowHeight(-1);
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $linha, $cont0["sufixo_codigo"]);
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $linha, $cont0["setor"]);
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, $linha, mysql_php($cont0["data_criacao"]));
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, $linha, $cont0["desc_nao_conformidade"]);
	
	$sql = "SELECT * FROM ".DATABASE.".setores  ";
	$sql .= "WHERE setores.id_setor = '".$cont0["id_disciplina"]."' ";
	
	//FAZ O SELECT
	$cont1 = $db->select($sql,'MYSQL');
	
	//se der mensagem de erro, mostra
	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}
	
	$regs1 = mysqli_fetch_assoc($cont1);

	switch($cont0["procedente"])
	{
		case 0:
			$procedente = "";
		break;
		
		case 1:
			$procedente = "SIM";
		break;
		
		case 2:
			$procedente = "NÃO";
		break;
	}
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $linha, $procedente);
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, $linha, $statusPlanoAcao[$cont0['status']]);
	
	//Pego a última ação a ser tomada para saber o prazo final
	$sql = "SELECT id_nao_conformidade, MAX(prazo) prazo FROM ".DATABASE.".planos_acoes_complementos WHERE id_nao_conformidade = ".$cont0['id_nao_conformidade']." GROUP BY id_nao_conformidade ";
	
	$db->select($sql, 'MYSQL', true);
		
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8, $linha, mysql_php($db->array_select[0]['prazo']));
	
	$linha++;
}

// Redirect output to a clients web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="nc_"'.date('His').'".xlsx"');
header('Cache-Control: max-age=0');

$objWriter->save('php://output');
?>