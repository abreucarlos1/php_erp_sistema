<?php
/*
		Formulário de Campos	
		
		Criado por Carlos Abreu  
		
		local/Nome do arquivo:
		../ti/campos.php
	
		Versão 0 --> VERSÃO INICIAL : 24/03/2009
		Versão 1 --> Atualização classe de banco de dados - 27/01/2015 - Carlos Abreu
		Versão 2 --> Atualização layout - Carlos Abreu - 11/04/2017
		Versão 3 --> Inclusão dos campos reg_del nas consultas - 23/11/2017 - Carlos Abreu
*/

ini_set('max_execution_time', 0); // No time limit
ini_set('post_max_size', '20M');
ini_set('upload_max_filesize', '20M');
ini_set('memory_limit', '1024M');

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(111))
{
	nao_permitido();
}

function voltar()
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$botao = $conf->botoes($resposta);

	$resposta -> addScriptCall("reset_campos('frm_campos')");
	
	$resposta -> addAssign("btninserir", "value", $botao[1]);
	
	$resposta -> addEvent("btninserir", "onclick", "xajax_insere(xajax.getFormValues('frm_campos'));");
	
	$resposta -> addEvent("btnvoltar", "onclick", "history.back();");

	return $resposta;

}

function atualizatabela($filtro,$index_tela)
{
	$resposta = new xajaxResponse();
	
	$xml = new XMLWriter();
	
	$conf = new configs();
	
	$campos = $conf->campos('campos',$resposta);
	
	$msg = $conf->msg($resposta);
	
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
		
		$sql_filtro = "AND (campos.texto LIKE '".$sql_texto."' ";
		$sql_filtro .= "OR tela.nome_tela LIKE '".$sql_texto."') ";
	}
	
	if($index_tela!="")
	{
		$filtro_tela = "AND tela.id_tela = ".$index_tela." ";
	}
	
	$sql = "SELECT * FROM ".DATABASE.".idiomas, ".DATABASE.".campos, ".DATABASE.".tela ";
	$sql .= "WHERE campos.id_tela = tela.id_tela ";
	$sql .= "AND idiomas.reg_del = 0 ";
	$sql .= "AND campos.reg_del = 0 ";
	$sql .= "AND tela.reg_del = 0 ";
	$sql .= "AND campos.id_idioma = idiomas.id_idioma ";
	$sql .= $sql_filtro;
	$sql .= $filtro_tela;
	$sql .= "ORDER BY campos.ordem ";

	$db->select($sql,'MYSQL',true);

	$conteudo = "";
	
	$xml->openMemory();
	$xml->setIndent(false);
	$xml->startElement('rows');
	
	foreach($db->array_select as $cont_desp)
	{	
		$xml->startElement('row');
			$xml->writeAttribute('id',$cont_desp["id_campo"]);
			
			$xml->startElement('cell');
				$xml->text($cont_desp["nome_tela"]);
			$xml->endElement();
			
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
				$xml->text('<img src="'.DIR_IMAGENS.'apagar.png" style="cursor:pointer;" onclick=if(apagar("'. trim($cont_desp["texto"]).'")){xajax_excluir("'.$cont_desp["id_campo"].'","'. $cont_desp["texto"].'");}>');
			$xml->endElement();
			
		$xml->endElement();	
			
	}
	
	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('campos',true,'450','".$conteudo."');");
	
	return $resposta;
}

