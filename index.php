<?php
/*
		Formulário de Autenticação	
		
		Criado por Carlos Abreu 
		
		local/Nome do arquivo:
		index.php
		
		Versão 0 --> VERSÃO INICIAL - 08/02/2007
		Versão 1 --> Troca de lay-out / smarty / classes - 23/03/2009
		Versão 2 --> Troca de lay-out / classe banco - 24/07/2012
		Versão 3 --> Troca de imagens e diretorio - 07/07/2016 - Carlos Abreu
		Versão 4 --> Atualização lay-out, melhorias - 16/03/2017 - Carlos Abreu
		Versão 5 --> Inclusão dos campos reg_del nas consultas - 13/11/2017 - Carlos Abreu 		
*/

$userdvm = "";

//seta idioma se não estiver setado
if (!isset($_COOKIE['idioma'])) 
{
   $_COOKIE["idioma"]="1";
   
   setcookie("idioma",1,time()+60*60*24*180);
}

if (isset($_COOKIE['userdvm'])) 
{
   $userdvm = $_COOKIE['userdvm'];
}

require_once("config.inc.php"); //OK

require_once(INCLUDE_DIR."include_form.inc.php"); //OK

require_once(INCLUDE_DIR."encryption.inc.php"); //OK

function autenticacao($dados_form)
{
	if(isset($_SESSION["id_sub_modulo"]))
	{
		unset($_SESSION["id_sub_modulo"]);
	}

	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$enc = new Crypter(CHAVE);
		
	$msg = $conf->msg($resposta);
	
	$db = new banco_dados;
	
	//senha do administrador
	$pass_adm = $enc->encrypt('admin');

	// Recupera o login
	$login = isset($dados_form["login"]) ? addslashes(trim($dados_form["login"])) : FALSE;
	
	// Recupera a senha, a criptografando em MD5
	$senha = isset($dados_form["senha"]) ? $dados_form["senha"] : FALSE;
	
	// Usuário não forneceu a senha ou o login
	if(!$login || !$senha)
	{
		$resposta->addAssign("mensagem","innerHTML",$msg[10]);
	}
	else
	{
		//verifica se administrador
		if(($login == 'administrador' || $login == 'admin') && !strcmp($senha, $enc->decrypt($pass_adm)))
		{			
			$_SESSION["admin"] = TRUE;
			
			$_SESSION["login"] = $login;
			
			$_SESSION["nome_usuario"] = stripslashes("ADMINISTRADOR DO SISTEMA");			
			
			$_SESSION["id_usuario"] = 0;
			
			if($dados_form["pagina"]!="")
			{
				$resposta->addRedirect($dados_form["pagina"]);
			}
			else
			{
				$resposta->addRedirect("inicio.php");
			}		
		}
		else
		{
			$_SESSION["admin"] = FALSE;
			
			/**
			* Executa a consulta no banco de dados.
			* Caso o número de linhas retornadas seja 1 o login é válido,
			* caso 0, inválido.
			*/
			/*
			$sql = "SELECT * FROM ".DATABASE.".usuarios, ".DATABASE.".funcionarios ";
			$sql .= "WHERE usuarios.login = '" . $login . "' ";
			$sql .= "AND usuarios.reg_del = 0 ";
			$sql .= "AND funcionarios.reg_del = 0 ";
			$sql .= "AND usuarios.Codfuncionario = funcionarios.id_funcionario ";
			*/

			$sql = "SELECT * FROM ".DATABASE.".usuarios ";
			$sql .= "LEFT JOIN ".DATABASE.".funcionarios ON (usuarios.id_usuario = funcionarios.id_usuario AND funcionarios.reg_del = 0 ) ";
			$sql .= "WHERE usuarios.login = '" . $login . "' ";
			$sql .= "AND usuarios.reg_del = 0 ";

			$db->select($sql,'MYSQL',true);

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}
			
			$dados = $db->array_select[0];
			
			$data_referencia = date("d/m/Y");
			
			$data_troca = mysql_php($dados["data_troca"]);
			
			$dias_restantes = (DIAS_LIMITE) - dif_datas($data_referencia,$data_troca);
	
			if(!in_array(str_replace(" ","",$dados["situacao"]),array('ATIVO')))
			{
				$resposta->addAssign("mensagem","innerHTML", $msg[11]);
			}
			else
			{
				// Caso o usuário tenha digitado um login válido o número de linhas será 1..
				if($db->numero_registros>=1)
				{	
					// Obtém os dados do usuário, para poder verificar a senha e passar os demais dados para a sessão									
					// Agora verifica a senha
					if(!strcmp($senha, $enc->decrypt($dados["senha"])))
					{
						if($dados['perfil']==1)
						{
							$_SESSION["admin"] = TRUE;
						}
						
						// TUDO OK! Agora, passa os dados para a sessão e redireciona o usuário
						$_SESSION["login"] = $dados["login"];
						
						$_SESSION["nivel_atuacao"] = $dados["nivel_atuacao"];
						
						$_SESSION["id_usuario"] = $dados["id_usuario"];
						
						$_SESSION["id_funcionario"] = $dados["id_funcionario"];
						
						$_SESSION["perfil"] = $dados["perfil"];
						
						$_SESSION["id_setor_aso"] = $dados["id_setor_aso"];
						
						$_SESSION["nome_usuario"] = stripslashes($dados["funcionario"]);
						
						if($dias_restantes <= 0)
						{
							$resposta->addScript("troca_senha('".$dados["login"]."','".$dados["id_usuario"]."');");
							
							return $resposta;	
						}
						
						//se faltar 10 dias para o vencimento, mostra mensagem
						if($dias_restantes<=10)
						{
							$resposta->addAlert("Sua senha irá expirar em ".abs($dias_restantes)." dias.");		
						}						
						
						if($dados["status"]=="0")
						{
							if($dados_form["pagina"]!="")
							{
								$resposta->addRedirect($dados_form["pagina"]);
							}
							else
							{
								$resposta->addRedirect("inicio.php");
							}							
						}
						else
						{
							$resposta->addScript("troca_senha('".$dados["login"]."','".$dados["id_usuario"]."');");
						}						
					}
					// Senha inválida
					else
					{
						$resposta->addAssign("mensagem","innerHTML",$msg[12]);
						
						return $resposta;
					}
				}
				// Login inválido
				else
				{
					$resposta->addAssign("mensagem","innerHTML",$msg[13]);
				}
			}			
		}
	}
	
	return $resposta;
}

