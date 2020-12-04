<?php 
/*
		Formulário de rotinas x manutencoes
		
		Criado por Carlos Abreu  
		
		local/Nome do arquivo:
		../ti/rotinas_manutencoes.php
	
		Versão 0 --> VERSÃO INICIAL - 24/02/2014
		Versão 1 --> Atualização layout - Carlos Abreu - 11/04/2017
		Versão 2 --> Inclusão dos campos reg_del nas consultas - Carlos Abreu - 13/11/2017
		Versão 3 --> Inclusão dos campos reg_del nas consultas - 23/11/2017 - Carlos Abreu
		Versão 4 --> Layout responsivo - 05/02/2018 - Carlos Eduardo
*/
	
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(322))
{
	nao_permitido();
}


function voltar()
{
	$resposta = new xajaxResponse();
	
	$resposta->addAssign("btninserir","value","Inserir");
	
	$resposta->addEvent("btninserir","onclick","xajax_insere(xajax.getFormValues('frm')); ");
	
	$resposta->addEvent("btnvoltar", "onclick", "history.back();");
	
	return $resposta;

}

function atualizatabela($dados_form)
{
	$resposta = new xajaxResponse();
	
	$xml = new XMLWriter();
	
	semana_ini_fim($dados_form["semana"],$data_ini,$datafim);
	
	$conf = new configs();
	
	$msg = $conf->msg($resposta);
	
	$campos = $conf->campos('ti_rotinas_manutencoes',$resposta);
	
	$db = new banco_dados;	

	$sql = "SELECT * FROM ".DATABASE.".ti_rotinas_manutencoes, ".DATABASE.".ti_rotinas, ".DATABASE.".ti_rotinas_frequencias, ".DATABASE.".ti_frequencias, ".DATABASE.".funcionarios ";
	$sql .= "WHERE ti_rotinas_manutencoes.reg_del = 0 ";
	$sql .= "AND ti_rotinas.reg_del = 0 ";
	$sql .= "AND ti_rotinas_frequencias.reg_del = 0 ";
	$sql .= "AND ti_frequencias.reg_del = 0 ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	$sql .= "AND ti_rotinas_manutencoes.id_ti_rotina = ti_rotinas.id_ti_rotina ";
	$sql .= "AND ti_rotinas_manutencoes.id_ti_analista = funcionarios.id_funcionario ";
	$sql .= "AND funcionarios.id_funcionario = '".$dados_form["analista"]."' ";
	$sql .= "AND ti_rotinas_manutencoes.ti_data_manutencao BETWEEN '".php_mysql($data_ini)."' AND '".php_mysql($datafim)."' ";
	$sql .= "AND ti_rotinas.id_ti_rotina = ti_rotinas_frequencias.id_ti_rotina ";
	$sql .= "AND ti_rotinas_frequencias.id_ti_frequencia = ti_frequencias.id_ti_frequencia ";
	$sql .= "ORDER BY funcionario, ti_data_manutencao, ti_rotina ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}

	$conteudo = "";
	
	$xml->openMemory();
	$xml->setIndent(false);
	$xml->startElement('rows');
	
	foreach($db->array_select as $regs)
	{
		$xml->startElement('row');
		    $xml->writeAttribute('id',$regs["id_ti_rotina_manutencao"]);
			
			$xml->startElement('cell');
				$xml->text(mysql_php($regs["ti_data_manutencao"]));
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text(mysql_php($regs["ti_data_previsao"]));
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text($regs["funcionario"]);
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text($regs["ti_rotina"]);
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text($regs["ti_frequencia"]);
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text($regs["ti_manutencao_observacao"]);
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text('<img style="cursor:pointer;" src="'.DIR_IMAGENS.'apagar.png" onclick=xajax_excluir("'.$regs["id_ti_rotina_manutencao"].'");>');
			$xml->endElement();
		
		$xml->endElement();	
		
	}
	
	$xml->endElement();	
	
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('dv_rotinas',true,'420','".$conteudo."');");

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
		
		if($dados_form["data"]!='' && $dados_form["cb_rotinas"]!='' && $dados_form["analista"]!='')
		{
			
			$sql = "SELECT * FROM ".DATABASE.".ti_rotinas_manutencoes ";
			$sql .= "WHERE ti_rotinas_manutencoes.id_ti_analista = '".$dados_form["analista"]."' ";
			$sql .= "AND ti_rotinas_manutencoes.id_ti_rotina = '".$dados_form["cb_rotinas"]."' ";
			$sql .= "AND ti_rotinas_manutencoes.ti_data_manutencao = '".php_mysql($dados_form["data"])."' ";
			$sql .= "AND ti_rotinas_manutencoes.reg_del = 0 ";

			$db->select($sql,'MYSQL',true);

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}			
			
			if($db->numero_registros<=0)
			{
				$sql = "SELECT * FROM ".DATABASE.".ti_rotinas_frequencias, ".DATABASE.".ti_frequencias ";
				$sql .= "WHERE ti_rotinas_frequencias.reg_del = 0 ";
				$sql .= "AND ti_frequencias.reg_del = 0 ";
				$sql .= "AND ti_rotinas_frequencias.id_ti_rotina = '".$dados_form["cb_rotinas"]."' ";
				$sql .= "AND ti_rotinas_frequencias.id_ti_frequencia = ti_frequencias.id_ti_frequencia ";

				$db->select($sql,'MYSQL',true);

				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
					
					return $resposta;
				}
				
				$regs = $db->array_select[0];
							
				$isql = "INSERT INTO ".DATABASE.".ti_rotinas_manutencoes ";
				$isql .= "(id_ti_rotina, ti_data_manutencao, ti_data_inclusao, ti_data_previsao, ti_manutencao_observacao, id_ti_analista) ";
				$isql .= "VALUES ('" . $dados_form["cb_rotinas"] . "', ";
				$isql .= "'" . php_mysql($dados_form["data"]) . "', ";
				$isql .= "'" . date('Y-m-d') . "', ";
				$isql .= "'" . php_mysql(calcula_data($dados_form["data"], "sum", "day", $regs["ti_frequencia_dias"])) . "', ";
				$isql .= "'" . maiusculas($dados_form["observacao"]) . "', ";
				$isql .= "'" . $dados_form["analista"] . "') ";

				$db->insert($isql,'MYSQL');

				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
				}				
					
				$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'));");
				
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

