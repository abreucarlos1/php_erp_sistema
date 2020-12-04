<?php
/*
		Formulário de GRD
		
		Criado por Carlos Abreu / Otávio Pamplona  
		
		local/Nome do arquivo:
		../arquivotec/ged_pacotes.php		
		
		data de criação: 28/08/2007
		
		Versão 0 --> VERSÃO INICIAL 28/08/2007
		Versão 1 --> Melhorias gerais 13/12/2007 
		Versão 2 --> Impl. template Smarty, classe do banco, atualização do layout (24/07/2008)
		Versão 3 --> Removido o tipo de Cópia no formulário
					 Não desbloqueio dos arquivos que estão certificados através do checkbox
		Versão 4 --> Acrescentado função bloquear arquivos - Carlos Abreu - 21/08/2012
		Versão 5 --> Alteração em consultas - Carlos Abreu - 05/08/2013
		Versão 6 --> Troca do diretórios do GED
		Versao 7 --> Inclusão da aba Desbloqueios - 10/02/2014 - Carlos Abreu
 		Versão 8 --> Inclusão de verificação de duplicidade em número clientes - solicitado por George - Carlos Abreu - 26/06/2014
		Versão 9 --> Alteração layout - 18/09/2014 - Carlos
		Versão 10 --> Alteração dos campos versao_documento/revisao_documento - 09/10/2014 - Carlos Abreu
		Versão 11 --> Alterado para gravar GRD na pasta de Projetos - 28/09/2015 - Carlos Abreu
		Versão 12 --> Alterado biblioteca pclzip para zipArchive (Nativa PHP) - 22/10/2015 - Carlos Abreu
		Versão 13 --> Alterado a forma de desbloqueios - 14/04/2016 - Carlos Abreu
		Versão 14 --> Geração dos arquivos para envio de e-mail - 26/01/2017 - Carlos Abreu
		Versão 15 --> Alteração layout - Carlos Abreu - 22/03/2017
		Versão 16 --> unificação das tabelas numero_cliente e numeros_interno - 10/05/2017 - Carlos Abreu
		Versão 17 --> Inclusão dos campos reg_del nas consultas - 16/11/2017 - Carlos Abreu
*/


require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));

require_once(INCLUDE_DIR."include_form.inc.php");

require_once(implode(DIRECTORY_SEPARATOR,array('relatorios','rel_ged_grd.php')));

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(27))
{
	nao_permitido();
}

function geraRelatorio($id_grd)
{
	//Cria instância GRD
	$grd = new GRD();

	//Seta o id
	$grd->id_grd = $id_grd;
	
	//Gera o relatório em arquivo
	$grd->saida('GRD_');	
	
	//Retorna o número da GRD
	return $grd->numero_grd;
}

//funcao incluida para a verificação de numero cliente duplicado
//26/06/2014
function verifica_numcliente($numero_cliente, $id_os)
{
	$db = new banco_dados();
	
	$array_numcliente = NULL;
	
	//Pega o NumCli do documento informado
	$sql = "SELECT * FROM ".DATABASE.".numeros_interno, ".DATABASE.".ged_arquivos ";
	$sql .= "WHERE numeros_interno.reg_del = 0 ";
	$sql .= "AND ged_arquivos.reg_del = 0 ";
	$sql .= "AND numeros_interno.numero_cliente LIKE '".trim($numero_cliente)."%' ";
	$sql .= "AND numeros_interno.id_numero_interno = ged_arquivos.id_numero_interno ";
	$sql .= "AND numeros_interno.id_os = '".$id_os."' ";
	
	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		die($db->erro);
	}
	else
	{	
		foreach($db->array_select as $regs)
		{
			$array_numcliente[] = $regs["descricao"];
		}
	}

	return $array_numcliente;
}

//Criado em 26/04/2016
//Carlos Abreu
//Verifica a tabela de solicitação de desbloqueios
//status = 0 -- aprova / 1 - reprova
function verifica_desbloqueio($id_ged_versao, $status = 0)
{
	$db = new banco_dados();
	
	$erro = false;
	
	$array_result = NULL;
	
	//verifica se existe solicitação de desbloqueio
	$sql = "SELECT * FROM ".DATABASE.".ged_desbloqueios ";
	$sql .= "WHERE ged_desbloqueios.reg_del = 0 ";
	$sql .= "AND ged_desbloqueios.id_ged_versao = '" . $id_ged_versao . "' ";
	
	$db->select($sql,'MYSQL',true);
	
	if($db->erro!='')
	{
		$erro = true;
	}
	else
	{					
		$num_regs = $db->numero_registros;
		
		$regs = $db->array_select[0];
		
		//se houver solicitacao de desbloqueio
		if($num_regs > 0)
		{
			//pega o diretorio do arquivo
			$sql = "SELECT * FROM ".DATABASE.".numeros_interno, ".DATABASE.".setores, ".DATABASE.".ged_arquivos, ".DATABASE.".solicitacao_documentos_detalhes, ".DATABASE.".ged_versoes, ".DATABASE.".ordem_servico ";
			$sql .= "WHERE numeros_interno.reg_del = 0 ";
			$sql .= "AND solicitacao_documentos_detalhes.reg_del = 0 ";
			$sql .= "AND ged_arquivos.reg_del = 0 ";
			$sql .= "AND ged_versoes.reg_del = 0 ";
			$sql .= "AND ordem_servico.reg_del = 0 ";
			$sql .= "AND setores.reg_del = 0 ";
			$sql .= "AND ordem_servico.id_os = numeros_interno.id_os ";
			$sql .= "AND numeros_interno.id_numero_interno = solicitacao_documentos_detalhes.id_numero_interno ";
			$sql .= "AND numeros_interno.id_numero_interno = ged_arquivos.id_numero_interno ";
			$sql .= "AND numeros_interno.id_disciplina = setores.id_setor ";
			$sql .= "AND ged_arquivos.id_ged_arquivo = ged_versoes.id_ged_arquivo ";
			$sql .= "AND ged_versoes.id_ged_versao = '" . $id_ged_versao . "' ";
			
			$db->select($sql,'MYSQL',true);
			
			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}
			else
			{
				$reg_pacote = $db->array_select[0];
			}
			
			//se aprovado
			if(!$status)
			{
				//APROVA
				//insere na tabela comentários o motivo
				$isql = "INSERT INTO ".DATABASE.".ged_comentarios (id_ged_versao, comentario, id_funcionario) VALUES(";
				$isql .= "'" . $id_ged_versao . "', ";
				$isql .= "'" . $regs["motivo_desbloqueio"] . "', ";
				$isql .= "'" . $regs["id_funcionario_solicitante"] . "') ";
				
				$db->insert($isql,'MYSQL');
				
				$id_comentario = $db->insert_id;						
										
				//se tiver arquivo de desbloqueio, move para os comentários
				if($regs["strarquivo"]!='')
				{
					$diretorio_origem = DOCUMENTOS_GED . $reg_pacote["base"] . "/" . $reg_pacote["os"] . "/" . substr($reg_pacote["os"],0,4) . DISCIPLINAS . $reg_pacote["disciplina"] . "/" . $reg_pacote["atividade"] . "/" . $reg_pacote["sequencial"].DIRETORIO_DESBLOQUEIOS;
					
					$diretorio_destino = DOCUMENTOS_GED . $reg_pacote["base"] . "/" . $reg_pacote["os"] . "/" . substr($reg_pacote["os"],0,4) . DISCIPLINAS . $reg_pacote["disciplina"] . "/" . $reg_pacote["atividade"] . "/" . $reg_pacote["sequencial"].DIRETORIO_COMENTARIOS;
					
					$arquivo_origem = $diretorio_origem."/".$regs["strarquivo"];
					
					$nome_arquivo = $regs["nome_arquivo"];
					
					$array_flm = explode(".",$nome_arquivo);
					
					$extensao = $array_flm[count($array_flm)-1];
		
					$filename = preg_replace('/\.[^.]*$/', '', $nome_arquivo);
					
					$novo_nome_arquivo = $filename.'_'.sprintf("%05d",$id_comentario).'.'.$extensao;
					
					$arquivo_destino = $diretorio_destino."/".$novo_nome_arquivo;
					
					//Se ainda não existir a pasta de comentários no diretório do arquivo, cria
					if(!is_dir($diretorio_destino))
					{
						mkdir($diretorio_destino);
					}
					
					//Verifica se o arquivo já existe
					if(!is_file($arquivo_destino))
					{																
						//Move o arquivo para o diretório de comentários
						$move_comentario = rename($arquivo_origem,$arquivo_destino);
						
						//se movido com sucesso, atualiza ged_comentarios
						if($move_comentario)
						{
							$usql = "UPDATE ".DATABASE.".ged_comentarios SET ";
							$usql .= "ged_comentarios.nome_arquivo = '".$nome_arquivo."', ";
							$usql .= "ged_comentarios.strarquivo = '".$novo_nome_arquivo."' ";
							$usql .= "WHERE ged_comentarios.id_ged_comentario = '".$id_comentario."' ";
							$usql .= "AND ged_comentarios.reg_del = 0 ";
							
							$db->update($usql,'MYSQL');
		
							if($db->erro!='')
							{
								$erro = true;
							}
							
						}
					}														
				}						
				
				//exclui o ged_desbloqueio						
				$usql = "UPDATE ".DATABASE.".ged_desbloqueios SET ";
				$usql .= "ged_desbloqueios.reg_del = 1, ";
				$usql .= "ged_desbloqueios.reg_who = '".$_SESSION["id_funcionario"]."', ";
				$usql .= "ged_desbloqueios.data_del = '".date('Y-m-d')."' ";
				$usql .= "WHERE ged_desbloqueios.id_ged_desbloqueio = '" . $regs["id_ged_desbloqueio"] . "' ";
				$usql .= "AND ged_desbloqueios.reg_del = 0 ";
	
				$db->update($usql,'MYSQL');
	
				if($db->erro!='')
				{
					$erro = true;
				}
				
				//atualiza o status da revisao_documento
				$usql = "UPDATE ".DATABASE.".ged_versoes SET ";
				$usql .= "ged_versoes.status_devolucao = '".$regs["status_devolucao"]."', ";
				$usql .= "ged_versoes.data_devolucao = '".$regs["data_devolucao"]."' ";
				$usql .= "WHERE ged_versoes.id_ged_versao = '" . $id_ged_versao . "' ";
				$usql .= "AND ged_versoes.reg_del = 0 ";
				
				$db->update($usql,'MYSQL');
				
				if($db->erro!='')
				{
					$erro = true;
				}
									
			}
			else
			{
				$diretorio_origem = DOCUMENTOS_GED . $reg_pacote["base"] . "/" . $reg_pacote["os"] . "/" . substr($reg_pacote["os"],0,4) . DISCIPLINAS . $reg_pacote["disciplina"] . "/" . $reg_pacote["atividade"] . "/" . $reg_pacote["sequencial"].DIRETORIO_DESBLOQUEIOS;
				
				//recusado
				//exclui o ged_desbloqueio						
				$usql = "UPDATE ".DATABASE.".ged_desbloqueios SET ";
				$usql .= "ged_desbloqueios.reg_del = 1, ";
				$usql .= "ged_desbloqueios.reg_who = '".$_SESSION["id_funcionario"]."', ";
				$usql .= "ged_desbloqueios.data_del = '".date('Y-m-d')."' ";
				$usql .= "WHERE ged_desbloqueios.id_ged_desbloqueio = '" . $regs["id_ged_desbloqueio"] . "' ";
				$usql .= "AND ged_desbloqueios.reg_del = 0 ";
	
				$db->update($usql,'MYSQL');
				
				if($db->erro!='')
				{
					$erro = true;
				}
				
				//exclui o arquivo de desbloqueio
				if($regs["strarquivo"]!='')
				{
					unlink($diretorio_origem.$regs["strarquivo"]);	
				}
										
			}
			
			$array_result["solicitante"] = $regs["id_funcionario_solicitante"];		
		}
	}
	
	$array_result["erro"] = $erro;
	
	return $array_result;
}

//função utilizada para compor o menu pop-up do ged
function seleciona_opcoes($id_ged_versao,$x,$y)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados();

	//vem pelo grid o id da linha (id_ged_arquivo)
	$tipo = explode("_",$id_ged_versao);
	
	if(is_numeric($tipo[1]))
	{
		if($tipo[0]=='ARQ')
		{
			$sql = "SELECT * FROM ".DATABASE.".ged_arquivos, ".DATABASE.".ged_versoes ";
			$sql .= "WHERE ged_arquivos.reg_del = 0 ";
			$sql .= "AND ged_versoes.reg_del = 0 ";
			$sql .= "AND ged_versoes.id_ged_versao = '".$tipo[1]."' ";			
			$sql .= "AND ged_arquivos.id_ged_versao = ged_versoes.id_ged_versao ";
			
			$db->select($sql,'MYSQL',true);
  
			if ($db->erro != '')
			{
				$resposta->addAlert("Erro ao tentar selecionar os dados.".$sql);
			}
			else
			{
				$reg_arquivos = $db->array_select[0];
				
				//Se o status atual do arquivo for 0 (desbloqueado)
				if($reg_arquivos["status"]==0)
				{
					$operacao =  "1"; //permite o bloqueio
				}//Se for 1				
				else
				{
					$operacao = "2"; //permite o bloqueio
				}
			
				$resposta->addScript("popupMenu('".$tipo[1]."','".$operacao."','".$x."','".$y."');");
			}
		}	
	}
	
	return $resposta;
}

//VISUALIZA OS ARQUIVOS
function abrir($caminho)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados();
	
	//vem pelo grid o id da linha (id_ged_arquivo)
	$tipo = explode("_",$caminho);
	
	if(($tipo[0]=='ARQ' || $tipo[0]=='COM' || $tipo[0]=='DES') && is_numeric($tipo[1]))
	{
		switch ($tipo[0])
		{
			case 'ARQ':
			
				$sql = "SELECT * FROM ".DATABASE.".ged_arquivos, ".DATABASE.".ged_versoes ";
				$sql .= "WHERE ged_arquivos.reg_del = 0 ";
				$sql .= "AND ged_versoes.reg_del = 0 ";
				$sql .= "AND ged_versoes.id_ged_versao = '".$tipo[1]."' ";				
				$sql .= "AND ged_arquivos.id_ged_arquivo = ged_versoes.id_ged_arquivo ";
				
				$db->select($sql,'MYSQL',true);
	  
				if ($db->erro != '')
				{
					$resposta->addAlert("Erro ao tentar selecionar os dados.".$sql);
				}
				else
				{
					$reg_arquivos = $db->array_select[0];
					
					//se arquivo de revisao_documento
					if($tipo[2]=='VER')
					{
						$caminho = DOCUMENTOS_GED.$reg_arquivos["base"] . "/" . $reg_arquivos["os"] . "/" . substr($reg_arquivos["os"],0,4) . DISCIPLINAS . $reg_arquivos["disciplina"] . "/" . $reg_arquivos["atividade"] . "/" . $reg_arquivos["sequencial"] . "/" . DIRETORIO_VERSOES. "/". $reg_arquivos["nome_arquivo"].".".$reg_arquivos["id_ged_versao"];
					}
					else
					{
						$caminho = DOCUMENTOS_GED.$reg_arquivos["base"] . "/" . $reg_arquivos["os"] . "/" . substr($reg_arquivos["os"],0,4) . DISCIPLINAS . $reg_arquivos["disciplina"] . "/" . $reg_arquivos["atividade"] . "/" . $reg_arquivos["sequencial"] . "/" . $reg_arquivos["nome_arquivo"];	
					}
				}
						
			break;
			
			case 'COM':
			
				$sql = "SELECT *, ged_versoes.strarquivo AS ver_strarquivo, ged_comentarios.strarquivo AS cmt_strarquivo FROM ".DATABASE.".ged_versoes, ".DATABASE.".ged_comentarios ";
				$sql .= "WHERE ged_versoes.id_ged_versao = ged_comentarios.id_ged_versao ";
				$sql .= "AND ged_versoes.reg_del = 0 ";
				$sql .= "AND ged_comentarios.reg_del = 0 ";
				$sql .= "AND ged_comentarios.id_ged_comentario = '" . $tipo[1] . "' ";
			
				$db->select($sql,'MYSQL',true);
				
				if ($db->erro != '')
				{
					$resposta->addAlert("Erro ao tentar selecionar os dados: " . $db->erro);
				}
				else
				{
					$reg_coment = $db->array_select[0];
					
					if($reg_coment["sequencia_doc"]!=0)
					{
						$caminho = DOCUMENTOS_GED . $reg_coment["base"] . "/" . $reg_coment["os"] . "/" .  substr($reg_coment["os"],0,4) . DISCIPLINAS . $reg_coment["disciplina"] . "/" . $reg_coment["atividade"] . "/" . $reg_coment["sequencial"] . DIRETORIO_COMENTARIOS . $reg_coment["cmt_strarquivo"].".".sprintf("%03d",$reg_coment["sequencia_doc"]);	
					}
					else
					{
						$caminho = DOCUMENTOS_GED . $reg_coment["base"] . "/" . $reg_coment["os"] . "/" .  substr($reg_coment["os"],0,4) . DISCIPLINAS . $reg_coment["disciplina"] . "/" . $reg_coment["atividade"] . "/" . $reg_coment["sequencial"] . DIRETORIO_COMENTARIOS . $reg_coment["cmt_strarquivo"];
					}
				}
				  		
			break;
			
			case 'DES':

				//Seleciona os dados dos desbloqueios
				$sql = "SELECT *, ged_desbloqueios.strarquivo AS des_strarquivo FROM ".DATABASE.".ged_desbloqueios, ".DATABASE.".ged_versoes, ".DATABASE.".ged_arquivos ";
				$sql .= "WHERE ged_desbloqueios.reg_del = 0 ";
				$sql .= "AND ged_versoes.reg_del = 0 ";
				$sql .= "AND ged_arquivos.reg_del = 0 ";
				$sql .= "AND ged_arquivos.id_ged_arquivo = ged_versoes.id_ged_arquivo ";
				$sql .= "AND ged_desbloqueios.id_ged_versao = ged_versoes.id_ged_versao ";
				$sql .= "AND ged_desbloqueios.id_ged_desbloqueio = '" . $tipo[1] . "' ";
			
				$db->select($sql,'MYSQL',true);
				
				if ($db->erro != '')
				{
					$resposta->addAlert("Erro ao tentar selecionar os dados: " . $db->erro);
				}
				else
				{
					$reg_desbloq = $db->array_select[0];
					
					$caminho = DOCUMENTOS_GED . $reg_desbloq["base"] . "/" . $reg_desbloq["os"] . "/" .  substr($reg_desbloq["os"],0,4) . DISCIPLINAS . $reg_desbloq["disciplina"] . "/" . $reg_desbloq["atividade"] . "/" . $reg_desbloq["sequencial"] . DIRETORIO_DESBLOQUEIOS . $reg_desbloq["des_strarquivo"];	
				}
				  		
			break;			
		}
	}
	
	$resposta->addScript('open_doc("'.$caminho.'")');
	
	return $resposta;
}

