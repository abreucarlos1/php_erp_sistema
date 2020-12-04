<?php
/*
	Formulário de Menu Recursos Humanos
	
	Criado por Carlos Abreu  
	
	local/Nome do arquivo: 
	../rh/menurh.php
	
	Versão 0 --> VERSÃO INICIAL : 02/03/2006
	Versão 1 --> Atualização Lay-out | Smarty : 05/08/2008
	Versão 2 --> Atualização DB / Lay-out: Carlos Abreu - 16/10/2012
	Versão 3 --> Atualização layout - Carlos Abreu - 21/03/2017		
*/

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(10))
{
	nao_permitido();
}
$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","xajax_monta_menu(10);");

$conf = new configs();

$smarty->assign("revisao_documento","V3");

$smarty->assign("campo",$conf->campos('menurh'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("classe",CSS_FILE);

$template = TEMPLATES_DIR."menu.tpl";

$smarty->display($template);
?>