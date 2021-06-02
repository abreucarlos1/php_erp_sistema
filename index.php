<?php
/*
		Formulário de Autenticação	
		
		Criado por Carlos Abreu - 20/05/2021
		
		local/Nome do arquivo:
		index.php
	
*/

$user = "";

//seta idioma se não estiver setado
if (!isset($_COOKIE['idioma'])) 
{
   $_COOKIE["idioma"]="1";
   
   setcookie("idioma",1,time()+60*60*24*180);
}

if (isset($_COOKIE['user'])) 
{
   $user = $_COOKIE['user'];
}

require_once("config.inc.php"); //OK

require_once(INCLUDE_DIR."include_form.inc.php"); //OK

//require_once(INCLUDE_DIR."encryption.inc.php"); //OK

function autenticacao($dados_form)
{
	//session_start();

	$resposta = new xajaxResponse();

	if(isset($_SESSION["id_sub_modulo"]))
	{
		unset($_SESSION["id_sub_modulo"]);
	}

	$conf = new configs();
	
	//$enc = new Crypter(CHAVE);
		
	$msg = $conf->msg($resposta);
	
	$db = new banco_dados;
	
	//senha do administrador
	//$pass_adm = $enc->encrypt('admin');

	$pass_adm = gerar_hash('admin', 'admin'); //senha/nome

	$pass_administrador = gerar_hash('admin', 'administrador');

	// Recupera o login
	$login = isset($dados_form["login"]) ? addslashes(trim($dados_form["login"])) : FALSE;
	
	// Recupera a senha, a criptografando em MD5
	$senha = isset($dados_form["senha"]) ? $dados_form["senha"] : FALSE;
	
	// Usuário não forneceu a senha ou o login
	if(!$login || !$senha)
	{
		$resposta->addAssign("mensagem","innerHTML","Os campos não podem estar vazios.");
	}
	else
	{
		//verifica se administrador
		if(($login == 'administrador' || $login == 'admin'))
		{
			// Agora verifica a senha
			/*
			if(strcmp($senha, $enc->decrypt($pass_adm)))
			{
				$resposta->addAssign("mensagem","innerHTML",$msg[12]);
						
				return $resposta;
			}
			*/
			
			if(!(valida_pw($senha,$login,$pass_adm) || valida_pw($senha,$login,$pass_administrador)))
			{

				$resposta->addAssign("mensagem","innerHTML",'Senha incorreta.');
						
				return $resposta;
			}

			$_SESSION["admin"] = TRUE;
			
			$_SESSION["login"] = $login;
			
			$_SESSION["nome_usuario"] = stripslashes("ADMINISTRADOR DO SISTEMA");			
			
			$_SESSION["id_usuario"] = 0;

			$_SESSION["id_funcionario"] = 0;
			
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

			$sql = "SELECT *, usuarios.id_usuario as id_usuario FROM ".DATABASE.".usuarios ";
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
	
			if($dados["condicao"] == 0) //1-ativo / 0-inativo
			{
				$resposta->addAssign("mensagem","innerHTML", 'Usuário Inativo');
			}
			else
			{
				// Caso o usuário tenha digitado um login válido o número de linhas será 1..
				if($db->numero_registros>=1)
				{	
					// Obtém os dados do usuário, para poder verificar a senha e passar os demais dados para a sessão									
					// Agora verifica a senha
					//if(!strcmp($senha, $enc->decrypt($dados["senha"])))
					if(valida_pw($senha,$login,$dados["senha"]))
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
						
						$_SESSION["nome_usuario"] = stripslashes($dados["nome"]);
						
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
						$resposta->addAssign("mensagem","innerHTML","Senha inválida.");
						
						return $resposta;
					}
				}
				// login inválido
				else
				{
					$resposta->addAssign("mensagem","innerHTML","Login inválido");
				}
			}			
		}
	}
	
	return $resposta;
}

