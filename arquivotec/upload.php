<?php

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));

//VERIFICAR ESTE ARQUIVO JUNTAMENTE COM O GED.JS E O GED.PHP

//ALTERADO - 16/07/2017 - Carlos Abreu
function solicitacoes($id_os, $id_ged_arquivo, $funcao, $operacao)
{
	//gerencia o armazenamento das solicitacoes (operações solicitação/check-in e check-out)
	//funcao = 1 --> solicitacao
	//funcao = 2 --> checkin
	//funcao = 3 --> checkout
	//funcao = 4 --> desbloqueios
	//operacao = 1 --> adiciona
	//operacao = 2 --> retira
	//operacao = 0 --> limpa solicitação
		
	session_start();

	$db = new banco_dados();
	
	if($operacao==0)
	{
		$usql = "UPDATE ".DATABASE.".ged_solicitacoes SET ";
		$usql .= "ged_solicitacoes.reg_del = 1, ";
		$usql .= "ged_solicitacoes.reg_who = '" . $_SESSION['id_usuario']."', ";
		$usql .= "ged_solicitacoes.data_del = '".date('Y-m-d')."' ";
		$usql .= "WHERE ged_solicitacoes.id_os = '".$id_os."' ";
		$usql .= "AND ged_solicitacoes.id_funcionario = '".$_SESSION["id_funcionario"]."' ";
		$usql .= "AND ged_solicitacoes.reg_del = 0 ";
		
		$db->update($usql,'MYSQL');

		if ($db->erro != '')
		{
			die("Erro ao tentar excluir os dados.");
		}		
	}
	else
	{
		switch ($funcao)
		{
			case 1://solicitação
			{
				if($operacao==1)
				{
					$sql = "SELECT id_ged_solicitacoes FROM ".DATABASE.".ged_solicitacoes ";
					$sql .= "WHERE ged_solicitacoes.id_os = '".$id_os."' ";
					$sql .= "AND ged_solicitacoes.reg_del = 0 ";
					$sql .= "AND ged_solicitacoes.id_ged_arquivo = '".$id_ged_arquivo."' ";
					$sql .= "AND ged_solicitacoes.tipo = 1 ";
					$sql .= "AND ged_solicitacoes.id_funcionario = '".$_SESSION["id_funcionario"]."' ";
					
					$db->select($sql,'MYSQL',true);
					
					if ($db->erro != '');
					{
						die("Erro ao tentar selecionar os dados.");
					}
				
					if($db->numero_registros==0)
					{
						$isql = "INSERT INTO ".DATABASE.".ged_solicitacoes (id_os, id_ged_arquivo, id_funcionario, tipo, data_solicitacao) VALUES ( ";
						$isql .= "'".$id_os."', ";
						$isql .= "'".$id_ged_arquivo."', ";
						$isql .= "'".$_SESSION["id_funcionario"]."', ";
						$isql .= " 1, ";
						$isql .= "'".date('Y-m-d')."') ";
						
						$db->insert($isql,'MYSQL');
						
						if ($db->erro != '')
						{
							die("Erro ao tentar inserir os dados.");
						}	
					}			
				}
				else
				{
					$usql = "UPDATE ".DATABASE.".ged_solicitacoes SET ";
					$usql .= "ged_solicitacoes.reg_del = 1, ";
					$usql .= "ged_solicitacoes.reg_who = '" . $_SESSION['id_usuario'] ."', ";
					$usql .= "ged_solicitacoes.data_del = '".date('Y-m-d')."' ";
					$usql .= "WHERE ged_solicitacoes.id_os = '".$id_os."' ";
					$usql .= "AND ged_solicitacoes.id_ged_arquivo = '".$id_ged_arquivo."' ";
					$usql .= "AND ged_solicitacoes.tipo = 1 ";
					$usql .= "AND ged_solicitacoes.id_funcionario = '".$_SESSION["id_funcionario"]."' ";
					$usql .= "AND ged_solicitacoes.reg_del = 0 ";
				
					$db->update($usql,'MYSQL');
					
					if ($db->erro != '')
					{
						die("Erro ao tentar inserir os dados.");
					}				
				}
			}
			break;
			
			case 2://check-in
			{
				if($operacao==1)
				{
					$sql = "SELECT id_ged_solicitacoes FROM ".DATABASE.".ged_solicitacoes ";
					$sql .= "WHERE ged_solicitacoes.id_os = '".$id_os."' ";
					$sql .= "AND ged_solicitacoes.id_ged_arquivo = '".$id_ged_arquivo."' ";
					$sql .= "AND ged_solicitacoes.tipo = 2 ";
					$sql .= "AND ged_solicitacoes.id_funcionario = '".$_SESSION["id_funcionario"]."' ";
					$sql .= "AND ged_solicitacoes.reg_del = 0 ";
					
					$db->select($sql,'MYSQL',true);
					
					if ($db->erro != '')
					{
						die("Erro ao tentar selecionar os dados.");
					}
				
					if($db->numero_registros == 0)
					{
						$isql = "INSERT INTO ".DATABASE.".ged_solicitacoes (id_os, id_ged_arquivo, id_funcionario, tipo, data_solicitacao) VALUES ( ";
						$isql .= "'".$id_os."', ";
						$isql .= "'".$id_ged_arquivo."', ";
						$isql .= "'".$_SESSION["id_funcionario"]."', ";
						$isql .= " 2, ";
						$isql .= "'".date('Y-m-d')."') ";
						
						$db->insert($isql,'MYSQL');
						
						if ($db->erro != '')
						{
							die("Erro ao tentar inserir os dados.");
						}
					}
				}
				else
				{
					$usql = "UPDATE ".DATABASE.".ged_solicitacoes SET ";
					$usql .= "ged_solicitacoes.reg_del = 1, ";
					$usql .= "ged_solicitacoes.reg_who = '" . $_SESSION['id_usuario'] . "', ";
					$usql .= "ged_solicitacoes.data_del = '".date('Y-m-d')."' ";
					$usql .= "WHERE ged_solicitacoes.id_os = '".$id_os."' ";
					$usql .= "AND ged_solicitacoes.id_ged_arquivo = '".$id_ged_arquivo."' ";
					$usql .= "AND ged_solicitacoes.tipo = 2 ";
					$usql .= "AND ged_solicitacoes.id_funcionario = '".$_SESSION["id_funcionario"]."' ";
					$usql .= "AND ged_solicitacoes.reg_del = 0 ";
				
					$db->update($usql,'MYSQL');
					
					if ($db->erro != '')
					{
						die("Erro ao tentar inserir os dados.");		
					}
				}
			}
			break;
			
			case 3://check-out
			{
				if($operacao==1)
				{
					$sql = "SELECT id_ged_solicitacoes FROM ".DATABASE.".ged_solicitacoes ";
					$sql .= "WHERE ged_solicitacoes.id_os = '".$id_os."' ";
					$sql .= "AND ged_solicitacoes.id_ged_arquivo = '".$id_ged_arquivo."' ";
					$sql .= "AND ged_solicitacoes.tipo = 3 ";
					$sql .= "AND ged_solicitacoes.id_funcionario = '".$_SESSION["id_funcionario"]."' ";
					$sql .= "AND ged_solicitacoes.reg_del = 0 ";
					
					$db->select($sql,'MYSQL',true);
					
					if ($db->erro != '')
					{
						die("Erro ao tentar selecionar os dados.");
					}
				
					if($db->numero_registros == 0)
					{
						$isql = "INSERT INTO ".DATABASE.".ged_solicitacoes (id_os, id_ged_arquivo, id_funcionario, tipo, data_solicitacao) VALUES ( ";
						$isql .= "'".$id_os."', ";
						$isql .= "'".$id_ged_arquivo."', ";
						$isql .= "'".$_SESSION["id_funcionario"]."', ";
						$isql .= " 3, ";
						$isql .= "'".date('Y-m-d')."') ";
						
						$db->insert($isql,'MYSQL');
						
						if ($db->erro != '')
						{
							die("Erro ao tentar inserir os dados.");
						}
					}
				}
				else
				{
					$usql = "UPDATE ".DATABASE.".ged_solicitacoes SET ";
					$usql .= "ged_solicitacoes.reg_del = 1, ";
					$usql .= "ged_solicitacoes.reg_who = '" . $_SESSION['id_usuario'] . "', ";
					$usql .= "ged_solicitacoes.data_del = '".date('Y-m-d')."' ";
					$usql .= "WHERE ged_solicitacoes.id_os = '".$id_os."' ";
					$usql .= "AND ged_solicitacoes.id_ged_arquivo = '".$id_ged_arquivo."' ";
					$usql .= "AND ged_solicitacoes.tipo = 3 ";
					$usql .= "AND ged_solicitacoes.id_funcionario = '".$_SESSION["id_funcionario"]."' ";
					$usql .= "AND ged_solicitacoes.reg_del = 0 ";
				
					$db->update($usql,'MYSQL');
					
					if ($db->erro != '')
					{
						die("Erro ao tentar inserir os dados.");		
					}	
				}
			}
			
			case 4://solicitações
			{
				if($operacao==1)
				{
					$sql = "SELECT id_ged_solicitacoes FROM ".DATABASE.".ged_solicitacoes ";
					$sql .= "WHERE ged_solicitacoes.reg_del = 0 ";
					$sql .= "AND ged_solicitacoes.id_os = '".$id_os."' ";
					$sql .= "AND ged_solicitacoes.id_ged_arquivo = '".$id_ged_arquivo."' ";
					$sql .= "AND ged_solicitacoes.tipo = 4 ";
					$sql .= "AND ged_solicitacoes.id_funcionario = '".$_SESSION["id_funcionario"]."' ";
					
					$db->select($sql,'MYSQL',true);

					if ($db->erro != '')
					{
						die("Erro ao tentar selecionar os dados.".$sql);
					}
				
					if($db->numero_registros == 0)
					{
						$isql = "INSERT INTO ".DATABASE.".ged_solicitacoes (id_os, id_ged_arquivo, id_funcionario, tipo, data_solicitacao) VALUES ( ";
						$isql .= "'".$id_os."', ";
						$isql .= "'".$id_ged_arquivo."', ";
						$isql .= "'".$_SESSION["id_funcionario"]."', ";
						$isql .= " 4, ";
						$isql .= "'".date('Y-m-d')."') ";
						
						$db->insert($isql,'MYSQL');

						if ($db->erro != '')
						{
							die("Erro ao tentar incluir os dados.".$isql);
						}
	
					}
				}
				else
				{
					$usql = "UPDATE ".DATABASE.".ged_solicitacoes SET ";
					$usql .= "ged_solicitacoes.reg_del = 1, ";
					$usql .= "ged_solicitacoes.reg_who = '".$_SESSION["id_funcionario"]."', ";
					$usql .= "ged_solicitacoes.data_del = '".date("Y-m-d")."' ";
					$usql .= "WHERE ged_solicitacoes.id_os = '".$id_os."' ";
					$usql .= "AND ged_solicitacoes.reg_del = 0 ";
					$usql .= "AND ged_solicitacoes.id_ged_arquivo = '".$id_ged_arquivo."' ";
					$usql .= "AND ged_solicitacoes.tipo = 4 ";
					$usql .= "AND ged_solicitacoes.id_funcionario = '".$_SESSION["id_funcionario"]."' ";
				
					$db->update($usql,'MYSQL');
					
					if ($db->erro != '')
					{
						die("Erro ao tentar excluir os dados.".$usql);
					}		
	
				}
			}
			
			break;
		}
	}
}

$db = new banco_dados;

$result = 0;
$erro = 0;
$tamanho_format = 0;
$msg = "";

