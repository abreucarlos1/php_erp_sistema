<?php
/*
	Formulário de Cadastro de candidatos aprovados

	Criado por Carlos Eduardo Máximo
	
			local/Nome do arquivo:
			../rh/cadastro_aprovados.php

	Versão 0 --> VERSÃO INICIAL : 08/04/2016
	Versão 1 --> Alteração DB: 05/01/2017 - Carlos Abreu
	Versão 2 --> Atualização layout -  Carlos Abreu - 04/04/2017
	Versão 3 --> Inclusão dos campos reg_del nas consultas - 27/11/2017 - Carlos Abreu
 */

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

require_once(INCLUDE_DIR."antiInjection.php");

$conf = new configs();

if (isset($_GET['cadastrarNovaInstituicao']) && $_GET['cadastrarNovaInstituicao'] == 1)
{
	if (!empty($_POST['nome']))
	{
		$_POST['nome'] = strtoupper(AntiInjection::clean(utf8_decode($_POST['nome'])));
		
		$isql = "INSERT INTO ".DATABASE.".rh_instituicao_ensino (instituicao_ensino) VALUES( ";
		$isql .= "'".$_POST['nome']."')";

		$db->insert($isql, 'MYSQL');

		if (!empty($db->erro))
		exit(json_encode(array(0,0)));
		else
		exit(json_encode(array(1, $db->insert_id)));
	}
	else
	exit(0);
}

if (isset($_GET['excluirFormacao']) && $_GET['excluirFormacao'] == 1)
{
	$idFormacao = $_GET['id'];
	
	$usql = "UPDATE ".DATABASE.".candidatos_formacao SET ";
	$usql .= "reg_del = 1, ";
	$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
	$usql .= "data_del = '".date('Y-m-d')."' ";
	$usql .= "WHERE cf_id = ".$idFormacao ." ";
	$usql .= "AND reg_del = 0 ";
	
	$db->update($usql, 'MYSQL');

	if ($db->erro != '')
		exit('0');
	else
		exit('1');
}

if (isset($_GET['excluirCurso']) && $_GET['excluirCurso'] == 1)
{
	$idFormacao = $_GET['id'];
	
	$usql = "UPDATE ".DATABASE.".candidatos_cursos SET ";
	$usql .= "reg_del = 1, ";
	$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
	$usql .= "data_del = '".date('Y-m-d')."' ";
	$usql .= "WHERE ccu_id = ".$idFormacao." ";
	$usql .= "AND reg_del = 0 ";
	
	$db->update($usql, 'MYSQL');

	if ($db->erro != '')
	exit('0');
	else
	exit('1');
}

//Função principal para chamar as funçães que realizarão os salvamentos em banco de dados
if (isset($_GET['acao']) && $_GET['acao'] == 'salvar' && !empty($_POST))
{
	if (isset($_POST['rsh']))
	{
		$sql = "SELECT
					id, cdp_id, cd_id, cea_id, cia_id, ce_id, cdvm_id, ccu_id, desclocal
				FROM
					".DATABASE.".candidatos
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
						SELECT * FROM ".DATABASE.".candidatos_cursos WHERE candidatos_cursos.reg_del = 0
					) ccu
					ON ccu_candidato_id = id
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
					LEFT JOIN(
						SELECT id_local, id_requisicao, desclocal, reg_del FROM ".DATABASE.".requisicoes_pessoal
						LEFT JOIN(
							SELECT id_local, descricao desclocal FROM ".DATABASE.".local WHERE local.reg_del = 0 
						) loc
						ON id_local = id_local
					) req
					ON id_requisicao = id_req_vaga AND req.reg_del = 0
				WHERE
					rash = '".$_GET['rsh']."' 
					AND candidatos.reg_del = 0 ";

		$formacoes = array();

		$db->select($sql, 'MYSQL',true);
		
		$dados = $db->array_select;
		
		foreach($dados as $reg)
		{
			$formacoes[] = $reg['cf_id'];
		}

		if (!empty($dados[0]['id']))
		{
			//Salvando dados pessoais
			$retDadosPessoais = salvarDadosPessoais($_POST['dados_pessoais'], $dados[0]['id'], $dados[0]['cdp_id']);
				
			if (!$retDadosPessoais)
			{
				$smarty->assign('mensagem', array('#FF7777', 'DADOS PESSOAIS NÃO FORAM SALVOS'));
			}
				
			//Salvando documentos
			$retDocumentos = salvarDocumentos($_POST['documentos'], $dados[0]['id'], $dados[0]['cd_id']);
				
			if (!$retDocumentos)
			{
				$smarty->assign('mensagem', array('#FF7777', 'DOCUMENTOS NÃO FORAM SALVOS'));
			}
				
			//Salvando formação
			$retFormacao = salvarFormacao($_POST['formacao'], $dados[0]['id']);
				
			if (!$retFormacao)
			{
				$smarty->assign('mensagem', array('#FF7777', 'DADOS DA FORMAÇÃO NÃO FORAM SALVOS'));
			}
				
			//Salvando cursos
			$retCursos = salvarCursos($_POST['cursos'], $dados[0]['id']);
				
			if (!$retCursos)
			{
				$smarty->assign('mensagem', array('#FF7777', 'OS CURSOS NÃO FORAM SALVOS'));
			}
				
			//Salvando empresa anterior
			$retEmpresaAnterior = salvarEmpregoAnterior($_POST['emprego_anterior'], $dados[0]['id'], $dados[0]['cea_id']);
				
			if (!$retEmpresaAnterior)
			{
				$smarty->assign('mensagem', array('#FF7777', 'DADOS DA EMPRESA ANTERIOR NÃO FORAM SALVOS'));
			}
				
			//Salvando informações adicionais
			$retInformacoesAdicionais = salvarInformacoesAdicionais($_POST['informacoes_adicionais'], $dados[0]['id'], $dados[0]['cia_id']);
				
			if (!$retInformacoesAdicionais)
			{
				$smarty->assign('mensagem', array('#FF7777', 'INFORMAÇÕES ADICIONAIS NÃO FORAM SALVAS'));
			}
				
			//Salvando epi's
			$retEpi = salvarEpi($_POST['area_tecnica_epi'], $dados[0]['id'], $dados[0]['ce_id']);
				
			if (!$retEpi)
			{
				$smarty->assign('mensagem', array('#FF7777', 'INFORMAÇÕES ADICIONAIS NÃO FORAM SALVAS'));
			}
				
			//Salvando epi's
			$retDvm = salvarEmpresa($_POST['empresa'], $dados[0]['id'], $dados[0]['cdvm_id']);
				
			if (!$retDvm)
			{
				$smarty->assign('mensagem', array('#FF7777', 'DADOS DA EMPRESA NÃO FORAM SALVOS CORRETAMENTE'));
			}
				
			$cargo_pretendido 	= strtoupper(AntiInjection::clean($_POST['cargo_pretendido']));
			
			$salario_pretendido = str_replace('R$ ', '',  str_replace(',','.', str_replace('.', '', $_POST['salario_pretendido'])));
			
			$status_cadastro 	= $_POST['status_cadastro'] == 1 ? 2 : AntiInjection::clean($_POST['status_cadastro']);
			
			$cpf				= AntiInjection::clean($_POST['documentos']['cpf_num']);
			
			$tpSalario			= $_POST['rdoTpSalario'];	
				
			if (isset($_SESSION['id_funcionario']))
			{
				$uComplemento = ", cargo_pretendido = '".$cargo_pretendido."', salario_pretendido = '".$salario_pretendido."', tipo_salario = '".$tpSalario."' ";
			}
				
			//Alterando o status do candidato para EM PREENCHIMENTO
			$usql = "UPDATE candidatos.candidatos SET ";
			$usql .= "status = ".$status_cadastro." ".$uComplemento.", ";
			$usql .= "cpf = '".$cpf."' ";
			$usql .= "WHERE rash = '".$_GET['rsh']."' ";
			$usql .= "AND reg_del = 0 ";
			
			$db->update($usql, 'MYSQL');
				
			if ($db->erro == '')
			{
				$ajaxLink = $_POST['ajax'] == 1 ? '&ajax=1' : '';

				if ($status_cadastro == 3)
				{
					$corpo = "Mogi das Cruzes, ".date('d/m/Y')."<br><br><br>";
					$corpo .= "O candidato ".$_POST["funcionario"]." concluiu seu cadastro<br />";
					$corpo .= "local de Trabalho: ".$dados[0]['desclocal']."<br />";
					$corpo .= "Por favor, verificar se os dados estão corretos.";
				
					if(ENVIA_EMAIL)
					{
						//email
						$params 			= array();
						$params['from']		= "recrutamento@dominio.com.br";
						$params['from_name']= "RECURSOS HUMANOS";
						$params['subject'] 	= "CONCLUSÃO DE CADASTRO DE CANDIDATO APROVADO";

						$mail = new email($params,'conclusao_cadastro_aprovados');
						$mail->montaCorpoEmail($corpo);
						$mail->Send();
					}
				}

				exit(
					'<script>
						alert("Cadastro salvo corretamente!");
						location.href = "./cadastro_aprovados.php?rsh='.$_GET['rsh'].$ajaxLink.'";
					</script>'
					);
			}
			else
			{
				$smarty->assign('mensagem', array('#FF7777', 'ATENÇÃO: Houve uma falha ao tentar salvar os dados ('.$db->erro.')'));
			}
				
			$smarty->assign('post', $_POST);
		}
		else
		{
			exit('Cadastro não encontrado!');
		}
	}
	else
	{
		exit('Cadastro não autorizado!');
	}
}

