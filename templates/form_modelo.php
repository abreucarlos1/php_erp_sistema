<?php
/*

		Formulário de Modelo
		
		Criado por Carlos Abreu 
		
		local/Nome do arquivo:
		
		Versão x --> VERSÃO INICIAL - xx/xx/xxxx		
*/	

require("../includes/include_form.inc.php");


$smarty = new Smarty;
$smarty->compile_check = true;
$smarty->force_compile = true;

$db = new banco_dados;

function voltar()
{
	$resposta = new xajaxResponse();
	
	$resposta->addScript("xajax.$('frm').reset(); ");
	
	$resposta->addAssign("btninserir","value","Inserir");
	
	$resposta->addEvent("btninserir","onclick","xajax_insere(xajax.getFormValues('frm')); ");

	$resposta->addAssign("busca","disabled",false);
	
	$resposta->addScript("xajax_atualizatabela(''); ");	
	
	$resposta->addEvent("btnvoltar", "onclick", "history.back();");

	return $resposta;

}

function atualizatabela($filtro)
{
	$resposta = new xajaxResponse();
	
	$sql_filtro = "";
	
	$sql_texto = "";
	
	$db = new banco_dados;
	
	if($filtro!="")
	{
		$array_valor = explode(" ",$filtro);
		
		for($x=0;$x<count($array_valor);$x++)
		{
			$sql_texto .= "%" . $array_valor[$x] . "%";
		}
		
		$sql_filtro = " AND local.descricao LIKE '".$sql_texto."' ";

	}
	
	$sql_feriados = "SELECT *, feriados.descricao AS feriados_descricao, local.descricao AS local_Descricao FROM ".DATABASE.".feriados, ".DATABASE.".local ";
	$sql_feriados .= "WHERE feriados.id_local = local.id_local ";
	$sql_feriados .= $sql_filtro;
	$sql_feriados .= "GROUP BY id_feriado ";
	$sql_feriados .= " ORDER BY feriados.id_local, feriados.data ";	
	
	$reg_feriados = $db->select($sql_feriados,'MYSQL');

	$conteudo = "";
	
	$header = "<table id=\"tbl1\" class=\"dhtmlXGrid\" style=\"width:100%\">";
	$header .= "<tr>";
	$header .= "<td width=\"200\" type=\"ro\">local</td>";
	$header .= "<td width=\"100\" type=\"ro\">data</td>";
	$header .= "<td width=\"700\" type=\"ro\">Descrição</td>";
	$header .= "<td width=\"50\" type=\"ro\">D</td>";	
	$header .= "</tr>";
	
	$footer = "</table>";

	while($cont_feriados = mysqli_fetch_assoc($reg_feriados))
	{	
		$conteudo .= "<tr>";
		$conteudo .= "<td style=\"cursor:pointer;\" onclick=\"xajax_editar('" .  $cont_feriados["id_feriado"] . "');\">".$cont_feriados["local_Descricao"]."</td>";
		$conteudo .= "<td style=\"cursor:pointer;\" align=\"center\"  onclick=\"xajax_editar('" .  $cont_feriados["id_feriado"] . "');\">".mysql_php($cont_feriados["data"])."</td>";
		$conteudo .= "<td style=\"cursor:pointer;\" align=\"left\"  onclick=\"xajax_editar('" .  $cont_feriados["id_feriado"] . "');\">".$cont_feriados["feriados_descricao"]."</td>";
		$conteudo .= "<td align=\"center\"><img src=\"../images/buttons_action/apagar.gif\" style=\"cursor:pointer;\" onclick=\"if(confirm('Confirma a exclusão do feriado selecionado?')){xajax_excluir('" . $cont_feriados["id_feriado"] . "');}\"></td>";
		$conteudo .= "</tr>";	
	}

	$resposta->addAssign("feriados","innerHTML", $header.$conteudo.$footer);
	
	$resposta->addScript("grid('');");

	return $resposta;
}

function insere($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	if(count($dados_form["id_local"])>0 && $dados_form["data"]!=="" && $dados_form["descricao"]!=="")
	{		
		while($cont_id_local = each($dados_form["id_local"]))
		{		
			$sql_insere = "INSERT INTO ".DATABASE.".feriados (id_local, data, descricao) VALUES (";
			$sql_insere .= "'" . $cont_id_local[1] . "', ";
			$sql_insere .= "'" . php_mysql($dados_form["data"]) . "', ";
			$sql_insere .= "'" . addslashes(maiusculas($dados_form["descricao"])) . "') ";
			
			$cont_insere  = $db->insert($sql_insere,'MYSQL');
	
	
		}
	
		$resposta->addAlert("Feriado inserido com sucesso!");
		
		$resposta->addScript("xajax_voltar();");
		
		$resposta->addScript("xajax_atualizatabela(''); ");
	}
	else
	{
		$resposta->addAlert("É necessário preencher todos os campos!");
	}

	return $resposta;
}

