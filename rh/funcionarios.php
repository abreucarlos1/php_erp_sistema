<?php
/*
	Formulario de Funcionarios
	
	Criado por Carlos Abreu  
	
	local/Nome do arquivo:
	../rh/funcionarios.php
	
	Versao 0 --> VERSAO INICIAL : 17/07/2007
	Versao 1 --> Atualizacao de Lay-out | Smarty : 05/08/2008
	Versao 2 --> atualizacao de funcoes - microsiga : 25/05/2009
	Versao 3 --> Acrescentado permissoes novos / status de empresa socio - 04/09/2012
	Versao 4 --> Acrescentado campo de foto de colaboradores - Carlos
	Versao 5 --> Inclusao da formacao academica - Carlos
	Versao 6 --> Alteracao para exportacao para o protheus - erro no job na revisao_documento 11 - 12/06/2013 - Carlos
	Versao 7 --> Inclusao do campo nome_usuario para utilizacao do GED
	Versao 8 --> Inclusao de novas permissoes aos modulos - 15/08/2014 - Carlos Abreu
	Versao 9 --> Inclusao da aba Infra TI - 18/08/2014 - Carlos Abreu
	Versao 10 --> Inclusão do campo de siglas na tabela e no módulo de funcionários
	Versão 11 --> atualização layout - Carlos Abreu - 05/04/2017
	Versão 12 --> Inclusão dos campos reg_del nas consultas - 28/11/2017 - Carlos Abreu 
*/

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php'))); //ok
	
require_once(INCLUDE_DIR."include_form.inc.php"); //ok

require_once(INCLUDE_DIR."encryption.inc.php"); //ok

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO
//previne contra acesso direto	
if(!verifica_sub_modulo(92))
{
	nao_permitido();
}

$conf = new configs();

function voltar()
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados();

	$resposta->addScript("sub_campo();");

	$resposta->addScript("xajax.$('frm_funcionarios').reset(); ");

	$resposta->addAssign("data_admissao","readonly",true);
	$resposta->addAssign("salario_inicial","disabled",true);
	$resposta->addAssign("horario_entrada","disabled",true);
	$resposta->addAssign("horario_saida","disabled",true);
	$resposta->addAssign("horario_refeicao","disabled",true);
	$resposta->addAssign("descanso_semanal","disabled",true);
	$resposta->addAssign("demissao","disabled",true);
	$resposta->addAssign("visu","innerHTML","");

	$resposta->addAssign("protheusModulos","innerHTML","");
	$resposta->addAssign("dvmsysModulos","innerHTML","");
	$resposta->addAssign("outrosSoftwares","innerHTML","");

	$resposta->addScript("$('#tableAdicionais tr:not(:first)').remove();");
	$resposta->addScript("xajax_getAjudaCustoAdicional();");
	
	$sql = "SELECT MAX(numero_contrato) AS proximoContrato FROM ".DATABASE.".pj_contratos ";
	$sql .= "WHERE pj_contratos.reg_del = 0 ";
	
	$db->select($sql, 'MYSQL',true);
	
	$proximoContrato = $db->array_select[0];
	
	$nContrato = substr_replace($proximoContrato['proximoContrato'], '', -4, 4);
	
	$anoContrato = intval(substr($proximoContrato['proximoContrato'], -4));
	
	$resposta->addAssign("contratoColaboradorNumero","value",$nContrato+1);
	
	$resposta->addScript("seleciona_combo('".$anoContrato."', 'contratoColaboradorAno'); ");
	
	$resposta->addAssign("btninserir","value","Inserir");

	$resposta->addEvent("btninserir","onclick","xajax_insere(xajax.getFormValues('frm_funcionarios')); ");

	$resposta->addEvent("btnvoltar", "onclick", "history.back();");

	return $resposta;
}

function atualizatabela($filtro='', $dados_form='')
{
	$resposta = new xajaxResponse();
	
	$xml = new XMLWriter();

	$db = new banco_dados;

	$sql_filtro = "";

	$sql_texto = "";

	if($dados_form['busca']!="")
	{
	    $sql_texto = str_replace('  ', ' ', AntiInjection::clean($dados_form['busca']));
		
		$sql_texto = str_replace(' ', '%', '%'.$sql_texto.'%');

		$sql_filtro = "AND (funcionarios.funcionario LIKE '".$sql_texto."' ";
		$sql_filtro .= "OR rh_funcoes.descricao LIKE '".$sql_texto."' ";
		$sql_filtro .= "OR setores.setor LIKE '".$sql_texto."' ";
		$sql_filtro .= "OR salarios.tipo_contrato LIKE '".$sql_texto."' ";
		$sql_filtro .= "OR local.descricao LIKE '".$sql_texto."' ";
		$sql_filtro .= "OR funcionarios.funcionario_cidade LIKE '".$sql_texto."' ";
		$sql_filtro .= "OR empresa_funcionarios.empresa_func LIKE '".$sql_texto."' ";
		$sql_filtro .= "OR funcionarios.sigla_func LIKE '".$sql_texto."' ";
		$sql_filtro .= "OR rh_formacao.descricao LIKE '".$sql_texto."' ";
		$sql_filtro .= "OR funcionarios.situacao LIKE '".$sql_texto."') ";
	}
	
	if($dados_form['exibir']!='')
	{
		$sql_filtro .= "AND funcionarios.situacao = '".$dados_form["exibir"]."' ";
	}

	$conteudo = "";

	$sql = "SELECT 
				rh_funcoes.descricao AS descricao, funcionarios.id_funcionario AS id_funcionario,
  				setor, funcionario, setor_aso, situacao, empresa_func,
  				envio_microsiga, nivel_atuacao, arquivo_foto, sigla_func,cpf
			FROM ".DATABASE.".rh_funcoes, ".DATABASE.".funcionarios ";
	$sql .= "LEFT JOIN ".DATABASE.".setores ON (funcionarios.id_setor = setores.id_setor AND setores.reg_del = 0) ";
	$sql .= "LEFT JOIN ".DATABASE.".empresa_funcionarios ON (funcionarios.id_empfunc = empresa_funcionarios.id_empfunc AND empresa_funcionarios.reg_del = 0) ";
	$sql .= "LEFT JOIN ".DATABASE.".salarios ON (funcionarios.id_salario = salarios.id_salario AND salarios.reg_del = 0) ";
	$sql .= "LEFT JOIN ".DATABASE.".local ON (funcionarios.id_local = local.id_local AND local.reg_del = 0) ";
	$sql .= "LEFT JOIN ".DATABASE.".setor_aso ON (funcionarios.id_setor_aso = setor_aso.id_setor_aso AND setor_aso.reg_del = 0) ";
	$sql .= "LEFT JOIN ".DATABASE.".rh_formacao ON (funcionarios.id_funcionario = rh_formacao.id_funcionario AND rh_formacao.reg_del = 0) ";
	$sql .= "WHERE funcionarios.id_funcao = rh_funcoes.id_funcao ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	$sql .= "AND rh_funcoes.reg_del = 0 ";
	$sql .= "AND funcionarios.situacao NOT IN('CANCELADOCLIENTE','CANCELADOCANDIDATO','CANCELADO')";
	$sql .= $sql_filtro;
	$sql .= "GROUP BY funcionarios.id_funcionario ";
	$sql .= "ORDER BY funcionario";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	else
	{		
		$xml->openMemory();
		$xml->setIndent(false);
		$xml->startElement('rows');

		foreach($db->array_select as $reg)
		{
			$xml->startElement('row');
			$xml->writeAttribute('id', $reg['id_funcionario']);
			$xml->writeElement('cell', '<a class="tooltip" onmouseover=exibe_foto(this); onmouseout=oculta_foto(this);><span><img style="display:none;position:absolute;" src="fotos/'.$reg['arquivo_foto'].'" />'.$reg["funcionario"].'</span></a>');

			$xml->writeElement('cell', $reg['sigla_func']);
			$xml->writeElement('cell', $reg['descricao']);
			$xml->writeElement('cell', $reg['setor']);
			$xml->writeElement('cell', $reg['setor_aso']);
			$xml->writeElement('cell', $reg['situacao']);
			$xml->writeElement('cell', $reg['empresa_func']);

			if($reg["envio_microsiga"]==0 && $reg["nivel_atuacao"]!='P')
			{
				$xml->writeElement('cell', '<img src="'.DIR_IMAGENS.'bt_salvar.png" onclick=xajax_envia_microsiga("'.$reg["id_funcionario"].'"); />');
			}
			else
			{
				$xml->writeElement('cell', ' ');
			}
			
			$conteudo = $divMotivoNR."<img style=\'cursor:pointer;\' ".$complAcoesNr." src=\'".$img."\'>";
			
			$xml->writeElement('cell', $conteudo);

			$xml->endElement();
		}
		
		$xml->endElement();
		
		$conteudo = $xml->outputMemory(false);

		$resposta->addScript("grid('div_funcionarios', true, '290', '".$conteudo."');");
	}

	$resposta->addEvent("frm_funcionarios", "onsubmit", "xajax.upload('insere','frm_funcionarios');");
	$resposta->addScript("document.getElementById('alteracaoExigencias').value = 0");
	$resposta->addScript("habilitarNumeroContrato();");

	return $resposta;
}

function editar($id)
{
	$resposta = new xajaxResponse();
	
	$resposta->addScript("sub_campo();");

	$resposta->addScript("xajax.$('frm_funcionarios').reset(); ");

	$resposta->addAssign("id_funcionario", "value", "");

	$resposta->addScript("$('#data_admissao').attr('readonly','readonly');");
	
	$resposta->addScript("$('#salario_inicial').attr('readonly','readonly');");
	
	$resposta->addScript("$('#horario_entrada').attr('readonly','readonly');");
	
	$resposta->addScript("$('#horario_saida').attr('readonly','readonly');");
	
	$resposta->addScript("$('#horario_refeicao').attr('readonly','readonly');");
	
	$resposta->addScript("$('#descanso_semanal').attr('readonly','readonly');");
	
	$resposta->addScript("$('#demissao').attr('readonly','readonly');");
	
	$resposta->addAssign("visu","innerHTML","");

	$resposta->addAssign("protheusModulos","innerHTML","");
	$resposta->addAssign("dvmsysModulos","innerHTML","");
	$resposta->addAssign("outrosSoftwares","innerHTML","");

	$resposta->addScript("$('#tableAdicionais tr:not(:first)').remove();");
	$resposta->addScript("xajax_getAjudaCustoAdicional();");

	$resposta->addScript("document.getElementById('alteracaoExigencias').value = 0");
	
	$db = new banco_dados;

	//Seleciona os dados da requisicao escolhida
	$sql = "SELECT * FROM ".DATABASE.".rh_funcoes, ".DATABASE.".funcionarios ";
	$sql .= "LEFT JOIN ".DATABASE.".usuarios ON (usuarios.id_usuario = funcionarios.id_usuario AND usuarios.reg_del = 0) ";
	$sql .= "LEFT JOIN ".DATABASE.".setor_aso ON (funcionarios.id_setor_aso = setor_aso.id_setor_aso AND setor_aso.reg_del = 0) ";
	$sql .= "LEFT JOIN ".DATABASE.".setores ON funcionarios.id_setor = setores.id_setor AND setores.reg_del = 0 ";
	$sql .= "LEFT JOIN (
				SELECT id_funcionario contratoFuncionario, numero_contrato
				FROM ".DATABASE.".pj_contratos
				WHERE pj_contratos.reg_del = 0
				AND pj_contratos.id_funcionario = '".$id."'
			) contrato_pj
			ON contratoFuncionario = funcionarios.id_funcionario ";	
	$sql .= "LEFT JOIN (
			  SELECT id_funcionario codFun, refeicaoId, transporteId, hotelId, data_inicio data_inicio_contrato, data_fim data_fim_contrato, id_local_trabalho, numero_contrato_cliente, numero_os 
			  	FROM ".DATABASE.".cliente_exigencias
			  JOIN(
				SELECT id_adicional refeicaoId FROM ".DATABASE.".rh_adicional WHERE rh_adicional.reg_del = 0
			  ) refeicao
			  ON refeicaoId = id_adicional_refeicao

			  JOIN(
				SELECT id_adicional transporteId FROM ".DATABASE.".rh_adicional WHERE rh_adicional.reg_del = 0
			  ) transporte
			  ON transporteId = id_adicional_transporte
			  
			  JOIN(
				SELECT id_adicional hotelId FROM ".DATABASE.".rh_adicional WHERE rh_adicional.reg_del = 0
			  ) hotel
			  ON hotelId = id_adicional_hotel

			  WHERE cliente_exigencias.reg_del = 0
			  AND cliente_exigencias.id_funcionario = '" . $id . "'
			  ) pj_contratos ON codFun = funcionarios.id_funcionario ";

	$sql .= "WHERE funcionarios.id_funcao = rh_funcoes.id_funcao ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	$sql .= "AND rh_funcoes.reg_del = 0 ";
	$sql .= "AND funcionarios.id_funcionario = '" . $id . "' ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	else
	{
		$reg_editar = $db->array_select[0];

		//Assigna os campos para edicao
		$resposta->addAssign("id_funcionario", "value", $id);
		$resposta->addScript("seleciona_combo('0', 'empresa_funcionario'); ");
		$resposta->addAssign("funcionario","value",$reg_editar["funcionario"]);
		$resposta->addAssign("endereco","value",$reg_editar["funcionario_endereco"]);
		$resposta->addAssign("bairro","value",$reg_editar["funcionario_bairro"]);
		$resposta->addAssign("cidade","value",$reg_editar["funcionario_cidade"]);
		$resposta->addAssign("cep","value",$reg_editar["funcionario_cep"]);
		$resposta->addAssign("email_particular","value",$reg_editar["email_particular"]);
		$resposta->addScript("seleciona_combo('" . $reg_editar["funcionario_estado"]. "','estado'); ");
		$resposta->addAssign("pai","value",$reg_editar["filiacao_pai"]);
		$resposta->addAssign("mae","value",$reg_editar["filiacao_mae"]);
		$resposta->addAssign("nacionalidade_pai","value",$reg_editar["nacionalidade_pai"]);
		$resposta->addAssign("nacionalidade_mae","value",$reg_editar["nacionalidade_mae"]);
		$resposta->addAssign("telefone","value",$reg_editar["telefone"]);
		$resposta->addAssign("celular","value",$reg_editar["celular"]);
		$resposta->addAssign("ctps_num","value",$reg_editar["ctps_num"]);
		$resposta->addAssign("ctps_serie","value",$reg_editar["ctps_serie"]);
		$resposta->addAssign("reservista_num","value",$reg_editar["reservista_num"]);
		$resposta->addAssign("reservista_categoria","value",$reg_editar["reservista_categoria"]);
		$resposta->addAssign("titulo_eleitor","value",$reg_editar["titulo_eleitor"]);
		$resposta->addAssign("titulo_zona","value",$reg_editar["titulo_zona"]);
		$resposta->addAssign("titulo_secao","value",$reg_editar["titulo_secao"]);
		$resposta->addAssign("identidade_num","value",$reg_editar["identidade_num"]);
		$resposta->addAssign("identidade_emissor","value",$reg_editar["identidade_emissor"]);
		$resposta->addAssign("data_emissao","value",mysql_php($reg_editar["data_emissao"]));
		$resposta->addAssign("cpf_num","value",$reg_editar["cpf"]);
		$resposta->addAssign("visu","innerHTML","<img src=\"fotos/".$reg_editar["arquivo_foto"]." \" />");
		$resposta->addScript("seleciona_combo('" . $reg_editar["id_nacionalidade"] . "', 'id_nacionalidade'); ");
		$resposta->addAssign("naturalidade","value",$reg_editar["naturalidade"]);
		$resposta->addScript("seleciona_combo('" . $reg_editar["estado_nascimento"] . "', 'estado_nasc'); ");
		$resposta->addAssign("data_nascimento","value",mysql_php($reg_editar["data_nascimento"]));

		$idade = floor(numero_meses($reg_editar["data_nascimento"],date('Y-m-d'))/12);

		$resposta->addAssign("idade","value",$idade);
		$resposta->addScript("seleciona_combo('" . $reg_editar["id_estado_civil"] . "', 'estado_civil'); ");
		$resposta->addAssign("conjuge","value",$reg_editar["conjuge"]);
		$resposta->addScript("seleciona_combo('" . $reg_editar["id_escolaridade"] . "', 'grau_instrucao'); ");
		$resposta->addAssign("cor","value",$reg_editar["cor"]);
		$resposta->addScript("seleciona_combo('" . $reg_editar["sexo"] . "', 'sexo'); ");
		$resposta->addAssign("cabelos","value",$reg_editar["cabelo"]);
		$resposta->addAssign("olhos","value",$reg_editar["olhos"]);
		$resposta->addAssign("altura","value",$reg_editar["altura"]);
		$resposta->addAssign("peso","value",$reg_editar["peso"]);
		$resposta->addAssign("sigla_func","value",$reg_editar["sigla_func"]);
		$nContrato = substr_replace($reg_editar['numero_contrato'], '', -4, 4);
		$anoContrato = substr($reg_editar['numero_contrato'], -4); 
		$resposta->addAssign("contratoColaboradorNumero","value",$nContrato);
		$resposta->addScript("seleciona_combo('".$anoContrato."', 'contratoColaboradorAno'); ");
		$reg_editar['tipo_empresa'] = $reg_editar['tipo_empresa'] == 0 ? '' : $reg_editar['tipo_empresa'];
		$resposta->addScript("seleciona_combo('".$reg_editar['tipo_empresa']."', 'tipo_tributacao'); ");
		$resposta->addScript("seleciona_combo('".$reg_editar['refeicaoId']."', 'refeicao'); ");
		$resposta->addScript("seleciona_combo('".$reg_editar['transporteId']."', 'transporte'); ");
		$resposta->addScript("seleciona_combo('".$reg_editar['hotelId']."', 'hotel'); ");
		$resposta->addAssign("contratoDe","value",mysql_php($reg_editar["data_inicio_contrato"]));
		$resposta->addAssign("contratoAte","value",mysql_php($reg_editar["data_fim_contrato"]));

		$reg_editar["numero_contrato_cliente"] = intval($reg_editar["numero_contrato_cliente"]) > 0 ? $reg_editar["numero_contrato_cliente"] : '';
		$resposta->addAssign("numeroContrato","value",$reg_editar["numero_contrato_cliente"]);
		$resposta->addScript("seleciona_combo('".$reg_editar['numero_os']."', 'os'); ");
		$resposta->addScript("seleciona_combo('".$reg_editar["id_local"]."', 'empresa'); ");
		
		//preenche os campos de dependentes
		for($i=1;$i<=6;$i++)
		{
			$resposta->addAssign("nome_dep".$i,"value","");
			$resposta->addAssign("data_dep".$i,"value","");
			$resposta->addAssign("parentesco_dep".$i,"value","");
		}

		$i = 1;

		$sql = "SELECT * FROM ".DATABASE.".dependentes_funcionarios ";
		$sql .= "WHERE dependentes_funcionarios.id_funcionario = '" . $id . "' ";
		$sql .= "ORDER BY dependentes_funcionarios.id_dependente_funcionario ";

		$db->select($sql,'MYSQL',true);

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
		else
		{
			//preenche os campos de dependentes
			foreach($db->array_select as $reg)
			{
				$resposta->addAssign("nome_dep".$i,"value",$reg["nome_dependente"]);
				$resposta->addAssign("data_dep".$i,"value",mysql_php($reg["data_nascimento"]));
				$resposta->addAssign("parentesco_dep".$i,"value",$reg["parentesco"]);

				$i++;
			}
		}
		
		$resposta->addScript("xajax_funcoes('".$reg_editar["id_cargo_grupo"]."','".$reg_editar["id_funcao"]."');");//id_cargo_grupo vem do join
		$resposta->addScript("seleciona_combo('" . $reg_editar["id_cargo"] . "', 'cargo_dvm'); ");//id_cargo vem do funcionário, porém, deve ser igual ao id_cargo_grupo		
		$resposta->addScript("seleciona_combo('" . $reg_editar["nivel_atuacao"] . "', 'nivel_atuacao'); ");
		$resposta->addScript("seleciona_combo('" . $reg_editar["id_setor"] . "', 'setor'); ");
		$resposta->addScript("seleciona_combo('" . $reg_editar["id_setor_aso"] . "', 'setor_aso'); ");
		$resposta->addScript("seleciona_combo('" . $reg_editar["id_local"] . "', 'local_trabalho'); ");
		$resposta->addScript("seleciona_combo('" . $reg_editar["tipo_sanguineo"] . "', 'tipo_sanguineo'); ");

		//Seleciona os dados do salario
		$sql = "SELECT * FROM ".DATABASE.".salarios ";
		$sql .= "WHERE salarios.id_funcionario = '" . $id . "' ";
		$sql .= "AND salarios.reg_del = 0 ";
		$sql .= "ORDER BY salarios.data DESC, salarios.id_salario DESC LIMIT 1 ";

		$db->select($sql,'MYSQL',true);

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
		else
		{
			$reg_sal = $db->array_select[0];
		}

		//Seleciona os dados da admissao
		$sql = "SELECT data FROM ".DATABASE.".salarios ";
		$sql .= "WHERE salarios.id_funcionario = '" . $id . "' ";
		$sql .= "AND salarios.reg_del = 0 ";
		$sql .= "ORDER BY salarios.data ASC, salarios.id_salario ASC LIMIT 1 ";

		$db->select($sql,'MYSQL',true);

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
		else
		{
			$reg_date = $db->array_select[0];
		}

		$resposta->addAssign("salario_inicial","value",number_format($reg_sal["salario_clt"],2,",","."));
		$resposta->addAssign("salario_mensal","value",number_format($reg_sal["salario_mensalista"],2,",","."));
		$resposta->addAssign("salario_hora","value",number_format($reg_sal["salario_hora"],2,",","."));
		$resposta->addScript("seleciona_combo('" . $reg_sal["id_tipo_salario"] . "', 'tipo_salario'); ");
		$resposta->addAssign("data_admissao","value",mysql_php($reg_date["data"]));
		$resposta->addScript("seleciona_combo('" . $reg_sal["tipo_contrato"] . "', 'tipo_contrato'); ");
		$resposta->addScript("seleciona_combo('" . $reg_editar["situacao"] . "', 'situacao'); ");
		$resposta->addScript("seleciona_combo('" . $reg_editar["id_empfunc"] . "', 'empresa_dvm_funcionario'); ");
		$resposta->addScript("seleciona_combo('" . $reg_editar["id_cod_fornec"] . "', 'empresa_funcionario'); ");
		$resposta->addAssign("data_inicio","value",mysql_php($reg_editar["data_inicio"]));

		if(in_array($reg_editar["situacao"], array("DESLIGADO","FECHAMENTO FOLHA", "CANCELADOCLIENTE", "CANCELADOCANDIDATO", "CANCELADO")))
		{
	
			$resposta->addAssign("demissao","disabled",false);
		}

		$resposta->addAssign("data_desligamento","value",mysql_php($reg_editar["data_desligamento"]));
		$resposta->addScript("seleciona_combo('" . $reg_editar["tipo_demissao"] . "', 'demissao'); ");
		$resposta->addScript("seleciona_combo('" . $reg_editar["id_centro_custo"] . "', 'centrocusto'); ");
		$resposta->addScript("seleciona_combo('" . $reg_editar["id_produto"] . "', 'produto'); ");
		$resposta->addScript("seleciona_combo('" . $reg_editar["item_contabil"] . "', 'site'); ");
		$resposta->addAssign("email","value",minusculas($reg_editar["email"]));
		$resposta->addAssign("login","value",$reg_editar["login"]);

		// Se CLT
		if($reg_sal[" tipo_contrato"]=="EST" || $reg_sal[" tipo_contrato"]=="CLT" || $reg_sal[" tipo_contrato"]=="SC+CLT" || $reg_sal[" tipo_contrato"]=="SC+CLT+MENS")
		{
			$resposta->addAssign("clt_matricula","value",$reg_editar["clt_matricula"]);
			$resposta->addScript("seleciona_combo('" . $reg_editar["id_categoria_funcional"] . "', 'categoria_funcional'); ");
			$resposta->addScript("seleciona_combo('" . $reg_editar["id_tipo_pagamento"] . "', 'tipo_pagamento'); ");
			$resposta->addScript("seleciona_combo('" . $reg_editar["id_vinculo_empregaticio"] . "', 'vinculo_empregaticio'); ");
			$resposta->addScript("seleciona_combo('" . $reg_editar["id_tipo_admissao"] . "', 'tipo_admissao'); ");
			$resposta->addScript("seleciona_combo('" . $reg_editar["id_turno_trabalho"] . "', 'turno_trabalho'); ");
			$resposta->addAssign("horario_entrada","value",$reg_editar["horario_entrada"]);
			$resposta->addAssign("horario_refeicao","value",$reg_editar["refeicao"]);
			$resposta->addAssign("horario_saida","value",$reg_editar["horario_saida"]);
			$resposta->addAssign("descanso_semanal","value",$reg_editar["descanso_semanal"]);
			
			$resposta->addScript("$('#salario_inicial').attr('readonly','readonly');");
			$resposta->addScript("$('#horario_entrada').attr('readonly','readonly');");
			$resposta->addScript("$('#horario_saida').attr('readonly','readonly');");
			$resposta->addScript("$('#horario_refeicao').attr('readonly','readonly');");
			$resposta->addScript("$('#descanso_semanal').attr('readonly','readonly');");
		}
		else
		{
			
			$resposta->addScript("$('#salario_inicial').attr('readonly','readonly');");
			$resposta->addScript("$('#horario_entrada').attr('readonly','readonly');");
			$resposta->addScript("$('#horario_saida').attr('readonly','readonly');");
			$resposta->addScript("$('#horario_refeicao').attr('readonly','readonly');");
			$resposta->addScript("$('#descanso_semanal').attr('readonly','readonly');");
		}

		$sql = "SELECT * FROM ".DATABASE.".rh_formacao ";
		$sql .= "WHERE rh_formacao.id_funcionario = '" . $id . "' ";
		$sql .= "AND rh_formacao.reg_del = 0 ";
		$sql .= "ORDER BY ano_conclusao DESC ";

		$db->select($sql,'MYSQL',true);

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
		else
		{
			if($db->numero_registros>0)
			{
				$itens = 0;

				$resposta->addScript("sub_campo();");

				foreach($db->array_select as $regs_form)
				{
					$resposta->addScript("seleciona_combo('" . $regs_form["id_instituicao"] . "', 'instituicao_ensino_".$itens."'); ");
					$resposta->addAssign("descricao_formacao_".$itens,"value",$regs_form["descricao"]);
					$resposta->addAssign("ano_conclusao_".$itens,"value",$regs_form["ano_conclusao"]);
					$resposta->addScript("add_campo();");

					$itens++;
				}
			}
			else
			{
				$resposta->addScript("seleciona_combo('', 'instituicao_ensino_0'); ");
				$resposta->addAssign("descricao_formacao_0","value","");
				$resposta->addAssign("ano_conclusao_0","value","");
			}
		}

		$resposta->addScript("desseleciona_combo('infra_ti');");
	
		$resposta->addScript("desseleciona_combo('softwares_ti');");
				
		// Seleciona as necessidades de equipamentos
		$sql = "SELECT * FROM ".DATABASE.".rh_necessidades_x_funcionario ";
		$sql .= "WHERE rh_necessidades_x_funcionario.id_funcionario = '" . $id . "' ";
		$sql .= "AND rh_necessidades_x_funcionario.reg_del = 0 ";

		$db->select($sql,'MYSQL',true);

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
		else
		{
			if($db->numero_registros>0)
			{
				foreach($db->array_select as $regs_form)
				{
					switch($regs_form['tipo_necessidade'])
					{
						case '1';
							$resposta->addScript("seleciona_combo('" . $regs_form["id_necessidade"] . "', 'infra_ti'); ");
						break;
						case '2';
							$resposta->addScript("seleciona_combo('" . $regs_form["id_necessidade"] . "', 'softwares_ti'); ");
						break;
						case '3';
							$resposta->addAssign('protheusModulos', 'innerHTML', $regs_form['outros']);
						break;
						case '4';
							$resposta->addAssign('dvmsysModulos', 'innerHTML', $regs_form['outros']);
						break;
						case '5';
							$resposta->addAssign('outrosSoftwares', 'innerHTML', $regs_form['outros']);
						break;
					}
				}
			}
			else
			{
				$resposta->addScript("seleciona_combo('7', 'infra_ti'); ");//Nenhum para equipamentos
				$resposta->addScript("seleciona_combo('13', 'softwares_ti'); ");//Nenhum para softwares
				$resposta->addAssign('protheusModulos', 'innerHTML', '');
				$resposta->addAssign('dvmsysModulos', 'innerHTML', '');
				$resposta->addAssign('outrosSoftwares', 'innerHTML', '');
				$resposta->addScript("seleciona_combo('', 'instituicao_ensino_0'); ");
				$resposta->addAssign("descricao_formacao_0","value","");
				$resposta->addAssign("ano_conclusao_0","value","");
			}
		}

		$resposta->addScript('xajax_getAjudaCustoAdicional('.$id.')');
		$resposta->addAssign("pis_data","value",mysql_php($reg_editar["pis_data"]));
		$resposta->addAssign("pis_numero","value",$reg_editar["pis_num"]);
		$resposta->addScript("seleciona_combo('" . $reg_editar["pis_banco"] . "', 'pis_banco'); ");
		$resposta->addAssign("fgts_data","value",mysql_php($reg_editar["fgts_data"]));
		$resposta->addAssign("fgts_conta","value",$reg_editar["fgts_conta"]);
		$resposta->addScript("seleciona_combo('" . $reg_editar["fgts_banco"] . "', 'fgts_banco'); ");
		$resposta->addAssign("fgts_agencia","value",$reg_editar["fgts_agencia"]);
		$resposta->addAssign("ref_transp_outros","value",$reg_editar["ref_transp_outros"]);
		$resposta->addAssign("btninserir","value","Atualizar");
		$resposta->addEvent("btnvoltar", "onclick", "xajax_voltar();");
		$resposta->addEvent("frm_funcionarios", "onsubmit", "xajax.upload('atualizar','frm_funcionarios');");
		$resposta->addScript("habilitarNumeroContrato();");
	}

	return $resposta;
}

