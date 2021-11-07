<?php
/*
		Formulário de Requisição de Pessoal
		
		Criado por Carlos Abreu
		
		local/Nome do arquivo:
		../rh/requisicao_pessoal.php
		
		Versão 0 --> VERSÃO INICIAL : 21/06/2007
		Versão 1 --> Atualização Lay-out : 29/09/2008
		Versão 2 --> Alteração de funcionalidade / dividido em 2 arquivos : 01/10/2008
		Versão 3 --> Atualização banco de dados - 23/01/2015 - Carlos Abreu		
		Versão 4 --> Adição dos campos Aspectos comportamentais e reporte direto - 08/04/2015 - Carlos
		Versão 5 --> Passando para o novo layout, comecei do zero - 27/04/2016 - Carlos
		Versão 6 --> Adicionados os campos de mobilização - 31/10/2016 - Carlos 
		Versão 7 --> Removida a parte de idiomas, visto que agora está no módulo de cargos/funções
		Versão 8 --> Atualização layout - Carlos Abreu - 10/04/2017 
		Versão 9 --> Inclusão dos campos reg_del nas consultas - 29/11/2017 - Carlos Abreu
*/
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(131) && !verifica_sub_modulo(252) && !verifica_sub_modulo(263))
{
	nao_permitido();
}

function atualizatabela($dados_form)
{
	$resposta = new xajaxResponse();
	
	$resposta->addAssign("solicitante","innerHTML", $_SESSION["nome_usuario"]);
	
	$db = new banco_dados();
	
	$sql = "SELECT * FROM ".DATABASE.".funcionarios, ".DATABASE.".status_requisicao, ".DATABASE.".requisicoes_pessoal ";
	$sql .= "LEFT JOIN ".DATABASE.".ordem_servico ON (requisicoes_pessoal.id_os = ordem_servico.id_os AND ordem_servico.reg_del = 0) ";
	$sql .= "WHERE requisicoes_pessoal.id_solicitante = funcionarios.id_funcionario ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	$sql .= "AND status_requisicao.reg_del = 0 ";
	$sql .= "AND requisicoes_pessoal.reg_del = 0 ";
	$sql .= "AND requisicoes_pessoal.id_solicitante = '" . $_SESSION["id_funcionario"] . "' ";
	$sql .= "AND requisicoes_pessoal.ultimo_status = status_requisicao.id_status_requisicao ";
		
	if($dados_form["filtro"]!="")
	{
		if($dados_form["filtro"]!="-1")
			$sql .= "AND status_requisicao.id_status_requisicao = '" . $dados_form["filtro"] . "' ";
	}
	else
	{
		$sql .= "AND status_requisicao.id_status_requisicao = 2 "; //solicitado
	}
	
	if (!empty($dados_form['busca']))
	{
		$sql .= "AND ordem_servico.descricao LIKE '%".$dados_form['busca']."%' ";
	}
		
	$sql .= "GROUP BY requisicoes_pessoal.id_requisicao ";

	$db->select($sql,'MYSQL', true);
	
	$cont_requisicoes = $db->array_select;

	$xml = new XMLWriter();
	$xml->openMemory();
	$xml->startElement('rows');

	foreach($cont_requisicoes as $reg)
	{
		$sql = "SELECT rs.*, sr.status, sr.id_status_requisicao FROM ".DATABASE.".requisicao_x_status rs ";
		$sql .= "JOIN ".DATABASE.".status_requisicao sr ON sr.id_status_requisicao = rs.id_status_requisicao AND sr.reg_del = 0  ";
		$sql .= "WHERE rs.id_requisicao = '".$reg["id_requisicao"]."' ";
		$sql .= "AND rs.reg_del = 0 ";
		$sql .= "ORDER BY id_requisicao_x_status DESC LIMIT 1 ";
		
		$db->select($sql,'MYSQL',true);
		
		$regStatus = $db->array_select[0];
	
		//verifica se existe OS
		if(empty($reg["os"]))
		{
			if(empty($reg["os_outros"]))
			{
				$os = $reg["descricao"];
			}
			else
			{
				$os = $reg["os_outros"];
			}
		}
		else
		{
			$os = sprintf('%06d', $reg['os']).' - '.$reg["descricao"];
		}
		
		$xml->startElement('row');
			$xml->writeAttribute('id', $reg["id_requisicao"]);
			$xml->writeElement('cell', sprintf("%05d",$reg["id_requisicao"]));
			$xml->writeElement('cell', $regStatus["status"]);
			$xml->writeElement('cell', mysql_php($regStatus["data_alteracao"]));
			$xml->writeElement('cell', $reg["funcionario"]);
			$xml->writeElement('cell', str_replace("'","`", $os));
			
			if ($regStatus['id_status_requisicao'] == 2)
			{
				$img = '<img src="'.DIR_IMAGENS.'apagar.png" style="cursor:pointer;" onclick=confirmaCancelamento("'.$reg["id_requisicao"].'");>';
				
				$xml->writeElement('cell',$img);
			}
			else
			{
				$xml->writeElement('cell', ' ');
			}
		
		$xml->endElement();
	}

	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('div_requisicoes', true, '450', '".$conteudo."');");

	return $resposta;
}

