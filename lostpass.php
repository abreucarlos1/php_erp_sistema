<?php
/*
		Formulário de Recuperação de Senha	
		
		Criado por Carlos Abreu
		
		local/Nome do arquivo:
		lostpass.php
		
		Versão 0 --> VERSÃO INICIAL : 08/02/2006
		Versão 1 --> Atualização Lay-out / smarty : 26/03/2009
		Versão 2 --> Atualização DB / Lay-out : Carlos Abreu - 07/08/2012
		Versão 3 --> Inclusão dos campos reg_del nas consultas - 14/11/2017 - Carlos Abreu		
		
*/
require_once("config.inc.php"); //ok

require_once(INCLUDE_DIR."include_form.inc.php"); //ok

require_once(INCLUDE_DIR."encryption.inc.php"); //ok

function enviar($dados_form)
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$msg = $conf->msg($_COOKIE["idioma"],$resposta);
	
	if($dados_form["nome"]!="" && $dados_form["email"]!="" && $dados_form["senha"]!="")
	{	
	
		$db = new banco_dados;
	
		$enc = new Crypter(CHAVE);
	
		/*
		$sql = "SELECT * FROM ".DATABASE.".funcionarios, ".DATABASE.".usuarios ";
		$sql .= "WHERE funcionarios.funcionario = '". maiusculas(trim($dados_form["nome"])). "' ";
		$sql .= "AND funcionarios.reg_del = 0 ";
		$sql .= "AND usuarios.reg_del = 0 ";
		$sql .= "AND funcionarios.id_funcionario = usuarios.id_funcionario ";
		*/

		$sql = "SELECT id_usuario, login, email FROM ".DATABASE.".usuarios ";
		$sql .= "WHERE usuarios.login = '". minusculas(trim($dados_form["nome"])). "' ";
		$sql .= "AND usuarios.email = '". minusculas(trim($dados_form["email"])). "' ";
		$sql .= "AND usuarios.reg_del = 0 ";

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
			$mensagem .= "Login: " . $reg["login"] . "<br>\n";
			$mensagem .= "Senha: " . $senha . "<br><br>\n\n";
			$mensagem .= "Tecnologia da Informação <br><br>\n\n";
			$mensagem .= "Caso tenha recebido este e-mail sem sua solicitação, favor desconsiderá-lo. <br><br>\n\n";
			$mensagem .= "O envio desta confirmaçãoo foi registrado em nosso banco de dados em ". date("d/m/Y") . " as " . date("H:i") . " <br><br><br>\n\n\n";
			$mensagem .= "E-mail enviado em ". date("d/m/Y") . " as " . date("H:i") . " <br>\n"; 

			$params = array();
			
			$params['from']	= "empresa@dominio.com.br";
			
			$params['from_name'] = "Recuperação senha - EMPRESA X";
			
			$params['subject'] = "RECUPERAÇÃO DE SENHA";
			
			$params['emails']['to'][] = array('email' => $reg["email"], 'nome' => $regs["login"]);
			
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


$xajax->registerFunction("enviar");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));


?>
<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<?php

$conf = new configs();

$smarty->assign("revisao_documento","V3");

$smarty->assign("campo",$conf->campos('lostpass',$_COOKIE["idioma"]));

$smarty->assign("botao",$conf->botoes($_COOKIE["idioma"]));

$smarty->assign("classe",CSS_FILE);

$smarty->display('lostpass.tpl');

?>
