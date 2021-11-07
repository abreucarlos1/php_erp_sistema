<?php
/*
		Formulário de habilitação	
		
		Criado por Carlos Abreu
		
		local/Nome do arquivo:
		../rh/habilitacao.php
		
		Versão 0 --> VERSÃO INICIAL - 28/01/2008
		Versao 1 --> Atualização Lay-out : 12/08/2008
		Versão 2 --> Atualização classe banco de dados - 23/01/2015 - Carlos Abreu
		Versão 3 --> Atualização - 09/04/2015 - Carlos
		Versão 4 --> Atualização imagens - 12/07/2016 - Carlos Abreu
		Versão 5 --> Criação do Relatório de CNH - 16/08/2016 - Carlos
		Versão 6 --> Atualização layout - Carlos Abreu - 07/04/2017
		Versão 7 --> Inclusão dos campos reg_del nas consultas - 28/11/2017 - Carlos Abreu
*/
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(206) && !verifica_sub_modulo(227))
{
	nao_permitido();
}

$conf = new configs();

function voltar()
{
	$resposta = new xajaxResponse();

	$resposta->addScriptCall("reset_campos('frm_habilitacaos')");
	
	$resposta->addAssign("data_emissao", "value", "");
	
	$resposta->addAssign("data_vencimento", "value", "");
	
	$resposta->addAssign("numero_habilitacao", "value", "");
	
	$resposta->addAssign("categoria", "value", "");
	
	$resposta->addAssign("btninserir", "value", "Inserir");
	
	$resposta->addEvent("btninserir", "onclick", "xajax_insere(xajax.getFormValues('frm_habilitacao'));");
	
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
		
		$sql_filtro = " AND (funcionarios.funcionario LIKE '".$sql_texto."' ";
		$sql_filtro .= " OR rh_habilitacao.numero_habilitacao LIKE '".$sql_texto."') ";	
	}
	
	$sql = "SELECT * FROM ".DATABASE.".funcionarios, ".DATABASE.".rh_habilitacao ";
	$sql .= "WHERE rh_habilitacao.id_funcionario = funcionarios.id_funcionario ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	$sql .= "AND rh_habilitacao.reg_del = 0 ";
	$sql .= "AND funcionarios.situacao NOT IN ('DESLIGADO','CANCELADO')  ";
	$sql .= $sql_filtro;
	$sql .= "ORDER BY funcionarios.funcionario, rh_habilitacao.data_vencimento ";	

	$db->select($sql,'MYSQL',true);

	$xml = new XMLWriter();
	$xml->openMemory();
	$xml->startElement('rows');
	
	foreach($db->array_select as $cont_desp)
	{
		$xml->startElement('row');
		$xml->writeAttribute('id', $cont_desp['id_habilitacao']);
		$xml->writeElement('cell', $cont_desp['funcionario']);
		$xml->writeElement('cell', $cont_desp['numero_habilitacao']);
		$xml->writeElement('cell', $cont_desp['categoria']);
		$xml->writeElement('cell', mysql_php($cont_desp["data_emissao"]));
		$xml->writeElement('cell', mysql_php($cont_desp["data_vencimento"]));
		$xml->writeElement('cell', '<img style="cursor:pointer;" onclick=if(confirm("Deseja excluir?")){xajax_excluir("'.$cont_desp['id_habilitacao'].'")}; src="'.DIR_IMAGENS.'apagar.png">');
		$xml->endElement();
	}

	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('habilitacao', true, '360', '".$conteudo."');");
	
	return $resposta;
}

function insere($dados_form)
{
	$resposta = new xajaxResponse();

	$db = new banco_dados;
	
	if($dados_form["funcionario"]!='' && $dados_form["numero_habilitacao"]!='' && $dados_form["data_emissao"]!='' && $dados_form["data_vencimento"]!='' && $dados_form["categoria"]!='')
	{
		$isql = "INSERT INTO ".DATABASE.".rh_habilitacao ";
		$isql .= "(id_funcionario,numero_habilitacao, categoria, data_emissao, data_vencimento) ";
		$isql .= "VALUES ('" . $dados_form["funcionario"] . "', ";		
		$isql .= "'" . $dados_form["numero_habilitacao"] . "', ";
		$isql .= "'" . maiusculas($dados_form["categoria"]) . "', ";
		$isql .= "'" . php_mysql($dados_form["data_emissao"]) . "', ";		
		$isql .= "'" . php_mysql($dados_form["data_vencimento"]) . "') ";

		$db->insert($isql,'MYSQL');
		
		$resposta->addScript("xajax_atualizatabela('');");
		
		$resposta->addAlert("CNH cadastrado com sucesso.");			

	}
	else
	{
		$resposta->addAlert("Os campos devem estar preenchidos.");
	}
	
	$resposta->addScript('xajax_voltar();');	

	return $resposta;
}

