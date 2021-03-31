<?php
/*
		Acesso Especial	
		
		Criado por Carlos Eduardo Máximo
		
		local/Nome do arquivo:
		../ti/acessoespecial.php
	
		Versão 0 --> VERSÃO INICIAL : 02/09/2014
		Versão 1 --> Atualização layout - Carlos Abreu - 11/04/2017
		Versão 2 --> Inclusão dos campos reg_del nas consultas - 23/11/2017 - Carlos Abreu
		Versão 3 --> Layout responsivo - 05/02/2018 - Carlos Eduardo	
*/	

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

require_once(INCLUDE_DIR."encryption.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(493))
{
	nao_permitido();
}


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
	$pass_adm = $enc->encrypt('123456');
	
	// Recupera o login
	$login = isset($dados_form["login"]) ? addslashes(trim($dados_form["login"])) : FALSE;
	
	if($_SESSION['Perfil'] != 1 && !isset($_SESSION['adminTemp']))
	{
		$resposta->addAssign("mensagem","innerHTML",$msg[10]);
	}
	else
	{
		$sql = "SELECT * FROM ".DATABASE.".usuarios, ".DATABASE.".funcionarios ";
		$sql .= "WHERE login = '" . $login . "' ";
		$sql .= "AND usuarios.reg_del = 0 ";
		$sql .= "AND funcionarios.reg_del = 0 ";
		$sql .= "AND usuarios.id_usuario = funcionarios.id_usuario ";

		$db->select($sql,'MYSQL',true);

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
		
		$dados = $db->array_select[0];

		if($dados["situacao"]!="ATIVO")
		{
			$resposta->addAssign("mensagem","innerHTML", $msg[11]);
		}
		else
		{
			// Caso o usuário tenha digitado um login válido o número de linhas será 1..
			if($db->numero_registros>=1)
			{
				// Agora verifica a sessão do usuário administrador
				if(isset($_SESSION['Perfil']) && $_SESSION['Perfil'] == 1)
				{	
					//Cópia temporária da sessão do usuário administrador para dentro de admin
					//O objetivo é voltar para o usuário administrador depois que clicar em sair
					$_SESSION['adminTemp']['login'] = $_SESSION["login"];					
					$_SESSION['adminTemp']["nivel_atuacao"] = $_SESSION["nivel_atuacao"];					
					$_SESSION['adminTemp']["id_usuario"] = $_SESSION["id_usuario"];					
					$_SESSION['adminTemp']["id_funcionario"] = $_SESSION["id_funcionario"];					
					$_SESSION['adminTemp']["nome_usuario"] = $_SESSION["nome_usuario"];
					$_SESSION['adminTemp']["Perfil"] = $_SESSION["Perfil"];
					$_SESSION['adminTemp']["admin"] = $_SESSION["admin"];
					$_SESSION['adminTemp']["id_setor_aso"] = $_SESSION["id_setor_aso"];
					
					//Sessão do sistema
					$_SESSION["admin"] = FALSE;
					$_SESSION["login"] = $dados["login"];					
					$_SESSION["nivel_atuacao"] = $dados["nivel_atuacao"];					
					$_SESSION["id_usuario"] = $dados["id_usuario"];					
					$_SESSION["id_funcionario"] = $dados["id_funcionario"];
					$_SESSION["Perfil"] = $dados["Perfil"];					
					$_SESSION["id_setor_aso"] = $dados["id_setor_aso"];
					$_SESSION["nome_usuario"] = stripslashes($dados["funcionario"]);
					
					if($dados["status"]=="0")
					{
						if($dados_form["pagina"]!="")
						{
							$resposta->addRedirect($dados_form["pagina"]);
						}
						else
						{
							$resposta->addRedirect("../inicio.php");
						}						
					}
					else
					{
						$resposta->addRedirect("../firstpass.php");
					}						
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

function carrega_senha($usuario)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados();
	
	$crypter = new Crypter(CHAVE);
	
	$sql = "SELECT senha, id_funcionario, data_troca FROM ".DATABASE.".usuarios ";
	$sql .= "WHERE login = '".$usuario."' ";
	$sql .= "AND usuarios.reg_del = 0 ";
	
	$db->select($sql, 'MYSQL',true);
	
	$reg = $db->array_select[0];
	
	$resposta->addAssign('senhaHidden', 'value', $crypter->decrypt($reg['senha']));
	$resposta->addAssign('senha', 'value', $reg['senha']);
	$resposta->addAssign('dataTroca', 'value', mysql_php($reg['data_troca']));
	
	return $resposta;
}

function alterar_usuario($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados();
	
	$crypter = new Crypter(CHAVE);
	
	$senha = $crypter->encrypt(trim($dados_form['senha']));
	
	$usql = "UPDATE ".DATABASE.".usuarios SET ";
	$usql .= "data_troca = '".date('Y-m-d')."', ";
	$usql .= "senha = '".$senha."' ";
	$usql .= "WHERE login = '".$dados_form['login']."' ";
	$usql .= "AND reg_del = 0 ";
	
	$db->update($usql, 'MYSQL');
	
	if ($db->erro != '')
	{
		$resposta->addAlert('Falha ao alterar a senha!');
	}
	else
	{
		$resposta->addAlert('Senha alterada!');
		$resposta->addAssign('senhaHidden', 'value', trim($dados_form['senha']));
		$resposta->addAssign('senha', 'value', $senha);
		$resposta->addAssign('dataTroca', 'value', date('d/m/Y'));
	}
	
	return $resposta;
}

$xajax->registerFunction("autenticacao");
$xajax->registerFunction("carrega_senha");
$xajax->registerFunction("alterar_usuario");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script>
function revelar()
{
	document.getElementById('btnrevelar').value = 'Ocultar';
	
	document.getElementById('btnrevelar').onclick = function () {ocultar();};
	
	document.getElementById('senha').style.display = 'none';
	document.getElementById('senhaHidden').style.display = '';
}

function ocultar()
{

	document.getElementById('btnrevelar').value = 'Revelar';
	
	document.getElementById('btnrevelar').onclick = function(){revelar();};
	
	document.getElementById('senha').style.display = '';
	document.getElementById('senhaHidden').style.display = 'none';
}
</script>

<?php

$conf = new configs();

$db = new banco_dados();
		
$sql = "SELECT * FROM ".DATABASE.".usuarios
		JOIN (
			SELECT id_funcionario codFunc, funcionario
			FROM 
				".DATABASE.".funcionarios WHERE reg_del = 0 
		) Funcionarios
		ON codFunc = id_funcionario
		WHERE reg_del = 0
		ORDER BY
			funcionario";

$db->select($sql,'MYSQL',true);

if ($db->erro != '')
{
	exit("Não foi possível realizar a seleção.".$sql);
}

foreach($db->array_select as $regs)
{
	$array_usu_values[] = $regs["login"];
	$array_usu_output[] = substr($regs["funcionario"],0,1)." - ".sprintf('%05d', $regs["id_funcionario"])." - ".$regs['funcionario'];	
}

$smarty->assign("option_usu_values",$array_usu_values);
$smarty->assign("option_usu_output",$array_usu_output);

$smarty->assign("campo",$conf->campos('acessoespecial'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("revisao_documento","V3");

$smarty->assign('larguraTotal', 1);

$smarty->assign("classe",CSS_FILE);

$smarty->display('acessoespecial.tpl');
?>
