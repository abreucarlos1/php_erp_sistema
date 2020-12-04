<?php
/*
		Formulário de Relatório de Movimento de Funcionários - Excel	
		
		Criado por Carlos Abreu  
		
		local/Nome do arquivo:
		../rh/controle_movimento_funcionarios_excel.php
		
		Versão 0 --> VERSÃO INICIAL : 17/12/2012 - Carlos Abreu
		Versão 1 --> Alterações pedidas para contemplar histórico de locais de trabalho do funcionário
		Versão 2 --> Atualização layout - Carlos Abreu - 05/04/2017
*/	

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(260) && !verifica_sub_modulo(277))
{
	nao_permitido();
}

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<?php
$conf = new configs();

$db = new banco_dados();

$array_ordenacao_values = array("funcionario","id_centro_custo");
$array_ordenacao_output = array("FUNCIONÁRIO","CENTRO DE CUSTO");

$smarty->assign("option_ordenacao_values",$array_ordenacao_values);
$smarty->assign("option_ordenacao_output",$array_ordenacao_output);

$smarty->assign("data_ini",date('d/m/Y'));

$smarty->assign("revisao_documento","V2");

$smarty->assign("campo",$conf->campos('relatorio_movimento_funcionarios'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("classe",CSS_FILE);

$smarty->display('controle_movimento_funcionarios_excel.tpl');
?>