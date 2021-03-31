<?php
/*
		Formulário de Tarifas MO	
		
		Criado por Carlos Abreu  
		
		local/Nome do arquivo:
		../orcamento/tarifas_mo.php
		
		Versão 0 --> VERSÃO INICIAL - Carlos Abreu - 13/04/2017
		Versão 1 --> Inclusão dos campos reg_del nas consultas - 22/11/2017 - Carlos Abreu
		Versão 2 --> Retirada do campo categoria orcamento - 07/02/2018 - Carlos Abreu	
*/

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(593))
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

	//seleciona os profissionais cadastrados
	$sql = "SELECT * FROM ".DATABASE.".regiao, ".DATABASE.".tabela_valor_mo, ".DATABASE.".rh_cargos, ".DATABASE.".rh_categorias ";
	$sql .= "WHERE tabela_valor_mo.reg_del = 0 ";
	$sql .= "AND regiao.reg_del = 0 ";
	$sql .= "AND rh_categorias.reg_del = 0 ";
	$sql .= "AND rh_cargos.reg_del = 0 ";
	$sql .= "AND rh_cargos.id_categoria = rh_categorias.id_categoria ";
	$sql .= "AND tabela_valor_mo.id_cargo = rh_cargos.id_cargo_grupo ";
	$sql .= "AND tabela_valor_mo.id_regiao = regiao.id_regiao ";
	$sql .= "AND regiao.id_regiao = '".$dados_form["regiao"]."' ";
	$sql .= "ORDER BY tabela_valor_mo.id_regiao, rh_cargos.ordem_tarifas ";
	
	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}
	
	$array_regs = $db->array_select;

	foreach($array_regs as $regs)
	{
		//valor e data atual
		$sql = "SELECT * FROM ".DATABASE.".tabela_valor_mo_historico ";
		$sql .= "WHERE tabela_valor_mo_historico.id_tabela_valor_mo = '" . $regs["id_tabela_valor_mo"] . "' ";
		$sql .= "AND tabela_valor_mo_historico.reg_del = 0 ";
		$sql .= "ORDER BY id_tabela_valor_mo_historico DESC, data_alteracao DESC LIMIT 1 ";

		$db->select($sql,'MYSQL',true);

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
			
			return $resposta;
		}
		
		$regs1 = $db->array_select[0];

		$xml->startElement('row');
			$xml->writeAttribute('id', $regs['id_tabela_valor_mo']);
			$xml->writeElement('cell', $regs["regiao"]);
			$xml->writeElement('cell', $regs['grupo']);
			$xml->writeElement('cell', number_format($regs1['valor'],2,",","."));
			$xml->writeElement('cell', mysql_php($regs1["data_alteracao"]));
			$xml->writeElement('cell', '<img style="cursor:pointer;" src="'.DIR_IMAGENS.'detalhes.png" onclick=historico("'.$regs["id_tabela_valor_mo"].'","'.str_replace(" "," ",$regs["grupo"]).'");>');
			$xml->writeElement('cell', '<img style="cursor:pointer;" src="'.DIR_IMAGENS.'apagar.png" onclick=if(confirm("Deseja excluir os dados do índice? Todo o histórico será excluído!")){xajax_excluir("'.$regs["id_tabela_valor_mo"].'")};>');
		$xml->endElement();
	}
	
	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('valores', true, '400', '".$conteudo."');");

	return $resposta;
}

