<?php
/*
	Formulário de Controle de Aprovados
	
	Criado por Carlos Eduardo Máximo
		
	Versão 0 --> VERSÃO INICIAL : 08/04/2016
	Versão 1 --> Inclusão dos campos reg_del nas consultas - 28/11/2017 - Carlos Abreu
	Versão 3 --> Layout responsivo - 05/02/2018 - Carlos Eduardo
*/	

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

require_once(INCLUDE_DIR."encryption.inc.php");

require_once(INCLUDE_DIR."antiInjection.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(554))
{
	nao_permitido();
}

function atualizatabela($sql_texto)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	$arrays = array(
		'status' => array(
			'1' => 'AGUARD. PREENCH.',
			'2' => 'EM PREENCH.',
			'3' => 'PREENC. CONCLUÍDO',
			'4' => 'IMPORTADO',
		),
		'mod_contrato' => array(
			"CLT" => 'CLT',
			"EST" => 'ESTAGIÁRIO',
			"SC" => 'SOCIEDADE CIVIL (HORISTA)',
			"SC+CLT" => 'SOCIEDADE CIVIL + CLT',
			"SC+MENS" => 'SOCIEDADE CIVIL (MENSALISTA)',
			"SC+CLT+MENS" => 'SOCIEDADE CIVIL + CLT (MENSALISTA)',
			"SOCIO" => 'SÓCIO'
		),
	);
	
	$sql_filtro = '';

	if(!empty($sql_texto))
	{
	    $sql_texto = str_replace('  ', ' ', AntiInjection::clean($sql_texto));
	    
	    $sql_texto = str_replace(' ', '%', '%'.$sql_texto.'%');
	    
	    $sql_filtro = "AND (nome LIKE '".$sql_texto."' ";
	    $sql_filtro .= "OR email LIKE '".$sql_texto."' ";
	    $sql_filtro .= "OR cpf LIKE '".$sql_texto."' ";
	    $sql_filtro .= "OR mod_contrato LIKE '".$sql_texto."' ";
	    $sql_filtro .= "OR Descr LIKE '".$sql_texto."' ";
	    $sql_filtro .= "OR cdp_cidade LIKE '".$sql_texto."' ";
	    $sql_filtro .= "OR cdvm_email LIKE '".$sql_texto."') ";
	}
	
	$sql = "SELECT
				*
			FROM
				".DATABASE.".candidatos
				JOIN(
					SELECT
					  id_requisicao, CASE WHEN OS IS NULL THEN os_outros ELSE OS END OS, Descr
					FROM
					  ".DATABASE.".requisicoes_pessoal
					LEFT JOIN
					  (SELECT id_os as idOs, OS, descricao as Descr FROM ".DATABASE.".OS WHERE OS.reg_del = 0) OS
					  ON id_os = idOs
					WHERE ultimo_status IN(2,3,5,9,10) AND reg_del = 0
				) requisicao
				ON id_requisicao = id_req_vaga
				LEFT JOIN(
					SELECT * FROM ".DATABASE.".candidatos_dados_pessoais WHERE candidatos_dados_pessoais.reg_del = 0
				) dp
				ON cdp_candidato_id = id
				LEFT JOIN(
					SELECT * FROM ".DATABASE.".candidatos_documentos WHERE candidatos_documentos.reg_del = 0
				) cd
				ON cd_candidato_id = id
				LEFT JOIN(
					SELECT * FROM ".DATABASE.".candidatos_formacao WHERE candidatos_formacao.reg_del = 0
				) cf
				ON cf_candidato_id = id
				LEFT JOIN(
					SELECT * FROM ".DATABASE.".candidatos_emprego_anterior WHERE candidatos_emprego_anterior.reg_del = 0
				) cea
				ON cea_candidato_id = id
				LEFT JOIN(
					SELECT * FROM ".DATABASE.".candidatos_informacoes_adicionais WHERE candidatos_informacoes_adicionais.reg_del = 0
				) cia
				ON cia_candidato_id = id
				LEFT JOIN(
					SELECT * FROM ".DATABASE.".candidatos_epi WHERE candidatos_epi.reg_del = 0
				) ce
				ON ce_candidato_id = id
				LEFT JOIN(
					SELECT * FROM ".DATABASE.".candidatos_interno WHERE candidatos_interno.reg_del = 0
				) cdvm
				ON cdvm_candidato_id = id
				LEFT JOIN (
					SELECT MAX(id_funcionario) id_funcionario, cpf as funcCpf,
					(SELECT situacao FROM ".DATABASE.".funcionarios b WHERE b.reg_del = 0 AND b.id_funcionario = MAX(a.id_funcionario)) situacao
					FROM 
						".DATABASE.".funcionarios a
					WHERE cpf <> '' GROUP BY cpf
				) funcionarios
				ON funcCpf = cd_cpf
			WHERE
				candidatos.reg_del = 0 ".$sql_filtro."
	ORDER BY id DESC ";
	
	$xml = new XMLWriter();
	$xml->setIndent(false);
	$xml->openMemory();
	$xml->startElement('rows');
	
	$db->select($sql, 'MYSQL',true);
	
	foreach($db->array_select as $reg)
	{
		$reg['status'] = !empty($reg['id_funcionario']) && $reg['situacao'] == 'ATIVO' ? 4 : $reg['status'];
		$xml->startElement('row');
		
		//Chamado #1883 de Sara Silva
		if (!empty($reg['id_funcionario']) && $reg['situacao'] <> 'ATIVO')
		{
			$xml->writeAttribute('style', 'color: #888');
		}
		
		$reg['OS'] = str_replace("'", "`", $reg['OS']);
		
		$descr = $reg['id_requisicao'].' - '.$reg['OS'].' - '.$reg['Descr'];
		
		$xml->writeAttribute('id', $reg["rash"]);
		$xml->writeElement('cell', $reg["id"]);
		$xml->writeElement('cell', $reg["nome"]);
		$xml->writeElement('cell', $reg["salario_pretendido"]);
		$xml->writeElement('cell', $descr);
		$xml->writeElement('cell', $arrays['mod_contrato'][$reg["mod_contrato"]]);
		$xml->writeElement('cell', $reg["nivel_atuacao"]);
		$xml->writeElement('cell', $arrays['status'][$reg["status"]]);
		if ($reg['status'] == 3 && (empty($reg['id_funcionario']) || $reg['situacao'] <> 'ATIVO'))
			$xml->writeElement('cell', '<span class="icone icone-salvar cursor" onclick=if(confirm("ATENÇÃO:&nbsp;Deseja&nbsp;realmente&nbsp;exportar&nbsp;este&nbsp;cadastro?&nbsp;Só&nbsp;confirme&nbsp;caso&nbsp;o&nbsp;cadastro&nbsp;esteja&nbsp;completo.")){xajax_exportar("'.$reg["id"].'");}></span>');
		else
			$xml->writeElement('cell', '&nbsp;');

		if (empty($reg['id_funcionario']))
			$xml->writeElement('cell', '<span class="icone icone-excluir cursor" onclick=if(confirm("Confirma&nbsp;a&nbsp;exclusão?")){xajax_excluir("'.$reg["id"].'");}></span>');
		else
			$xml->writeElement('cell', '&nbsp;');
			
		$xml->writeElement('cell', "<span class=\'icone icone-arquivo-pdf cursor\' onclick=window.open(\'relatorios/ficha_candidato_pdf.php?idCandidato=".$reg['id']."\',\'_blank\',\'width=1024,height=500\');></span>");
		
		if (empty($reg['id_funcionario']) || $reg['situacao'] <> 'ATIVO')
			$xml->writeElement('cell', '<span class="icone icone-editar cursor" onclick=xajax_editar("'.$reg['id'].'");></span>');
		else
			$xml->writeElement('cell', '&nbsp;');

		if ($reg['status'] == 3 && (empty($reg['id_funcionario']) || $reg['situacao'] <> 'ATIVO'))
			$xml->writeElement('cell', '<span class="icone icone-cadeado-aberto cursor" onclick=if(confirm("ATENÇÃO:&nbsp;Deseja&nbsp;realmente&nbsp;liberar&nbsp;este&nbsp;cadastro?")){xajax_liberar("'.$reg["id"].'");}></span>');
		else
			$xml->writeElement('cell', '&nbsp;');	
		
		$xml->endElement();		
	}
	
	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('div_candidatos', true, '320', '".$conteudo."');");
	$resposta->addScript("hideLoader()");

	return $resposta;
}

