<?php
/*
	Módulo de Boletins de medição
	
	Iniciado por Carlos Abreu
	Finalizado por Carlos Eduardo
	
	Versão 0 --> VERSÃO INICIAL : 23/03/2009
	Versão 1 --> VERSÃO 1 : 19/05/2015
	Versão 2 --> atualização layout - Carlos Abreu - 22/03/2017
	Versão 3 --> Melhoria na questão de permissões - Carlos Eduardo - 10/10/2017
	Versão 4 --> Inclusão dos campos reg_del nas consultas - 17/11/2017 - Carlos Abreu
	Versão 5 --> Inclusão da funcionalidade de informações de pedidos - 27/02/2017 - Carlos Eduardo
	Versão 6 --> Inclusão do campo reembolso de despesa - 08/03/2018 - Carlos Eduardo
	Versão 7 --> Inclusão do funcionalidade de cancelamento de saldo remanescente - 19/03/2018 - Carlos Eduardo
*/
header('X-UA-Compatible: IE=edge');

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

require_once(INCLUDE_DIR."antiInjection.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO
//previne contra acesso direto
if(!verifica_sub_modulo(311))
{
	nao_permitido();
}

//Retorna se o usuário indicado tem acesso completo ao sistema
function acesso_completo($idFuncionario, $condicaoPrioritaria = false)
{
    //$arrUsuarios = array(6,12,978,987,1303);
    
    return in_array($idFuncionario, $arrUsuarios) || $condicaoPrioritaria;
}

//retorna valor e percentual a partir da quantidade
function calcula_quantidade($valor_total, $quantidade, $controle, $quantidade_total)
{
	$resposta = new xajaxResponse();
	
	$quantidade = str_replace(".", "", $quantidade); // Retiro o ponto
	$quantidade = str_replace(",", ".", $quantidade); // Troco a virgula por ponto...
	
	$quantidade_total = str_replace(".", "", $quantidade_total); // Retiro o ponto
	$quantidade_total = str_replace(",", ".", $quantidade_total); // Troco a virgula por ponto...
	
	$valor_total = str_replace(".", "", $valor_total); // Retiro o ponto
	$valor_total = str_replace(",", ".", $valor_total); // Troco a virgula por ponto...
	
	if ($controle == 'per')
	{
		$valor = ($valor_total / $quantidade_total * $quantidade);
		$resposta->addAssign("valor_planejado","value",number_format($valor,2,",","."));
		
		$valor = (100 / $quantidade_total * $quantidade);
		$resposta->addAssign("percent_planejado","value",number_format($valor,2,",","."));
	}
	else
	{
		$valor = ($valor_total / $quantidade_total * $quantidade);
		$resposta->addAssign("valor_medido","value",number_format($valor,2,",","."));
		
		$valor = (100 / $quantidade_total * $quantidade);
		$resposta->addAssign("percent_medido","value",number_format($valor,2,",","."));
	}
	
	return $resposta;	
}

//retorna valor a partir do percentual
function calcula_valor($valor_total, $percentual)
{
	$valor_total = str_replace(".", "", $valor_total); // Retiro o ponto
	$valor_total = str_replace(",", ".", $valor_total); // Troco a virgula por ponto...
	
	$percentual = str_replace(".", "", $percentual); // Retiro o ponto
	$percentual = str_replace(",", ".", $percentual); // Troco a virgula por ponto...
	
	return ($percentual/100)*$valor_total;	
}

//retorna percentual a partir do valor
function calcula_percentual($valor_total,$valor)
{
	$valor_total = str_replace(".", "", $valor_total); // Retiro o ponto
	$valor_total = str_replace(",", ".", $valor_total); // Troco a virgula por ponto...
	
	$valor = str_replace(".", "", $valor); // Retiro o ponto
	$valor = str_replace(",", ".", $valor); // Troco a virgula por ponto...
			
	if($valor_total==0)
	{
		$percentual = 0;
	}
	else
	{		
		$percentual = (($valor/$valor_total)*100);
	}
	
	return $percentual;	
}

function voltar()
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$botao = $conf->botoes($_COOKIE["idioma"],$resposta);

	$resposta->addScriptCall("reset_campos('frm')");
	
	$resposta->addAssign("btninserir_itens", "value", $botao[1]);
	$resposta->addAssign("btninserir", "value", 'Inserir');
	
	$resposta->addEvent("btninserir_itens", "onclick", "xajax_insere(xajax.getFormValues('frm', true));");
	
	$resposta->addEvent("btnvoltar", "onclick", "history.back();");

	return $resposta;

}

function carrega_total_orcamento($os)
{
	$resposta = new xajaxResponse();
	
	$db	= new banco_dados();
	
	$os	= explode('/', $os);

	/*
	$sql = "SELECT 
				AF5_TOTAL, AF5_ORCAME,AF8_REFCLI, AF8_START, AF8_FINISH, AF8_PROJET, AF8_DTSOLI inicio, AF8_DTENTR fim
			FROM 
				AF5010
				JOIN (SELECT AF8_REFCLI, AF8_START, AF8_FINISH, AF8_PROJET, AF8_DTSOLI, AF8_DTENTR FROM AF8010) AF8010 ON AF8_PROJET = AF5_ORCAME
			WHERE 
				AF5010.D_E_L_E_T_ = ''
				AND AF5_ORCAME = '".sprintf("%010d",$os[0])."'
				AND AF5_NIVEL = '001'";
	
	$db->select($sql,'MSSQL',true);
	
	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	
	foreach($db->array_select as $reg)
	{
		$resposta->addAssign('valor_total', 'value', number_format($reg['AF5_TOTAL'], 2, ',', '.'));
		$resposta->addAssign('data_pedido', 'value', mysql_php(protheus_mysql($reg['inicio'])));
		$resposta->addAssign('data_termino', 'value', mysql_php(protheus_mysql($reg['fim'])));
		$resposta->addAssign('ref_cliente', 'value', trim($reg['AF8_REFCLI']));		
	}
	*/

	$sql = "SELECT 
				funcionario, nome_contato
			FROM 
				".DATABASE.".ordem_servico
			    LEFT JOIN(SELECT id_funcionario, funcionario FROM ".DATABASE.".funcionarios WHERE funcionarios.reg_del = 0) funcs ON id_funcionario = id_cod_coord 
			    LEFT JOIN(SELECT * FROM ".DATABASE.".contatos WHERE contatos.reg_del = 0) contatos ON id_contato = id_cod_resp 
			where os = '".$os[0]."'
			AND ordem_servico.reg_del = 0 ";
	
	$db->select($sql,'MYSQL',true);
	
	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	
	foreach($db->array_select as $reg)
	{
		$resposta->addAssign('coord_cli', 'value', $reg['nome_contato']);
		$resposta->addAssign('coord_dvm', 'value', $reg['funcionario']);		
	}
	
	return $resposta;
}

function atualizatabela_itens($id_bms_pedido)
{
	$resposta = new xajaxResponse();
	
	if (empty($id_bms_pedido))
	{
		$resposta->addAssign('div_itens', 'innerHTML', '');
		$resposta->addScript("xajax_limparFormItens();");
		return $resposta;
	}
	
	$db = new banco_dados();
	
	$conf = new configs();
	
	$campos = $conf->campos('bms',$resposta);
	
	$msg = $conf->msg($resposta);
	
	$filtro = "";
	
	$conteudo = "";
	
	$valor_total = 0;
	$totalMedido = 0;
	
	$item = NULL;
	
	$os	= NULL;
	
	if(!empty($id_bms_pedido))
	{
		$filtro .= "AND bms_item.id_bms_pedido = '".$id_bms_pedido."' ";
	}
	
	$sql = "SELECT
				bms_item.id_bms_item, bms_item.id_os, numero_item, bms_item.descricao, quantidade, formato, valor, valor-SUM(valor_medido) totalMedido
			FROM
				".DATABASE.".bms_item
				JOIN(SELECT * FROM ".DATABASE.".formatos WHERE formatos.reg_del = 0) formatos ON bms_item.id_unidade = formatos.id_formato 
				JOIN ".DATABASE.".ordem_servico ON ordem_servico.id_os = bms_item.id_os AND ordem_servico.reg_del = 0 
				LEFT JOIN ".DATABASE.".bms_medicao ON bms_medicao.reg_del = 0 AND bms_medicao.id_bms_pedido = '".$id_bms_pedido."' and bms_item.id_bms_item = bms_medicao.id_bms_item ";
	$sql .= "WHERE bms_item.reg_del = 0 ";
	$sql .= $filtro;
	$sql .= "GROUP BY bms_item.id_bms_item ";
	$sql .= "ORDER BY round(bms_item.numero_item,1) ";
	
	$db->select($sql,'MYSQL',true);
	
	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	
	$xml = new XMLWriter();
	$xml->openMemory();
	$xml->startElement('rows');
	
	foreach($db->array_select as $regs)
	{
		$xml->startElement('row');
		
			$xml->writeAttribute('id', $regs['id_bms_item']);
			$xml->writeElement('cell', $regs['numero_item']);		
			$xml->writeElement('cell', $regs['descricao']);
			$xml->writeElement('cell', $regs['quantidade']);
			$xml->writeElement('cell', $regs['formato']);
			$xml->writeElement('cell', number_format($regs["valor"],2,",","."));
			$xml->writeElement('cell', number_format($regs["totalMedido"],2,",","."));
			$xml->writeElement('cell', '<span class="icone icone-excluir cursor" onclick=xajax_excluir("'.$regs["id_bms_item"].'");></span>');
			
		$xml->endElement();
		
		$item = trim($regs["numero_item"]);
		
		$os = $regs["os"];
		
		$valor_total += $regs["valor"];
		$totalMedido += $regs['totalMedido'];
	}
	
	$sql = "SELECT valor_pedido FROM ".DATABASE.".bms_pedido ";
	$sql .= "WHERE bms_pedido.id_bms_pedido = '".$id_bms_pedido."' ";
	$sql .= "AND bms_pedido.reg_del = 0 ";
	
	$db->select($sql,'MYSQL', true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	
	$regs = $db->array_select[0];
	
	$saldo = $regs['valor_pedido'] - $valor_total;
	
	$xml->startElement('row');
		$xml->writeAttribute('id', '999998');
	$xml->endElement();
	
	$xml->startElement('row');

		$xml->writeAttribute('id', '999999');
		$xml->writeElement('cell', '&nbsp;');
		$xml->writeElement('cell', '&nbsp;');
		$xml->writeElement('cell', '&nbsp;');
		$xml->writeElement('cell', '<b>A Planejar/A Medir</b>');
		$xml->writeElement('cell', number_format($saldo,2,",","."));
		$xml->writeElement('cell', number_format($totalMedido,2,",","."));

	$xml->endElement();

	$xml->endElement();
	
	$conteudo = $xml->outputMemory(true);
	
	$resposta->addScript("grid('div_itens', true, '320', '".$conteudo."');");
	
	/*
	$sql = "SELECT 
				AF5_TOTAL, AF5_ORCAME 
			FROM 
				AF5010
			WHERE 
				AF5010.D_E_L_E_T_ = ''
				AND AF5_ORCAME = '".sprintf("%010d",$os)."'
				AND AF5_NIVEL = '001'";
	
	$db->select($sql,'MSSQL', true);
	
	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	
	$regs1 = $db->array_select[0];
	*/
	
	$nr_item = explode(".",$item);
	
	if($nr_item[0]!='')
	{
		$nr_item[0] = versao_documento($nr_item[0]);
	}
	else
	{
		$nr_item[0] = '1';	
	}
	
	$resposta->addAssign("numero_item","value",$nr_item[0].".0");		

	$resposta->addScript("xajax_limparFormItens();");
	
	return $resposta;
}

/**
 * Esta função Insere ou altera um pedido
 * @param Array $dados_form
 */
function inserir_pedido($dados_form)
{
	$resposta = new xajaxResponse();
	
	if (intval($dados_form['valor_total']) == 0 || empty($dados_form['id_os']) || empty($dados_form['data_pedido']))
	{
		$resposta->addAlert('Por favor, verifique os campos OS, valor Total, data do Pedido e Condições de PGTO!');
		
		return $resposta;
	}
	
	$db = new banco_dados();

	//Insere
	if (empty($dados_form['pedido_numero']))
	{
		$dados_form['valor_total'] = str_replace(",",".",str_replace(".","",$dados_form["valor_total"]));
		
		$isql = "INSERT INTO ".DATABASE.".bms_pedido (id_os, valor_pedido, obs, data_pedido, data_termino, ref_cliente) ";
		$isql .= "VALUES('".$dados_form['id_os']."', '".$dados_form['valor_total']."', '".strtoupper(AntiInjection::clean($dados_form['obs_pedido']))."', '".php_mysql($dados_form['data_pedido'])."','".php_mysql($dados_form['data_termino'])."',  '".AntiInjection::clean($dados_form['ref_cliente'])."');";
		
		$db->insert($isql, 'MYSQL');
		
		if ($db->erro != '')
		{
			$resposta->addAlert('Houve uma falha ao tentar inserir o pedido! '.$db->erro);
			return $resposta;
		}
		else
			$resposta->addAlert('Registro inserido corretamente!');
	}
	else
	{
		$dados_form['valor_total'] = str_replace(",",".",str_replace(".","",$dados_form["valor_total"]));
		
		$usql = "UPDATE ".DATABASE.".bms_pedido SET
					id_os = '".$dados_form['id_os']."',
					valor_pedido = '".$dados_form['valor_total']."',
					obs = '".strtoupper(AntiInjection::clean($dados_form['obs_pedido']))."',
					data_pedido = '".php_mysql($dados_form['data_pedido'])."',
					data_termino = '".php_mysql($dados_form['data_termino'])."',
					ref_cliente = '".AntiInjection::clean($dados_form['ref_cliente'])."'
				WHERE
					id_bms_pedido = ".$dados_form['pedido_numero']."
					AND bms_pedido.reg_del = 0 ";
		
		$db->update($usql, 'MYSQL');
		
		if ($db->erro != '')
		{
			$resposta->addAlert('Houve uma falha ao tentar alterar o pedido! '.$db->erro);
			return $resposta;
		}
		else
			$resposta->addAlert('Registro alterado corretamente!');
	}
	
	$resposta->addScript("document.location.reload(true);");
	$resposta->addAssign("btninserir", "value", "Inserir");
	
	return $resposta;
}

function preenche_combo_item_medicoes($id = '')
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados();
	
	$conf = new configs();
	
	$campos = $conf->campos('bms',$resposta);
	
	$msg = $conf->msg($resposta);
	
	//Combo de items
	$resposta->addScript("combo_destino = document.getElementById('id_item');");
	$resposta->addScript("limpa_combo('id_item');");
	
	$filtro = "";
	
	$conteudo = "";
	
	$val_pln = 0;	
	$val_med = 0;	
	$val_sld = 0;
		
	$sql = "SELECT * FROM ".DATABASE.".bms_item a ";
	$sql .= "WHERE a.id_bms_pedido = '".$id."' ";
	$sql .= "AND a.reg_del = 0 ";
	$sql .= "ORDER BY round(numero_item,1)";
	
	$resposta->addScript("combo_destino.options[combo_destino.length] = new Option('SELECIONE', '');");
	
	$db->select($sql,'MYSQL',true);
	
	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	
	foreach($db->array_select as $regs)
	{
		$resposta->addScript("combo_destino.options[combo_destino.length] = new Option('".$regs["numero_item"]." - ".$regs["descricao"]."', '".$regs["id_bms_item"]."');");
	}
	
	return $resposta;
}

function preenche_combo_status()
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();
	
	//Combo de status
	$resposta->addScript("combo_destino2 = document.getElementById('id_status');");
	
	$resposta->addScriptCall("limpa_combo('id_status');");
	
	$sql = "SELECT * FROM ".DATABASE.".bms_controles ";
	$sql .= "WHERE id_bms_controle IN(1,2,4,5,3) ";
	$sql .= "AND bms_controles.reg_del = 0 ";
	$sql .= "ORDER BY id_bms_controle ";

	$db->select($sql,'MYSQL',true);
	
	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	
	foreach($db->array_select as $regs)
	{
		$resposta->addScript("combo_destino2.options[combo_destino2.length] = new Option('".$regs["bms_controle"]."', '".$regs["id_bms_controle"]."');");
	}
	
	return $resposta;
}

function atualizatabela_medicoes($id_bms_item)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados();	
	
	$conf = new configs();
	
	$val_pln = 0;
	$val_med = 0;
	$val_saldo = 0;
	$qtdPlanej = 0;
	$qtdMedido = 0;
	$dif_quantidade_medida = 0;
	$percent_planej = 0;
	$percent_medido = 0;
	$dif_medido = 0;
	$dif_percent_medido = 0;
	$totalSaldoFaturado = 0;
	
	$podeAprovar = true;
	
	$campos = $conf->campos('bms',$resposta);
	
	$msg = $conf->msg($resposta);
	
	$sql = "SELECT valor FROM ".DATABASE.".bms_item ";
	$sql .= "WHERE bms_item.reg_del = 0 ";
	$sql .= "AND bms_item.id_bms_item = ".$id_bms_item;
	
	$db->select($sql, 'MYSQL', true);
	
	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	
	$total = $db->array_select[0];
	
	$sql = "SELECT * FROM ".DATABASE.".bms_item 
			JOIN (SELECT id_os codOs, id_empresa_erp idCliente FROM ".DATABASE.".ordem_servico WHERE ordem_servico.reg_del = 0) os ON codOs = bms_item.id_os,
			".DATABASE.".bms_medicao
			JOIN (SELECT id_bms_controle idControle, bms_controle FROM ".DATABASE.".bms_controles WHERE bms_controles.reg_del = 0) AS bms_controle
			ON idControle = id_bms_controle ";
	$sql .= "WHERE bms_item.reg_del = 0 ";
	$sql .= "AND bms_medicao.reg_del = 0 ";
	$sql .= "AND bms_item.id_bms_item = bms_medicao.id_bms_item ";
	$sql .= "AND bms_item.id_bms_item = '".$id_bms_item."' ";
	$sql .= "ORDER BY bms_medicao.data ";
	
	$db->select($sql, 'MYSQL', true);
	
	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	
	$xml = new XMLWriter();
	$xml->openMemory();
	$xml->startElement('rows');
	
	foreach($db->array_select as $regs)
	{
		//Deve-se somar mesmo quando for cancelado

		$val_pln += $regs["valor_planejado"];
		$val_med += $regs["valor_medido"];
		$val_saldo += $val_pln - $val_med;
		$percent_planej	+= $regs["progresso_planejado"];
		$percent_medido	+= $regs["progresso_medido"];
		$qtdPlanej	+= $regs["quantidade_planejada"];
		$qtdMedido	+= $regs["quantidade_medida"];
		$dif_medido += date('Y-m-d') > $regs['data'] || $regs['id_bms_controle'] <> 1 ? $regs["valor_diferenca"] : 0;
		$dif_percent_medido	+= date('Y-m-d') > $regs['data'] || $regs['id_bms_controle'] <> 1 ? $regs["percentual_diferenca"] : 0;
		$dif_quantidade_medida	+= date('Y-m-d') > $regs['data'] || $regs['id_bms_controle'] <> 1 ? $regs["quantidade_diferenca"] : 0;
		
		$xml->startElement('row');
			$xml->writeAttribute('id', $regs['id_bms_medicao']);
			$xml->writeElement('cell', "&nbsp;");
			$xml->writeElement('cell', mysql_php($regs['data']));
			$xml->writeElement('cell', number_format($regs["valor_planejado"],2,",","."));
			$xml->writeElement('cell', number_format($regs["valor_medido"],2,",","."));
			$xml->writeElement('cell', number_format($regs["valor_diferenca"],2,",","."));
			$xml->writeElement('cell', number_format($regs["progresso_planejado"],2,",","."));
			$xml->writeElement('cell', number_format($regs["progresso_medido"],2,",","."));
			
			$saldo = ($total['valor']-$val_pln+$dif_medido);
			
			$xml->writeElement('cell', number_format($saldo,2,",","."));
			
			$xml->writeElement('cell', number_format($regs["quantidade_planejada"],2,",","."));
			$xml->writeElement('cell', number_format($regs["quantidade_medida"],2,",","."));

			$xml->writeElement('cell', number_format($regs["dif_faturado"],2,",","."));
			$totalSaldoFaturado += $regs["dif_faturado"];
			
			$xml->writeElement('cell', substr($regs['bms_controle'],0,2));
			
			//Faturado, mostra a NF na descrição
			if (in_array($regs['id_bms_controle'], array(3,5)))
			{
				$regs['observacao_medicao'] .= $regs['numero_nf'];
			}
			
			$xml->writeElement('cell', $regs['observacao_medicao']);
			
			//Se for bms ou medido, permite faturar
			if (in_array($regs['id_bms_controle'], array(2,3,5)) && acesso_completo($_SESSION['id_funcionario']))
			{
			    $iconeAprovar = !empty($regs['arquivo_liberacao']) ? 'icone-aprovar-amarelo' : 'icone-aprovar';
				
				$xml->writeElement('cell', '<span class="icone '.$iconeAprovar.' cursor" onclick=showModalNF("'.$regs['id_bms_medicao'].'","'.$regs['id_bms_item'].'");></span>');
				
				$podeAprovar = false;
			}
			else
			{
				$xml->writeElement('cell', "&nbsp;");
			}
			
			if ((!in_array($regs['id_bms_controle'], array(3,4,5)) && acesso_completo($_SESSION['id_funcionario'], ($regs['id_bms_controle'] == 1))) || $_SESSION['admin'] == 1)
			{
				$xml->writeElement('cell', '<span class="icone icone-excluir cursor" onclick=if(confirm("Deseja&nbsp;excluir&nbsp;esta&nbsp;medição?")){xajax_excluir_medicao("'.$regs['id_bms_medicao'].'","'.$regs['id_bms_item'].'");}></span>');
			}
			else
			{
				$xml->writeElement('cell', "&nbsp;");
			}
			
			$xml->writeElement('cell', '<span class="icone icone-balao cursor" onclick={buscar_observacoes("'.$regs['id_bms_medicao'].'");}></span>');
			
			if ($regs["valor_diferenca"] > '0.00')
			{
				$xml->writeAttribute('class', 'cor_2');
			}
			else if (intval($regs["id_bms_controle"]) == 4)
			{
				$xml->writeAttribute('class', 'cor_2');//VERIFICAR QUAL SERÁ A MELHOR COR
			}
			
			if (!in_array($regs['id_bms_controle'], array(3)) && !empty($regs['id_funcionario']) && acesso_completo($_SESSION['id_funcionario']))
			{
				$xml->writeElement('cell', '<span class="icone icone-inserir cursor" onclick={verificarApontamentos("'.$regs['id_bms_medicao'].'","'.$regs['idCliente'].'");}></span>');
			}
			else
			{
				$xml->writeElement('cell', '&nbsp;');
			}
				
			if ($regs['id_bms_controle'] == 1)
			{
				$xml->writeElement('cell', '<span class="icone icone-aprovar cursor" onclick=xajax_medir_exato("'.$regs['id_bms_medicao'].'","'.$regs['id_bms_item'].'","'.$regs['id_bms_pedido'].'")></span>');
			}
			else
			{
				$xml->writeElement('cell', '&nbsp;');
			}
				
		$xml->endElement();
	}
	
	$val_sld = ($total['valor']-$val_pln+$dif_medido);

	$idMedicao = isset($regs) ? $regs['id_bms_medicao'] : '0';
		$xml->startElement('row');
			$xml->writeAttribute('id', 'fim2_'.$idMedicao);
			$xml->writeElement('cell', "&nbsp;");
			$xml->writeElement('cell', '<b>TOTAIS</b>');
			$xml->writeElement('cell', '&nbsp;');
			$xml->writeElement('cell', '<b>'.number_format($val_med, 2, ',', '.').'</b>');
			$xml->writeElement('cell', '&nbsp;');
			$xml->writeElement('cell', '&nbsp;');
			$xml->writeElement('cell', '<b>'.number_format($percent_medido, 2, ',', '.').'</b>');			
			$xml->writeElement('cell', '<input type="hidden" value="'.number_format($val_sld, 2, ',', '.').'" id="saldoItem" name="saldoItem" /><b>'.number_format($val_sld, 2, ',', '.').'</b>');
			$xml->writeElement('cell', '&nbsp;');
			$xml->writeElement('cell', '<b>'.number_format($qtdMedido, 2, ',', '.').'</b>');
			$xml->writeElement('cell', '<b>'.number_format($totalSaldoFaturado, 2, ',', '.').'</b>');
			$xml->writeElement('cell', '&nbsp;');
			$xml->writeElement('cell', '&nbsp;');
		$xml->endElement();
	
	$xml->endElement();
	
	$conteudo = $xml->outputMemory(true);
	
	$resposta->addScript("grid('div_medicoes', true, '220', '".$conteudo."');");
	
	$resposta->addEvent("btninserir_medicoes", "onclick", "xajax_insere_medicoes(xajax.getFormValues('frm', true));");
	
	return $resposta;
}

