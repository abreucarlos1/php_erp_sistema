<?php
/*
	Módulo de Manuais de Sistemas para o sistema
	
	Criado por Carlos
	
	local/Nome do arquivo:
	../manuais_sistemas/menuadministrativomanuais.php
	
	Versão 0 --> VERSÃO INICIAL : 15/06/2015
	Versão 1 --> Atualização layout - Carlos Abreu - 21/03/2017
*/
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO
//previne contra acesso direto
if(!verifica_sub_modulo(523))
{
	nao_permitido();
}

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","xajax_monta_menu(523);");

$conf = new configs();

$smarty->assign("revisao_documento","V1");

$smarty->assign("campo",$conf->campos('menu_manuais_administrativo'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("classe",CSS_FILE);

$template = TEMPLATES_DIR."menu.tpl";

$smarty->display($template);

?>