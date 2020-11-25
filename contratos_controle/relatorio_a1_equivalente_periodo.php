<?php
/*
		Formulário de Relatorio A1 equivalente periodo	
		
		Criado por Carlos Abreu  
		
		local/Nome do arquivo:
		../contratos_controle/relatorio_a1_equivalente_periodo.php
	
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
?>

<script src="<?php echo ROOT_WEB.'/includes/' ?>validacao.js"></script>

<?php
$conf = new configs();

$db = new banco_dados;

$sql = "SELECT * FROM ".DATABASE.".codigos_emissao";

$db->select($sql, 'MYSQL', true);

$tiposEmissao = $db->array_select;

$smarty->assign("tiposEmissao",$tiposEmissao);

$smarty->assign("revisao_documento","V1");

$smarty->assign("campo",$conf->campos('relatorio_a1_equivalente_periodo'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("classe",CSS_FILE);

$smarty->display('relatorio_a1_equivalente_periodo.tpl');
?>