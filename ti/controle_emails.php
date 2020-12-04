<?php
/*
	Formul�rio de Controle de Emails
	Criado por Carlos Eduardo  
	
	local/Nome do arquivo: ../ti/controle_emails.php
	
	Versão 0 --> VERSÃO INICIAL - 03/10/2016
	Versão 1 --> Atualização layout - Carlos Abreu - 11/04/2017
	Versão 2 --> Inclusão dos campos reg_del nas consultas - 23/11/2017 - Carlos Abreu
	Versão 3 --> Layout responsivo - 05/02/2018 - Carlos Eduardo
*/

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));

require_once(INCLUDE_DIR."include_form.inc.php");

require_once(INCLUDE_DIR."antiInjection.php");

//previne contra acesso direto	
if(!verifica_sub_modulo(584))
{
    nao_permitido();
}

function voltar()
{
	$resposta = new xajaxResponse();
	
	$resposta->addScriptCall("reset_campos('frm')");
	
	$resposta->addAssign("btninserir", "value", "Inserir");

	$resposta->addEvent("btninserir", "onclick", "xajax_insere(xajax.getFormValues('frm'));");
	
	$resposta->addEvent("btnvoltar", "onclick", "javascript:location.href='menucadastros.php';");
	
	return $resposta;
}

function atualiza_tabela($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados();
	
	$sql = "SELECT * FROM ".DATABASE.".lista_emails ";
	$sql .= "WHERE lista_emails.reg_del = 0 ";
	$sql .= "ORDER BY lista_emails.le_nome ";
	
	$xml = new XMLWriter();
	
	$xml->setIndent(false);
	$xml->openMemory();
	$xml->startElement('rows');
	
	$chars = array("'","\"",")","(","\\","/");
	
	$db->select($sql,'MYSQL',true);
	
	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	
	foreach($db->array_select as $reg)
	{
		
		$xml->startElement('row');
			$xml->writeAttribute('id', $reg["le_id"]);
			$xml->writeElement('cell', $reg["le_uso"]);
			$xml->writeElement('cell', str_replace($chars, "", $reg["le_nome"]));
			$xml->writeElement('cell', $reg["le_email"]);
			$xml->writeElement('cell', $reg["le_tipo_envio"]);
			
			$img = '<img src="'.DIR_IMAGENS.'apagar.png" onclick=if(confirm("Deseja&nbsp;realmente&nbsp;excluir&nbsp;este&nbsp;item?"))xajax_excluir("'.$reg['le_id'].'"); style="cursor:pointer;" />';
			
			$xml->writeElement('cell', $img);
		$xml->endElement();		
	}
	
	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('divLista', true, '490', '".$conteudo."');");
	
	return $resposta;
}

function editar($leId, $valor, $colunaAlterada)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados();
	
	$colunas = array('le_uso','le_nome','le_email','le_tipo_envio');
	
	$usql = "UPDATE ".DATABASE.".lista_emails SET ".$colunas[$colunaAlterada]." = '".$valor."' ";
	$usql .= "WHERE le_id = ".$leId." ";
	$usql .= "AND reg_del = 0 ";
	
	$db->update($usql, 'MYSQL');

	if ($db->erro != '')
	{
		$resposta->addAlert('Houve uma falha ao tentar alterar o registro.');
	}
	else
	{
		$resposta->addAlert('Altera��o realizada corretamente!');
		
		$resposta->addScript('xajax_atualiza_tabela(xajax.getFormValues("frm"));');
	}
	
	return $resposta;
}

function insere($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados();
	
	$funcionario = explode('_', $dados_form['id_funcionario']);
	
	$uso = minusculas($dados_form['emailGrupo']);
	
	$nome = ucwords(strtolower($funcionario[0]));
	
	$email = ucwords(strtolower($funcionario[1]));
	
	$tipo = $dados_form['tipoEnvio'];
	
	$isql = "INSERT INTO ".DATABASE.".lista_emails (le_uso, le_nome, le_email, le_tipo_envio) VALUES ";
	$isql .= "('".$uso."', '".$nome."', '".$email."', '".$tipo."')";	
	
	$db->insert($isql, 'MYSQL');

	if ($db->erro != '')
	{
		$resposta->addAlert('Houve uma falha ao tentar alterar o registro.');
	}
	else
	{
		$resposta->addAlert('Altera��o realizada corretamente!');
		$resposta->addScript('window.location="./controle_emails.php"');
	}
	
	return $resposta;
}

