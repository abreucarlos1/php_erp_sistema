<?
/*

		Formulário de Lista dos documentos do Projeto
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		../planejamento/ged_lista_documentos.php
	
		
		data de criação: 25/02/2008
		
		Versão 0 --> VERSÃO INICIAL
		
		
		
*/	

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(294) && !verifica_sub_modulo(32))
{
	die("ACESSO PROIBIDO!");
}



$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<?php

$sql_fmt = "SELECT * FROM ".DATABASE.".formatos ";

$db->select($sql,'MYSQL',true);

if ($db->erro != '')
{
	exit("Não foi possível realizar a seleção." . $sql);
}
	 
foreach ($db->array_select as $reg_fmt)
{
	$array_fmt_values[] = $reg_fmt["id_formato"];
	$array_fmt_output[] = $reg_fmt["formato"];
}


$sql = "SELECT * FROM ".DATABASE.".codigos_emissao ";

$db->select($sql,'MYSQL',true);

if ($db->erro != '')
{
	exit("Não foi possível realizar a seleção." . $sql);
}
	 
foreach ($db->array_select as $reg)
{
	$array_finalidade_values[] = $reg["id_codigo_emissao"];
	$array_finalidade_output[] = $reg["codigos_emissao"]." - ". $reg["emissao"];
}

$sql = "SELECT * FROM ".DATABASE.".ordem_servico, ".DATABASE.".ordem_servico_status, ".DATABASE.".empresas ";
$sql .= "WHERE ordem_servico.id_empresa_erp = empresas.id_empresa_erp ";
$sql .= "AND ordem_servico.id_os_status = ordem_servico_status.id_os_status ";
$sql .= "GROUP BY ordem_servico.os ORDER BY ordem_servico.os ";

$db->select($sql,'MYSQL',true);

if ($db->erro != '')
{
	exit("Não foi possível realizar a seleção." . $sql);
}
	 
foreach ($db->array_select as $reg_os)
{
	$array_os_values[] = $reg_os["id_os"];
	$array_os_output[] = sprintf("%03d",$reg_os["os"]) . " - " . substr($reg_os["descricao"],0,80) . " - " . $reg_os["empresa"];
}


$sql = "SELECT * FROM ".DATABASE.".setores ";
$sql .= "ORDER BY setor ";

$db->select($sql,'MYSQL',true);

if ($db->erro != '')
{
	exit("Não foi possível realizar a seleção." . $sql);
}
	 
foreach ($db->array_select as $cont)
{
	$array_disc_values[] = $cont["id_setor"];
	$array_disc_output[] = $cont["setor"];
}


$smarty->assign("option_os_values",$array_os_values);
$smarty->assign("option_os_output",$array_os_output);

$smarty->assign("option_fmt_values",$array_fmt_values);
$smarty->assign("option_fmt_output",$array_fmt_output);

$smarty->assign("option_disc_values",$array_disc_values);
$smarty->assign("option_disc_output",$array_disc_output);

$smarty->assign("option_finalidade_values",$array_finalidade_values);
$smarty->assign("option_finalidade_output",$array_finalidade_output);

$smarty->assign("nome_formulario","LISTA DOS DOCUMENTOS DO PROJETO");

$smarty->assign("classe","setor_proj");

$smarty->display('ged_lista_documentos.tpl');

?>