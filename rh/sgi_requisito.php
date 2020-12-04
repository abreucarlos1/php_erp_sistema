<?php
/*
		Formulário de REQUISITO - SGI RH	
		
		Criado por Carlos Abreu  
		
		local/Nome do arquivo:
		../rh/sgi_requisito.php
	
		Versão 0 --> VERSÃO INICIAL : 11/12/2012 - Carlos Abreu
		Versão 1 --> Atualização interface - 07/07/2016 - Carlos Abreu
		Versão 2 --> Atualização layout - Carlos Abreu - 10/04/2017
*/	


require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(258))
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
	
	$conf = new configs();
	
	$xml = new XMLWriter();
	
	$campos = $conf->campos('sgi_requisito',$resposta);
	
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
		
		$sql_filtro = "AND sgi_requisito.sgi_requisito LIKE '".$sql_texto."' ";
	}
	
	$sql = "SELECT * FROM ".DATABASE.".sgi_requisito ";
	$sql .= "WHERE sgi_requisito.reg_del = 0 ";
	$sql .= $sql_filtro;
	$sql .= "ORDER BY sgi_requisito ";

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
		$xml->startElement('row');
		    $xml->writeAttribute('id',$cont_desp["id_sgi_requisito"]);
			
			$xml->startElement('cell');
				$xml->text($cont_desp["sgi_requisito"]);
			$xml->endElement();		
			$xml->startElement('cell');
				$xml->text('<img style="cursor:pointer;" src="'.DIR_IMAGENS.'apagar.png" onclick=if(apagar("'.str_replace(' ','&nbsp;',$cont_desp["sgi_requisito"]).'")){xajax_excluir("'.$cont_desp["id_sgi_requisito"].'","'.str_replace(' ','&nbsp;',$cont_desp["sgi_requisito"]).'");}>');
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
		
		if($dados_form["sgi_requisito"]!='')
		{
			
			$sql = "SELECT * FROM ".DATABASE.".sgi_requisito ";
			$sql .= "WHERE sgi_requisito.reg_del = 0 ";
			$sql .= "AND sgi_requisito = '".trim($dados_form["sgi_requisito"])."' ";

			$db->select($sql,'MYSQL',true);

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}
						
			if($db->numero_registros<=0)
			{		
				$isql = "INSERT INTO ".DATABASE.".sgi_requisito ";
				$isql .= "(sgi_requisito) ";
				$isql .= "VALUES ('" . maiusculas($dados_form["sgi_requisito"]) . "') ";

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
	
	$sql = "SELECT * FROM ".DATABASE.".sgi_requisito ";
	$sql .= "WHERE sgi_requisito.reg_del = 0 ";
	$sql .= "AND sgi_requisito.id_sgi_requisito = '".$id."' ";

	$db->select($sql,'MYSQL',true);
	
	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}

	$regs = $db->array_select[0];
	
	$resposta->addAssign("id_sgi_requisito", "value",$id);
	
	$resposta->addAssign("sgi_requisito", "value",$regs["sgi_requisito"]);
	
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
		
		if($dados_form["sgi_requisito"]!='')
		{
		
			$sql = "SELECT * FROM ".DATABASE.".sgi_requisito ";
			$sql .= "WHERE sgi_requisito.reg_del = 0 ";
			$sql .= "AND sgi_requisito = '".maiusculas(trim($dados_form["sgi_requisito"]))."' ";
			$sql .= "AND id_sgi_requisito <> '".$dados_form["id_sgi_requisito"]."' ";

			$db->select($sql,'MYSQL',true);

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}
			
			if($db->numero_registros<=0)
			{
				$usql = "UPDATE ".DATABASE.".sgi_requisito SET ";
				$usql .= "sgi_requisito = '" . maiusculas($dados_form["sgi_requisito"]) . "' ";
				$usql .= "WHERE id_sgi_requisito = '".$dados_form["id_sgi_requisito"]."' ";
				$usql .= "AND sgi_requisito.reg_del = 0 ";

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

		$usql = "UPDATE ".DATABASE.".sgi_requisito SET ";
		$usql .= "reg_del = 1, ";
		$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
		$usql .= "data_del = '".date('Y-m-d')."' ";
		$usql .= "WHERE sgi_requisito.id_sgi_requisito = '".$id."' ";

		$db->update($usql,'MYSQL');

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}

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

<script language="javascript">

function grid(tabela, autoh, height, xml)
{	
	mygrid = new dhtmlXGridObject(tabela);
	
	function doOnRowSelected(row,col)
	{
		if(col<=0)
		{						
			xajax_editar(row);
  
			return true;
		}
	}
	
	mygrid.attachEvent("onRowSelect",doOnRowSelected);	
	
	mygrid.enableAutoHeight(autoh,height);
	mygrid.enableRowsHover(true,'cor_mouseover');

	mygrid.setHeader("Requisito, D",
		null,
		["text-align:left","text-align:center"]);
	mygrid.setInitWidths("970,30");
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

$smarty->assign("campo",$conf->campos('sgi_requisito'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("classe",CSS_FILE);

$smarty->display('sgi_requisito.tpl');

?>

