<?php
/*
		Formul�rio de MEDIÇÃO / HH / OS / STATUS
		
		Criado por Carlos Abreu / Ot�vio Pamplon ia
		
		local/Nome do arquivo:		
		../planejamento/os_diciplina_hh.php
		
		Vers�o 0 --> VERS�O INICIAL - 02/03/2006
		Vers�o 1 --> Atualiza��o Lay-out | Smarty : 04/08/2008
		Vers�o 2 --> atualiza��o classe banco de dados - 22/01/2015 - Carlos Abreu
		Vers�o 3 --> atualiza��o layout - Carlos Abreu - 31/03/2017
		Vers�o 4 --> Inclus�o dos campos reg_del nas consultas - 20/11/2017 - Carlos Abreu
*/	

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO M�DULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(64))
{
	nao_permitido();
}


function preencheos($status)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	$sql = "SELECT * FROM ".DATABASE.".apontamento_horas, ".DATABASE.".OS, ".DATABASE.".empresas ";
	$sql .= "WHERE apontamento_horas.id_os = OS.id_os ";
	$sql .= "AND apontamento_horas.reg_del = 0 ";
	$sql .= "AND OS.reg_del = 0 ";
	$sql .= "AND empresas.reg_del = 0 ";
	$sql .= "AND OS.id_empresa_erp = empresas.id_empresa_erp ";
	$sql .= "AND OS.id_os_status = '".$status."' ";
	$sql .= "AND os.os > 100 ";
	$sql .= "GROUP BY os.os ";
	$sql .= "ORDER BY os.os ";
	 
	$db->select($sql,'MYSQL',true);
	
	$limp = "xajax.$('escolhaos').length = null";
	
	$resposta->addScript($limp);
	
	if($coordenador)
	{
		$comb = "xajax.$('escolhaos').options[xajax.$('escolhaos').length] = new Option('TODAS AS OS','-1');";
	}
	
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

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<?php

$conf = new configs();

$db = new banco_dados;

$array_status_values = NULL;
$array_status_output = NULL;

$array_status_values[] = "";
$array_status_output[] = "SELECIONE O STATUS";

$sql = "SELECT * FROM ".DATABASE.".ordem_servico_status ";
$sql .= "WHERE ordem_servico_status.reg_del = 0 ";

$db->select($sql,'MYSQL',true);

foreach ($db->array_select as $regs)
{
	$array_status_values[] = $regs["id_os_status"];
	$array_status_output[] = $regs["os_status"];
}


$smarty->assign("option_status_values",$array_status_values);
$smarty->assign("option_status_output",$array_status_output);

$campo[1] = "MEDIÇÃO Hh POR OS POR STATUS";

$smarty->assign("campo",$campo);

$smarty->assign("revisao_documento","V4");

$smarty->assign("classe",CSS_FILE);

$smarty->display('os_disciplina_hh.tpl');	

?>




