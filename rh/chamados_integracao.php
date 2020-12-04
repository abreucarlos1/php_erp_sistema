<?php
/*
		Formulário de chamados de integração	
		
		Criado por Carlos Eduardo
		
		local/Nome do arquivo:
		../rh/chamados_integracao.php
		
		Versão 0 --> VERSÃO INICIAL - 18/07/2016
		Versão 1 --> Atualização layout - Carlos Abreu - 04/04/2017
		Versão 2 --> Inclusão dos campos reg_del nas consultas - 28/11/2017 - Carlos Abreu
		Versão 3 --> Layout responsivo - 05/02/2018 - Carlos Eduardo
		Versão 4 --> Retirada de obrigatoriedades - 15/03/2018 - Carlos Eduardo
		Versão 5 --> Adicionei a opção para salvar uma integração para vários funcionarios ao mesmo tempo - 05/04/2018 - Carlos Eduardo
*/	

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

require_once("../ti/models/chamados_integracao_model.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(572))
{
	nao_permitido();
}

$conf = new configs();

//Se o usuário tiver permissão para Editar(4) ou Excluir(2) poderá visualizar, editar e excluir qualquer chamado
DEFINE(__ADMIN_CHAMADO__, $conf->checa_permissao(4) || $conf->checa_permissao(2) ? 1 : 0);

function voltar()
{
	$resposta = new xajaxResponse();

	$resposta->addScriptCall("reset_campos('frm')");
	
	$resposta->addAssign("btninserir", "value", "Inserir");
	$resposta->addScript("document.getElementById('status').disabled=true");
	$resposta->addEvent("btninserir", "onclick", "xajax_insere(xajax.getFormValues('frm'));");
	$resposta->addScript("document.getElementById('trInteracao').style.display = 'none';");

	$resposta->addEvent("btnvoltar", "onclick", "history.back();");

	return $resposta;
}

function atualizatabela($filtro)
{
	$resposta = new xajaxResponse();

	$db = new banco_dados;

	$sql_filtro = "";
	
	$sql_texto = "";	
	
	if($filtro!="")
	{		
		$array_valor = explode(" ",$filtro);
		
		for($x=0;$x<count($array_valor);$x++)
		{
			$sql_texto .= "%" . $array_valor[$x] . "%";
		}
		
		$sql_filtro = " AND (ci_desc LIKE '".$sql_texto."' ";
		$sql_filtro .= "OR cis_desc LIKE '".$sql_texto."' ";
		$sql_filtro .= "OR funcAbertura LIKE '".$sql_texto."' ";
		$sql_filtro .= "OR empresa LIKE '".$sql_texto."' ";
		$sql_filtro .= "OR descEmp LIKE '".$sql_texto."' ";
		$sql_filtro .= "OR unidade LIKE '".$sql_texto."' ";
		$sql_filtro .= "OR func LIKE '".$sql_texto."') ";
		
		$sql_filtro = str_replace('%%', '%', $sql_filtro);
	}
	
	$clausulaFuncAbertura = '';
	if (!__ADMIN_CHAMADO__)
	{
		$clausulaFuncAbertura = 'AND id_funcionario = '.$_SESSION['id_funcionario'];
	}
	
	$sql = "SELECT
				*
			FROM
				".DATABASE.".chamados_integracao
				JOIN(
					SELECT cis_desc, cis_id FROM ".DATABASE.".chamados_integracao_status WHERE chamados_integracao_status.reg_del = 0
				) status
				ON cis_id = ci_cis_id
				JOIN(
					SELECT
						id_funcionario idFuncAbertura, funcionario funcAbertura
					FROM
						".DATABASE.".funcionarios
					WHERE
						funcionarios.reg_del = 0
						AND situacao = 'ATIVO' ".$clausulaFuncAbertura."
				) funcAbertura
				ON idFuncAbertura = ci_id_funcionario_abertura
				JOIN(
					SELECT id_funcionario idFunc, funcionario func FROM ".DATABASE.".funcionarios WHERE funcionarios.reg_del = 0 AND situacao = 'ATIVO'
				) func
				ON idFunc = ci_id_funcionario
				JOIN(
					SELECT id_empresa_erp, empresa, descricao descEmp, unidade
					FROM ".DATABASE.".empresas, ".DATABASE.".unidade
					WHERE empresas.id_unidade = unidades.id_unidade
					AND empresas.status = 'CLIENTE' AND empresas.reg_del = 0
				) cliente
				ON id_empresa_erp = ci_id_cliente
			WHERE
				chamados_integracao.reg_del = 0 ".$sql_filtro."
			ORDER BY
				empresa, ci_id";

	$db->select($sql,'MYSQL',true);

	$xml = new XMLWriter();
	$xml->openMemory();
	$xml->startElement('rows');
	
	$chars = array("'","\"",")","(","\\","/");
	
	foreach($db->array_select as $cont_desp)
	{
		$xml->startElement('row');
		$xml->writeAttribute('id', $cont_desp['ci_id']);
		$xml->writeElement('cell', $cont_desp['ci_id']);
		$xml->writeElement('cell', $cont_desp['empresa'].' '.$cont_desp['descEmp'].' '.$cont_desp['unidade']);
		$xml->writeElement('cell', str_replace($chars, '', $cont_desp['ci_desc']));
		$xml->writeElement('cell', $cont_desp['ci_data'] != '0000-00-00' ? mysql_php($cont_desp['ci_data']) : '');
		$xml->writeElement('cell', $cont_desp['funcAbertura']);
		$xml->writeElement('cell', $cont_desp['func']);
		$xml->writeElement('cell', $cont_desp['cis_desc']);
		
		if(__ADMIN_CHAMADO__ && !in_array($cont_desp['cis_id'], array(5)))//5 - Chamado encerrado
			$xml->writeElement('cell', '<img src="'.DIR_IMAGENS.'apagar.png" style="cursor:pointer;" onclick=if(confirm("Deseja&nbsp;excluir&nbsp;este&nbsp;registro?")){xajax_excluir("'.$cont_desp['ci_id'].'");}; >');
		else
			$xml->writeElement('cell', '');
			
		$xml->endElement();	
	}
	
	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('div_lista', true, '330', '".$conteudo."');");
	
	return $resposta;
}

function insere($dados_form)
{
	$resposta = new xajaxResponse();
		
	$chamadoModel = new chamados_integracao_model();
	$retorno = $chamadoModel->inserir($dados_form);
	
	if ($retorno[0])
	{
		$resposta->addScript("xajax_atualizatabela('');");
		$resposta->addScript("xajax_voltar();");
		$resposta->addAlert("Registro realizado corretamente!");	
	}
	else
	{
		$resposta->addAlert($retorno[1]);	
	}	

	return $resposta;
}

function editar($id)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
		
	$sql = "SELECT * FROM ".DATABASE.".chamados_integracao ";
	$sql .= "WHERE ci_id = '".$id."' ";
	$sql .= "AND reg_del = 0 ";
	
	$db->select($sql,'MYSQL',true);

	$regs = $db->array_select[0];
	
	$resposta->addAssign("id_chamado", "value",$id);
	$resposta->addAssign("cliente", "value",$regs["ci_id_cliente"]);
	$resposta->addAssign("status", "value",$regs["ci_cis_id"]);
	$resposta->addScript("document.getElementById('status').disabled=false");
	$resposta->addAssign("funcionario", "value",$regs["ci_id_funcionario"]);
	$resposta->addAssign("descricao_integracao", "value",$regs["ci_desc"]);
	$resposta->addAssign("data", "value",$regs['ci_data'] != '0000-00-00' ? mysql_php($regs["ci_data"]) : '');
	$resposta->addAssign("btninserir", "value", "Atualizar");
	
	if (!in_array($regs['ci_cis_id'], array('5')))
		$atualizar = "xajax_atualizar(xajax.getFormValues('frm'));";
	else
		$atualizar = "alert('ATENÇÃO: Este chamado já foi encerrado e NÃO pode ser editado');";

	$resposta->addScript("document.getElementById('trInteracao').style.display = '';");	
		
	$resposta->addEvent("btninserir", "onclick", $atualizar);
	$resposta->addEvent("btnvoltar", "onclick", "xajax_voltar();");
	
	return $resposta;
}

