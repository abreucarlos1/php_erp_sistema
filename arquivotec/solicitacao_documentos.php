<?php
/*
		Formulário de Solicitação de Documentos	
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		../arquivotec/solicitacao_documentos.php
		
		Versão 0 --> VERSÃO INICIAL : 26/09/2005
		Versão 1 --> ATUALIZAÇÃO DE FORMULÁRIO (13/03/2006)
		Versão 2 --> Inclusão OS
		Versão 3 --> ATUALIZAÇÃO DE LAYOUT (07/03/2007)
		Versão 4 --> Atualização Lay-out | Smarty : 25/07/2008
		Versão 5 --> Adição da exclusão lógica do Ged, além de melhorias nas consultas
		Versão 6 --> Atualização da interface, titulos, inclusão de tabs - 30/03/2015 - Carlos Abreu
		Versão 7 --> Adicionado o campo servico_id na tabela solicitacao_documentos_detalhes - 30/06/2015 - Carlos Eduardo
		Versão 8 --> Inclusão campo numero_cliente, verificação dos números antes inclusão - 05/08/2015 - Carlos Abreu
		Versão 9 --> Utilização da classe email para maior controle de emails
		Versão 10--> Melhorias no Layout - 29/04/2016 - Carlos Abreu
		Versão 11 --> atualização layout - Carlos Abreu - 22/03/2017
		Versão 12 --> unificação das tabelas numero_cliente e numeros_interno - 10/05/2017 - Carlos Abreu
		Versão 13 --> inclusão da fase 09 - 12/07/2017 - Chamado #1925 - Carlos Abreu
		Versão 14 --> Inclusão dos campos reg_del nas consultas - 16/11/2017 - Carlos Abreu
		Versão 15 --> Remoção dos campos Folhas e Formato - #2567 - 09/02/2018 - Carlos Abreu
*/	

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));

require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(133))
{
	nao_permitido();
}

function dados_os($id_os)
{	
	$db = new banco_dados;

	$sql = "SELECT * FROM ".DATABASE.".ordem_servico ";
	$sql .= "WHERE ordem_servico.id_os = '".$id_os."' ";
	$sql .= "AND ordem_servico.reg_del = 0 ";
	
	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	
	$regs0 = $db->array_select[0];
	
	$array_dados['os'] = $regs0["os"];
	
	$array_dados['descricao'] = $regs0["descricao"];
	
	$array_dados['id_cliente'] = $regs0["id_empresa_erp"];
	
	$array_dados['titulo1'] = $regs0["titulo_1"];
	
	$array_dados['titulo2'] = $regs0["titulo_2"];
	
	return $array_dados;		
}

function voltar($pedido = 1)
{
	$resposta = new xajaxResponse();
	
	//se pedido, apaga valor
	if($pedido)
	{
		$resposta->addAssign("id_solicitacao_documento", "value", "");
	}
	
	$resposta->addAssign("data", "value", date('d/m/Y'));
	
	$resposta->addAssign("id_disciplina", "selectedIndex", "0");
	
	$limp = "xajax.$('id_atividade').length = null";
	
	$resposta->addScript($limp);
	
	$comb = "xajax.$('id_atividade').options[xajax.$('id_atividade').length] = new Option('SELECIONE','');";
	
	$resposta->addScript($comb);
	
	$resposta->addScript("document.getElementsByName('tipodoc')[0].checked=true");
	
	$resposta->addScript("habilita(false)");
	
	$resposta->addAssign("tag", "value", "");
	
	$resposta->addAssign("tag2", "value", "");
	
	$resposta->addAssign("tag3", "value", "");
	
	$resposta->addAssign("tag4", "value", "");
	
	$resposta->addAssign("area", "value", "");
	
	$resposta->addAssign("setor", "value", "");
	
	$resposta->addAssign("numero_cliente", "value", "");
	
	$resposta->addAssign("formato", "selectedIndex", "0");
	
	$resposta->addAssign("folhas", "value", "");
	
	$resposta->addAssign("versao_documento", "value", "0");
	
	$resposta->addAssign("txt_os", "value", "");
	
	$resposta->addAssign("btninserir","disabled","true");
	
	return $resposta;
}

function atualizatabela($dados_form)
{
	$resposta = new xajaxResponse();
	
	$xml = new XMLWriter();
	
	$db = new banco_dados;
	
	$sql_filtro = "";
	
	$sql_texto = "";
	
	if($dados_form["busca"]!="")
	{
		$array_valor = explode(" ",$dados_form["busca"]);
		
		for($x=0;$x<count($array_valor);$x++)
		{
			$sql_texto .= "%" . $array_valor[$x] . "%";
		}
		
		$sql_filtro = " AND ordem_servico.os LIKE '".$sql_texto."' ";
	}
	
	if($dados_form["os"]!='')
	{
		$sql_filtro .= " AND ordem_servico.id_os = '".$dados_form["os"]."' ";
	}

	$sql = "SELECT DISTINCT * FROM ".DATABASE.".solicitacao_documentos ";
	$sql .= "LEFT JOIN(
		SELECT id_solicitacao_documento codigo_pedido FROM ".DATABASE.".solicitacao_documentos_detalhes WHERE solicitacao_documentos_detalhes.reg_del = 0
	) solicitacao_documentos_detalhes
	ON codigo_pedido = id_solicitacao_documento ";
	$sql .= ", ".DATABASE.".ordem_servico ";
	$sql .= "WHERE id_funcionario='". $_SESSION["id_funcionario"]. "' ";
	$sql .= "AND solicitacao_documentos.reg_del = 0 ";
	$sql .= "AND ordem_servico.reg_del = 0 ";
	$sql .= "AND solicitacao_documentos.id_os = ordem_servico.id_os ";	
	//$sql .= "AND ordem_servico.os > 100 ";
	$sql .= $sql_filtro;	
	$sql .= "GROUP BY solicitacao_documentos.id_solicitacao_documento ";
	$sql .= "ORDER BY solicitacao_documentos.id_solicitacao_documento DESC, solicitacao_documentos.data DESC ";
	
	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	
	$xml->openMemory();
	$xml->setIndent(false);
	$xml->startElement('rows');

	foreach($db->array_select as $regs)
	{
		$status = "";
		
		switch ($regs["status"])
		{
			case 0:
				$status = 'NÃO&nbsp;ENVIADA';
			break;
			case 1:
				$status = 'ENVIADA';
			break;
			case 2:
				$status = 'RE-ENVIADA';
			break;
		}
		
		if(intval($regs['codigo_pedido'])==0 || $regs["status"] == 0)
		{
			$txt = '<img src="'.DIR_IMAGENS.'apagar.png" style="cursor:pointer;" onclick = if(confirm("Confirma&nbsp;a&nbsp;exclusão&nbsp;do&nbsp;pedido&nbsp;selecionado?")){xajax_excluir(' . $regs["id_solicitacao_documento"] . ');}>';
		}
		else
		{
			$txt = '&nbsp;';
		}
		
		$xml->startElement('row');
			$xml->writeAttribute('id', 'sl_'.$regs["id_solicitacao_documento"]);
			$xml->writeElement('cell', sprintf("%05d",$regs["id_solicitacao_documento"]));
			$xml->writeElement('cell', sprintf("%010d",$regs["os"])."&nbsp;-&nbsp;".trim($regs["descricao"]));
			$xml->writeElement('cell', mysql_php($regs["data"]));
			$xml->writeElement('cell', $status);
			$xml->writeElement('cell', $txt);
		$xml->endElement();	
		
	}
	
	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);

	$resposta->addScript("grid('solicitacao', true, '500', '".$conteudo."');");

	return $resposta;
}