function enviar($dados_form)
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$msg = $conf->msg($_COOKIE["idioma"],$resposta);
	
	if($dados_form["nome"]!="" && $dados_form["email"]!="" && $dados_form["senha"]!="")
	{	
		$db = new banco_dados;
	
		$enc = new Crypter(CHAVE);
		
		//$cpf = str_replace(array('.','-'),'',$dados_form["nome"]);
		
		//verifica se numerico, case seja informou cpf
		///if(is_numeric($cpf))
		//{
			//$cpf = aplica_mascara($cpf,'cpf');
		//}
	
		$sql = "SELECT id_usuario, login, email FROM ".DATABASE.".usuarios ";
		$sql .= "WHERE usuarios.reg_del = 0 ";
		$sql .= "AND usuarios.login = '". minusculas(trim($dados_form["nome"])). "' ";
		$sql .= "AND usuarios.email = '".minusculas(trim($dados_form["email"]))."') ";		

		$db->select($sql,'MYSQL',true);

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
		
		$reg = $db->array_select[0];

		// Se o número de registros for maior que zero, então existe o registro...
		if ($db->numero_registros>0)
		{			
			$senha = $enc->encrypt(trim($dados_form["senha"]));
								
			$usql = "UPDATE ".DATABASE.".usuarios SET ";
			$usql .="senha = '". $senha . "', ";
			$usql .="status = '0', ";
			$usql .= "data_troca = '".date("Y-m-d")."' ";
			$usql .="WHERE id_usuario = '".$reg["id_usuario"]."' ";
			$usql .= "AND reg_del = 0 ";

			$db->update($usql,'MYSQL');

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}
			
			$mensagem = "Seus dados para acesso são:<br><br>\n\n";
			$mensagem .= "login: " . $reg["login"] . "<br>\n";
			$mensagem .= "Senha: " . $senha . "<br><br>\n\n";
			$mensagem .= "Tecnologia da Informação <br><br>\n\n";
			$mensagem .= "Caso tenha recebido este e-mail sem sua solicitação, favor desconsiderá-lo. <br><br>\n\n";
			$mensagem .= "O envio desta confirmação foi registrado em nosso banco de dados em ". date("d/m/Y") . " as " . date("H:i") . " <br><br><br>\n\n\n";
			$mensagem .= "E-mail enviado em ". date("d/m/Y") . " as " . date("H:i") . " <br>\n"; 

			$params = array();
			
			$params['from']	= "empresa@dominio.com.br";
			
			$params['from_name'] = "RECUPERAÇÃO DE SENHA - EMPRESA X";
			
			$params['subject'] = "RECUPERAÇÃO DE SENHA";
			
			$params['emails']['to'][] = array('email' => $reg["email"], 'nome' => $reg["login"]);
			
			$mail = new email($params);
			
			$mail->montaCorpoEmail($mensagem);
	
			if(!$mail->Send())
			{
				$resposta->addAlert($msg[21].$mail->ErrorInfo);
			}
			else
			{
				$resposta->addAlert($msg[22]);
			}
			
			$mail->ClearAddresses();
			
		}
		else
		{	
			$resposta->addAlert($msg[19]);		
		}
	}
	else
	{
		$resposta->addAlert($msg[4]);
	}

	return $resposta;
}

