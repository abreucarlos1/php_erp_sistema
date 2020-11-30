<?php
/*
		Formul�rio de lista documentos excel
		
		Criado por Carlos Abreu  
		
		local/Nome do arquivo:
		
		../planejamento/lista_documentos_excel.php
		
		Vers�o 0 --> VERS�O INICIAL - 09/01/2018 - Carlos Abreu
*/	

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO M�DULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(616))
{
	nao_permitido();
}

$conf = new configs();

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<?php

$db = new banco_dados;

$array_os_values = NULL;
$array_os_output = NULL;

$array_os_values[] = "";
$array_os_output[] = "SELECIONE";

$sql = "SELECT * FROM ".DATABASE.".OS ";
$sql .= "WHERE OS.reg_del = 0 ";
$sql .= "AND os.os > 4000 ";
$sql .= "AND OS.id_os_status IN (1,2,11,13,15,16)";
$sql .= "ORDER BY OS ";

$db->select($sql,'MYSQL', true);
	 
foreach($db->array_select as $regs)
{
	$array_os_values[] = $regs["id_os"];
	$array_os_output[] = $regs["os"].' - '.$regs["descricao"];
}

$smarty->assign("option_os_values",$array_os_values);
$smarty->assign("option_os_output",$array_os_output);

$smarty->assign("revisao_documento","V0");

$smarty->assign("campo",$conf->campos('lista_documentos_excel'));

$smarty->assign("classe",CSS_FILE);

$smarty->display('lista_documentos_excel.tpl');

?>

