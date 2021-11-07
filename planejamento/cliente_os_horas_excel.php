<?php
/*

		Formulário de Cliente/OS/Horas em Excel
		
		Criado por Carlos Abreu
		
		local/Nome do arquivo:
		
		../planejamento/cliente_os_horas_excel.php
		
		Versão 0 --> VERSÃO INICIAL - 02/12/2009
		Versao 1 --> Atualização classe banco de dados - 21/01/2015 - Carlos Abreu
		Versão 2 --> Atualização layout - Carlos Abreu - 31/03/2017		
*/	


require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(214))
{
	nao_permitido();
}


?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<?php

$conf = new configs();

$campo[1] = "CLIENTE/OS/FUNC/HORAS EXCEL";

$smarty->assign("campo",$campo);

$smarty->assign("revisao_documento",'V2');

$smarty->assign("classe",CSS_FILE);

$smarty->display('cliente_os_horas_excel.tpl');

?>