function preenche_escolaridade($id_cargo)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;

	$sql = "SELECT * FROM ".DATABASE.".rh_funcoes, ".DATABASE.".rh_escolaridade ";
	$sql .= "WHERE rh_funcoes.id_funcao = '" . $id_cargo . "' ";
	$sql .= "AND rh_funcoes.reg_del = 0 ";
	$sql .= "AND rh_escolaridade.reg_del = 0 ";
	$sql .= "AND rh_funcoes.id_rh_escolaridade = rh_escolaridade.id_rh_escolaridade ";

	$db->select($sql,'MYSQL',true);

	$reg = $db->array_select[0];
	
	$resposta->addAssign("escolaridade","innerHTML",$reg["escolaridade"]);
	
	$resposta->addAssign("div_experiencia","innerHTML",$reg["experiencia"]);
	
	$sql = "SELECT * FROM ".DATABASE.".rh_cargos_x_conhecimento, ".DATABASE.".rh_conhecimentos ";
	$sql .= "WHERE rh_cargos_x_conhecimento.id_rh_cargo = '" . $id_cargo . "' ";
	$sql .= "AND rh_cargos_x_conhecimento.reg_del = 0 ";
	$sql .= "AND rh_conhecimentos.reg_del = 0 ";
	$sql .= "AND rh_conhecimentos. id_rh_conhecimento = rh_cargos_x_conhecimento.id_rh_conhecimento ";
	$sql .= "AND rh_cargos_x_conhecimento.rh_cargos_x_conhecimento_status = 1 ";

	$db->select($sql,'MYSQL',true);

	$resposta->addScript("combo_destino = xajax.$('requisitos_cargo'); ");
	$resposta->addScriptCall("limpa_combo('requisitos_cargo')");
	
	foreach($db->array_select as $reg1)
	{
		$tam>strlen($reg1["conhecimento"])?$tam=strlen($reg1["conhecimento"]):$tam;
		$resposta->addScript("combo_destino.options[combo_destino.length] = new Option('".$reg1["conhecimento"]."', '".$reg1["id_rh_conhecimento"]."');");
	}
	
	$resposta->addAssign("requisitos_cargo","style.width",$tam);
	$resposta->addAssign("experiencia","innerHTML",$reg['competencias_tecnicas']);
	$resposta->addAssign("aspectos_comportamentais","innerHTML",$reg['competencias_individuais']);	

	return $resposta;
}

