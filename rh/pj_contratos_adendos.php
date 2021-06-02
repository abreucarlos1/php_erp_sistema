<?php
/*
		Formulário de Adendos de contratos PJ - RH	
		
		Criado por Carlos Abreu  
		
		local/Nome do arquivo:
		../rh/pj_conratos_adendos.php
	
		Versão 0 --> VERSÃO INICIAL : 21/05/2013 - Carlos Abreu
		Versão 1 --> Atualização layout - Carlos Abreu - 07/04/2017
		Versão 2 --> Inclusão dos campos reg_del nas consultas - 29/11/2017 - Carlos Abreu
*/	

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));

require_once(INCLUDE_DIR."include_form.inc.php");

function atualizatabela($dados_form)
{
	$resposta = new xajaxResponse();
	
	$xml = new XMLWriter();
	
	$conf = new configs();
	
	$campos = $conf->campos('pj_contratos_adendos',$resposta);
	
	$msg = $conf->msg($resposta);

	$db = new banco_dados;
	
	$sql = "SELECT * FROM ".DATABASE.".pj_tipo_adendos, ".DATABASE.".pj_contratos_adendos ";
	$sql .= "WHERE pj_tipo_adendos.id_tipo_adendo = pj_contratos_adendos.id_tipo_adendo ";
	$sql .= "AND pj_tipo_adendos.reg_del = 0 ";
	$sql .= "AND pj_contratos_adendos.reg_del = 0 ";
	$sql .= "AND pj_contratos_adendos.id_contrato = '".$dados_form["id_contrato"]."' "; 
	$sql .= "ORDER BY id_contratos_adendos ASC ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}

	$conteudo = "";
	
	$xml->openMemory();
	$xml->setIndent(false);
	$xml->startElement('rows');
	
	foreach($db->array_select as $cont_desp)
	{
		$xml->startElement('row');
			$xml->writeAttribute('id',$cont_desp["id_contratos_adendo"]);
			
			$xml->startElement('cell');
				$xml->text($cont_desp["tipo_adendo"]);
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text(mysql_php($cont_desp["data_final"]));
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text(mysql_php($cont_desp["data_prorrogacao_ini"]));
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text(mysql_php($cont_desp["data_prorrogacao_fim"]));
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text($cont_desp["valor"]);
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text('<img src="'.DIR_IMAGENS.'imprimir.png" style="cursor:pointer;" onclick=imprimir_adendo("'.$cont_desp["id_contratos_adendos"].'");\>');
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text('<img src="'.DIR_IMAGENS.'apagar.png" style="cursor:pointer;" onclick=if(apagar("'. trim($cont_desp["tipo_adendo"]).'")){xajax_excluir("'.$cont_desp["id_contratos_adendos"].'","'. $cont_desp["tipo_adendo"].'");}>');
			$xml->endElement();
			
		$xml->endElement();	
	}
	
	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('div_grid',true,'450','".$conteudo."');");
	
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
		
		$data_fim = "0000-00-00";
		$data_pror_ini = "0000-00-00";
		$data_pror_fim = "0000-00-00";
		$valor = 0;
		
		if($dados_form["id_contrato"]!='' && $dados_form["tipo_adendo"]!='')
		{
			if($dados_form["data_fim"]!='')
			{
				$data_fim = php_mysql($dados_form["data_fim"]);
			}
			
			if($dados_form["data_pror_ini"]!="")
			{
				$data_pror_ini = php_mysql($dados_form["data_pror_ini"]);
			}
			
			if($dados_form["data_pror_fim"]!="")
			{
				$data_pror_fim = php_mysql($dados_form["data_pror_fim"]);
			}
	
			$isql = "INSERT INTO ".DATABASE.".pj_contratos_adendos ";
			$isql .= "(id_tipo_adendo, id_contrato, data_final, data_prorrogacao_ini, data_prorrogacao_fim, valor) ";
			$isql .= "VALUES ('" . $dados_form["tipo_adendo"] . "', ";
			$isql .= "'" . $dados_form["id_contrato"] . "', ";
			$isql .= "'" . $data_fim . "', ";
			$isql .= "'" . $data_pror_ini . "', ";
			$isql .= "'" . $data_pror_fim . "', ";
			$isql .= "'" . str_replace(",",".",str_replace(".","",$dados_form["valor"])) . "') ";

			$db->insert($isql,'MYSQL');

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}				
				
			$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'));");
			
			$resposta->addAlert($msg[1]);	
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

	if($conf->checa_permissao(2,$resposta))
	{
		$db = new banco_dados;
		
		$usql = "UPDATE ".DATABASE.".pj_contratos_adendos SET ";
		$usql .= "reg_del = 1, ";
		$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
		$usql .= "data_del = '".date('Y-m-d')."' ";
		$usql .= "WHERE id_contratos_adendos = '".$id."' ";
	
		$db->update($usql,'MYSQL');

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
		
		$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'));");
		
		$resposta->addAlert($what . $msg[3]);
	}

	return $resposta;
}

