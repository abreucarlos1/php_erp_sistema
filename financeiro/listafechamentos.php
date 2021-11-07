<?php
/*
	Listagem de Fechamentos	
	
	Criado por Carlos Abreu
	
	local/Nome do arquivo:
	../financeiro/listafechamentos.php
	
	Versão 0 --> VERSÃO INICIAL - 30/05/2006
	Versão 1 --> Atualização para o TAP de alteração dos fechamentos
	Versão 2 --> Atualização Layout - 14/07/2016 - Carlos Abreu
	Versão 3 --> atualizção layout - Carlos Abreu - 28/03/2017
	Versão 4 --> Inclusão dos campos reg_del nas consultas - 20/11/2017 - Carlos Abreu	
*/

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

function atualizatabela($dados_form)
{
	$resposta = new xajaxResponse();

	$db = new banco_dados();
	
	$xml = new XMLWriter();

	$sql = "SELECT id_fechamento, periodo, liberado FROM ".DATABASE.".fechamento_folha ";
	$sql .= "WHERE fechamento_folha.reg_del = 0 ";
	$sql .= "GROUP BY fechamento_folha.periodo ";
	$sql .= "ORDER BY fechamento_folha.periodo DESC ";

	$db->select($sql, 'MYSQL', true);
	
	if ($db->erro != '')
	{
		$resposta->addAlert("Não foi possível a seleção dos dados".$sql);
	}
		
	$xml->openMemory();
	$xml->setIndent(false);
	$xml->startElement('rows');
	
	foreach($db->array_select as $regs)
	{
		$array_periodo = explode(",",$regs["periodo"]);
		
		$per_dataini = substr($array_periodo[0],-2,2) . "/" . substr($array_periodo[0],0,4);
		
		$per_datafin = substr($array_periodo[1],-2,2) . "/" . substr($array_periodo[1],0,4);
		
		$checked = $regs['liberado']=='1' ? 'checked="checked"' : '';

		$xml->startElement('row');
			$xml->writeAttribute('id', $regs["id_fechamento"]);			
			$xml->writeElement('cell', $per_dataini . ' - ' . $per_datafin);
			$xml->writeElement('cell', '<input name="chk_'.$regs["periodo"].'" id="chk_'.$regs["periodo"].'" type="checkbox" value="1" '.$checked.' onclick=if(this.checked==false){document.getElementById(this.name+"_uncheck").checked=true;}else{document.getElementById(this.name+"_uncheck").checked=false;}; /><input name="chk_'.$regs["periodo"].'_uncheck" id="chk_'.$regs["periodo"].'_uncheck" type="checkbox" value="0" style="display:none;" />');
		$xml->endElement();		
	}
	
	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);
		
	$resposta->addScript("grid('fechamentos', true, '200', '".$conteudo."');");
	
	return $resposta;
}

