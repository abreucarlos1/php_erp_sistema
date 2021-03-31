<?php
/*
		Formulário de sub_modulos	
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		../ti/sub_modulos.php
	
		Versão 0 --> VERSÃO INICIAL : 28/10/2008
		Versão 1 --> Atualização Lay-out : 06/04/2009
		Versão 2 --> Atualização classe banco de dados - 27/01/2015 - Carlos Abreu
		Versão 3 --> Atualização layout - Carlos Abreu - 11/04/2017
		Versão 4 --> Inclusão dos campos reg_del nas consultas - 23/11/2017 - Carlos Abreu
*/	

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(109))
{
	nao_permitido();
}

function voltar()
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$botao = $conf->botoes($resposta);

	$resposta -> addScriptCall("reset_campos('frm_sub_modulos')");
	
	$resposta->addAssign("btninserir", "value", $botao[1]);
	
	$resposta->addEvent("btninserir", "onclick", "xajax_insere(xajax.getFormValues('frm_sub_modulos'));");
	
	$resposta->addEvent("btnvoltar", "onclick", "history.back();");

	return $resposta;

}

function atualizatabela($filtro, $page = 0)
{
	$offset = 50;
	
	$limit = $page * $offset;
	
	$resposta = new xajaxResponse();
	
	$xml = new XMLWriter();
	
	$conf = new configs();
	
	$campos = $conf->campos('sub_modulos',$resposta);
	
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
		
		$sql_filtro = "AND (a.sub_modulo LIKE '".$sql_texto."' ";
		$sql_filtro .= "OR b.modulo LIKE '".$sql_texto."' ";
		$sql_filtro .= "OR a.caminho_sub_modulo LIKE '".$sql_texto."') ";
	}
	
	$sql = "SELECT a.*, b.modulo
			FROM ".DATABASE.".sub_modulos a ";
	$sql .= "LEFT JOIN ".DATABASE.".modulos b ON(b.id_modulo = a.id_modulo AND b.reg_del = 0 ) ";
	$sql .= "WHERE a.reg_del = 0 ";
	$sql .= $sql_filtro;
	$sql .= "ORDER BY a.id_sub_modulo DESC ";

	$conteudo = "";
	
	$xml->openMemory();
	$xml->setIndent(false);
	$xml->startElement('rows');
	
	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}
	
	$num_regs = $db->numero_registros;
	
	$array_sub = $db->array_select;
	
	foreach($array_sub as $cont_desp)
	{
		if($cont_desp["visivel"])
		{
			$cor = '#006600';
			$bk = '#ffffff';
		}
		else
		{
			$cor = '#FF0000';
			$bk = '#FF0000';	
		}

		switch ($cont_desp["target"])
		{
			case 1: $target = "OUTRA JANELA";
			break;
			
			case 2: $target = "OUTRA JANELA / TAM. DEF.";
			break;
			
			default : $target = "MESMA JANELA";
		}
		
		switch ($cont_desp["visivel"])
		{
			case 0: $visivel = "NÃO";
			break;
			
			case 1: $visivel = "SIM";
			break;
		}
		
		$padrao = $cont_desp['acesso_padrao'] == 1 ? 'SIM' : 'NÃO';
		$permissao = $cont_desp['codigo_acesso'];
		
		$sql = "SELECT sub_modulo FROM ".DATABASE.".sub_modulos ";
		$sql .= "WHERE sub_modulos.id_sub_modulo = '".$cont_desp["id_sub_modulo_pai"]."' ";
		$sql .= "AND sub_modulos.reg_del = 0 ";

		$db->select($sql,'MYSQL',true);
		
		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
			
			return $resposta;
		}
		
		$cont1 = $db->array_select[0];

		$xml->startElement('row');
			$xml->writeAttribute('style', 'background-color='.$bk);
		    $xml->writeAttribute('id',$cont_desp["id_sub_modulo"]);
			
			$xml->startElement('cell');
				$xml->text($cont_desp["modulo"]);
			$xml->endElement();

			$xml->startElement('cell');
				$xml->text($cont_desp["id_sub_modulo"]);
			$xml->endElement();
			
			$xml->startElement('cell');
				//$xml->writeAttribute('style', 'color='.$cor.';background-color='.$bk);
				$xml->text($cont_desp["sub_modulo"]);
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text($cont1["sub_modulo"]);
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text($cont_desp["caminho_sub_modulo"]);
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text($target);
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text($cont_desp["altura"]);
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text($cont_desp["largura"]);
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text($visivel);
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text($padrao);
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text($permissao);
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text('<img style="cursor:pointer;" src="'.DIR_IMAGENS.'apagar.png" onclick=if(apagar("'.$cont_desp["sub_modulo"].'")){xajax_excluir("'.$cont_desp["id_sub_modulo"].'","'.$cont_desp["sub_modulo"] . '");}>');
			$xml->endElement();

		$xml->endElement();
				
	}
	
	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('sub_modulos',true,'350','".$conteudo."');");
	
	return $resposta;
}

