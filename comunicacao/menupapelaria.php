<?php
/*
     Formulário de Menu Comunicação
     
     Criado por Carlos Abreu  
     
     local/Nome do arquivo: ../comunicacao/menupapelaria.php
     
     
     Versão 0 --> VERSÃO INICIAL - 21/08/2014
     Versão 1 --> Atualização lay-out - Carlos Abreu - 21/03/2017
 */
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));

require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO
//previne contra acesso direto
if(!verifica_sub_modulo(372))
{
    nao_permitido();
}

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","xajax_monta_menu(372);"); //MÓDULO INSTITUCIONAL

$conf = new configs();

$smarty->assign("revisao_documento","V1");

$smarty->assign("campo",$conf->campos('menupapelaria'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("classe",CSS_FILE);

$template = TEMPLATES_DIR."menu.tpl";

$smarty->display($template);
?>