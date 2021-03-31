<?php

/*

		Formulário de OS POR FUNCIONÁRIOS
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		
		../coordenacao/controle_func_os.php
		
		data de criação: 02/03/2006
		
		Versão 0 --> VERSÃO INICIAL
		Versão 1 --> Atualização Lay-out | Smarty : 22/07/2008
		
*/	

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(63) && !verifica_sub_modulo(155))
{
	die("ACESSO PROIBIDO!");
}


$xajax->registerPreFunction("checaSessao");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<?php

$conf = new configs();

$db = new banco_dados;

$array_os_values = NULL;
$array_os_output = NULL;

if($coordenador)
{
	$array_os_values[] = "-1";
	$array_os_output[] = "TODOS AS OS";
}

$sql = "SELECT * FROM ".DATABASE.".ordem_servico, ".DATABASE.".ordem_servico_status, ".DATABASE.".empresas ";
$sql .= "WHERE ordem_servico.id_empresa = empresas.id_empresa ";
$sql .= "AND ordem_servico.id_os_status = ordem_servico_status.id_os_status ";
$sql .= "AND (ordem_servico_status.os_status LIKE 'EM ANDAMENTO' ";
$sql .= "OR ordem_servico_status.os_status LIKE 'AS BUILT' ";
$sql .= "OR ordem_servico_status.os_status LIKE 'ADICIONAL') ";
$sql .= $filtro;
$sql .= "GROUP BY ordem_servico.os ";
$sql .= "ORDER BY ordem_servico.os ";

$db->select($sql,'MYSQL',true);
	 
foreach ($db->array_select as $regs)
{
	$array_os_values[] = $regs["id_os"];
	$array_os_output[] = sprintf("%05d",$regs["os"]) ." - ".$regs["ordem_servico_cliente"]." - ".$regs["empresa"];
}

$smarty->assign("option_os_values",$array_os_values);
$smarty->assign("option_os_output",$array_os_output);

$smarty->assign("nome_formulario","OS POR FUNCIONÁRIOS");

$smarty->assign("classe","setor_proj");

$smarty->display('controle_func_os.tpl');	

?>