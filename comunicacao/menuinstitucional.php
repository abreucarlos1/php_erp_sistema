<?php
/*
     Formulário de Menu Comunicação
     
     Criado por Carlos Abreu  
     
     local/Nome do arquivo: ../comunicacao/menuinstitucional.php
     
     Versão 0 --> VERSÃO INICIAL - 12/08/2014
     Versão 1 --> Atualização Lay-out - Carlos Abreu - 21/03/2017
 */

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));

require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO
//previne contra acesso direto
if(!verifica_sub_modulo(370))
{
    nao_permitido();
}

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","xajax_monta_menu(370);"); //MÓDULO INSTITUCIONAL

$conf = new configs();

$smarty->assign("revisao_documento","V1");

$smarty->assign("campo",$conf->campos('menuinstitucional'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("classe",CSS_FILE);

$template = TEMPLATES_DIR."menu.tpl";

$smarty->display($template);
?>