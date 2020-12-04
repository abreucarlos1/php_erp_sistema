<?php
/*
		Formulário de documentos de contratação
		
		Criado por Carlos Eduardo
		
		local/Nome do arquivo:
		../rh/documentos_contratacao.php
		
		Versão 0 --> VERSÃO INICIAL
		Versão 1 --> Inclusão dos campos reg_del nas consultas - 28/11/2017 - Carlos Abreu
		Versão 2 --> Layout responsivo - Carlos Eduardo - 05/02/2018
*/
header('X-UA-Compatible: IE=edge');
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

require_once(INCLUDE_DIR."antiInjection.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(587))
{
	nao_permitido();
}

function voltar()
{
	$resposta = new xajaxResponse();
	
	$resposta->addScript("document.getElementById('btninserir').disabled=true;");
	
	$resposta->addEvent("btninserir","onclick","xajax_atualizar(xajax.getFormValues('frm')); ");
	
	$resposta->addEvent("btnvoltar", "onclick", "history.back();");

	return $resposta;

}

function atualizatabela($filtro)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	$sql_filtro = "";
	
	$sql_texto = "";
	
	if($filtro!="")
	{
	    $sql_texto = str_replace('  ', ' ', AntiInjection::clean($filtro));
	    
	    $sql_texto = str_replace(' ', '%', '%'.$sql_texto.'%');
	    
	    $sql_filtro = "AND (f.funcionario LIKE '".$sql_texto."' ";
	    $sql_filtro .= "OR sa.setor_aso LIKE '".$sql_texto."' ";
	    $sql_filtro .= "OR s. tipo_contrato LIKE '".$sql_texto."' ";
	    $sql_filtro .= "OR f.situacao LIKE '".$sql_texto."') ";
	}
	
	$sql = "SELECT 
		f.funcionario, pc.numero_contrato, pc.data_inicio, pc.data_fim, ef.empresa_func, ef.empresa_cnpj, ef.empresa_cidade, ef.empresa_estado, ef.empresa_endereco, 
		ef.empresa_bairro, id_tipo_contratacao, sa.setor_aso, salario_mensalista, salario_hora, pc.id_contrato, ef.id_empfunc, f.id_funcionario
		FROM ".DATABASE.".funcionarios f
			JOIN ".DATABASE.".salarios s ON s.id_salario = f.id_salario AND s.reg_del = 0
			LEFT JOIN ".DATABASE.".empresa_funcionarios ef ON (ef.empresa_socio = f.id_funcionario)
			LEFT JOIN (SELECT id_funcionario, MAX(id_contrato) id_contrato, numero_contrato, data_inicio, data_fim, id_tipo_contratacao FROM ".DATABASE.".pj_contratos WHERE reg_del = 0 GROUP BY id_funcionario, numero_contrato, data_inicio, data_fim, id_tipo_contratacao) pc ON pc.id_funcionario = f.id_funcionario
			LEFT JOIN ".DATABASE.".setor_aso sa ON sa.id_setor_aso = f.id_setor_aso AND sa.reg_del = 0
		WHERE f.situacao NOT IN('DESLIGADO')
			AND f.reg_del = 0 ".$sql_filtro."
		ORDER BY
			f.funcionario";
	
	$db->select($sql,'MYSQL',true);
	
	if ($db->erro != '')
	{
		$resposta->addAlert("Não foi possível fazer a seleção." . $sql_os);
	}

	$conteudo = "";
	
	$xml = new XMLWriter();
	$xml->openMemory();
	$xml->setIndent(false);
	$xml->startElement('rows');
	
	foreach($db->array_select as $reg)
	{
		$xml->startElement('row');
			$xml->writeAttribute('id', $reg["id_contrato"]);
		    $xml->writeElement('cell', $reg["id_contrato"]);
		    $xml->writeElement('cell', $reg["funcionario"]);
			$xml->writeElement('cell', mysql_php($reg["data_inicio"]));
			$xml->writeElement('cell', mysql_php($reg["data_fim"]));
			$xml->writeElement('cell', $reg["setor_aso"]);
			
			$imgArquivos = '<span class="icone icone-detalhes cursor" onclick=showModalAnexos('.$reg["id_contrato"].');></span>';
			$xml->writeElement('cell', $imgArquivos);
			
			$imgIos = '<span class="icone icone-arquivo-pdf cursor" onclick=window.open("./documentos_contratacao_outros_pdf.php?idFuncionario='.$reg["id_funcionario"].'&parte=ios","_blank");></span>';
			$xml->writeElement('cell', $imgIos);
			
			$imgSeguro = '<span class="icone icone-arquivo-pdf cursor" onclick=window.open("./documentos_contratacao_outros_pdf.php?parte=seguro","_blank");></span>';
			$xml->writeElement('cell', $imgSeguro);
			
			$imgEpi = '<span class="icone icone-arquivo-pdf cursor" onclick=window.open("./documentos_contratacao_outros_pdf.php?idFuncionario='.$reg["id_funcionario"].'&parte=epi","_blank");></span>';
			$xml->writeElement('cell', $imgEpi);
			
			$imgSeguro = '<span class="icone icone-arquivo-pdf cursor" onclick=window.open("./documentos_contratacao_outros_pdf.php?parte=termo_ti&idFuncionario='.$reg['id_funcionario'].'","_blank");></span>';
			$xml->writeElement('cell', $imgSeguro);
			
			if (!empty($reg['id_contrato']) && !empty($reg['id_tipo_contratacao']) && !empty($reg['id_empfunc']))
			{
				$imgContrato = '<span class="icone icone-arquivo-pdf cursor" onclick=window.open("./documentos_contratacao_pdf.php?idContrato='.$reg["id_contrato"].'&parte=contrato","_blank");></span>';
				$imgAnexo1 = '<span class="icone icone-arquivo-pdf cursor" onclick=window.open("./documentos_contratacao_pdf.php?idContrato='.$reg["id_contrato"].'&parte=anexo1","_blank");></span>';
			}
			else
			{
				$imgContrato = '';
				$imgAnexo1 = '';
			}
			
			$xml->writeElement('cell', $imgContrato);
			$xml->writeElement('cell', $imgAnexo1);
		$xml->endElement();	
	}
	
	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);

	$resposta->addScript("grid('divListaContratados', true, '360', '".$conteudo."');");
	$resposta->addScript("hideLoader();");
	
	return $resposta;
}