function inserir($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	if($dados_form["regiao"]!=0 && $dados_form["id_cargo"]!=0 && !empty($dados_form["valor"]))
	{	
		//verifica se o valor já esta cadastrado, caso esteja, incluir como historico
		$sql = "SELECT * FROM ".DATABASE.".tabela_valor_mo ";
		$sql .= "WHERE tabela_valor_mo.id_regiao = '".$dados_form["regiao"]."' ";
		$sql .= "AND tabela_valor_mo.id_cargo = '" . $dados_form["id_cargo"]."' ";
		$sql .= "AND tabela_valor_mo.reg_del = 0 ";
	
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
			$isql = "INSERT INTO ".DATABASE.".tabela_valor_mo(id_regiao, id_cargo) VALUES(";
			$isql .= "'" . $dados_form["regiao"] . "', ";
			$isql .= "'" . $dados_form["id_cargo"] . "') ";
	
			$db->insert($isql,'MYSQL');
	
			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
				
				return $resposta;
			}
			
			$id_valor = $db->insert_id;
			
			//insere o historico
			$isql = "INSERT INTO ".DATABASE.".tabela_valor_mo_historico(id_tabela_valor_mo, valor, id_funcionario, data_alteracao) VALUES(";
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
			$usql = "UPDATE ".DATABASE.".tabela_valor_mo SET ";
			$usql .= "id_tabela_valor_atual = '".$id_historico."' ";
			$usql .= "WHERE id_tabela_valor_mo = '".$id_valor."' ";
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
			$id_tabela_mo = $regs_val["id_tabela_valor_mo"];
					
			//insere o historico
			$isql = "INSERT INTO ".DATABASE.".tabela_valor_mo_historico(id_tabela_valor_mo, valor, id_funcionario, data_alteracao) VALUES(";
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
			$usql = "UPDATE ".DATABASE.".tabela_valor_mo SET ";
			$usql .= "id_tabela_valor_atual = '".$id_historico."' ";
			$usql .= "WHERE id_tabela_valor_mo = '".$id_tabela_mo."' ";
			$usql .= "AND reg_del = 0 ";
	
			$db->update($usql,'MYSQL');
	
			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
				
				return $resposta;
			}		
			
			$resposta->addAlert("Indice atualizado com sucesso.");	
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
	$sql = "SELECT * FROM ".DATABASE.".tabela_valor_mo ";
	$sql .= "WHERE tabela_valor_mo.id_tabela_valor_mo = '".$id_valor."' ";
	$sql .= "AND tabela_valor_mo.reg_del = 0 ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}

	$regs = $db->array_select[0];

	//valor atual
	$sql = "SELECT * FROM ".DATABASE.".tabela_valor_mo_historico ";
	$sql .= "WHERE id_tabela_valor_mo_historico = '" . $regs["id_tabela_valor_atual"] . "' ";
	$sql .= "AND tabela_valor_mo_historico.reg_del = 0 ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}
	
	$regs1 = $db->array_select[0];

	$resposta->addAssign("id_valor","value", $regs["id_tabela_valor_mo"]);

	$resposta->addScript("seleciona_combo('" . $regs["id_regiao"] . "', 'regiao'); ");
	
	$resposta->addScript("seleciona_combo('" . $regs["id_cargo"] . "', 'id_cargo'); ");
	
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
	
	if($dados_form["regiao"]!=0 && $dados_form["id_cargo"]!=0 && !empty($dados_form["valor"]))
	{
		//seleciona o valor
		$sql = "SELECT * FROM ".DATABASE.".tabela_valor_mo ";
		$sql .= "WHERE tabela_valor_mo.id_tabela_valor_mo = '".$dados_form["id_valor"]."' ";
		$sql .= "AND tabela_valor_mo.reg_del = 0 ";
	
		$db->select($sql,'MYSQL',true);
	
		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
			
			return $resposta;
		}
	
		$regs = $db->array_select[0];
		
		$usql = "UPDATE ".DATABASE.".tabela_valor_mo SET ";
		$usql .= "id_regiao = '".$dados_form["regiao"]."', ";
		$usql .= "id_cargo = '".$dados_form["id_cargo"]."' ";
		$usql .= "WHERE id_tabela_valor_mo = '".$dados_form["id_valor"]."' ";
		$usql .= "AND reg_del = 0 ";
		
		$db->update($usql,'MYSQL');

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
			
			return $resposta;
		}
		
		$usql = "UPDATE ".DATABASE.".tabela_valor_mo_historico SET ";
		$usql .= "valor = '".str_replace(",",".",str_replace(".","",$dados_form["valor"]))."', ";
		$usql .= "id_funcionario = '".$_SESSION["id_funcionario"]."', ";
		$usql .= "data_alteracao = '".php_mysql($dados_form["data"])."' ";
		$usql .= "WHERE id_tabela_valor_mo_historico = '".$regs["id_tabela_valor_atual"]."' ";
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