function insere($dados_form)
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$msg = $conf->msg($resposta);
	
	if($conf->checa_permissao(8,$resposta)) //id_sub_modulo sub-modulos = 109
	{	
		$db = new banco_dados;
		
		if($dados_form["sub_modulo"]!='' && $dados_form["modulo"]!='' && $dados_form["caminho"]!='')
		{
			$permissao 	= $dados_form["visualiza"] + $dados_form["inclui"] + $dados_form["edita"] +  $dados_form["apaga"] + $dados_form["imprime"];
		
			if (intval($permissao) == 0 && $dados_form['acesso_padrao'] == 1)
			{
				$resposta->addAlert('Por favor, preencha uma ou mais das opções de permissão!');
				return $resposta;
			}
			
			$sql = "SELECT * FROM ".DATABASE.".sub_modulos ";
			$sql .= "WHERE id_modulo = '".$dados_form["modulo"]."' ";
			$sql .= "AND sub_modulo = '".trim(maiusculas($dados_form["sub_modulo"]))."' ";
			$sql .= "AND id_sub_modulo_pai = '".$dados_form["sub_modulo_pai"]."' ";
			$sql .= "AND caminho_sub_modulo = '".$dados_form["caminho"]."' ";
			$sql .= "AND reg_del = 0 ";
			
			$db->select($sql,'MYSQL',true);
			
			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
				
				return $resposta;
			}
			
			if($db->numero_registros<=0)
			{			
				if ($dados_form['acesso_padrao'] == 0)
				{
					$permissao = 'null';
					$dados_form['tipo_acesso_padrao'] = 'null';
				}
				
				$isql = "INSERT INTO ".DATABASE.".sub_modulos ";
				$isql .= "(id_modulo, sub_modulo, id_sub_modulo_pai, caminho_sub_modulo, target, altura, largura, visivel, acesso_padrao, codigo_acesso, tipo_acesso_padrao) VALUES ( ";
				$isql .= "'" . $dados_form["modulo"] . "', ";
				$isql .= "'" . trim(maiusculas($dados_form["sub_modulo"])) . "', ";
				$isql .= "'" . $dados_form["sub_modulo_pai"] . "', ";
				$isql .= "'" . $dados_form["caminho"] . "', ";
				$isql .= "'" . $dados_form["target"] . "', ";
				$isql .= "'" . $dados_form["altura"] . "', ";
				$isql .= "'" . $dados_form["largura"] . "', ";
				$isql .= "'" . $dados_form["visivel"] . "', ";
				$isql .= "'".$dados_form['acesso_padrao']."', ";
				$isql .= $permissao.", ";
				$isql .= $dados_form['tipo_acesso_padrao'].") ";
		
				$db->insert($isql,'MYSQL');
				
				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
					
					return $resposta;
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
	
	$botao = $conf->botoes($resposta);
	
	$msg = $conf->msg($resposta);

	$db = new banco_dados;
	
	$sql = "SELECT * FROM ".DATABASE.".sub_modulos ";
	$sql .= "WHERE sub_modulos.id_sub_modulo = '".$id."' ";
	$sql .= "AND reg_del = 0 ";
	
	$db->select($sql,'MYSQL',true);
	
	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}

	$regs = $db->array_select[0];
	
	$resposta->addAssign("id_sub_modulo", "value",$id);
	
	$resposta->addScript("seleciona_combo(".$regs["id_modulo"].",'modulo');");
	
	$resposta->addScript("seleciona_combo(".$regs["id_sub_modulo_pai"].",'sub_modulo_pai');");
	
	$resposta->addScript("seleciona_combo(".$regs["visivel"].",'visivel');");

	$resposta->addAssign("sub_modulo", "value",$regs["sub_modulo"]);
	
	$resposta->addAssign("caminho", "value",$regs["caminho_sub_modulo"]);
	
	$resposta->addScript("seleciona_combo(".$regs["target"].",'target');");
	
	$resposta->addAssign("altura", "value",$regs["altura"]);
	
	$resposta->addAssign("largura", "value",$regs["largura"]);
	
	$resposta->addAssign("btninserir", "value", $botao[3]);
	
	$resposta->addScript("seleciona_combo(".$regs["acesso_padrao"].",'acesso_padrao');");
	
	//Permissoes
	$array_permissao['V'] = $regs["codigo_acesso"] & 16 ? 'true':'false';
	$array_permissao['I'] = $regs["codigo_acesso"] & 8 ? 'true':'false';
	$array_permissao['E'] = $regs["codigo_acesso"] & 4 ? 'true':'false';
	$array_permissao['A'] = $regs["codigo_acesso"] & 2 ? 'true':'false';	
	$array_permissao['P'] = $regs["codigo_acesso"] & 1 ? 'true':'false';
	
	$resposta->addScript("frm_sub_modulos.visualiza.checked = ".$array_permissao['V']);
	$resposta->addScript("frm_sub_modulos.inclui.checked = ".$array_permissao['I']);
	$resposta->addScript("frm_sub_modulos.edita.checked = ".$array_permissao['E']);
	$resposta->addScript("frm_sub_modulos.apaga.checked = ".$array_permissao['A']);
	$resposta->addScript("frm_sub_modulos.imprime.checked = ".$array_permissao['P']);

	$resposta->addScript("seleciona_combo(".$regs["tipo_acesso_padrao"].",'tipo_acesso_padrao');");
	$resposta->addScript("mostraCamposAcessoPadrao(".$regs['acesso_padrao'].");");
		
	$resposta->addEvent("btninserir", "onclick", "xajax_atualizar(xajax.getFormValues('frm_sub_modulos'));");

	$resposta->addEvent("btnvoltar", "onclick", "xajax_voltar();");
	
	return $resposta;
}

