<?php
/*
	Formulário de Unidades - materiais
	
	Criado por Carlos Abreu
	
	local/Nome do arquivo:
	
	../materiais/unidade.php
	
	Versão 0 --> VERSÃO INICIAL - 15/12/2008
	Versão 1 --> Atualização classe banco de dados - 21/01/2015
	Versão 2 --> Inclusão dos campos reg_del nas consultas - 01/12/2017 - Carlos Abreu
*/	
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

function voltar()
{
	$resposta = new xajaxResponse();

	$resposta->addScriptCall("reset_campos('frm_unidade')");
	
	$resposta->addAssign("btninserir", "value", "Inserir");
	
	$resposta->addEvent("btninserir", "onclick", "xajax_insere(xajax.getFormValues('frm_unidade'));");

	$resposta->addEvent("btnvoltar", "onclick", "javascript:location.href='menumateriais.php';");

	return $resposta;

}

function atualizatabela($ajax,$complemento = 0)
{
	$resposta = new xajaxResponse();

	$db = new banco_dados;
	
	$sql = "SELECT * FROM ".DATABASE.".unidade WHERE reg_del = 0 ORDER BY unidade.codigo_unidade ";

	$xml = new XMLWriter();
	$xml->openMemory();
	$xml->startElement('rows');
	
	$reg = $db->select($sql,'MYSQL', 
		function($reg, $i) use(&$xml,$ajax){
			$xml->startElement('row');
				$xml->writeAttribute('id', $reg['id_unidade']);
				$xml->writeElement('cell', $reg["codigo_unidade"]);
				$xml->writeElement('cell', $reg["unidade"]);
				$xml->writeElement('cell', $reg["desc_portugues"]);
				$xml->writeElement('cell', $reg["desc_ingles"]);
				$xml->writeElement('cell', $reg["desc_espanhol"]);
				$img = "<span class=\'icone icone-excluir cursor\' onclick=if(confirm(\'Deseja excluir este item?\')){xajax_excluir(".$reg['id_unidade'].");};></span>";
				$xml->writeElement('cell', $img);
				if (!empty($ajax))
				{
					if ($complemento>0)
					{
						$img = "<span class=\'icone icone-aprovar cursor\' onclick=seleciona(\'".strtoupper($reg['unidade'])."\',1);></span>";
					}
					else
					{
						$img = "<span class=\'icone icone-aprovar cursor\' onclick=seleciona(\'".strtoupper($reg['unidade'])."\');></span>";
					}
					$xml->writeElement('cell', $img);
				}
			$xml->endElement();
	});

	$xml->endElement();
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('unidades', true, '470', '".$conteudo."');");
	
	return $resposta;

}

function insere($dados_form)
{
	$resposta = new xajaxResponse();

	$db = new banco_dados;
	
	if(!empty($dados_form["unidade"]))
	{
		if (empty($dados_form["codigo"]))
		{
			$sql = "SELECT codigo_unidade ultimo FROM ".DATABASE.".unidade WHERE reg_del = 0 ORDER BY codigo_unidade DESC LIMIT 0, 1";
			$db->select($sql, 'MYSQL', true);
			$dados_form['codigo'] = sprintf('%02d', intval($db->array_select[0]['ultimo']) + 1);
		}
		
		$isql = "INSERT INTO ".DATABASE.".unidade ";
		$isql .= "(codigo_unidade, unidade, desc_portugues, desc_ingles, desc_espanhol) VALUES ( ";
		$isql .= "'" . $dados_form["codigo"] . "', ";
		$isql .= "'" . $dados_form["unidade"] . "', ";
		$isql .= "'" . $dados_form["descPort"] . "', ";
		$isql .= "'" . $dados_form["descIngles"] . "', ";
		$isql .= "'" . $dados_form["descEsp"] . "') ";

		//Carrega os registros
		$db->insert($isql,'MYSQL');
			
		$resposta->addScript("xajax_atualizatabela(document.getElementById('campoRef').value);");
		
		$resposta->addScript("xajax_voltar();");
	
		//Avisa o usuário do sucesso no cadastro das horas.		
		$resposta->addAlert("unidade cadastrado com sucesso.");	

	}
	else
	{
		$resposta->addAlert("Os campos devem estar preenchidos.");
	}	
	
	return $resposta;
}

