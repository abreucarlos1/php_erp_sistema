<?php
/*
		Formulário de Relatorio Custo Unitario / centro de custo	
		
		Criado por Carlos Abreu  
		
		local/Nome do arquivo:
		../contratos_controle/relatorio_custo_uni_cc.php
	
		Versão 0 --> VERSÃO INICIAL : 07/02/2014
		Versão 1 --> atualização layout - Carlos Abreu - 23/03/2017
		Versão 2 --> Inclusão dos campos reg_del nas consultas - 17/11/2017 - Carlos Abreu
*/	
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO M�DULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(317))
{
	nao_permitido();
}


?>

<script src="<?php echo ROOT_WEB.'/includes/' ?>validacao.js"></script>

<?php
$conf = new configs();

$db = new banco_dados;

$array_centro_custo_values = NULL;
$array_centro_custo_output = NULL;

$array_centro_custo_values[] = "-1";
$array_centro_custo_output[] = "TODOS";

/*

$sql = "SELECT * FROM CTT010 WITH(NOLOCK) ";
$sql .= "WHERE CTT010.D_E_L_E_T_ = '' ";
$sql .= "AND CTT_BLOQ = '2' "; //SOMENTE OS CC N�O BLOQUEADOS
$sql .= "ORDER BY CTT010.CTT_DESC01 ";

$db->select($sql,'MSSQL', true);

if($db->erro!='')
{
	die($db->erro);
}

$array_cc = $db->array_select;
*/	

foreach($array_cc as $regs)
{
	$sql = "SELECT * FROM ".DATABASE.".funcionarios ";
	$sql .= "WHERE id_centro_custo = ".$regs["CTT_CUSTO"]." ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	$sql .= "AND situacao NOT IN ('DESLIGADO','CANCELADO') ";

	$db->select($sql,'MYSQL', true);

	if($db->erro!='')
	{
		die($db->erro);
	}
	
	if($db->numero_registros>0)
	{				
		//$array_centro_custo_values[] = $regs["CTT_CUSTO"];
		//$array_centro_custo_output[] = $regs["CTT_DESC01"];
	}
}

$smarty->assign("revisao_documento","V2");

$smarty->assign("campo",$conf->campos('relatorio_custo_uni_cc'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("option_centro_custo_values",$array_centro_custo_values);
$smarty->assign("option_centro_custo_output",$array_centro_custo_output);

$smarty->assign("classe",CSS_FILE);

$smarty->display('relatorio_custo_uni_cc.tpl');

?>

