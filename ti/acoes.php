<?php
/*
		Formul�rio de A�oes	
		
		Criado por Carlos Abreu  
		
		local/Nome do arquivo:
		../ti/acoes.php
	
		Versão 0 --> VERSÃO INICIAL : 23/03/2009
		Versão 1 --> Atualização Lay-out, DB: 09/10/2012 - Carlos Abreu
		Versão 2 --> Troca da grid para o novo modelos: 19/06/2015 - Eduardo
		Versão 3 --> Atualização layout - Carlos Abreu - 11/04/2017
		Versão 4 --> Inclusão dos campos reg_del nas consultas - 23/11/2017 - Carlos Abreu		
*/	
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO M�DULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(112))
{
	nao_permitido();
}

function voltar()
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$botao = $conf->botoes($_COOKIE["idioma"],$resposta);

	$resposta->addScriptCall("reset_campos('frm')");
	
	$resposta->addAssign("btninserir", "value", $botao[1]);
	
	$resposta->addEvent("btninserir", "onclick", "xajax_insere(xajax.getFormValues('frm'));");
	
	$resposta->addEvent("btnvoltar", "onclick", "history.back();");

	return $resposta;

}

function atualizatabela($filtro)
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$campos = $conf->campos('acoes',$resposta);
	
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
		
		$sql_filtro = "AND acoes.acao LIKE '".$sql_texto."' ";
	}
	
	$sql = "SELECT * FROM ti.acoes ";
	$sql .= "WHERE acoes.reg_del = 0 ";
	$sql .= $sql_filtro;
	$sql .= "ORDER BY acao ";

	$db->select($sql,'MYSQL',true);
	
	

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}

	$chars = array("'","\"",")","(","\\","/");

	$xml = new XMLWriter();
	$xml->setIndent(false);
	$xml->openMemory();
	$xml->startElement('rows');
	
	foreach($db->array_select as $cont_desp)
	{
		$xml->startElement('row');
			$xml->writeAttribute('id', $cont_desp["id_acao"]);
			$xml->writeElement('cell', $cont_desp["acao"]);
			$xml->writeElement('cell', '<img src="'.DIR_IMAGENS.'apagar.png" style="cursor:pointer;" onclick=if(confirm("Confirma&nbsp;a&nbsp;exclus�o?")){xajax_excluir("'.$cont_desp["id_acao"].'");}>');
		$xml->endElement();
	}

	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('acoes', true, '300', '".$conteudo."');");
	
	return $resposta;
}

function insere($dados_form)
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$msg = $conf->msg($resposta);
	
	if($conf->checa_permissao(8,$resposta)) //id_sub_modulo acoes = 112
	{
		$db = new banco_dados;
		
		if($dados_form["acao"]!='')
		{
			
			$sql = "SELECT * FROM ti.acoes ";
			$sql .= "WHERE acao = '".trim($dados_form["acao"])."' ";
			$sql .= "AND reg_del = 0 ";

			$db->select($sql,'MYSQL',true);

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}
			
			if($db->numero_registros<=0)
			{		
				$isql = "INSERT INTO ti.acoes ";
				$isql .= "(acao) ";
				$isql .= "VALUES ('" . maiusculas($dados_form["acao"]) . "') ";

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
	
	$sql = "SELECT * FROM ti.acoes ";
	$sql .= "WHERE acoes.id_acao = '".$id."' ";
	$sql .= "AND reg_del = 0 ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}

	$regs = $db->array_select[0];
	
	$resposta->addAssign("id_acao", "value",$id);
	
	$resposta->addAssign("acao", "value",$regs["acao"]);
	
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
	
	if($conf->checa_permissao(4,$resposta)) //id_sub_modulo acoes = 112
	{
		$db = new banco_dados;
		
		if($dados_form["acao"]!='')
		{
		
			$sql = "SELECT * FROM ti.acoes ";
			$sql .= "WHERE acao = '".maiusculas(trim($dados_form["acao"]))."' ";
			$sql .= "AND id_acao <> '".$dados_form["id_acao"]."' ";
			$sql .= "AND reg_del = 0 ";

			$db->select($sql,'MYSQL',true);

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}
			
			if($db->numero_registros<=0)
			{
				$usql = "UPDATE ti.acoes SET ";
				$usql .= "acao = '" . maiusculas($dados_form["acao"]) . "' ";
				$usql .= "WHERE id_acao = '".$dados_form["id_acao"]."' ";
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

	if($conf->checa_permissao(2,$resposta)) //id_sub_modulo acoes = 112
	{
		$db = new banco_dados;
		/*
		$dsql = "DELETE FROM ti.acoes ";
		$dsql .= "WHERE acoes.id_acao = '".$id."' ";

		$db->delete($dsql,'MYSQL');
		*/
		$usql = "UPDATE ti.acoes SET ";
		$usql .= "reg_del = 1, ";
		$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
		$usql .= "data_del = 0 ";		
		$usql .= "WHERE acoes.id_acao = '".$id."' ";
		$usql .= "AND reg_del = 0 ";

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

<script language="javascript">

function grid(tabela, autoh, height, xml)
{	
	function doOnRowSelected(row,col)
	{
		if(col<2)
		{
			xajax_editar(row);
		
			return true;
		}
		
		return false;
	}
	
	mygrid = new dhtmlXGridObject(tabela);

	mygrid.enableAutoHeight(autoh,height);
	mygrid.enableRowsHover(true,'cor_mouseover');

	mygrid.setHeader("A��o,D");
	mygrid.setInitWidths("*,50");
	mygrid.setColAlign("left,center");
	mygrid.setColTypes("ro,ro");
	mygrid.setColSorting("str,str");
	
	mygrid.attachEvent("onRowSelect",doOnRowSelected);
	
	mygrid.setSkin("dhx_skyblue");
    //mygrid.enableMultiselect(true);
    //mygrid.enableCollSpan(true);	
	mygrid.init();
	mygrid.loadXMLString(xml);
}
</script>

<?php
$conf = new configs();

$smarty->assign("revisao_documento","V4");

$smarty->assign("campo",$conf->campos('acoes'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("classe",CSS_FILE);

$smarty->display('acoes.tpl');
?>