<?php
/*
    Formulario de Transferencia de apontamento de horas	
    
    Criado por Carlos Eduardo  
    
    local/Nome do arquivo:
    ../financeiro/transferencia_apontamento_horas.php
    
    Versão 0 --> VERSÃO INICIAL : 27/06/2017
    Versão 1 --> Inclusão dos campos reg_del nas consultas - 20/11/2017 - Carlos Abreu
    Versão 3 --> Layout responsivo - 28/11/2017 - Carlos Eduardo
*/
	
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));

require_once(INCLUDE_DIR."include_form.inc.php");

if(!verifica_sub_modulo(601))
{
    nao_permitido();
}

function voltar()
{
    $resposta = new xajaxResponse();

    $conf = new configs();

    $botao = $conf->botoes($resposta);

    $resposta -> addScriptCall("reset_campos('frm_grupos')");

    $resposta -> addAssign("btninserir", "value", $botao[1]);

    $resposta -> addEvent("btninserir", "onclick", "xajax_insere(xajax.getFormValues('frm_grupos'));");

    $resposta -> addEvent("btnvoltar", "onclick", "history.back();");

    return $resposta;

}

function atualizatabela($dados_form)
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();
	
	//Desselecionando as horas apos trocar de colaborador
	$resposta->addScript("document.getElementById('idHoras').value='';desbloquearBotaoInserir();");
	
	if (empty($dados_form['funcionarios'][0]))
	{
		return $resposta;
	}
	
	$funcionarios = implode(',',$dados_form['funcionarios']);
	
	$inicio  = !empty($dados_form['periodo_de']) ? "AND data >= '".php_mysql($dados_form['periodo_de'])."'" : '';
	$termino = !empty($dados_form['periodo_ate']) ? "AND data <= '".php_mysql($dados_form['periodo_ate'])."'" : '';
	
	$sql = "SELECT 
			a.id_apontamento_horas, a.id_funcionario, a.id_atividade, a.tarefa, a.id_setor, a.id_os, a.complemento, a.orcado, a.justificativa, a.data, a.data_inclusao, a.hora_normal, 
			a.hora_adicional, a.hora_adicional_noturna, a.hora_inicial, a.hora_final, a.retrabalho, a.id_local_trabalho_externo,
			b.descricao as Atividade, c.OS, codigo, funcionario
		FROM 
			".DATABASE.".apontamento_horas a
			JOIN ".DATABASE.".atividades b ON b.id_atividade = a.id_atividade AND b.reg_del = 0 
			JOIN ".DATABASE.".ordem_servico c ON c.id_os = a.id_os AND c.reg_del = 0 
			JOIN (SELECT funcionario, id_funcionario codFunc FROM ".DATABASE.".funcionarios WHERE funcionarios.reg_del = 0) d ON d.codFunc = a.id_funcionario
		WHERE 
			".$inicio." ".$termino."
			AND a.reg_del = 0 
			AND id_funcionario IN(".$funcionarios.")";
	
	$db->select($sql, 'MYSQL', true);
	$dados = $db->array_select;
	
	$xml = new XMLWriter();
	$xml->openMemory();
	$xml->startElement('rows');
	
	$nLinhas = 0;
	foreach($dados as $reg)
	{
		/*
		//VERIFICA SE O APONTAMENTO JÁ ESTA APROVADO NO PROTHEUS
		$sql = "SELECT * FROM AJK010 WITH(NOLOCK) ";
		$sql .= "WHERE AJK010.D_E_L_E_T_ = '' ";
		$sql .= "AND AJK010.AJK_CTRRVS = '1' ";
		$sql .= "AND AJK010.AJK_ID_DVM = '".trim($reg["id_apontamento_horas"])."' ";
		$sql .= "AND AJK_SITUAC = '2' ";
		$sql .= "AND AJK010.AJK_RECURS = 'FUN_".sprintf("%011d",$reg["id_funcionario"])."' ";

		$db->select($sql,'MSSQL', true);
		
		if (count($db->array_select) > 0)
		{
			continue;
		}
		*/
		
		$os = sprintf('%010d', $reg['OS']);
		
		$xml->startElement('row');
			$xml->writeAttribute('id', $reg["id_funcionario"].'_'.$reg["id_apontamento_horas"]);

			$descricao = !empty($reg['complemento']) ? $tarefa[0].' - '.$reg['complemento'] : $tarefa[0];
		
			$xml->writeElement('cell', "&nbsp;");
			$xml->writeElement('cell', $reg["funcionario"]);
			$xml->writeElement('cell', mysql_php($reg["data"]));
			$xml->writeElement('cell', $reg["hora_inicial"]);
			$xml->writeElement('cell', $reg['hora_final']);
			$xml->writeElement('cell', $reg['Atividade']);
			
		$xml->endElement();
		$nLinhas ++;		
	}
	
	if ($nLinhas == 0)
	{
		$resposta->addAlert('Todas as horas deste colaborador ja foram aprovadas!');
		return $resposta;
	}
	
	$xml->endElement();
	
	$conteudo = $xml->outputMemory(true);
	
	$resposta->addScript("grid('div_apontamentos', true, '415', '".$conteudo."');");
	
	return $resposta;
}

