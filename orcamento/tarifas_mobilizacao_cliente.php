<?php
/*
		Formulário de Tarifas Mobilização Cliente	
		
		Criado por Carlos Abreu  
		
		local/Nome do arquivo:
		../orcamento/tarifas_mobilizacao_cliente.php
		
		Versão 0 --> VERSÃO INICIAL - Carlos Abreu - 11/08/2017
		Versão 1 --> Inclusão dos campos reg_del nas consultas - 22/11/2017 - Carlos Abreu	
*/

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(605))
{
	nao_permitido();
}

//retorna o array de clientes
function clientes()
{
	$db = new banco_dados;
	/*
	$sql = "SELECT AF1_CLIENT, AF1_LOJA FROM AF1010 WITH(NOLOCK) ";
	$sql .= "WHERE AF1010.D_E_L_E_T_ = '' ";
	
	$db->select($sql,'MSSQL',true);
	
	$regs = $db->array_select;
	
	foreach($regs as $reg_cliente)
	{
		$sql = "SELECT id_empresa_erp, empresa, descricao FROM ".DATABASE.".empresas, ".DATABASE.".unidade ";
		$sql .= "WHERE empresas.id_cod_protheus = '".$reg_cliente["AF1_CLIENT"]."' ";
		$sql .= "AND empresas.id_loja_protheus = '".$reg_cliente["AF1_LOJA"]."' ";
		$sql .= "AND empresas.reg_del = 0 ";
		$sql .= "AND unidades.reg_del = 0 ";
		$sql .= "AND empresas.id_unidade = unidades.id_unidade ";
		
		$db->select($sql,'MYSQL',true);
		
		$reg_cli = $db->array_select[0];
		
		$array_cliente[$reg_cli["id_empresa_erp"]] = trim(tiraacentos($reg_cli["empresa"]))." - ".trim(tiraacentos($reg_cli["descricao"]));
	}
	
	asort($array_cliente);

	*/
	
	return $array_cliente;	
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
	
	$array_clientes = clientes();

	//seleciona os profissionais cadastrados
	$sql = "SELECT * FROM ".DATABASE.".tabela_valor_mobilizacao_cliente, ".DATABASE.".atividades ";
	$sql .= "WHERE tabela_valor_mobilizacao_cliente.reg_del = 0 ";
	$sql .= "AND atividades.reg_del = 0 ";
	$sql .= "AND tabela_valor_mobilizacao_cliente.id_cliente = '".$dados_form["cliente"]."' ";
	$sql .= "AND tabela_valor_mobilizacao_cliente.id_atividade = atividades.id_atividade ";
	$sql .= "ORDER BY atividades.descricao ";
	
	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}
	
	$xml = new XMLWriter();
	$xml->openMemory();
	$xml->startElement('rows');
	
	$array_regs = $db->array_select;

	foreach($array_regs as $regs)
	{
		//valor e data atual cliente
		$sql = "SELECT valor_interno, valor_cli, data_alteracao FROM ".DATABASE.".tabela_valor_mobilizacao_historico_cliente ";
		$sql .= "WHERE tabela_valor_mobilizacao_historico_cliente.id_tabela_valor_mobilizacao_cliente = '" . $regs["id_tabela_valor_mobilizacao_cliente"] . "' ";
		$sql .= "AND tabela_valor_mobilizacao_historico_cliente.reg_del = 0 ";
		$sql .= "ORDER BY tabela_valor_mobilizacao_historico_cliente.id_tabela_valor_mobilizacao_historico_cliente DESC, tabela_valor_mobilizacao_historico_cliente.data_alteracao DESC LIMIT 1 ";

		$db->select($sql,'MYSQL',true);

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
			
			return $resposta;
		}
		
		$regs1 = $db->array_select[0];
		
		$xml->startElement('row');
			$xml->writeAttribute('id', $regs['id_tabela_valor_mobilizacao_cliente']);
			$xml->writeElement('cell', $array_clientes[$regs["id_cliente"]]);
			$xml->writeElement('cell', $regs['descricao']);
			$xml->writeElement('cell', number_format($regs1['valor_interno'],2,",","."));
			$xml->writeElement('cell', number_format($regs1['valor_cli'],2,",","."));
			$xml->writeElement('cell', mysql_php($regs1["data_alteracao"]));
			$xml->writeElement('cell', '<img style="cursor:pointer;" src="'.DIR_IMAGENS.'detalhes.png" onclick=historico("'.$regs["id_tabela_valor_mobilizacao_cliente"].'","'.str_replace(" ","&nbsp;",$regs["descricao"]).'","'.$regs["id_cliente"].'");>');
			$xml->writeElement('cell', '<img style="cursor:pointer;" src="'.DIR_IMAGENS.'apagar.png" onclick=if(confirm("Deseja&nbsp;excluir&nbsp;os&nbsp;dados&nbsp;do&nbsp;valor?&nbsp;Todo&nbsp;o&nbsp;histórico&nbsp;será&nbsp;excluído!")){xajax_excluir("'.$regs["id_tabela_valor_mobilizacao_cliente"].'")};>');
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
	
	if($dados_form["cliente"]!=0 && $dados_form["id_atividade"]!=0 && !empty($dados_form["valor_interno"]))
	{	
		//verifica se o valor já esta cadastrado, caso esteja, incluir como histórico
		$sql = "SELECT * FROM ".DATABASE.".tabela_valor_mobilizacao_cliente ";
		$sql .= "WHERE tabela_valor_mobilizacao_cliente.id_cliente = '".$dados_form["cliente"]."' ";
		$sql .= "AND tabela_valor_mobilizacao_cliente.id_atividade = '" . $dados_form["id_atividade"]."' ";
		$sql .= "AND tabela_valor_mobilizacao_cliente.reg_del = 0 ";
	
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
			$isql = "INSERT INTO ".DATABASE.".tabela_valor_mobilizacao_cliente(id_cliente, id_atividade) VALUES(";
			$isql .= "'" . $dados_form["cliente"] . "', ";
			$isql .= "'" . $dados_form["id_atividade"] . "') ";
	
			$db->insert($isql,'MYSQL');
	
			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
				
				return $resposta;
			}
			
			$id_valor = $db->insert_id;
			
			//insere o historico
			$isql = "INSERT INTO ".DATABASE.".tabela_valor_mobilizacao_historico_cliente(id_tabela_valor_mobilizacao_cliente, valor_interno, valor_cli, id_funcionario, data_alteracao) VALUES(";
			$isql .= "'" . $id_valor . "', ";
			$isql .= "'" . str_replace(",",".",str_replace(".","",$dados_form["valor_interno"])) . "', ";
			$isql .= "'" . str_replace(",",".",str_replace(".","",$dados_form["valor_cli"])) . "', ";
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
			$usql = "UPDATE ".DATABASE.".tabela_valor_mobilizacao_cliente SET ";
			$usql .= "id_tabela_valor_cliente_atual = '".$id_historico."' ";
			$usql .= "WHERE id_tabela_valor_mobilizacao_cliente = '".$id_valor."' ";
	
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
			$id_tabela_mo = $regs_val["id_tabela_valor_mobilizacao_cliente"];
					
			//insere o historico
			$isql = "INSERT INTO ".DATABASE.".tabela_valor_mobilizacao_historico_cliente(id_tabela_valor_mobilizacao_cliente, valor_interno, valor_cli, id_funcionario, data_alteracao) VALUES(";
			$isql .= "'" . $id_tabela_mo . "', ";
			$isql .= "'" . str_replace(",",".",str_replace(".","",$dados_form["valor_interno"])) . "', ";
			$isql .= "'" . str_replace(",",".",str_replace(".","",$dados_form["valor_cli"])) . "', ";
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
			$usql = "UPDATE ".DATABASE.".tabela_valor_mobilizacao_cliente SET ";
			$usql .= "id_tabela_valor_cliente_atual = '".$id_historico."' ";
			$usql .= "WHERE id_tabela_valor_mobilizacao_cliente = '".$id_tabela_mo."' ";
	
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
	$sql = "SELECT * FROM ".DATABASE.".tabela_valor_mobilizacao_cliente ";
	$sql .= "WHERE tabela_valor_mobilizacao_cliente.id_tabela_valor_mobilizacao_cliente = '".$id_valor."' ";
	$sql .= "AND tabela_valor_mobilizacao_cliente.reg_del = 0 ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}

	$regs = $db->array_select[0];

	//valor atual
	$sql = "SELECT * FROM ".DATABASE.".tabela_valor_mobilizacao_historico_cliente ";
	$sql .= "WHERE tabela_valor_mobilizacao_historico_cliente.id_tabela_valor_mobilizacao_historico_cliente = '" . $regs["id_tabela_valor_cliente_atual"] . "' ";
	$sql .= "AND tabela_valor_mobilizacao_historico_cliente.reg_del = 0 ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}
	
	$regs1 = $db->array_select[0];

	$resposta->addAssign("id_valor","value", $regs["id_tabela_valor_mobilizacao_cliente"]);

	$resposta->addScript("seleciona_combo('" . $regs["id_cliente"] . "', 'cliente'); ");
	
	$resposta->addScript("seleciona_combo('" . $regs["id_atividade"] . "', 'id_atividade'); ");
	
	$resposta->addAssign("data","value",mysql_php($regs1["data_alteracao"]));
	
	$resposta->addAssign("valor_interno","value",number_format($regs1["valor_interno"],2,",","."));

	$resposta->addAssign("valor_cli","value",number_format($regs1["valor_cli"],2,",","."));	
	
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
	
	if($dados_form["cliente"]!=0 && $dados_form["id_atividade"]!=0 && !empty($dados_form["valor_interno"]))
	{
		//seleciona o valor
		$sql = "SELECT * FROM ".DATABASE.".tabela_valor_mobilizacao_cliente ";
		$sql .= "WHERE tabela_valor_mobilizacao_cliente.id_tabela_valor_mobilizacao_cliente = '".$dados_form["id_valor"]."' ";
		$sql .= "AND tabela_valor_mobilizacao_cliente.reg_del = 0 ";
	
		$db->select($sql,'MYSQL',true);
	
		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
			
			return $resposta;
		}
	
		$regs = $db->array_select[0];
		
		//atualiza o item
		$usql = "UPDATE ".DATABASE.".tabela_valor_mobilizacao_cliente SET ";
		$usql .= "id_cliente = '".$dados_form["cliente"]."', ";
		$usql .= "id_atividade = '".$dados_form["id_atividade"]."' ";
		$usql .= "WHERE id_tabela_valor_mobilizacao_cliente = '".$dados_form["id_valor"]."' ";
		$usql .= "AND reg_del = 0 ";
		
		$db->update($usql,'MYSQL');

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
			
			return $resposta;
		}
		
		//atualiza o historico
		$usql = "UPDATE ".DATABASE.".tabela_valor_mobilizacao_historico_cliente SET ";
		$usql .= "valor_interno = '".str_replace(",",".",str_replace(".","",$dados_form["valor_interno"]))."', ";
		$usql .= "valor_cli = '".str_replace(",",".",str_replace(".","",$dados_form["valor_cli"]))."', ";
		$usql .= "id_funcionario = '".$_SESSION["id_funcionario"]."', ";
		$usql .= "data_alteracao = '".php_mysql($dados_form["data"])."' ";
		$usql .= "WHERE id_tabela_valor_mobilizacao_historico_cliente = '".$regs["id_tabela_valor_cliente_atual"]."' ";
		$usql .= "AND reg_del = 0 ";
		
		$db->update($usql,'MYSQL');

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
			
			return $resposta;
		}
		
		$resposta->addAlert("valor inserido com sucesso.");
		
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
	
	$usql = "UPDATE ".DATABASE.".tabela_valor_mobilizacao_cliente SET ";
	$usql .= "reg_del = 1, ";
	$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
	$usql .= "data_del = '".date('Y-m-d')."' ";
	$usql .= "WHERE id_tabela_valor_mobilizacao_cliente = '".$id_valor."' ";
	$usql .= "AND reg_del = 0 ";
	
	$db->update($usql,'MYSQL');

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}
	
	$usql = "UPDATE ".DATABASE.".tabela_valor_mobilizacao_historico_cliente SET ";
	$usql .= "reg_del = 1, ";
	$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
	$usql .= "data_del = '".date('Y-m-d')."' ";
	$usql .= "WHERE id_tabela_valor_mobilizacao_cliente = '".$id_valor."' ";
	$usql .= "AND reg_del = 0 ";
	
	$db->update($usql,'MYSQL');

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}
	
	$resposta->addAlert("Valor excluído com sucesso.");
	
	$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'))");
	
	return $resposta;
}