function salvarDadosPessoais($POST, $idCandidato, $cdpId = '')
{
	$db = new banco_dados();

	//Tratamento do post
	$nacionalidade 	= strtoupper(AntiInjection::clean($POST['id_nacionalidade']));
	$naturalidade 	= strtoupper(AntiInjection::clean($POST['naturalidade']));
	$ufNascimento	= strtoupper(AntiInjection::clean($POST['estado_nasc']));
	$sexo			= strtoupper(AntiInjection::clean($POST['sexo']));
	$idade			= AntiInjection::clean($POST['idade']);
	$dataNasc		= php_mysql($POST['data_nascimento']);
	$endereco		= strtoupper(AntiInjection::clean($POST['endereco']));
	$bairro			= strtoupper(AntiInjection::clean($POST['bairro']));
	$cidade			= strtoupper(AntiInjection::clean($POST['cidade_mora']));
	$uf				= strtoupper(AntiInjection::clean($POST['uf']));
	$cep			= AntiInjection::clean($POST['cep']);
	$fone			= AntiInjection::clean($POST['telefone']);
	$cel			= AntiInjection::clean($POST['celular']);
	$foneRecados	= AntiInjection::clean($POST['foneRecados']);
	$estCivil		= AntiInjection::clean($POST['estado_civil']);
	$dataCasam		= php_mysql($POST['data_casamento']);
	$nFilhos		= AntiInjection::clean($POST['num_filhos']);
	$nomeConjuge	= strtoupper(AntiInjection::clean($POST['conjuge']));
	$nomePai		= strtoupper(AntiInjection::clean($POST['pai']));
	$nomeMae		= strtoupper(AntiInjection::clean($POST['mae']));
	$peso			= str_replace(',', '.', AntiInjection::clean($POST['peso']));
	$tpSangue		= strtoupper(AntiInjection::clean($POST['tipo_sanguineo']));
	$altura			= str_replace(',', '.', AntiInjection::clean($POST['altura']));
	$etnia			= strtoupper(AntiInjection::clean($POST['etnia']));
	$banco			= strtoupper(AntiInjection::clean($POST['banco']));
	$cc				= strtoupper(AntiInjection::clean($POST['cc']));
	$agencia		= strtoupper(AntiInjection::clean($POST['banco_agencia']));
	$tpContrato		= AntiInjection::clean($POST['tpContrato']);

	//No caso de insersão de dados pessoais
	if (empty($cdpId))
	{
		$isql =
		"INSERT INTO ".DATABASE.".candidatos_dados_pessoais (
			cdp_candidato_id, cdp_nacionalidade , cdp_naturalidade, cdp_uf_nasc, cdp_sexo, cdp_idade, cdp_data_nasc, cdp_endereco, cdp_bairro, cdp_cidade, cdp_uf, cdp_cep, cdp_fone, cdp_cel,
			cdp_fone_recados, cdp_est_civil, cdp_data_casamento, cdp_n_filhos, cdp_nome_conjuge, cdp_nome_pai, cdp_nome_mae, cdp_peso, cdp_tp_sangue, cdp_altura, cdp_etnia, cdp_banco,
			cdp_cc, cdp_agencia, cdp_tp_contrato)
		VALUES (
		".$idCandidato.",'".$nacionalidade."','".$naturalidade."','".$ufNascimento."',
			'".$sexo."','".$idade."','".$dataNasc."','".$endereco."',
			'".$bairro."','".$cidade."','".$uf."','".$cep."','".$fone."',
			'".$cel."','".$foneRecados."','".$estCivil."','".$dataCasam."','".$nFilhos."',
			'".$nomeConjuge."','".$nomePai."','".$nomeMae."','".$peso."',
			'".$tpSangue."','".$altura."','".$etnia."','".$banco."',
			'".$cc."','".$agencia."','".$tpContrato."'
		)";
			
		$db->insert($isql, 'MYSQL');
	}
	else//No caso de Atualização de dados pessoais
	{
		$usql =
		"UPDATE ".DATABASE.".candidatos_dados_pessoais SET 
			cdp_nacionalidade = '".$nacionalidade."',
			cdp_naturalidade = '".$naturalidade."',
			cdp_uf_nasc = '".$ufNascimento."',
			cdp_sexo = '".$sexo."',
			cdp_idade = '".$idade."',
			cdp_data_nasc = '".$dataNasc."',
			cdp_endereco = '".$endereco."',
			cdp_bairro = '".$bairro."',
			cdp_cidade = '".$cidade."',
			cdp_uf = '".$uf."',
			cdp_cep = '".$cep."',
			cdp_fone = '".$fone."',
			cdp_cel = '".$cel."',
			cdp_fone_recados = '".$foneRecados."',
			cdp_est_civil = '".$estCivil."',
			cdp_data_casamento = '".$dataCasam."',
			cdp_n_filhos = '".$nFilhos."',
			cdp_nome_conjuge = '".$nomeConjuge."',
			cdp_nome_pai = '".$nomePai."',
			cdp_nome_mae = '".$nomeMae."',
			cdp_peso = '".$peso."',
			cdp_tp_sangue = '".$tpSangue."',
			cdp_altura = '".$altura."',
			cdp_etnia = '".$etnia."',
			cdp_banco = '".$banco."',
			cdp_cc = '".$cc."',
			cdp_agencia = '".$agencia."',
			cdp_tp_contrato = '".$tpContrato."'
		WHERE
			reg_del = 0 
			AND cdp_id = ".$cdpId;

		$db->update($usql, 'MYSQL');
	}

	if ($db->erro != '')
	{
		return false;
	}

	return true;
}