function alterar($dados_form)
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();
	
	$emailEnvio = AntiInjection::clean(maiusculas($dados_form['email']));
	
	$idCandidato= !empty($dados_form['id_candidato']) ? AntiInjection::clean(maiusculas($dados_form['id_candidato'])) : '';
	$nome 		= !empty($dados_form['nome']) ? AntiInjection::clean(maiusculas($dados_form['nome'])) : '';
	$email 		= !empty($dados_form['email']) ? AntiInjection::clean(maiusculas($dados_form['email'])) : '';
	$cpf 		= !empty($dados_form['cpf']) ? AntiInjection::formatarGenerico(AntiInjection::clean($dados_form['cpf']), '###.###.###-##') : '';
	$sal 		= str_replace('R$ ', '',  str_replace(',','.', str_replace('.', '', $dados_form['salario'])));
	$tpSalario  = isset($dados_form['rdoTpSalario']) && !empty($dados_form['rdoTpSalario']) ? $dados_form['rdoTpSalario'] : '';
	$nivelAtu 	= !empty($dados_form['nivel_atuacao']) ? $dados_form['nivel_atuacao'] : '';
	$modCont 	= !empty($dados_form['tipo_contrato']) ? $dados_form['tipo_contrato'] : '';
	$idVaga 	= !empty($dados_form['id_requisicao']) ? $dados_form['id_requisicao'] : '';
	$idCargo 	= !empty($dados_form['cargo_pretendido']) ? $dados_form['cargo_pretendido'] : '';
	$dataInicio	= !empty($dados_form['data_inicio']) ? php_mysql($dados_form['data_inicio']) : '';
	$centroCusto= !empty($dados_form['centro_custo']) ? $dados_form['centro_custo'] : '';
	$setorAso	= !empty($dados_form['setor_aso']) ? $dados_form['setor_aso'] : '';
	
	if (!empty($idCandidato) && !empty($nome) && !empty($cpf) && !empty($sal) && !empty($nivelAtu) && !empty($modCont) && !empty($modCont) && !empty($dataInicio) && !empty($centroCusto) && !empty($idVaga) && !empty($tpSalario) && !empty($setorAso))
	{
		$sql = "SELECT id_req_vaga FROM ".DATABASE.".candidatos ";
		$sql .= "WHERE candidatos.id = ".$idCandidato." ";
		$sql .= "AND candidatos.reg_del = 0 ";
		
		$db->select($sql, 'MYSQL',true);
		
		$vagaAntiga = $db->array_select[0];
		
		$rash = md5($cpf.$email);
		
		$usql  = "UPDATE ".DATABASE.".candidatos SET
					nome = '".$nome."',
					email = '".$email."',
					cpf = '".$cpf."',
					status = 1,
					rash = '".$rash."',
					cargo_pretendido = '".$idCargo."',
					salario_pretendido = '".$sal."',
					id_req_vaga = '".$idVaga."',
					mod_contrato = '".$modCont."',
					nivel_atuacao = '".$nivelAtu."',
					data_inicio = '".$dataInicio."',
					centro_custo = '".$centroCusto."',
					tipo_salario = '".$tpSalario."',
					setor_aso = '".$setorAso."'
				  WHERE
				  	id = ".$idCandidato;

		$db->update($usql, 'MYSQL');
		
		if ($db->erro != '')
		{
			$resposta->addAlert('Houve uma falha ao tentar alterar os dados');
		}
		else
		{

			$usql = "UPDATE ".DATABASE.".rh_candidatos SET ";
			$usql .= "reg_del = 1, ";
			$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
			$usql .= "data_del = '".date('Y-m-d')."' ";
			$usql .= "WHERE id_requisicao = ".$vagaAntiga['id_req_vaga']." ";
			$usql .= "AND nome = '".$nome."' ";
			
			$db->update($usql, 'MYSQL');
			
			if ($db->erro != '')
			{
				$resposta->addAlert('Houve uma falha ao tentar inserir o candidato na VAGA');
			}
			else
			{
				$isql = "INSERT INTO
							".DATABASE.".rh_candidatos (id_requisicao, nome, valor, enviado_coordenacao, aprovacao, aprovacao_financeiro)
						 VALUES (
						 	'".$idVaga."', '".$nome."', '".$sal."', '1',1,1) ";
	
				$db->insert($isql, 'MYSQL');
			
				if ($db->erro != '')
				{
					$resposta->addAlert('Houve uma falha ao tentar inserir o candidato na VAGA');
				}
				else
				{
					$resposta->addAlert('Dados Alterados corretamente!');
					$resposta->addScript('xajax_atualizatabela();');
					$resposta->addScript('xajax_voltar();');
				}
			}
		}
	}
	else
	{
		$resposta->addAlert('ATENÇÃO: Preencha todos os campos do formulário');
	}
		
	return $resposta;
}