function hist($id_valor,$id_cliente)
{	
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	$comb_cliente = '';
	$comb_atividade = '';
	
	$array_clientes = clientes();
	
	foreach ($array_clientes as $cod=>$cliente)
	{
		
		if($cod==$id_cliente)
		{
			$selected = 'selected';
		}
		else
		{
			$selected = '';
		}
				
		$comb_cliente .= '<option value="'.$cod.'" '.$selected.'>'.$cliente.'</option>';	
	}
	
	$sql = "SELECT * FROM ".DATABASE.".atividades ";
	$sql .= "WHERE atividades.cod = 29 "; //setor Despesas
	$sql .= "ORDER BY descricao ";
	
	$db->select($sql,'MYSQL',true);
	
	foreach ($db->array_select as $regs)
	{
		$comb_atividade .= '<option value="'.$regs["id_atividade"].'">'.$regs["codigo"] . ' - ' .$regs["descricao"].'</option>';	
	}
	
	//monta o corpo do modal de historico
    $conteudo = '<table width="100%" border="0">';
    $conteudo .= '	<tr>';
    $conteudo .= '		<td colspan="3" width="13%"><label for="cliente_hist" class="labels">Cliente</label><br />';
    $conteudo .= '   		<select name="cliente_hist" class="caixa" id="cliente_hist" onkeypress="return keySort(this);" >';
    $conteudo .= 				$comb_cliente;  
    $conteudo .= '          </select>';
    $conteudo .= '       </td>';
    $conteudo .= '  </tr>';
	$conteudo .= '	<tr>';
    $conteudo .= '       <td width="10%"><label for="id_cargo_hist" class="labels">Despesa</label><br />';
    $conteudo .= '   		<select name="id_atividade_hist" class="caixa" id="id_atividade_hist" onkeypress="return keySort(this);" >';
    $conteudo .= 				$comb_cargo;  
    $conteudo .= '          </select>';
    $conteudo .= '       </td>';
    $conteudo .= '       <td width="10%"><label for="data_hist" class="labels">Data</label><br />';
    $conteudo .= '	      <input name="data_hist" type="text" class="caixa" id="data_hist" size="10" maxlength="10" onkeypress="return txtBoxFormat(document.frm_hist, \'data_hist\', \'99/99/9999\', event);" value="" /></td>';
    $conteudo .= '       <td width="80%"><label for="valor_dvm_hist" class="labels">Valor</label><br />'; 
    $conteudo .= '	      <input name="valor_dvm_hist" type="text" class="caixa" id="valor_dvm_hist" size="7" placeholder="valor" maxlength="7" /></td>';
	$conteudo .= '       <td width="80%"><label for="valor_cli_hist" class="labels">Valor&nbsp;cli</label><br />'; 
    $conteudo .= '	      <input name="valor_cli_hist" type="text" class="caixa" id="valor_cli_hist" size="7" placeholder="valor" maxlength="7" /></td>';
	$conteudo .= '	</tr>';
    $conteudo .= '</table>';
	
	$resposta->addAssign("corpo","innerHTML",$conteudo);

	$xml = new XMLWriter();
	$xml->openMemory();
	$xml->startElement('rows');

	//seleciona os valores cadastrados
	$sql = "SELECT * FROM ".DATABASE.".tabela_valor_mobilizacao_cliente, ".DATABASE.".tabela_valor_mobilizacao_historico_cliente, ".DATABASE.".atividades ";
	$sql .= "WHERE tabela_valor_mobilizacao_cliente.reg_del = 0 ";
	$sql .= "AND tabela_valor_mobilizacao_historico_cliente.reg_del = 0 ";
	$sql .= "AND tabela_valor_mobilizacao_cliente.id_tabela_valor_mobilizacao_cliente = '" . $id_valor . "' ";
	$sql .= "AND tabela_valor_mobilizacao_cliente.id_atividade = atividades.id_atividade ";
	$sql .= "AND tabela_valor_mobilizacao_cliente.id_tabela_valor_mobilizacao_cliente = tabela_valor_mobilizacao_historico_cliente.id_tabela_valor_mobilizacao_cliente ";
	$sql .= "ORDER BY tabela_valor_mobilizacao_cliente.id_tabela_valor_mobilizacao_cliente, atividades.descricao ";
	
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
		if($regs["id_tabela_valor_mobilizacao_historico_cliente"]==$regs["id_tabela_valor_cliente_atual"] && count($array_regs)==1)
		{
			$img = '&nbsp;';	
		}
		else
		{
			
			$img = 	'<img style="cursor:pointer;" src="'.DIR_IMAGENS.'apagar.png" onclick=if(confirm("Deseja&nbsp;excluir&nbsp;os&nbsp;dados&nbsp;do&nbsp;valor?")){xajax_excluir_hist("'.$regs["id_tabela_valor_mobilizacao_historico_cliente"].'")};>';	
		}
		
		$xml->startElement('row');
			$xml->writeAttribute('id', $regs['id_tabela_valor_mobilizacao_historico_cliente']);
			$xml->writeElement('cell', $array_clientes[$regs["id_cliente"]]);
			$xml->writeElement('cell', $regs['descricao']);
			$xml->writeElement('cell', number_format(($regs['valor_interno']),2,",","."));
			$xml->writeElement('cell', number_format($regs['valor_cli'],2,",","."));
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
	$sql = "SELECT * FROM ".DATABASE.".tabela_valor_mobilizacao_historico_cliente ";
	$sql .= "WHERE tabela_valor_mobilizacao_historico_cliente.id_tabela_valor_mobilizacao_historico_cliente = '" . $id_valor_hist . "' ";
	$sql .= "AND tabela_valor_mobilizacao_historico_cliente.reg_del = 0 ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}
	
	$regs = $db->array_select[0];

	//seleciona o valor
	$sql = "SELECT * FROM ".DATABASE.".tabela_valor_mobilizacao_cliente ";
	$sql .= "WHERE tabela_valor_mobilizacao_cliente.id_tabela_valor_mobilizacao_cliente = '".$regs["id_tabela_valor_mobilizacao_cliente"]."' ";
	$sql .= "AND tabela_valor_mobilizacao_cliente.reg_del = 0 ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}

	$regs1 = $db->array_select[0];

	$resposta->addAssign("id_valor_historico","value", $regs["id_tabela_valor_mobilizacao_historico_cliente"]);

	$resposta->addScript("seleciona_combo('" . $regs1["id_cliente"] . "', 'cliente_hist'); ");
	
	$resposta->addScript("seleciona_combo('" . $regs1["id_atividade"] . "', 'id_atividade_hist'); ");
	
	$resposta->addAssign("data_hist","value",mysql_php($regs["data_alteracao"]));

	$resposta->addAssign("valor_cli_hist","value",number_format($regs["valor_cli"],2,",","."));
	
	$resposta->addAssign("valor_dvm_hist","value",number_format($regs["valor_interno"],2,",","."));
	
	$resposta->addScript("document.getElementById('btn_alt').disabled = false");	

	return $resposta;
}