function excluir($id_requisicao, $dados_form)
{
	$resposta = new xajaxResponse();
	
	if(!empty($dados_form['txt_motivo_cancelamento']))
	{
		$db = new banco_dados;

		$usql = "UPDATE ".DATABASE.".requisicoes_pessoal SET ";
		$usql .= "ultimo_status = 1, "; //STATUS CANCELADO
		$usql .= "motivo_cancelamento = '" . addslashes(maiusculas($dados_form['txt_motivo_cancelamento'])) . "' ";
		$usql .= "WHERE id_requisicao = '" . $id_requisicao . "' ";
		
		$db->update($usql,'MYSQL');
	
		if($db->erro!='')
		{
			$isql = "INSERT INTO ".DATABASE.".requisicao_x_status ";
			$isql .= "(id_status_requisicao, id_requisicao, data_alteracao,id_funcionario) ";
			$isql .= "VALUES (";
			$isql .= "'1', "; //STATUS CANCELADO
			$isql .= "'".$id_requisicao."', ";
			$isql .= "'".date("Y-m-d")."', ";
			$isql .= "'".$_SESSION["id_funcionario"]."') ";
			
			$db->insert($isql,'MYSQL');
		
			$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm')); ");
			$resposta->addAlert("Requisição cancelada com sucesso.");
			$resposta->addScript("divPopupInst.destroi(); ");	
			
			//Manda e-mail com aviso do cancelamento ao RH
			$mensagem_rh = "<span style=\"font-family:Arial, Helvetica, sans-serif; font-size:11px; padding:0px; margin:0px;\"><P>A seguinte requisição foi cancelada pelo solicitante:</P><BR>";
			$mensagem_rh .= "<P>Nº: " . sprintf("%05d",$id_requisicao) . "</P>";
			$mensagem_rh .= "<P>data: " . date("d/m/Y") . "</P>";
			$mensagem_rh .= "<P>Solicitante: " . $_SESSION["nome_usuario"] . "</P>";
			$mensagem_rh .= "<P>Motivo: " . $motivo . "</P>";
			$mensagem_rh .= "<P>Clique <a href='http://ENDERECO/SISTEMA/rh/requisicao_pessoal_adm.php?id_requisicao=" . $id_requisicao . "'>aqui</a> para visualizar.</P></span>";
			
			if(ENVIA_EMAIL)
			{

				$params 			= array();
				$params['from']		= "recrutamento@".DOMINIO;
				$params['from_name']= "SISTEMA REQUISIÇÃO DE PESSOAL - CANCELAMENTO DE VAGA";
				$params['subject'] 	= "VAGA CANCELADA";
				
				$params['emails']['to'][] = array('email' => "recrutamento@".DOMINIO, 'nome' => "Recursos Humanos");

				$mail = new email($params);
				$mail->montaCorpoEmail($mensagem_rh);
				
				if(!$mail->Send())
				{
					$resposta->addAlert("Erro ao enviar e-mail!!! ".$mail->ErrorInfo);
				}
			}
			else
			{
				$resposta->addScriptCall('modal', $mensagem_rh, '300_650', 'Conteúdo email', 1);
			}			
		}
		else
		{
			$resposta->addAlert('Requisição cancelada corretamente!');
			$resposta->addScript('divPopupInst.destroi();');
			$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'));");
		}
	}
	else
	{
		$resposta->addAlert("É necessário informar um motivo para o cancelamento.");
	}
	
	return $resposta;
}

