<?php
/*
	Formulário de ASO	
	
	Criado por Carlos Abreu / Otávio Pamplona
	
	local/Nome do arquivo:
	../rh/pcmso.php
	
	Versão 0 --> VERSÃO INICIAL - 28/01/2008
	Versão 1 --> Atualização Lay-out : 13/08/2008
	Versão 2 --> Atualização rotinas : 30/11/2011
	Versão 3 --> Atualização classe banco de dados - 23/01/2015 - Carlos Abreu
	Versão 4 --> Atualização Layout - 14/04/2015 - Eduardo
	Versão 5 --> Atualização layout - Carlos Abreu - 07/04/2017
	Versão 6 --> Inclusão dos campos reg_del nas consultas - 28/11/2017 - Carlos Abreu
*/
header('X-UA-Compatible: IE=edge');
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

require_once(INCLUDE_DIR."antiInjection.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(96))
{
	nao_permitido();
}

$conf = new configs();

function voltar()
{
	$resposta = new xajaxResponse();

	$resposta->addScriptCall("reset_campos('frm_aso')");
	
	$resposta->addAssign("data_exame", "value", date('d/m/Y'));
	
	$resposta->addAssign("vigencia", "value", "");
	
	$resposta->addAssign("btninserir", "value", "Inserir");
	
	$resposta->addAssign("btninserir", "disabled", "");
	
	$resposta->addEvent("btninserir", "onclick", "xajax_insere(xajax.getFormValues('frm_aso'));");
	
	$resposta->addEvent("btnvoltar", "onclick", "history.back();");

	return $resposta;

}

function atualizatabela($filtro, $dados_form)
{
	$resposta = new xajaxResponse();

	$db = new banco_dados;

	$sql_filtro = "";
	
	$sql_texto = "";	
	
	if($filtro!="")
	{
		$sql_texto = str_replace('  ', ' ', AntiInjection::clean($filtro));
		$sql_texto = str_replace(' ', '%', '%'.$sql_texto.'%');
		
		$sql_filtro = " AND (funcionarios.funcionario LIKE '".$sql_texto."' ";
		$sql_filtro .= " OR rh_aso.id_rh_aso LIKE '".$sql_texto."') ";
	}
	
	$sql = "SELECT * FROM ".DATABASE.".funcionarios, ".DATABASE.".rh_aso ";
	$sql .= "WHERE rh_aso.id_funcionario = funcionarios.id_funcionario ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	$sql .= "AND rh_aso.reg_del = 0 ";
	$sql .= "AND funcionarios.situacao NOT IN ('DESLIGADO','CANCELADO') ";
	$sql .= $sql_filtro;
	
	if(strlen($dados_form["exibir"])>0)
	{
		$sql .= "AND rh_aso.realizado = '".$dados_form["exibir"]."' ";
	}
	
	$sql .= "ORDER BY rh_aso.data_vencimento, funcionarios.funcionario ";	
	
	$db->select($sql,'MYSQL',true);

	$xml = new XMLWriter();
	$xml->openMemory();
	$xml->startElement('rows');
	
	foreach($db->array_select as $cont_desp)
	{
		$tipo_exame = "";
		
		switch($cont_desp["tipo_exame"])
		{
			case '1':
				$tipo_exame = 'ADMISSIONAL';
			break;
			case '2':
				$tipo_exame = 'PERIÓDICO';
			break;
			case '3':
				$tipo_exame = 'PERIÓDICO/AUDIOMÉTRICO';
			break;
			case '4':
				$tipo_exame = 'MUDANÇA DE FUNÇÃO';
			break;
			case '5':
				$tipo_exame = 'DEMISSIONAL';
			break;
			case '6':
				$tipo_exame = 'RETORNO AO TRABALHO';
			break;		
		}
		
		$status = "";
		
		if($cont_desp["realizado"]=="0")
		{
			$status = 'NÃO REALIZADO';
		}
		else
		{
			$status = 'REALIZADO';
		}
		
		if(($cont_desp["data_vencimento"]<=date("Y-m-d"))&&($cont_desp["realizado"]=="0"))
		{
			$cor = "cor_9";
		}
		else
		{
			$cor = "";
		}
		
		$xml->startElement('row');
		$xml->writeAttribute('class', $cor);
		$xml->writeAttribute('id', $cont_desp['id_rh_aso']);
		$xml->writeElement('cell', $cont_desp["id_rh_aso"]);
		$xml->writeElement('cell', $cont_desp['funcionario']);
		$xml->writeElement('cell', $tipo_exame);
		$xml->writeElement('cell', mysql_php($cont_desp["data_exame"]));
		$xml->writeElement('cell', mysql_php($cont_desp["data_vencimento"]));
		$xml->writeElement('cell', $status);
		
		if($cont_desp["realizado"]=="0")
		{
			$xml->writeElement('cell', '<img src="'.DIR_IMAGENS.'aprovado.png" style="cursor:pointer;" onclick=if(confirm("Deseja marcar este registro como realizado?")){xajax_realizado("'.$cont_desp["id_rh_aso"].'")};>');
			$xml->writeElement('cell', '<img src="'.DIR_IMAGENS.'apagar.png" style="cursor:pointer;" onclick=if(confirm("Deseja excluir este registro?")){xajax_excluir("'.$cont_desp['id_rh_aso'].'")};>');
		}
		else
		{
			$xml->writeElement('cell', '<img src="'.DIR_IMAGENS.'bt_desfazer.png" style="cursor:pointer;" onclick=if(confirm("Deseja marcar este registro como NÃO realizado?")){xajax_realizado("'.$cont_desp["id_rh_aso"].'",0)};>');
			$xml->writeElement('cell', ' ');
		}
		$xml->endElement();		
	}
	$xml->endElement();

	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('aso', true, '340', '".$conteudo."');");
	$resposta->addScript("hideLoader();");
	
	return $resposta;
}

function insere($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	if($dados_form["funcionario"]!='' && $dados_form["data_exame"]!='' && $dados_form["data_vencimento"]!='' && $dados_form["exame"]!='' && $dados_form["vigencia"]!='')
	{
		
		$id_aso = "";
		
		//modificado em 30/11/2011
		$isql = "INSERT INTO ".DATABASE.".rh_aso ";
		$isql .= "(id_funcionario, tipo_exame, data_exame, vigencia, data_vencimento, data_mudanca_status) ";
		$isql .= "VALUES ('" . $dados_form["funcionario"] . "', ";
		$isql .= "'" . $dados_form["exame"] . "', ";
		$isql .= "'" . php_mysql($dados_form["data_exame"]) . "', ";
		$isql .= "'" . $dados_form["vigencia"] . "', ";
		$isql .= "'" . php_mysql($dados_form["data_vencimento"]) . "', ";
		$isql .= "'" . date("Y-m-d") . "') ";

		$db->insert($isql,'MYSQL');
		
		$id_aso = $db->insert_id;
		
		$sql = "SELECT * FROM ".DATABASE.".rh_aso_tipos_exames ";
		$sql .= "WHERE rh_aso_tipos_exames.reg_del = 0 ";
		$sql .= "ORDER BY ordem ";
		
		$db->select($sql,'MYSQL',true);
		
		foreach ($db->array_select as $cont)
		{
			if($dados_form["chk_".$cont["id_aso_tipos_exames"]]==1)
			{
				$isql = "INSERT INTO ".DATABASE.".rh_aso_exames ";
				$isql .= "(id_aso, id_tipo_exame) ";
				$isql .= "VALUES (".$id_aso.",".$cont["id_aso_tipos_exames"].") ";
				
				$db->insert($isql,'MYSQL'); 
	
			}
		}
			
		$resposta->addScript("showLoader();xajax_atualizatabela('',xajax.getFormValues('frm_aso'));");
		
		$resposta->addAlert("Exame cadastrado com sucesso.");			

	}
	else
	{
		$resposta->addAlert("Os campos devem estar preenchidos.");
	}
	
	$resposta->addScript('xajax_voltar();');	

	return $resposta;
}

function editar($id)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
		
	$sql = "SELECT * FROM ".DATABASE.".rh_aso ";
	$sql .= "WHERE rh_aso.id_rh_aso = '".$id."' ";
	$sql .= "AND reg_del = 0 ";
	
	$db->select($sql,'MYSQL',true);

	$regs = $db->array_select[0];	
	
	$resposta->addScript("setcheckbox('frm_aso','');");
	
	$resposta->addScript("seleciona_combo(".$regs["id_funcionario"].",'funcionario');");
	
	$resposta->addAssign("data_exame", "value",mysql_php($regs["data_exame"]));
	
	$resposta->addAssign("vigencia", "value",$regs["vigencia"]);
	
	$resposta->addAssign("data_vencimento", "value",mysql_php($regs["data_vencimento"]));
	
	$sql = "SELECT * FROM ".DATABASE.".rh_aso_exames, ".DATABASE.".rh_aso_tipos_exames ";
	$sql .= "WHERE rh_aso_exames.id_aso = '".$regs["id_rh_aso"]."' ";
	$sql .= "AND rh_aso_exames.reg_del = 0 ";
	$sql .= "AND rh_aso_tipos_exames.reg_del = 0 ";
	$sql .= "AND rh_aso_exames.id_tipo_exame = rh_aso_tipos_exames.id_aso_tipos_exames ";
	
	$db->select($sql,'MYSQL',true);
	
	foreach ($db->array_select as $cont)
	{
		if ($cont["id_aso_tipos_exames"])
		{
			$script = "document.getElementById('chk_".$cont["id_aso_tipos_exames"]."').checked = true;";
			$resposta->addScript($script);
		}
	}
	
	$index = $regs["tipo_exame"]-1;
	
	$resposta->addScript('document.getElementsByName("exame")['.$index.'].checked=true');

	$resposta->addAssign("btninserir", "disabled", "true");
	
	$resposta->addEvent("btnvoltar", "onclick", "xajax_voltar();");

	return $resposta;

}

