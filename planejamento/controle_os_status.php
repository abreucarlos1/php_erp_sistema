<?php
/*
		Formul�rio de OS POR STATUS
		
		Criado por Carlos Abreu / Ot�vio Pamplon ia
		
		local/Nome do arquivo:		
		../planejamento/controle_os_status.php
		
		Vers�o 0 --> VERS�O INICIAL - 02/03/2006
		Vers�o 1 --> Atualiza��o Lay-out | Smarty : 05/08/2008
		Versao 2 --> atualiza��o banco de dados - 22/01/2015 - Carlos Abreu	
		Vers�o 3 --> atualiza��o layout - Carlos Abreu - 31/03/2017
		Vers�o 4 --> Inclus�o dos campos reg_del nas consultas - 20/11/2017 - Carlos Abreu	
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

$sql = "SELECT * FROM ".DATABASE.".ordem_servico_status, ".DATABASE.".OS ";
$sql .= "WHERE ordem_servico_status.id_os_status = OS.id_os_status ";
$sql .= "AND ordem_servico_status.reg_del = 0 ";
$sql .= "AND OS.reg_del = 0 ";
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




