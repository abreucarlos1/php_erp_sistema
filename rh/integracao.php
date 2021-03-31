<?php
/*
		Formulário de Integração	
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		../rh/integracao.php
		
		Versão 0 --> VERSÃO INICIAL - 28/01/2008
		Versao 1 --> Atualização Lay-out : 12/08/2008
		Versão 2 --> Atualização Lay-out - 08/09/2014 - Carlos Abreu
		Versão 3 --> Atualização layout - Carlos Abreu - 07/04/2017
		Versão 4 --> Criação de um Relatório de integraçães validas - Carlos Eduardo - 10/08/2017
		Versão 5 --> Inclusão dos campos reg_del nas consultas - 28/11/2017 - Carlos Abreu		
*/

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(94) && !verifica_sub_modulo(495))
{
	nao_permitido();
}

function voltar()
{
	$resposta = new xajaxResponse();

	$resposta->addScriptCall("reset_campos('frm_integracao')");
	
	$resposta->addAssign("data_integracao", "value", date('d/m/Y'));
	
	$resposta->addAssign("vigencia", "value", "12");
	
	$resposta->addAssign("btninserir", "value", "Inserir");
	
	$resposta->addEvent("btninserir", "onclick", "xajax_insere(xajax.getFormValues('frm_integracao'));");
	
	$resposta->addEvent("btnvoltar", "onclick", "history.back();");

	return $resposta;
}

function atualizatabela($filtro)
{
	$resposta = new xajaxResponse();
	
	$xml = new XMLWriter();

	$db = new banco_dados;
	
	$conf = new configs();
	
	$campos = $conf->campos('integracao_clientes',$resposta);
	
	$msg = $conf->msg($resposta);

	$sql_filtro = "";
	
	$sql_texto = "";	
	
	if($filtro!="")
	{
		
		$array_valor = explode(" ",$filtro);
		
		for($x=0;$x<count($array_valor);$x++)
		{
			$sql_texto .= "%" . $array_valor[$x] . "%";
		}
		
		$sql_filtro = " AND (funcionarios.funcionario LIKE '".$sql_texto."' ";
		$sql_filtro .= " OR rh_integracao.id_rh_integracao LIKE '".$sql_texto."' ";
		$sql_filtro .= " OR local.descricao LIKE '".$sql_texto."') ";
	}
	
	$sql = "SELECT * FROM ".DATABASE.".funcionarios, ".DATABASE.".local, ".DATABASE.".rh_integracao ";
	$sql .= "WHERE rh_integracao.id_funcionario = funcionarios.id_funcionario ";
	$sql .= "AND rh_integracao.reg_del = 0 ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	$sql .= "AND local.reg_del = 0 ";
	$sql .= "AND funcionarios.situacao NOT IN ('DESLIGADO','CANCELADO') ";
	$sql .= "AND rh_integracao.id_local_trabalho = local.id_local ";
	$sql .= $sql_filtro;
	$sql .= "ORDER BY rh_integracao.data_vencimento, funcionarios.funcionario ";	

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
		if($cont_desp["data_vencimento"]<=date("Y-m-d"))
		{
			$estilo = 'bgcolor="#99FFFF" style="background-color:#99FFFF" title="VENCIDO" ';
			$status = 'VENCIDO';
		}
		else
		{
			$estilo = 'title="VÁLIDO" ';
			$status = 'VÁLIDO';
		}
		
		$xml->startElement('row');
			$xml->writeAttribute('id',$cont_desp["id_rh_integracao"]);
			$xml->writeAttribute('style',$estilo);
			
			$xml->startElement('cell');
				$xml->text($cont_desp["id_rh_integracao"]);
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text($cont_desp["funcionario"]);
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text($cont_desp["cracha"]);
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text($cont_desp["descricao"]);
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text(mysql_php($cont_desp["data_integracao"]));
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text(mysql_php($cont_desp["data_vencimento"]));
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text($status);
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text($cont_desp['observacoes']);
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text('<img src="'.DIR_IMAGENS.'apagar.png" style="cursor:pointer;" onclick=if(confirm("Deseja excluir?")){xajax_excluir("'.$cont_desp["id_rh_integracao"].'");}>');
			$xml->endElement();
			
		$xml->endElement();	
	
	}
	
	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('integracao',true,'450','".$conteudo."');");
	
	return $resposta;
}