function insere($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados();
	
	$emailEnvio = AntiInjection::clean(maiusculas($dados_form['email']));
	
	$nome 		= !empty($dados_form['nome']) ? AntiInjection::clean(maiusculas($dados_form['nome'])) : '';
	$email 		= !empty($dados_form['email']) ? AntiInjection::clean(maiusculas($dados_form['email'])) : '';
	$cpf 		= !empty($dados_form['cpf']) ? AntiInjection::formatarGenerico(AntiInjection::clean($dados_form['cpf']), '###.###.###-##') : '';
	$sal 		= str_replace('R$ ', '',  str_replace(',','.', str_replace('.', '', $dados_form['salario'])));
	$tpSalario  = isset($dados_form['rdoTpSalario']) && !empty($dados_form['rdoTpSalario']) ? $dados_form['rdoTpSalario'] : '';
	$nivelAtu 	= !empty($dados_form['nivel_atuacao']) ? $dados_form['nivel_atuacao'] : '';
	$modCont 	= !empty($dados_form['tipo_contrato']) ? $dados_form['tipo_contrato'] : '';
	$idVaga 	= !empty($dados_form['id_requisicao']) ? $dados_form['id_requisicao'] : '';
	$idCargo 	= !empty($dados_form['cargo_pretendido']) ? $dados_form['cargo_pretendido'] : '';
	$dataInicio	= !empty($dados_form['data_inicio']) ? php_mysql($dados_form['data_inicio']) : '';
	$centroCusto= !empty($dados_form['centro_custo']) ? $dados_form['centro_custo'] : '';
	$setorAso	= !empty($dados_form['setor_aso']) ? $dados_form['setor_aso'] : '';
	
	if (!empty($nome) && !empty($cpf) && !empty($sal) && !empty($nivelAtu) && !empty($modCont) && !empty($modCont) && !empty($dataInicio) && !empty($centroCusto) && !empty($idVaga) && !empty($tpSalario) && !empty($setorAso))
	{
		$sql = "SELECT email, cpf FROM ".DATABASE.".candidatos ";
		$sql .= "WHERE (email = '".$email."' OR cpf = '".$cpf."') ";
		$sql .= "AND candidatos.reg_del = 0 ";
		
		$db->select($sql, 'MYSQL', true);
		
		if ($db->numero_registros == 0)
		{
			$rash = $rash = md5($cpf.$email);
			$isql  = 'INSERT INTO '.DATABASE.'.candidatos (nome, email, cpf, status, rash, cargo_pretendido, salario_pretendido, tipo_salario, id_req_vaga, mod_contrato, nivel_atuacao, data_inicio, centro_custo, setor_aso) VALUES ';
			$isql .= "('".$nome."', '".$email."', '".$cpf."', 1, '".$rash."', '".$idCargo."', '".$sal."', '".$tpSalario."','".$idVaga."', '".$modCont."', '".$nivelAtu."', '".$dataInicio."', '".$centroCusto."', '".$setor."')";
	
			$db->insert($isql, 'MYSQL');
			
			if ($db->erro != '')
			{
				$resposta->addAlert('Houve uma falha ao tentar inserir o acesso do candidato');
			}
			else
			{
				$isql = "INSERT INTO
							".DATABASE.".rh_candidatos (id_requisicao, nome, valor, enviado_coordenacao, aprovacao, aprovacao_financeiro)
						 VALUES (
						 	'".$idVaga."', '".$nome."', '".$sal."', '1',1,1) ";

				$db->insert($isql, 'MYSQL');
			
				if ($db->erro != '')
				{
					$resposta->addAlert('Houve uma falha ao tentar inserir o candidato na VAGA');
				}
				else
				{
					$resposta->addScript("xajax_enviarEmail('".$emailEnvio."','".$cpf."','".$nome."','".$rash."');");
				}
			}
		}
		else
		{
			$mensagem = $db->array_select[0]['email'] == $email ? 'e-mail' : 'CPF';
			$resposta->addAlert("ATENÇÃO: Já existe este ".$mensagem." em nosso sistema");
			$resposta->addScript('limparForm();');
			$resposta->addScript('xajax_atualizatabela();');
		}
	}
	else
	{
		$resposta->addAlert('ATENÇÃO: Preencha todos os campos do formulário');
	}
		
	return $resposta;
}

function enviarEmail($email,$cpf,$nome,$rash)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados();
	
	$sql = "SELECT
			  *
			FROM
			".DATABASE.".candidatos
			  JOIN(
			    SELECT CONVERT(CONCAT(id_funcao,',',id_cargo_grupo) USING utf8) as cargo_id, descricao FROM ".DATABASE.".rh_funcoes WHERE rh_funcoes.reg_del = 0
			  ) cargo
			  ON cargo_id = cargo_pretendido
			  JOIN(
			    SELECT
			      id_requisicao, tipo_contrato, localTrab
			    FROM
			      ".DATABASE.".requisicoes_pessoal
			      LEFT JOIN(
			        SELECT id_local, descricao as localTrab FROM ".DATABASE.".local WHERE local.reg_del = 0 
			      ) localTrab
			      ON id_local = id_local
			  ) req_pes
			  ON id_requisicao = id_req_vaga
			  WHERE
			    candidatos.reg_del = 0
			  AND
			  	candidatos.cpf = '".$cpf."' ";
	
	$db->select($sql, 'MYSQL', true);
	
	$params 			= array();
	$params['from']		= "recrutamento@dominio.com.br";
	$params['from_name']= "RECURSOS HUMANOS";
	$params['subject'] 	= "Cadastro de candidato aprovado (".$nome.")";

	$params['emails']['to'][] = array('email' => $email, 'nome' => $nome);

	$link = "www.empresa.com.br/dir_web/rh/cadastro_aprovados.php?rsh=".$rash;
	
	$arrContratacao = array(
		'CLT' => 'CLT',
		'EST' => 'ESTAGIÁRIO',
		'SC' => 'PJ',
		'SC+CLT' => 'PJ + CLT',
		'SC+MENS' => 'PJ',
		'SC+CLT+MENS' => 'PJ + CLT',
		'SOCIO' => 'SÓCIO'
	);
	
	$tpContratacao = in_array(trim($db->array_select[0]['mod_contrato']), array('CLT')) ? 'CLT' : 'PJ';
	
	$corpo = "Seja bem vindo!<br /><br />";
	$corpo .= "Parabéns por sua Aprovação em nosso processo seletivo, desejamos muito sucesso nessa etapa profissional que se inicia.<br />";
	$corpo .= "Abaixo segue as informações detalhadas sobre a contratação:<br /><br />";
	$corpo .= "Posição: ".$db->array_select[0]['descricao'].'<br />';
	$corpo .= "Tipo Contratação: ".$tpContratacao.'<br />';
	$corpo .= "Remuneração: ".number_format($db->array_select[0]['salario_pretendido'], 2, ',', '.').'<br />';
	$corpo .= "Oferecemos seguro de vida<br />";
	$corpo .= "Local de trabalho: ".$db->array_select[0]['localTrab'].'<br /><br />';
	
	if (!in_array($arrContratacao[$tpContratacao], array('CLT','ESTAGIÁRIO','SÓCIO')))
	{
		$corpo = '';
	}
	
	$corpo .= "Para continuarmos o processo, por favor, preencha o cadastro no link abaixo:<br />";
	$corpo .= $link.'<br /><br />'; 
	$corpo .= "Nosso Endereço: Rua XXXXXXXX, XX - Centro - XXXXXXX. CEP: XXXXXXXX <br /><br />";
	$corpo .= "Ficamos a disposição para maiores esclarecimentos e informações!<br />Certo de sua atenção!";
	
	$mail = new email($params);
	$mail->montaCorpoEmail($corpo);

	if(!$mail->Send())
	{
		$resposta->addAlert('ATENÇÃO: O Acesso foi cadastrado corretamente, mas o e-mail não foi enviado por algum motivo desconhecido. Por favor, entre em contato com o Candidato');
	}
	else
	{
		$resposta->addAlert('Acesso cadastrado corretamente');
	}
	
	$resposta->addScript('limparForm();');
	$resposta->addScript('xajax_atualizatabela();');

	return $resposta;
}