function salvarDocumentos($POST, $idCandidato, $cdId = '')
{
	$db = new banco_dados();

	//Tratamento do post
	$ctps 			= AntiInjection::clean($POST['ctps_num']);
	$ctpsSerie 		= strtoupper(AntiInjection::clean($POST['ctps_serie']));
	$ctpsEmissao	= php_mysql($POST['ctps_data_emissao']);
	$rg				= AntiInjection::clean($POST['identidade_num']);
	$rgEmissor		= strtoupper(AntiInjection::clean($POST['identidade_emissor']));
	$rgEmissao		= php_mysql($POST['data_emissao']);
	$cpf			= AntiInjection::clean($POST['cpf_num']);
	$titulo			= AntiInjection::clean($POST['titulo_eleitor']);
	$tituloZona		= AntiInjection::clean($POST['titulo_zona']);
	$tituloSecao	= AntiInjection::clean($POST['titulo_secao']);
	$pis_numero		= AntiInjection::clean($POST['pis_numero']);
	$reservista		= AntiInjection::clean($POST['reservista_num']);
	$reservistaSer	= strtoupper(AntiInjection::clean($POST['reservista_serie']));
	$cidade			= strtoupper(AntiInjection::clean($POST['cidade']));
	$cnpj			= AntiInjection::clean($POST['cnpj']);
	$empresa		= strtoupper(AntiInjection::clean($POST['empresa']));
	$tpTributacao	= AntiInjection::clean($POST['tipo_tributacao']);

	//No caso de insersão de dados pessoais
	if (empty($cdId))
	{
		$isql =
		"INSERT INTO ".DATABASE.".candidatos_documentos (
			cd_candidato_id, cd_ctps, cd_ctps_serie, cd_ctps_emissao, cd_rg, cd_rg_orgao, cd_rg_emissao, cd_cpf,
			cd_titulo_eleitor, cd_titulo_secao, cd_titulo_zona, cd_pis, cd_reservista, cd_reservista_serie, cd_reservista_cidade,
			cd_cnpj, cd_empresa, cd_opcao)
		VALUES (
		".$idCandidato.",'".$ctps."','".$ctpsSerie."','".$ctpsEmissao."',
			'".$rg."','".$rgEmissor."','".$rgEmissao."','".$cpf."',
			'".$titulo."','".$tituloSecao."','".$tituloZona."','".$pis_numero."','".$reservista."',
			'".$reservistaSer."','".$cidade."','".$cnpj."','".$empresa."','".$tpTributacao."'				
		)";
			
		$db->insert($isql, 'MYSQL');
	}
	else//No caso de Atualização de dados pessoais
	{
		$usql =
		"UPDATE ".DATABASE.".candidatos_documentos SET 
			cd_ctps = '".$ctps."',
			cd_ctps_serie = '".$ctpsSerie."',
			cd_ctps_emissao = '".$ctpsEmissao."',
			cd_rg = '".$rg."',
			cd_rg_orgao = '".$rgEmissor."',
			cd_rg_emissao = '".$rgEmissao."',
			cd_cpf = '".$cpf."',
			cd_titulo_eleitor = '".$titulo."',
			cd_titulo_secao = '".$tituloSecao."',
			cd_titulo_zona = '".$tituloZona."',
			cd_pis = '".$pis_numero."',
			cd_reservista = '".$reservista."',
			cd_reservista_serie = '".$reservistaSer."',
			cd_reservista_cidade = '".$cidade."',
			cd_cnpj = '".$cnpj."',
			cd_empresa = '".$empresa."',
			cd_opcao = '".$tpTributacao."'
		WHERE
			reg_del = 0 
			AND cd_id = ".$cdId;

		$db->update($usql, 'MYSQL');
	}

	if ($db->erro != '')
	{
		return false;
	}

	return true;
}

