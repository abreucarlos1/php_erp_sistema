<?php
/*
		Formulário de Relatorio Os x Medição	
		
		Criado por Carlos Abreu  
		
		local/Nome do arquivo:
		../contratos_controle/relatorio_os_medicao.php
	
		Versão 0 --> VERSÃO INICIAL : 21/02/2018 - Carlos Abreu		
*/	
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(619))
{
	nao_permitido();
}

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","xajax_preenchecoord(xajax.getFormValues('frm'));");

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<?php
$conf = new configs();

$db = new banco_dados;

$array = array("JANEIRO","FEVEREIRO","MARÇO","ABRIL","MAIO","JUNHO","JULHO","AGOSTO","SETEMBRO","OUTUBRO","NOVEMBRO","DEZEMBRO");

for($i=1;$i<=12;$i++)
{
	$array_per_values[] = sprintf("%02d",$i);
	$array_per_output[] = $array[$i-1];
	
	if(date("m")==$i)
	{
		$index = sprintf("%02d",$i);
	}
}

$sql = "SELECT SUBSTRING_INDEX(data, '-', 1) AS ANO FROM ".DATABASE.".apontamento_horas ";
$sql .= "WHERE apontamento_horas.reg_del = 0 ";
$sql .= "AND DATE_FORMAT(data, '%Y') >= 2016 ";
$sql .= "GROUP BY ANO ";
$sql .= "ORDER BY ANO DESC ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}

foreach($db->array_select as $reg_ano)
{
	$array_ano_output[] = $reg_ano["ANO"];
	$array_ano_values[] = $reg_ano["ANO"];
}

$smarty->assign("option_per_values",$array_per_values);
$smarty->assign("option_per_id",$index);
$smarty->assign("option_per_output",$array_per_output);

$smarty->assign("option_ano_values",$array_ano_values);
$smarty->assign("option_ano_id",$index_ano);
$smarty->assign("option_ano_output",$array_ano_output);

$smarty->assign("revisao_documento","V0");

$smarty->assign("campo",$conf->campos('relatorio_os_medicao'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("classe",CSS_FILE);

$smarty->display('relatorio_os_medicao.tpl');

?>