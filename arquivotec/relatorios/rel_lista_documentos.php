<?php
/*
	  GED / Lista de documentos do Projeto	
	  
	  Criado por Carlos Abreu / Otávio Pamplona
	  
	  local/Nome do arquivo:
	  ../relatorios/ged_lista_documentos.php
	  
	  data de criação: 12/02/2008
	  
	  Versão 0 --> VERSÃO INICIAL
	  Versão 1 --> Estatistica de documentos - 04/09/2012 - Carlos Abreu
	  Versão 2 --> formatos nas estatisticas  - 20/12/2012 - Por Carlos Abreu
	  Versão 3 --> Atualização banco de dados - 26/09/2014 - Carlos Abreu
	  Versão 4 --> Inclusão de campo Serviço - 30/06/2015 - Carlos Eduardo 
	  Versão 5 --> unificação das tabelas numero_cliente e numeros_interno - 10/05/2017 - Carlos Abreu
	  Versão 6 --> Inclusão dos campos reg_del nas consultas - 14/11/2017 - Carlos Abreu
*/
require_once(implode(DIRECTORY_SEPARATOR,array('..','..','config.inc.php')));

require_once(INCLUDE_DIR."include_pdf.inc.php");

ini_set("memory_limit","-1");
ini_set('max_execution_time', '0'); // No time limit
ini_set('post_max_size', '200M');
ini_set('upload_max_filesize', '200M');

$db = new banco_dados;

$sql = "SELECT codigos_devolucao, descricao_devolucao FROM ".DATABASE.".codigos_devolucao ";
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

if($_POST["disciplina"])
{
	$sql .= "AND setores.id_setor = '".$_POST["disciplina"]."' ";
}

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
$sql = "SELECT id_codigo_emissao, codigos_emissao, emissao  FROM ".DATABASE.".codigos_emissao ";
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
$sql = "SELECT id_formato, formato FROM ".DATABASE.".formatos ";
$sql .= "WHERE reg_del = 0 ";

if(isset($_POST["id_formato"]))
{
	$sql .= "AND formatos.id_formato = '" . $_POST["id_formato"] . "' ";
}

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
$sql .= "AND numeros_interno.id_os = '" . $_POST["id_os"] . "' ";

if($_POST["disciplina"])
{
	$sql .= "AND numeros_interno.id_disciplina = '" . $_POST["disciplina"] . "' ";
}

if($_POST["id_atividade"])
{
	$sql .= "AND numeros_interno.id_atividade = '" . $_POST["id_atividade"] . "' ";
}

if($_POST["id_formato"])
{
	$sql .= "AND numeros_interno.id_formato = '" . $_POST["id_formato"] . "' ";
}

if($_POST["chk_periodo"])
{
	$sql .= "AND grd.data_emissao BETWEEN '" . php_mysql($_POST["dataini"]) . "' AND '" . php_mysql($_POST["datafim"]) . "' ";
}

//ALTERADO - CARLOS ABREU - 09/08/2012
if($_POST["chk_periodo_dev"])
{
	$sql .= "AND ged_versoes.data_devolucao BETWEEN '" . php_mysql($_POST["dataini_dev"]) . "' AND '" . php_mysql($_POST["datafim_dev"]) . "' ";
}	

if($_POST["chk_sdev"]) //Sem devolução
{
	$sql .= "AND ged_versoes.data_devolucao = '0000-00-00' ";
}

if($_POST["devolucao"]!="") //status Devolução
{
	$sql .= "AND ged_versoes.status_devolucao = '".$_POST["devolucao"]."' " ;
}

//Filtro por Finalidade, somente quando resumido
//incluido em 02/12/2011
if($_POST["chk_resumido"]=="1" && $_POST["id_finalidade"]!="")
{
	$sql .= "AND ged_versoes.id_fin_emissao = '".$_POST["id_finalidade"]."' " ;
}

$sql .= "GROUP BY numeros_interno.id_numero_interno, grd.id_grd ";

$db->select($sql,'MYSQL',true);

if ($db->erro != '')
{
	die("Erro ao selecionar os dados dos documentos: " . $sql);
}

$array_numdvm = $db->array_select;

