<?php
/*
	Formulário de EPI's
	Criado por Carlos
	local/Nome do arquivo: ../rh/epi.php
	Versão 0 --> VERSÃO INICIAL - 15/05/2017
	Versão 1 --> Inclusão dos campos reg_del nas consultas - 28/11/2017 - Carlos Abreu	
*/
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

function voltar()
{
	$resposta = new xajaxResponse();

	$resposta->addScriptCall("reset_campos('frm_epi')");
	
	$resposta->addAssign("btninserir", "value", "Inserir");
	
	$resposta->addEvent("btninserir", "onclick", "xajax_salvar(xajax.getFormValues('frm_epi'));");

	$resposta->addEvent("btnvoltar", "onclick", "history.back();");

	return $resposta;

}

function atualizatabela($filtro)
{
	$resposta = new xajaxResponse();

	$db = new banco_dados();
	
	$sql_filtro = "";
	
	$sql_texto = "";
	
	if($filtro!="")
	{
		$sql_texto = str_replace('  ', ' ', AntiInjection::clean($filtro));
		$sql_texto = str_replace(' ', '%', '%'.$sql_texto.'%');
		
		$sql_filtro = "AND (epi LIKE '".$sql_texto."' ";
		$sql_filtro .= "OR DATE_FORMAT(data_validade,'%d/%m/%Y') LIKE '".$sql_texto."' ";
		$sql_filtro .= "OR fabricante LIKE '".$sql_texto."' ";
		$sql_filtro .= "OR ca LIKE '".$sql_texto."' ";
		$sql_filtro .= "OR observacoes LIKE '".$sql_texto."') ";
	}
	
	$sql = "SELECT * FROM ".DATABASE.".epi ";
	$sql .= "WHERE epi.reg_del = 0 ";
	$sql .= "AND epi.atual = 1 ".$sql_filtro." ";
	$sql .= "ORDER BY data_insercao DESC ";

	$xml = new XMLWriter();
	$xml->openMemory();
	$xml->startElement('rows');
	
	$reg = $db->select($sql,'MYSQL', 
		function($reg, $i) use(&$xml,$ajax){
			$xml->startElement('row');
				$xml->writeAttribute('id', $reg['id_epi']);
				$xml->writeElement('cell', mysql_php($reg["data_insercao"]));
				$xml->writeElement('cell', $reg["epi"]);
				$xml->writeElement('cell', $reg["ca"]);
				$xml->writeElement('cell', mysql_php($reg["data_validade"]));
				$xml->writeElement('cell', $reg["fabricante"]);
				$xml->writeElement('cell', $reg["observacoes"]);
				$img = "<span class=\'icone icone-excluir cursor\' onclick=if(confirm(\'Deseja excluir este item?\')){xajax_excluir(".$reg['id_epi'].");};></span>";
				$xml->writeElement('cell', $img);
			$xml->endElement();
	});

	$xml->endElement();
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('div_epi', true, '270', '".$conteudo."');");
	
	return $resposta;
}

function salvar($dados_form)
{
	$resposta = new xajaxResponse();

	$db = new banco_dados();
	
	if (!empty($dados_form["id_epi"]))
	{
		//Mantendo o histórico
		$usql = "UPDATE ".DATABASE.".epi SET ";
		$usql .= "atual = 0 ";
		$usql .= "WHERE epi.reg_del = 0 ";
		$usql .= "AND epi.id_epi = '".$dados_form['id_epi']."'";
		
		$db->update($usql, 'MYSQL');
		
		$sql = "SELECT cod_anterior FROM ".DATABASE.".epi ";
		$sql .= "WHERE epi.reg_del = 0 ";
		$sql .= "AND epi.id_epi = '".$dados_form['id_epi']."' ";
		$sql .= "AND epi.cod_anterior IS NOT NULL ";
		
		$db->select($sql, 'MYSQL', true);
		
		$codAnterior = $db->numero_registros > 0 ? $db->array_select[0]['cod_anterior'] : $dados_form['id_epi'];
	}
	
	$isql = "INSERT INTO ".DATABASE.".epi ";
	$isql .= "(epi, ca, data_validade, fabricante, observacoes, atual, cod_anterior, data_insercao) VALUES ( ";
	$isql .= "'".trim(maiusculas($dados_form["descricao_epi"]))."', ";
	$isql .= "'".trim(maiusculas($dados_form["ca"]))."', ";
	$isql .= "'".php_mysql($dados_form['vencimento'])."', ";
	$isql .= "'".trim(maiusculas($dados_form["fabricante"]))."', ";
	$isql .= "'".trim(maiusculas($dados_form["obs"]))."', ";
	$isql .= "1, '".$codAnterior."', '".date('Y-m-d')."') ";
	
	$db->insert($isql,'MYSQL');
		
	$resposta->addScript("xajax_atualizatabela()");
	
	$resposta->addScript("xajax_voltar();");

	$resposta->addAlert("EPI cadastrado corretamente.");
	
	return $resposta;
}