function campos($dados_form)
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$campos = $conf->campos('pj_contratos_adendos',$resposta);
	
	$db = new banco_dados;
	
	$valor = 0;
	
	$sql = "SELECT * FROM ".DATABASE.".pj_contratos ";
	$sql .= "WHERE pj_contratos.id_contrato = '".$dados_form["id_contrato"]."' ";
	$sql .= "AND reg_del = 0 ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	
	$regs = $db->array_select[0];
	
	$sql = "SELECT * FROM ".DATABASE.".salarios ";
	$sql .= "WHERE salarios.id_funcionario = '".$regs["id_funcionario"]."' ";
	$sql .= "AND salarios.reg_del = 0 ";
	$sql .= "ORDER BY id_salario DESC LIMIT 1 ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	
	$regs1 = $db->array_select[0];	
	
	$sql = "SELECT * FROM ".DATABASE.".pj_contratos_adendos ";
	$sql .= "WHERE pj_contratos_adendos.id_contrato = '".$regs["id_contrato"]."' ";
	$sql .= "AND reg_del = 0 ";
	$sql .= "ORDER BY id_contratos_adendos DESC LIMIT 1 ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	
	$regs2 = $db->array_select[0];
	
		
	if($db->numero_registros>0)
	{
		switch ($regs2["id_tipo_adendo"])
		{
			case 1:
			case 2:
				$data_fim = $regs2["data_prorrogacao_fim"];
			break;
			
			case 3:
			case 4:
			case 5:
				$data_fim = $regs2["data_final"];
			break;
		}
		
	}
	else
	{
		$data_fim = $regs["data_fim"];	
	}
	
	$conteudo = '';
	
	$header = '<table border="0" width="95%" cellpadding="0" cellspacing="0">';
	
	$footer = '</table>';
	
	switch ($dados_form["tipo_adendo"])
	{
		case 1://prorrogação prazo
			
			
			$conteudo = '<tr>			
					  <td width="5%" valign="top"><label class="labels">
						'.$campos[4].'
						</label>
						  <input name="data_pror_ini" type="text" class="caixa" id="data_pror_ini" value="'.calcula_data(mysql_php($data_fim),'sum','day',1).'" size="12" maxlength="10" onkeypress=transformaData(this, event); onblur="return checaTamanhoData(this,10);" />
						</td>
					  <td width="5%" valign="top"><label class="labels">
						'.$campos[5].'
						</label>
						  <input name="data_pror_fim" type="text" class="caixa" id="data_pror_fim" value="'.calcula_data(mysql_php($data_fim),'sum','year',1).'" size="12" maxlength="10" onkeypress=transformaData(this, event); onblur="return checaTamanhoData(this,10);" />
						</td>
					</tr> ';
			
	
		break;
		
		case 2://REAJUSTE
			
			switch ($regs["id_clausula_tipo_contrato"])
			{
				case "3": //horista
					$valor = $regs1["salario_hora"];
				break;
				
				case "4": //mensalista
					$valor = $regs1["salario_mensalista"];
				break;		
			}
		
			$conteudo = '<tr>
					  <td width="5%" valign="top"><label class="labels">
						'.$campos[4].'
						</label>
						  <input name="data_pror_ini" type="text" class="caixa" id="data_pror_ini" value="'.calcula_data(mysql_php($data_fim),'sum','day',1).'" size="12" maxlength="10" onkeypress=transformaData(this, event); onblur="return checaTamanhoData(this,10);" />
						</td>
					  <td width="5%" valign="top"><label class="labels">
						'.$campos[5].'
						</label>
						  <input name="data_pror_fim" type="text" class="caixa" id="data_pror_fim" value="'.calcula_data(mysql_php($data_fim),'sum','year',1).'" size="12" maxlength="10" onkeypress=transformaData(this, event); onblur="return checaTamanhoData(this,10);" />
						</td>
					  <td width="5%" valign="top"><label class="labels">
						'.$campos[6].'
						</label>
						  <input name="valor" type="text" class="caixa" id="valor" value="'.formatavalor($valor).'" size="12" onKeyDown=FormataValor(this, 10, event); />
						</td>
					</tr> ';
		
		break;		
		
		case 3: //Rescisão colaborador
		case 4: //Rescisão empresa
		case 5: //distrato	
			$conteudo = '<tr>
					  <td width="5%" valign="top"><label class="labels">
						'.$campos[3].'
						</label>
						  <input name="data_fim" type="text" class="caixa" id="data_fim" value="'.calcula_data(mysql_php($data_fim),'sum','day',1).'" size="12" maxlength="10" onkeypress=transformaData(this, event); onblur="return checaTamanhoData(this,10);" />
						</td>
					</tr> ';
		
		break;
			
	}
			
	$resposta->addAssign("div_campos","innerHTML", $header.$conteudo.$footer);
	
	return $resposta;
}