function editar($id_contrato)
{
	$resposta = new xajaxResponse();

	$resposta->addScript("document.getElementById('frm').reset()");
	$resposta->addScript("seleciona_combo(954, 'testemunhas');seleciona_combo(819, 'testemunhas');");
	
	$db = new banco_dados();

	$sql = "SELECT 
				pc.numero_contrato, pc.id_funcionario, pc.id_contrato, pc.testemunha_1, pc.testemunha_2, ef.id_empfunc,
				pcd_id_contrato, pc.id_tipo_contratacao, pcd_titulo				
			FROM 
				".DATABASE.".pj_contratos pc ";
	$sql .= "JOIN ".DATABASE.".funcionarios f ON f.id_funcionario = pc.id_funcionario AND f.reg_del = 0 ";
	$sql .= "LEFT JOIN ".DATABASE.".pj_contratos_x_documentos pcd ON pcd_id_contrato = pc.id_contrato AND pc.reg_del = 0 ";
	$sql .= "LEFT JOIN ".DATABASE.".empresa_funcionarios ef ON (f.id_empfunc = ef.id_empfunc OR ef.empresa_socio = f.id_funcionario AND ef.reg_del = 0) ";
	$sql .= "WHERE pc.id_contrato = '".$id_contrato."' ";
	$sql .= "AND pc.reg_del = 0 ";

	$db->select($sql,'MYSQL',true);
	
	if ($db->erro != '')
	{
		$resposta->addAlert("Erro na conexão com o banco de dados.");
	}

	if($db->numero_registros > 0)
	{
		$regs = $db->array_select[0];
	
		$anoContrato = substr($regs['numero_contrato'], -4);
		$numContrato = str_replace($anoContrato, '', $regs['numero_contrato']);
		
		$resposta->addScript("seleciona_combo('".$regs["id_funcionario"]."','funcionario');");
		$resposta->addAssign("id_contrato","value",$regs["id_contrato"]);
		
		$resposta->addAssign("contratoColaboradorNumero","value",$numContrato);
		$resposta->addScript("seleciona_combo('".$anoContrato."','contratoColaboradorAno');");
		
		$resposta->addScript("desseleciona_combo('testemunhas');");
		$resposta->addScript("desseleciona_combo('empresa_funcionario');");
		
		if (!empty($regs['testemunha_1']) && !empty($regs['testemunha_2']))
		{
    		$resposta->addScript("seleciona_combo('".$regs['testemunha_1']."','testemunhas');");
    		$resposta->addScript("seleciona_combo('".$regs['testemunha_2']."','testemunhas');");
		}
		else
		{
		    $resposta->addScript("selecionarPadrao();");
		}
		
		if (!empty($regs['id_empfunc']))
			$resposta->addScript("seleciona_combo('".$regs['id_empfunc']."','empresa_funcionario');");
		
		$resposta->addScript("verificarTestemunhas();");
		
		foreach($db->array_select as $regs)
		{
			$idCheckbox = minusculas(str_replace(' ', '_', $regs['pcd_titulo']));
			$resposta->addAssign($idCheckbox,"checked",true);	
		}
		$resposta->addAssign("tipo_contrato_".$regs["id_tipo_contratacao"],"checked", true);
		
		$resposta->addScript("document.getElementById('btninserir').disabled=false;");
		$resposta->addEvent("btnvoltar", "onclick", "xajax_voltar();");	
	}
	else
	{
		$resposta->addAlert("CADASTRO INCOMPLETO, FAVOR VERIFICAR: Cadastro de funcionários, número do contrato e datas de inicio e fim de contrato.");	
	}

	return $resposta;
}

