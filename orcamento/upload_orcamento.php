<?php
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));

function lista_sem_versao()
{
	//Forma um array com os tipos de documentos que não terão versões, tipo EMAIL
	$lista_sem_versao = array('3'); 
	
	//Retorna o array
	return $lista_sem_versao;
}

$db = new banco_dados;

$result = 0;

$revisao_documento = 0;

$erro = '';

$id_proposta = $_POST["id_proposta"];

$tipo_documento = $_POST["documento"];

if($id_proposta)
{
	$sql = "SELECT * FROM ".DATABASE.".tipos_documentos ";
	$sql .= "WHERE id_tipo_documento = '".$tipo_documento."' ";
	$sql .= "AND tipos_documentos.reg_del = 0 ";
	
	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$erro = 3;
	}
	else
	{
		$regs0 = $db->array_select[0];		
		
		$sql = "SELECT numero_proposta FROM ".DATABASE.".propostas ";
		$sql .= "WHERE propostas.id_proposta = '".$id_proposta."' ";
		$sql .= "AND propostas.reg_del = 0 ";
		
		$db->select($sql,'MYSQL',true);

		if($db->erro!='')
		{
			$erro = 3;
		}
		else
		{ 
			$regs1 = $db->array_select[0];
			
			//seleciona o orcamento/cliente
			/*
			$sql = "SELECT * FROM AF1010 WITH(NOLOCK), SA1010 WITH(NOLOCK) ";
			$sql .= "WHERE AF1010.D_E_L_E_T_ = '' ";
			$sql .= "AND SA1010.D_E_L_E_T_ = '' ";
			$sql .= "AND AF1010.AF1_CLIENT = SA1010.A1_COD ";
			$sql .= "AND AF1010.AF1_LOJA = SA1010.A1_LOJA ";
			$sql .= "AND AF1010.AF1_ORCAME = '".$regs1["numero_proposta"]."' ";
			
			$db->select($sql,'MSSQL', true);

			if($db->erro!='')
			{
				$erro = 3;
			}
			else
			{
				if($db->numero_registros_ms > 0)
				{
					if(!empty($_FILES["arquivo"]["name"]))
					{										 
						$reg2 = $db->array_select[0];
						
						$dir = DOCUMENTOS_ORCAMENTO.trim(tiraacentos($reg2["A1_NOME"]))."/".sprintf("%05d",intval(trim($reg2["AF1_ORCAME"])));
					
						$diretorio = DOCUMENTOS_ORCAMENTO.trim(tiraacentos($reg2["A1_NOME"]))."/".sprintf("%05d",intval(trim($reg2["AF1_ORCAME"])))."/".tiraacentos($regs0["tipo_documento"]);
			
						//Se ainda não existir a pasta de comentários no diretório do arquivo, cria
						if(!is_dir($diretorio))
						{					
							if(mkdir($diretorio,0777,true))
							{
								$erro = 2;
							}
						}
						
						//verifica se existem versoes do arquivo
						$sql = "SELECT * FROM ".DATABASE.".arquivos_proposta, ".DATABASE.".arquivos_proposta_versoes ";
						$sql .= "WHERE arquivos_proposta.reg_del = 0 ";
						$sql .= "AND arquivos_proposta_versoes.reg_del = 0 ";
						$sql .= "AND arquivos_proposta.id_arquivo_proposta = arquivos_proposta_versoes.id_arquivo_proposta ";
						$sql .= "AND arquivos_proposta.id_proposta = '".$id_proposta."' ";
						$sql .= "AND arquivos_proposta.id_tipo_documento = '".$tipo_documento."' ";
						$sql .= "ORDER BY arquivos_proposta_versoes.revisao_documento DESC LIMIT 1 ";
						
						$db->select($sql,'MYSQL',true);
						
						//se der mensagem de erro, mostra
						if($db->erro!='')
						{
							$erro = "Erro ao selecionar os dados.";
						}
						else
						{
							$regs3 = $db->array_select[0];
							
							//se existe, incrementa a revisao_documento
							if($db->numero_registros>0)
							{
								$exitente = true;
								
								$revisao_documento = $regs3["revisao_documento"]+1;
							}
							else
							{
								$exitente = false;
							}					
							
							//Passa em todos os FILES do POST do xajax.upload
							foreach($_FILES as $chave=>$valor)
							{	
								//não esta vazio o campo arquivo
								if(tiraacentos($valor["name"])!='')
								{					
									//Verifica se o arquivo já existe
									if(is_file($diretorio . tiraacentos($valor["name"])))
									{
										$erro = "O arquivo com este nome já existe no diretorio. ".$valor["name"];
									}
									else
									{
										//se já existir a versão e não for sem versoes, move a versão atual para o diretorios de _versoes
										if($exitente && !in_array($tipo_documento,lista_sem_versao()))
										{
											$diretorio_versoes = DOCUMENTOS_ORCAMENTO.trim(tiraacentos($reg2["A1_NOME"]))."/".sprintf("%05d",intval(trim($reg2["AF1_ORCAME"])))."/".tiraacentos($regs0["tipo_documento"])."/_versoes";
								
											//Se ainda não existir a pasta de comentários no diretório do arquivo, cria
											if(!is_dir($diretorio_versoes))
											{					
												if(mkdir($diretorio_versoes,0777,true))
												{
													$erro = "Erro ao criar o diretório";
												}
											}
											
											//move o arquivo atual para as pasta de versoes
											if(rename($diretorio."/".$regs3["arquivo"],$diretorio_versoes."/".$regs3["arquivo"].".".$regs3["id_arquivo_versao"]))
											{
												//copia o arquivo para a raiz
												if(move_uploaded_file($valor["tmp_name"],$diretorio ."/". tiraacentos($valor["name"])))
												{
													//insere a referencia do arquivo no banco de versoes
													$isql = "INSERT INTO ".DATABASE.".arquivos_proposta_versoes (id_arquivo_proposta, base, arquivo, versao_documento, revisao_documento, id_autor, data_inclusao) VALUES(";
													$isql .= "'" . $regs3["id_arquivo_proposta"] . "', ";
													$isql .= "'" . trim(tiraacentos($reg2["A1_NOME"])) . "', ";
													$isql .= "'" . tiraacentos($valor["name"]). "', ";
													$isql .= "'".$_POST["versao_documento"]."', ";
													$isql .= "'".$revisao_documento."', ";
													$isql .= "'".$_SESSION["id_funcionario"]."',";
													$isql .= "'".date("Y-m-d H:i:s")."')";
												
													$db->insert($isql,'MYSQL');
													
													$id_arquivo_versao = $db->insert_id;
													
													if($db->erro!='')
													{
														$erro = "Erro ao tentar inserir os dados do arquivo";
													}
													else
													{
														//atualiza a nova revisao_documento										
														$usql = "UPDATE ".DATABASE.".arquivos_proposta SET ";
														$usql .= "id_arquivo_versao = '".$id_arquivo_versao."' ";
														$usql .= "WHERE id_arquivo_proposta = '".$regs3["id_arquivo_proposta"]."' ";
														$usql .= "AND reg_del = 0 ";
														
														$db->update($usql,'MYSQL');
															
														if($db->erro!='')
														{
															$erro = "Erro ao tentar atualizar os dados do arquivo";
														}
														else
														{
															$result = 1;	
														}
													}												
												}
												else
												{
													$erro = "Erro ao copiar o arquivo.";	
												}
											}
											else
											{
												$erro = "Erro ao mover o arquivo";	
											}								
										}
										else
										{
											$isql = "INSERT INTO ".DATABASE.".arquivos_proposta (id_proposta, id_tipo_documento) VALUES(";
											$isql .= "'" . $id_proposta . "', ";
											$isql .= "'" . $tipo_documento . "') ";
										
											$db->insert($isql,'MYSQL');
											
											$id_arquivo = $db->insert_id;							
														
											if($db->erro!='')
											{
												$erro = "Erro ao tentar inserir os dados do arquivo";
											}
											else
											{										
												//$result = 1;
												$isql = "INSERT INTO ".DATABASE.".arquivos_proposta_versoes (id_arquivo_proposta, base, arquivo, versao_documento, revisao_documento, id_autor, data_inclusao) VALUES(";
												$isql .= "'" . $id_arquivo . "', ";
												$isql .= "'" . trim(tiraacentos($reg2["A1_NOME"])) . "', ";
												$isql .= "'" . tiraacentos($valor["name"]). "', ";
												$isql .= "'" .$revisao_documento. "', ";
												$isql .= "'".$_POST["versao_documento"]."', ";
												$isql .= "'".$_SESSION["id_funcionario"]."',";
												$isql .= "'".date("Y-m-d H:i:s")."')";
											
												$db->insert($isql,'MYSQL');
												
												$id_arquivo_versao = $db->insert_id;							
															
												if($db->erro!='')
												{
													$erro = "Erro ao tentar inserir os dados do arquivo";
												}
												else
												{										
													$usql = "UPDATE ".DATABASE.".arquivos_proposta SET ";
													$usql .= "id_arquivo_versao = '".$id_arquivo_versao."' ";
													$usql .= "WHERE id_arquivo_proposta = '".$id_arquivo."' ";
													$usql .= "AND reg_del = 0 ";
													
													$db->update($usql,'MYSQL');
														
													if($db->erro!='')
													{
														$erro = "Erro ao tentar inserir os dados do arquivo";
													}
													else
													{
														$result = 1;	
													}
												}												
											}	
											
											//se tudo certo
											if($result)
											{	
												//se arquivo sem revisao_documento, coloca o id_arquivo_versao no final do arquivo																
												if(in_array($tipo_documento,lista_sem_versao()))
												{
													$nome_arquivo = $diretorio ."/". tiraacentos($valor["name"]).".".$id_arquivo_versao;
												}
												else
												{
													$nome_arquivo = $diretorio ."/". tiraacentos($valor["name"]);
												}
												
												$move_comentario = move_uploaded_file($valor["tmp_name"],$nome_arquivo);																						
								
												//Se foi movido com sucesso				
												if($move_comentario)
												{

													$result = 1;
																									
												}
												else
												{
													//deleta os registros incluidos
													$usql = "UPDATE ".DATABASE.".arquivos_proposta SET ";
													$usql .= "reg_del = '1', ";
													$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
													$usql .= "data_del = '".date('Y-m-d')."' ";
													$usql .= "WHERE id_arquivo_proposta = '".$id_arquivo."' ";
													$usql .= "AND reg_del = 0 ";
													
													$db->update($usql,'MYSQL');
													
													$usql = "UPDATE ".DATABASE.".arquivos_proposta_versoes SET ";
													$usql .= "reg_del = '1', ";
													$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
													$usql .= "data_del = '".date('Y-m-d')."' ";
													$usql .= "WHERE id_arquivo_versao = '".$id_arquivo_versao."' ";
													$usql .= "AND reg_del = 0 ";
													
													$db->update($usql,'MYSQL');
													
													$erro = "Erro ao tentar mover o arquivo.";
													
													$result = 0;
												}
											}
										}
									}		
									
									//Incrementa a contagem dos inputs
									$i++;					
								}
								else
								{
									$result = 0;
									
									$erro = "";
										
								}
								
								sleep(1);
							}
						}
					}
					else
					{
						$result = 0;
						
						$erro = "Favor anexar o arquivo.";	
					}
				
				}
				else
				{
					$result = 0;
					
					$erro = "Cliente não encontrado.";	
				}				
			}
			*/
		}
	}
}

?>
<script>
window.top.window.stopUpload_orcamento(<?= $id_proposta ?>,<?= $result ?>,<?= "'".$erro."'" ?>,<?= "'".$dir."'" ?>);
</script>