function salvarFormacao($POST, $idCandidato)
{
	$db = new banco_dados();

	foreach($POST['curso'] as $k => $curso)
	{
		$id				= AntiInjection::clean($POST['id'][$k]);
		$curso 			= strtoupper(tiraacentos(AntiInjection::clean($curso)));
		$instituicao	= AntiInjection::clean($POST['instituicao_ensino'][$k]);
		$descricao		= strtoupper(AntiInjection::clean(tiraacentos($POST['descricao_formacao'][$k])));
		$mesInicio		= AntiInjection::clean($POST['mes_inicio'][$k]);
		$mesFim			= AntiInjection::clean($POST['mes_conclusao'][$k]);
		$completo		= strtoupper(AntiInjection::clean(tiraacentos($POST['completo'][$k])));
		$ateSerie		= strtoupper(AntiInjection::clean(tiraacentos($POST['ate_serie'][$k])));

		if (empty($id))
		{
			//,'{$descricao}',
			//cf_descricao,
			$isql =
			"INSERT INTO ".DATABASE.".candidatos_formacao (
				cf_candidato_id, cf_curso, cf_instituicao, cf_mes_inicio, cf_mes_conclusao, cf_completou, cf_serie)
			VALUES ";
				
			$isql .= "(".$idCandidato.",'".$curso."','".$instituicao."','".$mesInicio."','".$mesFim."','".$completo."','".$ateSerie."') ";
				
			$db->insert($isql, 'MYSQL');
		}
		else
		{
			$usql =
			"UPDATE ".DATABASE.".candidatos_formacao SET 
				cf_curso = '".$curso."',
				cf_instituicao = '".$instituicao."',
				cf_mes_inicio = '".$mesInicio."',
				cf_mes_conclusao = '".$mesFim."',
				cf_completou = '".$completo."',
				cf_serie = '".$ateSerie."'
			WHERE
				reg_del = 0 
				AND cf_id = " . $id;
				
			$db->update($usql, 'MYSQL');
		}
	}

	if ($db->erro != '')
	{
		return false;
	}

	return true;
}

function salvarCursos($POST, $idCandidato)
{
	$db = new banco_dados();

	foreach($POST['curso'] as $k => $curso)
	{
		$id				= AntiInjection::clean($POST['id'][$k]);
		$curso 			= strtoupper(tiraacentos(AntiInjection::clean($curso)));
		$instituicao	= strtoupper(AntiInjection::clean($POST['instituicao_ensino'][$k]));
		$descricao		= strtoupper(AntiInjection::clean(tiraacentos($POST['descricao_formacao'][$k])));
		$mesInicio		= AntiInjection::clean($POST['mes_inicio'][$k]);
		$mesFim			= AntiInjection::clean($POST['mes_conclusao'][$k]);
		$nivel			= strtoupper(AntiInjection::clean(tiraacentos($POST['nivel'][$k])));

		if (empty($id))
		{
			$isql =
			"INSERT INTO ".DATABASE.".candidatos_cursos (
				ccu_candidato_id, ccu_curso, ccu_instituicao, ccu_mes_inicio, 
				ccu_mes_conclusao, ccu_nivel)
			VALUES ";
				
			$isql .= "(".$idCandidato.",'".$curso."','".$instituicao."','".$mesInicio."','".$mesFim."','".$nivel."') ";
				
			$db->insert($isql, 'MYSQL');
		}
		else
		{
			$usql =
			"UPDATE ".DATABASE.".candidatos_cursos SET 
				ccu_curso = '".$curso."',
				ccu_instituicao = '".$instituicao."',
				ccu_mes_inicio = '".$mesInicio."',
				ccu_mes_conclusao = '".$mesFim."',
				ccu_nivel = '".$nivel."'
			WHERE
				reg_del = 0 
				AND ccu_id = ".$id;
				
			$db->update($usql, 'MYSQL');
		}
	}

	if ($db->erro != '')
	{
		return false;
	}

	return true;
}

function salvarEmpregoAnterior($POST, $idCandidato, $ceaId = '')
{
	$db = new banco_dados();

	//Tratamento do post
	$empresa	= strtoupper(AntiInjection::clean($POST['empresa']));
	$fone 		= AntiInjection::clean($POST['fone']);
	$endereco	= strtoupper(AntiInjection::clean($POST['endereco']));
	$cidade		= strtoupper(AntiInjection::clean($POST['cidade']));
	$uf			= strtoupper($POST['uf']);
	$cargo		= strtoupper(AntiInjection::clean($POST['cargo']));
	$admissao	= php_mysql($POST['admissao']);
	$demissao	= php_mysql($POST['demissao']);
	$salInicial	= str_replace('R$ ', '', str_replace('.', '', $POST['sal_ini']));
	$salFinal	= str_replace('R$ ', '', str_replace('.', '', $POST['sal_fim']));
	$descricao	= strtoupper(AntiInjection::clean(tiraacentos($POST['descricao'])));
	$motSaida	= strtoupper(AntiInjection::clean(tiraacentos($POST['mot_saida'])));

	//No caso de insersão de dados pessoais
	if (empty($ceaId))
	{
		$isql =
		"INSERT INTO ".DATABASE.".candidatos_emprego_anterior (
			cea_candidato_id, cea_empresa, cea_fone, cea_endereco, cea_cidade, cea_uf, cea_cargo, cea_admissao, cea_demissao, cea_sal_ini, cea_sal_fim, cea_descricao, cea_mot_saida)
		VALUES (
		".$idCandidato.",'".$empresa."','".$fone."','".$endereco."',
			'".$cidade."','".$uf."','".$cargo."','".$admissao."',
			'".$demissao."','".$salInicial."','".$salFinal."','".$descricao."','".$motSaida."') ";

		$db->insert($isql, 'MYSQL');
	}
	else//No caso de Atualização de dados pessoais
	{
		$usql =
		"UPDATE ".DATABASE.".candidatos_emprego_anterior SET 
			cea_empresa = '".$empresa."',
			cea_fone = '".$fone."',
			cea_endereco = '".$endereco."',
			cea_cidade = '".$cidade."',
			cea_uf = '".$uf."',
			cea_cargo = '".$cargo."',
			cea_admissao = '".$admissao."',
			cea_demissao = '".$demissao."',
			cea_sal_ini = '".$salInicial."',
			cea_sal_fim = '".$salFinal."',
			cea_descricao = '".$descricao."',
			cea_mot_saida = '".$motSaida."'
		WHERE
			reg_del = 0
			AND cea_id = ".$ceaId;

		$db->update($usql, 'MYSQL');
	}

	if ($db->erro != '')
	{
		return false;
	}

	return true;
}