//atualiza o historico
function atualizar_hist($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	if($dados_form["cliente_hist"]!=0 && $dados_form["id_atividade_hist"]!="" && !empty($dados_form["valor_cli_hist"]))
	{
		$usql = "UPDATE ".DATABASE.".tabela_valor_mobilizacao_cliente SET ";
		$usql .= "id_cliente = '".$dados_form["cliente_hist"]."', ";
		$usql .= "id_atividade = '".$dados_form["id_atividade_hist"]."' ";
		$usql .= "WHERE id_tabela_valor_mobilizacao_cliente = '".$dados_form["id_valor"]."' ";
		$usql .= "AND reg_del = 0 ";
		
		$db->update($usql,'MYSQL');

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
			
			return $resposta;
		}
		
		$usql = "UPDATE ".DATABASE.".tabela_valor_mobilizacao_historico_cliente SET ";
		$usql .= "valor_interno = '".str_replace(",",".",str_replace(".","",$dados_form["valor_dvm_hist"]))."', ";
		$usql .= "valor_cli = '".str_replace(",",".",str_replace(".","",$dados_form["valor_cli_hist"]))."', ";
		$usql .= "id_funcionario = '".$_SESSION["id_funcionario"]."', ";
		$usql .= "data_alteracao = '".php_mysql($dados_form["data_hist"])."' ";
		$usql .= "WHERE id_tabela_valor_mobilizacao_historico_cliente = '".$dados_form["id_valor_historico"]."' ";
		$usql .= "AND reg_del = 0 ";
		
		$db->update($usql,'MYSQL');

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
			
			return $resposta;
		}
		
		$resposta->addScript("xajax_hist('".$dados_form["id_valor"]."','".$dados_form["cliente_hist"]."')");
		
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
	$sql = "SELECT * FROM ".DATABASE.".tabela_valor_mobilizacao_historico_cliente ";
	$sql .= "WHERE tabela_valor_mobilizacao_historico_cliente.id_tabela_valor_mobilizacao_historico_cliente = '".$id_valor_hist."' ";
	$sql .= "AND tabela_valor_mobilizacao_historico_cliente.reg_del = 0 ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}
	
	$regs = $db->array_select[0];
	
	$id_valor = $regs["id_tabela_valor_mobilizacao_cliente"];
	
	//exclui o registro do histórico
	$usql = "UPDATE ".DATABASE.".tabela_valor_mobilizacao_historico_cliente SET ";
	$usql .= "reg_del = 1, ";
	$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
	$usql .= "data_del = '".date('Y-m-d')."' ";
	$usql .= "WHERE id_tabela_valor_mobilizacao_historico_cliente = '".$id_valor_hist."' ";
	$usql .= "AND reg_del = 0 ";
	
	$db->update($usql,'MYSQL');

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}
	
	//seleciona o histórico para obter o último registro
	$sql = "SELECT * FROM ".DATABASE.".tabela_valor_mobilizacao_historico_cliente ";
	$sql .= "WHERE tabela_valor_mobilizacao_historico_cliente.id_tabela_valor_mobilizacao_cliente = '".$id_valor."' ";
	$sql .= "AND tabela_valor_mobilizacao_historico_cliente.reg_del = 0 ";
	$sql .= "ORDER BY tabela_valor_mobilizacao_historico_cliente.id_tabela_valor_mobilizacao_historico_cliente DESC, tabela_valor_mobilizacao_historico_cliente.data_alteracao DESC LIMIT 1 ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}
	
	$regs1 = $db->array_select[0];

	//atualiza o registro de indices para o atual
	$usql = "UPDATE ".DATABASE.".tabela_valor_mobilizacao_cliente SET ";
	$usql .= "id_tabela_valor_cliente_atual = '".$regs1["id_tabela_valor_mobilizacao_historico_cliente"]."' ";
	$usql .= "WHERE id_tabela_valor_mobilizacao_cliente = '".$id_valor."' ";
	$usql .= "AND reg_del = 0 ";

	$db->update($usql,'MYSQL');

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}
	
	//seleciona o cliente
	$sql = "SELECT id_cliente FROM ".DATABASE.".tabela_valor_mobilizacao_cliente ";
	$sql .= "WHERE tabela_valor_mobilizacao_cliente.reg_del = 0 ";
	$sql .= "AND tabela_valor_mobilizacao_cliente.id_tabela_valor_cliente_atual = '".$regs1["id_tabela_valor_mobilizacao_historico_cliente"]."' ";
	
	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}
	
	$regs2 = $db->array_select[0];
	
	$resposta->addScript("xajax_hist('".$id_valor."','".$reg2["id_cliente"]."')");	
	
	$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'))");
	
	return $resposta;
}

