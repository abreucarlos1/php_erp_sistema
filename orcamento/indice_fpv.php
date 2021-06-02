<?php
/*
		Formulário de Indices FPV	
		
		Criado por Carlos Abreu  
		
		local/Nome do arquivo:
		../orcamento/indice_fpv.php
		
		Versão 0 --> VERSÃO INICIAL - Carlos Abreu - 12/04/2017	
		Versão 1 --> Inclusão dos campos reg_del nas consultas - 20/11/2017 - Carlos Abreu
*/

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(590))
{
	nao_permitido();
}

function voltar()
{
	$resposta = new xajaxResponse();

	$resposta->addScriptCall("reset_campos('frm')");
	
	$resposta->addAssign("btn_atualizar", "value", "Inserir");
	
	$resposta->addAssign("data","value",date('d/m/Y'));
	
	$resposta->addEvent("btn_atualizar", "onclick", "xajax_inserir(xajax.getFormValues('frm'));");
	
	$resposta->addEvent("btnvoltar", "onclick", "history.back();");

	return $resposta;
}

function atualizatabela($dados_form)
{	
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;

	$xml = new XMLWriter();
	$xml->openMemory();
	$xml->startElement('rows');

	//seleciona os indices cadastrados
	$sql = "SELECT * FROM ".DATABASE.".indices_fpv, ".DATABASE.".tipos_indices ";
	$sql .= "WHERE indices_fpv.reg_del = 0 ";
	$sql .= "AND tipos_indices.reg_del = 0 ";
	$sql .= "AND indices_fpv.id_tipo_indice = tipos_indices.id_tipo_indice ";
	$sql .= "ORDER BY tipos_indices.ordem ";
	
	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}
	
	$array_regs = $db->array_select;

	foreach($array_regs as $regs)
	{
		//percentual e data atual
		$sql = "SELECT * FROM ".DATABASE.".indices_fpv_historico ";
		$sql .= "WHERE indices_fpv_historico.id_indice_fpv = '" . $regs["id_indice_fpv"] . "' ";
		$sql .= "AND indices_fpv_historico.reg_del = 0 ";
		$sql .= "ORDER BY id_indice_fpv_historico DESC, data_alteracao DESC LIMIT 1 ";

		$db->select($sql,'MYSQL',true);

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
			
			return $resposta;
		}
		
		$regs1 = $db->array_select[0];

		$xml->startElement('row');
			$xml->writeAttribute('id', $regs['id_indice_fpv']);
			$xml->writeElement('cell', $regs['indice']);
			$xml->writeElement('cell', number_format($regs1['percentual'],4,",","."));
			$xml->writeElement('cell', mysql_php($regs1["data_alteracao"]));
			$xml->writeElement('cell', '<img style="cursor:pointer;" src="'.DIR_IMAGENS.'detalhes.png" onclick=historico("'.$regs["id_indice_fpv"].'","'.str_replace(" "," ",$regs["indice"]).'");>');
			$xml->writeElement('cell', '<img style="cursor:pointer;" src="'.DIR_IMAGENS.'apagar.png" onclick=if(confirm("Deseja excluir os dados do índice? Todo o histórico será excluído!")){xajax_excluir("'.$regs["id_indice_fpv"].'")};>');
		$xml->endElement();
	}
	
	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('indices', true, '400', '".$conteudo."');");

	return $resposta;
}