function insere($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	$conf = new configs();
	
	$msg = $conf->msg($resposta);
	
	if($conf->checa_permissao(8,$resposta))
	{		
		if($dados_form["funcionario"]!='' && $dados_form["data_integracao"]!='' && $dados_form["data_vencimento"]!='' && $dados_form["local_trabalho"]!='')
		{
			$isql = "INSERT INTO ".DATABASE.".rh_integracao ";
			$isql .= "(id_funcionario, cracha, id_local_trabalho, data_integracao, vigencia, observacoes, data_vencimento) ";
			$isql .= "VALUES ('" . $dados_form["funcionario"] . "', ";
			$isql .= "'" . $dados_form["cracha"] . "', ";
			$isql .= "'" . $dados_form["local_trabalho"] . "', ";
			$isql .= "'" . php_mysql($dados_form["data_integracao"]) . "', ";
			$isql .= "'" . $dados_form["vigencia"] . "', ";
			$isql .= "'" . $dados_form["observacoes"] . "', ";
			$isql .= "'" . php_mysql($dados_form["data_vencimento"]) . "') ";

			$db->insert($isql,'MYSQL');

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}
			else
			{			
				$resposta->addScript("xajax_atualizatabela('');");
				
				$resposta->addAlert("Integração cadastrada com sucesso.");
			}	
		}
		else
		{
			$resposta -> addAlert("Os campos devem estar preenchidos.");
		}
	}
	
	$resposta->addScript('xajax_voltar();');	

	return $resposta;
}

function editar($id)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	$sql = "SELECT * FROM ".DATABASE.".rh_integracao ";
	$sql .= "WHERE rh_integracao.id_rh_integracao = '".$id."' ";
	$sql .= "AND reg_del = 0 ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}

	$regs = $db->array_select[0];		

	$resposta->addAssign("id_integracao", "value",$id);
	
	$resposta->addScript("seleciona_combo(".$regs["id_funcionario"].",'funcionario');");
	
	$resposta->addScript("seleciona_combo(".$regs["id_local_trabalho"].",'local_trabalho');");
	
	$resposta->addAssign("vigencia", "value",$regs["vigencia"]);
	
	$resposta->addAssign("cracha", "value",$regs["cracha"]);
	
	$resposta->addAssign("observacoes", "value",$regs["observacoes"]);
	
	$resposta->addAssign("data_integracao", "value",mysql_php($regs["data_integracao"]));
	
	$resposta->addAssign("data_vencimento", "value",mysql_php($regs["data_vencimento"]));
	
	$resposta->addAssign("btninserir", "value", "Atualizar");

	$resposta->addEvent("btninserir", "onclick", "xajax_atualizar(xajax.getFormValues('frm_integracao'));");
	
	$resposta->addEvent("btnvoltar", "onclick", "xajax_voltar();");

	return $resposta;
}

function atualizar($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	$conf = new configs();
	
	$msg = $conf->msg($resposta);
	
	if($conf->checa_permissao(4,$resposta))
	{		
		if($dados_form["funcionario"]!='' && $dados_form["data_integracao"]!='' && $dados_form["data_vencimento"]!='' && $dados_form["local_trabalho"]!='')
		{
			$usql = "UPDATE ".DATABASE.".rh_integracao SET ";
			$usql .= "id_funcionario = '" . $dados_form["funcionario"] . "', ";
			$usql .= "id_local_trabalho = '" . $dados_form["local_trabalho"] . "', ";
			$usql .= "data_integracao = '" . php_mysql($dados_form["data_integracao"]) . "', ";
			$usql .= "vigencia = '" . $dados_form["vigencia"] . "', ";
			$usql .= "cracha = '" . $dados_form["cracha"] . "', ";
			$usql .= "observacoes = '" . $dados_form["observacoes"] . "', ";
			
			if(php_mysql($dados_form["data_vencimento"])>date("Y-m-d"))
			{
				$usql .= "vencimento = '0', ";
			}
			else
			{
				$usql .= "vencimento = '1', ";
			}
			
			$usql .= "data_vencimento = '" . php_mysql($dados_form["data_vencimento"]) . "' ";
			$usql .= "WHERE id_rh_integracao = '".$dados_form["id_integracao"]."' ";

			$db->update($usql,'MYSQL');

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}
			else
			{						
				$resposta->addScript("xajax_atualizatabela('');");
				
				$resposta->addScript("xajax_voltar();");
			
				$resposta->addAlert("Integração atualizada com sucesso.");
			}	
		}
		else
		{
			$resposta->addAlert("Os campos devem estar preenchidos.");
		
		}
	}
	
	return $resposta;
}