function atualizatabela($filtro="", $tipo_filtro="")
{
	$resposta = new xajaxResponse();
	
	$xml = new XMLWriter();

	$db = new banco_dados();

	$sql_filtro = "";
	
	if($filtro)
	{	
		switch($tipo_filtro)
		{		
			//Filtro por critério de busca
			case "busca":

				if(strlen($filtro)=="4" && is_numeric($filtro))
				{
					$sql_filtro = "AND ged_pacotes.numero_pacote = '" . sprintf("%d",$filtro) . "' ";
				}
				//Se forem 4 casas e numérico, considera como busca por OS
				else if(strlen($filtro)=="5" && is_numeric($filtro))
				{
					$sql_filtro = "AND os.os = '" . $filtro . "' ";
				}
				//Em outro caso, faz uma busca genérica em diversos campos
				else
				{
					$sql_filtro = "AND (ged_versoes.strarquivo LIKE '%" . $filtro . "%' OR ";
					$sql_filtro .= "ged_arquivos.descricao LIKE '%" . $filtro . "%' OR ";
					$sql_filtro .= "numeros_interno.numero_cliente LIKE '%" . $filtro . "%' OR ";
					$sql_filtro .= "funcionarios.funcionario LIKE '%" . $filtro . "%' OR ";
					$sql_filtro .= "ged_pacotes.data = '" . php_mysql($filtro) . "') ";
				}		
			break;
			
			//Filtro por período (padrão)
			default:
		
				$array_filtro = explode("-",$filtro);
		
				$array_ini = explode("/",$array_filtro[0]);
				
				$array_fim = explode("/",$array_filtro[1]);		
				
				$stamp_data_ini = mktime(0,0,0,$array_ini[0],1,$array_ini[1]);

				$stamp_data_fim = mktime(0,0,0,$array_fim[0],31,$array_fim[1]);
				
				$data_ini = date("Y-m-d",$stamp_data_ini);
				
				$data_fim = date("Y-m-d",$stamp_data_fim);

				$sql_filtro = "AND ged_pacotes.data BETWEEN '" . $data_ini . "' AND '" . $data_fim . "' ";

			break;
		}
	}	

	$sql = "SELECT id_ged_pacote, id_grd FROM ".DATABASE.".grd ";
	$sql .= "WHERE grd.reg_del = 0 ";
	
	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	else
	{
		foreach($db->array_select as $reg_grd)
		{
			$array_grd[$reg_grd["id_ged_pacote"]] = $reg_grd["id_grd"];
		}
		
		$sql = "SELECT *, ged_pacotes.status AS status_pacote FROM ".DATABASE.".ged_arquivos, ".DATABASE.".ged_versoes, ".DATABASE.".ged_pacotes, ".DATABASE.".numeros_interno, ".DATABASE.".ordem_servico, ".DATABASE.".funcionarios ";
		$sql .= "WHERE ged_arquivos.reg_del = 0 ";
		$sql .= "AND ged_versoes.reg_del = 0 ";
		$sql .= "AND ged_pacotes.reg_del = 0 ";
		$sql .= "AND numeros_interno.reg_del = 0 ";
		$sql .= "AND ordem_servico.reg_del = 0 ";
		$sql .= "AND funcionarios.reg_del = 0 ";
		$sql .= "AND ged_versoes.id_ged_arquivo = ged_arquivos.id_ged_arquivo "; 
		$sql .= "AND ged_versoes.id_ged_pacote = ged_pacotes.id_ged_pacote ";
		$sql .= "AND ged_arquivos.id_numero_interno = numeros_interno.id_numero_interno ";
		$sql .= "AND numeros_interno.id_os = ordem_servico.id_os ";
		$sql .= "AND ged_pacotes.id_autor = funcionarios.id_funcionario ";
		$sql .= $sql_filtro;
		$sql .= "GROUP BY ged_pacotes.id_ged_pacote ";
		$sql .= "ORDER BY ged_pacotes.id_ged_pacote DESC, ordem_servico.os ASC ";
	
		$db->select($sql,'MYSQL',true);

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
		else
		{
			$xml->openMemory();
			$xml->setIndent(false);
			$xml->startElement('rows') ;
				
			foreach($db->array_select as $cont_pacotes)
			{
				if($cont_pacotes["status_pacote"]=="1")
				{
					$status = '<img src="'.DIR_IMAGENS.'bt_desfazer.png" style="cursor:pointer;" title="Pendente de retorno">';
				}
				else
				{
					$status = '&nbsp;';
				}
				
				if($array_grd[$cont_pacotes["id_ged_pacote"]])
				{
					$del = '&nbsp;';		
				}
				else
				{
					$del = '<img src="'.DIR_IMAGENS.'apagar.png" onclick=if(confirm("Deseja&nbsp;excluir&nbsp;o&nbsp;pacote?&nbsp;Os&nbsp;arquivos&nbsp;relacionados&nbsp;serão&nbsp;liberados&nbsp;no&nbsp;sistema.")){xajax_excluir_pacote("' . $cont_pacotes["id_ged_pacote"] . '");}>';
				}
				
				$xml->startElement('row');
					$xml->writeAttribute('id',$cont_pacotes["id_ged_pacote"]);
					$xml->startElement ('cell');
						$xml->text(sprintf("%04d",$cont_pacotes["numero_pacote"]));
					$xml->endElement();
					$xml->startElement ('cell');
						$xml->text(sprintf("%05d",$cont_pacotes["os"]));
					$xml->endElement();
					$xml->startElement ('cell');
						$xml->text(addslashes($cont_pacotes["funcionario"]));
					$xml->endElement();
					$xml->startElement ('cell');
						$xml->text(mysql_php($cont_pacotes["data"]));
					$xml->endElement();
					$xml->startElement ('cell');
						$xml->text($del);
					$xml->endElement();
					
				$xml->endElement();				
				
			}
			
			$xml->endElement();
					
			$conteudo = $xml->outputMemory(false);
			
			$resposta->addScript("grid('div_ged_pacotes',true,'300','".$conteudo."');");
			
		}
	}

	return $resposta;
}

//Mostra o conteúdo do pacote
function mostraPacote($id_ged_pacote)
{	
	$resposta = new xajaxResponse();
	
	$xml = new XMLWriter();
	
	$db = new banco_dados();
	
	$sql = "SELECT * FROM ".DATABASE.".codigos_copia ";	
	$sql .= "WHERE codigos_copia IN ('CE','CP') ";
	$sql .= "AND codigos_copia.reg_del = 0  ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	else
	{
		foreach($db->array_select as $reg_tipo)
		{
			$array_tipo[$reg_tipo["id_codigo_copia"]] = $reg_tipo["codigos_copia"];
			$array_desc_tipo[$reg_tipo["id_codigo_copia"]] = $reg_tipo["copia"];	
		}
	
		$sql = "SELECT * FROM ".DATABASE.".codigos_emissao ";
		$sql .= "WHERE codigos_emissao.reg_del = 0 ";
	
		$db->select($sql,'MYSQL',true);

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
		else
		{	
			foreach($db->array_select as $reg_fin)
			{
				$array_fin[$reg_fin["id_codigo_emissao"]] = $reg_fin["codigos_emissao"];
				$array_desc_fin[$reg_fin["id_codigo_emissao"]] = $reg_fin["emissao"];	
			}
		
			$sql = "SELECT * FROM ".DATABASE.".formatos ";
			$sql .= "WHERE formatos.reg_del = 0 ";
			
			$db->select($sql,'MYSQL',true);

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}
			else
			{
				foreach($db->array_select as $reg_form)
				{
					$array_form[$reg_form["id_formato"]] = $reg_form["formato"];	
				}
			
				//Seleciona as informações sobre o pacote
				$sql = "SELECT *, ged_pacotes.status FROM ".DATABASE.".ged_arquivos, ".DATABASE.".ged_versoes, ".DATABASE.".ged_pacotes, ".DATABASE.".numeros_interno, ".DATABASE.".ordem_servico, ".DATABASE.".empresas, ".DATABASE.".funcionarios, ".DATABASE.".contatos ";
				$sql .= "WHERE ged_arquivos.reg_del = 0 ";
				$sql .= "AND ged_versoes.reg_del = 0 ";
				$sql .= "AND ged_pacotes.reg_del = 0 ";
				$sql .= "AND numeros_interno.reg_del = 0 ";
				$sql .= "AND ordem_servico.reg_del = 0 ";
				$sql .= "AND empresas.reg_del = 0 ";
				$sql .= "AND funcionarios.reg_del = 0 ";
				$sql .= "AND contatos.reg_del = 0 ";
				$sql .= "AND ged_arquivos.id_ged_arquivo = ged_versoes.id_ged_arquivo ";
				$sql .= "AND ged_versoes.id_ged_pacote = ged_pacotes.id_ged_pacote ";
				$sql .= "AND ged_arquivos.id_numero_interno = numeros_interno.id_numero_interno ";
				$sql .= "AND numeros_interno.id_os = ordem_servico.id_os ";
				$sql .= "AND ordem_servico.id_empresa_erp = empresas.id_empresa_erp ";
				$sql .= "AND ordem_servico.id_cod_coord = funcionarios.id_funcionario ";
				$sql .= "AND ordem_servico.id_cod_resp = contatos.id_contato ";
				$sql .= "AND ged_versoes.id_ged_pacote = '" . $id_ged_pacote . "' ";
				$sql .= "GROUP BY ordem_servico.id_os ";
				
				$db->select($sql,'MYSQL',true);

				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
				}
				else
				{
			
					$reg_info = $db->array_select[0];
				
					$sql = "SELECT * FROM ".DATABASE.".grd ";
					$sql .= "WHERE grd.reg_del = 0 ";
					$sql .= "AND grd.id_ged_pacote = '" . $reg_info["id_ged_pacote"] . "' ";
					
					$db->select($sql,'MYSQL',true);

					if($db->erro!='')
					{
						$resposta->addAlert($db->erro);
					}
					else
					{				
						$reg_grd = $db->array_select[0];
						
						$num_reg_grd = $db->numero_registros;
					
						//Limpa o campo GRD
						$resposta->addAssign("informacao_emissao","innerHTML","");						
					
						if($num_reg_grd > 0)
						{
							$resposta->addAssign("informacao_emissao","innerHTML",mysql_php($reg_grd["data_emissao"]));
							
							$resposta->addAssign("div_pacote", "innerHTML", sprintf("%04d",$reg_info["numero_pacote"]));
							
							$resposta->addScript("xajax.$('btn_visualizar').disabled=false;");
							
							//Esconde o botão de pré-visualizar
							$resposta->addAssign("div_preview","style.display","none");
						
						}
						else
						{
							$input_num_pacote = '<input type="hidden" name="hid_num_pacote" id="hid_num_pacote" value="' . sprintf("%04d",$reg_info["numero_pacote"]) . '"><input type="text" class="caixa" size="4" maxlength="4" name="txt_num_pacote" id="txt_num_pacote" value="' . sprintf("%04d",$reg_info["numero_pacote"]) . '" onblur="if(this.value!==xajax.$("hid_num_pacote").value){if(confirm("ATENÇÃO: Isso irá alterar a numeração do pacote de forma definitiva. Deseja continuar?")){xajax_alteraPacote(xajax.getFormValues("frm"));}else{this.value=xajax.$("hid_num_pacote").value;}}" >';	
							
							$resposta->addAssign("div_pacote", "innerHTML", $input_num_pacote);	
							
							$resposta->addScript("xajax.$('btn_visualizar').disabled=true;");		
					
							//Mostra o botão de pré-visualizar
							$resposta->addAssign("div_preview","style.display","inline");								
						}
						
						$resposta->addAssign("div_coordenador","innerHTML", $reg_info["funcionario"]);
						$resposta->addAssign("div_cliente","innerHTML",$reg_info["empresa"]);
						$resposta->addAssign("div_coordenador_cliente","innerHTML",$reg_info["nome_contato"]);
						$resposta->addAssign("div_os","innerHTML",sprintf("%05d",$reg_info["os"]));
					
						//Seleciona os dados referentes ao pacote
						//Campos foram selecionados individualmente devido a ganho significativo na performance (query muito pesada)
						
						$sql = "SELECT setores.sigla, ged_versoes.descricao, ged_versoes.versao_, ged_versoes.revisao_interna, ";
						$sql .= "ged_versoes.id_codigo_emissao, ged_versoes.id_fin_emissao, ged_versoes.id_ged_pacote, ";
						$sql .= "numeros_interno.id_formato, ged_arquivos.id_ged_arquivo, ged_versoes.status_devolucao, ordem_servico.id_os, os.os, numeros_interno.sequencia, ";
						$sql .= "numeros_interno.numero_cliente, solicitacao_documentos_detalhes.id_numero_interno, ged_versoes.numero_folhas as folhas, "; 
						$sql .= "ged_versoes.copias, ged_versoes.revisao_cliente, ged_arquivos.status, ";
						$sql .= "ged_arquivos.documento_interno, ged_arquivos.id_ged_versao AS id_ged_versao_atual, ged_versoes.id_ged_versao AS id_ged_versao_pacote, ged_versoes.base, ged_versoes.os, ged_versoes.disciplina, ged_versoes.atividade, ged_versoes.strarquivo, ged_versoes.sequencial, ged_versoes.nome_arquivo "; 
						$sql .= "FROM ".DATABASE.".ged_arquivos, ".DATABASE.".ged_versoes, ".DATABASE.".solicitacao_documentos_detalhes, ".DATABASE.".numeros_interno, ".DATABASE.".ordem_servico, ".DATABASE.".setores ";
						$sql .= "WHERE ged_arquivos.reg_del = 0 ";
						$sql .= "AND ged_versoes.reg_del = 0 ";
						$sql .= "AND numeros_interno.reg_del = 0 ";
						$sql .= "AND solicitacao_documentos_detalhes.reg_del = 0 ";
						$sql .= "AND ordem_servico.reg_del = 0 ";
						$sql .= "AND setores.reg_del = 0 ";
						$sql .= "AND ged_arquivos.id_ged_arquivo = ged_versoes.id_ged_arquivo ";
						$sql .= "AND ged_arquivos.id_numero_interno = solicitacao_documentos_detalhes.id_numero_interno ";
						$sql .= "AND ged_arquivos.id_numero_interno = numeros_interno.id_numero_interno ";
						$sql .= "AND numeros_interno.id_os = ordem_servico.id_os ";
						$sql .= "AND numeros_interno.id_disciplina = setores.id_setor ";
						$sql .= "AND ged_versoes.id_ged_pacote = '" . $id_ged_pacote . "' ";
						$sql .= "ORDER BY numeros_interno.sequencia ASC ";
					
						$db->select($sql,'MYSQL',true);

						if($db->erro!='')
						{
							$resposta->addAlert($db->erro);
						}
						else
						{
							$num_reg_arquivos = $db->numero_registros;
						
							//Se o pacote estiver liberado
							if($reg_info["status"] == 0 && $num_reg_grd == 0)
							{
								//Habilita o botão
								$resposta->addScript("xajax.$('btn_enviar').disabled=false;");
								
								$resposta->addScript("xajax.$('btn_retorno').disabled=true;");
								
								$icon = 0;		
							}
							elseif($reg_info["status"]=="1")
							{
								
								$icon = 1;
								//Habilita o botão
								$resposta->addScript("xajax.$('btn_retorno').disabled=false;");
								
								$resposta->addScript("xajax.$('btn_enviar').disabled=true;");
	
								$resposta->addEvent("btnvoltar","onclick","location.reload()");	
							}
							else
							{	
								$icon = '';
								
								$resposta->addScript("xajax.$('btn_enviar').disabled=true;");
								
								$resposta->addScript("xajax.$('btn_retorno').disabled=true;");		
							}
							
							$xml->openMemory();
							$xml->setIndent(false);
							$xml->startElement('rows');
						
							foreach($db->array_select as $reg_arquivos)
							{
								$descricao_numdvm = "DVM-" . sprintf("%05d",$reg_arquivos["os"]) . "-" . $reg_arquivos["sigla"] . "-" .$reg_arquivos["sequencia"];
								
								$base_arquivo = $reg_arquivos["base"] . "/" . $reg_arquivos["os"] . "/" . substr($reg_arquivos["os"],0,4) . DISCIPLINAS . $reg_arquivos["disciplina"] . "/" . $reg_arquivos["atividade"];
								
								if($reg_arquivos["id_ged_versao_atual"]==$reg_arquivos["id_ged_versao_pacote"])
								{
									$arquivo = $base_arquivo . "/" . $reg_arquivos["sequencial"]."/".$reg_arquivos["nome_arquivo"];
									
									$openfile = 'ARQ_'.$reg_arquivos["id_ged_versao_pacote"];
								}
								else
								{
									$arquivo = $base_arquivo . "/" . $reg_arquivos["sequencial"].DIRETORIO_VERSOES."/".$reg_arquivos["nome_arquivo"].".".$reg_arquivos["id_ged_versao_pacote"];
								
									$openfile = 'ARQ_'.$reg_arquivos["id_ged_versao_pacote"].'_VER';
								}
								
								//se for um arquivo
								if(is_file(DOCUMENTOS_GED.$arquivo) && file_exists(DOCUMENTOS_GED.$arquivo))
								{			
									$options_tipo = "";									
									$options_fin = "";									
									$options_form = "";
							
									foreach($array_tipo as $chave_tipo=>$valor_tipo)
									{
										$selected_tipo = $chave_tipo == $reg_arquivos["codigos_emissao"] ? 'selected' : '';
										
										$options_tipo .= '<option value="' . $chave_tipo . '" ' . $selected_tipo . ' title="' . $array_desc_tipo[$chave_tipo] . '">' . $valor_tipo . '</option>';
									}
							
									foreach($array_fin as $chave_fin=>$valor_fin)
									{
										$selected_fin = $chave_fin == $reg_arquivos["id_fin_emissao"] ? 'selected' : '';
										
										$options_fin .= '<option value="' . $chave_fin . '" ' . $selected_fin . ' title="' . addslashes($array_desc_fin[$chave_fin]) . '">' . $valor_fin . '</option>';									
									}
							
									foreach($array_form as $chave_form=>$valor_form)
									{
										$selected_form = $chave_form == $reg_arquivos["id_formato"] ? 'selected' : '';
										
										$options_form .= '<option value="' . $chave_form . '" ' . $selected_form . ' title="' . $array_desc_form[$chave_form] . '">' . trim($valor_form) . '</option>';
									}
									
									//Preenche o checkbox, se o arquivo estiver com finalidade DIFERENTE DE CERTIFICADO 
									if($reg_arquivos["id_fin_emissao"]!=3)
									{
										$chk_checked = 'checked';
									}
									else
									{
										$chk_checked = '';
									}									
						
									//Preenche o checkbox, se o arquivo estiver com finalidade DIFERENTE DE CERTIFICADO 
									if($reg_arquivos["documento_interno"])
									{
										$chk_doc_checked = 'checked';
									}
									else
									{
										$chk_doc_checked = '';
									}						
						
									//Explode o nome do arquivo
									$extensao_array = explode(".",basename($reg_arquivos["nome_arquivo"]));
									
									//Pega somente a extensão
									$extensao = $extensao_array[count($extensao_array)-1];					
									
									//Pega a imagem referente a extensão
									$imagem = retornaImagem($extensao);						
									
									//Pega a imagem da bolinha referente ao status do arquivo
									switch ($reg_arquivos["status"])
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
											  if($reg_info["status"]==0) //local
											  {
												  $imagem_bolinha = retornaImagem(2);
											  }
											  else
											  {
												  $imagem_bolinha = retornaImagem(3);
											  }
										  break; 
										
									}
									
									//finalidade CERTIFICADO e DEVOLUÇÃO APROVADO
									if($reg_arquivos["id_fin_emissao"]==3 && $reg_arquivos["status_devolucao"]=='A')
									{
										if($reg_arquivos["status"]==2)
										{
											$imagem_bolinha = retornaImagem(4);
										}						
									}														
													
									//Pega o tamanho
									$tamanho = formataTamanho(filesize(DOCUMENTOS_GED.$arquivo));
								
									//MODIFICAÇÃO FEITA POR CARLOS ABREU - 13/09/2010
									$total_tamanho += filesize(DOCUMENTOS_GED.$arquivo);						
									
									//Pega a data de modificação
									$data_modificacao = date("d/m/Y H:i:s",filemtime(DOCUMENTOS_GED.$arquivo)); 
									
									if($reg_info["status"] == 0 && $num_reg_arquivos>1 && $num_reg_grd == 0)
									{
										$del = '<img src="'.DIR_IMAGENS.'apagar.png" style="cursor:pointer;" title="Remover&nbsp;do&nbsp;pacote&nbsp;e&nbsp;liberar&nbsp;no&nbsp;GED" onclick=if(confirm("Essa&nbsp;ação&nbsp;irá&nbsp;retirar&nbsp;o&nbsp;arquivo&nbsp;nº&nbsp;' . $descricao_numdvm. '&nbsp;do&nbsp;pacote&nbsp;e&nbsp;liberar&nbsp;a&nbsp;edição&nbsp;do&nbsp;mesmo&nbsp;no&nbsp;GED.&nbsp;Confirma?")){xajax_liberar_versao("' . $reg_arquivos["id_ged_versao_pacote"] . '");}>';
									}
									else
									{
										$del = '&nbsp;';
									}
									
									$xml->startElement('row');
										$xml->writeAttribute('id',$openfile);
										$xml->startElement ('cell');
											$xml->text('<input type="checkbox" id="arqnum_' . $reg_arquivos["id_ged_arquivo"] . '" name="arqnum_' . $reg_arquivos["id_ged_arquivo"] . '"  value="1" ' . $chk_checked . ' ' . $chk_disabled . '>');
										$xml->endElement();
										$xml->startElement ('cell');
											$xml->text('<input type="hidden" id="chk_arquivo_' . $reg_arquivos["id_ged_arquivo"] . '" name="chk_arquivo_' . $reg_arquivos["id_ged_arquivo"] . '" value="1">'.$imagem_bolinha);
										$xml->endElement();
										$xml->startElement ('cell');
											$xml->text($imagem);
										$xml->endElement();
										$xml->startElement ('cell');
											$xml->text(addslashes($reg_arquivos["nome_arquivo"]));
										$xml->endElement();
										$xml->startElement ('cell');
											$xml->text($descricao_numdvm);
										$xml->endElement();
										$xml->startElement ('cell');
											$xml->text('<input type="hidden" id="hid_rev_dvm_' . $reg_arquivos["id_ged_arquivo"] . '" name="hid_rev_dvm_' . $reg_arquivos["id_ged_arquivo"] . '" value="' . $reg_arquivos["revisao_interna"] . '"><input type="text" class="caixa" name="txt_rev_dvm_' . $reg_arquivos["id_ged_arquivo"] . '" value="' . $reg_arquivos["revisao_interna"] . '" style="width:100%;" onblur=if(xajax.$("hid_rev_dvm_' . $reg_arquivos["id_ged_arquivo"] . '").value!==this.value){if(confirm("Deseja&nbsp;atualizar&nbsp;a&nbsp;Revisão&nbsp;Interna&nbsp;"+xajax.$("hid_rev_dvm_' . $reg_arquivos["id_ged_arquivo"] . '").value+"&nbsp;para&nbsp;"+this.value+"?")){xajax.$("hid_rev_dvm_' . $reg_arquivos["id_ged_arquivo"] . '").value=this.value;xajax_atualiza_campos("revisao_interna",this.value,'.$reg_arquivos["id_ged_versao_atual"].');xajax.$("hid_rev_dvm_' . $reg_arquivos["id_ged_arquivo"] . '").value=this.value}else{this.value=xajax.$("hid_rev_dvm_' . $reg_arquivos["id_ged_arquivo"] . '").value;}}>');
										$xml->endElement();
										$xml->startElement ('cell');
											$xml->text($reg_arquivos["versao_"]);
										$xml->endElement();
										$xml->startElement ('cell');
											$xml->text('<input type="hidden" id="hid_numcliente_' . $reg_arquivos["id_ged_arquivo"] . '" name="hid_numcliente_' . $reg_arquivos["id_ged_arquivo"] . '" value="' . addslashes($reg_arquivos["numero_cliente"]) . '"><input type="text" class="caixa" name="txt_numcliente_' . $reg_arquivos["id_ged_arquivo"] . '" value="' . addslashes($reg_arquivos["numero_cliente"]) . '" style="width:100%;" onblur=if(xajax.$("hid_numcliente_' . $reg_arquivos["id_ged_arquivo"] . '").value!==this.value){if(confirm("Deseja&nbsp;atualizar&nbsp;o&nbsp;Nº&nbsp;Cliente&nbsp;"+xajax.$("hid_numcliente_' . $reg_arquivos["id_ged_arquivo"] . '").value+"&nbsp;para&nbsp;"+this.value+"?")){xajax.$("hid_numcliente_' . $reg_arquivos["id_ged_arquivo"] . '").value=this.value;xajax_atualiza_campos("numero_cliente",this.value,'.$reg_arquivos["id_ged_versao_atual"].');xajax.$("hid_numcliente_' . $reg_arquivos["id_ged_arquivo"] . '").value=this.value;}else{this.value=xajax.$("hid_numcliente_' . $reg_arquivos["id_ged_arquivo"] . '").value;}}>');
										$xml->endElement();
										$xml->startElement ('cell');
											$xml->text('<input type="hidden" id="hid_rev_cliente_' . $reg_arquivos["id_ged_arquivo"] . '" name="hid_rev_cliente_' . $reg_arquivos["id_ged_arquivo"] . '" value="' . $reg_arquivos["revisao_cliente"] . '"><input type="text" class="caixa" name="txt_rev_cliente_' . $reg_arquivos["id_ged_arquivo"] . '" value="' . $reg_arquivos["revisao_cliente"] . '" style="width:100%;" onblur=if(xajax.$("hid_rev_cliente_' . $reg_arquivos["id_ged_arquivo"] . '").value!==this.value){if(confirm("Deseja&nbsp;atualizar&nbsp;a&nbsp;Revisão&nbsp;Cliente&nbsp;"+xajax.$("hid_rev_cliente_' . $reg_arquivos["id_ged_arquivo"] . '").value+"&nbsp;para&nbsp;"+this.value+"?")){xajax.$("hid_rev_cliente_' . $reg_arquivos["id_ged_arquivo"] . '").value=this.value;xajax_atualiza_campos("revisao_cliente",this.value,'.$reg_arquivos["id_ged_versao_atual"].');xajax.$("hid_rev_cliente_' . $reg_arquivos["id_ged_arquivo"] . '").value=this.value;}else{this.value=xajax.$("hid_rev_cliente_' . $reg_arquivos["id_ged_arquivo"] . '").value;}}>');
										$xml->endElement();
										$xml->startElement ('cell');
											$xml->text($tamanho);
										$xml->endElement();										
										$xml->startElement ('cell');
											$xml->text('<select name="sel_form_' . $reg_arquivos["id_ged_arquivo"] . '" class="caixa" onclick=sel_form_vlr=this.options[this.selectedIndex].value; onchange=if(confirm("Confirma&nbsp;a&nbsp;alteração&nbsp;de&nbsp;formato?")){xajax_atualiza_campos("formato",this.value,'.$reg_arquivos["id_ged_versao_atual"].');}else{seleciona_combo(sel_form_vlr,"sel_form_'  . $reg_arquivos["id_ged_arquivo"] . '");}>' . $options_form . '</select>');
										$xml->endElement();
										$xml->startElement ('cell');
											$xml->text('<input type="hidden" id="hid_txt_fls_' . $reg_arquivos["id_ged_arquivo"] . '" name="hid_txt_fls_' . $reg_arquivos["id_ged_arquivo"] . '" value="' . $reg_arquivos["folhas"] . '"><input type="text" class="caixa" name="txt_fls_' . $reg_arquivos["id_ged_arquivo"] . '" id="txt_fls_' . $reg_arquivos["id_ged_arquivo"] . '" value="' . $reg_arquivos["folhas"] . '" size="3" onkeypress=num_only(); onblur=if(xajax.$("hid_txt_fls_' . $reg_arquivos["id_ged_arquivo"] . '").value!==this.value){if(confirm("Deseja&nbsp;alterar&nbsp;a&nbsp;quantidade&nbsp;de&nbsp;folhas&nbsp;de&nbsp;"+xajax.$("hid_txt_fls_' . $reg_arquivos["id_ged_arquivo"] . '").value+"&nbsp;para&nbsp;"+this.value+"?")){xajax_atualiza_campos("folhas",this.value,'.$reg_arquivos["id_ged_versao_atual"].');xajax.$("hid_txt_fls_' . $reg_arquivos["id_ged_arquivo"] . '").value=this.value;}else{this.value=xajax.$("hid_txt_fls_' . $reg_arquivos["id_ged_arquivo"] . '").value;}}>');
										$xml->endElement();										
										$xml->startElement ('cell');
											$xml->text('<select name="sel_fin_'  . $reg_arquivos["id_ged_arquivo"] . '" class="caixa" onclick=sel_fin_vlr=this.options[this.selectedIndex].value; onchange=if(confirm("Confirma&nbsp;a&nbsp;alteração&nbsp;de&nbsp;finalidade?")){xajax_atualiza_campos("finalidade",this.value,'.$reg_arquivos["id_ged_versao_atual"].');}else{seleciona_combo(sel_fin_vlr,"sel_fin_'  . $reg_arquivos["id_ged_arquivo"] . '");}>' . $options_fin . '</select>');
										$xml->endElement();
										$xml->startElement ('cell');
											$xml->text('<input type="hidden" id="hid_txt_cp_' . $reg_arquivos["id_ged_arquivo"] . '" name="hid_txt_cp_' . $reg_arquivos["id_ged_arquivo"] . '" value="' . $reg_arquivos["copias"] . '"><input type="text" class="caixa" name="txt_cp_' . $reg_arquivos["id_ged_arquivo"] . '" id="txt_cp_' . $reg_arquivos["id_ged_arquivo"] . '" value="' . $reg_arquivos["copias"] . '" size="2" onkeypress=num_only(); onblur=if(xajax.$("hid_txt_cp_' . $reg_arquivos["id_ged_arquivo"] . '").value!==this.value){if(confirm("Deseja&nbsp;alterar&nbsp;a&nbsp;quantidade&nbsp;de&nbsp;cópias&nbsp;de&"+xajax.$("hid_txt_cp_' . $reg_arquivos["id_ged_arquivo"] . '").value+"&nbsp;para&nbsp;"+this.value+"?")){xajax_atualiza_campos("copias",this.value,'.$reg_arquivos["id_ged_versao_atual"].');xajax.$("hid_txt_cp_' . $reg_arquivos["id_ged_arquivo"] . '").value=this.value;}else{this.value=xajax.$("hid_txt_cp_' . $reg_arquivos["id_ged_arquivo"] . '").value;}}>');
										$xml->endElement();
										$xml->startElement ('cell');
											$xml->text('<input title="Documento&nbsp;interno&nbsp;" type="checkbox" id="chk_doc_dvm_' . $reg_arquivos["id_ged_arquivo"] . '" name="chk_doc_dvm_' . $reg_arquivos["id_ged_arquivo"] . '"  value="1" onclick=if(confirm("Confirma&nbsp;a&nbsp;alteração&nbsp;de&nbsp;documento&nbsp;interno&nbsp;para&nbsp;externo?")){xajax_atualiza_campos("documento_interno",this.checked,'.$reg_arquivos["id_ged_versao_atual"].');}else{if(this.checked){this.checked=false}else{this.checked=true}} '.$chk_doc_checked.'>');
										$xml->endElement();
										$xml->startElement ('cell');
											$xml->text($del);
										$xml->endElement();			
										
									$xml->endElement();	
															
								}
								else
								{
									$resposta->addAlert('Erro: arquivo não encontrado.'.$arquivo);
								}								
							}
					
							$format_total_tamanho = formataTamanho($total_tamanho);
							
							if($num_reg_arquivos > 1)
							{
								$texto_arquivo =  $num_reg_arquivos . " arquivos, " . $format_total_tamanho . ".";
							}
							else
							{
								if($num_reg_arquivos == 1 && $format_total_tamanho > 0)
								{
									$texto_arquivo = "1 arquivo, " . $format_total_tamanho . ".";
								}
								else
								{
									$texto_arquivo = "0 arquivos.";	
								}
							}
							
							$xml->endElement();
									
							$conteudo = $xml->outputMemory(false);							
														
							$resposta->addScript("grid('div_conteudo_pacotes',true,'250','".$conteudo."');");
							
							//Assigna o id_ged_pacote
							$resposta->addAssign("id_ged_pacote","value",$id_ged_pacote);							
							$resposta->addAssign("numero_pacote","value",$reg_info["numero_pacote"]);
							$resposta->addAssign("barra_status","innerHTML", $texto_arquivo);
							$resposta->addAssign("OS","value",$reg_info["os"]);
							$resposta->addAssign("div_conteudo_pacotes","style.display","inline");
						
							$resposta->addScript("RCmenuInst.destroi(); ");														

						}
					}
				}
			}
		}
	}

	return $resposta;
}

