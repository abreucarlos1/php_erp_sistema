<?php
/*
		Formul�rio de Coordenador Por OS Por Funcion�rios
		
		Criado por Carlos Abreu / Ot�vio Pamplon ia
		
		local/Nome do arquivo:
		
		../planejamento/apontamentos_coordenador_os_funcionarios.php
		
		Vers�o 0 --> VERS�O INICIAL - 02/03/2006
		Vers�o 1 --> Atualiza��o Lay-out | Smarty : 21/07/2008
		Vers�o 2 --> Atualiza��o Lay-out | Smarty : 28/11/2014
		Vers�o 3 --> atualiza��o layout - Carlos Abreu - 31/03/2017
		Vers�o 4 --> Inclus�o dos campos reg_del nas consultas - 20/11/2017 - Carlos Abreu
*/
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO M�DULO 
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

$sql = "SELECT * FROM ".DATABASE.".OS, ".DATABASE.".funcionarios, ".DATABASE.".ordem_servico_status ";
$sql .= "WHERE OS.id_os_status = ordem_servico_status.id_os_status ";
$sql .= "AND funcionarios.reg_del = 0 ";
$sql .= "AND ordem_servico_status.reg_del = 0 ";
$sql .= "AND OS.id_cod_coord = funcionarios.id_funcionario ";

if (!in_array($_SESSION['id_funcionario'], array(6,689,18,39,978)))
{
	$sql .= "AND OS.id_cod_coord = '".$_SESSION["id_funcionario"]."' ";
}
else
{
	$array_coordenador_values[] = "-1";
	$array_coordenador_output[] = "TODOS";
}

$sql .= "AND funcionarios.situacao NOT IN ('DESLIGADO','CANCELADODVM','CANCELADO') ";
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