function excluir($id)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados();
	
	$usql = "UPDATE ".DATABASE.".candidatos SET ";
	$usql .= "reg_del = 1, ";
	$usql .= "data_del = '".date('Y-m-d')."' ";
	$usql .= "WHERE id = ".$id;
	
	$db->update($usql, 'MYSQL');
	
	if ($db->erro != '')
		$resposta->addAlert('Houve uma falha ao tentar excluir o candidato! Por favor, atualize a tela e verifique se a exclusão ocorreu!');
	else
	{
		$resposta->addAlert('Candidato excluído corretamente!');
		$resposta->addScript('xajax_atualizatabela();');
	}
	
	return $resposta;
}

function editar($id)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados();
	
	$sql = "SELECT
				*
			FROM
				".DATABASE.".candidatos
				LEFT JOIN(
					SELECT * FROM ".DATABASE.".candidatos_documentos WHERE reg_del = 0 AND cd_candidato_id = '".$id."'
				)docs
				ON cd_candidato_id = id
			WHERE
				candidatos.reg_del = 0
				AND id = '".$id."' ";
	
	$db->select($sql, 'MYSQL',true);

	$reg = $db->array_select[0];
	
	$cpf = trim($reg['cd_cpf']) != '' ? $reg['cd_cpf'] : $reg['cpf'];
	
	$resposta->addAssign('id_candidato', 'value', $reg['id']);
	$resposta->addAssign('nome', 'value', $reg['nome']);
	$resposta->addAssign('email', 'value', $reg['email']);
	$resposta->addAssign('cpf', 'value', $cpf);
	$resposta->addAssign('id_requisicao', 'value', $reg['id_req_vaga']);
	$resposta->addAssign('nivel_atuacao', 'value', $reg['nivel_atuacao']);
	$resposta->addAssign('salario', 'value', str_replace('.', ',', $reg['salario_pretendido']));
	$resposta->addAssign('cargo_pretendido', 'value', $reg['cargo_pretendido']);
	$resposta->addAssign('tipo_contrato', 'value', $reg['mod_contrato']);
	$resposta->addScript('document.getElementById("rdoTpSalario'.strtoupper($reg['tipo_salario']).'").checked="checked"');
	$resposta->addAssign('centro_custo', 'value', $reg['centro_custo']);
	$resposta->addAssign('setor_aso', 'value', $reg['setor_aso']);
	$resposta->addAssign('data_inicio', 'value', mysql_php($reg['data_inicio']));	
	
	$resposta->addEvent("btninserir", "onclick", "xajax_alterar(xajax.getFormValues('frm')); ");
	
	return $resposta;
}

function voltar($dados_form)
{
	$resposta = new xajaxResponse();
	
	$resposta->addScript("xajax.$('frm').reset(); ");
	
	$resposta->addEvent("btninserir","onclick","xajax_insere(xajax.getFormValues('frm')); ");
	
	$resposta->addEvent("btnvoltar", "onclick", "history.back();");
	
	return $resposta;
}