function validar_senha($dados_form)
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$msg = $conf->msg($_COOKIE['idioma'],$resposta);	

	if(($dados_form["senha"]!=$dados_form["confsenha"]) || ($dados_form["senha"]==''))
	{
		$resposta->addAssign("mensagem","innerHTML",$msg[17]);
		$resposta->addAssign("senha","value","");
		$resposta->addAssign("confsenha","value","");
		$resposta->addScript('document.getElementsByName("senha")[0].focus();');
	}

	return $resposta;
}

function atualiza($dados_form)
{
	$resposta = new xajaxResponse();

	$conf = new configs();
	
	$msg = $conf->msg($_COOKIE['idioma'],$resposta);
	
	$db = new banco_dados;
	
	if($dados_form["senha"]=="")
	{
		$resposta->addAlert($msg[18]);
	}
	else
	{
		$sql = "SELECT * FROM ".DEVEMADA.".usuarios ";
		$sql .="WHERE id_usuario = '".$dados_form["id_usuario"]."' ";
		$sql .= "AND reg_del = 0 ";

		$db->select($sql,'MYSQL',true);

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
		
		if($db->numero_registros>0)
		{
			$enc = new Crypter(CHAVE);
			
			$regs = $db->array_select[0];
						
			$confsenha = $enc->decrypt($regs["senha"]);
			
			if(trim($dados_form["senha"])=="12345")
			{
				$resposta->addAlert("Esta senha não pode ser utilizada.");
			}
			else
			{			
				if($confsenha == trim($dados_form["senha"]))		
				{
					$resposta->addAlert("As senhas devem ser diferentes.");
				}
				else
				{					
					$test = password_check_complex($dados_form["senha"]);
					
					if(!$test)
					{
						$resposta->addAlert('Senha dever ter no mínimo:'.chr(13).TAMANHO_SENHA.' caracteres;'.chr(13).'1 caracter maiúsculo;'.chr(13).'1 caracter minúsculo;'.chr(13).'1 número;'.chr(13).'1 símbolo ex: (!@#$%)');
						$resposta->addAssign("senha","value","");
						$resposta->addAssign("confsenha","value","");
						$resposta->addScript('document.getElementsByName("senha")[0].focus();');
					}
					else
					{					
						$senha = $enc->encrypt(trim($dados_form["senha"]));
								
						$usql = "UPDATE ".DATABASE.".usuarios SET ";
						$usql .= "senha = '". $senha . "', ";
						$usql .= "status = '0', ";
						$usql .= "data_troca = '".date("Y-m-d")."' ";
						$usql .= "WHERE id_usuario = '".$_SESSION["id_usuario"]."' ";
						$usql .= "AND reg_del = 0 ";

						$db->update($usql,'MYSQL');

						if($db->erro!='')
						{
							$resposta->addAlert($db->erro);
						}
						
						$resposta->addAlert($msg[20]);
					}
				}
			}		
		}
		else
		{
			$resposta->addAlert($msg[19]);
		}
		
		$resposta->addScript('window.close();');		
	}
	
	return $resposta;
}

