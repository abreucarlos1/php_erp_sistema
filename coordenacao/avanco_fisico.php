<?php
/*

		Formulário de Avanço Físico	
		
		Criado por Carlos Abreu
		
		local/Nome do arquivo:
		../coordenacao/avanco_fisico.php
		
		Versão 0 --> VERSÃO INICIAL : 26/08/2005
		Versão 1 --> ATUALIZAÇÃO LAYOUT : 31/03/2006
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
		
		$sql_filtro = " AND (ordem_servico.os LIKE '".$sql_texto."' ";
		$sql_filtro .= " OR empresas.empresa LIKE '".$sql_texto."' ";
		$sql_filtro .= " OR ordem_servico.descricao LIKE '".$sql_texto."') ";
		
	}

	$sql0 = "SELECT DISTINCT id_os, data, realizado, previsto, saldo FROM ".DATABASE.".avanco_fisico ";
	$sql0 .= "ORDER BY data ASC ";
	
	$db->select($sql0,'MYSQL', true);

	if($db->erro!='')
	{
		die($db->erro);
		
		return NULL;
	}

	foreach($db->array_select as $avf)
	{
		$avancoprev[$avf["id_os"]] = $avf["previsto"];
		$avancoreal[$avf["id_os"]] = $avf["realizado"];
		$avancosaldo[$avf["id_os"]] = $avf["saldo"];
		$avancodata[$avf["id_os"]] = $avf["data"];
	}
						
	$header = "<table id=\"tbl1\" class=\"dhtmlXGrid\" style=\"width:100%\">";
	$header .= "<tr>";
	$header .= "<td type=\"ro\">OS</td>";
	$header .= "<td type=\"ro\">Cliente</td>";
	$header .= "<td type=\"ro\">Descrição</td>";
	$header .= "<td type=\"ro\">Previsto</td>";
	$header .= "<td type=\"ro\">Realizado</td>";
	$header .= "<td type=\"ro\">Saldo</td>";
	$header .= "<td type=\"ro\">data</td>";
	$header .= "</tr>";
	
	$footer = "</table>";

	$conteudo = "";

	$sql = "SELECT * FROM ".DATABASE.".unidades, ".DATABASE.".empresas, ".DATABASE.".ordem_servico, ".DATABASE.".ordem_servico_status ";
	$sql .= "WHERE empresas.id_unidade = unidades.id_unidade ";
	$sql .= "AND ordem_servico.id_empresa = empresas.id_empresa ";
	$sql .= "AND empresas.id_unidade = unidades.id_unidade ";
	$sql .= "AND ordem_servico.id_os_status = ordem_servico_status.id_os_status ";
	$sql .= "AND ordem_servico_status.os_status NOT LIKE 'ENCERRADA' ";
	$sql .= $filtro_coord;
	$sql .= $sql_filtro;
	$sql .= "ORDER BY empresa, unidade, ordem_servico.os ";

	$db->select($sql,'MYSQL', true);

	if($db->erro!='')
	{
		die($db->erro);
		
		return NULL;
	}
	
	foreach($db->array_select as $cont_desp)
	{

		$conteudo .= "<tr>";
		$conteudo .= "<td>". sprintf("%05d",$cont_desp["os"])."</td>";
		$conteudo .= "<td>".$cont_desp["empresa"] ." - ".$cont_desp["unidade"] ."</td>";
		$conteudo .= "<td>".$cont_desp["descricao"] ."</td>";
		$conteudo .= "<td>".$avancoprev[$cont_desp["id_os"]] ." %</td>";
		$conteudo .= "<td>".$avancoreal[$cont_desp["id_os"]] ." %</td>";
		$conteudo .= "<td>".$avancosaldo[$cont_desp["id_os"]] ." %</td>";
		$conteudo .= "<td>".mysql_php($avancodata[$cont_desp["id_os"]]) ."</td>";		
		$conteudo .= "</tr>";	
	}

	$resposta->addAssign("avanco","innerHTML", $header.$conteudo.$footer);	
	
	$resposta->addScript("grid('');");

	return $resposta;
}

$xajax->registerFunction("atualizatabela");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","xajax_atualizatabela(xajax.getFormValues('frm'));");

$conf = new configs();

$smarty->assign("nome_formulario","AVANÇO FÍSICO");

$smarty->assign("classe",CSS_FILE);

$smarty->display("avanco_fisico.tpl");

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>utils.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script>

function grid()
{
	var mygrid = new dhtmlXGridFromTable('tbl1');
	mygrid.imgURL = "../includes/dhtmlx/dhtmlxGrid/codebase/imgs/";
	mygrid.enableAutoHeight(true,430);
	mygrid.enableRowsHover(true,'cor_mouseover');
	mygrid.setSkin("modern");
	
}

</script>