function atualizatabela_itens($dados_form)
{	
	$resposta = new xajaxResponse();
	
	$xml = new XMLWriter();

	$db = new banco_dados;
	
	$sql = "SELECT * FROM ".DATABASE.".solicitacao_documentos ";
	$sql .= "WHERE solicitacao_documentos.reg_del = 0 ";
	$sql .= "AND solicitacao_documentos.id_solicitacao_documento = '". $dados_form["id_solicitacao_documento"]. "' ";
	
	$db->select($sql, 'MYSQL',true);
	
	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	
	$regs0 = $db->array_select[0];	
	
	$sql = 
	" SELECT DISTINCT * FROM ".DATABASE.".solicitacao_documentos
	JOIN(
	  SELECT id_solicitacao_documento as pedido, item_pedido, id_atividade, numero_cliente, id_disciplina, id_numero_interno, tag, tag2, tag3, tag4, id_solicitacao_documentos_detalhe, id_formato, folhas, versao_documento FROM ".DATABASE.".solicitacao_documentos_detalhes
	  WHERE solicitacao_documentos_detalhes.reg_del = 0 
	) solicitacao_documentos_detalhes
	ON solicitacao_documentos_detalhes.pedido = solicitacao_documentos.id_solicitacao_documento
	
	JOIN(
		SELECT * FROM ".DATABASE.".formatos WHERE formatos.reg_del = 0 
	) formatos
	ON formatos.id_formato = solicitacao_documentos_detalhes.id_formato
	
	JOIN(
	  SELECT id_atividade codigo, descricao Descricao_Atividade FROM ".DATABASE.".atividades WHERE atividades.reg_del = 0 
	) atividades
	ON codigo = solicitacao_documentos_detalhes.id_atividade
	
	JOIN(
	  SELECT id_setor codigosetor, setor, sigla FROM ".DATABASE.".setores WHERE setores.reg_del = 0 
	) setores
	ON codigosetor = solicitacao_documentos_detalhes.id_disciplina	
	
	JOIN(
	  SELECT * FROM ".DATABASE.".ordem_servico WHERE ordem_servico.reg_del = 0 
	) ordem_servico
	ON ordem_servico.id_os = solicitacao_documentos.id_os

	LEFT JOIN(
		SELECT id_numero_interno, count(*) as total_arquivos FROM ".DATABASE.".ged_arquivos
		WHERE ged_arquivos.reg_del = 0
	) ged_arquivos
	ON ged_arquivos.id_numero_interno = solicitacao_documentos_detalhes.id_numero_interno
	
	WHERE
		solicitacao_documentos.id_solicitacao_documento = '". $dados_form["id_solicitacao_documento"]. "'
		AND solicitacao_documentos.reg_del = 0
	ORDER BY
		solicitacao_documentos_detalhes.item_pedido  ";
	
	$db->select($sql,'MYSQL',true);
	
	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
		
	$xml->openMemory();
	$xml->setIndent(false);
	$xml->startElement('rows');

	foreach($db->array_select as $regs)
	{	
		if($regs['total_arquivos'] > 0)
		{
			$txt = '&nbsp;';
			
			$hab_sol = false;
		}
		else
		{
			if($regs0["status"]==0)
			{
				$txt = '<img src="'.DIR_IMAGENS.'apagar.png" style="cursor:pointer;" onclick = if(confirm("Confirma&nbsp;a&nbsp;exclusão&nbsp;do&nbsp;documento&nbsp;selecionado?")){xajax_excluir_item(' . $regs["id_solicitacao_documentos_detalhe"] . ');}>';
		
				$hab_sol = true;
			}
			else
			{
				$txt = '&nbsp;';
				
				$hab_sol = false;				
			}
		}
		
		$xml->startElement('row');
			$xml->writeAttribute('id', 'iten_'.$regs["id_solicitacao_documentos_detalhe"]);
			$xml->writeElement('cell', $regs["item_pedido"]);
			$xml->writeElement('cell', $regs["setor"]);
			$xml->writeElement('cell', $regs["numero_cliente"]);
			$xml->writeElement('cell', addslashes($regs["tag"]));
			$xml->writeElement('cell', addslashes($regs["tag2"]));
			$xml->writeElement('cell', addslashes($regs["tag3"]));
			$xml->writeElement('cell', addslashes($regs["tag4"]));
			$xml->writeElement('cell', $regs["formato"]);
			$xml->writeElement('cell', $regs["folhas"]);
			$xml->writeElement('cell', $regs["versao_documento"]);
			$xml->writeElement('cell', $txt);
		$xml->endElement();		
	}

	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);

	$resposta->addScript("grid('itens', true, '350', '".$conteudo."');");
	
	//se status do pedido como não enviado
	if($hab_sol)
	{
		$resposta->addAssign("btninserir","disabled","");
	}
	else
	{
		$resposta->addAssign("btninserir","disabled","true");
	}
	
	//se status do pedido como não enviado
	if($regs0["status"]==0)
	{
		$resposta->addAssign("btninserir_itens","disabled","");
	}
	else
	{		
		$resposta->addAssign("btninserir_itens","disabled","true");
	}
	
	$resposta->addScript("limpa_combo('servico')");
	
	$resposta->addScript("addOption('servico', 'SELECIONE', '')");
	
	//Criando o campo de serviços
	$sql = "SELECT * FROM ".DATABASE.".servicos ";
	$sql .= "WHERE servicos.reg_del = 0 ";
	$sql .= "AND servicos.id_os = ".$dados_form['os']." ";
	$sql .= "ORDER BY servicos.servico_descricao ";

	$db->select($sql,'MYSQL',true);
	
	foreach($db->array_select as $reg)
	{
		$resposta->addScript("addOption('servico', '".$reg['servico']." - ".$reg['servico_descricao']."', '".$reg['servico_id']."')");
	}

	return $resposta;
}

