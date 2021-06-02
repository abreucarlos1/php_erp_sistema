<?php
/**
 *
 *		Formulário de Gerenciamento Eletrônico de Documentos	
 *		
 *		Criado por Carlos Abreu  
 *		
 *		local/Nome do arquivo:
 *		area_clientes/ged_clientes.php
 *		
 *		data de criação: 04/06/2014
 *		
 *		Versão 0 --> VERSÃO INICIAL
 *		Versão 1 --> Atualização de banco, e-mail - 20/01/2015 - Carlos Abreu	
 */	

require("../includes/include_form.inc.php");

function start()
{
	$resposta = new xajaxResponse();
	
	return $resposta;
}

function abrir($caminho, $nome_original="")
{
	$resposta = new xajaxResponse();
	
	if($_SESSION["id_funcionario"]==666666)
	{
		$resposta->addScript('open_doc("'.$caminho.'")');
	}
	else
	{
		
		//Verifica se existe o arquivo no caminho fornecido
		if(!is_file($caminho))
		{
			$resposta->addAlert("Ocorreu um erro ao tentar abrir o arquivo. ".$caminho);
		}
		else
		{
			//Se for fornecido o segundo argumento
			if($nome_original)
			{
				// Cria um cache do arquivo (com o nome original, sem a extensão da versão) antes de enviar para o usuário
				// Para evitar o cache do navegador e outros problemas, cria-se um diretório de cache com nome  randômico
				// Sempre remove o diretório temporário antes de iniciar a operação
				// Diretório temporário = "/tmp/"
				// Diretório de cache temporário = "/tmp/(número randômico)/"
		
				//Explode o caminho do arquivo original
				$array_arquivo = explode("/",$nome_original);
				
				//Pega o nome do arquivo
				$arquivo_original = $array_arquivo[count($array_arquivo)-1];
				
				//Remove o último item (nome do arquivo)
				array_pop($array_arquivo);
				
				//Pega o diretório sem o nome do arquivo
				$diretorio_original = implode("/",$array_arquivo);
			
				//Remove o   temporário
				full_rmdir($diretorio_original . DIRETORIO_VERSOES."/tmp/");
		
				//Se não existir o   de versões, cria
				if(!is_dir($diretorio_original . DIRETORIO_VERSOES))
				{
					mkdir($diretorio_original . DIRETORIO_VERSOES);
				}		
				
				//Se não existir o   temporário, cria
				if(!is_dir($diretorio_original . DIRETORIO_VERSOES."/tmp/"))
				{
					mkdir($diretorio_original . DIRETORIO_VERSOES."/tmp/");
				}
					
				//Cria numeração  randômica para nomear   de cache temporário
				$nr_rnd = rand(100000,999999);
				
				//Se não existir o   de cache temporário, cria
				if(!is_dir($diretorio_original . DIRETORIO_VERSOES. "/tmp/" . $nr_rnd . "/"))
				{
					mkdir($diretorio_original . DIRETORIO_VERSOES. "/tmp/" . $nr_rnd . "/");
				}			
				
				//Se o arquivo de cache temporário existir, remove
				if(is_file($diretorio_original . DIRETORIO_VERSOES. "/tmp/" . $nr_rnd . "/" . $arquivo_original))
				{
					unlink($diretorio_original . DIRETORIO_VERSOES."/tmp/" . $nr_rnd . "/" . $arquivo_original);
				}
		
				//Copia o arquivo com a extensão da versão (.xxx.xxx) para a raiz do   temporário, com o nome original do arquivo
				$copia_arquivo = copy($caminho, $diretorio_original . DIRETORIO_VERSOES."/tmp/" . $nr_rnd . "/" . $arquivo_original);

				$resposta->addScript('open_doc("'.urlencode($diretorio_original . DIRETORIO_VERSOES."/tmp/" . $nr_rnd . "/" . $arquivo_original).'")');
			}
			else
			{
				$resposta->addScript("open_doc('".$caminho."');");
			}
		
			$resposta->addScript("RCmenuInst.destroi(); ");
		}
	}

	return $resposta;
}

//Preenche a lista de pastas
function preenchePastas($dir, $dir_selecionado='')
{
	//Preenche a lista de pastas
	//Cria uma saída em XML	
	//Parte do   fornecido em $dir
	$resposta = new xajaxResponse();

	//Instancia o objeto
	$xml = new xmlWriter();
	
	$xml->openMemory();
	
	//Elemento raiz
	$xml->startElement('xmp');
	
	if($_SESSION["id_funcionario"]==666666666)	
	{
	
		function montaDiretorios($xml, $dir,$xml_string, $dir_selecionado)
		{
			$dh = new DirectoryIterator($dir);
			
			foreach($dh as $filename)
			{ 			
				if(!in_array($filename->getFilename(),array(".","..","_versoes","_comentarios")))
				{
					if($filename->isDir())
					{	
						$xml->startElement('item');
				 
							//$xml->writeAttribute('text', htmlentities(basename($dir . "/" . $filename)));
							$xml->writeAttribute('text', htmlentities($filename->getBasename()));
						
							$xml->writeAttribute('im0', 'folderClosed.gif');
							
							if(preg_match("/-DISCIPLINAS/",$filename->getPathname()))
							{
								$xml->writeAttribute('id', "DIS_".$filename->getPathname());
							}
							else
							{
								$xml->writeAttribute('id', "REF_".$filename->getPathname());
							}
							
							$xml->writeAttribute('child','1');
						
							$xml->startElement('userdata');
							
							$xml->writeAttribute('name', 'value');	

							$xml->text(htmlentities($filename->getPathname()));
						
							$xml->endElement(); //userdata
		
							montaDiretorios($xml, $filename->getPathname(), $xml_string, $dir_selecionado);
						
							$xml->endElement(); //item
					}						
				}
			}

			return $xml->outputMemory(false);	
		}
	}
	else
	{
		function montaDiretorios($xml, $dir,$xml_string, $dir_selecionado)
		{
			$dh = scandir($dir);	
			
			//Percorre o  
			foreach($dh as $filename)
			{ 			
				if(!in_array($filename,array(".","..","_versoes","_comentarios")))
				{
					if(is_dir($dir . "/" . $filename))
					{	
						$xml->startElement('item');
						 
							$xml->writeAttribute('text', htmlentities(basename($dir . "/" . $filename)));
						
							$xml->writeAttribute('im0', 'folderClosed.gif');
							
							if(preg_match("/-DISCIPLINAS/",$dir."/".$filename))
							{
								$xml->writeAttribute('id', "DIS_".$dir . "/" . $filename);
							}
							else
							{
								$xml->writeAttribute('id', "REF_".$dir . "/" . $filename);
							}
							$xml->writeAttribute('child','1');
						
							$xml->startElement('userdata');
							
							$xml->writeAttribute('name', 'value');	
							
							$xml->text(htmlentities($dir . "/" . $filename));

							$xml->endElement(); //userdata
		
							montaDiretorios($xml, $dir . "/" . $filename, $xml_string, $dir_selecionado);
					
							$xml->endElement(); //item
					}	
				}
			}
			
			return $xml->outputMemory(false);	
		}		
	}
	
	$resposta->addAssign("tree1","innerHTML",montaDiretorios($xml,$dir,$xml_string, $dir_selecionado)); 

	$resposta->addScript("htree('tree1');");	

	if($dir_selecionado)
	{
		$resposta->addScript("myTree.openItem('" . $dir_selecionado . "'); ");
	}

	$resposta->addScript("xajax_seta_checkin_checkout(document.getElementById(id_os).value);");

	$resposta->addScript("divPopupInst.destroi(); ");

	return $resposta;
}

