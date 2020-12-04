<?php
/*
	Formulário de Requisição de Pessoal - ADM
	
	Criado por Carlos Abreu / Otávio Pamplona
	
	local/Nome do arquivo:
	../rh/adm_requisicao_pessoal.php
	
	Versão 0 --> VERSÃO INICIAL : 21/06/2007
	Versão 1 --> Atualização Lay-out : 29/09/2008
	Versão 2 --> Alteração de funcionalidade : 08/10/2008
	Versão 3 --> Atualização de classe banco de dados - 23/01/2015
	Versão 4 --> Refeito o módulo ADM para agrupar todas as funções ao RH
	Versão 5 --> Adicionados os campos de mobilização
	Versão 6 --> Removida a parte de idiomas, visto que agora está no módulo de cargos/funções
	Versão 7 --> Atualização layout - Carlos Abreu - 10/04/2017
	Versão 8 --> Inclusão dos campos reg_del nas consultas - 29/11/2017 - Carlos Abreu
*/	

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

require_once(INCLUDE_DIR."antiInjection.php");

require_once(INCLUDE_DIR."mpdf60/mpdf.php");

$conf = new configs();

if (isset($_GET['impressao']) && $_GET['impressao'] == 1 && isset($_GET['id_requisicao']) && $_GET['id_requisicao'] > 0)
{
	$arrTipo = array('PROPOSTA', 'VAGA EFETIVA');
	$arrMotivo = array( "1" =>'NOVA OS',
						"2" => 'RETRABALHO OS',
						"3" =>'ATRASO DE OS',
						"4" =>'AUMENTO DE QUADRO',
						"5" =>'MOVIMENTAÇÃO INTERNA',
						"6" =>'DESLIGAMENTO JÁ EFETIVADO',
						"7" =>'DESLIGAMENTO A SER EFETIVADO',
						"8" =>'NOVA FUNÇÃO');
	$arrPrazo = array('1' => 'NORMAL: 15 A 30 DIAS','2' => 'URGENTE: 7 A 15 DIA', '3' => 'URGENTÍSSIMO: 3 A 7 DIAS');
	$arrTipoCt = array('1' => 'PJ','2' => 'CLT');
	$arrCategoria = array(	"1" =>'EFETIVO',
							"2" =>'TEMPORÁRIO',
							"3" =>'ESTAGIÁRIO',
							"4" =>'OUTRA');
	$arrNivelAtuacao = array(
		"A" =>'P / ADM. M.O.',
		"D" =>'DIREÇÃO',
		"C" =>'COORDENAÇÃO',
		"S" =>'SUPERVISÃO',
		"G" =>'GERÊNCIA',
		"E" =>'EXECUTANTE / INTERNO',
		"P" =>'PACOTE'
	);
	$arrIdiomas = array("1" =>'INGLÉS',
						"2" =>'ESPANHOL',
						"3" =>'FRANCÊS',
						"4" =>'ITALIANO',
						"5" =>'JAPONÊS');
	$arrNiveisIdiomas = array(	"1" => 'BÁSICO',
								"2" => 'INTERMEDIÁRIO',
								"3" => 'AVANÇADO',
								"4" => 'FLUENTE');

	$sql = "SELECT 
				funcionario, requisicoes_pessoal.*, OS.descricao as OS, GROUP_CONCAT(DISTINCT id_idioma) idiomas,
				GROUP_CONCAT(DISTINCT id_nivel) niveis_req, GROUP_CONCAT(id_infra) id_infra,
				GROUP_CONCAT(uso) infra_uso, status, local_trabalho, cargoDesc
			FROM 
				".DATABASE.".funcionarios, ".DATABASE.".requisicoes_pessoal ";
	$sql .= "JOIN(SELECT id_status_requisicao, status FROM ".DATABASE.".status_requisicao WHERE status_requisicao.reg_del = 0) status ON id_status_requisicao = ultimo_status ";
	$sql .= "LEFT JOIN(SELECT CAST(CONCAT(id_funcao,',',id_cargo_grupo) AS CHAR) as cargo, descricao as cargoDesc FROM ".DATABASE.".rh_funcoes WHERE rh_funcoes.reg_del = 0) funcao ON cargo = id_cargo ";
	$sql .= "LEFT JOIN (SELECT os, id_os codOs, descricao FROM ".DATABASE.".ordem_servico WHERE ordem_servico.reg_del = 0) os ON codOs = requisicoes_pessoal.id_os ";
	$sql .= "LEFT JOIN (SELECT id_requisicao idioma_req, id_idioma FROM ".DATABASE.".idioma_x_requisicao WHERE idioma_x_requisicao.reg_del = 0) idioma ON idioma_req = requisicoes_pessoal.id_requisicao ";
	$sql .= "LEFT JOIN (SELECT id_requisicao nivel_req, id_nivel FROM ".DATABASE.".nivel_x_requisicao WHERE nivel_x_requisicao.reg_del = 0) nivel_idioma ON nivel_req = requisicoes_pessoal.id_requisicao ";
	$sql .= "LEFT JOIN (SELECT id_requisicao infra_req, id_infra FROM ".DATABASE.".infra_x_requisicao WHERE infra_x_requisicao.reg_del = 0) infra ON infra_req = requisicoes_pessoal.id_requisicao ";
	$sql .= "LEFT JOIN (SELECT id_infra_estrutura, infra_estrutura, uso FROM ".DATABASE.".infra_estrutura WHERE infra_estrutura.reg_del = 0) infraExtrutura ON id_infra_estrutura = id_infra ";
	$sql .= "LEFT JOIN (SELECT id_local, descricao as local_trabalho FROM ".DATABASE.".local WHERE local.reg_del = 0) local ON id_local = id_local ";
	$sql .= "WHERE requisicoes_pessoal.id_solicitante = funcionarios.id_funcionario ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	$sql .= "AND requisicoes_pessoal.reg_del = 0 ";
	$sql .= "AND requisicoes_pessoal.id_requisicao = '" . $_GET['id_requisicao'] . "' ";

	//HTML que será transformado em pdf com o mPdf
	$css = "p,h2{font-family: arial, trebuschet;line-height:15px;} ";
	$css .= "p{font-size: 8pt;}";
	$css .= "h2{font-size:10pt;}";
	$css .= "hr{margin-top:-10px;}";
	
	$html = "<style>{$css}</style>";
	$html .= "<img src='".DIR_IMAGENS."logo_pb.png' width='150' /><br /><br />";
	$html .= "<h2 align='center'>REQUISIÇÃO DE PESSOAL Nº ".$_GET['id_requisicao']."</h2><hr />";
	
	$db->select($sql,'MYSQL',true);
	
	$reg = $db->array_select[0];
			
	$motivo = $reg['motivo'] != 9 ? $arrMotivo[$reg['motivo']] : $reg['motivo_outros'];
	
	$html .= "<p><b>REQUISITANTE</b>:  ".$reg['funcionario']."<br />";
	$html .= "<b>STATUS DA REQUISIÇÃO</b>:  ".$reg['status']."<br />";
	$html .= "<b>TIPO DE VAGA</b>:  ".$arrTipo[$reg['tipo']]."<br />";
	$html .= "<b>MOTIVO DA REQUISIÇÃO</b>:  ".$motivo."<br />";
	$html .= "<b>PRAZO:</b>:  ".$arrPrazo[$reg['prazo']]."<br />";
	$html .= "<b>TIPO DE CONTRATO</b>:  ".maiusculas($arrTipoCt[$reg['tipo_contrato']])."<br />";
	$html .= "<b>CATEGORIA CONTRATAÇÃO</b>:  ".maiusculas($arrCategoria[$reg['categoria_contratacao']])."<br />";

	$mobilizacao = $reg['mobilizacao'] == 0 ? 'EMPRESA' : 'COLABORADOR';
	$html .= "<b>MOBILIZAÇÃO:</b>: ".$mobilizacao."<br />";
	$html .= "<b>DETALHES MOBILIZAÇÃO</b>: ".$reg['detalhes_mobilizacao']."<br /></p>";
	
	$html .= "<p><b>OS</b>:  ".$reg['os']."<br />";
	$html .= "<b>LOCAL DE TRABALHO</b>:  ".$reg['local_trabalho']."<br />";
	$html .= "<b>QTD. VAGAS</b>:  ".$reg['qtde_vagas']."<br />";
	$html .= "<b>TEMPO DE SERVIÇO</b>:  ".maiusculas($reg['tempo_servico'])."<br />";
	$html .= "<b>FUNÇÃO</b>:  ".maiusculas($reg['cargoDesc'])."<br />";
	$html .= "<b>NÍVEL DE ATUAÇÃO</b>: ".$arrNivelAtuacao[$reg['nivel_atuacao']]."<br />";
	
	if (!empty($reg['id_infra']))
	{
		//Recursos de TI
		$sql = "SELECT * FROM ".DATABASE.".infra_estrutura ";
		$sql .= "WHERE uso IN (2,3) ";
		$sql .= "AND reg_del = 0 ";
		$sql .= "AND id_infra_estrutura IN(".$reg['id_infra'].") ";
		$sql .= "ORDER BY uso";
		
		$db->select($sql,'MYSQL',true);
				
		$infra = '';

		foreach($db->array_select as $reg1)
		{
			$infra .= '<br />&nbsp;&nbsp;'.maiusculas($reg1['infra_estrutura']);
		}		
	}
	
	$infra .= '<br />&nbsp;&nbsp;'.maiusculas($reg['informacoes_ti']);
	$html .= "<b>RECURSO DE TI</b>: ".$infra."</p>";
	
	$html .= "<p align='center'><b>REQUISITOS DO CARGO</b></p><hr />";
	
	$cargo = explode(',', $reg['id_cargo']);
	
	$sql = 
	"SELECT
		conhecimento
	FROM
		".DATABASE.".rh_cargos_x_conhecimento, ".DATABASE.".rh_conhecimentos
	WHERE
		rh_cargos_x_conhecimento.reg_del = 0
		AND rh_conhecimentos.reg_del = 0
		AND rh_cargos_x_conhecimento.id_rh_cargo = '".$cargo[0]."'
		AND rh_conhecimentos. id_rh_conhecimento = rh_cargos_x_conhecimento.id_rh_conhecimento
		AND rh_cargos_x_conhecimento.rh_cargos_x_conhecimento_status = 1";

	$requisitosCargo = '';
	
	$db->select($sql, 'MYSQL',true);
	
	foreach($db->array_select as $reg1)
	{
		$requisitosCargo .= "<br />&nbsp;&nbsp;".maiusculas($reg1['conhecimento']);
	}	
	
	$sql = "SELECT * FROM ".DATABASE.".rh_funcoes, ".DATABASE.".rh_escolaridade ";
	$sql .= "WHERE rh_funcoes.id_funcao = '".$cargo[0]."' ";
	$sql .= "AND rh_funcoes.reg_del = 0 ";
	$sql .= "AND rh_escolaridade.reg_del = 0 ";
	$sql .= "AND rh_funcoes.id_rh_escolaridade = rh_escolaridade.id_rh_escolaridade ";

	$db->select($sql,'MYSQL',true);

	$regTempoExperiencia = $db->array_select[0];
	
	$idiomas = '';
	
	foreach(explode(',', $reg['idiomas']) as $k => $idioma)
	{
		$idiomas .= "<br />&nbsp;&nbsp;".$arrIdiomas[$idioma].' '.$arrNiveisIdiomas[$reg['niveis_req'][$k]];
	}
	
	$html .= "<p><b>ESCOLARIDADE</b>: ".$regTempoExperiencia['escolaridade']."<br />";
	$html .= "<b>TEMPO EXPERIÊNCIA</b>: ".$regTempoExperiencia['experiencia']."<br />";
	$html .= "<b>REQUISITOS DO CARGO</b>: ".$requisitosCargo."<br />";
	$html .= "<b>IDIOMAS</b>: ".$idiomas;
	$html .= "</p>";
	
	$html .= "<p><b>EXPERIÊNCIAS, CONHECIMENTOS, HABILIDADES EM:</b>";
	$html .= "&nbsp;".maiusculas($reg['experiencia']).'</p>';
	
	$html .= "<p><b>ASPECTOS COMPORTAMENTAIS:</b>";
	$html .= "&nbsp;".maiusculas($reg['aspectos_comportamentais']).'</p>';
	
	$html .= "<p><b>REPORTE DIRETO PARA:</b>";
	$html .= "&nbsp;".maiusculas($reg['reporte_direto']).'</p>';
	
	$html .= "</p>";
	$html .= "<br /><br /><br /><br /><br /><br />";
	
	$html .= "<p align='center' style='border-top:solid;border-width:1px;width:250px;float:left;'><b>REQUISITANTE</b>: ".$reg['funcionario']."</p>";
	$html .= "<p align='center' style='border-top:solid;border-width:1px;width:250px;float:right;'><b>RECURSOS HUMANOS</b></p>";
	
	$html .= "<br /><br /><br /><br /><br />";
	
	$html .= "<p align='center' style='border-top:solid;border-width:1px;width:250px;float:left;'><b>DIRETOR FINANCEIRO</b></p>";
	$html .= "<p align='center' style='border-top:solid;border-width:1px;width:250px;float:right;'><b>DIRETOR ÁREA REQUISITANTE</b></p>";
		
	$mpdf = new mPDF('c');
	$mpdf->SetMargins(5, 5, 10);
	$mpdf->WriteHTML(utf8_encode($html));
	$arquivo = date('YmdHis').'.pdf';
	$mpdf->Output($arquivo, 'D');
	exit;
}

