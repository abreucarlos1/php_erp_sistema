<?php
/*
		Formulário de Botões	
		
		Criado por Carlos Abreu  
		
		local/Nome do arquivo:
		../administracao/botoes.php
	
		Versão 0 --> VERSÃO INICIAL : 20/05/2021

*/

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(165))
{
	nao_permitido();
}


function voltar()
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$botao = $conf->botoes($_COOKIE["idioma"],$resposta);

	$resposta -> addScriptCall("reset_campos('frm_botoes')");
	
	$resposta -> addAssign("btninserir", "value", $botao[1]);
	
	$resposta -> addEvent("btninserir", "onclick", "xajax_insere(xajax.getFormValues('frm_botoes'));");
	
	$resposta -> addEvent("btnvoltar", "onclick", "history.back();");

	return $resposta;

}

function atualizatabela($filtro)
{
	$resposta = new xajaxResponse();
	
	$xml = new XMLWriter();
	
	$conf = new configs();
	
	$campos = $conf->campos('botoes',$_COOKIE["idioma"],$resposta);
	
	$msg = $conf->msg($_COOKIE["idioma"],$resposta);
	
	$db = new banco_dados;
	
	$sql_filtro = "";
	
	$sql_texto = "";
	
	$filtro_tela = "";
	
	if($filtro!="")
	{		
		$array_valor = explode(" ",$filtro);
		
		for($x=0;$x<count($array_valor);$x++)
		{
			$sql_texto .= "%" . $array_valor[$x] . "%";
		}
		
		$sql_filtro = "AND botoes.texto LIKE '".$sql_texto."' ";
	}
	
	$sql = "SELECT * FROM ".DATABASE.".idiomas, ".DATABASE.".botoes ";
	$sql .= "WHERE botoes.id_idioma = idiomas.id_idioma ";
	$sql .= "AND botoes.reg_del = 0 ";
	$sql .= "AND idiomas.reg_del = 0 ";
	$sql .= $sql_filtro;
	$sql .= "ORDER BY botoes.ordem ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($msg[6].$db->erro);
	}

	$conteudo = "";
	
	$xml->openMemory();
	$xml->setIndent(false);
	$xml->startElement('rows');
	
	foreach($db->array_select as $cont_desp)
	{
		$xml->startElement('row');
			$xml->writeAttribute('id',$cont_desp["id_botao"]);
			
			$xml->startElement('cell');
				$xml->text($cont_desp["texto"]);
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text($cont_desp["idioma"]);
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text($cont_desp["ordem"]);
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text('<img src="'.DIR_IMAGENS.'apagar.png" style="cursor:pointer;" onclick=if(apagar("'. trim($cont_desp["texto"]).'")){xajax_excluir("'.$cont_desp["id_botao"].'","'. $cont_desp["texto"].'");}>');
			$xml->endElement();
			
		$xml->endElement();		
		
	}

	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('botoes',true,'450','".$conteudo."');");
	
	return $resposta;
}

