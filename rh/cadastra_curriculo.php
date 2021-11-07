<?php
/*
		Formulário de Cadastro de Currículos	
		
		Criado por Carlos Abreu 
		
		local/Nome do arquivo:
		../rh/cadastra_curriculo.php
		
		Versão 0 --> VERSÃO INICIAL : 01/06/2005
		Versão 1 --> OTIMIZAÇÃO DE CÓDIGO / SIMPLIFICAÇÃO NO PREENCHIMENTO
		Versão 2 --> OTIMIZAÇÃO DE CÓDIGO / MUDANÇA LAY-OUT : 15/02/2007
		Versão 3 --> Atualização Lay-out / smarty : 20/10/2008
		Versão 4 --> Atualização banco de dados - 23/01/2015 - Carlos Abreu
		Versão 5 --> Retorno para produção - 30/01/2017 - Carlos Abreu
		Versão 6 --> Atualização layout - Carlos Abreu - 04/04/2017
		Versão 7 --> Inclusão dos campos reg_del nas consultas - 27/11/2017 - Carlos Abreu		
*/

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

require_once(INCLUDE_DIR."encryption.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(84))
{
	die("ACESSO PROIBIDO!");
}

function voltar()
{
	$resposta = new xajaxResponse();

	$resposta -> addScriptCall("reset_campos('frm_curriculo')");
	
	$resposta -> addEvent("btnvoltar", "onclick", "history.back();");

	return $resposta;
}

function insere($dados_form)
{
	$enc = new Crypter(CHAVE);
	
	$db = new banco_dados;
	
	$resposta = new xajaxResponse();

	//Padrão: sem arquivo para enviar.
	$envio = 0;	
	
	if($dados_form["nome"]=="")
	{
		$resposta -> addAlert("O campo nome deve ser preenchido.");
		$resposta -> addScript('document.getElementsByName("nome")[0].focus();');
		
	}
	else
	{
		if(false)
		{
			$resposta -> addAlert("O campo e-mail deve ser preenchido.");
			$resposta -> addScript('document.getElementsByName("email")[0].focus();');
			
		}	
		else
		{
			if(true)
			{
				if($dados_form["modalidade"]=="")
				{
					$resposta -> addAlert("O campo modalidade deve ser escolhido.");
					$resposta -> addScript('document.getElementsByName("modalidade")[0].focus();');
					
				}
				else
				{
					if($dados_form["funcao"]=="")
					{
						$resposta -> addAlert("O campo função deve ser escolhido.");
						$resposta -> addScript('document.getElementsByName("funcao")[0].focus();');
						
					}
					else
					{
						if($_FILES["curriculo"]["name"]!="")
						{
							//Faz upload do arquivo do currículo (*.doc, *.txt ou *.rtf) e atualiza o campo LinkDoc, caso tenha sido selecionado.
							$chars = array("'","\"",")","(","\\","/");
							
							$tmp_arq = explode(".",$_FILES["curriculo"]["name"]);
							
							$ext = $tmp_arq[count($tmp_arq)-1];							  
							
							$curriculo_type = $_FILES["curriculo"]["type"];
					
							//faz upload do arquivo de logotipo, mostra mensagem caso ocorra algum erro.
							$curriculo_temp = $_FILES["curriculo"]["tmp_name"]; 
							
							//if($curriculo_type=="application/msword" || $curriculo_type=="text/plain" || $curriculo_type=="text/richtext" || $curriculo_type=="application/pdf")
							if($ext =="docx" || $ext=="doc" || $ext=="txt" || $ext=="rtf" || $ext=="pdf")
							{
								//Arquivo válido 
								$envio = 1;
							
							}
							else
							{
								//Arquivo inválido
								$envio = 2;
								//echo "<font color='red' face='arial' size=1><b>O tipo de arquivo do currículo anexado não é permitido. São permitidos apenas arquivos de tipo .doc (Word) .txt (Texto puro) .rtf (Rich Text) e .pdf (Adobe Acrobat).</b></font>";
								$resposta -> AddAlert("O tipo de arquivo do currículo anexado não é permitido");
							}							
						}
						
						if($envio==0 || $envio==1)						
						{
							$num = rand(111111,999999);
							
							$senha = $enc->encrypt($num);
													
							$sql = "SELECT * FROM ".DATABASE.".curriculos_conta ";
							$sql .= "LEFT JOIN ".DATABASE.".curriculos_dados ON (curriculos_dados.uid = curriculos_conta.uid AND curriculos_dados.reg_del = 0) ";
							$sql .= "WHERE email = '" . $dados_form["email"] . "' ";
							$sql .= "AND curriculos_conta.reg_del = 0 ";
					
							$db->select($sql,'MYSQL', true);
							
							if(($db->numero_registros>0)&&($dados_form["email"]!=""))
							{
								$user1 = $db->array_select[0];
								
								$pass = $enc->decrypt($user1["senha_cript"]);
								
								if($curriculo_temp != "")
								{
									move_uploaded_file($_FILES["curriculo"]["tmp_name"],'curriculos/'.minusculas(tiraacentos(str_replace($chars,"",$dados_form["nome"]))). $user1["uid"] . "." . $ext);								
								}
						
								if($envio==0)
								{
									$nome_arquivo_upload = "";
								}
								else
								{
									$nome_arquivo_upload = "../rh/curriculos/" . tiraacentos(minusculas(str_replace($chars,"",$dados_form["nome"]))) . $user1["uid"] . "." . $ext;
								}
								
								$usql = "UPDATE ".DATABASE.".curriculos_dados SET ";
								$usql .= "id_status = '".$dados_form["status"]."', ";
								$usql .= "atualizado = '2', ";
								$usql .= "entrevistado = '".$dados_form["entrevistado"]."', ";
								$usql .= "linkdoc = '" . $nome_arquivo_upload . "' ";
								$usql .= "WHERE uid = '".$user1["uid"]."' ";
								
								$db->update($usql,'MYSQL');
								
								if($dados_form["email"]!="")
								{
								
									$mensagem = "Foi detectado que seu currículo está cadastrado em nosso site, e seus dados para acesso são:<br><br>\n\n";
									$mensagem .= "login: " . $dados_form["email"] . "<br>\n";
									$mensagem .= "Senha: " . $pass . "<br><br>\n\n";
									$mensagem .= "Recursos Humanos  solicita que seus dados sejam atualizados.<br><br><br><br><br>\n\n\n\n\n";
									$mensagem .= "Acesse nosso site através do http://www.empresa.com.br/conosco.php <br><br><br>\n\n\n";
									$mensagem .= "Aguardamos a Atualização de seu cadastro.<br><br><br><br><br>\n\n\n\n\n";
									$mensagem .= "E-mail enviado em ". date("d/m/Y") . " as " . date("H:i") . " <br>\n";
									$mensagem .= "Este e-mail é enviado automaticamente, favor não responde-lo.<br>\n";
									
									if(ENVIA_EMAIL)
									{

										$params 			= array();
										$params['from']		= "recrutamento@".DOMINIO;
										$params['from_name']= NOME_EMPRESA;
										$params['subject'] 	= "Atualização de currículo";
										
										$params['emails']['to'][] = array('email' => $dados_form["email"], 'nome' => $user1["dad_nome"]);
								
										$mail = new email($params);
										$mail->montaCorpoEmail($mensagem);
										
										if(!$mail->Send())
										{
											$resposta->addAlert('Erro ao enviar e-mail!!! '.$mail->ErrorInfo);
										}
									}
									else 
									{
										$resposta->addScriptCall('modal', $mensagem, '300_650', 'Conteúdo email', 1);
									}
									
									$resposta->addAlert('E-mail já cadastrado em nosso banco de dados.');
								}
							
							}				
							else
							{								
								//Inclui a conta do Usuário no banco de dados.
								$isql = "INSERT INTO ".DATABASE.".curriculos_conta (email, senha_cript) VALUES (";
								$isql .= "'" . minusculas(tiraacentos($dados_form["email"])) . "', ";
								$isql .= "'" . $senha . "') ";
								
								$db->insert($isql,'MYSQL');
							
								$user_id = $db->insert_id;
							
								if($curriculo_temp != '')
								{
									move_uploaded_file($_FILES["curriculo"]["tmp_name"],'curriculos/'.minusculas(tiraacentos(str_replace($chars,"",$dados_form["nome"]))). $user_id . "." . $ext);
								}
						
								if($envio==0)
								{
									$nome_arquivo_upload = "";
								}
								else
								{
									$nome_arquivo_upload = "../rh/curriculos/" . tiraacentos(minusculas(str_replace($chars,"",$dados_form["nome"]))) . $user_id . "." . $ext;
								}								
								
								//Inclui os dados do Usuário no banco de dados.
								$isql = "INSERT INTO ".DATABASE.".curriculos_dados (uid, dad_nome, dad_cid, dad_est, id_status, atualizado, data_atualizacao, entrevistado, linkdoc) VALUES (";
								$isql .= "'" . $user_id . "', ";
								$isql .= "'" . ucwords(addslashes(trim($dados_form["nome"]))) . "', ";
								$isql .= "'" . $dados_form["cidade"] . "', ";
								$isql .= "'" . $dados_form["estado"] . "', ";
								$isql .= "'" . $dados_form["status"] . "', ";
								$isql .= "'2', ";
								$isql .= "'".date("Y-m-d")."', ";
								$isql .= "'" . $dados_form["entrevistado"] . "', ";
								$isql .= "'" . $nome_arquivo_upload . "') ";
								
								$db->insert($isql,'MYSQL');
								
								$isql = "INSERT INTO ".DATABASE.".curriculos_objetivos (uid, id_area, id_cargo) VALUES(";
								$isql .= "'" . $user_id . "', ";
								$isql .= "'" . $dados_form["modalidade"] . "', ";
								$isql .= "'" . $dados_form["funcao"] . "') ";
								
								$db->insert($isql,'MYSQL');
					
								$isql = "INSERT INTO ".DATABASE.".curriculos_formacao (uid, for_autocad, for_pds, for_pdms, for_micro, for_nr10) VALUES(";
								$isql .= "'" . $user_id . "', ";
								$isql .= "'" . $dados_form["autocad"] . "', ";
								$isql .= "'" . $dados_form["pds"] . "', ";
								$isql .= "'" . $dados_form["pdms"] . "', ";
								$isql .= "'" . $dados_form["microstation"] . "', ";
								$isql .= "'" . $dados_form["nr10"] . "') ";
								
								$db->insert($isql,'MYSQL');
								
								if($dados_form["email"]!="")
								{
									$mensagem = "A ".NOME_EMPRESA." cadastrou previamente seu currículo, e seus dados para acesso são:<br><br>\n\n";
									$mensagem .= "login: " . $dados_form["email"] . "<br>\n";
									$mensagem .= "Senha: " . $num . "<br><br>\n\n";
									$mensagem .= "Recursos Humanos <br><br><br><br><br>\n\n\n\n\n";
									$mensagem .= "Acesse nosso site através do http://www.empresa.com.br/conosco.php <br><br><br>\n\n\n";
									$mensagem .= "Aguardamos seu cadastro.<br><br><br><br><br>\n\n\n\n\n";
									$mensagem .= "E-mail enviado em ". date("d/m/Y") . " as " . date("H:i") . " <br>\n";
									$mensagem .= "Este e-mail é enviado automaticamente, favor não responde-lo.<br>\n"; 
									
									if(ENVIA_EMAIL)
									{

										$params 			= array();
										$params['from']		= "recrutamento@".DOMINIO;
										$params['from_name']= NOME_EMPRESA;
										$params['subject'] 	= 'Cadastro/Atualização de currículo';
										
										$params['emails']['to'][] = array('email' => $dados_form["email"], 'nome' => ucwords($dados_form["nome"]));
								
										$mail = new email($params);
										
										$mail->montaCorpoEmail($mensagem);
										
										if(!$mail->Send())
										{
											$resposta->addAlert('Erro ao enviar e-mail!!! '.$mail->ErrorInfo);
										}
									}
									else 
									{
										$resposta->addScriptCall('modal', $mensagem, '300_650', 'Conteúdo email', 1);
									}
								}								
								
								$resposta -> AddAlert('Currículo cadastrado com sucesso!');
								
								$resposta->addScript("xajax_voltar();");
							}		
						}
						
					}
				}
					
			}
			else
			{
				$resposta -> addAlert("Endereço de e-mail inválido.");
				
				$resposta -> addScript('document.getElementsByName("email")[0].focus();');						
			}						
		}
	}
	
	return $resposta;	
}

function preencheCombo($id, $controle='', $selecionado='')
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;

	$sql = "SELECT * FROM ".DATABASE.".cidades ";
	$sql .= "WHERE cidades.id_estado = '" . $id . "' ";
	$sql .= "ORDER BY cidades.cidade ";
		
	$db->select($sql,'MYSQL',true);

	foreach($db->array_select as $reg)
	{		
		$matriz[$reg["cidade"]] = $reg["id_cidade"];
	}
	
	$resposta->addNewOptions($controle, $matriz, $selecionado);
	
	return $resposta;

}

