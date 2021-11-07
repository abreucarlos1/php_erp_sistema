<?php
/*
    Formulário de Grupos de questões
    
    Criado por Carlos 
    
    Versão 0 --> VERSÃO INICIAL : 20/05/2015
    Versão 1 --> Atualização layout - Carlos Abreu - 07/04/2017
    Versão 2 --> Inclusão dos campos reg_del nas consultas - 28/11/2017 - Carlos Abreu
    Versão 3 --> Layout responsivo - 28/11/2017 - Carlos 
*/
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

require_once(INCLUDE_DIR."antiInjection.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(516))
{
	nao_permitido();
}

function salvar_grupo($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db	= new banco_dados();
	
	$sql = "SELECT * FROM ".DATABASE.".banco_questoes_grupos ";
	$sql .= "WHERE banco_questoes_grupos.reg_del = 0 ";
	$sql .= "AND banco_questoes_grupos.bqg_titulo = '".strtoupper(AntiInjection::clean(tiraacentos($dados_form['bqg_titulo'])))."' ";
	
	$db->select($sql, 'MYSQL',true);
	
	if ($db->numero_registros > 0)
	{
		$resposta->addAlert('ATEÇÃO: Já existe um grupo com esta descrição!');
		
		return $resposta;
	}
	
	//Inserir ou atualizar dependendo do ID do grupo
	if (empty($dados_form['bqg_id']))
	{
		$isql = "INSERT INTO ".DATABASE.".banco_questoes_grupos (bqg_titulo) VALUES ('".strtoupper(AntiInjection::clean(tiraacentos($dados_form['bqg_titulo'])))."')";
		
		$db->insert($isql, 'MYSQL');
	}
	else
	{
		$usql = "UPDATE ".DATABASE.".banco_questoes_grupos SET ";
		$usql .= "bqg_titulo = '".strtoupper(AntiInjection::clean(tiraacentos($dados_form['bqg_titulo'])))."' ";
		$usql .= "WHERE bqg_id = '".$dados_form['bqg_id']."' ";
		
		$db->update($usql, 'MYSQL');
	}
	
	if ($db->erro != '')
	{
		$resposta->addAlert('Houve uma falha ao tentar salvar o grupo! '.$db->erro);
	}
	else
	{
		$resposta->addAlert('Grupo salvo corretamente! '.$db->erro);
		$resposta->addScript('xajax_atualizatabela();');
	}
	
	return $resposta;
}

function atualizatabela()
{
	$resposta = new xajaxResponse();
	
	$db	= new banco_dados();
	
	$retorno = array();
	
	$xml = new XMLWriter();
	$xml->openMemory();
	$xml->startElement('rows');
	
	$sql = "SELECT * FROM ".DATABASE.".banco_questoes_grupos ";
	$sql .= "WHERE banco_questoes_grupos.reg_del = 0 ";
	
	$db->select($sql, 'MYSQL', true);
	
	foreach($db->array_select as $reg)
	{
			$xml->startElement('row');
				$xml->writeAttribute('id', $reg['bqg_id']);
				$xml->writeElement('cell', $reg['bqg_id']);
				$xml->writeElement('cell', $reg['bqg_titulo']);
				$xml->writeElement('cell', "<img style=\'cursor:pointer;\' src=\'".DIR_IMAGENS."apagar.png\' onclick=if(confirm(\'Deseja excluir este grupo?\')){xajax_excluir(".$reg['bqg_id'].");} />");
			$xml->endElement();
	}
					
	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('div_grupos',true,'600','".$conteudo."');");
	$resposta->addScript("limparForm();");
	$resposta->addAssign('btn_inserir', 'value', 'Inserir');
	
	return $resposta;
}

function editar($id)
{
	$resposta = new xajaxResponse();
	
	$db	= new banco_dados();
	
	$sql = "SELECT * FROM ".DATABASE.".banco_questoes_grupos ";
	$sql .= "WHERE bqg_id = ". $id ." ";
	$sql .= "AND reg_del = 0 ";
	
	$db->select($sql, 'MYSQL', true);
	
	$reg = $db->array_select[0];

	$resposta->addAssign('bqg_id', 'value', $reg['bqg_id']);
		
	$resposta->addAssign('bqg_titulo', 'value', $reg['bqg_titulo']);	
			
	$resposta->addAssign('btn_inserir', 'value', 'Alterar');
	
	return $resposta;
}

function excluir($id)
{
	$resposta = new xajaxResponse();
	
	$db	= new banco_dados();
	
	$usql = "UPDATE ".DATABASE.".banco_questoes_grupos SET ";
	$usql .= "reg_del = 1, ";
	$usql .= "reg_who = ".$_SESSION['id_funcionario'].", ";
	$usql .= "data_del = '".date('Y-m-d')."' ";
	$usql .= "WHERE bqg_id = ".$id." ";
	$usql .= "AND reg_del = 0 ";
	
	$db->update($usql, 'MYSQL');
	
	if ($db->erro != '')
	{
		$resposta->addAlert('Houve uma falha ao tentar excluir o grupo! '.$db->erro);
	}
	else
	{
		$resposta->addAlert('Grupo excluido corretamente! '.$db->erro);
		$resposta->addScript('xajax_atualizatabela();');
	}
	
	return $resposta;
}

$xajax->registerFunction("salvar_grupo");
$xajax->registerFunction("atualizatabela");
$xajax->registerFunction("editar");
$xajax->registerFunction("excluir");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","xajax_atualizatabela();");
?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script>

function grid(tabela, autoh, height, xml)
{	
	mygrid = new dhtmlXGridObject(tabela);

	mygrid.enableAutoHeight(autoh,height);
	mygrid.enableRowsHover(true,'cor_mouseover');

	function doOnRowSelected(row,col)
	{
		if(col<=2)
		{						
			xajax_editar(row);

			return true;
		}
	}

	mygrid.setHeader("ID, Título, D");
	mygrid.setInitWidths("50,*,50");
	mygrid.setColAlign("left,left,center");
	mygrid.setColTypes("ro,ro,ro");
	mygrid.setColSorting("str,str,str");

	mygrid.attachEvent('onRowSelect', doOnRowSelected);

	mygrid.setSkin("dhx_skyblue");
    mygrid.enableMultiselect(true);
    mygrid.enableCollSpan(true);	
	mygrid.init();
	mygrid.loadXMLString(xml);
}

function limparForm()
{
	document.getElementById('bqg_titulo').value = '';
	document.getElementById('bqg_id').value = '';
}

</script>

<?php

$conf = new configs();

$smarty->assign("campo",$conf->campos('grupos_questoes'));

$smarty->assign("revisao_documento","V3");

$smarty->assign('larguraTotal', 1);

$smarty->assign("classe",CSS_FILE);

$smarty->display('grupos_questoes.tpl');
?>