function inserir($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	if($dados_form["tipo_indice"]!=0 && !empty($dados_form["percentual"]))
	{	
		//verifica se o indice já esta cadastrado, caso esteja, incluir como historico
		$sql = "SELECT * FROM ".DATABASE.".indices_fpv ";
		$sql .= "WHERE indices_fpv.id_tipo_indice = '".$dados_form["tipo_indice"]."' ";
		$sql .= "AND indices_fpv.reg_del = 0 ";
	
		$db->select($sql,'MYSQL',true);
	
		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
			
			return $resposta;
		}
	
		$regs_ind = $db->array_select[0];
		
		//Insere um novo registro
		if($db->numero_registros<=0)
		{
			//Insere o tipo de indice
			$isql = "INSERT INTO ".DATABASE.".indices_fpv(id_tipo_indice) VALUES(";
			$isql .= "'" . $dados_form["tipo_indice"] . "') ";
	
			$db->insert($isql,'MYSQL');
	
			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
				
				return $resposta;
			}
			
			$id_indice = $db->insert_id;
			
			//insere o historico
			$isql = "INSERT INTO ".DATABASE.".indices_fpv_historico(id_indice_fpv, percentual, id_funcionario, data_alteracao) VALUES(";
			$isql .= "'" . $id_indice . "', ";
			$isql .= "'" . str_replace(",",".",str_replace(".","",$dados_form["percentual"])) . "', ";
			$isql .= "'".$_SESSION["id_funcionario"]."', ";
			$isql .= "'".php_mysql($dados_form["data"])."') ";
	
			$db->insert($isql,'MYSQL');
	
			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
				
				return $resposta;
			}
			
			$id_historico = $db->insert_id;
			
			//atualiza o indice atual na tabela principal
			$usql = "UPDATE ".DATABASE.".indices_fpv SET ";
			$usql .= "id_indice_atual = '".$id_historico."' ";
			$usql .= "WHERE id_indice_fpv = '".$id_indice."' ";
			$usql .= "AND reg_del = 0 ";
	
			$db->update($usql,'MYSQL');
	
			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
				
				return $resposta;
			}		
			
			$resposta->addAlert("Índice inserido com sucesso.");		
	
		}
		else
		{
			$indice_fpv = $regs_ind["id_indice_fpv"];
					
			//insere o historico
			$isql = "INSERT INTO ".DATABASE.".indices_fpv_historico(id_indice_fpv, percentual, id_funcionario, data_alteracao) VALUES(";
			$isql .= "'" . $indice_fpv . "', ";
			$isql .= "'" . str_replace(",",".",str_replace(".","",$dados_form["percentual"])) . "', ";
			$isql .= "'".$_SESSION["id_funcionario"]."', ";
			$isql .= "'".php_mysql($dados_form["data"])."') ";
	
			$db->insert($isql,'MYSQL');
	
			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
				
				return $resposta;
			}
			
			$id_historico = $db->insert_id;
			
			//atualiza o indice atual na tabela principal
			$usql = "UPDATE ".DATABASE.".indices_fpv SET ";
			$usql .= "id_indice_atual = '".$id_historico."' ";
			$usql .= "WHERE id_indice_fpv = '".$indice_fpv."' ";
			$usql .= "AND reg_del = 0 ";
	
			$db->update($usql,'MYSQL');
	
			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
				
				return $resposta;
			}		
			
			$resposta->addAlert("Índice atualizado com sucesso.");	
		}
		
		$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'))");
	}
	else
	{
		$resposta->addAlert("Os campos devem estar preenchidos.");	
	}

	return $resposta;
}

function editar($id_indice)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;

	//seleciona o indice
	$sql = "SELECT * FROM ".DATABASE.".tipos_indices, ".DATABASE.".indices_fpv ";
	$sql .= "WHERE indices_fpv.id_indice_fpv = '".$id_indice."' ";
	$sql .= "AND indices_fpv.reg_del = 0 ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}

	$regs = $db->array_select[0];

	//indice atual
	$sql = "SELECT * FROM ".DATABASE.".indices_fpv_historico ";
	$sql .= "WHERE id_indice_fpv_historico = '" . $regs["id_indice_atual"] . "' ";
	$sql .= "AND indices_fpv_historico.reg_del = 0 ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}
	
	$regs1 = $db->array_select[0];

	$resposta->addAssign("id_indice","value", $regs["id_indice_fpv"]);

	$resposta->addScript("seleciona_combo('" . $regs["id_tipo_indice"] . "', 'tipo_indice'); ");
	
	$resposta->addAssign("data","value",mysql_php($regs1["data_alteracao"]));

	$resposta->addAssign("percentual","value",number_format($regs1["percentual"],4,",","."));	
	
	$resposta->addAssign("btn_atualizar", "value", "Atualizar");
	
	$resposta->addEvent("btn_atualizar", "onclick", "if(confirm('Deseja alterar os dados do índice?')){xajax_atualizar(xajax.getFormValues('frm'));}");
	
	$resposta->addEvent("btnvoltar", "onclick", "xajax_voltar();");

	return $resposta;
}

function editar_hist($id_indice_hist)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	//indice historico
	$sql = "SELECT * FROM ".DATABASE.".indices_fpv_historico ";
	$sql .= "WHERE id_indice_fpv_historico = '" . $id_indice_hist . "' ";
	$sql .= "AND indices_fpv_historico.reg_del = 0 ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}
	
	$regs = $db->array_select[0];

	//seleciona o indice
	$sql = "SELECT * FROM ".DATABASE.".indices_fpv ";
	$sql .= "WHERE indices_fpv.id_indice_fpv = '".$regs["id_indice_fpv"]."' ";
	$sql .= "AND indices_fpv.reg_del = 0 ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}

	$regs1 = $db->array_select[0];

	$resposta->addAssign("id_indice_historico","value", $regs["id_indice_fpv_historico"]);

	$resposta->addScript("seleciona_combo('" . $regs1["id_tipo_indice"] . "', 'tipo_indice_hist'); ");
	
	$resposta->addAssign("data_hist","value",mysql_php($regs["data_alteracao"]));

	$resposta->addAssign("percentual_hist","value",number_format($regs["percentual"],4,",","."));
	
	$resposta->addScript("document.getElementById('btn_alt').disabled = false");	

	return $resposta;
}