//altera senha de acesso
function enviar($dados_form)
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$msg = $conf->msg($_COOKIE["idioma"],$resposta);
	
	if($dados_form["nome"]!="" && $dados_form["email"]!="" && $dados_form["senha"]!="")
	{	
		$db = new banco_dados;
	
		//$enc = new Crypter(CHAVE);
		
		//$cpf = str_replace(array('.','-'),'',$dados_form["nome"]);
		
		//verifica se numerico, case seja informou cpf
		///if(is_numeric($cpf))
		//{
			//$cpf = aplica_mascara($cpf,'cpf');
		//}
	
		$sql = "SELECT id_usuario, login, email FROM ".DATABASE.".usuarios ";
		$sql .= "WHERE usuarios.reg_del = 0 ";
		$sql .= "AND usuarios.login = '". minusculas(trim($dados_form["nome"])). "' ";
		$sql .= "AND usuarios.email = '".minusculas(trim($dados_form["email"]))."' ";		

		$db->select($sql,'MYSQL',true);

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
		
		$reg = $db->array_select[0];

		// Se o número de registros for maior que zero, então existe o registro...
		if ($db->numero_registros>0)
		{			
			//$senha = $enc->encrypt(trim($dados_form["senha"]));

			$senha = gera_hash(trim($dados_form["senha"]), minusculas(trim($dados_form["nome"])));
								
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
			$mensagem .= "Senha: " . trim($dados_form["senha"]) . "<br><br>\n\n";
			$mensagem .= "Tecnologia da Informação <br><br>\n\n";
			$mensagem .= "Caso tenha recebido este e-mail sem sua solicitação, favor desconsiderá-lo. <br><br>\n\n";
			$mensagem .= "O envio desta confirmação foi registrado em nosso banco de dados em ". date("d/m/Y") . " as " . date("H:i") . " <br><br><br>\n\n\n";
			$mensagem .= "E-mail enviado em ". date("d/m/Y") . " as " . date("H:i") . " <br>\n"; 

			if(ENVIA_EMAIL)
			{
				$params = array();
			
				$params['from']	= "empresa@".DOMINIO;
				
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
				$resposta->addScriptCall('modal', $mensagem, '300_650', 'Conteúdo email', 2);
			}
			
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
		$resposta->addAlert("A senha não poderá ser vazia.");
	}
	else
	{

		$sql = "SELECT * FROM ".DATABASE.".usuarios ";
		$sql .="WHERE id_usuario = '".$dados_form["id_usuario"]."' ";
		$sql .= "AND reg_del = 0 ";

		$db->select($sql,'MYSQL',true);

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
		
		if($db->numero_registros>0)
		{
			//$enc = new Crypter(CHAVE);
			
			$regs = $db->array_select[0];
						
			//$confsenha = $enc->decrypt($regs["senha"]);
			
			if(trim($dados_form["senha"])=="12345")
			{
				$resposta->addAlert("Esta senha não pode ser utilizada.");
			}
			else
			{			
				//if($confsenha == trim($dados_form["senha"]))
				if(valida_pw(trim($dados_form["senha"]),trim($regs["login"]),$regs["senha"]))		
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
						//$senha = $enc->encrypt(trim($dados_form["senha"]));
						$senha = gerar_hash(trim($dados_form["senha"]),trim($regs["login"]));
								
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
			$resposta->addAlert("Usuário não existe.");
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

$conf = new configs();

$smarty->assign("nome_empresa",NOME_EMPRESA);

$smarty->assign("revisao_documento","V0");

$smarty->assign("campo",$conf->campos('login'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("pagina",isset($_GET["pagina"]) ? $_GET["pagina"] : null);

$smarty->assign("user",$user);

$smarty->assign("classe",CSS_FILE);

$smarty->display("index.tpl");

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>utils.js"></script>

<script type="application/javascript">

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
	conteudo += '<input name="email" id="email" type="text" placeholder="E-mail" class="caixa" style="text-transform:none;" value="" size="50"/><br />';
	conteudo += '<label for="senha" class="labels">Nova senha</label><br />';
	conteudo += '<input name="senha" id="senha" type="password" placeholder="Senha" class="caixa" style="text-transform:none;" value="" size="50"/><br />';
	conteudo += '<input name="button" type="button" class="class_botao" onclick=xajax_enviar(xajax.getFormValues("frm_pass")); value="Enviar" />&nbsp';
	conteudo += '<input name="button" type="button" class="class_botao" onclick=divPopupInst.destroi(1); style="cursor:pointer;" value="Fechar" />';
	conteudo += '</form>';
	
	modal(conteudo, 'p', 'ESQUECI MINHA SENHA',1,diretorio_imagens);
	
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
	conteudo += '<label for="confsenha" class="labels">Confirme a senha</label><br />';
    conteudo += '<input name="confsenha" type="password" placeholder="Confime a senha" class="caixa" style="text-transform:none;" id="confsenha" size="30" onblur=xajax_validar_senha(xajax.getFormValues("frm_pass")); /><br />';
	conteudo += '<div class="alerta_erro" id="mensagem"> </div><br />';
	conteudo += '<input name="button" type="button" class="class_botao" onclick=xajax_atualiza(xajax.getFormValues("frm_pass")); value="Alterar" />&nbsp';
	conteudo += '<input name="button" type="button" class="class_botao" onclick=divPopupInst.destroi(1); style="cursor:pointer;" value="Fechar" />';
	conteudo += '</form>';

	modal(conteudo, 'p', 'TROCAR SENHA',1,diretorio_imagens);
	
	return true;	
}

</script>