//Preenche a lista de arquivos, conforme o   clicado pelo usuário
function preencheArquivos($dados_form, $dir = '', $tipo = '')
{
	//Preenche a lista de arquivos, conforme o   clicado pelo usuário
	
	$resposta = new xajaxResponse();

	//Instancia o objeto do bd
	$db = new banco_dados();
	
	$conteudo = "";
	
	$array_sql_filtro_proj = NULL;
	
	$array_sql_filtro_ref = NULL;
	
	$array_arquivos = NULL;
	
	$sql_filtro_proj = "";
	
	$sql_filtro_ref = "";	
	
	$tipo_dir = explode("_",$tipo);
	
	if($dados_form["id_os"]!="")
	{	
		//seleciona o autor
		$sql = "SELECT id_funcionario,nome_usuario FROM ".DATABASE.".funcionarios ";
		$sql .= "ORDER BY funcionarios.funcionario ";
		
		$cont = $db->select($sql,'MYSQL');
	
		while($regs = mysqli_fetch_assoc($cont))
		{
			$nome_funcionario[$regs["id_funcionario"]] = $regs["nome_usuario"];
		}
		
		//se não tiver dir informado (evento click das pastas)
		if(!$dir)
		{
			$dados_form["disciplina"] ? $sql_filtro_proj .= "AND numeros_interno.id_disciplina = '" . $dados_form["disciplina"] . "' " : "";	
			$dados_form["disciplina"] ? $sql_filtro_ref .= "AND documentos_referencia.id_disciplina = '" . $dados_form["disciplina"] . "' " : "";	
			
			$dados_form["id_atividade"] ? $sql_filtro_proj .= "AND numero_cliente.id_atividade = '" . $dados_form["id_atividade"] . "' " : "";
				
			$dados_form["txt_busca_inicial"] ? $array_sql_filtro_proj[] = "(solicitacao_documentos_detalhes.tag LIKE '%" . $dados_form["txt_busca_inicial"] . "%' OR solicitacao_documentos_detalhes.tag2 LIKE '%" . $dados_form["txt_busca_inicial"] . "%' OR solicitacao_documentos_detalhes.tag3 LIKE '%" . $dados_form["txt_busca_inicial"] . "%' OR solicitacao_documentos_detalhes.tag3 LIKE '%" . $dados_form["txt_busca_inicial"] . "%' OR solicitacao_documentos_detalhes.tag4 LIKE '%" . $dados_form["txt_busca_inicial"] . "%' OR numero_cliente.numero_cliente LIKE '%" . $dados_form["txt_busca_inicial"] . "%'  OR numeros_interno.sequencia LIKE '%" . $dados_form["txt_busca_inicial"] . "%' ) " : "";		
			$dados_form["txt_busca_inicial"] ? $array_sql_filtro_ref[] = "(tipos_documentos_referencia.tipo_documento LIKE '%" . $dados_form["txt_busca_inicial"] . "%' OR tipos_referencia.pasta_base LIKE '%" . $dados_form["txt_busca_inicial"] . "%' OR tipos_referencia.tipo_referencia LIKE '%" . $dados_form["txt_busca_inicial"] . "%' OR documentos_referencia.numero_registro LIKE '%" . $dados_form["txt_busca_inicial"] . "%' OR documentos_referencia.titulo LIKE '%" . $dados_form["txt_busca_inicial"] . "%' OR documentos_referencia.numero_documento LIKE '%" . $dados_form["txt_busca_inicial"] . "%' ) " : "";		
	
			//projetos
			if(count($array_sql_filtro_proj)>0)
			{
				$sql_filtro_proj .= "AND (";		
		
				foreach($array_sql_filtro_proj as $chave=>$valor)
				{
					//Adiciona "AND" exceto no primeiro item
					$sql_operador = $chave > 0 ? "AND " : "";
					$sql_filtro_proj .= $sql_operador . $valor;
				}
				
				$sql_filtro_proj .= ")";	
			}
			
			//referencias
			if(count($array_sql_filtro_ref)>0)
			{
				$sql_filtro_ref .= "AND (";		
		
				foreach($array_sql_filtro_ref as $chave=>$valor)
				{
					//Adiciona "AND" exceto no primeiro item
					$sql_operador = $chave > 0 ? "AND " : "";
					$sql_filtro_ref .= $sql_operador . $valor;
				}
				
				$sql_filtro_ref .= ")";	
			}
		}
		else
		{
			$diretorios = explode("/",str_replace(DOCUMENTOS_GED,"",$dir));
			
			//PROJETOS
			if($tipo_dir[0]=="DIS")
			{
				//separa os niveis dos diretorios para filtro
				//monta a partir da estrutura de disciplina
				// 0 - base
				// 1 - os
				// 2 - fixo(XXXX-DISCIPLINAS)
				// 3 - disciplina
				// 4 - atividade
				// 5 - sequencial
				// 6 - Descrição do arquivo 
				if(count($diretorios)>3)
				{	
					foreach($diretorios as $chave=>$niveis)
					{
						switch ($chave)
						{
							case 3:
								$sql_filtro_proj .= $niveis ? "AND ged_versoes.disciplina = '".$niveis."' " : "";
							break;
							
							case 4:
								$sql_filtro_proj .= $niveis ? "AND ged_versoes.atividade = '".$niveis."' " : "";
							break;
							
							case 5:
								$sql_filtro_proj .= $niveis ? "AND ged_versoes.sequencial = '".$niveis."' " : "";
							break;
							
							case 6:
								$sql_filtro_proj .= "AND (solicitacao_documentos_detalhes.tag LIKE '%" . $niveis . "%' OR solicitacao_documentos_detalhes.tag2 LIKE '%" . $niveis . "%' OR solicitacao_documentos_detalhes.tag3 LIKE '%" . $niveis . "%' OR solicitacao_documentos_detalhes.tag3 LIKE '%" . $niveis . "%' OR solicitacao_documentos_detalhes.tag4 LIKE '%" . $niveis . "%' OR numero_cliente.numero_cliente LIKE '%" . $niveis . "%'  OR numeros_interno.sequencia LIKE '%" . $niveis . "%' ) ";
							break;	
						}
					}
				}
			}
			else
			{
				if(count($diretorios)>3)
				{
					foreach($diretorios as $chave=>$niveis)
					{
						switch ($chave)
						{
							case 3:
								$sql_filtro_ref .= $niveis ? "AND tipos_referencia.pasta_base= '".$niveis."' " : "";
							break;
							
							case 4:
								$sql_filtro_ref .= $niveis ? "AND setores.abreviacao = '".$niveis."' " : "";
							break;
							
							case 5:
								$sql_filtro_ref .= $niveis ? "AND tipos_documentos_referencia.tipo_documento = '".$niveis."' " : "";
							break;	
						}
					}
				}
				else
				{
					foreach($diretorios as $chave=>$niveis)
					{
						switch ($chave)
						{
							case 3:						
								$dis = explode("-",$niveis);
							
								$filtro .= $niveis ? "AND setores.abreviacao = '".$dis[1]."' " : "";
							break;
							
							case 4:
								$filtro .= $niveis ? "AND setores.abreviacao = '".$niveis."' " : "";
							break;
							
							case 6:
								$filtro .= $niveis ? $filtro .= "AND (tipos_documentos_referencia.tipo_documento LIKE '%" . $niveis . "%' OR tipos_referencia.pasta_base LIKE '%" . $niveis . "%' OR tipos_referencia.tipo_referencia LIKE '%" . $niveis . "%' OR documentos_referencia.numero_registro LIKE '%" . $niveis . "%' OR documentos_referencia.titulo LIKE '%" . $niveis . "%' OR documentos_referencia.numero_documento LIKE '%" . $niveis . "%' ) " : "";
							break;	
						}
		
					}
					
					$sql .= $filtro;				
				}	
			}
		}		
		
		if($tipo_dir[0]=="" || $tipo_dir[0]=="DIS")
		{
			//Arquivos de Projeto
			$sql = "SELECT os.os, OS.id_os, setores.sigla, numeros_interno.sequencia, ged_arquivos.id_ged_arquivo, ged_versoes.arquivo, ged_versoes.base, ged_versoes.os, ged_versoes.disciplina, ged_versoes.atividade, ged_versoes.strarquivo, ged_versoes.sequencial, ged_versoes.nome_arquivo, ged_arquivos.status, ged_arquivos.situacao, ged_arquivos.id_autor, ged_arquivos.id_editor, ged_versoes.id_ged_pacote, ged_versoes.revisao_documento, ged_versoes.versao_documento, ged_versoes.id_fin_emissao, ged_versoes.status_devolucao, ged_arquivos.descricao, numero_cliente.numero_cliente, numeros_interno.id_disciplina 
			FROM ".DATABASE.".OS, ".DATABASE.".setores, ".DATABASE.".solicitacao_documentos_detalhes, ".DATABASE.".numeros_interno, ".DATABASE.".numero_cliente, ".DATABASE.".ged_arquivos, ".DATABASE.".ged_versoes ";
			$sql .= "WHERE ged_arquivos.id_ged_versao = ged_versoes.id_ged_versao ";
			$sql .= "AND ged_arquivos.reg_del = 0 ";
			$sql .= "AND ged_versoes.reg_del = 0 ";
			$sql .= "AND ged_arquivos.id_numero_interno = numeros_interno.id_numero_interno ";
			$sql .= "AND numeros_interno.id_numcliente = numero_cliente.id_numcliente ";
			$sql .= "AND numeros_interno.id_os = OS.id_os ";
			$sql .= "AND numeros_interno.reg_del = 0 ";
			$sql .= "AND numeros_interno.id_disciplina = setores.id_setor ";
			$sql .= "AND solicitacao_documentos_detalhes.reg_del = 0 ";
			$sql .= "AND numero_cliente.reg_del = 0 ";
			$sql .= "AND numeros_interno.id_os = '" . $dados_form["id_os"] . "' ";
			
			if($sql_filtro_proj)
			{
				$sql .= $sql_filtro_proj;
			}
			
			$sql .= "ORDER BY setores.abreviacao, ged_versoes.sequencial, ged_versoes.atividade ";
			
			$cont_arquivos = $db->select($sql,'MYSQL');	
		
			while($reg_arquivos = mysqli_fetch_assoc($cont_arquivos))
			{
				//Armazena os dados em arrays, para uso posterior
				$arquivo_id[$reg_arquivos["base"] . "/" . $reg_arquivos["os"] . "/" . substr($reg_arquivos["os"],0,4) . DISCIPLINAS . $reg_arquivos["disciplina"] . "/" . $reg_arquivos["atividade"] . "/" . $reg_arquivos["sequencial"] . "/" . $reg_arquivos["nome_arquivo"]] = $reg_arquivos["id_ged_arquivo"];
				
				$descricao_numdvm = "INT-" . sprintf("%05d",$reg_arquivos["os"]) . "-" . $reg_arquivos["sigla"] . "-" .$reg_arquivos["sequencia"];
				
				$arquivo_descricao[$reg_arquivos["id_ged_arquivo"]] = $descricao_numdvm;
		
				$arquivo_numcliente[$reg_arquivos["id_ged_arquivo"]] = $reg_arquivos["numero_cliente"];
		
				$arquivo_status[$reg_arquivos["id_ged_arquivo"]] = $reg_arquivos["status"];
				
				$arquivo_situacao[$reg_arquivos["id_ged_arquivo"]] = $reg_arquivos["situacao"];
				
				$arquivo_autor[$reg_arquivos["id_ged_arquivo"]] = $nome_funcionario[$reg_arquivos["id_autor"]]; //autor
				
				$arquivo_editor[$reg_arquivos["id_ged_arquivo"]] = $nome_funcionario[$reg_arquivos["id_editor"]]; //editor
				
				$arquivo_pacote[$reg_arquivos["id_ged_arquivo"]] = $reg_arquivos["id_ged_pacote"];
		
				$arquivo_revisao_cli[$reg_arquivos["id_ged_arquivo"]] = $reg_arquivos["revisao_cliente"];
				
				$arquivo_revisao_dvm[$reg_arquivos["id_ged_arquivo"]] = $reg_arquivos["revisao_interna"];
		
				$arquivo_versao[$reg_arquivos["id_ged_arquivo"]] = $reg_arquivos["versao_"];
		
				$arquivo_fin_emissao[$reg_arquivos["id_ged_arquivo"]] = $reg_arquivos["id_fin_emissao"];
			
				$arquivo_status_dev[$reg_arquivos["id_ged_arquivo"]] = $reg_arquivos["status_devolucao"];
						
			}
		}
		
		if($tipo_dir[0]=="" || $tipo_dir[0]=="REF")
		{		
			//Arquivos de referencia
			$sql = "SELECT * FROM ".DATABASE.".empresas, ".DATABASE.".OS, ".DATABASE.".setores, ".DATABASE.".documentos_referencia, ".DATABASE.".documentos_referencia_revisoes, ".DATABASE.".tipos_referencia, ".DATABASE.".tipos_documentos_referencia ";
			$sql .= "WHERE documentos_referencia.id_documento_referencia_revisoes = documentos_referencia_revisoes.id_documentos_referencia_revisoes ";
			$sql .= "AND documentos_referencia.id_os = OS.id_os ";
			$sql .= "AND documentos_referencia.id_tipo_documento_referencia = tipos_documentos_referencia.id_tipos_documentos_referencia ";
			$sql .= "AND tipos_documentos_referencia.id_tipo_referencia = tipos_referencia.id_tipo_referencia ";
			$sql .= "AND documentos_referencia.id_disciplina = setores.id_setor ";
			$sql .= "AND OS.id_empresa = empresas.id_empresa ";
			$sql .= "AND documentos_referencia.id_os = '" . $dados_form["id_os"] . "' ";
			
			if($sql_filtro_ref)
			{
				//$sql .= "AND documentos_referencia.id_os = '".$reg_filtro["id_os"]."' ";
				$sql .= $sql_filtro_ref;
			}
			
			$cont_arquivos_ref = $db->select($sql,'MYSQL');
		
			while($reg_arquivos_ref = mysqli_fetch_assoc($cont_arquivos_ref))
			{				
				//$diretorio = DOCUMENTOS_GED . $reg_docs["abreviacao_GED"] . "/" . $reg_docs["os"] . "-" .$reg_docs["descricao"] . "/" . $reg_docs["os"] . "-REFERENCIAS/" . $reg_docs["pasta_base"] . "/".$disciplina;
				
				//Monta a pasta
				//ex: ATAS/MEC
				if($reg_arquivos_ref["grava_disciplina"]==1)
				{
					$disciplina = $reg_arquivos_ref["abreviacao"]."/";	
				}
				else
				{
					$disciplina = "";	
				}
			
				$arquivo_ref_id[$reg_arquivos_ref["abreviacao_GED"]."/".$reg_arquivos_ref["os"] . "-" .$reg_arquivos_ref["descricao"]."/".$reg_arquivos_ref["os"].REFERENCIAS.$reg_arquivos_ref["pasta_base"] . "/".$disciplina.$reg_arquivos_ref["arquivo"]] = $reg_arquivos_ref["id_documento_referencia"];
				
				$arquivo_ref_autor[$reg_arquivos_ref["id_documento_referencia"]] = $nome_funcionario[$reg_arquivos_ref["id_autor"]];
				
				$arquivo_ref_editor[$reg_arquivos_ref["id_documento_referencia"]] = $nome_funcionario[$reg_arquivos_ref["id_editor"]];
				
				$arquivo_ref_status[$reg_arquivos_ref["id_documento_referencia"]] = 2;
				
				$arquivo_ref_versao[$reg_arquivos_ref["id_documento_referencia"]] = $reg_arquivos_ref["revisao_documento"];
				
				$arquivo_ref_revisao[$reg_arquivos_ref["id_documento_referencia"]] = $reg_arquivos_ref["versao_documento"];
		
				$arquivo_descricao_numdoc[$reg_arquivos_ref["id_documento_referencia"]] = $reg_arquivos_ref["numero_documento"];
			
				$arquivo_descricao_ref[$reg_arquivos_ref["id_documento_referencia"]] = $reg_arquivos_ref["numero_registro"];
			}		
		}					
		
		$header = "<table id=\"tbl1\" width=\"100%\" border=\"1\">";
		$header .= "<tr>";
		$header .= "<td width=\"20\" type=\"ro\"> </td>";
		$header .= "<td width=\"20\" type=\"ro\"> </td>";
		$header .= "<td width=\"20\" type=\"ro\"> </td>";	
		$header .= "<td width=\"150\" type=\"ro\">Nºmero INT</td>"; //300
		$header .= "<td width=\"180\" type=\"ro\">Nºmero Cliente</td>";
		$header .= "<td width=\"80\" type=\"ro\">Tamanho</td>";
		$header .= "<td width=\"130\" type=\"ro\">data</td>";
		$header .= "<td width=\"65\" type=\"ro\">Autor</td>";	
		$header .= "<td width=\"65\" type=\"ro\">Editor</td>";
		$header .= "<td width=\"20\" type=\"ro\">RD</td>";
		$header .= "<td width=\"20\" type=\"ro\">V</td>";
		$header .= "<td width=\"20\" type=\"ro\">RC</td>";
		$header .= "</tr>";
		
		$footer = "</table>";		
			
		if(count($arquivo_id)>0)
		{
			$conteudo .= "<tr>";
			$conteudo .= "<td colspan=\"10\" align=\"CENTER\"><STRONG>DISCIPLINAS</STRONG></td>";
			$conteudo .= "</tr>";	
		}	
		
		//implementado em 16/08/2013
		foreach($arquivo_id as $arquivo=>$id_ged_arquivo)
		{
			//se for um arquivo
			if(is_file(DOCUMENTOS_GED.$arquivo) && file_exists(DOCUMENTOS_GED.$arquivo))
			{
				//Explode o nome do arquivo
				$extensao_array = explode(".",basename($arquivo));
				
				//Pega somente a extensão
				$extensao = $extensao_array[count($extensao_array)-1];					
				
				//Pega a imagem referente a extensão
				$imagem = retornaImagem($extensao);						
				
				//Pega a imagem da bolinha referente ao status do arquivo
				switch ($arquivo_status[$id_ged_arquivo])
				{
					//arquivo liberado
					case 0:
						$imagem_bolinha = retornaImagem(0);
					break;
					
					//arquivo em edição (check-in) - bolinha vermelha
					case 1:
						$imagem_bolinha = retornaImagem(1);
					break;
					
					//arquivo em emissão (emitido ao cliente)
					case 2:
						if($arquivo_situacao[$id_ged_arquivo]==0) //local
						{
							$imagem_bolinha = retornaImagem(2);
						}
						else
						{
							$imagem_bolinha = retornaImagem(3);
						}
					break;
					
				}						
								
				//Pega o tamanho
				$tamanho = formataTamanho(filesize(DOCUMENTOS_GED.$arquivo));						
				
				//Pega a data de modificação
				$data_modificacao = date("d/m/Y H:i:s",filemtime(DOCUMENTOS_GED.$arquivo)); 
				
				//Pega o autor
				$autor = $arquivo_autor[$id_ged_arquivo];
				
				//Pega o editor
				$editor = $arquivo_editor[$id_ged_arquivo];
				
				//Pega a versão atual
				//$revisao_documento = $arquivo_versao[$id_ged_arquivo] . "." . $arquivo_revisao[$id_ged_arquivo];
				
				//$versao_cliente = $arquivo_versao[$id_ged_arquivo];
				
				//adicionado em 11/07/2011
				$arquivo_numcli = $arquivo_numcliente[$id_ged_arquivo];
										
				//Se o status atual do arquivo for 0 (desbloqueado)
				if($arquivo_status[$id_ged_arquivo]==0)
				{
					
					$operacao = $extensao=="zip" ? "3" : "1"; //3= Check-in sendo ZIP; 1 = Check-in não sendo ZIP
					//$operacao = "Check In";
					//Atribue o evento para rclick no div
					//$rclick = "popupMenu('" . $operacao . "',event); ";
					
				}
				//Se for 1
				elseif($arquivo_status[$id_ged_arquivo]==1)
				{
					
					$operacao = $extensao=="zip" ? "4" : "2"; //4= Check-out sendo ZIP; 2 = Check-out não sendo ZIP
					//$operacao = "Check Out";
					//Atribue o evento para rclick no div
					//$rclick = "popupMenu('" . $operacao . "',event); ";
					
				}
				else
				{
					//acrescentado em 26/06/2012
					//Carlos Abreu - erro na 4864
					
					if($id_ref[1]!='')
					{	
						$operacao = "7";
					}
					else
					{
						$operacao = "5";
					}
				}
				
				//finalidade CERTIFICADO e DEVOLUÇÃO APROVADO
				if($arquivo_fin_emissao[$id_ged_arquivo]==3 && $arquivo_status_dev[$id_ged_arquivo]=='A')
				{
					if($arquivo_status[$id_ged_arquivo]==2)
					{
						$imagem_bolinha = retornaImagem(4);
						$operacao = "5";
					}
	
				}

				if(($arquivo_status[$id_ged_arquivo]==2) || ($arquivo_fin_emissao[$id_ged_arquivo]==3 && $arquivo_status_dev[$id_ged_arquivo]=='A'))
				{
					$operacao = "9";
				}

				//Atribue o evento para onclick no div
				$onclick = "xajax_dadosArquivo('".$id_ged_arquivo."'); ";
			
				//Atribue o evento para rclick no div
				$rclick = "popupMenu('" . $operacao . "',event,'".$id_ged_arquivo."','".DOCUMENTOS_GED.$arquivo."'); ";
			
				//Atribue o evento para double click no div
				$dblclick = "xajax_abrir('" . DOCUMENTOS_GED.$arquivo . "'); ";			
								
				//Preenche o checkbox, se o arquivo estiver nos cookies
				//if((array_search($id_ged_arquivo,$array_arquivos[1])!==false)||(array_search($id_ged_arquivo,$array_arquivos[2])!==false)||(array_search($id_ged_arquivo,$array_arquivos[3])!==false))
				if((in_array($id_ged_arquivo,$array_arquivos[1]))||(in_array($id_ged_arquivo,$array_arquivos[2]))||(in_array($id_ged_arquivo,$array_arquivos[3])))
				{
					$chk_checked = "checked";				
				}
				else
				{
					$chk_checked = "";
				}
				
				//Desabilita o checkbox, se o arquivo for parte de um pacote enviado ao ArqTec ou finalidade CE e devolucao APROVADO e status arquivo for maior que 0
				if($arquivo_status[$id_ged_arquivo]>0 || ($arquivo_fin_emissao[$id_ged_arquivo]==3 && $arquivo_status_dev[$id_ged_arquivo]=='A'))
				{
					$chk_disabled = "disabled";
					
					//se for autor e não posuir editor ou for o editor da versão atual e esta em edição(bloqueado check-in)
					if((($arquivo_autor[$id_ged_arquivo]==$nome_funcionario[$_SESSION["id_funcionario"]] && $arquivo_editor[$id_ged_arquivo]) || ($nome_funcionario[$_SESSION["id_funcionario"]]==$arquivo_editor[$id_ged_arquivo])) && $arquivo_status[$id_ged_arquivo]==1)
					{
						$chk_disabled = "";
					}
											
				}
				else
				{
					$chk_disabled = "";
					
				}
				
				$conteudo .= "<tr>";						
				$conteudo .= "<td align=\"left\"><input type=\"checkbox\" value=\"1\" name=\"chk_" . $id_ged_arquivo . "\" id=\"chk_" . $id_ged_arquivo . "\" onclick=\"data=new Date(); xajax_selecaoCheckbox(this.name, this.checked,data.getHours()+':'+data.getMinutes());\" " . $chk_checked . " " . $chk_disabled . "></td>";
				$conteudo .= "<td align=\"center\">".$imagem_bolinha."</td>";
				$conteudo .= "<td align=\"center\">" . $imagem . "</td>";
				$conteudo .= "<td align=\"left\"><div name=\"a_itens_".$id_ged_arquivo."\" id=\"a_itens_".$id_ged_arquivo."\" class=\"cell1\" style=\"width:17%; float:left; cursor:pointer; \" onclick=\"" . $onclick . " \" oncontextmenu=\"" . $onclick . $rclick . " return false;\" ondblclick=\"" . $dblclick . "\">" .$arquivo_descricao[$id_ged_arquivo] . "</div></td>";
				$conteudo .= "<td align=\"left\">" . $arquivo_numcli . "</td>";
				$conteudo .= "<td align=\"left\">". $tamanho ."</td>";
				$conteudo .= "<td align=\"left\">". $data_modificacao ."</td>";
				$conteudo .= "<td align=\"left\">". $autor ."</td>";
				$conteudo .= "<td align=\"left\">". $editor ."</td>";
				//$conteudo .= "<td align=\"left\">". $revisao_documento ."</td>";
				$conteudo .= "<td align=\"left\">". $arquivo_revisao_dvm[$id_ged_arquivo] ."</td>";
				$conteudo .= "<td align=\"left\">". $arquivo_versao[$id_ged_arquivo] ."</td>";
				$conteudo .= "<td align=\"left\">". $arquivo_revisao_cli[$id_ged_arquivo] ."</td>";
				$conteudo .= "</tr>";
	
			}
			else
			{
				if(file_exists(DOCUMENTOS_GED.$arquivo) && $_SESSION["id_funcionario"]==666666)
				{
					$conteudo .= "<tr>";
					$conteudo .= "<td colspan=\"10\" align=\"left\">ERRO:".$arquivo."</td>";
					$conteudo .= "</tr>";
				}
			}
			
		}
		
		if(count($arquivo_ref_id)>0)
		{
			$conteudo .= "<tr>";
			$conteudo .= "<td colspan=\"10\" align=\"CENTER\"><STRONG>REFERÊNCIAS</STRONG></td>";
			$conteudo .= "</tr>";	
		}
				
		foreach($arquivo_ref_id as $arquivo=>$id_documento_referencia)
		{
			//se for um arquivo
			if(is_file(DOCUMENTOS_GED.$arquivo) && file_exists(DOCUMENTOS_GED.$arquivo))
			{
				$imagem_bolinha = "";
				
				//Explode o nome do arquivo
				$extensao_array = explode(".",basename($arquivo));
				
				//Pega somente a extensão
				$extensao = $extensao_array[count($extensao_array)-1];					
				
				//Pega a imagem referente a extensão
				$imagem = retornaImagem($extensao);					
						
				//Pega o tamanho
				$tamanho = formataTamanho(filesize(DOCUMENTOS_GED.$arquivo));						
				
				//Pega a data de modificação
				$data_modificacao = date("d/m/Y H:i:s",filemtime(DOCUMENTOS_GED.$arquivo)); 
	
				$autor = $arquivo_ref_autor[$id_documento_referencia];
				
				$editor = $arquivo_ref_editor[$id_documento_referencia];
				
				//$arquivo = $arquivo_descricao_ref[$id_documento_referencia];							
				//Pega a versão atual
				//$revisao_documento = $arquivo_ref_revisao[$id_documento_referencia] . "." . $arquivo_ref_versao[$id_documento_referencia];
	
				$arquivo_numcli = $arquivo_descricao_numdoc[$id_documento_referencia];
		
				$operacao = 7;	//abrir/propriedades referencias
		
				$onclick = "xajax_dadosArquivo('".DOCUMENTOS_GED.$arquivo."'); ";
				
				//Atribue o evento para rclick no div
				$rclick = "popupMenu('" . $operacao . "',event,'".$id_documento_referencia."','".DOCUMENTOS_GED.$arquivo."'); ";
			
				//Atribue o evento para double click no div
				$dblclick = "xajax_abrir('" . DOCUMENTOS_GED.$arquivo . "'); ";				
	
				$chk_disabled = "disabled";						
				
				$conteudo .= "<tr>";					
				//$conteudo .= "<td align=\"left\"><input type=\"checkbox\" value=\"1\" name=\"chk_ref_" . $id_documento_referencia . "\" onclick=\"data=new Date(); xajax_selecaoCheckbox(this.name, this.checked,data.getHours()+':'+data.getMinutes());\" " . $chk_checked . " " . $chk_disabled . "></td>";	
				$conteudo .= "<td align=\"left\" </td>";
				$conteudo .= "<td align=\"center\">".$imagem_bolinha."</td>";
				$conteudo .= "<td align=\"center\">" . $imagem . "</td>";
				$conteudo .= "<td align=\"left\"><div name=\"ref_itens_".$id_documento_referencia."\" id=\"ref_itens_".$id_documento_referencia."\" class=\"cell1\" style=\"width:17%; float:left; cursor:pointer; \" onclick=\"" . $onclick . " \" oncontextmenu=\"" . $onclick . $rclick . " return false;\" ondblclick=\"" . $dblclick . "\">" . $arquivo_descricao_ref[$id_documento_referencia] . "</div></td>";
				$conteudo .= "<td align=\"left\">" . $arquivo_numcli . "</td>";
				$conteudo .= "<td align=\"left\">". $tamanho ."</td>";
				$conteudo .= "<td align=\"left\">". $data_modificacao ."</td>";
				$conteudo .= "<td align=\"left\">". $autor ."</td>";
				$conteudo .= "<td align=\"left\">". $editor ."</td>";
				//$conteudo .= "<td align=\"left\">". $revisao_documento ."</td>";
				$conteudo .= "<td align=\"left\">". $arquivo_ref_revisao[$id_documento_referencia] ."</td>";
				$conteudo .= "<td align=\"left\">". $arquivo_ref_versao[$id_documento_referencia] ."</td>";
				$conteudo .= "<td align=\"left\"> </td>";
				$conteudo .= "</tr>";
	
			}
			else
			{
				if(file_exists(DOCUMENTOS_GED.$arquivo))
				{
					$conteudo .= "<tr>";
					$conteudo .= "<td colspan=\"10\" align=\"left\">".$arquivo."</td>";
					$conteudo .= "</tr>";
				}
			}	
		}
		
		$resposta->addAssign("div_arquivos","innerHTML", $header.$conteudo.$footer);
	
		$resposta->addScript("grid('tbl1',true,'400');");
		
		$resposta->addScript("document.body.onclick = function () { popupMenu('',event,'','".$str_caminho."'); } ");
		
	}
	else
	{
		$resposta->addAlert("Deve selecionar a OS.");
	}

	//Destrói a mensagem de carregamento
	$resposta->addScript("divPopupInst.destroi();");
	
	return $resposta;
}

