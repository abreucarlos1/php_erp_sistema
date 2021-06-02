<?php
/*
		Formulário de Diario Projetos
		
		Criado por Carlos Abreu  
		
		local/Nome do arquivo:
		../qualidade/diario_projetos.php
		
		Versão 0 --> VERSÃO INICIAL : 29/08/2016 - Carlos Abreu
		Versão 1 --> Atualização layout - Carlos Abreu - 03/04/2017
*/	

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(582))
{
	nao_permitido();
}

function voltar()
{
	$resposta = new xajaxResponse();
	
	$resposta->addAssign("btninserir","disabled","true");
	
	return $resposta;
}

function atualizatabela($dados_form)
{
	$resposta = new xajaxResponse();
	
	$resposta->addScript('document.getElementById("itens").innerHTML = "";');
	
	$xml = new XMLWriter();
	
	$db = new banco_dados;
	
	$sql = "SELECT * FROM ".DATABASE.".diario_projeto, ".DATABASE.".diario_projeto_itens, ".DATABASE.".funcionarios, ".DATABASE.".setores ";
	$sql .= "WHERE diario_projeto.reg_del = 0 ";
	$sql .= "AND diario_projeto_itens.reg_del = 0 ";
	$sql .= "AND diario_projeto.id_diario_projeto = diario_projeto_itens.id_diario_projeto ";
	$sql .= "AND diario_projeto_itens.id_funcionario = funcionarios.id_funcionario ";
	$sql .= "AND diario_projeto_itens.id_setor = setores.id_setor ";
	$sql .= "AND diario_projeto.id_os = '".$dados_form["id_os"]."' ";
	
	$db->select($sql,'MYSQL', true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	
	if($db->numero_registros>0)
	{
		$resposta->addScript("myTabbar.tabs('a20_').enable();");
	}
	else
	{
		$resposta->addScript("myTabbar.tabs('a20_').disable();");
	}	

	$xml->openMemory();
	$xml->setIndent(false);
	$xml->startElement('rows');
	
	foreach($db->array_select as $regs)
	{
		$descricao = wordwrap($regs["descricao_item"],165,"\n");		
		
		$xml->startElement('row');
			$xml->writeAttribute('id', 'item_'.$regs["id_diario_projeto_item"]);
			
			$xml->startElement('cell');
				$xml->text(sprintf("%03d",$regs["numero_item"]));
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->writeAttribute('colspan',2);
				$xml->text(str_replace("\n","<br />",$descricao));
			$xml->endElement();

		$xml->endElement();
		
		$xml->startElement('row');
			$xml->writeAttribute('id', 'item1_'.$regs["id_diario_projeto_item"]);
			
			$xml->startElement('cell');
				$xml->text('<strong>data:</strong> '.mysql_php($regs["data_item"]));
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text('<strong>Autor:</strong> '.$regs["funcionario"]);
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text('<strong>setor:</strong> '.$regs["setor"]);
			$xml->endElement();
		
		$xml->endElement();
		
		$xml->startElement('row');
			$xml->startElement('cell');
				$xml->writeAttribute('colspan',3);
				$xml->writeAttribute('height','2px;');
				$xml->text(' ');
			$xml->endElement();
		$xml->endElement();
		
	}

	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('itens', true, '500', '".$conteudo."');");

	return $resposta;
}

function inserir($dados_form)
{
	$resposta = new xajaxResponse();
	
	$chars = array("'");
	
	$db = new banco_dados;
	
	if (empty($dados_form["id_os"]) || empty($dados_form["descricao_item"]))
	{
		$resposta->addAlert('Por favor, preencha todos os campos.');
	}
	else
	{
		//verifica se já tem OS cadastrada
		$sql = "SELECT id_setor FROM ".DATABASE.".funcionarios ";
		$sql .= "WHERE funcionarios.id_funcionario = '".$_SESSION["id_funcionario"]."' ";
		
		$db->select($sql,'MYSQL',true);

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
		
		$regs_funcionario = $db->array_select[0];
		
		//verifica se já tem OS cadastrada
		$sql = "SELECT * FROM ".DATABASE.".diario_projeto ";
		$sql .= "WHERE diario_projeto.reg_del = 0 ";
		$sql .= "AND diario_projeto.id_os = '".$dados_form["id_os"]."' ";

		$db->select($sql,'MYSQL',true);

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
		
		$regs_diario = $db->array_select[0];
		
		//se não tiver registro, cadastra
		if($db->numero_registros==0)
		{
			$isql = "INSERT INTO ".DATABASE.".diario_projeto ";
			$isql .= "(id_os) ";
			$isql .= "VALUES ('". $dados_form["id_os"]. "') ";

			$db->insert($isql,'MYSQL');
		
			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}
			
			$id_diario_projeto = $db->insert_id;
			
			$item = 1;			
		}
		else
		{
			$id_diario_projeto = $regs_diario["id_diario_projeto"];
			
			//verifica o item cadastrado
			$sql = "SELECT numero_item FROM ".DATABASE.".diario_projeto_itens ";
			$sql .= "WHERE diario_projeto_itens.reg_del = 0 ";
			$sql .= "AND diario_projeto_itens.id_diario_projeto = '".$id_diario_projeto."' ";
			$sql .= "ORDER BY diario_projeto_itens.numero_item DESC ";			
			$sql .= "LIMIT 1";
	
			$db->select($sql,'MYSQL',true);
	
			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}
			
			$regs_item = $db->array_select[0];
			
			$item = $regs_item["numero_item"] + 1;			
		}
		
		//insere o item
		$isql = "INSERT INTO ".DATABASE.".diario_projeto_itens ";
		$isql .= "(id_diario_projeto, numero_item, descricao_item, data_item, id_funcionario, id_setor) ";
		$isql .= "VALUES ('" . $id_diario_projeto . "', ";
		$isql .= "'" . $item . "', ";
		$isql .= "'" . maiusculas(addslashes(str_replace($chars,"",$dados_form["descricao_item"]))). "', ";
		$isql .= "'" . date('Y-m-d') . "', ";
		$isql .= "'" . $_SESSION["id_funcionario"] . "', ";
		$isql .= "'" . $regs_funcionario["id_setor"] . "') ";

		$db->insert($isql,'MYSQL');

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}		
		else
		{
			$resposta->addAlert('Registro incluido corretamente!');
			$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'), true)");	
		}	
	}
	
	$resposta->addAssign("descricao_item", "innerHTML", "");

	return $resposta;
}

