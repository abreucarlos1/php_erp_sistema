<?php
/*
		Formulário de CONTROLE - SGI RH	
		
		Criado por Carlos Abreu  
		
		local/Nome do arquivo:
		../rh/sgi_controle.php
	
		Versão 0 --> VERSÃO INICIAL : 12/12/2012 - Carlos Abreu
		Versão 1 --> Atualização interface - 07/07/2016 - Carlos Abreu
		Versão 2 --> Atualização layout - Carlos Abreu - 10/04/2017
		Versão 3 --> Inclusão dos campos reg_del nas consultas - 28/11/2017 - Carlos Abreu
*/

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(259))
{
	nao_permitido();
}

function voltar()
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$botao = $conf->botoes($_COOKIE["idioma"],$resposta);

	$resposta -> addScriptCall("reset_campos('frm')");
	
	$resposta -> addAssign("data_realizacao", "value", date('d/m/Y'));
	
	$resposta -> addAssign("vigencia", "value", "12");
		
	$resposta -> addAssign("btninserir", "value", $botao[1]);
	
	$resposta -> addEvent("btninserir", "onclick", "xajax_insere(xajax.getFormValues('frm'));");
	
	$resposta -> addEvent("btnvoltar", "onclick", "history.back();");

	return $resposta;

}

function atualizatabela($filtro)
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$xml = new XMLWriter();
	
	$campos = $conf->campos('sgi_controle',$resposta);
	
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
		
		$sql_filtro = "AND (sgi_item.sgi_item LIKE '".$sql_texto."' ";
		$sql_filtro .= "OR sgi_requisito.sgi_requisito LIKE '".$sql_texto."') ";
	}
	
	$sql = "SELECT * FROM ".DATABASE.".sgi_controle, ".DATABASE.".sgi_requisito, ".DATABASE.".sgi_item ";
	$sql .= "WHERE sgi_controle.reg_del = 0 ";
	$sql .= "AND sgi_requisito.reg_del = 0 ";
	$sql .= "AND sgi_item.reg_del = 0 ";
	$sql .= "AND sgi_controle.id_sgi_item = sgi_item.id_sgi_item ";
	$sql .= "AND sgi_controle.id_sgi_requisito = sgi_requisito.id_sgi_requisito ";
	$sql .= $sql_filtro;
	$sql .= "ORDER BY sgi_controle.data_vencimento ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	
	$xml->openMemory();
	$xml->setIndent(false);
	$xml->startElement('rows');
	
	foreach($db->array_select as $cont_desp)
	{
		if($cont_desp["data_vencimento"]<=date("Y-m-d"))
		{
			$cor = 'background-color:#99FFFF';
		}
		else
		{
			$cor = '';
		}

		$xml->startElement('row');
		    $xml->writeAttribute('id',$cont_desp["id_sgi_controle"]);
			$xml->writeAttribute('style',$cor);
	
			$xml->startElement('cell');
				$xml->text($cont_desp["sgi_item"]);
			$xml->endElement();
			$xml->startElement('cell');
				$xml->text($cont_desp["sgi_requisito"]);
			$xml->endElement();
			$xml->startElement('cell');
				$xml->text(mysql_php($cont_desp["data_realizacao"]));
			$xml->endElement();
			$xml->startElement('cell');
				$xml->text($cont_desp["vigencia"]);
			$xml->endElement();
			$xml->startElement('cell');
				$xml->text(mysql_php($cont_desp["data_vencimento"]));
			$xml->endElement();
			$xml->startElement('cell');
				$xml->text('<img style="cursor:pointer;" src="'.DIR_IMAGENS.'apagar.png" onclick=if(apagar("'.str_replace(' ',' ',$cont_desp["sgi_item"]).'")){xajax_excluir("'.$cont_desp["id_sgi_controle"].'","'.str_replace(' ',' ',$cont_desp["sgi_item"]).'");}>');
			$xml->endElement();
		
		$xml->endElement();	
	}
	
	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('div_grid',true,'430','".$conteudo."');");
	
	return $resposta;
}