function atualizatabela($dados_form)
{
	$resposta = new xajaxResponse();
	
	$resposta->addAssign("solicitante","innerHTML", $_SESSION["nome_usuario"]);
	
	$db = new banco_dados();
	
	$sql = "SELECT *, (SELECT count(id_requisicao) totalAbertos FROM ".DATABASE.".rh_candidatos ";
	$sql .= "WHERE rh_candidatos.id_requisicao = requisicoes_pessoal.id_requisicao AND rh_candidatos.id_funcionario IS NULL AND aprovacao = 1 AND rh_candidatos.reg_del = 0) totalAbertos ";
	$sql .= "FROM ".DATABASE.".funcionarios, ".DATABASE.".status_requisicao, ".DATABASE.".requisicoes_pessoal ";
	$sql .= "LEFT JOIN ".DATABASE.".ordem_servico ON (requisicoes_pessoal.id_os = ordem_servico.id_os AND ordem_servico.reg_del = 0) ";
	$sql .= "WHERE requisicoes_pessoal.id_solicitante = funcionarios.id_funcionario ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	$sql .= "AND status_requisicao.reg_del = 0 ";
	$sql .= "AND requisicoes_pessoal.reg_del = 0 ";
	$sql .= "AND requisicoes_pessoal.ultimo_status = status_requisicao.id_status_requisicao ";
		
	if($dados_form["filtro"]!="")
	{
		if($dados_form["filtro"]!="-1")
		{
			$sql .= "AND status_requisicao.id_status_requisicao = '" . $dados_form["filtro"] . "' ";
		}
	}
	else
	{
		$sql .= "AND status_requisicao.id_status_requisicao = '2' "; //solicitado
	}
	
	$sql .= "GROUP BY requisicoes_pessoal.id_requisicao ";

	$db->select($sql,'MYSQL',true);
	
	$array_req = $db->array_select;

	$xml = new XMLWriter();
	$xml->openMemory();
	$xml->startElement('rows');
	
	$chars = array("'","\"",")","(","\\","/");

	foreach($array_req as $reg_requisicoes)
	{
		$sql = "SELECT * FROM ".DATABASE.".requisicao_x_status ";
		$sql .= "WHERE requisicao_x_status.id_requisicao = '".$reg_requisicoes["id_requisicao"]."' ";
		$sql .= "AND requisicao_x_status.reg_del = 0 ";
		$sql .= "ORDER BY id_requisicao_x_status DESC LIMIT 1 ";
		
		$db->select($sql,'MYSQL',true);
		
		$reg = $db->array_select[0];
	
		//verifica se existe OS
		if($reg_requisicoes["os"]=="")
		{
			if($reg_requisicoes["os_outros"]=="")
			{
			    $os = str_replace($chars, '', $reg_requisicoes["descricao"]);
			}
			else
			{
				$os = str_replace($chars, '', $reg_requisicoes["os_outros"]);
			}
		}
		else
		{
			$os = $reg_requisicoes["os"].' - '.$reg_requisicoes["descricao"];
		}
		
		$xml->startElement('row');
		$xml->writeAttribute('id', $reg_requisicoes["id_requisicao"]);
		$xml->writeElement('cell', sprintf("%05d",$reg_requisicoes["id_requisicao"]));
		$xml->writeElement('cell', $reg_requisicoes["status"]);
		$xml->writeElement('cell', mysql_php($reg["data_alteracao"]));
		$xml->writeElement('cell', $reg_requisicoes["funcionario"]);
		$xml->writeElement('cell', $os);
		
		$arrIntegracao = array('', 'NÃO', 'SIM');
		
		$xml->writeElement('cell', $arrIntegracao[$reg_requisicoes['integracao_cliente']]);
		
		if($reg_requisicoes["id_status_requisicao"]=="2")
		{
			$xml->writeElement('cell', '<span class="icone icone-excluir cursor" onclick=if(confirm("Deseja&nbsp;excluir?")){xajax_excluirVaga("'.$reg_requisicoes["id_requisicao"].'")};></span>');
		}
		else
		{
			$xml->writeElement('cell', '&nbsp;');
		}
		
		if ($reg_requisicoes['ultimo_status'] == 10 && $reg_requisicoes['totalAbertos'] > 0)
		{
			$xml->writeElement('cell', '<span class="icone icone-salvar cursor" onclick=if(confirm("Deseja&nbsp;exportar&nbsp;os&nbsp;candidatos&nbsp;aprovados&nbsp;para&nbsp;o&nbsp;SISTEMA?")){xajax_exportarAprovados("'.$reg_requisicoes['id_requisicao'].'")}; style="cursor:pointer;"></span>');
		}
		else
		{
			$xml->writeElement('cell', '&nbsp;');
		}
		
		$xml->endElement();
	}
	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('requisicoes', true, '120', '".$conteudo."');");

	return $resposta;
}

function atualizatabelaCandidatos($id)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados();
	
	$sql = "SELECT * FROM ".DATABASE.".rh_candidatos ";
	$sql .= "JOIN(
			  SELECT id_requisicao codReq, ultimo_status FROM ".DATABASE.".requisicoes_pessoal WHERE requisicoes_pessoal.reg_del = 0
			) req ON codReq = id_requisicao ";
	$sql .= "LEFT JOIN (
			  	SELECT
			  		id_rh_candidato candidato, tipo_contrato tipoContratoCandidato, data_inicio inicioCandidato, data_empresa dataEmpresa,
			  		salario_registro, salario_registro_tipo, salario_empresa, salario_empresa_tipo, salario_ajudacusto, salario_ajudacusto_tipo,
			  		salario_horaextra, salario_horaextra_tipo, in_transporte, in_refeicao, in_hotel, in_outros, fp_unibanco, fp_doc, fp_cheque,
			  		fp_moeda, observacoes observacoesCandidato, salario_horaextra_feriado, salario_horaextra_feriado_tipo,
			  		adicional_periculosidade, adicional_periculosidade_tipo
			  	FROM ".DATABASE.".financeiro_requisicoes WHERE financeiro_requisicoes.reg_del = 0
			  ) financeiro_requisicao
			  ON financeiro_requisicao.candidato = rh_candidatos.id_rh_candidato ";
	$sql .= "WHERE rh_candidatos.id_requisicao = '".$id."' ";
	$sql .= "AND rh_candidatos.reg_del = 0 ";
	$sql .= "ORDER BY rh_candidatos.nome ";

	$db->select($sql,'MYSQL',true);
	
	$xml = new XMLWriter();
	$xml->openMemory();
	$xml->startElement('rows');
	
	$aprovados = 0;
	
	foreach($db->array_select as $reg)
	{
		$xml->startElement('row');
		
		$status 	= '';
		
		if ($reg['ultimo_status'] != 10)
		{		
			$imgAprovar 	= $reg['candidato'] > 0 ? '<span class="icone icone-aprovar cursor" onclick=if(confirm("Deseja&nbsp;Aprovar?")){xajax_aprovarCandidato("'.$reg['id_rh_candidato'].'","'.intval($id).'")}; style="cursor:pointer;"></span>' : '';
			$imgDetalhes 	= '<span class="icone icone-detalhes cursor" onclick=mostraDetalhes("'.$reg["id_rh_candidato"].'","'.$id.'"); style="cursor:pointer;"></span>';
			$imgApagar 		= '<span class="icone icone-excluir cursor" onclick=if(confirm("Deseja&nbsp;Excluir?")){xajax_excluirCandidatos("'.$reg["id_rh_candidato"].'","'.intval($id).'")}; style="cursor:pointer;"></span>';
		}
		else
		{
			$imgAprovar 	= '&nbsp;';
			$imgDetalhes 	= '&nbsp;';
			$imgApagar 		= '&nbsp;';
		}
		
		if ($reg['aprovacao'] == 1)
		{
			$status = 'APROVADO';
			$imgAprovar = '';
			$aprovados++;
		}
		
		$xml->writeAttribute('id', $reg['id_rh_candidato']);
		$xml->writeElement('cell',$imgAprovar);
		$xml->writeElement('cell', $reg['nome']);
		$xml->writeElement('cell', $status);
		$xml->writeElement('cell', number_format($reg["valor"],2,",","."));
		$xml->writeElement('cell', $reg['observacoes']);
		$xml->writeElement('cell', $imgDetalhes);
		$xml->writeElement('cell', $imgApagar);
		$xml->endElement();
	}
	
	if ($aprovados == 0)
	{
		$resposta->addScript("$('#btnEnviarAprovados').hide();");
	}
	else
	{
		$resposta->addScript("$('#btnEnviarAprovados').show();");
		$resposta->addScript("$('#btnEnviarAprovados').css({'width':'auto','margin-top':'10px'});");
	}
	
	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('candidatos_div', true, '260', '".$conteudo."');");
		
	return $resposta;
}