//Envia e-mail com anexos para o arquivo tecnico
function enviaPacote($dados_form)
{
	//Alterado por Carlos Abreu em 24/05/2010
	//Visando a alteração da revisão quando do check-out do arquivo
	//a alteracao seta o campo retorno da tabela ged_arquivos
	//0 - não faz alteração - padrao ao incluir um novo arquivo
	//1 - emitido para o cliente - não altera versao_documento - aguarda retorno ou o cancelamento
	//2 - retornado do cliente - habilita o incremento da revisão no check-out	
	$resposta = new xajaxResponse();
		
	$db = new banco_dados();
	
	$erro = false;
	
	//Verifica se já existe GRD para o pacote selecionado
	$sql = "SELECT id_ged_pacote FROM ".DATABASE.".grd ";
	$sql .= "WHERE grd.reg_del = 0 ";
	$sql .= "AND grd.id_ged_pacote = '" . $dados_form["id_ged_pacote"] . "' ";
	
	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	else
	{
		  if($db->numero_registros > 0)
		  {
			  $resposta->addAlert("Já existe uma GRD para o pacote selecionado!");
		  }
		  else
		  {
			  //atualiza o status do pacote	
			  $usql = "UPDATE ".DATABASE.".ged_pacotes SET ";
			  $usql .= "ged_pacotes.status = '1' ";
			  $usql .= "WHERE ged_pacotes.id_ged_pacote = '" . $dados_form["id_ged_pacote"] . "' ";
			  $usql .= "AND ged_pacotes.reg_del = 0 ";
			  
			  $db->update($usql,'MYSQL');
			  
			  //Verifica a qual OS pertence o pacote
			  $sql = "SELECT * FROM ".DATABASE.".ged_pacotes ";
			  $sql .= "WHERE ged_pacotes.reg_del = 0 ";
			  $sql .= "AND ged_pacotes.id_ged_pacote = '" . $dados_form["id_ged_pacote"] . "' ";
			  
			  $db->select($sql,'MYSQL',true);

			  if($db->erro!='')
			  {
				  $resposta->addAlert($db->erro);
			  }
			  else
			  {
				  $reg_verif_os = $db->array_select[0];
				  
				  $id_os = $reg_verif_os["id_os"];
				  
				  //lista de usuarios para e-mail
				  $sql = "SELECT funcionarios.id_funcionario, funcionario, email FROM ".DATABASE.".funcionarios, ".DATABASE.".usuarios ";
				  $sql .= "WHERE funcionarios.id_funcionario = usuarios.id_funcionario ";
				  $sql .= "AND funcionarios.reg_del = 0 ";
				  $sql .= "AND usuarios.reg_del = 0 ";
				  $sql .= "AND funcionarios.situacao = 'ATIVO' ";
				  
				  $db->select($sql,'MYSQL',true);
	
				  if($db->erro!='')
				  {
					  $resposta->addAlert($db->erro);
				  }
				  
				  foreach($db->array_select as $regs)
				  {
					 	$array_func[$regs["id_funcionario"]] = $regs["funcionario"];
						
						$array_email[$regs["id_funcionario"]] = $regs["email"]; 
				  }				  
				  
				  //Insere GRD
				  $isql = "INSERT INTO ".DATABASE.".grd (id_ged_pacote, id_os, id_remetente, data_emissao) VALUES (";
				  $isql .= "'" . $dados_form["id_ged_pacote"] . "', ";
				  $isql .= "'" . $id_os . "', ";
				  $isql .= "'" . $_SESSION["id_funcionario"] . "', ";
				  $isql .= "'" . date('Y-m-d') . "') ";
				  
				  $db->insert($isql,'MYSQL');
				  
				  $id_grd = $db->insert_id;	
			  
				  //seleciona os arquivos para a GRD
				  $sql = "SELECT * FROM ".DATABASE.".ged_arquivos, ".DATABASE.".ged_versoes ";
				  $sql .= "WHERE ged_arquivos.reg_del = 0 ";
				  $sql .= "AND ged_versoes.reg_del = 0 ";
				  $sql .= "AND ged_arquivos.id_ged_arquivo = ged_versoes.id_ged_arquivo ";
				  $sql .= "AND ged_versoes.id_ged_pacote = '" . $dados_form["id_ged_pacote"] . "' ";
				  
				  $db->select($sql,'MYSQL',true);

				  if($db->erro!='')
				  {
					  $resposta->addAlert($db->erro);
				  }
				  else
				  {			  
					  foreach($db->array_select as $reg_grd_ver)
					  {
						  $isql = "INSERT INTO ".DATABASE.".grd_versoes (id_grd, id_ged_versao) VALUES(";
						  $isql .= "'" . $id_grd . "', ";
						  $isql .= "'" . $reg_grd_ver["id_ged_versao"] . "') ";		
						  
						  $db->insert($isql,'MYSQL');

						  if($db->erro!='')
						  {
							  $resposta->addAlert($db->erro);
						  }
						  else
						  {						  
							  //acrescentado por carlos abreu 24/05/2010
							  //1 - emitido para o cliente - não altera versao_documento - aguarda retorno ou o cancelamento
							  $usql = "UPDATE ".DATABASE.".ged_versoes SET ";
							  $usql .= "retorno = '1' ";
							  $usql .= "WHERE ged_versoes.id_ged_versao = '".$reg_grd_ver["id_ged_versao"]."' ";
							  $usql .= "AND ged_versoes.reg_del = 0 ";
							  
							  $db->update($usql,'MYSQL');
							  
							  $usql = "UPDATE ".DATABASE.".ged_arquivos SET ";
							  $usql .= "status = '2' ";
							  $usql .= "WHERE ged_arquivos.id_ged_arquivo = '".$reg_grd_ver["id_ged_arquivo"]."' ";
							  $usql .= "AND ged_arquivos.reg_del = 0 ";
							  
							  $db->update($usql,'MYSQL');
							  
							  //ACRESCENTADO POR CARLOS ABREU
							  //30/09/2010
							  //SETAR NA TABELA NUMDVM COMO EMITIDO PARA O CLIENTE, SALVANDO A DATA, FLAG EMITIDO, GRD NA QUAL FOI EMITIDO PELA ULTIMA VEZ
							  //E TAMBEM A DATA DO RETORNO PROVAVEL			
							  $usql = "UPDATE ".DATABASE.".numeros_interno SET ";
							  $usql .= "data_emissao_arquivo = '".date("Y-m-d")."', ";
							  $usql .= "data_retorno_arquivo = '".php_mysql(checaDiasUteis(date("d/m/Y"),5,$ret,"sum"))."', ";
							  $usql .= "data_envio_aviso = '".php_mysql(checaDiasUteis(date("d/m/Y"),1,$ret,"sum"))."', ";
							  $usql .= "id_grd_emitido = '".$id_grd."', ";
							  $usql .= "flag_emitido = 1 ";
							  $usql .= "WHERE id_numero_interno = '".$reg_grd_ver["id_numero_interno"]."' ";
							  $usql .= "AND numeros_interno.reg_del = 0 ";
							  
							  $db->update($usql,'MYSQL');
						  }
					  }
					  
					  //Seleciona informações da OS para GRD
					  //28/09/2015 -  Carlos Abreu
					  $sql = "SELECT * FROM ".DATABASE.".ordem_servico, ".DATABASE.".empresas ";
					  $sql .= "WHERE ordem_servico.id_os = '".$id_os."' ";
					  $sql .= "AND ordem_servico.reg_del = 0 ";
					  $sql .= "AND empresas.reg_del = 0 ";
					  $sql .= "AND ordem_servico.id_empresa_erp = empresas.id_empresa_erp ";
				  
					  $db->select($sql,'MYSQL',true);

					  if($db->erro!='')
					  {
						  $resposta->addAlert($db->erro);
					  }
					  else
					  {
						  $reg_dir = $db->array_select[0];
						  
						  $diretorio_grd = DOCUMENTOS_GED . $reg_dir["abreviacao_GED"] . "/" . $reg_dir["os"] . "-" .$reg_dir["descricao"]. "/" . substr($reg_dir["os"],0,4) . GRD;  
					  }					  
					  
					  $numero_grd = geraRelatorio($id_grd);		  
													  
					  //Seleciona informações do pacote
					  $sql = "SELECT * FROM ".DATABASE.".ged_arquivos, ".DATABASE.".ged_versoes ";
					  $sql .= "WHERE ged_arquivos.reg_del = 0 ";
					  $sql .= "AND ged_versoes.reg_del = 0 ";
					  $sql .= "AND ged_arquivos.id_ged_arquivo = ged_versoes.id_ged_arquivo ";
					  $sql .= "AND ged_versoes.id_ged_pacote = '" . $dados_form["id_ged_pacote"] . "' ";
					  $sql .= "GROUP BY ged_arquivos.id_ged_arquivo ";
					  $sql .= "ORDER BY ged_arquivos.descricao ";
				  
					  $db->select($sql,'MYSQL',true);

					  if($db->erro!='')
					  {
						  $resposta->addAlert($db->erro);
					  }
					  else
					  {						  
						  //se não existir a pasta	
						  if(!is_dir($diretorio_grd))
						  {
							  //tenta criar
							  if(!mkdir($diretorio_grd,0777))
							  {
								  $resposta->addAlert("Erro ao tentar criar a pasta temporária GRD no servidor.");			
							  } 		
						  }
						  
						  //armazena os arquivos temporarios
						  $dir_temp = ROOT_DIR."/arquivotec/temp_docs";
						  
						  //armazena os arquivos zip gerados
						  $dir_zip = ROOT_DIR."/arquivotec/temp_zip";	
						  
						  $pasta_rnd = rand(10000,99999);
						  
						  if(!is_dir($dir_temp))
						  {
							  if(!mkdir($dir_temp,0777))
							  {
								  $resposta->addAlert("Erro ao tentar criar a pasta temporária TEMP no servidor.");			
							  }		
						  }
						  
						  //Se não existir a pasta do documentos temporarios
						  if(!is_dir($dir_temp."/".$pasta_rnd))
						  {
							  if(!mkdir($dir_temp."/".$pasta_rnd,0777))
							  {
								  $resposta->addAlert("Erro ao tentar criar a pasta rnd no servidor.");			
							  }		
						  }
						  
						  if(!is_dir($dir_zip))
						  {
							  if(!mkdir($dir_zip,0777))
							  {
								  $resposta->addAlert("Erro ao tentar criar a pasta temporária TEMPZIP no servidor.");			
							  }		
						  }
						  					  
						  $zip = new ZipArchive();
						  
						  $nome_arquivo_zip = "GRD_" . $numero_grd . ".zip";
	
						  $nome_arquivo_pdf = $diretorio_grd."/GRD_" . $numero_grd . ".pdf";
						  
						  if($zip->open($dir_zip."/".$nome_arquivo_zip, ZIPARCHIVE::CREATE)!==TRUE)
						  {
								$resposta->addAlert("Erro ao criar o arquivo zipado.");
						  }
						  
						  $zip->addFile($nome_arquivo_pdf,"GRD_" . $numero_grd . ".pdf");
						  
						  //Loop para criar um array com os arquivos selecionados
						  foreach($db->array_select as $reg_arquivos)
						  {
							  //compoe o nome do arquivo para copia
							  $nome_arquivo_ged_orig = DOCUMENTOS_GED . $reg_arquivos["base"] . "/" . $reg_arquivos["os"] . "/" . substr($reg_arquivos["os"],0,4) . DISCIPLINAS . $reg_arquivos["disciplina"] . "/" . $reg_arquivos["atividade"] . "/" . $reg_arquivos["sequencial"]."/".$reg_arquivos["nome_arquivo"];
							  
							  $zip->addFile($nome_arquivo_ged_orig,$reg_arquivos["nome_arquivo"]);
							  					  
							  //Atualizar o campo situação em ged_arquivos
							  $usql = "UPDATE ".DATABASE.".ged_arquivos SET ";
							  $usql .= "situacao = 1 ";
							  $usql .= "WHERE ged_arquivos.id_ged_arquivo = '" . $reg_arquivos["id_ged_arquivo"] . "' ";
							  $usql .= "AND ged_arquivos.reg_del = 0 ";
							  
							  $db->update($usql,'MYSQL');							  
						  }
						  
						  $zip->close();
						 						  
						  if($dados_form["txt_busca"])
						  {
							  $resposta->addScript("xajax_atualizatabela('" . $dados_form["txt_busca"] . "','busca'); ");
						  }
						  else
						  {
							  $resposta->addScript("xajax_atualizatabela(xajax.$('periodos').options[xajax.$('periodos').selectedIndex].value);");
						  }
						  
						  $resposta->addScript("xajax_mostraPacote('" . $dados_form["id_ged_pacote"] . "'); ");
						  
						  $resposta->addScript("xajax.$('btn_retorno').disabled=false;");

					  	  //APRESENTA A TELA PARA DOWNLOAD
						  $resposta->addScript("window.open('download.php?documento=" . $dir_zip."/".$nome_arquivo_zip . "');");
							
						  //remove a pasta temporaria
						  $et = exec("rm -rf ".$dir_temp."/".$pasta_rnd);

					  }
				  }
			  }	  
		  }
	}
	
	return $resposta;
}

