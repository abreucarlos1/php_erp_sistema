<?php
/*
		Formulário de Acompanhamento Orçamento
		
		Criado por Carlos Abreu
		
		local/Nome do arquivo:		
		../contratos_controle/controle_negociando_protheus.php
		
		Versão 0 --> VERSÃO INICIAL - 25/07/2013
		Versão 1 --> Mudança dos bancos de projetos para orcamento - 12/11/2013 - Carlos Abreu
		Versão 2 --> Atualização classe banco - 20/01/2015 - Carlos Abreu
		Versão 3 --> Atualização - 09/04/2015 - Carlos
		Versão 4 --> Atualização layout - Carlos Abreu - 23/03/2017
		Versão 5 --> Inclusão dos campos reg_del nas consultas - 17/11/2017 - Carlos Abreu
*/
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(310))
{
	nao_permitido();
}

$conf = new configs();

function preencheos($id_coordenador)
{

	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	$limp = "xajax.$('escolhaos').length = null";
	
	$resposta->addScript($limp);
	
	$comb = "xajax.$('escolhaos').options[xajax.$('escolhaos').length] = new Option('TODAS AS OS','-1');";

	//Seleciona as OSs
	/*
	$sql = "SELECT * FROM AF1010 ";
	$sql .= "WHERE AF1010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF1010.AF1_FASE IN ('06') ";
	
	if($id_coordenador!="-1")
	{
		$sql .= "AND (AF1_COORD1 = '". sprintf("%04d",$id_coordenador) ."' OR AF1_COORD2 = '". sprintf("%04d",$id_coordenador) ."') " ;
	}

	$db->select($sql,'MSSQL', true);
	
	foreach($db->array_select as $regs0)
	{
		$comb .= "xajax.$('escolhaos').options[xajax.$('escolhaos').length] = new Option('". $regs0["AF1_ORCAME"]." - ".substr($regs0["AF1_DESCRI"],0,50) ."','". $regs0["AF1_ORCAME"] ."');";
	}
	*/

	$resposta->addScript($comb);
	
	return $resposta;
}

$xajax->registerFunction("preencheos");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$filtro = '';

$coordenador = false;

/*
if (in_array($_SESSION['id_funcionario'], array(6,16,17,19,18,51,49,689,861,737,927,978,1000)))
{
	$coordenador = true;
}
*/

$array_coordenador_values = NULL;
$array_coordenador_output = NULL;

$array_os_values = NULL;
$array_os_output = NULL;

if($coordenador)
{
	$array_coordenador_values[] = "-1";
	$array_coordenador_output[] = "TODOS OS COORDENADORES";
}

$sql = "SELECT id_funcionario, funcionario FROM ".DATABASE.".ordem_servico, ".DATABASE.".ordem_servico_status, ".DATABASE.".funcionarios ";
$sql .= "WHERE ordem_servico.id_os_status = ordem_servico_status.id_os_status ";
$sql .= "AND ordem_servico.reg_del = 0 ";
$sql .= "AND ordem_servico_status.reg_del = 0 ";
$sql .= "AND funcionarios.reg_del = 0 ";
$sql .= "AND funcionarios.situacao NOT IN ('DESLIGADO','CANCELADO') ";
$sql .= "AND funcionarios.nivel_atuacao IN ('D','C') ";

if(!$coordenador)
{
	$sql .= $filtro;
}

$sql .= "AND ordem_servico.id_cod_coord = funcionarios.id_funcionario ";
$sql .= "GROUP BY funcionarios.id_funcionario ";
$sql .= "ORDER BY funcionarios.funcionario ";

$db->select($sql,'MYSQL',true);
	 
foreach ($db->array_select as $regs)
{
	$array_coordenador_values[] = $regs["id_funcionario"];
	$array_coordenador_output[] = $regs["funcionario"];
}

/*
$sql = "SELECT * FROM AF1010 WITH(NOLOCK) ";
$sql .= "WHERE AF1010.D_E_L_E_T_ = '' ";
$sql .= "AND AF1010.AF1_FASE IN ('06') ";
$sql .= "AND AF1_ORCAME > 0000003000 ";
$sql .= "ORDER BY AF1_ORCAME ";

$db->select($sql,'MSSQL', true);

if($db->numero_registros_ms>0)
{
	$array_os_values[] = "-1";
	$array_os_output[] = "TODAS OS ORÇAMENTOS";
}
						 
foreach($db->array_select as $regs)
{
	$array_os_values[] = $regs["AF1_ORCAME"];
	$array_os_output[] =  $regs["AF1_ORCAME"]." - ".substr($regs["AF1_DESCRI"],0,100);
}
*/

$smarty->assign("option_coordenador_values",$array_coordenador_values);
$smarty->assign("option_coordenador_output",$array_coordenador_output);
$smarty->assign("option_os_values",$array_os_values);
$smarty->assign("option_os_output",$array_os_output);

$smarty->assign('campo', $conf->campos('planilha_orcamento_negociando_protheus'));

$smarty->assign('revisao_documento', 'V5');

$smarty->assign("classe",CSS_FILE);

$smarty->display('controle_negociando_protheus.tpl');

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>