//Mostra os dados do arquivo selecionado
function dadosArquivo($id_ged_arquivo)
{
	$resposta = new xajaxResponse();

	$db = new banco_dados();	

	$tipo_doc = explode("_",$id_ged_arquivo);
	
	//documentos normais
	if($tipo_doc[0]!='REF')
	{
	
		$sql_emissoes = "SELECT * FROM ".DATABASE.".ged_arquivos, ".DATABASE.".ged_versoes, ".DATABASE.".ged_pacotes, ".DATABASE.".grd, ".DATABASE.".OS, ".DATABASE.".grd_versoes ";
		$sql_emissoes .= "WHERE ged_arquivos.id_ged_arquivo = ged_versoes.id_ged_arquivo ";
		$sql_emissoes .= "AND ged_versoes.id_ged_versao = grd_versoes.id_ged_versao ";
		$sql_emissoes .= "AND grd_versoes.id_grd = grd.id_grd ";
		$sql_emissoes .= "AND ged_pacotes.id_os = OS.id_os ";
		$sql_emissoes .= "AND ged_pacotes.id_ged_pacote = ged_versoes.id_ged_pacote ";
		$sql_emissoes .= "AND ged_pacotes.id_ged_pacote = grd.id_ged_pacote ";
		$sql_emissoes .= "AND ged_arquivos.id_ged_arquivo = '" . $id_ged_arquivo . "' ";
		$sql_emissoes .= "ORDER BY grd.data_emissao ASC ";
		
		//$cont_emissoes = mysql_query($sql_emissoes,$db->conexao) or $resposta->addAlert("Erro ao tentar selecionar os dados: " . mysql_error($db->conexao).$sql_emissoes);
		$cont_emissoes = $db->select($sql_emissoes,'MYSQL');
		
		$id_emissao = $db->numero_registros;
		
		if($db->erro!='')
		{
			$resposta->addAlert('Erro ao selecionar os dados. '.$sql_emissoes);
			
			return $resposta;
		}
	
		$array_emiss = NULL;
	
		while($reg_emissoes = mysqli_fetch_assoc($cont_emissoes))
		{
			$array_emiss[] = array($reg_emissoes["os"] . "-" . sprintf("%03d",$reg_emissoes["numero_pacote"]),$reg_emissoes["data_emissao"],$reg_emissoes["revisao_documento"] . "." . $reg_emissoes["versao_documento"],$reg_emissoes["id_fin_emissao"]);
		}
		
		$sql_checkin = "SELECT * FROM ".DATABASE.".numeros_interno, ".DATABASE.".solicitacao_documentos_detalhes, ".DATABASE.".ged_arquivos, ".DATABASE.".ged_versoes ";
		$sql_checkin .= "WHERE ged_arquivos.id_ged_arquivo = ged_versoes.id_ged_arquivo ";
		$sql_checkin .= "AND ged_arquivos.id_ged_arquivo = '".$id_ged_arquivo."' ";
		$sql_checkin .= "AND numeros_interno.id_numero_interno = ged_arquivos.id_numero_interno ";
		$sql_checkin .= "AND numeros_interno.id_numero_interno = solicitacao_documentos_detalhes.id_numero_interno ";
		$sql_checkin .= "ORDER BY ged_versoes.id_ged_versao DESC LIMIT 1";
		
		$cont_checkin = $db->select($sql_checkin,'MYSQL');
		
		if($db->erro!='')
		{
			$resposta->addAlert('Erro ao selecionar os dados. '.$sql_checkin);
			
			return $resposta;
		}
		
		$reg_checkin = mysqli_fetch_assoc($cont_checkin);
		
		$sql_codemiss = "SELECT * FROM ".DATABASE.".codigos_emissao ";
		
		$cont_codemiss = $db->select($sql_codemiss,'MYSQL');
		
		if($db->erro!='')
		{
			$resposta->addAlert('Erro ao selecionar os dados. '.$sql_codemiss);
			
			return $resposta;
		}
	
		while($reg_codemiss = mysqli_fetch_assoc($cont_codemiss))
		{
			$array_codemiss[$reg_codemiss["id_codigo_emissao"]] = $reg_codemiss["codigos_emissao"];
			$array_descemiss[$reg_codemiss["id_codigo_emissao"]] = $reg_codemiss["emissao"];
		}
		
		$disciplina = explode("-",$reg_checkin["disciplina"]);
		
		$sql = "SELECT * FROM ".DATABASE.".setores ";
		$sql .= "WHERE setores.abreviacao = '".$disciplina[1]."' ";
		
		$cont = $db->select($sql,'MYSQL');
		
		if($db->erro!='')
		{
			$resposta->addAlert('Erro ao selecionar os dados. '.$sql);
			
			return $resposta;
		}
	
		$reg_disciplina = mysqli_fetch_assoc($cont);
	
		$conteudo_info = "";
	
		$conteudo_info .= "<div id='tit_info' style='background-color:#EDEDED; width:100%;height:20px;text-align:right;'><img src='../images/silk/application_side_list.gif' style='margin:2px; cursor:pointer' title='Fechar' onclick='dv_info(0);'></div>";
		$conteudo_info .= "<table border='0' class='fonte_descricao_campos' style='margin:10px; font-size:10px' width='90%' cellpadding='2'>";
		$conteudo_info .= "<tr><td colspan='2' style='font-size:20px;' align='center'>Informações do Documento</td></tr>";
		$conteudo_info .= "<tr><td colspan='2'> </td></tr>";
		$conteudo_info .= "<tr><td valign='top' style='width:120px;'>Arquivo: </td><td>" . $reg_checkin["nome_arquivo"] . "</td></tr>";
		$conteudo_info .= "<tr><td valign='top' style='width:120px;'>tipo de documento:</td><td>" . $reg_checkin["atividade"] . "</td></tr>";
		$conteudo_info .= "<tr><td valign='top' style='width:120px;'>Disciplina: </td><td>" . $reg_disciplina["setor"] . "</td></tr>";
		
		$conteudo_info .= "<tr><td valign='top' style='width:120px;'>Título 1:</td><td>" . $reg_checkin["tag"] . "</td></tr>";
		$conteudo_info .= "<tr><td valign='top' style='width:120px;'>Título 2:</td><td>" . $reg_checkin["tag2"] . "</td></tr>";
		$conteudo_info .= "<tr><td valign='top' style='width:120px;'>Título 3:</td><td>" . $reg_checkin["tag3"] . "</td></tr>";
		$conteudo_info .= "<tr><td valign='top' style='width:120px;'>Título 4:</td><td>" . $reg_checkin["tag4"] . "</td></tr>";
		
		if($id_emissao>0)
		{	
			$conteudo_info .= "<tr><td valign='top' style='width:120px;'>Emissões:</td><td>";			
			$conteudo_info .= "<table width='100%' class='fonte_descricao_campos' style='border: 1px #999999 solid; font-family: Arial; font-size:10px;' cellspacing='0' cellpadding='2'>";
			$conteudo_info .= "<tr style='background: #EFEFEF solid; '><th># GRD</th><th>Dt. Emiss.</th><th>R/V</th><th>Fin</th></tr>";
			
			foreach($array_emiss as $valor)
			{
				$conteudo_info .= "<tr><td align=\"center\">" . $valor[0] . "</td><td align=\"center\">" . mysql_php($valor[1]) . "</td><td align=\"center\">" . $valor[2] . "</td><td align=\"center\" title='" . $array_descemiss[$valor[3]] . "'>" . $array_codemiss[$valor[3]] . "</td></tr>";
			}
			
			$conteudo_info .= "</table>";			
			$conteudo_info .= "</td></tr>";
		}
		
		$conteudo_info .= "</table>";
	
		$resposta->addAssign("div_info","innerHTML",$conteudo_info);
		
		$resposta->addScript("dv_info('1');");	
	}
	else
	{
		//Seleciona os dados dos arquivos de referência
		$sql = "SELECT * FROM ".DATABASE.".documentos_referencia, ".DATABASE.".documentos_referencia_revisoes ";
		$sql .= "WHERE documentos_referencia.id_documento_referencia = '".$tipo_doc[1]."' ";
		$sql .= "AND documentos_referencia.id_documento_referencia_revisoes = documentos_referencia_revisoes.id_documentos_referencia_revisoes ";
		
		$cont_arquivos_ref = $db->select($sql,'MYSQL');
	
		$reg_arquivos_ref = mysqli_fetch_assoc($cont_arquivos_ref);
					
	}

	return $resposta;
}

//Preenche a janela de Propriedades
function preenchePropriedades($id_ged_arquivo)
{
	$resposta = new xajaxResponse();

	//Instancia o objeto do bd
	$db = new banco_dados();

	//seleciona o autor
	$sql = "SELECT id_funcionario,nome_usuario FROM ".DATABASE.".funcionarios ";
	$sql .= "ORDER BY funcionarios.funcionario ";
	
	$cont = $db->select($sql,'MYSQL');

	while($regs = mysqli_fetch_assoc($cont))
	{
		$nome_funcionario[$regs["id_funcionario"]] = $regs["nome_usuario"];
	}

	//Seleciona os dados do arquivo
	$sql_arquivo = "SELECT ged_arquivos.id_ged_arquivo, ged_arquivos.id_ged_versao, ged_arquivos.id_autor, ged_arquivos.id_editor, ged_versoes.arquivo, ged_versoes.base, ged_versoes.os, ged_versoes.disciplina, ged_versoes.atividade, ged_versoes.strarquivo, ged_versoes.sequencial, ged_versoes.nome_arquivo, ged_arquivos.status, ged_versoes.id_ged_pacote, numero_cliente.numero_cliente, numero_cliente.id_numcliente FROM ".DATABASE.".ged_arquivos, ".DATABASE.".ged_versoes, ".DATABASE.".numeros_interno, ".DATABASE.".numero_cliente ";
	$sql_arquivo .= "WHERE ged_arquivos.id_ged_versao = ged_versoes.id_ged_versao ";
	$sql_arquivo .= "AND ged_arquivos.id_numero_interno = numeros_interno.id_numero_interno ";
	$sql_arquivo .= "AND numeros_interno.id_numcliente = numero_cliente.id_numcliente ";
	$sql_arquivo .= "AND ged_arquivos.id_ged_arquivo = '".$id_ged_arquivo."' ";
	
	$cont_arquivo = $db->select($sql_arquivo,'MYSQL');

	$reg_arquivo = mysqli_fetch_assoc($cont_arquivo);
	
	$caminho = DOCUMENTOS_GED.$reg_arquivo["base"]."/".$reg_arquivo["os"]."/".substr($reg_arquivo["os"],0,4).DISCIPLINAS.$reg_arquivo["disciplina"]."/".$reg_arquivo["atividade"]."/" .$reg_arquivo["sequencial"]."/".$reg_arquivo["nome_arquivo"];	

	//Verifica se existem GRD's para esse documento
	$sql_grd = "SELECT * FROM ".DATABASE.".ged_pacotes, ".DATABASE.".grd, ".DATABASE.".grd_versoes, ".DATABASE.".ged_versoes ";
	$sql_grd .= "WHERE ged_pacotes.id_ged_pacote = grd.id_ged_pacote ";
	$sql_grd .= "AND ged_pacotes.id_ged_pacote = ged_versoes.id_ged_pacote ";
	$sql_grd .= "AND grd_versoes.id_ged_versao = ged_versoes.id_ged_versao ";
	$sql_grd .= "AND grd_versoes.id_grd = grd.id_grd ";
	$sql_grd .= "AND ged_versoes.id_ged_arquivo = '" . $id_ged_arquivo. "' ";
	
	$cont_grd = $db->select($sql_grd,'MYSQL');
	
	//Pega o nome do arquivo, sem o  
	$arquivo = $reg_arquivo["nome_arquivo"];
	
	//Explode o nome do arquivo em um array
	$array_extensao = explode(".",$arquivo);
	
	//Pega a extensão do arquivo
	$extensao = $array_extensao[count($array_extensao)-1];
		
	//Pega a imagem relativa a extensão
	$imagem = retornaImagem($extensao);

	//Pega o tamanho do arquivo
	$tamanho = formataTamanho(filesize($caminho));
		
	//Pega o autor
	$autor = $nome_funcionario[$reg_arquivo["id_autor"]];
	
	//Pega a data de criação do arquivo (SERÁ ALTERADO - ARMAZENADO NO BANCO (?) )
	$data_criacao  = date("d/m/Y H:i:s", filectime($caminho));
	
	//Pega a data de modificação do arquivo (SERÁ ALTERADO - ARMAZENADO NO BANCO (?))
	$data_modificacao = date("d/m/Y H:i:s", filemtime($caminho));
	
	//Forma o conteúdo da janela de Propriedades
	$conteudo = "<form method=\"POST\" name=\"frm_propriedades\">";
	$conteudo .= "<div id=\"conteudo\" class=\"fonte_11\" style=\"font-size:12px; width:100%; margin:10px;\">";
		$conteudo .= "<div id=\"tipo_arquivo\" style=\"padding:10px;\" onselectstart=\"return false\" unselectable=\"on\">" . $imagem . " tipo de arquivo: " . $extensao . "</div>";
		$conteudo .= "<div id=\"local\" style=\"padding:10px; border-top-style:groove; border-width:2px;\">Caminho: <div style=\"overflow:auto; width:95%; height:30px;border-style:inset; border-left-color:#999999; border-top-color: #999999; border-width:2px;\" onselectstart=\"return false\" unselectable=\"on\">" . $reg_arquivo["nome_arquivo"] . "</div></div>";
		$conteudo .= "<div id=\"tamanho\" style=\"padding:10px;\" onselectstart=\"return false\" unselectable=\"on\">Tamanho: " . $tamanho . "</div>";
		$conteudo .= "<div id=\"autor\" style=\"padding:10px;\" onselectstart=\"return false\" unselectable=\"on\">Autor: " . $autor . "</div>";		
		$conteudo .= "<div id=\"data_modificacao\" style=\"padding:10px;\" onselectstart=\"return false\" unselectable=\"on\">Última Atualização: " . $data_modificacao . "</div>";
		
		if(mysql_num_rows($cont_grd)=="0")
		{
			$conteudo .= "<div id=\"autor\" style=\"padding:10px;\">Numcliente: <input type=\"text\" name=\"txt_numcliente\" id=\"txt_numcliente\" class=\"caixa\" value=\"" . $reg_arquivo["numero_cliente"] . "\" size=\"40\"></div>";
		}		

		//Cria um array com as versões que possuem comentários
		$sql_coment = "SELECT id_ged_versao FROM ".DATABASE.".ged_comentarios ";

		$cont_coment = $db->select($sql_coment,'MYSQL');

		while($reg_coment = mysqli_fetch_assoc($cont_coment))
		{
			$array_comentarios[] = $reg_coment["id_ged_versao"];
		}
		
		//Forma a tabela
		$header = "<table id=\"tbl2\" class=\"dhtmlXGrid\" style=\"width:100%;\" onselectstart=\"return false;\" unselectable=\"on\">";
		$header .= "<tr>";
		$header .= "<td width=\"290\" type=\"ro\">Nome do arquivo</td>";
		$header .= "<td width=\"40\" type=\"ro\">Rev D</td>";
		$header .= "<td width=\"40\" type=\"ro\">Ver</td>";
		$header .= "<td width=\"40\" type=\"ro\">Rev C</td>";
		$header .= "<td width=\"25\" type=\"ro\">A</td>";
		$header .= "<td width=\"25\" type=\"ro\">C</td>";
		$header .= "<td width=\"25\" type=\"ro\">R</td>";
		$header .= "<td width=\"25\" type=\"ro\">E</td>";
		$header .= "</tr>";
		
		$footer = "</table>";
		
		$sql_versoes = "SELECT * FROM ".DATABASE.".ged_versoes ";
		$sql_versoes .= "WHERE ged_versoes.id_ged_arquivo = '" . $id_ged_arquivo . "' ";
		$sql_versoes .= "ORDER BY ged_versoes.versao_documento DESC, ged_versoes.revisao_documento DESC ";

		$cont_versoes = $db->select($sql_versoes,'MYSQL');

		//Forma o conteúdo das versões
		while($reg_versoes = mysqli_fetch_assoc($cont_versoes))
		{
			$caminho = DOCUMENTOS_GED . $reg_versoes["base"] . "/" . $reg_versoes["os"] . "/" . substr($reg_versoes["os"],0,4) . DISCIPLINAS . $reg_versoes["disciplina"] . "/" . $reg_versoes["atividade"] . "/" . $reg_versoes["sequencial"];
		
			//$extensao_array = explode(".",basename($reg_versoes["arquivo"]));
			//$extensao_array = explode(".",basename($str_caminho_arq));
			
			//Explode o nome do arquivo em um array
			$array_extensao = explode(".",$reg_versoes["nome_arquivo"]);
			
			//Pega a extensão do arquivo
			$extensao = $array_extensao[count($array_extensao)-1];
			
			//$array_qtde_espacos = explode(" ",basename($reg_versoes["arquivo"]));
			$array_qtde_espacos = explode(" ",$reg_versoes["nome_arquivo"]);
			
			//if(strlen(basename($reg_versoes["arquivo"]))>40 && count($array_qtde_espacos)==1)
			if(strlen($reg_versoes["nome_arquivo"])>40 && count($array_qtde_espacos)==1)
			{
				$nome_arquivo = substr($reg_versoes["nome_arquivo"],0,40) . "...";
				
				$title_arquivo = $reg_versoes["nome_arquivo"]; 
			}
			//elseif(strlen(basename($reg_versoes["arquivo"]))>30)
			elseif(strlen($reg_versoes["nome_arquivo"])>30)
			{
				$nome_arquivo = substr($reg_versoes["nome_arquivo"],0,30) . "...";
				
				$title_arquivo = $reg_versoes["nome_arquivo"];				 
			}
			else
			{
				$nome_arquivo = $reg_versoes["nome_arquivo"];
				
				$title_arquivo = "";
			}
			
			$str_1 = "";
			
			$editavel = "readonly";

			if(in_array($_SESSION["id_funcionario"],lista_arqtec()))
			{
				$editavel = "";
			}				
			
			$conteudo_tbl .= "<tr>";
			$conteudo_tbl .= "<td style=\"cursor:pointer;\" align=\"left\" title=\"" . $nome_arquivo . "\">".$nome_arquivo."</td>";
			$conteudo_tbl .= "<td style=\"cursor:pointer;\" align=\"left\"><input name=\"revisao_dvm_" . $reg_versoes["id_ged_versao"] . "\" id=\"revisao_dvm_" . $reg_versoes["id_ged_versao"] . "\" style=\"width:100%; font-size:11px; height:17px; border-color: #B2B2B2;  text-align:center;\" class=\"caixa\" type=\"text\" value=\"" . $reg_versoes["revisao_interna"] . "\" ".$editavel."></td>";
			$conteudo_tbl .= "<td style=\"cursor:pointer;\" align=\"left\"><input name=\"versao_" . $reg_versoes["id_ged_versao"] . "\" id=\"versao_" . $reg_versoes["id_ged_versao"] . "\" style=\"width:100%; font-size:11px; height:17px; border-color: #B2B2B2;  text-align:center;\" class=\"caixa\" type=\"text\" value=\"" . $reg_versoes["versao_"] . "\" ></td>";
			$conteudo_tbl .= "<td style=\"cursor:pointer;\" align=\"left\"><input name=\"revisao_cli_" . $reg_versoes["id_ged_versao"] . "\" id=\"revisao_cli_" . $reg_versoes["id_ged_versao"] . "\" style=\"width:100%; font-size:11px; height:17px; border-color: #B2B2B2;  text-align:center;\" class=\"caixa\" type=\"text\" value=\"" . $reg_versoes["revisao_cliente"] . "\" ".$editavel."></td>";
			
			$conteudo_coment = "";

			//Se existirem comentários para essa versão
			if(in_array($reg_versoes["id_ged_versao"],$array_comentarios))
			{
				$conteudo_coment = "<td style=\"cursor:pointer;\" align=\"left\" onclick=\"popupComentarios(" . $reg_versoes["id_ged_arquivo"] . ",".$reg_versoes["id_ged_versao"].");\" title=\"Visualizar comentários dessa versão\"><img src=\"../images/silk/comment.gif\"></td>";
			}
			else
			{
				$conteudo_coment = "<td align=\"left\"> </td>";			
			}
			
			//Se a versão não for a atual, mostra o botão de "Reverter" e "Excluir", e o "Abrir" abre a versão
			if($reg_arquivo["id_ged_versao"]!=$reg_versoes["id_ged_versao"])
			{
				$conteudo_tbl .= "<td style=\"cursor:pointer;\" align=\"left\" onclick=\"xajax_abrir('" . $caminho . DIRETORIO_VERSOES ."/". $reg_versoes["nome_arquivo"] . "." . $reg_versoes["id_ged_versao"] . "','" . $caminho."/".$reg_versoes["nome_arquivo"] . "'); \" title=\"Abrir essa versão\"><img src=\"../images/buttons_action/bt_busca.gif\"></td>";
				$conteudo_tbl .= $conteudo_coment;				
				$conteudo_tbl .= "<td style=\"cursor:pointer;\" align=\"left\" onclick=\"if(confirm('Tem certeza que deseja restaurar a versão selecionada e torná-la a atual?')){xajax_restaurar('" . $reg_versoes["id_ged_versao"] . "'); } \" title=\"Restaurar essa versão\"><img src=\"../images/buttons_action/bt_desfazer.gif\"></td>";

				//if(in_array($_SESSION["id_funcionario"],array('6','818','909','910')))
				if(in_array($_SESSION["id_funcionario"],lista_arqtec()))
				{
					//Adicionado 24/02/2010
					$conteudo_tbl .= "<td style=\"cursor:pointer;\" align=\"left\" onclick=\"if(confirm('ATENÇÃO: Tem certeza que deseja EXCLUIR a versão selecionada?')){xajax_excluir_versoes('" . $reg_versoes["id_ged_versao"] . "');}\" title=\"Excluir versão\"><img src=\"../images/buttons_action/apagar.gif\"></td>";
				}
				else
				{
					//Adicionado 24/02/2010
					$conteudo_tbl .= "<td align=\"left\"> </td>";
				}
			}
			else
			{
				//$conteudo_tbl .= "<td style=\"cursor:pointer;\" align=\"left\" onclick=\"xajax_abrir('" . $reg_versoes["arquivo"] . "'); \" title=\"Abrir a versão atual\"><img src=\"../images/buttons_action/bt_busca.gif\"></td>";
				$conteudo_tbl .= "<td style=\"cursor:pointer;\" align=\"left\" onclick=\"xajax_abrir('" . $caminho."/".$reg_versoes["nome_arquivo"] . "'); \" title=\"Abrir a versão atual\"><img src=\"../images/buttons_action/bt_busca.gif\"></td>";
				$conteudo_tbl .= $conteudo_coment;
				$conteudo_tbl .= "<td style=\"cursor:pointer;\" align=\"left\"> </td>";
				$conteudo_tbl .= "<td style=\"cursor:pointer;\" align=\"left\"> </td>";
			}
			
			$conteudo_tbl .= "</tr>";				
			
		}
		
		$conteudo .= $header.$conteudo_tbl.$footer;
		
		$conteudo .= "</div>";

		//Se o arquivo estiver bloqueado
		if($reg_arquivo["status"]=="2")
		{
			$btn_gravar = "disabled";
		}

		//$conteudo .= "<div id=\"botoes\" style=\"text-align:right; width:90%; margin-left:10px; \"><input type=\"hidden\" id=\"id_ged_arquivo\" name=\"id_ged_arquivo\" value=\"" . $reg_arquivo["id_ged_arquivo"] . "\"><input type=\"button\" value=\"Gravar alterações\" class=\"fonte_botao\" onclick=\"if(confirm('Confirma as alterações feitas nas versões?')){xajax_atualizaVersoes(xajax.getFormValues('frm_propriedades'));}\" " . $btn_gravar . "><input type=\"button\" value=\"Voltar\" class=\"fonte_botao\" onclick=\"divPopupInst.destroi();xajax_buscaArquivosInicial(xajax.getFormValues('frm_ged'));\"></div>";	
		$conteudo .= "<div id=\"botoes\" style=\"text-align:right; width:90%; margin-left:10px; \"><input type=\"hidden\" id=\"id_ged_arquivo\" name=\"id_ged_arquivo\" value=\"" . $reg_arquivo["id_ged_arquivo"] . "\"><input type=\"button\" value=\"Gravar alterações\" class=\"fonte_botao\" onclick=\"if(confirm('Confirma as alterações feitas nas versões?')){xajax_atualizaVersoes(xajax.getFormValues('frm_propriedades'));}\" " . $btn_gravar . "><input type=\"button\" value=\"Voltar\" class=\"fonte_botao\" onclick=\"xajax_preencheArquivos(xajax.getFormValues('frm_ged'));\"></div>";
	$conteudo .= "</div>";
	
	$conteudo .= "</form>";
	
	//Atribue o conteúdo
	$resposta->addAssign("div_conteudo","innerHTML",$conteudo);
	
	$resposta->addScript("grid('tbl2',true,'110');");
	
	return $resposta;
}

