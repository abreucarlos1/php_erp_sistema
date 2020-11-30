<?php
/*
		Formulário de Cadastro de senha	
		
		Criado por Carlos Abreu 
		
		local/Nome do arquivo:
		../firstpass.php
		
		Versão 0 --> VERSÃO INICIAL : 08/02/2006
		Versão 1 --> Atualização de lay-out / smarty
		Versão 2 --> Atualização de lay-out / DB: Carlos Abreu - 07/08/2012
		Versão 3 --> Atualização da regra de senhas - 29/09/2014 - Carlos Abreu
		Versão 4 --> Inclusão dos campos reg_del nas consultas - 13/11/2017 - Carlos Abreu		
*/
require_once("config.inc.php"); //ok

require_once(INCLUDE_DIR."include_form.inc.php");//ok

require_once(INCLUDE_DIR."encryption.inc.php"); //ok

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

function enviar($dados_form)
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
		$sql = "SELECT senha FROM ".DATABASE.".usuarios ";
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
						$usql .="senha = '". $senha . "', ";
						$usql .="status = '0', ";
						$usql .= "data_troca = '".date("Y-m-d")."' ";
						$usql .="WHERE id_usuario = '".$_SESSION["id_usuario"]."' ";
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

$xajax->registerFunction("validar_senha");
$xajax->registerFunction("enviar");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

?>
<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script language="javascript">

function limpa_div(div)
{
	div = document.getElementById(div);
	div.innerHTML = '';
}

</script>

<?php

$conf = new configs();

$smarty->assign("revisao_documento","V4");

$smarty->assign("campo",$conf->campos('firstpass',$_COOKIE['idioma']));

$smarty->assign("botao",$conf->botoes($_COOKIE['idioma']));

$smarty->assign("login",$_SESSION["login"]);

$smarty->assign("id_usuario",$_SESSION["id_usuario"]);

$smarty->assign("classe","classes/".$conf->classe('login').".css");

$smarty->display('firstpass.tpl');
?>