function enviar($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados();
	
	$sql = "SELECT * FROM ".DATABASE.".solicitacao_documentos, ".DATABASE.".usuarios, ".DATABASE.".funcionarios ";
	$sql .= "WHERE solicitacao_documentos.id_solicitacao_documento = '" . $dados_form["id_solicitacao_documento"] . "' ";
	$sql .= "AND solicitacao_documentos.reg_del = 0 ";
	$sql .= "AND usuarios.reg_del = 0 ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	$sql .= "AND usuarios.id_funcionario = solicitacao_documentos.id_funcionario ";
	$sql .= "AND usuarios.id_funcionario = funcionarios.id_funcionario ";
	
	$db->select($sql, 'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	
	$pedido = $db->array_select[0];
	
	$array_dados = dados_os($pedido["id_os"]);
	
	$array_erro = NULL;
	
	$array_detalhes = NULL;	
	
	//PEGA A REVISÃO ATUAL DO PROJETO
	/*
	$sql = "SELECT * FROM AF8010 WITH(NOLOCK) ";
	$sql .= "WHERE AF8010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF8010.AF8_PROJET = '".sprintf("%010d",$array_dados['os'])."' ";
	
	$db->select($sql, 'MSSQL', true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	
	$regs_atual = $db->array_select[0];
	*/
	
	if($db->numero_registros_ms > 0 || true)
	{
		$sql = "SELECT id_setor, setor, sigla FROM ".DATABASE.".setores ";
		$sql .= "WHERE setores.reg_del = 0 ";
		
		$db->select($sql,'MYSQL',true);

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
		
		foreach($db->array_select as $disc)
		{
			$array_setores[$disc["id_setor"]]['setor'] = $disc["setor"];
			$array_setores[$disc["id_setor"]]['sigla'] = $disc["sigla"];
		}		
		
		//percorre todos os itens do pedido	
		$sql = "SELECT * FROM ".DATABASE.".solicitacao_documentos_detalhes, ".DATABASE.".formatos, ".DATABASE.".atividades ";
		$sql .= "WHERE id_solicitacao_documento = '" . $dados_form["id_solicitacao_documento"] . "' ";
		$sql .= "AND solicitacao_documentos_detalhes.reg_del = 0 ";
		$sql .= "AND formatos.reg_del = 0 ";
		$sql .= "AND atividades.reg_del = 0 ";
		$sql .= "AND solicitacao_documentos_detalhes.id_atividade = atividades.id_atividade ";
		$sql .= "AND solicitacao_documentos_detalhes.id_formato = formatos.id_formato ";		
		$sql .= "ORDER BY solicitacao_documentos_detalhes.item_pedido ASC ";
		
		$db->select($sql, 'MYSQL',true);

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
		else
		{			
			if($db->numero_registros>0)
			{
				$array_itens = $db->array_select;		
								
				foreach($array_itens as $regs1)
				{
					$tipodoc = "";
					
					/* Não bloqueia mais - Conforme reunião P&D Ata #2 item 1.4 - a verificação/bloqueio seré realizada no GED		
					Alterado 16/04/2010 - Bloqueia somente se for da mesma OS
					
					 */  	
					//IMPLEMENTADO EM 01/04/2015
					//Verifica se o numero_cliente fornecido é existente
					$sql = "SELECT * FROM ".DATABASE.".numeros_interno ";
					$sql .= "WHERE numeros_interno.numero_cliente LIKE '" . trim(addslashes($regs1["numero_cliente"])) . "' ";
					$sql .= "AND numeros_interno.reg_del = 0 ";
					$sql .= "AND numeros_interno.id_os = '" . $pedido["id_os"] . "' ";
			
					$db->select($sql,'MYSQL',true);

					if($db->erro!='')
					{
						$resposta->addAlert($db->erro);
					}
			
					//Se existir
					if($db->numero_registros>0)
					{
						$array_erro[] = trim($regs1["numero_cliente"]);								
					}
					else
					{						
						$numcli = $regs1["numero_cliente"];
						
						$array_detalhes[$regs1["id_solicitacao_documentos_detalhe"]]['id_disciplina'] = $regs1["id_disciplina"];
						$array_detalhes[$regs1["id_solicitacao_documentos_detalhe"]]['id_atividade'] = $regs1["id_atividade"];
						$array_detalhes[$regs1["id_solicitacao_documentos_detalhe"]]['tipodoc'] = $regs1["tipodoc"];
						$array_detalhes[$regs1["id_solicitacao_documentos_detalhe"]]['tag'] = $regs1["tag"];
						$array_detalhes[$regs1["id_solicitacao_documentos_detalhe"]]['tag2'] = $regs1["tag2"];
						$array_detalhes[$regs1["id_solicitacao_documentos_detalhe"]]['tag3'] = $regs1["tag3"];
						$array_detalhes[$regs1["id_solicitacao_documentos_detalhe"]]['tag4'] = $regs1["tag4"];
						$array_detalhes[$regs1["id_solicitacao_documentos_detalhe"]]['numero_cliente'] = $numcli;
						$array_detalhes[$regs1["id_solicitacao_documentos_detalhe"]]['id_formato'] = $regs1["id_formato"];
						$array_detalhes[$regs1["id_solicitacao_documentos_detalhe"]]['formato'] = $regs1["formato"];
						$array_detalhes[$regs1["id_solicitacao_documentos_detalhe"]]['folhas'] = $regs1["folhas"];
						$array_detalhes[$regs1["id_solicitacao_documentos_detalhe"]]['obs'] = $regs1["obs"];
						$array_detalhes[$regs1["id_solicitacao_documentos_detalhe"]]['finalidade'] = $regs1["finalidade"];
						$array_detalhes[$regs1["id_solicitacao_documentos_detalhe"]]['area'] = $regs1["area"];
						$array_detalhes[$regs1["id_solicitacao_documentos_detalhe"]]['setor'] = $regs1["setor"];
						$array_detalhes[$regs1["id_solicitacao_documentos_detalhe"]]['versao_documento'] = $regs1["versao_documento"];												
					}
				}
				
				if(!empty($array_erro))
				{
					$txt = implode("\n",$array_erro);
				
					//Alerta o usuário e finaliza
					$resposta->addAlert("ERRO: O(s) Número(s) Cliente: \n".$txt."\n fornecido já existe(m) no sistema na mesma OS selecionada. Não será possível solicitar o pedido.\nFavor corrigir.");
					
					return $resposta;	
				}
				else
				{
					$tabela = 
					"<html>
					<head>
					<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />
					<title>SOLICITAÇÃO DE DOCUMENTOS</title>
					<style type=\"text/css\">
					.style4 {color: #0000FF; font-family: Arial, Helvetica, sans-serif; font-size: 10px; }
					.style7 {color: #000000; font-family: Arial, Helvetica, sans-serif; font-size: 9px; }
					</style>
					</head>
					
					<body>
					<table width=\"100%\">
					<tr>
					<td>SOLICITANTE: " . $pedido["funcionario"] . " </td>
					</tr>
					<tr>
					<td>DATA: " . mysql_php($pedido["data"]) . " </td>
					</tr>
					<tr>
					<td>OS: " . sprintf("%05d",$array_dados['os']) . " - " . $array_dados['descricao'] . " </td>
					</tr>
					<tr>
					<td>Nº&nbsp;PEDIDO: " . sprintf("%05d",$pedido["id_solicitacao_documento"]) . " </td>
					</tr>		
					</table>
					
					<table width=\"100%\" border=\"1\">
					  <tr>
						<td width=\"10%\" class=\"style4\">Nº&nbsp;Interno</td>
						<td width=\"10%\" class=\"style4\">Nº&nbsp;Cliente</td>
						<td width=\"10%\"><div align=\"center\" class=\"style4\">DISCIPLINA</div></td>			
						<td width=\"6%\"><div align=\"center\" class=\"style4\">TIPO</div></td>
						<td width=\"6%\"><div align=\"center\" class=\"style4\">FINALIDADE</div></td>
						<td width=\"10%\"><div align=\"center\" class=\"style4\">TÍTULO 1</div></td>
						<td width=\"10%\"><div align=\"center\" class=\"style4\">TÍTULO 2</div></td>
						<td width=\"10%\"><div align=\"center\" class=\"style4\">TÍTULO 3</div></td>
						<td width=\"10%\"><div align=\"center\" class=\"style4\">TÍTULO 4</div></td>
						<td width=\"8%\"><div align=\"center\" class=\"style4\">ÁREA</div></td>
						<td width=\"5%\"><div align=\"center\" class=\"style4\">SETOR</div></td>
						<td width=\"3%\" class=\"style4\">FMT</td>
						<td width=\"3%\" class=\"style4\"><div align=\"center\">FLS</div></td>
						<td width=\"3%\" class=\"style4\">REV.</td>
					  </tr>";
					  
					foreach($array_detalhes as $id_detalhe=>$a_campos)
					{
						//obtem o sequencial do numeros_interno								
						$sql = "SELECT sequencia FROM ".DATABASE.".numeros_interno ";
						$sql .= "WHERE id_os = '".$pedido["id_os"]."' ";
						$sql .= "AND numeros_interno.reg_del = 0 ";
						$sql .= "AND id_disciplina = '".$a_campos["id_disciplina"]."' ";						
						$sql .= "ORDER BY sequencia DESC ";
						 
						$db->select($sql,'MYSQL',true);
					
						if($db->erro!='')
						{
							$resposta->addAlert($db->erro);
						}
						
						if($db->numero_registros>0)
						{
							$seq = $db->array_select[0];
							
							$sequencia = $seq["sequencia"]+1;
						}
						else
						{
							$sequencia = 1;										
						}
						
						if($a_campos['numero_cliente']=="")
						{							
							$numcli = PREFIXO_DOC_GED . sprintf("%05d",$array_dados['os'])."-".$array_setores[$a_campos["id_disciplina"]]['sigla']."-".sprintf("%04d",$sequencia);		
						}
						else
						{
							$numcli = trim(addslashes($a_campos["numero_cliente"]));
						}
						
						$sequencia = sprintf('%04d', $sequencia);
						
						//insere o numero interno
						$isql = "INSERT INTO ".DATABASE.".numeros_interno ";
						$isql .= "(id_os, sequencia, id_disciplina, id_atividade, id_formato, numero_folhas, id_codigo_revisao, mostra_relatorios, numero_cliente, complemento, obs) ";
						$isql .= "VALUES ('".$pedido["id_os"]."','".$sequencia."', '".$a_campos["id_disciplina"]."','".$a_campos['id_atividade']."', '".$a_campos["id_formato"]."', ";
						$isql .= " '".$a_campos["folhas"]."', '1','1','".$numcli."','".trim(addslashes(maiusculas($a_campos["tag"])))."','" . trim(addslashes(maiusculas($a_campos["obs"]))) . "')";
						
						$db->insert($isql, 'MYSQL');
						
						$idnumdvm = $db->insert_id;

						if($db->erro!='')
						{
							$resposta->addAlert($db->erro);
						}
						
						//atualiza os detalhes pedido (compatibilidade)
						$usql = "UPDATE ".DATABASE.".solicitacao_documentos_detalhes SET ";
						$usql .= "solicitacao_documentos_detalhes.id_numero_interno = '".$idnumdvm."', ";
						$usql .= "solicitacao_documentos_detalhes.numero_cliente = '".$numcli."' ";
						$usql .= "WHERE solicitacao_documentos_detalhes.id_solicitacao_documentos_detalhe = '".$id_detalhe."' ";
						$usql .= "AND solicitacao_documentos_detalhes.reg_del = 0 ";
						
						$db->update($usql, 'MYSQL');

						if($db->erro!='')
						{
							$resposta->addAlert($db->erro);
						}
						
						$i = 0;
						
						if($i%2)
						{
							$cor = "#F0F0F0";			
						}
						else
						{
							$cor = "#FFFFFF";
						}
						
						$i++;							
				
						if($a_campos["tipodoc"])
						{
							$tipodoc = "EXISTENTE";
						}
						else
						{
							$tipodoc = "NOVO";
						}
						
						$tabela .=
						"<tr bgcolor=" .  $cor  . ">
						  
						  <td class=\"style7\">". PREFIXO_DOC_GED . sprintf("%05d",$array_dados['os']) . "-" . $array_setores[$a_campos["id_disciplina"]]['sigla'] . "-" . $sequencia . "</td>
						  
						  <td class=\"style7\">" . $numcli . "</td>
						  
						  <td class=\"style7\">" . $array_setores[$a_campos["id_disciplina"]]['setor'] . "</td>				 
						  <td class=\"style7\">" . $tipodoc . "</td>
						  <td class=\"style7\">" . $a_campos["finalidade"] . "</td>
						  <td class=\"style7\">" . addslashes(maiusculas($a_campos["tag"])) . "</td>
						  <td class=\"style7\">" . addslashes(maiusculas($a_campos["tag2"])) . "</td> 
						  <td class=\"style7\">" . addslashes(maiusculas($a_campos["tag3"])) . "</td> 
						  <td class=\"style7\">" . addslashes(maiusculas($a_campos["tag4"])) ."</td>
						  <td class=\"style7\">" . $a_campos["area"] . "</td>
						  <td class=\"style7\">" . $a_campos["setor"] . "</td>
						  <td class=\"style7\">" . $a_campos["formato"] . "</td>
						  <td class=\"style7\">" . $a_campos["folhas"] . "</td>
						  <td class=\"style7\">" . $a_campos["versao_documento"] . "</td>
						</tr>";						
						
					}					  

					$usql = "UPDATE ".DATABASE.".solicitacao_documentos SET ";
					$usql .= "status = '1' ";
					$usql .= "WHERE solicitacao_documentos.id_solicitacao_documento = '".$dados_form["id_solicitacao_documento"]."' ";
					$usql .= "AND solicitacao_documentos.reg_del = 0 ";
					
					$db->update($usql, 'MYSQL');
					
					$resposta->addAlert("Solicitação enviada com sucesso");
					
					$resposta->addScript("myTabbar.goToPrevTab();");
					
					$resposta->addScript("xajax_voltar();");
					
					$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm',true));");
					
					$params 			= array();
					$params['from']		= $pedido["email"];
					$params['from_name']= $pedido["Funcionario"];
					$params['subject'] 	= sprintf("%05d",$array_dados['os'])." - " . $array_dados['descricao'] . " - SOLICITAÇÃO DE DOCUMENTOS";
					
					$params['emails']['to'][] = array('email' => "arquivotecnico@dominio.com.br", 'nome' => "Arquivo Técnico");
					$params['emails']['to'][] = array('email' => $pedido["email"], 'nome' => $pedido["Funcionario"]);

					$mail = new email($params, 'solicitacao_documentos');
					
					$mail->montaCorpoEmail($tabela);
					
					if(!$mail->Send())
					{
						$resposta->addAlert('Erro ao enviar e-mail.');
					}
					
					$mail->ClearAddresses();
				}					
			}
			else
			{
				$resposta->addAlert('Não existem documentos a serem solicitados.');
			}
		}
	}
	else
	{
		$resposta->addAlert('Projeto fora da fase de execução.\n Favor procurar o Planejamento.');	
	}

	return $resposta;
}

function inserir($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	$item_pedido = 1;

	$numcli = '';
	
	$id_solicitacao_documento = '';		

	if (empty($dados_form["id_disciplina"]) || empty($dados_form["id_atividade"]) || trim($dados_form["versao_documento"]) == '')
	{
		$resposta->addAlert('Por favor, preencha todos os campos contendo (*), são obrigatórios!');
		
		return $resposta;
	}	
		
	if($dados_form["id_disciplina"]!=="" && $dados_form["id_atividade"]!=="")
	{
		if($dados_form["id_solicitacao_documento"]=='')
		{
			$isql = "INSERT INTO ".DATABASE.".solicitacao_documentos ";
			$isql .= "(id_funcionario, id_os, data) ";
			$isql .= "VALUES ('". $_SESSION["id_funcionario"]. "', ";
			$isql .= "'". $dados_form["os"]. "', ";
			$isql .= "'". php_mysql($dados_form["data"]). "') ";
			
			$db->insert($isql,'MYSQL');

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}
			
			$id_solicitacao_documento = $db->insert_id;		
			
			$resposta->addAssign("id_solicitacao_documento","value",$id_solicitacao_documento);
						
		}
		else
		{
			$id_solicitacao_documento = $dados_form["id_solicitacao_documento"];
		}
		
		//se existir id_solicitacao_documento, insere os detalhes
		if($id_solicitacao_documento!='' || $id_solicitacao_documento!=0)
		{	
			$array_dados = dados_os($dados_form["os"]);

			//IMPLEMENTADO EM 01/04/2015
			//ONDE O NUMERO INTERNO SERA CRIADO QUANDO A SOLICITAÇÃO FOR ENVIADA AO ARQUIVO TECNICO
			//verifica a sequencia dos itens
			$sql = "SELECT item_pedido FROM ".DATABASE.".solicitacao_documentos_detalhes ";			
			$sql .= "WHERE solicitacao_documentos_detalhes.reg_del = 0 ";
			$sql .= "AND solicitacao_documentos_detalhes.id_solicitacao_documento = '".$id_solicitacao_documento."' ";
			$sql .= "ORDER BY item_pedido DESC LIMIT 1 ";
			
			$db->select($sql,'MYSQL',true);

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}
			
			//se tiver registro, pegar o ultima sequencia
			if($db->numero_registros>0)
			{
				$seq = $db->array_select[0];
				
				$item_pedido = intval($seq["item_pedido"])+1;
			}
			else
			{
				$item_pedido = 1;
			}
			
			//documento novo
			if(!$dados_form["tipodoc"])
			{
				//incluido em 15/12/2011
				if($dados_form["versao_documento"]=="")
				{
					$versao_documento = '0';
				}
				else
				{
					$versao_documento = $dados_form["versao_documento"];
				}
				
				//Se for DOC NOVO e não fornecido o numero_cliente
				if($dados_form["numero_cliente"]=="")
				{
					$numcli = "";		
				}
				else //Se for DOC NOVO e fornecido o numero_cliente
				{
					$numcli = maiusculas(addslashes(trim($dados_form["numero_cliente"])));
				}
			}
			else
			{
				$versao_documento = $dados_form["versao_documento"];
				
				//Verifica se foi informado o numero_cliente
				if($dados_form["numero_cliente"]=="")
				{
					$resposta->addAlert("ATENÇÃO: Para um documento existente, é necessário informar o Número Cliente.");
					
					$resposta->addScript("xajax.$('numero_cliente').focus();");
					
					return $resposta;
				}
			
				//Se for DOC EXISTENTE e fornecido o numero_cliente
				$numcli = maiusculas(addslashes(trim($dados_form["numero_cliente"])));
			}
			
			//insere os detalhes do pedido
			$isql = "INSERT INTO ".DATABASE.".solicitacao_documentos_detalhes ";
			$isql .= "(id_solicitacao_documento, item_pedido, id_disciplina, id_atividade, tipodoc, finalidade, tag, tag2, tag3, tag4, area, setor, obs, numero_cliente, id_formato, folhas, versao_documento, servico_id) ";
			$isql .= "VALUES ('". $id_solicitacao_documento . "', ";
			$isql .= "'". sprintf("%04d",$item_pedido). "', ";
			$isql .= "'". $dados_form["id_disciplina"]. "', ";
			$isql .= "'". $dados_form["id_atividade"]. "', ";
			$isql .= "'". $dados_form["tipodoc"]. "', ";
			$isql .= "'". $dados_form["finalidade"]. "', ";
			$isql .= "'". maiusculas(addslashes($dados_form["tag"])). "', ";
			$isql .= "'". maiusculas(addslashes($dados_form["tag2"])). "', ";
			$isql .= "'". maiusculas(addslashes($dados_form["tag3"])). "', ";
			$isql .= "'". maiusculas(addslashes($dados_form["tag4"])). "', ";		
			$isql .= "'". maiusculas(addslashes($dados_form["area"])). "', ";
			$isql .= "'". maiusculas(addslashes($dados_form["setor"])). "', ";
			$isql .= "'". maiusculas(addslashes(trim($dados_form["txt_obs"]))). "', ";
			$isql .= "'". $numcli. "', ";
			$isql .= "'". $dados_form["formato"]. "', ";
			$isql .= "'". $dados_form["folhas"]. "', ";			
			$isql .= "'". $versao_documento. "', ";
			$isql .= "'". $dados_form["servico"]. "') ";
			
			$db->insert($isql, 'MYSQL');

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}
		}
		else
		{
			$resposta->addAlert('Erro no pedido do documento!');	
		}
	}
	else
	{
		$resposta->addAlert("É necessário selecionar uma Disciplina.");	
	}

	$resposta->addScript("xajax_atualizatabela_itens(xajax.getFormValues('frm',true));");

	return $resposta;
}

