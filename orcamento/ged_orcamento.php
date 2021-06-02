<?php
/**
 *
 *		Formulário de Gerenciamento Eletrônico de Documentos do Orçamento	
 *		
 *		Criado por Carlos Abreu  
 *		
 *		local/Nome do arquivo:
 *		../orcamento/ged_orcamento.php

 *		
 *		Versão 0 --> VERSÃO INICIAL - Carlos Abreu - 04/12/2014
 *		Versão 1 --> Inclusão dos campos reg_del nas consultas - 20/11/2017 - Carlos Abreu
 */	

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

require_once(INCLUDE_DIR."antiInjection.php");

if(!verifica_sub_modulo(507))
{
	nao_permitido();
}

//Lista de funcionarios com amplo acesso ao GED
function lista_autorizados()
{
	//Forma um array com id_funcionario dos funcionários do Arquivo tecnico que podem ter acesso amplo ao GED
	$lista_arqtec = array('0', '0'); 
	
	//Retorna o array
	return $lista_arqtec;
}

function lista_sem_versao()
{
	//Forma um array com os tipos de documentos que não terão versões, tipo EMAIL
	$lista_sem_versao = array('3'); 
	
	//Retorna o array
	return $lista_sem_versao;
}


//função utilizada para compor o menu pop-up do ged
function seleciona_opcoes($id_arquivo_versao,$x,$y)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados();
	
	$array_opcoes = NULL;
	
	//vem pelo grid o id da linha (id_arquivo_versao)
	$tipo = explode("_",$id_arquivo_versao);
	
	$tipo_documento = false;
	
	if(is_numeric($tipo[1]))
	{		
		if($tipo[0]=='ARQ')
		{
			$sql = "SELECT * FROM ".DATABASE.".arquivos_proposta, ".DATABASE.".arquivos_proposta_versoes ";
			$sql .= "WHERE arquivos_proposta.reg_del = 0 ";
			$sql .= "AND arquivos_proposta_versoes.reg_del = 0 ";
			$sql .= "AND arquivos_proposta_versoes.id_arquivo_versao = '".$tipo[1]."' ";			
			$sql .= "AND arquivos_proposta.id_arquivo_versao = arquivos_proposta_versoes.id_arquivo_versao ";
			
			$db->select($sql,'MYSQL',true);
  
			if ($db->erro != '')
			{
				$resposta->addAlert("Erro ao tentar selecionar os dados.".$sql);
			}
			else
			{
				$cont = $db->array_select[0];
		
				if(in_array($cont["tipo_documento"],lista_sem_versao()))
				{
					$tipo_documento = true;
				}
			}				
		}
		
		$array_opcoes['operacao'] = $operacao;
		
		//se for arquivo de revisao_documento, concatena o id
		if($tipo[2]=='VER' || $tipo_documento)
		{
			$array_opcoes['id_arquivo_versao'] = $tipo[1]."_".$tipo[2];
		}
		else
		{
			$array_opcoes['id_arquivo_versao'] = $tipo[1];
		}
		
		$resposta->addScript("popupMenu('".$x."','".$y."','".$array_opcoes['id_arquivo_versao']."');");
	}
	
	return $resposta;
}

//Abre o arquivo selecionado
function abrir($caminho)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados();
	
	//vem pelo grid o id da linha (id_ged_arquivo)
	$tipo = explode("_",$caminho);
	
	if(($tipo[0]=='ARQ') && is_numeric($tipo[1]))
	{
		switch ($tipo[0])
		{
			case 'ARQ':
			
				$sql = "SELECT * FROM ".DATABASE.".propostas, ".DATABASE.".tipos_documentos, ".DATABASE.".arquivos_proposta, ".DATABASE.".arquivos_proposta_versoes, ".DATABASE.".funcionarios ";
				$sql .= "WHERE propostas.reg_del = 0 ";
				$sql .= "AND arquivos_proposta.reg_del = 0 ";
				$sql .= "AND tipos_documentos.reg_del = 0 ";
				$sql .= "AND funcionarios.reg_del = 0 ";
				$sql .= "AND arquivos_proposta_versoes.reg_del = 0 ";
				$sql .= "AND arquivos_proposta_versoes.id_arquivo_versao = '" . $tipo[1] . "' ";
				$sql .= "AND arquivos_proposta.id_proposta = propostas.id_proposta ";
				$sql .= "AND arquivos_proposta.id_tipo_documento = tipos_documentos.id_tipo_documento ";				
				$sql .= "AND arquivos_proposta.id_arquivo_proposta = arquivos_proposta_versoes.id_arquivo_proposta ";				
				$sql .= "AND arquivos_proposta_versoes.id_autor = funcionarios.id_funcionario ";
				
				$db->select($sql,'MYSQL',true);
	  
				if ($db->erro != '')
				{
					$resposta->addAlert("Erro ao tentar selecionar os dados.".$sql);
				}
				else
				{
					$reg_arquivos = $db->array_select[0];
					
					$caminho = DOCUMENTOS_ORCAMENTO.$reg_arquivos["base"] . "/" . sprintf("%05d",intval(trim($reg_arquivos["numero_proposta"]))) . "/" . tiraacentos($reg_arquivos["tipo_documento"]) . "/";
					
					//se arquivo de revisao_documento
					if($tipo[2]=='VER')
					{
						$caminho .=  DIRETORIO_VERSOES. "/". $reg_arquivos["arquivo"].".".$reg_arquivos["id_arquivo_versao"];
					}
					else
					{
						if(in_array($reg_arquivos["id_tipo_documento"],lista_sem_versao()))
						{
							$caminho .= $reg_arquivos["arquivo"].".".$reg_arquivos["id_arquivo_versao"];
						}
						else
						{
							$caminho .= $reg_arquivos["arquivo"];
						}
					}
				}
						
			break;
			
		}
	}
	
	$resposta->addScript('open_doc("'.$caminho.'")');

	return $resposta;
}