//exclui todos os itens
function excluir($id_valor)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	$usql = "UPDATE ".DATABASE.".tabela_valor_mo SET ";
	$usql .= "reg_del = 1, ";
	$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
	$usql .= "data_del = '".date('Y-m-d')."' ";
	$usql .= "WHERE id_tabela_valor_mo = '".$id_valor."' ";
	$usql .= "AND reg_del = 0 ";
	
	$db->update($usql,'MYSQL');

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}
	
	$usql = "UPDATE ".DATABASE.".tabela_valor_mo_historico SET ";
	$usql .= "reg_del = 1, ";
	$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
	$usql .= "data_del = '".date('Y-m-d')."' ";
	$usql .= "WHERE id_tabela_valor_mo = '".$id_valor."' ";
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

function hist($id_valor)
{	
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	$comb_regiao = '';
	$comb_cargo = '';
	
	$sql = "SELECT * FROM ".DATABASE.".regiao ";
	$sql .= "WHERE regiao.reg_del = 0 ";

	$db->select($sql,'MYSQL',true);
	
	foreach ($db->array_select as $regs)
	{
		if($regs["id_regiao"]==1)//SÃO PAULO
		{
			$selected = 'selected';
		}
		else
		{
			$selected = '';
		}	
		
		$comb_regiao .= '<option value="'.$regs["id_regiao"].'" '.$selected.'>'.$regs["regiao"].'</option>';	
	}
	
	$sql = "SELECT * FROM ".DATABASE.".atividades_orcamento, ".DATABASE.".rh_cargos ";
	$sql .= "WHERE rh_cargos.id_cargo_grupo = atividades_orcamento.id_cargo ";
	$sql .= "AND atividades_orcamento.reg_del = 0 ";
	$sql .= "AND rh_cargos.reg_del = 0 ";
	$sql .= "AND id_categoria <> 0 ";
	$sql .= "GROUP BY id_cargo_grupo ";
	$sql .= "ORDER BY grupo ";
	
	$db->select($sql,'MYSQL',true);
	
	foreach ($db->array_select as $regs)
	{
		$comb_cargo .= '<option value="'.$regs["id_cargo_grupo"].'">'.$regs["grupo"].'</option>';	
	}
	
	//monta o corpo do modal de historico
    $conteudo = '<table width="100%" border="0">';
    $conteudo .= '	<tr>';
    $conteudo .= '		<td width="13%"><label for="regiao_hist" class="labels">Região</label><br />';
    $conteudo .= '   		<select name="regiao_hist" class="caixa" id="regiao_hist" onkeypress="return keySort(this);" >';
    $conteudo .= 				$comb_regiao;  
    $conteudo .= '          </select>';
    $conteudo .= '       </td>';
    $conteudo .= '       <td width="23%"><label for="id_cargo_hist" class="labels">Profissional</label><br />';
    $conteudo .= '   		<select name="id_cargo_hist" class="caixa" id="id_cargo_hist" onkeypress="return keySort(this);" >';
    $conteudo .= 				$comb_cargo;  
    $conteudo .= '          </select>';
    $conteudo .= '       </td>';
    $conteudo .= '       <td width="8%"><label for="data_hist" class="labels">Data</label><br />';
    $conteudo .= '	      <input name="data_hist" type="text" class="caixa" id="data_hist" size="10" maxlength="10" onkeypress="return txtBoxFormat(document.frm_hist, \'data_hist\', \'99/99/9999\', event);" value="" /></td>';
    $conteudo .= '       <td width="56%"><label for="valor_hist" class="labels">Valor</label><br />'; 
    $conteudo .= '	      <input name="valor_hist" type="text" class="caixa" id="valor_hist" size="7" placeholder="Valor" maxlength="7" /></td>';
    $conteudo .= '  </tr>';
    $conteudo .= '</table>';
	
	$resposta->addAssign("corpo","innerHTML",$conteudo);

	$xml = new XMLWriter();
	$xml->openMemory();
	$xml->startElement('rows');

	//seleciona os valores cadastrados
	$sql = "SELECT * FROM ".DATABASE.".regiao, ".DATABASE.".tabela_valor_mo, ".DATABASE.".tabela_valor_mo_historico, ".DATABASE.".rh_cargos ";
	$sql .= "WHERE tabela_valor_mo.reg_del = 0 ";
	$sql .= "AND tabela_valor_mo_historico.reg_del = 0 ";
	$sql .= "AND regiao.reg_del = 0 ";
	$sql .= "AND rh_cargos.reg_del = 0 ";
	$sql .= "AND tabela_valor_mo.id_tabela_valor_mo = '" . $id_valor . "' ";
	$sql .= "AND tabela_valor_mo.id_cargo = rh_cargos.id_cargo_grupo ";
	$sql .= "AND tabela_valor_mo.id_regiao = regiao.id_regiao ";
	$sql .= "AND tabela_valor_mo.id_tabela_valor_mo = tabela_valor_mo_historico.id_tabela_valor_mo ";
	$sql .= "ORDER BY tabela_valor_mo.id_tabela_valor_mo, rh_cargos.grupo ";
	
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
		if($regs["id_tabela_valor_mo_historico"]==$regs["id_tabela_valor_atual"] && count($array_regs)==1)
		{
			$img = ' ';	
		}
		else
		{
			
			$img = 	'<img style="cursor:pointer;" src="'.DIR_IMAGENS.'apagar.png" onclick=if(confirm("Deseja excluir os dados do valor?")){xajax_excluir_hist("'.$regs["id_tabela_valor_mo_historico"].'")};>';	
		}
		
		$xml->startElement('row');
			$xml->writeAttribute('id', $regs['id_tabela_valor_mo_historico']);
			$xml->writeElement('cell', $regs["regiao"]);
			$xml->writeElement('cell', $regs['grupo']);
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
	$sql = "SELECT * FROM ".DATABASE.".tabela_valor_mo_historico ";
	$sql .= "WHERE tabela_valor_mo_historico.id_tabela_valor_mo_historico = '" . $id_valor_hist . "' ";
	$sql .= "AND tabela_valor_mo_historico.reg_del = 0 ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}
	
	$regs = $db->array_select[0];

	//seleciona o valor
	$sql = "SELECT * FROM ".DATABASE.".tabela_valor_mo ";
	$sql .= "WHERE tabela_valor_mo.id_tabela_valor_mo = '".$regs["id_tabela_valor_mo"]."' ";
	$sql .= "AND tabela_valor_mo.reg_del = 0 ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}

	$regs1 = $db->array_select[0];

	$resposta->addAssign("id_valor_historico","value", $regs["id_tabela_valor_mo_historico"]);

	$resposta->addScript("seleciona_combo('" . $regs1["id_regiao"] . "', 'regiao_hist'); ");
	
	$resposta->addScript("seleciona_combo('" . $regs1["id_cargo"] . "', 'id_cargo_hist'); ");
	
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
	
	if($dados_form["regiao_hist"]!=0 && $dados_form["id_cargo_hist"]!="" && !empty($dados_form["valor_hist"]))
	{
		$usql = "UPDATE ".DATABASE.".tabela_valor_mo SET ";
		$usql .= "id_regiao = '".$dados_form["regiao_hist"]."', ";
		$usql .= "id_cargo = '".$dados_form["id_cargo_hist"]."' ";
		$usql .= "WHERE id_tabela_valor_mo = '".$dados_form["id_valor"]."' ";
		$usql .= "AND reg_del = 0 ";
		
		$db->update($usql,'MYSQL');

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
			
			return $resposta;
		}
		
		$usql = "UPDATE ".DATABASE.".tabela_valor_mo_historico SET ";
		$usql .= "valor = '".str_replace(",",".",str_replace(".","",$dados_form["valor_hist"]))."', ";
		$usql .= "id_funcionario = '".$_SESSION["id_funcionario"]."', ";
		$usql .= "data_alteracao = '".php_mysql($dados_form["data_hist"])."' ";
		$usql .= "WHERE id_tabela_valor_mo_historico = '".$dados_form["id_valor_historico"]."' ";
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
	$sql = "SELECT * FROM ".DATABASE.".tabela_valor_mo_historico ";
	$sql .= "WHERE tabela_valor_mo_historico.id_tabela_valor_mo_historico = '".$id_valor_hist."' ";
	$sql .= "AND tabela_valor_mo_historico.reg_del = 0 ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}
	
	$regs = $db->array_select[0];
	
	$id_valor = $regs["id_tabela_valor_mo"];
	
	//exclui o registro do histórico
	$usql = "UPDATE ".DATABASE.".tabela_valor_mo_historico SET ";
	$usql .= "reg_del = 1, ";
	$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
	$usql .= "data_del = '".date('Y-m-d')."' ";
	$usql .= "WHERE id_tabela_valor_mo_historico = '".$id_valor_hist."' ";
	$usql .= "AND reg_del = 0 ";
	
	$db->update($usql,'MYSQL');

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}
	
	//seleciona o historico para obter o ultimo registro
	$sql = "SELECT * FROM ".DATABASE.".tabela_valor_mo_historico ";
	$sql .= "WHERE tabela_valor_mo_historico.id_tabela_valor_mo = '".$id_valor."' ";
	$sql .= "AND tabela_valor_mo_historico.reg_del = 0 ";
	$sql .= "ORDER BY tabela_valor_mo_historico.id_tabela_valor_mo_historico DESC, tabela_valor_mo_historico.data_alteracao DESC LIMIT 1 ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}
	
	$regs1 = $db->array_select[0];

	//atualiza o registro de indices para o atual
	$usql = "UPDATE ".DATABASE.".tabela_valor_mo SET ";
	$usql .= "id_tabela_valor_atual = '".$regs1["id_tabela_valor_mo_historico"]."' ";
	$usql .= "WHERE id_tabela_valor_mo = '".$id_valor."' ";
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
		
			mygrid.setHeader("Região, Profissional, Valor, Data, H, E");
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
		
			mygrid.setHeader("Região, Profissional, Valor, Data, E");
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
	
	conteudo += '<input type="button" class="class_botao" name="btn_alt" id="btn_alt" value="Alterar" onclick=if(confirm("Deseja alterar os dados do índice?")){xajax_atualizar_hist(xajax.getFormValues("frm_hist"))}; disabled="disabled">  ';
	
	conteudo += '<input type="button" class="class_botao" name="btn_voltar" id="btn_voltar" value="Voltar" onclick=divPopupInst.destroi();>';
	
	conteudo += '<div id="valores_hist" style="width:100%"> </div></form>';	
	
	modal(conteudo, 'g', 'HISTÓRICO');	

	xajax_hist(id_valor);
}

