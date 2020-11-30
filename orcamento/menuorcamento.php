<?php
/*
	Formulário de Menu Orçamento
	
	Criado por Carlos Abreu  
	
	local/Nome do arquivo: 
	../orcamento/menuorcamento.php	
		
	Versão 0 --> VERSÃO INICIAL - 24/11/2014 - Carlos Abreu
	Versão 1 --> Atualização lay-out - Carlos Abreu - 21/03/2017

*/

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(13))
{
	nao_permitido();
}

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","xajax_monta_menu(505);");

$conf = new configs();

$smarty->assign("revisao_documento","V1");

$smarty->assign("campo",$conf->campos('menuorcamento'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("classe",CSS_FILE);

$template = TEMPLATES_DIR."menu.tpl";

$smarty->display($template);
?>
