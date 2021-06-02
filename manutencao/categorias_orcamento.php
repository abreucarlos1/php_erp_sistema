<?php
/*
		Formulário de CATEGORIAS ORCAMENTO	
		
		Criado por Carlos Abreu  
		
		local/Nome do arquivo:
		../manutencao/categorias_orcamento.php
	
		Versão 0 --> VERSÃO INICIAL : 31/01/2018 - Carlos Abreu
*/	

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(617))
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
	
	$campos = $conf->campos('categorias_orcamento',$resposta);
	
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
		
		$sql_filtro = "AND categorias_profissionais.categoria_profissional LIKE '".$sql_texto."' ";
	}
	
	$sql = "SELECT * FROM ".DATABASE.".categorias_profissionais ";
	$sql .= "WHERE categorias_profissionais.reg_del = 0 ";
	$sql .= $sql_filtro;
	$sql .= "ORDER BY categoria_profissional ";

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
			$xml->writeAttribute('id',$cont_desp["id_categoria_profissional"]);
			
			$xml->startElement('cell');
				$xml->text($cont_desp["categoria_profissional"]);
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text('<img src="'.DIR_IMAGENS.'apagar.png" style="cursor:pointer;" onclick=if(apagar("'. trim(str_replace($chars,"",$cont_desp["categoria_profissional"])).'")){xajax_excluir("'.$cont_desp["id_categoria_profissional"].'","'. trim(str_replace($chars,"",$cont_desp["categoria_profissional"])).'");}>');
			$xml->endElement();
			
		$xml->endElement();		
	}
	
	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('div_grid',true,'400','".$conteudo."');");
	
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
		
		if($dados_form["categoria"]!='')
		{
			
			$sql = "SELECT * FROM ".DATABASE.".categorias_profissionais ";
			$sql .= "WHERE categoria_profissional = '".trim($dados_form["categoria"])."' ";
			$sql .= "AND reg_del = 0 ";

			$db->select($sql,'MYSQL',true);

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}
			
			if($db->numero_registros<=0)
			{		
				$isql = "INSERT INTO ".DATABASE.".categorias_profissionais ";
				$isql .= "(categoria) ";
				$isql .= "VALUES ('" . maiusculas($dados_form["categoria"]) . "') ";

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
	
	$sql = "SELECT * FROM ".DATABASE.".categorias_profissionais ";
	$sql .= "WHERE categorias_profissionais.id_categoria_profissional = '".$id."' ";
	$sql .= "AND reg_del = 0 ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}

	$regs = $db->array_select[0];
	
	$resposta->addAssign("id_categoria", "value",$id);
	
	$resposta->addAssign("categoria", "value",$regs["categoria_profissional"]);
	
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
		
		if($dados_form["categoria"]!='')
		{
		
			$sql = "SELECT * FROM ".DATABASE.".categorias_profissionais ";
			$sql .= "WHERE categoria_profissional = '".maiusculas(trim($dados_form["categoria"]))."' ";
			$sql .= "AND id_categoria_profissional <> '".$dados_form["id_categoria"]."' ";
			$sql .= "AND reg_del = 0 ";

			$db->select($sql,'MYSQL',true);

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}
			
			if($db->numero_registros<=0)
			{
				$usql = "UPDATE ".DATABASE.".categorias_profissionais SET ";
				$usql .= "categoria_profissional = '" . maiusculas($dados_form["categoria"]) . "' ";
				$usql .= "WHERE id_categoria_profissional = '".$dados_form["id_categoria"]."' ";
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
		
		$usql = "UPDATE ".DATABASE.".categorias_profissionais SET ";
		$usql .= "reg_del = 1, ";
		$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
		$usql .= "data_del = '".date('Y-m-d')."' ";
		$usql .= "WHERE ".DATABASE.".id_categoria_profissional = '".$id."' ";
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

$conf = new configs();

$smarty->assign("revisao_documento","V0");

$smarty->assign("campo",$conf->campos('categorias_orcamento'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("classe",CSS_FILE);

$smarty->display('categorias_orcamento.tpl');

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

	mygrid.setHeader("Categoria,D",
		null,
		["text-align:left","text-align:center"]);
	mygrid.setInitWidths("*,25");
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