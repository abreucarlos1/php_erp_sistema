<?php
/*
	Formulário de Menu Tecnologia da Informação
	
	Criado por Carlos Abreu  
	
	local/Nome do arquivo: 
	../ti/menuti.php
	
	Versão 0 --> VERSÃO INICIAL : 27/04/2007
	Versão 1 --> Atualização Lay-out : 02/04/2009
	Versão 2 --> Atualização DB / Lay-out: Carlos Abreu - 07/08/2012
	Versão 3 --> Atualização layout - Carlos Abreu - 21/03/2017		
*/

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(15))
{
	nao_permitido();
}

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","xajax_monta_menu(15);");

$conf = new configs();

$smarty->assign("revisao_documento","V3");

$smarty->assign("campo",$conf->campos('menuti'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("classe",CSS_FILE);

$template = TEMPLATES_DIR."menu.tpl";

$smarty->display($template);
?>
