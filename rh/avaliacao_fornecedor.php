<?php
/*
		Formulário de Avaliação de Fornecedor
		
		Criado por Carlos
	
	  local/Nome do arquivo:
	  ../rh/avaliacao_fornecedor.php
	
		Versão 0 --> VERSÃO INICIAL : 20/05/2015
		Versão 1 --> alteracao layout - Carlos Abreu - 04/04/2017
		Versão 2 --> Alteração para contemplar a nova avaliação de conteúdo técnico - 07/11/2017
		Versão 3 --> Inclusão dos campos reg_del nas consultas - 27/11/2017 - Carlos Abreu
		Versão 4 --> Layout responsivo - 05/02/2018 - Carlos
*/
//header('X-UA-Compatible: IE=edge');

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

require_once(INCLUDE_DIR."antiInjection.php");

require_once(INCLUDE_DIR."mpdf60/mpdf.php");

require_once("../ti/models/avaliacoes.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(518))
{
	nao_permitido();
}

/**
 * Esta função pode ser unificada mudando-se apenas o template a ser carregado
 * @param unknown_type $dados_form
 */
function enviarAvaliacao($dados_form)
{
	$model = new avaliacoes(new Smarty());
	
	return $model->enviarAvaliacao($dados_form, './avaliacao_fornecedor.php');
}

/**
 * Função responsável por desenhar a avaliação de acordo com os critários cadastrados
 * @param number $codFuncionario
 */
function montaAvaliacao($codFuncionario, $impressao = false, $avaId = 0, $alvo = 2)
{
	$selecaoFuncionario = $codFuncionario;

	//Chega da seguinte forma codFuncionario/Aval1/Aval2...
	$arrAvaliacoes = explode('/', $codFuncionario);
	
	if (count($arrAvaliacoes) > 1)
	{
		$codFuncionario = $arrAvaliacoes[0];
		
		$arrAvaliacoes = array_reverse($arrAvaliacoes);
		array_pop($arrAvaliacoes);
		
		$avaliacoesAbertas = implode(',', $arrAvaliacoes);
	}
	else
	{
		$codFuncionario = $selecaoFuncionario;
		$avaliacoesAbertas = $avaId;
	}
	
	$smarty = new Smarty();
	$resposta = new xajaxResponse();
	$db = new banco_dados();
	$model = new avaliacoes();
	
	$smarty->template_dir = "templates_erp";
	$smarty->compile_dir = dirname(dirname(__FILE__))."/templates_c";
	$smarty->caching = false;
	$smarty->assign('PROJETO', PROJETO);
	$smarty->assign('autoavaliacao', false);
	
	$resposta->addScript("document.getElementById('frm').reset();");
	$resposta->addScript("seleciona_combo('{$selecaoFuncionario}', 'selSubId');");
	$smarty->assign('codFuncionario', $codFuncionario);
	
	if (!empty($codFuncionario))
	{
		$dados = $model->montaAvaliacao($codFuncionario, $impressao, $avaId, $alvo, $avaliacoesAbertas);
		
		foreach($dados as $var => $valor)
		{
			$smarty->assign($var, $valor);
		}
		
		//Duas versões da avaliação, uma para impressão e outra para responder
		$htmlAvaliacao = $smarty->fetch('./viewHelper/avaliacao/avaliacao.tpl');
		
		$resposta->addAssign("div_avaliacao_perguntas",'innerHTML', $htmlAvaliacao);
		$resposta->addScript("document.getElementById('div_avaliacao').style.display = '';");
	}
	else
	{
		$resposta->addScript("document.getElementById('div_avaliacao').style.display = 'none';");
	}

	$resposta->addScript("a_tabbar.tabs('avaliacao').setActive();");
	
	return $resposta;
}

function getAvaliados()
{
	$model = new avaliacoes();
	
	return $model->getAvaliados(2);
}

/**
 * Função responsável por desenhar a tela de pdi
 * @param number $codFuncionario
 * @param number $avaId
 */
function montaTelaPDI($codFuncionario, $avaId = 0)
{
	$model = new avaliacoes(new Smarty());
	
	return $model->montaTelaPDI($codFuncionario, $avaId);
}

/**
 * Função responsável por desenhar a tela de metas
 * @param number $codFuncionario
 * @param number $avaId
 */
function montaTelaMetas($codFuncionario, $avaId = 0)
{
	$model = new avaliacoes(new Smarty());
	
	return $model->montaTelaMetas($codFuncionario, $avaId);
}

function gravarPDI($dados_form)
{
	$model = new avaliacoes();
	
	return $model->gravarPDI($dados_form);
}

function gravarMetas($dados_form)
{
	$model = new avaliacoes(new Smarty());
	
	return $model->gravarMetas($dados_form);
}

function montarCriterios($avaAlvo = 2)
{
	$model = new avaliacoes(new Smarty());
	
	return $model->montarCriterios($avaAlvo);
}

function montarApresentacao($avaId)
{
	$model = new avaliacoes(new Smarty());
	
	return $model->montarApresentacao($avaId);	
}

$xajax->registerFunction("enviarAvaliacao");
$xajax->registerFunction("montaAvaliacao");
$xajax->registerFunction("getAvaliados");
$xajax->registerFunction("montaTelaPDI");
$xajax->registerFunction("gravarPDI");
$xajax->registerFunction("montaTelaMetas");
$xajax->registerFunction("gravarMetas");
$xajax->registerFunction("montarCriterios");
$xajax->registerFunction("montarApresentacao");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));
?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script src="../js/avaliacoes/scripts.js"></script>

<?php

$conf = new configs();

$model = new avaliacoes();

$retorno = $model->getColaboradores(2);

if (!empty($retorno['avaliacoesAbertas']))
{
	$smarty->assign("body_onload","xajax_getAvaliados();xajax_montarCriterios(2);xajax_montarApresentacao(".$retorno['avaliacoesAbertas'][0].");");
}
else
{
	$smarty->assign("body_onload","xajax_getAvaliados();");
}

$smarty->assign("todos_avaliados", $retorno['todos_avaliados']);

$smarty->assign("option_func_values",$retorno['option_func_values']);
$smarty->assign("option_func_output",$retorno['option_func_output']);

$smarty->assign("campo",$conf->campos('avaliacao_fornecedor'));

$smarty->assign("revisao_documento","V4");

$smarty->assign('larguraTotal', 1);

$smarty->assign("classe",CSS_FILE);

$smarty->display('avaliacao_fornecedor.tpl');
?>