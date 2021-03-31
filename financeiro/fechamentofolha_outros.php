<?php
/*
		Formulário de Detalhamento do "Outros" do Fechamento da Folha	
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		../financeiro/fechamentofolha_outros.php
		
		Versão 0 --> VERSÃO INICIAL - 16/03/2006
		Versão 1 --> Atualização classe banco - 21/01/2015 - Carlos Abreu
		Versão 2 --> Atualização da interface - 13/07/2016 - Carlos Abreu 
		Versão 3 --> atualização layout - Carlos Abreu - 27/03/2017
		Versão 4 --> Inclusão dos campos reg_del nas consultas - 20/11/2017 - Carlos Abreu
*/
	
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//$PHP_SELF = $_SERVER['PHP_SELF'];
//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(308))
{
	nao_permitido();
}

function voltar()
{
	$resposta = new xajaxResponse();

	$resposta->addAssign("descricao", "value", "");
	
	$resposta->addAssign("valor", "value", "0");
	
	$resposta->addAssign("btninserir", "value", "Inserir");
	
	$resposta->addEvent("btninserir", "onclick", "xajax_insere(xajax.getFormValues('frm'));");
	
	$resposta->addEvent("btnvoltar", "onclick", "envia_valore();");

	return $resposta;

}

function atualizatabela($dados_form)
{
	$resposta = new xajaxResponse();

	$db = new banco_dados();
	
	$xml = new XMLWriter();
	
	$valor_total = 0;

	$sql = "SELECT * FROM ".DATABASE.".fechamento_folha_detalhes ";
	$sql .= "WHERE fechamento_folha_detalhes.id_funcionario = '" . $dados_form["id_funcionario"] . "' ";
	$sql .= "AND fechamento_folha_detalhes.reg_del = 0 "; 
	$sql .= "AND fechamento_folha_detalhes.data_ini = '" . php_mysql($dados_form["data_ini"]) . "' ";
	$sql .= "AND fechamento_folha_detalhes.data_fim = '" . php_mysql($dados_form["data_fim"]) . "' ";
	$sql .= "AND fechamento_folha_detalhes.tipo = '" . $dados_form["tipo"] . "' ";

	$db->select($sql, 'MYSQL', true);
	
	if ($db->erro != '')
	{
		$resposta->addAlert("Não foi possível a seleção dos dados.".$sql);
	}
		
	$xml->openMemory();
	$xml->setIndent(false);
	$xml->startElement('rows');
	
	foreach($db->array_select as $regs)
	{
		$valor_total += $regs["valor"];
		
		$xml->startElement('row');
			$xml->writeAttribute('id', $regs["id_outros"]);			
			$xml->writeElement('cell', $regs["descricao"]);
			$xml->writeElement('cell', number_format($regs["valor"],2,',','.'));
			$xml->writeElement('cell', '<img style="cursor:pointer;" onclick=if(confirm("Deseja excluir?")){xajax_excluir("'.$regs['id_outros'].'")}; src="'.DIR_IMAGENS.'apagar.png">');
		$xml->endElement();
	}
	
	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);
		
	$resposta->addScript("grid('div_outros', true, '100', '".$conteudo."');");
	
	$resposta->addAssign('total_do_valor','value',str_replace('.','',number_format($valor_total,2,',','.')));
	
	return $resposta;
}

function insere($dados_form)
{
	$resposta = new xajaxResponse();

	$db = new banco_dados;
	
	if($dados_form["descricao"]!='' || $dados_form["valor"]!='')
	{
		$isql = "INSERT INTO ".DATABASE.".fechamento_folha_detalhes ";
		$isql .= "(id_funcionario, data_ini, data_fim, descricao, valor, tipo) ";
		$isql .= "VALUES ('" . $dados_form["id_funcionario"] . "', ";
		$isql .= "'" . php_mysql($dados_form["data_ini"]) . "', ";
		$isql .= "'" . php_mysql($dados_form["data_fim"]) . "', ";
		$isql .= "'" . maiusculas($dados_form["descricao"]) . "', ";
		$isql .= "'" . str_replace(",", ".", str_replace(".", "", $dados_form["valor"])) . "', ";
		$isql .= "'" . $dados_form["tipo"] . "') ";
		
		$db->insert($isql,'MYSQL');
		
		if ($db->erro != '')
		{
			$resposta->addAlert("Não foi possível a inserção dos dados".$isql);
		}

		$resposta->addScript("xajax_voltar('');");

		$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'));");
	
		$resposta->addAlert("Cadastrado com sucesso.");
	}
	else
	{
		$resposta->addAlert("Os campos devem estar preenchidos.");
	}	
	
	return $resposta;
}

function editar($id)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
		
	$sql = "SELECT * FROM ".DATABASE.".fechamento_folha_detalhes ";
	$sql .= "WHERE fechamento_folha_detalhes.id_outros = '" . $id . "' ";
	$sql .= "AND fechamento_folha_detalhes.reg_del = 0 "; 

	$db->select($sql, 'MYSQL', true);
	
	if ($db->erro != '')
	{
		$resposta->addAlert("Não foi possível a seleção dos dados".$sql);
	}


	$regs = $db->array_select[0];
	
	$resposta->addAssign("descricao", "value",$regs["descricao"]);
	
	$resposta->addAssign("valor", "value",$regs["valor"]);
	
	$resposta->addAssign("id_outros", "value",$regs["id_outros"]);
	
	$resposta->addAssign("btninserir", "value", "Atualizar");
	
	$resposta->addEvent("btninserir", "onclick", "xajax_atualizar(xajax.getFormValues('frm'));");
	
	$resposta->addEvent("btnvoltar", "onclick", "xajax_voltar('');");

	return $resposta;	
}