//altera o numero do pacote
function alteraPacote($dados_form)
{
	$resposta = new xajaxResponse();

	$db = new banco_dados();
	
	//Verifica a qual OS pertence o pacote
	$sql = "SELECT id_os FROM ".DATABASE.".ged_pacotes ";
	$sql .= "WHERE ged_pacotes.reg_del = 0 ";
	$sql .= "AND ged_pacotes.id_ged_pacote = " . $dados_form["id_ged_pacote"] . " ";
	
	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	else
	{
		$reg_verifica_os = $db->array_select[0];
		
		$sql = "SELECT * FROM ".DATABASE.".ged_pacotes ";
		$sql .= "WHERE ged_pacotes.reg_del = 0 ";
		$sql .= "AND ged_pacotes.id_os = " . $reg_verifica_os["id_os"] . " ";
		$sql .= "AND ged_pacotes.numero_pacote = " . $dados_form["txt_num_pacote"] . " ";
		$sql .= "GROUP BY ged_pacotes.id_ged_pacote ";
		
		$db->select($sql,'MYSQL');

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
		else
		{
			if($db->numero_registros > 0)
			{
				$resposta->addAlert("ATENÇÃO: \n\nO número de pacote informado já existe para essa OS. Favor selecionar outro.");
				
				$resposta->addAssign("txt_num_pacote","value",$dados_form["hid_num_pacote"]);
				
			}
			else
			{	
				$usql = "UPDATE ".DATABASE.".ged_pacotes SET ";
				$usql .= "numero_pacote = '" . $dados_form["txt_num_pacote"] . "' ";
				$usql .= "WHERE id_ged_pacote = '" . $dados_form["id_ged_pacote"] . "' ";
				$usql .= "AND ged_pacotes.reg_del = 0 ";
				
				$db->update($usql,'MYSQL');

				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
				}
				else
				{			
					$resposta->addAlert("Número atualizado com sucesso.");
					
					if($dados_form["txt_busca"])
					{
						$resposta->addScript("xajax_atualizatabela('" . $dados_form["txt_busca"] . "','busca'); ");
					}
					else
					{
						$resposta->addScript("xajax_atualizatabela(xajax.$('periodos').options[xajax.$('periodos').selectedIndex].value);");
					}			
				}
				
				$resposta->addAssign("hid_num_pacote","value",$dados_form["txt_num_pacote"]);
			}
		}
	}

	return $resposta;
}

//incluido em 19/04/2016
//Carlos Abreu
function propriedades_prop($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados();
	
	$conteudo = '';
	
	$sql = "SELECT * FROM ".DATABASE.".codigos_devolucao ";
	$sql .= "WHERE codigos_devolucao.reg_del = 0 ";
	
	$db->select($sql,'MYSQL',true);
	
	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	else
	{
		foreach($db->array_select as $regs)
		{
			$array_devolucao[$regs["codigos_devolucao"]] = $regs["descricao_devolucao"];	
		}
	}
	
	$sql = "SELECT * FROM ".DATABASE.".ged_pacotes, ".DATABASE.".ged_versoes, ".DATABASE.".ged_arquivos, ".DATABASE.".setores, ".DATABASE.".solicitacao_documentos_detalhes, ".DATABASE.".numeros_interno, ".DATABASE.".ordem_servico ";
	$sql .= "WHERE ged_versoes.reg_del = 0 ";
	$sql .= "AND ged_pacotes.reg_del = 0 ";
	$sql .= "AND ged_arquivos.reg_del = 0 ";
	$sql .= "AND numeros_interno.reg_del = 0 ";
	$sql .= "AND solicitacao_documentos_detalhes.reg_del = 0 ";
	$sql .= "AND setores.reg_del = 0 ";
	$sql .= "AND ordem_servico.reg_del = 0 ";
	$sql .= "AND ged_arquivos.id_ged_arquivo = ged_versoes.id_ged_arquivo ";
	$sql .= "AND ordem_servico.id_os = numeros_interno.id_os ";
	$sql .= "AND numeros_interno.id_numero_interno = solicitacao_documentos_detalhes.id_numero_interno ";
	$sql .= "AND numeros_interno.id_numero_interno = ged_arquivos.id_numero_interno ";
	$sql .= "AND numeros_interno.id_disciplina = setores.id_setor ";
	$sql .= "AND ged_versoes.id_ged_arquivo = ged_arquivos.id_ged_arquivo ";
	$sql .= "AND ged_versoes.id_ged_versao = '" . $dados_form["id_ged_versao"] . "' ";
	$sql .= "AND ged_pacotes.id_ged_pacote = ged_versoes.id_ged_pacote ";
	$sql .= "GROUP BY ged_arquivos.id_ged_arquivo ";
	
	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	else
	{
		$regs = $db->array_select[0];
		
		$resposta->addAssign('div_titulo','innerHTML','Propriedades do arquivo: '. PREFIXO_DOC_GED . sprintf("%05d",$regs["os"]) . '-' . $regs["sigla"] . '-' .$regs["sequencia"]);
		
		$resposta->addAssign('div_voltar','innerHTML','<input type="button" value="Voltar" onclick=divPopupInst.destroi();xajax_mostraPacote('.$regs["id_ged_pacote"].')>');	
		
		$conteudo .= '<table border="0" width="100%">';
		
		$conteudo .= '<tr>';
		$conteudo .= '<td width="5%"><label class="labels">Título&nbsp;1</label><br><input type="text" name="tag" id="tag" class="caixa" value="' . $regs["tag"] . '" size="40"></td>';
		$conteudo .= '<td width="5%"><label class="labels">Título&nbsp;2</label><br><input type="text" name="tag2" id="tag2" class="caixa" value="' . $regs["tag2"] . '" size="40"></td>';
		$conteudo .= '</tr>';
		
		$conteudo .= '<tr>';
		$conteudo .= '<td width="5%"><label class="labels">Título&nbsp;3</label><br><input type="text" name="tag3" id="tag3" class="caixa" value="' . $regs["tag3"] . '" size="40"></td>';
		$conteudo .= '<td width="5%"><label class="labels">Título&nbsp;4</label><br><input type="text" name="tag4" id="tag4" class="caixa" value="' . $regs["tag4"] . '" size="40"></td>';
		$conteudo .= '</tr>';
	
		$conteudo .= '<tr>';
		$conteudo .= '<td width="5%"><label class="labels">data&nbsp;devolução</label><br><input name="data_devolucao" type="text" class="caixa" id="data_devolucao" size="10" onkeypress=transformaData(this, event); value="'.mysql_php($regs["data_devolucao"]).'"></td>';
		$conteudo .= '<td width="5%"><label class="labels">status&nbsp;devolução</label><br>';
		
		  $conteudo .= '<select name="status_devolucao" id="status_devolucao" class="caixa">';
		  $conteudo .= '<option value="">SELECIONE</option>';
		  
		  foreach($array_devolucao as $cod=>$descricao)
		  {
			  if($regs["status_devolucao"]==$cod)
			  {
					$select = 'selected';  
			  }
			  else
			  {
					$select = '';  
			  }
			  
			  $conteudo .= '<option value='.$cod.' '.$select.'>'.$descricao.'</option>';
		  }
		  
		  $conteudo .= '</select>';
		  
		$conteudo .= '<input type="hidden" name="id_ged_versao" id="id_ged_versao" value="'.$regs["id_ged_versao"].'">';
		
		$conteudo .= '</td>';
			
		$conteudo .= '</tr>';
		
		$conteudo .= "</table>";
		
		$conteudo .= '<input class="class_botao" type="button" value="Alterar Propriedades" onclick=xajax_atualizaPropriedades(xajax.getFormValues("frm_prop"));>&nbsp;&nbsp;';
		
		$conteudo .= '<input class="class_botao" type="button" value="Voltar" onclick=divPopupInst.destroi();>';
		
		$resposta->addAssign("div_propriedades","innerHTML",$conteudo);
	}
		
	return $resposta;	
}

//incluido em 19/04/2016
//Carlos Abreu
function propriedades_versoes($dados_form)
{
	$resposta = new xajaxResponse();
	
	$xml = new XMLWriter();
	
	$db = new banco_dados();

	//seleciona o autor
	$sql = "SELECT id_funcionario, nome_usuario FROM ".DATABASE.".funcionarios ";
	$sql .= "WHERE funcionarios.reg_del = 0 ";
	$sql .= "ORDER BY funcionarios.funcionario ";
	
	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	else
	{
		foreach($db->array_select as $regs)
		{
			$nome_funcionario[$regs["id_funcionario"]] = $regs["nome_usuario"];
		}
	}

	$sql = "SELECT id_ged_arquivo, id_ged_versao FROM ".DATABASE.".ged_versoes ";
	$sql .= "WHERE ged_versoes.reg_del = 0 ";
	$sql .= "AND ged_versoes.id_ged_versao = '".$dados_form["id_ged_versao"]."' ";
	
	$db->select($sql,'MYSQL',true);
	
	$regs = $db->array_select[0];	

	$sql = "SELECT *, ged_versoes.nome_arquivo, ged_arquivos.descricao, ged_arquivos.id_autor AS autor, ged_arquivos.id_editor AS editor FROM ".DATABASE.".ged_arquivos, ".DATABASE.".ged_versoes ";
	$sql .= "WHERE ged_arquivos.reg_del = 0 ";
	$sql .= "AND ged_versoes.reg_del = 0 ";
	$sql .= "AND ged_arquivos.id_ged_arquivo = ged_versoes.id_ged_arquivo ";
	$sql .= "AND ged_arquivos.id_ged_arquivo = '" . $regs["id_ged_arquivo"] . "' ";
	$sql .= "ORDER BY ged_versoes.id_ged_versao DESC ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	else
	{
		$xml->openMemory();
		$xml->setIndent(false);
		$xml->startElement('rows');
					
		foreach($db->array_select as $reg_versoes)
		{					
			$extensao_array = explode(".",basename($reg_versoes["nome_arquivo"]));
					
			$extensao = $extensao_array[count($extensao_array)-1];
			
			if($regs["id_ged_versao"] == $reg_versoes["id_ged_versao"])
			{
				$ver = 'Versão&nbsp;do&nbsp;pacote';
				
				$img = '<img style="cursor:pointer;" src="'.DIR_IMAGENS.'bt_busca.png" onclick=xajax_abrir("ARQ_' . $reg_versoes["id_ged_versao"] . '");>';
			
				$id = 'ARQ_'.$reg_versoes["id_ged_versao"];
			}
			else
			{
				$ver = '&nbsp;';
				
				$img = '<img style="cursor:pointer;" src="'.DIR_IMAGENS.'bt_busca.png" onclick=xajax_abrir("ARQ_' . $reg_versoes["id_ged_versao"] . '_VER");>';
				
				$id = 'ARQ_'.$reg_versoes["id_ged_versao"].'_VER';
			}
			
			$xml->startElement('row');
				$xml->writeAttribute('id',$id);
				$xml->startElement ('cell');
					$xml->text(retornaImagem($extensao));
				$xml->endElement();
				$xml->startElement ('cell');
					$xml->text(basename(addslashes($reg_versoes["arquivo"])));
				$xml->endElement();
				$xml->startElement ('cell');
					$xml->text('<input type="hidden" id="rev_dvm_' . $reg_versoes["id_ged_versao"] . '" name="rev_dvm_' . $reg_versoes["id_ged_versao"] . '" value="' . $reg_versoes["revisao_interna"] . '"><input type="text" class="caixa" name="text_rev_dvm_' . $reg_versoes["id_ged_versao"] . '" value="' . $reg_versoes["revisao_interna"] . '" style="width:100%;" onblur=if(xajax.$("rev_dvm_' . $reg_versoes["id_ged_versao"] . '").value!==this.value){if(confirm("Deseja&nbsp;atualizar&nbsp;a&nbsp;Revisão&nbsp;Interna&nbsp;"+xajax.$("rev_dvm_' . $reg_versoes["id_ged_versao"] . '").value+"&nbsp;para&nbsp;"+this.value+"?")){xajax.$("rev_dvm_' . $reg_versoes["id_ged_versao"] . '").value=this.value;xajax_atualiza_campos("revisao_interna",this.value,'.$reg_versoes["id_ged_versao"].');}else{this.value=xajax.$("rev_dvm_' . $reg_versoes["id_ged_versao"] . '").value;}}>');
				$xml->endElement();
				$xml->startElement ('cell');
					$xml->text($reg_versoes["versao_"]);
				$xml->endElement();
				$xml->startElement ('cell');
					$xml->text('<input type="hidden" id="rev_cliente_' . $reg_versoes["id_ged_versao"] . '" name="rev_cliente_' . $reg_versoes["id_ged_versao"] . '" value="' . $reg_versoes["revisao_cliente"] . '"><input type="text" class="caixa" name="text_rev_cliente_' . $reg_versoes["id_ged_versao"] . '" value="' . $reg_versoes["revisao_cliente"] . '" style="width:100%;" onblur=if(xajax.$("rev_cliente_' . $reg_versoes["id_ged_versao"] . '").value!==this.value){if(confirm("Deseja&nbsp;atualizar&nbsp;a&nbsp;Revisão&nbsp;Cliente&nbsp;"+xajax.$("rev_cliente_' . $reg_versoes["id_ged_versao"] . '").value+"&nbsp;para&nbsp;"+this.value+"?")){xajax.$("rev_cliente_' . $reg_versoes["id_ged_versao"] . '").value=this.value;xajax_atualiza_campos("revisao_cliente",this.value,'.$reg_versoes["id_ged_versao"].');}else{this.value=xajax.$("rev_cliente_' . $reg_versoes["id_ged_versao"] . '").value;}}>');
				$xml->endElement();
				$xml->startElement ('cell');
					$xml->text(addslashes($nome_funcionario[$reg_versoes["autor"]]));
				$xml->endElement();
				$xml->startElement ('cell');
					$xml->text(addslashes($nome_funcionario[$reg_versoes["editor"]]));
				$xml->endElement();
				$xml->startElement ('cell');
					$xml->text($ver);
				$xml->endElement();
				$xml->startElement ('cell');
					$xml->text($img);
				$xml->endElement();						
			$xml->endElement();					
		}
		
		$xml->endElement();
				
		$conteudo_versao = $xml->outputMemory(false);	
		
		$resposta->addScript("grid('conteudo_versoes',true,'250','".$conteudo_versao."');");
	}

	return $resposta;	
}