function atualizar($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;

	if(!empty($dados_form["cliente"]) && !empty($dados_form["funcionario"]) && !empty($dados_form["descricao_integracao"]))
	{
		//Regras de data mínima para integração no cliente
		$data = $dados_form['data'];
		
		$usql = "UPDATE ".DATABASE.".chamados_integracao ";
		$usql .= "SET ci_id_cliente = ".$dados_form['cliente'].", ";
		$usql .= "ci_id_funcionario = ".$dados_form['funcionario'][0].", ";
		$usql .= "ci_data = '".php_mysql($dados_form['data'])."', ";
		$usql .= "ci_desc = '".maiusculas($dados_form['descricao_integracao'])."', ";
		$usql .= "ci_cis_id = ".$dados_form['status']." ";
		$usql .= "WHERE ci_id = ".$dados_form['id_chamado']." ";
		$usql .= "AND reg_del = 0 ";
		
		$db->update($usql,'MYSQL');
		
		if ($db->erro != '')
		{
			$resposta->addAlert("Houve uma falha ao tentar atualizar o registro! ".$db->erro);
			return $resposta;	
		}
		else
		{
			//Inserindo a interação
			$isql = "INSERT INTO ".DATABASE.".chamados_integracao_interacoes ";
			$isql .= "(cii_ci_id, cii_desc, cii_cis_id, cii_id_funcionario, cii_data) ";
			$isql .= "VALUES ( ";
			$isql .= $dados_form['id_chamado'].", ";
			$isql .= "'".maiusculas($dados_form['descricao_interacao'])."', ";
			$isql .= $dados_form['status'].", ";
			$isql .= $_SESSION['id_funcionario'].", ";
			$isql .= "'".date('Y-m-d')."') ";
			
			$db->insert($isql,'MYSQL');
		}
		
		//email
		$params 			= array();
		$params['from']		= "recrutamento@dominio.com.br";
		$params['from_name']= "RECURSOS HUMANOS";
		$params['subject'] 	= "ALTERAÇÃO NO CHAMADO ".$dados_form['id_chamado'];

		$corpo = "<b>Houve uma alteração no chamado de integração Nº ".$dados_form['id_chamado']."</b><br /><br />";
		
		$sql = 
			"SELECT
				DISTINCT funcionario, email, id_funcionario, CASE WHEN id_funcionario = ci_id_funcionario THEN '*' ELSE '' END func
			FROM
				".DATABASE.".funcionarios
				JOIN(
					SELECT email, id_funcionario id_funcionario FROM ".DATABASE.".usuarios WHERE usuarios.reg_del = 0 
				) usuario
				ON id_funcionario = id_funcionario
				JOIN ".DATABASE.".chamados_integracao ci ON ci.reg_del = 0 AND ci_id = ".$dados_form['id_chamado']."
			WHERE
				funcionarios.reg_del = 0
				AND situacao = 'ATIVO'
				AND id_funcionario IN (ci_id_funcionario, ci_id_funcionario_abertura)";
		
		$db->select($sql, 'MYSQL', true);
		
		foreach($db->array_select as $func)
		{
			$params['emails']['to'][] = array('email' => $func['email'], 'nome' => $func['funcionario']);
			
			if ($func['func'] == '*')
				$corpo .= "<b>Funcionário:</b> ".$func['funcionario'].".<br />";
			else if ($func['id_funcionario'] == $func['ci_id_funcionario_abertura'])
				$corpo .= "<b>Solicitante:</b> ".$func['funcionario'].".<br />";
		}
		
		$sql = "SELECT empresa, id_empresa_erp, descricao, unidade FROM ".DATABASE.".empresas, ".DATABASE.".unidade ";
		$sql .= "WHERE empresas.id_unidade = unidades.id_unidade ";
		$sql .= "AND empresas.reg_del = 0 ";
		$sql .= "AND unidades.reg_del = 0 ";
		$sql .= "AND id_empresa_erp = ".$dados_form['cliente'];
		
		$db->select($sql, 'MYSQL', true);
		
		$corpo .= "<b>Cliente</b>: ".$db->array_select[0]['empresa'].' '.$db->array_select[0]['descricao'].' '.$db->array_select[0]['unidade'].'<br />';
		
		$corpo .= "<b>data</b>: ".$dados_form['data']."<br />";
		$corpo .= "<b>Descrição</b>: ".maiusculas($dados_form['descricao_integracao']).'<br /><br /><hr />';
		$corpo .= "<b>Interação do chamado</b>: ".maiusculas($dados_form['descricao_interacao']).'<br />';
		
		$sql = "SELECT
			cis_desc
		FROM
			".DATABASE.".chamados_integracao_status
		WHERE
			chamados_integracao_status.reg_del = 0 
			AND chamados_integracao_status.cis_id = ".$dados_form['status'];
		
		$db->select($sql, 'MYSQL', true);
		
		$corpo .= "<b>status</b>: ".$db->array_select[0]['cis_desc'].'<br />';
		
		$mail = new email($params, 'chamados_integracao_cliente');
		$mail->montaCorpoEmail($corpo);
		$mail->Send();
					
		$resposta->addScript("xajax_atualizatabela('');");
		$resposta->addScript("xajax_voltar();");
		$resposta->addAlert("Registro realizado corretamente!");
	}
	else
	{
		$resposta->addAlert("Registro já existente no banco de dados");			
	}

	return $resposta;
}

