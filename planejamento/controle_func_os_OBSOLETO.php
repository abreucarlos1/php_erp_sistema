<?php
/*

		Formulário de OS POR FUNCIONÁRIOS
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		
		../planejamento/controle_func_os.php
		
		Versão 0 --> VERSÃO INICIAL - 02/03/2006
		Versão 1 --> Atualização Lay-out | Smarty : 22/07/2008
		
*/	

session_start();

function checaSessao()
{
	$resposta = new xajaxResponse();
	session_start();

	if(!isset($_SESSION["id_usuario"]) || !isset($_SESSION["nome_usuario"]))
	{

		$resposta->addAlert("A sessão expirou. É necessário efetuar o login novamente. ");
		// Usuário não logado! Redireciona para a página de login
		$resposta->addRedirect("../index.php?pagina=" . $_SERVER['PHP_SELF']);

	}

	return $resposta;
}

require("../includes/conectdb.inc.php");
include("../includes/tools.inc.php");
require("../includes/smarty/libs/Smarty.class.php");
require("../includes/xajax/xajaxExtend.php");


$smarty = new Smarty;

$smarty->left_delimiter = "<smarty>";

$smarty->right_delimiter = "</smarty>";

$smarty->template_dir = "templates";

$smarty->compile_check = true;

$smarty->force_compile = true;

$db = new banco_dados;
$db->db = 'ti';
$db->conexao_db();


$xajax = new xajaxExtend;

$xajax->setCharEncoding("utf-8");

$xajax->decodeUTF8InputOn();

$xajax->registerPreFunction("checaSessao");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript('../includes/xajax'));

?>
<!-- Javascript para validação de dados -->
<script type="text/javascript" src="../includes/validacao.js"></script>

<?

$array_os_values = NULL;
$array_os_output = NULL;


$array_os_values[] = "-1";
$array_os_output[] = "TODOS AS OS";

$sql = "SELECT * FROM ".DATABASE.".OS, ".DATABASE.".ordem_servico_status, ".DATABASE.".empresas ";
$sql .= "WHERE OS.id_empresa = empresas.id_empresa ";
$sql .= "AND OS.id_os_status = ordem_servico_status.id_os_status ";
$sql .= "AND ordem_servico_status.os_status NOT LIKE 'ENCERRADA' ";
$sql .= "AND ordem_servico_status.os_status NOT LIKE 'AS BUILT' ";
//$sql .= "AND os.os > 100 ";
$sql .= "GROUP BY os.os ";
$sql .= "ORDER BY os.os ";
$registro = mysql_query($sql,$db->conexao) or die("Não foi possível realizar a seleção." . $sql);
 
while ($regs = mysql_fetch_array($registro))
{
	$array_os_values[] = $regs["id_os"];
	$array_os_output[] = sprintf("%05d",$regs["os"]) ." - ".$regs["ordem_servico_cliente"]." - ".$regs["empresa"];
}

$smarty->assign("option_os_values",$array_os_values);
$smarty->assign("option_os_output",$array_os_output);

$smarty->assign("nome_formulario","OS POR FUNCIONÁRIOS");

$smarty->assign("classe","setor_adm");

$db->fecha_db();

$smarty->display('controle_func_os.tpl');	

?>