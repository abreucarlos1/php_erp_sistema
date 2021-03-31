<?php
/*
		Formulário de Tarifas Mobilização	
		
		Criado por Carlos Abreu  
		
		local/Nome do arquivo:
		../orcamento/tarifas_mobilizacao.php
		
		Versão 0 --> VERSÃO INICIAL - Carlos Abreu - 11/08/2017
		Versão 1 --> Inclusão dos campos reg_del nas consultas - 22/11/2017 - Carlos Abreu	
*/

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(604))
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

function cidades($dados_form,$selecionado=-1)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	$select = false;

	$resposta->addScript("combo_destino = document.getElementById('id_cidade');");
	
	$resposta->addScriptCall("limpa_combo('id_cidade')");
	
	$resposta->addScript("combo_destino.options[combo_destino.length] = new Option('LOCAL/EMPRESA', '0');");	
	
	/*
	$sql = "SELECT * FROM CC2010 ";
	$sql .= "WHERE CC2010.D_E_L_E_T_ = '' ";
	$sql .= "AND CC2_EST = '".$dados_form["id_estado"]."' ";
	$sql .= "ORDER BY CC2_MUN ";
	
	$db->select($sql,'MSSQL',true);
	
	foreach ($db->array_select as $regs)
	{
		if($regs["CC2_CODMUN"]==$selecionado)
		{
			$select = 'true';
		}
		else
		{
			$select = 'false';
		}
		
		$resposta->addScript("combo_destino.options[combo_destino.length] = new Option('".trim($regs["CC2_MUN"]) . "', '".trim(intval($regs["CC2_CODMUN"]))."',false,".$select.");");
	}
	*/
	
	return $resposta;
}

function atividades($dados_form,$selecionado=-1)
{	
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	$select = false;
	
	//mostra as despesas do filtro quando escolhido o cliente
	$array_filtro = array("1254","1267","1265","1266");

	$resposta->addScript("combo_destino = document.getElementById('id_atividade');");
	
	$resposta->addScriptCall("limpa_combo('id_atividade')");
	
	$resposta->addScript("combo_destino.options[combo_destino.length] = new Option('ESCOLHA A DESPESA', '');");	

	//seleciona as atividades despesas
	$sql = "SELECT * FROM ".DATABASE.".atividades ";
	$sql .= "WHERE atividades.cod = 29 "; //setor Despesas
	$sql .= "AND atividades.reg_del = 0 ";
	
	if($dados_form["id_cidade"]!=0)
	{
		$sql_filtro = implode(",",$array_filtro);
		
		$sql .= "AND atividades.id_atividade IN (".$sql_filtro.") ";
	}	
	
	$sql .= "ORDER BY descricao ";
	
	$db->select($sql,'MYSQL',true);
	
	foreach ($db->array_select as $regs)
	{
		if($regs["id_atividade"]==$selecionado)
		{
			$select = 'true';
		}
		else
		{
			$select = 'false';
		}
		
		$resposta->addScript("combo_destino.options[combo_destino.length] = new Option('".$regs["codigo"] . ' - ' .$regs["descricao"]."', '".$regs["id_atividade"]."',false,".$select.");");
	}
	
	return $resposta;
}