//incluido em 19/04/2016
//Carlos Abreu
function propriedades_comentarios($dados_form)
{
	$resposta = new xajaxResponse();
	
	$xml = new XMLWriter();
	
	$db = new banco_dados();
	
	//seleciona o autor
	$sql = "SELECT id_funcionario, nome_usuario FROM ".DATABASE.".funcionarios ";
	$sql .= "WHERE funcionarios.reg_del = 0 ";
	$sql .= "ORDER BY funcionarios.funcionario ";
	
	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	else
	{
		foreach($db->array_select as $regs)
		{
			$nome_funcionario[$regs["id_funcionario"]] = $regs["nome_usuario"];
		}
	}
	
	//Seleciona os dados dos arquivos de comentários
	$sql = "SELECT * FROM ".DATABASE.".ged_comentarios ";
	$sql .= "WHERE ged_comentarios.reg_del = 0 ";
	$sql .= "AND ged_comentarios.id_ged_versao = '" . $dados_form["id_ged_versao"] . "' ";
	$sql .= "ORDER BY ged_comentarios.id_ged_comentario DESC ";				
	
	$db->select($sql,'MYSQL',true);
	
	$num_coment = $db->numero_registros;

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	else
	{				
		$xml->openMemory();
		$xml->setIndent(false);
		$xml->startElement('rows');
		
		$conteudo_coment = "";		
		
		foreach($db->array_select as $reg_coment)
		{
			$extensao_array = explode(".",basename($reg_coment["strarquivo"]));
					
			$extensao = $extensao_array[count($extensao_array)-1];
			
			$id = 'COM_' . $reg_coment["id_ged_comentario"];
			
			$xml->startElement('row');
				$xml->writeAttribute('id',$id);

				$xml->startElement ('cell');
					$xml->text(preg_replace('/\s/',' ',$reg_coment["comentario"]));
				$xml->endElement();
				
				$xml->startElement ('cell');
					$xml->text($nome_funcionario[$reg_coment["id_funcionario"]]);
				$xml->endElement();
				
				if($reg_coment["strarquivo"]!='')
				{				
					$xml->startElement ('cell');
						$xml->text(retornaImagem($extensao).'&nbsp;'.addslashes($reg_coment["nome_arquivo"]));
					$xml->endElement();
					$xml->startElement ('cell');
						$xml->text('<img src="'.DIR_IMAGENS.'bt_busca.png" style="cursor:pointer;" alt="Abrir&nbsp;arquivo&nbsp;de&nbsp;comentário" onclick=xajax_abrir("COM_' . $reg_coment["id_ged_comentario"] .'")>');
					$xml->endElement();

				}
				else
				{
					$xml->startElement ('cell');
						$xml->text('&nbsp;');
					$xml->endElement();
					$xml->startElement ('cell');
						$xml->text('&nbsp;');
					$xml->endElement();
				}
				
				$xml->startElement ('cell');
					$xml->text('<img src="'.DIR_IMAGENS.'apagar.png" style="cursor:pointer;" alt="Remover&nbsp;arquivo&nbsp;de&nbsp;comentário" onclick=if(confirm("ATEN&Ccedil;&Atilde;O:&nbsp;O&nbsp;arquivo&nbsp;de&nbsp;comentário&nbsp;será&nbsp;exclu&iacute;do&nbsp;definitivamente.&nbsp;Deseja&nbsp;continuar?")){xajax_excluir_comentario(' . $reg_coment["id_ged_comentario"] . ');}>');
				$xml->endElement();
				
			$xml->endElement();
		}					
		
		$xml->endElement();
				
		$conteudo_coment = $xml->outputMemory(false);
		
		$resposta->addScript("grid('div_comentarios_existentes',true,'100','".$conteudo_coment."');");
	}

	return $resposta;	
}

//incluido em 19/04/2016
//Carlos Abreu
function propriedades_desbloqueios($dados_form)
{
	$resposta = new xajaxResponse();
	
	$xml = new XMLWriter();
	
	$db = new banco_dados();
	
	//seleciona o autor
	$sql = "SELECT id_funcionario, nome_usuario FROM ".DATABASE.".funcionarios ";
	$sql .= "WHERE funcionarios.reg_del = 0 ";
	$sql .= "ORDER BY funcionarios.funcionario ";
	
	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	else
	{
		foreach($db->array_select as $regs)
		{
			$nome_funcionario[$regs["id_funcionario"]] = $regs["nome_usuario"];
		}
	}
	
	$sql = "SELECT * FROM ".DATABASE.".codigos_devolucao ";
	$sql .= "WHERE codigos_devolucao.reg_del = 0 ";
	
	$db->select($sql,'MYSQL',true);
	
	if ($db->erro != '')
	{
		$resposta->addAlert("Erro ao tentar selecionar os dados dos arquivos: " . $db->erro);
	}
	
	foreach($db->array_select as $regs)
	{
		$array_devolucao[$regs["codigos_devolucao"]] = $regs["descricao_devolucao"];
	}	
	
	$sql = "SELECT * FROM ".DATABASE.".ged_desbloqueios ";
	$sql .= "WHERE ged_desbloqueios.reg_del = 0 ";
	$sql .= "AND ged_desbloqueios.id_ged_versao = '" . $dados_form["id_ged_versao"] . "' ";
	
	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	else
	{					
		$conteudo_desbloq = "";
		
		$xml->openMemory();
		$xml->setIndent(false);
		$xml->startElement('rows');
		
		foreach($db->array_select as $reg_desbloq)
		{
			$id = 'DES_' . $reg_desbloq["id_ged_desbloqueio"];
			
			$apr = '<img src="'.DIR_IMAGENS.'accept.png"  style="cursor:pointer;" title="Desbloquear" onclick=if(confirm("Deseja&nbsp;aprovar&nbsp;o&nbsp;desbloqueio?")){xajax_desbloquear("'.$reg_desbloq["id_ged_versao"].'",0)};>';
			
			$exc = '<img src="'.DIR_IMAGENS.'apagar.png"  style="cursor:pointer;" title="Recusar" onclick=if(confirm("Deseja&nbsp;excluir&nbsp;o&nbsp;desbloqueio?")){xajax_desbloquear("'.$reg_desbloq["id_ged_versao"].'",1)};>';
			
			$xml->startElement('row');
				$xml->writeAttribute('id',$id);
				$xml->startElement ('cell');
					$xml->text(preg_replace('/\s/',' ',$reg_desbloq["motivo_desbloqueio"]));
				$xml->endElement();
				
				if($reg_desbloq["strarquivo"]!='')
				{
					$extensao_array = explode(".",basename($reg_desbloq["strarquivo"]));
							
					$extensao = $extensao_array[count($extensao_array)-1];
									
					$xml->startElement ('cell');
						$xml->text(retornaImagem($extensao).'&nbsp;'.addslashes($reg_desbloq["nome_arquivo"]));
					$xml->endElement();
				}
				else
				{
					$xml->startElement ('cell');
						$xml->text('&nbsp;');
					$xml->endElement();					
				}
				
				$xml->startElement ('cell');
					$xml->text($array_devolucao[$reg_desbloq["status_devolucao"]]);
				$xml->endElement();
				
				$xml->startElement ('cell');
					$xml->text(mysql_php($reg_desbloq["data_devolucao"]));
				$xml->endElement();		
				
				$xml->startElement ('cell');
					$xml->text(addslashes($nome_funcionario[$reg_desbloq["id_funcionario_solicitante"]]));
				$xml->endElement();
				
				$xml->startElement ('cell');
					$xml->text(mysql_php($reg_desbloq["data_solicitacao"]));
				$xml->endElement();	
								
				$xml->startElement ('cell');
					$xml->text($apr);
				$xml->endElement();
				
				$xml->startElement ('cell');
					$xml->text($exc);
				$xml->endElement();
				
			$xml->endElement();
								
		}		
		
		$xml->endElement();
				
		$conteudo_desbloq = $xml->outputMemory(false);
		
		$resposta->addScript("grid('div_desbloqueios',true,'250','".$conteudo_desbloq."');");
	}

	return $resposta;	
}


function retornaCliente($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados();
	
	//lista de usuarios para e-mail
	$sql = "SELECT funcionarios.id_funcionario, funcionario, email FROM ".DATABASE.".funcionarios, ".DATABASE.".usuarios ";
	$sql .= "WHERE funcionarios.id_funcionario = usuarios.id_funcionario ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	$sql .= "AND usuarios.reg_del = 0 ";
	$sql .= "AND funcionarios.situacao = 'ATIVO' ";
	
	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	
	foreach($db->array_select as $regs)
	{
		  $array_func[$regs["id_funcionario"]] = $regs["funcionario"];
		  
		  $array_email[$regs["id_funcionario"]] = $regs["email"]; 
	}	
	
	foreach($dados_form as $chave=>$valor)
	{
		if(substr($chave,0,11)=="chk_arquivo")
		{
			$array_arquivos[] = substr($chave,strrpos($chave,"_")+1,strlen($chave)-strrpos($chave,"_"));
		}
		
		//pega os arquivos checados, que irão desbloquear
		if(substr($chave,0,6)=="arqnum" && $valor == 1)
		{
			$array_arqnum[substr($chave,strrpos($chave,"_")+1,strlen($chave)-strrpos($chave,"_"))] = substr($chave,strrpos($chave,"_")+1,strlen($chave)-strrpos($chave,"_"));	
		}			
	}
	
	$filtro_arquivos = implode($array_arquivos,"','");

	//Pega os arquivos do pacote
	$sql = "SELECT *, ged_arquivos.id_ged_arquivo, ged_versoes.arquivo, ged_versoes.id_ged_versao FROM ".DATABASE.".numeros_interno, ".DATABASE.".ged_versoes, ".DATABASE.".ged_arquivos ";
	$sql .= "WHERE ged_versoes.reg_del = 0 ";
	$sql .= "AND ged_arquivos.reg_del = 0 ";
	$sql .= "AND numeros_interno.reg_del = 0 ";
	$sql .= "AND ged_versoes.id_ged_arquivo = ged_arquivos.id_ged_arquivo ";
	$sql .= "AND ged_versoes.id_ged_versao = ged_arquivos.id_ged_versao ";
	$sql .= "AND ged_arquivos.id_numero_interno = numeros_interno.id_numero_interno ";
	$sql .= "AND ged_versoes.id_ged_arquivo IN ('" . $filtro_arquivos . "') ";
	
	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	else
	{	
		foreach($db->array_select as $reg_pacotes)
		{
			//ATUALIZA O CAMPO DE FLAG DE ENVIO EMAIL DE ARQUIVOS RETORNADOS
			$usql = "UPDATE ".DATABASE.".numeros_interno SET ";
			$usql .= "flag_numero_avisos = 0 ";
			$usql .= "WHERE numeros_interno.id_numero_interno = '" . $reg_pacotes["id_numero_interno"] . "' ";
			$usql .= "AND numeros_interno.reg_del = 0 ";
				
			$db->update($usql,'MYSQL');	
			
			$usql = "UPDATE ".DATABASE.".ged_arquivos SET ";
			$usql .= "id_ged_versao = '" . $reg_pacotes["id_ged_versao"] . "', ";
			$usql .= "situacao = 0, ";
			
			//se o arquivo estiver checkado (Finalidade <> CE)
			if(in_array($reg_pacotes["id_ged_arquivo"],$array_arqnum))
			{
				//se checkado, desbloqueia
				$usql .= "status = 0 ";				
			}
			else
			{	
				//mantem bloqueado	
				$usql .= "status = 2 ";		
			}
			
			$usql .= "WHERE ged_arquivos.id_ged_arquivo = '" . $reg_pacotes["id_ged_arquivo"] . "' ";
			$usql .= "AND ged_arquivos.reg_del = 0 ";
				
			$db->update($usql,'MYSQL');
			
			//verifica os desbloqueios
			$array_retorno[] = verifica_desbloqueio($reg_pacotes["id_ged_versao"],0);	
			
			//acrescentado por carlos abreu 24/05/2010
			//2 - retorno do cliente - não altera versao_documento - habilita o incremento da revisão no check-out
			$usql = "UPDATE ".DATABASE.".ged_versoes SET ";
			$usql .= "retorno = 2 ";
			$usql .= "WHERE ged_versoes.id_ged_versao = '".$reg_pacotes["id_ged_versao"]."' ";
			$usql .= "AND ged_versoes.reg_del = 0 ";
			
			$db->update($usql,'MYSQL');				
		}
		
		$sql = "SELECT *, ged_pacotes.id_autor as pct_id_autor FROM ".DATABASE.".ged_pacotes, ".DATABASE.".ordem_servico, ".DATABASE.".numeros_interno, ".DATABASE.".setores, ".DATABASE.".ged_versoes
                LEFT JOIN ".DATABASE.".ged_comentarios ON ged_comentarios.id_ged_versao = ged_versoes.id_ged_versao AND ged_comentarios.reg_del = 0, ".DATABASE.".ged_arquivos ";
		$sql .= "WHERE numeros_interno.reg_del = 0 ";
		$sql .= "AND ged_pacotes.reg_del = 0 ";
		$sql .= "AND ged_arquivos.reg_del = 0 ";
		$sql .= "AND ged_versoes.reg_del = 0 ";
		$sql .= "AND ordem_servico.reg_del = 0 ";
		$sql .= "AND ordem_servico.id_os = numeros_interno.id_os ";
		$sql .= "AND numeros_interno.id_numero_interno = ged_arquivos.id_numero_interno ";
		$sql .= "AND numeros_interno.id_disciplina = setores.id_setor ";
		$sql .= "AND ged_arquivos.id_ged_arquivo = ged_versoes.id_ged_arquivo ";
		$sql .= "AND ged_versoes.id_ged_pacote = ged_pacotes.id_ged_pacote ";
		$sql .= "AND ged_pacotes.id_ged_pacote = '" . $dados_form["id_ged_pacote"] . "' ";
		
		$db->select($sql,'MYSQL',true);

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
		else
		{		
			$reg_pacote = $db->array_select[0];
		
			$usql = "UPDATE ".DATABASE.".ged_pacotes SET ";
			$usql .= "ged_pacotes.status = 0 ";
			$usql .= "WHERE ged_pacotes.id_ged_pacote = '" . $dados_form["id_ged_pacote"] . "' ";
			$usql .= "AND ged_pacotes.reg_del = 0 ";
		
			$db->update($usql,'MYSQL');	
		
			$resposta->addScript("xajax_mostraPacote('" . $dados_form["id_ged_pacote"] . "'); ");
			
			//Forma o e-mail
			$params 			= array();
			$params['from']		= 'arqtec@dominio.com.br';
			$params['from_name']= 'Arquivo Tecnico';
			
			$params['subject'] = "RETORNO DE PACOTE - OS: " . sprintf("%05d",$reg_pacote["os"]) . " - Pacote: " . sprintf("%04d",$reg_pacote["numero_pacote"]).' COM COMENTARIOS';
			
			//arquivo técnico
			$params['emails']['to'][] = array('email' => "arqtec@dominio.com.br", 'nome' => "Arquivo Técnico");
			
			//autor do pacote
			if($reg_usuario["email"]!='')
			{
				$params['emails']['to'][] = array('email' => $array_email[$reg_pacote["pct_id_autor"]], 'nome' => $array_func[$reg_pacote["pct_id_autor"]]);
			}
			
			//Carlos Eduardo: 27/02/2018
			if($array_usremail[$reg_pacote['id_cod_coord']]!='')
			{
			    //COORDENADOR DO PROJETO
			    $params['emails']['to'][] = array('email' => $array_usremail[$reg_pacote['id_cod_coord']], 'nome' => $array_usrlogin[$reg_pacote['id_cod_coord']]);
			}
			
			//Carlos Eduardo: 23/01/2018
			$alocados = ProtheusDao::getAlocadosOS($reg_pacote['OS'], true, $reg_pacote['id_setor']);
			//TODOS OS ALOCADOS
			foreach($alocados as $alocado)
			{
			    if(!empty($alocado['email']))
			    {
			        $params['emails']['to'][] = array('email' => $alocado['email'], 'nome' => $alocado['Login']);
			    }
			}
			
			//$params['emails']['to'][] = array('email' => 'planejamento@dominio.com.br', 'nome' => 'Planejamento');
			
			//Adiciona a mensagem do corpo do e-mail
			$corpoEmail = "<html><body style='font: 11pt Arial'><p>O seguinte pacote foi retornado do cliente e os arquivos estão liberados no sistema:</p><div id='div_solicitante'>Solicitante: <a href='mailto:" . $array_email[$reg_pacote["pct_id_autor"]] . "'>" . $array_func[$reg_pacote["pct_id_autor"]] . "</a></div><div id='div_data'>data da solicitação: " . mysql_php($reg_pacote["data"]) . "</div></body></html>";
			
			$mail = new email($params);
			
			$mail->montaCorpoEmail($corpoEmail);

			if(!$mail->Send())
			{
				$resposta->addAlert('Erro ao enviar e-mail! '.$mail->ErrorInfo);
			}
		
			$resposta->addAlert("Arquivo retornado com sucesso.");
			
			foreach($array_retorno as $array_solicitantes=>$array_id)
			{
				$solicitante[] = $array_id["solicitante"];				
			}
			
			//há solicitantes
			if(count($solicitante)>0)
			{
				$params 			= array();
				$params['from']		= 'arqtec@dominio.com.br';
				$params['from_name']= 'Arquivo Tecnico';
				
				$params['subject'] = $reg_pacote["descricao"]." - DOCUMENTO DESBLOQUEADO: ";
				
				//GRUPO ARQUIVO TECNICO
				$params['emails']['to'][] = array('email' => "arqtec@dominio.com.br", 'nome' => "Grupo Arquivo Técnico");
				
				//EDITOR DO ARQUIVO
				if($array_email[$reg_pacote["id_editor"]]!="")
				{
					$params['emails']['to'][] = array('email' => $array_email[$reg_pacote["id_editor"]], 'nome' => $array_func[$reg_pacote["id_editor"]]);
				}
				
				//SOLICITANTE DO DESBLOQUEIO
				foreach($solicitante as $id_funcionario)
				{
					if($array_email[$id_funcionario]!="")
					{
						$params['emails']['to'][] = array('email' => $array_email[$id_funcionario], 'nome' => $array_func[$id_funcionario]);
					}
				}
				
				//Carlos Eduardo: 27/02/2018
				if($array_usremail[$reg_pacote['id_cod_coord']]!='')
				{
				    //COORDENADOR DO PROJETO
				    $params['emails']['to'][] = array('email' => $array_usremail[$reg_pacote['id_cod_coord']], 'nome' => $array_usrlogin[$reg_pacote['id_cod_coord']]);
				}
				
				//Carlos Eduardo: 23/01/2018
				$alocados = ProtheusDao::getAlocadosOS($reg_pacote['OS'], true, $reg_pacote['id_setor']);
				//TODOS OS ALOCADOS
				foreach($alocados as $alocado)
				{
				    if(!empty($alocado['email']))
				    {
				        $params['emails']['to'][] = array('email' => $alocado['email'], 'nome' => $alocado['Login']);
				    }
				}
				
// 				$params['emails']['to'][] = array('email' => 'planejamento@dominio.com.br', 'nome' => 'Planejamento');
//  				$params['emails']['cco'][] = array('email' => 'carlos.maximo@dominio.com.br', 'nome' => 'Carlos Maximo');
				
				$str_mensagem = "<p>O seguinte documento foi desbloqueado no sistema devido ao retorno do cliente: </p>";
				$str_mensagem .= "<p>" . $reg_pacote["descricao"] . "</p>";
				$str_mensagem .= "<p>Editor: " . $array_func[$reg_pacote["id_editor"]] . "</p>";
				$str_mensagem .= "<p>Desbloqueio por: " . $array_func[$_SESSION["id_funcionario"]] . " em " . date("d/m/Y - H:i:s") . "</p>";
				
				//Carlos Eduardo: 23/01/2018
				$str_mensagem .= "<p>Solicitante desbloqueio: " . $array_usrlogin[$array_resultado["solicitante"]] . "</p>";
				$str_mensagem .= "<p>Motivo do desbloqueio: " . $reg_pacote["comentario"] . "</p>";
				
				$str_mensagem .= "<p>&nbsp;</p>";
				$str_mensagem .= "<p>O mesmo agora se encontra livre no sistema para edição.</p>";
					
				$corpoEmail = "<html><body>" . $str_mensagem . "</body></html>";	 
				 
				$mail = new email($params);
				
				$mail->montaCorpoEmail($corpoEmail);

				if(!$mail->Send())
				{
					$resposta->addAlert('Erro ao enviar e-mail!!! '.$mail->ErrorInfo);
				}
			}				
		
			if($dados_form["txt_busca"])
			{
				$resposta->addScript("xajax_atualizatabela('" . $dados_form["txt_busca"] . "','busca'); ");
			}
			else
			{
				$resposta->addScript("xajax_atualizatabela(xajax.$('periodos').options[xajax.$('periodos').selectedIndex].value);");
			}
		
			$resposta->addScript("xajax_mostraPacote('" . $dados_form["id_ged_pacote"] . "'); ");
		}
	}

	return $resposta;
}

