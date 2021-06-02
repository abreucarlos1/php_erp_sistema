<?php
/*
		Formulário de A1 Equivalente
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:		
		../indices/a1_equivalente.php
		
		Versão 0 --> VERSÃO INICIAL - 02/03/2006
		Versão 1 --> Atualização Lay-out | Smarty : 21/07/2008
		Versão 2 --> atualização banco de dados - 21/01/2015 - Carlos Abreu
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
		$resposta->addScript("combo_destino.options[combo_destino.length] = new Option('".$regs["os"]." - ".substr($regs["descricao"],0,60). "', '".$regs["id_os"]."');");
	}
	
	return $resposta;

}

$xajax->registerFunction("seleciona_os");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$conf = new configs();

$db = new banco_dados;

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
 
foreach($db->array_select as $regs)
{
	$array_status_values[] = $regs["id_os_status"];
	$array_status_output[] = $regs["os_status"];
}

$sql = "SELECT * FROM ".DATABASE.".numeros_interno, ".DATABASE.".setores  ";
$sql .= "WHERE numeros_interno.id_disciplina = setores.id_setor ";
$sql .= "AND numeros_interno.reg_del = 0 ";
$sql .= "AND setores.reg_del = 0 ";
$sql .= "GROUP BY setores.id_setor ";
$sql .= "ORDER BY setores.setor ";

$db->select($sql,'MYSQL',true);

$check = "";
	 
foreach ($db->array_select as $regs)
{
	$check .= "<input type=\"checkbox\" id=\"chk_".$regs["id_setor"]."\" name=\"chk_".$regs["id_setor"]."\" value=\"checkbox\">  <label class=\"label_descricao_campos\">".$regs["setor"]."</label><br>";
}

$smarty->assign("option_status_values",$array_status_values);
$smarty->assign("option_status_output",$array_status_output);

$smarty->assign("check_box",$check);

$smarty->assign('campo', $conf->campos('a1_equivalente'));

$smarty->assign('revisao_documento', 'V4');

$smarty->assign("classe",CSS_FILE);

$smarty->display('a1_equivalente.tpl');

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>