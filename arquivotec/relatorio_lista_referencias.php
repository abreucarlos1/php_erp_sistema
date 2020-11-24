<?php
/*
		Formulário de Relatorio Lista de Referencias	
		
		Criado por Carlos Abreu  
		
		local/Nome do arquivo:
		../arquivotec/relatorio_lista_referencias.php
	
		Versão 0 --> VERSÃO INICIAL : 23/05/2016
		Versão 1 --> atualização layout - Carlos Abreu - 22/03/2017
		Versão 2 --> Inclusão dos campos reg_del nas consultas - 16/11/2017 - Carlos Abreu
		
*/	
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));

require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(562))
{
	nao_permitido();
}

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

?>

<script src="<?php echo ROOT_WEB.'/includes/' ?>validacao.js"></script>

<?php

$conf = new configs();

$db = new banco_dados;

//$lista_usuarios_irrestritos = array(6,49,909,910,978,871,1046,226);

if(!in_array($_SESSION["id_funcionario"], $lista_usuarios_irrestritos))
{
	$sql = "SELECT ordem_servico.os, ordem_servico.id_os, ordem_servico.descricao FROM ".DATABASE.".ordem_servico, ".DATABASE.".ordem_servico_status, ".DATABASE.".os_x_funcionarios, ".DATABASE.".documentos_referencia ";
	$sql .= "WHERE ordem_servico.id_os_status = ordem_servico_status.id_os_status ";
	$sql .= "AND documentos_referencia.reg_del = 0 ";
	$sql .= "AND ordem_servico.reg_del = 0 ";
	$sql .= "AND ordem_servico_status.reg_del = 0 ";
	$sql .= "AND os_x_funcionarios.reg_del = 0 ";
	$sql .= "AND documentos_referencia.id_os = ordem_servico.id_os ";
	$sql .= "AND ordem_servico.id_os = os_x_funcionarios.id_os ";
	$sql .= "AND os_x_funcionarios.id_funcionario = '" . $_SESSION["id_funcionario"] . "' ";
	$sql .= "AND ordem_servico_status.id_os_status IN (1,2,14,16) ";
	//$sql .= "AND ordem_servico.os > 3100 ";	
	$sql .= "GROUP BY ordem_servico.id_os ";
	$sql .= "ORDER BY ordem_servico.os ";
}
else
{
	$sql = "SELECT ordem_servico.os, ordem_servico.id_os, ordem_servico.descricao FROM ".DATABASE.".ordem_servico, ".DATABASE.".ordem_servico_status, ".DATABASE.".documentos_referencia "; 
	$sql .= "WHERE ordem_servico.id_os_status = ordem_servico_status.id_os_status ";
	$sql .= "AND documentos_referencia.reg_del = 0 ";
	$sql .= "AND ordem_servico.reg_del = 0 ";
	$sql .= "AND ordem_servico_status.reg_del = 0 ";
	$sql .= "AND documentos_referencia.id_os = ordem_servico.id_os ";
	//$sql .= "AND ordem_servico.os > 3100 ";	
	$sql .= "GROUP BY ordem_servico.id_os ";
	$sql .= "ORDER BY ordem_servico.os ";	
}

$db->select($sql,'MYSQL',true);

if ($db->erro != '')
{
	exit("Não foi possível realizar a seleção: ". $db->erro); 
}

foreach ($db->array_select as $regs)
{
	$os = sprintf("%05d",$regs["os"]);
	
	$array_os_values[] = $regs["id_os"];
	$array_os_output[] = $os." - ". substr($regs["descricao"],0,50);
}

$smarty->assign("revisao_documento","V2");

$smarty->assign("campo",$conf->campos('relatorio_lista_referencias'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("option_os_values",$array_os_values);
$smarty->assign("option_os_output",$array_os_output);

$smarty->assign("classe",CSS_FILE);

$smarty->display('relatorio_lista_referencias.tpl');

?>