function excluir($id)
{
	$resposta = new xajaxResponse();

	$db = new banco_dados();
			
	$usql = "UPDATE ".DATABASE.".chamados_integracao ";
	$usql .= "SET reg_del = 1, ";
	$usql .= "reg_who = '".$_SESSION['id_funcionario']."', ";
	$usql .= "data_del = '".date('Y-m-d')."' ";
	$usql .= "WHERE ci_id = '".$id."' ";
	$usql .= "AND reg_del = 0 ";
	
	$db->update($usql,'MYSQL');

	if ($db->erro != '')
	{
		$resposta->addAlert("Houve uma falha ao tentar excluir o registro ".$db->erro);
		return $resposta;
	}
	else
	{
		$resposta->addScript("xajax_atualizatabela('');");
		$resposta->addAlert("Registro excluido com sucesso.");
	}
	
	return $resposta;
}

/**
 * Função que busca os colaboradores já integrados no cliente
 * @param array $dados_form
 */
function getFuncionariosIntegrados($dados_form)
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();
	
	$idCliente = $dados_form['cliente'];
	$idFuncionario = implode(',', $dados_form['funcionario']);
	
	if (empty($idCliente) || empty($idFuncionario))
	{
		return $resposta;
	}

	//Pega o cargo do funcionário
	$sql = "SELECT id_funcao FROM ".DATABASE.".funcionarios ";
	$sql .= "WHERE id_funcionario IN(".$idFuncionario.") ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	
	$db->select($sql, 'MYSQL', true);
	
	//Busca a lista de chamados
	$sql = 
		"SELECT
		ci_id_funcionario, funcionario, ci_data, date_add(ci_data, INTERVAL 1 YEAR) validade
		FROM
		".DATABASE.".chamados_integracao
		JOIN(
			SELECT
				id_funcionario, funcionario
			FROM
				".DATABASE.".funcionarios
			WHERE
				funcionarios.reg_del = 0 
				AND situacao = 'ATIVO'
				AND id_funcao = ".$db->array_select[0]['id_funcao']."
		) func
		ON id_funcionario = ci_id_funcionario
		WHERE
		chamados_integracao.reg_del = 0
		AND chamados_integracao.ci_id_cliente = ".$idCliente."
		AND date_add(ci_data, INTERVAL 1 YEAR) >= NOW()
		AND chamados_integracao.ci_cis_id = 5";
	
	$xml = new XMLWriter();
	$xml->openMemory();
	$xml->startElement('rows');
	
	$db->select($sql, 'MYSQL', true);
	
	if ($db->numero_registros == 0)
		return $resposta;
	
	foreach($db->array_select as $reg)
	{
		$xml->startElement('row');
			$xml->writeAttribute('id', $reg['ci_id_funcionario']);
			$xml->writeElement('cell', $reg['funcionario']);
			$xml->writeElement('cell', mysql_php($reg['validade']));
		$xml->endElement();	
	};
	
	$html .= '<label class="labels">Atenção os seguintes colaboradores já possuem integração no cliente</label><br />';
	$html .= '<div id="div_lista_integrados"></div>';
	$resposta->addScript("modal('".$html."', 'p', 'COLABORADORES JÁ INTEGRADOS NO CLIENTE');");
	
	$xml->endElement();
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('div_lista_integrados', true, '260', '".$conteudo."');");
	return $resposta;
}

