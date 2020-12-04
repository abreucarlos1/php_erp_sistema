<?php
/*
		Formulário de HORAS POR PERÍODO	
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		../rh/controle_horas_acesso.php
		
		Versão 0 --> VERSÃO INICIAL : 02/03/2006		
		Versão 1 --> Atualização classe banco de dados - 23/01/2015 - Carlos Abreu
		Versão 2 --> Atualização Layout - 01/04/2015 - Eduardo
		Versão 3 --> Atualização banco - 21/07/2016 - Carlos Abreu
		Versão 4 --> Atualização layout - Carlos Abreu - 05/04/2017
		Versão 5 --> Inclusão dos campos reg_del nas consultas - 28/11/2017 - Carlos Abreu	
*/	

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(256) && !verifica_sub_modulo(278) && !verifica_sub_modulo(596))
{
	nao_permitido();
}

$conf = new configs();
?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>datetimepicker/datetimepicker_css.js"></script>

<?php
$array_funcionario_values = NULL;
$array_funcionario_output = NULL;

$array_funcionario_values[] = "-1";
$array_funcionario_output[] = "TODOS";

$sql = "SELECT * FROM ".DATABASE.".funcionarios ";
$sql .= "WHERE funcionarios.situacao NOT IN ('DESLIGADO','CANCELADO') ";
$sql .= "AND funcionarios.reg_del = 0 ";
$sql .= "ORDER BY funcionario ";

$db->select($sql,'MYSQL',true);
 
foreach($db->array_select as $regs)
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

$smarty->assign('campo', $conf->campos('controle_horas_acesso'));

$smarty->assign('revisao_documento', 'V5');

$smarty->assign("classe",CSS_FILE);

$smarty->display('controle_horas_acesso.tpl');
?>