function editar($id)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;

	//Seleciona os dados da requisição escolhida
	$sql = "SELECT 
				funcionario, requisicoes_pessoal.*, OS.descricao,
				GROUP_CONCAT(DISTINCT id_nivel) niveis_req, GROUP_CONCAT(id_infra) id_infra,
				GROUP_CONCAT(uso) infra_uso
			FROM 
				".DATABASE.".funcionarios, ".DATABASE.".requisicoes_pessoal ";
	$sql .= "LEFT JOIN (SELECT os, id_os codOs, descricao FROM ".DATABASE.".ordem_servico WHERE ordem_servico.reg_del = 0) os ON codOs = requisicoes_pessoal.id_os ";
	$sql .= "LEFT JOIN (SELECT id_requisicao nivel_req, id_nivel FROM ".DATABASE.".nivel_x_requisicao WHERE nivel_x_requisicao.reg_del = 0) nivel_idioma ON nivel_req = requisicoes_pessoal.id_requisicao ";
	$sql .= "LEFT JOIN (SELECT id_requisicao infra_req, id_infra FROM ".DATABASE.".infra_x_requisicao WHERE infra_x_requisicao.reg_del = 0) infra ON infra_req = requisicoes_pessoal.id_requisicao ";
	$sql .= "  LEFT JOIN (SELECT id_infra_estrutura, infra_estrutura, uso FROM ".DATABASE.".infra_estrutura WHERE infra_estrutura.reg_del = 0) infraExtrutura ON id_infra_estrutura = id_infra ";
	$sql .= "WHERE requisicoes_pessoal.id_solicitante = funcionarios.id_funcionario ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	$sql .= "AND requisicoes_pessoal.reg_del = 0 ";
	$sql .= "AND requisicoes_pessoal.id_requisicao = '" . $id . "' ";

	$db->select($sql,'MYSQL',true);

	$reg = $db->array_select[0];
	
	$resposta->addAssign("id_requisicao", "value", $id);
	
	$resposta->addScript("seleciona_combo(".$reg['ultimo_status'].",'status_alteracao'); ");
	
	/*if (in_array($reg['ultimo_status'], array(10)))
	{*/
		$resposta->addScript("$('#btn_financeiro_encerrar').show();");
		$resposta->addScript("$('#btnimprimir').show();");
		
		//Exibe a alteração de status abaixo dos botões de inserir, encerrar e imprimir vaga
		$resposta->addScript("xajax.$('divAlterarStatus').style.display='';");
		
	/*}
	else
	{
		$resposta->addScript("$('#btn_financeiro_encerrar').hide();");
		$resposta->addScript("$('#btnimprimir').hide();");
		$resposta->addScript("xajax.$('divAlterarStatus').style.display='none';");
	}*/

	//Atribue o número da requisição
	$resposta->addAssign("nr_requisicao","innerHTML","Nº " . sprintf("%05d",$reg["id_requisicao"]) . " ");	
	
	//Nome funcionário
	$resposta->addAssign("solicitante","innerHTML", $reg["funcionario"]);
	
	//Seleciona o radio do tipo de requisição
	$resposta->addScript("document.getElementsByName('tipo')[".$reg["tipo"]."].checked=true;");
	$resposta->addScript("document.getElementsByName('tipo')[".$reg["tipo"]."].onclick();");
	$resposta->addScript("document.getElementsByName('prazo')[".($reg["prazo"]-1)."].checked=true;");
	$resposta->addScript("document.getElementsByName('contrato')[".($reg["tipo_contrato"]-1)."].checked=true;");
	
	//Se for um outro motivo
	if($reg["motivo_outros"])
	{
		$resposta->addScript("mostra_outro(1,'motivo');");
		$resposta->addAssign("txt_motivo","value",$reg["motivo_outros"]);
	}
	else
	{
		$resposta->addScript("mostra_outro(0,'motivo');");
		$resposta->addScript("seleciona_combo('" . $reg["motivo"] . "', 'cmb_motivo'); ");
	}
	
	//necessidade de integração no cliente
	if($reg["integracao_cliente"] == 1)
	{
		$resposta->addAssign("integracao_cliente","checked",true);
		$resposta->addAssign("integracao_cliente2","checked",false);
	}
	else
	{
		$resposta->addAssign("integracao_cliente","checked",false);
		$resposta->addAssign("integracao_cliente2","checked",true);
	}
	
	
	//Traz o texto do Projeto
	if (intval($reg['id_os']) > 0)
	{
		$resposta->addAssign("projeto","value",$reg["descricao"]);
		$resposta->addScript("seleciona_combo('".$reg["id_os"]."', 'id_os'); ");
	}
	else
	{
		$resposta->addAssign("projeto","value",$reg["os_outros"]);
		$resposta->addScript("seleciona_combo('outro', 'id_os'); ");
	}
	
	//Se for um outro local
	if($reg["local_outros"])
	{
		$resposta->addScript("mostra_outro(1,'locais');");
		$resposta->addAssign("txt_locais","value",$reg["local_outros"]);
	}
	else
	{	
		$resposta->addScript("mostra_outro(0,'locais');");
		$resposta->addScript("seleciona_combo('".$reg["id_local"]."','locais'); ");
	}
	
	$infra    = explode(',', $reg['id_infra']);
	$infraUso = explode(',', $reg['infra_uso']);
	
	foreach($infra as $k => $infra)
	{	
		//Seleciona os checkbox da infra estrutura
		if ($infraUso[$k] == 2)
		{
			$resposta->addScript("seleciona_combo('".$infra."','infra_ti'); ");
		}
		else
		{ 
			if ($infraUso[$k] == 3)
			{
				$resposta->addScript("seleciona_combo('".$infra."','softwares_ti'); ");
			}
		}
	}
	
	//Assigna o valor informacoes_ti
	$resposta->addAssign("informacoes_ti","innerHTML",$reg["informacoes_ti"]);
	
	$niveis  = explode(',', $reg['niveis_req']);
	
	foreach($idiomas as $k => $idioma)
	{
		//Seleciona os checkboxes de idiomas
		$resposta->addScript("seleciona_checkbox('".$idioma."','idiomas'); ");
	}
	
	$resposta->addScript("xajax.$('nome_candidato').disabled=false; ");
	$resposta->addScript("xajax.$('btnaprovar_vaga').disabled=false; ");
	$resposta->addScript("xajax.$('valor_candidato').disabled=false; ");
	$resposta->addScript("xajax.$('observacoes_candidato').disabled=false; ");
	$resposta->addScript("xajax.$('btninserir_candidato').disabled=false; ");
	
	foreach($niveis as $k => $nivel)
	{
		//Seleciona os checkboxes de níveis de idiomas
		$resposta->addScript("seleciona_checkbox('".$nivel."','niveis'); ");
	}
	
	//Assigna o valor requisitos_cargo
	$resposta->addScript("xajax_preenche_escolaridade('".$reg["id_cargo"]."');");
	
	//Assigna os valores de experiência
	$resposta->addAssign("experiencia", "value", $reg["experiencia"]);
	$resposta->addAssign("aspectos_comportamentais", "value", $reg["aspectos_comportamentais"]);
	$resposta->addAssign("reporte_direto", "value", $reg["reporte_direto"]);
	$resposta->addAssign("nivel_atuacao", "value", $reg["nivel_atuacao"]);
	
	if (intval($reg['mobilizacao']) == 0)
	{
		$resposta->addScript("xajax.$('mobilizacao_dvm').checked=true");
		$resposta->addScript("xajax.$('mobilizacao_colaborador').checked=false");
		$resposta->addAssign("detalhes_mobilizacao", "innerHTML", $reg["detalhes_mobilizacao"]);
	}
	else if(intval($reg['mobilizacao']) == 1)
	{
		$resposta->addScript("xajax.$('mobilizacao_dvm').checked=false");
		$resposta->addScript("xajax.$('mobilizacao_colaborador').checked=true");
		$resposta->addAssign("detalhes_mobilizacao", "innerHTML", '');
	}
	else
	{
		$resposta->addScript("xajax.$('mobilizacao_dvm').checked=false");
		$resposta->addScript("xajax.$('mobilizacao_colaborador').checked=false");
		$resposta->addAssign("detalhes_mobilizacao", "innerHTML", '');
	}
	
	/*/Assigns necessários*/
	
	$resposta->addAssign("qtde_vagas", "value", $reg["qtde_vagas"]);
	$resposta->addAssign("tempo_servico", "value", $reg["tempo_servico"]);
	$resposta->addScript("seleciona_combo('".$reg['id_cargo']."','cargo'); ");
	$resposta->addScript("seleciona_combo('".$reg['categoria_contratacao']."','categoria_contratacao'); ");
	
	/*Desabilitando os campos do formulário*/
	//Chamado 1932: 
	//	$resposta->addScript("document.getElementsByName('tipo')[0].disabled=true;");
	//	$resposta->addScript("document.getElementsByName('tipo')[1].disabled=true;");
	
	$resposta->addScript("document.getElementsByName('prazo')[0].disabled=true;");
	$resposta->addScript("document.getElementsByName('prazo')[1].disabled=true;");
	$resposta->addScript("document.getElementsByName('prazo')[2].disabled=true;");
	
	$resposta->addScript("document.getElementsByName('contrato')[0].disabled=true;");
	$resposta->addScript("document.getElementsByName('contrato')[1].disabled=true;");
	
	$resposta->addScript("xajax.$('img_motivo').style.display='none';");
	$resposta->addScript("xajax.$('img_locais').style.display='none';");
	$resposta->addScript("xajax.$('img_os').style.display='none';");
	$resposta->addScript("xajax.$('img_categoria').style.display='none';");
	
	$resposta->addScript("xajax.$('btninserir').disabled=true;");
	$resposta->addScript("xajax.$('cmb_motivo').disabled=true;");
	$resposta->addScript("xajax.$('txt_motivo').disabled=true;");
	$resposta->addScript("xajax.$('id_os').disabled=true; ");
	$resposta->addScript("xajax.$('txt_os').disabled=true; ");
	$resposta->addScript("xajax.$('projeto').disabled=true; ");
	$resposta->addScript("xajax.$('locais').disabled=true; ");
	$resposta->addScript("xajax.$('txt_locais').disabled=true; ");
	$resposta->addScript("xajax.$('categoria_contratacao').disabled=true; ");
	$resposta->addScript("xajax.$('txt_categoria').disabled=true; ");
	$resposta->addScript("xajax.$('qtde_vagas').disabled=true; ");
	$resposta->addScript("xajax.$('tempo_servico').disabled=true; ");
	$resposta->addScript("xajax.$('cargo').disabled=true; ");
	$resposta->addScript("document.getElementsByName('infraestrutura')[0].disabled=true;");
	$resposta->addScript("document.getElementsByName('infraestrutura')[1].disabled=true;");
	$resposta->addScript("document.getElementsByName('infraestrutura')[2].disabled=true;");
	$resposta->addScript("document.getElementsByName('infraestrutura')[3].disabled=true;");
	$resposta->addScript("document.getElementsByName('infraestrutura')[4].disabled=true;");
	$resposta->addScript("document.getElementsByName('infraestrutura')[5].disabled=true;");
	$resposta->addScript("xajax.$('txt_infra').disabled=true; ");
	$resposta->addScript("xajax.$('informacoes_ti').disabled=true; ");
	
	$resposta->addScript("document.getElementsByName('idiomas')[0].disabled=true;");
	$resposta->addScript("document.getElementsByName('idiomas')[1].disabled=true;");
	$resposta->addScript("document.getElementsByName('idiomas')[2].disabled=true;");
	$resposta->addScript("document.getElementsByName('idiomas')[3].disabled=true;");
	$resposta->addScript("document.getElementsByName('idiomas')[4].disabled=true;");
	$resposta->addScript("document.getElementsByName('idiomas')[5].disabled=true;");
	
	$resposta->addScript("document.getElementsByName('niveis')[0].disabled=true;");
	$resposta->addScript("document.getElementsByName('niveis')[1].disabled=true;");
	$resposta->addScript("document.getElementsByName('niveis')[2].disabled=true;");
	$resposta->addScript("document.getElementsByName('niveis')[3].disabled=true;");

	$resposta->addScript("xajax.$('experiencia').disabled=true;");

	//Regras de negócios
	$resposta->addScript("xajax_atualizatabelaCandidatos('".$id."'); ");
	
	return $resposta;	
}