function insere_itens($dados_form)
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$msg = $conf->msg($resposta);	
	
	$db = new banco_dados();
	
	if(!empty($dados_form["pedido_numero"]) && !empty($dados_form["id_os"]) && !empty($dados_form["numero_item"]) && !empty($dados_form["quantidade"])  && !empty($dados_form["valor"]))
	{
		$sql = "SELECT SUM(valor) AS valor_total FROM ".DATABASE.".bms_item ";
		$sql .= "WHERE id_bms_pedido = '".$dados_form['pedido_numero']."' ";
		$sql .= "AND bms_item.reg_del = 0 ";
		
		$db->select($sql,'MYSQL', true);

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
		
		$regs = $db->array_select[0];
		
		if(($regs["valor_total"]+str_replace(",",".",str_replace(".","",$dados_form["valor"])))>str_replace(",",".",str_replace(".","",$dados_form["valor_total"])))
		{
			$resposta->addAlert("A soma dos itens é maior que o valor total.");
			
			return $resposta;
		}
		
		$sql = "SELECT * FROM ".DATABASE.".bms_item ";
		$sql .= "WHERE id_bms_pedido = '".$dados_form["pedido_numero"]."' ";
		$sql .= "AND bms_item.reg_del = 0 ";
		$sql .= "AND numero_item = '".trim($dados_form["numero_item"])."' ";
					
		$db->select($sql,'MYSQL',true);

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);

			return $resposta;
		}
					
		if($db->numero_registros<=0)
		{				
			$isql = "INSERT INTO ".DATABASE.".bms_item ";
			$isql .= "(id_bms_pedido, id_os, numero_item, descricao, quantidade, id_unidade, data_item, valor) VALUES ( ";
			$isql .= "'" . $dados_form["pedido_numero"] . "', ";
			$isql .= "'" . $dados_form["id_os"] . "', ";
			$isql .= "'" . trim($dados_form["numero_item"]) . "', ";
			$isql .= "'" . trim(strtoupper($dados_form["descricao_item"])) . "', ";
			$isql .= "'" . str_replace(",",".",str_replace(".","",$dados_form["quantidade"])) . "', ";
			$isql .= "'" . $dados_form["id_unidade"] . "', ";
			$isql .= "'" . php_mysql($dados_form["data_medicao"]) . "', ";
			$isql .= "'" . str_replace(",",".",str_replace(".","",$dados_form["valor"])) . "') ";
	
			$db->insert($isql,'MYSQL');

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
				
				return $resposta;
			}
		
			$resposta->addAlert($msg[1]);
		}
		else
		{
			$resposta->addAlert($msg[5]);
		}
	}
	else
	{
		$resposta->addAlert($msg[4]);
		return $resposta;
	}
	
	$resposta->addScript("xajax_atualizatabela_itens('".$dados_form['pedido_numero']."')");
	
	$resposta->addScript("xajax_preenche_combo_item_medicoes(".$dados_form['pedido_numero'].")");
	$resposta->addScript("seleciona_combo(".$dados_form['pedido_numero'].", 'id_status'); ");
	$resposta->addAssign("btninserir_itens", "value", "Inserir");
	$resposta->addEvent("btninserir_itens", "onclick", "xajax_insere_itens(xajax.getFormValues('frm', true));");
	
	$resposta->addAssign("numero_item", "value",'');
	$resposta->addAssign("descricao_item", "value",'');
	$resposta->addAssign("quantidade", "value",'');
	$resposta->addScript("seleciona_combo('', 'id_unidade'); ");
	$resposta->addAssign("valor", "value",'');
	
	return $resposta;
}

function insere_medicoes($dados_form)
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$msg = $conf->msg($resposta);
	
	$db = new banco_dados();
	
	$datasInserir = $dados_form["data_item"];
	$qtdsInserir = 0;
	
	if ($dados_form['id_status'] != 1)
	{
		$resposta->addAlert('ATENÇÃO: Só é permitido inserir medições com o status de PLANEJADO.');
		return $resposta;
	}
	
	if($dados_form["id_item"]!='' && $dados_form["valor_planejado"]!='' && $dados_form["percent_planejado"]!='')
	{
		$numRegistros = 1;
		
		if (isset($dados_form['chk_replicar']))
		{
			if (intval($dados_form['txt_num_replicas']) == 0 || empty($dados_form['datas_replica_definidas']))
			{
				$resposta->addAlert('ATENÇÃO: Para replicar este registro, por favor, digite o número de réplicas e defina as datas a serem utilizadas!');
				return $resposta;
			}
			
			$numRegistros = $dados_form['txt_num_replicas'];
			$datasInserir .= ','.$dados_form['datas_replica_definidas'];
			$qtdsInserir .= ';'.$dados_form['qtds_replica_definidas'];
		}
		
		$sql = "SELECT id_bms_pedido FROM ".DATABASE.".bms_item ";
		$sql .= "WHERE bms_item.reg_del = 0 ";
		$sql .= "AND bms_item.id_bms_item = '".$dados_form['id_item']."' ";
		
		$db->select($sql, 'MYSQL', true);
		
		$idBmsPedido = $db->numero_registros > 0 ? $db->array_select[0]['id_bms_pedido'] : 0;
		
		if ($idBmsPedido == 0)
		{
		    $resposta->addAlert('ATENÇÃO: Não foi encontrado um número de pedido válido! Entrar em contato com o TI');
		    return $resposta;
		}
		
		$sql = "SELECT SUM(valor_planejado) AS valor_total, SUM(valor_diferenca) AS valor_diferenca, id_bms_pedido FROM ".DATABASE.".bms_medicao ";
		$sql .= "WHERE bms_medicao.reg_del = 0 ";
		$sql .= "AND bms_medicao.id_bms_item = '".$dados_form["id_item"]."' ";
		$sql .= "AND bms_medicao.id_bms_controle NOT IN(4) ";
		$sql .= "GROUP BY bms_medicao.id_bms_pedido ";
				
		$db->select($sql,'MYSQL', true);
		
		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
			
			return $resposta;
		}
		
		$regs = $db->numero_registros > 0 ? $db->array_select[0] : array('valor_total' => 0, 'id_bms_pedido' => $idBmsPedido);
		
		$datasInserir = explode(',',$datasInserir);
		$qtdsInserir = explode(';',$qtdsInserir);
		$mensagem = '';
		
		//Fazendo as inserções, caso tenha que fazer replicas, fará varias vezes, senão apenas uma vez
		foreach($datasInserir as $k => $dataInserir)
		{
		    if (empty($dataInserir))
		        continue;
		        
			$dataInserir = php_mysql($dataInserir);
			
			$percentPlanejado = str_replace(",",".",str_replace(".","",$dados_form["percent_planejado"]));
			$valorPlanejado = str_replace(",",".",str_replace(".","",$dados_form["valor_planejado"]));
			$quantidadePlanejada = str_replace(",",".",str_replace(".","",$dados_form["quantidade_planejada"]));
			
			if (isset($qtdsInserir[$k]) && !empty($qtdsInserir[$k]))
			{
				$quantidadePlanejada = str_replace(",",".",str_replace(".","",$qtdsInserir[$k]));
				$percentPlanejado = str_replace(",",".",str_replace(".","",$qtdsInserir[$k]))/str_replace(",",".",str_replace(".","",$dados_form["quantidade_item"]));
				$valorPlanejado = str_replace(",",".",str_replace(".","",$dados_form["valor_item"]))*$percentPlanejado;
				
				$percentPlanejado *= 100;
			}
			
			$saldo = str_replace(',', '.', str_replace('.', '',$dados_form["valor_item"]))-str_replace(',', '.', str_replace('.', '',$valorPlanejado))-$regs["valor_total"];
			
			$sql = "SELECT data, ROUND('',2) FROM ".DATABASE.".bms_medicao ";
			$sql .= "WHERE bms_medicao.reg_del = 0 ";
			$sql .= "AND bms_medicao.id_bms_item = '".$dados_form["id_item"]."' ";
			$sql .= "AND bms_medicao.data = '".$dataInserir."' ";
			$sql .= "AND bms_medicao.id_bms_controle NOT IN(4) ";
			
			$db->select($sql,'MYSQL',true);
			
			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
				
				return $resposta;
			}
			
			$permiteInserir = $db->numero_registros > 0 ? false : true;
	
			if (!$permiteInserir)
			{
				$mensagem .= mysql_php($dataInserir).' ';

				if (count($datasInserir) > 1)
				{
					continue;
				}
			}
			
			if (empty($mensagem))
			{
    			$isql = "INSERT INTO ".DATABASE.".bms_medicao ";
    			$isql .= "(id_bms_item, data, data_status, progresso_planejado, valor_planejado, quantidade_planejada, valor_saldo, id_bms_controle, id_bms_pedido) VALUES ";
    			$isql .= "('" . $dados_form["id_item"] . "', ";
    			$isql .= "'" . $dataInserir . "', ";
    			$isql .= "'" . date('Y-m-d') . "', ";
    			$isql .= "'" . $percentPlanejado. "', ";
    			$isql .= "'" . $valorPlanejado . "', ";
    			$isql .= "'" . $quantidadePlanejada . "', ";
    			$isql .= "'" . $saldo . "', ";
    			$isql .= "'" . $dados_form["id_status"] . "', ";
    			$isql .= "'" . $idBmsPedido . "') ";
    			
    			$db->insert($isql,'MYSQL');
        
        		if($db->erro!='')
        		{
        			$resposta->addAlert($db->erro);
        			return $resposta;
        		}
			}
		}
		
		if (!empty($mensagem))
		{
			$resposta->addAlert('Já existe(m) lançamento(s) para a(s) data(s) a seguir: '.$mensagem);
		}
		
		$resposta->addScript("xajax_atualizatabela_medicoes(".$dados_form["id_item"].");");
		$resposta->addScript("xajax_limparFormMedicoes();");
		$resposta->addScript("xajax_preenchevalor(".$dados_form['id_item'].");");
	
		//$resposta->addAlert($msg[1]);
		//}
	}
	else
	{
	    $resposta->addAlert('Por favor, preencha os campos necessários!');
	}
	
	return $resposta;
}

function altera_medicao($id_bms_medicao)
{
	$resposta = new xajaxResponse();

	$db = new banco_dados();
	
	$id_bms_medicao = str_replace('fim_', '', $id_bms_medicao);
	
	$idItem = '';
	$idBmsControle = '';
	
	$sql = "SELECT * FROM ".DATABASE.".bms_medicao
			JOIN (SELECT valor as totalItem, id_bms_item as codItem FROM ".DATABASE.".bms_item WHERE bms_item.reg_del = 0) as bms_item
			ON codItem = id_bms_item ";
	$sql .= "WHERE bms_medicao.id_bms_medicao = ".$id_bms_medicao." ";
	$sql .= "AND bms_medicao.reg_del = 0 ";
	
	$db->select($sql,'MYSQL',true);
	
	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}
	
	foreach($db->array_select as $reg)
	{
		$resposta->addAssign("id_bms_medicao", "value",$reg["id_bms_medicao"]);
		$resposta->addAssign("data_item", "value",mysql_php($reg["data"]));
		$resposta->addAssign("valor_item", "value",number_format($reg["totalItem"], 2, ',', '.'));
		$resposta->addAssign("valor_planejado", "value",number_format($reg["valor_planejado"], 2, ',', '.'));
		$resposta->addAssign("quantidade_planejada", "value",number_format($reg["quantidade_planejada"], 2, ',', '.'));
		$resposta->addAssign("valor_medido", "value",number_format($reg["valor_medido"], 2, ',', '.'));
		$resposta->addAssign("percent_planejado", "value",number_format($reg["progresso_planejado"], 2, ',', '.'));
		$resposta->addAssign("percent_medido", "value",number_format($reg["progresso_medido"], 2, ',', '.'));
		$resposta->addAssign("quantidade_medida", "value",number_format($reg["quantidade_medida"], 2, ',', '.'));
		$resposta->addScript("seleciona_combo('" . $reg["id_bms_item"] . "', 'id_item'); ");
		$resposta->addScript("seleciona_combo('" . $reg["id_bms_controle"] . "', 'id_status'); ");
		
		$idItem = $reg['id_bms_item'];

		$idBmsControle = $reg["id_bms_controle"];
	}	
	
	//status de faturado e cancelado não permitem salvar a edição. Todos os outros sim
	if (in_array($idBmsControle, array(3,4)))
	{
		$resposta->addScript("document.getElementById('btninserir_medicoes').style.display = 'none'");
	}
	else
	{
		$resposta->addScript("document.getElementById('btninserir_medicoes').style.display = ''");
	}
	
	if ($idBmsControle == 2)
	{
		$resposta->addScriptCall("habilitaMedicao(2)");
	}
	else
	{
		$resposta->addScriptCall("habilitaMedicao(".$idBmsControle.")");
	}
		
	$resposta->addAssign("btninserir_medicoes", "value", "Atualizar");
	$resposta->addEvent("btninserir_medicoes", "onclick", "xajax_atualizar_medicoes(xajax.getFormValues('frm', true));");
	
	return $resposta;
}

function atualizar_medicoes($dados_form)
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$msg = $conf->msg($resposta);
	
	$db = new banco_dados();
		
	if ($dados_form['id_status'] == 3)
	{
		$resposta->addAlert('ATENÇÃO: Para faturar este item, clique na coluna F deste registro.');
		return $resposta;
	}
	
	$sql = "SELECT id_bms_pedido, id_bms_item FROM ".DATABASE.".bms_item ";
	$sql .= "WHERE bms_item.reg_del = 0 ";
	$sql .= "AND bms_item.id_bms_item = ".$dados_form['id_item'];
		
	$pedido = $db->select($sql, 'MYSQL', function($reg, $i){
		return $reg['id_bms_pedido'];
	});
	
	$sql = "SELECT SUM(valor_planejado) AS valor_total, SUM(valor_diferenca) AS total_diferenca FROM ".DATABASE.".bms_medicao ";
	$sql .= "WHERE bms_medicao.id_bms_item = '".$dados_form["id_item"]."' ";
	$sql .= "AND bms_medicao.id_bms_medicao <> '".$dados_form['id_bms_medicao']."' ";
	$sql .= "AND bms_medicao.reg_del = 0";
		
	$db->select($sql,'MYSQL', true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}

	$regs = $db->array_select[0];
	
	$sql = "SELECT data, 0 FROM ".DATABASE.".bms_medicao ";
	$sql .= "WHERE bms_medicao.id_bms_item = '".$dados_form["id_item"]."' ";
	$sql .= "AND bms_medicao.data = '".php_mysql($dados_form["data_item"])."' ";
	$sql .= "AND bms_medicao.id_bms_medicao <> '".$dados_form['id_bms_medicao']."' ";
	$sql .= "AND bms_medicao.reg_del = 0";
	
	$db->select($sql,'MYSQL',true);
	
	$permiteAtualizar = $db->numero_registros > 0 ? false : true;

	if (!$permiteAtualizar)
	{
		$resposta->addAlert("Já existe uma medição para esta data!");
		return $resposta;
	}
	
	$valorDiferenca = 0;
	$percentDiferenca = 0;
	$quantidadeDiferenca = 0;
	
	if ($dados_form['id_status'] == 2)
	{
		$valorDiferenca = str_replace(",",".",str_replace(".","",$dados_form["valor_planejado"])) - str_replace(",",".",str_replace(".","",$dados_form["valor_medido"]));
		$percentDiferenca = str_replace(",",".",str_replace(".","",$dados_form["percent_planejado"])) - str_replace(",",".",str_replace(".","",$dados_form["percent_medido"]));
		$quantidadeDiferenca = str_replace(",",".",str_replace(".","",$dados_form["quantidade_planejada"])) - str_replace(",",".",str_replace(".","",$dados_form["quantidade_medida"]));
	}
	else if ($dados_form['id_status'] == 1 || $dados_form['id_status'] == 4)
	{
		//Quando planejado, ou cancelado, zerar todos os valores das medições
		$dados_form["quantidade_medida"] = 0;
		$dados_form["percent_medido"] = 0;
		$dados_form["quantidade_medida"] = 0;
		$dados_form["valor_medido"] = 0;
	}
	
	$usql = "UPDATE ".DATABASE.".bms_medicao SET ";
	$usql .= "progresso_planejado = '" . str_replace(",",".",str_replace(".","",$dados_form["percent_planejado"])) . "', ";
	$usql .= "progresso_medido = '" . str_replace(",",".",str_replace(".","",$dados_form["percent_medido"])) . "', ";
	$usql .= "quantidade_planejada = '" . str_replace(",",".",str_replace(".","",$dados_form["quantidade_planejada"])) . "', ";
	$usql .= "quantidade_medida = '" . str_replace(",",".",str_replace(".","",$dados_form["quantidade_medida"])) . "', ";
	$usql .= "valor_planejado = '" . str_replace(",",".",str_replace(".","",$dados_form["valor_planejado"])) . "', ";
	$usql .= "valor_medido = '" . str_replace(",",".",str_replace(".","",$dados_form["valor_medido"])) . "', ";
	$usql .= "valor_saldo = '" . (str_replace(",",".",str_replace(".","",$dados_form["valor_item"]))-str_replace(",",".",str_replace(".","",$dados_form["valor_planejado"]))) . "', ";
	$usql .= "percentual_diferenca = ".$percentDiferenca.", ";
	$usql .= "valor_diferenca = ".$valorDiferenca.", ";
	$usql .= "quantidade_diferenca = ".$quantidadeDiferenca.", ";
	$usql .= "id_bms_pedido = ".$pedido[0].", ";
	$usql .= "data = '".php_mysql($dados_form["data_item"])."', ";
	$usql .= "periodo_ini = '" . date('Y-m-d') . "', ";
	$usql .= "id_bms_controle = '" . $dados_form['id_status'] . "' ";
	$usql .= "WHERE id_bms_medicao = '".$dados_form['id_bms_medicao']."' ";
	$usql .= "AND reg_del = 0 ";

	$db->update($usql,'MYSQL');
	
	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	else
	{
	    //Deixando fixo no sistema para não reimportar
	    $usql = "UPDATE ".DATABASE.".bms_pedido SET ";
		$usql .= "alterado_manualmente = 1 ";
		$usql .= "WHERE id_bms_pedido = ".$pedido[0]." ";
		$usql .= "AND reg_del = 0 ";
	    
		$db->update($usql, 'MYSQL');
	    
		$resposta->addAlert('Registro atualizado corretamente!');
		$resposta->addScript("xajax_atualizatabela_medicoes(".$dados_form['id_item'].");");
		$resposta->addScript("xajax_atualizatabela_itens(".$pedido[0].")");
		$resposta->addScript("xajax_limparFormMedicoes();");
		$resposta->addScript("xajax_preenchevalor(".$dados_form['id_item'].");");
	}

	return $resposta;
}

/**
 * Atualização dos itens do pedido selecionado
 */
function atualizar_item($dados_form)
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$msg = $conf->msg($resposta);
	
	$db = new banco_dados();
	
	//Somando todos os itens menos o selecionado para ser alterado
	$sql = "SELECT SUM(valor) AS valor_total FROM ".DATABASE.".bms_item ";
	$sql .= "WHERE bms_item.id_bms_pedido = '".$dados_form['pedido_numero']."' ";
	$sql .= "AND bms_item.id_bms_item <> ".$dados_form['id_bms_item']." ";
	$sql .= "AND bms_item.reg_del = 0";
				
	$db->select($sql,'MYSQL', true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	
	$regs = $db->array_select[0];

	$totalFinal = $regs["valor_total"]+str_replace(",",".",str_replace(".","",$dados_form["valor"]));
	
	if(round($totalFinal,2) > round(str_replace(",",".",str_replace(".","",$dados_form["valor_total"])),2))
	{
		$resposta->addAlert("A soma dos itens é maior que o valor total.");
		return $resposta;
	}

	$usql = "UPDATE ".DATABASE.".bms_item SET ";
	$usql .= "bms_item.numero_item = '" . trim($dados_form["numero_item"]) . "', ";
	$usql .= "bms_item.descricao = '" . trim(strtoupper($dados_form["descricao_item"])) . "', ";
	$usql .= "bms_item.quantidade = '" . str_replace(",",".",str_replace(".","",$dados_form["quantidade"])) . "', ";
	$usql .= "bms_item.id_unidade = '" . trim($dados_form["id_unidade"]) . "', ";
	$usql .= "bms_item.valor = '" . str_replace(",",".",str_replace(".","",$dados_form["valor"])) . "', ";
	$usql .= "bms_item.data_item = '" . php_mysql($dados_form["data_medicao"]) . "' ";
	$usql .= "WHERE bms_item.id_bms_item = '".$dados_form['id_bms_item']."' ";
	$usql .= "AND bms_item.reg_del = 0";

	$db->update($usql,'MYSQL');

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	else
	{
	    //Deixando fixo no sistema para não reimportar
	    $usql = "UPDATE ".DATABASE.".bms_pedido SET ";
		$usql .= "alterado_manualmente = 1 ";
		$usql .= "WHERE id_bms_pedido = ".$dados_form['pedido_numero']." ";
		$usql .= "AND reg_del = 0 ";
	   
	    $db->update($usql, 'MYSQL');
	    
		$resposta->addAlert("Item atualizado corretamente!");
	}
	
	$resposta->addScript("xajax_atualizatabela_itens('".$dados_form['pedido_numero']."')");
	$resposta->addScript("xajax_preenche_combo_item_medicoes(".$dados_form['pedido_numero'].")");
	$resposta->addScript("seleciona_combo(".$dados_form['pedido_numero'].", 'id_status'); ");
	$resposta->addAssign("btninserir_itens", "value", "Inserir");
	$resposta->addEvent("btninserir_itens", "onclick", "xajax_insere_itens(xajax.getFormValues('frm', true));");
	$resposta->addScript("xajax_limparFormItens(); ");
	
	return $resposta;
}

//prenche o valor do item no textbox
function preenchevalor($id_item)
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$msg = $conf->msg($_COOKIE["idioma"],$resposta);
	
	$db = new banco_dados();

	$sql = "SELECT * FROM ".DATABASE.".bms_item ";
	$sql .= "JOIN (SELECT id_formato, codigo_formato FROM ".DATABASE.".formatos WHERE formatos.reg_del = 0) formatos ON bms_item.id_unidade = id_formato ";
	$sql .= "WHERE bms_item.id_bms_item = '".$id_item."' ";
	$sql .= "AND bms_item.reg_del = 0 ";
	$sql .= "ORDER BY bms_item.numero_item ";
	
	$db->select($sql,'MYSQL', true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}	
	
	$regs = $db->array_select[0];
	
	if (!empty($regs['data_item']) && $regs['data_item'] != '0000-00-00')
	{
		$resposta->addAssign("data_item","value",cal_days_in_month(CAL_GREGORIAN, date('m'), date('Y')).date('/m/Y'));
	}
	
	$resposta->addAssign("valor_item","value",number_format($regs["valor"], 2, ",","." ));
	$resposta->addAssign("quantidade_item","value",number_format($regs["quantidade"], 2, ",","." ));
	$resposta->addAssign("unidadeLbl","innerHTML",'('.$regs["codigo_formato"].')');
	$resposta->addAssign("unidadePlanLbl","innerHTML",'('.$regs["codigo_formato"].')');
	$resposta->addAssign("unidadeMedLbl","innerHTML",'('.$regs["codigo_formato"].')');
	$resposta->addScript("seleciona_combo('" . $regs["id_bms_controle"] . "', 'id_status'); ");
	
	$resposta->addAssign('valor_medido', 'disabled', 'disabled');
	$resposta->addAssign('percent_medido', 'disabled', 'disabled');
		
	$resposta->addScript("document.getElementById('valor_planejado').removeAttribute('disabled');");
	$resposta->addScript("document.getElementById('percent_planejado').removeAttribute('disabled');");
	$resposta->addScript("document.getElementById('quantidade_planejada').removeAttribute('disabled');");
	
	$resposta->addScript("document.getElementById('btninserir_medicoes').style.display = ''");
	
	return $resposta;
}

