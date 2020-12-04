<?php
/*
	Formulário de Menu avaliaçães
	
	Criado por Carlos Abreu  
	
	local/Nome do arquivo: 
	../rh/menu_avaliacoes.php
	
	Versão 0 --> VERSÃO INICIAL : 26/08/2006
	Versão 1 --> Atualização layout - Carlos Abreu - 21/03/2017

*/

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(515) && !verifica_sub_modulo(543))
{
	nao_permitido();
}

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","xajax_monta_menu(543);"); 

$conf = new configs();

$smarty->assign("revisao_documento","V1");

$smarty->assign("campo",$conf->campos('menu_avaliacoes'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("classe",CSS_FILE);

$template = TEMPLATES_DIR."menu.tpl";

$smarty->display($template);
?>