function excluirCandidatos($id_rh_candidato, $id_requisicao)
{
	$resposta = new xajaxResponse();

	$db = new banco_dados;
	
	//Seleciona o id do candidato aprovado para preencher o combo
	$sql = "SELECT id_rh_candidato FROM ".DATABASE.".rh_candidatos ";
	$sql .= "WHERE id_requisicao = '".$id_requisicao."' ";
	$sql .= "AND reg_del = 0 ";
	
	$db->select($sql,'MYSQL',true);
	
	$reg = $db->array_select[0];
	
	$usql = "UPDATE ".DATABASE.".rh_candidatos SET ";
	$usql .= "reg_del = 1, ";
	$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
	$usql .= "data_del = '".date('Y-m-d')."' ";
	$usql .= "WHERE rh_candidatos.id_rh_candidato = '" . $id_rh_candidato . "' ";

	$db->update($usql,'MYSQL');

	$resposta->addAlert("Candidato excluído com sucesso.");

	$resposta->addScript("xajax_atualizatabelaCandidatos('".$id_requisicao."'); ");

	$resposta->addAssign("btninserir_candidato","value","Inserir");
	$resposta->addScript("xajax.$('btnexcluir_candidato').disabled=true; ");		
	$resposta->addAssign("id_rh_candidato","value","");
	$resposta->addAssign("nome_candidato","value","");
	$resposta->addAssign("valor_candidato","value","");
	$resposta->addAssign("observacoes_candidato","value","");

	return $resposta;
}

function excluirVaga($id_requisicao)
{
	$resposta = new xajaxResponse();

	$db = new banco_dados;
	
	$usql = "UPDATE ".DATABASE.".requisicoes_pessoal SET ";
	$usql .= "reg_del = 1, ";
	$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
	$usql .= "data_del = '".date('Y-m-d')."' ";
	$usql .= "WHERE id_requisicao = '" . $id_requisicao . "' ";

	$db->update($usql,'MYSQL');

	$resposta->addAlert("Vaga excluída com sucesso.");

	$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm_pessoal'))); ");

	$resposta->addScriptCall("xajax_atualizatabela(xajax.getFormValues('frm_pessoal'));");
	
	$resposta->addScript('xajax_voltar()');

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

	return $resposta;
}

function insereCandidatos($dados_form)
{
	$resposta = new xajaxResponse();

	$db = new banco_dados();
	
	//Se a requisição estiver carregada (edição)
	if($dados_form["id_requisicao"])
	{
		//Se for uma edição de candidato
		if($dados_form["id_rh_candidato"])
		{
			$resposta->addScript("xajax.$('btnexcluir_candidato').disabled=true; ");
		
			$usql = "UPDATE ".DATABASE.".rh_candidatos SET ";
			$usql .= "nome = '" . maiusculas($dados_form["nome_candidato"]) . "', ";
			$usql .= "valor = '" . number_format(str_replace(",",".",str_replace(".","",$dados_form["valor_candidato"])),2,".","") . "', ";
			$usql .= "observacoes = '" . addslashes(maiusculas($dados_form["observacoes_candidato"])) . "' ";
			$usql .= "WHERE id_rh_candidato = '" . $dados_form["id_rh_candidato"] . "' ";
			
			$db->update($usql,'MYSQL');
	
			$resposta->addAlert("Candidato alterado com sucesso.");
		}
		else
		{
			//Se for uma inserção de candidato
			$isql = "INSERT INTO ".DATABASE.".rh_candidatos ";
			$isql .= "(id_requisicao, nome, valor, observacoes) ";
			$isql	.= "VALUES (";
			$isql .= "'" . $dados_form["id_requisicao"] . "', ";
			$isql .= "'" . maiusculas($dados_form["nome_candidato"]) . "', ";
			$isql .= "'" . number_format(str_replace(",",".",str_replace(".","",$dados_form["valor_candidato"])),2,".","") . "', ";
			$isql .= "'" . addslashes(maiusculas($dados_form["observacoes_candidato"])) . "') ";
			
			$db->insert($isql,'MYSQL');

			//ALTERA STATUS
			$usql = "UPDATE ".DATABASE.".requisicoes_pessoal SET ";
			$usql .= "ultimo_status = '5' "; //STATUS SELECIONANDO CANDIDATOS
			$usql .= "WHERE id_requisicao = '" . $dados_form["id_requisicao"] . "' ";
			$usql .= "AND reg_del = 0 ";
			
			$db->update($usql,'MYSQL');
	
			//Insere um novo status
			$isql = "INSERT INTO ".DATABASE.".requisicao_x_status ";
			$isql .= "(id_status_requisicao, id_requisicao, data_alteracao,id_funcionario) ";
			$isql .= "VALUES (";
			$isql .= "'5', "; //STATUS SELECIONANDO CANDIDATOS
			$isql .= "'".$dados_form["id_requisicao"]."', ";
			$isql .= "'".date("Y-m-d")."', ";
			$isql .= "'".$_SESSION["id_funcionario"]."') ";
			
			$db->insert($isql,'MYSQL');
	
			$resposta->addAlert("Candidato inserido com sucesso.");
		}

		$resposta->addAssign("btninserir_candidato","value","Inserir");
		$resposta->addAssign("id_rh_candidato","value","");
		$resposta->addAssign("nome_candidato","value","");
		$resposta->addAssign("valor_candidato","value","");
		$resposta->addAssign("observacoes_candidato","value","");
				
		//Atualiza a tabela de Candidatos
		$resposta->addScript("xajax_atualizatabelaCandidatos('".$dados_form["id_requisicao"]."'); ");
	}
	else
	{
		$resposta->addAlert("Ocorreu uma falha. A requisição não está carregada.");	
	}
	
	return $resposta;
}

function editarCandidatos($id)
{
	$resposta = new xajaxResponse();

	$db = new banco_dados();

	//Seleciona os dados do candidato
	$sql = "SELECT * FROM ".DATABASE.".rh_candidatos, ".DATABASE.".requisicoes_pessoal ";
	$sql .= "WHERE rh_candidatos.id_requisicao = requisicoes_pessoal.id_requisicao ";
	$sql .= "AND rh_candidatos.reg_del = 0 ";
	$sql .= "AND requisicoes_pessoal.reg_del = 0 ";
	$sql .= "AND rh_candidatos.id_rh_candidato = '" . $id . "' ";

	$db->select($sql,'MYSQL',true);

	$reg_editar = $db->array_select[0];

	$resposta->addAssign("btninserir_candidato","value","Atualizar");
	
	if($reg_editar["aprovacao"]=='1' || $reg_editar["enviado_coordenacao"]=='1')
	{
		$resposta->addScript("xajax.$('btnexcluir_candidato').disabled=true; ");
	}
	else
	{
		$resposta->addScript("xajax.$('btnexcluir_candidato').disabled=false; ");
	}
	
	$resposta->addAssign("id_rh_candidato","value", $id);
	$resposta->addAssign("nome_candidato","value",$reg_editar["nome"]);
	$resposta->addAssign("valor_candidato","value",number_format($reg_editar["valor"],2,",","."));
	$resposta->addAssign("observacoes_candidato","value",$reg_editar["observacoes"]);

	return $resposta;
}

function preencheDetalhes($id, $tpContrato, $idRequisicao)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados();
	
	$edicao_financeiro = 1;
	
	$imprimir = 0;
	
	$sql = "SELECT * FROM ".DATABASE.".rh_candidatos ";
	$sql .= "WHERE rh_candidatos.id_rh_candidato = '" . $id . "' ";
	$sql .= "AND reg_del = 0 ";

	$db->select($sql,'MYSQL',true);

	$reg_candidato = $db->array_select[0];

	//Pega o conteúdo gerado pelo formulário req_pessoal_financeiro.php, para inserir no innerHTML do div da janela de Detalhes
	$conteudo = file_get_contents(PROJETO."/rh/req_pessoal_financeiro.php?id_rh_candidato=".$id."&edicao=".$edicao_financeiro."&imprimir=".$imprimir."&tpContrato=".$tpContrato."&idRequisicao=".$idRequisicao);
	
	//Insere o conteúdo no div da janela de Detalhes
	$resposta->addAssign("divConteudo","innerHTML",$conteudo);

	// Seleciona as necessidades de equipamentos
	$sql = "SELECT * FROM ".DATABASE.".rh_necessidades_x_funcionario ";
	$sql .= "WHERE id_requisicao = '" . $id . "'";
	$sql .= "AND reg_del = 0 ";

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
	
	$resposta->addAssign("numero","innerHTML","Nº " . sprintf("%05d",$reg_candidato["id_requisicao"]) . " ");
	$resposta->addAssign("financeiro_nome","value",$reg_candidato["nome"]);
	$resposta->addAssign("financeiro_id_rh_candidato","value",$reg_candidato["id_rh_candidato"]);
	
	return $resposta;
}

function aprovarCandidato($id_rh_candidato, $id_requisicao)
{
	$resposta = new xajaxResponse();
	
	$db	= new banco_dados();
	
	$usql = "UPDATE ".DATABASE.".rh_candidatos SET ";	
	$usql .= "rh_candidatos.aprovacao = 1 ";
	$usql .= "WHERE rh_candidatos.id_rh_candidato = '".$id_rh_candidato."' ";
	$usql .= "AND reg_del = 0 ";

	$db->update($usql,'MYSQL');
	
	if ($db->erro != '')
	{
		$resposta->addAlert('Houve uma falha ao tentar aprovar o candidato! '.$db->erro);
	}
	else
	{
		$resposta->addAlert('Candidato Aprovado corretamente!');
	}

	$resposta->addScript("xajax_atualizatabelaCandidatos('".$id_requisicao."'); ");

	$resposta->addAssign("btninserir_candidato","value","Inserir");
	$resposta->addAssign("id_rh_candidato","value","");
	$resposta->addAssign("nome_candidato","value","");
	$resposta->addAssign("valor_candidato","value","");
	$resposta->addAssign("observacoes_candidato","value","");
	
	return $resposta;
}