function insere($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	if(!isset($dados_form["tipo"]))
	{
		$resposta->addAlert("É necessário escolher o tipo de contratação.");
	}
	else
	{
		if($dados_form["tipo"]=='1')
		{
		    if((!isset($dados_form["id_os"]) || empty($dados_form["id_os"])) && (!isset($dados_form["txt_os"]) || empty($dados_form["txt_os"])))
			{
				$resposta->addAlert("Para a vaga efetiva, deve-se escolher uma OS.");
				
				return $resposta;		
			}
		}
		
		$integracaoCliente = isset($dados_form['integracao_cliente']) ? $dados_form['integracao_cliente'] : 0;
		
		//Insere a requisição
		$isql = "INSERT INTO ".DATABASE.".requisicoes_pessoal ";
		$isql .= "(id_solicitante, descricao, prazo, tipo_contrato, tipo, motivo, motivo_outros, id_os, id_local, os_outros, local_outros, categoria_contratacao, ";
		$isql .= "categoria_contratacao_outros, id_cargo, qtde_vagas, tempo_servico, infra_outros, informacoes_ti, ultimo_status, experiencia, aspectos_comportamentais, ";
		$isql .= "reporte_direto, nivel_atuacao, integracao_cliente, mobilizacao, detalhes_mobilizacao) ";
		$isql .= "VALUES (";
		$isql .= "'" . $_SESSION["id_funcionario"] . "', ";
		$isql .= "'" . addslashes(maiusculas($dados_form["projeto"])) . "', ";
		$isql .= "'" . $dados_form["prazo"] . "', ";
		$isql .= "'" . $dados_form["contrato"] . "', ";
		$isql .= "'" . $dados_form["tipo"] . "', ";
		$isql .= "'" . $dados_form["cmb_motivo"] . "', ";
		$isql .= "'" . addslashes(maiusculas($dados_form["txt_motivo"])) . "', ";
		$isql .= "'" . $dados_form["id_os"] . "', ";
		$isql .= "'" . $dados_form["locais"] . "', ";	
		$isql .= "'" . addslashes(maiusculas($dados_form["txt_os"])) . "', ";
		$isql .= "'" . addslashes(maiusculas($dados_form["txt_locais"])) . "', ";		
		$isql .= "'" . $dados_form["categoria_contratacao"] . "', ";
		$isql .= "'" . addslashes(maiusculas($dados_form["txt_categoria"])) . "', ";
		$isql .= "'" . $dados_form["cargo"] . "', ";
		$isql .= "'" . $dados_form["qtde_vagas"] . "', ";
		$isql .= "'" . $dados_form["tempo_servico"] . "', ";
		$isql .= "'" . addslashes(maiusculas($dados_form["txt_infra"])) . "', ";
		$isql .= "'" . addslashes(maiusculas($dados_form["informacoes_ti"])) . "', ";
		$isql .= "'2', "; // status solicitado
		$isql .= "'" . $dados_form["experiencia"] . "', ";
		$isql .= "'" . $dados_form["aspectos_comportamentais"] . "', ";
		$isql .= "'" . $dados_form["reporte_direto"] . "', ";
		$isql .= "'" . $dados_form["nivel_atuacao"] . "', ";
		$isql .= "'" . $integracaoCliente . "', ";
		$isql .= "'" . $dados_form['mobilizacao_colaborador'] . "', ";
		$isql .= "'" . $dados_form['detalhes_mobilizacao'] . "') ";
	
		$db->insert($isql,'MYSQL');
		
		$id_requisicao = $db->insert_id;
		
		//Insere status com data e funcionario
		$isql = "INSERT INTO ".DATABASE.".requisicao_x_status ";
		$isql .= "(id_status_requisicao, id_requisicao, data_alteracao,id_funcionario) ";
		$isql .= "VALUES (";
		$isql .= "'2', "; //STATUS SOLICITADO
		$isql .= "'".$id_requisicao."', ";
		$isql .= "'".date("Y-m-d")."', ";
		$isql .= "'".$_SESSION["id_funcionario"]."') ";
		
		$db->insert($isql,'MYSQL');
		
		for($x=0;$x<count($dados_form["infra_ti"]);$x++)
		{
			$infra_str .= "('" . $dados_form["infra_ti"][$x] . "','" . $id_requisicao . "') ";
			
			if($x<count($dados_form["infra_ti"])-1)
			{
				$infra_str .= ", ";
			}
		}		

		//Verificando se deve ser enviado e-mail ao ti
		$emailTi = false;
		
		if(count($dados_form["infra_ti"])>0)
		{
			$isql = "INSERT INTO ".DATABASE.".infra_x_requisicao (id_infra, id_requisicao) VALUES";
			$isql .= $infra_str;
		
			$db->insert($isql,'MYSQL');
			
			$emailTi = true;
		}
	
		if(count($dados_form["niveis"])>0)
		{	
			$isql = "INSERT INTO ".DATABASE.".nivel_x_requisicao (id_nivel, id_requisicao) VALUES";
			$isql .= $nivel_str;
		
			$db->insert($isql,'MYSQL');
		}
	
		//Verifica por possíveis erros e manda as msgs de confirmação		
		if($id_requisicao > 0)
		{
			switch($dados_form["prazo"])
			{
				case 1:
					$prazo = "NORMAL: 15 a 30 dias.";
				break;
				
				case 2:
					$prazo = "URGENTE: 7 a 15 dias.";
				break;
				
				case 3:
					$prazo = "URGENTÍSSIMO: 3 a 7 dias.";
				break;
			}
			
			switch($dados_form["contrato"])
			{
				case 1:
					$contrato = "PJ";
				break;
				
				case 2:
					$contrato = "CLT";
				break;
			}
		
			$sql = "SELECT rh_funcoes.descricao FROM ".DATABASE.".rh_funcoes ";
			$sql .= "WHERE rh_funcoes.id_funcao = '" . $dados_form["cargo"] . "' ";
			$sql .= "AND reg_del = 0 ";
		
			$db->select($sql,'MYSQL',true);
	
			$reg_cargo = $db->array_select[0];
			
			//Manda e-mail com aviso a Direção
			$mensagem_rh = "<span style=\"font-family:Arial, Helvetica, sans-serif; font-size:11px; padding:0px; margin:0px;\"><p>Há uma nova requisição no sistema:</P><BR>";
			$mensagem_rh .= "<P>Nº: " . sprintf("%05d",$id_requisicao) . "</P>";
			$mensagem_rh .= "<P>Data: " . date("d/m/Y") . "</P>";
			$mensagem_rh .= "<P>Solicitante: " . $_SESSION["nome_usuario"] . "</P>";
			
			$mensagem_rh .= "<P>Prazo: " . $prazo . "</P>";
			
			$mensagem_rh .= "<P>Contrato: " . $contrato . "</P>";
			
			$mensagem_rh .= "<P>Função: " . $reg_cargo["descricao"] . "</P><BR>";
			
			$arrIntegracao = array('NÃO', 'SIM');
			$mensagem_rh .= "<P>Necessita de integração no cliente: " . $arrIntegracao[$integracaoCliente] . "</P><BR>";
			
			$mensagem_rh .= "<P>Para visualizar a requisição basta acessar <a href='http://ENDERECO/EMPRESA'>SISTEMA</a>, clicar no módulo Recursos Humanos / Requisição de Pessoal - ADM</P></span>";
	
			$sql = "SELECT * FROM ".DATABASE.".usuarios, ".DATABASE.".funcionarios ";
			$sql .= "WHERE funcionarios.id_funcionario = '".$_SESSION["id_funcionario"]."' ";
			$sql .= "AND funcionarios.id_usuario = usuarios.id_usuario ";
			$sql .= "AND funcionarios.reg_del = 0 ";
			$sql .= "AND usuarios.reg_del = 0 ";
			
			$db->select($sql,'MYSQL',true);
			
			$usuario_req = $db->array_select[0];
			
			$params = array();
			$params['emails']['to'][] = array('email' => "recrutamento@".DOMINIO, 'nome' => "RECURSOS HUMANOS");
			
			//Se vaga efetiva
			if($dados_form["tipo"]=="1")
			{
				$params['subject'] 	= "VAGA SOLICITADA - EFETIVA";

				$params['emails']['to'][] = array('email' => "diretoria@".DOMINIO, 'nome' => "DIRETORIA");
			}
			else
			{
				$params['subject'] 	= "VAGA SOLICITADA - PROPOSTA";
			}
			
			if ($emailTi)
			{
				$params['emails']['to'][] = array('email' => "ti@".DOMINIO, 'nome' => "Suporte TI");
			}
		
			if(ENVIA_EMAIL)
			{

				$mail = new email($params);
				$mail->montaCorpoEmail($mensagem_rh);
				
				if(!$mail->Send())
				{
					$resposta->addAlert("Erro ao enviar e-mail!!! ".$mail->ErrorInfo);
				}
			}
			else
			{
				$resposta->addScriptCall('modal', $mensagem_rh, '300_650', 'Conteúdo email', 3);
			}
				
			//Adicionado e-mail ao financeiro quando mobilizaçã
			//31/10/2016: Feito a pedido chamado 1019
			if ($dados_form['mobilizacao_colaborador'] == 0 && !empty($dados_form["detalhes_mobilizacao"]))
			{
				$mensagem_financeiro = "<span style=\"font-family:Arial, Helvetica, sans-serif; font-size:11px; padding:0px; margin:0px;\"><p>Há uma nova requisição no sistema:</P><BR>";
				$mensagem_financeiro .= "<P>Nº: " . sprintf("%05d",$id_requisicao) . "</P>";
				$mensagem_financeiro .= "<P>Data: " . date("d/m/Y") . "</P>";
				$mensagem_financeiro .= "<P>Solicitante: " . $_SESSION["nome_usuario"] . "</P>";
				$mensagem_financeiro .= "<P>Prazo: " . $prazo . "</P>";
				$mensagem_financeiro .= "<P>Contrato: " . $contrato . "</P>";
				$mensagem_financeiro .= "<P>Função: " . $reg_cargo["descricao"] . "</P><BR>";
				$mensagem_financeiro .= "<P><b>Detalhes Mobilização</b>: " . $dados_form["detalhes_mobilizacao"] . "</P><BR>";

				if(ENVIA_EMAIL)
				{
				
					$params = array();
					$params['from']	= $usuario_req["email"];
					$params['from_name'] = "SISTEMA REQUISIÇÃO DE PESSOAL - MOBILIZAÇÃO";
					
					$mail = new email($params, 'requisicao_mobilizacao_empresa');
				
					$params['subject'] 	= "MOBILIZAÇÃO SOLICITADA";
					
					$mail->montaCorpoEmail($mensagem_financeiro);
					
					if(!$mail->Send())
					{
						$resposta->addAlert("Erro ao enviar e-mail!!! ".$mail->ErrorInfo);
					}
				}
				else
				{
					$resposta->addScriptCall('modal', $mensagem_financeiro, '300_650', 'Conteúdo email', 4);
				}
			}
			
			$resposta->addScript("xajax_voltar();");
			
			$resposta->addAlert("Requisição de pessoal cadastrada com sucesso.");
			
			$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm')); ");
		}
		else
		{
			$resposta->addAlert("Ocorreu um erro ao tentar inserir os dados.");
		}
	}

	return $resposta;	
}

