<?php
/*
		Formulário de HORAS POR PERÍODO	
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		../rh/relatorio_funcionario.php
		
		Versão 0 --> VERSÃO INICIAL : 02/03/2006
		Versão 1 --> Atualização Layout - 01/04/2015 - Eduardo
		Versão 2 --> Atualização layout - Carlos Abreu - 07/04/2017		
*/
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(271))
{
	nao_permitido();
}

$conf = new configs();

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<?php
$smarty->assign("data",date("d/m/Y"));

$smarty->assign('campo', $conf->campos('funcionarios_situacao_nivel_atuacao'));

$smarty->assign('revisao_documento', 'V2');

$smarty->assign("classe",CSS_FILE);

$smarty->display('relatorio_funcionario.tpl');
?>