function alterar($dados_form, $chaveTabela, $chave)
{
    $resposta = new xajaxResponse();
    $db = new banco_dados();

    if (empty($chaveTabela) || empty($chave))
    {
        $resposta->addAlert('Existe uma inconsistência na query. Nada será feito.');
        return $resposta;
    }
    
    $sql = "UPDATE ".$dados_form['tabela']." SET ";
    $sql .= $dados_form['campo']." = '".trim($dados_form[$dados_form['campo']])."' ";
    $sql .= "WHERE ".$chaveTabela." = '".$chave."' ";
	$sql .= "AND reg_del = 0 ";

    $db->update($sql, 'MYSQL');
    
    if ($db->erro != '')
        $resposta->addAlert("Houve uma falha ao tentar executar a query:\n(".$sql.")");
    else
    {
        $resposta->addAlert("Alteracao realizada!");
        $resposta->addScript("xajax_buscar(xajax.getFormValues('frm'));");
    }

    return $resposta;
}

function modal_transferir($dados_form)
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();

	$virgula = '';
	foreach($dados_form['funcionarios'] as $func)
	{
		$codFuncionarios = $virgula."'FUN_".sprintf('%011d', $func)."'";
		$virgula = ',';
	}	
	
	/*
	$sql = "SELECT TOP 200 AF8_PROJET , AF8_REVISA, AF8_DESCRI, AFA_RECURS
		FROM AF8010 WITH (NOLOCK), AF9010 WITH (NOLOCK), AFA010 WITH (NOLOCK)
		WHERE AF8010.D_E_L_E_T_ = ''
		AND AFA010.D_E_L_E_T_ = ''
		AND AF9010.D_E_L_E_T_ = ''
		AND AF9010.AF9_COMPOS <> ''
		AND AF9010.AF9_PROJET = AFA010.AFA_PROJET
		AND AF9010.AF9_REVISA = AFA010.AFA_REVISA
		AND AF9010.AF9_TAREFA = AFA010.AFA_TAREFA
		AND AFA010.AFA_RECURS IN(".$codFuncionarios.")
		AND AF9010.AF9_PROJET = AF8010.AF8_PROJET
		AND AF9010.AF9_REVISA = AF8010.AF8_REVISA
		--AND AF8010.AF8_FASE IN ('09') descomentar esta linha
		GROUP BY AF8_PROJET, AF8_REVISA, AF8_DESCRI, AFA_RECURS
		ORDER BY AF8_PROJET, AF8_REVISA DESC";

	$db->select($sql, 'MSSQL', true);
	
	if ($db->numero_registros_ms == 0 || empty($db->array_select[0]))
	{
		$resposta->addAlert('Nenhuma OS foi encontrada para este colaborador!');
		return $resposta;
	}
	*/
	
	$html = '<form id="frmTransf">';
	$html .= '<input type="hidden" name="idHoras" id="idHoras" value="'.$dados_form['idHoras'].'" />';
	$html .= '<label class="labels">Selecione uma OS</label><br /><select name="selOs" id="selOs" onchange=xajax_tarefas_os(xajax.getFormValues("frm"),this.value); style="width:100%;">';
	$html .= '<option value="">SELECIONE</option>';
	
	/*
	foreach($db->array_select as $reg)
	{
		$html .= '<option value="'.intval($reg['AF8_PROJET']).'_'.trim($reg['AF8_REVISA']).'">'.trim($reg['AF8_PROJET']).' - '.trim($reg['AF8_DESCRI']).'</option>';
	}
	*/
	
	$html .= '</select><br />';
	
	$html .= '<label class="labels">Tarefas</label><br /><select name="selTarefas" id="selTarefas" style="width:100%;">';
	
	$html .= '<input type="button" value="Confirmar Transferência" onclick=xajax_confirmar_transferencia(xajax.getFormValues("frmTransf")); style="width:170px;position:relative;bottom:-80px;" class="class_botao" />';
	
	$html .= '</form>';
	$resposta->addScript("modal('".$html."','200_750','Escolha OS e tarefa destino');");
    
	return $resposta;
}