function salvar($dados_form)
{
	$resposta = new xajaxResponse();

	$db = new banco_dados;
	
	$funcionario = $dados_form['funcionario'];
	$tipoContrato = $dados_form['tipo_contrato'];
	$testemunhas = $dados_form['testemunhas'];
	$numContrato = $dados_form['contratoColaboradorNumero'];
	$anoContrato = $dados_form['contratoColaboradorAno'];
	
	if (!empty($funcionario))
	{
		//Buscando o número do contrato com outros colaboradores para ver se não estão cadastrando errado
		$sql = "SELECT
					numero_contrato, id_empfunc, salario_mensalista, salario_hora, salario_clt, pc.data_inicio, pc.data_fim,
    				id_disciplina, id_local_trabalho, id_funcionario
				FROM
					".DATABASE.".pj_contratos pc
					JOIN ".DATABASE.".funcionarios f ON f.id_funcionario = pc.id_funcionario AND f.reg_del = 0  
					JOIN ".DATABASE.".salarios s ON s.id_salario = f.id_salario AND s.reg_del = 0
				WHERE
					pc.reg_del = 0
					AND f.id_funcionario = ".$funcionario;
		
		$db->select($sql, 'MYSQL',true);
		
		if (intval($db->array_select[0]['salario_hora']) > 0)
		{
			$valorContrato 	= $db->array_select[0]['salario_hora'];
		}
		else if (intval($db->array_select[0]['salario_mensalista']) > 0)
		{
			$valorContrato 	= $db->array_select[0]['salario_mensalista'];
		}
		else
		{
			$valorContrato 	= $db->array_select[0]['salario_clt'];
		}

		$usql = "UPDATE ".DATABASE.".pj_contratos SET
					id_empresa = ".$db->array_select[0]['id_empfunc'].",
					valor_contrato = ".$valorContrato.",
					id_disciplina = ".$db->array_select[0]['id_disciplina'].",
					id_local_trabalho = ".$db->array_select[0]['id_local_trabalho'].",
					testemunha_1 = '".$testemunhas[0]."',
					testemunha_2 = '".$testemunhas[1]."',
					id_tipo_contratacao = '".$tipoContrato."',
					numero_contrato = '".$numContrato.$anoContrato."'
				WHERE
					id_funcionario = ".$db->array_select[0]['id_funcionario']."
					AND id_contrato = ".$dados_form["id_contrato"];
		
		$db->update($usql, 'MYSQL');
		
		if ($db->erro != '')
		{
			$resposta->addAlert('Houve uma falha ao tentar gravar o contrato. '.$db->erro);
		}
		else
		{
			//isql padrão
			$isql = "INSERT INTO ".DATABASE.".pj_contratos_x_documentos ";
			$isql .= "(pcd_id_contrato, pcd_titulo) VALUES ";
			
			$isqlCompl = '';
			
			$virgula = '';

			//Isql dos documentos selecionados
			if (count($dados_form['documentos']) > 0)
			{
				foreach($dados_form['documentos'] as $titulo => $doc)
				{
					$titulo = maiusculas(str_replace('_', ' ', $titulo));

					
					$sql = "SELECT 
							pcd_titulo 
						FROM 
							".DATABASE.".pj_contratos_x_documentos 
						WHERE 
							reg_del = 0 
							AND pcd_id_contrato = ".$dados_form['id_contrato']." 
							AND pcd_titulo = '".$titulo."' 
							AND pcd_arquivo IS NOT NULL";
				
					$db->select($sql, 'MYSQL');
					
					if ($db->numero_registros == 0)
					{	
						$isqlCompl .= $virgula."(".$dados_form['id_contrato'].", '".$titulo."')";
						$virgula = ',';
					}
				}
			}
			
			//isql do tipo de contrato selecionado
			if (!empty($dados_form['tipo_contrato']))
			{
				$isqlCompl .= $virgula."(".$dados_form['id_contrato'].", '".$dados_form['tipo_contrato']."')";
			}
			
			//caso tenha passado em branco, não fazer nada
			if (!empty($isqlCompl))
			{
				$usql = "UPDATE ".DATABASE.".pj_contratos_x_documentos SET ";
				$usql .= "reg_del = 1, ";
				$usql .= "reg_who = '".$_SESSION['id_funcionario']."', ";
				$usql .= "data_del = '".date('Y-m-d')."' ";
				$usql .= "WHERE pcd_arquivo IS NULL ";
				$usql .= "AND pcd_id_contrato = ".$dados_form['id_contrato'];
				
				$db->update($usql, 'MYSQL');
				
				$isql .= $isqlCompl;
				$db->insert($isql, 'MYSQL');
				
				if ($db->erro != '')
				{
					$resposta->addAlert('Houve uma falha ao tentar gravar o contrato. '.$db->erro);
				}
				
				$resposta->addAlert('Registro salvo corretamente!');
				$resposta->addScript('window.location="./documentos_contratacao.php";');
			}
			else
			{
				$resposta->addAlert('Por favor, selecione os documentos para realizar a operação!');
			}
		}
	}
	return $resposta;
}	