function insere($dados_form)
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$msg = $conf->msg($resposta);
	
	if($conf->checa_permissao(8,$resposta))
	{
		$db = new banco_dados;
		
		if($dados_form["item"]!='' && $dados_form["requisito"]!='' && $dados_form["data_realizacao"]!='' && $dados_form["data_vencimento"]!='')
		{
			
			$sql = "SELECT * FROM ".DATABASE.".sgi_controle ";
			$sql .= "WHERE sgi_controle.reg_del = 0 ";
			$sql .= "AND id_sgi_item = '".$dados_form["item"]."' ";
			$sql .= "AND id_sgi_requisito = '".$dados_form["requisito"]."' ";
			$sql .= "AND vigencia = '".$dados_form["vigencia"]."' ";
			$sql .= "AND data_realizacao = '".php_mysql($dados_form["data_realizacao"])."' ";
			$sql .= "AND data_vencimento = '".php_mysql($dados_form["data_vencimento"])."' ";

			$db->select($sql,'MYSQL',true);

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}
			
			if($db->numero_registros<=0)
			{		
				$isql = "INSERT INTO ".DATABASE.".sgi_controle ";
				$isql .= "(id_sgi_item, id_sgi_requisito, data_realizacao, vigencia, data_vencimento) ";
				$isql .= "VALUES ('" . $dados_form["item"] . "', ";
				$isql .= "'".$dados_form["requisito"]."', ";
				$isql .= "'".php_mysql($dados_form["data_realizacao"])."', ";
				$isql .= "'".$dados_form["vigencia"]."', ";
				$isql .= "'".php_mysql($dados_form["data_vencimento"])."') ";

				$db->insert($isql,'MYSQL');

				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
				}				
					
				$resposta->addScript("xajax_atualizatabela('');");
				
				$resposta->addScript('xajax_voltar();');
			
				$resposta->addAlert($msg[1]);
			}
			else
			{
				$resposta->addAlert($msg[5]);
			}
	
		}
		else
		{
			$resposta->addAlert($msg[4]);
		}	
			
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

		$usql = "UPDATE ".DATABASE.".sgi_controle SET ";
		$usql .= "reg_del = 1, ";
		$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
		$usql .= "data_del = '".date('Y-m-d')."' ";
		$usql .= "WHERE sgi_controle.id_sgi_controle = '".$id."' ";

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

function calcula_vencimento($data,$vigencia=12)
{
	$resposta = new xajaxResponse();
	
	$resposta->addAssign("data_vencimento","value",calcula_data($data, "sum", "month", $vigencia));

	return $resposta;
}

$xajax->registerFunction("voltar");
$xajax->registerFunction("insere");
$xajax->registerFunction("excluir");
$xajax->registerFunction("atualizatabela");
$xajax->registerFunction("calcula_vencimento");

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

	mygrid.enableAutoHeight(autoh,height);
	mygrid.enableRowsHover(true,'cor_mouseover');

	mygrid.setHeader("Item, Requisito, Data realização, Vigência, Data vencimento, D",
		null,
		["text-align:left","text-align:left","text-align:left","text-align:center","text-align:center","text-align:center"]);
	mygrid.setInitWidths("100,600,100,60,110,30");
	mygrid.setColAlign("left,left,left,center,left,center");
	mygrid.setColTypes("ro,ro,ro,ro,ro,ro");
	mygrid.setColSorting("str,str,str,str,str,str");
	
	mygrid.setSkin("dhx_skyblue");
	mygrid.enableMultiselect(true);
	mygrid.enableCollSpan(true);		
	mygrid.init();
	mygrid.loadXMLString(xml);
}

</script>

<?php

$conf = new configs();

$array_item_values[] = "";
$array_item_output[] = "SELECIONE";

$array_requisito_values[] = "";
$array_requisito_output[] = "SELECIONE";

$sql = "SELECT * FROM ".DATABASE.".sgi_item ";
$sql .= "WHERE reg_del = 0 ";
$sql .= "ORDER BY sgi_item ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}
	
foreach($db->array_select as $regs)
{
	$array_item_values[] = $regs["id_sgi_item"];
	$array_item_output[] = $regs["sgi_item"];

}

$sql = "SELECT * FROM ".DATABASE.".sgi_requisito ";
$sql .= "WHERE reg_del = 0 ";
$sql .= "ORDER BY sgi_requisito ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}
	
foreach ($db->array_select as $regs)
{
	$array_requisito_values[] = $regs["id_sgi_requisito"];
	$array_requisito_output[] = $regs["sgi_requisito"];

}

$smarty->assign("revisao_documento","V3");

$smarty->assign("campo",$conf->campos('sgi_controle'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("data_realizacao",date('d/m/Y'));

$smarty->assign("option_item_values",$array_item_values);
$smarty->assign("option_item_output",$array_item_output);

$smarty->assign("option_requisito_values",$array_requisito_values);
$smarty->assign("option_requisito_output",$array_requisito_output);

$smarty->assign("classe",CSS_FILE);

$smarty->display('sgi_controle.tpl');

?>