//calcula o valor e/ou percentual do item
function calcula_valor_percent($valor_total,$valor_digitado,$controle,$qtdTotal)
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$msg = $conf->msg($_COOKIE["idioma"],$resposta);
	
	$valor = 0;
	$qtd = 0;
	
	switch ($controle)
	{
		case 'val':
			$valor = calcula_valor($valor_total,$valor_digitado);
			
			$resposta->addAssign("valor_planejado","value",number_format($valor,2,",","."));
	
			$valor_digitado = str_replace(".", "", $valor_digitado); // Retiro o ponto
			$valor_digitado = str_replace(",", ".", $valor_digitado); // Troco a virgula por ponto...
			
			$qtdTotal = str_replace(".", "", $qtdTotal); // Retiro o ponto
			$qtdTotal = str_replace(",", ".", $qtdTotal); // Troco a virgula por ponto...
			
			//quantidade
			$qtd = ($qtdTotal * $valor_digitado / 100);
			$resposta->addAssign("quantidade_planejada","value",number_format($qtd,2,",","."));
		break;
		
		case 'per':
			$valor = calcula_percentual($valor_total,$valor_digitado);
			$resposta->addAssign("percent_planejado","value",number_format($valor,2,",","."));
			
			$valor_digitado = str_replace(".", "", $valor_digitado); // Retiro o ponto
			$valor_digitado = str_replace(",", ".", $valor_digitado); // Troco a virgula por ponto...
			
			$qtdTotal = str_replace(".", "", $qtdTotal); // Retiro o ponto
			$qtdTotal = str_replace(",", ".", $qtdTotal); // Troco a virgula por ponto...
			
			//quantidade
			$qtd = ($qtdTotal * $valor / 100);
			$resposta->addAssign("quantidade_planejada","value",number_format($qtd,2,",","."));
		break;
		
		case 'val_med':
			$valor = calcula_valor($valor_total,$valor_digitado);
			$resposta->addAssign("valor_medido","value",number_format($valor,2,",","."));
			
			$valor_digitado = str_replace(".", "", $valor_digitado); // Retiro o ponto
			$valor_digitado = str_replace(",", ".", $valor_digitado); // Troco a virgula por ponto...
			
			$qtdTotal = str_replace(".", "", $qtdTotal); // Retiro o ponto
			$qtdTotal = str_replace(",", ".", $qtdTotal); // Troco a virgula por ponto...
			
			//quantidade
			$qtd = ($qtdTotal * $valor_digitado / 100);
			$resposta->addAssign("quantidade_medida","value",number_format($qtd,2,",","."));
		break;
		
		case 'per_med':
			$valor = calcula_percentual($valor_total,$valor_digitado);
			$resposta->addAssign("percent_medido","value",number_format($valor,2,",","."));
			
			$valor_digitado = str_replace(".", "", $valor_digitado); // Retiro o ponto
			$valor_digitado = str_replace(",", ".", $valor_digitado); // Troco a virgula por ponto...
			
			$qtdTotal = str_replace(".", "", $qtdTotal); // Retiro o ponto
			$qtdTotal = str_replace(",", ".", $qtdTotal); // Troco a virgula por ponto...
			
			//quantidade
			$qtd = ($qtdTotal * $valor / 100);
			$resposta->addAssign("quantidade_medida","value",number_format($qtd,2,",","."));
		break;
	}
	
	return $resposta;	
}

function editar_item($id_bms_item)
{
	$resposta = new xajaxResponse();
	
	$db	= new banco_dados();
	
	$sql = "SELECT * FROM ".DATABASE.".bms_item ";
	$sql .= "WHERE id_bms_item = ".$id_bms_item." ";
	$sql .= "AND reg_del = 0 ";

	$db->select($sql,'MYSQL',true);
	
	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	
	foreach($db->array_select as $reg)
	{
		$resposta->addAssign('id_bms_item', 'value', $reg['id_bms_item']);
		$resposta->addAssign('numero_item', 'value', $reg['numero_item']);
		$resposta->addAssign('descricao_item', 'value', $reg['descricao']);
		$resposta->addAssign('quantidade', 'value', number_format($reg['quantidade'], 2, ',', '.'));
		$resposta->addAssign('id_unidade', 'value', $reg['id_unidade']);
		$resposta->addAssign('valor', 'value', number_format($reg['valor'], 2, ',', '.'));
		
		if ($reg['id_unidade'] == 6)
		{
			$resposta->addScript('document.getElementById("spanHoraCalculo").style.display="block";');
			$resposta->addAssign('valor_hora', 'value', number_format($reg['valor']/$reg['quantidade'], 2, ',', '.'));
		}
		else
		{
			$resposta->addScript('document.getElementById("spanHoraCalculo").style.display="none";');
		}
	}
	
	$resposta->addAssign('btninserir_itens', 'value', 'Alterar');
	$resposta->addEvent("btninserir_itens", "onclick", "xajax_atualizar_item(xajax.getFormValues('frm'));");
	
	return $resposta;
}

function editar($os)
{
	$resposta = new xajaxResponse();
	
	$os = explode('_', $os);
	
	$resposta->addAssign('id_os', 'value', $os[0]);
	
	$db	= new banco_dados();
	
	foreach($db->array_select as $reg)
	{
		//$resposta->addAssign('ref_cliente', 'value', trim($reg['AF8_REFCLI']));
	}
		
	$sql = "SELECT 
				funcionario, nome_contato, bms_pedido.id_os, id_bms_pedido, obs, ref_cliente, condicao_pgto, data_pedido, data_termino, valor_pedido, ordem_servico.id_empresa_erp, ordem_servico.os, ordem_servico.id_os
			FROM 
				".DATABASE.".bms_pedido 
				LEFT JOIN  ".DATABASE.".ordem_servico ON ordem_servico.id_os = bms_pedido.id_os AND ordem_servico.reg_del = 0  
			    LEFT JOIN(SELECT id_funcionario, funcionario FROM ".DATABASE.".funcionarios WHERE funcionarios.reg_del = 0) funcs ON id_funcionario = id_cod_coord 
			    LEFT JOIN(SELECT * FROM ".DATABASE.".contatos WHERE contatos.reg_del = 0) contatos ON id_contato = id_cod_resp 
			where bms_pedido.reg_del = 0 AND bms_pedido.id_os = ".intval($os[0]);
	
	$db->select($sql,'MYSQL',true);
	
	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	
	$idOs = $db->array_select[0]['id_os'];
	
	foreach($db->array_select as $reg)
	{
		$resposta->addAssign('id_cliente', 'value', $reg['id_empresa_erp']);
		$resposta->addAssign('coord_cli', 'value', $reg['nome_contato']);
		$resposta->addAssign('coord_dvm', 'value', $reg['Funcionario']);
		
		$resposta->addAssign('valor_total', 'value', number_format($reg['valor_pedido'], 2, ',', '.'));
		$resposta->addAssign('data_pedido', 'value', mysql_php($reg['data_pedido']));
		$resposta->addAssign('data_termino', 'value', mysql_php($reg['data_termino']));
		$resposta->addAssign('obs_pedido', 'value', $db->array_select[0]['obs']);
		$resposta->addAssign('ref_cliente', 'value', $db->array_select[0]['ref_cliente']);
		$resposta->addAssign('lblOS', 'innerHTML', sprintf("%05s", $db->array_select[0]['os']));
	}
	
	
	if (!empty($db->array_select[0]['id_bms_pedido']))
	{
		$resposta->addAssign('pedido_numero', 'value', $db->array_select[0]['id_bms_pedido']);
		$resposta->addAssign('btninserir', 'value', 'Alterar');
	}
	else
	{
		$resposta->addAssign('btninserir', 'value', 'Inserir');
	}
	
	$resposta->addScript("xajax_atualizatabela_itens(".$db->array_select[0]['id_bms_pedido'].");");
	$resposta->addScript("xajax_preenche_combo_item_medicoes(".$db->array_select[0]['id_bms_pedido'].")");
	
	$resposta->addEvent("btnvoltar", "onclick", "window.location.reload();");
	
	$resposta->addScript("xajax_limparFormMedicoes(1);");
	$resposta->addAssign("div_medicoes", 'innerHTML', '');
	
	return $resposta;
}

/**
 * Exclusão de itens do pedido
 * @param id_item $id
 */
function excluir($id)
{
	$resposta = new xajaxResponse();

	$db = new banco_dados();
	
	$usql = "UPDATE ".DATABASE.".bms_item SET ";
	$usql .= "reg_del = 1, ";
	$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
	$usql .= "data_del = '".date('Y-m-d')."' ";
	$usql .= "WHERE id_bms_item = ".$id." ";
	$usql .= "AND reg_del = 0 ";
	
	$db->update($usql, 'MYSQL');
	
	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	else
	{
		
		$usql = "UPDATE ".DATABASE.".bms_medicao SET ";
		$usql .= "reg_del = 1, ";
		$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
		$usql .= "data_del = '".date('Y-m-d')."' ";
		$usql .= "WHERE id_bms_item = ".$id." ";
		$usql .= "AND reg_del = 0 ";		
		
		$db->update($usql, 'MYSQL');
		
		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
		
		$resposta->addAlert("Item excluído corretamente!");
	}
	
	$resposta->addScript("xajax_atualizatabela_itens(document.getElementById('pedido_numero').value);");
	
	$resposta->addAssign("btninserir_itens", "value", "Inserir");
	
	$resposta->addEvent("btninserir_itens", "onclick", "xajax_insere_itens(xajax.getFormValues('frm', true));");
	
	$resposta->addScript("xajax_limparFormItens(); ");
	
	return $resposta;
}

/**
 * Exclusão de itens do medicoes
 * @param id_item $id
 */
function excluir_medicao($id, $id_item)
{
	$resposta = new xajaxResponse();

	$id = explode(',', $id);
	
	$ids = '';
	
	$complExcluir = '';
	
	if (count($id) > 0)
	{
	    $virgula = '';
	 
		foreach($id as $key)
	    {
	        if (!strpos($key, '_'))
	        {
	           $ids .= $virgula.$key;
	           $virgula = ',';
	        } 
	    }
	    
	    //Somente 1 e 2 serão excluídos
		if ($_SESSION['admin'] == 1)
		{
		   $complExcluir = ' AND id_bms_controle IN(1,2)';
		}
	}
	
	$db = new banco_dados();
	
	$usql = "UPDATE ".DATABASE.".bms_medicao SET ";
	$usql .= "reg_del = 1, ";
	$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
	$usql .= "data_del = '".date('Y-m-d')."' ";
	$usql .= "WHERE id_bms_medicao IN(".$ids.") ";
	$usql .= "AND reg_del = 0 ";
	
	$db->update($usql, 'MYSQL');
	
	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		return $resposta;
	}
	else
	{
	    $resposta->addScript("desbloquearBotaoExcluir();");
		$resposta->addAlert("Medição excluída corretamente!");
	}
	
	$resposta->addScript("xajax_atualizatabela_medicoes(".$id_item.");");
	
	$resposta->addAssign("btninserir_itens", "value", "Inserir");
	
	$resposta->addEvent("btninserir_itens", "onclick", "xajax_insere_itens(xajax.getFormValues('frm', true));");
	
	$resposta->addScript("xajax_limparFormMedicoes();");
	
	$resposta->addScript("xajax_preenchevalor(".$id_item.");");
	
	return $resposta;
}

/**
 * Faturar iten do medicoes
 * @param id_item $id
 */
function faturar_medicao($id, $id_item, $num_nf, $valor_faturado)
{
	$resposta = new xajaxResponse();

	$db = new banco_dados();
	
	//Buscando todos os itens com valores medidos na mesma data e para o mesmo item 
	$sql = "SELECT
				DISTINCT medicoes.id_bms_medicao, medicao.valor_medido
			FROM
				".DATABASE.".bms_medicao medicao
				JOIN (
					SELECT * FROM ".DATABASE.".bms_item WHERE bms_item.reg_del = 0 
				) item ON item.id_bms_item = medicao.id_bms_item
				LEFT JOIN (
					SELECT 
						DISTINCT *
					FROM 
						".DATABASE.".bms_medicao
			            JOIN(
							SELECT id_bms_pedido idPedido, id_bms_item idItem, descricao descItemMesmaData FROM ".DATABASE.".bms_item WHERE bms_item.reg_del = 0 
			            ) ped
			            ON ped.idItem = bms_medicao.id_bms_item
					 WHERE bms_medicao.reg_del = 0 AND valor_medido IS NOT NULL
				) medicoes 
				ON medicoes.idPedido = item.id_bms_pedido
				AND medicoes.data = medicao.data
			WHERE
				medicao.id_bms_medicao = ".$id."  
				AND medicao.reg_del = 0 ";
	
	$valorMedicaoAtual = 0;
	$itens = $db->select($sql, 'MYSQL', function ($reg, $i) use(&$valorMedicaoAtual){
	    if (empty($valorMedicaoAtual))
	        $valorMedicaoAtual = $reg['valor_medido'];
	    
		return $reg['id_bms_medicao'];
	});
	
	$itens = implode(',', $itens);
	
	$difFaturado = !empty($valor_faturado) ? $valorMedicaoAtual - str_replace(',', '.', str_replace('.', '', $valor_faturado)) : 0;
	
	$usql = "UPDATE ".DATABASE.".bms_medicao SET ";
	$usql .= "id_bms_controle = 3, ";
	$usql .= "numero_nf = '".$num_nf."' ";
	$usql .= "WHERE id_bms_medicao IN(".$itens.") ";
	$usql .= "AND reg_del = 0 ";
	
	$db->update($usql, 'MYSQL');
	
	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		return $resposta;
	}
	else
	{
	    //Alterando o campo dif_faturado caso a fatura seja diferente do valor da nota fiscal
	    //Adicionado em 05/10/2017
	    if ($difFaturado > 0)
	    {
    	    $usql = "UPDATE ".DATABASE.".bms_medicao SET ";
    	    $usql .= "dif_faturado = '".$difFaturado."' ";
    	    $usql .= "WHERE id_bms_medicao = ".$id." ";
			$usql .= "AND reg_del = 0 ";
    	    
    	    $db->update($usql, 'MYSQL');
	    }
	    
        $resposta->addAlert("Medição faturada corretamente!");
	}
	
	$resposta->addScript("xajax_atualizatabela_medicoes(".$id_item.");");
	
	$resposta->addAssign("btninserir_itens", "value", "Inserir");
	
	$resposta->addEvent("btninserir_itens", "onclick", "xajax_insere_itens(xajax.getFormValues('frm', true));");
	
	$resposta->addScript("xajax_limparFormMedicoes(); ");
	
	$resposta->addScript("divPopupInst.destroi();");
	
	$resposta->addScript("xajax_preenchevalor(".$id_item.");");
	
	return $resposta;
}

function inserePedidoAutomatico($AF5Orcame, $idOs)
{
	$db = new banco_dados();

	/*
	$sql = "SELECT AF5_TOTAL, AF5_ORCAME, AF1_DTAPRO, AF1_CLIENT, AF1_LOJA, AF1_TPORC, AF1_DTFIM, AF8_FASE ". 
			"FROM AF5010 ".
			"LEFT JOIN (SELECT AF8_PROJET, AF8_FASE FROM AF8010 WHERE D_E_L_E_T_ = '') AF8010 ON AF8_PROJET = AF5_ORCAME ".
			"JOIN (SELECT AF1_ORCAME, AF1_DTAPRO, AF1_CLIENT, AF1_LOJA, AF1_TPORC, AF1_DTFIM FROM AF1010 WHERE D_E_L_E_T_ = '' AND AF1_ORCAME = '".$AF5Orcame."') AF1010 ON AF1_ORCAME = AF5_ORCAME ".
			"WHERE ". 
				"AF5010.D_E_L_E_T_ = '' ".
				"AND AF5_ORCAME = '".$AF5Orcame."' ".
				"AND AF5_NIVEL = '001' ";
	
		$novoPedido = $db->select($sql, 'MSSQL', function($reg, $i) use($idOs){
		if (trim($reg['AF1_DTAPRO']) == '' || intval($reg['AF5_TOTAL']) == 0)
		{
			return array('id_bms_pedido' => '', 'data_pedido' => protheus_mysql($reg['AF1_DTAPRO']), 'valor_pedido' => $reg['AF5_TOTAL']);
			return;
		}

		*/
		
		//Numero total de meses entre inicio e fim do orçamento
		//$qtdMeses = numero_meses(protheus_mysql($reg['AF1_DTAPRO']), protheus_mysql($reg['AF1_DTFIM']));
		
		//$dataInicio = explode('-', protheus_mysql($reg['AF1_DTAPRO']));
		
		//Regra <10 e >10 (Aprovação orçamento até dia 10, bms dia 20 do mês de aprovação, senão dia 20 do mês subsequente á aprovação
		$regra = $dataInicio[2] <= 10 ? 1 : 0; 
		$diasMedicoes = array();
		
		$mesInicial = $dataInicio[1];
		$anoInicial = $dataInicio[0];
		
		//Regra pronta
		$dataInicial = $regra == 1 ? $anoInicial.'-'.$mesInicial.'-20' : dateAdd($anoInicial.'-'.$mesInicial.'-20', 1, 'Y-m-d', 'months'); 
		$diasMedicoes[] = $dataInicial;
		
		for($mes=0; $mes<$qtdMeses; $mes++)
		{
			//$diasMedicoes[] = $dataInicial;
			$dataInicial = dateAdd($dataInicial, 1, 'Y-m-d', 'months');
			$diasMedicoes[] = $dataInicial;
		}
		
		/*
		$isql = "INSERT INTO ".DATABASE.".bms_pedido (id_os, valor_pedido, data_pedido, id_cliente_protheus, id_loja_protheus, data_termino) ";
		$isql .= "VALUES (";
		$isql .= intval($reg['AF5_ORCAME']).", '".$reg['AF5_TOTAL']."', '".protheus_mysql($reg['AF1_DTAPRO'])."', '".$reg['AF1_CLIENT']."', '".$reg['AF1_LOJA']."', '".$reg['AF1_DTFIM']."'";
		$isql .= ")";
		
		$db2 = new banco_dados();
		$db2->insert($isql, 'MYSQL');
		*/
		/*
		$retorno = array('id_bms_pedido' => $db2->insert_id, 'data_pedido' => protheus_mysql($reg['AF1_DTAPRO']));

		if (empty($db2->erro))
		{
			//Buscando os itens(EDT'S) nos níveis 02 e 03
			$sql = "SELECT 
						DISTINCT AF5_EDT, AF5_DESCRI, AF5_QUANT, AF5_UM, AF5_TOTAL, AF5_NIVEL, AF2_GRPCOM, AF2_ORCAME
					FROM 
						AF5010
						LEFT JOIN AF2010 ON AF2_ORCAME = AF5_ORCAME AND AF2_EDTPAI = AF5_EDT AND AF2010.D_E_L_E_T_ = ''
					WHERE 
						AF5_ORCAME = '".$reg['AF5_ORCAME']."' AND AF5010.D_E_L_E_T_ = '' AND AF5_NIVEL IN('002','003');";
			
			$db2->select($sql, 'MSSQL', true);
			
			$arrTpUnidades = array('DES' => '13', 'MOE' => '6', 'CIV' => '8', 'COR' => '8', 'PLN' => '8', 'SUP' => '8', 'CIV' => '8');
			
			if ($db2->numero_registros_ms > 0)
			{
				$j = 0;
				$k = 0;
				foreach($db2->array_select as $item)
				{
					if ($item['AF5_NIVEL'] == '002')
					{
						$j = 0;
						$k++; 
					}
					else
					{
						$j++;
					}
					
					$numItem = $k.'.'.$j;
						
					$unid = isset($arrTpUnidades[$item['AF2_GRPCOM']]) ? $arrTpUnidades[$item['AF2_GRPCOM']] : 13;
					
					$isql = "INSERT INTO ".DATABASE.".bms_item ";
					$isql .= "(id_bms_pedido, id_os, numero_item, descricao, quantidade, id_unidade, data_item, valor, grupo_comercial, nivel) VALUES ";
					$isql .= "(".$retorno['id_bms_pedido'].", ".intval($reg['AF5_ORCAME']).", ".$numItem.", '".$item['AF5_DESCRI']."', ".$item['AF5_QUANT'].", $unid, '".date('Y-m-d')."', '".$item['AF5_TOTAL']."', '".$item['AF2_GRPCOM']."', '".$item['AF5_NIVEL']."') ";
					
					$db2->insert($isql, 'MYSQL');
					
					//Inserindo as medi��es planejadas caso haja encontrado data de fim de projeto
					if (count($diasMedicoes) > 0 && empty($db2->erro))
					{
						$virgula = '';
						$idItem = $db2->insert_id;
						
						//TOTAIS PARA AS PREVIS�ES DE MEDIÇÃO
						$totalDividido = $item['AF5_TOTAL'] / count($diasMedicoes);
						$qtdDividida = $item['AF5_QUANT'] / count($diasMedicoes);
						$percentDividido = $totalDividido / $item['AF5_TOTAL'] * 100; 
						$saldoPlanejado = $item['AF5_TOTAL'] - $totalDividido;
						
						$isql = "INSERT INTO ".DATABASE.".bms_medicao ";
						$isql .= "(id_bms_item, data, data_status, progresso_planejado, valor_planejado, quantidade_planejada, valor_saldo, id_bms_controle) VALUES ";
						foreach($diasMedicoes as $dia)
						{
							$saldoPlanejado -= $totalDividido; 
							$isql .= $virgula."(".$idItem.", '".$dia."', '".date('Y-m-d')."', '".$percentDividido."', '".$totalDividido."', '".$qtdDividida."', '".$saldoPlanejado."', 1)";
							$virgula = ',';
						}
						
						$db2->insert($isql, 'MYSQL');
					}
				}
			}
		}
		*/
		
		return $retorno;
	//});
	
	return $novoPedido;
}