//Só atualiza o item atual
function atualizar($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	if($dados_form["tipo_indice"]!=0 && !empty($dados_form["percentual"]))
	{
		//seleciona o indice
		$sql = "SELECT * FROM ".DATABASE.".indices_fpv ";
		$sql .= "WHERE indices_fpv.id_indice_fpv = '".$dados_form["id_indice"]."' ";
		$sql .= "AND indices_fpv.reg_del = 0 ";
	
		$db->select($sql,'MYSQL',true);
	
		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
			
			return $resposta;
		}
	
		$regs = $db->array_select[0];
		
		$usql = "UPDATE ".DATABASE.".indices_fpv_historico SET ";
		$usql .= "percentual = '".str_replace(",",".",str_replace(".","",$dados_form["percentual"]))."', ";
		$usql .= "id_funcionario = '".$_SESSION["id_funcionario"]."', ";
		$usql .= "data_alteracao = '".php_mysql($dados_form["data"])."' ";
		$usql .= "WHERE id_indice_fpv_historico = '".$regs["id_indice_atual"]."' ";
		$usql .= "AND reg_del = 0 ";
		
		$db->update($usql,'MYSQL');

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
			
			return $resposta;
		}
		
		$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'))");
		
	}
	else
	{
		$resposta->addAlert("Os campos devem estar preenchidos.");		
	}
	
	return $resposta;
}

//atualiza o historico
function atualizar_hist($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	if($dados_form["tipo_indice_hist"]!=0 && !empty($dados_form["percentual_hist"]))
	{
	
		$usql = "UPDATE ".DATABASE.".indices_fpv_historico SET ";
		$usql .= "percentual = '".str_replace(",",".",str_replace(".","",$dados_form["percentual_hist"]))."', ";
		$usql .= "id_funcionario = '".$_SESSION["id_funcionario"]."', ";
		$usql .= "data_alteracao = '".php_mysql($dados_form["data_hist"])."' ";
		$usql .= "WHERE id_indice_fpv_historico = '".$dados_form["id_indice_historico"]."' ";
		$usql .= "AND reg_del = 0 ";
		
		$db->update($usql,'MYSQL');

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
			
			return $resposta;
		}
		
		$resposta->addScript("xajax_hist('".$dados_form["id_indice"]."')");
		
		$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'))");		
	}
	else
	{
		$resposta->addAlert("Os campos devem estar preenchidos.");		
	}
	
	return $resposta;
}

function excluir($id_indice)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	$usql = "UPDATE ".DATABASE.".indices_fpv SET ";
	$usql .= "reg_del = 1, ";
	$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
	$usql .= "data_del = '".date('Y-m-d')."' ";
	$usql .= "WHERE id_indice_fpv = '".$id_indice."' ";
	$usql .= "AND reg_del = 0 ";
	
	$db->update($usql,'MYSQL');

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}
	
	$usql = "UPDATE ".DATABASE.".indices_fpv_historico SET ";
	$usql .= "reg_del = 1, ";
	$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
	$usql .= "data_del = '".date('Y-m-d')."' ";
	$usql .= "WHERE id_indice_fpv = '".$id_indice."' ";
	$usql .= "AND reg_del = 0 ";
	
	$db->update($usql,'MYSQL');

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}
	
	$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'))");
	
	return $resposta;
}