function excluir($id)
{
	$resposta = new xajaxResponse();
			
	$db = new banco_dados;	
	
	$usql = "UPDATE ".DATABASE.".solicitacao_documentos ";
	$usql .="SET solicitacao_documentos.reg_del = 1, ";
	$usql .= "solicitacao_documentos.reg_who = '".$_SESSION['id_funcionario']."', ";
	$usql .= "solicitacao_documentos.data_del = '".date('Y-m-d')."' ";
	$usql .= "WHERE solicitacao_documentos.id_solicitacao_documento = '".$id."' ";
	$usql .= "AND solicitacao_documentos.reg_del = 0 ";
	
	$db->update($usql,'MYSQL');

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	else
	{
		$usql = "UPDATE ".DATABASE.".solicitacao_documentos_detalhes ";
		$usql .="SET solicitacao_documentos_detalhes.reg_del = 1, ";
		$usql .= "solicitacao_documentos_detalhes.reg_who = '".$_SESSION['id_funcionario']."', ";
		$usql .= "solicitacao_documentos_detalhes.data_del = '".date('Y-m-d')."' ";
		$usql .= "WHERE solicitacao_documentos_detalhes.id_solicitacao_documento = '".$id."' ";
		$usql .= "AND solicitacao_documentos_detalhes.reg_del = 0 ";
		
		$db->update($usql,'MYSQL');

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
		else
		{		
			$resposta->addAlert("Pedido excluído com sucesso!");
			
			$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm',true)); ");
		}
	}
	
	return $resposta;
}