function alterar($dados_form)
{
	$resposta = new xajaxResponse();

	$db = new banco_dados;

	$sql = "SELECT periodo FROM ".DATABASE.".fechamento_folha ";
	$sql .= "WHERE fechamento_folha.reg_del = 0 ";
	$sql .= "GROUP BY fechamento_folha.periodo ";

	$db->select($sql, 'MYSQL', true);
	
	if ($db->erro != '')
	{
		exit("Não foi possível fazer a seleção.");
	}
	
	foreach($db->array_select as $cont_sl)
	{
		//$virgula = ',';
		
		if (isset($dados_form["chk_".$cont_sl["periodo"]]) || isset($dados_form["chk_".$cont_sl["periodo"].'_uncheck']))
		{
			if($dados_form["chk_".$cont_sl["periodo"]]=="1")
			{
				$array_libera [] = "'".$cont_sl["periodo"]."'";
				
			}

			if(isset($dados_form["chk_".$cont_sl['periodo']."_uncheck"]))
			{
				$array_n_libera[] = "'".$cont_sl["periodo"]."'";
			}
		}
	}
	
	$filtro_fechamento_libera = implode(',',$array_libera);
	$filtro_fechamento_desliga = implode(',',$array_n_libera);

	$params = array();
	
	if($filtro_fechamento_libera)
	{
		$params['emails']['to'][] = array('email' => 'financeiro@dominio.com.br', 'nome' => 'Financeiro');
		$params['subject'] = 'LIBERAÇÃO DE FECHAMENTO';
		
		//Concatena mensagem de urgência
		$texto = '<B><FONT FACE=ARIAL COLOR=RED>LIBERAÇÃO DE FECHAMENTO</FONT></B><BR><br><br>';
		$texto .= 'O fechamento já está disponível para consulta no sistema.<br>';
		$texto .= 'Favor encaminhar a NF para financeiro@dominio.com.br.<br>';
		
		$sql = "SELECT * FROM ".DATABASE.".fechamento_folha, ".DATABASE.".usuarios, ".DATABASE.".funcionarios ";
		$sql .= "WHERE fechamento_folha.periodo IN(".$filtro_fechamento_libera.") ";
		$sql .= "AND fechamento_folha.reg_del = 0 ";
		$sql .= "AND usuarios.reg_del = 0 ";
		$sql .= "AND funcionarios.reg_del = 0 ";
		$sql .= "AND fechamento_folha.liberado = 0 ";		
		$sql .= "AND fechamento_folha.id_funcionario = funcionarios.id_funcionario ";
		$sql .= "AND funcionarios.id_usuario = usuarios.id_usuario ";
		$sql .= "ORDER BY funcionario ";
		
		$db->select($sql,'MYSQL',true);
		
		if ($db->erro != '')
		{
			exit("Não foi possível fazer a seleção.");
		}
		
		foreach($db->array_select as $cont)
		{
			$params['emails']['to'][] = array('email' => $cont['email'], 'nome' => $cont['funcionario']);
			
			$periodo = explode(",",$cont["periodo"]);
			
			$data = explode("-",$periodo[1]); //data[0] = ano, data[1] = mes
			
			$data_formada = mktime(0,0,0,$data[1]+1,1,$data[0]);//mes seguinte ao fechamento
			
			$texto_emisao = 'Emitir nota SOMENTE a partir '.date('d/m/Y',$data_formada).' e não mencionar período de referência.<br><br>';			
		}

		if(ENVIA_EMAIL)
		{
	
			$mail = new email($params);
		
			$mail->montaCorpoEmail($texto.$texto_emisao);
			
			if(!$mail->Send())
			{
				echo $mail->ErrorInfo;
			}
		}
		else 
		{
			$resposta->addScriptCall('modal', $texto.$texto_emisao, '300_650', 'Conteúdo email', 1);
		}	
	}
	
	if($filtro_fechamento_desliga)
	{	
		$usql = "UPDATE ".DATABASE.".fechamento_folha SET ";
		$usql .= "fechamento_folha.liberado = '0' ";
		$usql .= "WHERE fechamento_folha.periodo IN(".$filtro_fechamento_desliga.") ";
		$usql .= "AND reg_del = 0 ";
		
		$db->update($usql, 'MYSQL');
		
		if ($db->erro != '')
		{
			exit("Não foi possível atualizar os dados.");
		}
	}
	else
	{
		$usql = "UPDATE ".DATABASE.".fechamento_folha SET ";
		$usql .= "fechamento_folha.liberado = '1' ";
		$usql .= "WHERE fechamento_folha.periodo IN(".$filtro_fechamento_libera.") ";
		$usql .= "AND reg_del = 0 ";
		
		$db->update($usql, 'MYSQL');
		
		if ($db->erro != '')
		{
			exit("Não foi possível atualizar os dados.");
		}
	}
	
	$resposta->addAlert("Fechamento liberado com sucesso.");	
	
	return $resposta;
}

$xajax->registerFunction("atualizatabela");

$xajax->registerFunction("alterar");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","xajax_atualizatabela(xajax.getFormValues('frm'));");

$conf = new configs();

$smarty->assign('ocultarCabecalhoRodape','style="display:none;"');

$smarty->assign('revisao_documento', 'V4');

$smarty->assign('campo', $conf->campos('listafechamentos'));

$smarty->assign("botao", $conf->botoes());

$smarty->assign("classe",CSS_FILE);

$smarty->display('listafechamentos.tpl');

?>
<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script>

function grid(tabela, autoh, height, xml)
{
	mygrid = new dhtmlXGridObject(tabela);

	mygrid.enableAutoHeight(autoh,height);
	mygrid.enableRowsHover(true,'cor_mouseover');

	mygrid.setHeader("Fechamento,liberado");
	mygrid.setInitWidths("100,100");
	mygrid.setColAlign("left,center");
	mygrid.setColTypes("ro,ro");
	mygrid.setColSorting("str,str");
	
	mygrid.setSkin("dhx_skyblue");
    mygrid.enableMultiselect(true);
    mygrid.enableCollSpan(true);	
	mygrid.init();
	mygrid.loadXMLString(xml);
}

</script>