function atualizatabela($dados_form)
{	
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;

	$xml = new XMLWriter();
	$xml->openMemory();
	$xml->startElement('rows');
	
	/*
	$sql = "SELECT * FROM CC2010 ";
	$sql .= "WHERE CC2010.D_E_L_E_T_ = '' ";
	$sql .= "AND CC2_EST = '".$dados_form["id_estado"]."' ";
	$sql .= "ORDER BY CC2_MUN ";
	
	$db->select($sql,'MSSQL',true);
	
	foreach ($db->array_select as $regs)
	{
		$array_cidades[trim(intval($regs["CC2_CODMUN"]))] = trim($regs["CC2_MUN"]);	
	}
	*/	
	
	//seleciona os profissionais cadastrados
	$sql = "SELECT * FROM ".DATABASE.".atividades, ".DATABASE.".tabela_valor_mobilizacao ";
	$sql .= "WHERE tabela_valor_mobilizacao.reg_del = 0 ";
	$sql .= "AND atividades.reg_del = 0 ";
	$sql .= "AND tabela_valor_mobilizacao.id_atividade = atividades.id_atividade ";
	$sql .= "AND tabela_valor_mobilizacao.estado = '".$dados_form["id_estado"]."' ";
	$sql .= "AND tabela_valor_mobilizacao.id_cidade = '".$dados_form["id_cidade"]."' ";
	$sql .= "ORDER BY tabela_valor_mobilizacao.id_cidade, atividades.descricao ";
	
	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}
	
	$array_regs = $db->array_select;

	foreach($array_regs as $regs)
	{
		if($regs["id_cidade"]==0)
		{
			$cidade = "LOCAL";
		}
		else
		{
			$cidade = $array_cidades[$regs["id_cidade"]];
		}
		
		//valor e data atual
		$sql = "SELECT * FROM ".DATABASE.".tabela_valor_mobilizacao_historico ";
		$sql .= "WHERE tabela_valor_mobilizacao_historico.id_tabela_valor_mobilizacao = '" . $regs["id_tabela_valor_mobilizacao"] . "' ";
		$sql .= "AND tabela_valor_mobilizacao_historico.reg_del = 0 ";
		$sql .= "ORDER BY tabela_valor_mobilizacao_historico.id_tabela_valor_mobilizacao_historico DESC, tabela_valor_mobilizacao_historico.data_alteracao DESC LIMIT 1 ";

		$db->select($sql,'MYSQL',true);

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
			
			return $resposta;
		}
		
		$regs1 = $db->array_select[0];

		$xml->startElement('row');
			$xml->writeAttribute('id', $regs['id_tabela_valor_mobilizacao']);
			$xml->writeElement('cell', $cidade);
			$xml->writeElement('cell', $regs['descricao']);
			$xml->writeElement('cell', number_format($regs1['valor'],2,",","."));
			$xml->writeElement('cell', mysql_php($regs1["data_alteracao"]));
			$xml->writeElement('cell', '<img style="cursor:pointer;" src="'.DIR_IMAGENS.'detalhes.png" onclick=historico("'.$regs["id_tabela_valor_mobilizacao"].'","'.str_replace(" "," ",$regs["descricao"]).'");>');
			$xml->writeElement('cell', '<img style="cursor:pointer;" src="'.DIR_IMAGENS.'apagar.png" onclick=if(confirm("Deseja excluir os dados do valor? Todo o histórico será excluido!")){xajax_excluir("'.$regs["id_tabela_valor_mobilizacao"].'")};>');
		$xml->endElement();
	}
	
	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('valores', true, '400', '".$conteudo."');");
	
	$resposta->addScript("xajax_atividades(xajax.getFormValues('frm'))");

	return $resposta;
}

