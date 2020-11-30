<?php
/*
	Formulário de MENU FINANCEIRO	
	
	Criado por Carlos Abreu  
	
	local/Nome do arquivo:
	../financeiro/menufinanceiro.php
	
	Versão 0 --> VERSÃO INICIAL
	Versão 1 --> Atualização Lay-out : 27/04/2007
	Versão 2 --> Atualização Lay-out : Carlos Abreu - 09/10/2012
	Versão 3 --> Atualização layout - Carlos Abreu - 21/03/2017		
*/	

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(5))
{
	nao_permitido();
}

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","xajax_monta_menu(5);"); //MÓDULO FINANCEIRO

$conf = new configs();

$smarty->assign("revisao_documento","V3");

$smarty->assign("campo",$conf->campos('menufinanceiro'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("classe",CSS_FILE);

$template = TEMPLATES_DIR."menu.tpl";

$smarty->display($template);
?>
