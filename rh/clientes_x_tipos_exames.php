<?php
/*
		Formulário de Exames
		
		Criado por Carlos Eduardo Máximo
		
		local/Nome do arquivo:
		../rh/clientes_x_tipos_exames.php
		
		Versão 0 --> VERSÃO INICIAL - 14/07/2016
		Versão 1 --> Atualização layout - Carlos Abreu - 04/04/2017
*/
	
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(570))
{
	nao_permitido();
}

function voltar()
{
	$resposta = new xajaxResponse();
	$resposta -> addScriptCall("reset_campos('frm_conhecimento')");
	$resposta -> addAssign("btninserir", "value", "Inserir");
	$resposta -> addEvent("btninserir", "onclick", "xajax_insere(xajax.getFormValues('frm_conhecimento'));");
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
		
		$sql_filtro = " WHERE empresa LIKE '".$sql_texto."' ";
		$sql_filtro = " OR (id_empresa LIKE '".$sql_texto."') ";	
	}
	
	$sql = 
		"SELECT DISTINCT id_empresa, empresa, descricao, unidade FROM ".DATABASE.".unidades, ".DATABASE.".empresas
		JOIN(
		SELECT
			*
		FROM
			".DATABASE.".tipos_exames_x_clientes
		WHERE
			tipos_exames_x_clientes.reg_del = 0
		) exames
		ON tec_cod_empresa = id_empresa ".$sql_filtro." 
		WHERE
			empresas.id_unidade = unidades.id_unidade";

	$db->select($sql,'MYSQL',true);

	$conteudo = "";
	
	$xml = new XMLWriter();
	$xml->openMemory();
	$xml->startElement('rows');
	
	$chars = array("'","\"",")","(","\\","/");
	
	foreach($db->array_select as $cont_desp)
	{
		$xml->startElement('row');
		$xml->writeAttribute('id', $cont_desp['id_empresa']);
		$xml->writeElement('cell', $cont_desp['empresa']. " - " . $cont_desp["descricao"] . " - " . $cont_desp["unidade"]);
		$xml->writeElement('cell', '<img src="'.DIR_IMAGENS.'apagar.png" style="cursor:pointer;" onclick=if(confirm("Deseja excluir a lista?")){xajax_excluir("'.$cont_desp["id_empresa"].'");}; >');
		$xml->endElement();
	}

	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('divLista', true, '400', '".$conteudo."');");
	$resposta->addScript("hideLoader();");
	
	return $resposta;
}

function insere($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados();
	
	if(!empty($dados_form["tipos_exames"]) && !empty($dados_form['cliente']))
	{
		//Exclui todos os exames do cliente para inserir novamente abaixo
		$usql = 
		"UPDATE 
			".DATABASE.".tipos_exames_x_clientes
			SET reg_del = 1, 
				reg_who = '".$_SESSION['id_funcionario']."', 
				data_del = '".date('Y-m-d')."'
			WHERE
				tec_cod_empresa = ".$dados_form['cliente'];
		
		$db->update($usql,'MYSQL');

		if($db->erro != '')
		{
			$resposta->addAlert('Houve uma falha ao tentar atualizar os registros.');
			return $resposta;
		}
		
		$isql = "INSERT INTO ".DATABASE.".tipos_exames_x_clientes
					(tec_cod_empresa, tec_id_aso_tipos_exames) VALUES ";
		foreach($dados_form['tipos_exames'] as $k => $value)
		{
			$isql .= $virgula."('".$dados_form['cliente']."', '".$value."') ";
			$virgula = ',';
		}
		
		$db->insert($isql, 'MYSQL');
		
		if($db->erro != '')
		{
			$resposta->addAlert('Houve uma falha ao tentar inserir os registros.');
			return $resposta;
		}
		
		$resposta->addAlert('Registros salvos corretamente!');
		$resposta->addScript("xajax_atualizatabela('');");
		$resposta->addScript("document.getElementById('frm').reset();");
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
	
	$db = new banco_dados();

	$sql = 
		"SELECT
			*
		FROM
			".DATABASE.".tipos_exames_x_clientes
		WHERE
			tipos_exames_x_clientes.tec_cod_empresa = '".$id."'
			AND tipos_exames_x_clientes.reg_del = 0 ";
	
	$resposta->addScript("desseleciona_combo('tipos_exames');");
	
	$db->select($sql,'MYSQL', true);
	
	$reg = $db->array_select[0];
	
	$resposta->addAssign("cliente", "value",$reg['tec_cod_empresa']);
	$resposta->addScript("seleciona_combo(".$reg['tec_id_aso_tipos_exames'].",'tipos_exames');");

	$resposta->addEvent("btnvoltar", "onclick", "xajax_voltar();");
	
	return $resposta;
}

function excluir($id)
{
	$resposta = new xajaxResponse();

	$db = new banco_dados;
		
	$usql = "UPDATE ".DATABASE.".tipos_exames_x_clientes
				SET reg_del = 1, 
				reg_who = '".$_SESSION['id_funcionario']."', 
				data_del = '".date('Y-m-d')."'
			WHERE tec_cod_empresa = '".$id."' ";
	
	$db->update($usql,'MYSQL');

	$resposta->addAlert("Registro excluido corretamente!");
	$resposta->addScript("xajax_atualizatabela('');");
	$resposta->addScript("document.getElementById('frm').reset();");

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

	mygrid.setHeader("Cliente, D");
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
$array_cliente_values[] = "0";
$array_cliente_output[] = "SELECIONE";
	  
$sql = "SELECT * FROM ".DATABASE.".empresas, ".DATABASE.".unidades ";
$sql .= "WHERE empresas.id_unidade = unidades.id_unidade ";
$sql .= "AND empresas.status = 'CLIENTE' ";
$sql .= "ORDER BY empresa ";

$db->select($sql,'MYSQL', true);

if ($db->erro != '')
{
	exit("Não foi possível realizar a seleção.".$sql);
}

foreach($db->array_select as $regs)
{
	$array_cliente_values[] = $regs["id_empresa"];
	$array_cliente_output[] = $regs["empresa"] . " - " . $regs["descricao"] . " - " . $regs["unidade"];	
}

$smarty->assign("option_cliente_values",$array_cliente_values);
$smarty->assign("option_cliente_output",$array_cliente_output);

$array_exames_values = array();
$array_exames_output = array();

$sql = "SELECT * FROM ".DATABASE.".rh_aso_tipos_exames ";
$sql .= "ORDER BY ordem ";

$db->select($sql,'MYSQL', true);

if ($db->erro != '')
{
	exit("Não foi possível realizar a seleção.".$sql);
}

foreach($db->array_select as $regs)
{
	$array_exames_values[] = $regs["id_aso_tipos_exames"];
	$array_exames_output[] = $regs["nome_exame"];
}

$smarty->assign("option_exames_values",$array_exames_values);
$smarty->assign("option_exames_output",$array_exames_output);

$smarty->assign('campo', $conf->campos('clientes_x_tipos_exames'));

$smarty->assign('revisao_documento', 'V1');

$smarty->assign("classe",CSS_FILE);

$smarty->display('clientes_x_tipos_exames.tpl');
?>