function excluir($id)
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$msg = $conf->msg($resposta);

	//if($conf->checa_permissao(2,$resposta))
	//{
		$db = new banco_dados;
		
		$usql = "UPDATE ".DATABASE.".ti_rotinas_manutencoes SET ";
		$usql .= "ti_rotinas_manutencoes.reg_del = 1, ";
		$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
		$usql .= "data_del = '".date('Y-m-d')."' ";
		$usql .= "WHERE ti_rotinas_manutencoes.id_ti_rotina_manutencao = '".$id."' ";
		$usql .= "AND reg_del = 0 ";
		
		$db->update($usql,'MYSQL');

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
		else
		{
			$resposta->addAlert($what . $msg[3]);	
		}

		$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'));");
	//}

	return $resposta;
}

function rotinas($dados_form)
{
	$resposta = new xajaxResponse();	
	
	$resposta->addScript("limpa_combo('cb_rotinas');");
	
	$resposta->addScript("combo_destino = document.getElementById('cb_rotinas');");	
	
	$db = new banco_dados;		
	
	//Percorre a tabela de rotinas
	$sql = "SELECT * FROM ".DATABASE.".ti_rotinas, ".DATABASE.".ti_rotinas_frequencias, ".DATABASE.".ti_frequencias, ".DATABASE.".ti_rotinas_analistas ";	
	$sql .= "WHERE ti_rotinas.id_ti_rotina = ti_rotinas_analistas.id_ti_rotina ";
	$sql .= "AND ti_rotinas.reg_del = 0 ";
	$sql .= "AND ti_rotinas_frequencias.reg_del = 0 ";
	$sql .= "AND ti_rotinas_analistas.reg_del = 0 ";
	$sql .= "AND ti_frequencias.reg_del = 0 ";	
	$sql .= "AND ti_rotinas_analistas.id_ti_analista = ".$dados_form["analista"]." ";
	$sql .= "AND ti_rotinas.id_ti_rotina = ti_rotinas_frequencias.id_ti_rotina ";
	$sql .= "AND ti_rotinas_frequencias.id_ti_frequencia = ti_frequencias.id_ti_frequencia ";	
	$sql .= "ORDER BY ti_frequencia, ti_rotina ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}
	
	foreach($db->array_select as $cont)
	{
		$resposta->addScript("combo_destino.options[combo_destino.length] = new Option('".$cont["ti_frequencia"]." - ".$cont["ti_rotina"]."', '".$cont["id_ti_rotina"]."');");
	}	
	
	return $resposta;
}

