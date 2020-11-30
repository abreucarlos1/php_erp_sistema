<?php
/*
		Formulario de Excessões de apontamentos	
		
		Criado por Carlos Eduardo Máximo
		
		local/Nome do arquivo:
		../ti/excessoes_apontamento.php
	
		Versão 0 --> VERSÃO INICIAL : 20/10/2015
		Versão 1 --> Inclusão dos campos reg_del nas consultas - 22/11/2017 - Carlos Abreu
		Versão 2 --> Layout responsivo - 22/11/2017 - Carlos Eduardo
*/	

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

require_once(INCLUDE_DIR."antiInjection.php");

require_once(INCLUDE_DIR."encryption.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(546))
{
	nao_permitido();
}

function atualiza_tabela($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados();
	
	$xml = new XMLWriter();
	
	$xml->openMemory();
	$xml->startElement('rows');
	
	$clausulaOS = '';
	
	$conteudo = "";
	
	if (!empty($dados_form['id_os']))
	{		
		$os = explode('_', $dados_form['id_os']);
		
		$clausulaOS = "AND id_os = '" . $os[0] . "' ";
	}
	
	$sql = "SELECT
				*
			FROM
				".DATABASE.".excessoes_calendario
				JOIN(
					SELECT id_funcionario, funcionario FROM ".DATABASE.".funcionarios WHERE funcionarios.situacao NOT IN('DESLIGADO') AND funcionarios.reg_del = 0
				) func
				ON func.id_funcionario = id_funcionario
				JOIN(
					SELECT os, id_os codOs, descricao FROM ".DATABASE.".ordem_servico WHERE ordem_servico.reg_del = 0 ".$clausulaOS ." 
				) os
				ON codOs = id_os
			WHERE 
				excessoes_calendario.reg_del = 0
			ORDER BY
				os ";
	
	$db->select($sql, 'MYSQL',true);

	foreach($db->array_select as $reg)
	{
		  $xml->startElement('row');
			  $xml->writeAttribute('id', $reg["id_exc_cal"]);
			  $xml->writeElement('cell', $reg["id_exc_cal"]);
			  $xml->writeElement('cell', $reg["os"].' - '.$reg['descricao']);
			  $xml->writeElement('cell', $reg["funcionario"]);
			  $xml->writeElement('cell', mysql_php($reg["inicio"]));
			  $xml->writeElement('cell', mysql_php($reg["fim"]));
			  $xml->writeElement('cell', sec_to_time($reg["hr_inicio"]));
			  $xml->writeElement('cell', sec_to_time($reg["hr_fim"]));
			  $xml->writeElement('cell', ($reg["intervalo"])/60);
			  $xml->writeElement('cell', '<img src="'.DIR_IMAGENS.'apagar.png" style="cursor:pointer;" onclick=if(confirm("Deseja&nbsp;excluir&nbsp;este&nbsp;item?")){xajax_excluir("'.$reg['id_exc_cal'].'")}; >');
		  $xml->endElement();			
	}
		
	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('excessoes', true, '450', '".$conteudo."');");
	
	return $resposta;
}

function funcionarios_os($dados_form, $idSelecionar)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados();
	
	$os = explode('_', $dados_form['id_os']);
	
	/*
	$sql = "SELECT
				DISTINCT AFA_RECURS, AFA_PROJET, MIN(AFA_START) AFA_START, MAX(AFA_FINISH) AFA_FINISH
			FROM
				AFA010 WITH (NOLOCK)
			WHERE
				D_E_L_E_T_ = ''
				AND AFA_PROJET = '".sprintf('%010d', $os[1])."'
			GROUP BY
				AFA_RECURS, AFA_PROJET
			ORDER BY
				AFA_RECURS";
	
	$resposta->addScript("combo_destino = document.getElementById('id_funcionario');");
	
	$resposta->addScriptCall('limpa_combo', 'id_funcionario');
	
	$resposta->addScript("combo_destino.options[combo_destino.length] = new Option('SELECIONE', '0');");
	
	$db->select($sql,'MSSQL',true);
	
	$array_recurso = $db->array_select;
	
	foreach($array_recurso as $reg)
	{
		$resposta->addAssign('data_inicio', 'value', mysql_php(protheus_mysql($reg['AFA_START'])));
		
		$resposta->addAssign('data_fim', 'value', mysql_php(protheus_mysql($reg['AFA_FINISH'])));
		
		$codFuncionario = intval(preg_replace("/[^0-9]/", "", $reg['AFA_RECURS']));	
		
		$sql = "SELECT DISTINCT 
					id_funcionario, funcionario
				FROM
					".DATABASE.".funcionarios
				WHERE
					funcionarios.id_funcionario = '" .$codFuncionario ."' AND funcionarios.situacao NOT IN ('DESLIGADO') AND funcionarios.reg_del = 0 
					#AND id_funcionario NOT IN(SELECT id_funcionario FROM ".DATABASE.".excessoes_calendario WHERE excessoes_calendario.reg_del = 0 AND excessoes_calendario.id_os = '" .$os[0] ."')";
		
		$db->select($sql, 'MYSQL', true);
		
		if ($db->numero_registros > 0)
		{
			$regs = $db->array_select[0];
			
			$resposta->addScript("combo_destino.options[combo_destino.length] = new Option('".$regs["funcionario"]."', '".$codFuncionario."',false,'".$idSelecionar."');");
		}
		
	}
	*/
	
	return $resposta;
}