//filtra OS
function filtra_os($id_proposta)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados();
	
	$resposta->addAssign("div_arquivos","innerHTML","");
	
	$sql = "SELECT numero_proposta FROM ".DATABASE.".propostas ";
	$sql .= "WHERE propostas.id_proposta = '".$id_proposta."' ";
	$sql .= "AND propostas.reg_del = 0 ";
	
	$db->select($sql,'MYSQL',true);
	
	if ($db->erro != '')
	{
		$resposta->addAlert("Erro ao tentar selecionar os dados: " . $db->erro);
	}

	$reg = $db->array_select[0];
	
	/*
	$sql = "SELECT * FROM AF1010, SA1010 ";
	$sql .= "WHERE AF1010.D_E_L_E_T_ = '' ";
	$sql .= "AND SA1010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF1010.AF1_CLIENT = SA1010.A1_COD ";
	$sql .= "AND AF1010.AF1_LOJA = SA1010.A1_LOJA ";
	$sql .= "AND AF1010.AF1_ORCAME = '".$reg["numero_proposta"]."' ";
	
	$db->select($sql,'MSSQL', true);
	
	if ($db->erro != '')
	{
		$resposta->addAlert("Erro ao tentar selecionar os dados: " . $db->erro);
	}

	$reg1 = $db->array_select[0];
	
	$resposta->addScript("xajax_preenchePastas('".DOCUMENTOS_ORCAMENTO.trim($reg1["A1_NOME"])."');");
	*/
	
	return $resposta;
}

//Preenche a lista de pastas
function preenchePastas($dir)
{
	//Preenche a lista de pastas
	//Cria uma saída em XML	
	//Parte do diretório fornecido em $dir
	$resposta = new xajaxResponse();

	//Instancia o objeto
	$xml = new xmlWriter();
	
	$xml->openMemory();
	
	$xml->setIndent(false);
	
	//Elemento raiz
	$xml->startElement('tree');
	
	$xml->writeAttribute('id', '0');
	
	$dire = explode(DOCUMENTOS_ORCAMENTO,$dir);
	
	$xml->startElement('item');

		$xml->writeAttribute('id', addslashes($dir));
								
		$xml->writeAttribute('text', basename(addslashes($dire[1])));
	
		function montaDiretorios($xml, $dir)
		{
			$dh = scandir($dir);
			
			//Percorre o diretório
			foreach($dh as $filename)
			{ 			
				if(!in_array($filename,array(".","..","_excluidos","_versoes")))
				{
					if(is_dir($dir . "/" . $filename))
					{	
						$xml->startElement('item');
	
							$xml->writeAttribute('id', addslashes($dir . "/" . $filename));
													
							$xml->writeAttribute('text', basename(addslashes($dir . "/" . $filename)));
					
							montaDiretorios($xml, $dir . "/" . $filename);
						
						$xml->endElement();	
					}	
				}
			}
		}
		
		montaDiretorios($xml,$dir,$xml_string);
	
		$xml->endElement();	
	
	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addAssign("tree1","innerHTML","");
	
	$resposta->addScript("htree('tree1','".$conteudo."');");	

	return $resposta;
}