function confirmar_transferencia($dados_form)
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();
	
	if (empty($dados_form['idHoras']) || empty($dados_form['selOs']) || empty($dados_form['selTarefas']))
	{
		$resposta->addAlert('Por favor, selecione uma OS e uma tarefa para realizar a transferencia');
	}
	else
	{
		$tarefa = $dados_form['selTarefas'];
		$osDestino = explode('_', $dados_form['selOs']);
		
		$sql = "SELECT id_os FROM ".DATABASE.".ordem_servico ";
		$sql .= "WHERE ordem_servico.os = ".$osDestino[0]." ";
		$sql .= "AND ordem_servico.reg_del = 0 ";
		
		$db->select($sql, 'MYSQL', true);
		
		$idOs = $db->array_select[0]['id_os'];
		
		if (empty($idOs))
		{
			$resposta->addAlert('OS não encontrada, favor, tentar novamente mais tarde!');
			return $resposta;
		}
		
		$arrHoras = array();
		$idHoras = explode(',', $dados_form['idHoras']);
		foreach($idHoras as $hora)
		{
			$id = explode('_', $hora);
			
			$arrHoras[$id[0]] .= $virgula.$id[1];
			$virgula = ',';
		}
		
		$idHoras = implode(',', $arrHoras);
		
		$sql = "SELECT id_apontamento_horas, id_funcionario, id_atividade, tarefa, id_setor, id_os, complemento, orcado, justificativa, data, data_inclusao, hora_normal, hora_adicional, hora_adicional_noturna,";
    	$sql .= "hora_inicial, hora_final, retrabalho, id_local_trabalho_externo ";
    	$sql .= "FROM ".DATABASE.".apontamento_horas ";
    	$sql .= "WHERE id_apontamento_horas IN(".$idHoras.") ";
		$sql .= "AND apontamento_horas.reg_del = 0 ";		
    	
    	$db->select($sql, 'MYSQL', true);
    	
		$dados = $db->array_select;

    	//Inserindo as horas de destino
    	foreach($dados as $reg)
    	{
			/*
    		//Verificando se ja existe apontamento para o projeto, recurso, versao_documento e data lancado no sistema
    		$sql = "SELECT 
					AJK_PROJET, AJK_REVISA, AJK_RECURS, AJK_DATA
				FROM
					AJK010 
				WHERE 
					AJK_PROJET = '".sprintf('%010d', trim($osDestino[0]))."'
					AND AJK_REVISA = '".sprintf('%04d', trim($osDestino[1]))."'
					AND AJK_RECURS = 'FUN_".sprintf("%011d",$reg["id_funcionario"])."' 
					AND AJK_DATA = '".str_replace("-","",php_mysql($reg["data"]))."';";
    		
    		$db->select($sql, 'MSSQL', true);
    		
    		if (count($db->array_select) > 0)
    		{
    			$resposta->addAlert('Ja existe um apontamento para este PROJETO, RECURSO e DATA!');
    			return $resposta;	
    		}

    		//MSSQL
			//obtem a composicao
			$sql = "SELECT AF9010.AF9_COMPOS, AF9010.AF9_DESCRI FROM AF9010 WITH(NOLOCK) ";
			$sql .= "WHERE AF9010.D_E_L_E_T_ = ''  ";
			$sql .= "AND AF9010.AF9_PROJET = '".sprintf('%010d', trim($osDestino[0]))."'  ";
			$sql .= "AND AF9010.AF9_REVISA = '".sprintf('%04d', trim($osDestino[1]))."'  ";
			$sql .= "AND AF9010.AF9_TAREFA = '".$tarefa."' ";

			$db->select($sql,'MSSQL', true);

			if($db->erro!='')
			{
				$html = $db->erro . "<br><br>";
				$html .= "Um e-mail foi enviado ao desenvolvimento.";
				
				$resposta->addScript('modal("'.$html.'","p","Erro")');
				
				return $resposta;
			}
			
			
			$compos = $db->array_select[0];
			
			//pega o grupo da disciplina
			$grpcom = substr(trim($compos["AF9_COMPOS"]),0,3);
			
			//pega a descricao da atividade
			$desc_ativ = $compos["AF9_DESCRI"];
		
			//obtem a atividade
			$sql = "SELECT id_atividade, codigo, cod FROM ".DATABASE.".atividades ";
			$sql .= "WHERE codigo = '".trim($compos["AF9_COMPOS"])."' ";
			$sql .= "AND atividades.reg_del = 0 ";

			$db->select($sql,'MYSQL', true);

			if($db->erro!='')
			{
				$html = $db->erro . "<br><br>";
				$html .= "Um e-mail foi enviado ao desenvolvimento.";
				
				$resposta->addScript('modal("'.$html.'","p","Erro")');
				
				return $resposta;
			}
	
			if($db->numero_registros>0)
			{		
				$codativ = $db->array_select[0];
				
				$codatividade = $codativ["id_atividade"];
				
				$codigo = $codativ["codigo"];
				
				$codset = $codativ["cod"];		
			}
			else
			{
				$resposta->addAlert('Atividade/tarefa nao reconhecida. Favor conversar com seu coordenador/planejador.');
				
				return $resposta;	
			}
			*/
			
	    	$isql = "INSERT INTO ".DATABASE.".apontamento_horas ";
	    	$isql .= "(id_funcionario, id_atividade, tarefa, id_setor, id_os, complemento, orcado, justificativa, data, ";
	    	$isql .= "data_inclusao, hora_normal, hora_adicional, hora_adicional_noturna, hora_inicial, hora_final, ";
	    	$isql .= "retrabalho, id_local_trabalho_externo) VALUES ";
	    	
	    	$isql .= "('".$reg['id_funcionario']."', '".$codatividade."', '".$tarefa."', '".$reg['id_setor']."', '".$idOs."', '".$reg['complemento']."', '".$reg['orcado']."', '".$reg['justificativa']."', '".$reg['data']."', ";
    		$isql .= "'".$reg['data_inclusao']."', '".$reg['hora_normal']."', '".$reg['hora_adicional']."', '".$reg['hora_adicional_noturna']."', '".$reg['hora_inicial']."', '".$reg['hora_final']."', ";
    		$isql .= "'".$reg['retrabalho']."', '".$reg['id_local_trabalho_externo']."')";
    		
    		$horas_total = $reg['hora_normal']+$reg['hora_adicional']+$reg['hora_adicional_noturna'];
    		
    		$db->insert($isql, 'MYSQL');
	    	
			$idInserido = $db->insert_id;
	    	//$idInserido = 457383;
    		//CHAMAR EXCLUSAO
    		if ($db->erro != '')
    		{
    			$resposta->addAlert('Houve uma falha ao tentar realizar a transferência de horas!');
    		}
    		else
    		{
				
	    		if (excluir($reg['id_apontamento_horas'], $resposta))
	    		{
					/*
	    			//Inserindo no Protheus
	    			$sql = "SELECT TOP 1 YP_CHAVE FROM SYP010 WITH(NOLOCK) ";
					$sql .= "WHERE SYP010.D_E_L_E_T_ = '' ";				
					$sql .= "ORDER BY YP_CHAVE DESC ";
					
		    		$db->select($sql,'MSSQL', true);
					
					if($db->erro!='')
					{
						$html = $db->erro . "<br><br>";
						$html .= "Um e-mail foi enviado ao desenvolvimento.";
						
						$resposta->addScript('modal("'.$html.'","p","Erro")');
						
						return $resposta;
					}
					
					$regs10 = $db->array_select[0];
					
					$chave_syp = $regs10["YP_CHAVE"] + 1;
					
					//traz o tamanho do complemento
					$qtd_char = strlen($reg['complemento']);
					
					$num_str = 0;
					
					if($qtd_char>0)
					{
						//quantos itens deve ter
						$num_str = ceil($qtd_char/80);
						
						for($i = 1; $i<=$num_str ;$i++)
						{
							//banco memo protheus
							$sql = "SELECT TOP 1 R_E_C_N_O_ FROM SYP010 WITH(NOLOCK) ";	
							$sql .= "ORDER BY R_E_C_N_O_ DESC ";
	
							$db->select($sql,'MSSQL',true);
	
							if($db->erro!='')
							{
								$html = $db->erro . "<br><br>";
								$html .= "Um e-mail foi enviado ao desenvolvimento.";
								
								$resposta->addScript('modal("'.$html.'","p","Erro")');
								
								return $resposta;
							}
							
							$regs9 = $db->array_select[0];
							
							$recno_syp = $regs9["R_E_C_N_O_"] + 1;
							
							$isql = "INSERT INTO SYP010 ";
							$isql .= "(YP_CHAVE, YP_SEQ, YP_TEXTO, YP_CAMPO, D_E_L_E_T_, R_E_C_N_O_, R_E_C_D_E_L_) ";
							$isql .= "VALUES ('" . sprintf("%06d",$chave_syp) . "', ";
							$isql .= "'" . sprintf("%03d",$i) . "', ";
							$isql .= "'" . substr($reg['complemento'],(($i-1)*80),($i*80)) . "', ";
							$isql .= "'AJK_CODMEM', ";
							$isql .= "'', ";
							$isql .= "'" . $recno_syp . "', ";
							$isql .= "'0') ";
							
							$db->insert($isql,'MSSQL');
	
							if($db->erro!='')
							{
								$html = $db->erro . "<br><br>";
								$html .= "Um e-mail foi enviado ao desenvolvimento.";
								
								$resposta->addScript('modal("'.$html.'","p","Erro")');
								
								return $resposta;
							}	
						}
					}
	    			
					$hi = explode(":",$reg['hora_inicial']);
					
					$hf = explode(":",$reg['hora_final']);
					
					//banco pre-aprovacao protheus
					$sql = "SELECT TOP 1 R_E_C_N_O_ FROM AJK010 ";				
					$sql .= "ORDER BY R_E_C_N_O_ DESC ";
	
					$db->select($sql,'MSSQL',true);
					
					if($db->erro!='')
					{
						$html = $db->erro . "<br><br>";
						$html .= "Um e-mail foi enviado ao desenvolvimento.";
						
						$resposta->addScript('modal("'.$html.'","p","Erro")');
						
						return $resposta;
					}	
					
					$regs = $db->array_select[0];
					
					$recno_ajk = $regs["R_E_C_N_O_"] + 1;
					
					$isql = "INSERT INTO AJK010 ";
					$isql .= "(AJK_PROJET, AJK_REVISA, AJK_TAREFA, AJK_RECURS, AJK_DATA, AJK_HORAI, AJK_HORAF, AJK_HQUANT, AJK_CTRRVS, AJK_CODMEM, AJK_DOCUME, AJK_ITEM, AJK_SITUAC, AJK_ID_DVM, AJK_EQUIP, R_E_C_N_O_) ";
					$isql .= "VALUES ('" . sprintf('%010d', trim($osDestino[0])) . "', ";
					$isql .= "'" . trim($osDestino[1]) . "', ";
					$isql .= "'" . trim($tarefa) . "', ";
					$isql .= "'FUN_".sprintf("%011d",$reg["id_funcionario"])."', ";
					$isql .= "'" . str_replace("-","",php_mysql($reg["data"])) . "', ";
					$isql .= "'" . sprintf("%02d",$hi[0]) .":".sprintf("%02d",$hi[1]). "', ";
					$isql .= "'" . sprintf("%02d",$hf[0]) .":".sprintf("%02d",$hf[1]) . "', ";
					$isql .= "" . $horas_total . ", ";
					$isql .= "1, ";				
					$isql .= "'" . sprintf("%06d",$chave_syp) . "', ";
					$isql .= "'". sprintf("%09d",$idInserido). "', ";
					$isql .= "'01', ";				
					$isql .= "'1', ";
					$isql .= "". $idInserido. ", ";
					$isql .= "'".sprintf("%010d",$reg["id_setor"])."', ";
					$isql .= "'".$recno_ajk."') ";
					
					$db->insert($isql,'MSSQL');
	
					if($db->erro!='')
					{
						$sql = "SELECT * FROM AJK010 ";
						$sql .= "WHERE AJK_ID_DVM = '" . $idInserido ."' ";
						$sql .= "AND AJK010.D_E_L_E_T_ = '' ";
						
						$db->select($sql,'MSSQL',true);
						
						//21/09/2015 - Remover automaticamente o apontamento quando nao for inserido corretamente no PROTHEUS
						if ($db->erro != '' || $db->numero_registros_ms == 0)
						{

							$usql = "UPDATE ".DATABASE.".apontamento_horas SET ";
							$usql .= "reg_del = 1, ";
							$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
							$usql .= "data_del = '".date('Y-m-d')."' ";
							$usql .= "WHERE id_apontamento_horas = '" . $idInserido . "' ";
							$usql .= "AND reg_del = 0 ";
							
							$db->update($usql, 'MYSQL');
							
						}
						
						$html = $db->erro . "<br><br>";
						$html .= "Um e-mail foi enviado ao desenvolvimento.";
						
						$resposta->addScript('modal("'.$html.'","p","Erro")');
						
						return $resposta;
					}
										
	    			$resposta->addAlert('Transferencia de horas realizada corretamente!');
		    		$resposta->addScript('divPopupInst.destroi();');
					$resposta->addScript('xajax_atualizatabela(xajax.getFormValues("frm"));');
					
					*/
	    		}
		    	else
		    	{
		    		$resposta->addAlert('Houve uma falha ao tentar realizar a transferencia de horas!');	
		    	}
    		}
    	}
	}
	
	return $resposta;
}

