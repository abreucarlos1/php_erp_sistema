<?php
/*
		Relatório de Lista de Documentos Planejamento
		
		Criado por Carlos Abreu   
		
		local/Nome do arquivo:		
		../planejamento/relatorios/rel_lista_documentos_excel.php
		
		Versão 0 --> VERSÃO INICIAL - 17/01/2018 - Carlos Abreu
		Versão 1 --> Inclusão do tipo Emissão - 24/01/2018 - Carlos Abreu
		Versão 2 --> Mostrar todos os documento e não apenas emitido - 14/02/2018 - Carlos Abreu
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
 
ini_set('max_execution_time', 0); // No time limit
ini_set('post_max_size', '20M');
ini_set('upload_max_filesize', '20M');
ini_set('memory_limit', '1024M');

require_once(implode(DIRECTORY_SEPARATOR,array('..','..','config.inc.php')));

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(616))
{
	nao_permitido();
}

require_once(INCLUDE_DIR."PHPExcel/Classes/PHPExcel.php"); 

$db = new banco_dados();

$sql = "SELECT * FROM ".DATABASE.".codigos_devolucao ";
$sql .= "WHERE reg_del = 0 ";

$db->select($sql,'MYSQL',true);

foreach($db->array_select as $reg_1)
{
	$array_devolucao[$reg_1["codigos_devolucao"]] = $reg_1["descricao_devolucao"];
}

//DISCIPLINAS
$sql = "SELECT id_setor, setor FROM ".DATABASE.".setores, ".DATABASE.".numeros_interno ";
$sql .= "WHERE numeros_interno.reg_del = 0 ";
$sql .= "AND setores.reg_del = 0 ";
$sql .= "AND setores.id_setor = numeros_interno.id_disciplina ";
$sql .= "AND numeros_interno.id_os = '".$_POST["id_os"]."' ";
$sql .= "GROUP BY id_setor ";
$sql .= "ORDER BY setor ";

$db->select($sql,'MYSQL',true);

if ($db->erro != '')
{
	die("Erro ao selecionar os dados das disciplinas: " . $sql);
}

foreach($db->array_select as $reg_setores)
{
	$array_disciplinas[] = $reg_setores["id_setor"];
}

$string_disciplinas = implode("','",$array_disciplinas);

//CODIGO DE EMISSÃO
$sql = "SELECT * FROM ".DATABASE.".codigos_emissao ";
$sql .= "WHERE reg_del = 0 ";

$db->select($sql,'MYSQL',true);

if ($db->erro != '')
{
	die("Erro ao tentar selecionar os dados.".$sql);
}

foreach($db->array_select as $reg_cod_emissao)
{
	$codigos_emissao[$reg_cod_emissao["id_codigo_emissao"]] = $reg_cod_emissao["codigos_emissao"];
	
	$tit_emiss[$reg_cod_emissao["codigos_emissao"]] = $reg_cod_emissao["emissao"];
}

//CODIGO REVISÃO
$sql = "SELECT numerico, alfanumerico FROM ".DATABASE.".codigos_revisao ";
$sql .= "WHERE reg_del = 0 ";

$db->select($sql,'MYSQL',true);

if ($db->erro != '')
{
	die("Erro ao tentar selecionar os dados.".$sql);
}

foreach($db->array_select as $reg_cod_revisao)
{
	$codigos_revisao[$reg_cod_revisao["numerico"]] = $reg_cod_revisao["alfanumerico"];
}

//FORMATOS
$sql = "SELECT * FROM ".DATABASE.".formatos ";
$sql .= "WHERE reg_del = 0 ";

$db->select($sql,'MYSQL',true);

if($db->erro != '')
{
	die("Erro ao tentar selecionar os dados.".$sql);
}

if ($db->numero_registros > 0)
{
	foreach($db->array_select as $reg_formatos)
	{
		$cod_formato[$reg_formatos["id_formato"]] = $reg_formatos["formato"];
	}
}

$sql = "SELECT * FROM ".DATABASE.".setores, ".DATABASE.".numeros_interno, ".DATABASE.".ged_arquivos, ".DATABASE.".ged_versoes, ".DATABASE.".ged_pacotes, ".DATABASE.".grd ";
$sql .= "WHERE numeros_interno.reg_del = 0 ";
$sql .= "AND setores.reg_del = 0 ";
$sql .= "AND ged_arquivos.reg_del = 0 ";
$sql .= "AND ged_versoes.reg_del = 0 ";
$sql .= "AND ged_pacotes.reg_del = 0 ";
$sql .= "AND grd.reg_del = 0 ";
$sql .= "AND numeros_interno.id_numero_interno = ged_arquivos.id_numero_interno ";
$sql .= "AND ged_arquivos.id_ged_arquivo = ged_versoes.id_ged_arquivo ";
$sql .= "AND ged_versoes.id_ged_pacote = ged_pacotes.id_ged_pacote ";
$sql .= "AND ged_pacotes.id_ged_pacote = grd.id_ged_pacote ";	
$sql .= "AND numeros_interno.id_disciplina = setores.id_setor ";	
$sql .= "AND numeros_interno.mostra_relatorios = '1' ";	
$sql .= "AND numeros_interno.id_os = '" . $_POST["id_os"] . "' ";$sql .= "GROUP BY numeros_interno.id_numero_interno, grd.id_grd ";

$db->select($sql,'MYSQL',true);

if ($db->erro != '')
{
	die("Erro ao selecionar os dados dos documentos INT: " . $sql);
}

$array_numdvm = $db->array_select;

foreach($array_numdvm as $reg_numdvm)
{
	$grd_versao[$reg_numdvm["id_numero_interno"]][] = $reg_numdvm["versao_"];
	$grd_revisao_cliente[$reg_numdvm["id_numero_interno"]][] = $reg_numdvm["revisao_cliente"];
	$grd_revisao_alfa[$reg_numdvm["id_numero_interno"]][] = $codigos_revisao[$reg_numdvm["revisao_cliente"]];
	$grd_revisao_dvm[$reg_numdvm["id_numero_interno"]][] = $reg_numdvm["revisao_interna"];
	$grd_num_pacote[$reg_numdvm["id_numero_interno"]][] = $reg_numdvm["numero_pacote"];
	$grd_num_folhas[$reg_numdvm["id_numero_interno"]][] = $reg_numdvm["numero_folhas"];
	$grd_data_emissao[$reg_numdvm["id_numero_interno"]][] = $reg_numdvm["data_emissao"];
	$grd_data[$reg_numdvm["id_numero_interno"]][] = $reg_numdvm["data"];
	$grd_cod_emissao[$reg_numdvm["id_numero_interno"]][] = $reg_numdvm["id_fin_emissao"];
	$grd_data_devolucao[$reg_numdvm["id_numero_interno"]][] = $reg_numdvm["data_devolucao"];
	$grd_status_devolucao[$reg_numdvm["id_numero_interno"]][] = $reg_numdvm["status_devolucao"];		
}

$sql = "SELECT * FROM ".DATABASE.".solicitacao_documentos_detalhes, ".DATABASE.".ordem_servico, ".DATABASE.".atividades, ".DATABASE.".formatos, ".DATABASE.".setores, ".DATABASE.".numeros_interno ";
$sql .= "LEFT JOIN (
  SELECT id_ged_arquivo codArquivo, id_numero_interno codNumdvm, id_ged_versao
  FROM ".DATABASE.".ged_arquivos
  WHERE ged_arquivos.reg_del = 0
) ged_arquivos
ON ged_arquivos.codNumdvm = numeros_interno.id_numero_interno ";

$sql .= "LEFT JOIN ".DATABASE.".ged_versoes ON (ged_arquivos.id_ged_versao = ged_versoes.id_ged_versao AND ged_versoes.reg_del = 0) ";
$sql .= "WHERE numeros_interno.reg_del = 0 ";
$sql .= "AND solicitacao_documentos_detalhes.reg_del = 0 ";
$sql .= "AND ordem_servico.reg_del = 0 ";
$sql .= "AND atividades.reg_del = 0 ";
$sql .= "AND formatos.reg_del = 0 ";
$sql .= "AND setores.reg_del = 0 ";
$sql .= "AND numeros_interno.id_atividade = atividades.id_atividade ";
$sql .= "AND numeros_interno.id_formato = formatos.id_formato ";
$sql .= "AND numeros_interno.id_os = ordem_servico.id_os ";
$sql .= "AND solicitacao_documentos_detalhes.id_numero_interno = numeros_interno.id_numero_interno ";		
$sql .= "AND numeros_interno.id_disciplina = setores.id_setor ";	
$sql .= "AND numeros_interno.id_os = '" . $_POST["id_os"] . "' ";
$sql .= "AND numeros_interno.id_disciplina IN ('" . $string_disciplinas . "') ";
$sql .= "AND numeros_interno.mostra_relatorios = '1' ";
$sql .= "GROUP BY numeros_interno.id_numero_interno ";
$sql .= "ORDER BY setores.setor, numeros_interno.sequencia, numeros_interno.numero_cliente "; 

$db->select($sql,'MYSQL',true);

if ($db->erro != '')
{
	die("Erro ao selecionar os dados dos documentos: " . $db->erro);
}

$qtd_linhas = 0;

foreach($db->array_select as $reg_docs)
{
	$qtd_linhas++;
	
	$array_numdvm['os'][$reg_docs["setor"]][$reg_docs["id_numero_interno"]] = sprintf("%010d",$reg_docs["os"]);
	
	$array_numdvm['numero_interno'][$reg_docs["setor"]][$reg_docs["id_numero_interno"]] = PREFIXO_DOC_GED . sprintf("%05d",$reg_docs["os"]) . "-" . $reg_docs["sigla"] . "-" . $reg_docs["sequencia"]; 
	
	$array_numdvm['numero_cliente'][$reg_docs["setor"]][$reg_docs["id_numero_interno"]] = $reg_docs["numero_cliente"];
	
	if($reg_docs["tag"]!="")
	{
		$array_numdvm['tag'][$reg_docs["setor"]][$reg_docs["id_numero_interno"]] = $reg_docs["tag"];
	}
	else
	{
		if($reg_docs["complemento"]!="")
		{
			$array_numdvm['tag'][$reg_docs["setor"]][$reg_docs["id_numero_interno"]] = $reg_docs["complemento"];
		}
		else
		{
			$array_numdvm['tag'][$reg_docs["setor"]][$reg_docs["id_numero_interno"]] = $reg_docs["descricao"];	
		}
	}
	
	if($reg_docs["tag2"]!="")
	{
		$array_numdvm['tag'][$reg_docs["setor"]][$reg_docs["id_numero_interno"]] .= ' ' .$reg_docs["tag2"];
	}
	
	if($reg_docs["tag3"]!="")
	{
		$array_numdvm['tag'][$reg_docs["setor"]][$reg_docs["id_numero_interno"]] .= ' ' . $reg_docs["tag3"];
	}
	
	if($reg_docs["tag4"]!="")
	{
		$array_numdvm['tag'][$reg_docs["setor"]][$reg_docs["id_numero_interno"]] .= ' ' . $reg_docs["tag4"];
	}
	
	$array_numdvm['formato'][$reg_docs["setor"]][$reg_docs["id_numero_interno"]][] = $reg_docs["formato"];
	
	//se não tiver grd, o numero de folhas vem do numero
	if(count($array_numdvm['numero_folhas'][$reg_docs["setor"]][$reg_docs["id_numero_interno"]])<=0)
	{
		$array_numdvm['numero_folhas'][$reg_docs["setor"]][$reg_docs["id_numero_interno"]][] = $reg_docs["numero_folhas"];
	}
	
	//se não tiver grd, as revisoes vem do ged_versoes
	if(count($array_numdvm['revisao_interno'][$reg_docs["setor"]][$reg_docs["id_numero_interno"]])<=0)
	{
		$array_numdvm['revisao_interno'][$reg_docs["setor"]][$reg_docs["id_numero_interno"]][] = $reg_docs["revisao_interna"];
		$array_numdvm['revisao_cliente'][$reg_docs["setor"]][$reg_docs["id_numero_interno"]][] = $reg_docs["revisao_cliente"];
	}
	
	for($x = 0; $x<count($grd_versao[$reg_docs["id_numero_interno"]]); $x++)
	{
		$qtd_linhas++;
		
		$ret = "";
		
		$array_numdvm['revisao_interno'][$reg_docs["setor"]][$reg_docs["id_numero_interno"]][$x] = $grd_revisao_dvm[$reg_docs["id_numero_interno"]][$x];
		
		$array_numdvm['revisao_cliente'][$reg_docs["setor"]][$reg_docs["id_numero_interno"]][$x] = $grd_revisao_cliente[$reg_docs["id_numero_interno"]][$x];
		
		$array_numdvm['numero_folhas'][$reg_docs["setor"]][$reg_docs["id_numero_interno"]][$x] = $grd_num_folhas[$reg_docs["id_numero_interno"]][$x];
		
		$array_numdvm['grd'][$reg_docs["setor"]][$reg_docs["id_numero_interno"]][$x] = $reg_docs["os"] . "-" . sprintf("%03d",$grd_num_pacote[$reg_docs["id_numero_interno"]][$x]);
		
		$array_numdvm['data_emissao'][$reg_docs["setor"]][$reg_docs["id_numero_interno"]][$x] = mysql_php($grd_data_emissao[$reg_docs["id_numero_interno"]][$x]);
		
		$array_numdvm['tipo_emissao'][$reg_docs["setor"]][$reg_docs["id_numero_interno"]][$x] = $codigos_emissao[$grd_cod_emissao[$reg_docs["id_numero_interno"]][$x]];
		
		$array_numdvm['data_prev'][$reg_docs["setor"]][$reg_docs["id_numero_interno"]][$x] = $ret;
		
		$array_numdvm['data_dev'][$reg_docs["setor"]][$reg_docs["id_numero_interno"]][$x] = mysql_php($grd_data_devolucao[$reg_docs["id_numero_interno"]][$x]);
		
		$array_numdvm['status_dev'][$reg_docs["setor"]][$reg_docs["id_numero_interno"]][$x] = $grd_status_devolucao[$reg_docs["id_numero_interno"]][$x];
		
	}
}


/*
$sql = "SELECT ged_versoes.id_ged_versao, os.os, numeros_interno.numero_cliente, revisao_cliente, setores.sigla, setores.setor, sequencia, revisao_interna, solicitacao_documentos_detalhes.tag, solicitacao_documentos_detalhes.tag2, ";
$sql .= "solicitacao_documentos_detalhes.tag3, solicitacao_documentos_detalhes.tag4, formato, numero_folhas, numero_pacote, data_emissao, codigos_emissao, emissao, ";
$sql .= "data_devolucao, status_devolucao "; 
$sql .= "FROM ".DATABASE.".grd, ".DATABASE.".grd_versoes, ".DATABASE.".ged_pacotes, "; 
$sql .= "".DATABASE.".ged_versoes, ".DATABASE.".ged_arquivos, ".DATABASE.".codigos_emissao, "; 
$sql .= "".DATABASE.".numeros_interno, ".DATABASE.".formatos, ".DATABASE.".setores, ".DATABASE.".solicitacao_documentos_detalhes, ".DATABASE.".OS ";
$sql .= "WHERE grd.reg_del = 0 ";
$sql .= "AND grd_versoes.reg_del = 0 ";
$sql .= "AND ged_versoes.reg_del = 0 ";
$sql .= "AND ged_arquivos.reg_del = 0 ";
$sql .= "AND numeros_interno.reg_del = 0 ";
$sql .= "AND formatos.reg_del = 0 ";
$sql .= "AND setores.reg_del = 0 ";
$sql .= "AND ged_pacotes.reg_del = 0 ";
$sql .= "AND OS.reg_del = 0 ";
$sql .= "AND solicitacao_documentos_detalhes.reg_del = 0 ";
$sql .= "AND OS.id_os = '".$_POST["id_os"]."' ";
$sql .= "AND numeros_interno.id_os = OS.id_os ";
$sql .= "AND numeros_interno.id_disciplina = setores.id_setor ";
$sql .= "AND numeros_interno.id_formato = formatos.id_formato ";
$sql .= "AND numeros_interno.mostra_relatorios = 1 ";
$sql .= "AND numeros_interno.id_numero_interno = ged_arquivos.id_numero_interno ";
$sql .= "AND numeros_interno.id_numero_interno = solicitacao_documentos_detalhes.id_numero_interno ";
$sql .= "AND grd.id_ged_pacote = ged_pacotes.id_ged_pacote ";
$sql .= "AND grd.id_grd = grd_versoes.id_grd ";
$sql .= "AND grd_versoes.id_ged_versao = ged_versoes.id_ged_versao ";
$sql .= "AND ged_versoes.id_ged_arquivo =ged_arquivos.id_ged_arquivo ";
$sql .= "AND ged_versoes.id_fin_emissao = codigos_emissao.id_codigo_emissao ";
//$sql .= "GROUP BY numeros_interno.id_numero_interno "; 
$sql .= "ORDER BY setores.setor, numeros_interno.numero_cliente, versao_ ";
*/