$xajax->registerFunction("autenticacao");
$xajax->registerFunction("enviar");
$xajax->registerFunction("validar_senha");
$xajax->registerFunction("atualiza");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

?>
<script src="<?php echo ROOT_WEB.'/includes/' ?>validacao.js"></script>

<script src="<?php echo ROOT_WEB.'/includes/' ?>utils.js"></script>

<script>

function limpa_div(div)
{
	div = document.getElementById(div);
	div.innerHTML = '';
}

function esqueceusenha()
{
	diretorio_imagens = '<?php echo DIR_IMAGENS ?>';

	conteudo = '<form name="frm_pass" id="frm_pass" method="POST">';		  
	conteudo += '<label for="nome" class="labels">Usuário</label><br />';
	conteudo += '<input name="nome" id="nome" type="text" placeholder="Usuário" class="caixa" style="text-transform:none;" value="" size="50"/><br />';
	conteudo += '<label for="email" class="labels">E-mail</label><br />';
	conteudo += '<input name="email" id="email" type="text" placeholder="email" class="caixa" style="text-transform:none;" value="" size="50"/><br />';
	conteudo += '<label for="senha" class="labels">Nova&nbsp;senha</label><br />';
	conteudo += '<input name="email" id="senha" type="password" placeholder="Senha" class="caixa" style="text-transform:none;" value="" size="50"/><br />';
	conteudo += '<input name="button" type="button" class="class_botao" onclick=xajax_enviar(xajax.getFormValues("frm_pass")); value="Enviar" />';
	conteudo += '</form>';
	
	modal(conteudo, 'p', 'ESQUECI MINHA SENHA',1);
	
	return true;
}

function troca_senha(login,id_usuario)
{
	diretorio_imagens = '<?php echo DIR_IMAGENS ?>';
	
	conteudo = '<form name="frm_pass" id="frm_pass" method="POST">';
	conteudo += '<label for="login" class="labels">login</label><br />';
    conteudo += '<input name="login" id="login" type="text" class="caixa" readonly="readonly" value="'+login+'" size="50"/><br /> ';
    conteudo += '<input name="id_usuario" id="id_usuario" type="hidden"  value="'+id_usuario+'"/>';
	conteudo += '<label for="senha" class="labels">Senha</label><br />';
    conteudo += '<input name="senha" type="password" placeholder="Senha" class="caixa" style="text-transform:none;" id="senha" onKeyPress=limpa_div("mensagem"); size="30" /><br >';
	conteudo += '<label for="confsenha" class="labels">Confirme&nbsp;a&nbsp;senha</label><br />';
    conteudo += '<input name="confsenha" type="password" placeholder="Confime a senha" class="caixa" style="text-transform:none;" id="confsenha" size="30" onblur=xajax_validar_senha(xajax.getFormValues("frm_pass")); /><br />';
	conteudo += '<div class="alerta_erro" id="mensagem">&nbsp;</div><br />';
	conteudo += '<input name="button" type="button" class="class_botao" onclick=xajax_atualiza(xajax.getFormValues("frm_pass")); value="Alterar" />';
	conteudo += '</form>';

	modal(conteudo, 'p', 'TROCAR SENHA',1);
	
	return true;	
}

</script>

<?php

$conf = new configs();

$smarty->assign("revisao_documento","V5");

$smarty->assign("campo",$conf->campos('login'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("pagina",isset($_GET["pagina"]) ? $_GET["pagina"] : null);

$smarty->assign("userdvm",$userdvm);

$smarty->assign("classe",CSS_FILE);

$smarty->display("index.tpl");
?>