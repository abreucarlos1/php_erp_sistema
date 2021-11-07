<?php
/*
	Formulário de Clausulas PJ - RH	
	
	Criado por Carlos Abreu  
	
	local/Nome do arquivo:
	../rh/pj_clausulas.php

	Versão 0 --> VERSÃO INICIAL : 09/05/2013 - Carlos Abreu		
	Versão 1 --> Atualização layout - Carlos Abreu
	Versão 2 --> Inclusão dos campos reg_del nas consultas - 29/11/2017 - Carlos Abreu
	Versão 3 --> Layout responsivo - 05/02/2018 - Carlos
*/
	
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(301))
{
	nao_permitido();
}

function voltar($buscar)
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$botao = $conf->botoes($_COOKIE["idioma"],$resposta);

	$resposta -> addScriptCall("reset_campos('frm')");
	
	$resposta -> addAssign("btninserir", "value", $botao[1]);
	
	$resposta -> addAssign("busca", "value", $buscar);
	
	$resposta -> addEvent("btninserir", "onclick", "xajax_insere(xajax.getFormValues('frm'));");
	
	$resposta -> addEvent("btnvoltar", "onclick", "history.back();");

	return $resposta;

}

function atualizatabela($filtro)
{
	$resposta = new xajaxResponse();
	
	$xml = new XMLWriter();
	
	$conf = new configs();
	
	$campos = $conf->campos('pj_clausulas',$resposta);
	
	$msg = $conf->msg($resposta);

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
		
		$sql_filtro = "AND pj_tipo_contratacao.tipo_contratacao LIKE '".$sql_texto."' ";
	}
	
	$sql = "SELECT * FROM ".DATABASE.".pj_clausula ";
	$sql .= "JOIN ".DATABASE.".pj_tipo_contratacao ON id_tipo_contratacao = id_tipo_contrato AND pj_tipo_contratacao.reg_del = 0 ";
	$sql .= $sql_filtro;
	$sql .= "WHERE pj_clausula.reg_del = 0 ";
	$sql .= "ORDER BY numero ";

	$db->select($sql,'MYSQL',true);
	
	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}

	$conteudo = "";
	
	$xml->openMemory();
	$xml->setIndent(false);
	$xml->startElement('rows');
	
	foreach($db->array_select as $cont_desp)
	{
		$xml->startElement('row');
			$xml->writeAttribute('id',$cont_desp["id_clausula"]);
			
			$xml->startElement('cell');
				$xml->text($cont_desp["numero"]);
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text($cont_desp["tipo_contratacao"]);
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text($cont_desp["clausula"]);
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text('<img src="'.DIR_IMAGENS.'apagar.png" style="cursor:pointer;" onclick=if(apagar("'. trim($cont_desp["clausula"]).'")){xajax_excluir("'.$cont_desp["id_clausula"].'","'. trim($cont_desp["clausula"]).'");}>');
			$xml->endElement();
			
		$xml->endElement();	
	
	}
	
	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('div_grid',true,'450','".$conteudo."');");
	
	return $resposta;
}

function insere($dados_form)
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$msg = $conf->msg($resposta);
	
	$db = new banco_dados;
	
	if(!empty($dados_form["tipo_contrato"]) && $dados_form["numero_clausula"] != '' && !empty($dados_form['descricao_clausula']))
	{
		$isql = "INSERT INTO ".DATABASE.".pj_clausula ";
		$isql .= "(id_tipo_contrato, clausula, descricao_clausula, numero) ";
		$isql .= "VALUES ('" . $dados_form["tipo_contrato"] . "', ";
		$isql .= "'" . maiusculas($dados_form["clausula"]) . "', ";
		$isql .= "'" . $dados_form["descricao_clausula"] . "', ";
		$isql .= "'" . $dados_form["numero_clausula"] . "') ";

		$db->insert($isql,'MYSQL');

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}				
			
		$resposta->addScript("xajax_atualizatabela(document.getElementById('busca').value);");
		
		$resposta->addScript('xajax_voltar(document.getElementById("busca").value);');
	
		$resposta->addAlert($msg[1]);
	}
	else
	{
		$resposta->addAlert('Por favor, preencha todos os campos');
	}

	return $resposta;
}