//$db->select($sql, 'MYSQL', true);

//$array_projetos = $db->array_select;

/*
foreach($array_projetos as $regs)
{
		
	$array_proj['os'][$regs["id_ged_versao"]] = sprintf("%010d",$regs["os"]);
	$array_proj['numero_cliente'][$regs["id_ged_versao"]] = $regs["numero_cliente"];
	$array_proj['revcliente'][$regs["id_ged_versao"]] = $regs["revisao_cliente"];
	$array_proj['numeros_interno'][$regs["id_ged_versao"]] = 'INT-'.sprintf("%05d",$regs["os"]).'-'.$regs["sigla"].'-'.$regs["sequencia"];
	$array_proj['revdvm'][$regs["id_ged_versao"]] = $regs["revisao_interna"];
	$array_proj['titulo'][$regs["id_ged_versao"]] = trim($regs["tag"]).' '.trim($regs["tag2"]).' '.trim($regs["tag3"]).' '.trim($regs["tag4"]);
	$array_proj['formato'][$regs["id_ged_versao"]] = $regs["formato"];
	$array_proj['folhas'][$regs["id_ged_versao"]] = $regs["numero_folhas"];
	$array_proj['disciplina'][$regs["id_ged_versao"]] = $regs["setor"];
	$array_proj['grd'][$regs["id_ged_versao"]] = $regs["os"].'-'.sprintf("%03d",$regs["numero_pacote"]);
	$array_proj['tipo_emissao'][$regs["id_ged_versao"]] = $regs["codigos_emissao"];
	$array_proj['data_emissao'][$regs["id_ged_versao"]] = $regs["data_emissao"];
	$array_proj['data_devolucao'][$regs["id_ged_versao"]] = $regs["data_devolucao"];
	$array_proj['status_devolucao'][$regs["id_ged_versao"]] = $regs["status_devolucao"];
	
}
*/