function editar($id)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	$sql = "SELECT * FROM ".DATABASE.".rh_habilitacao ";
	$sql .= "WHERE rh_habilitacao.id_habilitacao = '".$id."' ";
	$sql .= "AND reg_del = 0 ";
	
	$db->select($sql,'MYSQL',true);

	$regs = $db->array_select[0];		

	$resposta->addAssign("id_habilitacao", "value",$id);
	
	$resposta->addScript("seleciona_combo(".$regs["id_funcionario"].",'funcionario');");
	
	$resposta->addAssign("numero_habilitacao", "value",$regs["numero_habilitacao"]);
	
	$resposta->addAssign("categoria", "value",$regs["categoria"]);
	
	$resposta->addAssign("data_emissao", "value",mysql_php($regs["data_emissao"]));
	
	$resposta->addAssign("data_vencimento", "value",mysql_php($regs["data_vencimento"]));
	
	$resposta->addAssign("btninserir", "value", "Atualizar");

	$resposta->addEvent("btninserir", "onclick", "xajax_atualizar(xajax.getFormValues('frm_habilitacao'));");
	
	$resposta->addEvent("btnvoltar", "onclick", "xajax_voltar();");
	
	return $resposta;
}

function atualizar($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	if($dados_form["funcionario"]!='' && $dados_form["numero_habilitacao"]!='' && $dados_form["data_emissao"]!='' && $dados_form["data_vencimento"]!='' && $dados_form["categoria"]!='')
	{
		$usql = "UPDATE ".DATABASE.".rh_habilitacao SET ";
		$usql .= "id_funcionario = '" . $dados_form["funcionario"] . "', ";
		$usql .= "numero_habilitacao = '" . $dados_form["numero_habilitacao"] . "', ";
		$usql .= "categoria = '" . maiusculas($dados_form["categoria"]) . "', ";
		$usql .= "data_emissao = '" . php_mysql($dados_form["data_emissao"]) . "', ";		
		
		if(php_mysql($dados_form["data_vencimento"])>date("Y-m-d"))
		{
			$usql .= "vencimento = '0', ";
		}
		else
		{
			$usql .= "vencimento = '1', ";
		}
		
		$usql .= "data_vencimento = '" . php_mysql($dados_form["data_vencimento"]) . "' ";
		$usql .= "WHERE id_habilitacao = '".$dados_form["id_habilitacao"]."' ";
		$usql .= "AND reg_del = 0 ";

		$db->update($usql,'MYSQL');
		
		$resposta->addScript("xajax_atualizatabela('');");
		
		$resposta->addScript("xajax_voltar();");
	
		$resposta->addAlert("CNH atualizado com sucesso.");			

	}
	else
	{
		$resposta->addAlert("Os campos devem estar preenchidos.");
	
	}
	
	return $resposta;
}

function excluir($id, $what)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	$usql = "UPDATE ".DATABASE.".rh_habilitacao SET ";
	$usql .= "reg_del = 1, ";
	$usql .= "reg_who = ".$_SESSION['id_funcionario'].", ";
	$usql .= "data_del = '".date('Y-m-d')."' ";
	$usql .= "WHERE rh_habilitacao.id_habilitacao = '".$id."' ";
	
	$db->update($usql,'MYSQL');

	$resposta->addScript("xajax_atualizatabela('');");
	
	$resposta->addAlert($what . " excluido com sucesso.");
		
	return $resposta;
}

$xajax->registerFunction("voltar");
$xajax->registerFunction("insere");
$xajax->registerFunction("editar");
$xajax->registerFunction("atualizar");
$xajax->registerFunction("excluir");
$xajax->registerFunction("atualizatabela");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","xajax_atualizatabela('');");

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script>

function grid(tabela, autoh, height, xml)
{
	mygrid = new dhtmlXGridObject(tabela);

	mygrid.enableAutoHeight(autoh,height);
	mygrid.enableRowsHover(true,'cor_mouseover');

	mygrid.setHeader("Funcionário, Nº CNH, Categoria, Data da Emissão, Data do Vencimento,D");
	mygrid.setInitWidths("*,80,80,80,80,50");
	mygrid.setColAlign("left,center,center,center,center,center,center");
	mygrid.setColTypes("ro,ro,ro,ro,ro,ro,ro");
	mygrid.setColSorting("str,str,str,str,str,data,str");

	function editar(id, col)
	{
		if (col <= 4)
			xajax_editar(id);
	}
	
	mygrid.attachEvent("onRowSelect",editar);

	mygrid.setSkin("dhx_skyblue");
	mygrid.enableMultiselect(true);
	mygrid.enableCollSpan(true);
	mygrid.init();
	mygrid.loadXMLString(xml);
}
</script>
<?php

$array_funcionario_values = NULL;
$array_funcionario_output = NULL;

$array_funcionario_values[] = "";
$array_funcionario_output[] = "SELECIONE";

$sql = "SELECT * FROM ".DATABASE.".funcionarios  ";
$sql .= "WHERE funcionarios.situacao NOT IN('DESLIGADO','CANCELADO') ";
$sql .= "AND funcionarios.reg_del = 0 ";
$sql .= "ORDER BY funcionarios.funcionario ";

$db->select($sql,'MYSQL',true);

foreach ($db->array_select as $cont)
{
	$array_funcionario_values[] = $cont["id_funcionario"];
	$array_funcionario_output[] = $cont["funcionario"];
}

$smarty->assign("option_funcionario_values",$array_funcionario_values);
$smarty->assign("option_funcionario_output",$array_funcionario_output);

$smarty->assign('campo', $conf->campos('habilitacao'));

$smarty->assign('revisao_documento', 'V7');

$smarty->assign("classe",CSS_FILE);

$smarty->display('habilitacao.tpl');
?>