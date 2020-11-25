<?php
/*
		Formulário de Relatorio Margem de Lucro	
		
		Criado por Carlos Abreu  
		
		local/Nome do arquivo:
		../contratos_controle/relatorio_margem_lucro.php
	
		Versão 0 --> VERSÃO INICIAL : 11/07/2014 - Carlos Abreu
		Versão 1 --> Versão 1 : Inclusão do filtro por fase (#1028) - 04/09/2014 - Carlos Abreu
		
*/	
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(332))
{
	nao_permitido();
}

function preencheos($id_os, $fase)
{
	//$id_os =  A1_COD#A1_LOJA
	
	$cad_cliente = explode("#",$id_os);
	
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	/*
	$sql = "SELECT AF8_PROJET, AF8_DESCRI FROM AF8010 WITH(NOLOCK), AF1010 WITH(NOLOCK) ";	
	$sql .= "WHERE AF8010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF1010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF1_ORCAME = AF8_ORCAME ";
	$sql .= "AND AF8_CLIENT = '".$cad_cliente[0]."' ";
	$sql .= "AND AF8_LOJA = '".$cad_cliente[1]."' ";
	$sql .= "AND AF8_FASE = '".$fase."' ";
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

	foreach($db->array_select as $os)
	{
		$comb .= "xajax.$('escolhaos').options[xajax.$('escolhaos').length] = new Option('". trim($os["AF8_PROJET"])." - ".trim($os["AF8_DESCRI"])."','". $os["AF8_PROJET"] ."');";
	}

	$resposta->addScript($comb);
	*/

	return $resposta;

}

function preenchecliente($fase)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;

	/*
	$sql = "SELECT A1_COD, A1_NOME, A1_NREDUZ, A1_LOJA FROM SA1010 WITH(NOLOCK), AF8010 WITH(NOLOCK), AF1010 WITH(NOLOCK) ";
	$sql .= "WHERE AF8010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF1010.D_E_L_E_T_ = '' ";
	$sql .= "AND SA1010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF1_ORCAME = AF8_ORCAME ";
	$sql .= "AND AF8_FASE = '".$fase."' ";
	$sql .= "AND SA1010.A1_COD = AF8010.AF8_CLIENT ";
	$sql .= "AND SA1010.A1_LOJA = AF8010.AF8_LOJA ";
	$sql .= "GROUP BY A1_COD, A1_NOME, A1_NREDUZ, A1_LOJA ";
	$sql .= "ORDER BY SA1010.A1_NOME ";

	$db->select($sql,'MSSQL', true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}

	$limp = "xajax.$('escolhacliente').length = null";
	
	$resposta->addScript($limp);
	
	$comb = "xajax.$('escolhacliente').options[xajax.$('escolhacliente').length] = new Option('TODOS','-1');";

	foreach($db->array_select as $os)
	{
		$comb .= "xajax.$('escolhacliente').options[xajax.$('escolhacliente').length] = new Option('". trim($os["A1_NOME"])." - ". trim($os["A1_NREDUZ"])."','". $os["A1_COD"]."#".$os["A1_LOJA"] ."');";
	}

	$resposta->addScript($comb);
	*/

	return $resposta;
}


$xajax->registerFunction("preencheos");
$xajax->registerFunction("preenchecliente");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));


?>

<script src="<?php echo ROOT_WEB.'/includes/' ?>validacao.js"></script>

<?php

$conf = new configs();

$db = new banco_dados;

$array_fase_values = NULL;
$array_fase_output = NULL;

$array_fase_values[] = "";
$array_fase_output[] = "SELECIONE";

/*
$sql = "SELECT AEA_COD, AEA_DESCRI FROM AEA010 WITH(NOLOCK), AF8010 WITH(NOLOCK), AF1010 WITH(NOLOCK) ";
$sql .= "WHERE AF8010.D_E_L_E_T_ = '' ";
$sql .= "AND AEA010.D_E_L_E_T_ = '' ";
$sql .= "AND AF1010.D_E_L_E_T_ = '' ";
$sql .= "AND AF8_ORCAME = AF1_ORCAME ";
$sql .= "AND AF8_FASE = AEA_COD ";
$sql .= "GROUP BY AEA_COD, AEA_DESCRI ";
$sql .= "ORDER BY AEA010.AEA_COD ";

$db->select($sql,'MSSQL', true);

if($db->erro!='')
{
	die($db->erro);
}	

foreach($db->array_select as $regs)
{
	$array_fase_values[] = $regs["AEA_COD"];
	$array_fase_output[] = $regs["AEA_DESCRI"];		
}
*/

$smarty->assign("revisao_documento","V1");

$smarty->assign("campo",$conf->campos('relatorio_margem_lucro'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("option_fase_values",$array_fase_values);
$smarty->assign("option_fase_output",$array_fase_output);

$smarty->assign("classe",CSS_FILE);

$smarty->display('relatorio_margem_lucro.tpl');
?>