//se for solicitacao de desbloqueio
if($_POST["funcao"]=='desbloqueio')
{
	$arquivo = false;
	
	$nome_arquivo = "";
	
	$novo_nome_arquivo = "";
	
	$diretorio = "";
	
	$diretorio_tmp = DOCUMENTOS_GED . 'temp/';
	
	//passa nos arquivos
	foreach($_FILES as $chave=>$valor)
	{
		//não esta vazio o campo arquivo
		if(tiraacentos(addslashes($valor["name"]))!='')
		{			
			$nome_arquivo = tiraacentos(addslashes($valor["name"])); //nome do arquivo (ex. "arquivo.dwg")
			
			$nome_tmp_arquivo = $valor["tmp_name"];
			
			//Se ainda não existir a pasta temporaria, cria
			if(!is_dir($diretorio_tmp))
			{
				mkdir($diretorio_tmp);
			}
			
			//move o arquivo para uma pasta temporaria
			$move_arq = move_uploaded_file($nome_tmp_arquivo, $diretorio_tmp . $nome_arquivo);
			
			if($move_arq)
			{
				$arquivo = true;
			}
		}
	}
	
	//verifica os campos se vazios	
	if((empty(addslashes($_POST["motivo"])) && !$arquivo) || (empty($_POST["data_devolucao"]) || empty($_POST["status_devolucao"])))
	{
		$result = 0;
		
		$erro = 0;
		
		$tamanho_format = 0;
		
		$msg = " Os campos motivo e/ou anexo e/ou status e/ou data devem estar preenchidos!";
		
		sleep(1);
		
		?>
		<script>
		window.top.window.stopUpload(<?= $result ?>,<?= $_POST["id_os"] ?>,<?= "'". $nome_arquivo ."'" ?>,<?= "'". $tamanho_format ."'" ?>,<?= $erro ?>,<?= "'".$msg."'" ?>);
		</script>
		<?php		
	}
	else
	{
		//Preenche um array com dados de Usuários
		$sql = "SELECT funcionarios.id_funcionario, email, funcionario FROM ".DATABASE.".usuarios, ".DATABASE.".funcionarios ";
		$sql .= "WHERE funcionarios.id_usuario = usuarios.id_usuario ";
		$sql .= "AND usuarios.reg_del = 0 ";
		$sql .= "AND funcionarios.reg_del = 0 ";
		$sql .= "AND funcionarios.situacao NOT IN ('DESLIGADO') ";
	
		$db->select($sql,'MYSQL',true);
		
		if ($db->erro != '')
		{
			$result = 0;
			
			$erro = 0;
			
			$tamanho_format = 0;
			
			$msg = "";
		}
	
		foreach($db->array_select as $reg_usuarios)
		{
			$array_usremail[$reg_usuarios["id_funcionario"]] = $reg_usuarios["email"];
			$array_usrnome[$reg_usuarios["id_funcionario"]] = $reg_usuarios["funcionario"];		
		}	
			
		//filtra as versoes do arquivo	
		foreach($_POST as $campo=>$id_ged_versao)
		{
			$campo_versoes = explode("_",$campo);
			
			if($campo_versoes[0]=='idgedversao')
			{
				//insere o registro de desbloqueio
				$isql = "INSERT INTO ".DATABASE.".ged_desbloqueios (id_ged_versao, motivo_desbloqueio, status_devolucao, data_devolucao, nome_arquivo, data_solicitacao, id_funcionario_solicitante) VALUES(";
				$isql .= "'" . $id_ged_versao . "', ";
				$isql .= "'" . trim(addslashes($_POST["motivo"])) . "', ";
				$isql .= "'" . $_POST["status_devolucao"] . "', ";
				$isql .= "'" . php_mysql($_POST["data_devolucao"]) . "', ";
				$isql .= "'" . $nome_arquivo . "', ";
				$isql .= "'" . date('Y-m-d') . "', ";
				$isql .= "'" . $_SESSION["id_funcionario"] . "') ";
				
				$db->insert($isql,'MYSQL');
				
				$num_id = $db->insert_id;
				
				if($db->erro!='')
				{
					$erro = 1;
					
					$result = 0;
					
					$msg = "Erro ao tentar inserir os dados do arquivo: " . $db->erro;
				}
				else
				{					
					//busca diretorio dos arquivos
					$sql = "SELECT *, ged_arquivos.descricao AS num_arquivo FROM ".DATABASE.".numeros_interno, ".DATABASE.".setores, ".DATABASE.".ged_arquivos, ".DATABASE.".solicitacao_documentos_detalhes, ".DATABASE.".ged_versoes, ".DATABASE.".ordem_servico ";
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
						$result = 0;
						
						$erro = 0;
						
						$msg = $sql;
					}
					else
					{ 
						$regs = $db->array_select[0];
						
						$diretorio = DOCUMENTOS_GED . $regs["base"] . "/" . $regs["os"] . "/" . substr($regs["os"],0,4) . DISCIPLINAS . $regs["disciplina"] . "/" . $regs["atividade"] . "/" . $regs["sequencial"] . DIRETORIO_DESBLOQUEIOS;
					}
					
					//se tiver arquivo
					if($arquivo && $diretorio!="")
					{
						$array_flm = explode(".",$nome_arquivo);
						
						$extensao = $array_flm[count($array_flm)-1];
			
						$filename = preg_replace('/\.[^.]*$/', '', $nome_arquivo);
						
						$novo_nome_arquivo = $filename.'_'.sprintf("%05d",$num_id).'.'.$extensao;
						
						//Atualiza o registro do arquivo inserido, definindo a versão atual do arquivo como a versão inserida acima
						$usql = "UPDATE ".DATABASE.".ged_desbloqueios SET ";
						$usql .= "strarquivo = '".$novo_nome_arquivo ."' ";
						$usql .= "WHERE ged_desbloqueios.id_ged_desbloqueio = '" . $num_id . "' ";
						$usql .= "AND ged_desbloqueios.reg_del = 0 ";
						
						$db->update($usql,'MYSQL');	
							
						//Se ainda não existir a pasta de desbloqueios no diretório do arquivo, cria
						if(!is_dir($diretorio))
						{
							mkdir($diretorio);
						}
						else
						{
							$result = 0;
							
							$erro = 0;
							
							$msg = "O diretorio não foi criado ".$diretorio;						
						}
						
						//Verifica se o arquivo já existe
						if(is_file($diretorio . $novo_nome_arquivo))
						{
							$result = 0;
							
							$erro = 0;
							
							$msg = "O seguinte arquivo de comentário já existe para essa versão e não será incluído: ".$nome_arquivo;
						}
						else
						{
							//copia o arquivo do diretorio temporario							
							$move_comentario = copy($diretorio_tmp . $nome_arquivo, $diretorio . $novo_nome_arquivo);
						
							if($move_comentario)
							{								
								$result = 1;
								
								$erro = 0;
								
								$msg = "";	
							}
							else
							{
								$result = 0;
								
								$erro = 0;
								
								$msg = "Erro no diretorio ".$diretorio . $novo_nome_arquivo;	
							}							
																			
						}		
											
					}
					
					$array_num_arquivo[] = $regs["num_arquivo"];					
				}
			}
			
			//exclui o registro de solicitação
			if($campo_versoes[0]=='idgedarquivo')
			{
				$usql = "UPDATE ".DATABASE.".ged_solicitacoes SET ";
				$usql .= "ged_solicitacoes.reg_del = 1, ";
				$usql .= "ged_solicitacoes.reg_who = '".$_SESSION["id_funcionario"]."', ";
				$usql .= "ged_solicitacoes.data_del = '".date('Y-m-d')."' ";
				$usql .= "WHERE ged_solicitacoes.id_ged_arquivo = '" . $id_ged_versao . "' ";
				$usql .= "AND ged_solicitacoes.tipo = 4 "; //desbloqueios
				$usql .= "AND ged_solicitacoes.reg_del = 0  ";
				
				$db->update($usql,'MYSQL');
				
				solicitacoes($_POST["id_os"],$id_ged_versao,4,2);	
			}			
		}
		
		//envia email aos responsaveis		
		if(count($array_num_arquivo)>0)
		{
			$params 			= array();
			$params['from']		= 'arquivotecnico@dominio.com.br';
			$params['from_name']= "ARQUIVO DESBLOQUEADO GED";
			$params['subject']  = "SOLICITAÇÃO DE DESBLOQUEIO - COMENTÁRIOS";
			
			//solicitante
			if($array_usremail[$_SESSION["id_funcionario"]]!="")
			{
				$params['emails']['to'][] = array('email' => $array_usremail[$_SESSION["id_funcionario"]], 'nome' => $array_usrnome[$_SESSION["id_funcionario"]]); 
			}
			
			//arquivo tecnico
			$params['emails']['to'][] = array('email' => "arquivotecnico@".DOMINIO, 'nome' => "Arquivo Técnico");
			
			$str_mensagem = "<p>O(s) seguinte(s) documento(s) teve solicitação de desbloqueio: </p>";
			
			$sql = "SELECT os, id_cod_coord FROM ".DATABASE.".ordem_servico WHERE reg_del = 0 AND id_os = ".$_POST['id_os'];
			
			$db->select($sql, 'MYSQL', true);
			
			//Carlos Eduardo: 27/02/2018 - Adicionando o coordenador para receber este e-mail
			if($array_usremail[$db->array_select[0]['id_cod_coord']]!="")
			{
			    $params['emails']['to'][] = array('email' => $array_usremail[$db->array_select[0]['id_cod_coord']], 'nome' => $array_usrnome[$db->array_select[0]['id_cod_coord']]);
			}
			
			//Carlos Eduardo: 23/01/2018
			//$alocados = ProtheusDao::getAlocadosOS($db->array_select[0]['OS'], true);
			//TODOS OS ALOCADOS
			//foreach($alocados as $alocado)
			//{
			//    if(!empty($alocado['email']))
			//    {
			//        $params['emails']['to'][] = array('email' => $alocado['email'], 'nome' => $alocado['login']);
			//    }
			//}
			
			//Organizando por texto os nomes de arquivos
			sort($array_num_arquivo);
			
			foreach($array_num_arquivo as $num_arquivo)
			{
				$str_mensagem .= "<p>" . $num_arquivo . "</p>";
			}
			
			$str_mensagem .= "<p>em " . date('d/m/Y') . ", sendo registrado no sistema.</p>";
			$str_mensagem .= "<p>Solicitante: " . $array_usrnome[$_SESSION["id_funcionario"]] . "</p>";
			$str_mensagem .= "<p>Motivo do desbloqueio: " . trim(addslashes($_POST["motivo"])) . "</p>";
			$str_mensagem .= "<p> </p>";
			$str_mensagem .= "<p>Aguardando desbloqueio pelo arquivo técnico.</p>";
			
			$corpoEmail = "<html><body>" . $str_mensagem . "</body></html>";
			
			if(ENVIA_EMAIL)
			{

				$mail = new email($params);
				
				$mail->montaCorpoEmail($corpoEmail);

				if(!$mail->Send())
				{
					$result = 0;
					
					$erro = 0;
					
					$msg = "Erro e-mail!";
				}
				else
				{

					
					$result = 1;
					
					$erro = 0;	
				}
			}

			?>
				<script>
				alert('Solicitação enviada!');
				</script>
			<?php
			
		}
		
		sleep(1);
		
		//se tiver arquivo, exclui do diretorio temporario
		if($arquivo)
		{
			unlink($diretorio_tmp . $nome_arquivo);
		}
		
		?>
		<script>		
		window.top.window.stopUpload(<?= $result ?>,<?= $_POST["id_os"] ?>,<?= "'". $novo_nome_arquivo ."'" ?>,<?= "'". $tamanho_format ."'" ?>,<?= $erro ?>,<?= "'".$msg."'" ?>);
		</script>
		<?php	
	}
}