//REVISAR ESTA FUNÇÃO, COLOCAR NA GRID PRINCIPAL
//19/09/2014 
function preencheGRDs($id_ged_pacote)
{
	$resposta = new xajaxResponse();
	
	$xml = new XMLWriter();

	$db = new banco_dados();
	
	$conteudo = "";
	
	$xml->openMemory();
	$xml->setIndent(false);
	$xml->startElement('rows');

	$sql = "SELECT *, grd.data_emissao as data_grd, grd.status AS grd_status FROM ".DATABASE.".grd, ".DATABASE.".ged_pacotes, ".DATABASE.".ged_versoes, ".DATABASE.".ged_arquivos, ".DATABASE.".numeros_interno, ".DATABASE.".ordem_servico ";
	$sql .= "WHERE grd.reg_del = 0 ";
	$sql .= "AND ged_pacotes.reg_del = 0 ";
	$sql .= "AND ged_versoes.reg_del = 0 ";
	$sql .= "AND ged_arquivos.reg_del = 0 ";
	$sql .= "AND numeros_interno.reg_del = 0 ";
	$sql .= "AND ordem_servico.reg_del = 0 ";
	$sql .= "AND grd.id_ged_pacote = ged_pacotes.id_ged_pacote ";
	$sql .= "AND ged_pacotes.id_ged_pacote = ged_versoes.id_ged_pacote ";
	$sql .= "AND ged_versoes.id_ged_arquivo = ged_arquivos.id_ged_arquivo ";
	$sql .= "AND ged_arquivos.id_numero_interno = numeros_interno.id_numero_interno ";
	$sql .= "AND numeros_interno.id_os = ordem_servico.id_os ";
	$sql .= "AND grd.id_ged_pacote = '" . $id_ged_pacote . "' ";
	$sql .= "GROUP BY grd.id_grd ";
	$sql .= "ORDER BY grd.id_grd ";
	
	$db->select($sql,'MYSQL',true);

	foreach($db->array_select as $reg_grd)
	{		
		if($reg_grd["grd_status"]=="1")
		{
			$st = '<img src="'.DIR_IMAGENS.'application_form_delete.png" style="cursor:pointer;" title="Clique&nbsp;para&nbsp;marcar&nbsp;essa&nbsp;GRD&nbsp;como&nbsp;cancelada" onclick=if(confirm("Deseja&nbsp;marcar&nbsp;essa&nbsp;GRD&nbsp;como&nbsp;cancelada?")){xajax_cancelarGRD("' . $reg_grd["id_grd"] . '");}>';
		}
		else
		{
			$st = '&nbsp;';			
		}
		
		$vis = '<img src="'.DIR_IMAGENS.'bt_busca.png" style="cursor:pointer;" title="Visualizar&nbsp;GRD" onclick=window.open("relatorios/rel_ged_grd.php?id_grd=' . $reg_grd["id_grd"] . '");>';
		
		$del = '<img src="'.DIR_IMAGENS.'apagar.png" style="cursor:pointer;" title="Clique&nbsp;para&nbsp;excluir&nbsp;definitivamente&nbsp;essa&nbsp;GRD" onclick=if(confirm("Deseja&nbsp;excluir&nbsp;a&nbsp;GRD?")){xajax_excluirGRD("' . $reg_grd["id_grd"] . '");}>';
		
		$xml->startElement('row');
			$xml->writeAttribute('id',$reg_grd["id_grd"]);
			$xml->startElement ('cell');
				$xml->text(sprintf("%05d",$reg_grd["os"]) . '-'. sprintf("%04d",$reg_grd["numero_pacote"]));
			$xml->endElement();
			$xml->startElement ('cell');
				$xml->text(mysql_php($reg_grd["data_grd"]));
			$xml->endElement();
			$xml->startElement ('cell');
				$xml->text($vis);
			$xml->endElement();
			$xml->startElement ('cell');
				$xml->text($st);
			$xml->endElement();
			$xml->startElement ('cell');
				$xml->text($del);
			$xml->endElement();
			
		$xml->endElement();
	}

	$sql = "SELECT * FROM ".DATABASE.".ged_pacotes ";
	$sql .= "WHERE ged_pacotes.id_ged_pacote = '" . $id_ged_pacote . "' ";
	$sql .= "AND ged_pacotes.reg_del = 0 ";
	
	$db->select($sql,'MYSQL',true);
	
	$reg_pacote = $db->array_select[0];

	$resposta->addAssign("div_titulo","innerHTML","Pacote: " . sprintf("%04d",$reg_pacote["numero_pacote"]));
	
	$xml->endElement();
			
	$conteudo = $xml->outputMemory(false);							
								
	$resposta->addScript("grid('conteudo_grds',true,'250','".$conteudo."');");

	return $resposta;
}

//atualiza os campos dos aquivos
function atualiza_campos($campo, $valor, $id_ged_versao)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados();
	
	$array_numcli = NULL;
	
	$texto = "";	
	
	if($id_ged_versao!='' || $id_ged_versao!=0)
	{
		//Passa no array que contém as informações do POST
		switch ($campo)
		{	
			case 'revisao_interna':

				$usql = "UPDATE ".DATABASE.".ged_versoes SET ";
				$usql .= "ged_versoes.revisao_interna = '" . trim($valor) . "' ";
				$usql .= "WHERE ged_versoes.id_ged_versao = '".$id_ged_versao."' ";
				$usql .= "AND ged_versoes.reg_del = 0 ";
		
				$db->update($usql,'MYSQL');

				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
				}	
				
			break;
			
			case 'numero_cliente':
			
				$sql = "SELECT id_os FROM ".DATABASE.".numeros_interno, ".DATABASE.".ged_arquivos, ".DATABASE.".ged_versoes ";
				$sql .= "WHERE numeros_interno.reg_del = 0 " ;
				$sql .= "AND ged_arquivos.reg_del = 0 ";
				$sql .= "AND ged_versoes.reg_del = 0 ";
				$sql .= "AND numeros_interno.id_numero_interno = ged_arquivos.id_numero_interno ";
				$sql .= "AND ged_arquivos.id_ged_arquivo = ged_versoes.id_ged_arquivo ";
				$sql .= "AND ged_versoes.id_ged_versao = '".$id_ged_versao."' ";
				
				$db->select($sql,'MYSQL',true);

				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
					
					return $resposta;
				}
				else
				{
					$cont = $db->array_select[0];
					
					//alterado em 26/06/2014
					//George chamado #626
					$array_numcli = verifica_numcliente(trim($valor),$cont["id_os"]);
					
					if(!is_null($array_numcli))
					{
						foreach($array_numcli as $numero_dvm)
						{
							$texto .= $numero_dvm . "\n";
						}
						
						$resposta->addAlert("Já existe(m) este(s) número(s) de cliente cadastrado no(s) seguinte(s) documento(s):".chr(13).$texto.chr(13)."e não será atualizado");
						
						return $resposta;
					}
					else
					{
						//Atualiza o numero_cliente da tabela de NumCliente
						$usql = "UPDATE ".DATABASE.".numeros_interno, ".DATABASE.".solicitacao_documentos_detalhes, ".DATABASE.".ged_arquivos, ".DATABASE.".ged_versoes SET ";
						$usql .= "numeros_interno.numero_cliente = '" . trim(addslashes(maiusculas($valor))) . "', ";
						$usql .= "solicitacao_documentos_detalhes.numero_cliente = '" . trim(addslashes(maiusculas($valor))) . "' ";
						$usql .= "WHERE numeros_interno.id_numero_interno = ged_arquivos.id_numero_interno ";
						$usql .= "AND solicitacao_documentos_detalhes.id_numero_interno = numeros_interno.id_numero_interno ";
						$usql .= "AND ged_arquivos.id_ged_arquivo = ged_versoes.id_ged_arquivo ";
						$usql .= "AND ged_versoes.id_ged_versao = '".$id_ged_versao."' ";
						$usql .= "AND numeros_interno.reg_del = 0 ";
						$usql .= "AND solicitacao_documentos_detalhes.reg_del = 0 ";
						$usql .= "AND ged_arquivos.reg_del = 0 ";
						$usql .= "AND ged_versoes.reg_del = 0 ";
						
						$db->update($usql,'MYSQL');

						if($db->erro!='')
						{
							$resposta->addAlert($db->erro);
						}
						else
						{
							$resposta->addAlert("Número cliente atualizado com sucesso.");	
						}
					}											
				}				
			break;
		
			case 'revisao_cliente':

				$usql = "UPDATE ".DATABASE.".ged_versoes SET ";
				$usql .= "ged_versoes.revisao_cliente = '" . trim($valor) . "' ";
				$usql .= "WHERE ged_versoes.id_ged_versao = '".$id_ged_versao."' ";
				$usql .= "AND ged_versoes.reg_del = 0 ";
		
				$db->update($usql,'MYSQL');

				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
				}	
				
			break;
			
			case 'formato':
				
				//Atualiza o id_formato na tabela solicitacao_documentos_detalhes
				$usql = "UPDATE ".DATABASE.".solicitacao_documentos_detalhes, ".DATABASE.".numeros_interno, ".DATABASE.".ged_arquivos, ".DATABASE.".ged_versoes SET ";
				$usql .= "solicitacao_documentos_detalhes.id_formato = '" . $valor . "', ";
				$usql .= "numeros_interno.id_formato = '" . $valor . "' ";
				$usql .= "WHERE solicitacao_documentos_detalhes.id_numero_interno = numeros_interno.id_numero_interno ";
				$usql .= "AND numeros_interno.id_numero_interno = ged_arquivos.id_numero_interno ";
				$usql .= "AND ged_arquivos.id_ged_arquivo = ged_versoes.id_ged_arquivo ";
				$usql .= "AND ged_versoes.id_ged_versao = '".$id_ged_versao."' ";
				$usql .= "AND solicitacao_documentos_detalhes.reg_del = 0 ";
				$usql .= "AND ged_arquivos.reg_del = 0 ";
				$usql .= "AND ged_versoes.reg_del = 0 ";
				$usql .= "AND numeros_interno.reg_del = 0 ";
				
				$db->update($usql,'MYSQL');

				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
				}
				
			break;
			
			case 'finalidade':
				
				$usql = "UPDATE ".DATABASE.".ged_versoes SET ";
				$usql .= "ged_versoes.id_fin_emissao = '" . $valor . "' ";
				$usql .= "WHERE ged_versoes.id_ged_versao = '".$id_ged_versao."' ";
				$usql .= "AND ged_versoes.reg_del = 0 ";
	
				$db->update($usql,'MYSQL');

				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
				}
				
			break;	
	
			case 'folhas':
				
				$usql = "UPDATE ".DATABASE.".solicitacao_documentos_detalhes, ".DATABASE.".numeros_interno, ".DATABASE.".ged_arquivos, ".DATABASE.".ged_versoes SET ";
				$usql .= "solicitacao_documentos_detalhes.folhas = '" . $valor . "', ";
				$usql .= "ged_versoes.numero_folhas = '" . $valor . "', ";
				$usql .= "numeros_interno.numero_folhas = '" . $valor . "' ";
				$usql .= "WHERE solicitacao_documentos_detalhes.id_numero_interno = ged_arquivos.id_numero_interno ";
				$usql .= "AND ged_arquivos.id_numero_interno = numeros_interno.id_numero_interno ";
				$usql .= "AND ged_arquivos.id_ged_arquivo = ged_versoes.id_ged_arquivo ";
				$usql .= "AND ged_versoes.id_ged_versao = '".$id_ged_versao."' ";
				$usql .= "AND solicitacao_documentos_detalhes.reg_del = 0 ";
				$usql .= "AND numeros_interno.reg_del = 0 ";
				$usql .= "AND ged_arquivos.reg_del = 0 ";
				$usql .= "AND ged_versoes.reg_del = 0 ";
				
				$db->update($usql,'MYSQL');

				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
				}	
				
			break;
			
			case 'copias':
				
				$usql = "UPDATE ".DATABASE.".ged_versoes SET ";
				$usql .= "ged_versoes.copias = '" . $valor . "' ";
				$usql .= "WHERE ged_versoes.id_ged_versao = '".$id_ged_versao."' ";
				$usql .= "AND ged_versoes.reg_del = 0 ";
				
				$db->update($usql,'MYSQL');

				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
				}
				
			break;
			
			case 'documento_interno':
				
				if($valor=='true')
				{
					$valor = 1;
				}
				else
				{
					$valor = 0;	
				}
			
				$usql = "UPDATE ".DATABASE.".ged_arquivos, ".DATABASE.".ged_versoes SET ";
				$usql .= "ged_arquivos.documento_interno = " . $valor . " ";
				$usql .= "WHERE ged_arquivos.id_ged_arquivo = ged_versoes.id_ged_arquivo ";
				$usql .= "AND ged_versoes.id_ged_versao = '".$id_ged_versao."' ";
				$usql .= "AND ged_arquivos.reg_del = 0 ";
				$usql .= "AND ged_versoes.reg_del = 0 ";
			
				$db->update($usql,'MYSQL');

				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
				}
				
			break;		
			
		}		
	}
	else
	{
		$resposta->addAlert('Erro ao tentar atualizar.');	
	}
		
	return $resposta;	
}

function atualizaPropriedades($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados();
	
	$sql = "SELECT * FROM ".DATABASE.".numeros_interno, ".DATABASE.".setores, ".DATABASE.".ged_arquivos, ".DATABASE.".solicitacao_documentos_detalhes, ".DATABASE.".ged_versoes, ".DATABASE.".ordem_servico ";
	$sql .= "WHERE numeros_interno.reg_del = 0 ";
	$sql .= "AND ged_arquivos.reg_del = 0 ";
	$sql .= "AND solicitacao_documentos_detalhes.reg_del = 0 ";
	$sql .= "AND ged_versoes.reg_del = 0 ";
	$sql .= "AND setores.reg_del = 0 ";
	$sql .= "AND ordem_servico.reg_del = 0 ";
	$sql .= "AND ordem_servico.id_os = numeros_interno.id_os ";
	$sql .= "AND numeros_interno.id_numero_interno = solicitacao_documentos_detalhes.id_numero_interno ";
	$sql .= "AND numeros_interno.id_numero_interno = ged_arquivos.id_numero_interno ";
	$sql .= "AND numeros_interno.id_disciplina = setores.id_setor ";
	$sql .= "AND ged_arquivos.id_ged_arquivo = ged_versoes.id_ged_arquivo ";
	$sql .= "AND ged_versoes.id_ged_versao = '" . $dados_form["id_ged_versao"] . "' ";
	
	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	else
	{ 
		$reg_complemento = $db->array_select[0];
	
		//Atualiza os dados da Solicitação
		$usql = "UPDATE ".DATABASE.".numeros_interno, ".DATABASE.".solicitacao_documentos_detalhes SET ";
		$usql .= "solicitacao_documentos_detalhes.tag = '" . addslashes(trim($dados_form["tag"])) . "', ";
		$usql .= "solicitacao_documentos_detalhes.tag2 = '" . addslashes(trim($dados_form["tag2"])) . "', ";
		$usql .= "solicitacao_documentos_detalhes.tag3 = '" . addslashes(trim($dados_form["tag3"])) . "', ";
		$usql .= "solicitacao_documentos_detalhes.tag4 = '" . addslashes(trim($dados_form["tag4"])) . "', ";
		$usql .= "numeros_interno.complemento = '" . addslashes(trim($dados_form["tag"])) . "' ";
		$usql .= "WHERE numeros_interno.id_numero_interno = solicitacao_documentos_detalhes.id_numero_interno ";
		$usql .= "AND id_solicitacao_documentos_detalhe = '" . $reg_complemento["id_solicitacao_documentos_detalhe"] . "' ";
		$usql .= "AND numeros_interno.reg_del = 0 ";
		$usql .= "AND solicitacao_documentos_detalhes.reg_del = 0 ";
		
		$db->update($usql,'MYSQL');

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
		else
		{	
			//Atualiza os dados da data de devolução
			$usql = "UPDATE ".DATABASE.".ged_versoes SET ";
			$usql .= "data_devolucao = '" . php_mysql($dados_form["data_devolucao"]) . "', ";
			$usql .= "status_devolucao = '" . $dados_form["status_devolucao"] . "' ";
			$usql .= "WHERE id_ged_versao = '" . $reg_complemento["id_ged_versao"] . "' ";
			$usql .= "AND ged_versoes.reg_del = 0 ";
			
			$db->update($usql,'MYSQL');

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}
			else
			{		
				//INCLUIDO POR CARLOS ABREU EM 22/06/2010 CONFORME SOLICITAÇÃO DE KATSUMI/WAGNER ROCHA/
				//RICARDO GIGLI/ZANIRATO
				//ENVIA E-MAIL PARA O(S) COORDENADOR(ES) DA OS INFORMANDO O STATUS DA DEVOLUÇÃO
				//CASO SEJA ESCOLHIDO UM STATUS
				if($dados_form["status_devolucao"]!="")
				{
					$sql = "SELECT * FROM ".DATABASE.".codigos_devolucao ";
					$sql .= "WHERE codigos_devolucao.reg_del = 0 ";
					
					$db->select($sql,'MYSQL',true);
					
					if($db->erro!='')
					{
						$resposta->addAlert($db->erro);
					}
					
					foreach($db->array_select as $regs)
					{
						$array_devolucao[$regs["codigos_devolucao"]] = $regs["descricao_devolucao"];
					}									
					
					//Seleciona o Coordenadores
					$sql = "SELECT funcionario, email FROM ".DATABASE.".funcionarios, ".DATABASE.".usuarios ";
					$sql .= "WHERE funcionarios.id_funcionario = usuarios.id_funcionario ";
					$sql .= "AND funcionarios.reg_del = 0 ";
					$sql .= "AND usuarios.reg_del = 0 ";
					$sql .= "AND funcionarios.situacao = 'ATIVO' ";
					
					$db->select($sql,'MYSQL',true);

					if($db->erro!='')
					{
						$resposta->addAlert($db->erro);
					}
					else
					{				 
						foreach($db->array_select as $reg_pri)
						{
							$array_coord[$reg_pri["id_funcionario"]]['email']= $reg_pri["email"];
							$array_coord[$reg_pri["id_funcionario"]]['nome']= $reg_pri["funcionario"];	
						}
						
						//seleciona o pacote do arquivo
						$sql = "SELECT * FROM ".DATABASE.".ged_pacotes, ".DATABASE.".ordem_servico ";
						$sql .= "WHERE ged_pacotes.reg_del = 0 ";
						$sql .= "AND ordem_servico.reg_del = 0 ";
						$sql .= "AND ged_pacotes.id_ged_pacote = '".$reg_complemento["id_ged_pacote"]."' ";
						$sql .= "AND ged_pacotes.id_os = ordem_servico.id_os ";
						
						$db->select($sql,'MYSQL',true);

						if($db->erro!='')
						{
							$resposta->addAlert($db->erro);
						}
						else
						{					 
							$reg_grd = $db->array_select[0];		
							
							//Forma o e-mail
							$params 			= array();
							$params['from']		= 'Comentários devolvidos GED';
							$params['from_name']= 'Arquivo Tecnico';
							
							$params['subject'] = "Comentário(s) devolvido pelo cliente - OS: ".sprintf("%05d",$reg_complemento["os"]);
							
							$params['emails']['to'][] = array('email' => "arqtec@dominio.com.br", 'nome' => "Arquivo Técnico");
							
							if($array_coord[$reg_complemento["id_cod_coord"]]['email']!='')
							{
								$params['emails']['to'][] = array('email' => $array_coord[$reg_complemento["id_cod_coord"]]['email'], 'nome' => $array_coord[$reg_complemento["id_cod_coord"]]['nome']);
							}
							
							if($array_coord[$reg_complemento["id_coord_aux"]]['email']!='')
							{
								$params['emails']['to'][] = array('email' => $array_coord[$reg_complemento["id_coord_aux"]]['email'], 'nome' => $array_coord[$reg_complemento["id_coord_aux"]]['nome']);
							}
							
							//Carlos Eduardo: 27/02/2018
							if($array_usremail[$reg_complemento['id_cod_coord']]!='')
							{
							    //COORDENADOR DO PROJETO
							    $params['emails']['to'][] = array('email' => $array_usremail[$reg_complemento['id_cod_coord']], 'nome' => $array_usrlogin[$reg_complemento['id_cod_coord']]);
							}
							
							//Carlos Eduardo: 23/01/2018
							$alocados = ProtheusDao::getAlocadosOS($reg_complemento["os"], true, $reg_complemento["id_setor"]);
							//TODOS OS ALOCADOS
							foreach($alocados as $alocado)
							{
							    if(!empty($alocado['email']))
							    {
							        $params['emails']['to'][] = array('email' => $alocado['email'], 'nome' => $alocado['Login']);
							    }
							}

							//Adiciona a mensagem do corpo do e-mail
							$corpoEmail = "<html>";
							$corpoEmail .= "<body style='font: 11pt Arial'><p>O seguinte arquivo do pacote teve comentários em ".$dados_form["data_devolucao"];
							$corpoEmail .= "<div id='pacote'>Pacote: ".sprintf("%04d",$reg_grd["numero_pacote"]) . "-" . sprintf("%05d",$reg_grd["os"]) . "</div>";
							$corpoEmail .= "<div id='div_numdvm'>Número Devemada: ". PREFIXO_DOC_GED . sprintf("%05d",$reg_complemento["os"]) . "-" . $reg_complemento["sequencia"] . "</div>";
							$corpoEmail .= "<div id='div_numcli'>Número Cliente: " . $reg_complemento["numero_cliente"] . "</div>";
							$corpoEmail .= "<div id='div_disc'>Disciplina: "  . $reg_complemento["setor"] . "</div>";
							$corpoEmail .= "<div id='div_revisao'>Revisão / Versão: " . $reg_complemento["revisao_documento"] . "." . $reg_complemento["versao_documento"] . "</div>";
							$corpoEmail .= "<div id='div_status'>status do comentário: " . $array_devolucao[$dados_form["status_devolucao"]] . "</div>";
							$corpoEmail .= "<div id='div_data_sol'>data da solicitação do Pacote: " . mysql_php($reg_grd["data"]) . "</div>";
							$corpoEmail .= "<div id='div_obs'>Favor encaminhar este e-mail para os responsáveis.</div></body></html>";
							
							$mail = new email($params);
							
							$mail->montaCorpoEmail($corpoEmail);

							if(!$mail->Send())
							{
								$resposta->addAlert('Erro ao enviar e-mail! '.$mail->ErrorInfo);
							}
						}					
					}				
				}
								
				$resposta->addAlert("Dados atualizados com sucesso.");
				
				$resposta->addScript("xajax_preencheVersoes_comentarios('" . $dados_form["id_ged_versao"] . "'); ");
			}
		}
	}

	return $resposta;
}