//Preenche a lista de arquivos, conforme o diretório clicado pelo usuário
function preencheArquivos($dados_form, $dir = '')
{
	$resposta = new xajaxResponse();

	$db = new banco_dados();
	
	$xml = new XMLWriter();
	
	$conteudo = "";
	
	$array_sql_filtro_proj = NULL;
	
	$array_arquivos = NULL;
	
	$sql_filtro_proj = "";
	
	if($dados_form["id_proposta"]!="")
	{	
		$diretorios = explode("/",str_replace(DOCUMENTOS_ORCAMENTO,"",$dir));		
		
		//separa os niveis dos diretorios para filtro
		//monta a partir da estrutura de disciplina
		// 0 - cliente
		// 1 - os
		// 2 - tipo
		
		if(count($diretorios)>1 || $dados_form["id_proposta"]!="")
		{	
			foreach($diretorios as $chave=>$niveis)
			{
				switch ($chave)
				{
					case 2:
						$sql_filtro_proj .=  "AND tipos_documentos.tipo_documento = '".$niveis."' ";
					break;
				}
			}
			
			$xml->openMemory();
			$xml->setIndent(false);
			$xml->startElement('rows') ;				
			
				//Arquivos de Projeto
				$sql = "SELECT * FROM ".DATABASE.".propostas, ".DATABASE.".tipos_documentos, ".DATABASE.".arquivos_proposta, ".DATABASE.".arquivos_proposta_versoes, ".DATABASE.".funcionarios ";
				$sql .= "WHERE propostas.reg_del = 0 ";
				$sql .= "AND arquivos_proposta.reg_del = 0 ";
				$sql .= "AND tipos_documentos.reg_del = 0 ";
				$sql .= "AND funcionarios.reg_del = 0 ";
				$sql .= "AND arquivos_proposta_versoes.reg_del = 0 ";
				$sql .= "AND propostas.id_proposta = '" . $dados_form["id_proposta"] . "' ";
				$sql .= "AND arquivos_proposta.id_proposta = propostas.id_proposta ";
				$sql .= "AND arquivos_proposta.id_tipo_documento = tipos_documentos.id_tipo_documento ";
				$sql .= "AND arquivos_proposta.id_arquivo_versao = arquivos_proposta_versoes.id_arquivo_versao ";
				$sql .= "AND arquivos_proposta_versoes.id_autor = funcionarios.id_funcionario ";
				
				if($sql_filtro_proj)
				{
					$sql .= $sql_filtro_proj;
				}
				
				$db->select($sql,'MYSQL',true);
				
				if ($db->erro != '')
				{
					$resposta->addAlert("Não foi possível selecionar as informações do arquivo.".$db->erro);	
				}
	
				$array_propostas = $db->array_select;
	
				foreach($array_propostas as $reg_arquivos)
				{									
					//acrescentado em 26/05/2015
					// se arquivo não possuir versoes, lista todos
					//formato: nome_arquivo.extensao.id_arquivo_versao
					$arquivo_sem_versao = "";	
										
					if(in_array($reg_arquivos["id_tipo_documento"],lista_sem_versao()))
					{
						$sql = "SELECT * FROM ".DATABASE.".arquivos_proposta_versoes, ".DATABASE.".funcionarios ";
						$sql .= "WHERE arquivos_proposta_versoes.reg_del = 0 ";
						$sql .= "AND funcionarios.reg_del = 0 ";
						$sql .= "AND arquivos_proposta_versoes.id_arquivo_proposta = '".$reg_arquivos["id_arquivo_proposta"]."' ";
						$sql .= "AND arquivos_proposta_versoes.id_autor = funcionarios.id_funcionario ";
						
						$db->select($sql,'MYSQL',true);
						
						if ($db->erro != '')
						{
							$resposta->addAlert("Não foi possível selecionar as informações do arquivo.".$db->erro);	
						}
			
						foreach($db->array_select as $reg_versoes)
						{
							$a_arquivo[$reg_versoes["id_arquivo_versao"]] = $reg_arquivos["base"] . "/" . sprintf("%05d",intval(trim($reg_arquivos["numero_proposta"]))) . "/" . tiraacentos($reg_arquivos["tipo_documento"]) . "/" . $reg_versoes["arquivo"].".".$reg_versoes["id_arquivo_versao"];
						
							$arquivo_s_versao[$reg_versoes["id_arquivo_versao"]] = $reg_arquivos["base"] . "/" . sprintf("%05d",intval(trim($reg_arquivos["numero_proposta"]))) . "/" . tiraacentos($reg_arquivos["tipo_documento"]) . "/" . $reg_versoes["arquivo"];
						
							$arquivo_tipo[$reg_versoes["id_arquivo_versao"]] = $reg_arquivos["tipo_documento"];
							
							$arquivo_nome[$reg_versoes["id_arquivo_versao"]] = $reg_versoes["arquivo"];
							
							$arquivo_revisao[$reg_versoes["id_arquivo_versao"]] = $reg_versoes["versao_documento"];
							
							$arquivo_versao[$reg_versoes["id_arquivo_versao"]] = $reg_versoes["revisao_documento"];
							
							$arquivo_data[$reg_versoes["id_arquivo_versao"]] = $reg_versoes["data_inclusao"];
							
							$arquivo_data[$reg_versoes["id_arquivo_versao"]] = $reg_versoes["data_inclusao"];
							
							$arquivo_editor[$reg_versoes["id_arquivo_versao"]] = $reg_versoes["nome_usuario"];
						}
					}
					else
					{
						$a_arquivo[$reg_arquivos["id_arquivo_versao"]] = $reg_arquivos["base"] . "/" . sprintf("%05d",intval(trim($reg_arquivos["numero_proposta"]))) . "/" . tiraacentos($reg_arquivos["tipo_documento"]) . "/" . $reg_arquivos["arquivo"];
						
						$arquivo_s_versao[$reg_arquivos["id_arquivo_versao"]] = $reg_arquivos["base"] . "/" . sprintf("%05d",intval(trim($reg_arquivos["numero_proposta"]))) . "/" . tiraacentos($reg_arquivos["tipo_documento"]) . "/" . $reg_arquivos["arquivo"];
					
						$arquivo_tipo[$reg_arquivos["id_arquivo_versao"]] = $reg_arquivos["tipo_documento"];
						
						$arquivo_nome[$reg_arquivos["id_arquivo_versao"]] = $reg_arquivos["arquivo"];
						
						$arquivo_revisao[$reg_arquivos["id_arquivo_versao"]] = $reg_arquivos["versao_documento"];
						
						$arquivo_versao[$reg_arquivos["id_arquivo_versao"]] = $reg_arquivos["revisao_documento"];
						
						$arquivo_editor[$reg_arquivos["id_arquivo_versao"]] = $reg_arquivos["nome_usuario"];
					}

				}
				
				foreach($a_arquivo as $id_arquivo_versao=>$arquivo)
				{
					if(is_file(DOCUMENTOS_ORCAMENTO.$arquivo) && file_exists(DOCUMENTOS_ORCAMENTO.$arquivo))
					{
						//Explode o nome do arquivo
						$extensao_array = explode(".",basename($arquivo_s_versao[$id_arquivo_versao]));
						
						//Pega somente a extensão
						$extensao = $extensao_array[count($extensao_array)-1];
						
						//Pega a imagem referente a extensão
						$imagem = retornaImagem($extensao);					
					
						$xml->startElement('row');
							$xml->writeAttribute('id','ARQ_'.$id_arquivo_versao);
							$xml->writeElement ('cell',$imagem);
							$xml->writeElement ('cell',$arquivo_tipo[$id_arquivo_versao]);
							$xml->writeElement ('cell',$arquivo_nome[$id_arquivo_versao]);
							$xml->writeElement ('cell',$arquivo_revisao[$id_arquivo_versao]);
							$xml->writeElement ('cell',$arquivo_versao[$id_arquivo_versao]);
							$xml->writeElement ('cell',date("d/m/Y",strtotime($arquivo_data[$id_arquivo_versao])));
							$xml->writeElement ('cell',$arquivo_editor[$id_arquivo_versao]);
						$xml->endElement();
					}	
				}
			
			$xml->endElement();
					
			$conteudo = $xml->outputMemory(false);
			
		}
				
		$resposta->addScript("grid('div_arquivos',true,'400','".$conteudo."');");
	}
	else
	{
		$resposta->addAlert("Deve selecionar a OS.");
	}

	$resposta->addScript("divPopupInst.destroi();");
	
	return $resposta;
}