</script>

<?php

$conf = new configs();

$db = new banco_dados;

//seleciona as atividades e os recursos associados
$sql = "SELECT * FROM ".DATABASE.".atividades_orcamento, ".DATABASE.".rh_cargos ";
$sql .= "WHERE rh_cargos.id_cargo_grupo = atividades_orcamento.id_cargo ";
$sql .= "AND atividades_orcamento.reg_del = 0 ";
$sql .= "AND rh_cargos.reg_del = 0 ";
$sql .= "AND id_categoria <> 0 ";
$sql .= "GROUP BY id_cargo_grupo ";
$sql .= "ORDER BY rh_cargos.ordem_tarifas ";

$db->select($sql,'MYSQL',true);

foreach ($db->array_select as $regs)
{
	$array_cargo_values[] = $regs["id_cargo_grupo"];
	$array_cargo_output[] = $regs["grupo"];
}

//seleciona as regioes
$sql = "SELECT * FROM ".DATABASE.".regiao ";
$sql .= "WHERE regiao.reg_del = 0 ";

$db->select($sql,'MYSQL',true);

foreach ($db->array_select as $regs)
{
	$array_regiao_values[] = $regs["id_regiao"];
	$array_regiao_output[] = $regs["regiao"];
}

$smarty->assign("option_cargo_values",$array_cargo_values);
$smarty->assign("option_cargo_output",$array_cargo_output);

$smarty->assign("option_regiao_values",$array_regiao_values);
$smarty->assign("option_regiao_output",$array_regiao_output);
$smarty->assign("selecionado","1");

$smarty->assign('campo', $conf->campos('tarifas_mo'));

$smarty->assign('revisao_documento', 'V2');

$smarty->assign("classe",CSS_FILE);

$smarty->display('tarifas_mo.tpl');

?>