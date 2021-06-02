<?php
/*
		Formulário de Controle função protheus
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:		
		../coordenacao/controle_acumulado_os_funcao_protheus.php
		
		Versão 0 --> VERSÃO INICIAL - 02/03/2006
		Versão 1 --> Atualização Lay-out | Smarty : 21/07/2008
		Versão 2 --> Atualização Lay-out: 27/11/2014
		Versão 3 --> atualização layout - Carlos Abreu - 24/03/2017
		Versão 4 --> Inclusão dos campos reg_del nas consultas - 17/11/2017 - Carlos Abreu
*/	

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(209) && !verifica_sub_modulo(285) && !verifica_sub_modulo(261))
{
	nao_permitido();
}

$filtro = '';
$coordenador = false;

/*
if (in_array($_SESSION['id_funcionario'], array(17,19,49,16,51,18,7,6,689,861,927)))
{
	$coordenador = true;
}
*/

function preencheos($id_coordenador)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	$sql = "SELECT * FROM ".DATABASE.".ordem_servico, ".DATABASE.".ordem_servico_status, ".DATABASE.".empresas ";
	$sql .= "WHERE ordem_servico.id_empresa = empresas.id_empresa ";
	$sql .= "AND ordem_servico.reg_del = 0 ";
	$sql .= "AND ordem_servico_status.reg_del = 0 ";
	$sql .= "AND empresas.reg_del = 0 ";
	$sql .= "AND ordem_servico.id_os_status = ordem_servico_status.id_os_status ";
	$sql .= "AND (ordem_servico.id_cod_coord = '". $id_coordenador ."' OR ordem_servico.id_coord_aux = '". $id_coordenador ."') " ;
	$sql .= "AND ordem_servico_status.id_os_status IN (1,2,14,16) ";
	$sql .= "GROUP BY ordem_servico.os ";
	$sql .= "ORDER BY ordem_servico.os ";
	
	$db->select($sql,'MYSQL',true);
	
	if ($db->erro != '')
	{
		$resposta->addAlert("Não foi possível realizar a seleção." . $sql);
	}
	
	$limp = "xajax.$('escolhaos').length = null";
	
	$resposta->addScript($limp);
	
	$comb = "xajax.$('escolhaos').options[xajax.$('escolhaos').length] = new Option('TODAS AS OS','-1');";

	foreach($db->array_select as $os)
	{
		$comb .= "xajax.$('escolhaos').options[xajax.$('escolhaos').length] = new Option('". sprintf("%05d",$os["os"])." - ".substr($os["descricao"],0,50)." - ".$os["empresa"] ."','". $os["id_os"] ."');";
	}

	$resposta->addScript($comb);
	
	return $resposta;
}

function checaData($data, $campo)
{
	$resposta = new xajaxResponse();
	
	$data_array = explode("/", $data);
	
	$dia = $data_array[0];
	$mes = $data_array[1];
	$ano = $data_array[2];
	
	$data_stamp = mktime(0,0,0,$mes, $dia, $ano);
	
	$data_format = getdate($data_stamp);
	
	$dia_semana = $data_format["wday"];
	
	if(!checkdate($mes, $dia, $ano) || $ano<=2005)
	{
		$resposta->addAlert("Data inválida! Favor preencher corretamente.");
		$resposta->addAssign($campo,"value","");
		$resposta->addScript("document.getElementsByName('".$campo."')[0].focus();");
	}

	return $resposta;
}

$xajax->registerFunction("checaData");
$xajax->registerFunction("preencheos");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$conf = new configs();

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
$sql .= "AND funcionarios.nivel_atuacao IN ('D','C') ";

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
	exit("Não foi possível realizar a seleção." . $sql);
}
	 
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
$sql .= "AND ordem_servico_status.id_os_status IN (1,2,14,16) ";
$sql .= "GROUP BY ordem_servico.os ";
$sql .= "ORDER BY ordem_servico.os ";

$db->select($sql, 'MYSQL',true);

if ($db->erro != '')
{
	exit("Não foi possível realizar a seleção." . $sql);
}

if($db->numero_registros > 0)
{
	$array_os_values[] = "-1";
	$array_os_output[] = "TODAS AS OS";
}
						 
foreach ($db->array_select as $regs)
{
	$array_os_values[] = $regs["id_os"];
	$array_os_output[] =  sprintf("%05d",$regs["os"])." - ".substr($regs["descricao"],0,50)." - ".$regs["empresa"];
}

$smarty->assign("option_coordenador_values",$array_coordenador_values);
$smarty->assign("option_coordenador_output",$array_coordenador_output);

$smarty->assign("option_os_values",$array_os_values);
$smarty->assign("option_os_output",$array_os_output);

$smarty->assign("revisao_documento","V4");

$smarty->assign("campo",$conf->campos('acomp_os_func_protheus'));
$smarty->assign("botao",$conf->botoes());

$smarty->assign("classe",CSS_FILE);

$smarty->display('controle_acumulado_os_funcao_protheus.tpl');

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>