function excluir($id, $what = '')
{
	$resposta = new xajaxResponse();

	$db = new banco_dados;
	
	$conf = new configs();
	
	$msg = $conf->msg($resposta);

	if($conf->checa_permissao(2,$resposta))
	{	
		$usql = "UPDATE ".DATABASE.".rh_integracao SET ";
		$usql .= "reg_del = 1, ";
		$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
		$usql .= "data_del = '".date('Y-m-d')."' ";
		$usql .= "WHERE rh_integracao.id_rh_integracao = '".$id."' ";

		$db->update($usql,'MYSQL');

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
		else
		{	
			$resposta->addScript("xajax_atualizatabela('');");
			
			$resposta->addAlert("Registro excluido com sucesso.");
		}
	}
	
	return $resposta;
}

function calcula_vencimento($data,$vigencia=12)
{
	$resposta = new xajaxResponse();
	
	$resposta->addAssign("data_vencimento","value",calcula_data($data, "sum", "month", $vigencia));

	return $resposta;
}

$xajax->registerFunction("voltar");
$xajax->registerFunction("insere");
$xajax->registerFunction("editar");
$xajax->registerFunction("atualizar");
$xajax->registerFunction("excluir");
$xajax->registerFunction("atualizatabela");
$xajax->registerFunction("calcula_vencimento");

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
	
	function doOnRowSelected(id,ind) 
	{
		if(ind<=8)
		{
			xajax_editar(id);
			
			return true;
		}
		
		return false;
	}
	
	mygrid.enableAutoHeight(autoh,height);
	
	mygrid.attachEvent("onRowSelect", doOnRowSelected);
	
	mygrid.enableRowsHover(true,'cor_mouseover');

	mygrid.setHeader("Nº,Funcionário,Crachá,Local de Trabalho,Data Integração,Vencimento,Status,Observação,D",
		null,
		["text-align:left","text-align:left","text-align:left","text-align:left","text-align:left","text-align:left","text-align:left","text-align:left","text-align:center"]);
	mygrid.setInitWidths("50,250,*,*,*,*,80,*,30");
	mygrid.setColAlign("left,left,left,left,center,center,center,left,center");
	mygrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro");
	mygrid.setColSorting("str,str,str,str,str,str,str,str,str");
	
	mygrid.setSkin("dhx_skyblue");
	mygrid.enableMultiselect(true);
	mygrid.enableCollSpan(true);		
	mygrid.init();
	mygrid.loadXMLString(xml);

}

</script>

<?php
$conf = new configs();

$array_funcionario_values = NULL;
$array_funcionario_output = NULL;

$array_local_values = NULL;
$array_local_output = NULL;

$array_funcionario_values[] = "";
$array_funcionario_output[] = "SELECIONE";

$sql = "SELECT * FROM ".DATABASE.".funcionarios  ";
$sql .= "WHERE funcionarios.situacao NOT IN ('DESLIGADO','CANCELADO') ";
$sql .= "AND funcionarios.reg_del = 0 ";
$sql .= "ORDER BY funcionarios.funcionario ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}

foreach ($db->array_select as $cont)
{
	$array_funcionario_values[] = $cont["id_funcionario"];
	$array_funcionario_output[] = $cont["funcionario"];

}

$array_local_values[] = "";
$array_local_output[] = "SELECIONE";

$sql = "SELECT * FROM ".DATABASE.".local ";
$sql .= "WHERE local.reg_del = 0 ";
$sql .= "ORDER BY descricao ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}

foreach ($db->array_select as $cont)
{
	$array_local_values[] = $cont["id_local"];
	$array_local_output[] = $cont["descricao"];
}

$smarty->assign("revisao_documento","V5");

$smarty->assign("campo",$conf->campos('integracao_clientes'));

$smarty->assign("botao",$conf->botoes());						

$smarty->assign("option_funcionario_values",$array_funcionario_values);
$smarty->assign("option_funcionario_output",$array_funcionario_output);
$smarty->assign("option_local_values",$array_local_values);
$smarty->assign("option_local_output",$array_local_output);

$smarty->assign("data_integracao",date("d/m/Y"));

$smarty->assign("nome_formulario","INTEGRAÇÃO");

$smarty->assign("classe",CSS_FILE);

$smarty->display('integracao.tpl');
?>