$xajax->registerFunction("voltar");
$xajax->registerFunction("insere");
$xajax->registerFunction("preencheCombo");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

?>
<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<?php

$conf = new configs();

$array_setor_values = NULL;
$array_setor_output = NULL;

$array_cargo_values = NULL;
$array_cargo_output = NULL;

$array_estados_values = NULL;
$array_estados_output = NULL;

$array_status_values = NULL;
$array_status_output = NULL;

$array_setor_values[] = "";
$array_setor_output[] = "SELECIONE";

$array_cargo_values[] = "";
$array_cargo_output[] = "SELECIONE";

$array_estados_values[] = "";
$array_estados_output[] = "SELECIONE";

$sql = "SELECT * FROM ".DATABASE.".setores ";
$sql .= "ORDER BY setor";

$db->select($sql,'MYSQL',true);

foreach($db->array_select as $area)
{
	$array_setor_values[] = $area["id_setor"];
	$array_setor_output[] = $area["setor"];
} 


$sql = "SELECT * FROM ".DATABASE.".rh_funcoes ";
$sql .= "ORDER BY descricao ";

$db->select($sql,'MYSQL',true);

foreach($db->array_select as $cargo) 
{
	$array_cargo_values[] = $cargo["id_funcao"];
	$array_cargo_output[] = $cargo["descricao"];
}