function preencheCombo($id, $nomecombo, $controle='', $selecionado='' )
{
	$resposta = new xajaxResponse();

	$db = new banco_dados;
	
	switch ($nomecombo)
	{
		case "DISCIPLINA":
			$sql = "SELECT * FROM ".DATABASE.".atividades ";
			$sql .= "WHERE atividades.cod = '" . $id . "' ";
			$sql .= "AND atividades.reg_del = 0 ";
			$sql .= "ORDER BY atividades.descricao ";
				 
			$db->select($sql,'MYSQL',true);
			
			if ($db->erro != '')
			{
				$resposta->addAlert("Não foi possível selecionar as atividades!");
			}
			
			$resposta->addScript("combo_destino = document.getElementById('id_atividade');");
			
			$resposta->addScriptCall("limpa_combo('id_atividade')");		
			
			foreach($db->array_select as $reg_disciplina)
			{
				$resposta->addScript("combo_destino.options[combo_destino.length] = new Option('".$reg_disciplina["descricao"]."', '".$reg_disciplina["id_atividade"]."');");	
			}		
		
		break;
		
		case "CONTATO":
			$sql = "SELECT * FROM ".DATABASE.".contatos ";
			$sql .= "WHERE contatos.id_empresa_erp = '" . $id . "' ";
			$sql .= "AND contatos.reg_del = 0 ";
			$sql .= "ORDER BY contatos.nome_contato ";
				
			$db->select($sql,'MYSQL',true);
			
			if ($db->erro != '')
			{
				$resposta->addAlert("Não foi possível selecionar os contatos!");
			}
			
			foreach($db->array_select as $reg_contato)
			{				
				$matriz[$reg_contato["nome_contato"]] = $reg_contato["id_contato"];		
			}
			
			$resposta->addNewOptions($controle, $matriz, $selecionado);
			
		break;
	}	
	
	return $resposta;
}

function validar($dados_form)
{
	$resposta = new xajaxResponse();
	if (count($dados_form['testemunhas']) > 2)
	{
		$resposta->addAlert('Por favor, selecione apenas uma testemunha!');
	}
	
	return $resposta;
}

function listaArquivos($idContrato)
{
	$db = new banco_dados();
	$resposta = new xajaxResponse();

	//Tipos de contrato
	$sql = "SELECT * FROM ".DATABASE.".pj_tipo_contratacao ";
	$sql .= "WHERE pj_tipo_contratacao.reg_del = 0 ";
	
	$arrTemp = array();
	
	$db->select($sql, 'MYSQL', function($reg, $i) use(&$arrTemp){
		$arrTemp[$reg['id_tipo_contratacao']] = $reg['tipo_contratacao'];
	});

	$sql = "SELECT * FROM ".DATABASE.".pj_contratos_x_documentos ";
	$sql .= "WHERE pj_contratos_x_documentos.reg_del = 0 ";
	$sql .= "AND pj_contratos_x_documentos.pcd_id_contrato = ".$idContrato;
	
	$db->select($sql, 'MYSQL', true);
	
	$xml = new XMLWriter();
	$xml->openMemory();
	$xml->setIndent(false);
	$xml->startElement('rows');
	
	foreach($db->array_select as $k => $reg)
	{
		$k++;
		if (empty($reg['pcd_arquivo']))
		{
			$conteudo = '&nbsp;';
			$conteudo ='<form style="margin:0;" name="frm_'.$k.'" id="frm_'.$k.'" action="upload_docs_contratacao.php" target="upload_target_'.$k.'" method="post" enctype="multipart/form-data" >';						
			$conteudo .='<iframe id="upload_target_'.$k.'" name="upload_target_'.$k.'" src="#" style="border:0px solid #fff;display:none;"></iframe>';
			$conteudo .='<span id="txtup_'.$k.'" >';
			$conteudo .='<input class="caixa" onchange=document.getElementById("frm_'.$k.'").submit(); name="myfile_'.$k.'" type="file" size="30" style="width:60%;" />&nbsp;&nbsp;';
			$conteudo .='</span>';
			$conteudo .='<input name="id_contrato" type="hidden" id="id_contrato" value="'.$reg["pcd_id_contrato"].'">';
			$conteudo .='<input name="pcd_id" type="hidden" id="pcd_id" value="'.$reg["pcd_id"].'">';
			$conteudo .='</form>';
		}
		else
		{
			$conteudo = '<span onclick=window.open("../includes/documento.php?documento='.$reg['pcd_arquivo'].'&caminho=DOCUMENTOS_FUNCIONARIOS&janela=yes","_blank"); class="cursor">VISUALIZAR</span>';
		}
		
		$titulo = array_key_exists($reg['pcd_titulo'], $arrTemp) ? $arrTemp[$reg['pcd_titulo']] : $reg['pcd_titulo'];
		
		$xml->startElement('row');
			$xml->writeAttribute('id', $reg["pcd_id"]);
			$xml->writeElement('cell', $titulo);
			$xml->writeElement('cell', $conteudo);			

			$imgExcluir = '<span class="icone icone-excluir cursor" onclick=if(confirm("Deseja&nbsp;excluir&nbsp;este&nbsp;item?"))xajax_excluirArquivo("'.$reg['pcd_id'].'","'.$reg['pcd_id_contrato'].'");></span>';

			$xml->writeElement('cell', $imgExcluir);
			
		$xml->endElement();
	}
	
	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);

	$resposta->addScript("grid('divListaArquivos', true, '230', '".$conteudo."');");
	
	return $resposta;	
}

