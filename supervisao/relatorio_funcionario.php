<?php
/*
		Formulário de Relatório funcionario
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		../supervisao/relatorios_funcionario.php
		
		Versão 0 --> VERSÃO INICIAL : 02/03/2006
		Versao 1 --> Alteração de laytou: 27/11/2014
		Versão 2 --> Atualização layout - Carlos Abreu - 11/04/2017
		Versão 3 --> Inclusão dos campos reg_del nas consultas - 20/11/2017 - Carlos Abreu			
*/	
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(215) && !verifica_sub_modulo(296) && !verifica_sub_modulo(261))
{
	nao_permitido();
}

$conf = new configs();

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

?>
<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>
<?php

$array_os_values[] = "-1";
$array_os_output[] = "TODOS";

$sql = "SELECT * FROM ".DATABASE.".setores ";
$sql .= "WHERE setor NOT IN ('PDMS','GERAL','OUTROS','COMISSIONAMENTO','SUPRIMENTOS','FISCALIZAÇÃO DE MONTAGEM','ESTRUTURAS METÁLICAS','GERENCIAMENTO/DILIGENCIAMENTO','MATERIAIS E EQUIPAMENTOS') ";
$sql .= "AND setores.reg_del = 0 ";
$sql .= "ORDER BY setor ";

$db->select($sql, 'MYSQL',true);

if ($db->erro != '')
{
	exit($db->erro);
}

foreach ($db->array_select as $regs)
{
	$array_os_values[] = $regs["id_setor"];
	$array_os_output[] = $regs["setor"];
}

$smarty->assign("option_values",$array_os_values);
$smarty->assign("option_output",$array_os_output);

$smarty->assign("revisao_documento","V3");

$smarty->assign("campo",$conf->campos('funcionario_funcao'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("classe",CSS_FILE);

$smarty->display('relatorio_funcionario.tpl');
?>