<?php
/*
		Formulário de Coordenador Por OS Por Funcionários
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		
		../coordenacao/controle_coordenador_os_funcionarios.php
		
		Versão 0 --> VERSÃO INICIAL - 02/03/2006
		Versão 1 --> Atualização Lay-out | Smarty : 21/07/2008
		Versão 2 --> Atualização banco de dados - 22/01/2015 - Carlos Abreu
		Versão 3 --> Atualização layout - Carlos Abreu - 31/03/2017
		Versão 4 --> Inclusão dos campos reg_del nas consultas - 20/11/2017 - Carlos Abreu		
*/	

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(62) || !verifica_sub_modulo(159))
{
	nao_permitido();
}


?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<?php

$conf = new configs();

$db = new banco_dados;

$array_coordenador_values = NULL;
$array_coordenador_output = NULL;

$sql = "SELECT * FROM ".DATABASE.".ordem_servico, ".DATABASE.".funcionarios, ".DATABASE.".ordem_servico_status ";
$sql .= "WHERE ordem_servico.id_cod_coord = funcionarios.id_funcionario ";
$sql .= "AND ordem_servico.reg_del = 0 ";
$sql .= "AND funcionarios.reg_del = 0 ";
$sql .= "AND ordem_servico_status.reg_del = 0 ";
$sql .= "AND ordem_servico.id_os_status = ordem_servico_status.id_os_status ";
$sql .= "AND funcionarios.situacao NOT IN ('DESLIGADO','CANCELADO') ";
$sql .= "AND ordem_servico_status.id_os_status NOT IN (1,2,8,9,12) ";
$sql .= "GROUP BY id_cod_coord ";
$sql .= "ORDER BY funcionario ";

$db->select($sql,'MYSQL',true);
 
foreach ($db->array_select as $regs)
{
	$array_coordenador_values[] = $regs["id_cod_coord"];
	$array_coordenador_output[] = $regs["funcionario"];
}

$smarty->assign("option_coordenador_values",$array_coordenador_values);
$smarty->assign("option_coordenador_output",$array_coordenador_output);

$campo[1] = "COORDENADOR POR OS POR FUNCIONÁRIOS";

$smarty->assign("campo",$campo);

$smarty->assign("revisao_documento","V4");

$smarty->assign("classe",CSS_FILE);

$smarty->display('controle_coordenador_os_funcionarios.tpl');

?>