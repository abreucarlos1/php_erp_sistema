<?php
/*
	Formulário de Menu Integração Cliente
	
	Criado por Carlos Abreu  
	
	local/Nome do arquivo: 
	../rh/menuintegracaocliente.php
	
	Versão 0 --> VERSÃO INICIAL : 26/08/2006
	Versão 1 --> atualizacao layout - Carlos Abreu - 21/03/2017

*/

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(436))
{
	nao_permitido();
}

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","xajax_monta_menu(436);"); 

$conf = new configs();

$smarty->assign("revisao_documento","V1");

$smarty->assign("campo",$conf->campos('menuintegracaocliente'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("classe",CSS_FILE);

$template = TEMPLATES_DIR."menu.tpl";

$smarty->display($template);
?>