function enviarAprovadosFinanceiro($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados();
	
	$candidatos = "";
		
	//Seta status do candidato
	$sql = "SELECT * FROM ".DATABASE.".rh_candidatos ";
	$sql .= "WHERE rh_candidatos.enviado_coordenacao = 1 ";
	$sql .= "AND rh_candidatos.id_requisicao = '".$dados_form["id_requisicao"]."' ";
	$sql .= "AND reg_del = 0 ";

	$db->select($sql,'MYSQL',true);

	foreach($db->array_select as $reg)
	{	
		if($dados_form["chk_".$reg["id_rh_candidato"]])
		{
			$candidatos .= "<p>".$reg["nome"]."</p>";
		}
	}
	
	$sql = "SELECT rh_funcoes.descricao FROM ".DATABASE.".rh_funcoes, ".DATABASE.".requisicoes_pessoal ";
	$sql .= "WHERE rh_funcoes.id_funcao = requisicoes_pessoal.id_cargo ";
	$sql .= "AND rh_funcoes.reg_del = 0 ";
	$sql .= "AND requisicoes_pessoal.reg_del = 0 ";
	$sql .= "AND requisicoes_pessoal.id_requisicao = '".$dados_form["id_requisicao"]."'";

	$db->select($sql,'MYSQL',true);

	$reg = $db->array_select[0];
	
	switch($dados_form["contrato"])
	{
		case 1: 
			$contrato = "PJ";
		break;
		
		case 2: 
			$contrato = "CLT";
		break;
	}

	//Manda e-mail com aviso ao Financeiro
	$mensagem = "<span style=\"font-family:Arial, Helvetica, sans-serif; font-size:11px; padding:0px; margin:0px;\"><p>Uma requisição foi enviada pelo RH:</P>";
	$mensagem .= "<P>Nº: " . sprintf("%05d",$dados_form["id_requisicao"]) . "</P>";
	$mensagem .= "<P>Data: " . date("d/m/Y") . "</P>";
	$mensagem .= "<P>Enviada por: " . $_SESSION["nome_usuario"] . "</P>";
	$mensagem .= "<P>Função: " . $reg_cargo["descricao"] . "</P>";
	$mensagem .= "<P>Tipo Contrato: " . $contrato . "</P>";
	$mensagem .= "<P>Candidatos aprovados pelo RH:</P>";
	$mensagem .= $candidatos;

	$params = array();
	$params['subject'] = "REQUISIÇÃO DE PESSOAL - CANDIDATOS SELECIONADOS RH";
		
	$mail = new email($params, 'requisicao_pessoal');
	$mail->montaCorpoEmail($mensagem);
	
	if(!$mail->Send())
	{
		$resposta->addAlert('Erro ao enviar e-mail!!! '.$mail->ErrorInfo);
	}
	else
	{
		$resposta->addAlert("Requisição enviada com sucesso para o Financeiro.");
	}
	
	return $resposta;
}

function atualizar($dados_form, $id_requisicao)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
				
	$sql = "SELECT * FROM ".DATABASE.".financeiro_requisicoes ";
	$sql .= "WHERE financeiro_requisicoes.id_rh_candidato = '" . $dados_form["financeiro_id_rh_candidato"] . "' ";
	$sql .= "AND reg_del = 0 ";

	$db->select($sql,'MYSQL',true);
	
	//Se existir um registro de candidato, atualiza
	if($db->numero_registros>0)
	{				
		//Atualiza os dados fornecidos pelo Financeiro
		$usql = "UPDATE ".DATABASE.".financeiro_requisicoes SET ";
		$usql .= "status = '" . $dados_form["financeiro_status"] . "', ";
		$usql .= "status_outro = '" . $dados_form["financeiro_status_outro"] . "', ";
		$usql .= "tipo_contrato = '" . $dados_form["financeiro_tipo_contrato"] . "', ";
		$usql .= "tipo_contrato_outro = '" . $dados_form["financeiro_tipo_contrato_outro"] . "', ";
		$usql .= "cargo = '" . $dados_form["financeiro_cargo"] . "', ";		
		$usql .= "data_inicio = '" . php_mysql($dados_form["financeiro_data_inicio"]) . "', ";
		
		if (isset($dados_form["financeiro_data_empresa"]))
		{
			$usql .= "data_empresa = '" . php_mysql($dados_form["financeiro_data_empresa"]) . "', ";
		}
		
		if (isset($dados_form["financeiro_registro"]))
		{
			$usql .= "salario_registro = '" . str_replace(",",".",str_replace(".","",$dados_form["financeiro_registro"])). "',  ";
			$usql .= "salario_registro_tipo = '" . $dados_form["financeiro_registro_tipo"] . "',  ";
		}
		
		if (isset($dados_form["financeiro_empresa"]))
		{
			$usql .= "salario_empresa = '" . str_replace(",",".",str_replace(".","",$dados_form["financeiro_empresa"])). "',  ";
			$usql .= "salario_empresa_tipo = '" . $dados_form["financeiro_empresa_tipo"] . "',  ";
		}
		
		$usql .= "salario_ajudacusto = '" . str_replace(",",".",str_replace(".","",$dados_form["financeiro_ajudacusto"])) . "',  ";
		$usql .= "salario_ajudacusto_tipo = '" . $dados_form["financeiro_ajudacusto_tipo"] . "',  ";
		$usql .= "salario_horaextra = '" . str_replace(",",".",str_replace(".","",$dados_form["financeiro_horaextra"])) . "',  ";
		$usql .= "salario_horaextra_tipo = '" . $dados_form["financeiro_horaextra_tipo"] . "',  ";
		
		/*Adicionado em 23/02/2015 para contemplar O TAP Cadastro Financeiro*/
		$usql .= "salario_horaextra_feriado = '" . str_replace(",",".",str_replace(".","",$dados_form["financeiro_horaextra_feriado"])) . "',  ";
		$usql .= "salario_horaextra_feriado_tipo = '" . $dados_form["financeiro_horaextra_feriado_tipo"] . "',  ";
		$usql .= "pj = " . intval($dados_form["tipoContratoFlag"]) . ", ";
		$usql .= "adicional_periculosidade = '" . str_replace(",",".",str_replace(".","",$dados_form["financeiro_periculosidade"])) . "',  ";
		$usql .= "adicional_periculosidade_tipo = '" . $dados_form["financeiro_periculosidade_tipo"] . "',  ";		
		$usql .= "in_transporte = '" . intval($dados_form["financeiro_chk_transporte"]) . "', ";
		$usql .= "in_refeicao = '" . intval($dados_form["financeiro_chk_refeicao"]) . "', ";
		$usql .= "in_hotel = '" . intval($dados_form["financeiro_chk_hotel"]) . "', ";
		$usql .= "in_outros = '" . intval($dados_form["financeiro_chk_outros"]) . "', ";
		$usql .= "id_local = '" . intval($dados_form["financeiro_local_trabalho"]) . "', ";
		$usql .= "fp_unibanco = '" . intval($dados_form["financeiro_chk_unibanco"]) . "', ";
		$usql .= "fp_doc = '" . intval($dados_form["financeiro_chk_doc"]) . "', ";
		$usql .= "fp_cheque = '" . intval($dados_form["financeiro_chk_cheque"]) . "', ";
		$usql .= "fp_moeda = '" . intval($dados_form["financeiro_chk_moeda"]) . "', ";
		$usql .= "observacoes = '" . addslashes(trim($dados_form["financeiro_observacoes"])) . "', ";
		$usql .= "atividade = '" . addslashes(trim($dados_form["txt_atividade"])) . "' ";
		$usql .= "WHERE financeiro_requisicoes.id_rh_candidato = '" . $dados_form["financeiro_id_rh_candidato"] . "' ";
		$usql .= "AND reg_del = 0 ";

		$db->update($usql,'MYSQL');
	}
	//Se não existir, insere
	else
	{
		//Insere o candidato
		$isql = "INSERT INTO ".DATABASE.".financeiro_requisicoes ";
		$isql .= "(id_rh_candidato, status, status_outro, tipo_contrato, ";
		$isql .= "tipo_contrato_outro, cargo, data_inicio, data_empresa, ";
		$isql .= "salario_registro, salario_registro_tipo, salario_empresa, salario_empresa_tipo, ";
		$isql .= "salario_ajudacusto, salario_ajudacusto_tipo, salario_horaextra, salario_horaextra_tipo, ";
		$isql .= "in_transporte, in_refeicao, in_hotel, in_outros, id_local, ";
		$isql .= "fp_unibanco, fp_doc, fp_cheque, fp_moeda, observacoes, atividade, adicional_periculosidade, adicional_periculosidade_tipo) VALUES ( ";
		$isql .= "'" . $dados_form["financeiro_id_rh_candidato"] . "', ";
		$isql .= "'" . $dados_form["financeiro_status"] . "', ";
		$isql .= "'" . $dados_form["financeiro_status_outro"] . "', ";
		$isql .= "'" . $dados_form["financeiro_tipo_contrato"] . "', ";
		$isql .= "'" . $dados_form["financeiro_tipo_contrato_outro"] . "', ";
		$isql .= "'" . $dados_form["financeiro_cargo"] . "', ";
		$isql .= "'" . php_mysql($dados_form["financeiro_data_inicio"]) . "', ";
		$isql .= "'" . php_mysql($dados_form["financeiro_data_empresa"]) . "', ";
		$isql .= "'" . str_replace(",",".",str_replace(".","",$dados_form["financeiro_registro"])) . "', ";
		$isql .= "'" . $dados_form["financeiro_registro_tipo"] . "', ";			
		$isql .= "'" . str_replace(",",".",str_replace(".","",$dados_form["financeiro_empresa"])) . "', ";
		$isql .= "'" . $dados_form["financeiro_empresa_tipo"] . "', ";
		$isql .= "'" . str_replace(",",".",str_replace(".","",$dados_form["financeiro_ajudacusto"])) . "', ";
		$isql .= "'" . $dados_form["financeiro_ajudacusto_tipo"] . "', ";
		$isql .= "'" . str_replace(",",".",str_replace(".","",$dados_form["financeiro_horaextra"])) . "', ";
		$isql .= "'" . intval($dados_form["financeiro_horaextra_tipo"]) . "', ";
		$isql .= "'" . intval($dados_form["financeiro_chk_transporte"]) . "', ";
		$isql .= "'" . intval($dados_form["financeiro_chk_refeicao"]) . "', ";
		$isql .= "'" . intval($dados_form["financeiro_chk_hotel"]) . "', ";
		$isql .= "'" . intval($dados_form["financeiro_chk_outros"]) . "', ";
		$isql .= "'" . intval($dados_form["financeiro_local_trabalho"]) . "', ";
		$isql .= "'" . intval($dados_form["financeiro_chk_unibanco"]) . "', ";
		$isql .= "'" . intval($dados_form["financeiro_chk_doc"]) . "', ";
		$isql .= "'" . intval($dados_form["financeiro_chk_cheque"]) . "', ";
		$isql .= "'" . intval($dados_form["financeiro_chk_moeda"]) . "', ";
		$isql .= "'" . addslashes(trim($dados_form["financeiro_observacoes"])) . "', ";
		$isql .= "'" . addslashes(trim($dados_form["txt_atividade"])) . "', ";
		$isql .= "'" . str_replace(",",".",str_replace(".","",$dados_form["financeiro_periculosidade"])) . "', ";
		$isql .= "'" . $dados_form["financeiro_periculosidade_tipo"] . "') ";

		$db->insert($isql,'MYSQL');
	}
	
	gravarNecessidadesCandidato($dados_form);
	
	$resposta->addAlert("Informações atualizadas com sucesso.");
	$resposta->addScript("divPopupInst.destroi(); ");	
	$resposta->addAlert("Dados atualizados com sucesso.");
	$resposta->addScript("xajax_atualizatabelaCandidatos('".$id_requisicao."'); ");
	
	return $resposta;
}

