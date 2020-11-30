<?php
/*
		Formulário de Notas Fiscais Funcionarios	
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		../financeiro/nfsfunc.php
		
		Versão 0 --> VERSÃO INICIAL - 23/02/2007
		Versão 1 --> Alteração de layout e TAP de alteração do fechamento: 12/01/2015
		Versão 2 --> Alteração lay-out - 18/07/2016 - Carlos Abreu
		Versão 3 --> atualização layout -  Carlos Abreu - 28/03/2017
		Versão 4 --> Inclusão dos campos reg_del nas consultas - 20/11/2017 - Carlos Abreu
*/	
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

function atualizatabela($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	$sql = "SELECT nf_funcionarios.*, empresa_funcionarios.empresa_func, funcionarios.funcionario, fechamento_folha.data_ini, fechamento_folha.data_fim, fechamento_folha.valor_imposto, fechamento_folha.valor_pcc FROM ".DATABASE.".fechamento_folha, ".DATABASE.".nf_funcionarios, ".DATABASE.".funcionarios ";
	$sql .= "LEFT JOIN ".DATABASE.".empresa_funcionarios ON (empresa_funcionarios.id_empfunc = funcionarios.id_empfunc AND empresa_funcionarios.reg_del = 0) ";
	$sql .= "WHERE funcionarios.id_funcionario = fechamento_folha.id_funcionario ";
	$sql .= "AND fechamento_folha.reg_del = 0 ";
	$sql .= "AND nf_funcionarios.reg_del = 0 ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	$sql .= "AND fechamento_folha.id_fechamento = nf_funcionarios.id_fechamento ";
	$sql .= "AND fechamento_folha.id_fechamento = '" . $dados_form["id_fechamento"] . "' ";
	
	$db->select($sql,'MYSQL',true);
	
	if ($db->erro != '')
	{
		$resposta->addAlert("Não foi possível realizar a seleção. ERRO: ".$db->erro);	
	}

	$conteudo = "";
	
	$i = 0;
	
	$xml = new XMLWriter();
	$xml->openMemory();
	$xml->setIndent(false);
	$xml->startElement('rows');
	
	foreach($db->array_select as $nfsfunc)
	{
		if($nfsfunc["valor_imposto"]!="0.00")
		{
			$valor_imposto =  formatavalor(number_format(round(($nfsfunc["nf_valor"] * 1.5)/100,2),2)); 
		}
		else
		{
			$valor_imposto = "0,00";
		}
		
		if($nfsfunc["valor_pcc"]!="0.00")
		{
			$valor_csl =  formatavalor(number_format(round(($nfsfunc["nf_valor"] * 4.65)/100,2),2)); 
		}
		else
		{
			$valor_csl = "0,00";
		}

		$xml->startElement('row');
			$xml->writeAttribute('id', $nfsfunc["id_nf_funcionario"]);
			$xml->writeElement('cell', $nfsfunc["empresa_func"]);
			$xml->writeElement('cell', $nfsfunc["funcionario"].'&nbsp;-&nbsp;'.mysql_php($nfsfunc["data_ini"]).'&nbsp;'.mysql_php($nfsfunc["data_fim"]));
			$xml->writeElement('cell', $nfsfunc["nf_numero"]);
			$xml->writeElement('cell', formatavalor($nfsfunc["nf_valor"]));
			$xml->writeElement('cell', $valor_imposto);
			$xml->writeElement('cell', $valor_csl);
			$xml->writeElement('cell', mysql_php($nfsfunc["nf_emissao"]));
			$xml->writeElement('cell', '<img src="'.DIR_IMAGENS.'editar.png" style="cursor:pointer;" onclick=xajax_editar("'.$nfsfunc["id_nf_funcionario"].'"); />');
			$xml->writeElement('cell', '<img src="'.DIR_IMAGENS.'apagar.png" style="cursor:pointer;" onclick=if(confirm("Confirma&nbsp;a&nbsp;exclusão?")){xajax_excluir("'.$nfsfunc['id_nf_funcionario'].'")} />');
		$xml->endElement();
	}

	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);

	$resposta->addScript("grid('adiantamento', true, '260', '".$conteudo."');");
	
	return $resposta;
}

