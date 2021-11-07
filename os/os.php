<?php
/*
		Formulário de OS	
		
		Criado por Carlos Abreu
		
		local/Nome do arquivo:
		../os/os.php
		
		Versão 0 --> VERSÃO INICIAL
		Versão 1 --> ATUALIZAÇÃO LAYOUT (31/03/2006)
		Versão 2 --> ATUALIZAÇÃO LAYOUT / ROTINAS AJAX (26/03/2007)
		Versão 3 --> FUNÇÃO DE INSERÇÃO REMOVIDA (13/06/2007)
		Versão 4 --> Atualização Lay-out / Smarty : 30/06/2008
		Versão 5 --> Retirada de controles - atualização via protheus	
		Versão 6 --> Atualização de layout: 18/12/2014	
		Versão 7 --> atualização da classe banco de dados - 21/01/2015 - Carlos Abreu
		Versão 8 --> inclusão de titulos 1 e 2 para GED #1976 - 30/03/2015 - Carlos Abreu
		Versão 9 --> Adicionadas as funções anônimas para otimizaÇÃo dos recursos de processamento e Adicionadas as validações anti injection	
		Versão 10 --> atualização layout - Carlos Abreu - 31/03/2017
*/

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

require_once(INCLUDE_DIR."antiInjection.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(19))
{
	nao_permitido();
}

function voltar()
{
	$resposta = new xajaxResponse();
	
	$resposta->addScript("xajax.$('frm_os').reset(); ");
	
	$resposta->addAssign("btninserir","value","Inserir");
	
	$resposta->addEvent("btninserir","onclick","xajax_insere(xajax.getFormValues('frm_os')); ");
	
	$resposta->addEvent("btnvoltar", "onclick", "history.back();");

	return $resposta;

}

function insere($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	if($dados_form["os"]!='' || $dados_form["descricao"]!='' || $dados_form["cliente"]!='' || $dados_form["titulo_1"]!='')
	{
		$sql = "SELECT * FROM ".DATABASE.".ordem_servico, ".DATABASE.".empresas ";
		$sql .= "WHERE empresas.id_empresa = '".$dados_form["cliente"]."' ";
		$sql .= "AND ordem_servico.reg_del = 0 ";
		$sql .= "AND empresas.reg_del = 0 ";
		$sql .= "AND ordem_servico.os = '" . $dados_form["os"] . "' ";
		
		$db->select($sql, 'MYSQL',true);
		
		if ($db->erro != '')
		{
			$resposta->addAlert("Não foi possível executar a seleção.".$sql);
		}
		
		if($db->numero_registros > 0)
		{
			$resposta->addAlert("Ordem de Serviço já cadastrado");
		}
		else
		{
			$isql = "INSERT INTO ".DATABASE.".ordem_servico ";
			$isql .= "(ordem_servico_cliente, id_cod_coord, id_coord_aux, id_cod_resp, os, id_os_status, id_empresa, descricao, descricao_GED, titulo_1, titulo_2, data_inicio, data_fim, projeto_inicio, projeto_termino) ";
			$isql .= "VALUES ('" . maiusculas($dados_form["oscliente"]) . "', ";
			$isql .= "'" . $dados_form["coord"] . "', ";
			$isql .= "'" . $dados_form["coordaux"] . "', ";
			$isql .= "'" . $dados_form["coordcli"] . "', ";
			$isql .= "'" . maiusculas($dados_form["os"]) . "', ";
			$isql .= "'1', ";
			$isql .= "'" . $dados_form["cliente"] . "', ";
			$isql .= "'" . maiusculas($dados_form["descricao"]) . "', ";
			$isql .= "'" . maiusculas(tiraacentos($dados_form["descricao"])) . "', ";
			$isql .= "'" . maiusculas($dados_form["titulo_1"]) . "', ";
			$isql .= "'" . maiusculas($dados_form["titulo_2"]) . "', ";
			$isql .= "'" . php_mysql($dados_form["datainicio"]) . "', ";
			$isql .= "'" . php_mysql($dados_form["datafim"]) . "', ";
			$isql .= "'" . php_mysql($dados_form["datainicio"]) . "', ";
			$isql .= "'" . php_mysql($dados_form["datafim"]) . "') ";

			$registros = $db->insert($isql,'MYSQL');
			
			if ($db->erro != '')
			{
				$resposta->addAlert("Não foi possível a inserção dos dados".$isql);
			}

			$resposta->addScript("xajax_voltar('');");

			$resposta->addScript("xajax_atualizatabela('', '1');");

			$resposta->addAlert("Ordem de Serviço cadastrado com sucesso.");
		}

	}
	else
	{
		$resposta->addAlert("Os campos devem estar preenchidos.");
	}	
	
	return $resposta;
}

function atualizatabela($filtro, $combo='')
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	$sql_filtro = "";
	
	$sql_texto = "";
	
	if($combo=='')
	{
		if($filtro!="")
		{
			$array_valor = explode(" ",$filtro);
			
			for($x=0;$x<count($array_valor);$x++)
			{
				$array_valor[$x] = AntiInjection::clean($array_valor[$x]);
				$sql_texto .= "%" . $array_valor[$x] . "%";
			}
			
			$sql_filtro = " AND (ordem_servico.os LIKE '".$sql_texto."' ";
			$sql_filtro .= " OR empresas.empresa LIKE '".$sql_texto."' ";
			$sql_filtro .= " OR ordem_servico.descricao LIKE '".$sql_texto."') ";
		}
	}
	else
	{
		$sql_filtro .= " AND ordem_servico_status.id_os_status = '".$combo."' ";
	}

	$sql = "SELECT * FROM ".DATABASE.".unidades, ".DATABASE.".empresas, ".DATABASE.".funcionarios, ".DATABASE.".ordem_servico, ".DATABASE.".ordem_servico_status ";
	$sql .= "WHERE empresas.id_unidade = unidades.id_unidade ";
	$sql .= "AND ordem_servico.id_empresa = empresas.id_empresa ";
	$sql .= "AND ordem_servico.id_cod_coord = funcionarios.id_funcionario ";
	$sql .= "AND empresas.id_unidade = unidades.id_unidade ";
	$sql .= "AND ordem_servico.id_os_status = ordem_servico_status.id_os_status ";
	$sql .= $sql_filtro;
	
	$sql .= "GROUP BY ordem_servico.id_os ";
	$sql .= "ORDER BY ordem_servico.os DESC ";
	
	$db->select($sql,'MYSQL',true);
	
	if ($db->erro != '')
	{
		$resposta->addAlert("Não foi possível fazer a seleção." . $sql);
	}

	$conteudo = "";
	
	$xml = new XMLWriter();
	$xml->openMemory();
	$xml->setIndent(false);
	$xml->startElement('rows');
	
	foreach($db->array_select as $cont_desp)
	{
		$xml->startElement('row');
			$xml->writeAttribute('id', $cont_desp["id_os"]);
			$xml->writeElement('cell', sprintf("%05d",$cont_desp["os"]));
			$xml->writeElement('cell', $cont_desp["ordem_servico_cliente"]);
			$xml->writeElement('cell', $cont_desp["empresa"] ." - ".$cont_desp["unidade"]);
			$xml->writeElement('cell', $cont_desp["descricao"]);
			$xml->writeElement('cell', $cont_desp["funcionario"]);
			$xml->writeElement('cell', $cont_desp["os_status"]);
		$xml->endElement();	
	}
	
	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);

	$resposta->addScript("grid('os_tabela', true, '260', '".$conteudo."');");
	
	return $resposta;
}