function salvarInformacoesAdicionais($POST, $idCandidato, $ciaId = '')
{
	$db = new banco_dados();

	//Tratamento do post
	$dispViagens	= $POST['disp_viagens'];
	$dispCidades	= $POST['trab_outras_cid'];
	$trabTurno		= $POST['trab_turno'];
	$valeTransp		= $POST['vale_transp'];
	$qtdVtIda		= AntiInjection::clean($POST['qtd_vt_ida']);
	$qtdVtVolta		= AntiInjection::clean($POST['qtd_vt_volta']);
	$valVtIda		= str_replace('R$ ', '', str_replace(',','.', str_replace('.', '', $POST['val_vt_ida'])));
	$valVtVolta		= str_replace('R$ ', '', str_replace(',','.', str_replace('.', '', $POST['val_vt_volta'])));

	//No caso de inserção de dados pessoais
	if (empty($ciaId))
	{
		$isql =
		"INSERT INTO ".DATABASE.".candidatos_informacoes_adicionais (
			cia_candidato_id, cia_disp_viagens, cia_disp_cidades, 
			cia_disp_turnos, cia_vt, cia_val_ida, cia_val_volta, cia_qtd_ida, cia_qtd_volta)
		VALUES (
		".$idCandidato.",'".$dispViagens."','".$dispCidades."','".$trabTurno."','".$valeTransp."',
			'".$valVtIda."','".$valVtVolta."','".$qtdVtIda."','".$qtdVtVolta."') ";

		$db->insert($isql, 'MYSQL');
	}
	else//No caso de Atualização de dados pessoais
	{
		$usql =
		"UPDATE ".DATABASE."candidatos_informacoes_adicionais SET 
			cia_disp_viagens = '".$dispViagens."',
			cia_disp_cidades = '".$dispCidades."',
			cia_disp_turnos = '".$trabTurno."',
			cia_vt = '".$valeTransp."',
			cia_val_ida = '".$valVtIda."',
			cia_val_volta = '".$valVtVolta."',
			cia_qtd_ida = '".$qtdVtIda."',
			cia_qtd_volta = '".$qtdVtVolta."'
		WHERE
			reg_del = 0
			AND cia_id = ".$ciaId;

		$db->update($usql, 'MYSQL');
	}

	if ($db->erro != '')
	{
		return false;
	}

	return true;
}

function salvarEpi($POST, $idCandidato, $ceId = '')
{
	$db = new banco_dados();

	//Tratamento do post
	$numCalcado	= AntiInjection::clean($POST['num_calcado']);
	$tamCalca	= strtoupper(AntiInjection::clean($POST['tam_calca']));
	$tamJaleco	= strtoupper(AntiInjection::clean($POST['tam_jaleco']));
	$tamCamisa	= strtoupper(AntiInjection::clean($POST['tam_camisa']));
	$tpOculos	= $POST['tp_oculos'];

	//No caso de inserção de dados pessoais
	if (empty($ceId))
	{
		$isql =
		"INSERT INTO ".DATABASE.".candidatos_epi (
			ce_candidato_id, ce_num_calcado, ce_tam_calca, ce_tam_jaleco, ce_tam_camisa, ce_tp_oculos)
		VALUES (
		".$idCandidato.",'".$numCalcado."','".$tamCalca."', '".$tamJaleco."','".$tamCamisa."','".$tpOculos."' )";

		$db->insert($isql, 'MYSQL');
	}
	else//No caso de Atualização de dados pessoais
	{
		$usql =
		"UPDATE ".DATABASE.".candidatos_epi SET 
			ce_num_calcado = '".$numCalcado."',
			ce_tam_calca = '".$tamCalca."',
			ce_tam_jaleco = '".$tamJaleco."',
			ce_tam_camisa = '".$tamCamisa."',
			ce_tp_oculos = '".$tpOculos."'
		WHERE
			reg_del = 0 
			AND ce_id = ".$ceId;

		$db->update($usql, 'MYSQL');
	}

	if ($db->erro != '')
	{
		return false;
	}

	return true;
}

function salvarEmpresa($POST, $idCandidato, $cdvmId = '')
{
	$db = new banco_dados();

	//Tratamento do post
	$email	= strtoupper(AntiInjection::clean($POST['email']));
	$login	= strtoupper(AntiInjection::clean($POST['login']));
	$sigla	= strtoupper(AntiInjection::clean($POST['sigla_func']));

	//No caso de inserção de dados pessoais
	if (empty($cdvmId))
	{
		$isql =
		"INSERT INTO ".DATABASE.".candidatos_devemada (
			cdvm_candidato_id, cdvm_email, cdvm_login, cdvm_sigla)
		VALUES (
		".$idCandidato.",'".$email."','".$login."','".$sigla."' )";

		$db->insert($isql, 'MYSQL');
	}
	else//No caso de Atualização de dados pessoais
	{
		if (!empty($email) && !empty($login))
		{
			$usql =
			"UPDATE ".DATABASE.".candidatos_devemada SET 
				cdvm_email = '".$email."',
				cdvm_login = '".$login."',
				cdvm_sigla = '".$sigla."'
			WHERE
				reg_del = 0 
				AND cdvm_id = ".$cdvmId;
				
			$db->update($usql, 'MYSQL');
		}
	}

	if ($db->erro != '')
	{
		return false;
	}

	return true;
}
?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script	src="<?php echo INCLUDE_JS ?>jquery/jquery.min.js"></script>

<script	src="<?php echo INCLUDE_JS ?>jquery/jquery-ui-1.11.1/jquery-ui.min.js"></script>