function insere($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados();
	
	$idExcCal = $dados_form['id_exc_cal'];
	
	$os = explode('_', $dados_form['id_os']);
	
	$idFuncionario = $dados_form['id_funcionario'];
	
	$dataInicio	= !empty($dados_form['data_inicio']) ? php_mysql($dados_form['data_inicio']) : '';
	
	$dataFinal = !empty($dados_form['data_fim']) ? php_mysql($dados_form['data_fim']) : '';
	
	$horaInicio = time_to_sec(AntiInjection::clean($dados_form['hora_entrada']));
	
	$horaFinal = time_to_sec(AntiInjection::clean($dados_form['hora_saida']));
	
	$intervalo = ($dados_form["intervalo"]*60); //intervalo dado em minutos
	
	if (!empty($idFuncionario) && !empty($dataInicio) && !empty($dataFinal) && trim($horaInicio) != '' && trim($horaFinal) != '' && trim($intervalo) != '')
	{
		if (empty($idExcCal))
		{
			$isql = "INSERT INTO ".DATABASE.".excessoes_calendario(id_os, id_funcionario, inicio, fim, hr_inicio, hr_fim, intervalo)	VALUES ";
			$isql .= "('".$os[0]."','".$idFuncionario."','".$dataInicio."','".$dataFinal."','".$horaInicio."','".$horaFinal."','".$intervalo."') ";
			
			$db->insert($isql, 'MYSQL');
			
			if ($db->erro != '')
			{
				$resposta->addAlert('Houve uma falha ao tentar inserir o registro!');
				
				return $resposta;
			}
			
			$resposta->addAlert('Registro inserido corretamente!');

		}
		else
		{
			$usql = "UPDATE ".DATABASE.".excessoes_calendario	SET ";
			$usql .= "inicio = '".$dataInicio ."', ";
			$usql .= "fim = '".$dataFinal."', ";
			$usql .= "hr_inicio = '".$horaInicio."', ";
			$usql .= "hr_fim = '" . $horaFinal ."', ";
			$usql .= "intervalo = '" . $intervalo ."' ";
			$usql .= "WHERE	id_exc_cal = '".$idExcCal."' ";
			$usql .= "AND reg_del = 0 ";
			
			$db->update($usql, 'MYSQL');
			
			if ($db->erro != '')
			{
				$resposta->addAlert('Houve uma falha ao tentar alterar o registro!');
				
				return $resposta;
			}
			
			$resposta->addAlert('Registro alterado corretamente!');
		}
		
		$resposta->addScript('window.location = "./excessoes_apontamento.php"');
	}
	else
	{
		$resposta->addAlert('Todos os campos devem estar preenchidos!');
	}
	
	return $resposta;
}

function excluir($id)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados();
	
	$usql = "UPDATE ".DATABASE.".excessoes_calendario	SET ";
	$usql .= "reg_del = 1, ";
	$usql .= "reg_who = '".$_SESSION['id_funcionario']."', ";
	$usql .= "data_del = '".date('Y-m-d')."' ";
	$usql .= "WHERE	id_exc_cal = '".$id."' ";
	$usql .= "AND reg_del = 0 ";
	
	$db->update($usql, 'MYSQL');
	
	if ($db->erro != '')
	{
		$resposta->addAlert('Houve uma falha ao tentar excluir o registro!');
		
		return $resposta;
	}
	
	$resposta->addAlert('Registro excluido corretamente!');
	
	$resposta->addScript('xajax_funcionarios_os(xajax.getFormValues("frm"));');
	
	$resposta->addScript('xajax_atualiza_tabela(xajax.getFormValues("frm"));');
	
	return $resposta;
}