//die(print_r($array_proj,true));

$objPHPExcel = PHPExcel_IOFactory::load("../modelos_excel/lista_documentos_modelo.xls");

$locale = 'pt_br';

$validlocale = PHPExcel_Settings::setlocale($locale);

if (!$validlocale) 
{
	echo 'Unable to set locale to '.$locale." - reverting to en_us<br />\n";
}

// Redirect output to a clients web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="lista_documentos_'.date('dmYhis').'.xlsx"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

$objPHPExcel->setActiveSheetIndex(0);

$linha = 3; //linha

/*
foreach($array_proj['os'] as $id_ged_versao=>$os)
{
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $linha,iconv('ISO-8859-1', 'UTF-8', $os));
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $linha,iconv('ISO-8859-1', 'UTF-8', $array_proj['numero_cliente'][$id_ged_versao]));
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $linha,iconv('ISO-8859-1', 'UTF-8', $array_proj['revcliente'][$id_ged_versao]));
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $linha,iconv('ISO-8859-1', 'UTF-8', $array_proj['numeros_interno'][$id_ged_versao]));
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, $linha,iconv('ISO-8859-1', 'UTF-8', $array_proj['revdvm'][$id_ged_versao]));
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, $linha,iconv('ISO-8859-1', 'UTF-8', $array_proj['titulo'][$id_ged_versao]));
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $linha,iconv('ISO-8859-1', 'UTF-8', $array_proj['formato'][$id_ged_versao]));
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, $linha,iconv('ISO-8859-1', 'UTF-8', $array_proj['folhas'][$id_ged_versao]));
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8, $linha,iconv('ISO-8859-1', 'UTF-8', $array_proj['disciplina'][$id_ged_versao]));
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(9, $linha,iconv('ISO-8859-1', 'UTF-8', $array_proj['grd'][$id_ged_versao]));
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(10, $linha,iconv('ISO-8859-1', 'UTF-8', $array_proj['tipo_emissao'][$id_ged_versao]));
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(11, $linha,iconv('ISO-8859-1', 'UTF-8', mysql_php($array_proj['data_emissao'][$id_ged_versao])));
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(12, $linha,iconv('ISO-8859-1', 'UTF-8', mysql_php($array_proj['data_devolucao'][$id_ged_versao])));
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(13, $linha,iconv('ISO-8859-1', 'UTF-8', $array_proj['status_devolucao'][$id_ged_versao]));
	
	$linha++;
}
*/

