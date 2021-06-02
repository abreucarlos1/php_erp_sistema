<?php
/*
		Formulário de OS X Hora Adicional	
		
		Criado por Carlos Abreu / Otávio Pamplon ia
		
		local/Nome do arquivo:
		../financeiro/os_x_horaadicional.php
		
		Versão 0 --> VERSÃO INICIAL - 20/03/2007
		Versão 1 --> Atualização Lay-Out - 09/11/2007
		Versão 2 --> Implementação templates Smarty - 03/07/2008
		Versão 3 --> atualização da classe banco - 21/01/2015 - Carlos Abreu
		Versão 4 --> Atualização layout 2015 - 11/11/2015 - Carlos Eduardo Máximo
		Versão 5 --> Atualização imagens - 21/07/2016 - Carlos Abreu
		Versão 6 --> atualização layout - Carlos Abreu - 28/03/2017
		Versão 7 --> Inclusão dos campos reg_del nas consultas - 20/11/2017 - Carlos Abreu		
*/

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(37))
{
	nao_permitido();
}

function atualizatabela($filtro, $combo='')
{
	$resposta = new xajaxResponse();

	$xml = new XMLWriter();
	
	$db = new banco_dados;

	$sql_filtro = "";
	
	$sql_texto = "";	

	$chars = array("'","\"",")","(","\\","/");
	
	if($filtro!="")
	{		
		$array_valor = explode(" ",$filtro);
		
		for($x=0;$x<count($array_valor);$x++)
		{
			$sql_texto .= "%" . $array_valor[$x] . "%";
		}
		
		$sql_filtro = " AND (os.os LIKE '".$sql_texto."') ";
	
	}
	
	$sql = "SELECT * FROM ".DATABASE.".ordem_servico, ".DATABASE.".ordem_servico_status ";
	$sql .= "WHERE ordem_servico.id_os_status = ordem_servico_status.id_os_status ";
	$sql .= "AND ordem_servico.reg_del = 0 ";
	$sql .= "AND ordem_servico_status.reg_del = 0 ";
	$sql .= "AND ordem_servico_status.id_os_status NOT IN (3,8,9,12) ";
	$sql .= $sql_filtro;
	$sql .= "ORDER BY ordem_servico.os ";
	
	$xml->openMemory();
	$xml->setIndent(false);
	$xml->startElement('rows');
	
	$db->select($sql,'MYSQL',true);	
	
	foreach($db->array_select as $cont_desp)
	{
		if($cont_desp["hora_extra"]=='1')
		{
			$check = 'checked';
		}
		else
		{
			$check = '';
		}
		
		$xml->startElement('row');
			$xml->writeElement('cell', sprintf("%05d",$cont_desp["os"]));
			$xml->writeElement('cell', $cont_desp["descricao"]);
			$xml->writeElement('cell', '<input type="checkbox" name="chk_'.$cont_desp["id_os"].'" value="1" '.$check.' onclick=xajax_horaextra(xajax.getFormValues("frm_os_x_horaadicional"),'.$cont_desp["id_os"].'); />');
			
		$xml->endElement();
	}	

	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);

	$resposta->addScript("grid('os_x_horaadicional', true, '550', '".$conteudo."');");
	
	$resposta->addScript("combo('');");
	
	return $resposta;

}

function horaextra($dados_form,$id_os)
{		
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	if($dados_form["chk_".$id_os])
	{
		$status = 1;
	}
	else
	{
		$status = 0;
	}

	$usql = "UPDATE ".DATABASE.".ordem_servico SET ";
	$usql .= "ordem_servico.hora_extra = '" . $status . "' ";
	$usql .= "WHERE ordem_servico.id_os = '".$id_os."' ";
	$usql .= "AND reg_del = 0 ";

	$db->update($usql,'MYSQL');
	
	if ($db->erro != '')
		$resposta->addAlert('Houve uma falha ao tentar realizar esta operação!');
	else
		$resposta->addAlert('Alteração realizada corretamente!');
		
	return $resposta;
}

$xajax->registerFunction("horaextra");

$xajax->registerFunction("atualizatabela");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","xajax_atualizatabela('');");

$conf = new configs();

$smarty->assign("revisao_documento","V7");

$smarty->assign("campo",$conf->campos('os_horas_adicionais'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("classe",CSS_FILE);

$smarty->display('os_x_horaadicional.tpl');

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script>

function grid(tabela, autoh, height, xml)
{
	mygrid = new dhtmlXGridObject(tabela);

	mygrid.enableAutoHeight(autoh,height);
	mygrid.enableRowsHover(true,'cor_mouseover');

	mygrid.setHeader('OS,Descrição,HA');
	mygrid.setInitWidths("50,*,50");
	mygrid.setColAlign("left,left,center");
	mygrid.setColTypes("ro,ro,ro");
	mygrid.setColSorting("str,str,str");
	
	mygrid.setSkin("dhx_skyblue");
    mygrid.enableMultiselect(true);
    mygrid.enableCollSpan(true);	
	mygrid.init();
	mygrid.loadXMLString(xml);
}
</script>