//Preenche a janela de Propriedades
function preenchePropriedades($id_arquivo_versao)
{
	$resposta = new xajaxResponse();

	$db = new banco_dados();
	
	$xml = new XMLWriter();
	
	//seleciona o autor
	$sql = "SELECT id_funcionario, nome_usuario FROM ".DATABASE.".funcionarios ";
	$sql .= "WHERE funcionarios.reg_del = 0 ";
	$sql .= "ORDER BY funcionarios.funcionario ";
	
	$db->select($sql,'MYSQL',true);
	
	if ($db->erro != '')
	{
		$resposta->addAlert("Erro ao tentar selecionar os dados. " . $db->erro);
	}

	foreach($db->array_select as $regs)
	{
		$nome_funcionario[$regs["id_funcionario"]] = $regs["nome_usuario"];
	}

	//Seleciona os dados do arquivo
	$sql = "SELECT * FROM ".DATABASE.".arquivos_proposta, ".DATABASE.".arquivos_proposta_versoes, ".DATABASE.".propostas, ".DATABASE.".tipos_documentos ";
	$sql .= "WHERE arquivos_proposta.id_arquivo_proposta = arquivos_proposta_versoes.id_arquivo_proposta ";
	$sql .= "AND arquivos_proposta.reg_del = 0 ";
	$sql .= "AND arquivos_proposta_versoes.reg_del = 0 ";
	$sql .= "AND propostas.reg_del = 0 ";
	$sql .= "AND tipos_documentos.reg_del = 0 ";
	$sql .= "AND arquivos_proposta.id_proposta = propostas.id_proposta ";
	$sql .= "AND tipos_documentos.id_tipo_documento = arquivos_proposta.id_tipo_documento ";
	$sql .= "AND arquivos_proposta_versoes.id_arquivo_versao = '".$id_arquivo_versao."' ";
	
	$db->select($sql,'MYSQL',true);
	
	if ($db->erro != '')
	{
		$resposta->addAlert("Erro ao tentar selecionar os dados. " . $db->erro);
	}

	$reg_arquivo = $db->array_select[0];
	
	/*
	$sql = "SELECT * FROM AF1010, SA1010 ";
	$sql .= "WHERE AF1010.D_E_L_E_T_ = '' ";
	$sql .= "AND SA1010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF1010.AF1_CLIENT = SA1010.A1_COD ";
	$sql .= "AND AF1010.AF1_LOJA = SA1010.A1_LOJA ";
	$sql .= "AND AF1010.AF1_ORCAME = '".$reg_arquivo["numero_proposta"]."' ";
	
	$db->select($sql,'MSSQL', true);

	if($db->erro!='')
	{
		$resposta->addAlert("Erro ao tentar selecionar os dados do arquivo: " . $db->erro);
	}
	
	$reg2 = $db->array_select[0];
	
	$caminho = DOCUMENTOS_ORCAMENTO.trim(tiraacentos($reg2["A1_NOME"]))."/".sprintf("%05d",intval(trim($reg2["AF1_ORCAME"])))."/".tiraacentos($reg_arquivo["tipo_documento"])."/".tiraacentos($reg_arquivo["arquivo"]);	
	*/

	$arquivo = $reg_arquivo["arquivo"];
	
	$array_extensao = explode(".",$arquivo);
	
	$extensao = $array_extensao[count($array_extensao)-1];
		
	//Pega a imagem relativa a extensão
	$imagem = retornaImagem($extensao);

	//Pega o tamanho do arquivo
	$tamanho = formataTamanho(filesize($caminho));
		
	$autor = $nome_funcionario[$reg_arquivo["id_autor"]];
	
	$data_criacao  = date("d/m/Y H:i:s", filectime($caminho));
	
	$data_modificacao = date("d/m/Y H:i:s", filemtime($caminho));
	
	//Forma o conteúdo da janela de Propriedades
	$janela = '<form method="POST" name="frm_propriedades" id="frm_propriedades">';
		$janela .= '<div id="conteudo" style="font-size:12px; width:100%; margin:10px;">';
			$janela .= '<div id="tipo_arquivo" style="padding:5px;">' . $imagem . ' <label class="labels"><strong>Tipo de arquivo: </strong></label>' . $extensao . '</div>';
			$janela .= '<div id="local" style="padding:5px; border-top-style:groove; border-width:2px;"><label class="labels"><strong>Nome do Arquivo: </strong>' . $reg_arquivo["arquivo"] . '</label></div>';
			$janela .= '<div id="tamanho" style="padding:5px;" onselectstart="return false" unselectable="on"><label class="labels"><strong>Tamanho: </strong>' . $tamanho . '</label></div>';
			$janela .= '<div id="autor" style="padding:5px;" onselectstart="return false" unselectable="on"><label class="labels"><strong>Autor: </strong>' . $autor . '</label></div>';		
			$janela .= '<div id="data_modificacao" style="padding:5px;" onselectstart="return false" unselectable="on"><label class="labels"><strong>Última atualização: </strong>' . $data_modificacao . '</label></div>';
			$janela .=  '<div id="div_versoes" style="overflow:auto;"> </div>';
			$janela .= '<div id="botoes" style="text-align:left; width:90%; margin-top:10px;"><input type="hidden" id="id_arquivo_versao" name="id_arquivo_versao" value="' . $reg_arquivo["id_arquivo_versao"] . '"><input type="button" value="Voltar" onclick = xajax_preencheArquivos(xajax.getFormValues("frm"));></div>';
		$janela .= '</div>';
	$janela .= '</form>';
	
	//Atribue o conteúdo
	$resposta->addAssign("div_conteudo","innerHTML",$janela);
	
	$sql = "SELECT * FROM ".DATABASE.".arquivos_proposta_versoes ";
	$sql .= "WHERE arquivos_proposta_versoes.id_arquivo_proposta = '" . $reg_arquivo["id_arquivo_proposta"] . "' ";
	$sql .= "AND arquivos_proposta_versoes.reg_del = 0 ";
	$sql .= "ORDER BY arquivos_proposta_versoes.revisao_documento DESC ";

	$db->select($sql,'MYSQL',true);
	
	if ($db->erro != '')
	{
		$resposta->addAlert("Erro ao selecionar os dados sobre versões: " . $db->erro);
	}
	
	$xml->openMemory();
	$xml->setIndent(false);
	$xml->startElement('rows') ;

	//Forma o conteúdo das versões
	foreach($db->array_select as $reg_versoes)
	{
		//Explode o nome do arquivo em um array
		$array_extensao = explode(".",$reg_versoes["arquivo"]);
		
		$extensao = $array_extensao[count($array_extensao)-1];
		
		//Pega a imagem relativa a extensão
		$imagem = retornaImagem($extensao);

		$array_qtde_espacos = explode(" ",$reg_versoes["arquivo"]);
		
		if(strlen($reg_versoes["arquivo"])>40 && count($array_qtde_espacos)==1)
		{
			$nome_arquivo = substr($reg_versoes["arquivo"],0,40) . "...";
			
			$title_arquivo = $reg_versoes["arquivo"]; 
		}
		elseif(strlen($reg_versoes["arquivo"])>30)
		{
			$nome_arquivo = substr($reg_versoes["arquivo"],0,30) . "...";
			
			$title_arquivo = $reg_versoes["arquivo"];				 
		}
		else
		{
			$nome_arquivo = $reg_versoes["arquivo"];
			
			$title_arquivo = "";
		}
		
		$xml->startElement('row');
			//se versão não é a atual, abre o arquivo versoes
			if($reg_arquivo["id_arquivo_versao"]!=$reg_versoes["id_arquivo_versao"])
			{
				$xml->writeAttribute('id','ARQ_' . $reg_versoes["id_arquivo_versao"] . '_VER');
			}
			else
			{
				$xml->writeAttribute('id','ARQ_' . $reg_arquivo["id_arquivo_versao"]);
			}
					
			$xml->writeElement ('cell', $imagem);
			$xml->writeElement ('cell', $nome_arquivo);
			$xml->writeElement ('cell', $reg_versoes["versao_documento"].'.'.$reg_versoes["revisao_documento"]);
			$xml->writeElement ('cell', date("d/m/Y",strtotime($reg_versoes["data_inclusao"])));
			$xml->writeElement ('cell', $nome_funcionario[$reg_versoes["id_autor"]]);

		$xml->endElement();						
	}
	
	$xml->endElement();
			
	$conteudo_tbl = $xml->outputMemory(false);
		
	$resposta->addScript("grid('div_versoes',true,'130','".$conteudo_tbl."');");
	
	return $resposta;
}

