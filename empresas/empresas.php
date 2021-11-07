<?php
/*
		Formulário de empresas	
		
		Criado por Carlos Abreu
		
		local/Nome do arquivo:
		../empresas/empresas.php
		
		Versão 0 --> VERSÃO INICIAL - 20/03/2007
		Versão 1 --> Atualização Lay-out / Smarty : 27/06/2008
		Versão 2 --> Atualização Layout: 23/12/2014
		Versão 3 --> Atualização classe banco - 20/01/2015 - Carlos Abreu
		Versão 4 --> atualização layout - Carlos Abreu - 27/03/2017
		Versão 5 --> Inclusão dos campos reg_del nas consultas - 20/11/2017 - Carlos Abreu		
*/

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(121))
{
	nao_permitido();
}

function voltar()
{
	$resposta = new xajaxResponse();
	
	$resposta->addScript("xajax.$('frm_empresas').reset(); ");
	
	$resposta->addAssign("btninserir","value","Inserir");
	
	$resposta->addEvent("frm_empresas", "onsubmit", "xajax.upload('insere','frm_empresas');");
	
	$resposta->addEvent("btnvoltar", "onclick", "history.back();");
	
	return $resposta;
}

function atualizatabela($filtro,$status='')
{
	$resposta = new xajaxResponse();

	$db = new banco_dados;
	
	$sql_filtro = "";
	
	$sql_texto = "";
	
	$exibir = "";
	
	if($filtro!="")
	{
		$array_valor = explode(" ",$filtro);
		
		for($x=0;$x<count($array_valor);$x++)
		{
			$sql_texto .= "%" . $array_valor[$x] . "%";
		}
		
		$sql_filtro = " AND (empresas.empresa LIKE '".$sql_texto."' ";
		$sql_filtro .= " OR empresas.abreviacao LIKE '".$sql_texto."' ";
		$sql_filtro .= " OR empresas.cidade LIKE '".$sql_texto."' ";
		$sql_filtro .= " OR empresas.estado LIKE '".$sql_texto."' ";
		$sql_filtro .= " OR empresas.status LIKE '".$sql_texto."' ";
		$sql_filtro .= " OR unidades.unidade LIKE '".$sql_texto."' ";
		$sql_filtro .= " OR unidades.descricao LIKE '".$sql_texto."') ";
	}	
	
	if($status!="")
	{
			$exibir = "AND empresas.status = '".$status."' ";
	}
	
	$sql = "SELECT * FROM ".DATABASE.".empresas ";
	$sql .= "LEFT JOIN ".DATABASE.".unidades ON (empresas.id_unidade = unidades.id_unidade AND unidades.reg_del = 0) ";
	$sql .= "WHERE empresas.reg_del = 0 ";
	$sql .= $sql_filtro;
	$sql .= $exibir;
	$sql .= " ORDER BY empresa ";
	
	$db->select($sql,'MYSQL',true);
	
	if ($db->erro != '')
	{
		$resposta->addAlert("Não foi possível a seleção dos dados".$sql);
	}
	
	$array_empresas = $db->array_select;

	$conteudo = "";
	
	$xml = new XMLWriter();
	
	$xml->openMemory();
	$xml->setIndent(false);
	$xml->startElement('rows');
	
	foreach($array_empresas as $cont_desp)
	{
		$xml->startElement('row');
			$xml->writeAttribute('id', $cont_desp["id_empresa"]);
			$xml->writeElement('cell', $cont_desp["empresa"].'-'.$cont_desp["unidade"]);
			$xml->writeElement('cell', $cont_desp["abreviacao"]);
			$xml->writeElement('cell', $cont_desp["telefone"]);
			$xml->writeElement('cell', $cont_desp["status"]);
			$xml->writeElement('cell', ' ');
		$xml->endElement();
	}
	
	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("seleciona_combo('" . $status . "','exibir'); ");

	$resposta->addScript("grid('empresas', true, '260', '".$conteudo."');");
	
	return $resposta;
}