function atualizar($dados_form)
{
	$resposta = new xajaxResponse();
	
	if($dados_form["descricao"]!='' || $dados_form["valor"]!='')
	{
		$db = new banco_dados;	
			
		$usql = "UPDATE ".DATABASE.".fechamento_folha_detalhes SET ";
		$usql .= "descricao = '" . maiusculas($dados_form["descricao"]) . "', ";
		$usql .= "valor = '" . str_replace(",", ".", str_replace(".", "", $dados_form["valor"])) . "' ";
		$usql .= "WHERE id_outros = '".$dados_form["id_outros"]."' ";
		$usql .= "AND fechamento_folha_detalhes.reg_del = 0 ";
	
		$db->update($usql,'MYSQL');
		
		if ($db->erro != '')
		{
			$resposta->addAlert("Não foi possível a atualização dos dados.".$usql);
		}
	
		$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'));");
		
		$resposta->addAlert("Atualizado com sucesso.");
	
	}
	else
	{
		$resposta->addAlert("Os campos devem estar preenchidos.");
	}
	
	return $resposta;
}

function excluir($id)
{
	$resposta = new xajaxResponse();
			
	$db = new banco_dados();
	
	$usql = "UPDATE ".DATABASE.".fechamento_folha_detalhes SET ";
	$usql .= "reg_del = 1, ";
	$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
	$usql .= "data_del = '".date('Y-m-d')."' ";
	$usql .= "WHERE fechamento_folha_detalhes.id_outros = '" . $id . "' ";
	$usql .= "AND reg_del = 0 ";
	
	$db->update($usql,'MYSQL');
	
	if ($db->erro != '')
	{
		$resposta->addAlert("Erro ".$sql);
	}

	$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'));");
	
	$resposta->addAlert("Excluido com sucesso.");
	
	return $resposta;
}

$db = new banco_dados();

$xajax->registerFunction("voltar");
$xajax->registerFunction("insere");
$xajax->registerFunction("editar");
$xajax->registerFunction("atualizar");
$xajax->registerFunction("excluir");
$xajax->registerFunction("atualizatabela");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","xajax_atualizatabela(xajax.getFormValues('frm'));");

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script language="javascript">


window.onbeforeunload = function () 
{
    envia_valores();
};

function envia_valores()
{	
	ttl_valor = document.getElementById("total_do_valor").value;
	
	tipo = document.getElementById("tipo").value;

	switch (tipo)
	{
		case 'outros_descontos':		
			window.opener.document.forms[0].outros_descontos.value = ttl_valor;		
		break;
		
		case 'outros_acrescimos':
			window.opener.document.forms[0].outros_acrescimos.value = ttl_valor;
		break;
		
		case 'diferenca_clt_ferias':
			window.opener.document.forms[0].diferenca_clt_ferias.value = ttl_valor;
		break;
		
		case 'diferenca_clt_rescisao':
			window.opener.document.forms[0].diferenca_clt_rescisao.value = ttl_valor;
		break;			
	}
	
	window.close();	
}


function grid(tabela, autoh, height, xml)
{
	mygrid = new dhtmlXGridObject(tabela);

	mygrid.enableAutoHeight(autoh,height);
	mygrid.enableRowsHover(true,'cor_mouseover');

	mygrid.setHeader("Descrição,valor,D");
	mygrid.setInitWidths("450,100,40");
	mygrid.setColAlign("left,left,center");
	mygrid.setColTypes("ro,ro,ro");
	mygrid.setColSorting("str,str,str");

	function doOnRowSelected(id,col)
	{
		if(col<2)
		{
			xajax_editar(id);

			return true;
		}
	}
	
	mygrid.attachEvent("onRowSelect",doOnRowSelected);
	
	mygrid.setSkin("dhx_skyblue");
    mygrid.enableMultiselect(true);
    mygrid.enableCollSpan(true);	
	mygrid.init();
	mygrid.loadXMLString(xml);
}

</script>

<?php

$conf = new configs();

if(isset($_GET["id_funcionario"]) && isset($_GET["data_ini"]) && isset($_GET["data_fim"]))
{
	$cod_funcionario = $_GET["id_funcionario"];
	$data_ini = mysql_php($_GET["data_ini"]);
	$data_fin = mysql_php($_GET["data_fim"]);
	$tipo_valor = $_GET["tipo"];
}
else
{
	$cod_funcionario = $_POST["id_funcionario"];
	$data_ini = $_POST["dataini"];
	$data_fin = $_POST["data_fim"];
	$tipo_valor = $_POST["tipo"];
}

$sql = "SELECT funcionario FROM ".DATABASE.".funcionarios ";
$sql .= "WHERE funcionarios.id_funcionario = '".$cod_funcionario."' ";
$sql .= "AND funcionarios.reg_del = 0 ";

$db->select($sql, 'MYSQL', true);

if ($db->erro != '')
{
	$resposta->addAlert("Não foi possível a seleção dos dados.".$sql);
}

$reg = $db->array_select[0];

$smarty->assign('colaborador',$reg["funcionario"]);
$smarty->assign('id_funcionario',$cod_funcionario);
$smarty->assign('tipo',$tipo_valor);
$smarty->assign('data_ini',$data_ini);
$smarty->assign('data_fim',$data_fin);

$smarty->assign('ocultarCabecalhoRodape','style="display:none;"');

$smarty->assign('revisao_documento', 'V4');

$smarty->assign('campo', $conf->campos('fechamentofolha_outros'));

$smarty->assign("botao", $conf->botoes());

$smarty->assign("classe",CSS_FILE);

$smarty->display('fechamentofolha_outros.tpl');

?>
