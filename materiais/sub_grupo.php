<?php
/*
    Formulário de Sub-Grupos de materiais
    
    Criado por Carlos Abreu / Otávio Pamplona
    
    local/Nome do arquivo:
    
    ../materiais/sub_grupo.php
    
    Versão 0 --> VERSÃO INICIAL - 15/12/2008
    Versao 1 --> Atualização da classe banco de dados - 21/01/2015 - Carlos Abreu
    Versão 2 --> Atualização do layout - 27/10/2017 - Carlos Máximo
	Versão 3 --> Inclusão dos campos reg_del nas consultas - 01/12/2017 - Carlos Abreu		
*/
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
require_once(INCLUDE_DIR."include_form.inc.php");

function voltar()
{
	$resposta = new xajaxResponse();

	$resposta->addScriptCall("reset_campos('frm_sub_grupo')");
	
	$resposta->addAssign("btninserir", "value", "Inserir");
	
	$resposta->addEvent("btninserir", "onclick", "xajax_insere(xajax.getFormValues('frm_sub_grupo'));");

	$resposta->addEvent("btnvoltar", "onclick", "javascript:location.href='menumateriais.php';");

	return $resposta;

}

function getAtributos($dados_form)
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();
	
	$clausulaSubGrupo = '';
	if (!empty($dados_form['id_sub_grupo']))
		$clausulaSubGrupo = ' AND id_sub_grupo = '.$dados_form['id_sub_grupo'];
	
	$sql = "SELECT
			  *
			FROM
			  ".DATABASE.".sub_grupo
			  JOIN(
			    SELECT
			      id_atributo, atributo, subGrupo, codGrupo
			    FROM
			      ".DATABASE.".atributos
			      JOIN(
			        SELECT id_sub_grupo subGrupo, id_atributo codAtributo, id_grupo codGrupo FROM ".DATABASE.".atributos_x_sub_grupo WHERE atributos_x_sub_grupo.reg_del = 0
			      ) atrXSub
			      ON codAtributo = id_atributo
			    WHERE atributos.reg_del = 0
			  ) atributos
			  ON subGrupo IN(id_sub_grupo, 0)
			  AND codGrupo = codigo_grupo			  
			WHERE sub_grupo.reg_del = 0 AND codigo_grupo = ".$dados_form['codigo_grupo'].$clausulaSubGrupo;
	
	$db->select($sql, 'MYSQL', true);
	
	/*Montar o retorno aqui*/
	
	return $resposta;
}

function atualizatabela($filtro, $combo='')
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();

	$sql_filtro = "";
	$sql_texto = "";
	
	if($filtro!="")
	{		
		$array_valor = explode(" ",$filtro);
		
		for($x=0;$x<count($array_valor);$x++)
		{
			$sql_texto .= "%" . $array_valor[$x] . "%";
		}
		
		$sql_filtro = " AND sub_grupo.sub_grupo LIKE '".$sql_texto."' ";
	}
	
	$sql = "SELECT * FROM ".DATABASE.".sub_grupo ";
	$sql .= "WHERE reg_del = 0 ";
	$sql .= $sql_filtro;
	$sql .= "ORDER BY sub_grupo.codigo_sub_grupo ";

	$xml = new XMLWriter();
	$xml->openMemory();
	$xml->startElement('rows');
	
	$db->select($sql,'MYSQL',
		function($reg, $i) use(&$xml)
		{
		    $xml->startElement('row');
		    $xml->writeAttribute('id', trim($reg["codigo_sub_grupo"]));
		    
		    $xml->writeElement('cell', trim($reg["codigo_sub_grupo"]));
		    $xml->writeElement('cell', trim($reg["sub_grupo"]));
		    
		    $img = "<span class=\'icone icone-excluir cursor\' onclick=if(confirm(\'Deseja excluir o item?\')){xajax_excluir(".$reg["id_sub_grupo"].");}></span>";
		    $xml->writeElement('cell', $img);
		    $xml->endElement();
		}
	);

	$xml->endElement();
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('sub_grupos', true, '520', '".$conteudo."');");
	
	return $resposta;
}