function exportar($idCandidato)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados();
	
	$sql = "SELECT
				*
			FROM
				".DATABASE.".candidatos
		        JOIN (
		          SELECT * FROM ".DATABASE.".requisicoes_pessoal WHERE reg_del = 0 AND ultimo_status IN(2,5,10)
		        ) req
		        ON req.id_requisicao = id_req_vaga
				LEFT JOIN(
					SELECT * FROM ".DATABASE.".candidatos_dados_pessoais WHERE candidatos_dados_pessoais.reg_del = 0
				) dp
				ON cdp_candidato_id = id
				LEFT JOIN(
					SELECT * FROM ".DATABASE.".candidatos_documentos WHERE candidatos_documentos.reg_del = 0
				) cd
				ON cd_candidato_id = id
				LEFT JOIN(
					SELECT * FROM ".DATABASE.".candidatos_emprego_anterior WHERE candidatos_emprego_anterior.reg_del = 0
				) cea
				ON cea_candidato_id = id
				LEFT JOIN(
					SELECT * FROM ".DATABASE.".candidatos_informacoes_adicionais WHERE candidatos_informacoes_adicionais.reg_del = 0
				) cia
				ON cia_candidato_id = id
				LEFT JOIN(
					SELECT * FROM ".DATABASE.".candidatos_epi WHERE candidatos_epi.reg_del = 0
				) ce
				ON ce_candidato_id = id
				LEFT JOIN(
					SELECT * FROM ".DATABASE.".candidatos_interno WHERE candidatos_interno.reg_del = 0
				) cdvm
				ON cdvm_candidato_id = id
			WHERE
				candidatos.reg_del = 0
				AND id = '".$idCandidato."' ";
						
	$db->select($sql, 'MYSQL',true);
	
	$reg = $db->array_select[0];
	
	$cargo = explode(',', $reg["cargo_pretendido"]);
	
	$email	= $reg['cdvm_email'];
	$login	= $reg['cdvm_login'];
	
	//Chamado Nº585
	//Colaboradores externos não terão sigla
	$sigla	= in_array($reg['id_local'], array(3,9,53)) ? $reg['cdvm_sigla'] : '';
	
	//Verificando se já existe email, login ou sigla no cadastro de funcionarios
	$sql = 
	"SELECT UPPER(sigla_func) sigla_func, UPPER(email) email, UPPER(Login) Login
	FROM
		".DATABASE.".funcionarios
		JOIN(
			SELECT id_funcionario as id_funcionario, email, login FROM ".DATABASE.".usuarios WHERE usuarios.reg_del = 0
		) usuarios
		ON id_funcionario = id_funcionario
	WHERE
		funcionarios.reg_del = 0
		AND situacao <> 'DESLIGADO'
		AND (
			UPPER(email) = '".$email."'
			OR UPPER(login) = '".$login."'
			OR (UPPER(sigla_func) = '".$sigla."' AND sigla_func <> '')
		)";
	
	$db->select($sql, 'MYSQL', true);
	
	if ($db->numero_registros > 0)
	{
		$mensagem = '';
		if ($db->array_select[0]['email'] == $email)
			$mensagem .= 'E-mail: '.$db->array_select[0]['email']." - ";
			
		if ($db->array_select[0]['Login'] == $login)
			$mensagem .= 'Login: '.$db->array_select[0]['login']." - ";
			
		if ($db->array_select[0]['sigla_func'] == $sigla)
			$mensagem .= 'Sigla: '.$db->array_select[0]['sigla_func'];
			
		$resposta->addAlert('ATENÇÃO: ALTERE as Informações já existentes: '.$mensagem);
		return $resposta;
	}		
	
	//Insere o funcionario no banco Devemada
	$isql = "INSERT INTO ".DATABASE.".funcionarios ";
	$isql .= "(id_setor, nivel_atuacao, id_funcao, id_cargo, id_setor_aso, funcionario, nome_usuario, email_particular,
	funcionario_endereco, funcionario_bairro, funcionario_cidade, ";
	$isql .= "funcionario_cep, funcionario_estado, filiacao_pai, filiacao_mae, nacionalidade_pai, ";
	$isql .= "nacionalidade_mae, ctps_num, ctps_serie, reservista_num, reservista_categoria, titulo_eleitor, titulo_zona, titulo_secao, identidade_num, ";
	$isql .= "identidade_emissor, data_emissao, cpf, naturalidade, id_nacionalidade, estado_nascimento, data_nascimento, id_empfunc, ";
	$isql .= "id_estado_civil, conjuge, id_escolaridade, clt_matricula, clt_admissao, id_categoria_funcional, id_tipo_pagamento, pis_data,
	pis_num, pis_banco, ";
	$isql .= "fgts_data, fgts_conta, fgts_banco, fgts_agencia, id_vinculo_empregaticio, id_tipo_admissao, id_turno_trabalho, ";
	$isql .= "horario_entrada, refeicao, horario_saida, descanso_semanal, cor, sexo, tipo_sanguineo, cabelo, olhos, altura, peso, id_local, ";
	$isql .= "celular, telefone, data_inicio, id_centro_custo, id_produto, item_contabil, id_cod_fornec, tipo_empresa, situacao, sigla_func, ref_transp_outros) ";
	$isql .= "VALUES (";
	$isql .= "'', ";//codigo setor
	$isql .= "'".$reg['nivel_atuacao']."', ";
	$isql .= "'".$cargo[0]."', ";
	$isql .= "'".$cargo[1]."', ";
	$isql .= "'".$reg["setor_aso"]."', ";//id setor aso
	$isql .= "'".$reg["nome"]."', ";
	$isql .= "'".$reg['cdvm_login']."', ";//nome de usuário
	$isql .= "'".$reg["email"]."', ";
	$isql .= "'".$reg["cdp_endereco"]."', ";
	$isql .= "'".$reg["cdp_bairro"]."', ";
	$isql .= "'".$reg["cdp_cidade"]."', ";
	$isql .= "'".$reg["cdp_cep"]."', ";
	$isql .= "'".$reg["cdp_uf"]."', ";
	$isql .= "'".$reg["cdp_nome_pai"]."', ";
	$isql .= "'".$reg["cdp_nome_mae"]."', ";
	$isql .= "'', ";//nacionalidade pai
	$isql .= "'', ";//nacionalidade mae
	$isql .= "'".$reg["cd_ctps"]."', ";
	$isql .= "'".$reg["cd_ctps_serie"]."', ";
	$isql .= "'".$reg["cd_reservista"]."', ";
	$isql .= "'".$reg["cd_reservista_serie"]."', ";
	$isql .= "'".$reg["cd_titulo_eleitor"]."', ";
	$isql .= "'".$reg["cd_titulo_zona"]."', ";
	$isql .= "'".$reg["cd_titulo_secao"]."', ";
	$isql .= "'".$reg["cd_rg"]."', ";
	$isql .= "'".$reg["cd_rg_orgao"]."', ";
	$isql .= "'".$reg["cd_rg_emissao"]."', ";
	$isql .= "'".$reg["cd_cpf"]."', ";
	$isql .= "'".$reg["cdp_naturalidade"]."', ";
	$isql .= "'".$reg["cdp_nacionalidade"]."', ";
	$isql .= "'".$reg["cdp_uf_nasc"]."', ";
	$isql .= "'".$reg["cdp_data_nasc"]."', ";
	$isql .= "'', ";//id empresa funcionario
	$isql .= "'".$reg["cdp_est_civil"]."', ";
	$isql .= "'".$reg["cdp_nome_conjuge"]."', ";
	$isql .= "'', ";//Escolaridade
	$isql .= "'', ";//clt matricula
	$isql .= "'".$reg['data_inicio']."', ";//clt admissão
	$isql .= "'M', ";//id categoria funcional
	$isql .= "'M', ";//id tipo pagamento
	$isql .= "'', ";//pis_data
	$isql .= "'".$reg["cd_pis"]."', ";
	$isql .= "'', ";//pis banco
	$isql .= "'', ";//fgts data
	$isql .= "'', ";//fgts conta
	$isql .= "'', ";//fgts banco
	$isql .= "'', ";//fgts agencia
	$isql .= "'10', ";//id vinculo empregatício
	$isql .= "'9B', ";//Outros / Reemprego
	$isql .= "'001', ";//08:00 - 17:00
	$isql .= "'8:00', ";
	$isql .= "'12:00 ÁS 13:00', ";
	$isql .= "'17:00', ";
	$isql .= "'SÁBADOS E DOMINGOS', ";
	$isql .= "'".$reg["cdp_etnia"]."', ";
	$isql .= "'".$reg["cdp_sexo"]."', ";
	$isql .= "'".$reg["cdp_tp_sangue"]."', ";
	$isql .= "'', ";//cabelo
	$isql .= "'', ";//olhos
	$isql .= "'".$reg["cdp_altura"]."', ";
	$isql .= "'".$reg["cdp_peso"]."', ";
	$isql .= "'".$reg['id_local']."', ";//id_local deve vir da requisição de pessoal
	$isql .= "'".$reg["cdp_cel"]."', ";
	$isql .= "'".$reg["cdp_fone"]."', ";
	$isql .= "'".$reg['data_inicio']."', ";//Inicio candidato deve vir da requisição
	$isql .= "'".$reg['centro_custo']."', ";//centro de custo
	$isql .= "'', ";//produto não tem
	$isql .= "'', ";//item contábil não tem
	$isql .= "'', ";//id cod fornec
	$isql .= "'".$reg["cd_opcao"]."', ";//
	$isql .= "'ATIVO', ";
	$isql .= "'".$sigla."', ";//
	$isql .= "'') ";
	
	$db->insert($isql,'MYSQL');
		
	if ($db->erro != '')
	{
		$resposta->addAlert($db->erro);
	}
	else
	{
		$idNovo = $db->insert_id;
		
		if ($reg['mod_contrato'] == 'SC')
		{
			$tipo_contrato[3]['valor'] = $reg['salario_pretendido'];
			
			$tipo_contrato[2]['valor'] = 0;
			
			$tipo_contrato[1]['valor'] 	= 0;
		}
		else if ($reg['mod_contrato'] == 'SC+MENS')
		{
			$tipo_contrato[2]['valor'] 	= $reg['salario_pretendido'];
			$tipo_contrato[3]['valor'] 	= 0;
			$tipo_contrato[1]['valor'] 	= 0;
		}
		else
		{
			$tipo_contrato[1]['valor'] 	= $reg['salario_pretendido'];
			$tipo_contrato[3]['valor'] 	= 0;
			$tipo_contrato[2]['valor'] 	= 0;
		}
		
		//Insere o salario e tipo contrato
		$isql = "INSERT INTO ".DATABASE.".salarios ";
		$isql .= "(id_funcionario,  tipo_contrato, id_tipo_salario, salario_clt, salario_mensalista, salario_hora, data, id_func_altera, data_altera) ";
		$isql .= "VALUES (";
		$isql .= "'" . $idNovo . "', ";
		$isql .= "'" . $reg['mod_contrato'] . "', ";
		$isql .= "'', ";
		$isql .= "'" . tipo_contrato[1]['valor'] . "', ";
		$isql .= "'" . tipo_contrato[2]['valor'] . "', ";
		$isql .= "'" . tipo_contrato[3]['valor'] . "', ";
		$isql .= "'" . $reg['data_inicio'] . "', ";
		$isql .= "'" . $_SESSION["id_funcionario"] . "', ";
		$isql .= "'" . date('Y-m-d') . "') ";
		
		$db->insert($isql,'MYSQL');
		
		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}

		$id_salario = $db->insert_id;

		//Atualiza o id_salario no banco funcionarios
		$usql = "UPDATE ".DATABASE.".funcionarios SET ";
		$usql .= "id_salario = '" . $id_salario . "' ";
		$usql .= "WHERE id_funcionario = '".$idNovo."' ";
		$usql .= "AND reg_del = 0 ";

		$db->update($usql,'MYSQL');

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
		else
		{
			//Criando cadastro de usuários
						
			$enc = new Crypter(CHAVE);
			
			$senha = $enc->encrypt("123456");

			$isql = "INSERT INTO ".DATABASE.".usuarios ";
			$isql .= "(id_funcionario, email, Login, Senha, status, data_troca, perfil) ";
			$isql .= "VALUES (";
			$isql .= "'" . $idNovo . "', ";
			$isql .= "'" . minusculas(trim($reg["cdvm_email"])) . "', ";
			$isql .= "'" . minusculas(trim($reg["cdvm_login"])) . "', ";
			$isql .= "'" . $senha . "', ";
			$isql .= "'1',";
			$isql .= "'".date('Y-m-d')."',";
			$isql .= "'2') ";

			$db->insert($isql,'MYSQL');
			
			if ($db->erro != '')
			{
				$resposta->addAlert($db->erro);	
			}
			else
			{
				$idUsuario = $db->insert_id;
				
				//Incluindo a formação
				$sql = "SELECT
							*
						FROM
							".DATABASE.".candidatos_formacao 
						WHERE
							reg_del = 0
							AND cf_candidato_id = ".$idCandidato;
				
				$db->select($sql, 'MYSQL',true);
				
				foreach($db->array_select as $regs)
				{
					$anoConclusao = explode('/', $regs['cf_mes_conclusao']);
					
					$isql = "INSERT INTO ".DATABASE.".rh_formacao ";
					$isql .= "(id_funcionario, id_instituicao, descricao, ano_conclusao) ";
					$isql .= "VALUES (";
					$isql .= "'" . $idNovo . "', ";
					$isql .= "'" . $regs['cf_instituicao'] . "', ";
					$isql .= "'" . $regs['cf_curso'] . "', ";
					$isql .= "'" . $anoConclusao[1] . "') ";

					$db->insert($isql,'MYSQL');
				}
				
				#PERMISSÕES NOVO MODELO #######################################################################################
				
				//Definindo qual sera o tipo de acesso padrao por tipo de contrato
				$arrTipoAcesso = array('CLT' => 0, 'EST' => 0, 'SC' => 1, 'SC+MENS' => 1, 'SOCIO' => 1, 'SC+MENS+CLT' => 1);
												
				//Buscando os acessos todos padrões 0 => clt, 1 => pj, 2 => clt + pj
				//Primeira parte consulta busca todos os modulos padrão por tipo de acesso de usuário
				$sql = "SELECT id_sub_modulo, sub_modulo, codigo_acesso FROM ".DATABASE.".sub_modulos ";
				$sql .= "WHERE acesso_padrao = 1 ";
				$sql .= "AND reg_del = 0 ";
				$sql .= "AND tipo_acesso_padrao IN(".$arrTipoAcesso[$reg['mod_contrato']].",2) ";
				
				//Segunda parte consulta busca todos os modulos que nao sao padrao e so serao exibidos de acordo com um setor especifico, ex. TI, Financeiro Etc
				$sql .= "UNION ALL ";
				$sql .= "SELECT sms.id_sub_modulo, sm.sub_modulo, sms.codigo_acesso ";
				$sql .= "FROM ".DATABASE.".sub_modulos_x_setor sms ";
				$sql .= "JOIN ".DATABASE.".sub_modulos sm on sm.id_sub_modulo = sms.id_sub_modulo ";
				$sql .= "WHERE sms.reg_del = 0 ";
				$sql .= "AND sms.id_setor_aso = ".$reg["setor_aso"]." ";
				$sql .= "AND sms.tipo_acesso_padrao IN(".$arrTipoAcesso[$reg['mod_contrato']].",2)";
				
				$db->select($sql, 'MYSQL', true);
				
				//Montando um único sql para inserir numa unica transação com o banco de dados
				$virgula = '';
				
				$isql = "INSERT INTO ".DATABASE.".permissoes (id_usuario, id_sub_modulo, permissao) VALUES ";
				
				foreach($db->array_select as $reg_permissoes)
				{
					$isql .= $virgula."(".$idUsuario.", ".$reg_permissoes['id_sub_modulo'].", ".$reg_permissoes['codigo_acesso'].")";
					$virgula = ',';
				}
				
				$db->insert($isql,'MYSQL');

				#FIM DAS PERMISSÕES NOVO MODELO ###############################################################################
				
				//Inserindo necessidades do funcionário
				if (!empty($reg['informacoes_ti']))
				{
					$isql = "INSERT INTO ".DATABASE.".rh_necessidades_x_funcionario
							 (id_necessidade, tipo_necessidade, outros, id_funcionario, id_requisicao, id_local)
							 VALUES
							 ('0', '5', '".$reg['informacoes_ti']."', '".$idNovo."', '".$reg['id_req_vaga']."', '".$reg['id_local']."') ";
					$db->insert($isql, 'MYSQL');
				}
				
				$sql = "SELECT
							*
						FROM
							".DATABASE.".infra_x_requisicao
							JOIN ".DATABASE.".infra_estrutura ON id_infra_estrutura = id_infra
						WHERE
							infra_x_requisicao.reg_del = 0
							AND infra_estrutura.reg_del = 0
							AND id_requisicao = ".$reg['id_req_vaga'];
				
				$retornoHtml = '';
								
				$db->select($sql, 'MYSQL',true);
				
				foreach($db->array_select as $reqInfra)
				{
					$tipoNecessidade = $reqInfra['uso'] - 1;
					
					$isql = "INSERT INTO ".DATABASE.".rh_necessidades_x_funcionario
							 (id_necessidade, tipo_necessidade, outros, id_funcionario, id_requisicao, id_local)
							 VALUES
							 ('".$reqInfra['id_infra']."', '".$tipoNecessidade."', '', '".$idNovo."', '".$reqInfra['id_requisicao']."', '".$reg['id_local']."') ";
				
					$db->insert($isql, 'MYSQL');
					
					//Montagem do e-mail
					switch($tipoNecessidade)
					{
						case 1:
							$retornoHtml['equipamentos'] .= $reqInfra['infra_estrutura'].'<br />';
						break;
						case 2:
							$retornoHtml['softwares'] .= $reqInfra['infra_estrutura'].'<br />';
						break;
						case 3:
							$retornoHtml['protheus'] .= $reqInfra['outros'].'<br />';
						break;
						case 4:
							$retornoHtml['sistema'] .= $reqInfra['outros'].'<br />';
						break;
						case 5:
							$retornoHtml['softwares'] .= $reqInfra['outros'].'<br />';
						break;
					}					
				}
				
				//Dados da empresa pela OS
				$sql = "SELECT id_empresa_erp FROM ".DATABASE.".ordem_servico ";
				$sql .= "WHERE id_os = ".$reg['id_os']." ";
				$sql .= "AND reg_del = 0 ";
				
				$db->select($sql, 'MYSQL', true);
				
				//Inserindo o chamado de integração no cliente
				$dadosChamado = array(
					'cliente' => $db->array_select[0]['id_empresa_erp'],
					'funcionario' => $idNovo,
					'descricao_integracao' => 'PEDIDO AUTOMÁTICO PARA NOVO COLABORADOR VIA CONTROLE DE CANDIDATOS APROVADOS',
					'data' => dateAddWithoutWeekEnds(date('Y-m-d'), 8, 'd/m/Y')
				);
				
				require('../ti/models/chamados_integracao_model.php');
				$chamado = new chamados_integracao_model();
				$chamado->inserir($dadosChamado);
				
				//Função para o E-mail
				$sql = "SELECT * FROM ".DATABASE.".rh_funcoes ";
				$sql .= "WHERE id_funcao = '". $cargo[0] ."' ";
				$sql .= "AND reg_del = 0 ";

				$db->select($sql,'MYSQL',true);
				
				$reg_cargo = $db->array_select[0];
				
				$cargo_descr = $reg_cargo["descricao"];

				//local de trabalho para o E-mail
				$sql = "SELECT * FROM ".DATABASE.".local ";
				$sql .= "WHERE id_local = '". $reg["id_local"] ."' ";
				$sql .= "AND reg_del = 0 ";

				$db->select($sql,'MYSQL',true);
				
				$reg_local = $db->array_select[0];
				
				$local_trabalho = $reg_local["descricao"];

				$cont4 = ProtheusDao::getCentroCusto($db, true, $reg["centro_custo"]);
				
				$regs_cc = $cont4[0];
				
				$diasestampa = mktime(0,0,0,date('m'),date('d'),date('Y'));
				$diasarray = getdate($diasestampa);

				$corpo = CIDADE . ", ". $diasarray["mday"]." de ".meses($diasarray["mon"]-1,1)." de ".$diasarray["year"] ."<br><br><br>";
				$corpo .= "<span style=\"color: #FF0000; font-weight: bold; text-decoration: underline; font-family: Verdana, Arial;\">CADASTRO&nbsp;DE&nbsp;USUÁRIO</span><br><br><br>";
				$corpo .= "Favor cadastrar o login e e-mail do novo funcionario:<br>";
				$corpo .= "Nome: <strong>".maiusculas($reg["nome"])."</strong><br>";
				$corpo .= "Funcao: <strong>".$cargo_descr."</strong><br>";
				$corpo .= "Centro Custo: <strong>".$reg_cc["CTT_CUSTO"]." - ".$regs_cc["CTT_DESC01"]. "</strong><br>";
				$corpo .= "<span style=\"color: #FF0000;\">data inicio: <strong>".mysql_php($reg["data_inicio"])."</strong></span><br>";
				$corpo .= "Local Trabalho: <strong>".$local_trabalho."</strong><br><br><br>";

				//Parte do email que vai para a TI
				$corpo .= "<span style=\"color: #FF0000;\">Infraestrutura TI: </span><br>";
				$corpo .= '<br />Equipamentos: <br />'.$retornoHtml['equipamentos'].'<br />';
				$corpo .= 'Softwares: <br />'.$retornoHtml['softwares'].'<br />';
				$corpo .= $retorno['dvmsys'] != '' ? 'Módulos SISTEMA: '.$retornoHtml['dvmsys'].'<br />' : '';
				$corpo .= $retorno['protheus'] != '' ? 'Módulos Protheus: '.$retornoHtml['protheus'].'<br />' : '';
				
				$corpo .= "Login: <strong>".minusculas($reg["cdvm_login"])."</strong><br>";
				$corpo .= "E-mail: <strong>".minusculas($reg["cdvm_email"])."</strong><br>";
				$corpo .= "Sigla: <strong>".minusculas($reg["cdvm_sigla"])."</strong><br><br><br><br>";
				
				$corpo .= "Primeiro acesso a rede:<br><br>";
				$corpo .= "Usuario:<strong>".minusculas($reg["cdvm_login"])."</strong><br>";
				$corpo .= "Senha:<strong>Dvm@54321</strong><br><br><br><br>";

				$corpo .= "Acesso SISTEMA:<br><br>";
				$corpo .= "Usuario:<strong>".minusculas($reg["cdvm_login"])."</strong><br>";
				$corpo .= "Senha:<strong>123456</strong><br><br>";
				$corpo .= "<strong>OBS:</strong>O sistema solicitará a troca da senha ao primeiro acesso.<br><br><br><br>";
				$corpo .= "Atenciosamente, Depto. Recursos Humanos.";
				
				//email
				$params 			= array();
				$params['from']		= "recrutamento@dominio.com.br";
				$params['from_name']= "RECURSOS HUMANOS";
				$params['subject'] 	= "CADASTRO DE NOVO USUARIO";

				$mail = new email($params, 'controle_aprovados_novo_usuario');
				$mail->montaCorpoEmail($corpo);
				$mail->Send();
				
				/**
				 * E-mails que serão enviados aos colaboradores
				 */
				/*
				 * E-mail para os colaboradores que estão por administração realizarem a integração
				 */
				if ($reg['nivel_atuacao'] == 'A')
				{
				    $corpo = "<p>Olá!</p><br />
				             <p>Agora que você faz parte da empresa ".NOME_EMPRESA.". É muito importante que você conheça a nossa empresa, que se sinta integrado entre nós. 
							 Para isso, acesse o SISTEMA, no bloco RH entre em Integração, assista o vídeo e ao final assine o Termo de Participação e nos 
							envie digitalizado (<u>documento obrigatório</u> para seu portfólio)</p>
				             <p>Agradecemos a colaboração.</p>
				             <p>Nos colocamos é disposição para auxiliá-lo.</p>
				             <p>Desejamos sucesso nessa nova jornada.</p><br />";
				    
				    $params 			= array();
				    $params['from']		= "recrutamento@dominio.com.br";
				    $params['from_name']= "RECURSOS HUMANOS";
				    $params['subject'] 	= "Integração";
				    
				    $params['emails']['to'][] = array('email' => minusculas($reg["cdvm_email"]), 'nome' => $reg["nome"]);
				    
				    $mail = new email($params);
				    $mail->montaCorpoEmail($corpo);
				    $mail->Send();
				}
				
				/*
				 * E-mail para todos, ADM ou INTERNO, trata-se do primeiro acesso e orientações sobre apontamento de horas
				 */
				if (in_array($reg['nivel_atuacao'], array('A', 'E')))
				{
				    $corpo = "<p>".$reg["nome"].",</p><br />
								    <p>Segue:<br />
                                    1.	Procedimento para lançamento de horas no SISTEMA (anexo).<br />
				                    2.	Login e senha para utilização do e-mail e SISTEMA (abaixo).<br /><br />";
				    
				    $params 			= array();
				    $params['from']		= "recrutamento@dominio.com.br";
				    $params['from_name']= "RECURSOS HUMANOS";
				    $params['subject'] 	= "Integração";
				    
				    $params['emails']['to'][] = array('email' => minusculas($reg["cdvm_email"]), 'nome' => $reg["nome"]);
				    
				    $mail = new email($params);
				    $mail->montaCorpoEmail($corpo);
				   // $mail->addAttachment("./modelos_pdf/informacoes_de_acesso_novos_colaboradores.pdf");
				    $mail->Send();
				}
			}
			
			$resposta->addAlert('Candidato Exportado corretamente!');
			$resposta->addScript("xajax_atualizatabela('');");
		}
	}
	
	return $resposta;
}

