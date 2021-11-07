<?php
/*
	Relatório de lista de Referência
	
	Criado por Carlos Abreu
	
	local/Nome do arquivo:
	../relatorios/rel_lista_referencias.php
	
	Versão 0 --> Criação - 08/01/2009
	Versão 1 --> alteração banco de dados - 26/09/2014 - Carlos Abreu		
	Versão 2 --> alteração banco de dados - 26/09/2014 - Carlos Abreu
	Versão 3 --> Inclusão de campo Serviço - 30/06/2015 - Carlos Abreu
	Versão 4 --> Inclusão dos campos reg_del nas consultas - 14/11/2017 - Carlos Abreu
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

require_once(implode(DIRECTORY_SEPARATOR,array('..','..','config.inc.php')));

require_once(INCLUDE_DIR."PHPExcel/Classes/PHPExcel.php"); 

ini_set('max_execution_time', 0); // No time limit
ini_set('post_max_size', '20M');
ini_set('upload_max_filesize', '20M');

$db = new banco_dados();

$sql = "SELECT funcionario, id_funcionario FROM ".DATABASE.".funcionarios ";
$sql .= "WHERE funcionarios.reg_del = 0 ";

$db->select($sql,'MYSQL',true);

if ($db->erro != '')
{
	$resposta->addAlert("Erro ao tentar selecionar os dados: ". $db->erro);
}

foreach($db->array_select as $reg)
{
	$array_funcionarios[$reg["id_funcionario"]] = $reg["funcionario"];
}	

$sql = "SELECT * FROM ".DATABASE.".ordem_servico ";
$sql .= "WHERE ordem_servico.id_os = '".$_POST["escolhaos"]."' ";
$sql .= "AND ordem_servico.reg_del = 0 ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}

$cont0 = $db->array_select;

$objPHPExcel = PHPExcel_IOFactory::load("../modelos_excel/modelo_lista_referencias.xls");

$locale = 'pt_br';

$validlocale = PHPExcel_Settings::setlocale($locale);

if (!$validlocale) 
{
	echo 'Unable to set locale to '.$locale." - reverting to en_us<br />\n";
}

// Redirect output to a clients web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="relatorio_lista_referencias_"'.date('His').'".xlsx"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

//1ª folha
$objPHPExcel->setActiveSheetIndex(0);

$linha = 3;

$coluna = 1;

$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna, $linha, 'LISTA DE REFERÊNCIAS DA OS '.sprintf("%05d",$cont0["os"]));

$sql = "SELECT *,ordem_servico.descricao FROM ".DATABASE.".empresas, ".DATABASE.".ordem_servico, ".DATABASE.".documentos_referencia_revisoes, ".DATABASE.".documentos_referencia ";
$sql .= "LEFT JOIN ".DATABASE.".formatos ON (documentos_referencia.id_formato = formatos.id_formato AND formatos.reg_del = 0 ) ";
$sql .= "LEFT JOIN ".DATABASE.".setores ON (setores.id_setor = documentos_referencia.id_disciplina AND setores.reg_del = 0 )";		
$sql .= "LEFT JOIN ".DATABASE.".tipos_documentos_referencia ON (documentos_referencia.id_tipo_documento_referencia = tipos_documentos_referencia.id_tipos_documentos_referencia AND tipos_documentos_referencia.reg_del = 0) ";
$sql .= "LEFT JOIN ".DATABASE.".tipos_referencia ON (tipos_documentos_referencia.id_tipo_referencia = tipos_referencia.id_tipo_referencia AND tipos_referencia.reg_del = 0) ";
$sql .= "WHERE documentos_referencia.id_os = ordem_servico.id_os ";
$sql .= "AND empresas.reg_del = 0 ";
$sql .= "AND ordem_servico.reg_del = 0 ";
$sql .= "AND documentos_referencia.reg_del = 0 ";
$sql .= "AND documentos_referencia_revisoes.reg_del = 0 ";
$sql .= "AND documentos_referencia.id_documento_referencia_revisoes = documentos_referencia_revisoes.id_documentos_referencia_revisoes ";
$sql .= "AND ordem_servico.id_empresa = empresas.id_empresa ";
$sql .= "AND ordem_servico.id_os = '" . $cont0["id_os"] . "' ";
$sql .= "ORDER BY documentos_referencia_revisoes.numero_grd_cliente ASC , setores.setor ASC, tipos_referencia.tipo_referencia, documentos_referencia.numero_registro ";

$db->select($sql,'MYSQL',true);

if ($db->erro != '')
{
	exit("Não foi possível a seleção dos dados.".$sql);
}

$i = 0;

foreach($db->array_select as $regs)
{
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna, $linha+$i+4, $regs["numero_grd_cliente"]);

	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+1, $linha+$i+4, mysql_php($regs["data_registro"]));

	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+2, $linha+$i+4, $regs["numero_registro"]);

	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+3, $linha+$i+4, $regs["numero_documento"]);

	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+4, $linha+$i+4, $regs["titulo"]);

	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+5, $linha+$i+4, $regs["setor"]);

	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+6, $linha+$i+4, $regs["versao_documento"]);

	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+7, $linha+$i+4, $regs["formato"]);
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+8, $linha+$i+4, mysql_php($regs["data_inclusao"]));

	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna+9, $linha+$i+4, $array_funcionarios[$regs["id_autor"]]);

	$i++;
}

$objPHPExcel->setActiveSheetIndex(0);

$objWriter->save('php://output');

exit;

?>