// Exclui o arquivo do sistema
function excluir($id_arquivo_versao)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados();	

	//Seleciona os dados do arquivo atual
	$sql = "SELECT * FROM ".DATABASE.".arquivos_proposta, ".DATABASE.".arquivos_proposta_versoes, ".DATABASE.".tipos_documentos, ".DATABASE.".propostas ";
	$sql .= "WHERE arquivos_proposta.id_arquivo_proposta = arquivos_proposta_versoes.id_arquivo_proposta ";
	$sql .= "AND arquivos_proposta.reg_del = 0 ";
	$sql .= "AND arquivos_proposta_versoes.reg_del = 0 ";
	$sql .= "AND tipos_documentos.reg_del = 0 ";
	$sql .= "AND propostas.reg_del = 0 ";
	$sql .= "AND arquivos_proposta.id_proposta = propostas.id_proposta ";
	$sql .= "AND arquivos_proposta.id_tipo_documento = tipos_documentos.id_tipo_documento ";
	$sql .= "AND arquivos_proposta_versoes.id_arquivo_versao = '".$id_arquivo_versao."' ";
	
	$db->select($sql,'MYSQL',true);
	
	if ($db->erro != '')
	{
		$resposta->addAlert("Erro ao selecionar os dados: " . $db->erro);
	}
	
	$n_registros_inicial = $db->numero_registros;
	
	$reg_checa = $db->array_select[0];
	
	/*
	$sql = "SELECT * FROM AF1010, SA1010 ";
	$sql .= "WHERE AF1010.D_E_L_E_T_ = '' ";
	$sql .= "AND SA1010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF1010.AF1_CLIENT = SA1010.A1_COD ";
	$sql .= "AND AF1010.AF1_LOJA = SA1010.A1_LOJA ";
	$sql .= "AND AF1010.AF1_ORCAME = '".$reg_checa["numero_proposta"]."' ";
	
	$db->select($sql,'MSSQL', true);
	
	$reg2 = $db->array_select[0];

	if($n_registros_inicial > 0)
	{
		$caminho = DOCUMENTOS_ORCAMENTO.trim(tiraacentos($reg2["A1_NOME"]))."/".sprintf("%05d",intval(trim($reg2["AF1_ORCAME"])))."/".tiraacentos($reg_checa["tipo_documento"])."/";
		
		//Agora não removemos mais o arquivo, e sim movemos o arquivo para a pasta excluídos				
		//Se não existir o   de exclusao, cria
		if(!is_dir($caminho . DIRETORIO_EXCLUIDOS))
		{
			mkdir($caminho . DIRETORIO_EXCLUIDOS);
		}
		
		// se for arquivo sem versoes, acrescenta o id_arquivo_versao
		if(in_array($reg_checa["id_tipo_documento"],lista_sem_versao()))
		{
			$nome_arquivo = $caminho."/".$reg_checa["arquivo"].".".$reg_checa["id_arquivo_versao"];
		}
		else
		{
			$nome_arquivo = $caminho."/".$reg_checa["arquivo"];
		}
		
		//remove o arquivo atual para o diretorio de excluídos				
		$remove_arquivo = rename($nome_arquivo, $caminho.DIRETORIO_EXCLUIDOS."/".$reg_checa["arquivo"].".".$reg_checa["id_arquivo_versao"]);
	
		$id_arquivo_versao = $reg_checa["id_arquivo_versao"];
		
		//marca como excluido
		$usql = "UPDATE ".DATABASE.".arquivos_proposta_versoes	SET ";
		$usql .= "reg_del = 1, ";
		$usql .= "reg_who = '".$_SESSION['id_funcionario']."', ";
		$usql .= "data_del = '".date('Y-m-d')."' ";
		$usql .= "WHERE id_arquivo_versao = '" . $id_arquivo_versao . "' ";
		$usql .= "AND reg_del = 0 ";
		
		$db->update($usql, 'MYSQL');
		
		if ($db->erro != '')
		{
			$resposta->addAlert("Erro ao tentar excluir os dados do banco: \n\n" . $db->erro);
		}
		
		//se não for arquivo de versões, atualiza para a versão anterior
		if(!in_array($reg_checa["id_tipo_documento"],lista_sem_versao()))
		{
			//pega a revisao_documento anterior / ultima revisao_documento
			$sql = "SELECT * FROM ".DATABASE.".arquivos_proposta_versoes ";
			$sql .= "WHERE arquivos_proposta_versoes.reg_del = 0 ";
			$sql .= "AND arquivos_proposta_versoes.id_arquivo_proposta = '".$reg_checa["id_arquivo_proposta"]."' ";
			$sql .= "ORDER BY revisao_documento DESC LIMIT 1 ";
	
			$db->select($sql,'MYSQL',true);
			
			if ($db->erro != '')
			{
				$resposta->addAlert("Erro ao selecionar os dados: " . $db->erro);
			}
			
			$reg_ver = $db->array_select[0];
			
			//move o arquivo revisao_documento para o diretorio raiz				
			$move_arquivo = rename($caminho."/".DIRETORIO_VERSOES."/".$reg_ver["arquivo"].".".$reg_ver["id_arquivo_versao"], $caminho."/".$reg_ver["arquivo"]);
	
			$usql = "UPDATE ".DATABASE.".arquivos_proposta	SET ";
			$usql .= "id_arquivo_versao = '".$reg_ver["id_arquivo_versao"]."' "; 
			$usql .= "WHERE id_arquivo_proposta = '" . $reg_checa["id_arquivo_proposta"] . "' ";
			$usql .= "AND reg_del = 0 ";
			
			$db->update($usql, 'MYSQL');
			
			if ($db->erro != '')
			{
				$resposta->addAlert("Erro ao tentar excluir os dados do banco: \n\n" . $db->erro);
			}
		}
	}
	
	*/

	//Se o arquivo foi removido com sucesso
	if($remove_arquivo && ($move_arquivo || !in_array($reg_checa["id_tipo_documento"],lista_sem_versao())))
	{		
		$resposta->addAlert("Arquivo excluído com sucesso.");
		$resposta->addScript("xajax_preencheArquivos(xajax.getFormValues('frm'));");				
	}
	else
	{	
		$resposta->addAlert("Ocorreram erros ao tentar remover os arquivos.");			
	}

	return $resposta;
}