//Preenche a janela de Propriedades - referencias
function preenchePropriedadesRef($id_documento_referencia)
{
	$resposta = new xajaxResponse();

	//Instancia o objeto do bd
	$db = new banco_dados();
	
	//seleciona o autor
	$sql = "SELECT id_funcionario,nome_usuario FROM ".DATABASE.".funcionarios ";
	$sql .= "ORDER BY funcionarios.funcionario ";
	
	$cont = $db->select($sql,'MYSQL');

	while($regs = mysqli_fetch_assoc($cont))
	{
		$nome_funcionario[$regs["id_funcionario"]] = $regs["nome_usuario"];
	}
	
	$sql = "SELECT *,OS.descricao FROM ".DATABASE.".empresas, ".DATABASE.".OS, ".DATABASE.".setores, ".DATABASE.".documentos_referencia_revisoes, ".DATABASE.".tipos_referencia, ".DATABASE.".tipos_documentos_referencia, ".DATABASE.".documentos_referencia ";
	$sql .= "LEFT JOIN ".DATABASE.".formatos ON (documentos_referencia.id_formato = formatos.id_formato) ";	
	$sql .= "WHERE documentos_referencia.id_os = OS.id_os ";
	$sql .= "AND documentos_referencia.id_tipo_documento_referencia = tipos_documentos_referencia.id_tipos_documentos_referencia ";
	$sql .= "AND documentos_referencia.id_disciplina = setores.id_setor ";
	$sql .= "AND documentos_referencia.id_documento_referencia_revisoes = documentos_referencia_revisoes.id_documentos_referencia_revisoes ";
	$sql .= "AND tipos_documentos_referencia.id_tipo_referencia = tipos_referencia.id_tipo_referencia ";
	$sql .= "AND OS.id_empresa = empresas.id_empresa ";
	$sql .= "AND documentos_referencia.id_documento_referencia = '" . $id_documento_referencia . "' ";	
	
	$cont_arquivo = $db->select($sql,'MYSQL');

	$reg_arquivo = mysqli_fetch_assoc($cont_arquivo);
	
	$os = sprintf("%05d",$reg_arquivo["os"]);
	
	//Monta a pasta
	//ex: ATAS/MEC
	if($reg_arquivo["grava_disciplina"]==1)
	{
		$disciplina = $reg_arquivo["abreviacao"]."/";	
	}
	else
	{
		$disciplina = "";	
	}
	
	//monta diretorio base
	$diretorio = DOCUMENTOS_GED.$reg_arquivo["abreviacao_GED"] . "/" . $reg_arquivo["os"] . "-" .$reg_arquivo["descricao"] . "/" . $reg_arquivo["os"] . REFERENCIAS . $reg_arquivo["pasta_base"] . "/".$disciplina;

	//Pega o nome do arquivo, sem o  
	$arquivo = $reg_arquivo["arquivo"];
	
	//Explode o nome do arquivo em um array
	$array_extensao = explode(".",$arquivo);
	
	//Pega a extensão do arquivo
	$extensao = $array_extensao[count($array_extensao)-1];
	
	//Pega a imagem relativa a extensão
	$imagem = retornaImagem($extensao);
	
	//Pega o tamanho do arquivo
	$tamanho = formataTamanho(filesize($diretorio.$arquivo));
	
	//Pega a data de criação do arquivo (SERÁ ALTERADO - ARMAZENADO NO BANCO (?) )
	$data_criacao  = date("d/m/Y H:i:s", filectime($diretorio.$arquivo));
	
	//Pega a data de modificação do arquivo (SERÁ ALTERADO - ARMAZENADO NO BANCO (?))
	$data_modificacao = date("d/m/Y H:i:s", filemtime($diretorio.$arquivo));
	
	//Forma o conteúdo da janela de Propriedades
	$conteudo = "<form method=\"POST\" name=\"frm_propriedades\">";
	$conteudo .= "<div id=\"conteudo\" class=\"fonte_11\" style=\"font-size:12px; width:100%; margin:10px;\">";
		$conteudo .= "<div id=\"tipo_arquivo\" style=\"padding:10px;\" onselectstart=\"return false\" unselectable=\"on\">" . $imagem . " tipo de arquivo: " . $extensao . "</div>";
		$conteudo .= "<div id=\"local\" style=\"padding:10px; border-top-style:groove; border-width:2px;\">Caminho: <div style=\"overflow:auto; width:95%; height:30px;border-style:inset; border-left-color:#999999; border-top-color: #999999; border-width:2px;\" onselectstart=\"return false\" unselectable=\"on\">" . $diretorio.$arquivo . "</div></div>";
		$conteudo .= "<div id=\"tamanho\" style=\"padding:10px;\" onselectstart=\"return false\" unselectable=\"on\">Tamanho: " . $tamanho . "</div>";
		$conteudo .= "<div id=\"data_modificacao\" style=\"padding:10px;\" onselectstart=\"return false\" unselectable=\"on\">Última Atualização: " . $data_modificacao . "</div>";
		
		$sql_rev = "SELECT * FROM ".DATABASE.".documentos_referencia, ".DATABASE.".documentos_referencia_revisoes ";
		$sql_rev .= "WHERE documentos_referencia_revisoes.id_documento_referencia = '" . $id_documento_referencia . "' ";
		$sql_rev .= "AND documentos_referencia_revisoes.id_documento_referencia = documentos_referencia.id_documento_referencia ";
		$sql_rev .= "ORDER BY revisao_documento DESC, id_documentos_referencia_revisoes DESC ";


		$cont_versoes = $db->select($sql_rev,'MYSQL');

		//Forma a tabela
		$header = "<table id=\"tbl2\" class=\"dhtmlXGrid\" style=\"width:100%;\" onselectstart=\"return false;\" unselectable=\"on\">";
		$header .= "<tr>";
		$header .= "<td width=\"160\" type=\"ro\">Nº INT</td>";
		$header .= "<td width=\"40\" type=\"ro\">R/V</td>";
		$header .= "<td width=\"100\" type=\"ro\">Autor</td>";
		$header .= "<td width=\"100\" type=\"ro\">Editor</td>";
		$header .= "<td width=\"30\" type=\"ro\">A</td>";
		$header .= "</tr>";
		
		$footer = "</table>";

		//Forma o conteúdo das versões
		while($reg_versoes = mysqli_fetch_assoc($cont_versoes))
		{
			$conteudo_tbl .= "<tr>";
			
			$conteudo_tbl .= "<td style=\"cursor:pointer;\" align=\"left\" title=\"" . $reg_versoes["numero_documento"] . "\">".$reg_versoes["numero_registro"]."</td>";
			
			$conteudo_tbl .= "<td style=\"cursor:pointer;\" align=\"left\">" . $reg_versoes["versao_documento"].".".$reg_versoes["revisao_documento"] . "</td>"; 

			$conteudo_tbl .= "<td style=\"cursor:pointer;\" align=\"left\">" . $nome_funcionario[$reg_versoes["id_autor"]] . "</td>";
			
			$conteudo_tbl .= "<td style=\"cursor:pointer;\" align=\"left\">" . $nome_funcionario[$reg_versoes["id_editor"]] . "</td>";
			
			//Se a versão não for a atual
			if($reg_versoes["id_documentos_referencia_revisoes"]!=$reg_arquivo["id_documento_referencia_revisoes"])
			{
				
				$conteudo_tbl .= "<td style=\"cursor:pointer;\" align=\"left\" onclick=\"xajax_abrir('" .$diretorio."_versoes/".$reg_versoes["arquivo"] . "." . $reg_versoes["id_documentos_referencia_revisoes"] . "'); \" title=\"Abrir essa versão\"><img src=\"../images/buttons_action/bt_busca.gif\"></td>";
			}
			else
			{
				//Original
				$conteudo_tbl .= "<td style=\"cursor:pointer;\" align=\"left\" onclick=\"xajax_abrir('" .$diretorio.$reg_versoes["arquivo"] . "'); \" title=\"Abrir a versão atual\"><img src=\"../images/buttons_action/bt_busca.gif\"></td>";
			}
			
			$conteudo_tbl .= "</tr>";		
			
		}
		
		$conteudo .= $header.$conteudo_tbl.$footer;
		
		$conteudo .= "</div>";

		$conteudo .= "<div id=\"botoes\" style=\"text-align:right; width:90%; margin-left:10px; \"><input type=\"button\" value=\"Voltar\" class=\"fonte_botao\" onclick=\"divPopupInst.destroi();\"></div>";

	$conteudo .= "</div>";
	
	$conteudo .= "</form>";
	
	//Atribue o conteúdo
	$resposta->addAssign("div_conteudo","innerHTML",$conteudo);
	
	$resposta->addScript("grid('tbl2',true,'110');");

	return $resposta;
}

//Preenche os combos de atividades, da janela principal e da janela de busca avançada
function preenchedocumentos($id, $id_os, $busca=false)
{
	$resposta = new xajaxResponse();
	
	//Instancia o objeto do bd
	$db = new banco_dados();
	
	if($busca)
	{
		//É utilizado um array ao invés de um NOT IN (SELECT id_numero_interno) por questão de performance.
		//Seleciona os documentos
		$sql = "SELECT * FROM ".DATABASE.".ged_arquivos, ".DATABASE.".numeros_interno, ".DATABASE.".numero_cliente, ".DATABASE.".atividades, ".DATABASE.".setores ";
		$sql .= "WHERE numero_cliente.id_disciplina = setores.id_setor ";
		$sql .= "AND numero_cliente.id_atividade = atividades.id_atividade ";
		$sql .= "AND atividades.cod = setores.id_setor ";
		$sql .= "AND numeros_interno.id_numcliente = numero_cliente.id_numcliente ";
		$sql .= "AND ged_arquivos.id_numero_interno = numeros_interno.id_numero_interno ";
		$sql .= "AND numeros_interno.id_os = '" . $id_os . "' ";
		$sql .= "AND setores.id_setor = '" . $id . "' ";
		$sql .= "AND atividades.solicitacao = 1 ";		
		$sql .= "GROUP BY atividades.id_atividade ";	
		$sql .= "ORDER BY atividades.descricao ";			
	
	}
	else
	{
		//É utilizado um array ao invés de um NOT IN (SELECT id_numero_interno) por questão de performance.
		//Seleciona os documentos
		$sql = "SELECT * FROM ".DATABASE.".numeros_interno, ".DATABASE.".numero_cliente, ".DATABASE.".atividades, ".DATABASE.".setores ";
		$sql .= "WHERE numero_cliente.id_disciplina = setores.id_setor ";
		$sql .= "AND numero_cliente.id_atividade = atividades.id_atividade ";
		$sql .= "AND atividades.cod = setores.id_setor ";
		$sql .= "AND numeros_interno.id_numcliente = numero_cliente.id_numcliente ";
		$sql .= "AND numeros_interno.id_os = '" . $id_os . "' ";
		$sql .= "AND setores.id_setor = '" . $id . "' ";
		$sql .= "AND atividades.solicitacao = 1 ";		
		$sql .= "GROUP BY atividades.id_atividade ";	
		$sql .= "ORDER BY atividades.descricao ";		
	
	}
			
	$cont = $db->select($sql,'MYSQL');
	
	while($reg = mysqli_fetch_assoc($cont))
	{
		$matriz[$reg["descricao"]] = $reg["id_atividade"];
	}	
	
	if($busca)
	{
		$resposta->addNewOptions("busca_id_atividade", $matriz, $selecionado);	
	}
	else
	{
		$resposta->addNewOptions("id_atividade", $matriz, $selecionado);
	}
	
	return $resposta;
}