$xajax->registerFunction("voltar");
$xajax->registerFunction("insere");
$xajax->registerFunction("atualizatabela");
$xajax->registerFunction("excluir");
$xajax->registerFunction("rotinas");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","xajax_atualizatabela(xajax.getFormValues('frm'));");

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>datetimepicker/datetimepicker_css.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script language="javascript">

//captura a tecla enter
document.onkeypress = keyhandler;

function keyhandler(e) 
{
	if (document.layers)
		Key = e.which;
	else
		Key = window.event.keyCode;

	if (Key != 0)
		if (Key == 13)
			//alert('Enter key press');
			xajax_insere(xajax.getFormValues('frm'));
}

function grid(tabela, autoh, height, xml)
{	
	mygrid = new dhtmlXGridObject(tabela);
	
	mygrid.enableAutoHeight(autoh,height);
	
	mygrid.enableRowsHover(true,'cor_mouseover');

	mygrid.setHeader("Data,Prox.&nbsp;data,Funcionário,Rotina,Frequência,Observação,D",
		null,
		["text-align:left","text-align:left","text-align:left","text-align:left","text-align:left","text-align:left","text-align:center"]);
	mygrid.setInitWidths("100,100,200,200,200,*,25");
	mygrid.setColAlign("left,left,left,left,left,left,center");
	mygrid.setColTypes("ro,ro,ro,ro,ro,ro,ro");
	mygrid.setColSorting("str,str,str,str,str,str,str");
	
	mygrid.setSkin("dhx_skyblue");
	mygrid.enableMultiselect(true);
	mygrid.enableCollSpan(true);		
	mygrid.init();
	//mygrid.enableSmartRendering(true,32);
	mygrid.loadXMLString(xml);

}

</script>

<?php

$array_analistas_values = NULL;
$array_analistas_output = NULL;

$array_analistas_values[] = "0";
$array_analistas_output[] = "SELECIONE";

$conf = new configs();

$db = new banco_dados;	

//Percorre a tabela de analistas
$sql = "SELECT * FROM ".DATABASE.".funcionarios, ".DATABASE.".ti_rotinas_analistas ";	
$sql .= "WHERE funcionarios.id_funcionario = ti_rotinas_analistas.id_ti_analista ";
$sql .= "AND ti_rotinas_analistas.reg_del = 0 ";
$sql .= "AND funcionarios.reg_del = 0 ";

if(!in_array($_SESSION["id_funcionario"],array(6,978)))
{ 
	$sql .= "AND ti_rotinas_analistas.id_ti_analista = '".$_SESSION["id_funcionario"]."' ";
}

$sql .= "GROUP BY funcionarios.funcionario ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}

foreach($db->array_select as $cont)
{
	$array_analistas_values[] = $cont["id_funcionario"];		
	$array_analistas_output[] = $cont["funcionario"];
}

$smarty->assign("revisao_documento","V4");

$smarty->assign('larguraTotal', 1);

$smarty->assign("campo",$conf->campos('ti_rotinas_manutencoes'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("nome_formulario","ROTINAS X MANUTENÇÕES");

$smarty->assign("option_analistas_values",$array_analistas_values);
$smarty->assign("option_analistas_output",$array_analistas_output);

$smarty->assign("classe",CSS_FILE);

$smarty->display('rotinas_manutencoes.tpl');

?>