function excluir($leId)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados();
	
	$usql = "UPDATE ".DATABASE.".lista_emails SET ";
	$usql .= "reg_del = 1, ";
	$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
	$usql .= "data_del = '".date('Y-m-d')."' ";
	$usql .= "WHERE le_id = ".$leId." ";
	$usql .= "AND reg_del = 0 ";
	
	$db->update($usql, 'MYSQL');

	if ($db->erro != '')
	{
		$resposta->addAlert('Houve uma falha ao tentar excluir o registro.');
	}
	else
	{
		$resposta->addAlert('Registro exclu�do corretamente!');
		$resposta->addScript('xajax_atualiza_tabela(xajax.getFormValues("frm"));');
	}
	
	return $resposta;
}

$xajax->registerFunction("voltar");
$xajax->registerFunction("atualiza_tabela");
$xajax->registerFunction("editar");
$xajax->registerFunction("insere");
$xajax->registerFunction("excluir");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","xajax_atualiza_tabela(xajax.getFormValues('frm'));");
?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script src="<?php echo INCLUDE_JS ?>jquery/jquery.min.js"></script>

<script language="javascript">
function grid(tabela, autoh, height, xml)
{
	mygrid = new dhtmlXGridObject(tabela);

	mygrid.enableAutoHeight(autoh,height);
	mygrid.enableRowsHover(true,'cor_mouseover');

	mygrid.setHeader("Grupo, funcionario, email, Envio, E");
	mygrid.setInitWidths("*,*,*,100,50");
	mygrid.setColAlign("left,left,left,left,center");
	mygrid.setColTypes("ed,ed,ed,ed,ro");
	
	mygrid.setColSorting("str,str,str,str,str");
	mygrid.enableEditEvents(true,true,true,true,false);

	//Editor usando a propria grid
	mygrid.attachEvent("onEditCell", function(stage,rId,cInd,nValue,oValue){
		if (stage == 2 && nValue != oValue)
		{
			if(confirm('Confirma a altera��o!'))
				xajax_editar(rId, nValue, cInd);
		}
	});

	//Filtro direto na grid
	mygrid.attachHeader("#text_filter,#text_filter,#text_filter,#text_filter");
	
	mygrid.setSkin("dhx_skyblue");
    mygrid.enableMultiselect(true);
    mygrid.enableCollSpan(true);	
	mygrid.init();
	
	mygrid.loadXMLString(xml);
}
</script>

<?php
$conf = new configs();

$array_func_values[] = "0";
$array_func_output[] = "SELECIONE";
	  
$sql = "SELECT
			funcionario, email
		FROM
			".DATABASE.".funcionarios
			JOIN(
				SELECT id_funcionario id_funcionario, email FROM ".DATABASE.".usuarios WHERE reg_del = 0
			) usuarios
			ON id_funcionario = id_funcionario
		WHERE
			situacao = 'ATIVO'
			AND reg_del = 0 
		ORDER BY
			funcionario";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	$resposta->addAlert($db->erro);
}

foreach($db->array_select as $regs)
{
	$array_func_values[] = $regs["funcionario"].'_'.$regs["email"];
	$array_func_output[] = $regs["funcionario"];
}

$smarty->assign("option_func_values",$array_func_values);
$smarty->assign("option_func_output",$array_func_output);

$smarty->assign("revisao_documento","V3");

$smarty->assign('larguraTotal', 1);

$smarty->assign("campo",$conf->campos('controle_emails'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("classe",CSS_FILE);

$smarty->display('controle_emails.tpl');
?>