function excluir_item($id_solicitacao_documentos_detalhe)
{
	$resposta = new xajaxResponse();

	$db = new banco_dados;
	
	$sql = 
	"SELECT * FROM ".DATABASE.".solicitacao_documentos_detalhes
	JOIN(
	  SELECT * FROM ".DATABASE.".numeros_interno WHERE numeros_interno.reg_del = 0
	) numeros_interno
	ON numeros_interno.id_numero_interno = solicitacao_documentos_detalhes.id_numero_interno
	LEFT JOIN(	
			SELECT id_numero_interno numero_dvm, count(*) as total_arquivos FROM ".DATABASE.".ged_arquivos	
			WHERE ged_arquivos.reg_del = 0	
		) ged_arquivos
	
		ON ged_arquivos.numero_dvm = solicitacao_documentos_detalhes.id_numero_interno
	  WHERE solicitacao_documentos_detalhes.reg_del = 0 
	  AND solicitacao_documentos_detalhes.id_solicitacao_documentos_detalhe = '".$id_solicitacao_documentos_detalhe."' ";
	
	$db->select($sql, 'MYSQL',true);
	
	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	
	$registro = $db->array_select[0];
	
	if(intval($registro['total_arquivos']) > 0)
	{
		$resposta->addAlert("Já existe documentos carregados neste número.");
	}
	else
	{
		//Exclui o Detalhe do pedido, o Número Interno e o Número Cliente
		$usql ="UPDATE ".DATABASE.".solicitacao_documentos_detalhes SET ";
		$usql .= "solicitacao_documentos_detalhes.reg_del = 1, ";
		$usql .= "solicitacao_documentos_detalhes.reg_who = '".$_SESSION['id_funcionario']."', ";
		$usql .= "solicitacao_documentos_detalhes.data_del = '".date('Y-m-d')."' ";
		$usql .= "WHERE solicitacao_documentos_detalhes.id_solicitacao_documentos_detalhe = '".$id_solicitacao_documentos_detalhe."' ";
		$usql .= "AND solicitacao_documentos_detalhes.reg_del = 0 ";
		
		$db->update($usql, 'MYSQL');

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}

		$usql ="UPDATE ".DATABASE.".numeros_interno SET ";
		$usql .= "numeros_interno.reg_del = 1, ";
		$usql .= "numeros_interno.reg_who = '".$_SESSION['id_funcionario']."', ";
		$usql .= "numeros_interno.data_del = '".date('Y-m-d')."' ";
		$usql .= "WHERE numeros_interno.id_numero_interno = '".$registro['id_numero_interno']."' ";
		$usql .= "AND numeros_interno.reg_del = 0 ";
		
		$db->update($usql, 'MYSQL');

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
		
		$resposta->addScript("xajax_atualizatabela_itens(xajax.getFormValues('frm',true));");
	}

	return $resposta;
}