function editar($id)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	$sql_feriados = "SELECT * FROM ".DATABASE.".feriados ";
	$sql_feriados .= "WHERE feriados.id_feriado = '" . $id . "' ";
	
	$cont_feriados = $db->select($sql_feriados,'MYSQL');

	$reg_feriados = mysqli_fetch_assoc($cont_feriados);
	
	$resposta->addAssign("data","value",mysql_php($reg_feriados["data"]));
	$resposta->addAssign("descricao","value",$reg_feriados["descricao"]);
	$resposta->addAssign("id_feriado","value",$reg_feriados["id_feriado"]);
	
	$resposta->addScript("xajax.$('id_local[]').multiple = false;");
	
	$resposta->addScript("seleciona_combo('" . $reg_feriados["id_local"] . "','id_local[]'); ");
	
	$resposta->addAssign("btninserir","value","Atualizar");
	
	$resposta->addEvent("btninserir","onclick","xajax_atualizar(xajax.getFormValues('frm_feriados')); ");
	
	$resposta->addAssign("busca","disabled",true);
	
	$resposta->addEvent("btnvoltar", "onclick", "xajax_voltar();");	
	
	return $resposta;	
}

function atualizar($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;

	$sql_atualiza = "UPDATE ".DATABASE.".feriados SET ";
	$sql_atualiza .= "data = '" . php_mysql($dados_form["data"]) . "', ";
	$sql_atualiza .= "descricao = '" . $dados_form["descricao"] . "', ";
	$sql_atualiza .= "id_local = '" . $dados_form["id_local"][0] . "' ";
	$sql_atualiza .= "WHERE id_feriado = '" . $dados_form["id_feriado"] . "' ";
	
	$cont_atualiza = $db->update($sql_atualiza,'MYSQL');

	if($cont_atualiza)
	{
		$resposta->addAlert("Feriado atualizado com sucesso!");
		
		$resposta->addScript("xajax.$('id_local[]').multiple = true;");

	}
	
	$resposta->addScript("xajax_voltar();");

	return $resposta;
}


function excluir($id)
{
	$resposta = new xajaxResponse();
			
	$db = new banco_dados;

	$dsql = "DELETE FROM ".DATABASE.".feriados WHERE id_feriado = '" . $id . "' ";
	
	$db->delete($dsql,'MYSQL');

	$resposta->addAlert("Feriado excluído com sucesso!");
	
	$resposta->addScript("xajax_atualizatabela(''); ");
	
	return $resposta;
}

$xajax->registerFunction("voltar");
$xajax->registerFunction("insere");
$xajax->registerFunction("editar");
$xajax->registerFunction("atualizar");
$xajax->registerFunction("excluir");
$xajax->registerFunction("atualizatabela");

$xajax->processRequests();


$smarty->assign("xajax_javascript",$xajax->printJavascript('../includes/xajax'));

$smarty->assign("body_onload","xajax_atualizatabela('');");



?>

<!-- Javascript para validação de dados -->
<script type="text/javascript" src="../includes/validacao.js"></script>


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
	mygrid.enableAutoHeight(true,500);
	mygrid.enableRowsHover(true,'cor_mouseover');
	mygrid.setSkin("modern");
	
}


function autoTab(input,len) 
{

	if(input.value.length==len)
	{
		document.getElementById('descricao').focus();
	}

}

</script>

<?php

$array_local_values = NULL;
$array_local_output = NULL;


$sql_local = "SELECT * FROM ".DATABASE.".local ";
$sql_local .= "ORDER BY local.descricao ";

$cont_local = $db->select($sql_local,'MYSQL');

while($reg_local = mysqli_fetch_assoc($cont_local))
{

	$array_local_values[] = $reg_local["id_local"];
	$array_local_output[] = $reg_local["descricao"];

}
					  	
$smarty->assign("option_local_values",$array_local_values);
$smarty->assign("option_local_output",$array_local_output);

$smarty->assign("nome_formulario","FERIADOS - V1");

$smarty->assign("classe",CSS_FILE);

$smarty->display('feriados.tpl');

?>