/*
foreach($array_projetos as $regs)
{	
	if(!empty($regs["numero_pacote"]))
	{
		$grd = $regs["os"].'-'.sprintf("%03d",$regs["numero_pacote"]);
	}
	else
	{
		$grd = ''; 	
	}		
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $linha,iconv('ISO-8859-1', 'UTF-8', sprintf("%010d",$regs["os"])));
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $linha,iconv('ISO-8859-1', 'UTF-8', $regs["id_numero_interno"]. '----'. $regs["numero_cliente"]));
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $linha,iconv('ISO-8859-1', 'UTF-8', $regs["revisao_cliente"]));
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $linha,iconv('ISO-8859-1', 'UTF-8', 'INT-'.sprintf("%05d",$regs["os"]).'-'.$regs["sigla"].'-'.$regs["sequencia"]));
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, $linha,iconv('ISO-8859-1', 'UTF-8', $regs["revisao_interna"]));
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, $linha,iconv('ISO-8859-1', 'UTF-8', trim($regs["tag"]).' '.trim($regs["tag2"]).' '.trim($regs["tag3"]).' '.trim($regs["tag4"])));
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $linha,iconv('ISO-8859-1', 'UTF-8', $regs["formato"]));
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, $linha,iconv('ISO-8859-1', 'UTF-8', $regs["numero_folhas"]));
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8, $linha,iconv('ISO-8859-1', 'UTF-8', $regs["setor"]));
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(9, $linha,iconv('ISO-8859-1', 'UTF-8', $grd));
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(10, $linha,iconv('ISO-8859-1', 'UTF-8', $regs["codigos_emissao"]));
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(11, $linha,iconv('ISO-8859-1', 'UTF-8', mysql_php($regs["data_emissao"])));
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(12, $linha,iconv('ISO-8859-1', 'UTF-8', mysql_php($regs["data_devolucao"])));
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(13, $linha,iconv('ISO-8859-1', 'UTF-8', $regs["status_devolucao"]));
	
	$linha++;
}
*/