function insere($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;

	if(trim($dados_form['btninserir']) == 'Atualizar')
	{
		return $resposta;
	}

	$enc = new Crypter(CHAVE);

	$senha = $enc->encrypt("123456");

	if($dados_form["tipo_contrato"]=="")
	{
		$resposta->addAlert("O tipo de contrato deve ser selecionado.");
	}
	else
	{
		if($dados_form["data_admissao"]=="")
		{
			$data_inicio = $dados_form["data_inicio"];
		}

		if($data_inicio=="")
		{
			$data_inicio = $dados_form["data_inicio"];
		}

		if($dados_form["funcionario"]!="" && $dados_form["login"]!="" && $dados_form["email"]!="" && $dados_form["setor"]!="" && $dados_form["infra_ti"]!="" && $dados_form["nivel_atuacao"]!="" && $dados_form["local_trabalho"]!="" && $dados_form["situacao"]!="" && $dados_form["funcao_dvm"]!="" && $dados_form["cargo_dvm"]!="")
		{
			//Verifica se o login fornecido existe no banco
			//Otavio - 16/08/2007
			$sql = "SELECT login FROM ".DATABASE.".usuarios ";
			$sql .= "WHERE login LIKE '" . $dados_form["login"] . "' ";
			$sql .= "AND usuarios.reg_del = 0 ";

			$db->select($sql,'MYSQL',true);

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}
			else
			{
				if($db->numero_registros==0)
				{
					$sql = "SELECT * FROM ".DATABASE.".bancos ";
					$sql .= "WHERE bancos.reg_del = 0 ";

					$db->select($sql,'MYSQL',true);

					if($db->erro!='')
					{
						$resposta->addAlert($db->erro);
					}
					else
					{
						foreach($db->array_select as $reg)
						{
							$bancos[$reg["id_banco"]] = $reg["dv"];
							$bancos_nome[$reg["id_banco"]] = $reg["instituicao"];
						}
					}

					//Insere o funcionario no banco  
					$isql = "INSERT INTO ".DATABASE.".funcionarios ";
					$isql .= "(id_setor, nivel_atuacao, id_funcao, id_cargo, id_setor_aso, funcionario, nome_usuario, email_particular, funcionario_endereco, funcionario_bairro, funcionario_cidade, ";
					$isql .= "funcionario_cep, funcionario_estado, filiacao_pai, filiacao_mae, nacionalidade_pai, ";
					$isql .= "nacionalidade_mae, ctps_num, ctps_serie, reservista_num, reservista_categoria, titulo_eleitor, titulo_zona, titulo_secao, identidade_num, ";
					$isql .= "identidade_emissor, data_emissao, cpf, naturalidade, id_nacionalidade, estado_nascimento, data_nascimento, id_empfunc, ";
					$isql .= "id_estado_civil, conjuge, id_escolaridade, clt_matricula, clt_admissao, id_categoria_funcional, id_tipo_pagamento, pis_data, pis_num, pis_banco, ";
					$isql .= "fgts_data, fgts_conta, fgts_banco, fgts_agencia, id_vinculo_empregaticio, id_tipo_admissao, id_turno_trabalho, ";
					$isql .= "horario_entrada, refeicao, horario_saida, descanso_semanal, cor, sexo, tipo_sanguineo, cabelo, olhos, altura, peso, id_local, ";
					$isql .= "celular, telefone, data_inicio, id_centro_custo, id_produto, item_contabil, id_cod_fornec, tipo_empresa, situacao, sigla_func, ref_transp_outros) ";
					$isql .= "VALUES (";
					$isql .= "'" . $dados_form["setor"] . "', ";
					$isql .= "'" . $dados_form["nivel_atuacao"] . "', ";
					$isql .= "'" . $dados_form["funcao_dvm"] . "', ";
					$isql .= "'" . $dados_form["cargo_dvm"] . "', ";
					$isql .= "'" . $dados_form["setor_aso"] . "', ";
					$isql .= "'" . maiusculas($dados_form["funcionario"]) . "', ";
					$isql .= "'" . minusculas($dados_form["login"]) . "', "; //salva o login no nome_usuario
					$isql .= "'" . minusculas($dados_form["email_particular"]) . "', ";
					$isql .= "'" . maiusculas($dados_form["endereco"]) . "', ";
					$isql .= "'" . maiusculas($dados_form["bairro"]) . "', ";
					$isql .= "'" . maiusculas($dados_form["cidade"]) . "', ";
					$isql .= "'" . $dados_form["cep"] . "', ";
					$isql .= "'" . maiusculas($dados_form["estado"]) . "', ";
					$isql .= "'" . maiusculas($dados_form["pai"]) . "', ";
					$isql .= "'" . maiusculas($dados_form["mae"]) . "', ";
					$isql .= "'" . maiusculas($dados_form["nacionalidade_pai"]) . "', ";
					$isql .= "'" . maiusculas($dados_form["nacionalidade_mae"]) . "', ";
					$isql .= "'" . $dados_form["ctps_num"] . "', ";
					$isql .= "'" . maiusculas($dados_form["ctps_serie"]) . "', ";
					$isql .= "'" . $dados_form["reservista_num"] . "', ";
					$isql .= "'" . maiusculas($dados_form["reservista_categoria"]) . "', ";
					$isql .= "'" . $dados_form["titulo_eleitor"] . "', ";
					$isql .= "'" . $dados_form["titulo_zona"] . "', ";
					$isql .= "'" . $dados_form["titulo_secao"] . "', ";
					$isql .= "'" . maiusculas($dados_form["identidade_num"]) . "', ";
					$isql .= "'" . maiusculas($dados_form["identidade_emissor"]) . "', ";
					$isql .= "'" . php_mysql($dados_form["data_emissao"]) . "', ";
					$isql .= "'" . $dados_form["cpf_num"] . "', ";
					$isql .= "'" . maiusculas($dados_form["naturalidade"]) . "', ";
					$isql .= "'" . $dados_form["id_nacionalidade"] . "', ";
					$isql .= "'" . $dados_form["estado"] . "', ";
					$isql .= "'" . php_mysql($dados_form["data_nascimento"]) . "', ";

					$isql .= "'" . $dados_form["empresa_dvm_funcionario"] . "', ";
					$isql .= "'" . $dados_form["estado_civil"] . "', ";
					$isql .= "'" . maiusculas($dados_form["conjuge"]) . "', ";
					$isql .= "'" . $dados_form["grau_instrucao"] . "', ";
					$isql .= "'" . sprintf("%06d",$dados_form["clt_matricula"]). "', ";
					$isql .= "'" . php_mysql($dados_form["data_admissao"]) . "', ";
					$isql .= "'" . $dados_form["categoria_funcional"] . "', ";
					$isql .= "'" . $dados_form["tipo_pagamento"] . "', ";
					$isql .= "'" . php_mysql($dados_form["pis_data"]) . "', ";
					$isql .= "'" . $dados_form["pis_numero"] . "', ";
					$isql .= "'" . $dados_form["pis_banco"] . "', ";

					$isql .= "'" . php_mysql($dados_form["fgts_data"]) . "', ";
					$isql .= "'" . $dados_form["fgts_conta"] . "', ";
					$isql .= "'" . $dados_form["fgts_banco"] . "', ";
					$isql .= "'" . $dados_form["fgts_agencia"] . "', ";

					$isql .= "'" . $dados_form["vinculo_empregaticio"] . "', ";
					$isql .= "'" . $dados_form["tipo_admissao"] . "', ";
					$isql .= "'" . $dados_form["turno_trabalho"] . "', ";
					$isql .= "'" . $dados_form["horario_entrada"] . "', ";
					$isql .= "'" . maiusculas($dados_form["horario_refeicao"]) . "', ";
					$isql .= "'" . $dados_form["horario_saida"] . "', ";
					$isql .= "'" . maiusculas($dados_form["descanso_semanal"]) . "', ";
					$isql .= "'" . maiusculas($dados_form["cor"]) . "', ";
					$isql .= "'" . $dados_form["sexo"] . "', ";
					$isql .= "'" . $dados_form["tipo_sanguineo"] . "', ";
					$isql .= "'" . maiusculas($dados_form["cabelos"]) . "', ";
					$isql .= "'" . maiusculas($dados_form["olhos"]) . "', ";
					$isql .= "'" . $dados_form["altura"] . "', ";
					$isql .= "'" . $dados_form["peso"] . "', ";
					$isql .= "'" . $dados_form["local_trabalho"] . "', ";
					$isql .= "'" . $dados_form["celular"] . "', ";
					$isql .= "'" . $dados_form["telefone"] . "', ";
					$isql .= "'" . php_mysql($dados_form["data_inicio"]) . "', ";
					$isql .= "'" . $dados_form["centrocusto"] . "', ";
					$isql .= "'" . $dados_form["produto"] . "', ";
					$isql .= "'" . $dados_form["site"] . "', ";
					$isql .= "'" . $dados_form["empresa_funcionario"] . "', ";
					$isql .= "'" . $dados_form["tipo_tributacao"] . "', ";
					$isql .= "'" . $dados_form["situacao"] . "', ";
					$isql .= "'" . maiusculas($dados_form["sigla_func"]) . "', ";
					$isql .= "'" . $dados_form["ref_transp_outros"] . "') ";

					$db->insert($isql,'MYSQL');

					if($db->erro!='')
					{
						$resposta->addAlert($db->erro);
					}
					else
					{
						$id_funcionario = $db->insert_id;
						/*
						 * Adicionado em 30/01/2015
						 * Carlos Máximo
						 */
						$dados_form['id_funcionario'] = $id_funcionario;						
						
						$erros = gravarNecessidadesColaborador($dados_form);

						if ($erros != '')
						{
							$resposta->addAlert($erros);
							
							return $resposta;
						}
						
						/*
						 * Finalizado
						 */						
						
						$arq_foto = redimensionar($_FILES["foto"],$id_funcionario,"");

						$usql = "UPDATE ".DATABASE.".funcionarios SET ";
						$usql .= "arquivo_foto = '" . $arq_foto . "' ";
						$usql .= "WHERE id_funcionario = '".$id_funcionario."' ";

						$db->update($usql,'MYSQL');

						if($db->erro!='')
						{
							$resposta->addAlert($db->erro);
						}

						//inclusao de formacao
						//01/03/2013
						for($i=0;$i<=$dados_form["itens"];$i++)
						{
							if($dados_form["instituicao_ensino_".$i]!="" && $dados_form["descricao_formacao_".$i]!="")
							{
								$isql = "INSERT INTO ".DATABASE.".rh_formacao ";
								$isql .= "(id_funcionario, id_instituicao, descricao, ano_conclusao) ";
								$isql .= "VALUES (";
								$isql .= "'" . $id_funcionario . "', ";
								$isql .= "'" . $dados_form["instituicao_ensino_".$i] . "', ";
								$isql .= "'" . maiusculas($dados_form["descricao_formacao_".$i]) . "', ";
								$isql .= "'" . $dados_form["ano_conclusao_".$i] . "') ";

								$db->insert($isql,'MYSQL');

								if($db->erro!='')
								{
									$resposta->addAlert($db->erro);
								}
							}
						}

						//Insere o usuario
						$isql = "INSERT INTO ".DATABASE.".usuarios ";
						$isql .= "(email, login, senha, status) ";
						$isql .= "VALUES (";
						//$isql .= "'" . $id_funcionario . "', ";
						$isql .= "'" . minusculas(trim($dados_form["email"])) . "', ";
						$isql .= "'" . trim($dados_form["login"]) . "', ";
						$isql .= "'" . $senha . "', ";
						$isql .= "'1') ";

						$db->insert($isql,'MYSQL');

						if($db->erro!='')
						{
							$resposta->addAlert($db->erro);
						}
						else
						{
							$id_usuario = $db->insert_id;

							//atualiza o registro do funcionario
							$usql = "UPDATE ".DATABASE.".funcionarios SET ";
							$usql .= "id_usuario = '" . $id_usuario . "' ";
							$usql .= "WHERE id_funcionario = '".$id_funcionario."' ";
	
							$db->update($usql,'MYSQL');
	
							if($db->erro!='')
							{
								$resposta->addAlert($db->erro);
							}

							//niveis de atuacao que nao apontam horas no sistema
							$nivel_atuacao = array('P'); //PACOTE

							$sql = "SELECT * FROM ".DATABASE.".rh_funcoes ";
							$sql .= "WHERE id_funcao = '". $dados_form["funcao_dvm"] ."' ";
							$sql .= "AND rh_funcoes.reg_del = 0 ";

							$db->select($sql,'MYSQL',true);

							if($db->erro!='')
							{
								$resposta->addAlert($db->erro);
							}

							$reg_cargo = $db->array_select[0];

							$cargo_descr = $reg_cargo["descricao"];

							$sql = "SELECT * FROM ".DATABASE.".local ";
							$sql .= "WHERE id_local = '". $dados_form["local_trabalho"] ."' ";
							$sql .= "AND local.reg_del = 0 ";

							$db->select($sql,'MYSQL',true);

							if($db->erro!='')
							{
								$resposta->addAlert($db->erro);
							}

							$reg_local = $db->array_select[0];

							$local_trabalho = $reg_local["descricao"];
							
							/*
							$sql = "SELECT * FROM CTT010 WITH(NOLOCK) ";
							$sql .= "WHERE D_E_L_E_T_ = '' "; //CENTRO DE CUSTO
							$sql .= "AND CTT_CUSTO = '".$dados_form["centrocusto"]."' "; //SOMENTE OS CC NAO BLOQUEADOS

							$db->select($sql,'MSSQL', true);

							if($db->erro!='')
							{
								$resposta->addAlert($db->erro);
							}

							$regs_cc = $db->array_select[0];
							*/

							if(!in_array($nivel_atuacao,$dados_form["nivel_atuacao"]))
							{
								//Insere o salario e tipo contrato
								$isql = "INSERT INTO ".DATABASE.".salarios ";
								$isql .= "(id_funcionario,  tipo_contrato, id_tipo_salario, salario_clt, salario_mensalista, salario_hora, data, id_func_altera, data_altera) ";
								$isql .= "VALUES (";
								$isql .= "'" . $id_funcionario . "', ";
								$isql .= "'" . $dados_form["tipo_contrato"] . "', ";
								$isql .= "'" . trim($dados_form["tipo_salario"]) . "', ";
								$isql .= "'" . str_replace(",",".",str_replace(".","",$dados_form["salario_inicial"])) . "', ";
								$isql .= "'" . str_replace(",",".",str_replace(".","",$dados_form["salario_mensal"])) . "', ";
								$isql .= "'" . str_replace(",",".",str_replace(".","",$dados_form["salario_hora"])) . "', ";
								$isql .= "'" . php_mysql($dados_form["data_inicio"]) . "', ";
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
								$usql .= "WHERE id_funcionario = '".$id_funcionario."' ";

								$db->update($usql,'MYSQL');

								if($db->erro!='')
								{
									$resposta->addAlert($db->erro);

									return $resposta;
								}

								$DEPENDENTES = "";

								for($i=1;$i<=6;$i++)
								{
									if($dados_form["nome_dep".$i]!="")
									{
										$isql = "INSERT INTO ".DATABASE.".dependentes_funcionarios ";
										$isql .= "(id_funcionario, nome_dependente, data_nascimento, parentesco) ";
										$isql .= "VALUES (";
										$isql .= "'" . $id_funcionario . "', ";
										$isql .= "'" . maiusculas($dados_form["nome_dep".$i]) . "', ";
										$isql .= "'" . php_mysql($dados_form["data_dep".$i]) . "', ";
										$isql .= "'" . maiusculas($dados_form["parentesco_dep".$i]) . "') ";

										$db->insert($isql,'MYSQL');

										if($db->erro!='')
										{
											$resposta->addAlert($db->erro);
										}

										$DEPENDENTES .="<tr align=\"center\" style=\"font-family: Arial, Helvetica, sans-serif; font-size: 10px;\">
															<td>". maiusculas($dados_form["nome_dep".$i]) ."</td>
															<td>". $dados_form["data_dep".$i] ."</td>
															<td>". maiusculas($dados_form["parentesco_dep".$i]) ."</td>
														</tr>";
									}
								}
								
								#PERMISSÕES NOVO MODELO
								//Definindo qual sera o tipo de acesso padrao por tipo de contrato
								$arrTipoAcesso = array('CLT' => 0, 'EST' => 0, 'SC' => 1, 'SC+MENS' => 1, 'SOCIO' => 1, 'SC+MENS+CLT' => 1);
																
								//Buscando os acessos todos padrões 0 => clt, 1 => pj, 2 => clt + pj
								//Primeira parte consulta busca todos os modulos padrão por tipo de acesso de usuário
								$sql = "SELECT id_sub_modulo, sub_modulo, codigo_acesso FROM ".DATABASE.".sub_modulos ";
								$sql .= "WHERE acesso_padrao = 1 ";
								$sql .= "AND sub_modulos.reg_del = 0 ";
								$sql .= "AND tipo_acesso_padrao IN(".$arrTipoAcesso[$dados_form['tipo_contrato']].",2) ";
								
								//Segunda parte consulta busca todos os modulos que nao sao padrao e so serao exibidos de acordo com um setor especifico, ex. TI, Financeiro Etc
								$sql .= "UNION ALL ";
								$sql .= "SELECT sms.id_sub_modulo, sm.sub_modulo, sms.codigo_acesso ";
								$sql .= "FROM ".DATABASE.".sub_modulos_x_setor sms ";
								$sql .= "JOIN ".DATABASE.".sub_modulos sm on sm.id_sub_modulo = sms.id_sub_modulo ";
								$sql .= "WHERE sms.reg_del = 0 ";
								$sql .= "AND sms.id_setor_aso = ".$dados_form['setor_aso']." ";
								$sql .= "AND sms.tipo_acesso_padrao IN(".$arrTipoAcesso[$dados_form['tipo_contrato']].",2)";
								
								$db->select($sql, 'MYSQL', true);

								if($db->erro!='')
								{
									$resposta->addAlert($db->erro);

									return $resposta;
								}
								
								//Montando um único sql para inserir numa unica transação com o banco de dados
								$virgula = '';

								$isql = "INSERT INTO ".DATABASE.".permissoes (id_usuario, id_sub_modulo, permissao) VALUES ";
								
								foreach($db->array_select as $reg_permissoes)
								{
									$isql .= $virgula."(".$id_usuario.", ".$reg_permissoes['id_sub_modulo'].", ".$reg_permissoes['codigo_acesso'].")";
									$virgula = ',';
								}
								
								$db->insert($isql,'MYSQL');

								#FIM DAS PERMISSÕES NOVO MODELO
								
								if($db->erro!='')
								{
									$resposta->addAlert($db->erro);
								}
			
								//se CLT
								if(in_array($dados_form["tipo_contrato"],array('CLT','SC+CLT','SC+MENS+CLT')))
								{

									$ADMISSAO = "<table width=\"100%\" border=\"0\">";
									$ADMISSAO .="<tr>
												<td width=\"7%\"> </td>
												<td width=\"19%\"><span style=\"color: #006699;	font-weight: bold; text-decoration: underline; font-family: Verdana, Arial;\">".NOME_EMPRESA."</span></td>
												<td width=\"67%\"> </td>
												<td width=\"7%\"> </td>
											  </tr>";
									$ADMISSAO .= "<tr>
												<td> </td>
												<td> </td>
												<td> </td>
												<td> </td>
											  </tr>";
									$ADMISSAO .="<tr>
												<td> </td>
												<td colspan=\"2\">".CIDADE.", ". date('d')." de ". meses(date('m')-1,1)." de ".date('Y') ."</td>
												<td> </td>
											  </tr>";

									$ADMISSAO .="<tr>
												<td> </td>
												<td> </td>
												<td> </td>
												<td> </td>
											  </tr>";
									$ADMISSAO .="<tr>
												<td> </td>
												<td>A/C  </td>
												<td> </td>
												<td> </td>
											  </tr>";
									$ADMISSAO .="<tr>
												<td> </td>
												<td> </td>
												<td> </td>
												<td> </td>
											  </tr>";
									$ADMISSAO .="<tr>
												<td> </td>
												<td colspan=\"2\" align=\"center\" style=\"color: #FF0000; font-weight: bold;\">ADMISSÃO DE FUNCIONÁRIO</td>
												<td> </td>
											  </tr>";
									$ADMISSAO .="<tr>
												<td> </td>
												<td> </td>
												<td> </td>
												<td> </td>
											  </tr>";
									$ADMISSAO .="<tr>
												<td> </td>
												<td colspan=\"2\">Gentileza providenciar o processo de admissão do funcionário abaixo:</td>
												<td> </td>
											  </tr>";
									$ADMISSAO .="<tr>
												<td> </td>
												<td> </td>
												<td> </td>
												<td> </td>
											  </tr>";
									$ADMISSAO .="<tr>
												<td> </td>
												<td colspan=\"2\">";
									$ADMISSAO .="		<table width=\"100%\" border=\"1\">";
									$ADMISSAO .="		<tr>
														<td style=\"font-family: Arial, Helvetica, sans-serif; font-weight: bold; font-size: 12px;\">NOME:</td>
														<td colspan=\"8\" style=\"font-family: Arial, Helvetica, sans-serif; font-size: 10px;\">". maiusculas($dados_form["funcionario"]) ."</td>
													</tr>";
									$ADMISSAO .="	  	<tr>
														<td width=\"10%\" rowspan=\"2\" style=\"font-family: Arial, Helvetica, sans-serif; font-weight: bold; font-size: 12px;\">FILIAÇÃO</td>
														<td width=\"6%\" style=\"font-family: Arial, Helvetica, sans-serif; font-weight: bold; font-size: 12px;\">PAI: </td>
														<td width=\"33%\" style=\"font-family: Arial, Helvetica, sans-serif; font-size: 10px;\">". maiusculas($dados_form["pai"]) ."</td>
														<td width=\"3%\"> </td>
														<td width=\"13%\" style=\"font-family: Arial, Helvetica, sans-serif; font-weight: bold; font-size: 12px;\">NACIONALIDADE:</td>
														<td width=\"35%\" colspan=\"4\" style=\"font-family: Arial, Helvetica, sans-serif; font-size: 10px;\">". maiusculas($dados_form["nacionalidade_pai"]) ."</td>
													</tr>";
									$ADMISSAO .="      <tr>
														<td style=\"font-family: Arial, Helvetica, sans-serif; font-weight: bold; font-size: 12px;\">MÃE:</td>
														<td style=\"font-family: Arial, Helvetica, sans-serif; font-size: 10px;\">". maiusculas($dados_form["mae"]) ."</td>
														<td> </td>
														<td style=\"font-family: Arial, Helvetica, sans-serif; font-weight: bold; font-size: 12px;\">NACIONALIDADE:</td>
														<td colspan=\"4\" style=\"font-family: Arial, Helvetica, sans-serif; font-size: 10px;\">". maiusculas($dados_form["nacionalidade_mae"]) ."</td>
													</tr>";
									$ADMISSAO .="      <tr>
														<td colspan=\"9\">";
									$ADMISSAO .="				<table width=\"100%\" border=\"1\">";
									$ADMISSAO .="		          <tr align=\"center\" style=\"font-family: Arial, Helvetica, sans-serif; font-weight: bold; font-size: 12px;\">
																<td width=\"16%\">CARTEIRA TRABALHO</td>
																<td width=\"7%\">SÉRIE</td>
																<td width=\"15%\">CARTEIRA RESERVISTA</td>
																<td width=\"13%\">CATEGORIA</td>
																<td width=\"11%\">T&Iacute;TULO ELEITOR</td>
																<td width=\"16%\">CÉDULA DE IDENTIDADE</td>
																<td width=\"12%\">ORGÃO EMISSOR</td>
																<td width=\"10%\">CPF</td>
															</tr>";
									$ADMISSAO .="		        <tr align=\"center\" style=\"font-family: Arial, Helvetica, sans-serif; font-size: 10px;\">
																<td>". $dados_form["ctps_num"] ."</td>
																<td>". maiusculas($dados_form["ctps_serie"]) ."</td>
																<td>". $dados_form["reservista_num"] ."</td>
																<td>". $dados_form["reservista_categoria"] ."</td>
																<td>". $dados_form["titulo_eleitor"] ."</td>
																<td>". maiusculas($dados_form["identidade_num"]) ."</td>
																<td>". maiusculas($dados_form["identidade_emissor"]) ."</td>
																<td>". $dados_form["cpf_num"] ."</td>
															</tr>";
									$ADMISSAO .="            </table>";
									$ADMISSAO .="            <table width=\"100%\" border=\"1\">";
									$ADMISSAO .="	            <tr align=\"center\" style=\"font-family: Arial, Helvetica, sans-serif; font-weight: bold; font-size: 12px;\">
																<td width=\"16%\">DATA DE NASCIMENTO</td>
																<td width=\"7%\">IDADE</td>
																<td width=\"15%\">NACIONALIDADE</td>
																<td width=\"13%\">NATURALIDADE</td>
																<td width=\"11%\">ESTADO</td>
																<td width=\"16%\">ESTADO CIVIL</td>
																<td width=\"12%\">GRAU DE INSTRUCÃO</td>
															</tr>";

									$sql = "SELECT * FROM ".DATABASE.".rh_escolaridade ";
									$sql .= "WHERE id_rh_escolaridade = '".$dados_form["grau_instrucao"]."' ";
									$sql .= "AND reg_del = 0 ";

									$db->select($sql,'MYSQL',true);

									if($db->erro!='')
									{
										$resposta->addAlert($db->erro);
									}

									$reg_escol = $db->array_select[0];

									$ADMISSAO .="              <tr align=\"center\" style=\"font-family: Arial, Helvetica, sans-serif; font-size: 10px;\">
																<td>". $dados_form["data_nascimento"] ."</td>
																<td>". $dados_form["idade"] ."</td>
																<td>". maiusculas($dados_form["nacionalidade"]) ."</td>
																<td>". maiusculas($dados_form["naturalidade"]) ."</td>
																<td>". $dados_form["estado_nasc"] ."</td>
																<td>". $dados_form["estado_civil"] ."</td>											
																<td>". $reg_escol["escolaridade"] ."</td>
															</tr>";
									$ADMISSAO .="           </table>";
									$ADMISSAO .="       </td>";
									$ADMISSAO .="    </tr>";
									$ADMISSAO .="    <tr>
													<td style=\"font-family: Arial, Helvetica, sans-serif; font-weight: bold; font-size: 12px;\">CÔNJUGE:</td>
													<td colspan=\"8\" style=\"font-family: Arial, Helvetica, sans-serif; font-size: 10px;\">". maiusculas($dados_form["conjuge"]) ."</td>
												  </tr>";
									$ADMISSAO .="    <tr>
													<td style=\"font-family: Arial, Helvetica, sans-serif; font-weight: bold; font-size: 12px;\">ENDEREÇO:</td>
													<td colspan=\"8\" style=\"font-family: Arial, Helvetica, sans-serif; font-size: 10px;\">". maiusculas($dados_form["endereco"]) ."</td>
												  </tr>";
									$ADMISSAO .="    <tr>
													<td style=\"font-family: Arial, Helvetica, sans-serif; font-weight: bold; font-size: 12px;\">BAIRRO:</td>
													<td colspan=\"8\" style=\"font-family: Arial, Helvetica, sans-serif; font-size: 10px;\">". maiusculas($dados_form["bairro"]) ."</td>
												  </tr>";
									$ADMISSAO .="    <tr>
													<td style=\"font-family: Arial, Helvetica, sans-serif; font-weight: bold; font-size: 12px;\">CIDADE:</td>
													<td colspan=\"8\" style=\"font-family: Arial, Helvetica, sans-serif; font-size: 10px;\">". maiusculas($dados_form["cidade"]) ."</td>
												  </tr>";
									$ADMISSAO .="    <tr>
													<td style=\"font-family: Arial, Helvetica, sans-serif; font-weight: bold; font-size: 12px;\">CEP:</td>
													<td colspan=\"8\" style=\"font-family: Arial, Helvetica, sans-serif; font-size: 10px;\">". $dados_form["cep"] ."</td>
												  </tr>";
									$ADMISSAO .="    <tr>
													<td style=\"font-family: Arial, Helvetica, sans-serif; font-weight: bold; font-size: 12px;\">ESTADO:</td>
													<td colspan=\"8\" style=\"font-family: Arial, Helvetica, sans-serif; font-size: 10px;\">". $dados_form["estado"] ."</td>
												  </tr>";
									$ADMISSAO .="    <tr>
													<td colspan=\"9\">";
									$ADMISSAO .="			<table width=\"100%\" border=\"1\">";
									$ADMISSAO .="     			<tr align=\"center\" style=\"font-family: Arial, Helvetica, sans-serif; font-weight: bold; font-size: 12px;\">
																<td colspan=\"3\">BENEFICIÁRIOS</td>
															</tr>";
									$ADMISSAO .="	            <tr align=\"center\" style=\"font-family: Arial, Helvetica, sans-serif; font-weight: bold; font-size: 12px;\">
																<td width=\"16%\">NOME</td>
																<td width=\"7%\">DATA DE NASCIMENTO </td>
																<td width=\"15%\">PARENTESCO</td>
															</tr>";

									$ADMISSAO .= $DEPENDENTES;

									$ADMISSAO .="         </table>";
									$ADMISSAO .="		</td>";
									$ADMISSAO .="   </tr>";
									$ADMISSAO .="	 <tr>
													<td colspan=\"9\">";
									$ADMISSAO .="			<table width=\"100%\" border=\"1\">";
									$ADMISSAO .="	          <tr align=\"center\" style=\"font-family: Arial, Helvetica, sans-serif; font-weight: bold; font-size: 12px;\">
																<td colspan=\"3\">PIS</td>
														  </tr>";
									$ADMISSAO .="	          <tr align=\"center\" style=\"font-family: Arial, Helvetica, sans-serif; font-weight: bold; font-size: 12px;\">
																<td width=\"16%\">CADASTRADO EM: </td>
																<td width=\"7%\">NÚMERO</td>
																<td width=\"15%\">BANCO</td>
														  </tr>";
									$ADMISSAO .="            <tr align=\"center\" style=\"font-family: Arial, Helvetica, sans-serif; font-size: 10px;\">
																<td>". $dados_form["pis_data"] ."</td>
																<td>". $dados_form["pis_numero"] ."</td>";

									$ADMISSAO .="     			<td>".$bancos[$dados_form["pis_banco"]]." / ".$bancos_nome[$dados_form["pis_banco"]]."</td>";
									$ADMISSAO .=" 			  </tr>";
									$ADMISSAO .="        	</table>";
									$ADMISSAO .=" 		</td>";
									$ADMISSAO .="    </tr>";
									$ADMISSAO .="    <tr>
													<td colspan=\"9\">";
									$ADMISSAO .="			<table width=\"100%\" border=\"1\">";
									$ADMISSAO .="				<tr align=\"center\" style=\"font-family: Arial, Helvetica, sans-serif; font-weight: bold; font-size: 12px;\">";
									$ADMISSAO .="		            <td width=\"16%\">DATA DA ADMISSÃO </td>
																<td width=\"7%\">NATUREZA DO CARGO</td>
																<td width=\"15%\">SALÁRIO INICIAL</td>
																
																<td width=\"11%\">DATA DE INÍCIO</td>
															</tr>";

									$sql = "SELECT * FROM ".DATABASE.".rh_cargos ";
									$sql .= "WHERE id_cargo_grupo = '". $dados_form["cargo_dvm"] ."' ";
									$sql .= "AND reg_del = 0 ";

									$db->select($sql,'MYSQL',true);

									if($db->erro!='')
									{
										$resposta->addAlert($db->erro);
									}

									$reg_cargo = $db->array_select[0];

									$ADMISSAO .="		        <tr align=\"center\" style=\"font-family: Arial, Helvetica, sans-serif; font-size: 10px;\">
																<td>". $dados_form["data_admissao"] ."</td>
																<td>". $reg_cargo["grupo"] ."</td>
																<td>". $dados_form["salario_inicial"]. "</td>
																
																<td>". $dados_form["data_inicio"] ."</td>
															</tr>";
									$ADMISSAO .=" 			</table>";
									$ADMISSAO .="  	</td>";
									$ADMISSAO .="   </tr>";
									$ADMISSAO .="	 <tr>
													<td colspan=\"9\">";
									$ADMISSAO .="			<table width=\"100%\" border=\"1\">";
									$ADMISSAO .="     			<tr align=\"center\" style=\"font-family: Arial, Helvetica, sans-serif; font-weight: bold; font-size: 12px;\">
																<td colspan=\"4\">HORÁRIO DE TRABALHO </td>
															</tr>";
									$ADMISSAO .="		        <tr align=\"center\" style=\"font-family: Arial, Helvetica, sans-serif; font-weight: bold; font-size: 12px;\">
																<td width=\"16%\">ENTRADA </td>
																<td width=\"7%\">REFEIÇÃO</td>
																<td width=\"15%\">SAÍDA </td>
																<td width=\"13%\">DESCANSO SEMANAL  </td>
															</tr>";
									$ADMISSAO .="		        <tr align=\"center\" style=\"font-family: Arial, Helvetica, sans-serif; font-size: 10px;\">
																<td>". $dados_form["horario_entrada"] ."</td>
																<td>". maiusculas($dados_form["horario_refeicao"]) ."</td>
																<td>". $dados_form["horario_saida"] ."</td>
																<td>". maiusculas($dados_form["descanso_semanal"]) ."</td>
															</tr>";
									$ADMISSAO .="        </table>";
									$ADMISSAO .=" 		</td>";
									$ADMISSAO .="  </tr>";
									$ADMISSAO .="  <tr>
													<td colspan=\"9\">";
									$ADMISSAO .="			<table width=\"100%\" border=\"1\">";
									$ADMISSAO .="	          <tr align=\"center\" style=\"font-family: Arial, Helvetica, sans-serif; font-weight: bold; font-size: 12px;\">
															<td colspan=\"6\">CARACTERÍSTICAS FÍSICAS </td>
														  </tr>";
									$ADMISSAO .="	          <tr align=\"center\" style=\"font-family: Arial, Helvetica, sans-serif; font-weight: bold; font-size: 12px;\">
															<td width=\"16%\">COR</td>
															<td width=\"16%\">SEXO</td>
															<td width=\"7%\">CABELOS</td>
															<td width=\"15%\">OLHOS</td>
															<td width=\"13%\">ALTURA(m)</td>
															<td width=\"13%\">PESO(kg)</td>
														  </tr>";
									$ADMISSAO .="	          <tr align=\"center\" style=\"font-family: Arial, Helvetica, sans-serif; font-size: 10px;\">
															<td>". maiusculas($dados_form["cor"]) ."</td>
															<td>". $dados_form["sexo"] ."</td>
															<td>". maiusculas($dados_form["cabelos"]) ."</td>
															<td>". maiusculas($dados_form["olhos"]) ."</td>
															<td>". $dados_form["altura"] ."</td>
															<td>". $dados_form["peso"] ."</td>
														  </tr>";
									$ADMISSAO .="        </table>";
									$ADMISSAO .="	   </td>";
									$ADMISSAO .="   </tr>";
									$ADMISSAO .="	</table>";
									$ADMISSAO .="</td>
											  <td> </td>
											 </tr>";
									$ADMISSAO .="<tr align=\"center\" style=\"font-family: Arial, Helvetica, sans-serif; font-weight: bold; font-size: 12px;\">
												<td colspan=\"4\" >Rua XXXXXXX, XXXX - Centro - XXXXXXX - SP</td>
											  </tr>";
									$ADMISSAO .="<tr align=\"center\" style=\"font-family: Arial, Helvetica, sans-serif; font-weight: bold; font-size: 12px;\">
												<td colspan=\"4\" >CEP: XXXXX - TEL: (11) XXXXXXX - FAX: (11) XXXXXXX</td>
											  </tr>";
									$ADMISSAO .="<tr align=\"center\" style=\"font-family: Arial, Helvetica, sans-serif; font-weight: bold; font-size: 12px;\">
												<td colspan=\"4\" >Site: http://www.empresa.com.br - E-mail: empresa@dominio.com.br</td>
											  </tr>";

									$ADMISSAO .="</table>";

									if(ENVIA_EMAIL)
									{
										$params 			= array();
										$params['from']		= "recrutamento@dominio.com.br";
										$params['from_name']= "RECURSOS HUMANOS";
										$params['subject'] 	= "ADMISSÃO FUNCIONÁRIO";

										//Agora passando o segundo parametro buscaremos os e-mails direto no banco de dados
										$mail = new email($params, 'admissao_funcionario');
										
										$mail->montaCorpoEmail($ADMISSAO);

										if(!$mail->Send())
										{
											$resposta->addAlert('Erro ao enviar e-mail!!! '.$mail->ErrorInfo);
										}

										$mail->ClearAddresses();
									}
									else
									{
										$resposta->addScriptCall('modal', $ADMISSAO, '300_650', 'Conteúdo email', 2);
									}
								}

								$TI = CIDADE . ", ". date('d')." de ".meses(date('m')-1,1)." de ". date('Y') ."<br><br><br>";
								$TI .= "<span style=\"color: #FF0000; font-weight: bold; text-decoration: underline; font-family: Verdana, Arial;\">CADASTRO DE USUÁRIO</span><br><br><br>";
								$TI .= "Favor cadastrar o login e e-mail do novo funcionario:<br>";
								$TI .= "Nome: <strong>".maiusculas($dados_form["funcionario"])."</strong><br>";
								$TI .= "Funcao: <strong>".$cargo_descr."</strong><br>";
								$TI .= "Centro Custo: <strong>".$dados_form["centrocusto"]." - ".$regs_cc["CTT_DESC01"]. "</strong><br>";
								$TI .= "<span style=\"color: #FF0000;\">data inicio: <strong>".$dados_form["data_inicio"]."</strong></span><br>";
								$TI .= "Local Trabalho: <strong>".$local_trabalho."</strong><br><br><br>";

								$tiHtml = getNecessidadesEmail($dados_form);
								
								if ($tiHtml !== false)
								{
									//Temporário
									$TI .= "<span style=\"color: #FF0000;\">Infraestrutura TI: </span><br>";
									$TI .= $tiHtml;
								}

								$TI .= "login: <strong>".$dados_form["login"]."</strong><br>";
								$TI .= "E-mail: <strong>".minusculas($dados_form["email"])."</strong><br><br><br><br>";

								$TI .= "Primeiro acesso a rede:<br><br>";
								$TI .= "Usuário:<strong>".$dados_form["login"]."</strong><br>";
								$TI .= "Senha:<strong>123456</strong><br><br><br><br>";

								$TI .= "Acesso SISTEMA:<br><br>";
								$TI .= "Usuário:<strong>".$dados_form["login"]."</strong><br>";
								$TI .= "Senha:<strong>123456</strong><br><br>";
								$TI .= "<strong>OBS:</strong>O sistema solicitará a troca da senha ao primeiro acesso.<br><br><br><br>";
								$TI .= "Atenciosamente, Depto. Recursos Humanos.";
								
								if(ENVIA_EMAIL)
								{
									$params 			= array();
									$params['from']		= "recrutamento@dominio.com.br";
									$params['from_name']= "RECURSOS HUMANOS";
									$params['subject'] 	= "CADASTRO DE NOVO USUÁRIO";

									$mail = new email($params, 'novo_usuario');
									
									$mail->montaCorpoEmail($TI);

									if(!$mail->Send())
									{
										$resposta->addAlert('Erro ao enviar e-mail!!! '.$mail->ErrorInfo);
									}

									$mail->ClearAddresses();
								}
								else
								{
									$resposta->addScriptCall('modal', $TI, '300_650', 'Conteúdo email', 3);
								}

								$DEPT = CIDADE . ", ". date('d')." de ".meses(date('m')-1,1)." de ". date('Y') ."<br><br><br>";
								$DEPT .= "<span style=\"color: #FF0000; font-weight: bold; text-decoration: underline; font-family: Verdana, Arial;\">CADASTRO DE USUÁRIO</span><br><br><br>";
								$DEPT .= "Nome: <strong>".maiusculas($dados_form["funcionario"])."</strong><br>";
								$DEPT .= "Funcao: <strong>".$cargo_descr."</strong><br><br><br>";
								$DEPT .= "Centro Custo: <strong>".$dados_form["centrocusto"]." - ".$regs_cc["CTT_DESC01"]."</strong><br><br><br>";
								$DEPT .= "tipo Contrato: <strong>".$dados_form["tipo_contrato"]."</strong><br><br><br>";
								$DEPT .= "E-mail: <strong>".minusculas($dados_form["email"])."</strong><br><br><br><br>";
								$DEPT .= "Data inicio: <strong>".$dados_form["data_inicio"]."</strong><br>";
								$DEPT .= "Local Trabalho: <strong>".$local_trabalho."</strong><br><br><br>";
								$DEPT .= "Atenciosamente, Depto. Recursos Humanos.";
								
								if(ENVIA_EMAIL)
								{
									$params 			= array();
									$params['from']		= "recrutamento@dominio.com.br";
									$params['from_name']= "RECURSOS HUMANOS";
									$params['subject'] 	= "CADASTRO DE NOVO COLABORADOR";

									$mail = new email($params, 'novo_colaborador');
									
									$mail->montaCorpoEmail($DEPT);
									
									if(!$mail->Send())
									{
										$resposta->addAlert('Erro ao enviar e-mail!!! '.$mail->ErrorInfo);
									}

									$mail->ClearAddresses();
								}
								else 
								{
									$resposta->addScriptCall('modal', $DEPT, '300_650', 'Conteúdo email', 4);
								}
							}
							else
							{
								$PCT = CIDADE. ", ". date('d') ." de ".meses(date('m')-1,1)." de ". date('Y') ."<br><br><br>";
								$PCT .= "<span style=\"color: #FF0000; font-weight: bold; text-decoration: underline; font-family: Verdana, Arial;\">CADASTRO DE USUÁRIO</span><br><br><br>";
								$PCT .= "Nome: <strong>".maiusculas($dados_form["funcionario"])."</strong><br>";
								$PCT .= "Função: <strong>".$cargo_descr."</strong><br><br><br>";
								$PCT .= "Centro Custo: <strong>".$dados_form["centrocusto"]." - ".$regs_cc["CTT_DESC01"]."</strong><br><br><br>";
								$PCT .= "E-mail: <strong>".minusculas($dados_form["email"])."</strong><br><br><br><br>";
								$PCT .= "Atenciosamente, Depto. Recursos Humanos.";
								
								if(ENVIA_EMAIL)
								{
									$params 			= array();
									$params['from']		= "recrutamento@dominio.com.br";
									$params['from_name']= "RECURSOS HUMANOS";
									$params['subject'] 	= "CADASTRO DE NOVO COLABORADOR - PACOTE";

									$mail = new email($params, 'novo_colaborador_pacote');
									
									$mail->montaCorpoEmail($PCT);

									if(!$mail->Send())
									{
										$resposta->addAlert('Erro ao enviar e-mail!!! '.$mail->ErrorInfo);
									}

									$mail->ClearAddresses();
								}
								else 
								{
									$resposta->addScriptCall('modal', $PCT, '300_650', 'Conteúdo email', 5);
								}
							}

							/**
							 * 28/01/2015
							 * Carlos Máximo
							 * TAP - Alteração do cadastro de funcionários
							 */
							//Adicionado o contrato do colaborador
							
							$erro = gravarExigenciasCliente($dados_form);
							
							if ($erro != '')
							{
								$resposta->addAlert($erro);

								return $resposta;
							}
							
							/**
							 * Final das alteracoes 28/01/2015
							 */
								
							/**
							 * 27/02/2015
							 * Carlos Máximo
							 * TAP - Alteração do cadastro de funcionários
							 */	
							//Adicionado o contrato do colaborador
							
							$erro = gravarAjudaCusto($dados_form);
							
							if ($erro != '')
							{
								$resposta->addAlert($erro);

								return $resposta;
							}
							
							/**
							 * Final das alteracoes 27/02/2015
							 */

							$resposta->addScript("xajax_voltar('');");
							
							$resposta->addScript("xajax_atualizatabela('',xajax.getFormValues('frm_funcionarios')); ");
							
							$resposta->addAlert("Funcionário cadastrado com sucesso.");
						}
					}
				}
				else
				{
					$resposta->addAlert("O login selecionado ja existe no banco de dados.");
				}
			}
		}
		else
		{
			$resposta->addAlert("Preencher os campos obrigatorios - inserir(*)");
		}
	}

	return $resposta;
}

