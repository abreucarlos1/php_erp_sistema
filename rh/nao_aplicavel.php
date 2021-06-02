<?php
/*
	Formulário de não aplicaveis	
	
	Criado por Carlos Eduardo  
	
	local/Nome do arquivo:
	../rh/nao_aplicavel.php
	
	Versão 0 --> VERSÃO INICIAL - 03/06/2016
	Versão 1 --> Atualização layout - Carlos Abreu - 07/04/2017
	Versão 2 --> Inclusão dos campos reg_del nas consultas - 28/11/2017 - Carlos Abreu
*/
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

require_once(INCLUDE_DIR."antiInjection.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(228))
{
	nao_permitido();
}

$conf = new configs();

function voltar()
{
	$resposta = new xajaxResponse();

	$resposta->addScript("desseleciona_combo('funcionario');");
	
	$resposta->addScriptCall("reset_campos('frm_treinamentos_efetuados')");
	
	$resposta->addAssign("data_treinamento", "value", date('d/m/Y'));
	
	$resposta->addAssign("vigencia", "value", "12");
	
	$resposta->addAssign("btninserir", "value", "Inserir");
	
	$resposta->addEvent("btninserir", "onclick", "xajax_insere(xajax.getFormValues('frm_treinamentos_efetuados'));");
	
	$resposta->addEvent("btnvoltar", "onclick", "history.back();");

	return $resposta;

}

function atualizatabela($filtro)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;

	$xml = new XMLWriter();
	$xml->openMemory();
	$xml->startElement('rows');

	$sql_filtro = "";

	$sql_texto = "";

	if($filtro!="")
	{
		$sql_texto = str_replace('  ', ' ', AntiInjection::clean($filtro));
		
		$sql_texto = str_replace(' ', '%', '%'.$sql_texto.'%');

		$sql_filtro = " AND (nome LIKE '".$sql_texto."' ";
		$sql_filtro .= " OR email LIKE '".$sql_texto."' ";
		$sql_filtro .= " OR cpf LIKE '".$sql_texto."') ";
	}
		
	$sql = "SELECT * FROM ".DATABASE.".nao_recomendados ";
	$sql .= "WHERE nao_recomendados.reg_del = 0 ";
	$sql .= $sql_filtro;
	$sql .= "ORDER BY nome ";
	
	$db->select($sql, 'MYSQL', true);
	
	foreach($db->array_select as $reg)
	{
		$xml->startElement('row');
			$xml->writeAttribute('id', $reg['id']);
			$xml->writeElement('cell', $reg['nome']);
			$xml->writeElement('cell', $reg['email']);
			$xml->writeElement('cell', AntiInjection::formatarGenerico($reg['cpf'], '###.###.###-##'));
			$xml->writeElement('cell', "<img style=\'cursor:pointer\' onclick=if(confirm(\'Deseja Desbloquear este currículo?\')){xajax_excluirNR(".$reg['idCurriculo'].")}; src=\'".DIR_IMAGENS."apagar.png\'>");
		$xml->endElement();
	}

	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('divLista', true, '440', '".$conteudo."');");
	
	return $resposta;
}

function excluirNR($id)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados();
	
	$usql = "UPDATE
				".DATABASE.".nao_recomendados 
				SET reg_del = 1, 
					reg_who = '".$_SESSION['id_funcionario']."', 
					data_del = '".date('Y-m-d')."'
			WHERE idCurriculo = ".$id;
	
	$db->update($usql, 'MYSQL');

	if ($db->erro != '')
	{
		$resposta->addAlert('Houve uma falha ao tentar realizar esta operação!');
	}
	else
	{
		$resposta->addAlert('Alteração realizada corretamente!');
		$resposta->addScript("xajax_atualizatabela(document.getElementById('busca').value);");
	}
	
	return $resposta;
}

$xajax->registerFunction("voltar");
$xajax->registerFunction("atualizatabela");
$xajax->registerFunction("excluirNR");

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

	mygrid.setHeader("Nome, E-Mail, CPF, D");
	mygrid.setInitWidths("*,*,100,50");
	mygrid.setColAlign("left,left,left,center");
	mygrid.setColTypes("ro,ro,ro,ro");
	mygrid.setColSorting("str,str,str,str");

	mygrid.setSkin("dhx_skyblue");
	mygrid.enableMultiselect(true);
	mygrid.enableCollSpan(true);
	mygrid.init();
	mygrid.loadXMLString(xml);
}
</script>

<?php

$smarty->assign('campo', $conf->campos('nao_aplicavel'));

$smarty->assign('revisao_documento', 'V2');

$smarty->assign("classe",CSS_FILE);

$smarty->display('nao_aplicavel.tpl');
?>