function selecionar_aspectos()
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados();
	
	$sql = "SELECT * FROM ".DATABASE.".requisicao_glossario ";
	$sql .= "WHERE requisicao_glossario.reg_del = 0 ";
	$sql .= "ORDER BY requisicao_glossario.tipo, requisicao_glossario.descricao ";
	
	$options = '';
	//Se precisar agrupar usar o comentario abaixo
	/*$arrTipos = array(
		1 => 'COMPETÊNCIAS TÉCNICAS',
		2 => 'HABILIDADES / COMPETÊNCIAS DE GESTÃO',
		3 => 'ATITUDES'
	);*/
	
	$db->select($sql, 'MYSQL', true);
	
	foreach($db->array_select as $reg)
	{
		$options .= "<span style='word-break: normal;float:left;border-bottom:solid 1px grey;' title='".$reg['significado']."'>".
						"<input style='float:left;' type='checkbox' name='chkAspectos[]' value='".$reg['id']."' id='chkAspectos' />".
						"<div style='float:left;width:545px;'>".$reg['descricao']."<br />".$reg['significado']."</div>".
					"</span><br />";		
	}
	
	$html = "<form id='frmAspectos' name='frmAspectos'><div class='caixa' style='width:98%; height:340px; overflow:auto;' id='divAspecto' name='divAspecto'>".$options."</div><br />";
	$html .= "<input type='button' onclick=xajax_adicionar_aspectos_selecionados(xajax.getFormValues('frmAspectos')); value='Atribuir Selecionados' style='width: 150px;' class='class_botao' /></form>";
	
	$resposta->addScriptCall('modal', $html, 'm', 'Selecione um ou mais aspectos (Passe o mouse para ver o significado)');
	
	return $resposta;
}