function tarefas_os($dados_form, $os)
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();
	
	$os = explode('_', $os);
	
	$virgula = '';
	foreach($dados_form['funcionarios'] as $func)
	{
		$codFuncionarios = $virgula."'FUN_".sprintf('%011d', $func)."'";
		$virgula = ',';
	}

	/*
	$sql = "SELECT AF9_PROJET, AF9_REVISA, AF9_TAREFA, AF9_COMPOS, AF9_DESCRI, AF9_QUANT, AF9_START 
		FROM AFA010 WITH (NOLOCK), AF9010 WITH (NOLOCK) 
		WHERE AF9010.D_E_L_E_T_ = '' 
		AND AF9010.AF9_PROJET = '".sprintf('%010d', $os[0])."' 
		AND AF9010.AF9_REVISA = '".sprintf('%04d', $os[1])."' 
		AND AF9010.AF9_COMPOS <> '' 				
		AND AFA010.D_E_L_E_T_ = '' 
		AND AF9010.AF9_PROJET = AFA010.AFA_PROJET 
		AND AF9010.AF9_REVISA = AFA010.AFA_REVISA 
		AND AF9010.AF9_TAREFA = AFA010.AFA_TAREFA 
		AND AFA010.AFA_RECURS IN(".$codFuncionarios.") 			
		GROUP BY AF9010.AF9_PROJET, AF9010.AF9_REVISA, AF9010.AF9_TAREFA, AF9010.AF9_COMPOS, AF9010.AF9_DESCRI, AF9010.AF9_QUANT, AF9_START 
		ORDER BY AF9010.AF9_START, AF9010.AF9_TAREFA ";

	$db->select($sql,'MSSQL', true);
	
	$resposta->addScriptCall("limpa_combo('selTarefas')");
	//$resposta->addScript("selTarefas.options[selTarefas.length] = new Option('ESCOLHA A TAREFA', '');");
	$options = '<option value="">ESCOLHA A TAREFA</option>';
	foreach($db->array_select as $regs)
	{
		$options .= '<option value="'.trim($regs["AF9_TAREFA"]).'">'.trim($regs["AF9_TAREFA"])." - ".trim($regs["AF9_COMPOS"])." - ".maiusculas($regs["AF9_DESCRI"]).'</option>';
		//$resposta->addScript("selTarefas.options[selTarefas.length] = new Option('".trim($regs["AF9_TAREFA"])." - ".trim($regs["AF9_COMPOS"])." - ".maiusculas($regs["AF9_DESCRI"])."', '".trim($regs["AF9_TAREFA"])."',false,false);");
	}
	*/
	
	$resposta->addAssign('selTarefas', 'innerHTML', $options);
	return $resposta;
}