function realizado($id, $realizado=1)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
		
	$usql = "UPDATE ".DATABASE.".rh_aso SET ";
	$usql .= "data_mudanca_status = ".date("Y-m-d").", ";
	$usql .= "realizado = '" . $realizado . "' ";
	$usql .= "WHERE id_rh_aso = '".$id."' ";

	$db->update($usql,'MYSQL');
	
	$resposta->addScript("showLoader();xajax_atualizatabela(xajax.$('busca').value,xajax.getFormValues('frm_aso'));");
	
	return $resposta;	

}

function excluir($id)
{
	$resposta = new xajaxResponse();

	$db = new banco_dados;
	
	$usql = "UPDATE ".DATABASE.".rh_aso SET ";
	$usql .= "reg_del = 1, ";
	$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
	$usql .= "data_del = '".date('Y-m-d')."' ";
	$usql .= "WHERE rh_aso.id_rh_aso = '".$id."' ";

	$db->update($usql,'MYSQL');
	
	$usql = "UPDATE ".DATABASE.".rh_aso_exames SET ";
	$usql .= "reg_del = 1, ";
	$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
	$usql .= "data_del = '".date('Y-m-d')."' ";
	$usql .= "WHERE rh_aso_exames.id_aso = '".$id."' ";

	$db->update($usql,'MYSQL');

	$resposta->addScript("showLoader();xajax_atualizatabela('');");
	
	$resposta->addAlert("Registro excluido corretamente!");
	
	return $resposta;
}