<script	src="<?php echo INCLUDE_JS ?>jquery/jquery-maskmoney.min.js"></script>

<script	src="<?php echo INCLUDE_JS ?>jquery.maskedinput/dist/jquery.maskedinput.min.js"></script>

<link href="../includes/jquery/jquery-ui-1.11.1/jquery-ui.min.css" rel="stylesheet" type="text/css">

<script language="javascript">
$(document).on('ready', function(){
	$('.link_1').parent().remove();
	
	$('._data').datepicker({
		dayNamesMin: ['D','S','T','Q','Q','S','S','D'],
        dayNamesShort: ['Dom','Seg','Ter','Qua','Qui','Sex','Sáb','Dom'],
        dayNames: ['Domingo','Segunda','Terça','Quarta','Quinta','Sexta','Sábado'],
        monthNamesShort: ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'],
        monthNames: ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'],
        dateFormat: 'dd/mm/yy',
	});

	$('._currency').maskMoney({prefix:'R$ ', allowNegative: true, thousands:'.', decimal:',', affixesStay: false});
	$('._cep').mask('99999-999');
	$('._ano').mask('99/9999');
	$('._cpf').mask('999.999.999-99');
	$('._cnpj').mask('99.999.999/9999-99');
	$('._foneFixo').mask('(99) 9999-9999');

	$('#btn_add_campo').on('click', function(){
		var tr = $('#tbl_formacao').find('tr:last').clone();
		$('#tbl_formacao').append(tr);

		$('#tbl_formacao').find('tr:last').find('input').val('');
		$('._ano').mask('99/9999');
	});

	$('#btn_add_campo_curso').on('click', function(){
		var tr = $('#tbl_curso').find('tr:last').clone();
		$('#tbl_curso').append(tr);

		$('#tbl_curso').find('tr:last').find('input').val('');
		$('._ano').mask('99/9999');
	});
	
	$("._cel")
    	.mask("(99) 9999-9999?9")
    	.focusout(function (event) {  
	        var target, phone, element;  
	        target = (event.currentTarget) ? event.currentTarget : event.srcElement;  
	        phone = target.value.replace(/\D/g, '');
	        element = $(target);  
	        element.unmask();  
	        if(phone.length > 10) {  
	            element.mask("(99) 99999-999?9");  
	        } else {  
	            element.mask("(99) 9999-9999?9");  
	        }
    	});

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

	if ($('#status_cadastro').val() == 3 && $('#ajax').val() == 0){
		$('.divCadastroCandidato input,textarea,select').attr('disabled','disabled');
		$('#btn_add_campo').parent().remove();
	}

	$('.btnExcluirFormacao').on('click', function(){
		var id = $(this).attr('id');

		if (id == '')
		{
			$('#tr_'+id).find('input').val('');
			$('#tr_'+id).find('select').val(0);
			return false;
		}
		
		$.ajax({
			url: './cadastro_aprovados.php?excluirFormacao=1&id='+id,
			type: 'get',
			success: function(retorno){
				if (retorno == 1)
				{
					if ($('.trFormacao').length > 1)
					{
						$('#tr_'+id).remove();
					}
					else
					{
						$('#tr_'+id).find('input').val('');
						$('#tr_'+id).find('select').val(0);
					}
					alert('Formação excluída corretamente!');
				}
				else
				{
					alert('Houve uma falha ao tentar excluir a formação Nº '+id);
				}
			}
		});
	});

	$('.btnExcluirCurso').on('click', function(){
		var id = $(this).attr('id');

		if (id == '')
		{
			$('#tr_curso_').find('input').val('');
			$('#tr_curso_').find('select').val(0);
			return false;
		}
		
		$.ajax({
			url: './cadastro_aprovados.php?excluirCurso=1&id='+id,
			type: 'get',
			success: function(retorno){
				if (retorno == 1)
				{
					if ($('.trCursos').length > 1)
					{
						$('#tr_curso_'+id).remove();
					}
					else
					{
						$('#tr_curso_'+id).find('input').val('');
						$('#tr_curso_'+id).find('select').val(0);
					}
					alert('Curso excluído corretamente!');
				}
				else
				{
					alert('Houve uma falha ao tentar excluir o curso Nº '+id);
				}
			}
		});
	});

	$('.instituicaoEnsino').on('change', function(){
		el = $(this);
		if ($(this).val() == 999999)
		{
			var html = '<label class="labels">Digite o nome da instituição de ensino</labels><br />';
				html += '<input style="text-transform: uppercase" type="text" name="txtInstituicaoNome" size="45" id="txtInstituicaoNome" /><br /><br />';
				html += '<input onclick="cadastrarNovaInstituicao($(\'#txtInstituicaoNome\').val(), el);" type="button" class="class_botao" id="btnNovaInstituicao" name="btnNovaInstituicao" value="Cadastrar" />';
								
			modal(html,'pp','NOVA INSTITUIÇÃO DE ENSINO');
		}
	});
});

function cadastrarNovaInstituicao(nome, select){
	$.ajax({
		url: './cadastro_aprovados.php?cadastrarNovaInstituicao=1',
		type: 'post',
		data: {nome:nome},
		dataType: 'json',
		success: function(retorno){
			if (retorno[0] == 0)
			{
				alert('Não foi possível cadastrar esta instituição de ensino!');
			}
			else
			{
				$(select).append($('<option>', {
					value: retorno[1],
					text: nome
				}));

				$(select).val(retorno[1]);
				divPopupInst.destroi();
				alert('Instituição cadastrada corretamente');
			}
		}
	});
}
</script>