function atualizatabela($filtro = '', $status = '')
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();
	
	$sql_filtro = "";
	
	$sql_texto = "";
	
	if($filtro!="")
	{
		$sql_texto = str_replace('  ', ' ', AntiInjection::clean($filtro));
		$sql_texto = str_replace(' ', '%', '%'.$sql_texto.'%');
		
		$sql_filtro = " AND (LPAD(ordem_servico.os,10,'0') LIKE '%".$sql_texto."%' ";
		$sql_filtro .= " OR DATE_FORMAT(data_pedido,'%d/%m/%Y') LIKE '".$sql_texto."' ";
		$sql_filtro .= " OR descricao LIKE '".$sql_texto."') ";
	}
	
	//Trazemos somente Em Execução, As Built e OS Por ADM
	$clausulaSatus = 'ordem_servico_status.id_os_status IN(1,2,5,7,3,14,15,16,17,18,19)';
	if (!empty($status))
	{
		$clausulaSatus = "ordem_servico.id_os_status IN('".$status."') ";
	}
	
	$sql = "SELECT 
				LPAD(ordem_servico.os,10,'0') numero_proposta, ordem_servico.id_os id_os, CONCAT(empresa,' - ',ordem_servico.descricao) descricao_proposta, bms_pedido.id_bms_pedido, 
				data_pedido, data_termino, os_status, ordem_servico_status.id_os_status,
				totalMedido / valor_pedido totalMedido, arquivo_pedido, arquivo_contrato, arquivo_proposta, nf,
                difFaturado, numero_nf_saldo, bms_pedidos_informacoes.id_bms_pedidos_informacoes, reembolso_despesa,
                statusAtuais
			FROM
				".DATABASE.".ordem_servico 
				JOIN ".DATABASE.".ordem_servico_status ON ordem_servico_status.id_os_status = ordem_servico.id_os_status AND ordem_servico_status.reg_del = 0 
				JOIN ".DATABASE.".empresas ON empresas.id_empresa_erp = ordem_servico.id_empresa_erp AND empresas.reg_del = 0 
				JOIN ".DATABASE.".bms_pedido ON bms_pedido.id_os = ordem_servico.id_os AND bms_pedido.reg_del = 0
				LEFT JOIN(
					SELECT
						bms_medicao.id_bms_pedido idPedido, SUM(valor_medido) totalMedido, MAX(numero_nf) nf, SUM(dif_faturado) difFaturado,
                        GROUP_CONCAT(DISTINCT bms_medicao.id_bms_controle) statusAtuais
					FROM
						".DATABASE.".bms_medicao
                        JOIN ".DATABASE.".bms_item ON bms_item.id_bms_item = bms_medicao.id_bms_item
					WHERE
						bms_medicao.reg_del = 0
					GROUP BY
						bms_medicao.id_bms_pedido
	            ) medicao
	            ON idPedido = id_bms_pedido
                LEFT JOIN ".DATABASE.".bms_pedidos_informacoes ON bms_pedidos_informacoes.id_os = bms_pedido.id_os AND bms_pedidos_informacoes.reg_del = 0 
			WHERE 
				(data_pedido >= '2017-07-01' OR ordem_servico.id_os IN(SELECT id_os FROM ".DATABASE.".bms_excecoes WHERE bms_excecoes.reg_del = 0)) AND ordem_servico.reg_del = 0 AND
				".$clausulaSatus." ".$sql_filtro."
			GROUP BY 
				ordem_servico.os, descricao, bms_pedido.id_bms_pedido, data_pedido, os_status
			ORDER BY ordem_servico.os";

	$db->select($sql,'MYSQL',true);
	
	$dados = $db->array_select;
	
	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	
	//Todos os pedidos em aberto
	$xml[0] = new XMLWriter();
	$xml[0]->openMemory();
	$xml[0]->startElement('rows');
	
	//solicitacao_documentos finalizados
	$xml[1] = new XMLWriter();
	$xml[1]->openMemory();
	$xml[1]->startElement('rows');
	
	foreach($dados as $reg)
	{
	    $reg['numero_proposta'] = sprintf('%010s', $reg['numero_proposta']);
		$lista = 0;
		
		//Se estiver 100% medido e o status final for faturado (3), vai pra lista de finalizados.
        if ($reg['totalMedido'] >= 1 && !empty($reg['nf']))
            $lista = 1;
        else if (key_exists('ultimoStatus', $reg) && in_array($reg['ultimoStatus'], array(17,19)))
            continue;
		
        $reticencias = strlen($reg['descricao_proposta']) > 85 ? '...' : '';
        
        $texto = $reticencias == '...' ? '<span style="float:left;width:93%;">'.substr($reg["descricao_proposta"], 0, 85).$reticencias.'</span><span class="icone icone-inserir cursor" onclick=showModalTexto("'.str_replace(' ', '#', $reg["descricao_proposta"]).'");></span>' : $reg["descricao_proposta"];
            
		$xml[$lista]->startElement('row');
		$xml[$lista]->writeAttribute('id', $reg["numero_proposta"].'_'.$reg['id_os']);
        $xml[$lista]->writeElement('cell', $reg["numero_proposta"]);
        $xml[$lista]->writeElement('cell', $texto);
        $xml[$lista]->writeElement('cell', mysql_php($reg['data_pedido']));
        $xml[$lista]->writeElement('cell', mysql_php($reg['data_termino']));
        $xml[$lista]->writeElement('cell', $reg['os_status']);
        $xml[$lista]->writeElement('cell', number_format($reg['totalMedido']*100, 2, '.', ',').'%');
        
        $hiddenNF = '<input type="hidden" id="nfDifFat_'.$reg['id_bms_pedido'].'" value="'.$reg['numero_nf_saldo'].'" name="nfDifFat_'.$reg['id_bms_pedido'].'" />';
        $difFaturado = $reg['difFaturado'] > 0 ? '<span id="span_'.$reg['id_bms_pedido'].'" class="cursor" style="text-decoration:underline;" onclick=showModalNFSaldo('.$reg['id_bms_pedido'].',document.getElementById("nfDifFat_'.$reg['id_bms_pedido'].'").value);>'.$hiddenNF.number_format($reg['difFaturado'], 2, '.', ',')."</span>" : number_format($reg['difFaturado'], 2, '.', ',');
        $xml[$lista]->writeElement('cell', $difFaturado);
        
        $checked = $reg['reembolso_despesa'] == 1 ? 'checked="checked"' : '';
        $xml[$lista]->writeElement('cell', '<input type="checkbox" id="chkRD" '.$checked.' onclick="xajax_marcarRD('.$reg['id_bms_pedido'].',this.checked==true?1:0);" name="chkRD" value="'.$reg['reembolso_despesa'].'" />');
		
        if (!empty($reg['arquivo_pedido']) || !empty($reg['arquivo_contrato']) || !empty($reg['arquivo_proposta']))
		    $xml[$lista]->writeElement('cell', "<span class=\'icone icone-clips cursor\' onclick=xajax_modal_anexar_pedido(".$reg['numero_proposta'].") />");
		else
		    $xml[$lista]->writeElement('cell', '<span class="icone icone-seta-cima cursor" onclick=xajax_modal_anexar_pedido('.$reg['numero_proposta'].') />');
		
	    if (empty($reg['id_bms_pedidos_informacoes']))
		{
	       $xml[$lista]->writeElement('cell', '<span class="icone icone-balao-opaco cursor" onclick=xajax_modal_informacoes_pedido('.$reg['numero_proposta'].') />');
		}
		else
		{
		    $xml[$lista]->writeElement('cell', '<span class="icone icone-balao cursor" onclick=xajax_modal_informacoes_pedido('.$reg['numero_proposta'].') />');
		}

		if (strpos($reg['statusAtuais'], '1') > -1)
		{
		  $xml[$lista]->writeElement('cell', '<span class="icone icone-remover cursor" onclick=xajax_modal_cancelar_saldo('.$reg['id_bms_pedido'].') />');
		}
		else
		{
		    $xml[$lista]->writeElement('cell', '');
		}
		
		if ($_SESSION['admin'] == 1)
		{
		    $xml[$lista]->writeElement('cell', '<span class="icone icone-excluir cursor" onclick=if(confirm("Deseja&nbsp;excluir&nbsp;este&nbsp;pedido?")){xajax_excluir_pedido("'.$reg["id_bms_pedido"].'");}; />');
		}
		    
	    $xml[$lista]->endElement();		
	}
	
	$xml[0]->endElement();
	$xml[1]->endElement();
	
	$conteudo[0] = $xml[0]->outputMemory(true);
	$conteudo[1] = $xml[1]->outputMemory(true);
	
	$resposta->addScript("seleciona_combo('14', 'id_unidade'); ");
	$resposta->addScript("grid('div_pedidos', true, '415', '".$conteudo[0]."');");
	$resposta->addScript("grid('div_pedidos_finalizados', true, '415', '".$conteudo[1]."');");
	$resposta->addAssign('divNumeroRegistros', 'innerHTML', $db->numero_registros);
	
	return $resposta;
}

/**
 * Atualização dos itens do pedido selecionado
 * @param id_item $id
 */
function altera_pedido($id)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados();

	$sql = "SELECT * FROM ".DATABASE.".bms_pedido ";
	$sql .= "WHERE bms_pedido.id_bms_pedido = ".$id." ";
	$sql .= "AND bms_pedido.reg_del = 0 ";
	
	$db->select($sql,'MYSQL',true);
	
	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	
	$reg = $db->array_select[0];
	
	$resposta->addAssign("valor_total", "value", number_format($reg['valor_pedido'], 2,',', '.'));
	$resposta->addAssign("data_pedido", "value", mysql_php($reg['data_pedido']));
	$resposta->addAssign("data_termino", "value", mysql_php($reg['data_termino']));
	$resposta->addAssign("ref_cliente", "value", trim($reg['ref_cliente']));
	$resposta->addAssign("obs_pedido", "value", $reg['obs']);
	$resposta->addScript("seleciona_combo('".$reg['id_os']."', 'id_os'); ");
	$resposta->addScript("seleciona_combo('".$reg['condicao_pgto']."', 'condicao_pgto'); ");
	
	$resposta->addAssign("id_os", "disabled", 'disabled');
	$resposta->addScript("xajax_atualizatabela_itens('".$id."')");
	$resposta->addScript("xajax_preenche_combo_item_medicoes('".$id."')");
	$resposta->addScript("seleciona_combo(".$id.", 'id_status'); ");
	$resposta->addAssign("pedido_numero", "value", $id);
	$resposta->addAssign("div_medicoes", "innerHTML", '');
	$resposta->addScript("xajax_limparFormMedicoes();");
	$resposta->addAssign("valor_item", "value", '');
	$resposta->addAssign("btninserir", "value", "Alterar");
	$resposta->addEvent("btnvoltar", "onclick", "document.location.reload(true);");
	$resposta->addScript("seleciona_combo('14', 'id_unidade'); ");
	
	$sql = "SELECT 
				Funcionario, nome_contato
			FROM 
				".DATABASE.".ordem_servico
			    LEFT JOIN(SELECT id_funcionario, funcionario FROM ".DATABASE.".funcionarios WHERE funcionarios.reg_del = 0) funcs ON id_funcionario = id_cod_coord
			    LEFT JOIN(SELECT * FROM ".DATABASE.".contatos WHERE contatos.reg_del = 0) contatos ON id_contato = id_cod_resp
			where id_os = ".$reg['id_os']." 
			AND ordem_servico.reg_del = 0 ";
	
	$db->select($sql,'MYSQL',true);
	
	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	
	foreach($db->array_select as $reg)
	{
		$resposta->addAssign('coord_cli', 'value', $reg['nome_contato']);
		$resposta->addAssign('coord_dvm', 'value', $reg['funcionario']);		
	}
		
	return $resposta;
}

function excluir_pedido($id)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados();
	
	//Excluindo o pedido
	$usql = "UPDATE ".DATABASE.".bms_pedido SET ";
	$usql .= "bms_pedido.reg_del = 1, ";
	$usql .= "bms_pedido.reg_who = ".$_SESSION['id_funcionario'].", ";
	$usql .= "bms_pedido.data_del = '".date('Y-m-d')."' ";
	$usql .= "WHERE bms_pedido.id_bms_pedido = ".$id." ";
	$usql .= "AND reg_del = 0 ";
	
	$db->update($usql, 'MYSQL');
	
	if ($db->erro != '')
	{
		$resposta->addAlert('Houve uma falha ao tentar excluir o pedido! '.$db->erro);
	}
	else
	{
		//Excluindo as observações
		$usql = "UPDATE ".DATABASE.".bms_observacoes
				JOIN(
					SELECT id_bms_item, id_bms_medicao idMedicao, id_bms_pedido FROM ".DATABASE.".bms_item
				    JOIN(
						SELECT id_bms_item idItem, id_bms_medicao FROM ".DATABASE.".bms_medicao WHERE bms_medicao.reg_del = 0
				    ) med
				    ON idItem = id_bms_item
				    WHERE bms_item.reg_del = 0
				) item
				ON idMedicao = id_bms_medicao
				
				SET bms_observacoes.reg_del = 1, 
				bms_observacoes.reg_who = ".$_SESSION['id_funcionario'].", 
				bms_observacoes.data_del = '".date('Y-m-d')."'
				
				WHERE 
				bms_observacoes.reg_del = 0 AND 
				item.id_bms_pedido = ".$id;
		
		$db->update($usql, 'MYSQL');
	
		if ($db->erro != '')
		{
			$resposta->addAlert('Houve uma falha ao tentar excluir o pedido! '.$db->erro);
		}
		
		$usql = "UPDATE ".DATABASE.".bms_item SET ";
		$usql .= "bms_item.reg_del = 1, ";
		$usql .= "bms_item.reg_who = ".$_SESSION['id_funcionario'].", ";
		$usql .= "bms_item.data_del = '".date('Y-m-d')."' ";
		$usql .= "WHERE bms_item.reg_del = 0 ";
		$usql .= "AND bms_item.id_bms_pedido = ".$id; 
		
		$db->update($usql, 'MYSQL');
	
		if ($db->erro != '')
		{
			$resposta->addAlert('Houve uma falha ao tentar excluir o pedido! '.$db->erro);
		}
		
		$usql = "UPDATE ".DATABASE.".bms_medicao SET ";
		$usql .= "bms_medicao.reg_del = 1, ";
		$usql .= "bms_medicao.reg_who = ".$_SESSION['id_funcionario'].", ";
		$usql .= "bms_medicao.data_del = '".date('Y-m-d')."' ";
		$usql .= "WHERE bms_medicao.reg_del = 0 ";
		$usql .= "AND bms_medicao.id_bms_pedido = ".$id;
		
		$db->update($usql, 'MYSQL');
	
		if ($db->erro != '')
		{
			$resposta->addAlert('Houve uma falha ao tentar excluir o pedido! '.$db->erro);
		}
		else
			$resposta->addAlert('Registro excluido corretamente!');
	}
	
	$resposta->addScript('xajax_atualizatabela();');
	
	return $resposta;
}

function limparFormItens()
{
	$resposta = new xajaxResponse();
	
	$resposta->addAssign("btninserir_itens", "value", "Inserir");
	$resposta->addEvent("btninserir_itens", "onclick", "xajax_insere_itens(xajax.getFormValues('frm', true));");
	
	//$resposta->addAssign("numero_item", "value",'1.0');
	$resposta->addAssign("id_bms_item", "value",'');
	$resposta->addAssign("descricao_item", "value",'');
	$resposta->addAssign("quantidade", "value",'');
	$resposta->addScript("seleciona_combo('14', 'id_unidade'); ");
	$resposta->addAssign("valor", "value",'');
	$resposta->addAssign("valor_hora", "value",'');
	
	return $resposta;
}

function limparFormMedicoes($limparTodos = false)
{
	$resposta = new xajaxResponse();
	
	$resposta->addAssign("btninserir_medicoes", "value", "Inserir");
	$resposta->addEvent("btninserir_medicoes", "onclick", "xajax_insere_medicoes(xajax.getFormValues('frm', true));");
	
	$resposta->addAssign("id_bms_medicao", "value",'');
	$resposta->addAssign("data_item", "value",cal_days_in_month(CAL_GREGORIAN, date('m'), date('Y')).date('/m/Y'));
	$resposta->addAssign("valor_planejado", "value",'');
	$resposta->addAssign("percent_planejado", "value",'');
	$resposta->addScript("seleciona_combo('1', 'id_status'); ");
	$resposta->addAssign("valor_medido", "value",'');
	$resposta->addAssign("percent_medido", "value",'');
	$resposta->addAssign("quantidade_planejada", "value",'');
	$resposta->addAssign("quantidade_medida", "value",'');
	$resposta->addAssign("chk_replicar", "checked",false);
	$resposta->addAssign("txt_num_replicas", "value",'');
	$resposta->addAssign("datas_replica_definidas", "value",'');
	$resposta->addAssign("btnexcluir_selecionados", "disabled",true);
	
	if ($limparTodos)
	{
		$resposta->addAssign("valor_item", "value",'');
		$resposta->addAssign("quantidade_item", "value",'');
		$resposta->addAssign("data_item", "value",'');
		$resposta->addAssign("quantidade_item", "value",'');	
	}

	$resposta->addScript("document.getElementById('btninserir_medicoes').style.display = ''");
	
	return $resposta;
}

function gerar_bms($id_bms_pedido, $data_bms)
{
	$resposta = new xajaxResponse();
	$db	= new banco_dados();
	
	if (empty($id_bms_pedido))
	{
		$resposta->addAlert('Por favor, selecione um pedido da lista!');
		return $resposta;
	}
	
	if (empty($data_bms))
	{
		$resposta->addAlert('Por favor, digite a data da medição!');
		return $resposta;
	}
	
	$data_medicao = php_mysql($data_bms);
	
	$sql = 
	"SELECT * FROM
	  ".DATABASE.".bms_pedido p
		JOIN (SELECT descricao, id_os as osCod, os as osNum FROM ".DATABASE.".ordem_servico WHERE ordem_servico.reg_del = 0) AS os ON osCod = id_os
		JOIN (SELECT id_bms_item, numero_item, descricao as descItem, quantidade, id_unidade, valor, id_bms_pedido, data_item FROM ".DATABASE.".bms_item WHERE bms_item.reg_del = 0) i ON i.id_bms_pedido = p.id_bms_pedido
		JOIN (SELECT id_bms_item idItemMedido, id_bms_medicao, data_status, valor_medido, id_bms_controle, progresso_medido FROM ".DATABASE.".bms_medicao WHERE bms_medicao.reg_del = 0 AND bms_medicao.data = '".$data_medicao."' AND bms_medicao.id_bms_controle NOT IN(1,4)) m ON idItemMedido = i.id_bms_item	
	WHERE p.reg_del = 0
	AND p.id_bms_pedido = ".$id_bms_pedido.";";

	$db->select($sql,'MYSQL',true);
	
	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	
	foreach($db->array_select as $reg)
	{
		$idsBmsMedicao[$reg['id_bms_medicao']] = $reg['id_bms_medicao'];
	}
	
	if ($db->numero_registros == 0)
	{
		$resposta->addAlert('Não existem medições na data para este pedido!');
		return $resposta;
	}
	
	$usql = "UPDATE ".DATABASE.".bms_medicao SET ";
	$usql .= "id_bms_controle = 5 ";
	$usql .= "WHERE id_bms_medicao IN(".implode(',', $idsBmsMedicao).") ";
	$usql .= "AND reg_del = 0 ";
	
	$db->update($usql, 'MYSQL');
	
	if ($db->erro != '')
	{
		$resposta->addAlert('Houve uma falha ao tentar atualizar o registro de medições.');
		return $resposta;
	}
	
	$resposta->addScript("window.open('./relatorios/bms_excel.php?id_solicitacao_documento={$id_bms_pedido}&data={$data_medicao}', '_blank');");
	
	return $resposta;
}

function gerar_relatorio($nomeArquivoRelatorio, $dataRelatorio, $dataRelatorio2 = '', $idPedido = '', $tipoRelatorio = '')
{
	$resposta 	= new xajaxResponse();

	$complTpRelatorio = !empty($tipoRelatorio) ? '&tipoRelatorio='.$tipoRelatorio : '';
	
	$resposta->addScript("window.open('./relatorios/".$nomeArquivoRelatorio.".php?data=".$dataRelatorio."&data_fim=".$dataRelatorio2."&id_solicitacao_documento=".$idPedido.$complTpRelatorio."', '_blank');");
	
	return $resposta;
}

function buscar_observacoes($idMedicao)
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();
	
	$sql = "SELECT * FROM ".DATABASE.".bms_observacoes ";
	$sql .= "WHERE bms_observacoes.reg_del = 0 ";
	$sql .= "AND bms_observacoes.id_bms_medicao = ".$idMedicao." ";
	$sql .= "ORDER BY bms_observacoes.id_bms_observacao DESC ";
	
	$xml = new XMLWriter();
	$xml->openMemory();
	$xml->startElement('rows');
	
	$db->select($sql, 'MYSQL', function($reg, $i) use(&$xml){
		$xml->startElement('row');
			$xml->writeAttribute('id', $reg['id_bms_observacao']);
			$xml->writeElement('cell', mysql_php($reg["data"]));
			$xml->writeElement('cell', $reg["observacao"]);
			$xml->writeElement('cell', '<span class="icone icone-excluir cursor" onclick=if(confirm("Deseja&nbsp;excluir&nbsp;esta&nbsp;observação?")){xajax_excluir_observacao("'.$reg["id_bms_observacao"].'","'.$reg["id_bms_medicao"].'");};></span>');
		$xml->endElement();		
	});
	
	$xml->endElement();
	
	$conteudo = $xml->outputMemory(true);
	$resposta->addScript("grid('div_observacoes', true, '140', '".$conteudo."');");
	
	return $resposta;
}

function salvar_observacoes($dados_form)
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();
	
	if (!empty($dados_form['txtObservacao']))
	{
		if (!empty($dados_form['id_bms_observacao']))
		{
			$usql = "UPDATE 
						".DATABASE.".bms_observacoes 
						SET id_bms_medicao = ".$dados_form['id_bms_medicao'].",
						data = '".date('Y-m-d')."', 
						observacao = '".AntiInjection::clean(maiusculas($dados_form['txtObservacao']))."'
					WHERE id_bms_observacao = ".$dados_form['id_bms_observacao']." 
					AND reg_del = 0 ";
			
			$db->update($usql, 'MYSQL');
		}
		else
		{
			$isql = "INSERT INTO ".DATABASE.".bms_observacoes (id_bms_medicao, data, observacao) VALUES ";
			$isql .= "(".$dados_form['id_bms_medicao'].", '".date('Y-m-d')."', '".trim(maiusculas($dados_form['txtObservacao']))."'); ";
			$db->insert($isql, 'MYSQL');
		}
		
		if ($db->erro != '')
			$resposta->addAlert('Não foi possível salvar a observação, por favor, tente mais novamente. '.$db->erro);
		else
		{
			$resposta->addAlert('Observação salva corretamente!');
			$resposta->addScript('divPopupInst.destroi();');
			$resposta->addScript('buscar_observacoes('.$dados_form['id_bms_medicao'].');');
		}
	}
	else
		$resposta->addAlert('Por favor, digite uma observação para salvar!');
	
	return $resposta;
}

function alterar_observacao($idObservacao)
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();
	
	$sql = "SELECT * FROM ".DATABASE.".bms_observacoes ";
	$sql .= "WHERE bms_observacoes.reg_del = 0 ";
	$sql .= "AND bms_observacoes.id_bms_observacao = ".$idObservacao;
	
	$db->select($sql, 'MYSQL', function($reg, $i) use(&$resposta){
		$resposta->addAssign("txtObservacao","innerHTML",$reg['observacao']);
		$resposta->addAssign("id_bms_medicao","value",$reg['id_bms_medicao']);
		$resposta->addAssign("id_bms_observacao","value",$reg['id_bms_observacao']);
	});
	
	return $resposta;
}

function excluir_observacao($idObservacao, $idMedicao)
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();
	
	$usql = "UPDATE ".DATABASE.".bms_observacoes SET ";
	$usql .= "bms_observacoes.reg_del = 1, ";
	$usql .= "bms_observacoes.reg_who = '".$_SESSION['id_funcionario']."', ";
	$usql .= "bms_observacoes.data_del = '".date('Y-m-d')."' ";
	$usql .= "WHERE bms_observacoes.id_bms_observacao = ".$idObservacao." ";
	$usql .= "AND bms_observacoes.reg_del = 0 ";
	
	$db->update($usql, 'MYSQL');
	
	if ($db->erro != '')
		$resposta->addAlert('Não foi possível salvar a observação, por favor, tente mais novamente. '.$db->erro);
	else
	{
		$resposta->addAlert('Observação excluida corretamente!');
		$resposta->addScript('divPopupInst.destroi();');
		$resposta->addScript("buscar_observacoes(".$idMedicao.")");
	}		
	
	return $resposta;
}

function preencheComboPedidos()
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();
	
	$sql = "SELECT
				id_bms_pedido, ordem_servico.id_os, descricao
			FROM
				".DATABASE.".bms_pedido
				JOIN (
				  SELECT
					id_os idOs, id_empresa_erp, os, descricao
				  FROM
					".DATABASE.".ordem_servico WHERE ordem_servico.reg_del = 0 
				) os
				ON ordem_servico.id_os = bms_pedido.id_os
			WHERE
				bms_pedido.reg_del = 0
			ORDER BY
				bms_pedido.id_bms_pedido";
	
	$resposta->addScript("combo_destino = document.getElementById('selPedidos');");
	$resposta->addScript("limpa_combo('selPedidos');");
	$resposta->addScript("combo_destino.options[combo_destino.length] = new Option('TODOS', '');");
	
	$db->select($sql, 'MYSQL', function($reg, $i) use(&$resposta){
		$resposta->addScript("combo_destino.options[combo_destino.length] = new Option('".sprintf('%05d', $reg["os"])." - ".$reg["descricao"]."', '".$reg["id_bms_pedido"]."');");
	});
	
	return $resposta;
}