function atualizar($dados_form)
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$msg = $conf->msg($resposta);
	
	if($conf->checa_permissao(4,$resposta)|| TRUE) //id_sub_modulo sub-modulos = 109
	{	
		$db = new banco_dados;
		
		if($dados_form["sub_modulo"]!='' && $dados_form["modulo"]!='' && $dados_form["caminho"]!='')
		{
			$permissao 	= $dados_form["visualiza"] + $dados_form["inclui"] + $dados_form["edita"] +  $dados_form["apaga"] + $dados_form["imprime"];
		
			if (intval($permissao) == 0 && $dados_form['acesso_padrao'] == 1)
			{
				$resposta->addAlert('Por favor, preencha uma ou mais das opções de permissão!');
				return $resposta;
			}
			
			$sql = "SELECT * FROM ".DATABASE.".sub_modulos ";
			$sql .= "WHERE id_modulo = '".$dados_form["modulo"]."' ";
			$sql .= "AND sub_modulo = '".trim(maiusculas($dados_form["sub_modulo"]))."' ";
			$sql .= "AND id_sub_modulo_pai = '".$dados_form["sub_modulo_pai"]."' ";
			$sql .= "AND caminho_sub_modulo = '".$dados_form["caminho"]."' ";
			$sql .= "AND id_sub_modulo <> '".$dados_form["id_sub_modulo"]."' ";
			$sql .= "AND reg_del = 0 ";
			
			$db->select($sql,'MYSQL',true);
			
			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
				
				return $resposta;
			}
			
			if($db->numero_registros<=0)
			{
				if ($dados_form['acesso_padrao'] == 0)
				{
					$permissao = 'null';
					$dados_form['tipo_acesso_padrao'] = 'null';
				}
				
				$usql = "UPDATE ".DATABASE.".sub_modulos SET ";
				$usql .= "sub_modulo = '" . trim(maiusculas($dados_form["sub_modulo"])) . "', ";
				$usql .= "id_modulo = '" . $dados_form["modulo"] . "', ";
				$usql .= "id_sub_modulo_pai = '" . $dados_form["sub_modulo_pai"] . "', ";
				$usql .= "target = '" . $dados_form["target"] . "', ";
				$usql .= "altura = '" . $dados_form["altura"] . "', ";
				$usql .= "largura = '" . $dados_form["largura"] . "', ";
				$usql .= "visivel = '" . $dados_form["visivel"] . "', ";
				$usql .= "caminho_sub_modulo = '" . $dados_form["caminho"] . "', ";
				$usql .= "acesso_padrao = '".$dados_form['acesso_padrao']."', ";
				$usql .= "codigo_acesso = ".$permissao.", ";
				$usql .= "tipo_acesso_padrao = ".$dados_form['tipo_acesso_padrao']." ";
				$usql .= "WHERE id_sub_modulo = '".$dados_form["id_sub_modulo"]."' ";
				$usql .= "AND reg_del = 0 ";

				$db->update($usql,'MYSQL');
				
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

	if($conf->checa_permissao(2,$resposta)) //id_sub_modulo sub-modulos = 109
	{
		$db = new banco_dados;
		
		$usql = "UPDATE ".DATABASE.".sub_modulos SET ";
		$usql .= "reg_del = 1, ";
		$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
		$usql .= "data_del = '".date('Y-m-d')."' ";
		$usql .= "WHERE sub_modulos.id_sub_modulo = '".$id."' ";
		$usql .= "AND reg_del = 0 ";
		
		$db->update($usql,'MYSQL');

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
	mygrid = new dhtmlXGridObject(tabela);
	
	function editar(id, col)
	{
		if (col <= 4)
		{
			xajax_editar(id);
		}
	}
	
	mygrid.attachEvent("onRowSelect",editar);
	
	mygrid.enableAutoHeight(autoh,height);
	
	mygrid.enableRowsHover(true,'cor_mouseover');

	mygrid.setHeader("Módulo,Id,Sub-módulo,Sub-pai,Caminho,Target,Alt,Larg,Vis,Padrão,Perm,D",
		null,
		["text-align:left","text-align:left","text-align:left","text-align:left","text-align:left","text-align:left","text-align:left","text-align:left","text-align:left","text-align:center","text-align:center","text-align:center"]);
	mygrid.setInitWidths("90,40,200,150,200,100,30,45,35,40,40,25");
	mygrid.setColAlign("left,left,left,left,left,left,left,left,left,center,center,center");
	mygrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
	mygrid.setColSorting("str,str,str,str,str,str,str,str,str,str,str,str");
	
	mygrid.setSkin("dhx_skyblue");
	mygrid.enableMultiselect(true);
	mygrid.enableCollSpan(true);		
	mygrid.init();
	//mygrid.enableSmartRendering(true,32);
	mygrid.loadXMLString(xml);
}

function mostraCamposAcessoPadrao(padrao)
{
	if (padrao == 1)
	{
		document.getElementById('td_modulo_padrao_1').style.display = '';
		document.getElementById('td_modulo_padrao_2').style.display = '';
		document.getElementById('visualiza').checked = true;
		document.getElementById('inclui').checked = true;
		document.getElementById('edita').checked = true;
		document.getElementById('apaga').checked = true;
		document.getElementById('imprime').checked = true;
	}
	else
	{
		document.getElementById('td_modulo_padrao_1').style.display = 'none';
		document.getElementById('td_modulo_padrao_2').style.display = 'none';
		document.getElementById('visualiza').checked = false;
		document.getElementById('inclui').checked = false;
		document.getElementById('edita').checked = false;
		document.getElementById('apaga').checked = false;
		document.getElementById('imprime').checked = false;
	}
}
</script>

<?php

$conf = new configs();

$smarty->assign("campo",$conf->campos('sub_modulos'));

$smarty->assign("botao",$conf->botoes());

$msg = $conf->msg();

$array_modulo_values = NULL;
$array_modulo_output = NULL;

$array_sub_modulo_values = NULL;
$array_sub_modulo_output = NULL;


$array_modulo_values[] = "0";
$array_modulo_output[] = "SELECIONE";

$array_sub_modulo_values[] = "0";
$array_sub_modulo_output[] = "SELECIONE";

$db = new banco_dados;

$sql = "SELECT * FROM ".DATABASE.".modulos ";
$sql .= "WHERE reg_del = 0 ";
$sql .= "ORDER BY modulo ";

$db->select($sql,'MYSQL',true);

foreach ($db->array_select as $regs)
{
	$array_modulo_values[] = $regs["id_modulo"];
	$array_modulo_output[] = $regs["modulo"];
}

$sql = "SELECT * FROM ".DATABASE.".sub_modulos ";
$sql .= "WHERE reg_del = 0 ";
$sql .= "ORDER BY id_sub_modulo_pai";

$db->select($sql,'MYSQL',true);

$array_sub = $db->array_select; 

foreach ($array_sub as $regs)
{
	$sql = "SELECT * FROM ".DATABASE.".sub_modulos ";
	$sql .= "WHERE sub_modulos.id_sub_modulo = '".$regs["id_sub_modulo_pai"]."' ";
	$sql .= "AND reg_del = 0 ";
	$sql .= "ORDER BY sub_modulo ";
	
	$db->select($sql,'MYSQL',true);
	
	$regs1 = $db->array_select[0];
	
	$array_sub_modulo_values[] = $regs["id_sub_modulo"];
	$array_sub_modulo_output[] = $regs1["sub_modulo"]." - ".$regs["sub_modulo"];
}

$smarty->assign("option_modulo_values",$array_modulo_values);
$smarty->assign("option_modulo_output",$array_modulo_output);

$smarty->assign("option_sub_modulo_values",$array_sub_modulo_values);
$smarty->assign("option_sub_modulo_output",$array_sub_modulo_output);

$smarty->assign("revisao_documento","V4");

$smarty->assign("classe",CSS_FILE);

$smarty->display('sub_modulos.tpl');

?>