function inserir($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	if($dados_form["id_atividade"]!=0 && !empty($dados_form["valor"]))
	{	
		//verifica se o valor já está cadastrado, caso esteja, incluir como histórico
		$sql = "SELECT * FROM ".DATABASE.".tabela_valor_mobilizacao ";
		$sql .= "WHERE tabela_valor_mobilizacao.reg_del = 0 ";
		$sql .= "AND tabela_valor_mobilizacao.estado = '".$dados_form["id_estado"]."' ";
		$sql .= "AND tabela_valor_mobilizacao.id_cidade = '".$dados_form["id_cidade"]."' ";
		$sql .= "AND tabela_valor_mobilizacao.id_atividade = '" . $dados_form["id_atividade"]."' ";		
	
		$db->select($sql,'MYSQL',true);
	
		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
			
			return $resposta;
		}
	
		$regs_val = $db->array_select[0];
		
		//Insere um novo registro
		if($db->numero_registros<=0)
		{
			//Insere o tipo de indice
			$isql = "INSERT INTO ".DATABASE.".tabela_valor_mobilizacao(estado, id_cidade, id_atividade) VALUES(";
			$isql .= "'" . $dados_form["id_estado"] . "', ";
			$isql .= "'" . $dados_form["id_cidade"] . "', ";
			$isql .= "'" . $dados_form["id_atividade"] . "') ";
	
			$db->insert($isql,'MYSQL');
	
			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
				
				return $resposta;
			}
			
			$id_valor = $db->insert_id;
			
			//insere o historico
			$isql = "INSERT INTO ".DATABASE.".tabela_valor_mobilizacao_historico(id_tabela_valor_mobilizacao, valor, id_funcionario, data_alteracao) VALUES(";
			$isql .= "'" . $id_valor . "', ";
			$isql .= "'" . str_replace(",",".",str_replace(".","",$dados_form["valor"])) . "', ";
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
			$usql = "UPDATE ".DATABASE.".tabela_valor_mobilizacao SET ";
			$usql .= "id_tabela_valor_atual = '".$id_historico."' ";
			$usql .= "WHERE id_tabela_valor_mobilizacao = '".$id_valor."' ";
			$usql .= "AND reg_del = 0 ";
	
			$db->update($usql,'MYSQL');
	
			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
				
				return $resposta;
			}		
			
			$resposta->addAlert("valor inserido com sucesso.");		
	
		}
		else
		{
			$id_tabela_mo = $regs_val["id_tabela_valor_mobilizacao"];
					
			//insere o historico
			$isql = "INSERT INTO ".DATABASE.".tabela_valor_mobilizacao_historico(id_tabela_valor_mobilizacao, valor, id_funcionario, data_alteracao) VALUES(";
			$isql .= "'" . $id_tabela_mo . "', ";
			$isql .= "'" . str_replace(",",".",str_replace(".","",$dados_form["valor"])) . "', ";
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
			$usql = "UPDATE ".DATABASE.".tabela_valor_mobilizacao SET ";
			$usql .= "id_tabela_valor_atual = '".$id_historico."' ";
			$usql .= "WHERE id_tabela_valor_mobilizacao = '".$id_tabela_mo."' ";
			$usql .= "AND reg_del = 0 ";
	
			$db->update($usql,'MYSQL');
	
			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
				
				return $resposta;
			}		
			
			$resposta->addAlert("valor atualizado com sucesso.");	
		}
		
		$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'))");
	}
	else
	{
		$resposta->addAlert("Os campos devem estar preenchidos.");	
	}

	return $resposta;
}

function editar($id_valor)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;

	//seleciona o valor
	$sql = "SELECT * FROM ".DATABASE.".tabela_valor_mobilizacao ";
	$sql .= "WHERE tabela_valor_mobilizacao.id_tabela_valor_mobilizacao = '".$id_valor."' ";
	$sql .= "AND tabela_valor_mobilizacao.reg_del = 0 ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}

	$regs = $db->array_select[0];

	//valor atual
	$sql = "SELECT * FROM ".DATABASE.".tabela_valor_mobilizacao_historico ";
	$sql .= "WHERE tabela_valor_mobilizacao_historico.id_tabela_valor_mobilizacao_historico = '" . $regs["id_tabela_valor_atual"] . "' ";
	$sql .= "AND tabela_valor_mobilizacao_historico.reg_del = 0 ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}
	
	$regs1 = $db->array_select[0];

	$resposta->addAssign("id_valor","value", $regs["id_tabela_valor_mobilizacao"]);

	$resposta->addScript("xajax_atividades(xajax.getFormValues('frm'),'".$regs["id_atividade"]."')");
	
	$resposta->addAssign("data","value",mysql_php($regs1["data_alteracao"]));

	$resposta->addAssign("valor","value",number_format($regs1["valor"],2,",","."));	
	
	$resposta->addAssign("btn_atualizar", "value", "Atualizar");
	
	$resposta->addEvent("btn_atualizar", "onclick", "if(confirm('Deseja alterar os dados do valor?')){xajax_atualizar(xajax.getFormValues('frm'));}");
	
	$resposta->addEvent("btnvoltar", "onclick", "xajax_voltar();");

	return $resposta;
}