if($_POST["funcao"]=='comentario')
{
	$result = 0;
	
	$novo_nome_arquivo = "";
	
	$nome_arquivo = "";
	
	$erro = 0;
	
	if($_POST["id_ged_versao"])
	{
		$sql = "SELECT * FROM ".DATABASE.".numeros_interno, ".DATABASE.".setores, ".DATABASE.".ged_arquivos, ".DATABASE.".solicitacao_documentos_detalhes, ".DATABASE.".ged_versoes, ".DATABASE.".ordem_servico ";
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
		$sql .= "AND ged_versoes.id_ged_versao = '" . $_POST["id_ged_versao"] . "' ";
		
		$db->select($sql,'MYSQL',true);

		if($db->erro!='')
		{
			$erro = 3;
		}
		else
		{
			if($db->numero_registros>0)
			{			 
				$regs = $db->array_select[0];
				
				$diretorio = DOCUMENTOS_GED . $regs["base"] . "/" . $regs["os"] . "/" . substr($regs["os"],0,4) . DISCIPLINAS . $regs["disciplina"] . "/" . $regs["atividade"] . "/" . $regs["sequencial"];
				
				//Insere os comentários no banco - 20/04/2016
				$isql = "INSERT INTO ".DATABASE.".ged_comentarios (id_ged_versao, comentario, id_funcionario) VALUES(";
				$isql .= "'" . $regs["id_ged_versao"] . "', ";
				$isql .= "'" . trim(addslashes($_POST["motivo"])) . "', ";
				$isql .= "'" . $_SESSION["id_funcionario"] . "') ";
				
				$db->insert($isql,'MYSQL');							
							
				if($db->erro!='')
				{
					$erro = 3;
					
					$result = 0;
				}
				else
				{
					$id_comentario = $db->insert_id;
					
					$erro = 0;
					
					$result = 1;
	
					//Passa em todos os FILES do POST do xajax.upload
					foreach($_FILES as $chave=>$valor)
					{	
						//não esta vazio o campo arquivo
						if(tiraacentos(addslashes($valor["name"]))!='')
						{
							$nome_arquivo = tiraacentos(addslashes($valor["name"])); //nome do arquivo (ex. "arquivo.dwg")
							
							$nome_tmp_arquivo = $valor["tmp_name"];
							
							$array_flm = explode(".",$nome_arquivo);
							
							$extensao = $array_flm[count($array_flm)-1];
				
							$filename = preg_replace('/\.[^.]*$/', '', $nome_arquivo);
							
							$novo_nome_arquivo = $filename.'_'.sprintf("%05d",$id_comentario).'.'.$extensao;								
												
							//Se ainda não existir a pasta de comentários no diretório do arquivo, cria
							if(!is_dir($diretorio . DIRETORIO_COMENTARIOS))
							{
								mkdir($diretorio . DIRETORIO_COMENTARIOS);
							}
					
							//Verifica se o arquivo já existe
							if(is_file($diretorio . DIRETORIO_COMENTARIOS . $novo_nome_arquivo))
							{
								$result = 0;
								$erro = 7;
								$msg = "O seguinte arquivo de comentário já existe para essa versão e não será incluído: ".$valor["name"];
							}
							else
							{												
								//Move o arquivo para o diretório de comentários
								$move_comentario = move_uploaded_file($nome_tmp_arquivo,$diretorio . DIRETORIO_COMENTARIOS . $novo_nome_arquivo);											
				
								//Se foi movido com sucesso				
								if($move_comentario)
								{
									$usql = "UPDATE ".DATABASE.".ged_comentarios SET ";
									$usql .= "nome_arquivo = '".$nome_arquivo."', ";
									$usql .= "strarquivo = '".$novo_nome_arquivo."' ";
									$usql .= "WHERE id_ged_comentario = '".$id_comentario."' ";
									$usql .= "AND reg_del = 0 ";
									
									$db->update($usql,'MYSQL');							
												
									if($db->erro!='')
									{
										$erro = 7;
									}
									else
									{
										$result = 1;
										$erro = 0;	
									}			
									
								}
								else
								{
									$erro = 7;
									$msg = "Erro ao mover o arquivo";										
									$result = 0;
								}
							}				
						}
												
						sleep(1);
					}					
				}
			}
		}
		
		?>
		<script>		
		window.top.window.stopUpload(<?= $result ?>,<?= $_POST["id_ged_versao"] ?>,<?= "'". $novo_nome_arquivo ."'" ?>,<?= "'". $tamanho_format ."'" ?>,<?= $erro ?>,<?= "'".$msg."'" ?>);
		</script>
		<?php		
	}	
}