function gravarNecessidadesCandidato($dados_form)
{
	$idCandidato = $dados_form['financeiro_id_rh_candidato'];
	
	$idlocalTrabalho = $dados_form['financeiro_local_trabalho'];

	$erro = '';
	
	$db = new banco_dados();

	if ($idCandidato > 0)
	{

		$usql = "UPDATE ".DATABASE.".rh_necessidades_x_funcionario SET ";
		$usql .= "reg_del = 1, ";
		$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
		$usql .= "data_del = '".date('Y-m-d')."' ";
		$usql .= "WHERE id_requisicao = ".$idCandidato." ";

		$db->update($usql,'MYSQL');
		
		$erroBanco = $db->erro;
	}
	
	if (count($dados_form['infra_ti']) > 0 && $erroBanco == '')
	{
		$tipoNecessidade = 1;

		$sep = ', ';

		$isql = "INSERT INTO ".DATABASE.".rh_necessidades_x_funcionario VALUES ";
		
		foreach($dados_form['infra_ti'] as $equip)
		{
			$equip = intval($equip);
			$isql .= "(NULL, ".intval($equip).", ".$tipoNecessidade.", 0, NULL, NULL, NULL, NULL, ".$idCandidato.", ".$idlocalTrabalho.")".$sep;
		}

		if (count($dados_form['softwares_ti']) > 0)
		{
			$tipoNecessidade = 2;

			foreach($dados_form['softwares_ti'] as $soft)
			{
				$isql .= "(NULL, ".intval($soft).", ".$tipoNecessidade.", 0, NULL, NULL, NULL, NULL, ".$idCandidato.", ".$idlocalTrabalho.")".$sep;
			}

			if ($dados_form['outrosSoftwares'] != '')
			{
				$tipoNecessidade = 5;
				$texto = AntiInjection::clean($dados_form['outrosSoftwares']);
				$isql .= "(NULL, 0, ".$tipoNecessidade.", 0, NULL, NULL, '".$texto."', NULL, ".$idCandidato.", ".$idlocalTrabalho.")".$sep;
			}

			if ($dados_form['protheusModulos'] != '')
			{
				$tipoNecessidade = 3;
				$texto = AntiInjection::clean($dados_form['protheusModulos']);
				$isql .= "(NULL, 0, ".$tipoNecessidade.", 0, NULL, NULL, '".$texto."', NULL, ".$idCandidato.", ".$idlocalTrabalho.")".$sep;
			}

			if ($dados_form['dvmsysModulos'] != '')
			{
				$tipoNecessidade = 4;
				$texto = AntiInjection::clean($dados_form['dvmsysModulos']);
				$isql .= "(NULL, 0, ".$tipoNecessidade.", 0, NULL, NULL, '".$texto."', NULL, ".$idCandidato.", ".$idlocalTrabalho.")".$sep;
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
		$erro = 'Por favor, selecione uma opção de Equipamentos em Condiçães contratuais!';
	}

	return $erro;
}

function encerraVaga($dados_form)
{
	$resposta = new xajaxResponse();

	$db = new banco_dados;
	
	//Se a requisição estiver carregada (edição)
	if($dados_form["id_requisicao"])
	{
		//ALTERA STATUS
		$usql = "UPDATE ".DATABASE.".requisicoes_pessoal SET ";		
		$usql .= "ultimo_status = 10 ";		
		$usql .= "WHERE id_requisicao = '" . $dados_form["id_requisicao"] . "' ";
		$usql .= "AND reg_del = 0 ";
		
		$db->update($usql,'MYSQL');

		//Insere um novo status
		$isql = "INSERT INTO ".DATABASE.".requisicao_x_status ";
		$isql .= "(id_status_requisicao, id_requisicao, data_alteracao,id_funcionario) ";
		$isql .= "VALUES (";				
		$isql .= "'10', ";		
		$isql .= "'".$dados_form["id_requisicao"]."', ";
		$isql .= "'".date("Y-m-d")."', ";
		$isql .= "'".$_SESSION["id_funcionario"]."') ";
		
		$db->insert($isql,'MYSQL');

		//Manda e-mail com aviso do cancelamento ao RH
		$mensagem_rh = "<span style=\"font-family:Arial, Helvetica, sans-serif; font-size:11px; padding:0px; margin:0px;\"><P>A seguinte requisição foi encerrada:</P>";
		$mensagem_rh .= "<P>Nº: " . sprintf("%05d",$dados_form["id_requisicao"]) . "</P>";
		$mensagem_rh .= "<P>Data: " . date("d/m/Y") . "</P>";
		$mensagem_rh .= "<P>Solicitante: " . $_SESSION["nome_usuario"] . "</P>";
		
		$params = array();
		$params['subject'] = "REQUISIÇÃO DE PESSOAL - ENCERRAMENTO DE VAGA";

		$params['emails']['to'][] = array('email' => 'recursos_humanos@dominio.com.br', 'nome' => 'Recursos Humanos');
		
		$mail = new email($params);
		$mail->montaCorpoEmail($mensagem_rh);
				
		if(!$mail->Send())
		{
			$resposta->addAlert("Erro ao enviar e-mail!!! ".$mail->ErrorInfo);
		}
		
		$sql = "SELECT * FROM ".DATABASE.".requisicoes_pessoal, ".DATABASE.".usuarios, ".DATABASE.".funcionarios ";
		$sql .= "WHERE requisicoes_pessoal.id_requisicao = '".$dados_form["id_requisicao"]."' ";
		$sql .= "AND requisicoes_pessoal.reg_del = 0 ";
		$sql .= "AND usuarios.reg_del = 0 ";
		$sql .= "AND funcionarios.reg_del = 0 ";
		$sql .= "AND requisicoes_pessoal.id_solicitante = usuarios.id_funcionario ";
		$sql .= "AND usuarios.id_funcionario = funcionarios.id_funcionario ";

		$db->select($sql,'MYSQL',true);
		
		$regs = $db->array_select[0];
		
		//Manda e-mail com aviso do cancelamento ao solicitante / TI / Compras
		$mensagem = "<span style=\"font-family:Arial, Helvetica, sans-serif; font-size:11px; padding:0px; margin:0px;\"><P>A seguinte requisição foi encerrada:</P><BR>";
		$mensagem .= "<P>Nº: " . sprintf("%05d",$dados_form["id_requisicao"]) . "</P>";
		$mensagem .= "<P>Data: " . date("d/m/Y") . "</P>";
		$mensagem .= "<P>Solicitante: " . $regs["funcionario"] . "</P>";
		$mensagem .= "<P>Esta requisiÇÃo foi encerrada.</P>";
		
		$params = array();
		$params['subject'] = "REQUISIÇÃO DE PESSOAL - ENCERRAMENTO DE VAGA";
		
		$params['emails']['to'][] = array('email' => $regs["email"], 'nome' => $regs["funcionario"]);
		
		$mail = new email($params);
		$mail->montaCorpoEmail($mensagem);
		
		if(!$mail->Send())
		{
			$resposta->addAlert("Erro ao enviar e-mail!!! ".$mail->ErrorInfo);
		}
		
		$resposta->addScriptCall("xajax_atualizatabela(xajax.getFormValues('frm_pessoal'));");
		
		$resposta->addScript('xajax_voltar()');
			
		$resposta->addAlert("Vaga encerrada com sucesso.");
	}
	else
	{
		$resposta->addAlert("Ocorreu um erro. A requisição não está carregada.");
	
	}
	
	return $resposta;
}

function exportarAprovados($idRequisicao)
{
	$resposta = new xajaxResponse();
	
	$db	= new banco_dados();
	
	$erro = array();
	
	$exportados = array();
	
	$sql = "SELECT
			  *
			FROM ".DATABASE.".requisicoes_pessoal
			JOIN(
			  SELECT 
			  	id_requisicao as req, nome, valor, observacoes, id_rh_candidato, data_provavel_admissao,
			  	candidato, tipoContratoCandidato, inicioCandidato, dataEmpresa,
		  		salario_registro, salario_registro_tipo, salario_empresa, salario_empresa_tipo, salario_ajudacusto, salario_ajudacusto_tipo,
		  		salario_horaextra, salario_horaextra_tipo, in_transporte, in_refeicao, in_hotel, in_outros, fp_unibanco, fp_doc, fp_cheque,
		  		fp_moeda, observacoes observacoesCandidato, salario_horaextra_feriado, salario_horaextra_feriado_tipo,
		  		adicional_periculosidade, adicional_periculosidade_tipo
			  FROM ".DATABASE.".rh_candidatos
			  JOIN (
			  	SELECT 
			  		id_rh_candidato candidato, tipo_contrato tipoContratoCandidato, data_inicio inicioCandidato, data_empresa dataEmpresa,
			  		salario_registro, salario_registro_tipo, salario_empresa, salario_empresa_tipo, salario_ajudacusto, salario_ajudacusto_tipo,
			  		salario_horaextra, salario_horaextra_tipo, in_transporte, in_refeicao, in_hotel, in_outros, fp_unibanco, fp_doc, fp_cheque,
			  		fp_moeda, observacoes observacoesCandidato, salario_horaextra_feriado, salario_horaextra_feriado_tipo,
			  		adicional_periculosidade, adicional_periculosidade_tipo 
			  	FROM ".DATABASE.".financeiro_requisicoes WHERE financeiro_requisicoes.reg_del = 0
			  ) financeiro_requisicao
			  ON financeiro_requisicao.candidato = rh_candidatos.id_rh_candidato
			  WHERE
			  	reg_del = 0
			   AND id_requisicao = '".$idRequisicao."'
			  AND aprovacao = 1
			  AND id_funcionario IS NULL
			) candidatos_aprovados
			ON req = id_requisicao
			WHERE
			  ultimo_status = 10
			  AND reg_del = 0
			  AND id_requisicao = '".$idRequisicao."' ";
		
	$db->select($sql, 'MYSQL',true);
	
	$array_cand = $db->array_select;
	
	if ($db->numero_registros == 0)
	{
		$resposta->addAlert('Não foram encontrados candidatos aprovados nesta requisição!');
		
		return $resposta;
	}
	
	foreach ($array_cand as $reg)
	{
		if ($reg['id_cargo'] > 0)
		{
			$sql = "SELECT id_cargo_grupo FROM ".DATABASE.".rh_funcoes ";
			$sql .= "WHERE id_funcao = '".$reg['id_cargo']."'";
			$sql .= "AND reg_del = 0 ";
			
			$db->select($sql, 'MYSQL',true);
			
			$regCargo = $db->array_select[0];
			
			//Insere o funcionario no banco
			$isql = "INSERT INTO ".DATABASE.".funcionarios ";
			$isql .= "(id_setor, nivel_atuacao, Codcargo, id_cargo, id_setor_aso, funcionario, nome_usuario, email_particular, funcionario_endereco, funcionario_bairro, funcionario_cidade, ";
			$isql .= "funcionario_cep, funcionario_estado, filiacao_pai, filiacao_mae, nacionalidade_pai, ";
			$isql .= "nacionalidade_mae, ctps_num, ctps_serie, reservista_num, reservista_categoria, titulo_eleitor, titulo_zona, titulo_secao, identidade_num, ";
			$isql .= "identidade_emissor, data_emissao, cpf, naturalidade, id_nacionalidade, estado_nascimento, data_nascimento, id_empfunc, ";
			$isql .= "id_estado_civil, conjuge, id_escolaridade, clt_matricula, clt_admissao, id_categoria_funcional, id_tipo_pagamento, pis_data, pis_num, pis_banco, ";
			$isql .= "fgts_data, fgts_conta, fgts_banco, fgts_agencia, id_vinculo_empregaticio, id_tipo_admissao, id_turno_trabalho, ";
			$isql .= "horario_entrada, refeicao, horario_saida, descanso_semanal, cor, sexo, tipo_sanguineo, cabelo, olhos, altura, peso, id_local, ";
			$isql .= "celular, telefone, data_inicio, id_centro_custo, id_produto, item_contabil, id_cod_fornec, tipo_empresa, situacao, ref_transp_outros) ";
			$isql .= "VALUES (";
			$isql .= "'', ";
			$isql .= "'', ";
			$isql .= "'" . $reg["id_cargo"] . "', ";
			$isql .= "'" . $regCargo['id_cargo_grupo'] . "', ";
			$isql .= "'', ";
			$isql .= "'" . maiusculas($reg["nome"]) . "', ";
			$isql .= "'', "; //salva o login no nome_usuario
			$isql .= "'', ";
			$isql .= "'', ";
			$isql .= "'', ";
			$isql .= "'', ";
			$isql .= "'', ";
			$isql .= "'', ";
			$isql .= "'', ";
			$isql .= "'', ";
			$isql .= "'', ";
			$isql .= "'', ";
			$isql .= "'', ";
			$isql .= "'', ";
			$isql .= "'', ";
			$isql .= "'', ";
			$isql .= "'', ";
			$isql .= "'', ";
			$isql .= "'', ";
			$isql .= "'', ";
			$isql .= "'', ";
			$isql .= "'', ";
			$isql .= "'', ";
			$isql .= "'', ";
			$isql .= "'', ";
			$isql .= "'', ";
			$isql .= "'', ";
		
			$isql .= "'', ";
			$isql .= "'', ";
			$isql .= "'', ";
			$isql .= "'', ";
			$isql .= "'', ";
			$isql .= "'', ";
			$isql .= "'', ";
			$isql .= "'', ";
			$isql .= "'', ";
			$isql .= "'', ";
			$isql .= "'', ";
		
			$isql .= "'', ";
			$isql .= "'', ";
			$isql .= "'', ";
			$isql .= "'', ";
		
			$isql .= "'', ";
			$isql .= "'9B', ";//Outros / Reemprego
			$isql .= "'001', ";//08:00 - 17:00
			$isql .= "'8:00', ";
			$isql .= "'12:00 ÁS 13:00', ";
			$isql .= "'17:00', ";
			$isql .= "'SÁBADOS E DOMINGOS', ";
			$isql .= "'', ";
			$isql .= "'', ";
			$isql .= "'', ";
			$isql .= "'', ";
			$isql .= "'', ";
			$isql .= "'', ";
			$isql .= "'', ";
			$isql .= "'" . $reg["id_local"] . "', ";
			$isql .= "'', ";
			$isql .= "'', ";
			$isql .= "'" . $reg['inicioCandidato'] . "', ";
			$isql .= "'', ";
			$isql .= "'', ";
			$isql .= "'', ";
			$isql .= "'', ";
			$isql .= "'', ";
			$isql .= "'ATIVO', ";
			$isql .= "'') ";

			$db->insert($isql,'MYSQL');
			
			if ($db->erro != '')
			{
				$resposta->addAlert($db->erro);
				
				$erro[] = true;
			}
			else
			{
				$idNovo = $db->insert_id;
				
				$exportados[] = $idNovo.' - '.$reg['nome'];
				
				$usql = "UPDATE ".DATABASE.".rh_candidatos SET ";
				$usql .= "id_funcionario = '".$idNovo."' ";
				$usql .= "WHERE id_rh_candidato = '".$reg['id_rh_candidato']."' ";
				$usql .= "AND reg_del = 0 ";
				
				$db->update($usql, 'MYSQL');
				
				if ($reg['tipo_contrato'] == 1)
				{
					$tipo_contrato[1]['valor'] = 0;
					
					$legenda = 'SC';
					$tipo_contrato[2]['valor'] = $reg['salario_empresa'];
				}
				else
				{
					$tipo_contrato[1]['valor'] = $reg['salario_registro'];
					
					$legenda = 'CLT';
					$tipo_contrato[2]['valor'] = $reg['salario_empresa'];
				}
				
				//Insere o salario e tipo contrato
				$isql = "INSERT INTO ".DATABASE.".salarios ";
				$isql .= "(id_funcionario,  tipo_contrato, id_tipo_salario, salario_clt, salario_mensalista, salario_hora, data, id_func_altera, data_altera) ";
				$isql .= "VALUES (";
				$isql .= "'" . $idNovo . "', ";
				$isql .= "'" . $legenda . "', ";
				$isql .= "'', ";
				$isql .= "'" . $tipo_contrato[1]['valor'] . "', ";
				$isql .= "'" . $tipo_contrato[2]['valor'] . "', ";
				$isql .= "'" . 0 . "', ";
				$isql .= "'" . $reg["inicioCandidato"] . "', ";
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
				
				//Atualiza as necessidades dos funcionarios
				$usql = "UPDATE ".DATABASE.".rh_necessidades_x_funcionario SET ";
				$usql .= "id_funcionario = '" . $idNovo . "' ";
				$usql .= "WHERE id_requisicao = '".$reg['id_rh_candidato']."' ";//id_requisicao neste caso é o id_rh_candidato
				$usql .= "AND reg_del = 0 ";
				
				$db->update($usql,'MYSQL');

				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
				}
				
				$refeicao = $reg['in_refeicao'] == 1 ? 16 : 'null';//VR ou nada
				$transporte = $reg['in_transporte'] == 1 ? 19 : 'null';//VT  ou N�o
				$hotel = $reg['in_hotel'] == 1 ? 24 : 27;//Por conta da EMPRESA ou Não tem hotel
				
				//Grava cliente_exigencias, estes dados vem de inclusões na tarifa
				$isql = "INSERT INTO
							".DATABASE.".cliente_exigencias
							(
								id_funcionario, id_adicional_refeicao, id_adicional_transporte, id_adicional_hotel, id_local_trabalho,
								data_inicio, data_fim, numero_contrato_cliente, numero_os, centrocusto
							)
						VALUES(
							 '" . $idNovo . "',
							 " . $refeicao . ",
							 " . $transporte . ",
							 " . $hotel . ",
							 '" . $reg["id_local"] . "',
							 '" . $reg["inicioCandidato"] . "',
							 '',
							 '0',
							 '" . intval($reg['id_os']) . "',
						 	 '')";

				$db->insert($isql, 'MYSQL');
				
				if (intval($reg['salario_ajudacusto']) > 0)
				{
					$isql = "INSERT INTO
								".DATABASE.".funcionario_x_ajudacusto
							VALUES 
								(NULL, 
								".$idNovo.",
								17, 1, 2, ".$reg['salario_ajudacusto'].", '', ".$reg['id_local'].", ".intval($reg['id_os']).", 0, NULL, NULL, '".date('Y-m-d')."')";
					
					$db->insert($isql, 'MYSQL');
				}
			}
		}
		else
		{
			$resposta->addAlert('Esta requisição não possui um local válido!');
		}
	}
	
	if (count($erro) == 0)
	{
		$resposta->addAlert('Candidatos exportados corretamente:('.implode(", ", $exportados).')');
		$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm_pessoal')");
	}
	else
	{
		$resposta->addAlert('Ocorreram falhas na exportação dos candidatos!');
	}
	
	return $resposta;
}

function voltar()
{
	$resposta = new xajaxResponse();
	
	//Atribue o número da requisição
	$resposta->addAssign("nr_requisicao","innerHTML","");
	$resposta->addScript("xajax.$('divAlterarStatus').style.display='none';");
	$resposta->addAssign("id_requisicao","value","");
	$resposta->addScript("$('#btn_financeiro_encerrar').hide();");
	
	$resposta->addScript("xajax.$('btndirecao').disabled=true;");
	$resposta->addScript("xajax.$('btninserir_tarifa').disabled=true; ");
	$resposta->addScript("xajax.$('nome_candidato').disabled=true; ");
	$resposta->addScript("xajax.$('valor_candidato').disabled=true;");
	$resposta->addScript("xajax.$('observacoes_candidato').disabled=true;");
	$resposta->addScript("xajax.$('btninserir_candidato').disabled=true;");
	$resposta->addScript("xajax.$('btnexcluir_candidato').disabled=true;");
	$resposta->addScript("xajax.$('btnenviar_solicitante').disabled=true;");
	$resposta->addScript("xajax.$('btnenviar_financeiro').disabled=true;");
	$resposta->addScript("xajax.$('btnenviar_rh').disabled=true;");
	$resposta->addScript("xajax.$('btnvaga_encerrada').disabled=true;");
	$resposta->addAssign("solicitante","innerHTML", "");
	$resposta->addScript("xajax.$('frm_pessoal').reset();");
	$resposta->addScript("xajax_atualizatabelaCandidatos('','')");
	$resposta->addEvent("btnvoltar", "onclick", "javascript:history.back();");
	
	return $resposta;
}

function alterarStatus($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados();
	
	$idRequisicao = $dados_form['id_requisicao'];
	
	$statusAlteracao = $dados_form['status_alteracao'];
	
	$sql = "SELECT * FROM ".DATABASE.".requisicoes_pessoal, ".DATABASE.".usuarios, ".DATABASE.".funcionarios ";
	$sql .= "WHERE requisicoes_pessoal.id_requisicao = '".$idRequisicao."' ";
	$sql .= "AND requisicoes_pessoal.reg_del = 0 ";
	$sql .= "AND usuarios.reg_del = 0 ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	$sql .= "AND requisicoes_pessoal.id_solicitante = usuarios.id_funcionario ";
	$sql .= "AND usuarios.id_funcionario = funcionarios.id_funcionario ";

	$db->select($sql,'MYSQL',true);
	
	$regs = $db->array_select[0];
	
	//ALTERA STATUS
	$usql = "UPDATE ".DATABASE.".requisicoes_pessoal SET ";		
	$usql .= "ultimo_status = '".$statusAlteracao."' ";		
	$usql .= "WHERE id_requisicao = '" . $idRequisicao . "' ";
	$usql .= "AND reg_del = 0 ";
	
	$db->update($usql,'MYSQL');

	//Insere um novo status
	$isql = "INSERT INTO ".DATABASE.".requisicao_x_status ";
	$isql .= "(id_status_requisicao, id_requisicao, data_alteracao,id_funcionario) ";
	$isql .= "VALUES (";			
	$isql .= "'10', ";	
	$isql .= "'".$idRequisicao."', ";
	$isql .= "'".date("Y-m-d")."', ";
	$isql .= "'".$_SESSION["id_funcionario"]."') ";
	
	$db->insert($isql,'MYSQL');

	//Manda e-mail com aviso do cancelamento ao RH
	$mensagem = "<span style=\"font-family:Arial, Helvetica, sans-serif; font-size:11px; padding:0px; margin:0px;\"><P>A seguinte requisição foi alterada:</P>";
	$mensagem .= "<P>Nº: " . sprintf("%05d",$idRequisicao) . "</P>";
	$mensagem .= "<P>data: " . date("d/m/Y") . "</P>";
	$mensagem .= "<P>Autor da alteração: " . $_SESSION["nome_usuario"] . "</P>";
	
	$params = array();
	$params['subject'] = "REQUISIÇÃO DE PESSOAL - ALTERAÇÃO DE STATUS DE VAGA";
	
	$params['emails']['to'][] = array('email' => $regs["email"], 'nome' => $regs["funcionario"]);
	
	$sql = "SELECT * FROM ".DATABASE.".status_requisicao ";
	$sql .= "WHERE id_status_requisicao = ".$statusAlteracao." ";
	$sql .= "AND reg_del = 0 ";
	
	$db->select($sql, 'MYSQL', true);
	
	$mensagem .= "<P>Novo status: ".$db->array_select[0]['status'];
	
	$mail = new email($params, 'alteracao_status_requisicao');
	$mail->montaCorpoEmail($mensagem);
	
	if(!$mail->Send())
	{
		$resposta->addAlert("Erro ao enviar e-mail!!! ".$mail->ErrorInfo);
	}
	
	$resposta->addScriptCall("xajax_atualizatabela(xajax.getFormValues('frm_pessoal'));");
	
	$resposta->addScript('xajax_voltar()');
		
	$resposta->addAlert("Status alterado com sucesso.");
	
	return $resposta;
}

//Chamado 1932
function alterarTipoVaga($dados_form)
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();
	
	$usql = "UPDATE ".DATABASE.".requisicoes_pessoal SET ";		
	$usql .= "tipo = '".$dados_form['tipo']."' ";		
	$usql .= "WHERE id_requisicao = '" . $dados_form['id_requisicao'] . "' ";
	$usql .= "AND reg_del = 0 ";
	
	$db->update($usql,'MYSQL');
	
	if ($db->erro != '')
	{
		$resposta->addAlert('Houve uma falha ao tentar alterar o tipo de requisição');
	}
	else
	{
		$resposta->addAlert('Alteração realizada!');
	}
	
	return $resposta;
}