//criado em 16/07/2013 - carlos abreu
//Mostra dos documentos solicitados para carregar os arquivos
function preencheNRDocumentos_grid($dados_form, $checkout=0)
{
	$resposta = new xajaxResponse();
	
	//Instancia o objeto do bd
	$db = new banco_dados();
	
	$conteudo = "";
	
	$id_ged_arquivo = "";
	
	$array_arquivos = NULL;
	
	$sql = "SELECT * FROM ".DATABASE.".ged_solicitacoes ";
	$sql .= "WHERE ged_solicitacoes.id_os = '".$dados_form["id_os"]."' ";
	$sql .= "AND ged_solicitacoes.id_funcionario = '".$_SESSION["id_funcionario"]."' ";
	$sql .= "AND ged_solicitacoes.tipo = 3 ";
	$sql .= "ORDER BY ged_solicitacoes.id_ged_arquivo ";
	
	$cont = $db->select($sql,'MYSQL');
	
	while($regs = mysqli_fetch_assoc($cont))
	{
		$array_arquivos[$regs["tipo"]][] = $regs["id_ged_arquivo"];	
	}
	
	$header = "<table id=\"tbl2\" class=\"dhtmlXGrid\" style=\"width:100%\" onselectstart=\"return false;\" unselectable=\"on\">";
	$header .= "<tr>";
	$header .= "<td width=\"120\" type=\"ro\">Nºmero INT</td>"; //300
	$header .= "<td width=\"120\" type=\"ro\">Nºmero Cliente</td>";
	$header .= "<td width=\"120\" type=\"ro\">complemento</td>";
	$header .= "<td width=\"350\" type=\"ro\">Arquivo</td>";
	$header .= "<td width=\"70\" type=\"ro\">Tamanho</td>";
	$header .= "<td width=\"100\" type=\"ro\">Progresso</td>";
	$header .= "<td width=\"30\" type=\"img\">D</td>";	
	$header .= "</tr>";
	
	$footer = "</table>";	

	if($checkout)
	{
		switch($checkout)
		{	
			//Check Out de um arquivo único (click mouse direito)
			case "1":
			
				$id_ged_arquivo = $dados_form["id_ged_arquivo"];

				$sql = "SELECT *, atividades.descricao AS atividades_Descricao FROM ".DATABASE.".OS, ".DATABASE.".numeros_interno, ".DATABASE.".numero_cliente, ".DATABASE.".atividades, ".DATABASE.".ged_arquivos, ".DATABASE.".ged_versoes ";
				$sql .= "WHERE numeros_interno.id_os = OS.id_os ";
				$sql .= "AND numeros_interno.id_numero_interno = ged_arquivos.id_numero_interno ";
				$sql .= "AND numeros_interno.id_numcliente = numero_cliente.id_numcliente ";
				$sql .= "AND numero_cliente.id_atividade = atividades.id_atividade ";
				$sql .= "AND ged_arquivos.id_ged_arquivo = ged_versoes.id_ged_arquivo ";
				$sql .= "AND ged_arquivos.id_ged_arquivo = '".$dados_form["id_ged_arquivo"]."' ";
				$sql .= "AND ged_arquivos.status = 1 "; //em edição
				
				$cont_id_arquivo = $db->select($sql,'MYSQL');				
				
				if($db->numero_registros==0)
				{
					$resposta->addAlert("Não existem documentos disponíveis.");
					$resposta->addScript("xajax.$('btn_checkout_enviar').disabled=true;");
				}
				else
				{
				
					$reg_id_arquivo = mysqli_fetch_assoc($cont_id_arquivo);
					
					$str_complemento = str_replace($reg_id_arquivo["atividades_Descricao"],"",$reg_id_arquivo["complemento"]);
					
					$conteudo .= "<tr height=\"30px\">";
					$conteudo .= "<td align=\"left\">INT-". sprintf("%05d",$reg_id_arquivo["os"]) . "-" .$reg_id_arquivo["sigla"]."-".$reg_id_arquivo["sequencia"] . "</td>";
					$conteudo .= "<td align=\"left\">".$reg_id_arquivo["numero_cliente"]."</td>";
					$conteudo .= "<td align=\"left\">".$reg_id_arquivo["atividades_Descricao"] . " " . $str_complemento."</td>";
					
					$conteudo .= "<td><form name=\"frm_teste_".$reg_id_arquivo["id_numero_interno"]."\" id=\"frm_teste_".$reg_id_arquivo["id_numero_interno"]."\" action=\"upload.php\" target=\"upload_target_".$reg_id_arquivo["id_numero_interno"]."\" method=\"post\" enctype=\"multipart/form-data\" onsubmit=\"startUpload(".$reg_id_arquivo["id_numero_interno"].");\" >";
					$conteudo .= "<input type=\"hidden\" id=\"id_num_dvm\" name=\"id_num_dvm\" value=\"".$reg_id_arquivo["id_numero_interno"]."\">";
					$conteudo .= "<input type=\"hidden\" id=\"operacao\" name=\"operacao\" value=\"".$checkout."\">";
					$conteudo .= "<iframe id=\"upload_target_".$reg_id_arquivo["id_numero_interno"]."\" name=\"upload_target_".$reg_id_arquivo["id_numero_interno"]."\" src=\"#\" style=\"width:0;height:0;border:0px solid #fff;display:none;\"></iframe>";
					$conteudo .= "<p id=\"txtup_".$reg_id_arquivo["id_numero_interno"]."\"><input class=\"caixa\" name=\"myfile_".$reg_id_arquivo["id_numero_interno"]."\" type=\"file\" size=\"30\" />  <input type=\"submit\" name=\"submitBtn\" class=\"caixa\" value=\"Upload\" /></p></td>";
					$conteudo .= "</form>";
					$conteudo .= "<td><p id=\"tam_".$reg_id_arquivo["id_numero_interno"]."\"> </p></td>";
					
					$conteudo .= "<td><p style=\"visibility:hidden;\" id=\"upload_".$reg_id_arquivo["id_numero_interno"]."\"> </p></td>";
					
					$conteudo .= "<td><p style=\"visibility:hidden;\" id=\"delete_".$reg_id_arquivo["id_numero_interno"]."\"><img src=\"../images/buttons_action/apagar.gif\" onclick=\"if(confirm('Deseja excluir o arquivo ')){xajax_excluir_upload(".$reg_id_arquivo["id_numero_interno"].",".$checkout.");delUpload(".$reg_id_arquivo["id_numero_interno"].")}\"></p></td>";
					
					$conteudo .= "</tr>";				
				}		
			
			break;
			
			//Check Out de múltiplos arquivos
			case "2":
			
				//adiciona na cx. de seleção o NumDVM selecionado - verif. se necessário preencher junto o numero_cliente
				$sql_numdvm = "SELECT *, atividades.descricao AS atividades_Descricao FROM ".DATABASE.".OS, ".DATABASE.".numeros_interno, ".DATABASE.".numero_cliente, ".DATABASE.".ged_arquivos, ".DATABASE.".ged_versoes, ".DATABASE.".atividades ";	
				$sql_numdvm .= "WHERE ged_arquivos.id_numero_interno = numeros_interno.id_numero_interno ";
				$sql_numdvm .= "AND numeros_interno.id_numcliente = numero_cliente.id_numcliente ";
				$sql_numdvm .= "AND numeros_interno.id_os = OS.id_os ";
				$sql_numdvm .= "AND numero_cliente.id_atividade = atividades.id_atividade ";
				$sql_numdvm .= "AND ged_arquivos.id_ged_arquivo = ged_versoes.id_ged_arquivo ";
				$sql_numdvm .= "AND ged_arquivos.id_ged_versao = ged_versoes.id_ged_versao ";
				$sql_numdvm .= "AND ged_arquivos.id_ged_arquivo IN (" . implode(",",$array_arquivos[3]) . ") ";
				$sql_numdvm .= "AND ged_arquivos.status = 1 "; //em edição
				
				$cont_numdvm = $db->select($sql_numdvm,'MYSQL');
		
				if($db->numero_registros==0)
				{
					$resposta->addAlert("Não existem documentos disponíveis. \n".$sql_numdvm);
					
					$resposta->addScript("xajax.$('btn_checkout_enviar').disabled=true;");
				}
				else
				{		
					while($reg_numdvm = mysqli_fetch_assoc($cont_numdvm))
					{		
				
						$str_complemento = str_replace($reg_numdvm["atividades_Descricao"],"",$reg_numdvm["complemento"]);
						
						$conteudo .= "<tr height=\"30px\">";
						$conteudo .= "<td align=\"left\">INT-". sprintf("%05d",$reg_numdvm["os"]) . "-" .$reg_numdvm["sigla"]."-".$reg_numdvm["sequencia"] . "</td>";
						$conteudo .= "<td align=\"left\">".$reg_numdvm["numero_cliente"]."</td>";
						$conteudo .= "<td align=\"left\">".$reg_numdvm["atividades_Descricao"] . " " . $str_complemento."</td>";
						
						$conteudo .= "<td><form name=\"frm_teste_".$reg_numdvm["id_numero_interno"]."\" id=\"frm_teste_".$reg_numdvm["id_numero_interno"]."\" action=\"upload.php\" target=\"upload_target_".$reg_numdvm["id_numero_interno"]."\" method=\"post\" enctype=\"multipart/form-data\" onsubmit=\"startUpload(".$reg_numdvm["id_numero_interno"].");\" >";
						$conteudo .= "<input type=\"hidden\" id=\"id_num_dvm\" name=\"id_num_dvm\" value=\"".$reg_numdvm["id_numero_interno"]."\">";
						$conteudo .= "<input type=\"hidden\" id=\"operacao\" name=\"operacao\" value=\"".$checkout."\">";
						$conteudo .= "<iframe id=\"upload_target_".$reg_numdvm["id_numero_interno"]."\" name=\"upload_target_".$reg_numdvm["id_numero_interno"]."\" src=\"#\" style=\"width:0;height:0;border:0px solid #fff;display:none;\"></iframe>";
						$conteudo .= "<p id=\"txtup_".$reg_numdvm["id_numero_interno"]."\"><input class=\"caixa\" name=\"myfile_".$reg_numdvm["id_numero_interno"]."\" type=\"file\" size=\"30\" />  <input type=\"submit\" name=\"submitBtn\" class=\"caixa\" value=\"Upload\" /></p></td>";
						$conteudo .= "</form>";
						$conteudo .= "<td><p id=\"tam_".$reg_numdvm["id_numero_interno"]."\"> </p></td>";
						
						$conteudo .= "<td><p style=\"visibility:hidden;\" id=\"upload_".$reg_numdvm["id_numero_interno"]."\"> </p></td>";
						
						$conteudo .= "<td><p style=\"visibility:hidden;\" id=\"delete_".$reg_numdvm["id_numero_interno"]."\"><img src=\"../images/buttons_action/apagar.gif\" onclick=\"if(confirm('Deseja excluir o arquivo ')){xajax_excluir_upload(".$reg_numdvm["id_numero_interno"].",".$checkout.");delUpload(".$reg_numdvm["id_numero_interno"].")}\"></p></td>";
						
						$conteudo .= "</tr>";	
											
					}
				}
				
			break;
		}			
	}
	else
	{	
		//Procura no banco se existem documentos cadastrados no GED para aquela OS/Disciplina/Atividade
		$sql = "SELECT * FROM ".DATABASE.".ged_arquivos, ".DATABASE.".numeros_interno, ".DATABASE.".numero_cliente ";
		$sql .= "WHERE ged_arquivos.id_numero_interno = numeros_interno.id_numero_interno ";
		$sql .= "AND numeros_interno.id_numcliente = numero_cliente.id_numcliente ";
		
		//Caso selecionado Atividade/tipo de documento
		if($dados_form["id_atividade"])
		{
			$sql .= "AND numero_cliente.id_atividade = '" . $dados_form["id_atividade"] . "' ";
		}
		
		$sql .= "AND numeros_interno.id_os = '" . $dados_form["id_os"] . "' ";
		$sql .= "AND numeros_interno.id_disciplina = '" . $dados_form["disciplina"] . "' ";
		
		$cont_ged_arquivo = $db->select($sql,'MYSQL');
	  
		while($reg_ged_arquivo = mysqli_fetch_assoc($cont_ged_arquivo))
		{
			$array_numdvm[] = $reg_ged_arquivo["id_numero_interno"];
			
			$id_ged_arquivo = $reg_ged_arquivo["id_ged_arquivo"];
		}
	  
		$filtro_numdvm = "";
		
		$filtro_numdvm = "(" . implode(",",$array_numdvm) . ") ";		
		
		$sql_nrdocs = "SELECT *, atividades.descricao AS atividades_Descricao FROM ".DATABASE.".numeros_interno, ".DATABASE.".numero_cliente, ".DATABASE.".OS, ".DATABASE.".setores, ".DATABASE.".atividades ";	
		$sql_nrdocs .= "WHERE numeros_interno.id_numcliente = numero_cliente.id_numcliente ";
		//Caso selecionado Atividade/tipo de documento
		if($dados_form["id_atividade"])
		{
			$sql_nrdocs .= "AND numero_cliente.id_atividade = '" . $dados_form["id_atividade"] . "' ";
		}
		$sql_nrdocs .= "AND numeros_interno.id_os = ".DATABASE.".OS.id_os ";
		$sql_nrdocs .= "AND numero_cliente.id_atividade = atividades.id_atividade ";
		$sql_nrdocs .= "AND numeros_interno.id_disciplina = '" . $dados_form["disciplina"] . "' ";
		$sql_nrdocs .= "AND numeros_interno.id_os = '" . $dados_form["id_os"] . "' ";
		$sql_nrdocs .= "AND setores.id_setor = numeros_interno.id_disciplina ";		
		
		if(count($array_numdvm)>0)
		{
			//Filtra pra fora os documentos existentes
			$sql_nrdocs .= "AND numeros_interno.id_numero_interno NOT IN " . $filtro_numdvm;
		}
		
		$sql_nrdocs .= "ORDER BY numeros_interno.sequencia ";	
	  
		$cont_nrdocs = $db->select($sql_nrdocs,'MYSQL');
	  
		if($db->numero_registros==0)
		{
			$resposta->addAlert("Não existem documentos disponíveis.");
			$resposta->addScript("xajax.$('btn_checkout_enviar').disabled=true;");
		}
		else
		{	  
			while($reg_nrdocs = mysqli_fetch_assoc($cont_nrdocs))
			{			
				$str_complemento = str_replace($reg_nrdocs["atividades_Descricao"],"",$reg_nrdocs["complemento"]);
				
				$conteudo .= "<tr height=\"30px\">";
				$conteudo .= "<td align=\"left\">INT - ". sprintf("%05d",$reg_nrdocs["os"]) . " - " .$reg_nrdocs["sigla"]." - ".$reg_nrdocs["sequencia"] . "</td>";
				$conteudo .= "<td align=\"left\">".$reg_nrdocs["numero_cliente"]."</td>";
				$conteudo .= "<td align=\"left\">".$reg_nrdocs["atividades_Descricao"] . " " . $str_complemento."</td>";
				
				$conteudo .= "<td><form name=\"frm_teste_".$reg_nrdocs["id_numero_interno"]."\" id=\"frm_teste_".$reg_nrdocs["id_numero_interno"]."\" action=\"upload.php\" target=\"upload_target_".$reg_nrdocs["id_numero_interno"]."\" method=\"post\" enctype=\"multipart/form-data\" onsubmit=\"startUpload(".$reg_nrdocs["id_numero_interno"].");\" >";
				$conteudo .= "<input type=\"hidden\" id=\"id_num_dvm\" name=\"id_num_dvm\" value=\"".$reg_nrdocs["id_numero_interno"]."\">";
				$conteudo .= "<input type=\"hidden\" id=\"operacao\" name=\"operacao\" value=\"".$checkout."\">";
				$conteudo .= "<iframe id=\"upload_target_".$reg_nrdocs["id_numero_interno"]."\" name=\"upload_target_".$reg_nrdocs["id_numero_interno"]."\" src=\"#\" style=\"width:0;height:0;border:0px solid #fff;display:none;\"></iframe>";
				$conteudo .= "<p id=\"txtup_".$reg_nrdocs["id_numero_interno"]."\"><input class=\"caixa\" name=\"arquivo_".$reg_nrdocs["id_numero_interno"]."\" type=\"file\" size=\"30\" />  <input type=\"submit\" name=\"submitBtn\" class=\"caixa\" value=\"Upload\" /></p></td>";
				$conteudo .= "</form>";
				$conteudo .= "<td><p id=\"tam_".$reg_nrdocs["id_numero_interno"]."\"> </p></td>";
				
				$conteudo .= "<td><p style=\"visibility:hidden;\" id=\"upload_".$reg_nrdocs["id_numero_interno"]."\"> </p></td>";
				
				$conteudo .= "<td><p style=\"visibility:hidden;\" id=\"delete_".$reg_nrdocs["id_numero_interno"]."\"><img src=\"../images/buttons_action/apagar.gif\" onclick=\"if(confirm('Deseja excluir o arquivo ')){xajax_excluir_upload(".$reg_nrdocs["id_numero_interno"].",".$checkout.");delUpload(".$reg_nrdocs["id_numero_interno"].")}\"></p></td>";
				
				$conteudo .= "</tr>";		
		  
			}
		}	
	}
	
	$resposta->addAssign("div_nrdocs","innerHTML", $header.$conteudo.$footer);
	
	$resposta->addScript("grid('tbl2',true,'380');");	

	return $resposta;
}

//Preenche os combos de disciplinas da janela de busca avançada
function preenchedisciplina($id_os, $combo='')
{
	//CRIADO POR CARLOS ABREU PARA FILTRAR AS DISCIPLINAS
	//CUJOS DOCS ESTEJAM RELACIONADOS NO GED
	//17/09/2010
	$resposta = new xajaxResponse();
	
	//Instancia o objeto do bd
	$db = new banco_dados();
	
	if($combo=='')	
	{
		//Seleciona os dados para o preenchimento do combo de disciplinas
		$sql = "SELECT setor, id_setor FROM ".DATABASE.".setores, ".DATABASE.".numeros_interno, ".DATABASE.".ged_arquivos ";
		$sql .= "WHERE numeros_interno.id_disciplina = setores.id_setor ";
		$sql .= "AND numeros_interno.id_os = '".$id_os."' ";
		$sql .= "AND numeros_interno.id_numero_interno = ged_arquivos.id_numero_interno ";
		$sql .= "GROUP BY id_disciplina ";
		$sql .= "ORDER BY setor ";
		
		$cont = $db->select($sql,'MYSQL');
	
		$matriz_disc["TODAS"] = "";
		
		while($reg_disciplina = mysqli_fetch_assoc($cont))
		{
			$matriz_disc[$reg_disciplina["setor"]] = $reg_disciplina["id_setor"];
		}
		
		$resposta->addNewOptions("busca_id_disciplina", $matriz_disc, $selecionado,false);	
	
	}
	else
	{
		//Seleciona os dados para o preenchimento do combo de disciplinas
		$sql = "SELECT setor, id_setor FROM ".DATABASE.".setores, ".DATABASE.".numeros_interno ";
		$sql .= "WHERE numeros_interno.id_disciplina = setores.id_setor ";
		$sql .= "AND numeros_interno.id_os = '".$id_os."' ";
		$sql .= "GROUP BY id_disciplina ";
		$sql .= "ORDER BY setor ";
		
		$cont = $db->select($sql,'MYSQL');
	
		$matriz_disc["TODAS"] = "";
		
		while($reg_disciplina = mysqli_fetch_assoc($cont))
		{
			$matriz_disc[$reg_disciplina["setor"]] = $reg_disciplina["id_setor"];
		}
		
		$resposta->addNewOptions("disciplina", $matriz_disc, $selecionado,false);			
	}
	
	return $resposta;
}

