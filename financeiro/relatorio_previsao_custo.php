<?php
/*
		Formulário de Relatorio Previsão Custo	
		
		Criado por Carlos Abreu  
		
		local/Nome do arquivo:
		../financeiro/relatorio_previso_custo.php
	
		Versão 0 --> VERSÃO INICIAL : 03/06/2014
		Versão 1 --> atualização layout - Carlos Abreu - 28/03/2017
*/
	
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(327))
{
	nao_permitido();
}

$conf = new configs();

$db = new banco_dados;

$array_ano_values = NULL;
$array_ano_output = NULL;

$array_ano_values[] = "-1";
$array_ano_output[] = "SELECIONE";

$array_ano_values[] = date('Y');
$array_ano_output[] = date('Y');

$smarty->assign("revisao_documento","V1");

$smarty->assign("campo",$conf->campos('relatorio_previsao_custo'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("option_ano_values",$array_ano_values);
$smarty->assign("option_ano_output",$array_ano_output);

$smarty->assign("classe",CSS_FILE);

$smarty->display('relatorio_previsao_custo.tpl');

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>