$xajax->registerFunction("atualizatabela");
$xajax->registerFunction("encerraVaga");
$xajax->registerFunction("exportarAprovados");
$xajax->registerFunction("atualizar");
$xajax->registerFunction("enviarAprovadosFinanceiro");
$xajax->registerFunction("gravarNecessidadesCandidato");
$xajax->registerFunction("atualizatabelaCandidatos");
$xajax->registerFunction("aprovarCandidato");
$xajax->registerFunction("editar");
$xajax->registerFunction("voltar");
$xajax->registerFunction("editarCandidatos");
$xajax->registerFunction("preenche_escolaridade");
$xajax->registerFunction("insereCandidatos");
$xajax->registerFunction("excluirCandidatos");
$xajax->registerFunction("preencheDetalhes");
$xajax->registerFunction("excluirVaga");
$xajax->registerFunction("alterarStatus");
$xajax->registerFunction("alterarTipoVaga");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","xajax_atualizatabela(xajax.getFormValues('frm_pessoal'));");

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script>
var perguntar = true;
function confirmar()
{
	if (perguntar && confirm('Deseja alterar o tipo de vaga?'))
	{
		return true;
	}
	else
	{
		perguntar = true;
	}

	return false;
}


function imprimir(idRequisicao)
{
	window.open('./requisicao_pessoal_adm.php?impressao=1&id_requisicao='+idRequisicao,"_blank");
}