function insere($dados_form)
{
	$resposta = new xajaxResponse();

	$db = new banco_dados;
	
	if($dados_form["empresa"]!='' && $dados_form["unidade"]!='')
	{

		if ($_FILES["logotipo"]["name"] !== '')
		{
			//faz upload do arquivo de logotipo, mostra mensagem caso ocorra algum erro.			
			move_uploaded_file($_FILES["logotipo"]["tmp_name"],'../logotipos/'.$_FILES["logotipo"]["name"]);
			
			$logotipo = "../logotipos/".$_FILES["logotipo"]["name"];
		}
		else 
		{
			$logotipo = $dados_form["logotipoatual"];
		}
		
		if($logotipo=='')
		{
			$logotipo = "../logotipos/ndisp.jpg";
		}	
		
		$isql = "INSERT INTO ".DATABASE.".empresas ";
		$isql .= "(empresa, id_unidade, id_segmento, status, relevancia, abreviacao, endereco, bairro, cep, cidade, estado, telefone, fax, homepage, logotipo) ";
		$isql .= "VALUES ('" . maiusculas($dados_form["empresa"]) . "', ";
		$isql .= "'" . $dados_form["unidade"] . "', ";
		$isql .= "'" . $dados_form["atuacao"] . "', ";
		$isql .= "'" . $dados_form["status"] . "', ";
		$isql .= "'" . $dados_form["relevancia"] . "', ";
		$isql .= "'" . maiusculas($dados_form["abreviacao"]) . "', ";
		$isql .= "'" . maiusculas($dados_form["endereco"]) . "', ";
		$isql .= "'" . maiusculas($dados_form["bairro"]) . "', ";
		$isql .= "'" . $dados_form["cep"] . "', ";
		$isql .= "'" . maiusculas($dados_form["cidade"]) . "', ";
		$isql .= "'" . $dados_form["estado"] . "', ";
		$isql .= "'" . $dados_form["telefone"] . "', ";
		$isql .= "'" . $dados_form["fax"] . "', ";
		$isql .= "'" . minusculas($dados_form["homepage"]) . "', ";	
		$isql .= "'" . $logotipo . "') ";

		$registros = $db->insert($isql,'MYSQL');
		
		if ($db->erro != '')
		{
			$resposta->addAlert("Não foi possível a inserção dos dados".$isql);
		}
	}
	else
	{
		$resposta->addAlert("Os campos devem estar preenchidos.");
	}

	$resposta->addScript("xajax_voltar('');");

	$resposta->addScript("xajax_atualizatabela('');");

	$resposta->addAlert("Empresa cadastrada com sucesso.");	

	return $resposta;
}

function editar($id)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	$sql = "SELECT * FROM ".DATABASE.".empresas ";
	$sql .= "WHERE empresas.id_empresa = '".$id."' ";
	$sql .= "AND empresas.reg_del = 0 ";
	
	$db->select($sql,'MYSQL',true);
	
	if ($db->erro != '')
	{
		$resposta->addAlert("Não foi possível fazer a seleção." . $sql);
	}
	
	$regs = $db->array_select[0];
	
	$resposta->addScript("seleciona_combo('" . $regs["id_unidade"] . "','unidade'); ");

	$resposta->addScript("seleciona_combo('" . $regs["id_segmento"] . "','atuacao'); ");
	
	$resposta->addScript("seleciona_combo('" . $regs["estado"] . "','estado'); ");

	$resposta->addScript("seleciona_combo('" . $regs["status"] . "','status'); ");
	
	$resposta->addScript("seleciona_combo('" . $regs["relevancia"] . "','relevancia'); ");
	
	$resposta->addAssign("id_empresa", "value",$regs["id_empresa"]);
	
	$resposta->addAssign("empresa", "value",$regs["empresa"]);
	
	$resposta->addAssign("abreviacao", "value",$regs["abreviacao"]);
	
	$resposta->addAssign("endereco", "value",$regs["endereco"]);
	
	$resposta->addAssign("bairro", "value",$regs["bairro"]);
	
	$resposta->addAssign("cidade", "value",$regs["cidade"]);
	
	$resposta->addAssign("cep", "value",$regs["cep"]);
	
	$resposta->addAssign("telefone", "value",$regs["telefone"]);
	
	$resposta->addAssign("fax", "value",$regs["fax"]);
	
	$resposta->addAssign("homepage", "value",$regs["homepage"]);
	
	$resposta->addAssign("logotipo", "value",$regs["logotipo"]);
	
	$resposta->addAssign("logotipoatual", "value",$regs["logotipo"]);
	
	$resposta->addAssign("btninserir", "value", "Atualizar");
	
	$resposta->addEvent("btnvoltar", "onclick", "xajax_voltar();");
	
	$resposta->addEvent("frm_empresas", "onsubmit", "xajax.upload('atualizar','frm_empresas');");
	
	return $resposta;	
}

function atualizar($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	if($dados_form["empresa"]!='' && $dados_form["unidade"]!='')
	{
		if ($_FILES["logotipo"]["name"] !== '')
		{
			//faz upload do arquivo de logotipo, mostra mensagem caso ocorra algum erro.			
			move_uploaded_file($_FILES["logotipo"]["tmp_name"],'../logotipos/'.$_FILES["logotipo"]["name"]);
			
			$logotipo = "../logotipos/".$_FILES["logotipo"]["name"];
		}
		else 
		{
			$logotipo = $dados_form["logotipoatual"];
		}
		
		if($logotipo=='')
		{
			$logotipo = "../logotipos/ndisp.jpg";
		}	
		
		$usql = "UPDATE ".DATABASE.".empresas SET ";
		$usql .= "abreviacao = '" . maiusculas($dados_form["abreviacao"]) . "', ";
		$usql .= "id_segmento = '" . $dados_form["atuacao"] . "', ";
		$usql .= "homepage = '" . $dados_form["homepage"] . "', ";
		$usql .= "logotipo = '" . $logotipo . "', ";
		$usql .= "relevancia = '" . $dados_form["relevancia"] . "', ";
		$usql .= "status = '" . $dados_form["status"] . "' ";
		$usql .= "WHERE id_empresa = '".$dados_form["id_empresa"]."' ";
		$usql .= "AND reg_del = 0 ";

		$db->update($usql,'MYSQL');
		
		if ($db->erro != '')
		{
			$resposta->addAlert("Não foi possível a atualização dos dados".$db->erro);
		}

		$resposta->addScript("xajax_voltar('');");			

		$resposta->addScript("xajax_atualizatabela('','".$dados_form["exibir"]."');");
		
		$resposta->addScript("document.getElementById('aguarde').style.display = 'none';");

	}
	else
	{
		$resposta->addAlert("Os campos devem estar preenchidos.");
	}
	
	return $resposta;
}

