<?php
/*
		Formulário de Relatorio Medição	
		
		Criado por Carlos Abreu  
		
		local/Nome do arquivo:
		../contratos_controle/relatorio_medicao.php
	
		Versão 0 --> VERSÃO INICIAL : 12/04/2016 - Carlos Abreu
		Versão 1 --> atualização layout - Carlos Abreu - 23/03/2017
		Versão 2 --> Mudanças das tabelas atuais para as tabelas de BMS - Carlos Abreu - 24/10/2017
		Versão 3 --> Alterações do chamado #2613 - 15/02/2018 - Carlos Abreu
		
*/	
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(556))
{
	nao_permitido();
}

function preenchecoord($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	/*
	$sql = "SELECT PA7_ID, PA7_NOME FROM PA7010 WITH (NOLOCK), AF1010 WITH (NOLOCK) ";
	$sql .= "WHERE AF1010.D_E_L_E_T_ = '' ";
	$sql .= "AND (PA7010.PA7_ID = AF1010.AF1_COORD1 ";
	$sql .= "OR PA7010.PA7_ID = AF1010.AF1_COORD2) ";
	$sql .= "GROUP BY PA7_ID, PA7_NOME ";
	$sql .= "ORDER BY PA7010.PA7_NOME ";

	$db->select($sql,'MSSQL', true);

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


$xajax->registerFunction("preenchecoord");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","xajax_preenchecoord(xajax.getFormValues('frm'));");

$conf = new configs();

$db = new banco_dados;

$smarty->assign("revisao_documento","V3");

$smarty->assign("campo",$conf->campos('relatorio_medicao'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("classe",CSS_FILE);

$smarty->display('relatorio_medicao.tpl');

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>