<?php
/*
		Formulário de MENU DE PLANEJAMENTO	
		
		Criado por Carlos Abreu
		
		local/Nome do arquivo:
		../coordenacao/menuplanejamento.php
		
		Versão 0 --> VERSÃO INICIAL : 02/03/2006
		Versão 1 --> Atualização Lay-out | Smarty : 10/07/2008
		Versão 2 --> Atualização Layout: 10/04/2015
		Versão 3 --> atualização layout - Carlos Abreu - 28/03/2017	
*/	

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

function chamapagina($relatorio)
{
	$resposta = new xajaxResponse();

	switch ($relatorio)
	{
		case 'avancofisico':

			$resposta->addRedirect('avancofisico.php');			

		break;
	}
		
	return $resposta;
}

$conf = new configs();

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign('campo', $conf->campos('indices'));

$smarty->assign('revisao_documento', 'V3');

$smarty->assign("classe",CSS_FILE);

$smarty->display("menuindices.tpl");
?>