$conf = new configs();

$db = new banco_dados;

$xajax->registerFunction("voltar");
$xajax->registerFunction("insere");
$xajax->registerFunction("editar");
$xajax->registerFunction("atualizar");
$xajax->registerFunction("atualizatabela");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","xajax_atualizatabela('','CLIENTE');");
?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script>


function grid(tabela, autoh, height, xml)
{
	mygrid = new dhtmlXGridObject(tabela);

	mygrid.enableAutoHeight(autoh,height);
	mygrid.enableRowsHover(true,'cor_mouseover');

	mygrid.setHeader("Empresa,Abreviação,telefone,status");
	mygrid.setInitWidths("350,*,*,*");
	mygrid.setColAlign("left,left,center,center");
	mygrid.setColTypes("ro,ro,ro,ro");
	mygrid.setColSorting("str,str,str,str");

	mygrid.attachEvent("onRowSelect",'xajax_editar');
	
	mygrid.setSkin("dhx_skyblue");
    mygrid.enableMultiselect(true);
    mygrid.enableCollSpan(true);	
	mygrid.init();
	mygrid.loadXMLString(xml);
}

function visualizar(id_empresa)
{
	caminho = 'visualizar_empresa.php?id_empresa='+id_empresa+'';
	
	nome = 'detalhes empresa';
	
	windows = window.open(caminho);
}

</script>

<?php
$array_unidade_values = NULL;
$array_unidade_output = NULL;

$array_atuacao_values = NULL;
$array_atuacao_output = NULL;

$array_exibir_values = NULL;
$array_exibir_output = NULL;

$array_unidade_values[] = "";
$array_unidade_output[] = "SELECIONE";

$array_atuacao_values[] = "";
$array_atuacao_output[] = "SELECIONE";

$sql = "SELECT * FROM ".DATABASE.".unidades ";
$sql .= "WHERE unidades.reg_del = 0 ";
$sql .= "ORDER BY descricao ";

$db->select($sql,'MYSQL',true);

if ($db->erro != '')
{
	exit("Não foi possível realizar a seleção. ".$sql);
}

foreach ($db->array_select as $regs)
{
	$array_unidade_values[] = $regs["id_unidade"];
	$array_unidade_output[] = $regs["descricao"]." - ".$regs["unidade"];

}

$sql = "SELECT * FROM ".DATABASE.".segmentos ";
$sql .= "WHERE segmentos.reg_del = 0 ";
$sql .= "ORDER BY segmento ";

$db->select($sql,'MYSQL',true);

if ($db->erro != '')
{
	exit("Não foi possível realizar a seleção. ".$sql);
}

foreach ($db->array_select as $regs)
{
	$array_atuacao_values[] = $regs["id_segmento"];
	$array_atuacao_output[] = $regs["segmento"];
}

$array_exibir_values[] = "";
$array_exibir_output[] = "TODOS";

$sql = "SELECT status FROM ".DATABASE.".empresas ";
$sql .= "WHERE empresas.reg_del = 0 ";
$sql .= "GROUP BY status ";
$sql .= "ORDER BY status ";

$db->select($sql,'MYSQL',true);

if ($db->erro != '')
{
	exit("Não foi possível realizar a seleção. ".$sql);
}

foreach ($db->array_select as $regs)
{
	$array_exibir_values[] = $regs["status"];
	$array_exibir_output[] = $regs["status"];
}

$smarty->assign("option_unidade_values",$array_unidade_values);
$smarty->assign("option_unidade_output",$array_unidade_output);

$smarty->assign("option_atuacao_values",$array_atuacao_values);
$smarty->assign("option_atuacao_output",$array_atuacao_output);

$smarty->assign("option_exibir_values",$array_exibir_values);
$smarty->assign("option_exibir_output",$array_exibir_output);

$smarty->assign('revisao_documento', 'V5');

$smarty->assign('campo', $conf->campos('empresas'));

$smarty->assign("botao", $conf->botoes());

$smarty->assign("classe",CSS_FILE);

$smarty->assign("larguraTotal",1);

$smarty->display('empresas.tpl');
?>