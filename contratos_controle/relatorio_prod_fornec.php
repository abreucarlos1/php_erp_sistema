<?php
/*
		Formulário de Relatorio Produtos x Fornecedor	
		
		Criado por Carlos Abreu  
		
		local/Nome do arquivo:
		../contratos_controle/relatorio_prod_fornec.php
	
		Versão 0 --> VERSÃO INICIAL : 20/06/2016 - Carlos Abreu
		Versão 1 --> atualização layout - Carlos Abreu - 23/03/2017
		Versão 2 --> Adicionada a os 900 Chamado #1896 - Eduardo - 30/06/2017
		
*/	
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(566))
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
	
	$sql .= "AND AF1010.AF1_FASE IN ('04') ";
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
	$sql = "SELECT DISTINCT AF1_ORCAME, AF1_DESCRI FROM AF1010 WITH(NOLOCK), AF2010 WITH(NOLOCK), AF8010 WITH(NOLOCK), SC7010 WITH(NOLOCK) ";	
	$sql .= "WHERE AF1010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF8010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF2010.D_E_L_E_T_ = '' ";
	$sql .= "AND SC7010.D_E_L_E_T_ = '' ";
	$sql .= "AND SUBSTRING (C7_CLVL,9,10) = AF1_ORCAME ";
	$sql .= "AND C7_CONTA IN (411005001,411006001) ";
	$sql .= "AND AF1_ORCAME = AF8_ORCAME ";
	$sql .= "AND AF1_ORCAME = AF2_ORCAME ";
	$sql .= "AND AF2_CODIGO <> '' ";
	$sql .= "AND AF1010.AF1_FASE IN ('04') ";

	if($dados_form["escolhafase"]>0)
	{	
		$sql .= "AND AF8_FASE = '".$dados_form["escolhafase"]."'  ";
	}	
	
	if($dados_form["escolhacoord"]>0)
	{
		$sql .= "AND (AF1010.AF1_COORD1 = '". $dados_form["escolhacoord"] ."' OR AF1010.AF1_COORD2 = '". $dados_form["escolhacoord"] ."') " ;
	}
	
	//Inclusão pedida pelo Ewerton Paiva no chamado #1896
	//Tentei incluir na consulta acima a OS 900 e ficou extremamente lento, da forma abaixo está bem rápido
	$sql .= "UNION ALL ";
	$sql .= " SELECT AF1_ORCAME, AF1_DESCRI FROM AF1010 WITH(NOLOCK) ";
	$sql .= "WHERE AF1_ORCAME = '0000000900' ";	
	
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
		
		$comb = "xajax.$('escolhaos').options[xajax.$('escolhaos').length] = new Option('SELECIONE','0');";
	
		foreach($db->array_select as $regs)
		{
			$comb .= "xajax.$('escolhaos').options[xajax.$('escolhaos').length] = new Option('". trim($regs["AF1_ORCAME"])." - ".trim($regs["AF1_DESCRI"])."','". $regs["AF1_ORCAME"] ."');";
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

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<?php
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

$smarty->assign("revisao_documento","V1");

$smarty->assign("campo",$conf->campos('relatorio_prod_fornec'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("option_fases_values",$array_fases_values);
$smarty->assign("option_fases_output",$array_fases_output);

$smarty->assign("classe",CSS_FILE);

$smarty->display('relatorio_prod_fornec.tpl');

?>