//function calcula_vencimento($data,$vigencia=12)
function calcula_vencimento($data,$vigencia)
{
	$resposta = new xajaxResponse();
	
	if($vigencia!='')
	{
		$resposta->addAssign("data_vencimento","value",calcula_data($data, "sum", "month", $vigencia));
	}
	else
	{
		$resposta->addAlert('Favor preencher a vigência.');	
	}
	
	return $resposta;
}

$xajax->registerFunction("voltar");
$xajax->registerFunction("insere");
$xajax->registerFunction("editar");
$xajax->registerFunction("realizado");
$xajax->registerFunction("excluir");
$xajax->registerFunction("atualizatabela");
$xajax->registerFunction("calcula_vencimento");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","showLoader();xajax_atualizatabela('',xajax.getFormValues('frm_aso'));");

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script language="javascript">

function grid(tabela, autoh, height, xml)
{
	mygrid = new dhtmlXGridObject(tabela);

	mygrid.enableAutoHeight(autoh,height);
	mygrid.enableRowsHover(true,'cor_mouseover');

	mygrid.setHeader("Nº Exame, Funcionário, Tipo de Exame, Data do Exame, Data do Vencimento, Status, R, D");
	mygrid.setInitWidths("110,*,*,100,140,140,50,50");
	mygrid.setColAlign("left,left,left,left,center,center,center,center");
	mygrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro");
	mygrid.setColSorting("str,str,str,str,str,str,str,str");

	function editar(id, col)
	{
		if (col <= 5)
			xajax_editar(id);
	}
	
	mygrid.attachEvent("onRowSelect",editar);

	mygrid.setSkin("dhx_skyblue");
	mygrid.enableMultiselect(true);
	mygrid.enableCollSpan(true);
	mygrid.init();
	mygrid.loadXMLString(xml);
}