function getDadosVaga($id_requisicao)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados();
	
	$sql = "SELECT * FROM ".DATABASE.".requisicoes_pessoal ";
	$sql .= "WHERE id_requisicao = ".$id_requisicao." ";
	$sql .= "AND reg_del = 0 ";
	
	$db->select($sql, 'MYSQL',true);
	
	$reg = $db->array_select[0];
	
	$resposta->addAssign('nivel_atuacao', 'value', $reg['nivel_atuacao']);
	$resposta->addAssign('cargo_pretendido', 'value', $reg['id_cargo']);
	
	return $resposta;
}

function liberar($idCandidato)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados();

	$usql = "UPDATE ".DATABASE.".candidatos SET ";
	$usql .= "status = 2 ";
	$usql .= "WHERE id = ".$idCandidato;
	
	
	$db->update($usql, 'MYSQL');
	
	if ($db->erro != '')
	{
		$resposta->addAlert('Houve uma falha ao tentar liberar o cadastro!');
	}
	else
	{
		$resposta->addAlert('Cadastro liberado corretamente!');
		$resposta->addScript('xajax_atualizatabela();');
	}
	
	return $resposta;
}

$xajax->registerFunction("exportar");
$xajax->registerFunction("insere");
$xajax->registerFunction("excluir");
$xajax->registerFunction("atualizatabela");
$xajax->registerFunction("enviarEmail");
$xajax->registerFunction("getDadosVaga");
$xajax->registerFunction("editar");
$xajax->registerFunction("alterar");
$xajax->registerFunction("voltar");
$xajax->registerFunction("liberar");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","showLoader();xajax_atualizatabela('');");
?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script src="<?php echo INCLUDE_JS ?>jquery/jquery.min.js"></script>
<script src="<?php echo INCLUDE_JS ?>jquery/jquery-ui-1.11.1/jquery-ui.min.js"></script>
<script src="<?php echo INCLUDE_JS ?>jquery/jquery-maskmoney.min.js"></script>
<script src="<?php echo INCLUDE_JS ?>jquery.maskedinput/dist/jquery.maskedinput.min.js"></script>