$xajax->registerFunction("seleciona_opcoes");
$xajax->registerFunction("abrir");
$xajax->registerFunction("preenchePastas");
$xajax->registerFunction("preencheArquivos");
$xajax->registerFunction("preenchePropriedades");
$xajax->registerFunction("excluir");


$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));
?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="ged_orcamento.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script>

function startUpload_orcamento()
{
	  document.getElementById('inf_upload').innerHTML = '<img width="100px" src="../imagens/loader.gif" />';	
      document.getElementById('inf_upload').style.visibility = 'visible';
	  
	  setTimeout('',1000);
	    
      return true;
}

function stopUpload_orcamento(id_proposta,success,erro,dir)
{
      var result = '';

	  if (success == 1)
	  {
		 result = '<span class="labels">Concluído!</span>';
	  }
	  else 
	  {
		 result = '<span class="labels">Erro! '+erro+'</span>';
	  }      
	  
	  document.getElementById('inf_upload').innerHTML = result;
	  
	  xajax_preenchePastas(dir);
	  
	  xajax_preencheArquivos(xajax.getFormValues('frm'));
	    
      return true;   
}

//desabilita right click
document.oncontextmenu = function(){return false};

var myTree;

function grid(tabela, autoh, height, xml)
{

	function doOnRowDblClicked(row,col)
	{
		if(col>=1 && col<=10)
		{
			xajax_abrir(row);
			
			return true;
		}
		
		return false;
	}
	
	function doOnRightClick(row,col)
	{
		if(col>=1 && col<=10)
		{	
			//pega as coordenadas do mouse
			var e = window.event;
			
			xc = e.clientX;
			yc = e.clientY;
			
			xajax_seleciona_opcoes(row,xc,yc);
			
			return true;
		}
		
		return false;
	}
	
	mygrid = new dhtmlXGridObject(tabela);

	mygrid.enableAutoHeight(autoh,height);
	
	switch(tabela)
	{
		case 'div_arquivos': //tabela principal
				mygrid.attachEvent("onRowDblClicked",doOnRowDblClicked);
			mygrid.attachEvent("onRightClick",doOnRightClick);
			mygrid.setHeader(" ,Tipo Documento,Arquivo,R,V,Data,Autor");
			mygrid.setInitWidths("22,150,120,40,40,80,130");
			mygrid.setColAlign("center,left,left,center,center,left,left");
			mygrid.setColTypes("ro,ro,ro,ro,ro,ro,ro");
			mygrid.setColSorting("int,str,str,str,str,str,str");		
		break;
	
		case 'div_versoes': //tabela propriedades
			//mygrid.attachEvent("onRowSelect",doOnRowSelected);
			mygrid.attachEvent("onRowDblClicked",doOnRowDblClicked);
			//mygrid.attachEvent("onRightClick",doOnRightClick);
			mygrid.setHeader(" ,Nome Arquivo,R/V,Data,Autor");
			mygrid.setInitWidths("22,150,40,80,120");
			mygrid.setColAlign("center,left,center,left,left");
			mygrid.setColTypes("ro,ro,ro,ro,ro");
			mygrid.setColSorting("str,str,str,str,str");		
		break;
	
	}

	mygrid.enableRowsHover(true,'cor_mouseover');
	mygrid.setSkin("dhx_skyblue");
    mygrid.enableMultiselect(true);
    mygrid.enableCollSpan(true);	
	mygrid.init();
	mygrid.loadXMLString(xml);	
}