//Exclui o pacote, desvinculando os arquivos
function excluir_pacote($id_ged_pacote)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados();	

	//Libera os arquivos relacionados ao pacote que será excluído
	$usql = "UPDATE ".DATABASE.".ged_arquivos, ".DATABASE.".ged_versoes SET ";
	$usql .= "ged_arquivos.status = 0 ";
	$usql .= "WHERE ged_arquivos.id_ged_arquivo = ged_versoes.id_ged_arquivo ";
	$usql .= "AND ged_versoes.id_ged_pacote = '" . $id_ged_pacote . "' ";
	$usql .= "AND ged_arquivos.reg_del = 0 ";
	$usql .= "AND ged_versoes.reg_del = 0 ";
	
	$db->update($usql,'MYSQL');

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	else
	{
		//Desvincula os arquivos do pacote
		$usql = "UPDATE ".DATABASE.".ged_versoes SET ";
		$usql .= "id_ged_pacote = 0 ";	
		$usql .= "WHERE ged_versoes.id_ged_pacote = '" . $id_ged_pacote . "' ";
		$usql .= "AND ged_versoes.reg_del = 0 ";
	
		$db->update($usql,'MYSQL');

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
		else
		{		
			//Remove o pacote
			$usql = "UPDATE ".DATABASE.".ged_pacotes SET ";
			$usql .= "ged_pacotes.reg_del = 1, ";
			$usql .= "ged_pacotes.reg_who = '".$_SESSION["id_funcionario"]."', ";
			$usql .= "ged_pacotes.data_del = '".date('Y-m-d')."' ";	
			$usql .= "WHERE ged_pacotes.id_ged_pacote = '" . $id_ged_pacote . "' ";
			$usql .= "AND ged_pacotes.reg_del = 0 ";
		
			$db->update($usql,'MYSQL');

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}
			else
			{			
				$resposta->addAlert("Pacote excluído com sucesso.");
				
				 $resposta->addScript("xajax_mostraPacote('" . $id_ged_pacote . "'); ");
					
				$resposta->addScript("xajax_atualizatabela(document.getElementById('periodos').value); ");
			}
		}
	
	}
	
	return $resposta;
}

//Desvincula a versão do pacote
function liberar_versao($id_ged_versao)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados();

	//Seleciona os dados:
	$sql = "SELECT * FROM ".DATABASE.".ged_versoes, ".DATABASE.".numeros_interno, ".DATABASE.".ged_arquivos, ".DATABASE.".ged_pacotes, ".DATABASE.".setores, ".DATABASE.".ordem_servico, ".DATABASE.".usuarios, ".DATABASE.".funcionarios ";
	$sql .= "WHERE numeros_interno.reg_del = 0 ";
	$sql .= "AND ged_arquivos.reg_del = 0 ";
	$sql .= "AND ged_versoes.reg_del = 0 ";
	$sql .= "AND ged_pacotes.reg_del = 0 ";
	$sql .= "AND setores.reg_del = 0 ";
	$sql .= "AND ordem_servico.reg_del = 0 ";
	$sql .= "AND usuarios.reg_del = 0 ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	$sql .= "AND numeros_interno.id_disciplina = setores.id_setor ";
	$sql .= "AND ged_pacotes.id_os = ordem_servico.id_os ";
	$sql .= "AND ged_versoes.id_ged_pacote = ged_pacotes.id_ged_pacote ";
	$sql .= "AND ged_versoes.id_ged_arquivo = ged_arquivos.id_ged_arquivo ";
	$sql .= "AND ged_arquivos.id_numero_interno = numeros_interno.id_numero_interno ";
	$sql .= "AND ged_versoes.id_ged_versao = '" . $id_ged_versao . "' ";
	$sql .= "AND ged_pacotes.id_autor = usuarios.id_funcionario ";
	$sql .= "AND usuarios.id_funcionario = funcionarios.id_funcionario ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	else
	{
		$reg_pkt = $db->array_select[0];
		
		//Retira a versão selecionada do pacote
		$usql = "UPDATE ".DATABASE.".ged_versoes SET ";
		$usql .= "ged_versoes.id_ged_pacote = 0 ";
		$usql .= "WHERE ged_versoes.id_ged_versao = '" . $id_ged_versao . "' ";
		$usql .= "AND ged_versoes.reg_del = 0 ";
		
		$db->update($usql,'MYSQL');

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
		else
		{		
			$usql = "UPDATE ".DATABASE.".ged_versoes, ".DATABASE.".ged_arquivos SET ";
			$usql .= "ged_arquivos.status = 0 ";
			$usql .= "WHERE ged_versoes.id_ged_arquivo = ged_arquivos.id_ged_arquivo ";
			$usql .= "AND ged_versoes.id_ged_versao = '" . $id_ged_versao . "' ";
			$usql .= "AND ged_versoes.reg_del = 0 ";
			$usql .= "AND ged_arquivos.reg_del = 0 ";
			
			$db->update($usql,'MYSQL');

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}
			else
			{
				//Forma o e-mail
				$params 			= array();
				$params['from']		= 'arqtec@dominio.com.br';
				$params['from_name']= 'Arquivo Tecnico';
				
				$params['subject'] = "Arquivo retirado de pacote: " . sprintf("%05d",$reg_pkt["os"]) . "-" . sprintf("%04d",$reg_pkt["numero_pacote"]);
				$params['emails']['to'][] = array('email' => "arqtec@dominio.com.br", 'nome' => "Arquivo Técnico");
				
				if($reg_pkt["email"]!='')
				{
					$params['emails']['to'][] = array('email' => $reg_pkt["email"], 'nome' => $reg_pkt["funcionario"]);
				}	
				
				//Adiciona a mensagem do corpo do e-mail
				$corpoEmail = "<html><body style='font: 11pt Arial'><p>O seguinte arquivo foi retirado do pacote " . sprintf("%05d",$reg_pkt["os"]) . "-" . sprintf("%04d",$reg_pkt["numero_pacote"]) . " e está liberado no sistema:</p>";
				$corpoEmail .= "<div>Número Devemada: ". PREFIXO_DOC_GED . sprintf("%05d",$reg_pkt["os"]) . "-" . $reg_pkt["sigla"] . "-" . $reg_pkt["sequencia"] . "</div>";
				$corpoEmail .= "<div>Revisão / Versão: " . $reg_pkt["revisao_documento"] . "." . $reg_pkt["versao_documento"] . "</div>";
				$corpoEmail .= "<div>data da solicitação do Pacote: " . mysql_php($reg_pkt["data"]) . "</div>";
				$corpoEmail .= "<div>Retirado por: " . $_SESSION["login"] . "</div></body></html>";
				
				$mail = new email($params);
				
				$mail->montaCorpoEmail($corpoEmail);

				if(!$mail->Send())
				{
					$resposta->addAlert('Erro ao enviar e-mail! '.$mail->ErrorInfo);
				}
			
				$resposta->addAlert("Arquivo excluído do pacote e liberado no GED com sucesso. ");
				
				$resposta->addScript("xajax_mostraPacote('" . $reg_pkt["id_ged_pacote"] . "'); ");
			}
		}
	}
	
	return $resposta;
}

//Exclui o comentário associado ao arquivo de versão
function excluir_comentario($id_ged_comentario)
{
	$resposta = new xajaxResponse();

	$db = new banco_dados();
	
	$sql = "SELECT * FROM ".DATABASE.".ged_comentarios ";
	$sql .= "WHERE ged_comentarios.reg_del = 0 ";
	$sql .= "AND ged_comentarios.id_ged_comentario = '" . $id_ged_comentario . "' ";

	$db->select($sql,'MYSQL',true);
	
	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	else
	{
		$reg_coment = $db->array_select[0];
	
		if($db->numero_registros > 0)
		{
			$str_arquivo = DOCUMENTOS_GED . $reg_coment["base"] . "/" . $reg_coment["os"] . "/" .  substr($reg_coment["os"],0,4) . DISCIPLINAS . "/" . $reg_coment["disciplina"] . "/" . $reg_coment["atividade"] . "/" . $reg_coment["sequencial"] . DIRETORIO_COMENTARIOS . $reg_coment["strarquivo"] . "." . sprintf("%03d",$reg_coment["sequencia_doc"]);
			
			if(file_exists($str_arquivo))
			{
				$exclui_comentario = unlink($str_arquivo);
			}

			$usql = "UPDATE ".DATABASE.".ged_comentarios SET ";
			$usql .= "ged_comentarios.reg_del = 1, ";
			$usql .= "ged_comentarios.reg_who = '".$_SESSION["id_funcionario"]."', ";
			$usql .= "ged_comentarios.data_del = '".date('Y-m-d')."' ";
			$usql .= "WHERE ged_comentarios.id_ged_comentario = '" . $id_ged_comentario . "' ";
			$usql .= "AND ged_comentarios.reg_del = 0 ";
		
			$db->update($usql,'MYSQL');

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}
			else
			{				
				$resposta->addAlert("Comentário excluído com sucesso.");
				
				$resposta->addScript("xajax_propriedades_comentarios(xajax.getFormValues('frm_prop'))");
			}				
		}
	}
	
	return $resposta;
}

//Exclui a GRD, sem afetar o pacote
function excluirGRD($id_grd)
{
	$resposta = new xajaxResponse();

	$db = new banco_dados();
	
	$sql = "SELECT * FROM ".DATABASE.".ged_pacotes, ".DATABASE.".grd ";
	$sql .= "WHERE grd.reg_del = 0 ";
	$sql .= "AND ged_pacotes.reg_del = 0 ";
	$sql .= "AND grd.id_ged_pacote = ged_pacotes.id_ged_pacote ";
	$sql .= "AND grd.id_grd = '" . $id_grd . "' ";
	
	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	else
	{
		$regs = $db->array_select[0];
		
		$usql = "UPDATE ".DATABASE.".ged_pacotes, ".DATABASE.".grd SET ";
		$usql .= "ged_pacotes.status = 0 ";
		$usql .= "WHERE grd.id_ged_pacote = ged_pacotes.id_ged_pacote ";
		$usql .= "AND grd.id_grd = '" . $id_grd . "' ";
		$usql .= "AND ged_pacotes.reg_del = 0 ";
		$usql .= "AND grd.reg_del = 0 ";
	
		$db->update($usql,'MYSQL');
	
		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
		else
		{
			//pega o numeros_interno para retirar a flag emitido
			$sql = "SELECT id_numero_interno FROM ".DATABASE.".grd_versoes, ".DATABASE.".ged_versoes, ".DATABASE.".ged_arquivos ";
			$sql .= "WHERE grd_versoes.reg_del = 0 ";
			$sql .= "AND ged_versoes.reg_del = 0 ";
			$sql .= "AND ged_arquivos.reg_del = 0 ";
			$sql .= "AND grd_versoes.id_grd = '".$id_grd."' ";
			$sql .= "AND grd_versoes.id_ged_versao = ged_versoes.id_ged_versao ";
			$sql .= "AND ged_versoes.id_ged_arquivo = ged_arquivos.id_ged_arquivo ";
			
			$db->select($sql,'MYSQL',true);
		
			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}
			else
			{
				foreach($db->array_select as $regs1)
				{
					//ACRESCENTADO POR CARLOS ABREU
					//30/09/2010
					//retirar NA TABELA NUMDVM COMO EMITIDO PARA O CLIENTE, SALVANDO A DATA, FLAG EMITIDO, GRD NA QUAL FOI EMITIDO PELA ULTIMA VEZ
					//E TAMBEM A DATA DO RETORNO PROVAVEL					
					$usql = "UPDATE ".DATABASE.".numeros_interno SET ";
					$usql .= "data_emissao_arquivo = '0000-00-00', ";
					$usql .= "data_retorno_arquivo = '0000-00-00', ";
					$usql .= "id_grd_emitido = '0', ";
					$usql .= "flag_emitido = 0 ";
					$usql .= "WHERE id_numero_interno = '".$regs1["id_numero_interno"]."' ";
					$usql .= "AND numeros_interno.reg_del = 0 ";
					
					$db->update($usql,'MYSQL');										
				}
									
				//Exclui da tabela grd
				$usql = "UPDATE ".DATABASE.".grd SET ";
				$usql .= "grd.reg_del = 1, ";
				$usql .= "grd.reg_who = '".$_SESSION["id_funcionario"]."', ";
				$usql .= "grd.data_del = '".date('Y-m-d')."' ";
				$usql .= "WHERE grd.id_grd = '" . $id_grd . "' ";
				$usql .= "AND grd.reg_del = 0 ";
				
				$db->update($usql,'MYSQL');
				
				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
				}
				else
				{			
					//Exclui da tabela grd versões		
					$usql = "UPDATE ".DATABASE.".grd_versoes SET ";
					$usql .= "grd_versoes.reg_del = 1, ";
					$usql .= "grd_versoes.reg_who = '".$_SESSION["id_funcionario"]."', ";
					$usql .= "grd_versoes.data_del = '".date('Y-m-d')."' ";
					$usql .= "WHERE grd_versoes.id_grd = '" . $id_grd . "' ";
					$usql .= "AND grd_versoes.reg_del = 0 ";
					
					$db->update($usql,'MYSQL');
					
					if($db->erro!='')
					{
						$resposta->addAlert($db->erro);
					}
					else
					{								
						$resposta->addAlert("GRD excluída com sucesso.");
						$resposta->addScript("divPopupInst.destroi();");
						$resposta->addScript("xajax_mostraPacote('".$regs["id_ged_pacote"]."')");						
						$resposta->addScript("xajax_atualizatabela(xajax.$('periodos').options[xajax.$('periodos').selectedIndex].value);");
						$resposta->addScript("xajax.$('txt_busca').value,'busca');");
					}
				}
			}
		}
	}
		
	return $resposta;
}

//Cancela a GRD, alterando a finalidade dos arquivos
function cancelarGRD($id_grd)
{
	$resposta = new xajaxResponse();

	$db = new banco_dados();

	$usql = "UPDATE ".DATABASE.".grd, ".DATABASE.".ged_versoes SET ";
	$usql .= "grd.status = 2, "; //1=NORMAL; 2=CANCELADA
	$usql .= "ged_versoes.id_fin_emissao = 1 "; //CA - CANCELADA
	$usql .= "WHERE grd.id_ged_pacote = ged_versoes.id_ged_pacote ";
	$usql .= "AND grd.id_grd = '" . $id_grd . "' ";
	$usql .= "AND grd.reg_del = 0 ";
	
	$db->update($usql,'MYSQL');
	
	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	else
	{		
		$sql = "SELECT * FROM ".DATABASE.".ged_pacotes, ".DATABASE.".grd ";
		$sql .= "WHERE ged_pacotes.reg_del = 0 ";
		$sql .= "AND grd.reg_del = 0 ";
		$sql .= "AND ged_pacotes.id_ged_pacote = grd.id_ged_pacote ";
		$sql .= "AND grd.id_grd = '" . $id_grd . "' ";
		
		$db->select($sql,'MYSQL',true);
		
		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
		else
		{	
			$reg_pacote = $db->array_select[0];
			
			$resposta->addScript("divPopupInst.destroi(); mostraGrds('" . $reg_pacote["id_ged_pacote"] . "'); ");
		}
	}

	return $resposta;
}