function insere($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	if($dados_form["id_fechamento"]!='' || $dados_form["nf_numero"]!='' || $dados_form["nf_emissao"]!='')
	{
		$valor_notafunc = str_replace(",",".",str_replace(".","",$dados_form["nf_valor"]));
	
		$isql = "INSERT INTO ".DATABASE.".nf_funcionarios ";
		$isql .= "(id_fechamento, nf_numero, nf_valor, nf_emissao, nf_ajuda_custo, nf_descricao) ";
		$isql .= "VALUES ('". $dados_form["id_fechamento"]."', ";
		$isql .= "'" . $dados_form["nf_numero"] ."', ";
		$isql .= "'" . $valor_notafunc ."', ";
		$isql .= "'" . php_mysql($dados_form["nf_emissao"]) ."', ";
		$isql .= "'" . $dados_form["nf_ajuda_custo"] . "', ";
		$isql .= "'" . maiusculas($dados_form["nf_descricao"]) . "') ";	
	
		$db->insert($isql,'MYSQL');
		
		if ($db->erro != '')
		{
			$resposta->addAlert("Não foi possível a inserção dos dados".$isql);
		}

		//Zera o campo complemento
		$resposta->addAssign("data", "value", date("d/m/Y"));
		
		$resposta->addAssign("nf_valor", "value", "");
		
		$resposta->addAssign("id_fechamento", "value", $dados_form["id_fechamento"]);
		
		$resposta->addAssign("nf_descricao", "value", "");	
			
		$resposta->addScript("seleciona_combo('', 'nf_ajuda_custo'); ");

		$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'));");

		$resposta->addAlert("Nota Fiscal cadastrada com sucesso.");
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
	
	$conteudo = "";
	
	$db = new banco_dados;
	
	$sql = "SELECT nf_funcionarios.*, empresa_funcionarios.empresa_func, funcionarios.funcionario, fechamento_folha.data_ini, fechamento_folha.data_fim, fechamento_folha.valor_imposto, fechamento_folha.valor_pcc FROM ".DATABASE.".fechamento_folha, ".DATABASE.".nf_funcionarios, ".DATABASE.".funcionarios ";
	$sql .= "LEFT JOIN ".DATABASE.".empresa_funcionarios ON (empresa_funcionarios.id_empfunc = funcionarios.id_empfunc AND empresa_funcionarios.reg_del = 0) ";
	$sql .= "WHERE funcionarios.id_funcionario = fechamento_folha.id_funcionario ";
	$sql .= "AND fechamento_folha.reg_del = 0 ";
	$sql .= "AND nf_funcionarios.reg_del = 0 ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	$sql .= "AND fechamento_folha.id_fechamento = nf_funcionarios.id_fechamento ";
	$sql .= "AND nf_funcionarios.id_nf_funcionario = '" . $id . "' ";

	$db->select($sql,'MYSQL',true);
	
	if ($db->erro != '')
	{
		$resposta->addAlert("Não foi possível realizar a seleção. ERRO: ".$db->erro);	
	}

	$nfsfunc = $db->array_select[0];
	
	$resposta->addScript("seleciona_combo('".$nfsfunc["nf_ajuda_custo"]."', 'nf_ajuda_custo'); ");

	$resposta->addAssign("id_nf_funcionario", "value", $id);
		
	$resposta->addAssign("nf_numero", "value",$nfsfunc["nf_numero"]);
	
	$resposta->addAssign("nf_emissao", "value", mysql_php($nfsfunc["nf_emissao"]));

	$resposta->addAssign("nf_valor", "value", number_format($nfsfunc["nf_valor"], 2, ',', '.'));
	
	$resposta->addAssign("nf_descricao", "value", $nfsfunc["nf_descricao"]);
	
	$resposta->addAssign("btninserir", "value", "Atualizar");
	
	$resposta->addEvent("btninserir", "onclick", "xajax_atualizar(xajax.getFormValues('frm'));");

	return $resposta;	
}

function atualizar($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	if($dados_form["id_fechamento"]!='' || $dados_form["nf_numero"]!='' || $dados_form["nf_emissao"]!='')
	{
		$valor_notafunc = str_replace(",",".",str_replace(".","",$dados_form["nf_valor"]));
		
		$usql = "UPDATE ".DATABASE.".nf_funcionarios SET ";
		$usql .= "id_fechamento = '" . $dados_form["id_fechamento"] . "', ";
		$usql .= "nf_emissao = '" . php_mysql($dados_form["nf_emissao"]) . "', ";
		$usql .= "nf_numero = '" . $dados_form["nf_numero"] . "', ";
		$usql .= "nf_valor = '" . $valor_notafunc . "', ";
		$usql .= "nf_ajuda_custo = '" . $dados_form["nf_ajuda_custo"] . "', ";
		$usql .= "nf_descricao = '" . maiusculas($dados_form["nf_descricao"]) . "' ";
		$usql .= "WHERE id_nf_funcionario = '" . $dados_form["id_nf_funcionario"] ."' ";
		$usql .= "AND reg_del = 0 ";
		
		$db->update($usql, 'MYSQL');
		
		if ($db->erro != '')
		{
			$resposta->addAlert("Não foi possível a atualização dos dados.".$sql);
		}

		$resposta->addAlert("Nota Fiscal alterada com sucesso.");
	}
	else
	{	
		$resposta->addAlert("Os campos devem estar preenchidos.");
	}

	$resposta->addAssign("nf_emissao", "value", date("d/m/Y"));
	
	$resposta->addAssign("nf_numero", "value", "");
	
	$resposta->addAssign("id_fechamento", "value", $dados_form["id_fechamento"]);
	
	$resposta->addAssign("id_nf_funcionario", "value", "");
	
	$resposta->addAssign("nf_valor", "value", "");
	
	$resposta->addScript("seleciona_combo('', 'nf_ajuda_custo'); ");
	
	$resposta->addAssign("nf_descricao", "value", "");
	
	$resposta->addAssign("btninserir", "value", "Inserir");
	
	$resposta->addEvent("btninserir", "onclick", "xajax_insere(xajax.getFormValues('frm'));");

	$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'));");
	
	return $resposta;
}

function excluir($id)
{
	$resposta = new xajaxResponse();
			
	$db = new banco_dados();
	
	$usql = "UPDATE ".DATABASE.".nf_funcionarios SET ";
	$usql .= "reg_del = 1, ";
	$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
	$usql .= "data_del = '".date('Y-m-d')."' ";
	$usql .= "WHERE nf_funcionarios.id_nf_funcionario = '".$id."' ";
	$usql .= "AND reg_del = 0 ";
	
	$db->update($usql,'MYSQL');
	
	if ($db->erro != '')
	{
		$resposta->addAlert("Ocorreu um erro. \nSQL: ".$db->erro);
	}

	$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'));");
	
	$resposta->addAlert("Nota fiscal excluida com sucesso.");

	return $resposta;
}


$xajax->registerFunction("insere");
$xajax->registerFunction("atualizar");
$xajax->registerFunction("editar");
$xajax->registerFunction("excluir");
$xajax->registerFunction("atualizatabela");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","xajax_atualizatabela(xajax.getFormValues('frm'))");

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script>

function grid(tabela, autoh, height, xml)
{
	mygrid = new dhtmlXGridObject(tabela);

	mygrid.enableAutoHeight(autoh,height);
	mygrid.enableRowsHover(true,'cor_mouseover');

	mygrid.setHeader('Empresa,Funcionário&nbsp;/&nbsp;Período,NF,Valor(R$),IR(R$),CSLL(R$),Data,E,D');
	mygrid.setInitWidths("*,*,60,70,50,70,70,50,50");
	mygrid.setColAlign("left,left,left,left,left,left,left,center");
	mygrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro");
	mygrid.setColSorting("str,str,str,str,str,str,str,str,str");

	mygrid.setSkin("dhx_skyblue");
    mygrid.enableMultiselect(true);
    mygrid.enableCollSpan(true);	
	mygrid.init();
	mygrid.loadXMLString(xml);
}
</script>

<?php

$db = new banco_dados();

$conf = new configs();

$sql = "SELECT * FROM ".DATABASE.".fechamento_folha, ".DATABASE.".funcionarios ";
$sql .= "LEFT JOIN ".DATABASE.".empresa_funcionarios ON (empresa_funcionarios.id_empfunc = funcionarios.id_empfunc AND empresa_funcionarios.reg_del = 0) ";
$sql .= "WHERE funcionarios.id_funcionario = fechamento_folha.id_funcionario ";
$sql .= "AND fechamento_folha.reg_del = 0 ";
$sql .= "AND funcionarios.reg_del = 0 ";
$sql .= "AND fechamento_folha.id_fechamento = '".$_GET["id_fechamento"]."' ";
$sql .= "ORDER BY funcionarios.funcionario, fechamento_folha.data_ini, fechamento_folha.data_fim ";
 
$db->select($sql,'MYSQL',true);

$cont_funcionario = $db->array_select[0];

$smarty->assign('ocultarCabecalhoRodape','style="display:none;"');

$smarty->assign("revisao_documento","V4");

$smarty->assign("campo",$conf->campos('notas_fiscais'));

$smarty->assign('cont_funcionario', $cont_funcionario);

$smarty->assign('id_fechamento', $_GET['id_fechamento']);
						
$smarty->assign("classe",CSS_FILE);

$smarty->display('nfsfunc.tpl');

?>