function insere($dados_form)
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$msg = $conf->msg($_COOKIE["idioma"],$resposta);
	
	if($conf->checa_permissao(8,$resposta)) //id_sub_modulo botoes = 165
	{	
		$db = new banco_dados;
		
		if($dados_form["texto"]!='' && $dados_form["idioma"]!='' && $dados_form["ordem"]!='')
		{		
			$sql = "SELECT * FROM ".DATABASE.".botoes ";
			$sql .= "WHERE texto = '".trim($dados_form["texto"])."' ";
			$sql .= "AND id_idioma = '".$dados_form["idioma"]."' ";
			$sql .= "AND ordem = '".$dados_form["ordem"]."' ";
			$sql .= "AND reg_del = 0 ";

			$db->select($sql,'MYSQL',true);

			if($db->erro!='')
			{
				$resposta->addAlert($msg[6].$db->erro);
			}
			
			if($db->numero_registros<=0)
			{	
				$isql = "INSERT INTO ".DATABASE.".botoes ";
				$isql .= "(texto, id_idioma, ordem) VALUES( ";
				$isql .= "'" . trim($dados_form["texto"]) . "', ";
				$isql .= "'" . $dados_form["idioma"] . "', ";
				$isql .= "'" . $dados_form["ordem"] . "') ";

				$db->insert($isql,'MYSQL');

				if($db->erro!='')
				{
					$resposta->addAlert($msg[7].$db->erro);
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

function editar($id)
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();

	$botao = $conf->botoes($_COOKIE["idioma"]);
	
	$msg = $conf->msg($_COOKIE["idioma"]);

	$db = new banco_dados;
	
	$sql = "SELECT * FROM ".DATABASE.".botoes ";
	$sql .= "WHERE botoes.id_botao = '".$id."' ";
	$sql .= "AND reg_del = 0 ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($msg[6].$db->erro);
	}

	$regs = $db->array_select[0];
	
	$resposta -> addAssign("id_botao", "value",$id);
	
	$resposta -> addAssign("texto", "value",$regs["texto"]);
	
	$resposta -> addScript("seleciona_combo(".$regs["id_idioma"].",'idioma');");

	$resposta -> addAssign("ordem", "value",$regs["ordem"]);
	
	$resposta -> addAssign("btninserir", "value", $botao[3]);
	
	$resposta -> addEvent("btninserir", "onclick", "xajax_atualizar(xajax.getFormValues('frm_botoes'));");

	$resposta -> addEvent("btnvoltar", "onclick", "xajax_voltar();");
	
	return $resposta;	

}

function atualizar($dados_form)
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$msg = $conf->msg($_COOKIE["idioma"]);
	
	if($conf->checa_permissao(4,$resposta)) //id_sub_modulo botoes = 165
	{
		$db = new banco_dados;		
		
		if($dados_form["texto"]!='' && $dados_form["idioma"]!='' && $dados_form["ordem"]!='')
		{
			$sql = "SELECT * FROM ".DATABASE.".botoes ";
			$sql .= "WHERE texto = '".trim($dados_form["texto"])."' ";
			$sql .= "AND id_idioma = '".$dados_form["idioma"]."' ";
			$sql .= "AND ordem = '".$dados_form["ordem"]."' ";
			$sql .= "AND id_botao <> '".$dados_form["id_botao"]."' ";
			$sql .= "AND reg_del = 0 ";

			$db->select($sql,'MYSQL',true);

			if($db->erro!='')
			{
				$resposta->addAlert($msg[6].$db->erro);
			}			
			
			if($db->numero_registros<=0)
			{
				$usql = "UPDATE ".DATABASE.".botoes SET ";
				$usql .= "texto = '" . trim($dados_form["texto"]) . "', ";
				$usql .= "id_idioma = '" . $dados_form["idioma"] . "', ";
				$usql .= "ordem = '" . $dados_form["ordem"] . "' ";
				$usql .= "WHERE id_botao = '".$dados_form["id_botao"]."' ";
				$usql .= "AND reg_del = 0 ";

				$db->update($usql,'MYSQL');

				if($db->erro!='')
				{
					$resposta->addAlert($msg[8].$db->erro);
				}				
				
				$resposta->addAlert($msg[2]);
				
				$resposta->addScript("xajax_voltar();");
		
				$resposta->addScript("xajax_atualizatabela('');");
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
	
	$msg = $conf->msg($_COOKIE["idioma"],$resposta);

	if($conf->checa_permissao(2,$resposta)) //id_sub_modulo botoes = 165
	{
		$db = new banco_dados;
		
		$usql = "UPDATE ".DATABASE.".botoes SET ";
		$usql .= "reg_del = 1, ";
		$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
		$usql .= "data_del = '".date('Y-m-d')."' ";
		$usql .= "WHERE botoes.id_botao = '".$id."' ";
		$usql .= "AND reg_del = 0 ";
		
		$db->update($usql,'MYSQL');

		if($db->erro!='')
		{
			$resposta->addAlert($msg[9].$db->erro);
		}

		$resposta->addScript("xajax_atualizatabela('');");
		
		$resposta->addAlert($what . $msg[3]);

	}

	return $resposta;
}

$xajax->registerFunction("atualizatabela");
$xajax->registerFunction("voltar");
$xajax->registerFunction("insere");
$xajax->registerFunction("editar");
$xajax->registerFunction("atualizar");
$xajax->registerFunction("excluir");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","xajax_atualizatabela('');");

$conf = new configs();

$array_idioma_values = NULL;
$array_idioma_output = NULL;

$array_idioma_values[] = "";
$array_idioma_output[] = "SELECIONE";

$smarty->assign("campo",$conf->campos('botoes',$_COOKIE["idioma"]));

$smarty->assign("botao",$conf->botoes($_COOKIE["idioma"]));

$msg = $conf->msg($_COOKIE["idioma"]);

$db = new banco_dados;

$sql = "SELECT id_idioma, idioma FROM ".DATABASE.".idiomas ";
$sql .= "WHERE reg_del = 0 ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($msg[6].$db->erro);
}

foreach ($db->array_select as $regs)
{
	$array_idioma_values[] = $regs["id_idioma"];
	$array_idioma_output[] = $regs["idioma"];
}

$smarty->assign("revisao_documento","V0");

$smarty->assign("option_idioma_values",$array_idioma_values);
$smarty->assign("option_idioma_output",$array_idioma_output);

$smarty->assign("classe",CSS_FILE);

$smarty->assign("nome_empresa",NOME_EMPRESA);

$smarty->assign('larguraTotal', 1);

$smarty->display('botoes.tpl');

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

	mygrid.setHeader("Texto,Idioma,Ordem,D",
		null,
		["text-align:left","text-align:left","text-align:left","text-align:center"]);
	mygrid.setInitWidths("*,*,*,30");
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

