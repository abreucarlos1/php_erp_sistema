<?php
/*
		Formulário de Relatorio A1 equivalente	
		
		Criado por Carlos Abreu  
		
		local/Nome do arquivo:
		../contratos_controle/relatorio_a1_equivalente.php
	
		Versão 0 --> VERSÃO INICIAL : 17/09/2013
		Versão 1 --> atualização layout - Carlos Abreu - 23/03/2017
*/	
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(312))
{
	nao_permitido();
}

function preencheos($id_coordenador)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;

	/*
	$sql = "SELECT AF8_PROJET, AF8_DESCRI FROM AF8010 WITH(NOLOCK) ";	
	$sql .= "WHERE AF8010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF8_PROJET > 0000003100 ";
	$sql .= "AND AF8_FASE NOT IN ('01','06','08','09', '10', '13', '17', '18') ";
	
	if($id_coordenador>0)
	{
		$sql .= "AND (AF8010.AF8_COORD1 = '". $id_coordenador ."' OR AF8010.AF8_COORD2 = '". $id_coordenador ."') " ;
	}
	
	$sql .= "GROUP BY AF8_PROJET, AF8_DESCRI ";
	$sql .= "ORDER BY AF8_PROJET ";

	$db->select($sql,'MSSQL', true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}

	$limp = "xajax.$('escolhaos').length = null";
	
	$resposta->addScript($limp);
	
	$comb = "xajax.$('escolhaos').options[xajax.$('escolhaos').length] = new Option('TODOS','-1');";

	foreach($db->array_select as $os)
	{
		$comb .= "xajax.$('escolhaos').options[xajax.$('escolhaos').length] = new Option('". trim($os["AF8_PROJET"])." - ".trim($os["AF8_DESCRI"])."','". $os["AF8_PROJET"] ."');";
	}

	*/

	$resposta->addScript($comb);

	return $resposta;

}


$xajax->registerFunction("preencheos");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","xajax_preencheos(-1);");

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<?php

$conf = new configs();

$db = new banco_dados;

$array_coordenador_values = NULL;
$array_coordenador_output = NULL;

$array_coordenador_values[] = "-1";
$array_coordenador_output[] = "TODOS";

/*
$sql = "SELECT PA7_ID, PA7_NOME FROM PA7010 WITH(NOLOCK), AF8010 WITH (NOLOCK) ";
$sql .= "WHERE AF8010.D_E_L_E_T_ = '' ";
$sql .= "AND AF8_FASE NOT IN ('01','06','08','09', '10', '13', '17', '18') ";
$sql .= "AND (PA7010.PA7_ID = AF8010.AF8_COORD1 ";
$sql .= "OR PA7010.PA7_ID = AF8010.AF8_COORD2) ";
$sql .= "GROUP BY PA7_ID, PA7_NOME ";
$sql .= "ORDER BY PA7010.PA7_NOME ";

$db->select($sql,'MSSQL',true);

if($db->erro!='')
{
	die($db->erro);
}	

foreach($db->array_select as $regs)
{
	$array_coordenador_values[] = $regs["PA7_ID"];
	$array_coordenador_output[] = $regs["PA7_NOME"];		
}
*/

$smarty->assign("revisao_documento","V1");

$smarty->assign("campo",$conf->campos('relatorio_a1_equivalente'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("option_coordenador_values",$array_coordenador_values);
$smarty->assign("option_coordenador_output",$array_coordenador_output);

$smarty->assign("classe",CSS_FILE);

$smarty->display('relatorio_a1_equivalente.tpl');

?>