function excluirArquivo($pcdId, $pcdIdContrato)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados();
	
	$usql = "UPDATE ".DATABASE.".pj_contratos_x_documentos SET ";
	$usql .= "reg_del = 1, ";
	$usql .= "reg_who = '".$_SESSION['id_funcionario']."', ";
	$usql .= "data_del = '".date('Y-m-d')."' ";
	$usql .= "WHERE pcd_id = ".$pcdId;
	
	$db->update($usql, 'MYSQL');
	
	if ($db->erro != '')
	{
		$resposta->addAlert('Houve uma falha ao tentar excluir o item!');
	}
	else
	{
		$resposta->addAlert('Item excluído corretamente!');
		$resposta->addScript('xajax_listaArquivos("'.$pcdIdContrato.'");');
	}
	
	return $resposta;
}

function preencheComboEpi($indice)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados();
	
	//$resposta->addScript("limpaCombo('selEPI[".indice."]');");
	$resposta->addScriptCall('addOption','selEPI['.$indice.']','NENHUM','');
	
	$sql = "SELECT * FROM ".DATABASE.".epi ";
	$sql .= "WHERE epi.reg_del = 0 ";
	$sql .= "AND epi.atual = 1 ";
	
	$db->select($sql, 'MYSQL', function($reg, $i) use(&$resposta, $indice){
		$resposta->addScriptCall('addOption','selEPI['.$indice.']',$reg['epi'],$reg['id_epi']);
	});
	
	return $resposta;
}

function salvarFichaEpi($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados();

	$usql = "UPDATE ".DATABASE.".funcionario_x_epi SET ";
	$usql .= "reg_del = 1, ";
	$usql .= "reg_who = '".$_SESSION['id_funcionario']."', ";
	$usql .= "data_del = '".date('Y-m-d')."' ";
	$usql .= "WHERE fxe_id_funcionario = ".$dados_form['id_funcionario'][0];
	
	$db->update($usql, 'MYSQL');	
		
	$isql = "INSERT INTO ".DATABASE.".funcionario_x_epi (fxe_id_epi, fxe_id_funcionario, fxe_qtd, fxe_data_entrega) VALUES ";
	
	$virgula = '';
	foreach($dados_form['selEPI'] as $k => $id)
	{
		if (!empty($dados_form['qtd'][$k]))
		{
			if (empty($dados_form['data'][$k]))
			{
				$dados_form['data'][$k] = $dados_form['data'][$k-1];
			}
			
			if (empty($dados_form['data'][$k]))
				continue;
			
			$isql .= $virgula."('".$id."', '".$dados_form['id_funcionario'][0]."', '".$dados_form['qtd'][$k]."', '".php_mysql($dados_form['data'][$k])."')";
			$virgula = ',';
		}
	}
	
	//só existirá virgula se pelo menos um epi for encontrado
	if ($virgula == ',')
	{
		$db->insert($isql, 'MYSQL');
		
		if ($db->erro != '')
			$resposta->addAlert('Houve uma falha ao tentar cadastrar os EPI\'s. '.$db->erro);
		else
		{
			$resposta->addAlert('Epi\'s cadatrados corretamente!');
			$resposta->addScript('divPopupInst.destroi();');
		}
	}
	
	return $resposta;
}

