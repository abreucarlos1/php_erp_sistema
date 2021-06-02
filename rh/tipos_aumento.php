<?php
/*
		Formulário de tipos aumento 	
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		../rh/tipos_autmento.php
		
		Versão 0 --> VERSÃO INICIAL - 28/01/2008
		Versão 1 --> Atualização Lay-out - 11/08/2008
		Versao 2 --> Atualização classe banco de dados - 23/01/2015 - Carlos Abreu
		Versão 3 --> Atualização layout - Carlos Abreu - 10/04/2017
		Versão 4 --> Inclusão dos campos reg_del nas consultas - 29/11/2017 - Carlos Abreu
*/	

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

function voltar()
{
	$resposta = new xajaxResponse();

	$resposta -> addScriptCall("reset_campos('frm_tipoaumento')");
	
	$resposta -> addAssign("btninserir", "value", "Inserir");
	
	$resposta -> addEvent("btninserir", "onclick", "xajax_insere(xajax.getFormValues('frm_tipoaumento'));");

	$resposta->addEvent("btnvoltar", "onclick", "history.back();");

	return $resposta;

}

function atualizatabela($filtro)
{
	$resposta = new xajaxResponse();
	
	$xml = new XMLWriter();

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
		
		$sql_filtro = "AND rh_tipos_aumento.tipo_aumento LIKE '".$sql_texto."' ";	
	}
	
	$sql = "SELECT * FROM ".DATABASE.".rh_tipos_aumento ";
	$sql .= "WHERE reg_del = 0 ";
	$sql .= $sql_filtro;

	$db->select($sql,'MYSQL',true);
	
	$conteudo = "";
	
	$xml->openMemory();
	$xml->setIndent(false);
	$xml->startElement('rows');
	
	foreach($db->array_select as $cont_desp)
	{
		$xml->startElement('row');
			$xml->writeAttribute('id',$cont_desp["id_tipo_aumento"]);
			
			$xml->startElement('cell');
				$xml->text($cont_desp["tipo_aumento"]);
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text('<img src="'.DIR_IMAGENS.'apagar.png" style="cursor:pointer;" onclick=if(apagar("'. trim($cont_desp["tipo_aumento"]).'")){xajax_excluir("'.$cont_desp["id_tipo_aumento"].'","'. $cont_desp["tipo_aumento"].'");}>');
			$xml->endElement();
			
		$xml->endElement();	
	
	}
	
    $resposta -> addAssign("btninserir", "value", "Inserir");

    $resposta -> addAssign("tipoaumento", "value", "");
	
	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('mostratipoaumento',true,'450','".$conteudo."');");
	
	return $resposta;

}

function insere($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
		
	if($dados_form["tipoaumento"]!='')
	{
		$sql = "SELECT id_tipo_aumento FROM ".DATABASE.".rh_tipos_aumento ";
		$sql .= "WHERE rh_tipos_aumento.tipo_aumento = '".maiusculas($dados_form["tipoaumento"])."' ";
		$sql .= "AND reg_del = 0 ";
		
		$db->select($sql,'MYSQL',true);

		if($db->numero_registros==0)
		{
			$isql = "INSERT INTO ".DATABASE.".rh_tipos_aumento ";
			$isql .= "(tipo_aumento) ";
			$isql .= "VALUES ('" . maiusculas($dados_form["tipoaumento"]) . "') ";

			$db->insert($isql,'MYSQL');			
			
			$resposta -> addScript("xajax_atualizatabela('');");
			
			$resposta -> addScript("xajax_voltar();");
		    
			if($db->numero_registros>0)
		    {
				$resposta->addAlert("Curso cadastrado com sucesso.");			
		    }			
		    else
			{
				$resposta->addAlert("Curso não cadastrado .");	
			}		
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
		
	$sql = "SELECT * FROM ".DATABASE.".rh_tipos_aumento ";
	$sql .= "WHERE rh_tipos_aumento.id_tipo_aumento = '".$id."' ";
	$sql .= "AND reg_del = 0 ";
	
	$db->select($sql,'MYSQL',true);

	$regs = $db->array_select[0];
	
	$resposta -> addAssign("id_tipo_aumento", "value",$id);
	
	$resposta -> addAssign("tipoaumento", "value",$regs["tipo_aumento"]);
	
	$resposta -> addAssign("btninserir", "value", "Atualizar");

	$resposta -> addEvent("btninserir", "onclick", "xajax_atualizar(xajax.getFormValues('frm_tipoaumento'));");
	
	$resposta -> addEvent("btnvoltar", "onclick", "xajax_voltar();");
	
	return $resposta;
}

function atualizar($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;

	if($dados_form["tipoaumento"]!='')
	{		
		$sql = "SELECT id_tipo_aumento FROM ".DATABASE.".rh_tipos_aumento ";
		$sql .= "WHERE rh_tipos_aumento.tipo_aumento = '".maiusculas($dados_form["tipoaumento"])."' ";
		$sql .= "AND reg_del = 0 ";
		
		$db->select($sql,'MYSQL',true);

		if($db->numero_registros==0)
		{
			$usql = "UPDATE ".DATABASE.".rh_tipos_aumento SET ";
			$usql .= "rh_tipos_aumento.tipo_aumento = '" . maiusculas($dados_form["tipoaumento"]) . "' ";
			$usql .= "WHERE rh_tipos_aumento.id_tipo_aumento = '".$dados_form["id_tipo_aumento"]."' ";
			$usql .= "AND reg_del = 0 ";

			$db->update($usql,'MYSQL');	
	
			$resposta -> addScript("xajax.voltar();");
			
			$resposta -> addScript("xajax_atualizatabela('');");			
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
	
	$usql = "UPDATE ".DATABASE.".rh_tipos_aumento SET ";
	$usql .= "reg_del = 1, ";
	$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
	$usql .= "data_del = '".date('Y-m-d')."' ";
	$usql .= "WHERE rh_tipos_aumento.id_tipo_aumento = '".$id."' ";

	$db->update($usql,'MYSQL');	

	$resposta->addScript("xajax_atualizatabela('');");
	
	$resposta -> addAlert($what . " excluido com sucesso.");
	
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
	
	function doOnRowSelected(id,ind) 
	{
		if(ind<=0)
		{
			xajax_editar(id);
			
			return true;
		}
		
		return false;
	}
	
	mygrid.enableAutoHeight(autoh,height);
	
	mygrid.attachEvent("onRowSelect", doOnRowSelected);
	
	mygrid.enableRowsHover(true,'cor_mouseover');

	mygrid.setHeader("Tipo reajuste,D",
		null,
		["text-align:left","text-align:center"]);
	mygrid.setInitWidths("*,30");
	mygrid.setColAlign("left,center");
	mygrid.setColTypes("ro,ro");
	mygrid.setColSorting("str,str");
	
	mygrid.setSkin("dhx_skyblue");
	mygrid.enableMultiselect(true);
	mygrid.enableCollSpan(true);		
	mygrid.init();
	mygrid.loadXMLString(xml);

}

</script>

<?php

$conf = new configs();

$smarty->assign("revisao_documento","V4");

$smarty->assign("nome_formulario","TIPOS DE REAJUSTES");

$smarty->assign("classe",CSS_FILE);

$smarty->display('tipos_aumento.tpl');

?>