function copia($id_cliente)
{	
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	$comb_cliente = '';
	
	$comb_cliente .= '<option value="0">SELECIONE</option>';
	
	$origem = NULL;
	
	$array_clientes = clientes();
	
	foreach ($array_clientes as $cod=>$cliente)
	{		
		if($cod!=$id_cliente)
		{
			$comb_cliente .= '<option value="'.$cod.'" '.$selected.'>'.$cliente.'</option>';
		}
		else
		{
			$origem['id'] = $cod;
			$origem['descricao'] = $cliente;
		}		
	}
	
	//seleciona os valores cadastrados
	$sql = "SELECT * FROM ".DATABASE.".tabela_valor_mobilizacao_cliente, ".DATABASE.".atividades ";
	$sql .= "WHERE tabela_valor_mobilizacao_cliente.reg_del = 0 ";
	$sql .= "AND atividades.reg_del = 0 ";
	$sql .= "AND tabela_valor_mobilizacao_cliente.id_cliente = '".$origem['id']."' ";
	$sql .= "AND tabela_valor_mobilizacao_cliente.id_atividade = atividades.id_atividade ";
	$sql .= "ORDER BY atividades.descricao ";
	
	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}
	
	$array_regs = $db->array_select;	
	
	if(count($array_regs)==0)
	{
		$resposta->addAlert("Não há valores a serem copiados.");
		
		sleep(3);
		
		$resposta->addScript("divPopupInst.destroi();");
	}
	else
	{		
		$sql = "SELECT * FROM ".DATABASE.".atividades ";
		$sql .= "WHERE atividades.cod = 29 "; //setor Despesas
		$sql .= "AND atividades.reg_del = 0 ";
		$sql .= "ORDER BY descricao ";
		
		$db->select($sql,'MYSQL',true);
		
		foreach ($db->array_select as $regs)
		{
			$comb_atividade .= '<option value="'.$regs["id_atividade"].'">'.$regs["codigo"] . ' - ' .$regs["descricao"].'</option>';	
		}
		
		//monta o corpo do modal de historico
		$conteudo = '<table width="100%" border="0">';
		$conteudo .= '	<tr>';
		$conteudo .= '		<td width="13%"><label class="labels">Cliente&nbsp;origem</label><br />';
		$conteudo .= '      <label class="labels">'.$origem['descricao'].'</label>';
		$conteudo .= '       </td>';
		$conteudo .= '  </tr>';
		$conteudo .= '	<tr>';
		$conteudo .= '		<td width="13%"><label for="cliente_dest" class="labels">Cliente&nbsp;destino</label><br />';
		$conteudo .= '   		<select name="cliente_dest" class="caixa" id="cliente_dest" onkeypress="return keySort(this);" onchange="if(this.value){document.getElementById(\'btn_copia\').disabled=false;}else{document.getElementById(\'btn_copia\').disabled=true;};" >';
		$conteudo .= 				$comb_cliente;  
		$conteudo .= '          </select>';
		$conteudo .= '       </td>';
		$conteudo .= '  </tr>';
		$conteudo .= '</table>';
		
		$resposta->addAssign("corpo","innerHTML",$conteudo);	
		
		$xml = new XMLWriter();
		$xml->openMemory();
		$xml->startElement('rows');
	
		foreach($array_regs as $regs)
		{
			//valor e data atual cliente
			$sql = "SELECT valor_interno, valor_cli, data_alteracao FROM ".DATABASE.".tabela_valor_mobilizacao_historico_cliente ";
			$sql .= "WHERE tabela_valor_mobilizacao_historico_cliente.id_tabela_valor_mobilizacao_cliente = '" . $regs["id_tabela_valor_mobilizacao_cliente"] . "' ";
			$sql .= "AND tabela_valor_mobilizacao_historico_cliente.reg_del = 0 ";
			$sql .= "ORDER BY tabela_valor_mobilizacao_historico_cliente.id_tabela_valor_mobilizacao_historico_cliente DESC, tabela_valor_mobilizacao_historico_cliente.data_alteracao DESC LIMIT 1 ";
	
			$db->select($sql,'MYSQL',true);
			
			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
				
				return $resposta;
			}
			
			$regs1 = $db->array_select[0];
			
			$xml->startElement('row');
				$xml->writeAttribute('id', $regs['id_tabela_valor_mobilizacao_cliente']);
				$xml->writeElement('cell', $regs['descricao']);
				$xml->writeElement('cell', '<input name="vdvm_'.$regs['id_atividade'].'" type="text" class="caixa" id="vdvm_'.$regs['id_atividade'].'" value="'. number_format(($regs1['valor_interno']),2,",",".").'">');
				$xml->writeElement('cell', '<input name="vcli_'.$regs['id_atividade'].'" type="text" class="caixa" id="vcli_'.$regs['id_atividade'].'" value="'. number_format(($regs1['valor_cli']),2,",",".").'">');
			$xml->endElement();
		}
		
		$xml->endElement();
		
		$cont = $xml->outputMemory(false);
		
		$resposta->addScript("grid('valores_copia', true, '420', '".$cont."');");
	}

	return $resposta;
}