function verificar_apontamentos($idMedicao, $dados_form = array())
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();
	
	$sql = 
	"SELECT 
		ap.*,
		SEC_TO_TIME(SUM(TIME_TO_SEC(hora_normal))) AS HN,
		SEC_TO_TIME(SUM(TIME_TO_SEC(hora_adicional))) AS HA, 
		SEC_TO_TIME(SUM(TIME_TO_SEC(hora_adicional_noturna))) AS HAN,
		e.data dataApontamento,
		WEEKDAY(e.data) diaSemana,
		LAST_DAY(e.data) ultimoDia
	FROM (
		SELECT
			a.id_bms_item, a.id_os, a.quantidade, b.quantidade_planejada, a.valor, b.data, b.valor_planejado, a.id_unidade,
		
		CASE COALESCE(mes,0)
			WHEN 0 THEN
				CONCAT(SUBSTRING(DATE_ADD(b.data, INTERVAL -30 DAY), 1,8),26)
			WHEN 1 THEN
                CASE WHEN dia_inicial >= dia_final THEN
				        CONCAT(SUBSTRING(DATE_ADD(b.data, INTERVAL -60 DAY), 1,8),dia_inicial)
                    WHEN dia_inicial < dia_final THEN
				        CONCAT(SUBSTRING(DATE_ADD(b.data, INTERVAL -30 DAY), 1,8),dia_inicial)
                END
			WHEN 2 THEN
				CASE WHEN dia_inicial >= dia_final THEN
					CONCAT(SUBSTRING(DATE_ADD(b.data, INTERVAL -30 DAY), 1,8),dia_inicial)
				WHEN dia_inicial < dia_final THEN
					CONCAT(SUBSTRING(b.data, 1,8),dia_inicial)
				END
		END AS dataAnterior,
		
		CASE COALESCE(mes,0)
			WHEN 0 THEN
				CONCAT(SUBSTRING(b.data, 1,8),25)
			WHEN 1 THEN
                CASE WHEN dia_inicial >= dia_final THEN
                        CONCAT(SUBSTRING(DATE_ADD(b.data, INTERVAL -60 DAY), 1,8),dia_final)
                    WHEN dia_inicial < dia_final THEN
				        CONCAT(SUBSTRING(DATE_ADD(b.data, INTERVAL -30 DAY), 1,8),dia_final)
                    END
			WHEN 2 THEN
				CONCAT(SUBSTRING(b.data, 1,8),dia_final)
		END AS dataFinal,
		
		d.id_os, b.id_bms_medicao, d.hora_extra, a.id_funcionario funcMedicao, d.id_empresa_erp, a.descricao descItem,
		e.dia_inicial, e.dia_final, COALESCE(mes, 0) mes,
		e.tarifa_ha, e.tarifa_han, e.tarifa_ha_domingo, e.tarifa_ha_sabado, e.tarifa_ha_feriado, e.feriados, 
		c.id_bms_medicao idMedicaoAnterior, ba.horas_adicionais_noturno
	FROM 
		".DATABASE.".bms_item a
		JOIN ".DATABASE.".bms_pedido p ON p.id_bms_pedido = a.id_bms_pedido AND p.reg_del = 0
		JOIN ".DATABASE.".ordem_servico d ON d.id_os = a.id_os AND d.reg_del = 0 
		JOIN ".DATABASE.".bms_medicao b ON b.id_bms_item = a.id_bms_item 
		LEFT JOIN ".DATABASE.".bms_periodos_medicao_cliente e ON e.id_cliente = d.id_empresa_erp AND e.reg_del = 0
		LEFT JOIN ".DATABASE.".bms_medicao c ON c.reg_del = 0 AND c.id_bms_item = b.id_bms_item AND c.data < b.data
		LEFT JOIN ".DATABASE.".bms_apontamentos ba ON ba.reg_del = 0 AND ba.id_bms_medicao = b.id_bms_medicao	
			
		WHERE b.reg_del = 0 AND a.reg_del = 0
		AND a.id_unidade = 6
		AND b.id_bms_medicao = ".$idMedicao."

		GROUP BY a.id_bms_item, a.os, a.quantidade, b.quantidade_planejada, a.valor, b.data, b.valor_planejado, a.id_unidade, a.id_funcionario, a.descricao,
				e.dia_inicial, e.dia_final, e.mes, e.tarifa_ha, e.tarifa_han
	) ap
	LEFT JOIN ".DATABASE.".apontamento_horas e ON e.id_os = ap.id_os AND e.data BETWEEN ap.dataAnterior AND ap.dataFinal AND e.id_funcionario = funcMedicao AND e.reg_del = 0

	GROUP BY id_bms_item, os, quantidade_planejada, valor, e.data, valor_planejado, id_unidade, dataAnterior, id_os, id_bms_medicao";
	
	$totalHN = 0;
	$totalHAN = 0;
	$totalHA = 0;
	$totalHASab = 0;
	$totalHADom = 0;
	$totalHAFeriados = 0;
	$valorCalcular = 0;
	$qtdHorasPlanej = 0;
	
	$percentualHASem = 0;
	$percentualHAN = 0;
	$percentualHASab = 0;
	$percentualHADom = 0;
	$percentualHAFer = 0; 
	$calcularAdicionalNoturno = true;
	$htmlCamposOcultos = '';
	
	$db->select($sql, 'MYSQL', function($reg, $i) use(&$htmlCamposOcultos, &$valorCalcular, &$qtdHorasPlanej, &$resposta, &$totalHN, &$totalHA, &$totalHAN, &$totalHASab, &$totalHADom, &$totalHAFeriados, &$percentualHASem, &$percentualHAN, &$percentualHASab, &$percentualHADom, &$percentualHAFer, &$calcularAdicionalNoturno){
		$reg['dia_inicial'] = empty($reg['dia_inicial']) ? 26 : $reg['dia_inicial'];
		$reg['dia_final'] = empty($reg['dia_final']) ? 25 : $reg['dia_final'];
		
		if ($i == 0)
		{
			if ($reg['dataFinal'] > $reg['ultimoDia'] && !empty($reg['ultimoDia']))
				$reg['dataFinal'] = $reg['ultimoDia'];
				
			$resposta->addAssign('diaInicioMedicao', 'value', sprintf('%02d', $reg['dia_inicial']));
			$resposta->addAssign('diaFimMedicao', 'value', sprintf('%02d', $reg['dia_final']));
			
			$resposta->addAssign('dataInicioMedicao', 'value', mysql_php($reg['dataAnterior']));
			$resposta->addAssign('dataFimMedicao', 'value', mysql_php($reg['dataFinal']));
			$resposta->addAssign('txtFeriados', 'value', $reg['feriados']);			
			$resposta->addAssign('txtHAN', 'value', number_format($totalHAN, 2, ',', '.'));
			
			$percentualHASem = isset($reg['tarifa_ha']) && !empty($reg['tarifa_ha']) ? number_format($reg['tarifa_ha'], 2, ',', '.') : 60.00;
			$percentualHAN = isset($reg['tarifa_han']) && !empty($reg['tarifa_han']) ? number_format($reg['tarifa_han'], 2, ',', '.') : 20.00;
			$percentualHASab = isset($reg['tarifa_ha_sabado']) && !empty($reg['tarifa_ha_sabado']) ? number_format($reg['tarifa_ha_sabado'], 2, ',', '.') : 60.00;
			$percentualHADom = isset($reg['tarifa_ha_domingo']) && !empty($reg['tarifa_ha_domingo']) ? number_format($reg['tarifa_ha_domingo'], 2, ',', '.') : 100.00;
			$percentualHAFer = isset($reg['tarifa_ha_feriado']) && !empty($reg['tarifa_ha_feriado']) ? number_format($reg['tarifa_ha_feriado'], 2, ',', '.') : 100.00; 
			
			$resposta->addAssign('txtHASemPercentual', 'value', $percentualHASem);
			$resposta->addAssign('txtHANPercentual', 'value', $percentualHAN);
			$resposta->addAssign('txtHASabPercentual', 'value', $percentualHASab);
			$resposta->addAssign('txtHADPercentual', 'value', $percentualHADom);
			$resposta->addAssign('txtHAFeriadoPercentual', 'value', $percentualHAFer);
			
			$resposta->addAssign('selMes', 'value', $reg['mes']);			
		
			//Ajuste final do adicional noturno, Caso o adicional noturno denha sido digitado pelo financeiro na hora da medição, trazer o valor digitado
			//Como o valor vem igual em todas as tuplas, trazer somente o primeiro
			if (!empty($reg['horas_adicionais_noturno']))
			{
				$totalHAN = $reg['horas_adicionais_noturno'];
				$totalHN -= $totalHAN;
				$calcularAdicionalNoturno = false;
			}
			else
			{
				$totalHAN = time_to_float($reg['HAN']);
			}
			
			//Criando campos ocultos para realizar a medição propriamente dita
			$htmlCamposOcultos .= '<input type="hidden" name="valor_item" id="valor_item" value="'.$reg['valor'].'" />';
			$htmlCamposOcultos .= '<input type="hidden" name="id_item" id="id_item" value="'.$reg['id_bms_item'].'" />';
			$htmlCamposOcultos .= '<input type="hidden" name="id_bms_medicao" id="id_bms_medicao" value="'.$reg['id_bms_medicao'].'" />';
			$htmlCamposOcultos .= '<input type="hidden" name="data_item" id="data_item" value="'.mysql_php($reg['data']).'" />';
			$htmlCamposOcultos .= '<input type="hidden" name="id_status" id="id_status" value="2" />';
			$htmlCamposOcultos .= '<input type="hidden" name="valor_planejado" id="valor_planejado" value="'.number_format($reg['valor_planejado'], 2, ',', '.').'" />';
			$htmlCamposOcultos .= '<input type="hidden" name="quantidade_planejada" id="quantidade_planejada" value="'.number_format($reg['quantidade_planejada'], 2, ',', '.').'" />';
			$htmlCamposOcultos .= '<input type="hidden" name="percent_planejado" id="percent_planejado" value="'.number_format($reg['quantidade_planejada']/$reg['quantidade'], 2, ',', '.').'" />';
			
			$valorCalcular = $reg['valor_planejado'] / $reg['quantidade_planejada'];
			$qtdHorasPlanej = $reg['quantidade_planejada'];
		}
		
		//Verificando feriados
		$feriadosTemp = explode(',', $reg['feriados']);
		$feriados = array();
		foreach($feriadosTemp as $fer)
		{
			if (!empty($fer))
				$feriados[] = php_mysql($fer);				
		}
		
		if (!in_array($reg['dataApontamento'], $feriados))
		{
			//Totalizando horas durante a semana
			if (in_array($reg['diaSemana'], array(0,1,2,3,4)))
			{
			    $totalHA += str_replace(',', '.', str_replace('.', '', time_to_float($reg['HA'])));
			    $totalHN += str_replace(',', '.', str_replace('.', '', time_to_float($reg['HN'])));
				
				if ($calcularAdicionalNoturno)
				    $totalHAN += str_replace(',', '.', str_replace('.', '', time_to_float($reg['HAN'])));
			}
			
			//Totalizando horas durante o sabado
			if ($reg['diaSemana'] == 5)
			{
			    $totalHASab += str_replace(',', '.', str_replace('.', '', time_to_float($reg['HA'])));
			}
			
			//Totalizando horas durante o domingo
			if ($reg['diaSemana'] == 6)
			{
			    $totalHADom += str_replace(',', '.', str_replace('.', '', time_to_float($reg['HA'])));
			}
		}
		else
		{
		    $totalHAFeriados += str_replace(',', '.', str_replace('.', '', time_to_float($reg['HA'])));
		}
	});
	
	
	$resposta->addAssign('txtHN', 'value', number_format($totalHN, 2, ',', '.'));
	$resposta->addAssign('txtHAN', 'value', number_format($totalHAN, 2, ',', '.'));
	$resposta->addAssign('txtHASem', 'value', number_format($totalHA, 2, ',', '.'));
	$resposta->addAssign('txtHASab', 'value', number_format($totalHASab, 2, ',', '.'));
	$resposta->addAssign('txtHAD', 'value', number_format($totalHADom, 2, ',', '.'));
	$resposta->addAssign('txtHAFeriado', 'value', number_format($totalHAFeriados, 2, ',', '.'));
	
	$totalHoras = $totalHN + ($totalHA*(1+$percentualHASem/100));//Horas normais mais horas adicionais semana
	$totalHoras += ($totalHAN*(1+$percentualHAN/100));//Adicional noturno
	$totalHoras += ($totalHASab*(1+$percentualHASab/100));//Adicional Sabado
	$totalHoras += ($totalHADom*(1+$percentualHADom/100));//Adicional Domingo
	$totalHoras += ($totalHAFeriados*(1+$percentualHAFer/100));//Adicional Feriado
	
	$resposta->addAssign('txtTotalMedicao', 'value', number_format($totalHoras, 2, ',', '.'));
	
	$htmlCamposOcultos .= '<input type="hidden" name="quantidade_medida" id="quantidade_medida" value="'.number_format($totalHoras,2, ',', '.').'" />';
	$htmlCamposOcultos .= '<input type="hidden" name="valor_medido" id="valor_medido" value="'.number_format($valorCalcular * $totalHoras,2, ',', '.').'" />';
	$htmlCamposOcultos .= '<input type="hidden" name="percent_medido" id="percent_medido" value="'.number_format($totalHoras / $qtdHorasPlanej,2, ',', '.').'" />';
	
	$resposta->addAssign('camposOcultos', 'innerHTML', $htmlCamposOcultos);
	
	return $resposta;
}

function salvarInfoMedicao($dados_form)
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();
	$erro = '';
	
	if (!empty($dados_form['txtIdCliente']) && !empty($dados_form['diaInicioMedicao']) && !empty($dados_form['diaFimMedicao']))
	{
		$sql = "SELECT * FROM ".DATABASE.".bms_periodos_medicao_cliente ";
		$sql .= "WHERE bms_periodos_medicao_cliente.reg_del = 0 ";
		$sql .= "AND bms_periodos_medicao_cliente.id_cliente = ".$dados_form['txtIdCliente'];
		
		$db->select($sql, 'MYSQL', true);

		$dados_form['txtHAPercentual'] = '';
		
		$dados_form['txtHAPercentual'] = str_replace(',', '.', $dados_form['txtHAPercentual']);
		$dados_form['txtHANPercentual'] = str_replace(',', '.', $dados_form['txtHANPercentual']);
		
		//Verificando feriados
		$feriadosTemp = explode(',', $dados_form['txtFeriados']);
		$feriados = array();
		foreach($feriadosTemp as $fer)
		{
			if (!empty($fer))
				$feriados[] = php_mysql($fer);
		}
		
		if ($db->numero_registros > 0)
		{
			$usql = "UPDATE ".DATABASE.".bms_periodos_medicao_cliente SET ";
			$usql .= "dia_inicial = '".php_mysql($dados_form['diaInicioMedicao'])."', ";
			$usql .= "dia_final = '".php_mysql($dados_form['diaFimMedicao'])."', ";
			$usql .= "tarifa_ha = '".$dados_form['txtHASemPercentual']."', ";
			$usql .= "tarifa_han = '".$dados_form['txtHANPercentual']."', ";
			$usql .= "mes = '".$dados_form['selMes']."', ";
			$usql .= "tarifa_ha_domingo = '".$dados_form['txtHADPercentual']."', ";
			$usql .= "tarifa_ha_sabado = '".$dados_form['txtHASabPercentual']."', ";
			$usql .= "tarifa_ha_feriado = '".$dados_form['txtHAFeriadoPercentual']."', ";
			$usql .= "feriados = '".$dados_form['txtFeriados']."' ";
			$usql .= "WHERE id_pmc = ".$db->array_select[0]['id_pmc']." ";
			$usql .= "AND reg_del = 0 ";
			
			$db->update($usql, 'MYSQL');

			if ($db->erro != '')
			{
				$erro = $db->erro;
			}
		}
		else
		{
			$isql = "INSERT INTO ".DATABASE.".bms_periodos_medicao_cliente (id_cliente, dia_inicial, dia_final, tarifa_ha, tarifa_han, mes, tarifa_ha_domingo, tarifa_ha_sabado, tarifa_ha_feriado, feriados) VALUES ";
			$isql .= "('".$dados_form['txtIdCliente']."', '".php_mysql($dados_form['diaInicioMedicao'])."', '".php_mysql($dados_form['diaFimMedicao'])."', '".$dados_form['txtHAPercentual']."', '".$dados_form['txtHANPercentual']."', '".$dados_form['selMes']."', ";
			$isql .= "'".$dados_form['txtHADPercentual']."', '".$dados_form['txtHASabPercentual']."', '".$dados_form['txtHAFeriadoPercentual']."', '".$dados_form['txtFeriados']."')";
			$db->insert($isql, 'MYSQL');

			if ($db->erro != '')
			{
				$erro = $db->erro;
			}
		}

		$horasNormais 	 = str_replace(',', '.', str_replace('.', '', $dados_form['txtHN']));
		$horasAdicSemana = str_replace(',', '.', str_replace('.', '', $dados_form['txtHASem']));
		$horasAdicNoturno= str_replace(',', '.', str_replace('.', '', $dados_form['txtHAN']));
		$horasAdicSabado = str_replace(',', '.', str_replace('.', '', $dados_form['txtHASab']));
		$horasAdicDomingo = str_replace(',', '.', str_replace('.', '', $dados_form['txtHAD']));
		$horasAdicFeriado = str_replace(',', '.', str_replace('.', '', $dados_form['txtHAFeriado']));
				
		$feriados = implode(',',$feriados);
		
		$usql = "UPDATE ".DATABASE.".bms_apontamentos SET ";
		$usql .= "bms_apontamentos.reg_del = 1, ";
		$usql .= "bms_apontamentos.reg_who = '".$_SESSION['id_funcionario']."', ";
		$usql .= "bms_apontamentos.data_del = '".date('Y-m-d')."' ";
		$usql .= "WHERE bms_apontamentos.id_bms_medicao = ".$dados_form['txtIdMedicao']." ";
		$usql .= "AND bms_apontamentos.reg_del = 0 ";
		
		$db->update($usql, 'MYSQL');
		
		$isql = "INSERT INTO ".DATABASE.".bms_apontamentos (id_bms_medicao, horas_normais, horas_adicionais_semana, horas_adicionais_noturno, horas_adicionais_sabado, horas_adicionais_domingo, horas_adicionais_feriado, feriados) VALUES";
		$isql .= "('".$dados_form['txtIdMedicao']."', '".$horasNormais."', '".$horasAdicSemana."', '".$horasAdicNoturno."', '".$horasAdicSabado."', '".$horasAdicDomingo."', '".$horasAdicFeriado."', '".$feriados."' ";
		$isql .= ")";
		
		$db->insert($isql, 'MYSQL');
		
		if ($db->erro != '')
		{
			$resposta->addAlert('Houve uma falha ao tentar calcular as horas');
		}
	}
	else
	{
		$resposta->addAlert('Por favor, preencha os dias do período de medição!');
	}
	
	
	if (!empty($erro))
	{
		$resposta->addAlert($erro);
	}
	else
	{
		$resposta->addScript("xajax_verificar_apontamentos('".$dados_form['txtIdMedicao']."');");
	}
		
	return $resposta;
}

function modal_periodo_cliente()
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();
	
	$html = '<form id="frmPeriodoCliente" name="frmPeriodoCliente"><table width="100%">'.
			'<tr><td colspan="3"><label class="labels">Cliente</label><br />'.
				'<select id="selCliente" name="selCliente" class="caixa" style="width:100%;" onchange="xajax_editar_periodo_cliente(this.value);">'.
																				'<option value="">Selecione</option>';
	
	$sql = "SELECT id_empresa_erp, empresa, descricao FROM ".DATABASE.".empresas ";
	$sql .= "LEFT JOIN ".DATABASE.".unidade ON (empresas.id_unidade = unidades.id_unidade AND unidades.reg_del = 0) ";
	$sql .= "WHERE empresas.reg_del = 0 ";
	$sql .= " ORDER BY empresa ";
	
	$db->select($sql, 'MYSQL', function($reg, $i) use(&$html){
		$html .= "<option value='".$reg['id_empresa_erp']."'>".$reg['empresa']." - ".$reg['descricao']."</option>";
	});
	
	$html .= '</select></td></tr>'.
			 '<tr><td width="25%"><label class="labels">Período de Medição</label><br />'.
				'<span style="float:left;">'.
					'<input type="text" class="caixa" name="diaInicioMedicao" id="diaInicioMedicao" size="5" /><label class="labels">&nbsp;a&nbsp;</label>'.
					'<input type="text" class="caixa" name="diaFimMedicao" id="diaFimMedicao" size="5" /></span></td>'.
				'<td width="25%"><label class="labels">tipo</labels><br />'.
				'<select name="selMes" id="selMes" class="caixa">'.
							'<option value="0">Fechamento</option>'.
							'<option value="1">Mês Anterior Medição</option>'.
							'<option value="2">Mês Medição</option>'.
						 '</select></td>'.
				'<td><label class="labels">Dia BMS</label><br />'.
				'<input type="text" class="caixa" name="diaMedicao" id="diaMedicao" size="5" /></span></td>'.	
				'<tr><td><label class="labels">% Adic. Semana</label></td><td><input type="text" name="txtHASemPercentual" id="txtHASemPercentual" size="10" /></td></tr>'.
				'<tr><td><label class="labels">% Adic. Noturno</label></td><td><input type="text" name="txtHANPercentual" id="txtHANPercentual" size="10" /></td></tr>'.
				'<tr><td><label class="labels">% Adic. Sábado</label></td><td><input type="text" name="txtHASabPercentual" id="txtHASabPercentual" size="10" /></td></tr>'.
				'<tr><td><label class="labels">% Adic. Domingo</label></td><td><input type="text" name="txtHADPercentual" id="txtHADPercentual" size="10" /></td></tr>'.
				'<tr><td><label class="labels">% Adic. Feriado</label></td><td><input type="text" name="txtHAFeriadoPercentual" id="txtHAFeriadoPercentual" size="10" /></td></tr>'.
				'<tr><td colspan="3"><input type="button" value="Salvar" class="class_botao" onclick="xajax_salvar_periodo_cliente(xajax.getFormValues(\'frmPeriodoCliente\'));" /></tr></form>';
	
	$resposta->addScriptCall('modal', $html, '300_650', 'Cadastro de período por cliente', '1');
	
	return $resposta;
}

function editar_periodo_cliente($idCliente)
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();
	
	if (!empty($idCliente))
	{
		$resposta->addScript('document.getElementById("frmPeriodoCliente").reset();');
		$resposta->addAssign('selCliente', 'value', $idCliente);
			
		$sql = "SELECT * FROM ".DATABASE.".bms_periodos_medicao_cliente ";
		$sql .= "WHERE bms_periodos_medicao_cliente.reg_del = 0 ";
		$sql .= "AND bms_periodos_medicao_cliente.id_cliente = ".$idCliente;
		
		$db->select($sql, 'MYSQL', function($reg, $i) use(&$resposta){
			$resposta->addAssign('diaInicioMedicao', 'value', sprintf('%02d', $reg['dia_inicial']));
			$resposta->addAssign('diaFimMedicao', 'value', sprintf('%02d', $reg['dia_final']));
			$resposta->addAssign('diaMedicao', 'value', $reg['dia_bms']);
			$resposta->addAssign('selMes', 'value', $reg['mes']);
			$resposta->addAssign('txtHASemPercentual', 'value', $reg['tarifa_ha']);
			$resposta->addAssign('txtHANPercentual', 'value', $reg['tarifa_han']);
			$resposta->addAssign('txtHADPercentual', 'value', $reg['tarifa_ha_domingo']);
			$resposta->addAssign('txtHASabPercentual', 'value', $reg['tarifa_ha_sabado']);
			$resposta->addAssign('txtHAFeriadoPercentual', 'value', $reg['tarifa_ha_feriado']);
		});
	}
	
	return $resposta;
} 