function abreJanela()
{
	var html = '<form id="frmExames" action="./relatorios/rel_exames_periodo_excel.php" target="_blank" method="post">'+
					'<label class="labels" style="float:left; width: 100px;">Data Inicio: </label><input class="caixa" type="text" name="data_inicio" id="data_inicio" onKeyPress="transformaData(this, event);" /><br />'+
					'<label class="labels" style="float:left; width: 100px;">Data Fim: </label><input class="caixa" type="text" name="data_fim" id="data_fim" onKeyPress="transformaData(this, event);" /><br />'+
					'<input type="submit" value="Gerar Relatório" class="class_botao" />';

	modal(html, '', 'Relatório de exames');
}

function realizado(texto)
{
	if(confirm('Deseja marcar como realizado o registro '+texto+'?'))
	{
		return true;
	}
	else
	{
		return false;
	} 
}

</script>

<?php

$array_funcionario_values = NULL;
$array_funcionario_output = NULL;

$array_funcionario_values[] = "";
$array_funcionario_output[] = "SELECIONE";

$sql = "SELECT * FROM ".DATABASE.".funcionarios ";
$sql .= "WHERE funcionarios.situacao NOT IN ('DESLIGADO','CANCELADO') ";
$sql .= "AND funcionarios.reg_del = 0 ";
$sql .= "ORDER BY funcionarios.funcionario ";

$db->select($sql,'MYSQL',true);

foreach ($db->array_select as $cont)
{
	$array_funcionario_values[] = $cont["id_funcionario"];
	$array_funcionario_output[] = $cont["funcionario"];

}

$sql = "SELECT * FROM ".DATABASE.".rh_aso_tipos_exames ";
$sql .= "WHERE reg_del = 0 ";
$sql .= "ORDER BY ordem ";

$db->select($sql,'MYSQL',true);

$exames = "<table width=\"100%\">";

$i=0;

foreach ($db->array_select as $cont)
{
	
	if(!$i%2)
	{
		$exames .= "<tr>";	
	}
		
	$exames .= "<td><input type=\"checkbox\" name=\"chk_".$cont["id_aso_tipos_exames"]."\" id=\"chk_".$cont["id_aso_tipos_exames"]."\" value=\"1\" /><label class=\"labels\">".$cont["nome_exame"]."</label></td>";

	if($i%2)
	{
		$exames .= "</tr>";	
	}
	
	$i++;
}

$exames .= "</table>";

$smarty->assign("option_funcionario_values",$array_funcionario_values);
$smarty->assign("option_funcionario_output",$array_funcionario_output);

$smarty->assign("exames",$exames);
$smarty->assign("data_exame",date("d/m/Y"));

$smarty->assign('campo', $conf->campos('pcmso'));

$smarty->assign('revisao_documento', 'V6');

$smarty->assign("classe",CSS_FILE);

$smarty->display('pcmso.tpl');
?>