if($_POST["funcao"]=='comunicacao_interna')
{
	$params 			= array();
	$params['from']		= "arquivotecnico@".DOMINIO;
	$params['from_name']= "GED";
	
	$params['subject'] 	= "COMUNICAÇÃO INTERNA";
	
	$arquivo = FALSE;
	
	if(php_mysql($_POST["data_registro"])=="0000-00-00")
	{
		$erro = "A data retornada esta inválida.";
	}
	else
	{	
		if($_POST["id_os"]!=="" && $_POST["tipo_doc"]!=="" && $_POST["data_registro"]!=="")
		{
			if($_FILES["arquivo"]["name"]!=="")
			{
				$arquivo = TRUE;	
			}		
			
			//incluir
			if($_POST["acao"]=='incluir')
			{								
				//seleciona Cliente/OS para composição da pasta
				$sql = "SELECT ordem_servico.id_os, ordem_servico.os, ordem_servico.descricao, empresas.abreviacao_GED FROM ".DATABASE.".ordem_servico, ".DATABASE.".empresas ";
				$sql .= "WHERE ordem_servico.id_empresa = empresas.id_empresa ";
				$sql .= "AND ordem_servico.reg_del = 0 ";
				$sql .= "AND empresas.reg_del = 0 ";
				$sql .= "AND ordem_servico.id_os = '" . $_POST["id_os"] . "' ";
				
				$db->select($sql,'MYSQL',true);
				
				if($db->erro!='')
				{
					$erro = "Erro ao tentar selecionar os dados.".$sql;
				}
				else
				{		
					$reg_docsref = $db->array_select[0];											

					//seleciona o tipo de documento (CI)
					$sql = "SELECT * FROM ".DATABASE.".tipos_documentos_referencia, ".DATABASE.".tipos_referencia ";
					$sql .= "WHERE tipos_referencia.id_tipo_referencia = 3 ";
					$sql .= "AND tipos_referencia.reg_del = 0 ";
					$sql .= "AND tipos_documentos_referencia.reg_del = 0 ";
					$sql .= "AND tipos_documentos_referencia.id_tipo_referencia = tipos_referencia.id_tipo_referencia ";
			
					$db->select($sql,'MYSQL',true);
					
					if($db->erro!='')
					{
						$erro = "Erro ao tentar selecionar os dados.".$sql;
					}
					else
					{				
						$reg_tipo = $db->array_select[0];
						
						$id_disciplina = $reg_tipo["id_disciplina"];
						
						$id_tipo_doc = $reg_tipo["id_tipos_documentos_referencia"];
						
						$titulo = $reg_tipo["tipo_documento"];
					}					
					
					if($_POST["titulo"]!='')
					{
						$titulo	= $_POST["titulo"];
					}
					
					//seleciona a disciplina (TUB, MEC, etc)					
					$sql = "SELECT setores.abreviacao FROM ".DATABASE.".setores ";
					$sql .= "WHERE setores.id_setor = '" . $id_disciplina . "' ";
					$sql .= "AND setores.reg_del = 0 ";
			
					$db->select($sql,'MYSQL',true);
					
					if($db->erro!='')
					{
						$erro = "Erro ao tentar selecionar os dados.".$sql;
					}
					else
					{			
						$reg_setor = $db->array_select[0];									
					
						//Seleciona os dados da sequencia
						$sql = "SELECT sequencial FROM ".DATABASE.".documentos_referencia ";
						$sql .= "WHERE documentos_referencia.reg_del = 0 ";
						$sql .= "AND documentos_referencia.id_os = '" . $_POST["id_os"] . "' ";
						$sql .= "AND documentos_referencia.id_disciplina = '".$id_disciplina."' ";
						$sql .= "ORDER BY sequencial DESC LIMIT 1 ";
						
						$db->select($sql,'MYSQL',true);
					
						if($db->erro!='')
						{
							$erro = "Erro ao tentar selecionar os dados.".$sql;
						}
						else
						{																			
							$reg_num = $db->array_select[0];
							
							$num_sequencia = sprintf("%04d",$reg_num["sequencial"]+1);
							
							//EX: INT-0XXXX-TUB-TIPO DOC-SEQ
							$cod_dvm = PREFIXO_DOC_GED . sprintf("%05d",$reg_docsref["os"])."-".$reg_setor["abreviacao"]."-".$reg_tipo["abreviacao"]."-".$num_sequencia;
							
							//Insere os dados
							$isql = "INSERT INTO ".DATABASE.".documentos_referencia (id_os, id_disciplina, id_tipo_documento_referencia, numero_registro, numero_documento, sequencial, titulo, palavras_chave, origem, servico_id) VALUES( ";
							$isql .= "'" . $_POST["id_os"] . "', ";
							$isql .= "'" . $id_disciplina . "', ";
							$isql .= "'" . $id_tipo_doc . "', ";
							$isql .= "'" . $cod_dvm . "', ";
							
							//se o numero do cliente estiver vazio, grava o nome interno
							if($_POST["numero_documento"]=="")
							{
								$isql .= "'" . $cod_dvm . "', ";	
							}
							else
							{
								$isql .= "'" . addslashes(maiusculas($_POST["numero_documento"])) . "', ";	
							}								
							
							$isql .= "'" .$num_sequencia . "', ";
							$isql .= "'" . maiusculas(addslashes($titulo)) . "', ";
							$isql .= "'" . maiusculas(addslashes($_POST["palavras_chave"])) . "', ";
							$isql .= "'" . maiusculas(addslashes($_POST["origem"])) . "', ";
							$isql .= "'" .$_POST["servico"]."') ";
					
							$db->insert($isql,'MYSQL');
							
							$id_documento_referencia = $db->insert_id;															
							
							if($db->erro!='')
							{
								$erro = "Erro ao tentar inserir os dados.".$isql;
								
								$result = 0;
							}
							else
							{
								//Insere a revisão/revisao_documento --> 0/0
								$isql = "INSERT INTO ".DATABASE.".documentos_referencia_revisoes (id_documento_referencia, texto_ci, versao_documento, revisao_documento, data_registro, data_inclusao, id_autor, id_editor) VALUES( ";
								$isql .= "'" . $id_documento_referencia . "', ";
								$isql .= "'" . maiusculas(addslashes($_POST["texto_ci"])) . "', ";
								$isql .= "'" . maiusculas($_POST["versao_documento"]) . "', ";
								$isql .= "'0', ";
								$isql .= "'" . php_mysql($_POST["data_registro"]) . "', ";
								$isql .= "'" . date("Y-m-d") . "', ";
								$isql .= "'" . $_SESSION["id_funcionario"] . "', ";
								$isql .= "'0') ";
								
								$db->insert($isql,'MYSQL');
								
								if($db->erro!='')
								{
									$erro = "Erro ao tentar inserir os dados.".$isql;
									
									$result = 0;
								}
								else
								{																
									$id_documento_referencia_rev = $db->insert_id;
									
									//Atualiza o arquivo atual
									$usql = "UPDATE ".DATABASE.".documentos_referencia SET ";
									$usql .= "id_documento_referencia_revisoes = '".$id_documento_referencia_rev."' ";
									$usql .= "WHERE id_documento_referencia = '".$id_documento_referencia."' ";
									$usql .= "AND reg_del = 0 ";
									
									$db->update($usql,'MYSQL');
									
									if($db->erro!='')
									{
										$erro = "Erro ao tentar atualizar os dados.".$usql;
									} 
									else
									{										
										//se tiver arquivo, salva							
										if($arquivo)
										{
											//verifica se grava as disciplinas nas pastas
											//ex: ATAS/MEC
											if($reg_tipo["grava_disciplina"]==1)
											{
												$disciplina = $reg_setor["abreviacao"]."/";	
											}
											else
											{
												$disciplina = "";	
											}			
							
											$array_rpl = array("/",".",":","&",")","(","{","}");
											
											$abreviacao_cliente = str_replace($array_rpl, " ",maiusculas(tiraacentos(trim($reg_docsref["abreviacao_GED"]))));		
											
											$descricao_os = str_replace($array_rpl," ",maiusculas(tiraacentos(trim($reg_docsref["descricao"]))));
											
											//monta diretorio base
											$diretorio = DOCUMENTOS_GED . $abreviacao_cliente . "/" . $reg_docsref["os"] . "-" .$descricao_os . "/" . $reg_docsref["os"] . REFERENCIAS . $reg_tipo["pasta_base"] . "/".$disciplina;
										
											//Se não existir a PASTA
											//ex: ./documentos/abr.cliente/os-descricao/os-REFERENCIA/pasta_base/disciplina/arquivo
											if(!is_dir($diretorio))
											{						
												if(!mkdir($diretorio,0777,true))
												{
													$erro = "Erro ao tentar criar a pasta no servidor.";
												}						
											}
											
											//Obtem o nome do arquivo		
											$nome_arquivo = tiraacentos(addslashes($_FILES["arquivo"]["name"]));
											
											//retira a extensão
											$array_flm = explode(".",$nome_arquivo);
											
											$extensao = $array_flm[count($array_flm)-1];
											
											//Checa se o arquivo já existe e move o arquivo para o lugar correto
											if(!is_file($diretorio . $cod_dvm.".".$extensao))
											{				
												//Move o arquivo
												$arq_valid = move_uploaded_file($_FILES["arquivo"]["tmp_name"],$diretorio . $cod_dvm.".".$extensao);
												
												if($arq_valid)
												{	
													$usql = "UPDATE ".DATABASE.".documentos_referencia_revisoes SET ";
													$usql .= "nome_arquivo = '".$nome_arquivo."', ";
													$usql .= "arquivo = '".$cod_dvm.".".$extensao."' ";
													$usql .= "WHERE id_documentos_referencia_revisoes = '".$id_documento_referencia_rev."' ";
													$usql .= "AND reg_del = 0 ";
													
													$db->update($usql,'MYSQL');
												}
												else
												{
													$erro = "O documento não foi gravado no diretorio.";
												}
											}
											else
											{
												//Se o arquivo já existir
												$erro = "O documento existe no caminho especificado.";
											}															
										}										
										
										//ENVIA EMAIL A EQUIPE QUANDO TIPO DOCUMENTO FOR COMUNICAÇÃO INTERNA	
																						
										//Obtém os Nomes/e-mails da equipe do projeto
										$sql = "SELECT funcionario, email FROM ".DATABASE.".os_x_funcionarios, ".DATABASE.".funcionarios, ".DATABASE.".usuarios ";
										$sql .= "WHERE os_x_funcionarios.id_os = '".$_POST["id_os"]."'  ";
										$sql .= "AND os_x_funcionarios.reg_del = 0 ";
										$sql .= "AND funcionarios.reg_del = 0 ";
										$sql .= "AND usuarios.reg_del = 0 ";
										$sql .= "AND os_x_funcionarios.id_funcionario = funcionarios.id_funcionario ";
										$sql .= "AND funcionarios.id_usuario = usuarios.id_usuario ";
										$sql .= "ORDER BY funcionario ";
										
										$db->select($sql,'MYSQL',true);
										
										if($db->erro!='')
										{
											$erro = "Erro ao tentar selecionar os dados.".$sql;
										} 
										else
										{
			
											foreach($db->array_select as $reg_equipe)
											{							
												if($reg_equipe["email"]!='')
												{
													$params['emails']['to'][] = array('email' => $reg_equipe["email"], 'nome' => $reg_equipe["funcionario"]);
												}											
											}												
										
											//Obtém o funcionario emissor
											$sql = "SELECT funcionario, email FROM ".DATABASE.".funcionarios, ".DATABASE.".usuarios ";
											$sql .= "WHERE funcionarios.id_funcionario = '" . $_SESSION["id_funcionario"] . "' ";
											$sql .= "AND funcionarios.reg_del = 0 ";
											$sql .= "AND usuarios.reg_del = 0 ";
											$sql .= "AND funcionarios.id_usuario = usuarios.id_usuario ";
									
											$db->select($sql,'MYSQL',true);
									
											$reg_fun = $db->array_select[0];
											
											$params['emails']['to'][] = array('email' => $reg_equipe["email"], 'nome' => $reg_fun["funcionario"]);
											
											$texto = "<p>Incluído CI no sistema:</p>";
											$texto .= "<div id='div_doc'><strong>Nº: </strong> " . $cod_dvm . "</div>";
											$texto .= "<div id='div_titulo'><strong>Assunto:</strong> " . maiusculas($titulo). "</div>";
											$texto .= "<div id='div_titulo'><strong>Emitente:</strong> " . $reg_fun["funcionario"]. "</div>";
											$texto .= "<div id='div_data'><strong>Data:</strong> " . date("d/m/Y") . "</div>";
											$texto .= "<p>".maiusculas(addslashes($_POST["texto_ci"]))."</p>";
											
											if($arquivo)
											{
												$texto .= "<p><strong>OBS: Existe arquivo anexado.</strong></p>";
											}											
										
											$corpoEmail = "<html><body>" . $texto . "</body></html>";
											
											if(ENVIA_EMAIL)
											{

												$mail = new email($params);
												
												$mail->montaCorpoEmail($corpoEmail);
												
												if(!$mail->Send())
												{
													$erro = "Erro ao enviar e-mail!!! ".$mail->ErrorInfo;
												}

											}

											$result = 1;
										}									
									}
								}
							}
						}
					}
				}				
			}
			else //editar
			{				
				//Seleciona os dados da versao_documento/revisao_documento atual
				$sql = "SELECT * FROM ".DATABASE.".documentos_referencia, ".DATABASE.".documentos_referencia_revisoes ";
				$sql .= "WHERE documentos_referencia.reg_del = 0 ";
				$sql .= "AND documentos_referencia_revisoes.reg_del = 0 ";
				$sql .= "AND documentos_referencia_revisoes.id_documento_referencia = '" . $_POST["id_documento_referencia"] . "' ";
				$sql .= "AND documentos_referencia.id_documento_referencia = documentos_referencia_revisoes.id_documento_referencia ";
				$sql .= "ORDER BY revisao_documento DESC LIMIT 1 ";
				
				$db->select($sql,'MYSQL',true);

				if($db->erro!='')
				{
					$erro = "Erro ao tentar selecionar os dados.".$sql;
				}
				else
				{			
					$reg_ver = $db->array_select[0];
					
					$id_documento_referencia = $reg_ver["id_documento_referencia"];
					
					//seleciona Cliente/OS para composição da pasta
					$sql = "SELECT ordem_servico.id_os, ordem_servico.os, ordem_servico.descricao, empresas.abreviacao_GED FROM ".DATABASE.".ordem_servico, ".DATABASE.".empresas ";
					$sql .= "WHERE ordem_servico.id_empresa = empresas.id_empresa ";
					$sql .= "AND ordem_servico.reg_del = 0 ";
					$sql .= "AND empresas.reg_del = 0 ";
					$sql .= "AND ordem_servico.id_os = '" . $reg_ver["id_os"] . "' ";
					
					$db->select($sql,'MYSQL',true);
				
					if($db->erro!='')
					{
						$erro = "Erro ao tentar selecionar os dados.".$sql;
					} 
					else
					{
						$reg_docsref = $db->array_select[0];
				
						//seleciona a disciplina (TUB, MEC, etc)
						$sql = "SELECT setores.abreviacao FROM ".DATABASE.".setores ";
						$sql .= "WHERE setores.id_setor = '" . $reg_ver["id_disciplina"] . "' ";
						$sql .= "AND setores.reg_del = 0 ";
				
						$db->select($sql,'MYSQL',true);
						
						if($db->erro!='')
						{
							$erro = "Erro ao tentar selecionar os dados.".$sql;
						} 
						else
						{
							$reg_setor = $db->array_select[0];
							
							//seleciona o tipo de documento (CI)
							$sql = "SELECT * FROM ".DATABASE.".tipos_documentos_referencia, ".DATABASE.".tipos_referencia ";
							$sql .= "WHERE tipos_documentos_referencia.id_tipos_documentos_referencia = '" . $reg_ver["id_tipo_documento_referencia"] . "' ";
							$sql .= "AND tipos_documentos_referencia.reg_del = 0 ";
							$sql .= "AND tipos_referencia.reg_del = 0 ";
							$sql .= "AND tipos_documentos_referencia.id_tipo_referencia = tipos_referencia.id_tipo_referencia ";
					
							$db->select($sql,'MYSQL',true);
							
							if($db->erro!='')
							{
								$erro = "Erro ao tentar selecionar os dados.".$sql;
							} 
							else
							{
								$reg_tipo = $db->array_select[0];								
									
								//Atualiza o arquivo atual
								$usql = "UPDATE ".DATABASE.".documentos_referencia SET ";
								$usql .= "numero_documento = '".maiusculas(addslashes($_POST["numero_documento"]))."', ";
								$usql .= "titulo = '".maiusculas(addslashes($_POST["titulo"]))."', ";
								$usql .= "palavras_chave = '".maiusculas(addslashes($_POST["palavras_chave"]))."', ";
								$usql .= "origem = '".maiusculas(addslashes($_POST["origem"]))."', ";
								$usql .= "servico_id = '".maiusculas(addslashes($_POST["servico"]))."' ";
								$usql .= "WHERE id_documento_referencia = '".$reg_ver["id_documento_referencia"]."' ";
								$usql .= "AND reg_del = 0 ";
								
								$db->update($usql,'MYSQL');
								
								if($db->erro!='')
								{
									$erro = "Erro ao tentar atualizar os dados.".$usql;
								}
								else
								{
									$usql = "UPDATE ".DATABASE.".documentos_referencia_revisoes SET ";
									$usql .= "data_registro = '".php_mysql($_POST["data_registro"])."', ";
									$usql .= "data_inclusao = '".date('Y-m-d')."', ";
									$usql .= "id_editor = '".$_SESSION["id_funcionario"]."', ";
									$usql .= "texto_ci = '".maiusculas(addslashes($_POST["texto_ci"]))."', ";
									$usql .= "versao_documento = '".$_POST["versao_documento"]."' ";
									$usql .= "WHERE id_documentos_referencia_revisoes = '".$reg_ver["id_documentos_referencia_revisoes"]."' ";
									$usql .= "AND reg_del = 0 ";
									
									$db->update($usql,'MYSQL');		
									
									//se tiver arquivo								
									if($arquivo)
									{
										//verifica se grava as disciplinas nas pastas
										//ex: ATAS/MEC
										if($reg_tipo["grava_disciplina"]==1)
										{
											$disciplina = $reg_setor["abreviacao"]."/";	
										}
										else
										{
											$disciplina = "";	
										}							
								
										$array_rpl = array("/",".",":","&",")","(","{","}");
										
										$abreviacao_cliente = str_replace($array_rpl, " ",maiusculas(tiraacentos(trim($reg_docsref["abreviacao_GED"]))));		
										
										$descricao_os = str_replace($array_rpl," ",maiusculas(tiraacentos(trim($reg_docsref["descricao"]))));
										
										//monta diretorio base
										$diretorio = DOCUMENTOS_GED . $abreviacao_cliente . "/" . $reg_docsref["os"] . "-" .$descricao_os . "/" . $reg_docsref["os"] . REFERENCIAS . $reg_tipo["pasta_base"] . "/".$disciplina;
										
										$dir_erro = false;
										
										
										//Se ainda não existir a pasta de versões no diretório do arquivo, cria
										if(!is_dir($diretorio . "_versoes"))
										{
											//Se a criação do diretório não for feita com sucesso
											if(!mkdir($diretorio . "_versoes",0777,true))
											{
												$erro = "Erro ao criar o diretório de versões.";
											
												$dir_erro = true;
											}					
											
										}
																					
										//em caso de erro na criação do diretório, aborta
										if(!$dir_erro)
										{	
											//Move o arquivo atual para a pasta _versoes, com a extensão do id documentos_referencia_revisoes
											$move_antigo = rename($diretorio.$reg_ver["arquivo"],$diretorio . "_versoes/" . $reg_ver["nome_arquivo"] . "." . $reg_ver["id_documentos_referencia_revisoes"]);
											
											//Obtem o nome do arquivo		
											$nome_arquivo = tiraacentos(addslashes($_FILES["arquivo"]["name"]));
											
											//retira a extensão do arquivo
											$array_flm = explode(".",$nome_arquivo);
												
											$extensao = $array_flm[count($array_flm)-1];
											
											$arq_bd = explode(".",$reg_ver["arquivo"]);
																							
											//copia o novo arquivo para o diretorio
											$move_novo = move_uploaded_file($_FILES["arquivo"]["tmp_name"], $diretorio . $arq_bd[0].".".$extensao);
										}
										
										if($move_novo)
										{
											$usql = "UPDATE ".DATABASE.".documentos_referencia_revisoes SET ";
											$usql .= "nome_arquivo = '".$nome_arquivo."', ";
											$usql .= "arquivo = '".$arq_bd[0].".".$extensao."' ";
											$usql .= "WHERE id_documentos_referencia_revisoes = '".$reg_ver["id_documentos_referencia_revisoes"]."' ";
											$usql .= "AND reg_del = 0 ";
											
											$db->update($usql,'MYSQL');																				
										}
										else
										{
											$erro = "Erro no upload do arquivo.".$move_novo."#".$move_antigo;
										}
									}
									
									//ENVIA EMAIL A EQUIPE QUANDO TIPO DOCUMENTO FOR COMUNICAÇÃO						
									//Obtém os Nomes/e-mails da equipe do projeto
									$sql = "SELECT funcionario, email FROM ".DATABASE.".os_x_funcionarios, ".DATABASE.".funcionarios, ".DATABASE.".usuarios ";
									$sql .= "WHERE os_x_funcionarios.id_os = '".$_POST["id_os"]."'  ";
									$sql .= "AND os_x_funcionarios.reg_del = 0 ";
									$sql .= "AND funcionarios.reg_del = 0 ";
									$sql .= "AND usuarios.reg_del = 0 ";
									$sql .= "AND os_x_funcionarios.id_funcionario = funcionarios.id_funcionario ";
									$sql .= "AND funcionarios.id_usuario = usuarios.id_usuario ";
									$sql .= "ORDER BY funcionario ";
									
									$db->select($sql,'MYSQL',true);
									
									if($db->erro!='')
									{
										$erro = "Erro ao tentar selecionar os dados.".$sql;
									}
									else
									{
										$params 			= array();
										$params['from']		= 'arquivotecnico@dominio.com.br';
										$params['from_name']= 'Arquivo Tecnico';
																
										foreach($db->array_select as $reg_equipe)
										{							
											if($reg_equipe["email"]!='')
											{
												$params['emails']['to'][] = array('email' => $reg_equipe["email"], 'nome' => $reg_equipe["funcionario"]);
											}
										}													
					
										//Obtém o funcionario emissor
										$sql = "SELECT funcionario, email FROM ".DATABASE.".funcionarios, ".DATABASE.".usuarios ";
										$sql .= "WHERE funcionarios.id_funcionario = '" . $_SESSION["id_funcionario"] . "' ";
										$sql .= "AND funcionarios.reg_del = 0 ";
										$sql .= "AND usuarios.reg_del = 0 ";
										$sql .= "AND funcionarios.id_usuario = usuarios.id_usuario ";
								
										$db->select($sql,'MYSQL',true);
										
										if($db->erro!='')
										{
											$erro = "Erro ao tentar selecionar os dados.".$sql;
										} 
										else
										{
											$reg_fun = $db->array_select[0];
											
											$params['subject'] = "COMUNICAÇÃO INTERNA: " . $reg_ver["numero_registro"] . " ALTERADA";
											$params['emails']['to'][] = array('email' => $reg_fun["email"], 'nome' => $reg_fun["funcionario"]);
																							
											$texto = "<p>Alterada CI no sistema:</p>";
											$texto .= "<div id='div_doc'><strong>Nº: </strong> " . $_POST["numero_registro"] . "</div>";
											$texto .= "<div id='div_titulo'><strong>Assunto:</strong> " . maiusculas(addslashes($_POST["titulo"])). "</div>";
											$texto .= "<div id='div_titulo'><strong>Emitente:</strong> " . $reg_fun["funcionario"]. "</div>";
											$texto .= "<div id='div_data'><strong>Data:</strong> " . date("d/m/Y") . "</div>";
											$texto .= "<p>".maiusculas(addslashes($_POST["texto_ci"]))."</p>";
											
											if($arquivo)
											{
												$texto .= "<p><strong>OBS: Existe arquivo anexado.</strong></p>";
											}
											
											$corpoEmail = "<html><body>" . $texto . "</body></html>";
											
											if(ENVIA_EMAIL)
											{

												$mail = new email($params);
												
												$mail->montaCorpoEmail($corpoEmail);

												if(!$mail->Send())
												{
													$erro = "Erro ao enviar e-mail!!! ".$mail->ErrorInfo;
												}

											}

											$erro = "";
													
											$result = 1;									
											
										}
									}
								}							
							}
						}
					}
				}				
			}		
		}
		else
		{
			$erro = "Os campos devem estar preenchidos.";
		}
	}
	
	?>
	<script>
	window.top.window.stopUpload_referencias(<?= $result ?>,<?= "'".$erro."'" ?>,<?= $id_documento_referencia ?>);
	</script>
	<?php		
}

