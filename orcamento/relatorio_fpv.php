<?php
/*
		Formulário de Relatorio Formação Valores	
		
		Criado por Carlos Abreu  
		
		local/Nome do arquivo:
		../orcamento/relatorio_fpv.php
	
		Versão 0 --> VERSÃO INICIAL : 17/09/2013
		
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
	$sql = "SELECT AF1_ORCAME, AF1_DESCRI FROM AF1010, AF2010, AF8010 ";	
	$sql .= "WHERE AF1010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF8010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF2010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF1_ORCAME = AF8_ORCAME ";
	$sql .= "AND AF1_ORCAME = AF2_ORCAME ";
	$sql .= "AND AF2_CODIGO <> '' ";
	$sql .= "AND AF8_ENCPRJ <> '1' ";
	$sql .= "AND AF8_FASE IN ('02','03','07','09') ";
	$sql .= "AND AF1010.AF1_FASE IN ('04') ";
	
	if($id_coordenador>0)
	{
		$sql .= "AND (AF1010.AF1_COORD1 = '". $id_coordenador ."' OR AF1010.AF1_COORD2 = '". $id_coordenador ."') " ;
	}
	
	$sql .= "GROUP BY AF1_ORCAME, AF1_DESCRI ";
	$sql .= "ORDER BY AF1_ORCAME ";
	
	$db->select($sql,'MSSQL', true);
	
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
			$comb .= "xajax.$('escolhaos').options[xajax.$('escolhaos').length] = new Option('". trim($regs["AF1_ORCAME"])." - ".trim($regs["AF1_DESCRI"])."','". $regs["AF1_ORCAME"] ."');";

		}
	
		$resposta->addScript($comb);
	}
	*/

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
$sql = "SELECT PA7_ID, PA7_NOME FROM PA7010, AF1010, AF2010, AF8010 ";
$sql .= "WHERE AF1010.D_E_L_E_T_ = '' ";
$sql .= "AND AF8010.D_E_L_E_T_ = '' ";
$sql .= "AND AF2010.D_E_L_E_T_ = '' ";
$sql .= "AND AF8_ORCAME = AF1_ORCAME ";
$sql .= "AND AF1_ORCAME = AF2_ORCAME ";
$sql .= "AND AF8_ENCPRJ <> '1' ";
$sql .= "AND AF2_CODIGO <> '' ";
$sql .= "AND AF8_FASE IN ('02','03','07','09') ";
$sql .= "AND AF1010.AF1_FASE IN ('04') ";
$sql .= "AND (PA7010.PA7_ID = AF1010.AF1_COORD1 ";
$sql .= "OR PA7010.PA7_ID = AF1010.AF1_COORD2) ";
$sql .= "GROUP BY PA7_ID, PA7_NOME ";
$sql .= "ORDER BY PA7010.PA7_NOME ";

$db->select($sql,'MSSQL', true);

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

$smarty->assign("revisao_documento","V0");

$smarty->assign("campo",$conf->campos('relatorio_custo_prev_real'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("option_coordenador_values",$array_coordenador_values);
$smarty->assign("option_coordenador_output",$array_coordenador_output);

$smarty->assign("classe",CSS_FILE);

$smarty->display('relatorio_custos_prev_real.tpl');

?>