$xajax->registerFunction("insere");
$xajax->registerFunction("excluir");
$xajax->registerFunction("campos");
$xajax->registerFunction("atualizatabela");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","xajax_atualizatabela(xajax.getFormValues('frm'));");
?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script>

function grid(tabela, autoh, height, xml)
{	
	mygrid = new dhtmlXGridObject(tabela);
	
	mygrid.enableAutoHeight(autoh,height);
	
	mygrid.enableRowsHover(true,'cor_mouseover');

	mygrid.setHeader("Tipo,Data distrato,Data inicial,Data final,Valor Contrato,I,D",
		null,
		["text-align:left","text-align:left","text-align:left","text-align:left","text-align:left","text-align:center","text-align:center"]);
	mygrid.setInitWidths("*,*,*,*,*,30,30");
	mygrid.setColAlign("left,left,left,left,left,center,center");
	mygrid.setColTypes("ro,ro,ro,ro,ro,ro,ro");
	mygrid.setColSorting("str,str,str,str,str,str,str");
	
	mygrid.setSkin("dhx_skyblue");
	mygrid.enableMultiselect(true);
	mygrid.enableCollSpan(true);		
	mygrid.init();
	mygrid.loadXMLString(xml);

}

function imprimir_adendo(id_adendo)
{
	window.open('relatorios/pj_imprimir_adendo.php?id_adendo='+id_adendo+'', '_blank');
}

</script>

<?php
$conf = new configs();

$array_tipo_values[] = "";
$array_tipo_output[] = "SELECIONE";

$sql = "SELECT * FROM ".DATABASE.".pj_tipo_adendos ";
$sql .= "WHERE reg_del = 0 ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}

foreach ($db->array_select as $regs)
{
	$array_tipo_values[] = $regs["id_tipo_adendo"];
	$array_tipo_output[] = $regs["tipo_adendo"];
}

$sql = "SELECT * FROM ".DATABASE.".pj_contratos ";
$sql .= "WHERE id_contrato = '".$_GET["id_contrato"]."' ";
$sql .= "AND reg_del = 0 ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}

$regs = $db->array_select[0];

$smarty->assign("revisao_documento","V2");

$smarty->assign("option_tipo_values",$array_tipo_values);
$smarty->assign("option_tipo_output",$array_tipo_output);

$smarty->assign("campo",$conf->campos('pj_contratos_adendos'));

$smarty->assign("id_contrato",$_GET["id_contrato"]);

$smarty->assign("num_contrato",sprintf("%04d",$regs["id_contrato"])."/".substr($regs["data_inicio"],0,4));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("classe",CSS_FILE);

$smarty->display('pj_contratos_adendos.tpl');
?>