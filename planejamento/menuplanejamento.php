<?php
/*
	Formul�rio de Menu Planejamento
	
	Criado por Carlos Abreu  
	
	local/Nome do arquivo: 
	../planejamento/menuplanejamento.php
	
	Vers�o 0 --> VERS�O INICIAL : 02/03/2006
	Vers�o 1 --> Atualiza��o Lay-out : 28/07/2008
	Vers�o 2 --> Atualiza��o DB / Lay-out: Carlos Abreu - 11/10/2012
	Vers�o 3 --> Atualiza��o Lay-out: Carlos Abreu - 21/03/2017		
*/

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO M�DULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(8))
{
	nao_permitido();
}

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","xajax_monta_menu(8);"); //M�DULO PLANEJAMENTO

$conf = new configs();

$smarty->assign("revisao_documento","V3");

$smarty->assign("campo",$conf->campos('menuplanejamento'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("classe",CSS_FILE);

$template = TEMPLATES_DIR."menu.tpl";

$smarty->display($template);
?>