//só atualiza o item atual
function atualizar($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	if($dados_form["id_atividade"]!=0 && !empty($dados_form["valor"]))
	{
		//seleciona o valor
		$sql = "SELECT * FROM ".DATABASE.".tabela_valor_mobilizacao ";
		$sql .= "WHERE tabela_valor_mobilizacao.id_tabela_valor_mobilizacao = '".$dados_form["id_valor"]."' ";
		$sql .= "AND tabela_valor_mobilizacao.reg_del = 0 ";
	
		$db->select($sql,'MYSQL',true);
	
		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
			
			return $resposta;
		}
	
		$regs = $db->array_select[0];
		
		$usql = "UPDATE ".DATABASE.".tabela_valor_mobilizacao_historico SET ";
		$usql .= "valor = '".str_replace(",",".",str_replace(".","",$dados_form["valor"]))."', ";
		$usql .= "id_funcionario = '".$_SESSION["id_funcionario"]."', ";
		$usql .= "data_alteracao = '".php_mysql($dados_form["data"])."' ";
		$usql .= "WHERE id_tabela_valor_mobilizacao_historico = '".$regs["id_tabela_valor_atual"]."' ";
		$usql .= "AND reg_del = 0 ";
		
		$db->update($usql,'MYSQL');

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
			
			return $resposta;
		}
		
		$resposta->addAlert("valor atualizado com sucesso.");
		
		$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'))");
		
	}
	else
	{
		$resposta->addAlert("Os campos devem estar preenchidos.");		
	}
	
	return $resposta;
}

//exclui todos os itens
function excluir($id_valor)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	$usql = "UPDATE ".DATABASE.".tabela_valor_mobilizacao SET ";
	$usql .= "reg_del = 1, ";
	$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
	$usql .= "data_del = '".date('Y-m-d')."' ";
	$usql .= "WHERE id_tabela_valor_mobilizacao = '".$id_valor."' ";
	$usql .= "AND reg_del = 0 ";
	
	$db->update($usql,'MYSQL');

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}
	
	$usql = "UPDATE ".DATABASE.".tabela_valor_mobilizacao_historico SET ";
	$usql .= "reg_del = 1, ";
	$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
	$usql .= "data_del = '".date('Y-m-d')."' ";
	$usql .= "WHERE id_tabela_valor_mobilizacao = '".$id_valor."' ";
	$usql .= "AND reg_del = 0 ";
	
	$db->update($usql,'MYSQL');

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}
	
	$resposta->addAlert("valor excluído com sucesso.");
	
	$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'))");
	
	return $resposta;
}

