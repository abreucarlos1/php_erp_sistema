<?php
/*
	Relatório de serviços por os
	
	Criado por Carlos
	
	local/Nome do arquivo: 
	../coordenacao/relatorio_servicos_os.php
	
	Versão 0 --> VERSÃO INICIAL : 03/07/2015
	Versão 1 --> atualização layout - Carlos Abreu - 24/03/2017
	Versão 2 --> Inclusão dos campos reg_del nas consultas - 17/11/2017 - Carlos Abreu
*/

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(528))
{
	nao_permitido();
}

$conf = new configs();

function preencheServicos($os)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados();
	
	$sql = "SELECT servico_id, servico_descricao, servico FROM ".DATABASE.".servicos ";
	$sql .= "WHERE id_os = '".$os."'";
	$sql .= "AND reg_del = 0 ";
	
	$resposta->addScript("limpa_combo('sel_servico');");
	
	$resposta->addScript("addOption('sel_servico', 'TODOS', '')");
	
	$db->select($sql, 'MYSQL',true);
	
	foreach($db->array_select as $reg)
	{
		$resposta->addScript("addOption('sel_servico', '".$reg['servico']." - ".$reg['servico_descricao']."', '".$reg['servico_id']."')");
	}
		
	return $resposta;
}

$xajax->registerFunction("preencheServicos");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));
?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<?php

$sql = "SELECT * FROM ".DATABASE.".ordem_servico, ".DATABASE.".ordem_servico_status, ".DATABASE.".solicitacao_documentos ";
$sql .= "WHERE ordem_servico.id_os_status = ordem_servico_status.id_os_status ";
$sql .= "AND ordem_servico.reg_del = 0 ";
$sql .= "AND ordem_servico_status.reg_del = 0 ";
$sql .= "AND solicitacao_documentos.reg_del = 0 ";
$sql .= "AND ordem_servico.id_os = solicitacao_documentos.id_os ";	
$sql .= "GROUP BY ordem_servico.id_os ";
$sql .= "ORDER BY ordem_servico.os ";

$array_os_values[] = '';
$array_os_output[] = 'SELECIONE';

$db->select($sql,'MYSQL',true);

foreach($db->array_select as $reg)
{
	$os = sprintf("%05d",$reg["os"]).' - '.$reg['descricao'];	
	$array_os_output[] = $os;
	$array_os_values[] = $reg['id_os'];
}


$smarty->assign("option_os_values",$array_os_values);
$smarty->assign("option_os_output",$array_os_output);

$smarty->assign("revisao_documento","V2");

$smarty->assign("campo",$conf->campos('relatorio_servicos_os'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("classe",CSS_FILE);

$smarty->display('relatorio_servicos_os.tpl');
?>