//Preenche os combos da busca avançada
function preencheBuscaAvancada($tipo_busca="")
{
	$resposta = new xajaxResponse();

	$db = new banco_dados();
	
	$resposta->addScriptCall("limpa_combo('busca_id_cliente')");
	
	if($tipo_busca==1)
	{
		$sql = "SELECT empresas.id_empresa, empresas.empresa, unidades.unidade FROM ".DATABASE.".numeros_interno, ".DATABASE.".ged_arquivos, ".DATABASE.".empresas, ".DATABASE.".unidades, ".DATABASE.".OS "; //, ".DATABASE.".numeros_interno - retirado devido a impressão do escopo
		$sql .= "WHERE numeros_interno.id_numero_interno = ged_arquivos.id_numero_interno ";
		$sql .= "AND numeros_interno.id_os = OS.id_os ";
		$sql .= "AND os.os > 1700 ";
		$sql .= "AND OS.id_empresa = empresas.id_empresa ";
		$sql .= "AND empresas.id_unidade = unidades.id_unidade ";
	}
	else
	{
		$sql = "SELECT empresas.id_empresa, empresas.empresa, unidades.unidade FROM ".DATABASE.".documentos_referencia, ".DATABASE.".empresas, ".DATABASE.".unidades, ".DATABASE.".OS "; //, ".DATABASE.".numeros_interno - retirado devido a impressão do escopo
		$sql .= "WHERE documentos_referencia.id_os = OS.id_os ";
		$sql .= "AND os.os > 1700 ";
		$sql .= "AND OS.id_empresa = empresas.id_empresa ";
		$sql .= "AND empresas.id_unidade = unidades.id_unidade ";		
	}	
	
	if($_SESSION["id_funcionario"]!=6)
	{
	
		$sql .= "AND os.os > 1700 ";
		
	}
	
	$sql .= "GROUP BY empresas.id_empresa, empresas.id_unidade ";
	$sql .= "ORDER BY empresa ";
	
	$reg = $db->select($sql,'MYSQL'); 
	
	while ($reg_os = mysqli_fetch_assoc($reg))
	{	
   
		$matriz_os[$reg_os["empresa"]." - ".$reg_os["unidade"]] = $reg_os["id_empresa"];		
	}

	//Preenche o combo de OS
	$resposta->addCreateOptions("busca_id_cliente",$matriz_os,"0",false);	
	
	return $resposta;
}

//Preenche os combos da busca avançada
function preenche_os_BuscaAvancada($dados_form)
{
	$resposta = new xajaxResponse();

	$db = new banco_dados();
	
	$resposta->addScriptCall("limpa_combo('busca_id_os')");
	
	if($dados_form["tipo_busca"]==1)
	{
		$sql = "SELECT OS.id_os, os.os, OS.descricao FROM ".DATABASE.".numeros_interno, ".DATABASE.".ged_arquivos, ".DATABASE.".OS "; //, ".DATABASE.".numeros_interno - retirado devido a impressão do escopo
		$sql .= "WHERE numeros_interno.id_numero_interno = ged_arquivos.id_numero_interno ";
		$sql .= "AND numeros_interno.id_os = OS.id_os ";
		$sql .= "AND os.os > 1700 ";
	}
	else
	{
		$sql = "SELECT OS.id_os, os.os, OS.descricao FROM ".DATABASE.".documentos_referencia, ".DATABASE.".OS "; //, ".DATABASE.".numeros_interno - retirado devido a impressão do escopo
		$sql .= "WHERE documentos_referencia.id_os = OS.id_os ";
		$sql .= "AND os.os > 1700 ";
		
	}
	
	if($dados_form["busca_id_cliente"]!="")
	{
		$sql .= "AND OS.id_empresa = '".$dados_form["busca_id_cliente"]."' ";
	}
		
	if($_SESSION["id_funcionario"]!=6)
	{
	
		$sql .= "AND os.os > 1700 ";
		
	}
	
	$sql .= "GROUP BY OS.id_os ";
	$sql .= "ORDER BY OS ";
	
	$reg = $db->select($sql,'MYSQL'); 
	
	while ($reg_os = mysqli_fetch_assoc($reg))
	{	
		$os = sprintf("%05d",$reg_os["os"]);
   
		$matriz_os[$os . " - " . substr($reg_os["descricao"],0,40)] = $reg_os["id_os"];		
	}

	//Preenche o combo de OS
	$resposta->addCreateOptions("busca_id_os",$matriz_os,"0",false);	
	
	return $resposta;
}

//Função de busca de arquivos
function buscaArquivos($string_busca,$seleciona_arquivo=false)
{
	$resposta = new xajaxResponse();

	$db = new banco_dados();

	$sql_busca = "SELECT *, ged_arquivos.id_ged_arquivo FROM ".DATABASE.".os_x_funcionarios, ".DATABASE.".numeros_interno, ".DATABASE.".numero_cliente, ".DATABASE.".ged_arquivos, ".DATABASE.".ged_versoes ";
	$sql_busca .= "WHERE ged_arquivos.id_ged_versao = ged_versoes.id_ged_versao ";
	$sql_busca .= "AND numeros_interno.id_numero_interno = ged_arquivos.id_numero_interno ";
	$sql_busca .= "AND numeros_interno.id_numcliente = numero_cliente.id_numcliente ";
	$sql_busca .= "AND numeros_interno.id_os = os_x_funcionarios.id_os ";
	$sql_busca .= "AND os_x_funcionarios.id_funcionario = '" . $_SESSION["id_funcionario"] . "' ";
	$sql_busca .= "AND (SUBSTR(ged_versoes.nome_arquivo, (LENGTH(ged_versoes.nome_arquivo) - LOCATE('/', REVERSE(ged_versoes.nome_arquivo))+1), LOCATE('/',REVERSE(ged_versoes.nome_arquivo))) LIKE '%" . addslashes($string_busca) . "%' ";
	$sql_busca .= "OR ged_arquivos.descricao LIKE '%" . addslashes($string_busca) . "%' ";
	$sql_busca .= "OR numero_cliente.numero_cliente LIKE '%" . addslashes($string_busca) . "%')";

	$cont_busca = $db->select($sql_busca,'MYSQL');
	
	$num_reg_arq = $db->numero_registros;

	$i = 0;
	
	$conteudo = "";

	while($reg_busca = mysqli_fetch_assoc($cont_busca))
	{		
		if($i%2)
		{
			// escuro
			$cor = "#F0F0F0";		
		}
		else
		{
			//claro		
			$cor = "#FFFFFF";
		}
		
		$i++;
		
		$caminho = DOCUMENTOS_GED . $reg_busca["base"] . "/" . $reg_busca["os"] . "/" . substr($reg_busca["os"],0,4) . DISCIPLINAS . $reg_busca["disciplina"] . "/" . $reg_busca["atividade"];
		
		$conteudo .= "<div id=\"div_arquivo\" style=\"width:100%; background-color:" . $cor . "; font-family:Arial; font-size:9px; padding-top:5px;\" onMouseOver=\"setPointerDiv(this, 1, 'over', '". $cor ."', '#BECCD9', '#FFCC99');\" onMouseOut=\"setPointerDiv(this, 1, 'out', '". $cor . "', '#BECCD9', '#FFCC99');\" onclick=\"xajax_preencheArquivos('xajax.getFormValues(\"frm_ged\")'); xajax_preenchePastas('".DOCUMENTOS_GED."','" . $caminho . "'); buscamenuInst.destroi();  \">";
		$conteudo .= "<div id=\"nome_arquivo_" . $reg_busca["id_ged_arquivo"] . "\">" . $reg_busca["nome_arquivo"] . "</div>";
		$conteudo .= "</div>";
	
	}

	$i = 0;

	//Percorre os Docs. de Ref.
	$sql_busca_ref = "SELECT *,OS.descricao FROM ".DATABASE.".empresas, ".DATABASE.".OS, ".DATABASE.".os_x_funcionarios, ".DATABASE.".setores, ".DATABASE.".documentos_referencia_revisoes, ".DATABASE.".tipos_referencia, ".DATABASE.".tipos_documentos_referencia, ".DATABASE.".documentos_referencia ";
	$sql_busca_ref .= "WHERE documentos_referencia.id_os = OS.id_os ";
	$sql_busca_ref .= "AND documentos_referencia.id_tipo_documento_referencia = tipos_documentos_referencia.id_tipos_documentos_referencia ";
	$sql_busca_ref .= "AND documentos_referencia.id_disciplina = setores.id_setor ";
	$sql_busca_ref .= "AND documentos_referencia.id_documento_referencia_revisoes = documentos_referencia_revisoes.id_documentos_referencia_revisoes ";
	$sql_busca_ref .= "AND tipos_documentos_referencia.id_tipo_referencia = tipos_referencia.id_tipo_referencia ";
	$sql_busca_ref .= "AND OS.id_empresa = empresas.id_empresa ";
	$sql_busca_ref .= "AND documentos_referencia.id_os = os_x_funcionarios.id_os ";
	$sql_busca_ref .= "AND documentos_referencia.id_documento_referencia_revisoes = documentos_referencia_revisoes.id_documentos_referencia_revisoes ";
	$sql_busca_ref .= "AND (".DATABASE.".documentos_referencia_revisoes.arquivo LIKE '%" . addslashes($string_busca) . "%' ";
	$sql_busca_ref .= "OR ".DATABASE.".documentos_referencia.numero_registro LIKE '%" . addslashes($string_busca) . "%' ";
	$sql_busca_ref .= "OR ".DATABASE.".documentos_referencia.numero_documento LIKE '%" . addslashes($string_busca) . "%' ";
	$sql_busca_ref .= "OR ".DATABASE.".tipos_documentos_referencia.tipo_documento LIKE '%" . addslashes($string_busca) . "%') ";
	$sql_busca_ref .= "GROUP BY documentos_referencia.id_documento_referencia ";	
	
	$cont_busca_ref = $db->select($sql_busca_ref,'MYSQL');
	
	$num_reg_ref = $db->numero_registros;

	while($reg_busca_ref = mysqli_fetch_assoc($cont_busca_ref))
	{
		if($i%2)
		{
			// escuro
			$cor = "#F0F0F0";		
		}
		else
		{
			//claro		
			$cor = "#FFFFFF";
		}
		
		$i++;
				
		$os = sprintf("%05d",$reg_busca_ref["os"]);
		
		//Monta a pasta
		//ex: ATAS/MEC
		if($reg_docs["grava_disciplina"]==1)
		{
			$disciplina = $reg_busca_ref["abreviacao"]."/";	
		}
		else
		{
			$disciplina = "";	
		}
		
		//monta diretorio base
		$diretorio = DOCUMENTOS_GED.$reg_busca_ref["abreviacao_GED"] . "/" . $reg_busca_ref["os"] . "-" .$reg_busca_ref["descricao"] . "/" . $reg_busca_ref["os"] . REFERENCIAS . $reg_busca_ref["pasta_base"] . "/".$disciplina;

		//$conteudo .= "<div id=\"div_arquivo\" style=\"width:100%; background-color:" . $cor . "; font-family:Arial; font-size:9px; padding-top:5px;\" onMouseOver=\"setPointerDiv(this, 1, 'over', '". $cor ."', '#BECCD9', '#FFCC99');\" onMouseOut=\"setPointerDiv(this, 1, 'out', '". $cor . "', '#BECCD9', '#FFCC99');\" onclick=\"xajax_preencheArquivos('" . $diretorio . "', '" . $reg_busca_ref["id_documento_referencia"] . "',".$reg_busca_ref["id_os"]."); buscamenuInst.destroi();  \">";
		
		$conteudo .= "<div id=\"div_arquivo\" style=\"width:100%; background-color:" . $cor . "; font-family:Arial; font-size:9px; padding-top:5px;\" onMouseOver=\"setPointerDiv(this, 1, 'over', '". $cor ."', '#BECCD9', '#FFCC99');\" onMouseOut=\"setPointerDiv(this, 1, 'out', '". $cor . "', '#BECCD9', '#FFCC99');\" onclick=\"xajax_preencheArquivos('xajax.getFormValues(\"frm_ged\")'); buscamenuInst.destroi();  \">";
		$conteudo .= "<div id=\"nome_arquivo_" . $reg_busca_ref["id_documento_referencia"] . "\">" . $reg_busca_ref["arquivo"] . "</div>";
		$conteudo .= "</div>";
	
	}

	if($num_reg_arq==0 && $num_reg_ref==0)
	{
		$conteudo .= "<div id=\"div_aviso\" style=\"color:#999999; font-family:Arial; font-size:9px;\">Nenhum arquivo encontrado.</div>";
	}

	$resposta->addAssign("menu_div_fundo","innerHTML",$conteudo);
	
	return $resposta;
}