function adicionar_aspectos_selecionados($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados();
	
	$resposta->addAssign('aspectos_comportamentais', 'value', '');
	
	$ids = implode(',', $dados_form['chkAspectos']);
	
	$sql = "SELECT * FROM ".DATABASE.".requisicao_glossario ";
	$sql .= "WHERE requisicao_glossario.reg_del = 0 ";
	$sql .= "AND requisicao_glossario.id IN(".$ids.") ";
	$sql .= "ORDER BY requisicao_glossario.tipo, requisicao_glossario.descricao";
	
	$innerHTML = '';
	
	$sep = '';
	
	$db->select($sql, 'MYSQL',true);
	
	foreach($db->array_select as $reg)
	{
		$innerHTML .= $sep.$reg['descricao'].";";
		$sep = "\n";		
	}
	
	$resposta->addAssign('aspectos_comportamentais', 'value', $innerHTML);
	$resposta->addScript("divPopupInst.destroi(); ");
	
	return $resposta;
}

$xajax->registerFunction("atualizatabela");
$xajax->registerFunction("preenche_escolaridade");
$xajax->registerFunction("insere");
$xajax->registerFunction("excluir");
$xajax->registerFunction("selecionar_aspectos");
$xajax->registerFunction("adicionar_aspectos_selecionados");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","xajax_atualizatabela(xajax.getFormValues('frm'));");
?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script>
function grid(tabela, autoh, height, xml)
{	
	mygrid = new dhtmlXGridObject(tabela);
		
	mygrid.enableAutoHeight(autoh,height);
	mygrid.enableRowsHover(true,'cor_mouseover');

	mygrid.setHeader("Nº,status,Data Alteração,Solicitante,OS,D");
	mygrid.setInitWidths("50,100,100,*,*,50");
	mygrid.setColAlign("left,left,left,left,left,center");
	mygrid.setColTypes("ro,ro,ro,ro,ro,ro");
	mygrid.setColSorting("str,str,str,str,str,str");
	
	mygrid.setSkin("dhx_skyblue");
    mygrid.enableMultiselect(true);
    mygrid.enableCollSpan(true);	
	mygrid.init();
	mygrid.loadXMLString(xml);
}

