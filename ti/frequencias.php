<?php 
/*
		Formulário de frequencias
		
		Criado por Carlos Abreu  
		
		local/Nome do arquivo:
		../ti/frequencias.php
		
		Versão 0 --> VERSÃO INICIAL - 20/02/2014
		Versão 1 -> Atualização layout - Carlos Abreu - 11/04/2017
		Versão 2 --> Inclusão dos campos reg_del nas consultas - Carlos Abreu - 13/11/2017
		Versão 3 --> Inclusão dos campos reg_del nas consultas - 23/11/2017 - Carlos Abreu
*/
	
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(319))
{
	nao_permitido();
}

function voltar()
{
	$resposta = new xajaxResponse();
	
	$resposta->addAssign("btninserir","value","Inserir");
	
	$resposta->addEvent("btninserir","onclick","xajax_insere(xajax.getFormValues('frm')); ");
	
	$resposta->addEvent("btnvoltar", "onclick", "history.back();");
	
	$resposta->addAssign("frequencia","value","");
	
	return $resposta;

}

function atualizatabela($dados_form)
{
	$resposta = new xajaxResponse();
	
	$xml = new XMLWriter();
	
	$conf = new configs();
	
	$msg = $conf->msg($resposta);
	
	$campos = $conf->campos('ti_frequencias',$resposta);
	
	$db = new banco_dados;	

	$sql = "SELECT * FROM ".DATABASE.".ti_frequencias ";
	$sql .= "WHERE ti_frequencias.reg_del = 0 ";
	$sql .= "ORDER BY ti_frequencia ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}

	$conteudo = "";

	$xml->openMemory();
	$xml->setIndent(false);
	$xml->startElement('rows');
	
	foreach($db->array_select as $regs)
	{
		$xml->startElement('row');
		    $xml->writeAttribute('id',$regs["id_ti_frequencia"]);
			
			$xml->startElement('cell');
				$xml->text($regs["ti_frequencia"]);
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text($regs["ti_frequencia_dias"]);
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text('<img style="cursor:pointer;" src="'.DIR_IMAGENS.'apagar.png" onclick=if(apagar("'.$regs["ti_frequencia"] . '")){xajax_excluir("'.$regs["id_ti_frequencia"].'","'.$regs["ti_frequencia"] . '");}>');
			$xml->endElement();
		$xml->endElement();
	}

	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('frequencias',true,'420','".$conteudo."');");

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
		
		if($dados_form["frequencia"]!='' && $dados_form["dias"]!='')
		{			
			$sql = "SELECT * FROM ".DATABASE.".ti_frequencias ";
			$sql .= "WHERE ti_frequencia = '".trim($dados_form["frequencia"])."' ";
			$sql .= "AND ti_frequencias.ti_frequencia_dias = '".trim($dados_form["dias"])."' ";
			$sql .= "AND ti_frequencias.reg_del = 0 ";

			$db->select($sql,'MYSQL',true);

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}
			
			if($db->numero_registros<=0)
			{		
				$isql = "INSERT INTO ".DATABASE.".ti_frequencias ";
				$isql .= "(ti_frequencia, ti_frequencia_dias) ";
				$isql .= "VALUES ('" . maiusculas($dados_form["frequencia"]) . "', ";
				$isql .= "'" . $dados_form["dias"] . "') ";

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
	
	$db = new banco_dados;
	
	$conf = new configs();

	$botao = $conf->botoes($resposta);
	
	$msg = $conf->msg($resposta);	
	
	$sql = "SELECT * FROM ".DATABASE.".ti_frequencias ";
	$sql .= "WHERE ti_frequencias.id_ti_frequencia = '".$id."' ";
	$sql .= "AND ti_frequencias.reg_del = 0 ";
	
	$db->select($sql,'MYSQL',true);
	
	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}

	$regs = $db->array_select[0];
	
	$resposta -> addAssign("id_ti_frequencia", "value",$id);
	
	$resposta -> addAssign("frequencia", "value",$regs["ti_frequencia"]);
	
	$resposta -> addAssign("dias", "value",$regs["ti_frequencia_dias"]);
	
	$resposta -> addAssign("btninserir", "value", $botao[3]);
	
	$resposta -> addEvent("btninserir", "onclick", "xajax_atualizar(xajax.getFormValues('frm'));");

	$resposta -> addEvent("btnvoltar", "onclick", "xajax_voltar();");

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
		
		if($dados_form["frequencia"]!='' && $dados_form["dias"]!='')
		{
			$sql = "SELECT * FROM ".DATABASE.".ti_frequencias ";
			$sql .= "WHERE ti_frequencia = '".trim($dados_form["frequencia"])."' ";
			$sql .= "AND ti_frequencia_dias = '".$dados_form["dias"]."' ";
			$sql .= "AND ti_frequencias.reg_del = 0 ";
			$sql .= "AND id_ti_frequencia <> '".$dados_form["id_ti_frequencia"]."' ";
			
			$db->select($sql,'MYSQL',true);

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}
			
			$regs = $db->array_select[0];
			
			if($db->numero_registros<=0)
			{
				$usql = "UPDATE ".DATABASE.".ti_frequencias SET ";
				$usql .= "ti_frequencia = '" . maiusculas($dados_form["frequencia"]) . "', ";
				$usql .= "ti_frequencia_dias = '" . $dados_form["dias"] . "' ";
				$usql .= "WHERE id_ti_frequencia = '".$dados_form["id_ti_frequencia"]."' ";
				$usql .= "AND reg_del = 0 ";

				$db->update($usql,'MYSQL');

				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
				}	
							
				$resposta->addAlert($msg[2]);
				
				$resposta->addScript("xajax_voltar();");
		
				$resposta->addScript("xajax_atualizatabela('')");
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

function excluir($id)
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$msg = $conf->msg($resposta);

	if($conf->checa_permissao(8,$resposta))
	{
		$db = new banco_dados;
		
		$usql = "UPDATE ".DATABASE.".ti_frequencias SET ";
		$usql .= "reg_del = 1, ";
		$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
		$usql .= "data_del = '".date('Y-m-d')."' ";
		$usql .= "WHERE ti_frequencias.id_ti_frequencia = '".$id."' ";
		$usql .= "AND reg_del = 0 ";
		
		$db->update($usql,'MYSQL');

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
		else
		{
			$resposta->addAlert($what . $msg[3]);	
		}

		$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'));");	

	}

	return $resposta;
}