function excluir_hist($id_indice_hist)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;

	//seleciona o histórico para obter o id_indice
	$sql = "SELECT * FROM ".DATABASE.".indices_fpv_historico ";
	$sql .= "WHERE indices_fpv_historico.id_indice_fpv_historico = '".$id_indice_hist."' ";
	$sql .= "AND indices_fpv_historico.reg_del = 0 ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}
	
	$regs = $db->array_select[0];
	
	$id_indice = $regs["id_indice_fpv"];
	
	//exclui o registro do historico
	$usql = "UPDATE ".DATABASE.".indices_fpv_historico SET ";
	$usql .= "reg_del = 1, ";
	$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
	$usql .= "data_del = '".date('Y-m-d')."' ";
	$usql .= "WHERE id_indice_fpv_historico = '".$id_indice_hist."' ";
	$usql .= "AND reg_del = 0 ";
	
	$db->update($usql,'MYSQL');

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}
	
	//seleciona o historico para obter o ultimo registro
	$sql = "SELECT * FROM ".DATABASE.".indices_fpv_historico ";
	$sql .= "WHERE indices_fpv_historico.id_indice_fpv = '".$id_indice."' ";
	$sql .= "AND indices_fpv_historico.reg_del = 0 ";
	$sql .= "ORDER BY id_indice_fpv_historico DESC, data_alteracao DESC LIMIT 1 ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}
	
	$regs1 = $db->array_select[0];

	//atualiza o registro de indices para o atual
	$usql = "UPDATE ".DATABASE.".indices_fpv SET ";
	$usql .= "id_indice_atual = '".$regs1["id_indice_fpv_historico"]."' ";
	$usql .= "WHERE id_indice_fpv = '".$id_indice."' ";
	$usql .= "AND reg_del = 0 ";

	$db->update($usql,'MYSQL');

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}
	
	$resposta->addScript("xajax_hist('".$id_indice."')");	
	
	$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'))");
	
	return $resposta;
}

function hist($id_indice)
{	
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;

	$sql = "SELECT * FROM ".DATABASE.".tipos_indices ";
	$sql .= "WHERE tipos_indices.reg_del = 0 ";
	$sql .= "AND tipos_indices.reg_del = 0 ";
	$sql .= "ORDER BY tipos_indices.ordem ";
	
	$db->select($sql,'MYSQL',true);
	
	$comb = '<select name="tipo_indice_hist" class="caixa" id="tipo_indice_hist" onkeypress="return keySort(this);" >';
	$comb .= '<option value="">SELECIONE</option>';
	
	foreach ($db->array_select as $regs)
	{
		$array_indice[$regs["id_tipo_indice"]] = $regs["indice"];
		
		$comb .= '<option value="'.$regs["id_tipo_indice"].'">'.$regs["indice"].'</option>';
	}
	
	$comb .= '</select>'.

	$xml = new XMLWriter();
	$xml->openMemory();
	$xml->startElement('rows');

	//seleciona os indices cadastrados
	$sql = "SELECT * FROM ".DATABASE.".indices_fpv, ".DATABASE.".indices_fpv_historico ";
	$sql .= "WHERE indices_fpv.reg_del = 0 ";
	$sql .= "AND indices_fpv_historico.reg_del = 0 ";
	$sql .= "AND indices_fpv.id_indice_fpv = '" . $id_indice . "' ";
	$sql .= "AND indices_fpv.id_indice_fpv = indices_fpv_historico.id_indice_fpv ";
	$sql .= "ORDER BY indices_fpv.id_tipo_indice, indices_fpv.indice_fpv ";
	
	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}
	
	$array_regs = $db->array_select;

	foreach($array_regs as $regs)
	{
		//verifica se é o id atual E tenha + de 1 registro
		if($regs["id_indice_fpv_historico"]==$regs["id_indice_atual"] && count($array_regs)==1)
		{
			$img = ' ';	
		}
		else
		{
			
			$img = 	'<img style="cursor:pointer;" src="'.DIR_IMAGENS.'apagar.png" onclick=if(confirm("Deseja excluir os dados do índice?")){xajax_excluir_hist("'.$regs["id_indice_fpv_historico"].'")};>';	
		}
		
		$xml->startElement('row');
			$xml->writeAttribute('id', $regs['id_indice_fpv_historico']);
			$xml->writeElement('cell', $array_indice[$regs['id_tipo_indice']]);
			$xml->writeElement('cell', number_format($regs['percentual'],4,",","."));
			$xml->writeElement('cell', mysql_php($regs["data_alteracao"]));
			$xml->writeElement('cell', $img);
		$xml->endElement();
	}
	
	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addAssign("div_indice","innerHTML",$comb);
	
	$resposta->addScript("grid('indices_hist', true, '250', '".$conteudo."');");

	return $resposta;
}	

