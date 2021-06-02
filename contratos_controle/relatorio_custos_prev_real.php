<?php
/*
		Formulário de Relatorio Custo Prev. x Real	
		
		Criado por Carlos Abreu  
		
		local/Nome do arquivo:
		../contratos_controle/relatorio_custos_prev_real.php
	
		Versão 0 --> VERSÃO INICIAL : 17/09/2013 - Carlos Abreu
		Versão 1 --> Inclusão de filtro por fase - 15/01/2015 - Carlos Abreu
		Versão 2 --> Revisão da interface - 31/05/2016 - Carlos Abreu
		Versão 3 --> atualização layout - Carlos Abreu - 23/03/2017
		
*/	
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(312))
{
	nao_permitido();
}

function preenchecoord($cod_fase)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;

	/*
	$sql = "SELECT PA7_ID, PA7_NOME FROM PA7010 WITH(NOLOCK), AF1010 WITH(NOLOCK), AF2010 WITH(NOLOCK), AF8010 WITH(NOLOCK) ";
	$sql .= "WHERE AF1010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF8010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF2010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF8_ORCAME = AF1_ORCAME ";
	$sql .= "AND AF1_ORCAME = AF2_ORCAME ";
	$sql .= "AND AF2_CODIGO <> '' ";

	if($cod_fase>0)
	{	
		$sql .= "AND AF8_FASE = '".$cod_fase."' ";
	}
	
	$sql .= "AND AF1010.AF1_FASE IN ('04','09') ";
	$sql .= "AND (PA7010.PA7_ID = AF1010.AF1_COORD1 ";
	$sql .= "OR PA7010.PA7_ID = AF1010.AF1_COORD2) ";
	$sql .= "GROUP BY PA7_ID, PA7_NOME ";
	$sql .= "ORDER BY PA7010.PA7_NOME ";

	$db->select($sql,'MSSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	else
	{
		$limp = "xajax.$('escolhacoord').length = null";
		
		$resposta->addScript($limp);
		
		$comb = "xajax.$('escolhacoord').options[xajax.$('escolhacoord').length] = new Option('TODOS','-1');";
	
		foreach($db->array_select as $regs)
		{
			$comb .= "xajax.$('escolhacoord').options[xajax.$('escolhacoord').length] = new Option('". trim($regs["PA7_NOME"])."','". $regs["PA7_ID"] ."');";
		}
	
		$resposta->addScript($comb);
	}
	*/

	return $resposta;
}

function preencheos($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	/*
	$sql = "SELECT AF1_ORCAME, AF1_DESCRI FROM AF1010 WITH(NOLOCK), AF2010 WITH(NOLOCK), AF8010 WITH(NOLOCK) ";	
	$sql .= "WHERE AF1010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF8010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF2010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF1_ORCAME = AF8_ORCAME ";
	$sql .= "AND AF1_ORCAME = AF2_ORCAME ";
	$sql .= "AND AF2_CODIGO <> '' ";
	$sql .= "AND AF1010.AF1_FASE IN ('04','09') ";

	if($dados_form["escolhafase"]>0)
	{	
		$sql .= "AND AF8_FASE = '".$dados_form["escolhafase"]."'  ";
	}	
	
	if($dados_form["escolhacoord"]>0)
	{
		$sql .= "AND (AF1010.AF1_COORD1 = '". $dados_form["escolhacoord"] ."' OR AF1010.AF1_COORD2 = '". $dados_form["escolhacoord"] ."') " ;
	}
	
	$sql .= "GROUP BY AF1_ORCAME, AF1_DESCRI ";
	$sql .= "ORDER BY AF1_ORCAME ";

	$db->select($sql,'MSSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	else
	{
		$limp = "xajax.$('escolhaos').length = null";
		
		$resposta->addScript($limp);
		
		$comb = "xajax.$('escolhaos').options[xajax.$('escolhaos').length] = new Option('TODOS','-1');";
	
		foreach($db->array_select as $regs)
		{
			$comb .= "xajax.$('escolhaos').options[xajax.$('escolhaos').length] = new Option('". trim($regs["AF1_ORCAME"])." - ".trim(addslashes($regs["AF1_DESCRI"]))."','". $regs["AF1_ORCAME"] ."');";
		}
	
		$resposta->addScript($comb);
	}
	*/

	return $resposta;
}

$xajax->registerFunction("preenchecoord");
$xajax->registerFunction("preencheos");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","xajax_preenchecoord(-1);xajax_preencheos(xajax.getFormValues('frm'));");

$conf = new configs();

$db = new banco_dados;

$array_fases_values = NULL;
$array_fases_output = NULL;

$array_fases_values[] = "-1";
$array_fases_output[] = "TODOS";

//fases
/*
$sql = "SELECT AEA_COD, AEA_DESCRI FROM AEA010 WITH(NOLOCK) ";
$sql .= "WHERE AEA010.D_E_L_E_T_ = '' ";
$sql .= "AND AEA_COD NOT IN ('13') ";
$sql .= "ORDER BY AEA_DESCRI ";

$db->select($sql,'MSSQL',true);

if($db->erro!='')
{
	die($db->erro);
}	

foreach($db->array_select as $regs)
{
	$array_fases_values[] = $regs["AEA_COD"];
	$array_fases_output[] = $regs["AEA_DESCRI"];		
}
*/

$smarty->assign("revisao_documento","V3");

$smarty->assign("campo",$conf->campos('relatorio_custo_prev_real'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("option_fases_values",$array_fases_values);
$smarty->assign("option_fases_output",$array_fases_output);

$smarty->assign("classe",CSS_FILE);

$smarty->display('relatorio_custos_prev_real.tpl');

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>