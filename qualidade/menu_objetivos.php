<?php
/*
	Formulário de Menu OBJETIVOS SGI
	
	Criado por Carlos Abreu  
	
	local/Nome do arquivo: ../qualidade/menu_objetivos.php
	
	Versão 0 --> VERSÃO INICIAL : 06/08/2014 - Carlos Abreu
	Versão 1 --> Atualização layout - Carlos Abreu - 21/03/2017
	
*/

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(340))
{
	nao_permitido();
}

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","xajax_monta_menu(340);");

$conf = new configs();

$smarty->assign("revisao_documento","V1");

$smarty->assign("campo",$conf->campos('menuobjetivos'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("classe",CSS_FILE);

$template = TEMPLATES_DIR."menu.tpl";

$smarty->display($template);

?>
