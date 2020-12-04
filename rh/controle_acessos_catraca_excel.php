<?php
/*
		Formulório de Relatório de Acessos - DIMEP - Excel	
		
		Criado por Carlos Abreu  
		
		local/Nome do arquivo:
		../rh/controle_acessos_catraca_excel.php
		
		Versão 0 --> VERSÃO INICIAL : 12/11/2012
		Versão 1 --> Atualização layout - Carlos Abreu - 05/04/2017
		Versão 2 --> Inclusão dos campos reg_del nas consultas - 28/11/2017 - Carlos Abreu
*/	

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(276))
{
	nao_permitido();
}

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<?php

$conf = new configs();

$db = new banco_dados;

$db->db_ms = 'DMPACESSO_V100';

$array_funcionario_values = NULL;
$array_funcionario_output = NULL;

$array_funcionario_values[] = "-1";
$array_funcionario_output[] = "TODOS OS FUNCIONÁRIOS";

$sql = "SELECT * FROM PESSOAS WITH(NOLOCK) ";
$sql .= "ORDER BY PES_NOME ";

$db->select($sql,'MSSQL', true);

if($db->erro!='')
{
	die($db->erro);
}

foreach($db->array_select as $regs)
{
	$array_funcionario_values[] = $regs["PES_NUMERO"];
	$array_funcionario_output[] = $regs["PES_NOME"];
}

$smarty->assign("option_funcionario_values",$array_funcionario_values);
$smarty->assign("option_funcionario_output",$array_funcionario_output);

$smarty->assign("revisao_documento","V2");

$smarty->assign("campo",$conf->campos('relatorio_acessos'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("classe",CSS_FILE);

$smarty->display('controle_acessos_catraca_excel.tpl');
?>