<?php
/*
		Formulário de Exames Vencidos	
		
		Criado por Carlos Abreu 
		
		local/Nome do arquivo:
		../rh/exames_vencidos.php
		
		Versão 0 --> VERSÃO INICIAL : 02/03/2006
		Versão 1 --> Atualização Lay-out : 12/08/2008		
		Versão 2 --> Atualização layout - Carlos Abreu - 05/04/2017
		Versão 3 --> Inclusão dos campos reg_del nas consultas - 28/11/2017 - Carlos Abreu
*/	

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(97) && !verifica_sub_modulo(273))
{
	nao_permitido();
}

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<?php

$conf = new configs();

$index = "";
$array_mes_values = NULL;
$array_mes_output = NULL;

$array = NULL;

$array[1] = "JANEIRO";
$array[2] = "FEVEREIRO";
$array[3] = "MARÇO";
$array[4] = "ABRIL";
$array[5] = "MAIO"; 
$array[6] = "JUNHO";
$array[7] = "JULHO";
$array[8] = "AGOSTO";
$array[9] = "SETEMBRO";
$array[10] = "OUTUBRO";
$array[11] = "NOVEMBRO";
$array[12] = "DEZEMBRO";

for($i=1;$i<=12;$i++)
{
	$array_mes_values[] = sprintf("%02d",$i);
	$array_mes_output[] = $array[$i];
	if(date("m")==$i)
	{
		$index = sprintf("%02d",$i);
	}
}

for($y=date("Y");$y>=(date("Y")-5);$y--)
{
	$array_ano_values[] = $y;
	$array_ano_output[] = $y;
}

$smarty->assign("option_mes_values",$array_mes_values);

$smarty->assign("option_mes_id",$index);

$smarty->assign("option_mes_output",$array_mes_output);

$smarty->assign("option_ano_values",$array_ano_values);

$smarty->assign("option_ano_output",$array_ano_output);

$campo[1] = "EXAMES ASO VENCIDOS";

$smarty->assign("campo",$campo);

$smarty->assign("revisao_documento",'V3');

$smarty->assign("classe",CSS_FILE);

$smarty->display('exames_vencidos.tpl');

?>

