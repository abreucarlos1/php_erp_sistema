<?php
/*
		Formulário de Aniversariantes	
		
		Criado por Carlos Abreu 
		
		local/Nome do arquivo:
		../rh/aniversariantes.php
		
		Versão 0 --> VERSÃO INICIAL : 02/03/2006
		Versão 1 --> Atualização Lay-out : 12/08/2008
		Versão 2 --> Atualização Layout : 02/04/2015 - Carlos
		Versão 3 --> atualizacao layout - Carlos Abreu - 04/04/2017
		Versão 4 --> Inclusão dos campos reg_del nas consultas - 27/11/2017 - Carlos Abreu
		Versão 5 --> Inclusão de Relatório em excel - 02/01/2018 - Carlos
*/
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(274))
{
	nao_permitido();
}

$conf = new configs();

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$index = "";
$array_mes_values = NULL;
$array_mes_output = NULL;

$array = NULL;

$array[1] = "JANEIRO";
$array[2] = "FEVEREIRO";
$array[3] = "MARÇO";
$array[4] = "ABRIL";
$array[5] = "MAIO"; 
$array[6] = "JUNHO";
$array[7] = "JULHO";
$array[8] = "AGOSTO";
$array[9] = "SETEMBRO";
$array[10] = "OUTUBRO";
$array[11] = "NOVEMBRO";
$array[12] = "DEZEMBRO";

for($i=1;$i<=12;$i++)
{
	$array_mes_values[] = sprintf("%02d",$i);
	$array_mes_output[] = $array[$i];
	if(date("m")==$i)
	{
		$index = sprintf("%02d",$i);
	}
}

$smarty->assign("option_mes_values",$array_mes_values);
$smarty->assign("option_mes_id",$index);
$smarty->assign("option_mes_output",$array_mes_output);

$smarty->assign('campo', $conf->campos('aniversariantes'));

$smarty->assign('revisao_documento', 'V4');

$smarty->assign("classe",CSS_FILE);

$smarty->display('aniversariantes.tpl');
?>