function editar($id)
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$botao = $conf->botoes();

	$msg = $conf->msg($resposta);

	$db = new banco_dados;
	
	$sql = "SELECT * FROM ".DATABASE.".pj_clausula ";
	$sql .= "JOIN ".DATABASE.".pj_tipo_contratacao ON id_tipo_contratacao = id_tipo_contrato AND pj_tipo_contratacao.reg_del = 0 ";
	$sql .= "WHERE id_clausula = '".$id."' ";
	$sql .= "AND pj_clausula.reg_del = 0 ";

	$regs = $db->select($sql,'MYSQL', function($reg, $i){
		return $reg;
	});

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	
	$resposta->addAssign("id_clausula", "value",$id);
	
	$resposta->addScript("seleciona_combo(".$regs[0]["id_tipo_contrato"].",'tipo_contrato');");
	
	$resposta->addAssign("clausula", "value",$regs[0]["clausula"]);
	$resposta->addAssign("numero_clausula", "value",$regs[0]["numero"]);
	$resposta->addAssign("descricao_clausula", "value",$regs[0]["descricao_clausula"]);
	
	$resposta->addAssign("btninserir", "value", $botao[3]);
	
	$resposta->addEvent("btninserir", "onclick", "xajax_atualizar(xajax.getFormValues('frm'));");

	$resposta->addEvent("btnvoltar", "onclick", "xajax_voltar();");
	
	return $resposta;
}

function atualizar($dados_form)
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$msg = $conf->msg($resposta);
	
	$db = new banco_dados;
	
	if(!empty($dados_form["tipo_contrato"]) && $dados_form["numero_clausula"] != '' && !empty($dados_form['descricao_clausula']))
	{
		$usql = "UPDATE ".DATABASE.".pj_clausula SET ";
		$usql .= "id_tipo_contrato = '".$dados_form["tipo_contrato"]."', ";
		$usql .= "clausula = '".maiusculas($dados_form["clausula"])."', ";
		$usql .= "descricao_clausula = '".$dados_form["descricao_clausula"]."', ";
		$usql .= "numero = '".$dados_form["numero_clausula"]."' ";
		$usql .= "WHERE id_clausula = ".$dados_form['id_clausula']." ";
		$usql .= "AND reg_del = 0 ";

		$db->update($usql,'MYSQL');

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}				
			
		$resposta->addScript("xajax_atualizatabela(document.getElementById('busca').value);");
		
		$resposta->addScript('xajax_voltar(document.getElementById("busca").value);');
	
		$resposta->addAlert($msg[2]);
	}

	return $resposta;
}

function excluir($id, $what)
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$msg = $conf->msg($resposta);

	if($conf->checa_permissao(2,$resposta))
	{
		$db = new banco_dados;
		
		$usql = "UPDATE ".DATABASE.".pj_clausula SET ";
		$usql .= "reg_del = 1, ";
		$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
		$usql .= "data_del = '".date('Y-m-d')."' ";
		$usql .= "WHERE id_clausula = '".$id."' ";
	
		$db->update($usql,'MYSQL');
		
		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
		
		$resposta->addScript("xajax_atualizatabela('');");
		
		$resposta->addAlert($what . $msg[3]);
	}

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
		if(ind<=2)
		{
			xajax_editar(id);
			
			return true;
		}
		
		return false;
	}
	
	mygrid.enableAutoHeight(autoh,height);
	
	mygrid.attachEvent("onRowSelect", doOnRowSelected);
	
	mygrid.enableRowsHover(true,'cor_mouseover');

	mygrid.setHeader("Número Claúsula, Tipo contrato, Claúsula,D",
		null,
		["text-align:left","text-align:left","text-align:left","text-align:center"]);
	mygrid.setInitWidths("150,*,*,30");
	mygrid.setColAlign("left,left,left,center");
	mygrid.setColTypes("ro,ro,ro,ro");
	mygrid.setColSorting("str,str,str,str");
	
	mygrid.setSkin("dhx_skyblue");
	mygrid.enableMultiselect(true);
	mygrid.enableCollSpan(true);		
	mygrid.init();
	mygrid.loadXMLString(xml);
}

</script>

<?php
$conf = new configs();

$sql = "SELECT * FROM ".DATABASE.".pj_tipo_contratacao ";
$sql .= "WHERE reg_del = 0 ";
$sql .= "ORDER BY tipo_contratacao ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}
	
foreach ($db->array_select as $regs)
{
	$array_tipo_values[] = $regs["id_tipo_contratacao"];
	$array_tipo_output[] = $regs["tipo_contratacao"];
}

$smarty->assign("revisao_documento","V3");

$smarty->assign("option_tipo_values",$array_tipo_values);
$smarty->assign("option_tipo_output",$array_tipo_output);

$smarty->assign("campo",$conf->campos('pj_clausulas'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("classe",CSS_FILE);

$smarty->assign('larguraTotal', 1);

$smarty->display('pj_clausulas.tpl');
?>