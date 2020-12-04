<?php
/*
	Formul�rio de Menu Procedimentos
	
	Criado por Carlos M�xim ia
	
	local/Nome do arquivo: 
	../ti/menu_procedimentos_ti.php	
		
	Versão 0 --> VERSÃO INICIAL - 24/09/2014
	Versão 1 -->Atualização layout - Carlos Abreu - 21/03/2017		
*/
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO M�DULO
//previne contra acesso direto
if(!verifica_sub_modulo(499))
{
	nao_permitido();
}

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","xajax_monta_menu(499);"); //M�DULO PROCEDIMENTOS TI

$conf = new configs();

$smarty->assign("revisao_documento","V1");

$smarty->assign("campo",$conf->campos('menuprocedimentosti'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("classe",CSS_FILE);

$template = TEMPLATES_DIR."menu.tpl";

$smarty->display($template);


?>