function editar($id_os)
{
	$resposta = new xajaxResponse();

	$db = new banco_dados;

	$sql = "SELECT * FROM ".DATABASE.".ordem_servico ";
	$sql .= "WHERE ordem_servico.id_os = '" . $id_os . "' ";

	$db->select($sql,'MYSQL',true);
	
	if ($db->erro != '')
	{
		$resposta->addAlert("Erro na conexão com o banco de dados." . $sql);
	}

	if($db->numero_registros > 0)
	{
		$reg_os = $db->array_select[0];
		
		$resposta->addScript("seleciona_combo('" . $reg_os["id_empresa"] . "','cliente'); ");

		$resposta->addAssign("id_os","value",$reg_os["id_os"]);
		
		$resposta->addAssign("os","value",$reg_os["os"]);
		
		$resposta->addAssign("oscliente","value",$reg_os["ordem_servico_cliente"]);
		
		$resposta->addAssign("descricao","value",$reg_os["descricao"]);
		
		$resposta->addAssign("titulo_1","value",$reg_os["titulo_1"]);
		
		$resposta->addAssign("titulo_2","value",$reg_os["titulo_2"]);
		
		$resposta->addScript("xajax_preencheCombo('".$reg_os["id_empresa"]."','CONTATO','coordcli','".$reg_os["id_cod_resp"]."');");			
	
		$resposta->addScript("seleciona_combo('" . $reg_os["id_cod_coord"] . "', 'coord');");
		
		$resposta->addScript("seleciona_combo('" . $reg_os["id_coord_aux"] . "','coordaux');");

		$resposta->addAssign("datainicio","value",mysql_php($reg_os["data_inicio"]));

		$resposta->addAssign("datafim","value",mysql_php($reg_os["data_fim"]));
		
		//$resposta->addScript("document.getElementById('btninserir').disabled=false;");

		$resposta->addAssign("btninserir", "value", "Atualizar");
	
		$resposta->addEvent("btninserir", "onclick", "xajax_atualizar(xajax.getFormValues('frm'));");
		
		$resposta->addEvent("btnvoltar", "onclick", "xajax_voltar('');");
	}
	else
	{
		$resposta->addAlert("Não foi possível fazer a seleção. \nERRO: ".$sql_os);	
	}

	return $resposta;
}