function editar($id)
{

	$resposta = new xajaxResponse();

	$db = new banco_dados;
		
	$sql = "SELECT * FROM ".DATABASE.".unidade ";
	$sql .= "WHERE unidade.codigo_unidade = '".$id."' ";
	$sql .= "AND reg_del = 0 ";
	
	$registro = $db->select($sql,'MYSQL', true);

	$resposta->addAssign("id_unidade", "value",$id);
	$resposta->addAssign("codigo", "value",$db->array_select[0]["codigo_unidade"]);
	$resposta->addAssign("unidade", "value",$db->array_select[0]["unidade"]);
	$resposta->addAssign("descPort", "value",$db->array_select[0]["desc_portugues"]);
	$resposta->addAssign("descIngles", "value",$db->array_select[0]["desc_ingles"]);
	$resposta->addAssign("descEsp", "value",$db->array_select[0]["desc_espanhol"]);
	
	$resposta->addAssign("btninserir", "value", "Atualizar");
	$resposta->addEvent("btninserir", "onclick", "xajax_atualizar(xajax.getFormValues('frm_unidade'));");
	$resposta->addEvent("btnvoltar", "onclick", "xajax_voltar();");
	$resposta->addAssign('btnselecionar', 'disabled', '');
	
	return $resposta;
}

function atualizar($dados_form)
{
	$resposta = new xajaxResponse();
	$db = new banco_dados;
	
	if(!empty($dados_form["unidade"]))
	{
		$usql = "UPDATE ".DATABASE.".unidade SET ";
		$usql .= "codigo_unidade = '" . $dados_form["codigo"] . "', ";
		$usql .= "unidade = '" . $dados_form["unidade"] . "', ";
		$usql .= "desc_portugues = '" . $dados_form["descPort"] . "', ";
		$usql .= "desc_ingles = '" . $dados_form["descIngles"] . "', ";
		$usql .= "desc_espanhol = '" . $dados_form["descEsp"] . "' ";
		$usql .= "WHERE id_unidade = '".$dados_form["id_unidade"]."' ";
		$usql .= "AND reg_del = 0 ";

		$db->update($usql,'MYSQL');
		
		$resposta->addAlert("unidade atualizado com sucesso.");
		$resposta->addScript("xajax_atualizatabela(document.getElementById('campoRef').value);");
	}
	else
	{
		$resposta->addAlert("Os campos devem estar preenchidos.");
	}	

	$resposta->addScript("xajax_voltar();");	

	return $resposta;
}

function excluir($id, $what = '')
{
	$resposta = new xajaxResponse();

	$db = new banco_dados;
	/*
	$sql = "DELETE FROM ".DATABASE.".unidade ";
	$sql .= "WHERE unidade.id_unidade = '".$id."' ";
	
	$db->delete($sql,'MYSQL');
	*/
	
	$usql = "UPDATE ".DATABASE.".unidade SET ";
	$usql .= "reg_del = 1, ";
	$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
	$usql .= "data_del = '".date('Y-m-d')."' ";
	$usql .= "WHERE unidade.id_unidade = '".$id."' ";
	
	$db->update($usql,'MYSQL');	
	
	//Chama rotina para atualizar a tabela via AJAX
	$resposta->addScript("xajax_atualizatabela(document.getElementById('campoRef').value);");
	
	$resposta->addAlert("Registro excluido corretamente!");

	return $resposta;
}

$xajax->registerFunction("voltar");
$xajax->registerFunction("insere");
$xajax->registerFunction("editar");
$xajax->registerFunction("atualizar");
$xajax->registerFunction("excluir");
$xajax->registerFunction("atualizatabela");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","xajax_atualizatabela(document.getElementById('campoRef').value);");
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

	mygrid.setHeader("Código, unidade,Desc. Português,Desc. Inglês,Desc Espanhol, D, S");
	mygrid.setInitWidths("60,*,*,*,*,50,50");
	mygrid.setColAlign("left,left,left,left,left,center,center");
	mygrid.setColTypes("ro,ro,ro,ro,ro,ro,ro");
	mygrid.setColSorting("str,str,str,str,str,str,str");

	mygrid.attachEvent("onRowSelect",'xajax_editar');

	mygrid.setSkin("dhx_skyblue");
    mygrid.enableMultiselect(true);
    mygrid.enableCollSpan(true);	
	mygrid.init();
	mygrid.loadXMLString(xml);
}

function seleciona(codigo_unidade, complemento)
{
	campoReferencia = document.getElementById('campoRef').value;
	nomeAdicional = document.getElementById('adicional').value != '' ? document.getElementById('adicional').value : '';
	window.parent.document.getElementById(campoReferencia).value=codigo_unidade;

	if (nomeAdicional != '')
		window.parent.divPopupInst.destroi(1);
	else
		window.parent.divPopupInst.destroi();
}
</script>

<?php
$conf = new configs();

//Esta parte só é executada de fora do programa principal ex; materiais/produtos.php
if (isset($_GET['ajax']))
{
	$smarty->assign('ocultarCabecalhoRodape', 'style="display:none;"');
	$smarty->assign('campoReferencia', $_GET['ref']);
	if (isset($_GET['adicional']))
		$smarty->assign('adicional', $_GET['adicional']);
}

$smarty->assign("revisao_documento","V2");
$smarty->assign("campo",$conf->campos('unidade'));
$smarty->assign("botao",$conf->botoes());
$smarty->assign("classe",CSS_FILE);

$smarty->assign('larguraTotal', 1);

$smarty->display('unidade.tpl');