function salvar_periodo_cliente($dados_form)
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();
	$erro = '';
	
	if (!empty($dados_form['selCliente']) && !empty($dados_form['diaInicioMedicao']) && !empty($dados_form['diaFimMedicao']))
	{
		$sql = "SELECT * FROM ".DATABASE.".bms_periodos_medicao_cliente ";
		$sql .= "WHERE bms_periodos_medicao_cliente.reg_del = 0 ";
		$sql .= "AND bms_periodos_medicao_cliente.id_cliente = ".$dados_form['selCliente'];
		
		$db->select($sql, 'MYSQL', true);

		$dados_form['txtHAPercentual'] = '';
		
		$dados_form['txtHAPercentual'] = str_replace(',', '.', $dados_form['txtHAPercentual']);
		$dados_form['txtHANPercentual'] = str_replace(',', '.', $dados_form['txtHANPercentual']);
		
		if ($dados_form['selMes'] == 0)
		{
			$dados_form['diaInicioMedicao'] = 26;
			$dados_form['diaFimMedicao'] = 25;
			$dados_form['diaMedicao'] = 26;
		}
		
		if ($db->numero_registros > 0)
		{
			$usql = "UPDATE ".DATABASE.".bms_periodos_medicao_cliente SET ";
			$usql .= "dia_inicial = '".php_mysql($dados_form['diaInicioMedicao'])."', ";
			$usql .= "dia_final = '".php_mysql($dados_form['diaFimMedicao'])."', ";
			$usql .= "tarifa_ha = '".$dados_form['txtHASemPercentual']."', ";
			$usql .= "tarifa_han = '".$dados_form['txtHANPercentual']."', ";
			$usql .= "mes = '".$dados_form['selMes']."', ";
			$usql .= "tarifa_ha_domingo = '".$dados_form['txtHADPercentual']."', ";
			$usql .= "tarifa_ha_sabado = '".$dados_form['txtHASabPercentual']."', ";
			$usql .= "tarifa_ha_feriado = '".$dados_form['txtHAFeriadoPercentual']."', ";
			$usql .= "dia_bms = '".$dados_form['diaMedicao']."' ";
			$usql .= "WHERE id_pmc = ".$db->array_select[0]['id_pmc']." ";
			$usql .= "AND reg_del = 0 ";
			
			$db->update($usql, 'MYSQL');

			if ($db->erro != '')
			{
				$erro = $db->erro;
			}
		}
		else
		{
			$isql = "INSERT INTO ".DATABASE.".bms_periodos_medicao_cliente (id_cliente, dia_inicial, dia_final, tarifa_ha, tarifa_han, mes, tarifa_ha_domingo, tarifa_ha_sabado, tarifa_ha_feriado, dia_bms) VALUES ";
			$isql .= "('".$dados_form['selCliente']."', '".php_mysql($dados_form['diaInicioMedicao'])."', '".php_mysql($dados_form['diaFimMedicao'])."', '".$dados_form['txtHAPercentual']."', '".$dados_form['txtHANPercentual']."', '".$dados_form['selMes']."', ";
			$isql .= "'".$dados_form['txtHADPercentual']."', '".$dados_form['txtHASabPercentual']."', '".$dados_form['txtHAFeriadoPercentual']."', '".$dados_form['diaMedicao']."')";
			$db->insert($isql, 'MYSQL');

			if ($db->erro != '')
			{
				$erro = $db->erro;
			}
		}
	}
	else
	{
		$resposta->addAlert('Por favor, preencha os dias do período de medição!');
	}
	
	
	if (!empty($erro))
	{
		$resposta->addAlert($erro);
	}
	else
	{
		$resposta->addAlert('Registro atualizado corretamente!');
		$resposta->addScript("xajax_editar_periodo_cliente(".$dados_form['selCliente'].");");
	}
		
	return $resposta;
}

function medir_exato($idBmsMedicao, $idBmsItem, $idBmsPedido)
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();
	
	$usql = 
	"UPDATE ".DATABASE.".bms_medicao SET 
		progresso_medido = progresso_planejado, 
		valor_medido = valor_planejado, 
		percentual_diferenca = 0, 
		valor_diferenca = 0,
		quantidade_medida = quantidade_planejada, 
		quantidade_diferenca = 0, 
		id_bms_controle = 2, 
		id_bms_pedido = '".$idBmsPedido."'
	WHERE id_bms_medicao = '".$idBmsMedicao."' 
	AND reg_del = 0 ";
	
	$db->update($usql, 'MYSQL');
	
	if ($db->erro != '')
	{
		$resposta->addAlert('Houve uma falha ao tentar medir o item');
	}
	else
	{
	    //Deixando fixo no sistema para não reimportar
	    $usql = "UPDATE ".DATABASE.".bms_pedido SET ";
	    $usql .= "alterado_manualmente = 1 ";
	    $usql .= "WHERE id_bms_pedido = ".$idBmsPedido." ";
	    $usql .= "AND reg_del = 0 ";
	    
	    $db->update($usql, 'MYSQL');
	    
		$resposta->addAlert('Medição realizada corretamente!');
		$resposta->addScript("xajax_atualizatabela_medicoes(".$idBmsItem.");");
	}
	
	return $resposta;
}

function verifica_saldo_item($idBmsItem, $valorJaPlanejado = 0)
{
    $resposta = new xajaxResponse();
    $db = new banco_dados();
    
    $sql = "SELECT
			a.id_bms_item, descricao, quantidade, SUM(quantidade_planejada) qtdMedida
		FROM
			".DATABASE.".bms_item a
			LEFT JOIN ".DATABASE.".bms_medicao b on b.id_bms_item = a.id_bms_item AND b.reg_del = 0
		WHERE 
			a.id_bms_item = ".$idBmsItem." 
			AND a.reg_del = 0 ";
    
    $db->select($sql, 'MYSQL', true);
    
    $total = number_format($db->array_select[0]['quantidade']-$db->array_select[0]['qtdMedida']-$valorJaPlanejado, 2, ',', '.');
    
    $resposta->addAssign('saldoMedicaoReplica', 'value', $total);
    $resposta->addAssign('saldoMedicaoReplicaHidden', 'value', $total);
    return $resposta;
}

function modal_anexar_pedido($os)
{
    $resposta = new xajaxResponse();
    $db = new banco_dados();
    $conf = new configs();
    
    $sql = "SELECT arquivo_pedido, arquivo_proposta, arquivo_contrato, os FROM ".DATABASE.".bms_pedido ";
    $sql .= "WHERE bms_pedido.reg_del = 0 ";
    $sql .= "AND bms_pedido.id_os = ".$os;
    
    $db->select($sql, 'MYSQL', true);
    
    //Por enquanto está liberado
    $liberaExclusao = $conf->checa_permissao(2) ? 'display:block;' : 'display:block;';
    $temContrato = false;
    
    if ($db->numero_registros > 0)
    {
        $imgExcluir = '<button class="class_botao" %s style="margin-left:10px;'.$liberaExclusao.'">&nbsp;<span class="icone icone-excluir icone-botao"></span>EXCLUIR</button>';
        
        if (!empty($db->array_select[0]['arquivo_pedido']))
        {
            $acao = 'onclick="if(confirm(\'Deseja realmente excluir este arquivo?\')){xajax_excluir_arquivo('.$os.',\'arquivo_pedido\');}"';
            $htmlPedido = "<fieldset style='margin:0;'><legend class='labels'>Pedido</legend>".
                "<button class='class_botao' style='width:250px;float:left;' onclick=window.open('../includes/documento.php?documento=".DOCUMENTOS_FINANCEIRO.'/pedidos/'.$db->array_select[0]["arquivo_pedido"]."');>VISUALIZAR PEDIDO</button>".
                sprintf($imgExcluir, $acao).
                "</fieldset>";
        }
        else
        {
            $htmlPedido = '<fieldset style="margin:0;padding-bottom:0;"><legend class="labels">Pedido</legend><form id="frm_pedido" enctype="multipart/form-data" action="../contratos_controle/upload_bms_pedido.php" target="upload_target" method="post">'.
                '<label class="labels" style="float:left;width:80px;">Nº pedido</label>'.
                '<input name="nome_arquivo" class="caixa" type="text" id="nome_arquivo">'.
                '<input class="caixa" name="myfile" type="file" size="30" />'.
                '<input name="tipo_arquivo" type="hidden" id="tipo_arquivo" value="pedido" />'.
                '<button class="class_botao" onclick=document.getElementById("frm_pedido").submit();><span class="icone icone-clips icone-botao"></span>&nbsp;ANEXAR</button>'.
                '<input name="os" type="hidden" id="os" value="'.$os.'">'.
                '</form></fieldset>';
        }
        
        if (!empty($db->array_select[0]['arquivo_proposta']))
        {
            $acao = 'onclick="if(confirm(\'Deseja realmente excluir este arquivo?\')){xajax_excluir_arquivo('.$os.',\'arquivo_proposta\');}"';
            $htmlProposta = "<fieldset style='margin:0;'><legend class='labels'>Proposta</legend>".
                "<button class='class_botao' style='width:250px;float:left;' onclick=window.open('../includes/documento.php?documento=".DOCUMENTOS_FINANCEIRO.'/pedidos/'.$db->array_select[0]["arquivo_proposta"]."');>VISUALIZAR PROPOSTA</button>".
                sprintf($imgExcluir, $acao).
                "</fieldset>";
        }
        else
        {
            $htmlProposta = '<fieldset style="margin:0;padding-bottom:0;"><legend class="labels">Proposta</legend><form id="frm_proposta" enctype="multipart/form-data" action="../contratos_controle/upload_bms_pedido.php" target="upload_target" method="post">'.
                '<label class="labels" style="float:left;width:80px;">Nº proposta</label>'.
                '<input name="nome_arquivo" class="caixa" type="text" id="nome_arquivo">'.
                '<input name="tipo_arquivo" type="hidden" id="tipo_arquivo" value="proposta" />'.
                '<input class="caixa" name="myfile" type="file" size="30" />'.
                '<button class="class_botao" onclick=document.getElementById("frm_proposta").submit();><span class="icone icone-clips icone-botao"></span>&nbsp;ANEXAR</button>'.
                '<input name="os" type="hidden" id="os" value="'.$os.'">'.
                '</form></fieldset>';
        }
        
        if (!empty($db->array_select[0]['arquivo_contrato']))
        {
            $acao = 'onclick="if(confirm(\'Deseja realmente excluir este arquivo?\')){xajax_excluir_arquivo('.$os.',\'arquivo_contrato\');}"';
            $htmlContrato = "<fieldset style='margin:0;'><legend class='labels'>Contrato</legend>".
                "<button class='class_botao' style='width:250px;float:left;' onclick=window.open('../includes/documento.php?documento=".DOCUMENTOS_FINANCEIRO.'/pedidos/'.$db->array_select[0]["arquivo_contrato"]."');>VISUALIZAR CONTRATO</button>".
                sprintf($imgExcluir, $acao).
                "</fieldset>";
                
                $temContrato = true;
        }
        else
        {
            $htmlContrato = '<fieldset style="margin:0;padding-bottom:0;"><legend class="labels">Contrato</legend><form id="frm_contrato" enctype="multipart/form-data" action="../contratos_controle/upload_bms_pedido.php" target="upload_target" method="post">'.
                '<label class="labels" style="float:left;width:80px;">Nº contrato</label>'.
                '<input name="nome_arquivo" class="caixa" type="text" id="nome_arquivo">'.
                '<input name="tipo_arquivo" type="hidden" id="tipo_arquivo" value="contrato" />'.
                '<input class="caixa" name="myfile" type="file" size="30" />'.
                '<button class="class_botao" onclick=document.getElementById("frm_contrato").submit();><span class="icone icone-clips icone-botao"></span>&nbsp;ANEXAR</button>'.
                '<input name="os" type="hidden" id="os" value="'.$os.'">'.
                '</form></fieldset>';
        }
    }
    else
    {
        $htmlPedido = '<fieldset style="margin:0;padding-bottom:0;"><legend class="labels">Pedido</legend><form id="frm_pedido" enctype="multipart/form-data" action="../contratos_controle/upload_bms_pedido.php" target="upload_target" method="post">'.
            '<label class="labels" style="float:left;width:80px;">Nº pedido</label>'.
            '<input name="nome_arquivo" class="caixa" type="text" id="nome_arquivo">'.
            '<input class="caixa" name="myfile" type="file" size="30" />'.
            '<input name="tipo_arquivo" type="hidden" id="tipo_arquivo" value="pedido" />'.
            '<button class="class_botao" onclick=document.getElementById("frm_pedido").submit();><span class="icone icone-clips icone-botao"></span>&nbsp;ANEXAR</button>'.
            '<input name="os" type="hidden" id="os" value="'.$os.'">'.
            '</form></fieldset>';
        
        $htmlContrato = '<fieldset style="margin:0;padding-bottom:0;"><legend class="labels">Contrato</legend><form id="frm_contrato" enctype="multipart/form-data" action="../contratos_controle/upload_bms_pedido.php" target="upload_target" method="post">'.
            '<label class="labels" style="float:left;width:80px;">Nº contrato</label>'.
            '<input name="nome_arquivo" class="caixa" type="text" id="nome_arquivo">'.
            '<input name="tipo_arquivo" type="hidden" id="tipo_arquivo" value="contrato" />'.
            '<input class="caixa" name="myfile" type="file" size="30" />'.
            '<button class="class_botao" onclick=document.getElementById("frm_contrato").submit();><span class="icone icone-clips icone-botao"></span>&nbsp;ANEXAR</button>'.
            '<input name="os" type="hidden" id="os" value="'.$os.'">'.
            '</form></fieldset>';
        
        $htmlProposta = '<fieldset style="margin:0;padding-bottom:0;"><legend class="labels">Proposta</legend><form id="frm_proposta" enctype="multipart/form-data" action="../contratos_controle/upload_bms_pedido.php" target="upload_target" method="post">'.
            '<label class="labels" style="float:left;width:80px;">Nº proposta</label>'.
            '<input name="nome_arquivo" class="caixa" type="text" id="nome_arquivo">'.
            '<input name="tipo_arquivo" type="hidden" id="tipo_arquivo" value="proposta" />'.
            '<input class="caixa" name="myfile" type="file" size="30" />'.
            '<button class="class_botao" onclick=document.getElementById("frm_proposta").submit();><span class="icone icone-clips icone-botao"></span>&nbsp;ANEXAR</button>'.
            '<input name="os" type="hidden" id="os" value="'.$os.'">'.
            '</form></fieldset>';
    }
    
    //SE NAO TEM CONTRATO, VERIFICAR SE TEM RAIZ E VERIFICAR SE A OS RAIZ TEM CONTRATO
    if (!$temContrato)
    {
		//VERIFICANDO SE A TEM OS RAIZ E SE ESTA RAIZ TEM DADOS DE CONTRATO PARA BUSCAR
		/*
        $sql = "SELECT AF1_RAIZ, AF1_ORCAME FROM AF1010 WHERE D_E_L_E_T_ = '' AND AF1_ORCAME = '".sprintf('%010d', $db->array_select[0]['os'])."'";
        $db->select($sql, 'MSSQL', true);
		$osRaiz = trim($db->array_select[0]['AF1_RAIZ']);
		*/
        
        if (!empty($osRaiz))
        {
            $sql = "SELECT arquivo_pedido, arquivo_proposta, arquivo_contrato, id_os FROM ".DATABASE.".bms_pedido ";
            $sql .= "WHERE bms_pedido.reg_del = 0 AND arquivo_contrato IS NOT NULL ";
            $sql .= "AND bms_pedido.id_os = ".$osRaiz;
            
            $db->select($sql, 'MYSQL', true);
            
            if ($db->numero_registros > 0)
            {
                $htmlContrato = "<fieldset style='margin:0;'><legend class='labels'>Contrato</legend>".
                    "<button class='class_botao' style='width:250px;float:left;' onclick=window.open('../includes/documento.php?documento=".DOCUMENTOS_FINANCEIRO.'/pedidos/'.$db->array_select[0]["arquivo_contrato"]."');>VISUALIZAR CONTRATO RAIZ</button>".
                    "</fieldset>";
            }
        }
    }
    
    $html = '<iframe id="upload_target" name="upload_target" src="#" style="width:100%;height:100px;border:1px solid #000;display:none;"></iframe>';
    
    $html .= $htmlPedido.$htmlProposta.$htmlContrato;
    
    $resposta->addScriptCall("modal",$html, '250_800', 'Anexar arquivos ao pedido');
    
    return $resposta;
}

function excluir_arquivo($id_bms_pedido, $campo)
{
    $resposta = new xajaxResponse();
    $db = new banco_dados();
    
    $sql = "SELECT ".$campo." as arquivo FROM ".DATABASE.".bms_pedido ";
	$sql .= "WHERE bms_pedido.reg_del = 0 ";
	$sql .= "AND bms_pedido.id_os = ".$id_bms_pedido;
    
	$db->select($sql, 'MYSQL', true);
    
	if (HOST != 'localhost')
        $pasta = DOCUMENTOS_FINANCEIRO.'/pedidos/';
	else
        $pasta = ROOT_DIR.'/contratos_controle/pedidos/';
    
    if (is_file($pasta.$db->array_select[0]['arquivo']))
    {
        if (unlink($pasta.$db->array_select[0]['arquivo']))
        {
            $usql = "UPDATE ".DATABASE.".bms_pedido SET ".$campo." = '' ";
			$usql .= "WHERE bms_pedido.reg_del = 0 ";
			$usql .= "AND bms_pedido.id_os = ".$id_bms_pedido;
           
		    $db->update($usql, 'MYSQL');
            
            if ($db->erro != '')
            {
                $resposta->addAlert('Arquivo Excluido parcialmente! Houve uma falha no registro do banco de dados!');
            }
            else
            {
                $resposta->addAlert('Arquivo Excluído corretamente!');
                $resposta->addScript('divPopupInst.destroi();');
                $resposta->addScriptCall('xajax_modal_anexar_pedido', $id_bms_pedido);
                $resposta->addScript('xajax_atualizatabela();');
            }
        }
        else
        {
            $resposta->addAlert('Houve uma falha ao tentar excluir o arquivo!');
        }
    }
    else
    {
        $usql = "UPDATE ".DATABASE.".bms_pedido SET ".$campo." = '' ";
		$usql .= "WHERE bms_pedido.reg_del = 0 ";
		$usql .= "AND bms_pedido.id_os = ".$id_bms_pedido;
		
        $db->update($usql, 'MYSQL');
        
        if ($db->erro != '')
        {
            $resposta->addAlert('Houve uma falha no registro do banco de dados!');
        }
        else
        {
            $resposta->addAlert('Arquivo Excluído corretamente!');
            $resposta->addScript('divPopupInst.destroi();');
            $resposta->addScriptCall('xajax_modal_anexar_pedido', $id_bms_pedido);
            $resposta->addScript('xajax_atualizatabela();');
        }
    }
    
    return $resposta;
}

function excluir_arquivo_liberacao($nome_arquivo,$idItem)
{
    $resposta = new xajaxResponse();
    $db = new banco_dados();
    
    if (HOST != 'localhost')
        $pasta = DOCUMENTOS_FINANCEIRO.'/pedidos/';
    else
        $pasta = ROOT_DIR.'/contratos_controle/pedidos/';
            
    if (is_file($pasta.$nome_arquivo))
    {
        if (!unlink($pasta.$nome_arquivo))
        {
            $resposta->addAlert('Houve uma falha ao tentar excluir o arquivo!');
        }
    }
    
    $usql = "UPDATE ".DATABASE.".bms_medicao SET arquivo_liberacao = '' ";
    $usql .= "WHERE reg_del = 0 ";
    $usql .= "AND arquivo_liberacao = '".$nome_arquivo."'";
    
    $db->update($usql, 'MYSQL');
    
    if ($db->erro != '')
    {
        $resposta->addAlert('Arquivo Excluido parcialmente! Houve uma falha no registro do banco de dados!');
    }
    else
    {
        $resposta->addScript('xajax_atualizatabela_medicoes('.$idItem.')');
        $resposta->addAlert('Arquivo Excluído corretamente!');
        $resposta->addScript('divPopupInst.destroi(1);');
    }
    
    return $resposta;
}

function salvarNFSaldo($dados_form)
{
    $resposta = new xajaxResponse();
    $db = new banco_dados();
    
    if (!empty($dados_form['idPedido']))
    {
        $usql = "UPDATE ".DATABASE.".bms_pedido SET ";
		$usql .= "numero_nf_saldo = '".$dados_form['nfSaldo']."' ";
		$usql .= "WHERE id_bms_pedido = ".$dados_form['idPedido']." ";
		$usql .= "AND reg_del = 0 ";
        
		$db->update($usql, 'MYSQL');
        
        if ($db->erro != '')
        {
            $resposta->addAlert('Houve uma falha ao tentar salvar o registro. '.$db->erro);
        }
        else
        {
            $resposta->addAlert('Nota gravada corretamente!');
            $resposta->addAssign('nfDifFat_'.$dados_form['idPedido'], 'value', $dados_form['nfSaldo']);
            $resposta->addScript('divPopupInst.destroi();');
        }
    }
    else
    {
        $resposta->addAlert('Está faltando o número do pedido e/ou numero da nota fiscal');
    }
    
    return $resposta;
}

function marcarRD($idBmsPedido,$rd)
{
    $resposta = new xajaxResponse();
    $db = new banco_dados();
    
    if (!empty($idBmsPedido) && $rd != '')
    {
        $usql = "UPDATE ".DATABASE.".bms_pedido SET ";
        $usql .= "reembolso_despesa = '".intval($rd)."', alterado_manualmente = 1 ";
        $usql .= "WHERE id_bms_pedido = ".$idBmsPedido." ";
        $usql .= "AND reg_del = 0 ";

        $db->update($usql, 'MYSQL');
        
        if ($db->erro != '')
        {
            $resposta->addAlert('Houve uma falha ao tentar salvar o registro. '.$db->erro);
        }
        else
        {
            $resposta->addAlert('Registro alterado corretamente!');
        }
    }
    else
    {
        $resposta->addAlert('Está faltando o número do pedido');
    }
    
    return $resposta;
}

