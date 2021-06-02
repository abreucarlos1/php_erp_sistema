<?php
/*
		Formulário de Controle Propostas Protheus
		
		Criado por Carlos Abreu 
		
		local/Nome do arquivo:
		
		../contratos_controle/controle_propostas_protheus.php
		
		Versão 0 --> VERSÃO INICIAL - 08/04/2013 - Carlos Abreu
		Versão 1 --> atualização classe banco - 20/01/2015 - Carlos Abreu
		Versão 2 --> Atualização - 09/04/2015 - Eduardo
		Versão 3 --> atualização layout - Carlos Abreu - 23/03/2017
		Versão 4 --> Inclusão dos campos reg_del nas consultas - 17/11/2017 - Carlos Abreu
*/
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(280))
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

	/*
	//Seleciona as OSs
	$sql = "SELECT * FROM AF1010 WITH(NOLOCK) ";
	$sql .= "WHERE AF1010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF1010.AF1_FASE IN ('01') ";
	
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

$db = new banco_dados;

$filtro = '';

$coordenador = false;

/*
if (in_array($_SESSION['id_funcionario'], array(6,16,17,19,18,51,49,689,861,871,927,978,1000,1102)))
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
//$sql .= "AND os.os > 2000 ";

if(!$coordenador)
{
	$sql .= $filtro;
}

$sql .= "AND OS.id_cod_coord = funcionarios.id_funcionario ";
$sql .= "GROUP BY funcionarios.id_funcionario ";
$sql .= "ORDER BY funcionarios.funcionario ";

$db->select($sql,'MYSQL',true);
	 
foreach ($db->array_select as $regs)
{
	$array_coordenador_values[] = $regs["id_funcionario"];
	$array_coordenador_output[] = $regs["funcionario"];
}

/*
$sql = "SELECT * FROM AF1010 WITH(NOLOCK), SA1010 WITH(NOLOCK) ";
$sql .= "WHERE AF1010.D_E_L_E_T_ = '' ";
$sql .= "AND SA1010.D_E_L_E_T_ = '' ";
$sql .= "AND AF1_CLIENT = A1_COD ";
$sql .= "AND AF1_LOJA = A1_LOJA ";
$sql .= "AND AF1_FASE IN ('01') ";
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

$check = '';

$sql = "SELECT * FROM ".DATABASE.".setores ";
$sql .= "WHERE setores.reg_del = 0 ";
$sql .= "ORDER BY setor ";

$db->select($sql,'MYSQL',true);

$check .= '<label class="labels"><input type="checkbox" name="chk_TODOS" id="chk_TODOS" value="-1" onclick="if(this.checked){setcheckbox("frm_rel","check");}else{setcheckbox("frm_rel","");}">TODOS</label><br>';

foreach($db->array_select as $reg)
{
	$check .= '<label class="labels"><input type="checkbox" name="chk_'.$reg["id_setor"].'" id="chk_'.$reg["id_setor"].'" value="1" />'.$reg["setor"].'</label><br>';
}

$smarty->assign("check_equipe",$check);

$smarty->assign("option_coordenador_values",$array_coordenador_values);
$smarty->assign("option_coordenador_output",$array_coordenador_output);

$smarty->assign("option_os_values",$array_os_values);
$smarty->assign("option_os_output",$array_os_output);

$smarty->assign('campo', $conf->campos('planilha_vendas_geral_protheus'));

$smarty->assign('revisao_documento', 'V4');

$smarty->assign("classe",CSS_FILE);

$smarty->display('controle_propostas_protheus.tpl');

?>
<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>