$conf = new configs();

$xajax->registerFunction("atualizatabela");
$xajax->registerFunction("voltar");
$xajax->registerFunction("inserir");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","tab();");


$array_os_values = NULL;
$array_os_output = NULL;

$sql = "SELECT * FROM ".DATABASE.".ordem_servico, ".DATABASE.".ordem_servico_status ";
$sql .= "WHERE ordem_servico.id_os_status = ordem_servico_status.id_os_status ";
$sql .= "GROUP BY ordem_servico.id_os ";
$sql .= "ORDER BY ordem_servico.os ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	$resposta->addAlert($db->erro);
}

foreach($db->array_select as $cont)
{
	$array_os[$cont["id_os"]] = sprintf("%05d",$cont["os"]) . " - " . $cont["descricao"];
}

//Re-ordena as OS's
asort($array_os);

$array_os_values[] = "";
$array_os_output[] = "SELECIONE";

//Percorre o array de OS's
foreach($array_os as $chave=>$valor)
{
	$array_os_values[] = $chave;
	$array_os_output[] = $valor;
}

$smarty->assign("option_os_values",$array_os_values);
$smarty->assign("option_os_output",$array_os_output);

$smarty->assign("revisao_documento","V1");

$smarty->assign("campo",$conf->campos('diario_projetos'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("nome_funcionario",$_SESSION["nome_usuario"]);

$smarty->assign("classe",CSS_FILE);

$smarty->display('diario_projetos.tpl');

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script src="<?php echo INCLUDE_JS ?>datetimepicker/datetimepicker_css.js"></script>

<script>

function valida(id)
{
	if(this.value!=0)
	{
		document.getElementById('descricao_item').disabled=false;
		
		document.getElementById('btninserir').disabled=false;
		
		document.getElementById('btnexportar').disabled=false;
		
		myTabbar.tabs('a20_').enable();
	}
	else
	{
		document.getElementById('descricao_item').disabled=true;
		
		document.getElementById('btninserir').disabled=true;
		
		document.getElementById('btnexportar').disabled=true;
	}
		
}

function exportar()
{
	document.getElementById('frm').action = 'relatorios/rel_diario_projeto_excel.php';
	document.getElementById('frm').target = '_blank';
	document.getElementById('frm').submit();	
}


function tab()
{
	myTabbar = new dhtmlXTabBar("my_tabbar");
	
	function sel_tab(idNew,idOld)
	{		
		//ativa quando seleciona a tab		
		switch(idNew)
		{
			
			case "a20_":
				
				xajax_atualizatabela(xajax.getFormValues('frm',true));
				
			break;
		}
		
		return true; // allow selection	
	}
	
	myTabbar.attachEvent("onSelect", sel_tab);
	
	myTabbar.addTab("a10_", "Inclusão", null, null, true);
	myTabbar.addTab("a20_", "Visualização");

	myTabbar.tabs("a10_").attachObject("a10");
	myTabbar.tabs("a20_").attachObject("a20");
	
	myTabbar.enableAutoReSize(true);
	
	myTabbar.tabs('a20_').disable(true);
}

function grid(tabela, autoh, height, xml)
{
	mygrid = new dhtmlXGridObject(tabela);
	
	mygrid.enableAutoHeight(autoh,height);
	mygrid.enableRowsHover(true,'cor_mouseover');	
		
	mygrid.setHeader("Nº item,Descrição, ");
	mygrid.setInitWidths("100,*,250");
	mygrid.setColAlign("center,left,left");
	mygrid.setColTypes("ro,ro,ro");
	mygrid.setColSorting("str,str,str");
	
	mygrid.setSkin("dhx_skyblue");
    mygrid.enableMultiselect(true);
    mygrid.enableCollSpan(true);	
	mygrid.init();
	mygrid.loadXMLString(xml);
}

</script>