function copiar_valores($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	$array_indices = NULL;
	
	$array_valor = NULL;
	
	if($dados_form["id_cliente"]!=0 && $dados_form["cliente_dest"]!="0")
	{
		//separa os valores
		foreach($dados_form as $campos=>$val)
		{
			$id_atividade = explode("_",$campos);
			
			if($id_atividade[0]=='vcli' || $id_atividade[0]=='vdvm')
			{
				$array_indices[$id_atividade[1]] = $id_atividade[1];
				
				switch ($id_atividade[0])
				{
					case 'vcli':
						$array_valor['cli'][$id_atividade[1]] = $val;
					break;
					
					case 'vdvm':
						$array_valor['dvm'][$id_atividade[1]] = $val;
					break;						
				}
			}
		}
		
		//percorre os cargos
		foreach($array_indices as $id_atividade)
		{
			//verifica se o valor já está cadastrado, caso esteja, incluir como histórico
			$sql = "SELECT * FROM ".DATABASE.".tabela_valor_mobilizacao_cliente ";
			$sql .= "WHERE tabela_valor_mobilizacao_cliente.id_cliente = '".$dados_form["cliente_dest"]."' ";
			$sql .= "AND tabela_valor_mobilizacao_cliente.id_atividade = '" . $id_atividade."' ";
			$sql .= "AND tabela_valor_mobilizacao_cliente.reg_del = 0 ";
		
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
				$isql = "INSERT INTO ".DATABASE.".tabela_valor_mobilizacao_cliente(id_cliente, id_atividade) VALUES(";
				$isql .= "'" . $dados_form["cliente_dest"] . "', ";
				$isql .= "'" . $id_atividade . "') ";
		
				$db->insert($isql,'MYSQL');
		
				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
					
					return $resposta;
				}
				
				$id_valor = $db->insert_id;
				
				//insere o historico
				$isql = "INSERT INTO ".DATABASE.".tabela_valor_mobilizacao_historico_cliente(id_tabela_valor_mobilizacao_cliente, valor_interno, valor_cli, id_funcionario, data_alteracao) VALUES(";
				$isql .= "'" . $id_valor . "', ";
				$isql .= "'" . str_replace(",",".",str_replace(".","",$array_valor['dvm'][$id_atividade])) . "', ";
				$isql .= "'" . str_replace(",",".",str_replace(".","",$array_valor['cli'][$id_atividade])) . "', ";
				$isql .= "'".$_SESSION["id_funcionario"]."', ";
				$isql .= "'".date('Y-m-d')."') ";
		
				$db->insert($isql,'MYSQL');
		
				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
					
					return $resposta;
				}
				
				$id_historico = $db->insert_id;
				
				//atualiza o indice atual na tabela principal
				$usql = "UPDATE ".DATABASE.".tabela_valor_mobilizacao_cliente SET ";
				$usql .= "id_tabela_valor_cliente_atual = '".$id_historico."' ";
				$usql .= "WHERE id_tabela_valor_mobilizacao_cliente = '".$id_valor."' ";
				$usql .= "AND reg_del = 0 ";
		
				$db->update($usql,'MYSQL');
		
				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
					
					return $resposta;
				}		
			}
			else
			{
				$id_tabela_mo = $regs_val["id_tabela_valor_mobilizacao_cliente"];
						
				//insere o historico
				$isql = "INSERT INTO ".DATABASE.".tabela_valor_mobilizacao_historico_cliente(id_tabela_valor_mobilizacao_cliente, valor_interno, valor_cli, id_funcionario, data_alteracao) VALUES(";
				$isql .= "'" . $id_tabela_mo . "', ";
				$isql .= "'" . str_replace(",",".",str_replace(".","",$array_valor['dvm'][$id_atividade])) . "', ";
				$isql .= "'" . str_replace(",",".",str_replace(".","",$array_valor['cli'][$id_atividade])) . "', ";
				$isql .= "'".$_SESSION["id_funcionario"]."', ";
				$isql .= "'".date('Y-m-d')."') ";
		
				$db->insert($isql,'MYSQL');
		
				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
					
					return $resposta;
				}
				
				$id_historico = $db->insert_id;
				
				//atualiza o indice atual na tabela principal
				$usql = "UPDATE ".DATABASE.".tabela_valor_mobilizacao_cliente SET ";
				$usql .= "id_tabela_valor_cliente_atual = '".$id_historico."' ";
				$usql .= "WHERE id_tabela_valor_mobilizacao_cliente = '".$id_tabela_mo."' ";
				$usql .= "AND reg_del = 0 ";
		
				$db->update($usql,'MYSQL');
		
				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
					
					return $resposta;
				}		
			}			
		}
		
		$resposta->addAlert("Valores copiados.");
		
		sleep(3);
		
		$resposta->addScript("divPopupInst.destroi();");		
	}
	else
	{
		$resposta->addAlert("Os campos devem estar preenchidos.");		
	}
		
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
$xajax->registerFunction("copia");
$xajax->registerFunction("copiar_valores");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

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
		
			mygrid.setHeader("Cliente, Despesa, Valor, Valor&nbsp;cli., data, H, E");
			mygrid.setInitWidths("250,*,80,80,100,30,30");
			mygrid.setColAlign("left,left,left,left,left,center,center");
			mygrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro");
			mygrid.setColSorting("str,str,str,str,str,str,str,str");
		
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
		
			mygrid.setHeader("Cliente, Despesa, Valor, Valor&nbsp;cli., data, E");
			mygrid.setInitWidths("250,*,80,80,100,30");
			mygrid.setColAlign("left,left,left,left,left,center");
			mygrid.setColTypes("ro,ro,ro,ro,ro,ro");
			mygrid.setColSorting("str,str,str,str,str,str");
		
			mygrid.attachEvent("onRowSelect",doOnRowSelected2);
		break;
		
		case 'valores_copia':
		
			mygrid.setHeader("Despesa, Valor, Valor&nbsp;cli.");
			mygrid.setInitWidths("250,80,80");
			mygrid.setColAlign("left,left,left");
			mygrid.setColTypes("ro,ro,ro");
			mygrid.setColSorting("str,str,str");

		break;
		
	}
	mygrid.setSkin("dhx_skyblue");
	mygrid.enableMultiselect(true);
	mygrid.enableCollSpan(true);
	mygrid.init();
	mygrid.loadXMLString(xml);
}