function editar($id)
{
	$resposta = new xajaxResponse();

	$db = new banco_dados;
		
	$sql = "SELECT * FROM ".DATABASE.".epi ";
	$sql .= "WHERE epi.id_epi = ".$id." ";
	$sql .= "AND epi.reg_del = 0 ";
	
	$db->select($sql,'MYSQL', true);

	$resposta->addAssign("id_epi", "value",$db->array_select[0]["id_epi"]);
	$resposta->addAssign("descricao_epi", "value",$db->array_select[0]["epi"]);
	$resposta->addAssign("ca", "value",$db->array_select[0]["ca"]);
	$resposta->addAssign("fabricante", "value",$db->array_select[0]["fabricante"]);
	$resposta->addAssign("obs", "value",$db->array_select[0]["observacoes"]);
	$resposta->addAssign("vencimento", "value",mysql_php($db->array_select[0]["data_validade"]));
	
	$resposta->addAssign("btninserir", "value", "Atualizar");
	$resposta->addEvent("btnvoltar", "onclick", "xajax_voltar();");
	$resposta->addAssign('btnselecionar', 'disabled', '');
	
	return $resposta;
}

function excluir($id)
{
	$resposta = new xajaxResponse();

	$db = new banco_dados;
	
	$usql = "UPDATE ".DATABASE.".epi SET ";
	$usql .= "reg_del = 1, ";
	$usql .= "reg_who = '".$_SESSION['id_funcionario']."', ";
	$usql .= "data_del = '".date('Y-m-d')."' ";
	$usql .= "WHERE id_epi = '".$id."' ";
	
	$db->update($usql,'MYSQL');
	
	$resposta->addAlert("Registro excluido corretamente!");
	$resposta->addScript("xajax_atualizatabela()");
	$resposta->addScript("xajax_voltar()");

	return $resposta;
}

$xajax->registerFunction("voltar");
$xajax->registerFunction("salvar");
$xajax->registerFunction("editar");
$xajax->registerFunction("atualizar");
$xajax->registerFunction("excluir");
$xajax->registerFunction("atualizatabela");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript('../includes/xajax'));

$smarty->assign("body_onload","xajax_atualizatabela();");
?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script>
function grid(tabela, autoh, height, xml)
{
	mygrid = new dhtmlXGridObject(tabela);

	mygrid.setImagePath("../includes/dhtmlx_403/codebase/imgs/");	
	mygrid.enableAutoHeight(autoh,height);
	mygrid.enableRowsHover(true,'cor_mouseover');

	mygrid.setHeader("Inserido em, EPI, CA, Validade CA, Fabricante, Observações, D");
	mygrid.setInitWidths("90,190,60,90, 190, *, 50");
	mygrid.setColAlign("left,left,left,left,left,left, center");
	mygrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro");
	mygrid.setColSorting("str,str,str,str,str,str,str,str");

	mygrid.attachEvent("onRowSelect",'xajax_editar');

	mygrid.setSkin("dhx_skyblue");
    mygrid.enableMultiline(true);
	mygrid.init();
	mygrid.loadXMLString(xml);
}
</script>

<?php
$conf = new configs();

$smarty->assign("revisao_documento","V1");

$smarty->assign("campo",$conf->campos('epi'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("classe",CSS_FILE);

$smarty->display('epi.tpl');

?>