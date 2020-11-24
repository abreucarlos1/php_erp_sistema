<?php
/*
     Formulário de Menu Comunicação
     
     Criado por Carlos Eduardo Máximo
     
     local/Nome do arquivo: ../comunicacao/menulogotipos.php
     
     Versão 0 --> VERSÃO INICIAL - 22/08/2014
     Versão 1 --> Atualização lay-out - Carlos Abreu - 21/03/2017
 */
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));

require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO
//previne contra acesso direto
if(!verifica_sub_modulo(373))
{
    nao_permitido();
}

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","xajax_monta_menu(373);"); //MÓDULO COMUNICAÇÃO

$conf = new configs();

$smarty->assign("revisao_documento","V1");

$smarty->assign("campo",$conf->campos('menulogotipos'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("classe",CSS_FILE);

$template = TEMPLATES_DIR."menu.tpl";

$smarty->display($template);
?>