function excluir($id_horas, &$resposta)
{
	$db = new banco_dados;
	
	/*
	//VERIFICA SE O APONTAMENTO JÁ ESTA CONFIRMADO NO PROTHEUS
	$sql = "SELECT AJK010.AJK_RECURS FROM AJK010 WITH (NOLOCK) ";
	$sql .= "WHERE AJK010.D_E_L_E_T_ = '' ";
	$sql .= "AND AJK010.AJK_CTRRVS = '1' ";
	$sql .= "AND AJK010.AJK_SITUAC IN ('1','3') "; //NÃO CONFIRMADO, REPROVADO
	$sql .= "AND AJK010.AJK_ID_DVM = '".trim($id_horas)."' ";

	$db->select($sql,'MSSQL',true);

	if($db->erro!='')
	{
		$html = $db->erro . "<br><br>";
		$html .= "Um e-mail foi enviado ao desenvolvimento.";
		
		$resposta->addScript('modal("'.$html.'","p","Erro")');
		
		return false;
	}
	
	//Não existe o registro com situação de aprovado
	if($db->numero_registros_ms>0)
	{	

		$usql = "UPDATE ".DATABASE.".apontamento_horas SET ";
		$usql .= "reg_del = 1, ";
		$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
		$usql .= "data_del = '".date('Y-m-d')."' ";
		$usql .= "WHERE id_apontamento_horas = '".$id_horas."' ";
		$usql .= "AND reg_del = 0 ";
		
		$db->update($usql,'MYSQL');
		
		if($db->erro!='')
		{
			$html = $db->erro . "<br><br>";
			$html .= "Um e-mail foi enviado ao desenvolvimento.";
			
			$resposta->addScript('modal("'.$html.'","p","Erro")');
			
			return false;
		}
		
		$sql = "SELECT AJK_CODMEM FROM AJK010 WITH (NOLOCK) ";
		$sql .= "WHERE AJK010.D_E_L_E_T_ = '' ";	
		$sql .= "AND AJK_ID_DVM = '".$id_horas."' ";

		$db->select($sql,'MSSQL', true);
		
		if($db->erro!='')
		{
			$html = $db->erro . "<br><br>";
			$html .= "Um e-mail foi enviado ao desenvolvimento.";
			
			$resposta->addScript('modal("'.$html.'","p","Erro")');
			
			return false;
		}
		
		$regs = $db->array_select[0];
		
		if($regs["AJK_CODMEM"]!='')
		{
			$usql = "UPDATE SYP010 SET ";
			$usql .= "D_E_L_E_T_ = '*', ";
			$usql .= "R_E_C_D_E_L_ = R_E_C_N_O_ ";
			$usql .= "WHERE YP_CHAVE = '".$regs["AJK_CODMEM"]."' ";
			$usql .= "AND YP_CAMPO = 'AJK_CODMEM' ";

			$db->update($usql,'MSSQL');

			if($db->erro!='')
			{
				$html = $db->erro . "<br><br>";
				$html .= "Um e-mail foi enviado ao desenvolvimento.";
				
				$resposta->addScript('modal("'.$html.'","p","Erro")');
				
				return false;
			}
		}			
		
		$usql = "UPDATE AJK010 SET ";
		$usql .= "D_E_L_E_T_ = '*' ";
		$usql .= "WHERE AJK_ID_DVM = '".$id_horas."' ";

		$db->update($usql,'MSSQL');
		
		if($db->erro!='')
		{
			$html = $db->erro . "<br><br>";
			$html .= "Um e-mail foi enviado ao desenvolvimento.";
			
			$resposta->addScript('modal("'.$html.'","p","Erro")');
			
			return false;
		}
	}
	else
	{
		$resposta->addAlert("O apontamento ja esta confirmado e nao pode ser excluido.");
	}
	*/
	
	return true;
}

