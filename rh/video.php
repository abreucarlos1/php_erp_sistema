<?php
/*
	Formulário de Menu Liderança
	
	Criado por Carlos
	
	local/Nome do arquivo: ../rh/video.php
	
	Versão 0 --> VERSÃO INICIAL : 01/10/2015
*/

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(539))
{
	nao_permitido();
}

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$conf = new configs();

//$video = DOCUMENTOS_RH.'TREINAMENTOS/INTEGRACAO.mp4';
$video = 'INTEGRACAO.mp4';

$smarty->assign("video",$video);

$smarty->assign("revisao_documento","V0");

$smarty->assign("campo",$conf->campos('video_integracao'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("classe",CSS_FILE);

$smarty->display('video.tpl');
?>