<link href="../includes/jquery/jquery-ui-1.11.1/jquery-ui.min.css" rel="stylesheet" type="text/css">

<script language="javascript">
$(document).on('ready', function(){
	$('._currency').maskMoney({prefix:'R$ ', allowNegative: true, thousands:'.', decimal:',', affixesStay: false});
	$('._cpf').mask('999.999.999-99');

	$("._email").focusout(function(event){
		var email = $(this).val();
		if(email != "")
		{
			var filtro = /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i;
			if(filtro.test(email))
			{
				return true;
			}
			else
			{
				alert("Este endereço de email não é válido!");
				$(this).focus();
				return false;
			}
		}
		else
		{
			alert('Digite um email!');
			return false;
		}
	});
});

function limparForm()
{
	document.getElementById('frm').reset();
}

function grid(tabela, autoh, height, xml)
{	
	function doOnRowSelected(row,col)
	{
		if(col<5)
		{
			window.open('./cadastro_aprovados.php?rsh='+row+'&ajax=1','_blank','width=1024,height=800');
		
			return true;
		}
		
		return false;
	}
	
	mygrid = new dhtmlXGridObject(tabela);

	mygrid.enableAutoHeight(autoh,height);
	mygrid.enableRowsHover(true,'cor_mouseover');

	mygrid.setHeader("&nbsp;,Candidato,Sal.,Vaga,Modalidade,N.&nbsp;Atu.,Status,E,D,I,A,L");
	mygrid.setInitWidths("30,250,80,*,170,80,120,30,30,30,30,30");
	mygrid.setColAlign("left,left,left,left,left,left,left,center,center,center,center,center");
	mygrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
	mygrid.setColSorting("str,str,str,str,str,str,str,str,str,str,str,str");

	mygrid.attachEvent("onRowSelect",doOnRowSelected);
	
	mygrid.setSkin("dhx_skyblue");
    mygrid.enableMultiselect(true);
    mygrid.enableCollSpan(true);
    mygrid.enableMultiline(true);
	mygrid.init();
	mygrid.loadXMLString(xml);
}
</script>