function insere($dados_form)
{
	$resposta = new xajaxResponse();

	$db = new banco_dados;
	
	if($dados_form["sub_grupo"]!='')
	{
		if (empty($dados_form["codigo"]))
		{
			$sql = "SELECT * FROM ".DATABASE.".sub_grupo WHERE reg_del = 0 ORDER BY codigo_sub_grupo DESC LIMIT 0, 1";
			$db->select($sql, 'MYSQL', true);
			$dados_form['codigo'] = sprintf('%02d', intval($db->array_select[0]['ultimo']) + 1);
		}
		
		
		
		$isql = "INSERT INTO ".DATABASE.".sub_grupo ";
		$isql .= "(codigo_sub_grupo, sub_grupo) VALUES ( ";
		$isql .= "'" . $dados_form["codigo"] . "', ";
		$isql .= "'" . maiusculas($dados_form["sub_grupo"]) . "') ";

		//Carrega os registros
		$db->insert($isql,'MYSQL');
			
		$resposta->addScript("xajax_atualizatabela('');");
		
		$resposta->addScript("xajax_voltar();");
	
		//Avisa o usuário do sucesso no cadastro das horas.		
		$resposta->addAlert("sub_grupo cadastrado com sucesso.");	

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
		
	$sql = "SELECT * FROM ".DATABASE.".sub_grupo ";
	$sql .= "WHERE sub_grupo.codigo_sub_grupo = '".$id."' ";
	$sql .= "AND reg_del = 0 ";
	
	$db->select($sql,'MYSQL', true);

	$regs = $db->array_select[0];
	
	$resposta->addAssign("id_sub_grupo", "value",$id);
	
	$resposta->addAssign("codigo", "value",$regs["codigo_sub_grupo"]);
	
	$resposta->addAssign("sub_grupo", "value",$regs["sub_grupo"]);
	
	$resposta->addAssign("btninserir", "value", "Atualizar");
	
	$resposta->addEvent("btninserir", "onclick", "xajax_atualizar(xajax.getFormValues('frm_sub_grupo'));");

	$resposta->addEvent("btnvoltar", "onclick", "xajax_voltar();");
		
	return $resposta;
}

function atualizar($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	if($dados_form["sub_grupo"]!='')
	{

		$usql = "UPDATE ".DATABASE.".sub_grupo SET ";
		$usql .= "codigo_sub_grupo = '" . $dados_form["codigo"] . "', ";
		$usql .= "sub_grupo = '" . maiusculas($dados_form["sub_grupo"]) . "' ";
		$usql .= "WHERE id_sub_grupo = '".$dados_form["id_sub_grupo"]."' ";
		$usql .= "AND reg_del = 0 ";

		$db->update($usql,'MYSQL');
		
		$resposta->addAlert("Subgrupo atualizado com sucesso.");
		
		//Chama rotina para atualizar a tabela via AJAX
		$resposta->addScript("xajax_atualizatabela('');");
	}
	else
	{
		$resposta->addAlert("Os campos devem estar preenchidos.");
	}	

	$resposta->addScript("xajax_voltar();");	

	return $resposta;
}

function excluir($id)
{
	$resposta = new xajaxResponse();

	$db = new banco_dados;
	
	/*
	$sql = "DELETE FROM ".DATABASE.".sub_grupo ";
	$sql .= "WHERE sub_grupo.id_sub_grupo = '".$id."' ";
	
	$db->delete($sql,'MYSQL');
	*/
	
	$usql = "UPDATE ".DATABASE.".sub_grupo SET ";
	$usql .= "reg_del = 1, ";
	$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
	$usql .= "data_del = '".date('Y-m-d')."' ";
	$usql .= "WHERE sub_grupo.id_sub_grupo = '".$id."' ";
	
	$db->update($usql,'MYSQL');
	
	$resposta->addScript("xajax_atualizatabela('');");
	
	$resposta->addAlert("Item excluido corretamente");

	return $resposta;
}

$xajax->registerFunction("voltar");
$xajax->registerFunction("insere");
$xajax->registerFunction("editar");
$xajax->registerFunction("atualizar");
$xajax->registerFunction("excluir");
$xajax->registerFunction("atualizatabela");
$xajax->registerFunction("getAtributos");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","xajax_atualizatabela('');");
?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script>
function grid(tabela, autoh, height, xml)
{
	function editar(id, row)
	{
		if (row < 2)
			xajax_editar(id);
	}
	
	
	mygrid = new dhtmlXGridObject(tabela);

	mygrid.attachEvent("onRowSelect",editar);
	mygrid.setImagePath("../includes/dhtmlx_403/codebase/imgs/");	
	mygrid.enableAutoHeight(autoh,height);
	mygrid.enableRowsHover(true,'cor_mouseover');

	mygrid.setHeader("Código, Subgrupo, D");
	mygrid.setInitWidths("80,*,50");
	mygrid.setColAlign("left,left,center");
	mygrid.setColTypes("ro,ro,ro");
	mygrid.setColSorting("str,str,str");

	mygrid.setSkin("dhx_skyblue");
    mygrid.enableMultiselect(true);
    mygrid.enableCollSpan(true);	
	mygrid.init();
	mygrid.loadXMLString(xml);
}
</script>
<?php
$conf = new configs();

$smarty->assign('larguraTotal', 1);

$smarty->assign("revisao_documento","V3");
$smarty->assign("campo",$conf->campos('subgrupos'));
$smarty->assign("botao",$conf->botoes());
$smarty->assign("classe",CSS_FILE);

$smarty->display('sub_grupo.tpl');
?>