<?php
/*
		Formulário de Conhecimento 	
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		../rh/conhecimento.php
		
		Versão 0 --> VERSÃO INICIAL - 28/01/2008
		Versão 1 --> Atualização Lay-out - 11/08/2008
		Versão 2 --> Atualização classe banco de dados - 23/01/2015 - Carlos Abreu
		Versão 3 --> Atualização Layout - 01/04/2015 - Eduardo
		Versão 4 --> Atualização layout - Carlos Abreu - 05/04/2017
		Versão 5 --> Inclusão dos campos reg_del nas consultas - 28/11/2017 - Carlos Abreu
*/	

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(87))
{
	nao_permitido();
}

function voltar()
{
	$resposta = new xajaxResponse();

	$resposta -> addScriptCall("reset_campos('frm_conhecimento')");
	
	$resposta -> addAssign("btninserir", "value", "Inserir");
	
	$resposta -> addEvent("btninserir", "onclick", "xajax_insere(xajax.getFormValues('frm_conhecimento'));");

	$resposta->addEvent("btnvoltar", "onclick", "history.back();");

	return $resposta;

}

function atualizatabela($filtro)
{
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
		
		$sql_filtro = "AND rh_conhecimentos.conhecimento LIKE '".$sql_texto."' ";
	}
	
	$sql = "SELECT * FROM ".DATABASE.".rh_conhecimentos ";
	$sql .= "WHERE rh_conhecimentos.reg_del = 0 ";
	$sql .= $sql_filtro;

	$db->select($sql,'MYSQL',true);

	$conteudo = "";
	
	$xml = new XMLWriter();
	$xml->openMemory();
	$xml->startElement('rows');
	
	$chars = array("'","\"",")","(","\\","/");
	
	foreach($db->array_select as $cont_desp)
	{
		$xml->startElement('row');
		$xml->writeAttribute('id', $cont_desp['id_rh_conhecimento']);
		$xml->writeElement('cell', $cont_desp['conhecimento']);
		$xml->writeElement('cell', '<img src="'.DIR_IMAGENS.'apagar.png" style="cursor:pointer;" onclick=if(confirm("Deseja&nbsp;excluir&nbsp;o&nbsp;conhecimento?")){xajax_excluir("'.$cont_desp["id_rh_conhecimento"].'");}; >');
		$xml->endElement();
	}

	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('conhecimentos', true, '400', '".$conteudo."');");
	
	return $resposta;
}

function insere($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
		
	if($dados_form["conhecimento"]!='')
	{
		$sql = "SELECT id_rh_conhecimento FROM ".DATABASE.".rh_conhecimentos ";
		$sql .= "WHERE rh_conhecimentos.conhecimento = '".maiusculas($dados_form["conhecimento"])."' ";
		$sql .= "AND reg_del = 0 ";
		
		$db->select($sql,'MYSQL',true);

		if($db->numero_registros==0)
		{
			$isql = "INSERT INTO ".DATABASE.".rh_conhecimentos ";
			$isql .= "(conhecimento) ";
			$isql .= "VALUES ('" . maiusculas($dados_form["conhecimento"]) . "') ";

			$db->insert($isql,'MYSQL');
			
			$resposta -> addScript("xajax_atualizatabela('');");
			
			$resposta -> addScript("xajax_voltar();");
		
			$resposta->addAlert("Conhecimento cadastrado com sucesso.");			
		}
		else
		{
			$resposta->addAlert("Registro já existente no banco de dados.");	
		}		
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

	$sql = "SELECT * FROM ".DATABASE.".rh_conhecimentos ";
	$sql .= "WHERE rh_conhecimentos.id_rh_conhecimento = '".$id."' ";
	$sql .= "AND reg_del = 0 ";
	
	$db->select($sql,'MYSQL',true);

	$regs = $db->array_select[0];
	
	$resposta -> addAssign("id_conhecimento", "value",$id);
	
	$resposta -> addAssign("conhecimento", "value",$regs["conhecimento"]);
	
	$resposta -> addAssign("btninserir", "value", "Atualizar");

	$resposta -> addEvent("btninserir", "onclick", "xajax_atualizar(xajax.getFormValues('frm_conhecimento'));");
	
	$resposta -> addEvent("btnvoltar", "onclick", "xajax_voltar();");
	
	return $resposta;
}

function atualizar($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;

	if($dados_form["conhecimento"]!='')
	{		
		$sql = "SELECT id_rh_conhecimento FROM ".DATABASE.".rh_conhecimentos ";
		$sql .= "WHERE rh_conhecimentos.conhecimento = '".maiusculas($dados_form["conhecimento"])."' ";
		$sql .= "AND reg_del = 0 ";
		
		$db->select($sql,'MYSQL',true);

		if($db->numero_registros==0)
		{
			$usql = "UPDATE ".DATABASE.".rh_conhecimentos SET ";
			$usql .= "rh_conhecimento.conhecimento = '" . maiusculas($dados_form["conhecimento"]) . "' ";
			$usql .= "WHERE rh_conhecimentos.id_rh_conhecimento = '".$dados_form["id_conhecimento"]."' ";
	
			$db->update($usql,'MYSQL');
	
			$resposta -> addScript("xajax.voltar();");
			
			$resposta -> addScript("xajax_atualizatabela('');");
			
			$resposta->addAlert("Conhecimento atualizado com sucesso.");

		}
		else
		{
			$resposta->addAlert("Registro já existente no banco de dados.");
		}
		
	}
	else
	{
		$resposta->addAlert("Os campos devem estar preenchidos.");
	}	
	
	return $resposta;
}

function excluir($id, $what)
{
	$resposta = new xajaxResponse();

	$db = new banco_dados;
	
	$usql = "UPDATE ".DATABASE.".rh_conhecimentos SET ";
	$usql .= "reg_del = 1, ";
	$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
	$usql .= "data_del = '".date('Y-m-d')."' ";
	$usql .= "WHERE rh_conhecimentos.id_rh_conhecimento = '".$id."' ";
	
	$db->update($usql,'MYSQL');

	$resposta->addScript("xajax_atualizatabela('');");
	
	$resposta->addAlert($what . " excluido com sucesso.");

	return $resposta;
}

$conf = new configs();

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

<script language="javascript">
	
function grid(tabela, autoh, height, xml)
{
	mygrid = new dhtmlXGridObject(tabela);

	mygrid.enableAutoHeight(autoh,height);
	mygrid.enableRowsHover(true,'cor_mouseover');

	mygrid.setHeader("Conhecimento, D");
	mygrid.setInitWidths("*,50");
	mygrid.setColAlign("left,center");
	mygrid.setColTypes("ro,ro");
	mygrid.setColSorting("str,str");

	function editar(id, col)
	{
		if (col == 0)
		{
			xajax_editar(id);
		}
	}
	
	mygrid.attachEvent("onRowSelect",editar);

	mygrid.setSkin("dhx_skyblue");
	mygrid.enableMultiselect(true);
	mygrid.enableCollSpan(true);
	mygrid.init();
	mygrid.loadXMLString(xml);
}
</script>

<?php
$smarty->assign('campo', $conf->campos('conhecimento'));

$smarty->assign('revisao_documento', 'V5');

$smarty->assign("classe",CSS_FILE);

$smarty->display('conhecimento.tpl');
?>