<?php
/*
		Formul�rio de HORAS POR PER�ODO	
		
		Criado por Carlos Abreu / Ot�vio Pamplon ia
		
		local/Nome do arquivo:
		../planejamento/relatorios_horas_adicionais.php
		
		Vers�o 0 --> VERS�O INICIAL : 02/03/2006		
		Versao 1 --> atualiza��o classe banco de dados - 22/01/2015 - Carlos Abreu
		Vers�o 2 --> Atualiza��o Layout : 10/04/2015 - Eduardo
		Vers�o 3 --> atualiza��o layout - Carlos Abreu - 03/04/2017
*/
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO M�DULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(261))
{
	nao_permitido();
}

$conf = new configs();
?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>datetimepicker.js"></script>

<?php
$array = array("JANEIRO","FEVEREIRO","MAR�O","ABRIL","MAIO","JUNHO","JULHO","AGOSTO","SETEMBRO","OUTUBRO","NOVEMBRO","DEZEMBRO");

for($i=1;$i<=12;$i++)
{
	$array_per_values[] = sprintf("%02d",$i);
	$array_per_output[] = $array[$i-1];
	if(date("m")==$i)
	{
		$index = sprintf("%02d",$i);
	}
}

$smarty->assign("option_per_values",$array_per_values);
$smarty->assign("option_per_id",$index);
$smarty->assign("option_per_output",$array_per_output);

$smarty->assign("data",date("d/m/Y"));

$smarty->assign('campo', $conf->campos('controle_horas_adicionais'));

$smarty->assign('revisao_documento', 'V3');

$smarty->assign("classe",CSS_FILE);

$smarty->display('relatorios_horas_adicionais.tpl');
?>