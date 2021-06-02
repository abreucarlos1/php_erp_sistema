<?php
/*
		Formulário de Relevância
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:		
		../empresas/escolha_relevancia.php
		
		Versão 0 --> VERSÃO INICIAL : 16/07/2009
		Versão 1 --> atualização classe banco - 20/01/2015 - Carlos Abreu
		Versão 2 --> atualização layout - Carlos Abreu - 27/03/2017
		Versão 3 --> Inclusão dos campos reg_del nas consultas - 20/11/2017 - Carlos Abreu		
*/	

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(281))
{
	nao_permitido();
}

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$conf = new configs();

$db = new banco_dados;

$array_relevancia_values = NULL;
$array_relevancia_output = NULL;

$array_decisao_values = NULL;
$array_decisao_output = NULL;

$array_relevancia_values[] = "-1";
$array_relevancia_output[] = "TODOS";

$array_decisao_values[] = "-1";
$array_decisao_output[] = "TODOS";

$sql = "SELECT relevancia FROM ".DATABASE.".empresas ";
$sql .= "WHERE empresas.reg_del = 0 ";
$sql .= "GROUP BY empresas.relevancia ";
$sql .= "ORDER BY empresas.relevancia ";

$db->select($sql,'MYSQL',true);
	 
foreach ($db->array_select as $regs)
{
	switch ($regs["relevancia"])
	{
		case 1: $relevancia = "BAIXA";
		break;
		
		case 2: $relevancia = "MÉDIA";
		break;
		
		case 3: $relevancia = "ALTA";
		break;
		
	}
	
	$array_relevancia_values[] = $regs["relevancia"];
	$array_relevancia_output[] = $relevancia;
}

$sql = "SELECT decisao FROM ".DATABASE.".contatos ";
$sql .= "WHERE contatos.reg_del = 0 ";
$sql .= "GROUP BY contatos.decisao ";
$sql .= "ORDER BY contatos.decisao ";

$db->select($sql,'MYSQL',true);

foreach ($db->array_select as $regs)
{
	switch ($regs["decisao"])
	{
		case 0: $decisao = "NãO";
		break;
		
		case 1: $decisao = "SIM";
		break;
	
	}
	$array_decisao_values[] = $regs["decisao"];
	$array_decisao_output[] =  $decisao;
}

$smarty->assign("option_relevancia_values",$array_relevancia_values);
$smarty->assign("option_relevancia_output",$array_relevancia_output);

$smarty->assign("option_decisao_values",$array_decisao_values);
$smarty->assign("option_decisao_output",$array_decisao_output);

$smarty->assign('revisao_documento', 'V3');

$smarty->assign("nome_formulario","CLIENTES POR RELEVÂNCIA");

$smarty->assign("classe",CSS_FILE);

$smarty->display('escolha_relevancia.tpl');

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>