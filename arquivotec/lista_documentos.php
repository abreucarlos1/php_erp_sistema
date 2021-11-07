<?php
/*
	  Formulário de Lista dos documentos do Projeto
	  
	  Criado por Carlos Abreu
	  
	  local/Nome do arquivo:
	  ../arquivotec/lista_documentos.php
	  
	  data de criação: 25/02/2008
	  
	  Versão 0 --> VERSÃO INICIAL
	  Versão 1 --> mudança layout/pasta - 17/10/2014 - Carlos Abreu		
	  Versão 2 --> mudança layout TPLs - 17/10/2014 - Carlos Abreu
	  Versão 3 --> atualização layout - Carlos Abreu - 22/03/2017
	  Versão 4 --> Inclusão dos campos reg_del nas consultas - 16/11/2017 - Carlos Abreu
*/	

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));

require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO
//previne contra acesso direto
if(!verifica_sub_modulo(294) && !verifica_sub_modulo(32))
{
    nao_permitido();
}

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$conf = new configs();

$sql = "SELECT id_formato, formato FROM ".DATABASE.".formatos ";
$sql .= "WHERE formatos.reg_del = 0 ";

$db->select($sql,'MYSQL',true);

foreach($db->array_select as $reg_fmt)
{
	$array_fmt_values[] = $reg_fmt["id_formato"];
	$array_fmt_output[] = $reg_fmt["formato"];
}

$sql = "SELECT id_codigo_emissao, codigos_emissao, emissao FROM ".DATABASE.".codigos_emissao ";
$sql .= "WHERE codigos_emissao.reg_del = 0 ";

$db->select($sql,'MYSQL',true);

foreach($db->array_select as $reg)
{
	$array_finalidade_values[] = $reg["id_codigo_emissao"];
	$array_finalidade_output[] = $reg["codigos_emissao"]." - ". $reg["emissao"];
}

$sql = "SELECT ordem_servico.id_os, ordem_servico.os, descricao, empresa FROM ".DATABASE.".ordem_servico, ".DATABASE.".ordem_servico_status, ".DATABASE.".empresas, ".DATABASE.".numeros_interno ";
$sql .= "WHERE ordem_servico.id_empresa = empresas.id_empresa ";
$sql .= "AND numeros_interno.reg_del = 0 ";
$sql .= "AND ordem_servico.reg_del = 0 ";
$sql .= "AND ordem_servico_status.reg_del = 0 ";
$sql .= "AND empresas.reg_del = 0 ";
$sql .= "AND ordem_servico.id_os = numeros_interno.id_os ";
$sql .= "AND ordem_servico.id_os_status = ordem_servico_status.id_os_status ";
//$sql .= "AND os.os < 60000 ";
//$sql .= "AND os.os > 3001 ";
$sql .= "GROUP BY ordem_servico.os ORDER BY ordem_servico.os ";

$db->select($sql,'MYSQL',true);

foreach($db->array_select as $reg_os)
{
	$array_os_values[] = $reg_os["id_os"];
	$array_os_output[] = sprintf("%05d",$reg_os["os"]) . " - " . substr($reg_os["descricao"],0,80) . " - " . $reg_os["empresa"];
}

$sql = "SELECT id_setor, setor FROM ".DATABASE.".setores ";
$sql .= "WHERE setor NOT IN ('2','4','15','17','3','11') ";
$sql .= "AND setores.reg_del = 0 ";
$sql .= "ORDER BY setor ";

$db->select($sql,'MYSQL',true);

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

$smarty->assign("revisao_documento","V4");

$smarty->assign("campo",$conf->campos('ged_lista_documentos'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("nome_formulario","LISTA DOS DOCUMENTOS DO PROJETO");

$smarty->assign("classe",CSS_FILE);

$smarty->display('lista_documentos.tpl');

?>

<script src="<?php echo INCLUDE_JS ?>datetimepicker/datetimepicker_css.js"></script>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>
