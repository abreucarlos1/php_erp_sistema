<?php
/*
		Formulário do Relatório de Previstos/Executados
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:		
		../coordenacao/relatorio_prevexec.php
		
		Versão 0 --> VERSÃO INICIAL - 24/06/2009
		Versão 1 --> atualização da classe do bannco - 20/01/2015 - Carlos Abreu
		Versão 2 --> atualizção layout - Carlos Abreu - 24/03/2017
		Versão 3 --> Inclusão dos campos reg_del nas consultas - 17/11/2017 - Carlos Abreu		
*/	

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

function preencheos($id_coordenador)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;

	$sql = "SELECT * FROM ".DATABASE.".ordem_servico, ".DATABASE.".ordem_servico_status, ".DATABASE.".empresas ";
	$sql .= "WHERE ordem_servico.id_empresa = empresas.id_empresa ";
	$sql .= "AND ordem_servico_status.reg_del = 0 ";
	$sql .= "AND ordem_servico.reg_del = 0 ";
	$sql .= "AND empresas.reg_del = 0 ";
	$sql .= "AND ordem_servico.id_os_status = ordem_servico_status.id_os_status ";
	$sql .= "AND (ordem_servico.id_cod_coord = '". $id_coordenador ."' OR ordem_servico.id_coord_aux = '". $id_coordenador ."') " ;
	$sql .= "AND ordem_servico_status.id_os_status IN (1,2,4) ";
	$sql .= "GROUP BY ordem_servico.os ";
	$sql .= "ORDER BY ordem_servico.os ";
	
	$db->select($sql,'MYSQL',true);

	$limp = "xajax.$('id_os').length = null";
	
	$resposta->addScript($limp);
	
	$comb = "xajax.$('id_os').options[xajax.$('id_os').length] = new Option('TODAS AS OS','-1');";

	foreach($db->array_select as $os)
	{
		$comb .= "xajax.$('id_os').options[xajax.$('id_os').length] = new Option('". sprintf("%05d",$os["os"])." - ".$os["ordem_servico_cliente"]." - ".$os["empresa"] ."','". $os["id_os"] ."');";
	}

	$resposta->addScript($comb);

	return $resposta;

}

$xajax->registerFunction("preencheos");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$db = new banco_dados;

$filtro = '';

$coordenador = false;

$array_coordenador_values = NULL;
$array_coordenador_output = NULL;

$array_os_values = NULL;
$array_os_output = NULL;

if($coordenador)
{
	$array_coordenador_values[] = "-1";
	$array_coordenador_output[] = "TODOS OS COORDENADORES";
}

$sql = "SELECT * FROM ".DATABASE.".ordem_servico, ".DATABASE.".ordem_servico_status, ".DATABASE.".funcionarios ";
$sql .= "WHERE ordem_servico.id_os_status = ordem_servico_status.id_os_status ";
$sql .= "AND ordem_servico.reg_del = 0 ";
$sql .= "AND ordem_servico_status.reg_del = 0 ";
$sql .= "AND funcionarios.reg_del = 0 ";
$sql .= "AND funcionarios.situacao NOT IN ('DESLIGADO','CANCELADO') ";

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

$sql = "SELECT * FROM ".DATABASE.".ordem_servico, ".DATABASE.".ordem_servico_status, ".DATABASE.".empresas ";
$sql .= "WHERE ordem_servico.id_empresa = empresas.id_empresa ";
$sql .= "AND ordem_servico.reg_del = 0 ";
$sql .= "AND ordem_servico_status.reg_del = 0 ";
$sql .= "AND empresas.reg_del = 0 ";
$sql .= "AND ordem_servico.id_os_status = ordem_servico_status.id_os_status ";
$sql .= $filtro;
$sql .= "AND ordem_servico_status.id_os_status IN (1,2,4) ";
$sql .= "GROUP BY ordem_servico.os ";
$sql .= "ORDER BY ordem_servico.os ";

$db->select($sql,'MYSQL',true);

if($db->numero_registros>0)
{
	$array_os_values[] = "-1";
	$array_os_output[] = "TODAS AS OS";
}
						 
foreach ($db->array_select as $regs)
{
	$array_os_values[] = $regs["id_os"];
	$array_os_output[] =  sprintf("%05d",$regs["os"])." - ".$regs["ordem_servico_cliente"]." - ".$regs["empresa"];
}


$sql = "SELECT * FROM ".DATABASE.".setores ";
$sql .= "AND reg_del = 0 ";
$sql .= "ORDER BY setores.setor ";

$db->select($sql,'MYSQL',true);

$array_setores_values[] = "";
$array_setores_output[] = "TODAS";

foreach($db->array_select as $reg_setor)
{
	$array_setores_values[] = $reg_setor["id_setor"];
	$array_setores_output[] = $reg_setor["setor"];
}

$smarty->assign("option_coordenador_values",$array_coordenador_values);
$smarty->assign("option_coordenador_output",$array_coordenador_output);

$smarty->assign("option_os_values",$array_os_values);
$smarty->assign("option_os_output",$array_os_output);

$smarty->assign("option_setores_values",$array_setores_values);
$smarty->assign("option_setores_output",$array_setores_output);

$smarty->assign("revisao_documento","V3");

$smarty->assign("nome_formulario","RELATÓRIO DOCS. PREV./EXEC");

$smarty->assign("classe",CSS_FILE);

$smarty->display('relatorio_prevexec.tpl');

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>