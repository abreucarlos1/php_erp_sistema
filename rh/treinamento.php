<?php
/*
		Formulário de treinamento 	
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		../rh/treinamento.php
		
		Versão 0 --> VERSÃO INICIAL - 28/01/2008
		Versão 1 --> Atualização Lay-out - 11/08/2008
		Versão 2 --> Atualização banco de dados - 23/01/2015 - Carlos Abreu
		Versão 3 --> Atualização layout - Carlos Abreu - 10/04/2017
		Versão 4 --> novo campo (avaliar_eficacia) - Eduardo - 31/10/2017
		Versão 5 --> Inclusão dos campos reg_del nas consultas - 29/11/2017 - Carlos Abreu
*/	

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(226))
{
	nao_permitido();
}

function voltar()
{
	$resposta = new xajaxResponse();

	$resposta->addScriptCall("reset_campos('frm')");
	
	$resposta->addAssign("btninserir", "value", "Inserir");
	
	$resposta->addEvent("btninserir", "onclick", "xajax_insere(xajax.getFormValues('frm'));");

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
		
		$sql_filtro = "AND rh_treinamentos.treinamento LIKE '".$sql_texto."' ";
	}
	
	$sql = "SELECT * FROM ".DATABASE.".rh_treinamentos ";
	$sql .= "WHERE reg_del = 0 ";
	$sql .= $sql_filtro;

	$db->select($sql,'MYSQL',true);

	$conteudo = "";
	
	$xml->openMemory();
	$xml->setIndent(false);
	$xml->startElement('rows');
	
	foreach($db->array_select as $reg)
	{
		$xml->startElement('row');
			$xml->writeAttribute('id',$reg["id_rh_treinamento"]);
			
			$xml->startElement('cell');
				$xml->text($reg["treinamento"]);
			$xml->endElement();
			
			$xml->writeElement('cell', $reg['avaliar_eficacia'] == 1 ? 'SIM' : '-');
			$xml->writeElement('cell', $reg['vigencia']);
			
			$xml->startElement('cell');
				$xml->text('<span class="icone icone-excluir cursor" onclick=if(confirm("Deseja&nbsp;excluir&nbsp;este&nbsp;item?")){xajax_excluir("'.$reg["id_rh_treinamento"].'");}></span>');
			$xml->endElement();
			
		$xml->endElement();		
	}
	
	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('treinamentos',true,'450','".$conteudo."');");
	
	return $resposta;
}

function insere($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	if($dados_form["treinamento"]!='')
	{
		$sql = "SELECT id_rh_treinamento FROM ".DATABASE.".rh_treinamentos ";
		$sql .= "WHERE rh_treinamentos.treinamento = '".maiusculas($dados_form["treinamento"])."' ";
		$sql .= "AND reg_del = 0 ";
		
		$db->select($sql,'MYSQL',true);

		if($db->numero_registros==0)
		{
			$isql = "INSERT INTO ".DATABASE.".rh_treinamentos ";
			$isql .= "(treinamento, avaliar_eficacia, vigencia) ";
			$isql .= "VALUES ('".maiusculas($dados_form["treinamento"])."', '".$dados_form['avaliar_eficacia']."', '".$dados_form['vigencia']."') ";
	
			$db->insert($isql,'MYSQL');
			
			$resposta->addScript("xajax_atualizatabela('');");
			
			$resposta->addScript("xajax_voltar();");
		
			$resposta->addAlert("Curso cadastrado com sucesso.");			
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
		
	$sql = "SELECT * FROM ".DATABASE.".rh_treinamentos ";
	$sql .= "WHERE id_rh_treinamento = '".$id."' ";
	$sql .= "AND reg_del = 0 ";
	
	$db->select($sql,'MYSQL',true);

	$reg = $db->array_select[0];
	
	if ($reg["avaliar_eficacia"] == 1)
	    $resposta->addScript("frm.avaliar_eficacia[0].checked=true");
    else
        $resposta->addScript("frm.avaliar_eficacia[1].checked=true");
	
	$resposta->addAssign("id_treinamento", "value",$id);
	$resposta->addAssign("treinamento", "value",$reg["treinamento"]);
	$resposta->addAssign("vigencia", "value",$reg["vigencia"]);
	$resposta->addAssign("btninserir", "value", "Atualizar");
	$resposta->addEvent("btninserir", "onclick", "xajax_atualizar(xajax.getFormValues('frm'));");
	$resposta->addEvent("btnvoltar", "onclick", "xajax_voltar();");
	
	return $resposta;
}

function atualizar($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;

	if($dados_form["treinamento"]!='')
	{		
		$sql = "SELECT id_rh_treinamento FROM ".DATABASE.".rh_treinamentos ";
		$sql .= "WHERE rh_treinamentos.treinamento = '".maiusculas($dados_form["treinamento"])."' ";
		$sql .= "AND id_rh_treinamento <> '".$dados_form["id_treinamento"]."' ";
		$sql .= "AND reg_del = 0 ";
		
		$db->select($sql,'MYSQL',true);

		if($db->numero_registros==0)
		{
			$usql = "UPDATE ".DATABASE.".rh_treinamentos SET ";
			$usql .= "treinamento = '" . maiusculas($dados_form["treinamento"]) . "', ";
			$usql .= "avaliar_eficacia = '" . $dados_form["avaliar_eficacia"] . "', ";
			$usql .= "vigencia = '" . $dados_form["vigencia"] . "' ";
			$usql .= "WHERE id_rh_treinamento = '".$dados_form["id_treinamento"]."' ";
			$usql .= "AND reg_del = 0 ";
	
			$db->update($usql,'MYSQL');
	
			$resposta->addScript("xajax.voltar();");
			
			$resposta->addScript("xajax_atualizatabela('');");
			
			$resposta->addAlert("Curso atualizado com sucesso.");

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

function excluir($id, $what = '')
{
	$resposta = new xajaxResponse();

	$db = new banco_dados;
	
	$usql = "UPDATE ".DATABASE.".rh_treinamentos SET ";
	$usql .= "reg_del = 1, ";
	$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
	$usql .= "data_del = '".date('Y-m-d')."' ";
	$usql .= "WHERE rh_treinamentos.id_rh_treinamento = '".$id."' ";

	$db->update($usql,'MYSQL');	

	$resposta->addScript("xajax_atualizatabela('');");
	
	$resposta->addAlert("Item excluido com sucesso.");
	
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

<script language="javascript">

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

	mygrid.setHeader("Descrição Treinamento, Avaliar Eficácia, Vigência (Meses),D",
		null,
		["text-align:left","text-align:left","text-align:left","text-align:center"]);
	mygrid.setInitWidths("*,120,130,30");
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

$smarty->assign('campo', $conf->campos('treinamentos'));
$smarty->assign('botoes', $conf->botoes());
$smarty->assign("revisao_documento","V5");

$smarty->assign("nome_formulario","CURSOS PARA TREINAMENTO");

$smarty->assign("classe",CSS_FILE);

$smarty->display('treinamento.tpl');

?>