function editar($idExcCal)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados();
	
	$sql = "SELECT
				*
			FROM
				".DATABASE.".excessoes_calendario
				JOIN(
					SELECT id_os codOs, os FROM ".DATABASE.".ordem_servico WHERE ordem_servico.reg_del = 0
				) os
				ON os.codOs = id_os 
			WHERE reg_del = 0
				AND id_exc_cal = '".$idExcCal."' ";
				

	$db->select($sql,'MYSQL',true);
	
	$reg = $db->array_select[0];

	$resposta->addAssign('id_exc_cal', 'value', $reg['id_exc_cal']);
	
	$resposta->addAssign('data_inicio', 'value', mysql_php($reg['inicio']));
	$resposta->addAssign('data_fim', 'value', mysql_php($reg['fim']));
	
	$resposta->addAssign('hora_entrada', 'value', sec_to_time($reg['hr_inicio']));
	$resposta->addAssign('hora_saida', 'value', sec_to_time($reg['hr_fim']));
	
	$resposta->addAssign('intervalo', 'value', ($reg['intervalo']/60));
	
	$resposta->addScriptCall('seleciona_combo',$reg['id_os'].'_'.$reg['OS'], 'id_os');
	
	$resposta->addScript("xajax_funcionarios_os(xajax.getFormValues('frm'), '".$reg['id_funcionario']."');");
	
	$resposta->addAssign('btninserir', 'value', 'Alterar');
	
	$resposta->addEvent("btnvoltar","onclick","xajax_voltar();");	
	
	return $resposta;
}

function voltar()
{
	$resposta = new xajaxResponse();
	
	$resposta->addAssign("btninserir","value","Inserir");
	
	$resposta->addAssign('id_funcionario', 'innerHTML', '');
	
	$resposta->addAssign('id_exc_cal', 'value', '');
	
	$resposta->addScript('document.frm.reset();');
	
	$resposta->addEvent("btninserir","onclick","xajax_insere(xajax.getFormValues('frm'));");
	
	$resposta->addEvent("btnvoltar","onclick","history.back();");
	
	$resposta->addScript("xajax_atualiza_tabela(xajax.getFormValues('frm'));");
	
	return $resposta;
}

$xajax->registerFunction("atualiza_tabela");

$xajax->registerFunction("funcionarios_os");
$xajax->registerFunction("insere");
$xajax->registerFunction("excluir");
$xajax->registerFunction("editar");
$xajax->registerFunction("voltar");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","xajax_atualiza_tabela(xajax.getFormValues('frm'));");
?>
<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script language="javascript">
function grid(tabela, autoh, height, xml)
{
	mygrid = new dhtmlXGridObject(tabela);

	mygrid.enableAutoHeight(autoh,height);
	mygrid.enableRowsHover(true,'cor_mouseover');

	mygrid.setHeader("ID,OS,Funcionário,Inicio,Fim,Hora&nbsp;Ini,Hora&nbsp;Fim,Intervalo,D");
	mygrid.setInitWidths("30,*,205,70,70,70,70,70,40");
	mygrid.setColAlign("left,left,left,left,left,left,left,left,center");
	mygrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro");
	mygrid.setColSorting("str,str,str,str,str,str,str,str,str");

	function editar(id, col)
	{
		if (col <= 7)
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

$conf = new configs();

$db = new banco_dados();
		
$array_os_values = array();
$array_os_output = array();

$array_os_values[] = '';
$array_os_output[] = 'SELECIONE';

/*
$sql = "SELECT AF8_PROJET, AF8_REVISA, AF8_DESCRI FROM AF8010 WITH(NOLOCK) ";
$sql .= "WHERE AF8010.D_E_L_E_T_ = '' ";
$sql .= "AND AF8010.AF8_FASE IN ('03','09','07') ";//andamento e adm e sem crono OR AF8010.AF8_FASE = '09'
$sql .= "AND AF8010.AF8_PROJET > '0000003000' ";
$sql .= "GROUP BY AF8010.AF8_PROJET, AF8010.AF8_REVISA, AF8010.AF8_DESCRI  ";
$sql .= "ORDER BY AF8010.AF8_PROJET, AF8010.AF8_REVISA DESC  ";

$db->select($sql,'MSSQL',true);

$array_proj = $db->array_select;

foreach($array_proj as $res)
{
	$os = intval($res["AF8_PROJET"]);
	
	$sql = "SELECT id_os, OS FROM  ".DATABASE.".OS ";
	$sql .= "WHERE os.os = '". (string)$os."' ";
	$sql .= "AND OS.reg_del = 0 ";

	$db->select($sql,'MYSQL',true);
	
	foreach($db->array_select as $reg)
	{
		$array_os_values[] = $reg['id_os'].'_'.$reg['OS'];
		$array_os_output[] = intval($res["AF8_PROJET"])." - ".trim($res["AF8_DESCRI"]);
	}
}

*/

$smarty->assign("option_os_values",$array_os_values);
$smarty->assign("option_os_output",$array_os_output);

$checados = array(1 => "checked='checked'", 2 => "checked='checked'", 3 => "checked='checked'", 4 => "checked='checked'", 5 => "checked='checked'", 6 => "", 0 => "");

$smarty->assign("checked", $checados);

$smarty->assign("campo",$conf->campos('excessoes_apontamento'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("revisao_documento","V2");

$smarty->assign('larguraTotal', 1);

$smarty->assign("classe",CSS_FILE);

$smarty->display('excessoes_apontamento.tpl');
?>