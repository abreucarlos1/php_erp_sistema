<?php
/*
		Formulário de Regiões	
		
		Criado por Carlos Abreu  
		
		local/Nome do arquivo:
		../orcamento/regiao.php
		
		Versão 0 --> VERSÃO INICIAL - Carlos Abreu - 17/04/2017
		Versão 1 --> Inclusão dos campos reg_del nas consultas - 22/11/2017 - Carlos Abreu	
*/

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(594))
{
	nao_permitido();
}

function voltar()
{
	$resposta = new xajaxResponse();

	$resposta->addScriptCall("reset_campos('frm')");
	
	$resposta->addAssign("btn_atualizar", "value", "Inserir");
	
	$resposta->addEvent("btn_atualizar", "onclick", "xajax_inserir(xajax.getFormValues('frm'));");
	
	$resposta->addEvent("btnvoltar", "onclick", "history.back();");

	return $resposta;
}

function atualizatabela($dados_form)
{	
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;

	$xml = new XMLWriter();
	$xml->openMemory();
	$xml->startElement('rows');

	//seleciona os profissionais cadastrados
	$sql = "SELECT * FROM ".DATABASE.".regiao ";
	$sql .= "WHERE regiao.reg_del = 0 ";
	
	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}
	
	$array_regs = $db->array_select;

	foreach($array_regs as $regs)
	{
		//valor e data atual
		$sql = "SELECT * FROM ".DATABASE.".tabela_valor_mo ";
		$sql .= "WHERE tabela_valor_mo.id_regiao = '" . $regs["id_regiao"] . "' ";
		$sql .= "AND tabela_valor_mo.reg_del = 0 ";

		$db->select($sql,'MYSQL',true);

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
			
			return $resposta;
		}
		
		if($db->numero_registros>0)
		{
			$img = '&nbsp;';
		}
		else
		{
			$img = '<img style="cursor:pointer;" src="'.DIR_IMAGENS.'apagar.png" onclick=if(confirm("Deseja&nbsp;excluir&nbsp;os&nbsp;dados?")){xajax_excluir("'.$regs["id_regiao"].'")};>';
		}
		
		$xml->startElement('row');
			$xml->writeAttribute('id', $regs['id_regiao']);
			$xml->writeElement('cell', $regs["regiao"]);
			$xml->writeElement('cell', $img);
		$xml->endElement();
	}
	
	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('regioes', true, '400', '".$conteudo."');");

	return $resposta;
}

function inserir($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	if($dados_form["regiao"]!='')
	{	
		//verifica se a região já esta cadastrado
		$sql = "SELECT * FROM ".DATABASE.".regiao ";
		$sql .= "WHERE regiao.reg_del = 0 ";
		$sql .= "AND regiao.regiao = '".maiusculas(addslashes($dados_form["regiao"]))."' ";
	
		$db->select($sql,'MYSQL',true);
	
		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
			
			return $resposta;
		}
		
		//Insere um novo registro
		if($db->numero_registros<=0)
		{
			//Insere o tipo de indice
			$isql = "INSERT INTO ".DATABASE.".regiao(regiao) VALUES(";
			$isql .= "'" . maiusculas(addslashes($dados_form["regiao"])) . "') ";
	
			$db->insert($isql,'MYSQL');
	
			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
				
				return $resposta;
			}
			
			$resposta->addAlert("valor inserido com sucesso.");		
	
		}
		else
		{
			$resposta->addAlert("Região já cadastrada.");	
		}
		
		$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'))");
	}
	else
	{
		$resposta->addAlert("Os campos devem estar preenchidos.");	
	}

	return $resposta;
}

function editar($id_regiao)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;

	//seleciona a região
	$sql = "SELECT * FROM ".DATABASE.".regiao ";
	$sql .= "WHERE regiao.id_regiao = '".$id_regiao."' ";
	$sql .= "AND regiao.reg_del = 0 ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}

	$regs = $db->array_select[0];

	$resposta->addAssign("id_regiao","value", $regs["id_regiao"]);

	$resposta->addAssign("regiao","value",$regs["regiao"]);	
	
	$resposta->addAssign("btn_atualizar", "value", "Atualizar");
	
	$resposta->addEvent("btn_atualizar", "onclick", "xajax_atualizar(xajax.getFormValues('frm'));");
	
	$resposta->addEvent("btnvoltar", "onclick", "xajax_voltar();");

	return $resposta;
}

function atualizar($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	if($dados_form["regiao"]!='')
	{
		$usql = "UPDATE ".DATABASE.".regiao SET ";
		$usql .= "regiao = '".$dados_form["regiao"]."' ";
		$usql .= "WHERE id_regiao = '".$dados_form["id_regiao"]."' ";
		$usql .= "AND reg_del = 0 ";
		
		$db->update($usql,'MYSQL');

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
			
			return $resposta;
		}
		
		$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'))");
		
	}
	else
	{
		$resposta->addAlert("Os campos devem estar preenchidos.");		
	}
	
	return $resposta;
}

function excluir($id_regiao)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	$usql = "UPDATE ".DATABASE.".regiao SET ";
	$usql .= "reg_del = 1, ";
	$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
	$usql .= "data_del = '".date('Y-m-d')."' ";
	$usql .= "WHERE id_regiao = '".$id_regiao."' ";
	$usql .= "AND reg_del = 0 ";
	
	$db->update($usql,'MYSQL');

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}
	
	$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'))");
	
	return $resposta;
}
	
$xajax->registerFunction("voltar");
$xajax->registerFunction("inserir");
$xajax->registerFunction("editar");
$xajax->registerFunction("atualizar");
$xajax->registerFunction("atualizatabela");
$xajax->registerFunction("excluir");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","xajax_atualizatabela(xajax.getFormValues('frm'));");

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script language="javascript">

function grid(tabela, autoh, height, xml)
{
	mygrid = new dhtmlXGridObject(tabela);

	mygrid.enableAutoHeight(autoh,height);
	mygrid.enableRowsHover(true,'cor_mouseover');
	
	//retira o scroll horizontal
	mygrid.objBox.style.overflowX = "hidden";   
	mygrid.objBox.style.overflowY = "auto";

	function doOnRowSelected1(row,col)
	{
		if(col<=3)
		{						
			xajax_editar(row);

			return true;
		}
	}

	mygrid.setHeader("Região, E");
	mygrid.setInitWidths("*,30");
	mygrid.setColAlign("left,center");
	mygrid.setColTypes("ro,ro");
	mygrid.setColSorting("str,str");

	mygrid.attachEvent("onRowSelect",doOnRowSelected1);

	mygrid.setSkin("dhx_skyblue");
	mygrid.enableMultiselect(true);
	mygrid.enableCollSpan(true);
	mygrid.init();
	mygrid.loadXMLString(xml);
}


</script>

<?php

$conf = new configs();

$smarty->assign('campo', $conf->campos('regiao'));

$smarty->assign('revisao_documento', 'V1');

$smarty->assign("classe",CSS_FILE);

$smarty->display('regiao.tpl');

?>