function hist($id_valor)
{	
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	$comb_atividade = '';	
	
	/*
	$sql = "SELECT * FROM CC2010 ";
	$sql .= "WHERE CC2010.D_E_L_E_T_ = '' ";
	$sql .= "AND CC2_EST = '".$dados_form["id_estado"]."' ";
	$sql .= "ORDER BY CC2_MUN ";
	
	$db->select($sql,'MSSQL',true);
	
	foreach ($db->array_select as $regs)
	{
		$array_cidades[trim($regs["CC2_CODMUN"])] = trim($regs["CC2_MUN"]);	
	}
	*/
	
	$sql = "SELECT * FROM ".DATABASE.".tabela_valor_mobilizacao ";
	$sql .= "WHERE tabela_valor_mobilizacao.reg_del = 0 ";
	$sql .= "AND tabela_valor_mobilizacao.id_tabela_valor_mobilizacao = '" . $id_valor . "' ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}
	
	$regs0 = $db->array_select[0];
	
	if($regs0["id_cidade"]==0)
	{
		$cliente = 'LOCAL';
	}
	else
	{
		$cliente = $array_cidades[$regs0["id_cidade"]];	
	}
	
	//monta o corpo do modal de historico
    $conteudo = '<table width="100%" border="0">';
    $conteudo .= '	<tr>';
    $conteudo .= '		<td width="13%"><label for="regiao_hist" class="labels">Cidade</label><br />';
    $conteudo .= '		<label class="labels">'.$cliente.'</label><br />';
	$conteudo .= '       </td>';
    $conteudo .= '       <td width="8%"><label for="data_hist" class="labels">Data</label><br />';
    $conteudo .= '	      <input name="data_hist" type="text" class="caixa" id="data_hist" size="10" maxlength="10" onkeypress="return txtBoxFormat(document.frm_hist, \'data_hist\', \'99/99/9999\', event);" value="" /></td>';
    $conteudo .= '       <td width="56%"><label for="valor_hist" class="labels">valor</label><br />'; 
    $conteudo .= '	      <input name="valor_hist" type="text" class="caixa" id="valor_hist" size="7" placeholder="valor" maxlength="7" /></td>';
    $conteudo .= '  </tr>';
    $conteudo .= '</table>';
	
	$resposta->addAssign("corpo","innerHTML",$conteudo);

	$xml = new XMLWriter();
	$xml->openMemory();
	$xml->startElement('rows');

	//seleciona os valores cadastrados
	$sql = "SELECT * FROM ".DATABASE.".tabela_valor_mobilizacao, ".DATABASE.".tabela_valor_mobilizacao_historico, ".DATABASE.".atividades ";
	$sql .= "WHERE tabela_valor_mobilizacao.reg_del = 0 ";
	$sql .= "AND tabela_valor_mobilizacao_historico.reg_del = 0 ";
	$sql .= "AND atividades.reg_del = 0 ";
	$sql .= "AND tabela_valor_mobilizacao.id_tabela_valor_mobilizacao = '" . $id_valor . "' ";
	$sql .= "AND tabela_valor_mobilizacao.id_atividade = atividades.id_atividade ";
	$sql .= "AND tabela_valor_mobilizacao.id_tabela_valor_mobilizacao = tabela_valor_mobilizacao_historico.id_tabela_valor_mobilizacao ";
	$sql .= "ORDER BY tabela_valor_mobilizacao.id_tabela_valor_mobilizacao, atividades.descricao ";
	
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
		if($regs["id_tabela_valor_mobilizacao_historico"]==$regs["id_tabela_valor_atual"] && count($array_regs)==1)
		{
			$img = ' ';	
		}
		else
		{
			
			$img = 	'<img style="cursor:pointer;" src="'.DIR_IMAGENS.'apagar.png" onclick=if(confirm("Deseja excluir os dados do valor?")){xajax_excluir_hist("'.$regs["id_tabela_valor_mobilizacao_historico"].'")};>';	
		}
		
		$xml->startElement('row');
			$xml->writeAttribute('id', $regs['id_tabela_valor_mobilizacao_historico']);
			$xml->writeElement('cell', $cidade);
			$xml->writeElement('cell', $regs['descricao']);
			$xml->writeElement('cell', number_format($regs['valor'],2,",","."));
			$xml->writeElement('cell', mysql_php($regs["data_alteracao"]));
			$xml->writeElement('cell', $img);
		$xml->endElement();
	}
	
	$xml->endElement();
	
	$cont = $xml->outputMemory(false);
	
	$resposta->addScript("grid('valores_hist', true, '250', '".$cont."');");

	return $resposta;
}

function editar_hist($id_valor_hist)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	//valor historico
	$sql = "SELECT * FROM ".DATABASE.".tabela_valor_mobilizacao_historico ";
	$sql .= "WHERE tabela_valor_mobilizacao_historico.id_tabela_valor_mobilizacao_historico = '" . $id_valor_hist . "' ";
	$sql .= "AND tabela_valor_mobilizacao_historico.reg_del = 0 ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}
	
	$regs = $db->array_select[0];

	//seleciona o valor
	$sql = "SELECT * FROM ".DATABASE.".tabela_valor_mobilizacao ";
	$sql .= "WHERE tabela_valor_mobilizacao.id_tabela_valor_mobilizacao = '".$regs["id_tabela_valor_mobilizacao"]."' ";
	$sql .= "AND tabela_valor_mobilizacao.reg_del = 0 ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}

	$regs1 = $db->array_select[0];

	$resposta->addAssign("id_valor_historico","value", $regs["id_tabela_valor_mobilizacao_historico"]);
	
	$resposta->addAssign("data_hist","value",mysql_php($regs["data_alteracao"]));

	$resposta->addAssign("valor_hist","value",number_format($regs["valor"],2,",","."));
	
	$resposta->addScript("document.getElementById('btn_alt').disabled = false");	

	return $resposta;
}