foreach($db->array_select as $reg_numdvm)
{
	if($_POST["chk_resumido"]=="1")
	{
		//Alteração 24/11/2014
		//Correção da busca pela finalidade no relatório resumido
		//Carlos Eduardo
		$sql = "SELECT * FROM ( ";		
			$sql .= "SELECT ged_versoes.id_fin_emissao, ged_pacotes.id_ged_pacote, numeros_interno.id_numero_interno FROM ".DATABASE.".setores, ".DATABASE.".ged_pacotes, ".DATABASE.".grd, ".DATABASE.".ged_arquivos, ".DATABASE.".ged_versoes, ".DATABASE.".numeros_interno ";
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
			
			$sql .= "AND numeros_interno.id_numero_interno = '" . $reg_numdvm["id_numero_interno"] . "' ";
			
			if($_POST["disciplina"])
			{
				$sql .= "AND numeros_interno.id_disciplina = '" . $_POST["disciplina"] . "' ";
			}			
	
			if($_POST["id_formato"])
			{
				$sql .= "AND numeros_interno.id_formato = '" . $_POST["id_formato"] . "' ";
			}
	
			if($_POST["chk_periodo"])
			{
				$sql .= "AND grd.data_emissao BETWEEN '" . php_mysql($_POST["dataini"]) . "' AND '" . php_mysql($_POST["datafim"]) . "' ";
			}
			
			//ALTERADO - CARLOS ABREU - 09/08/2012
			if($_POST["chk_periodo_dev"])
			{
				$sql .= "AND ged_versoes.data_devolucao BETWEEN '" . php_mysql($_POST["dataini_dev"]) . "' AND '" . php_mysql($_POST["datafim_dev"]) . "' ";
			}
			
			if($_POST["chk_sdev"]) //Sem devolução
			{
				$sql .= "AND ged_versoes.data_devolucao = '0000-00-00' ";
			}
			
			$sql .= "ORDER BY ged_pacotes.numero_pacote DESC ";
		
			$sql .= "LIMIT 1 ";
			
		$sql .=") AS BUSCA_PRINCIPAL ";

		//Filtro por finalidade
		//02/12/2011
		if($_POST["id_finalidade"]!="") //finalidade
		{
			$sql .= "WHERE BUSCA_PRINCIPAL.id_fin_emissao = '".$_POST["id_finalidade"]."' ";
		}

		$db->select($sql,'MYSQL',true);
		
		$reg_ultimopac = $db->array_select[0];
	}
	
	if($reg_ultimopac["id_ged_pacote"]==$reg_numdvm["id_ged_pacote"] || $_POST["chk_resumido"]!="1")
	{
		if($_POST["chk_resumido"]=="1" && !in_array($reg_ultimopac["id_ged_pacote"],$ultimo_pct))
		{			
			$ultimo_pct[] = $reg_ultimopac["id_numero_interno"];
		}
		
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
}

if($_POST["chk_emitidos"]=="1")
{
	//ALTERADO EM 02/01/2018 - Carlos Abreu
	/*
	$sql = "SELECT * FROM ".DATABASE.".numeros_interno, ".DATABASE.".OS, ".DATABASE.".atividades, ".DATABASE.".formatos, 
				(SELECT
				    id_solicitacao_documentos_detalhe,id_solicitacao_documento,id_atividade,item_pedido,id_disciplina,tipodoc,finalidade,tag,tag2,tag3,tag4,area,setor,obs,id_numero_interno,id_formato,folhas,versao_documento,servico_id
				 FROM 
				 	".DATABASE.".solicitacao_documentos_detalhes 
				 WHERE 
				 	solicitacao_documentos_detalhes.reg_del = 0) solicitacao_documentos_detalhes,
   				".DATABASE.".ged_versoes,
   				".DATABASE.".ged_arquivos, 
   				".DATABASE.".setores, 
   				".DATABASE.".grd, 
   				".DATABASE.".grd_versoes ";
	$sql .= "WHERE numeros_interno.reg_del = 0 ";
	$sql .= "AND OS.reg_del = 0 ";
	$sql .= "AND atividades.reg_del = 0 ";
	$sql .= "AND formatos.reg_del = 0 ";
	$sql .= "AND ged_arquivos.reg_del = 0 ";
	$sql .= "AND ged_versoes.reg_del = 0 ";
	$sql .= "AND grd.reg_del = 0 ";
	$sql .= "AND grd_versoes.reg_del = 0 ";
	$sql .= "AND numeros_interno.id_atividade = atividades.id_atividade ";
	$sql .= "AND numeros_interno.id_numero_interno = ged_arquivos.id_numero_interno ";			
	$sql .= "AND ged_arquivos.id_ged_arquivo = ged_versoes.id_ged_arquivo ";
	*/
	
	$sql = "SELECT * FROM ".DATABASE.".solicitacao_documentos_detalhes, ".DATABASE.".numeros_interno, ".DATABASE.".ordem_servico, ".DATABASE.".atividades, ".DATABASE.".formatos, 
   				".DATABASE.".ged_versoes,
   				".DATABASE.".ged_arquivos, 
   				".DATABASE.".setores, 
   				".DATABASE.".grd, 
   				".DATABASE.".grd_versoes ";
	$sql .= "WHERE numeros_interno.reg_del = 0 ";
	$sql .= "AND solicitacao_documentos_detalhes.reg_del = 0 ";
	$sql .= "AND ordem_servico.reg_del = 0 ";
	$sql .= "AND atividades.reg_del = 0 ";
	$sql .= "AND formatos.reg_del = 0 ";
	$sql .= "AND ged_arquivos.reg_del = 0 ";
	$sql .= "AND ged_versoes.reg_del = 0 ";
	$sql .= "AND grd.reg_del = 0 ";
	$sql .= "AND grd_versoes.reg_del = 0 ";
	$sql .= "AND numeros_interno.id_atividade = atividades.id_atividade ";
	$sql .= "AND numeros_interno.id_numero_interno = ged_arquivos.id_numero_interno ";			
	$sql .= "AND ged_arquivos.id_ged_arquivo = ged_versoes.id_ged_arquivo ";
	
	//acrescentado por Carlos Abreu - 28/07/2010		
	if(count($ultimo_pct)>0)
	{
		$string_pacotes = implode("','",$ultimo_pct);
		
		$sql .= "AND numeros_interno.id_numero_interno IN ('" . $string_pacotes . "') ";
	}
	
	$sql .= "AND ged_versoes.id_ged_versao = grd_versoes.id_ged_versao ";
	$sql .= "AND grd_versoes.id_grd = grd.id_grd ";
	$sql .= "AND numeros_interno.id_formato = formatos.id_formato ";
	$sql .= "AND numeros_interno.id_os = ordem_servico.id_os ";
	$sql .= "AND numeros_interno.id_os = '" . $_POST["id_os"] . "' ";	
	$sql .= "AND solicitacao_documentos_detalhes.id_numero_interno = numeros_interno.id_numero_interno ";
	$sql .= "AND numeros_interno.id_disciplina = setores.id_setor ";
	
	if($_POST["disciplina"])
	{
		$sql .= "AND numeros_interno.id_disciplina = '" . $_POST["disciplina"] . "' ";
	}
	
	if($_POST["id_formato"])
	{
		$sql .= "AND numeros_interno.id_formato = '" . $_POST["id_formato"] . "' ";
	}
	
	if($_POST["chk_periodo"])
	{
		$sql .= "AND grd.data_emissao BETWEEN '" . php_mysql($_POST["dataini"]) . "' AND '" . php_mysql($_POST["datafim"]) . "' ";
	}
	
	//ALTERADO - CARLOS ABREU - 09/08/2012
	if($_POST["chk_periodo_dev"])
	{
		$sql .= "AND ged_versoes.data_devolucao BETWEEN '" . php_mysql($_POST["dataini_dev"]) . "' AND '" . php_mysql($_POST["datafim_dev"]) . "' ";
	}
	
	if($_POST["chk_sdev"]) //Sem devolução
	{
		$sql .= "AND ged_versoes.data_devolucao = '0000-00-00' ";
	}
	
	if($_POST["devolucao"]!="") //status Devolução
	{
		$sql .= "AND ged_versoes.status_devolucao = '".$_POST["devolucao"]."' " ;
	}

	if($_POST["servico"]!="") //status Devolução
	{
		$sql .= "AND solicitacao_documentos_detalhes.servico_id = '".$_POST["servico"]."' " ;
	}
	
	$sql .= "AND numeros_interno.id_disciplina IN ('" . $string_disciplinas . "') ";		
	$sql .= "AND numeros_interno.mostra_relatorios = '1' ";
	$sql .= "GROUP BY numeros_interno.id_numero_interno ";		
	$sql .= "ORDER BY setores.setor, ordem_servico.os, numeros_interno.sequencia ";
}
else
{
	
	$sql = "SELECT * FROM ".DATABASE.".solicitacao_documentos_detalhes, ".DATABASE.".ordem_servico, ".DATABASE.".atividades, ".DATABASE.".formatos, ".DATABASE.".setores, ".DATABASE.".numeros_interno ";
	
	//Alterado em 29/10/2014
	//Carlos Máximo
	$sql .=
	"LEFT JOIN
	(
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
	
	if($_POST["disciplina"])
	{
		$sql .= "AND numeros_interno.id_disciplina = '" . $_POST["disciplina"] . "' ";
	}
	else
	{
		$sql .= "AND numeros_interno.id_disciplina IN ('" . $string_disciplinas . "') ";
	}
	
	if($_POST["id_atividade"])
	{
		$sql .= "AND numeros_interno.id_atividade = '" . $_POST["id_atividade"] . "' ";
	}
	
	if($_POST["id_formato"])
	{
		$sql .= "AND numeros_interno.id_formato = '" . $_POST["id_formato"] . "' ";
	}		

	if($_POST["servico"]!="") //status Devolução
	{
		$sql .= "AND solicitacao_documentos_detalhes.servico_id = '".$_POST["servico"]."' " ;
	}
	
	$sql .= "AND numeros_interno.mostra_relatorios = '1' ";
	
	$sql .= "GROUP BY numeros_interno.id_numero_interno ";
	
	if($_POST["ordem_lista_documentos"]=="numero_cliente")
	{
		$sql .= "ORDER BY setores.setor, ordem_servico.os, numeros_interno.numero_cliente, numeros_interno.sequencia "; 
	}
	else
	{
		$sql .= "ORDER BY setores.setor, ordem_servico.os, numeros_interno.sequencia "; 
	}
}

$db->select($sql,'MYSQL',true);

if ($db->erro != '')
{
	die("Erro ao selecionar os dados dos documentos: " . $db->erro);
}

$numero_docs = $db->numero_registros;

if ($numero_docs == 0)
{
	exit('Não foram encontrados documentos!');
}

$qtd_linhas = 0;

foreach($db->array_select as $reg_docs)
{
	$qtd_linhas++;
	
	$array_numdvm['numero_int'][$reg_docs["setor"]][$reg_docs["id_numero_interno"]] = PREFIXO_DOC_GED . sprintf("%05d",$reg_docs["os"]) . "-" . $reg_docs["sigla"] . "-" . $reg_docs["sequencia"]; 
	
	$array_numdvm['numero_cliente'][$reg_docs["setor"]][$reg_docs["id_numero_interno"]] = $reg_docs["numero_cliente"];
	
	if($reg_docs["tag"]!="")
	{
		$array_numdvm['tag'][$reg_docs["setor"]][$reg_docs["id_numero_interno"]][] = $reg_docs["tag"];
	}
	else
	{
		if($reg_docs["complemento"]!="")
		{
			$array_numdvm['tag'][$reg_docs["setor"]][$reg_docs["id_numero_interno"]][] = $reg_docs["complemento"];
		}
		else
		{
			$array_numdvm['tag'][$reg_docs["setor"]][$reg_docs["id_numero_interno"]][] = $reg_docs["descricao"];	
		}
	}
	
	if($reg_docs["tag2"]!="")
	{
		$array_numdvm['tag'][$reg_docs["setor"]][$reg_docs["id_numero_interno"]][] = $reg_docs["tag2"];
	}
	
	if($reg_docs["tag3"]!="")
	{
		$array_numdvm['tag'][$reg_docs["setor"]][$reg_docs["id_numero_interno"]][] = $reg_docs["tag3"];
	}
	
	if($reg_docs["tag4"]!="")
	{
		$array_numdvm['tag'][$reg_docs["setor"]][$reg_docs["id_numero_interno"]][] = $reg_docs["tag4"];
	}
	
	$array_numdvm['formato'][$reg_docs["setor"]][$reg_docs["id_numero_interno"]] = $reg_docs["formato"];
	
	//se não tiver grd, o numero de folhas vem do numero interno
	if(count($array_numdvm['numero_folhas'][$reg_docs["setor"]][$reg_docs["id_numero_interno"]])<=0)
	{
		$array_numdvm['numero_folhas'][$reg_docs["setor"]][$reg_docs["id_numero_interno"]][] = $reg_docs["numero_folhas"];
	}
	
	//se não tiver grd, as revisoes vem do ged_versoes
	if(count($array_numdvm['revisao_interna'][$reg_docs["setor"]][$reg_docs["id_numero_interno"]])<=0)
	{
		$array_numdvm['revisao_interna'][$reg_docs["setor"]][$reg_docs["id_numero_interno"]][] = $reg_docs["revisao_interna"];
		$array_numdvm['revisao_cliente'][$reg_docs["setor"]][$reg_docs["id_numero_interno"]][] = $reg_docs["revisao_cliente"];
	}
	
	$array_numdvm['observacao'][$reg_docs["setor"]][$reg_docs["id_numero_interno"]] = $reg_docs["obs"];
	
	for($x = 0; $x<count($grd_versao[$reg_docs["id_numero_interno"]]); $x++)
	{
		$qtd_linhas++;
		
		$ret = "";
		
		$array_numdvm['revisao_interna'][$reg_docs["setor"]][$reg_docs["id_numero_interno"]][$x] = $grd_revisao_dvm[$reg_docs["id_numero_interno"]][$x];
		
		$array_numdvm['revisao_cliente'][$reg_docs["setor"]][$reg_docs["id_numero_interno"]][$x] = $grd_revisao_cliente[$reg_docs["id_numero_interno"]][$x];
		
		$array_numdvm['numero_folhas'][$reg_docs["setor"]][$reg_docs["id_numero_interno"]][$x] = $grd_num_folhas[$reg_docs["id_numero_interno"]][$x];
		
		$array_numdvm['grd'][$reg_docs["setor"]][$reg_docs["id_numero_interno"]][$x] = $reg_docs["os"] . "-" . sprintf("%03d",$grd_num_pacote[$reg_docs["id_numero_interno"]][$x]);
		
		$array_numdvm['data_emissao'][$reg_docs["setor"]][$reg_docs["id_numero_interno"]][$x] = mysql_php($grd_data_emissao[$reg_docs["id_numero_interno"]][$x]);
		
		$array_numdvm['tipo_emissao'][$reg_docs["setor"]][$reg_docs["id_numero_interno"]][$x] = $codigos_emissao[$grd_cod_emissao[$reg_docs["id_numero_interno"]][$x]];
		
		checaDiasUteis(mysql_php($grd_data_emissao[$reg_docs["id_numero_interno"]][$x]),5,$ret,"sum");
		
		$array_numdvm['data_prev'][$reg_docs["setor"]][$reg_docs["id_numero_interno"]][$x] = $ret;
		
		$array_numdvm['data_dev'][$reg_docs["setor"]][$reg_docs["id_numero_interno"]][$x] = mysql_php($grd_data_devolucao[$reg_docs["id_numero_interno"]][$x]);
		
		$array_numdvm['status_dev'][$reg_docs["setor"]][$reg_docs["id_numero_interno"]][$x] = $grd_status_devolucao[$reg_docs["id_numero_interno"]][$x];
		
		if($grd_status_devolucao[$reg_docs["id_numero_interno"]][$x])
		{
			//QUANTIFICA AS FINALIDADES
			if($x==count($grd_versao[$reg_docs["id_numero_interno"]])-1)
			{
				$emissao[$codigos_emissao[$grd_cod_emissao[$reg_docs["id_numero_interno"]][$x]]]+= 1;
				
				$devolucao[$grd_status_devolucao[$reg_docs["id_numero_interno"]][$x]]+=1;
			
				$emissao_disciplina[$reg_docs["setor"]][$codigos_emissao[$grd_cod_emissao[$reg_docs["id_numero_interno"]][$x]]]+= 1;
				
				$devolucao_disciplina[$reg_docs["setor"]][$grd_status_devolucao[$reg_docs["id_numero_interno"]][$x]]+=1;
				
				$formatos[$reg_docs["formato"]]+= $reg_docs["numero_folhas"];
				
				$formatos_disciplina[$reg_docs["setor"]][$reg_docs["formato"]]+= $reg_docs["numero_folhas"];
			
				$formatos_a1_equiv[$reg_docs["formato"]]+= ($reg_docs["numero_folhas"]*$reg_docs["fator_equivalente"]);
			
				$formatos_disciplina_a1_equiv[$reg_docs["setor"]][$reg_docs["formato"]]+= ($reg_docs["numero_folhas"]*$reg_docs["fator_equivalente"]);

			}
		}
		else
		{
			if($x==count($grd_versao[$reg_docs["id_numero_interno"]])-1)
			{
				$emissao[$codigos_emissao[$grd_cod_emissao[$reg_docs["id_numero_interno"]][$x]]]+= 1;
				
				$devolucao['S']+=1;
				
				$formatos[$reg_docs["formato"]] += $reg_docs["numero_folhas"];
							
				$emissao_disciplina[$reg_docs["setor"]][$codigos_emissao[$grd_cod_emissao[$reg_docs["id_numero_interno"]][$x]]]+= 1;
				
				$devolucao_disciplina[$reg_docs["setor"]]['S']+=1;
				
				$formatos_disciplina[$reg_docs["setor"]][$reg_docs["formato"]]+= $reg_docs["numero_folhas"];

				$formatos_a1_equiv[$reg_docs["formato"]] += ($reg_docs["numero_folhas"]*$reg_docs["fator_equivalente"]);
	
				$formatos_disciplina_a1_equiv[$reg_docs["setor"]][$reg_docs["formato"]]+= ($reg_docs["numero_folhas"]*$reg_docs["fator_equivalente"]);
			}
		}
	
	}
}

if(isset($_POST["id_funcionario"]))
{
	$funcionario = $_POST["id_funcionario"];	
}
else
{
	$funcionario = $_SESSION["id_funcionario"];	
}

if($_POST["chk_emitidos"]=="1")
{
	$titulo="LISTA DOS DOCUMENTOS DO PROJETO (EMITIDOS)";
}
else
{
	$titulo="LISTA DOS DOCUMENTOS DO PROJETO";	
}	

if(isset($_POST["chk_periodo"]))
{
	$periodo = "Período: " . $_POST["dataini"] . " até " . $_POST["datafim"];
}
else
{
	$periodo = "";	
} 

$sql = "SELECT empresas.id_empresa, empresa, os, descricao  FROM ".DATABASE.".ordem_servico, ".DATABASE.".empresas ";
$sql .= "WHERE ordem_servico.id_empresa = empresas.id_empresa ";
$sql .= "AND ordem_servico.reg_del = 0 ";
$sql .= "AND empresas.reg_del = 0 ";
$sql .= "AND ordem_servico.id_os = '" . $_POST["id_os"] . "' ";

$db->select($sql,'MYSQL',true);

if ($db->erro != '')
{
	die($db->erro);
}

$reg_projeto = $db->array_select[0];	

//PDF
if($_POST["chk_excel"]==0) 
{	
	class PDF extends FPDF
	{	
		var $strPeriodo;
		var $breakpage = true;
		
		function legendas()
		{
			//Caixa de legendas
			$this->SetLineWidth(0.3);
			//Linha acima
			$this->Line(10,$this->GetY(), 280, $this->GetY());
			//Linha esquerda
			$this->Line(10,$this->GetY(),10,$this->GetY()+32);
			//Linha baixo
			$this->Line(10,$this->GetY()+32,280,$this->GetY()+32);
			//Linha direita
			$this->Line(280,$this->GetY(),280,$this->GetY()+32);
			
			$tam_leg = 8;	
			
			//Conteúdo da Finalidade de Emissão	
			$this->Ln(1);
			$this->SetFont('Arial','B',$tam_leg);
			$this->Cell(280,4,"TIPO DE EMISSÃO (TE)",0,0,'C',0);
				
			$this->Ln(4);
			
			$this->Cell(5,4,"PA",0,0,'L',0);
			
			$this->SetFont('Arial','',$tam_leg);
			$this->Cell(30,4," = Para Aprovação",0,0,'L',0);
			
			//Espaçamento
			$this->Cell(10,4,"",0,0,'L',0);
			
			$this->SetFont('Arial','B',$tam_leg);
			$this->Cell(5,4,"PC",0,0,'L',0);
			
			$this->SetFont('Arial','',$tam_leg);
			$this->Cell(30,4," = Para Construção",0,0,'L',0);
			
			//Espaçamento
			$this->Cell(10,4,"",0,0,'L',0);
			
			$this->SetFont('Arial','B',$tam_leg);
			$this->Cell(5,4,"CE",0,0,'L',0);
			
			$this->SetFont('Arial','',$tam_leg);
			$this->Cell(30,4," = Certificado",0,0,'L',0);
			
			//Espaçamento
			$this->Cell(5,4,"",0,0,'L',0);
			
			$this->SetFont('Arial','B',$tam_leg);
			$this->Cell(5,4,"CO",0,0,'L',0);
			
			$this->SetFont('Arial','',$tam_leg);
			$this->Cell(30,4," = Para Comentários",0,0,'L',0);
			
			//Espaçamento
			$this->Cell(10,4,"",0,0,'L',0);
			
			$this->SetFont('Arial','B',$tam_leg);
			$this->Cell(5,4,"CA",0,0,'L',0);
			
			$this->SetFont('Arial','',$tam_leg);
			$this->Cell(30,4," = Cancelado",0,0,'L',0);
			
			//Espaçamento
			$this->Cell(5,4,"",0,0,'L',0);
			
			$this->SetFont('Arial','B',$tam_leg);
			$this->Cell(5,4,"CC",0,0,'L',0);
			
			$this->SetFont('Arial','',$tam_leg);
			$this->Cell(30,4," = Conforme Construído \"AS BUILT\"",0,0,'L',0);
			
			//Line feed
			$this->Ln(4);
			
			$this->SetFont('Arial','B',$tam_leg);
			$this->Cell(5,4,"PR",0,0,'L',0);
			
			$this->SetFont('Arial','',$tam_leg);
			$this->Cell(30,4," = Preliminar",0,0,'L',0);
			
			//Espaçamento
			$this->Cell(10,4,"",0,0,'L',0);
			
			$this->SetFont('Arial','B',$tam_leg);
			$this->Cell(5,4,"CN",0,0,'L',0);
			
			$this->SetFont('Arial','',$tam_leg);
			$this->Cell(30,4," = Para Conhecimento",0,0,'L',0);
		
			//Espaçamento
			$this->Cell(10,4,"",0,0,'L',0);
			
			$this->SetFont('Arial','B',$tam_leg);
			$this->Cell(5,4,"DV",0,0,'L',0);
			
			$this->SetFont('Arial','',$tam_leg);
			$this->Cell(30,4," = Devolução Documento",0,0,'L',0);
			
			//Espaçamento
			$this->Cell(5,4,"",0,0,'L',0);
			
			$this->SetFont('Arial','B',$tam_leg);
			$this->Cell(5,4,"CS",0,0,'L',0);
			
			$this->SetFont('Arial','',$tam_leg);
			$this->Cell(30,4," = Para Consulta",0,0,'L',0);
			
			//Espaçamento
			$this->Cell(10,4,"",0,0,'L',0);
			
			$this->SetFont('Arial','B',$tam_leg);
			$this->Cell(5,4,"CV",0,0,'L',0);
			
			$this->SetFont('Arial','',$tam_leg);
			$this->Cell(30,4," = Cópia Avançada",0,0,'L',0);
			
			//Espaçamento
			$this->Cell(5,4,"",0,0,'L',0);
			
			$this->SetFont('Arial','B',$tam_leg);
			$this->Cell(5,4,"PO",0,0,'L',0);
			
			$this->SetFont('Arial','',$tam_leg);
			$this->Cell(30,4," = Para Orçamento",0,0,'L',0);
			
			$this->Cell(10,4,"",0,0,'L',0);
		
			$this->Ln(4);
			
			$this->SetFont('Arial','B',$tam_leg);
			$this->Cell(5,4,"LC",0,0,'L',0);
			
			$this->SetFont('Arial','',$tam_leg);
			$this->Cell(30,4," = Liberado para Compra",0,0,'L',0);
			
			//Espaçamento
			$this->Cell(10,4,"",0,0,'L',0);
			
			$this->SetFont('Arial','B',$tam_leg);
			$this->Cell(5,4,"LE",0,0,'L',0);
			
			$this->SetFont('Arial','',$tam_leg);
			$this->Cell(30,4," = Liberado para Execução",0,0,'L',0);
			
			//Espaçamento
			$this->Cell(10,4,"",0,0,'L',0);
			
			$this->SetFont('Arial','B',$tam_leg);
			$this->Cell(5,4,"CM",0,0,'L',0);
			
			$this->SetFont('Arial','',$tam_leg);
			$this->Cell(30,4," = Conforme Comprado",0,0,'L',0);
			
			//Espaçamento
			$this->Cell(5,4,"",0,0,'L',0);
			
			$this->SetFont('Arial','B',$tam_leg);
			$this->Cell(5,4,"CT",0,0,'L',0);
			
			$this->SetFont('Arial','',$tam_leg);
			$this->Cell(30,4," = Para Cotação",0,0,'L',0);	
			
			//Espaçamento
			$this->Cell(10,4,"",0,0,'L',0);
			
			$this->SetFont('Arial','B',$tam_leg);
			$this->Cell(5,4,"RC",0,0,'L',0);
			
			$this->SetFont('Arial','',$tam_leg);
			$this->Cell(30,4," = Revisado pelo Cliente",0,0,'L',0);
			
			//ALTERADO 03/09/2013
			$this->Cell(5,4,"",0,0,'L',0);
			
			$this->SetFont('Arial','B',$tam_leg);
			$this->Cell(5,4,"A",0,0,'L',0);
			
			$this->SetFont('Arial','',$tam_leg);
			$this->Cell(30,4," = Aprovado",0,0,'L',0);
			
			$this->Ln(4);
			
			$this->SetFont('Arial','B',$tam_leg);
			$this->Cell(5,4,"EI",0,0,'L',0);
			
			$this->SetFont('Arial','',$tam_leg);
			$this->Cell(30,4," = Emissão Interna",0,0,'L',0);				
		
			//CONTEUDO DO STATUS 
			$this->Ln(5);
			$this->SetFont('Arial','B',$tam_leg);
			$this->Cell(280,4,"STATUS DE DEVOLUÇÃO",0,0,'C',0);
			$this->SetFont('Arial','',$tam_leg);
			
			$this->Ln(4);
			$this->SetFont('Arial','B',$tam_leg);
			$this->Cell(5,4,"A",0,0,'L',0);
			
			$this->SetFont('Arial','',$tam_leg);
			$this->Cell(30,4," = Aprovado",0,0,'L',0);
			
			//Espaçamento
			$this->Cell(10,4,"",0,0,'L',0);
			
			$this->SetFont('Arial','B',$tam_leg);
			$this->Cell(5,4,"AC",0,0,'L',0);
			
			$this->SetFont('Arial','',$tam_leg);
			$this->Cell(30,4," = Aprovado com comentários",0,0,'L',0);
			
			//Espaçamento
			$this->Cell(10,4,"",0,0,'L',0);
			
			$this->SetFont('Arial','B',$tam_leg);
			$this->Cell(5,4,"C",0,0,'L',0);
			
			$this->SetFont('Arial','',$tam_leg);
			$this->Cell(30,4," = Cancelado",0,0,'L',0);
			
			$this->SetFont('Arial','B',$tam_leg);
			$this->Cell(5,4,"N",0,0,'L',0);
			
			$this->SetFont('Arial','',$tam_leg);
			$this->Cell(30,4," = Não Aprovado",0,0,'L',0);
			
			//Espaçamento
			$this->Cell(10,4,"",0,0,'L',0);
			
			$this->SetFont('Arial','B',$tam_leg);
			$this->Cell(5,4,"PI",0,0,'L',0);
			
			$this->SetFont('Arial','',$tam_leg);
			$this->Cell(30,4," = Para Informação",0,0,'L',0);
			
			//Espaçamento
			$this->Cell(10,4,"",0,0,'L',0);
			
			$this->SetFont('Arial','B',$tam_leg);
			$this->Cell(5,4,"NP",0,0,'L',0);
			
			$this->SetFont('Arial','',$tam_leg);
			$this->Cell(30,4," = Comentário não procedente",0,0,'L',0);
			
		}
		
		function titulos()
		{
			$this->SetLineWidth(0.2);								
			$this->SetFont('Arial','B',8);
			$this->Cell(45,10,"Número Cliente",1,0,'L',0);
			$this->HCell(15,10,"Rev Cli",1,0,'C',0);
			$this->Cell(30,10,"Número Interno",1,0,'L',0);
			$this->HCell(15,10,"Rev. Int.",1,0,'C',0);				
			$this->Cell(65,10,"Título",1,0,'C',0);
			$this->HCell(10,10,"Fmt",1,0,'C',0);
			$this->Cell(10,10,"Fls",1,0,'C',0);
			$this->Cell(30,5,"Emissão",1,0,'C',0);
			$this->Cell(50,5,"Devolução",1,0,'C',0);
			$this->Ln(5);
			
			$this->Cell(190,5,"",0,0,'C',0);				
			$this->Cell(11,5,"Nº GRD",1,0,'C',0);
			$this->Cell(14,5,"data",1,0,'C',0);
			$this->Cell(5,5,"TE",1,0,'C',0);						
			$this->Cell(20,5,"Prev.",1,0,'C',0);				
			$this->Cell(15,5,"Data",1,0,'C',0);
			$this->Cell(15,5,"status",1,0,'C',0);		
							
			$this->Ln(5);
		}
		
		//Page header
		function Header()
		{
			//portrait
			$this->SetDrawColor(0,0,0);
			$this->Image(DIR_IMAGENS.'logo_pb.png',10,10,40);
			$this->SetFont('Arial','',6);
			$this->Cell(242,4,'',0,0,'L',0);
			$this->Cell(15,4,'EMISSÃO:',0,0,'R',0);
			$this->Cell(15,4,$this->Emissao(),0,1,'R',0); //aqui
			$this->Cell(242,4,'',0,0,'L',0);
			$this->Cell(15,4,'FOLHA:',0,0,'R',0); //aqui
			$this->Cell(15,4,$this->PageNo().' de {nb}',0,0,'R',0);			
			$this->Ln(8);
			$this->SetFont('Arial','B',12);
			$this->Cell(270,5,$this->Titulo(),0,1,'R',0); //270
			$this->SetFont('Arial','B',8);
			$this->Cell(70,4,$this->strPeriodo,0,0,'L',0);			
			$this->Cell(200,4,"",0,1,'R',0);
			$this->SetLineWidth(0.7);
			$this->Line(10,32,280,32);			
			$this->SetLineWidth(0.5);
			$this->SetXY(10,33);	
			
			//Se não for a primeira página
			if($this->PageNo()>1 && $this->breakpage)
			{
				$this->Ln(5);
				$this->titulos();				
			}			
		}
		
		//Page footer
		function Footer()
		{
			if($this->PageNo()==1)
			{
				$this->SetXY(10,39);
				$this->Cell(270,5,"Qtd. de Docs.: " . $this->Revisao(),0,0,'R',0);
			}
			
			$this->SetFont('Arial','',5);
			$this->SetXY(10,205);
			$this->Cell(270,5,"Ver.:3",0,0,'R',0);
		}
	}
	
	$pdf = new PDF('L','mm','A4');
	$pdf->SetAutoPageBreak(true,5);
	$pdf->SetMargins(10,10); 
	$pdf->SetLineWidth(0.5);
	
	//Seta o cabeçalho
	$pdf->departamento=NOME_EMPRESA;

	$pdf->titulo = $titulo;

	$pdf->strPeriodo = $periodo;		
	
	$pdf->emissao = date("d/m/Y");	
	$pdf->AliasNbPages();	
	$pdf->AddPage();
	
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(130,5,"Cliente: ".$reg_projeto["empresa"],0,1,'L',0);	
	$pdf->Cell(50,5,"Nº Projeto: " . $reg_projeto["os"],0,0,'L',0);
	$pdf->Cell(205,5,$reg_projeto["descricao"],10,1,'C',0);		
	
	$pdf->legendas();
	$pdf->Ln(10);
	$pdf->titulos();
	$pdf->versao_documento = $numero_docs;	
	
	$pdf->Ln(3);
	
	$tamanho_linha = 5;
	
	foreach($array_numdvm['numero_int'] as $setor=>$array_numeros)
	{
		if($pdf->GetY()>185)
		{
			$pdf->addPage();
		}
		
		$pdf->SetFont('Arial','B',9);
		$pdf->Ln(2);
		$pdf->Cell(200,$tamanho_linha,$setor,0,1,'L',0);
		$pdf->SetFont('Arial','',7);
		
		foreach($array_numeros as $id_numero_interno=>$numero_int)
		{
			if($pdf->GetY()>185)
			{
				$pdf->addPage();
			}
			
			//contabiliza qual é o maior indice
			$array_maior[0] =  count($array_numdvm['revisao_cliente'][$setor][$id_numero_interno]);
			$array_maior[1] =  count($array_numdvm['revisao_interna'][$setor][$id_numero_interno]);
			$array_maior[2] =  count($array_numdvm['tag'][$setor][$id_numero_interno]);
			
			$max_linha = max($array_maior);
					
			$pdf->SetLineWidth(0.1);
			
			$x1 = $pdf->GetX();			
			$y1 = $pdf->GetY();
			
			$pdf->Line(10,$pdf->GetY(),280,$pdf->GetY());
			
			$pdf->HCell(45,$tamanho_linha,$array_numdvm['numero_cliente'][$setor][$id_numero_interno],0,0,'L',0);
			
			$pdf->SetX($x1+60);
			
			$pdf->HCell(45,$tamanho_linha,$numero_int,0,0,'L',0);
			
			$pdf->SetX($x1+170);
			
			$pdf->HCell(10,$tamanho_linha,$array_numdvm['formato'][$setor][$id_numero_interno],0,0,'C',0);
			
			$pdf->SetY($y1);
			
			for($x = 0; $x < $max_linha; $x++)
			{
				$pdf->SetX($x1+45);
				
				$pdf->HCell(15,$tamanho_linha,$array_numdvm['revisao_cliente'][$setor][$id_numero_interno][$x],0,0,'C',0);
				
				$pdf->SetX($x1+90);
				
				$pdf->HCell(15,$tamanho_linha,$array_numdvm['revisao_interna'][$setor][$id_numero_interno][$x],0,0,'C',0);
				
				$pdf->SetX($x1+105);
				
				$pdf->HCell(65,$tamanho_linha,$array_numdvm['tag'][$setor][$id_numero_interno][$x],0,0,'C',0);
				
				$pdf->SetX($x1+180);
				
				$pdf->HCell(10,$tamanho_linha,$array_numdvm['numero_folhas'][$setor][$id_numero_interno][$x],0,0,'C',0);
				
				$pdf->HCell(11,$tamanho_linha,$array_numdvm['grd'][$setor][$id_numero_interno][$x],0,0,'C',0);
				
				$pdf->HCell(14,$tamanho_linha,$array_numdvm['data_emissao'][$setor][$id_numero_interno][$x],0,0,'C',0);
				
				$pdf->HCell(5,$tamanho_linha,$array_numdvm['tipo_emissao'][$setor][$id_numero_interno][$x],0,0,'C',0);
				
				$pdf->HCell(20,$tamanho_linha,$array_numdvm['data_prev'][$setor][$id_numero_interno][$x],0,0,'C',0);
				
				$pdf->HCell(15,$tamanho_linha,$array_numdvm['data_dev'][$setor][$id_numero_interno][$x],0,0,'C',0);
				
				$pdf->HCell(15,$tamanho_linha,$array_numdvm['status_dev'][$setor][$id_numero_interno][$x],0,0,'C',0);
				
				$pdf->Ln($tamanho_linha);
				
			}
			
			$pdf->Ln($tamanho_linha);
			
			if($array_numdvm['observacao'][$setor][$id_numero_interno]!="")
			{
				$pdf->SetFont('Arial','B',7);
				$pdf->HCell(10,$tamanho_linha,"Obs.:",0,0,'L',0);
				$pdf->HCell(150,$tamanho_linha,$array_numdvm['observacao'][$setor][$id_numero_interno],0,1,'L',0);
				$pdf->SetFont('Arial','',7);
			}			
		}
	}

	$pdf->breakpage = false;
	
	if($_POST["chk_estatistica"]==1)
	{
		$pdf->AddPage();
		
		$pdf->SetDrawColor(0,0,0);
		
		$pdf->SetFont('Arial','B',12);
	
		$pdf->Cell(270,5,"ESTATÍSTICAS DE DOCUMENTOS - TOTAL",0,0,'C',0);
		
		$pdf->SetFont('Arial','',8);	
	
		$pdf->Ln(15);		
		
		//TOTAL DOCUMENTOS RESERVADOS
		$sql = "SELECT * FROM ".DATABASE.".numeros_interno ";
		$sql .= "WHERE numeros_interno.reg_del = 0 "; 
		$sql .= "AND numeros_interno.id_os = '" . $_POST["id_os"] . "' ";
		$sql .= "AND numeros_interno.mostra_relatorios = '1' ";
		$sql .= "GROUP BY numeros_interno.id_numero_interno ";	
		
		$db->select($sql,'MYSQL',true);		
		
		if ($db->erro != '')
		{
			die("Erro ao tentar selecionar os dados: " . $db->erro);	
		}		
	
		$total_docs_sol = $db->numero_registros;
				
		//TOTAL DOCUMENTOS
		$sql = "SELECT * FROM ".DATABASE.".setores, ".DATABASE.".numeros_interno, ".DATABASE.".ged_arquivos ";  
		$sql .= "WHERE numeros_interno.id_os = '" . $_POST["id_os"] . "' ";
		$sql .= "AND numeros_interno.reg_del = 0 ";
		$sql .= "AND setores.reg_del = 0 ";
		$sql .= "AND ged_arquivos.reg_del = 0 ";
		$sql .= "AND numeros_interno.id_disciplina = setores.id_setor ";
		$sql .= "AND ged_arquivos.documento_interno = 1 "; //somente documentos internos
		$sql .= "AND ged_arquivos.id_numero_interno = numeros_interno.id_numero_interno "; 
		$sql .= "AND numeros_interno.mostra_relatorios = '1' ";
		$sql .= "GROUP BY numeros_interno.id_numero_interno ";
		
		$db->select($sql,'MYSQL',true);
		
		if ($db->erro!= '')
		{
			die("Erro ao tentar selecionar os dados: " . $db->erro);	
		}
	
		$total_docs = $db->numero_registros;
	
		//DOCUMENTOS EMITIDOS
		$sql = "SELECT * FROM ".DATABASE.".setores, ".DATABASE.".numeros_interno, ".DATABASE.".ged_arquivos, ".DATABASE.".ged_versoes, ".DATABASE.".ged_pacotes "; 
		$sql .= "WHERE numeros_interno.reg_del = 0 ";
		$sql .= "AND setores.reg_del = 0 ";
		$sql .= "AND ged_arquivos.reg_del = 0 ";
		$sql .= "AND ged_versoes.reg_del = 0 ";
		$sql .= "AND ged_pacotes.reg_del = 0 ";
		$sql .= "AND numeros_interno.id_os = '" . $_POST["id_os"] . "' ";
		$sql .= "AND numeros_interno.id_disciplina = setores.id_setor ";
		$sql .= "AND ged_arquivos.documento_interno = 1 "; //somente documentos internos
		$sql .= "AND ged_arquivos.id_numero_interno = numeros_interno.id_numero_interno "; 
		$sql .= "AND numeros_interno.mostra_relatorios = '1' ";
		$sql .= "AND numeros_interno.id_numero_interno = ged_arquivos.id_numero_interno ";
		$sql .= "AND ged_arquivos.id_ged_versao = ged_versoes.id_ged_versao ";
		$sql .= "AND ged_versoes.id_ged_pacote = ged_pacotes.id_ged_pacote ";
		$sql .= "GROUP BY numeros_interno.id_numero_interno ";
		
		$db->select($sql,'MYSQL',true);
		
		if ($db->erro)
		{
			die("Erro ao tentar selecionar os dados: " . $db->erro);	
		}
	
		$total_emitidos = $db->numero_registros;
		
		$Y = $pdf->GetY();
	
		$pdf->SetFont('Arial','B',8);
		
		$pdf->Cell(70,5,"TOTAIS",0,1,'C',0);
		
		$pdf->SetFont('Arial','',8);
		
		$pdf->Cell(50,5,"TOTAL NÚMEROS RESERVADOS",1,0,'L',0);
		
		$pdf->Cell(20,5,($total_docs_sol!='')?$total_docs_sol:'0',1,1,'C',0);	
		
		$pdf->Cell(50,5,"TOTAL DOCUMENTOS",1,0,'L',0);
		
		$pdf->Cell(20,5,($total_docs!='')?$total_docs:'0',1,1,'C',0);
		
		$pdf->Cell(50,5,"DOCUMENTOS EMITIDOS",1,0,'L',0);
		
		$pdf->Cell(20,5,($total_emitidos!='')?$total_emitidos:'0',1,1,'C',0);
		
		$pdf->Cell(50,5,"FALTAM EMITIR",1,0,'L',0);
		
		$pdf->Cell(20,5,(($total_docs-$total_emitidos)!='')?$total_docs-$total_emitidos:'0',1,1,'C',0);
		
		$pdf->Ln(5);
		
		$YL = $pdf->GetY();
		
		$pdf->SetXY(90,$Y);
		
		$pdf->SetFont('Arial','B',8);
		
		$pdf->Cell(70,5,"DE ACORDO COM A FINALIDADE",0,1,'C',0);
		
		$pdf->SetFont('Arial','',8);
		
		$certi = 0;
		
		//quantidades por finalidade
		foreach($emissao as $key=>$valor)
		{
			$pdf->SetX(90);
			//caso não certificado
			if($key!='CE')
			{
				$pdf->HCell(50,5,$tit_emiss[$key],1,0,'L',0);
				
				$pdf->Cell(20,5,($valor!='')?$valor:'0',1,1,'C',0);
			}
			else
			{
				$certi = $valor;	
			}		
		}
	
		if(is_null($certi))
		{
			$certi = '0';		
		}
		
		$pdf->SetX(90);
		
		$pdf->Cell(50,5,'CERTIFICADO',1,0,'L',0);
		
		$pdf->Cell(20,5,$certi,1,1,'C',0);
		
		$pdf->Ln(5);
		
		if($YL<=$pdf->GetY())
		{
			$YL = $pdf->GetY();	
		}
		
		$pdf->SetXY(170,$Y);
		
		$pdf->SetFont('Arial','B',8);
		
		$pdf->Cell(70,5,"DE ACORDO COM A DEVOLUÇÃO",0,1,'C',0);
		
		$pdf->SetFont('Arial','',8);
		
		//quantidades por devolução
		foreach($devolucao as $key=>$valor)
		{
			$pdf->SetX(170);
			
			$pdf->Cell(50,5,$array_devolucao[$key],1,0,'L',0);
			
			$pdf->Cell(20,5,($valor!='')?$valor:'0',1,1,'C',0);				
		}
		
		$pdf->Ln(5);
		
		if($YL<=$pdf->GetY())
		{
			$YL = $pdf->GetY();	
		}
		
		$pdf->SetXY(250,$Y);
		
		$pdf->SetFont('Arial','B',8);
		
		$pdf->Cell(30,5,"FORMATOS (FOLHAS A1 EQUIV.)",0,1,'C',0);
		
		$pdf->SetFont('Arial','',8);
		
		ksort($formatos);
		
		//quantidades por formatos
		foreach($formatos as $key=>$valor)
		{
			$pdf->SetX(250);
			
			$pdf->Cell(15,5,$key,1,0,'L',0);
			
			$pdf->Cell(15,5,($valor!='')?$valor:'0',1,1,'C',0);
			
			$a1_equiv += $formatos_a1_equiv[$key];
		}
		
		$pdf->SetX(250);
		
		$pdf->Cell(15,5,'A1 Equiv.',1,0,'L',0);
		
		$pdf->Cell(15,5,($a1_equiv!='')?$a1_equiv:'0',1,1,'C',0);
		
		$pdf->Ln(5);	
					
		if($YL<=$pdf->GetY())
		{
			$YL = $pdf->GetY();	
		}
		
		$pdf->Line(10,$YL , 280, $YL );
		
		$pdf->SetY($YL);
		
		$certi = 0;
	
		$a1_equiv = 0;
	
		foreach($array_disc as $disc1)
		{	
			if($pdf->GetY()>180)
			{
				$pdf->AddPage();
			}
			
			$pdf->SetFont('Arial','B',8);
			
			$pdf->Cell(50,5,$disc1,0,1,'L',0);
			
			$pdf->SetFont('Arial','',8);
			
			//TOTAL DOCUMENTOS RESERVADOS
			$sql = "SELECT * FROM ".DATABASE.".numeros_interno, ".DATABASE.".setores ";
			$sql .= "WHERE numeros_interno.id_os = '" . $_POST["id_os"] . "' ";
			$sql .= "AND numeros_interno.reg_del = 0 ";
			$sql .= "AND setores.reg_del = 0 ";
			$sql .= "AND numeros_interno.mostra_relatorios = '1' ";			
			$sql .= "AND numeros_interno.id_disciplina = setores.id_setor ";			
			$sql .= "AND setores.setor = '".$disc1."' ";
			$sql .= "GROUP BY numeros_interno.id_numero_interno ";
			
			$db->select($sql,'MYSQL',true);
			
			if($db->erro != '')
			{
				die("Erro ao tentar selecionar os dados: " . $db->erro);	
			}
		
			$total_docs_sol = $db->numero_registros;
			
			//TOTAL DOCUMENTOS
			$sql = "SELECT * FROM ".DATABASE.".numeros_interno, ".DATABASE.".setores, ".DATABASE.".ged_arquivos "; 
			$sql .= "WHERE numeros_interno.id_os = '" . $_POST["id_os"] . "' ";
			$sql .= "AND numeros_interno.reg_del = 0 ";
			$sql .= "AND ged_arquivos.reg_del = 0 ";
			$sql .= "AND setores.reg_del = 0 ";
			$sql .= "AND ged_arquivos.id_numero_interno = numeros_interno.id_numero_interno "; 
			$sql .= "AND numeros_interno.mostra_relatorios = '1' ";
			$sql .= "AND numeros_interno.id_disciplina = setores.id_setor ";
			$sql .= "AND setores.setor = '".$disc1."' ";
			$sql .= "GROUP BY numeros_interno.id_numero_interno ";
			
			$db->select($sql,'MYSQL',true);
			
			if ($db->erro != '')
			{
				die("Erro ao tentar selecionar os dados: " . $db->erro);	
			}
		
			$total_docs = $db->numero_registros;
			
			//DOCUMENTOS EMITIDOS
			$sql = "SELECT * FROM ".DATABASE.".numeros_interno, ".DATABASE.".setores, ".DATABASE.".ged_arquivos, ".DATABASE.".ged_versoes, ".DATABASE.".ged_pacotes ";
			$sql .= "WHERE numeros_interno.id_os = '" . $_POST["id_os"] . "' ";
			$sql .= "AND numeros_interno.reg_del = 0 ";
			$sql .= "AND ged_arquivos.reg_del = 0 ";
			$sql .= "AND ged_pacotes.reg_del = 0 ";
			$sql .= "AND ged_versoes.reg_del = 0 ";
			$sql .= "AND setores.reg_del = 0 ";
			$sql .= "AND ged_arquivos.id_numero_interno = numeros_interno.id_numero_interno ";			
			$sql .= "AND numeros_interno.mostra_relatorios = '1' ";
			$sql .= "AND ged_arquivos.id_ged_versao = ged_versoes.id_ged_versao ";
			$sql .= "AND ged_versoes.id_ged_pacote = ged_pacotes.id_ged_pacote ";
			$sql .= "AND numeros_interno.id_disciplina = setores.id_setor ";
			$sql .= "AND setores.setor = '".$disc1."' ";
			$sql .= "GROUP BY numeros_interno.id_numero_interno ";
			
			$db->select($sql,'MYSQL',true);
			
			if ($db->erro != '')
			{
				die("Erro ao tentar selecionar os dados: " . $db->erro);	
			}
		
			$total_emitidos = $db->numero_registros;
			
			$Y = $pdf->GetY();
			
			$pdf->Cell(50,5,"TOTAL NÚMEROS RESERVADOS",1,0,'L',0);
			
			$pdf->Cell(20,5,($total_docs_sol!='')?$total_docs_sol:'0',1,1,'C',0);	
			
			$pdf->Cell(50,5,"TOTAL DOCUMENTOS",1,0,'L',0);
			
			$pdf->Cell(20,5,($total_docs!='')?$total_docs:'0',1,1,'C',0);
			
			$pdf->Cell(50,5,"DOCUMENTOS EMITIDOS",1,0,'L',0);
			
			$pdf->Cell(20,5,($total_emitidos!='')?$total_emitidos:'0',1,1,'C',0);
			
			$pdf->Cell(50,5,"FALTAM EMITIR",1,0,'L',0);
			
			$pdf->Cell(20,5,(($total_docs-$total_emitidos)!='')?$total_docs-$total_emitidos:'0',1,1,'C',0);
			
			$pdf->Ln(5);
			
			$YL = $pdf->GetY();
			
			$pdf->SetXY(90,$Y);
			
			foreach($emissao_disciplina[$disc1] as $keys=>$docs)
			{
				$pdf->SetX(90);
	
				$pdf->Cell(50,5,$tit_emiss[$keys],1,0,'L',0);
					
				$pdf->Cell(20,5,($docs!='')?$docs:'0',1,1,'C',0);
				
			}
			
			if($YL<=$pdf->GetY())
			{
				$YL = $pdf->GetY();	
			}
			
			$pdf->SetXY(170,$Y);
			
			//quantidades por devolução
			foreach($devolucao_disciplina[$disc1] as $key=>$valor)
			{
				$pdf->SetX(170);
				
				$pdf->Cell(50,5,$array_devolucao[$key],1,0,'L',0);
				
				$pdf->Cell(20,5,($valor!='')?$valor:'0',1,1,'C',0);					
			}
			
			if($YL<=$pdf->GetY())
			{
				$YL = $pdf->GetY();	
			}
			
			$pdf->SetXY(250,$Y);
			
			ksort($formatos_disciplina[$disc1]);
			
			$a1_equiv = 0;
			
			//quantidades por devolução
			foreach($formatos_disciplina[$disc1] as $key=>$valor)
			{
				$pdf->SetX(250);
				
				$pdf->Cell(15,5,$key,1,0,'L',0);
				
				$pdf->Cell(15,5,($valor!='')?$valor:'0',1,1,'C',0);
				
				$a1_equiv += $formatos_disciplina_a1_equiv[$disc1][$key];
				
			}
			
			$pdf->SetX(250);
			
			$pdf->Cell(15,5,'A1 Equiv.',1,0,'L',0);
			
			$pdf->Cell(15,5,($a1_equiv!='')?$a1_equiv:'0',1,1,'C',0);	

			$YL += 2;
			
			$pdf->SetY($YL);
			
			$pdf->Line(10,$YL , 280, $YL );
							
		}
		
	}

	$pdf->Output('LISTA_DOCUMENTOS_'.date('dmYhis').'.pdf', 'D');
}
else ////////////////////////////////////////////////////////////////////////////////////////////
{
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
	/** PHPExcel_IOFactory */
	
	require_once(INCLUDE_DIR."PHPExcel/Classes/PHPExcel.php"); 
	
	$objPHPExcel = PHPExcel_IOFactory::load("../modelos_excel/lista_documentos_modelo.xls");
	
	$locale = 'pt_br';
	
	$validlocale = PHPExcel_Settings::setlocale($locale);
	
	if (!$validlocale) 
	{
		echo 'Unable to set locale to '.$locale." - reverting to en_us<br />\n";
	}
	
	// Redirect output to a client's web browser (Excel2007)
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="lista_documentos_"'.date('dmYHis').'".xlsx"');
	header('Cache-Control: max-age=0');
	
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	
	$objWriter->setPreCalculateFormulas(false);	
	
	//1ª folha
	$objPHPExcel->setActiveSheetIndex(0);
	
	//titulo
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, 1, $titulo);
	
	//data emissão
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, 3, 'data emissão: '.date('d/m/Y'));
	
	//nome projeto
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, 5, sprintf("%05d",$reg_projeto["os"])." - ".$reg_projeto["descricao"]);
	
	//Cliente
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, 6, $reg_projeto["empresa"]);
	
	$objPHPExcel->getActiveSheet()->insertNewRowBefore(16,$qtd_linhas*2);
	
	$linha = 16;
	
	foreach($array_numdvm['numero_int'] as $setor=>$array_numeros)
	{
		$objPHPExcel->getActiveSheet()->getStyle('A'.$linha.":P".$linha)->getFont()->setBold(true)->setSize(10);
		
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $linha, $setor);
		
		$objPHPExcel->getActiveSheet()->mergeCells("A".($linha).":P".($linha));

		foreach($array_numeros as $id_numero_interno=>$numero_int)
		{
			$linha++;
			//contabiliza qual é o maior indice
			$array_maior[0] =  count($array_numdvm['revisao_cliente'][$setor][$id_numero_interno]);
			$array_maior[1] =  count($array_numdvm['revisao_interna'][$setor][$id_numero_interno]);
			$array_maior[2] =  count($array_numdvm['tag'][$setor][$id_numero_interno]);
			
			$max_linha = max($array_maior);
			
			$objPHPExcel->getActiveSheet()->getStyle('A'.$linha.":P".$linha)->getFont()->setBold(false)->setSize(10);
			//numero cliente
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $linha, $array_numdvm['numero_cliente'][$setor][$id_numero_interno]);
			
			//numero interno
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $linha, $numero_int);
			
			//formato
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8, $linha, $array_numdvm['formato'][$setor][$id_numero_interno]);
			
			for($x = 0; $x < $max_linha; $x++)
			{
				$objPHPExcel->getActiveSheet()->getStyle('A'.$linha.":P".$linha)->getFont()->setBold(false)->setSize(10);								
				//versao_documento cliente
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $linha, $array_numdvm['revisao_cliente'][$setor][$id_numero_interno][$x]);
				
				//versao_documento devemada
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $linha, $array_numdvm['revisao_interna'][$setor][$id_numero_interno][$x]);
				
				//titulo
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, $linha, $array_numdvm['tag'][$setor][$id_numero_interno][$x]);
				
				//$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, $linha, iconv('ISO-8859-1', 'UTF-8',$tag));
				
				$objPHPExcel->getActiveSheet()->mergeCells("E".($linha).":H".($linha));
				
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(9, $linha, $array_numdvm['numero_folhas'][$setor][$id_numero_interno][$x]);
				
				//GRD
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(10, $linha, $array_numdvm['grd'][$setor][$id_numero_interno][$x]);

				//data emissao
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(11, $linha, $array_numdvm['data_emissao'][$setor][$id_numero_interno][$x]);

				//tipo emissao
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(12, $linha, $array_numdvm['tipo_emissao'][$setor][$id_numero_interno][$x]);

				//data prev
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(13, $linha, $array_numdvm['data_prev'][$setor][$id_numero_interno][$x]);

				//data dev
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(14, $linha, $array_numdvm['data_dev'][$setor][$id_numero_interno][$x]);

				//data dev
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(15, $linha, $array_numdvm['status_dev'][$setor][$id_numero_interno][$x]);
				
				$linha++;			
			}			
		}

		$linha++;
	}	
	
	$objWriter->save('php://output');
	
	$objPHPExcel->disconnectWorksheets();
	
	unset($objPHPExcel);
		
	exit;
}

?> 