//Função de busca de arquivos avançada
function buscaArquivosAvancada($dados_form)
{
	$resposta = new xajaxResponse();
	
	$resposta->addScript("RCmenuInst.destroi(); ");
	
	//Instancia o objeto do bd
	$db = new banco_dados();

	$array_arquivos = NULL;
	
	//seleciona o autor
	$sql = "SELECT id_funcionario,nome_usuario FROM ".DATABASE.".funcionarios ";
	$sql .= "ORDER BY funcionarios.funcionario ";
	
	$cont = $db->select($sql,'MYSQL');

	while($regs = mysqli_fetch_assoc($cont))
	{
		$nome_funcionario[$regs["id_funcionario"]] = $regs["nome_usuario"];
	}
	
	$sql = "SELECT * FROM ".DATABASE.".ged_solicitacoes ";
	$sql .= "WHERE ged_solicitacoes.id_os = '".$dados_form["busca_id_os"]."' ";
	$sql .= "AND ged_solicitacoes.id_funcionario = '".$_SESSION["id_funcionario"]."' ";
	$sql .= "ORDER BY ged_solicitacoes.id_ged_arquivo ";
	
	$cont1 = $db->select($sql,'MYSQL');
	
	while($regs = mysqli_fetch_assoc($cont1))
	{
		$array_arquivos[$regs["tipo"]][] = $regs["id_ged_arquivo"];	
	}
	
	//Cria um array com os critérios da busca - PROJETO
	$dados_form["busca_id_os"] ? $array_sql_filtro[] = "numeros_interno.id_os = '" . $dados_form["busca_id_os"] . "' " : "";
	$dados_form["busca_id_atividade"] ? $array_sql_filtro[] = "numero_cliente.id_atividade = '" . $dados_form["busca_id_atividade"] . "' " : "";	
	$dados_form["busca_id_disciplina"] ? $array_sql_filtro[] = "numeros_interno.id_disciplina = '" . $dados_form["busca_id_disciplina"] . "' " : "";	
	$dados_form["busca_observacao"] ? $array_sql_filtro[] = "ged_arquivos.descricao LIKE '%" . $dados_form["busca_observacao"] . "%' " : "";		
	$dados_form["busca_texto"] ? $array_sql_filtro[] = "(solicitacao_documentos_detalhes.tag LIKE '%" . $dados_form["busca_texto"] . "%' OR solicitacao_documentos_detalhes.tag2 LIKE '%" . $dados_form["busca_texto"] . "%' OR solicitacao_documentos_detalhes.tag3 LIKE '%" . $dados_form["busca_texto"] . "%' OR solicitacao_documentos_detalhes.tag3 LIKE '%" . $dados_form["busca_texto"] . "%' OR solicitacao_documentos_detalhes.tag4 LIKE '%" . $dados_form["busca_texto"] . "%' OR numero_cliente.numero_cliente LIKE '%" . $dados_form["busca_texto"] . "%'  OR numeros_interno.sequencia LIKE '%" . $dados_form["busca_texto"] . "%' ) " : "";		
	
	//Cria um array com os critérios da busca - REFERÊNCIA
	$dados_form["busca_id_os"] ? $array_sql_filtro_ref[] = "documentos_referencia.id_os = '" . $dados_form["busca_id_os"] . "' " : "";
	
	$dados_form["busca_id_disciplina"] ? $array_sql_filtro_ref[] = "documentos_referencia.id_disciplina = '" . $dados_form["busca_id_disciplina"] . "' " : "";	
	
	$dados_form["busca_texto"] ? $array_sql_filtro_ref[] = "(documentos_referencia_revisoes.arquivo LIKE '%" . $dados_form["busca_texto"] . "%' OR documentos_referencia.numero_registro LIKE '%" . $dados_form["busca_texto"] . "%' OR documentos_referencia.numero_documento LIKE '%" . $dados_form["busca_texto"] . "%' OR documentos_referencia.titulo LIKE '%" . $dados_form["busca_texto"] . "%' OR documentos_referencia.palavras_chave LIKE '%" . $dados_form["busca_texto"] . "%' OR documentos_referencia.origem LIKE '%" . $dados_form["busca_texto"] . "%' OR tipos_documentos_referencia.tipo_documento LIKE '%" . $dados_form["busca_texto"] . "%') " : "";		

	//Seleciona os dados da Equipe
	$sql_osxfunc = "SELECT id_os, id_funcionario FROM ".DATABASE.".os_x_funcionarios ";
	$sql_osxfunc .= "WHERE id_funcionario = " . $_SESSION["id_funcionario"] . " ";
	
	$cont_osxfunc = $db->select($sql_osxfunc,'MYSQL');

	while($reg_osxfunc = mysqli_fetch_assoc($cont_osxfunc))
	{
		$array_osxfunc[$reg_osxfunc["id_os"]] = $reg_osxfunc["id_funcionario"];	
	}

	if(count($array_sql_filtro)>0)
	{
		$sql_filtro = "AND (";		

		foreach($array_sql_filtro as $chave=>$valor)
		{
			//Adiciona "AND" exceto no primeiro item
			$sql_operador = $chave > 0 ? "AND " : "";
			$sql_filtro .= $sql_operador . $valor;
		}
		
		$sql_filtro .= ")";

	}	

	if(count($array_sql_filtro_ref)>0)
	{
		$sql_filtro_ref = "AND (";		

		foreach($array_sql_filtro_ref as $chave=>$valor)
		{
			//Adiciona "AND" exceto no primeiro item
			$sql_operador = $chave > 0 ? "AND " : "";
			$sql_filtro_ref .= $sql_operador . $valor;
		}
		
		$sql_filtro_ref .= ")";

	}

	//PROJETO
	switch($dados_form["tipo_busca"])
	{
		case "1":			
			$sql_busca = "SELECT * FROM ".DATABASE.".OS, ".DATABASE.".setores, ".DATABASE.".solicitacao_documentos_detalhes, ".DATABASE.".numeros_interno, ".DATABASE.".ged_versoes, ".DATABASE.".ged_arquivos, ".DATABASE.".numero_cliente ";
			$sql_busca .= "WHERE solicitacao_documentos_detalhes.id_numero_interno = numeros_interno.id_numero_interno ";
			$sql_busca .= "AND numeros_interno.id_numero_interno = ged_arquivos.id_numero_interno ";
			$sql_busca .= "AND ged_arquivos.id_ged_versao = ged_versoes.id_ged_versao "; //Pega somente a revisao_documento atual
			$sql_busca .= "AND numeros_interno.id_numcliente = numero_cliente.id_numcliente ";
			$sql_busca .= "AND numeros_interno.id_os = OS.id_os ";
			$sql_busca .= "AND numeros_interno.id_disciplina = setores.id_setor ";
		
			$sql_busca .= $sql_filtro;
			
			$sql_busca .= "ORDER BY os.os ASC, setores.setor ASC, ged_arquivos.descricao ASC ";
			
			
			$cont_busca = $db->select($sql_busca,'MYSQL');
			
			$conteudo = "";
			
			$header = "<table id=\"tbl2\" class=\"dhtmlXGrid\" style=\"width:100%\" onselectstart=\"return false;\" unselectable=\"on\">";
			$header .= "<tr>";
			$header .= "<td width=\"20\" type=\"ro\"> </td>";
			$header .= "<td width=\"20\" type=\"ro\"> </td>";
			$header .= "<td width=\"20\" type=\"ro\"> </td>";			
			$header .= "<td width=\"120\" type=\"ro\">Arquivo</td>";
			$header .= "<td width=\"180\" type=\"ro\">Nºmero Cliente</td>";
			$header .= "<td width=\"70\" type=\"ro\">Autor</td>";
			$header .= "<td width=\"50\" type=\"ro\">OS</td>";
			$header .= "<td width=\"50\" type=\"ro\">Disc.</td>";
			$header .= "<td width=\"140\" type=\"ro\">Título 1</td>";
			$header .= "<td width=\"140\" type=\"ro\">Título 2</td>";
			$header .= "<td width=\"140\" type=\"ro\">Título 3</td>";
			$header .= "<td width=\"140\" type=\"ro\">Título 4</td>";
			$header .= "<td width=\"50\" type=\"ro\">Abrir</td>";	
			$header .= "</tr>";
			
			$footer = "</table>";
		
			while($reg_busca = mysqli_fetch_assoc($cont_busca))
			{			
		
				$caminho = DOCUMENTOS_GED . $reg_busca["base"] . "/" . $reg_busca["os"] . "/" . substr($reg_busca["os"],0,4) . DISCIPLINAS . $reg_busca["disciplina"] . "/" . $reg_busca["atividade"] . "/" . $reg_busca["sequencial"]."/".$reg_busca["nome_arquivo"];
			
				//Explode o nome do arquivo
				$extensao_array = explode(".",$reg_busca["nome_arquivo"]);
				
				//Pega somente a extensão
				$extensao = $extensao_array[count($extensao_array)-1];	
				
				//Pega a imagem relativa a extensão
				$imagem = retornaImagem($extensao);

				//Pega a imagem da bolinha referente ao status do arquivo
				$imagem_bolinha = retornaImagem($reg_busca["status"]);

				//Preenche o checkbox, se o arquivo estiver nos cookies
				if(in_array($reg_busca["id_ged_arquivo"],$array_arquivos[1]) || in_array($reg_busca["id_ged_arquivo"],$array_arquivos[2]) || in_array($reg_busca["id_ged_arquivo"],$array_arquivos[3]))
				{
					$chk_checked = "checked";
				}
				else
				{
					$chk_checked = "";
				}
				
				if($reg_busca["status"]=="2")
				{
					$chk_disabled = "disabled";					
				}
				else
				{
					$chk_disabled = "";
				}

				//ADICIONADO POR CARLOS ABREU
				//16/09/2010
				$operacao = $extensao=="zip" ? "5" : "6"; //3= Check-in sendo ZIP; 1 = Check-in não sendo ZIP
				
				//Atribue o evento para onclick no div
				$onclick = "xajax_dadosArquivo('".$reg_busca["id_ged_arquivo"]."'); ";
				
				//Atribue o evento para rclick no div
				//$rclick = "popupMenu('" . $operacao . "',event); ";
				$rclick = "popupMenu('" . $operacao . "',event,'".$reg_busca["id_ged_arquivo"]."','".$caminho."'); ";
	
				//Atribue o evento para double click no div
				$dblclick = "xajax_abrir('" . $caminho . "'); ";				
				
				$resposta->addScript("document.body.onclick = function () { popupMenu('',event,'','".$caminho."'); } ");
			
				$imagem_vis = "<img src=\"../images/buttons_action/procurar.png\" title=\"Abrir versão atual\" style=\"cursor:pointer;\" onclick=\"xajax_abrir('" . $caminho . "');\">";
				
				$conteudo .= "<tr>";
				
				if($_SESSION["id_funcionario"]==$array_osxfunc[$reg_busca["id_os"]] || $_SESSION["id_funcionario"]==$reg_busca["id_autor"] || $array_liberacao[$reg_busca["id_ged_arquivo"]])
				{
					$conteudo .= "<td><input type=\"checkbox\" value=\"1\" name=\"chk_" . $reg_busca["id_ged_arquivo"] . "\" onclick=\"data=new Date(); xajax_selecaoCheckbox(this.name, this.checked,data.getHours()+':'+data.getMinutes());\" " . $chk_checked . " " . $chk_disabled . "></td>";
				}
				else
				{
					$conteudo .= "<td> </td>";				
				}
				
				$conteudo .= "<td>" . $imagem_bolinha . "</td>";
				$conteudo .= "<td>" .$imagem . "</td>";
				$conteudo .= "<td><div name=\"a_itens_".$reg_busca["id_ged_arquivo"]."\" id=\"a_itens_".$reg_busca["id_ged_arquivo"]."\" class=\"cell1\" style=\"width:17%; float:left; cursor:pointer; \" onclick=\"" . $onclick . " \" oncontextmenu=\"" . $onclick . $rclick . " return false;\" ondblclick=\"" . $dblclick . "\">" .substr($reg_busca["nome_arquivo"],0,40) . "</div></td>";				
				$conteudo .= "<td>" . $reg_busca["numero_cliente"] . "</td>";
				$conteudo .= "<td>". $nome_funcionario[$reg_busca["id_autor"]]."</td>";
				$conteudo .= "<td>" . $reg_busca["os"] . "</td>";
				$conteudo .= "<td>" . $reg_busca["abreviacao"] . "</td>";
				$conteudo .= "<td>" . $reg_busca["tag"] . "</td>";
				$conteudo .= "<td>" . $reg_busca["tag2"] . "</td>";
				$conteudo .= "<td>" . $reg_busca["tag3"] . "</td>";
				$conteudo .= "<td>" . $reg_busca["tag4"] . "</td>";
				$conteudo .= "<td>" . $imagem_vis . "</td>";
				$conteudo .= "</tr>";	
			}			
				
			$resposta->addAssign("div_busca_resultados","innerHTML", $header.$conteudo.$footer);
			
			$resposta->addScript("grid('tbl2',true,'350');");				
				
			//Correção bug
			$resposta->addScript("xajax.('div_busca_resultados').style.width='1150px'; ");		
				

		break;
		
		case "2":		
		
			$sql_busca = "SELECT * FROM ".DATABASE.".empresas, ".DATABASE.".tipos_referencia, ".DATABASE.".tipos_documentos_referencia, ".DATABASE.".documentos_referencia, ".DATABASE.".documentos_referencia_revisoes, ".DATABASE.".setores, ".DATABASE.".OS ";
			$sql_busca .= "WHERE documentos_referencia.id_disciplina = setores.id_setor ";
			$sql_busca .= "AND documentos_referencia.id_os = OS.id_os ";
			$sql_busca .= "AND documentos_referencia.id_documento_referencia_revisoes = documentos_referencia_revisoes.id_documentos_referencia_revisoes ";
			$sql_busca .= "AND documentos_referencia.id_tipo_documento_referencia = tipos_documentos_referencia.id_tipos_documentos_referencia ";
			$sql_busca .= "AND tipos_documentos_referencia.id_tipo_referencia = tipos_referencia.id_tipo_referencia ";
			$sql_busca .= "AND OS.id_empresa = empresas.id_empresa ";
		
			$sql_busca .= $sql_filtro_ref;
			
			$sql_busca .= " ORDER BY documentos_referencia_revisoes.arquivo ASC ";	
			
			$cont_busca = $db->select($sql_busca,'MYSQL');
			
			$conteudo = "";
			
			$header = "<table id=\"tbl2\" class=\"dhtmlXGrid\" style=\"width:100%\" onselectstart=\"return false;\" unselectable=\"on\">";
			$header .= "<tr>";
			$header .= "<td width=\"250\" type=\"ro\">Arquivo</td>";
			$header .= "<td width=\"130\" type=\"ro\">Nº Registro</td>";
			$header .= "<td width=\"170\" type=\"ro\">Nº Documento</td>";
			$header .= "<td width=\"70\" type=\"ro\">Autor</td>";
			$header .= "<td width=\"230\" type=\"ro\">Título</td>";
			$header .= "<td width=\"50\" type=\"ro\">OS</td>";
			$header .= "<td width=\"70\" type=\"ro\">Disciplina</td>";
			$header .= "<td width=\"100\" type=\"ro\">Palavras chave</td>";
			$header .= "<td width=\"50\" type=\"ro\">Origem</td>";
			$header .= "<td width=\"50\" type=\"ro\">Abrir</td>";	
			$header .= "</tr>";
			
			$footer = "</table>";
		
			while($reg_busca = mysqli_fetch_assoc($cont_busca))
			{
				
				$os = sprintf("%05d",$reg_busca["os"]);
				
				//Monta a pasta
				//ex: ATAS/MEC
				if($reg_busca["grava_disciplina"]==1)
				{
					$disciplina = $reg_busca["abreviacao"]."/";	
				}
				else
				{
					$disciplina = "";	
				}
				
				//monta diretorio base
				$diretorio = DOCUMENTOS_GED . $reg_busca["abreviacao_GED"] . "/" . $reg_busca["os"] . "-" .$reg_busca["descricao"] . "/" . $reg_busca["os"] . REFERENCIAS . $reg_busca["pasta_base"] . "/".$disciplina;

		
				$array_extensao = explode(".",$reg_busca["arquivo"]);
				
				//Pega a extensão do arquivo
				$extensao = $array_extensao[count($array_extensao)-1];
				
				//Pega a imagem relativa a extensão
				$imagem = retornaImagem($extensao);

				$imagem_vis = "<img src=\"../images/buttons_action/procurar.png\" title=\"Abrir\" style=\"cursor:pointer;\" onclick=\"xajax_abrir('" . $diretorio.$reg_busca["arquivo"] . "');\">";

				$conteudo .= "<tr>";
				$conteudo .= "<td>".$imagem . " " . basename($reg_busca["arquivo"])."</td>";
				$conteudo .= "<td>" . $reg_busca["numero_registro"] . "</td>";	
				$conteudo .= "<td>" . $reg_busca["numero_documento"] . "</td>";
				$conteudo .= "<td>" . $nome_funcionario[$reg_busca["id_autor"]] . "</td>";		
				$conteudo .= "<td>" . $reg_busca["titulo"] . "</td>";	
				$conteudo .= "<td>" . $reg_busca["os"] . "</td>";
				$conteudo .= "<td>" . $reg_busca["setor"] . "</td>";
				$conteudo .= "<td>" . $reg_busca["palavras_chave"] . "</td>";	
				$conteudo .= "<td>" . $reg_busca["origem"] . "</td>";	
				$conteudo .= "<td>" . $imagem_vis . "</td>";
				$conteudo .= "</tr>";	

			}
			
			$resposta->addAssign("div_busca_resultados","innerHTML", $header.$conteudo.$footer);

			$resposta->addScript("grid('tbl2',true,'350');");		
				
			//Correção bug
			$resposta->addScript("xajax.('div_busca_resultados').style.width='1150px'; ");		
		
		break;
	
	}
	
	return $resposta;
}

//visualizar arquivos
function visualizar($caminho)
{
	$resposta = new xajaxResponse();
	
	if(is_file($caminho))
	{
		$nome_arquivo = basename($caminho);
		
		//Pega o nome do arquivo upload sem a extensão
		$nome_arquivo_format = substr($nome_arquivo,0,strrpos($nome_arquivo,"."));
		
		//Pega a extensão do arquivo upload sem o nome
		$nome_extensao_format = substr($nome_arquivo,strrpos($nome_arquivo,"."),(strlen($nome_arquivo)-strrpos($nome_arquivo,".")));

		switch($nome_extensao_format)
		{
			//AutoCAD
			case ".dwg":
						
				$array_dir = explode("/",$caminho);
				
				array_pop($array_dir);
				
				$diretorio_original = "documentos_dxf";
	
				//Remove o   temporário
				full_rmdir($diretorio_original . DIRETORIO_VERSOES."/tmp/");
			
				$nr_rnd = rand(100000,999999);
			
				//Se não existir o   de versões, cria
				if(!is_dir($diretorio_original . DIRETORIO_VERSOES))
				{
					mkdir($diretorio_original . DIRETORIO_VERSOES);
				}
				
				//Se não existir o   temporário, cria
				if(!is_dir($diretorio_original . DIRETORIO_VERSOES."/tmp/"))
				{
					mkdir($diretorio_original . DIRETORIO_VERSOES."/tmp/");
				}
				
				//Cria numeração  randômica para nomear de cache temporário
				$nr_rnd = rand(100000,999999);
				
				//Se não existir o   de cache temporário, cria
				if(!is_dir($diretorio_original . DIRETORIO_VERSOES."/tmp/" . $nr_rnd . "/"))
				{
					mkdir($diretorio_original . DIRETORIO_VERSOES."/tmp/" . $nr_rnd . "/");
				}
				
				//Se o arquivo de cache temporário existir, remove
				if(is_file($diretorio_original . DIRETORIO_VERSOES. "/tmp/" . $nr_rnd . "/" . $nome_arquivo))
				{
					unlink($diretorio_original . DIRETORIO_VERSOES. "/tmp/" . $nr_rnd . "/" . $nome_arquivo);
				}
		
				//Copia o arquivo para o dir temporário
				$copia_arquivo = copy($caminho,$diretorio_original . DIRETORIO_VERSOES. "/tmp/" . $nr_rnd . "/" . $nome_arquivo);
				
				//Se o arquivo de cache temporário existir, remove
				if(is_file($diretorio_original .DIRETORIO_VERSOES. "/tmp/" . $nr_rnd . "/" . $nome_arquivo_format . ".dxf"))
				{
					unlink($diretorio_original . DIRETORIO_VERSOES."/tmp/" . $nr_rnd . "/" . $nome_arquivo_format . ".dxf");
				}
				
				$sys_comando = shell_exec("/usr/bin/dwg2dxf " . escapeshellarg($dir_raiz . $diretorio_original . DIRETORIO_VERSOES."/tmp/" . $nr_rnd . "/" . $nome_arquivo));			
			
				sleep(1);
		
				//Funcionou - 26/03
				$resposta->addScript("popupVisualizarDWG('" . $diretorio_original .DIRETORIO_VERSOES. "/tmp/" . $nr_rnd . "/" . $nome_arquivo_format . ".dxf'); ");
		
			break;
	
			case ".xls":
			case ".xlsx":
				$resposta->addAlert("Em desenvolvimento.");

				//$resposta->addScript("popupVisualizarDOCXLS('teste');");
			
			break;
			
			case ".doc":
			case ".docx":
				$resposta->addAlert("Em desenvolvimento.");
				
			break;
			
			default:
				$resposta->addAlert("Não implementado para esse tipo de arquivo.");
			break;
		
		}

	}

	return $resposta;
}

//Preenche comentários
function preencheComentarios($id_ged_versao)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados();

	$sql = "SELECT *, numeros_interno.sequencia FROM ".DATABASE.".ged_comentarios, ".DATABASE.".ged_versoes, ".DATABASE.".ged_arquivos, ".DATABASE.".numeros_interno, ".DATABASE.".numero_cliente, ".DATABASE.".OS, ".DATABASE.".setores,  ";
	$sql .= "".DATABASE.".ged_pacotes INNER JOIN ".DATABASE.".grd ON (ged_pacotes.id_ged_pacote = grd.id_ged_pacote) ";
	$sql .= "WHERE ged_comentarios.id_ged_versao = ged_versoes.id_ged_versao ";
	$sql .= "AND ged_versoes.id_ged_pacote = ged_pacotes.id_ged_pacote ";
	$sql .= "AND ged_versoes.id_ged_arquivo = ged_arquivos.id_ged_arquivo ";
	$sql .= "AND ged_arquivos.id_numero_interno = numeros_interno.id_numero_interno ";	
	$sql .= "AND numeros_interno.id_numcliente = numero_cliente.id_numcliente ";
	$sql .= "AND numeros_interno.id_disciplina = setores.id_setor ";
	$sql .= "AND numeros_interno.id_os = OS.id_os ";
	$sql .= "AND ged_pacotes.id_ged_pacote = ged_versoes.id_ged_pacote ";
	$sql .= "AND ged_pacotes.id_ged_pacote = grd.id_ged_pacote ";
	$sql .= "AND ged_versoes.id_ged_versao = '" . $id_ged_versao . "' ";
	
	$cont_cabecalho_coment = $db->select($sql,'MYSQL');

	$reg_cabecalho_coment = mysqli_fetch_assoc($cont_cabecalho_coment);
	
	$conteudo_cabecalho = "<table border=\"0\" width=\"100%\"><tr>";
	$conteudo_cabecalho .= "<td><div class=\"fonte_descricao_campos\">Nº INT</div></td>";
	$conteudo_cabecalho .= "<td><div class=\"fonte_descricao_campos\">Nº Cliente</div></td>";	
	$conteudo_cabecalho .= "<td><div class=\"fonte_descricao_campos\">GRD</div></td>";		
	$conteudo_cabecalho .= "<td><div class=\"fonte_descricao_campos\">Rev. D</div></td>";
	$conteudo_cabecalho .= "<td><div class=\"fonte_descricao_campos\">Rev. C</div></td>";		
	$conteudo_cabecalho .= "</tr><tr>";
	$conteudo_cabecalho .= "<td><div class=\"fonte_11\">INT-" . sprintf("%05d",$reg_cabecalho_coment["os"]) . "-" . $reg_cabecalho_coment["sigla"] . "-" . $reg_cabecalho_coment["sequencia"] . "</div></td>";		
	$conteudo_cabecalho .= "<td><div class=\"fonte_11\">" . $reg_cabecalho_coment["numero_cliente"] . "</div></td>";		
	$conteudo_cabecalho .= "<td><div class=\"fonte_11\">" . $reg_cabecalho_coment["os"] . "-" . sprintf("%03d",$reg_cabecalho_coment["numero_pacote"]) . "</div></td>";		
	$conteudo_cabecalho .= "<td><div class=\"fonte_11\">" . $reg_cabecalho_coment["revisao_interna"] . "</div></td>";
	$conteudo_cabecalho .= "<td><div class=\"fonte_11\">" . $reg_cabecalho_coment["revisao_cliente"] . "</div></td>";
	$conteudo_cabecalho .= "</tr><tr><td> </td></tr></table>";	
	
	$resposta->addAssign("div_cabecalho_comentarios","innerHTML",$conteudo_cabecalho);

	$sql_coment = "SELECT *, ged_versoes.strarquivo AS ver_strarquivo, ged_comentarios.strarquivo AS cmt_strarquivo FROM ".DATABASE.".ged_versoes, ".DATABASE.".ged_comentarios ";
	$sql_coment .= "WHERE ged_versoes.id_ged_versao = ged_comentarios.id_ged_versao ";
	$sql_coment .= "AND ged_comentarios.id_ged_versao = '" . $id_ged_versao . "' ";

	$cont_coment = $db->select($sql_coment,'MYSQL');
	
	$conteudo_coment = "";
	
	$str_arquivo = "";	
	
	while($reg_coment = mysqli_fetch_assoc($cont_coment))
	{
		if($i_coment%2)
		{
			// escuro
			$cor_coment = "#F0F0F0";		
		}
		else
		{
			//claro		
			$cor_coment = "#FFFFFF";
		}
		
		$i_coment++;		
		
		$caminho = DOCUMENTOS_GED . $reg_coment["base"] . "/" . $reg_coment["os"] . "/" .  substr($reg_coment["os"],0,4) . DISCIPLINAS . $reg_coment["disciplina"] . "/" . $reg_coment["atividade"] . "/" . $reg_coment["sequencial"] . DIRETORIO_COMENTARIOS . $reg_coment["cmt_strarquivo"];
		
		if($_SESSION["id_funcionario"]==6)
		{
			$conteudo_coment .= "<div title=\"".$caminho.".".sprintf("%03d",$reg_coment["sequencia_doc"])."\" id=\"". $reg_coment["id_ged_comentario"]."\"  class=\"cell\" style=\"background-color:". $cor_coment ."; width:100%;\" onMouseOver=\"setPointerDiv(this, 1, 'over', '". $cor_coment ."', '#BECCD9', '#FFCC99');\" onMouseOut=\"setPointerDiv(this, 1, 'out', '". $cor_coment . "', '#BECCD9', '#FFCC99');\">";
		}
		else
		{
			$conteudo_coment .= "<div id=\"". $reg_coment["id_ged_comentario"]."\"  class=\"cell\" style=\"background-color:". $cor_coment ."; width:100%;\" onMouseOver=\"setPointerDiv(this, 1, 'over', '". $cor_coment ."', '#BECCD9', '#FFCC99');\" onMouseOut=\"setPointerDiv(this, 1, 'out', '". $cor_coment . "', '#BECCD9', '#FFCC99');\">";
		}
		
		$conteudo_coment .= "<div id=\"coment_" . $reg_coment["id_ged_comentario"] . "\" class=\"tabela_celulas\" style=\"width:90%;\">" . $reg_coment["cmt_strarquivo"]. "</div>";
		$conteudo_coment .= "<div id=\"abrir_coment_" . $reg_coment["id_ged_comentario"] . "\" class=\"tabela_celulas\" style=\"width:1%;\"><img src=\"../images/buttons_action/bt_busca.gif\" style=\"cursor:pointer;\" alt=\"Abrir arquivo de comentário\" onclick=\"xajax_abrir('" . $caminho . "." . sprintf("%03d",$reg_coment["sequencia_doc"]) . "', '" . $caminho . "');\"></div>";
		
		$conteudo_coment .= "</div>";
		
	}

	$resposta->addAssign("div_comentarios_existentes","innerHTML",$conteudo_coment);

	return $resposta;
}