function editar($id)
{
	$resposta = new xajaxResponse();
			
	$db = new banco_dados;	
	
	$id = explode("_",$id);
	
	if(intval($id[1]))
	{
		$sql = "SELECT * FROM ".DATABASE.".solicitacao_documentos ";
		$sql .= "WHERE solicitacao_documentos.reg_del = 0 ";
		$sql .= "AND solicitacao_documentos.id_solicitacao_documento = '".$id[1]."' ";
		
		$db->select($sql,'MYSQL',true);

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
		
		$regs0 = $db->array_select[0];
		
		$resposta->addScript("xajax_voltar(0);");
			
		$resposta->addScript("seleciona_combo('".$regs0['id_os']."', 'os'); ");
		
		$resposta->addScript("myTabbar.tabs('a20_').enable();");
			
		$resposta->addAssign("id_solicitacao_documento","value",$id[1]);
	}
	
	return $resposta;
}

function preenchedisciplina($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	$limp = "xajax.$('id_disciplina').length = null";
	
	$resposta->addScript($limp);
	
	$comb = "xajax.$('id_disciplina').options[xajax.$('id_disciplina').length] = new Option('SELECIONE','');";
	
	$array_dados = dados_os($dados_form["os"]);

	/*
	//PEGA A ULTIMA REVISÃO DA FASE 03 (EXECUÇÃO)
	$sql = "SELECT MAX(AFE_REVISA) AS ULT_REVISA FROM AFE010 WITH(NOLOCK) ";
	$sql .= "WHERE AFE010.D_E_L_E_T_ = '' ";
	$sql .= "AND AFE010.AFE_PROJET = '".sprintf("%010d",$array_dados['os'])."' ";
	$sql .= "AND AFE010.AFE_FASE = '03' ";
	
	$db->select($sql, 'MSSQL', true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	
	$regs_ult_rev = $db->array_select[0];
	*/
	
	/*
	//PEGA A ULTIMA REVISãO DA FASE 07 (proj sem cronograma)
	$sql = "SELECT MAX(AFE_REVISA) AS ULT_REVISA FROM AFE010 WITH(NOLOCK) ";
	$sql .= "WHERE AFE010.D_E_L_E_T_ = '' ";
	$sql .= "AND AFE010.AFE_PROJET = '".sprintf("%010d",$array_dados['os'])."' ";
	$sql .= "AND AFE010.AFE_FASE = '07' ";
	
	$db->select($sql, 'MSSQL', true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	
	$regs_ult_rev_cron = $db->array_select[0];
	*/
	
	/*
	//PEGA A ULTIMA REVISãO DA FASE 07 (proj sem cronograma)
	$sql = "SELECT MAX(AFE_REVISA) AS ULT_REVISA FROM AFE010 WITH(NOLOCK) ";
	$sql .= "WHERE AFE010.D_E_L_E_T_ = '' ";
	$sql .= "AND AFE010.AFE_PROJET = '".sprintf("%010d",$array_dados['os'])."' ";
	$sql .= "AND AFE010.AFE_FASE = '09' ";
	
	$db->select($sql, 'MSSQL', true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	
	$regs_ult_rev_adm = $db->array_select[0];
	*/

	/*
	//Percorre as tarefas para compor as disciplinas
	$sql = "SELECT DISTINCT SUBSTRING(AF9_COMPOS,0,4) AS GRP FROM AF9010 WITH(NOLOCK), AFE010 WITH(NOLOCK) ";
	$sql .= "WHERE AF9010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF9_PROJET = '" . sprintf("%010d",$array_dados['os']) . "' ";	
	$sql .= "AND (AF9_REVISA = '".$regs_ult_rev["ULT_REVISA"]."' ";
	$sql .= "OR AF9_REVISA = '".$regs_ult_rev_cron["ULT_REVISA"]."' ";
	$sql .= "OR AF9_REVISA = '".$regs_ult_rev_adm["ULT_REVISA"]."') ";
	$sql .= "AND AF9_COMPOS <> '' ";
	$sql .= "AND AFE010.D_E_L_E_T_ = '' ";
	$sql .= "AND AFE_PROJET = AF9_PROJET ";
	$sql .= "AND AFE_REVISA = AF9_REVISA ";
	
	$db->select($sql, 'MSSQL', true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	
	$array_tarefas = $db->array_select;

	*/
	$sql = "SELECT * FROM ".DATABASE.".setores ";
	//$sql .= "WHERE abreviacao = '".trim($regs2["GRP"])."' ";
	$sql .= "WHERE setores.reg_del = 0 ";
	
	$db->select($sql, 'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}

	$array_tarefas = $db->array_select;

	foreach($array_tarefas as $regs2)
	{
		/*
		$sql = "SELECT * FROM ".DATABASE.".setores ";
		$sql .= "WHERE abreviacao = '".trim($regs2["GRP"])."' ";
		$sql .= "AND setores.reg_del = 0 ";
		
		$db->select($sql, 'MYSQL',true);

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
		
		$cont_setor = $db->array_select[0];
		*/

		$comb .= "xajax.$('id_disciplina').options[xajax.$('id_disciplina').length] = new Option('".trim(str_replace("'","",$regs2["setor"]))."', '".$regs2["id_setor"]."');"; 
				
	}

	$resposta->addScript($comb);
	
	$resposta->addAssign("tag","value",$array_dados['titulo1']);
	
	$resposta->addAssign("tag2","value",$array_dados['titulo2']);	

	return $resposta;
}

function preenchetarefas($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	$limp = "xajax.$('id_atividade').length = null";
	
	$resposta->addScript($limp);
	
	$comb = "xajax.$('id_atividade').options[xajax.$('id_atividade').length] = new Option('SELECIONE','');";
	
	$array_dados = dados_os($dados_form["os"]);	
	
	//Seleciona o setor
	$sql = "SELECT * FROM ".DATABASE.".setores ";
	$sql .= "WHERE setores.id_setor = '".$dados_form["id_disciplina"]."' ";
	$sql .= "AND setores.reg_del = 0 ";
	
	$db->select($sql, 'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	
	$regs_setor = $db->array_select[0];	
	
	/*
	//PEGA A REVISÃO ATUAL DO PROJETO
	$sql = "SELECT * FROM AF8010 WITH(NOLOCK) ";
	$sql .= "WHERE AF8010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF8010.AF8_PROJET = '".sprintf("%010d",$array_dados['os'])."' ";
		
	$db->select($sql, 'MSSQL', true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	
	$regs_atual = $db->array_select[0];
	*/
	
	if($db->numero_registros_ms > 0 || TRUE)
	{
		/*	
		//PEGA A ULTIMA REVISÃO DA FASE 03 (execução)
		$sql = "SELECT MAX(AFE_REVISA) AS ULT_REVISA FROM AFE010 WITH(NOLOCK) ";
		$sql .= "WHERE AFE010.D_E_L_E_T_ = '' ";
		$sql .= "AND AFE010.AFE_PROJET = '".sprintf("%010d",$array_dados['os'])."' ";
		$sql .= "AND AFE010.AFE_FASE = '".$regs_atual["AF8_FASE"] ."' ";
		
		$db->select($sql, 'MSSQL', true);

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
		
		$regs_ult_rev = $db->array_select[0];
		*/

		/*
		//Percorre as tarefas para compor os documentos
		$sql = "SELECT * FROM AF9010 WITH(NOLOCK) ";
		$sql .= "WHERE AF9010.D_E_L_E_T_ = '' ";
		$sql .= "AND AF9_PROJET = '" . sprintf("%010d",$array_dados['os']) . "' ";		
		$sql .= "AND AF9_REVISA = '".$regs_atual["AF8_REVISA"]."' ";
		$sql .= "AND AF9_COMPOS LIKE '".$regs_setor["abreviacao"]."%' ";
		$sql .= "ORDER BY AF9_TAREFA, AF9_DESCRI ";
		
		$db->select($sql, 'MSSQL', true);

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
		*/

		$sql = "SELECT * FROM ".DATABASE.".atividades ";
		//$sql .= "WHERE atividades.codigo = '" . trim($regs_tarefas["AF9_COMPOS"]). "' ";
		$sql .= "WHERE atividades.cod = '" . $dados_form["id_disciplina"]. "' ";
		$sql .= "AND atividades.reg_del = 0 ";
		$sql .= "AND solicitacao = '1' "; 
	
		$db->select($sql, 'MYSQL',true);

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
		
		$array_tarefas = $db->array_select;
		
		foreach($array_tarefas as $regs_tarefas)
		{
			/*
			$sql = "SELECT * FROM ".DATABASE.".atividades ";
			$sql .= "WHERE atividades.codigo = '" . trim($regs_tarefas["AF9_COMPOS"]). "' ";
			$sql .= "AND atividades.reg_del = 0 ";
			$sql .= "AND solicitacao = '1' "; 
		
			$db->select($sql, 'MYSQL',true);

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}
			*/
			
			//if($db->numero_registros > 0)
			//{
				//$reg_atividade = $db->array_select[0];
			
				//$comb .= "xajax.$('id_atividade').options[xajax.$('id_atividade').length] = new Option('".trim(str_replace("'","",$regs_tarefas["AF9_TAREFA"]))."-".trim(str_replace("'","",$regs_tarefas["AF9_DESCRI"]))."', '". $reg_atividade["id_atividade"]."');"; 
			
				$comb .= "xajax.$('id_atividade').options[xajax.$('id_atividade').length] = new Option('".trim(str_replace("'","",$regs_tarefas["codigo"]))."-".trim(str_replace("'","",$regs_tarefas["descricao"]))."', '". $reg_atividade["id_atividade"]."');";
			//}
		}
		
		$resposta->addScript($comb);		
	}

	return $resposta;
}

function formato($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	$array_dados = dados_os($dados_form["os"]);
	
	//Seleciona a atividade
	$sql = "SELECT * FROM ".DATABASE.".atividades ";
	$sql .= "WHERE atividades.id_atividade = '" . $dados_form["id_atividade"]. "' ";
	$sql .= "AND atividades.reg_del = 0 ";
	
	$db->select($sql, 'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	
	$reg_atividade = $db->array_select[0];
	
	/*
	//PEGA A ULTIMA REVISÃO DA FASE 03 (EXECUÇÃO)
	$sql = "SELECT MAX(AFE_REVISA) AS ULT_REVISA FROM AFE010 WITH(NOLOCK) ";
	$sql .= "WHERE AFE010.D_E_L_E_T_ = '' ";
	$sql .= "AND AFE010.AFE_PROJET = '".sprintf("%010d",$array_dados['os'])."' ";
	$sql .= "AND AFE010.AFE_FASE = '03' ";
	
	$db->select($sql, 'MSSQL', true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	
	$regs_ult_rev = $db->array_select[0];
	*/
	/*
	//PEGA A ULTIMA REVISÃO DA FASE 07 (PROJ SEM CRONOGRAMA)
	$sql = "SELECT MAX(AFE_REVISA) AS ULT_REVISA FROM AFE010 WITH(NOLOCK) ";
	$sql .= "WHERE AFE010.D_E_L_E_T_ = '' ";
	$sql .= "AND AFE010.AFE_PROJET = '".sprintf("%010d",$array_dados['os'])."' ";
	$sql .= "AND AFE010.AFE_FASE = '07' ";
	
	$db->select($sql, 'MSSQL', true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	
	$regs_ult_rev_cron = $db->array_select[0];
	*/
	
	/*
	//PEGA A ULTIMA REVISÃO DA FASE 09 (PROJ ADM)
	$sql = "SELECT MAX(AFE_REVISA) AS ULT_REVISA FROM AFE010 WITH(NOLOCK) ";
	$sql .= "WHERE AFE010.D_E_L_E_T_ = '' ";
	$sql .= "AND AFE010.AFE_PROJET = '".sprintf("%010d",$array_dados['os'])."' ";
	$sql .= "AND AFE010.AFE_FASE = '09' ";
	
	$db->select($sql, 'MSSQL', true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	
	$regs_ult_rev_adm = $db->array_select[0];
	*/		
	
	/*
	//Seleciona a tarefa
	$sql = "SELECT AF9_UM FROM AF9010 WITH(NOLOCK), AFE010 WITH(NOLOCK) ";
	$sql .= "WHERE AF9010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF9_PROJET = '" . sprintf("%010d",$array_dados['os']) . "' ";	
	$sql .= "AND (AF9_REVISA = '".$regs_ult_rev["ULT_REVISA"]."' ";
	$sql .= "OR AF9_REVISA = '".$regs_ult_rev_cron["ULT_REVISA"]."' ";
	$sql .= "OR AF9_REVISA = '".$regs_ult_rev_adm["ULT_REVISA"]."') ";
	$sql .= "AND AF9_COMPOS = '".$reg_atividade["codigo"]."' ";
	$sql .= "AND AFE010.D_E_L_E_T_ = '' ";
	$sql .= "AND AFE_PROJET = AF9_PROJET ";
	$sql .= "AND AFE_REVISA = AF9_REVISA ";
	
	$db->select($sql, 'MSSQL', true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	
	$regs_formato = $db->array_select[0];
	*/
	
	//Seleciona o formato
	$sql = "SELECT * FROM ".DATABASE.".formatos ";
	//$sql .= "WHERE formatos.formato = '" . trim($regs_formato["AF9_UM"]). "' ";
	$sql .= "WHERE formatos.reg_del = 0 ";
	
	$db->select($sql, 'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	
	$reg_form = $db->array_select[0];
	
	$resposta->addScript("seleciona_combo('" . $reg_form["id_formato"] . "', 'formato');");
	
	return $resposta;
}

$conf = new configs();

$xajax->registerFunction("atualizatabela");
$xajax->registerFunction("atualizatabela_itens");
$xajax->registerFunction("voltar");
$xajax->registerFunction("enviar");
$xajax->registerFunction("editar");
$xajax->registerFunction("inserir");
$xajax->registerFunction("excluir");
$xajax->registerFunction("excluir_item");
$xajax->registerFunction("preenchetarefas");
$xajax->registerFunction("preenchedisciplina");
$xajax->registerFunction("formato");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","tab();");
?>

<script src="<?php echo ROOT_WEB.'/includes/' ?>validacao.js"></script>

<script src="<?php echo ROOT_WEB.'/includes/' ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script src="<?php echo ROOT_WEB.'/includes/' ?>datetimepicker/datetimepicker_css.js"></script>

<script language="javascript">

function habilita(status)
{
	if(status) //existente
	{
		document.forms["frm"].finalidade.disabled = false;
		
		document.forms["frm"].versao_documento.disabled = false;
	}
	else
	{
		document.forms["frm"].finalidade.disabled = true;
		
		document.forms["frm"].versao_documento.disabled = true;
		
		document.forms["frm"].versao_documento.value = '0';
	}
}

//OK
function tab()
{
	myTabbar = new dhtmlXTabBar("my_tabbar");
	
	function sel_tab(idNew,idOld)
	{		
		//ativa quando seleciona a tab		
		switch(idNew)
		{
			case "a10_":

				if(idOld=='a20_')
				{
					xajax_voltar();
					
					xajax_atualizatabela(xajax.getFormValues('frm',true));					
					
					document.getElementById('btninserir_itens').disabled = 'disabled';
				}
															
			break;
			
			case "a20_":

				xajax_preenchedisciplina(xajax.getFormValues('frm',true));
				
				xajax_atualizatabela_itens(xajax.getFormValues('frm',true));
				
			break;
		}
		
		return true; // allow selection	
	}
	
	myTabbar.attachEvent("onSelect", sel_tab);
	
	myTabbar.addTab("a10_", "solicitacao_documentos", null, null, true);
	myTabbar.addTab("a20_", "Documentos");

	myTabbar.tabs("a10_").attachObject("a10");
	myTabbar.tabs("a20_").attachObject("a20");
	
	myTabbar.enableAutoReSize(true);
	
	myTabbar.tabs('a20_').disable(true);
}

function grid(tabela, autoh, height, xml)
{
	mygrid = new dhtmlXGridObject(tabela);
	
	mygrid.enableAutoHeight(autoh,height);
	
	mygrid.enableRowsHover(true,'cor_mouseover');	
	
	switch (tabela)
	{
	 	case 'solicitacao':

			function doOnRowSelected(id,col)
			{
				if(col<=3)
				{
					xajax_editar(id);
				}
			}
					
			mygrid.setHeader("Nº,OS,data,status,D");
			mygrid.setInitWidths("50,*,100,100,30");
			mygrid.setColAlign("left,left,left,left,center");
			mygrid.setColTypes("ro,ro,ro,ro,ro");
			mygrid.setColSorting("str,str,str,str,str");
		
			mygrid.attachEvent("onRowSelect",doOnRowSelected);
		
		break;
		
	 	case 'itens':
		
			mygrid.setHeader("Nº&nbsp;item,Disciplina,Nº&nbsp;Cliente,Título&nbsp;1,Título&nbsp;2,Título&nbsp;3,Título&nbsp;4,Fmt,Fls,Rev.,D");
			mygrid.setInitWidths("60,120,100,140,140,140,140,35,35,35,30");
			mygrid.setColAlign("left,left,left,left,left,left,left,center,center,center,center");
			mygrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
			mygrid.setColSorting("str,str,str,str,str,str,str,str,str,str,str,str,str");
		
		break;		
	}	
	
	mygrid.setSkin("dhx_skyblue");
    mygrid.enableMultiselect(true);
    mygrid.enableCollSpan(true);	
	mygrid.init();
	mygrid.loadXMLString(xml);
}

</script>

<?php

$array_os_values = NULL;
$array_os_output = NULL;

//$arrUsuariosLiberados = array(6, 17, 49, 689, 709, 909, 910, 978, 981, 871, 1213, 1061,1142);

//Modificação feita por carlos abreu em 11/05/2010
if(!in_array($_SESSION["id_funcionario"], $arrUsuariosLiberados))
{
	$sql = "SELECT ordem_servico.id_os, ordem_servico.os, ordem_servico.descricao FROM ".DATABASE.".ordem_servico, ".DATABASE.".ordem_servico_status, ".DATABASE.".os_x_funcionarios "; //, ".DATABASE.".numeros_interno - retirado devido a impressão do escopo
	$sql .= "WHERE ordem_servico.id_os = os_x_funcionarios.id_os ";
	$sql .= "AND ordem_servico.reg_del = 0 ";
	$sql .= "AND ordem_servico_status.reg_del = 0 ";
	$sql .= "AND os_x_funcionarios.reg_del = 0 ";
	$sql .= "AND os_x_funcionarios.id_funcionario = '" . $_SESSION["id_funcionario"] . "' ";
	$sql .= "AND ordem_servico.id_os_status = ordem_servico_status.id_os_status ";
	$sql .= "AND (ordem_servico_status.id_os_status IN (1,14,16) OR ordem_servico.os LIKE '3311')";
}
else
{
	$sql = "SELECT ordem_servico.id_os, ordem_servico.os, ordem_servico.descricao FROM ".DATABASE.".ordem_servico, ".DATABASE.".ordem_servico_status ";
	$sql .= "WHERE ordem_servico.id_os_status = ordem_servico_status.id_os_status ";
	$sql .= "AND ordem_servico.reg_del = 0 ";
	$sql .= "AND ordem_servico_status.reg_del = 0 ";
	//$sql .= "AND ordem_servico.os < 50000 ";	
}

/*
if($_SESSION["id_funcionario"]!=6 || $_SESSION["id_funcionario"]!=978)
{
	$sql .= "AND ordem_servico.os > 1700 ";
}
*/

$sql .= "GROUP BY ordem_servico.id_os ";
$sql .= "ORDER BY ordem_servico.os ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	$resposta->addAlert($db->erro);
}

foreach ($db->array_select as $cont)
{
	$array_os[$cont["id_os"]] = sprintf("%05d",$cont["os"]) . " - " . $cont["descricao"];
}

$sql = "SELECT id_formato, formato FROM ".DATABASE.".formatos ";
$sql .= "WHERE formatos.reg_del = 0 ";
$sql .= "ORDER BY formato ";

$db->select($sql, 'MYSQL',true);

if($db->erro!='')
{
	$resposta->addAlert($db->erro);
}

foreach ($db->array_select as $cont_fmt)
{
	$array_formatos_values[] = $cont_fmt["id_formato"];
	$array_formatos_output[] = $cont_fmt["formato"];
}

//Re-ordena as OS's
asort($array_os);

$array_os_values[] = "";
$array_os_output[] = "SELECIONE";

//Percorre o array de OS's
foreach($array_os as $chave=>$valor)
{
	$array_os_values[] = $chave;
	$array_os_output[] = $valor;
}

$smarty->assign("option_os_values",$array_os_values);
$smarty->assign("option_os_output",$array_os_output);

$smarty->assign("option_formatos_values",$array_formatos_values);
$smarty->assign("option_formatos_output",$array_formatos_output);

$smarty->assign("revisao_documento","V14");

$smarty->assign("campo",$conf->campos('solicitacao_documentos'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("nome_funcionario",$_SESSION["nome_usuario"]);

$smarty->assign("nome_formulario","SOLICITAÇÃO DE DOCUMENTOS");

$smarty->assign("classe",CSS_FILE);

$smarty->display('solicitacao_documentos.tpl');
?>