<?php
if (isset($_GET['rsh']))
{
	$sql = "SELECT
					*
				FROM
					".DATABASE.".candidatos
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
						SELECT * FROM ".DATABASE.".candidatos_cursos WHERE candidatos_cursos.reg_del = 0
					) ccu
					ON ccu_candidato_id = id
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
					rash = '".$_GET['rsh']."' AND candidatos.reg_del = 0 ";

	$dadosPessoais 		= array();
	$dadosPrincipais 	= array();
	$post 				= array();
	
	$db->select($sql, 'MYSQL',true);
	
	$dados = $db->array_select;
	
	foreach($dados as $reg)
	{
		//Empresa
		if (!isset($post['empresa']))
		{
			$nome = explode(' ', tiraacentos($reg['nome']));
			$arrNomeCompl = $nome;
			$login = $nome[0].'.'.array_pop($nome);
			$sigla = '';

			$arrExclusaoSigla = array('DE','DA','DO','DAS','DOS');
			
			//Criando a sigla padrão com a primeira letra de cada nome da pessoa
			foreach($arrNomeCompl as $n)
			{
				if (!in_array($n, $arrExclusaoSigla))
					$sigla .= $n[0];
			}
			
			$sigla = $sigla[0].$sigla[1].$sigla[2];
			
			$ext = in_array($reg['mod_contrato'], array('SC', 'SC+MENS')) ? 'ext.' : '';
			
			$post['devemada']= array(
				'email' 		=> !empty($reg['cdvm_email']) ? $reg['cdvm_email'] : $ext.$login.'@dominio.com.br',
				'login' 		=> !empty($reg['cdvm_login']) ? $reg['cdvm_login'] : $login,
				'sigla_func'	=> !empty($reg['cdvm_candidato_id']) ? $reg['cdvm_sigla'] : $sigla
			);
		}

		//Área Técnica Epis
		if (!isset($post['area_tecnica_epi']))
		{
			$post['area_tecnica_epi']= array(
				'tp_oculos' 		=> is_null($reg['ce_tp_oculos']) ? 1 : $reg['ce_tp_oculos'],
				'num_calcado' 		=> $reg['ce_num_calcado'],
				'tam_calca' 		=> $reg['ce_tam_calca'],
				'tam_jaleco' 		=> $reg['ce_tam_jaleco'],
				'tam_camisa' 		=> $reg['ce_tam_camisa']
			);
		}

		//Informações adicionais
		if (!isset($post['informacoes_adicionais']))
		{
			$post['informacoes_adicionais']= array(
				'disp_viagens' 		=> is_null($reg['cia_disp_viagens']) ? 1 : $reg['cia_disp_viagens'],
				'trab_outras_cid' 	=> is_null($reg['cia_disp_cidades']) ? 1 : $reg['cia_disp_cidades'],
				'trab_turno' 		=> is_null($reg['cia_disp_turnos']) ? 1 : $reg['cia_disp_turnos'],
				'vale_transp' 		=> is_null($reg['cia_vt']) ? 1 : $reg['cia_vt'],
				'qtd_vt_ida' 		=> $reg['cia_qtd_ida'],
				'qtd_vt_volta' 		=> $reg['cia_qtd_volta'],
				'val_vt_ida' 		=> 'R$ '.number_format($reg['cia_val_ida'],2,',','.'),
				'val_vt_volta' 		=> 'R$ '.number_format($reg['cia_val_volta'],2,',','.'),
			);
		}

		//Emprego anterior
		if (!isset($post['emprego_anterior']))
		{
			$post['emprego_anterior']= array(
				'empresa' => $reg['cea_empresa'],	
				'fone' => $reg['cea_fone'],
				'endereco' => $reg['cea_endereco'],
				'cidade' => $reg['cea_cidade'],
				'uf' => $reg['cea_uf'],
				'cargo' => $reg['cea_cargo'],
				'admissao' => $reg['cea_admissao'],
				'demissao' => $reg['cea_demissao'],
				'sal_ini' => 'R$ '.number_format($reg['cea_sal_ini'],2,',','.'),
				'sal_fim' => 'R$ '.number_format($reg['cea_sal_fim'],2,',','.'),
				'descricao' => $reg['cea_descricao'],
				'mot_saida' => $reg['cea_mot_saida']
			);
		}

		//Formação
		$post['formacao'][$reg['cf_id']] = array(
			'id' => $reg['cf_id'],	
			'curso' => $reg['cf_curso'],
			'instituicao_ensino' => $reg['cf_instituicao'],
			'descricao_formacao' => $reg['cf_descricao'],
			'mes_inicio' => $reg['cf_mes_inicio'],
			'mes_conclusao' => $reg['cf_mes_conclusao'],
			'completo' => $reg['cf_completou'],
			'ate_serie' => $reg['cf_serie']
		);

		//cursos
		$post['cursos'][$reg['ccu_id']] = array(
			'id' => $reg['ccu_id'],	
			'curso' => $reg['ccu_curso'],
			'instituicao_ensino' => $reg['ccu_instituicao'],
			'mes_inicio' => $reg['ccu_mes_inicio'],
			'mes_conclusao' => $reg['ccu_mes_conclusao'],
			'nivel' => $reg['ccu_nivel']
		);

		//Documentos
		if (!isset($post['documentos']))
		{
			$cpfNum = !empty($reg['cd_cpf']) ? $reg['cd_cpf'] : $reg['cpf'];
				
			$post['documentos'] = array(
				'ctps_num' => $reg['cd_ctps'],
				'ctps_serie' => $reg['cd_ctps_serie'],
				'ctps_data_emissao' => $reg['cd_ctps_emissao'],
				'identidade_num' => $reg['cd_rg'],
				'identidade_emissor' => $reg['cd_rg_orgao'],
				'data_emissao' => $reg['cd_rg_emissao'],
				'cpf_num' => $cpfNum,
				'titulo_eleitor' => $reg['cd_titulo_eleitor'],
				'titulo_zona' => $reg['cd_titulo_zona'],
				'titulo_secao' => $reg['cd_titulo_secao'],
				'pis_numero' => $reg['cd_pis'],
				'reservista_num' => $reg['cd_reservista'],
				'reservista_serie' => $reg['cd_reservista_serie'],
				'cidade' => $reg['cd_reservista_cidade'],
				'cnpj' => $reg['cd_cnpj'],
				'empresa' => $reg['cd_empresa'],
				'tipo_tributacao' => $reg['cd_opcao']
			);
		}

		//Dados Principais
		if (empty($dadosPrincipais))
		{
			$dadosPrincipais = array(
				'id' => $reg['id'],
				'nome' => $reg['nome'],
				'email'=> $reg['email'],
				'cpf'=> $reg['cpf'],
				'status'=> $reg['status'],
				'rash'=> $reg['rash'],
				'salario_pretendido'=> 'R$ '.number_format($reg['salario_pretendido'],2,',','.'),
				'cargo_pretendido'=> $reg['cargo_pretendido'],
				'data_inicio'	=> !empty($reg['data_inicio']) ? mysql_php($reg['data_inicio']) : '',
				'centro_custo'	=> !empty($reg['centro_custo']) ? $reg['centro_custo'] : '',
				'tipo_salario' => $reg['tipo_salario']
				);
		}

		//Dados Pessoais
		if (empty($dadosPessoais))
		{
			$dadosPessoais = array(
			 	'cdp_nacionalidade' => $reg['cdp_nacionalidade'],
				'cdp_naturalidade' 	=> $reg['cdp_naturalidade'],
				'cdp_uf_nasc' 		=> $reg['cdp_uf_nasc'],
				'cdp_sexo' 			=> $reg['cdp_sexo'],
				'cdp_idade' 		=> $reg['cdp_idade'],
				'cdp_data_nasc' 	=> $reg['cdp_data_nasc'],
				'cdp_endereco' 		=> $reg['cdp_endereco'],
				'cdp_bairro' 		=> $reg['cdp_bairro'],
				'cdp_cidade' 		=> $reg['cdp_cidade'],
				'cdp_uf' 			=> $reg['cdp_uf'],
				'cdp_cep' 			=> $reg['cdp_cep'],
				'cdp_fone' 			=> $reg['cdp_fone'],
				'cdp_cel' 			=> $reg['cdp_cel'],
				'cdp_fone_recados' 	=> $reg['cdp_fone_recados'],
				'cdp_est_civil' 	=> $reg['cdp_est_civil'],
				'cdp_data_casamento' => $reg['cdp_data_casamento'],
				'cdp_n_filhos' 		=> $reg['cdp_n_filhos'],
				'cdp_nome_conjuge' 	=> $reg['cdp_nome_conjuge'],
				'cdp_nome_pai' 		=> $reg['cdp_nome_pai'],
				'cdp_nome_mae' 		=> $reg['cdp_nome_mae'],
				'cdp_peso' 			=> $reg['cdp_peso'],
				'cdp_tp_sangue' 	=> $reg['cdp_tp_sangue'],
				'cdp_altura' 		=> $reg['cdp_altura'],
				'cdp_etnia' 		=> $reg['cdp_etnia'],
				'cdp_banco' 		=> $reg['cdp_banco'],
				'cdp_cc' 			=> $reg['cdp_cc'],
				'cdp_agencia' 		=> $reg['cdp_agencia'],
				'cdp_tp_contrato' 	=> $reg['cdp_tp_contrato']
			);
		}		
	}

	$smarty->assign("dados_pessoais",$dadosPessoais);
	$smarty->assign("dados_principais", $dadosPrincipais);
	$smarty->assign("post", $post);

	$smarty->assign('rsh', $_GET['rsh']);
}

