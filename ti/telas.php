<?php
/*
		Formul�rio de Telas	
		
		Criado por Carlos Abreu  
		
		local/Nome do arquivo:
		../ti/telas.php
	
		Versão 0 --> VERSÃO INICIAL : 24/03/2009
		Versão 1 --> Atualização Lay-out : 09/10/2012 - Carlos Abreu
		Versão 2 --> Atualização layout - Carlos Abreu - 11/04/2017
		Versão 3 --> Inclusão dos campos reg_del nas consultas - 23/11/2017 - Carlos Abreu
*/

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO M�DULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(110))
{
	nao_permitido();
}


function voltar()
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$botao = $conf->botoes($resposta);

	$resposta -> addScriptCall("reset_campos('frm_tela')");
	
	$resposta -> addAssign("btninserir", "value", $botao[1]);
	
	$resposta -> addEvent("btninserir", "onclick", "xajax_insere(xajax.getFormValues('frm_tela'));");
	
	$resposta -> addEvent("btnvoltar", "onclick", "history.back();");

	return $resposta;

}

function atualizatabela($filtro)
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$xml = new XMLWriter();
	
	$campos = $conf->campos('telas',$resposta);
	
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
		
		$sql_filtro = "AND tela.nome_tela LIKE '".$sql_texto."' ";
	}
	
	$sql = "SELECT * FROM ".DATABASE.".tela ";
	$sql .= "WHERE tela.reg_del = 0 ";
	$sql .= $sql_filtro;
	$sql .= "ORDER BY nome_tela ";

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
		    $xml->writeAttribute('id',$cont_desp["id_tela"]);
			
			$xml->startElement('cell');
				$xml->text($cont_desp["id_tela"]);
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text($cont_desp["nome_tela"]);
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text('<img style="cursor:pointer;" src="'.DIR_IMAGENS.'apagar.png" onclick=if(apagar("'.$cont_desp["nome_tela"].'")){xajax_excluir("'.$cont_desp["id_tela"].'","'.$cont_desp["nome_tela"] . '");}>');
			$xml->endElement();
		$xml->endElement();
			
	}

	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('telas',true,'420','".$conteudo."');");
	
	return $resposta;
}

function insere($dados_form)
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$msg = $conf->msg($resposta);
	
	if($conf->checa_permissao(8,$resposta)) //id_sub_modulo telas = 110
	{		
		$db = new banco_dados;
		
		if($dados_form["tela"]!="")
		{
			
			$sql = "SELECT * FROM ".DATABASE.".tela ";
			$sql .= "WHERE nome_tela = '".trim($dados_form["tela"])."' ";
			$sql .= "AND reg_del = 0 ";

			$db->select($sql,'MYSQL',true);

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}
			
			if($db->numero_registros<=0)
			{		
				$isql = "INSERT INTO ".DATABASE.".tela ";
				$isql .= "(nome_tela) ";
				$isql .= "VALUES ('" . minusculas($dados_form["tela"]) . "') ";

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
	
	$botao = $conf->botoes($resposta);

	$msg = $conf->msg($resposta);

	$db = new banco_dados;
	
	$sql = "SELECT * FROM ".DATABASE.".tela ";
	$sql .= "WHERE tela.id_tela = '".$id."' ";
	$sql .= "AND reg_del = 0 ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}

	$regs = $db->array_select[0];
	
	$resposta->addAssign("id_tela", "value",$id);
	
	$resposta->addAssign("tela", "value",$regs["nome_tela"]);
	
	$resposta->addAssign("btninserir", "value", $botao[3]);
	
	$resposta->addEvent("btninserir", "onclick", "xajax_atualizar(xajax.getFormValues('frm_tela'));");

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
		
		if($dados_form["tela"]!='')
		{
		
			$sql = "SELECT * FROM ".DATABASE.".tela ";
			$sql .= "WHERE nome_tela = '".trim($dados_form["tela"])."' ";
			$sql .= "AND id_tela <> '".$dados_form["id_tela"]."' ";
			$sql .= "AND reg_del = 0 ";

			$db->select($sql,'MYSQL',true);

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}
					
			if($db->numero_registros<=0)
			{
				$usql = "UPDATE ".DATABASE.".tela SET ";
				$usql .= "nome_tela = '" . minusculas($dados_form["tela"]) . "' ";
				$usql .= "WHERE id_tela = '".$dados_form["id_tela"]."' ";
				$usql .= "AND reg_del = 0 ";
	
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
		
		/*
		$dsql = "DELETE FROM ".DATABASE.".tela ";
		$dsql .= "WHERE tela.id_tela = '".$id."' ";

		$db->delete($dsql,'MYSQL');
		*/
		$usql = "UPDATE ".DATABASE.".tela SET ";
		$usql .= "reg_del = 1, ";
		$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
		$usql .= "data_del = '".date('Y-m-d')."' ";
		$usql .= "WHERE tela.id_tela = '".$id."' ";
		$usql .= "AND reg_del = 0 ";

		$db->update($usql,'MYSQL');

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
		
		$resposta->addScript("xajax_atualizatabela('');");
		
		$resposta -> addAlert($what . $msg[3]);
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

	mygrid.setHeader("Id,Tela,D",
		null,
		["text-align:left","text-align:left","text-align:center"]);
	mygrid.setInitWidths("400,*,25");
	mygrid.setColAlign("left,left,center");
	mygrid.setColTypes("ro,ro,ro");
	mygrid.setColSorting("str,str,str");
	
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

$smarty->assign("campo",$conf->campos('telas'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("revisao_documento","V3");

$smarty->assign("classe",CSS_FILE);

$smarty->display('telas.tpl');

?>