//solicitação de desbloqueio para os arquivos, para possibilitar check-in
//alterado em 25/04/2016 - Carlos Abreu
function desbloquear($id_ged_versao, $status)
{	
	$resposta = new xajaxResponse();
	
	$db = new banco_dados();
	
	$diretorio = "";
	
	$params = array();
	
	$params['from']	= 'arqtec@dominio.com.br';
	
	$params['from_name'] = 'Arquivo Tecnico';	
	
	//Preenche um array com dados de Usuários
	$sql = "SELECT funcionarios.id_funcionario, email, funcionario FROM ".DATABASE.".usuarios, ".DATABASE.".funcionarios ";
	$sql .= "WHERE funcionarios.id_funcionario = usuarios.id_funcionario ";
	$sql .= "AND usuarios.reg_del = 0 ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	$sql .= "AND funcionarios.situacao NOT IN ('DESLIGADO','CANCELADODVM','CANCELADO') ";
		
	$db->select($sql,'MYSQL',true);
	
	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	else
	{	
		foreach($db->array_select as $reg_usuarios)
		{
			$array_usremail[$reg_usuarios["id_funcionario"]] = $reg_usuarios["email"];
			$array_usrlogin[$reg_usuarios["id_funcionario"]] = $reg_usuarios["funcionario"];		
		}
		
		$sql = "SELECT *, motivo_desbloqueio comentario FROM ".DATABASE.".numeros_interno, ".DATABASE.".setores, ".DATABASE.".solicitacao_documentos_detalhes, ".DATABASE.".ged_versoes ";
		
		//Carlos Eduardo: 23/01/2018
		$sql .= "LEFT JOIN ".DATABASE.".ged_desbloqueios ON ged_desbloqueios.id_ged_versao = ged_versoes.id_ged_versao AND ged_desbloqueios.reg_del = 0, ";
		
		$sql .="".DATABASE.".ged_arquivos, ".DATABASE.".ordem_servico ";
		$sql .= "WHERE numeros_interno.reg_del = 0 ";
		$sql .= "AND solicitacao_documentos_detalhes.reg_del = 0 ";
		$sql .= "AND ged_arquivos.reg_del = 0 ";
		$sql .= "AND ged_versoes.reg_del = 0 ";
		$sql .= "AND setores.reg_del = 0 ";
		$sql .= "AND ordem_servico.reg_del = 0 ";
		$sql .= "AND ordem_servico.id_os = numeros_interno.id_os ";
		$sql .= "AND numeros_interno.id_numero_interno = solicitacao_documentos_detalhes.id_numero_interno ";
		$sql .= "AND numeros_interno.id_numero_interno = ged_arquivos.id_numero_interno ";
		$sql .= "AND numeros_interno.id_disciplina = setores.id_setor ";
		$sql .= "AND ged_arquivos.id_ged_arquivo = ged_versoes.id_ged_arquivo ";
		$sql .= "AND ged_versoes.id_ged_versao = '" . $id_ged_versao . "' ";
		
		$db->select($sql,'MYSQL',true);
		
		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
		else
		{
			$reg_pacote = $db->array_select[0];
			$motivoDesbloqueio = $regs['comentario'];
				
			//verifica o desbloqueio
			$array_resultado = verifica_desbloqueio($id_ged_versao,$status);
			
			//SE O STATUS DO DESBLOQUEIO ESTIVER COMO SOLICITADO (0)
			if(!$status)
			{
				//desbloqueio dos arquivos	
				$usql = "UPDATE ".DATABASE.".ged_arquivos SET ";
				$usql .= "ged_arquivos.status = 0, ";
				$usql .= "ged_arquivos.situacao = 0 ";
				$usql .= "WHERE ged_arquivos.id_ged_arquivo = '" . $reg_pacote["id_ged_arquivo"] . "' ";
				$usql .= "AND ged_arquivos.reg_del = 0 ";
				
				$db->update($usql,'MYSQL');
				
				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
				}								
			}

			if(!$status)
			{
			  
			    $params['subject'] = $reg_pacote["descricao"]." - DOCUMENTO DESBLOQUEADO COM COMENTARIO: ";
			  
			    $params['emails']['to'][] = array('email' => "arqtec@dominio.com.br", 'nome' => "Arquivo Técnico");
			    				
				//EDITOR DO ARQUIVO
				if($array_usremail[$reg_pacote["id_editor"]]!="")
				{
					$params['emails']['to'][] = array('email' => $array_usremail[$reg_pacote["id_editor"]], 'nome' => $array_usrlogin[$reg_pacote["id_editor"]]);
				}
				
				//DESBLOQUEADOR
				if($array_usremail[$_SESSION["id_funcionario"]]!="")
				{
					$params['emails']['to'][] = array('email' => $array_usremail[$_SESSION["id_funcionario"]], 'nome' => $array_usrlogin[$_SESSION["id_funcionario"]]);
				}
				
				//SOLICITANTE DO DESBLOQUEIO
				if($array_usremail[$array_resultado["solicitante"]]!="")
				{
					$params['emails']['to'][] = array('email' => $array_usremail[$array_resultado["solicitante"]], 'nome' => $array_usrlogin[$array_resultado["solicitante"]]);
				}
				
				//Carlos Eduardo: 27/02/2018
				if($array_usremail[$reg_pacote['id_cod_coord']]!='')
				{
				    //COORDENADOR DO PROJETO
				    $params['emails']['to'][] = array('email' => $array_usremail[$reg_pacote['id_cod_coord']], 'nome' => $array_usrlogin[$reg_pacote['id_cod_coord']]);
				}
				
				//Carlos Eduardo: 23/01/2018
				$alocados = ProtheusDao::getAlocadosOS($reg_pacote['OS'], true, $reg_pacote['id_setor']);
				//TODOS OS ALOCADOS
				foreach($alocados as $alocado)
				{
				    if(!empty($alocado['email']))
				    {
				        $params['emails']['to'][] = array('email' => $alocado['email'], 'nome' => $alocado['Login']);
				    }
				}
				
// 				$params['emails']['to'][] = array('email' => 'planejamento@dominio.com.br', 'nome' => 'Planejamento');
//  				$params['emails']['cco'][] = array('email' => 'carlos.maximo@dominio.com.br', 'nome' => 'Carlos Maximo');
				
				$str_mensagem = "<p>O seguinte documento foi desbloqueado manualmente no sistema: </p>";
				$str_mensagem .= "<p>" . $reg_pacote["descricao"] . "</p>";
				$str_mensagem .= "<p>Editor: " . $array_usrlogin[$reg_pacote["id_editor"]] . "</p>";
				$str_mensagem .= "<p>Desbloqueado por: " . $array_usrlogin[$_SESSION["id_funcionario"]] . " em " . date("d/m/Y - H:i:s") . "</p>";

				//Carlos Eduardo: 23/01/2018
				$str_mensagem .= "<p>Solicitante desbloqueio: " . $array_usrlogin[$array_resultado["solicitante"]] . "</p>";
				$str_mensagem .= "<p>Motivo do desbloqueio: " . $motivoDesbloqueio . "</p>";
				
				$str_mensagem .= "<p>&nbsp;</p>";
				$str_mensagem .= "<p>O mesmo agora se encontra livre no sistema para edição.</p>";
					
				$corpoEmail = "<html><body>" . $str_mensagem . "</body></html>";	 

				$mail = new email($params);
				
				$mail->montaCorpoEmail($corpoEmail);

				if(!$mail->Send())
				{
					$resposta->addAlert('Erro ao enviar e-mail!!! '.$mail->ErrorInfo);
				}
				else
				{
					$resposta->addAlert("Documento desbloqueado com sucesso.");			
				}		
			}
			else
			{
			    $params['subject'] = "DOCUMENTO COM RECUSA DE DESBLOQUEIO: " . $reg_pacote["descricao"];
				
				//GRUPO ARQUIVO TECNICO
				$params['emails']['to'][] = array('email' => "arqtec@dominio.com.br", 'nome' => "Arquivo Técnico");
		
				//DESBLOQUEADOR
				if($array_usremail[$_SESSION["id_funcionario"]]!='')
				{
					$params['emails']['to'][] = array('email' => $array_usremail[$_SESSION["id_funcionario"]], 'nome' => $array_usrlogin[$_SESSION["id_funcionario"]]);
				}
				
				//SOLICITANTE DO DESBLOQUEIO
				if($array_usremail[$array_resultado["solicitante"]]!='')
				{
					$params['emails']['to'][] = array('email' => $array_usremail[$array_resultado["solicitante"]], 'nome' => $array_usrlogin[$array_resultado["solicitante"]]);
				}
				
				$str_mensagem = "<p>O seguinte documento teve o desbloqueio recusado no sistema: </p>";
				$str_mensagem .= "<p>" . $reg_pacote["descricao"] . "</p>";
				$str_mensagem .= "<p>&nbsp;</p>";
				$str_mensagem .= "<p>A recusa é ocasionado por informação insuficiente no motivo.</p>";
					
				$corpoEmail = "<html><body>" . $str_mensagem . "</body></html>";	 
				 
				$mail = new email($params);
				
				$mail->montaCorpoEmail($corpoEmail);

				if(!$mail->Send())
				{
					$resposta->addAlert('Erro ao enviar e-mail!!! '.$mail->ErrorInfo);
				}
				else
				{
					$resposta->addAlert("E-mail enviado com sucesso.");			
				}				
			}
			
			$resposta->addScript("xajax_mostraPacote(".$reg_pacote["id_ged_pacote"].")");
		}
	}
	
	$resposta->addScript("xajax_propriedades_desbloqueios(xajax.getFormValues('frm_des'))");

	return $resposta;
}

function bloquear($id_ged_versao)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados();
	
	$params = array();
	
	$params['from']	= 'arqtec@dominio.com.br';
	
	$params['from_name'] = 'Arquivo Tecnico';
	
	//Preenche um array com dados de Usuários
	$sql = "SELECT funcionarios.id_funcionario, email, funcionario FROM ".DATABASE.".usuarios, ".DATABASE.".funcionarios ";
	$sql .= "WHERE funcionarios.id_funcionario = usuarios.id_funcionario ";
	$sql .= "AND usuarios.reg_del = 0 ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	$sql .= "AND funcionarios.situacao NOT IN ('DESLIGADO','CANCELADO', 'CANCELADODVM') ";

	$db->select($sql,'MYSQL',true);
	
	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	else
	{	
		foreach($db->array_select as $reg_usuarios)
		{
			$array_usremail[$reg_usuarios["id_funcionario"]] = $reg_usuarios["email"];
			$array_usrlogin[$reg_usuarios["id_funcionario"]] = $reg_usuarios["funcionario"];		
		}
	
		$sql = "SELECT * FROM ".DATABASE.".ged_versoes, ".DATABASE.".ged_arquivos ";
		$sql .= "WHERE ged_versoes.reg_del = 0 ";
		$sql .= "AND ged_arquivos.reg_del = 0 ";
		$sql .= "AND ged_versoes.id_ged_versao = '".$id_ged_versao."' ";
		$sql .= "AND ged_versoes.id_ged_arquivo = ged_arquivos.id_ged_arquivo ";
		
		$db->select($sql,'MYSQL',true);
		
		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
		else
		{		
			$reg_pacote = $db->array_select[0];
		
			$usql = "UPDATE ".DATABASE.".ged_arquivos SET ";
			$usql .= "ged_arquivos.status = 2, ";
			$usql .= "ged_arquivos.situacao = 0 ";
			$usql .= "WHERE ged_arquivos.id_ged_arquivo = '" . $reg_pacote["id_ged_arquivo"] . "' ";
			$usql .= "AND ged_arquivos.reg_del = 0 ";
			
			$db->update($usql,'MYSQL');		
	
			$params['subject'] = "DOCUMENTO BLOQUEADO: " . $reg_arquivo["descricao"];
			
			$params['emails']['to'][] = array('email' => 'arqtec@dominio.com.br', 'nome' => "Arquivo Técnico");
			
			//Editor
			if($array_usremail[$reg_pacote["id_editor"]]!='')
			{			
				$params['emails']['to'][] = array('email' => $array_usremail[$reg_pacote["id_editor"]], 'nome' => $array_usrlogin[$reg_pacote["id_editor"]]);
			}
			
			//Solicitante do bloqueio
			if($array_usremail[$_SESSION["id_funcionario"]]!='')
			{
				$params['emails']['to'][] = array('email' => $array_usremail[$_SESSION["id_funcionario"]], 'nome' => $array_usrlogin[$_SESSION["id_funcionario"]]);
			}		
			
			$str_mensagem = "<p>O seguinte documento foi bloqueado manualmente no sistema: </p>";
			$str_mensagem .= "<p>" . $reg_pacote["descricao"] . "</p>";
			$str_mensagem .= "<p>Editor: " . $array_usrlogin[$reg_pacote["id_editor"]] . "</p>";
			$str_mensagem .= "<p>bloqueado por: " . $array_usrlogin[$_SESSION["id_funcionario"]] . " em " . date("d/m/Y - H:i:s") . "</p>";
			$str_mensagem .= "<p>&nbsp;</p>";
			$str_mensagem .= "<p>O mesmo agora se encontra bloqueado no sistema para edição.</p>";
				
			$corpoEmail = "<html><body>" . $str_mensagem . "</body></html>";	 

			$mail = new email($params);
			
			$mail->montaCorpoEmail($corpoEmail);			

			if(!$mail->Send())
			{
				$resposta->addAlert('Erro ao enviar e-mail!!! '.$mail->ErrorInfo);
			}
			else
			{
				$resposta->addAlert("Documento bloqueado com sucesso.");			
			}
			
			$resposta->addScript("xajax_mostraPacote(".$reg_pacote["id_ged_pacote"].")");
		}
		
	}

	return $resposta;
}

$xajax->registerFunction("atualizatabela");
$xajax->registerFunction("seleciona_opcoes");
$xajax->registerFunction("mostraPacote");
$xajax->registerFunction("baixaPacote");
$xajax->registerFunction("enviaPacote");
$xajax->registerFunction("alteraPacote");
$xajax->registerFunction("retornaCliente");
$xajax->registerFunction("preencheGRDs");
$xajax->registerFunction("atualizaPropriedades");
$xajax->registerFunction("excluir_pacote");
$xajax->registerFunction("excluirGRD");
$xajax->registerFunction("excluir_comentario");
$xajax->registerFunction("cancelarGRD");
$xajax->registerFunction("abrir");
$xajax->registerFunction("liberar_versao");
$xajax->registerFunction("desbloquear");
$xajax->registerFunction("bloquear");

$xajax->registerFunction("propriedades_prop");
$xajax->registerFunction("propriedades_versoes");
$xajax->registerFunction("propriedades_comentarios");
$xajax->registerFunction("propriedades_desbloqueios");

$xajax->registerFunction("atualiza_campos");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","xajax_atualizatabela(xajax.$('periodos').options[xajax.$('periodos').selectedIndex].value);");

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="ged.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script language="javascript">

//desabilita right click
document.oncontextmenu=RightMouseDown;

function RightMouseDown() { return false;}

var valor_anterior=0;
var sel_tipo_vlr;
var sel_fin_vlr;
var txt_fls_vlr;
var txt_copia_vlr;

function grid(tabela, autoh, height, xml)
{
	mygrid = new dhtmlXGridObject(tabela);

	mygrid.enableAutoHeight(autoh,height);

	mygrid.enableRowsHover(true,'cor_mouseover');
	
	if(tabela=='div_ged_pacotes')
	{
		function doOnRowSelected(id,ind) 
		{
			if(ind<=3)
			{
				xajax_mostraPacote(id);
				
				return true;
			}
			
			return false;
		}

		mygrid.attachEvent("onRowSelect", doOnRowSelected);
		
		mygrid.setHeader("Pacote, OS, Solicitante, Data,D,R",
			null,
			["text-align:center","text-align:left","text-align:left","text-align:center","text-align:center","text-align:center"]);
		mygrid.setInitWidths("80,80,*,80,25,25");
		mygrid.setColAlign("center,left,left,left,center,center");
		mygrid.setColTypes("ro,ro,ro,ro,ro,ro");
		mygrid.setColSorting("str,str,str,str,str,str");
	}
	else
	{
		
		if(tabela=='div_conteudo_pacotes')
		{
			function doOnRowDblClicked1(row,col)
			{
				if(col>=1 && col<=3)
				{
					xajax_abrir(row);
					
					return true;
				}
				
				return false;
			}
			
			function doOnRightClick1(row,col)
			{
				if(col>=1 && col<=3)
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
			
			mygrid.attachEvent("onRowDblClicked",doOnRowDblClicked1);
			mygrid.attachEvent("onRightClick",doOnRightClick1);
		
			mygrid.setHeader("&nbsp;, &nbsp;, &nbsp;, Nome&nbsp;arquivo, Nº&nbsp;Interno, Rev.&nbsp;D, Ver., Nº&nbsp;Cliente, Rev.C, Tamanho, Formato, Fls., Fin., Cp., Doc.&nbsp;Int., D",
				null,
				["text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center"]);
			mygrid.setInitWidths("22,20,20,120,100,*,35,100,*,65,60,*,*,*,*,20");
			mygrid.setColAlign("center,center,center,left,left,left,center,left,left,left,left,left,left,left,center,center");
			mygrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
			mygrid.setColSorting("str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str");
		}
		else
		{
			if(tabela=='conteudo_versoes')
			{
				function doOnRowDblClicked2(row,col)
				{
					if(col>=0 && col<=2)
					{
						xajax_abrir(row);
						
						return true;
					}
					
					return false;
				}
				
				mygrid.attachEvent("onRowDblClicked",doOnRowDblClicked2);

				mygrid.setHeader("&nbsp;, Nome&nbsp;arquivo, Revisão&nbsp;Interna, Versão, Revisão&nbsp;Cli., Autor, Editor, Obs., A",
					null,
					["text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center"]);
				mygrid.setInitWidths("22,120,*,*,*,*,*,*,25");
				mygrid.setColAlign("center,center,left,left,left,center,center,center,center");
				mygrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro");
				mygrid.setColSorting("str,str,str,str,str,str,str,str,str");				
			}
			else
			{
				if(tabela=='div_comentarios_existentes')
				{
				
					mygrid.setHeader("Comentário, Autor, Nome&nbsp;Arquivo, A, E",
						null,
						["text-align:center","text-align:center","text-align:center","text-align:center","text-align:center"]);
					mygrid.setInitWidths("250,200,220,25,25");
					mygrid.setColAlign("left,left,left,left,center");
					mygrid.setColTypes("ro,ro,ro,ro,ro");
					mygrid.setColSorting("str,str,str,str,str");
					
				}
				else
				{
					if(tabela=='div_desbloqueios')
					{
						
						function doOnRowDblClicked3(row,col)
						{
							if(col==1)
							{
								xajax_abrir(row);
								
								return true;
							}
							
							return false;
						}
						
						mygrid.attachEvent("onRowDblClicked",doOnRowDblClicked3);
						
						mygrid.setHeader("Motivo&nbsp;desbloqueio, Arquivo, Status, Data&nbsp;devol., Solicitante, Data&nbsp;Sol., A, E",
							null,
							["text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center"]);
						mygrid.setInitWidths("120,*,*,*,*,*,25,25");
						mygrid.setColAlign("center,center,center,center,center,center,center,center");
						mygrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro");
						mygrid.setColSorting("str,str,str,str,str,str,str,str");							
					}
					else
					{
						if(tabela=='conteudo_grds')
						{

							mygrid.setHeader("Nº&nbsp;GRD, data, Visualizar, Cancelar, Excluir",
								null,
								["text-align:center","text-align:center","text-align:center","text-align:center","text-align:center"]);
							mygrid.setInitWidths("*,*,*,*,*");
							mygrid.setColAlign("center,center,center,center,center");
							mygrid.setColTypes("ro,ro,ro,ro,ro");
							mygrid.setColSorting("str,str,str,str,str");
							
						}
					}
				}
			}
		}		
	}
	
	mygrid.setSkin("dhx_skyblue");
	mygrid.enableMultiselect(true);
	mygrid.enableCollSpan(true);	
	mygrid.init();
	mygrid.loadXMLString(xml);	
}

function popupMenu(id_ged_versao, operacao, x, y)
{
	RCmenuInst = new RCmenu();
	
	switch(operacao)
	{
		case "1":
			status_desbloquear = 0;
			status_bloquear = 1;
		break;
		
		case "2":
			//desbloqueio
			status_desbloquear = 1;
			status_bloquear = 0;
		break;

	}	

	//Forma os itens do menu	
	var array_itens = new Array();
	
	array_itens[array_itens.length] = ['Abrir', function () {RCmenuInst.destroi();xajax_abrir('ARQ_'+id_ged_versao); },1,0];

	array_itens[array_itens.length] = ['Desbloquear', function () {RCmenuInst.destroi();xajax_desbloquear(id_ged_versao,0); },status_desbloquear,1];

	array_itens[array_itens.length] = ['Bloquear', function () {RCmenuInst.destroi();xajax_bloquear(id_ged_versao); },status_bloquear,2];

	array_itens[array_itens.length] = ['Propriedades', function () {RCmenuInst.destroi();mostraVersoes(id_ged_versao); }, 1,3];
	
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

function mostraVersoes(id_ged_versao)
{
	popupVersoes_comentarios(id_ged_versao);
}

function mostraGrds(id_ged_pacote)
{
	popupGRDs();

	xajax_preencheGRDs(id_ged_pacote);
}

function open_doc(dir)
{
	window.open("documento_v2.php?documento="+dir,"_blank");
}

</script>

<?php

$conf = new configs();

$sql = "SELECT SUBSTRING(ged_pacotes.data,1,7) AS periodo FROM ".DATABASE.".ged_pacotes ";
$sql .= "WHERE ged_pacotes.reg_del = 0 ";
$sql .= "GROUP BY SUBSTRING(ged_pacotes.data,1,7) ";
$sql .= "ORDER BY SUBSTRING(ged_pacotes.data,1,7) ";

$db->select($sql,'MYSQL',true);

foreach($db->array_select as $reg_periodos)
{
	unset($array_data_output);
	
	$data_output = "";

	$array_data_output = explode("-",$reg_periodos["periodo"]);
	
	if($array_data_output[0]=="12")
	{
		$str_ano = $array_data_output[0]+1;
	}
	else
	{
		$str_ano = $array_data_output[0];	
	}

	$str_mes = $array_data_output[1];	

	$stamp_per_ini = mktime(0,0,0,$str_mes,0,$array_data_output[0]);
	$stamp_per_fim = mktime(0,0,0,$str_mes+1,0,$str_ano);
	
	$array_periodos_values[] = date("m/Y",$stamp_per_ini) . "-" . date("m/Y",$stamp_per_fim);
	$array_periodos_output[] = date("m/Y",$stamp_per_ini) . " - " . date("m/Y",$stamp_per_fim);	

}

$periodo_atual = date("m/Y",$stamp_per_ini) . "-" . date("m/Y",$stamp_per_fim);

$smarty->assign("revisao_documento","V17");

$smarty->assign("campo",$conf->campos('ged_pacotes'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("option_periodos_values",$array_periodos_values);
$smarty->assign("option_periodos_output",$array_periodos_output);
$smarty->assign("periodo_atual",$periodo_atual);

$smarty->assign("nome_formulario","GUIA DE REMESSA DE DOCUMENTOS");

$smarty->assign("classe",CSS_FILE);

$smarty->display('ged_pacotes.tpl');

?>