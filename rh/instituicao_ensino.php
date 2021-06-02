<?php
/*
		Formulário de Instituição Ensino	
		
		Criado por Carlos Abreu  
		
		local/Nome do arquivo:
		../rh/instituicao_ensino.php
	
		Versão 0 --> VERSÃO INICIAL : 28/02/2013 - Carlos Abreu
		Versão 1 --> Atualização layout - Carlos Abreu - 07/04/2017
		Versão 2 --> Inclusão dos campos reg_del nas consultas - 28/11/2017 - Carlos Abreu
*/	


require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(267))
{
	nao_permitido();
}

function voltar()
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$botao = $conf->botoes($_COOKIE["idioma"],$resposta);

	$resposta -> addScriptCall("reset_campos('frm')");
	
	$resposta -> addAssign("btninserir", "value", $botao[1]);
	
	$resposta -> addEvent("btninserir", "onclick", "xajax_insere(xajax.getFormValues('frm'));");
	
	$resposta -> addEvent("btnvoltar", "onclick", "history.back();");

	return $resposta;

}

function atualizatabela($filtro)
{
	$resposta = new xajaxResponse();
	
	$xml = new XMLWriter();
	
	$conf = new configs();
	
	$campos = $conf->campos('instituicao_ensino',$resposta);
	
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
		
		$sql_filtro = "AND rh_instituicao_ensino.instituicao_ensino LIKE '".$sql_texto."' ";
	}
	
	$sql = "SELECT * FROM ".DATABASE.".rh_instituicao_ensino ";
	$sql .= "WHERE reg_del = 0 ";
	$sql .= $sql_filtro;
	$sql .= "ORDER BY instituicao_ensino ";

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
			$xml->writeAttribute('id',$cont_desp["id_rh_instituicao_ensino"]);
			
			$xml->startElement('cell');
				$xml->text($cont_desp["instituicao_ensino"]);
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text('<img src="'.DIR_IMAGENS.'apagar.png" style="cursor:pointer;" onclick=if(apagar("'. trim($cont_desp["instituicao_ensino"]).'")){xajax_excluir("'.$cont_desp["id_rh_instituicao_ensino"].'","'. trim($cont_desp["instituicao_ensino"]).'");}>');
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
	
	if($conf->checa_permissao(8,$resposta))
	{
		$db = new banco_dados;
		
		if($dados_form["instituicao_ensino"]!='')
		{
			
			$sql = "SELECT * FROM ".DATABASE.".rh_instituicao_ensino ";
			$sql .= "WHERE instituicao_ensino = '".trim($dados_form["instituicao_ensino"])."' ";
			$sql .= "AND reg_del = 0 ";

			$db->select($sql,'MYSQL',true);

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}
			
			if($db->numero_registros<=0)
			{		
				$isql = "INSERT INTO ".DATABASE.".rh_instituicao_ensino ";
				$isql .= "(instituicao_ensino) ";
				$isql .= "VALUES ('" . maiusculas($dados_form["instituicao_ensino"]) . "') ";

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

function editar($id)
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$botao = $conf->botoes();

	$msg = $conf->msg($resposta);

	$db = new banco_dados;
	
	$sql = "SELECT * FROM ".DATABASE.".rh_instituicao_ensino ";
	$sql .= "WHERE rh_instituicao_ensino.id_rh_instituicao_ensino = '".$id."' ";
	$sql .= "AND reg_del = 0 ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}

	$regs = $db->array_select[0];
	
	$resposta->addAssign("id_instituicao_ensino", "value",$id);
	
	$resposta->addAssign("instituicao_ensino", "value",$regs["instituicao_ensino"]);
	
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
	
	if($conf->checa_permissao(4,$resposta))
	{
		$db = new banco_dados;
		
		if($dados_form["instituicao_ensino"]!='')
		{
		
			$sql = "SELECT * FROM ".DATABASE.".rh_instituicao_ensino ";
			$sql .= "WHERE instituicao_ensino = '".maiusculas(trim($dados_form["instituicao_ensino"]))."' ";
			$sql .= "AND id_rh_instituicao_ensino <> '".$dados_form["id_instituicao_ensino"]."' ";
			$sql .= "AND reg_del = 0 ";

			$db->select($sql,'MYSQL',true);

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}
			
			if($db->numero_registros<=0)
			{
				$usql = "UPDATE ".DATABASE.".rh_instituicao_ensino SET ";
				$usql .= "instituicao_ensino = '" . maiusculas($dados_form["instituicao_ensino"]) . "' ";
				$usql .= "WHERE id_rh_instituicao_ensino = '".$dados_form["id_instituicao_ensino"]."' ";

				$db->update($usql,'MYSQL');

				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
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
	
	$msg = $conf->msg($resposta);

	if($conf->checa_permissao(2,$resposta))
	{
		$db = new banco_dados;
		
		$usql = "UPDATE ".DATABASE.".rh_instituicao_ensino SET ";
		$usql .= "reg_del = 1, ";
		$usql .= "reg_who = ".$_SESSION['id_funcionario'].", ";
		$usql .= "data_del = '".date('Y-m-d')."' ";
		$usql .= "WHERE rh_instituicao_ensino.id_rh_instituicao_ensino = '".$id."' ";

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

	mygrid.setHeader("Intituição,D",
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

$smarty->assign("revisao_documento","V2");

$smarty->assign("campo",$conf->campos('instituicao_ensino'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("classe",CSS_FILE);

$smarty->display('instituicao_ensino.tpl');

?>

