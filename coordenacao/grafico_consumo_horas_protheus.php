<?php
/*
		Formulário de Gráfico do Consumo de Horas
		
		Criado por Carlos Abreu
		
		local/Nome do arquivo:		
		../coordenacao/grafico_consumo_horas_protheus.php
		
		Versão 0 --> VERSÃO INICIAL - 02/03/2006
		Versão 1 --> Atualização Lay-out | Smarty : 21/07/2008
		Versão 2 --> Atualização Layout 2014
		Versão 3 --> atualização layout - Carlos Abreu - 24/03/2017
		Versão 4 --> Inclusão dos campos reg_del nas consultas - 17/11/2017 - Carlos Abreu	
*/

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(210) && !verifica_sub_modulo(286) && !verifica_sub_modulo(261))
{
	nao_permitido();
}

$filtro = '';

$coordenador = false;

function grafico($dados_form)
{
	$resposta = new xajaxResponse();
	
	$conteudo = '';
	
	if($dados_form["escolhaos"])
	{
		$conteudo = '<img src="graficos_protheus.php?id_os='. $dados_form["escolhaos"].'"><br>';
		$conteudo .= '<input name="btnimprimir" type="button" class="class_botao" value="Visualizar p/ impressão" onclick="imprimir();">';
	}
	
	$resposta->addAssign("grafico","innerHTML",$conteudo);

	return $resposta;
}

$coordenador = true;

$xajax->registerFunction("grafico");
$xajax->registerFunction("imprimir");
$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script>
function imprimir()
{
	x = document.frm_rel.escolhaos.value;
	document.frm_rel.action='graficos_protheus.php?id_os='+x+'';
	document.frm_rel.target='blank';
	document.frm_rel.submit();
}
</script>

<?php

$conf = new configs();

$array_os_values = NULL;
$array_os_output = NULL;

$sql = "SELECT * FROM ".DATABASE.".ordem_servico, ".DATABASE.".ordem_servico_status ";
$sql .= "WHERE ordem_servico_status.id_os_status IN (1,2,14) ";
$sql .= "AND ordem_servico.reg_del = 0 ";
$sql .= "AND ordem_servico_status.reg_del = 0 ";
$sql .= "AND ordem_servico.id_os_status = ordem_servico_status.id_os_status ";
$sql .= $filtro;
$sql .= "ORDER BY ordem_servico.os ";

$db->select($sql,'MYSQL',true);

if ($db->erro != '')
{
	exit("Não foi possível realizar a seleção.".$sql);
}

foreach ($db->array_select as $regs)
{
	$array_os_values[] = $regs["id_os"];
	$array_os_output[] = sprintf("%05d",$regs["os"]) ." - ". $regs["descricao"];
}

$smarty->assign("option_os_values",$array_os_values);
$smarty->assign("option_os_output",$array_os_output);

$smarty->assign("revisao_documento","V4");

$smarty->assign("campo",$conf->campos('grafico_consumo_horas_protheus'));
$smarty->assign("botao",$conf->botoes());

$smarty->assign("classe",CSS_FILE);

$smarty->display('grafico_consumo_horas_protheus.tpl');
?>