function getNecessidadesEmail($dados_form)
{
	$db = new banco_dados();
	
	//Abaixo nova rotina para selecionar as necessidades do novo colaborador.
	//Caso for usar esta rotina na alteração de usuário, favor, criar uma função
	$sql = "SELECT
				id_necessidade, tipo_necessidade, id_funcionario, outros, infra_estrutura
			FROM
			  ".DATABASE.".rh_necessidades_x_funcionario r
			LEFT JOIN(SELECT * FROM ".DATABASE.".infra_estrutura WHERE infra_estrutura.reg_del = 0) infra
			ON id_infra_estrutura = id_necessidade
			WHERE r.reg_del = 0
			AND r.id_funcionario = ".$dados_form['id_funcionario'];

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		return false;
	}
	else
	{
		$equipamentos = '';
		$softwares = '';
		$dvmsys = '';
		$protheus = '';
	
		foreach ($db->array_select as $reg)
		{
			switch($reg['tipo_necessidade'])
			{
				case 1:
					$equipamentos .= $reg['infra_estrutura'].'<br />';
				break;
				case 2:
					$softwares .= $reg['infra_estrutura'].'<br />';
				break;
				case 3:
					$protheus .= $reg['outros'].'<br />';
				break;
				case 4:
					$dvmsys .= $reg['outros'].'<br />';
				break;
				case 5:
					$softwares .= $reg['outros'].'<br />';
				break;
			}
		}
	
		$tiHtml = '<br />Equipamentos: <br />'.$equipamentos.'<br />';
		$tiHtml .= 'Softwares: <br />'.$softwares.'<br />';
		$tiHtml .= $dvmsys != '' ? 'Módulos SISTEMA: '.$dvmsys.'<br />' : '';
		$tiHtml .= $protheus != '' ? 'Módulos Protheus: '.$protheus.'<br />' : '';
		
		return $tiHtml;
	}
}

