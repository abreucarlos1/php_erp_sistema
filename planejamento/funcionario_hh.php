<?php
/*
		Formul�rio de Funcion�rio po HH
		
		Criado por Carlos Abreu / Ot�vio Pamplon ia
		
		local/Nome do arquivo:
		
		../planejamento/funcionario_hh.php
		
		Vers�o 0 --> VERS�O INICIAL - 02/03/2006
		Vers�o 1 --> Atualiza��o Lay-out | Smarty : 04/08/2008
		Vers�o 2 --> atualiza��o classe banco de dados - 22/01/2015 - Carlos Abreu
		Vers�o 3 --> atualiza��o layout - Carlos Abreu - 31/03/2017
		Vers�o 4 --> Inclus�o dos campos reg_del nas consultas - 20/11/2017 - Carlos Abreu
*/	

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO M�DULO 
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
$sql .= "WHERE funcionarios.situacao NOT IN ('DESLIGADO','CANCELADODVM','CANCELADO') ";
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

//$smarty->assign("nome_formulario","FUNCION�RIO POR Hh - V2");
$campo[1] = "FUNCION�RIO POR Hh";

$smarty->assign("campo",$campo);

$smarty->assign("revisao_documento","V4");

$smarty->assign("classe",CSS_FILE);

$smarty->display('funcionario_hh.tpl');

?>