function confirmaCancelamento(id_requisicao)
{
	var html = '<div id="div_aviso"><label class="labels">';
		html += 'A requisição será cancelada.<br />Explique o motivo do cancelamento: </label><br /> ';
		html += '<form id="frm_cancela" name="frm_cancela"><textarea cols="44" id="txt_motivo_cancelamento" name="txt_motivo_cancelamento" class="caixa" /></textarea><br /> ';
		html += '<input type="button" id="btn_cancelar" class="class_botao" name="btn_cancelar" value="Confirmar" ';
		html += ' onclick=if(confirm(\"Confirma o cancelamento da requisição?\")){xajax_excluir('+id_requisicao+',xajax.getFormValues("frm_cancela"))} /> ';
		html += '</form></div>';

	modal(html, 'pp', 'Cancelamento da requisição Nº '+id_requisicao);
}

function mostra_outro(acao, nome)
{
	switch(acao)
	{
		case 0:
		xajax.$('div_cmb'+nome).style.display = 'inline';
		xajax.$('div_txt'+nome).style.display = 'none';
		xajax.$('id_'+nome).selectedIndex = 0;
		xajax.$('id_'+nome).focus();
		
		break;
		
		case 1:
		xajax.$('div_cmb'+nome).style.display = 'none';
		xajax.$('div_txt'+nome).style.display = 'inline';
		xajax.$('txt_'+nome).focus();
		
		break;
	}
}

function liberarIntegracao(local)
{
	xajax.$('integracao_cliente').checked = false;
	xajax.$('integracao_cliente2').checked = false;
	
	if (local == 3)
	{
		xajax.$('integracao_cliente').disabled = true;
		xajax.$('integracao_cliente2').disabled = true;
	}
	else
	{
		xajax.$('integracao_cliente').disabled = false;
		xajax.$('integracao_cliente2').disabled = false;
	}
}
</script>

<?php
$conf = new configs();

//INFRAESTRUTURA TI
$sql = "SELECT * FROM ".DATABASE.".infra_estrutura ";
$sql .= "WHERE uso IN (1,2,3) ";
$sql .= "AND reg_del = 0 ";
$sql .= "ORDER BY infra_estrutura";

$array_infra_values = array();
$array_infra_output= array();

$array_softwares_values = array();
$array_softwares_output= array();

$db->select($sql,'MYSQL', true);

foreach($db->array_select as $reg)
{
	if (in_array($reg['uso'], array(1,2)))
	{
		$array_infra_values[] = $reg["id_infra_estrutura"];
		$array_infra_output[] = $reg["infra_estrutura"];
	}
	else
	{
		$array_softwares_values[] = $reg["id_infra_estrutura"];
		$array_softwares_output[] = $reg["infra_estrutura"];
	}	
}