function htree(tree,xml)
{
	function tonclick(id)
	{
		xajax_preencheArquivos(xajax.getFormValues('frm'),id);
	}
		
	myTree = new dhtmlXTreeObject(tree,"100%","100%",0);
	myTree.setSkin('dhx_skyblue');
	myTree.setOnClickHandler(tonclick);
	myTree.loadXMLString(xml);
}

function popupMenu(x,y,id_arquivo_versao)
{	
	RCmenuInst = new RCmenu();	

	var status_propriedades = 1;
	var status_excluir = 1;
		
	if(id_arquivo_versao)
	{	
		//Forma os itens do menu	
		var array_itens = new Array();
		
		array_itens[array_itens.length] = ['Abrir', function () {RCmenuInst.destroi();xajax_abrir('ARQ_'+id_arquivo_versao); },1,0];
		array_itens[array_itens.length] = ['Excluir', function () { if(confirm('Isso irá excluir o arquivo definitivamente. Deseja continuar?')){RCmenuInst.destroi();xajax_excluir(id_arquivo_versao);} },status_excluir,0];
		array_itens[array_itens.length] = ['Propriedades', function () {RCmenuInst.destroi();popupPropriedades(id_arquivo_versao); }, status_propriedades,1];
	}

	if(event.type=="click")
	{
		RCmenuInst.destroi();	
	}
	else
	{
		RCmenuInst.altura = '20px';
		
		RCmenuInst.insere(x,y, array_itens);
	}
	
}

