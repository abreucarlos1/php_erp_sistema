<?php
/*
		Formulário de Lista dos documentos do Projeto
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		../arquivotec/vis_acompanhamento_os.php
		
		Versão 0 --> VERSÃO INICIAL - 25/02/2008
		Versão 1 --> Novo Layout
		Versão 2 --> atualização layout - Carlos Abreu - 22/03/2017
		Versão 3 --> Inclusão dos campos reg_del nas consultas - 16/11/2017 - Carlos Abreu	
*/	
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));

require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(197))
{
	nao_permitido();
}

$conf = new configs();

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

?>

<script src="<?php echo INCLUDE_JS ?>datetimepicker.js"></script>

<script>

function visualizarRel()
{
	//Abre o relatório em uma janela popup
	sel_os = document.getElementById("id_os");
	
	id_os = sel_os.options[sel_os.selectedIndex].value;
	
	window.open('relatorios/rel_acompanhamento_os.php?id_os='+id_os);
}

</script>

<?php

$sql = "SELECT ordem_servico.id_os, ordem_servico.os, ordem_servico.descricao, empresa FROM ".DATABASE.".ordem_servico, ".DATABASE.".ordem_servico_status, ".DATABASE.".empresas ";
$sql .= "WHERE ordem_servico.id_empresa_erp = empresas.id_empresa_erp ";
$sql .= "AND ordem_servico.reg_del = 0 ";
$sql .= "AND ordem_servico_status.reg_del = 0 ";
$sql .= "AND empresas.reg_del = 0 ";
$sql .= "AND ordem_servico.id_os_status = ordem_servico_status.id_os_status ";
//$sql .= "AND ordem_servico.descricao NOT LIKE 'INT%' ";
//$sql .= "AND ordem_servico.os > 1700 ";
$sql .= "GROUP BY ordem_servico.os ORDER BY ordem_servico.os ";

$db->select($sql,'MYSQL',true);

if ($db->erro != '')
{
	exit("Erro ao tentar selecionar os dados: " . $db->erro);
}

foreach($db->array_select as $reg_os)
{
	$array_os_values[] = $reg_os["id_os"];
	$array_os_output[] = sprintf("%03d",$reg_os["os"]) . " - " . substr($reg_os["descricao"],0,80) . " - " . $reg_os["empresa"];
}

$smarty->assign("option_os_values",$array_os_values);
$smarty->assign("option_os_output",$array_os_output);

$smarty->assign("revisao_documento","V3");

$smarty->assign("campo",$conf->campos('acomp_os'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("classe",CSS_FILE);

$smarty->display('vis_acompanhamento_os.tpl');
?>