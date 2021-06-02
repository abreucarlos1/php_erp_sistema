<?php
/*
		Listagem de Fechamentos para permitir anexos	
		
		Criado por Carlos Abreu  
		
		local/Nome do arquivo:
		../financeiro/libera_anexos.php
		
		Versão 0 --> VERSÃO INICIAL - 05/03/2012
		Versão 1 --> Atualização classe banco de dados - 21/01/2015 - Carlos Abreu
		Versão 2 --> Atualização Layout - 18/07/2016 - Carlos Abreu
		Versão 3 --> atualização layout - Carlos Abreu - 28/03/2017
		Versão 4 --> Inclusão dos campos reg_del nas consultas - 20/11/2017 - Carlos Abreu
*/	

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

function atualizatabela($dados_form)
{
	$resposta = new xajaxResponse();

	$db = new banco_dados();
	
	$xml = new XMLWriter();

	$sql = "SELECT id_fechamento, periodo, permite_anexos FROM ".DATABASE.".fechamento_folha ";
	$sql .= "WHERE fechamento_folha.reg_del = 0 ";
	$sql .= "GROUP BY fechamento_folha.periodo ";
	$sql .= "ORDER BY fechamento_folha.periodo DESC ";

	$db->select($sql, 'MYSQL', true);
	
	if ($db->erro != '')
	{
		$resposta->addAlert("Não foi possível a seleção dos dados.".$sql);
	}
		
	$xml->openMemory();
	$xml->setIndent(false);
	$xml->startElement('rows');
	
	foreach($db->array_select as $regs)
	{
		$array_periodo = explode(",",$regs["periodo"]);
		
		$per_dataini = substr($array_periodo[0],-2,2) . "/" . substr($array_periodo[0],0,4);
		
		$per_datafin = substr($array_periodo[1],-2,2) . "/" . substr($array_periodo[1],0,4);
		
		$checked = $regs['permite_anexos']=='1' ? 'checked="checked"' : '';

		$xml->startElement('row');
			$xml->writeAttribute('id', $regs["id_fechamento"]);			
			$xml->writeElement('cell', $per_dataini . ' - ' . $per_datafin);
			$xml->writeElement('cell', '<input name="chk_'.$regs["periodo"].'" id="chk_'.$regs["periodo"].'" type="checkbox" value="1" '.$checked.' onclick=if(this.checked==false){document.getElementById(this.name+"_uncheck").checked=true;}else{document.getElementById(this.name+"_uncheck").checked=false;}; /><input name="chk_'.$regs["periodo"].'_uncheck" id="chk_'.$regs["periodo"].'_uncheck" type="checkbox" value="0" style="display:none;" />');
		$xml->endElement();		
	}
	
	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);
		
	$resposta->addScript("grid('anexos', true, '200', '".$conteudo."');");
	
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

	if($filtro_fechamento_desliga)
	{	
		$usql = "UPDATE ".DATABASE.".fechamento_folha SET ";
		$usql .= "fechamento_folha.permite_anexos = '0' ";
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
		$usql .= "fechamento_folha.permite_anexos = '1' ";
		$usql .= "WHERE fechamento_folha.periodo IN(".$filtro_fechamento_libera.") ";
		$usql .= "AND reg_del = 0 ";
		
		$db->update($usql, 'MYSQL');
		
		if ($db->erro != '')
		{
			exit("Não foi possível atualizar os dados.");
		}
	}
	
	$resposta->addAlert("Anexos liberado com sucesso.");	
	
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

$smarty->assign('campo', $conf->campos('libera_anexos'));

$smarty->assign("botao", $conf->botoes());

$smarty->assign("classe",CSS_FILE);

$smarty->display('libera_anexos.tpl');

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