<?php
$conf = new configs();

$sql = "SELECT
		  id_requisicao, os, Descr
		FROM
		  ".DATABASE.".requisicoes_pessoal
		LEFT JOIN
		  (SELECT id_os as idOs, os, descricao as Descr FROM ".DATABASE.".ordem_servico WHERE ordem_servico.reg_del = 0) os
		  ON id_os = idOs
		WHERE
			requisicoes_pessoal.reg_del = 0
		    AND ultimo_status IN(2,5)
		ORDER BY
		  os ";
$db->select($sql, 'MYSQL', true);

$array_req_values[] = "";
$array_req_output[] = "SELECIONE...";

foreach($db->array_select as $reg)
{
	$array_req_values[] = $reg["id_requisicao"];
	$array_req_output[] = sprintf('%06d', $reg["id_requisicao"]).' - '.sprintf('%06d', $reg["os"]).' - '.$reg['Descr'];	
}

$smarty->assign("option_req_values",$array_req_values);
$smarty->assign("option_req_output",$array_req_output);

$sql = "SELECT id_funcao, descricao, id_cargo_grupo FROM ".DATABASE.".rh_funcoes ";
$sql .= "WHERE rh_funcoes.reg_del = 0 ";
$sql .= "ORDER BY rh_funcoes.descricao ";

$db->select($sql,'MYSQL',true);

$array_cargos_values = NULL;
$array_cargos_output = NULL;

foreach($db->array_select as $reg_cargo)
{
	$array_cargos_values[] = $reg_cargo["id_funcao"].','.$reg_cargo['id_cargo_grupo'];
	$array_cargos_output[] = substr($reg_cargo["descricao"],0,40);
}

$smarty->assign("option_cargos_values",$array_cargos_values);
$smarty->assign("option_cargos_output",$array_cargos_output);

//Centro de custo
$cc = ProtheusDao::getCentroCusto($db, true);

$array_cc_values[] = '';
$array_cc_output[] = 'SELECIONE...';

foreach($cc as $regs)
{
	$array_cc_values[] = trim($regs["CTT_CUSTO"]);
	$array_cc_output[] = trim($regs["CTT_CUSTO"]). ' - ' .maiusculas($regs["CTT_DESC01"]);
}

$smarty->assign("option_cc_values",$array_cc_values);
$smarty->assign("option_cc_output",$array_cc_output);

$sql = "SELECT * FROM ".DATABASE.".setor_aso ";
$sql .= "WHERE reg_del = 0 ";
$sql .= "ORDER BY setor_aso.setor_aso ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}

foreach($db->array_select as $cont)
{
	$array_setor_aso_values[] = $cont["id_setor_aso"];
	$array_setor_aso_output[] = $cont["setor_aso"];
}

$smarty->assign("option_setor_aso_values",$array_setor_aso_values);
$smarty->assign("option_setor_aso_output",$array_setor_aso_output);

$smarty->assign("revisao_documento","V1");

$smarty->assign("campo",$conf->campos('controle_aprovados'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("classe",CSS_FILE);

$smarty->assign('larguraTotal', 1);

$smarty->display('controle_aprovados.tpl');
?>