<?php
/*
		Formulário de progresso fisico protheus
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		
		../coordenacao/controle_progresso_fisico_protheus.php
		
		Versão 0 --> VERSÃO INICIAL - 21/07/2010
		Versão 1 --> Atualização classe banco de dados - 22/01/2015 - Carlos Abreu
		Versão 2 --> Alteração Lay-out - 18/02/2015 - Carlos Abreu
		Versão 3 --> Atualização layout - Carlos Abreu - 27/03/2017		
*/	

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(211))
{
	nao_permitido();
}

$conf = new configs();

function preencheos($id_coordenador)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	$sql = "SELECT * FROM AF1010 WITH(NOLOCK) ";
	$sql .= "WHERE AF1010.D_E_L_E_T_ = '' ";
	$sql .= "AND (AF1_COORD1 = '". $id_coordenador ."' OR AF1_COORD2 = '". $id_coordenador ."') ";	
	$sql .= "ORDER BY AF1_ORCAME ";
	
	$db->select($sql,'MSSQL', true);
	
	$limp = "xajax.$('escolhaos').length = null";
	
	$resposta->addScript($limp);

	foreach($db->array_select as $os)
	{
		$comb .= "xajax.$('escolhaos').options[xajax.$('escolhaos').length] = new Option('". trim($os["AF1_ORCAME"])." - ".trim(addslashes($os["AF1_DESCRI"]))."','". trim($os["AF1_ORCAME"]) ."');";
	}

	$resposta->addScript($comb);

	return $resposta;
}

$xajax->registerFunction("preencheos");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<?php

$db = new banco_dados;

$array_coordenador_values = NULL;
$array_coordenador_output = NULL;

$array_os_values = NULL;
$array_os_output = NULL;

$array_coordenador_values[] = "";
$array_coordenador_output[] = "SELECIONE";

$sql = "SELECT PA7_ID, PA7_NOME FROM AF1010 WITH(NOLOCK), PA7010 WITH(NOLOCK) ";
$sql .= "WHERE AF1010.D_E_L_E_T_ = '' ";
$sql .= "AND PA7010.D_E_L_E_T_ = '' ";
$sql .= "AND (AF1_COORD1 = PA7_ID OR AF1_COORD2 = PA7_ID) ";
$sql .= "GROUP BY PA7_ID, PA7_NOME ";
$sql .= "ORDER BY PA7_NOME ";

$db->select($sql,'MSSQL', true);
	 
foreach($db->array_select as $regs)
{
	$array_coordenador_values[] = $regs["PA7_ID"];
	$array_coordenador_output[] = $regs["PA7_NOME"];
}

$smarty->assign("option_coordenador_values",$array_coordenador_values);
$smarty->assign("option_coordenador_output",$array_coordenador_output);

$smarty->assign("revisao_documento","V3");

$smarty->assign("campo",$conf->campos('progresso_fisico_protheus'));

$smarty->assign("classe",CSS_FILE);

$smarty->display('controle_progresso_fisico_protheus.tpl');

?>