$xajax->registerFunction("voltar");
$xajax->registerFunction("insere");
$xajax->registerFunction("editar");
$xajax->registerFunction("atualizar");
$xajax->registerFunction("excluir");
$xajax->registerFunction("atualizatabela");
$xajax->registerFunction("getFuncionariosIntegrados");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","xajax_atualizatabela('');");
?>
<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script language="javascript">

	function grid(tabela, autoh, height, xml)
	{
		mygrid = new dhtmlXGridObject(tabela);

		mygrid.enableAutoHeight(autoh,height);
		mygrid.enableRowsHover(true,'cor_mouseover');

		switch(tabela)
		{
			case 'div_lista': 
				mygrid.setHeader("ID, Cliente, Descrição, Data, Requisitante, Funcionário, Status, D");
				mygrid.setInitWidths("35,*,*,70,200,200,180,35");
				mygrid.setColAlign("left,left,left,left,left,left,left,center");
				mygrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro");
				mygrid.setColSorting("str,str,str,str,str,str,str,str");
			
				function editar(id, col)
				{
					if (col <= 6)
						xajax_editar(id);
				}
				
				mygrid.attachEvent("onRowSelect",editar);
			break;
			case 'div_lista_integrados':
				mygrid.setHeader("Funcionário, Validade");
				mygrid.setInitWidths("*,65");
				mygrid.setColAlign("left,left");
				mygrid.setColTypes("ro,ro");
				mygrid.setColSorting("str,str");
			break;
		}
	
		mygrid.setSkin("dhx_skyblue");
		mygrid.enableMultiselect(true);
		mygrid.enableCollSpan(true);
		mygrid.init();
		mygrid.loadXMLString(xml);
	}
