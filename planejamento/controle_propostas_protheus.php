<?php
/*
		Formul�rio de Acompanhamento propostas
		
		Criado por Carlos Abreu 
		
		local/Nome do arquivo:		
		../planejamento/controle_propostas_protheus.php

		Vers�o 0 --> VERS�O INICIAL - 08/04/2013
		Versao 1 --> Atualiza��o classe banco de dados - 22/01/2015 - Carlos Abreu
		Vers�o 2 --> Inclus�o dos campos reg_del nas consultas - 20/11/2017 - Carlos Abreu		
*/	

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

function preencheos($id_coordenador)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	$sql = "SELECT * FROM ".DATABASE.".OS, ".DATABASE.".ordem_servico_status, ".DATABASE.".empresas ";
	$sql .= "WHERE OS.id_empresa_erp = empresas.id_empresa_erp ";
	$sql .= "AND OS.reg_del = 0 ";
	$sql .= "AND ordem_servico_status.reg_del = 0 ";
	$sql .= "AND empresas.reg_del = 0 ";
	$sql .= "AND OS.id_os_status = ordem_servico_status.id_os_status ";
	$sql .= "AND os.os > 2000  ";
	$sql .= "AND (OS.id_cod_coord = '". $id_coordenador ."' OR OS.id_coord_aux = '". $id_coordenador ."') " ;
	$sql .= "AND ordem_servico_status.id_os_status IN (1,2,14,16) ";
	$sql .= "GROUP BY os.os ";
	$sql .= "ORDER BY os.os ";
	
	$db->select($sql,'MYSQL',true);
	
	$limp = "xajax.$('escolhaos').length = null";
	
	$resposta->addScript($limp);
	
	$comb = "xajax.$('escolhaos').options[xajax.$('escolhaos').length] = new Option('TODAS AS OS','-1');";

	foreach($db->array_select as $os)
	{
		$comb .= "xajax.$('escolhaos').options[xajax.$('escolhaos').length] = new Option('". sprintf("%05d",$os["os"])." - ".substr($os["descricao"],0,50)." - ".$os["empresa"] ."','". $os["id_os"] ."');";
	}

	$resposta->addScript($comb);
	
	return $resposta;

}

$xajax->registerFunction("preencheos");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<?php

$conf = new configs();

$db = new banco_dados;

$filtro = '';

$coordenador = false;

switch ($_SESSION["id_funcionario"])
{
	case '17': // L&Uacute;CIO
		$coordenador = true;
	break;
	case '19': // KATSUMI
		$coordenador = true;
	break;
	case '49': // SIMIOLI
		$coordenador = true;
	break;
	case '16': // FL&Aacute;VIO
		$coordenador = true;
	break;
	case '51': // JORGE
		$coordenador = true;
	break;
	case '18': // FERNANDO
		$coordenador = true;
	break;
	case '7': // CARLOS RODRIGUES
		$coordenador = true;
	break;
	
	case '6': // CARLOS ABREU
		$coordenador = true;
		//$filtro = "AND OS.id_cod_coord = '148' ";
	break;
	
	case '689': //Ewerton
		$coordenador = true;
	break;

	default:
		$coordenador = false;
	break;	
}

$array_coordenador_values = NULL;
$array_coordenador_output = NULL;

$array_os_values = NULL;
$array_os_output = NULL;


if($coordenador)
{
	$array_coordenador_values[] = "-1";
	$array_coordenador_output[] = "TODOS OS COORDENADORES";
}


$sql = "SELECT * FROM ".DATABASE.".OS, ".DATABASE.".ordem_servico_status, ".DATABASE.".funcionarios ";
$sql .= "WHERE OS.id_os_status = ordem_servico_status.id_os_status ";
$sql .= "AND OS.reg_del = 0 ";
$sql .= "AND ordem_servico_status.reg_del = 0 ";
$sql .= "AND funcionarios.reg_del = 0 ";
$sql .= "AND funcionarios.situacao NOT IN ('DESLIGADO','CANCELADO','CANCELADODVM') ";
$sql .= "AND funcionarios.nivel_atuacao IN ('D','C') ";
$sql .= "AND os.os > 2000 ";

if(!$coordenador)
{
	$sql .= $filtro;
}

$sql .= "AND OS.id_cod_coord = funcionarios.id_funcionario ";
$sql .= "GROUP BY funcionarios.id_funcionario ";
$sql .= "ORDER BY funcionarios.funcionario ";

$db->select($sql,'MYSQL',true);
	 
foreach ($db->array_select as $regs)
{
	$array_coordenador_values[] = $regs["id_funcionario"];
	$array_coordenador_output[] = $regs["funcionario"];
}

$sql = "SELECT * FROM ".DATABASE.".OS, ".DATABASE.".ordem_servico_status, ".DATABASE.".empresas ";
$sql .= "WHERE OS.id_empresa_erp = empresas.id_empresa_erp ";
$sql .= "AND OS.reg_del = 0 ";
$sql .= "AND ordem_servico_status.reg_del = 0 ";
$sql .= "AND empresas.reg_del = 0 ";
$sql .= "AND OS.id_os_status = ordem_servico_status.id_os_status ";
$sql .= "AND os.os > 1700 ";
$sql .= $filtro;
$sql .= "AND ordem_servico_status.id_os_status NOT IN (3,8,9,12) ";
$sql .= "GROUP BY os.os ";
$sql .= "ORDER BY os.os ";

$db->select($sql,'MYSQL',true);

if($db->numero_registros>0)
{
	$array_os_values[] = "-1";
	$array_os_output[] = "TODAS AS OS";
}
						 
foreach ($db->array_select as $regs)
{
	$array_os_values[] = $regs["os"];
	$array_os_output[] =  sprintf("%05d",$regs["os"])." - ".substr($regs["descricao"],0,50)." - ".$regs["empresa"];
}

$check = "";

$sql = "SELECT * FROM ".DATABASE.".setores ";
$sql .= "WHERE setores.reg_del = 0 ";
$sql .= "ORDER BY setor ";

$db->select($sql,'MYSQL',true);

$check .= "<label class=\"labels\"><input type=\"checkbox\" name=\"chk_TODOS\" id=\"chk_TODOS\" value=\"-1\" onclick=\"if(this.checked){setcheckbox('frm_rel','check');}else{setcheckbox('frm_rel','');}\">TODOS</label><br>";

foreach($db->array_select as $reg)
{
	$check .= "<label class=\"labels\"><input type=\"checkbox\" name=\"chk_".$reg["id_setor"]."\" id=\"chk_".$reg["id_setor"]."\" value=\"1\" />".$reg["setor"]."</label><br>";
}

$smarty->assign("check_equipe",$check);

$smarty->assign("option_coordenador_values",$array_coordenador_values);
$smarty->assign("option_coordenador_output",$array_coordenador_output);

$smarty->assign("option_os_values",$array_os_values);
$smarty->assign("option_os_output",$array_os_output);

$campo[1] = "ACOMPANHAMENTO DE PROPOSTAS - PROTHEUS";

$smarty->assign("campo",$campo);

$smarty->assign("revisao_documento","V3");

$smarty->assign("classe",CSS_FILE);

$smarty->display('controle_propostas_protheus.tpl');

?>