function showModalEPI($idFuncionario)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados();
	
	$options = '';
	
	$sql = "SELECT * FROM ".DATABASE.".epi ";
	$sql .= "WHERE epi.reg_del = 0 ";
	
	$db->select($sql, 'MYSQL', function($reg, $i) use(&$options){
		$options .= '<option value="'.$reg['id_epi'].'" sel="'.$reg['id_epi'].'">'.$reg['epi'].'</option>';
	});
	
	$html = '<form id="frmPDI" method="post"><table id="tabelaEpi">';
	$html .= '<input name="id_funcionario[0]" type="hidden" id="id_funcionario[0]" value="'.$idFuncionario.'">';

	$html .= '<tr><td><label class="labels">Data</label></td>'.
			'<td><label class="labels">Qtd.</label></td>'.
			"<td><label class='labels'>EPI</label></td></tr>";
	
	$indice = 0;
	
	$sql = "SELECT * FROM ".DATABASE.".funcionario_x_epi ";
	$sql .= "WHERE funcionario_x_epi.reg_del = 0 ";
	$sql .= "AND funcionario_x_epi.fxe_id_funcionario = ".$idFuncionario;
	
	$db->select($sql, 'MYSQL', function($reg, $i) use(&$resposta, &$html, $options, &$indice){
		$options = str_replace('sel="'.$reg['fxe_id_epi'].'"', 'selected="selected"', $options);
		
		$html .= '<tr id="tr_'.$i.'">'.
				'<td><input name="data['.$i.']" id="data['.$i.']" value="'.mysql_php($reg['fxe_data_entrega']).'" size="12" onKeyPress="transformaData(this, event);"></td>'.
				'<td><input name="qtd['.$i.']" id="qtd['.$i.']"  value="'.$reg['fxe_qtd'].'" size="12"></td>'.
				"<td><select name='selEPI[".$i."]' class='caixa' id='selEPI[".$i."]'>".$options."</select></td>".
				"<td><span class='icone icone-remover cursor' ref='".$i."' id='span[".$i."]' onclick='xajax_excluirEPIFunc(".$reg['fxe_id'].");' ></span></td></tr>";
		
		$indice++;
	});
	
	$html .= '<tr id="tr_last"><td><input type="button" value="Salvar Arquivo" onclick="xajax_salvarFichaEpi(xajax.getFormValues(\'frmPDI\'));" class="class_botao" /></td></tr></form>';
	
	$resposta->addScriptCall('modal', $html, '300_600', 'Ficha de EPI');
	
	$resposta->addScriptCall('criarLinha', $indice);
	
	return $resposta;
}

function excluirEPIFunc($idEpiFunc)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados();
	
	$usql = "UPDATE ".DATABASE.".funcionario_x_epi SET ";
	$usql .= "reg_del = 1, ";
	$usql .= "reg_who = '".$_SESSION['id_funcionario']."', ";
	$usql .= "data_del = '".date('Y-m-d')."' ";
	$usql .= "WHERE fxe_id = ".$idEpiFunc;
	
	$db->update($usql, 'MYSQL');
	
	if ($db->erro != '')
	{
		$resposta->addAlert('Houve uma falha ao tentar excluir o item. '.$db->erro);
	}
	else
	{
		$resposta->addAlert('Item excluído corretamente!');
	}
	
	return $resposta;
}

$xajax->registerFunction("voltar");

$xajax->registerFunction("editar");
$xajax->registerFunction("salvar");
$xajax->registerFunction("atualizatabela");
$xajax->registerFunction("preencheCombo");
$xajax->registerFunction("validar");
$xajax->registerFunction("listaArquivos");
$xajax->registerFunction("excluirArquivo");
$xajax->registerFunction("preencheComboEpi");
$xajax->registerFunction("salvarFichaEpi");
$xajax->registerFunction("showModalEPI");
$xajax->registerFunction("excluirEPIFunc");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","showLoader();xajax_atualizatabela('','1');selecionarPadrao();verificarTestemunhas();");

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script src="<?php echo INCLUDE_JS ?>/jquery/jquery.min.js"></script>

<script language="javascript">
function showModalAditamento()
{
	var html = 	'<form id="frmAditamento" name="frmAditamento">'+
					'<label class="labels">Nº Adit.</label>'+
					'<input type="text" name="numero_termo" id="numero_termo" class="caixa" size="4" />'+

					'<label class="labels">Prazo Meses</label>'+
					'<input type="text" name="prazo_meses" id="prazo_meses" class="caixa" size="4" /><br />'+

					'<label class="labels">Início em</label>'+
					'<input type="text" name="prazo_inicio" id="prazo_inicio" class="caixa" size="10" onKeyPress="transformaData(this, event);" onblur="verificaDataErro(this.value, \'prazo_inicio\');" />'+

					'<label class="labels">Fim em</label>'+
					'<input type="text" name="prazo_final" id="prazo_final" class="caixa" size="10" onKeyPress="transformaData(this, event);" onblur="verificaDataErro(this.value, \'prazo_final\');" /><br />'+

					'<label class="labels">Emitido em</label>'+
					'<input type="text" name="data_emissao" id="data_emissao" class="caixa" size="10" onKeyPress="transformaData(this, event);" onblur="verificaDataErro(this.value, \'prazo_inicio\');" /><br /><br />'+
					
					"<input type='button' class='class_botao' value='Gerar Arquivo' onclick=window.open('./documentos_contratacao_outros_pdf.php?parte=aditamento&idFuncionario='+document.getElementById('funcionario').value+'&prazo_meses='+document.getElementById('prazo_meses').value+'&prazo_inicio='+document.getElementById('prazo_inicio').value+'&prazo_final='+document.getElementById('prazo_final').value+'&data_emissao='+document.getElementById('data_emissao').value+'&numero_termo='+document.getElementById('numero_termo').value,'_blank'); />"+
				'</form>';

	modal(html, '200_350', 'INFORMAÇÕES ADICIONAIS AO ADITAMENTO');
	
}

