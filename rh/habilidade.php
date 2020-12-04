<?php
/*
		Formulário de habilidade 	
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		../rh/habilidade.php
		
		Versão 0 --> VERSÃO INICIAL - 28/01/2008
		Versão 1 --> Atualização Lay-out : 11/08/2008
		Versão 2 --> Atualização classe banco de dados - 23/01/2015 - Carlos Abreu
		Versão 3 --> Atualização layout - 02/04/2015 - Eduardo
		Versão 4 --> Atualização layout - Carlos Abreu - 07/04/2017
		Versão 5 --> Inclusão dos campos reg_del nas consultas - 28/11/2017 - Carlos Abreu
*/	

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(93))
{
	nao_permitido();
}

function voltar()
{
	$resposta = new xajaxResponse();

	$resposta->addScriptCall("reset_campos('frm_habilidade')");
	
	$resposta->addAssign("btninserir", "value", "Inserir");
	
	$resposta->addEvent("btninserir", "onclick", "xajax_insere(xajax.getFormValues('frm_habilidade'));");

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
		
		$sql_filtro = " AND rh_habilidades.habilidade LIKE '".$sql_texto."' ";
	}
	
	$sql = "SELECT * FROM ".DATABASE.".rh_habilidades ";
	$sql .= "WHERE reg_del = 0 ";
	$sql .= $sql_filtro;

	$db->select($sql,'MYSQL',true);

	$xml = new XMLWriter();
	$xml->openMemory();
	$xml->startElement('rows');
	
	$chars = array("'","\"",")","(","\\","/");
	
	foreach($db->array_select as $cont_desp)
	{
		$xml->startElement('row');
		$xml->writeAttribute('id', $cont_desp['id_rh_habilidade']);
		$xml->writeElement('cell', $cont_desp['habilidade']);
		$xml->writeElement('cell', '<img src="'.DIR_IMAGENS.'apagar.png" style="cursor:pointer;" onclick=if(confirm("Deseja&nbsp;excluir&nbsp;a&nbsp;habilidade?")){xajax_excluir("'.$cont_desp['id_rh_habilidade'].'");}; >');
		$xml->endElement();	
	}
	
	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('habilidades', true, '460', '".$conteudo."');");
	
	return $resposta;
}

function insere($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	if($dados_form["habilidade"]!='')
	{
		$sql = "SELECT id_rh_habilidade FROM ".DATABASE.".rh_habilidades ";
		$sql .= "WHERE rh_habilidades.habilidade = '".maiusculas($dados_form["habilidade"])."' ";
		$sql .= "AND reg_del = 0 ";
		
		$db->select($sql,'MYSQL',true);

		if($db->numero_registros==0)
		{
			$isql = "INSERT INTO ".DATABASE.".rh_habilidades ";
			$isql .= "(habilidade) ";
			$isql .= "VALUES ('" . maiusculas($dados_form["habilidade"]) . "') ";
	
			$db->insert($isql,'MYSQL');
			
			$resposta->addScript("xajax_atualizatabela('');");
			
			$resposta->addScript("xajax_voltar();");
		
			$resposta->addAlert("habilidade cadastrado com sucesso.");			
		}
		else
		{
			$resposta->addAlert("Registro já existente no banco de dados.");			
		}		
	}
	else
	{
		$resposta->addAlert("Os campos devem estar preenchidos.");
	}

	return $resposta;
}

function editar($id)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
		
	$sql = "SELECT * FROM ".DATABASE.".rh_habilidades ";
	$sql .= "WHERE rh_habilidades.id_rh_habilidade = '".$id."' ";
	$sql .= "AND reg_del = 0 ";
	
	$db->select($sql,'MYSQL',true);

	$regs = $db->array_select[0];
	
	$resposta->addAssign("id_habilidade", "value",$id);
	
	$resposta->addAssign("habilidade", "value",$regs["habilidade"]);
	
	$resposta->addAssign("btninserir", "value", "Atualizar");

	$resposta->addEvent("btninserir", "onclick", "xajax_atualizar(xajax.getFormValues('frm_habilidade'));");
	
	$resposta->addEvent("btnvoltar", "onclick", "xajax_voltar();");
	
	return $resposta;	

}

function atualizar($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;

	if($dados_form["habilidade"]!='')
	{
		
		$sql = "SELECT id_rh_habilidade FROM ".DATABASE.".rh_habilidades ";
		$sql .= "WHERE rh_habilidades.habilidade = '".maiusculas($dados_form["habilidade"])."' ";
		$sql .= "AND reg_del = 0 ";

		$db->select($sql,'MYSQL',true);
 
		if($db->numero_registros==0)
		{
			$usql = "UPDATE ".DATABASE.".rh_habilidades SET ";
			$usql .= "habilidade = '" . maiusculas($dados_form["habilidade"]) . "' ";
			$usql .= "WHERE id_rh_habilidade = '".$dados_form["id_habilidade"]."' ";
			$usql .= "AND reg_del = 0 ";
	
			$db->update($usql,'MYSQL');
					
			$resposta->addScript("xajax.voltar();");
			
			$resposta->addScript("xajax_atualizatabela('');");
			
			$resposta->addAlert("habilidade atualizado com sucesso.");  
		}
		else
		{
			$resposta->addAlert("Registro já existente no banco de dados.");			
		}		
	}
	else
	{
		$resposta->addAlert("Os campos devem estar preenchidos.");
	}	

	return $resposta;
}

function excluir($id, $what)
{
	$resposta = new xajaxResponse();

	$db = new banco_dados;
	
	$usql = "UPDATE ".DATABASE.".rh_habilidades SET ";
	$usql .= "reg_del = 1, ";
	$usql .= "reg_who = ".$_SESSION['id_funcionario'].", ";
	$usql .= "data_del = '".date('Y-m-d')."' ";
	$usql .= "WHERE rh_habilidades.id_rh_habilidade = '".$id."' ";
	
	$db->update($usql,'MYSQL');

	$resposta->addScript("xajax_atualizatabela('');");
	
	$resposta->addAlert($what . " excluído com sucesso.");
	
	return $resposta;
}

$conf = new configs();

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

		mygrid.enableAutoHeight(autoh,height);
		mygrid.enableRowsHover(true,'cor_mouseover');
	
		mygrid.setHeader("Habilidade, D");
		mygrid.setInitWidths("*,50");
		mygrid.setColAlign("left,center");
		mygrid.setColTypes("ro,ro");
		mygrid.setColSorting("str,str");
	
		function editar(id, col)
		{
			if (col == 0)
				xajax_editar(id);
		}
		
		mygrid.attachEvent("onRowSelect",editar);
	
		mygrid.setSkin("dhx_skyblue");
		mygrid.enableMultiselect(true);
		mygrid.enableCollSpan(true);
		mygrid.init();
		mygrid.loadXMLString(xml);
	}
</script>

<?php
$smarty->assign('campo', $conf->campos('habilidade'));

$smarty->assign('revisao_documento', 'V5');

$smarty->assign("classe",CSS_FILE);

$smarty->display('habilidade.tpl');
?>