function atualizar($dados_form)
{
	$resposta = new xajaxResponse();
		
	$db = new banco_dados;

	$modificacao = NULL;

	$dep_nome = NULL;

	if($dados_form["tipo_contrato"]=="")
	{
		$resposta->addAlert("O tipo de contrato deve ser selecionado.");
	}
	else
	{
		if($dados_form["data_admissao"]=="")
		{
			$data_inicio = $dados_form["data_inicio"];
		}

		if($data_inicio=="")
		{
			$data_inicio = $dados_form["data_inicio"];
		}

		if($dados_form["funcionario"]!="" && $dados_form["login"]!="" && $dados_form["email"]!="" && $dados_form["setor"]!="" && $dados_form["nivel_atuacao"]!="" && $dados_form["local_trabalho"]!="" && $dados_form["situacao"]!="" && $dados_form["funcao_dvm"]!="" && $dados_form["cargo_dvm"]!="")
		{
			/*
			 * Adicionado em 30/01/2015
			 * Carlos Máximo
			 */
			$erros = gravarNecessidadesColaborador($dados_form);

			if ($erros != '')
			{
				$resposta->addAlert($erros);
				return $resposta;
			}
			/*
			 * Finalizado
			 */

			$sql = "SELECT * FROM ".DATABASE.".dependentes_funcionarios ";
			$sql .= "WHERE dependentes_funcionarios.id_funcionario = '" . $dados_form["id_funcionario"] . "' ";
			$sql .= "AND reg_del = 0 ";

			$db->select($sql,'MYSQL',true);

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}

			foreach($db->array_select as $reg_dep)
			{
				$dep_nome[] = $reg_dep["nome_dependente"];
			}

			$sql = "SELECT * FROM ".DATABASE.".rh_funcoes, ".DATABASE.".setores, ".DATABASE.".funcionarios ";
			$sql .= "LEFT JOIN ".DATABASE.".usuarios ON (usuarios.id_usuario = funcionarios.id_usuario AND usuarios.reg_del = 0) ";
			$sql .= "WHERE funcionarios.id_setor = setores.id_setor ";
			$sql .= "AND rh_funcoes.reg_del = 0 ";
			$sql .= "AND setores.reg_del = 0 ";
			$sql .= "AND funcionarios.reg_del = 0 ";
			$sql .= "AND funcionarios.id_funcao = rh_funcoes.id_funcao ";
			$sql .= "AND funcionarios.id_funcionario = '" . $dados_form["id_funcionario"] . "' ";

			$db->select($sql,'MYSQL',true);

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}
			else
			{
				$reg_editar = $db->array_select[0];

				$modificacao["setor"] 					= $reg_editar["id_setor"]!=$dados_form["setor"] ? "bgcolor=\"#FFFF00\" " : '';
				$modificacao["funcao_dvm"] 				= $reg_editar["id_funcao"]!=$dados_form["funcao_dvm"] ? "bgcolor=\"#FFFF00\"  " : '';
				$modificacao["funcionario"] 			= $reg_editar["funcionario"]!=maiusculas($dados_form["funcionario"]) ? "bgcolor=\"#FFFF00\" " : '';
				$modificacao["endereco"] 				= $reg_editar["funcionario_endereco"]!=maiusculas($dados_form["endereco"]) ? "bgcolor=\"#FFFF00\"  " : '';
				$modificacao["bairro"] 					= $reg_editar["funcionario_bairro"]!=maiusculas($dados_form["bairro"]) ? "bgcolor=\"#FFFF00\"  " : '';
				$modificacao["cidade"] 					= $reg_editar["funcionario_cidade"]!=maiusculas($dados_form["cidade"]) ? "bgcolor=\"#FFFF00\"  " : '';
				$modificacao["cep"] 					= $reg_editar["funcionario_cep"]!=maiusculas($dados_form["cep"]) ? "bgcolor=\"#FFFF00\"  " : '';
				$modificacao["estado"] 					= $reg_editar["funcionario_estado"]!=maiusculas($dados_form["estado"]) ? "bgcolor=\"#FFFF00\"  " : '';
				$modificacao["pai"] 					= $reg_editar["filiacao_pai"]!=maiusculas($dados_form["pai"]) ? "bgcolor=\"#FFFF00\"  " : '';
				$modificacao["mae"] 					= $reg_editar["filiacao_mae"]!=maiusculas($dados_form["mae"]) ? "bgcolor=\"#FFFF00\"  " : '';
				$modificacao["nacionalidade_pai"]		= $reg_editar["nacionalidade_pai"]!=maiusculas($dados_form["nacionalidade_pai"]) ? "bgcolor=\"#FFFF00\"  " : '';
				$modificacao["nacionalidade_mae"]		= $reg_editar["nacionalidade_mae"]!=maiusculas($dados_form["nacionalidade_mae"]) ? "bgcolor=\"#FFFF00\"  " : '';
				$modificacao["ctps_num"] 				= $reg_editar["ctps_num"]!=$dados_form["ctps_num"] ? "bgcolor=\"#FFFF00\"  " : '';
				$modificacao["ctps_serie"] 				= $reg_editar["ctps_serie"]!=maiusculas($dados_form["ctps_serie"]) ? "bgcolor=\"#FFFF00\"  " : '';
				$modificacao["reservista_num"] 			= $reg_editar["reservista_num"]!=$dados_form["reservista_num"] ? "bgcolor=\"#FFFF00\"  " : '';
				$modificacao["reservista_categoria"] 	= $reg_editar["reservista_categoria"]!=maiusculas($dados_form["reservista_categoria"]) ? "bgcolor=\"#FFFF00\"  " : '';
				$modificacao["titulo_eleitor"] 			= $reg_editar["titulo_eleitor"]!=maiusculas($dados_form["titulo_eleitor"]) ? "bgcolor=\"#FFFF00\"  " : '';
				$modificacao["identidade_num"] 			= $reg_editar["identidade_num"]!=$dados_form["identidade_num"] ? "bgcolor=\"#FFFF00\"  " : '';
				$modificacao["identidade_emissor"] 		= $reg_editar["identidade_emissor"]!=maiusculas($dados_form["identidade_emissor"]) ? "bgcolor=\"#FFFF00\"  " : '';
				$modificacao["cpf_num"] 				= $reg_editar["cpf"]!=$dados_form["cpf_num"] ? "bgcolor=\"#FFFF00\"  " : '';
				$modificacao["naturalidade"] 			= $reg_editar["naturalidade"]!=maiusculas($dados_form["naturalidade"]) ? "bgcolor=\"#FFFF00\"  " : '';
				$modificacao["nacionalidade"] 			= $reg_editar["nacionalidade"]!=maiusculas($dados_form["nacionalidade"]) ? "bgcolor=\"#FFFF00\"  " : '';
				$modificacao["estado_nasc"] 			= $reg_editar["estado_nascimento"]!=$dados_form["estado_nasc"] ? "bgcolor=\"#FFFF00\"  " : '';
				$modificacao["data_nascimento"] 		= $reg_editar["data_nascimento"]!=php_mysql($dados_form["data_nascimento"]) ? "bgcolor=\"#FFFF00\"  " : '';
				$modificacao["data_inicio"] 			= $reg_editar["data_inicio"]!=php_mysql($dados_form["data_inicio"]) ? "bgcolor=\"#FFFF00\"  " : '';
				$modificacao["estado_civil"] 			= $reg_editar["estado_civil"]!=$dados_form["estado_civil"] ? "bgcolor=\"#FFFF00\"  " : '';
				$modificacao["conjuge"] 				= $reg_editar["conjuge"]!=maiusculas($dados_form["conjuge"]) ? "bgcolor=\"#FFFF00\"  " : '';
				$modificacao["grau_instrucao"] 			= $reg_editar["id_rh_escolaridade"]!=$dados_form["grau_instrucao"] ? "bgcolor=\"#FFFF00\"  " : '';
				$modificacao["data_admissao"] 			= $reg_editar["clt_admissao"]!=php_mysql($dados_form["data_admissao"]) ? "bgcolor=\"#FFFF00\"  " : '';
				$modificacao["pis_data"] 				= $reg_editar["pis_data"]!=php_mysql($dados_form["pis_data"]) ? "bgcolor=\"#FFFF00\"  " : '';
				$modificacao["pis_numero"]			 	= $reg_editar["pis_num"]!=$dados_form["pis_numero"] ? "bgcolor=\"#FFFF00\"  " : '';
				$modificacao["pis_banco"] 				= $reg_editar["pis_banco"]!=$dados_form["pis_banco"] ? "bgcolor=\"#FFFF00\"  " : '';
				$modificacao["horario_entrada"] 		= $reg_editar["horario_entrada"]!=$dados_form["horario_entrada"] ? "bgcolor=\"#FFFF00\"  " : '';
				$modificacao["horario_refeicao"] 		= $reg_editar["refeicao"]!=maiusculas($dados_form["horario_refeicao"]) ? "bgcolor=\"#FFFF00\"  " : '';
				$modificacao["horario_saida"] 			= $reg_editar["horario_saida"]!=$dados_form["horario_saida"] ? "bgcolor=\"#FFFF00\"  " : '';
				$modificacao["descanso_semanal"] 		= $reg_editar["descanso_semanal"]!=maiusculas($dados_form["descanso_semanal"]) ? "bgcolor=\"#FFFF00\"  " : '';
				$modificacao["cor"] 					= $reg_editar["cor"]!=maiusculas($dados_form["cor"]) ? "bgcolor=\"#FFFF00\"  " : '';
				$modificacao["sexo"] 					= $reg_editar["sexo"]!=$dados_form["sexo"] ? "bgcolor=\"#FFFF00\"  " : '';
				$modificacao["cabelos"] 				= $reg_editar["cabelo"]!=maiusculas($dados_form["cabelos"]) ? "bgcolor=\"#FFFF00\"  " : '';
				$modificacao["olhos"] 					= $reg_editar["olhos"]!=maiusculas($dados_form["olhos"]) ? "bgcolor=\"#FFFF00\"  " : '';
				$modificacao["altura"] 					= $reg_editar["altura"]!=$dados_form["altura"] ? "bgcolor=\"#FFFF00\"  " : '';
				$modificacao["peso"] 					= $reg_editar["peso"]!=$dados_form["peso"] ? "bgcolor=\"#FFFF00\"  " : '';
				$modificacao["ref_transp_outros"] 		= $reg_editar["ref_transp_outros"]!=$dados_form["ref_transp_outros"] ? "bgcolor=\"#FFFF00\"  " : '';

				$arq_foto = redimensionar($_FILES["foto"],$dados_form["id_funcionario"],$reg_editar["arquivo_foto"]);

				/*
				$sql = "SELECT * FROM CTT010 WITH(NOLOCK) ";
				$sql .= "WHERE D_E_L_E_T_ = '' "; //CENTRO DE CUSTO
				$sql .= "AND CTT_CUSTO = '".$dados_form["centrocusto"]."' ";

				$db->select($sql,'MSSQL', true);

				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
				}

				$regs_cc = $db->array_select[0];
				*/

				$usql = "UPDATE ".DATABASE.".funcionarios SET ";
				$usql .= "id_setor = '" . $dados_form["setor"] . "', ";
				$usql .= "nivel_atuacao = '" . $dados_form["nivel_atuacao"] . "', ";
				$usql .= "id_funcao = '" . $dados_form["funcao_dvm"] . "', "; //FUNCAO
				$usql .= "id_cargo = '" . $dados_form["cargo_dvm"] . "', ";  //CARGO
				$usql .= "id_setor_aso = '" . $dados_form["setor_aso"] . "', ";
				$usql .= "funcionario = '" . maiusculas($dados_form["funcionario"]) . "', ";
				$usql .= "email_particular = '" . minusculas($dados_form["email_particular"]) . "', ";
				$usql .= "funcionario_endereco = '" . maiusculas($dados_form["endereco"]) . "', ";
				$usql .= "funcionario_bairro = '" . maiusculas($dados_form["bairro"]) . "', ";
				$usql .= "funcionario_cidade = '" . maiusculas($dados_form["cidade"]) . "', ";
				$usql .= "funcionario_cep = '" . maiusculas($dados_form["cep"]) . "', ";
				$usql .= "funcionario_estado = '" . maiusculas($dados_form["estado"]) . "', ";
				$usql .= "filiacao_pai = '" . maiusculas($dados_form["pai"]) . "', ";
				$usql .= "filiacao_mae = '" . maiusculas($dados_form["mae"]) . "', ";
				$usql .= "nacionalidade_pai = '" . maiusculas($dados_form["nacionalidade_pai"]) . "', ";
				$usql .= "nacionalidade_mae = '" . maiusculas($dados_form["nacionalidade_mae"]) . "', ";
				$usql .= "ctps_num = '" . $dados_form["ctps_num"] . "', ";
				$usql .= "ctps_serie = '" . maiusculas($dados_form["ctps_serie"]) . "', ";
				$usql .= "reservista_num= '" . $dados_form["reservista_num"] . "', ";
				$usql .= "reservista_categoria = '" . maiusculas($dados_form["reservista_categoria"]) . "', ";
				$usql .= "titulo_eleitor = '" . $dados_form["titulo_eleitor"] . "', ";
				$usql .= "titulo_zona = '" . $dados_form["titulo_zona"] . "', ";
				$usql .= "titulo_secao = '" . $dados_form["titulo_secao"] . "', ";
				$usql .= "identidade_num = '" . $dados_form["identidade_num"] . "', ";
				$usql .= "identidade_emissor = '" . maiusculas($dados_form["identidade_emissor"]) . "', ";
				$usql .= "data_emissao = '" . php_mysql($dados_form["data_emissao"]) . "', ";
				$usql .= "cpf = '" . $dados_form["cpf_num"] . "', ";
				$usql .= "naturalidade = '" . maiusculas($dados_form["naturalidade"]) . "', ";
				$usql .= "id_nacionalidade = '" . $dados_form["id_nacionalidade"] . "', ";
				$usql .= "estado_nascimento = '" . $dados_form["estado"] . "', ";
				$usql .= "data_nascimento = '" . php_mysql($dados_form["data_nascimento"]) . "', ";
				$usql .= "data_inicio = '" . php_mysql($dados_form["data_inicio"]) . "', ";
				$usql .= "id_categoria_funcional = '" . $dados_form["categoria_funcional"] . "', ";
				$usql .= "id_tipo_pagamento = '" . $dados_form["tipo_pagamento"] . "', ";
				$usql .= "id_empfunc = '" . $dados_form["empresa_dvm_funcionario"] . "', ";
				$usql .= "id_cod_fornec = '" . sprintf("%06d",$dados_form["empresa_funcionario"]) . "', ";
				$usql .= "id_estado_civil = '" . $dados_form["estado_civil"] . "', ";
				$usql .= "conjuge = '" . maiusculas($dados_form["conjuge"]) . "', ";
				$usql .= "id_escolaridade = '" . $dados_form["grau_instrucao"] . "', ";
				$usql .= "clt_matricula = '" . sprintf("%06d",$dados_form["clt_matricula"]) . "', ";
				$usql .= "clt_admissao = '" . php_mysql($dados_form["data_admissao"]) . "', ";
				$usql .= "pis_data = '" . php_mysql($dados_form["pis_data"]) . "', ";
				$usql .= "pis_num = '" . $dados_form["pis_numero"] . "', ";
				$usql .= "pis_banco = '" . $dados_form["pis_banco"] . "', ";
				$usql .= "fgts_data = '" . php_mysql($dados_form["fgts_data"]) . "', ";
				$usql .= "fgts_conta = '" . $dados_form["fgts_conta"] . "', ";
				$usql .= "fgts_banco = '" . $dados_form["fgts_banco"] . "', ";
				$usql .= "fgts_agencia = '" . $dados_form["fgts_agencia"] . "', ";
				$usql .= "id_vinculo_empregaticio = '" . $dados_form["vinculo_empregaticio"] . "', ";
				$usql .= "id_tipo_admissao = '" . $dados_form["tipo_admissao"] . "', ";
				$usql .= "id_turno_trabalho = '" . $dados_form["turno_trabalho"] . "', ";
				$usql .= "horario_entrada = '" . $dados_form["horario_entrada"] . "', ";
				$usql .= "refeicao = '" . maiusculas($dados_form["horario_refeicao"]) . "', ";
				$usql .= "horario_saida = '" . $dados_form["horario_saida"] . "', ";
				$usql .= "descanso_semanal = '" . maiusculas($dados_form["descanso_semanal"]) . "', ";
				$usql .= "cor = '" . maiusculas($dados_form["cor"]) . "', ";
				$usql .= "sexo = '" . $dados_form["sexo"] . "', ";
				$usql .= "tipo_sanguineo = '" . $dados_form["tipo_sanguineo"] . "', ";
				$usql .= "cabelo = '" . maiusculas($dados_form["cabelos"]) . "', ";
				$usql .= "olhos = '" . maiusculas($dados_form["olhos"]) . "', ";
				$usql .= "altura = '" . $dados_form["altura"] . "', ";
				$usql .= "peso = '" . $dados_form["peso"] . "', ";
				$usql .= "idade = '" . $dados_form["idade"] . "', ";
				$usql .= "id_centro_custo = '" . $dados_form["centrocusto"] . "', ";
				$usql .= "id_produto = '" . $dados_form["produto"] . "', ";
				$usql .= "id_local = '" . $dados_form["local_trabalho"] . "', ";
				$usql .= "celular = '" . $dados_form["celular"] . "', ";
				$usql .= "telefone = '" . $dados_form["telefone"] . "', ";
				$usql .= "arquivo_foto = '" . $arq_foto . "', ";
				$usql .= "tipo_demissao = '" . $dados_form["demissao"] . "', ";
				$usql .= "data_desligamento = '" . php_mysql($dados_form["data_desligamento"]) . "', ";
				$usql .= "item_contabil = '" . $dados_form["site"] . "', ";
				$usql .= "situacao = '" . $dados_form["situacao"] . "', ";
				$usql .= "id_local = '" . $dados_form["local_trabalho"] . "', ";
				$usql .= "tipo_empresa = '" . $dados_form["tipo_tributacao"] . "', ";
				$usql .= "sigla_func = '" . maiusculas($dados_form["sigla_func"]) . "', ";
				$usql .= "ref_transp_outros = '" . $dados_form["ref_transp_outros"] . "' ";
				$usql .= "WHERE id_funcionario = '" . $dados_form["id_funcionario"] . "' ";

				$db->update($usql,'MYSQL');
				
				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
				}

				//Alteração na tabela de salários apenas para algumas pessoas
				if (in_array($_SESSION['id_funcionario'], array(0)) && !empty($dados_form['data_admissao']))
				{
					$usql = "UPDATE ".DATABASE.".salarios SET ";
					$usql .= "data = '".php_mysql($dados_form['data_admissao'])."' ";
					$usql .= "WHERE id_funcionario = '".$dados_form["id_funcionario"]."' ";
					$usql .= "AND salarios.reg_del = 0 ";
					
					$db->update($usql,'MYSQL');
					
					if($db->erro!='')
					{
						$resposta->addAlert($db->erro);
					}					
				}

				//Insere o usuario
				$sql = "SELECT * FROM ".DATABASE.".usuarios U ";
				$sql .= "JOIN ".DATABASE.".funcionarios f ON f.id_usuario = U.id_usuario AND f.reg_del = 0 ";
				$sql .= "WHERE f.id_funcionario = '".$dados_form["id_funcionario"]."' ";
				$sql .= "AND U.reg_del = 0 ";
				
				$db->select($sql, 'MYSQL',true);
				
				$retornoUsuario = $db->array_select[0];
				
				//se não houver registros, insere
				if ($db->numero_registros == 0)
				{
					$isql = "INSERT INTO ".DATABASE.".usuarios ";
					$isql .= "(email, login, senha, status) ";
					$isql .= "VALUES (";
					//$isql .= "'" . $dados_form['id_funcionario'] . "', ";
					$isql .= "'" . minusculas(trim($dados_form["email"])) . "', ";
					$isql .= "'" . trim($dados_form["login"]) . "', ";
					$isql .= "'" . $senha . "', ";
					$isql .= "'1') ";

					$db->insert($isql,'MYSQL');
					
					if($db->erro!='')
					{
						$resposta->addAlert($db->erro);
					}
				}
				
				//Envio de email caso haja alteração de e-mail, principalmente devido á mudança de PJ para CLT ou vice versa
				if($retornoUsuario['email'] != $dados_form['email'])
				{
					if(ENVIA_EMAIL)
					{
						$params 			= array();
						$params['from']		= "ti@dominio.com.br";
						$params['from_name']= "RH - Alteração de E-mail";
						$params['subject'] 	= "ALTERAÇÃO DE E-MAIL DE COLABORADOR";
						$params['emails']['to'][] = array('email' => 'ti@dominio.com.br', 'nome' => 'Suporte');

						$corpoAlteracao =  "<b>ALTERAÇÃO DE E-MAIL DE COLABORADOR</b>: ".$retornoUsuario['funcionario']."<br />";
						$corpoAlteracao .= "<b>E-MAIL ANTERIOR</b>: ".$retornoUsuario['email']."<br />";
						$corpoAlteracao .= "<b>E-MAIL ATUAL</b> ".$dados_form['email']."<br />";
						$corpoAlteracao .= "<b>AO TI,<br />Favor, alterar o e-mail do colaborador citado no Active Directory.</b> ";
						
						$mail = new email($params);
						
						$mail->montaCorpoEmail($corpoAlteracao);

						if(!$mail->Send())
						{
							$resposta->addAlert('Erro ao enviar e-mail!!! '.$mail->ErrorInfo);
						}

						$mail->ClearAddresses();
					}
					else
					{
						$resposta->addScriptCall('modal', $corpoAlteracao, '300_650', 'Conteúdo email', 6);	
					}
				}
				
				//Envio de email caso haja alteração de status, principalmente devido á mudança de Fechamento Folha para Ativo
				if($retornoUsuario['situacao'] != $dados_form["situacao"])
				{
					if(ENVIA_EMAIL)
					{
						$params 			= array();
						$params['from']		= "ti@dominio.com.br";
						$params['from_name']= "RH - Reativacao de colaborador";
						$params['subject'] 	= "ALTERAÇÃO DE SITUAÇÃO DE COLABORADOR";
						$params['emails']['to'][] = array('email' => 'ti@dominio.com.br', 'nome' => 'Suporte');

						$corpoAlteracao =  "<b>ALTERAÇÃO DE SITUAÇÃO DE COLABORADOR</b>: ".$retornoUsuario['funcionario']."<br />";
						$corpoAlteracao .= "<b>SITUAÇÃO ANTERIOR</b>: ".$retornoUsuario['situacao']."<br />";
						$corpoAlteracao .= "<b>SITUAÇÃO ATUAL</b> ".$dados_form['situacao'];
						
						$mail = new email($params);
						
						$mail->montaCorpoEmail($corpoAlteracao);

						if(!$mail->Send())
						{
							$resposta->addAlert('Erro ao enviar e-mail!!! '.$mail->ErrorInfo);
						}

						$mail->ClearAddresses();
					}
					else 
					{
						$resposta->addScriptCall('modal', $corpoAlteracao, '300_650', 'Conteúdo email', 7);
					}
				}
				
				/**
				 * 28/01/2015
				 * Carlos Máximo
				 */
				//Adicionado o contrato do colaborador
				$erro = gravarExigenciasCliente($dados_form);
				
				if ($erro != '')
				{
					$resposta->addAlert($erro);
				}
				/**
				 * Final das alteracoes 28/01/2015
				 */
				
				/**
				 * 27/02/2015
				 * Carlos Máximo
				 */	
				//Adicionado o contrato do colaborador
				$erro = gravarAjudaCusto($dados_form);
				
				if ($erro != '')
				{
					$resposta->addAlert($erro);
				}
				/**
				 * Final das alteracoes 27/02/2015
				 */

				//05/01/2015
				//Buscando notebooks que o usuario possa estar usando.
				$sql = "SELECT
							equipamento, e.num_dvm
						FROM
							".DATABASE.".inventario i
							JOIN ".DATABASE.".equipamentos e on e.id_equipamento = i.id_equipamento
						WHERE
							i.reg_del = 0
							AND e.reg_del = 0
							AND id_funcionario = '".$dados_form["id_funcionario"]."'
							AND situacao = 1 ";

				$db->select($sql, 'MYSQL',true);

				$equipamentos = '';
				
				if ($db->erro == '')
				{
					if ($db->numero_registros > 0)
					{
						$equipamentos = 'O colaborador esta de posse do(s) equipamento(s): <br />';
						$sep = '';

						foreach ($db->array_select as $regs)
						{
							$equipamentos .= $sep.$regs['equipamento'].' ('.$regs['num_dvm'].')';
							$sep = ', ';
						}
					}
				}
					
				//Alteracoes feitas em 07/08/2008
				//Se situacao for fechamento folha (email)
				if($dados_form["situacao"]=="FECHAMENTO FOLHA")
				{
					// Se tipo contrato for CLT
					if(in_array($dados_form["tipo_contrato"],array('CLT','SC+CLT','SC+MENS+CLT')))
					{
						$DEMISSAO = "<table width=\"100%\" border=\"0\">";
						$DEMISSAO .="<tr>
											<td width=\"7%\"> </td>
											<td width=\"19%\"><span style=\"color: #006699;	font-weight: bold; text-decoration: underline; font-family: Verdana, Arial;\">".NOME_EMPRESA."</span></td>
											<td width=\"67%\"> </td>
											<td width=\"7%\"> </td>
										</tr>";
						$DEMISSAO .= "<tr>
											<td> </td>
											<td> </td>
											<td> </td>
											<td> </td>
										</tr>";
						$DEMISSAO .="<tr>
											<td> </td>
											<td colspan=\"2\">". CIDADE .", ". date('d')." de ".meses(date('m')-1,1)." de ".date('Y') ."</td>
											<td> </td>
										</tr>";

						$DEMISSAO .="<tr>
											<td> </td>
											<td> </td>
											<td> </td>
											<td> </td>
										</tr>";
						$DEMISSAO .="<tr>
											<td> </td>
											<td>A/C  </td>
											<td> </td>
											<td> </td>
										</tr>";
						$DEMISSAO .="<tr>
											<td> </td>
											<td> </td>
											<td> </td>
											<td> </td>
										</tr>";
						$DEMISSAO .="<tr>
											<td> </td>
											<td colspan=\"2\" align=\"center\" style=\"color: #FF0000; font-weight: bold;\">DESLIGAMENTO DE FUNCIONÁRIO</td>
											<td> </td>
										</tr>";
						$DEMISSAO .="<tr>
											<td> </td>
											<td> </td>
											<td> </td>
											<td> </td>
										</tr>";
						$DEMISSAO .="<tr>
											<td> </td>
											<td colspan=\"2\">Gentileza providenciar o processo de desligamento do funcionário abaixo:</td>
											<td> </td>
										</tr>";
						$DEMISSAO .="<tr>
											<td> </td>
											<td> </td>
											<td> </td>
											<td> </td>
										 </tr>";
						$DEMISSAO .="<tr>
											<td> </td>
											<td colspan=\"2\">";
						$DEMISSAO .="		<table width=\"100%\" border=\"1\">";
						$DEMISSAO .="			<tr>
														<td style=\"font-family: Arial, Helvetica, sans-serif; font-weight: bold; font-size: 12px;\">NOME:</td>
														<td colspan=\"8\" style=\"font-family: Arial, Helvetica, sans-serif; font-size: 10px;\">". maiusculas($dados_form["funcionario"]) ."</td>
													</tr>";
						$DEMISSAO .="	  		<tr>
														<td style=\"font-family: Arial, Helvetica, sans-serif; font-weight: bold; font-size: 12px;\">DATA DE SAÍDA:</td>
														<td colspan=\"8\" style=\"font-family: Arial, Helvetica, sans-serif; font-size: 10px;\">". $dados_form["data_desligamento"] ."</td>
													</tr>";
						$DEMISSAO .="      		<tr>
														<td style=\"font-family: Arial, Helvetica, sans-serif; font-weight: bold; font-size: 12px;\">MOTIVO:</td>
														<td colspan=\"8\" style=\"font-family: Arial, Helvetica, sans-serif; font-size: 10px;\">". $dados_form["demissao"] ."</td>
													</tr>";

						$DEMISSAO .="        </table>";
						$DEMISSAO .="	</td>";
						$DEMISSAO .="</tr>";
						$DEMISSAO .="<tr align=\"center\" style=\"font-family: Arial, Helvetica, sans-serif; font-weight: bold; font-size: 12px;\">
											<td colspan=\"4\" >Rua XXXXXX, XX - Centro - XXXXXXX - SP</td>
										</tr>";
						$DEMISSAO .="<tr align=\"center\" style=\"font-family: Arial, Helvetica, sans-serif; font-weight: bold; font-size: 12px;\">
										<td colspan=\"4\" >CEP: XXXXXX - TEL: (11) XXXXXX - FAX: (11) XXXXXXX </td>
									  </tr>";
						$DEMISSAO .="<tr align=\"center\" style=\"font-family: Arial, Helvetica, sans-serif; font-weight: bold; font-size: 12px;\">
										<td colspan=\"4\" >Site: http://www.empresa.com.br - E-mail: empresa@dominio.com.br</td>
									  </tr>";
						$DEMISSAO .="</table>";
						
						if(ENVIA_EMAIL)
						{

							$params 			= array();
							$params['from']		= "recrutamento@dominio.com.br";
							$params['from_name']= "RECURSOS HUMANOS";
							$params['subject'] 	= "DESLIGAMENTO FUNCIONARIO - CLT";

							$mail = new email($params, 'desligamento_funcionario');
							
							$mail->montaCorpoEmail($DEMISSAO);

							if(!$mail->Send())
							{
								$resposta->addAlert('Erro ao enviar e-mail!!! '.$mail->ErrorInfo);
							}

							$mail->ClearAddresses();
						}
						else 
						{
							$resposta->addScriptCall('modal', $DEMISSAO, '300_650', 'Conteúdo email', 8);	
						}

						$resposta->addScriptCall("imprimir_formularios('".$dados_form["id_funcionario"]."')");
					}

					$TI = CIDADE . ", ". date('d')." de ".meses(date('m')-1,1)." de ".date('Y') ."<br><br><br>";
					$TI .= "<span style=\"color: #FF0000; font-weight: bold; text-decoration: underline; font-family: Verdana, Arial;\">USUÁRIO PRESTES A SER DESLIGADO</span><br><br><br>";
					$TI .= "Nome: <strong>".$dados_form["funcionario"]."</strong><br>";
					$TI .= "Função: <strong>".$reg_editar["descricao"]."</strong><br><br><br>";
					$TI .= "Centro Custo: <strong>".$dados_form["centrocusto"]." - ".$regs_cc["CTT_DESC01"]."</strong><br><br><br>";
					$TI .= "login: <strong>".$dados_form["login"]."</strong><br>";
					$TI .= "E-mail: <strong>".$dados_form["email"]."</strong><br>";
					$TI .= $equipamentos.'<br><br><br>';
										
					$TI .= "Atenciosamente, Depto. Recursos Humanos.";

					if(ENVIA_EMAIL)
					{

						$params 			= array();
						$params['from']		= "recrutamento@dominio.com.br";
						$params['from_name']= "RECURSOS HUMANOS";
						$params['subject'] 	= "USUÁRIO PRESTES A SER DESLIGADO";

						$sql = "SELECT * FROM ".DATABASE.".funcionarios, ".DATABASE.".usuarios ";
						$sql .= "WHERE funcionarios.nivel_atuacao IN ('C','S') ";
						$sql .= "AND funcionarios.reg_del = 0 ";
						$sql .= "AND usuarios.reg_del = 0 ";
						$sql .= "AND funcionarios.situacao = 'ATIVO' ";
						$sql .= "AND funcionarios.id_usuario = usuarios.id_usuario ";

						$db->select($sql,'MYSQL',true);

						if($db->erro!='')
						{
							$resposta->addAlert($db->erro);
						}

						foreach ($db->array_select as $reg_coord)
						{
							$params['emails']['to'][] = array('email' => $reg_coord["email"], 'nome' => $reg_coord["funcionario"]);
						}

						$mail = new email($params, 'usuario_sera_desligado');
						
						$mail->montaCorpoEmail($TI);

						if(!$mail->Send())
						{
							$resposta->addAlert('Erro ao enviar e-mail!!! '.$mail->ErrorInfo);
						}

						$mail->ClearAddresses();
					}
					else 
					{
						$resposta->addScriptCall('modal', $TI, '300_650', 'Conteúdo email', 9);
					}

					$DEPT = CIDADE . ", ". date('d')." de ".meses(date('m')-1,1)." de ".date('Y') ."<br><br><br>";
					$DEPT .= "<span style=\"color: #FF0000; font-weight: bold; text-decoration: underline; font-family: Verdana, Arial;\">USUÁRIO PRESTES A SER DESLIGADO</span><br><br><br>";
					$DEPT .= "Nome: <strong>".$dados_form["funcionario"]."</strong><br>";
					$DEPT .= "Função: <strong>".$reg_editar["descricao"]."</strong><br><br><br>";
					$DEPT .= "Centro Custo: <strong>".$dados_form["centrocusto"]." - ".$regs_cc["CTT_DESC01"]."</strong><br><br><br>";
					$DEPT .= "E-mail: <strong>".$dados_form["email"]."</strong><br>";
					$DEPT .= $equipamentos.'<br><br><br>';
					$DEPT .= "Atenciosamente, Depto. Recursos Humanos.";

					if(ENVIA_EMAIL)
					{

						$params 			= array();
						$params['from']		= "recrutamento@dominio.com.br";
						$params['from_name']= "RECURSOS HUMANOS";
						$params['subject'] 	= "COLABORADOR EM FECHAMENTO / AVISO PREVIO(CLT)";

						$mail = new email($params, 'colaborador_fechamento');
						
						$mail->montaCorpoEmail($DEPT);

						if(!$mail->Send())
						{
							$resposta->addAlert('Erro ao enviar e-mail!!! '.$mail->ErrorInfo);
						}

						$mail->ClearAddresses();
					}
					else 
					{
						$resposta->addScriptCall('modal', $DEPT, '300_650', 'Conteúdo email', 10);
					}

					$usql = "UPDATE ".DATABASE.".os_x_funcionarios SET ";
					$usql .= "reg_del = 1, ";
					$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
					$usql .= "data_del = '".date('Y-m-d')."' ";					
					$usql .= "WHERE id_funcionario = '".$dados_form["id_funcionario"]."' ";
					$usql .= "AND reg_del = 0 ";

					$db->update($usql,'MYSQL');

					if($db->erro!='')
					{
						$resposta->addAlert($db->erro);
					}
					
					//retira as permissões para evitar acessos indevidos
					$usql = "UPDATE ".DATABASE.".permissoes SET ";
					$usql .= "reg_del = 1, ";
					$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
					$usql .= "data_del = '".date('Y-m-d')."' ";
					$usql .= "WHERE id_usuario = '".$retornoUsuario["id_usuario"]."' ";
					$usql .= "AND id_sub_modulo NOT IN ('2','4') ";  //apontamento, fechamento PJ
					$usql .= "AND reg_del = 0 ";

					$db->update($usql,'MYSQL');	

					if($db->erro!='')
					{
						$resposta->addAlert($db->erro);
					}					
				}
				else
				{
				    if($dados_form["situacao"]=="DESLIGADO" && !empty($dados_form["login"]))
					{
						$TI = CIDADE . ", ". date('d')." de ".meses(date('m')-1,1)." de ".date('Y') ."<br><br><br>";
						$TI .= "<span style=\"color: #FF0000; font-weight: bold; text-decoration: underline; font-family: Verdana, Arial;\">DESLIGAMENTO de USUÁRIO</span><br><br><br>";
						$TI .= "Fica aqui registrado o desligamento do colaborador: <br><br>";
						$TI .= "Nome: <strong>".$dados_form["funcionario"]."</strong><br>";
						$TI .= "Função: <strong>".$reg_editar["descricao"]."</strong><br><br><br>";
						$TI .= "login: <strong>".$dados_form["login"]."</strong><br>";
						$TI .= "E-mail: <strong>".$dados_form["email"]."</strong><br><br>";
						$TI .= "Favor providenciar os procedimentos de back-up da maquina, quando aplicado, e<br>";
						$TI .= "remover a conta do usuário acima citado.<br>";
						$TI .= $equipamentos.'<br><br><br>';
						$TI .= "Atenciosamente, Depto. Recursos Humanos.";

						$DEPT = CIDADE . ", ". date('d')." de ".meses(date('m')-1,1)." de ".date('Y') ."<br><br><br>";
						$DEPT .= "<span style=\"color: #FF0000; font-weight: bold; text-decoration: underline; font-family: Verdana, Arial;\">DESLIGAMENTO de USUÁRIO</span><br><br><br>";
						$DEPT .= "Fica aqui registrado o desligamento do colaborador: <br><br>";
						$DEPT .= "Nome: <strong>".$dados_form["funcionario"]."</strong><br>";
						$DEPT .= "Cargo: <strong>".$reg_editar["descricao"]."</strong><br><br><br>";
						$DEPT .= "Centro Custo: <strong>".$dados_form["centrocusto"]." - ".$regs_cc["CTT_DESC01"]."</strong><br><br><br>";

						//Busca a empresa do funcionario
						$sql = "SELECT * FROM ".DATABASE.".empresa_funcionarios ";
						$sql .= "WHERE id_empfunc = '".$reg_editar["id_empfunc"]."' ";
						$sql .= "AND reg_del = 0 ";

						$db->select($sql,'MYSQL',true);

						if($db->erro!='')
						{
							$resposta->addAlert($db->erro);
						}

						$reg_emp = $db->array_select[0];

						if($db->numero_registros>0)
						{
							$DEPT .= "Empresa: <strong>".$reg_emp["empresa_func"]."</strong><br><br><br>";
						}

						$DEPT .= "E-mail: <strong>".$dados_form["email"]."</strong><br><br><br><br>";
						$DEPT .= "Atenciosamente, Depto. Recursos Humanos.";

						//altera o status da empresa
						//Busca a empresa do funcionario
						$sql = "SELECT * FROM ".DATABASE.".empresa_funcionarios ";
						$sql .= "WHERE empresa_socio = '".$dados_form["id_funcionario"]."' ";
						$sql .= "AND reg_del = 0 ";

						$db->select($sql,'MYSQL',true);

						if($db->erro!='')
						{
							$resposta->addAlert($db->erro);
						}

						if($db->numero_registro>0)
						{
							$reg = $db->array_select[0];

							$usql = "UPDATE ".DATABASE.".empresa_funcionarios SET ";
							$usql .= "empresa_situacao = 0 ";
							$usql .= "WHERE id_empfunc = '".$reg["id_empfunc"]."' ";
							$usql .= "AND reg_del = 0 ";

							$db->update($usql,'MYSQL');

							if($db->erro!='')
							{
								$resposta->addAlert($db->erro);
							}
						}

						$sql = "SELECT * FROM ".DATABASE.".usuarios, ".DATABASE.".funcionarios ";
						$sql .= "WHERE funcionarios.id_funcionario = '".$dados_form["id_funcionario"]."' ";
						$sql .= "AND funcionarios.id_usuario = usuarios.id_usuario ";
						$sql .= "AND reg_del = 0 ";

						$db->select($sql,'MYSQL',true);

						if($db->erro!='')
						{
							$resposta->addAlert($db->erro);
						}

						$reg_u = $db->array_select[0];					

						/*
                        OCOMON - 30/01/2014
                        */						
						if (!empty($reg_u['login']))
						{
							//Removendo o usuário da lista de e-mails caso exista
							$usql = "UPDATE ".DATABASE.".lista_emails SET ";
							$usql .= "reg_del = 1, ";
							$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
							$usql .= "data_del = '".date('Y-m-d')."' ";
							$usql .= "WHERE le_email = '".trim($reg_u['email'])."' ";
							$usql .= "AND reg_del = 0 ";
							
							$db->update($usql,'MYSQL');
							
							if($db->erro!='')
							{
								$resposta->addAlert($db->erro);
							}
						}
						
						//metodo antigo de permissões
						$usql = "UPDATE ".DATABASE.".permissoes SET ";
						$usql .= "reg_del = 1, ";
						$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
						$usql .= "data_del = '".date('Y-m-d')."' ";
						$usql .= "WHERE id_usuario = '". $reg_u["id_usuario"] ."' ";
						$usql .= "AND reg_del = 0 ";

						$db->update($usql,'MYSQL');

						if($db->erro!='')
						{
							$resposta->addAlert($db->erro);
						}
						
						//exclui das equipes
						$usql = "UPDATE ".DATABASE.".os_x_funcionarios SET ";
						$usql .= "reg_del = 1, ";
						$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
						$usql .= "data_del = '".date('Y-m-d')."' ";
						$usql .= "WHERE id_funcionario = '".$dados_form["id_funcionario"]."' ";
						$usql .= "AND reg_del = 0 ";

						$db->update($usql,'MYSQL');

						if($db->erro!='')
						{
							$resposta->addAlert($db->erro);
						}
						
						//exclui o usuário
						$usql = "UPDATE ".DATABASE.".usuarios SET ";
						$usql .= "reg_del = 1, ";
						$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
						$usql .= "data_del = '".date('Y-m-d')."' ";
						$usql .= "WHERE id_usuario = '". $reg_u["id_usuario"] ."' ";
						$usql .= "AND reg_del = 0 ";

						$db->update($usql,'MYSQL');

						if($db->erro!='')
						{
							$resposta->addAlert($db->erro);
						}

						if(ENVIA_EMAIL)
						{
							$params 			= array();
							$params['from']		= "recrutamento@dominio.com.br";
							$params['from_name']= "RECURSOS HUMANOS";
							$params['subject'] 	= "DESLIGAMENTO DE USUARIO";

							$mail = new email($params, 'desligamento_usuario');
							
							$mail->montaCorpoEmail($TI);

							if(!$mail->Send())
							{
								$resposta->addAlert('Erro ao enviar e-mail!!! '.$mail->ErrorInfo);
							}

							$mail->ClearAddresses();
						}
						else 
						{
							$resposta->addScriptCall('modal', $TI, '300_650', 'Conteúdo email', 11);
						}

						if(ENVIA_EMAIL)
						{
							$params 			= array();
							$params['from']		= "recrutamento@dominio.com.br";
							$params['from_name']= "RECURSOS HUMANOS";
							$params['subject'] 	= "DESLIGAMENTO DE COLABORADOR";

							$mail = new email($params, 'desligamento_colaborador');
							
							$mail->montaCorpoEmail($DEPT);

							if(!$mail->Send())
							{
								$resposta->addAlert('Erro ao enviar e-mail!!! '.$mail->ErrorInfo);
							}

							$mail->ClearAddresses();
						}
						else 
						{
							$resposta->addScriptCall('modal', $DEPT, '300_650', 'Conteúdo email', 12);
						}
						
						//inativa o recurso no PROTHEUS
						/*
						$usql = "UPDATE AE8010 SET ";
						$usql .= "AE8_ATIVO = '2' ";
						$usql .= "WHERE AE8_RECURS = 'FUN_".sprintf("%011d",$dados_form["id_funcionario"])."' ";
						$usql .= "AND D_E_L_E_T_ = '' ";

						$db->update($usql,'MSSQL');

						if($db->erro!='')
						{
							$resposta->addAlert($db->erro);
						}
						*/
					}
					else
					{

						$usql = "UPDATE ".DATABASE.".dependentes_funcionarios SET ";
						$usql .= "reg_del = 1, ";
						$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
						$usql .= "data_del = '".date('Y-m-d')."' ";
						$usql .= "WHERE id_funcionario = '" . $dados_form["id_funcionario"] . "' ";
						$usql .= "AND reg_del = 0 ";

						$db->update($usql,'MYSQL');

						if($db->erro!='')
						{
							$resposta->addAlert($db->erro);
						}

						for($i=1;$i<=6;$i++)
						{
							if($dados_form["nome_dep".$i]!="")
							{
								$DEPENDENTES .="<tr align=\"center\" style=\"font-family: Arial, Helvetica, sans-serif; font-size: 10px;\">
														<td>". maiusculas($dados_form["nome_dep".$i]) ."</td>
														<td>". $dados_form["data_dep".$i] ."</td>
														<td>". maiusculas($dados_form["parentesco_dep".$i]) ."</td>
													</tr>";

								if(!in_array($dados_form["nome_dep".$i],$dep_nome))
								{
									$modificacao["dependente"] = "bgcolor=\"#FFFF00\"  ";
								}

								$isql = "INSERT INTO ".DATABASE.".dependentes_funcionarios ";
								$isql .= "(id_funcionario, nome_dependente, data_nascimento, parentesco) ";
								$isql .= "VALUES (";
								$isql .= "'" . $dados_form["id_funcionario"] . "', ";
								$isql .= "'" . maiusculas($dados_form["nome_dep".$i]) . "', ";
								$isql .= "'" . php_mysql($dados_form["data_dep".$i]) . "', ";
								$isql .= "'" . maiusculas($dados_form["parentesco_dep".$i]) . "') ";

								$db->insert($isql,'MYSQL');

								if($db->erro!='')
								{
									$resposta->addAlert($db->erro);
								}
							}
						}

						$usql = "UPDATE ".DATABASE.".usuarios SET ";
						$usql .= "email = '" . minusculas(trim($dados_form["email"])) . "', ";
						$usql .= "login = '" . trim($dados_form["login"]) . "' ";
						$usql .= "WHERE id_usuario = '" . $reg_u["id_usuario"] . "' ";
						$usql .= "AND reg_del = 0 ";

						$db->update($usql,'MYSQL');

						if($db->erro!='')
						{
							$resposta->addAlert($db->erro);
						}
					}
				}

				//redmine #60 - Adicionado em 26/09/2008 - Otávio
				//Seleciona os dados do salario
				$sql = "SELECT * FROM ".DATABASE.".salarios ";
				$sql .= "WHERE salarios.id_funcionario = '" . $dados_form["id_funcionario"] . "' ";
				$sql .= "AND salarios.reg_del = 0 ";
				$sql .= "ORDER BY salarios.data DESC, salarios.id_salario DESC LIMIT 1 ";

				$db->select($sql,'MYSQL',true);

				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
				}

				$reg_sal = $db->array_select[0];

				//inclusao da formacao
				
				$usql = "UPDATE ".DATABASE.".rh_formacao SET ";
				$usql .= "reg_del = 1, ";
				$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
				$usql .= "data_del = '".date('Y-m-d')."' ";
				$usql .= "WHERE id_funcionario = '" . $dados_form["id_funcionario"] . "' ";
				$usql .= "AND reg_del = 0 ";

				$db->update($usql,'MYSQL');

				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
				}

				for($i=0;$i<=$dados_form["itens"];$i++)
				{
					if($dados_form["instituicao_ensino_".$i]!="" && $dados_form["descricao_formacao_".$i]!="")
					{
						$isql = "INSERT INTO ".DATABASE.".rh_formacao ";
						$isql .= "(id_funcionario, id_instituicao, descricao, ano_conclusao) ";
						$isql .= "VALUES (";
						$isql .= "'" . $dados_form["id_funcionario"] . "', ";
						$isql .= "'" . $dados_form["instituicao_ensino_".$i] . "', ";
						$isql .= "'" . maiusculas($dados_form["descricao_formacao_".$i]) . "', ";
						$isql .= "'" . $dados_form["ano_conclusao_".$i] . "') ";

						$db->insert($isql,'MYSQL');

						if($db->erro!='')
						{
							$resposta->addAlert($db->erro);
						}
					}
				}

				//Verifica se foi alterado o tipo de Contrato
				if($reg_sal[" tipo_contrato"]!==$dados_form["tipo_contrato"])
				{
					//Insere o salario e tipo contrato
					$isql = "INSERT INTO ".DATABASE.".salarios ";
					$isql .= "(id_funcionario,  tipo_contrato, id_tipo_salario, salario_clt, salario_mensalista, salario_hora, data, id_func_altera, data_altera) ";
					$isql .= "VALUES (";
					$isql .= "'" . $dados_form["id_funcionario"] . "', ";
					$isql .= "'" . $dados_form["tipo_contrato"] . "', ";
					$isql .= "'" . trim($dados_form["tipo_salario"]) . "', ";
					$isql .= "'" . str_replace(",",".",str_replace(".","",$dados_form["salario_inicial"])) . "', ";
					$isql .= "'" . str_replace(",",".",str_replace(".","",$dados_form["salario_mensal"])) . "', ";
					$isql .= "'" . str_replace(",",".",str_replace(".","",$dados_form["salario_hora"])) . "', ";
					$isql .= "'" . date("Y-m-d") . "', ";
					$isql .= "'" . $_SESSION["id_funcionario"] . "', ";
					$isql .= "'" . date("Y-m-d") . "') ";

					$db->insert($isql,'MYSQL');

					if($db->erro!='')
					{
						$resposta->addAlert($db->erro);
					}
				}

				//preve a alteracao do protheus - recursos
				$sql = "SELECT * FROM ".DATABASE.".rh_cargos, ".DATABASE.".rh_funcoes ";
				$sql .= "WHERE rh_cargos.id_cargo_grupo =  rh_funcoes.id_cargo_grupo ";
				$sql .= "AND rh_cargos.reg_del = 0 ";
				$sql .= "AND rh_funcoes.reg_del = 0 ";
				$sql .= "AND rh_funcoes.id_funcao = '".$dados_form["funcao_dvm"]."' ";

				$db->select($sql,'MYSQL',true);

				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
				}

				$reg1 = $db->array_select[0];

				$ae8_funcao = $reg1["id_cargo_grupo"];

				$nivel_atuacao = array('P'); //PACOTE

				if(!in_array($nivel_atuacao,$dados_form["nivel_atuacao"]))
				{
					//status protheus
					if($dados_form["situacao"]=="DESLIGADO")
					{
						$status = 2;
					}
					else
					{
						$status = 1;
					}
					
					/*
					$usql = "UPDATE AE8010 SET ";
					$usql .= "AE8_EQUIP = '".sprintf("%010d",$dados_form["setor"])."', ";
					$usql .= "AE8_FUNCAO = '".sprintf("%09d",$ae8_funcao)."', ";
					$usql .= "AE8_EMAIL = '".minusculas(trim($dados_form["email"]))."', ";
					$usql .= "AE8_ATIVO = '".$status."', ";
					$usql .= "AE8_XFUNC = '".maiusculas(tiraacentos($reg1["descricao"]))."' ";
					$usql .= "WHERE AE8_RECURS = 'FUN_".sprintf("%011d",$dados_form["id_funcionario"])."' ";
					$usql .= "AND D_E_L_E_T_ = '' ";

					$db->update($usql,'MSSQL');

					if($db->erro!='')
					{
						$resposta->addAlert($db->erro);
					}
					*/
				}

				$resposta->addAlert("funcionario atualizado com sucesso.");
				
				//$resposta->addScript("window.location='".$_SERVER['PHP_SELF']."';");

				$TI = CIDADE . ", ". date('d')." de ".meses(date('m')-1,1)." de ".date('Y') ."<br><br><br>";
				$TI .= "<span style=\"color: #FF0000; font-weight: bold; text-decoration: underline; font-family: Verdana, Arial;\">ALTERAÇÃO DE USUÁRIO</span><br><br><br>";
				$TI .= "Fica aqui registrado a alteracao do colaborador: <br><br>";
				$TI .= "ID: <strong>".$dados_form["id_funcionario"]."</strong><br>";
				$TI .= "Nome: <strong>".$dados_form["funcionario"]."</strong><br>";
				$TI .= "Função: <strong>".$reg_editar["descricao"]."</strong><br><br><br>";
				$TI .= "E-mail: <strong>".$dados_form["email"]."</strong><br><br>";
				$TI .= "Atenciosamente, Depto. Recursos Humanos.";

				if(ENVIA_EMAIL)
				{
				
					$params 			= array();
					$params['from']		= "recrutamento@dominio.com.br";
					$params['from_name']= "RECURSOS HUMANOS";
					$params['subject'] 	= "ALTERACAO DE USUARIO";

					$mail = new email($params, 'alteracao_usuario');
					
					$mail->montaCorpoEmail($TI);

					if(!$mail->Send())
					{
						$resposta->addAlert('Erro ao enviar e-mail!!! '.$mail->ErrorInfo);
					}

					$mail->ClearAddresses();
				}
				else
				{
					$resposta->addScriptCall('modal', $TI, '300_650', 'Conteúdo email', 13);
				}
			}
			
			$resposta->addScript("xajax_atualizatabela('',xajax.getFormValues('frm_funcionarios'));");
		}
		else
		{
			$resposta->addAlert("Preencher os campos obrigatorios(*)");
		}
	}

	return $resposta;
}