$xajax->registerFunction("voltar");
$xajax->registerFunction("insere");
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

document.onkeypress = keyhandler;

function keyhandler(e) 
{
	if (document.layers)
		Key = e.which;
	else
		Key = window.event.keyCode;

	if (Key != 0)
		if (Key == 13)
			//alert('Enter key press');
			xajax_insere(xajax.getFormValues('frm'));
}

function grid(tabela, autoh, height, xml)
{	
	mygrid = new dhtmlXGridObject(tabela);
	
	function editar(id, col)
	{
		if (col <= 2)
		{
			xajax_editar(id);
		}
	}
	
	mygrid.attachEvent("onRowSelect",editar);
	
	mygrid.enableAutoHeight(autoh,height);
	
	mygrid.enableRowsHover(true,'cor_mouseover');

	mygrid.setHeader("Frequência,Dias,D",
		null,
		["text-align:left","text-align:left","text-align:left"]);
	mygrid.setInitWidths("*,150,25");
	mygrid.setColAlign("left,left,center");
	mygrid.setColTypes("ro,ro,ro");
	mygrid.setColSorting("str,str,str");
	
	mygrid.setSkin("dhx_skyblue");
	mygrid.enableMultiselect(true);
	mygrid.enableCollSpan(true);		
	mygrid.init();
	mygrid.loadXMLString(xml);

}

</script>

<?php

$conf = new configs();

$smarty->assign("revisao_documento","V3");

$smarty->assign("campo",$conf->campos('ti_frequencias'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("nome_formulario","FREQUÊNCIA");

$smarty->assign("classe",CSS_FILE);

$smarty->display('frequencias.tpl');

?>