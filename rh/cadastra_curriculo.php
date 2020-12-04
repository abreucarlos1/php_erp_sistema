<?php
/*
		Formul�rio de Cadastro de Curr�culos	
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		../rh/cadastra_curriculo.php
		
		Versão 0 --> VERSÃO INICIAL : 01/06/2005
		Versão 1 --> OTIMIZA��O DE C�DIGO / SIMPLIFICA��O NO PREENCHIMENTO
		Versão 2 --> OTIMIZA��O DE C�DIGO / MUDAN�A LAY-OUT : 15/02/2007
		Versão 3 --> Atualização Lay-out / smarty : 20/10/2008
		Versão 4 --> Atualização banco de dados - 23/01/2015 - Carlos Abreu
		Versão 5 --> Retorno para produ��o - 30/01/2017 - Carlos Abreu
		Versão 6 --> Atualização layout - Carlos Abreu - 04/04/2017
		Versão 7 --> Inclusão dos campos reg_del nas consultas - 27/11/2017 - Carlos Abreu		
*/

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

require_once(INCLUDE_DIR."encryption.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO M�DULO 
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

	//Padr�o: sem arquivo para enviar.
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
						$resposta -> addAlert("O campo fun��o deve ser escolhido.");
						$resposta -> addScript('document.getElementsByName("funcao")[0].focus();');
						
					}
					else
					{
						if($_FILES["curriculo"]["name"]!="")
						{
							//Faz upload do arquivo do curr�culo (*.doc, *.txt ou *.rtf) e atualiza o campo LinkDoc, caso tenha sido selecionado.
							$chars = array("'","\"",")","(","\\","/");
							
							$tmp_arq = explode(".",$_FILES["curriculo"]["name"]);
							
							$ext = $tmp_arq[count($tmp_arq)-1];							  
							
							$curriculo_type = $_FILES["curriculo"]["type"];
					
							//faz upload do arquivo de logotipo, mostra mensagem caso ocorra algum erro.
							$curriculo_temp = $_FILES["curriculo"]["tmp_name"]; 
							
							//if($curriculo_type=="application/msword" || $curriculo_type=="text/plain" || $curriculo_type=="text/richtext" || $curriculo_type=="application/pdf")
							if($ext =="docx" || $ext=="doc" || $ext=="txt" || $ext=="rtf" || $ext=="pdf")
							{
								//Arquivo v�lido 
								$envio = 1;
							
							}
							else
							{
								//Arquivo inv�lido
								$envio = 2;
								//echo "<font color='red' face='arial' size=1><b>O tipo de arquivo do curr�culo anexado n�o � permitido. S�o permitidos apenas arquivos de tipo .doc (Word) .txt (Texto puro) .rtf (Rich Text) e .pdf (Adobe Acrobat).</b></font>";
								$resposta -> AddAlert("O tipo de arquivo do curr�culo anexado n�o � permitido");
							}	
							
							
						}
						
						if($envio==0 || $envio==1)						
						{
							$num = rand(111111,999999);
							
							$senha = $enc->encrypt($num);
													
							$sql = "SELECT * FROM Curriculo.CONTA ";
							$sql .= "LEFT JOIN Curriculo.DADOS ON (DADOS.UID = CONTA.UID AND DADOS.reg_del = 0) ";
							$sql .= "WHERE EMAIL = '" . $dados_form["email"] . "' ";
							$sql .= "AND CONTA.reg_del = 0 ";
					
							$db->select($sql,'MYSQL', true);
							
							if(($db->numero_registros>0)&&($dados_form["email"]!=""))
							{
								$user1 = $db->array_select[0];
								
								$pass = $enc->decrypt($user1["SENHA_CRIPT"]);
								
								if($curriculo_temp != "")
								{
									move_uploaded_file($_FILES["curriculo"]["tmp_name"],'curriculos/'.minusculas(tiraacentos(str_replace($chars,"",$dados_form["nome"]))). $user1["UID"] . "." . $ext);								
								}
						
								if($envio==0)
								{
									$nome_arquivo_upload = "";
								}
								else
								{
									$nome_arquivo_upload = "../rh/curriculos/" . tiraacentos(minusculas(str_replace($chars,"",$dados_form["nome"]))) . $user1["UID"] . "." . $ext;
								}
								
								$usql = "UPDATE Curriculo.DADOS SET ";
								$usql .= "id_status = '".$dados_form["status"]."', ";
								$usql .= "ATUALIZADO = '2', ";
								$usql .= "entrevistado = '".$dados_form["entrevistado"]."', ";
								$usql .= "LinkDoc = '" . $nome_arquivo_upload . "' ";
								$usql .= "WHERE DADOS.UID = '".$user1["UID"]."' ";
								
								$db->update($usql,'MYSQL');
								
								if($dados_form["email"]!="")
								{
								
									$mensagem = "Foi detectado que seu curr�culo est� cadastrado em nosso site, e seus dados para acesso s�o:<br><br>\n\n";
									$mensagem .= "Login: " . $dados_form["email"] . "<br>\n";
									$mensagem .= "Senha: " . $pass . "<br><br>\n\n";
									$mensagem .= "Recursos Humanos  solicita que seus dados sejam atualizados.<br><br><br><br><br>\n\n\n\n\n";
									$mensagem .= "Acesse nosso site atrav�s do http://www.devemada.com.br/conosco.php <br><br><br>\n\n\n";
									$mensagem .= "Aguardamos a Atualização de seu cadastro.<br><br><br><br><br>\n\n\n\n\n";
									$mensagem .= "E-mail enviado em ". date("d/m/Y") . " as " . date("H:i") . " <br>\n";
									$mensagem .= "Este e-mail � enviado automaticamente, favor n�o responde-lo.<br>\n";
									
									$params 			= array();
									$params['from']		= "recrutamento@dominio.com.br";
									$params['from_name']= "DEVEMADA ENGENHARIA";
									$params['subject'] 	= "Atualização de curr�culo";
									
									$params['emails']['to'][] = array('email' => $dados_form["email"], 'nome' => $user1["DAD_NOME"]);
							
									$mail = new email($params);
									$mail->montaCorpoEmail($mensagem);
									
									if(!$mail->Send())
									{
										$resposta->addAlert('Erro ao enviar e-mail!!! '.$mail->ErrorInfo);
									}
									
									$resposta->addAlert('E-mail j� cadastrado em nosso banco de dados.');
								}
							
							}				
							else
							{								
								//Inclui a conta do Usu�rio no banco de dados.
								$isql = "INSERT INTO Curriculo.CONTA (EMAIL, SENHA_CRIPT) VALUES (";
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
								
								//Inclui os dados do Usu�rio no banco de dados.
								$isql = "INSERT INTO Curriculo.DADOS (UID, DAD_NOME, DAD_CID, DAD_EST, id_status, ATUALIZADO, data_atualizacao, entrevistado, LinkDoc) VALUES (";
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
								
								$isql = "INSERT INTO Curriculo.OBJETIVO (UID, id_area, id_cargo) VALUES(";
								$isql .= "'" . $user_id . "', ";
								$isql .= "'" . $dados_form["modalidade"] . "', ";
								$isql .= "'" . $dados_form["funcao"] . "') ";
								
								$db->insert($isql,'MYSQL');
					
								$isql = "INSERT INTO Curriculo.FORMACAO (UID, FOR_AUTOCAD, FOR_PDS, FOR_PDMS, FOR_MICRO, FOR_NR10) VALUES(";
								$isql .= "'" . $user_id . "', ";
								$isql .= "'" . $dados_form["autocad"] . "', ";
								$isql .= "'" . $dados_form["pds"] . "', ";
								$isql .= "'" . $dados_form["pdms"] . "', ";
								$isql .= "'" . $dados_form["microstation"] . "', ";
								$isql .= "'" . $dados_form["nr10"] . "') ";
								
								$db->insert($isql,'MYSQL');
								
								if($dados_form["email"]!="")
								{
									$mensagem = "A DEVEMADA ENGENHARIA cadastrou previamente seu curr�culo, e seus dados para acesso s�o:<br><br>\n\n";
									$mensagem .= "Login: " . $dados_form["email"] . "<br>\n";
									$mensagem .= "Senha: " . $num . "<br><br>\n\n";
									$mensagem .= "Recursos Humanos <br><br><br><br><br>\n\n\n\n\n";
									$mensagem .= "Acesse nosso site atrav�s do http://www.devemada.com.br/conosco.php <br><br><br>\n\n\n";
									$mensagem .= "Aguardamos seu cadastro.<br><br><br><br><br>\n\n\n\n\n";
									$mensagem .= "E-mail enviado em ". date("d/m/Y") . " as " . date("H:i") . " <br>\n";
									$mensagem .= "Este e-mail � enviado automaticamente, favor n�o responde-lo.<br>\n"; 
									
									$params 			= array();
									$params['from']		= "recrutamento@dominio.com.br";
									$params['from_name']= "DEVEMADA ENGENHARIA";
									$params['subject'] 	= 'Cadastro/Atualização de curr�culo';
									
									$params['emails']['to'][] = array('email' => $dados_form["email"], 'nome' => ucwords($dados_form["nome"]));
							
									$mail = new email($params);
									$mail->montaCorpoEmail($mensagem);
									
									if(!$mail->Send())
									{
										$resposta->addAlert('Erro ao enviar e-mail!!! '.$mail->ErrorInfo);
									}
								}								
								
								$resposta -> AddAlert('Curr�culo cadastrado com sucesso!');
								
								$resposta->addScript("xajax_voltar();");
							}		
						}
						
					}
				}
					
			}
			else
			{
				$resposta -> addAlert("Endere�o de e-mail inv�lido.");
				
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

$sql = "SELECT * FROM Curriculo.status ";

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

$smarty->assign("nome_formulario","CADASTRO DE CURR�CULOS");

$smarty->assign("classe",CSS_FILE);

$smarty->display('cadastra_curriculo.tpl');

?>