/**
 * Função responsável pela gravação dos equipamentos de ti, administrativos e softwares
 */
function gravarNecessidadesColaborador($dados_form)
{
	$idFuncionario = $dados_form['id_funcionario'];
	$idlocalTrabalho = $dados_form['local_trabalho'];
	$dataInclusao = date('Y-m-d');

	$erro = '';
	$erroBanco = '';
	
	$db = new banco_dados();

	if ($idFuncionario > 0)
	{
		$usql = "UPDATE ".DATABASE.".rh_necessidades_x_funcionario SET ";
		$usql .= "reg_del = 1, ";
		$usql .= "data_del = '".$dataInclusao."', ";
		$usql .= "reg_who = ".$_SESSION['id_funcionario']." ";
		$usql .= "WHERE id_funcionario = ".$idFuncionario." ";
		$usql .= "AND reg_del = 0 ";
		
		$db->update($usql, 'MYSQL');
		
		$erroBanco = $db->erro;
	}

	//$resposta->addAlert(print_r($dados_form['infra_ti']));
	
	if (count($dados_form['infra_ti']) > 0 && $erroBanco == '')
	{
		$tipoNecessidade = 1;

		$sep = ', ';

		$isql = "INSERT INTO ".DATABASE.".rh_necessidades_x_funcionario (id_necessidade, tipo_necessidade, id_funcionario, id_local) VALUES ";
		
		foreach($dados_form['infra_ti'] as $equip)
		{
			$equip = intval($equip);
			
			$isql .= "(".intval($equip).", ".$tipoNecessidade.", ".$idFuncionario.", ".$idlocalTrabalho.")".$sep;
		}

		if (count($dados_form['softwares_ti']) > 0)
		{
			$tipoNecessidade = 2;

			foreach($dados_form['softwares_ti'] as $soft)
			{
				$isql .= "(".intval($soft).", ".$tipoNecessidade.", ".$idFuncionario.", ".$idlocalTrabalho.")".$sep;
			}

			if ($dados_form['outrosSoftwares'] != '')
			{
				$tipoNecessidade = 5;
				$texto = AntiInjection::clean($dados_form['outrosSoftwares']);
				$isql .= "(0, ".$tipoNecessidade.", ".$idFuncionario.", ".$idlocalTrabalho.")".$sep;
			}

			if ($dados_form['protheusModulos'] != '')
			{
				$tipoNecessidade = 3;
				$texto = AntiInjection::clean($dados_form['protheusModulos']);
				$isql .= "(0, ".$tipoNecessidade.", ".$idFuncionario.", ".$idlocalTrabalho.")".$sep;
			}

			if ($dados_form['dvmsysModulos'] != '')
			{
				$tipoNecessidade = 4;
				$texto = AntiInjection::clean($dados_form['dvmsysModulos']);
				$isql .= "(0, ".$tipoNecessidade.", ".$idFuncionario.", ".$idlocalTrabalho.")".$sep;
			}

			$isql = substr_replace(trim($isql), "", -1);

			//Múltiplos inserts
			$db->insert($isql, 'MYSQL');

			if ($db->erro != '')
			{
				$erro = $db->erro;
			}
		}
	}
	else
	{
		$erro = 'Por favor, selecione uma opção de Equipamentos em Condições contratuais!'. $idFuncionario . $erroBanco;
	}

	return $erro;
}

