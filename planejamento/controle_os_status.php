<?php
/*
		Formulário de OS POR STATUS
		
		Criado por Carlos Abreu
		
		local/Nome do arquivo:		
		../planejamento/controle_os_status.php
		
		Versão 0 --> VERSÃO INICIAL - 02/03/2006
		Versão 1 --> Atualização Lay-out | Smarty : 05/08/2008
		Versao 2 --> Atualização banco de dados - 22/01/2015 - Carlos Abreu	
		Versão 3 --> Atualização layout - Carlos Abreu - 31/03/2017
		Versão 4 --> Inclusão dos campos reg_del nas consultas - 20/11/2017 - Carlos Abreu	
*/	

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<?php

$conf = new configs();

$db = new banco_dados;

$array_status_values = NULL;
$array_status_output = NULL;

$sql = "SELECT * FROM ".DATABASE.".ordem_servico_status, ".DATABASE.".ordem_servico ";
$sql .= "WHERE ordem_servico_status.id_os_status = OS.id_os_status ";
$sql .= "AND ordem_servico_status.reg_del = 0 ";
$sql .= "AND ordem_servico.reg_del = 0 ";
$sql .= "GROUP BY ordem_servico_status.id_os_status ";
$sql .= "ORDER BY os_status ";

$db->select($sql,'MYSQL',true);

foreach ($db->array_select as $regs)
{
	$array_status_values[] = $regs["id_os_status"];
	$array_status_output[] = $regs["os_status"];
}


$smarty->assign("option_status_values",$array_status_values);
$smarty->assign("option_status_output",$array_status_output);

$campo[1] = "OS POR STATUS";

$smarty->assign("campo",$campo);

$smarty->assign("revisao_documento",'V4');

$smarty->assign("classe",CSS_FILE);

$smarty->display('controle_os_status.tpl');	

?>




