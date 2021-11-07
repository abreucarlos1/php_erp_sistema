<?php
/*
		Formulário de MENU DE PLANEJAMENTO	
		
		Criado por Carlos Abreu
		
		local/Nome do arquivo:
		../planejamento/funcionarios_os_excel.php
		
		Versão 0 --> VERSÃO INICIAL : 02/03/2006
		Versão 1 --> Atualização Lay-out | Smarty : 30/07/2008		
		Versão 2 --> Atualização layout - Carlos Abreu - 31/03/2017
*/	
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(57))
{
	nao_permitido();
}


?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<?php

$conf = new configs();

$campo[1] = "FUNCIONÁRIOS POR OS - EXCEL";

$smarty->assign("campo",$campo);

$smarty->assign("revisao_documento",'V2');

$smarty->assign("classe",CSS_FILE);

$smarty->display('funcionarios_os_excel.tpl');

?>