//filtra OS
function filtra_os($os)
{
	$resposta = new xajaxResponse();

	$db = new banco_dados();
		
	$resposta->addAssign("div_arquivos","innerHTML","");
	
	$sql = "SELECT abreviacao_GED, OS, descricao FROM ".DATABASE.".empresas, ".DATABASE.".OS ";
	$sql .= "WHERE OS.id_os = '".$os."' ";
	$sql .= "AND OS.id_empresa = empresas.id_empresa ";
	
	$cont = $db->select($sql,'MYSQL');

	$reg = mysqli_fetch_assoc($cont);
	
	$resposta->addScript("xajax_preenchePastas('".DOCUMENTOS_GED.$reg["abreviacao_GED"]."/".$reg["os"]."-".$reg["descricao"]."');");
	
	return $resposta;
}

//Preenche titulos
function preencheTitulos($id_ged_arquivo)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados();

	$sql_complemento = "SELECT * FROM ".DATABASE.".numeros_interno, ".DATABASE.".numero_cliente, ".DATABASE.".ged_arquivos, ".DATABASE.".solicitacao_documentos_detalhes, ".DATABASE.".OS, ".DATABASE.".setores ";
	$sql_complemento .= "WHERE OS.id_os = numeros_interno.id_os ";
	$sql_complemento .= "AND numeros_interno.id_numcliente = numero_cliente.id_numcliente ";
	$sql_complemento .= "AND numeros_interno.id_numero_interno = solicitacao_documentos_detalhes.id_numero_interno ";
	$sql_complemento .= "AND numeros_interno.id_numero_interno = ged_arquivos.id_numero_interno ";
	$sql_complemento .= "AND numeros_interno.id_disciplina = setores.id_setor ";
	$sql_complemento .= "AND ged_arquivos.id_ged_arquivo = '" . $id_ged_arquivo . "' ";
	
	$cont_complemento = $db->select($sql_complemento,'MYSQL');
 
	$reg_complemento = mysqli_fetch_assoc($cont_complemento);

	$conteudo_compl = "";
	$conteudo_compl .= "<form action=\"ged.php\" method=\"post\" name=\"frm_titulos\">";
	$conteudo_compl .= "<table border=\"0\" width=\"100%\">";
	$conteudo_compl .= "<tr>";
	$conteudo_compl .= "<td><label class=\"label_descricao_campos\"><strong>Alteração de Títulos</strong></label></td>";
	$conteudo_compl .= "</tr>";
	$conteudo_compl .= "<tr>";
	$conteudo_compl .= "<td class=\"td_sp\" width=\"5%\"><label class=\"label_descricao_campos\">Nº INT</label><BR><input type=\"text\" name=\"n_dvm\" class=\"caixa\" value=\"" . "INT-" . sprintf("%05d",$reg_complemento["os"]) . "-" . $reg_complemento["sigla"] . "-" .$reg_complemento["sequencia"] . "\" size=\"40\" readonly=\"readonly\"></td>";
	$conteudo_compl .= "<td class=\"td_sp\" width=\"5%\"><label class=\"label_descricao_campos\">Nº Cliente</label><BR><input type=\"text\" name=\"numero_cliente\" id=\"numero_cliente\" class=\"caixa\" value=\"" . $reg_complemento["numero_cliente"] . "\" size=\"40\"></td>";
	$conteudo_compl .= "</tr></table>";

	$conteudo_compl .= "<table border=\"0\" width=\"100%\">";
	$conteudo_compl .= "<tr>";
	$conteudo_compl .= "<td class=\"td_sp\" width=\"5%\"><label class=\"label_descricao_campos\">Título 1</label><BR><input type=\"text\" name=\"tag\" id=\"tag\" class=\"caixa\" value=\"" . $reg_complemento["tag"] . "\" size=\"40\"></td>";
	$conteudo_compl .= "<td class=\"td_sp\" width=\"5%\"><label class=\"label_descricao_campos\">Título 2</label><BR><input type=\"text\" name=\"tag2\" id=\"tag2\" class=\"caixa\" value=\"" . $reg_complemento["tag2"] . "\" size=\"40\"></td></tr>";
	$conteudo_compl .= "<tr><td class=\"td_sp\" width=\"5%\"><label class=\"label_descricao_campos\">Título 3</label><BR><input type=\"text\" name=\"tag3\" id=\"tag3\" class=\"caixa\" value=\"" . $reg_complemento["tag3"] . "\" size=\"40\"></td>";
	$conteudo_compl .= "<td class=\"td_sp\" width=\"5%\"><label class=\"label_descricao_campos\">Título 4</label><BR><input type=\"text\" name=\"tag4\" id=\"tag4\" class=\"caixa\" value=\"" . $reg_complemento["tag4"] . "\" size=\"40\"></td>";
	$conteudo_compl .= "<td class=\"td_sp\" width=\"90%\"> </td>";
	$conteudo_compl .= "</tr>";
	$conteudo_compl .= "</table>";
	$conteudo_compl .= "<table border=\"0\" width=\"100%\">";
	$conteudo_compl .= "<tr>";
	$conteudo_compl .= "<tr><td><input type=\"button\" class=\"fonte_botao\" value=\"Alterar Titulos\" onclick=\"xajax_alterarTitulos(xajax.getFormValues('frm_titulos'));\" ></td>";
	$conteudo_compl .= "<td><input type=\"hidden\" name=\"id_ged_arquivo\" id=\"id_ged_arquivo\" value=\"".$id_ged_arquivo."\"><input type=\"button\" value=\"Voltar\" onclick=\"divPopupInst.destroi();\" class=\"fonte_botao\"></td>";
	$conteudo_compl .= "</tr></table></form>";

	$resposta->addAssign("div_tit","innerHTML",$conteudo_compl);

	$resposta->addAssign("id_ged_arquivo","value",$id_ged_arquivo);
	
	return $resposta;
}

$xajax->registerFunction("start");
$xajax->registerFunction("preenchePastas");
$xajax->registerFunction("preencheArquivos");
$xajax->registerFunction("dadosArquivo");
$xajax->registerFunction("preenchedocumentos");
$xajax->registerFunction("preencheNRDocumentos");
$xajax->registerFunction("preenchePropriedades");
$xajax->registerFunction("preenchePropriedadesRef");
$xajax->registerFunction("abrir");
$xajax->registerFunction("buscaArquivos");
$xajax->registerFunction("buscaArquivosAvancada");
$xajax->registerFunction("preencheArquivosSol");
$xajax->registerFunction("preencheBuscaAvancada");
$xajax->registerFunction("preenche_os_BuscaAvancada");
$xajax->registerFunction("preenchedisciplina");
$xajax->registerFunction("visualizar");
$xajax->registerFunction("preencheComentarios");
$xajax->registerFunction("filtra_os");
$xajax->registerFunction("preencheTitulos");
$xajax->registerFunction("preencheNRDocumentos_grid");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript('../includes/xajax'));

?>

<style>

body {
	scrollbar-face-color: #F5F5F5; 
	scrollbar-shadow-color: #2D2D73; 
    scrollbar-highlight-color: #2D2D73; 
	scrollbar-3dlight-color: #F5F5F5; 
    scrollbar-darkshadow-color: #F5F5F5; 
	scrollbar-track-color: #FFFFFF; 
    scrollbar-arrow-color: #2D2D73;
}

.standartTreeRow{

font-size:9px;
font-family:Arial, Helvetica, sans-serif;

}

.selectedTreeRow{
font-size:9px;
font-family:Arial, Helvetica, sans-serif;

}

</style>

<!-- Javascript para validação de dados -->
<script type="text/javascript" src="../includes/validacao.js"></script>

<script language="javascript" src="ged_clientes.js"></script>

<script type="text/javascript" src="../includes/dhtmlx/dhtmlxTree/codebase/dhtmlxcommon.js"></script>
<script type="text/javascript" src="../includes/dhtmlx/dhtmlxTree/codebase/dhtmlxtree.js"></script>
<script type="text/javascript" src="../includes/dhtmlx/dhtmlxTree/codebase/ext/dhtmlxtree_start.js"></script>

<script type="text/javascript" src="../includes/dhtmlx/dhtmlxTabbar/codebase/dhtmlxtabbar.js"></script>

<!-- Grid -->
<script type="text/javascript" src="../includes/dhtmlx/dhtmlxGrid/codebase/dhtmlxcommon.js"></script>
<script type="text/javascript" src="../includes/dhtmlx/dhtmlxGrid/codebase/dhtmlxgrid.js"></script>		
<script type="text/javascript" src="../includes/dhtmlx/dhtmlxGrid/codebase/dhtmlxgridcell.js"></script>
<script type="text/javascript" src="../includes/dhtmlx/dhtmlxGrid/codebase/ext/dhtmlxgrid_start.js"></script>

<script>

var myTree;

function grid(tabela, autoh, height)
{		
	var mygrid = new dhtmlXGridFromTable(tabela);
	mygrid.imgURL = "../includes/dhtmlx_3_6/dhtmlxGrid/codebase/imgs/";
	mygrid.enableAutoHeight(autoh,height);
	mygrid.enableRowsHover(true,'cor_mouseover');
	//mygrid.setSkin("modern");
	mygrid.setSkin("dhx_skyblue");	
}

function tonclick(id)
{	
	xajax_preencheArquivos(xajax.getFormValues('frm_ged'),myTree.getUserData(id, "value"),id);
}

function htree(id_tree)
{	
	myTree = dhtmlXTreeFromHTML(id_tree);
	//Seta espaçamento no final do TreeGrid
	document.getElementById('tree1').childNodes[0].childNodes[0].style.marginBottom = '100px';
	myTree.setSkin("modern");
	mygrid.setSkin("dhx_skyblue");	
}

function seleciona_tree(id)
{
	myTree.openItem(id);
}

function popupMenu(operacao,event,id,caminho)
{
	RCmenuInst = new RCmenu();
	
	var status_chkin  = 0;
	var status_chkout = 0;
	var status_zip = 0;
	var status_desbloquear = 1;
	var status_propriedades = 1;
	var status_excluir = 1;
	var status_nova_versao = 0;
	
	switch(operacao)
	{
		case "1":
		//Check-in / Sem ZIP
		status_chkin = 1;
		status_chkout = 0;
		status_zip = 0;
		status_desbloquear = 0;
		status_propriedades = 1;
		status_excluir = 1;
		status_nova_versao = 0;
		break;
		
		case "2":
		//Check-out / Sem ZIP
		status_chkin = 0;
		status_chkout = 1;
		status_zip = 0;
		status_desbloquear = 1;
		status_propriedades = 1;
		status_excluir = 1;
		status_nova_versao = 0;
		break;
		
		case "3":
		//Check-in / Com ZIP
		status_chkin = 1;
		status_chkout = 0;
		status_zip = 1;
		status_desbloquear = 1;
		status_propriedades = 1;
		status_excluir = 1;
		status_nova_versao = 0;
		break;

		case "4":
		//Check-out / Com ZIP
		status_chkin = 0;
		status_chkout = 1;
		status_zip = 1;
		status_desbloquear = 1;
		status_propriedades = 1;
		status_excluir = 1;
		status_nova_versao = 0;
		break;
		
		case "5":
		//sem Check-out / Com ZIP
		status_chkin = 0;
		status_chkout = 0;
		status_zip = 1;
		status_desbloquear = 0;
		status_propriedades = 1;
		status_excluir = 0;
		status_nova_versao = 0;
		break;
		
		case "6":
		//sem Check-in / sem ZIP
		status_chkin = 0;
		status_chkout = 0;
		status_zip = 0;
		status_desbloquear = 0;
		status_propriedades = 1;
		status_excluir = 0;
		status_nova_versao = 0;
		break;
		
		case "7":
		//sem Check-in / sem ZIP
		status_chkin = 0;
		status_chkout = 0;
		status_zip = 0;
		status_desbloquear = 0;
		status_propriedades = 1;
		status_excluir = 0;
		status_nova_versao = 1;
		break;
		
	}
	
	if(caminho)
	{	
		//Forma os itens do menu	
		var array_itens = new Array();	
		
		//referencias
		if(operacao==7)
		{
			array_itens[array_itens.length] = ['Propriedades', function () { popupPropriedadesRef(id); }, status_propriedades,1];
		}
		else
		{

			if(operacao==5 || operacao==6)
			{
				array_itens[array_itens.length] = ['Abrir', function () { xajax_abrir(caminho); },1,0];

				array_itens[array_itens.length] = ['Propriedades', function () { popupPropriedades(id); }, status_propriedades,1];
			}
			else
			{
				if(operacao==8)
				{
					
					//array_itens[array_itens.length] = ['Desbloquear',function () { if(confirm('Confirma o desbloqueio do arquivo?')){xajax_desbloquear(id); } },1,0];
					array_itens[array_itens.length] = ['Abrir', function () { xajax_abrir(caminho); },1,0];
					array_itens[array_itens.length] = ['Propriedades', function () { popupPropriedades(id); }, 1,1];
				}
				else
				{
					array_itens[array_itens.length] = ['Abrir', function () { xajax_abrir(caminho); },1,0];
					array_itens[array_itens.length] = ['Propriedades', function () { popupPropriedades(id); }, status_propriedades,1];
				}
				
			}

		}
	}	
	
	if(event.type=="click")
	{
		RCmenuInst.destroi();	
	}
	else
	{
		RCmenuInst.insere(event.clientX,event.clientY, array_itens);
	}
}

function open_doc(dir)
{
	window.open("documento.php?documento="+dir,"_blank");
}

function dv_info(status)
{
	//mostra/esconde div de info
	//1 - mostra / 0 - esconde
	var div_arq = document.getElementById('div_arquivos');
	
	var div_inf = document.getElementById('div_info');
	
	if(status=='1')
	{
		div_arq.style.height = '300px';
		
		div_inf.style.height = '250px';
		
		div_inf.style.visibility = 'visible';

	}
	else
	{
		div_arq.style.height = '400px';
		
		div_inf.style.height = '1px';
		
		div_inf.style.visibility = 'hidden';
		
		div_inf.innerHTML = '';	
	}
}

function estado_inicial(id_os)
{
	document.getElementById('disciplina').selectedIndex = 0;	

	document.getElementById('btn_lat_buscar').disabled = false;

	document.getElementById('disciplina').focus();
	
	xajax_filtra_os(id_os); 
	
	xajax_preenchedisciplina(id_os,'1');
		
	return true;	
}

function disciplinas_inicial(id_disciplina)
{
	xajax_preenchedocumentos(id_disciplina,document.getElementById('id_os').value);
	
	document.getElementById('id_atividade').focus();
	
	return true;
}


</script>

<?php

$conf = new configs();

$db = new banco_dados;

$sql = "SELECT * FROM ".DATABASE.".OS, ".DATABASE.".contatos, ".DATABASE.".empresas ";
$sql .= "WHERE OS.id_empresa = empresas.id_empresa ";
$sql .= "AND empresas.id_empresa = contatos.id_empresa ";
$sql .= "AND contatos.id_contato = '".$_SESSION["id_contato"]."' ";
$sql .= "GROUP BY OS.id_os ";
$sql .= "ORDER BY OS ";

//FAZ O SELECT
$reg = $db->select($sql,'MYSQL');

//se der mensagem de erro, mostra
if($db->erro!='')
{
	die($db->erro);
}

while ($regs = mysqli_fetch_assoc($reg))
{
	$os = sprintf("%05d",$regs["os"]);
	
	$array_os_values[] = $regs["id_os"];
	$array_os_output[] = $os . " - " . substr($regs["descricao"],0,40);	
}


$smarty->assign("option_os_values",$array_os_values);
$smarty->assign("option_os_output",$array_os_output);

$smarty->assign("revisao_documento","V1");

$campo[1] = "GERENCIADOR DE DOCUMENTOS";

$smarty->assign("campo",$campo);

$smarty->assign("classe",CSS_FILE);

$smarty->display('ged_clientes.tpl');
?>