if($_POST["funcao"]=='documento_referencia')
{
	$id_documento_referencia = 0;
	
	$incluir = false;
	
	$id_os = "";
	$tipo_doc = "";
	$arquivo = "";
	$arquivo_ed = "";
	$id_disciplina = "";
	$id_tipo_doc = "";
	
	if($_POST["acao"]=='incluir')
	{
		$id_os = $_POST["inc_id_os"];
		$tipo_doc = $_POST["inc_tipo_doc"];
		$arquivo = addslashes($_FILES["inc_arquivo"]["name"]);
		$id_disciplina = $_POST["inc_id_disciplina"];
		$id_tipo_doc = $_POST["inc_id_tipo_doc"];	
	}
	else
	{
		if($_POST["acao"]=='atualizar')
		{
			$id_os = $_POST["id_os"];
			$tipo_doc = $_POST["tipo_doc"];
			$arquivo = addslashes($_FILES["arquivo"]["name"]);
			$arquivo_ed = $_POST["arquivo_ed"];
			$id_disciplina = $_POST["id_disciplina"];
			$id_tipo_doc = $_POST["id_tipo_doc"];				
		}
	}
	
	if($id_os!='' && $tipo_doc!='' && ($arquivo!='' || $arquivo_ed!=''))
	{
		if($tipo_doc!=1)
		{
			$incluir = true;
		}
		else
		{
			if($id_disciplina!='' && $id_tipo_doc!='')
			{
				$incluir = true;	
			}
			else
			{
				$incluir = false;
			}
		}
	}
	else
	{
		$incluir = false;	
	}	
	
	if($incluir)
	{
		//incluir
		if($_POST["acao"]=='incluir')
		{
			if($_FILES["inc_arquivo"]["name"]!=="")
			{
				$arquivo = TRUE;	
			}
								
			//seleciona Cliente/OS para composição da pasta
			$sql = "SELECT ordem_servico.id_os, ordem_servico.os, ordem_servico.descricao, empresas.abreviacao_GED FROM ".DATABASE.".ordem_servico, ".DATABASE.".empresas ";
			$sql .= "WHERE ordem_servico.id_empresa = empresas.id_empresa ";
			$sql .= "AND ordem_servico.reg_del = 0 ";
			$sql .= "AND empresas.reg_del = 0 ";
			$sql .= "AND ordem_servico.id_os = '" . $_POST["inc_id_os"] . "' ";
			
			$db->select($sql,'MYSQL',true);
			
			if($db->erro!='')
			{
				$erro = "Erro ao tentar selecionar os dados.".$sql;
			}
			else
			{		
				$reg_docsref = $db->array_select[0];											
	
				//Se tipo documento for Referencia técnica, escolhe disciplina
				//caso contrário, seta a disciplina do tipo documento
				if($_POST["inc_tipo_doc"]==1)
				{
					//seleciona o tipo de documento (CI, ATA, PROPOSTA, ETC)
					$sql = "SELECT * FROM ".DATABASE.".tipos_documentos_referencia, ".DATABASE.".tipos_referencia ";
					$sql .= "WHERE tipos_documentos_referencia.id_tipos_documentos_referencia = '" . $_POST["inc_id_tipo_doc"] . "' ";
					$sql .= "AND tipos_documentos_referencia.reg_del = 0 ";
					$sql .= "AND tipos_referencia.reg_del = 0 ";
					$sql .= "AND tipos_documentos_referencia.id_tipo_referencia = tipos_referencia.id_tipo_referencia ";
			
					$db->select($sql,'MYSQL',true);
					
					if($db->erro!='')
					{
						$erro = "Erro ao tentar selecionar os dados.".$sql;
					}
					else
					{				
						$reg_tipo = $db->array_select[0];
						
						$id_disciplina = $_POST["inc_id_disciplina"];
						
						$id_tipo_doc = $_POST["inc_id_tipo_doc"];
						
						$titulo = $reg_tipo["tipo_documento"];
						
						$grava_disciplina = $reg_tipo["grava_disciplina"];
					}
				}
				else
				{
					//seleciona o tipo de documento (CI, ATA, PROPOSTA, ETC)
					$sql = "SELECT * FROM ".DATABASE.".tipos_documentos_referencia, ".DATABASE.".tipos_referencia ";
					$sql .= "WHERE tipos_referencia.id_tipo_referencia = '" . $_POST["inc_tipo_doc"] . "' ";
					$sql .= "AND tipos_documentos_referencia.reg_del = 0 ";
					$sql .= "AND tipos_referencia.reg_del = 0 ";
					$sql .= "AND tipos_documentos_referencia.id_tipo_referencia = tipos_referencia.id_tipo_referencia ";
			
					$db->select($sql,'MYSQL',true);
					
					if($db->erro!='')
					{
						$erro = "Erro ao tentar selecionar os dados.".$sql;
					}
					else
					{				
						$reg_tipo = $db->array_select[0];
						
						$id_disciplina = $reg_tipo["id_disciplina"];
						
						$id_tipo_doc = $reg_tipo["id_tipos_documentos_referencia"];
						
						$titulo = $reg_tipo["tipo_documento"];
						
						$grava_disciplina = $reg_tipo["grava_disciplina"];
					}					
				}
				
				if($_POST["inc_titulo"]!='')
				{
					$titulo	= $_POST["inc_titulo"];
				}
				
				//seleciona a disciplina (TUB, MEC, etc)
				$sql = "SELECT setores.abreviacao FROM ".DATABASE.".setores ";
				$sql .= "WHERE setores.id_setor = '" . $id_disciplina . "' ";
				$sql .= "AND setores.reg_del = 0 ";
		
				$db->select($sql,'MYSQL',true);
				
				if($db->erro!='')
				{
					$erro = "Erro ao tentar selecionar os dados.".$sql;
				}
				else
				{			
					$reg_setor = $db->array_select[0];				
				
					//Seleciona os dados da sequencia do documentos de referencia
					$sql = "SELECT sequencial FROM ".DATABASE.".documentos_referencia ";
					$sql .= "WHERE documentos_referencia.reg_del = 0 ";
					$sql .= "AND documentos_referencia.id_os = '" . $_POST["inc_id_os"] . "' ";
					$sql .= "AND documentos_referencia.id_disciplina = '".$id_disciplina."' ";
					$sql .= "ORDER BY sequencial DESC LIMIT 1 ";
					
					$db->select($sql,'MYSQL',true);
				
					if($db->erro!='')
					{
						$erro = "Erro ao tentar selecionar os dados.".$sql;
					}
					else
					{																			
						$reg_num = $db->array_select[0];
						
						//Seleciona os dados da sequencia do documentos de referencia incluidos
						$sql = "SELECT sequencial FROM ".DATABASE.".documentos_referencia_inclusao ";
						$sql .= "WHERE documentos_referencia_inclusao.reg_del = 0 ";
						$sql .= "AND documentos_referencia_inclusao.id_os = '" . $_POST["inc_id_os"] . "' ";
						$sql .= "AND documentos_referencia_inclusao.id_disciplina = '".$id_disciplina."' ";
						$sql .= "ORDER BY sequencial DESC LIMIT 1 ";
						
						$db->select($sql,'MYSQL',true);
						
						$reg_num_inc = $db->array_select[0];
						
						//se a sequencia já estiver incluida, verifica se é >= a anterior
						if($reg_num_inc["sequencial"]>=$reg_num["sequencial"])
						{
							$sequencial = $reg_num_inc["sequencial"];
						}
						else
						{
							$sequencial = $reg_num["sequencial"];
						}
						
						$num_sequencia = sprintf("%04d",$sequencial+1);
						
						//EX: INT-0XXXX-TUB-TIPO DOC-SEQ
						$cod_dvm =  PREFIXO_DOC_GED . sprintf("%05d",$reg_docsref["os"])."-".$reg_setor["abreviacao"]."-".$reg_tipo["abreviacao"]."-".$num_sequencia;
						
						$array_rpl = array("/",".",":","&",")","(","{","}");
						
						$abreviacao_cliente = str_replace($array_rpl, " ",maiusculas(tiraacentos(trim($reg_docsref["abreviacao_GED"]))));		
						
						$descricao_os = str_replace($array_rpl," ",maiusculas(tiraacentos(trim($reg_docsref["descricao"]))));
						
						//monta diretorio temporario
						//$diretorio_tmp = DOCUMENTOS_GED . $abreviacao_cliente . "/" . $reg_docsref["os"] . "-" .$descricao_os . "/" . $reg_docsref["os"] . REFERENCIAS . $reg_tipo["pasta_base"] . "/".$disciplina;
						$diretorio = DOCUMENTOS_GED . $abreviacao_cliente . "/" . $reg_docsref["os"] . "-" .$descricao_os . "/" . $reg_docsref["os"] . REFERENCIAS . "temp/";
	
						//Obtem o nome do arquivo		
						$nome_arquivo = tiraacentos($_FILES["inc_arquivo"]["name"]);
						
						//retira a extensão					
						$array_flm = explode(".",$nome_arquivo);
						
						$extensao = $array_flm[count($array_flm)-1];
			
						$filename = preg_replace('/\.[^.]*$/', '', $nome_arquivo);
	
						//Checa se o arquivo já existe, se existir não inclui
						if(!is_file($diretorio . $cod_dvm.".".$extensao))
						{					
							//Insere os dados
							$isql = "INSERT INTO ".DATABASE.".documentos_referencia_inclusao (id_os, id_disciplina, id_tipo_documento_referencia, id_formato, id_autor, numero_registro, numero_documento, sequencial, titulo, palavras_chave, origem, versao_documento, numero_grd_cliente, data_registro, data_inclusao, edital, certificado, servico_id) VALUES( ";
							$isql .= "'" . $_POST["inc_id_os"] . "', ";
							$isql .= "'" . $id_disciplina . "', ";
							$isql .= "'" . $id_tipo_doc . "', ";
							$isql .= "'" . $_POST["inc_id_formato"] . "', ";
							$isql .= "'" . $_SESSION["id_funcionario"] . "', ";
							$isql .= "'" . $cod_dvm . "', ";
							
							//se o numero do cliente estiver vazio, grava o nome Interno
							if($_POST["inc_numdocumento"]=="")
							{
								$isql .= "'" . $cod_dvm . "', ";	
							}
							else
							{
								$isql .= "'" . maiusculas(trim(addslashes($_POST["inc_numdocumento"]))) . "', ";	
							}								
							
							$isql .= "'".$num_sequencia ."', ";
							$isql .= "'" . maiusculas(trim(addslashes($titulo))) . "', ";
							$isql .= "'" . maiusculas(trim(addslashes($_POST["inc_palavras_chave"]))) . "', ";
							$isql .= "'" . maiusculas(trim(addslashes($_POST["inc_origem"]))) . "', ";
							$isql .= "'" . maiusculas(trim(addslashes($_POST["inc_revisao"]))) . "', ";
							$isql .= "'" . maiusculas(trim(addslashes($_POST["inc_num_grd"]))) . "', ";
							$isql .= "'" . php_mysql($_POST["inc_data_registro"]) . "', ";
							$isql .= "'" . date('Y-m-d') . "', ";
							$isql .= "'" . $_POST["inc_chk_edital"] . "', ";
							$isql .= "'" . $_POST["inc_chk_cert"] . "', "; 
							$isql .= "'" . $_POST["inc_servico"] . "') ";
					
							$db->insert($isql,'MYSQL');
							
							if($db->erro!='')
							{
								$erro = "Erro ao tentar inserir os dados.".$isql;
								
								$result = 0;
							}
							else
							{					
								$id_documento_referencia_inc = $db->insert_id;
								
								$result = 1;						
							}					
							
							//Se tiver arquivo, grava em diretorio temporario							
							if($arquivo)
							{
								//Se não existir a PASTA, cria
								//ex: ./documentos/abr.cliente/os-descricao/os-REFERENCIA/pasta_base/disciplina/arquivo
								if(!is_dir($diretorio))
								{						
									if(!mkdir($diretorio,0777,true))
									{
										$erro = "Erro ao tentar criar a pasta no servidor.";
									}						
								}
								
								//Checa se o arquivo já existe e move o arquivo para o lugar correto
								if(!is_file($diretorio . $cod_dvm.".".$extensao))
								{				
									//Move o arquivo
									$arq_valid = move_uploaded_file($_FILES["inc_arquivo"]["tmp_name"],$diretorio . $cod_dvm.".".$extensao);
								
									//se tudo correto, salva o nome arquivo no banco
									if($arq_valid)
									{
										$usql = "UPDATE ".DATABASE.".documentos_referencia_inclusao SET ";
										$usql .= "documentos_referencia_inclusao.nome_arquivo = '".$nome_arquivo."', ";
										$usql .= "documentos_referencia_inclusao.strarquivo = '".$cod_dvm.".".$extensao."' ";
										$usql .= "WHERE documentos_referencia_inclusao.id_documento_ref_inclusao = '".$id_documento_referencia_inc."' ";
										$usql .= "AND documentos_referencia_inclusao.reg_del = 0 ";
										
										$db->update($usql,'MYSQL');
										
										if($db->erro!='')
										{
											$erro = "Erro ao tentar atualizar os dados.".$usql;
											
											$result = 0;
										}
										else
										{					
											$result = 1;
											
										}								
									}						
								}
								else
								{
									//Se o arquivo já existir
									$erro = "O documento existe no caminho especificado.";
									
									$result = 0;
								}															
							}
						}
						else
						{
							$result = 0;	
						}
					}
				}
			}
		}
		else
		{
			if($_FILES["arquivo"]["name"]!=="")
			{
				$arquivo = TRUE;	
			}
			//editar
			//if($arquivo)
			//{
				//Seleciona os dados da versao_documento/revisao_documento atual
				$sql = "SELECT * FROM ".DATABASE.".documentos_referencia, ".DATABASE.".documentos_referencia_revisoes ";
				$sql .= "WHERE documentos_referencia.reg_del = 0 ";
				$sql .= "AND documentos_referencia_revisoes.reg_del = 0 ";
				$sql .= "AND documentos_referencia_revisoes.id_documento_referencia = '" . $_POST["id_documento_referencia"] . "' ";
				$sql .= "AND documentos_referencia.id_documento_referencia = documentos_referencia_revisoes.id_documento_referencia ";
				$sql .= "ORDER BY revisao_documento DESC LIMIT 1 ";
				
				$db->select($sql,'MYSQL',true);
	
				if($db->erro!='')
				{
					$erro = "Erro ao tentar selecionar os dados.".$sql;
				}
				else
				{			
					$reg_ver = $db->array_select[0];
					
					//seleciona Cliente/OS para composição da pasta
					$sql = "SELECT ordem_servico.id_os, ordem_servico.os, ordem_servico.descricao, empresas.abreviacao_GED FROM ".DATABASE.".ordem_servico, ".DATABASE.".empresas ";
					$sql .= "WHERE ordem_servico.id_empresa = empresas.id_empresa ";
					$sql .= "AND ordem_servico.reg_del = 0 ";
					$sql .= "AND empresas.reg_del = 0 ";
					$sql .= "AND ordem_servico.id_os = '" . $reg_ver["id_os"] . "' ";
					
					$db->select($sql,'MYSQL',true);
				
					if($db->erro!='')
					{
						$erro = "Erro ao tentar selecionar os dados.".$sql;
					} 
					else
					{
						$reg_docsref = $db->array_select[0];
				
						//seleciona a disciplina (TUB, MEC, etc)
						$sql = "SELECT setores.abreviacao FROM ".DATABASE.".setores ";
						$sql .= "WHERE setores.id_setor = '" . $reg_ver["id_disciplina"] . "' ";
						$sql .= "AND setores.reg_del = 0 ";
				
						$db->select($sql,'MYSQL',true);
						
						if($db->erro!='')
						{
							$erro = "Erro ao tentar selecionar os dados.".$sql;
						} 
						else
						{
							$reg_setor = $db->array_select[0];
							
							//seleciona o tipo de documento (CI, ATA, PROPOSTA, ETC)
							$sql = "SELECT * FROM ".DATABASE.".tipos_documentos_referencia, ".DATABASE.".tipos_referencia ";
							$sql .= "WHERE tipos_documentos_referencia.id_tipos_documentos_referencia = '" . $reg_ver["id_tipo_documento_referencia"] . "' ";
							$sql .= "AND tipos_documentos_referencia.reg_del = 0 ";
							$sql .= "AND tipos_referencia.reg_del = 0 ";
							$sql .= "AND tipos_documentos_referencia.id_tipo_referencia = tipos_referencia.id_tipo_referencia ";
					
							$db->select($sql,'MYSQL',true);
							
							if($db->erro!='')
							{
								$erro = "Erro ao tentar selecionar os dados.".$sql;
							} 
							else
							{
								$reg_tipo = $db->array_select[0];
								
								//se for uma nova versao_documento, incrementa conforme tipo(alpha/num)
								if($_POST["perm_rev"]==1)
								{
									$versao_documento = $reg_ver["versao_documento"];
									
									$versao_documento = versao_documento($versao_documento);	
								}
								else
								{
									//obtem a versao_documento
									if($_POST["versao_documento"])
									{
										$versao_documento = $_POST["versao_documento"];
									}
									else
									{					
										$versao_documento = $reg_ver["versao_documento"];						
									}				
								}					
								
								//incrementa a revisao_documento
								$revisao_documento = $reg_ver["revisao_documento"]+1;
								
								$isql = "INSERT INTO ".DATABASE.".documentos_referencia_revisoes (id_documento_referencia, texto_ci, versao_documento, revisao_documento, data_registro, data_inclusao, id_autor, id_editor, numero_grd_cliente) VALUES( ";
								$isql .= "'" . $reg_ver["id_documento_referencia"] . "', ";
								$isql .= "'" . maiusculas(addslashes($_POST["texto_ci"])) . "', ";
								$isql .= "'" . $versao_documento . "', ";
								$isql .= "'" . $revisao_documento . "', ";
								$isql .= "'" . php_mysql($_POST["data_registro"]) . "', ";
								$isql .= "'" . date("Y-m-d") . "', ";
								$isql .= "'" . $reg_ver["id_autor"] . "', ";
								$isql .= "'" . $_SESSION["id_funcionario"] . "', ";
								$isql .= "'" . $_POST["num_grd"] . "') ";
								
								$db->insert($isql,'MYSQL');
	
								if($db->erro!='')
								{
									$erro = "Erro ao tentar inserir os dados.".$isql;
								} 
								else
								{
									$id_documento_referencia_rev = $db->insert_id;
									
									//Atualiza o documento atual
									$usql = "UPDATE ".DATABASE.".documentos_referencia SET ";
									$usql .= "documentos_referencia.id_documento_referencia_revisoes = '".$id_documento_referencia_rev."', ";
									$usql .= "documentos_referencia.id_formato = '".$_POST["id_formato"]."', ";
									$usql .= "documentos_referencia.numero_documento = '".maiusculas(trim(addslashes($_POST["numero_documento"])))."', ";									
									$usql .= "documentos_referencia.titulo = '".maiusculas(trim(addslashes($_POST["titulo"])))."', ";
									$usql .= "documentos_referencia.palavras_chave = '".maiusculas(trim(addslashes($_POST["palavras_chave"])))."', ";
									$usql .= "documentos_referencia.origem = '".maiusculas(trim(addslashes($_POST["origem"])))."', ";
									$usql .= "documentos_referencia.edital = '".$_POST["chk_edital"]."', ";
									$usql .= "documentos_referencia.certificado = '".$_POST["chk_cert"]."', ";
									$usql .= "documentos_referencia.servico_id = '".$_POST["servico"]."' ";
									$usql .= "WHERE documentos_referencia.id_documento_referencia = '".$reg_ver["id_documento_referencia"]."' ";
									$usql .= "AND documentos_referencia.reg_del = 0 ";
									
									$db->update($usql,'MYSQL');
									
									if($db->erro!='')
									{
										$erro = "Erro ao tentar atualizar os dados.".$usql;
									}
									else
									{						
										$id_documento_referencia = $reg_ver["id_documento_referencia"];
									}								
								
									//se tiver arquivo
									if($arquivo)
									{									
										//verifica se grava as disciplinas nas pastas
										//ex: ATAS/MEC
										if($reg_tipo["grava_disciplina"]==1)
										{
											$disciplina = $reg_setor["abreviacao"]."/";	
										}
										else
										{
											$disciplina = "";	
										}							
								
										$array_rpl = array("/",".",":","&",")","(","{","}");
										
										$abreviacao_cliente = str_replace($array_rpl, " ",maiusculas(tiraacentos(trim($reg_docsref["abreviacao_GED"]))));		
										
										$descricao_os = str_replace($array_rpl," ",maiusculas(tiraacentos(trim($reg_docsref["descricao"]))));
										
										//monta diretorio base
										$diretorio = DOCUMENTOS_GED . $abreviacao_cliente . "/" . $reg_docsref["os"] . "-" .$descricao_os . "/" . $reg_docsref["os"] . REFERENCIAS . $reg_tipo["pasta_base"] . "/".$disciplina;
										
										$dir_erro = false;
										
										//Se ainda não existir a pasta de versões no diretório do arquivo, cria
										if(!is_dir($diretorio . "_versoes"))
										{
											//Se a criação do diretório não for feita com sucesso
											if(!mkdir($diretorio . "_versoes",0777,true))
											{
												$erro = "Erro ao criar o diretório de versões.";
											
												$dir_erro = true;
											}					
											
										}
										
										//em caso de erro na criação do diretório, aborta
										if(!$dir_erro)
										{	
											//Move o arquivo atual para a pasta _versoes, com a extensão do id documentos_referencia_revisoes
											$move_antigo = rename($diretorio.$reg_ver["arquivo"],$diretorio . "_versoes/" . $reg_ver["arquivo"] . "." . $reg_ver["id_documentos_referencia_revisoes"]);
											
											//Obtem o nome do arquivo		
											$nome_arquivo = tiraacentos(addslashes($_FILES["arquivo"]["name"]));
											
											//retira a extensão do arquivo
											$ext = explode(".",$nome_arquivo);
											
											//retira a extensão do nome do arquivo cadastrado no banco
											if($reg_ver["arquivo"]!='')
											{
												$arq_bd = explode(".",$reg_ver["arquivo"]);
											}
											else
											{
												$arq_bd[0] = $ext[0];
											}
											
											//copia o novo arquivo para o diretorio
											$move_novo = move_uploaded_file($_FILES["arquivo"]["tmp_name"], $diretorio . $arq_bd[0].".".$ext[count($ext)-1]);
										}
										
										if(!$move_antigo || !$move_novo)
										{
											$erro = "Erro no upload do arquivo.".$move_novo."#".$move_antigo;									
										}
									}
									else
									{
										//atualiza o nome arquivo (caso não possua arquivo, copia do anterior os caminhos)
										$usql = "UPDATE ".DATABASE.".documentos_referencia_revisoes SET ";
										$usql .= "documentos_referencia_revisoes.nome_arquivo = '".$reg_ver["nome_arquivo"]."', ";
										$usql .= "documentos_referencia_revisoes.arquivo = '".$reg_ver["arquivo"]."' ";
										$usql .= "WHERE documentos_referencia_revisoes.id_documentos_referencia_revisoes = '".$id_documento_referencia_rev."' ";
										$usql .= "AND documentos_referencia_revisoes.reg_del = 0 ";
																			
										$db->update($usql,'MYSQL');
										
										if($db->erro!='')
										{
											$erro = "Erro ao tentar atualizar os dados.".$usql;
										}
										else
										{
											$result = 1;	
										}
									}
		
									if(($move_antigo && $move_novo))
									{
										//atualiza o nome arquivo
										$usql = "UPDATE ".DATABASE.".documentos_referencia_revisoes SET ";
										$usql .= "documentos_referencia_revisoes.nome_arquivo = '".$nome_arquivo."', ";
										$usql .= "documentos_referencia_revisoes.arquivo = '".$arq_bd[0].".".$ext[count($ext)-1]."' ";
										$usql .= "WHERE documentos_referencia_revisoes.id_documentos_referencia_revisoes = '".$id_documento_referencia_rev."' ";
										$usql .= "AND documentos_referencia_revisoes.reg_del = 0 ";
																			
										$db->update($usql,'MYSQL');
										
										if($db->erro!='')
										{
											$erro = "Erro ao tentar atualizar os dados.".$usql;
										}
										else
										{
											$result = 1;	
										}
									}
								}							
							}
						}
					}
				}
		}
	
	}
	else
	{
		$result = 0;	
	}

	?>
	<script>
	window.top.window.stopUpload_referencias(<?= $result ?>,<?= "'".$erro."'" ?>,<?= $id_documento_referencia ?>);
	</script>
	<?php		
}
else
{
	//Passa em todos os FILES do POST do xajax.upload
	foreach($_FILES as $chave=>$valor)
	{	
		//não esta vazio o campo arquivo
		if(tiraacentos($valor["name"])!='')
		{		
			$array_nomearquivo = explode("_",$chave);
			
			$id_numero_interno = $array_nomearquivo[1];
			
			$array_numdvm[] = $id_numero_interno; //Inclue o numeros_interno no array
			
			$array_name[$id_numero_interno] = tiraacentos(addslashes($valor["name"])); //nome do arquivo (ex. "arquivo.dwg")
			
			$array_tmp_name[$id_numero_interno] = $valor["tmp_name"];
			
			$tamanho = $valor["size"];
			
			if($tamanho>1048576)
			{
				$tamanho_format = number_format(($tamanho/1024)/1024,2,".","") . " Mb";
			}
			elseif($tamanho>1024)
			{
				$tamanho_format = number_format($tamanho/1024,2,".","") . " Kb";
			}
			else
			{	
				$tamanho_format = $tamanho . " bytes";		
			}
			
			switch ($_POST["operacao"])
			{		
				
				case "0": //novos arquivos (upload)
				
					//Checa se o arquivo já está inserido no GED
					$sql = "SELECT * FROM ".DATABASE.".ged_arquivos, ".DATABASE.".numeros_interno ";
					$sql .= "WHERE ged_arquivos.id_numero_interno = numeros_interno.id_numero_interno ";
					$sql .= "AND ged_arquivos.reg_del = 0 ";
					$sql .= "AND numeros_interno.reg_del = 0 ";
					$sql .= "AND numeros_interno.id_numero_interno IN (". implode(",",$array_numdvm) .") ";
					
					$db->select($sql,'MYSQL',true);
					
					if ($db->erro != '')
					{
						$erro = 3;
					}
			
					if($db->numero_registros > 0)
					{
						$erro = 1;					
					}
					else
					{					
						//Loop em todos os NumDVM do array
						
						$sql = "SELECT empresas.id_empresa, empresas.abreviacao_GED, ordem_servico.os, ordem_servico.id_os, ordem_servico.descricao AS OS_Descricao, numeros_interno.id_numero_interno, numeros_interno.sequencia, 
							atividades.descricao AS atividades_Descricao, setores.abreviacao, setores.sigla, solicitacao_documentos_detalhes.versao_documento 
						FROM ".DATABASE.".empresas, ".DATABASE.".ordem_servico, 
							".DATABASE.".setores, ".DATABASE.".atividades, ".DATABASE.".solicitacao_documentos_detalhes, ".DATABASE.".numeros_interno 
						WHERE numeros_interno.id_os = ordem_servico.id_os 
						AND numeros_interno.reg_del = 0 
						AND solicitacao_documentos_detalhes.reg_del = 0  
						AND empresas.reg_del = 0
						AND ordem_servico.reg_del = 0
						AND atividades.reg_del = 0
						AND numeros_interno.id_disciplina = setores.id_setor 
						AND numeros_interno.id_atividade = atividades.id_atividade 
						AND ordem_servico.id_empresa = empresas.id_empresa 
						AND numeros_interno.id_numero_interno IN (" . implode(",",$array_numdvm) . ") 
						AND numeros_interno.id_numero_interno = solicitacao_documentos_detalhes.id_numero_interno ";
	
						$db->select($sql,'MYSQL',true);
						
						if ($db->erro != '')
						{
							$erro = 3;
						}
					
						foreach($db->array_select as $reg_numdvm)
						{	
							//1 - Cria os diretórios e sub-diretórios			
							$array_rpl = array("/",".",":","&");
							
							$str_nome_documento = str_replace($array_rpl, " ",maiusculas(tiraacentos($reg_numdvm["atividades_Descricao"])));
							
							$abreviacao_cliente = str_replace($array_rpl, " ",maiusculas(tiraacentos(trim($reg_numdvm["abreviacao_GED"]))));
							
							$descricao_os = str_replace($array_rpl," ",maiusculas(tiraacentos(trim($reg_numdvm["OS_Descricao"]))));
			
							$cria_dir = true;
	
							//Se não existir a subpasta Nr do documento
							if(!is_dir(DOCUMENTOS_GED . $abreviacao_cliente . "/" . $reg_numdvm["os"] . "-" .$descricao_os . "/" . $reg_numdvm["os"] . DISCIPLINAS . $reg_numdvm["os"] . "-" . maiusculas(tiraacentos($reg_numdvm["abreviacao"])) . "/" . $str_nome_documento . "/" . $reg_numdvm["os"] . "-" . $reg_numdvm["sequencia"]))
							{
								if(!mkdir(DOCUMENTOS_GED . $abreviacao_cliente . "/" . $reg_numdvm["os"] . "-" .$descricao_os . "/" . $reg_numdvm["os"] . DISCIPLINAS . $reg_numdvm["os"] . "-" . maiusculas(tiraacentos($reg_numdvm["abreviacao"])) . "/" . $str_nome_documento . "/" . $reg_numdvm["os"] . "-" . $reg_numdvm["sequencia"],0777,true))
								{									
									$erro = 4;
									
									$cria_dir = false;
									
									$msg = DOCUMENTOS_GED . $abreviacao_cliente . "/" . $reg_numdvm["os"] . "-" .$descricao_os . "/" . $reg_numdvm["os"] . DISCIPLINAS . $reg_numdvm["os"] . "-" . maiusculas(tiraacentos($reg_numdvm["abreviacao"])) . "/" . $str_nome_documento . "/" . $reg_numdvm["os"] . "-" . $reg_numdvm["sequencia"];
								}
							
							}
							//Se o diretório existir e/ou foi criado com sucesso
							if($cria_dir)		
							{
								$str_caminho_arq = DOCUMENTOS_GED . $abreviacao_cliente . "/" . $reg_numdvm["os"] . "-" .$descricao_os . "/" . $reg_numdvm["os"] . DISCIPLINAS . $reg_numdvm["os"] . "-" . maiusculas(tiraacentos($reg_numdvm["abreviacao"])) . "/" . $str_nome_documento . "/" . $reg_numdvm["os"] . "-" . $reg_numdvm["sequencia"];		
					
								//2 - Verifica e move/copia o arquivo que veio via POST
								
								//atribue o nome do arquivo
								$nome_arquivo = $array_name[$reg_numdvm["id_numero_interno"]];
						
								//Checa se o nome do arquivo está formado corretamente (com extensão, etc)
								if(substr_count($nome_arquivo,".")==0)
								{
									$erro = 6;
								}
								else
								{
									$nome_extensao_format = substr($nome_arquivo,strrpos($nome_arquivo,"_")+1,strrpos($nome_arquivo,".")-strrpos($nome_arquivo,"_")-1);
							
									//Reseta a versão
									//Modificado em 25/10/2010
									//pega a versao_documento do solicitacao_documentos_detalhes							
									if($reg_numdvm["versao_documento"]=="")
									{
										$versao_inicial = 0;
									}
									else
									{
										$versao_inicial = $reg_numdvm["versao_documento"];
									}
									
									//Pega o nome do arquivo completo
									$nome_arquivo_sem_versao = $array_name[$reg_numdvm["id_numero_interno"]];				
						
									//Checa se o arquivo já existe e move o arquivo para o lugar correto
									if(!is_file($str_caminho_arq . "/" . $nome_arquivo_sem_versao)) 
									{						
										//Move o arquivo
										$move_arquivo = move_uploaded_file($array_tmp_name[$reg_numdvm["id_numero_interno"]],$str_caminho_arq . "/" . $nome_arquivo_sem_versao);
																
										//3 - Insere as informações do arquivo no banco de dados - GED
										
										$descricao = PREFIXO_DOC_GED . sprintf("%05d",$reg_numdvm["os"]) . "-" . $reg_numdvm["sigla"] . "-" .$reg_numdvm["sequencia"];
																
										//Se arquivo foi movido com sucesso, insere no banco
										if($move_arquivo)
										{				
											//Insere o arquivo no banco
											$isql = "INSERT INTO ".DATABASE.".ged_arquivos (id_numero_interno, descricao, id_autor) VALUES(";
											$isql .= "'" . $reg_numdvm["id_numero_interno"] . "', ";
											$isql .= "'" . $descricao . "', ";
											$isql .= "'" . $_SESSION["id_funcionario"] . "') ";									
										
											$db->insert($isql,'MYSQL');
											
											if ($db->erro != '')
											{
												$erro = 3;
												
												$cont_insere_arquivo = 0;
											}
											else
											{
												$cont_insere_arquivo = 1;	
											}
											
											//Pega o id do arquivo inserido
											$id_ged_arquivo = $db->insert_id;									
			
											//Insere a versão/revisão inicial "0.0"
											$isql = "INSERT INTO ".DATABASE.".ged_versoes (id_ged_arquivo, id_autor, arquivo, base, os, disciplina, atividade, strarquivo, sequencial, nome_arquivo, revisao_interna, revisao_cliente, versao_, versao_original) VALUES(";
											$isql .= "'" . $id_ged_arquivo . "', ";
											$isql .= "'" . $_SESSION["id_funcionario"] . "', ";
											$isql .= "'" . $str_caminho_arq . "/" . $nome_arquivo_sem_versao . "', ";
											$isql .= "'" . $abreviacao_cliente . "', ";//base
											$isql .= "'" . $reg_numdvm["os"] . "-" .$descricao_os . "', ";//os
											$isql .= "'" . $reg_numdvm["os"] . "-" . maiusculas(tiraacentos($reg_numdvm["abreviacao"])) . "', ";//disciplina
											$isql .= "'" . $str_nome_documento . "', ";//atividade
											$isql .= "'" . $reg_numdvm["os"] . "-" . $reg_numdvm["sequencia"] . "/" . $nome_arquivo_sem_versao . "', ";//strarquivo
											$isql .= "'" . $reg_numdvm["os"] . "-" . $reg_numdvm["sequencia"] ."', ";//sequencial
											$isql .= "'" . $nome_arquivo_sem_versao ."', ";//nome_arquivo
											$isql .= "'" . $versao_inicial . "', ";
											$isql .= "'" . $versao_inicial . "', ";
											$isql .= "'0', ";
											$isql .= "'1') ";
											
											$db->insert($isql,'MYSQL');									
											
											if ($db->erro != '')
											{
												$erro = 3;
												
												$cont_insere_versao = 0;
											}
											else
											{
												$cont_insere_versao = 1;	
											}
								
											//Pega o id da versão inserida
											$id_ged_versao = $db->insert_id;
								
											//Atualiza o registro do arquivo inserido, definindo a versão atual do arquivo como a versão inserida acima
											$usql = "UPDATE ".DATABASE.".ged_arquivos SET ";
											$usql .= "id_ged_versao = '" . $id_ged_versao . "' ";
											$usql .= "WHERE ged_arquivos.id_ged_arquivo = '" . $id_ged_arquivo . "' ";
											$usql .= "AND ged_arquivos.reg_del = 0 ";
											
											$db->update($usql,'MYSQL');
											
											$cont_atualiza_arquivo = 1;
											 
											if ($db->erro != '')
											{
												$erro = 3;
												
												$cont_atualiza_arquivo = 0;											
											}
											else
											{
												$cont_atualiza_arquivo = 1;
											}
									
											$str_caminho_seleciona = $str_caminho_arq;										
											
										}
										else
										{
											$erro = 7;
										}	
									}
									else
									{
										//Se o arquivo já existir
										$erro = 1;
									}
								}
							}
							else
							{
								$erro = 5;
							}
						}
						
						if($cont_insere_arquivo && $cont_insere_versao && $cont_atualiza_arquivo)
						{
							//Sucesso, informa o usuário
							$result = 1;
							
							$id_numero_interno = $reg_numdvm["id_numero_interno"];
							
							$result = 1;

						}
						else
						{
							$result = 0;						
						}
					}
					
				break;
				
				case "1": //checkout unico arquivo
				case "2": //chechout multiplos arquivos
				
					//Loop em todos os NumDVM do array 
					$sql = "SELECT ged_arquivos.id_autor, ged_arquivos.id_ged_arquivo, ged_versoes.base, ged_versoes.os, ged_versoes.disciplina, ged_versoes.atividade, ged_versoes.strarquivo, ged_versoes.sequencial, ged_versoes.nome_arquivo, numeros_interno.id_numero_interno, numeros_interno.id_disciplina AS numdvm_id_disciplina 
							FROM ".DATABASE.".numeros_interno, ".DATABASE.".ged_arquivos, ".DATABASE.".ged_versoes ";
					$sql .= "WHERE numeros_interno.id_numero_interno = ged_arquivos.id_numero_interno ";
					$sql .= "AND numeros_interno.reg_del = 0 ";
					$sql .= "AND ged_arquivos.reg_del = 0 ";
					$sql .= "AND ged_versoes.reg_del = 0 ";
					$sql .= "AND ged_arquivos.id_ged_arquivo = ged_versoes.id_ged_arquivo ";
					$sql .= "AND ged_arquivos.id_ged_versao = ged_versoes.id_ged_versao ";
					$sql .= "AND numeros_interno.id_numero_interno IN (" . implode(",",$array_numdvm) . ") ";
								
					$db->select($sql,'MYSQL',true);
					
					if ($db->erro != '')
					{
						$erro = 3;
					}
					
					$array_ndvm = $db->array_select;
				
					foreach($array_ndvm as $reg_numdvm)
					{
						//Checa se o nome do arquivo esta formado corretamente (com extensão, etc)
						if(substr_count($array_name[$reg_numdvm["id_numero_interno"]],".")==0)
						{
							$erro = 6;					
						}
						else
						{
							//Pega o nome do arquivo
							$caminho = DOCUMENTOS_GED . $reg_numdvm["base"] . "/" . $reg_numdvm["os"] . "/" . substr($reg_numdvm["os"],0,4) . DISCIPLINAS . $reg_numdvm["disciplina"] . "/" . $reg_numdvm["atividade"] . "/" . $reg_numdvm["sequencial"];
							
							$cria_dir = true;
							
							//Se ainda não existir a pasta de versões no diretório do arquivo, cria
							if(!is_dir($caminho . DIRETORIO_VERSOES))
							{
								//Se a criação do diretório não for feita com sucesso
								if(!mkdir($caminho . DIRETORIO_VERSOES))
								{								
									$erro = 4; 
									
									$cria_dir = false;
									
									$msg .= $caminho . DIRETORIO_VERSOES;					
								}	
							}
							
							//O diretorio foi criado e/ou existe com sucesso
							if($cria_dir)
							{
								//Verifica a última versão do arquivo
								//Alterado por carlos abreu em 25/11/2010
								$sql = "SELECT id_ged_versao, ged_versoes.base, ged_versoes.os, ged_versoes.disciplina, ged_versoes.atividade, ged_versoes.strarquivo, ged_versoes.sequencial, ged_versoes.nome_arquivo, versao_, revisao_interna, revisao_cliente, arquivo, retorno FROM ".DATABASE.".ged_versoes ";
								$sql .= "WHERE ged_versoes.id_ged_arquivo = '" . $reg_numdvm["id_ged_arquivo"] . "' ";
								$sql .= "AND ged_versoes.reg_del = 0 ";
								$sql .= "ORDER BY id_ged_versao DESC LIMIT 1 ";
								
								$db->select($sql,'MYSQL',true);
								
								if ($db->erro != '')
								{
									$erro = 3;
								}
						
								$reg_versao = $db->array_select[0];
											
								//Se a seleção dos dados de versão ocorreu com sucesso
								if($db->erro == '')
								{				
									$caminho_antigo = DOCUMENTOS_GED . $reg_versao["base"] . "/" . $reg_versao["os"] . "/" . substr($reg_versao["os"],0,4) . DISCIPLINAS . $reg_versao["disciplina"] . "/" . $reg_versao["atividade"] . "/" . $reg_versao["sequencial"];
								
									//Rotina para incremento de versão
									$nova_versao = $reg_versao["versao_"]+1; //revisao_documento								 
					
									$nova_revisao_dvm = $reg_versao["revisao_interna"]; //versao_documento dvm
									
									$nova_revisao_cliente = $reg_versao["revisao_cliente"]; //versao_documento cliente
									
									//salva o conteudo do retorno para manter a referencia do ultimo retorno
									$retorno[$reg_numdvm["id_ged_arquivo"]] = $reg_versao["retorno"];
									
									//Alterado por carlos abreu em 14/10/2010
									//Se o campo ged_versoes.retorno tiver 2
									//aumenta a revisão e volta o status do retorno para 0
									if($retorno[$reg_numdvm["id_ged_arquivo"]]==2)
									{
										//RETIRADO O INCREMENTO AUTOMATICO
										//CONFORME ACORDADO COM FERNANDO
										//27/10/2010
										//PASSANDO A UTILIZAR ALFANUMERICO
										//FLYSPRAY #61
					
										//acrescentado por carlos abreu 24/05/2010
										//0 - volta status atual - não altera versao_documento
										$usql = "UPDATE ".DATABASE.".ged_versoes SET ";
										$usql .= "retorno = '0' ";
										$usql .= "WHERE ged_versoes.id_ged_versao = '".$reg_versao["id_ged_versao"]."' ";
										$usql .= "AND ged_versoes.reg_del = 0 ";
										
										$db->update($usql,'MYSQL');
										
										if ($db->erro != '')
										{
											$erro = 3;
										}
	
										$retorno[$reg_numdvm["id_ged_arquivo"]] = 0;								
									}
									
									//Move o arquivo atual para a pasta _versoes, com a extensão da versão
									$move_antigo = rename($caminho_antigo."/".$reg_numdvm["nome_arquivo"],$caminho_antigo . DIRETORIO_VERSOES ."/". $reg_numdvm["nome_arquivo"] . "." . $reg_versao["id_ged_versao"]);
						
									$nome_arquivo_novo = $array_name[$reg_numdvm["id_numero_interno"]];
									
									$move_novo = move_uploaded_file($array_tmp_name[$reg_numdvm["id_numero_interno"]], $caminho . "/" . $nome_arquivo_novo);
					
									//Se os arquivos forem movidos com sucesso
									if($move_antigo && $move_novo)
									{					
										//Insere a nova versão
										$isql = "INSERT INTO ".DATABASE.".ged_versoes (id_ged_arquivo, id_autor, arquivo, base, os, disciplina, atividade, strarquivo, sequencial, nome_arquivo, versao_, revisao_interna, revisao_cliente, retorno) VALUES(";
										$isql .= "'" . $reg_numdvm["id_ged_arquivo"] . "', ";									
										$isql .= "'" . $reg_numdvm["id_autor"] . "', ";									
										$isql .= "'" . $caminho . "/" . $nome_arquivo_novo . "', ";
										$isql .= "'" . $reg_numdvm["base"] . "', ";
										$isql .= "'" . $reg_numdvm["os"] . "', ";
										$isql .= "'" . $reg_numdvm["disciplina"] . "', ";
										$isql .= "'" . $reg_numdvm["atividade"] . "', ";
										$isql .= "'" . $reg_numdvm["sequencial"] . "/" . $nome_arquivo_novo . "', ";
										$isql .= "'" . $reg_numdvm["sequencial"] . "', ";
										$isql .= "'" . $nome_arquivo_novo . "', "; 
										$isql .= "'" . $nova_versao . "', ";
										$isql .= "'" . $nova_revisao_dvm . "', ";
										$isql .= "'" . $nova_revisao_cliente . "', ";
										$isql .= "'" . $retorno[$reg_numdvm["id_ged_arquivo"]] . "' ) ";
										
										$db->insert($isql,'MYSQL');
										
										$cont_nova_versao = $db->insert_id;
										
										if ($db->erro != '')
										{
											$erro = 3;
										}
									
										$id_ged_versao = $db->insert_id;
	
										//Atualiza o status do arquivo para 0 - NORMAL
										$usql = "UPDATE ".DATABASE.".ged_arquivos SET ";
										$usql .= "status = '0', ";
										$usql .= "id_ged_versao = '" . $id_ged_versao . "', ";
										$usql .= "id_editor = '" . $_SESSION["id_funcionario"] . "' ";
										$usql .= "WHERE ged_arquivos.id_ged_arquivo = '" . $reg_numdvm["id_ged_arquivo"] . "' ";
										$usql .= "AND ged_arquivos.reg_del = 0 ";
						
										$db->update($usql,'MYSQL');
										
										if ($db->erro != '')
										{
											$erro = 3;
										}
										else
										{
											$cont_atualiza_arquivo = 1;
										}
									
										solicitacoes($reg_numdvm["id_os"],$reg_numdvm["id_ged_arquivo"],1,2);
										
										solicitacoes($reg_numdvm["id_os"],$reg_numdvm["id_ged_arquivo"],2,2);
										
										solicitacoes($reg_numdvm["id_os"],$reg_numdvm["id_ged_arquivo"],3,2);
									
										$str_caminho_seleciona = $caminho;										
	
										$erro = 0;
										
										$result = 1;
	
									}
									else
									{
										//se der erro no arquivo novo (upload), retorna o anterior
										if(!$move_novo)
										{
											$move_anterior = rename($caminho . DIRETORIO_VERSOES. "/" . $reg_versao["nome_arquivo"] . "." . $reg_versao["id_ged_versao"],$caminho_antigo."/".$reg_versao["nome_arquivo"]);
										}
										else
										{
											$usql = "UPDATE ".DATABASE.".ged_versoes SET ";
											$usql .= "ged_versoes.reg_del = 1, ";
											$usql .= "ged_versoes.reg_who = '" . $_SESSION['id_usuario'] ."', ";
											$usql .= "ged_versoes.data_del = '".date('Y-m-d')."' ";
											$usql .= "WHERE	ged_versoes.id_ged_versao = '".$id_ged_versao."' ";
											$usql .= "AND ged_versoes.reg_del = 0 ";								
											
											$db->update($usql,'MYSQL');
											
											if ($db->erro != '')
											{
												$erro = 3;
											}
												
											$erro = 7;
										}
									}
									
								}
							}
							else
							{
								$msg .= "criar arquivo revisao_documento";	
							}
						}
					}
					
				break;		
			}
		}
		else
		{
			$result = 0;
			
			$id_numero_interno = $_POST["id_num_dvm"];
			
			$tamanho_format = '';
			
			$erro = 0;
			
			$msg .= "sem arquivos";					
		}
		
		sleep(1);
		
		?>
		<script>
		window.top.window.stopUpload(<?= $result ?>,<?= $id_numero_interno ?>,<?= "'". tiraacentos($valor["name"]) ."'" ?>,<?= "'". $tamanho_format ."'" ?>,<?= $erro ?>,<?= "'".$msg."'" ?>);
		</script>
		<?php	
	}
}
?>