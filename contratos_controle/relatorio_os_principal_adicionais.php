<?php
/*
		Formulário de Relatorio OS Principal x Adicionais	
		
		Criado por Carlos Abreu  
		
		local/Nome do arquivo:
		../contratos_controle/relatorio_os_principal_adicionais.php
	
		Versão 0 --> VERSÃO INICIAL : 18/07/2017 - Carlos Abreu
*/	

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(603))
{
	nao_permitido();
}

$conf = new configs();

$db = new banco_dados;

$array_os_values = NULL;
$array_os_output = NULL;

$array_os_values[] = "-1";
$array_os_output[] = "TODOS";

/*
$sql = "SELECT AF1_RAIZ FROM AF1010 WITH(NOLOCK) ";	
$sql .= "WHERE AF1010.D_E_L_E_T_ = '' ";
$sql .= "AND AF1_RAIZ <> '' ";

$db->select($sql,'MSSQL', true);

if($db->erro!='')
{
	die($db->erro);
}	

foreach($db->array_select as $regs)
{	
	$sql = "SELECT AF1_ORCAME, AF1_DESCRI FROM AF1010 WITH(NOLOCK) ";	
	$sql .= "WHERE AF1010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF1_ORCAME = '" .$regs["AF1_RAIZ"] ."' ";
	
	$db->select($sql,'MSSQL', true);
	
	if($db->erro!='')
	{
		die($db->erro);
	}
	
	$regs1 = $db->array_select[0];	
	
	$array_os_values[] = $regs["AF1_RAIZ"];
	$array_os_output[] = $regs1["AF1_ORCAME"]." - ".$regs1["AF1_DESCRI"];		
}
*/

$smarty->assign("revisao_documento","V0");

$smarty->assign("campo",$conf->campos('relatorio_os_principal_adicionais'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("option_os_values",$array_os_values);
$smarty->assign("option_os_output",$array_os_output);

$smarty->assign("classe",CSS_FILE);

$smarty->display('relatorio_os_principal_adicionais.tpl');

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>