$xajax->registerFunction("voltar");
$xajax->registerFunction("alterar");
$xajax->registerFunction("atualizatabela");
$xajax->registerFunction("modal_transferir");
$xajax->registerFunction("tarefas_os");
$xajax->registerFunction("confirmar_transferencia");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));
?>

<script type="text/javascript" src="../includes/validacao.js"></script>

<script src="<?php echo INCLUDE_JS; ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script language="javascript">
function desbloquearBotaoInserir()
{
	var idHoras = document.getElementById('idHoras').value;

	if (idHoras != '')
	{
		document.getElementById('btninserir').disabled = false;
	}
	else
	{
		document.getElementById('btninserir').disabled = true;
	} 	
}

function grid(tabela, autoh, height, xml)
{
	mygrid = new dhtmlXGridObject(tabela);
	
	mygrid.setImagePath("<?php echo INCLUDE_JS; ?>dhtmlx_403/codebase/imgs/");

	var idHoras = document.getElementById('idHoras');
	var chkAll = '<input type="checkbox" id="chkTodos" onclick="mygrid.checkAll(this.checked);idHoras.value=mygrid.getCheckedRows(0);desbloquearBotaoInserir();" />';
	
	mygrid.setHeader(chkAll+",Colaborador, Data, Hora inicial, Hora final, Atividade");
	mygrid.setInitWidths("60,*,100,100,100,*");
	mygrid.setColAlign("left,left,left,left,left,left");
	mygrid.setColTypes("ch,ro,ro,ro,ro,ro");
	mygrid.setColSorting("na,str,str,str,str,str");
	mygrid.enableMultiline(true);

	mygrid.enableRowsHover(true,'cor_mouseover');

	mygrid.attachEvent("onCheck", function(rId,cInd,state){
		idHoras.value=mygrid.getCheckedRows(0);desbloquearBotaoInserir();
	});
	
	mygrid.setSkin("dhx_skyblue");
    mygrid.enableMultiselect(true);
    mygrid.enableCollSpan(true);
    mygrid.checkAll(true);
	mygrid.init();
	mygrid.loadXMLString(xml);
}
</script>

<?php
$conf = new configs();

$sql = "SELECT 
		DISTINCT a.id_funcionario, b.funcionario
	FROM 
		".DATABASE.".apontamento_horas a
		JOIN ".DATABASE.".funcionarios b ON b.id_funcionario = a.id_funcionario AND b.reg_del = 0 
	WHERE 
		a.reg_del = 0 ";
	

$array_funcs_values = array("");
$array_funcs_output = array("SELECIONE");

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}

foreach($db->array_select as $reg)
{
  	$array_funcs_values[] = $reg['id_funcionario'];
  	$array_funcs_output[] = $reg['funcionario'];
}

$smarty->assign("option_funcs_values",$array_funcs_values);
$smarty->assign("option_funcs_output",$array_funcs_output);

$smarty->assign("campo",$conf->campos('transferencia_apontamento_horas'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("revisao_documento","V2");

$smarty->assign('larguraTotal', 1);

$smarty->assign("classe",CSS_FILE);

$smarty->display('transferencia_apontamento_horas.tpl');

?>