</script>

<?php
$array_cliente_values[] = "0";
$array_cliente_output[] = "SELECIONE";
	  
$sql = "SELECT empresa, id_empresa_erp, descricao, unidade FROM ".DATABASE.".empresas, ".DATABASE.".unidade ";
$sql .= "WHERE empresas.id_unidade = unidades.id_unidade ";
$sql .= "AND empresas.reg_del = 0 ";
$sql .= "AND unidades.reg_del = 0 ";
$sql .= "AND empresas.status = 'CLIENTE' ";
$sql .= "ORDER BY empresa ";

$db->select($sql,'MYSQL',true);

if ($db->erro != '')
{
	exit("Não foi possível realizar a seleção.".$sql);
}

foreach($db->array_select as $regs)
{
	$array_cliente_values[] = $regs["id_empresa_erp"];
	$array_cliente_output[] = $regs["empresa"] . " - " . $regs["descricao"] . " - " . $regs["unidade"];
}

$smarty->assign("option_cliente_values",$array_cliente_values);
$smarty->assign("option_cliente_output",$array_cliente_output);

$array_func_values[] = "0";
$array_func_output[] = "SELECIONE";
	  
$sql = "SELECT id_funcionario, funcionario FROM ".DATABASE.".funcionarios ";
$sql .= "WHERE situacao = 'ATIVO' ";
$sql .= "AND funcionarios.reg_del = 0 ";
$sql .= "ORDER BY funcionario ";

$db->select($sql,'MYSQL',true);

if ($db->erro != '')
{
	exit("Não foi possível realizar a seleção.".$sql);
}

foreach($db->array_select as $regs)
{
	$array_func_values[] = $regs["id_funcionario"];
	$array_func_output[] = $regs["funcionario"];	
}

$smarty->assign("option_func_values",$array_func_values);
$smarty->assign("option_func_output",$array_func_output);

$array_status_values[] = "0";
$array_status_output[] = "SELECIONE";
	  
$sql = "SELECT * FROM ".DATABASE.".chamados_integracao_status ";
$sql .= "WHERE chamados_integracao_status.reg_del = 0 ";

$db->select($sql,'MYSQL',true);

if ($db->erro != '')
{
	exit("Não foi possível realizar a seleção.".$sql);
}

foreach($db->array_select as $regs)
{
	$array_status_values[] = $regs["cis_id"];
	$array_status_output[] = $regs["cis_desc"];
}

$smarty->assign("option_status_values",$array_status_values);
$smarty->assign("option_status_output",$array_status_output);

$smarty->assign('admin', __ADMIN_CHAMADO__);

$smarty->assign('campo', $conf->campos('chamado_integracao_cliente'));

$smarty->assign('revisao_documento', 'V5');

$smarty->assign('larguraTotal', 1);

$smarty->assign("classe",CSS_FILE);

$smarty->display('chamados_integracao.tpl');

?>