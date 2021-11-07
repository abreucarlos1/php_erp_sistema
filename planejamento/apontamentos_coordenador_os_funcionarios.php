<?php
/*
		Formulário de Coordenador Por OS Por Funcionários
		
		Criado por Carlos Abreu
		
		local/Nome do arquivo:
		
		../planejamento/apontamentos_coordenador_os_funcionarios.php
		
		Versão 0 --> VERSÃO INICIAL - 02/03/2006
		Versão 1 --> Atualização Lay-out | Smarty : 21/07/2008
		Versão 2 --> Atualização Lay-out | Smarty : 28/11/2014
		Versão 3 --> Atualização layout - Carlos Abreu - 31/03/2017
		Versão 4 --> Inclusão dos campos reg_del nas consultas - 20/11/2017 - Carlos Abreu
*/
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
if(!verifica_sub_modulo(287))
{
	nao_permitido();
}

$conf = new configs();

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<?php
$array_coordenador_values = NULL;
$array_coordenador_output = NULL;

$sql = "SELECT * FROM ".DATABASE.".ordem_servico, ".DATABASE.".funcionarios, ".DATABASE.".ordem_servico_status ";
$sql .= "WHERE OS.id_os_status = ordem_servico_status.id_os_status ";
$sql .= "AND funcionarios.reg_del = 0 ";
$sql .= "AND ordem_servico_status.reg_del = 0 ";
$sql .= "AND ordem_servico.id_cod_coord = funcionarios.id_funcionario ";
$sql .= "AND ordem_servico.id_cod_coord = '".$_SESSION["id_funcionario"]."' ";
$sql .= "AND funcionarios.situacao NOT IN ('DESLIGADO','CANCELADO') ";
$sql .= "AND ordem_servico_status.id_os_status NOT IN (2,3,8,9,12,13) ";
$sql .= "GROUP BY id_funcionario ";
$sql .= "ORDER BY funcionario ";

$db->select($sql,'MYSQL',true);

if ($db->erro != '')
{
	exit("Não foi possível realizar a seleção." . $sql);
}
 
foreach($db->array_select as $regs)
{
	$array_coordenador_values[] = $regs["id_funcionario"];
	$array_coordenador_output[] = $regs["funcionario"];
}

$smarty->assign("option_coordenador_values",$array_coordenador_values);
$smarty->assign("option_coordenador_output",$array_coordenador_output);

$smarty->assign("revisao_documento","V4");

$smarty->assign("campo",$conf->campos('apont_coord_os_func'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("classe",CSS_FILE);

$smarty->display('apontamentos_coordenador_os_funcionarios.tpl');
?>