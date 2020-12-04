<?php
/*
	Formulário de Menu Qualidade
	
	Criado por Carlos Eduardo  
	
	local/Nome do arquivo: 
	../qualidade/menurequisitos.php
	
	Versão 0 --> VERSÃO INICIAL : 03/06/2016
	Versão 1 --> Atualização layout - Carlos Abreu - 21/03/2017	 	
*/
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(564))
{
	nao_permitido();
}

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","xajax_monta_menu(564);");

$conf = new configs();

$smarty->assign("revisao_documento","V1");

$smarty->assign("campo",$conf->campos('menurequisitos'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("classe",CSS_FILE);

$template = TEMPLATES_DIR."menu.tpl";

$smarty->display($template);

?>