function modal_informacoes_pedido($os)
{
    $resposta = new xajaxResponse();
    $db = new banco_dados();
    
    $osInt = intval($os);
    
    $id_bms_pedidos_informacoes = $condPgto = $formaPgto = $recebimento = $medicao = $periodoMed = $respMed = $respNF = $obs = '';
    
    $sql = "SELECT * FROM ".DATABASE.".bms_pedidos_informacoes WHERE reg_del = 0 AND id_os = ".$osInt;
    $db->select($sql, 'MYSQL', true);
    
    if ($db->numero_registros > 0)
    {
        $id_bms_pedidos_informacoes = $db->array_select[0]['id_bms_pedidos_informacoes'];
        $condPgto = $db->array_select[0]['cond_pgto'];
        $formaPgto = $db->array_select[0]['forma_pgto'];
        $recebimento = $db->array_select[0]['recebimento'];
        $medicao = $db->array_select[0]['data_medicao'];
        $periodoMed = $db->array_select[0]['periodo_medicao'];
        $respMed = $db->array_select[0]['responsavel_medicao'];
        $respNF = $db->array_select[0]['responsavel_nf'];
        $obs = $db->array_select[0]['obs'];
    }
    else
    {
		//VERIFICANDO SE A TEM OS RAIZ E SE ESTA RAIZ TEM DADOS DE CONTRATO PARA BUSCAR
		/*
        $sql = "SELECT AF1_RAIZ, AF1_ORCAME FROM AF1010 WHERE D_E_L_E_T_ = '' AND AF1_ORCAME = '".sprintf('%010d', $os)."'";
        $db->select($sql, 'MSSQL', true);
		$osRaiz = trim($db->array_select[0]['AF1_RAIZ']);
		*/
        
        //Buscando dados do contrato da os ou da raiz
        $complOsRaiz = intval($osRaiz) > 0 ? ", (SELECT os FROM ".DATABASE.".bms_pedido WHERE reg_del = 0 AND id_os = '".$osRaiz."')" : '';
        $sql = "SELECT * FROM ".DATABASE.".bms_pedidos_informacoes WHERE reg_del = 0 AND id_os IN(".$osInt.$complOsRaiz.")";
        $db->select($sql, 'MYSQL', true);
        
        if ($db->numero_registros > 0)
        {
            $condPgto = $db->array_select[0]['cond_pgto'];
            $formaPgto = $db->array_select[0]['forma_pgto'];
            $recebimento = $db->array_select[0]['recebimento'];
            $medicao = $db->array_select[0]['data_medicao'];
            $periodoMed = $db->array_select[0]['periodo_medicao'];
            $respMed = $db->array_select[0]['responsavel_medicao'];
            $respNF = $db->array_select[0]['responsavel_nf'];
            $obs = $db->array_select[0]['obs'];
        }
    }
    
    $html = '<form id="frm_informacoes" method="post">'.
        '<input name="os" type="hidden" id="os" value="'.$os.'">'.
        '<input name="id_bms_pedidos_informacoes" type="hidden" id="id_bms_pedidos_informacoes" value="'.$id_bms_pedidos_informacoes.'">'.
        '<label class="labels" style="float:left;width:140px;">Condições de PGTO</label>'.
        '<input name="cond_pgto" class="caixa" type="text" placeholder="Condicoes de pgto 10DDL | 60DDL ..." id="cond_pgto" size="90" value="'.$condPgto.'" /><br />'.
        
        '<label class="labels" style="float:left;width:140px;">Responsável Medição</label>'.
        '<input name="responsavel_medicao" class="caixa" style="text-transform:initial;" type="text" placeholder="Nome e/ou E-mail responsável pela aprovacao do boletim de medicao" id="responsavel_medicao" size="90" value="'.$respMed.'" /><br />'.
        
        '<label class="labels" style="float:left;width:140px;">Responsável NF</label>'.
        '<input name="responsavel_nf" class="caixa" style="text-transform:initial;" type="text" placeholder="Nome e/ou E-mail responsável pelo recebimento da nota fiscal" id="responsavel_nf" size="90" value="'.$respNF.'" /><br />'.
        
        '<label class="labels" style="float:left;width:140px;">Forma de PGTO</label>'.
        '<input name="forma_pgto" class="caixa" type="text" placeholder="Digite uma forma de PGTO - 3X Mes - 10/20/30 ..." id="forma_pgto" size="90" value="'.$formaPgto.'" /><br />'.
        
        '<label for="recebimento" style="float:left;width:140px;" class="labels">Data&nbsp;de&nbsp;Recebimento</label>'.
        '<input name="recebimento" type="text" class="caixa" placeholder="Até 25/MÊS | Até 30/MÊS ..." id="recebimento"  value="'.$recebimento.'" /><br />'.
        
        '<label for="data_medicao" style="float:left;width:140px;" class="labels">Data&nbsp;de&nbsp;Medição</label>'.
        '<input name="data_medicao" placeholder="30/MÊS | 10/MÊS ..." type="text" class="caixa" id="data_medicao"  value="'.$medicao.'" /><br />'.
        
        '<label for="periodo_medicao" style="float:left;width:140px;" class="labels">Período&nbsp;de&nbsp;Medição</label>'.
        '<input name="periodo_medicao" type="text" class="caixa" placeholder="26 - 25 | 11 - 10 ..." id="periodo_medicao"  value="'.$periodoMed.'" /><br />'.
        
        '<label for="obs" style="float:left;width:140px;" class="labels">Outras informações</label>'.
        '<textarea id="obs" name="obs" class="caixa" cols="88" rows="7" placeholder="Outras informações importantes">'.$obs.'</textarea><br />'.
        
        '<input type="button" class="class_botao" onclick="xajax_salvar_informacoes_pedido(xajax.getFormValues(\'frm_informacoes\'));" value="SALVAR">'.
        '<input type="button" class="class_botao" onclick="xajax_modal_anexar_pedido('.$os.');" value="ANEXAR" />'.
        '</form>';
    
    $resposta->addScriptCall("modal",$html, '450_800', 'Informações sobre o pedido','1');
    
    return $resposta;
}

function modal_cancelar_saldo($idPedido)
{
    $resposta = new xajaxResponse();
    $db = new banco_dados();
    
    $html = '<form id="frmSaldoRemanescente" name="frmSaldoRemanescente">'.
                '<input type="hidden" name="idPedido" id="idPedido" value="'.$idPedido.'" />'.
                '<label class="labels">Motivo para cancelamento do saldo *</label><br />'.
                '<textarea id="motivoCancelamento" name="motivoCancelamento" class="caixa" style="width:95%;height:110px;"></textarea>'.
                '<input type="button" name="btnCancelarSaldo" onclick="if(confirm(\'Deseja cancelar todo o saldo deste pedido?\')){xajax_cancelar_saldo_remanescente(xajax.getFormValues(\'frmSaldoRemanescente\'));}" id="btnCancelarSaldo" class="class_botao" value="Confirmar cancelamento de saldo" style="width:auto;" />'.
            '</form>';
    
    $resposta->addScriptCall('modal', $html, '200_400', 'CANCELAMENTO DE SALDO REMANESCENTE');
    
    return $resposta;
}

function cancelar_saldo_remanescente($dados_form)
{
    $resposta = new xajaxResponse();
    $db = new banco_dados();
    
    if (empty($dados_form['motivoCancelamento']))
    {
        $resposta->addAlert('Por favor, digite um motivo para o cancelamento do saldo');
        return $resposta;
    }
    
    //Procura as medicoes planejadas e calcula o saldo remanescente
		$sql = "SELECT SUM(valor_planejado) total_cancelar, GROUP_CONCAT(DISTINCT id_bms_medicao) idsMedicao, b.id_os
	FROM 
		".DATABASE.".bms_medicao a
		JOIN ".DATABASE.".bms_pedido b ON b.id_bms_pedido = a.id_bms_pedido AND b.reg_del = 0
	WHERE
		a.reg_del = 0
		AND a.id_bms_pedido = ".$dados_form['idPedido']."
		AND a.id_bms_controle IN(1,4)
	GROUP BY
		b.id_os";
    
    $db->select($sql, 'MYSQL', true);
    
    if ($db->numero_registros == 0)
    {
        $resposta->addAlert('Não foram encontradas medições planejadas para serem canceladas');
        return $resposta;
    }
    
    $idsMedicao = $db->array_select[0]['idsMedicao'];
    $totalCancelar = $db->array_select[0]['total_cancelar'];
    $os = $db->array_select[0]['os'];
    
    //Cancela todas as medicoes encontradas
    $usql = "UPDATE ".DATABASE.".bms_medicao SET
                progresso_medido = '0', 
                quantidade_medida = '0', 
                valor_medido = '0',
                valor_saldo = '0', 
                percentual_diferenca = '0',
                valor_diferenca = '0',
                quantidade_diferenca = '0',
                id_bms_controle = '4'
            WHERE 
                id_bms_medicao IN(".$idsMedicao.")
                AND reg_del = 0 ";
    
    $db->update($usql,'MYSQL');
    
    if ($db->erro != '')
    {
        $resposta->addAlert('Houve uma falha ao tentar cancelar as medições '.$db->erro);
        return $resposta;
    }
    
    //Retira o saldo remanescente do pedido
    $usql = "UPDATE ".DATABASE.".bms_pedido SET valor_pedido = valor_pedido - ".$totalCancelar." WHERE id_bms_pedido = ".$dados_form['idPedido']." AND reg_del = 0";
    $db->update($usql, 'MYSQL');
    
    if ($db->erro != '')
    {
        $resposta->addAlert('Houve uma falha ao alterar o valor do pedido '.$db->erro);
        return $resposta;
    }
    
    //Insere a observacao que e obrigatoria
    $isql = "INSERT INTO ".DATABASE.".bms_observacoes (id_bms_medicao, data, observacao) VALUES ";
    $arrMedicoes = explode(',', $idsMedicao);
    foreach($arrMedicoes as $idMed)
    {
        $isql .= $virgula."(".$idMed.", '".date('Y-m-d')."', '".trim(maiusculas($dados_form['motivoCancelamento']))."') ";
        $virgula = ',';
    }
    
    $db->insert($isql, 'MYSQL');
    
    //MANDAR EMAIL COM O MOTIVO DO CANCELAMENTO E PEDINDO PARA ALTERAR NO OR�AMENTO NO PROTHEUS.
    $params 			= array();
    $params['from']		= "comercial@dominio.com.br";
    $params['from_name']= "BMS - CANCELAMENTO DE SALDO";
    $params['subject'] 	= "Cancelamento de saldo remanescente - ";
    
    $corpo .= "ATENÇÃO: Foi cancelado o saldo remanescente da OS ".sprintf('%05d', $os)."<br />";
    $corpo .= "Saldo cancelado: R$ ".number_format($totalCancelar, 2, ',', '.')."<br />";
    $corpo .= "Motivo para cancelamento: ".trim(maiusculas($dados_form['motivoCancelamento']))."<br />";
    $corpo .= "Favor, ajustar o valor no módulo Orçamento.";
    
    $mail = new email($params, 'cancelamento_saldo_remanescente');
    $mail->montaCorpoEmail($corpo);
    
    if ($mail->send())
    {
        $resposta->addAlert('Cancelamento realizado corretamente;');
    }
    else
    {
        $resposta->addAlert('Cancelamento realizado, PORÉM, não foi possível enviar um e-mail ao orçamento;');
    }
    
    $resposta->addScript('xajax_editar('.$os.');xajax_atualizatabela();divPopupInst.destroi();');
    
    return $resposta;
}

function salvar_informacoes_pedido($dados_form)
{
    $resposta = new xajaxResponse();
    $db = new banco_dados();
    
    //Atualiza
    if (!empty($dados_form['id_bms_pedidos_informacoes']))
    {
        $virgula = '';
        $campos = '';
        $valores = '';
        $usql = 'UPDATE ".DATABASE.".bms_pedidos_informacoes SET ';
        foreach($dados_form as $campo => $valor)
        {
            if ($campo == 'id_bms_pedidos_informacoes')
            {
                $where = ' WHERE '.$campo.' = '.$valor;
                continue;
            }
            
            $usql .= $virgula.$campo." = '".maiusculas($valor)."'";
            $virgula = ', ';
        }
        $usql .= $where;
        $db->update($usql, 'MYSQL');
    }//Insere
    else
    {
        $virgula = '';
        $campos = '';
        $valores = '';
        $isql = '';
        foreach($dados_form as $campo => $valor)
        {
            if ($campo == 'id_bms_pedidos_informacoes')
                continue;
            
            $campos .= $virgula.$campo;
            $valores .= $virgula."'".maiusculas($valor)."'";
            $virgula = ', ';
        }
        $isql = "INSERT INTO ".DATABASE.".bms_pedidos_informacoes (".$campos.") VALUES (".$valores.")";
        $db->insert($isql, 'MYSQL');
        
        $resposta->addAssign('id_bms_pedidos_informacoes', 'value', $db->insert_id);
    }
    
    if($db->erro != '')
    {
        $resposta->addAlert('Houve uma falha ao tentar salvar o registro. '.$db->erro);
    }
    else
    {
        $resposta->addAlert('Registro salvo corretamente.');
    }
    
    return $resposta;
}

function showModalAnexoLiberacao($idMedicao, $idItem)
{
    $resposta = new xajaxResponse();
    $db = new banco_dados();

    if (HOST != 'localhost')
        $pastaArquivos = DOCUMENTOS_FINANCEIRO.'/pedidos/';
    else
        $pastaArquivos = str_replace('\\','/', ROOT_DIR). '/contratos_controle/pedidos/';
    
    $sql = "SELECT arquivo_liberacao FROM ".DATABASE.".bms_medicao WHERE reg_del = 0 AND id_bms_medicao = ".$idMedicao." AND arquivo_liberacao IS NOT NULL";
    $db->select($sql, 'MYSQL', true);
    
    $arquivo = $db->numero_registros > 0 ? $db->array_select[0]['arquivo_liberacao'] : '';
    
    if (empty($arquivo))
    {
        $html = '<fieldset style="margin:0;padding-bottom:0;"><legend class="labels">Documento de liberação'.
        '</legend><form id="frm_doc_liberacao" enctype="multipart/form-data" action="../contratos_controle/upload_bms_liberacoes.php" target="upload_target" method="post">'.
        '<input class="caixa" name="myfile" type="file" size="30" onchange=document.getElementById("frm_doc_liberacao").submit() />'.
        '<input name="idMedicaoLiberacao" type="hidden" id="idMedicaoLiberacao" value="'.$idMedicao.'" />'.
        '<input name="idItemLiberacao" type="hidden" id="idItemLiberacao" value="'.$idItem.'" />'.
        '<iframe id="upload_target" name="upload_target" src="#" style="width:100%;height:600px;border:1px solid #000;display:none;"></iframe>'.
        '</form></fieldset>';
    }
    else
    {
        $imgExcluir = '<button class="class_botao" %s style="margin-left:10px;'.$liberaExclusao.'">&nbsp;<span class="icone icone-excluir icone-botao"></span>EXCLUIR</button>';
        $acao = 'onclick="if(confirm(\'Deseja realmente excluir este arquivo?\')){xajax_excluir_arquivo_liberacao(\''.$arquivo.'\',\''.$idItem.'\');}"';
        $html = "<button class='class_botao' style='width:250px;float:left;' onclick=window.open('../includes/documento.php?documento=".$pastaArquivos.'/'.$arquivo."');>VISUALIZAR DOCUMENTO</button>".
        sprintf($imgExcluir, $acao);
    }
    
    $resposta->addScriptCall('modal', $html, '100_400', 'ANEXAR DOCUMENTO DE LIBERACAO', '1');
    return $resposta;
}

$xajax->registerFunction("excluir_arquivo");
$xajax->registerFunction("modal_anexar_pedido");
$xajax->registerFunction("modal_informacoes_pedido");
$xajax->registerFunction("limparFormMedicoes");
$xajax->registerFunction("excluir_pedido");
$xajax->registerFunction("excluir_medicao");
$xajax->registerFunction("faturar_medicao");
$xajax->registerFunction("carrega_total_orcamento");
$xajax->registerFunction("limparFormItens");
$xajax->registerFunction("excluir");
$xajax->registerFunction("editar");
$xajax->registerFunction("atualizar_item");
$xajax->registerFunction("altera_pedido");
$xajax->registerFunction("atualizatabela_itens");
$xajax->registerFunction("atualizatabela_medicoes");
$xajax->registerFunction("preenche_combo_item_medicoes");
$xajax->registerFunction("voltar");
$xajax->registerFunction("insere_itens");
$xajax->registerFunction("insere_medicoes");
$xajax->registerFunction("altera_medicao");
$xajax->registerFunction("atualizar_medicoes");
$xajax->registerFunction("preenchevalor");
$xajax->registerFunction("calcula_valor_percent");
$xajax->registerFunction("inserir_pedido");
$xajax->registerFunction("atualizatabela");
$xajax->registerFunction("gerar_bms");
$xajax->registerFunction("gerar_relatorio");
$xajax->registerFunction("calcula_quantidade");
$xajax->registerFunction("buscar_observacoes");
$xajax->registerFunction("salvar_observacoes");
$xajax->registerFunction("alterar_observacao");
$xajax->registerFunction("excluir_observacao");
$xajax->registerFunction("preencheComboPedidos");
$xajax->registerFunction("preenche_combo_status");
$xajax->registerFunction("editar_item");
$xajax->registerFunction("verificar_apontamentos");
$xajax->registerFunction("salvarInfoMedicao");
$xajax->registerFunction("modal_periodo_cliente");
$xajax->registerFunction("editar_periodo_cliente");
$xajax->registerFunction("salvar_periodo_cliente");
$xajax->registerFunction("medir_exato");
$xajax->registerFunction("verifica_saldo_item");
$xajax->registerFunction("salvarNFSaldo");
$xajax->registerFunction("salvar_informacoes_pedido");
$xajax->registerFunction("marcarRD");
$xajax->registerFunction("modal_cancelar_saldo");
$xajax->registerFunction("cancelar_saldo_remanescente");
$xajax->registerFunction("showModalAnexoLiberacao");
$xajax->registerFunction("excluir_arquivo_liberacao");


