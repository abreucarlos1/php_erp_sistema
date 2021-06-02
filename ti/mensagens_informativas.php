<?php
/*
    Formulário de Mensagens Informativas
    
    Criado por Carlos Eduardo  
    
    local/Nome do arquivo:
    ../ti/mensagens_informativas.php
    
    Versão 0 --> VERSÃO INICIAL : 20/11/2017
	Versão 1 --> Inclusão dos campos reg_del nas consultas - 23/11/2017 - Carlos Abreu
	Versão 2 --> Layout responsivo - 05/02/2018 - Carlos Eduardo
*/	
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(613))
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
	
	$resposta->addEvent("btninserir", "onclick", "xajax_salvar(xajax.getFormValues('frm'));");
	
	$resposta->addEvent("btnvoltar", "onclick", "history.back();");

	return $resposta;

}

function atualizatabela($filtro)
{
	$resposta = new xajaxResponse();
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
		
		$sql_filtro = "AND mensagem LIKE '".$sql_texto."' ";
	}
	
	$sql = "SELECT * FROM ".DATABASE.".mensagens_informativas ";
	$sql .= "WHERE reg_del = 0 ";
	$sql .= $sql_filtro;
	$sql .= "ORDER BY mensagem ";

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
	
	foreach($db->array_select as $reg)
	{
		$xml->startElement('row');
    		$xml->writeAttribute('id', $reg["id"]);
    		$xml->writeElement('cell', $reg["mensagem"]);
			
			$img = $reg['ativo'] == 0 ? 'NAO' : 'SIM';
			
			$xml->writeElement('cell', $img);
			
			$img = '<span class="icone icone-excluir cursor" onclick=if(confirm("Deseja excluir?")){xajax_excluir('.$reg['id'].');}></span>';
			$xml->writeElement('cell', $img);
		$xml->endElement();
	}

	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);

	$resposta->addScript("grid('div_mensagens', true, '300', '".$conteudo."');");
	
	return $resposta;
}

function salvar($dados_form)
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();
	
	if(empty($dados_form['id_mensagem']))
	{		
		$isql = "INSERT INTO ".DATABASE.".mensagens_informativas ";
		$isql .= "(mensagem, ativo) ";
		$isql .= "VALUES ('" . maiusculas($dados_form["mensagem"]) . "', ".$dados_form['ativo'].") ";

		$db->insert($isql,'MYSQL');
	}
	else
	{
	    $usql = "UPDATE ".DATABASE.".mensagens_informativas ";
	    $usql .= "SET mensagem = '" . maiusculas($dados_form["mensagem"]) . "', ";
	    $usql .= 'ativo = '.$dados_form['ativo']." ";
	    $usql .= "WHERE id = ".$dados_form['id_mensagem']." ";
		$usql .= "AND reg_del = 0 ";
	    
	    $db->update($usql,'MYSQL');    
    }
    
    if($db->erro!='')
    {
        $resposta->addAlert($db->erro);
    }
    else
    {
        $resposta->addAlert('Mensagem salva corretamente!');
    }
    
    $resposta->addScript("xajax_atualizatabela('');");
    
    $resposta->addScript('xajax_voltar();');

	return $resposta;
}

function editar($id)
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();
	
	$conf = new configs();
	
	$botao = $conf->botoes();
	$msg = $conf->msg($resposta);

	
	$sql = "SELECT * FROM ".DATABASE.".mensagens_informativas ";
	$sql .= "WHERE id = '".$id."' ";
	$sql .= "AND reg_del = 0 ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}

	$reg = $db->array_select[0];
	
	$resposta->addAssign("id_mensagem", "value",$id);
	
	$resposta->addAssign("mensagem", "value",$reg["mensagem"]);
	
	$ativo = !empty($reg['ativo']) && $reg['ativo'] == 1 ? 'ativo' : 'inativo';
	
	$resposta->addAssign($ativo, "checked","checked");
	
	$resposta->addAssign("btninserir", "value", $botao[3]);
	
	$resposta->addEvent("btninserir", "onclick", "xajax_salvar(xajax.getFormValues('frm'));");

	$resposta->addEvent("btnvoltar", "onclick", "xajax_voltar();");
	
	return $resposta;
}

function excluir($id)
{
	$resposta = new xajaxResponse();
	$db = new banco_dados;
		
	$usql = "UPDATE ".DATABASE.".mensagens_informativas SET ";
	$usql .= "reg_del = 1, ";
	$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
	$usql .= "data_del = '".date('Y-m-d')."' ";
	$usql .= "WHERE id = '".$id."' ";
	$usql .= "AND reg_del = 0 ";

	$db->update($usql,'MYSQL');

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	else
	{
	    $resposta->addAlert('Registro excluido corretamente!');
    	$resposta->addScript("xajax_atualizatabela('');");
	}
	
	return $resposta;
}

$xajax->registerFunction("voltar");
$xajax->registerFunction("salvar");
$xajax->registerFunction("editar");
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
	
	mygrid.setHeader("Mensagem,A,D",
			null,
			["text-align:left","text-align:center","text-align:center"]);
	mygrid.setInitWidths("*,50,50");
	mygrid.setColAlign("left,center,center");
	mygrid.setColTypes("ro,ro,ro");
	mygrid.setColSorting("str,str,str");
	
	mygrid.attachEvent("onRowSelect",doOnRowSelected);
	
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

$smarty->assign('larguraTotal', 1);

$smarty->assign("campo",$conf->campos('mensagens_informativas'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("classe",CSS_FILE);

$smarty->display('mensagens_informativas.tpl');
?>