function historico(id_valor,descricao,id_cliente)
{
	conteudo = '<form name="frm_hist" id="frm_hist" action="" method="POST">';
	
	conteudo += '<table width="100%">';
	
	conteudo += '<tr><td>';
	
	conteudo += '<label class="labels">'+descricao+'</label>';
	conteudo += '<input type="hidden" name="id_valor" id="id_valor" value="'+id_valor+'">';
	conteudo += '<input type="hidden" name="id_valor_historico" id="id_valor_historico" value="">';
	
	conteudo += '</tr></td>';
	
	conteudo += '<tr><td class="espacamento">';

	conteudo += '<div id="corpo" style="width:100%;">&nbsp;</div>';
	
	conteudo += '</td></tr></table>';
	
	conteudo += '<input type="button" class="class_botao" name="btn_alt" id="btn_alt" value="Alterar" onclick=if(confirm("Deseja&nbsp;alterar&nbsp;os&nbsp;dados&nbsp;da&nbsp;despesa?")){xajax_atualizar_hist(xajax.getFormValues("frm_hist"))}; disabled="disabled">&nbsp;&nbsp;';
	
	conteudo += '<input type="button" class="class_botao" name="btn_voltar" id="btn_voltar" value="Voltar" onclick=divPopupInst.destroi();>';
	
	conteudo += '<div id="valores_hist" style="width:100%">&nbsp;</div></form>';	
	
	modal(conteudo, 'g', 'HIST&Oacute;RICO');	

	xajax_hist(id_valor,id_cliente);
}

