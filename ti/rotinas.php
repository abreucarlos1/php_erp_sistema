<?php 
/*
		Formul�rio de Rotinas
		
		Criado por Carlos Abreu  
		
		local/Nome do arquivo:
		../ti/rotinas.php
		
		Versão 0 --> VERSÃO INICIAL - 20/02/2014
		Versão 1 --> Atualização layout - Carlos Abreu - 11/04/2017
		Versão 2 --> Inclusão dos campos reg_del nas consultas - Carlos Abreu - 13/11/2017
		Versão 3 --> Inclusão dos campos reg_del nas consultas - 23/11/2017 - Carlos Abreu
		Versão 4 --> Layout responsivo - 06/02/2018 - Carlos Eduardo
*/	
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO M�DULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(318))
{
	nao_permitido();
}


function voltar()
{
	$resposta = new xajaxResponse();
	
	$resposta->addAssign("btninserir","value","Inserir");
	
	$resposta->addEvent("btninserir","onclick","xajax_insere(xajax.getFormValues('frm')); ");
	
	$resposta->addEvent("btnvoltar", "onclick", "history.back();");
	
	$resposta->addAssign("rotina","value","");
	
	return $resposta;

}

function atualizatabela($dados_form)
{
	
	$resposta = new xajaxResponse();
	
	$xml = new XMLWriter();
	
	$conf = new configs();
	
	$msg = $conf->msg($resposta);
	
	$campos = $conf->campos('ti_rotinas',$resposta);
	
	$db = new banco_dados;	

	$sql = "SELECT * FROM ti.ti_rotinas ";
	$sql .= "WHERE ti_rotinas.reg_del = 0 ";
	$sql .= "ORDER BY ti_rotina ";

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
		    $xml->writeAttribute('id',$regs["id_ti_rotina"]);
			
			$xml->startElement('cell');
				$xml->text($regs["ti_rotina"]);
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text('<img style="cursor:pointer;" src="'.DIR_IMAGENS.'apagar.png" onclick=if(apagar("'.$regs["ti_rotina"].'")){xajax_excluir("'.$regs["id_ti_rotina"].'","'.$regs["ti_rotina"] . '");}>');
			$xml->endElement();
		$xml->endElement();
	}

	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('rotinas',true,'420','".$conteudo."');");

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
		
		if($dados_form["rotina"]!='')
		{
			
			$sql = "SELECT * FROM ti.ti_rotinas ";
			$sql .= "WHERE ti_rotina = '".trim($dados_form["rotina"])."' ";
			$sql .= "AND ti_rotinas.reg_del = 0 ";

			$db->select($sql,'MYSQL',true);

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}
			
			if($db->numero_registros<=0)
			{		
				$isql = "INSERT INTO ti.ti_rotinas ";
				$isql .= "(ti_rotina) ";
				$isql .= "VALUES ('" . maiusculas($dados_form["rotina"]) . "') ";

				$db->insert($isql,'MYSQL');

				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
				}				
					
				$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'));");
				
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
	
	$sql = "SELECT * FROM ti.ti_rotinas ";
	$sql .= "WHERE ti_rotinas.id_ti_rotina = '".$id."' ";
	$sql .= "AND ti_rotinas.reg_del = 0 ";
	
	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}

	$regs = $db->array_select[0];
	
	$resposta -> addAssign("id_ti_rotina", "value",$id);
	
	$resposta -> addAssign("rotina", "value",$regs["ti_rotina"]);
	
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
		
		if($dados_form["rotina"]!='')
		{
			$sql = "SELECT * FROM ti.ti_rotinas ";
			$sql .= "WHERE ti_rotina = '".trim($dados_form["rotina"])."' ";
			$sql .= "AND ti_rotinas.reg_del = 0 ";
			$sql .= "AND id_ti_rotina <> '".$dados_form["id_ti_rotina"]."' ";
			
			$db->select($sql,'MYSQL',true);

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}
			
			$regs = $db->array_select[0];
			
			if($db->numero_registros<=0)
			{
				$usql = "UPDATE ti.ti_rotinas SET ";
				$usql .= "ti_rotina = '" . maiusculas($dados_form["rotina"]) . "' ";
				$usql .= "WHERE id_ti_rotina = '".$dados_form["id_ti_rotina"]."' ";
				$usql .= "AND reg_del = 0 ";

				$db->update($usql,'MYSQL');

				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
				}	
							
				$resposta->addAlert($msg[2]);
				
				$resposta->addScript("xajax_voltar();");
		
				$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'))");
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

	if($conf->checa_permissao(2,$resposta))
	{
		$db = new banco_dados;
		
		$usql = "UPDATE ti.ti_rotinas SET ";
		$usql .= "reg_del = 1, ";
		$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
		$usql .= "data_del = '".date('Y-m-d')."' ";
		$usql .= "WHERE ti_rotinas.id_ti_rotina = '".$id."' ";
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

//captura a tecla enter
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

	mygrid.setHeader("Rotina,D",
		null,
		["text-align:left","text-align:left","text-align:center"]);
	mygrid.setInitWidths("*,30");
	mygrid.setColAlign("left,center");
	mygrid.setColTypes("ro,ro");
	mygrid.setColSorting("str,str");
	
	mygrid.setSkin("dhx_skyblue");
	mygrid.enableMultiselect(true);
	mygrid.enableCollSpan(true);		
	mygrid.init();
	//mygrid.enableSmartRendering(true,32);
	mygrid.loadXMLString(xml);

}

</script>

<?php

$conf = new configs();

$smarty->assign("revisao_documento","V4");

$smarty->assign('larguraTotal', 1);

$smarty->assign("campo",$conf->campos('ti_rotinas'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("nome_formulario","ROTINAS");

$smarty->assign("classe",CSS_FILE);

$smarty->display('rotinas.tpl');

?>