function atualizar($dados_form)
{
	$resposta = new xajaxResponse();

	$db = new banco_dados;
	
	if(trim($dados_form["titulo_1"])=='')	
	{
		$resposta->addAlert('Favor preencher o Título 1.');	
	}
	else
	{
		$usql = "UPDATE ".DATABASE.".ordem_servico SET ";
		$usql .= "titulo_1 = '".maiusculas(trim(AntiInjection::clean($dados_form["titulo_1"])))."', ";
		$usql .= "titulo_2 = '".maiusculas(trim(AntiInjection::clean($dados_form["titulo_2"])))."', ";
		$usql .= "id_cod_resp = '" . $dados_form["coordcli"] . "' ";
		$usql .= "WHERE id_os = '" . $dados_form["id_os"] ."' ";
		
		$db->update($usql, 'MYSQL');
		
		if ($db->erro != '')
		{
			$resposta->addAlert("Não foi possível a atualização dos dados.".$usql);
		}
	
		$resposta->addScript("xajax_atualizatabela('','".$dados_form["exibir"]."');");		
	
		$resposta->addScript("xajax_voltar();");
		
		$resposta->addAlert("Ordem Serviço atualizada com sucesso.");
	}

	return $resposta;
}	

function preencheCombo($id, $nomecombo, $controle='', $selecionado='' )
{
	$resposta = new xajaxResponse();

	$db = new banco_dados;
	
	switch ($nomecombo)
	{
		case "DISCIPLINA":
			$sql = "SELECT id_atividade, descricao FROM ".DATABASE.".atividades ";
			$sql .= "WHERE atividades.cod = '" . $id . "' ";
			$sql .= "ORDER BY atividades.descricao ";
				 
			$db->select($sql,'MYSQL',true);
			
			if ($db->erro != '')
			{
				$resposta->addAlert("Não foi possível selecionar as atividades! " .$sql);
			}
			
			$resposta->addScript("combo_destino = document.getElementById('id_atividade');");
			
			$resposta->addScriptCall("limpa_combo('id_atividade')");		
			
			foreach($db->array_select as $reg_disciplina)
			{
				$resposta->addScript("combo_destino.options[combo_destino.length] = new Option('".$reg_disciplina["descricao"]."', '".$reg_disciplina["id_atividade"]."');");	
			}		
		
		break;
		
		case "CONTATO":
			$sql = "SELECT id_contato, nome_contato FROM ".DATABASE.".contatos ";
			$sql .= "WHERE contatos.id_empresa = '" . $id . "' ";
			$sql .= "ORDER BY contatos.nome_contato ";
				
			$db->select($sql,'MYSQL',true);
			
			if ($db->erro != '')
			{
				$resposta->addAlert("Não foi possível selecionar os contatos!");
			}
			
			foreach($db->array_select as $reg_contato)
			{				
				$matriz[$reg_contato["nome_contato"]] = $reg_contato["id_contato"];		
			}
			
			$resposta->addNewOptions($controle, $matriz, $selecionado);
			
		break;
	}	
	
	return $resposta;
}

function excluir($id, $empresa)
{
	$resposta = new xajaxResponse();
			
	$db = new banco_dados();
	
	$usql = "UPDATE ".DATABASE.".ordem_servico SET ";
	$usql .= "reg_del = 1, ";
	$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
	$usql .= "data_del = '".date('Y-m-d')."' ";
	$usql .= "WHERE ordem_servico.id_os = '".$id."' ";
	$usql .= "AND reg_del = 0 ";
	
	$db->update($usql,'MYSQL');
	
	if ($db->erro != '')
	{
		$resposta->addAlert("Erro ".$sql);
	}

	$resposta->addScript("xajax_atualizatabela('', '1');");
	
	$resposta->addAlert("Ordem de Serviço excluido com sucesso.");
	
	return $resposta;
}

$xajax->registerFunction("voltar");
$xajax->registerFunction("insere");
$xajax->registerFunction("editar");
$xajax->registerFunction("atualizar");
$xajax->registerFunction("excluir");
$xajax->registerFunction("atualizatabela");
$xajax->registerFunction("preencheCombo");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","xajax_atualizatabela('','1');");

$conf = new configs();

$array_cliente_values = NULL;
$array_cliente_output = NULL;

$array_coord_values = NULL;
$array_coord_output = NULL;

$array_status_values = NULL;
$array_status_output = NULL;