foreach($array_numdvm['numero_interno'] as $setor=>$array_numeros)
{
	foreach($array_numeros as $id_numero_interno=>$numero_dvm)
	{
		//$linha++;
		//contabiliza qual é o maior indice
		$array_maior[0] =  count($array_numdvm['revisao_cliente'][$setor][$id_numero_interno]);
		$array_maior[1] =  count($array_numdvm['revisao_interno'][$setor][$id_numero_interno]);
		$array_maior[2] =  count($array_numdvm['tag'][$setor][$id_numero_interno]);
		
		$max_linha = max($array_maior);
		
		for($x = 0; $x < $max_linha; $x++)
		{		
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $linha, $array_numdvm['os'][$setor][$id_numero_interno]);
	
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $linha, $array_numdvm['numero_cliente'][$setor][$id_numero_interno]);
			
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $linha, $array_numdvm['revisao_cliente'][$setor][$id_numero_interno][$x]);
			
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $linha, $numero_dvm);
			
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, $linha, $array_numdvm['revisao_interno'][$setor][$id_numero_interno][$x]);
			
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, $linha, $array_numdvm['tag'][$setor][$id_numero_interno]);
			
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $linha, $array_numdvm['formato'][$setor][$id_numero_interno][$x]);
			
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, $linha, $array_numdvm['numero_folhas'][$setor][$id_numero_interno][$x]);
			
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8, $linha, $setor);

			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(9, $linha, $array_numdvm['grd'][$setor][$id_numero_interno][$x]);

			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(10, $linha, $array_numdvm['tipo_emissao'][$setor][$id_numero_interno][$x]);

			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(11, $linha, $array_numdvm['data_emissao'][$setor][$id_numero_interno][$x]);

			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(12, $linha, $array_numdvm['data_dev'][$setor][$id_numero_interno][$x]);

			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(13, $linha, $array_numdvm['status_dev'][$setor][$id_numero_interno][$x]);
			
			$linha++;			
		}			
	}

}

$objWriter->save('php://output');

exit;

?>