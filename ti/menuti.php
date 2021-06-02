<?php
/*
	Formulário de Menu Administração
	
	Criado por Carlos Abreu  
	
	local/Nome do arquivo: 
	../administracao/menuti.php
	
	Versão 0 --> VERSÃO INICIAL : 20/05/2021
	
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

$smarty->assign("nome_empresa",NOME_EMPRESA);

$smarty->assign("revisao_documento","V0");

$smarty->assign("campo",$conf->campos('menuti'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("classe",CSS_FILE);

$smarty->assign("larguraTotal",1);

$template = TEMPLATES_DIR."menu.tpl";

$smarty->display($template);
?>