//Trazendo os dados das tabelas UF, Nacionalidade, estado Civil e Grau de Instrução
$cont9 = ProtheusDao::getTabelaX5($db, true, '12,34,33,26');

foreach($cont9 as $regs)
{
	switch($regs['X5_TABELA'])
	{
	    //UF
	    case '12';
	    $array_uf_values[] = trim($regs["X5_CHAVE"]);
	    break;
		case '34';
		$array_nacionalidade_values[] = trim($regs["X5_CHAVE"]);
		$array_nacionalidade_output[] = maiusculas($regs["X5_DESCRI"]);
		break;
		//ESTADO CIVIL
		case '33';
		$array_est_civ_values[] = trim($regs["X5_CHAVE"]);
		$array_est_civ_output[] = maiusculas($regs["X5_DESCRI"]);
		break;
		//GRAU INSTRUCAO
		case '26';
		$array_instrucao_values[] = trim($regs["X5_CHAVE"]);
		$array_instrucao_output[] = maiusculas($regs["X5_DESCRI"]);
		break;
	}
}

$smarty->assign("option_uf_values",$array_uf_values);

$smarty->assign("option_nacionalidade_values",$array_nacionalidade_values);
$smarty->assign("option_nacionalidade_output",$array_nacionalidade_output);

$smarty->assign("option_est_civ_values",$array_est_civ_values);
$smarty->assign("option_est_civ_output",$array_est_civ_output);
$smarty->assign("estado_civil_selecionado",$dados[0]['cdp_est_civil']);

$array_instituicao_values[] = "";
$array_instituicao_output[] = "SELECIONE";

$array_instituicao_values[] = "999999";
$array_instituicao_output[] = "OUTRA";

$sql = "SELECT * FROM ".DATABASE.".rh_instituicao_ensino ";
$sql .= "WHERE rh_instituicao_ensino.reg_del = 0 ";
$sql .= "ORDER BY instituicao_ensino ";

$db->select($sql,'MYSQL',true);

foreach($db->array_select as $regs)
{
	$array_instituicao_values[] = $regs["id_rh_instituicao_ensino"];
	$array_instituicao_output[] = $regs["instituicao_ensino"];
}

$smarty->assign("option_instituicao_values",$array_instituicao_values);
$smarty->assign("option_instituicao_output",$array_instituicao_output);

$array_bancos_values[] = "";
$array_bancos_output[] = "SELECIONE";

$sql = "SELECT * FROM ".DATABASE.".bancos ";
$sql .= "WHERE bancos.reg_del = 0 ";
$sql .= "ORDER BY instituicao ";

$db->select($sql,'MYSQL',true);

foreach($db->array_select as $contbancos)
{
	$array_bancos_values[] = $contbancos["id_banco"];
	$array_bancos_output[] = $contbancos["dv"]." - ".$contbancos["instituicao"];	
}

$smarty->assign("banco_selecionado",$dados[0]['cdp_banco']);

$smarty->assign("option_bancos_values",$array_bancos_values);
$smarty->assign("option_bancos_output",$array_bancos_output);

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

$ajax = 0;
//Esta parte só é executada de fora do programa principal ex; materiais/produtos.php
if (isset($_GET['ajax']))
{
	$smarty->assign('ocultarCabecalhoRodape', 'style="display:none;"');
	$ajax = 1;
}

$smarty->assign('ajax', $ajax);

$smarty->assign("revisao_documento","V3");


$smarty->assign("botao",$conf->botoes());

$smarty->assign("classe",CSS_FILE);

$smarty->display("cadastro_aprovados.tpl");
?>