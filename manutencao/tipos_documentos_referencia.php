<?php
/*
		Formulário de Tipos Documentos referencia	
		
		Criado por Carlos Abreu  
		
		local/Nome do arquivo:
		../manutencao/tipos_documentos_referencia.php
		
		Versão 0 --> VERSÃO INICIAL : 16/03/2012
		Versão 1 --> atualização classe banco de dados - 21/01/2015 - Carlos Abreu
		Versão 2 --> Atualização de layout - 28/05/2015 - Carlos
		Versão 3 --> atualização layout - Carlos Abreu - 30/03/2017
		Versão 4 --> Inclusão dos campos reg_del nas consultas - 22/11/2017 - Carlos Abreu	
*/
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(204))
{
	nao_permitido();
}

function voltar()
{
	$resposta = new xajaxResponse();
	
	$resposta->addScript("xajax.$('frm').reset(); ");
	
	$resposta->addAssign("btninserir","value","Inserir");
	
	$resposta->addEvent("btninserir","onclick","xajax_insere(xajax.getFormValues('frm')); ");
	
	$resposta->addScript("xajax_atualizatabela(''); ");	
	
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
		$array_valor = explode(" ",$filtro);
		
		for($x=0;$x<count($array_valor);$x++)
		{
			$sql_texto .= "%" . $array_valor[$x] . "%";
		}
		
		$sql_filtro = " AND tipos_documentos_referencia.tipo_documento LIKE '".$sql_texto."' ";
		$sql_filtro .= " OR tipos_documentos_referencia.abreviacao LIKE '".$sql_texto."' ";
		$sql_filtro .= " OR setores.setor LIKE '".$sql_texto."' ";
		$sql_filtro .= " OR setores.abreviacao LIKE '".$sql_texto."' ";
	}
	
	$sql = "SELECT * FROM ".DATABASE.".tipos_documentos_referencia, ".DATABASE.".tipos_referencia, ".DATABASE.".setores ";
	$sql .= "WHERE tipos_documentos_referencia.id_disciplina = setores.id_setor ";
	$sql .= "AND tipos_documentos_referencia.reg_del = 0 ";
	$sql .= "AND tipos_referencia.reg_del = 0 ";
	$sql .= "AND setores.reg_del = 0 ";
	$sql .= "AND tipos_documentos_referencia.id_tipo_referencia = tipos_referencia.id_tipo_referencia ";
	$sql .= $sql_filtro;
	$sql .= "ORDER BY tipos_documentos_referencia.tipo_documento ";
	
	$db->select($sql,'MYSQL',true);

	$conteudo = "";	
	
	$xml = new XMLWriter();
	$xml->openMemory();
	$xml->startElement('rows');
	
	foreach($db->array_select as $cont_desp)
	{
		$xml->startElement('row');
			$xml->writeAttribute('id', $cont_desp['id_tipos_documentos_referencia']);
			$xml->writeElement('cell', $cont_desp["setor"]);
			$xml->writeElement('cell', $cont_desp["tipo_referencia"]);
			$xml->writeElement('cell', $cont_desp["tipo_documento"]);
			$xml->writeElement('cell', $cont_desp["abreviacao"]);
		
			$img = "<img src=\'".DIR_IMAGENS."apagar.png\' style=\'cursor:pointer;\' onclick=if(confirm(\'Confirma a exclusão do tipo selecionado?\')){xajax_excluir(\'".$cont_desp["id_tipos_documentos_referencia"]."\');}>";
			$xml->writeElement('cell', $img);
		$xml->endElement();
	}
	
	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('setores',true,'400','".$conteudo."');");

	return $resposta;
}