function open_doc(dir)
{
	window.open("../includes/documento.php?documento="+dir,"_blank");
}


</script>

<?php

$conf = new configs();

$db = new banco_dados();

$sql = "SELECT * FROM ".DATABASE.".propostas ";
$sql .= "WHERE propostas.reg_del = 0 ";
$sql .= "ORDER BY numero_proposta DESC ";

$db->select($sql,'MYSQL',true);

if ($db->erro != '')
{
	die("Não foi possível realizar a seleção: ". $db->erro); 
}

$array_propostas = $db->array_select;

/*
foreach ($array_propostas as $regs)
{	
	$sql = "SELECT * FROM AF1010, SA1010 ";
	$sql .= "WHERE AF1010.D_E_L_E_T_ = '' ";
	$sql .= "AND SA1010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF1010.AF1_CLIENT = SA1010.A1_COD ";
	$sql .= "AND AF1010.AF1_LOJA = SA1010.A1_LOJA ";
	$sql .= "AND AF1010.AF1_ORCAME = '".$regs["numero_proposta"]."' ";
	
	$db->select($sql,'MSSQL',true);

	if($db->erro!='')
	{
		die('Erro.');
	}
	else
	{
		if($db->numero_registros_ms > 0)
		{
			$array_proposta_values[] = $regs["id_proposta"];
			$array_proposta_output[] = $regs["numero_proposta"] . " - " . substr($regs["descricao_proposta"],0,40);
		}
	}
}
*/

$sql = "SELECT * FROM ".DATABASE.".tipos_documentos ";
$sql .= "WHERE tipos_documentos.reg_del = 0 ";

$db->select($sql,'MYSQL',true);

if ($db->erro != '')
{
	die("Não foi possível realizar a seleção: ". $db->erro); 
}

foreach ($db->array_select as $regs)
{
	
	$array_tipo_values[] = $regs["id_tipo_documento"];
	$array_tipo_output[] = $regs["tipo_documento"];	
}

$smarty->assign("revisao_documento","V1");

$smarty->assign("campo",$conf->campos('ged_orcamento'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("option_proposta_values",$array_proposta_values);
$smarty->assign("option_proposta_output",$array_proposta_output);

$smarty->assign("option_tipo_values",$array_tipo_values);
$smarty->assign("option_tipo_output",$array_tipo_output);

$smarty->assign("classe",CSS_FILE);

$smarty->display('ged_orcamento.tpl');
?>