$xajax->registerFunction("voltar");
$xajax->registerFunction("inserir");
$xajax->registerFunction("editar");
$xajax->registerFunction("atualizar");
$xajax->registerFunction("atualizatabela");
$xajax->registerFunction("excluir");
$xajax->registerFunction("hist");
$xajax->registerFunction("editar_hist");
$xajax->registerFunction("atualizar_hist");
$xajax->registerFunction("excluir_hist");

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
	
	//retira o scroll horizontal
	mygrid.objBox.style.overflowX = "hidden";   
	mygrid.objBox.style.overflowY = "auto";
	
	switch(tabela)
	{
		case 'indices':
			function doOnRowSelected1(row,col)
			{
				if(col<=3)
				{						
					xajax_editar(row);
		
					return true;
				}
			}
		
			mygrid.setHeader("Tipo índice, Percentual, Data,H, E");
			mygrid.setInitWidths("300,120,100,30,30");
			mygrid.setColAlign("left,left,left,left,center,center");
			mygrid.setColTypes("ro,ro,ro,ro,ro,ro");
			mygrid.setColSorting("str,str,str,str,str,str");
		
			mygrid.attachEvent("onRowSelect",doOnRowSelected1);
		break;
		
		case 'indices_hist':
			function doOnRowSelected2(row,col)
			{
				if(col<=2)
				{						
					xajax_editar_hist(row);
		
					return true;
				}
			}
		
			mygrid.setHeader("Tipo índice, Percentual, Data, E");
			mygrid.setInitWidths("300,120,100,30");
			mygrid.setColAlign("left,left,left,left,center");
			mygrid.setColTypes("ro,ro,ro,ro,ro");
			mygrid.setColSorting("str,str,str,str,str");
		
			mygrid.attachEvent("onRowSelect",doOnRowSelected2);
		break;
		
	}
	mygrid.setSkin("dhx_skyblue");
	mygrid.enableMultiselect(true);
	mygrid.enableCollSpan(true);
	mygrid.init();
	mygrid.loadXMLString(xml);
}

function historico(id_indice,descricao)
{
	conteudo = '<form name="frm_hist" id="frm_hist" action="" method="POST">';
	
	conteudo += '<table width="100%">';
	
	conteudo += '<tr><td>';
	
	conteudo += '<label class="labels">'+descricao+'</label>';
	conteudo += '<input type="hidden" name="id_indice" id="id_indice" value="'+id_indice+'">';
	conteudo += '<input type="hidden" name="id_indice_historico" id="id_indice_historico" value="">';
	
	conteudo += '</tr></td>';
	
	conteudo += '<tr><td class="espacamento">';
	
    conteudo += '<table width="100%" border="0">';
    conteudo += '	<tr>';
    conteudo += '		<td width="13%"><label for="tipo_indice_hist" class="labels">Tipo índice</label><br />';
    conteudo += '		<div id="div_indice"> </div>';
	conteudo += '       </td>';
    conteudo += '       <td width="8%"><label for="data_hist" class="labels">Data</label><br />';
    conteudo += '	      <input name="data_hist" type="text" class="caixa" id="data_hist" size="10" maxlength="10" onkeypress="return txtBoxFormat(document.frm_hist, \'data_hist\', \'99/99/9999\', event);" value="" /></td>';
    conteudo += '       <td width="56%"><label for="percentual_hist" class="labels">Percentual</label><br />'; 
    conteudo += '	      <input name="percentual_hist" type="text" class="caixa" id="percentual_hist" size="7" placeholder="Percentual" maxlength="7" /></td>';
    conteudo += '  </tr>';
    conteudo += '</table>';
	
	conteudo += '</td></tr></table>';
	
	conteudo += '<input type="button" class="class_botao" name="btn_alt" id="btn_alt" value="Alterar" onclick=if(confirm("Deseja alterar os dados do índice?")){xajax_atualizar_hist(xajax.getFormValues("frm_hist"))}; disabled="disabled">  ';
	
	conteudo += '<input type="button" class="class_botao" name="btn_voltar" id="btn_voltar" value="Voltar" onclick=divPopupInst.destroi();>';
	
	conteudo += '<div id="indices_hist" style="width:100%"> </div></form>';	
	
	modal(conteudo, 'm', 'HISTÓRICO');	

	xajax_hist(id_indice);
}

</script>

<?php

$conf = new configs();

$db = new banco_dados;

$array_indice_values[] = '';
$array_indice_output[] = 'SELECIONE';

$sql = "SELECT * FROM ".DATABASE.".tipos_indices ";
$sql .= "WHERE tipos_indices.reg_del = 0 ";
$sql .= "ORDER BY tipos_indices.ordem ";

$db->select($sql,'MYSQL',true);

foreach ($db->array_select as $regs)
{
	$array_indice_values[] = $regs["id_tipo_indice"];
	$array_indice_output[] = $regs["indice"];
}

$smarty->assign("option_indice_values",$array_indice_values);
$smarty->assign("option_indice_output",$array_indice_output);

$smarty->assign('campo', $conf->campos('indice_fpv'));

$smarty->assign('revisao_documento', 'V1');

$smarty->assign("classe",CSS_FILE);

$smarty->display('indice_fpv.tpl');

?>