function criarLinha(indice)
{
	var html = 	'<tr id="tr_'+indice+'">'+
				'<td><input name="data['+indice+']" id="data['+indice+']" size="12" onKeyPress="transformaData(this, event);"></td>'+
				'<td><input name="qtd['+indice+']" id="qtd['+indice+']" size="12"></td>'+
				"<td><select name='selEPI["+indice+"]' class='caixa' id='selEPI["+indice+"]'></select></td>"+
				"<td><span class='icone icone-adicionar cursor' ref='"+indice+"' id='span["+indice+"]' ></span></td></tr>";

	if ($("#tabelaEpi").find('span').length)
	{
		anterior = $("#tabelaEpi").find('span').eq($("#tabelaEpi").find('span').length - 1);

		//Mudando o icone de adicionar para remover
		$(anterior).removeClass('icone-adicionar');
		$(anterior).addClass('icone-remover');
		$(anterior).unbind();
		$(anterior).bind('click', function(){
			$('#tr_'+$(this).attr('ref')).remove();
		});
	}
	
	$('#tr_last').before(html);

	$('#tabelaEpi').find('span').eq(indice).bind('click', function(){
	    criarLinha(parseInt(indice)+1);
	});

	xajax_preencheComboEpi(indice);
		
	return html;
}

function showModalEPI(idFuncionario)
{
	var html = '<form id="frmPDI" method="post"><table id="tabelaEpi">';
	html += '<input name="id_funcionario[0]" type="hidden" id="id_funcionario[0]" value="'+idFuncionario+'">';

	html += '<tr><td><label class="labels">data</label></td>'+
			'<td><label class="labels">Qtd.</label></td>'+
			"<td><label class='labels'>EPI</label></td></tr>";
			
	html += '<tr id="tr_last"><td><input type="button" value="Salvar Arquivo" onclick="xajax_salvarFichaEpi(xajax.getFormValues(\'frmPDI\'));" class="class_botao" /></td></tr></form>';

	
	modal(html, '300_600', 'Ficha de EPI');
	criarLinha(0);
}

function showModalAnexos(idContrato)
{
	var html = '<iframe id="upload_target" name="upload_target" src="#" style="border:0px solid #fff;display:none;"></iframe>'+
			'<form id="frm_0" enctype="multipart/form-data" action="upload_docs_contratacao.php" target="upload_target" method="post">'+
			'<input class="caixa" name="myfile_0" type="file" size="30" style="width:60%;" />'+
			'<input name="id_contrato" type="hidden" id="id_contrato" value="'+idContrato+'">'+
			'<input name="pcd_id" type="hidden" id="pcd_id" value="0">'+
			'<input type="submit" value="Salvar Arquivo" class="class_botao" />'+
			'<div id="divListaArquivos"></div>'+
		'</form><br />';

	xajax_listaArquivos(idContrato);
	
	modal(html, '350_700', 'Arquivos do contratado');
}

function verificarTestemunhas()
{
	var options = $('#testemunhas').find(':selected');
	var testemunhas = '';
	var virgula = '';
	$.each( options, function( key, value ) {
	  testemunhas += virgula+value.text;
	  virgula = '<br />';
	});

	$('#divTestemunhas').html('<b>Testemunhas:</b><br />'+testemunhas);
}

function selecionarPadrao()
{
	seleciona_combo(954, 'testemunhas');
	seleciona_combo(819, 'testemunhas');
}

function verificaTestemunhasSelecionadas()
{
	if ($('#testemunhas').find(':selected').length > 2)
	{
		alert('Por favor, selecione apenas 2 (duas) testemunhas');
		$('#testemunhas').val('');
	}
}