//atualiza o historico
function atualizar_hist($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	if(!empty($dados_form["valor_hist"]))
	{
		
		$usql = "UPDATE ".DATABASE.".tabela_valor_mobilizacao_historico SET ";
		$usql .= "valor = '".str_replace(",",".",str_replace(".","",$dados_form["valor_hist"]))."', ";
		$usql .= "id_funcionario = '".$_SESSION["id_funcionario"]."', ";
		$usql .= "data_alteracao = '".php_mysql($dados_form["data_hist"])."' ";
		$usql .= "WHERE id_tabela_valor_mobilizacao_historico = '".$dados_form["id_valor_historico"]."' ";
		$usql .= "AND reg_del = 0 ";
		
		$db->update($usql,'MYSQL');

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
			
			return $resposta;
		}
		
		$resposta->addScript("xajax_hist('".$dados_form["id_valor"]."')");
		
		$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'))");		
	}
	else
	{
		$resposta->addAlert("Os campos devem estar preenchidos.");		
	}
	
	return $resposta;
}

function excluir_hist($id_valor_hist)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;

	//seleciona o histórico para obter o id_valor
	$sql = "SELECT * FROM ".DATABASE.".tabela_valor_mobilizacao_historico ";
	$sql .= "WHERE tabela_valor_mobilizacao_historico.id_tabela_valor_mobilizacao_historico = '".$id_valor_hist."' ";
	$sql .= "AND tabela_valor_mobilizacao_historico.reg_del = 0 ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}
	
	$regs = $db->array_select[0];
	
	$id_valor = $regs["id_tabela_valor_mobilizacao"];
	
	//exclui o registro do histórico
	$usql = "UPDATE ".DATABASE.".tabela_valor_mobilizacao_historico SET ";
	$usql .= "reg_del = 1, ";
	$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
	$usql .= "data_del = '".date('Y-m-d')."' ";
	$usql .= "WHERE id_tabela_valor_mobilizacao_historico = '".$id_valor_hist."' ";
	$usql .= "AND reg_del = 0 ";
	
	$db->update($usql,'MYSQL');

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}
	
	//seleciona o histórico para obter o último registro
	$sql = "SELECT * FROM ".DATABASE.".tabela_valor_mobilizacao_historico ";
	$sql .= "WHERE tabela_valor_mobilizacao_historico.id_tabela_valor_mobilizacao = '".$id_valor."' ";
	$sql .= "AND tabela_valor_mobilizacao_historico.reg_del = 0 ";
	$sql .= "ORDER BY tabela_valor_mobilizacao_historico.id_tabela_valor_mobilizacao_historico DESC, tabela_valor_mobilizacao_historico.data_alteracao DESC LIMIT 1 ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}
	
	$regs1 = $db->array_select[0];

	//atualiza o registro de índices para o atual
	$usql = "UPDATE ".DATABASE.".tabela_valor_mobilizacao SET ";
	$usql .= "id_tabela_valor_atual = '".$regs1["id_tabela_valor_mobilizacao_historico"]."' ";
	$usql .= "WHERE id_tabela_valor_mobilizacao = '".$id_valor."' ";
	$usql .= "AND reg_del = 0 ";

	$db->update($usql,'MYSQL');

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}
	
	$resposta->addScript("xajax_hist('".$id_valor."')");	
	
	$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'))");
	
	return $resposta;
}
	
