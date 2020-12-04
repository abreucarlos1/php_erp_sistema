<?php
/*
		Formulário de HORAS POR PERÍODO	
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		../planejamento/relatorios_horas.php
		
		Versão 0 --> VERSÃO INICIAL : 02/03/2006		
		Versao 1 --> Atualização classe banco de dados - 22/01/2015 - Carlos Abreu
		Versão 2 --> Atualização Layout - 01/04/2015 - Eduardo
		Versão 3 --> Atualização layout - Carlos Abreu - 03/04/2017
		Versão 4 --> Inclusão dos campos reg_del nas consultas - 20/11/2017 - Carlos Abreu
*/	

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(50) && !verifica_sub_modulo(270) && !verifica_sub_modulo(290) && !verifica_sub_modulo(261))
{
	nao_permitido();
}

$conf = new configs();

$db = new banco_dados;

function escolhaos($id_funcionario)
{
	$resposta = new xajaxResponse();

	$db = new banco_dados;
	
	$resposta->addScript("combo_destino = document.getElementById('os');");
	
	$resposta->addScriptCall("limpa_combo('os')");
	
	$resposta->addScript("combo_destino.options[combo_destino.length] = new Option('TODAS', '-1');");
	
	$sql = "SELECT * FROM ".DATABASE.".apontamento_horas, ".DATABASE.".ordem_servico ";
	$sql .= "WHERE apontamento_horas.id_funcionario = '".$id_funcionario."' ";
	$sql .= "AND apontamento_horas.reg_del = 0 ";
	$sql .= "AND ordem_servico.reg_del = 0 ";
	$sql .= "AND apontamento_horas.id_os = ordem_servico.id_os ";
	$sql .= "GROUP BY ordem_servico.id_os ";
	$sql .= "ORDER BY ordem_servico.os ";

	$db->select($sql,'MYSQL',true);
	
	foreach ($db->array_select as $regs)
	{
		$resposta->addScript("combo_destino.options[combo_destino.length] = new Option('".sprintf("%05d",$regs["os"])."', '".$regs["id_os"]."');");
	}		
	
	return $resposta;
}

$xajax->registerFunction("escolhaos");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

?>
<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>datetimepicker/datetimepicker_css.js"></script>

<script language="javascript">
function alternaAction()
{
	cmb_formato = document.getElementById('formato');
	frm_horas = document.getElementById('frm_rel');
	
	switch(cmb_formato.options[cmb_formato.options.selectedIndex].value)
	{
		case "1":
			frm_horas.action = 'relatorios/rel_controlehoras_periodo.php';
		break;
		
		case "2":
			frm_horas.action = 'relatorios/controlehorasperiodo_assinaturas.php';	
		break;
	}
	
	frm_horas.submit();
}
</script>

<?php
$array_funcionario_values = NULL;
$array_funcionario_output = NULL;

$array_funcionario_values[] = '';
$array_funcionario_output[] = 'ESCOLHA O COLABORADOR';

$sql = "SELECT * FROM ".DATABASE.".funcionarios ";
$sql .= "WHERE funcionarios.situacao NOT IN ('DESLIGADO','CANCELADO') ";
$sql .= "AND funcionarios.reg_del = 0 ";
$sql .= "ORDER BY funcionario ";

$db->select($sql,'MYSQL',true);
 
foreach ($db->array_select as $regs)
{
	$array_funcionario_values[] = $regs["id_funcionario"];
	$array_funcionario_output[] = $regs["funcionario"];
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

$smarty->assign("option_funcionario_values",$array_funcionario_values);
$smarty->assign("option_funcionario_output",$array_funcionario_output);

$smarty->assign("data",date("d/m/Y"));

$smarty->assign('campo', $conf->campos('horas_periodo'));

$smarty->assign('revisao_documento', 'V4');

$smarty->assign("classe",CSS_FILE);

$smarty->display('relatorio_horas.tpl');
?>