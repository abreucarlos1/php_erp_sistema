<?php
/*
		Formulário de NF	
		
		Criado por Carlos Abreu
		
		local/Nome do arquivo:
		../planejamento/controle_nf_empresas_os_excel.php
		
		Versão 0 --> VERSÃO INICIAL : 02/03/2006
		Versão 1 --> Atualização Lay-out | Smarty 
		Versão 2 --> Atualização layout - Carlos Abreu - 31/03/2017		
*/	
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<?php

$conf = new configs();

$campo[1] = "NOTAS FISCAIS FUNCIONÁRIOS POR OS";

$smarty->assign("campo",$campo);

$smarty->assign("revisao_documento",'V2');

$smarty->assign("classe",CSS_FILE);

$smarty->display('controle_nf_empresas_os_excel.tpl');

?>