//Esta função irá mudar
function gravarExigenciasCliente($dados_form)
{
	if ($dados_form['alteracaoExigencias'] == 0)
	{
		return '';
	}

	$db = new banco_dados();
	
	$erro = '';

	$numeroContrato = intval($dados_form['contratoColaboradorNumero']).$dados_form['contratoColaboradorAno'];

	$atribuido = false;
	
	if (intval($dados_form['tipo_tributacao']) > 0)
	{
		if (intval($dados_form['contratoColaboradorNumero']) == 0)
		{
			return "Por favor, verifique o número do contrato!";
		}
		
		//Buscando o número do contrato com outros colaboradores para ver se não estão cadastrando errado
		$sql = "SELECT
					*
				FROM
					".DATABASE.".cliente_exigencias
				WHERE
					cliente_exigencias.reg_del = 0
					AND cliente_exigencias.id_funcionario = ".$dados_form['id_funcionario'];
		
		$db->select($sql, 'MYSQL', true);
		
		$reg = $db->array_select[0];
		
		//Buscando o número do contrato com outros colaboradores para ver se não estão cadastrando errado
		$sql = "SELECT
					numero_contrato
				FROM
					".DATABASE.".pj_contratos
				WHERE
					pj_contratos.reg_del = 0
					AND pj_contratos.numero_contrato = ".$numeroContrato." 
					AND pj_contratos.id_funcionario <> ".$dados_form['id_funcionario'];
		
		$db->select($sql, 'MYSQL',true);
		
		$atribuido = $db->numero_registros > 0 ? true : false;
	}
		
	if ($atribuido)
	{
		$erro = "Este número de contrato já está atribuído a outro colaborador. Por favor, utilizar outra numeração! Dados do Contrato não foram salvos";	
	}
	else
	{
		//Para gravar o número do contrato do colaborador, é obrigatório adicionar o período
		if (trim($dados_form['contratoDe']) != '' && trim($dados_form['contratoAte']) != '')
		{
			$contratoDe = php_mysql($dados_form['contratoDe']);
			$contratoAte = php_mysql($dados_form['contratoAte']);

			$tipoContrato = 0;
			$valorContrato = 0.0;

			if (intval($dados_form['salario_mensal']) > 0)
			{
				$tipoContrato = 4;
				$valorContrato = str_replace(",",".",str_replace(".","",$dados_form['salario_mensal']));
			}
			else if (intval($dados_form['salario_hora']) > 0)
			{
				$tipoContrato = 3;
				$valorContrato = str_replace(",",".",str_replace(".","",$dados_form['salario_hora']));
			}

			$complemento = intval($dados_form['contratoColaboradorNumero']) > 0 ? "Dados do Contrato não foram salvos" : "";
			
			if (!empty($dados_form['refeicao']) || !empty($dados_form['transporte']))
			{
				if (empty($dados_form['local_trabalho']))
				{
					$erro = 'Por favor, verificar o campo Cliente em Condições Contratuais! '.$complemento;
				}
				else
				{
					if ($dados_form['alteracaoExigencias']) {
						//Gravando o contrato do colaborador PJ
						if (intval($dados_form['local_trabalho']) != intval($reg['id_local_trabalho']))
						{
							//Gerando um registro para o colaborador
							$usql = "UPDATE
										".DATABASE.".cliente_exigencias
									SET
										reg_del = 1,
										reg_who = '" . $_SESSION['id_funcionario'] . "',
										data_del = '" . date('Y-m-d') . "'
									WHERE
										id_funcionario = '" . $dados_form['id_funcionario'] . "'
										AND reg_del = 0 ";
	
							$db->update($usql, 'MYSQL');
							
							$isql = "INSERT INTO
										".DATABASE.".cliente_exigencias
										(
											id_funcionario, id_adicional_refeicao, id_adicional_transporte, id_adicional_hotel, id_local_trabalho,
											data_inicio, data_fim, numero_contrato_cliente, numero_os, centrocusto
										)
									VALUES(
										 " . $dados_form["id_funcionario"] . ",
										 " . $dados_form['refeicao'] . ",
										 " . $dados_form['transporte'] . ",
										 " . $dados_form['hotel'] . ",
										 " . $dados_form["local_trabalho"] . ",
										 '" . $contratoDe . "',
										 '" . $contratoAte . "',
										 " . intval($dados_form["numeroContrato"]) . ",
										 " . intval($dados_form['os']) . ",
									 	 " . intval($dados_form['centrocusto']) . ")";
	
							$db->insert($isql, 'MYSQL');
						}
						else
						{
							$usql = "UPDATE
										".DATABASE.".cliente_exigencias
									SET
										id_funcionario = " . $dados_form["id_funcionario"] . ",
										id_adicional_refeicao = " . $dados_form['refeicao'] . ",
										id_adicional_transporte = " . $dados_form['transporte'] . ",
										id_adicional_hotel = " . $dados_form['hotel'] . ",
										id_local_trabalho = " . $dados_form["local_trabalho"] . ",
										data_inicio = '" . $contratoDe . "',
										data_fim = '" . $contratoAte . "',
										numero_contrato_cliente = " . intval($dados_form["numeroContrato"]) . ",
										numero_os = " . intval($dados_form['os']) . ",
										centrocusto = " . intval($dados_form['centrocusto']) . "
									WHERE
										id_funcionario = '" . intval($dados_form['id_funcionario']) . "'
										AND reg_del = 0 ";
	
							$db->update($usql, 'MYSQL');
							
							if ($db->erro != '') 
							{
								$erro = $db->erro;
							}
							
						}

						$usql = "UPDATE
									".DATABASE.".pj_contratos
								SET
									reg_del = 1,
									reg_who = '" . $_SESSION['id_funcionario'] . "',
									data_del = '" . date('Y-m-d') . "'
								WHERE
									id_funcionario = '" . $dados_form['id_funcionario'] . "'
									AND reg_del = 0 ";

						$db->update($usql, 'MYSQL');

						if ($db->erro != '') 
						{
							$erro = $db->erro;
						}

						$isql = "INSERT INTO
									".DATABASE.".pj_contratos
									(id_tipo_contratacao,id_funcionario,nome_subcontratado,id_empresa,id_clausula_reajuste,id_clausula_refeicao,id_clausula_transporte,
									 id_clausula_hospedagem,id_clausula_refeicao_mob,id_clausula_transporte_mob,id_clausula_hospedagem_mob,id_clausula_tipo_contrato,
									 valor_contrato,id_disciplina,id_local_trabalho,data_inicio,data_fim,vigencia,numero_contrato,
									 id_empresa, numero_contrato_cliente, numero_os)
								VALUES(0,
									 " . $dados_form["id_funcionario"] . ",
									 '',
									 0,
									 0,
									 0,
									 0,
									 0,
									 " . $dados_form['refeicao'] . ",
									 " . $dados_form['transporte'] . ",
									 0,
									 " . $tipoContrato . ",
									 " . $valorContrato . ",
									 0,
									 " . $dados_form["local_trabalho"] . ",
									 '" . $contratoDe . "',
									 '" . $contratoAte . "',
									 0,
									 " . $numeroContrato . ",
									 " . $dados_form['local_trabalho'] . ",
									 " . intval($dados_form['numeroContrato']) . ",
									 " . intval($dados_form['os']) . ")";

						$db->insert($isql, 'MYSQL');

						if ($db->erro != '') 
						{
							$erro = $db->erro;
						}
					}
				}
			}
			else
			{
				$erro = 'Por favor, verificar os campos Refeição e Transporte em Condições Contratuais! '.$complemento;
			}
		}
		else
		{
			$erro = 'Por favor, verificar o período do contrato em Condições Contratuais! '.$complemento;
		}
	}
	
	return $erro;
}

function gravarAjudaCusto($dados_form)
{
	$db = new banco_dados();
	
	$erro = '';
		
	if (count($dados_form['ajudaCusto']) > 0 && intval($dados_form['ajudaCusto'][0]) != '')
	{
		$idFuncionario = $dados_form['id_funcionario'];
		$dataInclusao = date('Y-m-d');
		$codlocal = $dados_form['local_trabalho'];
		$idOs = $dados_form['os'];

		if ($idFuncionario > 0)
		{
			$usql = "UPDATE ".DATABASE.".funcionario_x_ajudacusto SET ";
			$usql .= "reg_del = 1, ";
			$usql .= "data_del = '".$dataInclusao."', ";
			$usql .= "reg_who = ".$_SESSION['id_funcionario']." ";
			$usql .= "WHERE id_funcionario = ".$idFuncionario." ";
			$usql .= "AND reg_del = 0 ";
			
			$db->update($usql, 'MYSQL');
			
			$erro = $db->erro;
		}
			
		if ($erro == '')
		{
			$sep = ', ';
			
			$isql = "INSERT INTO ".DATABASE.".funcionario_x_ajudacusto VALUES ";
						
			foreach($dados_form['ajudaCusto'] as $k => $v)
			{
				if (!empty($v))
				{
					$idTipoAdicional 		= $v;
					$formaReembolso 		= $dados_form['tipoReembolso'][$k];
					$respPGTO 				= $dados_form['responsavelPGTO'][$k];
					$valorAjudaCusto 		= number_format($dados_form['valorAjudaCusto'][$k], 2, '.', '');
					$descricaoAjudaCusto	= $dados_form['descricaoAjudaCusto'][$k];
					
					$isql .= "(NULL, ".$idFuncionario.", ".$idTipoAdicional.", ".$formaReembolso.", ".$respPGTO.", ".$valorAjudaCusto.", '".$descricaoAjudaCusto."', '".$codlocal."', '".$idOs."', 0, NULL, NULL, '".$dataInclusao."')".$sep;
				}
			}
			
			$isql = substr_replace(trim($isql), "", -1);
	
			//Múltiplos inserts
			$db->insert($isql, 'MYSQL');
	
			if ($db->erro != '')
			{
				$erro = $db->erro;
			}
		}
	}
	
	return $erro;
}

function getAjudaCustoAdicional($id_funcionario = null)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados();
	
	$sql = "SELECT * FROM ".DATABASE.".tipo_adicional ";
	$sql .= "WHERE reg_del = 0 ";
	
	$db->select($sql, 'MYSQL',true);
	
	if ($db->erro != '')
	{
		$resposta->addAlert($db->erro);
	}
	
	//Criando um array associativo para todos os registros retornados
	//$tiposAdicional = array();
	
	foreach ($db->array_select as $regs)
	{
		$tiposAdicional[$regs["id_tipo_adicional"]] = $regs["tipo_adicional"];	
	}
	
	//array_pop($tiposAdicional);//Removendo o último registro que sempre é vazio
	
	$html = '';
	
	if (!is_null($id_funcionario))
	{
		//Quando forem encontratas trs na tabela, apagar todos menos o primeiro  
		$resposta->addScript("$('#tableAdicionais tr:not(:first)').remove();");
		
		$sql = "SELECT * FROM ".DATABASE.".funcionario_x_ajudacusto ";
		$sql .= "WHERE funcionario_x_ajudacusto.id_funcionario = ".$id_funcionario." ";
		$sql .= "AND funcionario_x_ajudacusto.reg_del = 0 ";
		
		$db->select($sql, 'MYSQL',true);
		
		if ($db->erro != '')
		{
			$resposta->addAlert($db->erro);
		}
		
		foreach($db->array_select as $regExiste)
		{
			$html = '';
			$html .= '<tr id="ajudaCusto_'.$regExiste['id_ajudacusto'].'"><td><select name="ajudaCusto[]" id="ajudaCusto" class="caixa">';
			$html .= '<option value="">Selecione...</option>';
			
			$checked = '';
			
			foreach($tiposAdicional as $reg)
			{
				$checked = $reg['id_tipo_adicional'] == $regExiste['id_tipo_adicional'] ? 'selected="selected"' : '';
				$html .= '<option value="'.$reg['id_tipo_adicional'].'" '.$checked.'>'.$reg['tipo_adicional'].'</option>';
			}
			
			$html .= '</select></td>';
			
			$checked1 = $regExiste['id_forma_reembolso'] == 1 ? 'selected="selected"' : '';
			$checked2 = $regExiste['id_forma_reembolso'] == 2 ? 'selected="selected"' : '';
			
			$html .= '<td><select id="tipoReembolso" name="tipoReembolso[]" class="caixa">';
			$html .= '<option value="">Selecione...</option>';
			$html .= '<option value="1" '.$checked1.'>Contra Recibo de Despesa</option>';
			$html .= '<option value="2" '.$checked2.'>Contra NF de Prestação de Serviço</option>';
			$html .= '</select></td>';
			
			$html .= '<td><select id="responsavelPGTO" name="responsavelPGTO[]" class="caixa">';
			$html .= '<option value="">Selecione...</option>';
			
			$checked1 = $regExiste['id_resp_pgto'] == 1 ? 'selected="selected"' : '';
			$checked2 = $regExiste['id_resp_pgto'] == 2 ? 'selected="selected"' : '';
			
			$html .= '<option value="1" '.$checked1.'>Cliente</option>';
			$html .= '<option value="2" '.$checked2.'>Empresa</option>';
			$html .= '</select></td>';
			
			$html .= '<td><input size="7" type="text" value="'.$regExiste['valor'].'" id="valorAjudaCusto" name="valorAjudaCusto[]" class="caixa" /></td>';
			$html .= '<td><input type="text" value="'.$regExiste['descricao'].'" id="descricaoAjudaCusto" name="descricaoAjudaCusto[]" class="caixa" style="width:100px;" /></td>';
			$html .= '<td><img src="'.DIR_IMAGENS.'delete.png" onclick="xajax_removerAjudaCustoAdicional('.$regExiste['id_ajudacusto'].');" style="cursor:pointer;"></td></tr>';
			
			$resposta->addScript("$('#tableAdicionais tr:last').after('".$html."');");
		}
	}
	
	$html = '<tr><td><select name="ajudaCusto[]" id="ajudaCusto" class="caixa">';
	$html .= '<option value="">Selecione...</option>';
	
	foreach($tiposAdicional as $chave=>$texto)
	{
		$html .= '<option value="'.$chave.'">'.$texto.'</option>';
	}
	
	$html .= '</select></td>';
	
	$html .= '<td><select id="tipoReembolso" name="tipoReembolso[]" class="caixa">';
	$html .= '<option value="">Selecione...</option>';
	$html .= '<option value="1">Contra Recibo de Despesa</option>';
	$html .= '<option value="2">Contra NF de Prestação de Serviço</option>';
	$html .= '</select></td>';
	
	$html .= '<td><select id="responsavelPGTO" name="responsavelPGTO[]" class="caixa">';
	$html .= '<option value="">Selecione...</option>';
	$html .= '<option value="1">Cliente</option>';
	$html .= '<option value="2">Empresa</option>';
	$html .= '</select></td>';
	
	$html .= '<td><input size="7" type="text" value="" id="valorAjudaCusto" name="valorAjudaCusto[]" class="caixa" /></td>';
	$html .= '<td><input type="text" value="" id="descricaoAjudaCusto" name="descricaoAjudaCusto[]" class="caixa" style="width:100px;" /></td>';
	$html .= '<td><img src="'.DIR_IMAGENS.'add.png" onclick="xajax_getAjudaCustoAdicional();" style="cursor:pointer;"></tr>';
	
	$resposta->addScript("$('#tableAdicionais tr:last').after('".$html."');");
	
	return $resposta;
}

function removerAjudaCustoAdicional($idAjudaCusto)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados();
	
	$usql = "UPDATE ".DATABASE.".funcionario_x_ajudacusto SET ";
	$usql .= "reg_del = 1, ";
	$usql .= "data_del = '".date('Y-m-d')."', ";
	$usql .= "reg_who = ".$_SESSION['id_funcionario']." ";
	$usql .= "WHERE id_ajudacusto = ".$idAjudaCusto." ";
	$usql .= "AND reg_del = 0 ";
	
	$db->update($usql, 'MYSQL');
	
	if ($db->erro != '')
	{
		$resposta->addAlert('Houve uma falha ao tentar remover o registro. '.$db->erro);
	}
	else
	{
		$resposta->addScript("$('#tableAdicionais #ajudaCusto_".$idAjudaCusto."').remove();");
		$resposta->addAlert('Registro Excluído corretamente!');
	}
	
	return $resposta;
}

function preenche($valor)
{
	$resposta = new xajaxResponse();

	$txt = explode(" ",$valor);

	$resposta->addAssign("email","value",minusculas(tiraacentos($txt[0].'.'.$txt[count($txt)-1]))."@dominio.com.br");

	$resposta->addAssign("login","value",minusculas(tiraacentos($txt[0].'.'.$txt[count($txt)-1])));

	return $resposta;
}

function calcula_idade($data)
{
	$resposta = new xajaxResponse();

	$data = explode("/",$data);

	$resposta->addAssign("idade","value",floor(numero_meses($data[2]."-".$data[1]."-".$data[0],date('Y-m-d'))/12));

	return $resposta;
}

function data_deslig()
{
	$resposta = new xajaxResponse();

	$resposta->addAssign("data_desligamento","value",date('d/m/Y'));

	return $resposta;
}

