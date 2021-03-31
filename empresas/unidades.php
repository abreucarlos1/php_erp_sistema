<?php
/*

		Formulário de Unidades	
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		../empresas/unidades.php
		
		Versão 0 --> VERSÃO INICIAL : 20/03/2007
		Versão 1 --> Atualização Lay-out / Smarty : 25/06/2008
		Versão 2 --> Atualização Layout: 23/12/2014
		Versão 3 --> atualização layout - Carlos Abreu - 27/03/2017
		Versão 4 --> Inclusão dos campos reg_del nas consultas - 20/11/2017 - Carlos Abreu
*/
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(120))
{
	nao_permitido();
}

function voltar()
{
	$resposta = new xajaxResponse();
	
	$resposta->addScript("xajax.$('frm_unidades').reset(); ");
	
	$resposta->addAssign("btninserir","value","Inserir");
	
	$resposta->addEvent("btninserir","onclick","xajax_insere(xajax.getFormValues('frm_unidades')); ");
	
	$resposta->addScript("xajax_atualizatabela(''); ");	
	
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
		
		$sql_filtro = " AND unidades.unidade LIKE '".$sql_texto."' ";
		$sql_filtro .= " OR unidades.descricao LIKE '".$sql_texto."' ";

	}
	
	$sql = "SELECT * FROM ".DATABASE.".unidades ";
	$sql .= "WHERE unidades.reg_del = 0 ";
	$sql .= $sql_filtro;
	$sql .= "ORDER BY descricao ";
	
	$db->select($sql,'MYSQL',true);
	
	if ($db->erro != '')
	{
		$resposta->addAlert("Não foi possível a seleção dos dados".$db->erro);
	}

	$xml = new XMLWriter();
	$xml->openMemory();
	$xml->startElement('rows');
	
	$conteudo = "";

	foreach($db->array_select as $cont_desp)
	{
		$xml->startElement('row');
			$xml->writeAttribute('id', $cont_desp['id_unidade']);
			$xml->writeElement('cell', $cont_desp["unidade"]);
			$xml->writeElement('cell', $cont_desp["descricao"]);
			$html = '<img src="'.DIR_IMAGENS.'apagar.png" style="cursor:pointer;" onclick=if(confirm("Confirma?")){xajax_excluir("'.$cont_desp["id_unidade"].'");} />';
			$xml->writeElement('cell', $html);
		$xml->endElement();
	}
	
	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('unidades', true, '500', '".$conteudo."');");
	
	return $resposta;
}

function insere($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	if($dados_form["abreviacao"]!='' || $dados_form["unidade"]!='')
	{
		$isql = "INSERT INTO ".DATABASE.".unidades ";
		$isql .= "(unidade, descricao) ";
		$isql .= "VALUES ('" . maiusculas($dados_form["abreviacao"]) . "', ";
		$isql .= "'" . maiusculas($dados_form["unidade"]) . "') ";

		$db->insert($isql,'MYSQL');
		
		if ($db->erro != '')
		{
			$resposta->addAlert("Não foi possível a inserção dos dados".$isql);
		}

		$resposta->addScript("xajax_voltar();");
				
		$resposta->addScript("xajax_atualizatabela('');");
		
		$resposta->addAlert("unidade cadastrada com sucesso.");	
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
		
	$sql = "SELECT * FROM ".DATABASE.".unidades  ";
	$sql .= "WHERE unidades.id_unidade = '".$id."' ";
	$sql .= "AND unidades.reg_del = 0 ";
	
	$db->select($sql,'MYSQL',true);
	
	if ($db->erro != '')
	{
		$resposta->addAlert("Não foi possível fazer a seleção." . $db->erro);
	}

	$regs = $db->array_select[0];
	
	$resposta->addAssign("abreviacao", "value",$regs["unidade"]);
	
	$resposta->addAssign("unidade", "value",$regs["descricao"]);
	
	$resposta->addAssign("id_unidade", "value",$regs["id_unidade"]);
	
	$resposta->addAssign("btninserir", "value", "Atualizar");
	
	$resposta->addEvent("btninserir", "onclick", "xajax_atualizar(xajax.getFormValues('frm_unidades'));");

	$resposta->addEvent("btnvoltar", "onclick", "xajax_voltar();");
	
	return $resposta;	
}

function atualizar($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	if($dados_form["abreviacao"]!='' || $dados_form["unidade"]!='')
	{
		$usql = "UPDATE ".DATABASE.".unidades SET ";
		$usql .= "unidade = '" . maiusculas($dados_form["abreviacao"]) . "', ";
		$usql .= "descricao = '" . maiusculas($dados_form["unidade"]) . "' ";
		$usql .= "WHERE id_unidade = '".$dados_form["id_unidade"]."' ";
		$usql .= "AND reg_del = 0 ";

		$db->update($usql,'MYSQL');
		
		if ($db->erro != '')
		{
			$resposta->addAlert("Não foi possível a inserção dos dados ".$db->erro);
		}
		
		$resposta->addScript("xajax_voltar();");
		
		$resposta->addScript("xajax_atualizatabela('');");
	
		$resposta->addAlert("unidade atualizada com sucesso.");	
	}
	else
	{
		$resposta->addAlert("Os campos devem estar preenchidos.");
	}	

	return $resposta;
}

function excluir($id)
{
	$resposta = new xajaxResponse();
			
	$db = new banco_dados;
	
	$sql = "SELECT * FROM ".DATABASE.".empresas ";
	$sql .= "WHERE id_unidade = '".$id."' ";
	$sql .= "AND empresas.reg_del = 0 ";
	
	$db->select($sql,'MYSQL',true);
	
	if ($db->erro != '')
	{
		$resposta->addAlert($sql);
	}
	
	if($db->numero_registros > 0)
	{
		$resposta->addAlert("unidade atrelada a uma ou mais empresas, não pode ser excluida!");
	}
	else
	{	

		$usql = "UPDATE ".DATABASE.".unidades SET ";
		$usql .= "reg_del = 1, ";
		$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
		$usql .= "data_del = '".date('Y-m-d')."' ";
		$usql .= "WHERE id_unidade = '".$id."' ";
		$usql .= "AND reg_del = 0 ";
		
		$db->update($usql,'MYSQL');
		
		if ($db->erro != '')
		{
			$resposta->addAlert($dsql);
		}
		
		$resposta->addAlert("unidade excluÍda com sucesso!");
		
		$resposta->addScript("xajax_atualizatabela(''); ");
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

<script language="javascript">

function grid(tabela, autoh, height, xml)
{
	mygrid = new dhtmlXGridObject(tabela);

	mygrid.enableAutoHeight(autoh,height);
	
	mygrid.enableRowsHover(true,'cor_mouseover');

	mygrid.setHeader("Abreviação,Unidade,D");
	mygrid.setInitWidths("*,*,50");
	mygrid.setColAlign("left,left,center");
	mygrid.setColTypes("ro,ro,ro");
	mygrid.setColSorting("str,str,str");

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

$smarty->assign('revisao_documento', 'V3');

$smarty->assign('campo', $conf->campos('unidades'));

$smarty->assign("botao", $conf->botoes());

$smarty->assign("classe",CSS_FILE);

$smarty->display('unidades.tpl');
?>