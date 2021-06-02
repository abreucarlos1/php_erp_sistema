<?php
/*
		Formulário de Controle Valores Despesas	
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		../financeiro/controle_valores_despesas.php
	
		Versão 0 --> VERSÃO INICIAL - 02/03/2006
		Versão 1 --> atualização de classe banco - 20/01/2015 - Carlos Abreu
		Versão 2 --> atualização layout - 11/11/2015 - Carlos Eduardo Máximo
		Versão 3 --> atualização layout - Carlos Abreu - 27/03/2017
		Versão 4 --> Inclusão dos campos reg_del nas consultas - 20/11/2017 - Carlos Abreu		
*/	

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(41))
{
	nao_permitido();
}

$filtro = '';
	
$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$conf = new configs();
	  
$sql = "SELECT * FROM ".DATABASE.".ordem_servico, ".DATABASE.".ordem_servico_status, ".DATABASE.".empresas ";
$sql .= "WHERE ordem_servico.id_empresa = empresas.id_empresa ";
$sql .= "AND ordem_servico.reg_del = 0 ";
$sql .= "AND ordem_servico_status.reg_del = 0 ";
$sql .= "AND empresas.reg_del = 0 ";
$sql .= "AND ordem_servico.id_os_status = ordem_servico_status.id_os_status ";
$sql .= "AND ordem_servico_status.id_os_status NOT IN (3,8,9,12) ";
$sql .= "GROUP BY ordem_servico.os ORDER BY ordem_servico.os ";

$db->select($sql,'MYSQL',true);

if($db->numero_registros>0)
{	
	$array_os_values[] = "-1";
	$array_os_output[] = "TODAS AS OS";	
}
						 
foreach($db->array_select as $regs)
{	
	$os = sprintf("%05d",$regs["os"]);

	$array_os_values[] = $regs["id_os"];
	$array_os_output[] = $os . " - " . $regs["ordem_servico_cliente"] . " - " . $regs["empresa"];	
}

$smarty->assign("option_os_values",$array_os_values);
$smarty->assign("option_os_output",$array_os_output);

$smarty->assign("revisao_documento","V4");

$smarty->assign("campo",$conf->campos('valores_despesas'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("classe",CSS_FILE);

$smarty->display('controle_valores_despesas.tpl');

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>