function insere($dados_form)
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$msg = $conf->msg($resposta);
	
	if($conf->checa_permissao(8,$resposta)) //id_sub_modulo campos = 111
	{	
		$db = new banco_dados;
		
		if($dados_form["tela"]!='' && $dados_form["texto"]!='' && $dados_form["idioma"]!='' && $dados_form["ordem"]!='')
		{		
			$sql = "SELECT * FROM ".DATABASE.".campos ";
			$sql .= "WHERE texto = '".trim($dados_form["texto"])."' ";
			$sql .= "AND id_tela = '".$dados_form["tela"]."' ";
			$sql .= "AND id_idioma = '".$dados_form["idioma"]."' ";
			$sql .= "AND ordem = '".$dados_form["ordem"]."' ";
			$sql .= "AND reg_del = 0 ";
			
			$db->select($sql,'MYSQL',true);
			
			if($db->numero_registros<=0)
			{	
				$isql = "INSERT INTO ".DATABASE.".campos ";
				$isql .= "(id_tela, texto, id_idioma, ordem) VALUES( ";
				$isql .= "'" . trim($dados_form["tela"]) . "', ";
				$isql .= "'" . $dados_form["texto"] . "', ";
				$isql .= "'" . $dados_form["idioma"] . "', ";
				$isql .= "'" . $dados_form["ordem"] . "') ";
		
				$db->insert($isql,'MYSQL');
					
				$resposta->addScript("xajax_atualizatabela('','".$dados_form["tela"]."');");
	
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
	
	$sql = "SELECT * FROM ".DATABASE.".campos ";
	$sql .= "WHERE campos.id_campo = '".$id."' ";
	$sql .= "AND campos.reg_del = 0 ";
	
	$db->select($sql,'MYSQL',true);

	$regs = $db->array_select[0];
	
	$resposta -> addAssign("id_campo", "value",$id);
	
	$resposta -> addScript("seleciona_combo(".$regs["id_tela"].",'tela');");
	
	$resposta -> addAssign("texto", "value",$regs["texto"]);
	
	$resposta -> addScript("seleciona_combo(".$regs["id_idioma"].",'idioma');");

	$resposta -> addAssign("ordem", "value",$regs["ordem"]);
	
	$resposta -> addAssign("btninserir", "value", $botao[3]);
	
	$resposta -> addEvent("btninserir", "onclick", "xajax_atualizar(xajax.getFormValues('frm_campos'));");

	$resposta -> addEvent("btnvoltar", "onclick", "xajax_voltar();");
	
	return $resposta;	

}

function atualizar($dados_form)
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$msg = $conf->msg($resposta);
	
	if($conf->checa_permissao(4,$resposta)) //id_sub_modulo campos = 111
	{
		$db = new banco_dados;
		
		if($dados_form["tela"]!='')
		{
			$sql = "SELECT * FROM ".DATABASE.".campos ";
			$sql .= "WHERE texto = '".trim($dados_form["texto"])."' ";
			$sql .= "AND id_tela = '".$dados_form["tela"]."' ";
			$sql .= "AND id_idioma = '".$dados_form["idioma"]."' ";
			$sql .= "AND ordem = '".$dados_form["ordem"]."' ";
			$sql .= "AND id_campo <> '".$dados_form["id_campo"]."' ";
			$sql .= "AND reg_del = 0 ";
			
			$db->select($sql,'MYSQL',true);
			
			if($db->numero_registros<=0)
			{
				$usql = "UPDATE ".DATABASE.".campos SET ";
				$usql .= "texto = '" . $dados_form["texto"] . "', ";
				$usql .= "id_tela = '" . $dados_form["tela"] . "', ";
				$usql .= "id_idioma = '" . $dados_form["idioma"] . "', ";
				$usql .= "ordem = '" . $dados_form["ordem"] . "' ";
				$usql .= "WHERE id_campo = '".$dados_form["id_campo"]."' ";
				$usql .= "AND reg_del = 0 ";

				$db->update($usql,'MYSQL');
				
				$resposta->addAlert($msg[2]);
				
				$resposta->addScript("xajax_voltar();");
		
				$resposta->addScript("xajax_atualizatabela('','".$dados_form["tela"]."');");
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
		
		$sql = "SELECT * FROM ".DATABASE.".campos ";
		$sql .= "WHERE id_campo = '".$id."' ";
		$sql .= "AND reg_del = 0 ";
		
		$db->select($sql,'MYSQL',true);
		
		$regs = $db->array_select[0];
		
		$usql = "UPDATE ".DATABASE.".campos SET ";
		$usql .= "reg_del = 1, ";
		$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
		$usql .= "data_del = '".date('Y-m-d')."' ";
		$usql .= "WHERE campos.id_campo = '".$id."' ";
		$usql .= "AND reg_del = 0 ";
		
		$db->update($usql,'MYSQL');

		$resposta->addScript("xajax_atualizatabela('','".$regs["id_tela"]."');");
		
		$resposta -> addAlert($what . $msg[3]);
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

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script language="javascript">

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

	mygrid.setHeader("Tela,Campo,Idioma,Ordem,D",
		null,
		["text-align:left","text-align:left","text-align:left","text-align:left","text-align:center"]);
	mygrid.setInitWidths("*,*,*,*,30");
	mygrid.setColAlign("left,left,left,left,center");
	mygrid.setColTypes("ro,ro,ro,ro,ro");
	mygrid.setColSorting("str,str,str,str,str");
	
	mygrid.setSkin("dhx_skyblue");
	mygrid.enableMultiselect(true);
	mygrid.enableCollSpan(true);		
	mygrid.init();
	mygrid.loadXMLString(xml);

}

</script>

<?php
$conf = new configs();

$array_tela_values = NULL;
$array_tela_output = NULL;

$array_tela_values[] = "";
$array_tela_output[] = "SELECIONE";

$array_idioma_values = NULL;
$array_idioma_output = NULL;

$array_idioma_values[] = "";
$array_idioma_output[] = "SELECIONE";

$smarty->assign("campo",$conf->campos('campos'));

$smarty->assign("botao",$conf->botoes());

$msg = $conf->msg();

$db = new banco_dados;

$sql = "SELECT * FROM ".DATABASE.".tela ";
$sql .= "ORDER BY nome_tela ";

$db->select($sql,'MYSQL',true);

foreach ($db->array_select as $regs)
{
	$array_tela_values[] = $regs["id_tela"];
	$array_tela_output[] = $regs["nome_tela"];
}

$sql = "SELECT * FROM ".DATABASE.".idiomas ";

$db->select($sql,'MYSQL',true);

foreach ($db->array_select as $regs)
{
	$array_idioma_values[] = $regs["id_idioma"];
	$array_idioma_output[] = $regs["idioma"];
}

$smarty->assign("option_tela_values",$array_tela_values);
$smarty->assign("option_tela_output",$array_tela_output);

$smarty->assign("option_idioma_values",$array_idioma_values);
$smarty->assign("option_idioma_output",$array_idioma_output);

$smarty->assign("revisao_documento","V3");

$smarty->assign("classe",CSS_FILE);

$smarty->display('campos.tpl');

?>

