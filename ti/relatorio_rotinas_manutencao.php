<?php
/*
		Formul�rio de Relatorio Rotinas Manuten��es	
		
		Criado por Carlos Abreu  
		
		local/Nome do arquivo:
		../ti/relatorio_rotinas_manutencoes.php
	
		Versão 0 --> VERSÃO INICIAL : 27/02/2014
		Versão 1 --> Atualização layout - Carlos Abreu - 11/04/2017
		Versão 2 --> Inclusão dos campos reg_del nas consultas - Carlos Abreu - 13/11/2017
		Versão 3 --> Inclusão dos campos reg_del nas consultas - 23/11/2017 - Carlos Abreu
		Versão 4 --> Layout responsivo - 05/02/2018 - Carlos Eduardo
*/
	
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO M�DULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(323))
{
	nao_permitido();
}


?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<?php

$conf = new configs();

$db = new banco_dados;

$array_ano_values = NULL;
$array_ano_output = NULL;

$array_ano_values[] = "-1";
$array_ano_output[] = "SELECIONE";

$sql = "SELECT DATE_FORMAT(ti_data_manutencao,'%Y') AS ano FROM ti.ti_rotinas_manutencoes ";
$sql .= "WHERE ti_rotinas_manutencoes.reg_del = 0 ";
$sql .= "GROUP BY DATE_FORMAT(ti_data_manutencao,'%Y') ";
$sql .= "ORDER BY ti_data_manutencao DESC ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}	

foreach($db->array_select as $regs)
{
	$array_ano_values[] = $regs["ano"];
	$array_ano_output[] = $regs["ano"];		
}

$smarty->assign("revisao_documento","V4");

$smarty->assign('larguraTotal', 1);

$smarty->assign("campo",$conf->campos('relatorio_rotinas_manutencao'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("option_ano_values",$array_ano_values);
$smarty->assign("option_ano_output",$array_ano_output);

$smarty->assign("classe",CSS_FILE);

$smarty->display('relatorio_rotinas_manutencao.tpl');
?>