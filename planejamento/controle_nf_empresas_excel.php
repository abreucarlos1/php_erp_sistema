<?php
/*
		Formul�rio de Controle NF empresas	
		
		Criado por Carlos Abreu / Ot�vio Pamplon ia
		
		local/Nome do arquivo:
		../planejamento/controle_nf_empresas_excel.php
		
		Vers�o 0 --> VERS�O INICIAL : 02/03/2006
		Vers�o 1 --> Atualiza��o Lay-Out | Smarty : 30/07/2008	
		Vers�o 2 --> Atualiza��o classe banco de dados - 22/01/2015 - Carlos Abreu
		Vers�o 3 --> atualiza��o layout - Carlos Abreu - 31/03/2017
		Vers�o 4 --> Inclus�o dos campos reg_del nas consultas - 20/11/2017 - Carlos Abreu
*/	

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<?php

$conf = new configs();

$db = new banco_dados;

$array_periodo_values = NULL;
$array_periodo_output = NULL;

$array_periodo_values[] = "";
$array_periodo_output[] = "SELECIONE O PER�ODO";


$sql = "SELECT * FROM ".DATABASE.".fechamento_folha ";
$sql .= "WHERE fechamento_folha.reg_del = 0 ";
$sql .= "GROUP BY fechamento_folha.periodo ";
$sql .= "ORDER BY fechamento_folha.periodo DESC ";

$db->select($sql,'MYSQL',true);

foreach($db->array_select as $cont_periodo)
{
	$array_periodo = explode(",",$cont_periodo["periodo"]);
	$per_dataini = substr($array_periodo[0],-2,2) . "/" . substr($array_periodo[0],0,4);
	$per_datafin = substr($array_periodo[1],-2,2) . "/" . substr($array_periodo[1],0,4);

	$array_periodo_values[] = $array_periodo[0]."-26"."#".$array_periodo[1]."-25";
	$array_periodo_output[] = $per_dataini . " - " . $per_datafin;
}

$smarty->assign("option_periodo_values",$array_periodo_values);
$smarty->assign("option_periodo_output",$array_periodo_output);

$campo[1] = "NOTAS FISCAIS - EMPRESAS FUNCION�RIOS";

$smarty->assign("campo",$campo);

$smarty->assign("revisao_documento","V4");

$smarty->assign("classe",CSS_FILE);

$smarty->display('controle_nf_empresas_excel.tpl');

?>