$array_site_values = NULL;
$array_site_output = NULL;

$array_os_raiz_values = NULL;
$array_os_raiz_output = NULL;

$array_cliente_values[] = "0";
$array_cliente_output[] = "SELECIONE";
	  
$sql = "SELECT id_empresa, empresa, descricao, unidade  FROM ".DATABASE.".empresas, ".DATABASE.".unidades ";
$sql .= "WHERE empresas.id_unidade = unidades.id_unidade ";
$sql .= "AND empresas.status = 'CLIENTE' ";
$sql .= "ORDER BY empresa ";

$db->select($sql,'MYSQL',true);

if ($db->erro != '')
{
	die("Não foi possível realizar a seleção.".$sql);
}

foreach($db->array_select as $regs)
{
	$array_cliente_values[] = $regs["id_empresa"];
	$array_cliente_output[] = $regs["empresa"] . " - " . $regs["descricao"] . " - " . $regs["unidade"];
}

$array_coord_values[] = "0";
$array_coord_output[] = "SELECIONE";

$array_coordca_values[] = "0";
$array_coordca_output[] = "SELECIONE";

$sql = "SELECT id_funcionario, funcionario  FROM ".DATABASE.".funcionarios ";
$sql .= "WHERE (funcionarios.nivel_atuacao = 'C' ";
$sql .= "OR funcionarios.nivel_atuacao = 'CA' ";
$sql .= "OR funcionarios.nivel_atuacao = 'D' ";
$sql .= "OR funcionarios.nivel_atuacao = 'S' ";
$sql .= "OR funcionarios.nivel_atuacao = 'G') ";
$sql .= "AND funcionarios.situacao NOT LIKE 'DESLIGADO' ";
$sql .= "ORDER BY funcionario ";

$db->select($sql,'MYSQL',true);

if ($db->erro != '')
{
	exit("Não foi possível realizar a seleção.".$sql);
}

foreach($db->array_select as $regs)
{
		$array_coord_values[] = $regs["id_funcionario"];
		$array_coord_output[] = $regs["funcionario"];
		
		$array_coordca_values[] = $regs["id_funcionario"];
		$array_coordca_output[] = $regs["funcionario"];
}

$sql = "SELECT id_os_status, os_status FROM ".DATABASE.".ordem_servico_status ";

/*
 * 13/05/2015
 * Esta e todas as outras funções anônimas do documento foram implementadas nesta data 
 * Na função $db->select abaixo, utilizo o terceiro parâmetro como sendo uma função anônima
 * As funções anônimas passadas em $db->select sempre receberão 2 parametros(pode-se usar qualquer nome que quiser, neste caso escolhi $regs e $i, pois o loop
 * original usava $regs):
 * 	- $regs -> São os registros de uma linha do resultado
 *  - $i -> O numero da iteração
 */
$db->select($sql,'MYSQL',true);

if ($db->erro != '')
{
	exit("Não foi possível realizar a seleção.".$sql);
}

foreach($db->array_select as $regs)
{
		$array_status_values[] = $regs["id_os_status"];
		$array_status_output[] = $regs["os_status"];
}

$smarty->assign("revisao_documento","V10");

$smarty->assign("campo",$conf->campos('os'));

$smarty->assign("option_cliente_values",$array_cliente_values);
$smarty->assign("option_cliente_output",$array_cliente_output);
$smarty->assign("option_coord_values",$array_coord_values);
$smarty->assign("option_coord_output",$array_coord_output);
$smarty->assign("option_coordca_values",$array_coordca_values);
$smarty->assign("option_coordca_output",$array_coordca_output);
$smarty->assign("option_status_values",$array_status_values);
$smarty->assign("option_status_output",$array_status_output);

$smarty->assign("nome_formulario","OS");

$smarty->assign("classe",CSS_FILE);

$smarty->assign("larguraTotal",1);

$smarty->display('os.tpl');

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script>

function grid(tabela, autoh, height, xml)
{
	mygrid = new dhtmlXGridObject(tabela);

	mygrid.enableAutoHeight(autoh,height);
	mygrid.enableRowsHover(true,'cor_mouseover');

	mygrid.setHeader("OS,OS Cliente,Cliente,Descrição,Coordenador,status");
	mygrid.setInitWidths("50,100,*,*,*,100");
	mygrid.setColAlign("center,center,left,left,left,left");
	mygrid.setColTypes("ro,ro,ro,ro,ro,ro");
	mygrid.setColSorting("str,str,str,str,str,str");

	mygrid.attachEvent("onRowSelect",'xajax_editar');
	
	mygrid.setSkin("dhx_skyblue");
    mygrid.enableMultiselect(true);
    mygrid.enableCollSpan(true);	
	mygrid.init();
	mygrid.loadXMLString(xml);
}

</script>