function envia_microsiga($id)
{
	$resposta = new xajaxResponse();

	$erro = false;

	$db = new banco_dados;

	//SELECIONA OS BANCOS
	$sql = "SELECT * FROM ".DATABASE.".bancos ";
	$sql .= "WHERE reg_del = 0 ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}

	foreach($db->array_select as $reg)
	{
		$bancos[$reg["id_banco"]] = $reg["dv"];
		
		$bancos_nome[$reg["id_banco"]] = $reg["instituicao"];
	}

	$sql = "SELECT * FROM ".DATABASE.".rh_funcoes, ".DATABASE.".setores, ".DATABASE.".funcionarios ";
	$sql .= "LEFT JOIN ".DATABASE.".usuarios ON (funcionarios.id_usuario = usuarios.id_usuario AND usuarios.reg_del = 0) ";
	$sql .= "WHERE funcionarios.id_setor = setores.id_setor ";
	$sql .= "AND rh_funcoes.reg_del = 0 ";
	$sql .= "AND setores.reg_del = 0 ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	$sql .= "AND funcionarios.id_funcao = rh_funcoes.id_funcao ";
	$sql .= "AND funcionarios.id_funcionario = '" . $id . "' ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}

	$reg_editar = $db->array_select[0];

	//Seleciona os dados do salario
	$sql = "SELECT * FROM ".DATABASE.".salarios ";
	$sql .= "WHERE salarios.id_funcionario = '" . $id . "' ";
	$sql .= "AND salarios.reg_del = 0 ";
	$sql .= "ORDER BY salarios.data DESC, salarios.id_salario DESC LIMIT 1 ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}

	$reg_sal = $db->array_select[0];

	if($reg_editar["situacao"]=="DESLIGADO")
	{
		$status = 2;
	}
	else
	{
		$status = 1;
	}

	//TRATAMENTO DE ERRO - CAMPOS NAO PREENCHIDOS
	if($reg_sal[" tipo_contrato"]==="") //CONTRATO
	{
		$resposta->addAlert("Preencher o tipo de contrato.");

		$erro = true;
	}

	if(in_array($reg_sal[" tipo_contrato"],array("SC"))) //PJ
	{
		if($reg_editar["id_produto"]==="") //PRODUTO
		{
			$resposta->addAlert("Preencher o produto.");

			$erro = true;
		}

		if($reg_editar["id_cod_fornec"]==="") //FORNECEDOR
		{
			$resposta->addAlert("Preencher o fornecedor.");

			$erro = true;
		}
	}

	$tc = array('CLT','EST');

	if(in_array($reg_sal[" tipo_contrato"],$tc)) //CLT-EST
	{

		if($reg_editar["clt_matricula"]=="" || $reg_editar["clt_matricula"]=="000000") //MATRICULA
		{
			$resposta->addAlert("Preencher a matricula.");

			$erro = true;
		}

		if($reg_editar["fgts_data"]=="") //DATA OPCAO valor_fgts
		{
			$resposta->addAlert("Preencher a data de opcao valor_fgts.");

			$erro = true;
		}

		if($reg_editar["fgts_banco"]=="") //BANCO valor_fgts
		{
			$resposta->addAlert("Preencher o banco de valor_fgts.");

			$erro = true;
		}

		if($reg_editar["fgts_agencia"]=="") //agencia valor_fgts
		{
			$resposta->addAlert("Preencher a agencia de valor_fgts.");

			$erro = true;
		}

		if($reg_editar["id_funcao"]=="") //funcao
		{
			$resposta->addAlert("Preencher a funcao.");

			$erro = true;
		}

		if($reg_editar["id_categoria_funcional"]=="") //categoria funcional
		{
			$resposta->addAlert("Preencher a categoria funcional.");

			$erro = true;
		}

		if($reg_editar["id_tipo_admissao"]=="") //tipo admissao
		{
			$resposta->addAlert("Preencher o tipo de admissao.");

			$erro = true;
		}

		if($reg_editar["id_vinculo_empregaticio"]=="") //vinculo empregaticio
		{
			$resposta->addAlert("Preencher o vinculo empregaticio.");

			$erro = true;
		}

		if($reg_editar["ctps_num"]=="") //numero carteira trabalho
		{
			$resposta->addAlert("Preencher o numero da carteira de trabalho.");

			$erro = true;
		}

		if($reg_editar["ctps_serie"]=="") //serie carteira trabalho
		{
			$resposta->addAlert("Preencher a serie da carteira de trabalho.");

			$erro = true;
		}

		if($reg_editar["id_turno_trabalho"]=="") //turno de trabalho
		{
			$resposta->addAlert("Preencher o turno de trabalho.");

			$erro = true;
		}

	}

	if($reg_editar["id_setor"]==="") //SETOR
	{
		$resposta->addAlert("Preencher o setor.");

		$erro = true;
	}

	if($reg_editar["funcionario_estado"]==="") //estado
	{
		$resposta->addAlert("Preencher o estado.");

		$erro = true;
	}

	if($reg_editar["id_nacionalidade"]==="") //nacionalidade
	{
		$resposta->addAlert("Preencher a nacionalidade.");

		$erro = true;
	}

	if($reg_editar["sexo"]==="") //sexo
	{
		$resposta->addAlert("Preencher o sexo.");

		$erro = true;
	}

	if($reg_editar["id_estado_civil"]==="") //estado civil
	{
		$resposta->addAlert("Preencher o estado civil.");

		$erro = true;
	}

	if($reg_editar["data_nascimento"]==="") //data nascimento
	{
		$resposta->addAlert("Preencher a data de nascimento.");

		$erro = true;
	}

	if($reg_editar["id_centro_custo"]==="") //centro de custo
	{
		$resposta->addAlert("Preencher o centro de custo.");

		$erro = true;
	}

	if($reg_editar["data_inicio"]==="") //data inicio
	{
		$resposta->addAlert("Preencher a data de inicio.");

		$erro = true;
	}

	if($reg_editar["id_escolaridade"]==="") //grau de instrucao
	{
		$resposta->addAlert("Preencher o grau de instrucao.");

		$erro = true;
	}

	if($reg_editar["id_tipo_pagamento"]==="") //tipo de pagamento
	{
		$resposta->addAlert("Preencher o tipo de pagamento.");

		$erro = true;
	}

	//FIM TRATAMENTO DE ERRO	
	if($erro)
	{
		$resposta->addAlert("Nao e possivel exportar para o Microsiga.\n Favor revisar os campos obrigatorios.");
	}
	else
	{
		if($dados_form["data_admissao"]=="")
		{
			$data_inicio = $dados_form["data_inicio"];
		}

		if($data_inicio=="")
		{
			$data_inicio = $dados_form["data_inicio"];
		}

		$tp_real = "1";
		$cust_fix = 0;
		$cust_men = 0;
		$tipo_contrato = "";

		$matricula = "";
		$ae8_valor = 0;
		$ae8_fornce = "";
		$ae8_func = 0;

		$ra_opcao = "";
		$ra_bcdpfgt = "";
		$ra_hrsmes = "";
		$ra_hrsseman = "";
		$ra_codfunc = "";
		$ra_catfunc = "";
		$ra_tipoadm = "";
		$ra_viemrai = "";
		$ra_numcp = "";
		$ra_sercp = "";
		$fornecedor = "";
		$ra_tnotrab = "";
		$ra_site = "101";

		if(in_array($reg_sal[" tipo_contrato"],array('EST','CLT')))
		{
			if($reg_sal[" tipo_contrato"]=="CLT")
			{
				$tipo_contrato = "1";
			}
			else
			{
				$tipo_contrato = "2";
			}

			//FOLHA
			$tp_real = "3";

			$matricula = sprintf("%06d",$reg_editar["clt_matricula"]);

			$ra_opcao = str_replace("-","",php_mysql($reg_editar["fgts_data"]));
			$ra_bcdpfgt = $banco[$reg_editar["fgts_banco"]].$reg_editar["fgts_agencia"];
			$ra_hrsmes = 200;
			$ra_hrsseman = 40;
			$ra_codfunc = sprintf("%05d",$reg_editar["id_funcao"]);
			$ra_catfunc = $reg_editar["id_categoria_funcional"];
			$ra_tipoadm = $reg_editar["id_tipo_admissao"];
			$ra_viemrai = $reg_editar["id_vinculo_empregaticio"];
			$ra_numcp = $reg_editar["ctps_num"];
			$ra_sercp = $reg_editar["ctps_serie"];
			$ra_tnotrab = $reg_editar["id_turno_trabalho"];
			$ra_site = $reg_editar["item_contabil"];
		}
		else
		{
			if($reg_sal[" tipo_contrato"]=="SC")
			{
				$tp_real = "1"; //FIFO
				$tipo_contrato = "3"; //PJ
				$cust_fix = $reg_sal["salario_hora"];
				$cust_men = 0;
				$ra_opcao = "";
				$ra_bcdpfgt = "";
				$ra_hrsmes = "";
				$ra_hrsseman = "";
				$ra_codfunc = "";
				$ra_catfunc = "";
				$ra_tipoadm = "";
				$ra_viemrai = "";
				$ra_numcp = "";
				$ra_sercp = "";
				$ra_tnotrab = "";
				$fornecedor = sprintf("%06d",$reg_editar["id_cod_fornec"]);

			}
			else
			{
				if($reg_sal[" tipo_contrato"]=="SC+MENS")
				{
					$tp_real = "5"; //MENSAL

					$tipo_contrato = "5";

					$cust_fix = $reg_sal["salario_hora"];

					$cust_men = $reg_sal["salario_mensalista"];

					$fornecedor = sprintf("%06d",$reg_editar["id_cod_fornec"]);

				}
				else
				{
					if($reg_sal[" tipo_contrato"]=="SC+CLT+MENS")
					{
						$tp_real = "5";//fIFO

						$tipo_contrato = "6";

						$cust_men = 0;

						$cust_fix = $reg_sal["salario_hora"];

						$cust_men = $reg_sal["salario_mensalista"];

						$fornecedor = sprintf("%06d",$reg_editar["id_cod_fornec"]);

					}
					else
					{
						if($reg_sal[" tipo_contrato"]=="SC+CLT")
						{
							$tp_real = "1";//fiFO

							$tipo_contrato = "4";

							$cust_fix = $reg_sal["salario_hora"];

							$cust_men = $reg_sal["salario_mensalista"];

							$fornecedor = sprintf("%06d",$reg_editar["id_cod_fornec"]);
						}
						else
						{
							$tipo_contrato = "7"; //Socio

							$cust_fix = 0;

							$cust_men = $reg_sal["salario_mensalista"];
						}

					}
				}
			}
		}

		$sql = "SELECT * FROM ".DATABASE.".rh_cargos, ".DATABASE.".rh_funcoes ";
		$sql .= "WHERE rh_cargos.id_cargo_grupo =  rh_funcoes.id_cargo_grupo ";
		$sql .= "AND rh_cargos.reg_del = 0 ";
		$sql .= "AND rh_funcoes.reg_del = 0 ";
		$sql .= "AND rh_funcoes.id_funcao = '".$reg_editar["id_funcao"]."' ";

		$db->select($sql,'MYSQL',true);

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}

		$reg1 = $db->array_select[0];

		$ae8_funcao = $reg1["id_cargo_grupo"];

		$texto = explode(" ",$reg1["grupo"]);

		switch ($texto[0])
		{
			case 'ENGENHEIRO':
				$ae8_valor = '50.00';
				break;

			case 'SUPERVISOR':
				$ae8_valor = '40.00';
				break;

			case 'COORDENADOR':
				$ae8_valor = '30.00';
				break;

			case 'PROJETISTA':
				$ae8_valor = '20.00';
				break;

			case 'DESENHISTA':
				$ae8_valor = '10.00';
				break;

			default: $ae8_valor = 0;

		}

		if($reg_editar["clt_admissao"]=="")
		{
			$data_inicio = $reg_editar["data_inicio"];
		}

		if($data_inicio=="")
		{
			$data_inicio = $reg_editar["clt_admissao"];
		}

		//Insere o recurso no banco microsiga
		/*
		$sql = "SELECT TOP 1 R_E_C_N_O_ FROM AE8010 WITH(NOLOCK) ";
		$sql .= "ORDER BY R_E_C_N_O_ DESC ";

		$db->select($sql,'MSSQL', true);

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}

		$regs = $db->array_select[0];

		$recno_ae8 = $regs["R_E_C_N_O_"] + 1;

		$isql = "INSERT INTO AE8010 ";
		$isql .= "(AE8_RECURS, AE8_DESCRI, AE8_TIPO, AE8_UMAX, AE8_PRODUT, AE8_CALEND, ";
		$isql .= "AE8_TPREAL, AE8_VALOR, AE8_EMAIL, AE8_CUSFIX, AE8_CODFUN, AE8_PRDREA, ";
		$isql .= "AE8_ATIVO, AE8_EQUIP, AE8_CUSMEN, AE8_FORNEC, AE8_XFUNC, ";
		$isql .= "AE8_MCONTR, AE8_FUNCAO, AE8_RASTRO, AE8_ID, AE8_ID_CAR, R_E_C_N_O_, R_E_C_D_E_L_) ";
		$isql .= "VALUES ( ";
		$isql .= "'FUN_".sprintf("%011d",$id)."', ";
		$isql .= "'".maiusculas(tiraacentos($reg_editar["funcionario"]))."', ";
		$isql .= "'2', ";
		$isql .= "'100', ";
		$isql .= "'".$reg_editar["id_produto"]."', ";
		$isql .= "'001', ";
		$isql .= "'".$tp_real."', ";
		$isql .= "'".$ae8_valor."', ";
		$isql .= "'".minusculas($reg_editar["email"])."', ";
		$isql .= "'".$cust_fix."', ";
		$isql .= "'".$matricula."', ";
		$isql .= "'".$reg_editar["id_produto"]."', ";
		$isql .= "'".$status."', ";
		$isql .= "'".sprintf("%010d",$reg_editar["id_setor"])."', ";
		$isql .= "'".$cust_men."', ";
		$isql .= "'".$fornecedor."', ";
		$isql .= "'".maiusculas(tiraacentos($reg1["descricao"]))."', ";
		$isql .= "'".$tipo_contrato."', ";
		$isql .= "'".sprintf("%09d",$ae8_funcao)."', ";
		$isql .= "'0', ";
		$isql .= "'".$id."', ";
		$isql .= "'0', ";																		//FLAG													
		$isql .= "'".$recno_ae8."', ";
		$isql .= "'0') ";

		$db->insert($isql,'MSSQL');

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
		*/

		//SE RECURSO FOR CLT
		if($matricula!='')
		{
			//Insere o FUNCIONARIO no banco microsiga
			/*
			$sql = "SELECT TOP 1 R_E_C_N_O_ FROM SRA010 WITH(NOLOCK) ";
			$sql .= "ORDER BY R_E_C_N_O_ DESC ";

			$db->select($sql,'MSSQL', true);

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}

			$regs1 = $db->array_select[0];

			$recno_sra = $regs1["R_E_C_N_O_"] + 1;

			$isql = "INSERT INTO SRA010 ";
			$isql .= "(RA_FILIAL, RA_MAT, RA_NOME, RA_NATURAL, RA_NACIONA, RA_SEXO, ";
			$isql .= "RA_ESTCIVI, RA_NASC, RA_CC, RA_ADMISSA, RA_OPCAO, RA_HRSMES, ";
			$isql .= "RA_HRSEMAN, RA_CATFUNC, RA_TIPOPGT, RA_TIPOADM, ";
			$isql .= "RA_VIEMRAI, RA_GRINRAI, RA_NUMCP, RA_SERCP, RA_ADTPOSE, RA_TNOTRAB, ";
			$isql .= "RA_ID, RA_PROCES, R_E_C_N_O_, R_E_C_D_E_L_) ";
			$isql .= "VALUES ( ";
			$isql .= "'01', ";
			$isql .= "'".$matricula."', ";
			$isql .= "'".maiusculas(tiraacentos($reg_editar["funcionario"]))."', ";
			$isql .= "'".$reg_editar["funcionario_estado"]."', ";
			$isql .= "'".$reg_editar["id_nacionalidade"]."', ";
			$isql .= "'".$reg_editar["sexo"]."', ";
			$isql .= "'".$reg_editar["id_estado_civil"]."', ";
			$isql .= "'".str_replace("-","",$reg_editar["data_nascimento"])."', ";
			$isql .= "'".$reg_editar["id_centro_custo"]."', ";
			$isql .= "'".str_replace("-","",$data_inicio)."', ";
			$isql .= "'".$ra_opcao."', ";
			$isql .= "'".$ra_hrsmes."', ";														//HORAS MES
			$isql .= "'".$ra_hrsseman."', ";
			$isql .= "'".$ra_catfunc."', ";
			$isql .= "'".$reg_editar["id_tipo_pagamento"]."', ";
			$isql .= "'".$ra_tipoadm."', ";
			$isql .= "'".$ra_viemrai."', ";
			$isql .= "'".$reg_editar["id_escolaridade"]."', ";
			$isql .= "'".$ra_numcp."', ";
			$isql .= "'".$ra_sercp."', ";
			$isql .= "'*****T', ";
			$isql .= "'".$ra_tnotrab."', ";
			//$isql .= "'".$ra_site."', ";
			$isql .= "'".$id."', ";
			$isql .= "'00001', ";																		//FLAG													
			$isql .= "'".$recno_sra."', ";
			$isql .= "'0') ";

			$db->insert($isql,'MSSQL');

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}
			
			//CARREGA A TABELA SX5 DO PROTHEUS
			$sql = "SELECT DISTINCT X5_TABELA, X5_CHAVE, X5_DESCRI FROM SX5010 WITH(NOLOCK) ";
			$sql .= "WHERE D_E_L_E_T_ = '' ";
			
			$db->select($sql,'MSSQL', true);
			
			if($db->erro!='')
			{
				die($db->erro);
			}
			
			foreach($db->array_select as $regs)
			{
				$tabela_x5[trim($regs['X5_TABELA'])][trim($regs["X5_CHAVE"])] = trim(maiusculas($regs["X5_DESCRI"]));
			}
			*/
			
			//SELECIONA OS DEPENDENTES
			$DEPENDENTES = "";
			
			$sql = "SELECT * FROM ".DATABASE.".dependentes_funcionarios ";
			$sql .= "WHERE dependentes_funcionarios.id_funcionario = '".$id."' ";
			$sql .= "AND reg_del = 0 ";
			
			$db->select($sql,'MYSQL', true);
			
			if($db->erro!='')
			{
				die($db->erro);
			}
			
			foreach($db->array_select as $regs)
			{
				$DEPENDENTES .="<tr align=\"center\" style=\"font-family: Arial, Helvetica, sans-serif; font-size: 10px;\">
									<td>". $regs["nome_dependente"] ."</td>
									<td>". mysql_php($regs["data_nascimento"]) ."</td>
									<td>". $regs["parentesco"] ."</td>
								</tr>";			
			}
		
			$ADMISSAO = "<table width=\"100%\" border=\"0\">";
			$ADMISSAO .="<tr>
						<td width=\"7%\"> </td>
						<td width=\"19%\"><span style=\"color: #006699;	font-weight: bold; text-decoration: underline; font-family: Verdana, Arial;\">".NOME_EMPRESA."</span></td>
						<td width=\"67%\"> </td>
						<td width=\"7%\"> </td>
					  </tr>";
			$ADMISSAO .= "<tr>
						<td> </td>
						<td> </td>
						<td> </td>
						<td> </td>
					  </tr>";
			$ADMISSAO .="<tr>
						<td> </td>
						<td colspan=\"2\">".CIDADE.", ". date('d')." de ". meses(date('m')-1,1)." de ".date('Y') ."</td>
						<td> </td>
					  </tr>";

			$ADMISSAO .="<tr>
						<td> </td>
						<td> </td>
						<td> </td>
						<td> </td>
					  </tr>";
			$ADMISSAO .="<tr>
						<td> </td>
						<td>A/C </td>
						<td> </td>
						<td> </td>
					  </tr>";
			$ADMISSAO .="<tr>
						<td> </td>
						<td> </td>
						<td> </td>
						<td> </td>
					  </tr>";
			$ADMISSAO .="<tr>
						<td> </td>
						<td colspan=\"2\" align=\"center\" style=\"color: #FF0000; font-weight: bold;\">ADMISSÃO DE FUNCIONÁRIO</td>
						<td> </td>
					  </tr>";
			$ADMISSAO .="<tr>
						<td> </td>
						<td> </td>
						<td> </td>
						<td> </td>
					  </tr>";
			$ADMISSAO .="<tr>
						<td> </td>
						<td colspan=\"2\">Gentileza providenciar o processo de admissão do funcionário abaixo:</td>
						<td> </td>
					  </tr>";
			$ADMISSAO .="<tr>
						<td> </td>
						<td> </td>
						<td> </td>
						<td> </td>
					  </tr>";
			$ADMISSAO .="<tr>
						<td> </td>
						<td colspan=\"2\">";
			$ADMISSAO .="		<table width=\"100%\" border=\"1\">";
			$ADMISSAO .="		<tr>
								<td style=\"font-family: Arial, Helvetica, sans-serif; font-weight: bold; font-size: 12px;\">NOME:</td>
								<td colspan=\"8\" style=\"font-family: Arial, Helvetica, sans-serif; font-size: 10px;\">". $reg_editar["funcionario"] ."</td>
							</tr>";
			$ADMISSAO .="	  	<tr>
								<td width=\"10%\" rowspan=\"2\" style=\"font-family: Arial, Helvetica, sans-serif; font-weight: bold; font-size: 12px;\">FILIAÇÃO</td>
								<td width=\"6%\" style=\"font-family: Arial, Helvetica, sans-serif; font-weight: bold; font-size: 12px;\">PAI: </td>
								<td width=\"33%\" style=\"font-family: Arial, Helvetica, sans-serif; font-size: 10px;\">". $reg_editar["filiacao_pai"] ."</td>
								<td width=\"3%\"> </td>
								<td width=\"13%\" style=\"font-family: Arial, Helvetica, sans-serif; font-weight: bold; font-size: 12px;\">NACIONALIDADE:</td>
								<td width=\"35%\" colspan=\"4\" style=\"font-family: Arial, Helvetica, sans-serif; font-size: 10px;\">". $reg_editar["nacionalidade_pai"] ."</td>
							</tr>";
			$ADMISSAO .="      <tr>
								<td style=\"font-family: Arial, Helvetica, sans-serif; font-weight: bold; font-size: 12px;\">MÃE:</td>
								<td style=\"font-family: Arial, Helvetica, sans-serif; font-size: 10px;\">". $reg_editar["filiacao_mae"] ."</td>
								<td> </td>
								<td style=\"font-family: Arial, Helvetica, sans-serif; font-weight: bold; font-size: 12px;\">NACIONALIDADE:</td>
								<td colspan=\"4\" style=\"font-family: Arial, Helvetica, sans-serif; font-size: 10px;\">". $reg_editar["nacionalidade_mae"] ."</td>
							</tr>";
			$ADMISSAO .="      <tr>
								<td colspan=\"9\">";
			$ADMISSAO .="				<table width=\"100%\" border=\"1\">";
			$ADMISSAO .="		          <tr align=\"center\" style=\"font-family: Arial, Helvetica, sans-serif; font-weight: bold; font-size: 12px;\">
										<td width=\"16%\">CARTEIRA TRABALHO</td>
										<td width=\"7%\">SÉRIE</td>
										<td width=\"15%\">CARTEIRA RESERVISTA</td>
										<td width=\"13%\">CATEGORIA</td>
										<td width=\"11%\">TÍTULO ELEITOR</td>
										<td width=\"16%\">CÉDULA DE IDENTIDADE</td>
										<td width=\"12%\">ORGÃO EMISSOR</td>
										<td width=\"10%\">CPF</td>
									</tr>";
			$ADMISSAO .="		        <tr align=\"center\" style=\"font-family: Arial, Helvetica, sans-serif; font-size: 10px;\">
										<td>". $reg_editar["ctps_num"] ."</td>
										<td>". $reg_editar["ctps_serie"] ."</td>
										<td>". $reg_editar["reservista_num"] ."</td>
										<td>". $reg_editar["reservista_categoria"] ."</td>
										<td>". $reg_editar["titulo_eleitor"] ."</td>
										<td>". $reg_editar["identidade_num"] ."</td>
										<td>". $reg_editar["identidade_emissor"] ."</td>
										<td>". $reg_editar["cpf"] ."</td>
									</tr>";
			$ADMISSAO .="            </table>";
			$ADMISSAO .="            <table width=\"100%\" border=\"1\">";
			$ADMISSAO .="	            <tr align=\"center\" style=\"font-family: Arial, Helvetica, sans-serif; font-weight: bold; font-size: 12px;\">
										<td width=\"16%\">DATA DE NASCIMENTO</td>
										<td width=\"7%\">IDADE</td>
										<td width=\"15%\">NACIONALIDADE</td>
										<td width=\"13%\">NATURALIDADE</td>
										<td width=\"11%\">ESTADO</td>
										<td width=\"16%\">ESTADO CIVIL</td>
										<td width=\"12%\">GRAU DE INSTRUÇÃO</td>
									</tr>";
			$ADMISSAO .="              <tr align=\"center\" style=\"font-family: Arial, Helvetica, sans-serif; font-size: 10px;\">
										<td>". $reg_editar["data_nascimento"] ."</td>
										<td>". $reg_editar["idade"] ."</td>
										<td>". $tabela_x5[34][$reg_editar["id_nacionalidade"]]  ."</td>
										<td>". $reg_editar["naturalidade"] ."</td>
										<td>". $reg_editar["estado_nascimento"] ."</td>
										<td>". $tabela_x5[33][$reg_editar["id_estado_civil"]] ."</td>											
										<td>". $tabela_x5[26][$reg_editar["id_escolaridade"]] ."</td>
									</tr>";
			$ADMISSAO .="           </table>";
			$ADMISSAO .="       </td>";
			$ADMISSAO .="    </tr>";
			$ADMISSAO .="    <tr>
							<td style=\"font-family: Arial, Helvetica, sans-serif; font-weight: bold; font-size: 12px;\">CÔNJUGE:</td>
							<td colspan=\"8\" style=\"font-family: Arial, Helvetica, sans-serif; font-size: 10px;\">". $reg_editar["conjuge"] ."</td>
						  </tr>";
			$ADMISSAO .="    <tr>
							<td style=\"font-family: Arial, Helvetica, sans-serif; font-weight: bold; font-size: 12px;\">ENDEREÇÃO:</td>
							<td colspan=\"8\" style=\"font-family: Arial, Helvetica, sans-serif; font-size: 10px;\">". $reg_editar["funcionario_endereco"] ."</td>
						  </tr>";
			$ADMISSAO .="    <tr>
							<td style=\"font-family: Arial, Helvetica, sans-serif; font-weight: bold; font-size: 12px;\">BAIRRO:</td>
							<td colspan=\"8\" style=\"font-family: Arial, Helvetica, sans-serif; font-size: 10px;\">". $reg_editar["funcionario_bairro"] ."</td>
						  </tr>";
			$ADMISSAO .="    <tr>
							<td style=\"font-family: Arial, Helvetica, sans-serif; font-weight: bold; font-size: 12px;\">CIDADE:</td>
							<td colspan=\"8\" style=\"font-family: Arial, Helvetica, sans-serif; font-size: 10px;\">". $reg_editar["funcionario_cidade"] ."</td>
						  </tr>";
			$ADMISSAO .="    <tr>
							<td style=\"font-family: Arial, Helvetica, sans-serif; font-weight: bold; font-size: 12px;\">cep:</td>
							<td colspan=\"8\" style=\"font-family: Arial, Helvetica, sans-serif; font-size: 10px;\">". $reg_editar["funcionario_cep"] ."</td>
						  </tr>";
			$ADMISSAO .="    <tr>
							<td style=\"font-family: Arial, Helvetica, sans-serif; font-weight: bold; font-size: 12px;\">ESTADO:</td>
							<td colspan=\"8\" style=\"font-family: Arial, Helvetica, sans-serif; font-size: 10px;\">". $reg_editar["funcionario_estado"] ."</td>
						  </tr>";
			$ADMISSAO .="    <tr>
							<td colspan=\"9\">";
			$ADMISSAO .="			<table width=\"100%\" border=\"1\">";
			$ADMISSAO .="     			<tr align=\"center\" style=\"font-family: Arial, Helvetica, sans-serif; font-weight: bold; font-size: 12px;\">
										<td colspan=\"3\">BENEFICIÁRIOS</td>
									</tr>";
			$ADMISSAO .="	            <tr align=\"center\" style=\"font-family: Arial, Helvetica, sans-serif; font-weight: bold; font-size: 12px;\">
										<td width=\"16%\">NOME</td>
										<td width=\"7%\">DATA DE NASCIMENTO </td>
										<td width=\"15%\">PARENTESCO</td>
									</tr>";

			$ADMISSAO .= $DEPENDENTES;

			$ADMISSAO .="         </table>";
			$ADMISSAO .="		</td>";
			$ADMISSAO .="   </tr>";
			$ADMISSAO .="	 <tr>
							<td colspan=\"9\">";
			$ADMISSAO .="			<table width=\"100%\" border=\"1\">";
			$ADMISSAO .="	          <tr align=\"center\" style=\"font-family: Arial, Helvetica, sans-serif; font-weight: bold; font-size: 12px;\">
										<td colspan=\"3\">PIS</td>
								  </tr>";
			$ADMISSAO .="	          <tr align=\"center\" style=\"font-family: Arial, Helvetica, sans-serif; font-weight: bold; font-size: 12px;\">
										<td width=\"16%\">CADASTRADO EM: </td>
										<td width=\"7%\">NÚMERO</td>
										<td width=\"15%\">BANCO</td>
								  </tr>";
			$ADMISSAO .="            <tr align=\"center\" style=\"font-family: Arial, Helvetica, sans-serif; font-size: 10px;\">
										<td>". mysql_php($reg_editar["pis_data"]) ."</td>
										<td>". $reg_editar["pis_num"] ."</td>";

			$ADMISSAO .="     			<td>".$bancos[$reg_editar["pis_banco"]]." / ".$bancos_nome[$reg_editar["pis_banco"]]."</td>";
			$ADMISSAO .=" 			  </tr>";
			$ADMISSAO .="        	</table>";
			$ADMISSAO .=" 		</td>";
			$ADMISSAO .="    </tr>";
			$ADMISSAO .="    <tr>
							<td colspan=\"9\">";
			$ADMISSAO .="			<table width=\"100%\" border=\"1\">";
			$ADMISSAO .="				<tr align=\"center\" style=\"font-family: Arial, Helvetica, sans-serif; font-weight: bold; font-size: 12px;\">";
			$ADMISSAO .="		            <td width=\"16%\">DATA DA ADMISSÃO </td>
										<td width=\"7%\">NATUREZA DO CARGO</td>
										<td width=\"15%\">SÁLARIO INICIAL</td>
										
										<td width=\"11%\">DATA DE INICIO</td>
									</tr>";

			$ADMISSAO .="		        <tr align=\"center\" style=\"font-family: Arial, Helvetica, sans-serif; font-size: 10px;\">
										<td>". $reg_editar["clt_admissao"] ."</td>
										<td>". $reg1["grupo"] ."</td>
										<td>". $reg_sal["salario_clt"]. "</td>
										
										<td>". $reg_editar["data_inicio"] ."</td>
									</tr>";
			$ADMISSAO .=" 			</table>";
			$ADMISSAO .="  	</td>";
			$ADMISSAO .="   </tr>";
			$ADMISSAO .="	 <tr>
							<td colspan=\"9\">";
			$ADMISSAO .="			<table width=\"100%\" border=\"1\">";
			$ADMISSAO .="     			<tr align=\"center\" style=\"font-family: Arial, Helvetica, sans-serif; font-weight: bold; font-size: 12px;\">
										<td colspan=\"4\">HORÁRIO DE TRABALHO </td>
									</tr>";
			$ADMISSAO .="		        <tr align=\"center\" style=\"font-family: Arial, Helvetica, sans-serif; font-weight: bold; font-size: 12px;\">
										<td width=\"16%\">ENTRADA </td>
										<td width=\"7%\">REFEIÇÃO</td>
										<td width=\"15%\">SAÍDA </td>
										<td width=\"13%\">DESCANSO SEMANAL  </td>
									</tr>";
			$ADMISSAO .="		        <tr align=\"center\" style=\"font-family: Arial, Helvetica, sans-serif; font-size: 10px;\">
										<td>". $reg_editar["horario_entrada"] ."</td>
										<td>". $reg_editar["refeicao"] ."</td>
										<td>". $reg_editar["horario_saida"] ."</td>
										<td>". $reg_editar["descanso_semanal"] ."</td>
									</tr>";
			$ADMISSAO .="        </table>";
			$ADMISSAO .=" 		</td>";
			$ADMISSAO .="  </tr>";
			$ADMISSAO .="  <tr>
							<td colspan=\"9\">";
			$ADMISSAO .="			<table width=\"100%\" border=\"1\">";
			$ADMISSAO .="	          <tr align=\"center\" style=\"font-family: Arial, Helvetica, sans-serif; font-weight: bold; font-size: 12px;\">
									<td colspan=\"6\">CARACTERÍSTICAS FÍSICAS </td>
								  </tr>";
			$ADMISSAO .="	          <tr align=\"center\" style=\"font-family: Arial, Helvetica, sans-serif; font-weight: bold; font-size: 12px;\">
									<td width=\"16%\">COR</td>
									<td width=\"16%\">SEXO</td>
									<td width=\"7%\">CABELOS</td>
									<td width=\"15%\">OLHOS</td>
									<td width=\"13%\">ALTURA(m)</td>
									<td width=\"13%\">PESO(kg)</td>
								  </tr>";
			$ADMISSAO .="	          <tr align=\"center\" style=\"font-family: Arial, Helvetica, sans-serif; font-size: 10px;\">
									<td>". $reg_editar["cor"] ."</td>
									<td>". $reg_editar["sexo"] ."</td>
									<td>". $reg_editar["cabelos"] ."</td>
									<td>". $reg_editar["olhos"] ."</td>
									<td>". $reg_editar["altura"] ."</td>
									<td>". $reg_editar["peso"] ."</td>
								  </tr>";
			$ADMISSAO .="        </table>";
			$ADMISSAO .="	   </td>";
			$ADMISSAO .="   </tr>";
			$ADMISSAO .="	</table>";
			$ADMISSAO .="</td>
					  <td> </td>
					 </tr>";
			$ADMISSAO .="<tr align=\"center\" style=\"font-family: Arial, Helvetica, sans-serif; font-weight: bold; font-size: 12px;\">
						<td colspan=\"4\" >Rua XXXXXXXXX, XX - Centro - XXXXXXXX - SP</td>
					  </tr>";
			$ADMISSAO .="<tr align=\"center\" style=\"font-family: Arial, Helvetica, sans-serif; font-weight: bold; font-size: 12px;\">
						<td colspan=\"4\" >cep: XXXXXXX - TEL: (11) XXXXXXXX - FAX: (11) XXXXXXX</td>
					  </tr>";
			$ADMISSAO .="<tr align=\"center\" style=\"font-family: Arial, Helvetica, sans-serif; font-weight: bold; font-size: 12px;\">
						<td colspan=\"4\" >Site: http://www.empresa.com.br - E-mail: empresa@dominio.com.br</td>
					  </tr>";

			$ADMISSAO .="</table>";

			if(ENVIA_EMAIL)
			{
				$params 			= array();
				$params['from']		= "recrutamento@dominio.com.br";
				$params['from_name']= "RECURSOS HUMANOS";
				$params['subject'] 	= "ADMISSÃO FUNCIONARIO";

				//Agora passando o segundo parametro buscaremos os e-mails direto no banco de dados
				$mail = new email($params, 'admissao_funcionario');
				
				$mail->montaCorpoEmail($ADMISSAO);

				if(!$mail->Send())
				{
					$resposta->addAlert('Erro ao enviar e-mail!!! '.$mail->ErrorInfo);
				}

				$mail->ClearAddresses();
			}
			else 
			{
				$resposta->addScriptCall('modal', $ADMISSAO, '300_650', 'Conteúdo email', 14);
			}
		
		}

		$usql = "UPDATE ".DATABASE.".funcionarios SET ";
		$usql .= "envio_microsiga = 1 ";
		$usql .= "WHERE id_funcionario = '".$id."' ";
		$usql .= "AND reg_del = 0 ";

		$db->update($usql,'MYSQL');

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}

		$resposta->addAlert("funcionario exportado com sucesso para o Microsiga");
	}

	$resposta->addScript("xajax_atualizatabela('',xajax.getFormValues('frm_funcionarios')); ");

	return $resposta;
}

function funcoes($cod_cargo, $selecionado = 0)
{
	$resposta = new xajaxResponse();

	$resposta->addScript("combo_destino = document.getElementById('funcao_dvm');");

	$resposta->addScriptCall("limpa_combo('funcao_dvm')");

	$db = new banco_dados;

	$sql = "SELECT * FROM ".DATABASE.".rh_funcoes ";
	$sql .= "WHERE rh_funcoes.id_cargo_grupo = '".$cod_cargo."' ";
	$sql .= "AND reg_del = 0 ";
	$sql .= "ORDER BY descricao ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	else
	{
		$i = 0;

		foreach ($db->array_select as $regs)
		{
			if($regs["id_funcao"]==$selecionado)
			{
				$sel = 'true';
			}
			else
			{
				$sel = 'false';
			}

			if($i==0)
			{
				$def = 'true';
			}
			else
			{
				$def = 'false';
			}

			$i = 1;


			$resposta->addScript("combo_destino.options[combo_destino.length] = new Option('".$regs["descricao"]."', '".$regs["id_funcao"]."',".$def.",".$sel.");");
		}
	}

	return $resposta;
}

function redimensionar($imagem,$id_funcionario,$nome_antigo = "")
{
	if($nome_antigo=="")
	{
		$nome = "";
	}
	else
	{
		$nome = $nome_antigo;
	}

	if($imagem["name"]!="")
	{

		$dir = "fotos/";

		$nome = "foto_".$id_funcionario."_".rand().".jpg";

		if(is_file($dir.$nome_antigo))
		{
			unlink($dir.$nome_antigo);
		}

		if ($imagem['type']=="image/jpeg" || $imagem['type']=="image/pjpeg")
		{
			$img = imagecreatefromjpeg($imagem['tmp_name']);

			$x   = imagesx($img);

			$y   = imagesy($img);

			$largura = 100;

			$altura = 120;

			$nova = imagecreatetruecolor($largura, $altura);

			imagecopyresampled($nova, $img, 0, 0, 0, 0, $largura, $altura, $x, $y);

			imagejpeg($nova, $dir.$nome);

			imagedestroy($img);

			imagedestroy($nova);
		}
	}

	return $nome;
}