$smarty->assign("option_softwares_values",$array_softwares_values);
$smarty->assign("option_softwares_output",$array_softwares_output);

$smarty->assign("option_infra_values",$array_infra_values);
$smarty->assign("option_infra_output",$array_infra_output);

$array_os_values = NULL;
$array_os_output = NULL;

//Popula a combo-box de OS
$sql = "SELECT ordem_servico.id_os, ordem_servico.os, ordem_servico.descricao FROM ".DATABASE.".ordem_servico, ".DATABASE.".ordem_servico_status ";
$sql .= "WHERE ordem_servico.id_os_status = ordem_servico_status.id_os_status ";
$sql .= "AND ordem_servico.reg_del = 0 ";
$sql .= "AND ordem_servico_status.reg_del = 0 ";
$sql .= "AND ordem_servico_status.id_os_status IN (1,14,16) ";
$sql .= "ORDER BY ordem_servico.os ";

$db->select($sql,'MYSQL',true);

foreach ($db->array_select as $reg_os)
{
	$array_os_values[] = $reg_os["id_os"];
	$array_os_output[] = $reg_os["os"].' - '.$reg_os["descricao"];
}

$smarty->assign("option_os_values",$array_os_values);
$smarty->assign("option_os_output",$array_os_output);

$array_locais_values = NULL;
$array_locais_output = NULL;

$sql = "SELECT * FROM ".DATABASE.".local ";
$sql .= "WHERE reg_del = 0 ";
$sql .= "ORDER BY descricao ";

$db->select($sql,'MYSQL',true);

foreach ($db->array_select as $regs)
{
	$array_locais_values[] = $regs["id_local"];
	$array_locais_output[] = $regs["descricao"];
}

$smarty->assign("option_locais_values",$array_locais_values);
$smarty->assign("option_locais_output",$array_locais_output);

$array_cargos_values = NULL;
$array_cargos_output = NULL;

$sql = "SELECT rh_funcoes.id_funcao, rh_funcoes.descricao, id_cargo_grupo FROM ".DATABASE.".rh_funcoes ";
$sql .= "WHERE reg_del = 0 ";
$sql .= "ORDER BY rh_funcoes.descricao ";

$db->select($sql,'MYSQL',true);

foreach($db->array_select as $reg_cargo)
{
	$array_cargos_values[] = $reg_cargo["id_funcao"].','.$reg_cargo["id_cargo_grupo"];
	$array_cargos_output[] = substr($reg_cargo["descricao"],0,40);
}

$smarty->assign("option_cargos_values",$array_cargos_values);
$smarty->assign("option_cargos_output",$array_cargos_output);

$array_escolaridade_values[] = "";
$array_escolaridade_output[] = "SELECIONE";

$sql = "SELECT * FROM ".DATABASE.".rh_escolaridade  ";
$sql .= "WHERE reg_del = 0 ";
$sql .= "ORDER BY rh_escolaridade.escolaridade ";

$db->select($sql,'MYSQL',true);

foreach ($db->array_select as $cont)
{
	$array_escolaridade_values[] = $cont["id_rh_escolaridade"];
	$array_escolaridade_output[] = $cont["escolaridade"];
}

$smarty->assign("option_escolaridade_values",$array_escolaridade_values);
$smarty->assign("option_escolaridade_output",$array_escolaridade_output);

$array_filtro_values[] = "-1";
$array_filtro_output[] = "TODAS";

$sql = "SELECT * FROM ".DATABASE.".status_requisicao  ";
$sql .= "WHERE reg_del = 0 ";

$db->select($sql,'MYSQL',true);

foreach ($db->array_select as $cont)
{
	$array_filtro_values[] = $cont["id_status_requisicao"];
	$array_filtro_output[] = $cont["status"];
}

$smarty->assign("option_filtro_values",$array_filtro_values);
$smarty->assign("option_filtro_output",$array_filtro_output);

$smarty->assign('campo', $conf->campos('requisicao_pessoal'));

$smarty->assign('revisao_documento', 'V9');

$smarty->assign("classe",CSS_FILE);

$smarty->display('requisicao_pessoal.tpl');

?>