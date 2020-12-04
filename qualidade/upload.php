<?php
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));

//padrão arquivo = RNC-NUMERO_RNC-RANDOMICO.ext

$erro = NULL;

$diretorio = "";

$prefixo = "";

$db = new banco_dados;

$id_nc = $_POST["id"];

//define qual documento
$prefixo = $_POST["prefixo"];

switch ($prefixo)
{
	case 'RNC':
	case 'PAC':
		if($prefixo=='RNC')
		{
			$diretorio = DOCUMENTOS_SGI."ANEXOS_RNC/";
		}
		
		if($prefixo=='PAC')
		{
			$diretorio = DOCUMENTOS_SGI."ANEXOS_PAC/";
		}
		
		if($id_nc && is_dir($diretorio) && $prefixo)
		{
			//Passa em todos os FILES do POST do xajax.upload
			foreach($_FILES as $chave=>$valor)
			{	
				//não esta vazio o campo arquivo
				if(tiraacentos($valor["name"])!='')
				{
					$sequencia = sprintf("%05d",mt_rand(1,99999));
								
					//Se ainda não existir a pasta, cria
					if(!is_dir($diretorio))
					{
						mkdir($diretorio);
					}
					
					$arq = pathinfo(tiraacentos($valor["name"]));
					
					$extensao = $arq["extension"];
					
					$nome_arq = $prefixo."-".sprintf("%05d",$id_nc)."-".$sequencia.".".$extensao;
			
					//Verifica se o arquivo já existe
					if(is_file($diretorio . $nome_arq))
					{
						$erro = "O seguinte arquivo já existe no diretório e não será incluído.";
					}
					else
					{				
						//Move o arquivo para o diretório
						$move_arq = move_uploaded_file($valor["tmp_name"],$diretorio . $nome_arq);											
		
						//Se foi movido com sucesso				
						if($move_arq)
						{
							if($prefixo=='RNC')
							{				
								//Insere os comentários no banco - 03/04/2009
								$isql = "INSERT INTO ".DATABASE.".nao_conformidades_anexos (id_nao_conformidade, nome_arquivo, anexo) VALUES(";
								$isql .= "'" . $id_nc . "', ";
								$isql .= "'" .tiraacentos($valor["name"]). "', ";
								$isql .= "'" . $nome_arq . "') ";						

								$db->insert($isql,'MYSQL');

								if($db->erro!='')
								{
									$erro = $db->erro."-".$isql;				
								}
							
							}
							else
							{
								
								//Insere os comentários no banco - 03/04/2009
								$isql = "INSERT INTO ".DATABASE.".planos_acoes_anexos (id_plano_acao, nome_arquivo, anexo) VALUES(";
								$isql .= "'" . $id_nc . "', ";
								$isql .= "'" .tiraacentos($valor["name"]). "', ";
								$isql .= "'" . $nome_arq . "') ";

								$db->insert($isql,'MYSQL');

								if($db->erro!='')
								{
									$erro = $db->erro."-".$isql;				
								}									
							}		
						}
						else
						{
							$erro = "Erro ao tentar mover o arquivo.".$diretorio . $nome_arq;
						}
					}		
					
				}
				
				sleep(1);
			}
		}
		else
		{
			$erro = "Erro ao referenciar o diretorio.";	
		}
	break;
	
	case 'ACT':
	
		$array_rpl = array("'","\"",")","(","\\","/",".",":","&","%");
		
		$sql = "SELECT * FROM  ".DATABASE.".ordem_servico, ".DATABASE.".empresas ";
		$sql .= "WHERE ordem_servico.id_os = '" . $id_nc . "' ";
		$sql .= "AND ordem_servico.id_empresa_erp = empresas.id_empresa_erp ";
		
		$db->select($sql,'MYSQL',true);

		$reg_act = $db->array_select[0];

		$abreviacao_cliente = str_replace($array_rpl, " ",maiusculas(tiraacentos($reg_act["abreviacao_GED"])));		
		
		$descricao_os = str_replace($array_rpl," ",maiusculas(tiraacentos($reg_act["descricao"])));

		$diretorio = DOCUMENTOS_GED . $abreviacao_cliente . "/" . $reg_act["os"] . "-" .$descricao_os;
		
		if (!is_dir($diretorio))
		{
			if(!mkdir($diretorio,0777))
			{
				$erro = 'Erro ao criar diretorio';
				
				exit;	
			}
		}		
		
		$diretorio .= '/'.$reg_act["os"] . ACT;		
		
		if($id_nc && $diretorio && $prefixo)
		{
			//Passa em todos os FILES do POST do xajax.upload
			foreach($_FILES as $chave=>$valor)
			{	
				//não esta vazio o campo arquivo
				if(tiraacentos($valor["name"])!='')
				{								
					//Se ainda não existir a pasta, cria
					if(!is_dir($diretorio))
					{
						if(!mkdir($diretorio))
						{
							$erro = 'Erro ao criar diretorio';
							
							exit;	
						}
					}
			
					$arq = pathinfo(tiraacentos($valor["name"]));
					
					$extensao = $arq["extension"];
					
					$nome_arq = $prefixo."-".sprintf("%05d",$id_nc).".".$extensao;			
			
					//Verifica se o arquivo já existe, se existir move o antigo para o diretorio _versoes
					if(is_file($diretorio ."/". $nome_arq))
					{
						//Se ainda não existir a pasta, cria
						if(!is_dir($diretorio.DIRETORIO_VERSOES))
						{
							mkdir($diretorio.DIRETORIO_VERSOES);
						}
						
						$sequencia = sprintf("%05d",mt_rand(1,99999));
						
						$move_antigo = rename($diretorio ."/". $nome_arq, $diretorio . DIRETORIO_VERSOES ."/". $nome_arq . "." . $sequencia);
						
						if($move_antigo)
						{
							$move_arq = move_uploaded_file($valor["tmp_name"],$diretorio ."/". $nome_arq);
							
							if($move_arq)
							{
								$usql = "UPDATE ".DATABASE.".os_x_anexos_cat SET ";
								$usql .= "nome_arquivo = '".tiraacentos($valor["name"])."', ";
								$usql .= "anexo = '".$nome_arq ."' ";
								$usql .= "WHERE id_os = '".$id_nc."' ";
								$usql .= "AND reg_del = 0 ";
								
								$db->update($usql,'MYSQL');
	
								if($db->erro!='')
								{
									$erro = $db->erro."-".$isql;				
								}
							}
							else
							{
								$erro = "Erro ao fazer o upload do arquivo.";	
							}
							
						}
						else
						{
							$erro = "Erro ao mover o arquivo.";	
						}						
					}
					else
					{
						//Se ainda não existir a pasta, cria
						if(!is_dir($diretorio))
						{
							mkdir($diretorio);
						}
										
						//Move o arquivo para o diretório
						$move_arq = move_uploaded_file($valor["tmp_name"],$diretorio ."/". $nome_arq);											
		
						//Se foi movido com sucesso				
						if($move_arq)
						{				
							//Insere os anexos
							$isql = "INSERT INTO ".DATABASE.".os_x_anexos_cat (id_os, nome_arquivo, anexo) VALUES(";
							$isql .= "'" . $id_nc . "', ";
							$isql .= "'" .tiraacentos($valor["name"]). "', ";
							$isql .= "'" . $nome_arq . "') ";						

							$db->insert($isql,'MYSQL');

							if($db->erro!='')
							{
								$erro = $db->erro."-".$isql;				
							}							
						}
						else
						{
							$erro = "Erro ao tentar mover o arquivo.".$diretorio . $nome_arq;
						}
					}					
				}
				
				sleep(1);
			}
		}
		else
		{
			$erro = "Erro ao referenciar o diretorio.";	
		}
	
	break;
	
	case 'APJ':

		if($id_nc && $prefixo)
		{
			$array_rpl = array("/",".",":","&",")","(","{","}");
			
			//Forma um array com a data fornecida
			$data_array = explode("/",$_POST["data_ap"]);
			
			$dia = $data_array[0];
			$mes = $data_array[1];
			$ano = $data_array[2];
			
			if($_POST["data_solicitacao"]!=="" && $_POST["identificacao_problema_ap"]!=="" && $_POST["status_ap"]!=="")
			{
				//nova inclusão
				if($_POST["id_os_x_analise_critica_periodica"]=='')
				{
		
					$sql = "SELECT * FROM  ".DATABASE.".ordem_servico, ".DATABASE.".empresas, ".DATABASE.".os_x_analise_critica_periodica ";
					$sql .= "WHERE os_x_analise_critica_periodica.id_os = '" . $id_nc . "' ";
					$sql .= "AND os_x_analise_critica_periodica.reg_del = 0 ";
					$sql .= "AND os_x_analise_critica_periodica.id_os = ordem_servico.id_os ";
					$sql .= "AND ordem_servico.id_empresa_erp = empresas.id_empresa_erp ";
					$sql .= "ORDER BY item DESC, data_ap DESC, id_os_x_analise_critica_periodica DESC LIMIT 1 ";
					
					$db->select($sql,'MYSQL',true);
	
					$reg_item = $db->array_select[0];
					
					if($db->numero_registros>0)
					{
						$item = $reg_item["item"]+1;					
					}
					else
					{
						$item = 1;
					}					
					
					$abreviacao_cliente = str_replace($array_rpl, " ",maiusculas(tiraacentos($reg_item["abreviacao_GED"])));		
					
					$descricao_os = str_replace($array_rpl," ",maiusculas(tiraacentos($reg_item["descricao"])));
					
					//monta o diretorio
					$diretorio = DOCUMENTOS_GED . $abreviacao_cliente . "/" . $reg_item["os"] . "-" .$descricao_os . "/" . $reg_item["os"] . ACOMPANHAMENTO;
					
					$isql = "INSERT INTO ".DATABASE.".os_x_analise_critica_periodica (item, id_os, id_disciplina, solicitado_por, solucao_por, data_ap, data_solicitacao, identificacao_problema_ap, solucao_possivel_ap, acao_corretiva_ap, pendencia_interna, status_ap) VALUES(";
					$isql .= "'" . $item . "', ";
					$isql .= "'" . $_POST["id_os"] . "', ";
					$isql .= "'" . $_POST["disciplina_analise_critica"] . "', ";
					$isql .= "'" . maiusculas($_POST["solicitado_por"]) . "', ";
					$isql .= "'" . maiusculas($_POST["solucao_por"]) . "', ";
					$isql .= "'" . php_mysql($_POST["data_ap"]) . "', ";
					$isql .= "'" . php_mysql($_POST["data_solicitacao"]) . "', ";
					$isql .= "'" . maiusculas($_POST["identificacao_problema_ap"]) . "', ";
					$isql .= "'" . maiusculas($_POST["solucao_possivel_ap"]) . "', ";
					$isql .= "'" . maiusculas($_POST["acao_corretiva_ap"]) . "', ";
					$isql .= "'" . $_POST["pend_int"] . "', ";
					$isql .= "'" . $_POST["status_ap"] . "') ";
					
					$db->insert($isql,'MYSQL');
					
					$id_analise_per = $db->insert_id;
					
					//Se houver arquivo para anexar, passa FILES do POST
					if($_FILES["arq_analise_periodica"]["name"]!='')
					{	
						//não esta vazio o campo arquivo
						if(tiraacentos($_FILES["arq_analise_periodica"]["name"])!='')
						{
							//Se ainda não existir a pasta, cria
							if(!is_dir($diretorio))
							{
								mkdir($diretorio);
							}
					
							//arquivo atual
							$arq = pathinfo(tiraacentos($_FILES["arq_analise_periodica"]["name"]));
							
							$extensao = $arq["extension"];
							
							$nome_arq = $prefixo."-".sprintf("%05d",$reg_item["os"])."-".sprintf("%05d",$id_analise_per)."-".sprintf("%03d",$item).".".$extensao;
					
							//Verifica se o arquivo já existe, se existir move o antigo para o diretorio _versoes
							if(is_file($diretorio ."/". $arq_banco["anexo"]))
							{
								//Se ainda não existir a pasta, cria
								if(!is_dir($diretorio.DIRETORIO_VERSOES))
								{
									mkdir($diretorio.DIRETORIO_VERSOES);
								}
								
								$move_antigo = rename($diretorio ."/". $arq_banco["anexo"], $diretorio . DIRETORIO_VERSOES ."/". $arq_banco["anexo"] . "." . $arq_banco["id_os_x_anexos"]);
								
								if($move_antigo)
								{
									$move_arq = move_uploaded_file($_FILES["arq_analise_periodica"]["tmp_name"],$diretorio ."/". $nome_arq);
									
									if($move_arq)
									{
										$usql = "UPDATE ".DATABASE.".os_x_anexos_cat SET ";
										$usql .= "reg_del = 1, ";
										$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
										$usql .= "data_del = '".date('Y-m-d')."' ";
										$usql .= "WHERE id_os_x_anexos = '".$arq_banco["id_os_x_anexos"]."' ";
										$usql .= "AND reg_del = 0 ";
										
										$db->update($usql,'MYSQL');
			
										if($db->erro!='')
										{
											$erro = $db->erro."-".$isql;				
										}
										
										//Insere os anexos
										$isql = "INSERT INTO ".DATABASE.".os_x_anexos_cat (id_os, nome_arquivo, anexo) VALUES(";
										$isql .= "'" . $id_nc . "', ";
										$isql .= "'" .tiraacentos($_FILES["arq_analise_periodica"]["name"]). "', ";
										$isql .= "'" . $nome_arq . "') ";						
			
										$db->insert($isql,'MYSQL');
			
										if($db->erro!='')
										{
											$erro = $db->erro."-".$isql;				
										}
										
									}
									else
									{
										$erro = "Erro ao fazer o upload do arquivo.";	
									}
									
								}
								else
								{
									$erro = "Erro ao mover o arquivo.";	
								}						
							}
							else
							{	
								//Move o arquivo para o diretório
								$move_arq = move_uploaded_file($_FILES["arq_analise_periodica"]["tmp_name"],$diretorio ."/". $nome_arq);											
				
								//Se foi movido com sucesso				
								if($move_arq)
								{				
									//Insere os anexos
									$usql = "UPDATE ".DATABASE.".os_x_analise_critica_periodica SET ";
									$usql .= "nome_arquivo = '".tiraacentos($_FILES["arq_analise_periodica"]["name"])."', ";
									$usql .= "anexo = '".$nome_arq."' ";
									$usql .= "WHERE id_os_x_analise_critica_periodica = '".$id_analise_per."' ";
									$usql .= "AND os_x_analise_critica_periodica.reg_del = 0 ";
									
									$db->update($usql,'MYSQL');
		
									if($db->erro!='')
									{
										$erro = $db->erro."-".$usql;				
									}
																
								}
								else
								{
									$erro = "Erro ao tentar mover o arquivo. ".$diretorio . $nome_arq;
								}
							}		
							
						}
						
						sleep(1);							
					}
				}
				else
				{
					$nome_arquivo = "";
					$nome_arq = "";
					$ins_anexo = "";
					
					//Se houver arquivo para anexar, passa FILES do POST
					if($_FILES["arq_analise_periodica"]["name"]!='')
					{						
						//obtem o item da analise		
						$sql = "SELECT * FROM ".DATABASE.".OS, ".DATABASE.".empresas, ".DATABASE.".os_x_analise_critica_periodica ";
						$sql .= "WHERE os_x_analise_critica_periodica.id_os_x_analise_critica_periodica = '" . $_POST["id_os_x_analise_critica_periodica"] . "' ";
						$sql .= "AND os_x_analise_critica_periodica.id_os = OS.id_os ";
						$sql .= "AND OS.id_empresa_erp = empresas.id_empresa_erp ";
						$sql .= "AND os_x_analise_critica_periodica.reg_del = 0 ";
						
						$db->select($sql,'MYSQL',true);
		
						$reg_item = $db->array_select[0];
				
						//arquivo atual
						$arq = pathinfo(tiraacentos($_FILES["arq_analise_periodica"]["name"]));
						
						$extensao = $arq["extension"];
						
						$nome_arq = $prefixo."-".sprintf("%05d",$reg_item["os"])."-".sprintf("%05d",$reg_item["id_os_x_analise_critica_periodica"])."-".sprintf("%03d",$reg_item["item"]).".".$extensao;

						$abreviacao_cliente = str_replace($array_rpl, " ",maiusculas(tiraacentos($reg_item["abreviacao_GED"])));		
						
						$descricao_os = str_replace($array_rpl," ",maiusculas(tiraacentos($reg_item["descricao"])));
						
						//monta o diretorio
						$diretorio = DOCUMENTOS_GED . $abreviacao_cliente . "/" . $reg_item["os"] . "-" .$descricao_os . "/" . $reg_item["os"] . ACOMPANHAMENTO;

						//Se ainda não existir a pasta, cria
						if(!is_dir($diretorio))
						{
							mkdir($diretorio);
						}

						//Verifica se o arquivo já existe, se existir move o antigo para o diretorio _versoes
						if(is_file($diretorio ."/". $reg_item["anexo"]))
						{
							//Se ainda não existir a pasta, cria
							if(!is_dir($diretorio.DIRETORIO_VERSOES))
							{
								mkdir($diretorio.DIRETORIO_VERSOES);
							}
							
							$move_antigo = rename($diretorio ."/". $reg_item["anexo"], $diretorio . DIRETORIO_VERSOES ."/". $reg_item["anexo"] . "." . $reg_item["id_os_x_analise_critica_periodica"]);
							
							if($move_antigo)
							{
								$move_arq = move_uploaded_file($_FILES["arq_analise_periodica"]["tmp_name"],$diretorio ."/". $nome_arq);
								
								if(!$move_arq)
								{
									$erro = "Erro ao fazer o upload do arquivo.";
								}
								else
								{
									$nome_arquivo = tiraacentos($_FILES["arq_analise_periodica"]["name"]);
								}

							}
							else
							{
								$erro = "Erro ao mover o arquivo.";	
							}						
						}
						else
						{
							//Move o arquivo para o diretório
							$move_arq = move_uploaded_file($_FILES["arq_analise_periodica"]["tmp_name"],$diretorio ."/". $nome_arq);											
			
							//Se foi movido com erro				
							if(!$move_arq)
							{
								$erro = "Erro ao fazer o upload do arquivo.";
							}
							else
							{
								$nome_arquivo = tiraacentos($_FILES["arq_analise_periodica"]["name"]);
							}
						}
						
						$ins_anexo .= "nome_arquivo = '".$nome_arquivo."', ";
						$ins_anexo .= "anexo = '".$nome_arq."', ";						
					}
					
					$usql = "UPDATE ".DATABASE.".os_x_analise_critica_periodica SET ";
					$usql .= "data_ap = '" . php_mysql($_POST["data_ap"]) . "', ";
					$usql .= "data_solicitacao = '" . php_mysql($_POST["data_solicitacao"]) . "', ";
					$usql .= "id_disciplina = '" . $_POST["disciplina_analise_critica"] . "', ";
					$usql .= "solicitado_por = '" . maiusculas($_POST["solicitado_por"]) . "', ";
					$usql .= "solucao_por = '" . maiusculas($_POST["solucao_por"]) . "', ";
					$usql .= "identificacao_problema_ap = '" . maiusculas($_POST["identificacao_problema_ap"]) . "', ";
					$usql .= "solucao_possivel_ap = '" . maiusculas($_POST["solucao_possivel_ap"]) . "', ";
					$usql .= "acao_corretiva_ap = '" . maiusculas($_POST["acao_corretiva_ap"]) . "', ";
					$usql .= "pendencia_interna = '" . $_POST["pend_int"] . "', ";
					$usql .= $ins_anexo;
					$usql .= "status_ap = '" . maiusculas($_POST["status_ap"]) . "' ";
					$usql .= "WHERE id_os_x_analise_critica_periodica = '" . $_POST["id_os_x_analise_critica_periodica"] . "' ";
					$usql .= "AND os_x_analise_critica_periodica.reg_del = 0 ";
					
					$db->update($usql,'MYSQL');											
				}				
			}
			else
			{
				$erro = "É necessário preencher todos os campos!";
			}
		}
		else
		{
			$erro = "Erro ao referenciar o diretorio.";	
		}
	
	break;
	
}

?>
<script>

window.parent.finish('<?= $erro ?>');

</script>