$sql = "SELECT * FROM ".DATABASE.".estados ";
$sql .= "ORDER BY uf ";

$db->select($sql,'MYSQL',true);

foreach($db->array_select as $estado) 
{
	$array_estados_values[] = $estado["id_estado"];
	$array_estados_output[] = $estado["estado"];
}

$sql = "SELECT * FROM ".DATABASE.".curriculos_status ";

$db->select($sql,'MYSQL',true);

foreach($db->array_select as $cont)
{
	$array_status_values[] = $cont["id_status"];
	$array_status_output[] = $cont["status"];
}

$smarty->assign("option_setor_values",$array_setor_values);
$smarty->assign("option_setor_output",$array_setor_output);

$smarty->assign("option_cargo_values",$array_cargo_values);
$smarty->assign("option_cargo_output",$array_cargo_output);

$smarty->assign("option_estados_values",$array_estados_values);
$smarty->assign("option_estados_output",$array_estados_output);

$smarty->assign("option_status_values",$array_status_values);
$smarty->assign("option_status_output",$array_status_output);

$smarty->assign("revisao_documento","V6");

$smarty->assign("nome_formulario","CADASTRO DE CURRÍCULOS");

$smarty->assign("classe",CSS_FILE);

$smarty->display('cadastra_curriculo.tpl');

?>
