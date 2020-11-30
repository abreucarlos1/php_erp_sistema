<?php
/*
		Formulário de HORAS DE RETRABALHO	
		
		Criado por Carlos Abreu  
		
		local/Nome do arquivo:
		../indices/horas_retrabalho.php
		
		Versão 0 --> VERSÃO INICIAL : 23/09/2010
		Versão 1 --> atualização classe banco de dados -21/01/2015 - Carlos Abreu
		Versão 2 --> Atualização Layout : 10/04/2015 - Eduardo
		Versão 3 --> atualização layout - Carlos Abreu - 28/03/2017
		Versão 4 --> Inclusão dos campos reg_del nas consultas - 20/11/2017 - Carlos Abreu
*/	

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

function seleciona_os($id_os_status)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	$resposta->addScript("combo_destino = document.getElementById('os');");
	
	$resposta->addScriptCall("limpa_combo('os')");	
	
	$sql = "SELECT * FROM ".DATABASE.".apontamento_horas, ".DATABASE.".ordem_servico, ".DATABASE.".ordem_servico_status ";
	$sql .= "WHERE apontamento_horas.id_os = ordem_servico.id_os ";
	$sql .= "AND apontamento_horas.reg_del = 0 ";
	$sql .= "AND ordem_servico.reg_del = 0 ";
	$sql .= "AND ordem_servico_status.reg_del = 0 ";	
	$sql .= "AND ordem_servico.id_os_status = '".$id_os_status."' ";
	$sql .= "GROUP BY ordem_servico.os ORDER BY ordem_servico.os ";
	
	$db->select($sql,'MYSQL',true);
	
	if ($db->erro != '')
	{
		die("Não foi possível realizar a seleção." . $sql);
	}
	 
	foreach ($db->array_select as $regs)
	{
		$resposta->addScript("combo_destino.options[combo_destino.length] = new Option('".$regs["os"]." - ".substr($regs["descricao"],0,60)."', '".$regs["id_os"]."');");
	}
	
	return $resposta;

}

$xajax->registerFunction("seleciona_os");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>datetimepicker.js"></script>

<?php
$conf = new configs();

$array_os_values = NULL;
$array_os_output = NULL;

$array_status_values = NULL;
$array_status_output = NULL;

$array_status_values[] = "-1";
$array_status_output[] = "TODOS";

$sql = "SELECT * FROM ".DATABASE.".apontamento_horas, ".DATABASE.".ordem_servico_status, ".DATABASE.".ordem_servico ";
$sql .= "WHERE ordem_servico.id_os_status = ordem_servico_status.id_os_status ";
$sql .= "AND apontamento_horas.reg_del = 0 ";
$sql .= "AND ordem_servico.reg_del = 0 ";
$sql .= "AND ordem_servico_status.reg_del = 0 ";	
$sql .= "AND ordem_servico.id_os = apontamento_horas.id_os ";
$sql .= "GROUP BY ordem_servico_status.id_os_status ";
$sql .= "ORDER BY ordem_servico_status.ordem ";

$db->select($sql,'MYSQL',true);
 
foreach ($db->array_select as $regs)
{
	$array_status_values[] = $regs["id_os_status"];
	$array_status_output[] = $regs["os_status"];
}

$array = array("JANEIRO","FEVEREIRO","MARÇO","ABRIL","MAIO","JUNHO","JULHO","AGOSTO","SETEMBRO","OUTUBRO","NOVEMBRO","DEZEMBRO");

for($i=1;$i<=12;$i++)
{
	$array_per_values[] = sprintf("%02d",$i);
	$array_per_output[] = $array[$i-1];
	if(date("m")==$i)
	{
		$index = sprintf("%02d",$i);
	}
}

$smarty->assign("option_per_values",$array_per_values);
$smarty->assign("option_per_id",$index);
$smarty->assign("option_per_output",$array_per_output);

$smarty->assign("option_status_values",$array_status_values);
$smarty->assign("option_status_output",$array_status_output);

$smarty->assign("data",date("d/m/Y"));

$smarty->assign('campo', $conf->campos('horas_retrabalho'));

$smarty->assign('revisao_documento', 'V4');

$smarty->assign("classe",CSS_FILE);

$smarty->display('horas_retrabalho.tpl');
?>