function insere($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	if($dados_form["id_disciplina"]!='' || $dados_form["abreviacao"]!='' || $dados_form["tipo_doc"]!='')
	{

		$isql = "INSERT INTO ".DATABASE.".tipos_documentos_referencia ";
		$isql .= "(id_disciplina, id_tipo_referencia, tipo_documento, abreviacao) ";
		$isql .= "VALUES ('" . $dados_form["id_disciplina"] . "', ";
		$isql .= "'" . $dados_form["id_tipo_ref"] . "', ";
		$isql .= "'" . maiusculas($dados_form["tipo_doc"]) . "', ";
		$isql .= "'" . maiusculas($dados_form["abreviacao"]) . "') ";

		$db->insert($isql,'MYSQL');
			
		$resposta->addScript("xajax_voltar();");		

		$resposta->addScript("xajax_atualizatabela('');");

		$resposta->addAlert("tipo documento cadastrado com sucesso.");	

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
		
	$sql = "SELECT * FROM ".DATABASE.".tipos_documentos_referencia  ";
	$sql .= "WHERE tipos_documentos_referencia.id_tipos_documentos_referencia = '".$id."' ";
	$sql .= "AND tipos_documentos_referencia.reg_del = 0 ";
	
	$db->select($sql,'MYSQL',true);

	$regs = $db->array_select[0];
	
	$resposta -> addScript("seleciona_combo('" . $regs["id_disciplina"] . "', 'id_disciplina');");
	
	$resposta -> addScript("seleciona_combo('" . $regs["id_tipo_referencia"] . "', 'id_tipo_ref');");
	
	$resposta -> addAssign("abreviacao", "value",$regs["abreviacao"]);
	
	$resposta -> addAssign("tipo_doc", "value",$regs["tipo_documento"]);
	
	$resposta -> addAssign("id_tipo", "value", $regs["id_tipos_documentos_referencia"]);
	
	$resposta -> addAssign("btninserir", "value", "Atualizar");
	
	$resposta -> addEvent("btninserir", "onclick", "xajax_atualizar(xajax.getFormValues('frm'));");

	$resposta->addEvent("btnvoltar", "onclick", "xajax_voltar();");
	
	return $resposta;	
}

function atualizar($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	if($dados_form["id_disciplina"]!='' || $dados_form["abreviacao"]!='' || $dados_form["tipo_doc"]!='' || $dados_form["pasta_base"]!='')
	{
		$usql = "UPDATE ".DATABASE.".tipos_documentos_referencia SET ";
		$usql .= "id_disciplina = '" . $dados_form["id_disciplina"] . "', ";
		$usql .= "id_tipo_referencia = '" . $dados_form["id_tipo_ref"] . "', ";
		$usql .= "abreviacao = '" . maiusculas($dados_form["abreviacao"]) . "', ";
		$usql .= "tipo_documento = '" . maiusculas($dados_form["tipo_doc"]) . "' ";
		$usql .= "WHERE id_tipos_documentos_referencia = '".$dados_form["id_tipo"]."' ";
		$usql .= "AND reg_del = 0 ";

		$db->update($usql,'MYSQL');
		
		$resposta->addScript("xajax_voltar();");
		
		$resposta->addScript("xajax_atualizatabela('');");
	
		$resposta->addAlert("tipo documento atualizado com sucesso.");	

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
	
	$usql = "UPDATE ".DATABASE.".tipos_documentos_referencia SET ";
	$usql .= "reg_del = 1, ";
	$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
	$usql .= "data_del = '".date('Y-m-d')."' ";
	$usql .= "WHERE id_tipos_documentos_referencia = '".$id."' ";
	$usql .= "AND reg_del = 0 ";
	
	$db->update($usql,'MYSQL');

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}
	
	$resposta->addAlert("Tipo de referência excluído com sucesso!");
	
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

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","xajax_atualizatabela('');");

$conf = new configs();

$sql = "SELECT * FROM ".DATABASE.".setores ";
$sql .= "WHERE id_setor NOT IN (6,2,1,15,17,3,21,24,16,23,11,18,19,31,28) ";
$sql .= "AND setores.reg_del = 0 ";
$sql .= "ORDER BY setor ";

$db->select($sql,'MYSQL',true);

foreach ($db->array_select as $cont)
{
	$array_setor_values[] = $cont["id_setor"];
	$array_setor_output[] = $cont["setor"];
}

$sql = "SELECT * FROM ".DATABASE.".tipos_referencia ";
$sql .= "WHERE tipos_referencia.reg_del = 0 ";

$db->select($sql,'MYSQL',true);

foreach ($db->array_select as $cont)
{
	$array_tipo_values[] = $cont["id_tipo_referencia"];
	$array_tipo_output[] = $cont["tipo_referencia"];
}

$smarty->assign("option_setor_values",$array_setor_values);
$smarty->assign("option_setor_output",$array_setor_output);

$smarty->assign("option_tipo_values",$array_tipo_values);
$smarty->assign("option_tipo_output",$array_tipo_output);

$smarty->assign("revisao_documento","V4");

$smarty->assign('campo', $conf->campos('tipos_documentos_referencias'));

$smarty->assign("classe",CSS_FILE);

$smarty->display('tipos_documentos_referencia.tpl');

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script>


function grid(tabela, autoh, height, xml)
{	
	mygrid = new dhtmlXGridObject(tabela);

	mygrid.enableAutoHeight(autoh,height);
	mygrid.enableRowsHover(true,'cor_mouseover');

	function doOnRowSelected(row,col)
	{
		if(col<=3)
		{						
			xajax_editar(row);

			return true;
		}
	}

	mygrid.setHeader("Disciplina, Tipo Referência, Documento, Abreviação, D");
	mygrid.setInitWidths("*,*,*,100,50");
	mygrid.setColAlign("left,left,left,left,center");
	mygrid.setColTypes("ro,ro,ro,ro,ro");
	mygrid.setColSorting("str,str,str,str,str");

	mygrid.attachEvent('onRowSelect', doOnRowSelected);

	mygrid.setSkin("dhx_skyblue");
    mygrid.enableMultiselect(true);
    mygrid.enableCollSpan(true);	
	mygrid.init();
	mygrid.loadXMLString(xml);
}
</script>