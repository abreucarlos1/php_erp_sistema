<?php
/*
	Formulário de MENU PJ	
	
	Criado por Carlos Abreu  
	
	local/Nome do arquivo:
	../financeiro/menupj.php
	
	Versão 0 --> VERSÃO INICIAL - 27/04/2007
	Versão 1 --> Atualização Lay-out : 11/08/2008
	Versão 2 --> Atualização Lay-out : Carlos Abreu - 08/10/2012
	Versão 3 --> atualização layout - Carlos Abreu - 28/03/2017
	Versão 4 --> Inclusão dos campos reg_del nas consultas - 20/11/2017 - Carlos Abreu		
*/

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(4))
{
	nao_permitido();
}


function chamapagina($pagina)
{
	$resposta = new xajaxResponse();

	$db = new banco_dados;

	switch ($pagina)
	{
		case 'fechamento':
			$sql = "SELECT * FROM ".DATABASE.".fechamento_folha ";
			$sql .= "WHERE fechamento_folha.id_funcionario = '" . $_SESSION["id_funcionario"] . "' ";
			$sql .= "AND fechamento_folha.liberado = 1 ";
			$sql .= "AND fechamento_folha.reg_del = 0 ";
			$sql .= "ORDER BY fechamento_folha.data_fim ";

			$db->select($sql,'MYSQL',true);

			//se der mensagem de erro, mostra
			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}

			$cont = $db->array_select[0];

			//Verifica se o usuário possue Fechamentos a serem visualizados
			//Caso negativo, mostra mensagem de alerta e volta para o Menu Principal
			if($db->numero_registros>0)
			{
				$resposta->addScript("abrejanela('Fechamento', 'fechamento_forn.php?id_funcionario=".$cont["id_funcionario"]."',750,380);");
			}
			else
			{
				$resposta->addAlert("Não há liberação de fechamento.");
			}
			break;

		case 'anexar':
			$sql = "SELECT id_fechamento FROM ".DATABASE.".fechamento_folha ";
			$sql .= "WHERE fechamento_folha.permite_anexos = 1 ";
			$sql .= "AND fechamento_folha.id_funcionario = '" . $_SESSION["id_funcionario"] . "' ";
			$sql .= "AND fechamento_folha.reg_del = 0 ";
			$sql .= "ORDER BY fechamento_folha.data_fim DESC ";

			$db->select($sql,'MYSQL',true);

			//se der mensagem de erro, mostra
			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}

			$cont = $db->array_select[0];

			//Verifica se o usuário possue Fechamentos a serem visualizados
			//Caso negativo, mostra mensagem de alerta e volta para o Menu Principal
			if($db->numero_registros>0)
			{
				$resposta->addScript("window.open('../financeiro/cadastra_docs_forn.php?id_fechamento=". $cont["id_fechamento"] ."')");
			}
			else
			{
				$resposta->addAlert("Não há liberação de inclusão de anexos.\nFavor contatar o setor Financeiro.");
			}
			break;

		case 'modelo':
			$resposta->addScript("abrejanela('FechamentoModelo', 'fechamento_modelo.php',750,380);");
			break;
	}

	return $resposta;
}

$xajax->registerFunction("chamapagina");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$conf = new configs();

$smarty->assign("revisao_documento","V4");

$smarty->assign("botao",$conf->botoes());

$smarty->assign("campo",$conf->campos('menupj'));

$smarty->assign("classe",CSS_FILE);

$smarty->display('menupj.tpl');

?>

<script>

function abrejanela(nome,caminho,largura,altura)
{
	params = "width="+largura+",height="+altura+",resizable=0,titlebar=0,status=0,scrollbars=1,toolbar=0,location=0,directories=0,menubar=0,top="+(screen.height/2-altura/2)+", left="+(screen.width/2-largura/2)+" ";
	windows = window.open( caminho, nome , params);
	if(window.focus)
	{
		setTimeout("windows.focus()",100);
	}
}
		
</script>