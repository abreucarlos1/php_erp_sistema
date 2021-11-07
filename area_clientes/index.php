<?php
/*
		Formulário de Autenticação - area clientes	
		
		Criado por Carlos Abreu 
		
		local/Nome do arquivo:
		area_clientes/index.php
		
		Versão 0 --> VERSÃO INICIAL - 04/06/2014

*/

//error_reporting(E_ALL);

$usercliente = "";

//setcookie("idioma",1,time()+60*60*24*180);

//seta idioma se não estiver setado
if (!isset($_COOKIE['idioma'])) 
{
   $_COOKIE["idioma"]="1";
   setcookie("idioma",1,time()+60*60*24*180);
}

if (isset($_COOKIE['usercliente'])) 
{
   $usercliente = $_COOKIE['usercliente'];
}

require("../includes/include_form.inc.php");

include("../includes/encryption.inc.php");

function autenticacao($dados_form)
{
	session_start();
	
	if(isset($_SESSION["id_sub_modulo"]))
	{
		unset($_SESSION["id_sub_modulo"]);
	}

	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$enc = new Crypter('ENGENHARIA');
		
	$msg = $conf->msg($resposta);
	
	$db = new banco_dados;	

	// Recupera o login
	$login = isset($dados_form["login"]) ? addslashes(trim($dados_form["login"])) : FALSE;
	// Recupera a senha, a criptografando em MD5
	$senha = isset($dados_form["senha"]) ? $dados_form["senha"] : FALSE;
	// Usuário não forneceu a senha ou o login
	if(!$login || !$senha)
	{
		//echo "Você deve digitar sua senha e login!";
		$resposta->addAssign("mensagem","innerHTML",$msg[10]);
	}
	else
	{
		/**
		* Executa a consulta no banco de dados.
		* Caso o número de linhas retornadas seja 1 o login é válido,
		* caso 0, inválido.
		*/
		$sql = "SELECT * FROM ".DATABASE.".contatos ";
		$sql .= "WHERE email = '" . $login . "' ";
		//$sql .= "AND situacao = 1 ";
		
		//FAZ O SELECT
		$result = $db->select($sql,'MYSQL');
		
		//se der mensagem de erro, mostra
		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
		
		$dados = mysqli_fetch_assoc($result);

		if($dados["situacao"]!="1")
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
					// TUDO OK! Agora, passa os dados para a sessão e redireciona o usuário
					//$id_funcionario = $dados["id_funcionario"];
					$_SESSION["login"] = trim($dados["email"]);
					
					$_SESSION["id_usuario"] = "#";
					
					$_SESSION["nome_usuario"] = "#";
					
					$_SESSION["id_contato"] = $dados["id_contato"];
					
					$_SESSION["nome_contato"] = stripslashes($dados["nome_contato"]);
					
					$resposta->addRedirect("inicio.php");							
					
				}
				// Senha inválida
				else
				{
					$resposta->addAssign("mensagem","innerHTML",$msg[12]);
					
					return $resposta;
				}
			}
			// login inválido
			else
			{
				$resposta->addAssign("mensagem","innerHTML",$msg[13]);
			}
		}		
	}
	
	return $resposta;
}

$xajax->registerFunction("autenticacao");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript('../includes/xajax'));

//$smarty->assign("body_onload","Muda();");

?>

<script>

function esqueceusenha()
{	
	altura = 325;
	largura = 625;
	
	y = screen.height/2-altura/2;
	x = screen.width/2-largura/2;	
	
	window.open('lostpass.php', '_blank', 'height='+altura+', width='+largura+', location=no, menubar=no, resizable=no, scrollbars=no, status=no, toolbar=no, top='+y+', left='+x+'');
}

</script>

<?php

$conf = new configs();

$smarty->assign("revisao_documento","V0");

$smarty->assign("campo",$conf->campos('login_clientes'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("pagina",$_GET["pagina"]);

$smarty->assign("usercliente",$usercliente);

$smarty->assign("classe",CSS_FILE);

$smarty->display("index.tpl");

?>