$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","tab();xajax_atualizatabela();xajax_preenche_combo_status();xajax_preenche_combo_item_medicoes()");
?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script language="javascript">
function showModalTexto(texto)
{
	//Retirando os # que coloquei devido a grid que nao aceita espacos
	texto = texto.replace(/#/g, ' ')
	html = '<label class="labels">'+texto+'</label>';
	modal(html, '100_500');
}

function showModalNFSaldo(idPedido,nfExistente)
{
	var html = 	'<form id="frmNFSaldo" name="frmNFSaldo">'+
				'<input type="hidden" id="idPedido" name="idPedido" value="'+idPedido+'" />'+
				'<label class="labels" for="nfSaldo">NF Destino Saldo</label>'+
				'<input type="text" id="nfSaldo" name="nfSaldo" class="caixa" value="'+nfExistente+'" /><br />'+
				'<input type="button" value="Gravar" class="class_botao" onclick="xajax_salvarNFSaldo(xajax.getFormValues(\'frmNFSaldo\'))" />'+
				'</form>';

	modal(html, '100_300', 'Digite a NF para diferença de faturamento');
}

function calcularSaldoReplicas()
{
    var saldo = document.getElementById('saldoMedicaoReplica');
    var saldoOficial = document.getElementById('saldoMedicaoReplicaHidden');
    var qtdElementos = document.getElementsByClassName('qtd_replica').length;
    var qtdSaldo = saldoOficial.value.replace('.','');
    qtdSaldo = qtdSaldo.replace(',', '.');
    
    for(var i=1;i<=qtdElementos;i++)
    {
      if(document.getElementById('qtd_replica['+i+']').value != '')
      {
        qtdItem = document.getElementById('qtd_replica['+i+']').value.replace('.', '')
        qtdItem = qtdItem.replace(',', '.');
        qtdSaldo -= qtdItem;
      }
    }

    qtdItem = document.getElementById('quantidade_planejada').value.replace('.', '')
    qtdItem = qtdItem.replace(',', '.');
    qtdSaldo -= qtdItem;
    
    saldo.value = qtdSaldo.toFixed(2).toString().replace('.', ',');
}

function desbloquearBotaoExcluir()
{
	if (mygrid.getCheckedRows(0) != "")
		document.getElementById('btnexcluir_selecionados').disabled=false;
	else
		document.getElementById('btnexcluir_selecionados').disabled=true;
}

function excluir_itens_selecionados()
{
	if (confirm('Deseja excluir os itens selecionados?'))
	{
    	var idsSelecionados = mygrid.getCheckedRows(0);
    	xajax_excluir_medicao(idsSelecionados, document.getElementById('id_item').value);
	}
}

function calcularHora()
{
	var valHora = document.getElementById('valor_hora').value.replace('.', '');
    	valHora = valHora.replace(',', '.');

    var qtd = document.getElementById('quantidade').value.replace('.', '');
        qtd = qtd.replace(',', '.');
        
    var valorTotalItem = valHora * qtd;

	document.getElementById('valor').value = valorTotalItem.toFixed(2).toString().replace('.', ',');
}

function exibirOcultarValorHora()
{
	//Se for hora, exibe o valor da hora para calculos
	if (document.getElementById('id_unidade').value == '6')
	{
		document.getElementById('spanHoraCalculo').style.display = 'block';
	}
	else
	{
		document.getElementById('spanHoraCalculo').style.display = 'none';
	}
}

function verificarApontamentos(idMedicao, idCliente)
{
	var html = 	'<form id="frmApontamentosEncontrados"><table width="100%"><tr>'+
					'<span id="camposOcultos"></span>'+
					'<input type="hidden" value="'+idMedicao+'" name="txtIdMedicao" id="txtIdMedicao" />'+
					'<input type="hidden" value="'+idCliente+'" name="txtIdCliente" id="txtIdCliente" />'+
					'<td width="30%"><label class="labels">Período de Medição</label></td>'+

					'<td width="30%"><label class="labels">tipo</labels></td>'+
					
					'<td><label class="labels">Período Selecionado</label></td></tr>'+
					'<tr><td width="30%"><span style="float:left;"><input type="text" name="diaInicioMedicao" id="diaInicioMedicao" size="5" /><label class="labels">&nbsp;a&nbsp;</label>'+
					'<input type="text" name="diaFimMedicao" id="diaFimMedicao" size="5" /></span></td>'+

					'<td><select name="selMes" id="selMes">'+
							'<option value="0">Fechamento</option>'+
							'<option value="1">Mês Anterior Medição</option>'+
							'<option value="2">Mês Medição</option>'+
						 '</select></td>'+
					
					'<td colspan="2"><input type="text" disabled="disabled" name="dataInicioMedicao" id="dataInicioMedicao" size="10" /><label class="labels">&nbsp;a&nbsp;</label>'+
					'<input type="text" disabled="readonly" name="dataFimMedicao" id="dataFimMedicao" size="10" /></td></tr>'+

					'<tr><td colspan="3"><table width="100%"><tr><td width="30%"><label class="labels">Horas Normais</label></td><td>'+
					'<input type="text" name="txtHN" id="txtHN" size="10" /></td>'+
					'<tr><td width="30%"><label class="labels">Horas Adic. Sem.</label></td><td>'+
					'<input type="text" name="txtHASem" id="txtHASem" size="10" /></td>'+
					'<td colspan="2"><label class="labels" style="float:left;width:90px;">% Adic. Sem.</label><input type="text" name="txtHASemPercentual" id="txtHASemPercentual" size="10" /></td>'+
					'<tr><td width="30%"><label class="labels">Horas Adic. Not.</label></td><td>'+
					'<input type="text" name="txtHAN" id="txtHAN" size="10" /></td>'+
					'<td colspan="2"><label class="labels" style="float:left;width:90px;">% Adic. Not.</label><input type="text" name="txtHANPercentual" id="txtHANPercentual" size="10" /></td></tr>'+
					'<tr><td width="30%"><label class="labels">Horas Adic. Sab.</label></td><td>'+
					'<input type="text" name="txtHASab" id="txtHASab" size="10" /></td>'+
					'<td colspan="2"><label class="labels" style="float:left;width:90px;">% Adic. Sab.</label><input type="text" name="txtHASabPercentual" id="txtHASabPercentual" size="10" /></td></tr>'+
					'<tr><td width="30%"><label class="labels">Horas Adic. Dom.</label></td><td>'+
					'<input type="text" name="txtHAD" id="txtHAD" size="10" /></td>'+
					'<td colspan="2"><label class="labels" style="float:left;width:90px;">% Adic. Dom.</label><input type="text" name="txtHADPercentual" id="txtHADPercentual" size="10" /></td>'+
					'</tr>'+
					'<tr><td width="30%"><label class="labels">Horas Adic. Feriado.</label></td><td>'+
					'<input type="text" name="txtHAFeriado" id="txtHAFeriado" size="10" /></td>'+
					'<td><label class="labels" style="float:left;width:90px;">% Adic. Fer.</label><input type="text" name="txtHAFeriadoPercentual" id="txtHAFeriadoPercentual" size="10" /></td>'+
					'<td><label class="labels">Feriados</label><span class="icone icone-inserir cursor" onclick="showModalFeriados();"></span><input type="hidden" name="txtFeriados" id="txtFeriados" /></td>'+
					'</tr>'+
					'<tr><td><label class="labels">Total Horas Medição</label></td><td>'+
					'<input disabled="disabled" type="text" name="txtTotalMedicao" id="txtTotalMedicao" size="10" /></td></tr>'+
					'<tr><td>&nbsp;</td></tr>'+
					'<tr><td colspan="2"><span class="icone icone-editar cursor" onclick="xajax_salvarInfoMedicao(xajax.getFormValues(\'frmApontamentosEncontrados\'));"></span>&nbsp;<label class="labels">Recalcular Medição</label></td>'+
					'<td colspan="2"><input class="class_botao" style="width:200px;" type="button" value="Medir valor apurado" onclick="xajax_atualizar_medicoes(xajax.getFormValues(\'frmApontamentosEncontrados\'));divPopupInst.destroi();" /></td></tr>'+
					'</table>'+
				'</table></form>';

	modal(html, '350_650', 'Apontamentos encontrados nesta medição');
	xajax_verificar_apontamentos(idMedicao);
}

function showModalFeriados()
{
	var feriados = document.getElementById('txtFeriados').value.split(',');
	var feriado1 = feriados[0];
	var feriado2 = feriados[1] != undefined ? feriados[1] : '';
	var feriado3 = feriados[2] != undefined ? feriados[2] : '';
	
	var html = 	'<label class="labels">Feriado 1</label>&nbsp;<input type="text" value="'+feriado1+'" size="10" name="feriado[1]" id="feriado[1]" onKeyPress="transformaData(this, event);" /><br />'+
				'<label class="labels">Feriado 2</label>&nbsp;<input type="text" value="'+feriado2+'" size="10" name="feriado[2]" id="feriado[2]" onKeyPress="transformaData(this, event);" /><br />'+
				'<label class="labels">Feriado 3</label>&nbsp;<input type="text" value="'+feriado3+'" size="10" name="feriado[3]" id="feriado[3]" onKeyPress="transformaData(this, event);" /><br />'+
				'<input type="button" value="Concluído" onclick="concluirFeriados();" />'; 

	modal(html, 'pp', 'Defina as datas de feriados', '1');
}

function concluirFeriados()
{
	var feriado1 = document.getElementById('feriado[1]').value;
	var feriado2 = document.getElementById('feriado[2]').value;
	var feriado3 = document.getElementById('feriado[3]').value;

	var feriados = document.getElementById('txtFeriados').value = feriado1+','+feriado2+','+feriado3;

	divPopupInst.destroi(1);
}

function showModalInformacoes(informacoes)
{
	informacoes = informacoes.split(';');
	
	var html = '<label class="labels"><ol>';
	for (i=0;i<informacoes.length-1;i++)
	{
		html += '<li>'+informacoes[i]+'</li>';
	}

	html += '</ol></label>';
	
	modal(html, '100_450', 'Informações sobre erros na importação');
}

var myTabbar;
var myGrid;

var iniciaBusca2=
{
	buffer: false,
	tempo: 1000, 

	verifica : function(textbox)
	{
		setTimeout('iniciaBusca2.compara("' + textbox.id + '", "' + textbox.value + '")', this.tempo); 
	},
	compara : function(id, valor)
	{
		if(valor == document.getElementById(id).value && valor != this.buffer)
		{
			this.buffer = valor;
			iniciaBusca2.chamaXajax(valor);
		}
	},

	chamaXajax : function(valor)
	{
		xajax_atualizatabela(valor, document.getElementById('exibir').value);	
	}
}

function showModalDatasReplicas(num_replicas)
{
	var html = "<label class='labels'>Saldo Disponível</label><input type='text' size='10' name='saldoMedicaoReplica' disabled='disabled' class='caixa' id='saldoMedicaoReplica' /><hr />";
	html += "<input type='hidden' name='saldoMedicaoReplicaHidden' class='caixa' id='saldoMedicaoReplicaHidden' />";

	for(i = 1; i <= num_replicas; i++)
	{
		html += "<label class='labels'>data "+i+"</labels> "+
				"<input type='text' size='10' onKeyPress='transformaData(this, event);' class='caixa' name='data_replica["+i+"]' id='data_replica["+i+"]' />"+
				"<label class='labels'>Qtd</labels> "+
				"<input type='text' size='5' onblur='calcularSaldoReplicas();' name='qtd_replica["+i+"]' class='caixa qtd_replica' id='qtd_replica["+i+"]' />"+
				"<br />";
	}	

	var fatorAltura = 33*num_replicas+88;
	
	html += "<input type='button' id='btnEnviarDatasReplicas' name='btnEnviarDatasReplicas' class='class_botao' value='Definir' onclick='definirDatas("+num_replicas+");' />";
	modal(html, fatorAltura+'_290', 'Especifique as datas das medições');

	xajax_verifica_saldo_item(document.getElementById('id_item').value, document.getElementById('quantidade_planejada').value);
}

function definirDatas(num_datas)
{
	var datas = '';
	var virgula = '';
	var pVirgula = '';
	var qtds = '';
	
	for(i = 1; i <= num_datas; i++)
	{
	  datas += virgula+document.getElementById("data_replica["+i+"]").value;
	  qtds += pVirgula+document.getElementById("qtd_replica["+i+"]").value;
	  virgula = ',';
	  pVirgula = ';';
	}

	document.getElementById('datas_replica_definidas').value = datas;
	document.getElementById('qtds_replica_definidas').value = qtds;
	divPopupInst.destroi();
}

function buscar_observacoes(idMedicao)
{
	var html =  '<form id="frm_obs">'+
	    '<label class="labels">Descrição da Observação</label><br />'+
	    '<input type="hidden" id="id_bms_medicao" name="id_bms_medicao" value="'+idMedicao+'" />'+
	    '<input type="hidden" id="id_bms_observacao" name="id_bms_observacao" />'+
	    '<textarea id="txtObservacao" name="txtObservacao" cols="60" rows="2"></textarea><br />'+
	    '<input type="button" class="class_botao" id="btnEnviarObservacao" value="Salvar" onclick=xajax_salvar_observacoes(xajax.getFormValues("frm_obs")); /><br /><br />'+
	    '<div id="div_observacoes"></div>'+
	'</form>';

	modal(html, 'p', 'Observações da medição');
	xajax_buscar_observacoes(idMedicao);
}

function tab()
{
	myTabbar = new dhtmlXTabBar("my_tabbar");

	myTabbar.addTab("a10_", "solicitacao_documentos");
	myTabbar.addTab("a20_", "Item", null, null, true);
	myTabbar.addTab("a30_", "Progresso");
	myTabbar.addTab("a40_", "BMS");
	myTabbar.addTab("a50_", "solicitacao_documentos Finalizados");
	
	myTabbar.tabs("a10_").attachObject("a10");
	myTabbar.tabs("a20_").attachObject("a20");
	myTabbar.tabs("a30_").attachObject("a30");
	myTabbar.tabs("a40_").attachObject("a40");
	myTabbar.tabs("a50_").attachObject("a50");

	myTabbar.goToPrevTab();
	
	myTabbar.enableAutoReSize(true);
}

function grid(tabela, autoh, height, xml)
{	
	
	if (tabela != 'div_pedidos' && tabela != 'div_pedidos_finalizados')
		mygrid = new dhtmlXGridObject(tabela);
	
	function doOnRowSelected(row,col)
	{
		if(col<=5)
		{
			xajax_editar_item(row);

			return true;
		}
	}

	function doOnRowSelected2(row,col)
	{
		if(col<=10 && row.lastIndexOf('fim') == -1)
		{
			xajax_altera_medicao(row);

			return true;
		}
	}

	function doOnRowSelected3(id,col)
	{
		if(col<7)
		{
			xajax_editar(id);

			return true;
		}
	}

	function doOnRowSelected4(row,col)
	{
		if(col<=1)
		{
			xajax_alterar_observacao(row);

			return true;
		}
	}
	
	switch (tabela)
	{
		case 'divApontamentosEncontrados':
			mygrid.setHeader("Período, Horas Nor., Horas Adic., Horas Adic. Not.");
			mygrid.setInitWidths("160,100,100,120");
			mygrid.setColAlign("left,left,left,left");
			mygrid.setColTypes("ro,ro,ro,ro");
			mygrid.setColSorting("str,str,str,str");
		break;
		case 'div_observacoes':
			mygrid.setHeader("Data, Observação, D");
			mygrid.setInitWidths("90,*,50");
			mygrid.setColAlign("left,left,center");
			mygrid.setColTypes("ro,ro,ro");
			mygrid.setColSorting("str,str,str");

			mygrid.enableMultiline(true);
			
			mygrid.attachEvent('onRowSelect', doOnRowSelected4);
		break;
		case 'div_itens':
			mygrid.setHeader("Item, Descrição, Quantidade, Unidade, Valor, Saldo, D");
			mygrid.setInitWidths("100,*,100,100,100,100,50");
			mygrid.setColAlign("left,left,left,left,left,left,left");
			mygrid.setColTypes("ro,ro,ro,ro,ro,ro,ro");
			mygrid.setColSorting("str,str,str,str,str,str,str");
	
			mygrid.attachEvent('onRowSelect', doOnRowSelected);
		break;
		case 'div_medicoes':
			mygrid.setImagePath("<?php echo INCLUDE_JS; ?>dhtmlx_403/codebase/imgs/");
			
			var chkAll = '<input type="checkbox" id="chkTodos" style="margin:0;" onclick="mygrid.checkAll(this.checked);desbloquearBotaoExcluir();" />';
			
			mygrid.setHeader(chkAll+",Data,R$&nbsp;Planej,R$&nbsp;Medido, Dif.&nbsp;Med,%&nbsp;Plan,%&nbsp;Med, Saldo&nbsp;R$,Qtd.&nbsp;Planej,Qtd.&nbsp;Med,Dif.&nbsp;Fat,ST,NF,F,E,O,A,ME");
			mygrid.setInitWidths("30,80,90,90,*,*,*,100,*,*,*,30,*,30,30,30,30,35");
			mygrid.setColAlign("center,center,right,right,left,left,left,left,left,right,right,center,left,center,center,center,center,center");
			mygrid.setColTypes("ch,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
			mygrid.setColSorting("na,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str");

			mygrid.enableMultiline(true);

			mygrid.attachEvent("onCheck", function(rId,cInd,state){
				desbloquearBotaoExcluir();
			});
			
			mygrid.attachEvent('onRowSelect', doOnRowSelected2);
		break;
		case 'div_pedidos':
			mygrid = myTabbar.tabs("a10_").attachGrid();
			mygrid.setHeader("OS, Descrição, Data Pedido, Data Término, Situação, Progresso, Dif. Fat., RD, A, I, C, D");
			mygrid.setInitWidths("80,*,120,120,*,80,80,35,30,30,30,30,30");
			mygrid.setColAlign("left,left,left,left,left,left,center,left,center,center,center,center,center,center");
			mygrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
			mygrid.setColSorting("str,str,str,str,str,str,str,str,str,str,str,str,str,str");
			mygrid.enableMultiline(true);
			mygrid.attachEvent('onRowSelect', doOnRowSelected3);
		break;
		case 'div_pedidos_finalizados':
			mygrid = myTabbar.tabs("a50_").attachGrid();
			mygrid.setHeader("OS, Descrição, Data Pedido, Data Término, Situação, Progresso, Dif. Fat., RD, A, I, C");
			mygrid.setInitWidths("80,*,120,120,*,80,80,35,30,30,30");
			mygrid.setColAlign("left,left,left,left,center,left,center,left,center,center,center,center");
			mygrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
			mygrid.setColSorting("str,str,str,str,str,str,str,str,str,str,str,str");
			mygrid.enableMultiline(true);
			mygrid.attachEvent('onRowSelect', doOnRowSelected3);
		break;
	}

	if (tabela != 'div_pedidos' && tabela != 'div_pedidos_finalizados')
		mygrid.enableAutoHeight(autoh,height);
	
	mygrid.enableRowsHover(true,'cor_mouseover');
		
	mygrid.setSkin("dhx_skyblue");
    mygrid.enableMultiselect(true);
    mygrid.enableCollSpan(true);	
	mygrid.init();
	mygrid.loadXMLString(xml);
}

function habilitaMedicao(valor)
{
	if (valor == 2)
	{
		document.getElementById('valor_medido').removeAttribute('disabled');
		document.getElementById('percent_medido').removeAttribute('disabled');
		document.getElementById('quantidade_medida').removeAttribute('disabled');
		
		document.getElementById('valor_planejado').setAttribute('disabled','disabled');
		document.getElementById('percent_planejado').setAttribute('disabled','disabled');
		document.getElementById('quantidade_planejada').setAttribute('disabled','disabled');
		document.getElementById('chk_replicar').setAttribute('disabled','disabled');
		document.getElementById('txt_num_replicas').setAttribute('disabled','disabled');		
	}
	else
	{
		document.getElementById('valor_medido').setAttribute('disabled','disabled');
		document.getElementById('percent_medido').setAttribute('disabled','disabled');
		document.getElementById('quantidade_medida').setAttribute('disabled','disabled');
		
		document.getElementById('valor_planejado').removeAttribute('disabled');
		document.getElementById('percent_planejado').removeAttribute('disabled');
		document.getElementById('quantidade_planejada').removeAttribute('disabled');
		document.getElementById('chk_replicar').removeAttribute('disabled');
		document.getElementById('txt_num_replicas').removeAttribute('disabled');
	}
	
	return true;
}

function limparTabelaMedicoes()
{
	grid('div_medicoes', true, '460', '');
	return false;
}

function abrir_relatorios()
{
	var html = 	'<div><form id="frmRelatorios"><table>'+
					'<tr><td width="5%"><label class="labels">Data</labels><br /><input onKeyPress="transformaData(this, event);" type="text" size="13" class="caixa" id="dataDigitada" name="dataDigitada" /></td>'+
					'<td width="5%"><label class="labels">Data Fim</labels><br /><input onKeyPress="transformaData(this, event);" type="text" size="13" class="caixa" id="dataDigitada2" name="dataDigitada2" /></td>'+
					'<td colspan="2"><label class="labels">solicitacao_documentos</labels><br /><select class="caixa" id="selPedidos" name="selPedidos" style="width:100%"><option value="">Selecione...</option></select></td>'+
					'<td><span class="icone icone-desfazer cursor" style="float:right;" title="Limpar Formulário" onclick="document.getElementById(\'frmRelatorios\').reset();"></span></td></tr>'+
					'<tr><td colspan="5"><table><tr><td><input type="button" class="class_botao" style="width: 165px;" onclick="xajax_gerar_relatorio(\'bms_vendas_periodo\', dataDigitada.value, dataDigitada2.value, selPedidos.value);" value="Vendas no Período" /></td>'+
					'<td><input type="button" class="class_botao" style="width: 100px;" onclick="xajax_gerar_relatorio(\'bms_saldo_cliente\', dataDigitada.value, dataDigitada2.value, selPedidos.value);" value="Saldo Cliente" /></td>'+
					'<td><input type="button" class="class_botao" style="width: 90px;" onclick="xajax_gerar_bms(selPedidos.value, dataDigitada.value);" value="Gerar BMS" /></td>'+
					'<td><input type="button" class="class_botao" style="width: 180px;" onclick="modalMedicaoFatura();" value="Medição e Fatura / Período" /></td>'+
					'<td><input type="button" class="class_botao" style="width: 160px;" onclick="modalPlanejamentoPeriodo();" value="Planejamento / Período" /></td></tr>'+
					'<input type="hidden" id="tpMedicoes" name="tpMedicoes" />'+
				'</table></td></tr></table></form></div>';
	modal(html, 'gpp', 'RELATÓRIOS BOLETIM DE MEDIÇÃO');
	xajax_preencheComboPedidos();
}

function modalMedicaoFatura()
{
	var dataDigitada = document.getElementById('dataDigitada').value;
	var dataDigitada2 = document.getElementById('dataDigitada2').value;
	var selPedidos = document.getElementById('selPedidos').value;
	
	if (dataDigitada == '' || dataDigitada2 == '')
	{
		alert('� necess�rio selecionar a data e data Fim');
		return false;
	}
		
	var html = '<label class="labels">Todas as medições</label>'+
    '<input onclick="xajax_gerar_relatorio(\'bms_medicao_fatura_periodo\', \''+dataDigitada+'\', \''+dataDigitada2+'\', \''+selPedidos+'\', 0);divPopupInst.destroi(1);" type="radio" name="rdo_tp_medicoes" value="0", id="rdo_tp_medicoes"><br />'+
    '<label class="labels">Não viraram Nota</label>'+
    '<input onclick="xajax_gerar_relatorio(\'bms_medicao_fatura_periodo\', \''+dataDigitada+'\', \''+dataDigitada2+'\', \''+selPedidos+'\', 1);divPopupInst.destroi(1);" type="radio" name="rdo_tp_medicoes" value="1", id="rdo_tp_medicoes"><br />'+
    '<label class="labels">Viraram Nota</label>'+
    '<input onclick="xajax_gerar_relatorio(\'bms_medicao_fatura_periodo\', \''+dataDigitada+'\', \''+dataDigitada2+'\', \''+selPedidos+'\', 2);divPopupInst.destroi(1);" type="radio" name="rdo_tp_medicoes" value="2", id="rdo_tp_medicoes"><br />'+
    '<label class="labels">OS Administrativas</label>'+
    '<input onclick="xajax_gerar_relatorio(\'bms_medicao_fatura_periodo\', \''+dataDigitada+'\', \''+dataDigitada2+'\', \''+selPedidos+'\', 3);divPopupInst.destroi(1);" type="radio" name="rdo_tp_medicoes" value="2", id="rdo_tp_medicoes"><br />'+
    '<label class="labels">Pacotes</label>'+
    '<input onclick="xajax_gerar_relatorio(\'bms_medicao_fatura_periodo\', \''+dataDigitada+'\', \''+dataDigitada2+'\', \''+selPedidos+'\', 4);divPopupInst.destroi(1);" type="radio" name="rdo_tp_medicoes" value="2", id="rdo_tp_medicoes">';
    
	modal(html, '150_250','Selecione uma opção', 1);
}

function modalPlanejamentoPeriodo()
{
	var dataDigitada = document.getElementById('dataDigitada').value;
	var dataDigitada2 = document.getElementById('dataDigitada2').value;
	var selPedidos = document.getElementById('selPedidos').value;
	
	if (dataDigitada == '' || dataDigitada2 == '')
	{
		alert('É necessário selecionar a Data e Data Fim');
		return false;
	}

	var html = '<label class="labels">Todos os pedidos</label>'+
    '<input onclick="xajax_gerar_relatorio(\'bms_planejado_periodo\', \''+dataDigitada+'\', \''+dataDigitada2+'\', \''+selPedidos+'\', 0);divPopupInst.destroi(1);" type="radio" name="rdo_tp_medicoes" value="0", id="rdo_tp_medicoes"><br />'+
    '<label class="labels">Pacotes</label>'+
    '<input onclick="xajax_gerar_relatorio(\'bms_planejado_periodo\', \''+dataDigitada+'\', \''+dataDigitada2+'\', \''+selPedidos+'\', 2);divPopupInst.destroi(1);" type="radio" name="rdo_tp_medicoes" value="2", id="rdo_tp_medicoes"><br />'+
    '<label class="labels">OS\'s Administrativas</label>'+
    '<input onclick="xajax_gerar_relatorio(\'bms_planejado_periodo\', \''+dataDigitada+'\', \''+dataDigitada2+'\', \''+selPedidos+'\', 1);divPopupInst.destroi(1);" type="radio" name="rdo_tp_medicoes" value="1", id="rdo_tp_medicoes">';
    
	modal(html, '150_250','Selecione uma opção', 1);

}

function abrir_cadastros()
{
	var html = 	'<div><table><tr><td><input type="button" class="class_botao" style="width: 165px;" onclick="xajax_modal_periodo_cliente();" value="Período Cliente" /></td></tr></table></div>';
	modal(html, '100_200', 'CADASTROS');
	xajax_preencheComboPedidos();
}

function showModalNF(idMedicao, idItem)
{
	var html = 	'<div align="center"><form id="frmFaturar" name="frmFaturar">'+
					'<label class="labels" style="float: left;">Número NF:</labels> <input type="text" placeholder="numero nf" class="caixa" id="numNF" name="numNF" /><br />'+
					'<label class="labels" style="float: left;">Faturar a Menor:</labels>'+
					'<input name="valor_faturado" type="text" class="caixa" id="valor_faturado" placeholder="valor faturar" size="15" maxlength="10" onKeyDown="FormataValor(frmFaturar.valor_faturado, 10, event)" />'+
					"<input type='button' class='class_botao' style='width: 290px;' onclick=if(numNF.value==''){alert('Por&nbsp;favor,&nbsp;digite&nbsp;o&nbsp;número&nbsp;da&nbsp;NF')}else{xajax_faturar_medicao('"+idMedicao+"','"+idItem+"',numNF.value,valor_faturado.value)} value='Marcar Como Faturado' />"+
					"<input type='button' class='class_botao' id='btnAnexarLiberacao' style='width: 290px;' onclick=xajax_showModalAnexoLiberacao('"+idMedicao+"','"+idItem+"'); value='Anexar Doc. Libera&ccedil;&atilde;o' />"+
				'</form></div>';
	modal(html, '140_300', 'DIGITE O NÚMERO DA NF');
}
</script>

<?php
$conf = new configs();

$msg = $conf->msg();

$sql = "SELECT id_formato, formato FROM ".DATABASE.".formatos ";
$sql .= "WHERE formatos.reg_del = 0 ";

$array_unidade_values = NULL;
$array_unidade_output = NULL;

$array_unidade_values[] = "";
$array_unidade_output[] = "SELECIONE";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	$resposta->addAlert($db->erro);
}

foreach($db->array_select as $reg)
{
  	$array_unidade_values[] = $reg['id_formato'];
  	$array_unidade_output[] = $reg['formato'];
}

/*
$array_cond_values = array();
$array_cond_output = array();

$array_cond_values[] = '';
$array_cond_output[] = 'SELECIONE';

$sql = "SELECT DISTINCT E4_DESCRI, E4_COND, E4_CODIGO FROM SE4010 ";
$sql .= "WHERE D_E_L_E_T_ = '' ";

$db->select($sql,'MSSQL',true);

if($db->erro!='')
{
	$resposta->addAlert($db->erro);
}

foreach($db->array_select as $reg)
{
	$array_cond_values[] = $reg['E4_CODIGO'];
	$array_cond_output[] = $reg['E4_DESCRI'];	
}
*/

$array_status_values[] = '';
$array_status_output[] = 'SELECIONE';

$sql = "SELECT id_bms_controle, bms_controle FROM ".DATABASE.".bms_controles ";
$sql .= "WHERE id_bms_controle IN(1,2,4,5,3) ";
$sql .= "AND reg_del = 0 ";
$sql .= "ORDER BY id_bms_controle ";

$db->select($sql,'MYSQL',true);

foreach($db->array_select as $reg)
{
	$array_status_values[] = $reg['id_bms_controle'];
	$array_status_output[] = $reg['bms_controle'];	
}

$sql = "SELECT id_os_status, os_status FROM ".DATABASE.".ordem_servico_status ";
$sql .= "WHERE fase_protheus <> '00' ";
$sql .= "AND reg_del = 0 ";
$sql .= "AND id_os_status IN(1,2,7,14,15,16) ";

$db->select($sql,'MYSQL',true);

if ($db->erro != '')
{
	exit("Não foi possível realizar a seleção.".$sql);
}

$array_status_os_values[] = '';
$array_status_os_output[] = 'SELECIONE';

foreach($db->array_select as $regs)
{
	$array_status_os_values[] = $regs["id_os_status"];
	$array_status_os_output[] = $regs["os_status"];
}

$smarty->assign("option_status_os_values",$array_status_os_values);
$smarty->assign("option_status_os_output",$array_status_os_output);

$smarty->assign("option_status_values",$array_status_values);
$smarty->assign("option_status_output",$array_status_output);

$smarty->assign("option_unidade_values",$array_unidade_values);
$smarty->assign("option_unidade_output",$array_unidade_output);

$smarty->assign("option_cond_values",$array_cond_values);
$smarty->assign("option_cond_output",$array_cond_output);

$smarty->assign("campo",$conf->campos('bms',$_COOKIE["idioma"]));
$smarty->assign("botao",$conf->botoes($_COOKIE["idioma"]));

$smarty->assign("revisao_documento","V7");

$smarty->assign("larguraTotal",1);

$smarty->assign("classe",CSS_FILE);

$smarty->display('bms.tpl');
?>