$xajax->registerFunction("voltar");
$xajax->registerFunction("inserir");
$xajax->registerFunction("editar");
$xajax->registerFunction("atualizar");
$xajax->registerFunction("atualizatabela");
$xajax->registerFunction("atividades");
$xajax->registerFunction("cidades");
$xajax->registerFunction("excluir");
$xajax->registerFunction("hist");
$xajax->registerFunction("editar_hist");
$xajax->registerFunction("atualizar_hist");
$xajax->registerFunction("excluir_hist");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","xajax_cidades(xajax.getFormValues('frm'));xajax_atualizatabela(xajax.getFormValues('frm'));");

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script language="javascript">

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
		case 'valores':
			function doOnRowSelected1(row,col)
			{
				if(col<=3)
				{						
					xajax_editar(row);
		
					return true;
				}
			}
		
			mygrid.setHeader("Cidade, Despesa, Valor, Data, H, E");
			mygrid.setInitWidths("150,*,120,100,30,30");
			mygrid.setColAlign("left,left,left,left,center,center");
			mygrid.setColTypes("ro,ro,ro,ro,ro,ro");
			mygrid.setColSorting("str,str,str,str,str,str");
		
			mygrid.attachEvent("onRowSelect",doOnRowSelected1);
		break;
		
		case 'valores_hist':
			function doOnRowSelected2(row,col)
			{
				if(col<=2)
				{						
					xajax_editar_hist(row);
		
					return true;
				}
			}
		
			mygrid.setHeader("Cidade, Despesa, Valor, Data, E");
			mygrid.setInitWidths("150,*,120,100,30");
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

function historico(id_valor,descricao)
{
	conteudo = '<form name="frm_hist" id="frm_hist" action="" method="POST">';
	
	conteudo += '<table width="100%">';
	
	conteudo += '<tr><td>';
	
	conteudo += '<label class="labels">'+descricao+'</label>';
	conteudo += '<input type="hidden" name="id_valor" id="id_valor" value="'+id_valor+'">';
	conteudo += '<input type="hidden" name="id_valor_historico" id="id_valor_historico" value="">';
	
	conteudo += '</tr></td>';
	
	conteudo += '<tr><td class="espacamento">';

	conteudo += '<div id="corpo" style="width:100%;"> </div>';
	
	conteudo += '</td></tr></table>';
	
	conteudo += '<input type="button" class="class_botao" name="btn_alt" id="btn_alt" value="Alterar" onclick=if(confirm("Deseja alterar os dados do valor?")){xajax_atualizar_hist(xajax.getFormValues("frm_hist"))}; disabled="disabled">  ';
	
	conteudo += '<input type="button" class="class_botao" name="btn_voltar" id="btn_voltar" value="Voltar" onclick=divPopupInst.destroi();>';
	
	conteudo += '<div id="valores_hist" style="width:100%"> </div></form>';	
	
	modal(conteudo, 'g', 'HISTÓRICO');	

	xajax_hist(id_valor);
}

</script>

<?php

$conf = new configs();

$db = new banco_dados;

$array_estado_values[] = '';
$array_estado_output[] = 'SELECIONE';

//TABELA DE ESTADOS
/*
$sql = "SELECT * FROM SX5010 ";
$sql .= "WHERE SX5010.D_E_L_E_T_ = '' ";
$sql .= "AND X5_TABELA = '12' ";
$sql .= "ORDER BY X5_DESCRI ";

$db->select($sql,'MSSQL',true);

foreach ($db->array_select as $regs)
{
	$array_estado_values[] = trim($regs["X5_CHAVE"]);
	$array_estado_output[] = trim($regs["X5_DESCRI"]);
}
*/

$smarty->assign("option_estado_values",$array_estado_values);
$smarty->assign("option_estado_output",$array_estado_output);
$smarty->assign("selecionado1","SP");

$smarty->assign('campo', $conf->campos('tarifas_mobilizacao'));

$smarty->assign('revisao_documento', 'V1');

$smarty->assign("classe",CSS_FILE);

$smarty->display('tarifas_mobilizacao.tpl');

?>