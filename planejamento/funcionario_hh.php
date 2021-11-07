<?php
/*
		Formulário de Funcionário po HH
		
		Criado por Carlos Abreu 
		
		local/Nome do arquivo:
		
		../planejamento/funcionario_hh.php
		
		Versão 0 --> VERSÃO INICIAL - 02/03/2006
		Versão 1 --> Atualização Lay-out | Smarty : 04/08/2008
		Versão 2 --> Atualização classe banco de dados - 22/01/2015 - Carlos Abreu
		Versão 3 --> Atualização layout - Carlos Abreu - 31/03/2017
		Versão 4 --> Inclusão dos campos reg_del nas consultas - 20/11/2017 - Carlos Abreu
*/	

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(65))
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

$sql = "SELECT * FROM ".DATABASE.".funcionarios ";
$sql .= "WHERE funcionarios.situacao NOT IN ('DESLIGADO','CANCELADO') ";
$sql .= "AND funcionarios.reg_del = 0 ";
$sql .= "ORDER BY funcionario ";

$db->select($sql,'MYSQL',true);
 
foreach ($db->array_select as $regs)
{
	$array_coordenador_values[] = $regs["id_funcionario"];
	$array_coordenador_output[] = $regs["funcionario"];
}

$smarty->assign("option_coordenador_values",$array_coordenador_values);
$smarty->assign("option_coordenador_output",$array_coordenador_output);

$campo[1] = "FUNCIONÁRIO POR Hh";

$smarty->assign("campo",$campo);

$smarty->assign("revisao_documento","V4");

$smarty->assign("classe",CSS_FILE);

$smarty->display('funcionario_hh.tpl');

?>