function copia_origem(id_cliente)
{
	conteudo = '<form name="frm_copia" id="frm_copia" action="" method="POST">';
	
	conteudo += '<input type="hidden" name="id_cliente" id="id_cliente" value="'+id_cliente+'">';

	conteudo += '<div id="corpo" style="width:100%;">&nbsp;</div>';
	
	conteudo += '<input type="button" class="class_botao" name="btn_copia" id="btn_alt" value="Copiar" onclick=if(confirm("Deseja&nbsp;copiar&nbsp;os&nbsp;dados&nbsp;da&nbsp;origem?")){xajax_copiar_valores(xajax.getFormValues("frm_copia"))}; disabled="disabled">&nbsp;&nbsp;';
	
	conteudo += '<input type="button" class="class_botao" name="btn_voltar" id="btn_voltar" value="Voltar" onclick=divPopupInst.destroi();>';
	
	conteudo += '<div id="valores_copia" style="width:100%">&nbsp;</div></form>';	
	
	modal(conteudo, 'g', 'ORIGEM->DESTINO');	

	xajax_copia(id_cliente);
}

</script>

<?php

$conf = new configs();

$db = new banco_dados;

$array_cliente_values[] = '';
$array_cliente_output[] = 'SELECIONE';

//seleciona as atividades despesas
$sql = "SELECT * FROM ".DATABASE.".atividades ";
$sql .= "WHERE atividades.cod = 29 "; //setor Despesas
$sql .= "AND atividades.reg_del = 0 ";
$sql .= "ORDER BY descricao ";

$db->select($sql,'MYSQL',true);

foreach ($db->array_select as $regs)
{
	$array_atividade_values[] = $regs["id_atividade"];
	$array_atividade_output[] = $regs["codigo"] . ' - ' .$regs["descricao"];
}

$array_cliente = clientes();

foreach($array_cliente as $cod=>$cliente)
{
	$array_cliente_values[] = $cod;
	$array_cliente_output[] = $cliente;
}

$smarty->assign("option_atividade_values",$array_atividade_values);
$smarty->assign("option_atividade_output",$array_atividade_output);

$smarty->assign("option_cliente_values",$array_cliente_values);
$smarty->assign("option_cliente_output",$array_cliente_output);

$smarty->assign('campo', $conf->campos('tarifas_mobilizacao_cliente'));

$smarty->assign('revisao_documento', 'V1');

$smarty->assign("classe",CSS_FILE);

$smarty->display('tarifas_mobilizacao_cliente.tpl');

?>