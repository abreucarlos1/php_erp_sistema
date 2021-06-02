<?php
/*
		Formulário de Relatorio Produtividade	
		
		Criado por Carlos Abreu  
		
		local/Nome do arquivo:
		../contratos_controle/relatorio_produtividade.php
	
		Versão 0 --> VERSÃO INICIAL : 15/07/2014
		Versão 1 --> Incluido a fase no filtro : 04/09/2014 - Carlos Abreu
		Versão 2 --> atualização layout - Carlos Abreu - 24/03/2017
		
*/
	
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(333))
{
	nao_permitido();
}

function disciplina()
{
	$resposta = new xajaxResponse();
	
	$array_setores = NULL;
	
	$db = new banco_dados;

	/*
	$sql = "SELECT AE5_GRPCOM, AE5_DESCRI FROM AE5010 WITH(NOLOCK) ";
	$sql .= "WHERE AE5010.D_E_L_E_T_ = '' ";
	$sql .= "AND AE5_GRPCOM NOT IN ('DES') ";
	$sql .= "GROUP BY AE5_GRPCOM, AE5_DESCRI ";

	$db->select($sql,'MSSQL', true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}

	$limp = "xajax.$('escolhadisciplina').length = null";
	
	$resposta->addScript($limp);
	
	$comb = "xajax.$('escolhadisciplina').options[xajax.$('escolhadisciplina').length] = new Option('TODAS','-1');";

	foreach($db->array_select as $regs1)
	{
		$comb .= "xajax.$('escolhadisciplina').options[xajax.$('escolhadisciplina').length] = new Option('". trim($regs1["AE5_DESCRI"])."','". $regs1["AE5_GRPCOM"] ."');";
	}

	$resposta->addScript($comb);
	*/
	
	return $resposta;	
}

function preenchedisciplinas($id_os)
{

	$resposta = new xajaxResponse();
	
	$array_setores = NULL;
	
	$db = new banco_dados;
	
	if($id_os!=-1)
	{
		/*	
		$sql = "SELECT AE5_GRPCOM, AE5_DESCRI FROM AF9010 WITH(NOLOCK), AF8010 WITH(NOLOCK), AF1010 WITH(NOLOCK), AE5010 WITH(NOLOCK) ";
		$sql .= "WHERE AF8010.D_E_L_E_T_ = '' ";
		$sql .= "AND AF9010.D_E_L_E_T_ = '' ";
		$sql .= "AND AE5010.D_E_L_E_T_ = '' ";
		$sql .= "AND AF1010.D_E_L_E_T_ = '' ";
		$sql .= "AND AE5_GRPCOM = AF9_GRPCOM ";
		$sql .= "AND AF1_ORCAME = AF8_ORCAME ";
		$sql .= "AND AF8_PROJET = '".$id_os."' ";
		$sql .= "AND AF9_PROJET = AF8_PROJET ";
		$sql .= "AND AF9_REVISA = AF8_REVISA ";
		$sql .= "AND AE5_GRPCOM NOT IN ('DES') ";
		$sql .= "GROUP BY AE5_GRPCOM, AE5_DESCRI ";

		$db->select($sql,'MSSQL', true);

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
			
			return $resposta;
		}
	
		$limp = "xajax.$('escolhadisciplina').length = null";
		
		$resposta->addScript($limp);
		
		$comb = "xajax.$('escolhadisciplina').options[xajax.$('escolhadisciplina').length] = new Option('TODAS','-1');";
	
		foreach($db->array_select as $regs1)
		{
			$comb .= "xajax.$('escolhadisciplina').options[xajax.$('escolhadisciplina').length] = new Option('". trim($regs1["AE5_DESCRI"])."','". $regs1["AE5_GRPCOM"] ."');";
		}
	
		$resposta->addScript($comb);
		*/
	
	}
	else
	{
		$resposta->addScript("xajax_disciplina();");	
	}

	return $resposta;
}

function preencheatividades($dados_form)
{	
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	/*
	$sql = "SELECT AF9_GRPCOM, AF9_COMPOS, AE1_DESCRI FROM AF9010 WITH(NOLOCK), AF8010 WITH(NOLOCK), AF1010 WITH(NOLOCK), AE1010 WITH(NOLOCK) "; 
	$sql .= "WHERE AF8010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF9010.D_E_L_E_T_ = '' ";
	$sql .= "AND AE1010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF1010.D_E_L_E_T_ = '' ";
	
	if($dados_form["escolhaos"]!=-1)
	{
		$sql .= "AND AF8_PROJET = '".$dados_form["escolhaos"]."' ";
	}
	
	if($dados_form["escolhadisciplina"]!=-1)
	{
		$sql .= "AND AF9_GRPCOM = '".$dados_form["escolhadisciplina"]."' ";
	}
	
	$sql .= "AND AF1_ORCAME = AF8_ORCAME ";
	$sql .= "AND AF9_PROJET = AF8_PROJET ";
	$sql .= "AND AF9_REVISA = AF8_REVISA ";
	$sql .= "AND AE1_COMPOS = AF9_COMPOS ";
	$sql .= "GROUP BY AF9_GRPCOM, AF9_COMPOS, AE1_DESCRI ";
	$sql .= "ORDER BY AF9_COMPOS, AF9_GRPCOM, AE1_DESCRI ";

	$db->select($sql,'MSSQL', true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}

	$limp = "xajax.$('escolhaatividade').length = null";
	
	$resposta->addScript($limp);
	
	$comb = "xajax.$('escolhaatividade').options[xajax.$('escolhaatividade').length] = new Option('TODAS','-1');";

	foreach($db->array_select as $regs)
	{
		$comb .= "xajax.$('escolhaatividade').options[xajax.$('escolhaatividade').length] = new Option('". trim($regs["AF9_COMPOS"])." - ".trim($regs["AE1_DESCRI"])."','". $regs["AF9_COMPOS"] ."');";
	}

	$resposta->addScript($comb);

	*/

	return $resposta;

}

function preencheos($id_os)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	/*
	$sql = "SELECT AF8_PROJET, AF8_DESCRI FROM AF8010 WITH(NOLOCK), AF1010 WITH(NOLOCK) ";	
	$sql .= "WHERE AF8010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF1010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF1_ORCAME = AF8_ORCAME ";
	$sql .= "AND AF8_FASE = '".$id_os."' ";
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

	$resposta->addScript($comb);
	*/

	return $resposta;

}

$xajax->registerFunction("preencheos");
$xajax->registerFunction("disciplina");
$xajax->registerFunction("preenchedisciplinas");
$xajax->registerFunction("preencheatividades");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","xajax_disciplina();");

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
$sql .= "AND AF1_ORCAME = AF8_ORCAME ";
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

$smarty->assign("revisao_documento","V2");

$smarty->assign("campo",$conf->campos('relatorio_produtividade'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("option_fase_values",$array_fase_values);
$smarty->assign("option_fase_output",$array_fase_output);

$smarty->assign("classe",CSS_FILE);

$smarty->display('relatorio_produtividade.tpl');

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>