<?php
/*
		Formulário de documentos aprovados
		
		Criado por Carlos Abreu
		
		local/Nome do arquivo:		
		../arquivotec/controle_doc_apr_coment.php
		
		Versão 0 --> VERSÃO INICIAL - 02/03/2006
		Versão 1 --> Atualização Lay-out | Smarty : 21/07/2008
		Versão 2 --> Inclusão de status de devolução 
		Versão 3 --> Atualização do layout nov/2014
		Versão 4 --> atualização layout - Carlos Abreu - 22/03/2017
		Versão 5 --> Inclusão dos campos reg_del nas consultas - 14/11/2017 - Carlos Abreu
*/	

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));

require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(200))
{
    nao_permitido();
}

$conf = new configs();

$filtro = '';

$coordenador = true;

function preencheos($id_coordenador)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados();

	$sql = "SELECT * FROM ".DATABASE.".ordem_servico, ".DATABASE.".ordem_servico_status, ".DATABASE.".empresas ";
	$sql .= "WHERE ordem_servico.id_empresa = empresas.id_empresa ";
	$sql .= "AND ordem_servico.reg_del = 0 ";
	$sql .= "AND ordem_servico_status.reg_del = 0 ";
	$sql .= "AND empresas.reg_del = 0 ";
	$sql .= "AND ordem_servico.id_os_status = ordem_servico_status.id_os_status ";
	$sql .= "AND ordem_servico.os > 1700  ";
	$sql .= "AND ordem_servico_status.id_os_status IN (1,2,4) ";
	$sql .= "GROUP BY ordem_servico.os ";
	$sql .= "ORDER BY ordem_servico.os ";
	
	$db->select($sql,'MYSQL',true);
	
	if ($db->erro != '')
	{
		$resposta->addAlert("Não foi possível realizar a seleção." . $db->erro);
	}
	
	$limp = "xajax.$('escolhaos').length = null";
	
	$resposta->addScript($limp);
	
	$comb = "xajax.$('escolhaos').options[xajax.$('escolhaos').length] = new Option('TODAS AS OS','-1');";

	foreach($db->array_select as $os)
	{
		$comb .= "xajax.$('escolhaos').options[xajax.$('escolhaos').length] = new Option('". sprintf("%05d",$os["os"])." - ".$os["ordem_servico_cliente"]." - ".$os["empresa"] ."','". $os["id_os"] ."');";
	}

	$resposta->addScript($comb);
	
	return $resposta;
}

$xajax->registerFunction("preencheos");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$array_coordenador_values = NULL;
$array_coordenador_output = NULL;

$array_os_values = NULL;
$array_os_output = NULL;

$array_disciplina_values = NULL;
$array_disciplina_output = NULL;

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
$sql .= "AND funcionarios.nivel_atuacao IN ('D','C') ";
$sql .= "AND ordem_servico.os > 1700 ";
$sql .= "AND ordem_servico_status.id_os_status IN (1,2) ";

if(!$coordenador)
{
	$sql .= $filtro;
}

$sql .= "AND ordem_servico.id_cod_coord = funcionarios.id_funcionario ";
$sql .= "GROUP BY funcionarios.id_funcionario ";
$sql .= "ORDER BY funcionarios.funcionario ";

$db->select($sql,'MYSQL',true);

if ($db->erro != '')
{
	exit("Não foi possível realizar a seleção." . $db->erro);
}
	 
foreach($db->array_select as $regs)
{
	$array_coordenador_values[] = $regs["id_funcionario"];
	$array_coordenador_output[] = $regs["funcionario"];
}

$array_disciplina_values[] = "";
$array_disciplina_output[] = "TODAS";

$sql = "SELECT * FROM ".DATABASE.".setores ";
$sql .= "WHERE id_setor NOT IN (1,2,3,4,6,11,17,18,19,21,24) ";
$sql .= "AND setores.reg_del = 0 ";
$sql .= "ORDER BY setor ";

$db->select($sql,'MYSQL',true);

if ($db->erro != '')
{
	exit("Não foi possível realizar a seleção." . $db->erro);
}
	 
foreach($db->array_select as $regs)
{
	$array_disciplina_values[] = $regs["id_setor"];
	$array_disciplina_output[] = $regs["setor"];
}

$sql = "SELECT * FROM ".DATABASE.".ordem_servico, ".DATABASE.".ordem_servico_status, ".DATABASE.".empresas ";
$sql .= "WHERE ordem_servico.id_empresa = empresas.id_empresa ";
$sql .= "AND ordem_servico.reg_del = 0 ";
$sql .= "AND ordem_servico_status.reg_del = 0 ";
$sql .= "AND empresas.reg_del = 0 ";
$sql .= "AND ordem_servico.id_os_status = ordem_servico_status.id_os_status ";
$sql .= "AND ordem_servico.os > 1700 ";
$sql .= $filtro;
$sql .= "AND ordem_servico_status.id_os_status IN (1,2,4) ";
$sql .= "GROUP BY ordem_servico.os ";
$sql .= "ORDER BY ordem_servico.os ";

$db->select($sql,'MYSQL',true);

if ($db->erro != '')
{
	exit("Não foi possível realizar a seleção." . $db->erro);
}

if($db->numero_registros > 0)
{
	$array_os_values[] = "-1";
	$array_os_output[] = "TODAS AS OS";
}
						 
foreach($db->array_select as $regs)
{
	$array_os_values[] = $regs["id_os"];
	$array_os_output[] =  sprintf("%05d",$regs["os"])." - ".$regs["ordem_servico_cliente"]." - ".$regs["empresa"];
}

$smarty->assign("option_coordenador_values",$array_coordenador_values);
$smarty->assign("option_coordenador_output",$array_coordenador_output);

$smarty->assign("option_os_values",$array_os_values);
$smarty->assign("option_os_output",$array_os_output);

$smarty->assign("option_disciplina_values",$array_disciplina_values);
$smarty->assign("option_disciplina_output",$array_disciplina_output);

$smarty->assign("revisao_documento","V5");

$smarty->assign("campo",$conf->campos('documentos_aprovados'));

$smarty->assign("classe",CSS_FILE);

$smarty->display('controle_doc_apr_coment.tpl');

?>
<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>