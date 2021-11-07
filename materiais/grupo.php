<?php
/*
	Formulário de Grupos de materiais
	
	Criado por Carlos Abreu
	
	local/Nome do arquivo:
	
	../materiais/grupo.php
	
	Versão 0 --> VERSÃO INICIAL - 15/12/2008
	Versao 1 --> Atualização classe banco de dados - 21/01/2015 - Carlos Abreu
	Versão 2 --> Inclusão dos campos reg_del nas consultas - 01/12/2017 - Carlos Abreu
*/
header('X-UA-Compatible: IE=edge');
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

function voltar()
{
	$resposta = new xajaxResponse();
	$resposta -> addScriptCall("reset_campos('frm_grupo')");
	$resposta -> addAssign("btninserir", "value", "Inserir");

	$resposta -> addEvent("btninserir", "onclick", "xajax_insere(xajax.getFormValues('frm_grupo'));");
	$resposta -> addEvent("btnvoltar", "onclick", "javascript:location.href='menumateriais.php';");
	return $resposta;

}

function atualizatabela()
{
    $resposta = new xajaxResponse();
    $db = new banco_dados();
    
    $xml = new XMLWriter();
    $xml->openMemory();
    $xml->setIndent(false);
    $xml->startElement('rows');
	
	$sql = "SELECT * FROM ".DATABASE.".grupo ";
	$sql .= "WHERE grupo.reg_del = 0 ";
	$sql .= "ORDER BY grupo.codigo_grupo ";

	$retorno = '';
	$reg = $db->select($sql,'MYSQL',
	    function($reg, $i) use(&$xml){
	        $xml->startElement('row');
	        $xml->writeAttribute('id', $reg['id_grupo']);
	        $xml->writeElement('cell', sprintf('%02d', $reg['codigo_grupo']));
	        $xml->writeElement('cell', $reg['grupo']);
	        
	        $xml->writeElement('cell', "<span class=\'icone icone-excluir cursor\' onclick=if(confirm(\'Deseja excluir este item?\')){xajax_excluir(".$reg['id_grupo'].");};></span>");
	        $xml->endElement();
	});
	
	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('grupos', true, '550', '".$conteudo."');");

	return $resposta;
}

function insere($dados_form)
{
	$resposta = new xajaxResponse();

	$db = new banco_dados;
	
	if($dados_form["grupo"]!='')
	{
		if (empty($dados_form["codigo"]))
		{
			$sql = "SELECT codigo_grupo ultimo FROM ".DATABASE.".grupo WHERE reg_del = 0 ORDER BY codigo_grupo DESC LIMIT 0, 1";
			$db->select($sql, 'MYSQL', true);
			$dados_form['codigo'] = sprintf('%02d', intval($db->array_select[0]['ultimo']) + 1);
		}
		
		$isql = "INSERT INTO ".DATABASE.".grupo ";
		$isql .= "(codigo_grupo,grupo) VALUES ( ";
		$isql .= "'" . $dados_form["codigo"] . "', ";
		$isql .= "'" . maiusculas($dados_form["grupo"]) . "') ";

		//Carrega os registros
		$db->insert($isql,'MYSQL');
			
		$resposta->addAlert("Grupo cadastrado com sucesso.");	
		$resposta->addScript("window.location='./grupo.php';");	
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
		
	$sql = "SELECT * FROM ".DATABASE.".grupo ";
	$sql .= "WHERE grupo.id_grupo = '".$id."' ";
	$sql .= "AND reg_del = 0 ";
	
	$registro = $db->select($sql,'MYSQL');

	$regs = mysqli_fetch_assoc($registro);
	
	$resposta->addAssign("id_grupo", "value",$id);
	$resposta->addAssign("codigo", "value",$regs["codigo_grupo"]);
	$resposta->addAssign("grupo", "value",$regs["grupo"]);
	$resposta->addAssign("btninserir", "value", "Atualizar");
	$resposta->addEvent("btninserir", "onclick", "xajax_atualizar(xajax.getFormValues('frm_grupo'));");
	$resposta->addEvent("btnvoltar", "onclick", "xajax_voltar();");
	
	return $resposta;
}

function atualizar($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	if($dados_form["grupo"]!='')
	{

		$isql = "UPDATE ".DATABASE.".grupo SET ";
		$isql .= "codigo_grupo = '" . $dados_form["codigo"] . "', ";
		$isql .= "grupo = '" . maiusculas($dados_form["grupo"]) . "' ";
		$isql .= "WHERE id_grupo = '".$dados_form["id_grupo"]."' ";

		//Carrega os registros
		$db->update($isql,'MYSQL');
		
		$resposta->addAlert("Grupo atualizado com sucesso.");
		
		$resposta->addScript("window.location='./grupo.php';");	
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
	$sql = "DELETE FROM ".DATABASE.".grupo ";
	$sql .= "WHERE grupo.id_grupo = '".$id."' ";
	
	$db->delete($sql,'MYSQL');
	*/
	$usql = "UPDATE ".DATABASE.".grupo SET ";
	$usql .= "reg_del = 1, ";
	$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
	$usql .= "data_del = '".date('Y-m-d')."' ";
	$usql .= "WHERE grupo.id_grupo = '".$id."' ";
	
	$db->update($usql,'MYSQL');
	
	$resposta->addAlert("Registro Excluido corretamente!");
	$resposta->addScript("window.location='./grupo.php';");

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

$smarty->assign("body_onload","xajax_atualizatabela('');");

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

	mygrid.setHeader("Código, Grupo,D");
	mygrid.setInitWidths("50,*,50");
	mygrid.setColAlign("left,left,center");
	mygrid.setColTypes("ro,ro,ro");
	mygrid.setColSorting("int,str,str");

	mygrid.attachEvent("onRowSelect",'xajax_editar');
	
	mygrid.setSkin("dhx_skyblue");
    mygrid.enableMultiselect(true);
    mygrid.enableCollSpan(true);	
	mygrid.init();
	mygrid.loadXMLString(xml);
}
</script>

<?php
$conf = new configs();

$sql = "SELECT * FROM ".DATABASE.".grupo ";
$sql .= "WHERE reg_del = 0 ";
$sql .= "ORDER BY grupo.codigo_grupo";

$retorno = '';
$db->select($sql,'MYSQL',
	function($reg, $i) use(&$retorno)
	{
		$virgula = $i > 0 ? ',' : '';
		$retorno .= $virgula."{codigo:'".$reg['codigo_grupo']."',";
		$retorno .= "id:'".$reg['id_grupo']."',";
		$retorno .= "grupo:'".$reg['grupo']."'}";
	}
);

$retorno = 'names=['.$retorno.']';

$smarty->assign('larguraTotal', 1);

$smarty->assign('registros', $retorno);
$smarty->assign("revisao_documento","V2");
$smarty->assign("campo",$conf->campos('grupo_materiais'));
$smarty->assign("botao",$conf->botoes());
$smarty->assign("classe",CSS_FILE);
$smarty->display('grupo.tpl');
?>