function grid(tabela, autoh, height, xml)
{
	mygrid = new dhtmlXGridObject(tabela);

	mygrid.enableAutoHeight(autoh,height);
	mygrid.enableRowsHover(true,'cor_mouseover');

	function editar(id, row)
	{
		if (row <= 3)
			xajax_editar(id);
	}
	
	switch(tabela)
	{
		case 'divListaContratados':
			mygrid.setHeader("Cod,Nome,Data&nbsp;Início, Data&nbsp;fim, Setor, A, IOS, Seguro, EPI, Termo TI, Contr, Anexo 1");
			mygrid.setInitWidths("50,*,90,90,170,45,50,70,40,70,60,70");
			mygrid.setColAlign("left,left,left,left,left,center,center,center,center,center,center,center");
			mygrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
			mygrid.setColSorting("str,str,str,str,str,str,str,str,str,str,str,str");
			
			mygrid.attachEvent("onRowSelect",editar);
		break;
		case 'divListaArquivos':
			mygrid.setHeader("Título, Arquivo, D");
			mygrid.setInitWidths("190,*,60");
			mygrid.setColAlign("left,left,center");
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

</script>

<?php
$conf = new configs();

$array_cliente_values = NULL;
$array_cliente_output = NULL;

$array_coorddvm_values = NULL;
$array_coorddvm_output = NULL;

$array_status_values = NULL;
$array_status_output = NULL;

$array_site_values = NULL;
$array_site_output = NULL;

$array_os_raiz_values = NULL;
$array_os_raiz_output = NULL;

$array_cliente_values[] = "0";
$array_cliente_output[] = "SELECIONE";
	  
$sql = "SELECT * FROM ".DATABASE.".empresas, ".DATABASE.".unidade ";
$sql .= "WHERE empresas.id_unidade = unidades.id_unidade ";
$sql .= "AND empresas.reg_del = 0 ";
$sql .= "AND unidades.reg_del = 0 ";
$sql .= "AND empresas.status = 'CLIENTE' ";
$sql .= "ORDER BY empresa ";

$db->select($sql,'MYSQL',true);

if ($db->erro != '')
{
	die("Não foi possível realizar a seleção.".$sql);
}

foreach($db->array_select as $regs)
{
	$array_cliente_values[] = $regs["id_empresa_erp"];
	$array_cliente_output[] = $regs["empresa"] . " - " . $regs["descricao"] . " - " . $regs["unidade"];
}

$array_funcionarios_values[] = "0";
$array_funcionarios_output[] = "SELECIONE";

$sql = "SELECT * FROM ".DATABASE.".funcionarios ";
$sql .= "WHERE funcionarios.situacao NOT IN ('DESLIGADO') ";
$sql .= "AND funcionarios.reg_del = 0 ";
$sql .= "ORDER BY funcionario";

$db->select($sql,'MYSQL',true);

if ($db->erro != '')
{
	exit("Não foi possível realizar a seleção.".$sql);
}

foreach($db->array_select as $regs)
{
	$array_funcionarios_values[] = $regs["id_funcionario"];
	$array_funcionarios_output[] = $regs["funcionario"];
}

$smarty->assign("option_funcionarios_values",$array_funcionarios_values);
$smarty->assign("option_funcionarios_output",$array_funcionarios_output);

$sql = "SELECT MAX(numero_contrato) AS proximoContrato FROM ".DATABASE.".pj_contratos ";
$sql .= "WHERE pj_contratos.reg_del = 0 ";

$db->select($sql, 'MYSQL',true);

$proximoContrato = $db->array_select[0];

$nContrato = substr_replace($proximoContrato['proximoContrato'], '', -4, 4);
$anoContrato = intval(substr($proximoContrato['proximoContrato'], -4));

$smarty->assign('proximo_contrato', ($nContrato + 1));

$anos = array('' => 'Ano');

for($i = date('Y'); $i >= 2009; $i--)
{
	$anos[] = $i;
}

$smarty->assign('option_anos_values', $anos);

$array_empresa_values = array();
$array_empresa_output = array();

$array_empresa_values[] = "0";
$array_empresa_output[] = "NENHUMA";

$sql = "SELECT ef.empresa_func, ef.id_empfunc FROM ".DATABASE.".funcionarios f ";
$sql .= "LEFT JOIN ".DATABASE.".empresa_funcionarios ef ON (f.id_empfunc = ef.id_empfunc OR ef.empresa_socio = f.id_funcionario AND ef.reg_del = 0)";
$sql .= "WHERE f.situacao NOT IN ('DESLIGADO') ";
$sql .= "AND f.reg_del = 0 ";
$sql .= "ORDER BY ef.empresa_func ";

$db->select($sql,'MYSQL', true);

if($db->erro!='')
{
	die($db->erro);
}

foreach($db->array_select as $regs)
{
	$array_empresa_values[] = trim($regs["id_empfunc"]);
	$array_empresa_output[] = $regs["empresa_func"];
}

$smarty->assign("option_empresa_values",$array_empresa_values);
$smarty->assign("option_empresa_output",$array_empresa_output);

$smarty->assign("revisao_documento","V2");

$smarty->assign('larguraTotal', 1);

$smarty->assign("campo",$conf->campos('documentos_contratacao'));

$smarty->assign("nome_formulario","DOCUMENTOS CONTRATAÇÃO");

$smarty->assign("classe",CSS_FILE);

$smarty->display('documentos_contratacao.tpl');
?>