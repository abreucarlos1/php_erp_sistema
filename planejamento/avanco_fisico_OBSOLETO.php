<?php
/*

		Formulário de Avanço Físico	
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		../coordenacao/avanco_fisico.php
		
		Versão 0 --> VERSÃO INICIAL : 26/08/2005
		Versão 1 --> Atualização LAYOUT : 31/03/2006
		Versão 2 --> Atualização Lay-out | Smarty : 21/07/2008
		
		
*/

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

function atualizatabela($filtro)
{
	session_start();
	
	$resposta = new xajaxResponse();	
	
	$db = new banco_dados;
	
	$sql_filtro = "";
	
	$sql_texto = "";
	
	if($filtro!="")
	{
		$array_valor = explode(" ",$filtro);
		
		for($x=0;$x<count($array_valor);$x++)
		{
			$sql_texto .= "%" . $array_valor[$x] . "%";
		}
		
		$sql_filtro = " AND (os.os LIKE '".$sql_texto."' ";
		$sql_filtro .= " OR empresas.empresa LIKE '".$sql_texto."' ";
		$sql_filtro .= " OR OS.descricao LIKE '".$sql_texto."') ";
		
	}

	$sql0 = "SELECT DISTINCT id_os, data, realizado, previsto, saldo FROM ".DATABASE.".avanco_fisico ";
	$sql0 .= "ORDER BY data ASC ";
	$reg = mysql_query($sql0,$db->conexao) or $resposta->addAlert("Não foi possível fazer a seleção." . $sql0);
						
	while ($avf = mysql_fetch_array($reg))
	{
		$avancoprev[$avf["id_os"]] = $avf["previsto"];
		$avancoreal[$avf["id_os"]] = $avf["realizado"];
		$avancosaldo[$avf["id_os"]] = $avf["saldo"];
		$avancodata[$avf["id_os"]] = $avf["data"];
	}
	
	$sql = "SELECT * FROM ".DATABASE.".unidades, ".DATABASE.".empresas, ".DATABASE.".OS, ".DATABASE.".ordem_servico_status ";
	$sql .= "WHERE empresas.id_unidade = unidades.id_unidade ";
	$sql .= "AND OS.id_empresa = empresas.id_empresa ";
	$sql .= "AND empresas.id_unidade = unidades.id_unidade ";
	$sql .= "AND OS.id_os_status = ordem_servico_status.id_os_status ";
	$sql .= "AND ordem_servico_status.os_status NOT LIKE 'ENCERRADA' ";
	$sql .= "AND os.os > 100 ";
	//$sql .= $filtro_coord;
	$sql .= $sql_filtro;
	$sql .= "ORDER BY empresa, unidade, OS ";
	
	$reg_os = mysql_query($sql,$db->conexao) or $resposta->addAlert("Não foi possível ffazer a seleção." . $sql_os);

	$header = "<table id=\"tbl1\" class=\"dhtmlXGrid\" style=\"width:100%\">";
	$header .= "<tr>";
	$header .= "<td type=\"ro\">OS INT</td>";
	$header .= "<td type=\"ro\">Cliente</td>";
	$header .= "<td type=\"ro\">Descrição</td>";
	$header .= "<td type=\"ro\">Previsto</td>";
	$header .= "<td type=\"ro\">Realizado</td>";
	$header .= "<td type=\"ro\">Saldo</td>";
	$header .= "<td type=\"ro\">data</td>";
	$header .= "</tr>";
	
	$footer = "</table>";

	$conteudo = "";
	
	while($cont_desp = mysql_fetch_array($reg_os))
	{

		$conteudo .= "<tr>";
		$conteudo .= "<td style=\"cursor:pointer;\" onclick=\"javascript:location.href='definiravanco.php?id_os=".$cont_desp["id_os"]."';\">". sprintf("%05d",$cont_desp["os"])."</td>";
		$conteudo .= "<td style=\"cursor:pointer;\" onclick=\"javascript:location.href='definiravanco.php?id_os=".$cont_desp["id_os"]."';\">".$cont_desp["empresa"] ." - ".$cont_desp["unidade"] ."</td>";
		$conteudo .= "<td style=\"cursor:pointer;\" onclick=\"javascript:location.href='definiravanco.php?id_os=".$cont_desp["id_os"]."';\">".$cont_desp["descricao"] ."</td>";
		$conteudo .= "<td style=\"cursor:pointer;\" onclick=\"javascript:location.href='definiravanco.php?id_os=".$cont_desp["id_os"]."';\">".$avancoprev[$cont_desp["id_os"]] ." %</td>";
		$conteudo .= "<td style=\"cursor:pointer;\" onclick=\"javascript:location.href='definiravanco.php?id_os=".$cont_desp["id_os"]."';\">".$avancoreal[$cont_desp["id_os"]] ." %</td>";
		$conteudo .= "<td style=\"cursor:pointer;\" onclick=\"javascript:location.href='definiravanco.php?id_os=".$cont_desp["id_os"]."';\">".$avancosaldo[$cont_desp["id_os"]] ." %</td>";
		$conteudo .= "<td style=\"cursor:pointer;\" onclick=\"javascript:location.href='definiravanco.php?id_os=".$cont_desp["id_os"]."';\">".mysql_php($avancodata[$cont_desp["id_os"]]) ."</td>";
		$conteudo .= "</tr>";	
	}

	$resposta->addAssign("avanco","innerHTML", $header.$conteudo.$footer);
	
	
	
	$resposta->addScript("grid('');");

	$db->fecha_db();

	return $resposta;

}

$smarty = new Smarty;

$smarty->left_delimiter = "<smarty>";

$smarty->right_delimiter = "</smarty>";

$smarty->template_dir = "templates";

$smarty->compile_check = true;

$smarty->force_compile = true;

$xajax = new xajaxExtend;

$xajax->setCharEncoding("utf-8");

$xajax->decodeUTF8InputOn();

$xajax->registerPreFunction("checaSessao");

$xajax->registerFunction("atualizatabela");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript('../includes/xajax'));

$smarty->assign("body_onload","xajax_atualizatabela('');");

?>

<!-- Javascript para validação de dados -->
<script type="text/javascript" src="../includes/validacao.js"></script>

<!-- Grid -->
<script type="text/javascript" src="../includes/dhtmlx/dhtmlxGrid/codebase/dhtmlxcommon.js"></script>
<script type="text/javascript" src="../includes/dhtmlx/dhtmlxGrid/codebase/dhtmlxgrid.js"></script>		
<script type="text/javascript" src="../includes/dhtmlx/dhtmlxGrid/codebase/dhtmlxgridcell.js"></script>
<script type="text/javascript" src="../includes/dhtmlx/dhtmlxGrid/codebase/ext/dhtmlxgrid_start.js"></script>

<script>

xajax.loadingFunction = function() {xajax.$('aguarde').style.display = 'block';}
xajax.doneLoadingFunction = function() {xajax.$('aguarde').style.display='none';}


function grid()
{
	var mygrid = new dhtmlXGridFromTable('tbl1');
	mygrid.imgURL = "../includes/dhtmlx/dhtmlxGrid/codebase/imgs/";
	mygrid.enableAutoHeight(true,430);
	mygrid.enableRowsHover(true,'cor_mouseover');
	mygrid.setSkin("modern");
	
}


</script>

<?php


$smarty->assign("nome_formulario","AVANÇO FÍSICO");

$smarty->assign("classe",CSS_FILE);

$smarty->display("avanco_fisico.tpl");

?>