function calculaPericulosidade(value)
{
	var salario = value;
	salario = salario.replace('.', '');
	salario = parseFloat(salario, 10);

	var periculosidade = parseFloat(salario * 30 / 100, 10);

	if (periculosidade > 0)
	{
		document.getElementById('financeiro_periculosidade').value = periculosidade;
		document.getElementById('financeiro_periculosidade').value = document.getElementById('financeiro_periculosidade').value.replace('.', ',');
	}
}

function tab()
{
	myTabbar = new dhtmlXTabBar("my_tabbar");
	
	myTabbar.enableAutoReSize(true);
}

function grid(tabela, autoh, height, xml)
{
	mygrid = new dhtmlXGridObject(tabela);

	mygrid.enableAutoHeight(autoh,height);
	mygrid.enableRowsHover(true,'cor_mouseover');
	
	switch(tabela)
	{
		default:

			function editar(id, col)
			{
				if (col <= 4)
				{
					perguntar = false;
					xajax_editar(id);
				}
			}
			
			mygrid.attachEvent("onRowSelect",editar);
		
			mygrid.setHeader("Nº,status,Data&nbsp;Alteração,Solicitante,OS/Projeto,Req.&nbsp;Cliente,D,S");
			mygrid.setInitWidths("60,*,100,*,*,80,50,50");
			mygrid.setColAlign("left,left,left,left,left,center,center,center");
			mygrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro");
			mygrid.setColSorting("str,str,str,str,str,str,str,str");			

		break;
		case 'candidatos_div':

			function editarCandidatos(id, col)
			{
				if (col <= 4 && col > 0)
					xajax_editarCandidatos(id);
			}
			
			mygrid.attachEvent("onRowSelect",editarCandidatos);
			
			mygrid.setHeader("A, Nome, Status, Valor, Observações, D, E");
			mygrid.setInitWidths("50,150,120,80,150,30,30");
			mygrid.setColAlign("center,left,left,left,left,center,center");
			mygrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro");
			mygrid.setColSorting("str,str,str,str,str,str,str,str");

		break;
	}
	
	mygrid.init();
	mygrid.loadXMLString(xml);
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

function seleciona_checkbox(valor,id_checkbox)
{
	checkbox = document.getElementsByName(id_checkbox);

	for(x=0;x<checkbox.length;x++)
	{
		if(checkbox.item(x).value==valor)
		{
			checkbox.item(x).checked = true;
		}
	}
}

function mostraDetalhes(id, id_requisicao)
{
	var html = '<div id="divConteudo"></div>';
	modal(html, '776_865', 'Preencha os detalhes do candidato');
	//document.getElementById('div_conteudo').style.zIndex = 2;
	tpContrato = document.getElementsByName('contrato')[1].checked == true ? 2 : 1;

	xajax_preencheDetalhes(id, tpContrato, id_requisicao);
}
</script>

<?php

//Popula a combo-box de OS
$sql = "SELECT * FROM ".DATABASE.".ordem_servico, ".DATABASE.".ordem_servico_status ";
$sql .= "WHERE ordem_servico.id_os_status = ordem_servico_status.id_os_status ";
$sql .= "AND ordem_servico.reg_del = 0 ";
$sql .= "AND ordem_servico_status.reg_del = 0 ";
$sql .= "AND ordem_servico_status.id_os_status IN (1,14,16) ";
$sql .= "ORDER BY ordem_servico.os ";

$db->select($sql,'MYSQL',true);

foreach ($db->array_select as $reg_os)
{
	$array_os_values[] = $reg_os["id_os"];
	$array_os_output[] = $reg_os["os"];
}

$smarty->assign("option_os_values",$array_os_values);
$smarty->assign("option_os_output",$array_os_output);

$sql = "SELECT * FROM ".DATABASE.".local ";
$sql .= "WHERE reg_del = 0 ";

$db->select($sql,'MYSQL',true);

$array_locais_values[] = '';
$array_locais_output[] = '';

foreach ($db->array_select as $regs)
{
	$array_locais_values[] = $regs["id_local"];
	$array_locais_output[] = $regs["descricao"];
}

$smarty->assign("option_locais_values",$array_locais_values);
$smarty->assign("option_locais_output",$array_locais_output);

$sql = "SELECT * FROM ".DATABASE.".rh_escolaridade  ";
$sql .= "WHERE reg_del = 0 ";
$sql .= "ORDER BY rh_escolaridade.escolaridade ";

$db->select($sql,'MYSQL',true);

foreach($db->array_select as $cont)
{
	$array_escolaridade_values[] = $cont["id_rh_escolaridade"];
	$array_escolaridade_output[] = $cont["escolaridade"];
}

$smarty->assign("option_escolaridade_values",$array_escolaridade_values);
$smarty->assign("option_escolaridade_output",$array_escolaridade_output);

$array_filtro_values[] = "-1";
$array_filtro_output[] = "TODAS";

$sql = "SELECT * FROM ".DATABASE.".status_requisicao ";
$sql .= "WHERE id_status_requisicao IN (1,2,5,10,11,12,13) ";
$sql .= "AND reg_del = 0 ";

$db->select($sql,'MYSQL',true);

foreach ($db->array_select as $cont)
{
	$array_filtro_values[] = $cont["id_status_requisicao"];
	$array_filtro_output[] = $cont["status"];
}

$smarty->assign("option_filtro_values",$array_filtro_values);
$smarty->assign("option_filtro_output",$array_filtro_output);

$sql = "SELECT rh_funcoes.id_funcao, rh_funcoes.descricao, id_cargo_grupo FROM ".DATABASE.".rh_funcoes ";
$sql .= "WHERE reg_del = 0 ";
$sql .= "ORDER BY rh_funcoes.id_funcao ";

$db->select($sql,'MYSQL',true);

foreach($db->array_select as $reg_cargo)
{
	$array_cargos_values[] = $reg_cargo["id_funcao"].','.$reg_cargo["id_cargo_grupo"];
	$array_cargos_output[] = substr($reg_cargo["descricao"],0,40);
}

$smarty->assign("option_cargos_values",$array_cargos_values);
$smarty->assign("option_cargos_output",$array_cargos_output);

//INFRAESTRUTURA TI
$sql = "SELECT * FROM ".DATABASE.".infra_estrutura ";
$sql .= "WHERE uso IN (1,2,3) ";
$sql .= "AND reg_del = 0 ";
$sql .= "ORDER BY infra_estrutura";

$array_infra_values = array();
$array_infra_output= array();

$array_softwares_values = array();
$array_softwares_output= array();

$db->select($sql,'MYSQL',true);

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
	}}

$smarty->assign("option_softwares_values",$array_softwares_values);
$smarty->assign("option_softwares_output",$array_softwares_output);

$smarty->assign("option_infra_values",$array_infra_values);
$smarty->assign("option_infra_output",$array_infra_output);

$smarty->assign("campo",$conf->campos('requisicao_pessoal_adm'));

$smarty->assign("revisao_documento", "V8");

$smarty->assign("disabled","false");

$smarty->assign("muda_tab",$muda_tab);

$smarty->assign("aba_comercial",$aba_comercial);

$smarty->assign("aba_rh",$aba_rh);

$smarty->assign("aba_aprovacao",$aba_aprovacao);

$smarty->assign("aba_financeiro",$aba_financeiro);

$smarty->assign("aba_candidatos",$aba_candidatos);

$smarty->assign("data_aprovacao",date("d/m/Y"));

$smarty->assign("classe",CSS_FILE);

$smarty->display('requisicao_pessoal_adm.tpl');
?>