function marcarNR($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados();
	
	if (empty($dados_form['rdo_tp_nr']) || empty($dados_form['txt_envolvidos']))
	{
		$resposta->addAlert('Por favor, preencha todos os campos');
		return $resposta;
	}
	
	$sql = 
	"SELECT
		funcionario, email 
	FROM 
		".DATABASE.".funcionarios
		LEFT JOIN(
			SELECT * FROM ".DATABASE.".usuarios WHERE usuarios.reg_del = 0 			
		) usuarios
		ON funcionarios.id_usuario = usuarios.id_usuario
	WHERE
		funcionarios.reg_del = 0
		AND funcionarios.id_funcionario = '".$dados_form['cod_funcionario']."'";
	
	$db->select($sql, 'MYSQL', true);
	
	$isql  = "INSERT INTO ".DATABASE.".nao_recomendados (nome, email, cpf, tipo, descricao) VALUES ";
	$isql .= "('".$db->array_select[0]['funcionario']."', '".strtoupper($db->array_select[0]['email'])."', '".$dados_form['cpf']."', '".$dados_form['rdo_tp_nr']."', '".strtoupper(tiraacentos(preg_replace("/\r\n|\r|\n/",'<br/>',$dados_form['txt_envolvidos'])))."')";

	$db->insert($isql, 'MYSQL');
	
	if ($db->erro != '')
	{
		$resposta->addAlert('Houve uma falha ao tentar realizar esta tarefa! '.$db->erro);
	}
	else
	{
		$resposta->addAlert('Tarefa realizada corretamente!');
		$resposta->addScript('divPopupInst.destroi();');
		$resposta->addScript("xajax_atualizatabela('',xajax.getFormValues('frm_funcionarios'));");
	}
		
	return $resposta;
}

$xajax->registerFunction("voltar");
$xajax->registerFunction("insere");
$xajax->registerFunction("envia_microsiga");
$xajax->registerFunction("atualizar");
$xajax->registerFunction("editar");
$xajax->registerFunction("atualizatabela");
$xajax->registerFunction("preenche");
$xajax->registerFunction("calcula_idade");
$xajax->registerFunction("data_deslig");
$xajax->registerFunction("funcoes");
$xajax->registerFunction("getAjudaCustoAdicional");
$xajax->registerFunction("removerAjudaCustoAdicional");
$xajax->registerFunction("marcarNR");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","xajax_atualizatabela('',xajax.getFormValues('frm_funcionarios'));xajax_getAjudaCustoAdicional();");

$db = new banco_dados();

$array_cargo_values = NULL;
$array_cargo_output = NULL;

$array_setor_values = NULL;
$array_setor_output = NULL;

$array_empresa_values = NULL;
$array_empresa_output = NULL;

$array_local_values = NULL;
$array_local_output = NULL;

$array_bancos_values = NULL;
$array_bancos_output = NULL;

$array_instrucao_values = NULL;
$array_instrucao_output = NULL;

$array_infra_values = NULL;
$array_infra_output = NULL;

$array_cargo_values[] = "";
$array_cargo_output[] = "SELECIONE";

$array_cc_values[] = '';
$array_cc_output[] = 'SELECIONE...';

//CARGOS
$sql = "SELECT id_cargo_grupo, grupo FROM ".DATABASE.".rh_cargos ";
$sql .= "WHERE reg_del = 0 ";
$sql .= "ORDER BY grupo ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}

foreach($db->array_select as $reg)
{
	$array_cargo_values[] = $reg["id_cargo_grupo"];
	$array_cargo_output[] = $reg["grupo"];
}

$array_setor_values[] = "";
$array_setor_output[] = "SELECIONE";

$sql = "SELECT id_setor, setor FROM ".DATABASE.".setores ";
$sql .= "WHERE id_setor NOT IN ('16','17','19','21','24','25') ";
$sql .= "AND setores.reg_del = 0 ";
$sql .= "ORDER BY setor ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}

foreach ($db->array_select as $regset)
{
	$array_setor_values[] = $regset["id_setor"];
	$array_setor_output[] = $regset["setor"];
}

$array_setor_aso_values[] = "";
$array_setor_aso_output[] = "SELECIONE";


$sql = "SELECT id_setor_aso, setor_aso FROM ".DATABASE.".setor_aso ";
$sql .= "WHERE setor_aso.reg_del = 0 ";
$sql .= "ORDER BY setor_aso ";

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

$array_local_values[] = "";
$array_local_output[] = "SELECIONE";

$sql = "SELECT * FROM ".DATABASE.".local ";
$sql .= "WHERE reg_del = 0 ";
$sql .= "ORDER BY descricao ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}

foreach ($db->array_select as $reg)
{
	$array_local_values[] = $reg["id_local"];
	$array_local_output[] = $reg["descricao"];
}

$array_bancos_values[] = "";
$array_bancos_output[] = "SELECIONE";

$sql = "SELECT id_banco, dv, instituicao FROM ".DATABASE.".bancos ";
$sql .= "WHERE reg_del = 0 ";
$sql .= "ORDER BY instituicao ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}

foreach($db->array_select as $contbancos)
{
	$array_bancos_values[] = $contbancos["id_banco"];
	$array_bancos_output[] = $contbancos["dv"]." - ".$contbancos["instituicao"];
}

$array_instituicao_values[] = "";
$array_instituicao_output[] = "SELECIONE";

$sql = "SELECT id_rh_instituicao_ensino, instituicao_ensino FROM ".DATABASE.".rh_instituicao_ensino ";
$sql .= "WHERE reg_del = 0 ";
$sql .= "ORDER BY instituicao_ensino ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}

foreach($db->array_select as $regs)
{
	$array_instituicao_values[] = $regs["id_rh_instituicao_ensino"];
	$array_instituicao_output[] = $regs["instituicao_ensino"];
}

$array_empresa_values[] = "0";
$array_empresa_output[] = "NENHUMA";

$array_empresa_dvm_values[] = "0";
$array_empresa_dvm_output[] = "NENHUMA";

$array_produto_values[] = "";
$array_produto_output[] = "SELECIONE";

$array_site_values[] = "";
$array_site_output[] = "SELECIONE";

$sql = "SELECT empresa_situacao, id_empfunc, empresa_func FROM ".DATABASE.".empresa_funcionarios ";
$sql .= "WHERE reg_del = 0 ";
$sql .= "ORDER BY empresa_situacao DESC, empresa_func ASC ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}

foreach($db->array_select as $cont)
{
	if($cont["empresa_situacao"]==1)
	{
		$situacao = "ATIVA";
	}
	else
	{
		$situacao = "INATIVA";
	}

	$array_empresa_dvm_values[] = $cont["id_empfunc"];
	$array_empresa_dvm_output[] = $cont["empresa_func"]." - ".$situacao;
}

$array_infra_values = array();
$array_infra_output = array();

$array_softwares_values = array();
$array_softwares_output = array();

//INFRAESTRUTURA TI
$sql = "SELECT id_infra_estrutura, infra_estrutura, uso FROM ".DATABASE.".infra_estrutura ";
$sql .= "WHERE uso IN (1,2,3) ";
$sql .= "AND reg_del = 0 ";
$sql .= "ORDER BY infra_estrutura";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}

foreach ($db->array_select as $reg)
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

$sql = "SELECT tipoAdicional, id_adicional, adicional  FROM ".DATABASE.".tipo_adicional
		JOIN (
			SELECT id_tipo_adicional tipoAdicional, adicional, id_adicional
			FROM ".DATABASE.".rh_adicional WHERE rh_adicional.reg_del = 0 
		) clausulas ON tipoAdicional = id_tipo_adicional AND tipo_adicional.reg_del = 0 
		ORDER BY tipoAdicional, id_adicional ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}

$array_refeicao_values[] = "";
$array_refeicao_output[] = "Selecione...";

$array_transporte_values[] = "";
$array_transporte_output[] = "Selecione...";

$array_hotel_values[] = "";
$array_hotel_output[] = "Selecione...";

foreach ($db->array_select as $reg)
{
	switch($reg['tipoAdicional'])
	{
		case 1:
			$array_refeicao_values[] = $reg["id_adicional"];
			$array_refeicao_output[] = $reg["adicional"];
		break;
		case 2:
			$array_transporte_values[] = $reg["id_adicional"];
			$array_transporte_output[] = $reg["adicional"];
		break;
		case 3:
			$array_hotel_values[] = $reg["id_adicional"];
			$array_hotel_output[] = $reg["adicional"];
		break;
	}
}

//CONEXAO BANCO MICROSIGA
/*
$sql = "SELECT * FROM SA2010 WITH(NOLOCK) ";
$sql .= "WHERE D_E_L_E_T_ = '' ";
$sql .= "AND SA2010.A2_FABRICA = 2 "; //FORNECEDOR
$sql .= "ORDER BY SA2010.A2_NREDUZ ";

$db->select($sql,'MSSQL', true);

if($db->erro!='')
{
	die($db->erro);
}

foreach($db->array_select as $regs)
{
	$array_empresa_values[] = trim($regs["A2_COD"]);
	$array_empresa_output[] = maiusculas($regs["A2_NREDUZ"]);
}
*/

$sql = "SELECT tabela, chave, descricao FROM ".DATABASE.".genericos ";
$sql .= "WHERE reg_del = 0 ";

$db->select($sql,'MYSQL', true);

if($db->erro!='')
{
	die($db->erro);
}

foreach($db->array_select as $regs)
{
	switch($regs['tabela'])
	{
		case '34';
			$array_nacionalidade_values[] = trim($regs["chave"]);
			$array_nacionalidade_output[] = maiusculas($regs["descricao"]);
			break;
		//ESTADO CIVIL
		case '33';
			$array_est_civ_values[] = trim($regs["chave"]);
			$array_est_civ_output[] = maiusculas($regs["descricao"]);
			break;
		//GRAU INSTRUCAO
		case '26';
			$array_instrucao_values[] = trim($regs["chave"]);
			$array_instrucao_output[] = maiusculas($regs["descricao"]);
			break;
		//CATEGORIA FUNCIONAL
		case '28';
			$array_categoria_funcional_values[] = trim($regs["chave"]);
			$array_categoria_funcional_output[] = maiusculas($regs["descricao"]);
			break;
		//TIPO PAGAMENTO
		case '40';
			$array_tipo_pagamento_values[] = trim($regs["chave"]);
			$array_tipo_pagamento_output[] = maiusculas($regs["descricao"]);
			break;
		//VINCULO EMPREGATICIO
		case '25';
			$array_vinculo_values[] = trim($regs["chave"]);
			$array_vinculo_output[] = maiusculas($regs["descricao"]);
			break;
		//TIPO ADMISSAO
		case '38';
			$array_tipo_admissao_values[] = trim($regs["chave"]);
			$array_tipo_admissao_output[] = maiusculas($regs["descricao"]);
			break;
		//TIPO SALARIAL
		case '41';
			$array_tipo_salario_values[] = trim($regs["chave"]);
			$array_tipo_salario_output[] = maiusculas($regs["descricao"]);
			break;
	}
}

$array_turno_values[] = 1;
$array_turno_output[] = maiusculas('Seg-Sex/08:00 as 17:00');

/*

$sql = "SELECT DISTINCT R6_TURNO, R6_DESC FROM SR6010 WITH(NOLOCK) "; //TURNO TRABALHO
$sql .= "WHERE D_E_L_E_T_ = ' ' ";

$db->select($sql,'MSSQL', true);

if($db->erro!='')
{
	die($db->erro);
}

foreach($db->array_select as $regs)
{
	$array_turno_values[] = trim($regs["R6_TURNO"]);
	$array_turno_output[] = maiusculas($regs["R6_DESC"]);
}

$sql = "SELECT CTT_CUSTO, CTT_DESC01 FROM CTT010 WITH(NOLOCK) ";
$sql .= "WHERE D_E_L_E_T_ = '' "; //CENTRO DE CUSTO
$sql .= "AND CTT_BLOQ = '2' "; //SOMENTE OS CC NAO BLOQUEADOS

$db->select($sql,'MSSQL', true);

if($db->erro!='')
{
	die($db->erro);
}

foreach($db->array_select as $regs)
{
	$array_cc_values[] = trim($regs["CTT_CUSTO"]);
	$array_cc_output[] = trim($regs["CTT_CUSTO"]). ' - ' .maiusculas($regs["CTT_DESC01"]);
}

$sql = "SELECT B1_COD, B1_DESC FROM SB1010 WITH(NOLOCK) ";
$sql .= "WHERE D_E_L_E_T_ = '' ";
$sql .= "AND (B1_COD LIKE '11%' ";
$sql .= "OR B1_COD LIKE '12%' ";
$sql .= "OR B1_COD LIKE '13%') ";
$sql .= "ORDER BY B1_DESC ";

$db->select($sql,'MSSQL', true);

if($db->erro!='')
{
	die($db->erro);
}

foreach($db->array_select as $regs)
{
	$array_produto_values[] = trim($regs["B1_COD"]);
	$array_produto_output[] = maiusculas($regs["B1_DESC"]);
}

//ITEM
$sql = "SELECT CTD_ITEM, CTD_DESC01 FROM CTD010 WITH(NOLOCK) ";
$sql .= "WHERE D_E_L_E_T_ = '' ";
$sql .= "AND CTD_CLASSE = 2 ";

$db->select($sql,'MSSQL', true);

if($db->erro!='')
{
	die($db->erro);
}

foreach($db->array_select as $regs)
{
	$array_site_values[] = trim($regs["CTD_ITEM"]);
	$array_site_output[] = maiusculas($regs["CTD_DESC01"]);
}
*/

$sql = "SELECT DISTINCT id_os, os, descricao FROM ".DATABASE.".ordem_servico, ".DATABASE.".ordem_servico_status ";
$sql .= "WHERE ordem_servico.id_os_status = ordem_servico_status.id_os_status ";
$sql .= "AND ordem_servico.reg_del = 0 ";
$sql .= "AND ordem_servico_status.reg_del = 0 ";
$sql .= "AND ordem_servico_status.id_os_status IN (1,14,16) ";
$sql .= "ORDER BY ordem_servico.os ";

$db->select($sql,'MYSQL',true);

$array_os_values[] = "";
$array_os_output[] = "Selecione...";

foreach ($db->array_select as $reg)
{
	$array_os_values[] = $reg["id_os"];
	$array_os_output[] = $reg["os"].' - '.$reg['descricao'];
}

$sql = "SELECT MAX(numero_contrato) AS proximoContrato FROM ".DATABASE.".pj_contratos ";
$sql .= "WHERE pj_contratos.reg_del = 0 ";

$db->select($sql, 'MYSQL',true);

$proximoContrato = $db->array_select[0];

$nContrato = substr_replace($proximoContrato['proximoContrato'], '', -4, 4);
$anoContrato = intval(substr($proximoContrato['proximoContrato'], -4));

$smarty->assign('proximo_contrato', ($nContrato + 1));

$anos = array();

for($i = date('Y'); $i >= 2009; $i--)
{
	$anos[] = $i;
}

$smarty->assign('option_anos_values', $anos);

$smarty->assign("option_os_values",$array_os_values);
$smarty->assign("option_os_output",$array_os_output);

$smarty->assign("option_cargo_values",$array_cargo_values);
$smarty->assign("option_cargo_output",$array_cargo_output);

$smarty->assign("option_setor_values",$array_setor_values);
$smarty->assign("option_setor_output",$array_setor_output);

$smarty->assign("option_setor_aso_values",$array_setor_aso_values);
$smarty->assign("option_setor_aso_output",$array_setor_aso_output);

$smarty->assign("option_local_values",$array_local_values);
$smarty->assign("option_local_output",$array_local_output);

$smarty->assign("option_bancos_values",$array_bancos_values);
$smarty->assign("option_bancos_output",$array_bancos_output);

$smarty->assign("option_instituicao_values",$array_instituicao_values);
$smarty->assign("option_instituicao_output",$array_instituicao_output);

$smarty->assign("option_instrucao_values",$array_instrucao_values);
$smarty->assign("option_instrucao_output",$array_instrucao_output);

$smarty->assign("option_empresa_values",$array_empresa_values);
$smarty->assign("option_empresa_output",$array_empresa_output);

$smarty->assign("option_empresa_dvm_values",$array_empresa_dvm_values);
$smarty->assign("option_empresa_dvm_output",$array_empresa_dvm_output);

$smarty->assign("option_nacionalidade_values",$array_nacionalidade_values);
$smarty->assign("option_nacionalidade_output",$array_nacionalidade_output);

$smarty->assign("option_infra_values",$array_infra_values);
$smarty->assign("option_infra_output",$array_infra_output);

$smarty->assign("option_softwares_values",$array_softwares_values);
$smarty->assign("option_softwares_output",$array_softwares_output);

$smarty->assign("option_refeicao_values",$array_refeicao_values);
$smarty->assign("option_refeicao_output",$array_refeicao_output);

$smarty->assign("option_transporte_values",$array_transporte_values);
$smarty->assign("option_transporte_output",$array_transporte_output);

$smarty->assign("option_hotel_values",$array_hotel_values);
$smarty->assign("option_hotel_output",$array_hotel_output);

$smarty->assign("option_est_civ_values",$array_est_civ_values);
$smarty->assign("option_est_civ_output",$array_est_civ_output);
$smarty->assign("selecionado","S");

$smarty->assign("option_categoria_funcional_values",$array_categoria_funcional_values);
$smarty->assign("option_categoria_funcional_output",$array_categoria_funcional_output);
$smarty->assign("selecionado_1","M");

$smarty->assign("option_tipo_pagamento_values",$array_tipo_pagamento_values);
$smarty->assign("option_tipo_pagamento_output",$array_tipo_pagamento_output);
$smarty->assign("selecionado_2","M");

$smarty->assign("option_vinculo_values",$array_vinculo_values);
$smarty->assign("option_vinculo_output",$array_vinculo_output);
$smarty->assign("selecionado_3","10");

$smarty->assign("option_tipo_admissao_values",$array_tipo_admissao_values);
$smarty->assign("option_tipo_admissao_output",$array_tipo_admissao_output);
$smarty->assign("selecionado_4","9B");

$smarty->assign("option_tipo_salario_values",$array_tipo_salario_values);
$smarty->assign("option_tipo_salario_output",$array_tipo_salario_output);
$smarty->assign("selecionado_5","001");

$smarty->assign("option_turno_values",$array_turno_values);
$smarty->assign("option_turno_output",$array_turno_output);

$smarty->assign("option_cc_values",$array_cc_values);
$smarty->assign("option_cc_output",$array_cc_output);

$smarty->assign("option_produto_values",$array_produto_values);
$smarty->assign("option_produto_output",$array_produto_output);

$smarty->assign("option_site_values",$array_site_values);
$smarty->assign("option_site_output",$array_site_output);

$smarty->assign("data_inicio",date("d/m/Y"));

$smarty->assign("data_desligamento",date("d/m/Y"));

$smarty->assign("data_admissao",date("d/m/Y"));

$smarty->assign('campo', $conf->campos('cadastro_funcionarios'));

$smarty->assign('revisao_documento', 'V12');

$smarty->assign("classe",CSS_FILE);

$smarty->assign("larguraTotal",1);

$smarty->display('funcionarios.tpl');

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script src="<?php echo INCLUDE_JS ?>jquery/jquery.min.js"></script>
<script src="<?php echo INCLUDE_JS ?>jquery/jquery-ui-1.11.1/jquery-ui.min.js"></script>

<script language="javascript">
    function popup_marcar_nr(cod_funcionario, cpf)
    {
        var html = 	'<form id="frm_nr" name="frm_nr">'+
                    '<input type="hidden" id="cod_funcionario" name="cod_funcionario" value="'+cod_funcionario+'" />'+
                    '<input type="hidden" id="cpf" name="cpf" value="'+cpf+'" />'+
                    '<label class="labels">TÉCNICO</label> <input type="radio" class="caixa" value="TÉCNICO" id="rdo_tp_nr" name="rdo_tp_nr" /> '+
                    '<label class="labels">COMPORTAMENTAL</label> <input type="radio" class="caixa" value="COMPORTAMENTAL" id="rdo_tp_nr" name="rdo_tp_nr" /><br />'+
                    '<label class="labels">Envolvidos</label><br />'+
                    '<textarea class="caixa" style="width:100%;" rows="7" id="txt_envolvidos" name="txt_envolvidos"></textarea><br />'+
                    '<input type="button" class="class_botao" onclick="xajax_marcarNR(xajax.getFormValues(\'frm_nr\'));" value="SALVAR" id="btn_salvar" name="btn_salvar" />'+
                    '</form>';

        modal(html, 'pp', "MARCAR NR"); 
    }

    function alteracaolocalTrabalho(novolocal)
    {
        document.getElementById('empresa').value = novolocal;
        document.getElementById('numeroContrato').value = '';
        document.getElementById('refeicao').value = '';
        document.getElementById('transporte').value = '';
        document.getElementById('hotel').value = '';
        document.getElementById('ref_transp_outros').value = '';
        document.getElementById('contratoDe').value = '';
        document.getElementById('contratoAte').value = '';
        document.getElementById('os').value = '';
        document.getElementById('centrocusto').value = '';
    }
    
    function habilitarNumeroContrato()
    {
        tipoTributacao = document.getElementById('tipo_tributacao').value;

        if (tipoTributacao == '')
        {
          document.getElementById('contratoColaboradorNumero').disabled = true;
          document.getElementById('contratoColaboradorAno').disabled = true;
        }
        else
        {
          document.getElementById('contratoColaboradorNumero').disabled = false;
          document.getElementById('contratoColaboradorAno').disabled = false;
        }
    }
    
    function marcaAlteracaoExigencias()
    {
        document.getElementById('alteracaoExigencias').value = 1;
    }

    function exibe_foto(aElement)
    {
        aElement.getElementsByTagName('img')[0].style.display = 'block';
    }

    function oculta_foto(aElement)
    {
        aElement.getElementsByTagName('img')[0].style.display = 'none';
    }
	
	function tab()
	{
		myTabbar = new dhtmlXTabBar("my_tabbar");
	}

    function grid(tabela, autoh, height, xml)
    {
        mygrid = new dhtmlXGridObject(tabela);

        mygrid.enableAutoHeight(autoh,height);
        mygrid.enableRowsHover(true,'cor_mouseover');

        mygrid.setHeader("Funcionário, Sigla, Função, Disciplina, Setor/ASO, Situação, Empresa, Exp, NR");
        mygrid.setInitWidths("250,50,250,120,120,80,*,50,50");
        mygrid.setColAlign("left,left,left,left,left,center,left,center,center");
        mygrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro");
        mygrid.setColSorting("str,str,str,str,str,str,str,str,str");

        function editar(row,col)
        {
            if (col < 8)
                xajax_editar(row);
        }

        mygrid.attachEvent("onRowSelect",editar);
        mygrid.setSkin("dhx_skyblue");
        mygrid.enableMultiselect(true);
        mygrid.enableCollSpan(true);
        mygrid.enableTooltips("false,false,false,false,false,false,false,false");
        mygrid.init();
        mygrid.loadXMLString(xml);
    }

    function imprimir_formularios(id_funcionario)
    {
        window.open('relatorios/cartas_demissao.php?id_funcionario='+id_funcionario+'', '_blank');
    }

    var items = 0;

    function add_campo()
    {

        //exemplo de adicionar campos dinamicos a um formulario
        //incrementa o indice do campo
        items++;

        //cria o elemento combobox
        var combo_box = document.createElement('select');

        //percorre o formulario para encontrar os elementos
        for ( i=0; i < document.forms['frm_funcionarios'].elements.length; i++)
        {
            //caso seja um select

            if (document.forms['frm_funcionarios'].elements[i].type == 'select-one' && document.forms['frm_funcionarios'].elements[i].title == 'instituicao')
            {
                //seta as propriedades do combobox
                combo_box.name = 'instituicao_ensino_'+items;

                combo_box.id = 'instituicao_ensino_'+items;

                combo_box.className = 'caixa';

                combo_box.style.marginLeft = '3px';

                combo_box.onkeypress = 'return keySort(this);';

                //percorre os elementos do combobox inicial (que esta no formulario)
                for(j=0;j<document.getElementById('frm_funcionarios').elements.item(i).length;j++)
                {
                    //atribui o item e suas propriedades
                    var choice = document.createElement('option');

                    choice.value = document.getElementById('instituicao_ensino_0').options[j].value;

                    choice.appendChild(document.createTextNode(document.getElementById('instituicao_ensino_0').options[j].text));

                    //apenda ao combo criado
                    combo_box.appendChild(choice);

                }

                //obtem a qtd de itens do combo
                qtd_itens = document.getElementById('frm_funcionarios').elements.item(i).length;

                break;
            }

        }

        //verifica se a qtd itens e menor que a quantidade do combo
        if(items<(qtd_itens-1) || true)
        {
            //atribui o campo file

            texto = "<input name='descricao_formacao_"+items+"' size='25' type='text' class='caixa' id='descricao_formacao_"+items+"' />";
            texto1 = "<input name='ano_conclusao_"+items+"' size='5' maxlength='4' type='text' class='caixa' id='ano_conclusao_"+items+"' onkeypress='num_only();' />";

            tbl = document.getElementById('tbl_formacao');

            var novaLinha = tbl.insertRow(-1);

            var novaCelula;

            novaCelula = novaLinha.insertCell(0);

            //novaCelula.innerHTML = combo_box;
            novaCelula.appendChild(combo_box);

            novaCelula = novaLinha.insertCell(1);

            novaCelula.innerHTML = texto;

            novaCelula = novaLinha.insertCell(2);

            novaCelula.innerHTML = texto1;

            //atribui ao hidden o numero atual do item
            document.frm_funcionarios.itens.value = document.frm_funcionarios.itens.value = items;
        }
        else
        {
            alert('Nao pode inserir mais itens.');
        }

    }

    function sub_campo()
    {

        function remove_campo(campo)
        {
            elem = document.getElementById(campo);

            elem_rem = elem.parentNode;

            elem_rem.removeChild(elem);
        }

        var i;

        for(i=document.frm_funcionarios.itens.value;i>0;i--)
        {
            remove_campo('instituicao_ensino_'+i);

            remove_campo('descricao_formacao_'+i);

            remove_campo('ano_conclusao_'+i);

            el = document.getElementById('tbl_formacao');

        }

        items = 0;

        document.frm_funcionarios.itens.value = 0;


    }

    function verificaExt()
    {
        var emailNovo = document.getElementById('email').value;
        var tpContrato = document.getElementById('tipo_contrato').value;

        ext = tpContrato == 'SC' || tpContrato == 'SC+MENS' ? true : false;
        
        var arrEmail = emailNovo.split('ext.');

        //Verifica se já tem o ext. para não colocar mais de uma vez
        if (arrEmail.length == 1)
        {
            //Se for pj
            if (ext)
                emailNovo = 'ext.'+emailNovo;
        }
        else
        {
            //Se já tem o ext e for trocado para clt, tiro o ext do email
            if (!ext)
            {
                emailNovo = arrEmail[1];
            }
        }

        document.getElementById('email').value = emailNovo;
    }
    
</script>
